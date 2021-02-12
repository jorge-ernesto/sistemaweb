<?php
  /*
    Templates para Tablas Generales
    @TBCA
  */

class ContClienteTemplate extends Template {
 
  function titulo(){
    $titulo = '<div align="center"><h2>CONTROL DE CLIENTE</h2></div><hr>';
    return $titulo;
  }

  function errorResultado($errormsg){
    return '<blink><span class="MsgError">'.$errormsg.'</span></blink>';
  }
    
  function listado($registros){
  	$columnas = array('COMBUSTIBLE','LIM. GALONES','SAL. GALONES','LIM. IMPORTE','SAL. IMPORTE','INICIO','FIN','ESTADO','ULT. CONSUMO','ACCION');
    
    $listado ='<div id="error_body" align="center"></div>';
    $listado .= '<div id="resultados_grid" class="grid_item" align="left"><br>';
    $listado .= '<table border="1" width="100%"><caption class="grid_title">'.$titulo_grid.'</caption>';
    for($i=0;$i<count($columnas);$i++){
		$listado .= '<th class="grid_columtitle"> '.$columnas[$i].'</th>';
	}
     
    for($j=0;$j<count($registros);$j++){
    	 $listado .= '<tr height="10px;" class="grid_row" '.resaltar('white','#CDCE9C').' >';
    	 $listado .= '<td class="grid_item" align="center">'.($registros[$j][2]==''?'CUALQUIERA':$registros[$j][2]).'</td>';
	     $listado .= '<td class="grid_item" align="center">'.$registros[$j][3].'</td>';
	     $listado .= '<td class="grid_item" align="center">'.$registros[$j][4].'</td>';
	     $listado .= '<td class="grid_item" align="center">'.$registros[$j][5].'</td>';
	     $listado .= '<td class="grid_item" align="center">'.$registros[$j][6].'</td>';
	     $listado .= '<td class="grid_item" align="center">'.$registros[$j][7].'</td>';
	     $listado .= '<td class="grid_item" align="center">'.$registros[$j][8].'</td>';
	     $listado .= '<td class="grid_item" align="center">'.$registros[$j][9].'</td>';
	     $listado .= '<td class="grid_item" align="center">'.$registros[$j][10].'</td>';
	     $listado .= '<td class="grid_item" align="center"><A href="control.php?rqst=FACTURACION.CONTROL&task=CLIENTE'.
		  	              '&action=Eliminar&busqueda[codigo]='.$_REQUEST['busqueda']['codigo'].'&codigo='.trim($registros[$j][1]).trim($registros[$j][0]).'" onclick="return confirm(\'Desea Eliminar el combustible de la lista?\');" target="control"><img src="/sistemaweb/icons/delete.gif" alt="Editar" align="middle" border="0"/></A>
		  	              <A href="control.php?rqst=FACTURACION.CONTROL&task=CLIENTE'.
		  	              '&action=Modificar&busqueda[codigo]='.$_REQUEST['busqueda']['codigo'].'&codigo='.trim($registros[$j][1]).trim($registros[$j][0]).'" target="control"><img src="/sistemaweb/icons/open.gif" alt="Editar" align="middle" border="0"/></A>
		  	          </td>';
	     $listado .= '</tr>';
	}
   
    $listado .= '</table></div>';
    return $listado;
  }
  
  function formAgregar(){
  	
  	$clientetotal = ContClienteModel::devolverCriterioControl($_REQUEST['codigo']);
  	$descripcion_cliente = ContClienteModel::getClientes($clientetotal['cliente']);
  	$descripcion_articulo = ContClienteModel::getArticulos(trim($clientetotal['tipo_combustible']));
  	$aux1=split('-',$clientetotal['fec_inicio']);
  	$clientetotal['fec_inicio'] = $aux1[2].'/'.$aux1[1].'/'.$aux1[0];
  	$aux1=split('-',$clientetotal['fec_fin']);
  	$clientetotal['fec_fin'] = $aux1[2].'/'.$aux1[1].'/'.$aux1[0];
  	$form = new form2('', 'frmagregar', FORM_METHOD_POST, 'control.php', '', 'control',' onSubmit = "return validar_guardar()"');
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'FACTURACION.CONTROL'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'CLIENTE'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('busqueda[codigo]', $_REQUEST['busqueda']['codigo']));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('registroid', $_REQUEST['codigo']));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="2" cellpadding="2"> <tr><td class="form_label">'));
    $arrParam = array();
    $arraLimiteGalon=array();
    $arraLimiteImporte=array();
    if ($_REQUEST['action']=='Modificar') {
    	$arrParam = array('disabled'=>'true');
    	if ($clientetotal['lim_importe']=='0.00'){
    		$valordis=1;
    	}else{
    		$valordis=0;
    	}
    	if ($clientetotal['lim_galones']=='0.00'){
    		$valorgal=1;
    	}else{
    		$valorgal=0;
    	}
    
    }
    
   print_r('limite:'.$clientetotal['lim_importe'].'valor:'.$valordis);
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[cliente]','Cliente :</td><td>', @$clientetotal['cliente'], espacios(2), 6, 6,array_merge($arrParam,array("onKeyUp"=>"this.value=this.value.toUpperCase();getRegistroCli(this.value);","onChange"=>"clearAll();"))));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="desc_cliente" style="display:inline;">'.$descripcion_cliente['Datos'][$clientetotal['cliente']].'</div>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td class="form_label">'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[tipo_combustible]','Combustible :</td><td>', @$clientetotal['tipo_combustible'], espacios(2), 9, 8,array_merge($arrParam,array("onKeyUp"=>"getArticulo(this.value);","onChange"=>"clearAll();"))));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="desc_articulo" style="display:inline;">'.$descripcion_articulo['Datos'][trim($clientetotal['tipo_combustible'])].'</div>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td class="form_label">'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[lim_importe]','Lim. Importe :</td><td>', @$clientetotal['lim_importe'], espacios(2), 10, 10,array_merge($arrParam,array("onKeyUp"=>"bloquea(this,document.getElementsByName('datos[lim_galones]')[0],document.getElementsByName('datos[sal_galones]')[0],document.getElementsByName('datos[sal_importe]')[0]);","onKeyPress"=>"return validar(event,3);"))));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td class="form_label">'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[sal_importe]','Sal. Importe :</td><td>', @$clientetotal['sal_importe'], espacios(2), 10, 10,$valordis==1?array('disabled'=>'true'):array(), array("onKeyUp"=>"return validar(event,3);")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td class="form_label">'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[lim_galones]','Lim. Galones :</td><td>', @$clientetotal['lim_galones'], espacios(2), 10, 10,array_merge($arrParam,array("onKeyUp"=>"bloquea(this,document.getElementsByName('datos[lim_importe]')[0],document.getElementsByName('datos[sal_importe]')[0],document.getElementsByName('datos[sal_galones]')[0]);","onKeyPress"=>"return validar(event,3);"))));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td class="form_label">'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[sal_galones]','Sal. Galones :</td><td>', @$clientetotal['sal_galones'], espacios(2), 10, 10,$valorgal==1?array('disabled'=>'true'):array(),array("onKeyUp"=>"return validar(event,3);")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td class="form_label">'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('fec_inicio','Fecha Inicio :</td><td>', $_REQUEST['action']=='Agregar'?date('d/m/Y'):@$clientetotal['fec_inicio'], espacios(2), 10, 10,array('readonly'=>'true')));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'frmagregar.fec_inicio',null,null,null,670,265".');"><img src="/sistemaweb/images/show-calendar.gif" width=24 height=22 border=0></a>&nbsp;&nbsp;'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td class="form_label">'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('fec_fin','Fecha Fin :</td><td>', $_REQUEST['action']=='Agregar'?'':@$clientetotal['fec_fin'], espacios(2), 10, 10,array('readonly'=>'true')));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'frmagregar.fec_fin',null,null,null,670,298".');"><img src="/sistemaweb/images/show-calendar.gif" width=24 height=22 border=0/></a>&nbsp;&nbsp;'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</center><br/>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Guardar', espacios(2)));
    $form->addElement(FORM_GROUP_MAIN, new f2element_button('action','Regresar', espacios(2),array('onClick'=>"return volver_a_registro();")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="error_body" align="center"></div>'));
    return $form->getForm();
  }
  
  function formBuscar(){
  	$Clientes = ContClienteModel::getClientes($_REQUEST['busqueda']['codigo']);
  	$form = new form2('', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control');
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'FACTURACION.CONTROL'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'CLIENTE'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('busqueda[codigo]','Codigo :', $_REQUEST['busqueda']['codigo'], espacios(2), 20, 18, array("onkeyup"=>"this.value=this.value.toUpperCase();getRegistroCliente(this.value);")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="desc_cliente" style="display:inline;">'.$Clientes['Datos'][trim($_REQUEST['busqueda']['codigo'])].'</div>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Buscar',espacios(3)));
    $form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Agregar',espacios(3)));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</center>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));
    return $form->getForm();
  }
  
  function setRegistrosCliente($codigo){
    $RegistrosCB = ContClienteModel::ClientesCBArray("trim(cli_codigo)||''||trim(cli_razsocial) ~ '".pg_escape_string($codigo)."'");
    $result = '<blink><span class="MsgError">Error..</span></blink>';
    if (count($RegistrosCB) == 1) {
      foreach($RegistrosCB as $cod => $descri){
      	$result = $descri." <script language=\"javascript\">top.setRegistroCliente('".trim($cod)."');</script>";
      }
    }
    if (count($RegistrosCB) > 1){
      $att_opt = array();
      foreach($RegistrosCB as $cod => $descri){
        $att_opt[trim($cod)] = array("onclick"=>"getRegistroCliente('".trim($cod)."');");
      }
      $cb = new f2element_combo('cbDatosCliente', '','', $RegistrosCB,'',array("size"=>"5"), array(), $att_opt);
      $result = '<div align="left" style="position: absolute; width:250px;  z-index:1000;">'.$cb->getTag().'</div>';
    }
    return $result;
  }
  
  function setRegistrosCliente2($codigo){
    $RegistrosCB = ContClienteModel::ClientesCBArray("trim(cli_codigo)||''||trim(cli_razsocial) ~ '".pg_escape_string($codigo)."'");
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
  
  
  function setArticulo($codigo){
    $RegistrosCB = ContClienteModel::getArticulos($codigo);
   // $result = '<blink><span class="MsgError">Error..</span></blink>';
    if (count($RegistrosCB['Datos']) == 1) {
      foreach($RegistrosCB['Datos'] as $cod => $descri){
         $result = $descri." <script language=\"javascript\">top.setArticulo('".trim($cod)."');</script>";
      }
    }
    if (count($RegistrosCB['Datos']) > 1){
      $att_opt = array();
      foreach($RegistrosCB['Datos'] as $cod => $descri){
        $att_opt[trim($cod)] = array(" onclick"=>"getArticulo('".trim($cod)."');");
      }
      $cb = new f2element_combo('cbDatosArticulos', '','', $RegistrosCB['Datos'],'',array("size"=>"5"), array(), $att_opt);
      $result = '<div align="left" style="position: absolute; width:250px;  z-index:1000;">'.$cb->getTag().'</div>';
    }
    return $result;
  }
 
}

?>