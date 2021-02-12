<?php

class ClientesController extends Controller {

	function Init() {
		$this->visor = new Visor();
	        isset($_REQUEST['action'])?$this->action=$_REQUEST['action']:$this->action='';
    	}

	function Run() {
		include 'maestros/m_clientes.php';
		include 'maestros/t_clientes.php';

		$this->Init();

		$result = "";
		$result_f = "";
		$form_agregar = false;
		$listado = false;

		switch($this->action) {

           		 case "Modificar":
               			foreach($_REQUEST['codigos'] as $codigo) {
                   			ClientesModel::actualizarCliente($codigo, $_REQUEST['cli_razsocial'][$codigo][0], $_REQUEST['cli_razsocialbreve'][$codigo][0], $_REQUEST['cli_direccion'][$codigo][0], $_REQUEST['cli_ruc'][$codigo][0], $_REQUEST['cli_moneda'][$codigo][0], $_REQUEST['cli_telefono'][$codigo][0], $_REQUEST['cli_fax'][$codigo][0]);
                		}
				$listado = true;
				break;

            		case "Eliminar":
				foreach($_REQUEST['codigos'] as $codigo) {
				    ClientesModel::borrarCliente($codigo);
				}
				$listado = true;
				break;

            		case "Agregar":
                		ClientesModel::agregarCliente($_REQUEST['cli_codigo'], $_REQUEST['cli_razsocial'], $_REQUEST['cli_rsocialbreve'], $_REQUEST['cli_direccion'], $_REQUEST['cli_ruc'], $_REQUEST['cli_moneda'], $_REQUEST['cli_telefono'], $_REQUEST['cli_fax']);
            		
			default:
				$listado = true;
				$form_agregar = true;
				break;
        	}

        	if ($form_agregar) {
            		$result = ClientesTemplate::formAgregar();
        	}

        	if ($listado) {
            		$clientes = ClientesModel::obtenerClientes();
            		$result_f = ClientesTemplate::listado($clientes);
        	}

        	$this->visor->addComponent("ContentT", "content_title", ClientesTemplate::titulo(). "<h1>ptlime</h1>");
        	if ($result != "") 
			$this->visor->addComponent("ContentB", "content_body", $result);
        	if ($result_f != "") 
			$this->visor->addComponent("ContentF", "content_footer", $result_f);
    	}
}
