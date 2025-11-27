/*
================================================================
    SCRIPT COMPLETO: Base de Datos "Mi Espacio EDD" (v15)
    Motor: MySQL
    Fecha de actualización: Noviembre 2025
    Ultimo agregado: Incluye soporte para reportes y correcciones
================================================================
*/

-- Establecer el motor de almacenamiento y el charset por defecto 
SET default_storage_engine=InnoDB;
SET NAMES 'utf8mb4';

-- drop database Mi_espacio_EDD; 
Create database Mi_espacio_EDD;
use Mi_espacio_EDD;

/* --- 1. CATÁLOGOS --- */

CREATE TABLE Departamentos (
    id_departamento INT PRIMARY KEY AUTO_INCREMENT,
    nombre_departamento VARCHAR(255) NOT NULL UNIQUE
);

CREATE TABLE PeriodosEscolares (
    id_periodo INT PRIMARY KEY AUTO_INCREMENT,
    nombre_periodo VARCHAR(100) NOT NULL UNIQUE,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL
);

CREATE TABLE Materias (
    clave_materia VARCHAR(20) PRIMARY KEY,
    nombre_materia VARCHAR(255) NOT NULL,
    nivel VARCHAR(50) NOT NULL DEFAULT 'LICENCIATURA'
);

CREATE TABLE Carreras (
    id_carrera INT PRIMARY KEY AUTO_INCREMENT,
    nombre_carrera VARCHAR(255) NOT NULL UNIQUE,
    clave_carrera VARCHAR(20)
);

CREATE TABLE PlantillasDocumentos (
    id_plantilla INT PRIMARY KEY AUTO_INCREMENT,
    nombre_plantilla VARCHAR(255) NOT NULL,
    tipo_letra VARCHAR(50),
    tamaño_letra INT,
    margenes VARCHAR(100),
    formato VARCHAR(50),
    cuerpo LONGTEXT,
    rol_firmante VARCHAR(100),
    tipo_plantilla VARCHAR(50)
);

/* --- 2. PERSONAL Y ROLES --- */

CREATE TABLE Personal (
    matricula VARCHAR(20) PRIMARY KEY,
    curp VARCHAR(18) UNIQUE NOT NULL,
    filiacion VARCHAR(50) NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    ap_paterno VARCHAR(100) NOT NULL,
    ap_materno VARCHAR(100),
    correo VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    tipo_personal ENUM('DIRECTOR', 'SUBDIRECTOR', 'JEFE_AREA', 'DOCENTE') NOT NULL,
    fecha_ingreso DATE,
    id_departamento INT,
    ruta_firma_qr VARCHAR(512) NULL,
    FOREIGN KEY (id_departamento) REFERENCES Departamentos(id_departamento)
);

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
    departamento_cargo VARCHAR(100) NOT NULL,
    FOREIGN KEY (matricula_personal) REFERENCES Personal(matricula) ON DELETE CASCADE
);

CREATE TABLE Docentes (
    matricula_personal VARCHAR(20) PRIMARY KEY,
    grado_estudios VARCHAR(100),
    tipo_grado_docente VARCHAR(100),
    registro_cvu VARCHAR(50) NULL, -- NUEVO: Para constancia de Desarrollo Académico
    FOREIGN KEY (matricula_personal) REFERENCES Personal(matricula) ON DELETE CASCADE
);

CREATE TABLE Alumnos (
    numero_control VARCHAR(20) PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    ap_paterno VARCHAR(100) NOT NULL,
    ap_materno VARCHAR(100),
    curp VARCHAR(18),
    id_carrera INT NOT NULL,
    FOREIGN KEY (id_carrera) REFERENCES Carreras(id_carrera)
);

/* --- 3. DATOS ACADÉMICOS Y LABORALES --- */

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
    aula VARCHAR(20) NULL,       -- NUEVO: Para el Horario
    modalidad VARCHAR(20) DEFAULT 'PRESENCIAL', -- NUEVO: Para el Horario
    FOREIGN KEY (matricula_docente) REFERENCES Docentes(matricula_personal),
    FOREIGN KEY (clave_materia) REFERENCES Materias(clave_materia),
    FOREIGN KEY (id_periodo) REFERENCES PeriodosEscolares(id_periodo)
);

CREATE TABLE Horarios (
    id_horario INT PRIMARY KEY AUTO_INCREMENT,
    id_grupo INT NOT NULL,
    dia_semana TINYINT NOT NULL,
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
    numero_dictamen VARCHAR(100) NULL,
    FOREIGN KEY (matricula_docente) REFERENCES Docentes(matricula_personal),
    FOREIGN KEY (id_periodo) REFERENCES PeriodosEscolares(id_periodo)
);

CREATE TABLE ActasTitulacion (
    id_acta INT PRIMARY KEY AUTO_INCREMENT,
    folio_acta VARCHAR(50) UNIQUE NOT NULL,
    fecha_acto DATE NOT NULL,
    opcion_titulacion VARCHAR(255) NOT NULL,
    nombre_proyecto VARCHAR(500) NULL,
    numero_control_alumno VARCHAR(20) NOT NULL,
    matricula_presidente VARCHAR(20) NOT NULL,
    matricula_secretario VARCHAR(20) NOT NULL,
    matricula_vocal VARCHAR(20) NOT NULL,
    FOREIGN KEY (numero_control_alumno) REFERENCES Alumnos(numero_control),
    FOREIGN KEY (matricula_presidente) REFERENCES Docentes(matricula_personal),
    FOREIGN KEY (matricula_secretario) REFERENCES Docentes(matricula_personal),
    FOREIGN KEY (matricula_vocal) REFERENCES Docentes(matricula_personal)
);

CREATE TABLE RegistrosAnualesHR (
    id_registro INT PRIMARY KEY AUTO_INCREMENT,
    matricula_personal VARCHAR(20) NOT NULL,
    id_periodo INT NOT NULL,
    sanciones LONGTEXT NULL,
    cumplimiento_jornada DECIMAL(5, 2) NOT NULL,
    FOREIGN KEY (matricula_personal) REFERENCES Personal(matricula) ON DELETE CASCADE,
    FOREIGN KEY (id_periodo) REFERENCES PeriodosEscolares(id_periodo)
);

/* --- 4. DOCUMENTOS Y TRÁMITES --- */

CREATE TABLE Documentos (
    folio VARCHAR(50) PRIMARY KEY,
    nombre_documento VARCHAR(255) NOT NULL,
    tipo_documento VARCHAR(100),
    estado ENUM('Pendiente', 'Completado', 'Rechazado', 'En Revisión', 'Reportado', 'Corregido') NOT NULL,
    fecha_solicitud DATETIME DEFAULT NOW(),
    fecha_completado DATETIME NULL,
    mensaje_docente TEXT NULL,
    mensaje_admin TEXT NULL,
    id_plantilla INT NOT NULL,
    matricula_docente_solicitante VARCHAR(20) NOT NULL,
    matricula_personal_firmante VARCHAR(20) NULL,
    ruta_pdf VARCHAR(512) NULL,
    FOREIGN KEY (id_plantilla) REFERENCES PlantillasDocumentos(id_plantilla),
    FOREIGN KEY (matricula_docente_solicitante) REFERENCES Docentes(matricula_personal),
    FOREIGN KEY (matricula_personal_firmante) REFERENCES Personal(matricula)
);

CREATE TABLE Convocatoria (
    id_convocatoria INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(500) NOT NULL,
    id_periodo INT NOT NULL,
    fecha_inicio_aplicacion DATE NOT NULL,
    fecha_fin_aplicacion DATE NOT NULL,
    FOREIGN KEY (id_periodo) REFERENCES PeriodosEscolares(id_periodo)
);

CREATE TABLE Expediente (
    id_expediente INT PRIMARY KEY AUTO_INCREMENT,
    matricula_docente VARCHAR(20) NOT NULL,
    id_convocatoria INT NOT NULL,
    fecha_creacion DATETIME DEFAULT NOW(),
    estado ENUM('En Preparacion', 'Enviado', 'Revisado', 'Aprobado', 'Rechazado') NOT NULL,
    FOREIGN KEY (matricula_docente) REFERENCES Docentes(matricula_personal),
    FOREIGN KEY (id_convocatoria) REFERENCES Convocatoria(id_convocatoria) ON DELETE CASCADE
);

CREATE TABLE ExpedienteDocumentos (
    id_exp_doc INT PRIMARY KEY AUTO_INCREMENT,
    id_expediente INT NOT NULL,
    folio_documento VARCHAR(50) NULL,
    nombre_documento_manual VARCHAR(255) NULL,
    tipo_documento_manual VARCHAR(100) NULL,
    ruta_archivo VARCHAR(512) NULL,
    fecha_carga DATETIME DEFAULT NOW(),
    FOREIGN KEY (id_expediente) REFERENCES Expediente(id_expediente) ON DELETE CASCADE,
    FOREIGN KEY (folio_documento) REFERENCES Documentos(folio)
);

-- Tabla para almacenar múltiples firmas por documento
CREATE TABLE FirmasDocumento (
    id_firma INT PRIMARY KEY AUTO_INCREMENT,
    folio_documento VARCHAR(50) NOT NULL,
    matricula_firmante VARCHAR(20) NOT NULL,
    rol_firmante_en_acto VARCHAR(50) NOT NULL, -- Ej: 'DOCENTE', 'JEFE', 'DIRECTOR'
    fecha_firma DATETIME DEFAULT NOW(),
    token_firma VARCHAR(100) NOT NULL, -- Hash único de esa firma
    ruta_qr_snapshot VARCHAR(512) NOT NULL, -- Guardamos qué QR se usó
    
    FOREIGN KEY (folio_documento) REFERENCES Documentos(folio) ON DELETE CASCADE,
    FOREIGN KEY (matricula_firmante) REFERENCES Personal(matricula)
);

-- Modificar Documentos para saber que está "En Proceso de Firmas"
ALTER TABLE Documentos MODIFY COLUMN estado ENUM('Pendiente', 'En Proceso', 'Completado', 'Rechazado', 'En Revisión', 'Reportado', 'Corregido') NOT NULL;

SELECT 'Base de datos creada correctamente con todos los atributos al 100%.' AS Estatus;