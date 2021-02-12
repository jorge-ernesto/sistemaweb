<?php

class ClientesTemplate extends Template {

	function titulo() {
		return '<h2><b>Maestro de clientes</b></h2>';
    	}

	function formAgregar() {
		$result = "";
		$result .= '<form name="agregar" method="post" action="control.php" target="control">';
		$result .= '<input type="hidden" name="rqst" value="MAESTROS.CLIENTES">';
		$result .= '<table border="1">';
		$result .= '<tr>';
		$result .= '<td>Codigo:<input type="text" name="cli_codigo"></td>';
		$result .= '<td>Raz. Social:<input type="text" name="cli_razsocial"></td>';
		$result .= '<td>Raz. Social Corta:<input type="text" name="cli_rsocialbreve"></td>';
		$result .= '<td>Direccion:<input type="text" name="cli_direccion"></td>';
		$result .= '<td>RUC:<input type="text" name="cli_ruc"></td>';
		$result .= '<td>Moneda:<input type="text" name="cli_moneda" size="3" maxlength="2" value="01"></td>';
		$result .= '<td>Telefono:<input type="text" name="cli_telefono"></td>';
		$result .= '<td>Fax:<input type="text" name="cli_fax"></td>';
		$result .= '</table>';
		$result .= '<input type="submit" name="action" value="Agregar">';
		$result .= '</form>';

		return $result;
	}

	function listado($resultados) {

		$bLocal = true;
	        $result = '';

		if ($bLocal) {
			$result .= '<form name="clientes" action="control.php" target="control" method="POST">';
			$result .= '<input type="hidden" name="rqst" value="MAESTROS.CLIENTES">';
			$result .= '<input type="submit" name="action" value="Modificar">';
			$result .= '<input type="submit" name="action" value="Eliminar">';
		}

		$result .= '<table border="1">';
		$result .= '<tr>';

		if ($bLocal)
			$result .= '<th>&nbsp;</th>';

		$result .= '<th>Codigo</th>';
		$result .= '<th>Raz. Social</th>';
		$result .= '<th>Raz. Social Corta</th>';
		$result .= '<th>Direccion</th>';
		$result .= '<th>RUC</th>';
		$result .= '<th>Moneda</th>';
		$result .= '<th>Telefono</th>';
		$result .= '<th>Fax</th>';
		$result .= '</tr>';

        	foreach($resultados as $cli_codigo => $cliente) {
            		$result .= '<tr>';
            		$result .= '<td><input type="checkbox" name="codigos[]" value="' . htmlentities($cli_codigo) . '"></td>';
            		$result .= '<td>' . htmlentities($cli_codigo) . '</td>';

            		if ($bLocal) {
                		$result .= '<td><input type="text" name="cli_razsocial[' . htmlentities($cli_codigo) . '][]" value="' . htmlentities($cliente['cli_razsocial']) . '"></td>';
				$result .= '<td><input type="text" name="cli_razsocialbreve[' . htmlentities($cli_codigo) . '][]" value="' . htmlentities($cliente['cli_razsocialbreve']) . '"></td>';
				$result .= '<td><input type="text" name="cli_direccion[' . htmlentities($cli_codigo) . '][]" value="' . htmlentities($cliente['cli_direccion']) . '"></td>';
				$result .= '<td><input type="text" name="cli_ruc[' . htmlentities($cli_codigo) . '][]" value="' . htmlentities($cliente['cli_ruc']) . '"></td>';
				$result .= '<td><input type="text" name="cli_moneda[' . htmlentities($cli_codigo) . '][]" value="' . htmlentities($cliente['cli_moneda']) . '" size="3" maxlength="2"></td>';
				$result .= '<td><input type="text" name="cli_telefono[' . htmlentities($cli_codigo) . '][]" value="' . htmlentities($cliente['cli_telefono']) . '"></td>';
//				$result .= '<td><input type="text" name="cli_fax[' . htmlentities($cli_codigo) . '][]" value="' . htmlentities($cliente['cli_fax']) . '"></td>';
			} else {
				$result .= '<td>' . $cliente['cli_razsocial'] . '</td>';
				$result .= '<td>' . $cliente['cli_razsocialbreve'] . '</td>';
				$result .= '<td>' . $cliente['cli_direccion'] . '</td>';
				$result .= '<td>' . $cliente['cli_ruc'] . '</td>';
				$result .= '<td>' . $cliente['cli_moneda'] . '</td>';
				$result .= '<td>' . $cliente['cli_telefono'] . '</td>';
				$result .= '<td>' . $cliente['cli_fax'] . '</td>';
            		}
            		$result .= '</tr>';
        	}

        	$result .= '</table>';

        	if ($bLocal) {
            		$result .= '<input type="submit" name="action" value="Modificar">';
            		$result .= '<input type="submit" name="action" value="Eliminar">';
            		$result .= '</form>';
        	}

        	return $result;
    	}
}
