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

    //piero35
    function ActualizarDatosFacturas($Fecha) {
        global $sqlca;


        $query = "SELECT  
                            trim(c.ch_fac_seriedocumento)||'-'||c.ch_fac_numerodocumento::text as id,
                            trim(c.ch_fac_seriedocumento) as serie_documento,
                            c.ch_fac_numerodocumento::text as num_documneto,
                            to_char(c.dt_fac_fecha,'dd/MM/YYYY') as fecha_emision,
                            to_char(c.dt_fac_fecha,'HH12:MI:SS') as hora_emision,
                            'Sistema' as usuario,
                            c.cli_codigo,
                            clientes.ruc,
                            clientes.rs,
                            'S' as moneda,
                            '98' as sede_venta,--Sede Náutica - Grifo
                            '004' as alamcen,--ALM. SERVICIOS
                             case when c.ch_fac_anulado='S' then
                            'A'
                            else 'E'
                            end as estado,
                            d.art_codigo::text  as articulo,
                            d.nu_fac_cantidad  as cantidad,
                            d.nu_fac_precio as precio,
                            '0' as descuento,
                            d.nu_fac_valortotal as importe  ,
                            case when (c.ch_fac_forma_pago='01') then
                            'EF'
                            else
                            'ERR'
                            end as medio_pago,
                            'S' as moneda_credito,
                            d.nu_fac_valortotal as   importe_credito,
                            '000000' as cuenta

            FROM fac_ta_factura_cabecera c
            LEFT JOIN fac_ta_factura_detalle d 
            ON    c.ch_fac_tipodocumento=d.ch_fac_tipodocumento 
            AND   c.ch_fac_seriedocumento=d.ch_fac_seriedocumento 
            AND   c.ch_fac_numerodocumento=d.ch_fac_numerodocumento
            AND   c.cli_codigo=d.cli_codigo
            LEFT JOIN 
            ((SELECT  cl.cli_ruc as ruc,cl.cli_rsocialbreve as rs,cl.cli_codigo  FROM  int_clientes cl )
            UNION
            (SELECT   cl.ruc as ruc ,cl.razsocial as rs,cl.ruc as cli_codigo   FROM  ruc cl  )
             )  clientes   on (c.cli_codigo=clientes.ruc or c.cli_codigo=clientes.cli_codigo) 
            WHERE   to_char(c.dt_fac_fecha,'YYYY-MM')= '$Fecha' and   c.ch_fac_tipodocumento in('35','10') ;

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

	function ActualizarDatosPostrans($Fecha, $tickes_anu) {
        	global $sqlca;

		$Fecha = str_replace("-", "", $Fecha);

		$query = "
				SELECT  
				        c.caja||'-'||c.trans as id,
				        pcf.nroserie as serie_documento,
				        c.trans as num_documneto,
				        to_char(c.dia,'dd/MM/YYYY') as fecha_emision,
				        to_char(c.fecha,'HH12:MI:SS') as hora_emision,
				        'Sistema' as usuario,
				        c.ruc,
				        'S' as moneda,
				        '98' as sede_venta,
				        '004' as alamcen,
				        (CASE WHEN tm = 'A' THEN 'A' ELSE 'E' END) AS estado,
				        c.codigo as articulo,
				        c.cantidad,
				        c.precio,
				        '0' as descuento,
				        c.importe  ,
				        (CASE WHEN (c.fpago = '1') THEN 'EF' ELSE 'VI' END) AS medio_pago,
				        'S' as moneda_credito,
				        c.importe as importe_credito,
				        c.cuenta,
				        trim(c.caja)||c.dia||trim(c.turno)||trim(c.codigo)||trim(abs(c.cantidad)::TEXT)||abs(c.importe)||trim(c.ruc)||trim(c.pump)||trim(c.tipo) AS iden
		        	FROM
					pos_trans$Fecha c 
		        		LEFT JOIN pos_cfg pcf ON(c.caja = pcf.pos)
		        		LEFT JOIN 
					((SELECT cl.cli_ruc AS ruc, cl.cli_rsocialbreve AS rs FROM int_clientes cl)
					UNION
					(SELECT cl.ruc AS ruc, cl.razsocial as rs FROM ruc cl)) clientes ON(c.ruc = clientes.ruc)
		        	WHERE
					c.td IN('B','F')
					AND c.tipo IN('C','M')
					AND c.tm NOT IN('A')
				ORDER BY
					c.caja,
					c.trans;
		";

		/*echo "<pre>";
print_r($query);
		echo "</pre>";*/

		if($sqlca->query($query) < 0) {
			return array();
		}

        	$result = array();
        	$pasa = true;

        	while ($reg = $sqlca->fetchRow()) {

		    	$pasa = true;
		    
		    	for ($i=0;$i<count($tickes_anu);$i++){

				if($reg['iden'] === $tickes_anu[$i]['iden'] && $tickes_anu[$i]['estado'] == 'FALTA' && $tickes_anu[$i]['trans_tmp'] == '0') {

					$reg['estado']			= 'A';
					$tickes_anu[$i]['trans_tmp']	= $reg['num_documneto'];
					$tickes_anu[$i]['iden']		= 'OK';
				  
					//break;

				}
		    	}

		        $result[] = $reg;

		}

        	return $result;

	}

	function getTickesAnulados($Fecha) {
	        global $sqlca;

	        $Fecha = str_replace("-", "", $Fecha);

		$query = "
				SELECT 
					trim(caja)||dia||trim(turno)||trim(codigo)||trim(abs(cantidad)::TEXT)||abs(importe)||trim(ruc)||trim(pump)||trim(tipo) as iden,
					'FALTA' as estado,
					'0'as trans_tmp
				FROM
					pos_trans$Fecha
				WHERE
					tm = 'A';
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

}

