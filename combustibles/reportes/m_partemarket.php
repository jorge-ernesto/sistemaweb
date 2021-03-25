<?php

class ParteMarketModel extends Model {

	function obtieneTC($desde, $hasta, $estaciones) {
		global $sqlca;
		$almacenes = ParteMarketModel::obtieneListaEstaciones();

		$sqlA = "SELECT
						tca_venta_oficial
					FROM
						int_tipo_cambio
					WHERE
						tca_fecha = to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY')";
		echo "<pre>ObtieneTC";
		echo $sqlA;
		echo "</pre>";

		if($sqlca->query($sqlA) < 0) return false;
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a1 = $sqlca->fetchRow();
			@$result['propiedades']['0'] = $a1[0];
		}

		return $result;
	}

    function obtieneMarket($desde, $hasta, $estaciones) {
    	global $sqlca;

    	$propiedad = ParteMarketModel::obtenerPropiedadAlmacenes();
    	$almacenes = ParteMarketModel::obtieneListaEstaciones();

    	$sqlA = "SELECT
						SUM(F.nu_fac_valortotal) AS ventatienda
					FROM
						fac_ta_factura_cabecera F
					LEFT JOIN int_clientes c
						ON F.cli_codigo = c.cli_codigo
						AND c.cli_ndespacho_efectivo != 1
					WHERE
						F.ch_fac_seriedocumento = '" . pg_escape_string($estaciones) . "'
						AND F.ch_fac_tipodocumento = '45'
						AND F.dt_fac_fecha BETWEEN to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') AND to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
					";

		//TARJETAS DETALLE
		$sqlAa = "SELECT
						g.tab_descripcion as descripciontarjeta,
						SUM(t.importe)-SUM(COALESCE(t.km,0)) as importetarjeta
					FROM
						pos_trans". substr($desde,6,4) . substr($desde,3,2) . " t
					JOIN
						int_tabla_general g ON (g.tab_tabla='95' AND g.tab_elemento='00000'||t.at)
						LEFT JOIN int_clientes c on c.cli_ruc = t.ruc AND c.cli_ndespacho_efectivo != 1
					WHERE
						t.es = '" . pg_escape_string($estaciones) . "' AND
						t.fpago = '2' AND
						t.dia between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY') 
						and codigo not in (select ch_codigocombustible from comb_ta_combustibles)
					GROUP BY
						1 
					ORDER BY
						g.tab_descripcion;";

		//TARJETAS TOTAL
		$sqlA1 = "SELECT 
						SUM(CASE WHEN t.fpago='2' THEN t.importe-COALESCE(t.km,0) ELSE 0 END) AS tarjetascredito,
						SUM(CASE WHEN t.tm='V' THEN (CASE WHEN t.importe<0 THEN t.importe ELSE 0 END) ELSE 0 END) AS descuentos
					FROM
						pos_trans". substr($desde,6,4) . substr($desde,3,2) . " t LEFT JOIN int_clientes c on c.cli_ruc = t.ruc AND c.cli_ndespacho_efectivo != 1
					WHERE
						t.es='" . pg_escape_string($estaciones) . "'
						AND date(t.dia) BETWEEN to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') AND to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY') 
						AND codigo NOT IN (select ch_codigocombustible FROM comb_ta_combustibles)";
	
		//CREDITOS CLIENTES
		$sqlB = "SELECT
						SUM(CASE WHEN VC.ch_estado='1' THEN VC.nu_importe ELSE 0 END)
					FROM	
						(SELECT
							ch_estado,
							ch_documento,
							ch_cliente,
							nu_importe,
							ch_tarjeta,
							ch_caja,
							ch_lado,
							ch_turno 
						FROM
							val_ta_cabecera 
						WHERE
							ch_sucursal='" . pg_escape_string($estaciones) . "'
							AND dt_fecha BETWEEN to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY')
							AND to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')) VC 

	inner join (SELECT ch_documento
			FROM 	val_ta_detalle VTD 
			WHERE   ch_articulo not in (select ch_codigocombustible from comb_ta_combustibles) 
			GROUP BY ch_documento
	) VD 
	on VD.ch_documento = VC.ch_documento
	inner join (	SELECT cli_codigo 
			FROM int_clientes 
			WHERE cli_anticipo='N' AND cli_ndespacho_efectivo != 1) C
	on C.cli_codigo=VC.ch_cliente";

	//CREDITOS ANTICIPOS
	$sqlC ="SELECT	SUM(CASE WHEN VC.ch_estado='1' THEN VC.nu_importe ELSE 0 END)
		FROM	(	SELECT ch_estado,ch_documento,ch_cliente,nu_importe,ch_tarjeta,ch_caja,ch_lado,ch_turno 
			FROM val_ta_cabecera 
			WHERE ch_sucursal='" . pg_escape_string($estaciones) . "' and dt_fecha between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')) VC 

	inner join (SELECT ch_documento
			FROM 	val_ta_detalle VTD 
			WHERE   ch_articulo not in (select ch_codigocombustible from comb_ta_combustibles) 
			GROUP BY ch_documento
	) VD 
	on VD.ch_documento = VC.ch_documento
	inner join (	SELECT cli_codigo 
			FROM int_clientes 
			WHERE cli_anticipo='S' AND cli_ndespacho_efectivo != 1) C
	on C.cli_codigo=VC.ch_cliente";

	//OPTIMIZACION CODIGO DE DEPOSITOS 03/05/17 CSR
	$sqlD0 =	"SELECT
				PT1.ch_sucursal,
				PT1.dt_dia,
				PT1.ch_posturno,
				PT1.ch_codigo_trabajador
				FROM
				pos_historia_ladosxtrabajador PT1
				WHERE
				PT1.ch_tipo='M'
				AND PT1.dt_dia between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') AND to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
				AND PT1.ch_codigo_trabajador NOT IN
				(
				SELECT
				PT2.ch_codigo_trabajador
				FROM
				pos_historia_ladosxtrabajador PT2
				WHERE
				PT2.ch_tipo='C'
				AND PT2.dt_dia=PT1.dt_dia
				AND PT2.ch_posturno=PT1.ch_posturno
				GROUP BY
				PT2.ch_sucursal,
				PT2.dt_dia,
				PT2.ch_codigo_trabajador
				)
				GROUP BY
				PT1.ch_sucursal,
				PT1.dt_dia,
				PT1.ch_posturno,
				PT1.ch_codigo_trabajador";
		
			if ($sqlca->query($sqlD0) < 0) 
				return false;
			for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    	$d = $sqlca->fetchRow();
		    	$textoarmado = $textoarmado."(ch_almacen = '".trim($d[0])."' AND dt_dia = '".trim($d[1])."' AND ch_posturno = '".trim($d[2])."' AND ch_codigo_trabajador = '".trim($d[3])."') OR ";
			}

			$textofinal = substr($textoarmado,0,-3);

	if ($textofinal) {
	$sqlD = "SELECT
			sum(nu_importe) as importe
			FROM
			pos_depositos_diarios
			WHERE
			ch_almacen = '". pg_escape_string($estaciones) . "'
			AND ch_moneda='01' and ch_valida='S'
			AND
			(
			" . $textofinal . "
			)";

	$sqlE = "SELECT
			sum (nu_importe * tpc.tca_venta_oficial) as importe
			FROM
			pos_depositos_diarios
			JOIN int_tipo_cambio tpc ON (tpc.tca_fecha=dt_dia AND tpc.tca_moneda = '02')
			WHERE
			ch_almacen = '". pg_escape_string($estaciones) . "'
			AND ch_moneda!='01' and ch_valida='S'
			AND
			(
			" . $textofinal . "
			)";
	}
	
	//FALTANTES
	$sqlH="SELECT 	sum(-importe)
		FROM	comb_diferencia_trabajador CD
		WHERE 	importe<0 and es='" . pg_escape_string($estaciones) . "' and tipo='M' and  dia between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')";

	//SOBRANTES
	$sqlI="SELECT 	sum(-importe)
		FROM	comb_diferencia_trabajador CD
		WHERE 	importe>0 and es='" . pg_escape_string($estaciones) . "' and tipo='M' and dia between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')";

	echo "<pre>";
	echo 'Market:'.$sqlA;
	echo "</pre>";
	if ($sqlca->query($sqlA) < 0) return false;
	$result = Array();
	for ($i = 0; $i < $sqlca->numrows(); $i++) {
	    $a = $sqlca->fetchRow();
	    $ch_sucursal = pg_escape_string($estaciones);
	    $propio = ($propiedad[$ch_sucursal]=='S'?"ESTACION":"OTROS");
	    $ch_sucursal = $almacenes[$ch_sucursal];
	    @$result['propiedades'][$propio]['almacenes'][$ch_sucursal][0]['valor'][1] = '0';
	    @$result['propiedades'][$propio]['almacenes'][$ch_sucursal][1]['valor'][0] = '0';
	    @$result['propiedades'][$propio]['almacenes'][$ch_sucursal][1]['valor'][1] = '0';
	    @$result['propiedades'][$propio]['almacenes'][$ch_sucursal][2]['valor'][0] = 'VENTA';
	    @$result['propiedades'][$propio]['almacenes'][$ch_sucursal][2]['valor'][1] = $a[0];
	}

	echo "<pre>";
	echo 'Market:'.$sqlB;
	echo "</pre>";
	if ($sqlca->query($sqlB) < 0) return false;
	for ($i = 0; $i < $sqlca->numrows(); $i++) {
	    $b = $sqlca->fetchRow();
	    @$result['propiedades'][$propio]['almacenes'][$ch_sucursal][3]['valor'][0] = 'Credito Clientes';
	    @$result['propiedades'][$propio]['almacenes'][$ch_sucursal][3]['valor'][1] = $b[0];
	}

	echo "<pre>";
	echo 'Market:'.$sqlC;
	echo "</pre>";
	if ($sqlca->query($sqlC) < 0) return false;
	for ($i = 0; $i < $sqlca->numrows(); $i++) {
	    $c = $sqlca->fetchRow();
	    @$result['propiedades'][$propio]['almacenes'][$ch_sucursal][4]['valor'][0] = 'Credito Anticipos';
	    @$result['propiedades'][$propio]['almacenes'][$ch_sucursal][4]['valor'][1] = $c[0];
	}

	$j = 5;
	echo "<pre>";
	echo 'Market:'.$sqlAa;
	echo "</pre>";
	if ($sqlca->query($sqlAa) < 0) return false;
	@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][0]['valor'][0] = $sqlca->numrows();
	for ($i = 5; $i < $sqlca->numrows() + 5 ; $i++) {
	    $a0 = $sqlca->fetchRow();
	    $ch_sucursal = pg_escape_string($estaciones);
	    $ch_sucursal = $almacenes[$ch_sucursal];
	    @$result['propiedades'][$propio]['almacenes'][$ch_sucursal][$j]['valor'][0] = $a0[0];
	    @$result['propiedades'][$propio]['almacenes'][$ch_sucursal][$j]['valor'][1] = $a0[1];
	    $j = $j+1;
	}

	echo "<pre>";
	echo 'Market:'.$sqlA1;
	echo "</pre>";
	if ($sqlca->query($sqlA1) < 0) return false;
	for ($i = 0; $i < $sqlca->numrows(); $i++) {
	    $a1 = $sqlca->fetchRow();
	    @$result['propiedades'][$propio]['almacenes'][$ch_sucursal][$j]['valor'][0] = 'Total Tarjetas';
	    @$result['propiedades'][$propio]['almacenes'][$ch_sucursal][$j]['valor'][1] = $a1[0];
	    @$result['propiedades'][$propio]['almacenes'][$ch_sucursal][$j + 1]['valor'][0] = 'Descuentos';
	    @$result['propiedades'][$propio]['almacenes'][$ch_sucursal][$j + 1]['valor'][1] = $a1[1];
	}

	echo "<pre>";
	echo 'Market:'.$sqlD;
	echo "</pre>";
	if (!$textofinal) {
		@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][$j + 2]['valor'][0] = 'Efectivo Soles';
		@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][$j + 2]['valor'][1] = 0.00;
	} else {
		if ($sqlca->query($sqlD) < 0) return false;
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    $d = $sqlca->fetchRow();
		    @$result['propiedades'][$propio]['almacenes'][$ch_sucursal][$j + 2]['valor'][0] = 'Efectivo Soles';
		    @$result['propiedades'][$propio]['almacenes'][$ch_sucursal][$j + 2]['valor'][1] = $d[0];
		}
	}

	echo "<pre>";
	echo 'Market:'.$sqlE;
	echo "</pre>";
	if (!$textofinal) {
		@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][$j + 3]['valor'][0] = 'Efectivo Dolares';
		@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][$j + 3]['valor'][1] = 0.00;
	} else {
		if ($sqlca->query($sqlE) < 0) return false;
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    $e = $sqlca->fetchRow();
		    @$result['propiedades'][$propio]['almacenes'][$ch_sucursal][$j + 3]['valor'][0] = 'Efectivo Dolares';
		    @$result['propiedades'][$propio]['almacenes'][$ch_sucursal][$j + 3]['valor'][1] = $e[0];
		}
	}

	echo "<pre>";
	echo 'Market:'.$sqlH;
	echo "</pre>";
	if ($sqlca->query($sqlH) < 0) return false;
	for ($i = 0; $i < $sqlca->numrows(); $i++) {
	    $h = $sqlca->fetchRow();
	    @$result['propiedades'][$propio]['almacenes'][$ch_sucursal][$j + 4]['valor'][0] = 'Faltantes Trabajador';
	    @$result['propiedades'][$propio]['almacenes'][$ch_sucursal][$j + 4]['valor'][1] = $h[0];
	}

	echo "<pre>";
	echo 'Market:'.$sqlI;
	echo "</pre>";
	if ($sqlca->query($sqlI) < 0) return false;
	for ($i = 0; $i < $sqlca->numrows(); $i++) {
	    $ii = $sqlca->fetchRow();
	    @$result['propiedades'][$propio]['almacenes'][$ch_sucursal][$j + 5]['valor'][0] = 'Sobrantes Trabajador';
	    @$result['propiedades'][$propio]['almacenes'][$ch_sucursal][$j + 5]['valor'][1] = $ii[0];
	}

	return $result;
    }
    
    function obtenerPropiedadAlmacenes()
    {
	global $sqlca;
	
	$sql = "SELECT
		    ch_almacen,
		    'S' AS ch_almacen_propio
		FROM
		    inv_ta_almacenes
		WHERE
		    ch_clase_almacen='1'
		;
		";
	if ($sqlca->query($sql) < 0) return false;
	
	$result = Array();
	
	for ($i = 0; $i < $sqlca->numrows(); $i++) {
	    $a = $sqlca->fetchRow();
	    
	    $result[$a[0]] = $a[1];
	}
	
	return $result;
    }   

    function obtieneListaEstaciones()
    {
	global $sqlca;
	
	$sql = "SELECT
		    ch_almacen,
		    trim(ch_nombre_almacen)
		FROM
		    inv_ta_almacenes
		WHERE
		    ch_clase_almacen='1'
		ORDER BY
		    ch_almacen
		;
		";
	if ($sqlca->query($sql) < 0) return false;
	
	$result = Array();
	
	for ($i = 0; $i < $sqlca->numrows(); $i++) {
	    $a = $sqlca->fetchRow();
	    $result[$a[0]] = $a[0] . " - " . $a[1];
	}
	
	return $result;
    }

	function obtieneLineas($desde,$hasta, $estacion){
		global $sqlca;
		$sql = "SELECT
				sum(d.nu_fac_cantidad) AS total_cantidad,
				sum(d.nu_fac_valortotal) AS total_importe
			FROM
				fac_ta_factura_cabecera c
				RIGHT JOIN fac_ta_factura_detalle d ON (d.ch_fac_tipodocumento=c.ch_fac_tipodocumento AND d.ch_fac_seriedocumento=c.ch_fac_seriedocumento AND d.ch_fac_numerodocumento=c.ch_fac_numerodocumento AND d.cli_codigo=c.cli_codigo)
				LEFT JOIN int_clientes k on c.cli_codigo=k.cli_codigo AND k.cli_ndespacho_efectivo != 1	
			WHERE
				c.ch_fac_tipodocumento='45'
				AND c.ch_fac_seriedocumento='" . pg_escape_string($estacion) . "'
				AND c.dt_fac_fecha BETWEEN to_date('" . pg_escape_string($desde) . "', 'dd/mm/YYYY') AND to_date('" . pg_escape_string($hasta) . "', 'dd/mm/YYYY')
			";
		if ($estacion != 'TODAS') {
			$sql .= "AND c.ch_almacen='" . pg_escape_string($estacion) . "'";
		}

		if ($sqlca->query($sql) < 0) return false;

		$row = $sqlca->fetchRow();
		$total_cantidad = $row[0];
		$total_importe = $row[1];
		
		echo "<pre>obtieneLineas";
		echo $sql;
		echo "</pre>";

		$sql = "
			SELECT
				art.art_linea AS linea,
				max(tab.tab_descripcion) AS descripcion_linea,
				sum(d.nu_fac_cantidad) AS cantidad,
				sum(d.nu_fac_valortotal) AS importe
			FROM
				fac_ta_factura_cabecera c
				RIGHT JOIN fac_ta_factura_detalle d ON (d.ch_fac_tipodocumento=c.ch_fac_tipodocumento AND d.ch_fac_seriedocumento=c.ch_fac_seriedocumento AND d.ch_fac_numerodocumento=c.ch_fac_numerodocumento AND d.cli_codigo=c.cli_codigo)
				RIGHT JOIN int_articulos art ON (art.art_codigo=d.art_codigo)
				LEFT JOIN int_tabla_general tab ON (tab.tab_tabla='20' AND tab.tab_elemento=art.art_linea)
				LEFT JOIN int_clientes k on c.cli_codigo=k.cli_codigo AND k.cli_ndespacho_efectivo != 1
			WHERE
				c.ch_fac_tipodocumento='45'
				AND c.ch_fac_seriedocumento='" . pg_escape_string($estacion) . "'
				AND c.dt_fac_fecha BETWEEN to_date('" . pg_escape_string($desde) . "', 'dd/mm/YYYY') AND to_date('" . pg_escape_string($hasta) . "', 'dd/mm/YYYY')
			";
		if ($estacion != 'TODAS') {
			$sql .= "AND c.ch_almacen='" . pg_escape_string($estacion) . "'";
		}
		$sql .= "GROUP BY
				art.art_linea
			ORDER BY
				art.art_linea;";

		echo "<pre>obtieneLineas";
		echo $sql;
		echo "</pre>";

		if ($sqlca->query($sql) < 0) return false;
		
		$resultado = Array();
		
		$resultado['totales']['importe'] = 0;
		$resultado['totales']['cantidad'] = 0;
		$resultado['totales']['porcentaje_importe'] = 0;
		$resultado['totales']['porcentaje_cantidad'] = 0;

	
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$row = $sqlca->fetchRow();
			$resultado['filas'][$i]['linea'] = $row[0];
			$resultado['filas'][$i]['descripcion_linea'] = $row[1];
			$resultado['filas'][$i]['cantidad'] = $row[2];
			$resultado['filas'][$i]['importe'] = $row[3];
			$porcentaje_cantidad = ($row[2]/$total_cantidad)*100;
			$porcentaje_importe = ($row[3]/$total_importe)*100;
			$resultado['filas'][$i]['porcentaje_cantidad'] = $porcentaje_cantidad;
			$resultado['filas'][$i]['porcentaje_importe'] = $porcentaje_importe;

			$resultado['totales']['importe'] += $row[3];
			$resultado['totales']['porcentaje_importe'] += $porcentaje_importe;
			$resultado['totales']['cantidad'] += $row[2];
			$resultado['totales']['porcentaje_cantidad'] += $porcentaje_cantidad;
		}

		return $resultado;
	}



function obtieneLineasTurno($desde,$hasta, $estacion, $final){
		global $sqlca;

		$sql = "
SELECT
 turno,
 SUM(caja1) AS caja1,
 SUM(caja2) AS caja2,
 SUM(caja3) AS caja3,
 SUM(caja4) AS caja4,
 SUM(caja5) AS caja5,
 SUM(caja6) AS caja6,
 SUM(caja7) AS caja7,
 SUM(caja8) AS caja8,
 SUM(caja9) AS caja9
FROM (
SELECT
 turno,
 CASE WHEN turno='1' AND caja='1' THEN SUM (importe) END AS caja1,
 CASE WHEN turno='1' AND caja='2' THEN SUM(importe) END AS caja2,
 CASE WHEN turno='1' AND caja='3' THEN SUM(importe) END AS caja3,
 CASE WHEN turno='1' AND caja='4' THEN SUM(importe) END AS caja4,
 CASE WHEN turno='1' AND caja='5' THEN SUM(importe) END AS caja5,
 CASE WHEN turno='1' AND caja='6' THEN SUM(importe) END AS caja6,
 CASE WHEN turno='1' AND caja='7' THEN SUM(importe) END AS caja7,
 CASE WHEN turno='1' AND caja='8' THEN SUM(importe) END AS caja8,
 CASE WHEN turno='1' AND caja='9' THEN SUM(importe) END AS caja9
FROM
 pos_trans" . $final . "
WHERE
 tipo='M' 
 AND turno='1'
 AND dia BETWEEN to_date('" . pg_escape_string($desde) . "', 'dd/mm/YYYY') AND to_date('" .pg_escape_string($hasta) . "', 'dd/mm/YYYY')
 AND es='" . pg_escape_string($estacion) . "'	
GROUP BY
 turno,
 caja

UNION ALL

SELECT
 turno,
 CASE WHEN turno='2' AND caja='1' THEN SUM(importe) END as caja1,
 CASE WHEN turno='2' AND caja='2' THEN SUM(importe) END as caja2,
 CASE WHEN turno='2' AND caja='3' THEN SUM(importe) END as caja3,
 CASE WHEN turno='2' AND caja='4' THEN SUM(importe) END as caja4,
 CASE WHEN turno='2' AND caja='5' THEN SUM(importe) END as caja5,
 CASE WHEN turno='2' AND caja='6' THEN SUM(importe) END as caja6,
 CASE WHEN turno='2' AND caja='7' THEN SUM(importe) END AS caja7,
 CASE WHEN turno='2' AND caja='8' THEN SUM(importe) END AS caja8,
 CASE WHEN turno='2' AND caja='9' THEN SUM(importe) END AS caja9
FROM
 pos_trans" . $final . "
WHERE
 tipo='M'
 AND turno='2'
 AND dia BETWEEN to_date('" . pg_escape_string($desde) . "', 'dd/mm/YYYY') AND to_date('" .pg_escape_string($hasta) . "', 'dd/mm/YYYY')
 AND es='" . pg_escape_string($estacion) . "'
GROUP BY
 turno,
 caja

UNION ALL

SELECT
 turno,
 CASE WHEN turno='3' AND caja='1' THEN SUM(importe) END as caja1,
 CASE WHEN turno='3' AND caja='2' THEN SUM(importe) END as caja2,
 CASE WHEN turno='3' AND caja='3' THEN SUM(importe) END as caja3,
 CASE WHEN turno='3' AND caja='4' THEN SUM(importe) END as caja4,
 CASE WHEN turno='3' AND caja='5' THEN SUM(importe) END as caja5,
 CASE WHEN turno='3' AND caja='6' THEN SUM(importe) END as caja6,
 CASE WHEN turno='3' AND caja='7' THEN SUM(importe) END AS caja7,
 CASE WHEN turno='3' AND caja='8' THEN SUM(importe) END AS caja8,
 CASE WHEN turno='3' AND caja='9' THEN SUM(importe) END AS caja9
FROM
 pos_trans" . $final . "
WHERE
 tipo = 'M'
 AND turno='3'
 AND dia BETWEEN to_date('" . pg_escape_string($desde) . "', 'dd/mm/YYYY') AND to_date('" .pg_escape_string($hasta) . "', 'dd/mm/YYYY')
 AND es='" . pg_escape_string($estacion) . "'
GROUP BY
 turno,
 caja

UNION ALL

SELECT
 turno,
 CASE WHEN turno='4' AND caja='1' THEN SUM (importe) END as caja1,
 CASE WHEN turno='4' AND caja='2' THEN SUM(importe) END as caja2,
 CASE WHEN turno='4' AND caja='3' THEN SUM(importe) END as caja3,
 CASE WHEN turno='4' AND caja='4' THEN SUM(importe) END as caja4,
 CASE WHEN turno='4' AND caja='5' THEN SUM(importe) END AS caja5,
 CASE WHEN turno='4' AND caja='6' THEN SUM(importe) END AS caja6,
 CASE WHEN turno='4' AND caja='7' THEN SUM(importe) END AS caja7,
 CASE WHEN turno='4' AND caja='8' THEN SUM(importe) END AS caja8,
 CASE WHEN turno='4' AND caja='9' THEN SUM(importe) END AS caja9
FROM
 pos_trans" . $final . "
WHERE
 tipo='M'
 AND turno='4'
 AND dia BETWEEN to_date('" . pg_escape_string($desde) . "', 'dd/mm/YYYY') AND to_date('" .pg_escape_string($hasta) . "', 'dd/mm/YYYY')
 AND es='" . pg_escape_string($estacion) . "'
GROUP BY
 turno,
 caja

UNION ALL

SELECT
 turno,
 CASE WHEN turno='5' AND caja='1' THEN SUM (importe) END as caja1,
 CASE WHEN turno='5' AND caja='2' THEN SUM(importe) END as caja2,
 CASE WHEN turno='5' AND caja='3' THEN SUM(importe) END as caja3,
 CASE WHEN turno='5' AND caja='4' THEN SUM(importe) END as caja4,
 CASE WHEN turno='5' AND caja='5' THEN SUM(importe) END as caja5,
 CASE WHEN turno='5' AND caja='6' THEN SUM(importe) END as caja6,
 CASE WHEN turno='5' AND caja='7' THEN SUM(importe) END AS caja7,
 CASE WHEN turno='5' AND caja='8' THEN SUM(importe) END AS caja8,
 CASE WHEN turno='5' AND caja='9' THEN SUM(importe) END AS caja9
FROM
 pos_trans" . $final . "
WHERE
 tipo='M'
 AND turno='5'
 AND dia BETWEEN to_date('" . pg_escape_string($desde) . "', 'dd/mm/YYYY') AND to_date('" .pg_escape_string($hasta) . "', 'dd/mm/YYYY')
 AND es='" . pg_escape_string($estacion) . "'
GROUP BY
 turno,
 caja

UNION ALL

SELECT
 turno,
 CASE WHEN turno='6' AND caja='1' THEN SUM(importe) END AS caja1,
 CASE WHEN turno='6' AND caja='2' THEN SUM(importe) END AS caja2,
 CASE WHEN turno='6' AND caja='3' THEN SUM(importe) END AS caja3,
 CASE WHEN turno='6' AND caja='4' THEN SUM(importe) END AS caja4,
 CASE WHEN turno='6' AND caja='5' THEN SUM(importe) END AS caja5,
 CASE WHEN turno='6' AND caja='6' THEN SUM(importe) END AS caja6,
 CASE WHEN turno='6' AND caja='7' THEN SUM(importe) END AS caja7,
 CASE WHEN turno='6' AND caja='8' THEN SUM(importe) END AS caja8,
 CASE WHEN turno='6' AND caja='9' THEN SUM(importe) END AS caja9
FROM
 pos_trans" . $final . "
WHERE
 tipo='M'
 AND turno='6'
 AND dia BETWEEN to_date('" . pg_escape_string($desde) . "', 'dd/mm/YYYY') AND to_date('" .pg_escape_string($hasta) . "', 'dd/mm/YYYY')
 AND es='" . pg_escape_string($estacion) . "'
GROUP BY
 turno,
 caja
) AS tabla
GROUP BY
 tabla.turno
ORDER BY
 tabla.turno
";
echo "<pre>obtieneLineasTurno";
echo $sql;
echo "</pre>";

		if ($sqlca->query($sql) < 0) return false;
		
		$resultado = Array();
			
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$row = $sqlca->fetchRow();
			$resultado['filas'][$i]['turno'] = $row[0];
			$resultado['filas'][$i]['caja1'] = $row[1];
			$resultado['totales']['totcaja1'] += $row[1];
			$resultado['filas'][$i]['caja2'] = $row[2];
			$resultado['totales']['totcaja2'] += $row[2];
			$resultado['filas'][$i]['caja3'] = $row[3];
			$resultado['totales']['totcaja3'] += $row[3];
			$resultado['filas'][$i]['caja4'] = $row[4];
			$resultado['totales']['totcaja4'] += $row[4];
			$resultado['filas'][$i]['caja5'] = $row[5];
			$resultado['totales']['totcaja5'] += $row[5];
			$resultado['filas'][$i]['caja6'] = $row[6];
			$resultado['totales']['totcaja6'] += $row[6];
			$resultado['filas'][$i]['caja7'] = $row[7];
			$resultado['totales']['totcaja7'] += $row[7];
			$resultado['filas'][$i]['caja8'] = $row[8];
			$resultado['totales']['totcaja8'] += $row[8];
			$resultado['filas'][$i]['caja9'] = $row[9];
			$resultado['totales']['totcaja9'] += $row[9];
			$resultado['totales']['totaltodo'] += $row[1]+$row[2]+$row[3]+$row[4]+$row[5]+$row[6]+$row[7]+$row[8]+$row[9];
		}
		return $resultado;
	}

	function obtieneTurnos(){
		$lado = Array();
		$lado[1] = "1";
		$lado[2] = "2";
		$lado[3] = "3";
		$lado[4] = "4";
		$lado[5] = "5";
		$lado[6] = "6";
		$lado[7] = "7";
		$lado[8] = "8";
		$lado[9] = "9";
		$lado[''] = "TODOS";
		return $lado;
	}

	function acumuladoTurno($almacen, $desde, $hasta, $turno) {
		global $sqlca;

		$fecha_dc = explode('/', $desde, 3);
		settype($fecha_dc[0], "int");
		settype($fecha_dc[1], "int");
		settype($fecha_dc[2], "int");

		if ($fecha_dc[0] < 10)
		    $fecha_dc[0] = "0" . $fecha_dc[0];
		if ($fecha_dc[1] < 10)
		    $fecha_dc[1] = "0" . $fecha_dc[1];

		$diabuscado = $fecha_dc[2] . "-" . $fecha_dc[1] . "-" . $fecha_dc[0];

		$query = "
SELECT
 da_fecha,
 ch_posturno
FROM
 pos_aprosys
WHERE
 ch_poscd='A';
		";

		if ($sqlca->query($query) < 0)
			return false;

		$a 		= $sqlca->fetchRow();
		$dia_actual 	= $a['da_fecha'];
		$turno_actual 	= $a['ch_posturno'];

		if ($diabuscado == $dia_actual)
			$postrans = "pos_transtmp";
		else
			$postrans = pg_escape_string("pos_trans" . $fecha_dc[2] . $fecha_dc[1]);
	
		$sql = "
			SELECT
				trans.dia as dia,
				trans.turno as turno,
				sum(trans.cantidad) as total_cantidad,
				sum(trans.importe) as total_importe
			FROM	
				".$postrans." trans
				LEFT JOIN int_articulos art ON (art.art_codigo = trans.codigo)
			WHERE		
				trans.es = '".$almacen."'
				AND trans.caja = '1'
				AND trans.dia BETWEEN to_date('" . pg_escape_string($desde) . "', 'dd/mm/YYYY') AND to_date('" . pg_escape_string($hasta) . "', 'dd/mm/YYYY')";

		if(!empty($turno))
			$sql .= "AND trans.turno='".$turno."' ";

		$sql .= "
			GROUP BY  
				trans.dia, 
				trans.turno 
			ORDER BY
				trans.dia, trans.turno ";

		//echo "\nDIA Y TURNO\n".$sql."\n\n";

		if ($sqlca->query($sql) <= 0)
			return $sqlca->get_error();
		
		$diaturno = array();
		$canti = $sqlca->numrows();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$diaturno[$i]['dia']     = $a['dia'];
			$diaturno[$i]['turno']   = $a['turno'];		
			$diaturno[$i]['tot_can'] = $a['total_cantidad'];
			$diaturno[$i]['tot_imp'] = $a['total_importe'];		
		}

		$sql2="
			SELECT
				trans.dia as dia,
				trans.turno as turno,
				substr(l.tab_descripcion,0,40) as producto,
				sum(trans.cantidad) as cantidad,
				sum(trans.importe) as importe
			FROM	
				".$postrans." trans
				LEFT JOIN int_articulos a ON (a.art_codigo = trans.codigo)
				LEFT JOIN int_tabla_general l ON (l.tab_tabla='20' AND (a.art_linea = l.tab_elemento OR a.art_linea = substr(l.tab_elemento,5,2)))
			WHERE		
				trans.es = '".$almacen."'
				AND trans.caja = '1'
				AND trans.dia BETWEEN to_date('" . pg_escape_string($desde) . "', 'dd/mm/YYYY') AND to_date('" . pg_escape_string($hasta) . "', 'dd/mm/YYYY')";

		if(!empty($turno))
			$sql2 .= "AND trans.turno='".$turno."' ";

		$sql2 .= "
			GROUP BY 
				l.tab_descripcion, trans.dia, trans.turno
			ORDER BY
				trans.dia, trans.turno";

		//echo "\nCADA PRODUCTO\n".$sql2."\n\n";

		if ($sqlca->query($sql2)<=0)
			return $sqlca->get_error();
		
		$resultado = array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$resultado[$i]['dia']      = $a['dia'];
			$resultado[$i]['turno']    = $a['turno'];
			$resultado[$i]['producto'] = $a['producto'];
			$resultado[$i]['cantidad'] = $a['cantidad'];
			$resultado[$i]['importe']  = $a['importe'];
		}
		$res = array();
		$res['header'] = $diaturno;
		$res['body']   = $resultado;
		$res['info']['almacen'] = ParteMarketModel::obtenerEstacion($almacen);
		$res['info']['periodo'] = $periodo;
		$res['info']['mes']     = $mes;
		$res['info']['desde']   = $dia_desde;
		$res['info']['hasta']   = $dia_hasta;

		return $res;	
	}

	function obtenerEstacion($almacen) {
		global $sqlca;

		$sql = "SELECT 	 trim(ch_nombre_almacen) as nombre
			FROM	 inv_ta_almacenes
			WHERE	 ch_clase_almacen='1' AND ch_almacen='$almacen' ";

		if ($sqlca->query($sql) < 0) 
			return false;
		
		$a = $sqlca->fetchRow();
		$nomalmacen = $a['nombre'];
		
		return $nomalmacen;
	}	

	function obtenerComandoImprimir($file) {
		global $sqlca;
		
		$sql =	"
			SELECT
				trim(prn_samba),
				trim(ip) 
			FROM
				pos_cfg 
			WHERE
				pos = '1';
		";
		
		$rs = $sqlca->query($sql);
		if ($rs < 0) {
			echo "Error consultando POS\n";
			return false;
		}
		if ($sqlca->numrows()<1)
			return true;

		$row = $sqlca->fetchRow();
		$smbc="lpr -H {$row[1]} -P {$row[0]} {$file}";

		$fp = fopen("COMANDO.txt","a");
		fwrite($fp, "-".$smbc."-".PHP_EOL);
		fclose($fp);  
		return $smbc;
	}


}

