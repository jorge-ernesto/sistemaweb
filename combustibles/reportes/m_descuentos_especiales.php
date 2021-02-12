<?php
class Descuentos_Especiales_Model extends Model {

    function ObtenerEstaciones() {
		global $sqlca;
	
		try {
			$sql = "
SELECT
 ch_almacen AS almacen,
 TRIM(ch_nombre_almacen) AS nombre
FROM
 inv_ta_almacenes
WHERE
 ch_clase_almacen='1'
ORDER BY
 ch_almacen;
			";

			if($sqlca->query($sql) <= 0){
				throw new Exception("Error no se encontro turnos en la fecha indicada");
			}

			while($reg = $sqlca->fetchRow()){
				$registro[] = $reg;
			}

			return $registro;

		}catch(Exception $e){
			throw $e;
		}
    }

	function ObtenerFechaDTurno($fecha) {
		global $sqlca;

		$y = substr($fecha,6,4);
		$m = substr($fecha,3,2);
		$d = substr($fecha,0,2);

		$fecha = $y."-".$m."-".$d;

		try {

			$registro = array();
			$cerrado = "";
			//$cerrado = "Turno Cerrado";

			$query = "
			SELECT
				ch_poscd				
			FROM
			    pos_aprosys
			WHERE
			    da_fecha = '" . $fecha . "';
			";

			if ($sqlca->query($query) < 0)
			    return false;

			$a = $sqlca->fetchRow();
			$ch_poscd = $a['ch_poscd'];

			if ($ch_poscd == 'S') {
				$cerrado = '- 1';
			}

			$sql = "
			SELECT
				ch_posturno::INTEGER " . $cerrado . " AS turno
			FROM
			   	pos_aprosys
			WHERE
			   	da_fecha = '" . $fecha . "';
			";

			if($sqlca->query($sql) < 0){
				throw new Exception("Error get ObtenerFechaDTurno");
			} else if ($sqlca->query($sql) == 0) {
				throw new Exception("WARNING_1");//No hay registros
			}

			while($reg = $sqlca->fetchRow()){
			        $registro[] = $reg;
			}

			return $registro;

		}catch(Exception $e){
			throw $e;
		}
	}
//  function ObtenerCajas($almacen) {
	function ObtenerCajas($almacen, $dEmision) {
		global $sqlca;


		$anio	= substr($dEmision,6,4);
		$mes	= substr($dEmision,3,2);
		$dia	= substr($dEmision,0,2);
		$dia = $anio . "-" . $mes . "-" . $dia;


		try {

			$registro = array();

$sql="
SELECT
name
FROM
s_pos
WHERE
warehouse = '" . $almacen . "'
ORDER BY
name";
if($sqlca->query($sql) <= 0){

	$sql = "
	SELECT
	ch_posz_pos
	FROM
	pos_z_cierres
	WHERE
	ch_sucursal = '" . $almacen . "'
	and dt_posz_fecha_sistema = '" . $dia . "'
	ORDER BY
	ch_posz_pos";
	
				if($sqlca->query($sql) <= 0){
					throw new Exception("Error get ObtenerCajas ");
				}
}

			while($reg = $sqlca->fetchRow()){
			        $registro[] = $reg;
			}

			return $registro;

		}catch(Exception $e){
			throw $e;
		}
	}

	function SearchPump($almacen, $dia) {
		global $sqlca;

		$anio	= substr($dia,6,4);
		$mes	= substr($dia,3,2);
		$dia	= substr($dia,0,2);

		$query = "
		SELECT
			da_fecha,ch_posturno
		FROM
	       	pos_aprosys
	    WHERE
	       	ch_poscd = 'A';
		";

		if ($sqlca->query($query) < 0)
			return false;

		$row			= $sqlca->fetchRow();
		$dia_actual 	= $row['da_fecha'];
		$turno_actual 	= $row['ch_posturno'];

		$dia = $anio . "-" . $mes . "-" . $dia;

		if ($dia == $dia_actual)
			$postrans = "pos_transtmp";
		else
			$postrans = pg_escape_string("pos_trans" . $anio . $mes);

		try {

		$sql = "SELECT 1 FROM information_schema.tables WHERE table_schema = 'public' AND table_name = '" . $postrans . "'";

        if($sqlca->query($sql) === 1) {
			$sql = "
SELECT DISTINCT
 pump
FROM
 " . $postrans . "
WHERE
 dia='" . $dia . "'
 AND tipo='C'
ORDER BY
 pump
			";

			if ($sqlca->query($sql) <= 0)
               	throw new Exception("Error get SearchPump");

			while($reg = $sqlca->fetchRow())
				$registro[] = $reg;

			return $registro;
		} else {
			$registro[] = 1;
			return $registro;
		}

		}catch(Exception $e){
			throw $e;
		}
	}

	function ObtenerTipoCambio($fecha, $tipomoneda){
		global $sqlca;

		$y = substr($fecha,6,4);
		$m = substr($fecha,3,2);
		$d = substr($fecha,0,2);

		$fecha = $y."-".$m."-".$d;

		try {

			$registro = array();

			$sql = "SELECT tca_venta_oficial FROM int_tipo_cambio WHERE tca_moneda = '02' AND tca_fecha = '$fecha'";

			if($sqlca->query($sql) <= 0){
				return 0.00;
			}

			while($reg = $sqlca->fetchRow()){
			        $registro[] = $reg;
			}

			return $registro;

		}catch(Exception $e){
			throw $e;
		}

	}

	function ObtenerTipoCambioCompra($fecha, $tipomoneda){
		global $sqlca;

		$y = substr($fecha,6,4);
		$m = substr($fecha,3,2);
		$d = substr($fecha,0,2);

		$fecha = $y."-".$m."-".$d;

		try {

			$registro = array();

			$sql = "SELECT tca_venta_oficial FROM int_tipo_cambio WHERE tca_moneda = '02' AND tca_fecha = '$fecha'";

			if($sqlca->query($sql) <= 0){
				return 0.00;
			}

			while($reg = $sqlca->fetchRow()){
			        $registro[] = $reg;
			}

			return $registro;

		}catch(Exception $e){
			throw $e;
		}

	}

	function Correlativo($dateact) {
		global $sqlca;

		$month 	= substr($dateact,6,4);
		$year	= substr($dateact,3,2);

		$dateact = $year."-".$month;

		$sql = "
			SELECT
				numerator
			FROM
				act_preseq
			WHERE
				dateact = '$dateact'
			ORDER BY
				numerator
			LIMIT 1;
		";

		if ($sqlca->query($sql) < 0)
			return false;

		$row = $sqlca->fetchRow();

		if ($row != NULL)
			$result = str_pad($row[0], 10, "0", STR_PAD_LEFT);
		else {

			$sql = "
				SELECT
					numerator
				FROM
					act_day
				WHERE
					dateact = '$dateact';
				";

			if ($sqlca->query($sql) < 0)
				return false;

			$result = null;
			$a = $sqlca->fetchRow();

			if ($a != NULL) {
				$result = str_pad($a[0] + 1, 10, "0", STR_PAD_LEFT);
			}else{
				$result = '0000000001';
			}
		}
	    return $result;
	}

	function ObtenerReporte($almacen, $fdesde, $fhasta, $tdesde, $thasta, $tv, $td, $tarjeta) {
		global $sqlca;

		$anomes = substr($fdesde,6,4).substr($fdesde,3,2);
		$anomes2 = substr($fhasta,6,4).substr($fhasta,3,2);

		if($anomes != $anomes2)
			echo "Error: El rango de mes debe ser el mismo";

		if($almacen != "T")
			$es = "t.es = '$almacen' AND";

		if($tv != "T")
			$tipo = "t.tipo = '$tv' AND";

		if($td != "T")
			$docu = "t.td = '$td' AND";

		if($tarjeta == "T")
			$tar = "";
		else
			$tar = "t.at = '$tarjeta' AND ";

		$anio	= substr($fdesde,6,4);
		$mes	= substr($fdesde,3,2);
		$dia	= substr($fdesde,0,2);
		$fdesde	= $anio."-".$mes."-"."$dia";

		$anio	= substr($fhasta,6,4);
		$mes	= substr($fhasta,3,2);
		$dia	= substr($fhasta,0,2);
		$fhasta	= $anio."-".$mes."-"."$dia";
		try {

			$registro = array();

			$sql = "
				SELECT
					(CASE
						WHEN t.td = 'B' THEN 'Boleta'
						WHEN t.td = 'N' THEN 'Nota Despacho'
						WHEN t.td = 'F' THEN 'Factura'
					END) AS td,
					t.dia AS dia,
					t.turno AS turno,
					t.fecha AS fecha,
					t.caja AS caja,
					t.trans AS trans,
					t.importe AS importe,
					a.art_descripcion AS descripcion,
					(CASE
						WHEN t.td = 'N' THEN cli.cli_codigo
						WHEN t.td IN('B', 'F') THEN t.ruc
					END) AS ruc,
					(CASE
						WHEN t.td = 'N' THEN cli.cli_razsocial
						WHEN t.td IN('B', 'F') THEN r.razsocial
					ELSE
						'Cliente Boleta'
					END) AS razsocial,
					(CASE
					    WHEN t.fpago = '1' THEN 'EFECTIVO' 
						WHEN t.td = 'N' THEN ''
					ELSE
					    'T. CREDITO'
					END) AS formapago,
					tar.tab_descripcion AS nombretrj
				FROM
					pos_trans".$anomes." AS t
					LEFT JOIN int_articulos AS a ON(a.art_codigo = t.codigo)
					LEFT JOIN ruc AS r ON(r.ruc = t.ruc)
					LEFT JOIN int_clientes AS cli ON(cli.cli_codigo = t.cuenta)
					LEFT JOIN int_tabla_general AS tar ON(TRIM(t.at) = SUBSTRING(tar.tab_elemento,6,6) AND tar.tab_tabla ='95' AND tar.tab_elemento != '000000')
				WHERE
					" . $es . "
					" . $tipo . "
					" . $docu . "
					" . $tar . "
			";

				$sql.= "
					t.dia::date||t.turno BETWEEN '" . $fdesde. "'||'" . $tdesde . "' AND '" . $fhasta . "'||'" . $thasta . "'
					AND t.grupo = 'D'
					AND td IN ('B','F','N')
				ORDER BY
					t.fecha			
				";

			/*echo "<pre>";
			print_r($sql);
			echo $tarjeta;
			echo "</pre>";*/

			if ($sqlca->query($sql) <= 0) {
               	throw new Exception("No hay ningun registro en este rango de fecha: ".$fdesde.' - '.$fhasta);
			}
       
			while ($reg = $sqlca->fetchRow()) {
				$registro[] = $reg;
			}
			return $registro;
		}catch(Exception $e){
			throw $e;
		}

	}

	function searchForKeyword($keyword) {
		global $sqlca;

		$sql = "SELECT trim(cli_razsocial) FROM int_clientes WHERE trim(cli_razsocial) LIKE '%$keyword%' ORDER BY cli_razsocial;";

		if ($sqlca->query($sql) < 0)
		    return false;

		$items = Array();
		$items = $sqlca->fetchAll();

		/*$result = array();
		foreach ($items as $key => $value) {
		    array_push($result, array("id" => $key, "name" => strip_tags($value)));
//		    array_push($result, array("id" => $key, "label" => $value, "name" => strip_tags($value)));
		}

		//json_encode is available in PHP 5.2 and above, or you can install a PECL module in earlier versions
		$data_codi = json_encode($result);*/

		return $items;

	}


	function obtieneLados(){
		global $sqlca;

                $sql = "SELECT
				name
			FROM
				f_pump
			ORDER BY
				f_pump_id
                       ";

		if ($sqlca->query($sql) < 0) 
			return false;
		$producto = Array();
	
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$lado[$i]['name'] .= $a['name'];
		}

		return $lado;

	}


	function ObtenerTarjetas() {
		global $sqlca;

			$sql = "
				SELECT
					substring(tab_elemento from 6 for 1) id,
					tab_descripcion as name
				FROM
					int_tabla_general
				WHERE
					tab_tabla='95'
					AND tab_elemento!='000000'
				ORDER BY
					tab_descripcion;
				";

            	if ($sqlca->query($sql) <= 0) {
				throw new Exception("Error al obtener Tarjetas");
				}

			while ($reg = $sqlca->fetchRow()) {
				$registro[] = $reg;
			}

			return $registro;
	}

	function SearchTransaction($data) {
		global $sqlca;

		$registro 	= array();

		$nualmacen	= $data["nualmacen"];
		$dtfecha 	= $data["dtfecha"];
		$nuticket 	= $data["nuticket"];
		$nucaja 	= $data["nucaja"];
		$nuproducto 	= $data["nuproducto"];
		$nuprecio 	= $data["nuprecio"];

		$postrans = "pos_trans".substr($dtfecha,6,4).substr($dtfecha,3,2);

		$anio	= substr($dtfecha,6,4);
		$mes	= substr($dtfecha,3,2);
		$dia	= substr($dtfecha,0,2);
		$dia	= $anio."-".$mes."-"."$dia";

		try {

			$sql = "
				SELECT
					*
				FROM
					$postrans
				WHERE
					es	 	= '".$nualmacen."'
					AND dia		= '".$dia."'
					AND trans 	= $nuticket
					AND caja 	= '".$nucaja."'
			";

			if ($sqlca->query($sql) <= 0) {
				return false;
               			throw new Exception("Error");
			}

			while ($reg = $sqlca->fetchRow()) {
				$registro[] = $reg;
			}

			return $registro;

		}catch(Exception $e){
			throw $e;
		}

	}

	function GuardarDescuento($data, $nuprecio){
		global $sqlca;

		$tm 		= $data[0]["tm"];
		$caja 		= $data[0]["caja"];
		$td 		= $data[0]["td"];
		$dia 		= $data[0]["dia"];
		$turno 		= $data[0]["turno"];
		$codigo 	= trim($data[0]["codigo"]);
		$cantidad 	= $data[0]["cantidad"];

		$nuprecio 	= -$nuprecio;
		$nuimporte	= $nuprecio * $cantidad;
		$nuigv		= ($nuimporte - ($nuimporte / 1.18));

		$fecha		= $data[0]["fecha"];
		$tipo		= $data[0]["tipo"];
		$pump		= $data[0]["pump"];
		$grupo		= $data[0]["grupo"];
		$es		= $data[0]["es"];
		$trans		= $data[0]["trans"];

		$ruc		= $data[0]["ruc"];
		$cuenta		= $data[0]["cuenta"];

		$postrans = "pos_trans".substr($dia,0,4).substr($dia,5,2);

		try {

			$sql = "
				INSERT INTO $postrans (
							tm,
							caja,
							td,
							dia,
							turno,
							codigo,
							cantidad,
							precio,
							igv,
							importe,
							fecha,
							tipo,
							pump,
							grupo,
							es,
							flg_replicacion,
							trans,
							cuenta,
							ruc
				)VALUES(
							'".$tm."',
							'".$caja."',
							'".$td."',
							'".$dia."',
							'".$turno."',
							'".$codigo."',
							0,
							$nuprecio,
							$nuigv,
							$nuimporte,
							'".$fecha."',
							'".$tipo."',
							'".$pump."',
							'D',
							'".$es."',
							'0',
							$trans,
							'".$cuenta."',
							'".$ruc."'
				);	
			";

			if ($sqlca->query($sql) < 0) {
				return false;
               			throw new Exception("Error al insertar descuento". $sql);
			} else {
	
				if($td == 'N'){

					$documento = $caja."-".$trans;

					$sql = "
						UPDATE
							val_ta_detalle
						SET
							nu_importe = (SELECT SUM(importe) FROM $postrans WHERE es = '".$es."' AND dia = '".$dia."' AND trans = $trans AND td = '".$td."' AND caja = '".$caja."' AND turno = '".$turno."')
						WHERE
							ch_documento		= '".$documento."'
							AND dt_fecha::DATE 	= '".$dia."';
					";

					if ($sqlca->query($sql) < 0) {
						return false;
			       			throw new Exception("Error al actualizar detalle". $sql);
					} else {

						$sql = "
							UPDATE
								val_ta_cabecera
							SET
								nu_importe = (SELECT SUM(importe) FROM $postrans WHERE es = '".$es."' AND dia = '".$dia."' AND trans = $trans AND td = '".$td."' AND caja = '".$caja."' AND turno = '".$turno."')
							WHERE
								ch_documento 		= '".$documento."'
								AND dt_fecha::DATE 	= '".$dia."';
						";

						if ($sqlca->query($sql) < 0) {
							return false;
				       			throw new Exception("Error al actualizar cabecera". $sql);
						}
					}

				}

				$sql = "DELETE FROM comb_ta_contometros WHERE dt_fechaparte::DATE = '".$dia."' AND ch_usuario = 'AUTO';";

				if ($sqlca->query($sql) < 0) {
					return false;
		       			throw new Exception("Error al eliminar parte de venta". $sql);
				}

				$sql = "DELETE FROM inv_movialma WHERE mov_fecha::DATE = '".$dia."' AND tran_codigo IN('23','24','25');";

				if ($sqlca->query($sql) < 0) {
					return false;
		       			throw new Exception("Error al eliminar kardex". $sql);
				}

				$sql = "SELECT combex_fn_contometros_auto('".$dia."');";

				if ($sqlca->query($sql) < 0) {
					return false;
		       			throw new Exception("Error al importar parte de venta". $sql);
				}

			}

			return true;

		}catch(Exception $e){
			throw $e;
		}

	}

	function GetProductCode($data){
		global $sqlca;

		$registro 	= array();

		$nualmacen	= $data["nualmacen"];
		$dtfecha 	= $data["dtfecha"];
		$nuticket 	= $data["nuticket"];
		$nucaja 	= $data["nucaja"];

		$postrans = "pos_trans".substr($dtfecha,6,4).substr($dtfecha,3,2);

		$anio	= substr($dtfecha,6,4);
		$mes	= substr($dtfecha,3,2);
		$dia	= substr($dtfecha,0,2);
		$dia	= $anio."-".$mes."-"."$dia";

		try {

			$sql = "
				SELECT
					codigo AS codigo,
					(CASE
						WHEN pt.td = 'N' THEN cli.cli_razsocial
						WHEN pt.td = 'F' THEN ruc.razsocial
					ELSE
						'Cliente Boleta'
					END) AS nocliente,
					des.descuento AS nudescuento,
					grupo AS status
				FROM
					$postrans pt
					LEFT JOIN int_clientes cli ON (cli.cli_ruc = pt.cuenta)
					LEFT JOIN ruc ruc ON (ruc.ruc = pt.ruc)
					LEFT JOIN pos_descuento_ruc des ON (des.art_codigo = pt.codigo AND (CASE WHEN pt.td = 'N' THEN des.ruc = pt.cuenta ELSE des.ruc = pt.ruc END))
				WHERE
					es	 	= '".$nualmacen."'
					AND dia		= '".$dia."'
					AND trans 	= $nuticket
					AND caja 	= '".$nucaja."'
					AND td  	IN ('B','F','N')
				ORDER BY
					status DESC;
			";

			if ($sqlca->query($sql) <= 0) {
				return false;
               			throw new Exception("Error");
			}
       
			while ($reg = $sqlca->fetchRow()) {
				$registro[] = $reg;
			}

			return $registro;

		}catch(Exception $e){
			throw $e;
		}

	}

}

