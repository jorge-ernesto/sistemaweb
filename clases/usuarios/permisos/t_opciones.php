<?php
class OpcionesTemplate extends Template {
	function titulo() {
		return '<h2><b>Acceso a Opciones</b></h2>';
	}

	function listado($grupos) {
		$result  = '<form name="accesos" method="post" target="control" action="control.php">';
		$result .= '<input type="hidden" name="rqst" value="PERMISOS.OPCIONES">';
		$result .= '<table style="border-width: 1px; border-style: solid; border-color: black;">';
		$result .= '<tr>';
		$result .= '<td>Grupo</td>';
		$result .= '<td><select name="sl_grupo">';
		foreach($grupos as $k => $v){
			$result.='<option value="'.$k.'">'.$v.'</option>';
		}
		$result .= '</select></td>';
		$result.='<td><input type="submit" value="Buscar" name="action"/></td></tr></table>';
		$result .= '<div id="resultados_grid"></div>';

		return $result;
	}
    
	function listarOpcionesAsignadas($opciones,$group){
		$result  = "<form name=\"accesos\" method=\"post\" target=\"control\" action=\"control.php\">";
		$result .= "<input type=\"hidden\" name=\"rqst\" value=\"PERMISOS.OPCIONES\">";
		$result .= "<input type=\"hidden\" name=\"sl_grupo\" value=\"{$group}\">";
		$result .= '<table style="border-width: 1px; border-style: solid; border-color: black;">';
		$result .= '<tr><td>&nbsp;</td><td><input type="submit" name="action" value="Borrar" /></td><td><input type="submit" name="action" value="Agregar" /></td></tr><tr>';
		$result .= '<td>&nbsp;</td>';
		$result .= '<td><b>Opci&oacute;n Padre</b></td>';
		$result .= '<td><b>Descripci&oacute;n de la Opci&oacute;n</b></td></tr>';

		foreach($opciones as $r){
			$result .="<tr><td><input type=\"checkbox\" name=\"chk[]\" value=\"{$r[0]}\" /></td>";
			$result .="<td>".htmlentities((($r[1]=="" || $r[1]==NULL)?"(Ninguna)":$r[1]))."</td><td>".htmlentities($r[2])."</td></tr>";
		}

		$result .= '<tr><td>&nbsp;</td><td><input type="submit" name="action" value="Borrar" /></td><td><input type="submit" name="action" value="Agregar" /></td></tr></table>';
		$result .= "</form>";

		return $result;
	}

	function listarOpcionesNoAsignadas($opciones,$group){
		$result  = "<form name=\"accesos\" method=\"post\" target=\"control\" action=\"control.php\">";
		$result .= "<input type=\"hidden\" name=\"rqst\" value=\"PERMISOS.OPCIONES\">";
		$result .= "<input type=\"hidden\" name=\"sl_grupo\" value=\"{$group}\">";
		$result .= '<table style="border-width: 1px; border-style: solid; border-color: black;">';
		$result .= '<tr><td>&nbsp;</td><td>&nbsp;</td><td><input type="submit" name="action" value="Agregar Seleccionados" /></td></tr><tr>';
		$result .= '<td>&nbsp;</td>';
		$result .= '<td><b>Opci&oacute;n Padre</b></td>';
		$result .= '<td><b>Descripci&oacute;n de la Opci&oacute;n</b></td></tr>';

		foreach($opciones as $r){
			$result .="<tr><td><input type=\"checkbox\" name=\"chk[]\" value=\"{$r[0]}\" /></td>";
			$result .="<td>".htmlentities((($r[1]=="" || $r[1]==NULL)?"(Ninguna)":$r[1]))."</td><td>".htmlentities($r[2])."</td></tr>";
		}

		$result .= '<tr><td>&nbsp;</td><td>&nbsp;</td><td><input type="submit" name="action" value="Agregar Seleccionados" /></td></tr></table>';
		$result .= "</form>";

		return $result;
	}
}
