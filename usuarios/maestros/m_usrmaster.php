<?php
class UserMasterModel extends Model {
	function obtenerUsuarios($desde, $hasta) {
		global $sqlca;

		$sql = " SELECT
						uid,
						ch_login,
						ch_nombre,
						ch_email
					FROM
						int_usuarios_passwd
					WHERE
						ch_activo='S'
						AND uid > 0
					ORDER BY
						uid
				";

		if($desde > 0) $sql .= "OFFSET " . pg_escape_string($desde) . " ";
		if($hasta > 0) $sql .= "LIMIT " . pg_escape_string($hasta) . " ";

		$sql .= ";";
		if($sqlca->query($sql) < 0) return false;

		$result = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$result[$a[0]]['ch_login'] = $a[1];
			$result[$a[0]]['ch_nombre'] = $a[2];
			$result[$a[0]]['ch_email'] = $a[3];
		}

		return $result;
	}

	function obtieneUsuario($uid) {
		global $sqlca;

		$sql = " SELECT
						ch_login,
						ch_nombre,
						ch_email,
						ch_activo,
						pospassword
					FROM
						int_usuarios_passwd
					WHERE
						uid='" . pg_escape_string($uid) . "'
						AND ch_activo='S';
				";

		if($sqlca->query($sql) < 0) return;
		$a = $sqlca->fetchRow();

		return $a;
	}

	function updateUsuario($uid, $ch_nombre, $ch_email, $ch_password1, $ch_password2,$pospassword) {
		global $sqlca;

		$sql = " UPDATE
						int_usuarios_passwd
					SET
						ch_email='" . pg_escape_string($ch_email) . "',
						ch_nombre='" . pg_escape_string($ch_nombre) . "',
						pospassword='" . pg_escape_string($pospassword) . "'
					WHERE
						uid='" . pg_escape_string($uid) . "'
						AND ch_activo='S';
				";

		if($sqlca->query($sql) < 0) return false;
		if($ch_password1 != "" && $ch_password2 != "" && $ch_password1 == $ch_password2) {
			$sql = " SELECT
							ch_login
						FROM
							int_usuarios_passwd
						WHERE
							uid='" . pg_escape_string($uid) . "';
					";
			if ($sqlca->query($sql) < 0) return false;

			$a = $sqlca->fetchRow();
			$usuario = $a[0];

			$sql = " UPDATE
							int_usuarios_passwd
						SET
							ch_password='" . pg_escape_string(md5($usuario.$ch_password1.$usuario)) . "'
						WHERE
							uid='" . pg_escape_string($uid) . "';
					";

			if($sqlca->query($sql) < 0) return false;
		}

		return true;
	}

	function agregar($ch_login, $ch_nombre, $ch_email, $ch_password1, $ch_password2) {
		global $sqlca;

		if($ch_password1 == "" || $ch_password2 == "") return 1;
		if($ch_password1 != $ch_password2) return 2;

		$query = "SELECT max(uid)+1::integer FROM int_usuarios_passwd;";
		$sqlca->query($query);
		$uid = $sqlca->fetchRow();
		$sql = " INSERT INTO
						int_usuarios_passwd
						(
							uid,
							ch_login,
							ch_nombre,
							ch_email,
							ch_password,
							ch_activo
						) VALUES (
							'".$uid[0]."',
							'" . pg_escape_string($ch_login) . "',
							'" . pg_escape_string($ch_nombre) . "',
							'" . pg_escape_string($ch_email) . "',
							'" . pg_escape_string(md5($ch_login.$ch_password1.$ch_login)) . "',
							'S'
						);
				";

		echo $sql;

		if($sqlca->query($sql) < 0) return 3;

		return 0;	
	}

	function borrarUsuario($uids) {
		global $sqlca;

		foreach($uids as $uid) {
			$sql = " UPDATE
							int_usuarios_passwd
						SET
							ch_activo='N'
						WHERE
							uid='" . pg_escape_string($uid) . "';
					";

			if ($sqlca->query($sql) < 0) return false;

			/* Revocar los permisos del usuario */
			$sql = " DELETE FROM
							int_usuarios_grupos_pertenencia
						WHERE
							uid='" . pg_escape_string($uid) . "';
					";

			if ($sqlca->query($sql) < 0) return false;

			$sql = " DELETE FROM
							int_usuarios_permisos
						WHERE
							uid='" . pg_escape_string($uid) . "';
					";

			if ($sqlca->query($sql) < 0) return false;

			$sql = " DELETE FROM
							int_usuarios_almacenes
						WHERE
							uid='" . pg_escape_string($uid) . "';
					";

			if ($sqlca->query($sql) < 0) return false;	
		}

		return true;
	}

	function obtenerGruposPorUsuario($uid) {
		global $sqlca;

		$sql = " SELECT
						p.gid,
						g.ch_grupo,
						g.ch_nombre
					FROM
						int_usuarios_grupos_pertenencia p,
						int_usuarios_grupos g
					WHERE
						p.uid='" . pg_escape_string($uid) . "'
						AND g.gid=p.gid
						AND g.ch_activo='S'
					ORDER BY
						p.gid;
				";

		if($sqlca->query($sql) < 0) return false;

		$result = Array();

		for($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();

			$result[$a[0]]['ch_grupo'] = $a[1];
			$result[$a[0]]['ch_nombre'] = $a[2];
		}

		return $result;
	}

	function obtenerGrupos() {
		global $sqlca;

		$sql = " SELECT
						gid,
						ch_nombre
					FROM
						int_usuarios_grupos
					WHERE
						ch_activo='S'
						AND gid > 0
					ORDER BY
						gid
				";

		if($sqlca->query($sql) < 0) return false;

		$result = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$g = $sqlca->fetchRow();
			$result[$g[0]] = $g[1];
		}

		return $result;
	}    

	function esUsuarioValido($uid) {
		global $sqlca;

		$sql = " SELECT
						*
					FROM
						int_usuarios_passwd
					WHERE
						uid='" . pg_escape_string($uid) . "'
						AND ch_activo='S';
				";

		$sqlca->query($sql);

		if($sqlca->numrows() != 1) return false;

		return true;
	}

	function esGrupoValido($gid) {
		global $sqlca;

		$sql = " SELECT
						*
					FROM
						int_usuarios_grupos
					WHERE
						gid='" . pg_escape_string($gid) . "'
						AND ch_activo='S';
				";

		$sqlca->query($sql);

		if($sqlca->numrows() != 1) return false;

		return true;
	}

	function agregarGrupoUsuario($uid, $gid) {
		global $sqlca;

		if(!UserMasterModel::esUsuarioValido($uid) || !UserMasterModel::esGrupoValido($gid)) return false;

		$sql = " INSERT INTO
						int_usuarios_grupos_pertenencia
					VALUES (
						'" . pg_escape_string($uid) . "',
						'" . pg_escape_string($gid) . "'
					);
				";

		if ($sqlca->query($sql) < 0) return false;

		return true;
	}

	function borrarGrupoUsuario($uid, $gids) {
		global $sqlca;

		if(!UserMasterModel::esUsuarioValido($uid)) return false;

		foreach($gids as $gid) {
			if(!UserMasterModel::esGrupoValido($gid)) return false;

			$sql = " DELETE FROM
							int_usuarios_grupos_pertenencia
						WHERE
							gid='" . pg_escape_string($gid) . "'
							AND uid='" . pg_escape_string($uid) . "';
					";

			if ($sqlca->query($sql) < 0) return false;
		}

		return true;
	}
}
