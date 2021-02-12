<?php

class ReporteTurnoController extends Controller
{
     function __construct($visor)
    {
      include("m_turno.php");
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
     var_dump($this->reporte);
        ob_start();
        include("t_turno.php");
        $out1 = ob_get_contents();
        $this->visor->addComponent("Content", "content", $out1);
    }
    
}

