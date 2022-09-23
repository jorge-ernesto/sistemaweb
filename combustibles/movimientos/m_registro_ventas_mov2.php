<?php

class RegistroVentasMOVModel extends Model {

	function obtenerAlmacenes($almacen) {
		global $sqlca;
		
		$sql = "SELECT
			    	ch_almacen,
			    	ch_almacen||' - '||ch_nombre_almacen
				FROM
					inv_ta_almacenes
				WHERE
					ch_clase_almacen='1'
				ORDER BY
					ch_almacen;";
	
		if ($sqlca->query($sql) < 0) 
			return false;
	
		$result = Array();
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    	$a = $sqlca->fetchRow();		    
		    	$result[$a[0]] = $a[1];
		}
	
		return $result;
    }

    function Paginacion($pp, $pagina, $reporte, $almacen, $dia1, $dia2, $tipodoc, $articulo, $cliente, $serie, $numero) {
		global $sqlca;

		//Documentos de playa
		$condt="";
		$condarticuloT="";
		$condclienteT="";

		//Documentos manuales
		$cond="";		
		$condarticulo="";		
		$condcliente="";

		if (trim($tipodoc) != "") {
			//pos_trans
			if ($tipodoc == "B" || $tipodoc == "F") {
				$condt = "AND trans.td='" . pg_escape_string($tipodoc) . "'";
			} else if ($tipodoc == "N") {
				$condt = "AND trans.tm='A'";
			}

			//fac_ta_factura_cabecera
			if ($tipodoc == "F") {
				$cond = "AND TDOCU.tab_car_03 = '01' ";
			}else if ($tipodoc == "B"){
				$cond = "AND TDOCU.tab_car_03 = '03' ";
			}else if ($tipodoc == "N"){
				$cond = "AND TDOCU.tab_car_03 = '07' ";
			}			
		}	

		if (trim($articulo) != "") {
			$condarticuloT .= "AND art.art_codigo='" . pg_escape_string($articulo) . "' ";
			$condarticulo .= "AND CabD.art_codigo='" . pg_escape_string($articulo) . "' ";
		}
		
		if (trim($cliente) != "") {
			$condclienteT .= "AND trans.ruc='" . pg_escape_string($cliente) . "' ";
			$condcliente .= "AND CLI.cli_ruc='" . pg_escape_string($cliente) . "' ";
		}

		if (trim($serie) != "") {
			$condserieT .= "AND SUBSTR(TRIM(trans.usr), 0, 5) LIKE '%" . pg_escape_string($serie) . "%' ";
			$condserie .= "AND Cab.ch_fac_seriedocumento LIKE '%" . pg_escape_string($serie) . "%' ";
		}

		if (trim($numero) != "") {
			$condnumeroT .= "AND SUBSTR(TRIM(trans.usr), 6) LIKE '%" . pg_escape_string($numero) . "%' ";
			$condnumero .= "AND Cab.ch_fac_numerodocumento LIKE '%" . pg_escape_string($numero) . "%' ";
		}

		//Obtenemos tabla pos_trans
		$porciones      = explode("/", $dia1);
		$anio           = $porciones[2];
		$mes            = $porciones[1];
		$tabla_postrans = "pos_trans".$anio.$mes;

		$query = "
		(
			SELECT 
				CASE trans.td
					when 'F' then 'FACTURA'
					when 'B' then 'BOLETA'
					when 'N' then 'NOTA DESPACHO' 
				END as td,
				trans.usr,
				to_char(trans.dia, 'DD/MM/YYYY') as fecha_vencimiento,
				to_char(trans.dia, 'DD/MM/YYYY') as fecha_emision,
				'SOLES' as moneda,
				art.art_codigo,	
				trans.cantidad,
				trans.precio,
				(trans.importe -trans.igv) as base,
				trans.igv,
				trans.importe,
				trans.placa,
				trans.ruc,
				truc.razsocial,
				'S' as stock,
				to_char(trans.dia, 'DD/MM/YYYY') as fanulacion,
				'-' as ntcred,
				'USER' as usuario
			FROM
				$tabla_postrans trans
				LEFT JOIN ruc            AS truc ON (trans.ruc = truc.ruc)
				INNER JOIN int_articulos AS art  ON (trans.codigo = art.art_codigo)
			WHERE
				1 = 1
				AND trans.es = '".pg_escape_string($almacen)."' 
				AND trans.dia BETWEEN '".$dia1."' AND '".$dia2."'
				AND trans.tipo = 'C'			
				AND trans.td IN ('B','F')
				$condt
				$condarticuloT
				$condclienteT
				$condserieT
				$condnumeroT
		)
		UNION ALL
		(
			SELECT 
				TDOCU.tab_desc_breve as td, 
				CONCAT(Cab.ch_fac_seriedocumento,'-', Cab.ch_fac_numerodocumento) as usr,
				to_char(Cab.fe_vencimiento, 'DD/MM/YYYY') as fecha_vencimiento,
				to_char(Cab.dt_fac_fecha, 'DD/MM/YYYY') as fecha_emision,
				TMONE.tab_desc_breve as moneda,
				CabD.art_codigo,			
				CabD.nu_fac_cantidad as cantidad,			
				CabD.nu_fac_precio  as precio,			
				CabD.nu_fac_importeneto as base,			
				CabD.nu_fac_impuesto1 as igv,			
				CabD.nu_fac_valortotal as importe,
				VCOM.ch_fac_observacion1 as placa,
				CLI.cli_ruc as ruc,
				CLI.cli_razsocial as razsocial,
				Cab.ch_descargar_stock  as stock,
				to_char(Cab.dt_fac_fecha , 'DD/MM/YYYY') as fanulacion,
				'-' as ntcred,
				'USER' as usuario
			FROM
				fac_ta_factura_cabecera              AS Cab
				JOIN int_clientes                    AS CLI  USING (cli_codigo)
				LEFT JOIN fac_ta_factura_detalle     AS CabD USING (ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento, cli_codigo)
				LEFT JOIN fac_ta_factura_complemento AS VCOM USING (ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento, cli_codigo)
				JOIN int_tabla_general               AS TDOCU   ON (SUBSTRING(TDOCU.tab_elemento, 5) = Cab.ch_fac_tipodocumento AND TDOCU.tab_tabla ='08' AND TDOCU.tab_elemento != '000000')
				JOIN int_tabla_general               AS TMONE   ON (SUBSTRING(TMONE.tab_elemento, 5) = Cab.ch_fac_moneda AND TMONE.tab_tabla ='04' AND TMONE.tab_elemento != '000000')
			WHERE
				1 = 1
				AND cab.ch_almacen = '". pg_escape_string($almacen) ."'
				AND Cab.dt_fac_fecha BETWEEN '" .$dia1. "' AND '" . $dia2. "'
				AND Cab.ch_fac_tipodocumento IN ('10', '35', '11', '20')
				AND CabD.art_codigo IN ('11620301','11620302','11620303','11620304','11620305','11620306','11620307','11620308')
				$cond
				$condarticulo
				$condcliente
				$condserie
				$condnumero
		)
		ORDER BY 4";

		// echo "<pre>query";
		// echo $query;
		// echo "</pre>";

		$sqlca->query($query);
		$numrows_1 = $sqlca->numrows();

		//Variables para almacenar información y variables para totalizar
		$resultado_1 = array();
		$total_articulos = array();
		$totBase = 0;
		$totIgv = 0;
		$totImpo = 0;

		//Recorremos informacion total sin limit/offset
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$resultado_1[$i]['td'] 			      = $a[0];
			$resultado_1[$i]['usr'] 			  = $a[1];
			$resultado_1[$i]['fecha_vencimiento'] = $a[2];
			$resultado_1[$i]['fecha_emision']     = $a[3];
			$resultado_1[$i]['moneda'] 		      = $a[4];
			$resultado_1[$i]['art_codigo'] 	      = $a[5];
			$resultado_1[$i]['cantidad'] 		  = $a[6];
			$resultado_1[$i]['precio'] 		      = $a[7];
			$resultado_1[$i]['base'] 			  = $a[8];
			$resultado_1[$i]['igv'] 			  = $a[9];
			$resultado_1[$i]['importe'] 		  = $a[10];
			$resultado_1[$i]['placa'] 		      = $a[11];
			$resultado_1[$i]['ruc'] 			  = $a[12];
			$resultado_1[$i]['razsocial'] 	      = $a[13];
			$resultado_1[$i]['stock'] 		      = $a[14];
			$resultado_1[$i]['fanulacion']	      = $a[15];
			$resultado_1[$i]['ntcred'] 		      = $a[16];
			$resultado_1[$i]['usuario'] 	      = $a[17];	

			//Totalizamos cantidades
			$totBase = $totBase + $a[8];
			$totIgv  = $totIgv  + $a[9];
			$totImpo = $totImpo + $a[10];

			$art_codigo = $a[5];
			$importe    = $a[10];
			$cantidad   = $a[6];

			//Totalizamos por articulos			
			$total_articulos[$art_codigo]['importe']  += $importe;
			$total_articulos[$art_codigo]['cantidad'] += $cantidad;
		}

		//Obtenemos informacion de paginacion
		$paginador = new paginador($numrows_1, $pp, $pagina);		
		$data_paginacion['partir'] 		     = $paginador->partir();
		$data_paginacion['fin'] 		     = $paginador->fin();
		$data_paginacion['numero_paginas'] 	 = $paginador->numero_paginas();
		$data_paginacion['pagina_previa'] 	 = $paginador->pagina_previa();
		$data_paginacion['pagina_siguiente'] = $paginador->pagina_siguiente();
		$data_paginacion['pp'] 		         = $paginador->pp;
		$data_paginacion['paginas'] 		 = $paginador->paginas();
		$data_paginacion['primera_pagina'] 	 = $paginador->primera_pagina();
		$data_paginacion['ultima_pagina'] 	 = $paginador->ultima_pagina();

		//Limit de paginacion
		if ($reporte == "HTML") {
			$query .= " LIMIT " . pg_escape_string($pp) . " ";
			$query .= " OFFSET " . pg_escape_string($paginador->partir());
		}		

		// echo "<pre>query paginador";
		// echo $query;
		// echo "</pre>";
		
		$sqlca->query($query);

		//Variables para almacenar información
		$listado = array();
		$resultado = array();

		//Recorremos informacion limitada por paginacion con limit/offset
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$resultado[$i]['td'] 			    = $a[0];
			$resultado[$i]['usr'] 			    = $a[1];
			$resultado[$i]['fecha_vencimiento'] = $a[2];
			$resultado[$i]['fecha_emision']     = $a[3];
			$resultado[$i]['moneda'] 		    = $a[4];
			$resultado[$i]['art_codigo'] 	    = $a[5];
			$resultado[$i]['cantidad'] 		    = $a[6];
			$resultado[$i]['precio'] 		    = $a[7];
			$resultado[$i]['base'] 			    = $a[8];
			$resultado[$i]['igv'] 			    = $a[9];
			$resultado[$i]['importe'] 		    = $a[10];
			$resultado[$i]['placa'] 		    = $a[11];
			$resultado[$i]['ruc'] 			    = $a[12];
			$resultado[$i]['razsocial'] 	    = $a[13];
			$resultado[$i]['stock'] 		    = $a[14];
			$resultado[$i]['fanulacion']	    = $a[15];
			$resultado[$i]['ntcred'] 		    = $a[16];
			$resultado[$i]['usuario'] 	        = $a[17];	
		}

		//Datos de paginacion
		$listado["datos"] = $resultado;		
		$listado['paginacion'] = $data_paginacion;
		
		//Datos de todos los registros
		$listado['resultado_1'] = $resultado_1;
		$listado["datos_totales"]['totales']['totBase'] = $totBase;
		$listado["datos_totales"]['totales']['totIgv'] 	= $totIgv;
		$listado["datos_totales"]['totales']['totImpo'] = $totImpo;
		$listado["datos_totales"]['totales']['articulos'] = $total_articulos;
		
		return $listado;
    }	

	function busquedaxx($almacen, $dia1, $dia2) {
		global $sqlca;
				
		

		$sql = "
		SELECT 
			CASE trans.td
			when 'F' then 'FACTURA'
			when 'B' then 'BOLETA'
			when 'N' then 'N/CRED.	' END as td,
			trans.usr,
			to_char(trans.fecha, 'DD/MM/YYYY') as fecha,
			to_char(trans.dia, 'DD/MM/YYYY') as dia,
			'SOLES' as moneda,
			art.art_codigo,	
			trans.cantidad,
			trans.precio,
			(trans.importe -trans.igv) as base,
			trans.igv,
			trans.importe,
			trans.placa,
			trans.ruc,
			truc.razsocial,
			'S' as stock,
			to_char(trans.dia, 'DD/MM/YYYY') as fanulacion,
			'-' as ntcred,
			'USER' as usuario	
			
		FROM
			pos_trans202008 trans
			LEFT JOIN ruc truc ON (trans.ruc=truc.ruc),
			int_articulos art
		WHERE
			art.art_codigo=trans.codigo
			AND trans.dia BETWEEN '2020-08-01' AND '2020-08-01'
			AND trans.tipo='C'
			AND trans.es='003' 

	
			

		SELECT 
			TDOCU.tab_desc_breve as td, 
			CONCAT(Cab.ch_fac_seriedocumento,'-', Cab.ch_fac_numerodocumento) as usr,
			to_char(Cab.dt_fac_fecha, 'DD/MM/YYYY') as fecha,
			to_char(Cab.dt_fac_fecha, 'DD/MM/YYYY') as dia,
			TMONE.tab_desc_breve as moneda,
			CabD.art_codigo,			
			CabD.nu_fac_cantidad as cantidad,			
			CabD.nu_fac_precio  as precio,			
			CabD.nu_fac_importeneto as base,			
			CabD.nu_fac_impuesto1 as igv,			
			CabD.nu_fac_valortotal as importe,
			VCOM.ch_fac_observacion1 as placa,
			CLI.cli_ruc as ruc,
			CLI.cli_razsocial as razsocial,
			Cab.ch_descargar_stock  as stock,
			to_char(Cab.dt_fac_fecha , 'DD/MM/YYYY') as fanulacion,
			'-' as ntcred,
			'USER' as usuario
		FROM
			fac_ta_factura_cabecera AS Cab
			JOIN int_clientes AS CLI USING (cli_codigo)
			LEFT JOIN fac_ta_factura_detalle  AS CabD
				USING (ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento, cli_codigo)
			LEFT JOIN fac_ta_factura_complemento AS VCOM
				USING (ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento, cli_codigo)
			JOIN int_tabla_general AS TDOCU
				ON (SUBSTRING(TDOCU.tab_elemento, 5) = Cab.ch_fac_tipodocumento AND TDOCU.tab_tabla ='08' AND TDOCU.tab_elemento != '000000')
			JOIN int_tabla_general AS TMONE
				ON (SUBSTRING(TMONE.tab_elemento, 5) = Cab.ch_fac_moneda AND TMONE.tab_tabla ='04' AND TMONE.tab_elemento != '000000')
		WHERE
			Cab.ch_fac_tipodocumento IN ('10', '35', '11', '20')
			AND CabD.art_codigo in ('11620301','11620302','11620303','11620304','11620305','11620306','11620307','11620308')
			AND cab.ch_almacen = '003'
			AND Cab.dt_fac_fecha BETWEEN '2020-08-01' AND '2020-08-01' ";
		

		if ($sqlca->query($sql) < 0) 
			return false;

		$resultado = Array();
		$res 	   = Array();

		$can    = 0;
		$sem    = 0;
		$totbase = 0;
		$totdol = 0;
		$semsol = 0;								
		$semdol = 0;
										
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();

		    	$resultado[$i]['td'] 	= $a[0];
		    	$resultado[$i]['usr'] 		= $a[1];
		    	$resultado[$i]['fecha'] 	= $a[2];
		    	$resultado[$i]['dia'] 		= $a[3];
		    	$resultado[$i]['moneda'] 	= $a[4];
		    	$resultado[$i]['art_codigo'] 	= $a[5];
		    	$resultado[$i]['cantidad'] 	= $a[6];
				$resultado[$i]['precio'] 	= $a[7];
				$resultado[$i]['base'] 	= $a[8];
		    	$resultado[$i]['igv'] 		= $a[9];
		    	$resultado[$i]['importe'] 		= $a[10];
		    	$resultado[$i]['placa'] 	= $a[11];
		    	$resultado[$i]['ruc'] 	= $a[12];
		    	$resultado[$i]['razsocial'] 	= $a[13];
		    	$resultado[$i]['stock']	= $a[14];
		    	$resultado[$i]['fanulacion'] 	= $a[15];
		    	$resultado[$i]['ntcred'] 		= $a[16];
				$resultado[$i]['usuario'] 	= $a[17];
				//$resultado[$i]['bilbil'] 	= $a[18];
				//$resultado[$i]['monmon'] 	= $a[19];
		    	
		    	/*if(trim($a[11])!="01" and $a[14]>0) {
		    		$resultado[$i]['denominacion'] 	= "Billetes";
					$billetes = $billetes + $a[13];
		    	} else {
			    	if($a[18]>0 and $a[19]==0){
						$resultado[$i]['denominacion'] 	= "Billetes";
					$billetes = $billetes + $a[13];
			    	} else {
			    		if($a[18]==0 and $a[19]>0){
							$resultado[$i]['denominacion'] 	= "Monedas";
						$monedas = $monedas + $a[13];
			    		} else {
			    			if($a[18]>0 and $a[19]>0){
								$resultado[$i]['denominacion'] 	= "B y M";
								$monedas = $monedas + $a[13];
			    			} else {
			    				$resultado[$i]['denominacion'] 	= "Ninguna";
			    			}
			    		}
			    	}
				}		    		
		    	
		    	if(trim($a[2])=="S" or trim($a[2])=="s") {		    	

					$sem++;	
					$semsol = $semsol + $a[13];								
					$semdol = $semdol + $a[14];

					if(trim($a[11])!="01" and $a[14]>0) {
						$sbilletes = $sbilletes + $a[13];
						} else {
							if($a[18]>0 and $a[19]==0){
							$sbilletes = $sbilletes + $a[13];
							} else {
								if($a[18]>0 and $a[19]>0){
									$resultado[$i]['denominacion'] 	= "B y M";
								$smonedas = $smonedas + $a[13];
								}
						}
					}
				}		

		    	$can++;
				$totsol = $totsol + $a[13];
				$totdol = $totdol + $a[14];	*/		
		}

		$res['detalles'] 	  	= $resultado;
		/*$res['totales']['sem'] 	  	= $sem;
		$res['totales']['semsol']	= $semsol;
		$res['totales']['semdol']	= $semdol;
		$res['totales']['can'] 	  	= $can;
		$res['totales']['totsol'] 	= $totsol;
		$res['totales']['totdol'] 	= $totdol;	
		$res['totales']['billetes'] 	= $billetes;
		$res['totales']['sbilletes'] 	= $sbilletes;
		$res['totales']['monedas']	= $monedas;
		$res['totales']['smonedas'] 	= $smonedas;*/
		

		return $res;
    }	
}
