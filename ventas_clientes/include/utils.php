	<?php
include "fpdf.php";

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
	//$cadena = substr_replace(trim($cadena),"",-1);
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

//FUNCIONES PARA USO DE POSTSCRIPT

function crearPostscript($archivo_txt,$archivo_resultante,$cabecera){
global $usuario;
	$filas = file($archivo_txt);
	$pdf = new FPDF("Landscape", "pt", "A4");
	$pdf->SetFont("Courier", "", 6);
	$pdf->SetLeftMargin(1);
	$pdf->SetTopMargin(1);
	$pdf->SetAutoPageBreak(true, 1);
	$pdf->AddPage();

	$pagina = 0;
	$Nombre_usuario = $usuario->obtenerUsuario();
	$sucursal = $_SESSION["almacen"];
	$fecha = date("d/m/Y H:i:s");
	
	for($i=0;$i<count($filas);$i++){

	    $cadena = $filas[$i];
	
	    // Al cambio de pagina, imprime cabecera
	    if ($pagina != $pdf->PageNo()) {
		$pagina++;

		for ($a = 0; $a < count($cabecera); $a++) {
		    $cabecera_1 = str_replace("{pagina}",$pagina,$cabecera[$a]);
		    $cabecera_1 = str_replace("{usuario}",$Nombre_usuario,$cabecera_1);
		    $cabecera_1 = str_replace("{sucursal}",$sucursal,$cabecera_1);
		    $cabecera_1 = str_replace("{fecha}",$fecha,$cabecera_1);
		    $pdf->Cell(0, 8, $cabecera_1);
		    $pdf->Ln();
		}
	    }    

	    $pdf->Cell(0, 8, $cadena);
	    $pdf->Ln();
	}
		    
	$pdf->Output($archivo_resultante, "F");

}


