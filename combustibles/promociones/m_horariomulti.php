<?php

//print 'entrando a model canje';
class HorarioMultiModel extends Model{
        function llamadaRemota($procedimiento, $parametros)
        {
            global $sqlca;

            $sql = "select par_valor from int_parametros where par_nombre='master_puntos';";
            $sqlca->query($sql);
            $row = $sqlca->fetchRow();
            $ip = $row[0];

            $url = "http://" . $ip . "/sistemaweb/puntos/index.php?action=horariomulti&proc=" . urlencode($procedimiento);

            foreach($parametros as $parametro=>$valor) {
                $url .= "&" . $parametro . "=" . urlencode($valor);
            }

            $fh = fopen($url,"rb");
	    if ($fh===FALSE)
		return FALSE;

            $res = '';

            while (!feof($fh)) {
                $res .= fread($fh, 8192);
            }

            fclose($fh);

            return unserialize($res);
        }

	function ingresarHorario($idcampania,$descripcion,$diamulti,
							 $horaini,$minutoini,$horafin,$minutofin,
							 $factormulti,$usuario,$sucursal){
	    $params = array(
		"idcampania"	=> $idcampania,
		"descripcion"	=> $descripcion,
		"diamulti"	=> $diamulti,
		"horaini"	=> $horaini,
		"minutoini"	=> $minutoini,
		"horafin"	=> $horafin,
		"minutofin"	=> $minutofin,
		"factormulti"	=> $factormulti,
		"usuario"	=> $usuario,
		"sucursal"	=> $sucursal
	    );
	    
	    return HorarioMultiModel::llamadaRemota("ingresarHorario", $params);
	}

	function actualizarHorario($idhorariomulti,$idcampania,$descripcion,$diamulti,
							   $horaini,$minutoini,$horafin,$minutofin,
							   $factormulti,$usuario,$sucursal){
	    $params = array(
		"idhorariomulti"	=> $idhorariomulti,
		"idcampania"	=> $idcampania,
		"descripcion"	=> $descripcion,
		"diamulti"	=> $diamulti,
		"horaini"	=> $horaini,
		"minutoini"	=> $minutoini,
		"horafin"	=> $horafin,
		"minutofin"	=> $minutofin,
		"factormulti"	=> $factormulti,
		"usuario"	=> $usuario,
		"sucursal"	=> $sucursal
	    );
	    
	    return HorarioMultiModel::llamadaRemota("actualizarHorario", $params);
	}

	function eliminarHorario($idHorario){
	    $params = array(
		"idhorario"	=> $idHorario
	    );
	    
	    return HorarioMultiModel::llamadaRemota("eliminarHorario", $params);
	}

	function tmListado($filtro,$pp, $pagina){
	    $params = array(
		"filtro"	=> $filtro,
		"pp"		=> $pp,
		"pagina"	=> $pagina
	    );
	    
	    return HorarioMultiModel::llamadaRemota("tmListado", $params);
	}

  
}

