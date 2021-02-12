<?php

class TargpromocionTemplate extends Template {
	function titulo() {
	//echo " titulo de TargpromocionTemplate \n"; 
		$titulo = '<div align="center"><h2>TARJETAS PROMOCION </h2></div><hr>';
		return $titulo;
	}

	function errorResultado($errormsg) {
		return '<blink>'.$errormsg.'</blink>';
	}

	function listado($registros) {
		$contador =0;
		$titulo_grid = "LISTADO DE TARJETAS PROMOCION";
		$columnas = array('Nro. CUENTA','NOMBRE CUENTA','PUNTOS CUENTA','DNI','Nro. TARJETA','NOMBRE TARJETA','PLACA','PUNTOS TARJETA','FECHA CREACION CUENTA','SUCURSAL','USUARIO');
		$listado = '<div id="resultados_grid" class="grid" align="center"><br>
				<table>
				<caption class="grid_title">'.$titulo_grid.'</caption>
				<thead align="center" valign="center" >
				<tr class="grid_header">';

//		$listado .= '<button name="fm" value="" onClick="javascript:parent.location.href=\'/sistemaweb/maestros/reporteMaestros/ClienteTarjeta_' . session_id() . '.csv\';return false"><img src="/sistemaweb/images/excel_icon.png" alt="left"/> EXCEL</button><br><br>';
		//$listado .= '<button name="fm" value="" onClick="javascript:parent.location.href=\'/sistemaweb/maestros/reporteMaestros/ClienteTarjeta_.csv\';return false"><img src="/sistemaweb/images/excel_icon.png" alt="left"/> EXCEL</button><br><br>';
		
		for($i = 0; $i < count($columnas); $i++) { 
			$listado .= '<td class="grid_cabecera" height="20"> '.strtoupper($columnas[$i]).'&nbsp;</td>';
		}

		$listado .= '<th>'.espacios(10).'</th><th>'.espacios(5).'</th></tr><tbody class="grid_body" style="height:250px;">';

		foreach($registros as $reg) {
			$color = ($contador%2==0?"grid_detalle_par":"grid_detalle_impar");

			$listado .= '<tr >';
			$listado .= '<td class="'.$color.'"><a alt="Mostrar Cuenta" title="Mostrar Cuenta"  href="control.php?rqst=PROMOCIONES.TARGPROMOCION&task=TARGPROMOCION&action=ModificarCuenta&cuentaid='.$reg["id_cuenta"].'" target="control" alt= "Ver Datos de Cuenta">'.$reg["nu_cuenta_numero"].'&nbsp;</a></td>';
			$listado .= '<td class="'.$color.'">'.$reg["nombre_cuenta"].'&nbsp;</td>';
			$listado .= '<td class="'.$color.'">'.$reg["nu_cuenta_puntos"].'&nbsp;</td>';
			$listado .= '<td class="'.$color.'">'.$reg["ch_cuenta_dni"].'&nbsp;</td>';
			$listado .= '<td class="'.$color.'">'.$reg["nu_tarjeta_numero"].'&nbsp;</td>';
			$listado .= '<td class="'.$color.'">'.$reg["ch_tarjeta_descripcion"].'&nbsp;</td>';
			$listado .= '<td class="'.$color.'">'.$reg["ch_tarjeta_placa"].'&nbsp;</td>';
			$listado .= '<td class="'.$color.'">'.$reg["nu_tarjeta_puntos"].'&nbsp;</td>';
			$listado .= '<td class="'.$color.'">'.$reg["dt_fecha_creacion"].'&nbsp;</td>';
			$listado .= '<td class="'.$color.'">'.$reg["ch_sucursal"].'&nbsp;</td>';
			$listado .= '<td class="'.$color.'">'.$reg["ch_usuario"].'&nbsp;</td>';

			if($reg["id_tarjeta"]!='') {
				$listado .= '<td class="'.$color.'"> <A   href="control.php?rqst=PROMOCIONES.TARGPROMOCION&task=TARGPROMOCION&action=ModificarTarjeta&tarjetaid='.$reg["id_tarjeta"].'&cuentaid='.$reg["id_cuenta"].'&numcuenta='.$reg["nu_cuenta_numero"].'&numtarjeta='. $reg["nu_tarjeta_numero"].'&desctarjeta='.$reg["ch_tarjeta_descripcion"].'&placatarjeta='.$reg["ch_tarjeta_placa"].'&titulartarjetaSINO='.$reg['ch_tarjeta_titular'].'&fechacre='.$reg['dt_tarjeta_creacion'].'&fechaven='.$reg['dt_tarjeta_vencimiento'].'" target="control"><img alt="Editar Tarjeta" title="Editar Tarjeta" src="/sistemaweb/icons/kedit32x32.png" align="middle" border="0"/></A>&nbsp;</td>';
			} else {
				$listado .= '<td class="'.$color.'"><img src="/sistemaweb/icons/kedit32x32disabled.png" border="0" alt="No existe Tarjeta para editar" title="No existe Tarjeta para editar"></td>';
			}

			if($reg["nu_tarjeta_numero"]=='') {
				$listado .= '<td class="'.$color.'" ><a href="javascript:confirmarLink(\'�Desea eliminar la cuenta N� '.
				$reg['nu_cuenta_numero'].'?\',\'control.php?rqst=PROMOCIONES.TARGPROMOCION&task=TARGPROMOCION&action=EliminarCuenta&cuentaid='.
				$reg["id_cuenta"].'\',\'control\')"><img alt="Eliminar Cuenta" title="Eliminar Cuenta" src="/sistemaweb/icons/delete22x22.png" align="middle" border="0"/></a></td>';
			} else {
				$listado .= '<td class="'.$color.'" ><a><img alt="No se puede Eliminar Cuenta" title="No se puede Eliminar Cuenta"  src="/sistemaweb/icons/delete22x22disabled.png" align="middle" border="0"/></a></td>';
			}

			$listado .= '</tr>';
			$contador++;
		}
		$listado .= '</tbody></table></div>';
		return $listado;
	}

	function formBuscar($paginacion,$almacenes) {
		$Tipo = array(	'D'=>'Dni',
				'T' => 'Tarjeta',
				'C' => 'Cuenta',
				'P' => 'Persona o Empresa',
				'F' => 'Fecha Creacion Cuenta');

		$almacenes['TODOS'] = "Todos los Almacenes";

		$form = new form2('', 'Buscar', FORM_METHOD_GET, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'PROMOCIONES.TARGPROMOCION'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'TARGPROMOCION'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo('almacen','Almacenes : ','TODOS', $almacenes));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo('tipobusqueda','Buscar por : ',' ', $Tipo));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('busqueda','Busqueda  : ', ' ','', 40, 30,'',array()));	
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Buscar',espacios(3)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Nueva Cuenta',espacios(3)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Excel',espacios(3)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</center>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('P&aacute;gina '.$paginacion['paginas'].' de '.$paginacion['numero_paginas'].' P&aacute;ginas&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'));		
		$form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/first.gif', espacios(2),array( "border"=>"0", "alt"=>"Primera P&aacute;gina","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['primera_pagina']."')")));
		$form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/left.gif', espacios(5),array( "border"=>"0", "alt"=>"P&aacute;gina Anterior","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['pagina_previa']."')")));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('paginas','', $paginacion['paginas'], espacios(5), 3, 2, array( "onChange"=>"javascript:PaginarRegistros('".$paginacion['pp']."',this.value)")));
		$form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/right.gif', espacios(2),array( "border"=>"0", "alt"=>"P&aacute;gina Siguente","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['pagina_siguiente']."')")));
		$form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/last.gif', espacios(2),array( "border"=>"0", "alt"=>"&Uacute;ltima P&aacute;gina","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['ultima_pagina']."')")));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('numero_registros','Registros por P&aacute;gina : ', $paginacion['pp'], espacios(2), 4, 4,array("onChange"=>"javascript:PaginarRegistros(this.value,'".$paginacion['primera_pagina']."')")));

		return $form->getForm();
	}

	function formCuentapromocion($cuenta,$arrTarjetas,$arrTiposCuenta,$camposlectura) {
		echo ' entro a formTargpromocion';
		$Tipo = array('C' => 'Combustible', 'M' => 'Market');
		$SiNo = array('1' => 'SI','2' => 'NO');
		$vip = array('S' => 'SI','N' => 'NO');
		$estado = array(1 => 'Activo', 0 => 'Inactivo');

		$form = new form2('', 'form_cuentapromocion', FORM_METHOD_POST, 'control.php', '', 'control','onSubmit="return validar_registro_cuenta();"');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'PROMOCIONES.TARGPROMOCION'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'TARGPROMOCION'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('grupo', ''));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('auxilio', ''));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('idcuenta', @$cuenta["id_cuenta"]));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('fecServer', date('d/m/Y')));

		if($_REQUEST['action'] == 'ModificarCuenta') {
			$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('accion', 'actualizarcuenta'));
		} else {
			$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('accion', ''));
		}

		if($camposlectura =='0') {
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="0" cellpadding="0"> <tr><td >'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="2" cellpadding="2"> <tr><td colspan="2" align="center" class="form_cabecera">'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('DATOS DE CUENTA   </td></tr><tr><td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td class="form_texto">'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('cuentanumero','NUMERO CUENTA *  </td><td>: ', trim(@$cuenta["nu_cuenta_numero"]),'', 25, 15,'',array('onkeypress="return soloNumeros(event)"', 'onblur="document.getElementsByName(\'cuentadni\')[0].value = document.getElementsByName(\'cuentanumero\')[0].value"',   
				"onkeyup='javascript:copiar(this,".'"tarjetanumero"'.")'")));
			
			$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('trab[codigosuc]', $_SESSION['almacen']));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td class="form_texto">'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('cuentanombres','NOMBRES * </td><td>: ', trim(@$cuenta["ch_cuenta_nombres"]),'', 50, 50,'',array("onkeyup='javascript:copiar(this,".'"tarjetadescripcion"'.")'")));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));    
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td class="form_texto">'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('cuentaapellidos','APELLIDOS * </td><td>: ',trim(@$cuenta["ch_cuenta_apellidos"]),'', 25, 50,'',array('onKeyUp=validarTurno();')));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td valign="top">'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_combo('cuentavip','CLIENTE VIP</td><td>:&nbsp;&nbsp;',trim(@$cuenta["ch_cuenta_vip"]),$vip));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto">'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_combo('estadocuenta','ESTADO</td><td>:&nbsp;&nbsp;',trim(@$cuenta["isactive"]),$estado));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto">'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('cuentafechanacimiento','FECHA DE NACIMIENTO * </td><td class="form_texto">: ',trim(@$cuenta["dt_fecha_nacimiento"]),'', 10, 10,'',''));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_cuentapromocion.cuentafechanacimiento'".');"><img src="/sistemaweb/images/showcalendar.gif"  border=0></a></td></tr><tr><td class="form_texto">'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('cuentadni','DNI * </td><td>: ',trim(@$cuenta["ch_cuenta_dni"]),'', 25,15,'',array()));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td class="form_texto">'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('cuentaruc','RUC  </td><td>: ',trim(@$cuenta["ch_cuenta_ruc"]),'', 25, 15,'',array()));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td class="form_texto">'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('cuentadireccion','DIRECCION *  </td><td>: ', trim(@$cuenta["ch_cuenta_direccion"]),'', 50, 50));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td class="form_texto">'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('cuentatelefono1','TELEFONO * </td><td>: ', trim(@$cuenta["ch_cuenta_telefono1"]),'', 25, 20));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td class="form_texto">'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('cuentatelefono2','OTRO TELOFONO   </td><td>: ', trim(@$cuenta["ch_cuenta_telefono2"]),'', 25, 20));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td class="form_texto">'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('cuentaemail','E-MAIL   </td><td>: ', trim(@$cuenta["ch_cuenta_email"]),'', 50, 50));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td class="form_texto">'));

			if($_REQUEST['action'] == 'ModificarCuenta') {
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('PUNTOS </td><td>:&nbsp;&nbsp;'. trim(@$cuenta["nu_cuenta_puntos"]) ) );
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td class="form_texto">'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('VENCIMIENTO </td><td>:&nbsp;&nbsp;'.trim(@$cuenta["dt_fecha_vencimiento"])));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
			}

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td class="form_texto">'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('TIPO DE CUENTA   </td><td>:&nbsp;&nbsp;'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<select name="cuentatipo">'));

			foreach($arrTiposCuenta as $reg) {
				$seleccion = ($reg["id_tipo_cuenta"]==@$cuenta["id_tipo_cuenta"])?" selected ":"";
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<option'.$seleccion.' value="'.$reg["id_tipo_cuenta"].'">'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags($reg["ch_tipo_descripcion"].'</option>'));
			}

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</select >'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td class="form_texto">'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="2">'));

			if($camposlectura =='0') {
				$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Guardar Cuenta', espacios(15)));
			}

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td></td></tr>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="2"><HR></td></tr>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="2">'));
		} else {
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="0" cellpadding="0"> <tr><td >'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="2" cellpadding="2"> <tr><td colspan="2" align="center" class="form_cabecera">'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('DATOS DE CUENTA  </td></tr><tr><td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td width="20%" class="form_texto"> '));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('Nro. CUENTA  </td><td width="80%" class="form_texto">:&nbsp;<span class="form_valor_texto">'.trim(@$cuenta["nu_cuenta_numero"]).'</span></td></tr>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto"> NOMBRES  </td><td class="form_texto">:&nbsp;<span class="form_valor_texto">'.trim(@$cuenta["ch_cuenta_nombres"]).'</span></td></tr>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto"> APELLIDOS  </td><td class="form_texto">:&nbsp;<span class="form_valor_texto">'.trim(@$cuenta["ch_cuenta_apellidos"]).'</span></td></tr>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto"> DNI  </td><td class="form_texto">:&nbsp;<span class="form_valor_texto">'.trim(@$cuenta["ch_cuenta_dni"]).'</span></td></tr>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto"> RUC  </td><td class="form_texto">:&nbsp;<span class="form_valor_texto">'.trim(@$cuenta["ch_cuenta_ruc"]).'</span></td></tr>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto"> DIRECCION  </td><td class="form_texto">:&nbsp;<span class="form_valor_texto">'.trim(@$cuenta["ch_cuenta_direccion"]).'</span></td></tr>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto"> TELEFONO  </td><td class="form_texto">:&nbsp;<span class="form_valor_texto">'.trim(@$cuenta["ch_cuenta_telefono1"]).'</span></td></tr>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto"> OTRO TELEFONO  </td><td class="form_texto">:&nbsp;<span class="form_valor_texto">'.trim(@$cuenta["ch_cuenta_telefono2"]).'</span></td></tr>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto"> E-MAIL  </td><td class="form_texto">:&nbsp;<span class="form_valor_texto">'.trim(@$cuenta["ch_cuenta_email"]).'</span></td></tr>'));

			$descripcion = "";
			foreach($arrTiposCuenta as $reg) {
				if($reg["id_tipo_cuenta"] == @$cuenta["id_tipo_cuenta"]) {
					$descripcion = $reg["ch_tipo_descripcion"];
					break;
				}
			}

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto"> TIPO DE CUENTA  </td><td class="form_texto">:&nbsp;<span class="form_valor_texto">'.$descripcion.'</span></td></tr>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto"> PUNTOS  </td><td class="form_texto">:&nbsp;<span class="form_valor_texto">'.trim(@$cuenta["nu_cuenta_puntos"]).'</span></td></tr>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto"> VENCIMIENTO  </td><td class="form_texto">:&nbsp;<span class="form_valor_texto">'.trim(@$cuenta["dt_fecha_vencimiento"]).'</span></td></tr>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="2"><HR></td></tr>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="2">'));
		}

		$form->addElement(FORM_GROUP_MAIN,new f2element_freeTags('<table border="0">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>
									<td>NUMERO</td>
									<td>DESCRIPCION</td>
									<td>PLACA</td>
									<td>TITULAR?</td>
									<td colspan="2">FECHA VENCIMIENTO</td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="2"></td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td height="15" valign="top">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('tarjetanumero','', '','', 15, 15,'',array('onkeypress="return soloNumeros(event)"'))); 
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td  valign="top">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('tarjetadescripcion','', '','', 50, 50)); 
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td  valign="top">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('tarjetaplaca','','','', 15, 15)); 
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td valign="top">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo('tarjetatitular','','', $SiNo));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td valign="top">'));	
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('tarjetafechaven','','','', 13, 15,'',array('readonly'))); 
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td valign="top"><a href="javascript:show_calendar('."'form_cuentapromocion.tarjetafechaven'".');"><img src="/sistemaweb/images/showcalendar.gif"   border=0></a>&nbsp;&nbsp;'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden;  z-index:1000;"></div></td>'));		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td valign="top"></td>'));		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td></td></tr>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="6" >'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_button('button','Adicionar Tarjeta',espacios(2),array('onclick'=>'validar_registro_tarjeta();')));	
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));	

		if(count($arrTarjetas) > 0) {
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="6">'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table width="100%">'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td  class="grid_cabecera">NUMERO</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="grid_cabecera">DESCRIPCION</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="grid_cabecera">PLACA</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="grid_cabecera">TITULAR</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="grid_cabecera">FECHA CREACION</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="grid_cabecera">FECHA VENCIMIENTO</td></tr>'));
			$contador =0;

			foreach($arrTarjetas as $reg) {
				$color = ($contador%2 == 0?"grid_detalle_par":"grid_detalle_impar");

				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="'.$color.'">'.$reg['nu_tarjeta_numero'].'</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="'.$color.'">'.$reg['ch_tarjeta_descripcion'].'</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="'.$color.'">'.$reg['ch_tarjeta_placa'].'</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="'.$color.'">'.((trim($reg['ch_tarjeta_titular'])=='1')?'SI':'NO').'</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="'.$color.'">'.$reg['dt_tarjeta_creacion'].'</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="'.$color.'">'.$reg['dt_tarjeta_vencimiento'].'</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td><a href="javascript:confirmarLink(\'�Desea eliminar la tarjeta N� '.$reg['nu_tarjeta_numero'].'?\',\'control.php?rqst=PROMOCIONES.TARGPROMOCION&task=TARGPROMOCION'.'&action=EliminarTarjeta&cuentaid='.$reg['id_cuenta'].'&tarjetaid='.$reg['id_tarjeta'].'\', \'control\')"><img src="/sistemaweb/icons/delete16x16.png" border="0"></a></td></tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));
				$contador++;
			}
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</table>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));
		}
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td></td></tr>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</table>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr></table>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="3" align="center" height="30">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_button('button','Regresar', espacios(2),array('onclick'=>'regresar();')));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));

		return $form->getForm().'<div id="error_body" align="center"></div><hr>';
	}

	function formTarjetapromocion($cuenta,$tarjeta,$motivos) {
		$SiNo = array('1' => 'SI', '2' => 'NO');
		$form = new form2('', 'form_tarjetapromocion', FORM_METHOD_POST, 'control.php', '', 'control','onSubmit="return validar_registro_tarjeta(\'' . trim(@$tarjeta["nu_tarjeta_numero"]) . '\');"');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'PROMOCIONES.TARGPROMOCION'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'TARGPROMOCION'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('idtarjeta', @$tarjeta["id_tarjeta"]));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('accion', 'actualizartarjeta'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('fecServer', date('d/m/Y')));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="0" cellpadding="0"> <tr><td>'));	// Inicio Contenido TD 1
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="2" cellpadding="2"> <tr><td colspan="2" align="center" class="form_cabecera">'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('MODIFICAR TARJETA  </td></tr><tr><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td class="form_texto">'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('TITULAR DE CUENTA </td><td class="form_texto">:&nbsp;&nbsp;<span class="form_valor_texto">'. @$cuenta["ch_cuenta_nombres"]." ".@$cuenta["ch_cuenta_apellidos"]));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('idcuenta', @$cuenta["id_cuenta"]));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</span></td></tr><tr><td class="form_texto">'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('N&Uacute;MERO CUENTA </td><td class="form_texto">:&nbsp;&nbsp;<span class="form_valor_texto">'. @$cuenta["nu_cuenta_numero"]));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('idcuenta', @$cuenta["id_cuenta"]));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</span></td></tr>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="2">&nbsp;_______________________________________________________________________<br><br></td></tr><tr><td class="form_texto">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('tarjetanumero','N&Uacute;MERO TARJETA </td><td class="form_texto">: ', trim(@$tarjeta["nu_tarjeta_numero"]),'', 25, 15,'',array('onkeypress="return soloNumeros(event)"','onblur="verificaCambioTarjeta(\'' . trim(@$tarjeta["nu_tarjeta_numero"]) . '\')"')));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td class="form_texto">'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('tarjetadescripcion','DESCRIPCI&Oacute;N  </td><td class="form_texto">: ',trim(@$tarjeta["ch_tarjeta_descripcion"]),'', 50, 50,'',array()));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td class="form_texto">'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('tarjetaplaca','PLACA  </td><td class="form_texto">: ',trim(@$tarjeta["ch_tarjeta_placa"]),'', 25, 15,'',array()));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td class="form_texto">'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('FECHA CREACI&Oacute;N </td><td class="form_texto">:&nbsp;&nbsp;<span class="form_valor_texto">'. @$tarjeta["dt_tarjeta_creacion"]));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('auxfechacreacion', trim(@$tarjeta["dt_tarjeta_creacion"])));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</span></td></tr><tr><td class="form_texto">'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('tarjetafechaven','FECHA VENCIMIENTO  </td><td class="form_texto">: ', trim(@$tarjeta["dt_tarjeta_vencimiento"]),'', 25, 30,'',array('readonly')));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_tarjetapromocion.tarjetafechaven'".');"><img src="/sistemaweb/images/showcalendar.gif"  border=0></a>&nbsp;&nbsp;'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td class="form_texto">'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_combo('tarjetatitular','&iquest; TITULAR ? </td><td class="form_texto">: ', trim(@$tarjeta["ch_tarjeta_titular"]), $SiNo));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr id="filamotivocambio1" style="visibility:hidden"><td class="form_texto">'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_combo('motivoduplicada','MOTIVO DEL CAMBIO </td><td class="form_texto">: ',"", $motivos));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr id="filamotivocambio2" style="visibility:hidden"><td class="form_texto">'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('motivocambio','DETALLE</td><td class="form_texto">: ',"",'', 50, 50,'',array()));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td class="form_texto">'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));	// Fin Contenido TD 1
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr></table>'));	//Fin Contenido TD 2
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="3"><HR></td></tr>'));	
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="3" align="center" height="30">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Guardar Tarjeta', espacios(15)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_button('button','Regresar', espacios(2),array('onclick'=>'regresar();')));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));

		return $form->getForm().'<div id="error_body" align="center"></div><hr>';
	}

	function listadoTarjetas() {
		$listado ='<div ><table>';
		if(isset($_SESSION['arrtarjeta'])) {
			echo "en template, listando tarjeta... \n";
			$arrtarg = $_SESSION['arrtarjeta'];
			echo " tamanio tarjeta...".count($arrtarg)."\n";

			for ($i = 1; $i <= count ($arrtarg); $i++) {
				echo "entrando a for";
				echo $arrtarg[$i]['tarjetanumero']. "\n";
				$listado.='<tr><td>'.$arrtarg[$i]['tarjetanumero'].'</td></tr>';
			}
		}
		$listado .='</div>';
		return $listado;
	} 
}
