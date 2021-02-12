<?php
// Descomentar estas líneas, cuando estamos en modo - development

error_reporting(-1);
ini_set('display_errors', 1);

// Descomentar estas líneas, cuando estamos en modo - production
/*
ini_set('display_errors', 0);
if (version_compare(PHP_VERSION, '5.3', '>='))
{
	error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
}
else
{
	error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE);
}
*/

/**
 * Información de Tablas
 * pos_transYYYYMM - Ventas en playa CABECERA y DETALLE, la opción no debe de depender de esta tabla, ya que cuando se instala solo para oficina no se cuenta con dicha tabla.
 * val_ta_cabecera - Vales de crédito
 * fac_ta_factura_cabecera - Ventas generadas desde oficina
 **/
class modelRankingVentas {
	function array_debug($data){
		echo "<pre>";
		print_r($data);
		echo "</pre>";
	}

	public function getAllData($arrPost){
		global $sqlca;

	    $cond_almacen_pt = '';//pt = pos_transyyyymm
	    $cond_almacen_fc = '';//fc = fac_ta_factura_cabecera
	    $cond_almacen_vc = '';//vc = val_ta_cabecera
	    if ( !empty($arrPost['sAlmacen']) ) {
	    	$cond_almacen_pt = "AND PT.es = '" . $arrPost['sAlmacen'] . "'";
	    	$cond_almacen_fc = "AND VC.ch_almacen = '" . $arrPost['sAlmacen'] . "'";
	    	$cond_almacen_vc = "AND VC.ch_sucursal = '" . $arrPost['sAlmacen'] . "'";
	    }

	    $sGeneradoPor = $arrPost['sGeneradoPor'];//T=Todos, P=Playa y O=Oficina
	    $sYear = $arrPost['sYear'];
	    $sMonth = $arrPost['sMonth'];
		if( $sYear.$sMonth > date('Y').date('m') ) {
			return array(
		    	'sStatus' => 'warning',
		    	'sMessage' => 'La fecha no puede ser mayor a la actual',
		   	);
		}

		//Verificar que exista tabla pos_transYYYYMM
		if ( $sGeneradoPor != 'O' ){
			$sTable = 'pos_trans' . $sYear . $sMonth;
			$sql = "SELECT 1 FROM information_schema.tables WHERE table_schema='public' AND table_name='".$sTable."'";
			$iStatusSQL = $sqlca->query($sql);
			if ( $iStatusSQL <= 0){//No existe tabla
				return array(
			    	'sStatus' => 'warning',
			    	'sMessage' => 'No existe la tabla a consultar',
			    	'sql' => $sql,
			   	);				
			}
		}

		$sql="";
		if( $sGeneradoPor == 'T' || $sGeneradoPor == 'P' ) {
    		$sql .= "
(SELECT
 FIRST(PT.ruc) AS id_codigo_cliente,
 PT.ruc AS nu_documento_identidad_cliente,
 FIRST(R.razsocial) AS no_cliente,
 COUNT(*) AS nu_cantidad,
 SUM(PT.importe) AS ss_total
FROM
 " . $sTable . " AS PT
 LEFT JOIN ruc AS R
  USING(ruc)
WHERE
 PT.td='F'
 " . $cond_almacen_pt . "
 AND PT.tm='V'
 AND PT.grupo!='D'
GROUP BY
 PT.ruc
)
			";
		}
		if( $sGeneradoPor == 'T'){
			$sql .="
UNION
			";
		}
		if( $sGeneradoPor == 'T' || $sGeneradoPor == 'O' ) {
    		$sql .= "
(
SELECT
 FIRST(CLI.cli_codigo) AS id_codigo_cliente,
 CLI.cli_ruc AS nu_documento_identidad_cliente,
 FIRST(CLI.cli_razsocial) AS no_cliente,
 COUNT(*) AS nu_cantidad,
 SUM(CASE WHEN VC.ch_fac_moneda='01' THEN VC.nu_fac_valortotal ELSE VC.nu_fac_valortotal*TC.tca_venta_oficial END) AS ss_total
FROM
 fac_ta_factura_cabecera AS VC
 JOIN int_clientes AS CLI
  USING(cli_codigo)
 JOIN int_tipo_cambio AS TC
  ON(TC.tca_fecha = VC.dt_fac_fecha AND TC.tca_moneda = '02')
WHERE
 VC.ch_fac_tipodocumento='10'
 " . $cond_almacen_fc . "
 AND TO_CHAR(VC.dt_fac_fecha,'YYYY')='" . $sYear . "'
 AND TO_CHAR(VC.dt_fac_fecha,'MM')='" . $sMonth . "'
 AND (VC.ch_liquidacion='' OR VC.ch_liquidacion IS NULL)
GROUP BY
 CLI.cli_ruc
)
			";
		}
		if( $sGeneradoPor == 'T' || $sGeneradoPor == 'P' ) {
    		$sql .= "
UNION
(
SELECT
 FIRST(PT.ruc) AS id_codigo_cliente,
 PT.ruc AS nu_documento_identidad_cliente,
 FIRST(R.razsocial) AS no_cliente,
 COUNT(*) AS nu_cantidad,
 SUM(PT.importe) AS ss_total
FROM
 " . $sTable . " AS PT
 LEFT JOIN ruc AS R
  USING(ruc)
WHERE
 PT.td='B'
 " . $cond_almacen_pt . "
 AND PT.tm='V'
 AND PT.grupo!='D'
 AND LENGTH(PT.ruc)=8
 AND PT.ruc IS NOT NULL
 AND PT.ruc!=''
GROUP BY
 PT.ruc
)
			";
		}
		if( $sGeneradoPor == 'T' || $sGeneradoPor == 'O' ) {
    		$sql .= "
UNION
(
SELECT
 FIRST(CLI.cli_codigo) AS id_codigo_cliente,
 CLI.cli_ruc AS nu_documento_identidad_cliente,
 FIRST(CLI.cli_razsocial) AS no_cliente,
 COUNT(*) AS nu_cantidad,
 SUM(VC.nu_fac_valortotal) AS ss_total
FROM
 fac_ta_factura_cabecera AS VC
 JOIN int_clientes AS CLI
  USING(cli_codigo)
 JOIN int_tipo_cambio AS TC
  ON(TC.tca_fecha = VC.dt_fac_fecha AND TC.tca_moneda = '02')
WHERE
 VC.ch_fac_tipodocumento='35'
 " . $cond_almacen_fc . "
 AND TO_CHAR(VC.dt_fac_fecha,'YYYY')='" . $sYear . "'
 AND TO_CHAR(VC.dt_fac_fecha,'MM')='" . $sMonth . "'
 AND (VC.ch_liquidacion='' OR VC.ch_liquidacion IS NULL)
GROUP BY
 CLI.cli_ruc
)
			";
		}
		if( $sGeneradoPor == 'T' || $sGeneradoPor == 'P' ) {
    		$sql .= "
UNION
(
SELECT
 FIRST(VC.ch_cliente) AS id_codigo_cliente,
 VC.ch_cliente AS nu_documento_identidad_cliente,
 FIRST(CLI.cli_razsocial) AS no_cliente,
 COUNT(*) AS nu_cantidad,
 SUM(VC.nu_importe) AS ss_total
FROM
 val_ta_cabecera AS VC
 LEFT JOIN int_clientes AS CLI
  ON(VC.ch_cliente = CLI.cli_codigo)
WHERE
 TO_CHAR(VC.dt_fecha,'YYYY')='" . $sYear . "'
 " . $cond_almacen_vc . "
 AND TO_CHAR(VC.dt_fecha,'MM')='" . $sMonth . "'
GROUP BY
 VC.ch_cliente
)
			";
		}
$sql .= "
ORDER BY
 5 DESC,3,2;
 		";

		$iStatusSQL = $sqlca->query($sql);
		if ((int)$iStatusSQL > 0){
	    	return array(
	    		'sStatus' => 'success',
	    		'sMessage' => 'Registros Encontrados',
	    		'arrData' => $sqlca->fetchAll()
	    	);
		} else if ($iStatusSQL == 0) {
			return array(
				'sStatus' => 'warning',
				'sMessage' => 'No hay registros',
			);
		}
		return array(
	    	'sStatus' => 'danger',
	    	'sMessage' => 'Problemas al obtener datos <br>' . $sqlca->get_error(),
            'sMessageSQL' => $sqlca->get_error(),
            'SQL' => $sql,
	   	);
	}

	public function getAllDataDetail($arrPost){
		global $sqlca;

	    $cond_almacen_pt = '';//pt = pos_transyyyymm
	    $cond_almacen_fc = '';//fc = fac_ta_factura_cabecera
	    $cond_almacen_vc = '';//vc = val_ta_cabecera
	    if ( !empty($arrPost['sAlmacen']) ) {
	    	$cond_almacen_pt = "AND es = '" . $arrPost['sAlmacen'] . "'";
	    	$cond_almacen_fc = "AND VC.ch_almacen = '" . $arrPost['sAlmacen'] . "'";
	    	$cond_almacen_vc = "AND ch_sucursal = '" . $arrPost['sAlmacen'] . "'";
	    }

	    $sGeneradoPor = $arrPost['sGeneradoPor'];//T=Todos, P=Playa y O=Oficina
	    $sYear = $arrPost['sYear'];
	    $sMonth = $arrPost['sMonth'];
	    $sIdCliente = $arrPost['sIdCliente'];
		if( $sYear.$sMonth > date('Y').date('m') ) {
			return array(
		    	'sStatus' => 'warning',
		    	'sMessage' => 'La fecha no puede ser mayor a la actual',
		   	);
		}

		//Verificar que exista tabla pos_transYYYYMM
		if ( $sGeneradoPor != 'O' ){
			$sTable = 'pos_trans' . $sYear . $sMonth;
			$sql = "SELECT 1 FROM information_schema.tables WHERE table_schema='public' AND table_name='".$sTable."'";
			$iStatusSQL = $sqlca->query($sql);
			if ( $iStatusSQL <= 0){//No existe tabla
				return array(
			    	'sStatus' => 'warning',
			    	'sMessage' => 'No existe la tabla a consultar',
			    	'sql' => $sql,
			   	);				
			}
		}

		$sql="";
		if( $sGeneradoPor == 'T' || $sGeneradoPor == 'P' ) {
    		$sql .= "
(SELECT
 TO_CHAR(fecha, 'DD/MM/YYYY') AS fe_emision,
 (CASE
  WHEN tm='V' AND td='B' THEN 'B/VENTA'
  WHEN tm='V' AND td='F' THEN 'FACTURA'
  ELSE 'N/CRED.'
 END) AS no_tipo_documento,
 SUBSTRING(usr, 1, 4) AS no_serie_documento,
 SUBSTRING(usr, 6, 8) AS no_numero_documento,
 importe AS ss_total
FROM
 " . $sTable . " AS PT
WHERE
 td='F'
 AND ruc='" . $sIdCliente . "'
 " . $cond_almacen_pt . "
 AND tm='V'
 AND grupo!='D'
)
			";
		}
		if( $sGeneradoPor == 'T'){
			$sql .="
UNION
			";
		}
		if( $sGeneradoPor == 'T' || $sGeneradoPor == 'O' ) {
    		$sql .= "
(
SELECT
 TO_CHAR(VC.dt_fac_fecha, 'DD/MM/YYYY') AS fe_emision,
 TDOCU.tab_desc_breve AS no_tipo_documento,
 VC.ch_fac_seriedocumento AS no_serie_documento,
 VC.ch_fac_numerodocumento AS no_numero_documento,
 (CASE WHEN VC.ch_fac_moneda='01' THEN VC.nu_fac_valortotal ELSE VC.nu_fac_valortotal*TC.tca_venta_oficial END) AS ss_total
FROM
 fac_ta_factura_cabecera AS VC
 JOIN int_tabla_general AS TDOCU
  ON (SUBSTRING(TDOCU.tab_elemento, 5) = VC.ch_fac_tipodocumento AND TDOCU.tab_tabla ='08' AND TDOCU.tab_elemento != '000000')
 JOIN int_tipo_cambio AS TC
  ON(TC.tca_fecha = VC.dt_fac_fecha AND TC.tca_moneda = '02')
WHERE
 VC.ch_fac_tipodocumento='10'
 AND VC.cli_codigo='" . $sIdCliente . "'
 " . $cond_almacen_fc . "
 AND TO_CHAR(VC.dt_fac_fecha,'YYYY')='" . $sYear . "'
 AND TO_CHAR(VC.dt_fac_fecha,'MM')='" . $sMonth . "'
 AND (VC.ch_liquidacion='' OR VC.ch_liquidacion IS NULL)
)
			";
		}
		if( $sGeneradoPor == 'T' || $sGeneradoPor == 'P' ) {
    		$sql .= "
UNION
(
SELECT
 TO_CHAR(fecha, 'DD/MM/YYYY') AS fe_emision,
 (CASE
  WHEN tm='V' AND td='B' THEN 'B/VENTA'
  WHEN tm='V' AND td='F' THEN 'FACTURA'
  ELSE 'N/CRED.'
 END) AS no_tipo_documento,
 SUBSTRING(usr, 1, 4) AS no_serie_documento,
 SUBSTRING(usr, 6, 8) AS no_numero_documento,
 importe AS ss_total
FROM
 " . $sTable . "
WHERE
 td='B'
 AND ruc='" . $sIdCliente . "'
 " . $cond_almacen_pt . "
 AND tm='V'
 AND grupo!='D'
 AND LENGTH(ruc)=8
 AND ruc IS NOT NULL
 AND ruc!=''
)
			";
		}
		if( $sGeneradoPor == 'T' || $sGeneradoPor == 'O' ) {
    		$sql .= "
UNION
(
SELECT
 TO_CHAR(VC.dt_fac_fecha, 'DD/MM/YYYY') AS fe_emision,
 TDOCU.tab_desc_breve AS no_tipo_documento,
 VC.ch_fac_seriedocumento AS no_serie_documento,
 VC.ch_fac_numerodocumento AS no_numero_documento,
 (CASE WHEN VC.ch_fac_moneda='01' THEN VC.nu_fac_valortotal ELSE VC.nu_fac_valortotal*TC.tca_venta_oficial END) AS ss_total
FROM
 fac_ta_factura_cabecera AS VC
 JOIN int_tabla_general AS TDOCU
  ON (SUBSTRING(TDOCU.tab_elemento, 5) = VC.ch_fac_tipodocumento AND TDOCU.tab_tabla ='08' AND TDOCU.tab_elemento != '000000')
 JOIN int_tipo_cambio AS TC
  ON(TC.tca_fecha = VC.dt_fac_fecha AND TC.tca_moneda = '02')
WHERE
 VC.ch_fac_tipodocumento='35'
 AND VC.cli_codigo='" . $sIdCliente . "'
 " . $cond_almacen_fc . "
 AND TO_CHAR(VC.dt_fac_fecha,'YYYY')='" . $sYear . "'
 AND TO_CHAR(VC.dt_fac_fecha,'MM')='" . $sMonth . "'
 AND (VC.ch_liquidacion='' OR VC.ch_liquidacion IS NULL)
)
			";
		}
		if( $sGeneradoPor == 'T' || $sGeneradoPor == 'P' ) {
    		$sql .= "
UNION
(
SELECT
 TO_CHAR(dt_fecha, 'DD/MM/YYYY') AS fe_emision,
 'VALES' AS no_tipo_documento,
 '000' AS no_serie_documento,
 ch_documento AS no_numero_documento,
 nu_importe AS ss_total
FROM
 val_ta_cabecera
WHERE
 TO_CHAR(dt_fecha,'YYYY')='" . $sYear . "'
 AND ch_cliente='" . $sIdCliente . "'
 " . $cond_almacen_vc . "
 AND TO_CHAR(dt_fecha,'MM')='" . $sMonth . "'
)
			";
		}
$sql .= "
ORDER BY
 4 DESC,2,1;
 		";

		$iStatusSQL = $sqlca->query($sql);
		if ((int)$iStatusSQL > 0){
	    	return array(
	    		'sStatus' => 'success',
	    		'sMessage' => 'Registros Encontrados',
	    		'arrData' => $sqlca->fetchAll()
	    	);
		} else if ($iStatusSQL == 0) {
			return array(
				'sStatus' => 'warning',
				'sMessage' => 'No hay registros',
			);
		}
		return array(
	    	'sStatus' => 'danger',
	    	'sMessage' => 'Problemas al obtener datos <br>' . $sqlca->get_error(),
            'sMessageSQL' => $sqlca->get_error(),
            'SQL' => $sql,
	   	);
	}
}