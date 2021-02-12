<?php

class ParteLiquidacionModel extends Model {

	function obtieneTC($desde, $hasta, $estaciones) {
		global $sqlca;
	
		$propiedad = ParteLiquidacionModel::obtenerPropiedadAlmacenes();
		$almacenes = ParteLiquidacionModel::obtieneListaEstaciones();

		$sqlA = "SELECT tca_venta_oficial
			FROM int_tipo_cambio
			WHERE tca_fecha=to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY')";

		if ($sqlca->query($sqlA) < 0) 
			return false;
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a1 = $sqlca->fetchRow();
			@$result['propiedades']['0'] = $a1[0];
		}
		return $result;
	}

	function obtieneParte($desde, $hasta, $estaciones) {
		global $sqlca;

		$propiedad = ParteLiquidacionModel::obtenerPropiedadAlmacenes();
		$almacenes = ParteLiquidacionModel::obtieneListaEstaciones();

		$sqlA = "SELECT 
				Co.ch_codigocombustible AS Codigo,
				Co.ch_nombrebreve AS Producto,
				SUM(MD1.nu_medicion) AS Stock_Inicial,
				FIRST(MA.compras) AS compras, 
				FIRST(C.ventas) AS ventas,
				SUM(MD1.nu_medicion) - FIRST(C.ventas) AS Stock_Final,
				SUM(MD2.nu_medicion) AS Medicion,
				SUM(MD2.nu_medicion) - (SUM(MD1.nu_medicion) - FIRST(C.ventas)) AS Dia,";
		if($desde == $hasta)		
			$sqlA .= "
				(CASE WHEN FIRST(SAL.cantidad) > 0 THEN  FIRST(M.mes) + FIRST(SAL.cantidad) ELSE FIRST(M.mes) END) AS Mes,";
		else
			$sqlA .= "
				SUM(MD2.nu_medicion) - (SUM(MD1.nu_medicion) - FIRST(C.ventas)) AS Mes,";
		$sqlA .= "	
				(FIRST(C.valval)-FIRST(C.afeafe)) AS importe,
				FIRST(ENT2.cantidad) as cantidad,
				FIRST(SAL2.cantidad) as cantidad
			FROM 
				(select nu_medicion,ch_tanque from comb_ta_mediciondiaria where ch_sucursal='" . pg_escape_string($estaciones) . "' and dt_fechamedicion = to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY')-1) MD1

				INNER JOIN (select nu_medicion,ch_tanque from comb_ta_mediciondiaria where ch_sucursal='" . pg_escape_string($estaciones) . "' and dt_fechamedicion = to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')) MD2
				on MD1.ch_tanque = MD2.ch_tanque

				INNER JOIN (select ch_tanque,ch_codigocombustible from comb_ta_tanques where ch_sucursal= '" . pg_escape_string($estaciones) . "') T
				on T.ch_tanque = MD1.ch_tanque and T.ch_tanque = MD2.ch_tanque

				left JOIN (select art_codigo,sum(mov_cantidad) as compras from inv_movialma where tran_codigo='21' and date(mov_fecha) between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY') AND mov_almacen='$estaciones' group by art_codigo) MA
				on MA.art_codigo = T.ch_codigocombustible
				
				LEFT JOIN
					(SELECT
						art_codigo codigo,
						round(sum(mov_cantidad),2) as cantidad
					FROM
						inv_movialma alma
					WHERE
						";
						if($desde == $hasta)
							$sqlA .= "mov_fecha::date BETWEEN to_date('01".substr($desde,2,10)."','DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "','DD/MM/YYYY')";
						else
							$sqlA .= "mov_fecha::date BETWEEN to_date('" . pg_escape_string($desde) . "','DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "','DD/MM/YYYY')";
						$sqlA .= "AND tran_codigo = '27'
					GROUP BY
						art_codigo) ENT ON ENT.codigo = T.ch_codigocombustible

				
				LEFT JOIN
					(SELECT
						art_codigo codigo,
						round(sum(mov_cantidad),2) as cantidad
					FROM
						inv_movialma alma
					WHERE
						mov_fecha::date BETWEEN to_date('" . pg_escape_string($desde) . "','DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "','DD/MM/YYYY')
						AND tran_codigo = '27'
					GROUP BY
						art_codigo) ENT2 ON ENT2.codigo = T.ch_codigocombustible

				LEFT JOIN
					(SELECT
						art_codigo codigo,
						round(sum(mov_cantidad),2) as cantidad
					FROM
						inv_movialma alma
					WHERE
						";
						if($desde == $hasta)
							$sqlA .= "mov_fecha::date BETWEEN to_date('01".substr($desde,2,10)."','DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "','DD/MM/YYYY')";
						else
							$sqlA .= "mov_fecha::date BETWEEN to_date('" . pg_escape_string($desde) . "','DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "','DD/MM/YYYY')";
						$sqlA .= "AND tran_codigo = '28'
					GROUP BY
						art_codigo) SAL ON SAL.codigo = T.ch_codigocombustible

				LEFT JOIN
					(SELECT
						art_codigo codigo,
						round(sum(mov_cantidad),2) as cantidad
					FROM
						inv_movialma alma
					WHERE
						mov_fecha::date BETWEEN to_date('" . pg_escape_string($desde) . "','DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "','DD/MM/YYYY')
						AND tran_codigo = '28'
					GROUP BY
						art_codigo) SAL2 ON SAL2.codigo = T.ch_codigocombustible
	
				LEFT JOIN 
					(SELECT
						com1.ch_codigocombustible as codcod,
						--sum(com1.nu_ventagalon) - (sum(com1.nu_afericionveces_x_5) * 5) as ventas,
						sum(com1.nu_ventagalon) - (SELECT
							COALESCE(sum(afe.cantidad),0)
						FROM
							pos_ta_afericiones afe
						WHERE
							afe.codigo = com1.ch_codigocombustible AND afe.dia between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
						) as ventas,
						sum(com1.nu_ventavalor) valval, 
						(SELECT
							COALESCE(sum(afe.importe),0)
						FROM
							pos_ta_afericiones afe
						WHERE
							afe.codigo = com1.ch_codigocombustible AND afe.dia between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
						) as afeafe 
					FROM
						comb_ta_contometros com1 
	 				WHERE
						com1.ch_sucursal = '" . pg_escape_string($estaciones) . "'
							AND com1.dt_fechaparte BETWEEN to_date('" . pg_escape_string($desde) . "','DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "','DD/MM/YYYY')
					GROUP BY
						com1.ch_codigocombustible) C ON C.codcod = T.ch_codigocombustible

				INNER JOIN comb_ta_combustibles Co ON (Co.ch_codigocombustible = MA.art_codigo OR Co.ch_codigocombustible = C.codcod)

				LEFT JOIN(
					SELECT
						T.ch_codigocombustible as combustible,
						sum(MD2.nu_medicion-(MD1.nu_medicion+CASE WHEN MA.compras>0 THEN MA.compras ELSE 0.00 END-C.ventas)) as Mes
					FROM  
						comb_ta_mediciondiaria MD1

					INNER JOIN (select ch_sucursal,ch_tanque,ch_codigocombustible from comb_ta_tanques) T
					on T.ch_tanque = MD1.ch_tanque and T.ch_sucursal=MD1.ch_sucursal

					left JOIN (	select date(mov_fecha) as fecha,art_codigo,sum(mov_cantidad) as compras 
							from inv_movialma 
							where tran_codigo='21'  AND mov_almacen='$estaciones'
							group by art_codigo,date(mov_fecha)) MA
					on MA.art_codigo = T.ch_codigocombustible and MA.fecha=MD1.dt_fechamedicion+1

					INNER JOIN (select ch_sucursal,dt_fechaparte,ch_codigocombustible,sum(nu_afericionveces_x_5) as afericion,sum(nu_ventagalon)as venta,sum(nu_ventagalon-(nu_afericionveces_x_5*5)) as ventas from comb_ta_contometros group by ch_sucursal,dt_fechaparte,ch_codigocombustible) C
					on C.ch_sucursal = MD1.ch_sucursal and C.dt_fechaparte = MD1.dt_fechamedicion+1 and C.ch_codigocombustible=T.ch_codigocombustible

					INNER JOIN comb_ta_combustibles Co
					on Co.ch_codigocombustible = T.ch_codigocombustible

					INNER JOIN (select dt_fechamedicion,ch_sucursal,ch_tanque,nu_medicion from comb_ta_mediciondiaria) MD2
					on MD2.dt_fechamedicion=MD1.dt_fechamedicion+1 and MD2.ch_sucursal=MD1.ch_sucursal and MD2.ch_tanque = T.ch_tanque
				 
			WHERE  ";
		
		if($desde == $hasta)		
		$sqlA .= "	
				MD1.dt_fechamedicion between to_date('01".substr($desde,2,10)."','DD/MM/YYYY')-1 and to_date('" . pg_escape_string($hasta) . "','DD/MM/YYYY')-1 ";
		else
		$sqlA .= "	
				MD1.dt_fechamedicion between to_date('" . pg_escape_string($desde) . "','DD/MM/YYYY')-2 and to_date('" . pg_escape_string($hasta) . "','DD/MM/YYYY')-1 ";
		$sqlA .= "

				AND MD1.ch_sucursal='" . pg_escape_string($estaciones) . "'
			GROUP BY 
				T.ch_codigocombustible) M on M.combustible = T.ch_codigocombustible";

		$sqlA .= "	GROUP BY 1,2 
				  	ORDER BY Co.ch_nombrebreve ;";

		echo '- PARTE I: '.$sqlA.' -';

		if ($sqlca->query($sqlA) < 0) 
			return false;
		$result = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();		    
			$ch_sucursal = pg_escape_string($estaciones);
			$producto = $a[0];
			$propio = ($propiedad[$ch_sucursal]=='S'?"ESTACION":"OTROS");
			$ch_sucursal = $almacenes[$ch_sucursal];

			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['producto'] = $a[1];
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['inicial'] = $a[2];
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['compras'] = $a[3];
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['transfe'] = $a[10] - $a[11];
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['ventas'] = $a[4];
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['porcentaje'] = $a[4] * 100;

			if ($a[3]!=''){
				@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['final'] = $a[5] + $a[3];
				@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['dia'] = ($a[7] - $a[3]);//Esto es la columna diferencia DIA
				if($desde == $hasta)
					@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['mes'] = $a[8];
				else
					@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['mes'] = ($a[7] - $a[3]);//Esto es la columna diferencia DIA
				//else
					//@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['mes'] = ($a[8] - $a[3]);
			}else{
				@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['final'] = $a[5];
				@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['dia'] = $a[7] ;
				if($desde == $hasta)	
					@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['mes'] = $a[8];
				else
					@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['mes'] = $a[7];//Esto es la columna diferencia DIA
				//else
				//	@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['mes'] = ($a[8] - $a[3]);
			}
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['medicion'] = $a[6];
			//@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['mes'] = $a[8];
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['importe'] = $a[9];

			//TOTALES
			if ($a[1] == 'GLP'){
				@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['porcentaje'] = "100";
			} else {
				if ($a[3]!=''){
					@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total']['final'] += ($a[5] + $a[3]);
					@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total']['dia'] += ($a[7] - $a[3]);

				} else {
					@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total']['final'] += $a[5];
					@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total']['dia'] += ($a[7] - $a[3]);
				}
				@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total']['producto'] = "TOTAL";
				@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total']['inicial'] += $a[2];
				@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total']['compras'] += $a[3];
				@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total']['ventas'] += $a[4];
				@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total']['porcentaje'] = "100";
				@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total']['transfe'] += $a[10] - $a[11];
				@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total']['medicion'] += $a[6];
//				@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total']['mes'] += $a[8];
				if($desde == $hasta){
					@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total']['mes'] += $a[8];
				}else{
					@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total']['mes'] += ($a[7] - $a[3]);
				}
				@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total']['importe'] += $a[9];
			}
		}
		return $result;	
	}

	function obtieneCombustible($desde, $hasta, $estaciones) {
		global $sqlca;
	
		$propiedad = ParteLiquidacionModel::obtenerPropiedadAlmacenes();
		$almacenes = ParteLiquidacionModel::obtieneListaEstaciones();

		$sqlA = "SELECT 
				sum(C.valor) as valor,
				COALESCE(sum(A.afericion),0) as afericion,
				0 as venta,
				sum(TC.tarjetascredito) as tarjetascredito,
				sum(TC.descuentos) as descuentos
	
			FROM 	(
					SELECT 
						comb.ch_codigocombustible as combustible,
						sum(comb.nu_ventavalor) as valor
					FROM 
						comb_ta_contometros comb
					WHERE 	
						comb.ch_codigocombustible!='11620307'
						and comb.ch_sucursal='" . pg_escape_string($estaciones) . "' 
						and comb.dt_fechaparte between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
					GROUP BY 
						comb.ch_codigocombustible
				) C

				LEFT JOIN 

				(
					SELECT 
						t.codigo, 
						SUM(CASE WHEN t.td != 'N' AND t.fpago='2' THEN t.importe ELSE 0 END) AS tarjetascredito,
							SUM(CASE 
							WHEN t.tm='V' 
							THEN 
							(CASE WHEN (t.importe<0 ) THEN  -1 * t.importe ELSE (CASE WHEN (t.importe>0 and t.grupo='D') THEN -1 * t.importe ELSE 0 END) END) 
							WHEN t.tm='A' 
							THEN 
							(CASE WHEN (t.importe>0 and t.grupo='D') THEN -1 * t.importe ELSE 0 END) 
							END
							) AS descuentos
					FROM 
						pos_trans". substr($desde,6,4) . substr($desde,3,2) . " t 
					WHERE 
						t.es = '" . pg_escape_string($estaciones) . "'
						AND t.codigo != '11620307'
						AND t.tipo = 'C'
						AND t.dia BETWEEN to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
					GROUP BY
					 	t.codigo
				) TC on TC.codigo = C.combustible

				LEFT JOIN
				(
					SELECT
						SUM(af.importe) as afericion,
						af.codigo as codigo
					FROM 
						pos_ta_afericiones af
					WHERE
						af.codigo != '11620307'
						AND af.es = '" . pg_escape_string($estaciones) . "' 
						AND af.dia BETWEEN to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')		
					GROUP BY
						af.codigo
				) A on A.codigo = C.combustible;

			";

		$sqlA1 = "SELECT CASE WHEN CH_RESPONSABLE<>'' THEN 'Parte Manual' ELSE '' END 
			FROM 	comb_ta_contometros 
			WHERE 	ch_codigocombustible!='11620307'
				and ch_sucursal='" . pg_escape_string($estaciones) . "'
				and dt_fechaparte between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
			GROUP BY CH_RESPONSABLE
			ORDER BY CH_RESPONSABLE desc";

		//CREDITOS CLIENTES
		$sqlB ="SELECT	SUM(CASE WHEN VC.ch_estado='1' THEN VC.nu_importe ELSE 0 END)
			FROM	(SELECT ch_estado,ch_documento,ch_cliente,nu_importe,ch_tarjeta,ch_caja,ch_lado,ch_turno,ch_sucursal,dt_fecha 
				FROM val_ta_cabecera 
				WHERE ch_sucursal='" . pg_escape_string($estaciones) . "' and dt_fecha between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')) VC 
			INNER JOIN (SELECT ch_sucursal,dt_fecha,ch_documento
				FROM val_ta_detalle VTD
					INNER JOIN comb_ta_combustibles Co
					on VTD.ch_articulo=Co.ch_codigocombustible and Co.ch_codigocombex!='GL'
				GROUP BY ch_documento,ch_sucursal,dt_fecha ) VD on VC.ch_documento = VD.ch_documento and VC.ch_sucursal = VD.ch_sucursal and VC.dt_fecha = VD.dt_fecha
			INNER JOIN (SELECT cli_codigo 
				FROM int_clientes 
				WHERE cli_anticipo = 'N' AND cli_ndespacho_efectivo != 1) C ON C.cli_codigo=VC.ch_cliente";

		//CREDITOS ANTICIPOS
		$sqlC = "SELECT	SUM(CASE WHEN VC.ch_estado='1' THEN VC.nu_importe ELSE 0 END)
			FROM	(SELECT ch_estado,ch_documento,ch_cliente,nu_importe,ch_tarjeta,ch_caja,ch_lado,ch_turno 
				FROM val_ta_cabecera 
				WHERE ch_sucursal='".pg_escape_string($estaciones)."' and dt_fecha between to_date('".pg_escape_string($desde)."','DD/MM/YYYY') and to_date('".pg_escape_string($hasta)."','DD/MM/YYYY')) VC 
			INNER JOIN (SELECT ch_documento
				FROM 	val_ta_detalle VTD
					INNER JOIN comb_ta_combustibles Co
					ON VTD.ch_articulo=Co.ch_codigocombustible and Co.ch_codigocombex!='GL'
				GROUP BY ch_documento ) VD ON VD.ch_documento = VC.ch_documento
			INNER JOIN (SELECT cli_codigo 
				FROM 	int_clientes 
				WHERE 	cli_anticipo='S' AND cli_ndespacho_efectivo != 1) C ON C.cli_codigo = VC.ch_cliente";

		//EFECTIVO SOLES
		
		$sqlD = "SELECT sum(D.importe) 
			FROM (SELECT ch_almacen,dt_dia,ch_posturno,ch_codigo_trabajador, sum(nu_importe) as importe  FROM pos_depositos_diarios 
			WHERE 	ch_almacen='" . pg_escape_string($estaciones) . "' 
				and ch_moneda='01' and ch_valida='S' 
				and dt_dia between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
			GROUP BY ch_almacen,dt_dia,ch_posturno,ch_codigo_trabajador) D
			INNER JOIN (SELECT ch_sucursal,dt_dia,ch_posturno,ch_codigo_trabajador
				FROM 	pos_historia_ladosxtrabajador PT
				INNER JOIN (SELECT lado,prod1 FROM pos_cmblados) L ON L.lado = PT.ch_lado and prod1!='GL'
				GROUP BY ch_sucursal,dt_dia,ch_posturno,ch_codigo_trabajador) T 
				ON 	T.ch_sucursal = D.ch_almacen 
					and T.ch_posturno = D.ch_posturno
					and T.ch_codigo_trabajador = D.ch_codigo_trabajador
					and T.dt_dia = D.dt_dia";

		echo "\n".'- EFECTIVO SOLES COMBUSTIBLE:'.$sqlD.' -';

		//EFECTIVO DOLARES
		$sqlE = "SELECT sum(D.importe), sum(D.dolar)
			FROM (SELECT
					pos.ch_almacen,
					pos.dt_dia,
					pos.ch_posturno,
					pos.ch_codigo_trabajador,
					sum(pos.nu_importe * tc.tca_venta_oficial) as importe,
					sum(pos.nu_importe) as dolar
				 FROM
					pos_depositos_diarios pos
					LEFT JOIN int_tipo_cambio tc ON (pos.dt_dia = tc.tca_fecha)
				 WHERE
					ch_almacen='" . pg_escape_string($estaciones) . "' 
					and ch_moneda!='01' and ch_valida='S' 
					and dt_dia between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
				 GROUP BY
					ch_almacen,
					dt_dia,
					ch_posturno,
					ch_codigo_trabajador) D
			INNER JOIN (SELECT ch_sucursal,dt_dia,ch_posturno,ch_codigo_trabajador
				FROM pos_historia_ladosxtrabajador PT
				INNER JOIN (SELECT lado,prod1 FROM pos_cmblados) L ON L.lado = PT.ch_lado and prod1!='GL'
				GROUP BY ch_sucursal,dt_dia,ch_posturno,ch_codigo_trabajador) T
				ON 	T.ch_sucursal = D.ch_almacen 
					and T.ch_posturno = D.ch_posturno
					and T.ch_codigo_trabajador = D.ch_codigo_trabajador
					and T.dt_dia = D.dt_dia";

		echo "\n".'- EFECTIVO DOLARES COMBUSTIBLE:'.$sqlE.' -';

		/*FALTANTES
		$sqlH = "SELECT 
				sum(-importe)
			FROM
				comb_diferencia_trabajador FAL
			WHERE
				FAL.dia between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
				AND FAL.importe < 0
				AND FAL.es = '" . pg_escape_string($estaciones) . "'
				AND FAL.ch_codigo_trabajador not in (select
					tra.ch_codigo_trabajador
				from
					pos_historia_ladosxtrabajador tra
						LEFT JOIN
							pos_cmblados lad ON(lad.lado = tra.ch_lado)	
				where
					dt_dia between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
					AND lad.prod1 = 'GL'
				GROUP BY
					tra.ch_codigo_trabajador)";*/

		$sqlH = "SELECT sum(-importe)
			FROM	comb_diferencia_trabajador CD
				INNER JOIN (SELECT ch_codigo_trabajador,ch_posturno,dt_dia FROM pos_historia_ladosxtrabajador T INNER JOIN (SELECT lado FROM pos_cmblados where prod1!='GL') L on L.lado= T.ch_lado WHERE ch_sucursal='" . pg_escape_string($estaciones) . "' group by ch_codigo_trabajador,ch_posturno,dt_dia,ch_tipo having ch_tipo='C' ORDER BY dt_dia) LT
				on CD.ch_codigo_trabajador=LT.ch_codigo_trabajador and CD.turno=LT.ch_posturno and CD.dia=LT.dt_dia
			WHERE 	importe!=0 and importe<0 and es='" . pg_escape_string($estaciones) . "' and dia between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')";


		//SOBRANTES
		$sqlI = "SELECT sum(-importe)
			FROM	comb_diferencia_trabajador CD
				INNER JOIN (SELECT ch_codigo_trabajador,ch_posturno,dt_dia FROM pos_historia_ladosxtrabajador T INNER JOIN (SELECT lado FROM pos_cmblados where prod1!='GL') L on L.lado= T.ch_lado WHERE ch_sucursal='" . pg_escape_string($estaciones) . "' group by ch_codigo_trabajador,ch_posturno,dt_dia,ch_tipo having ch_tipo='C' ORDER BY dt_dia) LT
				on CD.ch_codigo_trabajador=LT.ch_codigo_trabajador and CD.turno=LT.ch_posturno and CD.dia=LT.dt_dia
			WHERE 	importe!=0 and importe>0 and es='" . pg_escape_string($estaciones) . "' and dia between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')";

		//DIFERENCIAS MANUALES
		$sqlR = "SELECT sum(importe)
			FROM	comb_diferencia_trabajador CD
				INNER JOIN (SELECT ch_codigo_trabajador,ch_posturno,dt_dia FROM pos_historia_ladosxtrabajador T INNER JOIN (SELECT lado FROM pos_cmblados where prod1!='GL') L on L.lado= T.ch_lado WHERE ch_sucursal='" . pg_escape_string($estaciones) . "' group by ch_codigo_trabajador,ch_posturno,dt_dia,ch_tipo having ch_tipo='C' ORDER BY dt_dia) LT
				on CD.ch_codigo_trabajador=LT.ch_codigo_trabajador and CD.turno=LT.ch_posturno and CD.dia=LT.dt_dia
			WHERE 	importe!=0 and CD.flag!=0 and es='" . pg_escape_string($estaciones) . "' and dia between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')";

		echo "\n".'- COMBUSTIBLE:'.$sqlA.' -';

		//RARO
		$raro = "SELECT
				D.importe,
				D.dt_dia
			 FROM (SELECT ch_almacen,dt_dia,ch_posturno,ch_codigo_trabajador, sum(nu_importe) as importe  FROM pos_depositos_diarios 
			 WHERE 	ch_almacen='002' 
				and ch_moneda='01' and ch_valida='S' 
				and dt_dia between to_date('01/04/2013', 'DD/MM/YYYY') and to_date('01/04/2013', 'DD/MM/YYYY') and ch_posturno = '1'
			 GROUP BY ch_almacen,dt_dia,ch_posturno,ch_codigo_trabajador) D
			 INNER JOIN (SELECT ch_sucursal,dt_dia,ch_posturno,ch_codigo_trabajador
				FROM 	pos_historia_ladosxtrabajador PT
				INNER JOIN (SELECT lado,prod1 FROM pos_cmblados) L ON L.lado = PT.ch_lado and prod1='GL'
				GROUP BY ch_sucursal,dt_dia,ch_posturno,ch_codigo_trabajador) T 
				ON 	T.ch_sucursal = D.ch_almacen 
					and T.ch_posturno = D.ch_posturno
					and T.ch_codigo_trabajador = D.ch_codigo_trabajador
					and T.dt_dia = D.dt_dia";


		echo "\n".'- JARCORRRRRRRRRRRRRRRRRRRRR:'.$raro.' -';

		if ($sqlca->query($sqlA) < 0) 
			return false;
		$result = Array();
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$ch_sucursal = pg_escape_string($estaciones);
			$propio = ($propiedad[$ch_sucursal]=='S'?"ESTACION":"OTROS");
			$ch_sucursal = $almacenes[$ch_sucursal];
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][0] = $a[0]; // VALOR
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][1] = $a[1]; // AFERICION
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][2] = $a[0]-$a[1]; //$a[2] -> VALOR - AFERICION = VENTA
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][3] = '0';
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][4] = '0';
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][5] = $a[3]; // TARJETAS DE CREDITO
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][6] = $a[4]; // DESCUENTOS
		}

		echo "\n".'- COMBUSTIBLE:'.$sqlA1.' -';
		if ($sqlca->query($sqlA1) < 0) 
			return false;
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a1 = $sqlca->fetchRow();
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][12] = $a1[0];
		}

		echo "\n".'- COMBUSTIBLE CREDITOS CLIENTES: '.$sqlB.' -';
		if ($sqlca->query($sqlB) < 0) 
			return false;
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$b = $sqlca->fetchRow();
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][3] = $b[0];
		}

		echo "\n".'- COMBUSTIBLE CREDITOS ANTICIPOS: '.$sqlC.' -';
		if ($sqlca->query($sqlC) < 0) 
			return false;
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$c = $sqlca->fetchRow(); 
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][4] = $c[0];
		}

		echo "\n".'- COMBUSTIBLE EFECTIVOS SOLES: '.$sqlD.' -';
		if ($sqlca->query($sqlD) < 0) 
			return false;
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$d = $sqlca->fetchRow();
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][7] = $d[0];
		}

		echo "\n".'- COMBUSTIBLE EFECTIVOS DOLARES: '.$sqlE.' -';
		if ($sqlca->query($sqlE) < 0) 
			return false;
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$e = $sqlca->fetchRow();
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][8] = $e[0];
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][11] = $e[1];
		}

		echo "\n".'- COMBUSTIBLE FALTANTES: '.$sqlH.' -';
		if ($sqlca->query($sqlH) < 0) 
			return false;
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$h = $sqlca->fetchRow();
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][9] = $h[0];
		}

		echo "\n".'- COMBUSTIBLE SOBRANTES: '.$sqlI.' -';
		if ($sqlca->query($sqlI) < 0) 
			return false;
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$ii = $sqlca->fetchRow();
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][10] = $ii[0];
		}

		echo "\n".'- COMBUSTIBLE DIFERENCIAS MANUALES:'.$sqlR.' -'; // diferencias manuales
		if ($sqlca->query($sqlR) < 0) 
			return false;
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$rr = $sqlca->fetchRow();
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][13] = $rr[0];
		}

		if ($sqlca->query($raro) < 0) 
			return false;
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$zz = $sqlca->fetchRow();
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][14] = $zz[0];
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][15] = $zz[1];
		}

		return $result;	
	}

	function obtieneGLP($desde, $hasta, $estaciones) {
		global $sqlca;
	
		$propiedad = ParteLiquidacionModel::obtenerPropiedadAlmacenes();
		$almacenes = ParteLiquidacionModel::obtieneListaEstaciones();

		$sqlA = "
		SELECT
			SUM(C.valor) AS valor,
			SUM(A.afericion) AS afericion,
			--SUM(C.venta) AS venta,
			SUM(C.valor - A.afericion) AS venta,
			SUM(TC.tarjetascredito) AS tarjetascredito,
			SUM(TC.descuentos) AS descuentos
		FROM
			(SELECT
				ch_codigocombustible AS combustible,
				SUM(nu_ventavalor) AS valor
			FROM
				comb_ta_contometros 
			WHERE
				ch_codigocombustible='11620307'
				AND ch_sucursal = '" . pg_escape_string($estaciones) . "' 
				AND dt_fechaparte BETWEEN to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
			GROUP BY
				ch_codigocombustible) C

			LEFT JOIN (SELECT
					t.codigo,
					SUM(CASE WHEN t.fpago='2' THEN t.importe ELSE 0 END) AS tarjetascredito,
					SUM(CASE WHEN t.tm='V' THEN (CASE WHEN (t.importe<0) THEN  -1*t.importe ELSE (CASE WHEN (t.importe>0 and t.grupo='D') THEN -1*t.importe ELSE 0 END)  END) END) AS descuentos
				    FROM
					pos_trans". substr($desde,6,4) . substr($desde,3,2) . " t LEFT JOIN int_clientes k on k.cli_ruc = t.ruc AND k.cli_ndespacho_efectivo != 1
				    WHERE
					t.es = '" . pg_escape_string($estaciones) . "'
					AND t.tipo = 'C'
					AND t.dia BETWEEN to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
				    group by
					t.codigo) TC
				    on
					TC.codigo = C.combustible

			INNER JOIN (SELECT ch_codigocombustible,ch_nombrebreve as nombre from comb_ta_combustibles) Co
			on Co.ch_codigocombustible = C.combustible

			LEFT JOIN

			(
				SELECT 
					sum(af.importe) as afericion,
					af.codigo as codigo
				FROM 
					pos_ta_afericiones af
				WHERE
					af.codigo='11620307'
					AND af.es='" . pg_escape_string($estaciones) . "' 
					AND af.dia between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')		
				GROUP BY
					af.codigo
			) A on A.codigo = C.combustible;
		";

		$sqlA1 = "SELECT CASE WHEN CH_RESPONSABLE<>'' THEN 'Parte Manual' ELSE '' END 
			FROM 	comb_ta_contometros 
			order by CH_RESPONSABLE desc";

		//CREDITOS CLIENTES
/*		$sqlB ="SELECT	SUM(CASE WHEN VC.ch_estado='1' THEN VC.nu_importe ELSE 0 END)
			FROM	(SELECT ch_estado,ch_documento,ch_cliente,nu_importe,ch_tarjeta,ch_caja,ch_lado,ch_turno 
				FROM 	val_ta_cabecera 
				WHERE 	ch_sucursal='" . pg_escape_string($estaciones) . "' and dt_fecha between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')) VC 

			INNER JOIN (SELECT ch_documento
				FROM 	val_ta_detalle VTD
					INNER JOIN comb_ta_combustibles Co
					on VTD.ch_articulo=Co.ch_codigocombustible and Co.ch_codigocombex='GL'
				GROUP BY ch_documento ) VD on VD.ch_documento = VC.ch_documento
			INNER JOIN (SELECT cli_codigo 
				FROM int_clientes 
				WHERE cli_anticipo='N' AND cli_ndespacho_efectivo != 1) C on C.cli_codigo=VC.ch_cliente";*/
		$sqlB =	"
			SELECT
				sum(x.n)
			FROM
				(SELECT
					CASE
						WHEN vc.ch_estado = '1' THEN vc.nu_importe
					ELSE 0
						END AS n
			FROM
				val_ta_detalle vd
				LEFT JOIN val_ta_cabecera vc ON (vd.ch_sucursal = vc.ch_sucursal AND vd.dt_fecha = vc.dt_fecha AND vd.ch_documento = vc.ch_documento)
				LEFT JOIN int_clientes c ON (vc.ch_cliente = c.cli_codigo)
			WHERE
				vd.ch_sucursal = '" . pg_escape_string($estaciones) . "'
				AND vd.ch_articulo = '11620307'
				AND vd.dt_fecha BETWEEN to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
				AND c.cli_anticipo='N'
				AND c.cli_ndespacho_efectivo != 1) x";

		//CREDITOS ANTICIPOS
		$sqlC = "SELECT	SUM(CASE WHEN VC.ch_estado='1' THEN VC.nu_importe ELSE 0 END)
			FROM	(SELECT ch_estado,ch_documento,ch_cliente,nu_importe,ch_tarjeta,ch_caja,ch_lado,ch_turno 
				FROM val_ta_cabecera 
				WHERE ch_sucursal='" . pg_escape_string($estaciones) . "' and dt_fecha between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')) VC 
			INNER JOIN (SELECT ch_documento
				FROM 	val_ta_detalle VTD
					INNER JOIN comb_ta_combustibles Co
					on VTD.ch_articulo=Co.ch_codigocombustible and Co.ch_codigocombex='GL'
				GROUP BY ch_documento ) VD on VD.ch_documento = VC.ch_documento
			INNER JOIN (SELECT cli_codigo 
				FROM int_clientes 
				WHERE cli_anticipo='S' AND cli_ndespacho_efectivo != 1) C on C.cli_codigo=VC.ch_cliente";

		//EFECTIVO SOLES
		$sqlD = "SELECT sum(D.importe) 
			FROM 	(SELECT ch_almacen,dt_dia,ch_posturno,ch_codigo_trabajador,sum(nu_importe) as importe  FROM pos_depositos_diarios 
			WHERE 	ch_almacen='" . pg_escape_string($estaciones) . "' 
				and ch_moneda='01' and ch_valida='S' 
				and dt_dia between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
			GROUP BY ch_almacen,dt_dia,ch_posturno,ch_codigo_trabajador) D
			INNER JOIN (SELECT ch_sucursal,dt_dia,ch_posturno,ch_codigo_trabajador
				FROM 	pos_historia_ladosxtrabajador PT
					INNER JOIN (SELECT lado,prod1 FROM pos_cmblados) L on L.lado = PT.ch_lado and prod1='GL'
					group by ch_sucursal,dt_dia,ch_posturno,ch_codigo_trabajador) T on T.ch_sucursal = D.ch_almacen 
					and T.ch_posturno = D.ch_posturno
					and T.ch_codigo_trabajador = D.ch_codigo_trabajador
					and T.dt_dia = D.dt_dia";
			
		//EFECTIVO DOLARES
					
		$sqlE = "SELECT sum(D.importe) 
			FROM 	(SELECT ch_almacen,dt_dia,ch_posturno,ch_codigo_trabajador,sum(nu_importe*nu_tipo_cambio) as importe  FROM pos_depositos_diarios 
			WHERE 	ch_almacen='" . pg_escape_string($estaciones) . "' AND ch_moneda!='01' and ch_valida='S' 
				AND dt_dia between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
			GROUP BY ch_almacen,dt_dia,ch_posturno,ch_codigo_trabajador) D
			INNER JOIN (SELECT ch_sucursal,dt_dia,ch_posturno,ch_codigo_trabajador
				FROM 	pos_historia_ladosxtrabajador PT
				INNER JOIN (SELECT lado,prod1 FROM pos_cmblados) L on L.lado = PT.ch_lado and prod1='GL'
				GROUP BY ch_sucursal,dt_dia,ch_posturno,ch_codigo_trabajador) T on T.ch_sucursal = D.ch_almacen 
					and T.ch_posturno = D.ch_posturno
					and T.ch_codigo_trabajador = D.ch_codigo_trabajador
					and T.dt_dia = D.dt_dia";
	
		//FALTANTES
		$sqlH = "SELECT sum(-importe)
			FROM	comb_diferencia_trabajador CD
				INNER JOIN (SELECT ch_codigo_trabajador,ch_posturno,dt_dia FROM pos_historia_ladosxtrabajador T INNER JOIN (SELECT lado FROM pos_cmblados where prod1='GL') L on L.lado= T.ch_lado WHERE ch_sucursal='" . pg_escape_string($estaciones) . "' group by ch_codigo_trabajador,ch_posturno,dt_dia,ch_tipo having ch_tipo='C' ORDER BY dt_dia) LT
				ON CD.ch_codigo_trabajador=LT.ch_codigo_trabajador and CD.turno=LT.ch_posturno and CD.dia=LT.dt_dia
			WHERE 	importe<0 and es='" . pg_escape_string($estaciones) . "' and dia between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')";

		//SOBRANTES
		$sqlI = "SELECT sum(-importe)
			FROM	comb_diferencia_trabajador CD
				INNER JOIN (SELECT ch_codigo_trabajador,ch_posturno,dt_dia FROM pos_historia_ladosxtrabajador T INNER JOIN (SELECT lado FROM pos_cmblados where prod1='GL') L on L.lado= T.ch_lado WHERE ch_sucursal='" . pg_escape_string($estaciones) . "' group by ch_codigo_trabajador,ch_posturno,dt_dia,ch_tipo having ch_tipo='C' ORDER BY dt_dia) LT
				ON CD.ch_codigo_trabajador=LT.ch_codigo_trabajador and CD.turno=LT.ch_posturno and CD.dia=LT.dt_dia
			WHERE 	importe>0 and es='" . pg_escape_string($estaciones) . "' and dia between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')";

		echo "\n".'- GLP:'.$sqlA.' -';
		if ($sqlca->query($sqlA) < 0) 
			return false;
		$result = Array();
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$ch_sucursal = pg_escape_string($estaciones);
			$propio = ($propiedad[$ch_sucursal]=='S'?"ESTACION":"OTROS");
			$ch_sucursal = $almacenes[$ch_sucursal];
	 		@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][0] = $a[0];
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][1] = $a[1];
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][2] = $a[2];
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][5] = $a[3];
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][6] = $a[4];
		}

		echo "\n".'GLP:'.$sqlA1.' -';
		if ($sqlca->query($sqlA1) < 0) 
			return false;
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a1 = $sqlca->fetchRow();
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][11] = $a1[0];
		}

		echo "\n".'- GLP CREDITOS CLIENTES: '.$sqlB.' -';
		if ($sqlca->query($sqlB) < 0) 
			return false;
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$b = $sqlca->fetchRow();
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][3] = $b[0];
		}

		echo "\n".'- GLP CREDITOS ANTICIPOS: '.$sqlC.' -';
		if ($sqlca->query($sqlC) < 0) 
			return false;
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$c = $sqlca->fetchRow();
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][4] = $c[0];
		}

		echo "\n".'- GLP EFECTIVO SOLES: '.$sqlD.' -';
		if ($sqlca->query($sqlD) < 0) 
			return false;
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$d = $sqlca->fetchRow();
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][7] = $d[0];
		}

		echo "\n".'- GLP EFECTIVO DOLARES: '.$sqlE.' -';
		if ($sqlca->query($sqlE) < 0) 
			return false;
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$e = $sqlca->fetchRow();
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][8] = $e[0];
		}

		echo "\n".'- GLP FALTANTES: '.$sqlH.' -';
		if ($sqlca->query($sqlH) < 0) 
			return false;
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$h = $sqlca->fetchRow();
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][9] = $h[0];
		}

		echo "\n".'- GLP SOBRANTES: '.$sqlI.' -';
		if ($sqlca->query($sqlI) < 0) 
			return false;
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$ii = $sqlca->fetchRow();
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][10] = $ii[0];
		}

		return $result;
	}

	function obtieneMarket($desde, $hasta, $estaciones) {
		global $sqlca;
	
	$propiedad = ParteLiquidacionModel::obtenerPropiedadAlmacenes();
	$almacenes = ParteLiquidacionModel::obtieneListaEstaciones();

	$sqlA = "SELECT SUM(F.nu_fac_valortotal) AS ventatienda
		FROM 	fac_ta_factura_cabecera F LEFT JOIN int_clientes c on F.cli_codigo=c.cli_codigo AND c.cli_ndespacho_efectivo != 1
		WHERE
			F.ch_fac_seriedocumento='" . pg_escape_string($estaciones) . "' AND 
			F.ch_fac_tipodocumento='45' AND
			F.dt_fac_fecha BETWEEN to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') AND to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')";

	$sqlA1 = "SELECT
			SUM(CASE WHEN t.fpago='2' THEN t.importe ELSE 0 END) AS tarjetascredito,
			SUM(CASE WHEN t.tm='V' THEN (CASE WHEN t.importe<0 THEN t.importe ELSE 0 END) ELSE 0 END) AS descuentos
		 FROM
			pos_trans". substr($desde,6,4) . substr($desde,3,2) . " t LEFT JOIN int_clientes k on k.cli_ruc = t.ruc AND k.cli_ndespacho_efectivo != 1
		 WHERE
			t.es='" . pg_escape_string($estaciones) . "' AND
			date(t.dia) BETWEEN to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') AND to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY') 
			and t.codigo not in (select ch_codigocombustible from comb_ta_combustibles)";
	
	//CREDITOS CLIENTES
	$sqlB = "SELECT
			SUM(CASE WHEN VC.ch_estado='1' THEN VC.nu_importe ELSE 0 END)
		 FROM
			(SELECT
				ch_estado,ch_documento,ch_cliente,nu_importe,ch_tarjeta,ch_caja,ch_lado,ch_turno 
			 FROM
				val_ta_cabecera 
			 WHERE
				ch_sucursal='" . pg_escape_string($estaciones) . "' and dt_fecha between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')) VC 
			INNER JOIN (SELECT
							ch_documento
				             FROM
							val_ta_detalle VTD 
					     WHERE
							ch_articulo not in (select ch_codigocombustible from comb_ta_combustibles)
							AND ch_sucursal='" . pg_escape_string($estaciones) . "' and dt_fecha between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
						GROUP BY ch_documento) VD ON VD.ch_documento = VC.ch_documento
			INNER JOIN (SELECT
						cli_codigo 
				     FROM
						int_clientes 
				     WHERE
						cli_anticipo='N' AND cli_ndespacho_efectivo != 1) C ON C.cli_codigo=VC.ch_cliente";

	//CREDITOS ANTICIPOS
	$sqlC = "
		SELECT
			SUM(VaCa.nu_importe) as importe
		FROM
			val_ta_detalle VTD
			JOIN val_ta_cabecera VaCa ON(VTD.ch_sucursal = VaCa.ch_sucursal AND VTD.dt_fecha = VaCa.dt_fecha AND VTD.ch_documento = VaCa.ch_documento)
			LEFT JOIN int_clientes IC ON (VaCa.ch_cliente = IC.cli_codigo)
		WHERE
			VaCa.ch_sucursal = '".pg_escape_string($estaciones) . "'
			AND IC.cli_ndespacho_efectivo != 1
			AND IC.cli_anticipo = 'S'
			AND VTD.ch_articulo NOT IN (select ch_codigocombustible from comb_ta_combustibles)
			AND VaCa.dt_fecha BETWEEN to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY');
	";

	//OPTIMIZACION CODIGO DE DEPOSITOS 03/05/17 CSR
	$sqlD0 = "
	SELECT
		PT1.ch_sucursal,
		PT1.dt_dia,
		PT1.ch_posturno,
		PT1.ch_codigo_trabajador
	FROM
		pos_historia_ladosxtrabajador PT1
	WHERE
		PT1.ch_tipo = 'M'
		AND PT1.dt_dia BETWEEN to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') AND to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
		AND PT1.ch_codigo_trabajador NOT IN (
		SELECT
			PT2.ch_codigo_trabajador
		FROM
			pos_historia_ladosxtrabajador PT2
		WHERE
			PT2.ch_tipo = 'C'
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
		PT1.ch_codigo_trabajador;
	";
		
	if ($sqlca->query($sqlD0) < 0) 
		return false;
	for ($i = 0; $i < $sqlca->numrows(); $i++) {
    	$d = $sqlca->fetchRow();
    	$textoarmado = $textoarmado."(ch_almacen = '".trim($d[0])."' AND dt_dia = '".trim($d[1])."' AND ch_posturno = '".trim($d[2])."' AND ch_codigo_trabajador = '".trim($d[3])."') OR ";
	}

	$textofinal = substr($textoarmado,0,-3);
/*
	echo "<pre>";
	echo "CSR: Creado";
	var_dump($textofinal);
	echo "</pre>";
*/

	if($textofinal){
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
	
	echo "\n".'- EFECTIVO SOLES MARKET:'.$sqlD.' -';

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

	echo "\n".'- EFECTIVO DOLARES MARKET:'.$sqlE.' -';

	/*
	//EFECTIVO SOLES
	$sqlD = "SELECT
			sum(D.importe) 
		 FROM
			(SELECT ch_almacen,dt_dia,ch_posturno,ch_codigo_trabajador,sum(nu_importe) as importe  FROM pos_depositos_diarios 
		 WHERE 	     ch_almacen='" . pg_escape_string($estaciones) . "' 
			 and ch_moneda='01' and ch_valida='S' 
			 and dt_dia between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
		GROUP BY ch_almacen,dt_dia,ch_posturno,ch_codigo_trabajador) D
		INNER JOIN (SELECT PT1.ch_sucursal, PT1.dt_dia, PT1.ch_posturno, PT1.ch_codigo_trabajador
			FROM pos_historia_ladosxtrabajador PT1
			where PT1.ch_tipo='M' 
			and PT1.ch_codigo_trabajador not in 
			(SELECT PT2.ch_codigo_trabajador 
			FROM pos_historia_ladosxtrabajador PT2 
				where PT2.ch_tipo='C' and PT2.dt_dia=PT1.dt_dia and PT2.ch_posturno=PT1.ch_posturno
				group by PT2.ch_sucursal,PT2.dt_dia,PT2.ch_codigo_trabajador)
				group by PT1.ch_sucursal, PT1.dt_dia, PT1.ch_posturno, PT1.ch_codigo_trabajador) T
				on T.ch_sucursal = D.ch_almacen 
				and T.ch_posturno = D.ch_posturno
				and T.ch_codigo_trabajador = D.ch_codigo_trabajador
				and T.dt_dia = D.dt_dia";
	*/
	//EFECTIVO DOLARES
	/*$sqlE = "SELECT sum(D.importe) 
		FROM (SELECT ch_almacen,dt_dia,ch_posturno,ch_codigo_trabajador,sum(nu_importe*nu_tipo_cambio) as importe  FROM pos_depositos_diarios 
		WHERE ch_almacen='" . pg_escape_string($estaciones) . "' 
			and ch_moneda!='01' and ch_valida='S' 
			and dt_dia between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
		group by     ch_almacen,dt_dia,ch_posturno,ch_codigo_trabajador) D
		INNER JOIN (SELECT PT1.ch_sucursal,PT1.dt_dia,PT1.ch_posturno,PT1.ch_codigo_trabajador
			FROM pos_historia_ladosxtrabajador PT1
			WHERE PT1.ch_tipo='M' 
			AND PT1.ch_codigo_trabajador not in 
			(SELECT PT2.ch_codigo_trabajador 
			FROM pos_historia_ladosxtrabajador PT2
			WHERE PT2.ch_tipo='C' and PT2.dt_dia=PT1.dt_dia and PT2.ch_posturno=PT1.ch_posturno
			GROUP BY PT2.ch_sucursal,PT2.dt_dia,PT2.ch_codigo_trabajador )
			group by PT1.ch_sucursal,PT1.dt_dia,PT1.ch_posturno,PT1.ch_codigo_trabajador) T
		on 	T.ch_sucursal = D.ch_almacen 
			and T.ch_posturno = D.ch_posturno
			and T.ch_codigo_trabajador = D.ch_codigo_trabajador
			and T.dt_dia = D.dt_dia";
*/
	//FALTANTES
	$sqlH="SELECT 	sum(-importe)
		FROM	comb_diferencia_trabajador CD
		WHERE 	importe<0 and es='".pg_escape_string($estaciones)."' and tipo='M' and  dia between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')";

	//SOBRANTES
	$sqlI="SELECT 	sum(-importe)
		FROM	comb_diferencia_trabajador CD
		WHERE 	importe>0 and es='" . pg_escape_string($estaciones)."' and tipo='M' and dia between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')";

	//DATOS MARKET
		echo "\n".'- MARKET:'.$sqlA.' -';
		if ($sqlca->query($sqlA) < 0) 
			return false;
		$result = Array();
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$ch_sucursal = pg_escape_string($estaciones);
			$propio = ($propiedad[$ch_sucursal]=='S'?"ESTACION":"OTROS");
			$ch_sucursal = $almacenes[$ch_sucursal];
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][0] = $a[0];
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][1] = '0';
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][2] = $a[0];
		}

		echo "\n".'- MARKET:'.$sqlA1.' -';
		if ($sqlca->query($sqlA1) < 0) 
			return false;
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a1 = $sqlca->fetchRow();
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][5] = $a1[0];
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][6] = $a1[1];
		}

		echo "\n".'- MARKET CREDITOS CLIENTES: '.$sqlB.' -';
		if ($sqlca->query($sqlB) < 0) 
			return false;
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    	$b = $sqlca->fetchRow();
		    	@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][3] = $b[0];
		}

		echo "\n".'- MARKET CREDITOS ANTICIPOS: '.$sqlC.' -';
		if ($sqlca->query($sqlC) < 0) 
			return false;
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$c = $sqlca->fetchRow();
		    	@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][4] = $c[0];
		}

		echo "\n".'- MARKET EFECTIVO SOLES: '.$sqlD.' -';
		if (!$textofinal) {
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][7] = 0.00;
		} else {
			if ($sqlca->query($sqlD) < 0) 
				return false;
			for ($i = 0; $i < $sqlca->numrows(); $i++) {
			   	$d = $sqlca->fetchRow();
			    @$result['propiedades'][$propio]['almacenes'][$ch_sucursal][7] = $d[0];
			}
		}

		echo "\n".'- MARKET EFECTIVO DOLARES: '.$sqlE.' -';
		if (!$textofinal) {
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][7] = 0.00;
		} else {
			if ($sqlca->query($sqlE) < 0) 
				return false;
			for ($i = 0; $i < $sqlca->numrows(); $i++) {
			    	$e = $sqlca->fetchRow();
			    	@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][8] = $e[0];
			}
		}

		echo "\n".'- MARKET FALTANTES: '.$sqlH.' -';
		if ($sqlca->query($sqlH) < 0) 
			return false;
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    	$h = $sqlca->fetchRow();
		    	@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][9] = $h[0];
		}

		echo "\n".'- MARKET SOBRANTES: '.$sqlI.' -';
		if ($sqlca->query($sqlI) < 0) 
			return false;
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    	$ii = $sqlca->fetchRow();
		    	@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][10] = $ii[0];
		}
		return $result;
    }

	function obtieneDocumentos($desde, $hasta, $estaciones) {
		global $sqlca;
	
		$propiedad = ParteLiquidacionModel::obtenerPropiedadAlmacenes();
		$almacenes = ParteLiquidacionModel::obtieneListaEstaciones();

		$pos_transYM = "pos_trans".substr($desde,6,4).substr($desde,3,2);
	
		$sqlF = "SELECT SUM(importe) FROM " . $pos_transYM . " WHERE es = '".pg_escape_string($estaciones)."' AND td = 'F' AND dia BETWEEN to_date('" . $desde . "','DD/MM/YYYY HH24:MI:SS') AND to_date('" . $hasta . "','DD/MM/YYYY HH24:MI:SS')";

		$sqlB = "SELECT SUM(importe) FROM " . $pos_transYM . " WHERE es = '".pg_escape_string($estaciones)."' AND td = 'B' AND dia BETWEEN to_date('" . $desde . "','DD/MM/YYYY HH24:MI:SS') AND to_date('" . $hasta . "','DD/MM/YYYY HH24:MI:SS')";

		$sqlN = "SELECT SUM(nu_importe) FROM val_ta_cabecera WHERE ch_sucursal = '".pg_escape_string($estaciones)."' AND dt_fecha BETWEEN to_date('" . $desde . "','DD/MM/YYYY') AND to_date('" . $hasta . "','DD/MM/YYYY')";

		$sqlA = "SELECT SUM(importe) FROM pos_ta_afericiones WHERE es = '".pg_escape_string($estaciones)."' AND dia BETWEEN to_date('" . $desde . "','DD/MM/YYYY HH24:MI:SS') AND to_date('" . $hasta . "','DD/MM/YYYY HH24:MI:SS')";

		$sqlDM = "SELECT SUM(nu_fac_valortotal) FROM fac_ta_factura_cabecera WHERE ch_almacen = '".pg_escape_string($estaciones)."' AND ch_fac_tipodocumento != '45' AND dt_fac_fecha BETWEEN to_date('" . $desde . "','DD/MM/YYYY') AND to_date('" . $hasta . "','DD/MM/YYYY')";
	
		echo "\n".'- TOTAL DE DOCUMENTOS MANUALES:'.$sqlD.' -';

		if ($sqlca->query($sqlF) < 0) 
			return false;
		$result = Array();
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$ch_sucursal = pg_escape_string($estaciones);
			$propio = ($propiedad[$ch_sucursal]=='S'?"ESTACION":"OTROS");
			$ch_sucursal = $almacenes[$ch_sucursal];
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][0] = '0';
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][1] = '0';
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][2] = $a[0];
		}

		//echo "\n".'- TOTAL DE BOLETAS: '.$sqlB.' -';

		if ($sqlca->query($sqlB) < 0) 
			return false;
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$b = $sqlca->fetchRow();
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][3] = $b[0];
		}

		//echo "\n".'- TOTAL DE NOTA DE DESPACHO: '.$sqlN.' -';

		if ($sqlca->query($sqlN) < 0) 
			return false;
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    	$c = $sqlca->fetchRow();
		    	@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][4] = $c[0];
		}

		//echo "\n".'- TOTAL DE NOTA DE AFERICIONES: '.$sqlA.' -';

		if ($sqlca->query($sqlA) < 0) 
			return false;
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    	$c = $sqlca->fetchRow();
		    	@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][5] = $c[0];
		}

		//echo "\n".'- TOTAL DE DOCUMENTOS MANUALES: '.$sqlA.' -';
		if ($sqlca->query($sqlDM) < 0) 
			return false;

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
	    	$c = $sqlca->fetchRow();
	    	@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][6] = $c[0];
		}
		return $result;
    }


	function obtieneGNV($desde, $hasta, $estaciones) {
	global $sqlca;
	
	$propiedad = ParteLiquidacionModel::obtenerPropiedadAlmacenes();
	$almacenes = ParteLiquidacionModel::obtieneListaEstaciones();

		$query = "SELECT 
				ch_almacen,          
				first(to_char(dt_fecha,'DD/MM/YYYY')),
				SUM(contometro_inicial),
				SUM(contometro_final),
				SUM(tot_cantidad),                
				SUM(tot_venta),
				SUM(tot_abono),
				SUM(tot_afericion),
				SUM(tot_cli_credito),
				SUM(tot_cli_anticipo),
				SUM(tot_tar_credito),
				SUM(tot_descuentos),
				SUM(tot_trab_faltantes),
				SUM(tot_trab_sobrantes),
				SUM(tot_soles),  
				SUM(tot_dolares),
				SUM(tot_surtidor_m3),
				SUM(tot_surtidor_soles),
				SUM(mermas_m3)
			FROM 
				comb_liquidaciongnv
			WHERE
				dt_fecha BETWEEN to_date('$desde','DD/MM/YYYY') AND to_date('$hasta','DD/MM/YYYY')
				AND ch_almacen = '".pg_escape_string($estaciones)."'
			GROUP BY
			ch_almacen;";

		echo $query;

		if ($sqlca->query($query) < 0) 
			return false;

		$result = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$ch_sucursal = pg_escape_string($estaciones);
			$propio = ($propiedad[$ch_sucursal]=='S'?"ESTACION":"OTROS");
			$ch_sucursal = $almacenes[$ch_sucursal];
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][0] = '0';
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][1] = '0';
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][2] = $a[0];
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][3] = $a[1];
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][4] = $a[2];
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][5] = $a[3];
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][6] = $a[4];
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][7] = $a[5];
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][8] = $a[6];
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][9] = $a[7];
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][10] = $a[8];
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][11] = $a[9];
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][12] = $a[10];
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][13] = $a[11];
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][14] = $a[12];
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][15] = $a[13];
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][16] = $a[14];
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][17] = $a[15];
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][18] = $a[16];
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][19] = $a[17];
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal][20] = $a[18];
		}

		return $result;
    	}
    
	function obtenerPropiedadAlmacenes() {
		global $sqlca;
	
		$sql = "SELECT ch_almacen, 'S' AS ch_almacen_propio
			FROM inv_ta_almacenes
			WHERE ch_clase_almacen='1'; ";

		if ($sqlca->query($sql) < 0) 
			return false;	
		$result = Array();
	
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();		    
			$result[$a[0]] = $a[1];
		}	
		return $result;
    	}   

    	function obtieneListaEstaciones() {
		global $sqlca;
	
		$sql = "SELECT ch_almacen, trim(ch_nombre_almacen)
			FROM inv_ta_almacenes
			WHERE ch_clase_almacen='1'
			ORDER BY ch_almacen ; ";
		if ($sqlca->query($sql) < 0) 
			return false;	
		$result = Array();
	
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    	$a = $sqlca->fetchRow();
		    	$result[$a[0]] = $a[0] . " - " . $a[1];
		}	
		return $result;
    	}
}
