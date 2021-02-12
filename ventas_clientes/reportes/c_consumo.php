<?php

class ConsumoController extends Controller
{
    function Init()
    {
      $this->visor = new Visor();
      $this->task = @$_REQUEST["task"];
      $this->action = isset($_REQUEST["action"])?$_REQUEST["action"]:'';
      $this->datos = isset($_REQUEST["datos"])?$_REQUEST["datos"]:'';
    }
    
    function Run()
    {
	include "reportes/m_consumo.php";
	include "reportes/t_consumo.php";
	include('../include/paginador_new.php');
	include_once('../include/m_sisvarios.php');
	
	$this->Init();
	
	$result = '';
	//print_r($_REQUEST);
	$this->visor->addComponent('ContentT', 'content_title', ConsumoTemplate::titulo());
	if(!@$_REQUEST['rxp'] && !@$_REQUEST['pagina'])
	{
	  //echo "entro rp\n";
	  $_REQUEST['rxp'] = 100;
	  $_REQUEST['pagina'] = 0;
	}elseif($_REQUEST['rxp']=='P'){
	  $_REQUEST['rxp'] = 100;
	  $_REQUEST['pagina'] = 0;
	}
	
	if($_REQUEST['numero_registros']!='P' && !empty($_REQUEST['numero_registros']) && $_REQUEST['rxp'] != $_REQUEST['numero_registros'])
	{
	  //echo "entro \n";
	  $_REQUEST['rxp'] = $_REQUEST['numero_registros'];
	}

	switch ($this->request)
	{//task
	case 'CONSUMO':
	//echo "ENTRO CONS";
	  $listado = false;
	  //evaluar y ejecutar $action
	  switch ($this->action)
	  {    
	      case 'Buscar':
	      //Listo
	      //echo "ENTRO BUSCAR\n";
		$listadoB    = ConsumoModel::Listado($_REQUEST['busqueda'],$_REQUEST['rxp'],$_REQUEST['pagina']);
		$result     =  ConsumoTemplate::formBuscar($listadoB['paginacion'],$_REQUEST["busqueda"]);
		$result     .= ConsumoTemplate::listado($listadoB['datos']);
		$this->visor->addComponent("ContentB", "content_body", $result);
	      
	      break;
  
	      case 'Generar archivo':
	      //print_r($_REQUEST);
		//echo "ENTRO\n";
		  include 'reportes/t_consumo_excel.php';
		  //$query = ConsumoModel::GeneraQuery($_REQUEST["busqueda"]);
		  //$reporte_array = ConsumoModel::tmListado($query,$_REQUEST['rxp'],$_REQUEST['pagina']);
		  $reporte_array = ConsumoModel::Listado($_REQUEST['busqueda'],$_REQUEST['rxp'],$_REQUEST['pagina']);
		  //GeneraExcelConsumoTemplate::ReporteExcel(@$reporte_array);
		  $this->visor->addComponent("ListadoB", "resultados_grid", GeneraExcelConsumoTemplate::ReporteExcel(@$reporte_array['datos']));
	      break;
  
	      default:
	      //listado
	      $listado = true;
	      //$this->visor->addComponent("ContentT","content_title",TarjetasMagneticasTemplate::titulo());
	      break;
	  }
	  
	  if ($listado) 
	  {
	    //echo "ENTRO\n";
	      //$query = ConsumoModel::GeneraQuery(@$_REQUEST["busqueda"]);
	      $listado    = ConsumoModel::Listado(@$_REQUEST["busqueda"],$_REQUEST['rxp'],$_REQUEST['pagina']);
	      $result     =  ConsumoTemplate::formBuscar($listado['paginacion'],@$_REQUEST["busqueda"]);
	      $result     .= ConsumoTemplate::listado($listado['datos']);
	      $this->visor->addComponent("ContentB", "content_body", $result);
	  }
	break;
	case 'CONSUMODET':
	//echo "ENTRO 2";
	  switch($this->action)
	  {
	    case 'setRegistroCli':
	      $result = ConsumoTemplate::setRegistroCli($_REQUEST["codigo"]);
	      $this->visor->addComponent("descrip_cliente", "descrip_cliente", $result);
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

