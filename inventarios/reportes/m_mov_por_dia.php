<?php

date_default_timezone_set('UTC');

class ModelMovPorDias extends Model {

	function GetAlmacen($nualmacen) {
		global $sqlca;

		$cond = '';
		if ($nualmacen != 'T') {
			$cond = "AND ch_almacen = '".$nualmacen."'";
		}

		try {
			$sql = "
			SELECT
				ch_almacen AS nualmacen,
				TRIM (ch_almacen) || ' - ' || TRIM (ch_nombre_almacen) AS noalmacen
			FROM
				inv_ta_almacenes
			WHERE
				ch_clase_almacen = '1' $cond
			ORDER BY
				ch_almacen;
			";

			if ($sqlca->query($sql) <= 0) {
				throw new Exception("Error no se encontro turnos en la fecha indicada");
			}

			while ($reg = $sqlca->fetchRow()) {
				$registro[] = $reg;
			}

			return $registro;

		} catch (Exception $e) {
			throw $e;
		}
	}

	function GetLinea() {
		global $sqlca;

		try {
			$sql = "
				SELECT
					tl.tab_elemento AS nucodlinea,
					tl.tab_elemento || ' - ' || tl.tab_descripcion AS nolinea
				FROM
					int_tabla_general as tl
				WHERE
					tl.tab_tabla = '20'
					AND (tl.tab_elemento != '000000' AND tl.tab_elemento != '')
				ORDER BY
					nolinea;
			";

			if ($sqlca->query($sql) <= 0) {
				$registro[] = array("nucodlinea"=>"","nolinea"=>"No hay Lineas");
			}

			while ($reg = $sqlca->fetchRow()) {
				$registro[] = $reg;
			}

			return $registro;

		} catch (Exception $e) {
			throw $e;
		}

	}

	function search($data) {
		global $sqlca;
		$registro = array();

		//var_dump($data);
		$nualmacen 	= $data['nualmacen'];
		$fecha_inicio 	= $data['fecha_inicio'];
		$fecha_final 	= $data['fecha_final'];

		$fecha_inicio_div = explode("/", $fecha_inicio);
		$fechai = $fecha_inicio_div[2]."-".$fecha_inicio_div[1]."-".$fecha_inicio_div[0];

		$fecha_final_div = explode("/", $fecha_final);
		$fechaf = $fecha_final_div[2]."-".$fecha_final_div[1]."-".$fecha_final_div[0];

		$d = new DateTime( $fechai );
		$d->modify( 'first day of last month' );
		$data = $d->format( 'Y - m' );

		$periodo = substr($data,0,4);
		$mes = substr($data,7,3);

		$fechamov = $fecha_inicio_div[2]."-".$fecha_inicio_div[1];

		//toma el saldo pendiente del ultimo mes
		$d = new DateTime( $fechai );
		$d->modify( 'first day of last month' );
		$data = $d->format( 'Y - m' );

		$periodo = substr($data,0,4);
		$mes = substr($data,7,3);

		//calcula los movimientos desde el inicio del mes hasta la fecha seleccionada
		$fechamovini = $fecha_inicio_div[2]."-".$fecha_inicio_div[1];
		$d = new DateTime( $fechai );
		$d->modify( '-1 second' );
		$fechamovfin = $d->format( 'Y-m-d H:i:s' );

		$sql = "
				SELECT
				stocki.art_codigo,
				stocki.art_descripcion,
				stocki.stock,
				mov.movimiento,
				entradas.entrada,
				salidas.salida,
				ajustes.ajuste
				FROM 
				(select 
				art.art_codigo,
				art.art_descripcion,
				sal.stk_stock" . $mes . " as stock 
				from int_articulos art
				left join inv_saldoalma sal on art.art_codigo=sal.art_codigo
				and stk_almacen='" . $nualmacen . "'
				and stk_periodo='" . $periodo . "') AS stocki
				LEFT JOIN
				(select
				art_codigo, sum(mov_cantidad) as movimiento
				from (select art_codigo, case when mov_naturaleza='1' or mov_naturaleza='2' then mov_cantidad else mov_cantidad*-1 end as mov_cantidad
				from inv_movialma where mov_fecha between '" . $fechamovini . "-01 00:00:00' AND '" . $fechamovfin . "'  and mov_almacen='" . $nualmacen . "' ) as koko
				group by art_codigo
				order by art_codigo) AS mov ON mov.art_codigo = stocki.art_codigo 
				LEFT JOIN
				(select 
				art_codigo, sum(mov_cantidad) as entrada
				from (select art_codigo,mov_cantidad
				from inv_movialma where mov_fecha between '" . $fechai . " 00:00:00' AND '" . $fechaf . " 23:59:59'
				and tran_codigo IN ('01','21') and mov_almacen='" . $nualmacen . "') as koko
				group by art_codigo
				order by art_codigo) AS entradas ON entradas.art_codigo = stocki.art_codigo
				LEFT JOIN
				(select 
				art_codigo, sum(mov_cantidad) as salida
				from (select art_codigo,mov_cantidad
				from inv_movialma where mov_fecha between '" . $fechai . " 00:00:00' AND '" . $fechaf . " 23:59:59'
				and tran_codigo IN ('25','45') and mov_almacen='" . $nualmacen . "') as koko
				group by art_codigo
				order by art_codigo) AS salidas ON salidas.art_codigo = stocki.art_codigo
				LEFT JOIN 
				(select 
				art_codigo, sum(mov_cantidad) as ajuste
				from (select art_codigo, case when mov_naturaleza='1' or mov_naturaleza='2' then mov_cantidad else mov_cantidad*-1 end as mov_cantidad
				from inv_movialma where mov_fecha between '" . $fechai . " 00:00:00' AND '" . $fechaf . " 23:59:59'
				and (tran_codigo!='45' and tran_codigo!='01' and tran_codigo!='25' and tran_codigo!='21')  and mov_almacen='" . $nualmacen . "') as koko
				group by art_codigo 
				order by art_codigo) AS ajustes ON ajustes.art_codigo = stocki.art_codigo
				WHERE NOT (entradas.entrada is null and salidas.salida is null and ajustes.ajuste is null)
				ORDER BY 1;
		";

		//echo $sql;
		//echo $fecha;

		if ($sqlca->query($sql) <= 0) {
			throw new Exception("No se encontro ningun registro");
		}

		while ($reg = $sqlca->fetchRow()) {
			$registro[] = $reg;
		}

		//var_dump($registro);
		return $registro;
	}

	function getDataPrint() {
		global $sqlca;

		$resutl = array();
		$result['error'] = false;

		$sql  =	"SELECT
			trim(pc_samba) as pc_samba,
			trim(prn_samba) as prn_samba,
			trim(ip) as ip
		FROM
			pos_cfg
		WHERE
			impcierre = true and tipo = 'M'
		ORDER BY
			tipo DESC,
			pos ASC;";

		$rs = $sqlca->query($sql);
		$result['message'] = 'Enviando impresión de wincha.';

		if ($rs < 0) {
			//echo "Error consultando POS\n";
			$result['message'] = 'Error consultando POS';
			$result['error'] = true;
			//return false;
		}

		if ($sqlca->numrows() < 1) {
			$result['message'] = 'No se encontraron datos de configuración';
			$result['error'] = true;
			//return true;
		}

		$result['data'] = $sqlca->fetchRow();
		return $result;
	}

}