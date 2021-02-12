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

class ConsumoValesModel extends Model {

    function ObtenerEstaciones() {
		global $sqlca;
	
		try {
			$sql = "
			SELECT
				ch_almacen AS almacen,
				trim(ch_nombre_almacen) AS nombre
			FROM
				inv_ta_almacenes
			WHERE
				ch_clase_almacen = '1'
			ORDER BY
				ch_almacen;
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

	function ObtenerReporte($almacen, $fdesde, $fhasta, $cliente, $liquidacion, $factura, $factura_ref, $hora, $arrRequest) {
		error_log(json_encode(array( $almacen, $fdesde, $fhasta, $cliente, $liquidacion, $factura, $factura_ref, $hora, $arrRequest )));
		global $sqlca;

		$d = substr($fdesde,0,2);
		$m = substr($fdesde,3,2);
		$a = substr($fdesde,6,4);

		$column_hora = "";
		$left_join_pos_trans = "";
		$orderby_hora = "";

		$FechaDiv = explode("/", $fdesde);
		$FechaIni = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];
		$postrans = "pos_trans".$FechaDiv[2].$FechaDiv[1];
		$FechaDiv = explode("/", $fhasta);
		$FechaFin = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];

		if (("pos_trans".$FechaDiv[2].$FechaDiv[1]) != $postrans && $hora == "true"){
			echo "<br/><label style='color:red'>Ambas fechas deben de estar dentro del mismo mes</label>";
			return "INVALID_DATE";
		} 
		
		if ($hora == true) {						
			$column_hora = ", TO_CHAR(PT.fecha, 'HH12:MI:SS') AS hora";
			$pos_transYM = "pos_trans" . $a . $m;
			//$left_join_pos_trans = "LEFT JOIN " . $pos_transYM . " AS PT ON(PT.caja||'-'||PT.trans = cab.ch_documento OR PT.trans::VARCHAR = cab.ch_documento)";
			$left_join_pos_trans = "
				LEFT JOIN (
					SELECT
						pos.caja as caja,
						pos.trans as trans,
						FIRST(pos.fecha) as fecha
					FROM
						" . $pos_transYM . " pos
					GROUP BY
						1,2
				) AS PT ON(PT.caja||'-'||PT.trans = cab.ch_documento OR PT.trans::VARCHAR = cab.ch_documento)
			";

			$orderby_hora = ", hora DESC";
			
			$column_hora_select = ", (SELECT TO_CHAR(PT.fecha, 'HH12:MI:SS') FROM " . $pos_transYM . " PT WHERE PT.caja||'-'||PT.trans = cab.ch_documento OR PT.trans::VARCHAR = cab.ch_documento LIMIT 1) as hora";
		}

		$fdesde = $a."-".$m."-".$d;

		$d = substr($fhasta,0,2);
		$m = substr($fhasta,3,2);
		$a = substr($fhasta,6,4);

		$fhasta = $a."-".$m."-".$d;

		if(!empty($cliente))
			$cliente = "AND cab.ch_cliente = '" . $cliente . "'";

		if(!empty($liquidacion))
			$liquidacion = "AND fac2.ch_liquidacion LIKE '%" . $liquidacion . "%'";

		if(!empty($factura))
			$factura = "AND (fac.ch_fac_numerodocumento LIKE '%" . $factura . "%' OR fac2.ch_fac_numerodocumento LIKE '% " . $factura . "%')";

		if($almacen == "T")
			$almacen = "";
		else
			$almacen = "AND cab.ch_sucursal = '" . $almacen . "'";

		$cond_tipo_cliente = '';
		if ( $arrRequest['iTipoCliente'] == '0' ){//Efectivo
			$cond_tipo_cliente = "AND cli.cli_ndespacho_efectivo='1' AND cli.cli_anticipo='N'";
		} else if ( $arrRequest['iTipoCliente'] == '1' ){//Crédito
			$cond_tipo_cliente = "AND cli.cli_ndespacho_efectivo='0' AND cli.cli_anticipo='N'";
		} else if ( $arrRequest['iTipoCliente'] == '2' ){//Anticipo
			$cond_tipo_cliente = "AND cli.cli_ndespacho_efectivo='0' AND cli.cli_anticipo='S'";
		}

		//Columna
		$sColumnPrecioPizarra = ($arrRequest['sPrecioPizarra'] == true) ? ', det.nu_precio_especial' : '';

		try {
			$registro = array();

			$sql = "
SELECT
 cab.ch_cliente codcliente,
 cli.cli_razsocial nomcliente,
 cab.ch_sucursal||' - '||alma.ch_nombre_breve_almacen almacen,
 cab.ch_sucursal nu_almacen,
 fac2.ch_liquidacion liquidacion,
 --fac.ch_fac_numerodocumento AS documento,
 CASE
when cli.cli_anticipo='S' then fac.cod_hermandad 
 else
 fac.ch_fac_seriedocumento||'-'||fac.ch_fac_numerodocumento
 end AS documento,
 fac2.ch_fac_numerodocumento AS documento2,
 cab.ch_documento AS numero,
 fac.cod_hermandad AS referencia,--cai
 TO_CHAR(cab.dt_fecha, 'DD/MM/YYYY') AS fecha,
 cab.ch_placa AS placa,
 art.art_descbreve AS producto,
 cab.nu_odometro AS odometro,
 det.nu_cantidad AS cantidad,
 det.nu_importe AS importe,
 com.ch_numeval AS vale,
 pf.nomusu AS chofer,
 cab.ch_turno,
 cli.cli_ndespacho_efectivo AS nu_tipo_efectivo,
 cli.cli_anticipo AS no_tipo_anticipo,
 CASE 
 WHEN det.nu_precio_unitario is NULL AND det.nu_importe != 0 AND det.nu_cantidad != 0 THEN ROUND(det.nu_importe/det.nu_cantidad,2)
 WHEN det.nu_precio_unitario is NULL AND det.nu_importe = 0 AND det.nu_cantidad = 0 THEN ROUND(0,2)
 ELSE 
 det.nu_precio_unitario  
 END AS ss_precio_contratado
  " . $column_hora_select . "
 " . $sColumnPrecioPizarra . "
FROM
 val_ta_cabecera AS cab
 LEFT JOIN val_ta_detalle AS det
  ON(cab.ch_sucursal = det.ch_sucursal AND cab.ch_documento = det.ch_documento AND cab.dt_fecha = det.dt_fecha)
 LEFT JOIN val_ta_complemento AS com
  ON(cab.ch_sucursal = com.ch_sucursal AND cab.ch_documento = com.ch_documento AND cab.dt_fecha = com.dt_fecha)
 LEFT JOIN val_ta_complemento_documento AS fac
  ON(fac.art_codigo = det.ch_articulo AND fac.ch_numeval = cab.ch_documento AND fac.ch_cliente = cab.ch_cliente AND cab.ch_sucursal = cab.ch_sucursal AND fac.dt_fecha = cab.dt_fecha)
 LEFT JOIN fac_ta_factura_cabecera AS fac2
  ON(fac2.ch_liquidacion = cab.ch_liquidacion)
 LEFT JOIN pos_fptshe1 AS pf
  ON(pf.numtar = cab.ch_tarjeta)
 LEFT JOIN inv_ta_almacenes AS alma
  ON(cab.ch_sucursal = alma.ch_almacen)
 LEFT JOIN int_clientes AS cli
  ON(cli.cli_codigo = cab.ch_cliente)
 LEFT JOIN int_articulos AS art
  ON(art.art_codigo = det.ch_articulo)
WHERE
 cab.dt_fecha BETWEEN '" . $fdesde . "' AND '" . $fhasta . "'
 " . $almacen . "
 " . $cliente . "
 " . $liquidacion . "
 " . $factura . "
 " . $cond_tipo_cliente . "
ORDER BY
 cab.ch_cliente,
 cab.ch_sucursal,
 cab.dt_fecha DESC
 " . $orderby_hora . "
			";
		error_log( json_encode( $sql ) );

			if ($sqlca->query($sql) <= 0)
				throw new Exception("No hay ningun registro en este rango de fecha: ".$fdesde." - ".$fhasta);
       
			while ($reg = $sqlca->fetchRow())
				$registro[] = $reg;

			return $registro;

		}catch(Exception $e){
			throw $e;
		}
	}
}

