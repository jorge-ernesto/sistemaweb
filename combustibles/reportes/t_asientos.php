<?php

class VarillasTemplate extends Template {

	function titulo() {
		return '<h2 align="center"><b>Stock Inicial - Medida Diaria de Varilla</b></h2>';
    	}
    
	function formSearch($cod_almacen, $desde, $hasta) {
		$hoy_timestamp  = time();
		$ayer_timestamp = $hoy_timestamp - (24*60*60);
		$ayer = date("d/m/Y", $ayer_timestamp);

		$mes  = date("m");
		$dia  = "01";
		$year = date("Y");
	
		$inicio_mes = mktime(0, 0, 0, $mes, $dia, $year);
		$fin_mes    = mktime(0, 0, 0, $mes+1, 0, $year);
		
		if ($desde == "" or $hasta == "") {
			$desde      = date("d/m/Y", $inicio_mes);
			$hasta      = date("d/m/Y", $fin_mes);
		} 

		if ($cod_almacen == "") 
			$cod_almacen = $_SESSION['almacen'];

		$sucursales = VarillasModel::obtenerSucursales("");
		$tanques    = VarillasModel::obtenerTanques($cod_almacen,"");

		$form = new Form('', "Buscar", FORM_METHOD_POST, "control.php", '', "control");	
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MOVIMIENTOS.VARILLAS"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_combo("<b>Sucursal:</b>", "ch_almacen", $cod_almacen, '<br><br>', '', 1, $sucursales, false, 'onchange="varillasUpdateSucursal(this)"'));
	
		$form->addGroup("FORM_GROUP_CONSULTA", "Consultar");
		$form->addElement("FORM_GROUP_CONSULTA", new form_element_text("Desde", "desde", $desde, '', '', 12, 10));
		$form->addElement("FORM_GROUP_CONSULTA", new form_element_anytext('<a href="javascript:show_calendar('."'Buscar.desde'".');"><img src="/sistemaweb/images/showcalendar.gif"  border=0></a>&nbsp;&nbsp;'));
		$form->addElement("FORM_GROUP_CONSULTA", new form_element_anytext('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div><br/>'));
		$form->addElement("FORM_GROUP_CONSULTA", new form_element_text("Hasta", "hasta", $hasta, '', '', 12, 10));
		$form->addElement("FORM_GROUP_CONSULTA", new form_element_anytext('<a href="javascript:show_calendar('."'Buscar.hasta'".');"><img src="/sistemaweb/images/showcalendar.gif"  border=0></a>&nbsp;&nbsp;<br><br>'));
		$form->addElement("FORM_GROUP_CONSULTA", new form_element_submit("action", "Buscar"));
		$form->addElement("FORM_GROUP_CONSULTA", new form_element_anytext('&nbsp;&nbsp;&nbsp;&nbsp;'));
		$form->addElement("FORM_GROUP_CONSULTA", new form_element_submit("action", "Agregar"));		
				
		return $form->getForm();
    	}
    
    	function listado($resultado, $desde, $hasta, $ch_almacen) {

		$result  = '<div align="center"><button name="fm" value="" onClick="javascript:parent.location.href=\'control.php?rqst=MOVIMIENTOS.VARILLAS&action=PDF&ch_almacen='.trim($ch_almacen).'&desde='.trim($desde).'&hasta='.trim($hasta).'\';return false"><img src="/sistemaweb/images/icono_pdf.gif" alt="left"/> PDF</button>&nbsp;&nbsp;&nbsp;';
		$result .= '<button name="fm" value="" onClick="javascript:parent.location.href=\'control.php?rqst=MOVIMIENTOS.VARILLAS&action=Excel&ch_almacen='.trim($ch_almacen).'&desde='.trim($desde).'&hasta='.trim($hasta).'\';return false"><img src="/sistemaweb/images/excel_icon.png" alt="left"/> Excel</button></br>';
		$result .= '<div align="center"><form name="editar" method="post" target="control" action="control.php">';
		$result .= '<input type="hidden" name="rqst" value="MOVIMIENTOS.VARILLAS">';
		$result .= '<input type="hidden" name="desde" value="' . htmlentities($desde) . '">';
		$result .= '<input type="hidden" name="hasta" value="' . htmlentities($hasta) . '">';
		$result .= '<input type="hidden" name="ch_almacen" value="' . htmlentities($ch_almacen) . '">';
		$result .= '<table border="1">';
		$result .= '<tr bgcolor="#FFFFCD">';
		$result .= '<th>Fecha</th>';
		$result .= '<th>Tanque</th>';
		$result .= '<th>Nombre</th>';
		$result .= '<th>Medida</th>';
		$result .= '<th>Responsable</th>';
		$result .= '<th>Fecha Actualizacion</th>';
		$result .= '<th>Usuario</th>';
		$result .= '<th>IP</th>';
		$result .= '<th>&nbsp;</th>';
		$result .= '</tr>';
	
		foreach($resultado as $i => $tanque) {
		    	$result .= '<tr>';		    	
		    	$result .= '<td>' . htmlentities($tanque['dt_fecha']) . '</td>';
		    	$result .= '<td>' . htmlentities($tanque['ch_tanque']) . '</td>';
		    	$result .= '<td>' . htmlentities($tanque['ch_nombre']) . '</td>';
		    	$result .= '<td>' . htmlentities($tanque['nu_medicion']) . '</td>';
		    	$result .= '<td>' . htmlentities($tanque['ch_responsable']) . '</td>';
			$result .= '<td>' . htmlentities($tanque['fec_actualizacion']) . '</td>';
			$result .= '<td>' . htmlentities($tanque['ch_usuario']) . '</td>';
			$result .= '<td>' . htmlentities($tanque['ch_auditorpc']) . '</td>';
			$result .= '<td><A href="control.php?rqst=MOVIMIENTOS.VARILLAS&action=Editar&sucursal='.$tanque['ch_sucursal'].'&dia='.$tanque['dt_fecha'].'&tanque='.$tanque['ch_tanque'].'&almacen='.$ch_almacen.'&desde='.$desde.'&hasta='.$hasta.'" target="control"><img alt="Editar" title="Editar" src="/sistemaweb/icons/anular.gif" align="middle" border="0"/></A>&nbsp;';			
		    	$result .= '</tr>';
		}
	
		$result .= '</table>';
		$result .= '</form></div>';
	
		return $result;
    	}
    
    	function formAgregarEditar($tipo, $resultado, $cod_almacen, $desde, $hasta) {

		$hoy_timestamp  = time();
		$ayer_timestamp = $hoy_timestamp - (24*60*60);
		$ayer = date("d/m/Y", $ayer_timestamp);
			
		if (trim($desde)=="" or trim($hasta)=="") {
			$desde = $ayer;
			$hasta = $ayer;
		}
			
		if ($tipo == "E") 
			$cod_almacen = $resultado['ch_sucursal']; 
		else
			$cod_almacen = $_SESSION['almacen']; 

		$sucursales = VarillasModel::obtenerSucursales("");
		$tanques    = VarillasModel::obtenerTanques($cod_almacen, "");

		$form = new Form('', "AgregarEditar", FORM_METHOD_POST, "control.php", '', "control");	
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MOVIMIENTOS.VARILLAS"));
		
		if ($tipo == "A") 
			$nombre = "Ingresar";
		else
			$nombre = "Editar";			
	
		$form->addGroup("FORM_GROUP_INGRESAR", $nombre);
		$form->addElement("FORM_GROUP_INGRESAR", new form_element_anytext('<table><tr><td>Sucursal: </td><td>'));
		
		if ($tipo == "E") {
			$suc = VarillasModel::obtenerSucursales($resultado['ch_sucursal']);
			$tan = VarillasModel::obtenerTanques($resultado['ch_sucursal'], $resultado['ch_tanque']);
			
			$form->addElement("FORM_GROUP_INGRESAR", new form_element_text("", "ch_almacen", $suc[$resultado['ch_sucursal']], '', '', 27, 27, true));
			$form->addElement("FORM_GROUP_INGRESAR", new form_element_anytext('</td></tr><tr><td>Tanque: </td><td>'));
			$form->addElement("FORM_GROUP_INGRESAR", new form_element_text("", "ch_tanque", $tan[$resultado['ch_tanque']], '', '', 15, 15, true));
			$form->addElement("FORM_GROUP_INGRESAR", new form_element_anytext('</td></tr><tr><td>Fecha de medicion: </td><td>'));
			$form->addElement("FORM_GROUP_INGRESAR", new form_element_text("", "dt_fecha", $resultado['dt_fecha'], '', '', 12, 10, true));
			$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("ch_sucursal", $resultado['ch_sucursal']));
			$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("ch_tanque", $resultado['ch_tanque']));
			$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("dt_fecha", $resultado['dt_fecha']));
		} else {		
			$form->addElement("FORM_GROUP_INGRESAR", new form_element_combo("", "ch_almacen", $cod_almacen, '', '', 1, $sucursales, false, ''));
			$form->addElement("FORM_GROUP_INGRESAR", new form_element_anytext('</td></tr><tr><td>Tanque: </td><td>'));
			$form->addElement("FORM_GROUP_INGRESAR", new form_element_combo("", "ch_tanque", '', '', '', 1, $tanques, false, ''));
			$form->addElement("FORM_GROUP_INGRESAR", new form_element_anytext('</td></tr><tr><td>Fecha de medicion: </td><td>'));
			$form->addElement("FORM_GROUP_INGRESAR", new form_element_text("", "dt_fecha", $ayer, '', '', 12, 10, ($_SESSION['usuario'] == "SISTEMAS" || $_SESSION['usuario'] == "CONTAB") ? false : true));
			$form->addElement("FORM_GROUP_INGRESAR", new form_element_anytext('<a href="javascript:show_calendar('."'AgregarEditar.dt_fecha'".');"><img src="/sistemaweb/images/showcalendar.gif"  border=0></a>&nbsp;&nbsp;<br/>'));
			$form->addElement("FORM_GROUP_INGRESAR", new form_element_anytext('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));

			$resultado['nu_medicion'] = "";
			$resultado['ch_responsable'] = "";
		}	
		
		$form->addElement("FORM_GROUP_INGRESAR", new form_element_anytext('</td></tr><tr><td>Cantidad: </td><td>'));
		$form->addElement("FORM_GROUP_INGRESAR", new form_element_text("", "nu_medicion", $resultado['nu_medicion'], '', '', 12, 10));
		$form->addElement("FORM_GROUP_INGRESAR", new form_element_anytext('</td></tr><tr><td>Responsable: </td><td>'));
		$form->addElement("FORM_GROUP_INGRESAR", new form_element_text("", "ch_responsable", $resultado['ch_responsable'], '', '', 6, 6));
		$form->addElement("FORM_GROUP_INGRESAR", new form_element_anytext('</td></tr><tr><td colspan="2" align="center">'));
		if ($tipo == "A")
			$form->addElement("FORM_GROUP_INGRESAR", new form_element_submit("action", "Ingresar"));
		else
			$form->addElement("FORM_GROUP_INGRESAR", new form_element_submit("action", "Guardar"));	
		$form->addElement("FORM_GROUP_INGRESAR", new form_element_anytext('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'));			
		$form->addElement("FORM_GROUP_INGRESAR", new form_element_button("btRegresar", "Regresar", '<br>', '', 1, 'onclick="varillasRegresar(\'' . $desde . '\', \'' . $hasta . '\', \'' . $cod_almacen . '\')"'));
		$form->addElement("FORM_GROUP_INGRESAR", new form_element_anytext('</td></tr>'));
		$form->addElement("FORM_GROUP_INGRESAR", new form_element_anytext('</table>'));

		return $form->getForm();
    	}
    	
    	function reportePDF($res, $almacen, $desde, $hasta) {
    	
		$nomalmacen = VarillasModel::obtenerSucursales($almacen);

		$cab = Array(
				"fecha"		=>	"FECHA",
				"tanque"	=>	"TANQUE",
				"nombrecom"	=>	"NOMBRE COMBUSTIBLE",
				"medicion"	=>	"MEDICION",
				"responsable"	=>	"RESPONSABLE"
			);

		$reporte = new CReportes2("P","pt","A4");

		$reporte->Ln();	 
		$reporte->definirCabecera(2, "L", " ");
		$reporte->definirCabecera(2, "R", "Pagina %p");
		$reporte->definirCabeceraSize(3, "C", "courier,B,15", "MEDIDA DIARIA DE VARILLA");
		$reporte->definirCabecera(4, "L", "ALMACEN  : ".$nomalmacen[$almacen]);
		$reporte->definirCabecera(5, "L", "FECHA DEL ".$desde." AL ".$hasta);
		$reporte->definirCabecera(6, "L", "");

		$reporte->SetMargins(10,10,10);
		$reporte->SetFont("courier", "", 9.5);

		$reporte->definirColumna("fecha",$reporte->TIPO_TEXTO,13,"L", "_pri");
		$reporte->definirColumna("tanque",$reporte->TIPO_TEXTO,22,"L", "_pri");
		$reporte->definirColumna("nombrecom",$reporte->TIPO_TEXTO,18,"R", "_pri");
		$reporte->definirColumna("medicion",$reporte->TIPO_IMPORTE,15,"R", "_pri");
		$reporte->definirColumna("responsable",$reporte->TIPO_TEXTO,20,"R", "_pri");

		$reporte->borrarCabeceraPredeterminada();
		$reporte->definirCabeceraPredeterminada($cab, "_pri");
		$reporte->AddPage();
		$reporte->Ln();	

		for ($i = 0; $i<count($res); $i++) {
			$nomtanque = VarillasModel::obtenerTanques($almacen, $res[$i]['ch_tanque']);
			$arr = array(	"fecha"=>$res[$i]['dt_fecha'], 
					"tanque"=>$nomtanque[$res[$i]['ch_tanque']], 
					"nombrecom"=>$res[$i]['ch_nombre'], 
					"medicion"=>$res[$i]['nu_medicion'], 
					"responsable"=>$res[$i]['ch_responsable'],
				);
			$reporte->nuevaFila($arr, "_pri"); 	
		}

		$reporte->borrarCabecera();
		$reporte->borrarCabeceraPredeterminada();
		$reporte->Lnew();$reporte->Lnew();				
		$reporte->Output("/sistemaweb/ventas_clientes/reportes/pdf/Varillaje.pdf", "F");

		return '<script> window.open("/sistemaweb/ventas_clientes/reportes/pdf/Varillaje.pdf","miwin","width=750,height=550,scrollbars=yes, resizable=yes, menubar=no");</script>';
	}
	
	function reporteExcel($res, $almacen, $desde, $hasta) {

		$nomalmacen = VarillasModel::obtenerSucursales($almacen);

		$workbook = new Workbook($chrFileName);
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

		$worksheet1 =& $workbook->add_worksheet('Hoja de Resultados');
		$worksheet1->set_column(0, 0, 16);
		$worksheet1->set_column(1, 1, 16);
		$worksheet1->set_column(2, 2, 12);
		$worksheet1->set_column(3, 3, 12);
		$worksheet1->set_column(4, 4, 12);
		$worksheet1->set_column(5, 5, 16);
		$worksheet1->set_column(6, 6, 16);

		$worksheet1->set_zoom(100);
		$worksheet1->set_landscape(100);

		$worksheet1->write_string(1, 0, "MEDIDA DIARIA DE VARILLA",$formato0);
		$worksheet1->write_string(3, 0, "ALMACEN: ".$nomalmacen[$almacen],$formato0);
		$worksheet1->write_string(4, 0, "FECHA DEL ".$desde." AL ".$hasta,$formato0);
		$worksheet1->write_string(5, 0, " ",$formato0);

		$a = 7;
		$worksheet1->write_string($a, 0, "FECHA",$formato2);
		$worksheet1->write_string($a, 1, "TANQUE",$formato2);
		$worksheet1->write_string($a, 2, "NOMBRE COMBUSTIBLE",$formato2);
		$worksheet1->write_string($a, 3, "MEDICION",$formato2);	
		$worksheet1->write_string($a, 4, "RESPONSABLE",$formato2);
		
		$a = 8;	

		for ($j=0; $j<count($res); $j++) {	
			$nomtanque = VarillasModel::obtenerTanques($almacen, $res[$j]['ch_tanque']);	
			
			$worksheet1->write_string($a, 0, $res[$j]['dt_fecha'],$formato5);
			$worksheet1->write_string($a, 0, $nomtanque[$res[$j]['ch_tanque']],$formato5);
			$worksheet1->write_string($a, 0, $res[$j]['ch_nombre'],$formato5);	
			$worksheet1->write_number($a, 0, number_format($res[$j]['nu_medicion'],3,'.',''),$formato5);
			$worksheet1->write_string($a, 0, $res[$j]['ch_responsable'],$formato5);	
			$a++;
		}
			
		$workbook->close();	

		$chrFileName = "Varillaje";
		header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename=$chrFileName.xls" );
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
		header("Pragma: public");	
	}
}
