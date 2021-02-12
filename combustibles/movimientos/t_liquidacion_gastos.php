<?php

class LiquidacionGastosTemplate extends Template {

    function titulo() {
        return '<h2 align="center"><b>Liquidaci&oacute;n de Gastos</b></h2>';
    }

    function formSearch($almacenes) {

        $form = new Form('', "Agregar", FORM_METHOD_POST, "control.php", '', "control");
        //  $form = new Form('', "Agregar", FORM_METHOD_POST, "", '', "control");
        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MOVIMIENTOS.LIQUIDACIONGASTOS"));
        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("editar_text", "dddd"));

        $form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<table style='width:400px;'><tr><td style='width:50%;text-align:right;'>"));

        $form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Almacen:</td><td style='width:50%;text-align:left;'>"));
        $form->addElement(FORM_GROUP_MAIN, new form_element_combo("", "almacen", $_SESSION['almacen'], '</td></tr><tr><td style="text-align:right;">', '', '', $almacenes, false, ''));
        $form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Del:</td><td style='text-align:left;'>"));
        $form->addElement(FORM_GROUP_MAIN, new form_element_text("", "fecha", date("d/m/Y"), '<a href="javascript:show_calendar(\'Agregar.fecha\');"><img src="/sistemaweb/images/showcalendar.gif" border=0></a><div style="position:relative"><div id="overDiv" style="position:absolute;float:right; visibility:hidden; z-index:0;"></div></div></td></tr><tr><td style="text-align:right;">', '', 10, 10, true));
        $form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Al:</td><td style='text-align:left;'>"));
        $form->addElement(FORM_GROUP_MAIN, new form_element_text("", "fecha2", date("d/m/Y"), '<a href="javascript:show_calendar(\'Agregar.fecha2\');"><img src="/sistemaweb/images/showcalendar.gif" border=0></a><div style="position:relative"><div id="overDiv" style="position:absolute;float:right; visibility:hidden; z-index:0;"></div></div></td></tr><tr><td colspan="2" style="text-align:center;">', '', 10, 10, true));

        $form->addElement(FORM_GROUP_MAIN, new form_element_submit("action", "Buscar", '', '', 20));
        $form->addElement(FORM_GROUP_MAIN, new form_element_submit("action", "Agregar", '', '', 20));
        $form->addElement(FORM_GROUP_MAIN, new form_element_submit("action", "Editar", '', '', 20));
        $form->addElement(FORM_GROUP_MAIN, new form_element_submit("action", "Eliminar", '', '', 20));

        $form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr></table>"));

        return $form->getForm();
    }

    function formAgregar($almacenes, $tipos, $dias, $fila = Array()) {
        $form = new Form('', 'editar', FORM_METHOD_POST, "control.php", '', 'control');
        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MOVIMIENTOS.LIQUIDACIONGASTOS"));

        $form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<table style='width:400px;'><tr><td style='width:50%;text-align:right;'>"));

        $form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Almacen:</td><td style='width:50%;text-align:left;'>"));
        $form->addElement(FORM_GROUP_MAIN, new form_element_combo('', 'almacen', $fila['almacen'], '</td></tr><tr><td style="text-align:right;">', '', '', $almacenes, false, ''));
        $form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Tipo de Gasto:</td><td style='text-align:left;'>"));
        $form->addElement(FORM_GROUP_MAIN, new form_element_combo('', 'tipo_gasto', $fila['tipo_gasto'], '</td></tr><tr><td style="text-align:right;">', '', '', $tipos, false, ''));
        $form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Fecha:</td><td style='text-align:left;'>"));
        $form->addElement(FORM_GROUP_MAIN, new form_element_combo('', 'fecha', $fila['fecha'], '</td></tr><tr><td style="text-align:right;">', '', '', $dias, false, ''));
        $form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Descripci&oacute;n:</td><td style='text-align:left;'>"));
        $form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'descripcion', $fila['descripcion'], '</td></tr><tr><td style="text-align:right;">', '', 80, 255, false));
        $form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Importe:</td><td style='text-align:left;'>"));
        $form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'importe', $fila['importe'], '</td></tr><tr><td colspan="2" style="text-align:center;">', '', 10, 10, false));

        $form->addElement(FORM_GROUP_MAIN, new form_element_submit("action", "Guardar", '', '', 20));
        $form->addElement(FORM_GROUP_MAIN, new form_element_submit("action", "Regresar", '', '', 20));
        $form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr></table>"));

        return $form->getForm();
    }

    function formEdit($almacenes, $tipos, $dias, $databusqueda) {


        $databusqueda = $databusqueda[0];
        $form = new Form('', 'editarform', FORM_METHOD_POST, "control.php", '', 'control');
        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MOVIMIENTOS.LIQUIDACIONGASTOS"));
        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("editar_text", $databusqueda['id']));

        $form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<table style='width:400px;'><tr><td style='width:50%;text-align:right;'>"));

        $form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Almacen:</td><td style='width:50%;text-align:left;'>"));
        $form->addElement(FORM_GROUP_MAIN, new form_element_combo('', 'almacen', $databusqueda['es'], '</td></tr><tr><td style="text-align:right;">', '', '', $almacenes, false, ''));
        $form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Tipo de Gasto:</td><td style='text-align:left;'>"));

        $form->addElement(FORM_GROUP_MAIN, new form_element_combo('', 'tipo_gasto', $databusqueda['tipo'], '</td></tr><tr><td style="text-align:right;">', '', '', $tipos, false, ''));

        $form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Fecha:</td><td style='text-align:left;'>"));
        $form->addElement(FORM_GROUP_MAIN, new form_element_combo('', 'fecha', $databusqueda['fecha'], '</td></tr><tr><td style="text-align:right;">', '', '', $dias, false, ''));
        $form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Descripci&oacute;n:</td><td style='text-align:left;'>"));
        $form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'descripcion', $databusqueda['descripcion'], '</td></tr><tr><td style="text-align:right;">', '', 80, 255, false));
        $form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Importe:</td><td style='text-align:left;'>"));
        $form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'importe', $databusqueda['importe'], '</td></tr><tr><td colspan="2" style="text-align:center;">', '', 10, 10, false));

        $form->addElement(FORM_GROUP_MAIN, new form_element_submit("action", "Actualizar", '', '', 20));
        $form->addElement(FORM_GROUP_MAIN, new form_element_submit("action", "Regresar", '', '', 20));
        $form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr></table>"));

        return $form->getForm();
    }

    function resultadosBusqueda($resultados, $almacenes, $tipos) {
        $result = "<script type='text/javascript'>";

        $result.="
           
                 function guardar(id){
                 //document.getElementById('editar_text').val
                  alert(id);
            }

";
        $result.="</script>";


        $result .= '<table align="center">';
        $result .= '<tr>';
        $result .= '<th class="grid_cabecera">&nbsp;</th>';
        $result .= '<th class="grid_cabecera">ALMACEN</th>';
        $result .= '<th class="grid_cabecera">TIPO</th>';
        $result .= '<th class="grid_cabecera">FECHA</th>';
        $result .= '<th class="grid_cabecera">DESCRIPCION</th>';
        $result .= '<th class="grid_cabecera">IMPORTE</th>';
        $result .= '<th class="grid_cabecera">USUARIO</th>';
        $result .= '</tr>';

        for ($i = 0; $i < count($resultados); $i++) {
            $color = ($i % 2 == 0 ? "grid_detalle_par" : "grid_detalle_impar");
            $a = $resultados[$i];
            $result .= '<tr bgcolor="">';
            $result .= '<td class="' . $color . '"><input type="radio" onclick=parent.document.getElementById("editar_text").value=' . $a['id'] . '; name="opcion_event"/></td>';
            $result .= '<td class="' . $color . '">' . htmlentities($a['id']) . '</td>';
            $result .= '<td class="' . $color . '">' . htmlentities($almacenes[$a['es']]) . '</td>';
            $result .= '<td class="' . $color . '">' . htmlentities($tipos[$a['tipo']]) . '</td>';
            $result .= '<td class="' . $color . '">' . htmlentities($a['fecha']) . '</td>';
            $result .= '<td class="' . $color . '">' . htmlentities($a['descripcion']) . '</td>';
            $result .= '<td class="' . $color . '">' . htmlentities($a['importe']) . '</td>';
            $result .= '<td class="' . $color . '">' . htmlentities($a['usuario']) . '</td>';
            $result .= '</tr>';
        }

        return $result;
    }

}

