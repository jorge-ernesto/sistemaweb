<?php

class TemplateFormPrueba extends Template
{

  function FormBusqueda()
  {
    $form =  new form2('Form Buscar', 'form2', FORM_METHOD_POST, 'control.php', '', 'control');
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_freetags('<table border="0"><tr><td>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('busqueda[txtcodigo]','Codigo</td><td> :', @$datos['txtcodigo'], espacios(2), 20, 18, array("onkeyup"=>"javascript:this.value=this.value.toUpperCase();")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freetags('</td><td align="center">'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Buscar'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freetags('</td></tr><tr><td>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('busqueda[txtnombre]','Nombre </td><td> :', @$datos['txtcodigo'], espacios(2), 20, 18, array("onkeyup"=>"javascript:this.value=this.value.toUpperCase();")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freetags('</td></tr></table>'));
    return $form->getForm();
  }

  function listado($registros){
  global $usuario;
        //echo "SISTEMA : ".$usuario->sistemaActual."\n";
        
  //print_r($usuario);
    //isset($_REQUEST["paglistado"])?$pagina=$_REQUEST["paglistado"]:$pagina=1;
    $Money = array('01'=>'Soles',
                   '02'=>'Dolares');

    $titulo_grid = "CLIENTES";
    //formulario de busqueda
    $columnas = array('COD. CLIE.','RAZON SOCIAL','RAZON SOCIAL BREVE', 'DIRECCION', 'R.U.C.','TELEFONO','MONEDA');
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
      $regCod = trim($reg["cli_codigo"]);
      //$listado .= '<td class="grid_columtitle">'.$cont.'</td>';
      for ($i=0; $i < count($columnas); $i++){
        //echo "";
            $listado .= '<td class="grid_item">'.$reg[$i].'</td>';
      }
      $listado .= '<td><A href="control.php?rqst=MAESTROS.CLIENTE&task=CLIENTE'.
                  '&action=Modificar&registroid='.$regCod.'" target="control"><img src="/sistemaweb/icons/open.gif" title="Editar" align="middle" border="0"/></A>&nbsp;';
      $listado .= '<A href="javascript:confirmarLink(\'Desea borrar cuenta '.$regCod.'\',\'control.php?rqst=MAESTROS.CLIENTE&task=CLIENTE'.
                  '&action=Eliminar&registroid='.$regCod.'\', \'control\')"><img src="/sistemaweb/icons/delete.gif" title="Borrar" align="middle" border="0"/></A></td><td>&nbsp;</td>';
      $listado .= '</tr>';
     //$cont += 1;
    }
    $listado .= '</tbody></table></div>';
    return $listado;
  }

}

