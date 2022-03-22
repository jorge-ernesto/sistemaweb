<?php

class AfericionesController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action']) ? $this->action = $_REQUEST['action']:$this->action='';
    	}
    
    	function Run() {
		include 'reportes/m_afericiones.php';
		include 'reportes/t_afericiones.php';
		include('../include/paginador_new.php');
	
		$this->Init();
	
		$result = '';
		$result_f = '';
		$search_form = false;

	      	if(!isset($_REQUEST['rxp'],$_REQUEST['pagina'])) {
			$_REQUEST['rxp'] = 30;
		 	$_REQUEST['pagina'] = 1;
	      	}

		switch ($this->action) {

			case "Reporte":
				echo 'Entro al Reporte'."\n";
				$busqueda    	= AfericionesModel::Paginacion($_REQUEST['desde'], $_REQUEST['hasta'],$_REQUEST['rxp'],$_REQUEST['pagina']);
				$result_f 	= AfericionesTemplate::reporte($busqueda['datos'],$_REQUEST['desde'], $_REQUEST['hasta']);
				$result     	= AfericionesTemplate::search_form($_REQUEST['desde'], $_REQUEST['hasta'],$busqueda['paginacion']);
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", $result_f);
				break;

			case "Agregar":
				echo 'Entro a Agregar'."\n";	
				$result 	= AfericionesTemplate::formAfericion();
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", "");
				break;

			case "Eliminar":
				$resultado 	= AfericionesModel::eliminarAfericion($_REQUEST["trans"],$_REQUEST['es'],$_REQUEST["dia"],$_REQUEST["caja"],$_REQUEST['codigo'],$_REQUEST['pump']); //ELIMINAR AFERICIONES	
				$busqueda    	= AfericionesModel::Paginacion('', '',$_REQUEST['rxp'],$_REQUEST['pagina']);
				$result_f 		= AfericionesTemplate::reporte($busqueda['datos'],date(d."/".m."/".Y), date(d."/".m."/".Y));
				$result     	= AfericionesTemplate::search_form(date(d."/".m."/".Y), date(d."/".m."/".Y),$busqueda['paginacion']);
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", $result_f);
				break;


			case 'Guardar':
				$fecha = trim($_REQUEST['fecha']);
				$ticket = trim($_REQUEST['ticket']);
				$caja = trim($_REQUEST['caja']);

				if (empty($ticket) or empty($caja)) {
					$result = '<script name="accion">alert("Debe ingresar el numero de ticket") </script>';
					echo $result;
				} else {
					$result = AfericionesModel::ingresarAfericion(trim($_REQUEST['fecha']), //INGRESAR AFERICIONES
						trim($_REQUEST['ticket']),
						trim($_REQUEST['caja']),
						trim($_SESSION['auth_usuario']));
					if ($result == -1) {
						$result = '<script name="accion">alert("Error, intentelo en otro momento.") </script>';
					} else if ($result == 0) {
						$result = '<script name="accion">alert("No existe el numero de ticket en esa fecha.") </script>';
					} else if ($result == 2) {
						$result = '<script name="accion">alert("El ticket no pasa como afericion.") </script>';
					} else if ($result == 3) {
						$result = '<script name="accion">alert("El ticket ya esta registrado en afericiones.") </script>';
					}  else if ($result == 1) {
						$result = '<script name="accion">alert("Datos guardados correctamente. Importante: Deben de anular el ticket en Ventas -> Anular Ticket") </script>';
					} else if ($result == -2) {
						$result = '<script name="accion">alert("Error, no hay surtidor, o hay mas de uno.") </script>';
					} else if ($result == -3) {
						$result = '<script name="accion">alert("Error, no hay afericion, o hay mas de uno.") </script>';
					}
					echo $result;
				}
				break;

		    	default:
				$busqueda    	= AfericionesModel::Paginacion('', '',$_REQUEST['rxp'],$_REQUEST['pagina']);
				$result_f 		= AfericionesTemplate::reporte($busqueda['datos'],date(d."/".m."/".Y), date(d."/".m."/".Y));
				$result     	= AfericionesTemplate::search_form(date(d."/".m."/".Y), date(d."/".m."/".Y),$busqueda['paginacion']);
				$search_form = true;
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", $result_f);
				break;
		}		
	}
}
