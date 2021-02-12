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


ini_set("memory_limit", '3G');
ini_set("upload_max_filesize", "15M");
ini_set("post_max_size", "15M");
ini_set('max_execution_time', '700');
ini_set('max_input_time', '700');

class Siscont_Model extends Model {

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

  function getManualInvoiceSale( $arrParams ){
    global $sqlca;

		/*
		Tipo de venta:
      1 > Documentos Electrónicos
      2 > Documentos Electrónicos y Tickets
      3 > Tickets y Documentos Manuales
	  */
    //$where_filtro_tipo_documento = ($arrParams["iTypeSale"] == "1" ? "AND SUBSTRING(ch_fac_seriedocumento FROM '[A-Z]+') != ''" : "AND SUBSTRING(ch_fac_seriedocumento FROM '[A-Z]+') IS NULL");

    $sql = "
SELECT
 VC.dt_fac_fecha::DATE AS emision,
 VC.fe_vencimiento::DATE AS vencimiento,
 TRIM(TDOCU.tab_car_03) AS tipo,
 VC.ch_fac_seriedocumento AS serie,
 VC.ch_fac_numerodocumento AS numero,
 (CASE
  WHEN TRIM(CLI.cli_ruc) = '99999999999' THEN '0'
  WHEN TRIM(CLI.cli_ruc) = '999999999' THEN '0'
  WHEN TRIM(CLI.cli_ruc) = '11111111' THEN '0'  
  WHEN CHAR_LENGTH(TRIM(CLI.cli_ruc)) = 11 THEN '6'
  WHEN CHAR_LENGTH(TRIM(CLI.cli_ruc)) = 8 THEN '1'
 ELSE
  '0'
 END) AS tipodi,
 (CASE
  WHEN VC.ch_fac_tipodocumento != '20' THEN
  CASE
   WHEN TRIM(CLI.cli_ruc) = '99999999999' THEN '00000000008'
   WHEN TRIM(CLI.cli_ruc) = '999999999' THEN '00000000008'
   WHEN TRIM(CLI.cli_ruc) = '11111111' THEN '00000000008'
  ELSE
   TRIM(CLI.cli_ruc)
  END
 ELSE
  CASE
   WHEN TRIM(CLI.cli_ruc) = '99999999999' THEN '00000000009'
   WHEN TRIM(CLI.cli_ruc) = '999999999' THEN '00000000009'
   WHEN TRIM(CLI.cli_ruc) = '11111111' THEN '00000000009'
  ELSE
   TRIM(CLI.cli_ruc)
  END
 END) AS ruc,
 (CASE
  WHEN VC.ch_fac_tipodocumento = '35' AND SUBSTRING(ch_fac_seriedocumento FROM '[A-Z]+') IS NULL THEN 'Consolidado boleta'
  WHEN VC.ch_fac_tipodocumento = '35' AND SUBSTRING(ch_fac_seriedocumento FROM '[A-Z]+') != '' THEN 'Consolidado de boleta de venta electronica'
  WHEN VC.ch_fac_tipodocumento = '20' AND (string_to_array(VCOM.ch_fac_observacion2, '*'))[3] = '35' AND SUBSTRING(ch_fac_seriedocumento FROM '[A-Z]+') != '' THEN 'Consolidado de nota de credito bve'
  ELSE SUBSTR(CLI.cli_razsocial, 0, 60)
 END) AS cliente,
 (CASE WHEN SUBSTRING(CLI.cli_ruc, 1, 2) = '10' THEN
  CASE
   WHEN VC.ch_fac_tipodocumento = '35' THEN ''
   WHEN VC.ch_fac_tipodocumento != '35' THEN split_part(CLI.cli_razsocial, ' ', 1)
  END
 END) as no_apellido_paterno,
 (CASE WHEN SUBSTRING(CLI.cli_ruc, 1, 2) = '10' THEN
  CASE
   WHEN VC.ch_fac_tipodocumento = '35' THEN ''
   WHEN VC.ch_fac_tipodocumento != '35' THEN split_part(CLI.cli_razsocial, ' ', 2)
  END
 END) as no_apellido_materno,
 (CASE WHEN SUBSTRING(CLI.cli_ruc, 1, 2) = '10' THEN
  CASE
   WHEN VC.ch_fac_tipodocumento = '35' THEN ''
   WHEN VC.ch_fac_tipodocumento != '35' THEN split_part(CLI.cli_razsocial, ' ', 3)
  END
 END) as no_nombre,
 TC.tca_venta_oficial AS tipocambio,
 (string_to_array(VCOM.ch_fac_observacion2, '*'))[2]||'-'||(string_to_array(VCOM.ch_fac_observacion2, '*'))[1] AS serie_numero_referencia,
 CASE WHEN VCOM.ch_fac_observacion3 != '' THEN TO_CHAR(VCOM.ch_fac_observacion3::DATE, 'DD/MM/YY') ELSE '' END AS fe_emision_referencia,
 TDOCUREFE.tab_car_03 AS nu_tipo_referencia,
 ROUND(nu_fac_valorbruto, ".$arrParams["iNumberDecimal"].") AS imponible,
 ROUND(nu_fac_impuesto1, ".$arrParams["iNumberDecimal"].") AS igv,
 ROUND(nu_fac_valortotal, ".$arrParams["iNumberDecimal"].") AS total
FROM
 fac_ta_factura_cabecera AS VC
 LEFT JOIN fac_ta_factura_complemento AS VCOM
  USING (ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento, cli_codigo)
 JOIN int_clientes AS CLI
  USING (cli_codigo)
 JOIN int_tabla_general AS TDOCU
  ON (SUBSTRING(TDOCU.tab_elemento, 5) = VC.ch_fac_tipodocumento AND TDOCU.tab_tabla ='08' AND TDOCU.tab_elemento != '000000')
 LEFT JOIN int_tabla_general AS TDOCUREFE
  ON (SUBSTRING(TDOCUREFE.tab_elemento, 5) = (string_to_array(VCOM.ch_fac_observacion2, '*'))[3] AND TDOCUREFE.tab_tabla ='08' AND TDOCUREFE.tab_elemento != '000000')
 LEFT JOIN int_tipo_cambio TC ON (VC.dt_fac_fecha = TC.tca_fecha)
WHERE
 VC.ch_almacen='".$arrParams["sCodeWarehouse"]."'
 AND EXTRACT(YEAR FROM VC.dt_fac_fecha)='".$arrParams["dYear"]."'
 AND EXTRACT(MONTH FROM VC.dt_fac_fecha)='".$arrParams["dMonth"]."'
ORDER BY
 VC.dt_fac_fecha,
 tipo,
 serie,
 numero
      ";
      $iStatusSQL = $sqlca->query($sql);
      $arrResponse = array(
        'status_sql' => $iStatusSQL,
        'message_sql' => $sqlca->get_error(),
        'sStatus' => 'danger',
        'sMessage' => 'problemas al obtener ventas manuales (ventas)',
      );
      if ( $iStatusSQL == 0 ) {
        $arrResponse = array(
          'sStatus' => 'warning',
          'sMessage' => 'No hay registros'
        );
      } else if ( $iStatusSQL > 0 ) {
        $arrDataSQL = $sqlca->fetchAll();
        $arrResponse = array(
          'sStatus' => 'success',
          'arrData' => $arrDataSQL
        );
      }
      return $arrResponse;
    }

    function getManualInvoiceSaleReceivable( $arrParams ){
      global $sqlca;

      $sql = "
SELECT * FROM(
 SELECT
  VC.dt_fac_fecha::DATE as emision,
  FIRST(TDOCU.tab_car_03)::TEXT AS tipo,
  VC.ch_fac_seriedocumento AS serie,
  VC.ch_fac_numerodocumento::TEXT AS numero,
  (CASE WHEN VC.ch_fac_tipodocumento='35' THEN '0' ELSE '6' END) as tipodi,
  (CASE
   WHEN VC.ch_fac_tipodocumento='35' AND (SUBSTRING(VC.ch_fac_seriedocumento FROM '[A-Z]+') IS NULL OR SUBSTRING(VC.ch_fac_seriedocumento FROM '[A-Z]+') ='') THEN '00000000004'
   WHEN VC.ch_fac_tipodocumento='35' AND SUBSTRING(VC.ch_fac_seriedocumento FROM '[A-Z]+') IN('B') THEN FIRST(cuentas.id_cliente_boleta)
   WHEN VC.ch_fac_tipodocumento='20' AND FIRST((string_to_array(VCOM.ch_fac_observacion2, '*'))[3])='35' THEN '00000000009'
   WHEN VC.ch_fac_tipodocumento='10' THEN FIRST(CLI.cli_ruc)
  END) AS ruc,
  (CASE
   WHEN VC.ch_fac_tipodocumento='35' AND (SUBSTRING(VC.ch_fac_seriedocumento FROM '[A-Z]+') IS NULL OR SUBSTRING(VC.ch_fac_seriedocumento FROM '[A-Z]+') ='') THEN 'Consolidado ticket boleta'
   WHEN VC.ch_fac_tipodocumento='35' AND SUBSTRING(VC.ch_fac_seriedocumento FROM '[A-Z]+') IN('B') THEN FIRST(cuentas.no_rsocial_cliente_boleta)
   WHEN VC.ch_fac_tipodocumento='20' AND FIRST((string_to_array(VCOM.ch_fac_observacion2, '*'))[3]) = '35' THEN 'Consolidado de nota de credito bve'
  ELSE SUBSTR(FIRST(CLI.cli_razsocial),0,60)
   END) AS cliente,
  (CASE WHEN SUBSTRING(FIRST(CLI.cli_ruc), 1, 2) = '10' THEN
   CASE
    WHEN VC.ch_fac_tipodocumento='B' THEN ''
    WHEN VC.ch_fac_tipodocumento='F' AND SUBSTR(FIRST(CLI.cli_ruc),1,1) = '1' THEN split_part(FIRST(CLI.cli_razsocial), ' ', 1)
   END
  END) AS no_apellido_paterno,
  (CASE WHEN SUBSTRING(FIRST(CLI.cli_ruc), 1, 2) = '10' THEN
   CASE
    WHEN VC.ch_fac_tipodocumento='B' THEN ''
    WHEN VC.ch_fac_tipodocumento='F' AND SUBSTR(FIRST(CLI.cli_ruc),1,1) = '1' THEN split_part(FIRST(CLI.cli_razsocial), ' ', 2)
   END
  END) AS no_apellido_materno,
  (CASE WHEN SUBSTRING(FIRST(CLI.cli_ruc), 1, 2) = '10' THEN
   CASE
    WHEN VC.ch_fac_tipodocumento='B' THEN ''
    WHEN VC.ch_fac_tipodocumento='F' AND SUBSTR(FIRST(CLI.cli_ruc),1,1) = '1' THEN split_part(FIRST(CLI.cli_razsocial), ' ', 3) || ' ' || split_part(FIRST(CLI.cli_razsocial), ' ', 4)
   END
  END) AS no_nombre,
  ROUND(SUM(VC.nu_fac_valortotal), ".$arrParams["iNumberDecimal"].") AS total,
  FIRST(TC.tca_venta_oficial) AS tipocambio,
  nu_cuentacontable AS cuentacontable,
  ''::TEXT AS no_flujoefectivo,
  ''::TEXT AS nu_mediopago,
  TO_CHAR(VC.dt_fac_fecha::DATE,'DD') AS nucorrelativo,
  '2'::TEXT AS orden,
  ''::TEXT AS turno,
	''::TEXT AS txt_glosa
 FROM
  fac_ta_factura_cabecera AS VC
  LEFT JOIN fac_ta_factura_complemento AS VCOM
   USING (ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento, cli_codigo)
  JOIN int_clientes AS CLI
   USING (cli_codigo)
  JOIN int_tabla_general AS TDOCU
   ON (SUBSTRING(TDOCU.tab_elemento, 5) = VC.ch_fac_tipodocumento AND TDOCU.tab_tabla ='08' AND TDOCU.tab_elemento != '000000')
  LEFT JOIN int_tabla_general AS TDOCUREFE
   ON (SUBSTRING(TDOCUREFE.tab_elemento, 5) = (string_to_array(VCOM.ch_fac_observacion2, '*'))[3] AND TDOCUREFE.tab_tabla ='08' AND TDOCUREFE.tab_elemento != '000000')
  LEFT JOIN int_tipo_cambio AS TC ON (VC.dt_fac_fecha = TC.tca_fecha)
  LEFT JOIN cuentas_contables_siscont AS cuentas ON (VC.nu_tipo_pago = '0'||cuentas.nu_fpago AND nu_tipooperacion = '04')
 WHERE
  VC.ch_almacen='".$arrParams["sCodeWarehouse"]."'
  AND EXTRACT(YEAR FROM VC.dt_fac_fecha)='".$arrParams["dYear"]."'
  AND EXTRACT(MONTH FROM VC.dt_fac_fecha)='".$arrParams["dMonth"]."'
  AND VC.nu_tipo_pago='02'
 GROUP BY
  VC.ch_almacen,
  VC.dt_fac_fecha,
  VC.nu_tipo_pago,
  nu_cuentacontable,
  VC.ch_fac_tipodocumento,
  VC.ch_fac_seriedocumento,
  VC.ch_fac_numerodocumento
 ) AS DM
 UNION ALL
 (
 SELECT
  VC.dt_fac_fecha::DATE as emision,
  FIRST(TDOCU.tab_car_03)::TEXT AS tipo,
  VC.ch_fac_seriedocumento AS serie,
  VC.ch_fac_numerodocumento::TEXT AS numero,
  (CASE WHEN VC.ch_fac_tipodocumento='35' THEN '0' ELSE '6' END) as tipodi,
  (CASE
   WHEN VC.ch_fac_tipodocumento='35' AND (SUBSTRING(VC.ch_fac_seriedocumento FROM '[A-Z]+') IS NULL OR SUBSTRING(VC.ch_fac_seriedocumento FROM '[A-Z]+') ='') THEN '00000000004'
   WHEN VC.ch_fac_tipodocumento='35' AND SUBSTRING(VC.ch_fac_seriedocumento FROM '[A-Z]+') IN('B') THEN FIRST(cuentas.id_cliente_boleta)
   WHEN VC.ch_fac_tipodocumento='20' AND FIRST((string_to_array(VCOM.ch_fac_observacion2, '*'))[3])='35' THEN '00000000009'
   WHEN VC.ch_fac_tipodocumento='10' THEN FIRST(CLI.cli_ruc)
  END) AS ruc,
  (CASE
   WHEN VC.ch_fac_tipodocumento='35' AND (SUBSTRING(VC.ch_fac_seriedocumento FROM '[A-Z]+') IS NULL OR SUBSTRING(VC.ch_fac_seriedocumento FROM '[A-Z]+') ='') THEN 'Consolidado ticket boleta'
   WHEN VC.ch_fac_tipodocumento='35' AND SUBSTRING(VC.ch_fac_seriedocumento FROM '[A-Z]+') IN('B') THEN FIRST(cuentas.no_rsocial_cliente_boleta)
   WHEN VC.ch_fac_tipodocumento='20' AND FIRST((string_to_array(VCOM.ch_fac_observacion2, '*'))[3]) = '35' THEN 'Consolidado de nota de credito bve'
  ELSE SUBSTR(FIRST(CLI.cli_razsocial),0,60)
   END) AS cliente,
  (CASE WHEN SUBSTRING(FIRST(CLI.cli_ruc), 1, 2) = '10' THEN
   CASE
    WHEN VC.ch_fac_tipodocumento='B' THEN ''
    WHEN VC.ch_fac_tipodocumento='F' AND SUBSTR(FIRST(CLI.cli_ruc),1,1) = '1' THEN split_part(FIRST(CLI.cli_razsocial), ' ', 1)
   END
  END) AS no_apellido_paterno,
  (CASE WHEN SUBSTRING(FIRST(CLI.cli_ruc), 1, 2) = '10' THEN
   CASE
    WHEN VC.ch_fac_tipodocumento='B' THEN ''
    WHEN VC.ch_fac_tipodocumento='F' AND SUBSTR(FIRST(CLI.cli_ruc),1,1) = '1' THEN split_part(FIRST(CLI.cli_razsocial), ' ', 2)
   END
  END) AS no_apellido_materno,
  (CASE WHEN SUBSTRING(FIRST(CLI.cli_ruc), 1, 2) = '10' THEN
   CASE
    WHEN VC.ch_fac_tipodocumento='B' THEN ''
    WHEN VC.ch_fac_tipodocumento='F' AND SUBSTR(FIRST(CLI.cli_ruc),1,1) = '1' THEN split_part(FIRST(CLI.cli_razsocial), ' ', 3) || ' ' || split_part(FIRST(CLI.cli_razsocial), ' ', 4)
   END   
  END) AS no_nombre,
  ROUND(SUM(VC.nu_fac_valortotal), ".$arrParams["iNumberDecimal"].") AS total,
  FIRST(TC.tca_venta_oficial) AS tipocambio,
  (SELECT nu_cuentacontable FROM cuentas_contables_siscont WHERE nu_id=6)::INTEGER AS cuentacontable,
  ''::TEXT AS no_flujoefectivo,
  ''::TEXT AS nu_mediopago,
  TO_CHAR(VC.dt_fac_fecha::DATE,'DD') AS nucorrelativo,
  '3'::TEXT AS orden,
  ''::TEXT AS turno,
	''::TEXT AS txt_glosa
 FROM
  fac_ta_factura_cabecera AS VC
  LEFT JOIN fac_ta_factura_complemento AS VCOM
   USING(ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento, cli_codigo)
  JOIN int_clientes AS CLI
   USING(cli_codigo)
  JOIN int_tabla_general AS TDOCU
   ON(SUBSTRING(TDOCU.tab_elemento, 5) = VC.ch_fac_tipodocumento AND TDOCU.tab_tabla ='08' AND TDOCU.tab_elemento != '000000')
  LEFT JOIN int_tabla_general AS TDOCUREFE
   ON(SUBSTRING(TDOCUREFE.tab_elemento, 5) = (string_to_array(VCOM.ch_fac_observacion2, '*'))[3] AND TDOCUREFE.tab_tabla ='08' AND TDOCUREFE.tab_elemento != '000000')
	LEFT JOIN int_tipo_cambio AS TC
	 ON(VC.dt_fac_fecha=TC.tca_fecha)
	LEFT JOIN cuentas_contables_siscont AS cuentas
	 ON(VC.nu_tipo_pago='0'||cuentas.nu_fpago AND nu_tipooperacion='04')
 WHERE
  VC.ch_almacen='".$arrParams["sCodeWarehouse"]."'
  AND EXTRACT(YEAR FROM VC.dt_fac_fecha)='".$arrParams["dYear"]."'
  AND EXTRACT(MONTH FROM VC.dt_fac_fecha)='".$arrParams["dMonth"]."'
  AND VC.nu_tipo_pago!='06'
 GROUP BY
  VC.ch_almacen,
  VC.dt_fac_fecha,
  VC.nu_tipo_pago,
  nu_cuentacontable,
  VC.ch_fac_tipodocumento,
  VC.ch_fac_seriedocumento,
  VC.ch_fac_numerodocumento
 )
 UNION ALL
 (
SELECT DISTINCT
 CP.created::DATE AS emision,
 'DP' AS tipo,
 '' AS serie,
 CP.pay_number AS numero,
 (CASE
 WHEN TRIM(CLI.cli_ruc) = '99999999999' THEN '0'
 WHEN TRIM(CLI.cli_ruc) = '999999999' THEN '0'
 WHEN TRIM(CLI.cli_ruc) = '11111111' THEN '0'  
 WHEN CHAR_LENGTH(TRIM(CLI.cli_ruc)) = 11 THEN '6'
 WHEN CHAR_LENGTH(TRIM(CLI.cli_ruc)) = 8 THEN '1'
 ELSE
 '0'
 END) AS tipodi,
 CLI.cli_ruc AS ruc,
 CLI.cli_razsocial AS cliente,
 '' AS no_apellido_paterno,
 '' AS no_apellido_materno,
 '' AS no_nombre,
 ROUND(CP.amount, 2) AS total,
 TC.tca_venta_oficial AS tipocambio,
 nu_cuentacontable AS cuentacontable,
 ''::TEXT AS no_flujoefectivo,
 ''::TEXT AS nu_mediopago,
 TO_CHAR(CP.created, 'DD') AS nucorrelativo,
 '2'::TEXT AS orden,
 ''::TEXT AS turno,
 CM.name||' - '||CP.pay_number::TEXT AS txt_glosa
FROM 
 c_cash_transaction AS CC
 JOIN c_cash_transaction_detail AS CD
  USING(c_cash_transaction_id)
 JOIN int_clientes AS CLI
  ON(CLI.cli_codigo=CC.bpartner)
 JOIN c_cash_transaction_payment AS CP
  USING(c_cash_transaction_id)
 JOIN c_bank AS BC
  USING(c_bank_id)
 JOIN int_tipo_cambio AS TC
  ON(CC.d_system=TC.tca_fecha)
 JOIN cuentas_contables_siscont AS cuentas
  ON(CP.c_cash_mpayment_id=cuentas.nu_mediopago::NUMERIC AND nu_tipooperacion='04' AND CP.c_bank_id=cuentas.id_bank)
 JOIN c_cash_mpayment AS CM
  USING(c_cash_mpayment_id)
WHERE
 CC.ware_house='".$arrParams["sCodeWarehouse"]."'
 AND EXTRACT(YEAR FROM CC.d_system)='".$arrParams["dYear"]."'
 AND EXTRACT(MONTH FROM CC.d_system)='".$arrParams["dMonth"]."'
 AND CD.doc_type='10'
 )
 UNION ALL
 (
SELECT DISTINCT
 CP.created::DATE AS emision,
 'DP' AS tipo,
 '' AS serie,
 CP.pay_number AS numero,
 (CASE
 WHEN TRIM(CLI.cli_ruc) = '99999999999' THEN '0'
 WHEN TRIM(CLI.cli_ruc) = '999999999' THEN '0'
 WHEN TRIM(CLI.cli_ruc) = '11111111' THEN '0'  
 WHEN CHAR_LENGTH(TRIM(CLI.cli_ruc)) = 11 THEN '6'
 WHEN CHAR_LENGTH(TRIM(CLI.cli_ruc)) = 8 THEN '1'
 ELSE
 '0'
 END) AS tipodi,
 cuentas.id_cliente_boleta AS ruc,
 cuentas.no_rsocial_cliente_boleta AS cliente,
 '' AS no_apellido_paterno,
 '' AS no_apellido_materno,
 '' AS no_nombre,
 ROUND(CP.amount, 2) AS total,
 TC.tca_venta_oficial AS tipocambio,
 nu_cuentacontable AS cuentacontable,
 ''::TEXT AS no_flujoefectivo,
 ''::TEXT AS nu_mediopago,
 TO_CHAR(CP.created, 'DD') AS nucorrelativo,
 '2'::TEXT AS orden,
 ''::TEXT AS turno,
 CM.name||' - '||CP.pay_number::TEXT AS txt_glosa
FROM 
 c_cash_transaction AS CC
 JOIN c_cash_transaction_detail AS CD
 USING(c_cash_transaction_id)
 JOIN int_clientes AS CLI
 ON(CLI.cli_codigo=CC.bpartner)
 JOIN c_cash_transaction_payment AS CP
 USING(c_cash_transaction_id)
 JOIN c_bank AS BC
 USING(c_bank_id)
 JOIN int_tipo_cambio AS TC
 ON(CC.d_system=TC.tca_fecha)
 JOIN cuentas_contables_siscont AS cuentas
 ON(CP.c_cash_mpayment_id=cuentas.nu_mediopago::NUMERIC AND nu_tipooperacion='04' AND CP.c_bank_id=cuentas.id_bank)
 JOIN c_cash_mpayment AS CM
 USING(c_cash_mpayment_id)
WHERE
 CC.ware_house='".$arrParams["sCodeWarehouse"]."'
 AND EXTRACT(YEAR FROM CC.d_system)='".$arrParams["dYear"]."'
 AND EXTRACT(MONTH FROM CC.d_system)='".$arrParams["dMonth"]."'
 AND CD.doc_type='35'
 )
 UNION ALL
 (
SELECT DISTINCT
 CP.created AS emision,
 TDOCU.tab_car_03::TEXT AS tipo,
 CD.doc_serial_number AS serie,
 CD.doc_number AS numero,
 (CASE
 WHEN TRIM(CLI.cli_ruc) = '99999999999' THEN '0'
 WHEN TRIM(CLI.cli_ruc) = '999999999' THEN '0'
 WHEN TRIM(CLI.cli_ruc) = '11111111' THEN '0'  
 WHEN CHAR_LENGTH(TRIM(CLI.cli_ruc)) = 11 THEN '6'
 WHEN CHAR_LENGTH(TRIM(CLI.cli_ruc)) = 8 THEN '1'
 ELSE
 '0'
 END) AS tipodi,
 (CASE WHEN CD.doc_type='10' THEN CLI.cli_ruc ELSE cuentas.id_cliente_boleta END) AS ruc,
 (CASE WHEN CD.doc_type='10' THEN CLI.cli_razsocial ELSE cuentas.no_rsocial_cliente_boleta END) AS cliente,
 '' AS no_apellido_paterno,
 '' AS no_apellido_materno,
 '' AS no_nombre,
 ROUND((CASE WHEN COBCAB.nu_importesaldo > 0.00 THEN CP.amount ELSE COBCAB.nu_importetotal END), 2) AS total,
 TC.tca_venta_oficial AS tipocambio,
 (SELECT nu_cuentacontable FROM cuentas_contables_siscont WHERE nu_id=6) AS cuentacontable,
 ''::TEXT AS no_flujoefectivo,
 ''::TEXT AS nu_mediopago,
 TO_CHAR(CP.created, 'DD') AS nucorrelativo,
 '2'::TEXT AS orden,
 ''::TEXT AS turno,
 CM.name||' - '||CP.pay_number::TEXT AS txt_glosa
FROM 
 c_cash_transaction AS CC
 JOIN c_cash_transaction_detail AS CD
  USING(c_cash_transaction_id)
 JOIN ccob_ta_cabecera AS COBCAB
  ON(COBCAB.cli_codigo=CC.bpartner AND COBCAB.ch_tipdocumento=CD.doc_type AND COBCAB.ch_seriedocumento=CD.doc_serial_number AND COBCAB.ch_numdocumento=CD.doc_number)
 JOIN int_clientes AS CLI
  ON(CLI.cli_codigo=CC.bpartner)
 JOIN c_cash_transaction_payment AS CP
  USING(c_cash_transaction_id)
 JOIN c_bank AS BC
  USING(c_bank_id)
 JOIN int_tipo_cambio AS TC
  ON(CC.d_system=TC.tca_fecha)
 JOIN cuentas_contables_siscont AS cuentas
  ON(CP.c_cash_mpayment_id=cuentas.nu_mediopago::NUMERIC AND nu_tipooperacion='04' AND CP.c_bank_id=cuentas.id_bank)
 JOIN c_cash_mpayment AS CM
  USING(c_cash_mpayment_id)
 JOIN int_tabla_general AS TDOCU
  ON(SUBSTRING(TDOCU.tab_elemento, 5)=CD.doc_type AND TDOCU.tab_tabla='08' AND TDOCU.tab_elemento!='000000')
WHERE
 CC.ware_house='".$arrParams["sCodeWarehouse"]."'
 AND EXTRACT(YEAR FROM CC.d_system)='".$arrParams["dYear"]."'
 AND EXTRACT(MONTH FROM CC.d_system)='".$arrParams["dMonth"]."'
 )
ORDER BY
 nucorrelativo,
 orden,
 tipo,
 serie,
 numero;
 		";
    $iStatusSQL = $sqlca->query($sql);
    $arrResponse = array(
      'status_sql' => $iStatusSQL,
      'message_sql' => $sqlca->get_error(),
      'sStatus' => 'danger',
      'sMessage' => 'problemas al obtener ventas manuales (cobranza)',
    );
    if ( $iStatusSQL == 0 ) {
      $arrResponse = array(
        'sStatus' => 'warning',
        'sMessage' => 'No hay registros'
      );
    } else if ( (int)$iStatusSQL > 0 ) {
      $arrDataSQL = $sqlca->fetchAll();
      $arrResponse = array(
        'sStatus' => 'success',
        'arrData' => $arrDataSQL
      );
    }
    return $arrResponse;
  }

	function CuentasContables($tipooperacion){
        	global $sqlca;

		if($tipooperacion == 1)
			$tipooperacion = '02';
		else
			$tipooperacion = '04';

		$sql = "
			SELECT
				nu_tipooperacion,
				nu_cuentacontable,
				no_flujoefectivo,
				nu_mediopago,
				no_tipolibro,
				nu_tiposiscont
			FROM
				cuentas_contables_siscont
			WHERE
				nu_tipooperacion = '$tipooperacion'
			ORDER BY
				nu_id,
				nu_tipooperacion,
				nu_cuentacontable;
		";

		if($sqlca->query($sql) < 0)
			return false;
		
		$data = array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    $a = $sqlca->fetchRow();
			$data[$i]['nu_tipooperacion'] 	= $a['nu_tipooperacion'];			
			$data[$i]['nu_cuentacontable'] 	= $a['nu_cuentacontable'];
			$data[$i]['no_flujoefectivo'] 	= $a['no_flujoefectivo'];
			$data[$i]['nu_mediopago'] 		= $a['nu_mediopago'];
			$data[$i]['no_tipolibro'] 		= $a['no_tipolibro'];
			$data[$i]['nu_tiposiscont'] 	= $a['nu_tiposiscont'];
		}
		return $data;
	}

    function AsientosCobranzasExtornos($almacen, $postrans, $fecha_serie, $tipo, $decimales, $iTipoVenta, $iConNotaDespacho) {
        global $sqlca;

		$where_td = "('B','F')";
	    if ($iConNotaDespacho == "2") //Si
			$where_td = "('B','F','N')";

        $sql = "
SELECT
 T.trans::TEXT as trans,
 T.caja::TEXT as caja,
 FIRST(T.dia::DATE) as emision,
 '07'::TEXT AS tipo,
 (CASE WHEN T.usr != '' THEN SUBSTR(TRIM(T.usr), 0, 5) ELSE FIRST(cfp.nu_posz_z_serie) END) as serie,
 (CASE WHEN T.usr != '' THEN SUBSTR(TRIM(T.usr), 6) ELSE TO_CHAR(T.trans,'FM999999999999') END) as numero,
 (CASE WHEN FIRST(T.td) IN('B','N') THEN '0' ELSE '6' END) as tipodi,
 (CASE
   WHEN FIRST(T.td)='B' AND FIRST(T.tm) IN('D','A') AND T.usr = '' THEN '00000000004'
   WHEN FIRST(T.td)='B' AND FIRST(T.tm) IN('D','A') AND T.usr != '' THEN '00000000009'
  ELSE FIRST(T.ruc)
 END) AS ruc,
 (CASE
   WHEN FIRST(T.td)='B' AND FIRST(T.tm) IN('D','A') AND T.usr = '' THEN 'Consolidado ticket boleta'
   WHEN FIRST(T.td)='B' AND FIRST(T.tm) IN('D','A') AND T.usr != '' THEN 'Consolidado de nota de credito bve'
  ELSE SUBSTR(FIRST(R.razsocial),0,60)
 END) as cliente,
 (CASE WHEN SUBSTRING(FIRST(T.ruc), 1, 2) = '10' THEN CASE WHEN FIRST(T.td) = 'B' THEN '' WHEN FIRST(T.td) = 'F' AND SUBSTR(FIRST(T.ruc),1,1) = '1' THEN split_part(FIRST(R.razsocial), ' ', 1) END END) as no_apellido_paterno,
 (CASE WHEN SUBSTRING(FIRST(T.ruc), 1, 2) = '10' THEN CASE WHEN FIRST(T.td) = 'B' THEN '' WHEN FIRST(T.td) = 'F' AND SUBSTR(FIRST(T.ruc),1,1) = '1' THEN split_part(FIRST(R.razsocial), ' ', 2) END END) as no_apellido_materno,
 (CASE WHEN SUBSTRING(FIRST(T.ruc), 1, 2) = '10' THEN CASE WHEN FIRST(T.td) = 'B' THEN '' WHEN FIRST(T.td) = 'F' AND SUBSTR(FIRST(T.ruc),1,1) = '1' THEN split_part(FIRST(R.razsocial), ' ', 3) || ' ' || split_part(FIRST(R.razsocial), ' ', 4) END END) as no_nombre,
 (CASE WHEN FIRST(T.td) = 'N' THEN '0' ELSE ROUND(SUM(T.importe), ".$decimales.") END) AS importe,
 FIRST(TC.tca_venta_oficial) as tipocambio,
 'A' as tipo_pdf,
 12121::INTEGER AS cuentacontable,
 ''::TEXT AS no_flujoefectivo,
 ''::TEXT AS nu_mediopago,
 TO_CHAR(FIRST(T.dia::DATE),'DD') AS nucorrelativo,
 '4'::TEXT AS orden,
 FIRST(T.turno)::TEXT as turno
FROM
 ".$postrans." AS T
 LEFT JOIN pos_z_cierres AS cfp ON(T.caja = cfp.ch_posz_pos AND T.dia = cfp.dt_posz_fecha_sistema::date AND T.turno::integer = cfp.nu_posturno AND T.es = cfp.ch_sucursal)
 LEFT JOIN int_tipo_cambio AS TC ON (TC.tca_fecha = T.dia)
 LEFT JOIN ruc AS R ON (R.ruc = T.ruc)
WHERE
 T.td IN".$where_td."
 AND T.es = '".$almacen."'
 AND T.tm IN('D','A')
GROUP BY
 T.trans,
 T.caja,
 T.usr,
 T.td;
 		";
    $iStatusSQL = $sqlca->query($sql);
    $arrResponse = array(
      'status_sql' => $iStatusSQL,
      'message_sql' => $sqlca->get_error(),
      'sStatus' => 'danger',
      'sMessage' => 'problemas al obtener ventas de playa extornos (cobranza)',
    );
    if ( $iStatusSQL == 0 ) {
      $arrResponse = array(
        'sStatus' => 'warning',
        'sMessage' => 'No hay registros'
      );
    } else if ( $iStatusSQL > 0 ) {
      $arrDataSQL = $sqlca->fetchAll();
      $arrResponse = array(
        'sStatus' => 'success',
        'arrData' => $arrDataSQL
      );
    }
    return $arrResponse;
  }

	function AsientosCobranzas($almacen, $postrans, $fecha_serie, $tipo, $decimales, $iTipoVenta, $iConNotaDespacho, $year, $month) {
        global $sqlca;

        $sCodigoClienteBoleta = '00000000004';
        if ($iTipoVenta == '1')
		    $sCodigoClienteBoleta = '00000000008';

		$where_td = "('B','F')";
	    if ($iConNotaDespacho == "2") //Si
		    $where_td = "('B','F','N')";

		$result 				= Array();
		$correlativo 			= 0;
		$key_array 				= "";
		$array_series 			= array();
		$correlativo_serie 		= 0;
		$aplicar_incremento 	= FALSE;

		//--VERIFICAR EXTORNOS

		$sql_aferciones = "
		SELECT
			LAST(venta_tickes.tickes_refe),
			venta_tickes.registro,
			venta_tickes.trans_ext
		FROM
			(SELECT 
				(p.trans||'-'||p.caja) as tickes_refe,
				p.trans,
				extorno.trans as trans_ext,
				extorno.registro,
				extorno.trans1,p.fecha
			FROM
				" . $postrans . " AS p
				INNER JOIN (
				SELECT 
					(dia|| caja || td ||turno ||codigo ||tipo || pump || fpago ||  abs(cantidad) ||abs(precio)|| abs(igv) || abs(importe) ||ruc) as registro,
					fecha,
					trans||'-'||caja as trans,
					trans as trans1
				FROM
					$postrans
				WHERE
					tm = 'A'
					AND td IN ('B','F')
				) as extorno ON (p.dia|| p.caja || p.td ||p.turno ||p.codigo ||p.tipo || p.pump || p.fpago ||  abs(p.cantidad) ||abs(p.precio)|| abs(p.igv) || abs(p.importe) ||ruc) = extorno.registro
				AND td IN ('B','F')
				AND tm = 'V'
				AND p.trans < extorno.trans1
			ORDER BY
				p.fecha asc
			) AS venta_tickes
		GROUP BY
			venta_tickes.registro,
			venta_tickes.trans_ext;
		";

		if ($sqlca->query($sql_aferciones) < 0)
        		return false;

		$num_afe = 0;
		$array_aferciones_cod = array();

		for (; $num_afe < $sqlca->numrows();){
		    $a_act_afericion		= $sqlca->fetchRow();
		    $array_aferciones_cod[] = $a_act_afericion[0];
		    $array_aferciones_cod[] = $a_act_afericion[2];
		    $num_afe++;
		}

        $sql_series = "
		SELECT
         	trim(dt_posz_fecha_sistema::TEXT) as dt_posz_fecha_sistema,
         	trim(ch_posz_pos::TEXT) as ch_posz_pos,
         	trim(nu_posz_z_serie::TEXT) as nu_posz_z_serie,
         	trim(nu_posturno::TEXT) as nu_posturno
		FROM
			pos_z_cierres 
		WHERE
			to_char(dt_posz_fecha_sistema,'YYYY-MM') = '" . $fecha_serie . "'
			AND ch_sucursal = '" . $almacen . "'
		GROUP BY
			dt_posz_fecha_sistema,
			ch_posz_pos,
			nu_posz_z_serie,
			nu_posturno
		ORDER BY
			dt_posz_fecha_sistema,
			ch_posz_pos,
			nu_posz_z_serie,
			nu_posturno;
		";

    	if ($sqlca->query($sql_series) < 0)
			return false;

    	for (; $correlativo_serie < $sqlca->numrows();) {
	    	$a_act = $sqlca->fetchRow();
	    	$array_series[$a_act['dt_posz_fecha_sistema']][$a_act['ch_posz_pos']][$a_act['nu_posturno']] = $a_act['nu_posz_z_serie'];
	    	$correlativo_serie++;
    	}

		$key_array = "ticket_tmp";

		//TICKES FACTURAS

		$sql_tickes_factura = "
SELECT * FROM(
 SELECT
  ''::TEXT AS trans,
  ''::TEXT AS caja,
  p.dia::DATE as emision,
  '12'::TEXT as tipo,
  ''::TEXT AS serie,
  ''::TEXT AS numero,
  ''::TEXT AS tipodi,
  ''::TEXT AS ruc,
  ''::TEXT AS cliente,
  ''::TEXT AS no_apellido_paterno,
  ''::TEXT AS no_apellido_materno,
  ''::TEXT AS no_nombre,
  ROUND(SUM(p.importe), ".$decimales.") + COALESCE(FIRST(VM.importe), 0) AS importe,
  FIRST(TC.tca_venta_oficial) AS tipocambio,
  '12'::TEXT AS tipo_pdf,
  nu_cuentacontable AS cuentacontable,
  no_flujoefectivo,
  nu_mediopago,
  TO_CHAR(p.dia::DATE,'DD') AS nucorrelativo,
  '1'::TEXT AS orden,
  ''::TEXT AS turno
 FROM
  ".$postrans." AS p
  LEFT JOIN int_tipo_cambio TC ON (TC.tca_fecha = p.dia)
  LEFT JOIN cuentas_contables_siscont AS cuentas ON (p.fpago = cuentas.nu_fpago AND nu_tipooperacion = '04')
  LEFT JOIN (
   SELECT
    VC.dt_fac_fecha::DATE AS emision,
    ROUND(SUM(VC.nu_fac_valortotal), ".$decimales.") AS importe
   FROM
    fac_ta_factura_cabecera AS VC
    LEFT JOIN int_tipo_cambio AS TC ON (VC.dt_fac_fecha = TC.tca_fecha)
    LEFT JOIN cuentas_contables_siscont AS cuentas ON (VC.nu_tipo_pago = '0'||cuentas.nu_fpago AND nu_tipooperacion = '04')
   WHERE
    VC.ch_almacen='".$almacen."'
    AND EXTRACT(YEAR FROM VC.dt_fac_fecha)='".$year."'
    AND EXTRACT(MONTH FROM VC.dt_fac_fecha)='".$month."'
    AND VC.nu_tipo_pago='01'
    AND (SUBSTRING(VC.ch_fac_seriedocumento FROM '[A-Z]+') IS NULL OR SUBSTRING(VC.ch_fac_seriedocumento FROM '[A-Z]+') ='')
   GROUP BY
    VC.ch_almacen,
    VC.dt_fac_fecha
   ) AS VM ON (VM.emision = p.dia::DATE)
  WHERE
   p.td IN('B','F')
   AND p.es='".$almacen."'
   AND p.fpago='1'
   AND p.tm='V'
   AND p.usr=''
   AND p.es||p.caja||p.trans NOT IN (
    SELECT
     LAST(venta.es||venta.caja||venta.trans) co_vtrans
    FROM
     ".$postrans." AS venta
     INNER JOIN (
      SELECT 
       (caja||td||dia||turno||codigo||abs(cantidad)||abs(precio)||abs(igv)||abs(importe)||ruc||tipo||pump||fpago||at||text1||placa||es) AS registro,
       fecha,
       es||caja||trans AS idticket
      FROM
       ".$postrans."
      WHERE
       tm='A'
       AND es='".$almacen."'
       AND fpago='1'
       AND usr=''
       AND td IN('B','F')
      ) AS extorno ON (venta.caja||venta.td||venta.dia||venta.turno||venta.codigo||abs(venta.cantidad)||abs(venta.precio)||abs(venta.igv)||abs(venta.importe)||venta.ruc||venta.tipo||venta.pump||venta.fpago||venta.at||venta.text1||venta.placa||venta.es) = extorno.registro
      AND venta.tm='V'
      AND venta.es='".$almacen."'
      AND venta.fpago='1'
      AND venta.usr=''
      AND venta.td IN('B','F')
      AND venta.fecha < extorno.fecha
    GROUP BY
     extorno.idticket
   )
 GROUP BY
  es,
  dia,
  nu_cuentacontable,
  no_flujoefectivo,
  nu_mediopago
 ) AS TKAE--ASIENTO EFECTIVO PLAYA + OFICINA NO ELECTRONICOS
 UNION ALL
 (
 SELECT
  ''::TEXT AS trans,
  ''::TEXT AS caja,
  p.dia::DATE AS emision,
  (CASE
   WHEN FIRST(p.td)='B' AND FIRST(p.tm)='V' THEN '03'
   WHEN FIRST(p.td)='F' AND FIRST(p.tm)='V' THEN '01'
   WHEN FIRST(p.td) IN('B','F') AND FIRST(p.tm) IN('D','A') THEN '07'
  END)::TEXT AS tipo,
  ''::TEXT AS serie,
  ''::TEXT AS numero,
  ''::TEXT AS tipodi,
  ''::TEXT AS ruc,
  ''::TEXT AS cliente,
  ''::TEXT AS no_apellido_paterno,
  ''::TEXT AS no_apellido_materno,
  ''::TEXT AS no_nombre,
  ROUND(SUM(p.importe), ".$decimales.") + COALESCE(FIRST(VM.importe), 0) AS importe,
  FIRST(TC.tca_venta_oficial) AS tipocambio,
  FIRST(p.td) AS tipo_pdf,
  nu_cuentacontable AS cuentacontable,
  no_flujoefectivo,
  nu_mediopago,
  TO_CHAR(p.dia::DATE,'DD') AS nucorrelativo,
  '1'::TEXT AS orden,
  ''::TEXT AS turno
 FROM
  ".$postrans." AS p
  LEFT JOIN int_tipo_cambio TC ON (TC.tca_fecha = p.dia)
  LEFT JOIN cuentas_contables_siscont AS cuentas ON (p.fpago = cuentas.nu_fpago AND nu_tipooperacion = '04')
  LEFT JOIN (
   SELECT
    VC.dt_fac_fecha::DATE AS emision,
    ROUND(SUM(VC.nu_fac_valortotal), ".$decimales.") AS importe
   FROM
    fac_ta_factura_cabecera AS VC
    LEFT JOIN int_tipo_cambio AS TC ON (VC.dt_fac_fecha = TC.tca_fecha)
    LEFT JOIN cuentas_contables_siscont AS cuentas ON (VC.nu_tipo_pago = '0'||cuentas.nu_fpago AND nu_tipooperacion = '04')
   WHERE
    VC.ch_almacen='".$almacen."'
    AND EXTRACT(YEAR FROM VC.dt_fac_fecha)='".$year."'
    AND EXTRACT(MONTH FROM VC.dt_fac_fecha)='".$month."'
    AND VC.nu_tipo_pago='01'
    AND SUBSTRING(VC.ch_fac_seriedocumento FROM '[A-Z]+') IN('B','F')
   GROUP BY
    VC.ch_almacen,
    VC.dt_fac_fecha
  ) AS VM ON (VM.emision = p.dia::DATE)
 WHERE
  p.td IN('B','F')
  AND p.es='".$almacen."'
  AND p.fpago='1'
  AND p.tm='V'
  AND p.usr!=''
  AND p.es||p.caja||p.trans NOT IN (
   SELECT
    LAST(venta.es||venta.caja||venta.trans) co_vtrans
   FROM
    ".$postrans." AS venta
    INNER JOIN (
     SELECT 
      (caja||td||dia||turno||codigo||abs(cantidad)||abs(precio)||abs(igv)||abs(importe)||ruc||tipo||pump||fpago||at||text1||placa||es) AS registro,
      fecha,
      es||caja||trans AS idticket
     FROM
      ".$postrans."
     WHERE
      tm='A'
      AND es='".$almacen."'
      AND fpago='1'
      AND td IN('B','F')
      AND usr!=''
     ) AS extorno ON (venta.caja||venta.td||venta.dia||venta.turno||venta.codigo||abs(venta.cantidad)||abs(venta.precio)||abs(venta.igv)||abs(venta.importe)||venta.ruc||venta.tipo||venta.pump||venta.fpago||venta.at||venta.text1||venta.placa||venta.es) = extorno.registro
     AND venta.tm='V'
     AND venta.es='".$almacen."'
     AND venta.fpago='1'
     AND venta.usr!=''
     AND venta.td IN('B','F')
     AND venta.fecha < extorno.fecha
   GROUP BY
    extorno.idticket
  )
 GROUP BY
  es,
  dia,
  fpago,
  nu_fpago,
  nu_cuentacontable,
  no_flujoefectivo,
  nu_mediopago
 )--ASIENTO EFECTIVO PLAYA + OFICINA DOCUMENTOS ELECTRONICOS
 UNION ALL
 (
 SELECT
  p.trans::TEXT AS trans,
  ''::TEXT AS caja,
  p.dia::DATE AS emision,
  (CASE
   WHEN p.td='B' AND FIRST(p.tm)='V' AND p.usr!='' THEN '03'
   WHEN p.td='F' AND FIRST(p.tm)='V' AND p.usr!='' THEN '01'
   WHEN p.td IN('B','F') AND FIRST(p.tm) IN ('D','A') AND p.usr!='' THEN '07'
  ELSE
   '12'
  END)::TEXT AS tipo,
  (CASE WHEN p.usr!='' THEN p.usr ELSE cfp.nu_posz_z_serie||'-'||p.trans END) AS serie,
  ''::TEXT AS numero,
  (CASE WHEN p.td='B' THEN '0' ELSE '6' END) as tipodi,
  (CASE
   WHEN p.td='B' AND p.usr='' THEN '00000000004'
   WHEN p.td='B' AND FIRST(p.tm) IN('V') AND p.usr!='' THEN FIRST(cuentas.id_cliente_boleta)
   WHEN p.td='B' AND FIRST(p.tm) IN('A','D') AND p.usr!='' THEN '00000000009'
   WHEN p.td='F' THEN FIRST(p.ruc)
  END) AS ruc,
  (CASE
    WHEN p.td='B' AND p.usr='' THEN 'Consolidado ticket boleta'
	WHEN p.td='B' AND FIRST(p.tm) IN('V') AND p.usr!='' THEN FIRST(cuentas.no_rsocial_cliente_boleta)
	WHEN p.td='B' AND FIRST(p.tm) IN('A','D') AND p.usr!='' THEN 'Consolidado de nota de credito bve'
   ELSE
    SUBSTR(FIRST(R.razsocial),0,60)
   END) AS cliente,
   (CASE WHEN SUBSTRING(FIRST(p.ruc), 1, 2) = '10' THEN
    CASE
     WHEN FIRST(p.td)='B' THEN ''
	   WHEN FIRST(p.td)='F' AND SUBSTR(FIRST(p.ruc),1,1) = '1' THEN split_part(FIRST(R.razsocial), ' ', 1)
    END
   END) AS no_apellido_paterno,
   (CASE WHEN SUBSTRING(FIRST(p.ruc), 1, 2) = '10' THEN
    CASE
	   WHEN FIRST(p.td)='B' THEN ''
	   WHEN FIRST(p.td)='F' AND SUBSTR(FIRST(p.ruc),1,1) = '1' THEN split_part(FIRST(R.razsocial), ' ', 2)
    END
   END) AS no_apellido_materno,
   (CASE WHEN SUBSTRING(FIRST(p.ruc), 1, 2) = '10' THEN
    CASE
	   WHEN FIRST(p.td)='B' THEN ''
	   WHEN FIRST(p.td)='F' AND SUBSTR(FIRST(p.ruc),1,1) = '1' THEN split_part(FIRST(R.razsocial), ' ', 3) || ' ' || split_part(FIRST(R.razsocial), ' ', 4)
    END
   END) AS no_nombre,
   ROUND(SUM(p.importe), ".$decimales.") AS importe,
   FIRST(TC.tca_venta_oficial) AS tipocambio,
   FIRST(p.td)::TEXT as tipo_pdf,
   nu_cuentacontable cuentacontable,
   ''::TEXT AS no_flujoefectivo,
   ''::TEXT AS nu_mediopago,
   TO_CHAR(p.dia::DATE,'DD') AS nucorrelativo,
   '2'::TEXT AS orden,
   ''::TEXT AS turno
  FROM
   ".$postrans." AS p
   LEFT JOIN pos_z_cierres AS cfp ON(p.caja = cfp.ch_posz_pos AND p.dia = cfp.dt_posz_fecha_sistema::date AND p.turno::integer = cfp.nu_posturno AND p.es = cfp.ch_sucursal)
   LEFT JOIN int_tipo_cambio AS TC ON (TC.tca_fecha = p.dia)
   LEFT JOIN cuentas_contables_siscont AS cuentas ON (p.fpago = cuentas.nu_fpago AND nu_tipooperacion = '04')
   LEFT JOIN ruc AS R ON (R.ruc = p.ruc)
  WHERE
   p.td IN ('B','F')
   AND p.es='".$almacen."'
   AND p.fpago='2'
   AND p.tm='V'
   AND p.es||p.caja||p.trans NOT IN (
   SELECT
	LAST(venta.es||venta.caja||venta.trans) co_vtrans
   FROM
	".$postrans." AS venta
    INNER JOIN(
	SELECT
     (caja||td||dia||turno||codigo||abs(cantidad)||abs(precio)||abs(igv)||abs(importe)||ruc||tipo||pump||fpago||at||text1||placa||es) AS registro,
	 fecha,
	 es||caja||trans AS idticket
	FROM
	 ".$postrans."
    WHERE
	 tm='A'
	 AND es='".$almacen."'
	 AND fpago='2'
	 AND td IN('B','F')
	) AS extorno ON (venta.caja||venta.td||venta.dia||venta.turno||venta.codigo||abs(venta.cantidad)||abs(venta.precio)||abs(venta.igv)||abs(venta.importe)||venta.ruc||venta.tipo||venta.pump||venta.fpago||venta.at||venta.text1||venta.placa||venta.es) = extorno.registro
	AND venta.tm='V'
	AND venta.es='".$almacen."'
	AND venta.fpago='2'
	AND venta.td IN('B','F')
	AND venta.fecha < extorno.fecha
   GROUP BY
    extorno.idticket
   )
  GROUP BY
   es,
   dia,
   fpago,
   nu_fpago,
   nu_cuentacontable,
   trans,
   cfp.nu_posz_z_serie,
   p.td,
   p.usr
  )
  UNION ALL
  (
  SELECT
   T.trans::TEXT AS trans,
   T.caja::TEXT AS caja,
   FIRST(T.dia::DATE) AS emision,
   (CASE
    WHEN T.td='B' AND FIRST(T.tm)='V' AND T.usr!='' THEN '03'
    WHEN T.td='F' AND FIRST(T.tm)='V' AND T.usr!='' THEN '01'
   ELSE
    '12'
   END)::TEXT AS tipo,
   (CASE WHEN T.usr!='' THEN SUBSTR(TRIM(T.usr), 0, 5) ELSE FIRST(cfp.nu_posz_z_serie) END) AS serie,
   (CASE WHEN T.usr!='' THEN SUBSTR(TRIM(T.usr), 6) ELSE TO_CHAR(T.trans,'FM999999999999') END) AS numero,
   (CASE WHEN FIRST(T.td) IN('B','N') THEN '0' ELSE '6' END) AS tipodi,
   (CASE
	 WHEN FIRST(T.td)='B' AND T.usr='' THEN '00000000004'
	 WHEN FIRST(T.td)='B' AND FIRST(T.tm)='V' AND T.usr!='' THEN FIRST(cuentas.id_cliente_boleta)
	ELSE
	 FIRST(T.ruc)
   END) AS ruc,
   (CASE
	 WHEN FIRST(T.td)='B' AND T.usr='' THEN 'Consolidado ticket boleta'
	 WHEN FIRST(T.td)='B' AND FIRST(T.tm)='V' AND T.usr!='' THEN FIRST(cuentas.no_rsocial_cliente_boleta)
	ELSE
	 SUBSTR(FIRST(R.razsocial),0,60)
   END) AS cliente,
   (CASE WHEN SUBSTRING(FIRST(T.ruc), 1, 2) = '10' THEN
    CASE
	   WHEN FIRST(T.td)='B' THEN ''
	   WHEN FIRST(T.td)='F' AND SUBSTR(FIRST(T.ruc),1,1) = '1' THEN split_part(FIRST(R.razsocial), ' ', 1)
    END
   END) AS no_apellido_paterno,
   (CASE WHEN SUBSTRING(FIRST(T.ruc), 1, 2) = '10' THEN
    CASE
     WHEN FIRST(T.td)='B' THEN ''
	   WHEN FIRST(T.td)='F' AND SUBSTR(FIRST(T.ruc),1,1) = '1' THEN split_part(FIRST(R.razsocial), ' ', 2)
    END
   END) AS no_apellido_materno,
   (CASE WHEN SUBSTRING(FIRST(T.ruc), 1, 2) = '10' THEN
    CASE
	   WHEN FIRST(T.td)='B' THEN ''
	   WHEN FIRST(T.td)='F' AND SUBSTR(FIRST(T.ruc),1,1) = '1' THEN split_part(FIRST(R.razsocial), ' ', 3) || ' ' || split_part(FIRST(R.razsocial), ' ', 4)
    END
   END) AS no_nombre,
   (CASE WHEN FIRST(T.td)='N' THEN '0' ELSE ROUND(SUM(T.importe), ".$decimales.") END) AS importe,
   FIRST(TC.tca_venta_oficial) AS tipocambio,
   FIRST(T.td) AS tipo_pdf,
   12121::INTEGER AS cuentacontable,
   ''::TEXT AS no_flujoefectivo,
   ''::TEXT AS nu_mediopago,
   TO_CHAR(FIRST(T.dia::DATE),'DD') AS nucorrelativo,
   '3'::TEXT AS orden,
   FIRST(T.turno)::TEXT AS turno
  FROM
   ".$postrans." AS T
   LEFT JOIN pos_z_cierres AS cfp ON(T.caja = cfp.ch_posz_pos AND T.dia = cfp.dt_posz_fecha_sistema::date AND T.turno::integer = cfp.nu_posturno AND T.es = cfp.ch_sucursal)
   LEFT JOIN int_tipo_cambio AS TC ON (TC.tca_fecha = T.dia)
   LEFT JOIN ruc AS R ON (R.ruc = T.ruc)
	 LEFT JOIN cuentas_contables_siscont AS cuentas
	  ON(T.fpago = cuentas.nu_fpago AND nu_tipooperacion = '04')
  WHERE
   T.td IN".$where_td."
   AND T.es='".$almacen."'
   AND T.tm='V'
  GROUP BY
   T.trans,
   T.caja,
   T.usr,
   T.td
  )
ORDER BY
 nucorrelativo,
 orden,
 caja,
 trans;
		";

		if ($sqlca->query($sql_tickes_factura) < 0)
			return false;

		$importe = 0;

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
	    	$a = $sqlca->fetchRow();

			$trans_caja = trim($a['trans']."-".$a['caja']);
    		$result[$key_array][$correlativo]['trans'] = $a['trans'];
    		$result[$key_array][$correlativo]['caja'] = $a['caja'];
    		$result[$key_array][$correlativo]['emision'] = $a['emision'];
    		$result[$key_array][$correlativo]['tipo'] = $a['tipo'];

    		if($a['cuentacontable'] == "162911" || $a['cuentacontable'] == "1011") {
        		$result[$key_array][$correlativo]['serie'] = $a['serie'];
      		} else {
    			if ( $a['tipo'] != "12" ) {
    		  		$result[$key_array][$correlativo]['serie'] = $a['serie'];
    			} else {
    				$result[$key_array][$correlativo]['serie'] = $array_series[$a['emision']][$a['caja']][$a['turno']];
        		}
    		}

    		$result[$key_array][$correlativo]['numero'] = $a['numero'];
    		$result[$key_array][$correlativo]['tipodi'] = $a['tipodi'];
    		$result[$key_array][$correlativo]['ruc'] = $a['ruc'];
    		$result[$key_array][$correlativo]['cliente'] = $a['cliente'];
    		$result[$key_array][$correlativo]['no_apellido_paterno'] = $a['no_apellido_paterno'];
    		$result[$key_array][$correlativo]['no_apellido_materno'] = $a['no_apellido_materno'];
    		$result[$key_array][$correlativo]['no_nombre'] = $a['no_nombre'];

			$importe = $a['importe'];

			if ( $a['tipo'] == "12" ) {
				if (in_array($trans_caja, $array_aferciones_cod)) {//PONEMOS LOS MONTOS EN CEROS PARA LOS TICKES DE EXTORNOS
      				$importe=0; //Evaluar esta condición, cuando descomento, no muestra los comprobante electrónicos originales de la anulación
				}
			}

	    	$result[$key_array][$correlativo]['importe'] = $importe;
    		$result[$key_array][$correlativo]['tipocambio'] = $a['tipocambio'];
    		$result[$key_array][$correlativo]['tipo_pdf'] = $a['tipo_pdf'];
			$result[$key_array][$correlativo]['cuentacontable'] = $a['cuentacontable'];
			$result[$key_array][$correlativo]['no_flujoefectivo'] = $a['no_flujoefectivo'];
			$result[$key_array][$correlativo]['nu_mediopago'] = $a['nu_mediopago'];
			$result[$key_array][$correlativo]['nucorrelativo'] = $a['nucorrelativo'];

			$correlativo++;
		}

		$correlativo = 0;
		$inicio_boleta = "0";
		$fin_boleta = "0";
		$fecha_agrupar = "";
		$serie_agrupar = "";
		$tipo_documento_venta	= "";
		$array_tmp_impresion = array();
		$imprimir = 0;
		$cantidad_factura = 0;

		for ($i = 0; $i < count($result['ticket_tmp']); $i++) {
	   		$a = $result['ticket_tmp'][$i];

	    	if ( $i == 0 ) {
		      	$tipo_documento_venta = trim($a['tipo_pdf']);
		      	$fecha_agrupar = $a['emision'];
		      	$serie_agrupar = $a['serie'];
		      	$inicio_boleta = $a['numero'];
	    	}

	    	if ((strcmp($tipo_documento_venta, $a['tipo_pdf']) == 0 && strcmp($a['emision'], $fecha_agrupar) == 0 && strcmp($a['serie'], $serie_agrupar) == 0) && $cantidad_factura == 0) {
				$fin_boleta = $a['numero'];
				if ( $a['tipo_pdf'] == "F" || $a['tipo_pdf'] == "N" ) {
	          		$cantidad_factura = 1;
				}
	    	} else {
	    		$result['ticket'][$correlativo] = $array_tmp_impresion;

		    	$result['ticket'][$correlativo]['trans'] = $inicio_boleta; //para que sea unico numero transacion
		    	$result['ticket'][$correlativo]['numero'] = $inicio_boleta."-".$fin_boleta;
		    	$result['ticket'][$correlativo]['serie'] = $serie_agrupar;
		    	$result['ticket'][$correlativo]['emision'] = $fecha_agrupar;
		    	$result['ticket'][$correlativo]['importe'] = $array_tmp_impresion['importe'];

		    	$inicio_boleta = $a['numero'];
		    	$i--;
		    	$correlativo++;
		    	$cantidad_factura = 0;
		    	$array_tmp_impresion = array(); //volvemos el array_tmp a vacio
		    	$imprimir = 1;
			}

	  		$tipo_documento_venta = trim($a['tipo_pdf']);
	  		$fecha_agrupar = $a['emision'];
	  		$serie_agrupar = $a['serie'];

	    	if ($imprimir == 0) {
        		$array_tmp_impresion = Siscont_Model::llenar_arreglo_objecto_imprimir_Cobranza($array_tmp_impresion, $a);
      		}
      		$imprimir = 0;
		}// /. For de union de boletas

	  	//PARA LA ULTIMA IMPRESION 
	  	$result['ticket'][$correlativo] = $array_tmp_impresion;
	  	$result['ticket'][$correlativo]['trans'] = $inicio_boleta; //para que sea unico numero transacion
	  	$result['ticket'][$correlativo]['numero'] = $inicio_boleta."-".$fin_boleta;
	  	$result['ticket'][$correlativo]['serie'] = $serie_agrupar;
	  	$result['ticket'][$correlativo]['importe'] = $array_tmp_impresion['importe'];
		return $result;
	}

	function llenar_arreglo_objecto_imprimir_Cobranza($array_tmp_impresion, $a) {
		$array_tmp_impresion['caja'] = $a['caja'];
		$array_tmp_impresion['emision'] = $a['emision'];
		$array_tmp_impresion['tipo'] = $a['tipo'];
		$array_tmp_impresion['serie'] = $a['serie'];
		$array_tmp_impresion['numero'] = $a['numero'];
		$array_tmp_impresion['tipodi'] = $a['tipodi'];
		$array_tmp_impresion['ruc'] = $a['ruc'];
		$array_tmp_impresion['cliente'] = $a['cliente'];
	  	$array_tmp_impresion['no_apellido_paterno']	= $a['no_apellido_paterno'];
	  	$array_tmp_impresion['no_apellido_materno'] = $a['no_apellido_materno'];
	  	$array_tmp_impresion['no_nombre']	= $a['no_nombre'];
		$array_tmp_impresion['tipocambio'] = $a['tipocambio'];
		$array_tmp_impresion['tipo_pdf'] = $a['tipo_pdf'];
		$array_tmp_impresion['cuentacontable'] = $a['cuentacontable'];
		$array_tmp_impresion['no_flujoefectivo'] = $a['no_flujoefectivo'];
		$array_tmp_impresion['nu_mediopago'] = $a['nu_mediopago'];
		$array_tmp_impresion['nucorrelativo'] = $a['nucorrelativo'];
		$array_tmp_impresion['importe']	+= $a['importe'];
		return $array_tmp_impresion;
  	}

	function AsientosVentas($almacen, $postrans, $fecha_serie, $tipo, $decimales, $iTipoVenta, $iConNotaDespacho) {
        global $sqlca;

        /*
		Tipos de venta:
		"1" > Documentos Electrónicos
	    "2" > Documentos Electrónicos y Tickets
	    "3" > Tickets y Documentos Manuales
        */
        $sCodigoClienteBoleta = '00000000004';
	    if ($iTipoVenta == '1')
	    	$sCodigoClienteBoleta = '00000000008';

       	$result 					= Array();
		$correlativo 				= 0;
		$tipo_documento_tickes 		= array("'F'");
		if ($iConNotaDespacho == "2")//Si
			$tipo_documento_tickes 		= array("'F','N'");
		$key_array 					= "";
		$array_series 				= array();
		$correlativo_serie 			= 0;
		$aplicar_incremento 		= FALSE;

		//CONSULTA PARA LAS AFERRICONES
    	$sql_aferciones = "
		SELECT
			LAST(venta_tickes.tickes_refe),
			venta_tickes.registro,
			venta_tickes.trans_ext
		FROM
			(SELECT 
				(p.trans||'-'||p.caja) as tickes_refe,
				p.trans,
				extorno.trans as trans_ext,
				extorno.registro,
				extorno.trans1,p.fecha
			FROM
				" . $postrans . " AS p
				INNER JOIN (
				SELECT
					(dia || caja || td || turno || codigo || tipo || pump || fpago || abs(cantidad) || abs(precio) || abs(igv) || abs(importe) || ruc) as registro,
					fecha,
					trans||'-'||caja as trans,
					trans as trans1
				FROM
					" . $postrans . "
				WHERE
					tm = 'A'
					AND td IN ('B','F')
				) as extorno ON (p.dia || p.caja || p.td || p.turno || p.codigo || p.tipo || p.pump || p.fpago || abs(p.cantidad) || abs(p.precio)|| abs(p.igv) || abs(p.importe) || ruc) = extorno.registro
				AND td IN ('B','F')
				AND tm = 'V'
				AND p.trans < extorno.trans1
			ORDER BY
				p.fecha asc
			) AS venta_tickes
		GROUP BY
			venta_tickes.registro,
			venta_tickes.trans_ext;
		";
		//echo $sql_aferciones;

		if ($sqlca->query($sql_aferciones) < 0)
        	return false;

		$num_afe = 0;
		$array_aferciones_cod = array();

		for (; $num_afe < $sqlca->numrows();){
		    $a_act_afericion    	= $sqlca->fetchRow();
		    $array_aferciones_cod[] = $a_act_afericion[0];
		    $array_aferciones_cod[] = $a_act_afericion[2];
		    $num_afe++;
		}

    	$sql_series = "
   		SELECT 
         	trim(dt_posz_fecha_sistema::TEXT) AS dt_posz_fecha_sistema,
         	trim(ch_posz_pos::TEXT) AS ch_posz_pos,
         	trim(nu_posz_z_serie::TEXT) AS nu_posz_z_serie,
         	trim(nu_posturno::TEXT) AS nu_posturno
    	FROM
			pos_z_cierres 
        WHERE
			to_char(dt_posz_fecha_sistema,'YYYY-MM') = '" . $fecha_serie . "'
			AND ch_sucursal = '" . $almacen . "'
		GROUP BY
			dt_posz_fecha_sistema,
			ch_posz_pos,
			nu_posz_z_serie,
			nu_posturno
		ORDER BY
			dt_posz_fecha_sistema,
			ch_posz_pos,
			nu_posz_z_serie,
			nu_posturno;
		";

    	if ($sqlca->query($sql_series) < 0)
			return false;

    	for (; $correlativo_serie < $sqlca->numrows();) {
	    	$a_act = $sqlca->fetchRow();
	    	$array_series[$a_act['dt_posz_fecha_sistema']][$a_act['ch_posz_pos']][$a_act['nu_posturno']] = $a_act['nu_posz_z_serie'];
	    	$correlativo_serie++;
    	}

		array_push($tipo_documento_tickes, "'B'");
		$key_array = "ticket_tmp";

		//TICKES FACTURAS
		$sql_tickes_factura = "
		SELECT
			T.trans as trans,
			T.caja as caja,
			FIRST(T.dia::date) as emision,
			FIRST(T.dia::date) as vencimiento,
			(CASE
				WHEN FIRST(T.td) = 'B' AND T.usr = '' THEN '12' 
				WHEN FIRST(T.td) = 'N' AND T.usr = '' THEN '12' 
				WHEN FIRST(T.td) = 'F' AND T.usr = '' THEN '12' 
				WHEN FIRST(T.td) = 'B' AND FIRST(T.tm) = 'V' AND T.usr != '' THEN '03'
				WHEN FIRST(T.td) = 'B' AND FIRST(T.tm) = 'D' AND T.usr != '' THEN '07'
				WHEN FIRST(T.td) = 'B' AND FIRST(T.tm) = 'A' AND T.usr != '' THEN '07'
				WHEN FIRST(T.td) = 'F' AND FIRST(T.tm) = 'V' AND T.usr != '' THEN '01'
				WHEN FIRST(T.td) = 'F' AND FIRST(T.tm) = 'D' AND T.usr != '' THEN '07'
				WHEN FIRST(T.td) = 'F' AND FIRST(T.tm) = 'A' AND T.usr != '' THEN '07'
			END) as tipo,
			(CASE WHEN FIRST(T.usr) = '' THEN to_char(T.trans,'FM999999999999') else SUBSTR(TRIM(T.usr), 6) END) AS numero,
			(CASE
				WHEN FIRST(T.td) = 'B' THEN '0'
				WHEN FIRST(T.td) = 'N' THEN '0'
				WHEN FIRST(T.td) = 'F' THEN '6'
			END) as tipodi,
			(CASE
				WHEN FIRST(T.td) = 'B' AND T.usr = '' THEN '00000000004'
				WHEN FIRST(T.td) = 'B' AND FIRST(T.tm) IN ('V') AND T.usr != '' THEN '00000000008'
				WHEN FIRST(T.td) = 'B' AND FIRST(T.tm) IN ('A', 'D') AND T.usr != '' THEN '00000000009'
				WHEN FIRST(T.td) = 'N' THEN '00000000000'
				ELSE FIRST(T.ruc)
			END) as ruc,
			(CASE
				WHEN FIRST(T.td) = 'B' AND T.usr = '' THEN 'Consolidado ticket boleta'
				WHEN FIRST(T.td) = 'B' AND FIRST(T.tm) IN ('V') AND T.usr != '' THEN 'Consolidado de boleta de venta electronica'
				WHEN FIRST(T.td) = 'B' AND FIRST(T.tm) IN ('A', 'D') AND T.usr != '' THEN 'Consolidado de nota de credito bve'
				ELSE substr(FIRST(R.razsocial),0,60)
			END) as cliente,
			(CASE WHEN SUBSTRING(FIRST(T.ruc), 1, 2) = '10' THEN
       CASE
				WHEN FIRST(T.td) = 'B' THEN ''
				WHEN FIRST(T.td) = 'F' AND SUBSTR(FIRST(T.ruc),1,1) = '1' THEN split_part(FIRST(R.razsocial), ' ', 1)
       END
			END) as no_apellido_paterno,
			(CASE WHEN SUBSTRING(FIRST(T.ruc), 1, 2) = '10' THEN
       CASE
				WHEN FIRST(T.td) = 'B' THEN ''
				WHEN FIRST(T.td) = 'F' AND SUBSTR(FIRST(T.ruc),1,1) = '1' THEN split_part(FIRST(R.razsocial), ' ', 2)
       END
			END) as no_apellido_materno,
			(CASE WHEN SUBSTRING(FIRST(T.ruc), 1, 2) = '10' THEN
       CASE
				WHEN FIRST(T.td) = 'B' THEN ''
				WHEN FIRST(T.td) = 'F' AND SUBSTR(FIRST(T.ruc),1,1) = '1' THEN split_part(FIRST(R.razsocial), ' ', 3) || ' ' || split_part(FIRST(R.razsocial), ' ', 4)
       END
			END) as no_nombre,
			(CASE WHEN FIRST(T.td) = 'N' THEN '0' ELSE ROUND(SUM(T.importe-T.igv), " . $decimales . ") END) AS imponible,
			(CASE WHEN FIRST(T.td) = 'N' THEN '0' ELSE ROUND(SUM(T.igv), " . $decimales . ") END) AS igv,
			(CASE WHEN FIRST(T.td) = 'N' THEN '0' ELSE ROUND(SUM(T.importe), " . $decimales . ") END) AS importe,
			FIRST(TC.tca_venta_oficial) as tipocambio,
			(CASE WHEN FIRST(T.tm) IN ('D','A') THEN 'A' ELSE FIRST(T.td) END) as tipo_pdf,
			FIRST(cfp.ch_posz_pos) as caja,
			FIRST(T.turno) as turno,
			(SELECT par_valor FROM int_parametros WHERE par_nombre = 'taxoptional') taxoptional,
			FIRST(T.es) as es,
			(SELECT par_valor FROM int_parametros WHERE par_nombre = 'razsocial') rs,
			SUBSTR(TRIM(T.usr), 0, 5) nserie,
			SUBSTR(TRIM(T.usr), 6) numdoc,
			SUBSTR(TRIM(ext.fe1), 0, 5)||'-'||SUBSTR(TRIM(ext.fe1), 6) AS serie_numero_referencia,
			FIRST(ext.fe_emision_referencia) AS fe_emision_referencia,
			FIRST(ext.nu_tipo_referencia) AS nu_tipo_referencia
		FROM
			" . $postrans . " AS T
			LEFT JOIN pos_z_cierres AS cfp ON(T.caja = cfp.ch_posz_pos AND T.dia = cfp.dt_posz_fecha_sistema::date AND T.turno::integer = cfp.nu_posturno AND T.es = cfp.ch_sucursal)
			LEFT JOIN int_tipo_cambio AS TC ON (TC.tca_fecha = T.dia)
			LEFT JOIN ruc AS R ON (R.ruc = T.ruc)
			LEFT JOIN
			(SELECT
				venta_tickes.feoriginal AS fe1,
				venta_tickes.ticketextorno AS fe2,
				venta_tickes.feextorno AS fe3,
				TO_CHAR(venta_tickes.fefecha, 'DD/MM/YY') AS fe_emision_referencia,
				venta_tickes.nu_tipo_referencia
			FROM
				(SELECT 
					extorno.origen AS cadenaorigen,
					p.trans AS ticketoriginal,
					extorno.trans1 AS ticketextorno,
					p.usr AS feoriginal,
					extorno.usr AS feextorno,
					p.fecha AS fefecha,
					(CASE
						WHEN p.td = 'B' AND p.tm = 'V' AND p.usr != '' THEN '03'
						WHEN p.td = 'F' AND p.tm = 'V' AND p.usr != '' THEN '01'
						WHEN p.td IN ('B', 'F') AND p.tm IN ('D', 'A') AND p.usr != '' THEN '07'
					END) AS nu_tipo_referencia
				FROM
					" . $postrans . " AS p
					INNER JOIN (
						SELECT 
							(dia || caja || trim(to_char(rendi_gln,'99999999'))) AS registro,
							(dia || caja || trans) AS origen,
							trans AS trans1,
							usr
						FROM
							" . $postrans . "
						WHERE
							tm = 'A'
							AND td IN ('B','F')
							AND usr != ''
						) as extorno ON (dia || caja || trim(to_char(trans,'99999999'))) = extorno.registro
						AND td IN ('B','F')
						AND tm = 'V'
						AND p.trans < extorno.trans1
						AND p.usr != ''
					) AS venta_tickes
				GROUP BY
					venta_tickes.cadenaorigen,
				    venta_tickes.ticketoriginal,
					venta_tickes.ticketextorno,
					venta_tickes.feoriginal,
					venta_tickes.feextorno,
					venta_tickes.fefecha,
					venta_tickes.nu_tipo_referencia
				) AS ext ON ( ext.fe2 = T.trans AND ext.fe3 = T.usr)
		WHERE
			T.td IN (" . implode(',', $tipo_documento_tickes) . ")
			AND T.es = '" . $almacen . "'
		GROUP BY
			T.dia,
			T.trans,
        	T.caja,
        	T.usr,
        	ext.fe1
		ORDER BY
			2,
			1;
		";

		if ($sqlca->query($sql_tickes_factura) < 0)
			return false;

		$sumatotal_formato_sunat_bi 		= 0;
		$sumatotal_formato_sunat_igv 		= 0;
		$sumatotal_formato_sunat_exonerada 	= 0;
		$sumatotal_formato_sunat_inafecto 	= 0;
		$sumatotal_formato_sunat_vv 		= 0;

		$imponible	= 0;
		$igv		= 0;
		$exonerada	= 0;
		$inafecto	= 0;
		$importe	= 0;

		for ($i = 0; $i < $sqlca->numrows(); $i++) {

	    	$a = $sqlca->fetchRow();

	    	$trans_caja 												= trim($a['trans'] . "-" . $a['caja']);
	    	$result[$key_array][$correlativo]['trans']					= $a['trans'];
	    	$result[$key_array][$correlativo]['caja'] 					= $a['caja'];
	    	$result[$key_array][$correlativo]['emision'] 				= $a['emision'];
	    	$result[$key_array][$correlativo]['vencimiento'] 			= $a['vencimiento'];
	    	$result[$key_array][$correlativo]['tipo'] 					= $a['tipo'];
	    	$result[$key_array][$correlativo]['serie'] 					= $array_series[$a['emision']][$a['caja']][$a['turno']];
	    	$result[$key_array][$correlativo]['numero'] 				= $a['numero'];
	    	$result[$key_array][$correlativo]['tipodi'] 				= $a['tipodi'];
	    	$result[$key_array][$correlativo]['ruc'] 					= $a['ruc'];
	    	$result[$key_array][$correlativo]['cliente']				= $a['cliente'];
	    	$result[$key_array][$correlativo]['no_apellido_paterno']	= $a['no_apellido_paterno'];
	    	$result[$key_array][$correlativo]['no_apellido_materno']	= $a['no_apellido_materno'];
	    	$result[$key_array][$correlativo]['no_nombre']				= $a['no_nombre'];
	    	$result[$key_array][$correlativo]['serie_numero_referencia'] = $a['serie_numero_referencia'];
	    	$result[$key_array][$correlativo]['fe_emision_referencia'] = $a['fe_emision_referencia'];
	    	$result[$key_array][$correlativo]['nu_tipo_referencia'] = $a['nu_tipo_referencia'];
	    	$result[$key_array][$correlativo]['vfexp']					= 0;

/*
			if($a['igv'] == 0.00){
				$imponible	= 0.00;
    			$igv		= 0.00;
    			$exonerada	= $a['importe'];
    			$inafecto	= 0.00;
    			$importe	= $a['importe'];
			}else{
				$imponible	= $a['imponible'];
    			$igv		= $a['igv'];
    			$exonerada	= 0.00;
				$inafecto 	= 0.00;
    			$importe	= $a['importe'];
			}
*/

			if (in_array($trans_caja, $array_aferciones_cod) && $a['numdoc']=='') {//PONEMOS LOS MONTOS EN CEROS PARA LOS TICKES DE EXTORNOS
				$imponible	= 0;
        		$igv		= 0;
        		$exonerada	= 0;
        		$inafecto	= 0;
        		$importe	= 0;
			}else{
				if($a['igv'] == 0.00){
					$imponible	= 0.00;
	    			$igv		= 0.00;
	    			$exonerada	= $a['importe'];
	    			$inafecto	= 0.00;
	    			$importe	= $a['importe'];
				}else{
					$imponible	= $a['imponible'];
	    			$igv		= $a['igv'];
	    			$exonerada	= 0.00;
					$inafecto 	= 0.00;
	    			$importe	= $a['importe'];
				}
			}

	    	$result[$key_array][$correlativo]['imponible'] 		= $imponible;
	    	$result[$key_array][$correlativo]['exonerada'] 		= $exonerada;
	    	$result[$key_array][$correlativo]['inafecto'] 		= $inafecto;
	    	$result[$key_array][$correlativo]['isc']			= 0;
	    	$result[$key_array][$correlativo]['igv'] 			= $igv;
	    	$result[$key_array][$correlativo]['otros'] 			= 0;
	    	$result[$key_array][$correlativo]['importe'] 		= $importe;
	    	$result[$key_array][$correlativo]['tipocambio'] 	= $a['tipocambio'];
	    	$result[$key_array][$correlativo]['fecha2'] 		= "";
	    	$result[$key_array][$correlativo]['tipo2'] 			= "";
	    	$result[$key_array][$correlativo]['serie2'] 		= "";
	    	$result[$key_array][$correlativo]['numero2'] 		= "";
	    	$result[$key_array][$correlativo]['tipo_pdf'] 		= $a['tipo_pdf'];
	    	$result[$key_array][$correlativo]['taxoptional']	= $a['taxoptional'];
	    	$result[$key_array][$correlativo]['es']				= $a['es'];
	    	$result[$key_array][$correlativo]['rs']				= $a['rs'];
	    	$result[$key_array][$correlativo]['nserie']			= $a['nserie'];

			if ($a['nserie'] == '') {
	    		$result[$key_array][$correlativo]['serie'] 			= $array_series[$a['emision']][$a['caja']][$a['turno']];
			}else{
				$result[$key_array][$correlativo]['serie'] 			= $a['nserie'];
			}
			
			//VARIABLES PARA LA SUMA TOTAL TICKETS GUARDO 2--Comentado 24/08/2018
			/*
	    	$result['ticket']['total_imponible']	+= $imponible;
	    	$result['ticket']['total_igv']			+= $igv;
	    	$result['ticket']['total_exonerada'] 	+= $exonerada;
	    	$result['ticket']['total_inafecto'] 	+= $inafecto;
	    	$result['ticket']['total_importe'] 		+= $importe;

	    	//TOTALES
	    	$result['totales_imponible'] 		+= $imponible;
	    	$result['totales_igv'] 				+= $igv;
	    	$result['totales_exonerada'] 		+= $exonerada;
	    	$result['totales_inafecto'] 		+= $inafecto;
	    	$result['totales_importe'] 			+= $importe;
	    	*/

			$correlativo++;
		}

		// REGISTRO DE VENTAS DETALLADA -> FORMATO SUNAT 
    	$correlativo			= 0;
    	$inicio_boleta 			= "0";
    	$fin_boleta 			= "0";
    	$fecha_agrupar 			= "";
    	$serie_agrupar 			= "";
    	$tipo_documento_venta	= "";
    	$array_tmp_impresion 	= array();
    	$imprimir 				= 0;
    	$cantidad_factura 		= 0;
    	$array_boletas 			= array();

    	for ($i = 0; $i < count($result['ticket_tmp']); $i++) {
   			$a = $result['ticket_tmp'][$i];

        	if ($i == 0) {
		    	$tipo_documento_venta 	= trim($a['tipo_pdf']);
		    	$fecha_agrupar		= $a['emision'];
		    	$serie_agrupar 		= $a['serie'];
		    	$inicio_boleta 		= $a['numero'];
        	}

        	if ((strcmp($tipo_documento_venta, $a['tipo_pdf']) == 0 && strcmp($a['emision'], $fecha_agrupar) == 0 && strcmp($a['serie'], $serie_agrupar) == 0) && $cantidad_factura == 0) {
				$fin_boleta = $a['numero'];
				if ($a['tipo_pdf'] == "F" || $a['tipo_pdf'] == "N" || $a['tipo_pdf'] == "A"){
					$cantidad_factura = 1;
				}
        	} else {
		    	$result['ticket'][$correlativo] = $array_tmp_impresion;

		    	$result['ticket'][$correlativo]['trans']	= $inicio_boleta; //para que sea unico numero transacion
		    	$result['ticket'][$correlativo]['numero']	= $inicio_boleta . "-" . $fin_boleta;
		    	$result['ticket'][$correlativo]['serie']	= $serie_agrupar;
		    	$result['ticket'][$correlativo]['emision']	= $fecha_agrupar;
		    	$result['ticket'][$correlativo]['imponible']	= $array_tmp_impresion['imponible'];
		    	$result['ticket'][$correlativo]['igv']		= $array_tmp_impresion['igv'];
		    	$result['ticket'][$correlativo]['exonerada']	= $array_tmp_impresion['exonerada'];
		    	$result['ticket'][$correlativo]['inafecto']	= $array_tmp_impresion['inafecto'];
		    	$result['ticket'][$correlativo]['importe']	= $array_tmp_impresion['importe'];

        		if ($result['ticket'][$correlativo]['tipodi'] == 1) {
					$array_boletas[] = $correlativo;
				}

				//SUMA DE MONTOS
				/*
		    	$sumatotal_formato_sunat_bi 		+= $array_tmp_impresion['imponible'];
		    	$sumatotal_formato_sunat_igv		+= $array_tmp_impresion['igv'];
		    	$sumatotal_formato_sunat_exonerada	+= $array_tmp_impresion['exonerada'];
		    	$sumatotal_formato_sunat_inafecto	+= $array_tmp_impresion['inafecto'];
		    	$sumatotal_formato_sunat_vv 		+= $array_tmp_impresion['importe'];
		    	*/

		    	$inicio_boleta 			= $a['numero'];
		    	$i--;
		    	$correlativo++;
		    	$cantidad_factura 		= 0;
		    	$array_tmp_impresion 	= array(); //volvemos el array_tmp a vacio
		    	$imprimir 				= 1;
			}
    		$tipo_documento_venta 	= trim($a['tipo_pdf']);
    		$fecha_agrupar 		= $a['emision'];
    		$serie_agrupar 		= $a['serie'];

    		if ($imprimir == 0)
				$array_tmp_impresion = Siscont_Model::llenar_arreglo_objecto_imprimir($array_tmp_impresion, $a);
			$imprimir = 0;
		}// /. FOR

    	//PARA LA ULTIMA IMPRESION --Comentado 24/08/2018
    	
    	$result['ticket'][$correlativo] 			= $array_tmp_impresion;
    	$result['ticket'][$correlativo]['trans'] 	= $inicio_boleta; //para que sea unico numero transacion
    	$result['ticket'][$correlativo]['numero'] 	= $inicio_boleta . "-" . $fin_boleta;
    	$result['ticket'][$correlativo]['serie'] 	= $serie_agrupar;

    	$result['ticket'][$correlativo]['imponible'] 	= $array_tmp_impresion['imponible'];
    	$result['ticket'][$correlativo]['igv'] 			= $array_tmp_impresion['igv'];
    	$result['ticket'][$correlativo]['exonerada']	= $array_tmp_impresion['exonerada'];
    	$result['ticket'][$correlativo]['inafecto']		= $array_tmp_impresion['inafecto'];
    	$result['ticket'][$correlativo]['importe'] 		= $array_tmp_impresion['importe'];
    	

    	//SUMA DE MONTOS--Comentado 24/08/2018
    	/*
    	$sumatotal_formato_sunat_bi 		+= $array_tmp_impresion['imponible'];
    	$sumatotal_formato_sunat_igv		+= $array_tmp_impresion['igv'];
    	$sumatotal_formato_sunat_exonerada	+= $array_tmp_impresion['exonerada'];
    	$sumatotal_formato_sunat_inafecto	+= $array_tmp_impresion['inafecto'];
    	$sumatotal_formato_sunat_vv 		+= $array_tmp_impresion['importe'];
    	*/
		return $result;
	}

	function llenar_arreglo_objecto_imprimir($array_tmp_impresion, $a) {
		$array_tmp_impresion['caja'] 				= $a['caja'];
		$array_tmp_impresion['emision'] 			= $a['emision'];
		$array_tmp_impresion['vencimiento']	 		= $a['vencimiento'];
		$array_tmp_impresion['tipo'] 				= $a['tipo'];
		$array_tmp_impresion['serie'] 				= $a['serie'];
		$array_tmp_impresion['numero'] 				= $a['numero'];
		$array_tmp_impresion['tipodi'] 				= $a['tipodi'];
		$array_tmp_impresion['ruc'] 				= $a['ruc'];
		$array_tmp_impresion['cliente'] 			= $a['cliente'];
    	$array_tmp_impresion['no_apellido_paterno']	= $a['no_apellido_paterno'];
    	$array_tmp_impresion['no_apellido_materno']	= $a['no_apellido_materno'];
    	$array_tmp_impresion['no_nombre']			= $a['no_nombre'];
		$array_tmp_impresion['vfexp'] 				= 0;
		$array_tmp_impresion['isc'] 				= 0;
		$array_tmp_impresion['otros'] 				= 0;
		$array_tmp_impresion['tipocambio'] 			= $a['tipocambio'];
		$array_tmp_impresion['fecha2'] 				= "";
		$array_tmp_impresion['tipo2'] 				= "";
		$array_tmp_impresion['serie2'] 				= "";
		$array_tmp_impresion['numero2'] 			= "";
		$array_tmp_impresion['tipo_pdf'] 			= $a['tipo_pdf'];
		$array_tmp_impresion['serie_numero_referencia'] = $a['serie_numero_referencia'];
		$array_tmp_impresion['fe_emision_referencia'] = $a['fe_emision_referencia'];
		$array_tmp_impresion['nu_tipo_referencia'] = $a['nu_tipo_referencia'];

/*
		$imponible = 0;
		$igv = 0;
		$exonerada = 0;
		$inafecto = 0;
		$importe = 0;

		$fIGV = (float)$a['igv'];

		if ( abs($fIGV) > 0.00 ){
			$imponible	= $a['imponible'];
			$igv		= $a['igv'];
			$inafecto 	= 0.00;
			$exonerada 	= 0.00;
			$importe	= $a['importe'];
		}else{
			$imponible	= 0.00;
			$igv		= 0.00;
			$exonerada	= $a['exonerada'];
			$inafecto	= 0.00;
			$importe	= $a['importe'];
		}

		if((($a['es'] == '101' || $a['es'] == '201') && $a['taxoptional'] == '1') || ($a['igv'] == 0 || $a['igv'] == 0.00)){
			$imponible	= 0.00;
			$igv		= 0.00;
			$exonerada	= $a['exonerada'];
			$inafecto	= 0.00;
			$importe	= $a['importe'];
		}else{
			$imponible	= $a['imponible'];
			$igv		= $a['igv'];
			$inafecto 	= 0.00;
			$exonerada 	= 0.00;
			$importe	= $a['importe'];
		}
*/

		//--Comentado 24/08/2018

		$fIGV = (float)$a['igv'];
		if ( abs($fIGV) > 0.00 ){
			$array_tmp_impresion['imponible']	+= $a['imponible'];
			$array_tmp_impresion['igv']		+= $a['igv'];
			$array_tmp_impresion['importe']	+= $a['importe'];
		} else {
			$array_tmp_impresion['exonerada']	+= $a['exonerada'];
			$array_tmp_impresion['importe']		+= $a['importe'];
		}

		return $array_tmp_impresion;
    }

  function AsientoContablesSiscontExcel($nu_almacen, $postrans, $year_month, $decimales, $nu_tipo_venta, $nu_nota_despacho){
   	global $sqlca;

   	$cond_nd = "";
   	if($nu_nota_despacho == '2' || $nu_nota_despacho == 2)
   		$cond_nd = ", 'N'";

		try {

			$registros = array();

	    	$sql = "
	    	SELECT * FROM (
				SELECT
					(SELECT DISTINCT nu_tipooperacion FROM cuentas_contables_siscont WHERE nu_tipooperacion = '02') AS nu_tipo_operacion,
					TO_CHAR(FC.dt_fac_fecha::DATE,'DD') AS nu_correlativo,
					TO_CHAR(FC.dt_fac_fecha, 'DD/MM/YYYY') AS fe_emision,
					TD.tab_car_03 AS nu_tipo_documento,
					FC.ch_fac_seriedocumento|| '-' ||FC.ch_fac_numerodocumento AS nu_documento,
					TO_CHAR(FC.dt_fac_fecha, 'DD/MM/YYYY') AS fe_documento,
					TO_CHAR(FC.dt_fac_fecha, 'DD/MM/YYYY') AS fe_vencimiento,
					(CASE WHEN TD.tab_car_03 = '03' THEN '00000000001' ELSE CLI.cli_ruc END) AS nu_codigo,
					(CASE WHEN FC.ch_fac_tiporecargo2 = 'S' THEN NULL ELSE 
						(CASE WHEN FC.ch_fac_tipodocumento = '20' THEN 
							(CASE WHEN FC.ch_fac_moneda = '01' OR FC.ch_fac_moneda = '1' THEN -ROUND(FC.nu_fac_valorbruto, " . $decimales . ") ELSE ROUND((-FC.nu_fac_valorbruto * TC.tca_venta_oficial), " . $decimales . ") END)
						ELSE
							(CASE WHEN FC.ch_fac_moneda = '01' OR FC.ch_fac_moneda = '1' THEN ROUND(FC.nu_fac_valorbruto, " . $decimales . ") ELSE ROUND((FC.nu_fac_valorbruto * TC.tca_venta_oficial), " . $decimales . ") END)
						END)
					END) AS nu_base_imponible,
					(CASE WHEN FC.ch_fac_tiporecargo2 = 'S' THEN 
						(CASE WHEN
							FC.ch_fac_tipodocumento = '20' THEN 
							(CASE WHEN fc.ch_fac_moneda = '01' OR fc.ch_fac_moneda = '1' THEN -ROUND(fc.nu_fac_valortotal, " . $decimales . ") ELSE ROUND((-FC.nu_fac_valortotal * TC.tca_venta_oficial), " . $decimales . ") END)
						ELSE
							(CASE WHEN fc.ch_fac_moneda = '01' OR fc.ch_fac_moneda = '1' THEN ROUND(fc.nu_fac_valortotal, " . $decimales . ") ELSE ROUND((FC.nu_fac_valortotal * TC.tca_venta_oficial), " . $decimales . ") END)
						END)
					ELSE
						NULL
					END) AS nu_exonerado,
					(CASE WHEN FC.ch_fac_tiporecargo2 = 'S' THEN NULL ELSE 
						(CASE WHEN FC.ch_fac_tipodocumento = '20' THEN 
							(CASE WHEN FC.ch_fac_moneda = '01' OR FC.ch_fac_moneda = '1' THEN -ROUND(FC.nu_fac_impuesto1, " . $decimales . ") ELSE ROUND((-FC.nu_fac_impuesto1 * TC.tca_venta_oficial), " . $decimales . ") END)
						ELSE
							(CASE WHEN FC.ch_fac_moneda = '01' OR FC.ch_fac_moneda = '1' THEN ROUND(FC.nu_fac_impuesto1, " . $decimales . ") ELSE ROUND((FC.nu_fac_impuesto1 * TC.tca_venta_oficial), " . $decimales . ") END)
						END)
					END) AS nu_igv,
					(CASE WHEN FC.ch_fac_moneda = '01' OR FC.ch_fac_moneda = '1' THEN 'S' ELSE 'D' END) AS no_moneda,
					TC.tca_venta_oficial AS nu_tipo_cambio,
					'VENTA DE COMBUSTIBLE' AS no_glosa,
					(SELECT nu_cuentacontable FROM cuentas_contables_siscont WHERE nu_tipooperacion = '02' and nu_id=3) AS nu_cuenta_bi,
					(SELECT nu_cuentacontable FROM cuentas_contables_siscont WHERE nu_tipooperacion = '02' and nu_id=2) AS nu_cuenta_igv,
					(SELECT nu_cuentacontable FROM cuentas_contables_siscont WHERE nu_tipooperacion = '04' and nu_id=4) AS nu_cuenta_caja_cobrar,
					SUBSTR(CLI.cli_razsocial,0,60) AS no_razon_social,
					(SELECT DISTINCT nu_tiposiscont FROM cuentas_contables_siscont) AS nu_tiposiscont,
					(CASE WHEN TD.tab_car_03 = '03' THEN '0' ELSE '6' END) AS nu_tipo_documento_identidad,
					TD2.tab_car_03::TEXT AS nu_tipo_documento_original,
					(RFC.nu_serie_documento || '-' || RFC.nu_numero_documento)::TEXT AS nu_serie_numero_documento_original,
					RFC.fe_emision_original
				FROM
					fac_ta_factura_cabecera AS FC
					LEFT JOIN fac_ta_factura_complemento AS FCOM USING (ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento, cli_codigo)
					LEFT JOIN int_tipo_cambio AS TC ON (TC.tca_fecha = FC.dt_fac_fecha)
					LEFT JOIN int_clientes AS CLI ON (CLI.cli_codigo = FC.cli_codigo)
					LEFT JOIN int_tabla_general AS TD ON(substring(TRIM(TD.tab_elemento) FOR 2 FROM length(TRIM(TD.tab_elemento))-1) = FC.ch_fac_tipodocumento AND TD.tab_tabla ='08' AND TD.tab_elemento != '000000')
					LEFT JOIN (
					SELECT
						ch_fac_tipodocumento AS nu_tipo_documento,
						ch_fac_seriedocumento AS nu_serie_documento,
						ch_fac_numerodocumento AS nu_numero_documento,
						TO_CHAR(dt_fac_fecha, 'DD/MM/YYYY') AS fe_emision_original
					FROM
						fac_ta_factura_cabecera
					) AS RFC ON (
						RFC.nu_numero_documento = (string_to_array(FCOM.ch_fac_observacion2, '*'))[1]
						AND RFC.nu_serie_documento = (string_to_array(FCOM.ch_fac_observacion2, '*'))[2]
						AND RFC.nu_tipo_documento = (string_to_array(FCOM.ch_fac_observacion2, '*'))[3]
					)
					LEFT JOIN int_tabla_general AS TD2 ON(substring(TRIM(TD2.tab_elemento) FOR 2 FROM length(TRIM(TD2.tab_elemento))-1) = RFC.nu_tipo_documento AND TD2.tab_tabla ='08' AND TD2.tab_elemento != '000000')
          JOIN inv_ta_almacenes AS ALMA ON(FC.ch_almacen = ALMA.ch_almacen)
				WHERE
					ALMA.ch_sucursal = '" . $nu_almacen . "'
					AND TO_CHAR(FC.dt_fac_fecha, 'YYYY-MM') = '" . $year_month . "'
					AND FC.ch_fac_tipodocumento IN ('10','11','20','35')
				) AS DMV

				UNION ALL";
				if($nu_tipo_venta == 1) {//1: Documentos Electrónicos
				$sql .= "
				(SELECT
					(SELECT DISTINCT nu_tipooperacion FROM cuentas_contables_siscont WHERE nu_tipooperacion = '02') AS nu_tipo_operacion,
					TO_CHAR(PT.dia::DATE,'DD') AS nu_correlativo,
					FIRST(TO_CHAR(PT.fecha, 'DD/MM/YYYY')) AS fe_emision,
					(CASE
						WHEN FIRST(PT.td) = 'B' AND PT.usr = '' THEN '12'
						WHEN FIRST(PT.td) = 'N' AND PT.usr = '' THEN '12'
						WHEN FIRST(PT.td) = 'F' AND PT.usr = '' THEN '12'
						WHEN FIRST(PT.td) = 'B' AND FIRST(PT.tm) = 'V' AND PT.usr != '' THEN '03'
						WHEN FIRST(PT.td) = 'B' AND FIRST(PT.tm) IN('A', 'D') AND PT.usr != '' THEN '07'
						WHEN FIRST(PT.td) = 'F' AND FIRST(PT.tm) = 'V' AND PT.usr != '' THEN '01'
						WHEN FIRST(PT.td) = 'F' AND FIRST(PT.tm) IN('A', 'D') AND PT.usr != '' THEN '07'
					END)::TEXT as nu_tipo_documento,
					(CASE WHEN FIRST(PT.usr) != '' THEN
						SUBSTR(TRIM(PT.usr), 0, 5) || '-' || SUBSTR(TRIM(PT.usr), 6)
					ELSE
						PZC.nu_posz_z_serie || '-' || PT.trans
					END) AS nu_documento,
					FIRST(TO_CHAR(PT.fecha, 'DD/MM/YYYY')) AS fe_documento,
					FIRST(TO_CHAR(PT.fecha, 'DD/MM/YYYY')) AS fe_vencimiento,
					(CASE WHEN FIRST(PT.td) = 'B' THEN '00000000001' ELSE FIRST(PT.ruc) END) AS nu_codigo,
					(CASE
						WHEN FIRST(PT.tm) IN ('D','A') THEN
							(CASE WHEN ABS(SUM(PT.igv)) > 0 THEN ROUND(SUM(PT.importe - PT.igv), " . $decimales . ") ELSE NULL END)
						ELSE
							(CASE WHEN ABS(SUM(PT.igv)) > 0 THEN ROUND(SUM(PT.importe - PT.igv), " . $decimales . ") ELSE NULL END)
					END) AS nu_base_imponible,
					(CASE
						WHEN FIRST(PT.tm) IN ('D','A') THEN
							(CASE WHEN ABS(SUM(PT.igv)) > 0 THEN NULL ELSE ROUND(SUM(PT.importe), " . $decimales . ") END)
						ELSE
							(CASE WHEN ABS(SUM(PT.igv)) > 0 THEN NULL ELSE ROUND(SUM(PT.importe), " . $decimales . ") END)
					END) AS nu_exonerado,
					(CASE
						WHEN FIRST(PT.tm) IN ('D','A') THEN
							(CASE WHEN ABS(SUM(PT.igv)) > 0 THEN ROUND(SUM(PT.igv), " . $decimales . ") ELSE NULL END)
						ELSE
							(CASE WHEN ABS(SUM(PT.igv)) > 0 THEN ROUND(SUM(PT.igv), " . $decimales . ") ELSE NULL END)
					END) AS nu_igv,
					'S' AS no_moneda,
					FIRST(TC.tca_venta_oficial) AS nu_tipo_cambio,
					'VENTA DE COMBUSTIBLE' AS no_glosa,
					(SELECT nu_cuentacontable FROM cuentas_contables_siscont WHERE nu_tipooperacion = '02' and nu_id=3) AS nu_cuenta_bi,
					(SELECT nu_cuentacontable FROM cuentas_contables_siscont WHERE nu_tipooperacion = '02' and nu_id=2) AS nu_cuenta_igv,
					(SELECT nu_cuentacontable FROM cuentas_contables_siscont WHERE nu_tipooperacion = '04' and nu_id=4) AS nu_cuenta_caja_cobrar,
					(CASE
						WHEN FIRST(PT.td) = 'B' AND PT.usr != '' THEN 'BOLETA ELECTRONICA'
						WHEN FIRST(PT.td) = 'F' AND PT.usr != '' THEN substr(FIRST(R.razsocial),0,60)
						ELSE
							(CASE WHEN FIRST(PT.td) = 'B' THEN 'TICKET' ELSE substr(FIRST(R.razsocial),0,60) END)
					END) AS no_razon_social,
					(SELECT DISTINCT nu_tiposiscont FROM cuentas_contables_siscont) AS nu_tiposiscont,
					(CASE WHEN FIRST(PT.td) = 'B' THEN '0' ELSE '6' END) AS nu_tipo_documento_identidad,
					FIRST(PTNC.nu_tipo_documento_original)::TEXT AS nu_tipo_documento_original,
					FIRST(PTNC.nu_serie_numero_documento_original)::TEXT AS nu_serie_numero_documento_original,
					FIRST(PTNC.fe_emision_original) AS fe_emision_original
				FROM
					" . $postrans . " AS PT
					LEFT JOIN pos_z_cierres AS PZC ON(PT.es = PZC.ch_sucursal AND PT.dia = PZC.dt_posz_fecha_sistema::DATE AND PT.turno::integer = PZC.nu_posturno AND PT.caja = PZC.ch_posz_pos)
					LEFT JOIN int_tipo_cambio AS TC ON (TC.tca_fecha = PT.dia)
					LEFT JOIN ruc AS R ON (R.ruc = PT.ruc)
					LEFT JOIN (
					SELECT
						rendi_gln,
						(CASE
							WHEN td = 'B' THEN '03'
							WHEN td = 'F' THEN '01'
						END) AS nu_tipo_documento_original,
						usr AS nu_serie_numero_documento_original,
						TO_CHAR(dia, 'DD/MM/YYYY') AS fe_emision_original
					FROM
						" . $postrans . " AS PT
					WHERE
						PT.es = '" . $nu_almacen . "'
						AND PT.tm IN ('V')
						AND PT.td IN ('B','F')
						AND PT.usr != ''
					) AS PTNC ON (PTNC.rendi_gln = PT.trans)
				WHERE
					PT.es = '" . $nu_almacen . "'
					AND PT.tm IN ('V', 'A','D')
					AND PT.td IN ('B','F')
					AND PT.usr != ''
				GROUP BY
					PT.es,
					PT.dia,
					PT.turno,
					PT.caja,
					PZC.nu_posz_z_serie,
					PT.trans,
					PT.usr
				)";
			} else if($nu_tipo_venta == 3) {//Tickets
			$sql .= "
				(SELECT
					(SELECT DISTINCT nu_tipooperacion FROM cuentas_contables_siscont WHERE nu_tipooperacion = '02') AS nu_tipo_operacion,
					TO_CHAR(PT.dia::DATE,'DD') AS nu_correlativo,
					FIRST(TO_CHAR(PT.fecha, 'DD/MM/YYYY')) AS fe_emision,
					'12'::TEXT as nu_tipo_documento,
					PZC.nu_posz_z_serie || '-' || PT.trans AS nu_documento,
					FIRST(TO_CHAR(PT.fecha, 'DD/MM/YYYY')) AS fe_documento,
					FIRST(TO_CHAR(PT.fecha, 'DD/MM/YYYY')) AS fe_vencimiento,
					(CASE WHEN FIRST(PT.td) = 'B' THEN '00000000001' ELSE FIRST(PT.ruc) END) AS nu_codigo,
					NULL AS nu_base_imponible,
					NULL AS nu_exonerado,
					NULL AS nu_igv,
					'S' AS no_moneda,
					FIRST(TC.tca_venta_oficial) AS nu_tipo_cambio,
					'VENTA DE COMBUSTIBLE' AS no_glosa,
					(SELECT nu_cuentacontable FROM cuentas_contables_siscont WHERE nu_tipooperacion = '02' and nu_id=3) AS nu_cuenta_bi,
					(SELECT nu_cuentacontable FROM cuentas_contables_siscont WHERE nu_tipooperacion = '02' and nu_id=2) AS nu_cuenta_igv,
					(SELECT nu_cuentacontable FROM cuentas_contables_siscont WHERE nu_tipooperacion = '04' and nu_id=4) AS nu_cuenta_caja_cobrar,
					(CASE
						WHEN FIRST(PT.td) = 'B' THEN 'TICKET'
						WHEN FIRST(PT.td) = 'N' THEN 'NOTA DESPACHO'
						ELSE substr(FIRST(R.razsocial),0,60)
					END) AS no_razon_social,
					(SELECT DISTINCT nu_tiposiscont FROM cuentas_contables_siscont) AS nu_tiposiscont,
					(CASE WHEN FIRST(PT.td) = 'B' THEN '0' ELSE '6' END) AS nu_tipo_documento_identidad,
					''::TEXT AS nu_tipo_documento_original,
					''::TEXT AS nu_serie_numero_documento_original,
					''::TEXT AS fe_emision_original
				FROM
					". $postrans ." PT
					INNER JOIN (
					SELECT
						(caja||td||dia||turno||codigo||abs(cantidad)||abs(precio)||abs(igv)||abs(importe)||ruc||tipo||pump||fpago||at||text1||placa||es) AS registro,
						fecha,
						es||caja||trans AS idticket
					FROM
						". $postrans ."
					WHERE
						es 		= '" . $nu_almacen . "'
						AND tm 	= 'A'
						AND td 	IN ('B','F' " . $cond_nd . ")
						AND usr = ''
					) AS extorno ON (PT.caja||PT.td||PT.dia||PT.turno||PT.codigo||abs(PT.cantidad)||abs(PT.precio)||abs(PT.igv)||abs(PT.importe)||PT.ruc||PT.tipo||PT.pump||PT.fpago||PT.at||PT.text1||PT.placa||PT.es) = extorno.registro
					AND PT.es = '" . $nu_almacen . "'
					AND PT.tm IN ('V', 'D')
					AND PT.td IN ('B','F' " . $cond_nd . ")
					AND PT.usr = ''
					AND PT.fecha < extorno.fecha
					LEFT JOIN pos_z_cierres PZC ON(PT.es = PZC.ch_sucursal AND PT.dia = PZC.dt_posz_fecha_sistema::DATE AND PT.turno::integer = PZC.nu_posturno AND PT.caja = PZC.ch_posz_pos)
					LEFT JOIN int_tipo_cambio TC ON (TC.tca_fecha = PT.dia)
					LEFT JOIN ruc R ON (R.ruc = PT.ruc)
				WHERE
					PT.es = '" . $nu_almacen . "'
					AND PT.td IN ('B','F' " . $cond_nd . ")
					AND PT.usr = ''
				GROUP BY
					PT.es,
					PT.dia,
					PT.turno,
					PT.caja,
					PZC.nu_posz_z_serie,
					PT.trans
				)
				UNION ALL
				(
				SELECT
					(SELECT DISTINCT nu_tipooperacion FROM cuentas_contables_siscont WHERE nu_tipooperacion = '02') AS nu_tipo_operacion,
					TO_CHAR(PT.dia::DATE,'DD') AS nu_correlativo,
					FIRST(TO_CHAR(PT.fecha, 'DD/MM/YYYY')) AS fe_emision,
					'12'::TEXT as nu_tipo_documento,
					PZC.nu_posz_z_serie || '-' || PT.trans AS nu_documento,
					FIRST(TO_CHAR(PT.fecha, 'DD/MM/YYYY')) AS fe_documento,
					FIRST(TO_CHAR(PT.fecha, 'DD/MM/YYYY')) AS fe_vencimiento,
					(CASE WHEN FIRST(PT.td) = 'B' THEN '00000000001' ELSE FIRST(PT.ruc) END) AS nu_codigo,
					NULL AS nu_base_imponible,
					NULL AS nu_exonerado,
					NULL AS nu_igv,
					'S' AS no_moneda,
					FIRST(TC.tca_venta_oficial) AS nu_tipo_cambio,
					'VENTA DE COMBUSTIBLE' AS no_glosa,
					(SELECT nu_cuentacontable FROM cuentas_contables_siscont WHERE nu_tipooperacion = '02' and nu_id=3) AS nu_cuenta_bi,
					(SELECT nu_cuentacontable FROM cuentas_contables_siscont WHERE nu_tipooperacion = '02' and nu_id=2) AS nu_cuenta_igv,
					(SELECT nu_cuentacontable FROM cuentas_contables_siscont WHERE nu_tipooperacion = '04' and nu_id=4) AS nu_cuenta_caja_cobrar,
					(CASE
						WHEN FIRST(PT.td) = 'B' THEN 'TICKET'
						WHEN FIRST(PT.td) = 'N' THEN 'NOTA DESPACHO'
						ELSE substr(FIRST(R.razsocial),0,60)
					END) AS no_razon_social,
					(SELECT DISTINCT nu_tiposiscont FROM cuentas_contables_siscont) AS nu_tiposiscont,
					(CASE WHEN FIRST(PT.td) = 'B' THEN '0' ELSE '6' END) AS nu_tipo_documento_identidad,
					''::TEXT AS nu_tipo_documento_original,
					''::TEXT AS nu_serie_numero_documento_original,
					''::TEXT AS fe_emision_original
				FROM
					". $postrans ." PT
					LEFT JOIN pos_z_cierres PZC ON(PT.es = PZC.ch_sucursal AND PT.dia = PZC.dt_posz_fecha_sistema::DATE AND PT.turno::integer = PZC.nu_posturno AND PT.caja = PZC.ch_posz_pos)
					LEFT JOIN int_tipo_cambio TC ON (TC.tca_fecha = PT.dia)
					LEFT JOIN ruc R ON (R.ruc = PT.ruc)
				WHERE
					PT.es 		= '" . $nu_almacen . "'
					AND PT.tm 	= 'A'
					AND PT.td IN ('B','F' " . $cond_nd . ")
					AND PT.usr = ''
				GROUP BY
					PT.es,
					PT.dia,
					PT.turno,
					PT.caja,
					PZC.nu_posz_z_serie,
					PT.trans)

				UNION ALL

				(SELECT
					(SELECT DISTINCT nu_tipooperacion FROM cuentas_contables_siscont WHERE nu_tipooperacion = '02') AS nu_tipo_operacion,
					TO_CHAR(PT.dia::DATE,'DD') AS nu_correlativo,
					FIRST(TO_CHAR(PT.fecha, 'DD/MM/YYYY')) AS fe_emision,
					'12'::TEXT as nu_tipo_documento,
					PZC.nu_posz_z_serie || '-' || PT.trans AS nu_documento,
					FIRST(TO_CHAR(PT.fecha, 'DD/MM/YYYY')) AS fe_documento,
					FIRST(TO_CHAR(PT.fecha, 'DD/MM/YYYY')) AS fe_vencimiento,
					(CASE WHEN FIRST(PT.td) = 'B' THEN '00000000001' ELSE FIRST(PT.ruc) END) AS nu_codigo,
					(CASE WHEN ABS(SUM(PT.igv)) > 0 THEN SUM(PT.importe - PT.igv) ELSE NULL END) AS nu_base_imponible,
					(CASE WHEN ABS(SUM(PT.igv)) > 0 THEN NULL ELSE SUM(PT.importe) END) AS nu_exonerado,
					(CASE WHEN ABS(SUM(PT.igv)) > 0 THEN SUM(PT.igv) ELSE NULL END) AS nu_igv,
					'S' AS no_moneda,
					FIRST(TC.tca_venta_oficial) AS nu_tipo_cambio,
					'VENTA DE COMBUSTIBLE' AS no_glosa,
					(SELECT nu_cuentacontable FROM cuentas_contables_siscont WHERE nu_tipooperacion = '02' and nu_id=3) AS nu_cuenta_bi,
					(SELECT nu_cuentacontable FROM cuentas_contables_siscont WHERE nu_tipooperacion = '02' and nu_id=2) AS nu_cuenta_igv,
					(SELECT nu_cuentacontable FROM cuentas_contables_siscont WHERE nu_tipooperacion = '04' and nu_id=4) AS nu_cuenta_caja_cobrar,
					(CASE
						WHEN FIRST(PT.td) = 'B' THEN 'TICKET'
						WHEN FIRST(PT.td) = 'N' THEN 'NOTA DESPACHO'
						ELSE substr(FIRST(R.razsocial),0,60)
					END) AS no_razon_social,
					(SELECT DISTINCT nu_tiposiscont FROM cuentas_contables_siscont) AS nu_tiposiscont,
					(CASE WHEN FIRST(PT.td) = 'B' THEN '0' ELSE '6' END) AS nu_tipo_documento_identidad,
					''::TEXT AS nu_tipo_documento_original,
					''::TEXT AS nu_serie_numero_documento_original,
					''::TEXT AS fe_emision_original
				FROM
					". $postrans ." PT
					LEFT JOIN pos_z_cierres PZC ON(PT.es = PZC.ch_sucursal AND PT.dia = PZC.dt_posz_fecha_sistema::DATE AND PT.turno::integer = PZC.nu_posturno AND PT.caja = PZC.ch_posz_pos)
					LEFT JOIN int_tipo_cambio TC ON (TC.tca_fecha = PT.dia)
					LEFT JOIN ruc R ON (R.ruc = PT.ruc)
				WHERE
					PT.es = '" . $nu_almacen . "'
					AND PT.tm IN('V','D')
					AND PT.td IN ('B','F' " . $cond_nd . ")
					AND PT.usr = ''
					AND es||caja||trans NOT IN (
					SELECT
						LAST(TK.es||TK.caja||TK.trans) co_vtrans
					FROM
						". $postrans ." TK
						INNER JOIN (
							SELECT 
								(caja||td||dia||turno||codigo||abs(cantidad)||abs(precio)||abs(igv)||abs(importe)||ruc||tipo||pump||fpago||at||text1||placa||es) AS registro,
								fecha,
								es||caja||trans AS idticket
							FROM
								". $postrans ."
							WHERE
								es 		= '" . $nu_almacen . "'
								AND tm 	= 'A'
								AND td  IN ('B','F' " . $cond_nd . ")
								AND usr = ''
							) AS extorno ON (TK.caja||TK.td||TK.dia||TK.turno||TK.codigo||abs(TK.cantidad)||abs(TK.precio)||abs(TK.igv)||abs(TK.importe)||TK.ruc||TK.tipo||TK.pump||TK.fpago||TK.at||TK.text1||TK.placa||TK.es) = extorno.registro
							AND TK.es = '" . $nu_almacen . "'
							AND TK.tm IN ('V', 'D')
							AND TK.td iN ('B','F' " . $cond_nd . ")
							AND TK.usr = ''
							AND TK.fecha < extorno.fecha
					GROUP BY
						extorno.idticket
					)
				GROUP BY
					PT.es,
					PT.dia,
					PT.turno,
					PT.caja,
					PZC.nu_posz_z_serie,
					PT.trans
				)";
			}
			$sql .="
			ORDER BY
				fe_emision,
				nu_tipo_documento,
				nu_documento;
			";

/*
      echo "<pre>";
      print_r($sql);
      echo "</pre>";
*/

			if ($sqlca->query($sql) <= 0) {
       	throw new Exception(FALSE);
			}

			$registros = $sqlca->fetchAll();
/*
      echo "<pre>";
      print_r($registros);
      echo "</pre>";
*/
			return $registros;

		}catch(Exception $e){
			throw $e;
		}
    }
}

