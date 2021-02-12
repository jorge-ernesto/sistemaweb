<?php
include("../config.php");
//include("../valida_sess.php");
include("/sistemaweb/combustibles/inc_top.php");
include("functions_ct.php");
require("../clases/funciones.php");



$funcion = new class_funciones;

// crea la clase para controlar errores
$clase_error = new OpensoftError;

// conectar con la base de datos
$coneccion=$funcion->conectar("","","","","");

if ( is_null($almacen) or trim($almacen)=="")
{
	$almacen="001";
}

$estab = $almacen;
if($estab=="")

	{
	?>
	<script language="JavaScript" >
	alert("No se ha registrado el Almacen ");
	</script>
	<?php
	}


verifica_config($almacen);
$xigv=obtieneigv($coneccion);
$xfecha=date("Y/m/d H:i:s");
$xcaja=obtienecaja($coneccion);

if (false)
{
	include("../config.php");
	include("inc_top.php");
	include("functions.php");
	obtieneigv();
	//$tipooperac="ON";   $formpago="1";
	$xfecha=date("Y/m/d H:i:s");
}

$v_sql="select  TAB_ELEMENTO,
		TAB_DESCRIPCION
		from INT_TABLA_GENERAL
		where TAB_TABLA='ALMA' and TAB_ELEMENTO like '%".$almacen."%' ";

$v_xsql=pg_query($coneccion,$v_sql);

if(pg_numrows($v_xsql)>0)
{
	$v_descalma=pg_result($v_xsql,0,1);
}

?>
ALMACEN ORIGEN <?php echo $almacen; ?> - <?php echo $v_descalma; ?>  <BR>
<?php

if($flg=="CT")
	{
		echo $coneccion." - ".$rutaprint." - ".$pc_samba." - ".$prn_samba." - ".$pc_ip;
		cierra_turno_parcial($coneccion, $rutaprint, $pc_samba, $prn_samba, $pc_ip);
	}

elseif($flg=="CD")
	{
		//  eliminar   cierra_turno_parcial($coneccion,$rutaprint,$pc_samba,$prn_samba,$pc_ip);
		cierra_dia($coneccion,$rutaprint,$pc_samba,$prn_samba,$pc_ip);
	}

?>
<html><link rel="stylesheet" href="<?php echo $v_path_url; ?>sistemaweb.css" type="text/css">
<head>
<title>integrado</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script type="text/javascript" language="JavaScript1.2" src="/sistemaweb_market/lib.js"></script>
<script language="javascript">
var miPopup

</script>

</head>
<body>
&nbsp;
TRANSACCIONES DE PUNTO DE VENTA <hr noshade>

<p><a href="grifos_manual.php?flg=CT">Cierre de Turno</a>&nbsp;&nbsp;&nbsp;<a
href="grifos_manual.php?flg=CD">Cierre del Dia</a></p>

</table>
</body>
</html>
<?php pg_close($coneccion); ?>
