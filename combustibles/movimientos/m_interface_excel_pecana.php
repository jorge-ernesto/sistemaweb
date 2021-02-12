<?php

class InterfaceMovModelCE extends Model {

    function ListadoAlmacenes($codigo) {
        global $sqlca;

        $cond = '';
        if ($codigo != "") {
            $cond = "AND trim(ch_sucursal) = '" . pg_escape_string($codigo) . "' ";
        }
        $query = "SELECT ch_almacen FROM inv_ta_almacenes WHERE trim(ch_clase_almacen)='1' " . $cond . " ORDER BY ch_almacen";

        if ($sqlca->query($query) <= 0) {
            return $sqlca->get_error();
        }
        $numrows = $sqlca->numrows();

        $x = 0;
        while ($reg = $sqlca->fetchRow()) {
            if ($numrows > 1) {
                if ($x < $numrows - 1) {
                    $conc = ".";
                } else {
                    $conc = "";
                }
            }
            $listado['' . $codigo . ''] .= $reg[0] . $conc;
            $x++;
        }

        return $listado;
    }

    function ActualizarDatosFacturas($Fecha, $cod_sucursal, $cod_pecana) {
        global $sqlca;

        $condicion = "";
        if ($cod_sucursal != "all")
            $condicion = "AND c.ch_almacen='" . $cod_sucursal . "'";

        $query = "
        SELECT
            (CASE WHEN c.ch_fac_tipodocumento IN('35') THEN 'TB' ELSE 'TFA' END) AS TipoDocExt,
            c.ch_fac_seriedocumento AS SerieVenta,
            c.ch_fac_numerodocumento::TEXT AS nVenta,
            'N' AS Moneda,
            TO_CHAR(c.dt_fac_fecha, 'dd/MM/YYYY') AS Fecha,
            TO_CHAR(c.dt_fac_fecha, 'dd/MM/YYYY') AS FechaVenc,
            'T' AS Condicion,
            (SELECT par_valor FROM int_parametros WHERE par_nombre = 'pecano') AS IdSucursal,
            (CASE WHEN c.ch_fac_tipodocumento = '35' THEN '-' ELSE CLI.cli_ruc END) AS RucClientes,
            (CASE WHEN c.ch_fac_tipodocumento = '35' THEN 'CLIENTES VARIOS' ELSE CLI.cli_razsocial END) AS NomCliente,
            '-' AS DirCliente,
            '' AS SerieTicke,
            '(Vendedor)' AS NomVendedo,
            '-' AS Formapago,
            '-' AS NomTarjeta,
            '-' AS nTarjeta,
            '-' AS Obs,
            PRO.art_codigo|| '-' ||PRO.art_descripcion::TEXT AS IdProdExt,
            d.nu_fac_cantidad AS Cantiditem,
            d.nu_fac_precio AS Precioitem,
            d.nu_fac_valortotal AS Totalitem,
            '0' AS Bonificaci,
            ROUND(d.nu_fac_importeneto, 2) AS SubtotalVe,
            ROUND(d.nu_fac_impuesto1, 2) AS IGVventa,
            d.nu_fac_valortotal AS TotalVenta,
            (CASE WHEN c.ch_fac_anulado = 'S' THEN '1' ELSE '0' END) AS Anulada,
            '0' AS PAnulTiket,
            '-' AS nVentaAnul,
            '0.18' AS pIGVitem,
            ROUND(d.nu_fac_impuesto1, 2) AS IGVitem,
            '0' AS IdCasoP,
            '-' AS IdTurno,
            '-' AS IdManguera,
            '-' AS SerieProducto,
            '' AS CadVentaAnticipo,
            '' AS NomPOS,
            d.art_codigo AS codigo,
            '-' AS type
        FROM
            fac_ta_factura_cabecera AS c
            LEFT JOIN fac_ta_factura_detalle AS d USING (ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento, cli_codigo)            
            JOIN int_clientes AS CLI ON(c.cli_codigo = CLI.cli_codigo)
            JOIN int_articulos AS PRO USING(art_codigo)
        WHERE
            TO_CHAR(c.dt_fac_fecha, 'YYYY-MM') = '" . $Fecha . "'
            AND c.ch_fac_tipodocumento IN('35','10')
            " . $condicion . ";
        ";

        /*error_log('PECANA: ActualizarDatosFacturas:');
        error_log($query);*/

        if ($sqlca->query($query) < 0)
            return array();

        $result = array();
        while ($reg = $sqlca->fetchRow())
            $result[] = $reg;
        return $result;
    }

    function ActualizarDatosPostrans($Fecha, $tickes_anu, $cod_sucursal, $cod_pecana) {
        global $sqlca;

        $Fecha = str_replace("-", "", $Fecha);
        $condicion = "";
        if ($cod_sucursal == "all") {
            $condicion = "";
        } else {
            $condicion = "AND c.es='" . $cod_sucursal . "'";
        }

        $query = "
        SELECT
        	(CASE WHEN c.td in('B') then 'TB' WHEN c.td in('N') THEN 'TN' ELSE 'TFA' END) AS TipoDocExt,
        	pcf.rutaprint AS SerieVenta,
        	c.trans::text AS nVenta,
            'N' AS Moneda,
            to_char(c.dia,'dd/MM/YYYY') AS Fecha,
            to_char(c.dia,'dd/MM/YYYY') AS FechaVenc,
            (CASE WHEN (c.fpago='1') THEN 'T' ELSE 'T' END) AS Condicion,
            (select par_valor from int_parametros where par_nombre='pecano') as IdSucursal,
            (CASE
                WHEN c.td = 'B' THEN '-'
                WHEN c.td = 'N' THEN c.cuenta
                ELSE ruc.ruc
            END) AS RucClientes,
            (CASE
                WHEN c.td = 'B' THEN 'CLIENTES VARIOS'
                WHEN c.td = 'N' THEN 'CLIENTE CREDITO'
                ELSE ruc.razsocial
            END) AS NomCliente,
            '-' as DirCliente,
            pcf.nroserie as SerieTicke,
            '(Vendedor)' as NomVendedo,
            '1' as Formapago,
            '-' as NomTarjeta,
            '-' as nTarjeta,
            '-' as Obs,
            (select TRIM(c.codigo)||'-'||art_descripcion  from int_articulos  where trim(art_codigo)=trim(c.codigo) limit 1) as IdProdExt,
            c.cantidad AS Cantiditem,
            c.precio AS Precioitem,
            c.importe as Totalitem,
            '0' as Bonificaci,
            round(c.importe-((c.importe*0.18)/1.18),2) as SubtotalVe,
            round(((c.importe*0.18)/1.18),2) as IGVventa,
            c.importe as TotalVenta,
            '0'as Anulada,
            '0' as PAnulTiket,
            '-' as nVentaAnul,
            '0.18' as pIGVitem,
            round(((c.importe*0.18)/1.18),2) as IGVitem,
            '0' as IdCasoP,
            '-' as IdTurno,
            '-' as IdManguera,
            '-' as SerieProducto,
            '' as CadVentaAnticipo,
            '' as NomPOS,
            c.codigo as codigo,
            c.tm as type,
            trim(c.caja)||c.dia||trim(c.turno)||trim(c.codigo)||trim(c.cantidad::TEXT)||abs(c.importe)||trim(c.ruc)||trim(c.pump)||trim(c.tipo) as iden
        FROM
        	pos_trans" . $Fecha . " AS c
            LEFT JOIN pos_cfg AS pcf ON (c.caja = pcf.pos)
            LEFT JOIN ruc ON (c.ruc = ruc.ruc)
        WHERE
            c.td in('B','F','N')
            AND c.tipo IN('C','M')
            " . $condicion . "
        ORDER BY
            c.caja,
            c.trans;
        ";

        /*error_log('PECANA: ActualizarDatosPostrans:');
        error_log($query);*/

        if ($sqlca->query($query) < 0)
            return array();

        $result = array();
        $pasa = true;
        while ($reg = $sqlca->fetchRow()) {
            $pasa = true;
            for ($i = 0; $i < count($tickes_anu); $i++) {
                if ($reg['iden'] === $tickes_anu[$i]['iden'] && $tickes_anu[$i]['estado'] == 'FALTA' && $tickes_anu[$i]['trans_tmp'] == '0') {
                    $reg['estado'] = 'A';
                    $tickes_anu[$i]['trans_tmp'] = $reg['num_documneto'];
                    $tickes_anu[$i]['iden'] = 'OK';

                    break;
                }
            }

            $result[] = $reg;
        }
        return $result;
    }

    function getTickesAnulados($Fecha,$cod_sucursal) {
        global $sqlca;
        $Fecha = str_replace("-", "", $Fecha);
//  c.caja, --pcf.nroserie as serie_documento

        if ($cod_sucursal == "all") {
            $condicion = "  ";
        } else {
            $condicion = "  AND es='$cod_sucursal' ";
        }

        $query = "
               SELECT
               trim(caja)||dia||trim(turno)||trim(codigo)||trim(cantidad::TEXT)||abs(importe)||trim(ruc)||trim(pump)||trim(tipo) as iden  ,
               'FALTA' as estado,'0' as trans_tmp
               FROM  pos_trans$Fecha where tm='A'  $condicion  ;
";

        if ($sqlca->query($query) < 0) {
            return array();
            //return $sqlca->get_error();
        }
        $result = array();
        while ($reg = $sqlca->fetchRow()) {
            $result[] = $reg;
        }


        return $result;
    }

}

