<?php
// Descomentar estas líneas, cuando estamos en modo - development
/*
error_reporting(-1);
ini_set('display_errors', 1);
*/
// Descomentar estas líneas, cuando estamos en modo - production

ini_set('display_errors', 0);
if (version_compare(PHP_VERSION, '5.3', '>='))
{
	error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
}
else
{
	error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE);
}


class VentasDiariasModel extends Model{

    function obtieneVentas($desde, $hasta, $estaciones, $bResumido){
		global $sqlca;
	
		$propiedad = $this->obtenerPropiedadAlmacenes($estaciones);
		$almacenes = $this->obtieneListaEstaciones();

		$where_codigo_almacen = ($estaciones != "" ? "AND CTC.ch_sucursal = '" . pg_escape_string($estaciones) . "'" : "");		
/*
	$sql = "
SELECT
 ch_sucursal,
 nu_ventavalor,
 nu_ventagalon,
 (nu_afericionveces_x_5*5) as nu_afericion,
 (SELECT ROUND(SUM(af.precio) / count(*), 2) AS af_cantidad FROM pos_ta_afericiones AS af WHERE
  af.codigo = ch_codigocombustible
  AND af.es = '" . pg_escape_string($estaciones) . "'
  AND af.dia BETWEEN to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') AND to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
 ) as nu_preciogalon,
 ch_codigocombustible,
 dt_fechaparte
FROM
 comb_ta_contometros
WHERE
 dt_fechaparte BETWEEN to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') AND to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
 AND nu_ventavalor > 0
 AND nu_ventagalon > 0
		";

		if ($estaciones != "TODAS") {
			$sql .= "AND ch_sucursal = '" . pg_escape_string($estaciones) . "'";
		}
	
		$sql .= "
ORDER BY
 ch_sucursal,
 dt_fechaparte,
 ch_numeroparte,
 ch_surtidor;
		";
*/

$sql = "
SELECT
 CTC.ch_sucursal,
 SUM(CTC.nu_ventavalor),
 SUM(CTC.nu_ventagalon),
 FIRST(PTA.cantidad) AS ss_afericion_cantidad,
 FIRST(PTA.importe) AS ss_afericion_soles,
 CTC.ch_codigocombustible,
 CTC.dt_fechaparte
FROM
 comb_ta_contometros AS CTC
 LEFT JOIN (
 SELECT
  dia,
  codigo,
  SUM(cantidad) AS cantidad,
  SUM(importe) AS importe
 FROM
  pos_ta_afericiones
 WHERE
  es = '" . pg_escape_string($estaciones) . "'
  AND dia BETWEEN to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') AND to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
 GROUP BY
  1,2
 ) AS PTA ON (CTC.dt_fechaparte = PTA.dia AND CTC.ch_codigocombustible = PTA.codigo)
WHERE
 CTC.dt_fechaparte BETWEEN to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') AND to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
 ". $where_codigo_almacen ."
 AND CTC.nu_ventavalor > 0
 AND CTC.nu_ventagalon > 0
GROUP BY
 1,6,7
ORDER BY
 ch_sucursal,
 dt_fechaparte;
";
error_log($sql);

	if ($sqlca->query($sql) < 0) return false;
	
	$result = Array();

	for ($i = 0; $i < $sqlca->numrows(); $i++) {
	    $a = $sqlca->fetchRow();

	    $ch_sucursal = trim($a[0]);
	    $nu_ventavalor = (float)$a[1];
	    $nu_ventagalon = (float)$a[2];
	    $fQuantityAfericion = (float)$a[3];
	    $fAmountAfericion = (float)$a[4];
	    $ch_codigocombustible = $a[5];
	    $dt_fechaparte = $a[6];
	    
	    $propio = ($propiedad[$ch_sucursal]=='S'?"ESTACION":"OTROS");
	    $ch_sucursal = $almacenes[$ch_sucursal];

	    /* Descuento de afericiones */
	    //$nu_ventagalon -= $nu_afericion;
	    //$nu_ventavalor -= ($nu_afericion*$nu_preciogalon);

	    $nu_ventagalon -= $fQuantityAfericion;
	    $nu_ventavalor -= $fAmountAfericion;

	    if ($ch_codigocombustible=='11620307') {
			// $nu_ventagalon=round($nu_ventagalon/3.785411784,2);
			$nu_ventagalon=round($nu_ventagalon/1);
	    }
	    
	    /* Si no esta resumido, totalizar venta por dia */
	    if (!$bResumido) {
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$dt_fechaparte][$ch_codigocombustible.'_galones'] += $nu_ventagalon;
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$dt_fechaparte][$ch_codigocombustible.'_importe'] += $nu_ventavalor;
			
			/* GLP no actualiza total de combustibles */
			if ($ch_codigocombustible!='11620307') {
			    @$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$dt_fechaparte]['total_galones'] += $nu_ventagalon;
			    @$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$dt_fechaparte]['total_importe'] += $nu_ventavalor;
			}

			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$dt_fechaparte]['total'] += $nu_ventavalor;
	    }
	    
	    /* Calcula total por CC */
	    @$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales'][$ch_codigocombustible.'_galones'] += $nu_ventagalon;
	    @$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales'][$ch_codigocombustible.'_importe'] += $nu_ventavalor;

	    if ($ch_codigocombustible!='11620307') {
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total_galones'] += $nu_ventagalon;
	        @$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total_importe'] += $nu_ventavalor;
	    }
	    @$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total'] += $nu_ventavalor;

	    /* Calcula total por Grupo */
	    @$result['propiedades'][$propio]['totales'][$ch_codigocombustible.'_galones'] += $nu_ventagalon;
	    @$result['propiedades'][$propio]['totales'][$ch_codigocombustible.'_importe'] += $nu_ventavalor;

	    if ($ch_codigocombustible!='11620307') {
			@$result['propiedades'][$propio]['totales']['total_galones'] += $nu_ventagalon;
			@$result['propiedades'][$propio]['totales']['total_importe'] += $nu_ventavalor;
	    }
	    @$result['propiedades'][$propio]['totales']['total'] += $nu_ventavalor;

	    /* Calcula total General */
	    @$result['totales'][$ch_codigocombustible.'_galones'] += $nu_ventagalon;
	    @$result['totales'][$ch_codigocombustible.'_importe'] += $nu_ventavalor;
	    
	    if ($ch_codigocombustible!='11620307') {
			@$result['totales']['total_galones'] += $nu_ventagalon;
			@$result['totales']['total_importe'] += $nu_ventavalor;
	    }
	    @$result['totales']['total'] += $nu_ventavalor;
	}

	/*QUERY PARA ALMACEN - MARKET */

	$sql = "
SELECT
 SUM(d.nu_fac_valortotal) AS total_tipo,
 TRIM(art.art_tipo),
 c.dt_fac_fecha,
 c.ch_punto_venta,
 TRIM(art.art_linea)
FROM
 fac_ta_factura_cabecera AS c
 LEFT JOIN fac_ta_factura_detalle AS d ON(c.cli_codigo = d.cli_codigo AND c.ch_fac_tipodocumento = d.ch_fac_tipodocumento AND c.ch_fac_seriedocumento = d.ch_fac_seriedocumento AND c.ch_fac_numerodocumento = d.ch_fac_numerodocumento)
 LEFT JOIN int_articulos AS art ON(art.art_codigo = d.art_codigo)
WHERE
 c.dt_fac_fecha BETWEEN to_date('". pg_escape_string($desde) . "', 'DD/MM/YYYY') AND to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
 AND c.ch_fac_tipodocumento = '45'
	";

	if($estaciones != "TODAS")
		$sql .= "AND c.ch_almacen = '" . pg_escape_string($estaciones) . "'";
		
		$sql .="
GROUP BY
 c.ch_punto_venta,
 c.dt_fac_fecha,
 art.art_tipo,
 art.art_linea
ORDER BY
 c.ch_punto_venta,
 c.dt_fac_fecha,
 art.art_tipo,
 art.art_linea;
			";
		error_log($sql);

		if ($sqlca->query($sql) < 0) return false;
		
		$ch_almacen = '';
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
		    
			$total_tipo	= $a[0];
			$art_tipo = $a[1];
			$dt_fac_fecha = $a[2];
			$ch_almacen	= trim($a[3]);
			$art_linea	= $a[4];

			// aqui poner lo de consulta
			/*
		
			cESTADS='01.02.03.06.05.08.09.10.99'		
		
			TipEst=a_tip2
			If !(TipEst$cESTADS)     && JCP   Si no esta especificado a market
				TipEst='05'
			Endif
			If fmarti01.a_line="34"  && Si es Linea de GAS DOMESTICO
				TipEst='05'     && A Columna de MARKET
			Endif
			*/
			//$cESTADS=array("01","02","03","06","05","08","09","10","99");		

			$cESTADS=array("01","02","03","06","05","09","10","99");

			if(!in_array($art_tipo, $cESTADS))
				$art_tipo = '05';
			if($art_linea == "000034")
				$art_tipo = '05';

			// fin de la adaptacion = de consulta
		
			$propio = ($propiedad[$ch_almacen] == 'S' ? "ESTACION" : "OTROS");

			$ch_almacen = $almacenes[$ch_almacen];

			switch ($art_tipo) {
				case '02':
					$nombre = "lubricantes";
				break;

				case '03':
					$nombre = "accesorios";
				break;

				case '05':
					$nombre = "market";
				break;

				case '06':
			    	$nombre = "servicios";
			    break;

				case '09':
					$nombre = "whiz";
			 	break;

				case '10':
			    	$nombre = "ob";
			    break;

				default:
			    	$nombre = "otros";
			    break;
			}
		    
			if (!$bResumido) {
				@$result['propiedades'][$propio]['almacenes'][$ch_almacen]['partes'][$dt_fac_fecha][$nombre] += $total_tipo;
				@$result['propiedades'][$propio]['almacenes'][$ch_almacen]['partes'][$dt_fac_fecha]['total'] += $total_tipo;
		    }

	    	@$result['propiedades'][$propio]['almacenes'][$ch_almacen]['totales'][$nombre] += $total_tipo;
	    	@$result['propiedades'][$propio]['almacenes'][$ch_almacen]['totales']['total'] += $total_tipo;

	    	@$result['propiedades'][$propio]['totales'][$nombre] += $total_tipo;
	    	@$result['propiedades'][$propio]['totales']['total'] += $total_tipo;

	    	@$result['totales'][$nombre] += $total_tipo;
	    	@$result['totales']['total'] += $total_tipo;
		}
		//var_dump($result);
		@$result['producto']= $this->obtiene11620303();
		error_log( json_encode($result) );
		return $result;	
	}
	
	function obtieneVentasSiigo($desde, $hasta, $estaciones, $bResumido){
		global $sqlca;
	
		$propiedad = $this->obtenerPropiedadAlmacenes($estaciones);
		$almacenes = $this->obtieneListaEstaciones();
		echo "<script>console.log('" . json_encode($estaciones) . "')</script>";
		echo "<script>console.log('" . json_encode($propiedad) . "')</script>";
		echo "<script>console.log('" . json_encode($almacenes) . "')</script>";

		$where_codigo_almacen = ($estaciones != "" ? "AND CTC.ch_sucursal = '" . pg_escape_string($estaciones) . "'" : "");		
$sql = "
SELECT
 CTC.ch_sucursal,
 SUM(CTC.nu_ventavalor) as nu_ventavalor,
 SUM(CTC.nu_ventagalon) as nu_ventagalon,
 FIRST(PTA.cantidad) AS ss_afericion_cantidad,
 FIRST(PTA.importe) AS ss_afericion_soles,
 CTC.ch_codigocombustible,
 CTC.dt_fechaparte,
 FIRST(FACMAN.cantidad) AS facman_cantidad,
 FIRST(FACMAN.importe) AS facman_valor,
 FIRST(CTCN.ch_nombrecombustible) as ch_nombrecombustible,
 SUM(CTC.nu_descuentos) as nu_descuentosvalor
FROM
 comb_ta_contometros AS CTC
 LEFT JOIN comb_ta_combustibles CTCN ON CTC.ch_codigocombustible = CTCN.ch_codigocombustible
 LEFT JOIN (
 SELECT
  dia,
  codigo,
  SUM(cantidad) AS cantidad,
  SUM(importe) AS importe
 FROM
  pos_ta_afericiones
 WHERE
  es = '" . pg_escape_string($estaciones) . "'
  AND dia BETWEEN to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') AND to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
 GROUP BY
  1,2
 ) AS PTA ON (CTC.dt_fechaparte = PTA.dia AND CTC.ch_codigocombustible = PTA.codigo)
 LEFT JOIN (
 SELECT 
  fc.dt_fac_fecha as dia, 
  fd.art_codigo as codigo, 
  SUM(fd.nu_fac_cantidad) as cantidad, 
  SUM(fd.nu_fac_valortotal) as importe
 FROM 
  fac_ta_factura_cabecera fc
 INNER JOIN fac_ta_factura_detalle fd ON fc.ch_fac_seriedocumento = fd.ch_fac_seriedocumento AND fc.ch_fac_numerodocumento = fd.ch_fac_numerodocumento
 INNER JOIN comb_ta_combustibles CTCN ON fd.art_codigo = CTCN.ch_codigocombustible
 WHERE 
  fc.ch_almacen = '" . pg_escape_string($estaciones) . "'
  AND fc.dt_fac_fecha BETWEEN to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') AND to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
 GROUP BY 
  1,2
 ) AS FACMAN ON (CTC.dt_fechaparte = FACMAN.dia AND CTC.ch_codigocombustible = FACMAN.codigo)
WHERE
 CTC.dt_fechaparte BETWEEN to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') AND to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
 ". $where_codigo_almacen ."
 AND CTC.nu_ventavalor > 0
 AND CTC.nu_ventagalon > 0
GROUP BY
 1,6,7
ORDER BY
 ch_sucursal,
 dt_fechaparte;
";
error_log($sql);

	/* Obtenemos data de ventas para asientos contables de Siigo */
	$dataVentas = Array();

	if ($sqlca->query($sql) < 0) return false;		
	for ($i = 0; $i < $sqlca->numrows(); $i++) {
		$a = $sqlca->fetchRow();						
			
		$ch_sucursal          = trim($a[0]);
		$nu_ventavalor        = (float)$a[1]; //Ventas valor
		$nu_ventagalon        = (float)$a[2]; //Ventas cantidad
		$fQuantityAfericion   = (float)$a[3]; //Afericiones cantidad
		$fAmountAfericion     = (float)$a[4]; //Afericiones valor
		$ch_codigocombustible = $a[5];
		$dt_fechaparte        = $a[6];
		$facman_cantidad      = (float)$a[7]; //Facturas manuales cantidad
		$facman_importe       = (float)$a[8]; //Facturas manuales importe
		$ch_nombrecombustible = $a[9];
		$nu_descuentosvalor   = $a[10];       //Descuentos valor
		
		@$dataVentas[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible]['ch_sucursal']          = $ch_sucursal;
		@$dataVentas[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible]['nu_ventavalor']        = $nu_ventavalor;
		@$dataVentas[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible]['nu_ventagalon']        = $nu_ventagalon;
		@$dataVentas[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible]['fQuantityAfericion']   = $fQuantityAfericion;
		@$dataVentas[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible]['fAmountAfericion']     = $fAmountAfericion;
		@$dataVentas[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible]['ch_codigocombustible'] = $ch_codigocombustible;
		@$dataVentas[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible]['dt_fechaparte']        = $dt_fechaparte;
		@$dataVentas[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible]['facman_cantidad']      = $facman_cantidad;
		@$dataVentas[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible]['facman_importe']       = $facman_importe;
		@$dataVentas[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible]['ch_nombrecombustible'] = $ch_nombrecombustible;					
		@$dataVentas[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible]['nu_descuentosvalor']   = $nu_descuentosvalor;							
	}
	echo "<script>console.log('" . json_encode($dataVentas) . "')</script>";
	
	/* Formatear asientos */
	$cuentas_contables = array(
		"7001010100" => "7001010100", //IMP
		"1001010000" => "1001010000", //TOT
		"4001010100" => "4001010100", //IGV
		"2001010100" => "2001010100", //MER
	);
	$dataSiigo = Array();
	
	foreach ($dataVentas[$estaciones] as $key=>$fecha) {	
		echo "<script>console.log('". json_encode($fecha) ."')</script>";	
		$total = 0;
		$imponible = 0;
		$total2 = 0;
		$total3 = 0;
		$total4 = 0;
		$imponible3 = 0;
		$igv3 = 0;
		
		foreach ($cuentas_contables as $key2=>$cuenta) {	
			echo "<script>console.log('". json_encode($cuenta) ."')</script>";			
			foreach ($fecha as $key3=>$combustible) {
				echo "<script>console.log('". json_encode($combustible) ."')</script>";			
				$a = $combustible;

				$ch_sucursal          = trim($a['ch_sucursal']);
				$nu_ventavalor        = (float)$a['nu_ventavalor'];      //Ventas valor
				$nu_ventagalon        = (float)$a['nu_ventagalon'];      //Ventas cantidad
				$fQuantityAfericion   = (float)$a['fQuantityAfericion']; //Afericiones cantidad
				$fAmountAfericion     = (float)$a['fAmountAfericion'];   //Afericiones valor
				$ch_codigocombustible = $a['ch_codigocombustible'];
				$dt_fechaparte        = $a['dt_fechaparte'];
				$facman_cantidad      = (float)$a['facman_cantidad'];    //Facturas manuales cantidad
				$facman_importe       = (float)$a['facman_importe'];     //Facturas manuales importe
				$ch_nombrecombustible = $a['ch_nombrecombustible'];
				$nu_descuentosvalor   = $a['nu_descuentosvalor'];        //Descuentos valor

				//Ventas reales
				$nu_ventavalor_real   = $nu_ventavalor - $fAmountAfericion;   //Ventas reales valor (Aun falta quitar las facturas manuales)
				$nu_ventagalon_real   = $nu_ventagalon - $fQuantityAfericion; //Ventas reales cantidad (Aun falta quitar las facturas manuales)

				//Fecha
				$numero_documento = explode("-", $dt_fechaparte);
				$numero_documento = $numero_documento[0].$numero_documento[1].$numero_documento[2];
				$ano_documento    = $numero_documento[0];
				$mes_documento    = $numero_documento[1];
				$dia_documento    = $numero_documento[2];

				if($cuenta == "7001010100"){												
					//Imponible
					$total     = round($nu_ventavalor_real, 2);
					$imponible = round($total/1.18, 2);
					@$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['tipo_comprobante']     = "F";               //1 posicion
					@$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['codigo_comprobante']   = "002";             //3 posiciones
					@$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['numero_documento']     = $numero_documento; //11 posiciones
					@$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['cuenta_contable']      = $cuenta;           //10 posiciones
					@$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['debito_credito']       = "C";			    //1 posicion
					@$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['valor_secuencia']      = $imponible;        //13 enteros, 2 decimales
					@$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['ano_documento']        = $ano_documento;    //4 posiciones
					@$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['mes_documento']        = $mes_documento;    //2 posiciones
					@$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['dia_documento']        = $dia_documento;    //2 posiciones
					@$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['dia_documento']        = $dia_documento;    //2 posiciones
					@$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['ch_nombrecombustible'] = $ch_nombrecombustible;    //2 posiciones
				}
	
				if($cuenta == "1001010000"){
					//Total acumulado
					$total2 += round($nu_ventavalor_real, 2);					
					@$dataSiigo[$ch_sucursal][$dt_fechaparte]["TODOS_1001010000"]['tipo_comprobante']     = "F";               //1 posicion
					@$dataSiigo[$ch_sucursal][$dt_fechaparte]["TODOS_1001010000"]['codigo_comprobante']   = "002";             //3 posiciones
					@$dataSiigo[$ch_sucursal][$dt_fechaparte]["TODOS_1001010000"]['numero_documento']     = $numero_documento; //11 posiciones
					@$dataSiigo[$ch_sucursal][$dt_fechaparte]["TODOS_1001010000"]['cuenta_contable']      = $cuenta;           //10 posiciones
					@$dataSiigo[$ch_sucursal][$dt_fechaparte]["TODOS_1001010000"]['debito_credito']       = "D";			   //1 posicion
					@$dataSiigo[$ch_sucursal][$dt_fechaparte]["TODOS_1001010000"]['valor_secuencia']      = $total2;           //13 enteros, 2 decimales
					@$dataSiigo[$ch_sucursal][$dt_fechaparte]["TODOS_1001010000"]['ano_documento']        = $ano_documento;    //4 posiciones
					@$dataSiigo[$ch_sucursal][$dt_fechaparte]["TODOS_1001010000"]['mes_documento']        = $mes_documento;    //2 posiciones
					@$dataSiigo[$ch_sucursal][$dt_fechaparte]["TODOS_1001010000"]['dia_documento']        = $dia_documento;      //2 posiciones
					@$dataSiigo[$ch_sucursal][$dt_fechaparte]["TODOS_1001010000"]['ch_nombrecombustible'] = $ch_nombrecombustible; //2 posiciones
				}
	
				if($cuenta == "4001010100"){
					//Imponible acumulado
					$total3      = round($nu_ventavalor_real, 2);								
					$imponible3 += round($total3/1.18, 2);				
					//Total acumulado
					$total4 += round($nu_ventavalor_real, 2);
					//Total acumulado - imponible acumulado
					$igv3   = $total4 - $imponible3;					
					@$dataSiigo[$ch_sucursal][$dt_fechaparte]["TODOS_4001010100"]['tipo_comprobante']     = "F";               //1 posicion
					@$dataSiigo[$ch_sucursal][$dt_fechaparte]["TODOS_4001010100"]['codigo_comprobante']   = "002";             //3 posiciones
					@$dataSiigo[$ch_sucursal][$dt_fechaparte]["TODOS_4001010100"]['numero_documento']     = $numero_documento; //11 posiciones
					@$dataSiigo[$ch_sucursal][$dt_fechaparte]["TODOS_4001010100"]['cuenta_contable']      = $cuenta;           //10 posiciones
					@$dataSiigo[$ch_sucursal][$dt_fechaparte]["TODOS_4001010100"]['debito_credito']       = "C";			   //1 posicion
					@$dataSiigo[$ch_sucursal][$dt_fechaparte]["TODOS_4001010100"]['valor_secuencia']      = $igv3;             //13 enteros, 2 decimales
					@$dataSiigo[$ch_sucursal][$dt_fechaparte]["TODOS_4001010100"]['ano_documento']        = $ano_documento;    //4 posiciones
					@$dataSiigo[$ch_sucursal][$dt_fechaparte]["TODOS_4001010100"]['mes_documento']        = $mes_documento;    //2 posiciones
					@$dataSiigo[$ch_sucursal][$dt_fechaparte]["TODOS_4001010100"]['dia_documento']        = $dia_documento;    //2 posiciones
					@$dataSiigo[$ch_sucursal][$dt_fechaparte]["TODOS_4001010100"]['ch_nombrecombustible'] = $ch_nombrecombustible; //2 posiciones
				}
	
				if($cuenta == "2001010100"){
					@$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_2001010100"]['tipo_comprobante']     = "F";               //1 posicion
					@$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_2001010100"]['codigo_comprobante']   = "002";             //3 posiciones
					@$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_2001010100"]['numero_documento']     = $numero_documento; //11 posiciones
					@$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_2001010100"]['cuenta_contable']      = $cuenta;           //10 posiciones
					@$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_2001010100"]['debito_credito']       = "C";			    //1 posicion
					@$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_2001010100"]['valor_secuencia']      = 0.00;              //13 enteros, 2 decimales
					@$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_2001010100"]['ano_documento']        = $ano_documento;    //4 posiciones
					@$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_2001010100"]['mes_documento']        = $mes_documento;    //2 posiciones
					@$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_2001010100"]['dia_documento']        = $dia_documento;    //2 posiciones
					@$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_2001010100"]['ch_nombrecombustible'] = $ch_nombrecombustible; //2 posiciones
				}
			}
		}
	}
	echo "<script>console.log('" . json_encode($dataSiigo) . "')</script>";

	/* Procedimiento para el reporte */
	if ($sqlca->query($sql) < 0) return false;
	$result = Array();
	for ($i = 0; $i < $sqlca->numrows(); $i++) {
	    $a = $sqlca->fetchRow();

	    $ch_sucursal = trim($a[0]);
	    $nu_ventavalor = (float)$a[1];
	    $nu_ventagalon = (float)$a[2];
	    $fQuantityAfericion = (float)$a[3];
	    $fAmountAfericion = (float)$a[4];
	    $ch_codigocombustible = $a[5];
	    $dt_fechaparte = $a[6];
	    
	    $propio = ($propiedad[$ch_sucursal]=='S'?"ESTACION":"OTROS");
	    $ch_sucursal = $almacenes[$ch_sucursal];

	    /* Descuento de afericiones */
	    //$nu_ventagalon -= $nu_afericion;
	    //$nu_ventavalor -= ($nu_afericion*$nu_preciogalon);

	    $nu_ventagalon -= $fQuantityAfericion;
	    $nu_ventavalor -= $fAmountAfericion;

	    if ($ch_codigocombustible=='11620307') {
			// $nu_ventagalon=round($nu_ventagalon/3.785411784,2);
			$nu_ventagalon=round($nu_ventagalon/1);
	    }
	    
	    /* Si no esta resumido, totalizar venta por dia */
	    if (!$bResumido) {
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$dt_fechaparte][$ch_codigocombustible.'_galones'] += $nu_ventagalon;
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$dt_fechaparte][$ch_codigocombustible.'_importe'] += $nu_ventavalor;
			
			/* GLP no actualiza total de combustibles */
			if ($ch_codigocombustible!='11620307') {
			    @$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$dt_fechaparte]['total_galones'] += $nu_ventagalon;
			    @$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$dt_fechaparte]['total_importe'] += $nu_ventavalor;
			}

			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$dt_fechaparte]['total'] += $nu_ventavalor;
	    }
	    
	    /* Calcula total por CC */
	    @$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales'][$ch_codigocombustible.'_galones'] += $nu_ventagalon;
	    @$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales'][$ch_codigocombustible.'_importe'] += $nu_ventavalor;

	    if ($ch_codigocombustible!='11620307') {
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total_galones'] += $nu_ventagalon;
	        @$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total_importe'] += $nu_ventavalor;
	    }
	    @$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total'] += $nu_ventavalor;

	    /* Calcula total por Grupo */
	    @$result['propiedades'][$propio]['totales'][$ch_codigocombustible.'_galones'] += $nu_ventagalon;
	    @$result['propiedades'][$propio]['totales'][$ch_codigocombustible.'_importe'] += $nu_ventavalor;

	    if ($ch_codigocombustible!='11620307') {
			@$result['propiedades'][$propio]['totales']['total_galones'] += $nu_ventagalon;
			@$result['propiedades'][$propio]['totales']['total_importe'] += $nu_ventavalor;
	    }
	    @$result['propiedades'][$propio]['totales']['total'] += $nu_ventavalor;

	    /* Calcula total General */
	    @$result['totales'][$ch_codigocombustible.'_galones'] += $nu_ventagalon;
	    @$result['totales'][$ch_codigocombustible.'_importe'] += $nu_ventavalor;
	    
	    if ($ch_codigocombustible!='11620307') {
			@$result['totales']['total_galones'] += $nu_ventagalon;
			@$result['totales']['total_importe'] += $nu_ventavalor;
	    }
	    @$result['totales']['total'] += $nu_ventavalor;
	}

	error_log( json_encode($result) );
	return $result;	
    }

	function obtenerPropiedadAlmacenes($sCodigoAlmacen){
		global $sqlca;

		//Se aumento where el 30/11/2018
		$where_codigo_almacen = ($sCodigoAlmacen != "" ? "AND ch_almacen = '" . pg_escape_string($sCodigoAlmacen) . "'" : "");

		$sql = "
SELECT
 ch_almacen,
 'S' AS ch_almacen_propio
FROM
 inv_ta_almacenes
WHERE
 ch_clase_almacen = '1'
 " . $where_codigo_almacen;

		if ($sqlca->query($sql) < 0) return false;
	
		$result = Array();
	
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();    
			$result[$a[0]] = $a[1];
		}
		return $result;
	}
    
    function obtieneListaEstaciones(){
		global $sqlca;
	
		$sql = "
SELECT
 ch_almacen,
 trim(ch_nombre_almacen)
FROM
 inv_ta_almacenes
WHERE
 ch_clase_almacen='1'
ORDER BY
 ch_almacen;
		";

		if ($sqlca->query($sql) < 0) return false;
	
		$result = Array();
	
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    $a = $sqlca->fetchRow();
		    $result[$a[0]] = $a[0] . " - " . $a[1];
		}
	
		return $result;
    }

    function obtiene11620303(){
		global $sqlca;
	
		$sql = "SELECT art_descbreve FROM int_articulos WHERE art_codigo='11620303' LIMIT 1;";
		$sqlca->query($sql);
		$row = $sqlca->fetchRow();
			
		return $row;
    }
}

