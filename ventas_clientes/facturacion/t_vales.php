<?php
  /*
    Templates para Tablas Generales
    @TBCA
  */

class ValesTemplate extends Template {
 
  function titulo(){
    $titulo = '<div align="center"><h2>CONTROL DE VALES</h2></div><hr>';
    return $titulo;
  }

  function errorResultado($errormsg){
    return '<center><blink>'.$errormsg.'</blink></center>';
  }
  
  function formBuscar(){
    $form = new form2('', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control');
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'FACTURACION.VALES'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'LISTADO'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('busqueda[codigo]','Codigo :', $_REQUEST['busqueda']['codigo'], espacios(2), 20, 18, array("onkeyup"=>"javascript:this.value=this.value.toUpperCase();getRegistroCli(this.value);")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;<div id="desc_cliente" style="display:inline;"></div>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Buscar',espacios(3)));
    $form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Agregar',espacios(3), array('onclick'=>'quitar_grid();')));
    $form->addElement(FORM_GROUP_MAIN, new f2element_radio ('busqueda[radio]','Cliente', '0', espacios(2), array('onclick'=>'limpiar_caja_busqueda();'),($_REQUEST['busqueda']['radio']=='0'?array('checked'):(is_null($_REQUEST['busqueda']['radio'])?array('checked'):array()))));
    $form->addElement(FORM_GROUP_MAIN, new f2element_radio ('busqueda[radio]','Tipo', '1', espacios(2),array('onclick'=>'limpiar_caja_busqueda();'),($_REQUEST['busqueda']['radio']=='1'?array('checked'):array())));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</center>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));
    return $form->getForm();
  }

  function formAgregar($vales=array()){
  	$form = new form2('', 'form_vales', FORM_METHOD_POST, 'control.php', '', 'control','onSubmit="return validar_registro_vales();"');
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'FACTURACION.VALES'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'LISTADO'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('registroid', @$vales["ch_tarjeta"]));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="0" cellpadding="0"> <tr><td bgcolor="#FFFFCD">'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="2" cellpadding="2"> <tr><td colspan="2" align="center" class="form_td_title">'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('vales[ch_cliente]','C&oacute;digo de Cliente </td><td>: ', @$vales["ch_cliente"], '', 7, 6, array("onKeyUp"=>"this.value=this.value.toUpperCase();getRegistro(this.value);", "class"=>"form_input_numeric")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags(''.espacios(2).'<div id="desc_cliente" style="display:inline;"></div>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('vales[ch_tipovale]','Tipo de Vale</td><td>: ', @$vales["ch_tipovale"], '', 2, 2, array("onKeyUp"=>"getRegistroVale(this.value);","onKeyPress"=>"return validar(event,2);","class"=>"form_input_numeric")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags(''.espacios(2).'<div id="desc_vales" style="display:inline;"></div>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td valign="top">'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('vales[ch_numero_inicio]','N&uacute;mero Inicio </td><td>: ', @$vales["ch_numero_inicio"], '', 11, 10, array("class"=>"form_input_numeric"),array('readonly')));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="2" align="right">'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Guardar', espacios(15)));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td></td></tr></table>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td bgcolor="#FFFFCD" valign="top" width="25">'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td bgcolor="#FFFFCD" valign="top">'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="2" cellpadding="2"> <tr><td colspan="2" align="center" class="form_td_title">'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text('vales[ch_tarjeta]','Tarjeta </td><td>: ', trim(@$vales["ch_tarjeta"]), '', 11,10,array(),array('readonly')));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text('importe','Importe </td><td>: ', '', '', 11,10,array(),array('readonly')));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('vales[ch_numero_fin]','N&uacute;mero Fin </td><td>: ', @$vales["ch_numero_fin"], '', 11, 10, array("class"=>"form_input_numeric","onKeyPress"=>"return validar(event,2);")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_button('action','Regresar', espacios(2),array('onclick'=>'return regresar_a_lista();')));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table></td></tr></table>'));
    return $form->getForm().'<div id="error_body" align="center"></div><hr>';
  }
  
  function formEditar($datos){
  	$form = new form2('', 'form_vales', FORM_METHOD_POST, 'control.php', '', 'control');
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'FACTURACION.VALES'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'LISTADO'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('busqueda[codigo]', $_REQUEST['busqueda']['codigo']));
	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('busqueda[radio]', $_REQUEST['busqueda']['radio']));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="5" cellpadding="5"> <tr>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="form_label"><b>Cliente</b></td>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="form_label">:'.espacios(3).$_REQUEST['cliente'].'</td>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="form_label"><b>Tarjeta</b></td>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="form_label">:'.espacios(3).$_REQUEST['tarjeta'].'</td>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr></table>'));
    $form->addGroup('doc_cargo', 'DATOS DE TARJETA');
    $form->addElement('doc_cargo', new f2element_freeTags('<table border="0" cellspacing="5" cellpadding="5" align="center"> <tr>'));
    $form->addElement('doc_cargo', new f2element_freeTags('<td class="grid_item"><b>TIPO DE VALE</b><br>'));
    $form->addElement('doc_cargo', new f2element_freeTags(''.$_REQUEST['desctipo'].'</td>'));
    $form->addElement('doc_cargo', new f2element_freeTags('<td class="form_label" align="center"><b>NUMERO DE INICIO</b><br>'));
    $form->addElement('doc_cargo', new f2element_freeTags(''.$_REQUEST['inicio'].'</td>'));
    $form->addElement('doc_cargo', new f2element_freeTags('<td class="form_label" align="center"><b>NUMERO DE FIN</b><br>'));
    $form->addElement('doc_cargo', new f2element_freeTags(''.$_REQUEST['fin'].'</td>'));
    $form->addElement('doc_cargo', new f2element_freeTags('<td class="form_label" align="center"><b>FECHA DE ENTREGA</b><br>'));
    $form->addElement('doc_cargo', new f2element_freeTags(''.$_REQUEST['fecha'].'</td>'));
    $form->addElement('doc_cargo', new f2element_freeTags('</tr></table>'));
    $form->addGroup('doc_monto', 'DETALLE DE VALES');
    $form->addElement('doc_monto', new f2element_freeTags('<table border="0" cellspacing="3" cellpadding="3" align="center"><thead>'));
    $form->addElement('doc_monto', new f2element_freeTags ('<tr><td class="grid_row"><b>NUMERO DE VALE</b></td><td class="grid_row"><b>FECHA DE CONSUMO</b></td><td class="grid_row"><b>ESTACION</b></td><td class="grid_row"><b>IMPORTE</b></td><td class="grid_row"><b>BLOQUEADO</b></td><td class="grid_row"><b>CONSUMIDO</b></td><td class="grid_row"><b>ACCION</b></td></tr></thead><tbody>'));
    for($i=0; $i<count($datos); $i++){
    	$form->addElement('doc_monto', new f2element_freeTags ('<tr '.resaltar('white','#CDCE9C').'><td class="grid_item" align="center">'.$datos[$i]['ch_numerovale'].'</td><td class="grid_item">'.$datos[$i]['ch_fecha_consumo'].'</td><td class="grid_item">'.$datos[$i]['ch_nombre_breve_sucursal'].'</td><td class="grid_item">'.$datos[$i]['nu_importe'].'</td><td class="grid_item" align="center">'.$datos[$i]['ch_bloqueado'].'</td><td class="grid_item" align="center">'.$datos[$i]['ch_consumido'].'</td><td class="grid_item">'));
    	if ($_SESSION['autorizacion'] && $datos[$i]['ch_estacion']=='')
    					$form->addElement('doc_monto', new f2element_freeTags((is_null($datos[$i]['ch_sucursal']))?'&nbsp;&nbsp;<a href="control.php?rqst=FACTURACION.VALES&task=LISTADO&action=Bloquear&cliente='.$_REQUEST['cliente'].'&desctipo='.$_REQUEST['desctipo'].'&tarjeta='.$_REQUEST['tarjeta'].'&tipo='.$_REQUEST['tipo'].'&inicio='.trim($_REQUEST['inicio']).'&fin='.trim($_REQUEST['fin']).'&fecha='.$_REQUEST['fecha'].'&vale='.$datos[$i]['ch_numerovale'].
                 	 	'" onclick="return confirm('."'Desea bloquear el vale?'".');" target="control"><img src="/sistemaweb/icons/candado_bloquear.gif" alt="Bloquear" align="middle" border="0" width="17px" height="17px"/></a> &nbsp;&nbsp; <a href="control.php?rqst=FACTURACION.VALES&task=LISTADO&action=Desbloquear&cliente='.$_REQUEST['cliente'].'&desctipo='.$_REQUEST['desctipo'].'&tarjeta='.$_REQUEST['tarjeta'].'&tipo='.$_REQUEST['tipo'].'&inicio='.trim($_REQUEST['inicio']).'&fin='.trim($_REQUEST['fin']).'&fecha='.$_REQUEST['fecha'].'&vale='.$datos[$i]['ch_numerovale'].
                 	 	'" onclick="return confirm('."'Desea Desbloquear el vale?'".');" target="control"><img src="/sistemaweb/icons/open.gif" alt="DesBloquear" align="middle" border="0" width="17px" height="17px"/></a>':''));
        $form->addElement('doc_monto', new f2element_freeTags('</td></tr>'));         	 
    }
    $form->addElement('doc_monto', new f2element_freeTags('</tbody></table>'));
    $form->addGroup ('buttons', '');
    $form->addElement('buttons', new f2element_submit('action','Regresar', espacios(2)));	 
  	return $form->getForm();
  }
  
  function listado($registros){
    
    $titulo_grid = "LISTADO DE TARJETAS/VALES";
    
    $columnas = array('CLIENTE','RAZON SOCIAL', 'MONTO DE VALE','NUMERO INICIO','NUMERO FIN', 'FECHA ENTREGA', 'REPLICADO','TIPO VALE', 'TARJETA');
    $listado = '<br>
                      <table>
                      <caption class="grid_title">'.$titulo_grid.'</caption>
                      <thead align="center" valign="center" >
                      <tr class="grid_header">';
    for($i=0;$i<count($columnas);$i++){
      $listado .= '<th class="grid_columtitle"> '.strtoupper($columnas[$i]).'</th>';
    }
    $listado .= '<th>'.espacios(10).'</th><th>'.espacios(5).'</th></tr><tbody class="grid_body" style="height:250px;">';

    //detalle
    foreach($registros as $reg){
      $listado .= '<tr height="10px;" class="grid_row" '.resaltar('white','#CDCE9C').'>';
      for ($i=0; $i < count($columnas); $i++){
            $listado .= '<td class="grid_item">'.$reg[$i].'</td>';
      }
      $listado .= '<td><A href="control.php?rqst=FACTURACION.VALES&task=LISTADO'.'&busqueda[codigo]='.$_REQUEST['busqueda']['codigo'].'&busqueda[radio]='.$_REQUEST['busqueda']['radio'].
                  '&action=Modificar&cliente='.trim($reg[1]).'&desctipo='.trim($reg[2]).'&tipo='.trim($reg[7]).'&inicio='.trim($reg[3]).'&fin='.trim($reg[4]).'&fecha='.trim($reg[5]).'&tarjeta='.trim($reg[8]).'" target="control"><img src="/sistemaweb/icons/open.gif" alt="Editar" align="middle" border="0"/></A>&nbsp;';
      if ($_SESSION['autorizacion'])
      			  $listado .= '<td><A href="control.php?rqst=FACTURACION.VALES&task=LISTADO'.
                  '&action=Eliminar&cliente='.trim($reg[0]).'&tipovale='.trim($reg[7]).'&inicio='.trim($reg[3]).'&fin='.trim($reg[4]).'&busqueda[codigo]='.$_REQUEST['busqueda']['codigo'].'&busqueda[radio]='.$_REQUEST['busqueda']['radio'].
                  '" onclick="return confirm('."'Desea eliminar los vales asignados a la tarjeta?'".');" target="control"><img src="/sistemaweb/icons/delete.gif" alt="Eliminar" align="middle" border="0"/></A></td>';
      $listado .= '</tr>';
    }
    $listado .= '</tbody></table>';
    return $listado;
  }
  
  function setRegistros($codigo)
  {
    $RegistrosCB = ValesModel::ClientesCBArray("trim(cli_codigo)||''||trim(cli_razsocial) ~ '".pg_escape_string($codigo)."'");
    $result = '<blink><span class="MsgError">Error: Cliente no existe</span></blink>'." <script language=\"javascript\">top.setRegistro('$codigo','NO_GRUPO');</script>";
    if (count($RegistrosCB) == 1) {
      foreach($RegistrosCB as $cod => $descri){
      	 $grupo = ValesModel::TarjetasMagneticas(trim($cod));
      	
      	 if ($grupo=='NO_GRUPO') $result = '<blink><span class="MsgError">Error: Cliente no  tiene grupo</span></blink>'." <script language=\"javascript\">top.setRegistro('$cod','NO_GRUPO');</script>";
      	 else {
      	 	if ($grupo =='NO_TARJETA')
      	 		$result = '<blink><span class="MsgError">Error: Cliente no tiene tarjeta de vales</span></blink>'." <script language=\"javascript\">top.setRegistro('$cod','NO_TARJETA');</script>";
      	 	else
				$result = $descri." <script language=\"javascript\">top.setRegistro('".trim($cod)."','".$grupo."');</script>";      	 		
      	 }
      }
    }
    if (count($RegistrosCB) > 1){
      $att_opt = array();
      foreach($RegistrosCB as $cod => $descri){
        $att_opt[trim($cod)] = array("onclick"=>"getRegistro('".trim($cod)."');");
      }
      $cb = new f2element_combo('cbDatosCliente', '','', $RegistrosCB,'',array("size"=>"5"), array(), $att_opt);
      $result = '<div align="left" style="position: absolute; width:250px;  z-index:1000;">'.$cb->getTag().'</div>';
    }
    if ($codigo==''){
    	$result='';
    }
    return $result;
  }
  
  function setRegistrosVale($codigo){
  	print_r($codigo);
    $RegistrosCB = ValesModel::getTipoVales("trim(ch_tipovale)||''||trim(ch_descripcion_corta) ~ '".pg_escape_string($codigo)."'");
    $result = '<blink><span class="MsgError">Error: Tipo de Vale no existe</span></blink>'." <script language=\"javascript\">top.setRegistroVale('','','');</script>";
    print_r($RegistrosCB);
    if (count($RegistrosCB) == 1) {
      foreach($RegistrosCB as $cod => $descri){
      	 //$grupo = ValesModel::TarjetasMagneticas(trim($cod));
      	 $inicial = ValesModel::getValeInicial(trim($cod));
      	 $result = $descri." <script language=\"javascript\">top.setRegistroVale('".trim($cod)."','".substr($descri,3,strlen($descri)-3)."','".$inicial."');</script>";
      }
    }
    if (count($RegistrosCB) > 1){
      $att_opt = array();
      foreach($RegistrosCB as $cod => $descri){
        $att_opt[trim($cod)] = array("onclick"=>"getRegistroVale('".trim($cod)."');");
      }
      $cb = new f2element_combo('cbDatosVales', '','', $RegistrosCB,'',array("size"=>"5"), array(), $att_opt);
      $result = '<div align="left" style="position: absolute; width:250px;  z-index:1000;">'.$cb->getTag().'</div>';
    }
    if ($codigo==''){
    	$result='';
    }
    return $result;
  }
  
  
  function setRegistrosCliente($codigo){
    $RegistrosCB = ValesModel::ClientesCBArray("trim(cli_codigo)||''||trim(cli_razsocial) ~ '".pg_escape_string($codigo)."'");
    
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

