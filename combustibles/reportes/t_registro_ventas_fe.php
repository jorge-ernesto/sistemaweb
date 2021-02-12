<?php

class RegistroVentasFETemplate extends Template {

	function titulo() {
		return '<h2 align="center"><b>Registro de Ventas Series Electronicas</b></h2>';
    	}
    
	function formSearch($almacen, $fdesde, $fhasta, $type) {

		if ($almacen == "") 
			$almacen = $_SESSION['almacen'];

		$almacenes = RegistroVentasFEModel::obtenerSucursales("");

		$tipos = array("T" => "Todos", "C" => "Combustible", "M" => "Market");

		$form = new Form('', "Buscar", FORM_METHOD_POST, "control.php", '', "control");	
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "REPORTES.REGISTROVENTASFE"));
	
		$form->addGroup("FORM_GROUP_CONSULTA", "Consultar");
		$form->addElement("FORM_GROUP_CONSULTA", new f2element_freeTags('<table border="0" align="center">'));
		$form->addElement("FORM_GROUP_CONSULTA", new f2element_freeTags("<tr><td align='right'>Almac&eacute;n: <td>"));
		$form->addElement("FORM_GROUP_CONSULTA", new form_element_combo("", "almacen", $almacen, "", "", 1, $almacenes, false, ""));
		$form->addElement("FORM_GROUP_CONSULTA", new f2element_freeTags("<tr><td align='right'>Fecha Inicio: <td>"));
		$form->addElement("FORM_GROUP_CONSULTA", new form_element_text("", "fdesde", $fdesde, '', '', 12, 10));
		$form->addElement("FORM_GROUP_CONSULTA", new form_element_anytext('<a href="javascript:show_calendar('."'Buscar.fdesde'".');"><img src="/sistemaweb/images/showcalendar.gif"  border=0></a>'));
		$form->addElement("FORM_GROUP_CONSULTA", new form_element_anytext('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div><br/>'));
		$form->addElement("FORM_GROUP_CONSULTA", new f2element_freeTags("<tr><td align='right'>Fecha Final: <td>"));
		$form->addElement("FORM_GROUP_CONSULTA", new form_element_text("", "fhasta", $fhasta, '', '', 12, 10));
		$form->addElement("FORM_GROUP_CONSULTA", new form_element_anytext('<a href="javascript:show_calendar('."'Buscar.fhasta'".');"><img src="/sistemaweb/images/showcalendar.gif"  border=0></a>'));

        $form->addElement("FORM_GROUP_CONSULTA", new f2element_freeTags('<tr><td align="right">Tipo Venta: <td>'));
        $form->addElement("FORM_GROUP_CONSULTA", new form_element_combo("", "type", $type, "", "", 1, $tipos, false, ""));

		$form->addElement("FORM_GROUP_CONSULTA", new f2element_freeTags('<tr><td colspan="2" align="center"><br>'));
		$form->addElement("FORM_GROUP_CONSULTA", new f2element_freeTags('<button name="action" onclick="return ckechDateValid()" type="submit" value="Buscar"><img src="/sistemaweb/icons/gbuscar.png" align="right" />Buscar</button>'));
		$form->addElement("FORM_GROUP_CONSULTA", new f2element_freeTags('<button name="action" type="submit" value="PDF"><img src="/sistemaweb/images/icono_pdf.gif" align="right" />PDF</button>'));
		$form->addElement("FORM_GROUP_CONSULTA", new f2element_freeTags('<button name="action" type="submit" value="Excel"><img src="/sistemaweb/icons/gexcel.png" align="right" />Excel</button>'));
				
		return $form->getForm();
    }

    	/*function checkSendDocuments($params) {
    		$first = $params['serie'][0];
    		$status = '';
			if (preg_match("/[a-z]/i", $first)) {
				if ($params['type'] == 0) {
					//$status = $first.': FIND! '.$params['timecop'];
					if (isset($params['ebi'][$params['timecop']])) {
						$ebi = $params['ebi'][$params['timecop']];

						if ($ebi['total'] > 0 && ($ebi['send'] == $ebi['total'])) {
							if($params['qty'] > 1) {
								$status .= '<b>ENVIADOS</b>';
							} else {
								$status .= '<b>ENVIADO</b>';
							}
						}
					}
				} else {
					if ($params['status'] == $params['qty']) {
						if($params['qty'] > 1) {
							$status .= '<b>ENVIADOS</b>';
						} else {
							$status .= '<b>ENVIADO</b>';
						}
					} else {
						$status .= '';
					}
				}
			}
			return $status;
    	}*/

    	function checkSendDocuments($params) {
    		$first = $params['serie'][0];
    		$status = '';
			if (preg_match("/[a-z]/i", $first)) {
				if ($params['type'] == 0) {
					if (isset($params['ebi'][$params['timecop']])) {
						$ebi = $params['ebi'][$params['timecop']];

						if ($ebi['registred'] > 0) {
							$status .= $ebi['registred'].' Pendiente(s)';
						} else  {
							$status .= 'Enviado(s)';
						}
						/*if ($ebi['registred'] > 0) {
							$status .= $ebi['registred'].' Pendiente(s)';
						} else if ($ebi['send'] > 0) {
							$status .= $status != '' ? ', ' : '';
							$status .= $ebi['send'].' Enviado(s)';
						}*/
					} else {
						$status .= 'Enviado(s)';
					}
				} else {
					//if ($params['status'] == $params['qty']) {
						/*if($params['qty'] > 1) {
							$status .= '<b>ENVIADOS</b>';
						} else {
							$status .= '<b>ENVIADO</b>';
						}*/
						if ($params['status']['pendiente'] > 0) {
							$status .= $params['status']['pendiente'].' Pendiente(s)';
						} else if ($params['status']['completado'] > 0) {
							$status .= $status != '' ? ', ' : '';
							$status .= $params['status']['completado'].' Completado(s)';
						} else if ($params['status']['anulado'] > 0) {
							$status .= $status != '' ? ', ' : '';
							$status .= $params['status']['anulado'].' Anulado(s)';
						} else if ($params['status']['completado_enviado'] > 0) {
							$status .= $status != '' ? ', ' : '';
							$status .= $params['status']['completado_enviado'].' Completado Enviado(s)';
						} else if ($params['status']['completado_error'] > 0) {
							$status .= $status != '' ? ', ' : '';
							$status .= $params['status']['completado_error'].' Completado Error(s)';
						} else if ($params['status']['anulado_enviado'] > 0) {
							$status .= $status != '' ? ', ' : '';
							$status .= $params['status']['anulado_enviado'].' Anulado Enviado(s)';
						} else if ($params['status']['anulado_error'] > 0) {
							$status .= $status != '' ? ', ' : '';
							$status .= $params['status']['anulado_error'].' Anulado Error(s)';
						}
					/*} else {
						$status .= '';
					}*/
				}
			}
			//$status .= '</b>';
			return $status;
    	}
    
    	function listado($tickets, $documentos, $ebi) {

    	$result = '';

		$result .= '<table class="RegistroVentas">';
		$result .= '<tr>';
		$result .= '<td class="coluheader">TIPO</th>';
		$result .= '<td class="coluheader">SERIE</th>';
		$result .= '<td class="coluheader">NUMERO</th>';
		$result .= '<td class="coluheader">FECHA EMISION</th>';
		$result .= '<td class="coluheader">CANTIDAD</th>';
		$result .= '<td class="coluheader">ANULADO</th>';
		$result .= '<td class="coluheader">VALOR BRUTO</th>';
		$result .= '<td class="coluheader">DESCUENTO</th>';
		$result .= '<td class="coluheader">VALOR VENTA</th>';
		$result .= '<td class="coluheader">I.G.V.</th>';
		$result .= '<td class="coluheader">TOTAL</th>';
		$result .= '<td class="coluheader">ESTADO COR</th>';
		$result .= '<td class="coluheader">ESTADO DOC</th>';
		$result .= '</tr>';
	
		$i = 0;
		$estilo = '';
		$estilo2 = '';

		/* DOCUMENTOS MANUALES */
		foreach($documentos['tipos'] as $tipo => $series) {

		    	$result .= '<tr>';
		    	$result .= '<td  class="coluheaderTipo"colspan="11">' . htmlentities($tipo) . '</td>';
		    	$result .= '<td class=""></td>';
				$result .= '<td class=""></td>';
		    	$result .= '</tr>';
		    
			foreach($series['series'] as $serie => $ventas) {

			    	$result .= '<tr>';
			    	$result .= '<td  class="coluheaderSerie" colspan="4">SERIE ' . htmlentities($serie) . ': </td>';
			    	$result .= '<td  class="coluheaderSerie" colspan="7"></td>';
			    	$result .= '<td class=""></td>';
					$result .= '<td class=""></td>';
			    	$result .= '</tr>';
			    
			    foreach($ventas['ventas'] as $rango => $registro) {

					$estilo		= "tbodyimparRegistroVentasFE";
					$estilo2	= "RowImporteImpar";

					if ($i % 2 == 0){
						$estilo		= "tbodyparRegistroVentasFE";
						$estilo2 	= "RowImportePar";
					}

					$i++;

					$result .= '<tr>';

					$result .= '<td class="'.$estilo.'">' . htmlentities(@$registro['tipo']) . '</td>';
					$result .= '<td class="'.$estilo.'">' . htmlentities(@$registro['serie']) . '</td>';
					$result .= '<td class="'.$estilo.'">' . htmlentities(@$registro['rango']) . '</td>';
					$result .= '<td class="'.$estilo.'">' . htmlentities(@$registro['femision']) . '</td>';
					$result .= '<td class="'.$estilo2.'">' . htmlentities(@$registro['cantidad']) . '</td>';
					$result .= '<td class="'.$estilo2.'">' . htmlentities(@$registro['anulado']) . '</td>';
					$result .= '<td class="'.$estilo2.'">' . htmlentities(@RegistroVentasFETemplate::formato($registro['vbruto'])) . '</td>';
					$result .= '<td class="'.$estilo2.'">' . htmlentities(@RegistroVentasFETemplate::formato($registro['dscto'])) . '</td>';
					$result .= '<td class="'.$estilo2.'">' . htmlentities(@RegistroVentasFETemplate::formato($registro['vventa'])) . '</td>';
					$result .= '<td class="'.$estilo2.'">' . htmlentities(@RegistroVentasFETemplate::formato($registro['igv'])) . '</td>';
					$result .= '<td class="'.$estilo2.'">' . htmlentities(@RegistroVentasFETemplate::formato($registro['total'])) . '</td>';

					if(@$registro['cantreal'] > @$registro['cantidad']) {
						$result .= '<td class="'.$estilo.'"><p style="color:red">ERROR: Número Correlativo</p></td>';
					} else {
						$result .= '<td class="'.$estilo.'"></td>';
					}

					$status = '';

					$status = RegistroVentasFETemplate::checkSendDocuments(
						array(
							'type' => 1,
							'status' => array(
								'pendiente' => $registro['_pendiente'],
								'completado' => $registro['_completado'],
								'anulado' => $registro['_anulado'],
								'completado_enviado' => $registro['_completado_enviado'],
								'completado_error' => $registro['_completado_error'],
								'anulado_enviado' => $registro['_anulado_enviado'],
								'anulado_error' => $registro['_anulado_error'],
							),
							'qty' => $registro['cantidad'],
							'serie' => $registro['serie']
						)
					);

					$result .= '<td class=""><b>'.$status.'</b></td>';

				}

			    	$result .= '<tr>';
			    	$result .= '<td class="coluheaderTotalSerie" colspan="4">TOTAL SERIE ' . htmlentities($serie) . ': </td>';
			    	$result .= '<td class="coluheaderTotalSerie">' . htmlentities(@$ventas['totales']['cantidad']) . '</td>';
			    	$result .= '<td class="coluheaderTotalSerie">' . htmlentities(@$ventas['totales']['anulado']) . '</td>';
			    	$result .= '<td class="coluheaderTotalSerie">' . htmlentities(@RegistroVentasFETemplate::formato($ventas['totales']['vbruto'])) . '</td>';
			    	$result .= '<td class="coluheaderTotalSerie">' . htmlentities(@RegistroVentasFETemplate::formato($ventas['totales']['dscto'])) . '</td>';
			    	$result .= '<td class="coluheaderTotalSerie">' . htmlentities(@RegistroVentasFETemplate::formato($ventas['totales']['vventa'])) . '</td>';
			    	$result .= '<td class="coluheaderTotalSerie">' . htmlentities(@RegistroVentasFETemplate::formato($ventas['totales']['igv'])) . '</td>';
			    	$result .= '<td class="coluheaderTotalSerie">' . htmlentities(@RegistroVentasFETemplate::formato($ventas['totales']['total'])) . '</td>';
			    	$result .= '<td class=""></td>';
			    	$result .= '<td class=""></td>';
			    	$result .= '</tr>';

			}

		    	$result .= '<tr>';
		    	$result .= '<td class="coluheaderTotalTipo" colspan="4">TOTAL ' . htmlentities($tipo) . ': </td>';
		    	$result .= '<td class="coluheaderTotalTipo">' . htmlentities(@$documentos['tipos'][$tipo]['totales']['cantidad']) . '</td>';
		    	$result .= '<td class="coluheaderTotalTipo">' . htmlentities(@$documentos['tipos'][$tipo]['totales']['anulado']) . '</td>';
		    	$result .= '<td class="coluheaderTotalTipo">' . htmlentities(@RegistroVentasFETemplate::formato($documentos['tipos'][$tipo]['totales']['vbruto'])) . '</td>';
		    	$result .= '<td class="coluheaderTotalTipo">' . htmlentities(@RegistroVentasFETemplate::formato($documentos['tipos'][$tipo]['totales']['dscto'])) . '</td>';
		    	$result .= '<td class="coluheaderTotalTipo">' . htmlentities(@RegistroVentasFETemplate::formato($documentos['tipos'][$tipo]['totales']['vventa'])) . '</td>';
		    	$result .= '<td class="coluheaderTotalTipo">' . htmlentities(@RegistroVentasFETemplate::formato($documentos['tipos'][$tipo]['totales']['igv'])) . '</td>';
		    	$result .= '<td class="coluheaderTotalTipo">' . htmlentities(@RegistroVentasFETemplate::formato($documentos['tipos'][$tipo]['totales']['total'])) . '</td>';
		    	$result .= '<td class=""></td>';
		    	$result .= '<td class=""></td>';
		    	$result .= '</tr>';
		}

		$result .= '<tr>';
		$result .= '<td class="coluheaderTotalTipo" colspan="4">TOTAL GENERAL DOCUMENTOS: </td>';
		$result .= '<td class="coluheaderTotalTipo">' . htmlentities(@$documentos['totales']['cantidad']) . '</td>';
		$result .= '<td class="coluheaderTotalTipo">' . htmlentities(@$documentos['totales']['anulado']) . '</td>';
		$result .= '<td class="coluheaderTotalTipo">' . htmlentities(@RegistroVentasFETemplate::formato($documentos['totales']['vbruto'])) . '</td>';
		$result .= '<td class="coluheaderTotalTipo">' . htmlentities(@RegistroVentasFETemplate::formato($documentos['totales']['dscto'])) . '</td>';
		$result .= '<td class="coluheaderTotalTipo">' . htmlentities(@RegistroVentasFETemplate::formato($documentos['totales']['vventa'])) . '</td>';
		$result .= '<td class="coluheaderTotalTipo">' . htmlentities(@RegistroVentasFETemplate::formato($documentos['totales']['igv'])) . '</td>';
		$result .= '<td class="coluheaderTotalTipo">' . htmlentities(@RegistroVentasFETemplate::formato($documentos['totales']['total'])) . '</td>';
		$result .= '<td class=""></td>';
		$result .= '<td class=""></td>';

		$general_cantidad_doc 	= $documentos['totales']['cantidad'];
		$general_anulado_doc 	= $documentos['totales']['anulado'];
		$general_vbruto_doc 	= $documentos['totales']['vbruto'];
		$general_dscto_doc 		= $documentos['totales']['dscto'];
		$general_vventa_doc 	= $documentos['totales']['vventa'];
		$general_igv_doc 		= $documentos['totales']['igv'];
		$general_total_doc 		= $documentos['totales']['total'];


		/* TICKETS */

	   	$result .= '<tr>';
		$result .= '<td  class="tbodyimparRegistroVentasFE" colspan="11">&nbsp;</td>';
		$result .= '<td class=""></td>';
		$result .= '<td class=""></td>';
	    $result .= '</tr>';

		foreach($tickets['tipos'] as $tipo => $series) {

		    	$result .= '<tr>';
		    	$result .= '<td  class="coluheaderTipo" colspan="11">' . htmlentities($tipo) . '</td>';
		    	$result .= '<td class=""></td>';
				$result .= '<td class=""></td>';
		    	$result .= '</tr>';
		    
			foreach($series['series'] as $serie => $ventas) {

			    	$result .= '<tr>';
			    	$result .= '<td  class="coluheaderSerie" colspan="4">SERIE ' . htmlentities($serie) . ': </td>';
			    	$result .= '<td  class="coluheaderSerie" colspan="7"></td>';
			    	$result .= '<td class=""></td>';
					$result .= '<td class=""></td>';
			    	$result .= '</tr>';
			    
			    	foreach($ventas['ventas'] as $rango => $registro) {

					$estilo		= "tbodyimparRegistroVentasFE";
					$estilo2	= "RowImporteImpar";

					if ($i % 2 == 0){
						$estilo = "tbodyparRegistroVentasFE";
						$estilo2 = "RowImportePar";
					}

					$i++;

					$result .= '<tr>';
					$result .= '<td class="'.$estilo.'">' . htmlentities(@$registro['tipo']) . '</td>';
					$result .= '<td class="'.$estilo.'">' . htmlentities(@$registro['serie']) . '</td>';
					$result .= '<td class="'.$estilo.'">' . htmlentities(@$registro['rango']) . '</td>';
					$result .= '<td class="'.$estilo.'">' . htmlentities(@$registro['femision']) . '</td>';
					$result .= '<td class="'.$estilo2.'">' . htmlentities(@$registro['cantidad']) . '</td>';
					$result .= '<td class="'.$estilo2.'">' . htmlentities(@$registro['anulado']) . '</td>';
					$result .= '<td class="'.$estilo2.'">' . htmlentities(@RegistroVentasFETemplate::formato($registro['vbruto'])) . '</td>';
					$result .= '<td class="'.$estilo2.'">' . htmlentities(@RegistroVentasFETemplate::formato($registro['dscto'])) . '</td>';
					$result .= '<td class="'.$estilo2.'">' . htmlentities(@RegistroVentasFETemplate::formato($registro['vventa'])) . '</td>';
					$result .= '<td class="'.$estilo2.'">' . htmlentities(@RegistroVentasFETemplate::formato($registro['igv'])) . '</td>';
					$result .= '<td class="'.$estilo2.'">' . htmlentities(@RegistroVentasFETemplate::formato($registro['total'])) . '</td>';
					$result .= '<td class="'.$estilo.'"></td>';

					
					$status = '';

					$status = RegistroVentasFETemplate::checkSendDocuments(
						array(
							'type' => 0,
							'ebi' => $ebi,
							'timecop' => $registro['timecop'],
							'serie' => $registro['serie'],
							'qty' => $registro['cantidad'],
						)
					);

					$result .= '<td class="">'.$status.'</td>';
					
					//$result .= '<td class="'.$estilo.'">asasasasasas</td>';
				}

			    	$result .= '<tr>';
			    	$result .= '<td class="coluheaderTotalSerie" colspan="4">TOTAL SERIE ' . htmlentities($serie) . ': </td>';

			    	$result .= '<td class="coluheaderTotalSerie">' . htmlentities(@$ventas['totales']['cantidad']) . '</td>';
			    	$result .= '<td class="coluheaderTotalSerie">' . htmlentities(@$ventas['totales']['anulado']) . '</td>';
			    	$result .= '<td class="coluheaderTotalSerie">' . htmlentities(@RegistroVentasFETemplate::formato($ventas['totales']['vbruto'])) . '</td>';
			    	$result .= '<td class="coluheaderTotalSerie">' . htmlentities(@RegistroVentasFETemplate::formato($ventas['totales']['dscto'])) . '</td>';
			    	$result .= '<td class="coluheaderTotalSerie">' . htmlentities(@RegistroVentasFETemplate::formato($ventas['totales']['vventa'])) . '</td>';
			    	$result .= '<td class="coluheaderTotalSerie">' . htmlentities(@RegistroVentasFETemplate::formato($ventas['totales']['igv'])) . '</td>';
			    	$result .= '<td class="coluheaderTotalSerie">' . htmlentities(@RegistroVentasFETemplate::formato($ventas['totales']['total'])) . '</td>';
			    	$result .= '<td class=""></td>';
			    	$result .= '<td class=""></td>';
			    	$result .= '</tr>';

			}

		    	$result .= '<tr>';
		    	$result .= '<td class="coluheaderTotalTipo" colspan="4">TOTAL ' . htmlentities($tipo) . ': </td>';

		    	$result .= '<td class="coluheaderTotalTipo">' . htmlentities(@$tickets['tipos'][$tipo]['totales']['cantidad']) . '</td>';
		    	$result .= '<td class="coluheaderTotalTipo">' . htmlentities(@$tickets['tipos'][$tipo]['totales']['anulado']) . '</td>';
		    	$result .= '<td class="coluheaderTotalTipo">' . htmlentities(@RegistroVentasFETemplate::formato($tickets['tipos'][$tipo]['totales']['vbruto'])) . '</td>';
		    	$result .= '<td class="coluheaderTotalTipo">' . htmlentities(@RegistroVentasFETemplate::formato($tickets['tipos'][$tipo]['totales']['dscto'])) . '</td>';
		    	$result .= '<td class="coluheaderTotalTipo">' . htmlentities(@RegistroVentasFETemplate::formato($tickets['tipos'][$tipo]['totales']['vventa'])) . '</td>';
		    	$result .= '<td class="coluheaderTotalTipo">' . htmlentities(@RegistroVentasFETemplate::formato($tickets['tipos'][$tipo]['totales']['igv'])) . '</td>';
		    	$result .= '<td class="coluheaderTotalTipo">' . htmlentities(@RegistroVentasFETemplate::formato($tickets['tipos'][$tipo]['totales']['total'])) . '</td>';
		    	$result .= '<td class=""></td>';
				$result .= '<td class=""></td>';
		    	$result .= '</tr>';

			$general_cantidad_ticket 	= $tickets['tipos'][$tipo]['totales']['cantidad'];
			$general_anulado_ticket 	= $tickets['tipos'][$tipo]['totales']['anulado'];
			$general_vbruto_ticket 		= $tickets['tipos'][$tipo]['totales']['vbruto'];
			$general_dscto_ticket 		= $tickets['tipos'][$tipo]['totales']['dscto'];
			$general_vventa_ticket 		= $tickets['tipos'][$tipo]['totales']['vventa'];
			$general_igv_ticket 		= $tickets['tipos'][$tipo]['totales']['igv'];
			$general_total_ticket 		= $tickets['tipos'][$tipo]['totales']['total'];

		}

		$result .= '<tr>';
		$result .= '<td class="coluheaderTotalTipo" colspan="4">TOTAL GENERAL: </td>';
		$result .= '<td class="coluheaderTotalTipo">' . htmlentities($general_cantidad_doc + $general_cantidad_ticket) . '</td>';
		$result .= '<td class="coluheaderTotalTipo">' . htmlentities($general_anulado_doc + $general_anulado_ticket) . '</td>';
		$result .= '<td class="coluheaderTotalTipo">' . htmlentities(@RegistroVentasFETemplate::formato($general_vbruto_doc + $general_vbruto_ticket)) . '</td>';
		$result .= '<td class="coluheaderTotalTipo">' . htmlentities(@RegistroVentasFETemplate::formato($general_dscto_doc + $general_dscto_ticket)) . '</td>';
		$result .= '<td class="coluheaderTotalTipo">' . htmlentities(@RegistroVentasFETemplate::formato($general_vventa_doc + $general_vventa_ticket)) . '</td>';
		$result .= '<td class="coluheaderTotalTipo">' . htmlentities(@RegistroVentasFETemplate::formato($general_igv_doc + $general_igv_ticket)) . '</td>';
		$result .= '<td class="coluheaderTotalTipo">' . htmlentities(@RegistroVentasFETemplate::formato($general_total_doc + $general_total_ticket)) . '</td>';
		$result .= '<td class=""></td>';
		$result .= '<td class=""></td>';

		return $result;

    }
    	
    function reportePDF($tickets, $documentos, $almacen, $desde, $hasta) {
    	
		$nomalmacen = RegistroVentasFEModel::obtenerSucursales($almacen);

		$cab = Array(
			"tipo" 			=> "TIPO",
			"serie"			=> "SERIE",
			"rango"			=> "NUMERO",
			"femision"		=> "FECHA EMISION",
			"cantidad"		=> "CANTIDAD",
			"anulado"		=> "ANULADO",
			"vbruto"		=> "VALOR BRUTO",
			"dscto"			=> "DESCUENTO",
			"vventa"		=> "VALOR VENTA",
			"igv"			=> "I.G.V.",
			"total"			=> "TOTAL",
		    );


		$reporte = new CReportes2("P","pt","A4");

		$reporte->Ln();
		$reporte->definirCabecera(1, "L", "SISTEMAWEB");
		$reporte->definirCabecera(1, "R", "Pag. %p");
		$reporte->definirCabecera(2, "L", "Usuario: %u");
		$reporte->definirCabecera(2, "R", "%f");
		$reporte->definirCabeceraSize(3, "C", "courier,B,12", "Registro de Ventas Series");
		$reporte->definirCabecera(4, "L", "ALMACEN: ".$nomalmacen[$almacen]);
		$reporte->definirCabecera(5, "L", "FECHA DEL ".$desde." AL ".$hasta);
		$reporte->definirCabecera(6, "L", "");

		$reporte->definirColumna("tipo", $reporte->TIPO_TEXTO, 4, "C");
		$reporte->definirColumna("serie", $reporte->TIPO_TEXTO, 12, "C");
		$reporte->definirColumna("rango", $reporte->TIPO_TEXTO, 15, "C");
		$reporte->definirColumna("femision", $reporte->TIPO_TEXTO, 10, "C");
		$reporte->definirColumna("cantidad", $reporte->TIPO_TEXTO, 10, "R");
		$reporte->definirColumna("anulado", $reporte->TIPO_TEXTO, 8, "R");
		$reporte->definirColumna("vbruto", $reporte->TIPO_IMPORTE, 11, "R");
		$reporte->definirColumna("dscto", $reporte->TIPO_IMPORTE, 9, "R");
		$reporte->definirColumna("vventa", $reporte->TIPO_IMPORTE, 11, "R");
		$reporte->definirColumna("igv", $reporte->TIPO_IMPORTE, 10, "R");
		$reporte->definirColumna("total", $reporte->TIPO_IMPORTE, 10, "R");

		$reporte->definirColumna("tipo", $reporte->TIPO_TEXTO, 4, "C", "_pri");
		$reporte->definirColumna("serie", $reporte->TIPO_TEXTO, 12, "C", "_pri");
		$reporte->definirColumna("rango", $reporte->TIPO_TEXTO, 15, "C", "_pri");
		$reporte->definirColumna("femision", $reporte->TIPO_TEXTO, 13, "C", "_pri");
		$reporte->definirColumna("cantidad", $reporte->TIPO_TEXTO, 10, "C", "_pri");
		$reporte->definirColumna("anulado", $reporte->TIPO_TEXTO, 8, "C", "_pri");
		$reporte->definirColumna("vbruto", $reporte->TIPO_TEXTO, 11, "C", "_pri");
		$reporte->definirColumna("dscto", $reporte->TIPO_TEXTO, 9, "C", "_pri");
		$reporte->definirColumna("vventa", $reporte->TIPO_TEXTO, 11, "C", "_pri");
		$reporte->definirColumna("igv", $reporte->TIPO_TEXTO, 10, "C", "_pri");
		$reporte->definirColumna("total", $reporte->TIPO_TEXTO, 10, "C", "_pri");

		$reporte->definirColumna("tipo", $reporte->TIPO_TEXTO, 50, "L", "tipo", "B");
		$reporte->definirColumna("serie", $reporte->TIPO_TEXTO, 50, "L", "serie", "B");

		$reporte->definirColumna("tipo", $reporte->TIPO_TEXTO, 4, "C", "total", "B");
		$reporte->definirColumna("serie", $reporte->TIPO_TEXTO, 5, "C", "total", "B");
		$reporte->definirColumna("rango", $reporte->TIPO_TEXTO, 5, "C", "total", "B");
		$reporte->definirColumna("femision", $reporte->TIPO_TEXTO, 27, "C", "total", "B");
		$reporte->definirColumna("cantidad", $reporte->TIPO_TEXTO, 10, "R", "total", "B");
		$reporte->definirColumna("anulado", $reporte->TIPO_TEXTO, 8, "R", "total", "B");
		$reporte->definirColumna("vbruto", $reporte->TIPO_IMPORTE, 11, "R", "total", "B");
		$reporte->definirColumna("dscto", $reporte->TIPO_IMPORTE, 9, "R", "total", "B");
		$reporte->definirColumna("vventa", $reporte->TIPO_IMPORTE, 11, "R", "total", "B");
		$reporte->definirColumna("igv", $reporte->TIPO_IMPORTE, 10, "R", "total", "B");
		$reporte->definirColumna("total", $reporte->TIPO_IMPORTE, 10, "R", "total", "B");

		$reporte->definirColumna("tipo", $reporte->TIPO_TEXTO, 4, "C", "total2", "B");
		$reporte->definirColumna("serie", $reporte->TIPO_TEXTO, 5, "C", "total2", "B");
		$reporte->definirColumna("rango", $reporte->TIPO_TEXTO, 5, "C", "total2", "B");
		$reporte->definirColumna("femision", $reporte->TIPO_TEXTO, 27, "C", "total2", "B");
		$reporte->definirColumna("cantidad", $reporte->TIPO_TEXTO, 10, "R", "total2", "B");
		$reporte->definirColumna("anulado", $reporte->TIPO_TEXTO, 8, "R", "total2", "B");
		$reporte->definirColumna("vbruto", $reporte->TIPO_IMPORTE, 11, "R", "total2", "B");
		$reporte->definirColumna("dscto", $reporte->TIPO_IMPORTE, 9, "R", "total2", "B");
		$reporte->definirColumna("vventa", $reporte->TIPO_IMPORTE, 11, "R", "total2", "B");
		$reporte->definirColumna("igv", $reporte->TIPO_IMPORTE, 10, "R", "total2", "B");
		$reporte->definirColumna("total", $reporte->TIPO_IMPORTE, 10, "R", "total2", "B");
	
		$reporte->definirCabeceraPredeterminada($cab, "_pri");

		$reporte->SetMargins(10,10,10);
		$reporte->SetFont("courier", "", 7.8);


		$reporte->AddPage();

		foreach($documentos['tipos'] as $tipo => $series) {

			$reporte->lineaH();
		    	$array = Array("tipo" => $tipo);
		    	$reporte->nuevaFila($array, "tipo");

			foreach($series['series'] as $serie => $ventas) {

				$reporte->lineaH();
		    		$array = Array("serie" => "SERIE ".trim($serie).": ");
		    		$reporte->nuevaFila($array, "serie");
				$reporte->lineaH();

			    	foreach($ventas['ventas'] as $rango => $registro) {

					$reporte->nuevaFila($registro);
				}

				$reporte->lineaH();
			    	$ventas['totales']['tipo'] = "";
			    	$ventas['totales']['serie'] = "";
			    	$ventas['totales']['rango'] = "";
			    	$ventas['totales']['femision'] = "TOTAL SERIE " . $serie.": ";
			    	$reporte->nuevaFila($ventas['totales'], "total");
				$reporte->lineaH();
		    	}

			$reporte->lineaH();
		    	$documentos['tipos'][$tipo]['totales']['tipo'] = "";
		    	$documentos['tipos'][$tipo]['totales']['serie'] = "";
		    	$documentos['tipos'][$tipo]['totales']['rango'] = "";
		    	$documentos['tipos'][$tipo]['totales']['femision'] = "TOTAL " . $tipo.": ";
		    	$reporte->nuevaFila($documentos['tipos'][$tipo]['totales'], "total2");
			$reporte->lineaH();

		}

		$reporte->lineaH();
	    	$documentos['totales']['tipo'] = "";
	    	$documentos['totales']['serie'] = "";
	    	$documentos['totales']['rango'] = "";
	    	$documentos['totales']['femision'] = "TOTAL GENERAL DOCUMENTOS: ";
	    	$reporte->nuevaFila($documentos['totales'], "total2");
		$reporte->lineaH();

		$general_cantidad_doc = $documentos['totales']['cantidad'];
		$general_anulado_doc = $documentos['totales']['anulado'];
		$general_vbruto_doc = $documentos['totales']['vbruto'];
		$general_dscto_doc = $documentos['totales']['dscto'];
		$general_vventa_doc = $documentos['totales']['vventa'];
		$general_igv_doc = $documentos['totales']['igv'];
		$general_total_doc = $documentos['totales']['total'];

		foreach($tickets['tipos'] as $tipo => $series) {

			$reporte->lineaH();
		    	$array = Array("tipo" => $tipo);
		    	$reporte->nuevaFila($array, "tipo");

			foreach($series['series'] as $serie => $ventas) {

				$reporte->lineaH();
		    		$array = Array("serie" => $serie);
		    		$reporte->nuevaFila($array, "serie");
				$reporte->lineaH();

			    	foreach($ventas['ventas'] as $rango => $registro) {
					$reporte->nuevaFila($registro);
				}

				$reporte->lineaH();
			    	$ventas['totales']['tipo'] = "";
			    	$ventas['totales']['serie'] = "";
			    	$ventas['totales']['rango'] = "";
			    	$ventas['totales']['femision'] = "TOTAL SERIE " . $serie.": ";
			    	$reporte->nuevaFila($ventas['totales'], "total");
				$reporte->lineaH();

		    	}

			$reporte->lineaH();
		    	$tickets['tipos'][$tipo]['totales']['tipo'] = "";
		    	$tickets['tipos'][$tipo]['totales']['serie'] = "";
		    	$tickets['tipos'][$tipo]['totales']['rango'] = "";
		    	$tickets['tipos'][$tipo]['totales']['femision'] = "TOTAL " . $tipo.": ";
		    	$reporte->nuevaFila($tickets['tipos'][$tipo]['totales'], "total2");
			$reporte->lineaH();

			$general_cantidad_ticket = $tickets['tipos'][$tipo]['totales']['cantidad'];
			$general_anulado_ticket = $tickets['tipos'][$tipo]['totales']['anulado'];
			$general_vbruto_ticket = $tickets['tipos'][$tipo]['totales']['vbruto'];
			$general_dscto_ticket = $tickets['tipos'][$tipo]['totales']['dscto'];
			$general_vventa_ticket = $tickets['tipos'][$tipo]['totales']['vventa'];
			$general_igv_ticket = $tickets['tipos'][$tipo]['totales']['igv'];
			$general_total_ticket = $tickets['tipos'][$tipo]['totales']['total'];

		}

		$documentos['totales']['cantidad'] = ($general_cantidad_doc + $general_cantidad_ticket);
		$documentos['totales']['anulado'] = ($general_anulado_doc + $general_anulado_ticket);
		$documentos['totales']['vbruto'] = ($general_vbruto_doc + $general_vbruto_ticket);
		$documentos['totales']['dscto'] = ($general_dscto_doc + $general_dscto_ticket);
		$documentos['totales']['vventa'] = ($general_vventa_doc + $general_vventa_ticket);
		$documentos['totales']['igv'] = ($general_igv_doc + $general_igv_ticket);
		$documentos['totales']['total'] = ($general_total_doc + $general_total_ticket);


		$reporte->lineaH();
	    	$documentos['totales']['tipo'] = "";
	    	$documentos['totales']['serie'] = "";
	    	$documentos['totales']['rango'] = "";
	    	$documentos['totales']['femision'] = "TOTAL GENERAL: ";
	    	$reporte->nuevaFila($documentos['totales'], "total2");
		$reporte->lineaH();

		$reporte->Output("/sistemaweb/ventas_clientes/reportes/pdf/RegistroVentasFESeries.pdf", "F");

		return '<script> window.open("/sistemaweb/ventas_clientes/reportes/pdf/RegistroVentasFESeries.pdf","miwin","width=750,height=550,scrollbars=yes, resizable=yes, menubar=no");</script>';

	}
	
	function reporteExcel($tickets, $documentos, $almacen, $desde, $hasta) {
		$nomalmacen = RegistroVentasFEModel::obtenerSucursales($almacen);
/*
01/01/2017

		echo '<pre>';
		var_dump($documentos);
		echo '</pre>';

		echo '<pre>';
		var_dump($tickets);
		echo '</pre>';
*/

		$workbook = new Workbook($chrFileName);
		$formato0 =& $workbook->add_format();
		$formato2 =& $workbook->add_format();
		$formato5 =& $workbook->add_format();
		$formato6 =& $workbook->add_format();
		$formato7 =& $workbook->add_format();
		$formato8 =& $workbook->add_format();
		$formato9 =& $workbook->add_format();

		$formato0->set_size(11);
		$formato0->set_bold(1);
		$formato0->set_align('left');
		$formato2->set_size(10);
		$formato2->set_bold(1);
		$formato2->set_align('center');
		$formato5->set_size(11);
		$formato5->set_align('center');
		$formato6->set_size(10);
		$formato6->set_bold(1);
		$formato6->set_align('left');
		$formato7->set_size(10);
		$formato7->set_bold(1);
		$formato7->set_align('right');
		$formato8->set_size(11);
		$formato8->set_align('right');
		$formato9->set_size(10);
		$formato9->set_bold(1);
		$formato9->set_align('center');

		$worksheet1 =& $workbook->add_worksheet('Registro Series F.E.');
		$worksheet1->set_column(0, 0, 15);
		$worksheet1->set_column(1, 1, 20);
		$worksheet1->set_column(2, 2, 22);
		$worksheet1->set_column(3, 3, 18);
		$worksheet1->set_column(4, 4, 12);
		$worksheet1->set_column(5, 5, 20);
		$worksheet1->set_column(6, 6, 15);
		$worksheet1->set_column(7, 7, 13);
		$worksheet1->set_column(8, 8, 20);
		$worksheet1->set_column(9, 9, 20);
		$worksheet1->set_column(10, 10, 20);

		$worksheet1->set_zoom(100);
		$worksheet1->set_landscape(100);

		$worksheet1->write_string(1, 0, "REGISTRO DE VENTAS SERIES FACTURACIÓN ELECTRÓNICA",$formato0);
		$worksheet1->write_string(3, 0, "ALMACEN: ".$nomalmacen[$almacen],$formato0);
		$worksheet1->write_string(4, 0, "FECHA DEL ".$desde." AL ".$hasta,$formato0);
		$worksheet1->write_string(5, 0, " ",$formato0);

		$a = 7;
		$worksheet1->write_string($a, 0, "TIPO",$formato2);
		$worksheet1->write_string($a, 1, "SERIE",$formato2);
		$worksheet1->write_string($a, 2, "RANGO",$formato2);
		$worksheet1->write_string($a, 3, "FECHA EMISION",$formato2);	
		$worksheet1->write_string($a, 4, "CANTIDAD",$formato2);
		$worksheet1->write_string($a, 5, "ANULADO",$formato2);
		$worksheet1->write_string($a, 6, "VALOR BRUTO",$formato2);
		$worksheet1->write_string($a, 7, "DESCUENTO",$formato2);
		$worksheet1->write_string($a, 8, "VALOR VENTA",$formato2);
		$worksheet1->write_string($a, 9, "I.G.V.",$formato2);
		$worksheet1->write_string($a, 10, "TOTAL",$formato2);
		
		$a = 8;

		foreach($documentos['tipos'] as $tipo => $series) {
			$worksheet1->write_string($a, 0, $tipo, $formato6);
			foreach($series['series'] as $serie => $ventas) {
				$a++;
				$worksheet1->write_string($a, 0, "SERIE ". $serie.": ", $formato6);
			    	foreach($ventas['ventas'] as $rango => $registro) {
					$a++;

					$worksheet1->write_string($a, 0, $registro['tipo'], $formato5);
					$worksheet1->write_string($a, 1, $registro['serie'], $formato5);
					$worksheet1->write_string($a, 2, $registro['rango'], $formato5);
					$worksheet1->write_string($a, 3, $registro['femision'], $formato5);
					$worksheet1->write_string($a, 4, $registro['cantidad'], $formato5);
					$worksheet1->write_string($a, 5, $registro['anulado'], $formato5);
					$worksheet1->write_number($a, 6, @RegistroVentasFETemplate::formatoNumero($registro['vbruto']), $formato8);
					$worksheet1->write_number($a, 7, @RegistroVentasFETemplate::formatoNumero($registro['dscto']), $formato8);
					$worksheet1->write_number($a, 8, @RegistroVentasFETemplate::formatoNumero($registro['vventa']), $formato8);
					$worksheet1->write_number($a, 9, @RegistroVentasFETemplate::formatoNumero($registro['igv']), $formato8);
					$worksheet1->write_number($a, 10, @RegistroVentasFETemplate::formatoNumero($registro['total']), $formato8);
				}
				$a++;

				$worksheet1->write_string($a, 3, "TOTAL SERIE ". $serie.": ", $formato7);
				$worksheet1->write_string($a, 4, $ventas['totales']['cantidad'], $formato9);
				$worksheet1->write_string($a, 5, $ventas['totales']['anulado'], $formato9);
				$worksheet1->write_number($a, 6, @RegistroVentasFETemplate::formatoNumero($ventas['totales']['vbruto']), $formato7);
				$worksheet1->write_number($a, 7, @RegistroVentasFETemplate::formatoNumero($ventas['totales']['dscto']), $formato7);
				$worksheet1->write_number($a, 8, @RegistroVentasFETemplate::formatoNumero($ventas['totales']['vventa']), $formato7);
				$worksheet1->write_number($a, 9, @RegistroVentasFETemplate::formatoNumero($ventas['totales']['igv']), $formato7);
				$worksheet1->write_number($a, 10, @RegistroVentasFETemplate::formatoNumero($ventas['totales']['total']), $formato7);
			}
			$a++;

			$worksheet1->write_string($a, 3, "TOTAL ". $tipo.": ", $formato7);
			$worksheet1->write_string($a, 4, $documentos['tipos'][$tipo]['totales']['cantidad'], $formato9);
			$worksheet1->write_string($a, 5, $documentos['tipos'][$tipo]['totales']['anulado'], $formato9);
			$worksheet1->write_number($a, 6, @RegistroVentasFETemplate::formatoNumero($documentos['tipos'][$tipo]['totales']['vbruto']), $formato7);
			$worksheet1->write_number($a, 7, @RegistroVentasFETemplate::formatoNumero($documentos['tipos'][$tipo]['totales']['dscto']), $formato7);
			$worksheet1->write_number($a, 8, @RegistroVentasFETemplate::formatoNumero($documentos['tipos'][$tipo]['totales']['vventa']), $formato7);
			$worksheet1->write_number($a, 9, @RegistroVentasFETemplate::formatoNumero($documentos['tipos'][$tipo]['totales']['igv']), $formato7);
			$worksheet1->write_number($a, 10, @RegistroVentasFETemplate::formatoNumero($documentos['tipos'][$tipo]['totales']['total']), $formato7);
			$a++;
		}

		$a++;

		$worksheet1->write_string($a, 3, "TOTAL GENERAL DOCUMENTOS: ", $formato7);
		$worksheet1->write_string($a, 4, $documentos['totales']['cantidad'], $formato9);
		$worksheet1->write_string($a, 5, $documentos['totales']['anulado'], $formato9);
		$worksheet1->write_number($a, 6, @RegistroVentasFETemplate::formatoNumero($documentos['totales']['vbruto']), $formato7);
		$worksheet1->write_number($a, 7, @RegistroVentasFETemplate::formatoNumero($documentos['totales']['dscto']), $formato7);
		$worksheet1->write_number($a, 8, @RegistroVentasFETemplate::formatoNumero($documentos['totales']['vventa']), $formato7);
		$worksheet1->write_number($a, 9, @RegistroVentasFETemplate::formatoNumero($documentos['totales']['igv']), $formato7);
		$worksheet1->write_number($a, 10, @RegistroVentasFETemplate::formatoNumero($documentos['totales']['total']), $formato7);

		$general_cantidad_doc = $documentos['totales']['cantidad'];
		$general_anulado_doc = $documentos['totales']['anulado'];
		$general_vbruto_doc = $documentos['totales']['vbruto'];
		$general_dscto_doc = $documentos['totales']['dscto'];
		$general_vventa_doc = $documentos['totales']['vventa'];
		$general_igv_doc = $documentos['totales']['igv'];
		$general_total_doc = $documentos['totales']['total'];

		$a++;

		foreach($tickets['tipos'] as $tipo => $series) {
			$worksheet1->write_string($a, 0, $tipo, $formato6);
			foreach($series['series'] as $serie => $ventas) {
				$a++;
				$worksheet1->write_string($a, 0, "SERIE ".$serie.": ", $formato6);
			    foreach($ventas['ventas'] as $rango => $registro) {
					$a++;
					$worksheet1->write_string($a, 0, $registro['tipo'], $formato5);
					$worksheet1->write_string($a, 1, $registro['serie'], $formato5);
					$worksheet1->write_string($a, 2, $registro['rango'], $formato5);
					$worksheet1->write_string($a, 3, $registro['femision'], $formato5);
					$worksheet1->write_string($a, 4, $registro['cantidad'], $formato5);
					$worksheet1->write_string($a, 5, $registro['anulado'], $formato5);
					$worksheet1->write_number($a, 6, @RegistroVentasFETemplate::formatoNumero($registro['vbruto']), $formato8);
					$worksheet1->write_number($a, 7, @RegistroVentasFETemplate::formatoNumero($registro['dscto']), $formato8);
					$worksheet1->write_number($a, 8, @RegistroVentasFETemplate::formatoNumero($registro['vventa']), $formato8);
					$worksheet1->write_number($a, 9, @RegistroVentasFETemplate::formatoNumero($registro['igv']), $formato8);
					$worksheet1->write_number($a, 10, @RegistroVentasFETemplate::formatoNumero($registro['total']), $formato8);
				}
				$a++;
				$worksheet1->write_string($a, 3, "TOTAL SERIE ". $serie.": ", $formato7);
				$worksheet1->write_string($a, 4, $ventas['totales']['cantidad'], $formato9);
				$worksheet1->write_string($a, 5, $ventas['totales']['anulado'], $formato9);
				$worksheet1->write_number($a, 6, @RegistroVentasFETemplate::formatoNumero($ventas['totales']['vbruto']), $formato7);
				$worksheet1->write_number($a, 7, @RegistroVentasFETemplate::formatoNumero($ventas['totales']['dscto']), $formato7);
				$worksheet1->write_number($a, 8, @RegistroVentasFETemplate::formatoNumero($ventas['totales']['vventa']), $formato7);
				$worksheet1->write_number($a, 9, @RegistroVentasFETemplate::formatoNumero($ventas['totales']['igv']), $formato7);
				$worksheet1->write_number($a, 10, @RegistroVentasFETemplate::formatoNumero($ventas['totales']['total']), $formato7);
			}
			$a++;
			$worksheet1->write_string($a, 3, "TOTAL ". $tipo.": ", $formato7);
			$worksheet1->write_string($a, 4, $tickets['tipos'][$tipo]['totales']['cantidad'], $formato9);
			$worksheet1->write_string($a, 5, $tickets['tipos'][$tipo]['totales']['anulado'], $formato9);
			$worksheet1->write_number($a, 6, @RegistroVentasFETemplate::formatoNumero($tickets['tipos'][$tipo]['totales']['vbruto']), $formato7);
			$worksheet1->write_number($a, 7, @RegistroVentasFETemplate::formatoNumero($tickets['tipos'][$tipo]['totales']['dscto']), $formato7);
			$worksheet1->write_number($a, 8, @RegistroVentasFETemplate::formatoNumero($tickets['tipos'][$tipo]['totales']['vventa']), $formato7);
			$worksheet1->write_number($a, 9, @RegistroVentasFETemplate::formatoNumero($tickets['tipos'][$tipo]['totales']['igv']), $formato7);
			$worksheet1->write_number($a, 10, @RegistroVentasFETemplate::formatoNumero($tickets['tipos'][$tipo]['totales']['total']), $formato7);

			$general_cantidad_ticket = $tickets['tipos'][$tipo]['totales']['cantidad'];
			$general_anulado_ticket = $tickets['tipos'][$tipo]['totales']['anulado'];
			$general_vbruto_ticket = $tickets['tipos'][$tipo]['totales']['vbruto'];
			$general_dscto_ticket = $tickets['tipos'][$tipo]['totales']['dscto'];
			$general_vventa_ticket = $tickets['tipos'][$tipo]['totales']['vventa'];
			$general_igv_ticket = $tickets['tipos'][$tipo]['totales']['igv'];
			$general_total_ticket = $tickets['tipos'][$tipo]['totales']['total'];
			$a++;
		}
		
		$a++;

		$worksheet1->write_string($a, 3, "TOTAL GENERAL: ", $formato7);
		$worksheet1->write_string($a, 4, ($general_cantidad_doc + $general_cantidad_ticket), $formato9);
		$worksheet1->write_string($a, 5, ($general_anulado_doc + $general_anulado_ticket), $formato9);
		$worksheet1->write_number($a, 6, @RegistroVentasFETemplate::formatoNumero(($general_vbruto_doc + $general_vbruto_ticket)), $formato7);
		$worksheet1->write_number($a, 7, @RegistroVentasFETemplate::formatoNumero(($general_dscto_doc + $general_dscto_ticket)), $formato7);
		$worksheet1->write_number($a, 8, @RegistroVentasFETemplate::formatoNumero(($general_vventa_doc + $general_vventa_ticket)), $formato7);
		$worksheet1->write_number($a, 9, @RegistroVentasFETemplate::formatoNumero(($general_igv_doc + $general_igv_ticket)), $formato7);
		$worksheet1->write_number($a, 10, @RegistroVentasFETemplate::formatoNumero(($general_total_doc + $general_total_ticket)), $formato7);
			
		$workbook->close();	
		$chrFileName = "RegistroVentasFESeries";
		header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename=$chrFileName.xls" );
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
		header("Pragma: public");
	}

	function formato($number) {
		return number_format($number, 2, '.', ',');
	}

	function formatoNumero($number) {
		return number_format($number, 2, '.', '');
	}

}
