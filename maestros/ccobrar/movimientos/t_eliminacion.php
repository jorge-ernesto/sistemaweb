<?php

class EliminacionTemplate extends Template {
 
  function titulo(){
    $titulo = '<div align="center"><h2>Eliminaci&oacute;n de Cuentas por Cobrar</h2></div><hr>';
    return $titulo;
  }

  function errorResultado($errormsg){
    return '<blink>'.$errormsg.'</blink>';
  }

  function listado($registros,$buscando){

    $Money = array('01'=>'Soles',
                   '02'=>'Dolares');

    $TContable = array('C'=>'CARGO',
                       'A'=>'ABONO');
    
    $TMovi = array('2'=>'Cancelaci&oacute;n');

    //$titulo_grid = "ELIMINACION DE CUENTAS POR COBRAR ";

    //formulario de busqueda de todos los registros

    $columnas = array(
			'TIPO',
			'SERIE',
			'NUMERO',
			'TIP. MOV.',
			'COD.',
			'RAZON SOCIAL',
			'FEC. MOV.',
			'FECHA ACTUALIZ.',
			'MON.',
			'IMPORTE',
			'T.D.R',
			'DOC. REF');

    $listado ='<div id="error_body" align="center"></div>';
    $listado.='<div id="resultados_grid" class="grid" align="center">
                      <table>
                      <caption class="grid_title">'.$titulo_grid.'</caption>
                      <thead align="center" valign="center" >
                      <tr class="grid_header">';

	    for($i=0;$i<count($columnas);$i++){
	      $listado .= '<th class="grid_columtitle"> '.strtoupper($columnas[$i]).'</th>';
	    }

    $listado.='<th>'.espacios(10).'</th><th>'.espacios(5).'</th></tr><tbody class="grid_body" style="height:250px;">';

    //detalle de los registros

    print_r($registros);

    foreach($registros as $reg){

      $TipMov = trim($reg[3]);
      $reg[3]   = $TMovi[trim($reg[3])];
      $reg[8]   = $Money[trim($reg[8])];
      $FechAct = explode('.',$reg[7]);
      $reg[7] = $FechAct[0];
      $listado .= '<tr height="10px;" class="grid_row" '.resaltar('white','#CDCE9C').'>';
      $regCod = trim($reg["cli_codigo"]);

	    for ($i=0; $i < count($columnas); $i++){
		    $listado .= '<td class="grid_item">'.$reg[$i].'</td>';	      
	    }
      //$listado .= '<td class="grid_item"><A href="javascript:confirmarLink(\'Estas seguro de borrar este registro '.$regCod.'?\',\'control.php?rqst=MOVIMIENTOS.ELIMINACION&task=ELIMINACION'.'&action=Eliminar&id_cuadre_turno_ticket='.($a['id_cuadre_turno_ticket']).'&dia='.$dia.'&dia2='.$dia2.'\', \'control\')"><img src="/sistemaweb/icons/delete.gif" alt="Borrar" align="middle" border="0"/></A>&nbsp;</td>';
      $listado .= '<td class="grid_item"><A href="javascript:confirmarLink(\'Estas seguro de eliminar este registro '. $regCod.'?\',\'control.php?rqst=MOVIMIENTOS.ELIMINACION&task=ELIMINACION'.'&action=Eliminar&codigo='.trim($regCod).'&tipo='.trim($reg[0]).'&serie='.trim($reg[1]).'&numero='.trim($reg[2]).'&importe='.trim($reg[9]).'&buscando='.$_REQUEST['busqueda']['codigo'].'\', \'control\')"><img src="/sistemaweb/icons/delete.gif" alt="Borrar" align="middle" border="0"/></A></td>';			
 
    }
    
    $listado .= '</tbody></table></div>';
    return $listado;

  }

  // Solo Formulario
  function formBuscar($codigo){

	if (trim($codigo) != ""){
		$_REQUEST['busqueda']['codigo'] = $codigo;
	}

    $desc = EliminacionModel::ClientesCBArray("cli_codigo='".$_REQUEST['busqueda']['codigo']."'");

    $form = new form2('', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control');
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MOVIMIENTOS.ELIMINACION'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'ELIMINACION'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('busqueda[codigo]','Codigo :', $_REQUEST['busqueda']['codigo'], espacios(2), 20, 18, array("onkeyup"=>"this.value=this.value.toUpperCase();getRegistroCli3(this.value);")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;<div id="desc_cliente" style="display:inline;">'.$desc[$_REQUEST['busqueda']['codigo']].'</div>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Buscar',espacios(3)));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</center>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));

    return $form->getForm();

  }

  function setRegistrosCliente($codigo){

    $RegistrosCB = EliminacionModel::ClientesCBArray("trim(cli_codigo)||''||trim(cli_razsocial) ~ '".pg_escape_string($codigo)."'");
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

