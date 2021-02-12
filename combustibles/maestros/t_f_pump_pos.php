<?php

/*
  Fecha de creacion     :Marzo 6, 2012, 11:04 AM
  Autor                 : Nestor Hernandez Loli
  Fecha de modificacion :
  Modificado por        :

  Clase template del mantenimiento de la tabla spos
 */

class FPumpPosTemplate extends Template {

    function titulo() {
        $titulo = '<div align="center"><h2>Lados por punto de venta</h2></div><hr>';
        return $titulo;
    }

    function errorResultado($errores) {
        $msg = "<div style = ''>";
        foreach ($errores as $err) {
            $msg .= $err . "<br>";
        }
        $msg .= "</div>";
        return $msg;
    }

    function listado($registros) {
        $model = new FPumpPosModel();
        $columnas = array('Lado', 'Productos', 'Punto venta');

        $listado = '<div id="resultados_grid" class="grid" align="center"><br>
				<table>
				<thead align="center" valign="center" >
				<tr class="grid_header">';

        for ($i = 0; $i < count($columnas); $i++) {
            $listado .= '<th class="grid_columtitle"> ' . strtoupper($columnas[$i]) . '</th>';
        }

        $listado .= '<th>' . espacios(10) . '</th><th>' . espacios(5) . '</th></tr><tbody class="grid_body" style="height:250px;">';

        //detalle
        foreach ($registros as $reg) {
            $listado .= '<tr class="grid_row" ' . resaltar('white', '#CDCE9C') . '>';
            $regCod = $reg["f_pump_pos_id"];

            $listado .= '<td class="grid_item">' . $reg['f_pump_id'] . '</td>';
            $productos = $model->productosPorLado($reg['f_pump_id']);
            $prod = "";
            foreach ($productos as $p) {
                $prod .= ", " . $p["art_descbreve"];
            }
            $prod = substr($prod, 1);

            $listado .= '<td class="grid_item">' . $prod . '</td>';
            $listado .= '<td class="grid_item">' . $reg['s_pos_id'] . '</td>';

            $listado .= '<td> <a href="control.php?rqst=MAESTROS.NEW_POS_LADOS&task=NEW_POS_LADOS&action=Modificar&registroid=' . $regCod . '" target="control">
					<img src="/sistemaweb/icons/open.gif" align="middle" border="0"/></a>&nbsp;';
            /*
              $listado .= '<a href="javascript:confirmarLink(\'Desea borrar este registro ' . $regCod .
              '\',\'control.php?rqst=MAESTROS.NEW_POS_PUNTO_VENTA&task=NEW_POS_PUNTO_VENTA' .
              '&action=Eliminar&registroid=' . $regCod . '\', \'control\')"><img src="/sistemaweb/icons/delete.gif" alt="Borrar" align="middle" border="0"/></A></td><td>&nbsp;</td>';
             */
            $listado .= '</tr>';
        }
        $listado .= '</tbody></table></div>';
        return $listado;
    }

    // Solo Formularios y otros
    function formBuscar($paginacion) {

        //echo "ENTRO BUSCAR\n";
        $form = new form2('', 'Buscar', FORM_METHOD_GET, 'control.php', '', 'control');
        $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MAESTROS.NEW_POS_LADOS'));
        $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'NEW_POS_LADOS'));

        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center>'));
//        $form->addElement(FORM_GROUP_MAIN, new f2element_submit('action', 'Buscar', espacios(3)));
        //$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action', 'Agregar', espacios(3)));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</center>'));

        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('P&aacute;gina ' . $paginacion['paginas'] . ' de ' . $paginacion['numero_paginas'] . ' P&aacute;ginas&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'));

        $form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/first.gif', espacios(2), array("border" => "0", "alt" => "Primera P&aacute;gina", "onclick" => "javascript:PaginarRegistros('" . $paginacion['pp'] . "','" . $paginacion['primera_pagina'] . "')")));
        $form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/left.gif', espacios(5), array("border" => "0", "alt" => "P&aacute;gina Anterior", "onclick" => "javascript:PaginarRegistros('" . $paginacion['pp'] . "','" . $paginacion['pagina_previa'] . "')")));

        $form->addElement(FORM_GROUP_MAIN, new f2element_text('paginas', '', $paginacion['paginas'], espacios(5), 3, 2, array("onChange" => "javascript:PaginarRegistros('" . $paginacion['pp'] . "',this.value)")));

        $form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/right.gif', espacios(2), array("border" => "0", "alt" => "P&aacute;gina Siguente", "onclick" => "javascript:PaginarRegistros('" . $paginacion['pp'] . "','" . $paginacion['pagina_siguiente'] . "')")));
        $form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/last.gif', espacios(2), array("border" => "0", "alt" => "&Uacute;ltima P&aacute;gina", "onclick" => "javascript:PaginarRegistros('" . $paginacion['pp'] . "','" . $paginacion['ultima_pagina'] . "')")));
        $form->addElement(FORM_GROUP_MAIN, new f2element_text('numero_registros', 'Registros por P&aacute;gina : ', $paginacion['pp'], espacios(2), 4, 4, array("onChange" => "javascript:PaginarRegistros(this.value,'" . $paginacion['primera_pagina'] . "')")));

        return $form->getForm();
    }

    function form($a, $tipo) {

        $model = new FPumpPosModel();
        $arrayPuntos = $model->obtenerPuntosVenta();
        $puntosVenta = "<select name = 's_pos_id' class = 'campo1'>";
        foreach ($arrayPuntos as $p) {
            $sel = "";
            if (!empty($p["s_pos_id"]) && $p["s_pos_id"] == $a["s_pos_id"]) {
                $sel = " selected ";
            }

            $puntosVenta .= "<option value = '" . $p["s_pos_id"] . "'" . $sel . ">" . $p["s_pos_id"] . "</option>";
        }

        $puntosVenta .= "</select>";

        $productos = $model->productosPorLado($a['f_pump_id']);
        $prod = "";
        foreach ($productos as $p) {
            $prod .= ", " . $p["art_descbreve"];
        }
        $prod = substr($prod, 1);

        $html = '<form method="post" class="form1" action = "control.php" target="control">
            <input type="hidden" name="rqst" value="MAESTROS.NEW_POS_LADOS"/>
            <input type="hidden" name="task" value="NEW_POS_LADOS"/>
            <input type="hidden" name="tipo_guardar" value="' . $tipo . '"/>

            <input type="hidden" name="f_pump_pos_id" value="' . $a["f_pump_pos_id"] . '"/>

            <fieldset>
                <legend>Datos de Lados por Punto de venta</legend>
                <div class="fila">
                    <label class="etiqueta">Lado: </label>
                    <label class ="etiqueta2">' . '&nbsp;' . $a["f_pump_id"] . '</label>
                </div>
                <div class="fila">
                    <label class="etiqueta">Productos: </label>
                    <label class ="etiqueta2">' . '&nbsp;' . $prod . '</label>
                </div>
                <div class="fila">
                    <label class="etiqueta">Punto de venta: </label>
                   ' . $puntosVenta . '
                </div>
                <div class="fila">
                    <input type="submit" name = "action" value="Guardar" />
                    <input type="button" value="Regresar" onclick="regresar()" />
                </div>
                
                <div id="error_body">
                    
                </div>
            </fieldset>
        </form>';
        return $html;
    }

}
