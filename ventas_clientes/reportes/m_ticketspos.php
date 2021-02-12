<?php

class TicketsPosModel extends Model {

	function obtenerAlmacenes() {
		global $sqlca;

		$sql = "SELECT ch_almacen, ch_almacen||' - '||ch_nombre_almacen
		        FROM inv_ta_almacenes
		        WHERE ch_clase_almacen='1';";
				
		if ($sqlca->query($sql) < 0) 
			return false;
		$result = array();
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$ch_almacen = $a[0];
			$ch_nombre_almacen = $a[1];
			$result[$ch_almacen] = $ch_nombre_almacen;
		}
        	return $result;
    	}

	function reporte_bonus($ip,$tipo_consulta, $tm, $td,$bonus,$almacen, $lado, $caja, $turno,$periodo, $mes, $dia_desde, $dia_hasta, $art_codigo, $ruc, $cuenta, $tarjeta, $tipo) {
		global $sqlca;

		$serie_equipo = "";
		$sql = "SELECT substring(nroserie from char_length(nroserie)-5 for 6) FROM pos_cfg where ip='".$ip."'";
		$rs = $sqlca->query($sql);
		if ($rs < 0) 
			return false;
		$row = $sqlca->fetchRow();
		$serie_equipo = "{$row[0]}";
		
		$codigo_bonus = "";
		$sql = "SELECT par_valor FROM int_parametros where par_nombre='codigo_bonus'";
		$rs = $sqlca->query($sql);
		if ($rs < 0) 
			return false;
		$row = $sqlca->fetchRow();
		$codigo_bonus="{$row[0]}";
	
		if ($tipo_consulta == "historico")
			$tabla = pg_escape_string("pos_trans" . $periodo . $mes);
		else
			$tabla = "pos_transtmp";
			
		$sql = "BEGIN";
		$sqlca->query($sql);		
		$desde = $dia_desde . "/" . $mes . "/" . $periodo;
		$hasta = $dia_hasta . "/" . $mes . "/" . $periodo;
		
		$sql = "select distinct 
					substring('000000'||'".pg_escape_string($codigo_bonus)."' from char_length(trim('".pg_escape_string($codigo_bonus)."'))+1 for 6)||
					substring('00000000000'||substring(p.indexa from 1 for char_length(trim(p.indexa))-1) from char_length(substring(p.indexa from 1 for char_length(trim(p.indexa))-1))+1 for 11)||
					to_char(p.fecha,'DDMMYYYY')||
					to_char(p.fecha,'HH24MI')||
					substring(cfg.nroserie from char_length(cfg.nroserie)-5 for 6)||
					substring('000000'||p.trans from char_length(p.trans::text)+1 for 6)||
					substring('00000'||trunc(abs(p2.total),0)::text from char_length(trunc(abs(p2.total),0)::text)+1 for 5)||substring(round(abs(p2.total),2)::text from position('.' in round(abs(p2.total),2)::text)+1 for 3)||
					CASE WHEN p.tipo='M' THEN substring('00000000000000'||p.codigo from char_length(trim(p.codigo))+1 for 14) Else substring('00000000000000'||c.ch_codigopec from char_length(trim(c.ch_codigopec))+1 for 14) END ||
					substring('00000'||trunc(abs(p.cantidad),0)::text from char_length(trunc(abs(p.cantidad),0)::text)+1 for 5)||substring(round(abs(p.cantidad),3)::text from position('.' in round(abs(p.cantidad),3)::text)+1 for 4)||
					substring('00000'||trunc(abs(p.importe),0)::text from char_length(trunc(abs(p.importe),0)::text)+1 for 5)||substring(round(abs(p.importe),2)::text from position('.' in round(abs(p.importe),2)::text)+1 for 3)
					as codigo,p.fecha
				
				from 	(select distinct td,tm,dia,turno,es,caja,indexa,fecha,trans,importe,cantidad,tipo,codigo from " . $tabla . " ORDER BY fecha) p
						left join 
						(select distinct sum(pos.importe) as total, pos.trans from (select distinct td,tm,dia,turno,es,caja,indexa,fecha,trans,importe,cantidad,tipo,codigo from " . $tabla . "  ORDER BY fecha) pos group by trans) p2 ON p.trans=p2.trans 
						left join 
						pos_cfg cfg ON cfg.pos=p.caja 
						left join 
						comb_ta_combustibles c ON c.ch_codigocombustible = p.codigo
				
				where 	trim(p.indexa)!='' and trim(p.indexa)!='.' and trim(p.indexa)!='0' AND p.es='" . pg_escape_string($almacen) . "' ";
				
			if ($tipo_consulta == "historico") {
				$sql .= "AND p.dia>='" . pg_escape_string($periodo.'-'.$mes.'-'.$dia_desde) . "'
					 AND p.dia<='" . pg_escape_string($periodo.'-'.$mes.'-'.$dia_hasta) . "' ";
			}

			if (count($tm) > 0) {
				$sql .= "AND p.tm IN (";
				for ($i = 0; $i < count($tm); $i++) {
					if ($i > 0) $sql .= ",";
					$sql .= "'" . pg_escape_string($tm[$i]) . "'";
				}
				$sql .= ") ";
			}

			if (count($td) > 0) {
				$sql .= "AND p.td IN (";
				for ($i = 0; $i < count($td); $i++) {
					if ($i > 0) $sql .= ",";
					$sql .= "'" . pg_escape_string($td[$i]) . "'";
				}
				$sql .= ") ";
			}

			if ($lado != "TODOS") {
				$sql .= "AND p.pump='" . pg_escape_string(substr($lado,0,2)) . "' ";
			}	
		
			if ($tipo != "TODOS") {
				$sql .= "AND p.tipo='" . $tipo . "'
						";
			}
			if ($turno != "TODOS" and $turno != "0") {
				$sql .= "AND p.turno='" . pg_escape_string(substr($turno,0,2)) . "' ";
			}

			if ($caja != "TODAS") {
				$sql .= "AND p.caja='" . pg_escape_string($caja) . "' ";
			}

			if ($art_codigo != "") {
				$sql .= "AND p.codigo='" . pg_escape_string($art_codigo) . "' ";
			}

			if ($ruc != "") {
				$sql .= "AND p.ruc='" . pg_escape_string($ruc) . "' ";
			}
			
			if ($cuenta != "") {
				$sql .= "AND p.cuenta='" . pg_escape_string($cuenta) . "' ";
			}

			if ($tarjeta != "") {
				$sql .= "AND p.indexa like '%" . pg_escape_string($tarjeta) . "% ' ";
			}
			
		$sql .= " ORDER BY p.fecha;";
		echo "bonus es: ".$sql;
		if ($sqlca->query($sql) < 0) 
			return false;
		$resultado = Array();
		
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$resultado[$i]['codigo'] = $a[0];
		}
		$sql = "COMMIT";
		$sqlca->query($sql);

		return $resultado;
	}

	function busqueda($tipo_consulta, $tm, $td,  $bonus,$almacen, $lado, $caja, $turno, $periodo, $mes, $dia_desde, $dia_hasta, $art_codigo, $ruc, $cuenta, $tarjeta, $tipo) {
		global $sqlca;

		if ($tipo_consulta == "historico")
			$tabla = pg_escape_string("pos_trans" . $periodo . $mes);
		else
			$tabla = "pos_transtmp";

		$sql = "BEGIN";
		$sqlca->query($sql);

		$desde = $dia_desde . "/" . $mes . "/" . $periodo;
		$hasta = $dia_hasta . "/" . $mes . "/" . $periodo;

		$sql = "SELECT
		            trans.tm,
		            trans.td,
		            trans.trans,
		            to_char(trans.fecha, 'DD/MM/YYYY HH24:MI:SS') as fecha,
		            art.art_descripcion,
		            round(CAST(trans.cantidad AS numeric), 4),
		            round(trans.precio,4),
		            round(trans.importe,4),
		            trans.tarjeta,
		            trans.odometro,
		            trans.placa,
		            trans.proveedor,
		            trans.usr,
		            trans.caja,
		            trans.pump,
		            trans.indexa,
		            trans.ruc,
			    truc.razsocial,
			    trans.turno,
			    round(trans.igv,4)
		        FROM
		            " . $tabla . " trans
			    LEFT JOIN ruc truc ON (trans.ruc=truc.ruc),
		            int_articulos art
		        WHERE
		                art.art_codigo=trans.codigo
		            AND trans.es='" . pg_escape_string($almacen) . "' ";
		
		if ($tipo_consulta == "historico") {
		    $sql .= "AND trans.dia>='" . pg_escape_string($periodo.'-'.$mes.'-'.$dia_desde) . "'
		             AND trans.dia<='" . pg_escape_string($periodo.'-'.$mes.'-'.$dia_hasta) . "'  ";
		}

		if (count($tm) > 0) {
		    $sql .= "AND trans.tm IN (";

		    for ($i = 0; $i < count($tm); $i++) {
		        if ($i > 0) 
				$sql .= ",";
		        $sql .= "'" . pg_escape_string($tm[$i]) . "'";
		    }
		    $sql .= ") ";
		}

		if (count($td) > 0) {
		    $sql .= "AND trans.td IN (";

		for ($i = 0; $i < count($td); $i++) {
		    	if ($i > 0) 
				$sql .= ",";
		        $sql .= "'" . pg_escape_string($td[$i]) . "'";
		    }
		    $sql .= ") ";
		}

		if ($lado != "TODOS") {
		    $sql .= "AND trans.pump='" . pg_escape_string(substr($lado,0,2)) . "' ";
		}
		
		if ($tipo != "TODOS") {
		    $sql .= "AND trans.tipo='" . $tipo . "' ";
		}

		if ($bonus == "Bo") {
		    $sql .= " AND trim(trans.indexa)!='' and trim(trans.indexa)!='.' and trim(trans.indexa)!='0' ";
		}

		if ($turno != "TODOS"  and $turno != "0") {
		    $sql .= "AND trans.turno='" . pg_escape_string(substr($turno,0,2)) . "' ";
		}

		if ($caja != "TODAS") {
		    $sql .= "AND trans.caja='" . pg_escape_string($caja) . "' ";
		}

		if ($art_codigo != "") {
		    $sql .= "AND trans.codigo='" . pg_escape_string($art_codigo) . "' ";
		}

		if ($ruc != "") {
		    $sql .= "AND trans.ruc='" . pg_escape_string($ruc) . "' ";
		}
	
		if ($cuenta != "") {
		    $sql .= "AND trans.cuenta='" . pg_escape_string($cuenta) . "' ";
		}

		if ($tarjeta != "") {
		    $sql .= "AND trans.indexa like '%" . pg_escape_string($tarjeta) . "%' ";
		}
	
		$sql .= " ORDER BY trans.fecha, trans.trans ASC ";

		echo "*** ".$sql." ***";

		if ($sqlca->query($sql) < 0) 
			return false;
		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$tm 		= $a[0];
			$td 		= $a[1];
			$trans 		= $a[2];
			$fecha 		= $a[3];
			$art_descripcion= $a[4];
			$cantidad 	= $a[5];
			$precio 	= $a[6];
			$importe 	= $a[7];
			$tarjeta 	= $a[8];
			$odometro 	= $a[9];
			$placa 		= $a[10];
			$codcli 	= $a[11];
			$usr 		= $a[12];
			$caja 		= $a[13];
			$pump 		= $a[14];
			$indexa 	= $a[15];
			$ruc 		= $a[16];
			$razsocial 	= $a[17];
			$turno 		= $a[18];
			$igv 		= $a[19];

			$resultado[$i]['tm'] 		= $tm;
			$resultado[$i]['td'] 		= $td;
			$resultado[$i]['trans'] 	= $trans;
			$resultado[$i]['fecha'] 	= $fecha;
			$resultado[$i]['art_descripcion']= $art_descripcion;
			$resultado[$i]['cantidad'] 	= $cantidad;
			$resultado[$i]['precio'] 	= $precio;
			$resultado[$i]['importe'] 	= $importe;
			$resultado[$i]['tarjeta'] 	= $tarjeta;
			$resultado[$i]['odometro'] 	= $odometro;
			$resultado[$i]['placa'] 	= $placa;
			$resultado[$i]['codcli'] 	= $codcli;
			$resultado[$i]['usr'] 		= $usr;
			$resultado[$i]['caja'] 		= $caja;
			$resultado[$i]['pump'] 		= $pump;
			$resultado[$i]['bonus'] 	= $indexa;
			$resultado[$i]['ruc'] 		= $ruc;
			$resultado[$i]['razsocial'] 	= $razsocial;
			$resultado[$i]['turno'] 	= $turno;
			$resultado[$i]['igv'] 		= $igv;
		}
		$sql = "COMMIT";
		$sqlca->query($sql);

		return $resultado;
	}

	function tmListado($pp, $pagina, $tipo_consulta, $tm, $td,  $bonus,$almacen, $lado, $caja, $turno, $periodo, $mes, $dia_desde, $dia_hasta, $art_codigo, $ruc, $cuenta, $tarjeta, $tipo, $fpago) {
		global $sqlca;

    	$tur = $turno;

		if ($tipo_consulta == "historico")
			$tabla = pg_escape_string("pos_trans" . $periodo . $mes);
		else
			$tabla = "pos_transtmp";

		$sql = "BEGIN";
		$sqlca->query($sql);

		$desde = $dia_desde . "/" . $mes . "/" . $periodo;
		$hasta = $dia_hasta . "/" . $mes . "/" . $periodo;		

		$sql = "
SELECT
 trans.tm,
 trans.td,
 trans.trans,
 to_char(trans.fecha, 'DD/MM/YYYY HH24:MI:SS') as fecha,
 art.art_descripcion,
 CASE WHEN trans.importe>=0 THEN trans.cantidad WHEN (trans.importe<0 and tm='D') THEN trans.cantidad ELSE 0 END,
 trans.precio,
 trans.importe,
 trans.tarjeta,
 trans.odometro,
 trans.placa,
 trans.proveedor,
 trans.usr,
 trans.caja,
 trans.pump,
 trans.indexa,
 trans.ruc,
 truc.razsocial,
 trans.turno,
 trans.igv,
 PTORIGEN.fecha,
 PTORIGEN.usr
FROM
 ".$tabla." AS trans
 LEFT JOIN (SELECT trans AS id_trans, usr, TO_CHAR(fecha, 'DD/MM/YYYY HH24:MI:SS') AS fecha FROM ".$tabla." WHERE tm='V' AND es = '" . pg_escape_string($almacen) . "' AND dia::DATE BETWEEN " . pg_escape_string($periodo.'-'.$mes.'-'.$dia_desde) . "' AND " . pg_escape_string($periodo.'-'.$mes.'-'.$dia_hasta) . "') AS PTORIGEN
  ON (PTORIGEN.id_trans = trans.rendi_gln)
 LEFT JOIN ruc AS truc ON (trans.ruc=truc.ruc),
 int_articulos AS art
WHERE
 art.art_codigo=trans.codigo
		";

		$where = "
		AND trans.es = '" . pg_escape_string($almacen) . "'
		";

		if ($tipo_consulta == "historico") {
			$where .= "
			AND trans.dia >= '" . pg_escape_string($periodo.'-'.$mes.'-'.$dia_desde) . "'
			AND trans.dia <= '" . pg_escape_string($periodo.'-'.$mes.'-'.$dia_hasta) . "'
			";
        }
	
		if($tm != ''){
			if (count($tm) > 0) {
				$where .= " AND trans.tm IN (";
				for ($i = 0; $i < count($tm); $i++) {
					if ($i > 0)
						$where .= ",";
					$where .= "'" . pg_escape_string($tm[$i]) . "' ";
				}
				$where .= ") ";
			}
		}

		$cfpago = FALSE;
		if($td != ''){
			if (count($td) > 0) {
				$where .= " AND trans.td IN (";
				for ($i = 0; $i < count($td); $i++) {
					if ($i > 0)
						$where .= ",";
					$where .= "'" . pg_escape_string($td[$i]) . "' ";
					if ($td[$i] == "B" || $td[$i] == "F")
						$cfpago = TRUE;
				}
				$where .= ") ";
			}
		}

		if ($fpago != '' && $cfpago) {
			if (count($fpago) > 0) {
				$where .= " AND trans.fpago IN (";

				for ($i = 0; $i < count($fpago); $i++) {
					if ($i > 0)
						$where .= ",";
					$where .= "'" . pg_escape_string($fpago[$i]) . "' ";
				}
				$where .= ") ";
			}
		}

		if($lado != '' && $lado != "TODOS") {
			$where .= "
			AND trans.pump='" . pg_escape_string(substr($lado,0,2)) . "'
			";
		}

		if($tipo != '' && $tipo != "TODOS") {
			$where .= "
			AND trans.tipo='" . $tipo . "'
			";
		}

		if ($bonus == "Bo") {
			$where .= "
			AND trim(trans.indexa)!='' and trim(trans.indexa)!='.' and trim(trans.indexa)!='0'
			";
		}

		if($turno != '' && trim($turno) != "0") {
			$where .= "
			AND trans.turno='".$turno."'
			";
		}

		if($caja != '' && $caja != "TODAS") {
			$where .= "
			AND trans.caja='" . pg_escape_string($caja) . "'
			";
		}

		if ($art_codigo != "") {
			$where .= "
			AND trans.codigo='" . pg_escape_string($art_codigo) . "'
			";
		}

		if ($ruc != "") {
			$where .= "
			AND trans.ruc='" . pg_escape_string($ruc) . "'
			";
		}

		if ($cuenta != "") {
			$where .= "
			AND trans.cuenta='" . pg_escape_string($cuenta) . "'
			";
		}

		if ($tarjeta != "") {
			$where .= "
			AND trans.indexa LIKE '%" . pg_escape_string($tarjeta) . "%'
			";
		}

		$sqlt = "SELECT count(*) as total, sum(importe) as importe FROM {$tabla} trans WHERE true $where;";
		$resultado_t = $sqlca->query($sqlt);
		$totales1 = $sqlca->fetchRow();

		$sqlt = "SELECT sum(cantidad) as cantidad FROM {$tabla} trans WHERE true $where AND (trans.importe>=0 OR (trans.importe<0 AND trans.tm='D'));";
		$resultado_t = $sqlca->query($sqlt);
		$totales2 = $sqlca->fetchRow();

		$totales['total']    = $totales1['total'];
		$totales['importe']  = $totales1['importe'];
		$totales['cantidad'] = $totales2['cantidad'];

		$sql .= $where;	
		$sql .= "
ORDER BY
 trans.fecha,
 trans.trans ASC
 		";

		$resultado_1 = $sqlca->query($sql);
		$numrows = $sqlca->numrows();

		if($pp && $pagina){
			//echo "ENTRO 2\n REGPP : $pp \n PAG : $pagina\n";
			$paginador = new paginador($numrows,$pp, $pagina);
		} else {
			//echo "ENTRO 2 ELSE\n REGPP : $pp \n PAG : $pagina\n";
			$paginador = new paginador($numrows,100,0);
		}
	
		$listado2['partir'] 		= $paginador->partir();
		$listado2['fin'] 			= $paginador->fin();
		$listado2['numero_paginas'] = $paginador->numero_paginas();
		$listado2['pagina_previa'] 	= $paginador->pagina_previa();
		$listado2['pagina_siguiente'] = $paginador->pagina_siguiente();
		$listado2['pp'] 			= $paginador->pp;
		$listado2['paginas'] 		= $paginador->paginas();
		$listado2['primera_pagina'] = $paginador->primera_pagina();
		$listado2['ultima_pagina'] 	= $paginador->ultima_pagina();

		if ($pp > 0)
			$sql .= "LIMIT ".pg_escape_string($pp) . " ";
		if ($pagina > 0)
			$sql .= "OFFSET ".pg_escape_string((($paginador->partir()<0)?0:$paginador->partir()));
		if ($sqlca->query($sql)<=0)
			return $sqlca->get_error();
		
		$listado = array();
		$resultado = array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();

			$tm 		 = $a[0];
			$td 		 = $a[1];
			$trans 		 = $a[2];
			$fecha 		 = $a[3];
			$art_descripcion = $a[4];
			$cantidad 	 = $a[5];
			$precio 	 = $a[6];
			$importe 	 = $a[7];
			$tarjeta 	 = $a[8];
			$odometro 	 = $a[9];
			$placa 		 = $a[10];
			$codcli 	 = $a[11];
			$usr 		 = $a[12];
			$caja 		 = $a[13];
			$pump 		 = $a[14];
			$indexa 	 = $a[15];
			$ruc 		 = $a[16];
			$razsocial 	 = $a[17];
			$turno 		 = $a[18];
			$igv		 = $a[19];
			$dFechaReferencia = $a[20];
			$sSerieNumeroReferencia = $a[21];

			$resultado[$i]['tm'] 		  = $tm;
			$resultado[$i]['td'] 		  = $td;
			$resultado[$i]['trans'] 	  = $trans;
			$resultado[$i]['fecha'] 	  = $fecha;
			$resultado[$i]['art_descripcion'] = $art_descripcion;
			$resultado[$i]['cantidad'] 	  = $cantidad;
			$resultado[$i]['precio'] 	  = $precio;
			$resultado[$i]['importe'] 	  = $importe;
			$resultado[$i]['tarjeta'] 	  = $tarjeta;
			$resultado[$i]['odometro'] 	  = $odometro;
			$resultado[$i]['placa'] 	  = $placa;
			$resultado[$i]['codcli'] 	  = $codcli;
			$resultado[$i]['usr'] 	  	  = $usr;
			$resultado[$i]['caja'] 		  = $caja;
			$resultado[$i]['pump'] 		  = $pump;
			$resultado[$i]['bonus'] 	  = $indexa;
			$resultado[$i]['ruc'] 		  = $ruc;
			$resultado[$i]['razsocial'] 	  = $razsocial;
			$resultado[$i]['turno'] 	  = $turno;
			$resultado[$i]['igv'] 	  	  = $igv;
			$resultado[$i]['dFechaReferencia']=$dFechaReferencia;
			$resultado[$i]['sSerieNumeroReferencia']=$sSerieNumeroReferencia;
		}
		$resultado[-1] = $totales;
		$sql = "COMMIT";
		$sqlca->query($sql);

		$listado['datos']      = $resultado;        
		$listado['paginacion'] = $listado2;

		return $listado;
	}

	function acumuladoTurno($almacen, $tipo_consulta, $tur, $periodo, $mes, $dia_desde, $dia_hasta, $caja, $tipo) {
		global $sqlca;

		if ($tipo_consulta == "historico")
			$tabla = pg_escape_string("pos_trans".$periodo.$mes);
		else
			$tabla = "pos_transtmp";

		$sql = "SELECT	
				trans.dia as dia,
				trans.turno as turno,
				sum(trans.cantidad) as total_cantidad,
				sum(trans.importe) as total_importe
			FROM	
				".$tabla." trans
				LEFT JOIN int_articulos art ON (art.art_codigo = trans.codigo)
			WHERE		
				trans.es = '".$almacen."' ";

		if ($tipo_consulta == "historico") 
			$sql .= "AND trans.dia BETWEEN '".$periodo.'-'.$mes.'-'.$dia_desde."' AND '".$periodo.'-'.$mes.'-'.$dia_hasta."' ";
        	
		if($tur != '' && $tur != "0") 
			$sql .= "AND trans.turno='".$tur."' ";

		if($tipo != "TODOS") {
			$sql .= " AND trans.tipo='" . $tipo . "' ";
		}

		if($caja != "TODAS") {
			$sql .= " AND trans.caja='" . pg_escape_string($caja) . "' ";
		}
		
		$sql .= "GROUP BY  
				trans.dia, 
				trans.turno 
			ORDER BY
				trans.dia, trans.turno ";
		//echo "\nDIA Y TURNO\n".$sql."\n\n";

		if ($sqlca->query($sql) <= 0)
			return $sqlca->get_error();
		
		$diaturno = array();
		$canti = $sqlca->numrows();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$diaturno[$i]['dia']     = $a['dia'];
			$diaturno[$i]['turno']   = $a['turno'];		
			$diaturno[$i]['tot_can'] = $a['total_cantidad'];
			$diaturno[$i]['tot_imp'] = $a['total_importe'];		
		}

		$sql2 = "SELECT	
				trans.dia as dia,
				trans.turno as turno,
				art.art_descbreve as producto,
				sum(trans.cantidad) as cantidad,
				sum(trans.importe) as importe
			FROM	
				".$tabla." trans
				LEFT JOIN int_articulos art ON (art.art_codigo = trans.codigo)
			WHERE		
				trans.es = '".$almacen."' ";

		if ($tipo_consulta == "historico") 
			$sql2 .= "AND trans.dia BETWEEN '".$periodo.'-'.$mes.'-'.$dia_desde."' AND '".$periodo.'-'.$mes.'-'.$dia_hasta."' ";
        	
		if($tur != '' && $tur != "0") 
			$sql2 .= "AND trans.turno='".$tur."' ";

		if($tipo != "TODOS") {
			$sql2 .= " AND trans.tipo='" . $tipo . "' ";
		}

		if($caja != "TODAS") {
			$sql2 .= " AND trans.caja='" . pg_escape_string($caja) . "' ";
		}
		
		
		$sql2 .= "
			GROUP BY 
				art.art_descbreve, trans.dia, trans.turno 
			ORDER BY
				trans.dia, trans.turno";
		//echo "\nCADA PRODUCTO\n".$sql2."\n\n";

		if ($sqlca->query($sql2)<=0)
			return $sqlca->get_error();
		
		$resultado = array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$resultado[$i]['dia']      = $a['dia'];
			$resultado[$i]['turno']    = $a['turno'];
			$resultado[$i]['producto'] = $a['producto'];
			$resultado[$i]['cantidad'] = $a['cantidad'];
			$resultado[$i]['importe']  = $a['importe'];
		}
		$res = array();
		$res['header'] = $diaturno;
		$res['body']   = $resultado;
		$res['info']['almacen'] = TicketsPosModel::obtenerEstacion($almacen);
		$res['info']['periodo'] = $periodo;
		$res['info']['mes']     = $mes;
		$res['info']['desde']   = $dia_desde;
		$res['info']['hasta']   = $dia_hasta;

		return $res;	
	}

	function obtenerComandoImprimir($file) {
		global $sqlca;
		
		$sql =	"SELECT
				trim(pc_samba),
				trim(prn_samba),
				trim(ip) 
			FROM 	pos_cfg 
			WHERE	impcierre = true and pos = (SELECT par_valor from int_parametros where par_nombre='pos_consolida')
			ORDER BY tipo DESC, pos ASC";
		
		$rs = $sqlca->query($sql);
		if ($rs < 0) {
			echo "Error consultando POS\n";
			return false;
		}
		if ($sqlca->numrows()<1)
			return true;

		$row = $sqlca->fetchRow();
		$smbc="lpr -H {$row[2]} -P {$row[1]} {$file}";

		$fp = fopen("COMANDO.txt","a");
		fwrite($fp, "-".$smbc."-".PHP_EOL);
		fclose($fp);  
		return $smbc;
	}

	function obtieneLados(){
		global $sqlca;
	
		$sql = "SELECT 	lado || '-' || prod1 || '-' || prod4 || '-' || prod3 || '-' || prod4
			FROM 	pos_cmblados 
			ORDER BY lado; ";
		if ($sqlca->query($sql) < 0) 
			return false;
		$producto = Array();
	
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$lado[$a[0]] = $a[0];
		}

		var_dump($lado);

		return $lado;
	}

	function obtieneCajas(){
		global $sqlca;
	
		$sql = "SELECT
				pos
			FROM
				pos_cfg
			ORDER BY
				pos;";

		if ($sqlca->query($sql) < 0) 
			return false;
		$producto = Array();
	
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$lado[$a[0]] = $a[0];
		}

		return $lado;
	}

	function obtieneTipos(){
		$tipo = Array();
		$tipo['C'] = "Combustible";
		$tipo['M'] = "Market";
		return $tipo;
	}

	function obtieneTurnos(){
		$lado = Array();
		$lado[1] = "1";
		$lado[2] = "2";
		$lado[3] = "3";
		$lado[4] = "4";
		$lado[5] = "5";
		$lado[6] = "6";
		$lado[7] = "7";
		$lado[8] = "8";
		$lado[9] = "9";
		$lado[0] = "TODOS";
		return $lado;
	}

	function obtenerEstacion($almacen) {
		global $sqlca;

		$sql = "SELECT 	 trim(ch_nombre_almacen) as nombre
			FROM	 inv_ta_almacenes
			WHERE	 ch_clase_almacen='1' AND ch_almacen='$almacen' ";

		if ($sqlca->query($sql) < 0) 
			return false;
		
		$a = $sqlca->fetchRow();
		$nomalmacen = $a['nombre'];
		
		return $nomalmacen;
	}
}
