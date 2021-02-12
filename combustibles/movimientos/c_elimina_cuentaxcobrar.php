<?php
class EliminaCuentaxCobrarController extends Controller {

	function Init(){
		$this->visor = new Visor();
		isset($_REQUEST['action'])?$this->action=$_REQUEST['action']:$this->action='';
	}

	function Run() {

		include 'movimientos/m_elimina_cuentaxcobrar.php';
		include 'movimientos/t_elimina_cuentaxcobrar.php';

		$this->Init();
		
		$result = "";
		$result_f = "";

		$buscar = false;
		$listado = false;

		switch($this->action) {

			case "Buscar":
				$listado = true;
				break;

			case "Agregar":

				$result	= EliminaCuentaxCobrarTemplate::formAgregar($_REQUEST['almacen'],$_REQUEST['fecha'],$_REQUEST['fecha2']);
				$result_f = "&nbsp;";
				break;

			case "Modificar":
				$resultado = EliminaCuentaxCobrarModel::recuperarRegistroArray($_REQUEST['es'],$_REQUEST['fecha'],$_REQUEST['turno'],$_REQUEST['descripcion']);
				$result	= EliminaCuentaxCobrarTemplate::formAgregar($resultado,$_REQUEST['dia'],$_REQUEST['dia2']);
				$result_f = "&nbsp;";
				break;

			case "Eliminar":
				
				$var = EliminaCuentaxCobrarModel::eliminarRegistro($_REQUEST['id_cuadre_turno_ticket']);
					if($var == ok){
						$resultados = EliminaCuentaxCobrarModel::buscar("", $_REQUEST['dia'], $_REQUEST['dia2']);	    	
		    				$result_f  = EliminaCuentaxCobrarTemplate::listado($resultados, $_REQUEST['dia'], $_REQUEST['dia2']);
					}
				
			    	break;

			case "Actualizar":
				
				$var = EliminaCuentaxCobrarModel::actualizar($_REQUEST['almacen'],$_REQUEST['fecha'],$_REQUEST['turno'],$_REQUEST['descripcion'],$_REQUEST['fecha_actualizacion'],$_SESSION['auth_usuario'],$ip);
					if ($var == ''){
						?><script>alert('Registro actualizado correctamente')</script><?php
					}else{					
						?><script>alert('Registro no modificado')</script><?php
					}
				break;
	
			case "Guardar":

				if($_REQUEST['descripcion'] == ''){
					$result_f = "<script>alert('Falta llenar campo de Observacion');</script>";				
				}else{
					$res = EliminaCuentaxCobrarModel::agregar($_REQUEST['almacen'],$_REQUEST['fecha'],$_REQUEST['turno'],$_REQUEST['descripcion'],$_REQUEST['fecha_actualizacion'],$_SESSION['auth_usuario'],$ip);		
					if($res==1){
						?><script>alert('Registro guardado correctamente')</script><?php		
					}else{
						?><script>alert('Este registro ya existe ingresar otro');</script><?php	
					}
				}				
				break;

			default:
				$buscar = true;
				break;

		}

		if ($buscar) {
		    	$result = EliminaCuentaxCobrarTemplate::formSearch($_REQUEST['almacen'], "", "");
		}

		if ($listado) {
			$result    = EliminaCuentaxCobrarTemplate::formSearch($_REQUEST['almacen'], $_REQUEST['fecha'], $_REQUEST['fecha2']);		
		    	$resultados = EliminaCuentaxCobrarModel::buscar($_REQUEST['almacen'], $_REQUEST['fecha'], $_REQUEST['fecha2']);	    	
		    	$result_f  = EliminaCuentaxCobrarTemplate::listado($resultados, $_REQUEST['fecha'], $_REQUEST['fecha2']);
		}

		$this->visor->addComponent("ContentT", "content_title", EliminaCuentaxCobrarTemplate::titulo());
		if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);

	}

}
