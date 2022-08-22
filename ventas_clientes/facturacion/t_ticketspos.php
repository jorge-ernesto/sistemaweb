<?php

class TicketsPosTemplate extends Template {

    function titulo() {
        return '<h2><b>Tickets en Punto de Venta</b></h2>';
    }

    function formSearch() {
        $almacenes = TicketsPosModel::obtenerAlmacenes();
        $tipos = Array("actual" => "Actual", "historico" => "Historico");

        $tipo = TicketsPosModel::obtieneTipos();
        $tipo['TODOS'] = "Todos los Tipos";

        $lado = TicketsPosModel::obtieneLados();
        $lado['TODOS'] = "Todos los Lados";

        $turno = TicketsPosModel::obtieneTurnos();

        $caja = TicketsPosModel::obtieneCajas();
        $caja['TODAS'] = "Todas las Cajas";

        $form = new Form('', "Agregar", FORM_METHOD_POST, "control.php", '', "control");
        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "FACTURACION.TICKETSPOS"));
        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rxp", "100"));
        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("pagina", "1"));

        $form->addGroup("GROUP_ALMACEN", "Almacen");
        $form->addElement("GROUP_ALMACEN", new form_element_combo("Almacen:", "ch_almacen", $_SESSION['almacen'], '<br>', '', '', $almacenes, false, ''));

        $form->addGroup("GROUP_TIPO", "Tipo");
        $form->addElement("GROUP_TIPO", new form_element_combo("Tipo:", "ch_tipo", "TODOS", '<br>', '', '', $tipo, false, ''));

        $form->addGroup("GROUP_LADO", "Lado");
        $form->addElement("GROUP_LADO", new form_element_combo("Lado:", "ch_lado", "TODOS", '<br>', '', '', $lado, false, ''));

        $form->addGroup("GROUP_CAJA", "Caja");
        $form->addElement("GROUP_CAJA", new form_element_combo("Caja:", "ch_caja", "TODAS", '<br>', '', '', $caja, false, ''));

        $form->addGroup("GRUPO_TIPO_CONSULTA", "Tipo de consulta");
        $form->addElement("GRUPO_TIPO_CONSULTA", new form_element_radio("", "ch_tipo_consulta", "actual", '', '', 1, $tipos, 'onclick="manejarTipoConsulta(this)"'));

        $form->addGroup("GRUPO_HISTORICO", "Rango de fecha", "none");
        $form->addElement("GRUPO_HISTORICO", new form_element_text("Periodo:", "ch_periodo", date("Y"), '<br>', '', 5, 4, false));
        $form->addElement("GRUPO_HISTORICO", new form_element_text("Mes:", "ch_mes", date("m"), '<br>', '', 3, 2, false));
        $form->addElement("GRUPO_HISTORICO", new form_element_text("Desde:", "ch_dia_desde", "01", '', '', 3, 2, false));
        $form->addElement("GRUPO_HISTORICO", new form_element_text("Hasta:", "ch_dia_hasta", date("d"), '<br>', '', 3, 2, false));
        $form->addElement("GRUPO_HISTORICO", new form_element_combo("Turno:", "ch_turno", '', '<br>', '', '', $turno, false, ''));

        $form->addGroup("GRUPO_CONDICIONES", "Opciones");
        $form->addElement("GRUPO_CONDICIONES", new form_element_checkbox("Venta", "tm[]", "V", '', '', '', true));
        $form->addElement("GRUPO_CONDICIONES", new form_element_checkbox("Devolucion", "tm[]", "D", '', '', '', true));
        $form->addElement("GRUPO_CONDICIONES", new form_element_checkbox("Extorno", "tm[]", "A", '<br>', '', '', true));
        $form->addElement("GRUPO_CONDICIONES", new form_element_checkbox("Boleta", "td[]", "B", '', '', '', true));
        $form->addElement("GRUPO_CONDICIONES", new form_element_checkbox("Factura", "td[]", "F", '', '', '', true));
        $form->addElement("GRUPO_CONDICIONES", new form_element_checkbox("Nota de Despacho", "td[]", "N", '<br>', '', '', true));
        $form->addElement("GRUPO_CONDICIONES", new form_element_checkbox("Efectivo", "tfpago[]", '', '', '', '', true));
        $form->addElement("GRUPO_CONDICIONES", new form_element_checkbox("Tarjeta de Cr&eacute;dito", "fpago[]", '', '<br>', '', '', true));
        $form->addElement("GRUPO_CONDICIONES", new form_element_freeTags('Con Bonus:'));
        $form->addElement("GRUPO_CONDICIONES", new form_element_freeTags('<span class="form_checkbox"><input type="checkbox" value="Bo" name="Bonus" onclick="archivoBonus(this)"><br></span></br>'));

        //$form->addElement("GRUPO_CONDICIONES", new form_element_text("Articulo:", "art_codigo", '', '', '', 15, 13, false));

        $form->addElement("GRUPO_CONDICIONES", new form_element_freeTags('
        Art&iacute;culo : <br>
        <input type="text" id="txt-No_Producto" class="mayuscula" name="art_desde2" placeholder="Ingresar Nombre - Codigo" autocomplete="off" value="" maxlength="35" size="28">
        <br>
        <input type="text" id="txt-Nu_Id_Producto" name="art_codigo" value="" maxlength="25" size="20">
        <br>
        '));

        $form->addElement("GRUPO_CONDICIONES", new form_element_hidden('itemdescripcion','DESCRIPCION', trim(@$item["ch_item_descripcion"]),'', 15, 13,'',array()));
        $form->addElement("GRUPO_CONDICIONES", new form_element_text("<br/>RUC:", "ruc", '', '<br>', '', 11, 11, false));
        $form->addElement("GRUPO_CONDICIONES", new form_element_text("Cuenta:", "cuenta", '', '<br>', '', 11, 11, false));
        $form->addElement("GRUPO_CONDICIONES", new form_element_text("Tarjeta Fidelizacion:", "tarjeta", '', '', '', 11, 11, false));

        $form->addGroup("GRUPO_FE", "Facturacion Electronica");
        $form->addElement("GRUPO_FE", new form_element_text("<br/>Serie:", "txtfeserie", '', '<br>', '', 6, 4, false));
        $form->addElement("GRUPO_FE", new form_element_text("Numero:", "txtfenumero", '', '<br>', '', 10, 8, false));
    
        $form->addGroup("GRUPO_BOTONES", "");
        $form->addElement("GRUPO_BOTONES", new form_element_freeTags('<button type="submit" name="action" value="Buscar"><img src="/sistemaweb/images/search.png" alt="left" />  Buscar</button>'));
        $form->addElement("GRUPO_BOTONES", new form_element_freeTags('&nbsp;&nbsp;&nbsp;'));
        $form->addElement("GRUPO_BOTONES", new form_element_freeTags('<button type="submit" name="action" value="Excel"><img src="/sistemaweb/images/excel_icon.png" alt="left" />  Excel</button>'));
        $form->addElement("GRUPO_BOTONES", new form_element_freeTags('</br></br>&nbsp;&nbsp;&nbsp'));
        $form->addElement("GRUPO_BOTONES", new form_element_freeTags('<button type="submit" name="action" value="Acumulada">Imprimir Venta Acum.</button>'));
        $form->addElement("GRUPO_BOTONES", new form_element_freeTags('</br></br>&nbsp;&nbsp;&nbsp'));
        $form->addElement("GRUPO_BOTONES", new form_element_freeTags('<button type="submit" name="action" value="AcumuladaExcel"><img src="/sistemaweb/images/excel_icon.png" alt="left"/> Venta Acumulada</button>'));

        $form->addGroup("GRUPO_BONUS", "Archivo zBonus", "none");
        $form->addElement("GRUPO_BONUS", new form_element_freeTags('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'));
        $form->addElement("GRUPO_BONUS", new form_element_freeTags('<button type="submit" name="action" value="Bonus">Exportar Bonus</button>'));
        $form->addElement("GRUPO_BONUS", new form_element_freeTags('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'));

        return $form->getForm();
    }

    function formPag($paginacion, $vec) {
        $ch_tipo_consulta = $vec[0];
        $tm0 = $vec[1];
        $tm1 = $vec[2];
        $tm2 = $vec[3];
        $td0 = $vec[4];
        $td1 = $vec[5];
        $td2 = $vec[6];
        $Bonus = $vec[7];
        $ch_almacen = $vec[8];
        $ch_lado = $vec[9];
        $ch_caja = $vec[10];
        $ch_turno = $vec[11];
        $ch_periodo = $vec[12];
        $ch_mes = $vec[13];
        $ch_dia_desde = $vec[14];
        $ch_dia_hasta = $vec[15];
        $art_codigo = $vec[16];
        $ruc = $vec[17];
        $cuenta = $vec[18];
        $tarjeta = $vec[19];
        $ch_tipo = $vec[20];

        $form = new form2('', 'Paginacion', FORM_METHOD_POST, "control.php", '', "control");
        $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "FACTURACION.TICKETSPOS"));
        $form->addGroup("GRUPO_PAGINA", "Paginacion");
        if ($paginacion['paginas'] == 'P') {
            $paginacion['paginas'] = '0';
        }
        $form->addElement("GRUPO_PAGINA", new f2element_freeTags('P&aacute;gina ' . $paginacion['paginas'] . ' de ' . $paginacion['numero_paginas'] . ' P&aacute;ginas&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'));
        $form->addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/first.gif', espacios(2), array("border" => "0", "alt" => "Primera P&aacute;gina", "onclick" => "javascript:PaginarRegistros('" . $paginacion['pp'] . "','" . $paginacion['primera_pagina'] . "','" . $ch_tipo_consulta . "','" . $tm0 . "','" . $tm1 . "','" . $tm2 . "','" . $td0 . "','" . $td1 . "','" . $td2 . "','" . $Bonus . "','" . $ch_almacen . "','" . $ch_lado . "','" . $ch_caja . "','" . $ch_turno . "','" . $ch_periodo . "','" . $ch_mes . "','" . $ch_dia_desde . "','" . $ch_dia_hasta . "','" . $art_codigo . "','" . $ruc . "','" . $cuenta . "','" . $tarjeta . "','" . $ch_tipo . "')", "")));
        $form->addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/left.gif', espacios(5), array("border" => "0", "alt" => "P&aacute;gina Anterior", "onclick" => "javascript:PaginarRegistros('" . $paginacion['pp'] . "','" . $paginacion['pagina_previa'] . "','" . $ch_tipo_consulta . "','" . $tm0 . "','" . $tm1 . "','" . $tm2 . "','" . $td0 . "','" . $td1 . "','" . $td2 . "','" . $Bonus . "', '" . $ch_almacen . "','" . $ch_lado . "','" . $ch_caja . "','" . $ch_turno . "','" . $ch_periodo . "','" . $ch_mes . "','" . $ch_dia_desde . "','" . $ch_dia_hasta . "','" . $art_codigo . "','" . $ruc . "','" . $cuenta . "','" . $tarjeta . "','" . $ch_tipo . "')", "")));



        $form->addElement("GRUPO_PAGINA", new f2element_text('paginas', '', $paginacion['paginas'], espacios(5), 3, 2, array("onChange" => "javascript:PaginarRegistros('" . $paginacion['pp'] . "',this.value,'" . $ch_tipo_consulta . "','" . $tm0 . "','" . $tm1 . "','" . $tm2 . "','" . $td0 . "','" . $td1 . "','" . $td2 . "','" . $Bonus . "','" . $ch_almacen . "','" . $ch_lado . "','" . $ch_caja . "','" . $ch_turno . "','" . $ch_periodo . "','" . $ch_mes . "','" . $ch_dia_desde . "','" . $ch_dia_hasta . "','" . $art_codigo . "','" . $ruc . "','" . $cuenta . "','" . $tarjeta . "','" . $ch_tipo . "')")));
        $form->addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/right.gif', espacios(2), array("border" => "0", "alt" => "P&aacute;gina Siguente", "onclick" => "javascript:PaginarRegistros('" . $paginacion['pp'] . "','" . $paginacion['pagina_siguiente'] . "','" . $ch_tipo_consulta . "','" . $tm0 . "','" . $tm1 . "','" . $tm2 . "','" . $td0 . "','" . $td1 . "','" . $td2 . "','" . $Bonus . "','" . $ch_almacen . "','" . $ch_lado . "','" . $ch_caja . "','" . $ch_turno . "','" . $ch_periodo . "','" . $ch_mes . "','" . $ch_dia_desde . "','" . $ch_dia_hasta . "','" . $art_codigo . "','" . $ruc . "','" . $cuenta . "','" . $tarjeta . "','" . $ch_tipo . "')", "")));
        $form->addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/last.gif', espacios(2), array("border" => "0", "alt" => "&Uacute;ltima P&aacute;gina", "onclick" => "javascript:PaginarRegistros('" . $paginacion['pp'] . "','" . $paginacion['ultima_pagina'] . "','" . $ch_tipo_consulta . "','" . $tm0 . "','" . $tm1 . "','" . $tm2 . "','" . $td0 . "','" . $td1 . "','" . $td2 . "','" . $Bonus . "','" . $ch_almacen . "','" . $ch_lado . "','" . $ch_caja . "','" . $ch_turno . "','" . $ch_periodo . "','" . $ch_mes . "','" . $ch_dia_desde . "','" . $ch_dia_hasta . "','" . $art_codigo . "','" . $ruc . "','" . $cuenta . "','" . $tarjeta . "','" . $ch_tipo . "')", "")));
        $form->addElement("GRUPO_PAGINA", new f2element_text('numero_registros', 'Registros por P&aacute;gina : ', $paginacion['pp'], espacios(2), 4, 4, array("onChange" => "javascript:PaginarRegistros(this.value,'" . $paginacion['primera_pagina'] . "','" . $ch_tipo_consulta . "','" . $tm0 . "','" . $tm1 . "','" . $tm2 . "','" . $td0 . "','" . $td1 . "','" . $td2 . "','" . $Bonus . "','" . $ch_almacen . "','" . $ch_lado . "','" . $ch_caja . "','" . $ch_turno . "','" . $ch_periodo . "','" . $ch_mes . "','" . $ch_dia_desde . "','" . $ch_dia_hasta . "','" . $art_codigo . "','" . $ruc . "','" . $cuenta . "','" . $tarjeta . "','" . $ch_tipo . "')", "")));

        return $form->getForm();
    }

    function listado($resultados, $modo, $iYear, $iMonth) {
        $totales['cantidad'] = 0;
        $totales['importe'] = 0;

        $result = '<center><table id="tabprincipal" align="center">';
        $result .= '<tr>';
        $result .= '<th class="grid_cabecera" style="font-size:0.8em; color:white;">&nbsp;</th>';
        $result .= '<th class="grid_cabecera" style="font-size:0.8em; color:white;">&nbsp;&nbsp;TM&nbsp;&nbsp;</th>';
        $result .= '<th class="grid_cabecera" style="font-size:0.8em; color:white;">&nbsp;&nbsp;TD&nbsp;&nbsp;</th>';
        $result .= '<th class="grid_cabecera" style="font-size:0.8em; color:white;">&nbsp;&nbsp;# Ticket&nbsp;&nbsp;</th>';
        $result .= '<th class="grid_cabecera" style="font-size:0.8em; color:white;">&nbsp;&nbsp;Numero&nbsp;&nbsp;</th>';
        $result .= '<th class="grid_cabecera" style="font-size:0.8em; color:white;">&nbsp;&nbsp;Fecha&nbsp;&nbsp;</th>';
        $result .= '<th class="grid_cabecera" style="font-size:0.8em; color:white;">&nbsp;&nbsp;Turno&nbsp;&nbsp;</th>';
        $result .= '<th class="grid_cabecera" style="font-size:0.8em; color:white;">&nbsp;&nbsp;Descripcion&nbsp;&nbsp;</th>';
        $result .= '<th class="grid_cabecera" style="font-size:0.8em; color:white;">&nbsp;&nbsp;Cantidad&nbsp;&nbsp;</th>';
        $result .= '<th class="grid_cabecera" style="font-size:0.8em; color:white;">&nbsp;&nbsp;Precio&nbsp;&nbsp;</th>';
        $result .= '<th class="grid_cabecera" style="font-size:0.8em; color:white;">&nbsp;&nbsp;IGV&nbsp;&nbsp;</th>';
        $result .= '<th class="grid_cabecera" style="font-size:0.8em; color:white;">&nbsp;&nbsp;Importe&nbsp;&nbsp;</th>';
        $result .= '<th class="grid_cabecera" style="font-size:0.8em; color:white;">&nbsp;&nbsp;Tarjeta&nbsp;&nbsp;</th>';
        $result .= '<th class="grid_cabecera" style="font-size:0.8em; color:white;">&nbsp;&nbsp;Odometro&nbsp;&nbsp;</th>';
        $result .= '<th class="grid_cabecera" style="font-size:0.8em; color:white;">&nbsp;Placa&nbsp;</th>';
        $result .= '<th class="grid_cabecera" style="font-size:0.8em; color:white;">&nbsp;&nbsp;Cod. Cli.&nbsp;&nbsp;</th>';
        $result .= '<th class="grid_cabecera" style="font-size:0.8em; color:white;">&nbsp;&nbsp;Usuario&nbsp;&nbsp;</th>';
        $result .= '<th class="grid_cabecera" style="font-size:0.8em; color:white;">&nbsp;Caja&nbsp;</th>';
        $result .= '<th class="grid_cabecera" style="font-size:0.8em; color:white;">&nbsp;Lado&nbsp;</th>';
        $result .= '<th class="grid_cabecera" style="font-size:0.8em; color:white;">&nbsp;&nbsp;Bonus&nbsp;&nbsp;</th>';
        $result .= '<th class="grid_cabecera" style="font-size:0.8em; color:white;">&nbsp;&nbsp;RUC&nbsp;&nbsp;</th>';
        $result .= '<th class="grid_cabecera" style="font-size:0.8em; color:white;">&nbsp;&nbsp;Razon Social&nbsp;&nbsp;</th>';
        $result .= '<th class="grid_cabecera" style="font-size:0.8em; color:white;">&nbsp;&nbsp;Puntos Bonus&nbsp;&nbsp;</th>';        
        $result .= '<th class="grid_cabecera" style="font-size:0.8em; color:white;">&nbsp;&nbsp;Fecha Extorno&nbsp;&nbsp;</th>';
        $result .= '<th class="grid_cabecera" style="font-size:0.8em; color:white;">&nbsp;&nbsp;Documento Extorno&nbsp;&nbsp;</th>';
        $result .= '<th class="grid_cabecera" style="font-size:0.8em; color:white;">&nbsp;&nbsp;Fecha Nuevo&nbsp;&nbsp;</th>';
        $result .= '<th class="grid_cabecera" style="font-size:0.8em; color:white;">&nbsp;&nbsp;Documento Nuevo&nbsp;&nbsp;</th>';
        $result .= '<th class="grid_cabecera" style="font-size:0.8em; color:white;">&nbsp;&nbsp;Trabajador&nbsp;&nbsp;</th>';
        $result .= '</tr>';

        $modelTicketPos = new TicketsPosModel();
        $tabla = "pos_transtmp";
        if ($modo == "historico")
            $tabla = pg_escape_string("pos_trans" . $iYear . $iMonth);

        $cantidad_de_ticke=array();
        foreach ($resultados as $x => $a) {
            if ($x == -1) {
                $totales = $a;
                break;
            }
// Modificacion por Alvaro: Eliminar esa ventana de reimpresion. Muestra datos errados y causa mucha confusion a los clientes.
//            $url = "/sistemaweb/ventas_clientes/reimpresiones.php?nro_caja=" . $a['caja'] . "&nro_trans=" . $a['trans'] . "&dia_trans=" . substr($a['fecha'], 0, 10) . "&tipo_consulta=" . urlencode($modo);
//            $result .= '<tr bgcolor="" onMouseOver=this.style.backgroundColor="#FFFFCC"; this.style.cursor="hand"; onMouseOut=this.style.backgroundColor=""; onClick="window.open(\'' . $url . '\',\'reimpresion\',\'width=600,height=400,scrollbars=yes,menubar=no,left=60,top=20\')";>';
            $url = "/sistemaweb/ventas_clientes/reimpresiones.php?nro_caja=" . $a['caja'] . "&nro_trans=" . $a['trans'] . "&dia_trans=" . substr($a['fecha'], 0, 10) . "&tipo_consulta=" . urlencode($modo);
            $result .= '<tr bgcolor="" onClick="return true;";>';

// By Alvaro - Eliminar la funciond e reimpresio - ya no se necesita
//            $result .= '<td><input type="radio" name="xxx" onClick="window.open(\'' . $url . '\',\'reimpresion\',\'width=600,height=400,scrollbars=yes,menubar=no,left=60,top=20\')"></td>';
            $usr="";
            if(isset($a['usr']))
                $usr=$a['usr'];

            //DOCUMENTO ORIGINAL
            $dFechaReferencia = "";
            $sSerieNumeroReferencia = "";
            if ( $a['rendi_gln'] != "" ) {
                $arrData = array(
                    "sNombreTabla" => $tabla,
                    "sCodigoAlmacen" => $a['almacen'],
                    "sCaja" => $a['caja'],
                    "sTipoDocumento" => $a['td'],
                    "fIDTrans" => $a['rendi_gln'],
                    "iNumeroDocumentoIdentidad" => $a['ruc'],
                );
                $arrResponseModel = $modelTicketPos->verify_reference_sales_invoice_document($arrData);
                $dFechaReferencia = "";
                $sSerieNumeroReferencia = "";
                if ($arrResponseModel["sStatus"] == "success") {
                    $dFechaReferencia = $arrResponseModel["arrDataModel"]["fecha"];
                    $sSerieNumeroReferencia = $arrResponseModel["arrDataModel"]["usr"];
                }
            }

            //DOCUMENTO RESULTANTE
            $dFechaReferencia_resultante = "";
            $sSerieNumeroReferencia_resultante = "";
            if ( $a['rendi_acu'] != "" ) {
                $arrData = array(
                    "sNombreTabla" => $tabla,
                    "sCodigoAlmacen" => $a['almacen'],
                    "sCaja" => $a['caja'],
                    "sTipoDocumento" => $a['td'],
                    "fIDTrans" => $a['rendi_acu'],
                    "iNumeroDocumentoIdentidad" => $a['ruc'],
                );
                $arrResponseModel = $modelTicketPos->verify_reference_sales_invoice_document_result($arrData);
                $dFechaReferencia_resultante = "";
                $sSerieNumeroReferencia_resultante = "";
                if ($arrResponseModel["sStatus"] == "success") {
                    $dFechaReferencia_resultante = $arrResponseModel["arrDataModel"]["fecha"];
                    $sSerieNumeroReferencia_resultante = $arrResponseModel["arrDataModel"]["usr"];
                }
            }

            $color = ($x%2==0?"grid_detalle_par":"grid_detalle_impar");
            $puntos_bonus = empty($a['bonus']) || $a['bonus'] == NULL || $a['bonus'] == 0 ? '' : floor($a['puntos']);

            $result .= '<td class="'.$color.'"><input type="radio" name="xxx" onClick="return false;"></td>';
            $result .= '<td class="'.$color.'" align="center">&nbsp;' . htmlentities($a['tm']) . '&nbsp;</td>';
            $result .= '<td class="'.$color.'" align="center">&nbsp;' . htmlentities($a['td']) . '&nbsp;</td>';
            $result .= '<td class="'.$color.'" align="center">&nbsp;' . htmlentities($a['trans']) . '&nbsp;</td>';
            $result .= '<td class="'.$color.'" align="center">&nbsp;' . htmlentities($a['feserie']) . ' -  ' . htmlentities($a['fenumero']) . '</td>';
            $result .= '<td class="'.$color.'">&nbsp;&nbsp;' . htmlentities($a['fecha']) . '&nbsp;&nbsp;</td>';
            $result .= '<td class="'.$color.'" align="center">&nbsp;' . htmlentities($a['turno']) . '&nbsp;</td>';
            $result .= '<td class="'.$color.'">&nbsp;&nbsp;' . htmlentities($a['art_descripcion']) . '&nbsp;&nbsp;</td>';
            $result .= '<td class="'.$color.'" align="right">&nbsp;' . htmlentities($a['cantidad']) . '&nbsp;</td>';
            $result .= '<td class="'.$color.'" align="right">&nbsp;' . htmlentities($a['precio']) . '&nbsp;</td>';
            $result .= '<td class="'.$color.'" align="right">&nbsp;' . htmlentities($a['igv']) . '&nbsp;</td>';
            $result .= '<td class="'.$color.'" align="right">&nbsp;' . htmlentities($a['importe']) . '&nbsp;</td>';
            $result .= '<td class="'.$color.'" align="center">&nbsp;' . htmlentities($a['tarjeta']) . '&nbsp;</td>';
            $result .= '<td class="'.$color.'" align="right">&nbsp;' . htmlentities($a['odometro']) . '&nbsp;</td>';
            $result .= '<td class="'.$color.'" align="center">&nbsp;' . htmlentities($a['placa']) . '&nbsp;</td>';
            $result .= '<td class="'.$color.'" align="center">&nbsp;' . htmlentities($a['codcli']) . '&nbsp;</td>';
            $result .= '<td class="'.$color.'" align="center">&nbsp;' . htmlentities($a['chofer']) . '&nbsp;</td>';
            $result .= '<td class="'.$color.'" align="center">&nbsp;' . htmlentities($a['caja']) . '&nbsp;</td>';
            $result .= '<td class="'.$color.'" align="center">&nbsp;' . htmlentities($a['pump']) . '&nbsp;</td>';
            $result .= '<td class="'.$color.'" align="center">&nbsp;' . htmlentities($a['bonus']) . '&nbsp;</td>';
            $result .= '<td class="'.$color.'" align="center">&nbsp;' . htmlentities($a['ruc']) . '&nbsp;</td>';
            $result .= '<td class="'.$color.'" align="center">&nbsp;' . htmlentities($a['razsocial']) . '&nbsp;</td>';
            $result .= '<td class="'.$color.'" align="center">&nbsp;' . htmlentities($puntos_bonus) . '&nbsp;</td>';            
            $result .= '<td class="'.$color.'" align="center">&nbsp;' . htmlentities($dFechaReferencia) . '&nbsp;</td>';
            $result .= '<td class="'.$color.'" align="center">&nbsp;' . htmlentities($sSerieNumeroReferencia) . '&nbsp;</td>';
            $result .= '<td class="'.$color.'" align="center">&nbsp;' . htmlentities($dFechaReferencia_resultante) . '&nbsp;</td>';
            $result .= '<td class="'.$color.'" align="center">&nbsp;' . htmlentities($sSerieNumeroReferencia_resultante) . '&nbsp;</td>';
            $result .= '<td class="'.$color.'" align="center">&nbsp;' . htmlentities($a['trabajador']) . '&nbsp;</td>';
            $result .= '</tr>';
            $cantidad_de_ticke['T-'.$a['trans']]=1;
        }
        $result .= '<tr bgcolor="">';
        $result .= '<td class="grid_detalle_impar" bgcolor=#F4FA58 colspan="6" style="font-weight:bold">TOTAL: ' . number_format($totales['total'], 0, '.', ',') . ' transacciones</td>';
        $result .= '<td class="grid_detalle_impar" bgcolor=#F4FA58>&nbsp;</td><td class="grid_detalle_impar" bgcolor=#F4FA58></td>';
        $result .= '<td class="grid_detalle_impar" bgcolor=#F4FA58 align="right" style="font-weight:bold">' . number_format($totales['cantidad'], 4, '.', ',') . '</td>';
        $result .= '<td class="grid_detalle_impar" bgcolor=#F4FA58>&nbsp;</td>';
        $result .= '<td class="grid_detalle_impar" bgcolor=#F4FA58>&nbsp;</td>';
        $result .= '<td class="grid_detalle_impar" bgcolor=#F4FA58 align="right" style="font-weight:bold">' . number_format($totales['importe'], 4, '.', ',') . '</td>';
        $result .= '<td class="grid_detalle_impar" bgcolor=#F4FA58 colspan="10">&nbsp;</td>';
        $result .= '<td class="grid_detalle_impar" bgcolor=#F4FA58 align="center" style="font-weight:bold">' . $totales['puntos'] . '</td></tr>';
         $result .= '<tr bgcolor="">';
         
          $result .= '<td class="grid_detalle_impar" bgcolor=#F4FA58 colspan="6" style="font-weight:bold">CANTIDAD TICKES : </td><td class="grid_detalle_impar" bgcolor=#F4FA58></td>';
        $result .= '<td class="grid_detalle_impar" bgcolor=#F4FA58>&nbsp;</td>';
        $result .= '<td class="grid_detalle_impar" bgcolor=#F4FA58 align="right" style="font-weight:bold">' . count($cantidad_de_ticke) . '</td>';
        $result .= '<td class="grid_detalle_impar" bgcolor=#F4FA58>&nbsp;</td>';
        $result .= '<td class="grid_detalle_impar" bgcolor=#F4FA58>&nbsp;</td>';
        $result .= '<td class="grid_detalle_impar" bgcolor=#F4FA58>&nbsp;</td>';
        $result .= '<td class="grid_detalle_impar" bgcolor=#F4FA58 colspan="10">&nbsp;</td>';
        $result .= '<td class="grid_detalle_impar" bgcolor=#F4FA58 >&nbsp;</td></tr>';
         
        $result .= '<td class="grid_detalle_impar" bgcolor=#F4FA58 colspan="6" style="font-weight:bold">UNIDADES X TRANSACCIÓN : </td>';
        $result .= '<td class="grid_detalle_impar" bgcolor=#F4FA58>&nbsp;</td><td class="grid_detalle_impar" bgcolor=#F4FA58></td>';
        $result .= '<td class="grid_detalle_impar" bgcolor=#F4FA58 align="right" style="font-weight:bold">' . number_format(($totales['cantidad']/count($cantidad_de_ticke)), 4, '.', ',') . '</td>';
        $result .= '<td class="grid_detalle_impar" bgcolor=#F4FA58>&nbsp;</td>';
        $result .= '<td class="grid_detalle_impar" bgcolor=#F4FA58>&nbsp;</td>';
        $result .= '<td class="grid_detalle_impar" bgcolor=#F4FA58>&nbsp;</td>';
        $result .= '<td class="grid_detalle_impar" bgcolor=#F4FA58 colspan="10">&nbsp;</td>';
        $result .= '<td class="grid_detalle_impar" bgcolor=#F4FA58 >&nbsp;</td></tr>';
        
         $result .= '<tr bgcolor="">';
        $result .= '<td class="grid_detalle_impar" bgcolor=#F4FA58 colspan="6" style="font-weight:bold">TICKET PROMEDIO (TRANS S/.) : </td>';
        $result .= '<td class="grid_detalle_impar" bgcolor=#F4FA58>&nbsp;</td><td class="grid_detalle_impar" bgcolor=#F4FA58></td>';
        $result .= '<td class="grid_detalle_impar" bgcolor=#F4FA58 align="right" style="font-weight:bold">' . number_format(($totales['importe']/count($cantidad_de_ticke)), 4, '.', ',') . '</td>';
        $result .= '<td class="grid_detalle_impar" bgcolor=#F4FA58>&nbsp;</td>';
        $result .= '<td class="grid_detalle_impar" bgcolor=#F4FA58>&nbsp;</td>';
        $result .= '<td class="grid_detalle_impar" bgcolor=#F4FA58>&nbsp;</td>';
        $result .= '<td class="grid_detalle_impar" bgcolor=#F4FA58 colspan="10"></td>';
        $result .= '<td class="grid_detalle_impar" bgcolor=#F4FA58 >&nbsp;</td></tr>';
        
        $result .= '</table></center>';

        return $result;
    }

    function imprimir($acumula) {
        $texto_impresion = "";
        $CRLF = "\r\n";
        $texto_impresion .= $CRLF;
        $texto_impresion .= alinea("VENTA POR PRODUCTOS EN TURNO", 2, 40) . $CRLF;
        $texto_impresion .= $CRLF;
        $texto_impresion .= "EDS: " . $acumula['info']['almacen'] . $CRLF;
        $texto_impresion .= "ANIO " . $acumula['info']['periodo'] . " MES " . $acumula['info']['mes'] . $CRLF;
        $texto_impresion .= "DEL " . $acumula['info']['desde'] . " AL " . $acumula['info']['hasta'] . $CRLF;
        $texto_impresion .= $CRLF;

        $diaturno = $acumula['header'];
        $resul = $acumula['body'];
        for ($k = 0; $k < count($diaturno); $k++) {
            $texto_impresion .= "DIA: " . $diaturno[$k]['dia'] . $CRLF . "TURNO: " . $diaturno[$k]['turno'] . $CRLF;
            $texto_impresion .= "----------------------------------------" . $CRLF;
//          $texto_impresion .= "DESCRIPCION            CANTIDAD    TOTAL".$CRLF;
            $texto_impresion .= "DESCRIPCION    CANTIDAD       TOTAL     " . $CRLF;
            $texto_impresion .= "----------------------------------------" . $CRLF;
            for ($j = 0; $j < count($resul); $j++) {
                if (($diaturno[$k]['dia'] == $resul[$j]['dia']) and ($diaturno[$k]['turno'] == $resul[$j]['turno'])) {
                    if ($resul[$j]['producto'] != $resul[$j + 1]['producto']) {
                        $texto_impresion .= alinea($resul[$j]['producto'], 0, 22) . $CRLF;
                        $texto_impresion .= "            ".alinea(showNumber(round($resul[$j]['cantidad'], 2)), 1, 9) . "     " . alinea(showNumber($resul[$j]['importe']), 1, 9) . $CRLF;
                    }
                }
            }
            $texto_impresion .= "----------------------------------------" . $CRLF;
            $texto_impresion .= alinea("TOTAL", 0, 12) . alinea(showNumber(round($diaturno[$k]['tot_can'], 2)), 1, 9) . "     ".alinea(showNumber($diaturno[$k]['tot_imp']), 1, 9) . $CRLF . $CRLF;
        }

        $file = "/sistemaweb/tmp/imprimir/acumula_turno.txt";
        $fh = fopen($file, "a");
        fwrite($fh, $texto_impresion . PHP_EOL . PHP_EOL . PHP_EOL);
        fclose($fh);

        error_log("****** Texto ******");
		error_log($texto_impresion);
        return $texto_impresion;
    }

    function repExcel($acumula) {

        $workbook = new Workbook($chrFileName);
        $formato0 = & $workbook->add_format();
        $formato1 = & $workbook->add_format();
        $formato2 = & $workbook->add_format();
        $formato3 = & $workbook->add_format();
        $formato4 = & $workbook->add_format();
        $formato5 = & $workbook->add_format();

        $formato0->set_size(11);
        $formato0->set_bold(1);
        $formato0->set_align('left');
        $formato1->set_top(1);
        $formato1->set_left(1);
        $formato1->set_border(0);
        $formato1->set_bold(1);
        $formato2->set_size(10);
        $formato2->set_bold(1);
        $formato2->set_align('center');
        $formato3->set_num_format(2);
        $formato4->set_num_format(2);
        $formato4->set_bold(1);
        $formato5->set_size(11);
        $formato5->set_align('left');

        $worksheet1 = & $workbook->add_worksheet('Hoja de Resultados');
        $worksheet1->set_column(0, 0, 50);
        $worksheet1->set_column(1, 1, 12);
        $worksheet1->set_column(2, 2, 12);

        $worksheet1->set_zoom(100);
        $worksheet1->set_landscape(100);

        $worksheet1->write_string(1, 0, "VENTAS ACUMULADAS POR TURNO", $formato0);
        $worksheet1->write_string(3, 0, "EESS: " . $acumula['info']['almacen'], $formato0);
        $worksheet1->write_string(4, 0, "ANIO: " . $acumula['info']['periodo'] . "   MES: " . $acumula['info']['mes'], $formato0);
        $worksheet1->write_string(5, 0, "DEL " . $acumula['info']['desde'] . " AL " . $acumula['info']['hasta'], $formato0);

        $a = 7;
        $diaturno = $acumula['header'];
        $resul = $acumula['body'];

        for ($k = 0; $k < count($diaturno); $k++) {
            $worksheet1->write_string($a, 0, "DIA: " . $diaturno[$k]['dia'] . $CRLF . "  TURNO: " . $diaturno[$k]['turno'], $formato1);
            $a++;
//          $worksheet1->write_string($a, 0, "DESCRIPCION",$formato2);
//          $worksheet1->write_string($a, 1, "CANTIDAD",$formato2);
            $worksheet1->write_string($a, 2, "TOTAL", $formato2);

            for ($j = 0; $j < count($resul); $j++) {
                if (($diaturno[$k]['dia'] == $resul[$j]['dia']) and ($diaturno[$k]['turno'] == $resul[$j]['turno'])) {
                    $a++;
                    if ($resul[$j]['producto'] != $resul[$j + 1]['producto']) {
                        $worksheet1->write_string($a, 0, $resul[$j]['producto'], $formato5);
                        $a++;
                        $worksheet1->write_string($a, 0, "CANTIDAD", $formato2);
                        $worksheet1->write_number($a, 1, number_format($resul[$j]['cantidad'], 2, '.', ''), $formato3);
                        $worksheet1->write_string($a, 2, "TOTAL", $formato2);
                        $worksheet1->write_number($a, 3, number_format($resul[$j]['importe'], 2, '.', ''), $formato3);
                    }
                }
            }
            $worksheet1->write_string($a, 0, "Total por Turno", $formato1);
            $worksheet1->write_number($a, 1, number_format($diaturno[$k]['tot_can'], 2, '.', ''), $formato4);
            $worksheet1->write_number($a, 2, number_format($diaturno[$k]['tot_imp'], 2, '.', ''), $formato4);
            $a++;
            $a++;
        }
        $workbook->close();

        $chrFileName = "acumulaporturno";
        header("Content-type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=$chrFileName.xls");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
        header("Pragma: public");
    }

}

function alinea($str, $tipo, $ll) {
    if ($tipo == 0)
        return ($str . espaciosA(($ll - strlen($str))));
    else if ($tipo == 1)
        return (espaciosA(($ll - strlen($str))) . $str);
    return (espaciosA((($ll / 2) - (strlen($str) / 2))) . $str . espaciosA((($ll / 2) - (strlen($str) / 2))));
}

function showNumber($num) {
    return number_format(round($num, 2), 2, ".", "");
}

function espaciosA($q) {
    $ret = "";
    for ($q; $q > 0; $q--)
        $ret .= " ";
    return $ret;
}


