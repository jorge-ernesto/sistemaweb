<?php

class DescuentoVentaController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action']) ? $this->action = $_REQUEST['action']:$this->action='';
    	}
    
    	function Run() {
		include 'reportes/m_descuento_venta.php';
		include 'reportes/t_descuento_venta.php';
	
		$this->Init();
	
		$result = '';
		$result_f = '';
		$search_form = false;


		switch ($this->action) {

			case 'Cambiar':
				$fecha = trim($_REQUEST['fecha']);
				$ticket = trim($_REQUEST['ticket']);
				$caja = trim($_REQUEST['caja']);

				if (empty($ticket) or empty($caja)){
					$result = '<script name="accion">alert("Debe ingresar el numero de ticket") </script>';
                                        echo $result;
				} else {				    
					$result = DescuentoVentaModel::ingresarComoVenta(trim($_REQUEST['fecha']),
								       		trim($_REQUEST['ticket']),
								       		trim($_REQUEST['caja']),
										trim($_SESSION['auth_usuario']));	
					if($result == 0) $result = '<script name="accion">alert("El ticket no existe o no tiene descuento.") </script>';		   
					if($result == 1) $result = '<script name="accion">alert("El descuento se cambio a boleta.") </script>';
					echo $result;	
				}				
				$result2 	= DescuentoVentaTemplate::formDescuentoVenta();
				$this->visor->addComponent("ContentB", "content_body", $result2);
				$this->visor->addComponent("ContentF", "content_footer", "");
				break;

		    	default:				
				$result 	= DescuentoVentaTemplate::formDescuentoVenta();
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", "");
				break;
		}		
	}
}
