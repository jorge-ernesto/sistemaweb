<?php
// Descomentar estas líneas, cuando estamos en modo - development
/*
error_reporting(-1);
ini_set('display_errors', 1);
*/
// Descomentar estas líneas, cuando estamos en modo - production

ini_set('display_errors', 0);
if (version_compare(PHP_VERSION, '5.3', '>='))
{
	error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
}
else
{
	error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE);
}


class VentaCombustiblexLadoModel extends Model {

	function getAlmacenes() {
		global $sqlca;

		$sql = "
SELECT
 ch_almacen,
 ch_almacen||' - '||ch_nombre_almacen
FROM
 inv_ta_almacenes
WHERE
 ch_clase_almacen='1'
ORDER BY
 ch_almacen;
		";
	
		if ($sqlca->query($sql) < 0) 
			return false;
	
		$result = Array();
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
	    	$a = $sqlca->fetchRow();
	    	$result[$a[0]] = $a[1];
		}
		return $result;
    }
    
	function getLados() {
		global $sqlca;

		$arrResult = array();
		$status = $sqlca->query("SELECT f_pump_id AS nu_lado FROM f_pump ORDER BY 1");

		$arrResult['estado'] = FALSE;
		$arrResult['result'] = '';
	    $arrResult['cantidad_registros'] = 0;

		if($status < 0)
			$arrResult['mensaje'] = 'Error SQL - function getLados';
		else if($status == 0){
			$arrResult['mensaje'] = 'No se encontró ningún registro';
		} else{
			$arrResult['estado'] = TRUE;
			$arrResult['result'] = $sqlca->fetchAll();
			$arrResult['cantidad_registros'] = $status;//status = Tambien contiene la cantidad de registros
		}
		return $arrResult;
    }

	function getProductos() {
		global $sqlca;

		$arrResult = array();
		$status = $sqlca->query("
SELECT
 ch_codigocombustible AS nu_codigo_producto,
 ch_nombrecombustible AS no_producto
FROM
 comb_ta_combustibles
WHERE
 ch_codigocombustible!='11620306'
ORDER BY
 2;
		");

		$arrResult['estado'] = false;
		if((int)$status < 0)
			$arrResult['mensaje'] = 'Error SQL - function getProductos';
		else if((int)$status == 0){
			$arrResult['mensaje'] = 'No se encontró ningún registro';
		} else {
			$arrResult['estado'] = true;
			$arrResult['data'] = $sqlca->fetchAll();
			$arrResult['cantidad_registros'] = $status;//status = Tambien contiene la cantidad de registros
		}
		return $arrResult;
    }

    function getFechaSistemaPA() {
		global $sqlca;
		$sqlca->query("SELECT TO_CHAR(da_fecha - integer '1', 'DD/MM/YYYY') AS fe_sistema FROM pos_aprosys WHERE ch_poscd = 'A' ORDER BY da_fecha DESC LIMIT 1;");
		$row = $sqlca->fetchRow();
		return $row['fe_sistema'];
    }
    
    function getVentaCOmbustiblexLado($nu_almacen, $fe_inicial, $fe_final, $arrLadosForm, $nu_codigo_producto) {
		global $sqlca;

		$sql = "
SELECT
 ch_sucursal,
 dt_fechaparte,
 COUNT(*) AS nu_estado_parte
FROM
 (SELECT
  ch_sucursal,
  dt_fechaparte
 FROM
  comb_ta_contometros AS A
 WHERE
  ch_sucursal = '" . $nu_almacen . "'
  AND dt_fechaparte BETWEEN '" . $fe_inicial . "' AND '" . $fe_final . "'
 GROUP BY
  ch_numeroparte,
  ch_sucursal,
  dt_fechaparte
 ) AS PARTEDUPLICADO
WHERE
 ch_sucursal = '" . $nu_almacen . "'
 AND dt_fechaparte BETWEEN '" . $fe_inicial . "' AND '" . $fe_final . "'
GROUP BY
 ch_sucursal,
 dt_fechaparte;
		";
		// echo "<pre>";
		// echo $sql;
		// echo "</pre>";

		// Ejecuntado SQL de verificación de parte duplicado
		$sqlca->query($sql);
        $row = $sqlca->fetchRow();
		$iStatusParte = $row["nu_estado_parte"];

		$condLado = "AND DISPENSADOR.ch_numerolado IN (";
		for($i = 0; $i < count($arrLadosForm); $i++){
			$condLado .= "'" . $arrLadosForm[$i] . "',";
		}
		$condLado = substr($condLado,0,-1);
		$condLado .= ")";

		$condLadoAFE = "AND pump::INTEGER IN (";
		for($i = 0; $i < count($arrLadosForm); $i++){
			$condLadoAFE .= "'" . $arrLadosForm[$i] . "',";
		}
		$condLadoAFE = substr($condLadoAFE,0,-1);
		$condLadoAFE .= ")";

		$arrResult = array();
		
		$condProducto = ( $nu_codigo_producto == 0 ? NULL : "AND CTC.ch_codigocombustible = '" . $nu_codigo_producto . "'");

		$sql2 = "
		SELECT
			CTC.ch_sucursal AS nu_almacen,
			FIRST(ALMA.ch_nombre_breve_almacen) AS no_almacen,
			TO_CHAR(CTC.dt_fechaparte, 'DD/MM/YYYY') AS fe_emision,
			DISPENSADOR.ch_numerolado AS nu_lado,
			DISPENSADOR.nu_manguera AS nu_manguera,
			FIRST(CTC.ch_codigocombustible) AS id_producto,
			FIRST(PRO.ch_nombrecombustible) AS no_producto,
			ROUND((SUM(PC.precio) / COUNT(*)),2) AS ss_precio,
			FIRST(CTC.nu_contometroinicialgalon) AS nu_lectura_inicial_cantidad,
			FIRST(CTC.nu_contometrofinalgalon) AS nu_lectura_final_cantidad,
			FIRST(CTC.nu_ventagalon) AS ss_cantidad,
			FIRST(CTC.nu_contometroinicialvalor) AS nu_lectura_inicial_soles,
			FIRST(CTC.nu_contometrofinalvalor) AS nu_lectura_final_soles,
			FIRST(CTC.nu_ventavalor) AS ss_total,
			SUM(PTA.cantidad) AS ss_afericion_cantidad,
			SUM(PTA.importe) AS ss_afericion_soles,
			-FIRST(CTC.nu_descuentos) AS ss_descuentos_incrementos,
			FIRST(CTC.ch_responsable) AS nu_tipo_venta,
			FIRST(CTC.ch_usuario) AS no_usuario,
			FIRST(CTC.ch_surtidor) AS id_surtidor,
			'" . $iStatusParte . "' AS nu_estado_parte
		FROM
			comb_ta_contometros AS CTC
			LEFT JOIN inv_ta_almacenes AS ALMA ON (ALMA.ch_almacen = CTC.ch_sucursal)
			LEFT JOIN comb_ta_surtidores AS DISPENSADOR ON (CTC.ch_surtidor = DISPENSADOR.ch_surtidor)
			LEFT JOIN pos_contometros AS PC ON (PC.dia = CTC.dt_fechaparte AND PC.num_lado::VARCHAR = DISPENSADOR.ch_numerolado AND PC.manguera = DISPENSADOR.nu_manguera)
			LEFT JOIN comb_ta_combustibles AS PRO ON (CTC.ch_codigocombustible = PRO.ch_codigocombustible)
			LEFT JOIN pos_ta_afericiones AS PTA ON (PC.dia = PTA.dia AND PC.turno::VARCHAR = PTA.turno AND PC.num_lado::INTEGER = PTA.pump::INTEGER AND DISPENSADOR.ch_codigocombustible = PTA.codigo)
		WHERE
			CTC.ch_sucursal = '" . $nu_almacen . "'
			AND CTC.dt_fechaparte BETWEEN '" . $fe_inicial . "' AND '" . $fe_final . "'
			" . $condLado . "
			" . $condProducto . "
		GROUP BY
			1,3,4,5, CTC.ch_numeroparte
		ORDER BY
			1,3,DISPENSADOR.ch_numerolado::INTEGER,5;
		";
		// echo "<pre>";
		// echo $sql2;
		// echo "</pre>";

		$status = $sqlca->query($sql2);

		$arrResult['estado'] = FALSE;
		$arrResult['result'] = '';
	    $arrResult['cantidad_registros'] = 0;

		if($status < 0)
			$arrResult['mensaje'] = 'Error SQL - function getVentaCOmbustiblexLado';
		else if($status == 0){
			$arrResult['mensaje'] = 'No se encontró ningún registro';
		} else{
			$arrResult['estado'] = TRUE;
			$arrResult['result'] = $sqlca->fetchAll();
			$arrResult['cantidad_registros'] = $status;
		}
		return $arrResult;
    }
}
