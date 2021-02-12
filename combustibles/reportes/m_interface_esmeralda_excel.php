<?php
ini_set("memory_limit", '3G');
ini_set("upload_max_filesize", "15M");
ini_set("post_max_size", "15M");
ini_set('max_execution_time', '700');
ini_set('max_input_time', '700');

class Descuentos_Especiales_Model extends Model {
   	function ObtenerEstaciones() {
		global $sqlca;
	
		try {
			$sql = "
SELECT
 ch_sucursal as almacen,
 ch_sucursal||' '||ch_nombre_breve_sucursal as nomalmacen
FROM
 int_ta_sucursales
ORDER BY
 ch_sucursal
			";

			if($sqlca->query($sql) <= 0){
				throw new Exception("Error no se encontro turnos en la fecha indicada");
			}

			while($reg = $sqlca->fetchRow()){
				$registro[] = $reg;
			}

			return $registro;
		}catch(Exception $e){
			throw $e;
		}
   	}

	function ActualizarDatosFacturas($sucursal, $fecha) {
        global $sqlca;

//         $query = "
// SELECT
//  trim(c.ch_fac_seriedocumento)||'-'||c.ch_fac_numerodocumento::TEXT as id,
//  trim(c.ch_fac_seriedocumento) as serie_documento,
//  c.ch_fac_numerodocumento::TEXT as num_documento,
//  to_char(c.dt_fac_fecha,'dd/MM/YYYY') as fecha_emision,
//  to_char(c.dt_fac_fecha,'HH12:MI:SS') as hora_emision,
//  cli.cli_codigo,
//  cli.cli_ruc AS ruc,
//  cli.cli_razsocial AS rs,
//  (CASE WHEN c.ch_fac_anulado = 'S' THEN 'A' ELSE 'E' END) AS estado,
//  d.art_codigo::TEXT  as articulo,
//  d.nu_fac_cantidad  as cantidad,
//  d.nu_fac_precio as precio,
//  d.nu_fac_valortotal as importe,
//  (CASE WHEN c.ch_fac_forma_pago = '01' THEN 'EF' ELSE 'ERR' END) AS medio_pago,
//  d.nu_fac_valortotal AS importe_credito
// FROM
//  fac_ta_factura_cabecera AS c
//  LEFT JOIN fac_ta_factura_detalle AS d ON(c.ch_fac_tipodocumento = d.ch_fac_tipodocumento AND c.ch_fac_seriedocumento = d.ch_fac_seriedocumento AND c.ch_fac_numerodocumento = d.ch_fac_numerodocumento AND c.cli_codigo = d.cli_codigo)
//  LEFT JOIN int_clientes AS cli ON(c.cli_codigo = cli.cli_codigo)
// WHERE
//  c.ch_almacen = '" . $sucursal . "'
//  AND TO_CHAR(c.dt_fac_fecha, 'YYYYMM') = '" . $fecha . "'
//  AND c.ch_fac_tipodocumento IN('35','10')
// 		";

		$query = "
SELECT
 trim(c.ch_fac_seriedocumento)||'-'||c.ch_fac_numerodocumento::TEXT as id,
 trim(c.ch_fac_seriedocumento) as serie_documento,
 c.ch_fac_numerodocumento::TEXT as num_documento,
 to_char(c.dt_fac_fecha,'dd/MM/YYYY') as fecha_emision,
 to_char(c.dt_fac_fecha,'HH12:MI:SS') as hora_emision,
 cli.cli_codigo,
 cli.cli_ruc AS ruc,
 cli.cli_razsocial AS rs,
 (CASE WHEN c.ch_fac_anulado = 'S' THEN 'A' ELSE 'E' END) AS estado,
 d.art_codigo::TEXT  as articulo,
 d.nu_fac_cantidad  as cantidad,
 d.nu_fac_precio as precio,
 d.nu_fac_valortotal as importe,
 (CASE WHEN c.ch_fac_forma_pago = '01' THEN 'EF' ELSE 'ERR' END) AS medio_pago,
 d.nu_fac_valortotal AS importe_credito,
 '' AS documento_referencia_requerimiento,
 (CASE WHEN c.ch_fac_anulado = 'S' THEN 'A' ELSE 'E' END) AS estado_requerimiento,
 (CASE WHEN c.ch_fac_forma_pago = '01' THEN 'EF' ELSE 'ERR' END) AS medio_pago_requerimiento,
 cli.cli_ruc AS ruc_requerimiento,
 cli.cli_razsocial AS rs_requerimiento
FROM
 fac_ta_factura_cabecera AS c
 LEFT JOIN fac_ta_factura_detalle AS d ON(c.ch_fac_tipodocumento = d.ch_fac_tipodocumento AND c.ch_fac_seriedocumento = d.ch_fac_seriedocumento AND c.ch_fac_numerodocumento = d.ch_fac_numerodocumento AND c.cli_codigo = d.cli_codigo)
 LEFT JOIN int_clientes AS cli ON(c.cli_codigo = cli.cli_codigo)
WHERE
 c.ch_almacen = '" . $sucursal . "'
 AND TO_CHAR(c.dt_fac_fecha, 'YYYYMM') = '" . $fecha . "'
 AND c.ch_fac_tipodocumento IN('35','10')
		";

		/*** Agregado 2020-01-28 ***/
		// echo "<pre>";
		// print_r($query);
		// echo "</pre>";
		// die();
		/***/

		if ($sqlca->query($query) < 0) {
			return array();
		}

    	$result = array();

    	while ($reg = $sqlca->fetchRow()) {
        	$result[] = $reg;
    	}

    	return $result;
	}

	function ActualizarDatosPostrans($sucursal, $fecha, $tickes_anu) {
        global $sqlca;

// 		$query = "
// SELECT  
//  c.caja||'-'||c.trans AS id,
//  (CASE WHEN c.usr::TEXT != '' THEN SUBSTR(TRIM(c.usr::TEXT), 0, 5) ELSE pcf.nroserie::TEXT END) AS serie_documento,
//  (CASE WHEN c.usr::TEXT != '' THEN SUBSTR(TRIM(c.usr::TEXT), 6) ELSE c.trans::TEXT END) AS num_documento,
//  to_char(c.fecha, 'dd/MM/YYYY') AS fecha_emision,
//  to_char(c.fecha, 'HH12:MI:SS') AS hora_emision,
//  (CASE WHEN c.td = 'F' THEN r.ruc ELSE '99999999999' END) AS ruc,
//  (CASE WHEN c.td = 'F' THEN r.razsocial ELSE 'CLIENTE VARIOS' END) AS rs,
//  (CASE WHEN tm = 'A' THEN 'A' ELSE 'E' END) AS estado,
//  c.codigo AS articulo,
//  c.cantidad,
//  c.precio,
//  c.importe,
//  (CASE WHEN (c.fpago = '1') THEN 'EF' ELSE 'VI' END) AS medio_pago,
//  c.importe AS importe_credito,
//  c.cuenta,
//  trim(c.caja)||c.dia||trim(c.turno)||trim(c.codigo)||trim(abs(c.cantidad)::TEXT)||abs(c.importe)||trim(c.ruc)||trim(c.pump)||trim(c.tipo) AS iden
// FROM
//  pos_trans" . $fecha . " AS c
//  LEFT JOIN pos_cfg AS pcf ON(c.caja = pcf.pos)
//  LEFT JOIN ruc AS r ON(c.ruc = r.ruc)
// WHERE
//  c.es = '" . $sucursal . "'
//  AND c.td IN('B','F')
// ORDER BY
//  c.caja,
//  c.trans
// 		";

		$query = "
SELECT  
 c.caja||'-'||c.trans AS id,
 (CASE WHEN c.usr::TEXT != '' THEN SUBSTR(TRIM(c.usr::TEXT), 0, 5) ELSE pcf.nroserie::TEXT END) AS serie_documento,
 (CASE WHEN c.usr::TEXT != '' THEN SUBSTR(TRIM(c.usr::TEXT), 6) ELSE c.trans::TEXT END) AS num_documento,
 to_char(c.fecha, 'dd/MM/YYYY') AS fecha_emision,
 to_char(c.fecha, 'HH12:MI:SS') AS hora_emision,
 (CASE WHEN c.td = 'F' THEN r.ruc ELSE '99999999999' END) AS ruc,
 (CASE WHEN c.td = 'F' THEN r.razsocial ELSE 'CLIENTE VARIOS' END) AS rs,
 (CASE WHEN tm = 'A' THEN 'A' ELSE 'E' END) AS estado,
 c.codigo AS articulo,
 c.cantidad,
 c.precio,
 c.importe,
 (CASE WHEN (c.fpago = '1') THEN 'EF' ELSE 'VI' END) AS medio_pago,
 c.importe AS importe_credito,
 c.cuenta,
 trim(c.caja)||c.dia||trim(c.turno)||trim(c.codigo)||trim(abs(c.cantidad)::TEXT)||abs(c.importe)||trim(c.ruc)||trim(c.pump)||trim(c.tipo) AS iden,
 c.tm,        -- Agregado 2020-01-28
 c.td,        -- Agregado 2020-01-28
 c.trans,     -- Agregado 2020-01-28
 c.rendi_gln, -- Agregado 2020-01-28
 c.rendi_acu, -- Agregado 2020-01-28
 c.usr,       -- Agregado 2020-01-28
 (CASE WHEN tm = 'A' THEN (SELECT usr FROM pos_trans" . $fecha . " c2 WHERE c2.trans = c.rendi_gln )  ELSE NULL END) AS documento_referencia_requerimiento, -- En caso sea nota de credito, se indicara el numero de referencia, agregado 2020-01-28
 (CASE WHEN tm = 'A' THEN 'E' ELSE 'E' END) AS estado_requerimiento, -- Estado anulado, estado emitido, agregado 2020-01-28 
 (CASE WHEN (c.fpago = '1') THEN 'EF' ELSE c.at END) AS medio_pago_requerimiento,
 c.ruc as ruc_requerimiento,
 (SELECT razsocial FROM ruc rx WHERE c.ruc ~* rx.ruc) as rs_requerimiento
FROM
 pos_trans" . $fecha . " AS c
 LEFT JOIN pos_cfg AS pcf ON(c.caja = pcf.pos)
 LEFT JOIN ruc AS r ON(c.ruc = r.ruc)
WHERE
 c.es = '" . $sucursal . "'
 AND c.td IN('B','F')
ORDER BY
 c.caja,
 c.trans
		";

		/*** Agregado 2020-01-28 ***/
		// echo "<pre>";
		// print_r($query);
		// echo "</pre>";
		// die();
		/***/

		if($sqlca->query($query) < 0) {
			return array();
		}

    	$result = array();
    	$pasa = true;

       	while ($reg = $sqlca->fetchRow()) {
	    	$pasa = true;		    
	    	for ($i = 0; $i < count($tickes_anu); $i++){
				if($reg['iden'] === $tickes_anu[$i]['iden'] && $tickes_anu[$i]['estado'] == 'FALTA' && $tickes_anu[$i]['trans_tmp'] == '0') {
					$reg['estado']					= 'A';
					$tickes_anu[$i]['trans_tmp']	= $reg['num_documento'];
					$tickes_anu[$i]['iden']			= 'OK';
				  	break;
				}
	    	}
	        $result[] = $reg;
		}
        return $result;
	}

	function getTickesAnulados($sucursal, $fecha) {
	    global $sqlca;

		$query = "
SELECT
 trim(caja)||dia||trim(turno)||trim(codigo)||trim(abs(cantidad)::TEXT)||abs(importe)||trim(ruc)||trim(pump)||trim(tipo) as iden,
 'FALTA' as estado,
 '0' as trans_tmp
FROM
 pos_trans" . $fecha . "
WHERE
 es = '" . $sucursal . "'
 AND tm = 'A';
		";

		/*** Agregado 2020-01-28 ***/
		// echo "<pre>";
		// print_r($query);
		// echo "</pre>";
		// die();
		/***/

		if ($sqlca->query($query) < 0) {
			return array();
		}

		$result = array();

		while ($reg = $sqlca->fetchRow()) {
			$result[] = $reg;
		}

		return $result;
	}
}

