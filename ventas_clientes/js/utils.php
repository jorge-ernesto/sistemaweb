	<?php
function contarCaracteresArchivo($archivo,$busqueda){
	$ar_archivo = file($archivo);
	$cnt = 0;
	for($i=0;$i<count($ar_archivo);$i++){
		
		if(substr_count($ar_archivo[$i],$busqueda)==1){
			$cnt++;
		}
	
	}
return $cnt;
}
	
function columnaVales($ch_numeval,$longitud_pad,$tipo_pad){
	$cadena = $ch_numeval;
	$cadena = substr_replace(trim($cadena),"",-1);
	$cadena = str_replace("<br>","\n",$cadena);
    //$longitud_pad = 40;
    $filas = explode(",",$cadena);
    $pad = "";
    $cad1 =  "";
    $num_filas = 0;
    for($i=0;$i<count($filas);$i++){

        $cad1 = $cad1.$filas[$i].",";

        if(($i+1)%3==0 && $i!=0){
            $num_filas++;

            //$pad = str_repeat("K", (40-strlen($cad1)+2));

            $salida[count($salida)] = $cad1;
            $cad1 = "";
        }

    }
    if($num_filas==0 && $i<1){$salida=$filas;}

    if(strlen($cad1)!=0){

        //$pad = str_repeat("Z", (40-strlen($cad1))+2);
        $salida[count($salida)] = $cad1;

    }

    //echo "\nSon ".count($salida)." filas\n";
 #AHORA HACEMOS EL PAD DE LA CADENA

    for($i=0;$i<count($salida);$i++){

        $cad1 = $salida[$i];
        if(substr_count($cad1,"\n")>0){
        $pad = str_repeat(" ", ($longitud_pad-strlen($cad1)+1));
        }else{
        $pad = str_repeat(" ", ($longitud_pad-strlen($cad1)));
        }
        $salida[$i] = $cad1.$pad;

    }


    //$cadena = implode($salida);
    //echo $cadena;
	$A["dato"] = implode($salida);

return $A;
}



function FormatearReporte($archivo,$cabecera,$num_filas,$archivo_salida,$salto){
	//$salto = chr(12);
	$filas = file($archivo);
	$reporte = "";
	$numero_pagina = 1;
	$usuario = $_SESSION["usuario"];
	$sucursal = $_SESSION["almacen"];
	$fecha = date("d/m/Y H:i:s");
	for($i=0;$i<count($filas);$i++){
		
			$reporte =  $reporte.$filas[$i];
			if( ($i%$num_filas==0) && ($i>=$num_filas) ){
				$numero_pagina++;
				$cabecera_1 = str_replace("{pagina}",$numero_pagina,$cabecera);
				$cabecera_1 = str_replace("{usuario}",$usuario,$cabecera_1);
				$cabecera_1 = str_replace("{sucursal}",$sucursal,$cabecera_1);
				$cabecera_1 = str_replace("{fecha}",$fecha,$cabecera_1);
				$reporte =  $reporte.$salto."\n".$cabecera_1;
			}
	}	

	$reporte =  $reporte.$salto;
	
	//Reemplazamos la primera cabecera con sus datos complementarios
	$reporte = str_replace("{pagina}",1,$reporte);
	$reporte = str_replace("{usuario}",$usuario,$reporte);
	$reporte = str_replace("{sucursal}",$sucursal,$reporte);
	$reporte = str_replace("{fecha}",$fecha,$reporte);
	
	$man_arch = fopen($archivo_salida,"w");
	fwrite($man_arch,$reporte);
	fclose($man_arch);

return $archivo_salida;
}

//FUNCIONES PARA USO DE POSTSCRIPT

function crearPostscript($archivo_txt,$archivo_resultante,$salto_pagina){

	//$array_txt = explode($salto_pagina,readfile($archivo_txt));
	$num_paginas = 9;
	$num_paginas = contarCaracteresArchivo($archivo_txt,$salto_pagina);
	$pagina_actual = 1;

	$linea = 3;
	$filas = file($archivo_txt);
	$nuevo = fopen($archivo_resultante,"w");
	//echo $filas[0]."\n";
	fputs($nuevo,iniciarPS($num_paginas));
	for($i=0;$i<count($filas);$i++){

        $cadena = $filas[$i];
        //echo $filas[$i]."\n";
        $cadena = ps_agregar_fila($cadena,$linea);

		if(substr_count($cadena,$salto_pagina)>0 && ($i!=count($filas)-1) ){
			$pagina_actual++;
			$cadena = str_replace($salto_pagina," ",$cadena);
			$cadena = nuevaPaginaPS($pagina_actual).$cadena;
			$linea = 2;
		}

        fputs($nuevo,$cadena);
        $linea++;

	}

	fputs($nuevo,cerrarPS());
	fclose($nuevo);

}

function ps_agregar_fila($cadena,$linea){

	$x = $linea*6.7;
$cadena = "
gsave
$x 12 moveto
90 rotate
1.0017 1.6 scale
($cadena) show
grestore
";

return $cadena;
}

function iniciarPS($num_paginas){
        $cab = "%!PS-Adobe-2.0
%%Pages: $num_paginas
/Courier10 findfont   % Get the basic font
5.3 scalefont            % Scale the font to 20 points
setfont                 % Make it the current font
newpath                 % Start a new path

%%Page: (1) 1

";

        $fin = "showpage";

        //$cadena = $cab.$cadena.$fin;

return $cab;
}


function cerrarPS(){
        $fin = "showpage";
return $fin;
}

function nuevaPaginaPS($num_pagina){

$nueva_pag = "gsave
showpage
%%Page: ($num_pagina) $num_pagina";
return $nueva_pag;
}

?>