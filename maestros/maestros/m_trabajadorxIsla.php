<?php

class TrabajadorxIslaModel extends Model {

	function ingresarRegistro($codigoSuc,$fecha,$turno,$isla,$codigoTrab,$tipo) {
		global $sqlca;

		$queryInicial = "SELECT ch_codigo_trabajador FROM pla_ta_trabajadores WHERE ch_codigo_trabajador = '".$codigoTrab."' ";
		echo $queryInicial;
		if($sqlca->query($queryInicial) <= 0) {
			return '0';
		} else {

			$query = " INSERT INTO 
						pos_historia_ladosxtrabajador 
						(
							ch_sucursal,
							dt_dia,
							ch_posturno,
							ch_lado,
							ch_codigo_trabajador,
							ch_tipo		
						) VALUES (
							'".pg_escape_string($codigoSuc).
							"',to_date('".pg_escape_string($fecha)."','dd/mm/yyyy'),'"
							.pg_escape_string($turno)."','"
							.pg_escape_string(substr($isla,0,2))."','"
							.pg_escape_string($codigoTrab)."','"
							.pg_escape_string($tipo)."'
						)";	
			$result = $sqlca->query($query);
			if($result == -1)
				return 'valida';
			else
				return '';
		}

	}

	function actualizarRegistro($codigoSuc,$fecha,$turno,$isla,$codigoTrab,$tipo) {
		global $sqlca;
	
		$queryInicial = "SELECT ch_codigo_trabajador FROM pla_ta_trabajadores WHERE ch_codigo_trabajador = '".$codigoTrab."' ";
	
		if($sqlca->query($queryInicial) <= 0) {			
			return '0';
		} else {
			$query = "UPDATE pos_historia_ladosxtrabajador SET ch_codigo_trabajador='"
					.pg_escape_string($codigoTrab)."' where ch_sucursal  ='"
					.pg_escape_string($codigoSuc)."' and dt_dia = to_date('"
					.pg_escape_string($fecha). "','dd/mm/yyyy') and ch_posturno = '"
					.pg_escape_string($turno). "' and ch_lado = '"
					.pg_escape_string(substr($isla,0,2)). "' and ch_tipo = '"
					.pg_escape_string($tipo). "' ";
	
		echo $query;

			$result = $sqlca->query($query);
			return '';
		}
 	}

	function recuperarRegistroArray($registroid,$fecha,$turno,$isla,$codTrab,$tipo) {
		global $sqlca;
		
		$registro = array();
		$query = "SELECT
				lt.ch_sucursal, 
				to_char(lt.dt_dia,'dd/mm/yyyy') as dt_dia, 
				lt.ch_posturno,
				l.lado || '-' || l.prod1 || '-' || l.prod2 || '-' || l.prod3 as ch_lado, 
				lt.ch_codigo_trabajador, 
				trim(t.ch_apellido_paterno)||' ' || trim(t.ch_apellido_materno) || ', 
				' || trim(t.ch_nombre1)|| ' ' || trim(t.ch_nombre2) as nombretrab, 
				lt.ch_tipo, 
				lt.ch_lado as ch_caja 
			FROM 	
				pos_historia_ladosxtrabajador lt inner join pla_ta_trabajadores t on lt.ch_codigo_trabajador = t.ch_codigo_trabajador 
				LEFT JOIN pos_cmblados l ON lt.ch_lado = l.lado 
			WHERE
				lt.ch_sucursal = '". pg_escape_string($registroid) . "' 
				AND lt.dt_dia = '".pg_escape_string($fecha)."' 
				AND lt.ch_posturno = '".pg_escape_string($turno)."' 
				AND lt.ch_lado = '".pg_escape_string(substr($isla,0,2))."' 
				AND lt.ch_codigo_trabajador = '".pg_escape_string($codTrab)."' 
				AND lt.ch_tipo = '".pg_escape_string($tipo)."'";trigger_error($query);
	 
		$sqlca->query($query);

		while( $reg = $sqlca->fetchRow()) {
			$registro = $reg;
		}
    
		return $registro;
  	}

	function eliminarRegistro($codigoSuc, $fecha, $turno, $isla, $codigoTrab, $tipo) {
		global $sqlca;
	
		$query = "	DELETE FROM 
					pos_historia_ladosxtrabajador 
				WHERE 
					ch_sucursal  ='".pg_escape_string($codigoSuc)."'
					AND ch_codigo_trabajador='".pg_escape_string($codigoTrab)."'
					AND dt_dia = '".pg_escape_string($fecha). "' 
					AND ch_posturno = ".pg_escape_string($turno). "
					AND ch_lado = '".pg_escape_string(substr($isla,0,2)). "' 
					AND ch_tipo = '".pg_escape_string($tipo). "' ";

		$result = $sqlca->query($query);

		return '';		
 	}

	function tmListado($desde,$hasta,$trabajador,$pp, $pagina,$completo) {		
		global $sqlca;
		$cond = "";

		if ($desde != "" && $hasta != "")
			$cond .= " lt.dt_dia BETWEEN '" . pg_escape_string($desde) . "' AND '" . pg_escape_string($hasta) . "' AND ";
		if ($trabajador != "")
			$cond .= " lt.ch_codigo_trabajador = '" . pg_escape_string($trabajador) . "' AND ";

    		$query = "	SELECT 	lt.ch_sucursal,
					lt.dt_dia, 
					lt.ch_posturno, 
					l.lado || '-' || l.prod1 || '-' || l.prod2 || '-' || l.prod3 as ch_lado, 
					trim(lt.ch_codigo_trabajador) AS ch_codigo_trabajador,
					trim(t.ch_apellido_paterno) || ' ' || trim(t.ch_apellido_materno) || ', ' || trim(t.ch_nombre1)|| ' ' || trim(t.ch_nombre2) as nombretrab, 
					lt.ch_tipo,lt.ch_lado as ch_caja 
				FROM 
					pos_historia_ladosxtrabajador lt 
					INNER JOIN pla_ta_trabajadores t on lt.ch_codigo_trabajador = t.ch_codigo_trabajador 
					LEFT JOIN pos_cmblados l ON lt.ch_lado = l.lado
				WHERE {$cond} 1 = 1
				ORDER BY 
					lt.ch_sucursal, 
					lt.dt_dia desc,
					lt.ch_posturno desc,
					lt.ch_lado  ";
	
		$resultado_1 = $sqlca->query($query);
        	$numrows = $sqlca->numrows();

		if ($completo == FALSE) {
			if($pp && $pagina) {
				$paginador = new paginador($numrows,$pp, $pagina);
			} else {
				$paginador = new paginador($numrows,100,0);
			}
	
			$listado2['partir'] 		= $paginador->partir();
			$listado2['fin'] 		= $paginador->fin();
			$listado2['numero_paginas'] 	= $paginador->numero_paginas();
			$listado2['pagina_previa'] 	= $paginador->pagina_previa();
			$listado2['pagina_siguiente'] 	= $paginador->pagina_siguiente();
			$listado2['pp'] 		= $paginador->pp;
			$listado2['paginas'] 		= $paginador->paginas();
			$listado2['primera_pagina'] 	= $paginador->primera_pagina();
			$listado2['ultima_pagina'] 	= $paginador->ultima_pagina();

			if ($pp > 0)
				$query .= "LIMIT " . pg_escape_string($pp) . " ";
			if ($pagina > 0)
		    		$query .= "OFFSET " . pg_escape_string($paginador->partir());
		}
       
   		if ($sqlca->query($query) <= 0) {
			return $sqlca->get_error();
   		}
    
		$listado[] = array();
    
		while( $reg = $sqlca->fetchRow()){
			$listado['datos'][] = $reg;
    		}    
        
		if ($completo == FALSE)
			$listado['paginacion'] = $listado2;

		return $listado;
  	}

    	function obtieneLados() {
		global $sqlca;
	
		$sql = "SELECT
			    	lado || '-' || prod1 || '-' || prod2 || '-' || prod3
			FROM
			    	pos_cmblados 
			ORDER BY
				lado;";

		if ($sqlca->query($sql) < 0) 
			return false;
	
		$producto = Array();
	
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    $a = $sqlca->fetchRow();
		    $lado[$a[0]] = $a[0];
		}
	
		return $lado;
    	}

    	function obtieneLadosMarket() { // agregado al query el OR para que obtenga los puntos de venta,market
		global $sqlca;
	
		$sql = "SELECT
			    pos
			FROM
			    pos_cfg 
			WHERE
			    tipo='M' OR (tipo='C' and b_hybrid='TRUE') OR (tipo='C')
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

    	function obtieneTurnos() {
		global $sqlca;
	
		$producto = Array();
		$lado[1] = "1";
		$lado[2] = "2";
		$lado[3] = "3";
		$lado[4] = "4";
		$lado[5] = "5";
		$lado[6] = "6";
		$lado[7] = "7";
		$lado[8] = "8";
		$lado[9] = "9";
		return $lado;
   	 }

	function obtieneFechaxDefecto() {
		global $sqlca;

		$sql = "SELECT to_char(util_fn_fechaactual_aprosys(),'DD/MM/YYYY');";
		if ($sqlca->query($sql) < 0) 
			return false;
		$a = $sqlca->fetchRow();
		$fecha = $a[0];
		return $fecha;
	}	

	
  	function validarConsolidacion($dia,$turno) {
		global $sqlca;	
	
		$fecha	= explode("/",$dia);
		$newday = $fecha[2].'-'.$fecha[1].'-'.$fecha[0];	
		$sql =	"SELECT 1 FROM pos_consolidacion WHERE dia = to_date('$dia','DD/MM/YYYY') AND turno = $turno;";

		if ($sqlca->query($sql)<0 || $sqlca->numrows()==0)
			return 0;
		return 1;
  	}

	/*function validarEliminacion($dia) {
		global $sqlca;	
	
		$sql =	"select 1 where (util_fn_fechaactual_aprosys()-'$dia')<5 and (util_fn_fechaactual_aprosys()-'$dia')>=0";

		if ($sqlca->query($sql)<0 || $sqlca->numrows()==0)
			return 0;
		return 1; // dentro de los 5 dias, permitido
  	}*/
}
