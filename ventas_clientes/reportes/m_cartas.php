<?php
  // Modelo para Reportes 

Class CartasModel extends Model{

	function verificarRango($desde, $hasta){
		if ($desde == $hasta){
			$lista[] = $desde;
		}elseif ($desde < $hasta){
			for ($i=$desde; $i<=$hasta; $i++){
				$lista[]= (int)$i;
			}
		}else{
			$lista = 'bcbbcvb';
			return $lista;
		}
		$cadenafinal ='';
		foreach ($lista as $k => $v){
			$cadena = '000000'.$v;
			$long = strlen($cadena);
			$lista[$k] = substr($cadena,$long-7,7);
			$cadenafinal .= $lista[$k];
		}
		
		return array('0'=>$cadenafinal);
	}
  
	function obtenerCarta($codigo){
		global $sqlca;
		//print_r($codigo);
	    $sqlca->query("BEGIN");
	    print_r("select imprimir_cartas('".$codigo."','ret')");
	    $sqlca->functionDB("imprimir_cartas('".$codigo."','ret')");
	    $sqlca->query("FETCH ALL IN ret", 'registros');
	    $sqlca->query("CLOSE ret");
	    $sqlca->query("END");
	    while($reg = $sqlca->fetchRow('registros')){
	    	$cbArray[]=$reg;
	    }
	    //print_r($reg);
	    return $cbArray;
	}
}

