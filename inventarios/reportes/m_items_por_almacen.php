<?php
class ModelItemsPorAlmacen extends Model {

	function GetAlmacen($nualmacen) {
		global $sqlca;

		$cond = '';
		if ($nualmacen != 'T')
			$cond = "AND ch_almacen = '" . $nualmacen . "'";

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
 tab_elemento AS nucodlinea,
 tab_elemento || ' - ' || tab_descripcion AS nolinea
FROM
 int_tabla_general
WHERE
 tab_tabla = '20'
 AND (tab_elemento != '000000' AND tab_elemento != '')
ORDER BY
 tab_descripcion;
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

		$nualmacen 	= $data['nualmacen'];
		$nucodlinea = $data['nucodlinea'];
		$nuyear 	= $data['nuyear'];
		$numonth 	= $data['numonth'];
		$utilidad 	= $data['utilidad'];
		$simple 	= $data['simple'];
		$notipo 	= $data['notipo'];
		$p_stock 	= $data['p_stock'];
		$c_stock 	= $data['c_stock'];
		$n_stock 	= $data['n_stock'];
		$fecha 		= $data['fecha_inicio'];

		$condalmacen 	= NULL;
		$condlinea 	= '';
		$condtipo 	= NULL;

		$fechadiv = explode("/", $fecha);
		$fechabq = $fechadiv[2]."-".$fechadiv[1]."-".$fechadiv[0];

		$anod = $fechadiv[2];
		$mesd = $fechadiv[1];

		$linea = '';
		$sqladd = '';

		//SOLO SI TIENEN STOCK POSITIVO  OSEA STOCK > 0
		if($p_stock == "P") 
			$sqladd = " and (s.stk_stock".$numonth." > '0')";  
		//PARA MOSTRAR STOCK EN CERO
		if($c_stock == "C") 
			$sqladd= "and (s.stk_stock".$numonth."= '0')"; 
		//PARA MOSTRAR SOLO NEGATIVOS
		if($n_stock == "N") 
			$sqladd= "and (s.stk_stock".$numonth." < '0')"; 

		if($p_stock == "P" && $c_stock == "C") 
			$sqladd = "and (s.stk_stock".$numonth." >= '0' ) ";  
		if($p_stock == "P" && $n_stock == "N") 
			$sqladd = "and (s.stk_stock".$numonth." > '0' or s.stk_stock".$numonth." < '0')" ; 
		if($c_stock == "C" && $n_stock == "N") 
			$sqladd = "and (s.stk_stock".$numonth." <=' 0') "; 
		if($p_stock == "P" && $c_stock == "C" && $n_stock == "N") 
			$sqladd = "and (s.stk_stock".$numonth." >= '0' or s.stk_stock".$numonth."<= '0') " ; 

		if ($nualmacen != 'T')
			$sqladd .= " and (s.stk_almacen='".$nualmacen."')"; 
		
		if ($nucodlinea != 'T')
			$linea = $nucodlinea;

		if ($notipo == 'S')
			$condlinea .= "AND saldo.stk_stock".$numonth." > 0";

		$query = "SELECT par_valor FROM int_parametros WHERE par_nombre='lista precio';";

		if ($sqlca->query($query) < 0)
			return false;

		$a = $sqlca->fetchRow();
		$listaprecio = trim($a[0]);

		$postrans = "pos_trans" . $nuyear . $numonth;

		if ($utilidad === 'S') {
			$sql = "
SELECT
 s.stk_almacen,
 a.ch_nombre_almacen,
 ar.art_linea,
 l.tab_descripcion AS DESCLINEA,
 ar.art_codigo,
 TRIM(ar.art_descripcion) AS descripcion,
 ar.art_unidad AS unidad,
 ROUND(s.stk_stock" . $numonth . ", 4) AS nucantidad,
 ROUND(s.stk_costo" . $numonth . ", 6) AS nucosto,
 ROUND((s.stk_stock" . $numonth . " * s.stk_costo" . $numonth . "), 4) AS subtot,
 CASE WHEN s.art_codigo IN('11620301', '11620302', '11620303', '11620304', '11620305', '11620306', '11620307', '11620308') THEN
 	ROUND(com.nu_preciocombustible, 2)
 ELSE
 	ROUND(p.pre_precio_act1, 2)
 END AS precio_venta,
 CASE WHEN s.art_codigo IN('11620301', '11620302', '11620303', '11620304', '11620305', '11620306', '11620307', '11620308') THEN
  ROUND(com.nu_preciocombustible * s.stk_stock" . $numonth . ", 4) - ROUND(ROUND(com.nu_preciocombustible * s.stk_stock" . $numonth . ", 4)/ (1 + util_fn_igv() / 100), 2)
 ELSE
  ROUND((p.pre_precio_act1 * s.stk_stock" . $numonth . ") - (p.pre_precio_act1 * s.stk_stock" . $numonth . ") / (1 + util_fn_igv() / 100), 2)
 END AS igv,
 CASE WHEN s.art_codigo IN('11620301', '11620302', '11620303', '11620304', '11620305', '11620306', '11620307', '11620308') THEN
  ROUND(com.nu_preciocombustible * s.stk_stock" . $numonth . ", 2)
 ELSE
  ROUND(p.pre_precio_act1 * s.stk_stock" . $numonth . ", 2)
 END AS total,
 ROUND((1 + util_fn_igv() / 100), 2) AS ss_impuesto
FROM
 inv_ta_almacenes AS a
 JOIN inv_saldoalma AS s
  ON(s.stk_almacen = a.ch_almacen)
 JOIN int_articulos AS ar
  USING(art_codigo)
 LEFT JOIN fac_lista_precios AS p
  ON (p.art_codigo = ar.art_codigo)
 LEFT JOIN comb_ta_combustibles AS com
  ON (com.ch_codigocombustible = ar.art_codigo)
 LEFT JOIN int_tabla_general AS l
  ON (l.tab_tabla='20' AND (ar.art_linea = l.tab_elemento OR ar.art_linea = substr(l.tab_elemento,5,2)))
WHERE
 (art_linea like '%" . $linea . "') 
 AND (a.ch_clase_almacen='1') 
 AND (trim(s.stk_almacen)=trim(a.ch_almacen)) 
 AND (s.stk_periodo='" . $nuyear . "') 
 AND (s.art_codigo=ar.art_codigo) 
 " . $sqladd . "
 AND ar.art_plutipo!='2' 
 AND ar.art_plutipo!='3' 
 AND p.pre_lista_precio='" . $listaprecio . "'
ORDER BY
 s.stk_almacen,
 ar.art_linea,
 s.art_codigo;
			";
		} else {
			$sql = "
SELECT
 s.stk_almacen,
 a.ch_nombre_almacen,
 ar.art_linea,
 l.tab_descripcion AS DESCLINEA,
 ar.art_codigo,
 TRIM(ar.art_descripcion) AS descripcion,
 ar.art_unidad AS unidad,
 ROUND(s.stk_stock" . $numonth . ", 4) AS nucantidad,
 ROUND(s.stk_costo" . $numonth . ", 6) AS nucosto,
 ROUND((s.stk_stock" . $numonth . " * s.stk_costo" . $numonth . "), 4) AS subtot
FROM
 inv_ta_almacenes AS a
 JOIN inv_saldoalma AS s
  ON(s.stk_almacen = a.ch_almacen)
 JOIN int_articulos AS ar
  USING(art_codigo)
 LEFT JOIN int_tabla_general l ON (l.tab_tabla='20' AND (ar.art_linea = l.tab_elemento OR ar.art_linea = substr(l.tab_elemento,5,2)))
WHERE
 (art_linea like '%" . $linea . "') 
 AND (a.ch_clase_almacen='1') 
 AND (trim(s.stk_almacen)=trim(a.ch_almacen)) 
 AND (s.stk_periodo='" . $nuyear . "')
 AND (s.art_codigo=ar.art_codigo)
 " . $sqladd . "
 AND ar.art_plutipo!='2'
 AND ar.art_plutipo!='3'
ORDER BY
 s.stk_almacen,
 ar.art_linea,
 s.art_codigo;
			";
		}
/*
		echo "<pre>";
		echo $sql;
		echo "</pre>";
*/
		if ($sqlca->query($sql) <= 0) {
			throw new Exception("No se encontro ningun registro");
		}

		while ($reg = $sqlca->fetchRow()) {
			$registro[] = $reg;
		}

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