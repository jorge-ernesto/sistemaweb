<?php

class StockModel extends Model
{
    function obtenerStockPorFecha($dt_fecha)
    {
	    global $sqlca;

	    $sql = "SELECT
		            a.nu_medicion,
		            b.ch_codigocombustible,
		            a.ch_sucursal
		        FROM
		            comb_ta_mediciondiaria a,
		            comb_ta_tanques b
		        WHERE
			            b.ch_tanque=a.ch_tanque
		            AND b.ch_sucursal=a.ch_sucursal
		            AND a.ch_sucursal='" . pg_escape_string($ch_almacen) . "'
		            AND a.dt_fechamedicion=to_date('" . pg_escape_string($dt_fecha) . "', 'dd/mm/yyyy')
		        ;
		        ";
	    if ($sqlca->query($sql) < 0) return false;

	    $result = Array();

	    for ($i = 0; $i < $sqlca->numrows(); $i++) {
	        $a = $sqlca->fetchRow();
	        $resultado['sucursales'][$a[2]][$a[1]] = $a[0];
	    }

	    return $result;
    }

    function pasarVarillasAStock($fecha)
    {
	    $varillas = StockModel::obtenerStockPorFecha($fecha);

	    foreach ($varillas as $ch_almacen => $stock) {
	        foreach ($stock as $art_codigo => $cantidad) {
	        }
	    }
    }

    function busqueda()
    {
        global $sqlca;

        $ayer = time() - (24*60*60);
        $fecha = date("Y-m-d", $ayer);
        $mes = date("m", $ayer);
        $ano = date("Y", $ayer);

        $sql = "SELECT
                    tanq.ch_sucursal,
                    prod.ch_codigocombustible,
                    prod.ch_nombrecombustible,
                    medi.nu_medicion,
                    sald.stk_stock" . $mes . "
                FROM
                    comb_ta_combustibles prod,
                    comb_ta_tanques tanq,
                    comb_ta_mediciondiaria medi,
                    inv_saldoalma sald
                WHERE
                        tanq.ch_codigocombustible=prod.ch_codigocombustible
                    AND medi.ch_tanque=tanq.ch_tanque
                    AND medi.ch_sucursal=tanq.ch_sucursal
                    AND medi.dt_fechamedicion='" . $fecha . "'
                    AND sald.stk_periodo='" . $ano . "'
                    AND trim(sald.stk_almacen)=trim(tanq.ch_sucursal)
                ORDER BY
                    tanq.ch_sucursal,
                    tanq.ch_codigocombustible
                ;
                ";

        if ($sqlca->query($sql) < 0) return false;

        $result = Array();

        for ($i = 0; $i < $sqlca->numrows(); $i++) {
            $a = $sqlca->fetchRow();

            $ch_sucursal = $a[0];
            $ch_codigocombustible = $a[1];
            $ch_nombrecombustible = $a[2];
            $nu_medicion = $a[3];
            $stk_stock = $a[4];

            $result[$ch_sucursal][$ch_codigocombustible]['nombre'] = $ch_nombrecombustible;
            $result[$ch_sucursal][$ch_codigocombustible]['medicion'] = $nu_medicion;
            $result[$ch_sucursal][$ch_codigocombustible]['stk_stock'] = $stk_stock;
        }

        return $result;
    }
}

