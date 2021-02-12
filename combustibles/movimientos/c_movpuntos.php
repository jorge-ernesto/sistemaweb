<?php
  // Controlador del Modulo Generales
	echo " apunto de ENTRar al controller asdasdan \n";
   
  Class MovpuntosController extends Controller{
	
    function Init(){
      echo " init ENTRO al controller targpromocion \n";
	//Verificar seguridad
      $this->visor = new Visor();
      $this->task = @$_REQUEST["task"];
      $this->action = isset($_REQUEST["action"])?$_REQUEST["action"]:'';
      //otros variables de entorno
    }

    function Run(){
 echo " run ENTRO al controller CANJEITEM \n";
      $this->Init();
      $result = '';
      $bolMensaje ='0';	
      include('movimientos/m_movpuntos.php');
      include('movimientos/t_movpuntos.php'); 
      include('../include/paginador_new.php');
	  require("../clases/funciones.php");	
      $funcion = new class_funciones;

      $this->visor->addComponent('ContentT', 'content_title',MovpuntosTemplate::titulo());
      if(!$_REQUEST['rxp'] && !$_REQUEST['pagina'])
      {
         $_REQUEST['rxp'] = 100;
         $_REQUEST['pagina'] = 0;
      }
	   echo "requs3 ".$this->request. "\n";
      switch ($this->request){//task
      case 'MOVPUNTOS':
      echo " ENTRO 1er switch";
	$tablaNombre = 'MOVPUNTOS';
	$listado = false;
	//evaluar y ejecutar $action
	//echo " action es: ".$this->action;
	switch ($this->action){

	     
	    case 'Consultar':
		ECHO "\n CONSULTADO. ..\n";
		    //Listo
			$filtro = strtoupper(trim($_REQUEST['busquedatarjeta']));
			$fechaini =trim($_REQUEST['fechainicio']);
			$fechafin =trim($_REQUEST['fechafin']);
			$objCuenta = MovpuntosModel::obtenerCuentaxTarjeta($filtro,"2");
			$objTarjeta =MovpuntosModel::obtenerTarjeta($filtro,"2");
		   	$busqueda = MovpuntosModel::tmListado($filtro,$fechaini,$fechafin,$_REQUEST['rxp'],$_REQUEST['pagina']);
			$result = MovpuntosTemplate::formBuscar();
			$tamaniopuntos = count($busqueda['datos']);
				$result  .= MovpuntosTemplate::formMovimientopuntos($objCuenta,$objTarjeta,$tamaniopuntos);
				//agregado por DPC 09/05/09
				$result .= MovpuntosTemplate::formPaginacion($busqueda['paginacion'],$filtro,$fechaini,$fechafin,$tamaniopuntos);
				$result .= MovpuntosTemplate::listado($busqueda['datos']);
				$this->visor->addComponent("ContentB", "content_body", $result);
		break;	
		
    
	    default:
		echo "default \n"; 
		   //listado
		   $listado = true;
		   break;
	}

		if ($listado) { 
		    $result    = MovpuntosTemplate::formBuscar(); 
  		    $this->visor->addComponent("ContentB", "content_body", $result);
		}

  
      }
    }
  }

