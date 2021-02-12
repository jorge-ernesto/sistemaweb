<?php
include("/sistemaweb/valida_sess.php");
include("../menu_princ.php");
include("../functions.php");
include("/sistemaweb/utils/funcion-texto.php");
include("../funcjch.php");
require("../clases/funciones.php");

$funcion 	= new class_funciones;
$clase_error 	= new OpensoftError;
$clase_error->_error();
$coneccion	= $funcion->conectar("","","","","");
$almacen	= "001";


if (!isset($_REQUEST['v_fecha_ini']))
	$_REQUEST['v_fecha_ini'] = date("d/m/Y");
if (!isset($_REQUEST['v_fecha_fin']))
	$_REQUEST['v_fecha_fin'] = date("d/m/Y");
if (!isset($_REQUEST['v_turno_ini']))
	$_REQUEST['v_turno_ini'] = 0;
if (!isset($_REQUEST['v_turno_fin']))
	$_REQUEST['v_turno_fin'] = 0;


$desde_dia = substr($_REQUEST['v_fecha_ini'], 0, 2);
$desde_mes = substr($_REQUEST['v_fecha_ini'], 3, 2);
$desde_ano = substr($_REQUEST['v_fecha_ini'], 6, 4);
$hasta_dia = substr($_REQUEST['v_fecha_fin'], 0, 2);
$hasta_mes = substr($_REQUEST['v_fecha_fin'], 3, 2);
$hasta_ano = substr($_REQUEST['v_fecha_fin'], 6, 4);

$anomes=$desde_ano.$desde_mes;
$pos_trans="pos_trans".pg_escape_string($anomes);

?>

<html><link rel="stylesheet" href="<?php echo $v_path_url; ?>sistemaweb.css" type="text/css">
<head>
<title>DESCUENTOS ESPECIALES</title>
<script language="JavaScript" src="js/miguel.js"></script>
<script language="JavaScript" src="/sistemaweb/js/calendario.js"></script>
<script language="JavaScript" src="/sistemaweb/js/overlib_mini.js"></script>
<script language="JavaScript" src="/sistemaweb/clases/reloj.js"></script>
<script language="JavaScript" src="/sistemaweb/compras/validacion.js"></script>
<script language="JavaScript" src="/sistemaweb/compras/valfecha.js"></script>
</head>

<body><div align="center">
<form name="f_repo" method="post">

DESCUENTOS ESPECIALES
<table border="1">
	<tr>
		<th colspan="3">Reporte Por : RANGO DE FECHAS </th>
	</tr>
	<tr>
		<td>INICIO :</th>
		<td>
			<p><input type="text" name="v_fecha_ini" size="16" maxlength="10" value='<?php echo $_REQUEST['v_fecha_ini'] ; ?>'  tabindex="1"  onKeyUp="javascript:validarFecha(this)" onblur="javascript:validarFecha(this)"  >		
			<a href="javascript:show_calendar('f_repo.v_fecha_ini');"><img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a><div id="overDiv" style="position:absolute; visibility:hidden; z-index:3;">
			</p>
		</td>
		<td>Turno: <input type="text" name="v_turno_ini" value="<?php echo htmlentities($_REQUEST['v_turno_ini']); ?>" size="1"></td>
	</tr>
	<tr>
		<td>FIN :</td>
		<td>
			<p><input type="text" name="v_fecha_fin" size="16" maxlength="10" value='<?php echo $_REQUEST['v_fecha_fin'] ; ?>'  tabindex="3" onKeyUp="javascript:validarFecha(this)" onblur="javascript:validarFecha(this)">			
			<a href="javascript:show_calendar('f_repo.v_fecha_fin');"><img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>
			</p>
		</td>
		<td>Turno: <input type="text" name="v_turno_fin" value="<?php echo htmlentities($_REQUEST['v_turno_fin']); ?>" size="1"></td>
	</tr>
	<tr>
		<td colspan="3"><p align="center"><input type="submit" name="action" value="Mostrar"></p></td>
	</tr>	
</table>
</form>

<?php

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "Mostrar") {

	$sql = "SELECT DISTINCT
			CASE WHEN t.td='B' THEN 'Boleta' ELSE 'Factura' END AS td,
			t.dia AS dia,
			t.turno AS turno,
	        	t.fecha AS fecha,
	        	t.caja AS caja,
	        	t.trans AS trans,
	        	t.importe AS importe,
	        	a.art_descripcion AS descripcion,
	        	t.ruc AS ruc,
	        	r.razsocial AS razsocial
	    	FROM
	        	" . $pos_trans . " t
	        	RIGHT JOIN int_articulos a ON (a.art_codigo=t.codigo)
	        	LEFT JOIN ruc r ON (r.ruc=t.ruc)
	    	WHERE
			t.tipo='C' AND tm = 'V' AND (importe < 0 OR grupo='D') ";

	if ($_REQUEST['v_turno_ini'] > 0) {
		$sql .= "AND ((t.dia= '" . pg_escape_string($desde_ano) . '-' . pg_escape_string($desde_mes) . '-' . pg_escape_string($desde_dia) . "' AND t.turno>=" . pg_escape_string($_REQUEST['v_turno_ini']) . ") ";
		$sql .= "OR t.dia> '" . pg_escape_string($desde_ano) . '-' . pg_escape_string($desde_mes) . '-' . pg_escape_string($desde_dia) . "') ";
	} else {
		$sql .= "AND t.dia>= '" . pg_escape_string($desde_ano) . '-' . pg_escape_string($desde_mes) . '-' . pg_escape_string($desde_dia) . "' ";
	}
    
	if ($_REQUEST['v_turno_fin'] > 0) {
		$sql .= "AND ((t.dia= '" . pg_escape_string($hasta_ano) . '-' . pg_escape_string($hasta_mes) . '-' . pg_escape_string($hasta_dia) . "' AND t.turno<=" . pg_escape_string($_REQUEST['v_turno_fin']) . ") ";
		$sql .= "OR t.dia< '" . pg_escape_string($hasta_ano) . '-' . pg_escape_string($hasta_mes) . '-' . pg_escape_string($hasta_dia) . "') ";
	} else {
		$sql .= "AND t.dia<= '" . pg_escape_string($hasta_ano) . '-' . pg_escape_string($hasta_mes) . '-' . pg_escape_string($hasta_dia) . "' ";
	}

	$sql .= " ORDER BY t.fecha ;";
	
	//print_r($sql);

	$v_result = pg_exec($coneccion, $sql);
	$rs = pg_fetch_all($v_result);

?>
<table border="1">
	<tr bgcolor="#D9F9B2" align="center">
		<td>T. Doc.</td>
		<td>Fecha Sistema</td>
		<td>Turno</td>
		<td>Fecha Emision</td>
		<td>Pto. Vta.</td>
		<td>Transaccion</td>
		<td>Importe Dscto.</td>
		<td>Descripcion</td>
		<td>RUC</td>
		<td>Razon Social</td>
	</tr>
<?php

$total = 0;
    
foreach ($rs as $fila) {
	$td 		= $fila['td'];
	$dia 		= substr($fila['dia'], 0, 10);
	$turno 		= $fila['turno'];
	$fecha 		= $fila['fecha'];
	$ptoventa 	= $fila['caja'];
	$transa 	= $fila['trans'];
	$importe 	= $fila['importe'];

	if($fila['importe'] > 0){
		$incremento = $incremento + $fila['importe'];
	}
	$descripcion = $fila['descripcion'];
	$ruc 	     = $fila['ruc'];
	$razsocial   = $fila['razsocial'];
	
	$total += $importe;
?>
	<tr>
		<td><?php echo htmlentities($td); ?></td>
		<td><?php echo htmlentities($dia); ?></td>
		<td><?php echo htmlentities($turno); ?></td>
		<td><?php echo htmlentities($fecha); ?></td>
		<td><?php echo htmlentities($ptoventa); ?></td>
		<td><?php echo htmlentities($transa); ?></td>
		<td><p align="right"><?php echo htmlentities($importe); ?></p></td>
		<td><?php echo htmlentities($descripcion); ?></td>
		<td><?php echo htmlentities($ruc); ?></td>
		<td><?php echo htmlentities($razsocial); ?></td>
	</tr>

<?php	
    	}
} 
?>

	<tr bgcolor="#D9FFFF">
		<td colspan="6"><B>Total Incrementos</B></td>
		<td><p align="right"><?php echo htmlentities($incremento); ?></p></td>
		<td colspan="3">&nbsp;</td>
	</tr>
	<tr bgcolor="#D9FFFF">
		<td colspan="6"><B>Total Descuentos</B></td>
		<td><p align="right"><?php echo htmlentities($total-$incremento); ?></p></td>
		<td colspan="3">&nbsp;</td>
	</tr>
	<!--<tr>
		<td colspan="6"><B>T O T A L</B></td>
		<td><p align="right"><?php echo htmlentities($total); ?></p></td>
		<td colspan="3">&nbsp;</td>
	</tr>-->
</table>
</div>
</body>
</html>
<?php
pg_close($coneccion);
