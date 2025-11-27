/*
================================================================
poblacion de datos version final
================================================================
*/

USE Mi_espacio_EDD;

-- =======================================================
-- 2. CATÁLOGOS BASE
-- =======================================================
INSERT INTO Departamentos (id_departamento, nombre_departamento) VALUES
(1, 'Sistemas y Computacion'), (2, 'Recursos Humanos'), (3, 'Ciencias Basicas'), (4, 'Ingenieria Industrial'),
(5, 'Servicios Escolares'), (6, 'Desarrollo Academico'), (7, 'Direccion');

INSERT INTO PeriodosEscolares (id_periodo, nombre_periodo, fecha_inicio, fecha_fin) VALUES
(1, 'AGO-DIC 2024', '2024-08-01', '2024-12-15'), (2, 'ENE-JUN 2025', '2025-01-15', '2025-06-30');

INSERT INTO Materias (clave_materia, nombre_materia, nivel) VALUES
('AEF-1050', 'Sistemas Operativos', 'LICENCIATURA'), ('SCD-1022', 'Taller de Bases de Datos', 'LICENCIATURA'),
('ACF-0901', 'Calculo Diferencial', 'LICENCIATURA'), ('MDC-1001', 'Mineria de Datos', 'MAESTRIA');

INSERT INTO Carreras (nombre_carrera, clave_carrera) VALUES 
('INGENIERÍA EN SISTEMAS COMPUTACIONALES', 'ISIC-2010-224'), ('INGENIERÍA INDUSTRIAL', 'IIND-2010-227');


-- =======================================================
-- 3. PLANTILLAS (CON HUECO DE FIRMA ESPECÍFICO Y DISEÑO CORREGIDO)
-- =======================================================
INSERT INTO PlantillasDocumentos (id_plantilla, nombre_plantilla, tipo_letra, tamaño_letra, margenes, formato, rol_firmante, tipo_plantilla, cuerpo) VALUES 
(1, 'Constancia de Servicios (RRHH)', 'Helvetica', 10, '15,15,15,15', 'Vertical', 'JEFE_RECURSOS_HUMANOS', 'SERVICIO','<style>.header-table td{vertical-align:middle}.title{font-weight:bold;font-size:11pt;text-align:right}.body-text{text-align:justify;line-height:1.5}.signature-table td{text-align:center;font-weight:bold;font-size:9pt}</style><table border="0" width="100%" class="header-table"><tr><td width="30%"><img src="archivos/recursos_graficos/logo_sep.png" height="45"></td><td width="20%"><img src="archivos/recursos_graficos/logo_tecnm.png" height="45"></td><td width="50%" align="right"><img src="archivos/recursos_graficos/logo_itc.png" height="50"><br><strong style="font-size:8pt">Instituto Tecnológico de Culiacán</strong></td></tr></table><br><br><div style="text-align:right;font-size:9pt"><strong>DEPTO. DE ADMON. DE REC. HUMANOS</strong><br>DARH-{{folio}}/{{anio_actual}}</div><br><br><div style="font-weight:bold;margin-bottom:10px">A QUIEN CORRESPONDA:</div><br><div class="body-text">La que suscribe, Jefa del Departamento de Administración de Recursos Humanos del Instituto Tecnológico de Culiacán, hace CONSTAR que el (la) <strong>C. {{nombre_completo}}</strong>, con filiación <strong>{{filiacion}}</strong>, presta sus servicios en este Instituto desde el <strong>{{fecha_ingreso}}</strong> a la fecha.<br><br>Tenía hasta el 1° de Enero de {{anio_actual}}, la(s) categoría(s) de <strong>{{categoria}}</strong>, con <strong>{{horas}} hrs.</strong>, con la clave presupuestal <strong>{{clave_presupuestal}}</strong>, con efectos desde el {{fecha_efectos}}, con estatus <strong>{{estatus_plaza}}</strong>.<br><br>No cuenta con ninguna sanción durante el período comprendido del 1° de Enero al 31 de Diciembre de {{anio_anterior}}, el (la) maestro (a) antes mencionado (a) cumplió con más del 90% de su jornada laboral y horario de trabajo.<br><br>Se extiende la presente constancia para los fines legales que al(a) interesado(a) convengan, en la ciudad de Culiacán, Sinaloa, a los {{dia_actual}} días del mes de {{mes_actual}} del año {{anio_actual}}.</div><br><br><br><br><div style="font-weight:bold;font-size:9pt">A T E N T A M E N T E<br><i style="font-size:8pt">Excelencia en Educación Tecnológica®</i></div><br><br><br><table width="100%" border="0"><tr><td width="50%">{{firma_jefe_rh}}<br>________________________________________<br><strong>{{nombre_jefe_rh}}</strong><br>JEFA DEL DEPARTAMENTO DE ADMINISTRACIÓN<br>DE RECURSOS HUMANOS</td><td width="50%"></td></tr></table>'),
(2, 'Constancia de Tutoría', 'Helvetica', 10, '15,15,15,15', 'Vertical', 'JEFE_DESARROLLO_ACADEMICO', 'TUTORIA', '<style>.tabla-tutorias{border-collapse:collapse;width:100%;margin-top:10px;margin-bottom:10px}.tabla-tutorias td,.tabla-tutorias th{border:1px solid black;padding:5px}</style><table border="0" width="100%"><tr><td width="30%"><img src="archivos/recursos_graficos/logo_sep.png" height="45"></td><td width="20%"><img src="archivos/recursos_graficos/logo_tecnm.png" height="45"></td><td width="50%" align="right"><img src="archivos/recursos_graficos/logo_itc.png" height="50"><br><strong style="font-size:8pt">Instituto Tecnológico de Culiacán</strong></td></tr></table><div style="text-align:right;font-size:9pt;margin-top:10px"><strong>ASUNTO:</strong> CONSTANCIA DE TUTORÍA</div><br><br><div style="font-weight:bold">H. COMISIÓN DICTAMINADORA DEL EDD<br>PRESENTE</div><br><div style="text-align:justify">Por medio de la presente se hace constar que al/a la <strong>C. {{nombre_completo}}</strong>, profesor/a del Depto. de {{nombre_departamento}} se le asignaron labores de tutoría durante los siguientes semestres y cantidades:<br><table class="tabla-tutorias"><tr><td>{{periodo_1}}</td><td>{{cantidad_1}} tutorados</td><td>{{carrera_1}}</td></tr><tr><td>{{periodo_2}}</td><td>{{cantidad_2}} tutorados</td><td>{{carrera_2}}</td></tr><tr><td><strong>TOTAL</strong></td><td><strong>{{total_tutorados}} tutorados</strong></td><td></td></tr></table><br>Además de que entregó en tiempo y forma el informe con el número de estudiantes atendidos por semestre y la evaluación del impacto en indicadores de eficiencia académica de la acción tutorial.<br><br>Se extiende la presente para los fines legales que convengan, en la ciudad de Culiacán, Sinaloa, el día {{fecha_texto_completa}}.</div><br><br><table width="100%" border="0"><tr><td width="50%" align="left"><strong>A T E N T A M E N T E</strong><br><i style="font-size:8pt">Excelencia en Educación Tecnológica®</i><br><br>{{firma_jefe_desarrollo}}<br>_________________________________<br><strong>{{nombre_jefe_desarrollo}}</strong><br>JEFA DEL DEPTO. DE DESARROLLO<br>ACADÉMICO</td><td width="50%" align="left"><strong>Vo. Bo.</strong><br><br><br>{{firma_subdirector}}<br>_________________________________<br><strong>{{nombre_subdirector}}</strong><br>RESPONSABLE DEL DESPACHO DE LA<br>SUBDIRECCIÓN ACADÉMICA</td></tr></table>'),
(3, 'Permiso Economico', 'Helvetica', 10, '15,15,15,15', 'Vertical', 'JEFE_AREA', 'ADMINISTRATIVO', '<style>.body-text{text-align:justify;line-height:1.5}</style><div style="text-align:right;font-size:10pt"><strong>ASUNTO:</strong> SOLICITUD DE PERMISO ECONÓMICO<br>Culiacán, Sin., a {{fecha_actual_texto}}</div><br><br><div style="font-weight:bold">{{nombre_jefe_depto}}<br>JEFE(A) DEL DEPARTAMENTO DE {{nombre_departamento}}<br>PRESENTE.</div><br><div class="body-text">Por medio del presente solicito a usted <strong>PERMISO ECONÓMICO</strong>, para ausentarme de mis labores el (los) día(s): <strong>{{dia_actual}} de {{mes_actual}} de {{anio}}</strong>.<br><br>Lo anterior por motivos de índole personal.<br><br>Sin más por el momento, reciba un cordial saludo.</div><br><br><br><div style="text-align:center;font-weight:bold">A T E N T A M E N T E<br><br>{{firma_docente}}<br>__________________________________<br>{{nombre_completo}}<br>R.F.C. {{rfc}}</div><br><br><hr><br><div style="font-size:8pt;text-align:center"><strong>AUTORIZACIÓN</strong><br><br>{{firma_jefe_rh}}<br>__________________________________<br>{{nombre_jefe_rh}}<br>JEFE(A) DE RECURSOS HUMANOS</div>'),
(4, 'Horario de Actividades', 'Helvetica', 7, '10,10,10,10', 'Horizontal', 'SUBDIRECTOR', 'HORARIO', '<style>.tabla-horario{width:100%;border-collapse:collapse;font-size:6pt}.tabla-horario td,.tabla-horario th{border:1px solid black;padding:2px;text-align:center}.titulo-seccion{background-color:#e0e0e0;font-weight:bold;text-align:left;padding-left:5px}.encabezado-tabla{text-align:left;font-size:7pt}</style><table width="100%" border="1" cellpadding="2" cellspacing="0"><tr><td width="15%" rowspan="3"><img src="archivos/recursos_graficos/logo_itc.png" height="40"></td><td width="55%" align="center" style="font-size:12pt;font-weight:bold">Formato para el Horario de Actividades</td><td width="30%">Código: SIG-CA-FE-06-01<br>Revisión: 2<br>Emisión: Junio de 2022</td></tr></table><br><table width="100%" class="tabla-horario"><tr><td align="left" colspan="4"><strong>NOMBRE COMPLETO:</strong> {{nombre_completo}}</td><td align="left" colspan="2"><strong>PERIODO ESCOLAR:</strong> {{periodo}}</td></tr><tr><td align="left" colspan="4"><strong>DEPARTAMENTO:</strong> {{nombre_departamento}}</td><td align="left" colspan="2"><strong>RFC:</strong> {{rfc}}</td></tr></table><br><div style="font-size:7pt;font-weight:bold">I. CARGA ACADÉMICA</div><table class="tabla-horario"><tr style="background-color:#ccc"><th width="20%">ASIGNATURA</th><th width="5%">GRUPO</th><th width="5%">AULA</th><th width="15%">CARRERA</th><th width="8%">LUNES</th><th width="8%">MARTES</th><th width="8%">MIERCOLES</th><th width="8%">JUEVES</th><th width="8%">VIERNES</th><th width="8%">SABADO</th><th width="7%">TOTAL HRS</th></tr>{{filas_horario_clases}}<tr><td colspan="4" align="right"><strong>PREPARACION, DOCENCIA Y EVALUACION</strong></td><td>09:00-10:00</td><td>09:00-10:00</td><td>09:00-10:00</td><td>09:00-10:00</td><td>09:00-10:00</td><td></td><td>{{horas_preparacion}}</td></tr></table><br><div style="font-size:7pt;font-weight:bold">II. ACTIVIDADES DE APOYO A LA DOCENCIA</div><table class="tabla-horario"><tr style="background-color:#ccc"><th width="40%">NOMBRE DE LA ACTIVIDAD</th><th width="8%">LUNES</th><th width="8%">MARTES</th><th width="8%">MIERCOLES</th><th width="8%">JUEVES</th><th width="8%">VIERNES</th><th width="8%">SABADO</th><th width="12%">TOTAL HRS</th></tr>{{filas_actividades_apoyo}}</table><br><br><br><table width="100%" border="0"><tr><td width="33%" align="center">{{firma_docente}}<br>_______________________<br>FIRMA DEL DOCENTE</td><td width="33%" align="center">{{firma_jefe}}<br>_______________________<br>{{nombre_jefe_depto}}<br>JEFE DE DEPTO</td><td width="33%" align="center">{{firma_subdirector}}<br>_______________________<br>{{nombre_subdirector}}<br>SUBDIRECCIÓN ACADÉMICA</td></tr></table>'),
(5, 'Carta de Recomendacion', 'Times', 11, '20,20,20,20', 'Vertical', 'DIRECTOR', 'GENERAL', '<style>.body-text{text-align:justify;line-height:1.8;font-family:Times New Roman,serif;font-size:12pt}</style><div style="text-align:right;font-family:Times New Roman,serif">Culiacán, Sinaloa, a {{fecha_actual_texto}}</div><br><br><div style="font-weight:bold;font-family:Times New Roman,serif">A QUIEN CORRESPONDA:</div><br><div class="body-text">Por medio de la presente, me permito recomendar ampliamente al C. <strong>{{nombre_completo}}</strong>, quien labora en esta institución adscrito al departamento de <strong>{{nombre_departamento}}</strong>.<br><br>Durante el tiempo que ha prestado sus servicios en este Instituto Tecnológico, ha demostrado ser una persona íntegra, responsable y competente en las actividades que se le han encomendado.<br><br>Se extiende la presente carta a petición del interesado para los fines legales que a él convengan.</div><br><br><br><div style="text-align:center;font-weight:bold;font-family:Times New Roman,serif">A T E N T A M E N T E<br><br>{{firma_director}}<br>__________________________________<br>{{nombre_director}}<br>DIRECTOR DEL INSTITUTO TECNOLÓGICO DE CULIACÁN</div>'),
(6, 'Constancia Académica (Comisión)', 'Helvetica', 10, '15,15,15,15', 'Vertical', 'JEFE_AREA', 'ACADEMICO', '<style>.body-text{text-align:justify;line-height:1.5}.tabla-datos{border-collapse:collapse;width:100%}.tabla-datos th,.tabla-datos td{border:1px solid black;padding:5px;font-size:9pt}.tabla-datos th{background-color:#f2f2f2}</style><table border="0" width="100%"><tr><td width="30%"><img src="archivos/recursos_graficos/logo_sep.png" height="45"></td><td width="20%"><img src="archivos/recursos_graficos/logo_tecnm.png" height="45"></td><td width="50%" align="right"><img src="archivos/recursos_graficos/logo_itc.png" height="50"><br><strong style="font-size:8pt">Instituto Tecnológico de Culiacán</strong></td></tr></table><br><div style="text-align:right;font-size:9pt">Culiacán, Sinaloa, {{fecha_actual}}<br>OFICIO No.: {{folio}}<br><strong>ASUNTO:</strong> Constancia.</div><br><div style="font-weight:bold;font-size:9pt">COMISIÓN DE EVALUACIÓN DEL<br>PROGRAMA DE ESTÍMULOS AL DESEMPEÑO DEL PERSONAL DOCENTE<br>PARA LOS INSTITUTOS TECNOLÓGICOS FEDERALES Y CENTROS.<br>PRESENTE.</div><br><div class="body-text">Por medio del presente se hace constar que la <strong>C. {{nombre_completo}}</strong>, {{descripcion_actividad}} durante el semestre <strong>{{periodo}}</strong>.<br><br>{{tabla_detalles_actividad}}<br><br>Se extiende la presente en la ciudad de Culiacán, Sinaloa, a los {{dia_actual_letra}} días del mes de {{mes_actual}} del año {{anio_actual_letra}}.</div><br><br><div style="font-weight:bold;font-size:9pt">A T E N T A M E N T E<br><i style="font-size:8pt">Excelencia en Educación Tecnológica®</i></div><br><br><table width="100%" border="0"><tr><td align="center">{{firma_jefe}}<br>________________________________________<br><strong>{{nombre_jefe_depto}}</strong><br>JEFA DEL DEPTO. DE {{nombre_departamento}}</td></tr></table><br><br><table width="100%" border="0"><tr><td width="50%" align="center" valign="top">{{firma_presidente}}<br>________________________________________<br><strong>{{nombre_presidente_academia}}</strong><br>PRESIDENTA DE ACADEMIA DE<br>{{nombre_academia}}</td><td width="50%" align="center" valign="top">{{firma_subdirector}}<br>________________________________________<br><strong>{{nombre_subdirector}}</strong><br>RESPONSABLE DEL DESPACHO DE LA<br>SUBDIRECCIÓN ACADÉMICA</td></tr></table>'),
(7, 'Constancia de Desarrollo Académico', 'Helvetica', 10, '15,15,15,15', 'Vertical', 'JEFE_DESARROLLO_ACADEMICO', 'DESARROLLO', '<style>.body-text{text-align:justify;line-height:1.5}</style><table border="0" width="100%"><tr><td width="30%"><img src="archivos/recursos_graficos/logo_sep.png" height="45"></td><td width="20%"><img src="archivos/recursos_graficos/logo_tecnm.png" height="45"></td><td width="50%" align="right"><img src="archivos/recursos_graficos/logo_itc.png" height="50"><br><strong style="font-size:8pt">Instituto Tecnológico de Culiacán</strong></td></tr></table><br><div style="text-align:right;font-size:9pt">Culiacán, Sinaloa, <strong>{{fecha_actual_completa}}</strong><br>DEPTO. DE DESARROLLO ACADÉMICO<br><strong>Núm. de Oficio:</strong> DDA-{{folio}}-{{anio}}</div><br><br><div style="font-weight:bold">H. COMISIÓN DICTAMINADORA DEL EDD<br>PRESENTE</div><div class="body-text">La que suscribe <strong>{{nombre_jefa_desarrollo}}</strong>, jefa del Departamento de Desarrollo Académico del Instituto Tecnológico de Culiacán.<br><div style="text-align:center;font-weight:bold;margin:20px 0;">HACE CONSTAR</div>Que el (la) <strong>C. {{nombre_completo}}</strong> cuenta con el registro <strong>{{registro_cvu}}</strong>, así como la actualización de su Currículum Vitae (CVU-TecNM) en el portal correspondiente al año {{anio}}, entregando en formato electrónico su CVU en extenso al Departamento de Desarrollo Académico.<br><br>A solicitud del interesado y para los fines que al mismo convenga se extiende la presente a los {{dia_letra}} días del mes de {{mes_letra}} del año {{anio_letra}}.</div><br><br><div style="font-weight:bold;font-size:9pt">A T E N T A M E N T E<br><i style="font-size:8pt">Excelencia en Educación Tecnológica®</i></div><br><br><br><table width="100%" border="0"><tr><td width="60%">{{firma_jefe_desarrollo}}<br>________________________________________<br><strong>{{nombre_jefa_desarrollo}}</strong><br>JEFA DEL DEPTO. DE DESARROLLO ACADÉMICO</td></tr></table><br><br><div style="font-size:7pt">C.c.p.- Archivo</div>'),
(8, 'Carta de Exclusividad Laboral', 'Times', 11, '20,20,20,20', 'Vertical', 'DOCENTE', 'EXCLUSIVIDAD', '<style>.body-text{text-align:justify;line-height:1.3}</style><div style="text-align:right;font-style:italic">(Culiacán Sinaloa a {{fecha_actual_texto}})</div><br><br><div style="text-align:center;font-weight:bold">CARTA DE EXCLUSIVIDAD LABORAL<br>DOCENTES CON PLAZA DE TIEMPO COMPLETO*</div><br><br><div class="body-text">El (La) que suscribe <strong>{{nombre_completo}}</strong>, con filiación: <strong>{{filiacion}}</strong>, Docente de tiempo completo, con clave presupuestal: <strong>{{clave_presupuestal}}</strong>, por medio de este documento manifiesto <strong>MI COMPROMISO</strong> con el Tecnológico Nacional de México, campus <strong>INSTITUTO TECNOLÓGICO DE CULIACÁN</strong> declaro que en caso de haber laborado en otra(s) institución(es) pública(s) o federal(es), la jornada no excedió las 12 horas-semana-mes durante el período a evaluar del estímulo.<br><br>Asimismo, manifiesto mi disposición para realizar las actividades propias de la Educación Superior Tecnológica enfocadas a satisfacer las necesidades de la dedicación, la calidad en el desempeño y permanencia en las actividades de la docencia.<br><br>En caso de que se me compruebe la <strong>NO EXCLUSIVIDAD LABORAL</strong>, me haré acreedor a la aplicación de las sanciones correspondientes de la normatividad vigente y perderé de manera permanente el derecho a participar en el Programa de Estímulos al Desempeño del Personal Docente.</div><br><br><br><div style="text-align:center;font-weight:bold">A T E N T A M E N T E<br><br><br><br>{{firma_docente}}<br>________________________________________<br>{{nombre_completo}}<br>Nombre y Firma del Docente</div><br><br><br><br><hr><div style="font-size:7pt">* Artículo 05 de los Lineamientos para la Operación del Programa de Estímulos al Desempeño del Personal Docente para los Institutos Tecnológicos Federales y Centros {{anio}}.</div>'),
(9, 'Acta de Exención de Examen Profesional', 'Helvetica', 10, '15,15,15,15', 'Vertical', 'PRESIDENTE_ACADEMIA', 'TITULACION', '<style>.linea-dato{border-bottom:1px solid black;display:inline-block;min-width:50px;text-align:center;font-weight:bold}</style><table border="0" width="100%"><tr><td width="20%"><img src="archivos/recursos_graficos/logo_tecnm_vertical.png" height="60"></td><td width="80%" align="right" style="font-weight:bold;font-size:14pt">INSTITUTO TECNOLÓGICO<br>DE CULIACÁN<br><span style="font-size:12pt">{{folio_acta}}</span></td></tr></table><br><br><div style="text-align:center;font-weight:bold;font-size:12pt">CONSTANCIA DE EXENCIÓN DE EXAMEN PROFESIONAL</div><br><br><div style="text-align:justify;line-height:1.8">De acuerdo con el instructivo vigente de Titulación, que no tiene como requisito la sustentación del Examen Profesional para efectos de obtención de Título, en las opciones VIII, IX y Titulación Integral, el jurado HACE CONSTAR que el (la) C. <span class="linea-dato" style="width:300px">{{nombre_alumno}}</span> número de control <span class="linea-dato" style="width:100px">{{numero_control}}</span> egresado (a) del Instituto Tecnológico de <span class="linea-dato" style="width:150px">Culiacán</span>, clave <span class="linea-dato">{{clave_escuela}}</span>, que cursó la carrera de <span class="linea-dato" style="width:350px">{{carrera_alumno}}</span>.<br><br>Cumplió satisfactoriamente con lo estipulado en la opción:<br><div style="border-bottom:1px solid black;width:100%">{{opcion_titulacion}}</div><div style="border-bottom:1px solid black;width:100%;margin-top:5px">{{nombre_proyecto}}</div><br><br>El (la) Presidente (a) del Jurado le hizo saber al sustentante el código de Ética Profesional y le tomó la Protesta de Ley. Se asienta la presente en la ciudad de Culiacán, Sinaloa, el día <u>{{dia}}</u> del mes de <u>{{mes}}</u> del año <u>{{anio}}</u>.</div><br><br><br><div style="text-align:center"><strong>PRESIDENTE (A)</strong><br><br>{{firma_presidente}}<br>_________________________________<br>{{nombre_presidente_jurado}}<br>Cédula Prof. {{cedula_presidente}}</div><br><br><table width="100%" border="0"><tr><td width="50%" align="center"><strong>SECRETARIO (A)</strong><br><br>{{firma_secretario}}<br>_______________________<br>{{nombre_secretario_jurado}}<br>Cédula Prof. {{cedula_secretario}}</td><td width="50%" align="center"><strong>VOCAL</strong><br><br>{{firma_vocal}}<br>_______________________<br>{{nombre_vocal_jurado}}<br>Cédula Prof. {{cedula_vocal}}</td></tr></table>'),
(10, 'Constancia Servicios Escolares', 'Helvetica', 10, '15,15,15,15', 'Vertical', 'JEFE_ESCOLARES', 'ESCOLARES', '<style>.tabla-materias{border-collapse:collapse;width:100%;font-size:8pt}.tabla-materias th,.tabla-materias td{border:1px solid black;padding:4px;text-align:center}.tabla-materias th{background-color:#f2f2f2;font-weight:bold}</style><table border="0" width="100%"><tr><td width="30%"><img src="archivos/recursos_graficos/logo_sep.png" height="45"></td><td width="20%"><img src="archivos/recursos_graficos/logo_tecnm.png" height="45"></td><td width="50%" align="right"><img src="archivos/recursos_graficos/logo_itc.png" height="50"><br><strong style="font-size:8pt">Instituto Tecnológico de Culiacán</strong></td></tr></table><br><div style="text-align:right;font-size:9pt"><strong>Depto. de Servicios Escolares</strong><br><strong>Asunto:</strong> Constancia.</div><br><div style="font-weight:bold;font-size:9pt">COMISIÓN DE EVALUACIÓN DEL TECNM<br>PROGRAMA DE ESTÍMULOS AL DESEMPEÑO DEL PERSONAL DOCENTE<br>P R E S E N T E.-</div><br><div style="text-align:justify">La que suscribe, hace constar que según registros que existen en el archivo escolar, la C. <strong>{{grado_academico}} {{nombre_completo}}</strong>, expediente <strong>{{matricula}}</strong> impartió las siguientes materias durante los Periodos {{periodos_evaluados}}:</div><br><table class="tabla-materias"><thead><tr><th width="20%">PERIODO</th><th width="15%">NIVEL</th><th width="15%">CLAVE</th><th width="40%">NOMBRE DE LA MATERIA</th><th width="10%">ALUMNOS</th></tr></thead><tbody>{{filas_materias_impartidas}}<tr><td colspan="4" align="right"><strong>Total</strong></td><td><strong>{{total_alumnos}}</strong></td></tr></tbody></table><br><div style="text-align:justify">Se extiende la presente, en la ciudad de Culiacán, Sinaloa, a los {{dia_letra}} días del mes de {{mes_letra}} de dos mil {{anio_letra}}, para los fines que más convengan al interesado.</div><br><br><br><div style="font-weight:bold;font-size:9pt">A T E N T A M E N T E<br><i style="font-size:8pt">Excelencia en Educación Tecnológica®</i></div><br><br><br><table width="100%" border="0"><tr><td>{{firma_jefe_escolares}}<br>________________________________________<br><strong>{{nombre_jefa_escolares}}</strong><br>JEFA DEL DEPTO. DE SERVICIOS ESCOLARES</td></tr></table>');

-- =======================================================
-- 4. PERSONAL Y ROLES (JERARQUÍA COMPLETA)
-- =======================================================
SET @pass = '$2y$10$VGOekOWSSGgbckkeTDwo3OkkSYwWuH/JkF1HdIED.a1wA/FSf/sVW'; -- 123456

-- DIRECTOR (100001)
INSERT INTO Personal (matricula, nombre, ap_paterno, ap_materno, correo, password_hash, tipo_personal, id_departamento, curp) VALUES
('100001', 'MIGUEL', 'DIAZ', 'HUERTA', 'director@tecnm.mx', @pass, 'DIRECTOR', 7, 'DIIM000000XXXXXX');
INSERT INTO Directores (matricula_personal) VALUES ('100001');

-- SUBDIRECTOR (200001)
INSERT INTO Personal (matricula, nombre, ap_paterno, ap_materno, correo, password_hash, tipo_personal, id_departamento, curp) VALUES
('200001', 'ROBERTO', 'GOMEZ', 'BOLAÑOS', 'subdir@tecnm.mx', @pass, 'SUBDIRECTOR', 7, 'GOMR000000XXXXXX');
INSERT INTO Subdirectores (matricula_personal) VALUES ('200001');

-- JEFA RH (700001) -> Para Constancia Servicio
INSERT INTO Personal (matricula, nombre, ap_paterno, ap_materno, correo, password_hash, tipo_personal, id_departamento, curp) VALUES
('700001', 'LAURA LILIANA', 'BARRAZA', 'CARDENAS', 'rh@tecnm.mx', @pass, 'JEFE_AREA', 2, 'BARL000000XXXXXX');
INSERT INTO JefesArea (matricula_personal, departamento_cargo) VALUES ('700001', 'Jefa de Recursos Humanos');

-- JEFA ESCOLARES (700002) -> Para Constancia Escolares
INSERT INTO Personal (matricula, nombre, ap_paterno, ap_materno, correo, password_hash, tipo_personal, id_departamento, curp) VALUES
('700002', 'DINORA', 'MEZA', 'GARCIA', 'escolares@tecnm.mx', @pass, 'JEFE_AREA', 5, 'MEGD000000XXXXXX');
INSERT INTO JefesArea (matricula_personal, departamento_cargo) VALUES ('700002', 'Jefa de Servicios Escolares');

-- JEFA DESARROLLO (700003) -> Para Constancia Desarrollo y Tutorías
INSERT INTO Personal (matricula, nombre, ap_paterno, ap_materno, correo, password_hash, tipo_personal, id_departamento, curp) VALUES
('700003', 'MARIA HIDAELIA', 'SANCHEZ', 'LOPEZ', 'desarrollo@tecnm.mx', @pass, 'JEFE_AREA', 6, 'SALM000000XXXXXX');
INSERT INTO JefesArea (matricula_personal, departamento_cargo) VALUES ('700003', 'Jefa de Desarrollo Academico');

-- JEFA CIENCIAS BÁSICAS (700004) -> Para Horario y Académica de Albert
INSERT INTO Personal (matricula, nombre, ap_paterno, ap_materno, correo, password_hash, tipo_personal, id_departamento, curp) VALUES
('700004', 'MARISOL', 'MANJARREZ', 'BELTRAN', 'basicas@tecnm.mx', @pass, 'JEFE_AREA', 3, 'MABM000000XXXXXX');
INSERT INTO JefesArea (matricula_personal, departamento_cargo) VALUES ('700004', 'Jefa de Ciencias Basicas');

-- DOCENTE: ALBERT EINSTEIN (300001)
INSERT INTO Personal (matricula, nombre, ap_paterno, ap_materno, correo, password_hash, tipo_personal, id_departamento, fecha_ingreso, curp) VALUES
('300001', 'ALBERT', 'EINSTEIN', 'ROSEN', 'albert@tecnm.mx', @pass, 'DOCENTE', 3, '2015-08-20', 'EIRA850303HXXXXX');
INSERT INTO Docentes (matricula_personal, grado_estudios, registro_cvu) VALUES ('300001', 'DOCTORADO EN FÍSICA', 'CVU-002');

-- OTROS DOCENTES (Para Jurado)
INSERT INTO Personal (matricula, nombre, ap_paterno, ap_materno, correo, password_hash, tipo_personal, id_departamento, curp) VALUES
('300002', 'ADA', 'LOVELACE', 'BYRON', 'ada@tecnm.mx', @pass, 'DOCENTE', 1, 'LOBA000000XXXXXX'),
('300003', 'NIKOLA', 'TESLA', 'SMILJAN', 'nikola@tecnm.mx', @pass, 'DOCENTE', 4, 'TESN000000XXXXXX');
INSERT INTO Docentes (matricula_personal, grado_estudios) VALUES ('300002', 'LICENCIATURA'), ('300003', 'MAESTRÍA');


-- =======================================================
-- 5. DATOS ACADÉMICOS PARA LAS PLANTILLAS
-- =======================================================

-- PLAZA
INSERT INTO Plazas (matricula_personal, categoria, horas, estatus, clave_presupuestal, fecha_efectos, es_actual) 
VALUES ('300001', 'TITULAR C', 40, 'ACTIVO', 'E3817-002', '2021-01-01', 1);

-- GRUPOS Y HORARIOS (Asegurando que la columna 'aula' y 'modalidad' existan si usaste el script de DB V3)
INSERT INTO Grupos (id_grupo, matricula_docente, clave_materia, id_periodo, alumnos_atendidos, aula, modalidad) 
VALUES (10, '300001', 'ACF-0901', 1, 40, 'B3', 'PRESENCIAL');
INSERT INTO Horarios (id_grupo, dia_semana, hora_inicio, hora_fin) VALUES (10, 1, '07:00:00', '09:00:00'), (10, 3, '07:00:00', '09:00:00');

INSERT INTO Grupos (id_grupo, matricula_docente, clave_materia, id_periodo, alumnos_atendidos, aula, modalidad) 
VALUES (11, '300001', 'MDC-1001', 1, 15, 'POS-1', 'VIRTUAL');
INSERT INTO Horarios (id_grupo, dia_semana, hora_inicio, hora_fin) VALUES (11, 2, '10:00:00', '12:00:00'), (11, 4, '10:00:00', '12:00:00');

-- TUTORÍAS
INSERT INTO GruposTutoria (matricula_docente, id_periodo, carrera, alumnos_atendidos) VALUES
('300001', 1, 'INGENIERÍA EN SISTEMAS', 25),
('300001', 1, 'INGENIERÍA INDUSTRIAL', 20);

-- ACTIVIDADES
INSERT INTO ActividadesDocente (matricula_docente, id_periodo, tipo_actividad, nombre_actividad, descripcion) VALUES
('300001', 1, 'Investigación', 'Proyecto IA', 'Desarrollo de algoritmos genéticos para optimización');

-- ACTA DE EXAMEN
INSERT INTO Alumnos (numero_control, nombre, ap_paterno, ap_materno, id_carrera) VALUES ('19170050', 'JUAN', 'PEREZ', 'LOPEZ', 1);
INSERT INTO ActasTitulacion (folio_acta, fecha_acto, opcion_titulacion, nombre_proyecto, numero_control_alumno, matricula_presidente, matricula_secretario, matricula_vocal) 
VALUES ('ACTA-001', '2025-06-13', 'TITULACIÓN INTEGRAL', 'SISTEMA WEB', '19170050', '300001', '300002', '300003');

-- CONVOCATORIAS
INSERT INTO Convocatoria (nombre, id_periodo, fecha_inicio_aplicacion, fecha_fin_aplicacion) VALUES
('Estímulos al Desempeño 2024', 1, '2024-11-01', '2024-11-30');

-- =======================================================
-- 0. PRE-REQUISITOS: INSERTAR MATERIAS FALTANTES
-- (Esto soluciona el Error 1452)
-- =======================================================
INSERT IGNORE INTO Materias (clave_materia, nombre_materia, nivel) VALUES
('ACF-0902', 'Calculo Integral', 'LICENCIATURA'),
('SCC-1012', 'Inteligencia Artificial', 'LICENCIATURA'),
('INC-1025', 'Ingenieria Economica', 'LICENCIATURA'),
('ACA-0907', 'Taller de Ética', 'LICENCIATURA');


-- =======================================================
-- DATOS PARA ADA LOVELACE (300002) - Depto. Sistemas
-- =======================================================

-- 1. PLAZA
INSERT INTO Plazas (matricula_personal, categoria, horas, estatus, clave_presupuestal, fecha_efectos, es_actual) 
VALUES ('300002', 'PROFESOR ASOCIADO B', 40, 'ACTIVO', 'E3815-005', '2022-01-16', 1);

-- 2. GRUPOS (Cálculo Integral e Inteligencia Artificial)
-- Ahora sí funcionará porque las materias ya existen
INSERT INTO Grupos (id_grupo, matricula_docente, clave_materia, id_periodo, alumnos_atendidos, aula, modalidad) 
VALUES 
(20, '300002', 'ACF-0902', 1, 35, 'CC1', 'PRESENCIAL'),
(21, '300002', 'SCC-1012', 1, 28, 'CC2', 'PRESENCIAL');

-- 3. HORARIOS (Lunes a Jueves)
-- Grupo 20 (Cálculo)
INSERT INTO Horarios (id_grupo, dia_semana, hora_inicio, hora_fin) VALUES 
(20, 1, '09:00:00', '11:00:00'), -- Lunes
(20, 3, '09:00:00', '11:00:00'); -- Miércoles
-- Grupo 21 (IA)
INSERT INTO Horarios (id_grupo, dia_semana, hora_inicio, hora_fin) VALUES 
(21, 2, '11:00:00', '13:00:00'), -- Martes
(21, 4, '11:00:00', '13:00:00'); -- Jueves

-- 4. TUTORÍAS
INSERT INTO GruposTutoria (matricula_docente, id_periodo, carrera, alumnos_atendidos) VALUES
('300002', 1, 'INGENIERÍA EN SISTEMAS', 30);

-- 5. ACTIVIDADES
INSERT INTO ActividadesDocente (matricula_docente, id_periodo, tipo_actividad, nombre_actividad, descripcion) VALUES
('300002', 1, 'Material Didáctico', 'Manual de Prácticas de IA', 'Elaboración de manual para laboratorio de cómputo');


-- =======================================================
-- DATOS PARA NIKOLA TESLA (300003) - Depto. Industrial
-- =======================================================

-- 1. PLAZA
INSERT INTO Plazas (matricula_personal, categoria, horas, estatus, clave_presupuestal, fecha_efectos, es_actual) 
VALUES ('300003', 'PROFESOR TITULAR A', 40, 'ACTIVO', 'E3817-008', '2023-05-01', 1);

-- 2. GRUPOS (Ingeniería Económica y Ética)
INSERT INTO Grupos (id_grupo, matricula_docente, clave_materia, id_periodo, alumnos_atendidos, aula, modalidad) 
VALUES 
(30, '300003', 'INC-1025', 1, 42, 'V1', 'VIRTUAL'),
(31, '300003', 'ACA-0907', 1, 40, 'D5', 'PRESENCIAL');

-- 3. HORARIOS (Viernes y Sábado)
-- Grupo 30 (Económica)
INSERT INTO Horarios (id_grupo, dia_semana, hora_inicio, hora_fin) VALUES 
(30, 5, '14:00:00', '16:00:00'); -- Viernes
-- Grupo 31 (Ética)
INSERT INTO Horarios (id_grupo, dia_semana, hora_inicio, hora_fin) VALUES 
(31, 6, '08:00:00', '12:00:00'); -- Sábado

-- 4. TUTORÍAS
INSERT INTO GruposTutoria (matricula_docente, id_periodo, carrera, alumnos_atendidos) VALUES
('300003', 1, 'INGENIERÍA INDUSTRIAL', 25),
('300003', 1, 'INGENIERÍA EN GESTIÓN', 15);

-- 5. ACTIVIDADES
INSERT INTO ActividadesDocente (matricula_docente, id_periodo, tipo_actividad, nombre_actividad, descripcion) VALUES
('300003', 1, 'Asesoría', 'Asesoría de Residencias', 'Revisión de anteproyectos de residencia profesional');


-- =======================================================
-- ACTA DE EXAMEN ADICIONAL (Donde Ada es Presidenta)
-- =======================================================
-- Alumno Nuevo
INSERT INTO Alumnos (numero_control, nombre, ap_paterno, ap_materno, id_carrera) 
VALUES ('20170099', 'MARIE', 'CURIE', 'SKLODOWSKA', 1);

-- Acta donde: Presidente=Ada, Secretario=Nikola, Vocal=Albert
INSERT INTO ActasTitulacion (folio_acta, fecha_acto, opcion_titulacion, nombre_proyecto, numero_control_alumno, matricula_presidente, matricula_secretario, matricula_vocal) 
VALUES ('ACTA-002', '2025-07-20', 'TESIS', 'REDES NEURONALES APLICADAS', '20170099', '300002', '300003', '300001');
SELECT 'poblacion de datos finalisada 100%' AS Estatus;