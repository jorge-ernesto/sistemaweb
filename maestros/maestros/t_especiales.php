<?php
  /*
    Templates para Tablas Generales
    @TBCA
  */
//include('lib/paginador_new.php');
//include ('/sistemaweb/include/reportes2.inc.php');


class EspecialesTemplate extends Template {
 
  function titulo($valor){
    $titulo = '<div align="center"><h2>'.$valor.'</h2></div><hr>';
    return $titulo;
  }

  function errorResultado($errormsg){
    return '<blink>'.$errormsg.'</blink>';
  }

  function listado($registros){
  
    $columnas = array('CODIGO','DESCRIPCION','FECHA INICIO','FECHA FIN','PRECIO','CARTA DE REFERENCIA', 'ESTADO');
    
    $listado ='<div id="error_body" align="center"></div>';
    $listado .= '<div id="resultados_grid" class="grid" align="center"><br>';
    if (count($registros)<=0){
    	$listado.='<table border="0" width="100%"><tr height="10px;" class="grid_row" '.resaltar('white','#CDCE9C').'><td colspan="6" class="grid_item" align="center"> No Registros Existentes </td></tr></table>';
    }
   
    for($j=0;$j<count($registros);$j++){
    	
    		
    	 if ($registros[$j-1][0]!=$registros[$j][0]){
	    	$titulo_grid = $registros[$j][0]." - ".$registros[$j][1];
	    	$listado .= '<table border="0" width="100%">
	                 <caption class="grid_title">'.$titulo_grid.'</caption>';
	    	for($i=0;$i<count($columnas);$i++){
		   		$listado .= '<th class="grid_columtitle"> '.$columnas[$i].'</th>';
			}
		 }
    	if ($registros[$j][9]=='t')
   	        $listado .= '<tr height="10px;" class="grid_row" >';
    	else 
    	  	$listado .= '<tr height="10px;" class="grid_pendiente" >';
    	  	
	      $listado .= '<td class="grid_item" align="center">'.$registros[$j][2].'</td>';
	      $listado .= '<td class="grid_item" align="center">'.$registros[$j][3].'</td>';
	      $listado .= '<td class="grid_item" align="center">'.$registros[$j][4].'</td>';
	      $listado .= '<td class="grid_item" align="center">'.($registros[$j][5]=='2999-01-01'?'INDEFINIDO':$registros[$j][5]).'</td>';
	      $listado .= '<td class="grid_item" align="center">'.$registros[$j][6].'</td>';
	      $listado .= '<td class="grid_item" align="center">'.$registros[$j][7].'</td>';
	      $listado .= '<td class="grid_item" align="center">'.(($registros[$j][9]=='t')?'AUTORIZADO':'PENDIENTE').'</td>';
	      
	      	if ($registros[$j][9]=='f'){
		      $listado .= '<td><A href="control.php?rqst=MAESTROS.ESPECIALES&task=ESPECIALES'.
		  	              '&action=Modificar&registroid='.trim($registros[$j][8]).' '.trim($registros[$j][0]).' '.trim($registros[$j][2]).' '.trim($registros[$j][4]).'" target="control"><img src="/sistemaweb/icons/open.gif" alt="Editar" align="middle" border="0"/></A></td>';
		      $listado .= '<td><A href="control.php?rqst=MAESTROS.ESPECIALES&task=ESPECIALES'.
		                  '&action=Eliminar&busqueda[codigo]='.trim($registros[$j][0]).'&registroid='.trim($registros[$j][8]).' '.trim($registros[$j][0]).' '.trim($registros[$j][2]).' '.trim($registros[$j][4]).' '.trim($registros[$j][5]).'" target="control" onclick ="return confirmar_eliminacion();"><img src="/sistemaweb/icons/delete.gif" alt="Eliminar" align="middle" border="0"/></A></td>';
	      	}
	      
	      $listado .= '</tr>';
	      
       if ($registros[$j+1][0]!=$registros[$j][0]){
      		$listado .= '<tr height="20px;"><td colspan="8"> </td></tr></table>';
       }
    }
     
    $listado .= '</div>';
    return $listado;
  }

  function formModificar($registros){
  	
  	$cbtipoprecio = array('C'=>'Cliente','G'=>'Grupo Empresarial');
  	$cbtipocliente = array('credito'=>'Pago a Credito','contado'=>'Pago al Contado');
  	if(!empty($datos["datos[ch_codigo_cliente_grupo]"])){
        $Clientes = EspecialesModel::getClientes($datos["datos[ch_codigo_cliente_grupo]"]);
    }
    
    if(!empty($datos["datos[art_codigo]"])){
        $Articulos = EspecialesModel::getArticulos($datos["datos[art_codigo]"]);
    }
   	
  	$form = new form2('', 'frmmodificar', FORM_METHOD_POST, 'control.php', '', 'control',' onSubmit = "return validar_guardar()"');
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MAESTROS.ESPECIALES'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'ESPECIALES'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('paginacion[codigo]', $registros['ch_codigo_cliente_grupo']));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('registroid', @$_REQUEST['registroid']));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="2" cellpadding="2"> <tr><td class="form_label">'));
    $arrParam = array();
    if ($_REQUEST['action']=='Modificar')
    	$arrParam = array('disabled'=>'true');
    else 
    	$arrParam = array("onkeypress"=>"javascript:this.value=this.value.toUpperCase();");
    
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_combo('datos[ch_tipo_precio]','Tipo Precio: </td><td>', trim(@$registros["ch_tipo_precio"]), $cbtipoprecio, espacios(3),$arrParam));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td class="form_label">'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[ch_codigo_cliente_grupo]','Codigo :</td><td>', @$registros['ch_codigo_cliente_grupo'], espacios(2), 10, 10,array_merge($arrParam,array("onKeyUp"=>"this.value=this.value.toUpperCase();getRegistronuevo(this.value);"))));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="desc_codigo" style="display:inline;">'.$Clientes['Datos'][trim($registros['ch_codigo_cliente_grupo'])].'</div>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td class="form_label">'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_combo('datos[ch_tipo_cliente]','Tipo Cliente: </td><td>', trim(@$registros["ch_tipo_cliente"]), $cbtipocliente, espacios(3),array_merge($arrParam,array("onChange"=>"javascript:clearCarta(this.value);"))));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td class="form_label">'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[dt_fecha_inicio]','Fecha Inicio :</td><td>', (is_null(@$registros['dt_fecha_inicio'])?date('d/m/Y'):@$registros['dt_fecha_inicio']), espacios(2), 10, 10,$arrParam));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td class="form_label">'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[dt_fecha_fin]','Fecha Fin :</td><td>', (is_null(@$registros['dt_fecha_fin'])?'01/01/2999':@$registros['dt_fecha_fin']), espacios(2), 10, 10,array('disabled'=>'true')));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td class="form_label">'));
   	$form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[art_codigo]','Articulo :</td><td>', @$registros['art_codigo'], espacios(2), 10, 10,array_merge($arrParam,array("onKeyUp"=>"getArticulo(this.value);","onkeypress"=>"return validar(event,2);"))));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="desc_articulo" style="display:inline;">'.$Clientes['Datos'][trim($registros['art_codigo'])].'</div>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td class="form_label">'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[nu_preciopactado]','Precio :</td><td>', @$registros['nu_preciopactado'], espacios(2), 10, 6, array("onKeyPress"=>"return validar(event,3);")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td class="form_label">'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[ch_cartaref]','Carta de Referencia :</td><td>', @$registros['ch_cartaref'], espacios(2), 30, 30,array('style'=>'display:inline')));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</center><br/>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Guardar', espacios(2)));
    $form->addElement(FORM_GROUP_MAIN, new f2element_button('action','Regresar', espacios(2),array('onClick'=>"return volver_a_registro();")));
     return $form->getForm();
  }
  
  function formBuscar(){
  	
    $codigo_fin= isset($_REQUEST['paginacion'])?$_REQUEST['paginacion']['codigo']:$_REQUEST['busqueda[codigo]'];
  	$Clientes = EspecialesModel::getClientes($codigo_fin);    
    $form = new form2('', 'frmbuscar', FORM_METHOD_POST, 'control.php', '', 'control');
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MAESTROS.ESPECIALES'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'ESPECIALES'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('busqueda[codigo]','Codigo :', $codigo_fin , espacios(2), 10, 10, array("onKeyUp"=>"procesar(event);this.value=this.value.toUpperCase();getRegistronuevo(this.value);")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="desc_codigo" style="display:inline;">'.$Clientes['Datos'][trim($codigo_fin)].'</div>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Buscar',espacios(3)));
    $form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Reporte',espacios(3)));
    $form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Volver',espacios(0).'<br/>',array('onClick'=>'deshabilitar_check();')));
    $form->addElement(FORM_GROUP_MAIN, new f2element_checkbox('busqueda[todos]', 'Mostrar Todos', 'S', '', array()));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</center>'));
  
    return $form->getForm();
  }

  function setRegistros($codigo)
  {
    $RegistrosCB = EspecialesModel::getClientes($codigo);
    $result = '<blink><span class="MsgError">Error..</span></blink>';
    if (count($RegistrosCB['Datos']) == 1) {
      foreach($RegistrosCB['Datos'] as $cod => $descri){
         $result = $descri." <script language=\"javascript\">top.setRegistro('".trim($cod)."');</script>";
      }
    }
    if (count($RegistrosCB['Datos']) > 1){
      $att_opt = array();
      foreach($RegistrosCB['Datos'] as $cod => $descri){
        $att_opt[trim($cod)] = array(" onclick"=>"getRegistronuevo('".trim($cod)."');");
      }
      $cb = new f2element_combo('cbDatosClientes', '','', $RegistrosCB['Datos'],'',array("size"=>"5"), array(), $att_opt);
      $result = '<div align="left" style="position: absolute; width:250px;  z-index:1000;">'.$cb->getTag().'</div>';
    }
    return $result;
  }
 
  function setArticulo($codigo)
  {
    $RegistrosCB = EspecialesModel::getArticulos($codigo);
    $result = '<blink><span class="MsgError">Error..</span></blink>';
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
  
  function formRegistrar(){
  	
  	if ($_REQUEST['action']=='Guardar'){
  		$codigo_fin = (isset($_REQUEST['paginacion']['codigo'])?$_REQUEST['paginacion']['codigo']:$_REQUEST['busqueda']['codigo']);
  		
  	}else {
  		$codigo_fin = (isset($_REQUEST['paginacion'])?$_REQUEST['paginacion']:$_REQUEST['busqueda']['codigo']);
  		
  	}
    $Clientes = EspecialesModel::getClientes($codigo_fin);
    $form = new form2('', 'frmbuscar', FORM_METHOD_POST, 'control.php', '', 'control');
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MAESTROS.ESPECIALES'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'ESPECIALES'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('busqueda[codigo]','Codigo :', $codigo_fin, espacios(2), 10, 10, array("onKeyUp"=>"this.value=this.value.toUpperCase();getRegistronuevo(this.value);")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="desc_codigo" style="display:inline;">'.$Clientes['Datos'][trim($codigo_fin)].'</div>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Detallar',espacios(3)));
    $form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Agregar',espacios(3)));
    $form->addElement(FORM_GROUP_MAIN, new f2element_button('evento','Consultar y Reportar',espacios(0).'<br/>',array('onclick'=>'return redireccionar_reportes();')));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</center>'));
  
    return $form->getForm();
  }
}

class EspecialesPDFTemplate extends Template {

  function reporte($datos_array)
  {
   
    $columnas = array('CODIGO'=>'CODIGO',
					    'DESCRIPCION'=>'DESCRIPCION',
					    'INICIO'=>'INICIO',
					    'FIN'=>'FIN',
					    'PRECIO'=>'PRECIO',
					    'REFERENCIA'=>'REFERENCIA',
					    'ESTADO'=>'ESTADO');
    $fontsize = 8;
       
    $reporte = new CReportes2();
    $reporte->SetMargins(5, 5, 5);
    $reporte->SetFont("courier", "", $fontsize);
    $reporte->definirColumna("CODIGO", $reporte->TIPO_TEXTO, 10, "L");
    $reporte->definirColumna("DESCRIPCION", $reporte->TIPO_TEXTO, 30, "L");
    $reporte->definirColumna("INICIO",$reporte->TIPO_TEXTO,14,"L");
    $reporte->definirColumna("FIN", $reporte->TIPO_TEXTO, 14, "L");
    $reporte->definirColumna("PRECIO", $reporte->TIPO_TEXTO, 10, "L");
    $reporte->definirColumna("REFERENCIA", $reporte->TIPO_TEXTO, 25, "L");
    $reporte->definirColumna("ESTADO", $reporte->TIPO_TEXTO, 15, "L");
    $reporte->AddPage();
    $cabecera = array('CODIGO'=>'001','DESCRIPCION'=>'SISTEMA WEB','INICIO'=>'','FIN'=>'PRECIOS ESPECIALES','PRECIO'=>'','REFERENCIA'=>'');
    $reporte->nuevaFila($cabecera);
    $reporte->Ln();
    $reporte->Ln();
   
    for($j=0;$j<count($datos_array);$j++){
    	if ($datos_array[$j-1][0] != $datos_array[$j][0]){
	    	$codigo = $datos_array[$j][0];
	    	$descripcion = $datos_array[$j][1];
	    	$titulo1=array('CODIGO'=>$codigo,'DESCRIPCION'=>$descripcion);
	    	$reporte->nuevaFila($titulo1);
	    	$reporte->lineaH();
	    	$reporte->nuevaFila($columnas);
	    	$reporte->lineaH();
		 }
      	  
   	    $datos['CODIGO']     			= $datos_array[$j][2];
		$datos['DESCRIPCION']        	= $datos_array[$j][3];
		$datos['INICIO']	     	= $datos_array[$j][4];
		$datos['FIN']     		= ($datos_array[$j][5]=='2999-01-01')?'INDEFINIDO':$datos_array[$j][5];
		$datos['PRECIO']  				= $datos_array[$j][6];
		$datos['REFERENCIA']   = $datos_array[$j][7];
		$datos['ESTADO']   = (($datos_array[$j][9]=='t')?'AUTORIZADO':'PENDIENTE');
		$reporte->nuevaFila($datos);
	    
	    if ($datos_array[$j+1][0] != $datos_array[$j][0]){
	        $reporte->lineaH();
	      	$reporte->Ln();
	      	$reporte->Ln();
	    }
    }
    
    $reporte->Output("/sistemaweb/maestros/reportes/pdf/reporte_especiales.pdf", "F");
    return '<iframe src="/sistemaweb/maestros/reportes/pdf/reporte_especiales.pdf" width="900" height="300"></iframe>';
  }
  
}

