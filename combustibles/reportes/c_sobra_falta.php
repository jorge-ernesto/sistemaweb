<?php

class SobraFaltaController extends Controller
{
    function Init()
    {
	    $this->visor = new Visor();
	    isset($_REQUEST['action'])?$this->action=$_REQUEST['action']:$this->action='';
	    isset($_REQUEST['reporte'])?$this->reporte=$_REQUEST['reporte']:$this->reporte='';
    }

    function Run()
    {
	    include 'reportes/m_sobra_falta.php';
	    include 'reportes/t_sobra_falta.php';

	    $this->Init();

	    $result = "";
	    $result_f = "";

	    //$form_search = false;
	    //$listado = false;

	    switch($this->action) 
	    {
                case 'Reporte':
                //echo "ENTRO\n";
                    include 'reportes/t_sobra_falta_reporte.php';
                    $reporte_array = SobraFaltaModel::SobraFaltaReporte($this->reporte);
                    //print_r($reporte_array);
                    $this->visor->addComponent("result", "reporte", SobraFaltaReporteTemplate::ReportePDF($reporte_array));
                  //  print_r($registros);
                    //$result = SobraFaltaTemplate::formReporteSobraFalta('');
                break;
                case 'setTanques':
                    $result = SobraFaltaTemplate::formReporteSobraFalta($_REQUEST['codigo']);
                break;
                default:
                    $result = SobraFaltaTemplate::formReporteSobraFalta('');
		break;
	    }

            //$result_f = SobraFaltaTemplate::formReporteSobraFalta();

	    $this->visor->addComponent("ContentT", "content_title", SobraFaltaTemplate::titulo());
	    if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
	    if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);
    }
    
}


