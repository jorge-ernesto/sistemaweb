<?php
class CuentasBancariasTemplate extends Template {

	function titulo() {
		return '<h2 align="center"><b>Cuentas Bancarias</b></h2>';
	}

	function formPag($paginacion) {

		$bancos = CuentasBancariasModel::ObtenerBancos();
		$bancos['TODOS'] = "Todos los Bancos";

		$form = new Form('', "Form", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "REPORTES.CUENTASBANCARIAS"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br/><table border="0" >'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan = "2" align="center">Bancos: '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("banco", "Bancos:", "TODOS", $bancos, espacios(3)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr><tr><td align="right">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Buscar"><img src="/sistemaweb/images/search.png" align="right" />Buscar</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td align="left">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Agregar"><img src="/sistemaweb/icons/agregar.gif" align="right" />Agregar</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="4" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp&nbsp&nbsp&nbsp&nbsp'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="4" align="center">'));

		$form->addGroup("GRUPO_PAGINA", "Paginacion");
	
		if ($paginacion['paginas'] == 'P'){
			$paginacion['paginas'] = '0';
		}

	 	$form->addElement("GRUPO_PAGINA", new f2element_freeTags('P&aacute;gina '.$paginacion['paginas'].' de '.$paginacion['numero_paginas'].' P&aacute;ginas&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'));
		$form->addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/first.gif', espacios(2),array( "border"=>"0", "alt"=>"Primera P&aacute;gina","onclick"=>"javascript:PaginarRegistrosFecha('".$paginacion['pp']."','".$paginacion['primera_pagina']."')")));
	   	$form->addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/left.gif', espacios(5),array( "border"=>"0", "alt"=>"P&aacute;gina Anterior","onclick"=>"javascript:PaginarRegistrosFecha('".$paginacion['pp']."','".$paginacion['pagina_previa']."')")));
	    	$form->addElement("GRUPO_PAGINA", new f2element_text ('paginas','', $paginacion['paginas'], espacios(5), 3, 2, array( "onChange"=>"javascript:PaginarRegistrosFecha('".$paginacion['pp']."',this.value)")));
	    	$form->addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/right.gif', espacios(2),array( "border"=>"0", "alt"=>"P&aacute;gina Siguente","onclick"=>"javascript:PaginarRegistrosFecha('".$paginacion['pp']."','".$paginacion['pagina_siguiente']."')")));
	    	$form->addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/last.gif', espacios(2),array( "border"=>"0", "alt"=>"&Uacute;ltima P&aacute;gina","onclick"=>"javascript:PaginarRegistrosFecha('".$paginacion['pp']."','".$paginacion['ultima_pagina']."')")));
		$form->addElement("GRUPO_PAGINA", new f2element_freeTags('Registros por P&aacute;gina  : '));
		$form->addElement("GRUPO_PAGINA", new f2element_text ('numero_registros','Registros por P&aacute;gina : ', $paginacion['pp'], espacios(2), 4, 4,array("onChange"=>"javascript:PaginarRegistrosFecha(this.value,'".$paginacion['primera_pagina']."')")));

		return $form->getForm();
    	}


	function formSearch($paginacion){

		$bancos = CuentasBancariasModel::ObtenerBancos();
		$bancos['TODOS'] = "Todos los Bancos";

		$form = new Form('', "Form", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "REPORTES.CUENTASBANCARIAS"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br/><table border="0" >'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan = "2" align="center">Bancos: '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("banco", "Bancos:", "", $bancos, espacios(3)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr><tr><td align="right">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Buscar"><img src="/sistemaweb/images/search.png" align="right" />Buscar</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td align="left">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Agregar"><img src="/sistemaweb/icons/agregar.gif" align="right" />Agregar</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="4" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp&nbsp&nbsp&nbsp&nbsp'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="4" align="center">'));

		//PAGINADOR

		$form->addGroup("GRUPO_PAGINA", "Paginacion");
 
		if ($paginacion['paginas'] == 'P'){
			$paginacion['paginas'] = '0';
		}

 		$form->addElement("GRUPO_PAGINA", new f2element_freeTags('P&aacute;gina '.$paginacion['paginas'].' de '.$paginacion['numero_paginas'].' P&aacute;ginas&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'));
		$form->addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/first.gif', espacios(2),array( "border"=>"0", "alt"=>"Primera P&aacute;gina","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['primera_pagina']."')")));
	   	$form->addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/left.gif', espacios(5),array( "border"=>"0", "alt"=>"P&aacute;gina Anterior","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['pagina_previa']."')")));
	    	$form->addElement("GRUPO_PAGINA", new f2element_text ('paginas','', $paginacion['paginas'], espacios(5), 3, 2, array( "onChange"=>"javascript:PaginarRegistros('".$paginacion['pp']."',this.value)")));
	    	$form->addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/right.gif', espacios(2),array( "border"=>"0", "alt"=>"P&aacute;gina Siguente","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['pagina_siguiente']."')")));
	    	$form->addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/last.gif', espacios(2),array( "border"=>"0", "alt"=>"&Uacute;ltima P&aacute;gina","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['ultima_pagina']."')")));
		$form->addElement("GRUPO_PAGINA", new f2element_freeTags('Registros por P&aacute;gina  : '));
		$form->addElement("GRUPO_PAGINA", new f2element_text ('numero_registros','Registros por P&aacute;gina : ', $paginacion['pp'], espacios(2), 4, 4,array("onChange"=>"javascript:PaginarRegistros(this.value,'".$paginacion['primera_pagina']."')")));


		return $form->getForm();
	}

	function formAgregar($fila) {

		$hoy = date("d/m/Y");

		$banco		= CuentasBancariasModel::ObtenerBancos();
		$currency	= CuentasBancariasModel::TipoMoneda();

		$form = new Form('',"Editar", FORM_METHOD_POST, "control.php", '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "REPORTES.CUENTASBANCARIAS"));

			if($_REQUEST['action'] == 'Modificar'){

				$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('accion', 'actualizar'));

			}
				$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<table>"));

				if($_REQUEST['action'] == 'Agregar'){

					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<tr><td colspan="2" style="text-align:center;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Banco:</td><td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new f2element_combo("banco", "Banco: ", "", $banco, '</td></tr><tr><td colspan="2" style="text-align:center;">'));
				   	$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("N&uacute;mero de Cuenta: </td><td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('','ncuenta', '','</td></tr><tr><td colspan="2" style="text-align:center;">', '', 20, 20, false,'onkeypress="return validar(event,2)"'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Moneda: </td><td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new f2element_combo("currency", "Moneda: ", "", $currency, '</td></tr><tr><td colspan="2" style="text-align:center;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Titular: </td><td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('','nombre', '','</td></tr><tr><td colspan="2" style="text-align:center;">', '', 30, 30, false,'onkeypress="return validar(event,1)"'));					
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Inciales del Titular: </td><td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('','ini', '','</td></tr><tr><td colspan="2" style="text-align:center;">', '', 10, 10, false,'onkeypress="return validar(event,1)"'));					
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<tr><td colspan = '2' align='center'>"));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Guardar"><img src="/sistemaweb/icons/agregar.gif" align="right" />Guardar</button>'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<td align='left'>"));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Regresar" onclick="regresar()"><img src="/sistemaweb/icons/atra.gif" align="right" />Regresar</button>'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr></table>"));

				}else{

					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<input type="hidden" name="idbanco" id="idbanco" value = "' . $fila['idbanco'] . '"/>'));

					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<tr>'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr><tr><td style='text-align:right;'> Banco: "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:left;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'banco', $fila['banco'], "", "",6,6,($_REQUEST['action']=='Modificar'?array('readonly'):array())));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<tr><td style='text-align:right;'> Numero de Cuenta: "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td><td> "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text("", "ncuenta", $fila['ncuenta'], "", "",20,20,($_REQUEST['action']=='Modificar'?array('readonly'):array())));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr><tr><td style='text-align:right;'> Moneda: "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:left;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'currency', $fila['currency'], "", "",12,12,($_REQUEST['action']=='Modificar'?array('readonly'):array())));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<tr><td style='text-align:right;'> Titular: "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td><td> "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text("", "name", $fila['name'], "", "",20,20,false,'onkeypress="return validar(event,1)"'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr><tr><td style='text-align:right;'> Iniciales del Titular: "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:left;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'ini', $fila['ini'], "", "",15,15,false,'onkeypress="return validar(event,1)"'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr><tr><td align='center'>"));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Actualizar"><img src="/sistemaweb/icons/update2.png" align="right" />Actualizar</button>'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<td align='left'>"));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Regresar" onclick="regresar()"><img src="/sistemaweb/icons/atra.gif" align="right" />Regresar</button>'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr></table>"));

				}

		return $form->getForm();
	}

	function resultadosBusqueda($resultados) {
		$result  = '';
		$result .= '<table align="center">';
		$result .= '<tr>';
		$result .= '<th class="grid_cabecera">BANCO</th>';
		$result .= '<th class="grid_cabecera">NRO. DE CUENTA</th>';
		$result .= '<th class="grid_cabecera">MONEDA</th>';
		$result .= '<th class="grid_cabecera">TITULAR</th>';
		$result .= '<th class="grid_cabecera">INI. TITULAR</th>';
		$result .= '<th colspan="2" class="grid_cabecera"></th>';
		$result .= '</tr>';

		for ($i = 0; $i < count($resultados); $i++) {

			$color = ($i%2==0?"grid_detalle_par":"grid_detalle_impar");
			$a = $resultados[$i];

			$result .= '<tr bgcolor="">';
			$result .= '<td class="'.$color.'" align ="center">' . htmlentities($a['banco']) . '</td>';
			$result .= '<td class="'.$color.'" align ="center">' . htmlentities($a['ncuenta']) . '</td>';
			$result .= '<td class="'.$color.'" align ="center">' . htmlentities($a['currency']) . '</td>';
			$result .= '<td class="'.$color.'" align ="center">' . htmlentities($a['nombre']) . '</td>';
			$result .= '<td class="'.$color.'" align ="center">' . htmlentities($a['ini']) . '</td>';
			$result .= '<td class="'.$color.'"><A href="control.php?rqst=REPORTES.CUENTASBANCARIAS&action=Modificar&ncuenta='.($a['ncuenta']).'&idbanco='.($a['idbanco']).'" target="control"><img src="/sistemaweb/icons/open.gif" align="middle" border="0"/></A>&nbsp;</td>';
			$result .= '<td class="'.$color.'"><A href="javascript:confirmarLink(\'Deseas eliminar el Nro. cuenta '. htmlentities($a['ncuenta']).' ?\',\'control.php?rqst=REPORTES.CUENTASBANCARIAS&action=Eliminar&ncuenta='.($a['ncuenta']).'&idbanco='.($a['idbanco']).'\', \'control\')"><img src="/sistemaweb/icons/delete.gif" alt="Borrar" align="middle" border="0"/></A>&nbsp;</td>';			
			$result .= '</tr>';

		}

		$result .= '</table>';
		return $result;
	}
}
