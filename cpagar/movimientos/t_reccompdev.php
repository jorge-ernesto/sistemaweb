<?php
  /*
    Templates para Tablas Generales
    @TBCA
  */
//include('lib/paginador_new.php');

class RecCompDevTemplate extends Template {
 
  function titulo(){
    $titulo = '<div align="center"><h2>Listado de Compras y Devoluciones</h2></div><hr>';
    return $titulo;
  }

  function errorResultado($errormsg){
    return '<blink>'.$errormsg.'</blink>';
  }

  function listado($registros){
    //isset($_REQUEST["paglistado"])?$pagina=$_REQUEST["paglistado"]:$pagina=1;
    
    //print_r($registros);
    $Almacenes = VariosModel::almacenCBArray();
    $Estado = array('f'=>'Pendiente',
                    't'=>'Regularizado');

    $titulo_grid = "LISTADO DE COMPRAS Y DEVOLUCIONES";
    //formulario de busqueda
    $columnas = array('FECHA','PROVEEDOR','DOCUM. REF.', 'ALMACEN', 'ART&Iacute;CULO','CANT.','C. U.', 'C. T.', 'N&#186; MOV.', 'FACT. PROV.');
    $listado = '<div id="resultados_grid" class="grid" align="center"><br>
                      <table>
                      <caption class="grid_title">'.$titulo_grid.'</caption>
                      <thead align="center" valign="center" >
                      <tr class="grid_header">';
    for($i=0;$i<count($columnas);$i++)
    {
      $listado .= '<th class="grid_columtitle"> '.$columnas[$i].'</th>';
    }
    $listado .= '<th>'.espacios(2).'</th><th>'.espacios(5).'</th></tr><tbody class="grid_body" style="height:250px;">';
    //print_r($registros);
    //detalle
    foreach($registros as $reg){
      //echo "VALOR : ".$reg[8]." \n";
      $reg[1] = $reg[2].$reg[14];
      $reg[2] = $reg[3]." ".$reg[4];
      $reg[3] = substr($Almacenes[$reg[5]], 4, 15);
      $reg[4] = $reg[6]." ".$reg[13];
      $reg[5] = $reg[7];
      $reg[6] = $reg[8];
      $reg[7] = $reg[9];
      $reg[8] = $reg[10];
      $reg[9] = $reg[12];
      $listado .= '<tr height="10px;" class="grid_row" '.resaltar('white','#CDCE9C').'>';
      $regCod = $reg['mov_almacen'].$reg['mov_numero'].trim($reg['art_codigo']).$reg['mov_fecha'];
      //$listado .= '<td class="grid_columtitle">'.$cont.'</td>';
      for ($i=0; $i < count($columnas); $i++){
        //echo "";
            $listado .= '<td class="grid_item">'.$reg[$i].'</td>';
      }
     // $listado .= '<td><A href="control.php?rqst=MOVIMIENTOS.RECCOMPDEV&task=RECCOMPDEV'.
                 // '&action=Modificar&registroid='.$regCod.'" target="control"><img src="/sistemaweb/icons/open.gif" alt="Editar" align="middle" border="0"/></A>&nbsp;';
      
      $listado .= '<td><A href="javascript:confirmarLink(\'Desea Soltar esta Compra '.$regCod.'\',\'control.php?rqst=MOVIMIENTOS.RECCOMPDEV&task=RECCOMPDEV'.
                  '&action=Modificar&registroid='.$regCod.'\', \'control\')"><img src="/sistemaweb/icons/open.gif" alt="Actuualizar" align="middle" border="0"/></A>&nbsp;';
      
      $listado .= '</td>';
      $listado .= '</tr>';
     //$cont += 1;
    }
    $listado .= '</tbody></table></div>';
    return $listado;
  }

  // Solo Formularios y otros
  function formBuscar($paginacion, $datos){
   //echo "ENTRO BUSCAR\n";
    $TipoTrans = array('01'=>'Ordenes de Compra',
                       '05'=>'Devoluciones');
    $fecha_ini = "01".date('/m/Y');
    $fecha_fin = date('d/m/Y');
    
    if(@$_REQUEST['busqueda']['opcion'] == "pendientes")
      $checkPend = "checked";
    elseif(@$_REQUEST['busqueda']['opcion'] == "atendidos")
      $checkAtend = "checked";
    else
      $checkTodos = "checked";
      
    $form = new form2('', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control');
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MOVIMIENTOS.RECCOMPDEV'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'RECCOMPDEV'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rxp', @$_REQUEST['rxp']));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('pagina', @$_REQUEST['pagina']));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="2" cellpadding="2"> <tr><td class="form_td_title">'));

    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="2" cellpadding="2" align="center"> <tr><td class="form_td_title">'));

    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('busqueda[fecha_ini]','Desde </td><td>: ', $fecha_ini, espacios(2), 11, 10));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td>'));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('busqueda[fecha_fin]','Hasta </td><td>: ', $fecha_fin, espacios(2), 11, 10));


    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));

    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('busqueda[proveedor]','Proveedor </td><td>: ', @trim($datos["proveedor"]), espacios(2), 8, 7, array("onKeyUp"=>"this.value=this.value.toUpperCase();getRegistro(this.value, 'setRegistroProv', 'RECCOMPDEVDET')")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags(''.espacios(2).'<div id="descrip_proveedor" style="display:inline;">'.@trim($datos["pro_rsocialbreve"]).'</div>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td>'));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_radio ('busqueda[opcion]','Todos </td><td>: ', 'todos', espacios(2), '', array("".@$checkTodos."")));

    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('busqueda[articulo]','Art&iacute;culo </td><td>: ', @trim($datos["articulo"]), espacios(2), 13, 15, array("onKeyUp"=>"this.value=this.value.toUpperCase();getRegistro(this.value, 'setRegistroArt', 'RECCOMPDEVDET')")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags(''.espacios(2).'<div id="descrip_articulo" style="display:inline;">'.@trim($datos["art_descbreve"]).'</div>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td>'));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_radio ('busqueda[opcion]','Pendientes </td><td>: ', 'pendientes', espacios(2), '', array("".@$checkPend."")));

    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('busqueda[almacen]','Almacen </td><td>: ', @trim($datos["almacen"]), espacios(2), 4, 5, array("onKeyUp"=>"this.value=this.value.toUpperCase();getRegistro(this.value, 'setRegistroAlm', 'RECCOMPDEVDET')")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags(''.espacios(2).'<div id="descrip_almacen" style="display:inline;">'.@trim($datos["ch_nombre_breve_almacen"]).'</div>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td>'));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_radio ('busqueda[opcion]','Atendidos </td><td>: ', 'atendidos', espacios(2), '', array("".@$checkAtend."")));

    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));

    $form->addElement(FORM_GROUP_MAIN, new f2element_combo('busqueda[tipo_transaccion]','Tipos Trans.  </td><td>: ', @trim($datos["tipo_transaccion"]), $TipoTrans, espacios(2), ''));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td>'));

    $form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Buscar',espacios(3)));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td>'));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Reporte',espacios(0)));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td align="center" class="form_label">'));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('P&aacute;gina '.$paginacion['paginas'].' de '.$paginacion['numero_paginas'].' P&aacute;ginas&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/first.gif', espacios(2),array( "border"=>"0", "alt"=>"Primera P&aacute;gina","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['primera_pagina']."')")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/left.gif', espacios(5),array( "border"=>"0", "alt"=>"P&aacute;gina Anterior","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['pagina_previa']."')")));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('paginas','', $paginacion['paginas'], espacios(5), 3, 2, array( "onChange"=>"javascript:PaginarRegistros('".$paginacion['pp']."',this.value)")));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/right.gif', espacios(2),array( "border"=>"0", "alt"=>"P&aacute;gina Siguente","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['pagina_siguiente']."')")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/last.gif', espacios(2),array( "border"=>"0", "alt"=>"&Uacute;ltima P&aacute;gina","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['ultima_pagina']."')")));
    //$form->addElement(FORM_GROUP_MAIN, new f2element_text ('numero_registros','Registros por P&aacute;gina : ', $paginacion['pp'], espacios(2), 4, 4,array("onChange"=>"javascript:PaginarRegistros(this.value,'".$paginacion['primera_pagina']."')")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('numero_registros','Registros por P&aacute;gina : ', $paginacion['pp'], espacios(2), 4, 4,array("onChange"=>"javascript:submit();")));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));

    return $form->getForm().'<div id="reporte" align="center"></div>';
  }

  function setRegistroProv($codigo)
  {
    $codigo==''?$Error='':$Error='Error';
    $RegistrosCB = RecCompDevModel::ProveedorCBArray("pro_codigo||trim(pro_razsocial) ~ '".pg_escape_string($codigo)."'");
    $result = '<blink><span class="MsgError">'.$Error.'</span></blink>';
    if (count($RegistrosCB) == 1) {
      foreach($RegistrosCB as $cod => $descri){
        $result = $descri." <script language=\"javascript\">top.setRegistro('".trim($cod)."', 'busqueda[proveedor]');</script>";
      }
    }
    if (count($RegistrosCB) > 1){
      $att_opt = array();
      foreach($RegistrosCB as $cod => $descri){
        $att_opt[trim($cod)] = array(" onclick"=>"getRegistro('".trim($cod)."', 'setRegistroProv', 'RECCOMPDEVDET');");
      }
      $cb = new f2element_combo('cbDatos', '','', $RegistrosCB,'',array("size"=>"5"), array(), $att_opt);
      $result = '<div align="left" style="position: absolute; width:250px;  z-index:1000;">'.$cb->getTag().'</div>';
    }
    return $result;
  }

  function setRegistroArt($codigo)
  {
    $codigo==''?$Error='':$Error='Error..';
    $RegistrosCB = RecCompDevModel::ArticuloCBArray("art_codigo||trim(art_descripcion) ~ '".pg_escape_string($codigo)."'");
    $result = '<blink><span class="MsgError">'.$Error.'</span></blink>';
    if (count($RegistrosCB) == 1) {
      foreach($RegistrosCB as $cod => $descri){
        $result = $descri." <script language=\"javascript\">top.setRegistro('".trim($cod)."', 'busqueda[articulo]');</script>";
      }
    }
    if (count($RegistrosCB) > 1){
      $att_opt = array();
      foreach($RegistrosCB as $cod => $descri){
        $att_opt[trim($cod)] = array(" onclick"=>"getRegistro('".trim($cod)."', 'setRegistroArt', 'RECCOMPDEVDET');");
      }
      $cb = new f2element_combo('cbDatos', '','', $RegistrosCB,'',array("size"=>"5"), array(), $att_opt);
      $result = '<div align="left" style="position: absolute; width:250px;  z-index:1000;">'.$cb->getTag().'</div>';
    }
    return $result;
  }

  function setRegistroAlm($codigo)
  {
    $codigo==''?$Error='':$Error='Error..';
    $RegistrosCB = RecCompDevModel::AlmacenCBArray("ch_almacen||ch_nombre_almacen ~ '".pg_escape_string($codigo)."'");
    $result = '<blink><span class="MsgError">'.$Error.'</span></blink>';
    if (count($RegistrosCB) == 1) {
      foreach($RegistrosCB as $cod => $descri){
        $result = $descri." <script language=\"javascript\">top.setRegistro('".trim($cod)."', 'busqueda[almacen]');</script>";
      }
    }
    if (count($RegistrosCB) > 1){
      $att_opt = array();
      foreach($RegistrosCB as $cod => $descri){
        $att_opt[trim($cod)] = array(" onclick"=>"getRegistro('".trim($cod)."', 'setRegistroAlm', 'RECCOMPDEVDET');");
      }
      $cb = new f2element_combo('cbDatos', '','', $RegistrosCB,'',array("size"=>"5"), array(), $att_opt);
      $result = '<div align="left" style="position: absolute; width:250px;  z-index:1000;">'.$cb->getTag().'</div>';
    }
    return $result;
  }

}

