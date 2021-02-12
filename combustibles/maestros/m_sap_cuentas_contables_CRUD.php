<?php

class SAPMapeoTablasCRUDModel extends Model {

	function obtenerSapMapeoTablas($nu_id_tipo_tabla) {
		global $sqlca;

		$status = $sqlca->query("SELECT * FROM sap_mapeo_tabla ORDER BY id_tipo_tabla;");
		$arrResult['estado'] = FALSE;
		$arrResult['result'] = '';
	    $arrResult['cantidad_registros'] = 0;

		if($status < 0)
			$arrResult['mensaje'] = 'Error SQL - function obtenerSapMapeoTablas';
		else if($status == 0)
			$arrResult['mensaje'] = 'No se encontró ningún registro';
		else{
			$arrResult['estado'] = TRUE;
			$arrResult['result'] = $sqlca->fetchAll();
			$arrResult['cantidad_registros'] = $status;//status = Tambien contiene la cantidad de registros
		}

		return $arrResult;
    }
    
    function listarTablasOpenComb($nu_id_tipo_tabla) {
		global $sqlca;

		if ($nu_id_tipo_tabla == 1 || $nu_id_tipo_tabla == '1') { //Centro Costo
			$status = $sqlca->query("
			SELECT
				SC.ch_sucursal AS opencomb_codigo,
				SC.ch_nombre_sucursal AS opencomb_nombre,
				SDMT.id_tipo_tabla_detalle,
				SDMT.sap_codigo AS sap_codigo
			FROM
				int_ta_sucursales AS SC
				LEFT JOIN sap_mapeo_tabla_detalle AS SDMT ON (id_tipo_tabla = 1 AND SC.ch_sucursal = SDMT.opencomb_codigo)
			ORDER BY
				2;
			");
		} else if ($nu_id_tipo_tabla == 2 || $nu_id_tipo_tabla == '2') { //Almacen
			$status = $sqlca->query("
			SELECT
				ALMA.ch_almacen AS opencomb_codigo,
				ALMA.ch_nombre_almacen AS opencomb_nombre,
				SDMT.id_tipo_tabla_detalle,
				SDMT.sap_codigo AS sap_codigo
			FROM
				inv_ta_almacenes AS ALMA
				LEFT JOIN sap_mapeo_tabla_detalle AS SDMT ON (id_tipo_tabla = 2 AND ALMA.ch_almacen = SDMT.opencomb_codigo)
			WHERE
				ALMA.ch_clase_almacen = '1'
			ORDER BY
				2;
			");
		} else if ($nu_id_tipo_tabla == 3 || $nu_id_tipo_tabla == '3') { //Lineas
			$status = $sqlca->query("
			SELECT
				LINEA.tab_elemento AS opencomb_codigo,
				LINEA.tab_descripcion AS opencomb_nombre,
				SDMT.id_tipo_tabla_detalle,
				SDMT.sap_codigo AS sap_codigo
			FROM
				int_tabla_general AS LINEA
				LEFT JOIN sap_mapeo_tabla_detalle AS SDMT ON (id_tipo_tabla = 3 AND LINEA.tab_elemento = SDMT.opencomb_codigo)
			WHERE
				LINEA.tab_tabla = '20'
			ORDER BY
				2;
			");
		} else if ($nu_id_tipo_tabla == 4 || $nu_id_tipo_tabla == '4') { //Tarjeta Créditos
			$status = $sqlca->query("
			SELECT
				TC.tab_elemento AS opencomb_codigo,
				TC.tab_descripcion AS opencomb_nombre,
				SDMT.id_tipo_tabla_detalle,
				SDMT.sap_codigo AS sap_codigo
			FROM
				int_tabla_general AS TC
				LEFT JOIN sap_mapeo_tabla_detalle AS SDMT ON (id_tipo_tabla = 4 AND TC.tab_elemento = SDMT.opencomb_codigo)
			WHERE
				TC.tab_tabla = '95'
			ORDER BY
				2;
			");
		}

		$arrResult['estado'] = FALSE;
		$arrResult['result'] = '';
	    $arrResult['cantidad_registros'] = 0;

		if($status < 0)
			$arrResult['mensaje'] = 'Error SQL - function listarSucursales';
		else if($status == 0)
			$arrResult['mensaje'] = 'No se encontró ningún registro';
		else{
			$arrResult['estado'] = TRUE;
			$arrResult['result'] = $sqlca->fetchAll();
			$arrResult['cantidad_registros'] = $status;//status = Tambien contiene la cantidad de registros
		}

		return $arrResult;
    }
    
    function guardarSapMapeoTablas($arrData) {
		global $sqlca;

		$arrInsert = array();
		for ($i=0; $i < count($arrData['arrOpenCombCodigo']); $i++)
			$arrInsert[] = "(" . $arrData['id_tipo_tabla'] . ", '" . $arrData['arrOpenCombCodigo'][$i] . "', '" . $arrData['arrSapCodigo'][$i] . "')";
		$sql_ins = 'INSERT INTO sap_mapeo_tabla_detalle (id_tipo_tabla, opencomb_codigo, sap_codigo ) VALUES ' . implode(',', $arrInsert);
		if($sqlca->query($sql_ins) < 0)
			return FALSE;
		return TRUE;
	}
    
    function actualizarSapMapeoTablas($arrData) {
		global $sqlca;

		$sql_upd = "UPDATE sap_mapeo_tabla_detalle SET sap_codigo = '" . pg_escape_string($arrData['valueSapCodigo']) . "' WHERE id_tipo_tabla = " . pg_escape_string($arrData['id_tipo_tabla']) . " AND id_tipo_tabla_detalle = " . pg_escape_string($arrData['id_tipo_tabla_detalle']) . ";";
		if($sqlca->query($sql_upd) < 0)
			return FALSE;
		return TRUE;
	}
}
