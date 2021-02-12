<?php
  /*
    Templates para Tabla ccob_ta_cabecera
    @MATT
  */

class AplicacionesTemplate extends Template {
 
  function titulo(){
    $titulo = '<div align="center"><h2>Aplicaciones</h2></div><hr>';
    return $titulo;
  }

  function errorResultado($errormsg){
    return '<blink>'.$errormsg.'</blink>';
  }

  function listadoCargos($registros){
    $Money = array('01'=>'Soles',
                   '02'=>'Dolares');

    $titulo_grid = "APLICACIONES DE PROVEEDORES";
    //formulario de busqueda
    $columnas = array('COD. PROV.','RAZON SOCIAL', 'TIPO','SERIE','NUMERO', 'FECHA EMISION', 'FECHA SALDO', 'MONEDA','TOTAL','SALDO');
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

    //detalle
    foreach($registros as $reg)
    {
      $reg[7] = $Money[trim($reg[7])];
      $listado .= '<tr height="10px;" class="grid_row" '.resaltar('white','#CDCE9C').'>';
      $regCod = urlencode(trim($reg["pro_codigo"]).trim($reg['pro_cab_seriedocumento']).trim($reg['pro_cab_numdocumento']).trim($reg['pro_cab_tipdocumento']));
      for ($i=0; $i < count($columnas); $i++){
            $listado .= '<td class="grid_item">'.$reg[$i].'</td>';
      }
      $listado .= '<td><A href="control.php?rqst=MOVIMIENTOS.APLICACIONES&task=APLICACIONES'.
                  '&action=Aplicacion&registroid='.$regCod.'&pro_codigo='.trim($reg['pro_codigo']).'" target="control"><img src="/sistemaweb/icons/open.gif" alt="Editar" align="middle" border="0"/></A></td>';
      $listado .= '</tr>';
    }
    $listado .= '</tbody></table></div>';
    return $listado;
  }

  // Solo Formularios y otros
  function formBuscar($paginacion){
    $form = new form2('', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control');
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MOVIMIENTOS.APLICACIONES'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'APLICACIONES'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('busqueda[codigo]','Codigo :', '', espacios(2), 20, 18, array("onkeyup"=>"javascript:this.value=this.value.toUpperCase();")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Buscar',espacios(3)));
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

  function listarAbonos($datos) {
    $Money = array('01'=>'Soles',
                   '02'=>'Dolares');

    $listado = '<table><thead><tr class="grid_header">';
    $columnas = array('TIPO','SERIE','NUMERO','FECHA EMISION','FECHA SALDO','TOTAL','MONEDA',' SALDO');
    foreach($columnas as $col){
      $listado .= '<th class="grid_columtitle">'.$col.'</th>';
    }
    $listado .= '<th class="grid_columtitle">'.espacios(17).'</th>
                 </tr></thead><tbody class="grid_body" style="height:100px">';
    foreach($datos as $dato){
      $regCod = $dato["ch_tipdocumento"];
      $dato["ch_moneda"] = $Money[trim($dato["pro_cab_moneda"])];
      $listado .= '<tr class="grid_row" '.resaltar('white','#CDCE9C').'>
                <td class="grid_item">'.$dato["pro_cab_tipdocumento"].'</td>
                <td class="grid_item">'.$dato["pro_cab_seriedocumento"].'</td>
                <td class="grid_item">'.$dato["pro_cab_numdocumento"].'</td>
                <td class="grid_item">'.$dato["pro_cab_fechaemision"].'</td>
                <td class="grid_item">'.$dato["pro_cab_fechasaldo"].'</td>
                <td class="grid_item" align="right">'.number_format($dato["pro_cab_imptotal"], 2, '.', ',').'</td>
                <td class="grid_item">'.$dato["pro_cab_moneda"].'</td>
                <td class="grid_item" align="right" >'.number_format($dato["pro_cab_impsaldo"], 2, '.', ',').'</td>
                <td class="grid_item">';
      $listado .= $dato["pro_cab_fechasaldo"]!=0 ?'<input type="checkbox" name="calcular[]" value="{'.$dato["pro_cab_imptotal"].'}{'.trim($dato["pro_cab_tipdocumento"]).'}{'.trim($dato["pro_cab_seriedocumento"]).trim($dato["pro_cab_numdocumento"]).'}" onChange="setCalcularAplicaciones(this);">':'&nbsp;';
      //$listado .= '<input type="hidden" name="ch_tipdocumento_abono[]" value="'.@trim($dato["ch_tipdocumento"]).'" >';
      //$listado .= '<input type="hidden" name="ch_numdocumento_abono[]" value="'.@trim($dato["ch_seriedocumento"]).@trim($dato["ch_numdocumento"]).'" >';
    }
    $listado .= '</tbody></table>';
    return $listado;
  }
  
  function verTotales($montos = array()){
    if($montos['TOTAL SALDO ABONO'] > $montos['TOTAL IMPORTE SALDO'])
    {
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

  function formAplicaciones($datos,$abonos_detalles)
  {
    $Money = array('01'=>'Soles',
		   '02'=>'Dolares');

    $form = new form2('APLICACIONES', 'form_aplicaciones', FORM_METHOD_POST, 'control.php', '', 'control');
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MOVIMIENTOS.APLICACIONES'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'APLICACIONES'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('registroid', @$datos["pro_codigo"]));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="5" cellpadding="5"> <tr>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="form_label"><b>Proveedor</b></td>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="form_label">:'.espacios(3).' '.@$datos["pro_codigo"].' - '.@$datos['pro_razsocial'].'</td>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr></table>'));
    
    $form->addGroup('doc_cargo', 'DOCUMENTO DE CARGO');
    $form->addElement('doc_cargo', new f2element_freeTags('<table border="0" cellspacing="5" cellpadding="5" align="center"> <tr>'));
    
    $form->addElement('doc_cargo', new f2element_freeTags('<td class="grid_item"><b>Tipo</b><br>'));
    $form->addElement('doc_cargo', new f2element_freeTags(''.@$datos['pro_cab_tipdocumento'].'</td>'));
    $form->addElement('doc_cargo', new f2element_hidden('ch_tipdocumento', @trim($datos['pro_cab_tipdocumento'])));

    $form->addElement('doc_cargo', new f2element_freeTags('<td class="form_label" align="center"><b>Serie</b><br>'));
    $form->addElement('doc_cargo', new f2element_freeTags(''.@$datos['pro_cab_seriedocumento'].'</td>'));

    $form->addElement('doc_cargo', new f2element_freeTags('<td class="form_label" align="center"><b>N&uacute;mero</b><br>'));
    $form->addElement('doc_cargo', new f2element_freeTags(''.@$datos['pro_cab_numdocumento'].'</td>'));
    $form->addElement('doc_cargo', new f2element_hidden('ch_numdocumento', @trim($datos["pro_cab_seriedocumento"]).@trim($datos['ch_numdocumento'])));

    $form->addElement('doc_cargo', new f2element_freeTags('<td class="form_label" align="center"><b>Fecha Emision</b><br>'));
    $form->addElement('doc_cargo', new f2element_freeTags(''.@$datos['pro_cab_fechaemision'].'</td>'));

    $form->addElement('doc_cargo', new f2element_freeTags('<td class="form_label" align="center"><b>Fecha Saldo</b><br>'));
    $form->addElement('doc_cargo', new f2element_freeTags(''.@$datos['pro_cab_fechasaldo'].'</td>'));

    $form->addElement('doc_cargo', new f2element_freeTags('<td class="form_label" align="center"><b>Moneda</b><br>'));
    $form->addElement('doc_cargo', new f2element_freeTags(''.@$Money[$datos['pro_cab_moneda']].'</td>'));

    $form->addElement('doc_cargo', new f2element_freeTags('<td class="form_label" align="center"><b>Total</b><br>'));
    $form->addElement('doc_cargo', new f2element_freeTags(''.@$datos['pro_cab_imptotal'].'</td>'));

    $form->addElement('doc_cargo', new f2element_freeTags('<td class="form_label" align="center"><b>Saldo</b><br>'));
    $form->addElement('doc_cargo', new f2element_freeTags(''.@$datos['pro_cab_impsaldo'].'</td>'));
    $form->addElement('doc_cargo', new f2element_hidden('pro_cab_impsaldo', @$datos["pro_cab_impsaldo"]));

    $form->addElement('doc_cargo', new f2element_freeTags('</tr></table>'));


    $form->addGroup('doc_monto', 'DOCUMENTOS DE ABONO');
    $form->addElement('doc_monto', new f2element_freeTags('<table border="0" cellspacing="3" cellpadding="3" align="center"> <tr><td class="form_td_title">'));
    $form->addElement('doc_monto', new f2element_freeTags ('<div id="detaAbono"  align="center">'.AplicacionesTemplate::listarAbonos($abonos_detalles).'</div>'));
    $form->addElement('doc_monto', new f2element_freeTags ('<div id="Totales" align="center" >'.AplicacionesTemplate::verTotales(/*AplicacionesModel::getAsientoContable($asc)*/).'</div>'));
    $form->addElement('doc_monto', new f2element_freeTags('<td></tr></table>'));

    $form->addGroup ('buttons', '');
    $form->addElement('buttons', new f2element_submit('action','Aplicar', espacios(2)));
    $form->addElement('buttons', new f2element_submit('action','Regresar', espacios(2)));
   
    return $form->getForm().'<div id="error_body" align="center"></div><hr>';
  }

}

