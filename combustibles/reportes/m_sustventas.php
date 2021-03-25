<?php

class SustentoVentasModel extends Model {

	function obtieneTC($desde, $hasta, $estaciones) {
		global $sqlca;
	
		$sqlA = "
		SELECT
			tca_venta_oficial
		FROM
			int_tipo_cambio
		WHERE
			tca_fecha = to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY')
		";

		if ($sqlca->query($sqlA) < 0) 
			return false;

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a1 = $sqlca->fetchRow();
			@$result['propiedades']['0'] = $a1[0];
		}

		return $result;
    }

	function obtieneVales($desde, $hasta, $estaciones, $tipo) {
		global $sqlca;

		$propiedad = '';
		$almacenes = SustentoVentasModel::obtieneListaEstaciones();

		$cond = "";

		if ($tipo == 'T') {
			$cond .= "";
		} else if ($tipo == 'C') {
			$cond .= "inner join comb_ta_combustibles Co on VTD.ch_articulo=Co.ch_codigocombustible and ch_codigocombustible != '11620307'";
		} else {
			$cond .= "WHERE ch_articulo not in (select ch_codigocombustible from comb_ta_combustibles)";
		}

		$sql = "
		SELECT
			C.cli_ruc AS ruc,
			C.cli_razsocial	AS cliente,
			SUM(VD.galones),
			SUM(VC.nu_importe) AS importe 
	 	FROM
			val_ta_cabecera VC 
			INNER JOIN
			(SELECT
				ch_sucursal,
				dt_fecha,
				ch_documento,
				SUM(nu_cantidad) AS galones
			FROM
				val_ta_detalle VTD ".$cond."
			GROUP BY
				ch_sucursal,dt_fecha,ch_documento
			) AS VD ON (VC.ch_sucursal = VD.ch_sucursal and VC.dt_fecha = VD.dt_fecha and VC.ch_documento = VD.ch_documento)
			INNER JOIN
			(SELECT
				cli_codigo,
				cli_ruc,
				cli_razsocial
			FROM
				int_clientes
			WHERE
				cli_ndespacho_efectivo != 1
			) AS C ON VC.ch_cliente = C.cli_codigo
		WHERE		
			VC.ch_sucursal='" . pg_escape_string($estaciones) . "' 
			AND VC.dt_fecha between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY') 
		GROUP BY	
			VC.ch_cliente,
			C.cli_ruc,
			C.cli_razsocial 
		ORDER BY 	
			VC.ch_cliente
		";

		echo 'SUSTENTO:'.$sql;
		if ($sqlca->query($sql) < 0) 
			return false;

		$result = Array();
		$total = $sqlca->numrows()-1;

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$ch_sucursal = pg_escape_string($estaciones);
			$propio = ($propiedad[$ch_sucursal]=='S'?"ESTACION":"OTROS");

			@$result['propiedades'][$propio]['almacenes'][$i][0] = $a[0];
			@$result['propiedades'][$propio]['almacenes'][$i][1] = $a[1];
			@$result['propiedades'][$propio]['almacenes'][$i][2] = $a[2];
			@$result['propiedades'][$propio]['almacenes'][$i][3] = $a[3];
			@$result['propiedades'][$propio]['almacenes'][$total]['total'] += $a[3];
		}

		return $result;	
	}

	function obtieneValesGLP($desde, $hasta, $estaciones) {
		global $sqlca;

		$propiedad = '';
		$almacenes = SustentoVentasModel::obtieneListaEstaciones();

		$sql = "SELECT   
				cli.cli_ruc as ruc,
				cli.cli_razsocial as cliente,
				sum(VD.galones),
				sum(VC.nu_importe) as importe 
		 	FROM
				val_ta_cabecera VC 
				inner join
					(SELECT ch_sucursal,dt_fecha,ch_documento,sum(nu_cantidad) as galones
				FROM
					val_ta_detalle VTD
				inner join
					comb_ta_combustibles Co
					on VTD.ch_articulo=Co.ch_codigocombustible
					and ch_codigocombustible = '11620307'
				GROUP BY
					ch_sucursal,dt_fecha,ch_documento) VD 
					on VC.ch_sucursal = VD.ch_sucursal and VC.dt_fecha = VD.dt_fecha and VC.ch_documento = VD.ch_documento 
 				inner join (SELECT cli_codigo,cli_ruc,cli_razsocial FROM int_clientes WHERE cli_ndespacho_efectivo != 1) cli
				on VC.ch_cliente = cli.cli_codigo 
			WHERE		
				VC.ch_sucursal='" . pg_escape_string($estaciones) . "' 
				AND VC.dt_fecha between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY') 
			GROUP BY	
				VC.ch_cliente,
				cli.cli_ruc,
				cli.cli_razsocial 
			ORDER BY 	
				VC.ch_cliente;";

		echo 'SUSTENTO GLP:'.$sql;
		if ($sqlca->query($sql) < 0) 
			return false;

		$result = Array();
		$total = $sqlca->numrows()-1;

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$ch_sucursal = pg_escape_string($estaciones);
			$propio = ($propiedad[$ch_sucursal]=='S'?"ESTACION":"OTROS");

			@$result['propiedades'][$propio]['almacenes'][$i][0] = $a[0];
			@$result['propiedades'][$propio]['almacenes'][$i][1] = $a[1];
			@$result['propiedades'][$propio]['almacenes'][$i][2] = $a[2];
			@$result['propiedades'][$propio]['almacenes'][$i][3] = $a[3];
			@$result['propiedades'][$propio]['almacenes'][$total]['total'] += $a[3];
		}

		return $result;	
	}

	function obtieneTarjeta($desde, $hasta, $estaciones, $tipo) {
		global $sqlca;
		$propiedad = '';

		if ($tipo == 'T') {
			$sql = "SELECT 
					g.tab_descripcion as descripciontarjeta,
					SUM(t.importe)-SUM(COALESCE(t.km,0)) as importetarjeta
				FROM
					pos_trans". substr($desde,6,4) . substr($desde,3,2) . " t
					JOIN int_tabla_general g ON (g.tab_tabla='95' AND g.tab_elemento='00000'||t.at) 
					--LEFT JOIN int_clientes c on c.cli_ruc = t.ruc AND c.cli_ndespacho_efectivo != 1
				WHERE
					--t.codigo != '11620307' AND
					t.es = '" . pg_escape_string($estaciones) . "' AND
					t.fpago = '2' AND
					t.td != 'N' AND
					t.dia between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY') 
				GROUP BY 
					1 
				ORDER BY 
					g.tab_descripcion";
		} else if ($tipo == 'C') {
			$sql = "SELECT 
					g.tab_descripcion as descripciontarjeta,
					SUM(CASE WHEN t.td != 'N' AND t.fpago='2' THEN t.importe-COALESCE(t.km,0) ELSE 0 END) AS tarjetascredito
				FROM
					pos_trans". substr($desde,6,4) . substr($desde,3,2) . " t
					JOIN int_tabla_general g ON (g.tab_tabla='95' AND g.tab_elemento='00000'||t.at) 
					--LEFT JOIN int_clientes c on c.cli_ruc = t.ruc AND c.cli_ndespacho_efectivo != 1
				WHERE
					t.codigo != '11620307'
					AND t.es = '" . pg_escape_string($estaciones) . "'
					AND t.dia between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
					AND t.tipo = 'C'
				GROUP BY 
					1 
				ORDER BY 
					g.tab_descripcion";
		} else {
			$sql = "SELECT 
					g.tab_descripcion as descripciontarjeta,
					SUM(t.importe)-SUM(COALESCE(t.km,0)) as importetarjeta,
					t.tipo 
				FROM
					pos_trans". substr($desde,6,4) . substr($desde,3,2) . " t
					JOIN int_tabla_general g ON (g.tab_tabla='95' AND g.tab_elemento='00000'||t.at) 
					LEFT JOIN int_clientes c on c.cli_ruc = t.ruc AND c.cli_ndespacho_efectivo != 1
				WHERE
					t.es = '" . pg_escape_string($estaciones) . "' AND
					t.tipo = '" . pg_escape_string($tipo) . "' AND 
					t.fpago = '2' AND
					t.dia between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY') 
				GROUP BY 
					1, 3 
				ORDER BY 
					g.tab_descripcion";
		}

		echo "<pre>";
		echo 'TARJETA:'.$sql;
		echo "</pre>";
		if ($sqlca->query($sql) < 0) 
			return false;

		$result = Array();
		$total = $sqlca->numrows()-1;

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$ch_sucursal = pg_escape_string($estaciones);
			$propio = ($propiedad[$ch_sucursal]=='S'?"ESTACION":"OTROS");
			@$result['propiedades'][$propio]['almacenes'][$i][0] = $a[0];
			@$result['propiedades'][$propio]['almacenes'][$i][1] = $a[1];
			@$result['propiedades'][$propio]['almacenes'][$total]['total'] += $a[1];
		}

		return $result;	
    	}

	function obtieneTarjetaGLP($desde, $hasta, $estaciones) {
		global $sqlca;
		$propiedad = '';

		$query = "SELECT 
				g.tab_descripcion as descripciontarjeta,
				SUM(t.importe)-SUM(COALESCE(t.km,0)) as importetarjeta,
				t.tipo 
			FROM
				pos_trans". substr($desde,6,4) . substr($desde,3,2) . " t
				JOIN int_tabla_general g ON (g.tab_tabla='95' AND g.tab_elemento='00000'||t.at) 
				LEFT JOIN int_clientes c on c.cli_ruc = t.ruc AND c.cli_ndespacho_efectivo != 1
			WHERE
				t.codigo = '11620307' AND
				t.es = '" . pg_escape_string($estaciones) . "' AND
				t.tipo = 'C' AND 
				t.fpago = '2' AND
				t.dia between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY') 
			GROUP BY 
				1, 3 
			ORDER BY 
				g.tab_descripcion";

		echo "<pre>";
		echo 'TARJETA:'.$query;
		echo "</pre>";

		if ($sqlca->query($query) < 0) 
			return false;

		$result = Array();
		$total = $sqlca->numrows()-1;

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$ch_sucursal = pg_escape_string($estaciones);
			$propio = ($propiedad[$ch_sucursal]=='S'?"ESTACION":"OTROS");
			@$result['propiedades'][$propio]['almacenes'][$i][0] = $a[0];
			@$result['propiedades'][$propio]['almacenes'][$i][1] = $a[1];
			@$result['propiedades'][$propio]['almacenes'][$total]['total'] += $a[1];
		}

		return $result;	
    	}

	function obtieneEfectivos($desde, $hasta, $estaciones, $tipo) {
		global $sqlca;

		$propiedad = '';
		$almacenes = SustentoVentasModel::obtieneListaEstaciones();
		$cond      = "";

		if ($tipo == 'T') {
			$cond .= "";
		} else if ($tipo == 'C'){
			$cond .= " INNER JOIN
					(SELECT lado,prod1 FROM pos_cmblados) L on L.lado = PT.ch_lado and prod1 != 'GL' 
				   WHERE ch_tipo = '" . pg_escape_string($tipo) . "' ";
		} else {
			// $cond .= " WHERE
			// 		ch_tipo = '" . pg_escape_string($tipo) . "'
			// 		AND PT.ch_codigo_trabajador NOT IN 
			// 		(SELECT PT2.ch_codigo_trabajador 
			// 		FROM pos_historia_ladosxtrabajador PT2
			// 		WHERE PT2.ch_tipo='C' and PT2.dt_dia=PT.dt_dia and PT2.ch_posturno=PT.ch_posturno
			// 		GROUP BY PT2.ch_sucursal,PT2.dt_dia,PT2.ch_codigo_trabajador )

			// 	";

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
		
			echo "-----> PRUEBA".$adicional;

			if ($sqlca->query($adicional) < 0) 
				return false;
			for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    	$d = $sqlca->fetchRow();
		    	$textoarmado = $textoarmado."(ch_sucursal = '".trim($d[0])."' AND dt_dia = '".trim($d[1])."' AND ch_posturno = '".trim($d[2])."' AND ch_codigo_trabajador = '".trim($d[3])."') OR ";
		    }

			$adicional2 = substr($textoarmado,0,-3);

			$cond  .= " WHERE ( " . $adicional2 . " )";

		}

		$sql = "
		SELECT	
			'Venta',	
			Case when D.ch_moneda='01' then '' Else '$' End || ' ' || Case when D.ch_moneda='01' then null Else sum(Cast(nu_importe As decimal(10,2))) End  || ' ' || Case when D.ch_moneda='01' then '' Else 'Dolares Americanos' End,
			sum(Case when ch_moneda='01' then nu_importe Else nu_importe * nu_tipo_cambio End)
		FROM	
			pos_depositos_diarios D
			INNER JOIN (
			SELECT
				ch_sucursal,
				dt_dia,
				ch_posturno,
				ch_codigo_trabajador
			FROM
				pos_historia_ladosxtrabajador PT
			  	".
				$cond
				."
			GROUP BY
				ch_sucursal,dt_dia,ch_posturno,ch_codigo_trabajador
			) T 
			ON T.ch_sucursal = D.ch_almacen 
			AND T.ch_posturno = D.ch_posturno
			AND T.ch_codigo_trabajador = D.ch_codigo_trabajador
			AND T.dt_dia = D.dt_dia
		WHERE	
			D.ch_almacen = '" . pg_escape_string($estaciones) . "'  and D.ch_valida='S' 
			and D.dt_dia between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
		GROUP BY
			D.ch_moneda
		ORDER BY
			D.ch_moneda DESC;
		";

		echo "\n" . "EFECTIVO: \n" . $sql;

		if ($sqlca->query($sql) < 0) 
			return false;

		$result = Array();
		$total = $sqlca->numrows()-1;

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$ch_sucursal = pg_escape_string($estaciones);
			$propio = ($propiedad[$ch_sucursal]=='S'?"ESTACION":"OTROS");
	    	@$result['propiedades'][$propio]['almacenes'][$i][0] = $a[0];
	    	@$result['propiedades'][$propio]['almacenes'][$i][1] = $a[1];
	    	@$result['propiedades'][$propio]['almacenes'][$i][2] = $a[2];
	    	@$result['propiedades'][$propio]['almacenes'][$total]['total'] += $a[2];
		}
		return $result;	
	}

	function obtieneEfectivosGLP($desde, $hasta, $estaciones) {
		global $sqlca;

		$propiedad = '';
		$almacenes = SustentoVentasModel::obtieneListaEstaciones();

		$sql = "SELECT	
				'Venta',	
				Case when D.ch_moneda='01' then '' Else '$' End || ' ' || Case when D.ch_moneda='01' then null Else sum(Cast(nu_importe As decimal(10,2))) End  || ' ' || Case when D.ch_moneda='01' then '' Else 'Dolares Americanos' End,
				sum(Case when ch_moneda='01' then nu_importe Else nu_importe * nu_tipo_cambio End)
			FROM	
				pos_depositos_diarios D
				INNER JOIN
					(SELECT ch_sucursal,dt_dia,ch_posturno,ch_codigo_trabajador
				FROM
					pos_historia_ladosxtrabajador PT
				INNER JOIN (SELECT lado,prod1 FROM pos_cmblados) L ON L.lado = PT.ch_lado and prod1 = 'GL'
				WHERE
					ch_tipo = 'C' 
				GROUP BY
					ch_sucursal,dt_dia,ch_posturno,ch_codigo_trabajador) T
				on 	T.ch_sucursal = D.ch_almacen 
				and 	T.ch_posturno = D.ch_posturno
				and 	T.ch_codigo_trabajador = D.ch_codigo_trabajador
				and 	T.dt_dia = D.dt_dia
			WHERE	
				D.ch_almacen = '" . pg_escape_string($estaciones) . "'
				and D.ch_valida = 'S'
				and D.dt_dia between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
			GROUP BY
				D.ch_moneda
			ORDER BY
				D.ch_moneda DESC ";

		echo 'EFECTIVO:'.$sql;
		if ($sqlca->query($sql) < 0) 
			return false;

		$result = Array();
		$total = $sqlca->numrows()-1;

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$ch_sucursal = pg_escape_string($estaciones);
			$propio = ($propiedad[$ch_sucursal]=='S'?"ESTACION":"OTROS");

		    	@$result['propiedades'][$propio]['almacenes'][$i][0] = $a[0];
		    	@$result['propiedades'][$propio]['almacenes'][$i][1] = $a[1];
		    	@$result['propiedades'][$propio]['almacenes'][$i][2] = $a[2];
		    	@$result['propiedades'][$propio]['almacenes'][$total]['total'] += $a[2];
		}

		return $result;	
	}


	function obtieneFaltantes($desde, $hasta, $estaciones, $tipo) {
		global $sqlca;

		$propiedad = '';
		$almacenes = SustentoVentasModel::obtieneListaEstaciones();

		if ($tipo == 'T') {
			$sql = "SELECT	
					DT.ch_codigo_trabajador || '  ' || ch_nombre1 || ' ' || ch_apellido_paterno || ' ' || ch_apellido_materno as nombre, 
					case when DT.importe<0 then 'Faltante' else 'Sobrante' end as tipo,
					DT.importe as importe
				FROM		
					comb_diferencia_trabajador DT
					INNER JOIN pla_ta_trabajadores T ON T.ch_codigo_trabajador = DT.ch_codigo_trabajador
				WHERE		
					DT.importe!=0.00 and dia between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')";
		} else if ($tipo == 'C') {
			$sql = "SELECT	
					CD.ch_codigo_trabajador || '  ' || ch_nombre1 || ' ' || ch_apellido_paterno || ' ' || ch_apellido_materno as nombre, 
					case when CD.importe<0 then 'Faltante' else 'Sobrante' end as tipo,
					CD.importe as importe,
					CD.tipo as tip
				FROM	
					comb_diferencia_trabajador CD
				INNER JOIN
					pla_ta_trabajadores T ON T.ch_codigo_trabajador = CD.ch_codigo_trabajador
				INNER JOIN
					(SELECT ch_codigo_trabajador,ch_posturno,dt_dia FROM pos_historia_ladosxtrabajador T 
				INNER JOIN
					(SELECT lado FROM pos_cmblados where prod1 != 'GL') L on L.lado= T.ch_lado
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
							CD.ch_codigo_trabajador=LT.ch_codigo_trabajador and CD.turno=LT.ch_posturno and CD.dia=LT.dt_dia
				WHERE		
					CD.importe!=0.00
					AND dia between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
					AND (CD.tipo is null or CD.tipo = 'C')";
		} else {
			$sql = "SELECT	
					DT.ch_codigo_trabajador || '  ' || ch_nombre1 || ' ' || ch_apellido_paterno || ' ' || ch_apellido_materno as nombre, 
					case when DT.importe<0 then 'Faltante' else 'Sobrante' end as tipo,
					DT.importe as importe, DT.tipo as tip
				FROM	
					comb_diferencia_trabajador DT
					INNER JOIN pla_ta_trabajadores T ON T.ch_codigo_trabajador = DT.ch_codigo_trabajador
					(SELECT lado FROM pos_cmblados where prod1!='GL') aa
				WHERE		
					DT.importe!=0.00
					and dia between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
					and DT.tipo = '" . pg_escape_string($tipo) . "'
				GROUP BY
					nombre,
					tipo,
					importe,
					tip ;";
		}

		echo '
			SOBRANTES Y FALTANTES:'.$sql;
		if ($sqlca->query($sql) < 0) return false;
		$result = Array();
		$total = $sqlca->numrows()-1;

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    	$a = $sqlca->fetchRow();
		    	$ch_sucursal = pg_escape_string($estaciones);
		    	$propio = ($propiedad[$ch_sucursal]=='S'?"ESTACION":"OTROS");

		    	@$result['propiedades'][$propio]['almacenes'][$i][0] = $a[0];
		    	@$result['propiedades'][$propio]['almacenes'][$i][1] = $a[1];
		    	@$result['propiedades'][$propio]['almacenes'][$i][2] = $a[2];
		    	@$result['propiedades'][$propio]['almacenes'][$total]['total'] += $a[2];

		    	if ($a[1] == 'Sobrante'){
		    		@$result['propiedades'][$propio]['almacenes'][$total]['total_Sob'] += $a[2];
		    	} else {
		    		@$result['propiedades'][$propio]['almacenes'][$total]['total_Fal'] += $a[2];
		    	}
		}

		return $result;	
    	}

	function obtieneFaltantesGLP($desde, $hasta, $estaciones) {
		global $sqlca;

		$propiedad = '';
		$almacenes = SustentoVentasModel::obtieneListaEstaciones();

		$sql = "SELECT
				CD.ch_codigo_trabajador || '  ' || ch_nombre1 || ' ' || ch_apellido_paterno || ' ' || ch_apellido_materno as nombre,
				case when CD.importe<0 then 'Faltante' else 'Sobrante' end as tipo,
				CD.importe as importe,
				CD.tipo as tip
			FROM
				comb_diferencia_trabajador CD
			INNER JOIN
				pla_ta_trabajadores T ON T.ch_codigo_trabajador = CD.ch_codigo_trabajador
			inner join
				(SELECT ch_codigo_trabajador,ch_posturno,dt_dia FROM pos_historia_ladosxtrabajador T 
			inner join
				(SELECT lado FROM pos_cmblados where prod1='GL') L on L.lado= T.ch_lado
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
						CD.ch_codigo_trabajador=LT.ch_codigo_trabajador and CD.turno=LT.ch_posturno and CD.dia=LT.dt_dia
			WHERE
				CD.importe != 0.00		
				and dia between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
				and (CD.tipo is null or CD.tipo = 'C');";

		echo '
			SOBRANTES Y FALTANTES: '.$sql;
		if ($sqlca->query($sql) < 0) return false;
		$result = Array();
		$total = $sqlca->numrows()-1;

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
	    	$a = $sqlca->fetchRow();
	    	$ch_sucursal = pg_escape_string($estaciones);
	    	$propio = ($propiedad[$ch_sucursal]=='S'?"ESTACION":"OTROS");

	    	@$result['propiedades'][$propio]['almacenes'][$i][0] = $a[0];
	    	@$result['propiedades'][$propio]['almacenes'][$i][1] = $a[1];
	    	@$result['propiedades'][$propio]['almacenes'][$i][2] = $a[2];
	    	@$result['propiedades'][$propio]['almacenes'][$total]['total'] += $a[2];

	    	if ($a[1] == 'Sobrante'){
	    		@$result['propiedades'][$propio]['almacenes'][$total]['total_Sob'] += $a[2];
	    	} else {
	    		@$result['propiedades'][$propio]['almacenes'][$total]['total_Fal'] += $a[2];
	    	}
		}

		return $result;	
    }

    function obtieneAfericion($desde, $hasta, $estaciones, $tipo) {
		global $sqlca;

		$propiedad = '';
		$almacenes = SustentoVentasModel::obtieneListaEstaciones();

		$cond_codigo_combustible = '';
		if($tipo == 'C')
			$cond_codigo_combustible = "AND af.codigo != '11620307'";

		$sql = "
		SELECT
			'Prueba de sistema' AS prueba,
			SUM(af.importe) AS afericion
		FROM
			pos_ta_afericiones AS af
		WHERE
			af.es = '" . pg_escape_string($estaciones) . "'
			AND af.dia BETWEEN to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') AND to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
			" . $cond_codigo_combustible . "
		";

		echo 'SUSTENTO:'.$sql;

		if ($sqlca->query($sql) < 0)
			return false;

		$result = Array();
		$total  = $sqlca->numrows()-1;

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
	    	$a = $sqlca->fetchRow();
	    	$ch_sucursal = pg_escape_string($estaciones);
	    	$propio = ($propiedad[$ch_sucursal]=='S'?"ESTACION":"OTROS");

    		@$result['propiedades'][$propio]['almacenes'][$i][0] = $a[0];
    		@$result['propiedades'][$propio]['almacenes'][$i][1] = $a[1];
    		@$result['propiedades'][$propio]['almacenes'][$total]['total'] += $a[1];
		}

		return $result;	
    }

	function obtieneAfericionMarket($desde, $hasta, $estaciones, $tipo) {
		global $sqlca;

		$propiedad = '';
		$almacenes = SustentoVentasModel::obtieneListaEstaciones();

		$sql = "SELECT
						'Prueba de sistema' AS prueba,
						SUM (
							CASE
							WHEN nu_ventagalon > 0 THEN
								(nu_ventavalor / nu_ventagalon) * nu_afericionveces_x_5 * 5
							ELSE
								0
							END
						) AS afericiones
					FROM
						comb_ta_contometros
					WHERE
						ch_sucursal = '" . pg_escape_string($estaciones) . "'
						AND dt_fechaparte BETWEEN to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY')
						AND to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY') 
				";

		echo 'SUSTENTO:'.$sql;

		if ($sqlca->query($sql) < 0) 
			return false;

		$result = Array();
		$total  = $sqlca->numrows()-1;

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    	$a = $sqlca->fetchRow();
		    	$ch_sucursal = pg_escape_string($estaciones);
		    	$propio = ($propiedad[$ch_sucursal]=='S'?"ESTACION":"OTROS");

	    		@$result['propiedades'][$propio]['almacenes'][$i][0] = $a[0];
	    		@$result['propiedades'][$propio]['almacenes'][$i][1] = $a[1];
	    		@$result['propiedades'][$propio]['almacenes'][$total]['total'] += $a[1];
		}

		return $result;	
    	}

	function obtieneAfericionGLP($desde, $hasta, $estaciones, $tipo = "") {
		global $sqlca;

		$propiedad = '';
		$almacenes = SustentoVentasModel::obtieneListaEstaciones();

		$sql = "SELECT 	
				'Prueba de sistema' as prueba,
				sum(af.importe) as afericion
			FROM 
				pos_ta_afericiones af
			WHERE
				af.codigo='11620307'
				AND af.es='" . pg_escape_string($estaciones) . "' 
				AND af.dia between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY');";

		echo 'SUSTENTO:'.$sql;

		if ($sqlca->query($sql) < 0) 
			return false;

		$result = Array();
		$total  = $sqlca->numrows()-1;

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    	$a = $sqlca->fetchRow();
		    	$ch_sucursal = pg_escape_string($estaciones);
		    	$propio = ($propiedad[$ch_sucursal]=='S'?"ESTACION":"OTROS");

	    		@$result['propiedades'][$propio]['almacenes'][$i][0] = $a[0];
	    		@$result['propiedades'][$propio]['almacenes'][$i][1] = $a[1];
	    		@$result['propiedades'][$propio]['almacenes'][$total]['total'] += $a[1];
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
		    		ch_almacen ; ";

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
