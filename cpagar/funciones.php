<?php
function separarCadena($cadena,$separador){

	$A = strtok($cadena, $separador);
	$i = 0;
	while($A){
		//echo $A."<br>";
		$R[$i] = $A;
		$A = strtok($separador);
		$i++;
	}

return $R;
}

function sumatoriaArray($AR,$campo){
	$S="";
	for($i=0;$i<count($AR);$i++){
		$A = $AR[$i];
		$S = $S + $A[$campo] ; 
	}
	
	return $S;
}
