<?php

class ReporteGeneralController extends Controller{
		
	function Init(){
		$this->visor = new Visor();
		isset($_REQUEST['action']) ? $this->action = $_REQUEST['action']:$this->action='';
	}
	
	function Run(){
	    ob_start();

		include('reportes/ccob_estado_cuenta_general_reporte.php');
		include('reportes/m_reporte_general.php');
		include('reportes/t_reporte_general.php');
		include('store_procedures.php'); 
		
		$this->Init();	
		$result = '';
		$result_f = '';

		switch ($this->action) {
			case "SerieDocumento":
				$this->visor->addComponent("SpaceSeries", "space", ReporteGeneralTemplate::listaSeries());
			return;

			case "Reporte":
				$seriesdocumentos	= $_REQUEST['seriesdocs'];
				$fecha_hasta		= trim($_REQUEST['c_fecha_hasta']);
				$dia_vencimiento	= trim($_REQUEST['c_dias_vcmt']);
				$vale 				= trim($_REQUEST['chk_vales']);
				$codcliente 		= trim($_REQUEST['codcliente']);
				$cliente			= trim($_REQUEST['c_todos_clientes']);
				$categoria 			= trim($_REQUEST['c_categoria']);
				$porgrupo 			= trim($_REQUEST['c_grupoemp_cliente']);
				$precancelado 		= trim($_REQUEST['c_precancelado']);
				$serie 				= trim($_REQUEST['c_serie']);
				$tasa_cambio		= trim($_REQUEST['c_tasa_cambio']);

				if($porgrupo == "GRUPOEMP"){
					$res		= ReporteGeneralModel::busquedaGrupo($fecha_hasta,$seriesdocumentos,$dia_vencimiento,$vale,$codcliente,$cliente,$categoria,$serie);
					$resta		= ReporteGeneralModel::busquedaClienteVales($fecha_hasta,$cliente,$codcliente);
					$result_f 	= ReporteGeneralTemplate::mostrar($resta,$res,$vale,$cliente,$porgrupo);
					$result_f 	.= ReporteGeneralTemplatePDF::ReportePDF($res,$resta,$fecha_hasta,$tasa_cambio,$porgrupo,$vale,"");
				}else{
					$res		= ReporteGeneralModel::busquedaCliente($fecha_hasta,$seriesdocumentos,$dia_vencimiento,$vale,$codcliente,$cliente,$categoria,$serie);
					if($vale == '1'){
						$resta		= ReporteGeneralModel::busquedaClienteVales($fecha_hasta,$cliente,$codcliente);
						$result_f 	= ReporteGeneralTemplate::mostrar($resta,$res,$vale,$cliente);
					}
					//REPORTE EN PDF
					$result_f 	.= ReporteGeneralTemplatePDF::ReportePDF($res,$resta,$fecha_hasta,$datos['nu_tipocambio'],$porgrupo,$vale,$cliente);
				}
				break;

			case "Excel":
				$seriesdocumentos	= $_REQUEST['seriesdocs'];
				$fecha_hasta		= trim($_REQUEST['c_fecha_hasta']);
				$dia_vencimiento	= trim($_REQUEST['c_dias_vcmt']);
				$vale 				= trim($_REQUEST['chk_vales']);
				$codcliente 		= trim($_REQUEST['codcliente']);
				$cliente			= trim($_REQUEST['c_todos_clientes']);
				$categoria 			= trim($_REQUEST['c_categoria']);
				$porgrupo 			= trim($_REQUEST['c_grupoemp_cliente']);
				$precancelado 		= trim($_REQUEST['c_precancelado']);
				$serie 				= trim($_REQUEST['c_serie']);
				$tasa_cambio		= trim($_REQUEST['c_tasa_cambio']);

				if ($porgrupo == "GRUPOEMP") {
					$res		= ReporteGeneralModel::busquedaGrupo($fecha_hasta,$seriesdocumentos,$dia_vencimiento,$vale,$codcliente,$cliente,$categoria,$serie);
					$resta		= ReporteGeneralModel::busquedaClienteVales($fecha_hasta,$cliente,$codcliente);
					$result_f 	= ReporteGeneralTemplate::reporteExcel($resta,$res,$vale,$cliente, $porgrupo);
				} else {
					$res		= ReporteGeneralModel::busquedaCliente($fecha_hasta,$seriesdocumentos,$dia_vencimiento,$vale,$codcliente,$cliente,$categoria,$serie);
					if($vale == '1'){
						$resta		= ReporteGeneralModel::busquedaClienteVales($fecha_hasta,$cliente,$codcliente);
						$result_f 	= ReporteGeneralTemplate::reporteExcel($resta,$res,$vale,$cliente, $porgrupo);
					}
				}

				break;

			default:
				$result = ReporteGeneralTemplate::formBuscar();		
				break;
		}
	
		$this->visor->addComponent("ContentT", "content_title", ReporteGeneralTemplate::titulo());
		if ($result != "")
			$this->visor->addComponent("ContentB", "content_body", $result);
			$this->visor->addComponent("SpaceSeries", "space", ReporteGeneralTemplate::listaSeries());
		if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);
	
	}
}
