<?php

//include "lib/paginador.php";

class ItemsTemplate extends Template {
	function titulo() {
		return '<h2 style="color:#336699;" align="center">MAESTRO DE ITEMS</td>';
	}

	function listado($items, $pagina, $cuenta, $tipo_busqueda, $codigo, $descripcion, $ubicacion, $linea, $orderby, $order) {
		if ($codigo != "") {
			$_REQUEST['txtbusqueda'] = $codigo;
		} elseif ($descripcion != "") {
			$_REQUEST['txtbusqueda'] = $descripcion;
		} elseif ($ubicacion != "") {
			$_REQUEST['txtbusqueda'] = $ubicacion;
		} elseif ($linea != "") {
			$_REQUEST['txtbusqueda'] = $linea;
		}

		$bOficina = false;

		if ($tipo_busqueda != "")
			$url = 'control.php?rqst=MAESTROS.ITEMS&action=Buscar&txtbusqueda=' . $_REQUEST['txtbusqueda'] . '&criterio=' . $tipo_busqueda . '&pagina=';
		else
			$url = 'control.php?rqst=MAESTROS.ITEMS&action=&pagina=';

		$paginador = new CPaginador($cuenta, $url, "control", $pagina);

		$form = ItemsTemplate::formBuscar("");
		$lista = $form->getForm();

		$lista .= '<table width="100%" cellpadding="1" cellspacing="1"><caption></caption><tbody><tr>';
		$lista .= '
			<th class="grid_cabecera" height="30px">C&Oacute;DIGO';
		if (!($orderby == 'art_codigo' && $order == 'ASC'))
			$lista .= ' <a href="control.php?rqst=MAESTROS.ITEMS&amp;action=Buscar&amp;pagina=' . urlencode($pagina) . '&amp;criterio=' . urlencode($tipo_busqueda) . '&amp;txtbusqueda=' . urlencode($_REQUEST['txtbusqueda']) . '&amp;orderby=art_codigo&amp;order=asc" target="control"  style="color: #ffffff;"><img src="images/arriba.gif"></a>';
		if (!($orderby == 'art_codigo' && $order == 'DESC'))
			$lista .= '<a href="control.php?rqst=MAESTROS.ITEMS&amp;action=Buscar&amp;pagina=' . urlencode($pagina) . '&amp;criterio=' . urlencode($tipo_busqueda) . '&amp;txtbusqueda=' . urlencode($_REQUEST['txtbusqueda']) . '&amp;orderby=art_codigo&amp;order=desc" target="control"  style="color: #ffffff;"><img src="images/abajo.gif"></a>';
		$lista .= '</th>';

		$lista .= '<th class="grid_cabecera">DESCRIPCI&Oacute;N';
		if (!($orderby == 'art_descripcion' && $order == 'ASC'))
		    $lista .= '   <a href="control.php?rqst=MAESTROS.ITEMS&amp;action=Buscar&amp;pagina=' . urlencode($pagina) . '&amp;criterio=' . urlencode($tipo_busqueda) . '&amp;txtbusqueda=' . urlencode($_REQUEST['txtbusqueda']) . '&amp;orderby=art_descripcion&amp;order=asc" target="control" style="color: #ffffff;"><img src="images/arriba.gif"></a>';
		if (!($orderby == 'art_descripcion' && $order == 'DESC'))
		    $lista .= '<a href="control.php?rqst=MAESTROS.ITEMS&amp;action=Buscar&amp;pagina=' . urlencode($pagina) . '&amp;criterio=' . urlencode($tipo_busqueda) . '&amp;txtbusqueda=' . urlencode($_REQUEST['txtbusqueda']) . '&amp;orderby=art_descripcion&amp;order=desc" target="control" style="color: #ffffff;"><img src="images/abajo.gif"></a>';
		$lista .= '</th>';

		$lista .= '<th class="grid_cabecera">PRECIO';
		if (!($orderby == 'art_precio' && $order == 'ASC'))
		    $lista .= '<a href="control.php?rqst=MAESTROS.ITEMS&amp;action=Buscar&amp;pagina=' . urlencode($pagina) . '&amp;criterio=' . urlencode($tipo_busqueda) . '&amp;txtbusqueda=' . urlencode($_REQUEST['txtbusqueda']) . '&amp;orderby=art_precio&amp;order=asc" target="control" style="color: #ffffff;"><img src="images/arriba.gif"></a>';
		if (!($orderby == 'art_precio' && $order == 'DESC'))
		    $lista .= '<a href="control.php?rqst=MAESTROS.ITEMS&amp;action=Buscar&amp;pagina=' . urlencode($pagina) . '&amp;criterio=' . urlencode($tipo_busqueda) . '&amp;txtbusqueda=' . urlencode($_REQUEST['txtbusqueda']) . '&amp;orderby=art_precio&amp;order=desc" target="control" style="color: #ffffff;"><img src="images/abajo.gif"></a>';
		$lista .= '</th>';

		$lista .= '<th class="grid_cabecera">L&Iacute;NEA';
		if (!($orderby == 'art_linea' && $order == 'ASC'))
		    $lista .= ' <a href="control.php?rqst=MAESTROS.ITEMS&amp;action=Buscar&amp;pagina=' . urlencode($pagina) . '&amp;criterio=' . urlencode($tipo_busqueda) . '&amp;txtbusqueda=' . urlencode($_REQUEST['txtbusqueda']) . '&amp;orderby=art_linea&amp;order=asc" target="control" style="color: #ffffff;"><img src="images/arriba.gif"></a>';
		if (!($orderby == 'art_linea' && $order == 'DESC'))
		    $lista .= '<a href="control.php?rqst=MAESTROS.ITEMS&amp;action=Buscar&amp;pagina=' . urlencode($pagina) . '&amp;criterio=' . urlencode($tipo_busqueda) . '&amp;txtbusqueda=' . urlencode($_REQUEST['txtbusqueda']) . '&amp;orderby=art_linea&amp;order=desc" target="control" style="color: #ffffff;"><img src="images/abajo.gif"></a>';
		$lista .= '</th>';

		$lista .= '<th class="grid_cabecera">TIPO';
		if (!($orderby == 'art_tipo' && $order == 'ASC'))
		    $lista .= ' <a href="control.php?rqst=MAESTROS.ITEMS&amp;action=Buscar&amp;pagina=' . urlencode($pagina) . '&amp;criterio=' . urlencode($tipo_busqueda) . '&amp;txtbusqueda=' . urlencode($_REQUEST['txtbusqueda']) . '&amp;orderby=art_tipo&amp;order=asc" target="control" style="color: #ffffff;"><img src="images/arriba.gif"></a>';
		if (!($orderby == 'art_tipo' && $order == 'DESC'))
		    $lista .= '<a href="control.php?rqst=MAESTROS.ITEMS&amp;action=Buscar&amp;pagina=' . urlencode($pagina) . '&amp;criterio=' . urlencode($tipo_busqueda) . '&amp;txtbusqueda=' . urlencode($_REQUEST['txtbusqueda']) . '&amp;orderby=art_tipo&amp;order=desc" target="control" style="color: #ffffff;"><img src="images/abajo.gif"></a>';
		$lista .= '</th>';

		$lista .= '<th class="grid_cabecera">UNIDAD';
		if (!($orderby == 'art_unidad' && $order == 'ASC'))
		    $lista .= ' <a href="control.php?rqst=MAESTROS.ITEMS&amp;action=Buscar&amp;pagina=' . urlencode($pagina) . '&amp;criterio=' . urlencode($tipo_busqueda) . '&amp;txtbusqueda=' . urlencode($_REQUEST['txtbusqueda']) . '&amp;orderby=art_unidad&amp;order=asc" target="control" style="color: #ffffff;"><img src="images/arriba.gif"></a>';
		if (!($orderby == 'art_unidad' && $order == 'DESC'))
		    $lista .= '<a href="control.php?rqst=MAESTROS.ITEMS&amp;action=Buscar&amp;pagina=' . urlencode($pagina) . '&amp;criterio=' . urlencode($tipo_busqueda) . '&amp;txtbusqueda=' . urlencode($_REQUEST['txtbusqueda']) . '&amp;orderby=art_unidad&amp;order=desc" target="control" style="color: #ffffff;"><img src="images/abajo.gif"></a>';
		$lista .= '</th>';

		$lista .= '<th class="grid_cabecera">UBICACI&Oacute;N';
		if (!($orderby == 'art_ubicacion' && $order == 'ASC'))
		    $lista .= ' <a href="control.php?rqst=MAESTROS.ITEMS&amp;action=Buscar&amp;pagina=' . urlencode($pagina) . '&amp;criterio=' . urlencode($tipo_busqueda) . '&amp;txtbusqueda=' . urlencode($_REQUEST['txtbusqueda']) . '&amp;orderby=art_ubicacion&amp;order=asc" target="control" style="color: #ffffff;"><img src="images/arriba.gif"></a>';
		if (!($orderby == 'art_ubicacion' && $order == 'DESC'))
		    $lista .= '<a href="control.php?rqst=MAESTROS.ITEMS&amp;action=Buscar&amp;pagina=' . urlencode($pagina) . '&amp;criterio=' . urlencode($tipo_busqueda) . '&amp;txtbusqueda=' . urlencode($_REQUEST['txtbusqueda']) . '&amp;orderby=art_ubicacion&amp;order=desc" target="control" style="color: #ffffff;"><img src="images/abajo.gif"></a>';
		$lista .= '</th>';

		$lista .= '<th class="grid_cabecera">C&Oacute;DIGO SKU';
		if (!($orderby == 'art_sku' && $order == 'ASC'))
		    $lista .= ' <a href="control.php?rqst=MAESTROS.ITEMS&amp;action=Buscar&amp;pagina=' . urlencode($pagina) . '&amp;criterio=' . urlencode($tipo_busqueda) . '&amp;txtbusqueda=' . urlencode($_REQUEST['txtbusqueda']) . '&amp;orderby=art_sku&amp;order=asc" target="control" style="color: #ffffff;"><img src="images/arriba.gif"></a>';
		if (!($orderby == 'art_sku' && $order == 'DESC'))
		    $lista .= '<a href="control.php?rqst=MAESTROS.ITEMS&amp;action=Buscar&amp;pagina=' . urlencode($pagina) . '&amp;criterio=' . urlencode($tipo_busqueda) . '&amp;txtbusqueda=' . urlencode($_REQUEST['txtbusqueda']) . '&amp;orderby=art_sku&amp;order=desc" target="control" style="color: #ffffff;"><img src="images/abajo.gif"></a>';
		$lista .= '</th>';

		$lista .= '<th class="grid_cabecera">ACTIVO';
		if (!($orderby == 'art_activo' && $order == 'ASC'))
		    $lista .= ' <a href="control.php?rqst=MAESTROS.ITEMS&amp;action=Buscar&amp;pagina=' . urlencode($pagina) . '&amp;criterio=' . urlencode($tipo_busqueda) . '&amp;txtbusqueda=' . urlencode($_REQUEST['txtbusqueda']) . '&amp;orderby=art_activo&amp;order=asc" target="control" style="color: #ffffff;"><img src="images/arriba.gif"></a>';
		if (!($orderby == 'art_activo' && $order == 'DESC'))
		    $lista .= '<a href="control.php?rqst=MAESTROS.ITEMS&amp;action=Buscar&amp;pagina=' . urlencode($pagina) . '&amp;criterio=' . urlencode($tipo_busqueda) . '&amp;txtbusqueda=' . urlencode($_REQUEST['txtbusqueda']) . '&amp;orderby=art_activo&amp;order=desc" target="control" style="color: #ffffff;"><img src="images/abajo.gif"></a>';
		$lista .= '</th>';

		$lista .= '<th class="grid_cabecera">STOCK';
		if (!($orderby == 'art_stock' && $order == 'ASC'))
		    $lista .= ' <a href="control.php?rqst=MAESTROS.ITEMS&amp;action=Buscar&amp;pagina=' . urlencode($pagina) . '&amp;criterio=' . urlencode($tipo_busqueda) . '&amp;txtbusqueda=' . urlencode($_REQUEST['txtbusqueda']) . '&amp;orderby=art_stock&amp;order=asc" target="control" style="color: #ffffff;"><img src="images/arriba.gif"></a>';
		if (!($orderby == 'art_stock' && $order == 'DESC'))
		    $lista .= '<a href="control.php?rqst=MAESTROS.ITEMS&amp;action=Buscar&amp;pagina=' . urlencode($pagina) . '&amp;criterio=' . urlencode($tipo_busqueda) . '&amp;txtbusqueda=' . urlencode($_REQUEST['txtbusqueda']) . '&amp;orderby=art_stock&amp;order=desc" target="control" style="color: #ffffff;"><img src="images/abajo.gif"></a>';
		$lista .= '</th>';

		$lista .= '<th class="grid_cabecera">USUARIO</th>';
		$lista .= '<th class="grid_cabecera"></th>';

        	for ($i = 0; $i < count($items); $i++) {
		    	$item = $items[$i];
		    	$lista .= "<tr class='bgcolor'>";
		    	$lista .= "<td><a href=\"control.php?rqst=MAESTROS.ITEMS&action=Modificar&codigo=" . $item[0] . "\" target=\"control\">" . $item[0] . "</a></td>";
		    	$lista .= "<td align='left'>" . $item[1] . "</td>";
		    	$lista .= "<td align='right'>" . $item[10] . "</td>";
		    	$lista .= "<td align='center'>" . $item[2] . "</td>";
		    	$lista .= "<td align='center'>" . $item[3] . "</td>";
		    	$lista .= "<td align='center'>" . $item[4] . "</td>";
		    	$lista .= "<td align='center'>" . $item[5] . "</td>";
		    	$lista .= "<td align='center'>" . $item[6] . "</td>";
		    	$lista .= "<td align='center'>" . ($item[7] == "0" ? "Si" : "No") . "</td>";
		    	$lista .= "<td align='right'>" . $item[9] . "</td>";
		    	$lista .= "<td align='center'>" . $item[8] . "</td>";
		    	$lista .= "<td><a href=\"control.php?rqst=MAESTROS.ITEMS&action=Delete&codigo=" . $item[0] . "\" target=\"control\">Eliminar</a></td>";
		    	$lista .= "</tr>\n";
        	}

        	$lista .= '
			</tr>
			</tbody>
			</table>
		';

        	if (count($items) == 0) {
			$lista .= "<tr>";
		    	$lista .= "<td colspan=\"6\">No hay registros</td>";
		    	$lista .= "</tr>";
		}

		$lista .= '<table width="100%"><tr><td width="50%">' . $paginador->obtienePaginador() . '</td><td align="right">';
		$lista .= 'Mostrando del ' . $paginador->desde() . ' al ' . $paginador->hasta() . ' de ' . $cuenta . '</td></tr></table>';
		$lista .= '</div><br>';

		return $lista;

	}

	function formBuscar($almacen) {

		if ($almacen == "") 
			$almacen = $_SESSION['almacen'];

		$almacenes = ItemsModel::obtenerSucursales("");

		$bOficina = false;
		$valores = Array("codigo" => "C&oacute;digo", "descripcion" => "Descripci&oacute;n", "ubicacion" => "Ubicaci&oacute;n ", "linea" => "L&iacute;nea");

		$form = new form('', "Buscar", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_MAIN, new form_element_hidden("rqst", "MAESTROS.ITEMS", '', '', 20));
		$form->addElement(FORM_GROUP_MAIN, new form_element_hidden("pagina", "1", '', '', 20));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("<tr><td colspan='2' align='center'>Almac&eacute;n: "));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("nualmacen", "", $almacen, $almacenes, espacios(3)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text("Buscar por:", "txtbusqueda", isset($_REQUEST['txtbusqueda']) ? htmlentities($_REQUEST['txtbusqueda']) : '', '', 15, 25, false, ""));
        $form->addElement(FORM_GROUP_MAIN, new form_element_radio('', "criterio", isset($_REQUEST['criterio']) ? htmlentities($_REQUEST['criterio']) : "descripcion", "<br>", '', '', $valores,""));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="center"><br>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Buscar"><img src="/sistemaweb/icons/gbuscar.png" align="right" />Buscar</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;&nbsp;<button name="action" type="submit" value="Agregar"><img src="/sistemaweb/icons/gadd.png" align="right" />Agregar</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;&nbsp;<button name="action" type="submit" value="Excel"><img src="/sistemaweb/icons/gexcel.png" align="right" />Excel</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;&nbsp;<button name="action" type="submit" value="Importar"><img src="/sistemaweb/icons/gexcel.png" align="right" />Importar Art&iacute;culos</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;&nbsp;<button id="btn-excel-lista_precio" name="action" type="button" value="ExcelListaPrecios"><img src="/sistemaweb/icons/gexcel.png" align="right" /> Excel Lista Precios</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</table>'));

        return $form;
	}

	function ImportarDataExcel() {

		$form = new form2('Lista de Articulos', 'form_listacompras', FORM_METHOD_POST, 'control.php', '', 'control','enctype="multipart/form-data"');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "MAESTROS.ITEMS"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br/><table border="0" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('Seleccionar archivo Excel: <input type="file" name="ubica" id="ubica" size="70" onClick="Mostrar();">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td><div id="ver" style="display:none;">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Importar Lista Excel"><img src="/sistemaweb/icons/gexcel.png" align="right" />Importar Lista Excel</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</div>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</table>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br/>'));
	
		return $form->getForm();

    	}


	function MostrarDataExcel($data, $filename) {

		$result		= '';
		$resultados 	= count($data->sheets[0]['cells']); //CUANTOS DATOS TIENE LA PRIMERA HOJA

		$result .= '<table align="center"> ';
		$result .= '<tr>';
		$result .= '<th style="font-size:0.7em; color:black;background-color: #D9F9B2">COD. LINEA</td>';
		$result .= '<th style="font-size:0.7em; color:black;background-color: #D9F9B2">LINEA</td>';
		$result .= '<th style="font-size:0.7em; color:black;background-color: #D9F9B2">MARCA</td>';
		$result .= '<th style="font-size:0.7em; color:black;background-color: #D9F9B2">COD. ARTICULO</td>';
		$result .= '<th style="font-size:0.7em; color:black;background-color: #D9F9B2">ARTICULO</td>';
		$result .= '<th style="font-size:0.7em; color:black;background-color: #D9F9B2">COD. UNIDAD MEDIDA</td>';
		$result .= '<th style="font-size:0.7em; color:black;background-color: #D9F9B2">UNIDAD MEDIDA</td>';
		$result .= '<th style="font-size:0.7em; color:black;background-color: #D9F9B2">COD. UBICACION INVENTARIO</td>';
		$result .= '<th style="font-size:0.7em; color:black;background-color: #D9F9B2">UBICACION INVENTARIO</td>';
		$result .= '<th style="font-size:0.7em; color:black;background-color: #D9F9B2">PRECIO</td>';
		$result .= '<th style="font-size:0.7em; color:black;background-color: #D9F9B2">COSTO INICIAL</td>';
		$result .= '<th style="font-size:0.7em; color:black;background-color: #D9F9B2">ESTADO</td>';
		$result .= '</tr>';

		/* VALORES DE LA FILA 2 DEL EXCEL DE ARTICULOS */

		$codlinea 	= $data->sheets[0]['cells'][2][1];
		$marca 		= $data->sheets[0]['cells'][2][3];
		$codproducto 	= $data->sheets[0]['cells'][2][4];
		$producto 	= $data->sheets[0]['cells'][2][5];
		$codunidad 	= $data->sheets[0]['cells'][2][6];
		$codinventario 	= $data->sheets[0]['cells'][2][8];
		$precio 	= $data->sheets[0]['cells'][2][10];
		$costo 		= $data->sheets[0]['cells'][2][11];
		/*** Agregado 2020-01-22 ***/
		echo "<script>console.log('" . json_encode($codlinea) . "')</script>";
		echo "<script>console.log('" . json_encode($marca) . "')</script>";
		echo "<script>console.log('" . json_encode($codproducto) . "')</script>";
		echo "<script>console.log('" . json_encode($producto) . "')</script>";
		echo "<script>console.log('" . json_encode($codunidad) . "')</script>";
		echo "<script>console.log('" . json_encode($codinventario) . "')</script>";
		echo "<script>console.log('" . json_encode($precio) . "')</script>";
		echo "<script>console.log('" . json_encode($costo) . "')</script>";
		// die();
		/***/

		$status 	= "";
		$codigoexcel	= "";

		if(strlen($codlinea) > 0 && strlen($marca) > 0 && strlen($codproducto) > 0 && strlen($producto) > 0 && strlen($codunidad) > 0 && strlen($codinventario) > 0 && strlen($precio) > 0 && strlen($costo) > 0){

			for ($i = 2; $i <= ($resultados); $i++) {

				/* VALIDACIONES DE REGISTROS DE EXCEL */								
			
				$codlinea	= $data->sheets[0]['cells'][$i][1];
				$marca		= $data->sheets[0]['cells'][$i][3];
				$codproducto	= $data->sheets[0]['cells'][$i][4];
				$producto	= $data->sheets[0]['cells'][$i][5];
				$codunidad	= $data->sheets[0]['cells'][$i][6];
				$codinventario	= $data->sheets[0]['cells'][$i][8];
				$precio		= $data->sheets[0]['cells'][$i][10];
				$costo		= $data->sheets[0]['cells'][$i][11];

				$datos = ItemsModel::ValidarRegistrosExcel($codproducto, $codlinea, $marca, $codunidad, $codinventario);

				$color = ($i%2 == 0 ? ' grid_detalle_par' : ' grid_detalle_impar');

				if($codigoexcel == $codproducto){

					$colorletra 		= "red";
					$colorlinea 		= "red";
					$colormarca 		= "red";
					$colorunidad 		= "red";
					$colorinventario 	= "red";
					$status			= "DUPLICADO";

				}elseif($datos[0]['articulo'] == "0"){

					$status 		= "NUEVO";
					$colorletra 		= "blue";
					$colorlinea 		= "blue";
					$colormarca 		= "blue";
					$colorunidad 		= "blue";
					$colorinventario 	= "blue";

					if($datos[0]['linea'] == NULL){
						$colorlinea		= "red";
						$datos[0]['linea'] 	= "SIN LINEA";
					}

					if($datos[0]['marca'] == NULL){
						$colormarca 		= "red";
						$datos[0]['marca'] 	= "SIN MARCA";
					}

					if($datos[0]['unidad'] == NULL){
						$codunidad 		= "ERROR: ".$codunidad;
						$colorunidad 		= "red";
						$datos[0]['unidad'] 	= "SIN UNIDAD MEDIDA";
					}

					if($datos[0]['inventario'] == NULL){
						$colorinventario 	= "red";
						$datos[0]['inventario'] = "SIN UNBICACION INVENTARIO";
					}

					$procesar 	= true;

				}else{
					$status 		= "EXISTE";
					$colorletra 		= "red";
					$colorlinea 		= "red";
					$colormarca 		= "red";
					$colorunidad 		= "red";
					$colorinventario 	= "red";
				}

				$result .= '<tr>';
				$result .= '<td class="'.$color.'" align = "center"><p style="color:'.$colorletra.';">' . htmlentities($codlinea) . '</td>';
				$result .= '<td class="'.$color.'" align = "left"><p style="color:'.$colorlinea.';">' . htmlentities($datos[0]['linea']) . '</td>';
				$result .= '<td class="'.$color.'" align = "left"><p style="color:'.$colormarca.';">' . htmlentities($datos[0]['marca']) . '</td>';
				$result .= '<td class="'.$color.'" align = "center"><p style="color:'.$colorletra.';">' . htmlentities($codproducto) . '</td>';
				$result .= '<td class="'.$color.'" align = "left"><p style="color:'.$colorletra.';">' . htmlentities($producto) . '</td>';
				$result .= '<td class="'.$color.'" align = "center"><p style="color:'.$colorunidad.';">' . htmlentities($codunidad) . '</td>';
				$result .= '<td class="'.$color.'" align = "left"><p style="color:'.$colorunidad.';">' . htmlentities($datos[0]['unidad']) . '</td>';
				$result .= '<td class="'.$color.'" align = "center"><p style="color:'.$colorletra.';">' . htmlentities($codinventario) . '</td>';
				$result .= '<td class="'.$color.'" align = "left"><p style="color:'.$colorinventario.';">' . htmlentities($datos[0]['inventario']) . '</td>';
				$result .= '<td class="'.$color.'" align = "right"><p style="color:'.$colorletra.';">' . htmlentities($precio) . '</td>';
				$result .= '<td class="'.$color.'" align = "right"><p style="color:'.$colorletra.';">' . htmlentities($costo) . '</td>';
				$result .= '<td class="'.$color.'" align = "center"><p style="color:'.$colorletra.';">' . htmlentities($status) . '</td>';
				$result .= '</tr>';

				$codigoexcel = $codproducto;

			}

		

		} else {

			if(empty($codlinea))
				$msgerror = "<br> * Codigo de Linea vacio";
			
			if(empty($marca))
				$msgerror .= "<br> * Marca vacia";

			if(empty($codproducto))
				$msgerror .= "<br> * Codigo de Producto vacio";

			if(empty($producto))
				$msgerror .= "<br> * Nombre de Producto vacio";

			if(empty($codunidad))
				$msgerror .= "<br> * Codigo de Unidad vacia";

			if(empty($codinventario))
				$msgerror .= "<br> * Codigo de Inventario vacio";

			if(empty($precio))
				$msgerror .= "<br> * Precio de Producto vacio";

			if(empty($costo))
				$msgerror .= "<br> * Costo Unitario de Producto vacio";

			$result .= '<tr bgcolor="">';

			$result .= '	<td colspan="12" align="center" class="'.$color.'" >
						<p align="left" style="font-size:12px; color:red;">ERROR: <br>'.$msgerror.'
					</td>';

			$result .= '</tr>';
			$procesar = false;

		}

		if($procesar){
			$result .= '<tr bgcolor="F3FAF5">';
			$result .= '<td colspan="12" align="right">';
			$result .= '<A href="control.php?rqst=MAESTROS.ITEMS&action=Upload&filename='.$filename.'" target="control"><button><img src="/sistemaweb/icons/importar_excel.png" align="right" />Procesar Lista</button></A></td>';
			$result .= '</tr>';
		} else {
			$result .= '<tr bgcolor="F3FAF5">';
			$result .= '<td colspan="12" align="right">';
			$result .= '<button name="action" type="button" value="Regresar" onclick="regresarMaesItems()"><img src="/sistemaweb/icons/atra.gif" align="right" />Regresar</button>';
			$result .= '</tr>';
		}


		$result .= '</table>';
		
		return $result;


    	}

    	function formModificar($item, $lineas, $tipos, $marcas, $plus, $unidades, $ubicaciones, $Impuestos, $listas) {

		$sino		= Array("S" => "Si", "N" => "No");
		$MarcasCB 	= VariosModel::MarcasItemsCBArray();
		$ItemsSKU 	= ItemsModel::ObtieneItemSKU();
		$unidad_prese 	= ItemsModel::ObtieneTablaGeneral('35');

		$producto = ItemsModel::ObtieneItem($item);
        $l_descs = ItemsModel::ObtieneTablaGeneral("LPRE");

		if ($_REQUEST['cod']) {
		    $ValCbTipos = $_REQUEST['cod'];
		} elseif ($_REQUEST['combotipos'] && !$_REQUEST['cod']) {
		    $ValCbTipos = $_REQUEST['combotipos'];
		} else {
		    $ValCbTipos = trim($item['art_tipo']);
		}

		foreach ($_REQUEST as $llave => $valor) {

		    if ($llave != 'sku' && $llave != 'impuesto' && $valor == 'all') {
		        $disabled = 'disabled';
		    }

		}

        $form = new form('', "Modificar", FORM_METHOD_POST, "control.php", '', "control");
        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("codigo", $item['art_codigo']));
        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MAESTROS.ITEMS"));
        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("action", "Modificar.Grabar"));
        $form->addGroup("GRUPO_GENERAL", "DATOS GENERALES");

        $form->addElement("GRUPO_GENERAL", new form_element_freeTags('<TABLE border="0" cellspacing="2" cellpadding="2"><tr><td class="form_label">'));

        $form->addElement("GRUPO_GENERAL", new form_element_freeTags('Codigo' . espacios(23) . '</td><td class="form_label">: <b>' . trim($item['art_codigo']) . '</b>'));
        $form->addElement("GRUPO_GENERAL", new form_element_freeTags('</td></tr><tr><td>'));

        $form->addElement("GRUPO_GENERAL", new form_element_text('Descripcion' . espacios(15) . '</td><td>:', "txtdescripcion", trim($item['art_descripcion']), "", "", 115, 100, false, 'onchange="javascript:rellenarCampo(this,forms[0].txtdescbreve);" onkeyup="javascript:this.value=this.value.toUpperCase();"'));
        $form->addElement("GRUPO_GENERAL", new form_element_freeTags('</td></tr><tr><td>'));

        $form->addElement("GRUPO_GENERAL", new form_element_text('Descripcion breve' . espacios(4) . '</td><td>:', "txtdescbreve", trim($item['art_descbreve']), "", "", 30, 20, false, ""));
        $form->addElement("GRUPO_GENERAL", new form_element_freeTags('</td></tr><tr><td>'));

        $form->addElement("GRUPO_GENERAL", new form_element_combo("Tipo" . espacios(29) . "</td><td>:", "combotipos", $ValCbTipos, "", "", 1, $tipos, false, 'onChange="javascript:getTipoLinea2(forms[0].codigo.value,this.options[this.selectedIndex].value)"'));
        $form->addElement("GRUPO_GENERAL", new form_element_freeTags('</td></tr><tr><td>'));

        $form->addElement("GRUPO_GENERAL", new form_element_combo("Linea" . espacios(26) . "</td><td>:", "combolineas", $_REQUEST['combolineas'] ? $_REQUEST['combolineas'] : $item['art_linea'], "", "", 1, $lineas, false, ""));
        $form->addElement("GRUPO_GENERAL", new form_element_freeTags('</td></tr><tr><td>'));

        $form->addElement("GRUPO_GENERAL", new form_element_combo("Marca" . espacios(25) . "</td><td>:", "combomarcas", $_REQUEST['combomarcas'] ? $_REQUEST['combomarcas'] : $item['art_clase'], "", "", 1, $MarcasCB, false, ""));
        $form->addElement("GRUPO_GENERAL", new form_element_freeTags('</td></tr><tr><td>'));

        $form->addElement("GRUPO_GENERAL", new form_element_combo("Tipo PLU" . espacios(20) . "</td><td>:", "comboplu", $_REQUEST['comboplu'] ? $_REQUEST['comboplu'] : $item['art_plutipo'], "", "", 1, $plus, false, 'onchange="pluUpdate(this.value)"'));
        $form->addElement("GRUPO_GENERAL", new form_element_freeTags('</td></tr><tr><td>'));

        $form->addElement("GRUPO_GENERAL", new form_element_combo("C&oacute;digo de Impuesto</td><td>:", "impuesto", $_REQUEST['impuesto'] ? $_REQUEST['impuesto'] : $item['art_impuesto1'], "", '', 1, $Impuestos, false, ''));
        $form->addElement("GRUPO_GENERAL", new form_element_freeTags('</td></tr><tr><td>'));

        $form->addElement("GRUPO_GENERAL", new form_element_combo("Codigo SKU" . espacios(14) . "</td><td>:", "sku", $_REQUEST['sku'] ? $_REQUEST['sku'] : $item['art_cod_sku'], "", '', 1, $ItemsSKU, false, ''));
        $form->addElement("GRUPO_GENERAL", new form_element_freeTags('</td></tr><tr><td>'));

        $form->addElement("GRUPO_GENERAL", new form_element_freeTags('</td><td>'));
        $form->addElement("GRUPO_GENERAL", new form_element_freeTags('</td></tr><tr><td>'));

        $form->addElement("GRUPO_GENERAL", new form_element_combo("Activo" . espacios(26) . "</td><td>:", "activo", $_REQUEST['activo'] ? $_REQUEST['activo'] : ($item['art_estado'] == "0" ? "S" : "N"), "", "", 1, $sino, false, ""));

        $form->addElement("GRUPO_GENERAL", new form_element_freeTags('</td></tr></table>'));

        $form->addGroup("GRUPO_ESTANDARD", "ITEM ESTANDARD", $item['art_plutipo'] == 1 || $item['art_plutipo'] == 3 ? "inline" : "inline");

        $form->addElement("GRUPO_ESTANDARD", new form_element_freeTags('<table border="0" cellspacing="3" cellpadding="3"><tr><td>'));

        $form->addElement("GRUPO_ESTANDARD", new form_element_combo("Unid. de medida" . espacios(10) . "</td><td>:", "combounidades", $_REQUEST['combounidades'] ? $_REQUEST['combounidades'] : trim($item['art_unidad']), "", "", 1, $unidades, false, ""));
        $form->addElement("GRUPO_ESTANDARD", new form_element_freeTags('</td></tr><tr><td>'));

        $form->addElement("GRUPO_ESTANDARD", new form_element_combo("Unid. de Presentaci&oacute;n</td><td>:", "art_presentacion", $_REQUEST['art_presentacion'] ? $_REQUEST['art_presentacion'] : trim($item['art_presentacion']), "", "", 1, $unidad_prese, false, ""));
        $form->addElement("GRUPO_ESTANDARD", new form_element_freeTags('</td></tr><tr><td>'));

        $form->addElement("GRUPO_ESTANDARD", new form_element_combo("Ubicaci&oacute;n" . espacios(22) . "</td><td>:", "comboubicaciones", $_REQUEST['comboubicaciones'] ? $_REQUEST['comboubicaciones'] : trim($item['art_cod_ubicac']), "", "", 1, $ubicaciones, false, ""));
        $form->addElement("GRUPO_ESTANDARD", new form_element_freeTags('</td></tr><tr><td>'));

        $form->addElement("GRUPO_ESTANDARD", new form_element_freeTags('</td><td>'));
        $form->addElement("GRUPO_ESTANDARD", new form_element_freeTags('</td></tr><tr><td>'));

        $form->addElement("GRUPO_ESTANDARD", new form_element_text("Plazo de reposicion promedio</td><td>:", "plzreposicion", $_REQUEST['plzreposicion'] ? $_REQUEST['plzreposicion'] : $item['art_plazoreposicprom'], "", "", 10, 20, false, ""));
        $form->addElement("GRUPO_ESTANDARD", new form_element_freeTags('</td></tr><tr><td>'));

        $form->addElement("GRUPO_ESTANDARD", new form_element_text("Dias de posici&oacute;n" . espacios(24) . "</td><td>:", "diasreposicion", $_REQUEST['diasreposicion'] ? $_REQUEST['diasreposicion'] : $item['art_diasreposic'], "", "", 10, 20, false, ""));
        $form->addElement("GRUPO_ESTANDARD", new form_element_freeTags('</td></tr><tr><td>'));


        $form->addElement("GRUPO_ESTANDARD", new form_element_text("Costo Inicial" . espacios(32) . "</td><td>:", "art_costoinicial", $_REQUEST['art_costoinicial'] ? $_REQUEST['art_costoinicial'] : $item['art_costoinicial'], "", '', 12, 10, false, ""));
        $form->addElement("GRUPO_ESTANDARD", new form_element_freeTags('</td></tr><tr><td>'));

        $form->addElement("GRUPO_ESTANDARD", new form_element_text("Costo de Reposici&oacute;n" . espacios(17) . "</td><td>:", "art_costoreposicion", $_REQUEST['art_costoreposicion'] ? $_REQUEST['art_costoreposicion'] : $item['art_costoreposicion'], "", "", 12, 10, true, ""));
        $form->addElement("GRUPO_ESTANDARD", new form_element_freeTags('</td></tr><tr><td>'));

        //$form->addElement("GRUPO_ESTANDARD", new form_element_text("Precio de Venta" . espacios(8) . "</td><td>:", "precio",$_REQUEST['precio'] ? $_REQUEST['precio'] : $item['pre_precio_act1'], "", '', 10, 10, false, ''));
        $form->addElement("GRUPO_ESTANDARD", new form_element_freeTags('</td></tr></table>'));



        $form->addGroup("GRUPO_PRECIO", "LISTA DE PRECIOS");

        $form->addElement("GRUPO_PRECIO", new form_element_freeTags('
		<table border="1"><tbody><tr>
		<th> Codigo </th>
		<th> Lista de Precios </th>
		<th> Precio </th></tr>'));

        $i=0;
        foreach ($listas as $precio) {
        	$form->addElement("GRUPO_PRECIO", new form_element_freeTags('<tr>'));
        	$form->addElement("GRUPO_PRECIO", new form_element_freeTags('<th>&nbsp;&nbsp;&nbsp;'));
        	$form->addElement("GRUPO_PRECIO", new form_element_text("", "listacod[$i]", $precio['pre_lista'], "", "", 10, 10, true, ""));
        	$form->addElement("GRUPO_PRECIO", new form_element_freeTags('&nbsp;</th>'));

        	$form->addElement("GRUPO_PRECIO", new form_element_freeTags('<th>&nbsp;&nbsp;' . $precio['tab_descr'] . '&nbsp;&nbsp;</th> '));
        	$form->addElement("GRUPO_PRECIO", new form_element_freeTags('<th>&nbsp;&nbsp;&nbsp;'));
        	$form->addElement("GRUPO_PRECIO", new form_element_text("", "listaprecio[$i]", $precio['pre_precio'], "", "", 10, 10, false, ""));
        	$form->addElement("GRUPO_PRECIO", new form_element_freeTags('&nbsp;</th>'));
        	$form->addElement("GRUPO_PRECIO", new form_element_freeTags('</tr>'));
        	$i++;
        }

        $form->addElement("GRUPO_PRECIO", new form_element_freeTags('</table>'));


        $form->addGroup("GRUPO_BOTONES", "");
        $form->addElement("GRUPO_BOTONES", new form_element_submit("sbmt", "Guardar", '', '', 20));

        $form->addElement("GRUPO_BOTONES", new form_element_button("btEnlaces", "Enlaces", '', '', 20, 'onclick="openEnlaces()"', false, $item['art_plutipo'] == 1 || $item['art_plutipo'] == 3 ? "none" : "inline"));
        //$form->addElement("GRUPO_BOTONES", new form_element_button("btPrecios", "Listas de precios", '', '', 20, 'onclick="openListaPrecios()"', false, ""));

        $form->addElement("GRUPO_BOTONES", new form_element_button("btnAlias", "Alias", '', '', 20, 'onclick="openAlias()"', false, "inline"));

        $form->addElement("GRUPO_BOTONES", new form_element_button("btRegresar", "Regresar", '', '', 20, 'onclick="regresarMaesItems()" ' . $disabled . '', false));
        return $form->getForm();

    }

    function mostrarError($valor, $funcion) {

        $result = "" . $_REQUEST['manual'];

        if ($valor) {
            switch ($funcion) {

			case "Modificar.Grabar":
				$result .= "<center><b>Actualizacion de item correcta.</b></center>";
			break;

            default:
               	$result .= "<center><b>La funcion se realizo correctamente.</b></center>";
        	}

        } else {

            switch ($funcion) {
                case "Modificar.Grabar":
                    switch ($_REQUEST['combotipos']) {
                        case "all":
                            $result .= "<center style='color:#FF1601;'><blink>Error Ingresando, debe seleccionar un Tipo de Linea</blink></center>";
                            break;
                        default:
                            break;
                    }

                    switch ($_REQUEST['combolineas']) {
                        case "all":
                            $result .= "<center style='color:#FF1601;'><blink>Error Ingresando, debe seleccionar la Linea</blink></center>";
                            break;
                        default:
                            break;
                    }

                    switch ($_REQUEST['combomarcas']) {
                        case "all":
                            $result .= "<center style='color:#FF1601;'><blink>Error Ingresando, debe seleccionar la Marca</blink></center>";
                            break;
                        default:
                            break;
                    }

                    switch ($_REQUEST['combounidades']) {
                        case "all":
                            $result .= "<center style='color:#FF1601;'><blink>Error Ingresando, debe seleccionar la Unidad.</blink></center>";
                            break;
                        default:
                            break;
                    }

                    switch ($_REQUEST['art_presentacion']) {
                        case "all":
                            $result .= "<center style='color:#FF1601;'><blink>Error Ingresando, debe seleccionar la Unid. Presentacion.</blink></center>";
                            break;
                        default:
                            break;
                    }

                    switch ($_REQUEST['comboubicaciones']) {
                        case "all":
                            $result .= "<center style='color:#FF1601;'><blink>Error Ingresando, debe seleccionar la Ubicaci&oacute;n</blink></center>";
                            break;
                        default:
                            break;
                    }

                    break;

                case "Agregar.Action":

                    switch ($_REQUEST['tipo']) {
                        case "all":
                            $result .= "<center style='color:#FF1601;'><blink>Error Ingresando, debe seleccionar un Tipo de Linea</blink></center>";
                            break;
                        default:
                            break;
                    }

                    switch ($_REQUEST['linea']) {
                        case "all":
                            $result .= "<center style='color:#FF1601;'><blink>Error Ingresando, debe seleccionar la Linea</blink></center>";
                            break;
                        default:
                            break;
                    }

                    switch ($_REQUEST['marca']) {
                        case "all":
                            $result .= "<center style='color:#FF1601;'><blink>Error Ingresando, debe seleccionar la Marca</blink></center>";
                            break;
                        default:
                            break;
                    }

                    /* switch ($_REQUEST['sku']) {
                      case "all":
                      $result .= "<center style='color:#FF1601;'><blink>Error Ingresando, debe seleccionar el C&oacute;digo SKU.</blink></center>";
                      break;
                      default:
                      break;
                      } */

                    switch ($_REQUEST['unidad']) {
                        case "all":
                            $result .= "<center style='color:#FF1601;'><blink>Error Ingresando, debe seleccionar la Unidad.</blink></center>";
                            break;
                        default:
                            break;
                    }

                    switch ($_REQUEST['art_presentacion']) {
                        case "all":
                            $result .= "<center style='color:#FF1601;'><blink>Error Ingresando, debe seleccionar la Unid. Presentacion.</blink></center>";
                            break;
                        default:
                            break;
                    }

                    switch ($_REQUEST['ubicacion']) {
                        case "all":
                            $result .= "<center style='color:#FF1601;'><blink>Error Ingresando, debe seleccionar la Ubicaci&oacute;n</blink></center>";
                            break;
                        default:
                            break;
                    }

                    break;

                default:
                    $result .= "<center><b>Error generico</b></center>";
                    break;
            }
        }

        return $result;
    }

    function enlacesPrincipal($item) {
        $result = "Codigo: " . htmlentities($item['art_codigo']) . "<br>";
        $result .= "Descripcion: " . htmlentities($item['art_descripcion']) . "<br>";

        return $result;
    }

    function enlacesItems($item, $_codigo) {
        $result = '<form name="enlaces" method="POST" target="control" action="control.php">';
        $result .= "<center><table border=1><caption>Enlaces para el item</caption><tbody>";
        $result .= "<tr><th>Codigo</th><th>Descripcion</th><th>Cantidad</th><th>&nbsp;</th></tr>";

        foreach ($item as $codigo => $data) {
            $result .= "<tr><td><a href=\"control.php?rqst=MAESTROS.ITEMS&action=Enlaces.Forms&method=Modificar&codigo=" . htmlentities($_codigo) . "&enlace=" . htmlentities($codigo) . "&cantidad=" . htmlentities($data['cantidad']) . "\" target=\"control\">" . htmlentities($codigo) . "</a></td>";
            $result .= "<td>" . htmlentities($data['descripcion']) . "</td>";
            $result .= "<td>" . htmlentities($data['cantidad']) . "</td>";
            $result .= '<td><input type="checkbox" name="checks[]" value="' . htmlentities($codigo) . '"></td>';
            $result .= "</tr>";
        }

        if (count($item) == 0)
            $result .= '<td colspan="4" align="center">No hay enlaces</td>';
        $result .= "</tbody></table>";

        $result .= '<input type="hidden" name="rqst" value="MAESTROS.ITEMS">';
        $result .= '<input type="hidden" name="action" id="action" value="Enlaces.Forms">';
        $result .= '<input type="hidden" name="codigo" value="' . htmlentities($_codigo) . '">';
        $result .= '<input type="submit" name="method" value="Agregar">';
        $result .= '<input type="submit" name="method" value="Borrar" onclick="borrarEnlaces()">';

        $result .= "</center></form>";

        return $result;
    }

    function enlacesControles() {
        $result = "";
        $result .= '<input type="button" name="Terminar" value="Terminar" onclick="enlacesCerrar()">';

        return $result;
    }

    function formEnlaceAgregar($codigo) {
        $form = new form('', "enlaceAgregar", FORM_METHOD_POST, "control.php", '', "control");

        $form->addGroup("GRUPO_PRINCIPAL", "");
        $form->addElement("GRUPO_PRINCIPAL", new form_element_text("Codigo: ", "enlace", '', '<br>', '', 13, 13, false, 'onblur="updateDescripcion(this)" onchange="updateDescripcion(this)"'));
        $form->addElement("GRUPO_PRINCIPAL", new form_element_text("Cantidad: ", "cantidad", '', '<br>', '', 4, 16, false, ""));
        $form->addGroup("GRUPO_AYUDA", "Ayuda", "none");
        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MAESTROS.ITEMS"));
        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("action", "Enlaces.Action"));
        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("codigo", htmlentities($codigo)));
        $form->addGroup("GRUPO_BOTONES", "");
        $form->addElement("GRUPO_BOTONES", new form_element_submit("method", "Agregar", '', '', 20));
        $form->addElement("GRUPO_BOTONES", new form_element_button("btRegresar", "Regresar", '', '', 20, 'onclick="regresarEnlace(\'' . $codigo . '\')"', false));

        $result = $form->getForm();
        $result .= '<div id="ayuda">&nbsp;</div>';

        return $form->getForm();
    }

    function formEnlaceModificar($codigo, $item, $cantidad) {
        $form = new form('', "enlaceModificar", FORM_METHOD_POST, "control.php", '', "control");
        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MAESTROS.ITEMS"));
        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("action", "Enlaces.Action"));
        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("codigo", htmlentities($codigo)));
        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("enlace", htmlentities($item['art_codigo'])));
        $form->addElement(FORM_GROUP_MAIN, new form_element_onlytext('', "Codigo: " . $item['art_codigo'], "<br>"));
        $form->addElement(FORM_GROUP_MAIN, new form_element_onlytext('', $item['art_descripcion'], "<br>"));
        $form->addElement(FORM_GROUP_MAIN, new form_element_text("Cantidad:", "cantidad", htmlentities($cantidad), "<br>", '', 4, 16, false, ""));
        $form->addElement(FORM_GROUP_MAIN, new form_element_submit("method", "Modificar", '', '', 20));
        $form->addElement(FORM_GROUP_MAIN, new form_element_button("btRegresar", "Regresar", '', '', 20, 'onclick="regresarEnlace(\'' . $codigo . '\')"', false));

        return $form->getForm();
    }

    /**
     * Formulario: Alias
     * tabla: int_articulos_alias
     * campos: art_codigo (int_articulos) y codigo_alias
     * Acciones: Ver, modificar, agregar y eliminar
     */
    function aliasPrincipal($item) {
        $result = "Codigo: " . htmlentities($item['art_codigo']) . "<br>";
        $result .= "Descripcion: " . htmlentities($item['art_descripcion']) . "<br>";

        return $result;
    }

    function aliasItems($item, $_codigo) {
        $result = '<form name="alias" method="POST" target="control" action="control.php">';
        $result .= "<center><table border=1><caption>Alias para el item</caption><tbody>";
        $result .= "<tr><th>Codigo Alias</th><th>&nbsp;</th></tr>";

        foreach ($item as $codigo => $data) {
            $result .= "<tr>";
            $result .= "<td>" . htmlentities($data['codigo_alias_item']) . "</td>";
            $result .= '<td><input type="checkbox" name="checks[]" value="' . htmlentities($codigo) . '"></td>';
            $result .= "</tr>";
        }

        if (count($item) == 0)
            $result .= '<td colspan="3" align="center">No hay alias de items</td>';
        $result .= "</tbody></table>";

        $result .= '<input type="hidden" name="rqst" value="MAESTROS.ITEMS">';
        $result .= '<input type="hidden" name="action" id="action" value="Alias.Forms">';
        $result .= '<input type="hidden" name="codigo" value="' . htmlentities($_codigo) . '">';
        $result .= '<input type="submit" name="method" value="Agregar">';
        $result .= '<input type="submit" name="method" value="Borrar" onclick="borrarAlias()">';

        $result .= "</center></form>";
        return $result;
    }

    function aliasControles() {
        $result = "";
        $result .= '<input type="button" name="Terminar" value="Terminar" onclick="enlacesCerrar()">';

        return $result;
    }

    function formAliasAgregar($codigo) {
        $form = new form('', "aliasAgregar", FORM_METHOD_POST, "control.php", '', "control");

        $form->addGroup("GRUPO_PRINCIPAL", "");
        $form->addElement("GRUPO_PRINCIPAL", new form_element_text("Codigo: ", "enlace", '', '<br>', '', 13, 13, false, 'onblur="updateDescripcion(this)" onchange="updateDescripcion(this)"'));
        $form->addGroup("GRUPO_AYUDA", "Ayuda", "none");
        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MAESTROS.ITEMS"));
        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("action", "Alias.Action"));
        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("codigo", htmlentities($codigo)));
        $form->addGroup("GRUPO_BOTONES", "");
        $form->addElement("GRUPO_BOTONES", new form_element_submit("method", "Agregar", '', '', 20));
        $form->addElement("GRUPO_BOTONES", new form_element_button("btRegresar", "Regresar", '', '', 20, 'onclick="regresarAlias(\'' . $codigo . '\')"', false));

        $result = $form->getForm();
        $result .= '<div id="ayuda">&nbsp;</div>';

        return $form->getForm();
    }

    function formAliasModificar($codigo, $item, $cantidad) {
        $form = new form('', "aliasModificar", FORM_METHOD_POST, "control.php", '', "control");
        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MAESTROS.ITEMS"));
        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("action", "Alias.Action"));
        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("codigo", htmlentities($codigo)));
        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("enlace", htmlentities($item['art_codigo'])));
        $form->addElement(FORM_GROUP_MAIN, new form_element_onlytext('', "Codigo: " . $item['art_codigo'], "<br>"));
        $form->addElement(FORM_GROUP_MAIN, new form_element_onlytext('', $item['art_descripcion'], "<br>"));
        $form->addElement(FORM_GROUP_MAIN, new form_element_submit("method", "Modificar", '', '', 20));
        $form->addElement(FORM_GROUP_MAIN, new form_element_button("btRegresar", "Regresar", '', '', 20, 'onclick="regresarAlias(\'' . $codigo . '\')"', false));

        return $form->getForm();
    }

    function enviarMargenLinea($margen) {
        return '<script language="JavaScript">parent.document.getElementsByName(\'margen_linea\')[0].value=' . (1 + ($margen / 100)) . ';</script>';
    }

    function formAgregar($tipos, $lineas, $plus, $unidades, $ubicaciones, $Impuestos, $CodManual, $listas) {
        $sino = Array("S" => "Si", "N" => "No");
        $codigo = str_pad($_REQUEST['codigo'], 13, "0", STR_PAD_LEFT);

        $MarcasCB = VariosModel::MarcasItemsCBArray();
        $ItemsSKU = ItemsModel::ObtieneItemSKU();
        $unidad_prese = ItemsModel::ObtieneTablaGeneral('35');

        foreach ($_REQUEST as $llave => $valor) {
            //echo "llave : $llave = VALOR : $valor \n";
        }

        foreach ($_REQUEST as $llave => $valor) {
            if ($llave != 'sku' && $llave != 'impuesto' && $valor == 'all') {
                $disabled = 'disabled';
            }
        }

        $form = new form('', "itemAgregar", FORM_METHOD_POST, "control.php", '', "control");

        if (ItemsModel::obtieneItem($codigo) != null && $codigo != "0000000000000") {
            $form->addElement(FORM_GROUP_MAIN, new form_element_onlytext('', "El c&oacute;digo especificado ya existe", "<br>", "align:center"));
            $codigo = "0000000000000";
        }

        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MAESTROS.ITEMS"));
        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("margen_linea", "0"));

        if ($codigo != "0000000000000") {
            $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("action", "Agregar.Action"));
            $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("codigo", htmlentities($codigo)));
            if ($_REQUEST['manual'] == '1' || $CodManual == 'Si') {
                $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("manual", "si"));
            } else {
                $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("manual", "no"));
            }
        } else
            $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("action", "Agregar"));

        if($CodManual=='si')
        	$CodManual='';

        $form->addGroup("GRUPO_CODIGO", $codigo == "0000000000000" ? "INGRESE NUEVO CODIGO" : "AGREGAR ITEM");

        if ($codigo != "0000000000000") {
            $form->addElement("GRUPO_CODIGO", new form_element_freeTags('<table border="0" cellspacing="2" cellpadding="2"> <tr><td>'));
            $form->addElement("GRUPO_CODIGO", new form_element_freeTags('<span class="form_label">Codigo ' . espacios(2) . ': <b>' . trim($codigo) . '</b></span>'));
            $form->addElement("GRUPO_CODIGO", new form_element_freeTags('</td></tr></table>'));
        } else {
            $form->addElement("GRUPO_CODIGO", new form_element_freeTags('<table border="0" cellspacing="2" cellpadding="2"> <tr><td>'));
            //$form->addElement("GRUPO_CODIGO", new form_element_text("Codigo:", "codigo", $CodManual, "", '', 20, 13, false, ''));
			$form->addElement("GRUPO_CODIGO", new form_element_text("Codigo:", "codigo", $CodManual, "", '', 20, 13, false, 'onkeyup="javascript:setNumerosLetras(\'codigo\');"'));
            $form->addElement("GRUPO_CODIGO", new form_element_freeTags('</td></tr><tr><td>'));
            $form->addElement("GRUPO_CODIGO", new form_element_checkbox('C&oacute;digo Manual :', 'manual', $_REQUEST['check'], '', '', 'onClick="DevuelveCodManual(this,document.getElementsByName(\'codigo\')[0])"'));
            $form->addElement("GRUPO_CODIGO", new form_element_freeTags('</td></tr></table>'));
        }

        if ($codigo != "0000000000000") {
            $form->addGroup("GRUPO_DATOS", "CONFIGURACION DEL ITEM");
            $form->addElement("GRUPO_DATOS", new form_element_freeTags('<table border="0" cellspacing="2" cellpadding="2"> <tr><td>'));
            $form->addElement("GRUPO_DATOS", new form_element_text("Descripci&oacute;n" . espacios(15) . "</td><td>:", "descripcion",
                            $_REQUEST['descripcion'], "", '', 115, 100, false, 'onchange="javascript:rellenarCampo(this,forms[0].descbreve);" onkeyup="javascript:this.value=this.value.toUpperCase();"'));
            $form->addElement("GRUPO_DATOS", new form_element_freeTags('</td></tr><tr><td>'));

            $form->addElement("GRUPO_DATOS", new form_element_text("Descripci&oacute;n breve" . espacios(4) . "</td><td>:", "descbreve",
                            $_REQUEST['descbreve'], "", '', 25, 20, false, ''));
            $form->addElement("GRUPO_DATOS", new form_element_freeTags('</td></tr><tr><td>'));

            $form->addElement("GRUPO_DATOS", new form_element_combo("TipoL" . espacios(26) . "</td><td>:", "tipo",
                            $_REQUEST['cod'] ? $_REQUEST['cod'] : $_REQUEST['tipo'], "", '', 1, $tipos, false, 'onChange="javascript:getTipoLinea(forms[0].codigo.value,this.options[this.selectedIndex].value,forms[0].descripcion.value,forms[0].descbreve.value,forms[0].manual.value)"'));
            $form->addElement("GRUPO_DATOS", new form_element_freeTags('</td></tr><tr><td>'));

            $form->addElement("GRUPO_DATOS", new form_element_combo("Linea" . espacios(26) . "</td><td>:", "linea",
                            $_REQUEST['linea'], "", '', 1, $lineas, false, 'onblur="control.location.href=\'control.php?rqst=MAESTROS.ITEMS&action=ObtenerMargenLinea&linea=\'+document.getElementsByName(\'linea\')[0].value;"'));
            $form->addElement("GRUPO_DATOS", new form_element_freeTags('</td></tr><tr><td>'));

            $form->addElement("GRUPO_DATOS", new form_element_combo("Marca" . espacios(25) . "</td><td>:", "marca",
                            $_REQUEST['marca'], "", '', 1, $MarcasCB, false, ''));
            $form->addElement("GRUPO_DATOS", new form_element_freeTags('</td></tr><tr><td>'));

            $form->addElement("GRUPO_DATOS", new form_element_combo("Tipo PLU" . espacios(20) . "</td><td>:", "plu",
                            $_REQUEST['plu'], "", '', 1, $plus, false, 'onchange="agregarUpdatePLU(this.value)"'));
            $form->addElement("GRUPO_DATOS", new form_element_freeTags('</td></tr><tr><td>'));

            $form->addElement("GRUPO_DATOS", new form_element_combo("C&oacute;digo de Impuesto" . espacios(0) . "</td><td>:", "impuesto", '000009', "", '', 1, $Impuestos, false, ''));
            $form->addElement("GRUPO_DATOS", new form_element_freeTags('</td></tr><tr><td>'));


            $form->addElement("GRUPO_DATOS", new form_element_combo("C&oacute;digo SKU" . espacios(14) . "</td><td>:", "sku",
                            $_REQUEST['sku'], "", '', 1, $ItemsSKU, false, ''));
            $form->addElement("GRUPO_DATOS", new form_element_freeTags('</td></tr><tr><td>'));

            $form->addElement("GRUPO_DATOS", new form_element_freeTags('</td><td>'));
            $form->addElement("GRUPO_DATOS", new form_element_freeTags('</td></tr><tr><td>'));

            $form->addElement("GRUPO_DATOS", new form_element_combo("Activo" . espacios(26) . "</td><td>:", "activo", "S", "", "", 1, $sino, false, ""));
            $form->addElement("GRUPO_DATOS", new form_element_freeTags('</td></tr></table>'));

            $form->addGroup("GRUPO_ESTANDARD", "DETALLES DEL PRODUCTO");
            $form->addElement("GRUPO_ESTANDARD", new form_element_freeTags('<table border="0" cellspacing="2" cellpadding="2"> <tr><td class="form_label">'));

            $form->addElement("GRUPO_ESTANDARD", new form_element_combo("Unidad de medida" . espacios(10) . "</td><td>:", "unidad",
                            $_REQUEST['unidad'], "", '', 1, $unidades, false, ''));
            $form->addElement("GRUPO_ESTANDARD", new form_element_freeTags('</td></tr><tr><td>'));

            $form->addElement("GRUPO_ESTANDARD", new form_element_combo("Unid. de Presentaci&oacute;n" . espacios(3) . "</td><td>:", "art_presentacion",
                            $_REQUEST['art_presentacion'], "", "", 1, $unidad_prese, false, ""));
            $form->addElement("GRUPO_ESTANDARD", new form_element_freeTags('</td></tr><tr><td>'));

            $form->addElement("GRUPO_ESTANDARD", new form_element_combo("Ubicacion" . espacios(25) . "</td><td>:", "ubicacion",
                            $_REQUEST['ubicacion'], "", '', 1, $ubicaciones, false, ''));
            $form->addElement("GRUPO_ESTANDARD", new form_element_freeTags('</td></tr><tr><td>'));

            $form->addElement("GRUPO_ESTANDARD", new form_element_freeTags('</td><td>'));
            $form->addElement("GRUPO_ESTANDARD", new form_element_freeTags('</td></tr><tr><td>'));

            $form->addElement("GRUPO_ESTANDARD", new form_element_text("Plazo de reposicion promedio" . espacios(0) . "</td><td>:", "reposicion",
                            $_REQUEST['reposicion'], "", '', 15, 20, false, ''));
            $form->addElement("GRUPO_ESTANDARD", new form_element_freeTags('</td></tr><tr><td>'));

            $form->addElement("GRUPO_ESTANDARD", new form_element_text("Dias de reposicion" . espacios(20) . "</td><td>:", "dias",
                            $_REQUEST['dias'], "", '', 15, 20, false, ''));
            $form->addElement("GRUPO_ESTANDARD", new form_element_freeTags('</td></tr><tr><td>'));

            $form->addElement("GRUPO_ESTANDARD", new form_element_text("Costo Inicial" . espacios(31) . "</td><td>:", "art_costoinicial",
                            $_REQUEST['art_costoinicial'], "", '', 12, 10, false, "onblur='document.getElementsByName(\"art_costoreposicion\")[0].value=document.getElementsByName(\"art_costoinicial\")[0].value; document.getElementsByName(\"precio\")[0].value = ((document.getElementsByName(\"art_costoinicial\")[0].value * 1.18) * document.getElementsByName(\"margen_linea\")[0].value).toFixed(2);'"));
            $form->addElement("GRUPO_ESTANDARD", new form_element_freeTags('</td></tr><tr><td>'));

            $form->addElement("GRUPO_ESTANDARD", new form_element_text("Costo de Reposici&oacute;n" . espacios(16) . "</td><td>:", "art_costoreposicion", $_REQUEST['art_costoreposicion'], "", "", 12, 10, false, ""));
            $form->addElement("GRUPO_ESTANDARD", new form_element_freeTags('</td></tr><tr><td>'));

            //$form->addElement("GRUPO_ESTANDARD", new form_element_text("Precio de Venta" . espacios(8) . "</td><td>:", "precio", $_REQUEST['precio'] ? $_REQUEST['precio'] : '0.00', "<br>", '', 10, 10, false, ''));
            $form->addElement("GRUPO_ESTANDARD", new form_element_freeTags('</td></tr></table>'));

            $form->addGroup("GRUPO_PRECIO", "LISTA DE PRECIOS");

	        $form->addElement("GRUPO_PRECIO", new form_element_freeTags('
			<table border="1"><tbody><tr>
			<th> Codigo </th>
			<th> Lista de Precios </th>
			<th> Precio </th></tr>'));

	        $i=0;
	        foreach ($listas as $precio) {
	        	$form->addElement("GRUPO_PRECIO", new form_element_freeTags('<tr>'));
	        	$form->addElement("GRUPO_PRECIO", new form_element_freeTags('<th>&nbsp;&nbsp;&nbsp;'));
	        	$form->addElement("GRUPO_PRECIO", new form_element_text("", "listacod[$i]", $precio['pre_lista'], "", "", 10, 10, true, ""));
	        	$form->addElement("GRUPO_PRECIO", new form_element_freeTags('&nbsp;</th>'));
	        	
	        	$form->addElement("GRUPO_PRECIO", new form_element_freeTags('<th>&nbsp;&nbsp;' . $precio['tab_descr'] . '&nbsp;&nbsp;</th> '));
	        	$form->addElement("GRUPO_PRECIO", new form_element_freeTags('<th>&nbsp;&nbsp;&nbsp;'));
	        	$form->addElement("GRUPO_PRECIO", new form_element_text("", "listaprecio[$i]", $precio['pre_precio'], "", "", 10, 10, false, ""));
	        	$form->addElement("GRUPO_PRECIO", new form_element_freeTags('&nbsp;</th>'));
	        	$form->addElement("GRUPO_PRECIO", new form_element_freeTags('</tr>'));
	        	$i++;
	        }

	        $form->addElement("GRUPO_PRECIO", new form_element_freeTags('</table>'));

        }

        $form->addGroup("GRUPO_BOTONES", "");

        if ($codigo != "0000000000000")
            $form->addElement("GRUPO_BOTONES", new form_element_submit("go", "Guardar", '', '', 20));

        $form->addElement("GRUPO_BOTONES", new form_element_button("btRegresar", "Regresar", espacios(2), '', 20, 'onclick="regresarMaesItems()" ' . $disabled . '', false));
        $form->addElement("GRUPO_BOTONES", new form_element_button("btagrega", "Agregar", '', '', 20, 'onclick="javascript:submit();"', false));

        return $form->getForm();
    }

    function preciosLista($codigo, $listas) {

        $producto = ItemsModel::ObtieneItem($codigo);
        $l_descs = ItemsModel::ObtieneTablaGeneral("LPRE");

        $result = '<form name="lista" method="POST" action="control.php" target="control">';
        $result .= '<input type="hidden" name="rqst" value="MAESTROS.ITEMS">';
        $result .= '<input type="hidden" name="action" value="Precios.Action">';
        $result .= '<input type="hidden" name="method" value="Borrar">';
        $result .= '<input type="hidden" name="codigo" value="' . htmlentities($codigo) . '">';
        $result .= '<table border="1"><caption>Lista de precios<br> ' . htmlentities($producto['art_descripcion']) . '</caption><tbody><tr>';
        $result .= '<th>Codigo</th>';
        $result .= '<th>Lista</th>';
        $result .= '<th>Precio</th>';
        $result .= '<th>&nbsp;</th></tr>';

        foreach ($listas as $cod_lista => $precio) {
            $result .= '<tr><th><a href="control.php?rqst=MAESTROS.ITEMS&action=Precios.Modificar&codigo=' . htmlentities($codigo) . '&lista=' . htmlentities($cod_lista) . '" target="control">' . htmlentities($codigo) . '</th>';
            $result .= '<th>' . htmlentities($l_descs[$cod_lista]) . '</th>';
            $result .= '<th>' . htmlentities($precio) . '</th>';

            if ($cod_lista != "01")
                $result .= '<th><input type="checkbox" name="listas[]" value="' . htmlentities($cod_lista) . '">';
            else
                $result .= '<th>&nbsp;</th>';

            $result .= '</tr>';
        }

        $result .= '</tbody></table>';

        $result .= '<input type="submit" value="Borrar seleccionados" name="submit">';
        $result .= '<input type="button" value="Agregar" name="Agregar" onclick="goAgregarPrecio(\'' . htmlentities($codigo) . '\')">';
        $result .= '</form>';

        return $result;
    }

    function precioModificar($codigo, $lista, $precio) {
        $form = new form('', "precioModificar", FORM_METHOD_POST, "control.php", '', "control");
        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MAESTROS.ITEMS"));
        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("action", "Precios.Action"));
        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("method", "Modificar"));
        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("codigo", $codigo));
        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("lista", $lista));

        $form->addElement(FORM_GROUP_MAIN, new form_element_onlytext('', "Modificar precio", "<br>"));
        $form->addElement(FORM_GROUP_MAIN, new form_element_onlytext('', "Codigo: " . htmlentities($codigo), "<br>"));
        $form->addElement(FORM_GROUP_MAIN, new form_element_onlytext('', "Lista de precios: " . htmlentities($lista), "<br>"));
        $form->addElement(FORM_GROUP_MAIN, new form_element_text("Precio:", "precio", $precio, '<br>', '', 10, 10, false, ''));
        $form->addElement(FORM_GROUP_MAIN, new form_element_submit("submit", "Guardar", '', '', 20));
        $form->addElement(FORM_GROUP_MAIN, new form_element_button("btRegresar", "Regresar", '', '', 20, 'onclick="regresarPrecios(\'' . htmlentities($codigo) . '\')', false));

        return $form->getForm();
    }

    function precioAgregar($codigo, $listas) {
        $form = new form('', "precioAgregar", FORM_METHOD_POST, "control.php", '', "control");
        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MAESTROS.ITEMS"));
        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("action", "Precios.Action"));
        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("method", "Agregar"));
        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("codigo", $codigo));

        $form->addElement(FORM_GROUP_MAIN, new form_element_combo("Lista:", "lista", "", "<br>", '', 1, $listas, false, ''));
        $form->addElement(FORM_GROUP_MAIN, new form_element_text("Precio:", "precio", '', '<br>', '', 10, 10, false, ''));
        $form->addElement(FORM_GROUP_MAIN, new form_element_submit("submit", "Agregar", '', '', 20));
        $form->addElement(FORM_GROUP_MAIN, new form_element_button("btRegresar", "Regresar", '', '', 20, 'onclick="regresarPrecios(\'' . htmlentities($codigo) . '\')', false));

        return $form->getForm();
    }

	function reporteExcel($res, $almacen) {
		$workbook = new Workbook("maestro_articulos.xls");
		$formato0 = & $workbook->add_format();
		$formato1 = & $workbook->add_format();
		$formato2 = & $workbook->add_format();
		$formato3 = & $workbook->add_format();
		$formato4 = & $workbook->add_format();
		$formato5 = & $workbook->add_format();

		$formato0->set_size(11);
		$formato0->set_bold(1);
		$formato0->set_align('left');
		$formato1->set_top(1);
		$formato1->set_left(1);
		$formato1->set_border(0);
		$formato1->set_bold(1);
		$formato2->set_size(10);
		$formato2->set_bold(1);
		$formato2->set_align('center');
		$formato3->set_num_format(2);
		$formato4->set_num_format(2);
		$formato4->set_bold(1);
		$formato5->set_size(11);
		$formato5->set_align('left');


		$worksheet1 = & $workbook->add_worksheet('Hoja de Resultados Items');
		$worksheet1->set_column(0, 0, 15);
		$worksheet1->set_column(1, 1, 30);
		$worksheet1->set_column(2, 2, 15);
		$worksheet1->set_column(3, 3, 40);
		$worksheet1->set_column(4, 4, 15);
		$worksheet1->set_column(5, 5, 10);
		$worksheet1->set_column(6, 6, 20);
		$worksheet1->set_column(7, 7, 10);
		$worksheet1->set_column(8, 8, 20);
		$worksheet1->set_column(9, 9, 10);
		$worksheet1->set_column(10, 10, 10);
		$worksheet1->set_column(11, 10, 10);
		$worksheet1->set_column(12, 10, 10);

		$worksheet1->set_zoom(100);
		$worksheet1->set_landscape(100);

		$worksheet1->write_string(1, 0, "ARTICULOS DEL ALMACEN " . $almacen, $formato0);
		$worksheet1->write_string(2, 0, " ", $formato0);

		$a = 3;

		$worksheet1->write_string($a, 0, "COD. LINEA", $formato2);
		$worksheet1->write_string($a, 1, "LINEA", $formato2);
		$worksheet1->write_string($a, 2, "COD. PRODUCTO", $formato2);
		$worksheet1->write_string($a, 3, "PRODUCTO", $formato2);
		$worksheet1->write_string($a, 4, "TIPO PLU", $formato2);
		$worksheet1->write_string($a, 5, "PRECIO", $formato2);
		$worksheet1->write_string($a, 6, "TIPO", $formato2);
		$worksheet1->write_string($a, 7, "UNIDAD", $formato2);
		$worksheet1->write_string($a, 8, "UBICACION", $formato2);
		$worksheet1->write_string($a, 9, "CODIGO SKU", $formato2);
		$worksheet1->write_string($a, 10, "ACTIVO", $formato2);
		$worksheet1->write_string($a, 11, "STOCK", $formato2);
		$worksheet1->write_string($a, 12, "COSTO", $formato2);

		$a++;

		for ($j = 0; $j < count($res); $j++) {
	    	$worksheet1->write_string($a, 0, $res[$j]['codlinea'], $formato5);
	 		$worksheet1->write_string($a, 1, $res[$j]['linea'], $formato5);
	    	$worksheet1->write_string($a, 2, $res[$j]['codigo'], $formato5);
	    	$worksheet1->write_string($a, 3, $res[$j]['descripcion'], $formato5);
	    	$worksheet1->write_string($a, 4, $res[$j]['notipoproducto'], $formato5);
	    	$worksheet1->write_number($a, 5, number_format($res[$j]['precio'], 2, '.', ''), $formato3);
	    	$worksheet1->write_string($a, 6, $res[$j]['tipo'], $formato5);
	    	$worksheet1->write_string($a, 7, $res[$j]['unidad'], $formato5);
	    	$worksheet1->write_string($a, 8, $res[$j]['ubicacion'], $formato5);
	    	$worksheet1->write_string($a, 9, $res[$j]['codsku'], $formato5);
	    	$worksheet1->write_string($a, 10, $res[$j]['estado'], $formato5);
	    	$worksheet1->write_number($a, 11, number_format($res[$j]['stock'], 2, '.', ''), $formato3);
	    	$worksheet1->write_number($a, 12, number_format($res[$j]['costo'], 2, '.', ''), $formato3);
			$a++;
		}
		$workbook->close();
        header("Location: /sistemaweb/maestros/maestro_articulos.xls");
	}
	
	function gridViewEXCELListaPrecio($arrResponseModelExcelListaPrecio) {
		$workbook = new Workbook("maestro_articulos.xls");
		$formato0 = & $workbook->add_format();
		$formato1 = & $workbook->add_format();
		$formato2 = & $workbook->add_format();
		$formato3 = & $workbook->add_format();
		$formato4 = & $workbook->add_format();
		$formato5 = & $workbook->add_format();

		$formato0->set_size(11);
		$formato0->set_bold(1);
		$formato0->set_align('left');
		$formato1->set_top(1);
		$formato1->set_left(1);
		$formato1->set_border(0);
		$formato1->set_bold(1);
		$formato2->set_size(10);
		$formato2->set_bold(1);
		$formato2->set_align('center');
		$formato3->set_num_format(2);
		$formato4->set_num_format(2);
		$formato4->set_bold(1);
		$formato5->set_size(11);
		$formato5->set_align('left');


		$worksheet1 = & $workbook->add_worksheet('Lista de precios');
		$worksheet1->set_column(0, 0, 35);
		$worksheet1->set_column(1, 1, 20);
		$worksheet1->set_column(2, 2, 80);
		$worksheet1->set_column(3, 3, 15);

		$worksheet1->set_zoom(100);
		$worksheet1->set_landscape(100);

		$worksheet1->write_string(1, 0, "LISTA DE PRECIOS", $formato0);
		$worksheet1->write_string(2, 0, " ", $formato0);

		++$fila;
		$worksheet1->write_string($fila, 0, "EDS LISTA PRECIO", $formato2);
		$worksheet1->write_string($fila, 1, "COD. ITEM", $formato2);
		$worksheet1->write_string($fila, 2, "DESCRIPCION", $formato2);
		$worksheet1->write_string($fila, 3, "PRECIO", $formato2);

		++$fila;
		foreach ($arrResponseModelExcelListaPrecio['arrData'] as $row) {
	    	$worksheet1->write_string($fila, 0, $row['no_lista_precio'], $formato5);
	 		$worksheet1->write_string($fila, 1, $row['nu_codigo_item'], $formato5);
	    	$worksheet1->write_string($fila, 2, $row['no_nombre_item'], $formato5);
	    	$worksheet1->write_number($fila, 3, number_format($row['pre_precio_act1'], 2, '.', ''), $formato3);
			++$fila;
		}
		$workbook->close();
        header("Location: /sistemaweb/maestros/maestro_articulos.xls");
		/*
		$sIdListaPrecio='';
		$sIdItem='';
		ob_end_clean();
		$buff = "LISTA DE PRECIOS \n\n";
		$buff .= "LISTA PRECIO, COD ITEM, DESCRIPCION, PRECIO\n";
		foreach ($arrResponseModelExcelListaPrecio['arrData'] as $row) {
			$buff .= "{$row['no_lista_precio']},{$row['nu_codigo_item']},{$row['no_nombre_item']},{$row['pre_precio_act1']} \n";
			$sIdListaPrecio = $row['id_lista_precio'];
			$sIdItem = $row['nu_codigo_item'];
		}
        header("Content-Type: text/csv; charset=utf-8");
        header("Content-Disposition: attachment; filename=\"ListaPrecio.csv\"");
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
        die($buff);
        */
	}
}

