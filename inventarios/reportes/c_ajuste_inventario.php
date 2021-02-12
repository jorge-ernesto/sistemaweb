<?php

date_default_timezone_set("America/Lima");

class AjusteInventarioController extends Controller {

	function Init() {

		$this->visor = new Visor();
      		$this->task = @$_REQUEST["task"];
      		$this->action = isset($_REQUEST["action"])?$_REQUEST["action"]:'';
      		$this->datos = isset($_REQUEST["datos"])?$_REQUEST["datos"]:'';

	}

	function Run() {
		ob_start();
		$this->Init();

		include 'reportes/m_ajuste_inventario.php';
		include 'reportes/t_ajuste_inventario.php';

		$result = "";
		$result_f = "";

		$t      = microtime(true);
		$micro  = sprintf("%06d",($t - floor($t)) * 1000000);
		$hora   = date('H:i:s.'.$micro,$t);

		$this->visor->addComponent("ContentT", "content_title", AjusteInventarioTemplate::titulo());

		switch ($this->request){

			case 'REGISTROS':

				switch($this->action) {

					case "Buscar":

						$almacenes 	= AjusteInventarioModel::Almacenes($_REQUEST['almacen']);				
						$ubicaciones	= AjusteInventarioModel::Ubicaciones($_REQUEST['almacen']);
						$resultados 	= AjusteInventarioModel::buscar($_REQUEST['almacen'], $_REQUEST['ubica'], $_REQUEST['myorden']);
						$result 	= AjusteInventarioTemplate::formSearch($almacenes, $ubicaciones);

						if($resultados)
							$result_f 	= AjusteInventarioTemplate::resultadosBusqueda($resultados, $_REQUEST['almacen'], $_REQUEST['ubica']);
						else
							$result_f	= "<center><blink style='color: red'><<< No hay datos >>></blink></center>";
			
						$this->visor->addComponent("ContentB", "content_body", $result);

					break;

					case "Ubica":

						$almacenes 	= AjusteInventarioModel::Almacenes($_REQUEST['almacen']);
						//TRAER UBICACION DE INVENTARIO DEL SERVIDOR CENTRAL
						/*$central = AjusteInventarioModel::DatosServidorRemoto();

						if($central[0])
							$ubicaciones = AjusteInventarioModel::UbicacionesRemoto($central[1], $central[2], $central[3], $central[4], $_REQUEST['almacen']);
						else
						*/
						$ubicaciones = AjusteInventarioModel::Ubicaciones($_REQUEST['almacen']);

						$result 	= AjusteInventarioTemplate::formSearch($almacenes, $ubicaciones);
						$this->visor->addComponent("ContentB", "content_body", $result);

					break;

					default:
						$almacenes 	= AjusteInventarioModel::Almacenes();
						$result 	= AjusteInventarioTemplate::formSearch($almacenes,'');
						$this->visor->addComponent("ContentB", "content_body", $result);
					break;

				}

			break;

			case 'PROCESANDO':

				$almacen	= $_REQUEST['almacen'];
				$ubica		= $_REQUEST['ubica'];
				$producto	= explode(",",$_REQUEST['producto']);//Me devuelve un array ([0]=>"valor del input text , ... [99]")
				$stkfisico	= explode(",",$_REQUEST['stkfisico']);
				$codpro		= $_REQUEST['producto'];

				$begin 		= AjusteInventarioModel::BEGINTransaccion();
				$procesar 	= AjusteInventarioModel::ProcesarAjustes($almacen, $producto, $stkfisico, $hora, $ubica);

				if($procesar){

					$commit 	= AjusteInventarioModel::COMMITransaccion();
					$ubicaciones	= AjusteInventarioModel::Ubicaciones($almacen);
					$almacenes 	= AjusteInventarioModel::Almacenes();
					$result 	= AjusteInventarioTemplate::formSearch($almacenes,$ubicaciones);

					$this->visor->addComponent("ContentB", "content_body", $result);

					$reporte 	= AjusteInventarioModel::Reporte($almacen, $codpro, $ubica);
					$result_f 	= AjusteInventarioTemplate::ReporteAjustes($reporte, $almacen, $codpro, $ubica);

				}else{

					$rollback	= AjusteInventarioModel::ROLLBACKTransaccion();
					$result_f	= "<center><blink style='color: red'><<< Error Al procesar >>></blink></center>";

				}

	      		break;

			case 'EXCEL':

				$almacen	= $_REQUEST['almacen'];
				$codigo		= $_REQUEST['codigo'];
				$ubica		= $_REQUEST['ubica'];

				$reporte 	= AjusteInventarioModel::Reporte($almacen, $codigo, $ubica);
				$result_f 	= AjusteInventarioTemplate::reporteExcel($reporte, $almacen, $ubica);

	      		break;

			default:
	       				$this->visor->addComponent('ContentB', 'content_body', '<h2>TAREA "'.$this->request.'" NO CONOCIDA EN REGISTROS</h2>');
			break;


		}

		if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);

	}

}
