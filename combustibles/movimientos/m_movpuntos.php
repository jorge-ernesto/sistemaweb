<?php

//print 'entrando a model canje';
Class MovpuntosModel extends Model{
	
        function llamadaRemota($procedimiento, $parametros)
        {
            global $sqlca;

            $sql = "select par_valor from int_parametros where par_nombre='master_puntos';";
            $sqlca->query($sql);
            $row = $sqlca->fetchRow();
            $ip = $row[0];

            $url = "http://" . $ip . "/sistemaweb/puntos/index.php?action=movpuntos&proc=" . urlencode($procedimiento);

            foreach($parametros as $parametro=>$valor) {
                $url .= "&" . $parametro . "=" . urlencode($valor);
            }

            $fh = fopen($url,"rb");

            $res = '';

            while (!feof($fh)) {
                $res .= fread($fh, 8192);
            }

            fclose($fh);
            echo $res;
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

  
}

