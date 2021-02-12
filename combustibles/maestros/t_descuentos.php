<?php

class DescuentosTemplate extends Template
{
    function titulo()
    {
	    return '<h2><b>Descuentos Especiales</b></h2>';
    }

    function formSearch()
    {
        if ($_SESSION['almacen'] == "001") {
	        $form = new Form('', "Descuentos", FORM_METHOD_POST, "control.php", '', "control");

	        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MAESTROS.DESCUENTOS"));

	        $form->addElement(FORM_GROUP_MAIN, new form_element_submit("action", "Anadir"));
	        $form->addElement(FORM_GROUP_MAIN, new form_element_submit("action", "Copiar"));
	        return $form->getForm();
        }
        else return '';
    }

    function listado($resultado)
    {
	    $result = '';

       // if ($_SESSION['almacen'] == "001") {
            $result .= '<form name="listado" method="post" action="control.php" target="control">';
            $result .= '<input type="hidden" name="rqst" value="MAESTROS.DESCUENTOS">';
            $result .= '<input type="submit" name="action" value="Editar">';
            $result .= '<input type="submit" name="action" value="Borrar">';
        //}

	    $result .= '<table border="1">';
	    $result .= '<tr>';

       // if ($_SESSION['almacen'] == "001")
            $result .= '<th>&nbsp;</th>';
	    $result .= '<th>Sucursal</th>';
	    $result .= '<th>Producto</th>';
	    $result .= '<th>Fecha Inicio</th>';
	    $result .= '<th>Fecha Fin</th>';
	    $result .= '<th>Importe descuento</th>';
	    $result .= '</tr>';

	    foreach($resultado as $correlativo => $descuento) {
	        $result .= '<tr>';

          //  if ($_SESSION['almacen'] == "014")
                $result .= '<td><input type="radio" name="descuento" value="' . htmlentities($correlativo) . '"></td>';
	        $result .= '<td>' . htmlentities($descuento['ch_almacen']) . '</td>';
	        $result .= '<td>' . htmlentities($descuento['art_codigo']." - " . $descuento['art_descripcion']) . '</td>';
	        $result .= '<td>' . htmlentities($descuento['dt_fecha_inicio']) . '</td>';
	        $result .= '<td>' . htmlentities($descuento['dt_fecha_fin']) . '</td>';
	        $result .= '<td>' . htmlentities($descuento['nu_impo_descuento_unidad']) . '</td>';
	        $result .= '</tr>';
	    }

	    $result .= '</table>';
        $result .= '<input type="submit" name="action" value="Editar">';
        $result .= '<input type="submit" name="action" value="Borrar">';
        $result .= '</form>';
	    return $result;
    }

    function formAgregar()
    {
        $hoy = date("d/m/Y");
        $clubes = DescuentosModel::obtenerClubes();
        $almacenes = DescuentosModel::obtenerAlmacenes();
        $combustibles = DescuentosModel::obtenerCombustibles();

	    $form = new Form('', "Agregar", FORM_METHOD_POST, "control.php", '', "control");

	    $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MAESTROS.DESCUENTOS"));

        $form->addElement(FORM_GROUP_MAIN, new form_element_combo("<b>Club:</b>", "ch_codigo_club", '', '<br>', '', 1, $clubes, false, ''));
        $form->addElement(FORM_GROUP_MAIN, new form_element_combo("Almacen:", "ch_almacen", '', '<br>', '', 1, $almacenes, false, ''));
        $form->addElement(FORM_GROUP_MAIN, new form_element_combo("Combustibles:", "art_codigo", '', '<br>', '', 1, $combustibles, false, ''));
        $form->addElement(FORM_GROUP_MAIN, new form_element_text("Inicio:", "dt_inicio", $hoy, '', '', 12, 10));
        $form->addElement(FORM_GROUP_MAIN, new form_element_text("", "hr_inicio", '24:00', '<br>', '', 6, 5));
        $form->addElement(FORM_GROUP_MAIN, new form_element_text("Fin:", "dt_fin", $hoy, '', '', 12, 10));
        $form->addElement(FORM_GROUP_MAIN, new form_element_text("", "hr_fin", '24:00', '<br>', '', 6, 5));
        $form->addElement(FORM_GROUP_MAIN, new form_element_text("Importe de descuento:", "nu_descuento", '0.00', '<br>', '', 10, 8));

	    $form->addElement(FORM_GROUP_MAIN, new form_element_submit("action", "Agregar"));
	    return $form->getForm();

    }

    function formEditar($descuento)
    {
        $clubes = DescuentosModel::obtenerClubes();
        $almacenes = DescuentosModel::obtenerAlmacenes();
        $combustibles = DescuentosModel::obtenerCombustibles();

        list($ano,$mes,$dia,$hora,$minutos,$segundos) = sscanf($descuento['dt_fecha_inicio'], "%4s-%2s-%2s %2s:%2s:%2s");
        $inicio_fecha = $dia . "/" . $mes . "/" . $ano;
        $inicio_hora = $hora . ":" . $minutos;

        list($ano,$mes,$dia,$hora,$minutos,$segundos) = sscanf($descuento['dt_fecha_fin'], "%4s-%2s-%2s %2s:%2s:%2s");
        $fin_fecha = $dia . "/" . $mes . "/" . $ano;
        $fin_hora = $hora . ":" . $minutos;

        $form = new Form('', "Editar", FORM_METHOD_POST, "control.php", '', "control");

	    $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MAESTROS.DESCUENTOS"));
        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("ch_correlativo", $descuento['ch_correlativo']));

        $form->addElement(FORM_GROUP_MAIN, new form_element_combo("<b>Club:</b>", "ch_codigo_club", $descuento['ch_codigo_club'], '<br>', '', 1, $clubes, false, ''));
        $form->addElement(FORM_GROUP_MAIN, new form_element_combo("Almacen:", "ch_almacen", $descuento['ch_almacen'], '<br>', '', 1, $almacenes, false, ''));
        $form->addElement(FORM_GROUP_MAIN, new form_element_combo("Combustibles:", "art_codigo", $descuento['art_codigo'], '<br>', '', 1, $combustibles, false, ''));
        $form->addElement(FORM_GROUP_MAIN, new form_element_text("Inicio:", "dt_inicio", $inicio_fecha, '', '', 12, 10));
        $form->addElement(FORM_GROUP_MAIN, new form_element_text("", "hr_inicio", $inicio_hora, '<br>', '', 6, 5));
        $form->addElement(FORM_GROUP_MAIN, new form_element_text("Fin:", "dt_fin", $fin_fecha, '', '', 12, 10));
        $form->addElement(FORM_GROUP_MAIN, new form_element_text("", "hr_fin", $fin_hora, '<br>', '', 6, 5));
        $form->addElement(FORM_GROUP_MAIN, new form_element_text("Importe de descuento:", "nu_descuento", $descuento['nu_impo_descuento_unidad'], '<br>', '', 10, 8));

        $form->addElement(FORM_GROUP_MAIN, new form_element_submit("action", "Modificar"));
        return $form->getForm();
    }

    function listadoCopiar($resultado)
    {
	    $result = '';

        $result .= '<form name="listado" method="post" action="control.php" target="control">';
        $result .= '<input type="hidden" name="rqst" value="MAESTROS.DESCUENTOS">';
        $result .= '<input type="submit" name="action" value="Hacer copia">';
	    $result .= '<table border="1">';
	    $result .= '<tr>';
        $result .= '<th>&nbsp;</th>';
	    $result .= '<th>Sucursal</th>';
	    $result .= '<th>Producto</th>';
	    $result .= '<th>Fecha Inicio</th>';
	    $result .= '<th>Fecha Fin</th>';
	    $result .= '<th>Importe descuento</th>';
	    $result .= '</tr>';

	    foreach($resultado as $correlativo => $descuento) {
	        $result .= '<tr>';
            $result .= '<td><input type="checkbox" name="descuentos[]" value="' . htmlentities($correlativo) . '"></td>';
	        $result .= '<td>' . htmlentities($descuento['ch_almacen']) . '</td>';
	        $result .= '<td>' . htmlentities($descuento['art_codigo']." - " . $descuento['art_descripcion']) . '</td>';
	        $result .= '<td>' . htmlentities($descuento['dt_fecha_inicio']) . '</td>';
	        $result .= '<td>' . htmlentities($descuento['dt_fecha_fin']) . '</td>';
	        $result .= '<td>' . htmlentities($descuento['nu_impo_descuento_unidad']) . '</td>';
	        $result .= '</tr>';
	    }

	    $result .= '</table>';
        $result .= '<input type="submit" name="action" value="Hacer copia">';
        $result .= '</form>';
	    return $result;

    }
}

