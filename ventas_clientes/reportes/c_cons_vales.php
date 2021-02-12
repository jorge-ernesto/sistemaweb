<?php
Class ConsvalesController extends Controller{
  function Init(){
    //Verificar seguridad
    $this->visor = new Visor();
    $this->task = @$_REQUEST["task"];
    isset($_REQUEST["action"])?$this->action = $_REQUEST["action"]:$this->action = '';
    //otros variables de entorno
  }

  function Run(){
    $this->Init();
    
    $result = '';
    $Controlador = null;

    include('reportes/m_cons_vales.php');
    include('reportes/t_cons_vales.php');

    if (!isset($_REQUEST["action"]))
      $this->visor->addComponent('ContentT', 'content_title', ConsvalesTemplate::titulo());
    
    switch ($this->request){
      case 'CONSUMO_VALES':
        if($this->action == 'Reporte'){
          $Parametros['factura'] = trim($_REQUEST['reporte']['cli_fac']);
          $Parametros['cod_liq'] = trim($_REQUEST['reporte']['ch_liquidacion']);
        
          include('reportes/t_cons_vales_report.php');//Template para generar los PDF's
          
          $reporte = ConsValesModel::GeneraDatosReportePDF($Parametros);
          $this->visor->addComponent("result", "reporte", ConsvalesPDFTemplate::reporteComprobacion($reporte));
        } else {
          $this->visor->addComponent("ContentB", "content_body", ConsvalesTemplate::formReporte());
        }
      break;
      
      default:
        $this->visor->addComponent("ContentB", "content_body", "<h2>REPORTE NO CONOCIDO</h2>");
      break;
    }
    
    if ($Controlador != null){
      $Controlador->Run();
      $this->visor = $Controlador->visor;
    }
  }
}
