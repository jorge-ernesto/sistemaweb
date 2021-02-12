<?php
class SelfPwdTemplate extends Template {
	function titulo() {
		return '<h2 style="color:#336699;" align="center">Cambio de Contrase&ntilde;a</td>';
	}

	function showSelfPwdChangeForm() {
		$result  = '<form name="accesos" method="post" target="control" action="control.php">';
		$result .= '<input type="hidden" name="rqst" value="PERMISOS.SELFPWD">';
		$result .= '<table align = "center">';
		$result .= '<tr>';
		$result .= '<td style="text-align:right;width:50%;">Contrase&ntilde;a:</td>';
		$result .= '<td style="text-align:left ;width:50%;"><input maxlength="15" type="password" name="pwd1"/></td>';
		$result .= '</tr>';
		$result .= '<tr>';
		$result .= '<td style="text-align:right;width:50%;">Confirmar contrase&ntilde;a:</td>';
		$result .= '<td style="text-align:left ;width:50%;"><input maxlength="15" type="password" name="pwd2" /></td>';
		$result .= '</tr>';
		$result .= '<td colspan="2" style="text-align:center;border-style:none;"><input type="submit" value="Cambiar" name="action"/></td></tr></table>';

		return $result;
	}
    
	function showSelfPwdChangeResult($res,&$ok,&$error) {
		if ($res == "OK")
			$ok = "<p style=\"text-align:center;\">Se ha actualizado su contraseña</p>";
		else if ($res == "NE")
			$error = "<script>alert('Las dos contraseñas no coinciden');</script>";
		else if ($res == "NL")
			$error = "<script>alert('Debe iniciar sesión');</script>";
		else if ($res == "BL")
			$error = "<script>alert('No se permite contraseña en blanco');</script>";
		else
			$error = "<script>alert('No se pudo cambiar la contraseña');</script>";
		return;
	}
}
