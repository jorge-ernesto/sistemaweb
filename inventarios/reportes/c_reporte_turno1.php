<?php

class ReporteTurnoController extends Controller
{
     function __construct($visor)
    {
      include("m_reporte_turno.php");
      $this->visor=$visor;
     
    }
    
    function run()
    {
     $this->vista();
    }
    
    function vista()
    {	
       
       $this->estaciones=ReporteTurnoModel::obtieneListaEstaciones();
       
       $this->reporte=ReporteTurnoModel::reporte_turno();
     
        ob_start();
        include("t_reporte_turno1.php");
        $out1 = ob_get_contents();
        $this->visor->addComponent("Content", "content", $out1);
    }
    
}

