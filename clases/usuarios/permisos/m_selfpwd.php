<?php

class SelfPwdModel extends Model {
	function changeSelfPassword($pwd1,$pwd2) {
		global $sqlca,$usuario;

		if ($pwd1 != $pwd2)
			return "NE";

		if ($pwd1 == "" || $pwd1 == NULL)
			return "BL";

		$uid = $usuario->getUID();
		if (!is_numeric($uid))
			return "NL";
		$uname = pg_escape_string($_SESSION['auth_usuario']);

		$md5hash = md5("{$uname}{$pwd1}{$uname}");

		$sql = "	UPDATE
					int_usuarios_passwd
				SET
					ch_password = '{$md5hash}'
				WHERE
					uid = {$uid};";

		if ($sqlca->query($sql) < 0)
			return "IE";

		return "OK";
	}
}
