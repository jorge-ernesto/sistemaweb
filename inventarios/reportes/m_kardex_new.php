<?php

class KardexActModel extends Model {

	function search($desde, $hasta, $art_desde, $estacion, $linea, $tipoventa) {
        	global $sqlca;

        	list($desde_dia, $desde_mes, $desde_ano) = sscanf($desde, "%2s/%2s/%4s");
        	list($hasta_dia, $hasta_mes, $hasta_ano) = sscanf($hasta, "%2s/%2s/%4s");

		$FechaDiv = explode("/", $desde);
		$FechaIni = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];
		$postrans = "pos_trans".$FechaDiv[2].$FechaDiv[1];
		$FechaDiv = explode("/", $hasta);
		$FechaFin = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];

		if (("pos_trans".$FechaDiv[2].$FechaDiv[1]) != $postrans) {
			return "INVALID_DATE";
		}

        	$saldos = KardexActModel::saldoInicial($desde, $art_desde, $estacion, $linea);
        	$formularios = KardexActModel::obtenerTiposFormularios();

	        $resultado = Array();
        	$anteriores = Array();

        	foreach ($saldos['almacenes'] as $cod_almacen => $almacen) {
            		foreach ($almacen['codigos'] as $codigo => $articulo) {
		        $resultado['almacenes'][$cod_almacen]['articulos'][$codigo]['saldoinicial']['cant_anterior'] = $articulo['stk_stock'];
		        $resultado['almacenes'][$cod_almacen]['articulos'][$codigo]['saldoinicial']['unit_anterior'] = $articulo['stk_costounitario'];
		        $resultado['almacenes'][$cod_almacen]['articulos'][$codigo]['saldoinicial']['costo_total'] = $articulo['stk_costototal'];
		        $resultado['almacenes'][$cod_almacen]['articulos'][$codigo]['saldoinicial']['codigo_CUO'] = $articulo['cod_CUO'];

		        $anteriores[$cod_almacen][$codigo]['cant_anterior'] = $articulo['stk_stock'];
		        $anteriores[$cod_almacen][$codigo]['unit_anterior'] = $articulo['stk_costounitario'];
            		}
       		}

		$sql = "";

		if($tipoventa == "D")
			$sql.="SELECT * FROM (";
		

	        $sql.="
			SELECT
				inv.mov_fecha fecha,
			    	trim(inv.tran_codigo),
			    	inv.mov_numero,
			    	inv.mov_almaorigen,
			    	inv.mov_almadestino,
			    	inv.mov_entidad,
			    	inv.mov_docurefe,
			    	inv.mov_cantidad,
			    	inv.mov_costounitario,
			    	inv.mov_costototal,
			    	inv.mov_costopromedio,
			    	inv.art_codigo codigo,
			    	inv.mov_naturaleza natu,
			    	inv.mov_almacen es,
			    	tab.tab_car_03,
				inv.mov_tipdocuref,
				''::TEXT,
				''::TEXT,
				''::TEXT,
				''::TEXT,
				''::TEXT,
				''::TEXT,
				''::TEXT
			FROM
			    	inv_movialma inv 
			    	LEFT JOIN int_articulos art ON (inv.art_codigo=art.art_codigo)  
			    	LEFT JOIN int_tabla_general tab ON (tab.tab_tabla='08' AND lpad(inv.mov_tipdocuref,6,'0')=tab.tab_elemento) 
			WHERE
				inv.mov_fecha BETWEEN '" . pg_escape_string($desde_ano . "-" . $desde_mes . "-" . $desde_dia) . " 00:00:00' AND '" . pg_escape_string($hasta_ano . "-" . $hasta_mes . "-" . $hasta_dia) . " 23:59:59'";

		if (trim($art_desde) != "")
			$sql .= "AND inv.art_codigo='" . pg_escape_string($art_desde) . "'  ";

        	if (trim($linea) != "")
            		$sql .= "AND art.art_linea='$linea'  ";

       		if ($estacion != "TODAS")
           		$sql .= "AND inv.mov_almacen='" . pg_escape_string($estacion) . "' ";

		$sql.="
			ORDER BY
				inv.mov_almacen,
				inv.art_codigo,
				date_trunc('day',inv.mov_fecha),
				inv.mov_naturaleza
			";

		if($tipoventa == "D"){

		$sql.="
			) AS A UNION (

			SELECT
				pos.dia fecha,
				'12',
				pos.trans::TEXT,
				FIRST(pos.es),
				FIRST(pos.es),
				FIRST(pos.ruc),
				lpad(pos.caja,3,'0')::TEXT,
				SUM(pos.cantidad),
				FIRST(inv.mov_costounitario),
				(SUM(pos.cantidad) * FIRST(inv.mov_costounitario)),
			    	FIRST(inv.mov_costopromedio),
				FIRST(pos.codigo) codigo,
				'3' natu,
				FIRST(pos.es) es,
				'12',
				'',--15
				FIRST(tab.tab_car_03) AS tipo,
				FIRST(fac.ch_fac_seriedocumento) AS serie,
				FIRST(fac.ch_fac_numerodocumento) AS numero,
				FIRST(fac2.ch_fac_seriedocumento) AS serie,
				FIRST(fac2.ch_fac_numerodocumento) AS numero,
				FIRST(cab.ch_liquidacion),
				FIRST(pos.td)
			FROM
				{$postrans} pos
				LEFT JOIN inv_movialma inv ON (inv.mov_fecha::date = pos.dia AND inv.mov_almacen = pos.es AND inv.art_codigo = pos.codigo)
				LEFT JOIN val_ta_cabecera cab ON (pos.cuenta = cab.ch_cliente AND pos.pump = cab.ch_lado AND pos.turno = cab.ch_turno::VARCHAR AND cab.dt_fecha::DATE = pos.dia::DATE AND cab.ch_placa = pos.placa AND cab.ch_caja = pos.caja AND (cab.ch_documento = pos.caja||'-'||pos.trans OR cab.ch_documento = pos.trans::TEXT))
				LEFT JOIN val_ta_complemento_documento fac ON (fac.ch_numeval = cab.ch_documento AND fac.ch_cliente = cab.ch_cliente AND fac.ch_sucursal = cab.ch_sucursal AND fac.dt_fecha = cab.dt_fecha)
				LEFT JOIN fac_ta_factura_cabecera fac2 ON (fac2.ch_liquidacion = cab.ch_liquidacion)
			   	LEFT JOIN int_tabla_general tab ON (tab.tab_tabla='08' AND lpad(fac.ch_fac_tipodocumento,6,'0')=tab.tab_elemento) 
				LEFT JOIN int_articulos art ON (pos.codigo = art.art_codigo)
			WHERE
				inv.tran_codigo IN('25', '45') AND pos.dia BETWEEN to_date('$desde', 'DD/MM/YYYY') and to_date('$hasta', 'DD/MM/YYYY') ";

		if (trim($art_desde) != "")
			$sql .= "AND pos.codigo='" . pg_escape_string($art_desde) . "'  ";

        	if (trim($linea) != "")
            		$sql .= "AND art.art_linea='$linea'  ";

       		if ($estacion != "TODAS")
           		$sql .= "AND pos.es='" . pg_escape_string($estacion) . "' ";

		$sql.="
			GROUP BY
				pos.trans,
				pos.dia,
				pos.caja
			ORDER BY
				pos.dia
			) ORDER BY
				es,
				codigo,
				fecha,
				natu;
		";

		}

		//echo "\n".$sql;

		if ($sqlca->query($sql) < 0)
			return null;

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();

		    	$mov_fecha		= substr($a[0], 0, 19);
			$tran_codigo		= $a[1];
		    	$mov_numero		= $a[2];
		    	$mov_almaorigen		= $a[3];
		    	$mov_almadestino	= $a[4];
		    	$mov_entidad		= $a[5];
		    	$mov_docurefe		= $a[6];
		    	$mov_cantidad		= $a[7];
		    	$mov_costounitario	= $a[8];
		    	$mov_costototal		= $a[9];
		    	$mov_costopromedio	= $a[10];
		    	$art_codigo		= $a[11];
		    	$mov_naturaleza		= $a[12];
		    	$mov_almacen		= $a[13];
		    	$tipodocu		= $a[14];
		    	$tipoopera		= $a[15];
		    	/*$tipodoc		= $a[16];
		    	$seriedoc		= $a[17];
		    	$numerodoc		= $a[18];
		    	$seriedoc2		= $a[19];
		    	$numerodoc2		= $a[20];
		    	$liquidado		= $a[21];
		    	$tickettype		= $a[22];*/


			if($tipoventa == "D" && ($tran_codigo != "45" && $tran_codigo != "25")){

				$tipodocu	= $a[14];
				$seriedocu	= $a[6];
				$numdocu	= $a[2];

				if($tran_codigo != '12'){
					$seriedocu		= substr($a[6], 0, 3);
					$numdocu		= substr($a[6], -7);
				    	$mov_tipdocuref		= $a['mov_tipdocuref'];
				}

			    	if($a[22] == "N"){
					if($a[21] != ''){ // PARA NOTAS DE DESPACHO LIQUIDADAS
					    	$tipodocu	= $a[16];
						if ($a[18] != ''){
						  	$seriedocu = $a[17];
						  	$numdocu = $a[18];
						}else{
						  	$seriedocu = $a[19];
						  	$numdocu = $a[20];
						}
					} else {
						$tipodocu	= $a[14];
						$seriedocu	= $a[6];
						$numdocu	= $a[2];
					}
				}

			    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_fecha'] = $mov_fecha;
			    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['tipodocu'] = $tipodocu;
			    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['seriedocu'] = $seriedocu;
			    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['numdocu'] = $numdocu;
			    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_numero'] = $mov_numero;
			    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['codigo_tipo_tansa'] = $tran_codigo;
			    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_almadestino'] = $mov_almadestino;

				if ($tran_codigo == "18" && $mov_cantidad < 0)//REGULARIZACION CON CANTIDA < 0
					$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['tran_codigo'] = $formularios[16];//MERMAS
				else
					$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['tran_codigo'] = $formularios[$tran_codigo];

				$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['tipoopera'] = $tipoopera;

		   		if ($mov_naturaleza < 3) {

					$anteriores[$mov_almacen][$art_codigo]['cant_anterior'] += $mov_cantidad;
					$cant_actual = $anteriores[$mov_almacen][$art_codigo]['cant_anterior'];
					$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_cant_entrada'] = $mov_cantidad;
					$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_unit_entrada'] = $mov_costounitario;
					$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_cost_entrada'] = $mov_costototal;
					$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['totales']['cant_entrada'] += $mov_cantidad;
					$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['totales']['uni_entrada'] += $mov_costounitario;
					$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['totales']['cost_entrada'] += $mov_costototal;

				} else {

					$anteriores[$mov_almacen][$art_codigo]['cant_anterior'] -= $mov_cantidad;
					$cant_actual = $anteriores[$mov_almacen][$art_codigo]['cant_anterior'];
					$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_cant_salida'] = $mov_cantidad;
					$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_unit_salida'] = $mov_costounitario;
					$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_cost_salida'] = $mov_costototal;
					$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['totales']['cant_salida'] += $mov_cantidad;
					$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['totales']['uni_salida'] += $mov_costounitario;
					$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['totales']['cost_salida'] += $mov_costototal;
				}

			    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_docurefe'] = $mov_docurefe;
			    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_tipdocuref'] = $mov_tipdocuref;

			    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_cant_anterior'] = $anteriores[$mov_almacen][$art_codigo]['cant_anterior'];
			    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_val_ant'] = $anteriores[$mov_almacen][$art_codigo]['unit_anterior'];

			    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_costounitario'] = $mov_costounitario;
			    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_cant_actual'] = $cant_actual;
			    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_val_unit_act'] = $mov_costopromedio;
			    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_total_act'] = $cant_actual * $mov_costopromedio;

			    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['totales']['cantidad_total'] += $cant_actual;
			    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['totales']['uni_total'] += $mov_costounitario;
			    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['totales']['valor_total'] += ($cant_actual * $mov_costopromedio);
			    	$anteriores[$mov_almacen][$art_codigo]['cant_anterior'] = $cant_actual;
			    	$anteriores[$mov_almacen][$art_codigo]['unit_anterior'] = $mov_costounitario;
	
			}else if($tipoventa == "R" && $tran_codigo!= "12"){

				$seriedocu		= substr($a[6], 0, 3);
				$numdocu		= substr($a[6], -7);
			    	$mov_tipdocuref		= $a['mov_tipdocuref'];

				$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_fecha'] = $mov_fecha;
			    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['tipodocu'] = $tipodocu;
			    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['seriedocu'] = $seriedocu;
			    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['numdocu'] = $numdocu;
			    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_numero'] = $mov_numero;
			    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['codigo_tipo_tansa'] = $tran_codigo;
			    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_almadestino'] = $mov_almadestino;

				if ($tran_codigo == "18" && $mov_cantidad < 0)//REGULARIZACION CON CANTIDA < 0
					$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['tran_codigo'] = $formularios[16];//MERMAS
				else
					$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['tran_codigo'] = $formularios[$tran_codigo];

				$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['tipoopera'] = $tipoopera;

		   		if ($mov_naturaleza < 3) {

					$anteriores[$mov_almacen][$art_codigo]['cant_anterior'] += $mov_cantidad;
					$cant_actual = $anteriores[$mov_almacen][$art_codigo]['cant_anterior'];
					$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_cant_entrada'] = $mov_cantidad;
					$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_unit_entrada'] = $mov_costounitario;
					$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_cost_entrada'] = $mov_costototal;
					$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['totales']['cant_entrada'] += $mov_cantidad;
					$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['totales']['uni_entrada'] += $mov_costounitario;
					$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['totales']['cost_entrada'] += $mov_costototal;

				} else {

					$anteriores[$mov_almacen][$art_codigo]['cant_anterior'] -= $mov_cantidad;
					$cant_actual = $anteriores[$mov_almacen][$art_codigo]['cant_anterior'];
					$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_cant_salida'] = $mov_cantidad;
					$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_unit_salida'] = $mov_costounitario;
					$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_cost_salida'] = $mov_costototal;
					$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['totales']['cant_salida'] += $mov_cantidad;
					$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['totales']['uni_salida'] += $mov_costounitario;
					$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['totales']['cost_salida'] += $mov_costototal;
				}

			    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_docurefe'] = $mov_docurefe;
			    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_tipdocuref'] = $mov_tipdocuref;

			    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_cant_anterior'] = $anteriores[$mov_almacen][$art_codigo]['cant_anterior'];
			    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_val_ant'] = $anteriores[$mov_almacen][$art_codigo]['unit_anterior'];

			    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_costounitario'] = $mov_costounitario;
			    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_cant_actual'] = $cant_actual;
			    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_val_unit_act'] = $mov_costopromedio;
			    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_total_act'] = $cant_actual * $mov_costopromedio;

			    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['totales']['cantidad_total'] += $cant_actual;
			    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['totales']['uni_total'] += $mov_costounitario;
			    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['totales']['valor_total'] += ($cant_actual * $mov_costopromedio);
			    	$anteriores[$mov_almacen][$art_codigo]['cant_anterior'] = $cant_actual;
			    	$anteriores[$mov_almacen][$art_codigo]['unit_anterior'] = $mov_costounitario;

			}

		}

		foreach ($resultado['almacenes'] as $ka => $va) {
			foreach ($va['articulos'] as $kb => $vb) {
				if (!isset($vb['movimientos']))
					unset($resultado['almacenes'][$ka]['articulos'][$kb]);
	    		}
		}		

		return $resultado;

	}

	function movialma($desde, $hasta, $art_desde, $estacion, $linea) {
        	global $sqlca;

        	list($desde_dia, $desde_mes, $desde_ano) = sscanf($desde, "%2s/%2s/%4s");
        	list($hasta_dia, $hasta_mes, $hasta_ano) = sscanf($hasta, "%2s/%2s/%4s");

		$sql="
			SELECT
				inv.art_codigo as codigo
			FROM 
				inv_movialma inv 
			    	LEFT JOIN int_articulos art ON (inv.art_codigo=art.art_codigo)  
			    	LEFT JOIN inv_tipotransa tran ON (trim(tran.tran_codigo)= trim(inv.tran_codigo))  
			    	LEFT JOIN int_tabla_general tab ON (tab.tab_tabla='08' AND lpad(inv.mov_tipdocuref,6,'0')=tab.tab_elemento)
			WHERE
				inv.mov_fecha BETWEEN '" . pg_escape_string($desde_ano . "-" . $desde_mes . "-" . $desde_dia) . " 00:00:00' AND '" . pg_escape_string($hasta_ano . "-" . $hasta_mes . "-" . $hasta_dia) . " 23:59:59'";

		if (trim($art_desde) != "")
			$sql .= "AND inv.art_codigo='" . pg_escape_string($art_desde) . "'  ";

		if (trim($linea) != "")
			$sql .= "AND art.art_linea='$linea'  ";

		if ($estacion != "TODAS")
			$sql .= "AND inv.mov_almacen='" . pg_escape_string($estacion) . "' ";

		if ($sqlca->query($sql) < 0)
			return -1;

		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$resultado[$i]['codigo'] = $a[0];
		}

		return $resultado;

	}

    function saldosProductos($desde, $art_desde, $estacion, $linea) {
        global $sqlca;

        list($dia, $mes, $ano) = sscanf($desde, "%2s/%2s/%4s");

        if ($mes == 1) {
            $mes = 12;
            $ano--;
        } else {
            $mes--;
        }

        if (strlen($mes) == 1)
            $mes = "0" . $mes;

        $sql = "SELECT
			    	sa.stk_almacen as almacen,
			    	sa.art_codigo as codigo,
			    	sa.stk_stock" . $mes . " as stock,
			    	sa.stk_costo" . $mes . " as costo,
				round (sa.stk_stock" . $mes . " * sa.stk_costo" . $mes . ",4) total
			FROM
			    	inv_saldoalma sa 
			    	LEFT JOIN int_articulos art ON (sa.art_codigo=art.art_codigo)  
			WHERE
				sa.stk_stock" . $mes . " > 0
				AND sa.stk_periodo='$ano' ";

        if (trim($art_desde) != "")
            $sql .= "AND sa.art_codigo='$art_desde' ";

        if (trim($linea) != "")
            $sql .= "AND art.art_linea='$linea' ";

        if ($estacion != "TODAS")
            $sql .= " AND sa.stk_almacen='$estacion' ";

        $sql .= "ORDER BY
			    	sa.stk_periodo,
			    	sa.stk_almacen,
			    	sa.art_codigo;";

       //echo "\n\nSaldo Inicial:" . $sql;

        if ($sqlca->query($sql) < 0)
            return -1;

        $resultado = Array();

        for ($i = 0; $i < $sqlca->numrows(); $i++) {
            $a = $sqlca->fetchRow();

            $resultado[$i]['almacen'] = $a[0];
            $resultado[$i]['codigo'] = $a[1];
            $resultado[$i]['stock'] = $a[2];
            $resultado[$i]['costo'] = $a[3];
            $resultado[$i]['total'] = $a[4];
        }

        return $resultado;
    }

    function saldoInicial($desde, $art_desde, $estacion, $linea) {
        global $sqlca;

        list($dia, $mes, $ano) = sscanf($desde, "%2s/%2s/%4s");

        if ($mes == 1) {
            $mes = 12;
            $ano--;
        } else {
            $mes--;
        }

        if (strlen($mes) == 1)
            $mes = "0" . $mes;

        $sql = "SELECT
			    	sa.stk_almacen,
			    	sa.art_codigo,
			    	sa.stk_stock" . $mes . ",
			    	sa.stk_costo" . $mes . ",
                                sa.stk_periodo
			FROM
			    	inv_saldoalma sa 
			    	LEFT JOIN int_articulos art ON (sa.art_codigo=art.art_codigo)  
			WHERE
				sa.stk_periodo='$ano' ";

        if (trim($art_desde) != "")
            $sql .= "AND sa.art_codigo='$art_desde' ";

        if (trim($linea) != "")
            $sql .= "AND art.art_linea='$linea' ";

        if ($estacion != "TODAS")
            $sql .= " AND sa.stk_almacen='$estacion' ";

        $sql .= "ORDER BY
			    	sa.stk_periodo,
			    	sa.stk_almacen,
			    	sa.art_codigo;";

        //echo "\n\nSaldo Inicial:".$sql;

        if ($sqlca->query($sql) < 0)
            return -1;

        $resultado = Array();

        for ($i = 0; $i < $sqlca->numrows(); $i++) {
            $a = $sqlca->fetchRow();

            $stk_almacen = $a[0];
            $art_codigo = $a[1];
            $stk_stock = $a[2];
            $stk_costo = $a[3];

            $resultado['almacenes'][$stk_almacen]['codigos'][$art_codigo]['stk_stock'] = $stk_stock;
            $resultado['almacenes'][$stk_almacen]['codigos'][$art_codigo]['stk_costopromedio'] = $stk_costo;
            $resultado['almacenes'][$stk_almacen]['codigos'][$art_codigo]['stk_costounitario'] = $stk_costo;
            $resultado['almacenes'][$stk_almacen]['codigos'][$art_codigo]['stk_costototal'] = $stk_stock * $stk_costo;
            $resultado['almacenes'][$stk_almacen]['codigos'][$art_codigo]['cod_CUO'] = '' . $stk_almacen . '' . $art_codigo . '' . $mes;
        }

        if ($mes == 12) {
            $mes = 1;
            $ano++;
        } else {
            $mes++;
        }

        if ($dia > 1) {
            $sql = "SELECT
					inv.mov_cantidad,
					inv.mov_costototal,
					inv.mov_costopromedio,
					inv.mov_costounitario,
					inv.mov_naturaleza,
					inv.mov_almacen,
					inv.art_codigo
			    	FROM
					inv_movialma inv 
					LEFT JOIN int_articulos art ON (inv.art_codigo=art.art_codigo) 
			   	WHERE
				    	inv.mov_fecha BETWEEN '" . ($ano . "-" . $mes . "-01") . " 00:00:00' AND '" . ($ano . "-" . $mes . "-" . ($dia - 1)) . " 23:59:59' ";

            if (trim($art_desde) != "")
                $sql .= "AND inv.art_codigo='" . pg_escape_string($art_desde) . "'  ";

            if (trim($linea) != "")
                $sql .= "AND art.art_linea='$linea'  ";

            if ($estacion != "TODAS")
                $sql .= "AND inv.mov_almacen='" . pg_escape_string($estacion) . "' ";

            $sql .= " ORDER BY
					inv.mov_almacen,
					inv.art_codigo,
					inv.mov_fecha;";

            //echo "\n\nSaldo Inicial 2: ".$sql;

            if ($sqlca->query($sql) < 0)
                return $resultado;

            for ($i = 0; $i < $sqlca->numrows(); $i++) {
                $a = $sqlca->fetchRow();

                $mov_cantidad = $a[0];
                $mov_costototal = $a[1];
                $mov_costopromedio = $a[2];
                $mov_costounitario = $a[3];
                $mov_naturaleza = $a[4];
                $mov_almacen = $a[5];
                $art_codigo = $a[6];

                if ($mov_naturaleza < 3) {

                    $resultado['almacenes'][$mov_almacen]['codigos'][$art_codigo]['stk_stock'] += $mov_cantidad;
                    $resultado['almacenes'][$mov_almacen]['codigos'][$art_codigo]['stk_costounitario'] = $mov_costounitario;
                } else {

                    $resultado['almacenes'][$mov_almacen]['codigos'][$art_codigo]['stk_stock'] -= $mov_cantidad;
                }
                $resultado['almacenes'][$mov_almacen]['codigos'][$art_codigo]['stk_costounitario'] = $mov_costounitario;
                $resultado['almacenes'][$mov_almacen]['codigos'][$art_codigo]['stk_costopromedio'] = $mov_costopromedio;
                $resultado['almacenes'][$mov_almacen]['codigos'][$art_codigo]['stk_costototal'] = $resultado['almacenes'][$mov_almacen]['codigos'][$art_codigo]['stk_stock'] * $resultado['almacenes'][$mov_almacen]['codigos'][$art_codigo]['stk_costopromedio'];

                $resultado['almacenes'][$mov_almacen]['codigos'][$art_codigo]['cod_CUO'] = '' . $mov_almacen . '' . $art_codigo . '' . $mes;
            }
        }

        return $resultado;
    }

    function obtenerEstaciones() {
        global $sqlca;

        $sql = "SELECT ch_almacen, ch_nombre_almacen FROM inv_ta_almacenes WHERE ch_clase_almacen='1' ORDER BY ch_almacen;";
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

        $sql = "SELECT trim(ch_nombre_almacen) FROM inv_ta_almacenes WHERE ch_almacen='$codigo';";
        if ($sqlca->query($sql, "_almacenes") < 0)
            return null;

        $a = $sqlca->fetchRow("_almacenes");
        return $a[0];
    }

    function obtenerTiposFormularios() {
        global $sqlca;

        $sql = "SELECT  trim(tran_codigo) as tran_codigo,trim(format_sunat) as tran_descripcion FROM inv_tipotransa ORDER BY tran_codigo;";
        if ($sqlca->query($sql, "_formularios") < 0)
            return null;

        $resultado = Array();
        for ($i = 0; $i < $sqlca->numrows("_formularios"); $i++) {
            $array = $sqlca->fetchRow("_formularios");
            $resultado[$array[0]] = $array[1];
            // $resultado[$array[0]] = $array[0] . " - " . $array[1];
        }

        $resultado['TODOS'] = "Todos los tipos";
        return $resultado;
    }

    function obtenerProveedor($codigo) {
        global $sqlca;

        $sql = "SELECT pro_razsocial FROM int_proveedores WHERE pro_codigo='" . pg_escape_string($codigo) . "';";
        if ($sqlca->query($sql, "_proveedor") < 0)
            return null;

        $a = $sqlca->fetchRow("_proveedor");
        return $a[0];
    }

    function obtenerCliente($codigo) {
        global $sqlca;

        $sql = "SELECT cli_razsocial FROM int_clientes WHERE cli_codigo='" . pg_escape_string($codigo) . "';";
        if ($sqlca->query($sql, "_cliente") < 0)
            return null;

        $a = $sqlca->fetchRow("_cliente");
        return $a[0];
    }

    function obtenerDescripcion($codigo) {
        global $sqlca;

        $sql = "SELECT art_descripcion FROM int_articulos WHERE art_codigo='" . pg_escape_string($codigo) . "';";
        if ($sqlca->query($sql, "_articulo") < 0)
            return null;

        $a = $sqlca->fetchRow("_articulo");
        return $a[0];
    }

    function datosEmpresa() {
        global $sqlca;

        $sql = "SELECT p1.par_valor, p2.par_valor, p3.par_valor FROM int_parametros p1, int_parametros p2, int_parametros p3 WHERE p1.par_nombre='razsocial' and p2.par_nombre='ruc' and p3.par_nombre='dires';";
        if ($sqlca->query($sql) < 0)
            return null;

        $res = Array();
        $a = $sqlca->fetchRow();
        $res['razsocial'] = $a[0];
        $res['ruc'] = $a[1];
        $res['direccion'] = $a[2];

        return $res;
    }

    function anexo_sunat() {
        global $sqlca;

        $sql = "SELECT par_valor FROM int_parametros  WHERE par_nombre='anexo_sunat';";
        if ($sqlca->query($sql) < 0)
            return "9999";

        $res = Array();
        $a = $sqlca->fetchRow();
        $anexo = "9999";
        if (!empty($a['par_valor'])) {
            $anexo = $a['par_valor'];
        }


        return $anexo;
    }

    function unidadMedida($codigo) {
        global $sqlca;

        $sql = "SELECT
                                tab_car_03 ||'-'|| tab_car_04
			FROM 
				int_tabla_general tab 
				LEFT JOIN int_articulos art ON (tab.tab_tabla='34' AND trim(tab.tab_elemento)=trim(art.art_unidad)) 
			WHERE 
				trim(art.art_codigo)='" . trim($codigo) . "';";

        if ($sqlca->query($sql) < 0)
            return null;
        $a = $sqlca->fetchRow();

        return split("-", $a[0]);
    }

    function unidadMedidaExcel($codigo) {
        global $sqlca;

        $sql = "SELECT
                                tab_car_03
			FROM 
				int_tabla_general tab 
				LEFT JOIN int_articulos art ON (tab.tab_tabla='34' AND trim(tab.tab_elemento)=trim(art.art_unidad)) 
			WHERE 
				trim(art.art_codigo)='" . trim($codigo) . "';";

        if ($sqlca->query($sql) < 0)
            return null;
        $a = $sqlca->fetchRow();
        return $a[0];
    }


}

