<?php

class VarillasController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action'])?$this->action = $_REQUEST['action']:$this->action="";
    }
    
    function Run() {
	    ob_start();
		include 'movimientos/m_varillas.php';
		include 'movimientos/t_varillas.php';

		$objModel = new VarillasModel();
		$objTemplate = new VarillasTemplate();

		$this->Init();

		//Obtener la fecha del ultimo del cierre
		$dUltimoCierre = $objModel->getFechaSistemaPA();
		
		$result   = "";
		$result_f = "";

		$form_search = false;
		$listado     = false;

		$ip = "";

		if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"),"unknown"))
			$ip = getenv("HTTP_CLIENT_IP");
		else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
			$ip = getenv("HTTP_X_FORWARDED_FOR");
		else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
			$ip = getenv("REMOTE_ADDR");
		else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
			$ip = $_SERVER['REMOTE_ADDR'];

		switch ($this->action) { 

		    	case "Buscar":
					$listado = true;
					break;

		    	case "Editar": 
					$var   	= VarillasModel::obtenerVarilla($_REQUEST['sucursal'], $_REQUEST['dia'], $_REQUEST['tanque']);
					$result = VarillasTemplate::formAgregarEditar("E", $var, $_REQUEST['almacen'], $_REQUEST['txt-dInicial'], $_REQUEST['txt-dFinal'], $_REQUEST['tanque']);
					$result_f  = "<br>";
					break;

		    	case "Guardar":
					$res = VarillasModel::guardarVarillas($_REQUEST['txt-dFinal'], $_REQUEST['ch_sucursal'], $_REQUEST['ch_tanque'], $_REQUEST['nu_medicion'], $_REQUEST['ch_responsable'], $_SESSION['auth_usuario'], $ip, $_REQUEST['ch_tanque_ant']);
					if($res == 1) {
						?><script>alert('Se guardo la varilla correctamente!');</script><?php	
					} else {
						?><script>alert('No se puede modificar. Fecha ya consolidada!');</script><?php	
					}
					break;
			
			case "Agregar":
				$result = VarillasTemplate::formAgregarEditar("A", "", $_REQUEST['cbo-iAlmacen'], $_REQUEST['txt-dInicial'], $_REQUEST['txt-dFinal'], "");
				$result_f  = "<br>";					
				break;

	    	case "Ingresar":
				$res = VarillasModel::insertar($_REQUEST['cbo-iAlmacen'], $_REQUEST['txt-dFinal'], $_REQUEST['ch_tanque'], $_REQUEST['nu_medicion'], $_REQUEST['ch_responsable'], $_SESSION['auth_usuario'], $ip);
				if($res == 1) {
					?><script>alert('Se guardo la varilla correctamente!');</script><?php	
				} else {
					if ($res == 2) {
						?><script>alert('No se puede Agregar. Fecha ya consolidada.');</script><?php	
					} else {
						?><script>alert('La varilla ya fue ingresada.');</script><?php	
					}
				}
				$result = VarillasTemplate::formAgregarEditar("A", "", $_REQUEST['cbo-iAlmacen'], "", "");
				break;
			
			case "PDF":
				$resultado = VarillasModel::search($_REQUEST['cbo-iAlmacen'], $_REQUEST['txt-dInicial'], $_REQUEST['txt-dFinal']);
				$result = VarillasTemplate::reportePDF($resultado, $_REQUEST['cbo-iAlmacen'], $_REQUEST['txt-dInicial'], $_REQUEST['txt-dFinal']) ;
				$mi_pdf = "/sistemaweb/ventas_clientes/reportes/pdf/Varillaje.pdf";
				header('Content-type: application/pdf');
				header('Content-Disposition: attachment; filename="'."Varillaje.pdf".'"');
				readfile($mi_pdf);
				break;
			
			case "Excel":
				$resultado = VarillasModel::search($_REQUEST['cbo-iAlmacen'], $_REQUEST['txt-dInicial'], $_REQUEST['txt-dFinal']);
				$resultt   = VarillasTemplate::reporteExcel($resultado, $_REQUEST['cbo-iAlmacen'], $_REQUEST['txt-dInicial'], $_REQUEST['txt-dFinal']) ;
				break;

	    	default:
				$form_search = true;
				break;
		}
	
		if ($form_search) {
		    $result = VarillasTemplate::formSearch($_REQUEST['cbo-iAlmacen'], "", $dUltimoCierre, $dUltimoCierre);
		}

		if ($listado) {
			error_log(json_encode( array($_REQUEST, $dUltimoCierre) ));
			$result    = VarillasTemplate::formSearch($_REQUEST['cbo-iAlmacen'], $_REQUEST['txt-dInicial'], $_REQUEST['txt-dFinal'], $dUltimoCierre);
	    	$resultado = VarillasModel::search($_REQUEST['cbo-iAlmacen'], $_REQUEST['txt-dInicial'], $_REQUEST['txt-dFinal']);
	    	$result_f  = VarillasTemplate::listado($resultado, $_REQUEST['txt-dInicial'], $_REQUEST['txt-dFinal'], $_REQUEST['cbo-iAlmacen']);
		}

		$this->visor->addComponent("ContentT", "content_title", VarillasTemplate::titulo());
		if ($result != "") 
			$this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != "") 
			$this->visor->addComponent("ContentF", "content_footer", $result_f);	
    	}
}
