<?php

class KardexActTemplate extends Template {

    function Titulo() {
        return '<div align="center"><h2><b>Kardex</b></h2></div>';
    }

    function formSearch() {
        $estaciones = KardexActModel::obtenerEstaciones();
        $hoy = date("d/m/Y");

        $form = new form2('', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control');
        $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "REPORTES.KARDEXACT"));
        $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("action", "Buscar"));

        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0">'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Almac&eacute;n</td><td>:</td><td>'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_combo("estacion", "", "TODAS", $estaciones, ''));

        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Desde</td><td>:</td><td>'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_text("desde", "", $hoy, '', 10, 12));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar(' . "'Buscar.desde'" . ');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Hasta: '));
        $form->addElement(FORM_GROUP_MAIN, new f2element_text("hasta", "", $hoy, '', 10, 12));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar(' . "'Buscar.hasta'" . ');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));

        $form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<tr><td align="left">Orden<td>:'));
        $form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<td align="left"><input type="radio" name="myorden" value="C" onClick="buscar(this.value);" checked>C&oacute;digo'));
        $form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<input type="radio" name="myorden" value="L" onClick="buscar(this.value);">L&iacute;nea</td>'));

        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td><div id="label" style="display:block;">Art&iacute;culo</td></div><td><div id="label2" style="display:block;">:</td><td>'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_text("art_desde", "", '', '', 17, 13));
        $form->addElement(FORM_GROUP_MAIN, new f2element_text("art_desde2", "", '', '', 25, 30, '', array('readonly')));

        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<img src="/sistemaweb/images/help.gif" id="imgc" width="16" height="15" onMouseOver="this.style.cursor=\'pointer\'" onclick="javascript:mostrarAyuda(\'/sistemaweb/ventas_clientes/lista_ayuda.php\',\'Buscar.art_desde\',\'Buscar.art_desde2\',\'articulos\',\'\',\'<?php echo $valor;?>\');"> '));

        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td><div id="l" style="visibility:hidden;">L&iacute;nea</td><td><div id="l2" style="visibility:hidden;">:</td><td>'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_text("art_linea", "", '', '', 17, 13, '', array('style="visibility:hidden;"')));
        $form->addElement(FORM_GROUP_MAIN, new f2element_text("art_linea2", "", '', '', 25, 30, '', array('style="visibility:hidden;"', 'readonly')));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<img src="/sistemaweb/images/help.gif" id="imgl" style="visibility:hidden;" width="16" height="15" onMouseOver="this.style.cursor=\'pointer\'" onclick="javascript:mostrarAyuda(\'/sistemaweb/ventas_clientes/lista_ayuda.php\',\'Buscar.art_linea\',\'Buscar.art_linea2\',\'lineas\',\'\',\'<?php echo $valor;?>\');"> '));

        $form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<tr><td align="left">Tipo Venta:<td>:'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="2" align="left">'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<select name="tipoventa" size="1">
										<option value="R" >Venta Resumida</option>
										<option value="D" >Venta Detallada</option>
										</select><br/>'));


        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="3" align="center">'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="3" align="center">'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="3" align="center">'));


        $form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<tr><td align="left">Tipo Reporte:<td>:'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="2" align="left">'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type="radio" name="tipo_reporte" value="CONTABLE" checked>Contable (Completo)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type="radio" name="tipo_reporte" value="FISICO">F&iacute;sico'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));


        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="3" align="center">'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="3" align="center">'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="3" align="center">'));


        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="3" align="center">'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Buscar"><img src="/sistemaweb/images/search.gif" align="right"/> Buscar</button>&nbsp;&nbsp;&nbsp;'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="PDF"><img src="/sistemaweb/images/icono_pdf.gif" align="right"/> PDF</button>'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Libro-Electronico"><img src="/sistemaweb/images/MasterDetail.gif" align="right" />Libro-Electronico</button>'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Excel"><img src="/sistemaweb/images/excel_icon.png" align="right" />Excel</button>'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));

        return $form->getForm();
    }

    function listado($resultado, $resulta, $resta, $desde, $hasta, $art_desde, $estacion, $tipo, $linea) {

        $result = '';


        foreach ($resultado['almacenes'] as $mov_almacen => $almacen) {//#FFFFFF bordercolor="#FFFFFF" FFFFFF
            $result .= '<table border="0" width="1500px">';
            if ($tipo == "CONTABLE") {
                $colspan = " colspan=\"14\" ";
                $colspan2 = " colspan=\"16\" ";
                $colspan3 = " colspan=\"8\" ";
                $colspan4 = " colspan=\"3\" ";
                $rowspan = " rowspan=\"2\" ";
            } else {
                $colspan = " colspan=\"8\" ";
                $colspan2 = " colspan=\"9\" ";
                $colspan4 = "";
                $rowspan = "";
            }
            $result .= '<tr>';
            $result .= '<td ' . $colspan2 . ' bgcolor="#368F9A" style="color:#FFFFFF;font-size:11px;"><center><font size=2><b>' . htmlentities($mov_almacen . " - " . KardexActModel::obtenerDescripcionAlmacen($mov_almacen)) . '</font></center></td>';
            $result .= '</tr>';

            $result .= '<tr>';
            $result .= '<td bgcolor="#9DD2DA" style="color:#126775;font-size:11px;"><font size=1><b>Codigo</font></td>';
            $result .= '<td colspan="3" bgcolor="#9DD2DA" style="color:#126775;font-size:11px;"><font size=1><b>Descripcion</strong></td>';
            if ($tipo == "CONTABLE") {
                $result .= '<td colspan="11" bgcolor="#9DD2DA" style="color:#126775;font-size:11px;"><font size=1><b>Unidad de Medida</strong></td>';
            } else {
                $result .= '<td colspan="6" bgcolor="#9DD2DA" style="color:#126775;font-size:11px;"><font size=1><b>Unidad de Medida</strong></td>';
            }
            $result .= '</tr>';

            $result .= '<tr>';
            $result .= '<td ' . $rowspan . ' bgcolor="#9DD2DA" style="color:#126775;font-size:11px;"><font size=1><b>Fecha</td>';
            $result .= '<td ' . $rowspan . ' bgcolor="#9DD2DA" style="color:#126775;font-size:11px;"><font size=1><b>Tipo</td>';
            $result .= '<td ' . $rowspan . ' bgcolor="#9DD2DA" style="color:#126775;font-size:11px;"><font size=1><b>Serie</td>';
            $result .= '<td ' . $rowspan . ' bgcolor="#9DD2DA" style="color:#126775;font-size:11px;"><font size=1><b>Numero</td>';
            $result .= '<td ' . $rowspan . ' bgcolor="#9DD2DA" style="color:#126775;font-size:11px;"><font size=1><b>Formulario</td>';
            $result .= '<td ' . $rowspan . ' bgcolor="#9DD2DA" style="color:#126775;font-size:11px;"><font size=1><b>Tipo de Operacion</td>';
            // ENTRADA
            if ($tipo == "CONTABLE") {
                $result .= '<td colspan="3" align="center" bgcolor="#9DD2DA" style="color:#126775;font-size:11px;"><font size=1><b>ENTRADAS</td>';
                $result .= '<td colspan="3" align="center" bgcolor="#9DD2DA" style="color:#126775;font-size:11px;"><font size=1><b>SALIDAS</td>';
                $result .= '<td colspan="3" align="center" bgcolor="#9DD2DA" style="color:#126775;font-size:11px;"><font size=1><b>SALDO FINAL</td><tr>';
            } else {
                $result .= '<td align="center" bgcolor="#9DD2DA" style="color:#126775;font-size:11px;"><font size=1><b>Entradas</td>';
                $result .= '<td align="center" bgcolor="#9DD2DA" style="color:#126775;font-size:11px;"><font size=1><b>Salidas</td>';
                $result .= '<td align="center" bgcolor="#9DD2DA" style="color:#126775;font-size:11px;"><font size=1><b>Saldo Final</td>';
            }
            if ($tipo == "CONTABLE") {
                $result .= '<td align="center" bgcolor="#9DD2DA" style="color:#126775;font-size:11px;"><font size=1><b>Cantidad</td>';
                $result .= '<td align="center" bgcolor="#9DD2DA" style="color:#126775;font-size:11px;"><font size=1><b>Costo Unitario</td>';
                $result .= '<td align="center" bgcolor="#9DD2DA" style="color:#126775;font-size:11px;"><font size=1><b>Costo Total</td>';
            }
            // SALIDA		    		    
            if ($tipo == "CONTABLE") {
                $result .= '<td align="center" bgcolor="#9DD2DA" style="color:#126775;font-size:11px;"><font size=1><b>Cantidad</td>';
                $result .= '<td align="center" bgcolor="#9DD2DA" style="color:#126775;font-size:11px;"><font size=1><b>Costo Unitario</td>';
                $result .= '<td align="center" bgcolor="#9DD2DA" style="color:#126775;font-size:11px;"><font size=1><b>Costo Total</td>';
            }
            // ACTUAL		    	
            if ($tipo == "CONTABLE") {
                $result .= '<td align="center" bgcolor="#9DD2DA" style="color:#126775;font-size:11px;"><font size=1><b>Cantidad</td>';
                $result .= '<td align="center" bgcolor="#9DD2DA" style="color:#126775;font-size:11px;"><font size=1><b>Costo Unitario</td>';
                $result .= '<td align="center" bgcolor="#9DD2DA" style="color:#126775;font-size:11px;"><font size=1><b>Costo Total</td>';
            }
            $result .= '</tr>';

            foreach ($almacen['articulos'] as $art_codigo => $articulo) {
                $result .= '<tr>'; //#E3CEF6
                $result .= '<td bgcolor="#C9E6EA">' . htmlentities($art_codigo) . '</td>';

                if ($tipo == "CONTABLE") {
                    $result .= '<td colspan="3" bgcolor="#C9E6EA">' . htmlentities(KardexActModel::obtenerDescripcion($art_codigo)) . '</td>';
                } else {
                    $result .= '<td colspan="4" bgcolor="#C9E6EA">' . htmlentities(KardexActModel::obtenerDescripcion($art_codigo)) . '</td>';
                }

		$unid = KardexActModel::unidadMedida($art_codigo);

                $result .= '<td ' . $colspan3 . ' bgcolor="#C9E6EA">' . $unid[0] . '</td>';
                $result .= '<td bgcolor="#F8F8F8" style="color:#126775;" align = "right">' . htmlentities(number_format($articulo['saldoinicial']['cant_anterior'], 4, '.', ',')) . '</td>';
                $result .= '<td bgcolor="#F8F8F8" style="color:#126775;" align = "right">' . htmlentities(number_format($articulo['saldoinicial']['unit_anterior'], 6, '.', ',')) . '</td>';

                if ($tipo == "CONTABLE")
                    $result .= '<td bgcolor="#F8F8F8" style="color:#126775;" align = "right">' . htmlentities(number_format($articulo['saldoinicial']['costo_total'], 4, '.', ',')) . '</td>';

                $result .= '</tr>';

                foreach ($articulo['movimientos'] as $i => $movimiento) {
                    $result .= '<tr>';
                    $result .= '<td bgcolor="#EBEDED">' . htmlentities($movimiento['mov_fecha']) . '</td>';
                    $result .= '<td bgcolor="#EBEDED">' . htmlentities($movimiento['tipodocu']) . '</td>';
                    $result .= '<td bgcolor="#EBEDED">' . htmlentities($movimiento['seriedocu']) . '</td>';
                    $result .= '<td bgcolor="#EBEDED">' . htmlentities($movimiento['numdocu']) . '</td>';
                    $result .= '<td bgcolor="#EBEDED">' . htmlentities($movimiento['mov_numero']) . '</td>';
                    $result .= '<td bgcolor="#EBEDED">' . htmlentities($movimiento['tran_codigo']) . '</td>';

                    if ($tipo == "CONTABLE") {
                        $result .= '<td bgcolor="#EBEDED" align = "right">' . htmlentities(number_format($movimiento['mov_cant_entrada'], 4, '.', ',')) . '</td>';
                        $result .= '<td bgcolor="#EBEDED" align = "right">' . htmlentities(number_format($movimiento['mov_unit_entrada'], 6, '.', ',')) . '</td>';
                        $result .= '<td bgcolor="#EBEDED" align = "right">' . htmlentities(number_format($movimiento['mov_cost_entrada'], 4, '.', ',')) . '</td>';
                    } else {
                        $result .= '<td bgcolor="#EBEDED" align = "right">' . htmlentities(number_format($movimiento['mov_cant_entrada'], 4, '.', ',')) . '</td>';
                    }

                    if ($tipo == "CONTABLE") {
                        $result .= '<td bgcolor="#EBEDED" align = "right">' . htmlentities(number_format($movimiento['mov_cant_salida'], 4, '.', ',')) . '</td>';
                        $result .= '<td bgcolor="#EBEDED" align = "right">' . htmlentities(number_format($movimiento['mov_unit_salida'], 6, '.', ',')) . '</td>';
                        $result .= '<td bgcolor="#EBEDED" align = "right">' . htmlentities(number_format($movimiento['mov_cost_salida'], 4, '.', ',')) . '</td>';
                    } else {
                        $result .= '<td bgcolor="#EBEDED" align = "right">' . htmlentities(number_format($movimiento['mov_cant_salida'], 4, '.', ',')) . '</td>';
                    }
                    if ($tipo == "CONTABLE") {
                        $result .= '<td bgcolor="#EBEDED" align = "right">' . htmlentities(number_format($movimiento['mov_cant_actual'], 4, '.', ',')) . '</td>';
                        $result .= '<td bgcolor="#EBEDED" align = "right">' . htmlentities(number_format($movimiento['mov_val_unit_act'], 6, '.', ',')) . '</td>';
                        $result .= '<td bgcolor="#EBEDED" align = "right">' . htmlentities(number_format($movimiento['mov_total_act'], 4, '.', ',')) . '</td>';
                    } else {
                        $result .= '<td bgcolor="#EBEDED" align = "right">' . htmlentities(number_format($movimiento['mov_cant_actual'], 4, '.', ',')) . '</td>';
                    }
                }

                $result .= '<tr>';
                $result .= '<td colspan="6" bgcolor="#FFFFFF" align="right">&nbsp;TOTAL:</td>';

                $result .= '<td bgcolor="#F8F8F8" align = "right">' . htmlentities(number_format($articulo['totales']['cant_entrada'], 4, '.', ',')) . '</td>';
                if ($tipo == "CONTABLE") {
                    $result .= '<td bgcolor="#F8F8F8">&nbsp;</td>';
                    $result .= '<td bgcolor="#F8F8F8" align = "right">' . htmlentities(number_format($articulo['totales']['cost_entrada'], 4, '.', ',')) . '</td>';
                }

                $result .= '<td bgcolor="#F8F8F8" align = "right">' . htmlentities(number_format($articulo['totales']['cant_salida'], 4, '.', ',')) . '</td>';
                if ($tipo == "CONTABLE") {
                    $result .= '<td bgcolor="#F8F8F8">&nbsp;</td>';
                    $result .= '<td bgcolor="#F8F8F8" align = "right">' . htmlentities(number_format($articulo['totales']['cost_salida'], 4, '.', ',')) . '</td>';
                }

                $result .= '<td bgcolor="#F8F8F8">&nbsp;</td>';
                if ($tipo == "CONTABLE") {
                    $result .= '<td bgcolor="#F8F8F8">&nbsp;</td>';
                    $result .= '<td bgcolor="#F8F8F8">&nbsp;</td>';
                }

                $result .= '</tr>';
            }

            $producto = "";

            /* DETALLE DE PRODUCTOS QUE NO TUVIERON MOVIMIENTOS PERO DEBEN DE MOSTRASE */


            for ($t = 0; $t < count($resulta); $t++) {
                for ($j = 0; $j < count($resta); $j++) {
                    if ($resta[$j]['codigo'] == $resulta[$t]['codigo'] and $resta[$j]['codigo'] != $resulta[$t + 1]['codigo']) {
                        $producto[$j] = $resta[$j]['codigo'];
                    }
                }
            }


            for ($j = 0; $j < count($resta); $j++) {

                if ($resta[$j]['codigo'] != $producto[$j]) {

			$cod_producto = $resta[$j]['codigo'];
                        $result .= '<tr>';
                        $result .= '<td bgcolor="#C9E6EA">' . htmlentities($cod_producto) . '</td>';
                        $result .= '<td colspan="3" bgcolor="#C9E6EA">' . htmlentities(KardexActModel::obtenerDescripcion($cod_producto)) . '</td>';

			$unid2 = KardexActModel::unidadMedida($cod_producto);

                        if ($tipo == "CONTABLE") {
                            $result .= '<td colspan="8" bgcolor="#C9E6EA">' . $unid2[0] . '</td>';
                        } else {
                            $result .= '<td colspan="2" bgcolor="#C9E6EA">' . $unid2[0] . '</td>';
                        }


                        $result .= '<td bgcolor="#F8F8F8" style="color:#126775;" align = "right">=>' . htmlentities(number_format($resta[$j]['stock'], 4, '.', ',')) . '</td>';
                        $result .= '<td bgcolor="#F8F8F8" style="color:#126775;" align = "right">' . htmlentities(number_format($resta[$j]['costo'], 4, '.', ',')) . '</td>';
                        $result .= '<td bgcolor="#F8F8F8" style="color:#126775;" align = "right">' . htmlentities(number_format($resta[$j]['total'], 4, '.', ',')) . '</td>';
                        $result .= '</tr>';

                }

            }

            /* ------------------------------------------------------------------------------------ */

            $result .= '</table>';
        }

        return $result;
    }

    function reporteExcel($resultado, $desde, $hasta, $tipo) {

        $empresa = KardexActModel::datosEmpresa();
        error_reporting(E_ALL);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);
        date_default_timezone_set('Europe/London');

        if (PHP_SAPI == 'cli')
            die('This example should only be run from a Web Browser');

        /** Include PHPExcel */
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
                ->setLastModifiedBy("OpenSysperu")
                ->setTitle("Office 2007 XLSX Test Document")
                ->setSubject("Office 2007 XLSX Test Document")
                ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
                ->setKeywords("office 2007 openxml php")
                ->setCategory("Test result file");

        // Add some data

        $cabecera = array('fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('argb' => 'FFCCFFCC')
            ),
            'borders' => array(
                'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
                'right' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM)
            )
        );


        // Miscellaneous glyphs, UTF-8
        $objPHPExcel->setActiveSheetIndex(0);
        $hoja = 0;

        if ($tipo == "CONTABLE") {
            $formato = "FORMATO 13.1";
            $titulo = "REGISTRO DE INVENTARIO PERMANENTE VALORIZADO - DETALLE DEL INVENTARIO VALORIZADO";
        } else {
            $formato = "FORMATO 12.1";
            $titulo = "REGISTRO DEL INVENTARIO PERMANENTE EN UNIDADES FISICAS - DETALLE DEL INVENTARIO PERMANENTE EN UNIDADES FISICAS";
        }

        //$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(14);
        $objPHPExcel->getActiveSheet()->freezePane('A8');

        //$objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getAlignment()->setWrapText(TRUE);

//        $reporte->definirCabecera(4, "L", "RUC: " . $empresa['ruc']);

        $objPHPExcel->setActiveSheetIndex($hoja)
                ->setCellValue('A1', $formato)
                ->setCellValue('A2', "PERIODO: ")
                ->setCellValue('B2', $desde . " AL " . $hasta)
                ->setCellValue('A3', "RUC: ")
                ->setCellValue('B3', $empresa['ruc'])
                ->setCellValue('A4', "RAZON SOCIAL: ")
                ->setCellValue('B4', $empresa['razsocial'])
                ->setCellValue('E1', $titulo)
                ->setCellValue('A5', 'TIPO :01'); //6

        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(40);
        $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(40);
        $objPHPExcel->getActiveSheet()->getStyle('E1')->getAlignment()->setWrapText(true);

        if ($tipo == "CONTABLE") {
            $met_evalucacion = "METODO DE VALUACION : ";
            $objPHPExcel->setActiveSheetIndex($hoja)
                    ->setCellValue('A6', $met_evalucacion)
                    ->setCellValue('B6', "PROMEDIO"); //9
        }

        $ini = 7;

        foreach ($resultado['almacenes'] as $mov_almacen => $almacen) {
            $establecimiento = $mov_almacen . " " . KardexActModel::obtenerDescripcionAlmacen($mov_almacen) . " " . $empresa['direccion'];

            $objPHPExcel->setActiveSheetIndex($hoja)
                    ->setCellValue('A' . $ini, 'ESTABLECIMIENTO :')
                    ->setCellValue('B' . $ini, $establecimiento);

                    $ini++;

		if ($tipo == "CONTABLE") {

                    $ini++;

                    $objPHPExcel->getActiveSheet()->getRowDimension($ini)->setRowHeight(30);
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $ini . ':O' . $ini)->applyFromArray($cabecera);
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $ini . ':O' . $ini)->getFont()->setBold(true);
                    $objPHPExcel->setActiveSheetIndex($hoja)
                            ->setCellValue('A' . $ini, 'FECHA')//10
                            ->setCellValue('B' . $ini, 'TIPO')
                            ->setCellValue('C' . $ini, 'SERIE')
                            ->setCellValue('D' . $ini, 'NÚMERO')
                            ->setCellValue('E' . $ini, ' FORMULARIO')
                            ->setCellValue('F' . $ini, ' TIPO DE  OPERACIÓN')
                            ->setCellValue('G' . $ini, 'CANTIDAD')
                            ->setCellValue('H' . $ini, 'COSTO UNITARIO')
                            ->setCellValue('I' . $ini, 'COSTO TOTAL')
                            ->setCellValue('J' . $ini, 'CANTIDAD')
                            ->setCellValue('K' . $ini, 'COSTO UNITARIO')
                            ->setCellValue('L' . $ini, 'COSTO TOTAL')
                            ->setCellValue('M' . $ini, 'CANTIDAD')
                            ->setCellValue('N' . $ini, 'COSTO UNITARIO')
                            ->setCellValue('O' . $ini, 'COSTO TOTAL');

                    $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setAutoSize(true);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setAutoSize(true);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setAutoSize(true);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setAutoSize(true);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setAutoSize(true);
                } else {

                    $ini++;

                    $objPHPExcel->getActiveSheet()->getRowDimension($ini)->setRowHeight(30);
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $ini . ':I' . $ini)->applyFromArray($cabecera);
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $ini . ':I' . $ini)->getFont()->setBold(true);
                    $objPHPExcel->setActiveSheetIndex($hoja)
                            ->setCellValue('A' . $ini, 'FECHA')//10
                            ->setCellValue('B' . $ini, 'TIPO')
                            ->setCellValue('C' . $ini, 'SERIE')
                            ->setCellValue('D' . $ini, 'NÚMERO')
                            ->setCellValue('E' . $ini, ' FORMULARIO')
                            ->setCellValue('F' . $ini, ' TIPO DE  OPERACIÓN')
                            ->setCellValue('G' . $ini, 'ENTRADA')
                            ->setCellValue('H' . $ini, 'SALIDA')
                            ->setCellValue('I' . $ini, ' SALDO  FINAL');
                    $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
               }

                $ini++;

            foreach ($almacen['articulos'] as $art_codigo => $articulo) {

                $cod_exi = $art_codigo;
                $descrip 	= KardexActModel::obtenerDescripcion($art_codigo);
                $codunidad	= KardexActModel::unidadMedidaExcel($art_codigo);

                $ini++;

                $objPHPExcel->setActiveSheetIndex($hoja)
                        ->setCellValue('A' . $ini, "CODIGO DE  LA EXISTENCIA: ")//5
                        ->setCellValue('B' . $ini, $cod_exi);

                $ini++;

                $objPHPExcel->setActiveSheetIndex($hoja)
                        ->setCellValue('A' . $ini, "DESCRIPCION: ")
                        ->setCellValue('B' . $ini, $descrip);

                $ini++;

                $objPHPExcel->setActiveSheetIndex($hoja)
                        ->setCellValue('A' . $ini, "CODIGO DE LA UNIDAD DE MEDIDA: ");

              	$objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(1, $ini, $codunidad, PHPExcel_Cell_DataType::TYPE_STRING);//COLUMNA F

                $ini++;

		if ($tipo == "CONTABLE") {

			$cant_inicial = (!empty($articulo['saldoinicial']['cant_anterior'])) ? (float) $articulo['saldoinicial']['cant_anterior'] : 0;
			$unit_inicial = (!empty($articulo['saldoinicial']['unit_anterior'])) ? (float) $articulo['saldoinicial']['unit_anterior'] : 0;
			$costo_inicial = (!empty($articulo['saldoinicial']['costo_total'])) ? (float) $articulo['saldoinicial']['costo_total'] : 0;

		        $objPHPExcel->setActiveSheetIndex($hoja)
		                ->setCellValue('M' . $ini, $cant_inicial)
		                ->setCellValue('N' . $ini, $unit_inicial)
		                ->setCellValue('O' . $ini, $costo_inicial);

		} else {

			$cant_inicial = (!empty($articulo['saldoinicial']['cant_anterior'])) ? (float) $articulo['saldoinicial']['cant_anterior'] : 0;
			$unit_inicial = (!empty($articulo['saldoinicial']['unit_anterior'])) ? (float) $articulo['saldoinicial']['unit_anterior'] : 0;

		        $objPHPExcel->setActiveSheetIndex($hoja)
		                ->setCellValue('M' . $ini, $cant_inicial)
		                ->setCellValue('N' . $ini, $unit_inicial);

                }

                $ini = $ini + 1;

                foreach ($articulo['movimientos'] as $i => $movimiento) {

                    if ($tipo == "CONTABLE") {
                        $mov_numero = (!empty($movimiento['mov_numero'])) ? $movimiento['mov_numero'] : '--------';
                        $mov_cant_entrada = (!empty($movimiento['mov_cant_entrada'])) ? (float) $movimiento['mov_cant_entrada'] : 0;
                        $mov_unit_entrada = (!empty($movimiento['mov_unit_entrada'])) ? (float) $movimiento['mov_unit_entrada'] : 0;
                        $mov_cost_entrada = (!empty($movimiento['mov_cost_entrada'])) ? (float) $movimiento['mov_cost_entrada'] : 0;
                        $mov_cant_salida = (!empty($movimiento['mov_cant_salida'])) ? (float) $movimiento['mov_cant_salida'] : 0;
                        $mov_unit_salida = (!empty($movimiento['mov_unit_salida'])) ? (float) $movimiento['mov_unit_salida'] : 0;
                        $mov_cost_salida = (!empty($movimiento['mov_cost_salida'])) ? (float) $movimiento['mov_cost_salida'] : 0;
                        $mov_cant_actual = (!empty($movimiento['mov_cant_actual'])) ? (float) $movimiento['mov_cant_actual'] : 0;
                        $mov_val_unit_act = (!empty($movimiento['mov_val_unit_act'])) ? (float) $movimiento['mov_val_unit_act'] : 0;
                        $mov_total_act = (!empty($movimiento['mov_total_act'])) ? (float) $movimiento['mov_total_act'] : 0;

                        $objPHPExcel->setActiveSheetIndex($hoja)
                                ->setCellValue('A' . $ini, substr($movimiento['mov_fecha'], 0, 10));

                        $tipodocu = (empty($movimiento['tipodocu'])) ? '  ' : (string) $movimiento['tipodocu'];
                        $seriedocu = (empty($movimiento['seriedocu'])) ? '  ' : (string) $movimiento['seriedocu'];
                        $numdocu = (empty($movimiento['numdocu'])) ? '  ' : (string) $movimiento['numdocu'];

                        $objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(1, $ini, $tipodocu, PHPExcel_Cell_DataType::TYPE_STRING);
                        $objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(2, $ini, $seriedocu, PHPExcel_Cell_DataType::TYPE_STRING);
                        $objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(3, $ini, $numdocu, PHPExcel_Cell_DataType::TYPE_STRING);
                        $objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(4, $ini, $mov_numero, PHPExcel_Cell_DataType::TYPE_STRING);

                        $objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('F' . $ini, $movimiento['tran_codigo'])
                                ->setCellValue('G' . $ini, $mov_cant_entrada)
                                ->setCellValue('H' . $ini, $mov_unit_entrada)
                                ->setCellValue('I' . $ini, $mov_cost_entrada)
                                ->setCellValue('J' . $ini, $mov_cant_salida)
                                ->setCellValue('K' . $ini, $mov_unit_salida)
                                ->setCellValue('L' . $ini, $mov_cost_salida)
                                ->setCellValue('M' . $ini, $mov_cant_actual)
                                ->setCellValue('N' . $ini, $mov_val_unit_act)
                                ->setCellValue('O' . $ini, $mov_total_act);
                    } else {

                        $mov_numero = (!empty($movimiento['mov_numero'])) ? $movimiento['mov_numero'] : '--------';
                        $mov_cant_entrada = (!empty($movimiento['mov_cant_entrada'])) ? (float) $movimiento['mov_cant_entrada'] : 0;
                        $mov_cant_salida = (!empty($movimiento['mov_cant_salida'])) ? (float) $movimiento['mov_cant_salida'] : 0;
                        $mov_cant_actual = (!empty($movimiento['mov_cant_actual'])) ? (float) $movimiento['mov_cant_actual'] : 0;

                        $objPHPExcel->setActiveSheetIndex($hoja)
                                ->setCellValue('A' . $ini, substr($movimiento['mov_fecha'], 0, 10))
                                ->setCellValue('B' . $ini, (empty($movimiento['tipodocu'])) ? '  ' : (string) $movimiento['tipodocu'])
                                ->setCellValue('C' . $ini, (empty($movimiento['seriedocu'])) ? '  ' : $movimiento['seriedocu'])
                                ->setCellValue('D' . $ini, (empty($movimiento['numdocu'])) ? '  ' : $movimiento['numdocu'])
                                ->setCellValue('E' . $ini, $mov_numero)
                                ->setCellValue('F' . $ini, $movimiento['tran_codigo'])
                                ->setCellValue('G' . $ini, $mov_cant_entrada)
                                ->setCellValue('H' . $ini, $mov_cant_salida)
                                ->setCellValue('I' . $ini, $mov_cant_actual);
                    }

                    $ini++;
                }

                //TOTALES 
                if ($tipo == "CONTABLE") {

                    $cant_entrada = (empty($articulo['totales']['cant_entrada'])) ? 0 : $articulo['totales']['cant_entrada'];
                    $cost_entrada = (empty($articulo['totales']['cost_entrada'])) ? 0 : $articulo['totales']['cost_entrada'];
                    $cant_salida = (empty($articulo['totales']['cant_salida'])) ? 0 : $articulo['totales']['cant_salida'];
                    $cost_salida = (empty($articulo['totales']['cost_salida'])) ? 0 : $articulo['totales']['cost_salida'];

                    $objPHPExcel->setActiveSheetIndex($hoja)
                            ->setCellValue('G' . $ini, $cant_entrada);

                    $objPHPExcel->setActiveSheetIndex($hoja)
                            ->setCellValue('I' . $ini, $cost_entrada);

                    $objPHPExcel->setActiveSheetIndex($hoja)
                            ->setCellValue('J' . $ini, $cant_salida);

                    $objPHPExcel->setActiveSheetIndex($hoja)
                            ->setCellValue('L' . $ini, $cost_salida);

                    $objPHPExcel->getActiveSheet()->getStyle('G' . $ini . ':L' . $ini)->getFont()->setBold(true);
                } else {

                    $cant_entrada = (empty($articulo['totales']['cant_entrada'])) ? 0 : $articulo['totales']['cant_entrada'];
                    $cant_salida = (empty($articulo['totales']['cant_salida'])) ? 0 : $articulo['totales']['cant_salida'];
                    $cantidad_total = (empty($articulo['totales']['cantidad_total'])) ? 0 : $articulo['totales']['cantidad_total'];

                    $objPHPExcel->setActiveSheetIndex($hoja)
                            ->setCellValue('G' . $ini, $cant_entrada);

                    $objPHPExcel->setActiveSheetIndex($hoja)
                            ->setCellValue('H' . $ini, $cant_salida);

                    $objPHPExcel->setActiveSheetIndex($hoja)
                            ->setCellValue('I' . $ini, $cantidad_total);

                    $objPHPExcel->getActiveSheet()->getStyle('G' . $ini . ':I' . $ini)->getFont()->setBold(true);
                }

                $ini++;
            }

            $hoja++;
        }

        //Rename worksheet
        $objPHPExcel->getActiveSheet()->setTitle('Excel');

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);

        //Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);
        $name_kar = "";

        if ($tipo == "CONTABLE") {
            $name_kar = "Inv.Per_valorizado.xls";
        } else {
            $name_kar = "Inv.Per_fisico.xls";
        }


        //Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $name_kar . '"');
        header('Cache-Control: max-age=0');

        //If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        //If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    function reportePDF($resultado, $resulta, $resta, $desde, $hasta, $tipo) {

        $empresa = KardexActModel::datosEmpresa();

        $cabecera2 = Array(
            "mov_fecha" => "FECHA",
            "tipodocu" => "TIPO",
            "seriedocu" => "SERIE",
            "numdocu" => "NUMERO",
            "mov_numero" => "FORMULARIO ",
            "tran_codigo" => "TIP.OPER.",
            "mov_cant_entrada" => "CANTIDAD",
            "mov_unit_entrada" => "COSTO UNI.",
            "mov_cost_entrada" => "COSTO TOTAL",
            "mov_cant_salida" => "CANTIDAD",
            "mov_unit_salida" => "COSTO UNI.",
            "mov_cost_salida" => "COSTO TOTAL",
            "mov_cant_actual" => "CANTIDAD",
            "mov_val_unit_act" => "COSTO UNI.",
            "mov_total_act" => "COSTO TOTAL"
        );

        if ($tipo == "CONTABLE") {
            $cabecera1 = Array(
                "entrada_izq" => "",
                "entrada_med" => "ENTRADAS",
                "salida_med" => "SALIDAS",
                "final_med" => "SALDO FINAL",
                "saldo_ini" => "",
                "saldo_cant" => "",
                "saldo_costo" => ""
            );
        } else {
            $cabecera1 = Array(
                "entrada_izq" => "",
                "entrada_med" => "ENTRADAS",
                "salida_med" => "SALIDAS",
                "final_med" => "SALDO FINAL"
            );
        }

        $fontsize = 7.5;
        $reporte = new CReportes2("L");

        $CabCli = array("CODPRO_V" => " ");
        $CabCli2 = array("NOMPRO_V" => " ");
        $CabCli3 = array("UNIPRO_V" => " ");

        if ($tipo == "CONTABLE") {
            $reporte->definirColumna("mov_fecha", $reporte->TIPO_TEXTO, 10, "L");
            $reporte->definirColumna("tipodocu", $reporte->TIPO_TEXTO, 4, "C");
            $reporte->definirColumna("seriedocu", $reporte->TIPO_TEXTO, 5, "C");
            $reporte->definirColumna("numdocu", $reporte->TIPO_TEXTO, 10, "L");
            $reporte->definirColumna("mov_numero", $reporte->TIPO_TEXTO, 10, "L");
            $reporte->definirColumna("tran_codigo", $reporte->TIPO_TEXTO, 18, "L");
            $reporte->definirColumna("tran_codigo", $reporte->TIPO_TEXTO, 24, "C");
            $reporte->definirColumna("mov_cant_entrada", $reporte->TIPO_TEXTO, 12, "C");
            $reporte->definirColumna("mov_unit_entrada", $reporte->TIPO_TEXTO, 11, "R");
            $reporte->definirColumna("mov_cost_entrada", $reporte->TIPO_COSTO, 12, "R");
            $reporte->definirColumna("mov_cant_salida", $reporte->TIPO_TEXTO, 12, "R");
            $reporte->definirColumna("mov_unit_salida", $reporte->TIPO_COSTO, 11, "R");
            $reporte->definirColumna("mov_cost_salida", $reporte->TIPO_COSTO, 12, "R");
            $reporte->definirColumna("mov_cant_actual", $reporte->TIPO_COSTO, 13, "R");
            $reporte->definirColumna("mov_val_unit_act", $reporte->TIPO_COSTO, 11, "R");
            $reporte->definirColumna("mov_total_act", $reporte->TIPO_COSTO, 14, "R");

            $reporte->definirColumna("CODPRO", $reporte->TIPO_TEXTO, 30, "L", "PRO");
            $reporte->definirColumna("CODPRO_V", $reporte->TIPO_TEXTO, 15, "L", "PRO");

            /* SIN MOVIMIENTOS */

            $reporte->definirColumna("NOMPRO", $reporte->TIPO_TEXTO, 15, "L", "PRO");
            $reporte->definirColumna("NOMPRO_V", $reporte->TIPO_TEXTO, 80, "L", "PRO");

            $reporte->definirColumna("STOCK", $reporte->TIPO_TEXTO, 0, "L", "PRO");
            $reporte->definirColumna("STOCK_V", $reporte->TIPO_TEXTO, 12, "L", "PRO");

            $reporte->definirColumna("COSTO", $reporte->TIPO_TEXTO, 0, "L", "PRO");
            $reporte->definirColumna("COSTO_V", $reporte->TIPO_TEXTO, 12, "L", "PRO");

            $reporte->definirColumna("TOTAL", $reporte->TIPO_TEXTO, 0, "L", "PRO");
            $reporte->definirColumna("TOTAL_V", $reporte->TIPO_TEXTO, 12, "L", "PRO");

            /* --------------- */

            $reporte->definirColumna("NOMPRO", $reporte->TIPO_TEXTO, 30, "L", "NPRO");
            $reporte->definirColumna("NOMPRO_V", $reporte->TIPO_TEXTO, 80, "L", "NPRO");

            $reporte->definirColumna("UNIPRO", $reporte->TIPO_TEXTO, 30, "L", "UPRO");
            $reporte->definirColumna("UNIPRO_V", $reporte->TIPO_TEXTO, 15, "L", "UPRO");

            $reporte->definirColumna("entrada_izq", $reporte->TIPO_TEXTO, 66, "C", "_saldoinicial2");
            $reporte->definirColumna("entrada_med", $reporte->TIPO_TEXTO, 40, "C", "_saldoinicial2");
            $reporte->definirColumna("salida_med", $reporte->TIPO_TEXTO, 45, "C", "_saldoinicial2");
            $reporte->definirColumna("final_med", $reporte->TIPO_TEXTO, 20, "C", "_saldoinicial2");

            $reporte->definirColumna("saldo_ini", $reporte->TIPO_TEXTO, 135, "C", "_saldoinicial3");
            $reporte->definirColumna("saldo_cant", $reporte->TIPO_COSTO, 35, "C", "_saldoinicial3");
            $reporte->definirColumna("saldo_costo", $reporte->TIPO_COSTO, 15, "C", "_saldoinicial3");

            $reporte->definirColumna("dummy1", $reporte->TIPO_TEXTO, 60, "R", "_totales");
            $reporte->definirColumna("cant_entrada", $reporte->TIPO_TEXTO, 25, "C", "_totales");
            $reporte->definirColumna("cost_entrada", $reporte->TIPO_COSTO, 20, "R", "_totales");
            $reporte->definirColumna("cant_salida", $reporte->TIPO_TEXTO, 14, "C", "_totales");
            $reporte->definirColumna("cost_salida", $reporte->TIPO_TEXTO, 37, "C", "_totales");
        } else {

            $reporte->definirColumna("CODPRO", $reporte->TIPO_TEXTO, 30, "L", "PRO");
            $reporte->definirColumna("CODPRO_V", $reporte->TIPO_TEXTO, 15, "L", "PRO");

            /* SIN MOVIMIENTOS */

            $reporte->definirColumna("NOMPRO", $reporte->TIPO_TEXTO, 15, "L", "PRO");
            $reporte->definirColumna("NOMPRO_V", $reporte->TIPO_TEXTO, 80, "L", "PRO");

            $reporte->definirColumna("STOCK", $reporte->TIPO_TEXTO, 0, "L", "PRO");
            $reporte->definirColumna("STOCK_V", $reporte->TIPO_TEXTO, 12, "L", "PRO");

            $reporte->definirColumna("COSTO", $reporte->TIPO_TEXTO, 0, "L", "PRO");
            $reporte->definirColumna("COSTO_V", $reporte->TIPO_TEXTO, 12, "L", "PRO");

            $reporte->definirColumna("TOTAL", $reporte->TIPO_TEXTO, 0, "L", "PRO");
            $reporte->definirColumna("TOTAL_V", $reporte->TIPO_TEXTO, 12, "L", "PRO");

            /* --------------- */

            $reporte->definirColumna("NOMPRO", $reporte->TIPO_TEXTO, 30, "L", "NPRO");
            $reporte->definirColumna("NOMPRO_V", $reporte->TIPO_TEXTO, 80, "L", "NPRO");

            $reporte->definirColumna("UNIPRO", $reporte->TIPO_TEXTO, 30, "L", "UPRO");
            $reporte->definirColumna("UNIPRO_V", $reporte->TIPO_TEXTO, 15, "L", "UPRO");

            $reporte->definirColumna("mov_fecha", $reporte->TIPO_TEXTO, 10, "L");
            $reporte->definirColumna("tipodocu", $reporte->TIPO_TEXTO, 4, "C");
            $reporte->definirColumna("seriedocu", $reporte->TIPO_TEXTO, 5, "C");
            $reporte->definirColumna("numdocu", $reporte->TIPO_TEXTO, 10, "L");
            $reporte->definirColumna("mov_numero", $reporte->TIPO_TEXTO, 10, "L");
            $reporte->definirColumna("tran_codigo", $reporte->TIPO_TEXTO, 24, "C");
            $reporte->definirColumna("mov_cant_entrada", $reporte->TIPO_CANTIDAD, 12, "R");
            $reporte->definirColumna("mov_cant_salida", $reporte->TIPO_CANTIDAD, 12, "R");
            $reporte->definirColumna("mov_cant_actual", $reporte->TIPO_CANTIDAD, 12, "R");

            $reporte->definirColumna("entrada_izq", $reporte->TIPO_TEXTO, 66, "C", "_saldoinicial2");
            $reporte->definirColumna("entrada_med", $reporte->TIPO_TEXTO, 20, "C", "_saldoinicial2");
            $reporte->definirColumna("salida_med", $reporte->TIPO_TEXTO, 6, "C", "_saldoinicial2");
            $reporte->definirColumna("final_med", $reporte->TIPO_TEXTO, 15, "C", "_saldoinicial2");

            $reporte->definirColumna("saldo_cant", $reporte->TIPO_COSTO, 206, "C", "_saldoinicial3");

            $reporte->definirColumna("dummy1", $reporte->TIPO_TEXTO, 62, "R", "_totales");
            $reporte->definirColumna("cant_entrada", $reporte->TIPO_CANTIDAD, 18, "R", "_totales");
            $reporte->definirColumna("cant_salida", $reporte->TIPO_CANTIDAD, 12, "R", "_totales");
            $reporte->definirColumna("cantidad_total", $reporte->TIPO_CANTIDAD, 12, "R", "_totales");
        }

        if ($tipo == "CONTABLE") {
            $formato = "FORMATO 13.1";
            $titulo = "REGISTRO DE INVENTARIO PERMANENTE VALORIZADO - DETALLE DEL INVENTARIO VALORIZADO";
        } else {
            $formato = "FORMATO 12.1";
            $titulo = "REGISTRO DEL INVENTARIO PERMANENTE EN UNIDADES FISICAS - DETALLE DEL INVENTARIO PERMANENTE EN UNIDADES FISICAS";
        }

        $reporte->SetFont("courier", "", $fontsize);
        $reporte->SetMargins(0, 0, 0);

        $formularios = KardexActModel::ObtenerTiposFormularios();

        $reporte->definirCabecera(1, "L", $formato);
        $reporte->definirCabecera(1, "R", "PAG.%p");
        $reporte->definirCabecera(2, "C", $titulo);

        $reporte->definirCabecera(3, "L", "PERIODO: " . $desde . " AL " . $hasta);
        $reporte->definirCabecera(4, "L", "RUC: " . $empresa['ruc']);
        $reporte->definirCabecera(5, "L", "RAZON SOCIAL: " . $empresa['razsocial']);
        $reporte->definirCabecera(6, "L", "ESTABLECIMIENTO: " . $mov_almacen . " " . KardexActModel::obtenerDescripcionAlmacen($mov_almacen) . " " . $empresa['direccion']);
        $reporte->definirCabecera(8, "L", "TIPO: 01");

        if ($tipo == "CONTABLE")
            $reporte->definirCabecera(9, "L", "METODO DEVALUACION: PROMEDIO");

        $reporte->definirCabeceraPredeterminada($cabecera1, "_saldoinicial2");
        $reporte->definirCabeceraPredeterminada($cabecera2);

        foreach ($resultado['almacenes'] as $mov_almacen => $almacen) {
            $reporte->definirCabecera(6, "L", "ESTABLECIMIENTO: " . $mov_almacen . " " . KardexActModel::obtenerDescripcionAlmacen($mov_almacen) . " " . $empresa['direccion']);
            $reporte->AddPage();

            foreach ($almacen['articulos'] as $art_codigo => $articulo) {

                $cod_producto = $art_codigo;
                $des_producto = KardexActModel::obtenerDescripcion($art_codigo);
                $uni_producto = KardexActModel::unidadMedida($art_codigo);

                $arr = array("CODPRO" => "CODIGO DE LA EXISTENCIA: ", "CODPRO_V" => $cod_producto);
                $reporte->nuevaFila($arr, "PRO");
                $arr = array("NOMPRO" => "DESCRIPCION: ", "NOMPRO_V" => $des_producto);
                $reporte->nuevaFila($arr, "NPRO");
                $arr = array("UNIPRO" => "CODIGO DE LA UNIDAD DE MEDIDA: ", "UNIPRO_V" => $uni_producto[0]);
                $reporte->nuevaFila($arr, "UPRO");

                if ($tipo == "CONTABLE") {
                    $arr = array("saldo_ini" => "", "saldo_cant" => $articulo['saldoinicial']['cant_anterior'], "saldo_costo" => $articulo['saldoinicial']['costo_total']);
                } else {
                    $arr = array("saldo_cant" => $articulo['saldoinicial']['cant_anterior']);
                }

                $reporte->nuevaFila($arr, "_saldoinicial3");

                $reporte->lineaH();

                foreach ($articulo['movimientos'] as $i => $movimiento) {
                    $reporte->nuevaFila($movimiento);
                }

                $reporte->lineaH();
                $reporte->nuevaFila($articulo['totales'], "_totales");
                $reporte->lineaH();
                $reporte->nuevaFila("                              ");
                $reporte->nuevaFila("                              ");
            }

            $producto = "";

            for ($t = 0; $t < count($resulta); $t++) {
                for ($j = 0; $j < count($resta); $j++) {
                    if ($resta[$j]['codigo'] == $resulta[$t]['codigo'] and $resta[$j]['codigo'] != $resulta[$t + 1]['codigo']) {
                        $producto[$j] = $resta[$j]['codigo'];
                    }
                }
            }

            for ($j = 0; $j < count($resta); $j++) {

                if ($resta[$j]['codigo'] != $producto[$j]) {

                    if ($resta[$j]['stock'] > 0) {

                        $cod_producto = $resta[$j]['codigo'];
                        $des_producto = KardexActModel::obtenerDescripcion($cod_producto);
                        $stock = $resta[$j]['stock'];
                        $costo = $resta[$j]['costo'];
                        $total = $resta[$j]['total'];

                        $arr = array("CODPRO" => "CODIGO DE LA EXISTENCIA: ", "CODPRO_V" => $cod_producto, "NOMPRO" => "DESCRIPCION: ", "NOMPRO_V" => $des_producto, "STOCK" => "", "STOCK_V" => $stock, "COSTO" => "", "COSTO_V" => $costo, "TOTAL" => "", "TOTAL_V" => $total);
                        $reporte->nuevaFila($arr, "PRO");
                    }
                }
            }
        }

        $reporte->Output("/sistemaweb/ventas_clientes/reportes/pdf/Kardex.pdf", "F");
        return '<script> window.open("/sistemaweb/ventas_clientes/reportes/pdf/Kardex.pdf","miwin","width=750,height=550,scrollbars=yes, resizable=yes, menubar=no");</script>';
    }

}

