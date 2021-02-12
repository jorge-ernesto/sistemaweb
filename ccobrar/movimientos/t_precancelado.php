<?php
  /*
    Templates para Tabla ccob_ta_cabecera
    @MATT
  */

class PrecanceladoTemplate extends Template {
 
  function titulo(){
    $titulo = '<div align="center"><h2>Precancelado</h2></div><hr>';
    return $titulo;
  }

  function errorResultado($errormsg){
    return '<blink>'.$errormsg.'</blink>';
  }

  function formPrecancelar($datos){
  	if(!empty($datos["ch_sucursal_precancelado"]) && !empty($datos["ch_sucursal_precancelado"])){
        	$DescSerie = PrecanceladoModel::TiposSeriesCBArray();
	}
  	if ($datos['dt_fecha_precancelado']!='' && $datos['ch_sucursal_precancelado']!=''){
    		if (!$_SESSION['autorizacion'])	$deshabilitar = "disabled";
    		else $deshabilitar="";
    	}
    	if ($datos['dt_fecha_precancelado']==''){
  		$datos['dt_fecha_precancelado']=date('d/m/Y');
  	}else{
  		$auxi = explode('-',$datos['dt_fecha_precancelado']);
  		$datos['dt_fecha_precancelado']=$auxi[2].'/'.$auxi[1].'/'.$auxi[0];
  	}
  	$auxi = explode('-',$_REQUEST['fechasaldo']);
  	$_REQUEST['fechasaldo']=$auxi[2].'/'.$auxi[1].'/'.$auxi[0];
   	$form = new form2('', 'precancelar', FORM_METHOD_POST, 'control.php', '', 'control','onSubmit = "return verificarPrecancelar();"');
    	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MOVIMIENTOS.PRECANCELACION'));
    	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'PRECANCELADO'));
    	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('datos[ch_tipdocumento]', @$datos['ch_tipdocumento']));
    	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('datos[ch_precancelado]', 'S'));
    	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('busqueda[codigo]', $_REQUEST['busqueda']['codigo']));
	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('fechasaldo', $_REQUEST['fechasaldo']));
    	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('datos[cli_codigo]', trim(@$datos['cli_codigo'])));
    	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('datos[nu_importe_precancelado]', @$datos['nu_importesaldo']));
    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center>'));
    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="3" cellpadding="3" align="center"> <tr>'));
    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="grid_item">'));
    	$form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[ch_seriedocumento]','Serie'.espacios(2).'</td><td>: ', trim(@$datos["ch_seriedocumento"]), '', 10, 10,array(),array('readonly')));
    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
    	$form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[ch_numdocumento]','Numero'.espacios(2).'</td><td>: ', trim(@$datos["ch_numdocumento"]), '', 10, 10,array(),array('readonly')));
    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
  	$form->addElement(FORM_GROUP_MAIN, new f2element_text ('dt_fecha_precancelado','Fecha'.espacios(2).'</td><td>: ', trim(@$datos["dt_fecha_precancelado"]), '', 10, 10, array(),array($deshabilitar)));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'precancelar.dt_fecha_precancelado'".');"><img src="/sistemaweb/images/showcalendar.gif" border=0></a>&nbsp;&nbsp;'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));
    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
    	$form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[ch_sucursal_precancelado]','Sucursal'.espacios(2).'</td><td>: ', @$datos["ch_sucursal_precancelado"], '', 10, 10,array("onKeyUp"=>"getRegistro(this.value);", "class"=>"form_input_numeric", "onKeyPress"=>"return validar(event,2);"),array($deshabilitar)));
    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;<div id="desc_series_doc" style="display:inline;">'.$DescSerie['Datos'][trim($datos["ch_sucursal_precancelado"])].'</div>'));
    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
    	$form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[ccob_informo]','Informo'.espacios(2).'</td><td>: ', @$datos["ccob_informo"], '', 25, 25,array(),array($deshabilitar)));
    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td align=center colspan=2>'));
    	if ($deshabilitar=='') $form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Precancelar', espacios(2)));
    	$form->addElement(FORM_GROUP_MAIN, new f2element_button('action','Regresar', espacios(2),array('onClick'=>'return volver_a_detalle();')));
    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));
    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</table>'));
    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</center>'));
    	return $form->getForm();
  }
  
  function formBuscar(){
    $form = new form2('', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control');
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MOVIMIENTOS.PRECANCELACION'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'PRECANCELADO'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('busqueda[codigo]','Codigo :', $_REQUEST['busqueda']['codigo'], espacios(2), 20, 18, array("onkeyup"=>"this.value=this.value.toUpperCase();getRegistroCli5(this.value);")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;<div id="desc_cliente" style="display:inline;"></div>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Buscar',espacios(3)));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</center>'));
    return $form->getForm();
  }

  function listado($registros){
  	 	$Money = array('01'=>'Soles',
	                   '02'=>'Dolares');
	    $titulo_grid = "PRECANCELACION DE FACTURAS";
    	$columnas = array('TIPO','NUMERO', 'CLIENTE', 'MONEDA', 'TOTAL DOC.', 'SALDO DOC.', 'A CANCELAR', 'FECHA PRECANCELADO','INFORMO');
    	$listado = '<div id="resultados_grid" class="grid" align="center"><br>
                      <table width="100%">
                      <caption class="grid_title">'.$titulo_grid.'</caption>
                      <thead align="center" valign="center" >
                      <tr class="grid_header">';
	    for($i=0;$i<count($columnas);$i++){
	      $listado .= '<th class="grid_columtitle" > '.strtoupper($columnas[$i]).'</th>';
	    }
	    $listado .= '<th>'.espacios(10).'</th><th>'.espacios(5).'</th></tr><tbody class="grid_body" width="100%">';
	    foreach($registros as $reg){
	      $reg[3] = $Money[trim($reg[3])];
	      $listado .= '<tr height="10px;" class="grid_row" '.resaltar('white','#CDCE9C').'>';
	      $regCod = trim($reg[0]).trim($reg[1]).trim($reg[2]);
	      for ($i=0; $i < count($columnas); $i++){
	            $listado .= '<td class="grid_item">'.$reg[$i].'</td>';
	      }
	      $listado .= '<td><A href="control.php?rqst=MOVIMIENTOS.PRECANCELACION&task=PRECANCELADO'.
	                  '&action=Mostrar&registroid='.$regCod.'&fechasaldo='.trim($reg[12]).'&busqueda[codigo]='.$_REQUEST['busqueda']['codigo'].'" target="control"><img src="/acosa/icons/open.gif" alt="Editar" align="middle" border="0"/></A></td>';
	      if ($_SESSION['autorizacion']){
	      	$listado .= '<td><A href="control.php?rqst=MOVIMIENTOS.PRECANCELACION&task=PRECANCELADO'.
	                  '&action=Quitar&registroid='.$regCod.'&busqueda[codigo]='.$_REQUEST['busqueda']['codigo'].'" target="control"><img src="/acosa/icons/delete.gif" alt="Eliminar" align="middle" border="0" onClick="if (confirm('."'Desea quitar la precancelacion?'".')) return true; else return false;"/></A></td>';
	      }
	      $listado .= '</tr>';
	    }
	    $listado .= '</tbody></table></div>';
	    return $listado;
  }
  
  function setRegistros($codigo){
    $RegistrosCB = PrecanceladoModel::TiposSeriesCBArray($codigo);
    $result = '<blink><span class="MsgError">Error..</span></blink>';
    if (count($RegistrosCB['Datos']) == 1) {
      foreach($RegistrosCB['Datos'] as $cod => $descri){
        $result = $descri." <script language=\"javascript\">top.setRegistro('".trim($cod)."');</script>";
      }
    }
    if (count($RegistrosCB['Datos']) > 1){
      $att_opt = array();
      foreach($RegistrosCB['Datos'] as $cod => $descri){
        $att_opt[trim($cod)] = array(" onclick"=>"getRegistro('".trim($cod)."');");
      }
      $cb = new f2element_combo('cbDatosSeries', '','', $RegistrosCB['Datos'],'',array("size"=>"5"), array(), $att_opt);
      $result = '<div align="left" style="position: absolute; width:250px;  z-index:1000;">'.$cb->getTag().'</div>';
    }
    return $result;
  }
  function setRegistrosCliente($codigo){
    $RegistrosCB = PrecanceladoModel::ClientesCBArray("trim(cli_codigo)||''||trim(cli_razsocial) ~ '".pg_escape_string($codigo)."'");
    $result = '<blink><span class="MsgError">Error..</span></blink>';
    if (count($RegistrosCB) == 1) {
      foreach($RegistrosCB as $cod => $descri){
      	$result = $descri." <script language=\"javascript\">top.setRegistroCli('".trim($cod)."');</script>";
      }
    }
    if (count($RegistrosCB) > 1){
      $att_opt = array();
      foreach($RegistrosCB as $cod => $descri){
        $att_opt[trim($cod)] = array("onclick"=>"getRegistroCli5('".trim($cod)."');");
      }
      $cb = new f2element_combo('cbDatosCliente', '','', $RegistrosCB,'',array("size"=>"5"), array(), $att_opt);
      $result = '<div align="left" style="position: absolute; width:250px;  z-index:1000;">'.$cb->getTag().'</div>';
    }
    return $result;
  }
  
}

