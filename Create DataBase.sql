/*
================================================================
    SCRIPT COMPLETO: Base de Datos "Mi Espacio EDD" (v2)
    Motor: MySQL
    Fecha de creación: 30 de octubre de 2025
    Fecha de modificación: 11 de noviembre de 2025
================================================================
*/

-- Establecer el motor de almacenamiento y el charset por defecto (Buena práctica)
SET default_storage_engine=InnoDB;
SET NAMES 'utf8mb4';
create database Mi_espacio_EDD;
use Mi_espacio_EDD;
/*==============================================================
   1. Tablas de Catálogos (Independientes)
==============================================================*/

-- Almacena los departamentos (ej. "Sistemas y Computación")
CREATE TABLE Departamentos (
    id_departamento INT PRIMARY KEY AUTO_INCREMENT,
    nombre_departamento VARCHAR(255) NOT NULL UNIQUE
);

-- Almacena los periodos (ej. "Enero-junio 2024")
CREATE TABLE PeriodosEscolares (
    id_periodo INT PRIMARY KEY AUTO_INCREMENT,
    nombre_periodo VARCHAR(100) NOT NULL UNIQUE,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL
);

-- Catálogo de todas las materias que se pueden impartir
CREATE TABLE Materias (
    clave_materia VARCHAR(20) PRIMARY KEY,
    nombre_materia VARCHAR(255) NOT NULL,
    nivel VARCHAR(50) NOT NULL DEFAULT 'LICENCIATURA'
);

-- Almacena el formato y cuerpo base de cada tipo de documento
CREATE TABLE PlantillasDocumentos (
    id_plantilla INT PRIMARY KEY AUTO_INCREMENT,
    nombre_plantilla VARCHAR(255) NOT NULL,
    tipo_letra VARCHAR(50),
    tamaño_letra INT,
    margenes VARCHAR(100),
    formato VARCHAR(50),
    cuerpo LONGTEXT,       -- El cuerpo de la plantilla (el "contenido" que usa TCPDF)
    rol_firmante VARCHAR(100), -- Rol que debe firmar (ej. 'DIRECTOR', 'JEFE_AREA')
    tipo_plantilla VARCHAR(50)  -- Para que el sistema sepa qué datos cargar (ej. 'SERVICIO', 'TUTORIA')
);

/* ==============================================================
   2. Tablas de Personal (Manejo de "IS A")
   ==============================================================*/

-- Superclase que almacena datos comunes a todo el personal
CREATE TABLE Personal (
    matricula VARCHAR(20) PRIMARY KEY,
    curp VARCHAR(18) UNIQUE NOT NULL,
    filiacion VARCHAR(50) NULL UNIQUE, -- Identificador de RRHH
    nombre VARCHAR(100) NOT NULL,
    ap_paterno VARCHAR(100) NOT NULL,
    ap_materno VARCHAR(100),
    correo VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL, -- (MODIFICADO a VARCHAR por recomendación anterior)
    tipo_personal ENUM('DIRECTOR', 'SUBDIRECTOR', 'JEFE_AREA', 'DOCENTE') NOT NULL,
    fecha_ingreso DATE,
    id_departamento INT,
    
    -- ==============================================================
    -- NUEVO CAMBIO 1: Ruta a la firma (QR o imagen)
    -- ==============================================================
    ruta_firma_qr VARCHAR(512) NULL, -- Almacena la ruta en el servidor (ej. '/firmas/qr_123.png')

    FOREIGN KEY (id_departamento) REFERENCES Departamentos(id_departamento)
);

-- Subclases de Personal (para especialización)
-- (Estas tablas no sufren cambios)
CREATE TABLE Directores (
    matricula_personal VARCHAR(20) PRIMARY KEY,
    FOREIGN KEY (matricula_personal) REFERENCES Personal(matricula) ON DELETE CASCADE
);

CREATE TABLE Subdirectores (
    matricula_personal VARCHAR(20) PRIMARY KEY,
    FOREIGN KEY (matricula_personal) REFERENCES Personal(matricula) ON DELETE CASCADE
);

CREATE TABLE JefesArea (
    matricula_personal VARCHAR(20) PRIMARY KEY,
    departamento_cargo VARCHAR(100) NOT NULL, -- Atributo específico de Jefe_Area
    FOREIGN KEY (matricula_personal) REFERENCES Personal(matricula) ON DELETE CASCADE
);

CREATE TABLE Docentes (
    matricula_personal VARCHAR(20) PRIMARY KEY,
    grado_estudios VARCHAR(100),
    tipo_grado_docente VARCHAR(100),
    FOREIGN KEY (matricula_personal) REFERENCES Personal(matricula) ON DELETE CASCADE
);

/* ==============================================================
   3. Tablas de Datos del Docente (Para generar constancias)
   (Estas tablas no sufren cambios)
  ==============================================================*/

CREATE TABLE Plazas (
    id_plaza INT PRIMARY KEY AUTO_INCREMENT,
    matricula_personal VARCHAR(20) NOT NULL,
    categoria VARCHAR(255) NOT NULL,
    horas INT NOT NULL,
    estatus VARCHAR(50) NOT NULL,
    clave_presupuestal VARCHAR(100) UNIQUE NOT NULL,
    fecha_efectos DATE NOT NULL,
    es_actual BIT NOT NULL DEFAULT 1,
    
    FOREIGN KEY (matricula_personal) REFERENCES Personal(matricula) ON DELETE CASCADE
);

CREATE TABLE Grupos (
    id_grupo INT PRIMARY KEY AUTO_INCREMENT,
    matricula_docente VARCHAR(20) NOT NULL,
    clave_materia VARCHAR(20) NOT NULL,
    id_periodo INT NOT NULL,
    alumnos_atendidos INT NOT NULL,
    alumnos_aprobados INT,
    
    FOREIGN KEY (matricula_docente) REFERENCES Docentes(matricula_personal),
    FOREIGN KEY (clave_materia) REFERENCES Materias(clave_materia),
    FOREIGN KEY (id_periodo) REFERENCES PeriodosEscolares(id_periodo)
);

CREATE TABLE Horarios (
    id_horario INT PRIMARY KEY AUTO_INCREMENT,
    id_grupo INT NOT NULL,
    dia_semana TINYINT NOT NULL, -- 1=Lunes, 2=Martes, etc.
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    
    FOREIGN KEY (id_grupo) REFERENCES Grupos(id_grupo) ON DELETE CASCADE,
    CHECK (dia_semana BETWEEN 1 AND 7)
);

CREATE TABLE GruposTutoria (
    id_tutoria INT PRIMARY KEY AUTO_INCREMENT,
    matricula_docente VARCHAR(20) NOT NULL,
    id_periodo INT NOT NULL,
    carrera VARCHAR(255) NOT NULL,
    alumnos_atendidos INT NOT NULL,
    alumnos_aprobados INT NULL,
    
    FOREIGN KEY (matricula_docente) REFERENCES Docentes(matricula_personal),
    FOREIGN KEY (id_periodo) REFERENCES PeriodosEscolares(id_periodo)
);

CREATE TABLE ActividadesDocente (
    id_actividad INT PRIMARY KEY AUTO_INCREMENT,
    matricula_docente VARCHAR(20) NOT NULL,
    id_periodo INT NOT NULL,
    tipo_actividad VARCHAR(255) NOT NULL,
    nombre_actividad VARCHAR(500) NOT NULL,
    descripcion LONGTEXT,
    numero_dictamen VARCHAR(100) NULL, -- Para actividades como "monitor de cine"
    
    FOREIGN KEY (matricula_docente) REFERENCES Docentes(matricula_personal),
    FOREIGN KEY (id_periodo) REFERENCES PeriodosEscolares(id_periodo)
);

CREATE TABLE RegistrosAnualesHR (
    id_registro INT PRIMARY KEY AUTO_INCREMENT,
    matricula_personal VARCHAR(20) NOT NULL,
    id_periodo INT NOT NULL,
    sanciones LONGTEXT NULL, -- "No cuenta con ninguna sanción"
    cumplimiento_jornada DECIMAL(5, 2) NOT NULL, -- Porcentaje (ej. 90.00)
    
    FOREIGN KEY (matricula_personal) REFERENCES Personal(matricula) ON DELETE CASCADE,
    FOREIGN KEY (id_periodo) REFERENCES PeriodosEscolares(id_periodo)
);

/* ==============================================================
   4. Tabla de Transacciones (Solicitud de Documentos)
   ==============================================================*/

-- Representa cada solicitud de documento
CREATE TABLE Documentos (
    folio VARCHAR(50) PRIMARY KEY,
    nombre_documento VARCHAR(255) NOT NULL,
    tipo_documento VARCHAR(100),
    estado ENUM('Pendiente', 'Completado', 'Rechazado', 'En Revisión') NOT NULL,
    fecha_solicitud DATETIME DEFAULT NOW(),
    fecha_completado DATETIME NULL,
    id_plantilla INT NOT NULL,
    matricula_docente_solicitante VARCHAR(20) NOT NULL,
    matricula_personal_firmante VARCHAR(20) NULL,
    
    -- ==============================================================
    -- NUEVO CAMBIO 2: Ruta al PDF generado
    -- ==============================================================
    ruta_pdf VARCHAR(512) NULL, -- Almacena la ruta al PDF final (ej. '/docs/gen/folio_abc.pdf')
    
    FOREIGN KEY (id_plantilla) REFERENCES PlantillasDocumentos(id_plantilla),
    FOREIGN KEY (matricula_docente_solicitante) REFERENCES Docentes(matricula_personal),
    FOREIGN KEY (matricula_personal_firmante) REFERENCES Personal(matricula)
);

/* ==============================================================
   5. Tablas de Convocatorias y Expedientes
   ==============================================================*/

-- Define los eventos de aplicación (ej. "Estímulos 2024")
CREATE TABLE Convocatoria (
    id_convocatoria INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(500) NOT NULL,
    id_periodo INT NOT NULL, -- Periodo que evalúa la convocatoria
    fecha_inicio_aplicacion DATE NOT NULL,
    fecha_fin_aplicacion DATE NOT NULL,
    
    FOREIGN KEY (id_periodo) REFERENCES PeriodosEscolares(id_periodo)
);

-- El "folder" o "aplicación" que un docente crea para una convocatoria
CREATE TABLE Expediente (
    id_expediente INT PRIMARY KEY AUTO_INCREMENT,
    matricula_docente VARCHAR(20) NOT NULL,
    id_convocatoria INT NOT NULL,
    fecha_creacion DATETIME DEFAULT NOW(),
    estado ENUM('En Preparacion', 'Enviado', 'Revisado', 'Aprobado', 'Rechazado') NOT NULL,
    
    FOREIGN KEY (matricula_docente) REFERENCES Docentes(matricula_personal),
    FOREIGN KEY (id_convocatoria) REFERENCES Convocatoria(id_convocatoria) ON DELETE CASCADE
);

-- El contenido de un expediente (documentos generados y subidos)
CREATE TABLE ExpedienteDocumentos (
    id_exp_doc INT PRIMARY KEY AUTO_INCREMENT,
    id_expediente INT NOT NULL,
    
    -- Para documentos generados por "Mi Espacio EDD"
    folio_documento VARCHAR(50) NULL,
    
    -- Para documentos subidos manualmente (ej. Título, CURP)
    nombre_documento_manual VARCHAR(255) NULL,
    tipo_documento_manual VARCHAR(100) NULL,
    
    -- ==============================================================
    -- CAMBIO ADICIONAL: Consistencia del Sistema (Opción 1)
    -- Se reemplaza LONGBLOB por una ruta, igual que en las otras tablas.
    -- ==============================================================
    ruta_archivo VARCHAR(512) NULL, -- Ruta al archivo subido (ej. '/uploads/exp1/curp.pdf')
    fecha_carga DATETIME DEFAULT NOW(),
    
    FOREIGN KEY (id_expediente) REFERENCES Expediente(id_expediente) ON DELETE CASCADE,
    FOREIGN KEY (folio_documento) REFERENCES Documentos(folio)
);

/*
-- No necesitas este ALTER si corres el script desde cero, 
-- pero lo dejo aquí si ya tenías la tabla 'Personal' creada
ALTER TABLE personal MODIFY password_hash VARCHAR(255) NOT NULL;
*/

SELECT 'Base de datos "Mi Espacio EDD" (versión MySQL v2) creada exitosamente.' AS Mensaje;