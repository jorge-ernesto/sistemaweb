<?php
class TanquesTemplate extends Template {

	function titulo() {
		return '<h2 align="center"><b>Configuracion de Tanques</b></h2>';
	}

	function Formulario($Producto,$Producto2){

		$hoy_timestamp  = time();
		$ayer_timestamp = $hoy_timestamp - (24*60*60);
		$ayer = date("d/m/Y", $ayer_timestamp);

		$mes  = date("m");
		$dia  = "01";
		$year = date("Y");
	
		$inicio_mes = mktime(0, 0, 0, $mes, $dia, $year);
		$fin_mes    = mktime(0, 0, 0, $mes+1, 0, $year);
		
		if ($Producto == "" or $Producto2 == "") {
			$Producto      = date("d/m/Y", $inicio_mes);
			$Producto2     = date("d/m/Y", $fin_mes);
		}

		$ver = TanquesModel::buscar($Producto,$Producto2);
		return $ver;

	}
	
	function formSearch(){

		$almacenes = TanquesModel::obtenerAlmacenes("");

		if($almacen == "")
			$almacen = $_SESSION['almacen'];

		$form = new Form('', "Form", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MAESTROS.TANQUES"));
		$form->addGroup("GRUPO_PAGINA", "Buscar por Almacen: ");
		$form->addGroup("GRUPO_AGREGAR", "Opcion: ");
		$form->addElement("GRUPO_PAGINA", new form_element_anytext("<table align='center'>"));
		$form->addElement("GRUPO_PAGINA", new form_element_anytext('<tr><td >'));
		//$form->addElement(FORM_GROUP_MAIN, new form_element_button("btRegresar", "Regresar", '<br>', '', 1, 'onclick="TipoCambioRegresar(\'' . $fecha . '\', \'' . $fecha2 . '\')"'));
		//$form->addElement("GRUPO_PAGINA", new f2element_combo('almacen', '', $almacen, $almacenes, 'onclick="BuscarProducto(\'' . $almacen . '\')"'));
		$form->addElement("GRUPO_PAGINA", new f2element_combo('almacen', '', $almacen, $almacenes, ''));
		$form->addElement("GRUPO_PAGINA", new f2element_freeTags('</td></tr><tr><td align="center">'));
		$form->addElement("GRUPO_PAGINA", new form_element_anytext('</td></tr></table>'));
		$form->addElement("GRUPO_AGREGAR", new form_element_anytext("<table>"));
		$form->addElement("GRUPO_AGREGAR", new form_element_anytext("<tr><td align='center'>"));
		$form->addElement("GRUPO_AGREGAR", new f2element_freeTags('<button name="action" type="submit" value="Agregar"><img src="/sistemaweb/icons/agregar.gif" align="left" />&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp;Agregar</button>'));
		$form->addElement("GRUPO_AGREGAR", new form_element_anytext('</td></tr></table>'));

		return $form->getForm();
	}

	function formAgregar($fila) {

		$productito = TanquesModel::obtenerProducto();

		$productito['TODOS'] = "Seleccionar";

		$form = new Form('',"Editar", FORM_METHOD_POST, "control.php", '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MAESTROS.TANQUES"));

			if($_REQUEST['action'] == 'Modificar'){

				$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('accion', 'actualizar'));

			}
				$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<table>"));

				if($_REQUEST['action'] == 'Agregar'){

					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<tr><td colspan="2" style="text-align:right;">'));
				   	$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("Codigo Tanque: <td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'cod_tanque', "" , '</td></tr><tr><td colspan="2" style="text-align:right;">', '', 4, 4,($_REQUEST['action']=='Modificar'?array('readonly'):array())));
					$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("Producto:</td><td style='text-align:left;'>"));
			    		$form->addElement(FORM_GROUP_MAIN, new f2element_combo('nom_producto','', 'TODOS',$productito, '',2));
				   	$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td></tr><td colspan='2' style='text-align:right;'>"));
					$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("Capacidad:</td><td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'capacidad', "", '</td></tr><tr><td colspan="2" style="text-align:center;">', 6, 6, false));
					$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("ULT Lectura Galones:</td><td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'lectu_gal', "", '</td></tr><tr><td colspan="2" style="text-align:center;">', '', 6, 6, false));
					$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td></tr>"));
					$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr><td colspan = '2' align='center'>"));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Guardar"><img src="/sistemaweb/icons/agregar.gif" align="right" />Guardar</button>'));
					$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
					$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td align='left'>"));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Regresar" onclick="regresar()"><img src="/sistemaweb/icons/atra.gif" align="right" />Regresar</button>'));
					$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td></tr></table>"));
				}else{
					//$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<table>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<tr>'));
					$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td style='text-align:right;'> Codigo Tanque: "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:left;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'cod_tanque', $fila['ch_tanque'], "", "",10,10,($_REQUEST['action']=='Modificar'?array('readonly'):array())));
					$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td></tr><tr><td style='text-align:right;'> Producto: "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:left;">'));
			    		$form->addElement(FORM_GROUP_MAIN, new f2element_combo('nom_producto','', $fila['nom_producto'],$productito, '',2));
					$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td></tr><tr><td style='text-align:right;'> Capacidad: "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:left;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'capacidad', $fila['nu_capacidad'], "", "",10,10));
					$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td></tr><tr><td style='text-align:right;'> ULT Lectura Galones : "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:left;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'lectu_gal', $fila['nu_ultimamedida'], "", "",10,10));
					$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td></tr><tr><td style='text-align:right;'> Fecha ULT Lectura : "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:left;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'dt_fecha', $fila['dt_fechaultimamedida'], "", "",10,10,($_REQUEST['action']=='Modificar'?array('readonly'):array())));
					$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td><tr><td align='center'>"));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Actualizar"><img src="/sistemaweb/icons/update2.png" align="right" />Actualizar</button>'));
					$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
					$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td align='left'>"));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Regresar" onclick="regresar()"><img src="/sistemaweb/icons/atra.gif" align="right" />Regresar</button>'));
					$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td></tr></table>"));
				}

		return $form->getForm();
	}

	function resultadosBusqueda($resultados) {

		$result .= '<table align="center">';
		$result .= '<tr>';
		$result .= '<th class="grid_cabecera">CODIGO</th>';
		$result .= '<th class="grid_cabecera">PRODUCTO</th>';
		$result .= '<th class="grid_cabecera">CAPACIDAD</th>';
		$result .= '<th class="grid_cabecera">ULT LECTURA GALONES</th>';
		$result .= '<th class="grid_cabecera">FECHA ULT LECTURA</th>';
		$result .= '</tr>';

		for ($i = 0; $i < count($resultados); $i++) {
			$color = ($i%2==0?"grid_detalle_par":"grid_detalle_impar");
			$a = $resultados[$i];
			$result .= '<tr bgcolor="">';
			$result .= '<td class="'.$color.'" align="center">' . htmlentities($a['ch_tanque']) . '</td>';
			$result .= '<td class="'.$color.'" align="center">' . htmlentities($a['ch_nombrecombustible']) . '</td>';
			$result .= '<td class="'.$color.'" align="center">' . htmlentities($a['nu_capacidad']) . '</td>';
			$result .= '<td class="'.$color.'" align="center">' . htmlentities($a['nu_ultimamedida']) . '</td>';
			$result .= '<td class="'.$color.'" align="center">' . htmlentities($a['dt_fechaultimamedida']) . '</td>';
			$result .= '<td class="'.$color.'" align="center"><A href="control.php?rqst=MAESTROS.TANQUES&action=Modificar&cod_tanque='.htmlentities($a['ch_tanque']).'" target="control"><img src="/sistemaweb/icons/open.gif" align="middle" border="0"/></A>&nbsp;</td>';
			$result .= '<td class="'.$color.'" align="center"><A href="control.php?rqst=MAESTROS.TANQUES&action=Eliminar&cod_tanque='.htmlentities($a['ch_tanque']).'" target="control"><img src="/sistemaweb/icons/delete.gif" align="middle" border="0"/></A>&nbsp;</td>';
			$result .= '</tr>';
		}
		$result .= '</table>';
		return $result;
	}
}
?>

