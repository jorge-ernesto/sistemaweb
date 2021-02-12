<?php

date_default_timezone_set('UTC');

class DaotController extends Controller{

	function Init(){
		$this->visor = new Visor();
		isset($_REQUEST['action']) ? $this->action = $_REQUEST['action'] : $this->action = "";
	}

	function Run() 	{
	
		include('reportes/m_daot.php');
		include('reportes/t_daot.php');
	
		$this->Init();

		$result = "";
		$result_f = "";
		$listado = false;

		switch ($this->action)
		{
			case 'Descargar DAOT':
				DaotModel::generarDaot($_REQUEST["anio"], $_REQUEST["base"], $_REQUEST["ruc"]);	
				$datos_pdf = DaotModel::buscarDaotxRuc();
				$cabecera = DaotModel::cabeceraReporte();
				DaotTemplate::reportePDF($datos_pdf, $cabecera);
				$this->visor->addComponent("ContentB", "content_body",DaotTemplate::hipervinculoDaot($_REQUEST["anio"], $_REQUEST["ruc"]));
				$listado=true;
			break;

			default:
			break;
		}
		
		if ($listado) {
			$resultados = DaotModel::buscarDetalladoDaot($_REQUEST["base"],$_REQUEST["ruc"]);
			$result_f =  DaotTemplate::listado($resultados);
		}

		$this->visor->addComponent("ContentT","content_title",DaotTemplate::formBuscar($_REQUEST));

		if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);
	}

 }

