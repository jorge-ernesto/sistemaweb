<?php
class VencimientoLotesController extends Controller {
	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action']) ? $this->action = $_REQUEST['action']:$this->action='';
	}

	function Run() {
	    ob_start();
		include 'reportes/m_vencimiento_lotes.php';
		include 'reportes/t_vencimiento_lotes.php';
	
		$this->Init();

		$objModel = new VencimientoLotesModel();
		$objTemplate = new VencimientoLotesTemplate();

		//Obtener la fecha del ultimo del cierre
		$dUltimoCierre = $objModel->getFechaSistemaPA();

		$result = '';
		$result_f = '';

		$formPrincipal 		= FALSE;
		$viewListadoHTML 	= FALSE;
		$viewListadoExcel 	= FALSE;

		if(getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"),"unknown"))
			$ip = getenv("HTTP_CLIENT_IP");
		else if(getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
			$ip = getenv("HTTP_X_FORWARDED_FOR");
		else if(getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
			$ip = getenv("REMOTE_ADDR");
		else if(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
			$ip = $_SERVER['REMOTE_ADDR'];

		switch ($this->action) { 
			case "Buscar":
					/*$busqueda = VencimientoLotesModel::buscar($_REQUEST['almacen'],$_REQUEST['desde'],$_REQUEST['hasta'],$_REQUEST['filtro'],$_REQUEST['estado']);
					$result_f = VencimientoLotesTemplate::reporte($busqueda,$_REQUEST['almacen'],$_REQUEST['desde'],$_REQUEST['hasta'],$_REQUEST['filtro'],$_REQUEST['estado']);
					$this->visor->addComponent("ContentF", "content_footer", $result_f);*/
					$formPrincipal = TRUE;
					$viewListadoHTML = TRUE;
					break;

			case "Excel":
					$formPrincipal = TRUE;
					$viewListadoHTML = TRUE;
					$viewListadoExcel = TRUE;
					break;

			case 'Modificar':
					$formulario = $_REQUEST['formulario'];
					$articulo 	= $_REQUEST['articulo'];
					$lote 		= $_REQUEST['lote']; 
					$usuario 	= $_SESSION['auth_usuario']; 
					
					$resultado = VencimientoLotesModel::obtenerFila($formulario, $articulo, $lote);
					$result = VencimientoLotesTemplate::formEdit($resultado, $dUltimoCierre);

					$this->visor->addComponent("ContentB", "content_body", $result);
					$this->visor->addComponent("ContentF", "content_footer", "");
					break;

			case 'Editar':
					$id_formulario 	= $_REQUEST['txt-tformulario'];
					$id_producto 	= $_REQUEST['txt-tno_producto'];
					$id_lote 		= $_REQUEST['txt-tlote'];  
					$lote 			= $_REQUEST['txt-dLote']; 
					$vencimiento 	= $_REQUEST['txt-dFechaVencimiento']; 
					$usuario 		= $_SESSION['auth_usuario'];

					$hoy = date("Y-m-d");
					
					$vence = trim($vencimiento);
					$vence = explode("/", $vencimiento);
					$vence = $vence[2] . "-" . $vence[1] . "-" . $vence[0];

					if( $vence > $hoy || $vence == $hoy) {
						$res = VencimientoLotesModel::Editar($id_formulario, $id_producto, $id_lote, $lote, $vencimiento, $usuario, $ip);
						if($res == 1) {
							echo '<script name="accion">alert("Datos Modificados Correctamente.") </script>';
						}else {
							echo '<script name="accion">alert("Error al Modificar Datos.") </script>';
						}	
					}else {
						echo '<script name="accion">alert("La fecha de vencimiento no puede ser menor a la fecha actual.") </script>';
					}

					break;

			case 'Actualizar':
			
					$formulario = $_REQUEST['formulario'];
					$articulo 	= $_REQUEST['articulo'];
					$lote 		= $_REQUEST['lote']; 
					$nu_estado 	= $_REQUEST['nu_estado']; 
					$usuario 	= $_SESSION['auth_usuario'];

					$desde 		= $_REQUEST['desde']; 
					$hasta 		= $_REQUEST['hasta'];
					$almacen 	= $_REQUEST['almacen'];
					$estado 	= $_REQUEST['estado']; 
					$sTipoOrdenFVencimiento = "checked";

					$res = VencimientoLotesModel::modificarEstado($formulario, $articulo, $lote, $nu_estado, $usuario, $ip);

					if($res == 1) {	
					$arrData 	= $objModel->getLoteVencimiento($almacen, $desde, $hasta, $sTipoOrdenFVencimiento, $sTipoOrdenFEmision, $estado);
					$result_f 	= $objTemplate->gridViewHTML($arrData, $almacen, $desde, $hasta, $sTipoOrdenFVencimiento, $sTipoOrdenFEmision, $estado);
					}
					else {
						echo '<script name="accion">alert("Error al Modificar Estado.") </script>';
					}

					break;

		    default:
		    	$formPrincipal = TRUE;
			break;
		}

		if ($formPrincipal) {
			$nu_almacen 	= (isset($_POST['cbo-iAlmacen']) ? trim($_POST['cbo-iAlmacen']) : $_SESSION['almacen']);
			$fe_inicial 	= (isset($_POST['txt-dInicial']) ? trim($_POST['txt-dInicial']) : $dUltimoCierre);
			$fe_final 		= (isset($_POST['txt-dFinal']) ? trim($_POST['txt-dFinal']) : $dUltimoCierre);
			$sTipoOrden 	= (isset($_POST['radio-sTipoOrden']) ? trim($_POST['radio-sTipoOrden']) : NULL);
			$nu_estado 		= (isset($_POST['cbo-Estado']) ? trim($_POST['cbo-Estado']) : 0);//0 = Todos

			$sTipoOrdenFVencimiento = "checked";
			$sTipoOrdenFEmision = "";
			
			if(isset($_POST['radio-sTipoOrden'])){
				$sTipoOrdenFVencimiento = ($sTipoOrden == "FV" ? "checked" : "");
				$sTipoOrdenFEmision = ($sTipoOrden == "FE" ? "checked" : "");
			}

			$arrAlmacenes 	= $objModel->getAlmacenes();
			$result 		= $objTemplate->formPrincipal($arrAlmacenes, $nu_almacen, $fe_inicial, $fe_final, $dUltimoCierre, $sTipoOrdenFVencimiento, $sTipoOrdenFEmision, $nu_estado);
		}

		if ($viewListadoHTML) {
			$fe_inicial = trim($fe_inicial);
			$fe_inicial = strip_tags($fe_inicial);
			$fe_inicial = explode("/", $fe_inicial);
			$fe_inicial = $fe_inicial[2] . "-" . $fe_inicial[1] . "-" . $fe_inicial[0];

			$fe_final = trim($fe_final);
			$fe_final = strip_tags($fe_final);
			$fe_final = explode("/", $fe_final);
			$fe_final = $fe_final[2] . "-" . $fe_final[1] . "-" . $fe_final[0];
		
			$arrData 	= $objModel->getLoteVencimiento($nu_almacen, $fe_inicial, $fe_final, $sTipoOrdenFVencimiento, $sTipoOrdenFEmision, $nu_estado);
			$result_f 	= $objTemplate->gridViewHTML($arrData, $nu_almacen, $fe_inicial, $fe_final, $sTipoOrdenFVencimiento, $sTipoOrdenFEmision, $nu_estado);
		}

		if ($viewListadoExcel)
			$result_f = $objTemplate->gridViewExcel($arrData);

		$this->visor->addComponent("ContentT", "content_title", $objTemplate->getTitulo());
		if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);		
	}
}
