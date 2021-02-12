<?php

include 'CNumeroaLetra.php';
function cuadrarArreglo($A,$num_filas){
    
    $B = null;
    for($i=0;$i<=$num_filas;$i++){
	if( isset($A[$i]) ){
	    $B[$i] = $A[$i];
	}else{
	    $B[$i] = "\n";
	}
    }
return $B;
}

function juntarDatos($datos,$Y){
    
    $cadena="";
    for($i=0;$i<count($datos);$i++){
	$fila = $datos[$i];
	if($fila["Y"]==$Y){
	    $cadena = $cadena.(str_repeat(" ",$fila["X"]-strlen($cadena))).$fila["data"];
	}
    }

    return $cadena;
}	

function crearTextoXY($datos){

    $man_arch = fopen("archivo.txt","w");
    $buf="";
    $linea = -1;
    $linea_vacia=-1;
    $A = null;
    
    for($i=0;$i<count($datos);$i++){
	
	$fila = $datos[$i];
	if($linea != $fila["Y"]){
	    
	    $linea = $fila["Y"];
	    //echo $linea.(juntarDatos($datos,$linea))."\n";
	    $linea_vacia = $linea;
	    $A[$linea] = (juntarDatos($datos,$linea))."\n";
	}else{
	    //++$linea_vacia;
	    //echo "-".($linea_vacia)."\n";
	    
	}
	
    }
    
    $A = cuadrarArreglo($A,$linea);
    
    for($i=0;$i<count($A);$i++){
		echo $A[$i];
		$buf = $buf.$A[$i];
    }
    
    echo "Lineas ".$linea."\n";
    
    fwrite($man_arch,$buf);
    fclose($man_arch);

}

function crearArchivoXY($datos,$archivo_txt,$clineas=''){
	$man_arch = fopen($archivo_txt,"w");
    	$buf="";
    	$linea = -1;
    	$linea_vacia=-1;
    	$A = null;
    
    	for($i=0;$i<count($datos);$i++){	
		$fila = $datos[$i];
		if($linea != $fila["Y"]){		    
		    	$linea = $fila["Y"];
		    	//echo $linea.(juntarDatos($datos,$linea))."\n";
		    	$linea_vacia = $linea;
		    	$A[$linea] = (juntarDatos($datos,$linea))."\n";
		}else{
		    	//++$linea_vacia;
		    	//echo "-".($linea_vacia)."\n";		    
		}	
	}	    
    	$A = cuadrarArreglo($A,$linea);
    
    	for($i=0;$i<count($A);$i++){
		//echo $A[$i];
		$buf = $buf.$A[$i];
    	}
    	
    	for($x=1; $x<=$clineas; $x++) {
      		$buf .="\n";
    	}
   	//echo "Lineas ".$linea."\n";
    
    	fwrite($man_arch,$buf);
    	fclose($man_arch);
}

function  cantidad_vales ($num_liquidacion){
$rs = pg_exec("select count(*) from val_ta_cabecera  where ch_liquidacion='$num_liquidacion'");
return pg_result($rs,0,0);
}

//funciones agregadas
function numeroEnLetras($n,$moneda){

	$numalet = new CNumeroaLetra();
	$numalet->setNumero($n);
	$numalet->setMayusculas(1);
	$numalet->setGenero(1);

	//anulado solo para Ultracom El Milagro JCP 16.10.2008
/*	if ($moneda=='01')
    		$numalet->setMoneda("SOLES");
	else
		$numalet->setMoneda("DOLARES AMERICANOS");*/

	//Geancarlos tomar denominación por B.D
	$rs = pg_exec("SELECT tab_descripcion FROM int_tabla_general WHERE tab_tabla ='04' and tab_elemento != '000000' AND tab_elemento = (LPAD(CAST('$moneda' AS bpchar),6,'0'));");
	$desc_moneda = pg_result($rs,0,0);

	if ($moneda=='01')
    		$numalet->setMoneda($desc_moneda);
	else
		$numalet->setMoneda($desc_moneda);

	$numalet->setPrefijo('');
	$numalet->setSufijo('');

	return $numalet->letra();
}

function aplicarMascara($numero,$mascara){

	if($numero=="" || $numero==null){ $numero=0;}
	$sql = "select substring( to_char($numero , '$mascara') for (length(to_char($numero , '$mascara'))-1) from  2)";
//	echo $sql . "\n";
	$rs = pg_exec($sql);
	return pg_result($rs,0,0);
}

// FUNCIONES DE CONVERSION DE NUMEROS A LETRAS

 





//FIN DE LAS FUNCIONES PARA CONVERTIR A NUMERO


/*
$x["X"] = 0;
$x["Y"] = 0;
$x["data"] = "Miguel";

$y["X"] = 20;
$y["Y"] = 1;
$y["data"] = "Angel";

$p["X"] = 40;
$p["Y"] = 1;
$p["data"] = "Angel2";

$z["X"] = 10;
$z["Y"] = 4;
$z["data"] = "Lam";

$w["X"] = 20;
$w["Y"] = 4;
$w["data"] = "Sedano";

$q["X"] = 30;
$q["Y"] = 4;
$q["data"] = "Sedano 2 ";


$datos[0] = $x;
$datos[1] = $y;
$datos[2] = $p;
$datos[3] = $z;
$datos[4] = $w;
$datos[5] = $q;

crearTextoXY($datos);

//echo strlen($x)."\n";
*/


