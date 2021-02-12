<?php
class TanquesController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action'])?$this->action=$_REQUEST['action']:$this->action='';
	}

	function Run() {

		include 'maestros/m_tanques.php';
		include 'maestros/t_tanques.php';

		$this->Init();
		$result = "";
		$result_f = "";
		$buscar = false;
		$listado = false;

		switch($this->action) {

			case "Buscar":

				$listado = true;


			case "Agregar":

				$result	  = TanquesTemplate::formAgregar(array());
				$result_f = "&nbsp;";

				break;

			case "Modificar":

				$resultado = TanquesModel::recuperarRegistroArray($_REQUEST['cod_tanque']);
				$result	   = TanquesTemplate::formAgregar($resultado);
				$result_f  = "&nbsp;";

				break;

			case "Eliminar":
				?><script>confirm("<?php echo 'Deseas eliminar el codigo de Tanque '.$_REQUEST['cod_tanque'].' ? ' ; ?> ");</script><?php

			   	$result = TanquesModel::eliminarRegistro($_REQUEST['cod_tanque']);
				$result = TanquesTemplate::formSearch($_REQUEST['fecha'],$_REQUEST['fecha2']);
				$resultados = TanquesModel::buscar($_REQUEST['fecha'], $_REQUEST['fecha2']);	    	
				$result_f  = TanquesTemplate::resultadosBusqueda($resultados, $_REQUEST['fecha'], $_REQUEST['fecha2']);

			    	break;

		
			case "Guardar":

				$result_f = "&nbsp;";

				if ($_REQUEST['cod_tanque'] == '' or $_REQUEST['capacidad'] == '' or $_REQUEST['lectu_gal'] == ''){
					$result_f = "<script>alert('Llenar los campos faltantes');</script>";				
				}else{
					$res = TanquesModel::agregar($_REQUEST['cod_tanque'],$_REQUEST['nom_producto'],$_REQUEST['capacidad'],$_REQUEST['lectu_gal'],$_SESSION['auth_usuario'],$_SESSION['almacen']);

					if($res == 1){
						?><script>alert('Registro guardado correctamente');</script><?php

						$result = TanquesTemplate::formSearch($_REQUEST['fecha'],$_REQUEST['fecha2']);
						$resultados = TanquesModel::buscar($_REQUEST['fecha'], $_REQUEST['fecha2']);	    	
					    	$result_f   = TanquesTemplate::resultadosBusqueda($resultados, $_REQUEST['fecha'], $_REQUEST['fecha2']);

					}else{
						?><script>alert('Ya existe registro debes ingresar otro');</script><?php
					}
								
				}

				break;

			case "Actualizar":

				$var = TanquesModel::actualizar($_REQUEST['cod_tanque'],$_REQUEST['nom_producto'],$_REQUEST['capacidad'],$_REQUEST['lectu_gal']);

					if ($var == ''){
						?><script>alert('Registro actualizado correctamente')</script><?php
						$result = TanquesTemplate::formSearch($_REQUEST['fecha'],$_REQUEST['fecha2']);
					}else{					
						?><script>alert('No ha echo nongun cambio')</script><?php
					}
				break;

			default:
				$buscar = true;
				break;

		}

		if ($buscar) {
		    	$result = TanquesTemplate::formSearch($_REQUEST['fecha'], "");
		}

		if ($listado) {

			$result     = TanquesTemplate::formSearch($_REQUEST['fecha'], $_REQUEST['fecha2']);		
		    	$resultados = TanquesModel::buscar($_REQUEST['fecha'], $_REQUEST['fecha2']);	    	
		    	$result_f   = TanquesTemplate::resultadosBusqueda($resultados, $_REQUEST['fecha'], $_REQUEST['fecha2']);
		}

		$this->visor->addComponent("ContentT", "content_title", TanquesTemplate::titulo());

		if ($result != "")
			$this->visor->addComponent("ContentB", "content_body", $result);

		if ($result_f == ""){

			$resultados = TanquesTemplate::Formulario($fecha,$fecha2);
			$this->visor->addComponent("ContentF", "content_footer", TanquesTemplate::resultadosBusqueda($resultados));

		}else{

			$this->visor->addComponent("ContentF", "content_footer", $result_f);
		}
		
	}
}
