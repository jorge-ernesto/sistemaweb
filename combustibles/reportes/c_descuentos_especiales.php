<?php

session_start();
include_once('/sistemaweb/include/dbsqlca.php');
$sqlca = new pgsqlDB('localhost', 'postgres', 'postgres', 'integrado');
include('/sistemaweb/include/mvc_sistemaweb.php');

include('t_descuentos_especiales.php');
include('m_descuentos_especiales.php');

$objmodel 	= new Descuentos_Especiales_Model();
$objtem 	= new Descuentos_Especiales_Template();

$accion 		= $_REQUEST['accion'];
$_SESSION['data_excel'] = null;
$almacen = null;

try {

	if($accion == "ActualizarPagos") {

		$fecha		= $_REQUEST['fecha_inicial'];

		if($_REQUEST['almacen'] == '')
			$almacen	= $_SESSION['almacen'];
		else
			$almacen	= $_REQUEST['almacen'];

		$turnos 	= Descuentos_Especiales_Model::ObtenerFechaDTurno($fecha);
		// $cajas		= Descuentos_Especiales_Model::ObtenerCajas($almacen);
		$cajas		= Descuentos_Especiales_Model::ObtenerCajas($almacen, $fecha);
		$lados		= Descuentos_Especiales_Model::SearchPump($almacen, $fecha); //SearchPump
		$turnoa 	= 0;

		$cadena.='<option value="">Todos los turnos</option>';

		for($i = 0; $i < $turnos[0]['turno']; $i++){

			if($turnoa < $turnos[0]['turno'])
				$turnoa = $turnoa + 1;
  
			$cadena.='<option value="'.$turnoa.'">' . $turnoa . '</option>';

		}

		$cadena2.='<option value="">Todas las cajas</option>';
		foreach($cajas as $fila) {

			$caja = $fila['name'];       
			$cadena2.='<option value="'.$caja.'">' . $fila['name'] . '</option>';

		}

		$cadena3.='<option value="">Todos los lados</option>';
		foreach($lados as $fila) {

			$lado = $fila['pump'];       
			$cadena3.='<option value="'.$lado.'">' . $fila['pump'] . '</option>';

		}

		echo "{'msg':'" . $cadena . "', 'msg2':'" . $cadena2 . "', 'msg3' : '".$cadena3."'}";

	} else if($accion == "TipoCambio") {

		$fecha		= $_REQUEST['fecha'];
		$tipomoneda	= $_REQUEST['tipomoneda'];

		$tc	= Descuentos_Especiales_Model::ObtenerTipoCambio($fecha, $tipomoneda);

		echo $tc[0][0];//PARA MOSTRAR EL TIPO DE CAMBIO EN FACTURAS DE VENTAS

	} else if($accion == "TipoCambioCompra") {

		$fecha		= $_REQUEST['fecha'];
		$tipomoneda	= $_REQUEST['tipomoneda'];

		$tc	= Descuentos_Especiales_Model::ObtenerTipoCambioCompra($fecha, $tipomoneda);

		echo $tc[0][0];//PARA MOSTRAR EL TIPO DE CAMBIO EN FACTURAS DE COMPRAS

	}  else if($accion == "Correlativo") {

		$fecha		= $_REQUEST['fecha'];

		$numerator	= Descuentos_Especiales_Model::Correlativo($fecha);

		echo $numerator;//PARA MOSTRAR EL TIPO DE CAMBIO EN FACTURAS DE COMPRAS

	} else if($accion == "fecha_servidor") {

		if(!empty($_REQUEST['fecha_inicial']))
			$fecha	= $_REQUEST['fecha_inicial'];
		else
			$fecha	= $_REQUEST['fecha_final'];

		$turnos 	= Descuentos_Especiales_Model::ObtenerFechaDTurno($fecha);
		$cadena 	= "";
		$turnoa 	= 0;

		for($i = 0; $i < $turnos[0]['turno']; $i++){

			if($turnoa < $turnos[0]['turno'])
				$turnoa = $turnoa + 1;
  
			$cadena.='<option value="'.$turnoa.'">' . $turnoa . '</option>';

		}

		echo "{'msg':'" . $cadena . "'}";

	} else if ($accion == "buscar") {

		$almacen 	= $_REQUEST['almacen'];
		$fdesde	 	= $_REQUEST['fecha_ini'];
		$fhasta	 	= $_REQUEST['fecha_fin'];
		$tdesde 	= $_REQUEST['turno_ini'];
		$thasta 	= $_REQUEST['turno_fin'];
		$tv 		= $_REQUEST['tv'];
		$td 		= $_REQUEST['td'];
		$tarjeta 	= $_REQUEST['tarjeta'];

		$datars 	= Descuentos_Especiales_Model::ObtenerReporte($almacen, $fdesde, $fhasta, $tdesde, $thasta, $tv, $td, $tarjeta);

		Descuentos_Especiales_Template::CrearTablaReporte($datars);

	} else if ($accion == "agregar") {

		Descuentos_Especiales_Template::AgregarDescuento();

	} else if ($accion == "guardar") {

		$data = Descuentos_Especiales_Model::SearchTransaction($_REQUEST);

		Descuentos_Especiales_Model::BEGINTransaccion();

		if(!empty($data)){//$a=false; var_dump( empty($a) );    #bool(true)

			$data = Descuentos_Especiales_Model::GuardarDescuento($data, $_REQUEST["nuprecio"]);

			if($data)
				Descuentos_Especiales_Model::COMMITransaccion();
			else
				Descuentos_Especiales_Model::ROLLBACKTransaccion();

		}

	} else if ($accion == "excel") {

		$almacen 	= $_REQUEST['almacen'];
		$fdesde	 	= $_REQUEST['fecha_ini'];
		$fhasta	 	= $_REQUEST['fecha_fin'];
		$tdesde 	= $_REQUEST['turno_ini'];
		$thasta 	= $_REQUEST['turno_fin'];
		$tv 		= $_REQUEST['tv'];
		$td 		= $_REQUEST['td'];
		$tarjeta 	= $_REQUEST['tarjeta'];

		$resultados 	= Descuentos_Especiales_Model::ObtenerReporte($almacen, $fdesde, $fhasta, $tdesde, $thasta, $tv, $td, $tarjeta);

		if(!empty($resultados))
			$_SESSION['data_excel'] = $resultados;

	} else if ($accion == "Obtener") {
		$datars 	= RegistroVentasModel::busqueda();
	} else if ($accion == "Search") {
		$data 	= Descuentos_Especiales_Model::searchForKeyword($_REQUEST['keyword']);
        	echo json_encode($data);
	} else if ($accion == "GetProductCode") {
		$data 	= Descuentos_Especiales_Model::GetProductCode($_REQUEST);
        	echo json_encode($data);
	}

} catch (Exception $r) {
	echo $r->getMessage();
}

