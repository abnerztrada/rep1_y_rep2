<?php
require_once(__DIR__ . '/../../config.php');

require_once('Xportxls.php');
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$id = $_GET['id'];

//Query que se manda en excel por el correo electronico.
$sql=("select 
@s:=@s + 1 id,
DATE_FORMAT(DATE_ADD(FROM_UNIXTIME(c.startdate, '%Y-%m-%d'), INTERVAL 1 HOUR),'%d/%m/%Y') AS fecha_activado,
if(codigo.codigo is null, 'Sin dato', codigo.codigo) as codigo_curso,
c.fullname as curso,
if(modalidad.modalidad is null, 'Sin dato', modalidad.modalidad) as modalidad,
if(aprendizaje.aprendizaje is null, 'Sin dato', aprendizaje.aprendizaje) as agenda_de_aprendizaje,
if(tipocurso.tipocurso is null, 'Sin dato', tipocurso.tipocurso) as tipo_de_curso,
if(dirigido.dirigido is null, 'Sin dato', dirigido.dirigido) as segmento_dirigido,
if(poblacion.poblacion is null, 'Sin dato', poblacion.poblacion) as poblacion_dirigida,
pais.pais,
u.username as codigopg,
concat(u.firstname,' ',u.lastname) as facilitador,
(CASE 
WHEN unidadd.data is null THEN 'Sin dato'
WHEN unidadd.data = '' THEN 'Sin dato'
WHEN unidadd.data = 'MM' THEN 'Alimentos'
WHEN unidadd.data = 'RCA' THEN 'Alimentos'
WHEN unidadd.data = 'B4B' THEN 'Alimentos'
WHEN unidadd.data = 'A&C' THEN 'Alimentos'
WHEN unidadd.data = 'IP' THEN 'Alimentos'
WHEN unidadd.data = 'MP' THEN 'Capital'
WHEN unidadd.data = 'FI' THEN 'Capital'
WHEN unidadd.data = 'EN' THEN 'Capital'
WHEN unidadd.data = 'CSI' THEN 'Centros Corporativos'
WHEN unidadd.data = 'CORP' THEN 'Centros Corporativos'
END) as agrupacion, 
if(unidadd.data is null, 'Sin dato', if(unidadd.data = '', 'Sin dato', unidadd.data)) as un,
u.email as correo,
if(hrbps.data is null, 'Sin dato', if(hrbps.data = '', 'Sin dato', hrbps.data)) as hrbps, 
if(stakeholder.nombre is null, 'Sin dato', stakeholder.nombre) as hrbp,
if(seccion.seccion is null, 'Sin dato', if(seccion.seccion = '', 'Sin dato', seccion.seccion)) as seccion,
CASE when completocriterio.num=completoecho.num2 THEN '1' else '.0' END as asistencia,
If(notas.finalgrade<=0 or notas.finalgrade is null, if(completocriterio.num=completoecho.num2, '100','0.0'), notas.finalgrade ) as nota,
if(complete.timecompleted is null,  'No finalizado', DATE_FORMAT(DATE_ADD(FROM_UNIXTIME(complete.timecompleted, '%Y-%m-%d %H:%i'), INTERVAL 1 HOUR),'%d/%m/%Y %H:%i'))AS fecha_finalizacion
        from  (select @s:=0) as s, mdl_course c
        INNER JOIN mdl_context con on con.instanceid = c.id
        INNER JOIN mdl_role_assignments as asg on asg.contextid = con.id
        INNER JOIN mdl_user as u on asg.userid = u.id
        INNER JOIN mdl_role r on asg.roleid = r.id
LEFT JOIN 
        (SELECT c.id as courseid, u.id as userid,u.username, cps.timecompleted, cps.course
	FROM mdl_course c 
        LEFT JOIN mdl_course_completions cps on cps.course = c.id 
        LEFT JOIN mdl_user u on u.id = cps.userid ) complete
	ON complete.userid = u.id and complete.courseid=c.id
LEFT JOIN
	(select u.id as userid, u.username, ud.data 
        from mdl_user u
	INNER JOIN mdl_user_info_data ud ON ud.userid = u.id
	INNER JOIN mdl_user_info_field uf ON uf.id = ud.fieldid 
	where uf.name = 'unidad') unidadd
        ON unidadd.userid = u.id
LEFT JOIN
        (select u.id as userid, u.username, ud.data from mdl_user u
        INNER JOIN mdl_user_info_data ud ON ud.userid = u.id
        INNER JOIN mdl_user_info_field uf ON uf.id = ud.fieldid 
        where uf.name = 'hrbp') hrbps
        ON hrbps.userid = u.id
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
        WHEN cd.value = 4 THEN 'Escuela t??cnica'
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
        WHEN cd.value = 2 THEN 'Alimentos'
        WHEN cd.value = 3 THEN 'Capital'
        WHEN cd.value = 4 THEN 'Centros Corporativos'
        end) as agrupacion
        FROM mdl_course c
        INNER JOIN mdl_customfield_data cd
        ON c.id=cd.instanceid
        INNER JOIN mdl_customfield_field cf
        ON cf.id=cd.fieldid
        WHERE cf.shortname='agrupacion') agrupacion
	ON agrupacion.instanceid=c.id
LEFT JOIN
        (SELECT cd.value AS codigo, cd.fieldid, cd.instanceid,(CASE WHEN cd.value = 0 THEN 'Sin dato'
	WHEN cd.value = 1 THEN 'No aplica'
        WHEN cd.value = 2 THEN 'Afganist??n'
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
        WHEN cd.value = 14 THEN 'Azerbaiy??n'
        WHEN cd.value = 15 THEN 'Bahamas'
        WHEN cd.value = 16 THEN 'Banglad??s'
        WHEN cd.value = 17 THEN 'Barbados'
        WHEN cd.value = 18 THEN 'Bar??in'
        WHEN cd.value = 19 THEN 'B??lgica'
        WHEN cd.value = 20 THEN 'Belice'
        WHEN cd.value = 21 THEN 'Ben??n'
        WHEN cd.value = 22 THEN 'Bielorrusia'
        WHEN cd.value = 23 THEN 'Birmania'
        WHEN cd.value = 24 THEN 'Bolivia'
        WHEN cd.value = 25 THEN 'Bosnia y Herzegovina'
        WHEN cd.value = 26 THEN 'Botsuana'
        WHEN cd.value = 27 THEN 'Brasil'
        WHEN cd.value = 28 THEN 'Brun??i'
        WHEN cd.value = 29 THEN 'Bulgaria'
        WHEN cd.value = 30 THEN 'Burkina Faso'
        WHEN cd.value = 31 THEN 'Burundi'
        WHEN cd.value = 32 THEN 'But??n'
        WHEN cd.value = 33 THEN 'Cabo Verde'
        WHEN cd.value = 34 THEN 'Camboya'
        WHEN cd.value = 35 THEN 'Camer??n'
        WHEN cd.value = 36 THEN 'Canad??'
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
        WHEN cd.value = 56 THEN 'Emiratos ??rabes Unidos'
        WHEN cd.value = 57 THEN 'Eritrea'
        WHEN cd.value = 58 THEN 'Eslovaquia'
        WHEN cd.value = 59 THEN 'Eslovenia'
        WHEN cd.value = 60 THEN 'Espa??a'
        WHEN cd.value = 61 THEN 'Estados Unidos'
        WHEN cd.value = 62 THEN 'Estonia'
        WHEN cd.value = 63 THEN 'Etiop??a'
        WHEN cd.value = 64 THEN 'Filipinas'
        WHEN cd.value = 65 THEN 'Finlandia'
        WHEN cd.value = 66 THEN 'Fiyi'
        WHEN cd.value = 67 THEN 'Francia'
        WHEN cd.value = 68 THEN 'Gab??n'
        WHEN cd.value = 69 THEN 'Gambia'
        WHEN cd.value = 70 THEN 'Georgia'
        WHEN cd.value = 71 THEN 'Ghana'
        WHEN cd.value = 72 THEN 'Granada'
        WHEN cd.value = 72 THEN 'Grecia'
        WHEN cd.value = 74 THEN 'Guatemala'
        WHEN cd.value = 75 THEN 'Guyana'
        WHEN cd.value = 76 THEN 'Guinea'
        WHEN cd.value = 77 THEN 'Guinea ecuatorial'
        WHEN cd.value = 78 THEN 'Guinea-Bis??u'
        WHEN cd.value = 79 THEN 'Hait??'
        WHEN cd.value = 80 THEN 'Honduras'
        WHEN cd.value = 81 THEN 'Hungr??a'
        WHEN cd.value = 82 THEN 'India'
        WHEN cd.value = 83 THEN 'Indonesia'
        WHEN cd.value = 84 THEN 'Irak'
        WHEN cd.value = 85 THEN 'Ir??n'
        WHEN cd.value = 86 THEN 'Irlanda'
        WHEN cd.value = 87 THEN 'Islandia'
        WHEN cd.value = 88 THEN 'Islas Marshall'
        WHEN cd.value = 89 THEN 'Islas Salom??n'
        WHEN cd.value = 90 THEN 'Israel'
        WHEN cd.value = 91 THEN 'Italia'
        WHEN cd.value = 92 THEN 'Jamaica'
        WHEN cd.value = 93 THEN 'Jap??n'
        WHEN cd.value = 94 THEN 'Jordania'
        WHEN cd.value = 95 THEN 'Kazajist??n'
        WHEN cd.value = 96 THEN 'Kenia'
        WHEN cd.value = 97 THEN 'Kirguist??n'
        WHEN cd.value = 98 THEN 'Kiribati'
        WHEN cd.value = 99 THEN 'Kuwait'
        WHEN cd.value = 100 THEN 'Laos'
        WHEN cd.value = 101 THEN 'Lesoto'
        WHEN cd.value = 102 THEN 'Letonia'
        WHEN cd.value = 103 THEN 'L??bano'
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
        WHEN cd.value = 114 THEN 'Mal??'
        WHEN cd.value = 115 THEN 'Malta'
        WHEN cd.value = 116 THEN 'Marruecos'
        WHEN cd.value = 117 THEN 'Mauricio'
        WHEN cd.value = 118 THEN 'Mauritania'
        WHEN cd.value = 119 THEN 'M??xico'
        WHEN cd.value = 120 THEN 'Micronesia'
        WHEN cd.value = 121 THEN 'Moldavia'
        WHEN cd.value = 122 THEN 'M??naco'
        WHEN cd.value = 123 THEN 'Mongolia'
        WHEN cd.value = 124 THEN 'Montenegro'
        WHEN cd.value = 125 THEN 'Mozambique'
        WHEN cd.value = 126 THEN 'Namibia'
        WHEN cd.value = 127 THEN 'Nauru'
        WHEN cd.value = 128 THEN 'Nepal'
        WHEN cd.value = 129 THEN 'Nicaragua'
        WHEN cd.value = 130 THEN 'N??ger'
        WHEN cd.value = 131 THEN 'Nigeria'
        WHEN cd.value = 132 THEN 'Noruega'
        WHEN cd.value = 133 THEN 'Nueva Zelanda'
        WHEN cd.value = 134 THEN 'Om??n'
        WHEN cd.value = 135 THEN 'Pa??ses Bajos'
        WHEN cd.value = 136 THEN 'Pakist??n'
        WHEN cd.value = 137 THEN 'Palaos'
        WHEN cd.value = 138 THEN 'Panam??'
        WHEN cd.value = 139 THEN 'Pap??a Nueva Guinea'
        WHEN cd.value = 140 THEN 'Paraguay'
        WHEN cd.value = 141 THEN 'Per??'
        WHEN cd.value = 142 THEN 'Polonia'
        WHEN cd.value = 143 THEN 'Portugal'
        WHEN cd.value = 144 THEN 'Reino Unido'
        WHEN cd.value = 145 THEN 'Rep??blica Centroafricana'
        WHEN cd.value = 146 THEN 'Rep??blica Checa'
        WHEN cd.value = 147 THEN 'Rep??blica del Congo'
        WHEN cd.value = 148 THEN 'Rep??blica Democr??tica del Congo'
        WHEN cd.value = 149 THEN 'Rep??blica Dominicana'
        WHEN cd.value = 150 THEN 'Ruanda'
        WHEN cd.value = 151 THEN 'Ruman??a'
        WHEN cd.value = 152 THEN 'Rusia'
        WHEN cd.value = 153 THEN 'Samoa'
        WHEN cd.value = 154 THEN 'San Crist??bal y Nieves'
        WHEN cd.value = 155 THEN 'San Marino'
        WHEN cd.value = 156 THEN 'San Vicente y las Granadinas'
        WHEN cd.value = 157 THEN 'Santa Luc??a'
        WHEN cd.value = 158 THEN 'Santo Tom?? y Pr??ncipe'
        WHEN cd.value = 159 THEN 'Senegal'
        WHEN cd.value = 160 THEN 'Serbia'
        WHEN cd.value = 161 THEN 'Seychelles'
        WHEN cd.value = 162 THEN 'Sierra Leona'
        WHEN cd.value = 163 THEN 'Singapur'
        WHEN cd.value = 164 THEN 'Siria'
        WHEN cd.value = 165 THEN 'Somalia'
        WHEN cd.value = 166 THEN 'Sri Lanka'
        WHEN cd.value = 167 THEN 'Suazilandia'
        WHEN cd.value = 168 THEN 'Sud??frica'
        WHEN cd.value = 169 THEN 'Sud??n'
        WHEN cd.value = 170 THEN 'Sud??n del Sur'
        WHEN cd.value = 171 THEN 'Suecia'
        WHEN cd.value = 172 THEN 'Suiza'
        WHEN cd.value = 172 THEN 'Surinam'
        WHEN cd.value = 174 THEN 'Tailandia'
        WHEN cd.value = 175 THEN 'Tanzania'
        WHEN cd.value = 176 THEN 'Tayikist??n'
        WHEN cd.value = 177 THEN 'Timor Oriental'
        WHEN cd.value = 178 THEN 'Togo'
        WHEN cd.value = 179 THEN 'Tonga'
        WHEN cd.value = 180 THEN 'Trinidad y Tobago'
        WHEN cd.value = 181 THEN 'T??nez'
        WHEN cd.value = 182 THEN 'Turkmenist??n'
        WHEN cd.value = 183 THEN 'Turqu??a'
        WHEN cd.value = 184 THEN 'Tuvalu'
        WHEN cd.value = 185 THEN 'Ucrania'
        WHEN cd.value = 186 THEN 'Uganda'
        WHEN cd.value = 187 THEN 'Uruguay'
        WHEN cd.value = 188 THEN 'Uzbekist??n'
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
        group by c.id)stakeholder
	on stakeholder.course_id=c.id
LEFT JOIN
        (SELECT cd.value AS seccion, cd.fieldid, cd.instanceid
        FROM mdl_course c
        INNER JOIN mdl_customfield_data cd
        ON c.id=cd.instanceid
        INNER JOIN mdl_customfield_field cf
        ON cf.id=cd.fieldid
        WHERE cf.shortname='seccion') seccion
	ON seccion.instanceid=c.id
left join
	(Select count(id)as num,course from mdl_course_completion_criteria
    	group by course) completocriterio
	on c.id=completocriterio.course
left join
	(Select userid,course,count(id)as num2 from mdl_course_completion_crit_compl
    	group by course, userid) completoecho
	on completoecho.userid=u.id and completoecho.course=c.id
left join
	(Select gi.courseid,gg.userid,gg.finalgrade
        from mdl_grade_items gi
        inner join mdl_grade_grades gg
        on gg.itemid=gi.id
        where gi.itemtype='course')as notas
	on u.id=notas.userid and c.id=notas.courseid
        
        where r.shortname = 'student' and u.deleted=0 and c.id = '$id'
        order by c.id");

/*Concatenar los stakeholders */
// select c.id as course_id, c.fullname,r.shortname, GROUP_CONCAT(concat(u.firstname, ' ', u.lastname)) as nombre,u.email
// from mdl_course c
// INNER JOIN mdl_context con on con.instanceid = c.id
// INNER JOIN mdl_role_assignments as asg on asg.contextid = con.id
// INNER JOIN mdl_user as u on asg.userid = u.id
// INNER JOIN mdl_role r on asg.roleid = r.id 
// where  r.shortname = 'stakeholder'
// group by c.id

$result = array();

//Campos que se ven en el excel.
$arraytemp = array('id_auto'=>'id',
'fecha_activado'=>'Fecha activado', 
'codigo_curso'=>'C??digo curso', 
'curso'=>'Curso', 
'modalidad'=>'Modalidad', 
'agenda_de_aprendizaje'=>'Agenda de aprendizaje', 
'tipo_de_curso'=>'Tipo de curso', 
'segmento_dirigido'=>'Segmento dirigido', 
'poblacion_dirigida'=>'Poblaci??n dirigida', 
'pais'=>'Pa??s',
'codigopg'=> 'C??digo PG', 
'facilitador'=>'Estudiante', 
'agrupacion'=>'Agrupaci??n', 
'un'=>'UN', 
'correo'=>'Email', 
'hrbps'=>'HRBP', 
'hrbp'=>'Stakeholder', 
'seccion'=>'Secci??n', 
'asistencia'=>'Asistencia', 
'nota'=>'Nota', 
'fecha_finalizacion'=>'Fecha finalizaci??n'
);

//
if ($datas = $DB->get_records_sql($sql)) {
    foreach($datas as $data) {
        array_push($result,(array)$data);
    }
}

array_unshift($result, $arraytemp);


$templatecontext = (object)[

    'users' => $result,
];
// die();
$obj= new Xportxls($templatecontext->users, true);
$obj->genString(true);
