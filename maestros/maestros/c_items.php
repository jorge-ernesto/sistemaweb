<?php

ini_set("memory_limit", '3G');
ini_set("upload_max_filesize", "15M");
ini_set("post_max_size", "15M");
ini_set('max_execution_time', '700');
ini_set('max_input_time', '700');

include_once('../include/m_sisvarios.php');

class ItemsController extends Controller {

	function Init() {

        	$this->visor = new Visor();

        	isset($_REQUEST['action']) ? $this->action = $_REQUEST['action'] : $this->action = "";
        	isset($_REQUEST['task']) ? $this->task = $_REQUEST['task'] : $this->task = "";

        	if (isset($_REQUEST['clear'])) {
        		unset($_SESSION['items_pagina']);
            		unset($_SESSION['criterio']);
            		unset($_SESSION['txtbusqueda']);
        	}

		if (isset($_REQUEST['pagina']))
        		$_SESSION['items_pagina'] = $_REQUEST['pagina'];

        	isset($_SESSION['items_pagina']) ? $this->pagina = $_SESSION['items_pagina'] : $this->pagina = 1;

		if (isset($_REQUEST['criterio']))
			$_SESSION['criterio'] = $_REQUEST['criterio'];

		isset($_SESSION['criterio']) ? $this->criterio = $_SESSION['criterio'] : $this->criterio = "codigo";

		if (isset($_REQUEST['txtbusqueda']))
			$_SESSION['txtbusqueda'] = $_REQUEST['txtbusqueda'];

       		isset($_SESSION['txtbusqueda']) ? $this->txtbusqueda = $_SESSION['txtbusqueda'] : $this->txtbusqueda = "";

	}

	function Run() {

        	ob_start();

        	$this->Init();

        	include "maestros/t_items.php";
        	include "maestros/m_items.php";
		include "../include/Classes/excel_reader2.php";

        //Class
        $objModelItem = new ItemsModel();
        $objTemplateItem = new ItemsTemplate();

		$usuario	= $_SESSION['auth_usuario'];

		$listado	= false;
		$result		= "";
		$Impuestos	= $objModelItem->ObtieneItemImpuestos();
		$blockearexcael = false;

        error_log("Acciones");
        error_log(json_encode($this->task));
        error_log(json_encode($this->action));

		switch ($this->task) {
            		case 'ValTipoLi':
                		$resultLin = ItemsModel::ObtieneTablaGeneral('20', $_REQUEST['cod']);
                	break;

			default:
                		break;
        	}

        	switch ($this->action) {

			case "Importar":

				$result     	= ItemsTemplate::ImportarDataExcel();
		    		$this->visor->addComponent("ContentB", "content_body", $result);

			break;

			case "Importar Lista Excel":

				$filename 	= $_FILES['ubica']['name'];
				$resultado 	= ItemsModel::extension($filename);
				$tamano 	= $_FILES['ubica']['size']/1024/1024;
				$tamano 	= substr($tamano,0,5);

				if ($_FILES['ubica']["error"] > 1){
					echo "<script>alert('Error al ubicar el archivo')</script>";
				} elseif ($tamano >= 15 ) {
					echo "<script>alert('Error el archivo debe ser menor a 15MB')</script>";
				} elseif ($resultado != 'xls') {
					?><script>alert("<?php echo 'Error la extension debe de ser .xls' ; ?> ");</script><?php
				}else{

					move_uploaded_file($_FILES['ubica']['tmp_name'],"/sistemaweb/maestros/excel_articulos/" . $_FILES['ubica']['name']);
					$archivo	= "/sistemaweb/maestros/excel_articulos/".$_FILES['ubica']['name'];
					$datos		= new Spreadsheet_Excel_Reader($archivo);
					$result     	= ItemsTemplate::ImportarDataExcel();
                    $result		.= ItemsTemplate::MostrarDataExcel($datos,$filename);                                                    

			    		$this->visor->addComponent("ContentB", "content_body", $result);

				}

			break;


			case "Upload":

				include "lib/paginador.php";

                $filename 	= $_REQUEST['filename'];                

				$archivo	= "/sistemaweb/maestros/excel_articulos/".$filename;
				$data		= new Spreadsheet_Excel_Reader($archivo);
                $resultados 	= ItemsModel::InsertarExcel($data, $usuario);
                
                /*** Agregado 2020-01-22 ***/
                // die();
                echo "<script>console.log('" . json_encode($filename) . "')</script>";                
                echo "<script>console.log('" . json_encode($archivo) . "')</script>";
                echo "<script>console.log('" . json_encode($resultados) . "')</script>";
                // die();
                /***/

				$articulos 	= substr($resultados[3], 0, -1);

				if(($resultados)){

					$msg .= "Articulos Ingresados: {$resultados[1]}. ";
					$msg .= "Articulos Existentes: {$resultados[2]}. ";
					$msg .= "Articulos No Ingresados: {$resultados[4]}. ";
					$msg .= "Articulos Duplicados: {$resultados[5]}. ";

					echo "<script>alert('{$msg}');</script>\n";

					$desc 		= "";
					$codigo 	= "";
					$linea 		= "";
					$ubicacion 	= "";
					$orderby 	= "";
					$order 		= "";
			                $ubicacion 	= ($this->pagina - 1) * 100;

			                $items = ItemsModel::busqueda($desde, 100, $codigo, $desc, $ubicacion, $linea, $orderby, $order, $articulos);

				        $result = ItemsTemplate::listado($items, $this->pagina, $cuenta, $this->criterio, $codigo, $desc, $ubicacion, $linea, $orderby, $order);
			    		$this->visor->addComponent("ContentB", "content_body", $result);

				} else {

					$result	= "<center><blink style='color: red'><<< Error >>></blink></center>";
			    		$this->visor->addComponent("ContentB", "content_body", $result);

				}

			break;

    		case "Excel":
        		$blockearexcael = TRUE;
				$almacen = $_REQUEST['nualmacen'];

         		if (trim($_REQUEST['txtbusqueda']) != "") {
				  	$opcion = isset($this->criterio) ? $this->criterio : "codigo";
				  	$codigo = "";
				  	$desc 	= "";
				  	$linea 	= "";

				  	if($this->criterio == "codigo") {
				  		$codigo = $this->txtbusqueda;
				  	}elseif($this->criterio == "ubicacion") {
				  		$ubicacion = $this->txtbusqueda;
				 	}elseif($this->criterio == "descripcion") {
				 		$desc = $this->txtbusqueda;
				  	}else if ($this->criterio == "linea") {
				  		$linea = $this->txtbusqueda;
				  	}
				 	$resu = $objModelItem->busquedaExcel($codigo, $desc, $ubicacion, $linea, $almacen);
				} else {
					$resu = $objModelItem->busquedaExcel("", "", "", "", $almacen);
				}
				$resultt = ItemsTemplate::reporteExcel($resu, $almacen);
				exit(0);
			break;

            case "ExcelListaPrecios":
                $blockearexcael=true;
                echo json_encode($objModelItem->getListaPrecio());


                /*
                $blockearexcael = TRUE;
                $almacen = $_REQUEST['nualmacen'];

                if (trim($_REQUEST['txtbusqueda']) != "") {
                    $opcion = isset($this->criterio) ? $this->criterio : "codigo";
                    $codigo = "";
                    $desc   = "";
                    $linea  = "";

                    if($this->criterio == "codigo") {
                        $codigo = $this->txtbusqueda;
                    }elseif($this->criterio == "ubicacion") {
                        $ubicacion = $this->txtbusqueda;
                    }elseif($this->criterio == "descripcion") {
                        $desc = $this->txtbusqueda;
                    }else if ($this->criterio == "linea") {
                        $linea = $this->txtbusqueda;
                    }
                    $resu = $objModelItem->busquedaExcel($codigo, $desc, $ubicacion, $linea, $almacen);
                } else {
                    $resu = $objModelItem->busquedaExcel("", "", "", "", $almacen);
                }

                exec("php -f /sistemaweb/maestros/maestros/cron_excel_lista_precio.php > /dev/null &");
                */
                /*
                $arrResponseModelExcelListaPrecio = $objModelItem->getListaPrecio();
                if ($arrResponseModelExcelListaPrecio["sStatus"] == "success") {
                    $resultt = $objTemplateItem->gridViewEXCELListaPrecio($arrResponseModelExcelListaPrecio);
                } else {
                    echo "<script>alert('{$arrResponseModelExcelListaPrecio["sMessage"]}');</script>\n";
                }
                exit(0);
                */
            break;

            case "Delete":
                // 1. Primero verificar información histórica
                $arrResponse = $objModelItem->checkHistoryItem($_REQUEST['codigo']);
                if ($arrResponse["sStatus"] != "success") {
                    echo "<script charset='utf8'>alert('{$arrResponse["sMessage"]}');</script>\n";
                    $listado = true;
                } else {// 2. Eliminar solo si el ITEM no tiene registros (1).
                    $arrResponse = $objModelItem->deleteItem($_REQUEST['codigo']);
                    if ($arrResponse["sStatus"] != "success") {
                        echo "<script charset='utf8'>alert('{$arrResponse["sMessage"]}');</script>\n";
                    } else {
                        echo "<script charset='utf8'>alert('{$arrResponse["sMessage"]}');</script>\n";
                        $listado = true;
                    }
                }
                break;

			case "Modificar":

				echo "Modicando=>";

				$item			= ItemsModel::ObtieneItem($_REQUEST['codigo']);
				$lineas			= ItemsModel::ObtieneTablaGeneral('20', $_REQUEST['cod'] ? $_REQUEST['cod'] : trim($item['art_tipo']));
				$tipos 			= ItemsModel::ObtieneTablaGeneral('21');
				$marcas 		= ItemsModel::ObtieneTablaGeneral('MARC');
				$plus 			= ItemsModel::ObtieneTablaGeneral('TPLU');
				$unidades 		= ItemsModel::ObtieneTablaGeneral('34');
				$ubicaciones 	= ItemsModel::ObtieneUbicaciones();

				$listas 		= ItemsModel::ObtenerPreciosPorItemNew($_REQUEST['codigo']);

				$result 		= ItemsTemplate::formModificar($item, $lineas, $tipos, $marcas, $plus, $unidades, $ubicaciones, $Impuestos, $listas);

                break;

            case "Modificar.Grabar":

            	//echo "Modicando22=>";

            	$listacodigos = $_REQUEST['listacod'];
            	$listaprecios = $_REQUEST['listaprecio'];

                $listas         = ItemsModel::ObtenerPreciosPorItemNew($_REQUEST['codigo']);
            
				if ($_REQUEST['cod']) {
					$ValCbTipos = $_REQUEST['cod'];
				} elseif ($_REQUEST['combotipos'] && !$_REQUEST['cod']) {
					$ValCbTipos = $_REQUEST['combotipos'];
				} else {
					$ValCbTipos = trim($item['art_tipo']);
				}

				$item			= ItemsModel::ObtieneItem($_REQUEST['codigo']);
				$lineas 		= ItemsModel::ObtieneTablaGeneral('20', $ValCbTipos);
				$tipos 			= ItemsModel::ObtieneTablaGeneral('21');
				$marcas 		= ItemsModel::ObtieneTablaGeneral('MARC');
				$plus 			= ItemsModel::ObtieneTablaGeneral('TPLU');
				$unidades 		= ItemsModel::ObtieneTablaGeneral('34');
				$ubicaciones 	= ItemsModel::ObtieneUbicaciones();
				$result 		= ItemsTemplate::formModificar($item, $lineas, $tipos, $marcas, $plus, $unidades, $ubicaciones, $Impuestos, $listas);

                $precios_modif  = ItemsModel::PrecioModificar($_REQUEST['codigo'], $listacodigos, $listaprecios); 
            
				if(trim($item['art_plutipo']) != trim($_REQUEST['comboplu'])){
					$validar_modif 	= ItemsModel::Validar($_REQUEST['codigo']);
					if($validar_modif == '1' || $validar_modif == 1)
						$result 	= "<center style='color:#FF1601;'><blink>No se puede modificar. El producto tiene movimientos</blink></center>" . $result;
					else{
						$bResult	= ItemsModel::GrabarItem($_REQUEST['codigo'], $usuario);
						$result 	= ItemsTemplate::MostrarError($bResult, $this->action) . $result;
					}
				} else {
					$bResult	= ItemsModel::GrabarItem($_REQUEST['codigo'], $usuario);
					$item		= ItemsModel::ObtieneItem($_REQUEST['codigo']);
                    $listas     = ItemsModel::ObtenerPreciosPorItemNew($_REQUEST['codigo']);
					$result 	= ItemsTemplate::formModificar($item, $lineas, $tipos, $marcas, $plus, $unidades, $ubicaciones, $Impuestos, $listas);
					$result 	= ItemsTemplate::MostrarError($bResult, $this->action) . $result;
				}

            break;

            case "pluUpdate":
                $result = ItemsTemplate::pluUpdate();
                $this->visor->addComponent("ContentPlu", "prod_type", $result);
                return;

            case "Enlaces.Forms":
                switch ($_REQUEST['method']) {
                    case "Agregar":
                        $result = ItemsTemplate::formEnlaceAgregar($_REQUEST['codigo']);
                        break;
                    case "Modificar":
                        $item = ItemsModel::ObtieneItem($_REQUEST['enlace']);
                        $result = ItemsTemplate::formEnlaceModificar($_REQUEST['codigo'], $item, $_REQUEST['cantidad']);
                        break;
                }
                $this->visor->addComponent("ContentB", "items_enlazados", $result);
                return;

            case "Enlaces.Action": // Acciones dentro de la ventana de enlaces
                $codigo = isset($_REQUEST['codigo']) ? $_REQUEST['codigo'] : "";
                $result = false;
                switch ($_REQUEST['method']) {
                    case "Agregar":
                        if (!ItemsModel::esItemEnlazado($_REQUEST['codigo'], $_REQUEST['enlace']))
                            $result = ItemsModel::enlazarItem($_REQUEST['codigo'], $_REQUEST['enlace'], $_REQUEST['cantidad']);
                        break;
                    case "Modificar":
                        $result = ItemsModel::actualizarEnlace($_REQUEST['codigo'], $_REQUEST['enlace'], $_REQUEST['cantidad']);
                        break;
                    case "UpdateDesc":
                        return;
                    case "Borrar":
                        $r = ItemsModel::borrarEnlace($_REQUEST['codigo'], $_REQUEST['checks']);
                        break;
                }

            case "Enlaces":
                $codigo = str_pad($_REQUEST['codigo'], 13, "0", STR_PAD_LEFT);
                $item = ItemsModel::ObtieneItem($_REQUEST['codigo']);
                $enlaces = ItemsModel::ObtieneEnlaces($item);
                $item_principal = ItemsTemplate::enlacesPrincipal($item);
                $items_enlazados = ItemsTemplate::enlacesItems($enlaces, $_REQUEST['codigo']);
                $controles_principales = ItemsTemplate::enlacesControles();
                $this->visor->addComponent("ItemPrinc", "item_principal", $item_principal);
                $this->visor->addComponent("ItemsEnla", "items_enlazados", $items_enlazados);
                $this->visor->addComponent("ContPrinc", "controles_principales", $controles_principales);
                return;

            /**
             * Formulario: Alias
             * tabla: int_articulos_alias
             * campos: art_codigo (int_articulos) y codigo_alias
             * Acciones: Ver, modificar, agregar y eliminar
             */
            case "Alias":
                $codigo = str_pad($_REQUEST['codigo'], 13, "0", STR_PAD_LEFT);
                $item = ItemsModel::ObtieneItem($_REQUEST['codigo']);
                $alias = ItemsModel::ObtieneAlias($item);
                $item_principal = ItemsTemplate::aliasPrincipal($item);
                $items_alias = ItemsTemplate::aliasItems($alias, $_REQUEST['codigo']);
                $controles_principales = ItemsTemplate::aliasControles();
                $this->visor->addComponent("ItemPrinc", "item_principal", $item_principal);
                $this->visor->addComponent("ItemsAlias", "items_alias", $items_alias);
                $this->visor->addComponent("ContPrinc", "controles_principales", $controles_principales);
                return;

            case "Alias.Forms":
                switch ($_REQUEST['method']) {
                    case "Agregar":
                        $result = ItemsTemplate::formAliasAgregar($_REQUEST['codigo']);
                        break;
                    case "Modificar":
                        $item = ItemsModel::ObtieneItem($_REQUEST['enlace']);
                        $result = ItemsTemplate::formAliasModificar($_REQUEST['codigo'], $item, $_REQUEST['cantidad']);
                        break;
                }
                $this->visor->addComponent("ContentB", "items_alias", $result);
                return;

            case "Alias.Action": // Acciones dentro de la ventana de items alias
                $codigo = isset($_REQUEST['codigo']) ? $_REQUEST['codigo'] : "";
                $result = false;
                switch ($_REQUEST['method']) {
                    case "Agregar":
                        $arrResponseModal = ItemsModel::aliasItem($_REQUEST['codigo'], $_REQUEST['enlace']);
                        if ( $arrResponseModal['sStatus'] == 'success' ) {
                            echo "<script charset='utf8'>alert('{$arrResponseModal["sMessage"]}');</script>\n";
                        } else {
                            echo "<script charset='utf8'>alert('{$arrResponseModal["sMessage"]}');</script>\n";
                        }
                        break;
                    case "UpdateDesc":
                        return;
                    case "Borrar":
                        $r = ItemsModel::borrarAlias(trim($_REQUEST['codigo']), $_REQUEST['checks']);
                        if ( $r ) {
                            echo "<script charset='utf8'>alert('Registro eliminado');</script>\n";
                        } else {
                            echo "<script charset='utf8'>alert('Problemas al eliminar');</script>\n";
                        }
                        break;
                }

            case "Agregar":
                error_log("Agregar");
                error_log(json_encode($_REQUEST));
                echo "entro agregar2 -> ";
                $lineas = ItemsModel::ObtieneTablaGeneral('20', $_REQUEST['cod']);
                $tipos = ItemsModel::ObtieneTablaGeneral('21');
                $plus = ItemsModel::ObtieneTablaGeneral('TPLU');
                $unidades = ItemsModel::ObtieneTablaGeneral('34');
                $ubicaciones = ItemsModel::ObtieneUbicaciones();
                $listas  = ItemsModel::ObtenerPreciosPorItemNew("");
                $result = ItemsTemplate::formAgregar($tipos, $lineas, $plus, $unidades, $ubicaciones, $Impuestos, $_REQUEST['manual'],$listas);
                break;

            case "AgregarCod":
                error_log("AgregarCod");
                error_log(json_encode($_REQUEST));
                $CodManual = ItemsModel::ObtieneCodManual('select', '10');
                $lineas = ItemsModel::ObtieneTablaGeneral('20', $_REQUEST['cod']);
                $tipos = ItemsModel::ObtieneTablaGeneral('21');
                $plus = ItemsModel::ObtieneTablaGeneral('TPLU');
                $unidades = ItemsModel::ObtieneTablaGeneral('34');
                $ubicaciones = ItemsModel::ObtieneUbicaciones();
                $result = ItemsTemplate::formAgregar($tipos, $lineas, $plus, $unidades, $ubicaciones, $Impuestos, $CodManual, array());
                break;

            case "Agregar.Action":
                error_log("Agregar.Action");
                error_log(json_encode($_REQUEST));                
                $result = ItemsModel::agregarItem($usuario);
                if ($result == true) {
                    if ($_REQUEST['manual'] == 'si') {
                        ItemsModel::ObtieneCodManual('insert', '10');
                    }
                    $listado = true;
                } else {
                    $lineas 		= ItemsModel::ObtieneTablaGeneral('20', $_REQUEST['cod']);
                    $tipos 			= ItemsModel::ObtieneTablaGeneral('21');
                    $plus 			= ItemsModel::ObtieneTablaGeneral('TPLU');
                    $unidades 		= ItemsModel::ObtieneTablaGeneral('34');
                    $ubicaciones 	= ItemsModel::ObtieneUbicaciones();
                    $listas         = ItemsModel::ObtenerPreciosPorItemNew("");
                    $bResult 		= ItemsTemplate::formAgregar($tipos, $lineas, $plus, $unidades, $ubicaciones, $Impuestos, $_REQUEST['manual'], $listas);
                    $result 		= ItemsTemplate::MostrarError($result, $this->action) . $bResult;
                }
                break;

            case "Precios.Action":
                switch ($_REQUEST['method']) {
                    case "Modificar":
                        ItemsModel::PrecioModificar($_REQUEST['codigo'], $_REQUEST['lista'], $_REQUEST['precio']);
                        break;
                    case "Agregar":
                        if (ItemsModel::EsListaValida($_REQUEST['lista']))
                            ItemsModel::PrecioAgregar($_REQUEST['codigo'], $_REQUEST['lista'], $_REQUEST['precio']);
                        break;
                    case "Borrar":
                        for ($i = 0; $i < count($_REQUEST['listas']); $i++) {
                            ItemsModel::PrecioBorrar($_REQUEST['codigo'], $_REQUEST['listas'][$i]);
                        }
                        break;
                }

            case "Precios":
                $listas = ItemsModel::ObtenerPreciosPorItem($_REQUEST['codigo']);
                $result = ItemsTemplate::PreciosLista($_REQUEST['codigo'], $listas);
                $controles_principales = ItemsTemplate::enlacesControles();
                $this->visor->addComponent("ItemPrinc", "item_principal", $result);
                $this->visor->addComponent("ContPrinc", "controles_principales", $controles_principales);
                return;

            case "Precios.Modificar":
                $result = ItemsTemplate::PrecioModificar($_REQUEST['codigo'], $_REQUEST['lista'], ItemsModel::ObtenerPreciosPorItemLista($_REQUEST['codigo'], $_REQUEST['lista']));
                $this->visor->addComponent("ItemPrinc", "item_principal", $result);
                return;

            case "Precios.Agregar":
                $listas = ItemsModel::ObtieneTablaGeneral("LPRE");
                $result = ItemsTemplate::PrecioAgregar($_REQUEST['codigo'], $listas);
                $this->visor->addComponent("ItemsPrinc", "item_principal", $result);
                return;

            case "ObtenerMargenLinea":
                $linea = $_REQUEST['linea'];
                $margen = ItemsModel::ObtenerMargenLinea($linea);
                echo ItemsTemplate::enviarMargenLinea($margen);
                return;

            case "Buscar":
            default:
                $listado = true;
                break;
        }

	if ($listado == true) {

		$almacen = $_REQUEST['nualmacen'];

        	include "lib/paginador.php";
            if (!isset($_REQUEST['order']))
                $order = 'ASC';
            else
                $order = strtoupper($_REQUEST['order']);

            if (!isset($_REQUEST['orderby']))
                $orderby = 'art_codigo';
            else
                $orderby = $_REQUEST['orderby'];

		if (isset($this->txtbusqueda)) {

		        $opcion = isset($this->criterio) ? $this->criterio : "codigo";
		        $codigo = "";
		        $desc = "";
		        $linea = "";

		        if ($this->criterio == "codigo") {
		            $codigo = $this->txtbusqueda;
		        } elseif ($this->criterio == "ubicacion") {
		            $ubicacion = $this->txtbusqueda;
		        } elseif ($this->criterio == "descripcion") {
		            $desc = $this->txtbusqueda;
		        } else if ($this->criterio == "linea") {
		            $linea = $this->txtbusqueda;
		        }

		        $desde = ($this->pagina - 1) * 100;
		        $cuenta = ItemsModel::busquedaObtieneCantidad($codigo, $desc, $ubicacion, $linea);
		        $items = ItemsModel::busqueda($desde, 100, $codigo, $desc, $ubicacion, $linea, $orderby, $order, '', $almacen);

		} else {
		        if (!isset($desde))
		            $desde = ($this->pagina - 1) * 100;
		        if (!isset($cuenta))
		            $cuenta = ItemsModel::ObtieneCuentaItems();
		        if (!isset($items))
		            $items = ItemsModel::ObtieneItems($desde, 100);
		}

		ItemsTemplate::formBuscar($almacen);
		$result = ItemsTemplate::listado($items, $this->pagina, $cuenta, $this->criterio, $codigo, $desc, $ubicacion, $linea, $orderby, $order);

	}

        if (!$blockearexcael) {
            $this->visor->addComponent("ContentT", "content_title", ItemsTemplate::titulo());
            $this->visor->addComponent("ContentB", "content_body", $result);
        }
    }

}

