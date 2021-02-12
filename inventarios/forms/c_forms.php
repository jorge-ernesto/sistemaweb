<?php

class FormsController extends Controller {

	function Init(){
      		$this->visor = new Visor();
    	}
    
    	function Run(){
	      	global $usuario;
	      	
		include "forms/m_forms.php";
		include "forms/t_forms.php";
		include('../include/m_sisvarios.php');
		
		$this->Init();
		$result = "";

		$bShowListado = false;

		switch($this->request) {
		
		  	case "ACTION":
			  
					// echo "<script>console.log('" . json_encode($_REQUEST) . "')</script>";
				
			    	$Datos['Cod_Estacion'] = $_REQUEST['almacen'];
			    	if(trim($_REQUEST['new_tipotransa']) == '18')
			    		$Seq_id = VariosModel::ObtSigCodigo('s_inv_ta_regularizacion');
			    		
			    	// validar fecha de cambio
			    	if(preg_match("/Modificar Fecha/", $_REQUEST['change_date'])) {
						// echo "<script>console.log('Entro')</script>";
			    		$d = explode("/", $_REQUEST['dato_change']);
			    		$dia = $d[2]."-".$d[1]."-".$d[0];
						$iAlmacen = $_GET['almacen'];
						$sFecha = $_GET['fecha'];
			    		$valida = FormsModel::Consolidacion($iAlmacen, $sFecha);
					if($valida == 1){					
						echo "<script>alert('Dia Consolidado');</script>";
					}else{


				    		$rpta = FormsModel::validaDia($dia);
						if($rpta=="0"){
							echo "<script>alert('Fecha menor a cierre de inventario.');</script>";
							$bShowListado = true;
							break;
						}elseif($rpta=="1"){
							echo "<script>alert('Fecha mayor a la actual.');</script>";
							$bShowListado = true;
							break;
						} else {
						
						}

					}
				}
				
			    	if (isset($_REQUEST['completo'])) {			    	
			      		foreach ($_REQUEST['transacciones'] as $i => $codigo) {	
			      		      
						$DatosTrans['old_trancodigo']     = substr($codigo, 0, 3);
						$DatosTrans['numero']             = substr($codigo, 3, 10);
						$art_codigo_arr                   = explode(",", $codigo);
						$DatosTrans['cantidad']           = $art_codigo_arr[2];
						$DatosTrans['naturaleza']         = $art_codigo_arr[3];
						$DatosTrans['new_trancodigo']     = $_REQUEST['new_tipotransa'];
						$DatosTrans['new_origen']         = $_REQUEST['new_tipo_alm_orig'];
						$DatosTrans['new_destino']        = $_REQUEST['new_tipo_alm_dest'];
						$DatosTrans['new_nrefer']         = $_REQUEST['dato_change'];
						$DatosTrans['new_fecha']          = $_REQUEST['dato_change'];
						$DatosTrans['new_tipo_doc']       = $_REQUEST['dato_change'];
						$DatosTrans['nu_regularizacion']  = $Seq_id;
		
						if(preg_match("/(01)|(05)|(21)|(22)/", trim($DatosTrans['old_tran']))){
							$DFac = FormsModel::VerificarExisFactura($DatosTrans['old_tran'].$DatosTrans['numero']);
			  				if($DFac)  {
			      					$mensaje = "El movimiento n&uacute;mero : ".$DatosTrans['numero']." ya esta Facturado.";
			    					break;
			  				}
						}

						$iAlmacen = $_GET['almacen'];
						$sFecha = $_GET['fecha'];
						$valida = FormsModel::Consolidacion($iAlmacen, $sFecha);
						if($valida == 1){					
							echo "<script>alert('Dia Consolidado');</script>";
						}else{

							if(preg_match("/Modificar Origen/", $_REQUEST['change_orig'])){
								if (FormsModel::CambiarOrigenFormulario($DatosTrans, $Datos) < 0) 
									break;
							}elseif(preg_match("/Modificar Destino/", $_REQUEST['change_dest'])){
								if (FormsModel::CambiarDestinoFormulario($DatosTrans, $Datos) < 0) 
									break;
							}elseif(preg_match("/Modificar Fecha/", $_REQUEST['change_date'])){							
								if (FormsModel::CambiarFechaFormulario($DatosTrans, $Datos) < 0) 
									break;
							}elseif(preg_match("/Modificar Nro. Registro/", $_REQUEST['change_nro_regi'])){
								if (FormsModel::CambiarNroReferFormulario($DatosTrans, $Datos) < 0) 
									break;
							}elseif(preg_match("/Modificar Tipo Doc./", $_REQUEST['change_tipo_doc'])){
								if (FormsModel::CambiarTipoDocFormulario($DatosTrans, $Datos) < 0) 
									break;
							}elseif(preg_match("/Cambiar Formulario/", $_REQUEST['change'])){
								if (FormsModel::CambiarNumeroFormulario($DatosTrans, $Datos) < 0) 
									break;
							}

						}
	     				}
    				} else {
	      				foreach ($_REQUEST['transacciones'] as $i => $codigo) {

						$DatosTrans['old_trancodigo'] = substr($codigo, 0, 3);
						$DatosTrans['numero']         = substr($codigo, 3, 10);
						$art_codigo_arr               = explode(",", $codigo);
						$DatosTrans['art_codigo']     = $art_codigo_arr[1];
						$DatosTrans['cantidad']       = $art_codigo_arr[2];
						$DatosTrans['naturaleza']     = $art_codigo_arr[3];
						$DatosTrans['registroid']     = $art_codigo_arr[4];
						$DatosTrans['old_fecha'] 	  = $_REQUEST['fecha'];
						$DatosTrans['new_trancodigo'] = $_REQUEST['new_tipotransa'];
						$DatosTrans['new_origen']     = $_REQUEST['new_tipo_alm_orig'];
						$DatosTrans['new_destino']    = $_REQUEST['new_tipo_alm_dest'];
						$DatosTrans['new_nrefer']     = $_REQUEST['dato_change'];
						$DatosTrans['new_fecha']      = $_REQUEST['dato_change'];
						$DatosTrans['new_tipo_doc']       = $_REQUEST['dato_change'];
						$DatosTrans['nu_regularizacion']  = $Seq_id;	
		
						if(preg_match("/(01)|(05)|(21)|(22)/", trim($DatosTrans['old_tran']))){
							$DFac = FormsModel::VerificarExisFactura($DatosTrans['old_tran'].$DatosTrans['numero'], $DatosTrans['art_codigo']);
							if($DFac) {
		      						$mensaje = "El art&iacute;culo  de c&oacute;digo : ".$DatosTrans['art_codigo']." ya esta Facturado.";
		    						break;
		  					}
						}

						$iAlmacen = $_GET['almacen'];
						$sFecha = $_GET['fecha'];
						$valida = FormsModel::Consolidacion($iAlmacen, $sFecha);
						if($valida == 1){					
							echo "<script>alert('Dia Consolidado');</script>";
						}else{
							if(preg_match("/Modificar Origen/", $_REQUEST['change_orig'])){
								if (FormsModel::cambiarOrigenFormularioArticulo($DatosTrans, $Datos) < 0) 
									break;
							}elseif(preg_match("/Modificar Destino/", $_REQUEST['change_dest'])){
								if (FormsModel::cambiarDestinoFormularioArticulo($DatosTrans, $Datos) < 0) break;
							}elseif(preg_match("/Modificar Fecha/", $_REQUEST['change_date'])){
								if (FormsModel::cambiarFechaFormularioArticulo($DatosTrans, $Datos) < 0) break;
							}elseif(preg_match("/Modificar Nro. Referencia/", $_REQUEST['change_nro_regi'])){
								if (FormsModel::cambiarNroReferFormularioArticulo($DatosTrans, $Datos) < 0) break;
							}elseif(preg_match("/Modificar Tipo Doc./", $_REQUEST['change_tipo_doc'])){
								if (FormsModel::cambiarTipoDocFormularioArticulo($DatosTrans, $Datos) < 0) break;
							}elseif(preg_match("/Cambiar Formulario/", $_REQUEST['change'])){
								if (FormsModel::CambiarNumeroFormularioArticulo($DatosTrans, $Datos) < 0) break;
							}

						}
	      				}
	   			}
	   			
	  		case "MODIFICAR":
	  			default:
	    			$bShowListado = true;
	    			break;
		}
	
		if ($bShowListado) {
	   	    	$tipos = FormsModel::ObtenerTipoTransacciones();
		    	$almacenes = FormsModel::ObtieneAlmacenes();

		    	$fecha = date('d/m/Y');
		    	if(isset($_REQUEST['fecha']))
		    		$fecha = $_REQUEST['fecha'];

		    	$tipotransa = '';
		    	if(isset($_REQUEST['tipotransa']))
		    		$tipotransa = $_REQUEST['tipotransa'];

		    	$almacen = '';
		    	if(isset($_REQUEST['almacen']))
		    		$almacen = $_REQUEST['almacen'];

	    	    $result = FormsTemplate::FormModificar($tipos, $almacenes, $fecha, $tipotransa, $almacen);
		    	$listado = FormsModel::ObtenerListado($fecha, $tipotransa, $almacen, 0, 0);
		    	if(count($listado) > 0) {
			    	$lista = FormsTemplate::FormListado($listado, $tipos);
			    	$this->visor->addComponent("ContentF", "content_footer", $lista);
			    } else {
			    	$this->visor->addComponent("ContentF", "content_footer", '<div align="center" style="color: #eb5050;"><br><b>No hay registros</b></div>');
			    }

		    	$mensaje = '';
		    	if(isset($mensaje))
		    		$mensaje = $mensaje;

		    	$resulta = FormsTemplate::errorResultado($mensaje);
		    	$this->visor->addComponent("error", "error_body", $resulta);
		    	$this->visor->addComponent("error_pie", "error_body_pie", $resulta);
		}

			$this->visor->addComponent("ContentT", "content_title", FormsTemplate::titulo());
			$this->visor->addComponent("ContentB", "content_body", $result);
    	}
}
