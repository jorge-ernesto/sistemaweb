<?php
date_default_timezone_set('UTC');
ini_set("upload_max_filesize", "15M");

class TipodeCambioController extends Controller {
	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action']) ? $this->action = $_REQUEST['action']:$this->action='';
	}

	function Run() {
		include 'reportes/m_importar_compra.php';
		include 'reportes/t_importar_compra.php';
		include '../include/paginador_new.php';
		include '../include/Classes/excel_reader2.php';

		$this->Init();

		$result = '';
		$result_f = '';
		$buscar = false;

	    if(!isset($_REQUEST['rxp'],$_REQUEST['pagina'])) {
			$_REQUEST['rxp'] = 30;
		 	$_REQUEST['pagina'] = 1;
	    }

		$ip = '';

		if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"),"unknown"))
			$ip = getenv("HTTP_CLIENT_IP");
		else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
			$ip = getenv("HTTP_X_FORWARDED_FOR");
		else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
			$ip = getenv("REMOTE_ADDR");
		else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
			$ip = $_SERVER['REMOTE_ADDR'];

		$usuario = $_SESSION['auth_usuario'];

		switch($this->action) {
			case 'descargarFormatoExcel':		
				$filename = '/sistemaweb/assets/downloads/compras_mercaderia_proveedor.xls';
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

			case "Importar":
				$result     	= TipodeCambioTemplate::ImportarDataExcel();
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", "");
				break;

			case "Importar Compra Excel":
				$filename 	= $_FILES['ubica']['name'];
				$resultado 	= TipodeCambioModel::extension($filename);
				$tamano 	= $_FILES['ubica']['size']/1024/1024;
				$tamano 	= substr($tamano,0,5);

				if ($_FILES['ubica']["error"] > 1){
					echo "<script>alert('Error al ubicar el archivo')</script>";
				} elseif ($tamano >= 15 ) {
					echo "<script>alert('Error el archivo debe ser menor a 15MB')</script>";
				} elseif ($resultado != 'xls') {
					?><script>alert("<?php echo 'Error la extension debe de ser .xls' ; ?> ");</script><?php
				} else {
					move_uploaded_file($_FILES['ubica']['tmp_name'],"/sistemaweb/inventarios/compras_excel/" . $_FILES['ubica']['name']);
					$result  	= TipodeCambioTemplate::ImportarDataExcel();
					$archivo 	= "/sistemaweb/inventarios/compras_excel/".$_FILES['ubica']['name'];
					$datos 		= new Spreadsheet_Excel_Reader($archivo);
					$datos->setOutputEncoding("CP1251");
					$datos->read($archivo);
					$result_f 	= TipodeCambioTemplate::MostrarDataExcel($datos,$filename);
					$this->visor->addComponent("ContentB", "content_body", $result);
					$this->visor->addComponent("ContentF", "content_footer", $result_f);
				}
				break;

			case "Buscar":

				$busqueda    	= TipodeCambioModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina'], $_REQUEST['estacion'], $_REQUEST['fecha'], $_REQUEST['fecha2'], $_REQUEST['formulario'], $_REQUEST['mov_docurefe'], $_REQUEST['art_desde'], $_REQUEST['art_desde2']);

				if($busqueda == ''){
					$result_f	= "<center><blink style='color: red'><<< No hay Registros >>></blink></center>";
					$this->visor->addComponent("ContentF", "content_footer", $result_f);
				}else{
					$busqueda   = TipodeCambioModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina'], $_REQUEST['estacion'], $_REQUEST['fecha'], $_REQUEST['fecha2'], $_REQUEST['formulario'], $_REQUEST['mov_docurefe'], $_REQUEST['art_desde'], $_REQUEST['art_desde2']);
					$vec 		= array($_REQUEST['fecha'], $_REQUEST['fecha2']);
					$result     = TipodeCambioTemplate::formPag($busqueda['paginacion'], $vec);
					$result_f 	= TipodeCambioTemplate::resultadosBusqueda($busqueda['datos'], $_REQUEST['fecha'], $_REQUEST['fecha2']);
					$this->visor->addComponent("ContentB", "content_body", $result);
					$this->visor->addComponent("ContentF", "content_footer", $result_f);
				}
				break;

			case "Actualizar":

				$almacen 		= trim($_REQUEST['almacen']);
				$tf		 	= trim($_REQUEST['tipo_formulario']);
				$codproveedor 		= trim($_REQUEST['codproveedor']);
				$fecha	 		= trim($_REQUEST['fecha']);
				$tipo 			= trim($_REQUEST['tipo']);
				$serie 			= trim($_REQUEST['serie']);
				$numero 		= trim($_REQUEST['numero']);
				$rubro	 		= trim($_REQUEST['rubro']);
				$contabilizar 		= trim($_REQUEST['contabilizar']);
				$fperiodo 		= trim($_REQUEST['fperiodo']);
				$base 			= trim($_REQUEST['base']);
				$impuesto 		= trim($_REQUEST['impuesto']);
				$total 			= trim($_REQUEST['total']);
				$correlativo 		= trim($_REQUEST['correlativo']);
				$fsystem 		= trim($_REQUEST['fsystem']);
				$perce 			= trim($_REQUEST['perce']);
				$codmoneda		= trim($_REQUEST['codmoneda']);
				$cuentaspagar		= trim($_REQUEST['cuentaspagar']);
				$fvencimientoday	= trim($_REQUEST['fvencimientoday']);
				$fvencimiento		= trim($_REQUEST['fvencimiento']);

				if($perce < 1 || $perce == NULL || empty($perce))
					$perce = 0;

				$filename 	= $_REQUEST['filename'];
				$archivo	= "/sistemaweb/inventarios/compras_excel/".$filename;

				$tc 		= TipodeCambioModel::TipoCambio($fecha);

				if($tc == FALSE){

					$result_f = "<center><blink style='color: red'><<< No hay Tipo de Cambio - Fecha: $fecha >>></blink></center>";
					$this->visor->addComponent("ContentF", "content_footer", $result_f);

				} else {

					$begin		= TipodeCambioModel::BEGINTransaccion();

					if ($cuentaspagar == 'true'){
						/* REGISTRAR COMPRA */
						$insertcab 	= TipodeCambioModel::AgregarComprasCabecera($almacen,$fecha,$codproveedor,$rubro,$tipo,$serie,$numero, $fvencimientoday, $fvencimiento,$tc,$codmoneda,$base,$impuesto,$total,$perce,$correlativo,$contabilizar, $fperiodo, $fsystem);
						$insertdet 	= TipodeCambioModel::AgregarComprasDetalle($almacen,$fecha,$codproveedor,$tipo,$serie,$numero,$tc,$codmoneda,$total);
					}

					$data = new Spreadsheet_Excel_Reader($archivo);
					$data->setOutputEncoding("CP1251");
					$data->read($archivo);
					$resultados = TipodeCambioModel::InsertarExcel($data, $almacen, $tf, $codproveedor, $fecha, $tipo, $serie, $numero, $usuario, $ip, $codmoneda, $base, $tc, $cuentaspagar);

					/* FECHA PARA BUSCAR */
					$dia	= substr($data->val(3, 2), 0, 2);
					$mes	= substr($data->val(3, 2), 3, 2);
					$year	= substr($data->val(3, 2), 6, 4);

					$fecha = $dia."/".$mes."/".$year;

					if($resultados[0] == FALSE){
						$rollback = TipodeCambioModel::ROLLBACKTransaccion();
						$result_f = "<center><blink style='color: red'><<< Error al procesar compra>>></blink></center>";
						$this->visor->addComponent("ContentF", "content_footer", $result_f);
					} else {

						$commit		= TipodeCambioModel::COMMITransaccion();

						$msg = "Articulos Ingresados: {$resultados[3]}. ";
						$msg .= "Articulos Inexistentes: {$resultados[1]}. ";
						$msg .= "Articulos Existentes: {$resultados[2]}. ";
						$msg .= "Articulos Duplicados: {$resultados[4]}. ";

						echo "<script>alert('{$msg}');</script>\n";

						$result     	= TipodeCambioTemplate::formSearch(date("01/".m."/".Y), date(d."/".m."/".Y),$busqueda['paginacion']);
						$this->visor->addComponent("ContentB", "content_body", $result);

						$resultados = count($data->sheets[0]['cells']);

						$busqueda    	= TipodeCambioModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina'], $almacen, $fecha, $fecha, $tf, null, null, null);
						$result_f 	= TipodeCambioTemplate::resultadosBusqueda($busqueda['datos']);
						$this->visor->addComponent("ContentF", "content_footer", $result_f);
					}
				}
				break;

			case "Cintillo":

				$formulario = trim($_REQUEST['formulario']);
				$tf 		= trim($_REQUEST['tf']);
				$fecha 		= trim($_REQUEST['fecha']);
				$numcompra 	= trim($_REQUEST['numcompra']);

				echo '<script> window.open("/sistemaweb/inventarios/inv_cintillo.php?num_mov='.$formulario.'&tip_mov='.$tf.'&fecha='.$fecha.'&compra='.$numcompra.'","miwin","width=1000,height=500,scrollbars=yes, resizable=yes, menubar=no");</script>';
				break;

			default:
				$result     	= TipodeCambioTemplate::formSearch(date("01/m/Y"), date("d/m/Y"), NULL);
				$busqueda    	= TipodeCambioModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina'], NULL, date("01/m/Y"), date("d/m/Y"), NULL, NULL, NULL, NULL);

				$this->visor->addComponent("ContentB", "content_body", $result);

				if($busqueda == ''){
					$result_f	= "<center><blink style='color: red'><<< No hay Registros >>></blink></center>";
					$this->visor->addComponent("ContentF", "content_footer", $result_f);
				}else{
					$result_f 	= TipodeCambioTemplate::resultadosBusqueda($busqueda['datos'], date("01/m/Y"), date("d/m/Y"));
					$buscar = true;
					$this->visor->addComponent("ContentF", "content_footer", $result_f);
				}
				break;
		}
		$this->visor->addComponent("ContentT", "content_title", TipodeCambioTemplate::titulo());
	}
}
