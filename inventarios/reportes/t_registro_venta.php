<?php

class RegistroVentaTemplate extends Template {

 

    function Titulo() {
        return '<div align="center"><h2><b>Registro de ventas</b></h2></div>';
    }

    function formSearch() {
        $estaciones = RegistroVentaModel::obtenerEstaciones();
        $hoy = date("d/m/Y");

        $form = new form2('', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control');
        $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "REPORTES.REGISVENTAS"));
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

        /* $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Art&iacute;culo</td><td>:</td><td>'));
          $form->addElement(FORM_GROUP_MAIN, new f2element_text("art_desde", "", '', '', 17, 13));
          $form->addElement(FORM_GROUP_MAIN, new f2element_text("art_desde2", "", '', '', 25, 30,'',array('readonly')));
          $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<img src="/sistemaweb/images/help.gif" width="16" height="15" onMouseOver="this.style.cursor=\'pointer\'" onclick="javascript:mostrarAyuda(\'/sistemaweb/ventas_clientes/lista_ayuda.php\',\'Buscar.art_desde\',\'Buscar.art_desde2\',\'articulos\',\'\',\'<?php echo $valor;?>\');"> '));

          $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>L&iacute;nea</td><td>:</td><td>'));
          $form->addElement(FORM_GROUP_MAIN, new f2element_text("art_linea", "", '', '', 17, 13));
          $form->addElement(FORM_GROUP_MAIN, new f2element_text("art_linea2", "", '', '', 25, 30,'',array('readonly')));
          $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<img src="/sistemaweb/images/help.gif" width="16" height="15" onMouseOver="this.style.cursor=\'pointer\'" onclick="javascript:mostrarAyuda(\'/sistemaweb/ventas_clientes/lista_ayuda.php\',\'Buscar.art_linea\',\'Buscar.art_linea2\',\'lineas\',\'\',\'<?php echo $valor;?>\');"> '));

          $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td  align="center" colspan="3">'));
          $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type="radio" name="tipo_reporte" value="CONTABLE" checked>Contable (Completo)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'));
          $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type="radio" name="tipo_reporte" value="FISICO">F&iacute;sico'));
         */
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="3" align="center">'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Buscar"><img src="/sistemaweb/images/search.gif" alt="left"/> Buscar</button>&nbsp;&nbsp;&nbsp;'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="PDF"><img src="/sistemaweb/images/icono_pdf.gif" alt="left"/> PDF</button>'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));

        return $form->getForm();
    }

   public function listado($resultado, $desde, $hasta, $art_desde, $estacion, $tipo, $linea) {

        $result = '';
        if (count($resultado) > 0) {
            
       
           
            $result = '<table><thead><tr>
                                <td>Num.C</td>
                                <td>F.Emi</td>
                                <td>F.Ven</td>
                                  <td>Tipo</td>
                                <td>Tipo.D</td>
                                <td>N.Serie</td>
                                <td>Trans</td>
                                <td>DI </td>
                                <td>Nombre</td>
                                <td>ISC </td>
                                 <td>Otros tributos </td>
                                <td>Imponible </td>
                                 <td>Igv </td>
                                 <td>Importe </td>
                                 <td>Tipo.Cambio </td>
                                </tr></thead>';
            $result .= '<tbody>';
            foreach ($resultado as $fila) {
                $result .= '<tr>';
                $result .= '<td>' . $fila['num_correlativo'] . '</td>';
                $result .= '<td>' . $fila['fecha_emision'] . '</td>';
                $result .= '<td>' . $fila['fecha_vencimiento'] . '</td>';
                $result .= '<td>' . trim($fila['tipo']) . '</td>';
                $result .= '<td>' . trim($fila['tipo_docu']) . '</td>';
                $result .= '<td>' . $fila['nroserie'] . '</td>';
                $result .= '<td>' . $fila['trans'] . '</td>';
                 $result .= '<td>' . $fila['ruc']. '</td>';
                $result .= '<td>'.$fila['nombre'].'</td>';
                $result .= '<td>0.00</td>';
                $result .= '<td>0.00</td>';
                $result .= '<td>' . $fila['imponible'] . '</td>';
                $result .= '<td>' . $fila['igv'] . '</td>';
                $result .= '<td>' . $fila['importe'] . '</td>';
                $result .= '<td>' . $fila['tipocambio'] . '</td>';
                $result .= '</tr>';
            }
            $result .= '</tbody>';
            $result .= '</table>';
            return $result;
        }
    }

   

     public function TD($td) {
         $tipo_comprobante_letra = array("B" => "(Boleta)", "F" => "(Factura)","N"=>"(Nota credito)");
       $tipo_comprobante = array("B" => "03", "F" => "01","N"=>"07");
        if (array_key_exists($td, $tipo_comprobante)) {
            return $tipo_comprobante[$td]."".$tipo_comprobante_letra[$td];
        } else {
            return $td;
        }
    }

    function reportePDF($resultado, $desde, $hasta, $tipo) {

        $empresa = RegistroVentaModel::datosEmpresa();
        echo "sista listo para mostar pdf";
        $cabecera2 = Array(
            "mov_fecha" => "FECHA",
            "tipodocu" => "TIPO",
            "seriedocu" => "SERIE",
            "numdocu" => "NUMERO",
            "mov_numero" => "FORMULARIO ",
            "tran_codigo" => "TIP.OPER.",
            "mov_cant_entrada" => "CANTIDAD",
            "mov_unit_entrada" => "COSTO UNITARIO",
            "mov_cost_entrada" => "COSTO TOTAL",
            "mov_cant_salida" => "CANTIDAD",
            "mov_unit_salida" => "COSTO UNITARIO",
            "mov_cost_salida" => "COSTO TOTAL",
            "mov_cant_actual" => "CANTIDAD",
            "mov_val_unit_act" => "COSTO UNITARIO",
            "mov_total_act" => "COSTO TOTAL"
        );

        if ($tipo == "CONTABLE") {
            $cabecera1 = Array(
                "entrada_izq" => "",
                "entrada_med" => "ENTRADAS",
                "salida_med" => "SALIDAS",
                "final_med" => "SALDO FINAL"
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

        if ($tipo == "CONTABLE") {
            $reporte->definirColumna("mov_fecha", $reporte->TIPO_TEXTO, 10, "L");
            $reporte->definirColumna("tipodocu", $reporte->TIPO_TEXTO, 4, "C");
            $reporte->definirColumna("seriedocu", $reporte->TIPO_TEXTO, 5, "C");
            $reporte->definirColumna("numdocu", $reporte->TIPO_TEXTO, 10, "L");
            $reporte->definirColumna("mov_numero", $reporte->TIPO_TEXTO, 10, "L");
            $reporte->definirColumna("tran_codigo", $reporte->TIPO_TEXTO, 18, "L");
            $reporte->definirColumna("tran_codigo", $reporte->TIPO_TEXTO, 24, "C");
            $reporte->definirColumna("mov_cant_entrada", $reporte->TIPO_TEXTO, 12, "C");
            $reporte->definirColumna("mov_unit_entrada", $reporte->TIPO_TEXTO, 12, "R");
            $reporte->definirColumna("mov_cost_entrada", $reporte->TIPO_COSTO, 12, "R");
            $reporte->definirColumna("mov_cant_salida", $reporte->TIPO_TEXTO, 12, "R");
            $reporte->definirColumna("mov_unit_salida", $reporte->TIPO_COSTO, 12, "R");
            $reporte->definirColumna("mov_cost_salida", $reporte->TIPO_COSTO, 12, "R");
            $reporte->definirColumna("mov_cant_actual", $reporte->TIPO_COSTO, 11, "R");
            $reporte->definirColumna("mov_val_unit_act", $reporte->TIPO_COSTO, 12, "R");
            $reporte->definirColumna("mov_total_act", $reporte->TIPO_COSTO, 12, "R");

            $reporte->definirColumna("entrada_izq", $reporte->TIPO_TEXTO, 66, "C", "_saldoinicial2");
            $reporte->definirColumna("entrada_med", $reporte->TIPO_TEXTO, 30, "C", "_saldoinicial2");
            $reporte->definirColumna("salida_med", $reporte->TIPO_TEXTO, 45, "C", "_saldoinicial2");
            $reporte->definirColumna("final_med", $reporte->TIPO_TEXTO, 15, "C", "_saldoinicial2");

            $reporte->definirColumna("dummy1", $reporte->TIPO_TEXTO, 62, "R", "_totales");
            $reporte->definirColumna("cant_entrada", $reporte->TIPO_CANTIDAD, 25, "C", "_totales");
            $reporte->definirColumna("cost_entrada", $reporte->TIPO_COSTO, 18, "R", "_totales");
            $reporte->definirColumna("cant_salida", $reporte->TIPO_CANTIDAD, 16, "C", "_totales");
            $reporte->definirColumna("cost_salida", $reporte->TIPO_COSTO, 20, "R", "_totales");
            $reporte->definirColumna("cantidad_total", $reporte->TIPO_CANTIDAD, 14, "C", "_totales");
            $reporte->definirColumna("valor_total", $reporte->TIPO_COSTO, 23, "R", "_totales");
        } else {
            $reporte->definirColumna("mov_fecha", $reporte->TIPO_TEXTO, 10, "L");
            $reporte->definirColumna("tipodocu", $reporte->TIPO_TEXTO, 4, "C");
            $reporte->definirColumna("seriedocu", $reporte->TIPO_TEXTO, 5, "C");
            $reporte->definirColumna("numdocu", $reporte->TIPO_TEXTO, 10, "L");
            $reporte->definirColumna("mov_numero", $reporte->TIPO_TEXTO, 10, "L");
//$reporte->definirColumna("tran_codigo", $reporte->TIPO_TEXTO, 18, "L");
            $reporte->definirColumna("tran_codigo", $reporte->TIPO_TEXTO, 24, "C");
            $reporte->definirColumna("mov_cant_entrada", $reporte->TIPO_CANTIDAD, 12, "R");
            $reporte->definirColumna("mov_cant_salida", $reporte->TIPO_CANTIDAD, 12, "R");
            $reporte->definirColumna("mov_cant_actual", $reporte->TIPO_CANTIDAD, 12, "R");

            $reporte->definirColumna("entrada_izq", $reporte->TIPO_TEXTO, 66, "C", "_saldoinicial2");
            $reporte->definirColumna("entrada_med", $reporte->TIPO_TEXTO, 20, "C", "_saldoinicial2");
            $reporte->definirColumna("salida_med", $reporte->TIPO_TEXTO, 6, "C", "_saldoinicial2");
            $reporte->definirColumna("final_med", $reporte->TIPO_TEXTO, 15, "C", "_saldoinicial2");
            $reporte->definirColumna("final_med", $reporte->TIPO_TEXTO, 13, "C", "_saldoinicial2");

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

        $formularios = RegistroVentaModel::ObtenerTiposFormularios();

        $reporte->definirCabecera(1, "L", $formato);
        $reporte->definirCabecera(1, "R", "PAG.%p");
        $reporte->definirCabecera(2, "C", $titulo);

        $reporte->definirCabecera(3, "L", "PERIODO: " . $desde . " AL " . $hasta);
        $reporte->definirCabecera(4, "L", "RUC: " . $empresa['ruc']);
        $reporte->definirCabecera(5, "L", "RAZON SOCIAL: " . $empresa['razsocial']);


        $reporte->definirCabeceraPredeterminada($cabecera1, "_saldoinicial2");
        $reporte->definirCabeceraPredeterminada($cabecera2);

        foreach ($resultado['almacenes'] as $mov_almacen => $almacen) {
            $reporte->definirCabecera(6, "L", "ESTABLECIMIENTO: " . $mov_almacen . " " . RegistroVentaModel::obtenerDescripcionAlmacen($mov_almacen) . " " . $empresa['direccion']);

            foreach ($almacen['articulos'] as $art_codigo => $articulo) {
                $reporte->definirCabecera(7, "L", "CODIGO DE LA EXISTENCIA: " . $art_codigo);
                $reporte->definirCabecera(8, "L", "TIPO: 01");
                $reporte->definirCabecera(9, "L", "DESCRIPCION: " . RegistroVentaModel::obtenerDescripcion($art_codigo));
                $reporte->definirCabecera(10, "L", "CODIGO DE LA UNIDAD DE MEDIDA: " . RegistroVentaModel::unidadMedida($art_codigo));
                $reporte->AddPage();

                if ($tipo == "CONTABLE") {
                    $arr = array("entrada_izq" => "", "entrada_med" => "", "salida_med" => "", "final_izq" => $articulo['saldoinicial']['cant_anterior'], "final_med" => "");
                } else {
                    $arr = array("entrada_izq" => "", "entrada_med" => "", "salida_med" => "", "final_med" => $articulo['saldoinicial']['cant_anterior']);
                }
                $reporte->nuevaFila($arr, "_saldoinicial2");

                foreach ($articulo['movimientos'] as $i => $movimiento) {
                    $reporte->nuevaFila($movimiento);
                }
                $reporte->lineaH();
                $reporte->nuevaFila($articulo['totales'], "_totales");
            }
        }

        $reporte->Output("/sistemaweb/ventas_clientes/reportes/pdf/Registro_venta.pdf", "F");
        return '<script> window.open("/sistemaweb/ventas_clientes/reportes/pdf/Registro_venta.pdf","miwin","width=750,height=550,scrollbars=yes, resizable=yes, menubar=no");</script>';
    }

}
