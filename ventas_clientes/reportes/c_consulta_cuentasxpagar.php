<?php

class ConsultaCuentaxPagarController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action'])?$this->action = $_REQUEST['action']:$this->action="";
    	}
    
    	function Run() {
		include 'reportes/m_consulta_cuentasxpagar.php';
		include 'reportes/t_consulta_cuentasxpagar.php';
	
		$this->Init();
	
		$result   = "";
		$result_f = "";

		$form_search = false;

		switch ($this->action) { 

			case "Consulta":
				$resultado 	= ConsultaCuentaxPagarModel::CuentasCobrar($_REQUEST['estacion']);
			    	$result 	= ConsultaCuentaxPagarTemplate::FormConsulta($_REQUEST['ch_almacen']);
				$result_f  	= ConsultaCuentaxPagarTemplate::FormBuscar($resultado,"",$_REQUEST['estacion'],"","","","","","","","","","");
			break;

			case "MostrarCab":
				
				$estacion 	= trim($_REQUEST['estacion']);
				$cliente 	= trim($_REQUEST['cliente']);
				$razsocial 	= trim($_REQUEST['razsocial']);

				$resultado 	= ConsultaCuentaxPagarModel::CuentasCobrar($estacion);
				$cobrarcab	= ConsultaCuentaxPagarModel::DocumentosCobrar($cliente,$estacion);
				$anticiposcab 	= ConsultaCuentaxPagarModel::DocumentosAnticipos($cliente,$estacion);
				$valescab	= ConsultaCuentaxPagarModel::DocumentosVales($cliente,$estacion);
				$result 	= ConsultaCuentaxPagarTemplate::FormConsulta($_REQUEST['ch_almacen']);
				$result_f  	= ConsultaCuentaxPagarTemplate::FormBuscar($resultado,$cobrarcab,"",$anticiposcab,"",$valescab,"",$estacion,$cliente,$razsocial,"","",$limite);
				
			break;


			case "MostrarDet":

				$estacion 	= trim($_REQUEST['estacion']);
				$cliente 	= trim($_REQUEST['cliente']);
				$razsocial 	= trim($_REQUEST['razsocial']);
				$documento	= trim($_REQUEST['documento']);
				$doc 		= trim($_REQUEST['doc']);

				$resultado 	= ConsultaCuentaxPagarModel::CuentasCobrar($estacion);
				$cobrarcab	= ConsultaCuentaxPagarModel::DocumentosCobrar($cliente,$estacion);
				$cobrardet 	= ConsultaCuentaxPagarModel::DocumentosCobrarDetalle($cliente,$estacion,$documento);
				$anticiposcab 	= ConsultaCuentaxPagarModel::DocumentosAnticipos($cliente,$estacion);
				$anticiposdet 	= ConsultaCuentaxPagarModel::DocumentosAnticiposDetalle($cliente,$estacion,$documento);
				$valescab	= ConsultaCuentaxPagarModel::DocumentosVales($cliente,$estacion);
				$valesdet 	= ConsultaCuentaxPagarModel::DocumentosValesDetalle($cliente,$estacion,$documento);
				$result 	= ConsultaCuentaxPagarTemplate::FormConsulta($_REQUEST['ch_almacen']);
				$result_f  	= ConsultaCuentaxPagarTemplate::FormBuscar($resultado,$cobrarcab,$cobrardet,$anticiposcab,$anticiposdet,$valescab,$valesdet,$estacion,$cliente,$razsocial,$documento,$doc,$limite);

			break;

		    	default:
				$form_search = true;
			break;
		}
	
		if ($form_search) {
		    	$result = ConsultaCuentaxPagarTemplate::FormConsulta($_REQUEST['ch_almacen']);
		}

		$this->visor->addComponent("ContentT", "content_title", ConsultaCuentaxPagarTemplate::titulo());
		if ($result != "") 
			$this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != "") 
			$this->visor->addComponent("ContentF", "content_footer", $result_f);	

    	}
}
