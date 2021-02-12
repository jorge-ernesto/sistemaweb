<?php

class ImportarPreciosController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action']) ? $this->action = $_REQUEST['action']:$this->action='';
    	}
    
    	function Run() {
    	
    		set_time_limit(0);// checar
		error_reporting(E_ALL ^ E_NOTICE); // checar
    	
		include 'reportes/m_importar_precios.php';
		include 'reportes/t_importar_precios.php';
		require_once 'excel_reader2.php';
	
		$this->Init();	
		$result = '';
		$result_f = '';

		switch ($this->action) {

			case "Mostrar":				
				echo 'Entra a Mostrar'."\n";
				$archi = $_FILES['ubicacion']['tmp_name'];
				$rpta  = ImportarPreciosModel::buscar($archi);
				if($rpta!=0) {
					$filtrados  = ImportarPreciosModel::filtrar($rpta);
					$result_f = ImportarPreciosTemplate::reporte($filtrados);
				}
				break;

			case "Actualizar": 
				echo 'Entro a Actualizar'."\n";
				$acod_pro = @$_REQUEST['acod_pro'];
				$anom_pro = @$_REQUEST['anom_pro'];
				$acod_art = @$_REQUEST['acod_art']; 
				$anom_art = @$_REQUEST['anom_art']; 
				$amoneda = @$_REQUEST['amoneda']; 
				$aprecio = @$_REQUEST['aprecio']; 				
				$icod_pro = @$_REQUEST['icod_pro'];// i
				$inom_pro = @$_REQUEST['inom_pro'];
				$icod_art = @$_REQUEST['icod_art']; 
				$inom_art = @$_REQUEST['inom_art']; 
				$imoneda = @$_REQUEST['imoneda']; 
				$iprecio = @$_REQUEST['iprecio'];
				$res = ImportarPreciosModel::actualizar($acod_pro, $anom_pro, $acod_art, $anom_art, $amoneda, $aprecio, $icod_pro, $inom_pro, $icod_art, $inom_art, $imoneda, $iprecio);
				if($res!=0) 
					echo '<script>alert("Se actualizaron los precios.");</script>';
				$result_f = ImportarPreciosTemplate::reporte($res);
				break;
				
			case "Insertar": 
				echo 'Entro a Insertar'."\n";
				$icod_pro = @$_REQUEST['icod_pro'];
				$inom_pro = @$_REQUEST['inom_pro'];
				$icod_art = @$_REQUEST['icod_art']; 
				$inom_art = @$_REQUEST['inom_art']; 
				$imoneda = @$_REQUEST['imoneda']; 
				$iprecio = @$_REQUEST['iprecio']; 
				$acod_pro = @$_REQUEST['acod_pro'];//a
				$anom_pro = @$_REQUEST['anom_pro'];
				$acod_art = @$_REQUEST['acod_art']; 
				$anom_art = @$_REQUEST['anom_art']; 
				$amoneda = @$_REQUEST['amoneda']; 
				$aprecio = @$_REQUEST['aprecio'];
				$res = ImportarPreciosModel::insertar($icod_pro, $inom_pro, $icod_art, $inom_art, $imoneda, $iprecio, $acod_pro, $anom_pro, $acod_art, $anom_art, $amoneda, $aprecio);					
				if($res!=0) 
					echo '<script>alert("Se ingresaron los precios.");</script>';
				$result_f = ImportarPreciosTemplate::reporte($res);
				break;

		    	default:
			    	$this->visor->addComponent("ContentT", "content_title", ImportarPreciosTemplate::titulo());
				$result     	= ImportarPreciosTemplate::search_form();
				break;
		}
		$this->visor->addComponent("ContentT", "content_title", ImportarPreciosTemplate::titulo());		
		if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);		
		if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);
				
	}
}
