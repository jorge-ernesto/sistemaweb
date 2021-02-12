<?php
class LiquidacionController extends Controller{

    function Init(){
		$this->visor = new Visor();
		isset($_REQUEST['action'])?$this->action=$_REQUEST['action']:$this->action='';
    }
    
    function Run(){
		include 'facturacion/m_liquidacion.php';
		include 'facturacion/t_liquidacion.php';
		
		$result = '';
		$result_f = '';
		$form_search = false;
		$listado = false;

		$this->Init();
		
		switch ($this->action) {
			case 'Buscar':
				$listado = true;
			break;
			
			default:
				$form_search = true;
			break;
			
			case 'Reporte':
				$ch_liquidacion = LiquidacionModel::ModelReportePDF($_REQUEST['serie'], $_REQUEST['numero']);
				echo '<script language="javascript">window.open("generar_liquidacion_vales_new.php?desde='.$ch_liquidacion.'&hasta='.$ch_liquidacion.'&ch_documneto='.$_REQUEST['serie']."-".$_REQUEST['numero'].'", "xxx");</script>';
				
				//header("generar_liquidacion_vales_new.php?desde=".$_REQUEST['desde']."&hasta=".$_REQUEST['hasta']);
				//$result .= LiquidacionTemplate::TmpReportePDF($record);
				//$this->visor->addComponent("ContentB", "content_body", $record);
			break;
		}

		if ($form_search) {
			$result = LiquidacionTemplate::formSearch();
		}

		if ($listado) {
			//$list = LiquidacionModel::search($_REQUEST['desde'], $_REQUEST['hasta']);
			$list = LiquidacionModel::search($_REQUEST['serie'], $_REQUEST['numero']);
			$result_f = LiquidacionTemplate::listado($list, $_REQUEST['desde'], $_REQUEST['hasta']);
		}

		$this->visor->addComponent("ContentT", "content_title", LiquidacionTemplate::titulo());
		if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);
    }
}

?>
