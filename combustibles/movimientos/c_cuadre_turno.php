<?php
class CuadreTurnoController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action'])?$this->action=$_REQUEST['action']:$this->action='';
	}

	function Run() {

		ob_start();
		include 'movimientos/m_cuadre_turno.php';
		include 'movimientos/t_cuadre_turno.php';

		$this->Init();
		
		$result = "";
		$result_f = "";

		$buscar = false;
		$listado = false;

		$ip = "";

		if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"),"unknown"))
			$ip = getenv("HTTP_CLIENT_IP");
		else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
			$ip = getenv("HTTP_X_FORWARDED_FOR");
		else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
			$ip = getenv("REMOTE_ADDR");
		else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
			$ip = $_SERVER['REMOTE_ADDR'];
	

		switch($this->action) {

			case "Buscar":
				$listado = true;
				break;

			case "Agregar":

				$result	= CuadreTurnoTemplate::formAgregar($_REQUEST['almacen'],$_REQUEST['fecha'],$_REQUEST['fecha2']);
				$result_f = "&nbsp;";
				break;

			case "Modificar":
				$resultado = CuadreTurnoModel::recuperarRegistroArray($_REQUEST['es'],$_REQUEST['fecha'],$_REQUEST['turno'],$_REQUEST['descripcion']);
				$result	= CuadreTurnoTemplate::formAgregar($resultado,$_REQUEST['dia'],$_REQUEST['dia2']);
				$result_f = "&nbsp;";
				break;

			case "Eliminar":
				
				$var = CuadreTurnoModel::eliminarRegistro($_REQUEST['id_cuadre_turno_ticket']);
					if($var == ok){
						$resultados = CuadreTurnoModel::buscar("", $_REQUEST['dia'], $_REQUEST['dia2']);	    	
		    				$result_f  = CuadreTurnoTemplate::listado($resultados, $_REQUEST['dia'], $_REQUEST['dia2']);
					}
				
			    	break;

			case "Actualizar":
				
				$var = CuadreTurnoModel::actualizar($_REQUEST['almacen'],$_REQUEST['fecha'],$_REQUEST['turno'],$_REQUEST['descripcion'],$_REQUEST['fecha_actualizacion'],$_SESSION['auth_usuario'],$ip);
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
					$res = CuadreTurnoModel::agregar($_REQUEST['almacen'],$_REQUEST['fecha'],$_REQUEST['turno'],$_REQUEST['descripcion'],$_REQUEST['fecha_actualizacion'],$_SESSION['auth_usuario'],$ip);		

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
		    	$result = CuadreTurnoTemplate::formSearch($_REQUEST['almacen'], "", "");
		}

		if ($listado) {
			$result    = CuadreTurnoTemplate::formSearch($_REQUEST['almacen'], $_REQUEST['fecha'], $_REQUEST['fecha2']);		
		    	$resultados = CuadreTurnoModel::buscar($_REQUEST['almacen'], $_REQUEST['fecha'], $_REQUEST['fecha2']);	    	
		    	$result_f  = CuadreTurnoTemplate::listado($resultados, $_REQUEST['fecha'], $_REQUEST['fecha2']);
		}

		$this->visor->addComponent("ContentT", "content_title", CuadreTurnoTemplate::titulo());
		if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);

	}

}
