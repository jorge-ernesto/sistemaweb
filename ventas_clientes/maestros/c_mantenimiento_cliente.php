<?php

class MantenimientoClienteController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action']) ? $this->action = $_REQUEST['action']:$this->action='';
    	}
    
    	function Run() {
		include 'maestros/m_mantenimiento_cliente.php';
		include 'maestros/t_mantenimiento_cliente.php';
	
		$this->Init();	
		$result   = '';
		$result_f = '';

		switch ($this->action) {

			case "Buscar":
				$busqueda    	= MantenimientoClienteModel::obtenerDatos(trim($_REQUEST['cliente']));
				$result_f 	= MantenimientoClienteTemplate::reporte($busqueda);
				$result     	= MantenimientoClienteTemplate::search_form();
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", $result_f);
				break;

			case "Agregar":	
				$result 	= MantenimientoClienteTemplate::formAgregar("A", "", "", "", "", "", date(d/m/Y), date(d/m/Y), "", "", "");
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", "");
				break;

			case "Editar":
				$cod_cliente	= trim($_REQUEST['cod_cliente']);
				$nom_cliente	= trim($_REQUEST['nom_cliente']);  
				$cod_articulo	= trim($_REQUEST['cod_articulo']);
				$nom_articulo 	= trim($_REQUEST['nom_articulo']);   
				$fec_inicio 	= trim($_REQUEST['fec_inicio']);
				$fec_fin 	= trim($_REQUEST['fec_fin']);
				$precio 	= trim($_REQUEST['precio']);
				$habilitado	= trim($_REQUEST['habilitado']);
				$cartaref	= trim($_REQUEST['carta_ref']);
				$tipocli	= trim($_REQUEST['tipo_cli']);
				$result 	= MantenimientoClienteTemplate::formAgregar("E", $cod_cliente, $nom_cliente, $cod_articulo, $nom_articulo, $fec_inicio, $fec_fin, $precio, $habilitado, $cartaref, $tipocli);
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", "");
				break;

			case "Guardar":
				$cod_cliente	= trim($_REQUEST['cod_cliente']);
				$cod_articulo	= trim($_REQUEST['cod_articulo']);
				$fec_inicio 	= trim($_REQUEST['fec_inicio']);
				$fec_fin 	= trim($_REQUEST['fec_fin']);
				$precio 	= trim($_REQUEST['precio']);
				$habilitado	= trim($_REQUEST['habilitado']);
				$cartaref	= trim($_REQUEST['carta_ref']);
				$tipocli	= trim($_REQUEST['tipo_cli']);
				$usuario	= trim($_SESSION['auth_usuario']);

				if ($habilitado == 'f')
					$habil = 'FALSE';
				else
					$habil = 'TRUE';

				if ($cod_cliente == "" or $cod_articulo == "" or $fec_inicio == "" or $fec_fin == "" or $precio == "") {
					$result = '<script name="accion">alert("Falta completar datos") </script>';
                                        echo $result;
				} else {
					if (trim($_REQUEST['tipoguardar']) == "A")				    
						$ingresa  = MantenimientoClienteModel::ingresarDescuento($cod_cliente, $cod_articulo, $fec_inicio, $fec_fin, $precio, $habil, $cartaref, $tipocli, $usuario);	
					else				    
						$ingresa  = MantenimientoClienteModel::editarDescuento($cod_cliente, $cod_articulo, $precio, $fec_inicio, $fec_fin, $habil, $cartaref, $tipocli, $usuario);	

					if($ingresa == 1) $result = '<script name="accion">alert("Datos guardados correctamente.") </script>';
					echo $result;
				}
				break;

			case "Eliminar":
				$elimina    	= MantenimientoClienteModel::eliminarCodigo(trim($_REQUEST['cod_cliente']), trim($_REQUEST['cod_articulo']),trim($_REQUEST['fec_ini']), trim($_REQUEST['fec_fin']));
				$busqueda    	= MantenimientoClienteModel::obtenerDatos("");
				$result_f 	= MantenimientoClienteTemplate::reporte($busqueda);
				$result     	= MantenimientoClienteTemplate::search_form();
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", $result_f);
				break;


		    	default:
				$busqueda    	= MantenimientoClienteModel::obtenerDatos("");
				$result_f 	= MantenimientoClienteTemplate::reporte($busqueda);
				$result     	= MantenimientoClienteTemplate::search_form();
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", $result_f);
				break;
		}		
	}
}
