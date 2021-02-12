<?php

class VentaGranelController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action'])?$this->action=$_REQUEST['action']:$this->action='';
    	}

    	function Run() {
	    	ob_start();

		include 'movimientos/m_venta_granel.php';
		include 'movimientos/t_venta_granel.php'; 
		
		$this->Init();

		$ip 		= "";
		$result 	= "";
		$result_f 	= "";

		if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"),"unknown"))
			$ip = getenv("HTTP_CLIENT_IP");
		else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
			$ip = getenv("HTTP_X_FORWARDED_FOR");
		else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
			$ip = getenv("REMOTE_ADDR");
		else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
			$ip = $_SERVER['REMOTE_ADDR'];

        	switch($this->action) {
        	
			case "Buscar":						
					$desde  = $_REQUEST['desde'];
					$hasta  = $_REQUEST['hasta'];
					$ruc    = $_REQUEST['ruc'];
					$pedido = $_REQUEST['pedido'];
					$Parametros = VentaGranelModel::obtenerParametros();
					$result    = VentaGranelTemplate::formBuscar($desde, $hasta, $ruc, $pedido);		
				    	$datos     = VentaGranelModel::busqueda($desde, $hasta, $ruc, $pedido); 	    	
				    	$result_f  = VentaGranelTemplate::listado($datos,$desde,$hasta,$ruc,$pedido);
					break;
			
			case "Agregar": 
					include 'movimientos/auto_gene.php'; 					
					$result    = VentaGranelTemplate::formAgregar("A", "",$cod_idx);
					$result_f  = " ";				
					break;
			
			case "Editar": 
					$codpedido 	= $_REQUEST['codpedido'];					
					$ruc 		= $_REQUEST['ruc'];					
					$codanexo	= $_REQUEST['codanexo'];
					$rpta     	= VentaGranelModel::buscaEditar($codpedido, $ruc, $codanexo);
					$result    = VentaGranelTemplate::formAgregar("E", $rpta);
					$result_f  = " ";		
					break;
								
			case "Adicionar": 
					$codpedido 	= $_REQUEST['codpedido'];
					$fecregistro 	= $_REQUEST['fecregistro'];
					$ruc 		= $_REQUEST['ruc'];
					$razsocial 	= $_REQUEST['razsocial'];
					$dirfiscal 	= $_REQUEST['dirfiscal'];
					$codanexo 	= $_REQUEST['codanexo'];
					$diranexo 	= $_REQUEST['diranexo'];
					$galones 	= $_REQUEST['galones'];
					$precio 	= $_REQUEST['precio'];
					$scop 		= $_REQUEST['scop'];
					$diascredito 	= $_REQUEST['diascredito'];
					$distrito 	= $_REQUEST['distrito'];
					$cod_produc 	= $_REQUEST['producto'];

					if($ruc == "" or $codanexo == ""){
						
					}else{
						$res       = VentaGranelModel::adicionar($codpedido,$fecregistro,$ruc,$razsocial,$dirfiscal,$codanexo,$diranexo,$galones,$precio,$scop,$diascredito,$distrito,$cod_produc);
						if($res==1){

							?><script>alert('Se ingreso correctamente.');</script><?php

							include 'movimientos/auto_gene.php';
							$result    = VentaGranelTemplate::formAgregar("A", "",$cod_idx);
							$result_f  = " ";
						}	
					}

					break;
					
			case "Modificar": 
					$codpedido 	= $_REQUEST['codpedido'];
					$ruc 		= $_REQUEST['ruc'];
					$codanexo 	= $_REQUEST['codanexo'];					
					$galones 	= $_REQUEST['galones'];
					$precio 	= $_REQUEST['precio'];
					$scop 		= $_REQUEST['scop'];
					$diascredito 	= $_REQUEST['diascredito'];
					$distrito 	= $_REQUEST['distrito'];					
					$res       	= VentaGranelModel::modificar($codpedido,$ruc,$codanexo,$galones,$precio,$scop,$diascredito,$distrito);
					if($res==1){
						?><script>alert('Se modifico el registro.');</script><?php	
					}
					break;
			
			case "Eliminar": 
					$codpedido 	= $_REQUEST['codpedido'];					
					$ruc 		= $_REQUEST['ruc'];					
					$codanexo	= $_REQUEST['codanexo'];
					$desde  	= $_REQUEST['desde'];
					$hasta  	= $_REQUEST['hasta'];
					$lisruc    	= $_REQUEST['lisruc'];
					$lispedido 	= $_REQUEST['lispedido'];	
					$rpta     	= VentaGranelModel::eliminar($codpedido, $ruc, $codanexo);
					$result    = VentaGranelTemplate::formBuscar($desde, $hasta, $lisruc, $lispedido);		
				    	$datos     = VentaGranelModel::busqueda($desde, $hasta, $lisruc, $lispedido); 	    	
				    	$result_f  = VentaGranelTemplate::listado($datos,$desde,$hasta,$lisruc,$lispedido);		
					break;			
					
		    	default:
					$result    = VentaGranelTemplate::formBuscar(date("01/m/Y"),date("d/m/Y"),"","");
				    	$datos     = VentaGranelModel::busqueda(date("01/m/Y"), date("d/m/Y"), $ruc, $pedido); 
	    			    	$result_f  = VentaGranelTemplate::listado($datos,$desde,$hasta,$ruc,$pedido);
					$this->visor->addComponent("ContentB", "content_body", $result);
					$this->visor->addComponent("ContentF", "content_footer", $result_f);		
					break;
		}

		$this->visor->addComponent("ContentT", "content_title", VentaGranelTemplate::titulo());
		if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);
	}
}
