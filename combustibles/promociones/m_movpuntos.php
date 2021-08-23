<?php

Class MovpuntosModel extends Model{
	
    function llamadaRemota($procedimiento, $parametros){
        global $sqlca;

        $sql = "SELECT par_valor FROM int_parametros WHERE par_nombre = 'master_puntos';";
        $sqlca->query($sql);
        $row = $sqlca->fetchRow();
        $ip = $row[0];

        $url = "http://" . $ip . "/sistemaweb/puntos/index.php?action=movpuntos&proc=" . urlencode($procedimiento);
		  error_log($url);

        foreach($parametros as $parametro=>$valor) {
        	$url .= "&" . $parametro . "=" . urlencode($valor);
        }

        $fh = fopen($url,"rb");
	    if ($fh === FALSE)
			return FALSE;

        $res = '';

        while (!feof($fh)) {
            $res .= fread($fh, 8192);
        }

        fclose($fh);
        return unserialize($res);
    }
	
	function obtenerCuentaxTarjeta($campovalor,$tipocampo){
	    $params = array(
		"campovalor"	=> $campovalor,
		"tipocampo"	=> $tipocampo
	    );
	    
	    return MovpuntosModel::llamadaRemota("obtenerCuentaxTarjeta", $params);
	}
	
	function obtenerTarjeta($campovalor,$tipocampo){
	    $params = array(
		"campovalor"	=> $campovalor,
		"tipocampo"	=> $tipocampo
	    );
	    
	    return MovpuntosModel::llamadaRemota("obtenerTarjeta", $params);
	}
	
  //Otras funciones para consultar la DB

  function tmListado($numerotarjeta,$fechaini,$fechafin,$pp, $pagina){
    $params = array(
	"numerotarjeta"	=> $numerotarjeta,
	"fechaini"	=> $fechaini,
	"fechafin"	=> $fechafin,
	"pp"		=> $pp,
	"pagina"	=> $pagina
    );
    
    return MovpuntosModel::llamadaRemota("tmListado", $params);
  }

  function tmResumen($numerotarjeta,$fechaini,$fechafin){

	$query = '';
	$cond = '';
		if($fechaini=='0' and $fechafin =='0' and $numerotarjeta=='0'){
			$cond=" WHERE t.nu_tarjeta_numero=0";
		}else{
			$cond = " WHERE t.nu_tarjeta_numero =".pg_escape_string($numerotarjeta).
				" AND to_date(to_char(mp.dt_punto_fecha,'dd/mm/yyyy'),'dd/mm/yyyy') BETWEEN to_date('".pg_escape_string($fechaini)."','dd/mm/yyyy') AND to_date('" .pg_escape_string($fechafin)."','dd/mm/yyyy') ";
		}
		
		$query = "SELECT mp.id_tarjeta,
				case mp.nu_punto_tipomov when 1 then 'Puntos' when 2 then 'Canje' 
							 when 3 then 'Vencimiento' when 4 then 'Retension' 
							 Else 'Otros' 
				end as tipo,
				 sum(mp.nu_punto_puntaje) as puntos 
			  FROM 
				prom_ta_movimiento_puntos mp
				inner join prom_ta_tarjetas t on (mp.id_tarjeta = t.id_tarjeta)
				left join int_articulos a on (mp.ch_trans_codigo = a.art_codigo) "
			.$cond.
			"group by mp.id_tarjeta,mp.nu_punto_tipomov ORDER BY 
				mp.id_tarjeta  
			";

    $params = array(
	"numerotarjeta"	=> $numerotarjeta,
	"fechaini"	=> $fechaini,
	"fechafin"	=> $fechafin
    );
    
    return MovpuntosModel::llamadaRemota("tmResumen", $params);
  }

  
}

