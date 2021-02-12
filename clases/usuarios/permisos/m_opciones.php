<?php

class OpcionesModel extends Model {
	function obtenerGrupos() {
		global $sqlca;
	
		$sql = "	SELECT
					gid,
					ch_grupo,
					ch_nombre
				FROM
					int_usuarios_grupos
				WHERE
					ch_activo='S'
					AND gid > 0
				ORDER BY
					gid;";

		if ($sqlca->query($sql) < 0)
			return false;

		$result = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();

			$result[$a[0]] = $a[1] . " - " . $a[2];
		}

		return $result;
	}

	function obtenerOpcionesAsignadas($group) {
		global $sqlca;

		$sql = "	SELECT
					a.mnu_access_id AS mnu_access_id,
					CASE
						WHEN p.mnu_menu_id = 0 THEN ''::text
						ELSE p.name
					END AS parent,
					m.name AS name
				FROM
					mnu_menu m
					RIGHT JOIN mnu_access a USING (mnu_menu_id)
					INNER JOIN mnu_menu p ON (m.parent_id = p.mnu_menu_id)
				WHERE
					m.issumary = 0
					AND m.isreserved = 0
					AND a.gid = {$group}
				ORDER BY
					m.parent_id,
					m.seq;";

		if ($sqlca->query($sql) < 0)
			return false;

		$result = Array();
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$result[] = $a;
		}

		return $result;
	}

	function obtenerOpcionesNoAsignadas($group) {
		global $sqlca;

		$sql = "	SELECT
					m.mnu_menu_id AS mnu_menu_id,
					CASE
						WHEN p.mnu_menu_id = 0 THEN ''::text
						ELSE p.name
					END AS parent,
					m.name AS name
				FROM
					mnu_menu m
					LEFT JOIN mnu_access a ON (m.mnu_menu_id = a.mnu_menu_id AND a.gid = {$group})
					INNER JOIN mnu_menu p ON (m.parent_id = p.mnu_menu_id)
				WHERE
					m.issumary = 0
					AND a.mnu_access_id IS NULL
				ORDER BY
					m.parent_id,
					m.seq;";

		if ($sqlca->query($sql) < 0)
			return false;

		$result = Array();
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$result[] = $a;
		}

		return $result;
	}

	function eliminarOpciones($idlist){
		global $sqlca;

		$ids = implode(",",$idlist);

		$sql = "	DELETE
				FROM
					mnu_access
				WHERE
					mnu_access_id IN ({$ids});";
		$sqlca->query($sql);
	}

	function agregarOpciones($idlist,$group) {
		global $sqlca;

		$seq = "select max(mnu_access_id) from mnu_access;";

		$sqlca->query($seq);  
	
		$seque = $sqlca->fetchRow();

		$sequence = "select setval('seq_mnu_access_id',{$seque[0]});";

		echo $sequence;

		$sqlca->query($sequence);

		foreach($idlist as $id) {
			$sql = "	INSERT
					INTO
						mnu_access
					(
						mnu_menu_id,
						gid
					) VALUES (
						{$id},
						{$group}
					);";

			//echo $sql;

			$sqlca->query($sql);
		}
	}

}
