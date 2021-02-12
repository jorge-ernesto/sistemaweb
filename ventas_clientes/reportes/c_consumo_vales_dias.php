<?php
class ConsumoValesDiasController extends Controller{

    function Init(){
		$this->visor = new Visor();
		isset($_REQUEST['action']) ? $this->action = $_REQUEST['action']:$this->action='';
    }
    
    function Run(){
		ob_start();
		include 'reportes/m_consumo_vales_dias.php';
		include 'reportes/t_consumo_vales_dias.php';
		
		$this->Init();
		
		// Instanciamos la class Template and Model
		$objValeDayTemplate = new ConsumoValesDiasTemplate();
		$objValeDayModel 	= new ConsumoValesDiasModel();

		$result 		= '';
		$result_f 		= '';
		$search_form 	= false;

		switch ($this->action) {
		    case "Reporte":
				$bResumido = false; 

	            if (substr($_REQUEST['desde'],2,7) != substr($_REQUEST['hasta'],2,7)){
					$result_f = '<center><blink>El intervalo de fechas debe ser dentro del mismo mes y a&ntilde;o</blink></center>';
				} else if ($_REQUEST['cliente']==''){
					$result_f = '<center><blink>Ingrese un codigo de cliente valido</blink></center>';
				} else {
					$results = $objValeDayModel->obtieneVentas($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion'], $_REQUEST['producto'], $_REQUEST['cliente'], $bResumido);
					$result_f = $result_f1.'<br/>'.$objValeDayTemplate->reporte($results, $_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion'], $_REQUEST['producto'], $_REQUEST['cliente']);
				}
			break;

			case "Excel":
				$almacenes 	= $objValeDayModel->obtieneListaEstaciones();
				$results 	= $objValeDayModel->obtieneVentas($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion'], $_REQUEST['producto'], $_REQUEST['cliente'], $bResumido);
				$resultt   	= $objValeDayTemplate->reportExcel($results, $almacenes, $_REQUEST['desde'], $_REQUEST['hasta']);
			break;

		    default:
				$search_form = true;
			break;
		}

		if ($search_form)
		    $result = $objValeDayTemplate->search_form();
		
		if ($result != '') $this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != '') $this->visor->addComponent("ContentF", "content_footer", $result_f);
	}
}

