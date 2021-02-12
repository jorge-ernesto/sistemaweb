<?php
class SelfPwdTemplate extends Template {
	function titulo() {
		return '<h2><b>Cambio de Contrase&ntilde;a</b></h2>';
	}

	function showSelfPwdChangeForm() {
		$result  = '<form name="accesos" method="post" target="control" action="control.php">';
		$result .= '<input type="hidden" name="rqst" value="PERMISOS.SELFPWD">';
		$result .= '<table style="border-width: 1px; border-style: solid; border-color: black;">';
		$result .= '<tr>';
		$result .= '<td style="text-align:right;width:50%;border-style:solid;border-width:0px 1px 1px 0px;">Contrase&ntilde;a:</td>';
		$result .= '<td style="text-align:left ;width:50%;border-style:solid;border-width:0px 0px 1px 0px;"><input type="password" name="pwd1" /></td>';
		$result .= '</tr>';
		$result .= '<tr>';
		$result .= '<td style="text-align:right;width:50%;border-style:solid;border-width:0px 1px 1px 0px;">Confirmar:</td>';
		$result .= '<td style="text-align:left ;width:50%;border-style:solid;border-width:0px 0px 1px 0px;"><input type="password" name="pwd2" /></td>';
		$result .= '</tr>';
		$result .= '<td colspan="2" style="text-align:center;border-style:none;"><input type="submit" value="Cambiar" name="action"/></td></tr></table>';

		return $result;
	}
    
	function showSelfPwdChangeResult($res,&$ok,&$error) {
		if ($res == "OK")
			$ok = "<p style=\"text-align:center;\">Se ha actualizado su contrase&ntilde;a</p>";
		else if ($res == "NE")
			$error = "<script>alert('Las dos contrase&ntilde;as no coinciden');</script>";
		else if ($res == "NL")
			$error = "<script>alert('Debe iniciar sesion');</script>";
		else if ($res == "BL")
			$error = "<script>alert('No se permite contrase&ntilde;a en blanco');</script>";
		else
			$error = "<script>alert('No se pudo cambiar la contrase&ntilde;a');</script>";
		return;
	}
}
