<?php

/*
  Fecha de creacion     : Feb 24, 2012, 4:24:34 PM
  Autor                 : Nestor Hernandez Loli
  Fecha de modificacion :
  Modificado por        :

  Clase template del mantenimiento de la tabla spos
 */

class SPosTemplate extends Template {

    function titulo() {
        $titulo = '<div align="center"><h2>Puntos de Venta</h2></div><hr>';

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

        $columnas = array('Punto de venta', 'Tipo de POS', 'Num. tickets',
            'Num. interno', 'Num. turno Z', 'Num. serie',
            'Autorizacion Sunat', 'Almacen', 'Lista de precio',
            'Eject', 'Lineas en blanco', 'IP terminal fijo');

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
            $regCod = $reg["s_pos_id"];
            $listado .= '<td class="grid_item">' . $reg['s_pos_id'] . '</td>';
//            $listado .= '<td class="grid_item">' . $reg['name'] . '</td>';
            $listado .= '<td class="grid_item">' . $reg['name_postype'] . '</td>';
            $listado .= '<td class="grid_item">' . $reg['numerator_tax'] . '</td>';
            $listado .= '<td class="grid_item">' . $reg['numerator_internal'] . '</td>';
            $listado .= '<td class="grid_item">' . $reg['numerator_z'] . '</td>';
            $listado .= '<td class="grid_item">' . $reg['printerserial'] . '</td>';
            $listado .= '<td class="grid_item">' . $reg['taxauthorization'] . '</td>';
            $listado .= '<td class="grid_item">' . $reg['ch_nombre_almacen'] . '</td>';
            $listado .= '<td class="grid_item">' . $reg['name_pricelist'] . '</td>';
            $listado .= '<td class="grid_item">' . $reg['ejectconfig'] . '</td>';
            $listado .= '<td class="grid_item">' . $reg['ejectlines'] . '</td>';
            $listado .= '<td class="grid_item">' . $reg['terminaldata'] . '</td>';

            $listado .= '<td> <a href="control.php?rqst=MAESTROS.NEW_POS_PUNTO_VENTA&task=NEW_POS_PUNTO_VENTA&action=Modificar&registroid=' . $regCod . '" target="control">
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
        $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MAESTROS.NEW_POS_PUNTO_VENTA'));
        $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'NEW_POS_PUNTO_VENTA'));

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

        $model = new SPosModel();

        //Voy a generar HTML para tener más control del diseño
        //que con las clases form2
        //Creando un combo para almacenes
        $comboAlmacenes = "<select name = 'warehouse' class = 'campo1'>";
        $almacenes = $model->obtenerAlmacenes();

        foreach ($almacenes as $alma) {
            $sel = "";
            if (!empty($a["warehouse"]) && $a["warehouse"] == $alma["ch_almacen"]) {
                $sel = " selected ";
            }

            $comboAlmacenes .= "<option value = '" . $alma["ch_almacen"] . "'" . $sel . ">" . $alma["ch_nombre_almacen"] .
                    "</option>";
        }
        $comboAlmacenes .= "</select>";

        $comboPrecios = "<select name = 'pricelist' class = 'campo1'>";
        $listas = $model->listasPrecios();
        foreach ($listas as $li) {
            $sel = "";
            if (!empty($a["pricelist"]) && trim($a["pricelist"]) == trim($li["tab_elemento"])) {
                $sel = " selected ";
            }
            $comboPrecios.= "<option value = '" . $li ["tab_elemento"] . "'" . $sel . ">" .
                    $li["tab_descripcion"] . "</option>";
        }
        $comboPrecios .= "</select>";


        //0=sin corte, 1=corta, 2=deja espacios y no corta
        $arrayEjectConfig = array('Sin corte', 'Corta', 'Deja espacios y no corta');
        $comboEject = "<select name = 'ejectconfig' class = 'campo1'>";
        for ($i = 0; $i < sizeof($arrayEjectConfig); $i++) {
            $sel = "";
            if (!empty($a["ejectconfig"]) && $a["ejectconfig"] == $i) {
                $sel = " selected ";
            }
            $comboEject.= "<option value = '" . $i . "'" . $sel . ">" . $arrayEjectConfig[$i] . "</option>";
        }

        $comboEject .= "</select>";

        $html = '<form method="post" class="form1" action = "control.php" target="control">
            <input type="hidden" name="rqst" value="MAESTROS.NEW_POS_PUNTO_VENTA"/>
            <input type="hidden" name="task" value="NEW_POS_PUNTO_VENTA"/>
            <input type="hidden" name="tipo_guardar" value="' . $tipo . '"/>

            <input type="hidden" name="s_pos_id" value="' . $a["s_pos_id"] . '"/>

            <fieldset>
                <legend>Datos del Punto de venta</legend>
                <div class="fila">
                    <label class="etiqueta">Punto de venta: </label>
                    <label class ="etiqueta2">' . '&nbsp;' . $a["s_pos_id"] . '</label>
                </div>
                <div class="fila">
                    <label class="etiqueta">Tipo POS: </label>
                    <label class ="etiqueta2">' . '&nbsp;' . $a["name_postype"] . '</label>
                </div>
                <div class="fila">
                    <label class="etiqueta">Numerador de tickets: </label>
                    <label class ="etiqueta2">' . '&nbsp;' . $a["numerator_tax"] . '</label>
                </div>
                <div class="fila">
                    <label class="etiqueta">Numerador interno: </label>
                    <label class ="etiqueta2">' . '&nbsp;' . $a["numerator_internal"] . '</label>
                </div>
                <div class="fila">
                    <label class="etiqueta">Numero de turno Z: </label>
                    <label class ="etiqueta2">' . '&nbsp;' . $a["numerator_z"] . '</label>
                </div>
                <div class="fila">
                    <label class="etiqueta">IP terminal fijo: </label>
                    <label class ="etiqueta2">' . '&nbsp;' . $a["terminaldata"] . '</label>
                </div>
                <div class="fila">
                    <label class="etiqueta">Numero de serie </label>
                    <input type="text" name="printerserial" maxlength="30" value = "' . $a["printerserial"] . '" class = "campo1" />
                </div>
                <div class="fila">
                    <label class="etiqueta">Autorizacion Sunat: </label>
                   <input type="text" name="taxauthorization" maxlength="30" value = "' . $a["taxauthorization"] . '" class = "campo1" />
                </div>
                <div class="fila">
                    <label class="etiqueta">Almacen: </label>
                    ' . $comboAlmacenes . '
                </div>
                <div class="fila">
                    <label class="etiqueta">Lista de precios: </label>
                    ' . $comboPrecios . '
                </div>
                <div class="fila">
                    <label class="etiqueta">Eject: </label>
                    ' . $comboEject . '
                </div>
                <div class="fila">
                    <label class="etiqueta">Lineas en blanco del corte: </label>
                    <input type="text" name="ejectlines" maxlength="30" value = "' . $a["ejectlines"] . '" class = "campo1" />
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
