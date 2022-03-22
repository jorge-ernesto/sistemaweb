<?php

class AfericionesModel extends Model { // BOLETA A AFERICION

	function Paginacion($desde, $hasta, $pp, $pagina) {
		global $sqlca;
	    
		$query ="
				SELECT
					caja,
					trans,
					dia,
					turno,
					fecha,
					pump,
					codigo,
					cantidad,
					precio,
					importe,
					veloc,
					lineas,
					responsabl,
					es
				FROM
					pos_ta_afericiones ";

		if($desde != ''){
			$query .= "WHERE dia BETWEEN to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')";
		}

		$query .= "		ORDER BY dia desc, turno, caja, trans ";

		echo "\n\n PRINCIPAL COMBUSTIBLE: \n\n".$query;

		$resultado_1 = $sqlca->query($query);
		$numrows = $sqlca->numrows();

		$paginador = new paginador($numrows,$pp, $pagina);
	
		$listado2['partir'] 		= $paginador->partir();
		$listado2['fin'] 		= $paginador->fin();
		$listado2['numero_paginas'] 	= $paginador->numero_paginas();
		$listado2['pagina_previa'] 	= $paginador->pagina_previa();
		$listado2['pagina_siguiente'] 	= $paginador->pagina_siguiente();
		$listado2['pp'] 		= $paginador->pp;
		$listado2['paginas'] 		= $paginador->paginas();
		$listado2['primera_pagina'] 	= $paginador->primera_pagina();
		$listado2['ultima_pagina'] 	= $paginador->ultima_pagina();

		$query .= " LIMIT " . pg_escape_string($pp) . " ";
		$query .= " OFFSET " . pg_escape_string($paginador->partir());
		
		//echo $query;

		if ($sqlca->query($query) <= 0)
			return $sqlca->get_error();
	    
		$listado[] = array();
		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$resultado[$i]['caja'] 		= $a[0];
			$resultado[$i]['trans'] 	= $a[1];
			$resultado[$i]['dia'] 		= $a[2];
			$resultado[$i]['turno'] 	= $a[3];
			$resultado[$i]['fecha'] 	= $a[4];
			$resultado[$i]['pump'] 		= $a[5];
			$resultado[$i]['codigo'] 	= $a[6];
			$resultado[$i]['cantidad'] 	= $a[7];
			$resultado[$i]['precio'] 	= $a[8];
			$resultado[$i]['importe'] 	= $a[9];
			$resultado[$i]['veloc'] 	= $a[10];
			$resultado[$i]['lineas'] 	= $a[11];
			$resultado[$i]['responsabl'] 	= $a[12];
			$resultado[$i]['es'] 	        = $a[13];
		}

		//$sql = "COMMIT";
		//$sqlca->query($sql);

		$listado['datos']      = $resultado;        
		$listado['paginacion'] = $listado2;

		return $listado;
  	}

	function ingresarAfericion($fecha, $ticket, $caja, $usuario) {
		global $sqlca;

		$fec = explode("/",$fecha);
		$dia = $fec[2]."-". $fec[1]."-".$fec[0];
		$diabusqueda = $dia;
		//error_log('dia: '.$dia);
		$query1 = "SELECT
 FIRST(es) es,
 FIRST(caja) caja,
 FIRST(dia) dia,
 FIRST(turno) turno,
 FIRST(pump) pump, --LADO
 FIRST(codigo) codigo, --CODIGO
 SUM(cantidad) cantidad,
 SUM(precio) precio,
 SUM(igv) igv,
 SUM(importe) importe,
 FIRST(trans) trans
FROM
 pos_trans".$fec[2].$fec[1]."
WHERE
 tipo = 'C'
 AND caja = '".$caja."'
 AND trans = ".$ticket."
 AND dia = '".$dia."';";

 		// error_log('query1: '.$query1);
		if ($sqlca->query($query1) < 0) {
			return -1;
			//return false;
		}

		if ($sqlca->numrows() == 0) {
			return 0;
		}

		if ($sqlca->numrows() > 1) {
			return 2;
		}

		$resultado = Array();
		
		$a = $sqlca->fetchRow();

		if (trim($a['trans']) == '' && trim($a['dia']) == '' && trim($a['caja']) == '') {
			//error_log('if de vacios');
			return 0;
		}

		$es = $a['es'];
		$caja = $a['caja'];
		$dia = $a['dia'];
		$turno = $a['turno'];
		$pump = $a['pump']; //LADO
		$codigo	= $a['codigo']; //CODIGO
		$cantidad = $a['cantidad'];
		$precio	= $a['precio'];
		$igv = $a['igv'];
		$importe = $a['importe'];
		$trans = $a['trans'];

		$dat = AfericionesModel::verificaAfericion($dia, $trans, $caja);
		// error_log('$dat: '.$dat);
		if ($dat == 1) {
			return 3;
		} else if ($dat == -1) {
			return $dat;
		}

		if ($sqlca->query('BEGIN;') < 0) {
			return -1;
		}

		$query2 = "INSERT INTO pos_ta_afericiones (
 es,
 caja,
 dia,
 turno,
 pump,
 veloc,
 lineas,
 codigo,
 cantidad,
 precio,
 igv,
 importe,
 responsabl,
 fecha,
 trans
) VALUES (
 '".trim($es)."', 
 '".trim($caja)."', 
 '".trim($dia)."', 
 '".trim($turno)."', 
 '".trim($pump)."', 
 'L',
 0,
 '".trim($codigo)."', 
 ".$cantidad.", 
 ".$precio.", 
 ".$igv.", 
 ".$importe.", 
 '".$usuario."',  
 now(),
".$trans.");";

		// error_log("query2: $query2");
		//echo "-- insert afericion: ".$query2.' --';
		$result_insert = $sqlca->query($query2);
		//error_log("result_insert: $result_insert");
		if ($result_insert != -1) {
			/*
			$actualizarParte = AfericionesModel::actualizarParte($dia); //ACTUALIZAR PARTE
			//error_log('actualizarParte: '.$actualizarParte);
			if ($actualizarParte == -1) {
				$sqlca->query('ROLLBACK;');
				return -1;
			}
			*/

			$actualizarParteAfericionIngresada = AfericionesModel::actualizarParteAfericionIngresada($es, $diabusqueda, $codigo, $pump); //ACTUALIZAR PARTE DE AFERICION INGRESADA
			error_log('actualizarParteAfericionIngresada: '.$actualizarParteAfericionIngresada);
			if ($actualizarParteAfericionIngresada < 0) {
				$sqlca->query('ROLLBACK;');
				return -1;
			}
		} else {
			$sqlca->query('ROLLBACK;');
			return -1;
		}
		//$sqlca->query('ROLLBACK;');
		$sqlca->query('COMMIT;');
		return 1;
	} 

	function eliminarAfericion($trans, $es, $dia, $caja, $codigo, $pump){
		global $sqlca;

		$query = "DELETE FROM pos_ta_afericiones WHERE trans=".$trans." AND dia ='".$dia."' AND caja ='".$caja."';";
		$sqlca->query($query);
		AfericionesModel::actualizarParteAfericionIngresada($es, $dia, $codigo, $pump); //ACTUALIZAR PARTE DE AFERICION INGRESADA
		return 'OK';

 	}

	function verificaAfericion($dia, $ticket, $caja) {
		global $sqlca;

		$query1 = "SELECT 1
FROM pos_ta_afericiones
WHERE
 trans = {$ticket}
 AND dia = '".$dia."'
 AND caja = '".$caja."';";

 		error_log('verificaAfericion: '.$query1);

		if ($sqlca->query($query1) < 0)	
			return -1;

		if ($sqlca->numrows() >= 1) 
			return 1 ;// si ya se ha ingresado la afericion
		else 
			return 0;
	} 

	function actualizarParte($dia) {
		error_log('actualizarParte');
		error_log($dia);
		
		global $sqlca;

		$query3 = "SELECT da_fecha FROM pos_aprosys WHERE ch_poscd = 'A';";
		if ($sqlca->query($query3) < 0) {
			return -1;
		}

		$a = $sqlca->fetchRow();
		$actual = $a['da_fecha'];

		if ($dia != $actual) {
			$query 	= "DELETE FROM comb_ta_contometros WHERE dt_fechaparte = '$dia' AND ch_usuario = 'AUTO';";
			$del_comb_ta_contometros = $sqlca->query($query);
			// error_log('del_comb_ta_contometros: '.$del_comb_ta_contometros);
			if ($del_comb_ta_contometros == -1) {
				return $del_comb_ta_contometros;
			} else {
				$query3 = "DELETE FROM inv_movialma WHERE date(mov_fecha) = '$dia' AND tran_codigo IN ('23','24','25');";
				$del_inv_movialma = $sqlca->query($query3);
				// error_log('del_inv_movialma: '.$del_inv_movialma);
				
				if ($del_inv_movialma == -1) {
					return $del_inv_movialma;
				} else {
					$query2 = "SELECT combex_fn_contometros_auto('$dia');";
					// error_log("SELECT combex_fn_contometros_auto('$dia');");
					$sel_combex_fn_contometros_auto = $sqlca->query($query2);
					// error_log('sel_combex_fn_contometros_auto: '.$sel_combex_fn_contometros_auto);
					if ($sel_combex_fn_contometros_auto < 1) {
						return -1;
					} 
				}
			}
		}
		return 1;
	}

	function actualizarParteAfericionIngresada($es, $dia, $codigo, $pump){
		error_log('actualizarParteAfericionIngresada');
		error_log(json_encode(array($dia, $codigo, $pump)));

		global $sqlca;

		/****************************************************************** AFERICIONES EN COMB_TA_CONTOMETROS ******************************************************************/
		//ENCONTRAMOS SURTIDOR POR CODIGO, LADO, ALMACEN
		$query1 = "SELECT * FROM comb_ta_surtidores WHERE TRIM(ch_codigocombustible) = '".TRIM($codigo)."' AND (ch_numerolado = '".$pump."' OR ch_numerolado = '".intval($pump)."') AND TRIM(ch_sucursal) = '".TRIM($es)."';";

		error_log('query1: '.$query1);
		if ($sqlca->query($query1) < 0) {
			return -1;
		}

		if ($sqlca->numrows() == 0 || $sqlca->numrows() > 1) { //No hay surtidor, o hay mas de uno
			return -2; 
		}
		
		$a = $sqlca->fetchRow();

		if (trim($a['ch_surtidor']) == '') { //No hay surtidor
			return -2; 
		}

		$ch_surtidor = $a['ch_surtidor'];

		//OBTENEMOS TOTAL DE AFERICIONES POR DIA, CODIGO, LADO, ALMACEN
		$query2 = "SELECT 
						COALESCE(sum(cantidad),0) / 5
					FROM
						pos_ta_afericiones
					WHERE
						dia = '".$dia."'
						AND to_number(pump,'00') = '".$pump."'
						AND TRIM(codigo)         = '".TRIM($codigo)."'
						AND TRIM(es)             = '".TRIM($es)."';
					";

		error_log('query2: '.$query2);
		if ($sqlca->query($query2) < 0) {
			return -1;
		}

		if ($sqlca->numrows() == 0 || $sqlca->numrows() > 1) { //No hay afericion, o hay mas de uno
			return -3; 
		}

		$a = $sqlca->fetchRow();
		$TmpAfericion = $a[0];
		
		//APLICAMOS INFORMACION DE AFERICIONES DIRECTAMENTE AL PARTE EXISTENTE POR DIA, CODIGO, SURTIDOR, ALMACEN
		$query3 = "UPDATE comb_ta_contometros SET nu_afericionveces_x_5 = '".$TmpAfericion."' WHERE dt_fechaparte = '".$dia."' AND TRIM(ch_codigocombustible) = '".TRIM($codigo)."' AND ch_surtidor = '".$ch_surtidor."' AND TRIM(ch_sucursal) = '".TRIM($es)."';";
		
		error_log('query3: '.$query3);
		$result_update = $sqlca->query($query3);
		if ($result_update != -1) {
			//Actualizacion correcta
		} else {
			$sqlca->query('ROLLBACK;');
			return -1;
		}

		/****************************************************************** AFERICIONES EN INV_MOVIALMA ******************************************************************/
		//OBTENEMOS TOTAL DE AFERICIONES POR DIA, CODIGO, ALMACEN
		$queryAfe = "SELECT 
						COALESCE(sum(cantidad),0) / 5
					FROM
						pos_ta_afericiones
					WHERE
						dia = '".$dia."'
						AND TRIM(codigo) = '".TRIM($codigo)."'
						AND TRIM(es)     = '".TRIM($es)."';
					";

		error_log('queryAfe: '.$queryAfe);
		if ($sqlca->query($queryAfe) < 0) {
			return -1;
		}

		if ($sqlca->numrows() == 0 || $sqlca->numrows() > 1) { //No hay afericion, o hay mas de uno
			return -3; 
		}

		$a = $sqlca->fetchRow();
		$totalAfericion = $a[0];
		
		//SI LA SUMA CANTIDAD DE AFERICIOENS ES 0, ENTONCES NO HAY MOVIMIENTOS DE INVENTARIO
		if ( $totalAfericion == 0 ) { 
			$querydel = "DELETE FROM inv_movialma WHERE date(mov_fecha) = '$dia' AND TRIM(art_codigo) = '".TRIM($codigo)."' AND TRIM(mov_almacen) = '".TRIM($es)."' AND tran_codigo IN ('23');";
			$del_inv_movialma = $sqlca->query($querydel);
			// error_log('del_inv_movialma: '.$del_inv_movialma);			
			if ($del_inv_movialma != -1) {
				$sqlca->query('COMMIT;');
				return 1;				
			} else {
				$sqlca->query('ROLLBACK;');
				return -1;
			}				
		}
		
		//VARIABLES DIA, CODIGO, ALMACEN
		$dia 			 = $dia;
		$cod_combustible = TRIM($codigo);
		$cod_almacen     = TRIM($es);

		//OBTENEMOS MES Y AÑO
		$query4 = "select to_char('$dia'::DATE,'mm'), to_char('$dia'::DATE,'yyyy');";
		
		error_log('query4: '.$query4);
		if ($sqlca->query($query4) < 0) {
			return -1;
		}

		$a    = $sqlca->fetchRow();
		$mes  = $a[0];
		$year = $a[1];
		
		if (TRIM($mes) == '' || TRIM($year) == '') //No hay mes o año
			return -1;

		//ENCONTRAMOS ALMACEN ORIGEN, ALMACEN DESTINO, NATURALEZA DE TIPO DE MOVIMIENTO 23
		$query5 = "select inv_tipotransa.tran_origen , inv_tipotransa.tran_destino , inv_tipotransa.tran_naturaleza 
					from inv_tipotransa where inv_tipotransa.tran_codigo='23';";
		
		error_log('query5: '.$query5);
		if ($sqlca->query($query5) < 0) {
			return -1;
		}
  
		$sqlca->query($query5);
		$a               = $sqlca->fetchRow();
		$tran_origen     = $a['tran_origen'];
		$tran_destino    = $a['tran_destino'];
		$tran_naturaleza = $a['tran_naturaleza'];
		$tran_destino    = $cod_almacen;

		if (TRIM($tran_origen) == '' || TRIM($tran_destino) == '' || TRIM($tran_naturaleza) == '')  //No hay almacenes
			return -1;

		//OBTENEMOS COSTO PROMEDIO
		$query6 = "select stk_costo$mes as stk_costo ,stk_stock$mes as stk_stock from inv_saldoalma  
					where stk_almacen=trim('$cod_almacen')  
			 		and stk_periodo='$year' and art_codigo='$codigo';";
		
		error_log('query6: '.$query6);
		if ($sqlca->query($query6) < 0) {
			return -1;
		}

		$sqlca->query($query6);
		$a         = $sqlca->fetchRow();
		$stk_costo = $a['stk_costo']; 
		$stk_stock = $a['stk_stock'];

		if (TRIM($stk_costo) == '') //No hay costo promedio por mes, año y producto
			return -1;

		//OBTENEMOS MOV_NUMERO DE COMB_TA_CONTOMETROS, BUSCAMOS POR DIA, CODIGO, SURTIDOR, ALMACEN
		$query7 = "SELECT ch_numeroparte FROM comb_ta_contometros WHERE dt_fechaparte = '".$dia."' AND TRIM(ch_codigocombustible) = '".TRIM($codigo)."' AND ch_surtidor = '".$ch_surtidor."' AND TRIM(ch_sucursal) = '".TRIM($es)."';";
		
		error_log('query7: '.$query7);
		if ($sqlca->query($query7) < 0) {
			return -1;
		}
		
		$sqlca->query($query7);
		$a               = $sqlca->fetchRow();
		$num_parte       = $a['ch_numeroparte'];
		$fecha           = $dia;
		$cod_combustible = $cod_combustible;
		$cod_almacen     = $cod_almacen; 
		
		if (TRIM($num_parte) == '' || TRIM($fecha) == '')
			return -1;

		//VERIFICAMOS QUE EXISTA EN INV_MOVIALMA
		$query8 = "SELECT count(*) AS existe FROM inv_movialma 
				   WHERE TRIM(mov_numero) = '".TRIM($num_parte)."' AND DATE(mov_fecha) = '$fecha' AND TRIM(art_codigo) = '".TRIM($cod_combustible)."' AND TRIM(mov_almacen) = '".TRIM($cod_almacen)."' AND tran_codigo = '23';";
		
		error_log('query8: '.$query8);
		if ($sqlca->query($query8) < 0) {
			return -1;
		}
		
		$sqlca->query($query8);
		$a = $sqlca->fetchRow();
		$existe = $a['existe'];		
		
		//EN CASO NO HUBIERA REGISTROS TIPO 23, LO INSERTAMOS
		if ($existe == 0) { //Si no hay registro
			$queryinsinv = "insert into inv_movialma (tran_codigo, mov_numero, mov_fecha, mov_almacen, art_codigo,
								mov_almaorigen, mov_almadestino, mov_cantidad,
								mov_costounitario, mov_costopromedio,
								mov_costototal, mov_naturaleza )
							values ('23', '$num_parte', '$fecha', '".trim($cod_almacen)."', $cod_combustible,
								'".trim($tran_origen)."', '".trim($tran_destino)."', $totalAfericion*5,
								$stk_costo, $stk_costo,
								$totalAfericion*5*$stk_costo, '$tran_naturaleza');";
			error_log('queryinsinv: '.$queryinsinv);	
			$result = $sqlca->query($queryinsinv); 
		} else { //EN CASO EXISTA ACTUALIZAMOS MOVIMIENTOS 23
			$queryupdinv = "update inv_movialma 
								set mov_cantidad = $totalAfericion*5, 
								mov_costounitario = $stk_costo, 
								mov_costopromedio = $stk_costo,
								mov_costototal = $totalAfericion*5*$stk_costo
							where 
								TRIM(mov_numero) = '".TRIM($num_parte)."' AND DATE(mov_fecha) = '$fecha' AND TRIM(art_codigo) = '".TRIM($cod_combustible)."' AND TRIM(mov_almacen) = '".TRIM($cod_almacen)."' AND tran_codigo = '23';";
			error_log('queryupdinv: '.$queryupdinv);	
			$result = $sqlca->query($queryupdinv); 
		}

		if ($result != -1) {
			//Accion correcta
		} else {
			$sqlca->query('ROLLBACK;');
			return -1;
		}

		$sqlca->query('COMMIT;');
		return 1;
	}

	function obtieneCajas(){
		global $sqlca;
	
		$sql = "
SELECT
 s_pos_id
FROM
 s_pos
WHERE
 s_pos_id NOT IN(
 SELECT
  s_pos_id
 FROM
  s_pos
  JOIN s_postype
   USING(s_postype_id)
 WHERE
  LOWER(description) LIKE '%market%'
 )
ORDER BY
 s_pos_id;
		";
		if ($sqlca->query($sql) < 0) 
			return false;
		$producto = Array();
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$pos[$a[0]] = $a[0];
		}
		return $pos;
	}
}
