<?php

class SalditosTemplate extends Template {
 
  	function titulo() {
    		$titulo = '<div align="center"><h2>Cancelaciones de Saldos - Fecha Cancelacion: '.$_SESSION['fec_aplicacion'].'</h2></div><hr>';
    		return $titulo;
  	}

  	function errorResultado($errormsg) {
    		return '<blink>'.$errormsg.'</blink>';
  	}

  	function formSalditos() {
  	
  		if (strchr($_REQUEST['fechaemision'],'-')){
  			$aux1 = explode('-',$_REQUEST['fechaemision']);
  	  		$_REQUEST['fechaemision']=$aux1[2].'/'.$aux1[1].'/'.$aux1[0];
  		}
  	 	  	
  	  	$form = new form2('', 'Salditos', FORM_METHOD_POST, 'control.php', '', 'control','onSubmit = "return verificar_cancelacion();"');
  	  	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MOVIMIENTOS.SALDITOS'));
   	  	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'SALDITOS'));
   	  	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('registroid', $_REQUEST['registroid']));
   	  	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('busqueda[codigo]', $_REQUEST['busqueda']['codigo']));
   	  	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('saldo', $_REQUEST['saldo']));
   	  	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('fechaemision', $_REQUEST['fechaemision']));
   	  	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('tipo', $_REQUEST['tipo']));
   	  	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('serie', $_REQUEST['serie']));
   	  	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('numero', $_REQUEST['numero']));
   	  	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('importe', $_REQUEST['importe']));
 	  	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('fecha', $_SESSION['fec_aplicacion']));
 	  	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('codigo', $_REQUEST['codigo']));
 	  	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('razsocial', $_REQUEST['razsocial']));
   	  	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center>'));
   	  	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellpadding="1" cellspacing="1">'));
   	  	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="form_label"><b>Cliente</b></td>'));
      		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="form_label">:'.espacios(3).' '.@$_REQUEST["codigo"].' - '.@$_REQUEST['razsocial'].'</td></tr></table>'));
      		$form->addGroup('doc_cargo', 'DOCUMENTO');
      		$form->addElement('doc_cargo', new f2element_freeTags('<table border="0" cellspacing="5" cellpadding="5" align="center"> <tr>'));
      		$form->addElement('doc_cargo', new f2element_freeTags('<td class="grid_item"><b>Tipo</b><br>'));
      		$form->addElement('doc_cargo', new f2element_freeTags(''.@$_REQUEST['tipo'].'</td>'));
      		$form->addElement('doc_cargo', new f2element_freeTags('<td class="form_label" align="center"><b>Serie</b><br>'));
      		$form->addElement('doc_cargo', new f2element_freeTags(''.@$_REQUEST['serie'].'</td>'));
      		$form->addElement('doc_cargo', new f2element_freeTags('<td class="form_label" align="center"><b>N&uacute;mero</b><br>'));
      		$form->addElement('doc_cargo', new f2element_freeTags(''.@$_REQUEST['numero'].'</td>'));
      		$form->addElement('doc_cargo', new f2element_freeTags('<td class="form_label" align="center"><b>Fecha Emision</b><br>'));
      		$form->addElement('doc_cargo', new f2element_freeTags(''.@$_REQUEST['fechaemision'].'</td>'));
      		$form->addElement('doc_cargo', new f2element_freeTags('<td class="form_label" align="center"><b>Total</b><br>'));
      		$form->addElement('doc_cargo', new f2element_freeTags(''.@$_REQUEST['importe'].'</td>'));
      		$form->addElement('doc_cargo', new f2element_freeTags('<td class="form_label" align="center"><b>Saldo</b><br>'));
      		$form->addElement('doc_cargo', new f2element_freeTags(''.@$_REQUEST['saldo'].'</td>'));
      		$form->addElement('doc_cargo', new f2element_freeTags('</tr></table>'));
      		$form->addGroup('doc_detalle', 'CANCELAR');
   	  	$form->addElement('doc_detalle', new f2element_freeTags('<table border=0><tr><td align="right">'));
   	  	$form->addElement('doc_detalle', new f2element_text ('monto','Monto a Cancelar </td><td>:', $_REQUEST['saldo'], espacios(2), 15, 15,array('onkeypress'=>'return validar(event,3);')));
   	  	$form->addElement('doc_detalle', new f2element_freeTags('</td></tr><tr><td align="right">'));
	  	$form->addElement('doc_detalle', new f2element_text ('tipo_doc','Tipo Doc. Referencia</td><td>:', $_REQUEST['tipo'], espacios(2), 15, 15,''));
   	  	$form->addElement('doc_detalle', new f2element_freeTags('</td></tr><tr><td align="right">'));
		$form->addElement('doc_detalle', new f2element_text ('num_doc','Nro. de Doc. Referencia</td><td>:', $_REQUEST['numero'], espacios(2), 15, 10,''));
		$form->addElement('doc_detalle', new f2element_freeTags('</td></tr><tr><td align="right">'));
		$form->addElement('doc_detalle', new f2element_text ('caja','Comprobante de Caja</td><td>:', '', '', 15, 10,''));
		$form->addElement('doc_detalle', new f2element_freeTags('</td></tr><tr><td align="right">'));
		$form->addElement('doc_detalle', new f2element_text ('glosa','Glosa</td><td>:', '', '', 40, 40,''));
		$form->addElement('doc_detalle', new f2element_freeTags('</td></tr><tr><td align="center" colspan="4">'));
		$form->addElement('doc_detalle', new f2element_submit('action','Cancelar Saldos',espacios(3)));
		$form->addElement('doc_detalle', new f2element_freeTags('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'));
		$form->addElement('doc_detalle', new f2element_button('action','Regresar', espacios(2),array('onClick'=>'volver_atras('."'".$_REQUEST['busqueda']['codigo']."'".');')));
		$form->addElement('doc_detalle', new f2element_freeTags('</td></tr></table></center>'));
		$form->addElement('doc_detalle', new f2element_freeTags('<br>'));
	  	
	  	return $form->getForm();
  	}
  
    	function formInterfaz() {
	  	if (!$_SESSION['fec_aplicacion']){
	  		$_SESSION['fec_aplicacion']=date('d/m/Y');
	  	}
  		$form = new form2('', 'Interfaz', FORM_METHOD_POST, 'control.php', '', 'control','onSubmit = "return verificar_interfaz();"');
    		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MOVIMIENTOS.SALDITOS'));
    		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'SALDITOS'));
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
  
  	function listadoDocumentos($registros){
    		$Money = array('01'=>'Soles', '02'=>'Dolares');

    		$titulo_grid = "CANCELACIONES DE DOCUMENTOS";
    		//formulario de busqueda
    		$columnas = array('COD. CLIE.','RAZON SOCIAL', 'TIPO','SERIE','NUMERO', 'FECHA EMISION', 'FECHA SALDO', 'MONEDA','TOTAL','SALDO');
    		$listado = '<div id="resultados_grid" class="grid" align="center"><br>
		              <table>
		              <caption class="grid_title">'.$titulo_grid.'</caption>
		              <thead align="center" valign="center" >
		              <tr class="grid_header">';
		              
    		for($i=0;$i<count($columnas);$i++)  {
      			$listado .= '<th class="grid_columtitle"> '.strtoupper($columnas[$i]).'</th>';
    		}
    		$listado .= '<th>'.espacios(10).'</th><th>'.espacios(5).'</th></tr><tbody class="grid_body" style="height:250px;">';

    		//detalle
    		foreach($registros as $reg)  {
      			$auximon = $reg[7];
      			$reg[7] = $Money[trim($reg[7])];
      			$listado .= '<tr height="10px;" class="grid_row" '.resaltar('white','#CDCE9C').'>';
      			$regCod = trim($reg["cli_codigo"]).trim($reg["ch_tipdocumento"]).trim($reg["ch_seriedocumento"]).$reg["ch_numdocumento"];
      			
      			for ($i=0; $i < count($columnas); $i++){
            			$listado .= '<td class="grid_item">'.$reg[$i].'</td>';
      			}
      			
      			$listado .= '<td><A href="control.php?rqst=MOVIMIENTOS.SALDITOS&task=SALDITOS'.
                 			'&action=Cancelacion&registroid='.trim($regCod).'&busqueda[codigo]='.$_REQUEST['busqueda']['codigo'].
                  			'&tipo='.trim($reg["ch_tipdocumento"]).'&codigo='.trim($reg["cli_codigo"]).'&serie='.trim($reg['ch_seriedocumento']).'&numero='.
                  			trim($reg['ch_numdocumento']).'&importe='.trim($reg['nu_importetotal']).'&saldo='.trim($reg['nu_importesaldo']).'&fechaemision='.trim($reg['dt_fechaemision']).'&razsocial='.trim($reg['cli_razsocial']).'" target="control"><img src="/sistemaweb/icons/open.gif" alt="Editar" align="middle" border="0"/></A></td>';
                  			//'&action=Aplicacion&registroid='.trim($reg["cli_codigo"]).'&tipo='.trim($reg["ch_tipdocumento"]).'" target="control"><img src="/sistemaweb/icons/open.gif" alt="Editar" align="middle" border="0"/></A></td>';
      			$listado .= '</tr>';
    		}
    		$listado .= '</tbody></table></div>';
    		
    		return $listado;
  	}

  	function formBuscar(){
    		$form = new form2('', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control');
    		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MOVIMIENTOS.SALDITOS'));
    		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'SALDITOS'));
    		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center>'));
    		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('busqueda[codigo]','Codigo :', $_REQUEST['busqueda']['codigo'], espacios(2), 20, 18, array("onkeyup"=>"this.value=this.value.toUpperCase();getRegistroCli(this.value);")));
    		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;<div id="desc_cliente" style="display:inline;"></div>'));
    		$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Buscar',espacios(3)));
    		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</center>'));
    		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));
    		
    		return $form->getForm();
  	}
        
  	function setRegistrosCliente($codigo){
    		$RegistrosCB = SalditosModel::ClientesCBArray("trim(cli_codigo)||''||trim(cli_razsocial) ~ '".pg_escape_string($codigo)."'");

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
