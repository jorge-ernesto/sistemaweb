<?php
class VentaGranelTemplate extends Template {

	function titulo() {
		return '<div align="center"><h2>Migrar datos de Compra y Venta a Granel</h2></div>';
	}
	
	function formBuscar($desde, $hasta, $ruc, $pedido) {	

		$form = new form2('', 'form_buscar', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'XPORTA.VENTAGRANEL2'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0">'));    
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Ultima Exportada</td><td>:</td><td></tr>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Exportar Desde</td><td>:</td><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("desde", "", $desde, '', 10, 12));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_buscar.desde'".');"><img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div style="position:relative"><div id="overDiv" style="position:absolute;float:right; visibility:hidden; z-index:0;"></div></div>'));
		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>Hasta</td><td>:</td><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("hasta", "", $hasta, '', 10, 12));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_buscar.hasta'".');"><img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
 
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));		

		

							
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="center" colspan="3">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Agregar"><img src="/sistemaweb/icons/export.gif" alt="left"/> Exportar</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Buscar"><img src="/sistemaweb/images/search.gif" alt="left"/> Buscar</button>&nbsp;&nbsp;&nbsp;'));		

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));

		return $form->getForm();
		
	}    	
	function carga(){
		
	
		
		$form = new form2('', 'form_carga', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'XPORTA.VENTAGRANEL2'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0">'));    
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Pasando Datos</td><td>:</td></tr>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td><img src="/sistemaweb/images/loading.gif" border="0" align="top"/></td><td>:</td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr></table>'));
				
		return $form->getForm();		
					
	}
	
	function listado($res,$desde,$hasta,$ruc,$pedido) {
		
		$form = new form2('', 'form_listado', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "XPORTA.VENTAGRANEL2"));
		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table><tr>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera" rowspan="2">&nbsp;CODIGO&nbsp;<br>&nbsp;PEDIDO</th>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera" rowspan="2">&nbsp;FECHA&nbsp;<br>&nbsp;REGISTRO</th>'));				
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera" rowspan="2">&nbsp;RUC&nbsp;</th>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera" rowspan="2">&nbsp;RAZON&nbsp;<br>&nbsp;SOCIAL&nbsp;</th>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera" rowspan="2">&nbsp;CODIGO&nbsp;<br>&nbsp;ANEXO&nbsp;</th>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera" rowspan="2">&nbsp;DISTRITO&nbsp;</th>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera" rowspan="2">&nbsp;GALONES&nbsp;</th>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera" rowspan="2">&nbsp;PRECIO&nbsp;</th>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera" rowspan="2">&nbsp;NRO.&nbsp;<br>&nbsp;SCOP&nbsp;</th>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera" colspan="3">&nbsp;DESPACHO&nbsp;</th>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera" rowspan="2">&nbsp;FACTURA&nbsp;</th>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera" rowspan="2">&nbsp;</th>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera" rowspan="2">&nbsp;</th>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr><tr><th class="grid_cabecera">&nbsp;FECHA&nbsp;</th><th class="grid_cabecera">&nbsp;CANTIDAD&nbsp;</th><th class="grid_cabecera">&nbsp;GOLPE&nbsp;</th></tr>'));

		for ($i = 0; $i < count($res); $i++) {	
			$color = ($i%2==0?"grid_detalle_par":"grid_detalle_impar");			
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));			
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">&nbsp;' . htmlentities($res[$i][0]) . '</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">&nbsp;' . htmlentities($res[$i][1]) . '</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">&nbsp;' . htmlentities($res[$i][2]) . '</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left" class="'.$color.'">&nbsp;' . htmlentities($res[$i][3]) . '</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">&nbsp;' . htmlentities($res[$i][4]) . '</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">&nbsp;' . htmlentities($res[$i][5]) . '</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">&nbsp;' . htmlentities($res[$i][6]) . '</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">&nbsp;' . htmlentities($res[$i][7]) . '</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">&nbsp;' . htmlentities($res[$i][8]) . '</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">&nbsp;' . htmlentities($res[$i][9]) . '</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">&nbsp;' . htmlentities($res[$i][10]) . '</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">&nbsp;' . htmlentities($res[$i][11]) . '</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">&nbsp;' . htmlentities($res[$i][12]) . '</td>'));
			
			if($res[$i][12]==0 and $res[$i][9]=='2000-01-01 00:00:00'){ // no tiene numero de factura o fecha de despacho -> modifica
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="'.$color.'"><A href="control.php?rqst=XPORTA.VENTAGRANEL2&action=Editar&codpedido='.$res[$i][0].'&ruc='.$res[$i][2].'&codanexo='.$res[$i][4].'" target="control"><img alt="Editar" title="Editar" src="/sistemaweb/icons/anular.gif" align="middle" border="0"/></A></td>'));						
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="'.$color.'"><A href="javascript:confirmarLink(\'Desea eliminar la matricula?\',\'control.php?rqst=XPORTA.VENTAGRANEL2&action=Eliminar&codpedido='.$res[$i][0].'&ruc='.$res[$i][2].'&codanexo='.$res[$i][4].'&desde='.$desde.'&hasta='.$hasta.'&lisruc='.$ruc.'&lispedido='.$pedido.'\', \'control\')"><img src="./icons/delete.gif" alt="Eliminar" align="middle" border="0"/></A></td>'));			
			} else { 
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="'.$color.'">&nbsp;</td>'));						
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="'.$color.'">&nbsp;</td>'));
			}
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));			
		}
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</table></center>'));
		
		return $form->getForm();
    	}
    	
    	function formAgregar($opcion, $res, $idx) {	//array("readonly")
			
			
		if($opcion == "A") {
			$nombre 	= "Adicionar Ruta";
			$nombutton 	= "Adicionar";
			$habilitado 	= "";
			$pedido 	= $idx; // codigo autogenerado
			$fecha 		= date("d/m/Y H:m:s");
			$ruc 		= "";
			$razsocial 	= "";
			$dirfiscal 	= "";
			$codanexo 	= "";
			$diranexo 	= "";
			$cantidad 	= "";
			$precio 	= "";
			$scop 		= "";
			$diascre 	= "";
			$distrito 	= "";
		} else { // modificar
			$nombre 	= "Modificar Ruta";
			$nombutton 	= "Modificar";
			$habilitado 	= array('readonly');
			$pedido 	= $res[0];
			$fecha 		= $res[1];
			$ruc 		= $res[2];
			$razsocial 	= $res[3];
			$dirfiscal 	= $res[4];
			$codanexo 	= $res[5];
			$diranexo 	= $res[6];
			$cantidad 	= $res[7];
			$precio 	= $res[8];
			$scop 		= $res[9];
			$diascre 	= $res[10];
			$distrito 	= $res[11];
		}

		$form = new form2($nombre, 'form_agregar', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'XPORTA.VENTAGRANEL2'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0">'));  
		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Codigo de Pedido</td><td>:</td><td colspan="2">'));		
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('codpedido','', $pedido, '</td></tr>', 18, 18, '', $habilitado));
		  
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Fecha de Registro</td><td>:</td><td colspan="2">'));		
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('fecregistro','', $fecha, '</td></tr>', 18, 18, '', array('readonly')));  
		  
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Ruc</td><td>:</td><td>'));		
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('ruc','', $ruc, '', 15, 13, '', $habilitado));  
		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<img src="../images/help.gif" width="16" height="15" onMouseOver="this.style.cursor=\'hand\'" onclick="javascript:mostrarAyuda(\'ayuda_granel.php\',\'form_agregar.ruc\',\'form_agregar.razsocial\',\'form_agregar.dirfiscal\',\'clientes\',\'\',\'<?php echo $valor;?>\');"> ')); 		
		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td>'));		
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('razsocial','', $razsocial, '</td></tr>', 40, 80, '', $habilitado));  
		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Direccion Fiscal</td><td>:</td><td colspan="2">'));		
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('dirfiscal','', $dirfiscal, '</td></tr>', 58, 80, '', $habilitado));
		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Codigo Lugar Anexo</td><td>:</td><td>'));		
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('codanexo','', $codanexo, '', 13, 10, '', $habilitado));  

$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<img src="../images/help.gif" width="16" height="15" onMouseOver="this.style.cursor=\'hand\'" onclick="javascript:mostrarAyuda2(\'ayuda_granel2.php\',\'form_agregar.ruc\',\'form_agregar.codanexo\',\'form_agregar.diranexo\',\'form_agregar.precio\',\'anexos\',\'\',\'<?php echo $valor;?>\');"> ')); 		
		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td>'));		
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('diranexo','', $diranexo, '</td></tr>', 40, 80, '', $habilitado));  
		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Producto</td><td>:</td><td>'));		
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('producto','', '11620307', '', 15, 13, '', array('readonly')));  
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td>'));		
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('nomproducto','', 'GLP', '</td></tr>', 15, 13, '', array('readonly')));  
		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Galones Pedido</td><td>:</td><td colspan="2">'));		
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('galones','', $cantidad, '</td></tr>', 15, 15));
		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Precio Unitario</td><td>:</td><td colspan="2">'));		
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('precio','', $precio, '</td></tr>', 15, 15));
		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Numero Scop</td><td>:</td><td colspan="2">'));		
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('scop','', $scop, '</td></tr>', 15, 15));
		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Dias de Credito</td><td>:</td><td colspan="2">'));		
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('diascredito','', $diascre, '</td></tr>', 10, 10));
		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Codigo de Distrito</td><td>:</td><td colspan="2">'));		
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('distrito','', $distrito, '</td></tr>', 15, 15));
									
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="center" colspan="4">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="'.$nombutton.'">'.$nombutton.'</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Cancelar" onclick="javascript:regresar()" >Cancelar</button>'));		

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));

		return $form->getForm();
	}  
}
