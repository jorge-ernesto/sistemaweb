<?php
class VentaGranelTemplate extends Template {

	function titulo() {
		return '<div align="center"><h2>Programacion de Ruta Granel</h2></div>';
	}
	
	function formBuscar($desde, $hasta, $ruc, $pedido,$plac) {	

		$this->visor->addComponent("ContentT", "content_title", VentaGranelTemplate::titulo());
		
		$form = new form2('', 'form_buscar', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MOVIMIENTOS.PROGRAMRUTA'));	
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0">'));    
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Desde</td><td>:</td><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("desde", "", $desde, '', 10, 12));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_buscar.desde'".');"><img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div style="position:relative"><div id="overDiv" style="position:absolute;float:right; visibility:hidden; z-index:0;"></div></div>'));
		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>Hasta</td><td>:</td><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("hasta", "", $hasta, '', 10, 12));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_buscar.hasta'".');"><img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
 
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>Ruc</td><td>:</td><td>'));		
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('ruc','', $ruc, '', 15, 11));
		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>Pedido</td><td>:</td><td>'));		
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('pedido','', $pedido, '', 15, 13));
		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>Placa</td><td>:</td><td>'));		
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('plac','', $plac, '', 15, 13));
							
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td align="left" colspan="3">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Buscar" onclick="javascript:fecha()"><img src="/sistemaweb/images/search.gif" alt="left"/> Buscar</button>&nbsp;&nbsp;&nbsp;'));		

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));

		return $form->getForm();
	}    	
	
	function listado($res,$desde,$hasta,$ruc,$pedido) {
		
		
			$pedido 	= $res[0];
			$fecha 		= $res[1];
			$ruc 		= $res[2];
			$razsocial 	= $res[3];
			$dirfiscal 	= $res[4];
			$codanexo 	= $res[5];
			$diranexo 	= $res[6];
			$cantidad 	= $res[7];
			$precio 	= $res[8];
			
		
		$form = new form2($nombre, 'form_listado', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MOVIMIENTOS.PROGRAMRUTA'));	

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table><tr>'));		
		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera" rowspan="2">&nbsp;CODIGO&nbsp;<br>&nbsp;PEDIDO</th>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera" rowspan="2">&nbsp;FECHA&nbsp;<br>&nbsp;REGISTRO</th>'));		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera" rowspan="2">&nbsp;RUC&nbsp;</th>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera" rowspan="2">&nbsp;RAZON&nbsp;<br>&nbsp;SOCIAL&nbsp;</th>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera" rowspan="2">&nbsp;ANEXO&nbsp;</th>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera" rowspan="2">&nbsp;DISTRITO&nbsp;</th>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera" rowspan="2">&nbsp;GALONES&nbsp;</th>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera" rowspan="2">&nbsp;NRO.&nbsp;<br>&nbsp;SCOP&nbsp;</th>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera" colspan="3">&nbsp;DESPACHO&nbsp;</th>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera" rowspan="2"></th>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera" rowspan="2">&nbsp;</th>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera" rowspan="2">&nbsp;</th>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr><tr><th class="grid_cabecera">&nbsp;FECHA&nbsp;</th><th class="grid_cabecera" colspan="2">&nbsp;VEHICULO&nbsp;</th></tr>'));
		$can=count($res);
		for ($i = 0; $i < $can; $i++) {	
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
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td id=resultado0'.$i.' align="right" class="'.$color.'">&nbsp;' . htmlentities($res[$i][8]) . '</td>'));
			if($res[$i][9]==''){
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'"><A href="control.php?rqst=MOVIMIENTOS.PROGRAMRUTA&action=Actualizar&codpedido='.$res[$i][0].'&fecregistro='.$res[$i][1].'&ruc='.$res[$i][2].'&razsocial='.$res[$i][3].'&anexo='.$res[$i][4].'&distrito='.$res[$i][5].'&galones='.$res[$i][6].'&scop='.$res[$i][7].'" target="control">Asignar</A></td>'));	
			}else{
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'"><A href="control.php?rqst=MOVIMIENTOS.PROGRAMRUTA&action=Actualizar&codpedido='.$res[$i][0].'&fecregistro='.$res[$i][1].'&ruc='.$res[$i][2].'&razsocial='.$res[$i][3].'&anexo='.$res[$i][4].'&distrito='.$res[$i][5].'&galones='.$res[$i][6].'&scop='.$res[$i][7].'&placa='.$res[$i][9].'" target="control">&nbsp;' . htmlentities($res[$i][9]) . '</A></td>'));											
			}
			/*//if($res[$i][9]=='' || $res[$i][9]==NULL){
			//	$placa='<A href="control.php?rqst=MOVIMIENTOS.PROGRAMRUTA&action=Actualizar&codpedido='.$res[$i][0].'&ruc='.$res[$i][2].'&codanexo='.$res[$i][4].'" target="control">Asignar</A></td>';
			}/*else{
				if($res[$i][6]!=0){
					$placa='<a style=color:#0431B4 href=# id=resultado1'.$i.' onClick=cambiarDisplay("row'.($i).'",'.$can.')>'.$res[$i][9].'</a>';}
				else{
					$placa=$res[$i][9];
				}
					
			}
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">&nbsp;' . $placa . '</td>'));		
			
			*/
			if($res[$i][12]==0 and $res[$i][9]=='2000-01-01 00:00:00'){ // no tiene numero de factura o fecha de despacho -> modifica
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="'.$color.'"><A href="control.php?rqst=MOVIMIENTOS.PROGRAMRUTA&action=Actuli&codpedido='.$res[$i][0].'&ruc='.$res[$i][2].'&codanexo='.$res[$i][4].'" target="control"><img alt="Editar" title="Editar" src="/sistemaweb/icons/anular.gif" align="middle" border="0"/></A></td>'));						
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="'.$color.'"><A href="javascript:confirmarLink(\'Desea eliminar la matricula?\',\'control.php?rqst=MOVIMIENTOS.PROGRAMRUTA&action=Eliminar&codpedido='.$res[$i][0].'&ruc='.$res[$i][2].'&codanexo='.$res[$i][4].'&desde='.$desde.'&hasta='.$hasta.'&lisruc='.$ruc.'&lispedido='.$pedido.'\', \'control\')"><img src="./icons/delete.gif" alt="Eliminar" align="middle" border="0"/></A></td>'));			
			} else { 
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="'.$color.'">&nbsp;</td>'));					
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="'.$color.'">&nbsp;</td>'));
			}

			//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));
			
			/*$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr id=row'.$i.' style=display:none><td colspan=8></td><td colspan=2>		
				
				<table border=1>

				<tr>
				<td>
				Fecha Despacho:
				</td>
				<td>
				
				<input type=text id=fhd'.$i.' name=fhd'.$i.' value='.$res[$i][8].'>'));
				
				//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_listado.fhd$i'".');"><img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));			
					
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>
				<input type=hidden id=idplaca'.$i.' name=idplaca'.$i.' value='.$res[$i][10].'></td>
				</tr>
				<tr>
				<td>Vehiculo:</td>
				<td>'));
				
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type=text id=placa'.$i.' name=placa'.$i.' value="'.htmlentities($res[$i][9]).'" readonly=readonly>'));			

				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags(' <img src="../images/help.gif" width="16" height="15" onMouseOver="this.style.cursor=\'hand\'" onclick="javascript:mostrarPlaca(\'placa.php\',\'form_listado.placa'.$i.'\',\'form_listado.idplaca'.$i.'\',\'form_listado.dirfiscal'.$i.'\',\'placas\',\'\',\'<?php echo $valor;?>\');"> '));
				
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td align=center colspan=2>'));				
					
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type=hidden name=codpedido value='.$res[$i][0].'>'));
			//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type=hidden name=ruc'.$i.' value='.$res[$i][2].'>'));				
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type=hidden name=ruc value='.$res[$i][2].'>'));				

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<A href=# onclick=cargarContenido("'.$res[$i][0].'","'.$res[$i][2].'",fhd'.$i.'.value,placa'.$i.'.value,idplaca'.$i.'.value,"'.$res[$i][6].'",'.$i.')><img src="../images/actua.png"></A> <div id=resultado2'.$i.'></div>'));
						
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table><br>'));*/
		
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));
							
		}

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</table></center>'));
		
		return $form->getForm();
    	}

	function FormAsignar($codpedido,$fecregistro,$ruc,$razsocial,$anexo,$distrito,$galones,$scop,$placa){
		
		$form = new form2('', 'form_asignar', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MOVIMIENTOS.PROGRAMRUTA'));
		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="3" cellpadding="2" cellspacing="2" bordercolor = "#30767F">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>&nbsp;Codigo de Pedido:&nbsp;</td><td>'));		
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'codpedido', $codpedido, "", 4, 4,'',array('readonly')));					

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><tr><td>Fecha de Registro:</td><td>'));		
		$form->addElement(FORM_GROUP_MAIN, new form_element_text ('','fecregistro', $fecregistro, '', 18, 18, '', array('readonly')));  
		  
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>Ruc:</td><td>'));		
		$form->addElement(FORM_GROUP_MAIN, new form_element_text ('','ruc', $ruc, '', 15, 13, '', array('readonly')));  

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>Razon Social:</td><td>'));	
		$form->addElement(FORM_GROUP_MAIN, new form_element_text ('','razsocial', $razsocial, '', 40, 60, '', array('readonly')));  
		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><tr><td>Direccion Fiscal:</td><td>'));		
		$form->addElement(FORM_GROUP_MAIN, new form_element_text ('','anexo',$anexo, '', 58, 60, '', array('readonly')));
		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><tr><td>Codigo Lugar Anexo:</td><td>'));		
		$form->addElement(FORM_GROUP_MAIN, new form_element_text ('','distrito',$distrito, '', 13, 10, '', array('readonly')));
  
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><tr><td>Galones:</td><td>'));		
		$form->addElement(FORM_GROUP_MAIN, new form_element_text ('','galones',$galones, '', 15, 15, '', array('readonly')));  

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>Nro. Scop:</td><td>'));		
		$form->addElement(FORM_GROUP_MAIN, new form_element_text ('','scop',$scop, '', 15, 15,'',array('readonly')));

		//$fecha = substr($fecregistro,0,);
		//$fecregistro = date("Y-m-d");

		$anio		= substr($fecregistro,0,4);
		$mes		= substr($fecregistro,5,2);
		$dia		= substr($fecregistro,8,2);

		$fechita = $dia.'/'.$mes.'/'.$anio;

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>Fecha Registro:</td><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text('fecha','',$fechita, '', 15, 15, '', array('readonly')));  
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_asignar.fecha'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div style="position:relative"><div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div></div>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>Placa:</td><td>'));		
		$form->addElement(FORM_GROUP_MAIN, new form_element_text ('','placa',$placa, '', 15, 15,'', array('readonly')));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<img src="../images/help.gif" width="16" height="15" onMouseOver="this.style.cursor=\'hand\'" onclick="javascript:mostrarPlaca(\'placa.php\',\'form_asignar.placa\',\'form_asignar.idplaca\',\'form_asignar.idplaca\',\'placas\',\'\',\'<?php echo $valor;?>\');"> '));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type=hidden id=idplaca name=idplaca value='.$res[$i][10].'></td>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Modificar">Modificar</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Cancelar" onclick="javascript:regresar()" >Regresar</button>'));		

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));

		return $form->getForm();
	}
	
}
