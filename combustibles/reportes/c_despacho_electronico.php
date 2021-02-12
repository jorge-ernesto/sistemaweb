<?php
session_start();
include_once('/sistemaweb/include/dbsqlca.php');
$sqlca = new pgsqlDB('localhost', 'postgres', 'postgres', 'integrado');
include('/sistemaweb/include/mvc_sistemaweb.php');
include('t_despacho_electronico.php');
include('m_despacho_electronico.php');
//************

$objmodel = new depacho_electronico_Model();
$objtem = new depacho_electronico_Template();

$accion = $_REQUEST['accion'];
$_SESSION['data_excel']=null;
try {
	if ($accion == "fecha_servidor") {
		$fecha_find = $_REQUEST['fecha_inicio'];
		$info_turno_fecha = depacho_electronico_Model:: ObtenerFechaDTurno($fecha_find);
		$cadena = "";
		foreach ($info_turno_fecha[0] as $fila) {
			$fecha=$fila['fecha'];
			$cadena.='<option value="'.$fecha.'">Cierre de turno final Anterior (' . $fila['turno'] . ')   ==>  ' . $fila['fecha'] . '</option>';
		}

		foreach ($info_turno_fecha[1] as $fila) {
			$fecha=$fila['fecha'];
			$cadena.='<option value="'.$fecha.'">Cierre de turno Actual <b> (' . $fila['turno'] . ')</b>   ==>  ' . $fila['fecha'] . '</option>';
		}
		echo "{'msg':'" . $cadena . "'}";
	} else if ($accion == "find_grade") {
		$id_pump = $_REQUEST['f_pump_id'];
		$datars = depacho_electronico_Model::ObtenerMangueras($id_pump);
		$cadena = '<option value=00>00</option>';
		foreach ($datars as $fila) {
			$lado_grade_id=  str_pad($fila["f_grade_id"], 2,"0",STR_PAD_LEFT);
			$cadena.='<option value=' . $lado_grade_id. '>' . $fila["name"] . '</option>';
		}
		echo "{'msg':'" . $cadena . "'}";
	} else if ($accion == "executar_reporte") {
		$fecha_ini = $_REQUEST['fecha_ini'];
		$fecha_fin = $_REQUEST['fecha_fin'];
		$lado = $_REQUEST['lado'];
		$manguera = $_REQUEST['manguera'];
		$datars = depacho_electronico_Model::ObtenerReporte($fecha_ini, $fecha_fin, $lado, $manguera);
		depacho_electronico_Template::CrearTablaReporte($datars);
	}

	else if ($accion == "executar_reporte_excel") {
		$fecha_ini = $_REQUEST['fecha_ini'];
		$fecha_fin = $_REQUEST['fecha_fin'];
		$lado = $_REQUEST['lado'];
		$manguera = $_REQUEST['manguera'];
		$datars = depacho_electronico_Model::ObtenerReporte($fecha_ini, $fecha_fin, $lado, $manguera);
		$_SESSION['data_excel']=$datars;
	}
} catch (Exception $r) {
	echo "<b style='color:red;with:18px;'>".$r->getMessage()."</b>";
}
