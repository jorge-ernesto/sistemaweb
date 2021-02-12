<?php
  // Controlador del Modulo Generales

  Class RecCompDevController extends Controller{
    function Init(){
      //Verificar seguridad
      $this->visor = new Visor();
      $this->task = @$_REQUEST["task"];
      $this->action = isset($_REQUEST["action"])?$_REQUEST["action"]:'';
      $this->datos = isset($_REQUEST["datos"])?$_REQUEST["datos"]:'';
      //otros variables de entorno
    }

    function Run()
    {
      $this->Init();
      $result = '';
      include('movimientos/m_reccompdev.php');
      include('movimientos/t_reccompdev.php');
      include('../include/paginador_new.php');
      $this->visor->addComponent('ContentT', 'content_title', RecCompDevTemplate::titulo());
      if(!@$_REQUEST['rxp'] && !@$_REQUEST['pagina'])
      {
        //echo "entro rp\n";
         $_REQUEST['rxp'] = 100;
         $_REQUEST['pagina'] = 0;
      }
      
      if(!empty($_REQUEST['numero_registros']) && $_REQUEST['rxp'] != $_REQUEST['numero_registros'])
      {
	//echo "entroe \n";
	$_REQUEST['rxp'] = $_REQUEST['numero_registros'];
      }

      //print_r($_REQUEST);
      switch ($this->request)
      {//task
      case 'RECCOMPDEV':
      //echo "ENTRO";
	$listado = false;
	//evaluar y ejecutar $action
	switch ($this->action)
	{
	    
	    /*case 'Agregar':
	    $result = RecCompDevTemplate::formRecCompDev(array());
	    $this->visor->addComponent("ContentB", "content_body", $result);
	    break;*/
    
	    /*case 'Eliminar':
	    $result = RecCompDevModel::eliminarRegistro($_REQUEST["registroid"]);
	    if ($result == OK){
		$listado= true;
	    } else {
		$result = RecCompDevTemplate::errorResultado($result);
		$this->visor->addComponent("ContentB", "content_body", $result);
	    }
	    break;*/
    
	    case 'Modificar':
	      $result = RecCompDevModel::guardarRegistro();
	      $listado = true;
	    break;
    
	    /*case 'Guardar':
	      $result = RecCompDevModel::guardarRegistro($this->datos);
	      $listado = true;
	    break;*/
    
	    case 'Buscar':
	    //Listo
	     /* $query = RecCompDevModel::GeneraQuery($_REQUEST["busqueda"]);
	      $busqueda = RecCompDevModel::tmListado($query, $_REQUEST['rxp'],$_REQUEST['pagina']);
	      $result = RecCompDevTemplate::listado($busqueda['datos']);
	      $this->visor->addComponent("ListadoB", "resultados_grid", $result);*/

	      $query = RecCompDevModel::GeneraQuery(@$_REQUEST["busqueda"]);
	      $listado    = RecCompDevModel::tmListado($query,$_REQUEST['rxp'],$_REQUEST['pagina']);
	      $result     =  RecCompDevTemplate::formBuscar($listado['paginacion'],$_REQUEST["busqueda"]);
	      $result     .= RecCompDevTemplate::listado($listado['datos']);
	      $this->visor->addComponent("ContentB", "content_body", $result);
	    
	    break;

	    case 'Reporte':
	     //print_r($_REQUEST);
	       //echo "ENTRO\n";
		include 'movimientos/t_reccompdev_reporte.php';
	   
		$query = RecCompDevModel::GeneraQuery($_REQUEST["busqueda"]);
		$reporte_array = RecCompDevModel::tmListado($query,$_REQUEST['rxp'],$_REQUEST['pagina']);
		$this->visor->addComponent("ListadoB", "resultados_grid", RecCompDevReporteTemplate::ReportePDF($reporte_array));
	    
	      /*$query = RecCompDevModel::GeneraQuery(@$_REQUEST["busqueda"]);
	      $listado    = RecCompDevModel::tmListado($query,$_REQUEST['rxp'],$_REQUEST['pagina']);
	      $result     =  RecCompDevTemplate::formBuscar($listado['paginacion'],$_REQUEST["busqueda"]);
	      $result     .= RecCompDevReporteTemplate::ReportePDF($listado);
	      $this->visor->addComponent("ContentB", "content_body", $result);*/
	    break;

	    default:
	    //listado
	    $listado = true;
	    //$this->visor->addComponent("ContentT","content_title",TarjetasMagneticasTemplate::titulo());
	    break;
	}
	
	if ($listado) 
	{
	    $query = RecCompDevModel::GeneraQuery(@$_REQUEST["busqueda"]);
	    $listado    = RecCompDevModel::tmListado($query,$_REQUEST['rxp'],$_REQUEST['pagina']);
	    $result     =  RecCompDevTemplate::formBuscar($listado['paginacion'],@$_REQUEST["busqueda"]);
	    $result     .= RecCompDevTemplate::listado($listado['datos']);
	    $this->visor->addComponent("ContentB", "content_body", $result);
	}
      break;
      case 'RECCOMPDEVDET':
       //echo "ENTRO 2";
        switch($this->action)
        {
          case 'setRegistroProv':
            $result = RecCompDevTemplate::setRegistroProv($_REQUEST["codigo"]);
            $this->visor->addComponent("descrip_proveedor", "descrip_proveedor", $result);
          break;
          
          case 'setRegistroArt':
            $result = RecCompDevTemplate::setRegistroArt($_REQUEST["codigo"]);
            $this->visor->addComponent("descrip_articulo", "descrip_articulo", $result);
          break;
          
          case 'setRegistroAlm':
            $result = RecCompDevTemplate::setRegistroAlm($_REQUEST["codigo"]);
            $this->visor->addComponent("descrip_almacen", "descrip_almacen", $result);
          break;
          
          default:
          //Casos por defecto
          break;
        }
     break;

      default:
        $this->visor->addComponent('ContentB', 'content_body', '<h2>TAREA "'.$this->request.'" NO CONOCIDA EN REGISTROS</h2>');
      break;
      }
    }
  }
