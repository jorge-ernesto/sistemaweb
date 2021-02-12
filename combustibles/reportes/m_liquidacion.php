<?php

class LiquidacionModel extends Model {

	function obtieneLiquidacion($desde, $hasta, $estaciones, $tipo) {
		global $sqlca;

		$propiedad	= '';
		$almacenes	= LiquidacionModel::obtieneListaEstaciones();
		$cond0		= "";
		$cond1		= "";
		$cond2		= "";

		if ($tipo == 'T') {
			$cond0	.= "";
			$cond1	.= " 	WHERE ";

			$cond2	 = " 	FROM 
						pos_trans".substr($desde,6,4).substr($desde,3,2) . " t
					WHERE	
						t.es = '" . pg_escape_string($estaciones) . "' 
						AND t.fpago = '2' 
						AND t.dia between to_date('".pg_escape_string($desde)."', 'DD/MM/YYYY') and to_date('".pg_escape_string($hasta)."', 'DD/MM/YYYY') 
					GROUP BY 
						date(t.dia)";

			/* Si es para tarjteas de credito no deberia usar este query ya que jala notas de despcaho efectivo
			    JOIN int_tabla_general g ON (g.tab_tabla='95' AND g.tab_elemento='00000'||t.at)
				LEFT JOIN int_clientes c ON (c.cli_ruc = t.ruc AND c.cli_ndespacho_efectivo != 1)

			*/

			/*$cond3 	.= "	SELECT
						to_char(dt_fechaparte, 'DD/MM/YYYY') as dia,
						CAST(SUM(Case when nu_ventagalon>0 then (case when ch_codigocombustible != '11620307' then ((nu_ventavalor/nu_ventagalon) * nu_afericionveces_x_5 * 5) else 0 End)End) as decimal(8,2)) AS afericiones
					FROM
						comb_ta_contometros 
					WHERE
						ch_sucursal = '" . pg_escape_string($estaciones) . "'  AND 
						dt_fechaparte between to_date('".pg_escape_string($desde)."', 'DD/MM/YYYY') and to_date('".pg_escape_string($hasta)."', 'DD/MM/YYYY') 
					GROUP BY
						dt_fechaparte";
			*/

			$cond3	.=	"	SELECT
							to_char(comb.dt_fechaparte, 'DD/MM/YYYY') as dia,
							(SELECT SUM(af.importe) FROM pos_ta_afericiones af where  af.dia = comb.dt_fechaparte and af.es='" . pg_escape_string($estaciones) . "') AS afericiones
						FROM
							comb_ta_contometros comb
						WHERE
							comb.ch_sucursal = '" . pg_escape_string($estaciones) . "'  AND 
							comb.dt_fechaparte between to_date('".pg_escape_string($desde)."', 'DD/MM/YYYY') and to_date('".pg_escape_string($hasta)."', 'DD/MM/YYYY') 
						GROUP BY
							comb.dt_fechaparte";

			$cond4	.= "";
			//srsoledad no desea que reste las afericiones del total en cond5

			$cond5	.= "
			SELECT 
			 Com.dia,
			 Com.total + COALESCE(Mar.total, 0) AS Total
			FROM
			 (SELECT
			  TO_CHAR(C.dt_fechaparte, 'DD/MM/YYYY') AS dia,
			  SUM(CASE WHEN C.nu_ventagalon!=0 THEN C.nu_ventavalor ELSE 0 END) AS total
			 FROM
			  comb_ta_contometros AS C
			 WHERE
			  C.ch_sucursal = '" . pg_escape_string($estaciones) . "' 
			  AND C.dt_fechaparte between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
			 GROUP BY
			  C.dt_fechaparte) AS Com
			 LEFT JOIN
			 (SELECT
			  TO_CHAR(f.dt_fac_fecha, 'DD/MM/YYYY') AS dia,
			  SUM(f.nu_fac_valortotal) AS total  
			 FROM
			  fac_ta_factura_cabecera AS f
			  LEFT JOIN int_clientes AS c ON (f.cli_codigo=c.cli_codigo AND c.cli_ndespacho_efectivo != 1)
			 WHERE
			  ch_almacen = '" . pg_escape_string($estaciones) . "' 
			  AND dt_fac_fecha between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
			  AND f.ch_fac_seriedocumento = '" . pg_escape_string($estaciones) . "' 
			  AND f.ch_fac_tipodocumento = '45'
			 GROUP BY
			  dt_fac_fecha) AS Mar ON (Com.dia = Mar.dia)
			";

			$cond7  .= " pos_trans".substr($desde,6,4).substr($desde,3,2) . " t";

			$cond8  .= " AND t.tipo IN ('C', 'M') ";

			$combu_anticipo  .= " ";

		} else if ($tipo == 'C') {
			$cond0	.= " 	INNER JOIN
							(SELECT lado,prod1 FROM pos_cmblados) L on L.lado = PT.ch_lado and prod1 != 'GL' 
					WHERE ch_tipo = '" . pg_escape_string($tipo) . "' ";

			$cond1	.= " 	INNER JOIN
						(SELECT
							ch_sucursal,
							dt_fecha,
							ch_documento
						FROM
							val_ta_detalle VTD
						inner join
							comb_ta_combustibles Co
							ON VTD.ch_articulo = Co.ch_codigocombustible and Co.ch_codigocombex!='GL'
						GROUP BY
							ch_documento,ch_sucursal,dt_fecha ) VD on VaCa.ch_documento = VD.ch_documento and VaCa.ch_sucursal = VD.ch_sucursal and VaCa.dt_fecha = VD.dt_fecha
						WHERE
							";

			$cond2	.= " 	, tipo
					FROM
						pos_trans". substr($desde,6,4) . substr($desde,3,2) . " t
					WHERE
						t.es = '" . pg_escape_string($estaciones) . "'
						AND t.fpago = '2'
						AND t.codigo != '11620307'
						AND tipo = 'C'
						AND td != 'N'
						AND t.dia between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
					GROUP BY
						date(t.dia), tipo";

			/*$cond3	.= "	SELECT
						to_char(dt_fechaparte, 'DD/MM/YYYY') as dia,
						CAST(SUM(Case when nu_ventagalon>0 then (case when ch_codigocombustible != '11620307' then ((nu_ventavalor/nu_ventagalon) * nu_afericionveces_x_5 * 4.99975) else 0 End)End) as decimal(8,2)) AS afericiones
					FROM
						comb_ta_contometros 
					WHERE
						ch_codigocombustible != '11620307' AND
						ch_sucursal = '" . pg_escape_string($estaciones) . "'  AND 
						dt_fechaparte between to_date('".pg_escape_string($desde)."', 'DD/MM/YYYY') and to_date('".pg_escape_string($hasta)."', 'DD/MM/YYYY') 
					GROUP BY
						dt_fechaparte";
			*/

			$cond3	.=	"	SELECT
							to_char(comb.dt_fechaparte, 'DD/MM/YYYY') as dia,
							(SELECT SUM(af.importe) FROM pos_ta_afericiones af where  af.dia = comb.dt_fechaparte and af.es='" . pg_escape_string($estaciones) . "' AND af.codigo != '11620307') AS afericiones
						FROM
							comb_ta_contometros comb
						WHERE
							comb.ch_sucursal = '" . pg_escape_string($estaciones) . "'  AND 
							comb.dt_fechaparte between to_date('".pg_escape_string($desde)."', 'DD/MM/YYYY') and to_date('".pg_escape_string($hasta)."', 'DD/MM/YYYY')  
						GROUP BY
							comb.dt_fechaparte";

			$cond4	.= " 	and tipo = 'C'";

			$cond5	.= "	SELECT
						to_char(dt_fechaparte, 'DD/MM/YYYY') as dia,
						sum(case when ch_codigocombustible != '11620307' then (CASE WHEN nu_ventavalor != 0 THEN nu_ventavalor ELSE 0 END) ELSE 0 END) as total
					FROM
						comb_ta_contometros C
					WHERE
						ch_codigocombustible != '11620307' AND
						ch_sucursal='" . pg_escape_string($estaciones) . "' and dt_fechaparte between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
					GROUP BY
						dt_fechaparte";
			$cond6 .= "inner join
						(SELECT ch_codigo_trabajador,ch_posturno,dt_dia FROM pos_historia_ladosxtrabajador T 
				   inner join
						(SELECT lado FROM pos_cmblados where prod1!='GL') L on L.lado= T.ch_lado
							WHERE
								ch_sucursal='" . pg_escape_string($estaciones) . "'
							group by
								ch_codigo_trabajador,
								ch_posturno,
								dt_dia,
								ch_tipo
							HAVING
								ch_tipo='C'
							ORDER BY
								dt_dia) LT
							ON
								CD.ch_codigo_trabajador=LT.ch_codigo_trabajador and CD.turno=LT.ch_posturno and CD.dia=LT.dt_dia";

			$cond7  .= "
					pos_trans".substr($desde,6,4).substr($desde,3,2) . " t";

			$cond8  .= " AND t.codigo != '11620307' ";

			$combu_anticipo  .= " INNER JOIN comb_ta_combustibles Co ON(VTD.ch_articulo = Co.ch_codigocombustible AND Co.ch_codigocombex != 'GL' )";

		} else {

			$adicional = "
				SELECT
				PT1.ch_sucursal,
				PT1.dt_dia,
				PT1.ch_posturno,
				PT1.ch_codigo_trabajador
				FROM
				pos_historia_ladosxtrabajador PT1
				WHERE
				PT1.ch_tipo='" . pg_escape_string($tipo) . "'
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
		
			//echo "-----> PRUEBA".$adicional;

			if ($sqlca->query($adicional) < 0) 
				return false;

			for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    	$d = $sqlca->fetchRow();
		    	$textoarmado = $textoarmado."(ch_sucursal = '".trim($d[0])."' AND dt_dia = '".trim($d[1])."' AND ch_posturno = '".trim($d[2])."' AND ch_codigo_trabajador = '".trim($d[3])."') OR ";
		    }

			$adicional2 = substr($textoarmado,0,-3);

			$cond0  .= " WHERE ( " . $adicional2 . " )";



			// $cond0	.= " 	WHERE
			// 			ch_tipo='" . pg_escape_string($tipo) . "'
			// 			AND dt_dia between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
			// 			AND PT.ch_codigo_trabajador NOT IN (SELECT
			// 									PT2.ch_codigo_trabajador 
			// 							     FROM
			// 									pos_historia_ladosxtrabajador PT2 
			// 							     WHERE
			// 									PT2.ch_tipo='C'
			// 									AND PT2.dt_dia = PT.dt_dia
			// 									AND PT2.ch_posturno=PT.ch_posturno
			// 									AND PT.dt_dia between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
			// 							     GROUP BY
			// 									PT2.ch_sucursal,PT2.dt_dia,PT2.ch_codigo_trabajador)";

			$cond1	.= " 	INNER JOIN
						(SELECT
							ch_documento
						FROM
							val_ta_detalle VTD
						WHERE
							ch_articulo != '11620307' and ch_articulo != '11620306' and ch_articulo != '11620305' and ch_articulo != '11620304' and ch_articulo != '11620303' and ch_articulo != '11620302' and ch_articulo != '11620301'
						GROUP BY
							ch_documento ) VD on VD.ch_documento = VaCa.ch_documento 
						 WHERE
							";

			$cond2 	.= " 	,tipo
					FROM
						pos_trans". substr($desde,6,4) . substr($desde,3,2) . " t
					WHERE
						t.es = '" . pg_escape_string($estaciones) . "'
						AND t.fpago = '2'
						AND tipo ='M'
						AND t.dia between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
					GROUP BY
						date(t.dia),tipo";

			/*$cond3	.= "	SELECT
						ch_codigocombustible as combustible,
						to_char(dt_fechaparte, 'DD/MM/YYYY') as dia,
						0.00 AS afericiones 
					FROM
						comb_ta_contometros 
					WHERE
						ch_sucursal='" . pg_escape_string($estaciones) . "' 
						AND dt_fechaparte between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
					GROUP BY
						dt_fechaparte,ch_codigocombustible ";
			*/
			$cond3		.= "	SELECT 
						af.codigo as combustible,
						to_char(af.dia, 'DD/MM/YYYY') as dia,
						0.00 AS afericiones
					FROM 
						pos_ta_afericiones af
					WHERE
						af.dia BETWEEN to_date('".pg_escape_string($desde)."', 'DD/MM/YYYY') and to_date('".pg_escape_string($hasta)."', 'DD/MM/YYYY')
						AND af.es='" . pg_escape_string($estaciones) . "'
					GROUP BY
						af.codigo,
						af.dia";

			$cond4	.= " 	AND tipo = 'M'";

			$cond5	.= "	SELECT
						to_char(fk.dt_fac_fecha, 'DD/MM/YYYY') as dia,
						sum(fk.nu_fac_valortotal) as total  
					FROM
						fac_ta_factura_cabecera fk
					LEFT JOIN
						int_clientes c ON (fk.cli_codigo=c.cli_codigo AND c.cli_ndespacho_efectivo != 1) 
					WHERE
						fk.ch_almacen = '" . pg_escape_string($estaciones) . "'
						AND fk.ch_fac_tipodocumento = '45'
						AND fk.dt_fac_fecha between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') AND to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
					GROUP BY
						fk.dt_fac_fecha";

			$cond7  .= "
					pos_trans".substr($desde,6,4).substr($desde,3,2) . " t";

			$cond8  .= "    AND t.tipo = 'M' ";

			$cond9  .= "    AND VaCa.ch_lado='' ";

			$combu_anticipo  .= "";

		}

		//ANTES HACIA JOIN A T. CUANTO NO HABIAN VENTAS EN TARJETAS EL PROGRAMA FALLABA. AHORA HACE JOIN AL SELECT PRINCIPAL DD
		$sql = "
		SELECT
			DD.dia,
			DD.soles,
			DD.dolares,
			VC.importe AS valesC,
			VC2.importe AS valesA,
			T.importetarjeta,
			AF.afericiones,
			FA.importe AS faltantes,
			SO.importe AS sobrantes,
			TT.total,
			SUM(TC.descuentos_factura) AS descuentos_factura,
			SUM(TC.descuentos_nota_despacho) AS descuentos_nota_despacho
		FROM
			(SELECT
				to_char(D.dt_dia, 'DD/MM/YYYY') as dia,
				cast(sum(Case when ch_moneda='01' then nu_importe else 0 end) As decimal(10,2)) as Soles,
				cast(sum(Case when ch_moneda='02' then nu_importe*nu_tipo_cambio else 0 end) As decimal(10,2)) as Dolares
			FROM
				pos_depositos_diarios D
					INNER JOIN
						(SELECT
							ch_sucursal,
							dt_dia,
							ch_posturno,
							ch_codigo_trabajador
						FROM
							pos_historia_ladosxtrabajador PT
						".
							$cond0
							."
						GROUP BY
							ch_sucursal,
							dt_dia,
							ch_posturno,
							ch_codigo_trabajador) T ON T.ch_sucursal = D.ch_almacen 
					AND 	T.ch_posturno = D.ch_posturno
					AND 	T.ch_codigo_trabajador = D.ch_codigo_trabajador
					AND 	T.dt_dia = D.dt_dia
					
				WHERE
					ch_valida = 'S' 
					AND ch_almacen = '" . pg_escape_string($estaciones) . "'
					AND D.dt_dia between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
				GROUP BY D.dt_dia ) DD
		
				LEFT JOIN (SELECT
						to_char(date(t.dia), 'DD/MM/YYYY') as dia, 
						SUM(t.importe) as importetarjeta ".$cond2.") T ON DD.dia = T.dia	

				LEFT JOIN (SELECT
						to_char(VaCa.dt_fecha, 'DD/MM/YYYY') as dia,
						SUM(VaCa.nu_importe) as importe 
					    FROM
						val_ta_cabecera VaCa
					    INNER JOIN
						(SELECT
							cli_codigo 
						 FROM
							int_clientes 
						 WHERE
							cli_anticipo = 'N' AND cli_ndespacho_efectivo != 1) C ON C.cli_codigo=VaCa.ch_cliente
						 ".
							$cond1
							." VaCa.ch_sucursal='" . pg_escape_string($estaciones) . "'
							AND VaCa.dt_fecha between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY') 
						GROUP BY
							VaCa.dt_fecha
					   ) VC ON VC.dia = DD.dia

				LEFT JOIN (
					SELECT
						to_char(VTD.dt_fecha, 'DD/MM/YYYY') as dia,
						sum(VaCa.nu_importe) as importe
					FROM
						val_ta_detalle VTD
						JOIN val_ta_cabecera VaCa ON(VTD.ch_sucursal = VaCa.ch_sucursal AND VTD.dt_fecha = VaCa.dt_fecha AND VTD.ch_documento = VaCa.ch_documento)
						JOIN int_clientes IC ON (VaCa.ch_cliente = IC.cli_codigo)
						$combu_anticipo
					WHERE
						IC.cli_ndespacho_efectivo != 1
						AND IC.cli_anticipo = 'S'
						AND VTD.ch_sucursal = '" . pg_escape_string($estaciones) . "'
						AND VTD.dt_fecha between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
						".
							$cond9
							."
					 GROUP BY
						VTD.dt_fecha
				) VC2  ON VC2.dia = DD.dia

				   LEFT JOIN (SELECT
						to_char(dt_fecha, 'DD/MM/YYYY') as dia,
						sum(nu_importe) as importe 
					   FROM
						val_ta_detalle VTD 
				  	   WHERE
						ch_sucursal='" . pg_escape_string($estaciones) . "' 
						AND ch_articulo not in (select ch_codigocombustible from comb_ta_combustibles) 
						AND dt_fecha between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY') 
					   GROUP BY
						dt_fecha
					   ) VM ON VM.dia = T.dia 

				LEFT JOIN (".$cond3.") AF ON AF.dia = DD.dia 
		
				LEFT JOIN (SELECT
						to_char(dia, 'DD/MM/YYYY') as dia,
						sum(importe) as importe
					   FROM
						comb_diferencia_trabajador CD ".$cond6."			
					   WHERE
						importe<0  ".$cond4." 
						AND es = '" . pg_escape_string($estaciones) . "' 
						AND dia between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
					   GROUP BY
						dia 
					  ) FA ON FA.dia = DD.dia 

				LEFT JOIN (SELECT
						to_char(dia, 'DD/MM/YYYY') as dia,
						sum(importe) as importe
  					   FROM
						comb_diferencia_trabajador CD ".$cond6."
					   WHERE
						importe>0  ".$cond4." 
						AND es = '" . pg_escape_string($estaciones) . "' 
						AND dia between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
 					  GROUP BY
						dia 
					  ) SO  ON  SO.dia = DD.dia 

				LEFT JOIN (".$cond5.") TT  ON  TT.dia = DD.dia

				LEFT JOIN (SELECT 
							to_char(t.dia, 'DD/MM/YYYY') as dia, 
							SUM(CASE WHEN t.fpago='2'  AND td!='N' THEN t.importe ELSE 0 END) AS tarjetascredito,
							ABS(SUM(CASE WHEN t.td IN('B', 'F') AND t.grupo='D' THEN t.importe ELSE 0 END)) AS descuentos_factura,
							SUM(CASE WHEN t.td='N' AND t.grupo='D' THEN t.importe*-1 ELSE 0 END) AS descuentos_nota_despacho
					     FROM 
							".$cond7." 
					     LEFT JOIN int_clientes k on k.cli_ruc = t.ruc AND k.cli_ndespacho_efectivo != 1
					     WHERE 
							t.es = '" . pg_escape_string($estaciones) . "' 
							".$cond8."
							AND t.dia BETWEEN to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
					     GROUP BY
							t.dia
					     ) TC on TC.dia = AF.dia
				GROUP BY
					DD.dia,
					DD.soles, 
					DD.dolares, 
					VC.importe, 
					VC2.importe, 
					T.importetarjeta, 
					AF.afericiones, 
					FA.importe, 
					SO.importe,
					TT.total
				ORDER BY
					DD.dia ASC;";

/*
		MODIFICACION PARA QUE RESTE LOS DESCUENTOS DE TICKETS EXTORNADOS Y DESCUENTOS A INCREMENTO DE PRECIO
		AGREGAR SI ES NECESARIO
		
		LEFT JOIN (SELECT 
						to_char(t.dia, 'DD/MM/YYYY') as dia, 
						SUM(CASE WHEN t.fpago='2'  AND td!='N' THEN t.importe ELSE 0 END) AS tarjetascredito,
						SUM(CASE 
						WHEN t.tm='V' 
						THEN 
						(CASE WHEN (t.grupo='D') THEN -1 * t.importe END) 
						WHEN t.tm='A' 
						THEN 
						(CASE WHEN (t.grupo='D') THEN -1 * t.importe END) 
						END
						) AS descuentos
					     FROM 
							".$cond7." 
					     LEFT JOIN int_clientes k on k.cli_ruc = t.ruc AND k.cli_ndespacho_efectivo != 1
					     WHERE 
							t.es = '" . pg_escape_string($estaciones) . "' 
							".$cond8."
							AND t.dia BETWEEN to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
					     GROUP BY
							t.dia
					     ) TC on TC.dia = AF.dia
*/


		echo "\n".'- LIQUIDACION: '.$sql.' -';
		//echo $sql;
		if ($sqlca->query($sql) < 0) 
			return false;

		$result = Array();
		$total = $sqlca->numrows()-1;

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$ch_sucursal = pg_escape_string($estaciones);
			$propio = ($propiedad[$ch_sucursal]=='S'?"ESTACION":"OTROS");

			@$result['propiedades'][$propio]['almacenes'][$i][0]	= $a[0];
			@$result['propiedades'][$propio]['almacenes'][$i][1]	= $a[1];
			@$result['propiedades'][$propio]['almacenes'][$i][2] 	= $a[2];
			@$result['propiedades'][$propio]['almacenes'][$i][3] 	= $a[3];
			@$result['propiedades'][$propio]['almacenes'][$i][4] 	= $a[4];
			@$result['propiedades'][$propio]['almacenes'][$i][5] 	= $a[5];
			@$result['propiedades'][$propio]['almacenes'][$i][6] 	= $a[6];
			@$result['propiedades'][$propio]['almacenes'][$i][7] 	= $a[7];
			@$result['propiedades'][$propio]['almacenes'][$i][8] 	= $a[8];
			@$result['propiedades'][$propio]['almacenes'][$i][9] 	= $a[9];
			@$result['propiedades'][$propio]['almacenes'][$i][12] 	= $a[10];
			@$result['propiedades'][$propio]['almacenes'][$i][13] 	= $a[11];

			@$result['propiedades'][$propio]['almacen'][$total]['total1'] 	+= $a[1];
			@$result['propiedades'][$propio]['almacen'][$total]['total2'] 	+= $a[2];
			@$result['propiedades'][$propio]['almacen'][$total]['total3'] 	+= $a[3];
			@$result['propiedades'][$propio]['almacen'][$total]['total4'] 	+= $a[4];
			@$result['propiedades'][$propio]['almacen'][$total]['total5'] 	+= $a[5];
			@$result['propiedades'][$propio]['almacen'][$total]['total6'] 	+= $a[6];
			@$result['propiedades'][$propio]['almacen'][$total]['total7'] 	+= $a[7];
			@$result['propiedades'][$propio]['almacen'][$total]['total8'] 	+= $a[8];
			@$result['propiedades'][$propio]['almacen'][$total]['total9'] 	+= $a[9];
			@$result['propiedades'][$propio]['almacen'][$total]['total10'] 	+= $a[10];
			@$result['propiedades'][$propio]['almacen'][$total]['total11'] 	+= $a[11];
		}

		return $result;	
	}

	function obtieneListaEstaciones() {
		global $sqlca;
	
		$sql = "SELECT 
				ch_almacen, 
				trim(ch_nombre_almacen)
			FROM 
				inv_ta_almacenes
			WHERE 
				ch_clase_almacen='1'
			ORDER BY 
				ch_almacen; ";

		if ($sqlca->query($sql) < 0) 
			return false;	

		$result = Array();	
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$result[$a[0]] = $a[0]." - ".$a[1];
		}	
		return $result;
	}

	///REPORTEP PARA GLP

	function obtieneGLP($desde, $hasta, $estaciones) {
		global $sqlca;

		$almacenes = LiquidacionModel::obtieneListaEstaciones();
		$con1 = "pos_trans".substr($desde,6,4).substr($desde,3,2) . " t";

		$sql = "SELECT 
				DD.dia, 
				DD.soles, 
				DD.dolares, 
				sum(VC.importe) as importe, 
				VC2.importe as valesA, 
				T.importetarjeta, 
				sum(AF.afericiones) as afericiones,
				FA.importe as faltantes, 
				SO.importe as sobrantes,
				TT.total, 
				sum(TC.descuentos) as descuentos
        
			FROM	(SELECT
					to_char(D.dt_dia, 'DD/MM/YYYY') as dia,
					cast(sum(Case when ch_moneda='01' then nu_importe else 0 end) As decimal(10,2)) as Soles,
					cast(sum(Case when ch_moneda='02' then nu_importe*nu_tipo_cambio else 0 end) As decimal(10,2)) as Dolares
				FROM
					pos_depositos_diarios D
					INNER JOIN
						(SELECT
							ch_sucursal,
							dt_dia,
							ch_posturno,
							ch_codigo_trabajador
						FROM
							pos_historia_ladosxtrabajador PT
						INNER JOIN
							(SELECT lado,prod1 FROM pos_cmblados) L on L.lado = PT.ch_lado and prod1 = 'GL' 
						WHERE ch_tipo = 'C'
						GROUP BY
							ch_sucursal,
							dt_dia,
							ch_posturno,
							ch_codigo_trabajador) T
						ON T.ch_sucursal = D.ch_almacen 
						AND 	T.ch_posturno = D.ch_posturno
						AND 	T.ch_codigo_trabajador = D.ch_codigo_trabajador
						AND 	T.dt_dia = D.dt_dia
					WHERE
						ch_valida = 'S' 
						AND ch_almacen = '" . pg_escape_string($estaciones) . "'
						AND D.dt_dia between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY') 

					GROUP BY
						D.dt_dia ) DD
		
				LEFT JOIN (SELECT
						to_char(date(t.dia), 'DD/MM/YYYY') as dia, 
						SUM(t.importe) as importetarjeta , tipo
					   FROM
						".$con1."
					   LEFT JOIN
						int_clientes c ON (c.cli_ruc = t.ruc AND c.cli_ndespacho_efectivo != 1)
					   JOIN
						int_tabla_general g ON (g.tab_tabla='95' AND g.tab_elemento='00000'||t.at)
					   WHERE
						t.es = '" . pg_escape_string($estaciones) . "'
						AND t.fpago = '2'
						AND t.codigo = '11620307'
						AND tipo ='C'
						AND t.dia between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY') 
					  GROUP BY
						date(t.dia), tipo) T ON DD.dia = T.dia

				LEFT JOIN (SELECT
						to_char(VaCa.dt_fecha, 'DD/MM/YYYY') as dia,
						SUM(VaCa.nu_importe) as importe 
					    FROM
						val_ta_cabecera VaCa
					    INNER JOIN
						(SELECT
							cli_codigo 
						 FROM
							int_clientes 
						 WHERE
							cli_anticipo = 'N' AND cli_ndespacho_efectivo != 1) C ON C.cli_codigo=VaCa.ch_cliente
						  	INNER JOIN
						(SELECT
							ch_sucursal,
							dt_fecha,
							ch_documento
						FROM
							val_ta_detalle VTD
						inner join
							comb_ta_combustibles Co
							ON VTD.ch_articulo = Co.ch_codigocombustible and Co.ch_codigocombex = 'GL'
						GROUP BY
							ch_documento,ch_sucursal,dt_fecha ) VD on VaCa.ch_documento = VD.ch_documento and VaCa.ch_sucursal = VD.ch_sucursal and VaCa.dt_fecha = VD.dt_fecha
						WHERE
							 VaCa.ch_sucursal = '" . pg_escape_string($estaciones) . "' 
							AND VaCa.dt_fecha between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY') 
						GROUP BY
							VaCa.dt_fecha
					   ) VC ON VC.dia = T.dia
	
				LEFT JOIN (
					SELECT
						to_char(VTD.dt_fecha, 'DD/MM/YYYY') as dia,
						sum(VaCa.nu_importe) as importe
					FROM
						val_ta_detalle VTD
						JOIN val_ta_cabecera VaCa ON(VTD.ch_sucursal = VaCa.ch_sucursal AND VTD.dt_fecha = VaCa.dt_fecha AND VTD.ch_documento = VaCa.ch_documento)
						INNER JOIN comb_ta_combustibles Co ON(VTD.ch_articulo = Co.ch_codigocombustible AND Co.ch_codigocombex = 'GL')
						JOIN int_clientes IC ON (VaCa.ch_cliente = IC.cli_codigo)
					WHERE
						IC.cli_ndespacho_efectivo != 1
						AND IC.cli_anticipo = 'S'
						AND VTD.ch_sucursal = '" . pg_escape_string($estaciones) . "'
						AND VTD.dt_fecha between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
					 GROUP BY
						VTD.dt_fecha
				) VC2 ON VC2.dia = T.dia

				LEFT JOIN (SELECT
						to_char(dt_fecha, 'DD/MM/YYYY') as dia,
						sum(nu_importe) as importe 
					   FROM
						val_ta_detalle VTD 
				  	   WHERE
						ch_sucursal = '" . pg_escape_string($estaciones) . "' 
						AND ch_articulo not in (select ch_codigocombustible from comb_ta_combustibles) 
						AND dt_fecha between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY') 
					   GROUP BY
						dt_fecha
					   ) VM ON VM.dia = T.dia  


				LEFT JOIN (SELECT
							to_char(af.dia, 'DD/MM/YYYY') as dia,
							CASE WHEN SUM(af.importe) > 0 THEN SUM(af.importe) ELSE 0.00 END AS afericiones
						FROM
							pos_ta_afericiones af
						WHERE
							af.es = '" . pg_escape_string($estaciones) . "' 
							AND af.codigo = '11620307' 
							AND af.dia between to_date('".pg_escape_string($desde)."', 'DD/MM/YYYY') and to_date('".pg_escape_string($hasta)."', 'DD/MM/YYYY')
						GROUP BY
							af.dia
					)AF ON AF.dia = DD.dia

				LEFT JOIN (SELECT
						to_char(dia, 'DD/MM/YYYY') as dia,
						sum(importe) as importe
					   FROM
						comb_diferencia_trabajador CD
					   inner join
						(SELECT ch_codigo_trabajador,ch_posturno,dt_dia FROM pos_historia_ladosxtrabajador T 
					   inner join
						(SELECT lado FROM pos_cmblados where prod1='GL') L on L.lado= T.ch_lado
							WHERE
								ch_sucursal = '" . pg_escape_string($estaciones) . "'
							group by
								ch_codigo_trabajador,
								ch_posturno,
								dt_dia,
								ch_tipo
							HAVING
								ch_tipo='C'
							ORDER BY
								dt_dia) LT
							ON
								CD.ch_codigo_trabajador=LT.ch_codigo_trabajador and CD.turno=LT.ch_posturno and CD.dia=LT.dt_dia				
					   WHERE
						importe<0
						AND tipo = 'C'
						AND es = '" . pg_escape_string($estaciones) . "' 
						AND dia between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
					   GROUP BY
						dia 
					  ) FA ON FA.dia = T.dia 

				LEFT JOIN (SELECT
						to_char(dia, 'DD/MM/YYYY') as dia,
						sum(importe) as importe
  					   FROM
						comb_diferencia_trabajador CD
					   inner join
						(SELECT ch_codigo_trabajador,ch_posturno,dt_dia FROM pos_historia_ladosxtrabajador T 
					   inner join
						(SELECT lado FROM pos_cmblados where prod1='GL') L on L.lado= T.ch_lado
							WHERE
								ch_sucursal = '" . pg_escape_string($estaciones) . "'
							group by
								ch_codigo_trabajador,
								ch_posturno,
								dt_dia,
								ch_tipo
							HAVING
								ch_tipo='C'
							ORDER BY
								dt_dia) LT
							ON
								CD.ch_codigo_trabajador=LT.ch_codigo_trabajador and CD.turno=LT.ch_posturno and CD.dia=LT.dt_dia
					   WHERE
						importe>0
						AND tipo = 'C'
						AND es = '" . pg_escape_string($estaciones) . "' 
						AND dia between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
 					  GROUP BY
						dia 
					  ) SO  ON  SO.dia = T.dia 

				LEFT JOIN (SELECT
						to_char(dt_fechaparte, 'DD/MM/YYYY') as dia,
						sum(case when ch_codigocombustible = '11620307' then (CASE WHEN nu_ventavalor != 0 THEN nu_ventavalor ELSE 0 END) ELSE 0 END) as total
					    FROM
						comb_ta_contometros C
					    WHERE
					 	ch_codigocombustible = '11620307' AND
						ch_sucursal = '" . pg_escape_string($estaciones) . "' AND
						dt_fechaparte between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY') 
				 	    GROUP BY
						dt_fechaparte) TT  ON  TT.dia = DD.dia
				LEFT JOIN (SELECT 
							to_char(t.dia, 'DD/MM/YYYY') as dia, 
							SUM(CASE WHEN t.fpago='2'  AND td!='N' THEN t.importe ELSE 0 END) AS tarjetascredito,
							SUM(CASE WHEN t.tm='V' THEN (CASE WHEN (t.importe<0) THEN  -1*t.importe ELSE (CASE WHEN (t.importe>0 and t.grupo='D') THEN -1*t.importe ELSE 0 END)  END) END) AS descuentos
					     FROM 
							".$con1."
					     LEFT JOIN int_clientes k on k.cli_ruc = t.ruc AND k.cli_ndespacho_efectivo != 1
					     WHERE 
							t.es = '" . pg_escape_string($estaciones) . "'
							AND t.codigo = '11620307'
							AND t.dia BETWEEN to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
					     GROUP BY
							t.dia
					     ) TC on TC.dia = AF.dia
				GROUP BY
					DD.dia,
					DD.soles, 
					DD.dolares,
					VC2.importe, 
					T.importetarjeta,
					FA.importe, 
					SO.importe,
					TT.total
				ORDER BY
					DD.dia ASC;";

		echo "\n".'- GLP: '.$sql.' -';

		if ($sqlca->query($sql) < 0) 
			return false;

		$result = Array();
		$total = $sqlca->numrows()-1;

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$ch_sucursal = pg_escape_string($estaciones);
			$propio = ($propiedad[$ch_sucursal]=='S'?"ESTACION":"OTROS");

			@$result['propiedades'][$propio]['almacenes'][$i][0]	= $a[0];
			@$result['propiedades'][$propio]['almacenes'][$i][1]	= $a[1];
			@$result['propiedades'][$propio]['almacenes'][$i][2] 	= $a[2];
			@$result['propiedades'][$propio]['almacenes'][$i][3] 	= $a[3];
			@$result['propiedades'][$propio]['almacenes'][$i][4] 	= $a[4];
			@$result['propiedades'][$propio]['almacenes'][$i][5] 	= $a[5];
			@$result['propiedades'][$propio]['almacenes'][$i][6] 	= $a[6];
			@$result['propiedades'][$propio]['almacenes'][$i][7] 	= $a[7];
			@$result['propiedades'][$propio]['almacenes'][$i][8] 	= $a[8];
			@$result['propiedades'][$propio]['almacenes'][$i][9] 	= $a[9];
			@$result['propiedades'][$propio]['almacenes'][$i][12] 	= $a[10];
			@$result['propiedades'][$propio]['almacenes'][$i][13] 	= $a[11];

			@$result['propiedades'][$propio]['almacen'][$total]['total1'] 	+= $a[1];
			@$result['propiedades'][$propio]['almacen'][$total]['total2'] 	+= $a[2];
			@$result['propiedades'][$propio]['almacen'][$total]['total3'] 	+= $a[3];
			@$result['propiedades'][$propio]['almacen'][$total]['total4'] 	+= $a[4];
			@$result['propiedades'][$propio]['almacen'][$total]['total5'] 	+= $a[5];
			@$result['propiedades'][$propio]['almacen'][$total]['total6'] 	+= $a[6];
			@$result['propiedades'][$propio]['almacen'][$total]['total7'] 	+= $a[7];
			@$result['propiedades'][$propio]['almacen'][$total]['total8'] 	+= $a[8];
			@$result['propiedades'][$propio]['almacen'][$total]['total9'] 	+= $a[9];
			@$result['propiedades'][$propio]['almacen'][$total]['total10'] 	+= $a[10];
			@$result['propiedades'][$propio]['almacen'][$total]['total11'] 	+= $a[11];
		}

		return $result;	

	}
}
