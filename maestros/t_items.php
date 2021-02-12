<?php

//include "lib/paginador.php";

class ItemsTemplate extends Template {

    function titulo() {
        return '<div align="center"><h2>Maestro de Items</h2></div><hr>';
    }

    function listado($items, $pagina, $cuenta, $tipo_busqueda, $codigo, $descripcion, $ubicacion, $linea, $orderby, $order) {
     
        if ($codigo != "") {
            $_REQUEST['txtbusqueda'] = $codigo;
        } elseif ($descripcion != "") {
            $_REQUEST['txtbusqueda'] = $descripcion;
        } elseif ($ubicacion != "") {
            $_REQUEST['txtbusqueda'] = $ubicacion;
        } elseif ($linea != "") {
            $_REQUEST['txtbusqueda'] = $linea;
        }

        $bOficina = false;

        if ($tipo_busqueda != "")
            $url = 'control.php?rqst=MAESTROS.ITEMS&action=Buscar&txtbusqueda=' . $_REQUEST['txtbusqueda'] . '&criterio=' . $tipo_busqueda . '&pagina=';
        else
            $url = 'control.php?rqst=MAESTROS.ITEMS&action=&pagina=';

        $paginador = new CPaginador($cuenta, $url, "control", $pagina);

        $form = ItemsTemplate::formBuscar();
        $lista = $form->getForm();

//		$lista .= '<p align="center"><a href="/sistemaweb/maestros/reporteMaestros/listaPrecios_' . session_id() . '.csv">Reporte de Lista de Precios</a></p>';
        //$lista.='<p align="center"><button onClick="javascript:parent.location.href=\'/sistemaweb/maestros/excel_items.xls\';return false"><img src = "/sistemaweb/images/excel_icon.png" />Excel</button></p>';	


        $lista .= '<table style="border: 1px solid;" width="100%" cellpadding="0" cellspacing="0"><caption>Articulos</caption><tbody><tr>';
        $lista .= '
		<th style="border-bottom: 1px solid;" height="30px">Codigo';
        if (!($orderby == 'art_codigo' && $order == 'ASC'))
            $lista .= '<a href="control.php?rqst=MAESTROS.ITEMS&amp;action=Buscar&amp;pagina=' . urlencode($pagina) . '&amp;criterio=' . urlencode($tipo_busqueda) . '&amp;txtbusqueda=' . urlencode($_REQUEST['txtbusqueda']) . '&amp;orderby=art_codigo&amp;order=asc" target="control"  style="color: #ffffff;"><img src="images/arriba.gif"></a>';
        if (!($orderby == 'art_codigo' && $order == 'DESC'))
            $lista .= '<a href="control.php?rqst=MAESTROS.ITEMS&amp;action=Buscar&amp;pagina=' . urlencode($pagina) . '&amp;criterio=' . urlencode($tipo_busqueda) . '&amp;txtbusqueda=' . urlencode($_REQUEST['txtbusqueda']) . '&amp;orderby=art_codigo&amp;order=desc" target="control"  style="color: #ffffff;"><img src="images/abajo.gif"></a>';
        $lista .= '</th>';

        $lista .= '<th style="border-bottom: 1px solid;">Descripcion';
        if (!($orderby == 'art_descripcion' && $order == 'ASC'))
            $lista .= '<a href="control.php?rqst=MAESTROS.ITEMS&amp;action=Buscar&amp;pagina=' . urlencode($pagina) . '&amp;criterio=' . urlencode($tipo_busqueda) . '&amp;txtbusqueda=' . urlencode($_REQUEST['txtbusqueda']) . '&amp;orderby=art_descripcion&amp;order=asc" target="control" style="color: #ffffff;"><img src="images/arriba.gif"></a>';
        if (!($orderby == 'art_descripcion' && $order == 'DESC'))
            $lista .= '<a href="control.php?rqst=MAESTROS.ITEMS&amp;action=Buscar&amp;pagina=' . urlencode($pagina) . '&amp;criterio=' . urlencode($tipo_busqueda) . '&amp;txtbusqueda=' . urlencode($_REQUEST['txtbusqueda']) . '&amp;orderby=art_descripcion&amp;order=desc" target="control" style="color: #ffffff;"><img src="images/abajo.gif"></a>';
        $lista .= '</th>';

        $lista .= '<th style="border-bottom: 1px solid;">Precio';
        if (!($orderby == 'art_precio' && $order == 'ASC'))
            $lista .= '<a href="control.php?rqst=MAESTROS.ITEMS&amp;action=Buscar&amp;pagina=' . urlencode($pagina) . '&amp;criterio=' . urlencode($tipo_busqueda) . '&amp;txtbusqueda=' . urlencode($_REQUEST['txtbusqueda']) . '&amp;orderby=art_precio&amp;order=asc" target="control" style="color: #ffffff;"><img src="images/arriba.gif"></a>';
        if (!($orderby == 'art_precio' && $order == 'DESC'))
            $lista .= '<a href="control.php?rqst=MAESTROS.ITEMS&amp;action=Buscar&amp;pagina=' . urlencode($pagina) . '&amp;criterio=' . urlencode($tipo_busqueda) . '&amp;txtbusqueda=' . urlencode($_REQUEST['txtbusqueda']) . '&amp;orderby=art_precio&amp;order=desc" target="control" style="color: #ffffff;"><img src="images/abajo.gif"></a>';
        $lista .= '</th>';

        $lista .= '<th style="border-bottom: 1px solid;">Linea';
        if (!($orderby == 'art_linea' && $order == 'ASC'))
            $lista .= '<a href="control.php?rqst=MAESTROS.ITEMS&amp;action=Buscar&amp;pagina=' . urlencode($pagina) . '&amp;criterio=' . urlencode($tipo_busqueda) . '&amp;txtbusqueda=' . urlencode($_REQUEST['txtbusqueda']) . '&amp;orderby=art_linea&amp;order=asc" target="control" style="color: #ffffff;"><img src="images/arriba.gif"></a>';
        if (!($orderby == 'art_linea' && $order == 'DESC'))
            $lista .= '<a href="control.php?rqst=MAESTROS.ITEMS&amp;action=Buscar&amp;pagina=' . urlencode($pagina) . '&amp;criterio=' . urlencode($tipo_busqueda) . '&amp;txtbusqueda=' . urlencode($_REQUEST['txtbusqueda']) . '&amp;orderby=art_linea&amp;order=desc" target="control" style="color: #ffffff;"><img src="images/abajo.gif"></a>';
        $lista .= '</th>';

        $lista .= '<th style="border-bottom: 1px solid;">Tipo';
        if (!($orderby == 'art_tipo' && $order == 'ASC'))
            $lista .= '<a href="control.php?rqst=MAESTROS.ITEMS&amp;action=Buscar&amp;pagina=' . urlencode($pagina) . '&amp;criterio=' . urlencode($tipo_busqueda) . '&amp;txtbusqueda=' . urlencode($_REQUEST['txtbusqueda']) . '&amp;orderby=art_tipo&amp;order=asc" target="control" style="color: #ffffff;"><img src="images/arriba.gif"></a>';
        if (!($orderby == 'art_tipo' && $order == 'DESC'))
            $lista .= '<a href="control.php?rqst=MAESTROS.ITEMS&amp;action=Buscar&amp;pagina=' . urlencode($pagina) . '&amp;criterio=' . urlencode($tipo_busqueda) . '&amp;txtbusqueda=' . urlencode($_REQUEST['txtbusqueda']) . '&amp;orderby=art_tipo&amp;order=desc" target="control" style="color: #ffffff;"><img src="images/abajo.gif"></a>';
        $lista .= '</th>';

        $lista .= '<th style="border-bottom: 1px solid;">Unidad';
        if (!($orderby == 'art_unidad' && $order == 'ASC'))
            $lista .= '<a href="control.php?rqst=MAESTROS.ITEMS&amp;action=Buscar&amp;pagina=' . urlencode($pagina) . '&amp;criterio=' . urlencode($tipo_busqueda) . '&amp;txtbusqueda=' . urlencode($_REQUEST['txtbusqueda']) . '&amp;orderby=art_unidad&amp;order=asc" target="control" style="color: #ffffff;"><img src="images/arriba.gif"></a>';
        if (!($orderby == 'art_unidad' && $order == 'DESC'))
            $lista .= '<a href="control.php?rqst=MAESTROS.ITEMS&amp;action=Buscar&amp;pagina=' . urlencode($pagina) . '&amp;criterio=' . urlencode($tipo_busqueda) . '&amp;txtbusqueda=' . urlencode($_REQUEST['txtbusqueda']) . '&amp;orderby=art_unidad&amp;order=desc" target="control" style="color: #ffffff;"><img src="images/abajo.gif"></a>';
        $lista .= '</th>';

        $lista .= '<th style="border-bottom: 1px solid;">Ubicacion';
        if (!($orderby == 'art_ubicacion' && $order == 'ASC'))
            $lista .= '<a href="control.php?rqst=MAESTROS.ITEMS&amp;action=Buscar&amp;pagina=' . urlencode($pagina) . '&amp;criterio=' . urlencode($tipo_busqueda) . '&amp;txtbusqueda=' . urlencode($_REQUEST['txtbusqueda']) . '&amp;orderby=art_ubicacion&amp;order=asc" target="control" style="color: #ffffff;"><img src="images/arriba.gif"></a>';
        if (!($orderby == 'art_ubicacion' && $order == 'DESC'))
            $lista .= '<a href="control.php?rqst=MAESTROS.ITEMS&amp;action=Buscar&amp;pagina=' . urlencode($pagina) . '&amp;criterio=' . urlencode($tipo_busqueda) . '&amp;txtbusqueda=' . urlencode($_REQUEST['txtbusqueda']) . '&amp;orderby=art_ubicacion&amp;order=desc" target="control" style="color: #ffffff;"><img src="images/abajo.gif"></a>';
        $lista .= '</th>';

        $lista .= '<th style="border-bottom: 1px solid;">Codigo SKU';
        if (!($orderby == 'art_sku' && $order == 'ASC'))
            $lista .= '<a href="control.php?rqst=MAESTROS.ITEMS&amp;action=Buscar&amp;pagina=' . urlencode($pagina) . '&amp;criterio=' . urlencode($tipo_busqueda) . '&amp;txtbusqueda=' . urlencode($_REQUEST['txtbusqueda']) . '&amp;orderby=art_sku&amp;order=asc" target="control" style="color: #ffffff;"><img src="images/arriba.gif"></a>';
        if (!($orderby == 'art_sku' && $order == 'DESC'))
            $lista .= '<a href="control.php?rqst=MAESTROS.ITEMS&amp;action=Buscar&amp;pagina=' . urlencode($pagina) . '&amp;criterio=' . urlencode($tipo_busqueda) . '&amp;txtbusqueda=' . urlencode($_REQUEST['txtbusqueda']) . '&amp;orderby=art_sku&amp;order=desc" target="control" style="color: #ffffff;"><img src="images/abajo.gif"></a>';
        $lista .= '</th>';

        $lista .= '<th style="border-bottom: 1px solid;">Activo';
        if (!($orderby == 'art_activo' && $order == 'ASC'))
            $lista .= '<a href="control.php?rqst=MAESTROS.ITEMS&amp;action=Buscar&amp;pagina=' . urlencode($pagina) . '&amp;criterio=' . urlencode($tipo_busqueda) . '&amp;txtbusqueda=' . urlencode($_REQUEST['txtbusqueda']) . '&amp;orderby=art_activo&amp;order=asc" target="control" style="color: #ffffff;"><img src="images/arriba.gif"></a>';
        if (!($orderby == 'art_activo' && $order == 'DESC'))
            $lista .= '<a href="control.php?rqst=MAESTROS.ITEMS&amp;action=Buscar&amp;pagina=' . urlencode($pagina) . '&amp;criterio=' . urlencode($tipo_busqueda) . '&amp;txtbusqueda=' . urlencode($_REQUEST['txtbusqueda']) . '&amp;orderby=art_activo&amp;order=desc" target="control" style="color: #ffffff;"><img src="images/abajo.gif"></a>';
        $lista .= '</th>';

        $lista .= '<th style="border-bottom: 1px solid;">Stock';
        if (!($orderby == 'art_stock' && $order == 'ASC'))
            $lista .= '<a href="control.php?rqst=MAESTROS.ITEMS&amp;action=Buscar&amp;pagina=' . urlencode($pagina) . '&amp;criterio=' . urlencode($tipo_busqueda) . '&amp;txtbusqueda=' . urlencode($_REQUEST['txtbusqueda']) . '&amp;orderby=art_stock&amp;order=asc" target="control" style="color: #ffffff;"><img src="images/arriba.gif"></a>';
        if (!($orderby == 'art_stock' && $order == 'DESC'))
            $lista .= '<a href="control.php?rqst=MAESTROS.ITEMS&amp;action=Buscar&amp;pagina=' . urlencode($pagina) . '&amp;criterio=' . urlencode($tipo_busqueda) . '&amp;txtbusqueda=' . urlencode($_REQUEST['txtbusqueda']) . '&amp;orderby=art_stock&amp;order=desc" target="control" style="color: #ffffff;"><img src="images/abajo.gif"></a>';
        $lista .= '</th>';

        for ($i = 0; $i < count($items); $i++) {
            $item = $items[$i];
            $lista .= "<tr>";
            $lista .= "<td><a href=\"control.php?rqst=MAESTROS.ITEMS&action=Modificar&codigo=" . $item[0] . "\" target=\"control\">" . $item[0] . "</a></td>";
            $lista .= "<td>" . $item[1] . "</td>";
            $lista .= "<td>" . $item[9] . "</td>";
            $lista .= "<td>" . $item[2] . "</td>";
            $lista .= "<td>" . $item[3] . "</td>";
            $lista .= "<td>" . $item[4] . "</td>";
            $lista .= "<td align='center'>" . $item[5] . "</td>";
            $lista .= "<td>" . $item[6] . "</td>";
            $lista .= "<td>" . ($item[7] == "0" ? "Si" : "No") . "</td>";
            $lista .= "<td>" . $item[8] . "</td>";
            $lista .= "</tr>\n";
        }

        $lista .= '
		</tr>
		</tbody>
		</table>
		';

        if (count($items) == 0) {
            $lista .= "<tr>";
            $lista .= "<td colspan=\"6\">No hay registros</td>";
            $lista .= "</tr>";
        }

        $lista .= '<table width="100%"><tr><td width="50%">' . $paginador->obtienePaginador() . '</td><td align="right">';
        $lista .= 'Mostrando del ' . $paginador->desde() . ' al ' . $paginador->hasta() . ' de ' . $cuenta . '</td></tr></table>';
        $lista .= '
		</div>
		<br>';

        return $lista;
    }

    function formBuscar() {
        $bOficina = false;
        $form = new form('', "Buscar", FORM_METHOD_POST, "control.php", '', "control");
        $form->addElement(FORM_GROUP_MAIN, new form_element_text("Buscar:", "txtbusqueda", isset($_REQUEST['txtbusqueda']) ? htmlentities($_REQUEST['txtbusqueda']) : '', '', 15, 25, false, ""));
        $form->addElement(FORM_GROUP_MAIN, new form_element_submit("action", "Buscar", '', '', 20));
        $form->addElement(FORM_GROUP_MAIN, new form_element_submit("action", "Agregar", '', '', 20));
        $form->addElement(FORM_GROUP_MAIN, new form_element_hidden("rqst", "MAESTROS.ITEMS", '', '', 20));
        $form->addElement(FORM_GROUP_MAIN, new form_element_hidden("pagina", "1", '', '', 20));

        $valores = Array("codigo" => "Codigo", "descripcion" => "Descripcion", "ubicacion" => "Ubicaci&oacute;n ", "linea" => "Linea");

        $form->addElement(FORM_GROUP_MAIN, new form_element_radio('', "criterio", isset($_REQUEST['criterio']) ? htmlentities($_REQUEST['criterio']) : "descripcion", "<br>", '', '', $valores));
        $form->addElement(FORM_GROUP_MAIN, new form_element_freeTags('<button name="action" type="submit" value="Excel"><img src="/sistemaweb/images/excel_icon.png" alt="left"/> Excel</button>&nbsp;&nbsp;&nbsp;'));

        return $form;
    }

    function formModificar($item, $lineas, $tipos, $marcas, $plus, $unidades, $ubicaciones, $Impuestos) {
        $sino = Array("S" => "Si", "N" => "No");
        $MarcasCB = VariosModel::MarcasItemsCBArray();
        $ItemsSKU = ItemsModel::ObtieneItemSKU();
        $unidad_prese = ItemsModel::ObtieneTablaGeneral('35');

        if ($_REQUEST['cod']) {
            $ValCbTipos = $_REQUEST['cod'];
        } elseif ($_REQUEST['combotipos'] && !$_REQUEST['cod']) {
            $ValCbTipos = $_REQUEST['combotipos'];
        } else {
            $ValCbTipos = trim($item['art_tipo']);
        }
        foreach ($_REQUEST as $llave => $valor) {
            if ($llave != 'sku' && $llave != 'impuesto' && $valor == 'all') {
                $disabled = 'disabled';
            }
        }
        $form = new form('', "Modificar", FORM_METHOD_POST, "control.php", '', "control");
        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("codigo", $item['art_codigo']));
        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MAESTROS.ITEMS"));
        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("action", "Modificar.Grabar"));
        $form->addGroup("GRUPO_GENERAL", "DATOS GENERALES");

        $form->addElement("GRUPO_GENERAL", new form_element_freeTags('<TABLE border="0" cellspacing="2" cellpadding="2"><tr><td class="form_label">'));

        $form->addElement("GRUPO_GENERAL", new form_element_freeTags('Codigo' . espacios(23) . '</td><td class="form_label">: <b>' . trim($item['art_codigo']) . '</b>'));
        $form->addElement("GRUPO_GENERAL", new form_element_freeTags('</td></tr><tr><td>'));

        $form->addElement("GRUPO_GENERAL", new form_element_text('Descripcion' . espacios(15) . '</td><td>:', "txtdescripcion", trim($item['art_descripcion']), "", "", 65, 55, false, 'onchange="javascript:rellenarCampo(this,forms[0].txtdescbreve);" onkeyup="javascript:this.value=this.value.toUpperCase();"'));
        $form->addElement("GRUPO_GENERAL", new form_element_freeTags('</td></tr><tr><td>'));

        $form->addElement("GRUPO_GENERAL", new form_element_text('Descripcion breve' . espacios(4) . '</td><td>:', "txtdescbreve", trim($item['art_descbreve']), "", "", 25, 20, false, ""));
        $form->addElement("GRUPO_GENERAL", new form_element_freeTags('</td></tr><tr><td>'));

        $form->addElement("GRUPO_GENERAL", new form_element_combo("Tipo" . espacios(29) . "</td><td>:", "combotipos", $ValCbTipos, "", "", 1, $tipos, false, 'onChange="javascript:getTipoLinea2(forms[0].codigo.value,this.options[this.selectedIndex].value)"'));
        $form->addElement("GRUPO_GENERAL", new form_element_freeTags('</td></tr><tr><td>'));

        $form->addElement("GRUPO_GENERAL", new form_element_combo("Linea" . espacios(26) . "</td><td>:", "combolineas", $_REQUEST['combolineas'] ? $_REQUEST['combolineas'] : $item['art_linea'], "", "", 1, $lineas, false, ""));
        $form->addElement("GRUPO_GENERAL", new form_element_freeTags('</td></tr><tr><td>'));

        $form->addElement("GRUPO_GENERAL", new form_element_combo("Marca" . espacios(25) . "</td><td>:", "combomarcas", $_REQUEST['combomarcas'] ? $_REQUEST['combomarcas'] : $item['art_clase'], "", "", 1, $MarcasCB, false, ""));
        $form->addElement("GRUPO_GENERAL", new form_element_freeTags('</td></tr><tr><td>'));

        $form->addElement("GRUPO_GENERAL", new form_element_combo("Tipo PLU" . espacios(20) . "</td><td>:", "comboplu", $_REQUEST['comboplu'] ? $_REQUEST['comboplu'] : $item['art_plutipo'], "", "", 1, $plus, false, 'onchange="pluUpdate(this.value)"'));
        $form->addElement("GRUPO_GENERAL", new form_element_freeTags('</td></tr><tr><td>'));

        $form->addElement("GRUPO_GENERAL", new form_element_combo("C&oacute;digo de Impuesto</td><td>:", "impuesto", $_REQUEST['impuesto'] ? $_REQUEST['impuesto'] : $item['art_impuesto1'], "", '', 1, $Impuestos, false, ''));
        $form->addElement("GRUPO_GENERAL", new form_element_freeTags('</td></tr><tr><td>'));

        $form->addElement("GRUPO_GENERAL", new form_element_combo("Codigo SKU" . espacios(14) . "</td><td>:", "sku", $_REQUEST['sku'] ? $_REQUEST['sku'] : $item['art_cod_sku'], "", '', 1, $ItemsSKU, false, ''));
        $form->addElement("GRUPO_GENERAL", new form_element_freeTags('</td></tr><tr><td>'));

        $form->addElement("GRUPO_GENERAL", new form_element_freeTags('</td><td>'));
        $form->addElement("GRUPO_GENERAL", new form_element_freeTags('</td></tr><tr><td>'));

        $form->addElement("GRUPO_GENERAL", new form_element_combo("Activo" . espacios(26) . "</td><td>:", "activo", $_REQUEST['activo'] ? $_REQUEST['activo'] : ($item['art_estado'] == "0" ? "S" : "N"), "", "", 1, $sino, false, ""));

        $form->addElement("GRUPO_GENERAL", new form_element_freeTags('</td></tr></table>'));

        $form->addGroup("GRUPO_ESTANDARD", "ITEM ESTANDARD", $item['art_plutipo'] == 1 || $item['art_plutipo'] == 3 ? "inline" : "inline");

        $form->addElement("GRUPO_ESTANDARD", new form_element_freeTags('<table border="0" cellspacing="3" cellpadding="3"><tr><td>'));

        $form->addElement("GRUPO_ESTANDARD", new form_element_combo("Unid. de medida" . espacios(10) . "</td><td>:", "combounidades", $_REQUEST['combounidades'] ? $_REQUEST['combounidades'] : trim($item['art_unidad']), "", "", 1, $unidades, false, ""));
        $form->addElement("GRUPO_ESTANDARD", new form_element_freeTags('</td></tr><tr><td>'));

        $form->addElement("GRUPO_ESTANDARD", new form_element_combo("Unid. de Presentaci&oacute;n</td><td>:", "art_presentacion", $_REQUEST['art_presentacion'] ? $_REQUEST['art_presentacion'] : trim($item['art_presentacion']), "", "", 1, $unidad_prese, false, ""));
        $form->addElement("GRUPO_ESTANDARD", new form_element_freeTags('</td></tr><tr><td>'));

        $form->addElement("GRUPO_ESTANDARD", new form_element_combo("Ubicaci&oacute;n" . espacios(22) . "</td><td>:", "comboubicaciones", $_REQUEST['comboubicaciones'] ? $_REQUEST['comboubicaciones'] : trim($item['art_cod_ubicac']), "", "", 1, $ubicaciones, false, ""));
        $form->addElement("GRUPO_ESTANDARD", new form_element_freeTags('</td></tr><tr><td>'));

        $form->addElement("GRUPO_ESTANDARD", new form_element_freeTags('</td><td>'));
        $form->addElement("GRUPO_ESTANDARD", new form_element_freeTags('</td></tr><tr><td>'));

        $form->addElement("GRUPO_ESTANDARD", new form_element_text("Plazo de reposicion promedio</td><td>:", "plzreposicion", $_REQUEST['plzreposicion'] ? $_REQUEST['plzreposicion'] : $item['art_plazoreposicprom'], "", "", 10, 20, false, ""));
        $form->addElement("GRUPO_ESTANDARD", new form_element_freeTags('</td></tr><tr><td>'));

        $form->addElement("GRUPO_ESTANDARD", new form_element_text("Dias de posici&oacute;n" . espacios(24) . "</td><td>:", "diasreposicion", $_REQUEST['diasreposicion'] ? $_REQUEST['diasreposicion'] : $item['art_diasreposic'], "", "", 10, 20, false, ""));
        $form->addElement("GRUPO_ESTANDARD", new form_element_freeTags('</td></tr><tr><td>'));


        $form->addElement("GRUPO_ESTANDARD", new form_element_text("Costo Inicial" . espacios(32) . "</td><td>:", "art_costoinicial", $_REQUEST['art_costoinicial'] ? $_REQUEST['art_costoinicial'] : $item['art_costoinicial'], "", '', 12, 10, false, ""));
        $form->addElement("GRUPO_ESTANDARD", new form_element_freeTags('</td></tr><tr><td>'));

        $form->addElement("GRUPO_ESTANDARD", new form_element_text("Costo de Reposici&oacute;n" . espacios(17) . "</td><td>:", "art_costoreposicion", $_REQUEST['art_costoreposicion'] ? $_REQUEST['art_costoreposicion'] : $item['art_costoreposicion'], "", "", 12, 10, true, ""));
        $form->addElement("GRUPO_ESTANDARD", new form_element_freeTags('</td></tr><tr><td>'));

        $form->addElement("GRUPO_ESTANDARD", new form_element_text("Precio de Venta" . espacios(8) . "</td><td>:", "precio",
                        $_REQUEST['precio'] ? $_REQUEST['precio'] : $item['pre_precio_act1'], "", '', 5, 5, false, ''));
        $form->addElement("GRUPO_ESTANDARD", new form_element_freeTags('</td></tr></table>'));

        $form->addGroup("GRUPO_BOTONES", "");
        $form->addElement("GRUPO_BOTONES", new form_element_submit("sbmt", "Guardar", '', '', 20));

        $form->addElement("GRUPO_BOTONES", new form_element_button("btEnlaces", "Enlaces", '', '', 20, 'onclick="openEnlaces()"', false, $item['art_plutipo'] == 1 || $item['art_plutipo'] == 3 ? "none" : "inline"));
        $form->addElement("GRUPO_BOTONES", new form_element_button("btPrecios", "Listas de precios", '', '', 20, 'onclick="openListaPrecios()"', false, ""));

        $form->addElement("GRUPO_BOTONES", new form_element_button("btRegresar", "Regresar", '', '', 20, 'onclick="regresarMaesItems()" ' . $disabled . '', false));

        return $form->getForm();
    }

    function mostrarError($valor, $funcion) {
        $result = "" . $_REQUEST['manual'];

        if ($valor) {
            switch ($funcion) {
                case "Modificar.Grabar":
                    $result .= "<center><b>Actualizacion de item correcta.</b></center>";
                    break;
                default:
                    $result .= "<center><b>La funcion se realizo correctamente.</b></center>";
            }
        } else {

            switch ($funcion) {
                case "Modificar.Grabar":
                    switch ($_REQUEST['combotipos']) {
                        case "all":
                            $result .= "<center style='color:#FF1601;'><blink>Error Ingresando, debe seleccionar un Tipo de Linea</blink></center>";
                            break;
                        default:
                            break;
                    }

                    switch ($_REQUEST['combolineas']) {
                        case "all":
                            $result .= "<center style='color:#FF1601;'><blink>Error Ingresando, debe seleccionar la Linea</blink></center>";
                            break;
                        default:
                            break;
                    }

                    switch ($_REQUEST['combomarcas']) {
                        case "all":
                            $result .= "<center style='color:#FF1601;'><blink>Error Ingresando, debe seleccionar la Marca</blink></center>";
                            break;
                        default:
                            break;
                    }

                    /* switch ($_REQUEST['sku']) {
                      case "all":
                      $result .= "<center style='color:#FF1601;'><blink>Error Ingresando, debe seleccionar el C&oacute;digo SKU.</blink></center>";
                      break;
                      default:
                      //$result .= "<center style='color:#FF1601;'><blink>Error Ingresando item</blink></center>";
                      break;
                      } */

                    switch ($_REQUEST['combounidades']) {
                        case "all":
                            $result .= "<center style='color:#FF1601;'><blink>Error Ingresando, debe seleccionar la Unidad.</blink></center>";
                            break;
                        default:
                            break;
                    }

                    switch ($_REQUEST['art_presentacion']) {
                        case "all":
                            $result .= "<center style='color:#FF1601;'><blink>Error Ingresando, debe seleccionar la Unid. Presentacion.</blink></center>";
                            break;
                        default:
                            break;
                    }

                    switch ($_REQUEST['comboubicaciones']) {
                        case "all":
                            $result .= "<center style='color:#FF1601;'><blink>Error Ingresando, debe seleccionar la Ubicaci&oacute;n</blink></center>";
                            break;
                        default:
                            break;
                    }

                    break;

                case "Agregar.Action":

                    switch ($_REQUEST['tipo']) {
                        case "all":
                            $result .= "<center style='color:#FF1601;'><blink>Error Ingresando, debe seleccionar un Tipo de Linea</blink></center>";
                            break;
                        default:
                            break;
                    }

                    switch ($_REQUEST['linea']) {
                        case "all":
                            $result .= "<center style='color:#FF1601;'><blink>Error Ingresando, debe seleccionar la Linea</blink></center>";
                            break;
                        default:
                            break;
                    }

                    switch ($_REQUEST['marca']) {
                        case "all":
                            $result .= "<center style='color:#FF1601;'><blink>Error Ingresando, debe seleccionar la Marca</blink></center>";
                            break;
                        default:
                            break;
                    }

                    /* switch ($_REQUEST['sku']) {
                      case "all":
                      $result .= "<center style='color:#FF1601;'><blink>Error Ingresando, debe seleccionar el C&oacute;digo SKU.</blink></center>";
                      break;
                      default:
                      break;
                      } */

                    switch ($_REQUEST['unidad']) {
                        case "all":
                            $result .= "<center style='color:#FF1601;'><blink>Error Ingresando, debe seleccionar la Unidad.</blink></center>";
                            break;
                        default:
                            break;
                    }

                    switch ($_REQUEST['art_presentacion']) {
                        case "all":
                            $result .= "<center style='color:#FF1601;'><blink>Error Ingresando, debe seleccionar la Unid. Presentacion.</blink></center>";
                            break;
                        default:
                            break;
                    }

                    switch ($_REQUEST['ubicacion']) {
                        case "all":
                            $result .= "<center style='color:#FF1601;'><blink>Error Ingresando, debe seleccionar la Ubicaci&oacute;n</blink></center>";
                            break;
                        default:
                            break;
                    }

                    break;

                default:
                    $result .= "<center><b>Error generico</b></center>";
                    break;
            }
        }

        return $result;
    }

    function enlacesPrincipal($item) {
        $result = "Codigo: " . htmlentities($item['art_codigo']) . "<br>";
        $result .= "Descripcion: " . htmlentities($item['art_descripcion']) . "<br>";

        return $result;
    }

    function enlacesItems($item, $_codigo) {
        $result = '<form name="enlaces" method="POST" target="control" action="control.php">';
        $result .= "<center><table border=1><caption>Enlaces para el item</caption><tbody>";
        $result .= "<tr><th>Codigo</th><th>Descripcion</th><th>Cantidad</th><th>&nbsp;</th></tr>";

        foreach ($item as $codigo => $data) {
            $result .= "<tr><td><a href=\"control.php?rqst=MAESTROS.ITEMS&action=Enlaces.Forms&method=Modificar&codigo=" . htmlentities($_codigo) . "&enlace=" . htmlentities($codigo) . "&cantidad=" . htmlentities($data['cantidad']) . "\" target=\"control\">" . htmlentities($codigo) . "</a></td>";
            $result .= "<td>" . htmlentities($data['descripcion']) . "</td>";
            $result .= "<td>" . htmlentities($data['cantidad']) . "</td>";
            $result .= '<td><input type="checkbox" name="checks[]" value="' . htmlentities($codigo) . '"></td>';
            $result .= "</tr>";
        }

        if (count($item) == 0)
            $result .= '<td colspan="4" align="center">No hay enlaces</td>';
        $result .= "</tbody></table>";

        $result .= '<input type="hidden" name="rqst" value="MAESTROS.ITEMS">';
        $result .= '<input type="hidden" name="action" id="action" value="Enlaces.Forms">';
        $result .= '<input type="hidden" name="codigo" value="' . htmlentities($_codigo) . '">';
        $result .= '<input type="submit" name="method" value="Agregar">';
        $result .= '<input type="submit" name="method" value="Borrar" onclick="borrarEnlaces()">';

        $result .= "</center></form>";

        return $result;
    }

    function enlacesControles() {
        $result = "";
        $result .= '<input type="button" name="Terminar" value="Terminar" onclick="enlacesCerrar()">';

        return $result;
    }

    function formEnlaceAgregar($codigo) {
        $form = new form('', "enlaceAgregar", FORM_METHOD_POST, "control.php", '', "control");

        $form->addGroup("GRUPO_PRINCIPAL", "");
        $form->addElement("GRUPO_PRINCIPAL", new form_element_text("Codigo: ", "enlace", '', '<br>', '', 13, 13, false, 'onblur="updateDescripcion(this)" onchange="updateDescripcion(this)"'));
        $form->addElement("GRUPO_PRINCIPAL", new form_element_text("Cantidad: ", "cantidad", '', '<br>', '', 4, 16, false, ""));
        $form->addGroup("GRUPO_AYUDA", "Ayuda", "none");
        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MAESTROS.ITEMS"));
        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("action", "Enlaces.Action"));
        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("codigo", htmlentities($codigo)));
        $form->addGroup("GRUPO_BOTONES", "");
        $form->addElement("GRUPO_BOTONES", new form_element_submit("method", "Agregar", '', '', 20));
        $form->addElement("GRUPO_BOTONES", new form_element_button("btRegresar", "Regresar", '', '', 20, 'onclick="regresarEnlace(\'' . $codigo . '\')"', false));

        $result = $form->getForm();
        $result .= '<div id="ayuda">&nbsp;</div>';

        return $form->getForm();
    }

    function formEnlaceModificar($codigo, $item, $cantidad) {
        $form = new form('', "enlaceModificar", FORM_METHOD_POST, "control.php", '', "control");
        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MAESTROS.ITEMS"));
        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("action", "Enlaces.Action"));
        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("codigo", htmlentities($codigo)));
        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("enlace", htmlentities($item['art_codigo'])));
        $form->addElement(FORM_GROUP_MAIN, new form_element_onlytext('', "Codigo: " . $item['art_codigo'], "<br>"));
        $form->addElement(FORM_GROUP_MAIN, new form_element_onlytext('', $item['art_descripcion'], "<br>"));
        $form->addElement(FORM_GROUP_MAIN, new form_element_text("Cantidad:", "cantidad", htmlentities($cantidad), "<br>", '', 4, 16, false, ""));
        $form->addElement(FORM_GROUP_MAIN, new form_element_submit("method", "Modificar", '', '', 20));
        $form->addElement(FORM_GROUP_MAIN, new form_element_button("btRegresar", "Regresar", '', '', 20, 'onclick="regresarEnlace(\'' . $codigo . '\')"', false));

        return $form->getForm();
    }

    function enviarMargenLinea($margen) {
        return '<script language="JavaScript">parent.document.getElementsByName(\'margen_linea\')[0].value=' . (1 - ($margen / 100)) . ';</script>';
    }

    function formAgregar($tipos, $lineas, $plus, $unidades, $ubicaciones, $Impuestos, $CodManual) {
        echo "hola";
        
        $sino = Array("S" => "Si", "N" => "No");
        $codigo = str_pad($_REQUEST['codigo'], 13, "0", STR_PAD_LEFT);

        $MarcasCB = VariosModel::MarcasItemsCBArray();
        $ItemsSKU = ItemsModel::ObtieneItemSKU();
        $unidad_prese = ItemsModel::ObtieneTablaGeneral('35');

        foreach ($_REQUEST as $llave => $valor) {
            //echo "llave : $llave = VALOR : $valor \n";
        }

        foreach ($_REQUEST as $llave => $valor) {
            if ($llave != 'sku' && $llave != 'impuesto' && $valor == 'all') {
                $disabled = 'disabled';
            }
        }

        $form = new form('', "itemAgregar", FORM_METHOD_POST, "control.php", '', "control");
        
        if (ItemsModel::obtieneItem($codigo) != null && $codigo != "0000000000000") {
            $form->addElement(FORM_GROUP_MAIN, new form_element_onlytext('', "El codigo especificado ya existe", "<br>", "align:center"));
            $codigo = "0000000000000";
        }

        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MAESTROS.ITEMS"));
        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("margen_linea", "0"));

        if ($codigo != "0000000000000") {
            $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("action", "Agregar.Action"));
            $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("codigo2", htmlentities($codigo)));
            if ($_REQUEST['manual'] == '1' || $CodManual == 'si') {
                $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("manual", "si"));
            } else {
                $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("manual", "no"));
            }
        } else
            $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("action", "Agregar"));

        $form->addGroup("GRUPO_CODIGO", $codigo == "0000000000000" ? "INGRESE NUEVO CODIGO" : "AGREGAR ITEM");

        if ($codigo != "0000000000000") {
            $form->addElement("GRUPO_CODIGO", new form_element_freeTags('<table border="0" cellspacing="2" cellpadding="2"> <tr><td>'));
            $form->addElement("GRUPO_CODIGO", new form_element_freeTags('<span class="form_label">Codigo ' . espacios(2) . ': <b>' . trim($codigo) . '</b></span>'));
            $form->addElement("GRUPO_CODIGO", new form_element_freeTags('</td></tr></table>'));
        } else {
            $form->addElement("GRUPO_CODIGO", new form_element_freeTags('<table border="0" cellspacing="2" cellpadding="2"> <tr><td>'));
            $form->addElement("GRUPO_CODIGO", new form_element_text("Codigo:", "codigo", $CodManual, "", '', 20, 13, false, 'onKeyUp="javascript:setNumerosLetras("codigo")";'));
            //$form->addElement("GRUPO_CODIGO", new form_element_text("Codigoasdasd: ", "codigo2", $CodManual, "", '', 20, 13, false, 'onkeyup="javascript:this.value=this.value.toUpperCase();"'));
            $form->addElement("GRUPO_CODIGO", new form_element_freeTags('</td></tr><tr><td>'));
            $form->addElement("GRUPO_CODIGO", new form_element_checkbox('C&oacute;digo Manual :', 'manual', $_REQUEST['check'], '', '', 'onClick="DevuelveCodManual(this,document.getElementsByName(\'codigo\')[0])"'));
            $form->addElement("GRUPO_CODIGO", new form_element_freeTags('</td></tr></table>'));
        }


        if ($codigo != "0000000000000") {
            $form->addGroup("GRUPO_DATOS", "CONFIGURACION DEL ITEM");
            $form->addElement("GRUPO_DATOS", new form_element_freeTags('<table border="0" cellspacing="2" cellpadding="2"> <tr><td>'));
            $form->addElement("GRUPO_DATOS", new form_element_text("Descripci&oacute;n" . espacios(15) . "</td><td>:", "descripcion",
                            $_REQUEST['descripcion'], "", '', 65, 55, false, 'onchange="javascript:rellenarCampo(this,forms[0].descbreve);" onkeyup="javascript:this.value=this.value.toUpperCase();"'));
            $form->addElement("GRUPO_DATOS", new form_element_freeTags('</td></tr><tr><td>'));

            $form->addElement("GRUPO_DATOS", new form_element_text("Descripci&oacute;n breve" . espacios(4) . "</td><td>:", "descbreve",
                            $_REQUEST['descbreve'], "", '', 25, 20, false, ''));
            $form->addElement("GRUPO_DATOS", new form_element_freeTags('</td></tr><tr><td>'));

            $form->addElement("GRUPO_DATOS", new form_element_combo("TipoL" . espacios(26) . "</td><td>:", "tipo",
                            $_REQUEST['cod'] ? $_REQUEST['cod'] : $_REQUEST['tipo'], "", '', 1, $tipos, false, 'onChange="javascript:getTipoLinea(forms[0].codigo.value,this.options[this.selectedIndex].value,forms[0].descripcion.value,forms[0].descbreve.value,forms[0].manual.value)"'));
            $form->addElement("GRUPO_DATOS", new form_element_freeTags('</td></tr><tr><td>'));

            $form->addElement("GRUPO_DATOS", new form_element_combo("Linea" . espacios(26) . "</td><td>:", "linea",
                            $_REQUEST['linea'], "", '', 1, $lineas, false, 'onblur="control.location.href=\'control.php?rqst=MAESTROS.ITEMS&action=ObtenerMargenLinea&linea=\'+document.getElementsByName(\'linea\')[0].value;"'));
            $form->addElement("GRUPO_DATOS", new form_element_freeTags('</td></tr><tr><td>'));

            $form->addElement("GRUPO_DATOS", new form_element_combo("Marca" . espacios(25) . "</td><td>:", "marca",
                            $_REQUEST['marca'], "", '', 1, $MarcasCB, false, ''));
            $form->addElement("GRUPO_DATOS", new form_element_freeTags('</td></tr><tr><td>'));

            $form->addElement("GRUPO_DATOS", new form_element_combo("Tipo PLU" . espacios(20) . "</td><td>:", "plu",
                            $_REQUEST['plu'], "", '', 1, $plus, false, 'onchange="agregarUpdatePLU(this.value)"'));
            $form->addElement("GRUPO_DATOS", new form_element_freeTags('</td></tr><tr><td>'));

            $form->addElement("GRUPO_DATOS", new form_element_combo("C&oacute;digo de Impuesto" . espacios(0) . "</td><td>:", "impuesto", '000009', "", '', 1, $Impuestos, false, ''));
            $form->addElement("GRUPO_DATOS", new form_element_freeTags('</td></tr><tr><td>'));


            $form->addElement("GRUPO_DATOS", new form_element_combo("C&oacute;digo SKU" . espacios(14) . "</td><td>:", "sku",
                            $_REQUEST['sku'], "", '', 1, $ItemsSKU, false, ''));
            $form->addElement("GRUPO_DATOS", new form_element_freeTags('</td></tr><tr><td>'));

            $form->addElement("GRUPO_DATOS", new form_element_freeTags('</td><td>'));
            $form->addElement("GRUPO_DATOS", new form_element_freeTags('</td></tr><tr><td>'));

            $form->addElement("GRUPO_DATOS", new form_element_combo("Activo" . espacios(26) . "</td><td>:", "activo", "S", "", "", 1, $sino, false, ""));
            $form->addElement("GRUPO_DATOS", new form_element_freeTags('</td></tr></table>'));

            $form->addGroup("GRUPO_ESTANDARD", "DETALLES DEL PRODUCTO");
            $form->addElement("GRUPO_ESTANDARD", new form_element_freeTags('<table border="0" cellspacing="2" cellpadding="2"> <tr><td class="form_label">'));

            $form->addElement("GRUPO_ESTANDARD", new form_element_combo("Unidad de medida" . espacios(10) . "</td><td>:", "unidad",
                            $_REQUEST['unidad'], "", '', 1, $unidades, false, ''));
            $form->addElement("GRUPO_ESTANDARD", new form_element_freeTags('</td></tr><tr><td>'));

            $form->addElement("GRUPO_ESTANDARD", new form_element_combo("Unid. de Presentaci&oacute;n" . espacios(3) . "</td><td>:", "art_presentacion",
                            $_REQUEST['art_presentacion'], "", "", 1, $unidad_prese, false, ""));
            $form->addElement("GRUPO_ESTANDARD", new form_element_freeTags('</td></tr><tr><td>'));

            $form->addElement("GRUPO_ESTANDARD", new form_element_combo("Ubicacion" . espacios(25) . "</td><td>:", "ubicacion",
                            $_REQUEST['ubicacion'], "", '', 1, $ubicaciones, false, ''));
            $form->addElement("GRUPO_ESTANDARD", new form_element_freeTags('</td></tr><tr><td>'));

            $form->addElement("GRUPO_ESTANDARD", new form_element_freeTags('</td><td>'));
            $form->addElement("GRUPO_ESTANDARD", new form_element_freeTags('</td></tr><tr><td>'));

            $form->addElement("GRUPO_ESTANDARD", new form_element_text("Plazo de reposicion promedio" . espacios(0) . "</td><td>:", "reposicion",
                            $_REQUEST['reposicion'], "", '', 15, 20, false, ''));
            $form->addElement("GRUPO_ESTANDARD", new form_element_freeTags('</td></tr><tr><td>'));

            $form->addElement("GRUPO_ESTANDARD", new form_element_text("Dias de reposicion" . espacios(20) . "</td><td>:", "dias",
                            $_REQUEST['dias'], "", '', 15, 20, false, ''));
            $form->addElement("GRUPO_ESTANDARD", new form_element_freeTags('</td></tr><tr><td>'));

            $form->addElement("GRUPO_ESTANDARD", new form_element_text("Costo Inicial" . espacios(31) . "</td><td>:", "art_costoinicial",
                            $_REQUEST['art_costoinicial'], "", '', 12, 10, false, "onblur='document.getElementsByName(\"art_costoreposicion\")[0].value=document.getElementsByName(\"art_costoinicial\")[0].value; document.getElementsByName(\"precio\")[0].value = ((document.getElementsByName(\"art_costoinicial\")[0].value * 1.18) / document.getElementsByName(\"margen_linea\")[0].value).toFixed(2);'"));
            $form->addElement("GRUPO_ESTANDARD", new form_element_freeTags('</td></tr><tr><td>'));

            $form->addElement("GRUPO_ESTANDARD", new form_element_text("Costo de Reposici&oacute;n" . espacios(16) . "</td><td>:", "art_costoreposicion", $_REQUEST['art_costoreposicion'], "", "", 12, 10, false, ""));
            $form->addElement("GRUPO_ESTANDARD", new form_element_freeTags('</td></tr><tr><td>'));

            $form->addElement("GRUPO_ESTANDARD", new form_element_text("Precio de Venta" . espacios(8) . "</td><td>:", "precio",
                            $_REQUEST['precio'] ? $_REQUEST['precio'] : '0.00', "<br>", '', 5, 5, false, ''));
            $form->addElement("GRUPO_ESTANDARD", new form_element_freeTags('</td></tr></table>'));
        }

        $form->addGroup("GRUPO_BOTONES", "");

        if ($codigo != "0000000000000")
            $form->addElement("GRUPO_BOTONES", new form_element_submit("go", "Guardar", '', '', 20));

        $form->addElement("GRUPO_BOTONES", new form_element_button("btRegresar", "Regresar", espacios(2), '', 20, 'onclick="regresarMaesItems()" ' . $disabled . '', false));
        $form->addElement("GRUPO_BOTONES", new form_element_button("btagrega", "Agregar", '', '', 20, 'onclick="javascript:submit();"', false));
        return $form->getForm();
    }

    function preciosLista($codigo, $listas) {

        $producto = ItemsModel::ObtieneItem($codigo);
        $l_descs = ItemsModel::ObtieneTablaGeneral("LPRE");

        $result = '<form name="lista" method="POST" action="control.php" target="control">';
        $result .= '<input type="hidden" name="rqst" value="MAESTROS.ITEMS">';
        $result .= '<input type="hidden" name="action" value="Precios.Action">';
        $result .= '<input type="hidden" name="method" value="Borrar">';
        $result .= '<input type="hidden" name="codigo" value="' . htmlentities($codigo) . '">';
        $result .= '<table border="1"><caption>Lista de precios<br> ' . htmlentities($producto['art_descripcion']) . '</caption><tbody><tr>';
        $result .= '<th>Codigo</th>';
        $result .= '<th>Lista</th>';
        $result .= '<th>Precio</th>';
        $result .= '<th>&nbsp;</th></tr>';

        foreach ($listas as $cod_lista => $precio) {
            $result .= '<tr><th><a href="control.php?rqst=MAESTROS.ITEMS&action=Precios.Modificar&codigo=' . htmlentities($codigo) . '&lista=' . htmlentities($cod_lista) . '" target="control">' . htmlentities($codigo) . '</th>';
            $result .= '<th>' . htmlentities($l_descs[$cod_lista]) . '</th>';
            $result .= '<th>' . htmlentities($precio) . '</th>';

            if ($cod_lista != "01")
                $result .= '<th><input type="checkbox" name="listas[]" value="' . htmlentities($cod_lista) . '">';
            else
                $result .= '<th>&nbsp;</th>';

            $result .= '</tr>';
        }

        $result .= '</tbody></table>';

        $result .= '<input type="submit" value="Borrar seleccionados" name="submit">';
        $result .= '<input type="button" value="Agregar" name="Agregar" onclick="goAgregarPrecio(\'' . htmlentities($codigo) . '\')">';
        $result .= '</form>';

        return $result;
    }

    function precioModificar($codigo, $lista, $precio) {
        $form = new form('', "precioModificar", FORM_METHOD_POST, "control.php", '', "control");
        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MAESTROS.ITEMS"));
        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("action", "Precios.Action"));
        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("method", "Modificar"));
        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("codigo", $codigo));
        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("lista", $lista));

        $form->addElement(FORM_GROUP_MAIN, new form_element_onlytext('', "Modificar precio", "<br>"));
        $form->addElement(FORM_GROUP_MAIN, new form_element_onlytext('', "Codigo: " . htmlentities($codigo), "<br>"));
        $form->addElement(FORM_GROUP_MAIN, new form_element_onlytext('', "Lista de precios: " . htmlentities($lista), "<br>"));
        $form->addElement(FORM_GROUP_MAIN, new form_element_text("Precio:", "precio", $precio, '<br>', '', 10, 10, false, ''));
        $form->addElement(FORM_GROUP_MAIN, new form_element_submit("submit", "Guardar", '', '', 20));
        $form->addElement(FORM_GROUP_MAIN, new form_element_button("btRegresar", "Regresar", '', '', 20, 'onclick="regresarPrecios(\'' . htmlentities($codigo) . '\')', false));

        return $form->getForm();
    }

    function precioAgregar($codigo, $listas) {
        $form = new form('', "precioAgregar", FORM_METHOD_POST, "control.php", '', "control");
        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MAESTROS.ITEMS"));
        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("action", "Precios.Action"));
        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("method", "Agregar"));
        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("codigo", $codigo));

        $form->addElement(FORM_GROUP_MAIN, new form_element_combo("Lista:", "lista", "", "<br>", '', 1, $listas, false, ''));
        $form->addElement(FORM_GROUP_MAIN, new form_element_text("Precio:", "precio", '', '<br>', '', 16, 5, false, ''));
        $form->addElement(FORM_GROUP_MAIN, new form_element_submit("submit", "Agregar", '', '', 20));
        $form->addElement(FORM_GROUP_MAIN, new form_element_button("btRegresar", "Regresar", '', '', 20, 'onclick="regresarPrecios(\'' . htmlentities($codigo) . '\')', false));

        return $form->getForm();
    }

    function hipervinculoReporte($anio) {
        $hipervinculo = '<p align="center"><a href="/sistemaweb/maestros/reporteMaestros/listaPrecios.csv">Reporte de Lista de Preciosa</a></p>';

        return $hipervinculo;
    }

    function reporteExcel($res, $almacen) {

        $workbook = new Workbook("maestro_item.xls");
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


        $worksheet1 = & $workbook->add_worksheet('Hoja de Resultados Varillaje');
        $worksheet1->set_column(0, 0, 15);
        $worksheet1->set_column(1, 1, 30);
        $worksheet1->set_column(2, 2, 12);
        $worksheet1->set_column(3, 3, 12);
        $worksheet1->set_column(4, 4, 12);
        $worksheet1->set_column(5, 5, 10);
        $worksheet1->set_column(6, 6, 10);
        $worksheet1->set_column(7, 7, 10);
        $worksheet1->set_column(8, 8, 10);
        $worksheet1->set_column(9, 9, 10);

        $worksheet1->set_zoom(100);
        $worksheet1->set_landscape(100);

        $worksheet1->write_string(1, 0, "ARTICULOS DEL ALMACEN " . $almacen, $formato0);
        $worksheet1->write_string(2, 0, " ", $formato0);

        $a = 3;
        $worksheet1->write_string($a, 0, "CODIGO", $formato2);
        $worksheet1->write_string($a, 1, "DESCRIPCION", $formato2);
        $worksheet1->write_string($a, 2, "PRECIO", $formato2);
        $worksheet1->write_string($a, 3, "LINEA", $formato2);
        $worksheet1->write_string($a, 4, "TIPO", $formato2);
        $worksheet1->write_string($a, 5, "UNIDAD", $formato2);
        $worksheet1->write_string($a, 6, "UBICACION", $formato2);
        $worksheet1->write_string($a, 7, "CODIGO SKU", $formato2);
        $worksheet1->write_string($a, 8, "ACTIVO", $formato2);
        $worksheet1->write_string($a, 9, "STOCK", $formato2);
        $a++;

        for ($j = 0; $j < count($res); $j++) {
            $worksheet1->write_string($a, 0, $res[$j]['codigo'], $formato5);
            $worksheet1->write_string($a, 1, $res[$j]['descripcion'], $formato5);
            $worksheet1->write_number($a, 2, number_format($res[$j]['precio'], 2, '.', ''), $formato3);
            $worksheet1->write_string($a, 3, $res[$j]['linea'], $formato5);
            $worksheet1->write_string($a, 4, $res[$j]['tipo'], $formato5);
            $worksheet1->write_string($a, 5, $res[$j]['unidad'], $formato5);
            $worksheet1->write_string($a, 6, $res[$j]['ubicacion'], $formato5);
            $worksheet1->write_string($a, 7, $res[$j]['codsku'], $formato5);
            $worksheet1->write_string($a, 8, $res[$j]['estado'], $formato5);
            $worksheet1->write_number($a, 9, number_format($res[$j]['stock'], 2, '.', ''), $formato3);
            $a++;
        }

        $workbook->close();

     /* $chrFileName = "Articulos_items";
        header("Content-type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=$chrFileName.xls");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
        header("Pragma: public");*/
        
         header("Location: /sistemaweb/maestros/maestro_item.xls");
    }

    function reporteExcel2() {

        

        $workbook = new Workbook($chrFileName);
        $formato0 = & $workbook->add_format();
        $formato2 = & $workbook->add_format();
        $formato5 = & $workbook->add_format();

        $formato0->set_size(11);
        $formato0->set_bold(1);
        $formato0->set_align('left');
        $formato2->set_size(10);
        $formato2->set_bold(1);
        $formato2->set_align('center');
        $formato5->set_size(11);
        $formato5->set_align('left');

        $worksheet1 = & $workbook->add_worksheet('Hoja de Resultados Varillaje');
        $worksheet1->set_column(0, 0, 16);
        $worksheet1->set_column(1, 1, 50);
        $worksheet1->set_column(2, 2, 12);
        $worksheet1->set_column(3, 3, 12);
        $worksheet1->set_column(4, 4, 12);
        $worksheet1->set_column(5, 5, 16);
        $worksheet1->set_column(6, 6, 16);

        $worksheet1->set_zoom(100);
        $worksheet1->set_landscape(100);

        $worksheet1->write_string(1, 0, "MEDIDA DIARIA DE VARILLA", $formato0);
        
        
        $worksheet1->write_string(5, 0, " ", $formato0);

        $a = 7;
        $worksheet1->write_string($a, 0, "FECHA", $formato2);
        $worksheet1->write_string($a, 1, "TANQUE", $formato2);
        $worksheet1->write_string($a, 2, "NOMBRE COMBUSTIBLE", $formato2);
        $worksheet1->write_string($a, 3, "MEDICION", $formato2);
        $worksheet1->write_string($a, 4, "RESPONSABLE", $formato2);

        $a = 8;

        /* for ($j=0; $j<count($res); $j++) {	
          $nomtanque = VarillasModel::obtenerTanques($almacen, $res[$j]['ch_tanque']);

          $worksheet1->write_string($a, 0, $res[$j]['dt_fecha'],$formato5);
          $worksheet1->write_string($a, 1, $nomtanque[$res[$j]['ch_tanque']],$formato5);
          $worksheet1->write_string($a, 2, $res[$j]['ch_nombre'],$formato5);
          $worksheet1->write_number($a, 3, number_format($res[$j]['nu_medicion'],3,'.',''),$formato5);
          $worksheet1->write_string($a, 4, $res[$j]['ch_responsable'],$formato5);
          $a++;
          } */

        $workbook->close();

        $chrFileName = "Varillaje";
        header("Content-type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=$chrFileName.xls");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
        header("Pragma: public");
    }

}

