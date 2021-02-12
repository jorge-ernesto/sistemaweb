<?php

class AplicacionesTemplate extends Template {
 
  	function titulo() {
	    	$titulo = '<div align="center"><h2>Aplicaciones - Fecha Aplicacion: '.$_SESSION['fec_aplicacion'].'</h2></div><hr>';
	    	return $titulo;
  	}

  	function errorResultado($errormsg){
    		return '<blink>'.$errormsg.'</blink>';
  	}

  	function formInterfaz() {
  		if (!$_SESSION['fec_aplicacion']) {
  			$_SESSION['fec_aplicacion'] = date('d/m/Y');
  		}
  		$form = new form2('', 'Interfaz', FORM_METHOD_POST, 'control.php', '', 'control','onSubmit = "return verificar_interfaz();"');
	    	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MOVIMIENTOS.APLICACIONES'));
	    	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'APLICACIONES'));
	    	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('hoy', date('d/m/Y')));
	    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center>'));
	    	$form->addElement(FORM_GROUP_MAIN, new f2element_text ('fecha','Fecha :', date('d/m/Y'), espacios(2), 10, 10,array(),array('readonly')));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'Interfaz.fecha'".');"><img src="/sistemaweb/images/showcalendar.gif" border=0></a>&nbsp;&nbsp;'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Ingresar',espacios(3)));
	    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</center>'));
	    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));

	    	return $form->getForm();
  	}
  
  	function listadoCargos($registros) {
		$Money = array('01'=>'Soles',
                		'02'=>'Dolares');

    		$titulo_grid = "APLICACIONES DE CLIENTES";
    		//formulario de busqueda
    		$columnas = array('COD. CLIE.','RAZON SOCIAL', 'TIPO','SERIE','NUMERO', 'FECHA EMISION', 'FECHA SALDO', 'MONEDA','TOTAL','SALDO');
    		$listado = '<div id="resultados_grid" class="grid" align="center"><br>
		              <table>
		              <caption class="grid_title">'.$titulo_grid.'</caption>
		              <thead align="center" valign="center" >
		              <tr class="grid_header">';

    		for($i=0;$i<count($columnas);$i++) {
      			$listado .= '<th class="grid_columtitle"> '.strtoupper($columnas[$i]).'</th>';
    		}
    		$listado .= '<th>'.espacios(10).'</th><th>'.espacios(5).'</th></tr><tbody class="grid_body" style="height:250px;">';

    		//detalle
    		foreach($registros as $reg) {
      			$auximon = $reg[7];
      			$reg[7] = $Money[trim($reg[7])];
      			$listado .= '<tr height="10px;" class="grid_row" '.resaltar('white','#CDCE9C').'>';
      			$regCod = trim($reg["cli_codigo"]).trim($reg["ch_tipdocumento"]).trim($reg["ch_seriedocumento"]).$reg["ch_numdocumento"];
      			for ($i=0; $i < count($columnas); $i++){
            			$listado .= '<td class="grid_item">'.$reg[$i].'</td>';
      			}
	      		$listado .= '<td><A href="control.php?rqst=MOVIMIENTOS.APLICACIONES&task=APLICACIONES'.
		          		'&action=Aplicacion&clicodigo=' . trim($reg['cli_codigo']) . '&registroid='.trim($regCod).'&busqueda[codigo]='.$_REQUEST['busqueda']['codigo'].'&tipo='.trim($reg["ch_tipdocumento"]).'&monedaza='.$auximon.'" target="control"><img src="/sistemaweb/icons/open.gif" alt="Editar" align="middle" border="0"/></A></td>';
		          		//'&action=Aplicacion&registroid='.trim($reg["cli_codigo"]).'&tipo='.trim($reg["ch_tipdocumento"]).'" target="control"><img src="/acosa/icons/open.gif" alt="Editar" align="middle" border="0"/></A></td>';
	      		$listado .= '</tr>';
    		}
    		$listado .= '</tbody></table></div>';

    		return $listado;
  	}

  	function formBuscar($paginacion) {
    		$form = new form2('', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control');
    		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MOVIMIENTOS.APLICACIONES'));
    		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'APLICACIONES'));
    		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center>'));
    		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('busqueda[codigo]','Codigo :', $_REQUEST['busqueda']['codigo'], espacios(2), 20, 18, array("onkeyup"=>"this.value=this.value.toUpperCase();getRegistroCli(this.value);")));
    		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;<div id="desc_cliente" style="display:inline;"></div>'));
    		$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Buscar',espacios(3)));
    		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</center>'));
    		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));

    		return $form->getForm();
  	}
   
  	function listarAbonos($datos) {
    		$Money = array('01'=>'Soles',
                   		'02'=>'Dolares');

    		$listado = '<table><thead><tr class="grid_header">';
    		$columnas = array('TIPO','SERIE','NUMERO','FECHA EMISION','FECHA SALDO','TOTAL','MONEDA',' SALDO');

    		foreach($columnas as $col) {
      			$listado .= '<th class="grid_columtitle">'.$col.'</th>';
    		}

    		$listado .= '<th class="grid_columtitle">'.espacios(17).'</th>
                 		</tr></thead><tbody class="grid_body" style="height:'.(30*count($datos)).'px">';
    		$acumulado_abono = 0;

    		foreach($datos as $dato) {
      			$regCod = $dato["ch_tipdocumento"];
      			$auximon = trim($dato["ch_moneda"]);
      			$dato["ch_moneda"] = $Money[trim($dato["ch_moneda"])];
      			$listado .= '<tr class="grid_row" '.resaltar('white','#CDCE9C').'>
                			<td class="grid_item">'.$dato["ch_tipdocumento"].'</td>
					<td class="grid_item">'.$dato["ch_seriedocumento"].'</td>
					<td class="grid_item">'.$dato["ch_numdocumento"].'</td>
					<td class="grid_item">'.$dato["dt_fechaemision"].'</td>
					<td class="grid_item">'.$dato["dt_fechasaldo"].'</td>
					<td class="grid_item" align="right">'.number_format($dato["nu_importetotal"], 2, '.', ',').'</td>
					<td class="grid_item">'.$dato["ch_moneda"].'</td>
					<td class="grid_item" align="right" >'.number_format($dato["nu_importesaldo"], 2, '.', ',').'</td>
					<td class="grid_item">';
      			$listado .= ($dato["dt_fechasaldo"]!=0 && $dato["nu_importetotal"]>=$dato["nu_importesaldo"])  ?'<input type="hidden" name="oculto[]" value="'.$auximon.'" /><input type="checkbox" name="calcular[]" value="{'.trim($dato["nu_importesaldo"]).'}{'.trim($dato["ch_tipdocumento"]).'}{'.trim($dato["ch_seriedocumento"]).trim($dato["ch_numdocumento"]).'}" onChange="setCalcularAplicaciones(this);">':'&nbsp;';
      			if ($dato["dt_fechasaldo"]!=0 && $dato["nu_importetotal"]>=$dato["nu_importesaldo"] && $auximon==$_REQUEST['monedaza'])  
				$acumulado_abono+=$dato["nu_importesaldo"];      
     		}     
    		$listado .= '<input type="hidden" name="monto_oculto" value="'.$acumulado_abono.'" /></tbody></table>';

    		return $listado;
  	}
  
  	function verTotales($montos = array()) {

    		if($montos['TOTAL SALDO ABONO'] > $montos['TOTAL IMPORTE SALDO']) {
        		$addTd = '<div class="MsgError"><blink>El Saldo es mayor al Importe Total del Saldo<blink></div>';
    		}

    		$totales = '<table border="0"><tbody class="grid_body"><tr class="grid_row">
      				<td class="grid_item">
       				 TOTAL SALDO
        			<input type="hidden" name="TotalSaldoAbono" value="'.$montos["TOTAL SALDO ABONO"].'" >
      				</td>
      				<tr class="grid_row">
     				<td class="grid_item" align="center">';
    		$totales.= number_format($montos["TOTAL SALDO ABONO"],2,'.',',').'</td></tr></tbody></table>';
    		$totales.= @$addTd;

    		return $totales;
  	}

  	function formAplicaciones($datos,$abonos_detalles) {

    		$Money = array('01'=>'Soles',
		   	'02'=>'Dolares');

    		$form = new form2('APLICACIONES', 'form_aplicaciones', FORM_METHOD_POST, 'control.php', '', 'control',' onSubmit = "return validar_aplicacion();"');
    		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MOVIMIENTOS.APLICACIONES'));
    		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'APLICACIONES'));
    		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('registroid', $_REQUEST['registroid']));
    		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('busqueda[codigo]', $_REQUEST['busqueda']['codigo']));
    		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('fecha', $_SESSION['fec_aplicacion']));
    		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('monedac', $datos['ch_moneda']));
    		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('monedaza', $_REQUEST['monedaza']));
    		$auxi1 = explode('-',@$datos['dt_fechaemision']);
    		$datos['dt_fechaemision'] = $auxi1[2].'/'.$auxi1[1].'/'.$auxi1[0];
    		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('datos[dt_fechaemision]', @$datos['dt_fechaemision']));
    		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="5" cellpadding="5"> <tr>'));
    		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="form_label"><b>Cliente</b></td>'));
    		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="form_label">:'.espacios(3).' '.@$datos["cli_codigo"].' - '.@$datos['cli_razsocial'].'</td>'));
    		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr></table>'));
    		$form->addGroup('doc_cargo', 'DOCUMENTO DE CARGO');
    		$form->addElement('doc_cargo', new f2element_freeTags('<table border="0" cellspacing="5" cellpadding="5" align="center"> <tr>'));
    		$form->addElement('doc_cargo', new f2element_freeTags('<td class="grid_item"><b>Tipo</b><br>'));
    		$form->addElement('doc_cargo', new f2element_freeTags(''.@$datos['ch_tipdocumento'].'</td>'));
    		$form->addElement('doc_cargo', new f2element_hidden('ch_tipdocumento', @trim($datos['ch_tipdocumento'])));
    		$form->addElement('doc_cargo', new f2element_freeTags('<td class="form_label" align="center"><b>Serie</b><br>'));
    		$form->addElement('doc_cargo', new f2element_freeTags(''.@$datos['ch_seriedocumento'].'</td>'));
    		$form->addElement('doc_cargo', new f2element_freeTags('<td class="form_label" align="center"><b>N&uacute;mero</b><br>'));
    		$form->addElement('doc_cargo', new f2element_freeTags(''.@$datos['ch_numdocumento'].'</td>'));
    		$form->addElement('doc_cargo', new f2element_hidden('ch_numdocumento', @trim($datos["ch_seriedocumento"]).@trim($datos['ch_numdocumento'])));
    		$form->addElement('doc_cargo', new f2element_freeTags('<td class="form_label" align="center"><b>Fecha Emision</b><br>'));
    		$form->addElement('doc_cargo', new f2element_freeTags(''.@$datos['dt_fechaemision'].'</td>'));
    		$form->addElement('doc_cargo', new f2element_freeTags('<td class="form_label" align="center"><b>Fecha Saldo</b><br>'));
    		$form->addElement('doc_cargo', new f2element_freeTags(''.@$datos['dt_fechasaldo'].'</td>'));
    		$form->addElement('doc_cargo', new f2element_freeTags('<td class="form_label" align="center"><b>Moneda</b><br>'));
    		$form->addElement('doc_cargo', new f2element_freeTags(''.@$Money[$datos['ch_moneda']].'</td>'));
    		$form->addElement('doc_cargo', new f2element_freeTags('<td class="form_label" align="center"><b>Total</b><br>'));
    		$form->addElement('doc_cargo', new f2element_freeTags(''.@$datos['nu_importetotal'].'</td>'));
    		$form->addElement('doc_cargo', new f2element_freeTags('<td class="form_label" align="center"><b>Saldo</b><br>'));
    		$form->addElement('doc_cargo', new f2element_freeTags(''.@$datos['nu_importesaldo'].'</td>'));
    		$form->addElement('doc_cargo', new f2element_hidden('nu_importesaldo', @$datos["nu_importesaldo"]));
    		$form->addElement('doc_cargo', new f2element_freeTags('</tr></table>'));
    		$form->addGroup('doc_monto', 'DOCUMENTOS DE ABONO');
    		$form->addElement('doc_monto', new f2element_checkbox('chkpormonto', '', 'S', '', array('onClick'=>"verificar_check(this,'APLICACIONES');")));
    		$form->addElement('doc_monto', new f2element_text ('monto','Monto a aplicar: ', '0.0', '', 8, 8,array("class"=>"form_input_numeric",'onkeypress'=>'return validar(event,3);'),array('disabled')));
   		$form->addElement('doc_cargo', new f2element_freeTags('&nbsp;&nbsp;&nbsp;'));
    		$form->addElement('doc_monto', new f2element_checkbox('chkpormontonota', '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    		Aplicar Monto a Nota de Credito', 'S', '', array('onClick'=>"aplicar_por_monto_nota();")));
    		$form->addElement('doc_monto', new f2element_freeTags('<table border="0" cellspacing="3" cellpadding="3" align="center"> <tr><td class="form_td_title">'));
    		$form->addElement('doc_monto', new f2element_freeTags ('<div id="detaAbono"  align="center">'.AplicacionesTemplate::listarAbonos($abonos_detalles).'</div>'));
    		$form->addElement('doc_monto', new f2element_freeTags ('<div id="Totales" align="center" >'.AplicacionesTemplate::verTotales(/*AplicacionesModel::getAsientoContable($asc)*/).'</div>'));
    		$form->addElement('doc_monto', new f2element_freeTags ('<div id="resulta"  align="left" class="form_label"> Ultimo Monto Aplicado: '.$_REQUEST['TotalSaldoAbono'].'</div>'));
    		$form->addElement('doc_monto', new f2element_freeTags('</td></tr></table>'));
    		$form->addGroup ('buttons', '');
    		$form->addElement('buttons', new f2element_submit('action','Aplicar', espacios(2)));
    		$form->addElement('buttons', new f2element_button('action','Regresar', espacios(2),array('onClick'=>'document.form_aplicaciones.submit();')));

    		return $form->getForm().'<div id="error_body" align="center"></div><hr>';
  	}

  	function setRegistrosCliente($codigo) {
    		$RegistrosCB = AplicacionesModel::ClientesCBArray("trim(cli_codigo)||''||trim(cli_razsocial) ~ '".pg_escape_string($codigo)."'");
    		//$result = '<blink><span class="MsgError">Ingresar codigo de cliente ejm: X-XXXX</span></blink>';
    		if (count($RegistrosCB) == 1) {
      			foreach($RegistrosCB as $cod => $descri){
      				$result = $descri." <script language=\"javascript\">top.setRegistroCli('".trim($cod)."');</script>";
      			}
    		}
    		if (count($RegistrosCB) > 1){
      			$att_opt = array();
      			foreach($RegistrosCB as $cod => $descri){
        			$att_opt[trim($cod)] = array("onclick"=>"getRegistroCli('".trim($cod)."');");
      			}
     			 $cb = new f2element_combo('cbDatosCliente', '','', $RegistrosCB,'',array("size"=>"5"), array(), $att_opt);
      			$result = '<div align="left" style="position: absolute; width:250px;  z-index:1000;">'.$cb->getTag().'</div>';
    		}

    		return $result;
  	}
}
