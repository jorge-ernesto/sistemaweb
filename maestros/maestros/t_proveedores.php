<?php
  /*
    Templates para Tablas Generales
    @TBCA
  */
//include('lib/paginador_new.php');

class ProveedoresTemplate extends Template {

  function titulo() {
    $titulo = '<h2 align="center" style="color:#336699"><b>PROVEEDORES</b></h2>';
    return $titulo;
  }

  function errorResultado($errormsg) {
    return '<table align="center"><tr><td style="font-size:20px; color:red;"><blink>'.$errormsg.'</blink></td></tr></table>';
  }

  function listado($registros) {
    //isset($_REQUEST["paglistado"])?$pagina=$_REQUEST["paglistado"]:$pagina=1;
    $Money = array('1'=>'Soles',
                   '2'=>'Dolares');    
    //formulario de busqueda
    $columnas = array('COD. PROV.','RAZÓN SOCIAL','RAZÓN SOCIAL BREVE', 'DIRECCIÓN', 'R.U.C.','TELEFONO','MONEDA');
    $listado = '<div id="resultados_grid" class="grid" align="center"><br>
                      <table>
                      <th align="center" valign="center" >
                      <tr>';
    for($i=0;$i<count($columnas);$i++)
    {
      $listado .= '<th class="grid_cabecera"><b>'.strtoupper($columnas[$i]).'</b></th>';
    }
    $listado .= '<th>'.espacios(10).'</th><th>'.espacios(5).'</th></tr><tbody class="grid_cabecera">';

    $a = 0;
    //detalle
    foreach($registros as $reg){
      $a++;
      $color = ($a%2==0?"grid_detalle_par":"grid_detalle_impar");

      $reg[6]   = $Money[trim($reg[6])];
      $listado .= '<tr class="bgcolor ' . $color . '">';
      $regCod   = $reg["pro_codigo"];


      for ($i=0; $i < count($columnas); $i++)
        $listado .= '<td>'.$reg[$i].'</td>';

      $listado .= '<td><A href="control.php?rqst=MAESTROS.PROVEEDOR&task=PROVEEDOR'.
                  '&action=Modificar&registroid='.$regCod.'" target="control"><img src="/sistemaweb/icons/open.gif" alt="Editar" align="middle"/></A>&nbsp;';
      $listado .= '<A href="javascript:confirmarLink(\'¿Desea borrar cuenta '.$regCod.'?\',\'control.php?rqst=MAESTROS.PROVEEDOR&task=PROVEEDOR'.
                  '&action=Eliminar&registroid='.$regCod.'\', \'control\')"><img src="/sistemaweb/icons/delete.gif" alt="Borrar" align="middle"/></A></td>';
      $listado .= '</tr>';
    }

    $listado .= '</tbody></table></div>';
    return $listado;
  }

  // Solo Formularios y otros
  function formBuscar($paginacion){
   //echo "ENTRO BUSCAR\n";
    $form = new form2('', 'Buscar', FORM_METHOD_GET, 'control.php', '', 'control');
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MAESTROS.PROVEEDOR'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'PROVEEDOR'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('busqueda[codigo]','Codigo :', '', espacios(2), 20, 18, array("onkeyup"=>"javascript:this.value=this.value.toUpperCase();")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Buscar',espacios(3)));
    $form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Agregar',espacios(0)));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</center>'));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));

    $form->addGroup("GRUPO_PAGINA", "Paginacion");
    
    $form->addElement("GRUPO_PAGINA", new f2element_freeTags('P&aacute;gina '.$paginacion['paginas'].' de '.$paginacion['numero_paginas'].' P&aacute;ginas&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'));
    
    $form->addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/first.gif', espacios(2),array( "alt"=>"Primera P&aacute;gina","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['primera_pagina']."')")));
    $form->addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/left.gif', espacios(5),array( "alt"=>"P&aacute;gina Anterior","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['pagina_previa']."')")));
    
    $form->addElement("GRUPO_PAGINA", new f2element_text ('paginas','', $paginacion['paginas'], espacios(5), 3, 2, array( "onChange"=>"javascript:PaginarRegistros('".$paginacion['pp']."',this.value)")));
    
    $form->addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/right.gif', espacios(2),array( "alt"=>"P&aacute;gina Siguente","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['pagina_siguiente']."')")));
    $form->addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/last.gif', espacios(2),array( "alt"=>"&Uacute;ltima P&aacute;gina","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['ultima_pagina']."')")));
    $form->addElement("GRUPO_PAGINA", new f2element_text ('numero_registros','Registros por P&aacute;gina : ', $paginacion['pp'], espacios(2), 4, 4,array("onChange"=>"javascript:PaginarRegistros(this.value,'".$paginacion['primera_pagina']."')")));
   
    return $form->getForm();
  }

  
  //function formProveedores($datos,$registrosXml, $sAction)
  function formProveedores($datos,$registrosXml)
  {
    //print_r($registrosXml);
    $siNo = array('N'=>'No',
                  'S'=>'Si');
    
    $Money = array('1'=>'S/. - Nuevos Soles',
                   '2'=>'US$ - Dolares Americanos');
    if($datos['pro_tipo'] == "N")
    {
	$datos_natural = substr($datos["pro_datos_natural"], 1, -1);
	$datos_natural = explode(',',$datos_natural);
	$d_natural["pro_ap_paterno"] = $datos_natural[0];
	$d_natural["pro_ap_materno"] = $datos_natural[1];
	$d_natural["pro_pri_nombre"] = $datos_natural[2];
	$d_natural["pro_seg_nombre"] = $datos_natural[3];
    }
    //$datos
    if($datos['pro_tipo'] && $datos['pro_tipo']=="J")
    {
        $checkedTPJuridica = array("checked");
        $checkedTPNatural  = '';
        $DisplayJuridica   = 'block';
        $DisplayNatural    = 'none';
    }elseif($datos['pro_tipo'] && $datos['pro_tipo']=="N")
    {
        $checkedTPJuridica = '';
        $checkedTPNatural  = array("checked");
        $DisplayJuridica   = 'none';
        $DisplayNatural    = 'block';
    }else{
        $checkedTPJuridica = array("checked");
        $checkedTPNatural  = '';
        $DisplayJuridica   = 'block';
        $DisplayNatural    = 'none';
    }
    
    if($datos['pro_xml_bancos'] && $datos['pro_xml_bancos']!="")
    {
      $checked = array("checked");
      $display = 'block';
    }else{
        $checked = '';
        $display = 'none';
    }
    if($datos["pro_codigo"])
    {
        $params="disabled";
        $val="true";
    }else{
        $params="enabled";
        $val="true";
    }
    $datos["pro_razsocial"]     = htmlentities($datos["pro_razsocial"]);
    $datos["pro_rsocialbreve"]  = htmlentities($datos["pro_rsocialbreve"]);
    $datos["pro_direccion"]     = htmlentities($datos["pro_direccion"]);
    $datos["pro_comp_direcc"]   = htmlentities($datos["pro_comp_direcc"]);
    
    $form = new form2('PROVEEDORES', 'form_proveedores', FORM_METHOD_POST, 'control.php', '', 'control');
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MAESTROS.PROVEEDOR'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'PROVEEDOR'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('registroid', trim(@$datos["pro_codigo"])));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table> <tr><td class="form_td_title">'));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[pro_codigo]','C&oacute;digo de Proveedor </td><td>: ', @$datos["pro_codigo"], '', 13, 11, array("onKeyUp"=>"javascript:this.value=this.value.toUpperCase();checkCodigo(this)", "class"=>"form_input_numeric", "$params" => "$val")));    
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;<div id="MensajeValidacion" class="MsgError" style="display:inline;"></div>'));
    //$form->addElement(FORM_GROUP_MAIN, new f2element_ ('datos[pro_tipo]','TIPO DE PERSONA: ', @$datos["pro_tipo"], '', array("onClick"=>"displaybanco(this,document.getElementById('bancos'));"), $checked));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td class="form_label">'));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('Tipo de Persona '));

    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td>:'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_radio('datos[pro_tipo]','Juridica', 'J', espacios(1), array("onClick"=>"displayTipoPersona(this,document.getElementById('juridica'),document.getElementById('natural'));"), $checkedTPJuridica));
    $form->addElement(FORM_GROUP_MAIN, new f2element_radio('datos[pro_tipo]','Natural', 'N', espacios(1), array("onClick"=>"displayTipoPersona(this,document.getElementById('natural'),document.getElementById('juridica'));"), $checkedTPNatural));
  //function f2element_radio ($nameid, $label, $value, $separator, $size, $maxlength, $attributes=array(), $parameters=array()){

    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));

    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[pro_ruc]','R.U.C. </td><td>: ', @$datos["pro_ruc"], '', 12, 11, array("OnChange"=>"javascript:checkRuc(this);","class"=>"form_input_numeric")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<span class="btn_span sunat_span" id="btn_span" title="Consulta RUC SUNAT"></span>&nbsp;&nbsp;'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;<div id="MensajeValidacionRuc" class="MsgError" style="display:inline;"></div>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));

    //inicio de la tabla para datos persona Juridica
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="2">'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table id="juridica" style="display:'.$DisplayJuridica.';"> <tr><td class="form_td_title">'));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[pro_razsocial]','Raz&oacute;n Social'.espacios(20).'</td><td>: ', @trim($datos["pro_razsocial"]), '', 42, 40, array("onkeyup"=>"javascript:this.value=this.value.toUpperCase();")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td valign="top">'));

    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[pro_rsocialbreve]','Raz&oacute;n Social Breve</td><td>: ', @trim($datos["pro_rsocialbreve"]), '', 22, 20));

    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));
    //fin de la tabla para datos persona Juridica


    //inicio de la tabla para datos persona Natural
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table id="natural" style="display:'.$DisplayNatural.';"> <tr><td class="form_td_title">'));

    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('d_natural[pro_ap_paterno]','Apellido Paterno'.espacios(14).'</td><td>: ', @$d_natural["pro_ap_paterno"], '', 22, 20, array("onkeyup"=>"javascript:this.value=this.value.toUpperCase();")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td valign="top">'));

    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('d_natural[pro_ap_materno]','Apellido Materno</td><td>: ', @$d_natural["pro_ap_materno"], '', 22, 20, array("onkeyup"=>"javascript:this.value=this.value.toUpperCase();")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td valign="top">'));

    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('d_natural[pro_pri_nombre]','Primer Nombre</td><td>: ', @$d_natural["pro_pri_nombre"], '', 22, 20, array("onkeyup"=>"javascript:this.value=this.value.toUpperCase();")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td valign="top">'));

    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('d_natural[pro_seg_nombre]','Segundo Nombre</td><td>: ', @$d_natural["pro_seg_nombre"], '', 22, 20, array("onkeyup"=>"javascript:this.value=this.value.toUpperCase();")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));
    //fin de la tabla para datos persona Natural

    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td valign="top">'));

    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[pro_forma_pago]','Forma de Pago </td><td>: ', @trim($datos["pro_forma_pago"]), '', 7, 6, array("onKeyUp"=>"getRegistroFP(this.value)", "class"=>"form_input_numeric")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags(''.espacios(2).'<div id="desc_forma_pago" style="display:inline;">'.@$datos["desc_forma_pago"].'</div>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));

    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[pro_ciiu]','C&oacute;digo CIIU </td><td>: ', @trim($datos["pro_ciiu"]), '', 7, 6, array("onKeyUp"=>"getRegistro(this.value)", "class"=>"form_input_numeric")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags(''.espacios(2).'<div id="desc_ciiu" style="display:inline;">'.@$datos["desc_ciiu"].'</div>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));

    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[pro_grupo]','Rubro </td><td>: ', @trim($datos["pro_grupo"]), '', 7, 6, array("onKeyUp"=>"this.value=this.value.toUpperCase();getRegistroRub(this.value)", "class"=>"form_input_numeric")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags(''.espacios(2).'<div id="desc_rubro" style="display:inline;">'.@$datos["desc_rubro"].'</div>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));

    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[pro_direccion]','Direcci&oacute;n </td><td>: ', @trim($datos["pro_direccion"]), '', 42, 40));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td valign="top">'));

    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[pro_comp_direcc]','Complemento Direcci&oacute;n </td><td>: ', @trim($datos["pro_comp_direcc"]), '', 22, 20));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td valign="top">'));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[pro_distrito]','Distrito </td><td>: ', @trim($datos["pro_distrito"]), '', 7, 6, array("onKeyUp"=>"this.value=this.value.toUpperCase();getRegistroDist(this.value)", "class"=>"form_input_numeric")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags(''.espacios(2).'<div id="desc_distrito" style="display:inline;">'.@$datos["desc_distrito"].'</div>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[pro_email]','Direcci&oacute;n E-Mail</td><td>: ', @$datos["pro_email"], '', 32, 30));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td valign="top">'));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[pro_telefono1]','Tel&eacute;fono </td><td>: ', @$datos["pro_telefono1"], '', 12, 11, array("class"=>"form_input_numeric")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[pro_telefono2]','Fax </td><td>: ', @$datos["pro_telefono2"], '', 12, 11, array("class"=>"form_input_numeric")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_combo('datos[pro_moneda]','Moneda </td><td>: ', trim(@$datos["pro_moneda"]), $Money, espacios(3)));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
      
    $form->addElement(FORM_GROUP_MAIN, new f2element_combo('datos[pro_agente_retencion]','Agente de Retenci&oacute;n </td><td>: ', trim(@$datos["pro_agente_retencion"]), $siNo, espacios(3)));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));    
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_checkbox ('datos2[Ctas_Corrientes]','DATOS BANCARIOS</td><td>: ', 'ACTIVAR', '', array("onClick"=>"displaybanco(this,document.getElementById('bancos'));"), $checked));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[pro_contacto]','Persona de Contacto</td><td>: ', @$datos["pro_contacto"], '', 32, 30));

    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));
        
    $form->addGroup ('bancos', 'DATOS BANCARIOS',array("style"=>"display:".$display.";"));
    //$form->addElement('conf', new f2element_text ('interface[iaa_nombre_modulo]','MODULO :', @$interface["iaa_nombre_modulo"], '<BR><BR>', 21, 20));
    
    $form->addElement('bancos', new f2element_freeTags('<table><tr valign="top"><td>'));
    $form->addElement('bancos', new f2element_freeTags('<div id="descrip_codigo" style="float:left;display:inline">C&Oacute;DIGO:'));
    $form->addElement('bancos', new f2element_freeTags('</td><td>'));
    $form->addElement('bancos', new f2element_freeTags('</td><td>'));
    $form->addElement('bancos', new f2element_freeTags ('</div><div id="descrip_cuenta" style="float:left;display:inline">N&Uacute;MERO DE CUENTA :'));
    $form->addElement('bancos', new f2element_freeTags('</td><td>'));
    $form->addElement('bancos', new f2element_freeTags('</div><div id="descrip_tip_cta">TIPO CUENTA :'));
    $form->addElement('bancos', new f2element_freeTags('</td><td>'));
    $form->addElement('bancos', new f2element_freeTags('</td></tr><tr valign="top"><td>'));
    $interface["dato_bancario"]["cod_banco"][] = '';
    foreach(@$interface["dato_bancario"]["cod_banco"] as $columna)
    {
      $form->addElement('bancos', new f2element_text ('interface[dato_bancario][cod_banco][]','', $columna, '', 8, 6, array("onKeyUp"=>"this.value=this.value.toUpperCase();getRegistroCodCta(this.value)", "class"=>"form_input_numeric")));      
      $form->addElement('bancos', new f2element_freeTags('<div id="desc_cta[]" style="float:left;display:inline"></div>'));
    }
    $form->addElement('bancos', new f2element_freeTags('</td><td>'));
    $form->addElement('bancos', new f2element_text ('interface[dato_bancario][desc_cta][]','', $alias, '', 25, 100, array("readonly"=>"true", "class"=>"form_input")));
    $form->addElement('bancos', new f2element_freeTags('</td><td>'));
    $interface["dato_bancario"]["nro_cuenta"][] = '';
    foreach(@$interface["dato_bancario"]["nro_cuenta"] as $alias)
    {
      $form->addElement('bancos', new f2element_text ('interface[dato_bancario][nro_cuenta][]','', $alias, '', 25, 20, array("class"=>"form_input_numeric")));
    }
    $form->addElement('bancos', new f2element_freeTags('</td><td>'));
    
    $interface["dato_bancario"]["tipo_cuenta"][] = '';
    foreach(@$interface["dato_bancario"]["tipo_cuenta"] as $columna)
    {
      $form->addElement('bancos', new f2element_text ('interface[dato_bancario][tipo_cuenta][]','', $columna, '', 8, 6, array("onKeyUp"=>"this.value=this.value.toUpperCase();getRegistroTipoCtaBan(this.value)", "class"=>"form_input_numeric")));
      $form->addElement('bancos', new f2element_freeTags('<div id="desc_tipoctaban[]" style="float:left;display:inline"></div>'));
    }
    $form->addElement('bancos', new f2element_text ('interface[dato_bancario][desc_tipoctaban][]','', $alias, '', 25, 100, array("readonly"=>"true", "class"=>"form_input")));
    $form->addElement('bancos', new f2element_freeTags('</td><td>'));
    $form->addElement('bancos', new f2element_freeTags('</td></tr></table>'));
    
    $form->addElement('bancos', new f2element_freeTags('<table width="100%"><tr valign="top"><td>'));
    $form->addElement('bancos', new f2element_freeTagsLinkJs ('Agregar Cuenta..', linea_h(1), array("href"=>"javascript:AgregaCuenta(document.getElementsByName('interface[dato_bancario][cod_banco][]')[0],document.getElementsByName('interface[dato_bancario][desc_cta][]')[0],document.getElementsByName('interface[dato_bancario][nro_cuenta][]')[0],document.getElementsByName('interface[dato_bancario][tipo_cuenta][]')[0],document.getElementsByName('interface[dato_bancario][desc_tipoctaban][]')[0])")));
    $form->addElement('bancos', new f2element_freeTags('</td></tr></table>'));
    //$form->addElement('bancos', new f2element_freeTags(espacios(10)));
    if($datos['pro_xml_bancos']!="" && $registrosXml)
    {
      //echo "registrosXml : $registrosXml\n";
      $listado_array = array();
      $root = $registrosXml->document_element();
      $columnas = explode(',',$root->get_attribute('campos'));
      for($i=0;$i<count($columnas);$i++)
      {
        $nombres[$i] = $columnas[$i];
      }
      $node_regs = $root->get_elements_by_tagname('reg');
      $i=1;
      foreach($node_regs as $reg) {
        $id = $reg->get_attribute('id');
        //echo "REG : $id \n";
        $form->addElement('bancos', new f2element_hidden('reg[]', $id));
        $valores = $reg->child_nodes();
        $regCod = '';
        foreach($valores as $llave => $valor){
          $regCod==''?$regCod=$valor->get_content():$regCod;
          //echo " LLAVE : $llave => REGCOD : ".$valor->get_content()." \n";
          $listado_array[$id][$nombres[$llave]] = $valor->get_content();
          if($listado_array[$id]['codigo_banco'])
          {
            $RegistrosCB = ProveedoresModel::CuentasCBArray("tab_elemento = '".pg_escape_string($listado_array[$id]['codigo_banco'])."'");
            $listado_array[$id]['descrip_banco'] = $RegistrosCB[$listado_array[$id]['codigo_banco']];
          }
          if($listado_array[$id]['tipo_cuenta_bancaria'])
          {
            $RegistrosCB = ProveedoresModel::TipoCtaBanCBArray("tab_elemento = '".pg_escape_string($listado_array[$id]['tipo_cuenta_bancaria'])."'");
            $listado_array[$id]['descrip_tipo_cuenta_bancaria'] = $RegistrosCB[$listado_array[$id]['tipo_cuenta_bancaria']];
          }
        }
        $i++;
      }
        $_SESSION["CUENTAS"] = $listado_array;
        //print_r($_SESSION["CUENTAS"]);
        //echo "ID : $id \n";
        $_SESSION["TOTAL_CUENTAS"] = $id;
        $form->addElement('bancos', new f2element_freeTags('<div id="datos_agregados">'.ProveedoresTemplate::addCuentasBancarias($listado_array).'</div>'));
    }else{
        $form->addElement('bancos', new f2element_freeTags('<div id="datos_agregados"></div>'));
    }


    $form->addGroup ('buttons', '');
    $form->addElement('buttons', new f2element_submit('action','Guardar', espacios(2)));
    /*
    if ( $sAction != 'Modificar' ){
        $form->addElement('buttons', new f2element_submit('action','Guardar', espacios(2)));
    } else {
        $form->addElement('buttons', new f2element_submit('action','update', espacios(2)));
    }
    */
    $form->addElement('buttons', new f2element_submit('action','Regresar', espacios(2)));
   
    return $form->getForm().'<div id="error_body" align="center"></div><hr>';
  }


  function addCuentasBancarias($lista)
  {
    if($lista!='')
    {
     $formulario .= '<table>'."\n";
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
                  "<a href=\"javascript:EliminarCuenta(".$llave.",document.getElementsByName('datos[pro_codigo]')[0])\">_</a>"."\n".
                  '</td>'."\n".
                  '</tr>'."\n";
    }
    $formulario .= '</table>'."\n";
    return $formulario;
    }
    
  }
  
  function setRegistros($codigo)
  {
    $RegistrosCB = ProveedoresModel::CiiuCBArray("tab_elemento ~ '".pg_escape_string($codigo)."'");
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
    $RegistrosCB = ProveedoresModel::FormaPagoCBArray("substring(tab_elemento for 2 from length(tab_elemento)-1 ) ~ '".pg_escape_string($codigo)."'");
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

  function setRegistrosDistrito($codigo)
  {
    $RegistrosCB = ProveedoresModel::DistritoCBArray("trim(tab_elemento)||''||trim(tab_descripcion) ~ '".pg_escape_string($codigo)."'");
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
    $RegistrosCB = ProveedoresModel::RubrosCBArray("trim(ch_codigo_rubro)||''||trim(ch_descripcion) ~ '".pg_escape_string($codigo)."'");
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
    $RegistrosCB = ProveedoresModel::CuentasCBArray("trim(tab_elemento)||''||trim(tab_descripcion) ~ '".pg_escape_string($codigo)."'");
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
    $RegistrosCB = ProveedoresModel::TipoCtaBanCBArray("trim(tab_elemento)||''||trim(tab_descripcion) ~ '".pg_escape_string($codigo)."'");
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

