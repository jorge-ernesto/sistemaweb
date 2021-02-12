<?php

class ClubesTemplate extends Template
{
    function titulo()
    {
        return '<h2><b>Maestro de Clubes</b></h2>';
    }

    function listado($resultado)
    {
        $result = '';

        //if ($_SESSION['almacen'] == "001") {
            $result .= '<form name="listado" method="post" action="control.php" target="control">';
            $result .= '<input type="hidden" name="rqst" value="MAESTROS.CLUBES">';
            $result .= '<input type="submit" name="action" value="Guardar">';
            $result .= '<input type="submit" name="action" value="Borrar">';
        //}

        $result .= '<table border="1">';
        $result .= '<tr>';
        //if ($_SESSION['almacen'] == "001")
            $result .= '<th>&nbsp;</th>';
        $result .= '<th>Cod.</th>';
        $result .= '<th>Descripcion</th>';
        $result .= '</tr>';

        foreach($resultado as $codigo => $descripcion) {
            $result .= '<tr>';
            //if ($_SESSION['almacen'] == "001")
                $result .= '<td><input type="checkbox" name="clubes[]" value="' . htmlentities($codigo) . '"></td>';
            $result .= '<td>' . htmlentities($codigo) . '</td>';

            //if ($_SESSION['almacen'] == "001")
                $result .= '<td><input type="text" name="descripciones[' . htmlentities($codigo) . ']" value="' . htmlentities($descripcion) . '"></td>';
            //else
             //   $result .= '<td>' . htmlentities($descripcion) . '</td>';
            $result .= '</tr>';
        }

        $result .= '</table>';

        //if ($_SESSION['almacen'] == "001")
            $result .= '<input type="submit" name="action" value="Guardar">';
            $result .= '<input type="submit" name="action" value="Borrar">';
            $result .= '</form>';

        return $result;
    }

    function formAgregar()
    {
        if ($_SESSION['almacen'] != "001") return "";

	    $form = new Form('', "Agregar", FORM_METHOD_POST, "control.php", '', "control");

	    $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MAESTROS.CLUBES"));

        $form->addElement(FORM_GROUP_MAIN, new form_element_text("Codigo:", "tab_elemento", '', '<br>', '', 2, 2));
        $form->addElement(FORM_GROUP_MAIN, new form_element_text("Descripcion:", "tab_descripcion", '', '<br>', '', 20, 40));

	    $form->addElement(FORM_GROUP_MAIN, new form_element_submit("action", "Agregar"));
	    return $form->getForm();
    }
}

