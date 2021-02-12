<?php

include("/sistemaweb/valida_sess.php");
include("/sistemaweb/functions.php");
require("/sistemaweb/clases/funciones.php");
$funcion = new class_funciones;
// crea la clase para controlar errores
$clase_error = new OpensoftError;
$clase_error->_error();
// conectar con la base de datos
$conector_id = $funcion->conectar("","","","","");


if($_REQUEST['accion'] == 'aprosys'){

	$hoy = $_REQUEST['dia'];

	$anio = substr($hoy,6,4);
	$mes = substr($hoy,3,2);
	$dia = substr($hoy,0,2);

	$fecha = $anio."-".$mes."-"."$dia";

	$sql = " SELECT ch_poscd FROM pos_aprosys WHERE da_fecha = '$fecha';";

	$sqlca->query($sql);

	$estado = $sqlca->fetchRow();

	if($estado[0] == 'A'){
		echo "<blink style='color: red'>¡ No se ha realizado cierre del día $fecha !</blink>";
		die();
	}

}else if($_REQUEST['accion'] == 'eliminar'){

	$hoy = $_REQUEST['fecha'];

	$anio = substr($hoy,6,4);
	$mes = substr($hoy,3,2);
	$dia = substr($hoy,0,2);

	$fecha = $anio."-".$mes."-"."$dia";

	$ver	= "SELECT COUNT(*) FROM comb_ta_contometros WHERE dt_fechaparte='$fecha';";

	$sqlca->query($ver);

	$datos = $sqlca->fetchRow();

	//var_dump($datos);

	if($datos[0] == "0"){
		echo "Error";
	}else{

		$sql = "DELETE FROM comb_ta_contometros WHERE dt_fechaparte='$fecha';";
		$sql2 = "DELETE FROM inv_movialma WHERE date(mov_fecha)='$fecha' AND tran_codigo IN ('23','24','25');";

		$sqlca->query($sql);
		$sqlca->query($sql2);

		echo "Paso";

	}

}

