<?php
class CanjeitemController extends Controller{
	
    function Init(){
	//Verificar seguridad
      $this->visor = new Visor();
      $this->task = @$_REQUEST["task"];
      $this->action = isset($_REQUEST["action"])?$_REQUEST["action"]:'';
      //otros variables de entorno
    }

    function Run(){
      $this->Init();
      $result = '';
      $bolMensaje ='0';	
      include('promociones/m_canjeitem.php');
      include('promociones/t_canjeitem.php');
      include('../include/paginador_new.php'); 
	  require("../clases/funciones.php");	
      $funcion = new class_funciones;
	  
      $this->visor->addComponent('ContentT', 'content_title',CanjeitemTemplate::titulo());
      if(!$_REQUEST['rxp'] && !$_REQUEST['pagina'])
      {
         $_REQUEST['rxp'] = 100;
         $_REQUEST['pagina'] = 0;
      }
      switch ($this->request){//task
      case 'CANJEITEM':
	$tablaNombre = 'CANJEITEM';
	$listado = false;
	//evaluar y ejecutar $action
	switch ($this->action){

	     
	    case 'Buscar':
		    //Listo
			$tipo = strtoupper(trim($_REQUEST['tipobusqueda']));
			$filtro = strtoupper(trim($_REQUEST['busqueda']));
		    $busqueda = CanjeitemModel::tmListado($filtro,$tipo,$_REQUEST['rxp'],$_REQUEST['pagina']);
		    $result = CanjeitemTemplate::listado($busqueda['datos']);
		   	$this->visor->addComponent("ListadoB", "resultados_grid", $result);
		break;	
		case 'Nuevo Producto':
			//TITULO
			$_REQUEST['titulo'] ='NUEVO PRODUCTO';
			$result = CanjeitemTemplate::formProductocanje(array());
			$this->visor->addComponent("ContentB", "content_body", $result);
		break;
		case 'Guardar':

			$listado = false;
			$exito="";
			//1.- CAPTURAMOS LOS VALORES DEL FORMULARIO
			$item['id_item'] = trim($_REQUEST['iditem']);
			$item['art_codigo'] =trim($_REQUEST['itemarticulo']);
			$item['ch_item_descripcion'] = strtoupper(trim($_REQUEST['itemdescripcion']));
			$item['dt_item_fecha_vencimiento'] = trim($_REQUEST['itemfechaven']);
			$item['nu_item_puntos'] = (trim($_REQUEST['itempuntos'])==""?"0":$_REQUEST['itempuntos']);
			$item['ch_item_observacion'] = strtoupper(trim($_REQUEST['itemobservacion']));
			$item['id_campana'] = trim($_REQUEST['id_campana']);
			
			//2.- CAPTURAMOS VALORES DE CONTROL
			$usuario=$_SESSION['auth_usuario'];
			$sucursal =$_SESSION['almacen'];
			
			var_dump($_SESSION);
			var_dump($sucursal);
			
			//exit;

			if($_REQUEST['accion']=='actualizaritem'){
			$result = CanjeitemModel::actualizarItem(				   $item['id_campana'],
												   $item['id_item'],
												   $item['art_codigo'],
												   $item['ch_item_descripcion'],
												   $item['dt_item_fecha_vencimiento'],
												   $item['nu_item_puntos'],
												   $item['ch_item_observacion'],
												   $usuario,
												   $sucursal);

			}else{
			$result = CanjeitemModel::ingresarItem(                                   
												   $item['id_campana'],
												   $item['art_codigo'],
												   $item['ch_item_descripcion'],
												   $item['dt_item_fecha_vencimiento'],
												   $item['nu_item_puntos'],
												   $item['ch_item_observacion'],
												   $usuario,
												   $sucursal);
			
			}

			$exito= ($result=="0")?"0":"1";

			if ($exito =="1"){
					$_REQUEST['titulo'] ='INGRESAR PRODUCTO';
					$result = CanjeitemTemplate::formProductocanje(array());
					$this->visor->addComponent("ContentB", "content_body", $result);
					$result = CanjeitemTemplate::errorResultado('¡ SE GRABO/ACTUALIZO CORRECTAMENTE LOS DATOS !');
					$this->visor->addComponent("error", "error_body", $result);	
			}else{
				$result =  CanjeitemTemplate::errorResultado('¡ ERROR: CÓDIGO DE 
				ARTÍCULO NO ES  CORRECTO O NO EXISTE, INGRESE OTRO !');
				$this->visor->addComponent("error", "error_body", $result);
			}	
					
			
		break;
		
		case 'Modificar':
				//1.- CAPTURAMOS LOS VALORES DEL FORMULARIO
			$_REQUEST['titulo'] ='MODIFICAR PRODUCTO';
			$item['id_item'] = $_REQUEST['itemid'];
			$item['art_codigo'] = $_REQUEST['articulocod'];
			$item['ch_item_descripcion'] = $_REQUEST['itemdescripcion'];
			$item['dt_item_fecha_creacion'] = $_REQUEST['itemfechacre'];
			$item['dt_item_fecha_vencimiento'] = $_REQUEST['itemfechaven'];
			$item['nu_item_puntos'] = (trim($_REQUEST['itempuntos'])==""?"0":$_REQUEST['itempuntos']);
			$item['ch_item_observacion'] = $_REQUEST['itemobservacion'];
			$item['id_campana'] = $_REQUEST['id_campana'];
			$item['ch_campana_descripcion'] = $_REQUEST['ch_campana_descripcion'];
			
			$result = CanjeitemTemplate::formProductocanje($item);
			$this->visor->addComponent("ContentB", "content_body", $result);
		break;
		
		case 'Eliminar':
			$result = CanjeitemModel::eliminarItem(trim($_REQUEST['itemid']));
			$listado  = CanjeitemModel::tmListado(' ','default',$_REQUEST['rxp'],$_REQUEST['pagina']);
		    $result    = CanjeitemTemplate::formBuscar($listado['paginacion']);   
		    $result   .= CanjeitemTemplate::listado($listado['datos']);
  		    $this->visor->addComponent("ListadoB", "resultados_grid", $result);
			//$this->visor->addComponent("ContentB", "content_body", $result);
			break;
			
    
	    default:
		   //listado
		   $listado = true;
		   break;
	}

		if ($listado) { 
		    $listado  = CanjeitemModel::tmListado(' ','default',$_REQUEST['rxp'],$_REQUEST['pagina']);
		    $result    = CanjeitemTemplate::formBuscar($listado['paginacion']);   
		    $result   .= CanjeitemTemplate::listado($listado['datos']);
  		    $this->visor->addComponent("ContentB", "content_body", $result);
		}

  
      }
    }
  }

