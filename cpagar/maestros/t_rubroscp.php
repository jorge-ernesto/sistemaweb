<?php
  /*
    Templates para Tablas Generales
    @TBCA
  */
//include('lib/paginador_new.php');

class RubrosCPTemplate extends Template {
 
  function titulo(){
    $titulo = '<div align="center"><h2>Rubros CxP</h2></div><hr>';
    return $titulo;
  }

  function errorResultado($errormsg){
    return '<blink>'.$errormsg.'</blink>';
  }

  function listado($registros){
    //isset($_REQUEST["paglistado"])?$pagina=$_REQUEST["paglistado"]:$pagina=1;
    $Money = array('01'=>'Soles',
                   '02'=>'Dolares');

    $titulo_grid = "RUBROS CXP";
    //formulario de busqueda
    $columnas = array('CODIGO','DESCRIPCION','DESCRIPCION BREVE', 'CUENTA', 'T.ITEM','PERCEP','DETRAC');
    $listado = '<div id="resultados_grid" class="grid" align="center"><br>
                      <table>
                      <caption class="grid_title">'.$titulo_grid.'</caption>
                      <thead align="center" valign="center" >
                      <tr class="grid_header">';
    for($i=0;$i<count($columnas);$i++)
    {
      $listado .= '<th class="grid_columtitle"> '.strtoupper($columnas[$i]).'</th>';
    }
    $listado .= '<th>'.espacios(10).'</th><th>'.espacios(5).'</th></tr><tbody class="grid_body" style="height:250px;">';
    //print_r($registros);
    //detalle
    foreach($registros as $reg){
      //echo "VALOR : ".$reg[8]." \n";
      $reg[6] = $Money[trim($reg[6])];
      $listado .= '<tr height="10px;" class="grid_row" '.resaltar('white','#CDCE9C').'>';
      $regCod = trim($reg["ch_codigo_rubro"]);
      //$listado .= '<td class="grid_columtitle">'.$cont.'</td>';
      for ($i=0; $i < count($columnas); $i++){
        //echo "";
            $listado .= '<td class="grid_item">'.$reg[$i].'</td>';
      }
      $listado .= '<td><A href="control.php?rqst=MAESTROS.RUBROSCP&task=RUBROSCP'.
                  '&action=Modificar&registroid='.$regCod.'" target="control"><img src="/sistemaweb/icons/open.gif" alt="Editar" align="middle" border="0"/></A>&nbsp;';
      $listado .= '<A href="javascript:confirmarLink(\'Desea borrar cuenta '.$regCod.'\',\'control.php?rqst=MAESTROS.RUBROSCP&task=RUBROSCP'.
                  '&action=Eliminar&registroid='.$regCod.'\', \'control\')"><img src="/sistemaweb/icons/delete.gif" alt="Borrar" align="middle" border="0"/></A></td><td>&nbsp;</td>';
      $listado .= '</tr>';
     //$cont += 1;
    }
    $listado .= '</tbody></table></div>';
    return $listado;
  }

  // Solo Formularios y otros
  function formBuscar($paginacion){
   //echo "ENTRO BUSCAR\n";
    $form = new form2('', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control');
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MAESTROS.RUBROSCP'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'RUBROSCP'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('busqueda[codigo]','Codigo :', '', espacios(2), 20, 18, array("onkeyup"=>"javascript:this.value=this.value.toUpperCase();")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Buscar',espacios(3)));
    $form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Agregar',espacios(0)));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</center>'));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('P&aacute;gina '.$paginacion['paginas'].' de '.$paginacion['numero_paginas'].' P&aacute;ginas&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/first.gif', espacios(2),array( "border"=>"0", "alt"=>"Primera P&aacute;gina","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['primera_pagina']."')")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/left.gif', espacios(5),array( "border"=>"0", "alt"=>"P&aacute;gina Anterior","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['pagina_previa']."')")));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('paginas','', $paginacion['paginas'], espacios(5), 3, 2, array( "onChange"=>"javascript:PaginarRegistros('".$paginacion['pp']."',this.value)")));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/right.gif', espacios(2),array( "border"=>"0", "alt"=>"P&aacute;gina Siguente","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['pagina_siguiente']."')")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/last.gif', espacios(2),array( "border"=>"0", "alt"=>"&Uacute;ltima P&aacute;gina","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['ultima_pagina']."')")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('numero_registros','Registros por P&aacute;gina : ', $paginacion['pp'], espacios(2), 4, 4,array("onChange"=>"javascript:PaginarRegistros(this.value,'".$paginacion['primera_pagina']."')")));
   
    return $form->getForm();
  }

  
  function formRubrosCP($datos)
  {
    print_r($datos);
    $CbSiNo = array('N'=>'No',
                  'S'=>'Si');
    
    $Money = array('01'=>'S/. - Nuevos Soles',
                   '02'=>'US$ - Dolares Americanos');
    
    if($datos["ch_codigo_rubro"])
    {
        $params="disabled";
        $val="true";
    }else{
        $params="enabled";
        $val="true";
    }

    $CbListaPrecio = RubrosCPModel::ListaPreciosCBArray("tab_elemento ~ '".$datos["cli_lista_precio"]."'");
    $CbDistrito = RubrosCPModel::DistritoCBArray("tab_elemento ~ '".$datos["cli_distrito"]."'");

    //print_r($CbListaPrecio);
    $CbTipoItem = RubrosCPModel::TipoItemCBArray();
    
    $form = new form2('RUBROSCP', 'form_rubroscp', FORM_METHOD_POST, 'control.php', '', 'control');
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MAESTROS.RUBROSCP'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'RUBROSCP'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('registroid', trim(@$datos["ch_codigo_rubro"])));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="2" cellpadding="2"> <tr><td class="form_td_title">'));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[ch_codigo_rubro]','C&oacute;digo de Rubro </td><td>: ', @$datos["ch_codigo_rubro"], '', 8, 6, array("onKeyUp"=>"javascript:this.value=this.value.toUpperCase();", "class"=>"form_input_numeric", "$params" => "$val")));    
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;<div id="MensajeValidacion" class="MsgError" style="display:inline;"></div>'));

    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td valign="top">'));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[ch_descripcion]','Descripci&oacute;n '.espacios(20).'</td><td>: ', @$datos["ch_descripcion"], '', 41, 40, array("onkeyup"=>"javascript:this.value=this.value.toUpperCase();")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td valign="top">'));

    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[ch_descripcion_breve]','Descripci&oacute;n Breve</td><td>: ', @$datos["ch_descripcion_breve"], '', 21, 20));

    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td valign="top">'));

    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[plc_codigo]','Cuenta </td><td>: ', @$datos["plc_codigo"], '', 16, 15));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td valign="top">'));

   // $form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[ch_tipo_item]','Tipo Item </td><td>: ', @$datos["ch_tipo_item"], '', 7, 6));
   // $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;<div id="MensajeValidacionRuc" class="MsgError" style="display:inline;"></div>'));

    $form->addElement(FORM_GROUP_MAIN, new f2element_combo('datos[ch_tipo_item]','Tipo Item  </t180871
	d><td>: ', trim(@$datos["ch_tipo_item"]), $CbTipoItem, espacios(3)));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));

    $form->addElement(FORM_GROUP_MAIN, new f2element_combo('datos[ch_percepcion_tipo]','Tipo Percepci&oacute;n </td><td>: ', trim(@$datos["ch_percepcion_tipo"]), $CbTipoItem, espacios(3)));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));

    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[ch_percepcion_porcentaje]','% Percepci&oacute;n </td><td>: ', @$datos["ch_percepcion_porcentaje"], '', 7, 6));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td valign="top">'));

    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[ch_detraccion_tipo]','Tipo Detracci&oacute;n </td><td>: ', @$datos["ch_detraccion_tipo"], '', 3, 2));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td valign="top">'));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[ch_detraccion_porcentaje]','% Detracci&oacute;n </td><td>: ', @trim($datos["ch_detraccion_porcentaje"]), '', 7, 6, array("class"=>"form_input_numeric")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags(''.espacios(2).'<div id="desc_distrito" style="display:inline;">'.$CbDistrito[$datos["cli_distrito"]].'</div>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
    

    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));

    $form->addGroup ('buttons', '');
    $form->addElement('buttons', new f2element_submit('action','Guardar', espacios(2)));
    $form->addElement('buttons', new f2element_submit('action','Regresar', espacios(2)));
   
    return $form->getForm().'<div id="error_body" align="center"></div><hr>';
  }


  function addCuentasBancarias($lista)
  {
    if($lista!='')
    {
     $formulario .= '<table border="0">'."\n";
    foreach($lista as $llave => $valor)
    {
    $formulario .= '<tr valign="top"><td>'."\n".
                  '</td></tr><tr valign="top"><td>'."\n".
                  '<input type="text" name="cod_banco[]" value="'.$valor['codigo_banco'].'" disabled class="form_input_numeric" size="8">'.
                  '</td><td>'."\n".
                  '<input type="text" name="desc_cta[]" value="'.$valor['descrip_banco'].'" disabled class="form_input" size="25">'.
                  '</td><td>'."\n".
                  '<input type="text" name="nro_cuenta[]" value="'.$valor['nro_cuenta_bancaria'].'" disabled class="form_input_numeric" size="25">'.
                  '</td><td>'."\n".
                  '<input type="text" name="tipo_cuenta[]" value="'.$valor['tipo_cuenta_bancaria'].'" disabled class="form_input_numeric" size="8">'.
                  '</td><td>'."\n".
                  '<input type="text" name="desc_tipoctaban[]" value="'.$valor['descrip_tipo_cuenta_bancaria'].'" disabled class="form_input" size="25">'.
                  '</td><td>'."\n".
                  //'<input type="hidden" name="id" value="'.$llave.'">'.
                  "<a href=\"javascript:EliminarCuenta(".$llave.",document.getElementsByName('datos[cli_codigo]')[0])\">_</a>"."\n".
                  '</td>'."\n".
                  '</tr>'."\n";
    }
    $formulario .= '</table>'."\n";
    return $formulario;
    }
    
  }
  
  function setRegistros($codigo)
  {
    $RegistrosCB = RubrosCPModel::CiiuCBArray("tab_elemento ~ '".pg_escape_string($codigo)."'");
    $result = '<blink><span class="MsgError">Error..</span></blink>';
    if (count($RegistrosCB) == 1) {
      foreach($RegistrosCB as $codciiu => $descriciiu){
        $result = $descriciiu." <script language=\"javascript\">top.setRegistro('".trim($codciiu)."');</script>";
      }
    }
    if (count($RegistrosCB) > 1){
      $att_opt = array();
      foreach($RegistrosCB as $codcli => $descricli){
        $att_opt[trim($codcli)] = array(" onclick"=>"getRegistro('".trim($codcli)."');");
      }
      $cb = new f2element_combo('cbDatosCiiu', '','', $RegistrosCB,'',array("size"=>"5"), array(), $att_opt);
      $result = '<div align="left" style="position: absolute; width:250px;  z-index:1000;">'.$cb->getTag().'</div>';
    }
    return $result;
  }

  function setRegistrosFormaPago($codigo)
  {
    $RegistrosCB = RubrosCPModel::FormaPagoCBArray("tab_elemento ~ '".pg_escape_string($codigo)."'");
    $result = '<blink><span class="MsgError">Error..</span></blink>';
    if (count($RegistrosCB) == 1) {
      foreach($RegistrosCB as $cod => $descri){
        $result = $descri." <script language=\"javascript\">top.setRegistroFP('".$cod."');</script>";
      }
    }
    if (count($RegistrosCB) > 1){
      $att_opt = array();
      foreach($RegistrosCB as $cod => $descri){
        $att_opt[trim($cod)] = array(" onclick"=>"getRegistroFP('".$cod."');");
      }
      $cb = new f2element_combo('cbDatosFormaPago', '','', $RegistrosCB,'',array("size"=>"5"), array(), $att_opt);
      $result = '<div align="left" style="position: absolute; width:250px;  z-index:1000;">'.$cb->getTag().'</div>';
    }
    return $result;
  }

  function setRegistrosListaPrecios($codigo)
  {
  //empty($codigo)?$codigo:'*';
    $RegistrosCB = RubrosCPModel::ListaPreciosCBArray("tab_elemento ~ '".pg_escape_string($codigo)."'");
    $result = '<blink><span class="MsgError">Error..</span></blink>';
    if (count($RegistrosCB) == 1) {
      foreach($RegistrosCB as $cod => $descri){
        $result = $descri." <script language=\"javascript\">top.setRegistroLPRE('".$cod."');</script>";
      }
    }
    if (count($RegistrosCB) > 1){
      $att_opt = array();
      foreach($RegistrosCB as $cod => $descri){
        $att_opt[trim($cod)] = array(" onclick"=>"getRegistroLPRE('".$cod."');");
      }
      $cb = new f2element_combo('cbDatosListaPrecios', '','', $RegistrosCB,'',array("size"=>"5"), array(), $att_opt);
      $result = '<div align="left" style="position: absolute; width:250px;  z-index:1000;">'.$cb->getTag().'</div>';
    }
    return $result;
  }

  function setRegistrosDistrito($codigo)
  {
    $RegistrosCB = RubrosCPModel::DistritoCBArray("trim(tab_elemento)||''||trim(tab_descripcion) ~ '".pg_escape_string($codigo)."'");
    $result = '<blink><span class="MsgError">Error..</span></blink>';
    if (count($RegistrosCB) == 1) {
      foreach($RegistrosCB as $codciiu => $descriciiu){
        $result = $descriciiu." <script language=\"javascript\">top.setRegistroDist('".trim($codciiu)."');</script>";
      }
    }
    if (count($RegistrosCB) > 1){
      $att_opt = array();
      foreach($RegistrosCB as $codcli => $descricli){
        $att_opt[trim($codcli)] = array(" onclick"=>"getRegistroDist('".trim($codcli)."');");
      }
      $cb = new f2element_combo('cbDatosDistrito', '','', $RegistrosCB,'',array("size"=>"5"), array(), $att_opt);
      $result = '<div align="left" style="position: absolute; width:250px;  z-index:1000;">'.$cb->getTag().'</div>';
    }
    return $result;
  }

  function setRegistrosRubro($codigo)
  {
    $RegistrosCB = RubrosCPModel::RubrosCBArray("trim(tab_elemento)||''||trim(tab_descripcion) ~ '".pg_escape_string($codigo)."'");
    $result = '<blink><span class="MsgError">Error..</span></blink>';
    if (count($RegistrosCB) == 1) {
      foreach($RegistrosCB as $cod => $descri){
        $result = $descri." <script language=\"javascript\">top.setRegistroRub('".trim($cod)."');</script>";
      }
    }
    if (count($RegistrosCB) > 1){
      $att_opt = array();
      foreach($RegistrosCB as $cod => $descri){
        $att_opt[trim($cod)] = array(" onclick"=>"getRegistroRub('".trim($cod)."');");
      }
      $cb = new f2element_combo('cbDatosRubro', '','', $RegistrosCB,'',array("size"=>"5"), array(), $att_opt);
      $result = '<div align="left" style="position: absolute; width:250px;  z-index:1000;">'.$cb->getTag().'</div>';
    }
    return $result;
  }

  function setRegistrosCuentas($codigo)
  {
  //echo "FIELDS : $fields";
    $RegistrosCB = RubrosCPModel::CuentasCBArray("trim(tab_elemento)||''||trim(tab_descripcion) ~ '".pg_escape_string($codigo)."'");
    $result = '<blink><span class="MsgError">Error..</span></blink>';
    if (count($RegistrosCB) == 1) {
      foreach($RegistrosCB as $cod => $descri){
        $result = " <script language=\"javascript\">top.setRegistroCodCta('".trim($cod)."','".$descri."');</script>";
      }
    }
    if (count($RegistrosCB) > 1){
      $att_opt = array();
      foreach($RegistrosCB as $cod => $descri){
        $att_opt[trim($cod)] = array(" onclick"=>"getRegistroCodCta('".trim($cod)."');");
      }
      $cb = new f2element_combo('cbDatosCtas', '','', $RegistrosCB,'',array("size"=>"5"), array(), $att_opt);
      $result = '<div align="left" style="position: absolute; width:250px;  z-index:1000;">'.$cb->getTag().'</div>';
    }
    return $result;
  }
  
  function setRegistrosTipoCtaBan($codigo)
  {
  //echo "FIELDS : $fields";
    $RegistrosCB = RubrosCPModel::TipoCtaBanCBArray("trim(tab_elemento)||''||trim(tab_descripcion) ~ '".pg_escape_string($codigo)."'");
    $result = '<blink><span class="MsgError">Error..</span></blink>';
    if (count($RegistrosCB) == 1) {
      foreach($RegistrosCB as $cod => $descri){
        $result = " <script language=\"javascript\">top.setRegistroTipoCtaBan('".trim($cod)."','".$descri."');</script>";
      }
    }
    if (count($RegistrosCB) > 1){
      $att_opt = array();
      foreach($RegistrosCB as $cod => $descri){
        $att_opt[trim($cod)] = array(" onclick"=>"getRegistroTipoCtaBan('".trim($cod)."');");
      }
      $cb = new f2element_combo('cbDatosTipoCtas', '','', $RegistrosCB,'',array("size"=>"5"), array(), $att_opt);
      $result = '<div align="left" style="position: absolute; width:250px;  z-index:1000;">'.$cb->getTag().'</div>';
    }
    return $result;
  }
}

