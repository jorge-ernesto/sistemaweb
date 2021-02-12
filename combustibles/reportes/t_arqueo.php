<?php

class ArqueoTemplate extends Template {

	function titulo() {
		return '<h2 align="center"><b>Arqueo</b></h2>';
    	}
    
	function formatoNumero($number) {
		return number_format($number, 2, '.', ',');
	}

	function formSearch($almacen, $fecha, $type) {
		if ($almacen == "") 
			$almacen = $_SESSION['almacen'];

		$almacenes = ArqueoModel::obtenerSucursales("");
		$tipos = array("T" => "Todos", "C" => "Combustible", "M" => "Market");

		$form = new Form('', "Buscar", FORM_METHOD_POST, "control.php", '', "control");	
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "REPORTES.ARQUEO"));
	
		$form->addGroup("FORM_GROUP_CONSULTA", "Consultar");
		$form->addElement("FORM_GROUP_CONSULTA", new f2element_freeTags('<table border="0" align="center">'));
		$form->addElement("FORM_GROUP_CONSULTA", new f2element_freeTags("<tr><td align='right'>Almac&eacute;n: <td>"));
		//$form->addElement("FORM_GROUP_CONSULTA", new form_element_combo("", "almacen", $almacen, "", "", 1, $almacenes, false, ""));
		$form->addElement("FORM_GROUP_CONSULTA", new f2element_combo("almacen", "", $almacen, $almacenes, "", array("onfocus" => "getFechaEmision();")));

		$form->addElement("FORM_GROUP_CONSULTA", new f2element_freeTags("<tr><td align='right'>Fecha: <td>"));
		$form->addElement("FORM_GROUP_CONSULTA", new form_element_text("", "fecha", $fecha, '', '', 12, 10));
		
		//$form->addElement("FORM_GROUP_CONSULTA", new form_element_anytext('<a href="javascript:show_calendar('."'Buscar.fecha'".');"><img src="/sistemaweb/images/showcalendar.gif"  border=0></a>'));
		//$form->addElement("FORM_GROUP_CONSULTA", new form_element_anytext('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div><br/>'));

        $form->addElement("FORM_GROUP_CONSULTA", new f2element_freeTags('<tr><td align="right">Tipo Venta: <td>'));
        $form->addElement("FORM_GROUP_CONSULTA", new form_element_combo("", "type", $type, "", "", 1, $tipos, false, ""));

		$form->addElement("FORM_GROUP_CONSULTA", new f2element_freeTags('<tr><td colspan="2" align="center"><br>'));
		$form->addElement("FORM_GROUP_CONSULTA", new f2element_freeTags('<button name="action" type="submit" value="Buscar"><img src="/sistemaweb/icons/gbuscar.png" align="right" />Buscar</button>'));

		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext(
		'<script>
			window.onload = function() {
				parent.document.getElementById("almacen").focus();
			}
		</script>'
		));

		return $form->getForm();
    }
       
    function ListadoRegistros($turnosEfectivo, $turnosCredito, $tickets, $documentos) {
		$result = '';

		$result .= '<table class="RegistroVentas">';
		$result .= '<tr>';
		$result .= '<td class="grid_cabecera">DESCRIPCION</th>';
		$result .= '<td colspan="2" class="grid_cabecera">TURNO 1</th>';
		$result .= '<td colspan="2" class="grid_cabecera">TURNO 2</th>';
		$result .= '<td colspan="2" class="grid_cabecera">TURNO 3</th>';
		$result .= '<tr>';
		$result .= '<td class="grid_cabecera"></th>';
		$result .= '<td class="grid_cabecera">S/.</th>';
		$result .= '<td class="grid_cabecera">US$</th>';
		$result .= '<td class="grid_cabecera">S/.</th>';
		$result .= '<td class="grid_cabecera">US$</th>';
		$result .= '<td class="grid_cabecera">S/.</th>';
		$result .= '<td class="grid_cabecera">US$</th>';

		$totalEfectivo	= 0;
		$totalCredito	= 0;

		$i = 0;

		foreach($turnosEfectivo['data'] as $clave => $data) {

			$i++;
			$color = ($i%2==0?"grid_detalle_par":"grid_detalle_par");

			$result .= '<tr>';
			$result .= '<td class="'.$color.'">' . htmlentities($data['descripcion']) . '</td>';
			$result .= '<td class="'.$color.'" align="right">' . htmlentities(@ArqueoTemplate::formatoNumero($data['turno1'])) . '</td>';
			$result .= '<td class="'.$color.'"></th>';
			$result .= '<td class="'.$color.'" align="right">' . htmlentities(@ArqueoTemplate::formatoNumero($data['turno2'])) . '</td>';
			$result .= '<td class="'.$color.'"></th>';
			$result .= '<td class="'.$color.'" align="right">' . htmlentities(@ArqueoTemplate::formatoNumero($data['turno3'])) . '</td>';
			$result .= '<td class="'.$color.'"></th>';
			$result .= '</tr>';

			$totalEfectivo1 = $totalEfectivo1 + $data['turno1'];
			$totalEfectivo2 = $totalEfectivo2 + $data['turno2'];
			$totalEfectivo3 = $totalEfectivo3 + $data['turno3'];
		}

		$i = 0;

		foreach($turnosCredito['data'] as $clave => $data) {

			$i++;
			$color = ($i%2==0?"grid_detalle_impar":"grid_detalle_impar");

			$result .= '<tr>';
			$result .= '<td class="'.$color.'">' . htmlentities($data['descripcion']) . '</td>';
			$result .= '<td class="'.$color.'" align="right">' . htmlentities(@ArqueoTemplate::formatoNumero($data['turno1'])) . '</td>';
			$result .= '<td class="'.$color.'"></th>';
			$result .= '<td class="'.$color.'" align="right">' . htmlentities(@ArqueoTemplate::formatoNumero($data['turno2'])) . '</td>';
			$result .= '<td class="'.$color.'"></th>';
			$result .= '<td class="'.$color.'" align="right">' . htmlentities(@ArqueoTemplate::formatoNumero($data['turno3'])) . '</td>';
			$result .= '<td class="'.$color.'"></th>';
			$result .= '</tr>';

			$totalCredito1 = $totalCredito1 + $data['turno1'];
			$totalCredito2 = $totalCredito2 + $data['turno2'];
			$totalCredito3 = $totalCredito3 + $data['turno3'];

		}

    	$result .= '<tr>';
    	$result .= '<td class="bgcolor_cabecera" align="right">TOTAL: </td>';
		$result .= '<td class="bgcolor_cabecera" align="right">' . htmlentities(@ArqueoTemplate::formatoNumero($totalEfectivo1 + $totalCredito1)) . '</td>';
		$result .= '<td class="bgcolor_cabecera"></th>';
		$result .= '<td class="bgcolor_cabecera" align="right">' . htmlentities(@ArqueoTemplate::formatoNumero($totalEfectivo2 + $totalCredito2)) . '</td>';
		$result .= '<td class="bgcolor_cabecera"></th>';
		$result .= '<td class="bgcolor_cabecera" align="right">' . htmlentities(@ArqueoTemplate::formatoNumero($totalEfectivo3 + $totalCredito3)) . '</td>';
		$result .= '<td class="bgcolor_cabecera"></th>';
    	$result .= '</tr>';

		$result .= '</table>';


		$result .= '<table class="RegistroVentas">';
		$result .= '<tr>';
		$result .= '<td>&nbsp;</td>';

		$result .= '<tr>';
		$result .= '<td colspan="6" class="grid_cabecera"></th>';
		$result .= '<td colspan="2" class="grid_cabecera">VENTA</th>';
		$result .= '<td colspan="2" class="grid_cabecera">VALES</th>';
		$result .= '<td colspan="2" class="grid_cabecera">TRANSFERENCIAS GRATUITAS</th>';

		$result .= '<tr>';
		$result .= '<td class="grid_cabecera">TURNO</th>';
		$result .= '<td class="grid_cabecera">TIPO</th>';
		$result .= '<td class="grid_cabecera">SERIE</th>';
		$result .= '<td class="grid_cabecera">NUMERO</th>';
		$result .= '<td class="grid_cabecera">CANTIDAD</th>';
		$result .= '<td class="grid_cabecera">ANULADO</th>';
		$result .= '<td class="grid_cabecera">S/.</th>';
		$result .= '<td class="grid_cabecera">US$</th>';
		$result .= '<td class="grid_cabecera">S/.</th>';
		$result .= '<td class="grid_cabecera">US$</th>';
		$result .= '<td class="grid_cabecera">S/.</th>';
		$result .= '<td class="grid_cabecera">US$</th>';

		$i = 0;

		/* DOCUMENTOS MANUALES */

		foreach($documentos['tipos'] as $tipo => $series) {
			foreach($series['series'] as $serie => $ventas) {
			    	foreach($ventas['ventas'] as $rango => $registro) {
			    		/*

						$estilo		= "tbodyimparRegistroVentas";
						$estilo2	= "RowImporteImpar";

						if ($i % 2 == 0){
							$estilo = "tbodyparRegistroVentas";
							$estilo2 = "RowImportePar";
						}

						$i++;
						*/
						$color = ($i%2==0?"grid_detalle_par":"grid_detalle_impar");

						$result .= '<tr>';
						$result .= '<td class="'.$color.'" align="center">' . htmlentities(@$registro['turno']) . '</td>';
						$result .= '<td class="'.$color.'" align="center">' . htmlentities(@$registro['tipo']) . '</td>';
						$result .= '<td class="'.$color.'" align="center">' . htmlentities(@$registro['serie']) . '</td>';
						$result .= '<td class="'.$color.'" align="center">' . htmlentities(@$registro['rango']) . '</td>';
						$result .= '<td class="'.$color.'" align="right">' . htmlentities(@$registro['cantidad']) . '</td>';
						$result .= '<td class="'.$color.'" align="right">' . htmlentities(@$registro['anulado']) . '</td>';
						$result .= '<td class="'.$color.'" align="right">' . htmlentities(@ArqueoTemplate::formatoNumero($registro['total'])) . '</td>';
						$result .= '<td class="'.$color.'"></td>';
						$result .= '<td class="'.$color.'" align="right">' . htmlentities(@ArqueoTemplate::formatoNumero($registro['totalvales'])) . '</td>';
						$result .= '<td class="'.$color.'"></td>';
						$result .= '<td class="'.$color.'"></td>';
						$result .= '<td class="'.$color.'"></td>';
						$result .= '</tr>';
					}

			    	$result .= '<tr>';
			    	$result .= '<td class="bgcolor_cabecera" colspan="6" align="right">TOTAL SERIE ' . htmlentities($serie) . ': </td>';
			    	$result .= '<td class="bgcolor_cabecera" align="right">' . htmlentities(@ArqueoTemplate::formatoNumero($ventas['totales']['total'])) . '</td>';
			    	$result .= '<td class="bgcolor_cabecera" align="right">0.00</td>';
			    	$result .= '<td class="bgcolor_cabecera" align="right">0.00</td>';
			    	$result .= '<td class="bgcolor_cabecera" align="right">0.00</td>';
			    	$result .= '<td class="bgcolor_cabecera" align="right">' . htmlentities(@ArqueoTemplate::formatoNumero($ventas['totales']['totalgratuita'])) . '</td>';
			    	$result .= '<td class="bgcolor_cabecera" align="right">0.00</td>';
			    	$result .= '</tr>';

			}

		    $result .= '<tr>';
		    $result .= '<td class="coluheaderTotalTipo" colspan="6" align="right">Total Documentos: </td>';
		    $result .= '<td class="coluheaderTotalTipo" align="right">' . htmlentities(@ArqueoTemplate::formatoNumero($documentos['tipos'][$tipo]['totales']['total'])) . '</td>';
			$result .= '<td class="coluheaderTotalTipo" align="right">0.00</td>';
			$result .= '<td class="coluheaderTotalTipo" align="right">0.00</td>';
			$result .= '<td class="coluheaderTotalTipo" align="right">0.00</td>';
		    $result .= '<td class="coluheaderTotalTipo" align="right">' . htmlentities(@ArqueoTemplate::formatoNumero($documentos['tipos'][$tipo]['totales']['totalgratuita'])) . '</td>';
			$result .= '<td class="coluheaderTotalTipo" align="right">0.00/td>';
		    $result .= '</tr>';

		}

		/* TICKETS */

		foreach($tickets['tipos'] as $tipo => $series) {
			foreach($series['series'] as $serie => $ventas) {
			    	foreach($ventas['ventas'] as $rango => $registro) {
/*
					$estilo		= "tbodyimparRegistroVentas";
					$estilo2	= "RowImporteImpar";

					if ($i % 2 == 0){
						$estilo = "tbodyparRegistroVentas";
						$estilo2 = "RowImportePar";
					}
*/
					$i++;
					$color = ($i%2==0?"grid_detalle_par":"grid_detalle_impar");

					$result .= '<tr>';
					$result .= '<td class="'.$color.'" align="center">' . htmlentities(@$registro['turno']) . '</td>';
					$result .= '<td class="'.$color.'" align="center">' . htmlentities(@$registro['tipo']) . '</td>';
					$result .= '<td class="'.$color.'" align="center">' . htmlentities(@$registro['serie']) . '</td>';
					$result .= '<td class="'.$color.'" align="center">' . htmlentities(@$registro['rango']) . '</td>';
					$result .= '<td class="'.$color.'" align="right">' . htmlentities(@$registro['cantidad']) . '</td>';
					$result .= '<td class="'.$color.'" align="right">' . htmlentities(@$registro['anulado']) . '</td>';
					$result .= '<td class="'.$color.'" align="right">' . htmlentities(@ArqueoTemplate::formatoNumero($registro['total'])) . '</td>';
					$result .= '<td class="'.$color.'"></td>';
					$result .= '<td class="'.$color.'" align="right">' . htmlentities(@ArqueoTemplate::formatoNumero($registro['totalvales'])) . '</td>';
					$result .= '<td class="'.$color.'"></td>';
					$result .= '<td class="'.$color.'"></td>';
					$result .= '<td class="'.$color.'"></td>';

				}

			    	$result .= '<tr>';
			    	$result .= '<td class="bgcolor_cabecera" colspan="6" align="right">Total Serie ' . htmlentities($serie) . ': </td>';
			    	$result .= '<td class="bgcolor_cabecera" align="right">' . htmlentities(@ArqueoTemplate::formatoNumero($ventas['totales']['total'])) . '</td>';
			    	$result .= '<td class="bgcolor_cabecera" align="right">0.00</td>';
			    	$result .= '<td class="bgcolor_cabecera" align="right">' . htmlentities(@ArqueoTemplate::formatoNumero($ventas['totales']['totalvales'])) . '</td>';
			    	$result .= '<td class="bgcolor_cabecera" align="right">0.00</td>';
			    	$result .= '<td class="bgcolor_cabecera" align="right">0.00</td>';
			    	$result .= '<td class="bgcolor_cabecera" align="right">0.00</td>';
			    	$result .= '</tr>';

			}

		    	$result .= '<tr>';
		    	$result .= '<td class="grid_cabecera" colspan="6" align="right">Total Tickets: </td>';
		    	$result .= '<td class="grid_cabecera" align="right">' . htmlentities(@ArqueoTemplate::formatoNumero($tickets['tipos'][$tipo]['totales']['total'])) . '</td>';
				$result .= '<td class="grid_cabecera" align="right">0.00</td>';
			    $result .= '<td class="grid_cabecera" align="right">' . htmlentities(@ArqueoTemplate::formatoNumero($tickets['tipos'][$tipo]['totales']['totalvales'])) . '</td>';
				$result .= '<td class="grid_cabecera" align="right">0.00</td>';
				$result .= '<td class="grid_cabecera" align="right">0.00</td>';
				$result .= '<td class="grid_cabecera" align="right">0.00</td>';
		    	$result .= '</tr>';


		}

		return $result;
		    
	} 

}
