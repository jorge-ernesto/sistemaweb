<?php

class FormsTemplate extends Template
{
    function titulo()
    {
	return '<h2 align="center" style="color:#336699"><b> Modificar Movimiento(s) de Formulario </b></h2>';
    }
    
    function errorResultado($errormsg){
        return '<blink>'.$errormsg.'</blink>';
    }

    function FormModificar($tipo_transa, $almacenes, $fecha, $tipo, $almacen){

		if (!isset($fecha)) $fecha = date("d/m/Y");
		$form = new form('', "Modificar", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "FORMS.BUSCAR"));

		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext(
		'<table border="0">
			<tr>
				<td align="right">Almacén </td>
				<td>
		'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_combo('almacen', '', trim(@$almacen), $almacenes, espacios(3), array("onfocus" => "getFechaEmision();"), ''));

		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('
				</td>
			</tr>
			<tr>
				<td align="right">Fecha emisión </td>
				<td>
					<input type="text" name="fecha" id="fecha" maxlength="10" size="12" class="fecha_formato" value="'.(empty($_REQUEST['fecha']) ? $fecha : $_REQUEST['fecha']).'" />
					<span id="resultado"></span>
				</td>
			</tr>
		'));

		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('
			</tr>
			<tr>
				<td align="right">Tipo Formulario</td>
				<td>
		'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_combo("", "tipotransa", $tipo, '', '', 1, $tipo_transa, false, ''));

		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('
				</td>
			</tr>
		'));

		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('
		<tr>
			<td colspan="2" align="center">
				<br>
				<button type="submit" id="buscar" name="buscar" value="Buscar"><img src="/sistemaweb/icons/gbuscar.png" align="right" />Buscar</button>
			</td>
		</tr>
		</table>
		'));

		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext(
		'<script>
			window.onload = function() {
				parent.document.getElementById("almacen").focus();
			}
		</script>'
		));

		return $form->getForm();
    }
    
    function FormListado($listado, $tipos) {

	    $DisplayOrig = "none;";
	    $DisplayDest = "none;";
	    $DisplayTipo = "block";
	     //print_r($_REQUEST);
	     //echo "TIME : ".date("G:i:s")."\n";
	     $tiposRegu = VariosModel::ListaGeneral('TREG', '');
	     if(trim($_REQUEST['tipotransa'])=='08'){
		//echo "ENTRO 08\n";
		$tipos_alma_des = FormsModel::obtenerTipoAlmacen('3');
		$DisplayDest = "block;";
	     }elseif(trim($_REQUEST['tipotransa'])=='07'){
	     //echo "ENTRO 07\n";
		$tipos_alma_ori = FormsModel::obtenerTipoAlmacen('2');
		$DisplayOrig = "block;";
	     }else{
	       // echo "ENTRO ELSE\n";
	     }

		$result .= '<div id="error_body" align="center"></div>';
		$result .= '<form name="transacciones" action="control.php" target="control">';
		$result .= '<input type="hidden" name="rqst" value="FORMS.ACTION">';
		$result .= '<input type="hidden" name="almacen" value="'.$_REQUEST['almacen'].'">';
		$result .= '<input type="hidden" name="fecha" value="'.$_REQUEST['fecha'].'">';
		$result .= '<input type="hidden" name="tipotransa" value="'.$_REQUEST['tipotransa'].'">';
	
		//$result .= '<table align="center" cellspacing="2" cellpadding="3"><caption><h3 align="center" style="color:#336699"><b> Movimientos de Inventarios </b></h3></caption>';
		$result .= '<table align="center" cellspacing="2" cellpadding="3"><br>';
	
		$result .= '<tr>';
		$result .= '<th class="grid_cabecera">&nbsp;</th>';
		$result .= '<th class="grid_cabecera">Formulario</th>';
		$result .= '<th class="grid_cabecera">F. Emision</th>';
		$result .= '<th class="grid_cabecera">Tipo</th>';
		$result .= '<th class="grid_cabecera">Serie</th>';
		$result .= '<th class="grid_cabecera">Numero</th>';
		$result .= '<th class="grid_cabecera">Producto</th>';
		//$result .= '<th class="grid_cabecera">COD. ARTICULO</th>';
		//$result .= '<th class="grid_cabecera">DESCRIPCI&Oacute;N</th>';
		$result .= '<th class="grid_cabecera">Cantidad</th>';
		$result .= '<th class="grid_cabecera">ALM. ORIG.</th>';
		$result .= '<th class="grid_cabecera">ALM. DEST.</th>';
		$result .= '<th class="grid_cabecera">NAT.</th>';
		$result .= '</tr>';
		$i = 0;
		foreach($listado as $codigo => $array) {
		   //echo "CODIGO : $codigo \n";
			$i++;
			$color = ($i%2==0?"grid_detalle_par":"grid_detalle_impar");

		    $result .= '<tr onmouseover="this.style.backgroundColor=\'white\'" onmouseout="this.style.backgroundColor=\'#CDCE9C\'">';
			    $result .= '<td class="'.$color.'"><input type="checkbox" name="transacciones[]" value="' . htmlentities($codigo) . '" ></td>';
			    $result .= '<td class="'.$color.'">' . htmlentities($array['mov_numero']) . '</td>';
			    $result .= '<td class="'.$color.'">' . htmlentities($array['mov_fecha']) . '</td>';
			    //$result .= '<td class="'.$color.'">' . htmlentities($array['tran_codigo']) . '</td>';
			    $result .= '<td class="'.$color.'">' . htmlentities($array['no_tipo_documento']) . '</td>';
			    $result .= '<td class="'.$color.'">' . htmlentities($array['nu_serie_documento']) . '</td>';
			    $result .= '<td class="'.$color.'">' . htmlentities($array['nu_numero_documento']) . '</td>';
			    //$result .= '<td class="'.$color.'">' . htmlentities($array['tip_doc_ref']) . '</td>';
			    $result .= '<td class="'.$color.'" align="left">' . htmlentities($array['art_codigo']) . ' - ' . htmlentities($array['art_descrip']) . '</td>';
			    //$result .= '<td class="'.$color.'">' . htmlentities($array['art_codigo']) . '</td>';
			    //$result .= '<td class="'.$color.'" align="left">' . htmlentities($array['art_descrip']) . '</td>';
			    $result .= '<td class="'.$color.'" align="right">' . htmlentities($array['mov_cantidad']) . '</td>';
			    $result .= '<td class="'.$color.'" align="center">' . htmlentities($array['alm_origen']) . '</td>';
			    $result .= '<td class="'.$color.'" align="center">' . htmlentities($array['alm_destino']) . '</td>';
			    //$result .= '<td class="'.$color.'">' . htmlentities($array['tip_doc_ref']) . '</td>';
			    $result .= '<td class="'.$color.'">' . htmlentities($array['mov_naturaleza']) . '</td>';
		    $result .= '</tr>';
		}
		$result .= '</table>';
	
		//$result .= '<table border="1" align="center" cellspacing="2" cellpadding="2"><tr>';
		$result .= '<hr><table border="0" align="center" cellspacing="2" cellpadding="3"><caption><h2 align="center" style="color:#336699"><b> Modificar datos formulario(s) </b></h2></caption><br>';

		$result .= '<tr>';
			$result .= '<td class="grid_title" align="right">Nueva Fecha Emision ';
			$result .= '</td>';
			$result .= '<td class="grid_title">';
				$result .= '<input type="text" id="dato_change" name="dato_change" value="" size="12" maxlength="10" class="form_input">';
				$result .= '<span id="resultado2"></span>';
			$result .= '</td>';	
			$result .= '<td class="grid_title">';
				$result .= '<button type="submit" id="change_date" name="change_date" value="Modificar Fecha"><img src="/sistemaweb/icons/actualizar.gif" align="right" />Cambiar Fecha</button>';
			$result .= '</td>';
		$result .= '</tr>';

		$result .= '<tr>';
			$result .= '<td><br>Nuevo Tipo Formulario </td>';
			$result .= '<td><br>';
				$result .= '<select name="new_tipotransa" size="1" class="form_combo" onChange="javascript:DisplayTipoRegul(this.value)">';	
					foreach ($tipos as $codigo => $descripcion) {
					    $result .= '<option value="' . htmlentities($codigo) . '" ';
					    if ($codigo=='18 ') $result .= "selected";
					    $result .= '>' . htmlentities($descripcion);'</option>';
					}
				$result .= '</select>';
			$result .= '</td>';
			$result .= '<td><br>';
				$result .= '<button type="submit" id="change" name="change" value="Cambiar Formulario"><img src="/sistemaweb/icons/actualizar.gif" align="right" />Cambiar Formulario</button>';
				//$result .= '<input type="submit" name="change" value="Cambiar Formulario"  class="form_button">';
			$result .= '</td>';
		$result .= '</tr>';

		/*$result .= '</tr><tr>';
		$result .= '<td colspan="2">';
		
		$result .= '<table id="tiporegul" width="100%" style="display:'.$DisplayTipo.'" border="0" cellspacing="3" cellpadding="3"><tr>';
		$result .= '<td class="form_label">';
		$result .= 'Observacion :</td><td> <textarea name="observacion" rows="1" columns="10" class="form_textarea"></textarea>';
		$result .= '</td><td class="form_label">';
		$result .= 'Tipo de Regul.</td><td><select name="tipo_regularizacion"  class="form_combo">';	
		foreach ($tiposRegu as $codigo => $descripcion) {
		    $result .= '<option value="' . htmlentities($codigo) . '" ';
		    $result .= '>' . htmlentities($descripcion);'</option>';
		}
		$result .= '</select>';
		$result .= '</td>';
		$result .= '</tr></table>';
		$result .= '</td>';
		$result .= '</tr><tr>';

		$result .= '<td colspan="2">';
		$result .= '<table width="100%" style="display:'.$DisplayOrig.'" cellspacing="3" cellpadding="3"><tr>';
		$result .= '<td class="grid_title">';
		$result .= '<input type="submit" name="change_orig" value="Modificar Origen a :"  class="form_button">';
		$result .= '</td><td>';
		$result .= '<select name="new_tipo_alm_orig" size="1" class="form_combo">';	
		foreach ($tipos_alma_ori as $codigo => $descripcion) {
		    $result .= '<option value="' . htmlentities($codigo) . '" ';
		    $result .= '>' . htmlentities($descripcion);'</option>';
		}
		$result .= '</select>';
		$result .= '</td>';
		$result .= '</tr></table>';
	
		$result .= '</td>';
		$result .= '</tr><tr>';
	
		$result .= '<td colspan="2">';
		$result .= '<table width="100%" style="display:'.$DisplayDest.'" cellspacing="10" cellpadding="1"><tr>';
		$result .= '<td class="grid_title">';
		$result .= '<input type="submit" name="change_dest" value="Modificar Destino a :"  class="form_button">';
		$result .= '</td><td>';
		$result .= '<select name="new_tipo_alm_dest" size="1" class="form_combo">';	
		foreach ($tipos_alma_des as $codigo => $descripcion) {
		    $result .= '<option value="' . htmlentities($codigo) . '" ';
		    $result .= '>' . htmlentities($descripcion);'</option>';
		}
		$result .= '</select>';
		$result .= '</td>';
		$result .= '</tr></table>';
		$result .= '</td>';	
		$result .= '</tr><tr>';

		$result .= '<tr>';
		$result .= '<td colspan="2">';
		$result .= '<table width="100%" border="0" cellspacing="1" cellpadding="1"><tr>';
		$result .= '<td class="grid_title">';
		$result .= '<input type="text" name="dato_change" value="" size="14" maxlength="12" class="form_input">';
		$result .= '</td><td>';
		$result .= '<input type="submit" name="change_date" value="Modificar Fecha" class="form_button">';
		$result .= '</td>';

	/*	
		$result .= '</td><td>';
		$result .= '<input type="submit" name="change_nro_regi" value="Modificar Nro. Referencia"  class="form_button">';
		$result .= '</td><td>';
		$result .= '<input type="submit" name="change_tipo_doc" value="Modificar Tipo Doc."  class="form_button">';
		$result .= '</td>';
		$result .= '</tr></table>';

		$result .= '</td>';	
		$result .= '</tr><tr>';

		$result .= '<td colspan="2">';
		$result .= '<table cellspacing="5" cellpadding="1"><tr>';
		$result .= '<td class="grid_title">';
		$result .= 'Cambiar transaccion completa : ';
		$result .= '</td><td>';
		$result .= '<input type="checkbox" name="completo" value="true">';
		$result .= '</td>';
		$result .= '</tr></table>';
	*/
		$result .= '</td>';
		$result .= '</tr></table>';
	
		$result .= '</form>';	
		$result .= '<div id="error_body_pie" align="center"></div>';
		return $result;
    	}
}
