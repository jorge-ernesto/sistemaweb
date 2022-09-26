<?php
// Descomentar estas líneas, cuando estamos en modo - development
/*
error_reporting(-1);
ini_set('display_errors', 1);
*/
// Descomentar estas líneas, cuando estamos en modo - production

ini_set('display_errors', 0);
if (version_compare(PHP_VERSION, '5.3', '>='))
{
	error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
}
else
{
	error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE);
}

class ValeCRUDModel {

	function BEGINTransacction() {
       	global $sqlca;

		try {
			$sql = "BEGIN;";
			if ($sqlca->query($sql) < 0) {
				throw new Exception("ERROR BEGIN");
			}
		} catch (Exception $e) {
			throw $e;
		}
	}

	function COMMITTransacction() {
		global $sqlca;

        try {
			$sql = "COMMIT;";
			if ($sqlca->query($sql) < 0) {
				throw new Exception("ERROR COMMIT TRANSACION");
			}
		} catch (Exception $e) {
			throw $e;
		}
	}

	function ROLLBACKTransacction() {
		global $sqlca;

		try {
			$sql = "ROLLBACK;";
			if ($sqlca->query($sql) < 0) {
				throw new Exception("ERROR ROLLBACK");
			}
		} catch (Exception $e) {
			throw $e;
		}
	}

    function ObtenerEstaciones() {
		global $sqlca;
	
		try {
			$sql = "
			SELECT
					ch_almacen as almacen,
					trim(ch_nombre_almacen) as nombre
				FROM
					inv_ta_almacenes
				WHERE
					ch_clase_almacen='1'
				ORDER BY
					ch_almacen;
			";

			if($sqlca->query($sql) <= 0)
				throw new Exception("Error no se encontro turnos en la fecha indicada");

			while($reg = $sqlca->fetchRow())
				$registro[] = $reg;

			return $registro;
		}catch(Exception $e){
			throw $e;
		}
    }

	function getListAll($data, $jqGridModel) {
		error_log( json_encode( $data ) );

		global $sqlca;

		$Nu_Almacen = trim($data['Nu_Almacen']);
		$Nu_Almacen = strip_tags($Nu_Almacen);

		$Fe_Inicial = trim($data['Fe_Inicial']);
		$Fe_Inicial = strip_tags($Fe_Inicial);
		$Fe_Inicial = explode("/", $Fe_Inicial);
		$Fe_Inicial = $Fe_Inicial[2] . "-" . $Fe_Inicial[1] . "-" . $Fe_Inicial[0];

		$Fe_Final = trim($data['Fe_Final']);
		$Fe_Final = strip_tags($Fe_Final);
		$Fe_Final = explode("/", $Fe_Final);
		$Fe_Final = $Fe_Final[2] . "-" . $Fe_Final[1] . "-" . $Fe_Final[0];

		$Nu_Estado = trim($data['Nu_Estado']);
		$Nu_Estado = strip_tags($Nu_Estado);

		$Nu_Ticket = trim($data['Nu_Ticket']);
		$Nu_Ticket = strip_tags($Nu_Ticket);

		$sIdCliente = trim($data['sIdCliente']);
		$sIdCliente = strip_tags($sIdCliente);

		$sNombreCliente = trim($data['sNombreCliente']);
		$sNombreCliente = strip_tags($sNombreCliente);

		$cond_almacen = NULL;
		$cond_estado = NULL;
		$cond_nu_ticket = NULL;
		$cond_id_cliente = ( !empty($sIdCliente) && !empty($sNombreCliente) ? "AND VC.ch_cliente='" . $sIdCliente . "'" : '' );

		if(!empty($Nu_Almacen))
			$cond_almacen = "AND VC.ch_sucursal = '" . $Nu_Almacen . "'";

		if($Nu_Estado == '1')//Pendiente
			$cond_estado = "AND VC.ch_liquidacion IS NULL";
		else if($Nu_Estado == '2')//Facturado
			$cond_estado = "AND VC.ch_liquidacion != ''";
		else if($Nu_Estado == '3')//Anulado
			$cond_estado = "AND VC.nu_importe = 0";

		if(!empty($Nu_Ticket))
			$cond_nu_ticket = "AND VC.ch_documento LIKE '%" . $Nu_Ticket . "%'";

		$sqlca->query("
		SELECT
			COUNT(*) total
		FROM
			val_ta_cabecera VC
		WHERE
			VC.dt_fecha BETWEEN '" . $Fe_Inicial . "' AND '" . $Fe_Final . "'
			" . $cond_almacen . "
			" . $cond_estado . "
			" . $cond_nu_ticket . "
			" . $cond_id_cliente . "
		");

		$cantidad_registros = $sqlca->fetchRow();
		$paginador = $jqGridModel->Config($cantidad_registros["total"]);

		try {
			$sql = "
			SELECT
				ALMA.ch_almacen AS nu_almacen,
				ALMA.ch_nombre_almacen AS no_almacen,
				TO_CHAR(VC.dt_fecha, 'DD/MM/YYYY') AS fe_sistema,
				CLI.cli_ruc AS nu_documento_idenidad,
				CLI.cli_razsocial AS no_razon_social,
				VC.ch_documento,
				VCC.ch_numeval AS nu_vale_manual,
				VC.nu_importe,
				VC.ch_tarjeta,
				VC.ch_placa,
				VC.nu_odometro,
				VC.ch_turno AS nu_turno,
				VC.ch_lado AS nu_lado,
				(CASE WHEN ruc.razsocial IS NOT NULL AND trim(ruc.razsocial) != '' THEN ruc.razsocial ELSE PLACA.nomusu END) AS no_chofer,
				VC.ch_usuario,
				VC.ch_auditorpc,
				VC.dt_fechaactualizacion
			FROM
				val_ta_cabecera AS VC
				JOIN inv_ta_almacenes AS ALMA
					ON(ALMA.ch_almacen = VC.ch_sucursal)
				JOIN int_clientes AS CLI
					ON(CLI.cli_codigo = VC.ch_cliente)
				LEFT JOIN pos_fptshe1 AS PLACA
					ON(VC.ch_cliente = PLACA.codcli AND VC.ch_tarjeta = PLACA.numtar AND VC.ch_placa = PLACA.numpla)
				LEFT JOIN val_ta_complemento AS VCC
					ON(VCC.ch_sucursal = VC.ch_sucursal AND VCC.dt_fecha = VC.dt_fecha AND VCC.ch_documento = VC.ch_documento)
				LEFT JOIN ruc
					ON(ruc.ruc = VC.nu_documento_identidad_chofer)
			WHERE
				VC.dt_fecha BETWEEN '" . $Fe_Inicial . "' AND '" . $Fe_Final . "'
				" . $cond_almacen . "
				" . $cond_estado . "
				" . $cond_nu_ticket . "
				" . $cond_id_cliente . "
			ORDER BY
				1,
				2,
				VC.dt_fecha,
				6
			LIMIT
				" . $paginador["limit"] . "
			OFFSET
				" . $paginador["start"];
			error_log( json_encode( $sql ) );
			
			if ($sqlca->query($sql) < 0){
				return $response = array(
					'status' => 'danger',
					'message' => 'Problemas al buscar registros'
				);
			}

			if ($sqlca->query($sql) == 0){
				return $response = array(
					'status' => 'warning',
					'message' => 'No hay registros'
				);
			}

			$jqGridModel->DataSource($sqlca->fetchAll());

			$response = array(
				'status' => 'success',
				'message' => 'Registros encontrados satisfactoriamente',
				'data' => $jqGridModel
			);

			return $response;

		}catch(Exception $e){
			throw $e;
		}
	}

	function getListAllExcel($data) {
		global $sqlca;

		$Nu_Almacen = trim($data['Nu_Almacen']);
		$Nu_Almacen = strip_tags($Nu_Almacen);

		$Fe_Inicial = trim($data['Fe_Inicial']);
		$Fe_Inicial = strip_tags($Fe_Inicial);
		$Fe_Inicial = explode("/", $Fe_Inicial);
		$Fe_Inicial = $Fe_Inicial[2] . "-" . $Fe_Inicial[1] . "-" . $Fe_Inicial[0];

		$Fe_Final = trim($data['Fe_Final']);
		$Fe_Final = strip_tags($Fe_Final);
		$Fe_Final = explode("/", $Fe_Final);
		$Fe_Final = $Fe_Final[2] . "-" . $Fe_Final[1] . "-" . $Fe_Final[0];

		$Nu_Estado = trim($data['Nu_Estado']);
		$Nu_Estado = strip_tags($Nu_Estado);

		$Nu_Ticket = trim($data['Nu_Ticket']);
		$Nu_Ticket = strip_tags($Nu_Ticket);

		$sIdCliente = trim($data['sIdCliente']);
		$sIdCliente = strip_tags($sIdCliente);

		$sNombreCliente = trim($data['sNombreCliente']);
		$sNombreCliente = strip_tags($sNombreCliente);

		$cond_almacen = NULL;
		$cond_estado = NULL;
		$cond_nu_ticket = NULL;
		$cond_id_cliente = ( !empty($sIdCliente) && !empty($sNombreCliente) ? "AND VC.ch_cliente='" . $sIdCliente . "'" : '' );

		if(!empty($Nu_Almacen))
			$cond_almacen = "AND VC.ch_sucursal = '" . $Nu_Almacen . "'";

		if($Nu_Estado == '1')//Pendiente
			$cond_estado = "AND VC.ch_liquidacion IS NULL";
		else if($Nu_Estado == '2')//Facturado
			$cond_estado = "AND VC.ch_liquidacion != ''";
		else if($Nu_Estado == '3')//Anulado
			$cond_estado = "AND VC.nu_importe = 0";

		if(!empty($Nu_Ticket))
			$cond_nu_ticket = "AND VC.ch_documento LIKE '%" . $Nu_Ticket . "%'";

		try {
			$sql = "
			SELECT
				ALMA.ch_almacen AS nu_almacen,
				ALMA.ch_nombre_almacen AS no_almacen,
				TO_CHAR(VC.dt_fecha, 'DD/MM/YYYY') AS fe_sistema,
				CLI.cli_ruc AS nu_documento_idenidad,
				CLI.cli_razsocial AS no_razon_social,
				VC.ch_documento,
				VCC.ch_numeval AS nu_vale_manual,
				VC.nu_importe,
				VC.ch_tarjeta,
				VC.ch_placa,
				VC.nu_odometro,
				VC.ch_turno AS nu_turno,
				VC.ch_lado AS nu_lado,
				(CASE WHEN ruc.razsocial IS NULL THEN PLACA.nomusu ELSE ruc.razsocial END) AS no_chofer,
				VC.ch_usuario,
				VC.ch_auditorpc,
				VC.dt_fechaactualizacion
			FROM
				val_ta_cabecera AS VC
				JOIN inv_ta_almacenes AS ALMA
					ON(ALMA.ch_almacen = VC.ch_sucursal)
				JOIN int_clientes AS CLI
					ON(CLI.cli_codigo = VC.ch_cliente)
				LEFT JOIN pos_fptshe1 AS PLACA
					ON(VC.ch_cliente = PLACA.codcli AND VC.ch_tarjeta = PLACA.numtar AND VC.ch_placa = PLACA.numpla)
				LEFT JOIN val_ta_complemento AS VCC
					ON(VCC.ch_sucursal = VC.ch_sucursal AND VCC.dt_fecha = VC.dt_fecha AND VCC.ch_documento = VC.ch_documento)
				LEFT JOIN ruc
					ON(ruc.ruc = VC.nu_documento_identidad_chofer)
			WHERE
				VC.dt_fecha BETWEEN '" . $Fe_Inicial . "' AND '" . $Fe_Final . "'
				" . $cond_almacen . "
				" . $cond_estado . "
				" . $cond_nu_ticket . "
				" . $cond_id_cliente . "
			ORDER BY
				1,
				2,
				VC.dt_fecha,
				6
			";
			
			if ($sqlca->query($sql) < 0){
				return $response = array(
					'status' => 'danger',
					'message' => 'Problemas al buscar registros'
				);
			}

			if ($sqlca->query($sql) == 0){
				return $response = array(
					'status' => 'warning',
					'message' => 'No hay registros'
				);
			}

			$response = array(
				'status' => 'success',
				'message' => 'Registros encontrados satisfactoriamente',
				'data' => $sqlca->fetchAll()
			);

			return $response;

		}catch(Exception $e){
			throw $e;
		}
	}

	private function verify_relation_partner_plate($arrData){
		global $sqlca;

		$sql = "
SELECT
 COUNT(*) AS existe
FROM
 int_clientes AS CLI
 JOIN pos_fptshe1 AS PLACA
  ON (CLI.cli_codigo = PLACA.codcli)
WHERE
 PLACA.codcli = '" . $arrData['iNumeroDocumentoIdentidad'] . "'
 AND numpla = '" . $arrData['sPlaca'] . "'
 AND numtar = '" . $arrData['sTarjeta'] . "'
LIMIT 1
		";

		$iStatus = $sqlca->query($sql);
		$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'danger', 'sMessage' => 'Problemas al verificar relación de cliente , placa y tarjeta');
    	if ( $iStatus >= 0 ) {
        	$row = $sqlca->fetchRow();//0 = no existe registro
	        $arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'warning', 'sMessage' => 'problemas con relación de cliente, placa y tarjeta, verificar');
	        if ( (int)$row["existe"] >= 1 )//1 = existe registro
	        	$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'success', 'sMessage' => 'Cliente - placa - tarjeta encontrado');
		}
		return $arrResponse;
	}

	public function addVale($data, $arrDetailCreditVoucher, $usuario, $ip) { //addVale
		global $sqlca;

		ValeCRUDModel::BEGINTransacction();

		$response = array(
			'status' => 'success',
			'message' => 'Registro guardado satisfactoriamente'
		);

		//get error de mensaje
		$message = '';
		$queryMessageError = '';

		//Variables opcionales
		$Nu_Documento_Identidad_Chofer = '';
		$Nu_Odometro = 0;

		try {
			$Nu_Documento_Identidad = trim($data['Nu_Documento_Identidad']);
			$Nu_Documento_Identidad = strip_tags($Nu_Documento_Identidad);

			$No_Placa = trim($data['No_Placa']);
			$No_Placa = strip_tags($No_Placa);

			$Nu_Tarjeta = trim($data['Nu_Tarjeta']);
			$Nu_Tarjeta = strip_tags($Nu_Tarjeta);

			//Verificar relación de cliente y placa exista, para agregar vale de crédito
			$arrValidacion = array(
				'iNumeroDocumentoIdentidad' => $Nu_Documento_Identidad,
				'sPlaca' => $No_Placa,
				'sTarjeta' => $Nu_Tarjeta,
			);
			$arrResponseValidacion = ValeCRUDModel::verify_relation_partner_plate($arrValidacion);

			if ( $arrResponseValidacion['sStatus'] != "success" ){
				$response = array(
					'status' => $arrResponseValidacion['sStatus'],
					'message' => $arrResponseValidacion['sMessage'],
				);
				return $response;
			}
			// /. Verificar

			$Nu_Almacen = trim($data['Nu_Almacen']);
			$Nu_Almacen = strip_tags($Nu_Almacen);

			$Fe_Emision = trim($data['Fe_Emision']);
			$Fe_Emision = strip_tags($Fe_Emision);

			$Fe_Emision = explode("/", $Fe_Emision);

			$Fe_Emision = $Fe_Emision[2] . "-" . $Fe_Emision[1] . "-" . $Fe_Emision[0];

			$Ch_Documento = trim($data['Ch_Documento']);
			$Ch_Documento = strip_tags($Ch_Documento);

			$Ch_Documento_Manual = trim($data['Ch_Documento_Manual']);
			$Ch_Documento_Manual = strip_tags($Ch_Documento_Manual);

			$Nu_Turno = trim($data['Nu_Turno']);
			$Nu_Turno = strip_tags($Nu_Turno);

			$Nu_Caja = trim($data['Nu_Caja']);
			$Nu_Caja = strip_tags($Nu_Caja);

			$Nu_Lado = trim($data['Nu_Lado']);
			$Nu_Lado = strip_tags($Nu_Lado);

			if(isset($data['Nu_Documento_Identidad_Chofer'])){
				$Nu_Documento_Identidad_Chofer = trim($data['Nu_Documento_Identidad_Chofer']);
				$Nu_Documento_Identidad_Chofer = strip_tags($Nu_Documento_Identidad_Chofer);
			}

			if(isset($data['Nu_Odometro'])){
				$Nu_Odometro = trim($data['Nu_Odometro']);
				$Nu_Odometro = strip_tags($Nu_Odometro);
			}

			$Nu_Id_Producto = trim($data['Nu_Id_Producto']);
			$Nu_Id_Producto = strip_tags($Nu_Id_Producto);

			$Nu_Cantidad = trim($data['Nu_Cantidad']);
			$Nu_Cantidad = strip_tags($Nu_Cantidad);

			$Nu_Precio = trim($data['Nu_Precio']);
			$Nu_Precio = strip_tags($Nu_Precio);

			$Nu_Total = trim($data['Nu_Total']);
			$Nu_Total = strip_tags($Nu_Total);

			$sqlca->query("
			SELECT
				COUNT(*) AS nu_existe_documento
			FROM
				val_ta_cabecera
			WHERE
				ch_sucursal 			= '" . $Nu_Almacen . "'
				AND dt_fecha 			= '" . $Fe_Emision . "'
				AND trim(ch_documento) 	= '" . $Ch_Documento . "';
			");

			$exist = $sqlca->fetchRow();

			if($exist['nu_existe_documento'] == 1){
				$response = array(
					'status' => 'error',
					'message' => 'Ya existe vale crédito'
				);
			}else{
				// la tabla val_ta_cabecera el campo nu_importe se actualizar po el trigger de val_ta_detalle nu_importe, " . $Nu_Total . ",

	 			$sql_val_ta_cabecera = "
	 			INSERT INTO val_ta_cabecera (
					ch_sucursal,
					dt_fecha,
					ch_documento,
					ch_cliente,
					ch_glosa,
					ch_placa,
					nu_odometro,
					ch_tarjeta,
					ch_estado,
					ch_caja,
					ch_lado,
					dt_fechaactualizacion,
					ch_usuario,
					ch_auditorpc,
					ch_turno,
					flg_replicacion,
					fecha_replicacion,
					nu_documento_identidad_chofer
	 			) VALUES (
	 				'" . $Nu_Almacen . "',
	 				'" . $Fe_Emision . "',
	 				'" . $Ch_Documento . "',
	 				'" . $Nu_Documento_Identidad . "',
	 				'VALE MANUAL AGREGADO POR: " . $usuario . "',--ch_glosa
	 				'" . $No_Placa . "',
	 				'" . $Nu_Odometro . "',
	 				'" . $Nu_Tarjeta . "',
					'1',--ch_estado
	 				'" . $Nu_Caja . "',
	 				'" . $Nu_Lado . "',
	 				NOW(),--dt_fechaactualizacion
	 				'" . $usuario . "',
	 				'" . $ip . "',--ch_auditorpc = la Ip de PC
	 				'" . $Nu_Turno . "',
					0,--flg_replicacion
					NOW(),--fecha_replicacion
					'" . $Nu_Documento_Identidad_Chofer . "'
	 			);
	 			";

				if ($sqlca->query($sql_val_ta_cabecera) < 0){
					$queryMessageError = "Error insert val_ta_cabecera";
					$queryError = $sql_val_ta_cabecera;
					$response = array(
						'status' => 'error',
						'queryMessageError' => $queryMessageError,
						'queryError' => $queryError
					);
				}

				$fTotal=0.00;
				foreach ($arrDetailCreditVoucher as $row) {
					$arrSQLDetail[] = "(
 					'" . strip_tags(stripslashes($Nu_Almacen)) . "',
 					'" . $Fe_Emision . "',
 					'" . $Ch_Documento . "',
 					'" . strip_tags(stripslashes($row['iIdItem'])) . "',
 					'" . strip_tags(stripslashes($row['fCantidad'])) . "',
 					'" . strip_tags(stripslashes($row['fPrecioVenta'])) . "',
 					'" . strip_tags(stripslashes($row['fTotal'])) . "',
					'1',--ch_estado
	 				NOW(),--dt_fechaactualizacion
	 				'" . $usuario . "',
	 				'" . $ip . "',--ch_auditorpc = la Ip de PC
					0,--flg_replicacion
					NOW(),--fecha_replicacion
					0,--nu_precio_especial
					util_fn_igv())
					";
					$fTotal += $row['fTotal'];
				}

				$sql_val_ta_detalle = "
				INSERT INTO val_ta_detalle (
					ch_sucursal,
					dt_fecha,
					ch_documento,
					ch_articulo,
					nu_cantidad,
					nu_precio_unitario,
					nu_importe,
					ch_estado,
					dt_fechaactualizacion,
					ch_usuario,
					ch_auditorpc,
					flg_replicacion,
					fecha_replicacion,
					nu_precio_especial,
					nu_factor_igv
	 			) VALUES " . implode(',', $arrSQLDetail);

				if ($sqlca->query($sql_val_ta_detalle) < 0){
					$queryMessageError = "Error insert sql_val_ta_detalle";
					$queryError = $sql_val_ta_detalle;
					$response = array(
						'status' => 'error',
						'queryMessageError' => $queryMessageError,
						'queryError' => $queryError
					);
				}

				//Insert into en val_ta_complemento en addVale
				$sql_val_ta_complemento = "
	 			INSERT INTO val_ta_complemento (
					ch_sucursal,
					dt_fecha,
					ch_documento,
					ch_numeval,
					nu_importe,
					ch_estado,
					dt_fechaactualizacion,
					ch_usuario,
					ch_auditorpc,
					flg_replicacion,
					fecha_replicacion,
					ch_tipovale 
	 			) VALUES (
	 				'" . $Nu_Almacen . "',
	 				'" . $Fe_Emision . "',
	 				'" . $Ch_Documento . "',
	 				'" . $Ch_Documento_Manual . "',
	 				" . $fTotal . ",
	 				'1',
					NOW(),
	 				'',
	 				'" . $ip . "',
					0,
					NOW(),
	 				'00'
	 			);
	 			";

				if ($sqlca->query($sql_val_ta_complemento) < 0){
					$response = array(
						'status' => 'error',
				        'message_sql' => $sqlca->get_error(),
						'message' => 'Problemas al insertar Vale de crédito (Complement)',
						'arrComplemento' => $arrComplemento,
					);
					return $response;
				}

				if($response['status'] == 'error'){
					ValeCRUDModel::ROLLBACKTransacction();
					$response = array(
						'status' => 'error',
						'message' => 'Error al guardar Vale de Crédito',
						'queryMessageError' => $queryMessageError,
						'queryError' => $queryError
					);
				}else{
					ValeCRUDModel::COMMITTransacction();
				}
			}
			return $response;
		}catch(Exception $e){
			throw $e;
		}
	}

	public function updateVale($data, $arrDetailCreditVoucher, $usuario, $ip) { //updateVale
		global $sqlca;

		ValeCRUDModel::BEGINTransacction();

		$response = array(
			'status' => 'success',
			'message' => 'Registro acutalizado satisfactoriamente'
		);

		//Variables opcionales
		$Nu_Documento_Identidad_Chofer = '';
		$Nu_Odometro = 0;

		try {
			$Nu_Documento_Identidad = trim($data['Nu_Documento_Identidad']);
			$Nu_Documento_Identidad = strip_tags($Nu_Documento_Identidad);

			$No_Placa = trim($data['No_Placa']);
			$No_Placa = strip_tags($No_Placa);

			$Nu_Tarjeta = trim($data['Nu_Tarjeta']);
			$Nu_Tarjeta = strip_tags($Nu_Tarjeta);

			//Verificar relación de cliente y placa exista, para agregar vale de crédito
			$arrValidacion = array(
				'iNumeroDocumentoIdentidad' => $Nu_Documento_Identidad,
				'sPlaca' => $No_Placa,
				'sTarjeta' => $Nu_Tarjeta,
			);
			$arrResponseValidacion = ValeCRUDModel::verify_relation_partner_plate($arrValidacion);

			if ( $arrResponseValidacion['sStatus'] != "success" ){
				$response = array(
					'status' => $arrResponseValidacion['sStatus'],
					'message' => $arrResponseValidacion['sMessage'],
				);
				return $response;
			}
			// /. Verificar

			$Nu_Almacen = trim($data['Nu_Almacen']);
			$Nu_Almacen = strip_tags($Nu_Almacen);

			$Fe_Emision = trim($data['Fe_Emision']);
			$Fe_Emision = strip_tags($Fe_Emision);

			$Fe_Emision = explode("/", $Fe_Emision);

			$Fe_Emision = $Fe_Emision[2] . "-" . $Fe_Emision[1] . "-" . $Fe_Emision[0];

			$Ch_Documento = trim($data['Ch_Documento']);
			$Ch_Documento = strip_tags($Ch_Documento);

			$Ch_Documento_Manual = trim($data['Ch_Documento_Manual']);
			$Ch_Documento_Manual = strip_tags($Ch_Documento_Manual);

			$Nu_Turno = trim($data['Nu_Turno']);
			$Nu_Turno = strip_tags($Nu_Turno);

			$Nu_Caja = trim($data['Nu_Caja']);
			$Nu_Caja = strip_tags($Nu_Caja);

			$Nu_Lado = '';

			if(isset($data['Nu_Lado'])){
				$Nu_Lado = trim($data['Nu_Lado']);
				$Nu_Lado = strip_tags($Nu_Lado);
			}else{
				$Nu_Lado = '';
			}

			if(isset($data['Nu_Documento_Identidad_Chofer'])){
				$Nu_Documento_Identidad_Chofer = trim($data['Nu_Documento_Identidad_Chofer']);
				$Nu_Documento_Identidad_Chofer = strip_tags($Nu_Documento_Identidad_Chofer);
			}

			if(isset($data['Nu_Odometro'])){
				$Nu_Odometro = trim($data['Nu_Odometro']);
				$Nu_Odometro = strip_tags($Nu_Odometro);
			}

			$Nu_Id_Producto = trim($data['Nu_Id_Producto']);
			$Nu_Id_Producto = strip_tags($Nu_Id_Producto);

			$Nu_Cantidad = trim($data['Nu_Cantidad']);
			$Nu_Cantidad = strip_tags($Nu_Cantidad);

			$Nu_Precio = trim($data['Nu_Precio']);
			$Nu_Precio = strip_tags($Nu_Precio);

			$Nu_Total = trim($data['Nu_Total']);
			$Nu_Total = strip_tags($Nu_Total);

			// 1 Paso .- Eliminar vale y volver a crearlo
			$sqlca->query("
			SELECT
				*
			FROM
				val_ta_cabecera
			WHERE
				ch_sucursal 		= '" . $Nu_Almacen . "'
				AND dt_fecha 		= '" . $Fe_Emision . "'
				AND ch_documento 	= '" . $Ch_Documento . "';
			");
			$row = $sqlca->fetchRow();

			$sqlca->query("SELECT validar_consolidacion('" . $Fe_Emision . "', '" . $Nu_Turno . "', '" . $Nu_Almacen . "')");
			$row_2 = $sqlca->fetchRow();

			if(trim($row['ch_liquidacion']) == 'LIQ'){
				$response = array(
					'status' => 'error',
					'message' => 'Vale crédito se encuentra liquidado, no se puede modificar',
				);
				return $response;
			}else if($row_2['validar_consolidacion'] == 1){
				$response = array(
					'status' => 'error',
					'message' => 'El dia y turno se encuentra consolidado',
				);
				return $response;
			} else {
				$sql_delete_val_ta_detalle = "DELETE FROM val_ta_detalle WHERE ch_sucursal = '" . $Nu_Almacen . "' AND dt_fecha = '" . $Fe_Emision . "' AND ch_documento = '" . $Ch_Documento . "'";
				if ($sqlca->query($sql_delete_val_ta_detalle) < 0){
					$response = array(
						'status' => 'error',
						'message' => 'Problemas al eliminar Vale de Credito (Detail)',
					);
					return $response;
				}

				//Select a val_ta_complemento en updateVale
				$sql_val_ta_complemento = "
SELECT
 *
FROM
 val_ta_complemento 
WHERE
 ch_sucursal 		= '" . $Nu_Almacen . "'
 AND dt_fecha 		= '" . $Fe_Emision . "'
 AND ch_documento 	= '" . $Ch_Documento . "';
 				";
 				$iStatusSQL = $sqlca->query($sql_val_ta_complemento);
 				if ( (int)$iStatusSQL < 0 ){
					$response = array(
						'status' => 'error',
						'message' => 'Problemas al obtener datos Vale de Credito (Complement)',
					);
					return $response; 					
 				}
 				$row = $sqlca->fetchRow();
 				$arrComplemento = array(
 					'ch_sucursal' => $row['ch_sucursal'],
 					'dt_fecha' => $row['dt_fecha'],
 					'ch_documento' => $row['ch_documento'],
 					'nu_importe' => ($row['nu_importe'] > 0.00 ? $row['nu_importe'] : 0),
 					'ch_estado' => $row['ch_estado'],
 					'dt_fechaactualizacion' => $row['dt_fechaactualizacion'],
 					'ch_usuario' => $row['ch_usuario'],
 					'ch_auditorpc' => $row['ch_auditorpc'],
 					'flg_replicacion' => $row['flg_replicacion'],
 					'fecha_replicacion' => $row['fecha_replicacion'],
 					'ch_tipovale' => $row['ch_tipovale'],
 				);

				$sql_delete_val_ta_complemento = "DELETE FROM val_ta_complemento WHERE ch_sucursal = '" . $Nu_Almacen . "' AND dt_fecha = '" . $Fe_Emision . "' AND ch_documento = '" . $Ch_Documento . "'";
 				$iStatusSQL = $sqlca->query($sql_delete_val_ta_complemento);
 				if ( (int)$iStatusSQL < 0 ){
					$response = array(
						'status' => 'error',
						'message' => 'Problemas al eliminar Vale de Credito (Complement)',
					);
					return $response;
				}

				$sql_delete_val_ta_cabecera = "DELETE FROM val_ta_cabecera WHERE ch_sucursal = '" . $Nu_Almacen . "' AND dt_fecha = '" . $Fe_Emision . "' AND ch_documento = '" . $Ch_Documento . "'";
				if ($sqlca->query($sql_delete_val_ta_cabecera) < 0){
					$response = array(
						'status' => 'error',
						'message' => 'Problemas al eliminar Vale de Credito (Header)',
					);
					return $response;
				}

				if($response['status'] == 'error'){
					ValeCRUDModel::ROLLBACKTransacction();
					$response = array(
						'status' => 'error',
						'message' => 'Error al eliminar Vale Credito',
					);
					return $response;
				}else{
					//ValeCRUDModel::COMMITTransacction();
					// Paso 2 - Crear nuevamente el vale (Cabecera, Complemento y Detalle)
					//Insert into en val_ta_cabecera en updateVale
					$sql_val_ta_cabecera = "
		 			INSERT INTO val_ta_cabecera (
						ch_sucursal,
						dt_fecha,
						ch_documento,
						ch_cliente,
						ch_glosa,
						ch_placa,
						nu_odometro,
						ch_tarjeta,
						ch_estado,
						ch_caja,
						ch_lado,
						dt_fechaactualizacion,
						ch_usuario,
						ch_auditorpc,
						ch_turno,
						flg_replicacion,
						fecha_replicacion,
						nu_documento_identidad_chofer
		 			) VALUES (
		 				'" . $Nu_Almacen . "',
		 				'" . $Fe_Emision . "',
		 				'" . $Ch_Documento . "',
		 				'" . $Nu_Documento_Identidad . "',
		 				'VALE MANUAL AGREGADO POR: " . $usuario . "',--ch_glosa
		 				'" . $No_Placa . "',
		 				'" . $Nu_Odometro . "',
		 				'" . $Nu_Tarjeta . "',
						'1',--ch_estado
		 				'" . $Nu_Caja . "',
		 				'" . $Nu_Lado . "',
		 				NOW(),--dt_fechaactualizacion
		 				'" . $usuario . "',
		 				'" . $ip . "',--ch_auditorpc = la Ip de PC
		 				'" . $Nu_Turno . "',
						0,--flg_replicacion
						NOW(),--fecha_replicacion
						'" . $Nu_Documento_Identidad_Chofer . "'
		 			);
		 			";

					if ($sqlca->query($sql_val_ta_cabecera) < 0){
						$response = array(
							'status' => 'error',
					        'message_sql' => $sqlca->get_error(),
							'message' => 'Problemas al insertar Vale de crédito (Header)',
						);
						return $response;
					}



					$fTotal=0.00;
					foreach ($arrDetailCreditVoucher as $row) {
						$arrSQLDetail[] = "(
	 					'" . strip_tags(stripslashes($Nu_Almacen)) . "',
	 					'" . $Fe_Emision . "',
	 					'" . $Ch_Documento . "',
	 					'" . strip_tags(stripslashes($row['iIdItem'])) . "',
	 					'" . strip_tags(stripslashes($row['fCantidad'])) . "',
	 					'" . strip_tags(stripslashes($row['fPrecioVenta'])) . "',
	 					'" . strip_tags(stripslashes($row['fTotal'])) . "',
						'1',--ch_estado
		 				NOW(),--dt_fechaactualizacion
		 				'" . $usuario . "',
		 				'" . $ip . "',--ch_auditorpc = la Ip de PC
						0,--flg_replicacion
						NOW(),--fecha_replicacion
						0,--nu_precio_especial
						util_fn_igv())
						";
						$fTotal += $row['fTotal'];
					}

					$sql_val_ta_detalle = "
					INSERT INTO val_ta_detalle (
						ch_sucursal,
						dt_fecha,
						ch_documento,
						ch_articulo,
						nu_cantidad,
						nu_precio_unitario,
						nu_importe,
						ch_estado,
						dt_fechaactualizacion,
						ch_usuario,
						ch_auditorpc,
						flg_replicacion,
						fecha_replicacion,
						nu_precio_especial,
						nu_factor_igv
		 			) VALUES " . implode(',', $arrSQLDetail);

					if ($sqlca->query($sql_val_ta_detalle) < 0){
						$response = array(
							'status' => 'error',
					        'message_sql' => $sqlca->get_error(),
							'message' => 'Problemas al actualizar Vale de crédito (Detail)',
						);
						return $response;
					}					
					
					error_log("arrComplemento");
					error_log(json_encode($arrComplemento));

					$existDataArrayComplemento = false;
					foreach ($arrComplemento as $key => $value) {
						if( !empty($value) ){ //Si hay información en algun campo, entonces existe información
							$existDataArrayComplemento = true;
						}
					}

					if ( $existDataArrayComplemento ) { //Encontro información en val_ta_complemento
						
						$sql_val_ta_complemento = "
						INSERT INTO val_ta_complemento (
							ch_sucursal,
							dt_fecha,
							ch_documento,
							ch_numeval,
							nu_importe,
							ch_estado,
							dt_fechaactualizacion,
							ch_usuario,
							ch_auditorpc,
							flg_replicacion,
							fecha_replicacion,
							ch_tipovale 
						) VALUES (
							'" . $arrComplemento['ch_sucursal'] . "',
							'" . $arrComplemento['dt_fecha'] . "',
							'" . $arrComplemento['ch_documento'] . "',
							'" . $Ch_Documento_Manual . "',
							" . $arrComplemento['nu_importe'] . ",
							'" . $arrComplemento['ch_estado'] . "',
							'" . $arrComplemento['dt_fechaactualizacion'] . "',
							'" . $arrComplemento['ch_usuario'] . "',
							'" . $arrComplemento['ch_auditorpc'] . "',
							'" . $arrComplemento['flg_replicacion'] . "',
							'" . ($arrComplemento['fecha_replicacion'] != "" ? $arrComplemento['fecha_replicacion'] : 'now()') . "',
							'" . $arrComplemento['ch_tipovale'] . "'
						);
						";

					} else { //No encontro información en val_ta_complemento

						$sql_val_ta_complemento = "
						INSERT INTO val_ta_complemento (
							ch_sucursal,
							dt_fecha,
							ch_documento,
							ch_numeval,
							nu_importe,
							ch_estado,
							dt_fechaactualizacion,
							ch_usuario,
							ch_auditorpc,
							flg_replicacion,
							fecha_replicacion,
							ch_tipovale
						) VALUES (
							'" . $Nu_Almacen . "',
							'" . $Fe_Emision . "',
							'" . $Ch_Documento . "',
							'" . $Ch_Documento_Manual . "',
							" . $fTotal . ",
							'1',
							'NOW()',
							'',
							'" . $ip . "',
							0,
							NOW(),
							'00'
						);
						";

					}
					error_log("existDataArrayComplemento");
					error_log($existDataArrayComplemento);
					error_log("sql_val_ta_complemento");
					error_log($sql_val_ta_complemento);

					if ($sqlca->query($sql_val_ta_complemento) < 0){
						$response = array(
							'status' => 'error',
					        'message_sql' => $sqlca->get_error(),
							'message' => 'Problemas al insertar Vale de crédito (Complement)',
							'arrComplemento' => $arrComplemento,
						);
						return $response;
					}

					if($response['status'] == 'error'){
						ValeCRUDModel::ROLLBACKTransacction();
						$response = array(
							'status' => 'error',
							'message' => 'Problemas al actualizar Vale de Crédito',
						);
						return $response;
					}else{
						ValeCRUDModel::COMMITTransacction();
					}
				}// SQL - Eliminando vale de crédito
			}// /. Paso 1 - Eliminar vale para después crearlo
			return $response;
		}catch(Exception $e){
			throw $e;
		}
	}

	public function getOpensoftCentral() {
		global $sqlca;

		//OPENSOFT-82: Registro de vales en Opensoft Central		
		//Obtenemos parametro opensoftCentral de int_parametros
		$arrOpensoftCentral = array();
		$sql_opensoftCentral = "SELECT par_nombre, par_valor FROM int_parametros WHERE par_nombre = 'opensoftCentral';";
		$sqlca->query($sql_opensoftCentral);
		$arrOpensoftCentral = $sqlca->fetchRow();

		//Validamos si no existe parametro
		if( is_null($arrOpensoftCentral) || empty($arrOpensoftCentral) ){
			$arrOpensoftCentral['par_nombre'] = 'opensoftCentral';
			$arrOpensoftCentral['par_valor'] = 0;
		}
		
		//Retornamos dato opensoftCentral
		$response = array(
			'status' => 'success',
			'arrOpensoftCentral' => $arrOpensoftCentral,
		);			
		return $response;
	}

	public function editVale($data) {
		global $sqlca;

		//OPENSOFT-82: Registro de vales en Opensoft Central		
		//Obtenemos parametro opensoftCentral de int_parametros
		$arrOpensoftCentral = array();
		$sql_opensoftCentral = "SELECT par_nombre, par_valor FROM int_parametros WHERE par_nombre = 'opensoftCentral';";
		$sqlca->query($sql_opensoftCentral);
		$arrOpensoftCentral = $sqlca->fetchRow();

		//Validamos si no existe parametro
		if( is_null($arrOpensoftCentral) || empty($arrOpensoftCentral) ){
			$arrOpensoftCentral['par_nombre'] = 'opensoftCentral';
			$arrOpensoftCentral['par_valor'] = 0;
		}

		$arrValeCabecera = array();
		$arrValeDetalle = array();

		$Nu_Almacen = trim($data['Nu_Almacen']);
		$Nu_Almacen = strip_tags($Nu_Almacen);

		$Fe_Emision = trim($data['Fe_Emision']);
		$Fe_Emision = strip_tags($Fe_Emision);
		$Fe_Emision = explode("/", $Fe_Emision);

		$postrans = "pos_trans".$Fe_Emision[2].$Fe_Emision[1];

		$Fe_Emision = $Fe_Emision[2] . "-" . $Fe_Emision[1] . "-" . $Fe_Emision[0];

		$Ch_Documento = trim($data['Ch_Documento']);
		$Ch_Documento = strip_tags($Ch_Documento);

		$Nu_Turno = trim($data['Nu_Turno']);
		$Nu_Turno = strip_tags($Nu_Turno);

		$Nu_Lado = trim($data['Nu_Lado']);
		$Nu_Lado = strip_tags($Nu_Lado);

		$response = NULL;

		$sql_val_ta_cabecera = "
		SELECT
			ALMA.ch_nombre_almacen AS no_almacen,
			VC.*,
			trim(VC.ch_turno) AS nu_turno,
			trim(VC.ch_caja) AS nu_caja,
			trim(VC.ch_lado) AS nu_lado,
			PLACA.nomusu AS no_chofer,
			TO_CHAR(VC.dt_fecha, 'DD/MM/YYYY') AS fe_emision,
			VC.ch_cliente AS nu_documento_identidad,
			CLI.cli_razsocial AS no_razon_social,
			VCOM.ch_numeval AS no_documento_manual
		FROM
			val_ta_cabecera AS VC
			LEFT JOIN val_ta_complemento AS VCOM
			    USING(ch_sucursal, dt_fecha, ch_documento)
			JOIN inv_ta_almacenes AS ALMA
				ON(ALMA.ch_almacen = VC.ch_sucursal)
			JOIN int_clientes AS CLI
				ON(CLI.cli_codigo = VC.ch_cliente)
			LEFT JOIN pos_fptshe1 AS PLACA
				ON(VC.ch_cliente = PLACA.codcli AND VC.ch_tarjeta = PLACA.numtar AND VC.ch_placa = PLACA.numpla)
		WHERE
			VC.ch_sucursal = '" . $Nu_Almacen . "'
			AND VC.dt_fecha = '" . $Fe_Emision . "'
			AND VC.ch_documento = '" . $Ch_Documento . "'
		";

		if ($sqlca->query($sql_val_ta_cabecera) < 0){
			$response = array(
				'status' => 'error'
			);
		}

		$arrValeCabecera = $sqlca->fetchRow();

		$sqlca->query("SELECT validar_consolidacion('" . $Fe_Emision . "', '" . $Nu_Turno . "', '" . $Nu_Almacen . "')");
		$row_2 = $sqlca->fetchRow();

		if($row_2['validar_consolidacion'] == 1){
			$response = array(
				'status' => 'error',
				'message' => 'El dia y turno se encuentra consolidado'
			);
			return $response;
		} else {
			$sql_val_ta_detalle = "
SELECT
 VD.*,
 CASE WHEN VD.nu_importe > 0.00 AND VD.nu_cantidad > 0.00 THEN ROUND(VD.nu_importe / VD.nu_cantidad, 3) ELSE 0 END AS nu_precio_venta, --El precio del detalle se calcula
 PRO.art_descripcion AS no_producto
FROM
 val_ta_detalle AS VD
 JOIN int_articulos AS PRO
  ON(VD.ch_articulo = PRO.art_codigo)
WHERE
 VD.ch_sucursal='" . $Nu_Almacen . "'
 AND VD.dt_fecha='" . $Fe_Emision . "'
 AND VD.ch_documento='" . $Ch_Documento . "'
 			";

			if ($sqlca->query($sql_val_ta_detalle) < 0){
				$response = array(
					'status' => 'error',
					'message' => 'Problemas al obtener data - detalle',
					'sql' => $sql_val_ta_detalle,
				);
				return $response;
			}

			$arrValeDetalle = $sqlca->fetchAll();

			//Array TCL = Get Values: Turno, Caja, Lado
			$arrTurnos = array();
			$arrCajas = array();
			$arrLados = array();

			//OPENSOFT-82: Registro de vales en Opensoft Central		
			//Si no existe el parametro buscamos turnos, cajas y lados
			if( $arrOpensoftCentral['par_valor'] == 0 ){
				// Turnos
				$sql = "
				SELECT
					ch_posturno::INTEGER AS turno
				FROM
						pos_aprosys
				WHERE
						da_fecha = '" . $Fe_Emision . "';
				";

				if ($sqlca->query($sql) < 0)
					return false;

				$arrTurnos = $sqlca->fetchAll();
				error_log("SQL Turnos");
				error_log($sql);

				// Cajas
				$sql = "SELECT DISTINCT caja FROM " . $postrans . " WHERE dia = '" . $Fe_Emision . "' ORDER BY 1;";
				if ($sqlca->query($sql) < 0)
					return false;
				$arrCajas = $sqlca->fetchAll();
				error_log("SQL Cajas");
				error_log($sql);

				//Lados
				if($Nu_Lado != ""){
					$sql = "SELECT DISTINCT pump FROM " . $postrans . " WHERE dia = '".$Fe_Emision."' AND tipo = 'C' ORDER BY 1;";
					if ($sqlca->query($sql) < 0)
						return false;
					$arrLados = $sqlca->fetchAll();
				}
				error_log("SQL Lados");
				error_log($sql);
			}

			if($response['status'] == 'error'){
				$response = array(
					'status' => 'error',
					'message' => 'Problemas al obtener data',
					'query_vale_cabecera' => $sql_val_ta_cabecera,
					'query_vale_detalle' => $sql_delete_val_ta_detalle,
				);
			}else{
				$response = array(
					'status' => 'success',
					'arrValeCabecera' => $arrValeCabecera,
					'arrValeDetalle' => $arrValeDetalle,
					'arrTurnos' => $arrTurnos,
					'arrCajas' => $arrCajas,
					'arrLados' => $arrLados,
					'arrOpensoftCentral' => $arrOpensoftCentral,
				);			
			}
		}
		return $response;
	}

	public function deleteVale($data) {
		global $sqlca;

		ValeCRUDModel::BEGINTransacction();

		$response = array(
			'status' => 'success',
			'message' => 'Vale de crédito eliminado satisfactoriamente'
		);

		$Nu_Almacen = trim($data['Nu_Almacen']);
		$Nu_Almacen = strip_tags($Nu_Almacen);

		$Fe_Emision = trim($data['Fe_Emision']);
		$Fe_Emision = strip_tags($Fe_Emision);
		$Fe_Emision = explode("/", $Fe_Emision);
		$Fe_Emision = $Fe_Emision[2] . "-" . $Fe_Emision[1] . "-" . $Fe_Emision[0];

		$Ch_Documento = trim($data['Ch_Documento']);
		$Ch_Documento = strip_tags($Ch_Documento);

		$Nu_Turno = trim($data['Nu_Turno']);
		$Nu_Turno = strip_tags($Nu_Turno);

		$sqlca->query("
		SELECT
			*
		FROM
			val_ta_cabecera
		WHERE
			ch_sucursal 		= '" . $Nu_Almacen . "'
			AND dt_fecha 		= '" . $Fe_Emision . "'
			AND ch_documento 	= '" . $Ch_Documento . "';
		");
		$row = $sqlca->fetchRow();

		$sqlca->query("SELECT validar_consolidacion('" . $Fe_Emision . "', '" . $Nu_Turno . "', '" . $Nu_Almacen . "')");
		$row_2 = $sqlca->fetchRow();

		if(trim($row['ch_liquidacion']) == 'LIQ'){
			$response = array(
				'status' => 'error',
				'message' => 'Vale crédito se encuentra liquidado, no se puede eliminar'
			);
		}else if($row_2['validar_consolidacion'] == 1){
			$response = array(
				'status' => 'error',
				'message' => 'El dia y turno se encuentra consolidado'
			);
		} else {

			$sql_delete_val_ta_detalle = "DELETE FROM val_ta_detalle WHERE ch_sucursal = '" . $Nu_Almacen . "' AND dt_fecha = '" . $Fe_Emision . "' AND ch_documento = '" . $Ch_Documento . "'";

			if ($sqlca->query($sql_delete_val_ta_detalle) < 0){
				$response = array(
					'status' => 'error',
					'query' => $sql_delete_val_ta_detalle,
				);
			}

			$sql_delete_val_ta_complemento = "DELETE FROM val_ta_complemento WHERE ch_sucursal = '" . $Nu_Almacen . "' AND dt_fecha = '" . $Fe_Emision . "' AND ch_documento = '" . $Ch_Documento . "'";
			$sqlca->query($sql_delete_val_ta_complemento);

			$sql_delete_val_ta_cabecera = "DELETE FROM val_ta_cabecera WHERE ch_sucursal = '" . $Nu_Almacen . "' AND dt_fecha = '" . $Fe_Emision . "' AND ch_documento = '" . $Ch_Documento . "'";

			if ($sqlca->query($sql_delete_val_ta_cabecera) < 0){
				$response = array(
					'status' => 'error'
				);
			}

			if($response['status'] == 'error'){
				ValeCRUDModel::ROLLBACKTransacction();
				$response = array(
					'status' => 'error',
					'message' => 'Error al eliminar Vale Credito',
					'query' => $sql_delete_val_ta_detalle,
				);
			}else{
				ValeCRUDModel::COMMITTransacction();
			}
		}
		return $response;
	}
}