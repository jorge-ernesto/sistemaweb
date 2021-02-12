<?php

class ValesFacturaModel extends Model {

    	function venta_vales($ano, $mes, $estacion) {
        	global $sqlca;

		$query_stacion	= ($estacion == "TODAS") ? '' : " AND  es='$estacion'";
		$AAAAMM		= $ano . "-" . $mes;
		$dia_ultimo	= $AAAAMM . "-01";
		$fecha_dia	= date("Y-m-d", (mktime(0, 0, 0, $mes, 1, $ano) - 1));

		//echo "La fecha es :" . $fecha_dia;

        	$sql = "
			SELECT 
		            	cli.cli_ruc,
		            	cli.cli_razsocial,
		            	vales.importe_total_vales,
		            	monto.monto_total ,
		            	monto.monto_cancelado,
		            	monto.saldo_inicial,
		            	facturas_liquidadadas.total_facturado
			FROM 
				(SELECT
					cli_codigo,
					cli_razsocial,
					cli_ruc
				FROM
					int_clientes     
				) AS cli

				LEFT JOIN(
						SELECT 
							ch_sucursal,
	                                		ch_cliente,
	                                		SUM(nu_importe) AS importe_total_vales
						FROM
							val_ta_cabecera 
                         			WHERE
							TO_CHAR(dt_fecha,'YYYY-MM') = '$AAAAMM'
						GROUP BY
							ch_sucursal,
							ch_cliente
						ORDER BY
							ch_sucursal,
							ch_cliente
				) AS vales ON (cli.cli_codigo = vales.ch_cliente OR cli.cli_ruc = vales.ch_cliente)

				LEFT JOIN(
						SELECT
							doc_x_cancela.cli_codigo,
							doc_x_cancela.monto_total,
							doc_cancelado.monto_cancelado,
							(doc_x_cancela.monto_total - (CASE WHEN doc_cancelado.monto_cancelado > 0 THEN doc_cancelado.monto_cancelado ELSE 0 END)) AS saldo_inicial
						FROM 
							(SELECT
								cli_codigo,
								SUM(nu_importemovimiento) AS monto_total
							FROM
								ccob_ta_detalle
							WHERE
								dt_fechamovimiento <= '$fecha_dia'  
								AND  ch_tipmovimiento='1'
							GROUP BY
								cli_codigo
							ORDER BY
								cli_codigo) AS doc_x_cancela

							LEFT JOIN(
									SELECT
										cli_codigo,
										SUM(nu_importemovimiento) AS monto_cancelado
									FROM
										ccob_ta_detalle
									WHERE
										dt_fechamovimiento <= '$fecha_dia'
										AND ch_tipmovimiento = '2'
										GROUP BY
											cli_codigo
										ORDER BY
											cli_codigo
							) AS doc_cancelado ON (doc_x_cancela.cli_codigo = doc_cancelado.cli_codigo)
				) AS monto ON (cli.cli_codigo = monto.cli_codigo)

				LEFT JOIN(
						SELECT 
                        				cli_codigo,
                        				SUM(nu_fac_valortotal) AS total_facturado
                    				FROM
							fac_ta_factura_cabecera
						WHERE
							ch_fac_tipodocumento IN('10','35')
							AND TO_CHAR(dt_fac_fecha, 'YYYY-MM') = '$AAAAMM' 
							AND (ch_liquidacion IS NOT NULL AND LENGTH(TRIM(ch_liquidacion)) = 10)
						GROUP BY
							cli_codigo 
				) AS facturas_liquidadadas ON (cli.cli_codigo = facturas_liquidadadas.cli_codigo)

				WHERE

					monto.saldo_inicial::INTEGER > 0 
					OR vales.importe_total_vales::INTEGER > 0 
					OR facturas_liquidadadas.total_facturado::INTEGER > 0;
			";

		echo $sql;

		if ($sqlca->query($sql) < 0)
			return -1;

		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {

			$a = $sqlca->fetchRow();
	
			$cli_ruc		= trim($a['cli_ruc']);
            		$cli_razsocial		= trim($a['cli_razsocial']);
            		$importe_total_vales	= $a['importe_total_vales'];
		    	$saldo_inicial		= $a['saldo_inicial'];
		    	$monto_cancelado	= $a['monto_cancelado'];
		    	$monto_total		= $a['monto_total'];
		    	$total_facturado	= $a['total_facturado'];

		    	$resultado[$cli_ruc]['cli_razsocial']		= $cli_razsocial;
		    	$resultado[$cli_ruc]['importe_total_vales']	= $importe_total_vales;
		    	$resultado[$cli_ruc]['saldo_inicial']		= $saldo_inicial;
		    	$resultado[$cli_ruc]['total_facturado']		= $total_facturado;

		}

		return $resultado;

	}

	function getdescripcion_linea() {
		global $sqlca;

		$sql = "
			SELECT 
				tab_elemento,
				tab_descripcion 
			FROM
				int_tabla_general 
			WHERE
				tab_tabla = '20';
			";

		if ($sqlca->query($sql) < 0)
			return -1;

		$resultado = Array();

        	for ($i = 0; $i < $sqlca->numrows(); $i++) {

            		$a = $sqlca->fetchRow();

            		$tab_elemento					= trim($a['tab_elemento']);
            		$resultado[$tab_elemento]['tab_descripcion']	= trim($a['tab_descripcion']);

		}

		return $resultado;

	}

    	function obtenerEstaciones() {
        	global $sqlca;

        	$sql = "
			SELECT
				ch_almacen,
				ch_nombre_almacen
			FROM
				inv_ta_almacenes
			WHERE
				ch_clase_almacen = '1'
			ORDER BY
				ch_almacen;
			";

		if ($sqlca->query($sql, "_estaciones") < 0)
			return null;

		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows("_estaciones"); $i++) {
			$array = $sqlca->fetchRow("_estaciones");
			$resultado[$array[0]] = $array[0] . " - " . $array[1];
		}

		$resultado['TODAS'] = "Todas las estaciones";

		return $resultado;

	}

	function obtenerDescripcionAlmacen($codigo) {
        	global $sqlca;

        	$sql = "
			SELECT
				TRIM(ch_nombre_almacen)
			FROM
				inv_ta_almacenes
			WHERE
				ch_almacen = '$codigo';
			";
	
		if ($sqlca->query($sql, "_almacenes") < 0)
            		return null;

		$a = $sqlca->fetchRow("_almacenes");

		return $a[0];

	}

	function obtenerTiposFormularios() {
        	global $sqlca;

		$sql = "
			SELECT
				TRIM(tran_codigo) AS tran_codigo,
				TRIM(format_sunat) AS tran_descripcion
			FROM
				inv_tipotransa
			ORDER BY
				tran_codigo;
			";

        	if ($sqlca->query($sql, "_formularios") < 0)
            		return null;

		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows("_formularios"); $i++) {
            		$array = $sqlca->fetchRow("_formularios");
            		$resultado[$array[0]] = $array[1];
		}

        	$resultado['TODOS'] = "Todos los tipos";

		return $resultado;

	}

	function getUltimoDiaMes($elAnio, $elMes) {
        	return date("d", (mktime(0, 0, 0, $elMes - 1, 1, $elAnio) - 1));
	}

}

