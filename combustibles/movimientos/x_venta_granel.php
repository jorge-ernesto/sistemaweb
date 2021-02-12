<?php

class VentaGranelController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action'])?$this->action=$_REQUEST['action']:$this->action='';
    	}

    	function Run() {
	    	ob_start();
		include 'movimientos/xm_venta_granel.php';

		include 'movimientos/xt_venta_granel.php'; 
		
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
					//$datosx=Array();					
					$desde  = $_REQUEST['desde'];
					$hasta  = $_REQUEST['hasta'];
					$ruc    = $_REQUEST['ruc'];
					$pedido = $_REQUEST['pedido'];
					//$Parametros = VentaGranelModel::obtenerParametros(); //xm
					$result    = VentaGranelTemplate::formBuscar($desde, $hasta, $ruc, $pedido); //xt
				    	$datos     = VentaGranelModel::busqueda($desde, $hasta, $ruc, $pedido); //xm  
				    	//$datosx 	=$datos;
				    	$result_f  = VentaGranelTemplate::listado($datos,$desde,$hasta,$ruc,$pedido); //xt
					break;
			
			case "Agregar": 
					
/*					$loading=VentaGranelTemplate::carga();
					$this->visor->addComponent("ContentB", "content_body", $loading); 
*/					
					$desde  = $_REQUEST['desde'];
					$hasta  = $_REQUEST['hasta'];
					$ruc    = $_REQUEST['ruc'];
					$pedido = $_REQUEST['pedido'];
					$result  = "";
					
					$datos     = VentaGranelModel::busqueda($desde, $hasta, $ruc, $pedido); //xm  
					$res    = VentaGranelModel::adicionar($datos);
					
					if($res !=0 || $res !=""){
						?><script>alert('Se Pasaron Satisfactoriamente!!!');</script>
						<?php	
					}
					//$result_t="";
					
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
					$res       = VentaGranelModel::adicionar($codpedido,$fecregistro,$ruc,$razsocial,$dirfiscal,$codanexo,$diranexo,$galones,$precio,$scop,$diascredito,$distrito);
					if($res==1){
						?><script>alert('Se ingreso correctamente.');</script><?php	
					}	
					$result    = VentaGranelTemplate::formBuscar(date("d/m/Y"),date("d/m/Y"),"","");					
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
					$result    = VentaGranelTemplate::formBuscar(date("d/m/Y"),date("d/m/Y"),"","");		
					break;
		}


	$this->visor->addComponent("ContentT", "content_title", VentaGranelTemplate::titulo());
		
		if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);
	}
}
