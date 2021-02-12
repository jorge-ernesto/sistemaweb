<?php

class StockTurnoController extends Controller {

    	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action']) ? $this->action = $_REQUEST['action']:$this->action='';
    	}
    
	function Run() {
		include 'movimientos/m_stockxturno.php';
		include 'movimientos/t_stockxturno.php';
		include('../include/paginador_new.php');
	
		$this->Init();
		$result = '';
		$result_f = '';
		$agregar = false;
		$buscar = false;

		global $usuario;

	      	if(!isset($_REQUEST['rxp'],$_REQUEST['pagina'])) {
			$_REQUEST['rxp'] = 30;
		 	$_REQUEST['pagina'] = 1;
	      	}


		switch ($this->action) {

			case "Buscar":

				$busqueda    	= StockTurnoModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina'],$_REQUEST['desde'], $_REQUEST['hasta']);

				if($busqueda == ''){
					$vec 		= array($_REQUEST['desde'], $_REQUEST['hasta']);
					$result     	= StockTurnoTemplate::formPag($busqueda['paginacion'],$vec,"","");
					$result_f 	= StockTurnoTemplate::reporte($busqueda['datos'],$_REQUEST['desde'], $_REQUEST['hasta']);
				}else{
					$busqueda    	= StockTurnoModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina'],$_REQUEST['desde'], $_REQUEST['hasta']);
					$vec 		= array($_REQUEST['desde'], $_REQUEST['hasta']);
					$result     	= StockTurnoTemplate::formPag($busqueda['paginacion'],$vec);
					$result_f 	= StockTurnoTemplate::reporte($busqueda['datos'], "", "");
				}

				break;


			case "Nuevo Stock":

				$almacenes = StockTurnoModel::obtenerEstaciones();
				$fechas_sistema = StockTurnoModel::obtenerFechas();
				$tanques = StockTurnoModel::obtenerTanques();
				$result = StockTurnoTemplate::formAgregar($almacenes,$fechas_sistema,$tanques);
				$result_f = " ";
				break;
		
			case 'Modificar':

				$desde 		= $_REQUEST['desde'];
				$hasta 		= $_REQUEST['hasta'];
				$almacen 	= $_REQUEST['almacen'];
				$record 	= StockTurnoModel::recuperarRegistroArray(trim($_REQUEST["registroid"]), trim($_REQUEST["responsable"]), trim($_REQUEST["articulo"]), trim($_REQUEST["unidad"]));
				$almas 		= StockTurnoModel::obtenerEstaciones();

				if(trim($record[0]) == substr($almas[trim($record[0])],0,3))
					$estacion = $almas[trim($record[0])];

				$result = StockTurnoTemplate::formModificar($record, $estacion,$desde,$hasta,$almacen);  
				$result_f = " ";

				break;

			case "Actualizar":

				if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"),"unknown"))
					$ip = getenv("HTTP_CLIENT_IP");
				else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
					$ip = getenv("HTTP_X_FORWARDED_FOR");
				else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
					$ip = getenv("REMOTE_ADDR");
				else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
					$ip = $_SERVER['REMOTE_ADDR'];
				else
					$ip = "";

				$regid 		= $_REQUEST['regid'];
				$modificar 	= StockTurnoModel::modificar($regid, $_REQUEST['stock'], $_REQUEST['fecha_inventario'], $_REQUEST['hora_inventario'], $_REQUEST['responsable'] );
				$producto 	= explode('-', trim($_REQUEST['tanque']));
				$articulo 	= trim($producto[1]);
				$unidad 	= trim($producto[2]);
				$record2 	= StockTurnoModel::recuperarRegistroArray(trim($regid), trim($_REQUEST["responsable"]), $articulo, $unidad);		
				$almas 		= StockTurnoModel::obtenerEstaciones();

				if(trim($record2[0]) == substr($almas[trim($record2[0])],0,3))
					$estacion = $almas[trim($record2[0])];

				$result = StockTurnoTemplate::formModificar($record2, $estacion,$desde,$hasta,$almacen); 

				if ($modificar) {
					$result_f = "<center><blink><<< Datos guardados correctamente >>></blink></center>";
				} else {
					$result_f = "<center><blink style='color: red'><<< Error al guardar, tal vez ya ingreso un stock del mismo turno y fecha >>></blink></center>";
				}		

				break;

		    	case "Guardar":

				if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"),"unknown"))
					$ip = getenv("HTTP_CLIENT_IP");
				else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
					$ip = getenv("HTTP_X_FORWARDED_FOR");
				else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
					$ip = getenv("REMOTE_ADDR");
				else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
					$ip = $_SERVER['REMOTE_ADDR'];
				else
					$ip = "";

				$almacenes 	= StockTurnoModel::obtenerEstaciones();
				$fechas_sistema = StockTurnoModel::obtenerFechas();
				$tanques 	= StockTurnoModel::obtenerTanques();

				$agregar = StockTurnoModel::agregar($_REQUEST['almacen'], $_REQUEST['fecha_sistema'], $_REQUEST['turno'], $_REQUEST['fecha_inventario'], $_REQUEST['hora_inventario'], $_REQUEST['tanque'], $_REQUEST['stock'], $_REQUEST['responsable'], $ip, $_SESSION['auth_usuario']);
				$result = StockTurnoTemplate::formAgregar($almacenes,$fechas_sistema, $tanques);

				if ($agregar) {
					$result_f = "<center><blink><<< Datos guardados correctamente >>></blink></center>";
				} else {
					$result_f = "<center><blink style='color: red'><<< Error al guardar, tal vez ya ingreso un stock del mismo turno y fecha >>></blink></center>";
				}	

				break;

		    	default:
				$desde = date("01"."/".m."/".Y); 
				$hasta = date(d."/".m."/".Y); 
				$busqueda    	= StockTurnoModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina'],$_REQUEST['desde'], $_REQUEST['hasta']);
				$result     	= StockTurnoTemplate::search_form($desde, $hasta, $busqueda['paginacion']);
				$result_f 	= StockTurnoTemplate::reporte($busqueda['datos'], "", "");
				$buscar = true;
				break;

		}

		$this->visor->addComponent("ContentT", "content_title", StockTurnoTemplate::titulo());
		if ($result != '')
			$this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != '')
			$this->visor->addComponent("ContentF", "content_footer", $result_f);
	}
}
