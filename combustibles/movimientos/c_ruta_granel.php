<?php

class VentaGranelController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action'])?$this->action=$_REQUEST['action']:$this->action='';
    	}

    	function Run() {
	    	ob_start();
	    	
		include 'movimientos/m_ruta_granel.php';
		include 'movimientos/t_ruta_granel.php'; 

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
				
					$desde 	 	= $_REQUEST['desde'];
					$hasta 	 	= $_REQUEST['hasta'];
					$ruc    	= $_REQUEST['ruc'];
					$pedido 	= $_REQUEST['pedido'];
					$plac    	= $_REQUEST['plac'];
					$Parametros	= VentaGranelModel::obtenerParametros();
					$result    	= VentaGranelTemplate::formBuscar($desde, $hasta, $ruc, $pedido,$plac);		
				    	$datos     	= VentaGranelModel::busqueda($desde, $hasta, $ruc, $pedido,$plac); 	    	
				    	$result_f 	= VentaGranelTemplate::listado($datos,$desde,$hasta,$ruc,$pedido);
					$this->visor->addComponent("ContentB", "content_body", $result);
					$this->visor->addComponent("ContentF", "content_footer", $result_f);	
				   	//$result    = VentaGranelTemplate::formReporte("A", "",$cod_idx);
					//$result_f  = " ";

					break;			
					
			case "Actualizar":

					$codpedido 		= $_REQUEST['codpedido'];
					$fecregistro 		= $_REQUEST['fecregistro'];
					$ruc 			= $_REQUEST['ruc'];
					$razsocial 		= $_REQUEST['razsocial'];
					$anexo 			= $_REQUEST['anexo'];
					$distrito 		= $_REQUEST['distrito'];
					$galones 		= $_REQUEST['galones'];
					$scop 			= $_REQUEST['scop'];
					$placa 			= $_REQUEST['placa'];

					//$resultado    	= VentaGranelModel::busqueda2($ruc,$codpedido,$placa);

					$result    	= VentaGranelTemplate::FormAsignar($codpedido,$fecregistro,$ruc,$razsocial,$anexo,$distrito,$galones,$scop,$placa);	
					$this->visor->addComponent("ContentB", "content_body", $result);
					$result_f  	= " ";
					$this->visor->addComponent("ContentF", "content_footer", $result_f);	

					
					/*$codpedido 	= $_REQUEST['codpedido'];
					$ruc 		= $_REQUEST['ruc'];
					$fhd 		= $_REQUEST['fhd'];					
					$placa 		= $_REQUEST['placa'];
					$idplaca 	= $_REQUEST['idplaca'];
					$galo 		= $_REQUEST['galo'];
								
					$xdat    	= VentaGranelModel::busqueda2($ruc,$codpedido,$placa);
					
					if(trim($placa)!=''){
					if(	
						($xdat[0][1] == '' || $xdat[0][1] == NULL)  ||
						($xdat[0][1] == $placa && $xdat[0][2] ==$codpedido) || 
						($xdat[0][1] == $placa && $xdat[0][2] !=$codpedido && $xdat[0][3]==0)
					){
					
					$res       	= VentaGranelModel::modificar($codpedido,$ruc,$fhd,$placa,$idplaca);
					$datos     	= VentaGranelModel::busqueda2($ruc, $codpedido,''); 

					if($res==1){
						?><script>alert('Se modifico el registro.');</script><?php	
					}
					echo "codpedido:".$codpedido." ruc:".$ruc." fhd:".$fhd." placa:".$placa;

					$datox = Array();
					
					$fecha = date_create($datos[0][0]);
					$dat_fecha = date_format($fecha, 'Y-m-d H:i:s');

					$datox[0][0]=$dat_fecha;
					$datox[0][1]=$datos[0][1];

					echo json_encode($datox);

					}

					if($xdat[0][2] != $codpedido){
						$datox[0][0]=1;
						$datox[0][1]=1;
						echo json_encode($datox);
					}
	
					}else
					{
						$datox[0][0]=2;
						$datox[0][1]=2;
						echo json_encode($datox);
					}
					$result = "";
					$result_f = "";
					*/
					break;

			case "Modificar":

					$desde 	 	= date("01/m/Y");
					$hasta 	 	= date("d/m/Y");
					$ruc 		= $_REQUEST['ruc'];
					$codpedido	= $_REQUEST['codpedido'];
					$hora 		= date("H:i:s");

					$dia		= substr($_REQUEST['fecha'],0,2);
					$mes		= substr($_REQUEST['fecha'],3,2);
					$anio		= substr($_REQUEST['fecha'],6,4);

					$idplaca	= $_REQUEST['placa'];
					$fecha		= $anio.'-'.$mes.'-'.$dia.' '.$hora;

					$autoplaca    	= VentaGranelModel::autoplaca($idplaca);
					$xdat    	= VentaGranelModel::busqueda2($ruc,$codpedido,$idplaca);

					if($idplaca == ''){
						?><script>alert("<?php echo 'Tienes que Seleccionar una placa' ; ?> ");</script><?php
					}

					if($anio == ''){
						?><script>alert("<?php echo 'Tienes que Seleccionar una fecha' ; ?> ");</script><?php
					}

					/*if(trim($idplaca)!=''){
						if(	
							($xdat[0][1] == '' || $xdat[0][1] == NULL)  ||
							($xdat[0][1] == $idplaca && $xdat[0][2] ==$codpedido) || 
							($xdat[0][1] == $idplaca && $xdat[0][2] !=$codpedido && $xdat[0][3]==0)
						){

							$datox    	= VentaGranelModel::busqueda2($ruc,$codpedido,'');*/
							$res    	= VentaGranelModel::modificar($codpedido,$autoplaca[0][0],$fecha);

							if($res == '1'){		
								?><script>alert("<?php echo 'Registro Actualizado' ; ?> ");</script><?php
								$result    	= VentaGranelTemplate::formBuscar(date("01/m/Y"),date("d/m/Y"),"","");
							    	$datos     	= VentaGranelModel::busqueda(date("01/m/Y"), date("d/m/Y"), '','',''); 	    	
							    	$result_f 	= VentaGranelTemplate::listado($datos,date("01/m/Y"),date("d/m/Y"),'','');
								$this->visor->addComponent("ContentB", "content_body", $result);
								$this->visor->addComponent("ContentF", "content_footer", $result_f);	
							}

						/*}else{
							?><script>alert("<?php echo 'Ya existe esta placa '.$idplaca.' ingresar otra' ; ?> ");</script><?php
						}
					}*/

					break;
								
		    	default:
					$result    	= VentaGranelTemplate::formBuscar(date("01/m/Y"),date("d/m/Y"),"","");
				    	$datos     	= VentaGranelModel::busqueda(date("01/m/Y"), date("d/m/Y"), '','',''); 	    	
				    	$result_f 	= VentaGranelTemplate::listado($datos,date("01/m/Y"),date("d/m/Y"),'','');
					$this->visor->addComponent("ContentB", "content_body", $result);
					$this->visor->addComponent("ContentF", "content_footer", $result_f);		
					break;
		}
	}
}
