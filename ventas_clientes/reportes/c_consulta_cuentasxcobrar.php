<?php

class ConsultaCuentaxCobrarController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action'])?$this->action = $_REQUEST['action']:$this->action="";
    	}
    
    	function Run() {
		include 'reportes/m_consulta_cuentasxcobrar.php';
		include 'reportes/t_consulta_cuentasxcobrar.php';
		include 'maestros/m_cliente.php';
	
		$this->Init();
	
		$result   = "";
		$result_f = "";

		$form_search = false;

		switch ($this->action) { 

			case "Consulta":
				$resultado 	= ConsultaCuentaxCobrarModel::CuentasCobrar($_REQUEST['estacion'],$_REQUEST['tipo']);
			    $result 	= ConsultaCuentaxCobrarTemplate::FormConsulta($_REQUEST['estacion']);
				$result_f  	= ConsultaCuentaxCobrarTemplate::FormBuscar($resultado,"",$_REQUEST['estacion'],"","","","","","","","","","");
			break;

			case "MostrarCab":
				
				$estacion 	= trim($_REQUEST['estacion']);
				$tipo 		= trim($_REQUEST['tipo']);
				$cliente 	= trim($_REQUEST['cliente']);
				$razsocial 	= trim($_REQUEST['razsocial']);

				$resultado 	= ConsultaCuentaxCobrarModel::CuentasCobrar($estacion,$tipo);
				$cobrarcab	= ConsultaCuentaxCobrarModel::DocumentosCobrar($cliente,$estacion);
				$anticiposcab 	= ConsultaCuentaxCobrarModel::DocumentosAnticipos($cliente,$estacion);
				$valescab	= ConsultaCuentaxCobrarModel::DocumentosVales($cliente,$estacion);
				$result 	= ConsultaCuentaxCobrarTemplate::FormConsulta($_REQUEST['ch_almacen']);
				$result_f  	= ConsultaCuentaxCobrarTemplate::FormBuscar($resultado,$cobrarcab,"",$anticiposcab,"",$valescab,"",$estacion,$cliente,$razsocial,"","",$limite);
				
			break;


			case "MostrarDet":

				$estacion 	= trim($_REQUEST['estacion']);
				$tipo 		= trim($_REQUEST['tipo']);
				$cliente 	= trim($_REQUEST['cliente']);
				$razsocial 	= trim($_REQUEST['razsocial']);
				$documento	= trim($_REQUEST['documento']);
				$doc 		= trim($_REQUEST['doc']);

				$resultado 	= ConsultaCuentaxCobrarModel::CuentasCobrar($estacion,$tipo);
				$cobrarcab	= ConsultaCuentaxCobrarModel::DocumentosCobrar($cliente,$estacion);
				$cobrardet 	= ConsultaCuentaxCobrarModel::DocumentosCobrarDetalle($cliente,$estacion,$documento);
				$anticiposcab 	= ConsultaCuentaxCobrarModel::DocumentosAnticipos($cliente,$estacion);
				$anticiposdet 	= ConsultaCuentaxCobrarModel::DocumentosAnticiposDetalle($cliente,$estacion,$documento);
				$valescab	= ConsultaCuentaxCobrarModel::DocumentosVales($cliente,$estacion);
				$valesdet 	= ConsultaCuentaxCobrarModel::DocumentosValesDetalle($cliente,$estacion,$documento);
				$result 	= ConsultaCuentaxCobrarTemplate::FormConsulta($_REQUEST['ch_almacen']);
				$result_f  	= ConsultaCuentaxCobrarTemplate::FormBuscar($resultado,$cobrarcab,$cobrardet,$anticiposcab,$anticiposdet,$valescab,$valesdet,$estacion,$cliente,$razsocial,$documento,$doc,$limite);

			break;

		    	default:
				$form_search = true;
			break;
		}
	
		if ($form_search) {
		    	$result = ConsultaCuentaxCobrarTemplate::FormConsulta($_REQUEST['ch_almacen']);
		}

		$this->visor->addComponent("ContentT", "content_title", ConsultaCuentaxCobrarTemplate::titulo());
		if ($result != "") 
			$this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != "") 
			$this->visor->addComponent("ContentF", "content_footer", $result_f);	

    	}
}
