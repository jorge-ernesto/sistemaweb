<?php
/*
	FUNCION PARA PAGINA EL CUERPO DE UN REPORTE SEGUN 
	EL NRO DE LINEAS SETEADO POR PARAMETRO;
	
		$cabecera = url del archivo TXT que contiene la cabecera del reporte
		$cuerpo = url del archivo TXT que contiene el detalle del reporte
		$lineas = al nro de lineas que tendrá el cuerpo del reporte sin contar la cabecera
		$archivo_final = Reportes TXT generado al final con la paginacion respectiva
*/

function formarReporte($cabecera, $cuerpo, $lineas, $archivo_final){
	//$lineas = 4;
	$new_page = "
";
    $archivo_cuerpo = file($cuerpo);
	$archivo_cabecera = file($cabecera);

	$nro_lineas_archivo = count($archivo_cuerpo);
	echo $nro_lineas_archivo." ";
	//$nro_paginas = round(($nro_lineas_archivo/$lineas),0);
	$nro_paginas = ceil($nro_lineas_archivo/$lineas);
	
	echo "IMPRIMIENDO ".$nro_paginas." PAGINAS !<P>";

	$posicion_actual = 0;
	$i = 0;

	$dia = date ("d/m/Y - H:i:s", time());

	while($i<$nro_paginas)	{
		if($i!=0) {
			$buffer_temp = $buffer_temp.$new_page;
		}

		$buffer_cabecera = "";
		for($cont=0; $cont<count($archivo_cabecera); $cont++)		{
			if($cont==0) {
				$buffer_cabecera = $buffer_cabecera.str_pad("PAG: ".($i+1)."/".$nro_paginas,60).str_pad($dia,70," ",STR_PAD_LEFT)."\n";
			}
			$buffer_cabecera = $buffer_cabecera.$archivo_cabecera[$cont];
		}

		$buffer_temp = $buffer_temp.$buffer_cabecera;
		$buffer_cuerpo = "";
		for($cont=0; $cont<$lineas; $cont++)		{
			$buffer_cuerpo = $buffer_cuerpo.$archivo_cuerpo[$posicion_actual];
			$posicion_actual++;
		}
		$buffer_temp = $buffer_temp.$buffer_cuerpo;
		$i++;
	}
//	echo $buffer_temp."<br>";
	$ft=fopen($archivo_final,'w');
	fwrite($ft,$buffer_temp.$new_page);
	fclose($ft);
}
