<?php
class Consumos_Placa_Model extends Model {
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
 ch_clase_almacen='1'
ORDER BY
 ch_almacen;
			";
			if($sqlca->query($sql) <= 0){
				throw new Exception("Problemas al obtener almacén");
			}
			while($reg = $sqlca->fetchRow()){
				$registro[] = $reg;
			}
			return $registro;
		}catch(Exception $e){
			throw $e;
		}
    }

	function ObtenerReporte($almacen, $fdesde, $fhasta, $cliente, $placa) {
		global $sqlca;

		$where_almacen = '';
		$where_cliente = '';
		$where_placa = '';
		if( !empty($almacen) && $almacen != 'T' )
			$where_almacen = "AND CAB.ch_sucursal='" . $almacen . "'";

		if( !empty($cliente) )
			$where_cliente = "AND CAB.ch_cliente LIKE '%" . $cliente . "%'";

		if( !empty($placa) )
			$where_placa = "AND CAB.ch_placa LIKE '%" . $placa . "%'";

		try {
			$registro = array();
			$sql = "
SELECT
 CAB.ch_placa AS placa,
 CAB.ch_cliente AS codcliente,
 CAB.ch_documento AS ticket,
 CAB.nu_odometro AS odometro,
 DET.ch_articulo AS codproducto,
 ROUND(SUM(DET.nu_importe), 2) AS importe,
 ROUND(SUM(DET.nu_cantidad), 2) AS cantidad,
 (CASE WHEN (SUM(DET.nu_cantidad) = 0) OR (SUM(DET.nu_importe) = 0 AND SUM(DET.nu_cantidad) = 0) THEN 0.00 ELSE ROUND(SUM(DET.nu_importe) / SUM(DET.nu_cantidad),2) END) AS precio,
 CAB.dt_fecha AS fecha,
 ART.art_descripcion AS nomproducto,
 ALMA.ch_nombre_breve_almacen AS desalmacen,
 TRIM(CLI.cli_razsocial) AS descliente
FROM
 val_ta_cabecera AS CAB
 LEFT JOIN val_ta_detalle AS DET
  USING(ch_sucursal, ch_documento, dt_fecha)
 LEFT JOIN int_articulos AS ART
  ON(DET.ch_articulo = ART.art_codigo)
 LEFT JOIN inv_ta_almacenes AS ALMA
  ON(CAB.ch_sucursal = ALMA.ch_almacen)
 LEFT JOIN int_clientes AS CLI
  ON(CLI.cli_codigo = CAB.ch_cliente)
WHERE
 CAB.dt_fecha BETWEEN '" . $fdesde . "' AND '" . $fhasta . "'
 " . $where_almacen . "
 " . $where_cliente . "
 " . $where_placa . "
GROUP BY
 CAB.ch_placa,
 CAB.ch_cliente,
 CAB.ch_documento,
 CAB.nu_odometro,
 DET.ch_articulo,
 CAB.dt_fecha,
 ART.art_descripcion,
 ALMA.ch_nombre_breve_almacen,
 CLI.cli_razsocial
ORDER BY
 placa,
 codcliente,
 fecha;
			";

			if ($sqlca->query($sql) <= 0) {
				throw new Exception("No hay ningun registro en el rango de fecha: " . $fdesde . ' - ' . $fhasta);
			}
			while ($reg = $sqlca->fetchRow()) {
				$registro[] = $reg;
			}
			return $registro;
		} catch(Exception $e) {
			throw $e;
		}
	}

	function datosEmpresa($almacen){
		global $sqlca;

		$sql = "select 
					razsocial, 
					ruc, 
					ch_sucursal,
					(SELECT trim(ch_nombre_almacen) FROM inv_ta_almacenes WHERE ch_almacen='$almacen') as ch_nombre_almacen
				from 
					int_ta_sucursales 
				where 
					ch_sucursal = '$almacen';";
        if ($sqlca->query($sql) < 0)
            return null;

        $res = Array();
        $a = $sqlca->fetchRow();
        $res['razsocial'] = $a[0];
        $res['ruc'] = $a[1];
		$res['ch_sucursal'] = $a[2];
		$res['ch_nombre_almacen'] = $a[3];

        return $res;
	}
}

