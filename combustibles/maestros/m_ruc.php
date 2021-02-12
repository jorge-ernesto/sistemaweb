<?php

class RucModel extends Model{

	function ModelReportePDF($filtro = array()) {
		global $sqlca;

		$cond = '';
		if (!empty($filtro["codigo"])){
			$cond = " WHERE ruc LIKE '".pg_escape_string($filtro["codigo"])."%' ";
		}

		$query = "SELECT ruc, razsocial FROM ruc ".$cond. " ORDER BY razsocial ";

		if ($sqlca->query($query) < 0)
			return null;
		$resultado = array();
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$fila = $sqlca->fetchRow();
			$resultado[$i] = $fila;
		}
		
		return $resultado;
	}

	function guardarRegistro($registroid,$razsocial){
		global $sqlca;
		$query = "Insert into ruc values('".pg_escape_string($registroid)."','".pg_escape_string($razsocial)."')";

		if ($sqlca->query($query) < 0)
			return '0';

		$result = $sqlca->query($query);
		return '';
	} 

	function actualizarRegistro($registroid,$razsocial,$fecha){
		global $sqlca;
		$query = "Update ruc set razsocial='".pg_escape_string($razsocial)."', fecha = to_date('".pg_escape_string($fecha)."','DD/MM/YYYY') where ruc='".pg_escape_string($registroid)."'";
		$result = $sqlca->query($query);
		return '';
	}

	function RUCupsert($ruc,$razsocial){
		global $sqlca;

		trigger_error("upserting ruc $ruc");
		$sql = "SELECT 1 FROM ruc WHERE ruc='" . pg_escape_string($ruc) . "';";
		if ($sqlca->query($sql) == 1) {
			$sql = "UPDATE ruc SET razsocial = '" . pg_escape_string($razsocial) . "' WHERE ruc = '" . pg_escape_string($ruc) . "';";
		} else {
			$sql = "INSERT INTO ruc (ruc,razsocial) VALUES ('" . pg_escape_string($ruc) . "','" . pg_escape_string($razsocial) . "');";
		}

		$sqlca->query($sql);

		return 1;
	}

	function recuperarRegistroArray($registroid){
		global $sqlca;

		$registro = array();
		$query = "SELECT ruc,razsocial,to_char(fecha,'DD/MM/YYYY') as fecha from RUC where ruc='" . pg_escape_string($registroid) . "'";

		$sqlca->query($query);

		while( $reg = $sqlca->fetchRow()){
			$registro = $reg;
		}
		return $registro;
	}

	function recuperarDetalledeClientenTarjetasMagneticas($cliente){
		global $sqlca;
		$registro = array();
		$query=" ";
	}

	function eliminarRegistro($idregistro){
		global $sqlca;
		$query = "DELETE FROM ruc WHERE ruc = '" . pg_escape_string($idregistro) . "';";
		$sqlca->query($query);
		return OK;
	}

	function listarRucsMuertos($FechaIni,$FechaFin) {
		global $sqlca;

		$FechaDiv = explode("/", $FechaIni);
		$FechaIni = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];
		$postrans = "pos_trans".$FechaDiv[2].$FechaDiv[1];
		$FechaDiv = explode("/", $FechaFin);
		$FechaFin = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];

		if (("pos_trans".$FechaDiv[2].$FechaDiv[1]) != $postrans) {
			return "La consulta debe ser dentro del mismo mes";
		}

		$sql = "SELECT
				t.ruc
			FROM
				$postrans t
				LEFT JOIN ruc r ON (t.ruc = r.ruc)
			WHERE
				(r.razsocial IS NULL
				OR r.razsocial = ''
				OR r.razsocial = r.ruc)
				AND t.ruc IS NOT NULL
				AND t.ruc != ''
			GROUP BY
				t.ruc;";

		if ($sqlca->query($sql) <= 0)
			return "Error al procesar la consulta";

		$ret = Array();
		while ($rr = $sqlca->fetchRow())
			$ret[] = $rr[0];

		return $ret;
	}

	function tmListado($filtro=array(),$desde, $hasta,$pp, $pagina, $sunat){
		global $sqlca;
		$entro = 0;
		if ($sunat != '2') {
			$cond = " where ";
			if (!empty($filtro["codigo"])) {
				$cond .= " ruc like trim('".pg_escape_string($filtro["codigo"])."%')";
				$entro = 1;
			}
			/*if ($desde!= '' and $hasta!= '') {
				if ($entro == 1)
					$cond .= " and ";
				$cond .= " fecha::DATE between to_date('".pg_escape_string($desde)."','DD/MM/YYYY') and to_date('".pg_escape_string($hasta)."','DD/MM/YYYY')";
			}*/
		}

		$query = "select ruc, razsocial, to_char(date(fecha),'DD/MM/YYYY') from ruc ".$cond. " order by ruc ";

		if ($sunat != '1') {
			$resultado_1 = $sqlca->query($query);
			$numrows = $sqlca->numrows();

			if($pp && $pagina) {
				$paginador = new paginador($numrows,$pp, $pagina);
			} else {
				$paginador = new paginador($numrows,100,0);
			}

			$listado2['partir'] = $paginador->partir();
			$listado2['fin'] = $paginador->fin();
			$listado2['numero_paginas'] = $paginador->numero_paginas();
			$listado2['pagina_previa'] = $paginador->pagina_previa();
			$listado2['pagina_siguiente'] = $paginador->pagina_siguiente();
			$listado2['pp'] = $paginador->pp;
			$listado2['paginas'] = $paginador->paginas();
			$listado2['primera_pagina'] = $paginador->primera_pagina();
			$listado2['ultima_pagina'] = $paginador->ultima_pagina();

			if ($pp > 0)
				$query .= "LIMIT " . pg_escape_string($pp) . " ";
			if ($pagina > 0)
				$query .= "OFFSET " . pg_escape_string($paginador->partir());
		}

		if ($sqlca->query($query)<=0)
			return $sqlca->get_error();

		$listado[] = array();
		while( $reg = $sqlca->fetchRow())
			$listado['datos'][] = $reg;

		if ($sunat != '1')
			$listado['paginacion'] = $listado2;

		return $listado;
	}
}
