<?php
  /*
    Templates para Tablas Generales
    @TBCA
  */
//include('lib/paginador_new.php');
//include ('/sistemaweb/include/reportes2.inc.php');


class DescuentosTemplate extends Template {
 
  function titulo(){
    $titulo = '<div align="center"><h2>AUTORIZAR PORCENTAJES DE DESCUENTO</h2></div><hr>';
    return $titulo;
  }

  function errorResultado($errormsg){
    return '<blink>'.$errormsg.'</blink>';
  }
    
  function formLogin(){
  	$form = new form2('', 'frmlogin', FORM_METHOD_POST, 'control.php', '', 'control');
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'FACTURACION.DESCUENTOS'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'DESCUENTOS'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('login','Usuario :', $_REQUEST['login'] , espacios(2), 10, 10));
    $form->addElement(FORM_GROUP_MAIN, new f2element_password ('clave','Clave :', $_REQUEST['clave'] , espacios(2), 10, 10));
    $form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Ingresar',espacios(3)));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</center>'));
    return $form->getForm();
  }
  
  function formAutorizar(){
  	$codigo_fin= $_REQUEST['busqueda']['codigo'];
  	$Clientes = EspecialesModel::getClientes($codigo_fin);    
    $form = new form2('', 'frmbuscar', FORM_METHOD_POST, 'control.php', '', 'control');
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'FACTURACION.DESCUENTOS'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'DESCUENTOS'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('busqueda[codigo]','Codigo :', $codigo_fin , espacios(2), 10, 10, array("onKeyUp"=>"this.value=this.value.toUpperCase();getRegistroCliente(this.value);")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="desc_codigo" style="display:inline;">'.$Clientes['Datos'][trim($codigo_fin)].'</div>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Buscar',espacios(3)));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</center>'));
    return $form->getForm();
  }
  
  function setRegistrosCliente($codigo)
  {
    $RegistrosCB = EspecialesModel::ClientesCBArray("trim(cli_codigo)||''||trim(cli_razsocial) ~ '".pg_escape_string($codigo)."'");
    print_r('ingreso correctamente');
    print_r($RegistrosCB);
    $result = '<blink><span class="MsgError">Error..</span></blink>';
    if (count($RegistrosCB) == 1) {
      foreach($RegistrosCB as $cod => $descri){
      	$result = $descri." <script language=\"javascript\">top.setRegistroCliente('".trim($cod)."');</script>";
      }
    }
    if (count($RegistrosCB) > 1){
      $att_opt = array();
      foreach($RegistrosCB as $cod => $descri){
        $att_opt[trim($cod)] = array(" onclick"=>"getRegistroCliente('".trim($cod)."');");
      }
      $cb = new f2element_combo('cbDatosCliente', '','', $RegistrosCB,'',array("size"=>"5"), array(), $att_opt);
      $result = '<div align="left" style="position: absolute; width:250px;  z-index:1000;">'.$cb->getTag().'</div>';
    }
    return $result;
  }
  
  function listado($registros){
  
    $columnas = array('','CODIGO','DESCRIPCION','DESCUENTO','ESTADO');
    
    $listado ='<div id="error_body" align="center"></div>';
    $listado .= '<div id="resultados_grid" class="grid_item" align="left"><br>';
    $listado .= '<form method="post" name="frmseleccionar" action="control.php" target="control" onSubmit="return confirmar_autorizar();">
    			<input type="hidden" name="rqst" value="FACTURACION.DESCUENTOS" />
    			<input type="hidden" name="task" value="DESCUENTOS" />
    			<input type="hidden" name="paginacion[codigo]" value="'.$_REQUEST['busqueda']['codigo'].'" />
    			<input type="hidden" name="contador" value="0" />';
    if (count($registros)>1){
    	$listado .= '&nbsp;&nbsp;<input type="checkbox" name="all" value="Seleccionar" onClick="check(this);" />AUTORIZAR TODOS<br/><br/>';
    }
    
    if (count($registros)<=0){
    	$listado.='<table border="0" width="100%"><tr height="10px;" class="grid_row" ><td colspan="6" class="grid_item" align="center"> <h3> <BR/> NO EXISTEN <BR/><BR/>CLIENTES CON DESCUENTO POR AUTORIZAR </h3></td></tr></table>';
    }
       
    for($j=0;$j<count($registros);$j++){
    	
    		
    	 if ($registros[$j-1][0]!=$registros[$j][0]){
	    	$titulo_grid = $registros[$j][0]." - ".$registros[$j][1];
	    	
	    	
	    	$listado .= '<table border="1" width="100%">
	    			  
	                 <caption class="grid_title">'.$titulo_grid.'</caption>';
	    	for($i=0;$i<count($columnas);$i++){
		   		$listado .= '<th class="grid_columtitle"> '.$columnas[$i].'</th>';
			}
		 }
    	if ($registros[$j][9]=='t')
   	        $listado .= '<tr height="10px;" class="grid_row" >';
    	else 
    		$listado .= '<tr height="10px;" class="grid_pendiente" >';
    		
    	  $listado .= '<td class="grid_item" align="center"><input type="checkbox" name="chk[]" value="'.trim($registros[$j][0]).'" onclick="return anadir_contador(this);"/></td>';
	      $listado .= '<td class="grid_item" align="center">'.$registros[$j][0].'</td>';
	      $listado .= '<td class="grid_item" align="center">'.$registros[$j][1].'</td>';
	      $listado .= '<td class="grid_item" align="center">'.($registros[$j][3]*100).' %</td>';
	      $listado .= '<td class="grid_item" align="center">PENDIENTE</td>';
	     $listado .= '<td><A href="control.php?rqst=FACTURACION.DESCUENTOS&task=DESCUENTOS'.
		  	              '&action=Modificar&busqueda[codigo]='.$_REQUEST['busqueda']['codigo'].'&codigo='.trim($registros[$j][0]).'" target="control"><img src="/sistemaweb/icons/open.gif" alt="Editar" align="middle" border="0"/></A></td>';
		   /*$listado .= '<td><A href="control.php?rqst=FACTURACION.AUTORIZAR&task=PRECIOS'.
		                  '&action=Eliminar&busqueda[codigo]='.$_REQUEST['busqueda']['codigo'].'&registroid='.trim($registros[$j][8]).' '.trim($registros[$j][0]).' '.trim($registros[$j][2]).' '.trim($registros[$j][4]).' '.trim($registros[$j][5]).'" target="control" onclick ="return confirmar_eliminacion();"><img src="/sistemaweb/icons/delete.gif" alt="Eliminar" align="middle" border="0"/></A></td>';
		                 */ 
	      $listado .= '</tr>';
	      
       if ($registros[$j+1][0]!=$registros[$j][0]){
      		$listado .= '<tr height="20px;"><td colspan="4"> </td></tr>';
       }
    }
     if (count($registros)>0) 
    	$listado .= '<tr height="20px;"><td colspan="4" align="center"><input type="submit" name="action" value="Autorizar" /></td></tr></table>';
    $listado .= '</form>';
    $listado .= '</div>';
    return $listado;
  }
  
  function formEditar(){
  	//$codigo_fin= $_REQUEST['busqueda']['codigo'];
  	//$Clientes = EspecialesModel::getClientes($codigo_fin);    
    $form = new form2('', 'frmbuscar', FORM_METHOD_POST, 'control.php', '', 'control','onsubmit="return habilitar_caja();"');
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'FACTURACION.DESCUENTOS'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'DESCUENTOS'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('busqueda[codigo]', $_REQUEST['busqueda']['codigo']));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="3" cellpadding="3"> <tr><td class="form_label">'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('codigo','Codigo </td><td>:', $_REQUEST['codigo'] , espacios(2), 10, 10, array("onKeyUp"=>"this.value=this.value.toUpperCase();"),array('disabled')));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('descuento','Descuento </td><td>:', '' , espacios(2), 4, 4,array("onKeyUp"=>"getRegistroDesc(this.value);","onKeyPress"=>"return validar(event,2);")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="desc_descuento" style="display:inline;">'.$Descuentos['Datos'][$_REQUEST['codigo']].'</div>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Cambiar',espacios(3)));
    $form->addElement(FORM_GROUP_MAIN, new f2element_button('action','Regresar',espacios(3),array('onclick'=>'return volver_atras();')));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));    

    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</center>'));
    return $form->getForm();
  }
  function setRegistrosDesc($codigo)
  {
    $RegistrosCB = DescuentosModel::DescuentosCBArray("substring(tab.tab_elemento for 2 from length(tab_elemento)-1) ~ '".pg_escape_string($codigo)."'");
    $result = '<blink><span class="MsgError">Error..</span></blink>';
    if (count($RegistrosCB['Datos']) == 1) {
      foreach($RegistrosCB['Datos'] as $cod => $descri){
        $result = $descri." <script language=\"javascript\">top.setRegistroDesc('".trim($cod)."');</script>";
      //CalcularValores();
      }
    }
    if (count($RegistrosCB['Datos']) > 1){
      $att_opt = array();
      foreach($RegistrosCB['Datos'] as $cod => $descri){
        $att_opt[trim($cod)] = array(" onclick"=>"getRegistroDesc('".trim($cod)."');");
      }
      $cb = new f2element_combo('cbDatosDesc', '','', $RegistrosCB['Datos'],'',array("size"=>"5"), array(), $att_opt);
      $result = '<div align="left" style="position: absolute; width:250px;  z-index:1000;">'.$cb->getTag().'</div>';
    }
    return $result;
  }
}

?>