<?php

/*include("/sistemaweb/valida_sess.php");
include("/sistemaweb/functions.php");
require("/sistemaweb/clases/funciones.php");
$funcion = new class_funciones;
// crea la clase para controlar errores
$clase_error = new OpensoftError;
$clase_error->_error();
// conectar con la base de datos
$conector_id = $funcion->conectar("","","","","");
*/

include_once('/sistemaweb/include/mvc_sistemaweb.php');
include_once('/sistemaweb/include/dbsqlca.php');

$sqlca = new pgsqlDB('localhost','postgres', 'postgres', 'integrado');

//Obtener año y mes de periodo en el sistema
$sql = "SELECT par_valor FROM int_parametros WHERE par_nombre = 'inv_ano_cierre';";
$sqlca->query($sql);
$year_cierre = $sqlca->fetchRow();

$sql = "SELECT par_valor FROM int_parametros WHERE par_nombre = 'inv_mes_cierre';";
$sqlca->query($sql);
$month_cierre = $sqlca->fetchRow();

//Se coloco el if para validar la opcion Registro de compras
if (isset($_POST['accion'])) {
	$iAlmacen	= $_POST['nu_almacen_destino'];
	$dFecha		= $_POST['fecha'];
	$iTurno 	= 0;

	$year 		= substr($dFecha, 6, 4);
	$month 		= substr($dFecha, 3, 2);
	$day = substr($dFecha, 0, 2);

	$arrResponse = array('status' => 'success', 'message' => 'bien');

	//Validar fecha de emision con fecha de inicio de sistema y que no este consolidado el día
	if ($year <= $year_cierre[0] && $month <= $month_cierre[0]) {
		$arrResponse = array('status' => 'danger', 'message' => 'Error: <br/ ><b>Periodo de Inventario Cerrado Año: ' . $year_cierre[0] . ' y Mes: ' . $month_cierre[0]);
	} else {
        $dFecha = $year . '-' . $month . '-' . $day;
		//Validar fecha de emision para verificar si tiene consolidacion
		$sql = "SELECT validar_consolidacion('" . $dFecha . "', " . $iTurno . ",'" . $iAlmacen . "');";
		$sqlca->query($sql);
		$row = $sqlca->fetchRow();
		if ($row["validar_consolidacion"] == '1' )//Consolidado
			$arrResponse = array('status' => 'danger', 'message' => 'Dia consolidado, ingresar otra fecha');
	}

	echo json_encode($arrResponse);
} else {

	$almacen	= $_REQUEST['almacen'];
	$dia		= $_REQUEST['fecha'];
	$turno 		= $_REQUEST['turno'];

	if(empty($turno))
		$turno		= 0;

	if(empty($almacen))
		$almacen	= $_REQUEST['nu_almacen_destino'];

	$year 	= substr($dia, 0, 4);
	$month 	= substr($dia, 5, 2);

	if ($year <= $year_cierre[0] && $month <= $month_cierre[0])
	echo "<blink style='color: red'>Error: <br/ ><b>Periodo de Inventario Cerrado Año: " . $year_cierre[0] . " y Mes: " . $month_cierre[0] . " holi " . $year . " - " . $month . "</b></blink>";
	else{

		$sql = "SELECT validar_consolidacion('" . $dia . "', " . $turno . ",'" . $almacen . "');";

		$sqlca->query($sql);

		$estado = $sqlca->fetchRow();

		if($estado[0] == 1){
			if(!empty($turno))
				echo "<blink style='color: red'> Consolidado - Dia: $dia y turno: $turno </blink>";
			else
				echo "<blink style='color: red'> Dia consolidado, ingresar otra fecha </blink>";
		}else{
			if(empty($turno))
				echo $_REQUEST['fecha'];
		}
	}
}
