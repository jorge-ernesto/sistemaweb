<?php

class ConsumoTemplate extends Template
{

  function titulo(){
    $titulo = '<div align="center"><h2>Listado de Vales por Cliente</h2></div><hr>';
    return $titulo;
  }

  function errorResultado($errormsg){
    return '<blink>'.$errormsg.'</blink>';
  }

  function listado($registros){
    
    //print_r($registros);
    //$Almacenes = VariosModel::almacenCBArray();
    $titulo_grid = "LISTADO DE VALES POR CLIENTE";
    //formulario de busqueda
    $columnas = array('FECHA','CLIENTE','N&#186; VALE', 'PLACA', 'ART&Iacute;CULO','N&#186; LIQ.','ODOM.', 'N&#186; TARJ.', 'SUN', 'CANT.', 'PRECIO', 'IMPORTE');
    $listado = '<div id="resultados_grid" class="grid" align="center"><br>
                      <table>
                      <caption class="grid_title">'.$titulo_grid.'</caption>
                      <thead align="center" valign="center" >
                      <tr class="grid_header">';
    for($i=0;$i<count($columnas);$i++)
    {
      $listado .= '<th class="grid_columtitle"> '.$columnas[$i].'</th>';
    }
    $listado .= '<th>'.espacios(2).'</th><th>'.espacios(1).'</th></tr><tbody class="grid_body" style="height:250px;">';
    //print_r($registros);
    //detalle
    foreach($registros as $reg){
      $listado .= '<tr height="10px;" class="grid_row" '.resaltar('white','#CDCE9C').'>';
      $reg[9] = money_format("%.2n",round($reg[9],2));
      $reg[10] = money_format("%.2n",round($reg[10],2));
      $reg[11] = money_format("%.2n",round($reg[11],2));
      
      for ($i=0; $i < count($columnas); $i++){
      $i == 9 ||$i == 10 || $i == 11?$aling = 'align="right"':$aling=" ";
        $listado .= '<td class="grid_item" '.$aling.'>'.$reg[$i].'</td>';
      }
      $listado .= '</tr>';
    }
    $listado .= '</tbody></table></div>';
    print_r($listado);
    return $listado;
  }

  function formBuscar($paginacion, $datos)
  {
    if(!$datos['desde']) $datos['desde']="01".date('/m/Y');
    if(!$datos['hasta']) $datos['hasta']=date('d/m/Y');
    
    //$fecha_ini = "01".date('/m/Y');
    //$fecha_fin = date('d/m/Y');
    $form = new form2("", "Buscar", FORM_METHOD_POST, "control.php", '', "control");
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "REPORTES.CONSUMO"));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'CONSUMO'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rxp', @$_REQUEST['rxp']));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('pagina', @$_REQUEST['pagina']));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="2" cellpadding="2"> <tr><td class="form_td_title">'));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0"><tr><td>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text("busqueda[cli_codigo]", 'Cliente</td><td>:', @$datos['cli_codigo'], '', 10, 8,array("onKeyUp"=>"this.value=this.value.toUpperCase();getRegistro(this.value, 'setRegistroCli', 'CONSUMODET')")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags(''.espacios(2).'<div id="descrip_cliente" style="display:inline;">'.@trim($datos["cli_rsocialbreve"]).'</div>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text("busqueda[desde]", 'Desde</td><td> :', @$datos['desde'], '', 10, 10));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text("busqueda[hasta]", 'Hasta</td><td>:', @$datos['hasta'], '', 10, 10));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="6" align="center">'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Buscar',espacios(3)));
    $form->addElement(FORM_GROUP_MAIN, new f2element_submit("action", "Generar archivo"));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td align="center" class="form_label">'));
    
    //Inicio : Datos de Paginación de registros
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('P&aacute;gina '.@$paginacion['paginas'].' de '.@$paginacion['numero_paginas'].' P&aacute;ginas&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/first.gif', espacios(2),array( "border"=>"0", "alt"=>"Primera P&aacute;gina","onclick"=>"javascript:PaginarRegistrosPost('".@$paginacion['pp']."','".@$paginacion['primera_pagina']."')")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/left.gif', espacios(5),array( "border"=>"0", "alt"=>"P&aacute;gina Anterior","onclick"=>"javascript:PaginarRegistrosPost('".@$paginacion['pp']."','".@$paginacion['pagina_previa']."')")));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('paginas','', @$paginacion['paginas'], espacios(5), 3, 2, array( "onChange"=>"javascript:PaginarRegistrosPost('".@$paginacion['pp']."',this.value)")));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/right.gif', espacios(2),array( "border"=>"0", "alt"=>"P&aacute;gina Siguente","onclick"=>"javascript:PaginarRegistrosPost('".@$paginacion['pp']."','".@$paginacion['pagina_siguiente']."')")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/last.gif', espacios(2),array( "border"=>"0", "alt"=>"&Uacute;ltima P&aacute;gina","onclick"=>"javascript:PaginarRegistrosPost('".@$paginacion['pp']."','".@$paginacion['ultima_pagina']."')")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('numero_registros','Registros por P&aacute;gina : ', @$paginacion['pp'], espacios(2), 4, 4,array("onChange"=>"javascript:PaginarRegistrosPost(this.value,'".@$paginacion['primera_pagina']."')")));
    //Fin : Datos de Paginación de registros
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));

    return $form->getForm();
  }

  function setRegistroCli($codigo)
  {
    $codigo==''?$Error='':$Error='Error..';
    $RegistrosCB = ConsumoModel::ClienteCBArray("cli_codigo||trim(cli_razsocial) ~ '".pg_escape_string($codigo)."'");
    $result = '<blink><span class="MsgError">'.$Error.'</span></blink>';
    if (count($RegistrosCB) == 1) {
      foreach($RegistrosCB as $cod => $descri){
        $result = $descri." <script language=\"javascript\">top.setRegistro('".trim($cod)."', 'busqueda[cli_codigo]');</script>";
      }
    }
    
    if (count($RegistrosCB) > 1){
      $att_opt = array();
      foreach($RegistrosCB as $cod => $descri){
        $att_opt[trim($cod)] = array(" onclick"=>"getRegistro('".trim($cod)."', 'setRegistroCli', 'CONSUMODET');");
      }
      $cb = new f2element_combo('cbDatos', '', '', $RegistrosCB, '', array("size"=>"5"), array(), $att_opt);
      $result = '<div align="left" style="position: absolute; width:250px;  z-index:1000;">'.$cb->getTag().'</div>';
    }
    return $result;
  }


}

