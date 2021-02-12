<?php

date_default_timezone_set('UTC');

class ListaComprasTemplate extends Template {
    
	function searchForm($proveedor) {

		$form = new form2('<h3><b>Lista de Precios Proveedor</b></h3>', 'form_listacompras', FORM_METHOD_POST, 'control.php', '', 'control','enctype="multipart/form-data"');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "REPORTES.LISTACOMPRAS"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Proveedor</td><td>:</td><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text('cod_proveedor', '', $proveedor, '', 13, 13));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text('nom_proveedor', '', '', '', 30, 50,'',array('readonly')));	
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<img src="../images/help.gif" width="16" height="15" onMouseOver="this.style.cursor=\'pointer\'" onclick="javascript:mostrarAyuda(\'lista_ayuda.php\',\'form_listacompras.cod_proveedor\',\'form_listacompras.nom_proveedor\',\'proveedores\',\'\',\'<?php echo $valor;?>\');"> ')); 
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Articulo</td><td>:</td><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text('cod_articulo', '', '', '', 13, 15));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text('nom_articulo', '', '', '', 30, 50,'',array('readonly')));		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<img src="../images/help.gif" width="16" height="15" onMouseOver="this.style.cursor=\'pointer\'" onclick="javascript:mostrarAyuda(\'lista_ayuda.php\',\'form_listacompras.cod_articulo\',\'form_listacompras.nom_articulo\',\'articulos\',\'\',\'<?php echo $valor;?>\');"> ')); 
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>&nbsp;'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="3" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Buscar"><img src="/sistemaweb/images/search.png" align="right" />Buscar</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Agregar"><img src="/sistemaweb/icons/agregar.gif" align="right" />Agregar</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Importar"><img src="/sistemaweb/images/excel_icon.png" align="right" />Importar Precios</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br/>'));
	
		return $form->getForm();
    }

	function ImportarDataExcel() {

		$form = new form2('Lista de Precios Proveedor', 'form_listacompras', FORM_METHOD_POST, 'control.php', '', 'control','enctype="multipart/form-data"');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "REPORTES.LISTACOMPRAS"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br/><table border="0" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('Seleccionar archivo Excel: <input type="file" name="ubica" id="ubica" size="70" onClick="Mostrar();">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td><div id="ver" style="display:none;">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Importar Lista Excel"><img src="/sistemaweb/images/excel_icon.png" align="right" />Importar Lista Excel</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</div>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</table>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br/>'));
	
		return $form->getForm();
    }

    function MostrarDataExcel($data, $filename) {

		$result			= '';
		$iCodigoProveedor 	= '';
		$sRazsocialProveedor 	= '';
		$color 			= '';
		$codmoneda		= 01;
		$fecha			= '';
		$procesar = null;

		$iCodigoProveedor 	= $data->val(1, 2);
		$fecha	 		= $data->val(3, 2);
		$codmoneda		= $data->val(2, 2);
		$nommoneda 		= $data->val(2, 3);

		$color		= 'black';

		$sRazsocialProveedor = ListaComprasModel::getProveedor($iCodigoProveedor);

		/* FORMATO DE FECHAS */

		$fsystem = ListaComprasModel::FechaSistema();//pos_aprosys
		$dsystem = substr($fsystem,8,2);
		$msystem = substr($fsystem,5,2);
		$ysystem = substr($fsystem,0,4);

		$year	= substr($data->val(3, 2), 6, 4);
		$mes	= substr($data->val(3, 2), 3, 2);
		$dia	= substr($data->val(3, 2), 0, 2);

		$result .= '<table align="center"> ';

		$result .= '<tr>';
		$result .= '<th align="right" style="font-size:0.7em; color:black;background-color: #D9F9B2">Fecha Sistema: </th>';
		$result .= '<th colspan="7" align="left" style="color:black;background-color: #D9F9B2">';
		$fsystem = $dsystem."-".$msystem."-".$ysystem;
		$result .= $fsystem;

		$result .= '<tr>';
		$result .= '<th align="right" style="font-size:0.7em; color:black;background-color: #D9F9B2">Proveedor: </th>';

		if($sRazsocialProveedor){
			$result .= '<th colspan="7" align="left" style="color:'.$color.';background-color: #D9F9B2">';
			$result .= $iCodigoProveedor.' - '.$sRazsocialProveedor;
		}else{
			$result .= '<th colspan="7" align="left" style="font-size:0.7em; color:red;background-color: #D9F9B2">';
			$result .= 'INEXISTENTE: '. $iCodigoProveedor;
		}

		$result .= '<tr>';
		$result .= '<th align="right" style="font-size:0.7em; color:black;background-color: #D9F9B2">Fecha Ingreso: </th>';

		$consolidacion = ListaComprasModel::validaDia($fecha, $_SESSION['almacen']);

		if($year.$mes.$dia > $ysystem.$msystem.$dsystem){
			echo "<script>alert('La fecha de ingreso no puede ser mayor a la Fecha Sistema');</script>";
			$result .= '<th colspan="7" align="left" style="color:red;background-color: #D9F9B2"><blink>';
			$result .= $fecha;
			$result .= '</blink>';
		} else if ($mes < $msystem){
			echo "<script>alert('El Mes debe de ser el mismo al de Fecha Sistema !');</script>";
			$result .= '<th colspan="7" align="left" style="color:red;background-color: #D9F9B2"><blink>';
			$result .= $fecha;
			$result .= '</blink>';

		} else if($consolidacion == 1){
			$result .= '<th colspan="7" align="left" style="font-size:0.7em; color:red;background-color: #D9F9B2"><blink>';
			$result .= ' CONSOLIDADO: '.$fecha;
			$result .= '</blink>';
		} else {
			$result .= '<th colspan="7" align="left" style="color:'.$color.';background-color: #D9F9B2">';
			$result .= $fecha;
		}

		$result .= '<tr>';
		$result .= '<th align="right" style="font-size:0.7em; color:black;background-color: #D9F9B2">Moneda: </th>';
		$result .= '<th colspan="7" align="left" style="color:black;background-color: #D9F9B2">';
		$result .= $codmoneda.' - '.$nommoneda;


		$result .= '<tr>';
		$result .= '<th colspan="8" style="font-size:0.7em; color:black;background-color: #D9F9B2">&nbsp;</td>';
		$result .= '</tr>';

		$result .= '<tr>';
		$result .= '<th style="font-size:0.7em; color:black;background-color: #D9F9B2">NRO. CORRELATIVO</td>';
		$result .= '<th style="font-size:0.7em; color:black;background-color: #D9F9B2">CODIGO ARTICULO</td>';
		$result .= '<th style="font-size:0.7em; color:black;background-color: #D9F9B2">DESRIPCION</td>';
		$result .= '<th style="font-size:0.7em; color:black;background-color: #D9F9B2">COSTO UNITARIO</td>';
		$result .= '<th style="font-size:0.7em; color:black;background-color: #D9F9B2">COSTO ANTERIOR</td>';
		$result .= '<th style="font-size:0.7em; color:black;background-color: #D9F9B2">DIFERENCIA %</td>';
		$result .= '<th style="font-size:0.7em; color:black;background-color: #D9F9B2">FECHA COSTO ANTERIOR</td>';
		$result .= '<th style="font-size:0.7em; color:black;background-color: #D9F9B2">ESTADO</td>';
		$result .= '</tr>';

		$resultados 	= count($data->sheets[0]['cells']);
		$codigoexcel	= '';

		if(strlen($data->sheets[0]['cells'][6][2]) > 0 && strlen($data->sheets[0]['cells'][6][4]) > 0){

			for ($i = 6; $i <= ($resultados + 1); $i++) {

				$correlativo	= $data->sheets[0]['cells'][$i][1];
				$codigo		= $data->sheets[0]['cells'][$i][2];
				$descripcion	= $data->sheets[0]['cells'][$i][3];
				$costo		= $data->sheets[0]['cells'][$i][4];

				if (strlen($codigo) > 0 && strlen($costo) > 0){

					//VALIDAR ARCHIVO EXCEL PRODUCTOS QUE EXISTAN B.D
					$datos	= ListaComprasModel::validarExcel($codigo, $iCodigoProveedor);

					$color = ($i%2==0?"grid_detalle_par":"grid_detalle_impar");

					$dcosto 	= $datos[0]['costo'];
					$porcentajeact 	= (($dcosto / 100) * 3);
					$porcentajeant 	= (($costo / 100) * 3);
					$procentaje 	= ($porcentajeact - $porcentajeant);
					$dfecha 	= $datos[0]['fecha'];

					if($datos[0]['existe'] == 1){

						if($codigoexcel == $codigo){

							$colorletra	= "red";
							$status		= "DUPLICADO";

						} elseif($datos[0]['codigo'] == NULL){

							$colorletra	= "red";
							$status 	= "INEXISTENTE";
							$dcosto 	= "";
							$procentaje 	= "";
							$dfecha 	= "";

						} elseif($datos[0]['codigo'] != NULL && $datos[0]['costo'] == NULL) {

							$colorletra 	= "blue";
							$status 	= "NUEVO";
							$procesar 	= true;

						} else {
							$colorletra 	= "red";
							$status 	= "EXISTE";
						}

						$result .= '<tr bgcolor="">';
						$result .= '<td class="'.$color.'" align = "center"><p style="color:'.$colorletra.';">' . htmlentities($correlativo) . '</td>';
						$result .= '<td class="'.$color.'" align = "left"><p style="color:'.$colorletra.';">' . htmlentities($codigo) . '</td>';
						$result .= '<td class="'.$color.'" align = "left"><p style="color:'.$colorletra.';">' . htmlentities($datos[0]['descripcion']) . '</td>';
						$result .= '<td class="'.$color.'" align = "right"><p style="color:'.$colorletra.';">' . htmlentities($costo) . '</td>';
						$result .= '<td class="'.$color.'" align = "right"><p style="color:'.$colorletra.';">' . htmlentities(number_format($dcosto, 4, '.', ',')) . '</td>';
						$result .= '<td class="'.$color.'" align = "center"><p style="color:'.$colorletra.';">' . htmlentities($procentaje) . '</td>';
						$result .= '<td class="'.$color.'" align = "center"><p style="color:'.$colorletra.';">' . htmlentities($dfecha) . '</td>';
						$result .= '<td class="'.$color.'" align = "center"><p style="color:'.$colorletra.';">' . htmlentities($status) . '</td>';

						$result .= '</tr>';

						$codigoexcel = $codigo;
				
					} else {
						$result .= '<tr bgcolor="">';
						$result .= '<td colspan="8" class="'.$color.'" align = "center"><p style="font-size:12px; color:red;">Error al importar Proveedor Invalido: '. htmlentities($iCodigoProveedor) . '</td>';
						break;
					}

				}

			}

		} else {

			$result .= '<tr bgcolor="">';
			$result .= '<td colspan="8" align="center" class="'.$color.'" ><p style="font-size:12px; color:red;">No hay informacion</td>';
			$result .= '</tr>';
			$procesar = false;

		}

		$result .= '<tr bgcolor="F3FAF5">';
		$result .= '<td colspan="8" align="center">&nbsp;</td>';
		$result .= '</tr>';

		if($procesar){
			$result .= '<tr bgcolor="F3FAF5">';
			$result .= '<td colspan="7" align="left">';
			$result .= '<td><A href="control.php?rqst=REPORTES.LISTACOMPRAS&action=Actualizar&filename='.$filename.'&codproveedor='.$iCodigoProveedor.'&codmoneda='.$codmoneda.'" target="control"><button><img src="/sistemaweb/icons/importar_excel.png" align="right" />Procesar Lista</button></A></td>';
			$result .= '</tr>';
		}

		$result .= '</table>';
		return $result;
    }

    	function reporte($resultados, $bproveedor) {

		$hoy = date("d/m/Y");

		$dia  = date("d");
		$mes  = date("m");
		$year = date("Y");

		$result = '';
		$result .= '<table align="center">';
		$result .= '<tr>';
		$result .= '<th style="font-size:0.7em; color:black;background-color: #D9F9B2">&nbsp;&nbsp;PROVEEDOR&nbsp;&nbsp;</th>';
		$result .= '<th style="font-size:0.7em; color:black;background-color: #D9F9B2">&nbsp;&nbsp;ARTICULO&nbsp;&nbsp;</th>';
		$result .= '<th style="font-size:0.7em; color:black;background-color: #D9F9B2">&nbsp;MONEDA&nbsp;</th>';
		$result .= '<th style="font-size:0.7em; color:black;background-color: #D9F9B2">&nbsp;&nbsp;PRECIO SIN IGV&nbsp;&nbsp;</th>';
		$result .= '<th style="font-size:0.7em; color:black;background-color: #D9F9B2">&nbsp;FECHA ACTUALIZACION&nbsp;</th>';
		$result .= '<th colspan="2" style="font-size:0.7em; color:black;background-color: #D9F9B2">&nbsp</th>';
		$result .= '</tr>';

		for ($i=0; $i<count($resultados); $i++) {

			$color = ($i%2==0?"grid_detalle_par":"grid_detalle_impar");

			$result .= '<tr bgcolor="">';
			$result .= '<td  class="'.$color.'" align="left">&nbsp;'.trim($resultados[$i]['cod_proveedor']).' - '.trim($resultados[$i]['nom_proveedor']).'</td>';
			$result .= '<td  class="'.$color.'" align="left">&nbsp;'.trim($resultados[$i]['cod_articulo']).' - '.trim($resultados[$i]['nom_articulo']).'</td>';

			if (trim($resultados[$i]['moneda'])=="01") 
				$moneda = "SOLES" ;
			else 
				$moneda = "DOLARES";

			$result .= '<td  class="'.$color.'" align="center">&nbsp;'.trim($moneda).'&nbsp;</td>';
			$result .= '<td  class="'.$color.'" align="center">&nbsp;'.trim($resultados[$i]['precio']).'&nbsp;</td>';
			$result .= '<td  class="'.$color.'" align="center">&nbsp;'.trim($resultados[$i]['ultima_compra']).'&nbsp;</td>';

			$result .= '<td class="'.$color.'"><A href="control.php?rqst=REPORTES.LISTACOMPRAS&action=Editar&fecha_compra='.trim($resultados[$i]['ultima_compra']).'&cod_proveedor='.trim($resultados[$i]['cod_proveedor']).'&nom_proveedor='.trim($resultados[$i]['nom_proveedor']).'&cod_articulo='.trim($resultados[$i]['cod_articulo']).'&nom_articulo='.trim($resultados[$i]['nom_articulo']).'&precio='.trim($resultados[$i]['precio']).'&moneda='.trim($resultados[$i]['moneda']).'&bproveedor='.trim($bproveedor).'" target="control")"><img src="/sistemaweb/icons/anular.gif" alt="Borrar" align="middle" border="0"/></A></td>';
			$result .= '<td  class="'.$color.'"><A href="javascript:confirmarLink(\'¿Desea eliminar el registro?\',\'control.php?rqst=REPORTES.LISTACOMPRAS&action=Eliminar&cod_proveedor='.trim($resultados[$i]['cod_proveedor']).'&cod_articulo='.trim($resultados[$i]['cod_articulo']).'\', \'control\')"><img src="/sistemaweb/icons/delete.gif" alt="Borrar" align="middle" border="0"/></A></td>';

			$result .= '</tr>';
		    	
		}		

		$result .= '</table>';

		return $result;

    	}

	function formAgregar($tipo, $proveedor, $nom_proveedor, $articulo, $nom_articulo, $precio, $moneda, $rec_arti_prove, $bproveedor) {

		$form = new form2('Datos de Articulo y Proveedor', 'form_agregar', FORM_METHOD_POST, 'control.php', '', 'control','');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'REPORTES.LISTACOMPRAS'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="0" cellpadding="5" bgcolor="#FFFFCD">'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<input type="hidden" name="bproveedor" id="bproveedor" value = "' . $bproveedor . '"/>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>'));

		if ($tipo == "A") {
			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('cod_proveedor','Proveedor</td><td>: ', $proveedor, '', 15, 15,'', ''));
			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('nom_proveedor','</td><td> ', $nom_proveedor, '', 30, 50,'',array('readonly')));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<img src="../images/help.gif" width="16" height="15" onMouseOver="this.style.cursor=\'pointer\'" onclick="javascript:mostrarAyuda(\'lista_ayuda.php\',\'form_agregar.cod_proveedor\',\'form_agregar.nom_proveedor\',\'proveedores\',\'\',\'<?php echo $valor;?>\');"> ')); 
		} else {
			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('cod_proveedor','Proveedor</td><td>: ', $proveedor, '', 15, 15,'',array('readonly')));
			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('nom_proveedor','</td><td> ', $nom_proveedor, '', 30, 50,'',array('readonly')));
		}

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>')); 

		if ($tipo == "A") {
			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('cod_articulo','Articulo</td><td>: ', $articulo, '', 15, 15,'','')); 
			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('nom_articulo','</td><td>', $nom_articulo, '', 30, 50,'',array('readonly'))); 
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<img src="../images/help.gif" width="16" height="15" onMouseOver="this.style.cursor=\'pointer\'" onclick="javascript:mostrarAyuda(\'lista_ayuda.php\',\'form_agregar.cod_articulo\',\'form_agregar.nom_articulo\',\'articulos\',\'\',\'<?php echo $valor;?>\');"> ')); 
		} else {
			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('cod_articulo','Articulo</td><td>: ', $articulo, '', 15, 15,'',array('readonly'))); 
			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('nom_articulo','</td><td>', $nom_articulo, '', 30, 50,'',array('readonly'))); 
		}

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>')); 

		if ($tipo == "A") {
			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('rec_arti_prove','PLU Proveedor</td><td>: ', $rec_arti_prove, '', 15, 15,'','')); 
		} else {
			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('rec_arti_prove','PLU Proveedor</td><td>: ', $rec_arti_prove, '', 15, 15,'','')); 
		}	

		$tipomonedas = Array("01"=>"SOLES", "02"=>"DOLARES"); 

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>')); 
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo ('moneda','Tipo de Moneda</td><td>: ', $moneda, $tipomonedas, '&nbsp', '',''));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('precio','Precio</td><td>: ', $precio,'', 13, 10,''));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));  
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="3" align="center" height="30">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Guardar"><img src="/sistemaweb/icons/agregar.gif" align="right" />Guardar</button>'));

		if ($tipo == "A")
			$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("tipoguardar", "A"));
		else 
			$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("tipoguardar", "E"));

//		$form->addElement(FORM_GROUP_MAIN, new f2element_button('button','Regresar', espacios(2),array('onclick'=>'regresar();')));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Regresar" onclick="regresar();"><img src="/sistemaweb/icons/atra.gif" align="right" />Regresar</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));

		return $form->getForm();
	}
}
