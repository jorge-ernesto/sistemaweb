<?php
  /*
    Templates para Tabla ccob_ta_cabecera
    @MATT
  */

class InclusionTemplate extends Template {
 
  function titulo(){
    $titulo = '<div align="center"><h2>Inclusi&oacute;n</h2></div><hr>';
    return $titulo;
  }

  function errorResultado($errormsg){
    return '<blink>'.$errormsg.'</blink>';
  }

  function listadoCargos($registros){
    $Money = array('01'=>'Soles',
                   '02'=>'Dolares');
    $TContable = array('C'=>'CARGO',
                       'A'=>'ABONO');

    $titulo_grid = "INCLUSI&Oacute;N DE CLIENTES";
    //formulario de busqueda
    $columnas = array('COD. CLIE.','RAZON SOCIAL', 'TIPO','SERIE','NUMERO', 'FEC. EMISION', 'FEC. SALDO', 'MONEDA','TOTAL','SALDO', 'T. CONTAB.');
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
      $reg[10] = $TContable[trim($reg[10])];
      $listado .= '<tr height="10px;" class="grid_row" '.resaltar('white','#CDCE9C').'>';
      $regCod = trim($reg["cli_codigo"]);
      for ($i=0; $i < count($columnas); $i++){
            $listado .= '<td class="grid_item">'.$reg[$i].'</td>';
      }
      $listado .= '<td><A href="control.php?rqst=MOVIMIENTOS.INCLUSION&task=INCLUSION'.
                  '&action=Aplicacion&registroid='.trim($regCod).trim($reg[2]).trim($reg[3]).trim($reg[4]).'&busqueda[codigo]='.$_REQUEST['busqueda']['codigo'].'" target="control"><img src="/sistemaweb/icons/open.gif" alt="Editar" align="middle" border="0"/></A></td>';
      $listado .= '</tr>';
    }
    $listado .= '</tbody></table></div>';
    return $listado;
  }

  // Solo Formularios y otros
  function formBuscar($paginacion){
    $form = new form2('', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control');
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MOVIMIENTOS.INCLUSION'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'INCLUSION'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('busqueda[codigo]','Codigo :', $_REQUEST['busqueda']['codigo'], espacios(2), 20, 18, array("onkeyup"=>"this.value=this.value.toUpperCase();getRegistroCli4(this.value);")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;<div id="desc_cliente" style="display:inline;"></div>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Buscar',espacios(3)));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</center>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));
    return $form->getForm();
  }

  function listarAbonos($datos) {
    $Money = array('01'=>'Soles',
                   '02'=>'Dolares');

    $TMovi = array('1'=>'INCLUSI&Oacute;N',
                   '2'=>'CANCELACI&Oacute;N',
                   '3'=>'APLICACI&Oacute;N',
                   '4'=>'CANJE');

    $listado = '<table><thead><tr class="grid_header">';
    $columnas = array('TIP. MOV.', 'TIPO','SERIE','N&Uacute;MERO','FEC. MOV.','FEC. ACTUA.','IMPORTE', 'T.D.R.','NUM DOC REF.', 'MONEDA');
    foreach($columnas as $col){
      $listado .= '<th class="grid_columtitle">'.$col.'</th>';
    }
    $listado .= '<th class="grid_columtitle">'.espacios(5).'</th>
                 </tr></thead><tbody class="grid_body" style="height:150px">';
    foreach($datos as $dato){
      $regCod = $dato["ch_tipdocumento"];
      $dato["ch_moneda"] = $Money[trim($dato["ch_moneda"])];
      $dato["ch_tipmovimiento"] = $TMovi[trim($dato["ch_tipmovimiento"])];
      $listado .= '<tr class="grid_row" '.resaltar('white','#CDCE9C').'>
                <td class="grid_item" align="right" >'.$dato["ch_tipmovimiento"].'</td>
                <td class="grid_item">'.$dato["ch_tipdocumento"].'</td>
                <td class="grid_item">'.$dato["ch_seriedocumento"].'</td>
                <td class="grid_item">'.$dato["ch_numdocumento"].'</td>
                <td class="grid_item">'.$dato["dt_fechamovimiento"].'</td>
                <td class="grid_item">'.$dato["dt_fecha_actualizacion"].'</td>
                <td class="grid_item" align="right">'.number_format($dato["nu_importemovimiento"], 2, '.', ',').'</td>
                <td class="grid_item" align="right" >'.$dato["ch_tipdocreferencia"].'</td>
                <td class="grid_item" align="right" >'.$dato["ch_numdocreferencia"].'</td>
                <td class="grid_item">'.$dato["ch_moneda"].'</td></tr>';
    }
    $listado .= '</tbody></table>';
    return $listado;
  }
  

  function formInclusion($datos,$abonos_detalles)
  {
    $Money = array('01'=>'Soles',
		   '02'=>'Dolares');

    $form = new form2('INCLUSION', 'form_inclusion', FORM_METHOD_POST, 'control.php', '', 'control');
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MOVIMIENTOS.INCLUSION'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'INCLUSION'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('registroid', @$datos["cli_codigo"]));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('busqueda[codigo]', $_REQUEST['busqueda']['codigo']));

    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="5" cellpadding="5"> <tr>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="form_label"><b>Cliente</b></td>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="form_label">:'.espacios(3).' '.@$datos["cli_codigo"].' - '.@$datos['cli_razsocial'].'</td>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr></table>'));
    
    $form->addGroup('doc_cargo', 'DOCUMENTO SELECCIONADO');
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

    $form->addElement('doc_cargo', new f2element_freeTags('<td class="form_label" align="center"><b>Fecha Vence</b><br>'));
    $form->addElement('doc_cargo', new f2element_freeTags(''.@$datos['dt_fechavencimiento'].'</td>'));

    $form->addElement('doc_cargo', new f2element_freeTags('<td class="form_label" align="center"><b>Moneda</b><br>'));
    $form->addElement('doc_cargo', new f2element_freeTags(''.@$Money[$datos['ch_moneda']].'</td>'));

    $form->addElement('doc_cargo', new f2element_freeTags('<td class="form_label" align="center"><b>Total</b><br>'));
    $form->addElement('doc_cargo', new f2element_freeTags(''.@$datos['nu_importetotal'].'</td>'));

    $form->addElement('doc_cargo', new f2element_freeTags('<td class="form_label" align="center"><b>Saldo</b><br>'));
    $form->addElement('doc_cargo', new f2element_freeTags(''.@$datos['nu_importesaldo'].'</td>'));
    $form->addElement('doc_cargo', new f2element_hidden('nu_importesaldo', @$datos["nu_importesaldo"]));
    
    //if ($datos['ch_tipdocumento']=='10' || $datos['ch_tipdocumento']=='35'){
		$form->addElement('doc_cargo', new f2element_freeTags('<td class="form_label" align="center"><b>Dias Vencimiento</b><br>'));
		$form->addElement('doc_cargo', new f2element_text ('dias','', @$datos['nu_dias_vencimiento'], '', 4, 4,array("class"=>"form_input_numeric",'onkeypress'=>'return validar(event,2);')));
		$form->addElement('doc_cargo', new f2element_submit('action','Modificar', espacios(2)));
		$form->addElement('doc_cargo', new f2element_freeTags('</td>'));
    //}
    $form->addElement('doc_cargo', new f2element_freeTags('</tr></table>'));


    $form->addGroup('doc_monto', 'DOCUMENTOS DETALLADOS');
    $form->addElement('doc_monto', new f2element_freeTags('<table border="0" cellspacing="3" cellpadding="3" align="center"> <tr><td class="form_td_title">'));
    $form->addElement('doc_monto', new f2element_freeTags ('<div id="detaAbono"  align="center">'.InclusionTemplate::listarAbonos($abonos_detalles).'</div>'));
    //$form->addElement('doc_monto', new f2element_freeTags ('<div id="Totales" align="center" >'.InclusionTemplate::verTotales(/*InclusionModel::getAsientoContable($asc)*/).'</div>'));
    $form->addElement('doc_monto', new f2element_freeTags('<td></tr></table>'));

    $form->addGroup ('buttons', '');
    //$form->addElement('buttons', new f2element_submit('action','Aplicar', espacios(2)));
    $form->addElement('buttons', new f2element_submit('action','Regresar', espacios(2)));
   
    return $form->getForm().'<div id="error_body" align="center"></div><hr>';
  }
  
  function setRegistrosCliente($codigo)
  {
    $RegistrosCB = InclusionModel::ClientesCBArray("trim(cli_codigo)||''||trim(cli_razsocial) ~ '".pg_escape_string($codigo)."'");
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

