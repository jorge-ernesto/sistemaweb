<?php
  // Controlador del Modulo Generales

  Class TransaccionesController extends Controller{
    function Init(){
      //Verificar seguridad
      $this->visor = new Visor();
      $this->task = @$_REQUEST["task"];
      $this->action = isset($_REQUEST["action"])?$_REQUEST["action"]:'';
      //otros variables de entorno
    }

    function Run()
    {
		$this->Init();
		$result = '';
		include('maestros/m_transacciones.php');
		include('maestros/t_transacciones.php');
		include('../include/paginador_new.php');
		$this->visor->addComponent('ContentT', 'content_title', TransaccionesTemplate::titulo());
		// aqui verifica si es la primera vez que hace la entrada
		if(!$_REQUEST['rxp'] && !$_REQUEST['pagina'])
		{
			$_REQUEST['rxp'] = 100;
			$_REQUEST['pagina'] = 0;
		}
		if(!$_REQUEST['param_ano']  && !$_REQUEST['param_mes'])
		{
			$_REQUEST['param_ano'] = '2007';
			$_REQUEST['param_mes'] = '05';
		}
		
		
		$this->request="TRANSACCIONES";
		switch ($this->request)
		{//task
		case 'TRANSACCIONES':
		
			$listado = false;

			//evaluar y ejecutar $action
			switch ($this->action)
			{
		
				case 'Buscar':
					//Listo
					$_REQUEST['busqueda']['fecha']=$_REQUEST['fecha'];
					$busqueda = TransaccionesModel::tmListado($_REQUEST["busqueda"],$_REQUEST['rxp'],$_REQUEST['pagina'],$_REQUEST['param_ano'],$_REQUEST['param_mes']);
					$result = TransaccionesTemplate::listado($busqueda['datos']);
					$this->visor->addComponent("ListadoB", "resultados_grid", $result);
					break;
		
				default:
					$listado = true;
					//$this->visor->addComponent("ContentT","content_title",TransaccionesTemplate::titulo());
					break;
			}
			if ($listado) 
			{
				$listado    = TransaccionesModel::tmListado('',$_REQUEST['rxp'],$_REQUEST['pagina'],$_REQUEST['param_ano'],$_REQUEST['param_mes']);
				$result     =  TransaccionesTemplate::formBuscar($listado['paginacion']);
				$result     .= TransaccionesTemplate::listado($listado['datos']);
				$this->visor->addComponent("ContentB", "content_body", $result);
			}
		break;
		default:
			$this->visor->addComponent('ContentB', 'content_body', '<h2>TAREA "'.$this->request.'" NO CONOCIDA EN REGISTROS</h2>');
		break;
		}
    }
  }
