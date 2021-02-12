<?php

class FacturasTemplate extends Template {

    function titulo() {
        $titulo = '<div align="center"><h2>Facturaci&oacute;n</h2></div>';
        return $titulo;
    }

    function errorResultado($errormsg) {
        return '<blink style="color: red">' . $errormsg . '</blink>';
    }

    function formReporte() {
        $serie 			= @$_REQUEST['busqueda']['serie'];
        $fecha_inicio 	= @$_REQUEST['busqueda']['fecha_ini'];
        $fecha_final 	= @$_REQUEST['busqueda']['fecha_fin'];
        $fecha_cod_cli 	= @$_REQUEST['busqueda']['codigo'];
        $tipo_docu 		= @$_REQUEST['busqueda']['tipo'];
        $num_doc 		= @$_REQUEST['busqueda']['numero'];
        $fecha_ini 		= date("d/m/Y");
        $fecha_fin 		= date("d/m/Y");

        $form = new form2('', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control', 'onSubmit="return pasarfechas();"');
        $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'FACTURACION.FACTURAS'));
        $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'FACTURAS'));

        $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('busqueda[serie]', @$serie));
        $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('busqueda[fecha_ini]', @$fecha_inicio));
        $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('busqueda[fecha_fin]', @$fecha_final));

        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="2" cellpadding="3"> <tr><td class="form_label">'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_text('fecha_ini', 'Fecha de Inicio :', $_REQUEST['busqueda']['fecha_ini'] ? @$_REQUEST['busqueda']['fecha_ini'] : @$fecha_ini, '', 20, 18));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar(' . "'Buscar.fecha_ini'" . ');"><img src="/sistemaweb/images/showcalendar.gif"  border=0></a>&nbsp;&nbsp;'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div><br/>'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_text('fecha_fin', 'Fecha Fin :', $_REQUEST['busqueda']['fecha_fin'] ? @$_REQUEST['busqueda']['fecha_fin'] : @$fecha_fin, '', 20, 18));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar(' . "'Buscar.fecha_fin'" . ');"><img src="/sistemaweb/images/showcalendar.gif"  border=0></a>&nbsp;&nbsp;'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center>'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_text('busqueda[codigo]', 'Codigo de Cliente :', $_REQUEST['busqueda']['codigo'] ? @$_REQUEST['busqueda']['codigo'] : @$cod_cli, '', 20, 18));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_text('busqueda[serie]', 'Serie Documento :', $_REQUEST['busqueda']['serie'] ? @$_REQUEST['busqueda']['serie'] : @$serie, '', 20, 18));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_text('busqueda[tipo]', 'Tipo de Documento :', $_REQUEST['busqueda']['tipo'] ? @$_REQUEST['busqueda']['tipo'] : @$tipo_docu, '', 20, 18));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_text('busqueda[numero]', 'Numero Documento :', $_REQUEST['busqueda']['numero'] ? @$_REQUEST['busqueda']['numero'] : @$num_doc, '', 20, 18));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_submit('action', 'Generar Reporte', ''));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;&nbsp;'));
        if ($_REQUEST['action'] == 'Generar Reporte')
            $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="generar_xls.php?codigo=' . $_REQUEST['busqueda']['codigo'] . '&fecha_ini=' . $_REQUEST['busqueda']['fecha_ini'] . '&fecha_fin=' . $_REQUEST['busqueda']['fecha_fin'] . '&serie=' . $_REQUEST['busqueda']['serie'] . '&tipo=' . $_REQUEST['busqueda']['tipo'] . '&numero=' . $_REQUEST['busqueda']['numero'] . '">Excel</a>'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</center>'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></table>'));

        return $form->getForm();
    }

	function listado($registros, $desde, $hasta, $codigo, $tipo_busqueda, $turno) {
		$Money = array('01' => 'Soles', '02' => 'Dolares');

		$columnas = array('T. DOC', 'S. DOC', 'N&#186; DOC.', 'COD. CLIENTE', 'RAZ&Oacute;N SOCIAL', 'FECHA', 'P.V.', 'MON.', 'T.C.', 'VAL. BRUTO', 'IMPUES.', 'DSCTO.', 'VAL. TOTAL', 'CRED.', 'ANUL.', 'ANTIC.', 'N&#186; LIQ.');

		$listado = '<div id="error_body" align="center"></div>';
		$listado .= '
			<div id="resultados_grid" class="grid" align="center">
				<table style = "border:0.5px">
					<caption class="grid_title">' . $titulo_grid . '</caption>
						<thead align="center" valign="center">
					    	<tr class="grid_header">
		';

		for ($i = 0; $i < count($columnas); $i++)
		    $listado .= '<th style = "border:0.5px solid #959C98; font-size:0.8em; color:black;" class="grid_columtitle" bgcolor=#F4FA58>&nbsp;' . $columnas[$i] . '&nbsp;</th>';

		$listado .= '<th style = "border:0.5px solid #959C98; font-size:0.8em; color:black;"  class="grid_columtitle" bgcolor=#F4FA58>ESTADO</th>';
		$listado .= '<th style = "border:0.5px solid #959C98; font-size:0.8em; color:black;"  class="grid_columtitle" bgcolor=#F4FA58 colspan = "6" bgcolor=#F4FA58>OPCIONES</th>';

		$count = 0;
		foreach ($registros as $reg) {
			$reg[7]				= $Money[trim($reg[7])];
	    	$regCod 			= trim($reg["codigo"]);
	    	$alinear[9]			= 'align="right"';
	    	$alinear[10]		= 'align="right"';
	    	$alinear[11]		= 'align="right"';

	    	$listado .= '<tr height="10px;" class="grid_row" '.resaltar('#EDFF59','#FAFEDF').'>';

	    	for ($i = 0; $i < count($columnas); $i++) {
				if ($i == 7 || $i == 9 || $i == 10 || $i == 11)
					$listado .= '<td class="grid_item"' . @$alinear[$i] . '>&nbsp;' . $reg[$i] . '&nbsp;</td>';
				else
					$listado .= '<td class="grid_item" align="center">&nbsp;' . $reg[$i] . '&nbsp;</td>';
	    	}

			$tipo 		= $reg['ch_fac_tipodocumento'];
			$serie 		= $reg['ch_fac_seriedocumento'];
			$numero 	= $reg['ch_fac_numerodocumento'];
			$cliente 	= trim($reg['cli_codigo']);
		
			$listado .= '<td style="font-size:0.9em; color:black;"><b>' . $reg['statusname'] . '</td>';

			if (ereg("[A-Z]+", $reg['nofe']) || $reg['status'] == 0)
				$listado .= '<td><a title="Modificar" class="document-'.$count.'-'.$reg['ch_fac_tipodocumento'].'-'.$serie.'-'.$numero.'-'.$cliente.'" href="control.php?rqst=FACTURACION.FACTURAS&task=FACTURAS&montorecargo=' . trim($reg[19]) . '&recargo=' . trim($reg[18]) .'' . '&busqueda[codigo]=' . $_REQUEST['busqueda']['codigo'] . '&busqueda[f_desde]=' . $_REQUEST['busqueda']['f_desde'] . '&busqueda[f_hasta]=' . $_REQUEST['busqueda']['f_hasta'] .'&action=Modificar&codigo=' . $_REQUEST['busqueda']['codigo'] . '&f_desde=' . $_REQUEST['busqueda']['f_desde'] . '&f_hasta=' . $_REQUEST['busqueda']['f_hasta'] . '&buscar_tipo=' . $_REQUEST['buscar_tipo'] . '&registroid=' .$regCod. '" target="control"><img src="/sistemaweb/icons/gedit.png" alt="Editar" align="middle" border="0"/></a>&nbsp;';

			if ( (empty($reg['nofe']) && empty($reg['ch_fac_anulado'])) || (ereg("[A-Z]+", $reg['nofe']) && ($reg['status'] == 0 || $reg['status'] == 1 || $reg['status'] == 3)) )
				$listado .= '<td><a title="Anular" class="document-'.$count.'-'.$reg['ch_fac_tipodocumento'].'-'.$serie.'-'.$numero.'-'.$cliente.'" href="javascript:confirmarLink(\'Desea Anular: ' . $reg[0] . ' - ' .$serie. ' - ' .$numero. '?\',\'control.php?rqst=FACTURACION.FACTURAS&task=FACTURAS&action=Anular&codigo=' . $_REQUEST['codigo'] . '&f_desde=' . $_REQUEST['f_desde'] . '&f_hasta=' . $_REQUEST['f_hasta'] . '&buscar_tipo=' . $_REQUEST['buscar_tipo'] . '&turno=' . $_REQUEST['turno'] . '&registroid=' . trim($reg[0]) . ' ' . trim($reg[1]) . ' ' . trim($reg[2]) . ' ' . trim($reg[3]) . '&_id=' .$regCod. '&dt_fac_fecha=' .$reg['dt_fac_fecha']. '&status=' . $reg['status'] . '&ch_fac_anulado=S&codalmacen=' . $reg['codalmacen'] . '&ch_fac_seriedocumento=' .$reg['ch_fac_seriedocumento']. '\', \'control\')"><img src="/sistemaweb/icons/anular.gif" alt="Anular" align="middle" border="0"/></a></td>';

			if ( (empty($reg['nofe']) && $reg['ch_fac_anulado'] != 'S') || (ereg("[A-Z]+", $reg['nofe']) && $reg['status'] == 0) )//$reg[12] = ch_fac_anulado
				$listado .= '<td><a title="Eliminar" class="document-'.$count.'-'.$reg['ch_fac_tipodocumento'].'-'.$serie.'-'.$numero.'-'.$cliente.'" href="javascript:confirmarLink(\'Desea eliminar: ' . $reg[0] . ' - ' .$serie. ' - ' .$numero. '?\',\'control.php?rqst=FACTURACION.FACTURAS&task=FACTURAS&action=Eliminar&codigo=' . $_REQUEST['codigo'] . '&f_desde=' . $_REQUEST['f_desde'] . '&f_hasta=' . $_REQUEST['f_hasta'] . '&buscar_tipo=' . $_REQUEST['buscar_tipo'] . '&turno=' . $_REQUEST['turno'] . '&registroid=' . trim($reg[0]) . ' ' . trim($reg[1]) . ' ' . trim($reg[2]) . ' ' . trim($reg[3]) . '\', \'control\')"><img src="/sistemaweb/icons/delete.gif" alt="Borrar" align="middle" border="0"/></a></td>';

			if ( (empty($reg['nofe']) && $reg['ch_fac_credito'] == 'S' && empty($reg['ch_fac_anulado'])) || (ereg("[A-Z]+", $reg['nofe']) && $reg['status'] == 0) )//$reg[12] = ch_fac_credito
				$listado .= '<td><a class="document-'.$count.'-'.$reg['ch_fac_tipodocumento'].'-'.$serie.'-'.$numero.'-'.$cliente.'" href="javascript:confirmarLink22(\'Desea pasar al contado el documento: ' . $reg[0] . ' - ' .$serie. ' - ' .$numero. '?\',\'control.php?rqst=FACTURACION.FACTURAS&task=FACTURAS&action=Contado&codigo=' . $_REQUEST['codigo'] . '&f_desde=' . $_REQUEST['f_desde'] . '&f_hasta=' . $_REQUEST['f_hasta'] . '&buscar_tipo=' . $_REQUEST['buscar_tipo'] . '&turno=' . $_REQUEST['turno'] . '&registroid=' .$regCod. '\', \'control\')"><img src="/sistemaweb/icons/actualizar.gif" alt="Contado" align="middle" border="0"/></a></td>';

			if (ereg("[A-Z]+", $reg['nofe']) && $reg['status'] == 0)
				//$listado .= '<td><a title="Enviar a SUNAT" class="document-'.$count.'-'.$reg['ch_fac_tipodocumento'].'-'.$serie.'-'.$numero.'-'.$cliente.'" href="javascript:confirmarLink22(\'Deseas enviar: ' . $reg[0] . ' - ' .$serie. ' - ' .$numero. '?\',\'control.php?rqst=FACTURACION.FACTURAS&task=FACTURAS&action=Complete&codigo=' . $_REQUEST['codigo'] . '&f_desde=' . $_REQUEST['f_desde'] . '&f_hasta=' . $_REQUEST['f_hasta'] . '&buscar_tipo=' . $_REQUEST['buscar_tipo'] . '&turno=' . $_REQUEST['turno'] . '&status=' . $reg['status'] . '&ch_fac_anulado=' . $reg['ch_fac_anulado'] . '&codalmacen=' . $reg['codalmacen'] . '&registroid=' .$regCod. '&dt_fac_fecha=' .$reg['dt_fac_fecha']. '&ch_fac_tipodocumento=' .$reg['ch_fac_tipodocumento']. '\', \'control\')"><img src="/sistemaweb/icons/completar.png" alt="Completar" align="center"/></a></td>';

			//Nuevo proceso de FE
			if (ereg("[A-Z]+", $reg['nofe']) && $reg['status'] == 0) {
				//$listado .= '<td>(<a title="Enviar" style="cursor: pointer; display: none;" class="document-'.$count.'-'.$reg['ch_fac_tipodocumento'].'-'.$serie.'-'.$numero.'-'.$cliente.'" onclick="enviarDocumentoSunat(\''.$count.'\', \''.$reg[0].'\', \''.$reg['ch_fac_tipodocumento'].'\', \''.$serie.'\', \''.$numero.'\',\''.$cliente.'\', \''.$regCod.'\', \''.$reg['ch_fac_anulado'].'\', \''.$reg['dt_fac_fecha'].'\', \''.$reg['codalmacen'].'\', \'\');"><img src="/sistemaweb/icons/completar.png" alt="Completar" align="center"/></a>)</td>';//kwn
				$listado .= '<td><a title="Enviar" style="cursor: pointer;" class="document-'.$count.'-'.$reg['ch_fac_tipodocumento'].'-'.$serie.'-'.$numero.'-'.$cliente.'" onclick="enviarDocumentoSunat(\''.$count.'\', \''.$reg[0].'\', \''.$reg['ch_fac_tipodocumento'].'\', \''.$serie.'\', \''.$numero.'\',\''.$cliente.'\', \''.$regCod.'\', \''.$reg['ch_fac_anulado'].'\', \''.$reg['dt_fac_fecha'].'\', \''.$reg['codalmacen'].'\', \'\');"><img src="/sistemaweb/icons/completar.png" alt="Completar" align="center"/></a></td>';//kwn
			}

			if (preg_match("/^[a-zA-Z][0-9]{3}+$/", $serie) == 1) {
				$listado .= '<td><a title="Representación impresa" style="cursor: pointer;"  onclick="_generarDocumentoLV(\''.$reg['ch_fac_tipodocumento'].'\',\''.$serie.'\',\''.$numero.'\',\''.$cliente.'\')"><img src="/sistemaweb/images/icono_imprimir.gif"/></a></td>';//kwn

				//$listado .= '<td><a title="Representación impresa" style="cursor: pointer;" class="document-'.$count.'-'.$reg['ch_fac_tipodocumento'].'-'.$serie.'-'.$numero.'-'.$cliente.'" onclick="documentoInterno(\''.$count.'\', \''.$reg[0].'\', \''.$reg['ch_fac_tipodocumento'].'\', \''.$serie.'\', \''.$numero.'\',\''.$cliente.'\', \''.$regCod.'\', \''.$reg['ch_fac_anulado'].'\', \''.$reg['dt_fac_fecha'].'\', \''.$reg['codalmacen'].'\', \'\');"><img src="/sistemaweb/images/icono_imprimir.gif" alt="pdf"/></a></td>';//kwn*/
			}

			$listado .= '</tr>';
			$count++;
		}
		$listado .= '</tbody></table></div>';
        return $listado;
	}

	function formBuscar() {
    	$tipos		= FacturasModel::obtenerTiposDocumento();

    	$f_desde	= date("d/m/Y");
    	$f_hasta	= date("d/m/Y");

        $turnos = FacturasModel::obtieneTurnos();
		$turnos[0] = "TODOS";

        $form = new form2('', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control');
        $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'FACTURACION.FACTURAS'));
        $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'FACTURAS'));

    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center>'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<table border="0">'));

		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<tr><td align="right">Fecha Inicio: </td><td>'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text("Fecha Inicio:", "f_desde", $f_desde, '', 10, 10));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar(\'Buscar.f_desde\');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td></tr><tr><td align="right">Fecha Final: </td><td>'));		
		$form->addElement(FORM_GROUP_MAIN, new form_element_text("", "f_hasta", $f_hasta, '', 10, 10));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar(\'Buscar.f_hasta\');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext('</td></tr>'));

		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<tr><td align="right">'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_text('codigo', 'Buscar por: <td>', '', espacios(2), 20, 18, array("onkeyup" => "javascript:this.value=this.value.toUpperCase();")));
        	
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<tr><td align="right">'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_combo('buscar_tipo', 'Tipo Documento: <td>', '0', $tipos, espacios(3)));

		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<tr><td align="right">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("turno", "Turno: <td>", '', $turnos, ""));

		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<tr><td align="center" colspan="2"><br>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Buscar"><img src="/sistemaweb/icons/gbuscar.png" align="right" />Buscar</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;&nbsp;<button name="action" id="agregar" type="submit" value="Agregar"><img src="/sistemaweb/icons/gadd.png" align="right" />Agregar</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;&nbsp;<button name="action" type="submit" value="excel"><img src="/sistemaweb/icons/gexcel.png" align="right" />Excel</button>'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<tr><td align="right"><br>'));
    	$form->addElement(FORM_GROUP_MAIN, new f2element_combo('forma_eliminar', 'Forma Eliminar: <td><br>', '0', array('0' => '[Escoger Opcion]', '1' => 'Sin Soltar Vales', '2' => 'Soltando Vales'), espacios(3)));
    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</center>'));
    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td><br>'));
    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</table>'));

       	return $form->getForm();
	}

	function Excel($listado){
		$workbook = new Workbook("Facturas.xls");
		$formato0 =& $workbook->add_format();
		$formato2 =& $workbook->add_format();
		$formato5 =& $workbook->add_format();

		$formato0->set_size(11);
		$formato0->set_bold(1);
		$formato0->set_align('left');
		$formato2->set_size(10);
		$formato2->set_bold(1);
		$formato2->set_align('center');
		$formato5->set_size(11);
		$formato5->set_align('left');

		$worksheet1 =& $workbook->add_worksheet('Documentos Manuales');
		$worksheet1->set_column(0, 0, 16);
		$worksheet1->set_column(1, 1, 10);
		$worksheet1->set_column(2, 2, 12);
		$worksheet1->set_column(3, 3, 12);
		$worksheet1->set_column(4, 4, 30);
		$worksheet1->set_column(5, 5, 30);
		$worksheet1->set_column(6, 6, 16);
		$worksheet1->set_column(10, 10, 16);

		$worksheet1->set_zoom(100);
		$worksheet1->set_landscape(100);

		$worksheet1->write_string(1, 0, "DOCUMENTOS DE VENTA",$formato0);

		$a = 4;
		$worksheet1->write_string($a, 0, "TIPO",$formato2);
		$worksheet1->write_string($a, 1, "SERIE",$formato2);
		$worksheet1->write_string($a, 2, "NUMERO",$formato2);
		$worksheet1->write_string($a, 3, "FECHA",$formato2); 
		$worksheet1->write_string($a, 4, "CLIENTE",$formato2);
		$worksheet1->write_string($a, 5, "PRODUCTO",$formato2);
		$worksheet1->write_string($a, 6, "CANTIDAD",$formato2);
		$worksheet1->write_string($a, 7, "VALOR V",$formato2);
		$worksheet1->write_string($a, 8, "IMPUESTO",$formato2);
		$worksheet1->write_string($a, 9, "TOTAL",$formato2);
		$worksheet1->write_string($a, 10, "ESTADO",$formato2);

		$a = 5;
		$tipo=array(
			"10"=>"FACTURA",
			"35"=>"BOLETA",
			"20"=>"NOTA DE CREDITO",
			"11"=>"NOTA DE DEBITO",
			"15"=>"RECIBOS SERV. PUBLICO",
			"71"=>"NRO LOTE DAEWOO",
			"72"=>"SISTEMA DE PAGO/TARJ DE CREDITO",
			"21"=>"ANTICIPO",
			"22"=>"RESUMEN DE ANTICIPO"
		);

        for ($j=0; $j<count($listado['datos']); $j++) {
            $worksheet1->write_string($a, 0,$tipo[$listado['datos'][$j][0]],$formato5);
            $worksheet1->write_string($a, 1, $listado['datos'][$j]['ch_fac_seriedocumento'],$formato5);
            $worksheet1->write_string($a, 2, $listado['datos'][$j]['ch_fac_numerodocumento'],$formato5);  
            $worksheet1->write_string($a, 3, $listado['datos'][$j]['dt_fac_fecha'],$formato5);  
            $worksheet1->write_string($a, 4, $listado['datos'][$j]['cli_codigo']."-".$listado['datos'][$j]['cli_rsocialbreve'],$formato5);  
            $worksheet1->write_string($a, 5, $listado['datos'][$j]['art_codigo'],$formato5);  
            $worksheet1->write_string($a, 6, $listado['datos'][$j]['nu_fac_cantidad'],$formato5);  
            $worksheet1->write_string($a, 7, $listado['datos'][$j]['nu_fac_importeneto'],$formato5);  
            $worksheet1->write_string($a, 8, $listado['datos'][$j]['nu_fac_impuesto1'],$formato5);  
            $worksheet1->write_string($a, 9, $listado['datos'][$j]['nu_fac_valortotal'],$formato5);
            $ch_fac_anulado=($listado['datos'][$j]['ch_fac_anulado']=='S')?"ANULADO":"";
            $worksheet1->write_string($a, 10, $ch_fac_anulado,$formato5);
            $a++;
        }
        $workbook->close(); 
        header('Location: Facturas.xls');
	}

	function formFacturas($datos, $DatosArrayArticulos, $desde, $hasta) {

    	$dt_fac_fecha		= date("d/m/Y");
    	$fecha_replicacion	= date("d/m/Y");

		if ($_REQUEST['action'] == 'Modificar')
			$deshabilitar = 'disabled';
		else
			$deshabilitar = '';

		$CbSiNo = array('N' => 'No', 'S' => 'Si');

       	if (empty($datos['dt_fac_fecha']) && !$datos["registroid"]) {
			$fecha_tp = date('Y-m-d');
    		$fechaDiv = explode('-', $fecha_tp);
    		$datos['dt_fac_fecha'] = $fechaDiv[2] . "/" . $fechaDiv[1] . "/" . $fechaDiv[0];
			$datos['fecha_replicacion'] = $fechaDiv[2] . "/" . $fechaDiv[1] . "/" . $fechaDiv[0];
        } else {
    		$fechaDiv 					= explode('-', $datos['dt_fac_fecha']);
    		$fechaDiv2					= explode('-', $datos['fecha_replicacion']);
    		$datos['dt_fac_fecha']		= $fechaDiv[2] . "/" . $fechaDiv[1] . "/" . $fechaDiv[0];
    		$datos['fecha_replicacion']	= $fechaDiv2[2] . "/" . $fechaDiv2[1] . "/" . $fechaDiv2[0];
    		$fecha_tp					= $fechaDiv[0] . "-" . $fechaDiv[1] . "-" . $fechaDiv[2];
    	}

		if (empty($datos['nu_tipocambio']))
		    $datos['nu_tipocambio'] = VariosModel::tipoCambioLibre(@$fecha_tp);

		if (empty($datos["ch_factipo_descuento1"]) || $datos["ch_factipo_descuento1"] <= 0)
		    $datos["ch_factipo_descuento1"] = '01';

		if (empty($datos["ch_fac_cd_impuesto1"]))
		    $datos["ch_fac_cd_impuesto1"] = VariosModel::ObtCodIgv();

		if (empty($datos["porce_fac_impuesto1"]))
		    $datos["porce_fac_impuesto1"] = 1 + VariosModel::ObtIgv();

		$CbListaAlmacenes	= FacturasModel::ListadosVarios("almacenes");
		$CbListaTipoDoc 	= FacturasModel::ListadosVarios("documentos_sunat");
		$CbListaMoneda 		= FacturasModel::ListadosVarios("monedas");
		$CbListaSeriesDoc 	= FacturasModel::ListadosVarios("series_documentos_sunat");

		if (!empty($datos["cli_codigo"]))
		    $DescCliente = FacturasModel::ClientesCBArray("trim(cli_codigo)='" . pg_escape_string(trim($datos["cli_codigo"])) . "'");

		if (!empty($datos["ch_fac_seriedocumento"]) && !empty($datos["ch_fac_tipodocumento"]))
		    $DescSerie = FacturasModel::TiposSeriesCBArray("doc.num_seriedocumento='" . pg_escape_string(trim($datos["ch_fac_seriedocumento"])) . "'", trim($datos["ch_fac_tipodocumento"]));

		if (!empty($datos["ch_fac_forma_pago"]) && !empty($datos["ch_fac_credito"]))
		    $DescFPago = FacturasModel::FormaPagoCBArray("substring(tab_elemento for 2 from length(tab_elemento)-1 ) = '" . trim($datos["ch_fac_forma_pago"]) . "'", trim($datos["ch_fac_credito"]));

		if (!empty($DatosArrayArticulos[1]["pre_lista_precio"]))
		    $DescLprecios = FacturasModel::ListaPreciosCBArray("tab_elemento ~ '" . trim($DatosArrayArticulos[1]["pre_lista_precio"]) . "'");

		if (!empty($datos["ch_factipo_descuento1"]))
		    $DescDescuento = FacturasModel::DescuentosCBArray("substring(tab.tab_elemento for 2 from length(tab_elemento)-1) ~ '" . trim($datos["ch_factipo_descuento1"]) . "'");

		$form = new form2('', 'form_facturas', FORM_METHOD_POST, 'control.php', '', 'control', ' onSubmit="return verificar_completo()"');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'FACTURACION.FACTURAS'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'FACTURAS'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('registroid', @$_REQUEST["registroid"]));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('datos[ch_fac_cd_impuesto1]', trim(@$datos["ch_fac_cd_impuesto1"])));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('porce_fac_impuesto1', trim(@$datos["porce_fac_impuesto1"])));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('porce_recargo', 0));

		//Estado para FE
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('datos[nu_fac_recargo3]', 0)); //1 = Completado

		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('referencia_fecha', ''));

		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('busqueda[codigo]', @$_REQUEST["busqueda"]["codigo"]));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('busqueda[f_desde]', @$_REQUEST["busqueda"]["f_desde"]));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('busqueda[f_hasta]', @$_REQUEST["busqueda"]["f_hasta"]));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('acumulado', ($DatosArrayArticulos ? count($DatosArrayArticulos) : 0)));

		$turnos = FacturasModel::obtieneTurnos();

		/* TIPO DE OPERACION AFECTACION */
		$taxoptional = FacturasModel::GetTaxOptional();
		$rowstaxoptional = array("N" => "No", "S" => "Si");

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="cont1" onload="messj()"><table border="0" cellspacing="2" cellpadding="2" align="center"> <tr><td valign="top"></div>'));

		/* Inicio de Datos del TD 1 */
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="3" cellpadding="3"> <tr><td class="form_label">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('N&uacute;mero</td><td class="form_label">:' . espacios(2) . '<b><div id="Numero" style="display:inline;">' . trim(@$datos["ch_fac_numerodocumento"]) . '</div></b></td></tr>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_label">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('Almacen</td><td class="form_label">:' . espacios(2) . '<b><div id="Almacen" style="display:inline;">' . trim(@$datos["ch_almacen"]) . '</div></b></td></tr><tr><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo('datos[ch_fac_tipodocumento]', 'Tipo Doc. </td><td>: ', trim(@$datos["ch_fac_tipodocumento"]), $CbListaTipoDoc, espacios(3), array("onChange" => "javascript:ClearSerieAlmacen(this.value);"), array($deshabilitar)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text('datos[ch_fac_seriedocumento]', 'Serie</td><td>: ', @$datos["ch_fac_seriedocumento"], '', 4, 4, array("onblur" => "MostrarFecha();","onfocus"=>"MostrarFecha();","onfocus" => "getValueCboTipoPago();", "onKeyUp" => "getRegistro(this.value=this.value.toUpperCase());javascript:this.value=this.value.toUpperCase();", "class" => "form_input_numeric", "onKeyPress" => "return validar(event,1);"), array($deshabilitar)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;<div id="desc_series_doc" style="display:inline;">' . $DescSerie['Datos'][trim($datos["ch_fac_seriedocumento"])] . '</div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text('dt_fac_fecha', 'Fecha' . espacios(2) . '</td><td>: ', @$datos["dt_fac_fecha"], '', 10, 12, array("onblur" => "MostrarFecha();","onfocus"=>"MostrarFecha();"), array($deshabilitar)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text('datos[cli_codigo]', 'C&oacute;digo de Cliente </td><td>: ', @$datos["cli_codigo"], '', 10, 11, array("onChange" => "actualizaTrabajador();","onKeyUp" => "javascript:this.value=this.value.toUpperCase();getRegistroCli(this.value)", "class" => "form_input_numeric bpartner_dm", "$params" => "$val"), array($deshabilitar)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;<div id="desc_cliente" style="display:inline;">' . @$DescCliente[trim($datos["cli_codigo"])] . '</div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text('fecha_replicacion', 'Fecha Sistema</td><td>: ', @$fecha_replicacion, '', 10, 12, array("onfocus"=>"hola();"), array($deshabilitar)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("datos[ch_fac_tiporecargo3]", "Turno: ", @$datos['ch_fac_tiporecargo3'], $turnos, ""));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

		/* SOLO PARA LAS EMPRESAS QUE SEAN EXONERADAS */
		if($taxoptional){
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_combo("datos[nuexonerado]", "<p style='font-size:1.2em; color:black;'><b>Exonerado </td><td>: ", @$datos['ch_fac_tiporecargo3'], $rowstaxoptional, espacios(3), array("onChange" => "OcultarMostrarFila(this.value);", "class" => "tiposmoneda"), array($deshabilitar)));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));
		}else{
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>'));
			$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('datos[nuexonerado]', 'NO'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));
		}

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</table>'));
		/* Fin de Datos del TD 1 */

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td valign="top">'));

		/* Inicio de Datos del TD 2 */
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="2" cellpadding="2"> <tr><td class="form_label">'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('datos[ch_fac_numerodocumento]', trim(@$datos["ch_fac_numerodocumento"])));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('datos[ch_almacen]', trim(@$datos["ch_almacen"])));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('datos[ch_punto_venta]', trim(@$datos['ch_punto_venta'])));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('c_dias_pago', trim($DescFPago['Dias'][trim($datos["ch_fac_forma_pago"])])));

		/* Obtener tipos de pago SUNAT */
		$arrFormaPagos = FacturasModel::getFormasPago();
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo('datos[nu_tipo_pago]', 'Tipo Pago</td><td>: ', trim(@$datos["nu_tipo_pago"]), $arrFormaPagos, espacios(3), array("onfocus" => "getFechaVencimiento();"), ''));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo('datos[ch_fac_credito]', 'Cr&eacute;dito</td><td>: ', trim(@$datos["ch_fac_credito"]), $CbSiNo, espacios(3), array("onChange" => "ClearCredito(); Mostrar_Liquidacion(this.value);"), ''));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('
		<tr>
			<td>
				<div class="cbo-Nu_Tipo_Pago">Dias Pago</div>
			</td>
			<td>
				<div class="cbo-Nu_Tipo_Pago">: 
					<input name="datos[ch_fac_forma_pago]" id="datos[ch_fac_forma_pago]" value="' . (trim(@$datos["ch_fac_forma_pago"]) == null ? '' : trim(@$datos["ch_fac_forma_pago"])) . '" autocomplete="off" onkeyup="getRegistroFP(this.value);" class="form_input_numeric" onkeypress="return validar(event,2);" size="3" maxlength="3" type="text">
				</div>
		'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('' . espacios(2) . '<div id="desc_forma_pago" style="display:inline;">' . trim($DescFPago['Datos'][trim($datos["ch_fac_forma_pago"])]) . '</div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));

		/* Fecha de Vencimiento y Tipo de Pago F.E */
		if($deshabilitar)
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type="hidden" id="Fe_Emision" name="Fe_Emision" maxlength="10" size="12" class="fecha_formato" value="'.@$datos["dt_fac_fecha"].'" />'));

		/* Solo se mostrará F. Vencimiento cuando el tipo de pago sea al Crédito */
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div class="cbo-Nu_Tipo_Pago">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text('fe_vencimiento', 'Fecha Vencimiento</td><td>: ', @$datos["fe_vencimiento"], '', 10, 12, array("onblur" => "hola();"), ''));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));

		//$form->addElement(FORM_GROUP_MAIN, new f2element_combo('datos[ch_fac_anticipo]" onchange="validarAnticipo(this)', 'Anticipado</td><td>: ', trim(@$datos["ch_fac_anticipo"]), $CbSiNo, espacios(3), array("style" => "display:inline"), array($deshabilitar)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo('datos[ch_fac_anticipo]', 'Anticipado</td><td>: ', trim(@$datos["ch_fac_anticipo"]), $CbSiNo,espacios(3), array("onChange" => "validarAnticipo();", "class" => "valor-anticipo", "style" => "display:inline"), array($deshabilitar)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text('articulos[pre_lista_precio]', 'L. de Precios</td><td>: ', (isset($articulos["pre_lista_precio"]) ? @trim($articulos["pre_lista_precio"]) : substr(trim($DatosArrayArticulos[1]["pre_lista_precio"]), 0, 2)), '', 3, 3, array("onKeyUp" => "getRegistroLPRE(this.value)", "class" => "form_input_numeric", "onKeyPress" => "return validar(event,2);"), array($deshabilitar)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('' . espacios(2) . '<div id="desc_lista_precios" style="display:inline;">' . @$DescLprecios[trim($DatosArrayArticulos[1]["pre_lista_precio"])] . '</div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo('datos[ch_fac_moneda]', 'Moneda</td><td>: ', trim(@$datos["ch_fac_moneda"]), $CbListaMoneda, espacios(3), array("onChange" => "limpiar_articulos(); VerTipoCambio(this.value);", "class" => "tiposmoneda"), array($deshabilitar)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_text('datos[nu_tipocambio]', 'T. Cambio</td><td>: ', @$datos['nu_tipocambio'], '', 5, 5, array("class" => "form_input_numeric", "class" => "valormoneda"), array($deshabilitar)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_checkbox('datos[ch_descargar_stock]', 'Descargar stock </td><td>: ', 'S', '', array(), (isset($datos['ch_descargar_stock']) ? array("checked", $deshabilitar) : array($deshabilitar))));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></td></tr>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td id="ch_fac_tiporecargo2">'));
      //$form->addElement(FORM_GROUP_MAIN, new f2element_checkbox('datos[ch_fac_tiporecargo2]', 'Transferencia Gratuita </td><td id="ch_fac_tiporecargo2A">: ', 'S', '', array(), (isset($datos['ch_fac_tiporecargo2']) ? array("checked", $deshabilitar) : array($deshabilitar))));
		$form->addElement(FORM_GROUP_MAIN, new f2element_checkbox('datos[ch_fac_tiporecargo2]', 'Transferencia Gratuita </td><td id="ch_fac_tiporecargo2A">: ', 'S', '', array(), ''));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></td></tr>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td id="ch_fac_cd_impuesto3">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_checkbox('datos[ch_fac_cd_impuesto3]', 'Despacho Perdido </td><td id="ch_fac_cd_impuesto3A">: ', 'S', '', array(), (isset($datos['ch_fac_cd_impuesto3']) ? array("checked", $deshabilitar) : array($deshabilitar))));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></td></tr></table>'));
		
		/* Fin de Datos del TD 2 */

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));

		$form->addGroup('dcomplementarios', 'DATOS COMPLEMENTARIOS');
		$form->addElement('dcomplementarios', new f2element_button('Complemento', 'Complemetarios', espacios(2), array("onClick" => "javascript:win_complemento('" . ($_REQUEST['action'] == 'Modificar' ? 'S' : 'N') . "');")));

		$form->addGroup('articulos', 'DATOS ART&Iacute;CULOS');

		if ($_REQUEST['action'] != 'Modificar') {
		    $form->addElement('articulos', new f2element_freeTags('<table border="0"><tr valign="top">'));
		    $form->addElement('articulos', new f2element_freeTags('<td><div id="descrip_codigo" style="float:left;display:inline">ART&Iacute;CULO</div></td>'));
		    $form->addElement('articulos', new f2element_freeTags('<td>DESCRIPCI&Oacute;N</td>'));
		    $form->addElement('articulos', new f2element_freeTags('<td align="center">CANT.</td>'));
		    $form->addElement('articulos', new f2element_freeTags('<td align="center">PRECIO (IGV)</td>'));
		    $form->addElement('articulos', new f2element_freeTags('<td align="center">NETO</td>'));
		    $form->addElement('articulos', new f2element_freeTags('<td align="center">I.G.V.</td>'));
		    $form->addElement('articulos', new f2element_freeTags('<td align="center">DSCTO.</td>'));
		    $form->addElement('articulos', new f2element_freeTags('<td align="center">TOTAL</td>'));
		    $form->addElement('articulos', new f2element_freeTags('</tr><tr valign="top"><td>'));
		    $form->addElement('articulos', new f2element_text('interface[dato_articulo][cod_articulo][]', '', '', '', 20, 13, array("onKeyUp" => "this.value=this.value.toUpperCase();limpiar_articulos2();getRegistroArt(this.value);", "class" => "form_input_numeric")));
		    $form->addElement('articulos', new f2element_freeTags('<div id="desc_articulo[]" style="float:left;display:inline"></div>'));
		    $form->addElement('articulos', new f2element_freeTags('</td><td>'));
		    $form->addElement('articulos', new f2element_textarea('interface[dato_articulo][desc_articulo][]', '', '', '', 30, 3, array("class" => "form_input", "onKeyUp" => "this.value=this.value.toUpperCase();", "onKeyDown" => "return maximaLongitud(this,199)"), array("disabled")));
		    $form->addElement('articulos', new f2element_freeTags('</td><td>'));
		    $form->addElement('articulos', new f2element_text('interface[dato_articulo][cant_articulo][]', '', '', '', 8, 8, array("onKeyUp" => "CalcularValores();", "class" => "form_input_numeric", "onKeyPress" => "return validar(event,3);", "style" => "text-align:right")));
		    $form->addElement('articulos', new f2element_freeTags('</td><td>'));
		    $form->addElement('articulos', new f2element_text('interface[dato_articulo][precio_articulo][]', '', '', '', 8, 10, array("onKeyUp" => "CalcularValores();", "class" => "form_input_numeric", "onKeyPress" => "return validar(event,3);", "style" => "text-align:right")));
		    $form->addElement('articulos', new f2element_freeTags('</td><td>'));
		    $form->addElement('articulos', new f2element_text('interface[dato_articulo][neto_articulo][]', '', '', '', 8, 10, array("class" => "form_input_numeric", "style" => "text-align:right"), array("readonly")));
		    $form->addElement('articulos', new f2element_freeTags('</td><td>'));
		    $form->addElement('articulos', new f2element_text('interface[dato_articulo][igv_articulo][]', '', '', '', 8, 10, array("onKeyUp" => "CalcularValores();", "class" => "form_input_numeric", "style" => "text-align:right"), array("readonly")));
		    $form->addElement('articulos', new f2element_freeTags('</td><td>'));
		    $form->addElement('articulos', new f2element_text('interface[dato_articulo][dscto_articulo][]', '', '', '', 8, 10, array("onKeyUp" => "CalcularValores();", "class" => "form_input_numeric", "style" => "text-align:right"), array("")));
		    $form->addElement('articulos', new f2element_freeTags('</td><td>'));
		    $form->addElement('articulos', new f2element_hidden('interface[dato_articulo][total_articulo][]', ''));
		    $form->addElement('articulos', new f2element_text('interface[dato_articulo][total_articulo2][]', '', '', '', 8, 10, array("onKeyUp" => "CalcularValoresC();", "class" => "form_input_numeric", "style" => "text-align:right"), ""));
		    $form->addElement('articulos', new f2element_freeTags('</td></tr></table>'));
		    $form->addElement('articulos', new f2element_freeTags('<table width="100%"><tr valign="top"><td align="right"><input type="button" onClick="AgregaArticulo();" value = "Agregar art&iacute;culo" style="color:#126775; font-size:12px;" /></td>'));
		    $form->addElement('articulos', new f2element_freeTags('</td></tr></table>'));
		} else {
		    $form->addElement('articulos', new f2element_freeTags('<table border="0"><tr valign="top">'));
		    $form->addElement('articulos', new f2element_freeTags('<td><div id="descrip_codigo" style="float:left;display:inline">ART&Iacute;CULO&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div></td>'));
		    $form->addElement('articulos', new f2element_freeTags('<td>DESCRIPCI&Oacute;N&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>'));
		    $form->addElement('articulos', new f2element_freeTags('<td align="center">CANT.&nbsp;&nbsp;</td>'));
		    $form->addElement('articulos', new f2element_freeTags('<td align="center">PRECIO (IGV) </td>'));
		    $form->addElement('articulos', new f2element_freeTags('<td align="center">NETO&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>'));
		    $form->addElement('articulos', new f2element_freeTags('<td align="center">I.G.V.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>'));
		    $form->addElement('articulos', new f2element_freeTags('<td align="center">DSCTO.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>'));
		    $form->addElement('articulos', new f2element_freeTags('<td align="center">TOTAL</td>'));
		    $form->addElement('articulos', new f2element_freeTags('</tr>'));
		    $form->addElement('articulos', new f2element_freeTags('</table>'));
		}

		if ($DatosArrayArticulos) {
		    $_SESSION["ARTICULOS"] = $DatosArrayArticulos;
		    $_SESSION["TOTAL_ARTICULOS"] = count($DatosArrayArticulos);
		    $form->addElement('articulos', new f2element_freeTags('<div id="datos_agregados">' . FacturasTemplate::addArticulos($DatosArrayArticulos) . '</div>'));
		    $form->addElement('articulos', new f2element_freeTags('<div id="datos_agregados_totales">' . FacturasTemplate::addTotales(FacturasModel::CalcularTotales($DatosArrayArticulos), $datos) . '</div>'));
		} else {
		    $form->addElement('articulos', new f2element_freeTags('<div id="datos_agregados"></div>'));
		    $form->addElement('articulos', new f2element_freeTags('<div id="datos_agregados_totales"></div>'));
		}

		$form->addGroup('buttons', '');

		if ($_REQUEST['action'] != 'Modificar')
			$form->addElement('buttons', new f2element_freeTags('<button name="action" type="submit" value="Guardar" class="btn-save-invoice"><img src="/sistemaweb/icons/agregar.gif" align="right" />Guardar</button>'));
		else
			$form->addElement('buttons', new f2element_freeTags('<button name="action" type="submit" value="Actualizar"><img src="/sistemaweb/icons/gedit.png" align="right" />Actualizar</button>'));


		$form->addElement('buttons', new f2element_freeTags('<button name="action" id="retorno" type="button" data='.$_SERVER["HTTP_REFERER"].' value="Regresar" onclick="window.location.assign(\''.$_SERVER["HTTP_REFERER"].'\');"><img src="/sistemaweb/icons/atra.gif" align="right" />Regresar</button>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('
		<script>
			window.onload = function() {
				parent.document.getElementById("datos[ch_fac_seriedocumento]").focus();
				parent.document.getElementById("datos[nu_tipo_pago]").focus();
			}
		</script>
		'));

		return $form->getForm() . '<div id="error_body" align="center"></div><hr>';
	}

	function addArticulos($lista) {

        if ($lista != '') {
            $formulario .= '<table border="0">' . "\n";
            $k = 0;
            foreach ($lista as $llave => $valor) {
                $k++;

                if (empty($valor['dscto_articulo']) && $valor['dscto_articulo'] <= 0) {
                    $valor['dscto_articulo'] = '0.00';
                }

                $formulario .= '<tr valign="top"><td>' . "\n" .
                        '</td></tr><tr valign="top"><td>' . "\n" .
                        '<input type="text" name="cod_articulo[]" value="' . trim($valor['cod_articulo']) . '" readonly class="form_input_numeric_disabled" size="20">' .
                        '</td><td>' . "\n" .
                        '<input type="text" name="desc_articulo[]" value="' . trim($valor['desc_articulo']) . '" readonly class="form_input_disabled" size="25">' .
                        '</td><td>' . "\n" .
                        '<input type="text" style="text-align:right" name="cant_articulo[]" value="' . money_format("%.3n", round($valor['cant_articulo'], 3)) . '" readonly class="form_input_numeric_disabled" size="5">' .
                        '</td><td>' . "\n" .
                        '<input type="text" style="text-align:right" name="precio_articulo[]" value="' . $valor['precio_articulo'] . '" readonly class="form_input_numeric_disabled" size="8">' .
                        '</td><td>' . "\n" .
                        '<input type="text" style="text-align:right" name="neto_articulo[]" value="' . $valor['neto_articulo'] . '" readonly class="form_input_numeric_disabled" size="8">' .
                        '</td><td>' . "\n" .
                        '<input type="text" style="text-align:right" name="igv_articulo[]" value="' . $valor['igv_articulo'] . '" readonly class="form_input_numeric_disabled" size="8">' .
                        '</td><td>' . "\n" .
                        '<input type="text" style="text-align:right" name="dscto_articulo[]" value="' . $valor['dscto_articulo'] . '" readonly class="form_input_numeric_disabled" size="8">' .
                        ' - ' . round(($valor['dscto_articulo'] * 100) / (money_format("%.3n", round($valor['cant_articulo'], 3)) * $valor['precio_articulo']), 2) . '% </td><td>' . "\n" .
						'<input type="hidden" style="text-align:right" name="total_articulo[]" value="' . $valor['total_articulo'] . '" readonly class="form_input_numeric_disabled" size="8">' .
						'<input type="text" style="text-align:right" name="total_articulo2[]" value="' . $valor['total_articulo2'] . '" readonly class="form_input_numeric_disabled" size="8">' .
//                        '<input type="text" style="text-align:right" name="total_articulo[]" value="' . $valor['total_articulo'] . '" readonly class="form_input_numeric_disabled" size="8">' .
                        '</td><td>' . "\n" .
                        ($_REQUEST['action'] != 'Modificar' ? "<a href=\"javascript:EliminarArticulo('" . trim($valor['cod_articulo']) . "'," . $_SESSION["TOTAL_ARTICULOS"] . ")\">X</a>" . "\n" : "") .
                        '</td>' . "\n" .
                        '</tr>' . "\n";
            }
            $formulario .= '</table>' . "\n";
        }

        return $formulario;
    }

    function addTotales($lista, $datos = null) {

        if ($_REQUEST['action'] == 'Modificar') {
            $lista['total_cant_articulo'] = '';
            $lista['total_precio_articulo'] = '';
            $lista['total_neto_articulo'] = $datos['nu_fac_valorbruto'];
            $lista['total_igv_articulo'] = $datos['nu_fac_impuesto1'];
            $lista['total_dscto_articulo'] = $datos['nu_fac_descuento1'];
            $lista['total_total_articulo'] = $datos['nu_fac_valortotal'];
            $lista['total_total_articulo2'] = $datos['nu_fac_valortotal'];
        }

        if ($_SESSION["TOTAL_ARTICULOS"] > 0) {
            $formulario .= '<table border="0" align="left">' . "\n";
            $formulario .= '<tr valign="top"><td>' . "\n" .
                    '</td></tr><tr valign="top"><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ISC: <input type="text" name="datos[nu_fac_impuesto2]" value="' . @$datos['nu_fac_impuesto2'] . '" class="form_input_numeric" size="7" onKeyPress="return validar(event,3);" > &nbsp;Recargo: <input type="text" name="datos[nu_fac_recargo2]" value="' . (isset($_REQUEST['montorecargo']) ? $_REQUEST['montorecargo'] : $lista['total_recargo']) . '" disabled class="form_input_numeric" size="5">
                   	TOTALES : ' . "\n" .
                    '</td><td>&nbsp;' . "\n" .
                    //'<input type="text" name="desc_articulo[]" value="'.$valor['desc_articulo'].'" disabled class="form_input" size="25">'.
                    '</td><td>' . "\n" .
                    '<input type="text" style="text-align:right" name="total_cant_articulo" value="' . $lista['total_cant_articulo'] . '" readonly class="form_input_numeric" size="5">' .
                    '</td><td>' . "\n" .
                    '<input type="text" style="text-align:right" name="total_precio_articulo" value="' . money_format("%.2n", $lista['total_precio_articulo']) . '" readonly class="form_input_numeric" size="8">' .
                    '</td><td>' . "\n" .
                    '<input type="text" style="text-align:right" name="datos[nu_fac_valorbruto]" value="' . money_format("%.2n", $lista['total_neto_articulo']) . '" readonly class="form_input_numeric" size="8">' .
                    '</td><td>' . "\n" .
                    '<input type="text" style="text-align:right" name="datos[nu_fac_impuesto1]" value="' . money_format("%.2n", $lista['total_igv_articulo']) . '" readonly class="form_input_numeric" size="8">' .
                    '</td><td>' . "\n" .
                    '<input type="text" style="text-align:right" name="datos[nu_fac_descuento1]" value="' . money_format("%.2n", ($lista['total_dscto_articulo'] == null ? 00 : $lista['total_dscto_articulo'])) . '" readonly class="form_input_numeric" size="8">' .
                    '</td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . "\n" .
					'<input type="hidden" style="text-align:right" name="datos[nu_fac_valortotal]" value="' . money_format("%.2n", round($lista['total_total_articulo'], 2)) . '" readonly class="form_input_numeric" size="8">' .
					'<input type="text" style="text-align:right" value="' . money_format("%.2n", round($lista['total_total_articulo2'], 2)) . '" readonly class="form_input_numeric" size="8">' .
//                    '<input type="text" style="text-align:right" name="datos[nu_fac_valortotal]" value="' . money_format("%.2n", round($lista['total_total_articulo'], 2)) . '" readonly class="form_input_numeric" size="8">' .
                    '</td><td>&nbsp;&nbsp;' . "\n" .
                    //"<a href=\"javascript:EliminarArticulo(".$llave.")\">_</a>"."\n".
                    '</td>' . "\n" .
                    '</tr>' . "\n";
            $formulario .= '</table>' . "\n";
        }

        return $formulario;
    }

    function setRegistros($codigo, $codtdoc) {
	    $RegistrosCB = FacturasModel::TiposSeriesCBArray("doc.num_seriedocumento ~ '" . trim($codigo) . "'", $codtdoc);
	    $result = '<blink><span class="MsgError">Error..</span></blink>';

        if (count($RegistrosCB['Datos']) == 1) {
            foreach ($RegistrosCB['Datos'] as $cod => $descri) {
                $auxi1 = explode('-', $RegistrosCB['Fechafin'][trim($cod)]);
                $RegistrosCB['Fechafin'][trim($cod)] = $auxi1[2] . '/' . $auxi1[1] . '/' . $auxi1[0];
                $result = $descri . " <script language=\"javascript\">top.setRegistro('" . trim($cod) . "', '" . $RegistrosCB['Numeros'][trim($cod)] . "','" . $RegistrosCB['Almacen'][trim($cod)] . "','" . $RegistrosCB['Fechafin'][trim($cod)] . "');</script>";
            }
        }

        if (count($RegistrosCB['Datos']) > 1) {
            $att_opt = array();
            foreach ($RegistrosCB['Datos'] as $cod => $descri) {
                $att_opt[trim($cod)] = array(" onclick" => "getRegistroSerie('" . trim($cod) . "');");
            }
            $cb = new f2element_combo('cbDatosSeries', '', '', $RegistrosCB['Datos'], '', array("size" => "5"), array(), $att_opt);
            $result = '<div align="left" style="position: absolute; width:250px;  z-index:1000;">' . $cb->getTag() . '</div>';
        }

        return $result;
    }

    function setRegistroSerie($codigo, $codtdoc) {
	    $RegistrosCB = FacturasModel::TiposSeriesCBArray("doc.num_seriedocumento = '" . trim($codigo) . "'", $codtdoc);
	    $result = '<blink><span class="MsgError">Error..</span></blink>';

        if (count($RegistrosCB['Datos']) == 1) {
            foreach ($RegistrosCB['Datos'] as $cod => $descri) {
                $auxi1 = explode('-', $RegistrosCB['Fechafin'][trim($cod)]);
                $RegistrosCB['Fechafin'][trim($cod)] = $auxi1[2] . '/' . $auxi1[1] . '/' . $auxi1[0];
                $result = $descri . " <script language=\"javascript\">top.setRegistro('" . trim($cod) . "', '" . $RegistrosCB['Numeros'][trim($cod)] . "','" . $RegistrosCB['Almacen'][trim($cod)] . "','" . $RegistrosCB['Fechafin'][trim($cod)] . "');</script>";
            }
        }

        if (count($RegistrosCB['Datos']) > 1) {
            $att_opt = array();
            foreach ($RegistrosCB['Datos'] as $cod => $descri) {
                $att_opt[trim($cod)] = array(" onclick" => "getRegistroSerie('" . trim($cod) . "');");
            }
            $cb = new f2element_combo('cbDatosSeries', '', '', $RegistrosCB['Datos'], '', array("size" => "5"), array(), $att_opt);
            $result = '<div align="left" style="position: absolute; width:250px;  z-index:1000;">' . $cb->getTag() . '</div>';
        }

        return $result;
    }

    function setRegistrosFormaPago($codigo, $codcred, $nu_codigo_cliente) {
        $RegistrosCB = FacturasModel::FormaPagoCBArray("substring(tab_elemento for 2 from length(tab_elemento)-1 ) ~ '" . pg_escape_string($codigo) . "'", $codcred, $nu_codigo_cliente);
        $result = '<blink><span class="MsgError">Error..</span></blink>';

        if (count($RegistrosCB['Datos']) == 1) {
            foreach ($RegistrosCB['Datos'] as $cod => $descri) {
                $result = $descri . " <script language=\"javascript\">top.setRegistroFP('" . $cod . "', '" . $RegistrosCB['Dias'][trim($cod)] . "');</script>";
            }
        }
        if (count($RegistrosCB['Datos']) > 1) {
            $att_opt = array();
            foreach ($RegistrosCB['Datos'] as $cod => $descri) {
                $att_opt[trim($cod)] = array(" onclick" => "getRegistroFP('" . $cod . "');");
            }
            $cb = new f2element_combo('cbDatosFormaPago', '', '', $RegistrosCB['Datos'], '', array("size" => "5"), array(), $att_opt);
            $result = '<div align="left" style="position: absolute; width:250px;  z-index:1000;">' . $cb->getTag() . '</div>';
        }

        return $result;
    }

    function setRegistrosListaPrecios($codigo) {
        $RegistrosCB = FacturasModel::ListaPreciosCBArray("tab_elemento ~ '" . pg_escape_string($codigo) . "'");
        $result = '<blink><span class="MsgError">Error..</span></blink>';
        if (count($RegistrosCB) == 1) {
            foreach ($RegistrosCB as $cod => $descri) {
                $result = $descri . " <script language=\"javascript\">top.setRegistroLPRE('" . $cod . "');</script>";
            }
        }
        if (count($RegistrosCB) > 1) {
            $att_opt = array();
            foreach ($RegistrosCB as $cod => $descri) {
                $att_opt[trim($cod)] = array(" onClick" => "getRegistroLPRE('" . $cod . "');");
            }
            $cb = new f2element_combo('cbDatosListaPrecios', '', '', $RegistrosCB, '', array("size" => "5"), array(), $att_opt);
            $result = '<div align="left" style="position: absolute; width:250px;  z-index:1000;">' . $cb->getTag() . '</div>';
        }

        return $result;
    }

    function setRegistrosCliente($codigo) {
        $RegistrosCB = FacturasModel::ClientesCBArray("trim(cli_codigo)||''||trim(cli_razsocial) ~ '" . pg_escape_string($codigo) . "'");
        $result = '<blink><span class="MsgError">Error..</span></blink>';

        if (count($RegistrosCB) == 1) {
            foreach ($RegistrosCB as $cod => $descri) {
                $recargo = FacturasModel::obtenerRecargoMantenimiento(trim($cod));
                $complementarios1 = FacturasModel::obtenerComplementarios(trim($cod));
                $lprecios = FacturasModel::obtenerListadePrecios(trim($cod));
                $porcdesc = FacturasModel::obtenerporcDesc(trim($cod));
                print_r($complementarios1);
                $COMP["razon_social"] = $complementarios1['cli_razsocial'];
                $COMP["direccion"] = $complementarios1['cli_direccion'];
                $COMP["ruc"] = $complementarios1['cli_ruc'];
                $COMP["comp_dir"] = $complementarios1['cli_comp_direccion'];
                $COMP["obs1"] = '';
                $COMP["obs2"] = '';
                $COMP["obs3"] = '';
                $_SESSION["ARR_COMP"] = $COMP;
                $result = $descri . " <script language=\"javascript\">top.setRegistroCli('" . trim($cod) . "','" . $recargo . "','" . trim($lprecios[0]) . "','" . trim($lprecios[1]) . "','" . trim($porcdesc[0]) . "','" . trim($porcdesc[1]) . "','" . trim($porcdesc[2]) . "','" . trim($porcdesc[3]) . "');</script>".'<span id="find-bpartner-ok"> </span>';
            }
        }
        if (count($RegistrosCB) > 1) {
            $att_opt = array();
            foreach ($RegistrosCB as $cod => $descri) {
                $att_opt[trim($cod)] = array("onclick" => "getRegistroCli('" . trim($cod) . "');");
            }
            $cb = new f2element_combo('cbDatosCliente', '', '', $RegistrosCB, '', array("size" => "5"), array(), $att_opt);
            $result = '<div align="left" style="position: absolute; width:250px;  z-index:1000;">' . $cb->getTag() . '</div>';
        }

        return $result;
    }

    function setRegistrosDesc($codigo) {
        $RegistrosCB = FacturasModel::DescuentosCBArray("substring(tab.tab_elemento for 2 from length(tab_elemento)-1) ~ '" . pg_escape_string($codigo) . "'");
        $result = '<blink><span class="MsgError">Error..</span></blink>';

        if (count($RegistrosCB['Datos']) == 1) {
            foreach ($RegistrosCB['Datos'] as $cod => $descri) {
                $result = $descri . " <script language=\"javascript\">top.setRegistroDesc('" . trim($cod) . "', '" . $RegistrosCB['Desc'][trim($cod)] . "');top.CalcularValores();</script>";
            }
        }

        if (count($RegistrosCB['Datos']) > 1) {
            $att_opt = array();
            foreach ($RegistrosCB['Datos'] as $cod => $descri) {
                $att_opt[trim($cod)] = array(" onclick" => "getRegistroDesc('" . trim($cod) . "');");
            }
            $cb = new f2element_combo('cbDatosDesc', '', '', $RegistrosCB['Datos'], '', array("size" => "5"), array(), $att_opt);
            $result = '<div align="left" style="position: absolute; width:250px;  z-index:1000;">' . $cb->getTag() . '</div>';
        }

        return $result;
    }

    function setRegistrosArticulos($codigo, $codlpre) {
        $RegistrosCB = FacturasModel::ArticulosCBArray("trim(art.art_codigo)||trim(art.art_descripcion) ~ '" . pg_escape_string($codigo) . "'", $codlpre);
        $result = '<blink><span class="MsgError">Error..</span></blink>';

        if (count($RegistrosCB['DATOS_VER']) == 1) {
            foreach ($RegistrosCB['DATOS_VER'] as $cod => $descri) {
                $descripcion = trim($RegistrosCB['DESCRIPCION'][trim($cod)]);
                $precio = money_format('%.2n', trim($RegistrosCB['PRECIO'][trim($cod)]));
                $editable = $RegistrosCB['EDITABLE'][trim($cod)];
                $result = " <script language=\"javascript\">top.setRegistroArt('" . trim($cod) . "','" . htmlspecialchars($descripcion) . "','" . $precio . "','" . trim($editable) . "');</script>";
            }
        }

        if (count($RegistrosCB['DATOS_VER']) > 1) {
            $att_opt = array();
            foreach ($RegistrosCB['DATOS_VER'] as $cod => $descri) {
                $att_opt[trim($cod)] = array(" onclick" => "getRegistroArt('" . trim($cod) . "');");
            }
            $cb = new f2element_combo('cbDatosArticulos', '', '', $RegistrosCB['DATOS_VER'], '', array("size" => "5"), array(), $att_opt);
            $result = '<div align="left" style="position: absolute; width:250px;  z-index:1000;">' . $cb->getTag() . '</div>';
        }

        return $result;
    }

    function setRegistrosCuentas($codigo) {
        $RegistrosCB = FacturasModel::CuentasCBArray("trim(tab_elemento)||''||trim(tab_descripcion) ~ '" . pg_escape_string($codigo) . "'");
        $result = '<blink><span class="MsgError">Error..</span></blink>';

        if (count($RegistrosCB) == 1) {
            foreach ($RegistrosCB as $cod => $descri) {
                $result = " <script language=\"javascript\">top.setRegistroCodCta('" . trim($cod) . "','" . $descri . "');</script>";
            }
        }

        if (count($RegistrosCB) > 1) {
            $att_opt = array();
            foreach ($RegistrosCB as $cod => $descri) {
                $att_opt[trim($cod)] = array(" onclick" => "getRegistroCodCta('" . trim($cod) . "');");
            }
            $cb = new f2element_combo('cbDatosCtas', '', '', $RegistrosCB, '', array("size" => "5"), array(), $att_opt);
            $result = '<div align="left" style="position: absolute; width:250px;  z-index:1000;">' . $cb->getTag() . '</div>';
        }
        return $result;
    }

    function setRegistrosTipoCtaBan($codigo) {
        $RegistrosCB = FacturasModel::TipoCtaBanCBArray("trim(tab_elemento)||''||trim(tab_descripcion) ~ '" . pg_escape_string($codigo) . "'");
        $result = '<blink><span class="MsgError">Error..</span></blink>';

        if (count($RegistrosCB) == 1) {
            foreach ($RegistrosCB as $cod => $descri) {
                $result = " <script language=\"javascript\">top.setRegistroTipoCtaBan('" . trim($cod) . "','" . $descri . "');</script>";
            }
        }

        if (count($RegistrosCB) > 1) {
            $att_opt = array();
            foreach ($RegistrosCB as $cod => $descri) {
                $att_opt[trim($cod)] = array(" onclick" => "getRegistroTipoCtaBan('" . trim($cod) . "');");
            }
            $cb = new f2element_combo('cbDatosTipoCtas', '', '', $RegistrosCB, '', array("size" => "5"), array(), $att_opt);
            $result = '<div align="left" style="position: absolute; width:250px;  z-index:1000;">' . $cb->getTag() . '</div>';
        }
        return $result;
    }

	function pdfDocumentoInterno($data, $resHead, $xAdd, $warehouse, $getOriginDocument, $getDataLineLFE, $getDataTaxFE, $getDataLineOFE, $getInfoInvoice, $getPlate, $getTax) {
		require '/sistemaweb/include/fpdf181.php';
		$pdf = new Lib\PDF\FPDF();

		/*echo '<hr>resHead<hr>';
		var_dump($resHead);
		echo '<hr>xAdd<hr>';
		var_dump($xAdd);
		echo '<hr>warehouse<hr>';
		var_dump($warehouse);
		echo '<hr>getOriginDocument<hr>';
		var_dump($getOriginDocument);
		echo '<hr>getDataLineLFE<hr>';
		var_dump($getDataLineLFE);
		echo '<hr>getDataTaxFE<hr>';
		var_dump($getDataTaxFE);
		echo '<hr>getDataLineOFE<hr>';
		var_dump($getDataLineOFE);
		echo '<hr>getInfoInvoice<hr>';
		var_dump($getInfoInvoice);
		echo '<hr>getPlate<hr>';
		var_dump($getPlate);
		echo '<hr>getTax<hr>';
		var_dump($getTax);*/

		$isDownload = isset($data['isDownload']) ? $data['isDownload'] : 'true';

		error_log('[general_lv_print v. 0.180503]');
		/*$serie = $data['serie'];
		$documento = $data['documento'];
		$tipoDocumento = $data['tipoDocumento'];*/
		//$codCliente = $data['codCliente'];

		$font = 'Courier';
		//$pdf = new reporte_lv();

		$pdf->_setFont($font);
		$pdf->SetFont($font,'B',10);
		$pdf->AddPage();

		$pdf->SetFont($pdf->font, 'B', 10);
		$pdf->Multicell(0,4,$pdf->printText(wordwrap($warehouse['razsocial'], 80, "\n")));

		$pdf->Cell(10,10,'RUC: '.$warehouse['ruc'],0,0,'L');
		$pdf->Cell(180,10,'DOCUMENTO DE CONTROL INTERNO',0,0,'R');

		$dir = explode('|', $warehouse['ch_direccion']);

		//$pdf->getDireccionSucursal($result_[1][2],$result_[0][2]);
		$pdf->Ln(5);

		$pdf->Ln(2);

		$w = array(125,67);//total 192
		$pdf->SetWidths($w);
		$pdf->Row(
			array('border' => 0),
			array(
				array('text' => $pdf->printText($dir[1]).' '.$pdf->printText($dir[2]). "\n" .$pdf->printText($dir[3].' - '.$dir[4].' - '.$dir[5]), 'align' => 'L'),
				array('text' => '  '.$getInfoInvoice['documenttype_name'].' ELECTRONICA'."\n  ".$resHead['serie'].' - '.$resHead['number'], 'align' => 'L'),
			)
		);

		$pdf->SetFont($pdf->font, '', 10);

		if (isset($xAdd['warehouse_name']) && $xAdd['warehouse_name'] != NULL && $xAdd['warehouse_name'] != '') {
			if (isset($xAdd['warehouse_addr']) && $xAdd['warehouse_addr'] != NULL && $xAdd['warehouse_addr'] != '') {
				$del = '|';
				$addr = explode($del, $xAdd['warehouse_addr']);
				if (isset($addr[1]) && isset($addr[2]) && isset($addr[3]) &&  isset($addr[4]) && isset($addr[5])) {
					if ($addr[1] != '' && $addr[2] != '' && $addr[3] != '' &&  $addr[4] != '' && $addr[5] != '') {
						$pdf->Cell(10, 10, $getInfoInvoice['warehouse_id'].' - '.$xAdd['warehouse_name']."\n", 0, 0, 'L');
						$pdf->Ln(4);
						$pdf->Cell(10, 10, $addr[1].' '.$addr[2]."\n", 0, 0, 'L');
						$pdf->Ln(4);
						$pdf->Cell(10, 10, $addr[3].' - '.$addr[4].' - '.$addr[5], 0, 0, 'L');
						
						$pdf->Ln(5);
					}
				}
			}
		}
		$pdf->Ln(2);

		$date = date_create($getInfoInvoice['dateacct']);

		//$this->Cell(10,10,'FECHA: '.date_format($date, 'd/m/Y'),0,0,'L');
		$pdf->Cell(10,10,'FECHA: '.date_format($date, 'Y-m-d'),0,0,'L');
		$pdf->Ln(5);
		$pdf->Cell(10,10,'MONEDA: '.$getInfoInvoice['currency_name'],0,0,'L');
		//$pdf->moneda = $result[4];
		$pdf->Ln(5);
		$pdf->Cell(10,10,$pdf->printText('RUC CLIENTE: '.$getInfoInvoice['ruc']),0,0,'L');
		$pdf->Ln(8);
		//$this->Cell(10,10,'RAZON SOCIAL: '.$this->printText($result[5]),0,0,'L');
		$pdf->Multicell(0,4,'RAZON SOCIAL: '.$pdf->printText($getInfoInvoice['bpartner_name']));
		$pdf->Ln(2);

		$plate = '';
		if ($getInfoInvoice['is_invoice'] == '1') {
			if ($getPlate != null && $getPlate['count'] > 0) {
				//$countj = count($plate);
				$countj = $getPlate['count'];
				$countj_ = $countj-1;
				for ($j=0; $j < $countj; $j++) {
					$plate .= $j == $countj_ ? $getPlate['rows'][$j]['plate'] : $getPlate['rows'][$j]['plate'].', ';
				}
				if ($plate != '') {
					$pdf->Ln(1);
					$pdf->Multicell(0,4,'PLACA: '.$pdf->printText($plate));
				}
			}
		}

		if (trim($getInfoInvoice['ch_fac_tipodocumento']) != '11' && trim($getInfoInvoice['ch_fac_tipodocumento']) != '20') {
			if (trim($getInfoInvoice['nu_tipo_pago']) != '05' && trim($getInfoInvoice['nu_tipo_pago']) != '') {
				$pdf->Ln(1);
				$pdf->Cell(10,10,'TIPO DE PAGO: '.$getInfoInvoice['no_tipo_pago'],0,0,'L');
				$pdf->Ln(4);
				if (trim($getInfoInvoice['nu_tipo_pago']) == '06') {
					$pdf->Cell(10,10,'FECHA VENCIMIENTO: '.$getInfoInvoice['fe_vencimiento'],0,0,'L');
				}
			}
		}

		$docRef = '';
		$dateRef = '';
		if (!$getOriginDocument['error']) {
			$docRef = $getOriginDocument['serie'].' - '.$getOriginDocument['number'];
			$dateRef = $getOriginDocument['date'];
		}
		if (trim($getInfoInvoice['ch_fac_tipodocumento']) == '11' || trim($getInfoInvoice['ch_fac_tipodocumento']) == '20') {
			$pdf->Ln(5);
			$pdf->Cell(10,10,'REFERENCIA: '.$docRef,0,0,'L');
			$pdf->Ln(5);
			$pdf->Cell(10,10,'FECHA REFERENCIA: '.$dateRef,0,0,'L');
			$pdf->Ln(9);
			$pdf->Multicell(0,4,$pdf->printText('OBS: '.wordwrap($getInfoInvoice['ch_fac_observacion1'], 88, "\n\n\n\n")));
		} else {
			if (trim($getInfoInvoice['ch_fac_observacion1']) != '') {
				$pdf->Ln(9);
				$pdf->Multicell(0,4,$pdf->printText('OBS: '.wordwrap($getInfoInvoice['ch_fac_observacion1'], 88, "\n\n\n\n")));
			}
		}

		if (trim($getInfoInvoice["no_detraccion_cuenta"]) != '' && $getInfoInvoice["nu_detraccion_importe"] > 0 && trim($getInfoInvoice["nu_detraccion_porcentaje"]) != '' && trim($getInfoInvoice["nu_detraccion_codigo"]) != '') {
			$pdf->Ln(7);
			$leyenda_detraccion = "OPERACION SUJETA A DETRACCION"; 
			$pdf->Multicell(0,4,$pdf->printText('LEYENDA: '.wordwrap($leyenda_detraccion, 80, "\n")));
			$pdf->Multicell(0,4,$pdf->printText('NRO. CUENTA DETRACCION: '.wordwrap($getInfoInvoice["no_detraccion_cuenta"], 30, "\n")));
			$pdf->Multicell(0,4,$pdf->printText('CODIGO DE BIENES Y SERVICIOS: '.wordwrap($getInfoInvoice["nu_detraccion_codigo"], 5, "\n")));
		}
		$pdf->Ln(10);

		$headerTable = array(
			array('text' => 'CODIGO', 'align' => 'L'),
			array('text' => 'DESCRIPCION', 'align' => 'L'),
			array('text' => 'UNIDAD', 'align' => 'L'),
			array('text' => 'CANTIDAD', 'align' => 'R'),
			array('text' => 'V. U.', 'align' => 'R'),
			array('text' => 'IMPORTE', 'align' => 'R'),
		);

		$w = array(30,62,18,20,33,30);
		$pdf->SetWidths($w);
		$pdf->SetFont($pdf->font, 'B', 10);
		$pdf->Row(array('border' => 1), $headerTable);
		$pdf->SetFont($pdf->font, '', 10);

		$first      = 0;
		$last       = count($getDataLineLFE) -1;

		$start_x    = $pdf->GetX(); //initial x (start of column position)
		$current_y  = $pdf->GetY();
		$current_x  = $pdf->GetX();

		$cell_height = 10;    //define cell height

		foreach ($getDataLineLFE as $key => $result) {
			$str = $pdf->printText($result['product_name']);

			$h  = 0;
			$vu = 0;

			if ($result['typetax'] == '1') {
				$vu = $result['price'];
			} else if ($result['typetax'] == '2') {
				$vu = $result['price'] / $result['_tax'];
			} else {
				$vu = $result['price'] / $result['_tax'];
			}

			$pdf->Row(
				array('border' => 1),
				array(
					array('text' => $pdf->printText($result['upc']), 'align' => 'L'),
					array('text' => $str, 'align' => 'L'),
					array('text' => $pdf->printText($result['uom']), 'align' => 'L'),
					array('text' => FacturasTemplate::getFormatNumber(array('number' => $result['quantity'], 'decimal' => 3)), 'align' => 'R'),
					array('text' => FacturasTemplate::getFormatNumber(array('number' => $vu, 'decimal' => 3)), 'align' => 'R'),
					array('text' => FacturasTemplate::getFormatNumber(array('number' => $result['amount'], 'decimal' => 2)), 'align' => 'R'),
				)
			);
		}

		$pdf->Ln();
		$oldY = $pdf->getY();
		$pdf->Line(10,$oldY-1,202,$oldY-1);
		$total = 0.0;

		if ($getInfoInvoice['typetax'] == '0') {
			$header = array('1','2','3');
			if ($getInfoInvoice['typetax'] == '2') {
				$resHead['grand_total'] = 0;
				$resHead['igv'] = 0;
				$resHead['taxable_operations'] = 0;
			}
			$igv;
			$text = array('OPERACIONES GRAVADAS:', 'I.G.V. ('.$getTax[0].'%)', 'IMPORTE TOTAL:');
			$value = array(
				(string)FacturasTemplate::getFormatNumber(array('number' => $resHead['taxable_operations'], 'decimal' => 2)),
				(string)FacturasTemplate::getFormatNumber(array('number' => $resHead['igv'], 'decimal' => 2)),
				(string)FacturasTemplate::getFormatNumber(array('number' => $resHead['grand_total'], 'decimal' => 2))
			);
			$value[3] = $resHead['grand_total'];
		}

		/*if ($getInfoInvoice['typetax'] == '2') {
			//Cero con cero décimos - CERO CON CERO - CERO SOLES
			$letras = 'SON: CERO Y 00/100 '.$getInfoInvoice['currency_name'];
			$letras .= "\n".'TRANSFERENCIA GRATUITA DE UN BIEN Y/O SERVICIO PRESTADO GRATUITAMENTE';
		} else if ($getInfoInvoice['typetax'] == '1') {
			$letras = 'SON: '.$data['amountLetters'];
			$letras .= "\n".'BIENES TRANSFERIDOS EN LA AMAZONIA REGION SELVA PARA SER CONSUMIDOS EN LA MISMA';
		} else {
			$letras = 'SON: '.$data['amountLetters'];
		}*/

		//if para saber si tienen el descuendo
		$pdf->MultiCell(150, 4, $pdf->printText($data['letters']), 0, "L");
		$oldY = $pdf->getY();
		$pdf->Line(10, $oldY+1, 202, $oldY+1);
		$pdf->Ln(6);

		/*$igv = $pdf->getIGVActual();
		$igv = $igv[0];*/

		/*
		(string)FacturasTemplate::getFormatNumber(array('number' => $resHead['taxable_operations'], 'decimal' => 2)),
		*/

		$w = array(157,35);//total 192
		$pdf->SetWidths($w);
		$pdf->Row(
			array('border' => 0),
			array(
				array('text' => $data['textog'], 'align' => 'R'),
				array('text' => FacturasTemplate::getFormatNumber(array('number' => $data['taxable_operations'], 'decimal' => 2)), 'align' => 'R'),
			)
		);
		if ($data['disc'] > 0) {
			$pdf->SetWidths($w);
			$pdf->Row(
				array('border' => 0),
				array(
					array('text' => 'TOTAL DESCUENTOS:', 'align' => 'R'),
					array('text' => FacturasTemplate::getFormatNumber(array('number' => $data['disc'], 'decimal' => 2)), 'align' => 'R'),
				)
			);
		}
		$pdf->SetWidths($w);
		$pdf->Row(
			array('border' => 0),
			array(
				array('text' => 'I.G.V.('.$getTax[0].'):', 'align' => 'R'),
				array('text' => FacturasTemplate::getFormatNumber(array('number' => $data['tax_total'], 'decimal' => 2)), 'align' => 'R'),
			)
		);
		$pdf->SetWidths($w);
		$pdf->Row(
			array('border' => 0),
			array(
				array('text' => 'IMPORTE TOTAL:', 'align' => 'R'),
				array('text' => FacturasTemplate::getFormatNumber(array('number' => $data['grand_total'], 'decimal' => 2)), 'align' => 'R'),
			)
		);
		$pdf->Ln();

		$pdf->Ln(4);
		$pdf->Cell(186,10,'COPIA PARA CONTROL ADMINISTRATIVO',0,0,'C');
		$pdf->Ln(5);
		$pdf->Cell(182,10,'CONSULTE LA REPRESENTACION IMPRESA EN '.$warehouse['ebiurl'],0,0,'C');
		$pdf->Ln(5);
		$pdf->Cell(188,10,'AUTORIZADO MEDIANTE R.I. NRO. '.$warehouse['ebiauth'],0,0,'C');

		//$this->updateIGV($data);

		$pdf->Output();

		/*if($isDownload == 'false') {
			$pdf->Output();
		} else {
			$pdf->Output('D','detalleDocumento.pdf');
		}*/
		//$pdf->Output('D','detalleDocumento.pdf');
    }

    function parts($str) {
    	$result = '';
		$del = '|';
		$this->dir = explode($del, $str);
    }

    function getFormatNumber($data) {
		return number_format($data['number'], $data['decimal'], '.', ',');
	}

	function getMaxChar($data) {
		$max = strlen($data[0]);
		if (strlen($data[1] > $max)) {
			$max = strlen($data[1]);
		}
		if (strlen($data[2]) > $max) {
			$max = strlen($data[2]);
		}
		return $max;//.' 0 => '.strlen($data[0]).' 1 => '.strlen($data[1]).' 2 => '.strlen($data[2]);
	}
}

