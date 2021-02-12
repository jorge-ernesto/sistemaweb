<?php
  /*
    Templates para Tablas Generales
    @TBCA
  */
//include('lib/paginador_new.php');

class LadosTemplate extends Template {
//METODO QUE DEVUELVE EL TITULO
	function titulo(){
		$titulo = '<div align="center"><h2>LADOS</h2></div><hr>';
		return $titulo;
	}
	//METODO QUE RETORNA UN MENSAJE DE ERROR
	function errorResultado($errormsg){
		return '<blink>'.$errormsg.'</blink>';
	}
	
	function listado($registros){
	
		$titulo_grid = "LADOS";

		$columnas = array('LADO','PROD.1','PROD.2','PROD.3','PROD.4','#D. CANTIDAD','#D. PRECIO','#D. IMPORTE','#D.Q CONTOMENTRO','#D.$ CONTOMETRO','ID INTERFASE','LADO INTERFASE');
		
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
			$listado .= '<tr class="grid_row" '.resaltar('white','#CDCE9C').'>';
			$regCod = $reg["lado"];
		
			for ($i=0; $i < count($columnas); $i++){
				$listado .= '<td class="grid_item">'.$reg[$i].'</td>';
			}
			
			$listado .= '<td> <A href="control.php?rqst=MAESTROS.LADOS&task=LADOS&action=Modificar&registroid='.$regCod.'" target="control">
					<img src="/sistemaweb/icons/open.gif" align="middle" border="0"/></A>&nbsp;';
		
			$listado .= '<A href="javascript:confirmarLink(\'Desea borrar al lado con codigo '.$regCod.'\',\'control.php?rqst=MAESTROS.LADOS&task=LADOS'.
				'&action=Eliminar&registroid='.$regCod.'\', \'control\')"><img src="/sistemaweb/icons/delete.gif" alt="Borrar" align="middle" border="0"/></A></td><td>&nbsp;</td>';
		
			$listado .= '</tr>';
		}
		$listado .= '</tbody></table></div>';
		return $listado;
	}
	
	// Solo Formularios y otros
	function formBuscar($paginacion){
	
		//echo "ENTRO BUSCAR\n";
		$form = new form2('', 'Buscar', FORM_METHOD_GET, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MAESTROS.LADOS'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'LADOS'));
		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('busqueda[parametro]','Producto :', '', espacios(2), 20, 18));
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Buscar',espacios(3)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Agregar',espacios(3)));

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
	
	function formLados($lado){
		$form = new form2('DATOS DE LADOS', 'form_Lados', FORM_METHOD_POST, 'control.php', '', 'control','onSubmit="return validar_registro_lado();"');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MAESTROS.LADOS'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'LADOS'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('grupo', ''));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('auxilio', ''));

		if($_REQUEST['action'] == 'Modificar'){
			$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('accion', 'actualizar'));
		}
		
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('registroid', @$lado["lado"]));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="0" cellpadding="0"> <tr><td bgcolor="#FFFFCD">'));
		// Inicio Contenido TD 1
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="2" cellpadding="2"> <tr><td colspan="2" align="center" class="form_td_title">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('DATOS DE LADOS  </td></tr><tr><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
			
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('lado[txtlado]','Lado  </td><td>: ', trim(@$lado["lado"]), '', 5, 2,'',($_REQUEST['action']=='Modificar'?array('readonly'):array())));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));

		//COMBO PRODUCTOS
		//PROD 1
		$CbListaProd1     = LadosModel::ListadoProductos();		
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo('lado[cbxprod1]','Producto 1 </td><td>: ', $lado["prod1"], $CbListaProd1, espacios(3), array("onChange"=>"javascript:ClearSerieAlmacen(this.value);"),array($deshabilitar)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
		
		//PROD 2
		$CbListaProd2     = LadosModel::ListadoProductos();		
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo('lado[cbxprod2]','Producto 2 </td><td>: ', $lado["prod2"], $CbListaProd2, espacios(3), array("onChange"=>"javascript:ClearSerieAlmacen(this.value);"),array($deshabilitar)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
		
		//PROD 3
		$CbListaProd3     = LadosModel::ListadoProductos();		
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo('lado[cbxprod3]','Producto 3 </td><td>: ', $lado["prod3"], $CbListaProd3, espacios(3), array("onChange"=>"javascript:ClearSerieAlmacen(this.value);"),array($deshabilitar)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
		
		//PROD 4
		$CbListaProd4     = LadosModel::ListadoProductos();		
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo('lado[cbxprod4]','Producto 4 </td><td>: ', $lado["prod4"], $CbListaProd4, espacios(3), array("onChange"=>"javascript:ClearSerieAlmacen(this.value);"),array($deshabilitar)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));

		//FIN COMBO PRODUCTOS
		
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('lado[txtndcantidad]','Num. Dec. Cantidad  </td><td>: ',trim(@$lado["ndec_cantidad"]),'', 5, 2));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
		
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('lado[txtndprecio]','Num. Dec. Precio  </td><td>: ',trim(@$lado["ndec_precio"]),'', 5, 2));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
		
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('lado[txtndimporte]','Num. Dec. Importe  </td><td>: ',trim(@$lado["ndec_importe"]),'',5, 2));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
		
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('lado[txtndcantidadcontometro]','Num. Dec. Cant. Contometro  </td><td>: ',trim(@$lado["ndec_contometro_cantidad"]),'',5, 2));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
		
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('lado[txtndimportecontometro]','Num. Dec. Impo. Contometro  </td><td>: ',trim(@$lado["ndec_contometro_importe"]),'',5, 2));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
				
		//COMBO ID INTERFASES
		$CbListaTipoDoc     = LadosModel::ListadoInterfases();		
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo('lado[cbxidinterfase]','ID Interfase </td><td>: ', $lado["idinterfase"], $CbListaTipoDoc, espacios(3), array("onChange"=>"javascript:ClearSerieAlmacen(this.value);"),array($deshabilitar)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
		//FIN COMBO
		
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('lado[txtladointerfase]','Lado Interfase  </td><td>: ', trim(@$lado["ladointerfase"]),'',5, 2));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));
		
		// Fin Contenido TD 1
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td bgcolor="#FFFFCD" valign="top" width="25">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td bgcolor="#FFFFCD" valign="topl">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));
		//Fin Contenido TD 2
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="3" align="center" height="30">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Guardar', espacios(15)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_button('button','Regresar', espacios(2),array('onclick'=>'regresar();')));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));
		
		return $form->getForm().'<div id="error_body" align="center"></div><hr>';
	}
}

?>
