<?php

class TipoCambioController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action']) ? $this->action = $_REQUEST['action']:$this->action='';
    	}
    
    	function Run() {
		include 'reportes/m_tipo_cambio.php';
		include 'reportes/t_tipo_cambio.php';	
	
		$this->Init();
	
		$result = '';
		$result_f = '';
		$search_form = false;

		switch ($this->action) {

			case "Migrar":				
				$fec1 = trim($_REQUEST['desde']);
				$f1 = explode("/", $fec1);
				$desde = $f1[2]."-".$f1[1]."-".$f1[0];

				$fec2 = trim($_REQUEST['hasta']);
				$f2 = explode("/", $fec2);
				$hasta = $f2[2]."-".$f2[1]."-".$f2[0];

				$tipocambio = trim($_REQUEST['tipocambio']);
				$result = TipoCambioModel::migrar($desde, $hasta, $tipocambio);		
				if($result == 1) $result = '<script name="accion">alert("Tipo de cambio migrado correctamente.") </script>';
				echo $result;					
				break;

		    	default:
				$result = TipoCambioTemplate::search_form(date(d."/".m."/".Y), date(d."/".m."/".Y));				
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", $result_f);
				break;
		}		
	}
}
