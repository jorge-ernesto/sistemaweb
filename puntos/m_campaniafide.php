<?php

class CampaniaFideModel {

	function ingresarCampania($idcampania,$descripcion,$fechaini,$fechafin,$diasven,$objetivo,$usuario,$sucursal,$repeticiones,$slogan,$saludacumple) {
		global $sqlca;

		$queryInicial = "SELECT ch_campana_descripcion FROM prom_ta_campanas WHERE ch_campana_descripcion LIKE '".$descripcion."' ";

		$query ="INSERT INTO	prom_ta_campanas (
		           		id_campana,
				    	ch_campana_descripcion,
					dt_campana_fecha_inicio,
					dt_campana_fecha_fin,
					nu_dias_vencimiento,
					ch_campana_objetivo,
					ch_usuario,
					ch_sucursal,
					nu_repeticiones,
					slogan,
					b_saluda_cumple) 
			 	VALUES	(".
				  	pg_escape_string($idcampania).",'".	
			      		pg_escape_string($descripcion)."',".
				 	"to_date('".pg_escape_string($fechaini)."','dd/mm/yyyy'),".
				  	"to_date('".pg_escape_string($fechafin)."','dd/mm/yyyy'),".
					pg_escape_string($diasven).",'".
					pg_escape_string($objetivo)."','". 
					pg_escape_string($usuario)."','".
					pg_escape_string($sucursal)."',".
					pg_escape_string($repeticiones).",'".
					pg_escape_string($slogan)."',".
					(($saludacumple=="1")?"1":"0")."::bit)";	
	
		if($sqlca->query($query)<0){
			return '0';
		}
	
		return '1';
	}

	function actualizarCampania($idcampana,$descripcion,$fechafin,$diasven,$objetivo,$usuario,$sucursal,$repeticiones,$slogan,$saludacumple) { 
		global $sqlca;

		$queryInicial = "Select id_campana from prom_ta_campanas where id_campana = {$idcampana};";

		if($sqlca->query($queryInicial)<=0) {
			return '0';
		} else {
			$query ="UPDATE prom_ta_campanas SET 
						   ch_campana_descripcion='".pg_escape_string($descripcion)."',".
						  "dt_campana_fecha_fin=to_date('".pg_escape_string($fechafin)."','dd/mm/yyyy'),".
						  "nu_dias_vencimiento=".pg_escape_string($diasven).",".
						  "ch_campana_objetivo='".pg_escape_string($objetivo)."',".
						  "ch_usuario='".pg_escape_string($usuario)."',".
						  "dt_fecha_actualiza=now(),".
						  "ch_sucursal='".pg_escape_string($sucursal)."', ".
						  "nu_repeticiones='".pg_escape_string($repeticiones)."', ".
						  "slogan='".pg_escape_string($slogan)."', ".
						  "b_saluda_cumple=".(($saludacumple=="1")?"1":"0")."::bit ".
				" WHERE id_campana= ".pg_escape_string($idcampana);

			if($sqlca->query($query)<0)
				return '0';
			
			return '1';						
		}		
	}

	function obtenerCampania($idcampania) {
		global $sqlca;

		settype($idcampania,"int");

		$sql =	"	SELECT
					id_campana,
					ch_campana_descripcion,
					to_char(dt_campana_fecha_creacion,'DD/MM/YYYY'),
					to_char(dt_campana_fecha_inicio,'DD/MM/YYYY'),
					to_char(dt_campana_fecha_fin,'DD/MM/YYYY'),
					ch_campana_objetivo,
					nu_factor_puntosxsol,
					nu_dias_vencimiento,
					ch_usuario,
					dt_fecha_actualiza,
					ch_sucursal,
					nu_repeticiones,
					slogan,
					b_saluda_cumple
				FROM
					prom_ta_campanas
				WHERE
					id_campana = {$idcampania};";

		$sqlca->query($sql);
	
		$reg = $sqlca->fetchRow();

		$campania['idcampania'] 		= $reg[0];
		$campania['campaniadescripcion']	= $reg[1];
		$campania['campaniafechacrea'] 		= $reg[2];
		$campania['campaniafechaini'] 		= $reg[3];
		$campania['campaniafechafin'] 		= $reg[4];
		$campania['campaniadiasven'] 		= $reg[7];
		$campania['campaniaobjetivo'] 		= $reg[5];
		$campania['campaniarepeticiones'] 	= $reg[11];
		$campania['slogan'] 			= $reg[12];
		$campania['saludacumple'] 		= $reg[13];

		return $campania;
	}
	
	function nuevoIdCampania() {
		global $sqlca;

		$registro = array();
		$query ="Select nextval('prom_seq_idcampanas') as nuevo_id ";

		$sqlca->query($query);	
		while($reg = $sqlca->fetchRow()) {
			$registro = $reg;			
		}
		return $registro;	
	}
	
	
	function obtenerCuenta($campovalor,$tipocampo) {
		global $sqlca;

		$registro = array();
		$campo ='';	
		
		if($tipocampo=='1') {	
			$campo='id_cuenta='.pg_escape_string($campovalor)." ";
		} else { 		
			$campo='nu_cuenta_numero='.pg_escape_string($campovalor)." ";	
		}
		$query = "SELECT id_cuenta,nu_cuenta_numero,ch_cuenta_nombres,ch_cuenta_apellidos,ch_cuenta_dni,ch_cuenta_ruc,ch_cuenta_direccion,
				ch_cuenta_telefono1,ch_cuenta_telefono2,ch_cuenta_email ,nu_cuenta_puntos,ch_usuario FROM prom_ta_cuentas WHERE ".$campo;
		$sqlca->query($query);

		while($reg = $sqlca->fetchRow()) {
			$registro = $reg;			
		}
		return $registro;		
	}
	
	function eliminarCampania($idcampana) {
		global $sqlca;

		$query = "Delete from prom_ta_campanas where id_campana=".pg_escape_string($idcampana)." ";
		$result= $sqlca->query($query);
		return '1';
	}

	function listarCampanias($filtro) {
		global $sqlca;

		$cond = " ";
		if(!empty($filtro) || $filtro!=" ") {
			$cond = " WHERE ch_campana_descripcion LIKE '".pg_escape_string(trim($filtro))."%'";
		}
		$query="SELECT id_campana,ch_campana_descripcion,ch_campana_objetivo,nu_repeticiones FROM prom_ta_campanas ".$cond." ORDER BY ch_campana_descripcion";
		$listado = array();
		$resultado= $sqlca->query($query);
		while($reg = $sqlca->fetchRow()) {
			$listado['datoscampanias'][] = $reg;
		}

		return $listado;
	}	

	function listarCampaniasTipo($tipo,$filtro) {
		global $sqlca;

		$cond = " ";
		$listado = array();
		if($tipo=="1") {
			$cond = " WHERE id_campana = ".pg_escape_string(trim($filtro));
		} else if($tipo="2") {
			$cond=	" WHERE id_tipo_cuenta = ".pg_escape_string(trim($filtro));
		}
		$query = "	SELECT
					ctc.id_campana_tipo as id_campana_tipo,
					ctc.id_campana as id_campana,
					ctc.id_tipo_cuenta as id_tipo_cuenta,
					tc.ch_tipo_descripcion as ch_tipo_descripcion
				FROM
					prom_ta_campanas_tipocuenta ctc
					INNER JOIN prom_ta_tipo_cuentas tc on ctc.id_tipo_cuenta = tc.id_tipo_cuenta ";
		$listado = array();
		$resultado= $sqlca->query($query);
		while($reg = $sqlca->fetchRow()) {
			$listado['datoscampaniastipo'][] = $reg;
		}

		return $listado;
	}	

	function listarTipoCuentas($filtro) {
		global $sqlca;

		$cond = "";
		if(!empty($filtro) || $filtro != "")
			$cond = "WHERE ch_tipo_descripcion LIKE '".pg_escape_string(trim($filtro))."%'; ";

		$query = "
		SELECT
			id_tipo_cuenta,
			ch_tipo_descripcion
		FROM
			prom_ta_tipo_cuentas
		" . $cond;

		$listado = array();
		$resultado= $sqlca->query($query);
		while($reg = $sqlca->fetchRow()){
			$listado['datostipocuentas'][] = $reg;
		}
		return $listado;
	}
	
	function obtenerArticulo($campovalor,$tipocampo) {
		global $sqlca;

		$registro = array();
		$campo ='';	

		if($tipocampo=='1'){
			$campo = " WHERE art_codigo='".pg_escape_string(trim($campovalor))."' ";
		} else {
			$campo = ' WHERE art_descripcion like '.pg_escape_string(trim($campovalor))."%'";	
		}
		$query = "SELECT art_codigo, art_descripcion FROM int_articulos ".$campo;
		$sqlca->query($query);	
		while($reg = $sqlca->fetchRow()) {
			$registro = $reg;			
		}

		return $registro;		
	}

  	function tmListado($filtro,$pp, $pagina) {
    		global $sqlca;
    		$cond = '';

    		if (!empty($filtro)){
			$cond =" WHERE ch_campana_descripcion LIKE '".trim($filtro)."%' ";
    		}

    		$query = "SELECT  		
					id_campana,
					ch_campana_descripcion,
					to_char(dt_campana_fecha_creacion,'dd/mm/yyyy') as dt_campana_fecha_creacion,
					to_char(dt_campana_fecha_inicio,'dd/mm/yyyy') as dt_campana_fecha_inicio,
					to_char(dt_campana_fecha_fin,'dd/mm/yyyy') as dt_campana_fecha_fin,
					ch_campana_objetivo,
					nu_dias_vencimiento,
					nu_repeticiones
				FROM 	prom_ta_campanas ".$cond.
	      			" ORDER BY ch_campana_descripcion ";
	
		$resultado_1 = $sqlca->query($query);	
         	$numrows = $sqlca->numrows();

		if($pp && $pagina){
			$paginador = new paginador($numrows,$pp, $pagina);
		}else{
			$paginador = new paginador($numrows,100,0);
		}
	
		$listado2['partir'] = $paginador->partir();
		$listado2['fin'] = $paginador->fin();
		$listado2['numero_paginas'] = $paginador->numero_paginas();
		$listado2['pagina_previa'] = $paginador->pagina_previa();
		$listado2['pagina_siguiente'] = $paginador->pagina_siguiente();
		$listado2['pp'] = $paginador->pp;
		$listado2['paginas'] = $paginador->paginas();
		$listado2['primera_pagina'] = $paginador->primera_pagina();
		$listado2['ultima_pagina'] = $paginador->ultima_pagina();
	
    		if ($pp > 0)
	    		$query .= "LIMIT " . pg_escape_string($pp) . " ";
		if ($pagina > 0)
	    		$query .= "OFFSET " . pg_escape_string($paginador->partir());
       
  		if ($sqlca->query($query)<=0){
      			return $sqlca->get_error();
   		}
    
    		$listado[] = array();
   		 while($reg = $sqlca->fetchRow()){
	  		$listado['datos'][] = $reg;
    		}            
    		$listado['paginacion'] = $listado2;

    		return $listado;
  	}

	function ingresarTipoCuenta($idcampania,$idtipocuenta){
	
		global $sqlca;
		$queryInicial = "Select ch_campana_descripcion from prom_ta_campanas where ch_campana_descripcion like '".$descripcion."' ";
	
			$query ="Insert into prom_ta_campanas_tipocuenta
					   (id_campana,
						id_tipo_cuenta) ".
				" values(".
					  pg_escape_string($idcampania).",".
					  pg_escape_string($idtipocuenta).")";

				$result = $sqlca->query($query);
				return "1";
			
		}
	
	  
	}

