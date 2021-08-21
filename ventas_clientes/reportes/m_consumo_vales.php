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
		
		if ($hora == "true") {						
			$column_hora = ", TO_CHAR(PT.fecha, 'HH12:MI:SS') AS hora";
			$pos_transYM = "pos_trans" . $a . $m;
			//$left_join_pos_trans = "LEFT JOIN " . $pos_transYM . " AS PT ON(PT.caja||'-'||PT.trans = cab.ch_documento OR PT.trans::VARCHAR = cab.ch_documento)";
			$left_join_pos_trans = "
				LEFT JOIN (
					SELECT
						pos.caja as caja,
						pos.trans as trans,
						FIRST(pos.fecha) as fecha --ESTO YA LO LIMITA A 1
					FROM
						" . $pos_transYM . " pos
					GROUP BY
						1,2
				) AS PT ON(PT.caja||'-'||PT.trans = cab.ch_documento OR PT.trans::VARCHAR = cab.ch_documento)
			";
			$orderby_hora = ", hora DESC";
			
			$column_hora_select = ", (SELECT TO_CHAR(PT.fecha, 'HH12:MI:SS') FROM " . $pos_transYM . " PT WHERE PT.caja||'-'||PT.trans = cab.ch_documento OR PT.trans::VARCHAR = cab.ch_documento LIMIT 1) as hora";
			$column_hora_select = ", TO_CHAR(cab.fecha_replicacion, 'HH24:MI:SS') as hora";
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
		//$sColumnPrecioPizarra = ($arrRequest['sPrecioPizarra'] == true) ? ', det.nu_precio_especial' : '';
		if($arrRequest['sPrecioPizarra'] == true){
			$sColumnPrecioPizarra = ",  CASE 
											WHEN det.nu_cantidad = 0 THEN ROUND(0,4)
											ELSE ROUND(com.nu_importe/det.nu_cantidad,4)
										END AS nu_precio_especial"; 
		}

		$orderByVersion = "";
		$nueva_logica = false;
		if($arrRequest['iTipoVersion'] == 1){
			$orderByVersion = "
				6, --documento
				art.art_codigo,
			"; 
			$nueva_logica = true;
		}

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
 end AS documento, --AQUI OBTIENE EL CAMPO #FACTURA
 fac2.ch_fac_numerodocumento AS documento2,
 cab.ch_documento AS numero,
 fac.cod_hermandad AS referencia,--cai
 TO_CHAR(cab.dt_fecha, 'DD/MM/YYYY') AS fecha,
 cab.ch_placa AS placa,
 art.art_codigo AS codigo,
 art.art_descbreve AS producto,
 cab.nu_odometro AS odometro,
 det.nu_cantidad AS cantidad,
 det.nu_importe AS importe,
 com.ch_numeval AS vale, --Numero de vale
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
 ,CASE
	WHEN cli.cli_anticipo='S' then --CLIENTES ANTICIPO
		(
		SELECT 
			nu_fac_valortotal 
		FROM 
			fac_ta_factura_cabecera
		WHERE 
			ch_fac_seriedocumento = (string_to_array(fac.cod_hermandad, '-'))[1]
			AND ch_fac_numerodocumento = (string_to_array(fac.cod_hermandad, '-'))[2]
			AND ch_fac_anticipo = 'S'
		LIMIT 1
		)
	ELSE --CLIENTES QUE NO SEAN ANTICIPO (CREDITO, EFECTIVO)
		(
		SELECT 
			nu_fac_valortotal 
		FROM 
			fac_ta_factura_cabecera
		WHERE 
			ch_fac_seriedocumento = fac.ch_fac_seriedocumento
			AND ch_fac_numerodocumento = fac.ch_fac_numerodocumento
			AND ch_fac_anticipo != 'S'
		LIMIT 1
		)
	END AS importe_factura_anticipo
FROM
 val_ta_cabecera AS cab
 LEFT JOIN val_ta_detalle AS det
  ON(cab.ch_sucursal = det.ch_sucursal AND cab.ch_documento = det.ch_documento AND cab.dt_fecha = det.dt_fecha)
 LEFT JOIN val_ta_complemento AS com --Numero de vale
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
 " . $orderByVersion . "
 cab.dt_fecha DESC
 " . $orderby_hora . "
			";
		error_log( json_encode( $sql ) );			

			if ($sqlca->query($sql) <= 0)
				throw new Exception("No hay ningun registro en este rango de fecha: ".$fdesde." - ".$fhasta);

			if($nueva_logica){
				while ($reg = $sqlca->fetchRow()){
					$dataVales[] = $reg;
				}

				foreach ($dataVales as $key => $reg) {
					//VERIFICAR TIPO DE CLIENTE
					$sTipoCliente = 'EFECTIVO';
					if ( $reg['nu_tipo_efectivo'] == '0' && $reg['no_tipo_anticipo'] == 'N' ){
						$sTipoCliente = 'CREDITO';
					} else if ( $reg['nu_tipo_efectivo'] == '0' && $reg['no_tipo_anticipo'] == 'S' ){
						$sTipoCliente = 'ANTICIPO';
					}
					
					//ARMAMOS ARRAY DE DATOS AGRUPADOS
					$cliente   = $reg['codcliente'] . "|" . $reg['nomcliente'] . "|" . $sTipoCliente;
					$documento = $reg['documento'];
					$item      = $reg['codigo'] . "|" . $reg['producto'];
					$registro[ $cliente ][ $documento ][ $item ][] = $reg;									
					
					//AGRUPAMOS CANTIDADES GENERALES
					$registro['total_general']['cantidad_general'] += $reg['cantidad'];					
					$registro['total_general']['importe_general'] += $reg['importe'];					

					//AGRUPAMOS CANTIDADES POR CLIENTES
					$registro[ $cliente ]['total_cliente']['cantidad_cliente'] += $reg['cantidad'];					
					$registro[ $cliente ]['total_cliente']['importe_cliente'] += $reg['importe'];				

					//AGRUPAMOS CANTIDADES POR FACTURAS DENTRO DE CLIENTE
					$registro[ $cliente ][ $documento ]['total_factura']['cantidad_factura'] += $reg['cantidad'];
					$registro[ $cliente ][ $documento ]['total_factura']['importe_factura'] += $reg['importe'];
					
					//AGRUPAMOS CANTIDADES POR ITEMS DENTRO DE FACTURAS DENTRO DE CLIENTES
					$registro[ $cliente ][ $documento ][ $item ]['total_item']['cantidad_item'] += $reg['cantidad'];
					$registro[ $cliente ][ $documento ][ $item ]['total_item']['importe_item'] += $reg['importe'];
				}
			}else{
				while ($reg = $sqlca->fetchRow()){
					$registro[] = $reg;
				}
			}

			return $registro;

		}catch(Exception $e){
			throw $e;
		}
	}

	function getTotalItemByFactura($sTipoCliente, $documento, $codigo_item){
		global $sqlca;

		//OBTENEMOS DATOS DE FACTURA
		$documento_porciones = explode("-", $documento);
		$serie  = $documento_porciones[0];
		$numero = $documento_porciones[1];

		//VERIFICACION DE TIPO DE CLIENTE
		if($sTipoCliente == "ANTICIPO"){
			$sql = "
				SELECT 
					SUM(nu_fac_cantidad) as nu_fac_cantidad,
					SUM(nu_fac_valortotal) as nu_fac_valortotal
				FROM 
					fac_ta_factura_detalle
				WHERE 
					ch_fac_seriedocumento = '". $serie ."'
					AND ch_fac_numerodocumento = '". $numero ."'
					AND art_codigo = '". $codigo_item ."'
				GROUP BY 
					art_codigo
				LIMIT 1
			";

			return $sqlca->firstRow($sql);
		}
		
		return NULL;
	}

	function getTotalFactura($sTipoCliente, $documento){
		global $sqlca;

		//OBTENEMOS DATOS DE FACTURA
		$documento_porciones = explode("-", $documento);
		$serie  = $documento_porciones[0];
		$numero = $documento_porciones[1];

		//VERIFICACION DE TIPO DE CLIENTE
		$where = "";
		if($sTipoCliente == "ANTICIPO"){
			$where = " AND ch_fac_anticipo = 'S'";
		}else{
			$where = " AND ch_fac_anticipo != 'S'";
		}

		//OBTENEMOS VALOR TOTAL
		$sql = "				
			SELECT 
				nu_fac_valortotal 
			FROM 
				fac_ta_factura_cabecera
			WHERE 
				ch_fac_seriedocumento = '". $serie ."'
				AND ch_fac_numerodocumento = '". $numero ."'
				$where
			LIMIT 1;
		";
		$reg = $sqlca->firstRow($sql);
		$nu_fac_valortotal = $reg['nu_fac_valortotal'];

		//OBTENEMOS CANTIDAD TOTAL
		$sql2 = "				
			SELECT 
				SUM(nu_fac_cantidad) as nu_fac_cantidad
			FROM 
				fac_ta_factura_detalle fd
				INNER JOIN fac_ta_factura_cabecera fc ON (fd.ch_fac_seriedocumento = fc.ch_fac_seriedocumento AND fd.ch_fac_numerodocumento = fc.ch_fac_numerodocumento)
			WHERE 
				fd.ch_fac_seriedocumento = '". $serie ."'
				AND fd.ch_fac_numerodocumento = '". $numero ."'
				$where
		";
		$reg = $sqlca->firstRow($sql2);
		$nu_fac_cantidad = $reg['nu_fac_cantidad'];

		//OBTENEMOS RESPONSE
		$response = array(
			0                   => $nu_fac_cantidad,
			1                   => $nu_fac_valortotal,
			"nu_fac_cantidad"   => $nu_fac_cantidad,
			"nu_fac_valortotal" => $nu_fac_valortotal
		);			
		
		return $response;
	}
}

