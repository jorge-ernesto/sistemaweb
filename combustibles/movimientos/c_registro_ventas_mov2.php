<?php

class RegistroVentasMOVController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action']) ? $this->action = $_REQUEST['action']:$this->action='';
	}

	function Run() {
		require('movimientos/m_registro_ventas_mov2.php');
		require('movimientos/t_registro_ventas_mov2.php');
		include('../include/paginador_new.php');

		$this->Init();

		$result 	= "";
		$result_f 	= "";

		if(!isset($_REQUEST['rxp'],$_REQUEST['pagina'])) {
			$_REQUEST['rxp'] = 100;
			$_REQUEST['pagina'] = 1;
		}

        switch($this->action) {        	
			case "Buscar":
				$reporte   = "HTML";  
				$res       = RegistroVentasMOVModel::Paginacion($_REQUEST['rxp'], $_REQUEST['pagina'], $reporte, $_REQUEST['almacen'], $_REQUEST['dia1'], $_REQUEST['dia2'], $_REQUEST['tipo_doc'], $_REQUEST['art_codigo'], $_REQUEST['art_cliente'], $_REQUEST['serie'], $_REQUEST['numero']);	
				echo "<script>console.log('REQUEST:, " . json_encode($_REQUEST) . "')</script>";
				echo "<script>console.log('" . json_encode($res) . "')</script>";
				$result    = RegistroVentasMOVTemplate::search_form($res["paginacion"], $_REQUEST['almacen'], $_REQUEST['dia1'], $_REQUEST['dia2'], $_REQUEST['tipo_doc'], $_REQUEST['art_codigo'], $_REQUEST['art_cliente'], $_REQUEST['serie'], $_REQUEST['numero']);		    	
				$result_f  = RegistroVentasMOVTemplate::reporte($res);						
				break;			
			
			case "Excel":
				$reporte = "Excel";
				$res       = RegistroVentasMOVModel::Paginacion($_REQUEST['rxp'], $_REQUEST['pagina'], $reporte, $_SESSION['almacen'], $_REQUEST['dia1'], $_REQUEST['dia2'],$_REQUEST['tipo_doc'],$_REQUEST['art_codigo'],$_REQUEST['art_cliente'], $_REQUEST['serie'], $_REQUEST['numero']);
				$resultt   = RegistroVentasMOVTemplate::reporteExcel($res,$_SESSION['almacen'], $_REQUEST['dia1'], $_REQUEST['dia2']) ;
				//$resultt   = RegistroVentasMOVTemplate::gridViewEXCEL($res) ;
				break;
			
			default:	
				$reporte   = "HTML";
				$almacen   = $_SESSION['almacen'];
				$res       = RegistroVentasMOVModel::Paginacion($_REQUEST['rxp'], $_REQUEST['pagina'], $reporte, $almacen, date("d/m/Y"), date("d/m/Y"),"","","","","");
				$result    = RegistroVentasMOVTemplate::search_form($res["paginacion"], $almacen,"","","","","","","");			    	
				$result_f  = RegistroVentasMOVTemplate::reporte($res);
				break;
		}		
	
		$this->visor->addComponent("ContentT", "content_title", RegistroVentasMOVTemplate::titulo());
		if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);
	}
}
