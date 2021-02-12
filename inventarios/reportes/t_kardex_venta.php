<?php

class KardexVentaActTemplate extends Template {

    function Titulo() {
        return '<h2 align="center" style="color:#336699;"><b>Venta-Rotacion-Margen</b></h2>';
    }

    function formSearch() {
        $estaciones = KardexVentaActModel::obtenerEstaciones();
        $ano = date("Y");
        $mes = date("m");
        $acciones = array("Normal" => "Normal", "Agrupado" => "Agrupado");
        $form = new form2('', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control');
        $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "REPORTES.KARDEXVENTA"));
        $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("action", "Buscar"));

        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0">'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Almac&eacute;n</td><td>:</td><td>'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_combo("estacion", "", "TODAS", $estaciones, ''));

        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Ano</td><td>:</td><td>'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_text("ano", "", $ano, '', 4, 5));

        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Mes: '));
        $form->addElement(FORM_GROUP_MAIN, new f2element_text("mes", "", $mes, '', 2, 4));

        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Accion</td><td>:</td><td>'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_combo("accion", "", "Normal", $acciones, ''));




        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="3" align="center">'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Buscar"><img src="/sistemaweb/images/search.gif" alt="left"/> Buscar</button>&nbsp;&nbsp;&nbsp;'));
        // $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="PDF"><img src="/sistemaweb/images/icono_pdf.gif" alt="left"/> PDF</button>'));
        // $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Excel"> Excel</button>'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));

        return $form->getForm();
    }

    function listado($accion, $listado_productos, $stock_inicial, $ingreso_inventario, $ventas_inventario, $saldos_final, $linea, $solo_venta_producto, $cantidadDiasMes) {

        $result = '';

        foreach ($listado_productos as $key_alm => $value_linea) {
            $result .= '<table border="0" width="1500px">';
            $result .= '<tr>';
            $result .= '<td colspan=2 bgcolor="#336699"  style="color:#FFFFFF;font-size:11px;"><center><font size=1><b>ALMACEN</font></center></td>';
            $result .= '<td  colspan=1 bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><center><font size=1><b>' . $key_alm . '</font></center></td>';
            $result .= '</tr>';




            foreach ($value_linea as $key_linea => $value_articulo) {
                $total_stock_inicial = 0.0;
                $total_stock_costototal = 0.0;
                $result .= '<tr>';
                $result .= '<td colspan=2 bgcolor="#66A3E0"  style="color:#FFFFFF;font-size:11px;"><center><font size=1><b>LINEA</font></center></td>';
                $result .= '<td  bgcolor="#66A3E0" style="color:#FFFFFF;font-size:11px;"><center><font size=1><b>' . $key_linea . ':' . $linea[$key_linea]['tab_descripcion'] . '</font></center></td>';
                $result .= '</tr>';

                $result .= '<tr>';
                $result .= '<td colspan=3 bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"></td>';
                $result .= '<td colspan=3 bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><font size=1><b>SALDO INICIAL</font></td>';
                $result .= '<td colspan=3 bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><font size=1><b>ENTRADO</font></td>';
                $result .= '<td colspan=3 bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><font size=1><b>SALIDA</font></td>';
                $result .= '<td colspan=3 bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><font size=1><b>SALDO FINAL</font></td>';
                $result .= '<td colspan=5 bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><font size=1><b>-</font></td>';

                $result .= '</tr>';
                $result .= '<tr>';
                $result .= '<td bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><font size=1><b>CODIGO EXISTENCIA</font></td>';
                $result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><center><font size=1><b>UNID MEDIDA</font></center></td>';
                $result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><font size=1><b>DESCRIPCION</strong></td>';
                $result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><font size=1><b>CANTIDAD</strong></td>';
                $result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><font size=1><b>COSTO UNITARIO</strong></td>';
                $result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><font size=1><b>COSTO TOTAL</strong></td>';
                $result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><font size=1><b>CANTIDAD </strong></td>';
                $result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><font size=1><b>COSTO UNITARIO </strong></td>';
                $result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><font size=1><b>COSTO TOTAL </strong></td>';
                $result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><font size=1><b>CANTIDAD </strong></td>';
                $result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><font size=1><b>COSTO UNITARIO </strong></td>';
                $result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><font size=1><b>COSTO TOTAL </strong></td>';
                $result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><font size=1><b>CANTIDAD </strong></td>';
                $result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><font size=1><b>COSTO UNITARIO </strong></td>';
                $result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><font size=1><b>COSTO TOTAL </strong></td>';
                $result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><font size=1><b>CANTIDAD VENTA. </strong></td>';
                $result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><font size=1><b>PRECIO VENTA. </strong></td>';
                $result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><font size=1><b>IMPORTE S/. </strong></td>';
                $result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><font size=1><b>ROTACION PRODUCTO. </strong></td>';
                $result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><font size=1><b>MARGEN. </strong></td>';


                $result .= '</tr>';
                $total_stock_entrada = 0.00;
                $total_stock_entradacostototal = 0.00;
                $total_stock_salida_venta = 0.00;
                $total_stock_salidacostototal = 0.00;
                $total_stock_final = 0.00;
                $total_stock_finalcostototal = 0.00;
                $total_cantidad_vendido = 0.00;
                $total_importe_vendido = 0.00;




                foreach ($value_articulo as $key_articulo => $value) {
                    if ($accion == "Normal") {
                        $result .= '<tr>';
                        $result .= '<td bgcolor="#FFFFFF"  align="right"><b>' . $value['cod'] . '</strong></td>';
                        $result .= '<td  bgcolor="#FFFFFF" align="right"><b>' . $value['unidades'] . '</strong></td>';
                        $result .= '<td  bgcolor="#FFFFFF" align="right"><b>' . $value['desc'] . '</strong></td>';

                        $result .= '<td  bgcolor="#FFFFFF" align="right"><font size=1><b>' . number_format($stock_inicial['st_inicial'][$key_alm][$value['cod']]['stk_stock'], 2, '.', ',') . '</strong></td>';
                        $result .= '<td  bgcolor="#FFFFFF" align="right"><font size=1><b>' . number_format($stock_inicial['st_inicial'][$key_alm][$value['cod']]['stk_costounitario'], 2, '.', ',') . '</strong></td>';
                        $result .= '<td  bgcolor="#C4C4C4" align="right"><font size=1><b>' . number_format($stock_inicial['st_inicial'][$key_alm][$value['cod']]['stk_costototal'], 2, '.', ',') . '</strong></td>';

                        $result .= '<td  bgcolor="#FFFFFF" align="right"><font size=1><b>' . number_format($ingreso_inventario[$key_alm][$value['cod']]['mov_cantidad'], 2, '.', ',') . '</strong></td>';
                        $result .= '<td  bgcolor="#FFFFFF" align="right"><font size=1><b>' . number_format($ingreso_inventario[$key_alm][$value['cod']]['mov_costounitario'], 2, '.', ',') . '</strong></td>';
                        $result .= '<td  bgcolor="#C4C4C4" align="right"><font size=1><b>' . number_format($ingreso_inventario[$key_alm][$value['cod']]['mov_costototal'], 2, '.', ',') . '</strong></td>';


                        $result .= '<td  bgcolor="#FFFFFF" align="right"><font size=1><b>' . number_format($ventas_inventario[$key_alm][$value['cod']]['mov_cantidad'], 2, '.', ',') . '</strong></td>';
                        $result .= '<td  bgcolor="#FFFFFF" align="right"><font size=1><b>' . number_format($ventas_inventario[$key_alm][$value['cod']]['mov_costounitario'], 2, '.', ',') . '</strong></td>';
                        $result .= '<td  bgcolor="#C4C4C4" align="right"><font size=1><b>' . number_format($ventas_inventario[$key_alm][$value['cod']]['mov_costototal'], 2, '.', ',') . '</strong></td>';

                        $result .= '<td  bgcolor="#FFFFFF" align="right"><font size=1><b>' . number_format($saldos_final['st_final'][$key_alm][$value['cod']]['stk_stock'], 2, '.', ',') . '</strong></td>';
                        $result .= '<td  bgcolor="#FFFFFF" align="right"><font size=1><b>' . number_format($saldos_final['st_final'][$key_alm][$value['cod']]['stk_costounitario'], 2, '.', ',') . '</strong></td>';
                        $result .= '<td  bgcolor="#C4C4C4" align="right"><font size=1><b>' . number_format($saldos_final['st_final'][$key_alm][$value['cod']]['stk_costototal'], 2, '.', ',') . '</strong></td>';


                        $result .= '<td  bgcolor="#C4C4C4" align="right"><font size=1><b>' . number_format($solo_venta_producto[$key_alm][$value['cod']]['mov_cantidad'], 2, '.', ',') . '</strong></td>';
                        $result .= '<td  bgcolor="#C4C4C4" align="right"><font size=1><b>' . number_format($solo_venta_producto[$key_alm][$value['cod']]['mov_precio'], 2, '.', ',') . '</strong></td>';
                        $result .= '<td  bgcolor="#C4C4C4" align="right"><font size=1><b>' . number_format($solo_venta_producto[$key_alm][$value['cod']]['mov_importe'], 2, '.', ',') . '</strong></td>';
                        $result .= '<td  bgcolor="#C4C4C4" align="right"><font size=1><b>' . number_format($solo_venta_producto[$key_alm][$value['cod']]['mov_cantidad'] / $cantidadDiasMes, 2, '.', ',') . '</strong></td>';
                        $Precio_sin_igv = ($solo_venta_producto[$key_alm][$value['cod']]['mov_precio'] / 1.18);
                        $margen=(($Precio_sin_igv-($saldos_final['st_final'][$key_alm][$value['cod']]['stk_costounitario']))/$Precio_sin_igv)*100;
                        $result .= '<td  bgcolor="#C4C4C4" align="right"><font size=1><b>' . number_format($margen,2, '.', ',') . '</strong></td>';
                        $result .= '</tr>';
                    }

                    $total_stock_inicial+=(double) $stock_inicial['st_inicial'][$key_alm][$value['cod']]['stk_stock'];
                    $total_stock_costototal+=(double) $stock_inicial['st_inicial'][$key_alm][$value['cod']]['stk_costototal'];

                    $total_stock_entrada+=(double) $ingreso_inventario[$key_alm][$value['cod']]['mov_cantidad'];
                    $total_stock_entradacostototal+=(double) $ingreso_inventario[$key_alm][$value['cod']]['mov_costototal'];

                    $total_stock_salida_venta+=(double) $ventas_inventario[$key_alm][$value['cod']]['mov_cantidad'];
                    $total_stock_salidacostototal+=(double) $ventas_inventario[$key_alm][$value['cod']]['mov_costototal'];

                    $total_cantidad_vendido+=(double) $solo_venta_producto[$key_alm][$value['cod']]['mov_cantidad'];
                    $total_importe_vendido+=(double) $solo_venta_producto[$key_alm][$value['cod']]['mov_importe'];
                }


                $result .= '<tr>';
                $result .= '<td colspan=2 bgcolor="#336699"  style="color:#FFFFFF;font-size:11px;"><center><font size=1><b>TOTALES DE LA LINEA</font></center></td>';
                $result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><center><font size=1><b>' . $key_linea . ':' . $linea[$key_linea]['tab_descripcion'] . '</font></center></td>';

                $result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><center><font size=1><b>' . number_format($total_stock_inicial, 2, '.', ',') . '</font></center></td>';
                $result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><center><font size=1><b>' . number_format(0, 2, '.', ',') . '</font></center></td>';
                $result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><center><font size=1><b>' . number_format($total_stock_costototal, 2, '.', ',') . '</font></center></td>';

                $result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><center><font size=1><b>' . number_format($total_stock_entrada, 2, '.', ',') . '</font></center></td>';
                $result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><center><font size=1><b>' . number_format(0, 2, '.', ',') . '</font></center></td>';
                $result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><center><font size=1><b>' . number_format($total_stock_entradacostototal, 2, '.', ',') . '</font></center></td>';

                $result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><center><font size=1><b>' . number_format($total_stock_salida_venta, 2, '.', ',') . '</font></center></td>';
                $result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><center><font size=1><b>' . number_format(0, 2, '.', ',') . '</font></center></td>';
                $result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><center><font size=1><b>' . number_format($total_stock_salidacostototal, 2, '.', ',') . '</font></center></td>';

                $result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><center><font size=1><b>' . number_format($total_stock_final, 2, '.', ',') . '</font></center></td>';
                $result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><center><font size=1><b>' . number_format(0, 2, '.', ',') . '</font></center></td>';
                $result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><center><font size=1><b>' . number_format($total_stock_finalcostototal, 2, '.', ',') . '</font></center></td>';


                $result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><center><font size=1><b>' . number_format($total_cantidad_vendido, 2, '.', ',') . '</font></center></td>';
                $result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><center><font size=1><b>' . number_format(0, 2, '.', ',') . '</font></center></td>';
                $result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><center><font size=1><b>' . number_format($total_importe_vendido, 2, '.', ',') . '</font></center></td>';
                $result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><center><font size=1><b>-</font></center></td>';
                $result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><center><font size=1><b>-</font></center></td>';

                $result .= '</tr>';
            }
        }


        $result .= '</table>';

        return $result;
    }

    function getUltimoDiaMes($elAnio, $elMes) {


        $fecha_actual = date("Y-m");
        $fecha_ingresada = trim($elAnio . "-" . $elMes);
        if ($fecha_actual == $fecha_ingresada) {
            return date("d");
        } else {
            return date("d", (mktime(0, 0, 0, $elMes + 1, 1, $elAnio) - 1));
        }
    }

}
