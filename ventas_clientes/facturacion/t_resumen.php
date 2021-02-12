<?php
  /*
    Templates para Tablas Generales
    @TBCA
  */
//include('lib/paginador_new.php');
//include ('/sistemaweb/include/reportes2.inc.php');


class ResumenTemplate extends Template {
 
  function titulo(){
    $titulo = '<div align="center"><h2>ELIMINAR RESUMENES</h2></div><hr>';
    return $titulo;
  }

  function errorResultado($errormsg){
    return '<center><blink>'.$errormsg.'</blink></center>';
  }
  
  function formBuscar(){
    $form = new form2('', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control');
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'FACTURACION.RESUMEN'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'RESUMEN'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('busqueda[codigo]','Codigo :', '', espacios(2), 20, 18, array("onkeyup"=>"javascript:this.value=this.value.toUpperCase();getRegistroCli(this.value);")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;<div id="desc_cliente" style="display:inline;"></div>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Buscar',espacios(3)));
   	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</center>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));
    return $form->getForm();
  }

  function listado($registros){
    $Money = array('01'=>'Soles',
                   '02'=>'Dolares');

    $titulo_grid = "ELIMIACION DE RESUMENES";
    //formulario de busqueda
    $columnas = array('COD. CLIE.','RAZON SOCIAL', 'TIPO','SERIE','NUMERO', 'FECHA EMISION', 'FECHA SALDO', 'MONEDA','TOTAL','SALDO');
    $listado = '<div id="resultados_grid" class="grid" align="center"><br>
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
      $auximon = $reg[7];
      $reg[7] = $Money[trim($reg[7])];
      $listado .= '<tr height="10px;" class="grid_row" '.resaltar('white','#CDCE9C').'>';
      $regCod = trim($reg["cli_codigo"]).trim($reg["ch_tipdocumento"]).trim($reg["ch_seriedocumento"]).$reg["ch_numdocumento"];
      for ($i=0; $i < count($columnas); $i++){
            $listado .= '<td class="grid_item">'.$reg[$i].'</td>';
      }
      $listado .= '<td><A href="control.php?rqst=FACTURACION.RESUMEN&task=RESUMEN'.
                  '&action=Eliminar&registroid='.trim($regCod).'&busqueda[codigo]='.$_REQUEST['busqueda']['codigo'].'" target="control" onclick="return confirm('."'Desea eliminar el resumen?'".');"><img src="/sistemaweb/icons/delete.gif" alt="Eliminar" align="middle" border="0"/></A></td>';
      $listado .= '</tr>';
    }
    $listado .= '</tbody></table></div>';
    return $listado;
  }
  
  function setRegistrosCliente($codigo)
  {
    $RegistrosCB = ResumenModel::ClientesCBArray("trim(cli_codigo)||''||trim(cli_razsocial) ~ '".pg_escape_string($codigo)."'");
    //print_r($RegistrosCB);
    //$result = '<blink><span class="MsgError">Error..</span></blink>';
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

?>