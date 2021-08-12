<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/6.11.0/sweetalert2.css"/>
<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/6.11.0/sweetalert2.js"></script>

<?php
require_once(__DIR__ . '/../../config.php');

require_once('Xportxls.php');
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

global $DB;
$id = $_GET['id'];

//Consulta #1 Elearning
$sql=("select @s:=@s + 1 id_auto, 
DATE_FORMAT(DATE_ADD(FROM_UNIXTIME(c.startdate, '%Y-%m-%d'), INTERVAL 1 HOUR),'%d/%m/%Y') AS fecha_activado,
codigo.codigo as codigo_curso,
c.fullname as curso,
if(modalidad.modalidad is null, 'Sin dato', if(modalidad.modalidad = '', 'Sin dato', modalidad.modalidad))as modalidad,
if(aprendizaje.aprendizaje is null, 'Sin dato', if(aprendizaje.aprendizaje = '', 'Sin dato', aprendizaje.aprendizaje))  as agenda_de_aprendizaje,
if(tipocurso.tipocurso is null, 'Sin dato', if(tipocurso.tipocurso = '', 'Sin dato', tipocurso.tipocurso)) as tipo_de_curso,
if(dirigido.dirigido is null, 'Sin dato', if(dirigido.dirigido = '', 'Sin dato', dirigido.dirigido)) as segmento_dirigido,
if(responsable.nombre is null, 'Sin dato', if(responsable.nombre = '', 'Sin dato', responsable.nombre)) as responsableCOE,
if(unidad.unidad is null, 'Sin dato', if(unidad.unidad = '', 'Sin dato', unidad.unidad)) as unidad_de_negocio,
if(poblacion.poblacion is null, 'Sin dato', if(poblacion.poblacion = '', 'Sin dato', poblacion.poblacion )) as poblacion_dirigida,
if(pais.pais is null, 'Sin dato', if(pais.pais = '', 'Sin dato', pais.pais)) as pais,
if(concat(u.firstname,' ',u.lastname) is null, 'Sin dato', concat(u.firstname,' ',u.lastname)) as facilitador,
if(matriculados.conteo is null, '0.0', matriculados.conteo) as inscritos,
asistencia.total as asistencia,
format((asistencia.total*100)/matriculados.conteo,2) as porcentaje_asistencia, 
tipoc.tipoc as tipo_de_encuesta, 
CASE when epromedio.promedio is null THEN '0.0' else epromedio.promedio END as epromedio,
if(pregunta1.promedio is null, '.0', pregunta1.promedio) as E_pregunta1,
if(pregunta2.promedio is null, '.0', pregunta2.promedio) as E_pregunta2,
if(pregunta3.promedio is null, '.0', pregunta3.promedio) as E_pregunta3,
if(pregunta4.promedio is null, '.0', pregunta4.promedio) as E_pregunta4,
if(pregunta5.promedio is null, '.0', pregunta5.promedio) as E_pregunta5
from (select @s:=0) as s, mdl_course c
INNER JOIN mdl_context con on con.instanceid = c.id
INNER JOIN mdl_role_assignments as asg on asg.contextid = con.id
INNER JOIN mdl_user as u on asg.userid = u.id
INNER JOIN mdl_role r on asg.roleid = r.id
LEFT JOIN
        (SELECT cd.value AS codigo, cd.fieldid, cd.instanceid
        FROM mdl_course c
        INNER JOIN mdl_customfield_data cd
        ON c.id=cd.instanceid
        INNER JOIN mdl_customfield_field cf
        ON cf.id=cd.fieldid
        WHERE cf.shortname='codigo') codigo
ON codigo.instanceid=c.id
LEFT JOIN
        (SELECT cd.fieldid, cd.instanceid,(CASE WHEN cd.value = 0 THEN 'Sin dato'
        WHEN cd.value = 1 THEN 'No aplica'
        WHEN cd.value = 2 THEN 'Virtual'
        WHEN cd.value = 3 THEN 'Presencial'
        WHEN cd.value = 4 THEN 'Blended'
        end) as modalidad
        FROM mdl_course c
        INNER JOIN mdl_customfield_data cd
        ON c.id=cd.instanceid
        INNER JOIN mdl_customfield_field cf
        ON cf.id=cd.fieldid
        WHERE cf.shortname='modalidad_curso') modalidad
ON modalidad.instanceid=c.id
LEFT JOIN
		(SELECT cd.fieldid, cd.instanceid,(CASE WHEN cd.value = 0 THEN 'Sin dato'
        WHEN cd.value = 1 THEN 'No aplica'
        WHEN cd.value = 2 THEN 'eLearning'
        WHEN cd.value = 3 THEN 'Webinar'
        WHEN cd.value = 4 THEN 'Presencial'
        end) as tipoc
        FROM mdl_course c
        INNER JOIN mdl_customfield_data cd
        ON c.id=cd.instanceid
        INNER JOIN mdl_customfield_field cf
        ON cf.id=cd.fieldid
        WHERE cf.shortname='tipo_de_encuesta') tipoc
ON tipoc.instanceid=c.id
LEFT JOIN
        (SELECT cd.value AS codigo, cd.fieldid, cd.instanceid,(CASE WHEN cd.value = 0 THEN 'Sin dato'
        WHEN cd.value = 1 THEN 'No aplica'
        WHEN cd.value = 2 THEN 'Mandatario'
        WHEN cd.value = 3 THEN 'Libre'
        end) as aprendizaje
        FROM mdl_course c
        INNER JOIN mdl_customfield_data cd
        ON c.id=cd.instanceid
        INNER JOIN mdl_customfield_field cf
        ON cf.id=cd.fieldid
        WHERE cf.shortname='agenda_aprendizaje') aprendizaje
ON aprendizaje.instanceid=c.id
LEFT JOIN
        (SELECT cd.value AS codigo, cd.fieldid, cd.instanceid,(CASE WHEN cd.value = 0 THEN 'Sin dato'
        WHEN cd.value = 1 THEN 'No aplica'
        WHEN cd.value = 2 THEN 'Liderazgo'
        WHEN cd.value = 3 THEN 'Escuela comercial'
        WHEN cd.value = 4 THEN 'Escuela técnica'
        WHEN cd.value = 5 THEN 'Estrategia'
        WHEN cd.value = 6 THEN 'Financiero'
        end) as tipocurso
        FROM mdl_course c
        INNER JOIN mdl_customfield_data cd
        ON c.id=cd.instanceid
        INNER JOIN mdl_customfield_field cf
        ON cf.id=cd.fieldid
        WHERE cf.shortname='tipo_curso') tipocurso
ON tipocurso.instanceid=c.id
LEFT JOIN
        (SELECT cd.value AS codigo, cd.fieldid, cd.instanceid,(CASE WHEN cd.value = 0 THEN 'Sin dato'
        WHEN cd.value = 1 THEN 'No aplica'
        WHEN cd.value = 2 THEN 'Transversal'
        WHEN cd.value = 3 THEN 'Vertical'
        end) as dirigido
        FROM mdl_course c
        INNER JOIN mdl_customfield_data cd
        ON c.id=cd.instanceid
        INNER JOIN mdl_customfield_field cf
        ON cf.id=cd.fieldid
        WHERE cf.shortname='segmento_dirigido') dirigido
ON dirigido.instanceid=c.id
LEFT JOIN
        (SELECT cd.value AS codigo, cd.fieldid, cd.instanceid,(CASE WHEN cd.value = 0 THEN 'Sin dato'
        WHEN cd.value = 1 THEN 'No aplica'
        WHEN cd.value = 2 THEN 'ALIMENTOS'
        WHEN cd.value = 3 THEN 'Rca'
        WHEN cd.value = 4 THEN 'Ip'
        WHEN cd.value = 5 THEN 'Cusa'
        WHEN cd.value = 6 THEN 'A&C'
        WHEN cd.value = 7 THEN 'Harinas'
        WHEN cd.value = 8 THEN 'B2B'
        WHEN cd.value = 9 THEN 'ABA'
        end) as unidad
        FROM mdl_course c
        INNER JOIN mdl_customfield_data cd
        ON c.id=cd.instanceid
        INNER JOIN mdl_customfield_field cf
        ON cf.id=cd.fieldid
        WHERE cf.shortname='unidades_negocio') unidad
ON unidad.instanceid=c.id
LEFT JOIN
        (SELECT cd.value AS codigo, cd.fieldid, cd.instanceid,(CASE WHEN cd.value = 0 THEN 'Sin dato'
        WHEN cd.value = 1 THEN 'No aplica'
        WHEN cd.value = 2 THEN 'Ejecutivo directivo'
        WHEN cd.value = 3 THEN 'Gerente'
        WHEN cd.value = 4 THEN 'Jefatura'
        WHEN cd.value = 5 THEN 'Administrativo'
        WHEN cd.value = 6 THEN 'Operativo'
        end) as poblacion
        FROM mdl_course c
        INNER JOIN mdl_customfield_data cd
        ON c.id=cd.instanceid
        INNER JOIN mdl_customfield_field cf
        ON cf.id=cd.fieldid
        WHERE cf.shortname='poblacion_dirigida') poblacion
ON poblacion.instanceid=c.id
LEFT JOIN
        (SELECT cd.value AS codigo, cd.fieldid, cd.instanceid,(CASE WHEN cd.value = 0 THEN 'Sin dato'
		WHEN cd.value = 1 THEN 'No aplica'
        WHEN cd.value = 2 THEN 'Afganistán'
        WHEN cd.value = 3 THEN 'Albania'
        WHEN cd.value = 4 THEN 'Alemania'
        WHEN cd.value = 5 THEN 'Andorra'
        WHEN cd.value = 6 THEN 'Angola'
        WHEN cd.value = 7 THEN 'Antigua y Barbuda'
        WHEN cd.value = 8 THEN 'Arabia Saudita'
        WHEN cd.value = 9 THEN 'Argelia'
        WHEN cd.value = 10 THEN 'Argentina'
        WHEN cd.value = 11 THEN 'Armenia'
        WHEN cd.value = 12 THEN 'Australia'
        WHEN cd.value = 13 THEN 'Austria'
        WHEN cd.value = 14 THEN 'Azerbaiyán'
        WHEN cd.value = 15 THEN 'Bahamas'
        WHEN cd.value = 16 THEN 'Bangladés'
        WHEN cd.value = 17 THEN 'Barbados'
        WHEN cd.value = 18 THEN 'Baréin'
        WHEN cd.value = 19 THEN 'Bélgica'
        WHEN cd.value = 20 THEN 'Belice'
        WHEN cd.value = 21 THEN 'Benín'
        WHEN cd.value = 22 THEN 'Bielorrusia'
        WHEN cd.value = 23 THEN 'Birmania'
        WHEN cd.value = 24 THEN 'Bolivia'
        WHEN cd.value = 25 THEN 'Bosnia y Herzegovina'
        WHEN cd.value = 26 THEN 'Botsuana'
        WHEN cd.value = 27 THEN 'Brasil'
        WHEN cd.value = 28 THEN 'Brunéi'
        WHEN cd.value = 29 THEN 'Bulgaria'
        WHEN cd.value = 30 THEN 'Burkina Faso'
        WHEN cd.value = 31 THEN 'Burundi'
        WHEN cd.value = 32 THEN 'Bután'
        WHEN cd.value = 33 THEN 'Cabo Verde'
        WHEN cd.value = 34 THEN 'Camboya'
        WHEN cd.value = 35 THEN 'Camerún'
        WHEN cd.value = 36 THEN 'Canadá'
        WHEN cd.value = 37 THEN 'Catar'
        WHEN cd.value = 38 THEN 'Chad'
        WHEN cd.value = 39 THEN 'Chile'
        WHEN cd.value = 40 THEN 'China'
        WHEN cd.value = 41 THEN 'Chipre'
        WHEN cd.value = 42 THEN 'Ciudad del Vaticano'
        WHEN cd.value = 43 THEN 'Colombia'
        WHEN cd.value = 44 THEN 'Comoras'
        WHEN cd.value = 45 THEN 'Corea del Norte'
        WHEN cd.value = 46 THEN 'Corea del Sur'
        WHEN cd.value = 47 THEN 'Costa de Marfil'
        WHEN cd.value = 48 THEN 'Costa Rica'
        WHEN cd.value = 49 THEN 'Croacia'
        WHEN cd.value = 50 THEN 'Cuba'
        WHEN cd.value = 51 THEN 'Dinamarca'
        WHEN cd.value = 52 THEN 'Dominica'
        WHEN cd.value = 53 THEN 'Ecuador'
        WHEN cd.value = 54 THEN 'Egipto'
        WHEN cd.value = 55 THEN 'El Salvador'
        WHEN cd.value = 56 THEN 'Emiratos Árabes Unidos'
        WHEN cd.value = 57 THEN 'Eritrea'
        WHEN cd.value = 58 THEN 'Eslovaquia'
        WHEN cd.value = 59 THEN 'Eslovenia'
        WHEN cd.value = 60 THEN 'España'
        WHEN cd.value = 61 THEN 'Estados Unidos'
        WHEN cd.value = 62 THEN 'Estonia'
        WHEN cd.value = 63 THEN 'Etiopía'
        WHEN cd.value = 64 THEN 'Filipinas'
        WHEN cd.value = 65 THEN 'Finlandia'
        WHEN cd.value = 66 THEN 'Fiyi'
        WHEN cd.value = 67 THEN 'Francia'
        WHEN cd.value = 68 THEN 'Gabón'
        WHEN cd.value = 69 THEN 'Gambia'
        WHEN cd.value = 70 THEN 'Georgia'
        WHEN cd.value = 71 THEN 'Ghana'
        WHEN cd.value = 72 THEN 'Granada'
        WHEN cd.value = 72 THEN 'Grecia'
        WHEN cd.value = 74 THEN 'Guatemala'
        WHEN cd.value = 75 THEN 'Guyana'
        WHEN cd.value = 76 THEN 'Guinea'
        WHEN cd.value = 77 THEN 'Guinea ecuatorial'
        WHEN cd.value = 78 THEN 'Guinea-Bisáu'
        WHEN cd.value = 79 THEN 'Haití'
        WHEN cd.value = 80 THEN 'Honduras'
        WHEN cd.value = 81 THEN 'Hungría'
        WHEN cd.value = 82 THEN 'India'
        WHEN cd.value = 83 THEN 'Indonesia'
        WHEN cd.value = 84 THEN 'Irak'
        WHEN cd.value = 85 THEN 'Irán'
        WHEN cd.value = 86 THEN 'Irlanda'
        WHEN cd.value = 87 THEN 'Islandia'
        WHEN cd.value = 88 THEN 'Islas Marshall'
        WHEN cd.value = 89 THEN 'Islas Salomón'
        WHEN cd.value = 90 THEN 'Israel'
        WHEN cd.value = 91 THEN 'Italia'
        WHEN cd.value = 92 THEN 'Jamaica'
        WHEN cd.value = 93 THEN 'Japón'
        WHEN cd.value = 94 THEN 'Jordania'
        WHEN cd.value = 95 THEN 'Kazajistán'
        WHEN cd.value = 96 THEN 'Kenia'
        WHEN cd.value = 97 THEN 'Kirguistán'
        WHEN cd.value = 98 THEN 'Kiribati'
        WHEN cd.value = 99 THEN 'Kuwait'
        WHEN cd.value = 100 THEN 'Laos'
        WHEN cd.value = 101 THEN 'Lesoto'
        WHEN cd.value = 102 THEN 'Letonia'
        WHEN cd.value = 103 THEN 'Líbano'
        WHEN cd.value = 104 THEN 'Liberia'
        WHEN cd.value = 105 THEN 'Libia'
        WHEN cd.value = 106 THEN 'Liechtenstein'
        WHEN cd.value = 107 THEN 'Lituania'
        WHEN cd.value = 108 THEN 'Luxemburgo'
        WHEN cd.value = 109 THEN 'Macedonia del Norte'
        WHEN cd.value = 110 THEN 'Madagascar'
        WHEN cd.value = 111 THEN 'Malasia'
        WHEN cd.value = 112 THEN 'Malaui'
        WHEN cd.value = 113 THEN 'Maldivas'
        WHEN cd.value = 114 THEN 'Malí'
        WHEN cd.value = 115 THEN 'Malta'
        WHEN cd.value = 116 THEN 'Marruecos'
        WHEN cd.value = 117 THEN 'Mauricio'
        WHEN cd.value = 118 THEN 'Mauritania'
        WHEN cd.value = 119 THEN 'México'
        WHEN cd.value = 120 THEN 'Micronesia'
        WHEN cd.value = 121 THEN 'Moldavia'
        WHEN cd.value = 122 THEN 'Mónaco'
        WHEN cd.value = 123 THEN 'Mongolia'
        WHEN cd.value = 124 THEN 'Montenegro'
        WHEN cd.value = 125 THEN 'Mozambique'
        WHEN cd.value = 126 THEN 'Namibia'
        WHEN cd.value = 127 THEN 'Nauru'
        WHEN cd.value = 128 THEN 'Nepal'
        WHEN cd.value = 129 THEN 'Nicaragua'
        WHEN cd.value = 130 THEN 'Níger'
        WHEN cd.value = 131 THEN 'Nigeria'
        WHEN cd.value = 132 THEN 'Noruega'
        WHEN cd.value = 133 THEN 'Nueva Zelanda'
        WHEN cd.value = 134 THEN 'Omán'
        WHEN cd.value = 135 THEN 'Países Bajos'
        WHEN cd.value = 136 THEN 'Pakistán'
        WHEN cd.value = 137 THEN 'Palaos'
        WHEN cd.value = 138 THEN 'Panamá'
        WHEN cd.value = 139 THEN 'Papúa Nueva Guinea'
        WHEN cd.value = 140 THEN 'Paraguay'
        WHEN cd.value = 141 THEN 'Perú'
        WHEN cd.value = 142 THEN 'Polonia'
        WHEN cd.value = 143 THEN 'Portugal'
        WHEN cd.value = 144 THEN 'Reino Unido'
        WHEN cd.value = 145 THEN 'República Centroafricana'
        WHEN cd.value = 146 THEN 'República Checa'
        WHEN cd.value = 147 THEN 'República del Congo'
        WHEN cd.value = 148 THEN 'República Democrática del Congo'
        WHEN cd.value = 149 THEN 'República Dominicana'
        WHEN cd.value = 150 THEN 'Ruanda'
        WHEN cd.value = 151 THEN 'Rumanía'
        WHEN cd.value = 152 THEN 'Rusia'
        WHEN cd.value = 153 THEN 'Samoa'
        WHEN cd.value = 154 THEN 'San Cristóbal y Nieves'
        WHEN cd.value = 155 THEN 'San Marino'
        WHEN cd.value = 156 THEN 'San Vicente y las Granadinas'
        WHEN cd.value = 157 THEN 'Santa Lucía'
        WHEN cd.value = 158 THEN 'Santo Tomé y Príncipe'
        WHEN cd.value = 159 THEN 'Senegal'
        WHEN cd.value = 160 THEN 'Serbia'
        WHEN cd.value = 161 THEN 'Seychelles'
        WHEN cd.value = 162 THEN 'Sierra Leona'
        WHEN cd.value = 163 THEN 'Singapur'
        WHEN cd.value = 164 THEN 'Siria'
        WHEN cd.value = 165 THEN 'Somalia'
        WHEN cd.value = 166 THEN 'Sri Lanka'
        WHEN cd.value = 167 THEN 'Suazilandia'
        WHEN cd.value = 168 THEN 'Sudáfrica'
        WHEN cd.value = 169 THEN 'Sudán'
        WHEN cd.value = 170 THEN 'Sudán del Sur'
        WHEN cd.value = 171 THEN 'Suecia'
        WHEN cd.value = 172 THEN 'Suiza'
        WHEN cd.value = 172 THEN 'Surinam'
        WHEN cd.value = 174 THEN 'Tailandia'
        WHEN cd.value = 175 THEN 'Tanzania'
        WHEN cd.value = 176 THEN 'Tayikistán'
        WHEN cd.value = 177 THEN 'Timor Oriental'
        WHEN cd.value = 178 THEN 'Togo'
        WHEN cd.value = 179 THEN 'Tonga'
        WHEN cd.value = 180 THEN 'Trinidad y Tobago'
        WHEN cd.value = 181 THEN 'Túnez'
        WHEN cd.value = 182 THEN 'Turkmenistán'
        WHEN cd.value = 183 THEN 'Turquía'
        WHEN cd.value = 184 THEN 'Tuvalu'
        WHEN cd.value = 185 THEN 'Ucrania'
        WHEN cd.value = 186 THEN 'Uganda'
        WHEN cd.value = 187 THEN 'Uruguay'
        WHEN cd.value = 188 THEN 'Uzbekistán'
        WHEN cd.value = 189 THEN 'Vanuatu'
        WHEN cd.value = 190 THEN 'Venezuela'
        WHEN cd.value = 191 THEN 'Vietnam'
        WHEN cd.value = 192 THEN 'Yemen'
        WHEN cd.value = 193 THEN 'Yibuti'
        WHEN cd.value = 194 THEN 'Zambia'
        WHEN cd.value = 195 THEN 'Zimbabue'
        end) as pais
        FROM mdl_course c
        INNER JOIN mdl_customfield_data cd
        ON c.id=cd.instanceid
        INNER JOIN mdl_customfield_field cf
        ON cf.id=cd.fieldid
        WHERE cf.shortname='pais') pais
ON pais.instanceid=c.id
left join
        (select c.id as course_id, c.fullname,r.shortname, GROUP_CONCAT(concat(u.firstname, ' ', u.lastname)) as nombre,u.email
        from mdl_course c
        INNER JOIN mdl_context con on con.instanceid = c.id
        INNER JOIN mdl_role_assignments as asg on asg.contextid = con.id
        INNER JOIN mdl_user as u on asg.userid = u.id
        INNER JOIN mdl_role r on asg.roleid = r.id 
        where  r.shortname = 'stakeholder'
        group by c.id)responsable
on responsable.course_id=c.id
left join
	(select c.id as course_id,c.fullname,count(c.id) as conteo,r.shortname,u.username
    from mdl_course c
    INNER JOIN mdl_context con on con.instanceid = c.id
    INNER JOIN mdl_role_assignments as asg on asg.contextid = con.id
    INNER JOIN mdl_user as u on asg.userid = u.id
    INNER JOIN mdl_role r on asg.roleid = r.id
    where r.shortname = 'student'
    group by c.id
    order by c.id)matriculados
on matriculados.course_id=c.id
LEFT JOIN
	(select c.id as course_id,c.fullname,u.username,u.id as user_id, count(c.id) as total
    from mdl_course c
    INNER JOIN mdl_context con on con.instanceid = c.id
    INNER JOIN mdl_role_assignments as asg on asg.contextid = con.id
    INNER JOIN mdl_user as u on asg.userid = u.id
    INNER JOIN mdl_role r on asg.roleid = r.id
    left join
        (Select count(id)as num,course from mdl_course_completion_criteria
         group by course) completocriterio
    on c.id=completocriterio.course
    left join
        (Select userid,course,count(id)as num2 from mdl_course_completion_crit_compl
    	group by course, userid) completoecho
    on completoecho.userid=u.id and completoecho.course=c.id
    where r.shortname = 'student' and u.deleted=0 and completocriterio.num=completoecho.num2
    group by c.id)asistencia
on asistencia.course_id=c.id
left join
	(select f.course,f.id as id_encuesta,fi.id as id_item,fv.id,format((sum(fv.value)*100)/(Count(fv.item)*10), 2)as promedio
    from mdl_feedback f
    inner join mdl_feedback_item fi
    on f.id=fi.feedback
    inner join mdl_feedback_value fv
    on fi.id=fv.item
    where f.name='Encuesta de satisfacción E-learning' and fi.typ='multichoice' and fi.label='pregunta1'
    group by fi.id)pregunta1
on pregunta1.course=c.id
left join
	(select f.course,f.id as id_encuesta,fi.id as id_item,fv.id,format((sum(fv.value)*100)/(Count(fv.item)*10), 2)as promedio
    from mdl_feedback f
    inner join mdl_feedback_item fi
    on f.id=fi.feedback
    inner join mdl_feedback_value fv
    on fi.id=fv.item
    where f.name='Encuesta de satisfacción E-learning' and fi.typ='multichoice' and fi.label='pregunta2'
    group by fi.id)pregunta2
on pregunta2.course=c.id
left join
	(select f.course,f.id as id_encuesta,fi.id as id_item,fv.id,format((sum(fv.value)*100)/(Count(fv.item)*10), 2)as promedio
    from mdl_feedback f
    inner join mdl_feedback_item fi
    on f.id=fi.feedback
    inner join mdl_feedback_value fv
    on fi.id=fv.item
    where f.name='Encuesta de satisfacción E-learning' and fi.typ='multichoice' and fi.label='pregunta3'
    group by fi.id)pregunta3
on pregunta3.course=c.id
left join
	(select f.course,f.id as id_encuesta,fi.id as id_item,fv.id,format((sum(fv.value)*100)/(Count(fv.item)*10), 2)as promedio
    from mdl_feedback f
    inner join mdl_feedback_item fi
    on f.id=fi.feedback
    inner join mdl_feedback_value fv
    on fi.id=fv.item
    where f.name='Encuesta de satisfacción E-learning' and fi.typ='multichoice' and fi.label='pregunta4'
    group by fi.id)pregunta4
on pregunta4.course=c.id
left join
	(select f.course,f.id as id_encuesta,fi.id as id_item,fv.id,format((sum(fv.value)*100)/(Count(fv.item)*10), 2)as promedio
    from mdl_feedback f
    inner join mdl_feedback_item fi
    on f.id=fi.feedback
    inner join mdl_feedback_value fv
    on fi.id=fv.item
    where f.name='Encuesta de satisfacción E-learning' and fi.typ='multichoice' and fi.label='pregunta5'
    group by fi.id)pregunta5
on pregunta5.course=c.id
left join
	(select f.course,f.id as id_encuesta,fi.id as id_item,fv.id,format((sum(fv.value)*100)/(Count(fv.item)*10), 2)as promedio
    from mdl_feedback f
    inner join mdl_feedback_item fi
    on f.id=fi.feedback
    inner join mdl_feedback_value fv
    on fi.id=fv.item
    where f.name='Encuesta de satisfacción' and fi.typ='multichoice' and fi.label='pregunta1'
    group by fi.id)spregunta1
on spregunta1.course=c.id
left join
	(select f.course,f.id as id_encuesta,fi.id as id_item,fv.id,format((sum(fv.value)*100)/(Count(fv.item)*10), 2)as promedio
    from mdl_feedback f
    inner join mdl_feedback_item fi
    on f.id=fi.feedback
    inner join mdl_feedback_value fv
    on fi.id=fv.item
    where f.name='Encuesta de satisfacción' and fi.typ='multichoice' and fi.label='pregunta2'
    group by fi.id)spregunta2
on spregunta2.course=c.id
left join
	(select f.course,f.id as id_encuesta,fi.id as id_item,fv.id,format((sum(fv.value)*100)/(Count(fv.item)*10), 2)as promedio
    from mdl_feedback f
    inner join mdl_feedback_item fi
    on f.id=fi.feedback
    inner join mdl_feedback_value fv
    on fi.id=fv.item
    where f.name='Encuesta de satisfacción' and fi.typ='multichoice' and fi.label='pregunta3'
    group by fi.id)spregunta3
on spregunta3.course=c.id
left join
	(select f.course,f.id as id_encuesta,fi.id as id_item,fv.id,format((sum(fv.value)*100)/(Count(fv.item)*10), 2)as promedio
    from mdl_feedback f
    inner join mdl_feedback_item fi
    on f.id=fi.feedback
    inner join mdl_feedback_value fv
    on fi.id=fv.item
    where f.name='Encuesta de satisfacción' and fi.typ='multichoice' and fi.label='pregunta4'
    group by fi.id)spregunta4
on spregunta4.course=c.id
left join
	(select f.course,f.id as id_encuesta,fi.id as id_item,fv.id,format((sum(fv.value)*100)/(Count(fv.item)*10), 2)as promedio
    from mdl_feedback f
    inner join mdl_feedback_item fi
    on f.id=fi.feedback
    inner join mdl_feedback_value fv
    on fi.id=fv.item
    where f.name='Encuesta de satisfacción' and fi.typ='multichoice' and fi.label='pregunta5'
    group by fi.id)spregunta5
on spregunta5.course=c.id
left join
	(select f.course,f.id as id_encuesta,fi.id as id_item,fv.id,format((sum(fv.value)*100)/(Count(fv.item)*10), 2)as promedio
    from mdl_feedback f
    inner join mdl_feedback_item fi
    on f.id=fi.feedback
    inner join mdl_feedback_value fv
    on fi.id=fv.item
    where f.name='Encuesta de satisfacción' and fi.typ='multichoice' and fi.label='pregunta6'
    group by fi.id)spregunta6
on spregunta6.course=c.id
left join
	(select f.course,f.id as id_encuesta,fi.id as id_item,fv.id,format((sum(fv.value)*100)/(Count(fv.item)*10), 2)as promedio
    from mdl_feedback f
    inner join mdl_feedback_item fi
    on f.id=fi.feedback
    inner join mdl_feedback_value fv
    on fi.id=fv.item
    where f.name='Encuesta de satisfacción' and fi.typ='multichoice' and fi.label='pregunta7'
    group by fi.id)spregunta7
on spregunta7.course=c.id
left join
	(select f.course,f.id as id_encuesta,fi.id as id_item,fv.id,format((sum(fv.value)*100)/(Count(fv.item)*10), 2)as promedio
    from mdl_feedback f
    inner join mdl_feedback_item fi
    on f.id=fi.feedback
    inner join mdl_feedback_value fv
    on fi.id=fv.item
    where f.name='Encuesta de satisfacción' and fi.typ='multichoice' and fi.label='pregunta8'
    group by fi.id)spregunta8
on spregunta8.course=c.id
left join
	(select f.course,f.id as id_encuesta,fi.id as id_item,fv.id,format((sum(fv.value)*100)/(Count(fv.item)*10), 2)as promedio
    from mdl_feedback f
    inner join mdl_feedback_item fi
    on f.id=fi.feedback
    inner join mdl_feedback_value fv
    on fi.id=fv.item
    where f.name='Encuesta de satisfacción' and fi.typ='multichoice' and fi.label='pregunta9'
    group by fi.id)spregunta9
on spregunta9.course=c.id
LEFT JOIN
    (select f.course,f.id as id_encuesta,fi.id as id_item,fv.id,format((sum(fv.value)*100)/(Count(fv.item)*10), 2)as promedio
    from mdl_feedback f
    inner join mdl_feedback_item fi
    on f.id=fi.feedback
    inner join mdl_feedback_value fv
    on fi.id=fv.item
    where f.name='Encuesta de satisfacción E-learning' and fi.typ='multichoice'
    group by f.course)epromedio
on epromedio.course=c.id
LEFT JOIN
    (select f.course,f.id as id_encuesta,fi.id as id_item,fv.id,format((sum(fv.value)*100)/(Count(fv.item)*10), 2)as promedio
    from mdl_feedback f
    inner join mdl_feedback_item fi
    on f.id=fi.feedback
    inner join mdl_feedback_value fv
    on fi.id=fv.item
    where f.name='Encuesta de satisfacción' and fi.typ='multichoice'
    group by f.course)spromedio
on spromedio.course=c.id
where r.shortname = 'editingteacher' and u.deleted=0 and c.id = '$id'
order by c.id");
$result = array();

//Consulta #2
$sql2 = "select @s:=@s + 1 id_auto, 
DATE_FORMAT(DATE_ADD(FROM_UNIXTIME(c.startdate, '%Y-%m-%d'), INTERVAL 1 HOUR),'%d/%m/%Y') AS fecha_activado,
codigo.codigo as codigo_curso,
c.fullname as curso,
if(modalidad.modalidad is null, 'Sin dato', if(modalidad.modalidad = '', 'Sin dato', modalidad.modalidad))as modalidad,
if(aprendizaje.aprendizaje is null, 'Sin dato', if(aprendizaje.aprendizaje = '', 'Sin dato', aprendizaje.aprendizaje))  as agenda_de_aprendizaje,
if(tipocurso.tipocurso is null, 'Sin dato', if(tipocurso.tipocurso = '', 'Sin dato', tipocurso.tipocurso)) as tipo_de_curso,
if(dirigido.dirigido is null, 'Sin dato', if(dirigido.dirigido = '', 'Sin dato', dirigido.dirigido)) as segmento_dirigido,
if(responsable.nombre is null, 'Sin dato', if(responsable.nombre = '', 'Sin dato', responsable.nombre)) as responsableCOE,
if(unidad.unidad is null, 'Sin dato', if(unidad.unidad = '', 'Sin dato', unidad.unidad)) as unidad_de_negocio,
if(poblacion.poblacion is null, 'Sin dato', if(poblacion.poblacion = '', 'Sin dato', poblacion.poblacion )) as poblacion_dirigida,
if(pais.pais is null, 'Sin dato', if(pais.pais = '', 'Sin dato', pais.pais)) as pais,
if(concat(u.firstname,' ',u.lastname) is null, 'Sin dato', concat(u.firstname,' ',u.lastname)) as facilitador,
if(matriculados.conteo is null, '0.0', matriculados.conteo) as inscritos,
if(asistencia.total is null, '0.0', asistencia.total) as asistencia,
if(format((asistencia.total*100)/matriculados.conteo,2) is null, '0.0', format((asistencia.total*100)/matriculados.conteo,2)) as porcentaje_asistencia, 
if(tipoc.tipoc is null, 'Sin dato', tipoc.tipoc) as tipo_de_encuesta, 
CASE when spromedio.promedio is null THEN '0.0' else spromedio.promedio END as spromedio,
if(spregunta1.promedio is null, '.0', spregunta1.promedio) as S_pregunta1,
if(spregunta2.promedio is null, '.0', spregunta2.promedio) as S_pregunta2,
if(spregunta3.promedio is null, '.0', spregunta3.promedio) as S_pregunta3,
if(spregunta4.promedio is null, '.0', spregunta4.promedio) as S_pregunta4,
if(spregunta5.promedio is null, '.0', spregunta5.promedio) as S_pregunta5,
if(spregunta6.promedio is null, '.0', spregunta6.promedio) as S_pregunta6,
if(spregunta7.promedio is null, '.0', spregunta7.promedio) as S_pregunta7,
if(spregunta8.promedio is null, '.0', spregunta8.promedio) as S_pregunta8,
if(spregunta9.promedio is null, '.0', spregunta9.promedio) as S_pregunta9
from (select @s:=0) as s, mdl_course c
INNER JOIN mdl_context con on con.instanceid = c.id
INNER JOIN mdl_role_assignments as asg on asg.contextid = con.id
INNER JOIN mdl_user as u on asg.userid = u.id
INNER JOIN mdl_role r on asg.roleid = r.id
LEFT JOIN
        (SELECT cd.value AS codigo, cd.fieldid, cd.instanceid
        FROM mdl_course c
        INNER JOIN mdl_customfield_data cd
        ON c.id=cd.instanceid
        INNER JOIN mdl_customfield_field cf
        ON cf.id=cd.fieldid
        WHERE cf.shortname='codigo') codigo
ON codigo.instanceid=c.id
LEFT JOIN
        (SELECT cd.fieldid, cd.instanceid,(CASE WHEN cd.value = 0 THEN 'Sin dato'
        WHEN cd.value = 1 THEN 'No aplica'
        WHEN cd.value = 2 THEN 'Virtual'
        WHEN cd.value = 3 THEN 'Presencial'
        WHEN cd.value = 4 THEN 'Blended'
        end) as modalidad
        FROM mdl_course c
        INNER JOIN mdl_customfield_data cd
        ON c.id=cd.instanceid
        INNER JOIN mdl_customfield_field cf
        ON cf.id=cd.fieldid
        WHERE cf.shortname='modalidad_curso') modalidad
ON modalidad.instanceid=c.id
LEFT JOIN
		(SELECT cd.fieldid, cd.instanceid,(CASE WHEN cd.value = 0 THEN 'Sin dato'
        WHEN cd.value = 1 THEN 'No aplica'
        WHEN cd.value = 2 THEN 'eLearning'
        WHEN cd.value = 3 THEN 'Webinar'
        WHEN cd.value = 4 THEN 'Presencial'
        end) as tipoc
        FROM mdl_course c
        INNER JOIN mdl_customfield_data cd
        ON c.id=cd.instanceid
        INNER JOIN mdl_customfield_field cf
        ON cf.id=cd.fieldid
        WHERE cf.shortname='tipo_de_encuesta') tipoc
ON tipoc.instanceid=c.id
LEFT JOIN
        (SELECT cd.value AS codigo, cd.fieldid, cd.instanceid,(CASE WHEN cd.value = 0 THEN 'Sin dato'
        WHEN cd.value = 1 THEN 'No aplica'
        WHEN cd.value = 2 THEN 'Mandatario'
        WHEN cd.value = 3 THEN 'Libre'
        end) as aprendizaje
        FROM mdl_course c
        INNER JOIN mdl_customfield_data cd
        ON c.id=cd.instanceid
        INNER JOIN mdl_customfield_field cf
        ON cf.id=cd.fieldid
        WHERE cf.shortname='agenda_aprendizaje') aprendizaje
ON aprendizaje.instanceid=c.id
LEFT JOIN
        (SELECT cd.value AS codigo, cd.fieldid, cd.instanceid,(CASE WHEN cd.value = 0 THEN 'Sin dato'
        WHEN cd.value = 1 THEN 'No aplica'
        WHEN cd.value = 2 THEN 'Liderazgo'
        WHEN cd.value = 3 THEN 'Escuela comercial'
        WHEN cd.value = 4 THEN 'Escuela técnica'
        WHEN cd.value = 5 THEN 'Estrategia'
        WHEN cd.value = 6 THEN 'Financiero'
        end) as tipocurso
        FROM mdl_course c
        INNER JOIN mdl_customfield_data cd
        ON c.id=cd.instanceid
        INNER JOIN mdl_customfield_field cf
        ON cf.id=cd.fieldid
        WHERE cf.shortname='tipo_curso') tipocurso
ON tipocurso.instanceid=c.id
LEFT JOIN
        (SELECT cd.value AS codigo, cd.fieldid, cd.instanceid,(CASE WHEN cd.value = 0 THEN 'Sin dato'
        WHEN cd.value = 1 THEN 'No aplica'
        WHEN cd.value = 2 THEN 'Transversal'
        WHEN cd.value = 3 THEN 'Vertical'
        end) as dirigido
        FROM mdl_course c
        INNER JOIN mdl_customfield_data cd
        ON c.id=cd.instanceid
        INNER JOIN mdl_customfield_field cf
        ON cf.id=cd.fieldid
        WHERE cf.shortname='segmento_dirigido') dirigido
ON dirigido.instanceid=c.id
LEFT JOIN
        (SELECT cd.value AS codigo, cd.fieldid, cd.instanceid,(CASE WHEN cd.value = 0 THEN 'Sin dato'
        WHEN cd.value = 1 THEN 'No aplica'
        WHEN cd.value = 2 THEN 'ALIMENTOS'
        WHEN cd.value = 3 THEN 'Rca'
        WHEN cd.value = 4 THEN 'Ip'
        WHEN cd.value = 5 THEN 'Cusa'
        WHEN cd.value = 6 THEN 'A&C'
        WHEN cd.value = 7 THEN 'Harinas'
        WHEN cd.value = 8 THEN 'B2B'
        WHEN cd.value = 9 THEN 'ABA'
        end) as unidad
        FROM mdl_course c
        INNER JOIN mdl_customfield_data cd
        ON c.id=cd.instanceid
        INNER JOIN mdl_customfield_field cf
        ON cf.id=cd.fieldid
        WHERE cf.shortname='unidades_negocio') unidad
ON unidad.instanceid=c.id
LEFT JOIN
        (SELECT cd.value AS codigo, cd.fieldid, cd.instanceid,(CASE WHEN cd.value = 0 THEN 'Sin dato'
        WHEN cd.value = 1 THEN 'No aplica'
        WHEN cd.value = 2 THEN 'Ejecutivo directivo'
        WHEN cd.value = 3 THEN 'Gerente'
        WHEN cd.value = 4 THEN 'Jefatura'
        WHEN cd.value = 5 THEN 'Administrativo'
        WHEN cd.value = 6 THEN 'Operativo'
        end) as poblacion
        FROM mdl_course c
        INNER JOIN mdl_customfield_data cd
        ON c.id=cd.instanceid
        INNER JOIN mdl_customfield_field cf
        ON cf.id=cd.fieldid
        WHERE cf.shortname='poblacion_dirigida') poblacion
ON poblacion.instanceid=c.id
LEFT JOIN
        (SELECT cd.value AS codigo, cd.fieldid, cd.instanceid,(CASE WHEN cd.value = 0 THEN 'Sin dato'
		WHEN cd.value = 1 THEN 'No aplica'
        WHEN cd.value = 2 THEN 'Afganistán'
        WHEN cd.value = 3 THEN 'Albania'
        WHEN cd.value = 4 THEN 'Alemania'
        WHEN cd.value = 5 THEN 'Andorra'
        WHEN cd.value = 6 THEN 'Angola'
        WHEN cd.value = 7 THEN 'Antigua y Barbuda'
        WHEN cd.value = 8 THEN 'Arabia Saudita'
        WHEN cd.value = 9 THEN 'Argelia'
        WHEN cd.value = 10 THEN 'Argentina'
        WHEN cd.value = 11 THEN 'Armenia'
        WHEN cd.value = 12 THEN 'Australia'
        WHEN cd.value = 13 THEN 'Austria'
        WHEN cd.value = 14 THEN 'Azerbaiyán'
        WHEN cd.value = 15 THEN 'Bahamas'
        WHEN cd.value = 16 THEN 'Bangladés'
        WHEN cd.value = 17 THEN 'Barbados'
        WHEN cd.value = 18 THEN 'Baréin'
        WHEN cd.value = 19 THEN 'Bélgica'
        WHEN cd.value = 20 THEN 'Belice'
        WHEN cd.value = 21 THEN 'Benín'
        WHEN cd.value = 22 THEN 'Bielorrusia'
        WHEN cd.value = 23 THEN 'Birmania'
        WHEN cd.value = 24 THEN 'Bolivia'
        WHEN cd.value = 25 THEN 'Bosnia y Herzegovina'
        WHEN cd.value = 26 THEN 'Botsuana'
        WHEN cd.value = 27 THEN 'Brasil'
        WHEN cd.value = 28 THEN 'Brunéi'
        WHEN cd.value = 29 THEN 'Bulgaria'
        WHEN cd.value = 30 THEN 'Burkina Faso'
        WHEN cd.value = 31 THEN 'Burundi'
        WHEN cd.value = 32 THEN 'Bután'
        WHEN cd.value = 33 THEN 'Cabo Verde'
        WHEN cd.value = 34 THEN 'Camboya'
        WHEN cd.value = 35 THEN 'Camerún'
        WHEN cd.value = 36 THEN 'Canadá'
        WHEN cd.value = 37 THEN 'Catar'
        WHEN cd.value = 38 THEN 'Chad'
        WHEN cd.value = 39 THEN 'Chile'
        WHEN cd.value = 40 THEN 'China'
        WHEN cd.value = 41 THEN 'Chipre'
        WHEN cd.value = 42 THEN 'Ciudad del Vaticano'
        WHEN cd.value = 43 THEN 'Colombia'
        WHEN cd.value = 44 THEN 'Comoras'
        WHEN cd.value = 45 THEN 'Corea del Norte'
        WHEN cd.value = 46 THEN 'Corea del Sur'
        WHEN cd.value = 47 THEN 'Costa de Marfil'
        WHEN cd.value = 48 THEN 'Costa Rica'
        WHEN cd.value = 49 THEN 'Croacia'
        WHEN cd.value = 50 THEN 'Cuba'
        WHEN cd.value = 51 THEN 'Dinamarca'
        WHEN cd.value = 52 THEN 'Dominica'
        WHEN cd.value = 53 THEN 'Ecuador'
        WHEN cd.value = 54 THEN 'Egipto'
        WHEN cd.value = 55 THEN 'El Salvador'
        WHEN cd.value = 56 THEN 'Emiratos Árabes Unidos'
        WHEN cd.value = 57 THEN 'Eritrea'
        WHEN cd.value = 58 THEN 'Eslovaquia'
        WHEN cd.value = 59 THEN 'Eslovenia'
        WHEN cd.value = 60 THEN 'España'
        WHEN cd.value = 61 THEN 'Estados Unidos'
        WHEN cd.value = 62 THEN 'Estonia'
        WHEN cd.value = 63 THEN 'Etiopía'
        WHEN cd.value = 64 THEN 'Filipinas'
        WHEN cd.value = 65 THEN 'Finlandia'
        WHEN cd.value = 66 THEN 'Fiyi'
        WHEN cd.value = 67 THEN 'Francia'
        WHEN cd.value = 68 THEN 'Gabón'
        WHEN cd.value = 69 THEN 'Gambia'
        WHEN cd.value = 70 THEN 'Georgia'
        WHEN cd.value = 71 THEN 'Ghana'
        WHEN cd.value = 72 THEN 'Granada'
        WHEN cd.value = 72 THEN 'Grecia'
        WHEN cd.value = 74 THEN 'Guatemala'
        WHEN cd.value = 75 THEN 'Guyana'
        WHEN cd.value = 76 THEN 'Guinea'
        WHEN cd.value = 77 THEN 'Guinea ecuatorial'
        WHEN cd.value = 78 THEN 'Guinea-Bisáu'
        WHEN cd.value = 79 THEN 'Haití'
        WHEN cd.value = 80 THEN 'Honduras'
        WHEN cd.value = 81 THEN 'Hungría'
        WHEN cd.value = 82 THEN 'India'
        WHEN cd.value = 83 THEN 'Indonesia'
        WHEN cd.value = 84 THEN 'Irak'
        WHEN cd.value = 85 THEN 'Irán'
        WHEN cd.value = 86 THEN 'Irlanda'
        WHEN cd.value = 87 THEN 'Islandia'
        WHEN cd.value = 88 THEN 'Islas Marshall'
        WHEN cd.value = 89 THEN 'Islas Salomón'
        WHEN cd.value = 90 THEN 'Israel'
        WHEN cd.value = 91 THEN 'Italia'
        WHEN cd.value = 92 THEN 'Jamaica'
        WHEN cd.value = 93 THEN 'Japón'
        WHEN cd.value = 94 THEN 'Jordania'
        WHEN cd.value = 95 THEN 'Kazajistán'
        WHEN cd.value = 96 THEN 'Kenia'
        WHEN cd.value = 97 THEN 'Kirguistán'
        WHEN cd.value = 98 THEN 'Kiribati'
        WHEN cd.value = 99 THEN 'Kuwait'
        WHEN cd.value = 100 THEN 'Laos'
        WHEN cd.value = 101 THEN 'Lesoto'
        WHEN cd.value = 102 THEN 'Letonia'
        WHEN cd.value = 103 THEN 'Líbano'
        WHEN cd.value = 104 THEN 'Liberia'
        WHEN cd.value = 105 THEN 'Libia'
        WHEN cd.value = 106 THEN 'Liechtenstein'
        WHEN cd.value = 107 THEN 'Lituania'
        WHEN cd.value = 108 THEN 'Luxemburgo'
        WHEN cd.value = 109 THEN 'Macedonia del Norte'
        WHEN cd.value = 110 THEN 'Madagascar'
        WHEN cd.value = 111 THEN 'Malasia'
        WHEN cd.value = 112 THEN 'Malaui'
        WHEN cd.value = 113 THEN 'Maldivas'
        WHEN cd.value = 114 THEN 'Malí'
        WHEN cd.value = 115 THEN 'Malta'
        WHEN cd.value = 116 THEN 'Marruecos'
        WHEN cd.value = 117 THEN 'Mauricio'
        WHEN cd.value = 118 THEN 'Mauritania'
        WHEN cd.value = 119 THEN 'México'
        WHEN cd.value = 120 THEN 'Micronesia'
        WHEN cd.value = 121 THEN 'Moldavia'
        WHEN cd.value = 122 THEN 'Mónaco'
        WHEN cd.value = 123 THEN 'Mongolia'
        WHEN cd.value = 124 THEN 'Montenegro'
        WHEN cd.value = 125 THEN 'Mozambique'
        WHEN cd.value = 126 THEN 'Namibia'
        WHEN cd.value = 127 THEN 'Nauru'
        WHEN cd.value = 128 THEN 'Nepal'
        WHEN cd.value = 129 THEN 'Nicaragua'
        WHEN cd.value = 130 THEN 'Níger'
        WHEN cd.value = 131 THEN 'Nigeria'
        WHEN cd.value = 132 THEN 'Noruega'
        WHEN cd.value = 133 THEN 'Nueva Zelanda'
        WHEN cd.value = 134 THEN 'Omán'
        WHEN cd.value = 135 THEN 'Países Bajos'
        WHEN cd.value = 136 THEN 'Pakistán'
        WHEN cd.value = 137 THEN 'Palaos'
        WHEN cd.value = 138 THEN 'Panamá'
        WHEN cd.value = 139 THEN 'Papúa Nueva Guinea'
        WHEN cd.value = 140 THEN 'Paraguay'
        WHEN cd.value = 141 THEN 'Perú'
        WHEN cd.value = 142 THEN 'Polonia'
        WHEN cd.value = 143 THEN 'Portugal'
        WHEN cd.value = 144 THEN 'Reino Unido'
        WHEN cd.value = 145 THEN 'República Centroafricana'
        WHEN cd.value = 146 THEN 'República Checa'
        WHEN cd.value = 147 THEN 'República del Congo'
        WHEN cd.value = 148 THEN 'República Democrática del Congo'
        WHEN cd.value = 149 THEN 'República Dominicana'
        WHEN cd.value = 150 THEN 'Ruanda'
        WHEN cd.value = 151 THEN 'Rumanía'
        WHEN cd.value = 152 THEN 'Rusia'
        WHEN cd.value = 153 THEN 'Samoa'
        WHEN cd.value = 154 THEN 'San Cristóbal y Nieves'
        WHEN cd.value = 155 THEN 'San Marino'
        WHEN cd.value = 156 THEN 'San Vicente y las Granadinas'
        WHEN cd.value = 157 THEN 'Santa Lucía'
        WHEN cd.value = 158 THEN 'Santo Tomé y Príncipe'
        WHEN cd.value = 159 THEN 'Senegal'
        WHEN cd.value = 160 THEN 'Serbia'
        WHEN cd.value = 161 THEN 'Seychelles'
        WHEN cd.value = 162 THEN 'Sierra Leona'
        WHEN cd.value = 163 THEN 'Singapur'
        WHEN cd.value = 164 THEN 'Siria'
        WHEN cd.value = 165 THEN 'Somalia'
        WHEN cd.value = 166 THEN 'Sri Lanka'
        WHEN cd.value = 167 THEN 'Suazilandia'
        WHEN cd.value = 168 THEN 'Sudáfrica'
        WHEN cd.value = 169 THEN 'Sudán'
        WHEN cd.value = 170 THEN 'Sudán del Sur'
        WHEN cd.value = 171 THEN 'Suecia'
        WHEN cd.value = 172 THEN 'Suiza'
        WHEN cd.value = 172 THEN 'Surinam'
        WHEN cd.value = 174 THEN 'Tailandia'
        WHEN cd.value = 175 THEN 'Tanzania'
        WHEN cd.value = 176 THEN 'Tayikistán'
        WHEN cd.value = 177 THEN 'Timor Oriental'
        WHEN cd.value = 178 THEN 'Togo'
        WHEN cd.value = 179 THEN 'Tonga'
        WHEN cd.value = 180 THEN 'Trinidad y Tobago'
        WHEN cd.value = 181 THEN 'Túnez'
        WHEN cd.value = 182 THEN 'Turkmenistán'
        WHEN cd.value = 183 THEN 'Turquía'
        WHEN cd.value = 184 THEN 'Tuvalu'
        WHEN cd.value = 185 THEN 'Ucrania'
        WHEN cd.value = 186 THEN 'Uganda'
        WHEN cd.value = 187 THEN 'Uruguay'
        WHEN cd.value = 188 THEN 'Uzbekistán'
        WHEN cd.value = 189 THEN 'Vanuatu'
        WHEN cd.value = 190 THEN 'Venezuela'
        WHEN cd.value = 191 THEN 'Vietnam'
        WHEN cd.value = 192 THEN 'Yemen'
        WHEN cd.value = 193 THEN 'Yibuti'
        WHEN cd.value = 194 THEN 'Zambia'
        WHEN cd.value = 195 THEN 'Zimbabue'
        end) as pais
        FROM mdl_course c
        INNER JOIN mdl_customfield_data cd
        ON c.id=cd.instanceid
        INNER JOIN mdl_customfield_field cf
        ON cf.id=cd.fieldid
        WHERE cf.shortname='pais') pais
ON pais.instanceid=c.id
left join
        (select c.id as course_id, c.fullname,r.shortname, GROUP_CONCAT(concat(u.firstname, ' ', u.lastname)) as nombre,u.email
        from mdl_course c
        INNER JOIN mdl_context con on con.instanceid = c.id
        INNER JOIN mdl_role_assignments as asg on asg.contextid = con.id
        INNER JOIN mdl_user as u on asg.userid = u.id
        INNER JOIN mdl_role r on asg.roleid = r.id 
        where  r.shortname = 'stakeholder'
        group by c.id)responsable
on responsable.course_id=c.id
left join
	(select c.id as course_id,c.fullname,count(c.id) as conteo,r.shortname,u.username
    from mdl_course c
    INNER JOIN mdl_context con on con.instanceid = c.id
    INNER JOIN mdl_role_assignments as asg on asg.contextid = con.id
    INNER JOIN mdl_user as u on asg.userid = u.id
    INNER JOIN mdl_role r on asg.roleid = r.id
    where r.shortname = 'student'
    group by c.id
    order by c.id)matriculados
on matriculados.course_id=c.id
LEFT JOIN
	(select c.id as course_id,c.fullname,u.username,u.id as user_id, count(c.id) as total
    from mdl_course c
    INNER JOIN mdl_context con on con.instanceid = c.id
    INNER JOIN mdl_role_assignments as asg on asg.contextid = con.id
    INNER JOIN mdl_user as u on asg.userid = u.id
    INNER JOIN mdl_role r on asg.roleid = r.id
    left join
        (Select count(id)as num,course from mdl_course_completion_criteria
         group by course) completocriterio
    on c.id=completocriterio.course
    left join
        (Select userid,course,count(id)as num2 from mdl_course_completion_crit_compl
    	group by course, userid) completoecho
    on completoecho.userid=u.id and completoecho.course=c.id
    where r.shortname = 'student' and u.deleted=0 and completocriterio.num=completoecho.num2
    group by c.id)asistencia
on asistencia.course_id=c.id
left join
	(select f.course,f.id as id_encuesta,fi.id as id_item,fv.id,format((sum(fv.value)*100)/(Count(fv.item)*10), 2)as promedio
    from mdl_feedback f
    inner join mdl_feedback_item fi
    on f.id=fi.feedback
    inner join mdl_feedback_value fv
    on fi.id=fv.item
    where f.name='Encuesta de satisfacción E-learning' and fi.typ='multichoice' and fi.label='pregunta1'
    group by fi.id)pregunta1
on pregunta1.course=c.id
left join
	(select f.course,f.id as id_encuesta,fi.id as id_item,fv.id,format((sum(fv.value)*100)/(Count(fv.item)*10), 2)as promedio
    from mdl_feedback f
    inner join mdl_feedback_item fi
    on f.id=fi.feedback
    inner join mdl_feedback_value fv
    on fi.id=fv.item
    where f.name='Encuesta de satisfacción E-learning' and fi.typ='multichoice' and fi.label='pregunta2'
    group by fi.id)pregunta2
on pregunta2.course=c.id
left join
	(select f.course,f.id as id_encuesta,fi.id as id_item,fv.id,format((sum(fv.value)*100)/(Count(fv.item)*10), 2)as promedio
    from mdl_feedback f
    inner join mdl_feedback_item fi
    on f.id=fi.feedback
    inner join mdl_feedback_value fv
    on fi.id=fv.item
    where f.name='Encuesta de satisfacción E-learning' and fi.typ='multichoice' and fi.label='pregunta3'
    group by fi.id)pregunta3
on pregunta3.course=c.id
left join
	(select f.course,f.id as id_encuesta,fi.id as id_item,fv.id,format((sum(fv.value)*100)/(Count(fv.item)*10), 2)as promedio
    from mdl_feedback f
    inner join mdl_feedback_item fi
    on f.id=fi.feedback
    inner join mdl_feedback_value fv
    on fi.id=fv.item
    where f.name='Encuesta de satisfacción E-learning' and fi.typ='multichoice' and fi.label='pregunta4'
    group by fi.id)pregunta4
on pregunta4.course=c.id
left join
	(select f.course,f.id as id_encuesta,fi.id as id_item,fv.id,format((sum(fv.value)*100)/(Count(fv.item)*10), 2)as promedio
    from mdl_feedback f
    inner join mdl_feedback_item fi
    on f.id=fi.feedback
    inner join mdl_feedback_value fv
    on fi.id=fv.item
    where f.name='Encuesta de satisfacción E-learning' and fi.typ='multichoice' and fi.label='pregunta5'
    group by fi.id)pregunta5
on pregunta5.course=c.id
left join
	(select f.course,f.id as id_encuesta,fi.id as id_item,fv.id,format((sum(fv.value)*100)/(Count(fv.item)*10), 2)as promedio
    from mdl_feedback f
    inner join mdl_feedback_item fi
    on f.id=fi.feedback
    inner join mdl_feedback_value fv
    on fi.id=fv.item
    where f.name='Encuesta de satisfacción' and fi.typ='multichoice' and fi.label='pregunta1'
    group by fi.id)spregunta1
on spregunta1.course=c.id
left join
	(select f.course,f.id as id_encuesta,fi.id as id_item,fv.id,format((sum(fv.value)*100)/(Count(fv.item)*10), 2)as promedio
    from mdl_feedback f
    inner join mdl_feedback_item fi
    on f.id=fi.feedback
    inner join mdl_feedback_value fv
    on fi.id=fv.item
    where f.name='Encuesta de satisfacción' and fi.typ='multichoice' and fi.label='pregunta2'
    group by fi.id)spregunta2
on spregunta2.course=c.id
left join
	(select f.course,f.id as id_encuesta,fi.id as id_item,fv.id,format((sum(fv.value)*100)/(Count(fv.item)*10), 2)as promedio
    from mdl_feedback f
    inner join mdl_feedback_item fi
    on f.id=fi.feedback
    inner join mdl_feedback_value fv
    on fi.id=fv.item
    where f.name='Encuesta de satisfacción' and fi.typ='multichoice' and fi.label='pregunta3'
    group by fi.id)spregunta3
on spregunta3.course=c.id
left join
	(select f.course,f.id as id_encuesta,fi.id as id_item,fv.id,format((sum(fv.value)*100)/(Count(fv.item)*10), 2)as promedio
    from mdl_feedback f
    inner join mdl_feedback_item fi
    on f.id=fi.feedback
    inner join mdl_feedback_value fv
    on fi.id=fv.item
    where f.name='Encuesta de satisfacción' and fi.typ='multichoice' and fi.label='pregunta4'
    group by fi.id)spregunta4
on spregunta4.course=c.id
left join
	(select f.course,f.id as id_encuesta,fi.id as id_item,fv.id,format((sum(fv.value)*100)/(Count(fv.item)*10), 2)as promedio
    from mdl_feedback f
    inner join mdl_feedback_item fi
    on f.id=fi.feedback
    inner join mdl_feedback_value fv
    on fi.id=fv.item
    where f.name='Encuesta de satisfacción' and fi.typ='multichoice' and fi.label='pregunta5'
    group by fi.id)spregunta5
on spregunta5.course=c.id
left join
	(select f.course,f.id as id_encuesta,fi.id as id_item,fv.id,format((sum(fv.value)*100)/(Count(fv.item)*10), 2)as promedio
    from mdl_feedback f
    inner join mdl_feedback_item fi
    on f.id=fi.feedback
    inner join mdl_feedback_value fv
    on fi.id=fv.item
    where f.name='Encuesta de satisfacción' and fi.typ='multichoice' and fi.label='pregunta6'
    group by fi.id)spregunta6
on spregunta6.course=c.id
left join
	(select f.course,f.id as id_encuesta,fi.id as id_item,fv.id,format((sum(fv.value)*100)/(Count(fv.item)*10), 2)as promedio
    from mdl_feedback f
    inner join mdl_feedback_item fi
    on f.id=fi.feedback
    inner join mdl_feedback_value fv
    on fi.id=fv.item
    where f.name='Encuesta de satisfacción' and fi.typ='multichoice' and fi.label='pregunta7'
    group by fi.id)spregunta7
on spregunta7.course=c.id
left join
	(select f.course,f.id as id_encuesta,fi.id as id_item,fv.id,format((sum(fv.value)*100)/(Count(fv.item)*10), 2)as promedio
    from mdl_feedback f
    inner join mdl_feedback_item fi
    on f.id=fi.feedback
    inner join mdl_feedback_value fv
    on fi.id=fv.item
    where f.name='Encuesta de satisfacción' and fi.typ='multichoice' and fi.label='pregunta8'
    group by fi.id)spregunta8
on spregunta8.course=c.id
left join
	(select f.course,f.id as id_encuesta,fi.id as id_item,fv.id,format((sum(fv.value)*100)/(Count(fv.item)*10), 2)as promedio
    from mdl_feedback f
    inner join mdl_feedback_item fi
    on f.id=fi.feedback
    inner join mdl_feedback_value fv
    on fi.id=fv.item
    where f.name='Encuesta de satisfacción' and fi.typ='multichoice' and fi.label='pregunta9'
    group by fi.id)spregunta9
on spregunta9.course=c.id
LEFT JOIN
    (select f.course,f.id as id_encuesta,fi.id as id_item,fv.id,format((sum(fv.value)*100)/(Count(fv.item)*10), 2)as promedio
    from mdl_feedback f
    inner join mdl_feedback_item fi
    on f.id=fi.feedback
    inner join mdl_feedback_value fv
    on fi.id=fv.item
    where f.name='Encuesta de satisfacción E-learning' and fi.typ='multichoice'
    group by f.course)epromedio
on epromedio.course=c.id
LEFT JOIN
    (select f.course,f.id as id_encuesta,fi.id as id_item,fv.id,format((sum(fv.value)*100)/(Count(fv.item)*10), 2)as promedio
    from mdl_feedback f
    inner join mdl_feedback_item fi
    on f.id=fi.feedback
    inner join mdl_feedback_value fv
    on fi.id=fv.item
    where f.name='Encuesta de satisfacción' and fi.typ='multichoice'
    group by f.course)spromedio
on spromedio.course=c.id
where r.shortname = 'editingteacher' and u.deleted=0 and c.id = '$id'
order by c.id"; 
$result2 = array(); 


if ($datas = $DB->get_records_sql($sql)) {
    foreach($datas as $data) {
        array_push($result,(array)$data);
        $tipo_c = $data->tipo_de_encuesta; 
    }
}

if ($datas2 = $DB->get_records_sql($sql2)) {
    foreach($datas2 as $data2) {
        array_push($result2,(array)$data2);
        $tipo_c = $data2->tipo_de_encuesta; 
    }
}

if($tipo_c == "eLearning"){

    $arraytemp = array(
    'id_auto'=>'id',
    'fecha_activado'=>'Fecha activo',
    'codigo_curso'=>'Código curso',
    'curso'=>'Curso', 
    'modalidad'=>'Modalidad',
    'agenda_de_aprendizaje'=>'Agenda de aprendizaje',
    'tipo_de_curso'=>'Tipo de curso',
	'segmento_dirigido'=>'Segmento dirigido',
    'responsableCOE'=> 'Stakeholders',
    'unidad_de_negocio'=>'Unidad de Negocio',
    'poblacion_dirigida'=>'Población dirigida',
    'pais'=>'País',
    'facilitador'=>'Facilitador',
    'inscritos'=>'Inscritos',
    'asistencia'=>'Asitencia',
    'porcentaje_asistencia'=>'Porcentaje de asistencia',
    'tipo_de_encuesta'=>'Tipo de encuesta',
    'epromedio'=>'Satisfacción Elearning', 
	'E_pregunta1'=>'Los contenidos ofrecidos ¿fueron actuales, precisos y cumplieron con sus expectativas?',
    'E_pregunta2'=>'2. Durante el entrenamiento ¿Se utilizaron diversas actividades interactivas para asegurar el aprendizaje? (videos, casos, selección múltiple, etc.)',
    'E_pregunta3'=>'3. ¿Las herramientas o técnicas proporcionadas son de fácil aplicación en tu puesto de trabajo?', 
    'E_pregunta4'=>'4. ¿Cómo calificarías la plataforma donde se desarrolló la actividad de aprendizaje? (Moodle, etc.)',
    'E_pregunta5'=>'5. ¿Cómo evalúas el acompañamiento recibido de laUcmi (apoyo para accesos a la plataforma, reseteo de contraseña, resolución de dudas) antes y durante la actividad de aprendizaje?'
    );
    
    
    array_unshift($result, $arraytemp);
    // echo '<pre>';
    // print_r($result);
    // echo '</pre>';
    $templatecontext = (object)[
        'users' => $result,
    ];

    // die(); 
    $obj= new Xportxls($templatecontext->users, true);
    $obj->genString(true);

}else if($tipo_c == "Webinar" or $tipo_c == "Presencial"){
    $arraytemp = array(
    'id_auto'=>'id', 
    'fecha_activado'=>'Fecha activo', 
    'codigo_curso'=>'Course Code', 
    'curso'=>'Curso', 
    'modalidad'=>'Modalidad', 
    'agenda_de_aprendizaje'=>'Agenda de aprendizaje', 
    'tipo_de_curso'=>'Tipo de curso',
	'segmento_dirigido'=>'Segmento dirigido', 
    'responsableCOE'=> 'responsableCOE', 
    'unidad_de_negocio'=>'Unidad de negocio',
    'poblacion_dirigida'=>'Poblacion dirigida', 
    'pais'=>'Pais', 
    'facilitador'=>'Facilitador',
    'inscritos'=>'inscritos', 
    'asistencia'=>'Asitencia', 
	'porcentaje_asistencia'=>'Porcentaje de asistencia', 
    'tipo_de_encuesta' => 'Tipo de encuesta',
    'spromedio'=>'Satisfaccion',
	'S_pregunta1'=>'1. Los contenidos ofrecidos ¿fueron actuales, precisos y cumplieron con sus expectativas?', 
    'S_pregunta2'=>'2. Los materiales compartidos durante el entrenamiento ¿fueron de calidad y contribuyeron a alcanzar los objetivos de aprendizaje?',
    'S_pregunta3'=>'3. Durante el entrenamiento ¿Se utilizaron diversas actividades interactivas para asegurar el aprendizaje? (videos, casos, selección múltiple, etc.)',
    'S_pregunta4'=>'4. ¿Las herramientas o técnicas proporcionadas son de fácil aplicación en tu puesto de trabajo?',
    'S_pregunta5'=>'5. ¿Demostró preparación, experiencia, y seguridad en las temáticas desarrolladas, relacionándolas a vivencias y/o experiencias habituales?',
    'S_pregunta6'=>'6. ¿Transmitió el conocimiento de forma clara, con un lenguaje verbal y corporal adecuado para la audiencia?',
	'S_pregunta7'=>'7. ¿Generó un ambiente dinámico y positivo, fomentando la participación por medio de discusión y diálogo?',
    'S_pregunta8'=>'8. ¿Cómo calificarías la plataforma donde se desarrolló la actividad de aprendizaje? (Teams, etc.)',
    'S_pregunta9'=>'9. ¿Cómo evalúas el acompañamiento de laUcmi (apoyo para acceso a Teams, resolución de dudas, atención, tiempos de respuesta, seguimiento, etc.) antes y durante la actividad de aprendizaje?'
    );

    array_unshift($result2, $arraytemp);
    // echo '<pre>';
    // print_r($result2);
    // echo '</pre>';
    $templatecontext = (object)[
        'users' => $result2,
    ];

    // die(); 
    $obj= new Xportxls($templatecontext->users, true);
    $obj->genString(true);
}else{
    echo
    '<script>
        swal({
            title: "Warning!",
            text: "El curso debe tener un tipo de encuesta y un profesor para gestionar el reporte",
            type: "warning"
        }).then(function(){
            window.location = "http://54.161.158.96/my";
        });
    </script>';
}


// array_unshift($result, $arraytemp);
// echo '<pre>';
//   print_r($tipo_c);
// echo '</pre>';
// $templatecontext = (object)[
//  'users' => $result,
// ];
// die(); 
// $obj= new Xportxls($templatecontext->users, true);
// $obj->genString(true);