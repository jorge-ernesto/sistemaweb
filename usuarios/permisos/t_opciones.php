<?php
class OpcionesTemplate extends Template {
	function titulo() {
		return '<h2 style="color:#336699;" align="center">Acceso a Opciones</td>';
	}

	function listado($grupos) {
		$result  = '<form name="accesos" method="post" target="control" action="control.php">';
		$result .= '<input type="hidden" name="rqst" value="PERMISOS.OPCIONES">';
		$result .= '<table align = "center">';
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
		$result .= '<table align="center">';
		//$result .= '<tr><td>&nbsp;</td><td><input type="submit" name="action" value="Borrar Opci&oacute;n" /></td><td><input type="submit" name="action" value="Agregar Opci&oacute;n" /></td></tr><tr>';
		$result .= '<tr><td>&nbsp;</td><td><button type="submit" name="action" value="BorrarOpcion">Borrar Opción</button></td><td><button type="submit" name="action" value="AgregarOpcion">Agregar Opción</button></td></tr>';
		$result .= '<td class="grid_cabecera">&nbsp;</td>';
		$result .= '<td class="grid_cabecera"><b>Opci&oacute;n Padre</b></td>';
		$result .= '<td class="grid_cabecera"><b>Descripci&oacute;n de la Opci&oacute;n</b></td></tr>';

			foreach($opciones as $key => $r){
				$color = ($key%2) == 0 ? ' grid_detalle_par' : ' grid_detalle_impar';
		
				$result .="<tr class=\"fila bgcolor $color\"><td><input type=\"checkbox\" name=\"chk[]\" value=\"{$r[0]}\" /></td>";
				$result .="<td>".htmlentities((($r[1]=="" || $r[1]==NULL)?"(Ninguna)":$r[1]))."</td><td>".htmlentities($r[2])."</td></tr>";
			}

		//$result .= '<tr><td>&nbsp;</td><td><input type="submit" name="action" value="Borrar Opci&oacute;n" /></td><td><input type="submit" name="action" value="Agregar Opci&oacute;n" /></td></tr></table>';
		$result .= '<tr><td>&nbsp;</td><td><button type="submit" name="action" value="BorrarOpcion">Borrar Opción</button></td><td><button type="submit" name="action" value="AgregarOpcion">Agregar Opción</button></td></tr>';
		$result .= "</form>";
		return $result;
	}

	function listarOpcionesNoAsignadas($opciones,$group){
		$result  = "<form name=\"accesos\" method=\"post\" target=\"control\" action=\"control.php\">";
		$result .= "<input type=\"hidden\" name=\"rqst\" value=\"PERMISOS.OPCIONES\">";
		$result .= "<input type=\"hidden\" name=\"sl_grupo\" value=\"{$group}\">";
		$result .= '<table align = "center">';
		//$result .= '<tr><td>&nbsp;</td><td>&nbsp;</td><td><input type="submit" name="action" value="Agregar Seleccionado(s)" /></td></tr><tr>';
		$result .= '<tr><td>&nbsp;</td><td>&nbsp;</td><td><button type="submit" name="action" value="AgregarSeleccionado">Agregar Seleccionado(s)</button></td></tr>';
		$result .= '<td class="grid_cabecera">&nbsp;</td>';
		$result .= '<td class="grid_cabecera"><b>Opci&oacute;n Padre</b></td>';
		$result .= '<td class="grid_cabecera"><b>Descripci&oacute;n de la Opci&oacute;n</b></td></tr>';

		foreach($opciones as $key => $r){
			$color = ($key%2) == 0 ? ' grid_detalle_par' : ' grid_detalle_impar';

			$result .="<tr class=\"fila bgcolor $color\"><td><input type=\"checkbox\" name=\"chk[]\" value=\"{$r[0]}\" /></td>";
			$result .="<td>".htmlentities((($r[1]=="" || $r[1]==NULL)?"(Ninguna)":$r[1]))."</td><td>".htmlentities($r[2])."</td></tr>";
		}

		//$result .= '<tr><td>&nbsp;</td><td>&nbsp;</td><td><input type="submit" name="action" value="Agregar Seleccionado(s)" /></td></tr></table>';
		$result .= '<tr><td>&nbsp;</td><td>&nbsp;</td><td><button type="submit" name="action" value="AgregarSeleccionado">Agregar Seleccionado(s)</button></td></tr>';
		$result .= "</form>";

		return $result;
	}
}
