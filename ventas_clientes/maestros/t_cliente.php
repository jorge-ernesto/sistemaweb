<?php

class ClienteTemplate extends Template {
 
	function titulo() {
		$titulo = '<div align="center"><h2>Clientes</h2></div><hr>';
		return $titulo;
	}

	function errorResultado($errormsg) {
		return '<blink>'.$errormsg.'</blink>';
	}

	function listado($registros) {
		global $usuario;

		$titulo_grid = isset($titulo_grid) ? $titulo_grid : '';
		//$columnas = array('COD. CLIENTE','RAZON SOCIAL','RAZON SOCIAL BREVE', 'DIRECCION', 'R.U.C.','TIPO','TELEFONO','MONEDA','COD. TARJETA MAG.');
		$columnas = array('COD. CLIENTE','RAZON SOCIAL','RAZON SOCIAL BREVE', 'R.U.C.', 'DIRECCION','TELEFONO','COD. TARJETA MAG.', 'T. CLIENTE');

		$listado = '<div id="resultados_grid" class="grid" align="center" ><br>
					<table bgcolor="white"><caption class="grid_title">'.$titulo_grid.'</caption>
                	      			<thead align="center" valign="center" >
							<tr class="grid_header" bgcolor="white">';

		for($i = 0; $i < count($columnas); $i++)
			$listado .= '<th class="grid_cabecera"> '.strtoupper(trim($columnas[$i])).'</th>';

		$a = 0;
		foreach($registros as $reg) {

			$listado .= '<tr class="grid_row" '.resaltar('#CFD8B4','#F0F5DD').'>';
			$regCod = trim($reg["cli_codigo"]);
			
			$color = ($a%2==0?"grid_detalle_par":"grid_detalle_impar");
			$a++;

			for ($i = 0; $i < count($columnas); $i++)
			    $listado .= '<td class="' . $color . '" style="font-size:0.6em;">'.$reg[$i].'</td>';

			$listado .= '<td class="' . $color . '"><A href="control.php?rqst=MAESTROS.CLIENTE&task=CLIENTE&action=Modificar&registroid='.$regCod.'" target="control"><img src="/sistemaweb/icons/gedit.png" title="Editar" align="middle" border="0"/></A>&nbsp;';
			$listado .= '<td class="' . $color . '"><A href="javascript:confirmarLink(\'Deseas eliminar cliente: ' . $regCod . ' - ' .$reg['cli_razsocial'].'?\',\'control.php?rqst=MAESTROS.CLIENTE&task=CLIENTE&action=Eliminar&registroid='.$regCod.'\', \'control\')"><img src="/sistemaweb/icons/gdelete.png" title="Eliminar" align="middle" border="0"/></A></td>';

			$listado .= '</tr>';

    		}

		$listado .= '</tbody></table></div>';

		return $listado;

	}

	function formBuscar($paginacion, $datos) {

		$type_client = array("0"=>"Todos", "S"=>"Anticipo", "1"=>"Credito", "2"=>"Efectivo", "3"=>"Venta Adelantada");

		$form = new form2('', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control');

		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MAESTROS.CLIENTE'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'CLIENTE'));

		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rxp', @$_REQUEST['rxp']));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('pagina', @$_REQUEST['pagina']));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('chequeo', @$_REQUEST['chequeo']));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center><table border ="0"><tr><td align="right">Buscar por: <td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('busqueda[codigo]','', @$datos['codigo'], espacios(2), 20, 18, array("onkeyup"=>"javascript:this.value=this.value.toUpperCase();")));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="right">Tipo Cliente: <td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("busqueda[tcliente]", "", @$datos['tcliente'], $type_client, ""));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="right">Ordernar por: <td>'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<input type="radio" name="busqueda[tarmag]" value="D">Tarjeta Magnetica</td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="2" align="center"><br>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Buscar"><img src="/sistemaweb/icons/gbuscar.png" align="right" />Buscar</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;&nbsp;<button name="action" type="submit" value="Agregar"><img src="/sistemaweb/icons/gadd.png" align="right" />Agregar</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;&nbsp;<button name="action" type="submit" value="Reporte"><img src="/sistemaweb/images/icono_pdf.gif" align="right" />PDF</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;&nbsp;<button name="action" type="submit" value="Excel"><img src="/sistemaweb/images/excel_icon.png" alt="left" /> Excel</button>'));
		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;&nbsp;<button name="action" type="submit" value="Total"><img src="/sistemaweb/images/excel_icon.png" alt="left" /> Total</button>'));
		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="2" align="center">'));
		    
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags(espacios(20)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</center>'));		    
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));

		$form->addGroup("GRUPO_PAGINA", "Paginacion");
 
		if ($paginacion['paginas'] == 'P'){
			$paginacion['paginas'] = '0';
		}

 		$form->addElement("GRUPO_PAGINA", new f2element_freeTags('P&aacute;gina '.$paginacion['paginas'].' de '.$paginacion['numero_paginas'].' P&aacute;ginas&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'));
		$form->addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/first.gif', espacios(2),array( "border"=>"0", "alt"=>"Primera P&aacute;gina","onclick"=>"javascript:PaginarRegistrosCli('".$paginacion['pp']."','".$paginacion['primera_pagina']."')")));
	   	$form->addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/left.gif', espacios(5),array( "border"=>"0", "alt"=>"P&aacute;gina Anterior","onclick"=>"javascript:PaginarRegistrosCli('".$paginacion['pp']."','".$paginacion['pagina_previa']."')")));
	    	$form->addElement("GRUPO_PAGINA", new f2element_text ('paginas','', $paginacion['paginas'], espacios(5), 3, 2, array( "onChange"=>"javascript:PaginarRegistrosCli('".$paginacion['pp']."',this.value)")));
	    	$form->addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/right.gif', espacios(2),array( "border"=>"0", "alt"=>"P&aacute;gina Siguente","onclick"=>"javascript:PaginarRegistrosCli('".$paginacion['pp']."','".$paginacion['pagina_siguiente']."')")));
	    	$form->addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/last.gif', espacios(2),array( "border"=>"0", "alt"=>"&Uacute;ltima P&aacute;gina","onclick"=>"javascript:PaginarRegistrosCli('".$paginacion['pp']."','".$paginacion['ultima_pagina']."')")));
		$form->addElement("GRUPO_PAGINA", new f2element_freeTags('Registros por P&aacute;gina  : '));
		$form->addElement("GRUPO_PAGINA", new f2element_text ('numero_registros','Registros por P&aacute;gina : ', $paginacion['pp'], espacios(2), 4, 4,array("onChange"=>"javascript:PaginarRegistrosCli(this.value,'".$paginacion['primera_pagina']."')")));

		/*$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('P&aacute;gina '.$paginacion['paginas'].' de '.$paginacion['numero_paginas'].' P&aacute;ginas&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'));		    
		$form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/first.gif', espacios(2),array( "border"=>"0", "title"=>"Primera P&aacute;gina","onclick"=>"javascript:PaginarRegistrosCliPost('".$paginacion['pp']."','".$paginacion['primera_pagina']."','".@$datos['elemento']."')")));
		$form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/left.gif', espacios(5),array( "border"=>"0", "title"=>"P&aacute;gina Anterior","onclick"=>"javascript:PaginarRegistrosCliPost('".$paginacion['pp']."','".$paginacion['pagina_previa']."','".@$datos['elemento']."')")));		    
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('paginas','', $paginacion['paginas'], espacios(5), 3, 2, array( "onChange"=>"javascript:PaginarRegistrosCli('".$paginacion['pp']."',this.value,'".@$_REQUEST['chequeo']."')")));
		$form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/right.gif', espacios(2),array( "border"=>"0", "title"=>"P&aacute;gina Siguente","onclick"=>"javascript:PaginarRegistrosCliPost('".$paginacion['pp']."','".$paginacion['pagina_siguiente']."','".@$datos['elemento']."')")));
		$form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/last.gif', espacios(2),array( "border"=>"0", "title"=>"&Uacute;ltima P&aacute;gina","onclick"=>"javascript:PaginarRegistrosCliPost('".$paginacion['pp']."','".$paginacion['ultima_pagina']."','".@$datos['elemento']."')")));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('numero_registros','Registros por P&aacute;gina : ', $paginacion['pp'], espacios(2), 4, 4,array("onChange"=>"javascript:PaginarRegistrosCliPost(this.value,'".$paginacion['primera_pagina']."','".@$datos['elemento']."')")));
		$form->addElement(FORM_GROUP_MAIN, new f2element_checkbox('busqueda[elemento]','Completo',@$datos['elemento'],espacios(0)));*/

		return $form->getForm();
	}

  
	function formCliente($datos,$registrosXml) {

		$CbSiNo = array('N'=>'No',
				'S'=>'Si');

		$CbSiNo2 = array(0=>'No',
				 1=>'Si');
    
		/**
		* OPENSOFT-XX: Venta adelantada
			- TIPOS DE CLIENTES:
		 		ANTICIPO:                     cli_ndespacho_efectivo = '0' / cli_anticipo = 'S'
		 		CREDITO:                      cli_ndespacho_efectivo = '0' / cli_anticipo = 'N'
		 		NOTA DE DESPACHO EN EFECTIVO: cli_ndespacho_efectivo = '1' / cli_anticipo = 'N'
		 		VENTA ADELANTADA:             cli_ndespacho_efectivo = '1' / cli_anticipo = 'S'
		*/
		$CbTipoCliente = array(
				 0=>'Anticipo',
				 1=>'Credito',
				 2=>'Nota de despacho en efectivo',
				 3=>'Venta adelantada');

		//Cargar nuevo combo 'Tipo Cliente' con el tipo de cliente
		if ( isset($datos['cli_ndespacho_efectivo']) && isset($datos['cli_anticipo']) ) {

			if ( $datos['cli_ndespacho_efectivo'] == '0' && $datos['cli_anticipo'] == 'S' ) { //Anticipo
				$datos['tcliente'] = 0;
			} elseif ( $datos['cli_ndespacho_efectivo'] == '0' && $datos['cli_anticipo'] == 'N' ) { //Credito
				$datos['tcliente'] = 1;
			} elseif ( $datos['cli_ndespacho_efectivo'] == '1' && $datos['cli_anticipo'] == 'N' ) { //Nota de despacho en efectivo
				$datos['tcliente'] = 2;
			} elseif ( $datos['cli_ndespacho_efectivo'] == '1' && $datos['cli_anticipo'] == 'S' ) { //Venta adelantada
				$datos['tcliente'] = 3;
			}

		}

		$Money = array(	'01'=>'S/. - Nuevos Soles',
				'02'=>'US$ - Dolares Americanos');
    
		if($datos["cli_codigo"]) {
			$params = "disabled";
			$val    = "true";
		} else {
			$params = "enabled";
			$val    = "true";
		}

		$CbListaPrecio 	= ClienteModel::ListaPreciosCBArray("tab_elemento ~ '".$datos["cli_lista_precio"]."'");
		$CbDistrito 	= ClienteModel::DistritoCBArray("tab_elemento ~ '".$datos["cli_distrito"]."'");
		$CbCategoria 	= ClienteModel::CategoriasCBArray();
		$DescDescuento 	= ClienteModel::DescuentosCBArray("substring(tab.tab_elemento for 2 from length(tab_elemento)-1) ~ '".trim($datos["cli_descuento"])."'");

		$form = new form2('CLIENTES', 'form_cliente', FORM_METHOD_POST, 'control.php', '', 'control','onSubmit = "return validar_form_clientes();"');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MAESTROS.CLIENTE'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'CLIENTE'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('registroid', trim(@$datos["cli_codigo"])));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('paginacion[codigo]', trim(@$datos["cli_codigo"])));
    
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="2" cellpadding="2"> <tr><td class="form_td_title">'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[cli_codigo]','* C&oacute;digo de Cliente </td><td>: ', @$datos["cli_codigo"], '', 14, 12, array("onKeyUp"=>"javascript:this.value=this.value.toUpperCase();checkCodigo(this);setNumerosLetras('datos[cli_codigo]');", "class"=>"form_input_numeric", "$params" => "$val")));    
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;<div id="MensajeValidacion"></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td valign="top">'));
		
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[cli_ruc]','* R.U.C. </td><td>: ', @trim($datos["cli_ruc"]), '', 20, 15, array("OnChange"=>"javascript:checkRuc(this);")));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<span class="btn_span sunat_span" id="btn_span" title="Consulta RUC SUNAT"></span>&nbsp;&nbsp;<div id="MensajeValidacionRuc"></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[cli_razsocial]','* Raz&oacute;n Social'.espacios(20).'</td><td>: ', @$datos["cli_razsocial"], '', 105, 100, array("onkeyup"=>"javascript:this.value=this.value.toUpperCase();")));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td valign="top">'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[cli_rsocialbreve]','* Raz&oacute;n Social Breve</td><td>: ', @$datos["cli_rsocialbreve"], '', 25, 20));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td valign="top">'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[cli_contacto]','Contacto </td><td>: ', @$datos["cli_contacto"], '', 60, 20));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td valign="top">'));

		//$form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[cli_ruc]','* R.U.C. </td><td>: ', @$datos["cli_ruc"], '', 12, 11, array("OnChange"=>"javascript:checkRuc(this);","class"=>"form_input_numeric","onKeyPress"=>"return validar(event,2);")));
		


		if (!$_SESSION['autorizacion']) {
			$ardes = array('disabled');
		}

		$form->addElement(FORM_GROUP_MAIN, new f2element_combo('datos[cli_tipo]','Tipo </td><td>: ', ($_REQUEST['action']=='Agregar'?'AC':trim(@$datos["cli_tipo"])), $CbCategoria, espacios(3),array(), ''));  // antes era:  espacios(3),array(),$ardes
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[cli_direccion]','* Direcci&oacute;n </td><td>: ', @$datos["cli_direccion"], '', 102, 100));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td valign="top">'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[cli_comp_direccion]','Complemento Direcci&oacute;n </td><td>: ', @$datos["cli_comp_direccion"], '', 25, 20));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td valign="top">'));
		    
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[cli_distrito]','* Distrito </td><td>: ', @trim($datos["cli_distrito"]), '', 7, 6, array("onKeyUp"=>"this.value=this.value.toUpperCase();getRegistroDist(this.value)", "class"=>"form_input_numeric")));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags(''.espacios(2).'<div id="desc_distrito" style="display:inline;">'.$CbDistrito[$datos["cli_distrito"]].'</div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
		    
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[cli_email]','Direcci&oacute;n E-Mail</td><td>: ', @$datos["cli_email"], '', 32, 30));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td valign="top">'));
		    
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[cli_telefono1]','Tel&eacute;fono </td><td>: ', @$datos["cli_telefono1"], '', 12, 11, array("class"=>"form_input_numeric","onKeyPress"=>"return validar(event,5);")));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
		    
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[cli_telefono2]','Fax </td><td>: ', @$datos["cli_telefono2"], '', 12, 11, array("class"=>"form_input_numeric","onKeyPress"=>"return validar(event,5);")));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_text('datos[cli_grupo]','* C&oacute;digo Tarjeta Mag.</td><td>: ', @trim($datos["cli_grupo"]), '', 3, 3, array("onKeyUp"=>"javascript:checkCodigoShell(this)", "class"=>"form_input_numeric", "onKeyPress"=>"return validar(event,2);", "onblur"=>"return cceros(3);"), ''));
//		$form->addElement(FORM_GROUP_MAIN, new f2element_text("datos[documento]", "", "", "", 7, 7,array("onblur"=>"return cceros(7);"), ''));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;<div id="MensajeValidacionShell"></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[cli_fpago_credito]','* Forma de Pago Credito</td><td>: ', @trim($datos["cli_fpago_credito"]), '', 3, 3, array("onKeyUp"=>"getRegistroFP(this.value)", "class"=>"form_input_numeric","onKeyPress"=>"return validar(event,2);")));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags(''.espacios(2).'<div id="desc_forma_pago" style="display:inline;">'.@$datos["desc_forma_pago"].'</div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_combo('datos[cli_moneda]','Moneda </td><td>: ', trim(@$datos["cli_moneda"]), $Money, espacios(3)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[cli_lista_precio]','* Lista de Precios</td><td>: ', @trim($datos["cli_lista_precio"]), '', 7, 6, array("onKeyUp"=>"getRegistroLPRE(this.value)", "class"=>"form_input_numeric","onKeyPress"=>"return validar(event,2);")));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags(''.espacios(2).'<div id="desc_lista_precios" style="display:inline;">'.$CbListaPrecio[$datos["cli_lista_precio"]].'</div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[cli_descuento]','Descuento al Contado</td><td>: ', @trim($datos["cli_descuento"]), '', 7, 6, array("onKeyUp"=>"getRegistroDesc(this.value)", "class"=>"form_input_numeric","onKeyPress"=>"return validar(event,2);")));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags(''.espacios(2).'<div id="desc_descuento" style="display:inline;">'.$DescDescuento['Datos'][trim($datos["cli_descuento"])].'</div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[cli_creditosol]','L&iacute;mite de Cr&eacute;dito Soles</td><td>: ', @$datos["cli_creditosol"], '', 12, 11, array("class"=>"form_input_numeric","onKeyPress"=>"return validar(event,2);")));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
		    
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[cli_creditodol]','L&iacute;mite de Cr&eacute;dito Dolares</td><td>: ', @$datos["cli_creditodol"], '', 12, 11, array("class"=>"form_input_numeric","onKeyPress"=>"return validar(event,2);")));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[cli_mantenimiento]','Mantenimiento</td><td>: ', @$datos["cli_mantenimiento"], '', 12, 11, array("class"=>"form_input_numeric","onKeyPress"=>"return validar(event,3);")));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_combo('datos[tcliente]','Tipo Cliente </td><td>: ', trim(@$datos["tcliente"]), $CbTipoCliente, espacios(3)));
		// $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
		
		// $form->addElement(FORM_GROUP_MAIN, new f2element_combo('datos[cli_anticipo]','Anticipos </td><td>: ', trim(@$datos["cli_anticipo"]), $CbSiNo, espacios(3)));
		// $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));

		// $form->addElement(FORM_GROUP_MAIN, new f2element_combo('datos[cli_ndespacho_efectivo]','Nota de Despacho en Efectivo </td><td>: ', trim(@$datos["cli_ndespacho_efectivo"]), $CbSiNo2, espacios(3)));

		if (isset($datos[22])) {
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("</td></tr><tr><td><span class=form_label >Linea Disponible</span></td><td>: {$datos[22]}"));
		}
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
	 	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

		// Tipos de agente
		$sSelectedTipoAgenteN='';
		$sSelectedTipoAgenteR='';		
		if ( $datos['cli_estado']=='' ) {
			$sSelectedTipoAgenteN='selected';
			$sSelectedTipoAgenteR='';
		} else if ( $datos['cli_estado']=='R' ) {
			$sSelectedTipoAgenteN='';
			$sSelectedTipoAgenteR='selected';
		}

		$html_option = '<option value="N" '.$sSelectedTipoAgenteN.'>Ninguno</option>';
		$html_option .= '<option value="R" '.$sSelectedTipoAgenteR.'>Retencion</option>';
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left"><span class=form_label>Tipo agente</span></td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left">'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags(
				':' . espacios(2) .
				'<select id="datos[cbo-sTipoAgente]" name="datos[cbo-sTipoAgente]">
					' . $html_option . '
				</select>'
			));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));
		// ./ TIpos de agente

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table><br>'));

		$form->addGroup('buttons', '');
//		$form->addElement('buttons', new f2element_submit('action','Guardar', espacios(2)));
		$form->addElement('buttons', new f2element_freeTags('<button name="action" type="submit" value="Guardar"><img src="/sistemaweb/icons/gadd.png" align="right" />Guardar</button>'));
		$form->addElement('buttons', new f2element_freeTags('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button name="action" type="button" value="Regresar" onclick="volver_a_maestro_clientes()"><img src="/sistemaweb/icons/atra.gif" align="right" />Regresar</button>'));
//		$form->addElement('buttons', new f2element_button('action','Regresar', espacios(2),array('onClick'=>'return volver_a_maestro_clientes();')));
   
		return $form->getForm().'<div id="error_body" align="center"></div><hr>';
	}

	function addCuentasBancarias($lista) {

		if($lista != '') {
			$formulario .= '<table border="0">'."\n";

			foreach($lista as $llave => $valor) {
				$formulario .= '<tr valign="top"><td>'."\n".
                  				'</td></tr><tr valign="top"><td>'."\n".
						'<input type="text" name="cod_banco[]" value="'.$valor['codigo_banco'].'" disabled class="form_input_numeric" size="8">'.
						'</td><td>'."\n".
						'<input type="text" name="desc_cta[]" value="'.$valor['descrip_banco'].'" disabled class="form_input" size="25">'.
						'</td><td>'."\n".
						'<input type="text" name="nro_cuenta[]" value="'.$valor['nro_cuenta_bancaria'].'" disabled class="form_input_numeric" size="25">'.
						'</td><td>'."\n".
						'<input type="text" name="tipo_cuenta[]" value="'.$valor['tipo_cuenta_bancaria'].'" disabled class="form_input_numeric" size="8">'.
						'</td><td>'."\n".
						'<input type="text" name="desc_tipoctaban[]" value="'.$valor['descrip_tipo_cuenta_bancaria'].'" disabled class="form_input" size="25">'.
						'</td><td>'."\n".
						"<a href=\"javascript:EliminarCuenta(".$llave.",document.getElementsByName('datos[cli_codigo]')[0])\">_</a>"."\n".
						'</td>'."\n".
						'</tr>'."\n";
			}
			$formulario .= '</table>'."\n";

			return $formulario;
		}
    
  	}
  
	function setRegistros($codigo) {

		$RegistrosCB = ClienteModel::CiiuCBArray("tab_elemento ~ '".pg_escape_string($codigo)."'");
		$result = '<blink><span class="MsgError">Error..</span></blink>';

		if (count($RegistrosCB) == 1) {
			foreach($RegistrosCB as $codciiu => $descriciiu) {
				$result = $descriciiu." <script language=\"javascript\">top.setRegistro('".trim($codciiu)."');</script>";
			}
		}
		if (count($RegistrosCB) > 1) {
			$att_opt = array();
			foreach($RegistrosCB as $codcli => $descricli) {
				$att_opt[trim($codcli)] = array(" onclick"=>"getRegistro('".trim($codcli)."');");
			}
			$cb = new f2element_combo('cbDatosCiiu', '','', $RegistrosCB,'',array("size"=>"5"), array(), $att_opt);
			$result = '<div align="left" style="position: absolute; width:250px;  z-index:1000;">'.$cb->getTag().'</div>';
		}

		return $result;
	}

	function setRegistrosFormaPago($codigo) {

		$RegistrosCB = ClienteModel::FormaPagoCBArray("tab_elemento ~ '".pg_escape_string($codigo)."'");
		$result = '<blink><span class="MsgError">Error..</span></blink>';

		if (count($RegistrosCB) == 1) {
			foreach($RegistrosCB as $cod => $descri) {
				$result = $descri." <script language=\"javascript\">top.setRegistroFP('".$cod."');</script>";
			}
		}
		if (count($RegistrosCB) > 1) {
			$att_opt = array();
			foreach($RegistrosCB as $cod => $descri) {
				$att_opt[trim($cod)] = array(" onclick"=>"getRegistroFP('".$cod."');");
			}
			$cb = new f2element_combo('cbDatosFormaPago', '','', $RegistrosCB,'',array("size"=>"5"), array(), $att_opt);
			$result = '<div align="left" style="position: absolute; width:250px;  z-index:1000;">'.$cb->getTag().'</div>';
		}

		return $result;
	}

	function setRegistrosListaPrecios($codigo) {

		$RegistrosCB = ClienteModel::ListaPreciosCBArray("tab_elemento ~ '".pg_escape_string($codigo)."'");
		$result = '<blink><span class="MsgError">Error..</span></blink>';

		if (count($RegistrosCB) == 1) {
			foreach($RegistrosCB as $cod => $descri) {
				$result = $descri." <script language=\"javascript\">top.setRegistroLPRE('".$cod."');</script>";
			}
		}
		if (count($RegistrosCB) > 1) {
			$att_opt = array();
			foreach($RegistrosCB as $cod => $descri) {
				$att_opt[trim($cod)] = array(" onclick"=>"getRegistroLPRE('".$cod."');");
			}
			$cb = new f2element_combo('cbDatosListaPrecios', '','', $RegistrosCB,'',array("size"=>"5"), array(), $att_opt);
			$result = '<div align="left" style="position: absolute; width:250px;  z-index:1000;">'.$cb->getTag().'</div>';
		}

		return $result;
	}

	function setRegistrosDistrito($codigo) {

		$RegistrosCB = ClienteModel::DistritoCBArray("trim(tab_elemento)||''||trim(tab_descripcion) ~ '".pg_escape_string($codigo)."'");
		$result = '<blink><span class="MsgError">Error..</span></blink>';

		if (count($RegistrosCB) == 1) {
			foreach($RegistrosCB as $codciiu => $descriciiu) {
				$result = $descriciiu." <script language=\"javascript\">top.setRegistroDist('".trim($codciiu)."');</script>";
			}
		}
		if (count($RegistrosCB) > 1) {
			$att_opt = array();
			foreach($RegistrosCB as $codcli => $descricli) {
				$att_opt[trim($codcli)] = array(" onclick"=>"getRegistroDist('".trim($codcli)."');");
			}
			$cb = new f2element_combo('cbDatosDistrito', '','', $RegistrosCB,'',array("size"=>"5"), array(), $att_opt);
			$result = '<div align="left" style="position: absolute; width:250px;  z-index:1000;">'.$cb->getTag().'</div>';
		}

		return $result;
	}

	function setRegistrosRubro($codigo) {

		$RegistrosCB = ClienteModel::RubrosCBArray("trim(tab_elemento)||''||trim(tab_descripcion) ~ '".pg_escape_string($codigo)."'");
		$result = '<blink><span class="MsgError">Error..</span></blink>';

		if (count($RegistrosCB) == 1) {
			foreach($RegistrosCB as $cod => $descri) {
				$result = $descri." <script language=\"javascript\">top.setRegistroRub('".trim($cod)."');</script>";
			}
		}
		if (count($RegistrosCB) > 1) {
			$att_opt = array();
			foreach($RegistrosCB as $cod => $descri) {
				$att_opt[trim($cod)] = array(" onclick"=>"getRegistroRub('".trim($cod)."');");
			}
			$cb = new f2element_combo('cbDatosRubro', '','', $RegistrosCB,'',array("size"=>"5"), array(), $att_opt);
			$result = '<div align="left" style="position: absolute; width:250px;  z-index:1000;">'.$cb->getTag().'</div>';
		}

		return $result;
	}

	function setRegistrosCuentas($codigo) {

		$RegistrosCB = ClienteModel::CuentasCBArray("trim(tab_elemento)||''||trim(tab_descripcion) ~ '".pg_escape_string($codigo)."'");
		$result = '<blink><span class="MsgError">Error..</span></blink>';

		if (count($RegistrosCB) == 1) {
			foreach($RegistrosCB as $cod => $descri) {
				$result = " <script language=\"javascript\">top.setRegistroCodCta('".trim($cod)."','".$descri."');</script>";
			}
		}
		if (count($RegistrosCB) > 1) {
			$att_opt = array();
			foreach($RegistrosCB as $cod => $descri) {
				$att_opt[trim($cod)] = array(" onclick"=>"getRegistroCodCta('".trim($cod)."');");
			}
			$cb = new f2element_combo('cbDatosCtas', '','', $RegistrosCB,'',array("size"=>"5"), array(), $att_opt);
			$result = '<div align="left" style="position: absolute; width:250px;  z-index:1000;">'.$cb->getTag().'</div>';
		}

		return $result;
	}
  
	function setRegistrosTipoCtaBan($codigo) {

		$RegistrosCB = ClienteModel::TipoCtaBanCBArray("trim(tab_elemento)||''||trim(tab_descripcion) ~ '".pg_escape_string($codigo)."'");
		$result = '<blink><span class="MsgError">Error..</span></blink>';

		if (count($RegistrosCB) == 1) {
			foreach($RegistrosCB as $cod => $descri) {
			$result = " <script language=\"javascript\">top.setRegistroTipoCtaBan('".trim($cod)."','".$descri."');</script>";
			}
		}
		if (count($RegistrosCB) > 1) {
			$att_opt = array();
			foreach($RegistrosCB as $cod => $descri) {
				$att_opt[trim($cod)] = array(" onclick"=>"getRegistroTipoCtaBan('".trim($cod)."');");
			}
			$cb = new f2element_combo('cbDatosTipoCtas', '','', $RegistrosCB,'',array("size"=>"5"), array(), $att_opt);
			$result = '<div align="left" style="position: absolute; width:250px;  z-index:1000;">'.$cb->getTag().'</div>';
		}

		return $result;
	}
  
	function setRegistrosDesc($codigo){

		$RegistrosCB = ClienteModel::DescuentosCBArray("substring(tab.tab_elemento for 2 from length(tab_elemento)-1) ~ '".pg_escape_string($codigo)."'");
		$result = '<blink><span class="MsgError">Error..</span></blink>';

		if (count($RegistrosCB['Datos']) == 1) {
			foreach($RegistrosCB['Datos'] as $cod => $descri) {
				$result = $descri." <script language=\"javascript\">top.setRegistroDesc('".trim($cod)."', '".$RegistrosCB['Desc'][trim($cod)]."');</script>";
			}
		}
		if (count($RegistrosCB['Datos']) > 1) {
			$att_opt = array();
			foreach($RegistrosCB['Datos'] as $cod => $descri) {
				$att_opt[trim($cod)] = array("onclick"=>"getRegistroDesc('".trim($cod)."');");
			}
			$cb = new f2element_combo('cbDatosDesc', '','', $RegistrosCB['Datos'],'',array("size"=>"5"), array(), $att_opt);
			$result = '<div align="left" style="position: absolute; width:250px;  z-index:1000;">'.$cb->getTag().'</div>';
		}

		return $result;
	}

	function gridViewEXCEL($arrResponseClientes) {
		$chrFileName = "";

		$workbook = new Workbook($chrFileName);
		$formato0 =& $workbook->add_format();
		$formato2 =& $workbook->add_format();
		$formato5 =& $workbook->add_format();

		//titulo
		$formato0->set_size(11);
		$formato0->set_bold(1);
		$formato0->set_align('center');

		//Sub titulo
		$formato2->set_size(10);
		$formato2->set_bold(1);
		$formato2->set_align('center');
		$formato2->set_bottom(1);
		$formato2->set_top(1);
		$formato2->set_right(1);
		$formato2->set_left(1);

		//Data
		$formato5->set_size(10);
		$formato5->set_align('left');

		$worksheet1 =& $workbook->add_worksheet('Clientes');

		$worksheet1->set_column(0, 0, 20);//COD. CLIENTE
		$worksheet1->set_column(1, 1, 80);//RAZON SOCIAL
		$worksheet1->set_column(2, 2, 40);//RAZON SOCIAL BREVE
		$worksheet1->set_column(3, 3, 15);//R.U.C.
		$worksheet1->set_column(4, 4, 80);//DIRECCION
		$worksheet1->set_column(5, 5, 15);//TELEFONO
		$worksheet1->set_column(6, 6, 20);//COD. TARJETA MAG.
		$worksheet1->set_column(7, 7, 15);//T. CLIENTE

		$worksheet1->set_zoom(100);
		$worksheet1->set_landscape(100);
		
		$worksheet1->write_string(1, 0, "MAESTRO DE CLIENTES",$formato0);

		$fila = 3;
		$worksheet1->write_string($fila, 0, "COD. CLIENTE",$formato2);
		$worksheet1->write_string($fila, 1, "RAZON SOCIAL",$formato2);
		$worksheet1->write_string($fila, 2, "RAZON SOCIAL BREVE",$formato2);
		$worksheet1->write_string($fila, 3, "R.U.C.",$formato2);
		$worksheet1->write_string($fila, 4, "DIRECCION",$formato2);
		$worksheet1->write_string($fila, 5, "TELEFONO",$formato2);
		$worksheet1->write_string($fila, 6, "COD. TARJETA MAG.",$formato2);
		$worksheet1->write_string($fila, 7, "T. CLIENTE",$formato2);

		$fila = 4;
		for ($i=0; $i<count($arrResponseClientes); $i++){
			$worksheet1->write_string($fila, 0, $arrResponseClientes[$i]['cli_codigo'],$formato5);
			$worksheet1->write_string($fila, 1, $arrResponseClientes[$i]['cli_razsocial'],$formato5);
			$worksheet1->write_string($fila, 2, $arrResponseClientes[$i]['cli_rsocialbreve'],$formato5);
			$worksheet1->write_string($fila, 3, trim($arrResponseClientes[$i]['cli_ruc']),$formato5);
			$worksheet1->write_string($fila, 4, $arrResponseClientes[$i]['cli_direccion'],$formato5);
			$worksheet1->write_string($fila, 5, $arrResponseClientes[$i]['cli_telefono1'],$formato5);
			$worksheet1->write_string($fila, 6, $arrResponseClientes[$i]['cli_grupo'],$formato5);
			$worksheet1->write_string($fila, 7, $arrResponseClientes[$i]['tipo_cliente'],$formato5);
			$fila++;
		}

		$workbook->close();	
		$chrFileName = "clientes";
		header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename = " . $chrFileName . ".xls");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
		header("Pragma: public");
	}
}
