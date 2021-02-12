<?php
class MovpuntosController extends Controller{
	
    function Init(){
		//Verificar seguridad
    	$this->visor = new Visor();
    	$this->task = @$_REQUEST["task"];
    	$this->action = isset($_REQUEST["action"])?$_REQUEST["action"]:'';
    }

    function Run(){
    	$this->Init();
    	$result = '';
    	$bolMensaje ='0';	
    	include('promociones/m_movpuntos.php');
    	include('promociones/t_movpuntos.php'); 
    	include('../include/paginador_new.php');

		require("../clases/funciones.php");	
    	$funcion = new class_funciones;

    	$this->visor->addComponent('ContentT', 'content_title',MovpuntosTemplate::titulo());

		if(!$_REQUEST['rxp'] && !$_REQUEST['pagina']){
	        $_REQUEST['rxp'] = 100;
	        $_REQUEST['pagina'] = 0;
    	}

		$fechaini = date("d/m/Y");
		$fechafin = date("d/m/Y");

    	switch ($this->request){//task
    		case 'MOVPUNTOS':
				$tablaNombre = 'MOVPUNTOS';
				$listado = false;
				//evaluar y ejecutar $action
				switch ($this->action){
					case 'Consultar':
					    //Listo
						$filtro = strtoupper(trim($_REQUEST['busquedatarjeta']));
						$fechaini =trim($_REQUEST['fechainicio']);
						$fechafin =trim($_REQUEST['fechafin']);
						$objCuenta = MovpuntosModel::obtenerCuentaxTarjeta($filtro,"2");
						$objTarjeta =MovpuntosModel::obtenerTarjeta($filtro,"2");
					   	$busqueda = MovpuntosModel::tmListado($filtro,$fechaini,$fechafin,$_REQUEST['rxp'],$_REQUEST['pagina']);
						$resumen = MovpuntosModel::tmResumen($filtro,$fechaini,$fechafin);
						$result = MovpuntosTemplate::formBuscar($fechaini, $fechafin);
						$tamaniopuntos = count($busqueda['datos']);
						$result  .= MovpuntosTemplate::formMovimientopuntos($objCuenta,$objTarjeta,$tamaniopuntos);
						//agregado por DPC 09/05/09
						$result .= MovpuntosTemplate::formPaginacion($busqueda['paginacion'],$filtro,$fechaini,$fechafin,$tamaniopuntos);
						$result .= MovpuntosTemplate::listado($busqueda['datos']);
						$result .= MovpuntosTemplate::resumen($resumen['datos']);

						$this->visor->addComponent("ContentB", "content_body", $result);
					break;	
					
				    default:
					   $listado = true;
					   break;
				}

				if ($listado) { 
				    $result    = MovpuntosTemplate::formBuscar($fechaini, $fechafin); 
		  		    $this->visor->addComponent("ContentB", "content_body", $result);
				}
			break;
		}
	}
}

