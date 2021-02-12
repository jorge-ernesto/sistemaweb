<?php

date_default_timezone_set('UTC');
ini_set("upload_max_filesize", "15M");

class ListaComprasController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action']) ? $this->action = $_REQUEST['action']:$this->action='';
    }
    
    function Run() {
		include 'reportes/m_lista_compras.php';
		include 'reportes/t_lista_compras.php';
		include '../include/Classes/excel_reader2.php';
	
		$this->Init();

		$result = '';
		$result_f = '';

		$search_form = false;

		if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"),"unknown"))
			$ip = getenv("HTTP_CLIENT_IP");
		else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
			$ip = getenv("HTTP_X_FORWARDED_FOR");
		else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
			$ip = getenv("REMOTE_ADDR");
		else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
			$ip = $_SERVER['REMOTE_ADDR'];

		$usuario	= $_SESSION['auth_usuario'];

		switch ($this->action) {

			case "Importar":
				$result = ListaComprasTemplate::ImportarDataExcel();
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", "");
				break;

			case "Importar Lista Excel":

				$filename 	= $_FILES['ubica']['name'];
				$resultado 	= ListaComprasModel::extension($filename);
				$tamano 	= $_FILES['ubica']['size']/1024/1024;
				$tamano 	= substr($tamano,0,5);

				if ($_FILES['ubica']["error"] > 1){
					echo "<script>alert('Error al ubicar el archivo')</script>";
				} elseif ($tamano >= 15 ) {
					echo "<script>alert('Error el archivo debe ser menor a 15MB')</script>";
				} elseif ($resultado != 'xls') {
					?><script>alert("<?php echo 'Error la extension debe de ser .xls' ; ?> ");</script><?php
				}else{
					move_uploaded_file($_FILES['ubica']['tmp_name'],"/sistemaweb/ventas_clientes/precios_proveedor_excel/" . $_FILES['ubica']['name']);
					$result     	= ListaComprasTemplate::ImportarDataExcel();
					$archivo	= "/sistemaweb/ventas_clientes/precios_proveedor_excel/".$_FILES['ubica']['name'];
					$datos		= new Spreadsheet_Excel_Reader($archivo);
					$datos->setOutputEncoding("CP1251");
					$datos->read($archivo);
					$result_f 	= ListaComprasTemplate::MostrarDataExcel($datos, $filename);
					$this->visor->addComponent("ContentB", "content_body", $result);
					$this->visor->addComponent("ContentF", "content_footer", $result_f);
				}

			break;

			case "Actualizar":

				$filename 		= $_REQUEST['filename'];
				$codproveedor 	= trim($_REQUEST['codproveedor']);
				$codmoneda 		= trim($_REQUEST['codmoneda']);

				$archivo	= "/sistemaweb/ventas_clientes/precios_proveedor_excel/".$filename;
				$data		= new Spreadsheet_Excel_Reader($archivo);
				$data->setOutputEncoding("CP1251");
				$data->read($archivo);
				$resultados = ListaComprasModel::InsertarExcel($data, $usuario, $ip, $codproveedor, $codmoneda);

				$codigos 	= substr($resultados[4], 0, -1);

				if(($resultados)){

					$msg = "Costos Nuevos: {$resultados[2]}. ";
					$msg .= "Costos Actualizados: {$resultados[3]}. ";
					$msg .= "Articulos No Validos: {$resultados[1]}. ";

					echo "<script>alert('{$msg}');</script>\n";

					$result     	= ListaComprasTemplate::searchForm("");
					$this->visor->addComponent("ContentB", "content_body", $result);

					$busqueda    	= ListaComprasModel::mostrar($codproveedor, $codigos);
					$result_f 	= ListaComprasTemplate::reporte($busqueda);
					$this->visor->addComponent("ContentF", "content_footer", $result_f);

				} else {

					$result_f = "<center><blink style='color: red'><<< Error >>></blink></center>";
					$this->visor->addComponent("ContentF", "content_footer", $result_f);

				}

			break;

			case "Buscar":
				$busqueda    	= ListaComprasModel::mostrar(trim($_REQUEST['cod_proveedor']), trim($_REQUEST['cod_articulo']));
				$result_f 	= ListaComprasTemplate::reporte($busqueda, trim($_REQUEST['cod_proveedor']));
				$result     	= ListaComprasTemplate::searchForm(trim($_REQUEST['cod_proveedor']));
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", $result_f);
				break;

			case "Agregar":

				$result 	= ListaComprasTemplate::formAgregar("A", "", "", "", "", "", "", "", $_REQUEST['cod_proveedor']);
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", "");
				break;

			case "Editar":

				$fecha_compra	= trim($_REQUEST['fecha_compra']);
				$proveedor	= trim($_REQUEST['cod_proveedor']); 
				$nom_proveedor	= trim($_REQUEST['nom_proveedor']);
				$articulo	= trim($_REQUEST['cod_articulo']);   
				$nom_articulo	= trim($_REQUEST['nom_articulo']);
				$precio		= trim($_REQUEST['precio']);
				$moneda		= trim($_REQUEST['moneda']);
				$rec_arti_prove	= trim($_REQUEST['rec_arti_prove']);

				$result = ListaComprasTemplate::formAgregar("E", $proveedor, $nom_proveedor, $articulo, $nom_articulo, $precio, $moneda, $rec_arti_prove, $_REQUEST['bproveedor']);

				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", "");

			break;

			case "Eliminar":
				$res 		= ListaComprasModel::eliminar(trim($_REQUEST['cod_proveedor']), trim($_REQUEST['cod_articulo']));
				$resultados    	= ListaComprasModel::mostrar(trim($_REQUEST['cod_proveedor']), '');
				$result     	= ListaComprasTemplate::searchForm(trim($_REQUEST['cod_proveedor']));
				$result_f 	= ListaComprasTemplate::reporte($resultados, trim($_REQUEST['cod_proveedor']));
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", $result_f);
				break;


			case 'Guardar':

				$proveedor	= trim($_REQUEST['cod_proveedor']);
				$articulo	= trim($_REQUEST['cod_articulo']);
				$precio		= trim($_REQUEST['precio']);
				$moneda		= trim($_REQUEST['moneda']);
				$rec_arti_prove	= trim($_REQUEST['rec_arti_prove']);

				if (trim($proveedor)=="" or trim($articulo)=="" or trim($precio)==""){
					$result = '<script name="accion">alert("Falta completar datos") </script>';
					echo $result;
				} else {
					if (trim($_REQUEST['tipoguardar']) == "A")				    
						$result = ListaComprasModel::ingresar("A", $proveedor, $articulo, $precio, $moneda, $rec_arti_prove, $usuario, $ip);
						if($result == 4) $result = '<script name="accion">alert("El articulo ya existe, solo se debe editar.") </script>';
					else				    
						$result = ListaComprasModel::ingresar("E", $proveedor, $articulo, $precio, $moneda, $rec_arti_prove, $usuario, $ip);	

					if($result == 1){

						echo "<script>alert('Datos guardados correctamente')</script>";

						$result     	= ListaComprasTemplate::searchForm($_REQUEST['bproveedor']);
						$resultados    	= ListaComprasModel::mostrar($_REQUEST['bproveedor'], '');
						$result_f 	= ListaComprasTemplate::reporte($resultados, $_REQUEST['bproveedor']);

						$this->visor->addComponent("ContentB", "content_body", $result);
						$this->visor->addComponent("ContentF", "content_footer", $result_f);
					}

					if($result == 2) $result = '<script name="accion">alert("Articulo no encontrado.") </script>';
					if($result == 3) $result = '<script name="accion">alert("Proveedor no encontrado.") </script>';					

					echo $result;
				}
				break;

		    default:
				$result     = ListaComprasTemplate::searchForm("");
				$resultados = ListaComprasModel::mostrar('', '');
				$result_f 	= ListaComprasTemplate::reporte($resultados, "");

				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", $result_f);
				break;
		}		
	}
}
