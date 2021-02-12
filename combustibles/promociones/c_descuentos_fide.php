<?php

class DescuentosFideController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action']) ? $this->action = $_REQUEST['action']:$this->action='';
    	}
    
    	function Run() {
		include 'promociones/m_descuentos_fide.php';
		include 'promociones/t_descuentos_fide.php';
	
		$this->Init();
	
		$result = '';
		$result_f = '';

		switch ($this->action) {

			case "Buscar":
				$busqueda    	= DescuentosFideModel::obtenerDatos(trim($_REQUEST['ruc']));
				$result_f 	= DescuentosFideTemplate::reporte($busqueda);
				$result     	= DescuentosFideTemplate::search_form();
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", $result_f);
				break;

			case "Agregar":	
				$result 	= DescuentosFideTemplate::formAgregar("A", "", "", "", "", "", date(d/m/Y), date(d/m/Y));
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", "");
				break;

			case "Editar":
				$id 		= trim($_REQUEST['id']); 
				$ruc 		= trim($_REQUEST['ruc']);
				$cod_articulo 	= trim($_REQUEST['cod_articulo']);   
				$nom_articulo 	= trim($_REQUEST['nom_articulo']);
				$descuento 	= trim($_REQUEST['descuento']);
				$inicio 	= trim($_REQUEST['inicio']);
				$fin 		= trim($_REQUEST['fin']);
				$result 	= DescuentosFideTemplate::formAgregar("E", $id, $ruc, $cod_articulo, $nom_articulo, $descuento, $inicio, $fin);
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", "");
				break;

			case "Guardar":
				$id 		= trim($_REQUEST['id']); 
				$ruc	 	= trim($_REQUEST['ruc']);
				$cod_articulo 	= trim($_REQUEST['cod_articulo']);  
				$descuento 	= trim($_REQUEST['descuento']);
				$inicio 	= trim($_REQUEST['inicio']);
				$fin 		= trim($_REQUEST['fin']);

				if ($ruc == "" or $cod_articulo == "" or $descuento == "" or $inicio == "" or $fin == ""){
					$result = '<script name="accion">alert("Falta completar datos") </script>';
                                        echo $result;
				} else {
					if (trim($_REQUEST['tipoguardar']) == "A")				    
						$ingresa    	= DescuentosFideModel::ingresarDescuento($ruc, $cod_articulo, $descuento, $inicio, $fin);	
					else				    
						$ingresa    	= DescuentosFideModel::editarDescuento($id, $descuento, $fin);	

					if($ingresa == 1) $result = '<script name="accion">alert("Datos guardados correctamente.") </script>';
					if($ingresa == 2) $result = '<script name="accion">alert("RUC no valido.") </script>';					
					echo $result;
				}
				break;

			case "Eliminar":
				$elimina    	= DescuentosFideModel::eliminarCodigo(trim($_REQUEST['id']));
				$busqueda    	= DescuentosFideModel::obtenerDatos("");
				$result_f 	= DescuentosFideTemplate::reporte($busqueda);
				$result     	= DescuentosFideTemplate::search_form();
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", $result_f);
				break;


		    	default:
				$busqueda    	= DescuentosFideModel::obtenerDatos("");
				$result_f 	= DescuentosFideTemplate::reporte($busqueda);
				$result     	= DescuentosFideTemplate::search_form();
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", $result_f);
				break;
		}		
	}
}
