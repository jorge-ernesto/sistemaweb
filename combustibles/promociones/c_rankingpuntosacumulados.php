<?php

class RankingPuntosAcumuladosController extends Controller {
		
	function Init() {
		$this->visor = new Visor();
		$this->task = @$_REQUEST["task"];
		$this->action = isset($_REQUEST["action"])?$_REQUEST["action"]:'';
	}
	
	function Run() {
		$this->Init();
		$result     = '';
		$bolMensaje ='0';	
		include('promociones/m_rankingpuntosacumulados.php');
		include('promociones/t_rankingpuntosacumulados.php'); 
		include('../include/paginador_new.php');
		require("../clases/funciones.php");	
		$funcion = new class_funciones;
		
		$this->visor->addComponent('ContentT', 'content_title',RankingPuntosAcumuladosTemplate::titulo());
		if(!$_REQUEST['rxp'] && !$_REQUEST['pagina']) {
			$_REQUEST['rxp'] = 100;
			$_REQUEST['pagina'] = 0;
		}

		$fechaini = date("d/m/Y");
		$fechafin = date("d/m/Y");

		switch ($this->request) {
			case 'RANKINGPUNTOSACUMULADOS':
				$tablaNombre = 'RANKINGPUNTOSACUMULADOS';
				$listado = false;

				switch ($this->action) { 	
					case 'Consultar':
						$fechaini = trim($_REQUEST['fechainicio']);
						$fechafin = trim($_REQUEST['fechafin']);
						$sucursal = trim($_REQUEST['almacen']);
						$estado   = trim($_REQUEST['estado']);
						$almacenes 	= RankingPuntosAcumuladosModel::obtenerAlmacenes();
						$busqueda 	= RankingPuntosAcumuladosModel::tmListado($fechaini,$fechafin,$sucursal,$_REQUEST['rxp'],$_REQUEST['pagina'],$estado);
						$result   	= RankingPuntosAcumuladosTemplate::formBuscar($almacenes, $fechaini, $fechafin);
						$tamaniopuntos 	= count($busqueda['datos']);
						$result        .= RankingPuntosAcumuladosTemplate::formRankingPuntosAcumulados($tamaniopuntos);
						$result        .= RankingPuntosAcumuladosTemplate::formPaginacion($busqueda['paginacion'],$fechaini,$fechafin,$tamaniopuntos,$sucursal);
						$result        .= RankingPuntosAcumuladosTemplate::listado($busqueda['datos'], $sucursal, $fechaini, $fechafin);
						$this->visor->addComponent("ContentB", "content_body", $result);
						break;

					case 'Excel':
						$fechaini = trim($_REQUEST['fechainicio']);
						$fechafin = trim($_REQUEST['fechafin']);
						$sucursal = trim($_REQUEST['almacen']);
						$estado   = trim($_REQUEST['estado']);
						$busqueda 	= RankingPuntosAcumuladosModel::tmListado($fechaini,$fechafin,$sucursal,$_REQUEST['rxp'],$_REQUEST['pagina'],$estado);
						
						ob_end_clean();
						$buff = " RAKING DE PUNTOS ACUMULADOS - FIDELIZACION \n\n";
						$buff .= "NRO. CUENTA; CREACION CUENTA; DNI; CLIENTE; TELEFONO; PUNTOS ACUMULADOS; PUNTOS ACTUAL; ULTIMO DESPACHO; SUCURSAL\n";

						foreach($busqueda['completo'] as $A)
							$buff .= "{$A['cuenta']}; {$A['fecha_creacion_cuenta']}; {$A['dni']}; {$A['cliente']}; {$A['telefono']}; {$A['puntosacumulados']}; {$A['nu_puntaje_actual']}; {$A['ultdespacho']}; {$A['ch_sucursal']} \n";
						
						header("Content-type: text/csv");
						header("Content-Disposition: attachment; filename=\"rank_puntos.csv\""); 
						header("Cache-Control: no-cache, must-revalidate");
						header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
						die($buff);
						break;
					
					case 'BuscarDetalle':
						$iAlmacen 	= trim($_REQUEST['iAlmacen']);
						$dIni 		= trim($_REQUEST['dIni']);
						$dFin 		= trim($_REQUEST['dFin']);
						$iCuenta 	= trim($_REQUEST['iCuenta']);
						$iTarjeta 	= trim($_REQUEST['iTarjeta']);
						$arrDataDetalle = RankingPuntosAcumuladosModel::listarDetalleMovimientos($iAlmacen, $dIni, $dFin, $iCuenta, $iTarjeta);

						$this->visor->addComponent("DetalleMovimientos", "div" . $iCuenta . $iTarjeta, RankingPuntosAcumuladosTemplate::gridViewHTMLDetail($arrDataDetalle));
						break;

					default:
						$listado = true;
						break;
				}
				if ($listado) { 
					$almacenes 	= RankingPuntosAcumuladosModel::obtenerAlmacenes();
					$result 	= RankingPuntosAcumuladosTemplate::formBuscar($almacenes, $fechaini, $fechafin);
					$this->visor->addComponent("ContentB", "content_body", $result);
				}
		}
	}
}

