<?php

class AplicacionesModel extends Model {
	function tmSeleccionaCargoAbono($filtro) {
		global $sqlca;

		$cond = '';

		if ($filtro != "") {
			$cond = " AND trim(cc.cli_codigo)||''||trim(cc.ch_tipdocumento)||''||trim(cc.ch_seriedocumento)||''||trim(ch_numdocumento) = '".pg_escape_string($filtro)."' ";
		}

		$query = "SELECT 
						cc.cli_codigo, 
						cl.cli_razsocial, 
						cc.ch_tipdocumento, 
						cc.ch_seriedocumento, 
						cc.ch_numdocumento, 
						cc.dt_fechaemision, 
						cc.dt_fechasaldo, 
						cc.ch_moneda, 
						cc.nu_importetotal, 
						cc.nu_importesaldo 
					FROM 
						ccob_ta_cabecera cc, 
						int_clientes cl 
					WHERE 
						cc.cli_codigo=cl.cli_codigo $cond
					ORDER BY 
						cc.ch_tipdocumento,
						cc.ch_seriedocumento,
						cc.ch_numdocumento
					";

		if ($sqlca->query($query) <= 0) {
			return $sqlca->get_error();
		}
		while( $reg = $sqlca->fetchRow())  {
			$listado['datos'][] = $reg;
		}
		return $listado;
	}

	function tmListadoFinalCargos($filtro) {
		global $sqlca;

		$cond = '';

		if ($filtro != "") {
  			$cond = " AND trim(cc.cli_codigo)||trim(cc.ch_tipdocumento)||trim(cc.ch_seriedocumento)||trim(cc.ch_numdocumento) = '".pg_escape_string(trim($filtro))."' ";
  		}

  		$query = "SELECT 
						cc.cli_codigo, 
						cl.cli_razsocial, 
						cc.ch_tipdocumento, 
						cc.ch_seriedocumento, 
						cc.ch_numdocumento, 
						cc.dt_fechaemision, 
						cc.dt_fechasaldo, 
						cc.ch_moneda, 
						cc.nu_importetotal, 
						cc.nu_importesaldo,
						cd.dt_fechamovimiento,
						cd.ch_moneda,
						cc.nu_tipocambio,
						cc.ch_sucursal,
						cc.ch_tipcontable,
						cc.dt_fecharegistro,
						cc.dt_fechavencimiento,
						cc.nu_dias_vencimiento,
						cc.nu_importetotal,
						cc.dt_fechasaldo,
						cc.ch_tipdocreferencia,
						cc.ch_numdocreferencia,
						cc.nu_importeafecto,
						cc.ch_tipoimpuesto1,
						cc.nu_impuesto1,
						cc.ch_formapago,
						cc.plc_codigo
					FROM 
						ccob_ta_cabecera cc
					LEFT JOIN ccob_ta_detalle cd ON(cc.ch_tipdocumento = cd.ch_tipdocumento AND cc.ch_seriedocumento = cd.ch_seriedocumento AND cc.ch_numdocumento = cd.ch_numdocumento)
					JOIN int_clientes cl ON (cc.cli_codigo = cl.cli_codigo)
					WHERE 
						cc.nu_importesaldo > 0 
						--AND trim(cc.ch_tipcontable) = 'C' 
						" . $cond . "
					ORDER BY 
						cc.ch_tipdocumento,
						cc.ch_seriedocumento,
						cc.ch_numdocumento;
					";

			echo "Query Monto Aplicar: " . $query;

		$resultado_1 = $sqlca->query($query);

		if($filtro != "") {
			while( $reg = $sqlca->fetchRow())
				$listado['datos'][] = $reg;
		}
		return $listado;    
	}

	function tmListadoCargos($filtro=array()) {
		global $sqlca;

		$cond = '';
		if ($filtro["codigo"] != "") {
			$cond = "AND trim(cc.cli_codigo) = '" . pg_escape_string($filtro["codigo"]) . "'";
		}

		$query = "
SELECT
 cc.cli_codigo,
 cl.cli_razsocial,
 cc.ch_tipdocumento,
 TD.tab_desc_breve AS no_tipo_documento,
 cc.ch_seriedocumento,
 cc.ch_numdocumento,
 cc.dt_fechaemision,
 cc.dt_fechasaldo,
 cc.ch_moneda,
 cc.nu_importetotal,
 cc.nu_importesaldo 
FROM 
 ccob_ta_cabecera AS cc
 JOIN int_clientes AS cl
  USING(cli_codigo)
 LEFT JOIN int_tabla_general AS TD
  ON(cc.ch_tipdocumento = substring(TRIM(TD.tab_elemento) for 2 FROM length(TRIM(TD.tab_elemento))-1) AND TD.tab_tabla = '08' AND TD.tab_elemento <> '000000')
WHERE 
 cc.nu_importesaldo > 0
 " . $cond . "
ORDER BY 
cc.ch_tipdocumento,
cc.ch_seriedocumento,
cc.ch_numdocumento
		";
		//AND trim(cc.ch_tipcontable) = 'C';
		//print($query);
		if ($sqlca->query($query)<=0) {
			return $sqlca->get_error();
		}

		if($filtro["codigo"] != "") {
			while( $reg = $sqlca->fetchRow()) {
				$listado['datos'][] = $reg;
			}
		}
		return $listado;
	}

	function tmListadoAbonos($codigo, $tipo) {
		global $sqlca;

		$cond = '';

		if ($codigo != "") {
			$cond = " AND trim(cli_codigo) = '".pg_escape_string(trim($codigo))."' ";
			$cond .= ($tipo=='10' || $tipo=='35' || $tipo=='20')?" AND trim(ch_tipdocumento) = '20' ":"";
		}

		$query = "SELECT 
						ch_tipdocumento, 
						ch_seriedocumento, 
						ch_numdocumento, 
						dt_fechaemision, 
						dt_fechasaldo, 
						ch_moneda, 
						nu_importetotal, 
						nu_importesaldo
					FROM 
						ccob_ta_cabecera 
					WHERE 
						nu_importesaldo>0 
						--AND trim(ch_tipcontable) = 'A'  
						$cond
					ORDER BY 
						ch_numdocumento ASC ";

						trigger_error($query);

						if ($sqlca->query($query)<=0) {
							return $sqlca->get_error();
						}

						while( $reg = $sqlca->fetchRow()) {
							$listado[] = $reg;
						}

						return $listado;
					}
	function ActualizarCargos($CodCliente, $CodDocCargo, $NumDocCargo, $CodDocAbono, $NumDocAbono, $ImporteAplicacion, $data) {
		global $sqlca;
		//$FechaAplicacion = $_SESSION['fec_aplicacion'];
		//$result = $sqlca->functionDB("ccob_fn_aplicaciones('".$CodDocCargo."','".$NumDocCargo."','".$CodDocAbono."','".$NumDocAbono."',".$ImporteAplicacion.",TO_DATE('".$FechaAplicacion."','DD/MM/YYYY'),'".trim($CodCliente)."')");

		$sql = "
				UPDATE
					ccob_ta_cabecera
				SET
					nu_importesaldo = nu_importesaldo - $ImporteAplicacion,
					dt_fecha_actualizacion = now()
				WHERE
					cli_codigo = '$CodCliente' AND
					ch_tipdocumento = '$CodDocCargo' AND
					ch_seriedocumento||ch_numdocumento = '$NumDocCargo';
				";

				var_dump($sql);

				if($sqlca->query($sql) < 0)
					return $sqlca->get_error();

					/*$sql2 = "INSERT INTO ccob_ta_cabecera (
										cli_codigo,
										ch_tipdocumento,
										ch_seriedocumento,
										ch_numdocumento,
										ch_tipcontable,
										dt_fechaemision,
										dt_fecharegistro,
										dt_fechavencimiento,
										nu_dias_vencimiento,
										ch_moneda,
										nu_tipocambio,
										nu_importetotal,
										nu_importesaldo,
										dt_fechasaldo,
										ch_tipdocreferencia,
										ch_numdocreferencia,
										ch_sucursal,
										nu_importeafecto,
										ch_tipoimpuesto1,
										nu_impuesto1,
										dt_fecha_actualizacion,
										ch_formapago,
										plc_codigo
									) VALUES (
										'$CodCliente',
										'$CodDocCargo',
										'" . pg_escape_string($data["datos"][0]['ch_seriedocumento']) . "',
										'" . pg_escape_string($data["datos"][0]['ch_numdocumento']) . "',
										'" . pg_escape_string($data["datos"][0]['ch_tipcontable']) . "',
										'" . pg_escape_string($data["datos"][0]['dt_fechaemision']) . "',
										'" . pg_escape_string($data["datos"][0]['dt_fecharegistro']) . "',
										'" . pg_escape_string($data["datos"][0]['dt_fechavencimiento']) . "',
										'" . pg_escape_string($data["datos"][0]['nu_dias_vencimiento']) . "',
										'" . pg_escape_string($data["datos"][0]['ch_moneda']) . "',
										" . pg_escape_string($data["datos"][0]['nu_tipocambio']) . ",
										" . pg_escape_string($data["datos"][0]['nu_importetotal']) . ",
										0.00,
										'" . pg_escape_string($data["datos"][0]['dt_fechasaldo']) . "',
										'" . pg_escape_string($data["datos"][0]['ch_tipdocreferencia']) . "',
										'" . pg_escape_string($data["datos"][0]['ch_numdocreferencia']) . "',
										'" . pg_escape_string($data["datos"][0]['ch_sucursal']) . "',
										" . pg_escape_string($data["datos"][0]['nu_importeafecto']) . ",
										'" . pg_escape_string($data["datos"][0]['ch_tipoimpuesto1']) . "',
										" . pg_escape_string($data["datos"][0]['nu_impuesto1']) . ",
										now(),
										'" . pg_escape_string($data["datos"][0]['ch_formapago']) . "',
										'" . pg_escape_string($data["datos"][0]['plc_codigo']) . "'
										);

									";

					echo "<pre>";
					print_r($sql2);
					echo "</pre>";

					if($sqlca->query($sql2) < 0)
					return $sqlca->get_error();*/

					$sql2 = "INSERT INTO ccob_ta_detalle (
									cli_codigo,
									ch_tipdocumento,
									ch_seriedocumento,
									ch_numdocumento,
									ch_identidad,
									ch_tipmovimiento,
									dt_fechamovimiento,
									ch_moneda,
									nu_tipocambio,
									nu_importemovimiento,
									plc_codigo,
									ch_sucursal,
									dt_fecha_actualizacion
								) VALUES (
									'$CodCliente',
									'$CodDocCargo',
									'" . pg_escape_string($data["datos"][0]['ch_seriedocumento']) . "',
									'" . pg_escape_string($data["datos"][0]['ch_numdocumento']) . "',
									'3',
									'3',
									'" . pg_escape_string($data["datos"][0]['dt_fechamovimiento']) . "',
									'" . pg_escape_string($data["datos"][0]['ch_moneda']) . "',
									" . pg_escape_string($data["datos"][0]['nu_tipocambio']) . ",
									" . pg_escape_string($data["datos"][0]['nu_importetotal']) . ",
									'12103',
									'" . pg_escape_string($data["datos"][0]['ch_sucursal']) . "',
									now()
									);
								";

		if($sqlca->query($sql2) < 0)
			return $sqlca->get_error();

    		return OK;
	}

	function ActualizarCargosNC($CodCliente,$numdoc,$ImporteAplicacion) {
		global $sqlca;

		//$FechaAplicacion = $_SESSION['fec_aplicacion'];
		//$result = $sqlca->functionDB("ccob_fn_aplicaciones('".$CodDocCargo."','".$NumDocCargo."','".$CodDocAbono."','".$NumDocAbono."',".$ImporteAplicacion.",TO_DATE('".$FechaAplicacion."','DD/MM/YYYY'),'".trim($CodCliente)."')");
		$sql = "UPDATE
						ccob_ta_cabecera
					SET
						nu_importesaldo			= nu_importesaldo - $ImporteAplicacion,
						dt_fecha_actualizacion	= now()
					WHERE
						cli_codigo									= '$CodCliente' AND
						ch_tipdocumento							= '20' AND
						ch_seriedocumento||ch_numdocumento	= '$numdoc';
					";

		var_dump($sql);

		if($sqlca->query($sql) < 0)
			return $sqlca->get_error();
				
		$sql2 = "
			UPDATE
				ccob_ta_detalle
			SET
				ch_tipmovimiento = '3'
			WHERE
				cli_codigo = '$CodCliente' AND
				ch_tipdocumento = '$CodDocCargo' AND
				ch_seriedocumento||ch_numdocumento = '$NumDocCargo';
			";

		var_dump($sql2);

		if($sqlca->query($sql2) < 0)
			return $sqlca->get_error();

		return OK;
	}

	function AplicarporMonto($CodCliente,$CodDocCargo,$NumDocCargo,$ImporteAplicacion){
		global $sqlca;

		$FechaAplicacion = $_SESSION['fec_aplicacion'];

		$result = $sqlca->functionDB("ccob_fn_aplicaciones_por_monto_notas_credito('".$CodDocCargo."','".$NumDocCargo."',".$ImporteAplicacion.",TO_DATE('".$FechaAplicacion."','DD/MM/YYYY'),'".trim($CodCliente)."')");

		return OK;
	}

	function ClientesCBArray($condicion='') {
		global $sqlca;

		$cbArray = array();
		$query = "SELECT cli_codigo,cli_razsocial,cli_rsocialbreve FROM int_clientes ".
		$query .= ($condicion!=''?' WHERE '.$condicion:'').' ORDER BY 2';

		if ($sqlca->query($query)<=0)
			return $cbArray;

		while($result = $sqlca->fetchRow()) {
			$cbArray[trim($result["cli_codigo"])] = $result["cli_codigo"].' '.$result["cli_rsocialbreve"];
		}

		ksort($cbArray);

		return $cbArray;
	}
}
