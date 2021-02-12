<?php

  class HorarioMultiController extends Controller{
	
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
      include('promociones/m_horariomulti.php');
      include('promociones/t_horariomulti.php');
      include('../include/paginador_new.php'); 
	  require("../clases/funciones.php");	
      $funcion = new class_funciones;
	  
      $this->visor->addComponent('ContentT', 'content_title',HorarioMultiTemplate::titulo());
      if(!$_REQUEST['rxp'] && !$_REQUEST['pagina'])
      {
         $_REQUEST['rxp'] = 100;
         $_REQUEST['pagina'] = 0;
      }
      switch ($this->request){//task
      case 'HORARIOMULTI':
	$tablaNombre = 'HORARIOMULTI';
	$listado = false;
	//evaluar y ejecutar $action
	switch ($this->action){

	     
	    case 'Buscar':
		    //Listo
			$filtro = strtoupper(trim($_REQUEST['busqueda']));
		    $busqueda = HorarioMultiModel::tmListado($filtro,$_REQUEST['rxp'],$_REQUEST['pagina']);
		    $result = HorarioMultiTemplate::listado($busqueda['datos']);
		   	$this->visor->addComponent("ListadoB", "resultados_grid", $result);
		break;	
		case 'Nuevo Horario':
			//TITULO
			$_REQUEST['titulo'] ='NUEVO HORARIO';
			$result = HorarioMultiTemplate::formHorarioMulti(array());
			$this->visor->addComponent("ContentB", "content_body", $result);
		break;
		case 'Guardar':
			$listado = false;
			$exito="";
			//1.- CAPTURAMOS LOS VALORES DEL FORMULARIO
			$horamulti['idhorariomulti'] = trim($_REQUEST['idhorariomulti']);
			$horamulti['idcampania'] = trim($_REQUEST['idcampania']);
			$horamulti['descripcion'] =strtoupper(trim($_REQUEST['horamultidescripcion']));
			$horamulti['dias'] = trim($_REQUEST['horamultidias']);
			$horamulti['horaini'] = trim($_REQUEST['horamultihoraini']);
			$horamulti['minutoini'] = trim($_REQUEST['horamultiminutoini']);
			$horamulti['horafin'] = trim($_REQUEST['horamultihorafin']);
			$horamulti['minutofin'] = trim($_REQUEST['horamultiminutofin']);
			$horamulti['factor'] = trim($_REQUEST['horamultifactor']);
			
			
			//2.- CAPTURAMOS VALORES DE CONTROL
			$usuario=$_SESSION['auth_usuario'];
			$sucursal =$_SESSION['almacen'];
			
			if($_REQUEST['accion']=='actualizarhorariomulti'){
			$result = HorarioMultiModel::actualizarHorario($horamulti['idhorariomulti'],
												   $horamulti['idcampania'] ,
												   $horamulti['descripcion'],
												   $horamulti['dias'],
												   $horamulti['horaini'],
												   $horamulti['minutoini'],
												   $horamulti['horafin'], 
												   $horamulti['minutofin'],
												   $horamulti['factor'],
												   $usuario,
												   $sucursal);

			}else{
			$result = HorarioMultiModel::ingresarHorario($horamulti['idcampania'] ,
												   $horamulti['descripcion'],
												   $horamulti['dias'],
												   $horamulti['horaini'],
												   $horamulti['minutoini'],
												   $horamulti['horafin'], 
												   $horamulti['minutofin'],
												   $horamulti['factor'],
												   $usuario,
												   $sucursal);
			
			}
			$exito= ($result=="0")?"0":"1";

			if ($exito =="1"){
					$_REQUEST['titulo'] ='INGRESAR PRODUCTO';
					$result = HorarioMultiTemplate::formHorarioMulti(array());
					$this->visor->addComponent("ContentB", "content_body", $result);
					$result = HorarioMultiTemplate::errorResultado('¡ SE GRABO/ACTUALIZO CORRECTAMENTE LOS DATOS !');
					$this->visor->addComponent("error", "error_body", $result);	
			}else{
				$result =  HorarioMultiTemplate::errorResultado('¡ ERROR: AL REGISTRAR EL HORARIO, VERIQUE LOS DATOS !');
				$this->visor->addComponent("error", "error_body", $result);
			}	
					
			
		break;
		
		case 'Modificar':
				//1.- CAPTURAMOS LOS VALORES DEL FORMULARIO
			$_REQUEST['titulo'] ='MODIFICAR HORARIO';
			$horamulti['idhorariomulti'] = trim($_REQUEST['idhorariomulti']);
			$horamulti['idcampania'] = trim($_REQUEST['idcampania']);
			$horamulti['desccampania'] =strtoupper(trim($_REQUEST['horamultidesccampania']));
			$horamulti['descripcion'] =strtoupper(trim($_REQUEST['horamultidescripcion']));
			$horamulti['fechacrea'] = trim($_REQUEST['horamultifechacrea']);
			$horamulti['dias'] = trim($_REQUEST['horamultidias']);
			$horamulti['horaini'] = trim($_REQUEST['horamultihoraini']);
			$horamulti['minutoini'] = trim($_REQUEST['horamultiminutoini']);
			$horamulti['horafin'] = trim($_REQUEST['horamultihorafin']);
			$horamulti['minutofin'] = trim($_REQUEST['horamultiminutofin']);
			$horamulti['factor'] = trim($_REQUEST['horamultifactor']);
			
			$result = HorarioMultiTemplate::formHorarioMulti($horamulti);
			$this->visor->addComponent("ContentB", "content_body", $result);
		break;
		
		case 'Eliminar':
			$result = HorarioMultiModel::eliminarHorario(trim($_REQUEST['idhorariomulti']));
			$listado  = HorarioMultiModel::tmListado(' ','default',$_REQUEST['rxp'],$_REQUEST['pagina']);
		    $result    = HorarioMultiTemplate::formBuscar($listado['paginacion']);   
		    $result   .= HorarioMultiTemplate::listado($listado['datos']);
  		    $this->visor->addComponent("ListadoB", "resultados_grid", $result);
			//$this->visor->addComponent("ContentB", "content_body", $result);
			break;
			
    
	    default:
		   //listado
		   $listado = true;
		   break;
	}

		if ($listado) { 
		    $listado  = HorarioMultiModel::tmListado(' ',$_REQUEST['rxp'],$_REQUEST['pagina']);
		    $result    = HorarioMultiTemplate::formBuscar($listado['paginacion']);   
		    $result   .= HorarioMultiTemplate::listado($listado['datos']);
  		    $this->visor->addComponent("ContentB", "content_body", $result);
		}

  
      }
    }
  }

