<?php

class AnularTickesModel extends Model {

    	function GetAlmacen() {
		global $sqlca;

		try {
			$sql = "
				SELECT
					ch_almacen as nualmacen,
					TRIM(ch_almacen) || ' - ' || TRIM(ch_nombre_almacen) as noalmacen
				FROM
					inv_ta_almacenes
				WHERE
					ch_clase_almacen = '1'
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


    function Consolidacion($dia,$almacen) {
		global $sqlca;

		$turno = 0;
		$sql = "SELECT validar_consolidacion('" . $dia . "', " . $turno . ",'" . $almacen . "');";
		$sqlca->query($sql);
		$estado = $sqlca->fetchRow();
		
		if($estado[0] == 1){
			return 1;//Consolidado
		}else{
			return 0;//No consolidado
		}
	}

	function getData($txtnualmacen, $caja, $trans, $fecha_trans, $td, $tv, $turno, $tm) { //Buscar Ticket
		global $sqlca;

		$txtnualmacen	= trim($txtnualmacen);
		$caja 			= trim($caja);
		$trans 			= trim($trans);
		$fecha_trans	= trim($fecha_trans);
		$td				= trim($td);
		$tv				= trim($tv);
		$turno			= trim($turno);

		$d = substr($fecha_trans,0,2);
		$m = substr($fecha_trans,3,2);
		$a = substr($fecha_trans,6,4);

		$fecha_trans = $a."-".$m."-".$d;

		$query = "SELECT ch_poscd FROM pos_aprosys WHERE da_fecha = '" . $fecha_trans . "' AND ch_posturno = '" . $turno . "';";
		$sqlca->query($query);
		$dato = $sqlca->fetchRow();

		if ($dato[0] == "A")
			$postrans = "pos_transtmp";
		else {
			$pos_trans 	= explode("-", $fecha_trans);
			$postrans	= "pos_trans".$pos_trans[0]."".$pos_trans[1];
		}

		$sql = "
		SELECT  
			tm,
	        caja,
	        (CASE
				WHEN td = 'N' THEN 'Nota Despacho'
				WHEN td = 'B' THEN 'Boleta'
				WHEN td = 'F' THEN 'Factura'
			ELSE
				'Afericion'
			END) AS td,
        	turno,
        	codigo,
        	cantidad,
        	precio,
        	igv,
        	importe,
        	ruc,
        	tipo,
        	pump,
        	TRIM(grupo) AS grupo,
       		(SELECT art_descripcion FROM int_articulos WHERE art_codigo = codigo LIMIT 1) AS producto,
			tarjeta,
			placa,
			cuenta,
			ROUND(importe - igv, 2) AS bi,
			DATE(dia) AS dia,
			DATE(fecha) AS fecha,
			usr
        FROM
			" . $postrans . "
		WHERE
			caja		= '" . $caja . "'
			AND es		= '" . $txtnualmacen . "'
			AND trans	= '" . $trans . "'
			AND dia		= '" . $fecha_trans . "'
			AND td 		= '" . $td . "'
			AND tipo 	= '" . $tv . "'
			AND turno 	= '" . $turno . "'
			AND tm		= '" . $tm . "'
		ORDER BY
			fecha_replicacion ASC;
		";

		if ($sqlca->query($sql) < 0)
			return false;

		$result = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$result[] = $a;
		}
		return $result;
	}

	function Anular_tickes($almacen, $caja, $trans, $fecha_trans, $td, $tv, $turno, $tm, $codigo, $i) { //Anular Tickets
		global $sqlca;


		$ip = "";

		if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"),"unknown"))
			$ip = getenv("HTTP_CLIENT_IP");
		else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
			$ip = getenv("HTTP_X_FORWARDED_FOR");
		else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
			$ip = getenv("REMOTE_ADDR");
		else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
			$ip = $_SERVER['REMOTE_ADDR'];

		$usuario = $_SESSION['auth_usuario'];

		//error_log('paso1: ');
		/* DATOS PARA POS_TRANS */
		$fecha_trans = trim($fecha_trans);

		$d = substr($fecha_trans,0,2);
		$m = substr($fecha_trans,3,2);
		$a = substr($fecha_trans,6,4);

		$fecha_trans = $a."-".$m."-".$d;

		$query = "SELECT ch_poscd FROM pos_aprosys WHERE da_fecha = '" . $fecha_trans . "' AND ch_posturno = '" . $turno . "';";
		$sqlca->query($query);
		$dato = $sqlca->fetchRow();

		if ($dato[0] == "A")
			$postrans = "pos_transtmp";
		else {
			$pos_trans 	= explode("-", $fecha_trans);
			$postrans	= "pos_trans" . $pos_trans[0] . $pos_trans[1];
		}

		/* ACTUALIZAR KARDEX y DOCUMENTO VENTA TIPO = 45 */
		if($tv == "M"){
			$type_product = "SELECT art_plutipo FROM int_articulos WHERE art_codigo = '" . $codigo . "';";
			$sqlca->query($type_product);
			$a = $sqlca->fetchRow();
			$tipo = $a['art_plutipo'];//Para saber si es STANDAR O PLU SALIENTE

			//$tipo = 1 THEN STANDAR
			//$tipo = 2 THEN PLU SALIENTE

			//verificar tipo de operacion
			if($tm == "D"){// D = "Devolucion"
				$cond = "mov_cantidad = mov_cantidad + (SELECT cantidad FROM $postrans WHERE trans = " . $trans . " AND td = '" . $td . "' AND caja = '" . $caja . "' AND dia = '" . $fecha_trans  . "' AND codigo = '" . $codigo . "')";

				$condfd = "
				nu_fac_cantidad 	= nu_fac_cantidad + (SELECT cantidad FROM " . $postrans . " WHERE trans = " . $trans . " AND td = '" . $td . "' AND caja = '" . $caja . "' AND dia = '" . $fecha_trans . "' AND codigo = '" . $codigo . "'),
				nu_fac_importeneto	= ROUND((nu_fac_precio * (nu_fac_cantidad - (SELECT cantidad FROM " . $postrans . " WHERE trans = " . $trans . " AND td = '" . $td . "' AND caja = '" . $caja . "' AND dia = '" . $fecha_trans . "' AND codigo = '" . $codigo . "'))), 2),
				nu_fac_impuesto1	= ROUND(((nu_fac_precio * (1 + (util_fn_igv()/100))) * (nu_fac_cantidad - (SELECT cantidad FROM " . $postrans . " WHERE trans = " . $trans . " AND td = '" . $td . "' AND caja = '" . $caja . "' AND dia = '" . $fecha_trans . "' AND codigo = '" . $codigo . "' ))) - ((nu_fac_precio * (nu_fac_cantidad - (SELECT cantidad FROM $postrans WHERE trans=$trans AND td = '" . $td . "' AND caja = '" . $caja . "' AND dia = '" . $fecha_trans . "' AND codigo = '" . $codigo . "'))), 2),
				nu_fac_valortotal	= ROUND(((nu_fac_precio * (1 + (util_fn_igv()/100))) * (nu_fac_cantidad - (SELECT cantidad FROM " . $postrans . " WHERE trans = " . $trans . " AND td = '" . $td . "' AND caja = '" . $caja . "' AND dia = '" . $fecha_trans . "' AND codigo = '" . $codigo . "' ))), 2)
				";

				$condfc = "
				nu_fac_valorbruto	= (
				SELECT
					ROUND(SUM(nu_fac_importeneto), 2)
				FROM
					fac_ta_factura_detalle
				WHERE
					ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo IN (
					SELECT
						ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo
					FROM
						fac_ta_factura_detalle
					WHERE
						ch_fac_tipodocumento 	= '45'
						AND art_codigo 			= '" . $codigo . "'
						AND ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo IN (SELECT ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo FROM fac_ta_factura_cabecera WHERE ch_fac_tipodocumento = '45' AND dt_fac_fecha = '" . $fecha_trans . "')
					)
				),
				nu_fac_impuesto1	= (
				SELECT
					ROUND(SUM(nu_fac_impuesto1), 2)
				FROM
					fac_ta_factura_detalle
				WHERE
					ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo IN (
					SELECT
						ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo
					FROM
						fac_ta_factura_detalle
					WHERE
						ch_fac_tipodocumento 	= '45'
						AND art_codigo 			= '" . $codigo . "'
						AND ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo IN (SELECT ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo FROM fac_ta_factura_cabecera WHERE ch_fac_tipodocumento = '45' AND dt_fac_fecha = '" . $fecha_trans . "')
					)
				),
				nu_fac_valortotal	= (
				SELECT
					ROUND(SUM(nu_fac_valortotal), 2)
				FROM
					fac_ta_factura_detalle
				WHERE
					ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo IN (
					SELECT
						ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo
					FROM
						fac_ta_factura_detalle
					WHERE
						ch_fac_tipodocumento 	= '45'
						AND art_codigo 			= '" . $codigo . "'
						AND ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo IN (SELECT ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo FROM fac_ta_factura_cabecera WHERE ch_fac_tipodocumento = '45' AND dt_fac_fecha = '" . $fecha_trans . "')
					)
				)
				";

			}else{
				$cond = "mov_cantidad = mov_cantidad - (SELECT cantidad FROM $postrans WHERE trans=$trans AND td = '" . $td . "' AND caja='" . $caja . "' AND dia='" . $fecha_trans . "' AND codigo='" . $codigo . "')";

				$condfd = "
				nu_fac_cantidad 	= nu_fac_cantidad - (SELECT cantidad FROM " . $postrans . " WHERE trans = " . $trans . " AND td = '" . $td . "' AND caja = '" . $caja . "' AND dia = '" . $fecha_trans . "' AND codigo = '" . $codigo . "'),
				nu_fac_importeneto	= ROUND((nu_fac_precio * (nu_fac_cantidad - (SELECT cantidad FROM " . $postrans . " WHERE trans = " . $trans . " AND td = '" . $td . "' AND caja = '" . $caja . "' AND dia = '" . $fecha_trans . "' AND codigo = '" . $codigo . "' ))), 2),
				nu_fac_impuesto1	= ROUND(((nu_fac_precio * (1 + (util_fn_igv()/100))) * (nu_fac_cantidad - (SELECT cantidad FROM " . $postrans . " WHERE trans = " . $trans . " AND td = '" . $td . "' AND caja = '" . $caja . "' AND dia = '" . $fecha_trans . "' AND codigo = '" . $codigo . "' ))), 2) - ROUND((nu_fac_precio * (nu_fac_cantidad - (SELECT cantidad FROM $postrans WHERE trans=$trans AND td = '" . $td . "' AND caja = '" . $caja . "' AND dia = '" . $fecha_trans . "' AND codigo = '" . $codigo . "'))), 2),
				nu_fac_valortotal	= ROUND(((nu_fac_precio * (1 + (util_fn_igv()/100))) * (nu_fac_cantidad - (SELECT cantidad FROM " . $postrans . " WHERE trans = " . $trans . " AND td = '" . $td . "' AND caja = '" . $caja . "' AND dia = '" . $fecha_trans . "' AND codigo = '" . $codigo . "' ))), 2)
				";

				$condfc = "
				nu_fac_valorbruto	= (
				SELECT
					ROUND(SUM(nu_fac_importeneto), 2)
				FROM
					fac_ta_factura_detalle
				WHERE
					ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo IN (
					SELECT
						ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo
					FROM
						fac_ta_factura_detalle
					WHERE
						ch_fac_tipodocumento 	= '45'
						AND art_codigo 			= '" . $codigo . "'
						AND ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo IN (SELECT ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo FROM fac_ta_factura_cabecera WHERE ch_fac_tipodocumento = '45' AND dt_fac_fecha = '" . $fecha_trans . "')
					)
				),
				nu_fac_impuesto1	= (
				SELECT
					ROUND(SUM(nu_fac_impuesto1), 2)
				FROM
					fac_ta_factura_detalle
				WHERE
					ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo IN (
					SELECT
						ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo
					FROM
						fac_ta_factura_detalle
					WHERE
						ch_fac_tipodocumento 	= '45'
						AND art_codigo 			= '" . $codigo . "'
						AND ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo IN (SELECT ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo FROM fac_ta_factura_cabecera WHERE ch_fac_tipodocumento = '45' AND dt_fac_fecha = '" . $fecha_trans . "')
					)
				),
				nu_fac_valortotal	= (
				SELECT
					ROUND(SUM(nu_fac_valortotal), 2)
				FROM
					fac_ta_factura_detalle
				WHERE
					ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo IN (
					SELECT
						ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo
					FROM
						fac_ta_factura_detalle
					WHERE
						ch_fac_tipodocumento 	= '45'
						AND art_codigo 			= '" . $codigo . "'
						AND ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo IN (SELECT ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo FROM fac_ta_factura_cabecera WHERE ch_fac_tipodocumento = '45' AND dt_fac_fecha = '" . $fecha_trans . "')
					)
				)
				";

			}
			//FIN DE VERIFICAR TIPO DE OPERACION
			if ($tipo == 1){// 1 = STANDAR
				$sql = "
				UPDATE
					inv_movialma
				SET
					" . $cond . "
				WHERE
					mov_fecha::DATE = '" . $fecha_trans . "'
					AND tran_codigo = '45'
					AND mov_almacen = (SELECT es FROM " . $postrans . " WHERE trans = " . $trans . " AND td = '" . $td . "' AND caja = '" . $caja . "' AND dia = '" . $fecha_trans . "' AND codigo = '" . $codigo . "' LIMIT 1)
					AND art_codigo IN (SELECT codigo FROM " . $postrans . " WHERE trans = " . $trans . " AND td = '" . $td . "' AND caja = '" . $caja . "' AND dia = '" . $fecha_trans . "' AND codigo = '" . $codigo . "');
				";
				$sqlca->query($sql);

				$sqlfd = "
				UPDATE
					fac_ta_factura_detalle
				SET
					" . $condfd . "
				WHERE
					ch_fac_tipodocumento 	= '45'
					AND art_codigo 			= '" . $codigo . "'
					AND ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo IN (SELECT ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo FROM fac_ta_factura_cabecera WHERE ch_fac_tipodocumento = '45' AND dt_fac_fecha = '" . $fecha_trans . "')
				";
				$sqlca->query($sqlfd);

				$sqlfc = "
				UPDATE
					fac_ta_factura_cabecera
				SET
					" . $condfc . "
				WHERE
					ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo IN (
					SELECT
						ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo
					FROM
						fac_ta_factura_detalle
					WHERE
						ch_fac_tipodocumento 	= '45'
						AND art_codigo 			= '" . $codigo . "'
						AND ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo IN (SELECT ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo FROM fac_ta_factura_cabecera WHERE ch_fac_tipodocumento = '45' AND dt_fac_fecha = '" . $fecha_trans . "')
					);
				";
				$sqlca->query($sqlfc);
			} else { // 2 = PLU SALIENTE
				$sql = "
				UPDATE
					inv_movialma
				SET
					" . $cond . "
				WHERE
					mov_fecha::DATE = '" . $fecha_trans . "'
					AND tran_codigo = '45'
					AND mov_almacen = (SELECT es FROM " . $postrans . " WHERE trans = " . $trans . " AND td = '" . $td . "' AND caja='" . $caja . "' AND dia='" . $fecha_trans . "' LIMIT 1)
					AND art_codigo IN (SELECT ch_item_estandar FROM int_ta_enlace_items WHERE art_codigo = '" . $codigo . "');
				";
				$sqlca->query($sql);

				$sqlfd = "
				UPDATE
					fac_ta_factura_detalle
				SET
					" . $condfd . "
				WHERE
					ch_fac_tipodocumento 	= '45'
					AND art_codigo 			= '" . $codigo . "'
					AND ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo IN (SELECT ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo FROM fac_ta_factura_cabecera WHERE ch_fac_tipodocumento = '45' AND dt_fac_fecha = '" . $fecha_trans . "')
				";
				$sqlca->query($sqlfd);

				$sqlfc = "
				UPDATE
					fac_ta_factura_cabecera
				SET
					" . $condfc . "
				WHERE
					ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo IN (
					SELECT
						ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo
					FROM
						fac_ta_factura_detalle
					WHERE
						ch_fac_tipodocumento 	= '45'
						AND art_codigo 			= '" . $codigo . "'
						AND ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo IN (SELECT ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo FROM fac_ta_factura_cabecera WHERE ch_fac_tipodocumento = '45' AND dt_fac_fecha = '" . $fecha_trans . "')
					);
				";
				$sqlca->query($sqlfc);
			}
		}/* FIN DE MARKET */

		//error_log('paso2');

		/* ACTUALIZAR POS_TRANSYYYYMM */ /* Actualizar pos_trans */

		$query="
		UPDATE
			" . $postrans . "
		SET
			cantidad	= 0,
			precio		= 0,
			igv			= 0,
			importe		= 0,
			soles_km	= 0
        WHERE
			trans		= " . $trans . "
			AND caja	= '" . $caja . "'
			AND dia		= '" . $fecha_trans . "'
			AND td 		= '" . $td . "'
			AND tipo	= '" . $tv . "'
			AND tm		= '" . $tm . "'
			AND turno	= '" . $turno . "'
			AND codigo	= '" . $codigo . "';
		";

		//error_log('holi: ' . $query);

		//echo $query;

		if($td == "N"){
			$detalle = "
			UPDATE
				val_ta_detalle
			SET
				nu_importe 	= 0,
				nu_cantidad = 0,
				ch_usuario = '" . $usuario . "',
				ch_auditorpc = '" . $ip . "',
				dt_fechaactualizacion = now()
			WHERE
				(ch_documento = '" . $trans . "' OR ch_documento = '" . $caja . "-" . $trans . "')
				AND dt_fecha = '" . $fecha_trans . "';
			";
			$sqlca->query($detalle);

			$complemento = "
			UPDATE
				val_ta_complemento
			SET
				nu_importe = 0
			WHERE
				(ch_documento = '" . $trans . "' OR ch_documento = '" . $caja . "-" . $trans . "')
				AND dt_fecha = '" . $fecha_trans . "';
			";
			$sqlca->query($complemento);

			$cabecera = "
			UPDATE
				val_ta_cabecera
			SET
				nu_importe = 0,
				ch_usuario = '" . $usuario . "',
				ch_auditorpc = '" . $ip . "',
				dt_fechaactualizacion = now()
			WHERE
				(ch_documento = '" . $trans . "' OR ch_documento = '" . $caja . "-" . $trans . "')
				AND dt_fecha 	= '" . $fecha_trans . "'
				AND ch_caja		= '" . $caja . "'
				AND ch_turno 	= '" . $turno . "'
				AND ch_lado		= '" . $lado . "';
			";
			$sqlca->query($cabecera);
		} else if($td == "A") {
			$afericion = "
			UPDATE
				pos_ta_afericiones
			SET
				cantidad	= 0,
				precio		= 0,
				igv			= 0,
				importe		= 0
			WHERE
				caja		= '" . $caja . "'
				AND trans	= '" . $trans . "'
				AND turno	= '" . $turno . "'
				AND dia		= '" . $fecha_trans . "';
			";
			$sqlca->query($afericion);
		}

		$feanular = "
		SELECT
			to_char(fecha,'YYYY-MM-DD')||'|'||
			(CASE
				WHEN tm = 'V' AND td = 'F' THEN '01'
				WHEN tm = 'V' AND td = 'B' THEN '03'
				WHEN tm = 'D' THEN '07'
				WHEN tm = 'A' THEN '07'
			END)
			||'|'||SUBSTR(TRIM(usr), 0, 5)||'|'||SUBSTR(TRIM(usr), 6)
		FROM
			" . $postrans . "
		WHERE
			caja		= '" . $caja . "'
			AND trans	= " . $trans . "
			AND dia		= '" . $fecha_trans . "'
			AND td 		= '" . $td . "'
			AND tipo	= '" . $tv . "'
			AND tm		= '" . $tm . "'
			AND turno	= '" . $turno . "'
			AND codigo	= '" . trim($codigo) . "'
			AND usr    != ''
			AND grupo  != 'D';
		";

		if ($sqlca->query($feanular) > 0) {
			$datofe = $sqlca->fetchRow();
			if ($i==0){
			   	$contentanular = "
				INSERT INTO ebi_queue(
					_id,
					created,
					taxid,
					optype,
					status,
					callback,
					content
				)VALUES(
					nextval('seq_ebi_queue_id'),
					now(),
					(SELECT ruc FROM int_ta_sucursales WHERE ch_sucursal = (SELECT ch_sucursal FROM inv_ta_almacenes WHERE ch_almacen = '" . trim($almacen) . "')),
					1,
					0,
					NULL,
					'$datofe[0]'
				);
			   	";
			   	$sqlca->query($contentanular);
			}
		}
		error_log("Query anular ticket");
		error_log( json_encode( array( $contentanular ) ) );

		if ($sqlca->query($query) <= 0) {
			return false;
		}
	}

	function getData2($almacen, $caja, $trans, $fecha_trans, $td, $tv, $turno, $tm, $codigo) {
		global $sqlca;

		$fecha_trans	= trim($fecha_trans);

		$d = substr($fecha_trans,0,2);
		$m = substr($fecha_trans,3,2);
		$a = substr($fecha_trans,6,4);

		$fecha_trans = $a."-".$m."-".$d;

		$query = "SELECT ch_poscd FROM pos_aprosys WHERE da_fecha = '$fecha_trans' AND ch_posturno='$turno';";

		$sqlca->query($query);

		$dato = $sqlca->fetchRow();

		if ($dato[0] == "A")
			$postrans = "pos_transtmp";
		else {
			$pos_trans 	= explode("-", $fecha_trans);
			$postrans	= "pos_trans".$pos_trans[0]."".$pos_trans[1];
		}

		$sql = "
		SELECT 
			to_char(fecha,'YYYY-MM-DD')||'|'||
			(CASE
				WHEN tm = 'V' AND td = 'F' THEN '01'
				WHEN tm = 'V' AND td = 'B' THEN '03'
				WHEN tm = 'D' THEN '07'
				WHEN tm = 'A' THEN '07'
			END)
			||'|'||SUBSTR(TRIM(usr), 0, 5)||'|'||SUBSTR(TRIM(usr), 6)
		FROM
			" . $postrans . "
		WHERE
			caja		= '" . $caja . "'
			AND trans	= " . $trans . "
			AND dia		= '" . $fecha_trans . "'
			AND td 		= '" . $td . "'
			AND tipo	= '" . $tv . "'
			AND tm		= '" . $tm . "'
			AND turno	= '" . $turno . "'
			AND codigo	= '" . trim($codigo) . "'
			AND usr    != ''
			AND grupo  != 'D';
		";

		if ($sqlca->query($sql) > 0){	
			$datofe = $sqlca->fetchRow();
			$buscar = "
			SELECT
				_id 
			FROM 
				ebi_queue
			WHERE 		
				content = '$datofe[0]';
		   	";

			if ($sqlca->query($buscar) < 0)
				return false;

			$result = Array();

			for ($i = 0; $i < $sqlca->numrows(); $i++) {
				$a = $sqlca->fetchRow();
				$result[] = $a;
			}
        }
        
		return $result;
	}

    
	function Insertar_tickes_Anulado($trans, $caja, $fecha ,$importe) {
        global $sqlca;

		if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"),"unknown"))
			$ip = getenv("HTTP_CLIENT_IP");
		else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
			$ip = getenv("HTTP_X_FORWARDED_FOR");
		else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
			$ip = getenv("REMOTE_ADDR");
		else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
			$ip = $_SERVER['REMOTE_ADDR'];

		$usuario = $_SESSION['auth_usuario'];
		
		$fecha_trans	= trim($fecha);

		$d = substr($fecha_trans,0,2);
		$m = substr($fecha_trans,3,2);
		$a = substr($fecha_trans,6,4);

		$fecha_trans = $a."-".$m."-".$d;

		$query	= "
		INSERT INTO tickes_anulados(
			numero_tickes,
			caja,
			fecha_tickes,
			fecha_anulacion,
			importe,
			usuario,
			ip
		)VALUES(
			" . $trans . ",
			" . $caja . ",
			'" . $fecha_trans . "',
			now(),
			" . $importe . ",
			'" . $usuario . "',
			'" . $ip . "'
		);
		";
      
		//error_log('holix2: ' . $query);


		if ($sqlca->query($query) <= 0) {
			return false;
		}
	}
    
	function fecha_aprosys() {
        	global $sqlca;

		$query = " SELECT da_fecha FROM pos_aprosys ORDER BY da_fecha DESC limit 1; ";

		if ($sqlca->query($query) <= 0) {
			return false;
		}

		$resultado = array();
		$a = $sqlca->fetchRow();
		$resultado['da_fecha'] = $a[0];

		return $resultado['da_fecha'];

	}

	function ObtenerFechaDTurno($fecha) {
		global $sqlca;

		$d = substr($fecha,0,2);
		$m = substr($fecha,3,2);
		$a = substr($fecha,6,4);

		$fecha = $a."-".$m."-".$d;

		try {

			$registro = array();
			$cerrado = "";

			$query = "
					SELECT
						ch_poscd				
				       	FROM
					    	pos_aprosys
					WHERE
					    	da_fecha='$fecha';
			";

			if ($sqlca->query($query) < 0)
			    return false;

			$a = $sqlca->fetchRow();
			$ch_poscd = $a['ch_poscd'];

			if ($ch_poscd == 'S')
				$cerrado = '- 1';

			$sql = "
				SELECT
					ch_posturno::INTEGER $cerrado AS turno
				FROM
				   	pos_aprosys
				WHERE
				   	da_fecha='$fecha';
				";

			if($sqlca->query($sql) <= 0){
				throw new Exception("Error");
			}

			while($reg = $sqlca->fetchRow()){
			        $registro[] = $reg;
			}

			return $registro;

		}catch(Exception $e){
			throw $e;
		}

	}

	function ObtenerCajas($almacen) {
		global $sqlca;

		try {

			$registro = array();

			$sql = "
				SELECT
					name
				FROM
					s_pos
				WHERE
					warehouse = '$almacen'
				ORDER BY
					name
				";

			if($sqlca->query($sql) <= 0){
				throw new Exception("Error");
			}

			while($reg = $sqlca->fetchRow()){
			        $registro[] = $reg;
			}

			return $registro;

		}catch(Exception $e){
			throw $e;
		}

	}


	function BEGINTransaccion() {
        global $sqlca;
        try {

            $sql = "BEGIN";

            if ($sqlca->query($sql) < 0) {
                throw new Exception("No se pudo INICIAR la TRANSACION");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    function COMMITransaccion() {
        global $sqlca;
        try {

            $sql = "COMMIT";

            if ($sqlca->query($sql) < 0) {
                throw new Exception("No se pudo procesar la TRANSACION");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    function ROLLBACKTransaccion() {
        global $sqlca;
        try {

            $sql = "ROLLBACK";

            if ($sqlca->query($sql) < 0) {
                throw new Exception("No se pudo Retroceder el proceso.");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

}

