<?php
  /*
    Templates para Tabla ccob_ta_cabecera
    @MATT
  */

class PreciosTemplate extends Template {
 
  function titulo(){
    $titulo = '<div align="center"><h2>LISTA DE PRECIOS PROMEDIO</h2></div><hr>';
    return $titulo;
  }

  function errorResultado($errormsg){
    return '<blink>'.$errormsg.'</blink>';
  }

  function listado($registros){
	print_r($registros);
    $titulo_grid = "LISTA DE PRECIOS PROMEDIO";
    //formulario de busqueda
    $columnas = array('LISTA','MONEDA','DESCRIPCION','PRECIO');
    $listado ='<div id="error_body" align="center"></div>';
    $listado .= '<div id="resultados_grid" class="grid" align="center">
                      <table width="50%">
                      <caption class="grid_title">'.$titulo_grid.'</caption>
                      <thead align="center" valign="center" >
                      <tr class="grid_header">';
    for($i=0;$i<count($columnas);$i++)
    {
      $listado .= '<th class="grid_columtitle"> '.strtoupper($columnas[$i]).'</th>';
    }
    $listado .= '<th>'.espacios(10).'</th><th>'.espacios(5).'</th></tr><tbody class="grid_body" width="100%">';

    //detalle

    foreach($registros as $reg)
    {
      
      $listado .= '<tr height="10px;" class="grid_row" '.resaltar('white','#CDCE9C').'>';
      $regCod = trim($reg["cli_codigo"]);
      for ($i=0; $i < count($columnas); $i++){
            $listado .= '<td class="grid_item">'.$reg[$i].'</td>';
      }
      
      $listado .= '<td><A href="control.php?rqst=MAESTROS.PRECIOS&task=PROMEDIO'.
                  '&action=Agregar&lista='.trim($reg[0]).'&articulo='.trim($reg[4]).
                  '&precio='.trim($reg[3]).'&busqueda[codigo]='.($_REQUEST['busqueda']['codigo']!=''?$_REQUEST['busqueda']['codigo']:'').
                  '&busqueda[radio]='.($_REQUEST['busqueda']['radio']!=''?$_REQUEST['busqueda']['radio']:'').'" target="control"><img src="/sistemaweb/icons/open.gif" alt="Editar" align="middle" border="0"/></A>&nbsp;';
      
      $listado .= '</tr>';
    }
    $listado .= '</tbody></table></div>';
    return $listado;
  }

  /*function formEditar(){
  	$form = new form2('', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control');
  	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MAESTROS.PRECIOS'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'PROMEDIO'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('lista', $_REQUEST['lista']));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('articulo', $_REQUEST['articulo']));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="2" cellpadding="3"> <tr><td class="form_label">'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('Lista de Precios </td><td>: &nbsp;'.($_REQUEST['lista']=='90'?'90 - SOLES':'91 - DOLARES').'</td></tr>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_label">Articulo </td><td>: &nbsp;'.$_REQUEST['articulo'].' - '.$_REQUEST['descripcion'].'</td></tr><tr><td>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('precio','Precio </td><td>: ', '', '', 10, 8, array("onKeyUp"=>"return validar(event,3);", "class"=>"form_input_numeric")));    
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></table>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</center>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));
  	return $form->getForm();
  }*/
  // Solo Formularios y otros
  function formBuscar(){
  	$Desc = PreciosModel::ArticulosCBArray(" art_codigo='".$_REQUEST['busqueda']['codigo']."'");
  	print_r($Desc);
    $form = new form2('', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control');
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MAESTROS.PRECIOS'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'PROMEDIO'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center>'));
   	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="2" cellpadding="3"> <tr><td class="form_label">'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('busqueda[codigo]','Codigo :', $_REQUEST['busqueda']['codigo'], espacios(2), 20, 18, array("onkeyup"=>"javascript:this.value=this.value.toUpperCase();getRegistroArticulo(this.value)")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;<div id="desc_articulo" style="display:inline;">'.$Desc[trim($_REQUEST['busqueda']['codigo'])].'</div>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Buscar',espacios(3)));
    $form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Agregar',espacios(3)));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_radio ('busqueda[radio]','Soles', '90', espacios(2), array(),($_REQUEST['busqueda']['radio']=='90'?array('checked'):array())));
    $form->addElement(FORM_GROUP_MAIN, new f2element_radio ('busqueda[radio]','Dolares', '91', espacios(2),array(),($_REQUEST['busqueda']['radio']=='91'?array('checked'):array())));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></table>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</center>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));
    return $form->getForm();
  }
  
  function formAgregar(){
  	$form = new form2('', 'frmagregar', FORM_METHOD_POST, 'control.php', '', 'control','onSubmit = "return verificar_completo();"');
  	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MAESTROS.PRECIOS'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'PROMEDIO'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_hidden ('busqueda[codigo]',$_REQUEST['busqueda']['codigo']));
    $form->addElement(FORM_GROUP_MAIN, new f2element_hidden ('busqueda[radio]',$_REQUEST['busqueda']['radio']));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="2" cellpadding="3"> <tr><td class="form_label">'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_combo('pre_lista_precio','Lista de Precios</td><td>: ', $_REQUEST['lista'], array('90'=>'90 - Soles','91'=>'91 - Dolares'), espacios(3)));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>')); 
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('cli_codigo','Cliente </td><td>: ', '', '', 10, 8, array("onKeyUp"=>"javascript:this.value=this.value.toUpperCase();getRegistroCli(this.value)", "class"=>"form_input_numeric")));    
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;<div id="desc_cliente" style="display:inline;"></div>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>')); 
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('art_codigo','Articulo </td><td>: ', $_REQUEST['articulo'], '', 15, 15, array("onKeyUp"=>"javascript:this.value=this.value.toUpperCase();getRegistroArticulo(this.value)", "class"=>"form_input_numeric")));    
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;<div id="desc_articulo" style="display:inline;"></div>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('precio','Precio </td><td>: ', $_REQUEST['precio'], '', 10, 8, array("onKeyPress"=>"return validar(event,3);", "class"=>"form_input_numeric")));    
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan=2 align=center>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Grabar',espacios(3)));
    $form->addElement(FORM_GROUP_MAIN, new f2element_button('action','Regresar',espacios(3),array('onclick'=>'return volver_a_detalle();')));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></table>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</center>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));
    return $form->getForm();

  }
  
  function setRegistrosArticulos($codigo)
  {
    $RegistrosCB = PreciosModel::ArticulosCBArray("trim(art_codigo)||trim(art_descripcion) ~ '".pg_escape_string($codigo)."'");
    $result = '<blink><span class="MsgError">Error..</span></blink>';
    
    if (count($RegistrosCB) == 1) {
      foreach($RegistrosCB as $cod => $descri){
         $result = $descri." <script language=\"javascript\">top.setRegistroArticulo('".trim($cod)."');</script>";
      }
    }
    if (count($RegistrosCB) > 1){
      $att_opt = array();
      foreach($RegistrosCB as $cod => $descri){
        $att_opt[trim($cod)] = array("onclick"=>"getRegistroArticulo('".trim($cod)."');");
      }
      $cb = new f2element_combo('cbDatosArticulos', '','', $RegistrosCB,'',array("size"=>"5"), array(), $att_opt);
      $result = '<div align="left" style="position: absolute; width:250px;  z-index:1000;">'.$cb->getTag().'</div>';
    }
    return $result;
  }
  
  function setRegistrosCliente($codigo)
  {
  	$RegistrosCB = PreciosModel::ClientesCBArray("trim(cli_codigo)||''||trim(cli_razsocial) ~ '".pg_escape_string($codigo)."'");
    $result = '<blink><span class="MsgError">Error..</span></blink>';
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

