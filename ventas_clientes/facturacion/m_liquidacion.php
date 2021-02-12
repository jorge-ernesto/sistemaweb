<?php

class LiquidacionModel extends Model{

    function ModelReportePDF($serie, $numero){
		global $sqlca;

		$result = Array();

		//OBTENEMOS CH_LIQUIDACION DE FAC_TA_FACTURA CON LA SERIE Y NUMERO DE DOCUMENTO
		$sql = "
SELECT 
	c.ch_liquidacion, c.cli_codigo
FROM
	val_ta_complemento_documento v
	INNER JOIN fac_ta_factura_cabecera c ON (c.ch_fac_tipodocumento = v.ch_fac_tipodocumento AND c.ch_fac_seriedocumento = v.ch_fac_seriedocumento AND c.ch_fac_numerodocumento = v.ch_fac_numerodocumento)
WHERE	
	c.ch_fac_seriedocumento = '". trim($serie) ."' AND c.ch_fac_numerodocumento = '". trim($numero) ."';
		";

		// echo "<pre>";
		// echo $sql;
		// echo "</pre>";

		if ($sqlca->query($sql) < 0) return false;

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$ch_liquidacion = $a['ch_liquidacion'];
			$cli_codigo = $a['cli_codigo'];
		}
		//CERRAR OBTENEMOS CH_LIQUIDACION DE FAC_TA_FACTURA CON LA SERIE Y NUMERO DE DOCUMENTO

		$sql = "
SELECT
 val_liq.ch_numeval AS ch_documento,
 val_liq.ch_cliente,
 cli.cli_razsocial,
 val_liq.nu_fac_valortotal AS nu_importe,
 val_det.ch_articulo,
 val_det.nu_cantidad as art_cantidad,		
 val_det.nu_importe as art_importe,
 art.art_descripcion,
 val_liq.ch_liquidacion, 
 val_liq.ch_sucursal,
 (CASE WHEN val_det.nu_cantidad > 0 THEN ROUND(val_det.nu_importe / val_det.nu_cantidad, 4) ELSE 0 END) AS art_precio,
 val_cab.ch_placa AS placa,
 (SELECT numpla FROM pos_fptshe1 WHERE pos_fptshe1.numpla = val_cab.ch_placa LIMIT 1),
 val_liq.dt_fecha AS fecha_insercion,
 pos.nomusu AS conductor,
 val_cab.fecha_replicacion AS dt_fechaactualizacion
FROM
 val_ta_complemento_documento AS val_liq
 LEFT JOIN val_ta_cabecera AS val_cab ON(val_cab.ch_cliente = val_liq.ch_cliente AND val_liq.ch_numeval = val_cab.ch_documento AND val_liq.dt_fecha = val_cab.dt_fecha)
 LEFT JOIN val_ta_detalle AS val_det ON(val_liq.ch_numeval = val_det.ch_documento AND val_liq.art_codigo = val_det.ch_articulo AND val_liq.dt_fecha = val_det.dt_fecha)
 --LEFT JOIN val_ta_cabecera val_cab ON (val_cab.ch_cliente = val_liq.ch_cliente AND val_liq.ch_numeval = val_cab.ch_documento AND val_liq.dt_fecha = val_cab.dt_fecha AND val_liq.ch_sucursal = val_cab.ch_sucursal)
 --LEFT JOIN val_ta_detalle val_det ON (val_liq.ch_numeval = val_det.ch_documento AND val_liq.art_codigo = val_det.ch_articulo AND val_liq.dt_fecha = val_det.dt_fecha AND val_liq.ch_sucursal = val_det.ch_sucursal)
 LEFT JOIN int_clientes AS cli ON(cli.cli_codigo = val_liq.ch_cliente)
 LEFT JOIN int_articulos AS art ON(art.art_codigo = val_liq.art_codigo)
 LEFT JOIN pos_fptshe1 AS pos ON(pos.numpla = val_cab.ch_placa AND pos.numtar = val_cab.ch_tarjeta AND pos.codcli = val_cab.ch_cliente)
WHERE
 val_liq.ch_liquidacion='" . trim($ch_liquidacion) . "'
 AND val_liq.ch_cliente='" . trim($cli_codigo) . "'
ORDER BY
 --val_cab.fecha_replicacion
 val_liq.ch_liquidacion,
 val_cab.ch_cliente,
 val_cab.ch_documento;
		";

		// echo "<pre>";
		// echo $sql;
		// echo "</pre>";

		$sqlca->query($sql);
		$numrows = $sqlca->numrows();
		while($reg = $sqlca->fetchRow()){
			$registro[] = $reg;
		}

		// return $registro;
		return $ch_liquidacion;
	}

	function search($serie, $numero){
		global $sqlca;

		$result = Array();

		//OBTENEMOS CH_LIQUIDACION DE FAC_TA_FACTURA CON LA SERIE Y NUMERO DE DOCUMENTO
		$sql = "
SELECT 
	c.ch_liquidacion, c.cli_codigo
FROM
	val_ta_complemento_documento v
	INNER JOIN fac_ta_factura_cabecera c ON (c.ch_fac_tipodocumento = v.ch_fac_tipodocumento AND c.ch_fac_seriedocumento = v.ch_fac_seriedocumento AND c.ch_fac_numerodocumento = v.ch_fac_numerodocumento)
WHERE	
	c.ch_fac_seriedocumento = '". trim($serie) ."' AND c.ch_fac_numerodocumento = '". trim($numero) ."';
		";

		// echo "<pre>";
		// echo $sql;
		// echo "</pre>";

		if ($sqlca->query($sql) < 0) return false;

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$ch_liquidacion = $a['ch_liquidacion'];
			$cli_codigo = $a['cli_codigo'];
		}
		//CERRAR OBTENEMOS CH_LIQUIDACION DE FAC_TA_FACTURA CON LA SERIE Y NUMERO DE DOCUMENTO

		$sql = "
SELECT
 FIRST(val_liq.ch_numeval) AS ch_documento,
 FIRST(val_liq.ch_cliente) AS ch_cliente,
 FIRST(cli.cli_razsocial) AS cli_razsocial,
 SUM(val_liq.nu_fac_valortotal) AS nu_importe,
 val_det.ch_articulo AS ch_articulo, --AGRUPADO
 SUM(val_det.nu_cantidad) as art_cantidad,		
 SUM(val_det.nu_importe) as art_importe,
 art.art_descripcion AS art_descripcion, --AGRUPADO
 FIRST(val_liq.ch_liquidacion) AS ch_liquidacion, 
 FIRST(val_liq.ch_sucursal) AS ch_sucursal,
 (CASE WHEN val_det.nu_cantidad > 0 THEN ROUND(val_det.nu_importe / val_det.nu_cantidad, 4) ELSE 0 END) AS art_precio, --AGRUPADO
 FIRST(val_cab.ch_placa) AS placa,
 (SELECT numpla FROM pos_fptshe1 WHERE pos_fptshe1.numpla = FIRST(val_cab.ch_placa) LIMIT 1),
 FIRST(val_liq.dt_fecha) AS fecha_insercion,
 FIRST(pos.nomusu) AS conductor,
 FIRST(val_cab.fecha_replicacion) AS dt_fechaactualizacion
FROM
 val_ta_complemento_documento AS val_liq
 LEFT JOIN val_ta_cabecera AS val_cab ON(val_cab.ch_cliente = val_liq.ch_cliente AND val_liq.ch_numeval = val_cab.ch_documento AND val_liq.dt_fecha = val_cab.dt_fecha)
 LEFT JOIN val_ta_detalle AS val_det ON(val_liq.ch_numeval = val_det.ch_documento AND val_liq.art_codigo = val_det.ch_articulo AND val_liq.dt_fecha = val_det.dt_fecha)
 --LEFT JOIN val_ta_cabecera val_cab ON (val_cab.ch_cliente = val_liq.ch_cliente AND val_liq.ch_numeval = val_cab.ch_documento AND val_liq.dt_fecha = val_cab.dt_fecha AND val_liq.ch_sucursal = val_cab.ch_sucursal)
 --LEFT JOIN val_ta_detalle val_det ON (val_liq.ch_numeval = val_det.ch_documento AND val_liq.art_codigo = val_det.ch_articulo AND val_liq.dt_fecha = val_det.dt_fecha AND val_liq.ch_sucursal = val_det.ch_sucursal)
 LEFT JOIN int_clientes AS cli ON(cli.cli_codigo = val_liq.ch_cliente)
 LEFT JOIN int_articulos AS art ON(art.art_codigo = val_liq.art_codigo)
 LEFT JOIN pos_fptshe1 AS pos ON(pos.numpla = val_cab.ch_placa AND pos.numtar = val_cab.ch_tarjeta AND pos.codcli = val_cab.ch_cliente)
WHERE
 val_liq.ch_liquidacion='" . trim($ch_liquidacion) . "'
GROUP BY
 5,8,11
ORDER BY
 --val_liq.ch_liquidacion,
 --val_cab.ch_cliente,
 --val_cab.ch_documento;
 9,2,1;
		";
		
		// echo "<pre>";
		// echo $sql;
		// echo "</pre>";
		
		if ($sqlca->query($sql) < 0) return false;
		
		$total = 0;
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
	
			$ch_documento = $a[0];
			$ch_cliente = $a[1];
			$cli_razsocial = $a[2];
			$nu_importe = $a[3];
			$ch_articulo = $a[4];
			$nu_cantidad = $a[5];
			$art_importe = $a[6];
			$art_descripcion = $a[7];
			$art_precio = $a[10];
			$dt_fecha = $a[13];

			$result[$ch_liquidacion]['detalle'][$i]['ch_documento'] = $ch_documento;
			$result[$ch_liquidacion]['detalle'][$i]['nu_importe'] = $nu_importe;
			$result[$ch_liquidacion]['detalle'][$i]['ch_articulo'] = $ch_articulo;
			$result[$ch_liquidacion]['detalle'][$i]['nu_cantidad'] = $nu_cantidad;
			$result[$ch_liquidacion]['detalle'][$i]['art_importe'] = $art_importe;
			$result[$ch_liquidacion]['detalle'][$i]['art_descripcion'] = $art_descripcion;
			$result[$ch_liquidacion]['detalle'][$i]['art_precio'] = $art_precio;
			$result[$ch_liquidacion]['detalle'][$i]['dt_fecha'] = $dt_fecha;
			
			$result[$ch_liquidacion]['ch_cliente'] = $ch_cliente;
			$result[$ch_liquidacion]['cli_razsocial'] = $cli_razsocial;
			$result[$ch_liquidacion]['serie'] = $serie;
			$result[$ch_liquidacion]['numero'] = $numero;
			$total += $art_importe;
		}
		if ($sqlca->numrows() > 0) $result[$ch_liquidacion]['total'] = $total;	    
	
		return $result;
    }
}

