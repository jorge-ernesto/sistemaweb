<?php
// Fecha de creacion     : Marzo 7, 2012, 5: 00 PM
// Autor                 : Nestor Hernandez Loli
// Fecha de modificacion :
// Modificado por        :

// Clase template del mantenimiento de la tabla  c_pos_descuento_ruc

class PosDescuentoRucTemplate extends Template {
	function titulo() {
		$titulo = '<div align="center"><h2>Descuentos por RUC</h2></div><hr>';
		return $titulo;
	}

	function errorResultado($errores) {
		$msg = "<div style = ''>";
		foreach ($errores as $err) {
			$msg .= $err . "<br>";
		}
		$msg .= "</div>";
		return $msg;
	}

	function listado($registros, $tipo) {
		$columnas = array('Fecha', 'RUC','Nombre', 'Articulo', 'Descuento', 'Estado', 'Tipo');
		$listado = '
		<div id="resultados_grid" class="grid" align="center"><br>
		<table>
			<thead align="center" valign="center" >
				<tr class="grid_header">';
		for ($i = 0; $i < count($columnas); $i++) {
			$listado .= '<th class="grid_cabecera"> ' . strtoupper($columnas[$i]) . '</th>';
		}
		$listado .= '<th>' . espacios(10) . '</th><th>' . espacios(5) . '</th></tr><tbody class="grid_body" style="height:250px;">';
		$a=0;
		foreach ($registros as $reg) {
			$color = ($a%2==0?"grid_detalle_par":"grid_detalle_impar");
			$a++;
			if($tipo=="1" || $tipo=="2" || $tipo=="3" || $tipo=="4") {
				$listado .= '<tr class="grid_row" ' . resaltar('white', '#CDCE9C') . '>';
				$regCod = $reg["pos_descuento_ruc_id"];
				$listado .= '<td class="' . $color . '">' . $reg['fecha'] . '</td>';
				$listado .= '<td class="' . $color . '">' . $reg['ruc'] . '</td>';
				$listado .= '<td class="' . $color . '">' . $reg['cli_razsocial'] . '</td>';
				$listado .= '<td class="' . $color . '">' . $reg['art_codigo'] . '</td>';
				$listado .= '<td class="' . $color . '" align="right">' . $reg['descuento'] . '</td>';
				$listado .= '<td class="' . $color . '">' . (($reg['activo'] == 0) ? "Inactivo" : "Activo") . '</td>';
				$listado .= '<td class="' . $color . '">' . $reg['tipo'] . '</td>';
				$listado .= '<td class="' . $color . '"> <a href="control.php?rqst=MAESTROS.POS_DESCUENTO_RUC&task=POS_DESCUENTO_RUC&action=Modificar&registroid=' . $regCod . '" target="control"><img src="/sistemaweb/icons/open.gif" align="middle" border="0"/></a>&nbsp;';
				$listado .= '<a href="javascript:confirmarLink(\'Deseas borrar el ruc: ' . $reg['ruc']. ' producto: ' . $reg['art_codigo'] . '\',\'control.php?rqst=MAESTROS.POS_DESCUENTO_RUC&task=POS_DESCUENTO_RUC' . '&action=Eliminar&registroid=' . $regCod . '\', \'control\')"><img src="/sistemaweb/icons/delete.gif" alt="Borrar" align="middle" border="0"/></A></td>';
				$listado .= '</tr>';
			} else {
				$listado .= '<tr class="grid_row" ' . resaltar('white', '#CDCE9C') . '>';
				$regCod = $reg["pos_descuento_ruc_id"];
				$listado .= '<td class="' . $color . '">' . $reg['fecha'] . '</td>';
				$listado .= '<td class="' . $color . '">' . $reg['ruc'] . '</td>';
				$listado .= '<td class="' . $color . '">' . $reg['cli_razsocial'] . '</td>';
				$listado .= '<td class="' . $color . '">' . $reg['art_codigo'] . '</td>';
				$listado .= '<td class="' . $color . '" align="right">' . $reg['descuento'] . '</td>';
				$listado .= '<td class="' . $color . '">' . (($reg['activo'] == 0) ? "Inactivo" : "Activo") . '</td>';
				$listado .= '<td class="' . $color . '">' . $reg['tipo'] . '</td>';
				$listado .= '<td class="' . $color . '"> <a href="control.php?rqst=MAESTROS.POS_DESCUENTO_RUC&task=POS_DESCUENTO_RUC&action=Modificar&registroid=' . $regCod . '" target="control"><img src="/sistemaweb/icons/open.gif" align="middle" border="0"/></a>&nbsp;';
				$listado .= '<a href="javascript:confirmarLink(\'Deseas borrar el ruc: ' . $reg['ruc']. ' producto: ' . $reg['art_codigo'] . '\',\'control.php?rqst=MAESTROS.POS_DESCUENTO_RUC&task=POS_DESCUENTO_RUC' . '&action=Eliminar&registroid=' . $regCod . '\', \'control\')"><img src="/sistemaweb/icons/delete.gif" alt="Borrar" align="middle" border="0"/></A></td>';
				$listado .= '</tr>';
			}
		}
		$listado .= '</tbody></table></div>';
		return $listado;
	}

	// Solo Formularios y otros
	function formBuscar($paginacion, $tipo = NULL) {
		$tipos = Array("T"=>"Todos", "1"=>"Nota Despacho", "2"=>"Factura", "3"=>"Tarjeta de Descuento (Boleta)");
		//echo "ENTRO BUSCAR\n";
		$form = new form2('', 'Buscar', FORM_METHOD_GET, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MAESTROS.POS_DESCUENTO_RUC'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'POS_DESCUENTO_RUC'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" align="center">'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Nombre Cliente: '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text('cliente','', '', espacios(2), 50, 48));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Tipo Documento: '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("tipo_doc", "", $tipo, $tipos, ""));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="2" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action', 'Buscar', espacios(3)));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action', 'Agregar', espacios(3)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Buscar"><img src="/sistemaweb/icons/gbuscar.png" align="right" />Buscar</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;&nbsp;<button name="action" type="submit" value="Agregar"><img src="/sistemaweb/icons/gadd.png" align="right" />Agregar</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;&nbsp;<button name="action" type="submit" value="Importar"><img src="/sistemaweb/icons/gexcel.png" align="right" />Importar Descuentos </button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;&nbsp;<button name="action" type="submit" value="Excel"><img src="/sistemaweb/images/excel_icon.png" alt="left" /> Excel</button>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="2" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('P&aacute;gina ' . $paginacion['paginas'] . ' de ' . $paginacion['numero_paginas'] . ' P&aacute;ginas&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/first.gif', espacios(2), array("border" => "0", "alt" => "Primera P&aacute;gina", "onclick" => "javascript:PaginarRegistros('" . $paginacion['pp'] . "','" . $paginacion['primera_pagina'] . "')")));
		$form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/left.gif', espacios(5), array("border" => "0", "alt" => "P&aacute;gina Anterior", "onclick" => "javascript:PaginarRegistros('" . $paginacion['pp'] . "','" . $paginacion['pagina_previa'] . "')")));

		$form->addElement(FORM_GROUP_MAIN, new f2element_text('paginas', '', $paginacion['paginas'], espacios(5), 3, 2, array("onChange" => "javascript:PaginarRegistros('" . $paginacion['pp'] . "',this.value)")));

		$form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/right.gif', espacios(2), array("border" => "0", "alt" => "P&aacute;gina Siguente", "onclick" => "javascript:PaginarRegistros('" . $paginacion['pp'] . "','" . $paginacion['pagina_siguiente'] . "')")));
		$form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/last.gif', espacios(2), array("border" => "0", "alt" => "&Uacute;ltima P&aacute;gina", "onclick" => "javascript:PaginarRegistros('" . $paginacion['pp'] . "','" . $paginacion['ultima_pagina'] . "')")));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text('numero_registros', 'Registros por P&aacute;gina : ', $paginacion['pp'], espacios(2), 4, 4, array("onChange" => "javascript:PaginarRegistros(this.value,'" . $paginacion['primera_pagina'] . "')")));
		return $form->getForm();
	}

	function ImportarDataExcel() {
		$form = new form2('Importar Descuentos Excel 2', 'form_listacompras', FORM_METHOD_POST, 'control.php', '', 'control','enctype="multipart/form-data"');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MAESTROS.POS_DESCUENTO_RUC'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'POS_DESCUENTO_RUC'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br/><table border="0" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="descargarFormatoExcel"><img src="/sistemaweb/icons/gexcel.png" align="right" />Descargar Formato Excel </button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('Seleccionar archivo Excel: <input type="file" name="ubica" id="ubica" size="70" onClick="Mostrar();">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td><div id="ver" style="display:none;">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="ImportarDescuentos"><img src="/sistemaweb/icons/gexcel.png" align="right" />Importar Excel</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</table>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br/>'));
		return $form->getForm();
   }

	function MostrarDataExcel($data, $filename) {
		$result = '';
		$cliente = '';
		$nuproducto = '';
		$norazsocial = '';

		$arrCeldasNombres = $data->sheets[0]['cells'];
		$arrCeldas = $data->sheets[0]['cellsInfo'];

		$nuproducto	= $arrCeldas[1][2]['raw'];
		$notd = $arrCeldasNombres[2][2];

		$notd = strtoupper($notd);

		$color = 'black';

		$producto = PosDescuentoRucModel::ValidarExcel(trim($nuproducto), $notd, NULL);
		$exiteproducto = $producto[0][0];
		$noproducto = $producto[0][1];

		if($exiteproducto == '1')
			$color = 'black';
		else
			$color = 'red';

		if($notd == 'FACTURA')
			$nutd = 2;
		elseif ($notd == 'BOLETA')
			$nutd = 3;
		else//NOTA DESPACHO
			$nutd = 1;

		$result .= '<table align="center" cellspacing="2" cellpadding="2"> ';
		$result .= '<tr>';
		$result .= '<th align="right" style="font-size:0.9em; color:black;background-color: #D9F9B2">Codigo Producto: </th>';
		$result .= '<th colspan="2" align="left" style="font-size:0.9em; color:'.$color.';background-color: #D9F9B2">';
		$result .= $nuproducto;

		$result .= '<tr>';
		$result .= '<th align="right" style="font-size:0.9em; color:black;background-color: #D9F9B2">Nombre Producto: </th>';
		if($noproducto) {
			$result .= '<th colspan="2" align="left" style="font-size:0.9em; color:'.$color.';background-color: #D9F9B2">';
			$result .= $noproducto;
		} else {
			$result .= '<th colspan="2" align="left" style="font-size:0.9em; color:'.$color.';background-color: #D9F9B2">';
			$result .= 'ERROR: NO EXISTE PRODUCTO';
		}

		$result .= '<tr>';
		$result .= '<th align="right" style="font-size:0.9em; color:black; background-color: #D9F9B2">Tipo Documento: </th>';
		$result .= '<th colspan="2" align="left" style="font-size:0.9em; color:'.$color.';background-color: #D9F9B2">';
		$result .= $data->val(2, 2);//NOMBRE Tipo Documento

		$result .= '<tr>';
		$result .= '<th colspan="3" style="font-size:0.9em; color:black;background-color: #D9F9B2">&nbsp;</td>';
		$result .= '</tr>';

		$result .= '<tr>';
		$result .= '<th style="font-size:0.9em; color:black;background-color: #D9F9B2">CLIENTE</td>';
		$result .= '<th style="font-size:0.9em; color:black;background-color: #D9F9B2">IMPORTE</td>';
		$result .= '<th style="font-size:0.9em; color:black;background-color: #D9F9B2">ESTADO</td>';
		$result .= '</tr>';

		$resultados = count($arrCeldas);
		$codigoexcel = '';
		$codigoexcel1 = '';

		if(strlen($arrCeldas[4][1]['raw']) > 0 && strlen($arrCeldas[4][2]['raw']) > 0) {
			for ($i = 4; $i <= $resultados; $i++) {
				$codcliente	= stripslashes($arrCeldas[$i][1]['raw']);
				$codcliente = trim($codcliente,"'");
				$nuimporte	= $arrCeldas[$i][2]['raw'];

				$color = ($i%2==0?"grid_detalle_par":"grid_detalle_impar");
				$datos = PosDescuentoRucModel::ValidarExcel(trim($nuproducto), $notd, trim($codcliente));
				if($codigoexcel == $codcliente && $codigoexcel1 == $nuimporte){
					$colorletra = "red";
					$nocliente = $datos[0]['nocliente'];
					$status = "DUPLICADO";
				} elseif ($datos[0]['existe_cliente'] == '0' && $datos[0]['existe_descuento'] == '0') {
					$colorletra = "red";
					$nocliente = "NO EXISTE PRODUCTO";
					$status = "-";
				} elseif ($datos[0]['existe_cliente'] == '1' && $datos[0]['existe_descuento'] == '0') {
					$colorletra = "blue";
					$nocliente = $datos[0]['nocliente'];
					$status = "NUEVO";
					$procesar = true;
				} elseif ($datos[0]['existe_cliente'] == '1' && $datos[0]['existe_descuento'] == '1') {
					$colorletra = "red";
					$nocliente = $datos[0]['nocliente'];
					$status = "EXISTE";
				}

				$result .= '<tr bgcolor="">';
				$result .= '<td class="'.$color.'" align = "center"><p style="color:'.$colorletra.';">' . htmlentities($nocliente) . '</td>';
				$result .= '<td class="'.$color.'" align = "right"><p style="color:'.$colorletra.';">S/ ' . number_format($nuimporte, 2, '.', ',') . '</td>';
				$result .= '<td class="'.$color.'" align = "center"><p style="color:'.$colorletra.';">' . htmlentities($status) . '</td>';
				$result .= '</tr>';
				$codigoexcel = $codcliente;
				$codigoexcel1 = $nuimporte;
			}
		} else {
			$result .= '<tr bgcolor="">';
			$result .= '<td colspan="3" align="center" class="'.$color.'" ><p style="font-size:12px; color:red;">No hay informacion</td>';
			$result .= '</tr>';
			$procesar = false;
		} if($procesar) {
			$result .= '<tr bgcolor="C9F4D4">';
			$result .= '<td colspan="3" align="center">';
			$result .= '<A href="control.php?rqst=MAESTROS.POS_DESCUENTO_RUC&task=POS_DESCUENTO_RUC&action=EnviarData&filename='.$filename.'&nuproducto='.$nuproducto.'&notd='.$notd.'&nutd='.$nutd.'" target="control"><button><img src="/sistemaweb/icons/importar_excel.png" align="right" />Procesar Lista</button></A>';
			$result .= '&nbsp;&nbsp;&nbsp;<button name="action" type="button" value="Regresar" onclick="regresar()"><img src="/sistemaweb/icons/atra.gif" align="right" />Regresar</button>';
			$result .= '</tr>';
		} else {
			$result .= '<tr bgcolor="C9F4D4">';
			$result .= '<td colspan="3" align="right">';
			$result .= '<button name="action" type="button" value="Regresar" onclick="regresar()"><img src="/sistemaweb/icons/atra.gif" align="right" />Regresar</button>';
			$result .= '</tr>';
		}
		$result .= '</table>';
		return $result;
   }

	function form($a, $tipo) {
		$model = new PosDescuentoRucModel();
		$radioActivo = "";
		for ($i = 0; $i <= 1; $i++) {
   		$check = "";
   		if (!empty($a["activo"]) && $a["activo"] == $i) {
				$check = " checked = 'checked'";
			}
			$descripcion = "";
			if ($i == 0) {
				$descripcion = "Inactivo ";
			} else {
				$descripcion = "Activo ";
			}
			$radioActivo .= $descripcion . "<input type = 'radio' name = 'activo' $check value = '$i'/>";
		}

		$articulos = $model ->buscarArticulosPorCodigo($a["art_codigo"]);
		$descrip = $articulos[0]["art_codigo"] ." - " .$articulos[0]["art_descripcion"];
		$html = '
		<div id = "dialog" style = "display:none;">
			<div class="fila">
				Buscar articulo por:
			</div>
			<div class="fila">
				<input type="radio" name ="tipoBuscar" id="tipoBuscar" class ="separador"/>
				Codigo
				<input type="radio" name ="tipoBuscar" id="tipoBuscar" class ="separador"/>
				Descripcion
			</div>
			<div class="fila">
				<label class="separador">Ingresar</label>
				<input type="text" id="art_descripcion" />
			</div>
		</div>
		<form method="post" class="form1" action = "control.php" target="control">
			<input type="hidden" name="rqst" value="MAESTROS.POS_DESCUENTO_RUC"/>
			<input type="hidden" name="task" value="POS_DESCUENTO_RUC"/>
			<input type="hidden" name="tipo_guardar" value="' . $tipo . '"/>
			<input type="hidden" name="pos_descuento_ruc_id" value="' . $a["pos_descuento_ruc_id"] . '"/>
			<fieldset>
				<legend>Datos del descuento</legend>
				<div class="fila">
					<label class="etiqueta">RUC: </label>
					<input type="text" name="ruc" maxlength="11" value = "' . $a["ruc"] . '" class = "campo1" />
				</div>
				<div class="fila">
					<label class="etiqueta">Articulo: </label>
					<input type = "hidden" name = "art_codigo" id = "art_codigo"  value = "'.$a["art_codigo"]. '"/>
					<label class="etiqueta2" id ="art_descrip">'.$descrip . '</label>
					<input type = "button" name = "btnBuscarArticulo" value = "Buscar articulo" onclick = "buscarArticulo()"/>
				</div>
				<div class="fila">
					<label class="etiqueta">Descuento: </label>
					<input type="text" name="descuento" maxlength="10" value = "' . $a["descuento"] . '" class = "campo1" />
				</div>
				<div class="fila">
					<label class="etiqueta">Estado: </label>
					' . $radioActivo . '
				</div>
				<div class="fila">
					<label class="etiqueta">Tipo: </label>
					<select name="tipo" >
						<option value="1">Nota de Despacho</option>
						<option value="2">Factura</option>
						<option value="3">Tarjeta de Descuento (Boleta)</option>
						<option value="5">Precio Pactado</option>
					</select>
				</div>
				<div class="fila">
					<input type="submit" name = "action" value="Guardar" />
					<input type="button" value="Regresar" onclick="regresar()" />
				</div>
				<div id="error_body"></div>
			</fieldset>
		</form>';
		
		return $html;
	}

	function gridViewEXCEL($arrDescuentos) {
		$chrFileName = "";

		$workbook = new Workbook($chrFileName);
		$formato0 =& $workbook->add_format();
		$formato2 =& $workbook->add_format();
		$formato5 =& $workbook->add_format();

		//titulo
		$formato0->set_size(11);
		$formato0->set_bold(1);
		$formato0->set_align('center');

		//Sub titulo
		$formato2->set_size(10);
		$formato2->set_bold(1);
		$formato2->set_align('center');
		$formato2->set_bottom(1);
		$formato2->set_top(1);
		$formato2->set_right(1);
		$formato2->set_left(1);

		//Data
		$formato5->set_size(10);
		$formato5->set_align('left');

		$worksheet1 =& $workbook->add_worksheet('Descuentos por RUC');
		$worksheet1->set_column(0, 0, 15);//FECHA
		$worksheet1->set_column(1, 1, 15);//RUC
		$worksheet1->set_column(2, 2, 60);//NOMBRE
		$worksheet1->set_column(3, 3, 30);//ARTICULO
		$worksheet1->set_column(4, 4, 15);//DESCUENTO
		$worksheet1->set_column(5, 5, 10);//ESTADO
		$worksheet1->set_column(6, 6, 20);//TIPO

		$worksheet1->set_zoom(100);
		$worksheet1->set_landscape(100);
		
		$worksheet1->write_string(1, 0, "DESCUENTOS POR RUC",$formato0);

		$fila = 3;
		$worksheet1->write_string($fila, 0, "FECHA",$formato2);
		$worksheet1->write_string($fila, 1, "RUC",$formato2);
		$worksheet1->write_string($fila, 2, "NOMBRE",$formato2);
		$worksheet1->write_string($fila, 3, "ARTICULO",$formato2);
		$worksheet1->write_string($fila, 4, "DESCUENTO",$formato2);
		$worksheet1->write_string($fila, 5, "ESTADO",$formato2);
		$worksheet1->write_string($fila, 6, "TIPO",$formato2);

		$fila = 4;
		for ($i=0; $i<count($arrDescuentos); $i++){
			$worksheet1->write_string($fila, 0, $arrDescuentos[$i]['fecha'],$formato5);
			$worksheet1->write_string($fila, 1, $arrDescuentos[$i]['ruc'],$formato5);
			$worksheet1->write_string($fila, 2, $arrDescuentos[$i]['cli_razsocial'],$formato5);
			$worksheet1->write_string($fila, 3, trim($arrDescuentos[$i]['art_codigo']),$formato5);
			$worksheet1->write_string($fila, 4, $arrDescuentos[$i]['descuento'],$formato5);
			$worksheet1->write_string($fila, 5, $arrDescuentos[$i]['activo'],$formato5);
			$worksheet1->write_string($fila, 6, $arrDescuentos[$i]['tipo'],$formato5);
			$fila++;
		}

		$workbook->close();	
		$chrFileName = "descuentos_por_ruc";
		header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename = " . $chrFileName . ".xls");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
		header("Pragma: public");
	}
}