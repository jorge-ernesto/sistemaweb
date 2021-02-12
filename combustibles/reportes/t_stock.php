<?php

class StockTemplate extends Template
{
    function titulo()
    {
	    return '<h2><b>Stock de combustibles</b></h2>';
    }

    function formSearch()
    {
        $form = new Form('', "Buscar", FORM_METHOD_POST, "control.php", '', "control");

        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "REPORTES.STOCK"));

        $form->addElement(FORM_GROUP_MAIN, new form_element_submit("action", "Generar"));
        return $form->getForm();
    }

    function listado($resultado)
    {
        $result = '';

        $result .= '<table border="1">';
        $result .= '<tr>';
        $result .= '<th>Sucursal</th>';
        $result .= '<th>Codigo</th>';
        $result .= '<th>Producto</th>';
        $result .= '<th>Medicion</th>';
        $result .= '<th>Stock</th>';
        $result .= '</tr>';

        foreach($resultado as $ch_sucursal => $productos) {
            foreach($productos as $ch_codigocombustible => $combustible) {
                $result .= '<tr>';
                $result .= '<td>' . htmlentities($ch_sucursal) . '</td>';
                $result .= '<td>' . htmlentities($ch_cobigocombustible) . '</td>';
                $result .= '<td>' . htmlentities($combustible['nombre']) . '</td>';
                $result .= '</tr>';
            }
        }

        $result .= '</table>';

        return $result;
    }
}

