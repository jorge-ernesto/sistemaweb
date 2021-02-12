<?php

date_default_timezone_set('UTC');

function unlinkRecursive($dir) {

    if (!$dh = @opendir($dir)) {
        return;
    }

    while (false !== ($obj = readdir($dh))) {
        if ($obj == '.' || $obj == '..') {
            continue;
        }

        if (!@unlink($dir . '/' . $obj)) {
            unlinkRecursive($dir . '/' . $obj);
        }
    }

    closedir($dh);

    return;

}

class InterfaceMovController extends Controller {

	function Init() {
		$this->visor 	= new Visor();
		$this->task 	= @$_REQUEST["task"];
		$this->action 	= isset($_REQUEST["action"]) ? $_REQUEST["action"] : '';
		$this->datos 	= isset($_REQUEST["datos"]) ? $_REQUEST["datos"] : '';
    }

    function Run() {
		$this->Init();
		$result = '';

		include('movimientos/m_interface_sigo.php');
		include('movimientos/t_interface_sigo.php');
		include('/sistemaweb/include/m_sisvarios.php');

		$this->visor->addComponent('ContentT', 'content_title', InterfaceMovTemplate::titulo());

        switch ($this->task) {
			case 'INTERFAZSIGO':
                $CbSucursales = VariosModel::sucursalCBArray();

				switch ($this->action) {
					case 'Asiento Contables': {
						$fecha				= $_REQUEST['datos']['fechaini'];
						$fecha_array		= explode("/", $fecha);
						$fecha_pos_trans	= $fecha_array[2] . "" . $fecha_array[1];
						$year 				= substr($fecha_array[2], 2, 2);
						$exe				= true;
						$acc				= trim($_REQUEST['datos']['modulos']);
						$iAlmacen			= trim($_REQUEST['datos']['sucursal']);
						$dataglobal			= array();

						if($acc == 'VT'){
							$tickes_postrans	= InterfaceMovModel::getdata_postran($fecha_pos_trans, $fecha, $iAlmacen);
							//echo "<script>console.log('" . json_encode($tickes_postrans) . "')</script>";
							//return;
				            $dataok_pos			= InterfaceMovModel::procesariInformacion($tickes_postrans, 'postrans', 'VT', null, null);
							//array_push(&$dataglobal,$dataok_pos);
							array_push($dataglobal,$dataok_pos);
						}

						if($acc == 'VTM'){

							$facturas_manules = InterfaceMovModel::getFacturasManuales($fecha);
				
							if($facturas_manules['errorf'] == TRUE){
								echo "Ocurrio un error manualess.";
							}else{
								$dataok_manual = InterfaceMovModel::procesariInformacion($facturas_manules,'manual','VT', null, null);
								//array_push(&$dataglobal,$dataok_manual);
								array_push($dataglobal,$dataok_manual);
							}

						}
								
						if($acc == 'CP'){

							$facturas_compras = InterfaceMovModel::getFacturasCompras($fecha);

							if($facturas_compras['errorf'] == TRUE){
								echo "Ocurrio un error.";
							}else{
								$dataok_compra = InterfaceMovModel::procesariInformacion($facturas_compras, 'manual', 'CP', $year, $fecha_array[1]);
								//array_push(&$dataglobal, $dataok_compra);
								array_push($dataglobal, $dataok_compra);
							}

						}
								
						if($exe){

							$uri_dir	= "/home/data/"; //Clientes
							$cmd		= "rm -f $uri_dir*";
							exec($cmd);	
				
							$fp		= fopen($uri_dir."V1.prn", "a");

							foreach ($dataglobal as $keyglobal => $dataok){
								foreach ($dataok  as $key => $value) {
									$caden_tmp = implode($value);
									 fwrite($fp, $caden_tmp . PHP_EOL);
								}
							}

							 fclose($fp);

						}                        

						if($exe){

			                    if (file_exists("/tmp/data.zip"))
				                	unlink("/tmp/data.zip");

			                    $cmd = "zip -j -m /tmp/data.zip $uri_dir*";
			                    exec($cmd);

			                    list($dia, $mes, $ano) = split('[-/]', $_REQUEST['datos']['fechaini']);
			                    $archivo = substr($_REQUEST['datos']['sucursal'], 1) . $dia . $mes . substr($ano, 2) . ".zip";
			                    header("Content-Type: application/x-zip-compressed");
			                    header('Content-Disposition: attachment; filename="' . $archivo . '"');
			                    readfile("/tmp/data.zip");
											 
						}

						}

					break;

		            case 'Clientes': {

						$fecha_pos	= explode("/", $_REQUEST['datos']['fechaini']);
						$fecha_pos_m	= $fecha_pos[2] . "" . $fecha_pos[1];
						$fecha		= $_REQUEST['datos']['fechaini'];
						$uri_dir	= "/home/data/"; //Clientes
						$data_retorno	= InterfaceMovModel::MostarClientes($fecha_pos_m, $fecha);

						$cmd 		= "rm -f $uri_dir*";
						exec($cmd);

									$fp1 = fopen($uri_dir."ventas_siigo_tercero1.prn", "a");
						$fp2 = fopen($uri_dir."ventas_siigo_tercero2.prn", "a");

						if (count($data_retorno) > 0) {

										$array_datos_para_2_archivo = array();

										foreach ($data_retorno as $key => $value) {

											$caden_tmp_1 = $value['ruc_tmp'] . $value['sucursal'] . 'C' . str_pad(trim($value['cli_razsocial']), 60, ' ', STR_PAD_RIGHT) . str_pad(trim($value['cli_contacto']), 50, ' ', STR_PAD_RIGHT) . str_pad(trim($value['cli_direccion']), 100, ' ', STR_PAD_RIGHT) . $value['tele'];

											fwrite($fp1, $caden_tmp_1 . PHP_EOL);

											$caden_tmp_2 = $value['tele2'] . $value['tele3'] . $value['tele4'] . $value['fax'] . $value['apartadeo_aereo'];
											$caden_tmp_2 .= $value['email'] . $value['sexo'] . $value['cod_clas_tributario'] . $value['tipo_identificacion'] . $value['cupo_credito'];
											$caden_tmp_2 .= $value['lista_precio'] . $value['cod_vendedor'] . $value['cod_ciudad'] . $value['por_decuento'] . $value['periodo_pago'];
											$caden_tmp_2 .= $value['observacion'] . $value['codigo_pais'] . $value['digito_verificacion'] . $value['calificacion'] . $value['actividad_economica'];
											$caden_tmp_2 .= $value['forma_de_pago'] . $value['cobrador'] . $value['tipo_persona'] . $value['declarante'] . $value['agente_rentendor'];
											$caden_tmp_2 .= $value['auto_rentendor'] . $value['beneficiario_retivo'] . $value['agente_retentor'] . $value['estado'] . $value['ente_publico'];
											$caden_tmp_2 .= $value['cod_ente_publico'] . $value['razon_social'] . $value['nombre1'] . $value['nombre2'] . $value['apellido1'];
										$caden_tmp_2 .= $value['apellido2'] . $value['numero_ide_extranjera'] . $value['ruta'] . $value['registro'] . $value['fecha_vencimiento'];
											$caden_tmp_2 .= $value['fecha_cumple'] . $value['ts'] . $value['autorizacion_imprenta'] . $value['autorizacion_contribuyente'] . $value['tc'];

								fwrite($fp2, $caden_tmp_2 . PHP_EOL);

										}

							fclose($fp1);
							fclose($fp2);

						}

						if (file_exists("/tmp/data.zip"))
							unlink("/tmp/data.zip");

									$cmd = "zip -j -m /tmp/data.zip $uri_dir*";
									exec($cmd);

						list($dia, $mes, $ano)	= split('[-/]', $_REQUEST['datos']['fechaini']);
									$archivo		= substr($_REQUEST['datos']['sucursal'], 1) . $dia . $mes . substr($ano, 2) . ".zip";

									header("Content-Type: application/x-zip-compressed");
									header('Content-Disposition: attachment; filename="' . $archivo . '"');
									readfile("/tmp/data.zip");
									unlinkRecursive("$uri_dir");

					}

					case 'Asientos Contables Siigo basado en Ventas diarias':
						$fechaini = $_REQUEST['datos']['fechaini'];
						$fechafin = $_REQUEST['datos']['fechafin'];
						$almacen  = $_REQUEST['datos']['sucursal'];
						$res = InterfaceMovModel::obtieneVentasSiigo($fechaini, $fechafin, $almacen, false);
						$resultt = InterfaceMovTemplate::reporteExcelSiigo($res, $almacen, $fechaini, $fechafin);
						
						//echo "<script>console.log('" . json_encode($_REQUEST) . "')</script>";
					break;

					default:
						$result = InterfaceMovTemplate::formInterfaceMov();
						$this->visor->addComponent("ContentB", "content_body", $result);
					break;
				}
            break;

			case 'SUNATDET':
				//Si hay detalles
			break;

		default:
			$this->visor->addComponent('ContentB', 'content_body', '<h2>TAREA "' . $this->request . '" NO CONOCIDA EN REGISTROS</h2>');
            break;
		}
	}

}

