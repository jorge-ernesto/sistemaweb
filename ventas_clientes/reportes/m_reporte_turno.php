<?php
class ReporteTurnoModel extends Model {
	function reporte_turno() {
		global $sqlca;
		$_POST["periodo"]	= (empty($_POST["periodo"])?date("Y"):$_POST["periodo"]);
		$_POST["mes"]		= (empty($_POST["mes"])?date("m"):$_POST["mes"]);
		$_POST["desde"]	= (empty($_POST["desde"])?date("d"):$_POST["desde"]);
		$_POST["hasta"]	= (empty($_POST["hasta"])?date("d"):$_POST["hasta"]);
		$_POST["ch_almacen"]	= (empty($_POST["ch_almacen"])?"":$_POST["ch_almacen"]);
		$td 	= "";
		$fpago = "";

		if(!empty($_POST["td"])) {
			$td = join(",", $_POST["td"]);   
			$td = "AND t.td IN (".$td.")";
		}

		if(!empty($_POST["fpago"])) {
			$fpago = "AND t.fpago IN (".$_POST["fpago"][0].")";
		}

		list($ch_almacen,$nombre_almacen) = explode("___", $_POST["ch_almacen"]);
		$sql="SELECT * FROM 
					(SELECT
						t.dia,
						sum(t.cantidad) as cantidad,
						sum(t.importe) as importe,
						t.turno,
						t.codigo,
						t.tipo,
					CASE
						WHEN t.codigo='11620301' THEN '84' 
						WHEN t.codigo='11620302' THEN '90' 
						WHEN t.codigo='11620303' THEN '97' 
						WHEN t.codigo='11620304' THEN 'DB5 UV' 
						WHEN t.codigo='11620305' THEN '95' 
						WHEN t.codigo='11620306' THEN 'DB5 S50'
						WHEN t.codigo='11620307' THEN 'GLP' 
						END AS codigo_gasolina           
					FROM 
						pos_trans".$_POST["periodo"].$_POST["mes"]." t
						JOIN int_articulos art ON (art.art_codigo = t.codigo)
					WHERE
						date_part('day', t.dia) BETWEEN ".$_POST["desde"]." AND ".$_POST["hasta"]."
						AND t.tipo = 'C'
						AND t.es = '".$ch_almacen."'
						".$td."
						".$fpago."
					GROUP BY
						t.tipo,
						t.dia,
						t.turno,
						t.codigo
					ORDER BY
						t.dia,
						t.turno,
						t.codigo,
						t.tipo
					) AS C

				UNION 

					(SELECT
						t.dia,
						sum(t.cantidad) as cantidad,
						sum(t.importe) as importe,
						t.turno,
						''::text tipo,
						t.tipo,
						('M') codigo_gasolina
					FROM
						pos_trans".$_POST["periodo"].$_POST["mes"]." t
						JOIN int_articulos art ON (art.art_codigo = t.codigo)
					WHERE
						date_part('day', t.dia) BETWEEN ".$_POST["desde"]." AND ".$_POST["hasta"]."
						AND t.tipo = 'M'
						AND t.es = '".$ch_almacen."'
						".$td."
						".$fpago."
					GROUP BY
						t.tipo,
						t.dia,
						t.turno
					ORDER BY
						t.dia,
						t.turno,
						t.tipo)
					ORDER BY
						dia,
						turno,
						tipo,
						codigo;
				";
	
		// echo "<pre>";
		// echo $sql;
		// echo "</pre>";
       
		if ($sqlca->query($sql) < 0) 
			return null;

		while($reg = $sqlca->fetchRow()){
			$reg["dia"] = date("Y-m-d", strtotime($reg["dia"]));

			$registro[] = $reg;
		}

		return $registro; 
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
						ch_almacen
				";

		if ($sqlca->query($sql) < 0) 
			return false;	

		while($reg = $sqlca->fetchRow()) {
			$registro[] = $reg;
		}

		return $registro; 
	}
}
?>
