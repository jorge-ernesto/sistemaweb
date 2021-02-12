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
					responsabl
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
		//error_log('dia: '.$dia);
		$query1 = "SELECT
 FIRST(es) es,
 FIRST(caja) caja,
 FIRST(dia) dia,
 FIRST(turno) turno,
 FIRST(pump) pump,
 FIRST(codigo) codigo,
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

 		//error_log('query1: '.$query1);
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
		$pump = $a['pump'];
		$codigo	= $a['codigo'];
		$cantidad = $a['cantidad'];
		$precio	= $a['precio'];
		$igv = $a['igv'];
		$importe = $a['importe'];
		$trans = $a['trans'];

		$dat = AfericionesModel::verificaAfericion($dia, $trans, $caja);
		//error_log('$dat: '.$dat);
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

		//error_log("query2: $query2");
		//echo "-- insert afericion: ".$query2.' --';
		$result_insert = $sqlca->query($query2);
		//error_log("result_insert: $result_insert");
		if ($result_insert != -1) {
			$actualizarParte = AfericionesModel::actualizarParte($dia);
			//error_log('actualizarParte: '.$actualizarParte);
			if ($actualizarParte == -1) {
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

	function eliminarAfericion($trans, $dia, $caja){
		global $sqlca;

		$query = "DELETE FROM pos_ta_afericiones WHERE trans=".$trans." AND dia ='".$dia."' AND caja ='".$caja."';";
		$sqlca->query($query);
		AfericionesModel::actualizarParte($dia);
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
			//error_log('del_comb_ta_contometros: '.$del_comb_ta_contometros);
			if ($del_comb_ta_contometros == -1) {
				return $del_comb_ta_contometros;
			} else {
				$query3 = "DELETE FROM inv_movialma WHERE date(mov_fecha) = '$dia' AND tran_codigo IN ('23','24','25');";
				$del_inv_movialma = $sqlca->query($query3);
				//error_log('del_inv_movialma: '.$del_inv_movialma);
				
				if ($del_inv_movialma == -1) {
					return $del_inv_movialma;
				} else {
					$query2 = "SELECT combex_fn_contometros_auto('$dia');";
					$sel_combex_fn_contometros_auto = $sqlca->query($query2);
					//error_log('sel_combex_fn_contometros_auto: '.$sel_combex_fn_contometros_auto);
					if ($sel_combex_fn_contometros_auto < 1) {
						return -1;
					} 
				}
			}
		}
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
