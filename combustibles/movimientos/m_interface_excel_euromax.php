<?php

ini_set("memory_limit", '3G');
ini_set("upload_max_filesize", "15M");
ini_set("post_max_size", "15M");
ini_set('max_execution_time', '700');
ini_set('max_input_time', '700');

class InterfaceMovModelEuromax extends Model {

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
			$condicion = "AND c.ch_almacen = '$cod_sucursal'";

		$query = "
			SELECT  
				(CASE WHEN c.ch_fac_tipodocumento in('35') THEN 'TB' ELSE 'TFA' END) as TipoDocExt,
			    	c.ch_fac_seriedocumento as SerieVenta,
		            	c.ch_fac_numerodocumento::text as nVenta,
		            	'N' as Moneda,
		            	to_char(c.dt_fac_fecha,'dd/MM/YYYY') as Fecha,
		            	to_char(c.dt_fac_fecha,'dd/MM/YYYY') as FechaVenc,
				'T' as Condicion,
		            	(select par_valor from int_parametros where par_nombre='euromax') as IdSucursal,
		            	(CASE WHEN c.ch_fac_tipodocumento in('35') THEN '-' ELSE clientes.cli_ruc END) as RucClientes,
		            	(CASE WHEN c.ch_fac_tipodocumento in('35') THEN 'CLIENTES VARIOS' ELSE clientes.cli_razsocial END) as NomCliente,
		            	'-' as DirCliente,
		            	'' as SerieTicke,
		            	'(Vendedor)' as NomVendedo,
		            	'-' as Formapago,
		           	'-' as NomTarjeta,
		            	'-' as nTarjeta,
		            	'-' as Obs,
		            	d.art_codigo AS IdProdExt,
		            	d.nu_fac_cantidad AS Cantiditem,
		            	d.nu_fac_precio as Precioitem,
		            	d.nu_fac_valortotal as Totalitem,
		            	'0' as Bonificaci,
		            	round((d.nu_fac_importeneto),2) as SubtotalVe,
		            	round((d.nu_fac_impuesto1),2) as IGVventa,
		            	d.nu_fac_valortotal as TotalVenta,
		            	(CASE WHEN c.ch_fac_anulado = 'S' THEN '1' ELSE '0' END) as Anulada,
		            	'0' as PAnulTiket,
		            	'-' as nVentaAnul,
		            	'0.18' as pIGVitem,
		            	round((d.nu_fac_impuesto1),2) as IGVitem,
		            	'0' as IdCasoP,
		            	'-' as IdTurno,
		            	'-' as IdManguera,
		            	'-' as SerieProducto,
		            	'' as CadVentaAnticipo,
		            	'' as NomPOS,
		            	d.art_codigo AS codigo--REG36
			FROM
				fac_ta_factura_cabecera c
				LEFT JOIN fac_ta_factura_detalle d ON(c.ch_fac_tipodocumento = d.ch_fac_tipodocumento AND c.ch_fac_seriedocumento = d.ch_fac_seriedocumento AND c.ch_fac_numerodocumento = d.ch_fac_numerodocumento AND c.cli_codigo = d.cli_codigo)
				LEFT JOIN int_clientes clientes ON(clientes.cli_codigo = c.cli_codigo)
			WHERE
				to_char(c.dt_fac_fecha, 'YYYY-MM') = '$Fecha'
				AND c.ch_fac_tipodocumento IN('35','10')
				$condicion;
		";

		if ($sqlca->query($query) < 0) {
			return array();
		}

		$result = array();

		while ($reg = $sqlca->fetchRow()) {
			$result[] = $reg;
		}

		return $result;

	}

    function ActualizarDatosPostrans($Fecha, $tickes_anu, $cod_sucursal, $cod_pecana) {
		global $sqlca;

		$Fecha		= str_replace("-", "", $Fecha);
		$condicion 	= "";

		if ($cod_sucursal != "all")
		    $condicion = "AND c.es = '$cod_sucursal'";

		$query = "
		SELECT
			(CASE WHEN c.td in('B') THEN 'TB' WHEN c.td in('N') THEN 'TN' ELSE 'TFA' END) AS TipoDocExt,
			pcf.rutaprint as SerieVenta,
			c.trans::text as nVenta,
			'N' as Moneda,
			to_char(c.dia,'dd/MM/YYYY') as Fecha,
			to_char(c.dia,'dd/MM/YYYY') as FechaVenc,
			(CASE WHEN (c.fpago = '1') THEN 'T' else 'T' END) as Condicion,
			(SELECT par_valor FROM int_parametros WHERE par_nombre = 'euromax') as IdSucursal,
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
        	art.art_descripcion AS IdProdExt,
			c.cantidad AS Cantiditem,
			c.precio as Precioitem,
			c.importe as Totalitem,
			'0' as Bonificaci,
			round(c.importe - ((c.importe*0.18)/1.18),2) as SubtotalVe,
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
			c.codigo AS codigo,
			trim(c.caja)||c.dia||trim(c.turno)||trim(c.codigo)||trim(c.cantidad::TEXT)||abs(c.importe)||trim(c.ruc)||trim(c.pump)||trim(c.tipo) as iden,
			c.tm AS type--REG38
		FROM
			pos_trans" . $Fecha . " c 
			LEFT JOIN pos_cfg pcf ON(c.caja = pcf.pos)
			LEFT JOIN int_articulos art ON(art.art_codigo = c.codigo)
	        LEFT JOIN ruc ON (c.ruc = ruc.ruc)
		WHERE
			c.td IN('B', 'F', 'N')
            AND c.tipo IN('C', 'M')
			" . $condicion . "
		ORDER BY
			Fecha,
			SerieTicke,
			c.trans;
        ";

		if ($sqlca->query($query) < 0)
			return array();

    	$result = array();
    	$pasa = true;
    	while ($reg = $sqlca->fetchRow()) {
	    	$pasa = true;
	    	for ($i = 0; $i < count($tickes_anu); $i++) {
	        	if ($reg['iden'] === $tickes_anu[$i]['iden'] && $tickes_anu[$i]['estado'] == 'FALTA' && $tickes_anu[$i]['trans_tmp'] == '0') {
			    	$reg['estado'] 					= 'A';
			    	//$tickes_anu[$i]['trans_tmp'] 	= $reg['num_documneto'];
			    	$tickes_anu[$i]['iden']			= 'OK';
					break;
				}
			}
			$result[] = $reg;
		}
		return $result;
	}

    function getTickesAnulados($Fecha, $cod_sucursal) {
	    global $sqlca;

        $Fecha = str_replace("-", "", $Fecha);

        if ($cod_sucursal != "all")
		$condicion = "AND es = '" . $cod_sucursal . "'";

    	$query = "
		SELECT
       		trim(caja)||dia||trim(turno)||trim(codigo)||trim(cantidad::TEXT)||abs(importe)||trim(ruc)||trim(pump)||trim(tipo) AS iden,
			'FALTA' AS estado,
			'0' AS trans_tmp
		FROM
			pos_trans" . $Fecha . "
		WHERE
			tm = 'A'
			" . $condicion . ";
		";

		if ($sqlca->query($query) < 0)
			return array();

		$result = array();

		while ($reg = $sqlca->fetchRow())
			$result[] = $reg;
		return $result;
	}
}

