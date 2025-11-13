-- Seleccionamos la base de datos sobre la que vamos a trabajar
USE Mi_espacio_EDD;

/*
================================================================
   NIVEL 0: Tablas de Catálogos (Sin dependencias)
================================================================
*/

-- Insertar Departamentos
INSERT INTO Departamentos (nombre_departamento) VALUES
('Sistemas y Computacion'),
('Recursos Humanos');

-- Insertar Periodos Escolares
INSERT INTO PeriodosEscolares (nombre_periodo, fecha_inicio, fecha_fin) VALUES
('AGO-DIC 2024', '2024-08-01', '2024-12-15'),
('ENE-JUN 2025', '2025-01-15', '2025-06-30');

-- Insertar Materias
INSERT INTO Materias (clave_materia, nombre_materia, nivel) VALUES
('AEF-1050', 'Sistemas Operativos', 'LICENCIATURA'),
('SCD-1022', 'Taller de Bases de Datos', 'LICENCIATURA');

-- Insertar Plantillas de Documentos
INSERT INTO PlantillasDocumentos (nombre_plantilla, cuerpo, rol_firmante, tipo_plantilla) VALUES
('Constancia de Servicio', '<h1>Constancia de Servicio</h1><p>El/La C. <strong>{{nombre_completo}}</strong>, con matrícula <strong>{{matricula}}</strong>, labora en esta institución...</p>', 'DIRECTOR', 'SERVICIO'),
('Constancia de Tutoría', '<h1>Constancia de Tutoría</h1><p>El docente <strong>{{nombre_completo}}</strong> fue tutor del grupo <strong>{{grupo_tutoria}}</strong> durante el período <strong>{{periodo}}</strong>...</p>', 'JEFE_AREA', 'TUTORIA');

/*
================================================================
   NIVEL 1: Tablas de Personal y Convocatorias
   (Dependen del Nivel 0)
================================================================
*/

-- Hash de la contraseña "123456" (el que generamos con crear_hash.php)
SET @hash_pass = '$2y$10$TKh8H1.PfQx37YgM/bJq.e.fnvT0a1.d2U.NlT8b.jL.O/v.2m';

-- Insertar Personal (Docente, Jefe de Área y Director)
INSERT INTO Personal (matricula, curp, nombre, ap_paterno, ap_materno, correo, password_hash, tipo_personal, fecha_ingreso, id_departamento, ruta_firma_qr) VALUES
('123456', 'CAZFJ850101HXXXXXX', 'Francisco Javier', 'Cazarez', 'Ibarra', 'fco@tecnm.mx', @hash_pass, 'DOCENTE', '2010-08-20', 1, 'archivos/qr_firmas/qr_123456.png'),
('789012', 'LOGA800202MXXXXXX', 'Ana', 'Lopez', 'Garcia', 'ana.lg@tecnm.mx', @hash_pass, 'JEFE_AREA', '2005-09-01', 1, 'archivos/qr_firmas/qr_789012.png'),
('100001', 'DIIM900303HXXXXXX', 'Miguel', 'Diaz', 'Huerta', 'miguel.dh@tecnm.mx', @hash_pass, 'DIRECTOR', '2001-01-10', 2, 'archivos/qr_firmas/qr_100001.png');

-- Insertar Convocatorias
INSERT INTO Convocatoria (nombre, id_periodo, fecha_inicio_aplicacion, fecha_fin_aplicacion) VALUES
('Programa de Estímulos 2024', 1, '2024-11-01', '2024-11-30');

/*
================================================================
   NIVEL 2: Sub-tablas de Personal y Datos de RRHH
   (Dependen del Nivel 1)
================================================================
*/

-- Poblar las tablas de roles
INSERT INTO Docentes (matricula_personal, grado_estudios) VALUES
('123456', 'Maestría en Sistemas Computacionales');

INSERT INTO JefesArea (matricula_personal, departamento_cargo) VALUES
('789012', 'Jefa del Depto. de Sistemas y Computación');

INSERT INTO Directores (matricula_personal) VALUES
('100001');

-- Insertar Plazas (para el docente '123456')
INSERT INTO Plazas (matricula_personal, categoria, horas, estatus, clave_presupuestal, fecha_efectos, es_actual) VALUES
('123456', 'Profesor de Carrera Asociado C', 40, 'ACTIVO', 'P0012345', '2015-01-16', 1);

-- Insertar Registros de RRHH (para el docente '123456')
INSERT INTO RegistrosAnualesHR (matricula_personal, id_periodo, sanciones, cumplimiento_jornada) VALUES
('123456', 1, 'No cuenta con ninguna sanción durante el período.', 100.00);


/*
================================================================
   NIVEL 3: Tablas de Datos del Docente y Expedientes
   (Dependen de los niveles anteriores)
================================================================
*/

-- Insertar Grupos (clases que da el docente '123456')
INSERT INTO Grupos (matricula_docente, clave_materia, id_periodo, alumnos_atendidos) VALUES
('123456', 'AEF-1050', 1, 35),
('123456', 'SCD-1022', 1, 30);

-- Insertar Tutorías (del docente '123456')
INSERT INTO GruposTutoria (matricula_docente, id_periodo, carrera, alumnos_atendidos) VALUES
('123456', 1, 'Ingenieria en Sistemas Computacionales', 28);

-- Insertar Actividades (del docente '123456')
INSERT INTO ActividadesDocente (matricula_docente, id_periodo, tipo_actividad, nombre_actividad) VALUES
('123456', 1, 'Curso Taller', 'Impartición del Taller de Docker y Contenedores');

-- Insertar un Expediente (para el docente '123456' en la convocatoria '1')
INSERT INTO Expediente (matricula_docente, id_convocatoria, estado) VALUES
('123456', 1, 'En Preparacion');

-- Insertar una Solicitud de Documento
INSERT INTO Documentos (folio, nombre_documento, tipo_documento, estado, id_plantilla, matricula_docente_solicitante, matricula_personal_firmante, ruta_pdf) VALUES
('FOLIO-CS-001', 'Constancia de Servicio', 'Constancia', 'Completado', 1, '123456', '100001', 'archivos/docs_generados/folio-cs-001.pdf');


/*
================================================================
   NIVEL 4: Tablas de Detalle (Finales)
   (Dependen del Nivel 3)
================================================================
*/

-- Insertar Horarios (para los grupos '1' y '2')
INSERT INTO Horarios (id_grupo, dia_semana, hora_inicio, hora_fin) VALUES
(1, 1, '09:00:00', '11:00:00'), -- Lunes 9-11
(1, 3, '09:00:00', '11:00:00'), -- Miércoles 9-11
(2, 2, '13:00:00', '15:00:00'), -- Martes 1-3
(2, 4, '13:00:00', '15:00:00'); -- Jueves 1-3

-- Insertar Documentos en el Expediente (expediente '1')
INSERT INTO ExpedienteDocumentos (id_expediente, folio_documento, nombre_documento_manual, tipo_documento_manual, ruta_archivo) VALUES
(1, 'FOLIO-CS-001', NULL, NULL, NULL), -- Documento generado por el sistema
(1, NULL, 'Copia de Título de Maestría', 'Grado Académico', 'archivos/uploads_exp/exp_1/titulo_maestria.pdf'), -- Documento subido
(1, NULL, 'CURP Actualizado 2024', 'Identificación', 'archivos/uploads_exp/exp_1/curp_2024.pdf'); -- Documento subido

SELECT 'Script de inserción completado con éxito.' AS Mensaje;