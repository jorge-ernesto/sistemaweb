<?php

class ClienteController extends Controller {

	function Init() {
		$this->visor = new Visor();
		$this->task = @$_REQUEST["task"];
		$this->action = isset($_REQUEST["action"])?$_REQUEST["action"]:'';
		$this->datos = isset($_REQUEST["datos"])?$_REQUEST["datos"]:'';
	}

	function Run() {
		ob_start();
		$this->Init();
		$result = '';

		include('maestros/m_cliente.php');
		include('maestros/t_cliente.php');
		include('../include/paginador_new.php');

		//Obtener clase cliente template y model
		$objClienteTemplate = new ClienteTemplate();
		$objClienteModel = new ClienteModel();

		$this->visor->addComponent('ContentT', 'content_title', $objClienteTemplate->titulo());

		if (!isset($_REQUEST['rxp'], $_REQUEST['pagina'])) {
			if (!$_REQUEST['rxp'] && !$_REQUEST['pagina']) {
				$_REQUEST['rxp'] = 100;
				$_REQUEST['pagina'] = 1;
			}
		}

		switch ($this->request) {
			case 'CLIENTE':
				$listado = false;
				switch ($this->action) {
					case "Excel":
						
						if (is_null($_REQUEST['busqueda'])) {
							$_REQUEST['busqueda'] = '';
						}
						$arrResponseClientes = ClienteModel::tmListado($_REQUEST["busqueda"],$_REQUEST['rxp'],$_REQUEST['pagina']);
						$view_excel_template = $objClienteTemplate->gridViewEXCEL($arrResponseClientes["datos"]);
						
    					 	break;
					case "Total":
						if (is_null($_REQUEST['busqueda'])) {
							$_REQUEST['busqueda'] = '';
						}
						$arrResponseClientes = ClienteModel::tmListadoTotal($_REQUEST["busqueda"],$_REQUEST['rxp'],$_REQUEST['pagina']);
						$view_excel_template = $objClienteTemplate->gridViewEXCEL($arrResponseClientes["datos"]);	
						break;

					case 'Agregar':
						$result = ClienteTemplate::formCliente(array(),"");
						$this->visor->addComponent("ContentB", "content_body", $result);
					break;

					case 'Eliminar':
						$arrDataGET = array(
							'iCodigoCliente' => strip_tags(stripslashes($_GET["registroid"])),
						);
						$arrResponseVerify = $objClienteModel->verify_credit_vouchers_sales_invoice_plates($arrDataGET);

						if ($arrResponseVerify["sStatus"] != "success") {
							echo "<script>alert('{$arrResponseVerify["sMessage"]}');</script>\n";
						} else {
							$arrResponseDelete = $objClienteModel->delete_partner($arrDataGET);
							if ($arrResponseDelete["sStatus"] != "success") {
								echo "<script>alert('{$arrResponseDelete["sMessage"]}');</script>\n";
							} else {
								echo "<script>alert('{$arrResponseDelete["sMessage"]}');</script>\n";
								$listado = true;
							}
						}
						break;

					case 'Modificar':
						$record = ClienteModel::recuperarRegistroArray($_REQUEST["registroid"]);
						error_log("Modificar");
						error_log( json_encode($record) );
						error_log( json_encode($registrosXml) );
						$result = ClienteTemplate::formCliente($record,$registrosXml);
						$this->visor->addComponent("ContentB", "content_body", $result);
						break;

					case 'Guardar':
						error_log("Guardar");
						error_log( json_encode( $this->datos ) );
						$result = ClienteModel::guardarRegistro($this->datos);
						$listado = true;
						/*
						if ($result == 'OK') {
							$listado = true;
						} else {
							?><script>alert("<?php echo 'Error al guardar Cliente, intentelo en otro momento'; ?> ");</script><?php
						}
						*/
						break;

					case 'Reporte':

						include "t_cliente_report.php";
						include "/sistemaweb/include/m_sisvarios.php";

						$reporte    = ClienteModel::tmListado($_REQUEST["busqueda"],$_REQUEST['rxp'],$_REQUEST['pagina']);
						$result    .= ClientesPDFTemplate::reporte($reporte);

						$mi_pdf = "/sistemaweb/ventas_clientes/reportes/pdf/reporte_clientes.pdf";
						header('Content-type: application/pdf');
						header('Content-Disposition: attachment; filename="'."reporte_clientes.pdf".'"');
						readfile($mi_pdf);

						break;

					case "Regresar":

						$busqueda = ClienteModel::tmListado($_REQUEST['paginacion'], $_REQUEST['rxp'], $_REQUEST['pagina']);
						$result   = ClienteTemplate::formBuscar($busqueda['paginacion'],$_REQUEST['paginacion']);
						$result  .= ClienteTemplate::listado($busqueda['datos']);
						$this->visor->addComponent("ContentB", "content_body", $result);

						break;

					case 'Buscar':

						if (is_null($_REQUEST['busqueda'])) {
							$_REQUEST['busqueda'] = '';
						}
						
						$busqueda = ClienteModel::tmListado($_REQUEST["busqueda"],$_REQUEST['rxp'],$_REQUEST['pagina']);
						$result   =  ClienteTemplate::formBuscar($busqueda['paginacion'],$_REQUEST["busqueda"]);
						$result  .= ClienteTemplate::listado($busqueda['datos']);
						$this->visor->addComponent("ListadoB", "content_body", $result);
						$listado = false;

					break;

					default:
						$listado = true;
						unset($_SESSION['CUENTAS']);
						unset($_SESSION['TOTAL_CUENTAS']);
					break;
				}

				if ($listado) {
					$listado = ClienteModel::tmListado('',$_REQUEST['rxp'],$_REQUEST['pagina']);
					$result  =  ClienteTemplate::formBuscar($listado['paginacion'], array());
					$result .= ClienteTemplate::listado($listado['datos']);
					$this->visor->addComponent("ContentB", "content_body", $result);
				}

				break;

				case 'CLIENTEDET':

				switch($this->action) {

					case 'setRegistro'://Codigo CIIU
						$result = ClienteTemplate::setRegistros($_REQUEST["codigo"]);
						$this->visor->addComponent("desc_ciiu", "desc_ciiu", $result);
						break;

					case 'setRegistroFP'://Forma de Pago
						$result = ClienteTemplate::setRegistrosFormaPago($_REQUEST["codigofp"]);
						$this->visor->addComponent("desc_forma_pago", "desc_forma_pago", $result);
						break;

					case 'setRegistroLPRE'://Lista de Precios
						$result = ClienteTemplate::setRegistrosListaPrecios($_REQUEST["codigolpre"]);
						$this->visor->addComponent("desc_lista_precios", "desc_lista_precios", $result);
						break;

					case 'setRegistroDesc'://Lista de Precios
						$result = ClienteTemplate::setRegistrosDesc($_REQUEST["codigo"]);
						$this->visor->addComponent("desc_descuento", "desc_descuento", $result);
						break;

					case 'setRegistroDist'://Distritos
						$result = ClienteTemplate::setRegistrosDistrito($_REQUEST["codigodist"]);
						$this->visor->addComponent("desc_distrito", "desc_distrito", $result);
						break;

					case 'setRegistroRub'://Rubros
						$result = ClienteTemplate::setRegistrosRubro($_REQUEST["codigorub"]);
						$this->visor->addComponent("desc_rubro", "desc_rubro", $result);
						break;

					case 'setRegistroCodCta'://Cuentas de Bancos
						$result = ClienteTemplate::setRegistrosCuentas($_REQUEST["codigocta"]);
						$this->visor->addComponent("desc_cta[]", "desc_cta[]", $result);
						break;
						  
					case 'setRegistroTipoCtaBan'://Cuentas de Bancos
						$result = ClienteTemplate::setRegistrosTipoCtaBan($_REQUEST["codigotipoctaban"]);
						$this->visor->addComponent("desc_tipoctaban[]", "desc_tipoctaban[]", $result);
						break;
						  
					case 'ValidarCodigo':
						$result = ClienteModel::validarCodigo($_REQUEST["Codigo"]);
						$this->visor->addComponent("MensajeValidacion", "MensajeValidacion", $result);
						break;

					case 'ValidarCodigoShell':
						$result = ClienteModel::validarCodigoShell($_REQUEST["CodigoShell"]);
						$this->visor->addComponent("MensajeValidacionShell", "MensajeValidacionShell", $result);
						break;
						  
					case 'ValidarRuc':
						$result = ClienteModel::validarRuc($_REQUEST["CodigoRuc"]);
						$this->visor->addComponent("MensajeValidacionRuc", "MensajeValidacionRuc", $result);
						break;

					default:
					break;
				}
				break;// /. CLIENTEDET

				case 'SUNAT'://Consulta de RUC SUNAT - services opensysperu
					switch($this->action) {
						case 'get_data_sunat':
							$cifkey = ClienteModel::CIFKey();
							if($cifkey === NULL) {
								//echo "<script>alert('La estacion no esta integrada con servidor CIF OCS');</script>\n";
								echo json_encode(array("operation"=>6,"message"=>"La estacion no esta integrada con servidor CIF OCS"));
								exit();
							}

							$this->ajax_validation_sunat($cifkey, $_POST);
							exit();
						break;
					}
				break;

			default:
				$this->visor->addComponent('ContentB', 'content_body', '<h2>TAREA "'.$this->request.'" NO CONOCIDA EN REGISTROS</h2>');
			break;

		}
	}// /. RUN

	function ajax_validation_sunat($key, $arrDataPOST){
		$tmp=$this->valruc_sunat(array("key" => $key, "taxid"=>$arrDataPOST["iTaxID"]));
		
		if($tmp["operation"]==1)
			echo json_encode($tmp);
			
		else if($tmp["operation"]==2)
			echo json_encode(array("operation"=>$tmp["operation"],"message"=>"Ha Ocurrido Un Error Con Servidor"));
		
		else if($tmp["operation"]==3)
			echo json_encode(array("operation"=>$tmp["operation"],"message"=>"RUC Invalido Por Sunat"));

		else if($tmp["operation"]==4)
			echo json_encode(array("operation"=>$tmp["operation"],"message"=>"Ruc En Cola Espere 15 Minutos"));

		else if($tmp["operation"]==5)
			echo json_encode(array("operation"=>$tmp["operation"],"message"=>"RUC Invalido"));
	}

    function valruc_sunat($data){
        $ch=curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://services.opensysperu.com/tid/pe/ruc/'.$data["key"].'/'.$data["taxid"]);
		  error_log('http://services.opensysperu.com/tid/pe/ruc/'.$data["key"].'/'.$data["taxid"]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $content=curl_exec($ch);
        $status=curl_getinfo($ch, CURLINFO_HTTP_CODE); 
        curl_close($ch);
        if($status=="200"){
			  	error_log(json_encode( $this->regular_expresion(array("content"=>$content,"taxid"=>$data["taxid"])) ));
            return $this->regular_expresion(array("content"=>$content,"taxid"=>$data["taxid"]));
        }
        return array("operation"=>2);
    }

    function regular_expresion($data){
        $tmp            =array();
        $pattern_queued ="/QUEUED/";
        $pattern_invalid="/INVALID/";
        
        if(preg_match($pattern_queued, $data["content"], $out_tmp))
            return array("operation"=>4);
        else if(preg_match($pattern_invalid, $data["content"], $out_tmp))
            return array("operation"=>5);
        
        foreach (
            array(
                "name"=>"/NAME\:([^\n]*)/",
                "streetName"=>"/FIELD\:streetName\:([^\n]*)/",
                "zone"=>"/FIELD\:zone\:([^\n]*)/",
                "location"=>"/FIELD\:location\:([^\n]*)/"
            ) as $key => $value) {
            if(preg_match($value, $data["content"], $out_tmp))
                $tmp[$key] = strip_tags($out_tmp[1]);
        }

        $tmp["operation"]=1;
        return $tmp;
    }
}
