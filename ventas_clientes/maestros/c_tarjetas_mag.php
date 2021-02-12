<?php
date_default_timezone_set('UTC');
ini_set("upload_max_filesize", "15M");
set_time_limit(0);

class TarjetasMagneticasController extends Controller{

	function Init(){
		$this->visor = new Visor();
		$this->task = @$_REQUEST["task"];
		$this->action = isset($_REQUEST["action"])?$_REQUEST["action"]:'';
		$this->datos = isset($_REQUEST["datos"])?$_REQUEST["datos"]:'';
	}

	function Run()  {

		$this->Init();

		$result = '';

		include('maestros/m_tarjetas_mag.php');
		include('maestros/t_tarjetas_mag.php');
		include('../include/paginador_new.php');
		include '../include/Classes/excel_reader2.php';

		//Obtener clase cliente template y model
		$objTarjetasMagneticasTemplate = new TarjetasMagneticasTemplate();
		$objTarjetasMagneticasModel = new TarjetasMagneticasModel();

		$this->visor->addComponent('ContentT', 'content_title', TarjetasMagneticasTemplate::titulo());

		$ip = '';

		if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"),"unknown"))
			$ip = getenv("HTTP_CLIENT_IP");
		else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
			$ip = getenv("HTTP_X_FORWARDED_FOR");
		else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
			$ip = getenv("REMOTE_ADDR");
		else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
			$ip = $_SERVER['REMOTE_ADDR'];

		$usuario	= $_SESSION['auth_usuario'];

		/*if(!$_REQUEST['rxp'] && !$_REQUEST['pagina']) {
			$_REQUEST['rxp'] = 100;
			$_REQUEST['pagina'] = 0;
		}*/

		if (!isset($_REQUEST['rxp'], $_REQUEST['pagina'])) {
			if (!$_REQUEST['rxp'] && !$_REQUEST['pagina']) {
				$_REQUEST['rxp'] = 100;
				$_REQUEST['pagina'] = 1;
			}
		}

		error_log(json_encode(array($this->request)));
		error_log(json_encode(array($this->action)));
	
		switch ($this->request)  {
			
			case 'TARJMAG':
				$tablaNombre = 'TARJETAS MAGNETICAS';
				$listado = false;

				switch ($this->action){

					case "Importar":
						$result = TarjetasMagneticasTemplate::ImportarDataExcel();
				    	$this->visor->addComponent("ContentB", "content_body", $result);
					break;

					case "Importar Lista Excel":

						$filename 	= $_FILES['ubica']['name'];
						$resultado 	= TarjetasMagneticasModel::extension($filename);
						$tamano 	= $_FILES['ubica']['size']/1024/1024;
						$tamano 	= substr($tamano,0,5);

						if ($_FILES['ubica']["error"] > 1){
							echo "<script>alert('Error al ubicar el archivo')</script>";
						} elseif ($tamano >= 15 ) {
							echo "<script>alert('Error el archivo debe ser menor a 15MB')</script>";
						} elseif ($resultado != 'xls') {
							?><script>alert("<?php echo 'Error la extension debe de ser .xls' ; ?> ");</script><?php
						}else{
							move_uploaded_file($_FILES['ubica']['tmp_name'],"/sistemaweb/ventas_clientes/libro_excel_placas/" . $_FILES['ubica']['name']);
							$archivo	= "/sistemaweb/ventas_clientes/libro_excel_placas/".$_FILES['ubica']['name'];
							$datos		= new Spreadsheet_Excel_Reader($archivo);
							$result     = TarjetasMagneticasTemplate::ImportarDataExcel();
							$result		.= TarjetasMagneticasTemplate::MostrarDataExcel($datos,$filename);
					    	$this->visor->addComponent("ContentB", "content_body", $result);
						}

					break;

					case "Total":
					/*if (is_null($_REQUEST['busqueda'])) {
						$_REQUEST['busqueda'] = '';
					}
					$arrResponseTarjetasMagneticas = TarjetasMagneticasModel::tmListadoTotal($_REQUEST["busqueda"],$_REQUEST['rxp'],$_REQUEST['pagina']);
					$view_excel_template = $objTarjetasMagneticasTemplate->gridViewEXCEL($arrResponseTarjetasMagneticas["datos"]);	
					*/
	        			$blockearexcel = TRUE;
						$resu = $objTarjetasMagneticasModel->busquedaExcel();
						$resultt = TarjetasMagneticasTemplate::reporteExcel($resu);
						exit(0);
						
					break;


					case "Actualizar":

						$filename 		= $_REQUEST['filename'];
						$codcliente 	= trim($_REQUEST['codcliente']);

						$archivo		= "/sistemaweb/ventas_clientes/libro_excel_placas/".$filename;
						$data			= new Spreadsheet_Excel_Reader($archivo);
						$resultados 	= TarjetasMagneticasModel::InsertarExcel($data, $usuario, $ip, $codcliente);

						$placas 		= substr($resultados[4], 0, -1);

						if(($resultados)){

							$msg = "Placas Duplicadas: {$resultados[1]}. ";
							$msg .= "Placas Ingresadas: {$resultados[2]}. ";
							$msg .= "Placas Existentes: {$resultados[3]}. ";

							echo "<script>alert('{$msg}');</script>\n";

							$busqueda	= TarjetasMagneticasModel::tmListado('',$_REQUEST['rxp'],$_REQUEST['pagina'],$placas,$codcliente);
				    			$result		= TarjetasMagneticasTemplate::formBuscar($listado['paginacion']);
							$result		.= TarjetasMagneticasTemplate::listado($busqueda['datos']);
					    		$this->visor->addComponent("ContentB", "content_body", $result);
						} else {

							$result	= "<center><blink style='color: red'><<< Error >>></blink></center>";
					    		$this->visor->addComponent("ContentB", "content_body", $result);

						}

					break;

			    	case 'Agregar':
						$result = TarjetasMagneticasTemplate::formTarjetasMagneticas(array());
				    	$this->visor->addComponent("ContentB", "content_body", $result);
				    break;
		
					case 'Reporte':
						$result = TarjetasMagneticasModel::ModelReportePDF($_REQUEST["busqueda"]);
						$record .= TarjetasMagneticasTemplate::TemplateReportePDF($result['datos']);
						$this->visor->addComponent("ContentB", "content_body", $record);
					break;
		
		    		case 'Eliminar':

			 	   		$result = TarjetasMagneticasModel::eliminarRegistro($_REQUEST["registroid"]);
			    		if ($result == OK){
							$listado= true;
			    		} else {
							$result = TarjetasMagneticasTemplate::errorResultado($result);
							$this->visor->addComponent("ContentB", "content_body", $result);
			    		}

			    	break;
	    
		    		case 'Modificar':
						TarjetasMagneticasTemplate::setBandera_num_tarjeta(true);
						TarjetasMagneticasTemplate::setBandera_num_placa(true);
		    			$record = TarjetasMagneticasModel::recuperarRegistroArray($_REQUEST["registroid"]);
		    			$result = TarjetasMagneticasTemplate::formTarjetasMagneticas($record);
		    			$this->visor->addComponent("ContentB", "content_body", $result);
			    	break;
	    
		    		case 'Segres':
		    			$result = TarjetasMagneticasTemplate::formSegres();
		    			$this->visor->addComponent("ContentB", "content_body", $result);
		    	 	break;   
		    	
		    		case 'TEXTO':
		    			$listado=false;
	    			break;
		    	
		    		case 'PDF':
						$datos = TarjetasMagneticasModel::obtener_tarjetas();
						$result = TarjetasMagneticasTemplate::SegresPDF($datos);
						$OK = TarjetasMagneticasModel::actualizarSegres();
						$this->visor->addComponent("ListadoB", "resultados_grid", $result);
						$listado=false;
					break;	
							    	
		    		case 'Guardar':
						error_log(json_encode($_REQUEST));

						$otros 		= TarjetasMagneticasModel::TarjetasMagneticas($_REQUEST['tarjeta']['codcli']);
						$listado 	= false;

						if ($otros!='NO_GRUPO'){
							if($_REQUEST['validar']=='1'){
								// $result = TarjetasMagneticasModel::validarPlaca($_REQUEST['tarjeta']['numpla']);
								// if($result[0] == '1'){
								// 	$result = TarjetasMagneticasTemplate::errorResultado('ERROR: Ya existe Nro. Placa: '.$_REQUEST['tarjeta']['numpla']);
								// 	$this->visor->addComponent("error", "error_body", $result);
								// }elseif($result[0] == '2'){
									$result = TarjetasMagneticasModel::guardarRegistro($_REQUEST["registroid"], $ip, $usuario);
									if ($result!=''){
										$result = TarjetasMagneticasTemplate::errorResultado('ERROR: LA TARJETA YA EXISTE');
										$this->visor->addComponent("error", "error_body", $result);
									}else{
										$result = TarjetasMagneticasTemplate::formTarjetasMagneticas(array());
										$this->visor->addComponent("ContentB", "content_body", $result);
										$result = TarjetasMagneticasTemplate::errorResultado('SE GRABO/ACTUALIZO CORRECTAMENTE LA TARJETA '.$_REQUEST['tarjeta']['numtar'].' !!!');
										$this->visor->addComponent("error", "error_body", $result);
									}
								// }
							}
							if($_REQUEST['validar']=='2'){//MODIFICAR
								$result = TarjetasMagneticasModel::guardarRegistro($_REQUEST["registroid"], $ip, $usuario);
								if ($result!=''){
									$result = TarjetasMagneticasTemplate::errorResultado('ERROR: LA TARJETA YA EXISTE');
									$this->visor->addComponent("error", "error_body", $result);
								}else{
									$result = TarjetasMagneticasTemplate::formTarjetasMagneticas(array());
									$this->visor->addComponent("ContentB", "content_body", $result);
									$result = TarjetasMagneticasTemplate::errorResultado('SE GRABO/ACTUALIZO CORRECTAMENTE LA TARJETA '.$_REQUEST['tarjeta']['numtar'].' !!!');
									$this->visor->addComponent("error", "error_body", $result);
								}
							}

						} else {
							$result = TarjetasMagneticasTemplate::errorResultado('ERROR: EL CLIENTE ESTA ERRADO');
							$this->visor->addComponent("error", "error_body", $result);
						}

					break;
	    
		    		case 'Buscar':

		    			if (isset($_GET['pagiBusqueda'])) {
		    		    	$arrBusqueda = array(
							"busqueda" => array(
								"codigo" => $_GET['pagiBusqueda']
								)
							);
		    		    	$_GET["busqueda"] = $arrBusqueda["busqueda"];
		    		    }

						$busqueda 	= TarjetasMagneticasModel::tmListado($_GET["busqueda"], $_GET['rxp'], $_GET['pagina'], '', '');
						$result   	= TarjetasMagneticasTemplate::formBuscar($busqueda['paginacion'], $_GET["busqueda"]);
						$result 	.= TarjetasMagneticasTemplate::listado($busqueda['datos']);
				    	$this->visor->addComponent("ContentB", "content_body", $result);
						$listado = false;

					break;

					case 'descargarFormatoExcel':		
						$filename = '/sistemaweb/assets/downloads/placas.xls';
						if (file_exists($filename)) {
							header("Content-type: application/vnd.ms-excel");
							header("Content-Disposition: attachment; filename=$filename");
							header("Expires: 0");
							header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
							readfile($filename);
						} else {
						    ?><script>alert("<?php echo 'No existe formato de importación excel' ; ?> ");</script><?php
						}
		            break;

						    
		   			default:
			    			$listado = true;
		    			break;

				}
		
				if ($listado) {

				    	$listado   = TarjetasMagneticasModel::tmListado('',$_REQUEST['rxp'],$_REQUEST['pagina'],$_REQUEST['codigo'],'');
				    	$result    = TarjetasMagneticasTemplate::formBuscar($listado['paginacion'], '');
				    	$result   .= TarjetasMagneticasTemplate::listado($listado['datos']);
				    	$this->visor->addComponent("ContentB", "content_body", $result);
				}

      				break;
      		
      			case 'TARJMAGDET':

        			switch($this->action){
        			
					case 'setRegistro':			
							error_log("Entro");												
							$result = TarjetasMagneticasTemplate::setRegistros($_REQUEST["codigo"], $_REQUEST["buscar_todos"]);																					
							error_log(json_encode(array($result)));
							$this->visor->addComponent("desc_cliente", "desc_cliente", $result);
						break;
          					
		  			case 'ValidarNroTar':
		    				$result = TarjetasMagneticasModel::validarNroTarjeta($_REQUEST["NroTarj"]);
		    				$this->visor->addComponent("MensajeValidacion", "MensajeValidacion", $result);
		  				break;

		  			case 'ValidarPlaca':
		    				$result = TarjetasMagneticasModel::validarPlaca($_REQUEST["Placa"]);
		    				$this->visor->addComponent("MensajeValidacion2", "MensajeValidacion2", $result[1]);
		  				break;
		  				
          				default:
            					//listar ultimos movimientos
          					break;
        			}

			break;

      			default:
        			$this->visor->addComponent('ContentB', 'content_body', '<h2>TAREA "'.$this->request.'" NO CONOCIDA EN REGISTROS</h2>');
      				break;
      		}
    	}
}
