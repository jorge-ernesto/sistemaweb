<?php

class LiquidacionGastosModel extends Model {
	function obtenerAlmacenes() {
		global $sqlca;

		$sql =	"	SELECT
					ch_almacen,
					ch_almacen || ' - ' || ch_nombre_almacen
				FROM
					inv_ta_almacenes
				WHERE
					ch_clase_almacen='1';";

		if ($sqlca->query($sql) < 0)
			return false;

		$result = array();

		for ($i=0;$i<$sqlca->numrows();$i++) {
			$a = $sqlca->fetchRow();
			$result[$a[0]] = $a[1];
		}

		return $result;
	}

	function obtenerTipoGasto() {
		global $sqlca;

		$sql =	"	SELECT
					id_tipo_gasto,
					nombre
				FROM
					comb_tipo_gasto;";

		if ($sqlca->query($sql) < 0)
			return false;

		$result = array();

		for ($i=0;$i<$sqlca->numrows();$i++) {
			$a = $sqlca->fetchRow();
			$result[$a[0]] = $a[1];
		}

		return $result;
	}

	function obtenerDiasIngreso() {
		global $sqlca;

		$sql =	"	SELECT
					util_fn_fechaactual_aprosys(),
					to_char(util_fn_fechaactual_aprosys(),'DD/MM/YYYY'),
					util_fn_fechaactual_aprosys()-1,
					to_char(util_fn_fechaactual_aprosys()-1,'DD/MM/YYYY'),
					util_fn_fechaactual_aprosys()-2,
					to_char(util_fn_fechaactual_aprosys()-2,'DD/MM/YYYY');";

		if ($sqlca->query($sql)<0)
			return Array();

		$a = $sqlca->fetchRow();

		return	Array(
				$a[0]	=>	$a[1],
				$a[2]	=>	$a[3],
				$a[4]	=>	$a[5]
			);
	}

	function buscar($almacen, $dia,$dia2) {
		global $sqlca;

		$sql =	"	SELECT
					id_liquidacion_gastos,
					es,
					id_tipo_gasto,
					to_char(fecha,'DD/MM/YYYY'),
					descripcion,
					importe,
					usuario
				FROM
					comb_liquidacion_gastos
				WHERE
					1 = 1
					" . (($almacen=="")?"":"AND es='$almacen'") . "
					" . (($dia=="" || $dia2=="")?"":"AND fecha BETWEEN to_date('$dia','DD/MM/YYYY') AND to_date('$dia2','DD/MM/YYYY')") . "
				ORDER BY
					1;";echo $sql;

		if ($sqlca->query($sql) < 0)
			return false;

		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();

			$resultado[$i]['id'] = $a[0];
			$resultado[$i]['es'] = $a[1];
			$resultado[$i]['tipo'] = $a[2];
			$resultado[$i]['fecha'] = $a[3];
			$resultado[$i]['descripcion'] = $a[4];
			$resultado[$i]['importe'] = $a[5];
			$resultado[$i]['usuario'] = $a[6];
		}

		return $resultado;
	}

   function Eliminar($id) {
		global $sqlca;

		$sql =	"
                    DELETE FROM  comb_liquidacion_gastos 
                    WHERE id_liquidacion_gastos='$id';";
                                        

		if ($sqlca->query($sql)<0){
			return FALSE;}
                        

		return TRUE;
	}     
function Actualizar($almacen,$tipo_gasto,$fecha,$descripcion,$importe,$usuario,$id) {
		global $sqlca;

		$sql =	"
                    UPDATE comb_liquidacion_gastos 
                    SET es='$almacen',
                    id_tipo_gasto='$tipo_gasto',
                    fecha='$fecha',
                    descripcion='$descripcion',
                    importe='$importe' ,
                    usuario='$usuario'
                    WHERE id_liquidacion_gastos='$id';";
                                        

		if ($sqlca->query($sql)<0){
			return FALSE;}
                        

		return TRUE;
	}

function BuscargastoxLiquidacion($id) {
		global $sqlca;

		$sql =	"SELECT * FROM comb_liquidacion_gastos WHERE id_liquidacion_gastos='$id'";
                                        

		if ($sqlca->query($sql) < 0)
			return false;

		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();

			$resultado[$i]['id'] = $a[0];
			$resultado[$i]['es'] = $a[1];
			$resultado[$i]['tipo'] = $a[2];
			$resultado[$i]['fecha'] = $a[3];
			$resultado[$i]['descripcion'] = $a[4];
			$resultado[$i]['importe'] = $a[5];
			$resultado[$i]['usuario'] = $a[6];
		}
                

		return $resultado;

		
	}
        
	function agregar($almacen,$tipo_gasto,$fecha,$descripcion,$importe,$usuario) {
		global $sqlca;

		$sql =	"	INSERT INTO
					comb_liquidacion_gastos
				(
					es,
					id_tipo_gasto,
					fecha,
					descripcion,
					importe,
					usuario
				) VALUES (
					'$almacen',
					{$tipo_gasto},
					'$fecha',
					'" . addslashes($descripcion) . "',
					$importe,
					'" . addslashes($usuario) . "'
				);";
                                        echo $sql;

		if ($sqlca->query($sql)<0)
			return FALSE;

		return TRUE;
	}


}
