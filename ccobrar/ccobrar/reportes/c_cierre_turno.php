<?php

class DiaCierreTurnoController extends Controller {
	
	function Init(){
		$this->visor = new Visor();
		isset($_REQUEST['action'])?$this->action=$_REQUEST['action']:$this->action='';
	}
	
	function Run(){	

		include 'reportes/m_cierre_turno.php';
		include 'reportes/t_cierre_turno.php'; 
		
		$this->Init();	
		$result = '';
		$result_f = '';
		$buscar = false;
		$listado = false;
		

		switch ($this->action) {

			case "Buscar":
				$listado = true;

			case "Agregar":
				$result	= DiaCierreTurnoTemplate::formAgregar($_REQUEST['fecha'],$_REQUEST['fecha2']);
				$result_f = "&nbsp;";
				break;

		
			case "Guardar":
					if ($_REQUEST['tca_moneda'] == '' or $_REQUEST['compra_libre'] == '' or $_REQUEST['venta_libre'] == '' or $_REQUEST['compra_banco'] == '' or $_REQUEST['venta_banco']  == '' or $_REQUEST['compra_oficial'] == '' or $_REQUEST['venta_oficial']  == ''){
						$result_f = "<script>alert('Llenar campos faltantes..!!');</script>";				
					}else{
						$res = TipodeCambioModel::agregar($_REQUEST['tca_moneda'],$_REQUEST['compra_libre'],$_REQUEST['venta_libre'],$_REQUEST['compra_banco'],$_REQUEST['venta_banco'],$_REQUEST['compra_oficial'],$_REQUEST['venta_oficial']);
						if($res == 1){
							?><script>alert('Registro guardado correctamente');</script><?php
							$result = DiaCierreTurnoTemplate::formSearch($_REQUEST['fecha'],$_REQUEST['fecha2']);
						}else{
							?><script>alert('Ya existe registro..!!');</script><?php
						}
								
					}
			break;
		}

		if ($buscar) {
		    	$result = DiaCierreTurnoTemplate::formSearch("", "");
		}

	
		if ($listado) {
			$result    = DiaCierreTurnoTemplate::formSearch($_REQUEST['fecha'], $_REQUEST['fecha2']);		
		    	$resultados = DiaCierreTurnoModel::buscar($_REQUEST['fecha'], $_REQUEST['fecha2']);	    	
		    	$result_f  = DiaCierreTurnoTemplate::resultadosBusqueda($resultados, $_REQUEST['fecha'], $_REQUEST['fecha2']);
		}

		$this->visor->addComponent("ContentT", "content_title", DiaCierreTurnoTemplate::titulo());
		if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);
	}
}
