<?php
/*
  Fecha de creacion     : Marzo 7, 2012, 5: 00 PM
  Autor                 : Nestor Hernandez Loli
  Fecha de modificacion :
  Modificado por        :

  Clase control del mantenimiento de la tabla c_pos_descuento_ruc
  Se solicita Actualizar, Eliminar, Consultar, Borrar
 */

class PosDescuentoRucController extends Controller {

	function Init() {
    	//Verificar seguridad
    	$this->visor = new Visor();
    	$this->task = @$_REQUEST["task"];
    	$this->action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : '';
    	//otros variables de entorno
	}

	function Run() {
	   	ob_start();
    	$this->Init();
    	$result = '';
    	require('maestros/m_pos_descuento_ruc.php');
    	require('maestros/t_pos_descuento_ruc.php');
    	include('../include/paginador_new.php');
		include '../include/Classes/excel_reader2.php';

		$template = new PosDescuentoRucTemplate();
		$modelo = new PosDescuentoRucModel();

		$this->visor->addComponent('ContentT', 'content_title', $template->titulo());

		if (isset($_REQUEST['rxp']) && !$_REQUEST['rxp'] && !$_REQUEST['pagina']) {
			$_REQUEST['rxp'] = 100;
			$_REQUEST['pagina'] = 0;
		}

		$listado = false;

		if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"),"unknown"))
			$ip = getenv("HTTP_CLIENT_IP");
		else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
			$ip = getenv("HTTP_X_FORWARDED_FOR");
		else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
			$ip = getenv("REMOTE_ADDR");
		else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
			$ip = $_SERVER['REMOTE_ADDR'];

		$usuario	= $_SESSION['auth_usuario'];
		//evaluar y ejecutar $action

		//VERSION ENERGIGAS
		$version_energigas = true;

		switch ($this->action) {
			case "Excel":
				$tipo  	= trim($_REQUEST['tipo_doc']);
				$rxp  	= pg_escape_string($_REQUEST['rxp']);
				$pagina = pg_escape_string($_REQUEST['pagina']);

				$arrDescuentos = $modelo->buscarPorTipo($_REQUEST['cliente'], $tipo, $rxp, $pagina);
						
				$view_excel_template = $template->gridViewEXCEL($arrDescuentos["datos"]);
				break;

            case 'Agregar':
                $result = $template->form(array(), 'Registrar');
				$this->visor->addComponent("ContentB", "content_body", $result);
				break;

            case 'Modificar':
				$record = $modelo->obtenerRegistro($_REQUEST["registroid"]);
				$result = $template->form($record, 'Actualizar');
				$this->visor->addComponent("ContentB", "content_body", $result);
            	break;

			case 'Eliminar':
				$f = $modelo->eliminarRegistro($_REQUEST["registroid"]);
				if ($f == 1) {
					$listado = true;
				} else {
					$result = $template->errorResultado(array("Hubo un error al borrar el registro"));
					$this->visor->addComponent("ContentB", "content_body", $result);
				}
				break;

			case 'Guardar':
				$listado = false;
				$pos_descuento_ruc_id = $_REQUEST['pos_descuento_ruc_id'];
				$ruc = trim(pg_escape_string($_REQUEST['ruc']));
				$art_codigo = trim(pg_escape_string($_REQUEST['art_codigo']));
				$descuento = trim(pg_escape_string($_REQUEST['descuento']));
				$activo = $_REQUEST['activo'];
				$tipo = $_REQUEST['tipo'];
				$validar = PosDescuentoRucModel::validarRuc(trim(pg_escape_string($_REQUEST['ruc'])));

            $errores = array();

				/**
				 * Para Notas de Despacho (1)
				 * -Valida que RUC exista
				 * 
				 * Para Factura (2)
				 * -No valida que RUC exista
				 * -Valida que el RUC tenga 11 digitos y que sea numerico
				 * 
				 * Boleta (3)
				 * -No valida que RUC exista
				 * -Valida que sea numerico
				 */
				if ($tipo == '3' and !is_numeric($ruc)) {
					$errores[] = "Ingrese un RUC con solo numeros";
				}else if ($tipo == '2' and (strlen($ruc) < 11 || !is_numeric($ruc))) {
				    $errores[] = "Ingrese un RUC con solo numeros y de 11 digitos";
				}else if ($tipo == '1'  and $validar!=1) {
					$errores[] = "no existe RUC en clientes";
				}

				/**
				 * Validaciones adicionales:
				 * -El descuento tiene que ser numerico
				 * -Codigo de Producto no puede estar vacio
				 * -Debe seleccionar un estado para el descuento (Activo o Inactivo)
				 * -El descuento no puede ser 0
				 */
				if (!is_numeric($descuento)) {
				    $errores[] = "El campo descuento tiene que ser un valor numerico";
				}

				if (empty($art_codigo)) {
				    $errores[] = "Seleccione un producto";
				}

				if(!isset($activo)) {
				    $errores[] = "Seleccione un estado para este descuento";
				}

				if($descuento == 0){
					$errores[] = "El descuento no puede ser de 0";
				}
                
                $tipoGuardar = $_REQUEST['tipo_guardar'];

				if (empty($errores)) {
				    $f = 0;
				    if ($tipoGuardar == 'Actualizar') {          
				        $f = $modelo->actualizarRegistro( $pos_descuento_ruc_id,$ruc, $art_codigo, $descuento, $activo, $tipo);
				    } else {
				        $f = $modelo->guardarRegistro( $ruc, $art_codigo, $descuento, $activo, $tipo);
				    }

				    if ($f <= 0) {
				        $result = $template->errorResultado(array('Hubo un error al guardar los datos'));
				        $this->visor->addComponent("error", "error_body", $result . "<br>");
				    } else {
				        $pos_descuento_ruc_id = $modelo -> obtenerUltimoID();
				        $datos = array(
							'pos_descuento_ruc_id' => $pos_descuento_ruc_id, 
							'ruc' => $ruc,
							'art_codigo' => $art_codigo,
							'descuento' => $descuento,
							'activo' => $activo,
							'tipo' => $tipo
						);

				        $result = $template->form($datos, 'Actualizar');

				        $this->visor->addComponent("ContentB", "content_body", $result);
				        $result = $template->errorResultado(array('Se guardaron los datos correctamente'));
				        $this->visor->addComponent("error", "error_body", $result);
				    }
				} else {
				    $datos = array(
						'pos_descuento_ruc_id' => $pos_descuento_ruc_id, 
						'ruc' => $ruc,
						'art_codigo' => $art_codigo,
						'descuento' => $descuento,
						'activo' => $activo,
						'tipo' => $tipo
					);

				    $result = $template->form($datos, $tipoGuardar);

				    $this->visor->addComponent("ContentB", "content_body", $result);
				    $result = $template->errorResultado($errores);

				    $this->visor->addComponent("error", "error_body", $result);
				}
				break;

			case 'Buscar':
				$tipo  	= trim($_REQUEST['tipo_doc']);
				$rxp  	= pg_escape_string($_REQUEST['rxp']);
				$pagina = pg_escape_string($_REQUEST['pagina']);

				$listado = $modelo->buscarPorTipo($_REQUEST['cliente'], $tipo, $rxp, $pagina);
				$result .= $template->formBuscar($listado["paginacion"]);
				$result = $template->listado($listado["datos"],$tipo);

				$this->visor->addComponent("ListadoB", "resultados_grid", $result);

				$listado = false;
	            break;


            case 'Importar':
				$result = $template->ImportarDataExcel();
			  	$this->visor->addComponent("ContentB", "content_body", $result);
            	break;

            case 'descargarFormatoExcel':		
				if($version_energigas == true){
					$filename = '/sistemaweb/assets/downloads/descuentos_ruc_optimizado.xls';
				}else{
					$filename = '/sistemaweb/assets/downloads/descuentos_ruc.xls';
				}
				if (file_exists($filename)) {
					header("Content-type: application/vnd.ms-excel");
					header("Content-Disposition: attachment; filename=$filename");
					readfile($filename);
				} else {
				    ?><script>alert("<?php echo 'No existe formato de importación excel' ; ?> ");</script><?php
				}
            	break;

			case "ImportarDescuentos":

				echo "<script>console.log('REQUEST: " . json_encode($_REQUEST) . "')</script>";
				echo "<script>console.log('FILES:" . json_encode($_FILES) . "')</script>";

				$filename 	= $_FILES['ubica']['name'];
				$resultado 	= $modelo->extension($filename); //Extension del archivo
				$tamano 	= $_FILES['ubica']['size']/1024/1024;
				$tamano 	= substr($tamano,0,5);

				echo "<script>console.log('" . json_encode( 
					array( 
						"filename"  => $filename, 
						"resultado" => $resultado, 
						"tamano"    => $tamano 
					) 
				) . "')</script>";

				if ($_FILES['ubica']["error"] > 1){
					echo "<script>alert('Error al ubicar el archivo')</script>";
				} elseif ($tamano >= 15 ) {
					echo "<script>alert('Error el archivo debe ser menor a 15MB')</script>";
				} elseif ($resultado != 'xls') {
					?><script>alert("<?php echo 'Error la extension debe de ser .xls' ; ?> ");</script><?php
				}else{
					
					//MOVEMOS ARCHIVO EXCEL
					move_uploaded_file($_FILES['ubica']['tmp_name'],"/sistemaweb/combustibles/excel_descuentos_ruc/" . $_FILES['ubica']['name']); //move_uploaded_file ( string $filename , string $destination )
					$archivo	= "/sistemaweb/combustibles/excel_descuentos_ruc/".$_FILES['ubica']['name'];
					
					//OBTENEMOS DATOS DE ARCHIVO EXCEL
					$datos		= new Spreadsheet_Excel_Reader($archivo);					
					$result     = $template->ImportarDataExcel();
					if($version_energigas == true){
						$result .= $template->MostrarDataExcelOptimizado($datos, $filename);
					}else{
						$result .= $template->MostrarDataExcel($datos, $filename);
					}

			    	$this->visor->addComponent("ContentB", "content_body", $result);

				}

			break;

			case "EnviarData":

				$filename 		= $_REQUEST['filename'];
				$nuproducto 	= trim($_REQUEST['nuproducto']);
				$notd 			= trim($_REQUEST['notd']);
				$nutd 			= trim($_REQUEST['nutd']);

				$archivo		= "/sistemaweb/combustibles/excel_descuentos_ruc/".$filename;
				$data			= new Spreadsheet_Excel_Reader($archivo);
				$resultados 	= $modelo->InsertarExcel($data, $usuario, $ip, $nuproducto, $notd, $nutd);

				$codcliente 	= substr($resultados[4], 0, -1);

				if(($resultados)){

					$msg = "Descuentos Duplicados: {$resultados[1]}. ";
					$msg .= "Descuentos Ingresados: {$resultados[2]}. ";
					$msg .= "Descuentos Existentes: {$resultados[3]}. ";

					echo "<script>alert('{$msg}');</script>\n";

					$listado	= $modelo->BuscarExcel($_REQUEST['rxp'], $_REQUEST['pagina'], $nuproducto, $nutd, $codcliente);

					$result 	= $template->formBuscar($listado["paginacion"], $nutd);
					$result 	.= $template->listado($listado["datos"], $nutd);

			    	$this->visor->addComponent("ContentB", "content_body", $result);
			    	$listado = false;

				} else {
					$result	= "<center><blink style='color: red'><<< Error >>></blink></center>";
			    	$this->visor->addComponent("ContentB", "content_body", $result);
				}

			break;

			default:
            	$listado = true;
			break;
		}

		if ($listado) {
			$rxp = pg_escape_string($_REQUEST['rxp']);
    		$pagina = pg_escape_string($_REQUEST['pagina']);
    		$listado = $modelo->listado('', $rxp, $pagina);

    		$result.= $template->formBuscar($listado["paginacion"], "");
    		$result .= $template->listado($listado['datos'], "");
    		$this->visor->addComponent("ContentB", "content_body", $result);
		}
	}

}