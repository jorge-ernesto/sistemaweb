<?php
/** rs -> resultset
    L  -> Array con las longitudes para cada campo
    nombre_fichero -> Es el nombre del archivo que se generara
    titulo --> es el titulo del reporte
*/
//Comando para imprimir por samba
//exec("smbclient //server14nw/epson -c 'print /tmp/archivo.txt' -P -N -I 192.168.1.1 ");

function imprimirTexto($rs,$L,$nombre_fichero,$titulo) {
	for($i=0;$i<count($L);$i++) {
		$long_fila = $long_fila+$L[$i];
	}

	if($long_fila<=130) {
		$titulo = $titulo."<br>";
		imprimir1($rs,$L,$nombre_fichero,$titulo);
	} else {
?>
		<script>
		alert('Demasiados campos, el ancho es maximo 130 caracteres');
		</script>

<?php
    
    }

}
//Imprime a texto basado en un result set

function imprimir1($rs,$L,$nombre_fichero,$titulo){

$MAX_ANCHO = 130;
$MAX_LARGO = 56;

$ANCHO = 130;
$LARGO = 56;

echo "<table>";
$fichero = "<table>";

for($i=0;$i<count($L);$i++){
    $long_fila = $long_fila+$L[$i];
}
$ANCHO = $long_fila;
$numcols = 0;
for($i=0;$i<pg_numrows($rs);$i++){
    echo "<tr>";
    $fichero = $fichero."<tr>";
    $A = pg_fetch_array($rs,$i);
    echo "cuenta".count($A);
    $numcols = count($A)/2;
    
    $fila = "*";
    
    
    
    /*Para la data*/
    for($a=0;$a<count($A)/2;$a++){
	echo "<td>";
	$fichero = $fichero."<td>".str_pad($A[$a],$L[$a])."</td>";
	echo $A[$a];
	echo "</td>";
	$fila = "<td>".str_pad($A[$a],$L[$a])."</td>";
	
    }
    
    

    $fila = "*";
    
    echo "</tr>";
    $fichero = $fichero."</tr>";
}

/*Para los nombres de los campos*/
    $cabe = "<tr>".$cabe;
	for($a=0;$a<count($A)/2;$a++){
	    echo "<td>";
	    $cabe = $cabe."<td>".str_pad(pg_field_name($rs,$a),$L[$a] )."</td>";
	    echo $A[$a];
	    echo "</td>";
	    
	
	}
    $cabe = $cabe."</tr>";
    /********************************/
    $fichero = $cabe.$fichero;

    /**Para ponerle el titulo al reporte*/
    $fichero = $titulo.$fichero;



$long_fila = $long_fila + $numcols;
$ANCHO = $ANCHO + $numcols;

$fichero = $fichero."</table>";
echo "</table>";

$linea = "+";
$linea = completarCaracteres($linea,"-",$ANCHO,"R");
$linea = $linea."+";

$barra = "===";
$barra = completarCaracteres($barra,"=",$ANCHO,"L"); 
//**************DESDE AQUI LA FUNCION PARA LA IMPRESION A TEXTO********///

echo "-----------> ".($ANCHO-$long_fila);
$espacios = str_pad("",$ANCHO-$long_fila);

$b = str_replace("miguel","--------------", "campo miguelsssss campo");
echo $b;

$fichero = str_replace("<table>" ,$linea."\n",$fichero);
$fichero = str_replace("</table>",$linea,$fichero);
$fichero = str_replace("<tr>","",$fichero);
$fichero = str_replace("</tr>","* \n",$fichero);
$fichero = str_replace("*",$espacios,$fichero);
$fichero = str_replace("<td>"," ",$fichero);
$fichero = str_replace("</td>","",$fichero);
$fichero = str_replace("<br>","\n".$barra."\n",$fichero);




$cabe = str_replace("<tr>","",$cabe);
$cabe = str_replace("</tr>","* \n",$cabe);
$cabe = str_replace("<td>"," ",$cabe);
$cabe = str_replace("</td>","",$cabe);
$cabe = str_replace("*",$espacios,$cabe);



$handler = fopen($nombre_fichero."-tmp","w+");
fwrite($handler,$fichero);
fclose($handler);


$tmp_file = file($nombre_fichero."-tmp");

echo "Fichero lines : ".count($tmp_file);

$new_page = "NP\n";
$new_page = "
";
$salida = "";
if(count($tmp_file)>$MAX_LARGO){

    for($i=0;$i<count($tmp_file);$i++){
	$salida = $salida.$tmp_file[$i];
	
	if( (($i % $MAX_LARGO) == 0 ) && $i!=0  ){
	    echo "carajo";
	    $salida = $salida.$new_page;
	    $salida = $salida.$cabe;
	}
    }
    
    $handler = fopen($nombre_fichero,"w+");
    fwrite($handler,$salida);
    fclose($handler);
    

}



////****************//
}




//Imprime a texto basado en una tabla html limpia pero sin cabeceras de campos
/*******imprimir2*********/

function imprimir2($variable,$L,$C,$nombre_fichero,$titulo){

$MAX_ANCHO = 130;
$MAX_LARGO = 56;

$ANCHO = 130;
$LARGO = 56;

$new_page = "NP\n";
$new_page = "
";
$salida = "";

//CABECERA
$cabe = "";
for($i=0;$i<count($C);$i++){
    $x = str_pad($C[$i] , $L[$i]);
    $cabe = $cabe.$x;
}


$handler = fopen($nombre_fichero."-tmp1","w+");
fwrite($handler,$variable);
fclose($handler);

$tmp_file = file($nombre_fichero."-tmp1");
//FICHERO
$fichero = "";
for($i=0;$i<count($tmp_file);$i++){
    
    if( ($i!=0) && ($MAX_ANCHO%$i==0) ){
	$fichero = $fichero.$new_line;
	$fichero = $fichero."\n".$cabe."\n";	
    }
	$fichero = $fichero.$tmp_file[$i];
    
}
//AGREGAMOS EL TITULO
$fichero = $titulo."\n".$fichero;

$fichero = str_replace("<table>" ,$linea."\n",$fichero);
$fichero = str_replace("</table>",$new_page,$fichero);
$fichero = str_replace("<tr>","",$fichero);
$fichero = str_replace("</tr>"," \n",$fichero);
//$fichero = str_replace("*",$espacios,$fichero);
$fichero = str_replace("<td>"," ",$fichero);
$fichero = str_replace("</td>","",$fichero);
$fichero = str_replace("<br>","\n".$barra."\n",$fichero);


$handler = fopen($nombre_fichero,"w+");
fwrite($handler,$fichero);
fclose($handler);
	
}

/*
$L es un array con el ancho de las columnas
*/
function imprimir3($variable,$nombre_fichero,$flg){

    $raya = "==================================================================================================================";
    
    $MAX_ANCHO = 130;
    $MAX_LARGO = 56;

    $ANCHO = 130;
    $LARGO = 56;

    $new_page = "NP\n";
    $new_page = "
";
    $salida = "";



    $handler = fopen($nombre_fichero."-tmp1","w+");
    fwrite($handler,$variable);
    fclose($handler);

    $tmp_file = file($nombre_fichero."-tmp1");
    //FICHERO
    $fichero = "";
    for($i=0;$i<count($tmp_file);$i++){
    
	if( ($i!=0) && ($MAX_ANCHO%$i==0) ){
	    $fichero = $fichero.$new_line;
	}
	
	$fichero = $fichero.$tmp_file[$i];
    
    }
    //AGREGAMOS EL TITULO
    $fichero = $titulo."\n".$fichero;

    $fichero = str_replace("<table>",$linea."",$fichero);
    $fichero = str_replace("</table>",$linea,$fichero);
    $fichero = str_replace("<tr>","",$fichero);
    $fichero = str_replace("</tr>","\n",$fichero);
    $fichero = str_replace("<p>","",$fichero);
    $fichero = str_replace("</p>","\n",$fichero);
    $fichero = str_replace("<b>","",$fichero);
    $fichero = str_replace("</b>","",$fichero);
    //$fichero = str_replace("*",$espacios,$fichero);
    $fichero = str_replace("<td>","",$fichero);
    $fichero = str_replace("</td>","",$fichero);
    $fichero = str_replace("<br>","",$fichero);


    $fichero = str_replace("<p align='center'>","\n",$fichero);
    $fichero = str_replace("raya",$raya,$fichero);
    $fichero = str_replace("<strong>","",$fichero);
    $fichero = str_replace("</strong>","",$fichero);

    $handler = fopen($nombre_fichero,"w+");
    fwrite($handler,$fichero);
    fclose($handler);	
    
    if($flg){
    /*Agregado para la paginacion*/
    paginarReporte($nombre_fichero);
    /*Agregado para la paginacion*/
    }
}


/**/
/*
$L es un array con el ancho de las columnas
*/
function imprimir4($variable,$nombre_fichero,$flg,$cabecera_txt){

    $raya = "==================================================================================================================";
    
    $MAX_ANCHO = 130;
    $MAX_LARGO = 56;

    $ANCHO = 130;
    $LARGO = 56;

    $new_page = "NP\n";
    $new_page = "
";
    $salida = "";



    $handler = fopen($nombre_fichero."-tmp1","w+");
    fwrite($handler,$variable);
    fclose($handler);

    $tmp_file = file($nombre_fichero."-tmp1");
    //FICHERO
    $fichero = "";
    for($i=0;$i<count($tmp_file);$i++){
    
	if( ($i!=0) && ($MAX_ANCHO%$i==0) ){
	    $fichero = $fichero.$new_line;
	}
	
	$fichero = $fichero.$tmp_file[$i];
    
    }
    //AGREGAMOS EL TITULO
    $fichero = $titulo."\n".$fichero;

    $fichero = str_replace("<table>",$linea."",$fichero);
    $fichero = str_replace("</table>",$linea,$fichero);
    $fichero = str_replace("<tr>","",$fichero);
    $fichero = str_replace("</tr>","\n",$fichero);
    $fichero = str_replace("<p>","",$fichero);
    $fichero = str_replace("</p>","\n",$fichero);
    $fichero = str_replace("<b>","",$fichero);
    $fichero = str_replace("</b>","",$fichero);
    //$fichero = str_replace("*",$espacios,$fichero);
    $fichero = str_replace("<td>","",$fichero);
    $fichero = str_replace("</td>","",$fichero);
    $fichero = str_replace("<br>","",$fichero);


    $fichero = str_replace("<p align='center'>","\n",$fichero);
    $fichero = str_replace("raya",$raya,$fichero);
    $fichero = str_replace("<strong>","",$fichero);
    $fichero = str_replace("</strong>","",$fichero);


    $cabecera_txt = str_replace("<tr>","",$cabecera_txt);
    $cabecera_txt = str_replace("</tr>","",$cabecera_txt);
    $cabecera_txt = str_replace("<td>","",$cabecera_txt);
    $cabecera_txt = str_replace("</td>","",$cabecera_txt);
        

    $handler = fopen($nombre_fichero,"w+");
    fwrite($handler,$fichero);
    fclose($handler);	
    
    if($flg){
    /*Agregado para la paginacion*/
    paginarReporte2($nombre_fichero,$cabecera_txt);
    /*Agregado para la paginacion*/
    }
}






function completarCaracteres($cadena,$comp,$longitud_final,$lado){

        $longitud_inicial = strlen($cadena);
	
	switch($lado){
	
	case "L" :
	    for($i=0;$i<$longitud_final-$longitud_inicial;$i++){
		$cadena = $comp.$cadena;
	    }
	    break;
	    
	case "R" :
	    for($i=0;$i<$longitud_final-$longitud_inicial;$i++){
		$cadena = $cadena.$comp;
	    }
	    break;
	
	}
	
	return $cadena;
	
}

//Convierte un result set a una tabla html pura
//T -> es un array con los nombres de los campos
function rs_a_html($rs,$T,$titulo){

	$cadena = $titulo."\n<table>";
	
	$cadena = $cadena."<tr>";
	
	for($i=0;$i<count($T);$i++){
	
	    $cadena = $cadena."<td>".$T[$i]."</td>";
	
	}
	
	$cadena = $cadena."</tr>";
        
	for($i=0;$i<pg_numrows($rs);$i++){
	    
	    $A = pg_fetch_array($rs,$i);
	    $cadena = $cadena."<tr>";
	    
	    for($a=0;$a<count($A)/2;$a++){
		$cadena = $cadena."<td>".$A[$a]."</td>";
	    }
	    
	    $cadena = str_replace("<p align='center'>","\n",$cadena);
	    
	    $cadena = $cadena."</tr>";
	}

	$cadena = $cadena."</table>";
	
	return $cadena;

}


function paginarReporte($path_archivo){
    $max = 50;    
    $new_page = "
";
    $linea =  str_repeat("-",20);
    //echo $linea;
    $archivo = file($path_archivo);
    $cnt = 0;
    $M[0] = "";
    //PRIMERO SACO CUANTAS DIVISIONES EXISTEN y EN QUE LINEAS ESTAN
    for($i=0;$i<count($archivo);$i++){
	$L = $archivo[$i];
	
	if(substr_count($L,$linea)){
	    
	    //print "Linea ".($i+1)." <br>";
	    $M[$cnt] = ($i+1);  
	    $cnt++; 
	}
	
    }
    
    //YA SE CUANTAS DIVISIONES HAY Y EN QUE LINEAS   
    
    $marca = $max;
    $cnt = 1;
    $SALTOS[0] = "0";
    for($i=0;$i<count($M);$i++){
	
	//$marca = $max * ($i+1);
	//print $marca."<br>";
	if($M[$i]>$marca){
	    $cnt++;
	    $marca = $max * ($cnt);
	    
	    //print "La linea ".$M[$i-1]." debe ir en otra pagina";
	    $SALTOS[$cnt-2]=$M[$i-1];
	}
    }
    
    
    for($i=0;$i<count($SALTOS);$i++){
	$archivo[$SALTOS[$i]] = $new_page.$archivo[$SALTOS[$i]]; 
    }
    
    
    $archivo_final = fopen($path_archivo,"w+");
    
    $txt = "";
    for($i=0;$i<count($archivo);$i++){
	$txt = $txt.$archivo[$i];
    }
    
    fwrite($archivo_final,$txt);
    fclose($archivo_final);
    
    }



function paginarReporte2($path_archivo,$cabecera_txt){
    $max = 50;    
    $new_page = "
";
    $linea =  str_repeat("-",20);
    //echo $linea;
    $archivo = file($path_archivo);
    $cnt = 0;
    $M[0] = "";
    //PRIMERO SACO CUANTAS DIVISIONES EXISTEN y EN QUE LINEAS ESTAN
    for($i=0;$i<count($archivo);$i++){
	$L = $archivo[$i];
	
	if(substr_count($L,$linea)){
	    
	    //print "Linea ".($i+1)." <br>";
	    $M[$cnt] = ($i+1);  
	    $cnt++; 
	}
	
    }
    
    //YA SE CUANTAS DIVISIONES HAY Y EN QUE LINEAS   
    
    $marca = $max;
    $cnt = 1;
    $SALTOS[0] = "0";
    for($i=0;$i<count($M);$i++){
	
	//$marca = $max * ($i+1);
	//print $marca."<br>";
	if($M[$i]>$marca){
	    $cnt++;
	    $marca = $max * ($cnt);
	    
	    //print "La linea ".$M[$i-1]." debe ir en otra pagina";
	    $SALTOS[$cnt-2]=$M[$i-1];
	}
    }
    
    
    for($i=0;$i<count($SALTOS);$i++){
	$archivo[$SALTOS[$i]] = $new_page.$archivo[$SALTOS[$i]].$cabecera_txt; 
    }
    
    
    $archivo_final = fopen($path_archivo,"w+");
    
    $txt = "";
    for($i=0;$i<count($archivo);$i++){
	$txt = $txt.$archivo[$i];
    }
    
    fwrite($archivo_final,$txt);
    fclose($archivo_final);
    
    }

    /*REPORTE CREADO EXCLUSIVAMENTE PARA LA COCHINA CONSISTENCIA DE MOVIMIENTOS*/
	function imprimirConsistencia($rs1){
		
		 $reporte = "<table>";
   
    
	$tip_mov_act = "x";
	$num_mov_act = "x";
  	for($i=0;$i<pg_numrows($rs1);$i++){
  		$A = pg_fetch_array($rs1,$i);	
		$tip_mov = $A["tip_mov"];
		$num_mov = $A["num_movimiento"];
		
		/*Agregado para Tula y su descripcion de almacenes (acentos omitidos aproposito)*/
		    $rs_alma = pg_exec("select ch_nombre_breve_almacen as des_desc from inv_ta_almacenes 
		    where ch_almacen = '".$A["des"]."' ");
		    $ALMA = pg_fetch_array($rs_alma,0);
		    $destino = $A["des"]." - ".$ALMA['des_desc'];
		/*Agregado para Tula y su descripcion de almacenes*/
		
  	
    	$reporte = $reporte."<tr>"; 
      	if($tip_mov_act!=$tip_mov){
	  		$tip_mov_act  = $tip_mov ;
	  
	  		$reporte = $reporte."<td>(*)Tipo de Movimiento ".$tip_mov_act." ".$A['desc_movimiento']."</td>";
			$reporte = $reporte."<td></td>";
      		$reporte = $reporte."<td></td>";
      		$reporte = $reporte."<td></td>";
    		$reporte = $reporte."</tr>";
		
		$cab1 = "<tr><td>Movimiento Fecha</td><td>Orden</td><td>Proveedor</td></tr>";
	
		$cab2 = "<tr><td>Codigo</td><td>Descripcion</td><td>Cantidad</td><td>Costo</td><td>Total</td>";
		$cab2 = $cab2."<td>Origen</td>";
		$cab2 = $cab2."<td>Destino</td>";
		$cab2 = $cab2."<td>Doc. Ref</td>";
		$cab2 = $cab2."<td>Num. Doc. Ref</td></tr>";
		
		$reporte = $reporte.$cab1.$cab2;
		}
	
    	if($num_mov_act!=$num_mov) {	
				$num_mov_act = $num_mov;
		
    			$reporte = $reporte."<tr>"; 
      			$reporte = $reporte."<td>";
	        	$reporte = $reporte.$A['tip_mov']." - ".$A['num_movimiento']." - ".$A['fecha']."</td>";
    	  		$reporte = $reporte."<td>";
        		$reporte = $reporte.$A['orden_compra']."</td>";
      			$reporte = $reporte."<td>".$A['cod_prov']."</td><td></td><td></td></tr>";
     	} 
    
	  $reporte = $reporte."<tr>"; 
      $reporte = $reporte."<td>".$A['cod_producto']."</td>";
      $reporte = $reporte."<td>".$A['desc_producto']."</td>";
      $reporte = $reporte."<td>".$A['cantidad']."</td>";
      $reporte = $reporte."<td>".$A['costo_unitario']."</td>";
      $reporte = $reporte."<td>".$A['total']."</td>";
      $reporte = $reporte."<td>".$A['ori']."-".$A['desc_ori']."</td>";
      $reporte = $reporte."<td>".$destino."</td>";
      $reporte = $reporte."<td>".$A['tip_docurefe']."</td>";
      $reporte = $reporte."<td>".$A['num_docurefe']."</td>";
      $reporte = $reporte."</tr>";
    
	}
	$reporte = $reporte."</table>";
	
	/*Procesamos los tag de html para el texto*/
	$reporte = str_replace("<table>","",$reporte);
    $reporte = str_replace("</table>","",$reporte);
    $reporte = str_replace("<tr>","",$reporte);
    $reporte = str_replace("</tr>","\n",$reporte);
    $reporte = str_replace("<p>","",$reporte);
    $reporte = str_replace("</p>","\n",$reporte);
    $reporte = str_replace("<b>","",$reporte);
    $reporte = str_replace("</b>","",$reporte);
    //$fichero = str_replace("*",$espacios,$fichero);
    $reporte = str_replace("<td>","",$reporte);
    $reporte = str_replace("</td>","",$reporte);
    $reporte = str_replace("<br>","",$reporte);
	
	$handler = fopen("/tmp/carajo.txt","w+");
    fwrite($handler,$reporte);
    fclose($handler);
	
	
	}
