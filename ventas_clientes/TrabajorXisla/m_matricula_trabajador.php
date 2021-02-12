<?php

class matricula_personal_Model extends Model {

	function ObtenerSucursal() {
        	global $sqlca;

        	$lados = array();

        	try {

            		$sql = "SELECT par_valor FROM int_parametros WHERE par_nombre='codes' LIMIT 1;";

			if ($sqlca->query($sql) < 0) {
				throw new Exception("Error Parametro Almacen");
			}

            		$reg = $sqlca->fetchRow();

            		return $reg['par_valor'];

		} catch (Exception $e) {
			throw $e;
        	}

	}

	function ObtenerLados($sucursal) {
        global $sqlca;

        $lados = array();
    	try {
       		$sql = "
SELECT
 p.f_pump_id,
 p.name,
 fg.product
FROM
 f_pump AS p
 INNER JOIN f_grade AS fg ON (p.f_pump_id=fg.f_pump_id)
WHERE
 p.f_pump_id IN(
 SELECT
  f_pump_id
 FROM
  f_pump_pos
 WHERE
  s_pos_id IN(SELECT s_pos_id FROM s_pos where warehouse='" . $sucursal . "')
 )
ORDER BY
 p.f_pump_id;
			";
			if ($sqlca->query($sql) < 0) {
				throw new Exception("Error Capturando Datos de los lados.");
			}
			while ($reg = $sqlca->fetchRow()) {
				$lados[] = array("id" => $reg[0], "name" => $reg[1], "producto" => $reg[2]);
			}
			return $lados;
		} catch (Exception $e) {
			throw $e;
		}
	}

	function obtenerLadoxProducto($sIdLado){
		global $sqlca;

		$sql = "SELECT product FROM f_grade WHERE f_pump_id=" . (int)$sIdLado;
		$iStatusSQL = $sqlca->query($sql);

		if( (int)$iStatusSQL < 0 ){
			return array(
				'sMessageSQL' => $sqlca->get_error(),
				'sStatus' => 'danger',
				'sMessage' => 'Problemas al obtener lado por producto',
			);
		}

		if ( (int)$iStatusSQL==0 ){
			return array(
				'sStatus' => 'warning',
				'sMessage' => 'No se encontro el lado ' . $sIdLado,
			);
		}

		return array(
			'sStatus' => 'success',
			'sMessage' => 'Encontrado',
			'arrData' => $sqlca->fetchAll(),
		);
	}

	function Obtenersusursaldesdelado($ld) {
        global $sqlca;

        try {
			$sql = "
SELECT
 sp.warehouse
FROM
 f_pump p
 INNER JOIN f_pump_pos fp ON (fp.f_pump_id=p.f_pump_id)
 INNER JOIN s_pos sp ON (sp.s_pos_id=fp.s_pos_id)
WHERE
p.name='$ld';
			";

			if ($sqlca->query($sql) < 0) {
				throw new Exception("Error al obtener el almacen de lado seleccionado.");
			}

			$registrold = $sqlca->fetchRow();

			return $registrold[0];
		} catch (Exception $e) {
			throw $e;
		}
	}









	function VerTrabajdor_X_Asignado($dt_dia, $ch_posturno, $almacen) {
        	global $sqlca;

        	$lados = array();

		try {

			$sql = "
				SELECT
					ch_lado,
					ch_codigo_trabajador,
					ch_tipo
				FROM
					pos_historia_ladosxtrabajador
				WHERE
					dt_dia = '$dt_dia'
					AND ch_posturno = '$ch_posturno'
					AND ch_sucursal = '$almacen';
				";
/*
echo "<pre>";
print_r($sql);
echo "</pre>";
*/
			if ($sqlca->query($sql) < 0) {
				error_log("ERROR No se encontro trabajadores:" . $sql);
			}

			while ($reg = $sqlca->fetchRow()) {
				$lados[] = array("ch_lado" => trim($reg['ch_lado']), "ch_codigo_trabajador" => trim($reg['ch_codigo_trabajador']), "ch_tipo" => trim($reg['ch_tipo']));
			}

			return $lados;

		} catch (Exception $e) {
			throw $e;
		}

	}

   	function ObtenerTrabajadores() {
    	global $sqlca;

    	try {
       		$sql = "
SELECT
 ch_codigo_trabajador,
 ch_apellido_paterno ||' ' ||ch_apellido_materno ||' ' || ch_nombre1 ||''||  ch_nombre2 as nombre
FROM
 pla_ta_trabajadores
WHERE
 (ch_tipo_contrato IS NULL OR ch_tipo_contrato='0');
			";

			if ($sqlca->query($sql) < 0) {
				throw new Exception("Error Capturando datos Clientes.");
			}

			while ($reg = $sqlca->fetchRow()) {
				$trabajares[] = array("id_traba" => $reg[0], "nombre" => $reg[1]);
			}

			return $trabajares;
		} catch (Exception $e) {
			throw $e;
		}
	}

    function ObtenerPuntoMarket($sucursal) {
       	global $sqlca;

       	try {
			$sql = "
				SELECT
					s_pos_id,
					name,
					printerserial,
					s_postype_id
				FROM
					s_pos
				WHERE
					warehouse = '$sucursal'
				ORDER BY
					1;
				";

			if ($sqlca->query($sql) < 0) {
				throw new Exception("Error al Obtener datos LIQUIDACION .");
			}

			while ($reg = $sqlca->fetchRow()) {
				$registro[] = $reg;
			}

			return $registro;

		} catch (Exception $e) {
			throw $e;
		}

	}

	function Obtenersusursaldesdepv($pv) {
        	global $sqlca;

        	try {
			$sql = "
				SELECT
					warehouse
				FROM
					s_pos
				WHERE
					s_pos_id = '$pv'
				ORDER BY
					1;
				";

			if ($sqlca->query($sql) < 0) {
				throw new Exception("Error al Obtener el almacen de Punto de Venta.");
			}

			$registropv = $sqlca->fetchRow();

			return $registropv[0];

		} catch (Exception $e) {
			throw $e;
		}
	}



	function validaDia($dia, $turno, $almacen) {
		global $sqlca;

		$sql = " SELECT validar_consolidacion('$dia',$turno,'$almacen') ";

		$sqlca->query($sql);

		$estado = $sqlca->fetchRow();

		//echo "devolvio:\n";
		//var_dump($estado);

		if($estado[0] == 1){
			return 1;//Consolidado
		}else{
			return 0;//No consolidado
		}
	}

	function AsignarTrabajador($ch_sucursal, $dt_dia, $ch_posturno, $ch_lado, $ch_codigo_trabajador, $ch_tipo) {
	        global $sqlca;

		$flag = matricula_personal_Model::validaDia($dt_dia, $ch_posturno, $ch_sucursal);

		if($flag == 1){
			throw new Exception('Fecha ya consolidada!');
		}else{
			try {

				$estado = matricula_personal_Model::ActualizarTrabajador($ch_sucursal, $dt_dia, $ch_posturno, $ch_lado, $ch_codigo_trabajador, $ch_tipo);

				// ini_set('date.timezone','America/Lima');
				$fecha_actual = date("Y-m-d H:i");				

				if ($estado == 1) {

					$consulta = "INSERT INTO
							pos_historia_ladosxtrabajador(
											ch_sucursal,
											dt_dia,
											ch_posturno,
											ch_lado,
											ch_codigo_trabajador,
											ch_tipo,
											fecha_replicacion
							)VALUES(
											'$ch_sucursal',
											'$dt_dia',
											'$ch_posturno',
											'$ch_lado',
											'$ch_codigo_trabajador',
											'$ch_tipo',
											'$fecha_actual'
							);
					";

					$estado = $sqlca->query($consulta);

					if ($estado < 0) {
						throw new Exception('ERROR AL ASIGNAR TRABAJADOR');
					}
				}

			} catch (Exception $e) {
				throw $e;
			}
		}

	}

	function ActualizarTrabajador($ch_sucursal, $dt_dia, $ch_posturno, $ch_lado, $ch_codigo_trabajador, $ch_tipo) {
        	global $sqlca;

		$flag = matricula_personal_Model::validaDia($dt_dia, $ch_posturno, $ch_sucursal);

		if($flag == 1){
			throw new Exception('Fecha ya consolidada!');
		}else{

			try {
				$sql = "SELECT
						count(*) as data
					FROM
						pos_historia_ladosxtrabajador
					WHERE
						ch_sucursal 	= '$ch_sucursal'
						AND dt_dia	= '$dt_dia'
						AND ch_posturno = '$ch_posturno'
						AND ch_tipo	= '$ch_tipo'
						AND ch_lado	= '$ch_lado'
				";

				error_log("CONSULTA :" . $sql);

				if ($sqlca->query($sql) < 0) {
		        	}

				$reg = $sqlca->fetchRow();

				if ($reg['data'] == 0) {
					return 1;
				} else {
					$consulta_interna = "";

					if ($ch_codigo_trabajador != "-1") {

						$consulta_interna = "
									UPDATE 
										pos_historia_ladosxtrabajador
									SET
										ch_codigo_trabajador = '$ch_codigo_trabajador'
									WHERE
										ch_sucursal	= '$ch_sucursal'
										AND dt_dia	= '$dt_dia'
										AND ch_posturno	= '$ch_posturno'
										AND ch_tipo	= '$ch_tipo'
										AND ch_lado	= '$ch_lado'
									";

						error_log("UPDATE :" . $consulta_interna);
					}

					$estado = $sqlca->query($consulta_interna);

					if ($estado < 0) {
						throw new Exception('ERROR AL ACTUALIZAR  TRABAJADOR');
					}

					return 2; //que no inserte
				}
			} catch (Exception $e) {
				throw $e;
			}
		}
	}

	function EliminarTrabajador($ch_sucursal, $dt_dia, $ch_posturno, $ch_lado, $ch_codigo_trabajador, $ch_tipo) {
        	global $sqlca;

		$flag = matricula_personal_Model::validaDia($dt_dia, $ch_posturno, $ch_sucursal);

		if($flag == 1){
			throw new Exception('Fecha ya consolidada!');
		}else{

			try {
				$sql = "
					SELECT
						count(*) as data
					FROM
						pos_historia_ladosxtrabajador
					WHERE
						ch_sucursal	= '$ch_sucursal'
						AND dt_dia	= '$dt_dia'
						AND ch_posturno	= '$ch_posturno'
						AND ch_tipo	= '$ch_tipo'
						AND ch_lado	= '$ch_lado'
				";

				error_log("CONSULTA :" . $sql);

				if ($sqlca->query($sql) < 0) {
				}

				$reg = $sqlca->fetchRow();

				if ($reg['data'] == 0) {
					return 1;
				} else {
					$consulta_interna = "";

					if ($ch_codigo_trabajador == "-1") {

						$consulta_interna = "
									DELETE FROM
										pos_historia_ladosxtrabajador
				        				WHERE
										ch_sucursal	= '$ch_sucursal'
										AND dt_dia	= '$dt_dia'
										AND ch_posturno	= '$ch_posturno'
										AND ch_tipo	= '$ch_tipo'
										AND ch_lado	= '$ch_lado'
						";
					}

					$estado = $sqlca->query($consulta_interna);

					if ($estado < 0) {
						throw new Exception('ERROR AL ELIMINAR  TRABAJADOR');
					}

					return 2; //que no inserte

				}
			} catch (Exception $e) {
				throw $e;
			}
		}
	}

	function Delete_Trabajadores($dia, $turno, $almacen){
        	global $sqlca;

		$flag = matricula_personal_Model::validaDia($dia, $turno, $almacen);

		if($flag == 1){
			?><script>alert("<?php echo 'Fecha ya consolidada!'; ?> ");</script><?php
		}else{
			$sql = " DELETE FROM pos_historia_ladosxtrabajador WHERE dt_dia = '$dia' AND ch_posturno = '$turno' AND ch_sucursal = '$almacen'; ";
			if ($sqlca->query($sql) < 0) {
				?><script>alert("<?php echo 'Error al Elminar Trabajadores'; ?> ");</script><?php		
			}
		}

	}

	function int_sucursales() {
        	global $sqlca;

		try {

			$sql = "
                 		SELECT
					ch_almacen AS ch_sucursal,
					ch_nombre_almacen AS ch_nombre_sucursal
				FROM
					inv_ta_almacenes
				WHERE
					ch_clase_almacen = '1';
				";

			if ($sqlca->query($sql) < 0) {
				throw new Exception("Error al Obtener datos del cliente.");
			}

			while ($reg = $sqlca->fetchRow()) {
				$registro[] = $reg;
			}

			return $registro;

        	} catch (Exception $e) {
            		throw $e;
        	}
    	}

    	function ObtenerTrabajadores_Asignado($fecha_inicio, $fecha_final, $cod_trabajor, $sucursal) {
        	global $sqlca;

        	try {
            		$sql = "
				SELECT 
					t.ch_sucursal,
					t.dt_dia,
					t.ch_posturno,			
					(select count(*) from pos_historia_ladosxtrabajador x where x.ch_sucursal=t.ch_sucursal and x.dt_dia=t.dt_dia and x.ch_posturno=t.ch_posturno and x.ch_tipo='C'"; if($cod_trabajor != '') $sql .=" AND ch_codigo_trabajador = '$cod_trabajor'"; $sql.=") as cantidad_matricula,
					(select count(*) from pos_historia_ladosxtrabajador x where x.ch_sucursal=t.ch_sucursal and x.dt_dia=t.dt_dia and x.ch_posturno=t.ch_posturno and x.ch_tipo='M'"; if($cod_trabajor != '') $sql .=" AND ch_codigo_trabajador = '$cod_trabajor'"; $sql.=") as cantidad_pv,
					t.fecha_replicacion
				FROM
					pos_historia_ladosxtrabajador  t
				WHERE
					dt_dia BETWEEN '$fecha_inicio' AND '$fecha_final'
					AND ch_sucursal = '$sucursal'";

			if($cod_trabajor != '')
			$sql.= "
					AND ch_codigo_trabajador = '$cod_trabajor'";

			$sql.= "
				GROUP BY
					t.ch_sucursal,
					t.dt_dia,
					t.ch_posturno,
					t.fecha_replicacion
				ORDER BY
					dt_dia DESC;
				";

// echo "<pre>";
// print_r($sql);
// echo "</pre>";

			if ($sqlca->query($sql) < 0) {
				?><script>alert("<?php echo 'No hay Trabajadores Matriculados'; ?> ");</script><?php
			}

			$ini = $reg[0]['dt_dia'];

			while ($reg = $sqlca->fetchRow()) {
                		$registro[] = $reg;
			}

			return $registro;

		} catch (Exception $e) {
			throw $e;
		}

	}

	function ObtenerreporteDetallado($fecha_inicio, $turno, $sucursal) {
        	global $sqlca;

		try {
            		$sql = "
				SELECT  
					hl.ch_sucursal,
                            		hl.dt_dia,
                            		hl.ch_posturno,
                            		hl.ch_lado,
                            		hl.ch_codigo_trabajador,
                            		(CASE WHEN ch_tipo='C' THEN 'COMBUSTIBLE' ELSE 'MARKET' END) AS tipo,
					pl.ch_apellido_paterno ||' '|| pl.ch_apellido_materno ||' '|| pl.ch_nombre1 AS nombre
				FROM
					pos_historia_ladosxtrabajador hl
					INNER JOIN pla_ta_trabajadores pl ON (hl.ch_codigo_trabajador = pl.ch_codigo_trabajador)
				WHERE
					dt_dia = '$fecha_inicio'
					AND ch_posturno = '$turno'
					AND hl.ch_sucursal = '$sucursal'
				ORDER BY
					hl.ch_lado;
				";

			if ($sqlca->query($sql) < 0) {
				throw new Exception("Error al Obtener datos del cliente.");
			}

			while ($reg = $sqlca->fetchRow()) {
				$registro[] = $reg;
			}

			return $registro;

		} catch (Exception $e) {
			throw $e;
		}
	}

	function search($fecha_inicio, $fecha_final, $cod_trab){
		global $sqlca;

        	try {
			$sql = "
				SELECT 
					hl.dt_dia,
					hl.ch_posturno,
					hl.ch_lado,
					hl.ch_codigo_trabajador,
					pl.ch_apellido_paterno||' '|| pl.ch_apellido_materno ||' '|| pl.ch_nombre1 AS nombre,
					hl.ch_tipo,
					hl.fecha_replicacion
				FROM
					pos_historia_ladosxtrabajador hl
					INNER JOIN pla_ta_trabajadores pl ON (hl.ch_codigo_trabajador = pl.ch_codigo_trabajador)
				WHERE
					hl.dt_dia BETWEEN '$fecha_inicio' AND '$fecha_final'
					AND hl.ch_codigo_trabajador LIKE '%$cod_trab%'
				ORDER BY
					hl.ch_posturno,
					hl.ch_tipo,
					hl.ch_lado;
				";

			if ($sqlca->query($sql) < 0) {
				throw new Exception("Error al Obtener datos del cliente.");
			}

			while ($reg = $sqlca->fetchRow()) {
				$registro[] = $reg;
			}

			return $registro;

		} catch (Exception $e) {
			throw $e;
		}
        
	}

	function searchPV(){
        	global $sqlca;

        	try {
            		$sql = "
                		SELECT
					sp.s_pos_id,
					fpp.f_pump_id,
					fp.name AS lado,
					fg.product
				FROM
					s_pos sp 
					INNER JOIN f_pump_pos fpp ON (sp.s_pos_id=fpp.s_pos_id)
					INNER JOIN f_pump fp ON (fpp.f_pump_id=fp.f_pump_id)
					INNER JOIN f_grade fg ON (fg.f_pump_id =fp.f_pump_id)
				ORDER BY
					fpp.f_pump_id;
				";

			if ($sqlca->query($sql) < 0) {
				throw new Exception("Error al Obtener datos del cliente.");
			}

			while ($reg = $sqlca->fetchRow()) {
				$registro[] = $reg;
			}

			return $registro;

		} catch (Exception $e) {
			throw $e;
		}

	}    

}

