<?php

class HorarioMultiModel {

	function ingresarHorario($idcampania,$descripcion,$diamulti,
							 $horaini,$minutoini,$horafin,$minutofin,
							 $factormulti,$usuario,$sucursal){

	global $sqlca;


		$query ="Insert into prom_ta_horarios_multi
		           (id_campana,
				    ch_horario_descripcion,
					nu_horario_dia_multi,
					nu_horario_hora_inicio,
					nu_horario_minuto_inicio,
					nu_horario_hora_fin,
					nu_horario_minuto_fin,
					nu_horario_factor_multi,
					ch_usuario,
					ch_sucursal) ".
			" values(".
			      pg_escape_string($idcampania).",'".
				  pg_escape_string($descripcion)."',".
				  pg_escape_string($diamulti).",".
				  pg_escape_string($horaini).",".
				  pg_escape_string($minutoini).",".
				  pg_escape_string($horafin).",".
				  pg_escape_string($minutofin).",".
				  pg_escape_string($factormulti).",'".
				  pg_escape_string($usuario)."','".
				  pg_escape_string($sucursal)."')";	
				   
			if($sqlca->query($query)<0){
				
				return '0';
			}	

			return '1';

	}
	function actualizarHorario($idhorariomulti,$idcampania,$descripcion,$diamulti,
							   $horaini,$minutoini,$horafin,$minutofin,
							   $factormulti,$usuario,$sucursal){

	global $sqlca;
		
		$query ="Update prom_ta_horarios_multi set 
						   id_campana=".pg_escape_string($idcampania).",".
						  "ch_horario_descripcion='".pg_escape_string($descripcion)."',".
						  "nu_horario_dia_multi=".pg_escape_string($diamulti).",".
						  "nu_horario_hora_inicio=".pg_escape_string($horaini).",".
						  "nu_horario_minuto_inicio=".pg_escape_string($minutoini).",".
						  "nu_horario_hora_fin=".pg_escape_string($horafin).",". 
						  "nu_horario_minuto_fin=".pg_escape_string($minutofin).",".
						  "nu_horario_factor_multi=".pg_escape_string($factormulti).",".
						  "ch_usuario='".pg_escape_string($usuario)."',".
						  "dt_fecha_actualiza=now(),".
						  "ch_sucursal='".pg_escape_string($sucursal)."' ".
			" where id_horario_multi= ".pg_escape_string($idhorariomulti);
	
			if($sqlca->query($query)<0){
				return 0;
			}	
			
			
			return '1';
		
		

	}

	function eliminarHorario($idhorariomulti){
		global $sqlca;
		$query = "Delete from prom_ta_horarios_multi where id_horario_multi=".pg_escape_string($idhorariomulti)." ";
		
		$result= $sqlca->query($query);
		 if ($sqlca->query($query)<=0){
      		return 0;
   		}
	return 1;
	}


  function tmListado($filtro,$pp, $pagina){
    global $sqlca;
    $cond = '';

    if (!empty($filtro)){
		$cond =" where h.ch_horario_descripcion like '".trim($filtro)."%' ";
    }

    $query = "Select   		
					h.id_horario_multi,
					h.ch_horario_descripcion,
					h.id_campana,
					c.ch_campana_descripcion,
					to_char(h.dt_horario_fecha_creacion,'dd/mm/yyyy') as dt_horario_fecha_creacion,
					h.nu_horario_dia_multi,
					h.nu_horario_hora_inicio, 
					h.nu_horario_minuto_inicio,
					h.nu_horario_hora_fin, 
					h.nu_horario_minuto_fin, 
					h.nu_horario_factor_multi 
			from prom_ta_horarios_multi h inner join prom_ta_campanas c on h.id_campana = c.id_campana ".$cond.
	      " order by h.ch_horario_descripcion ";

	
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


}

