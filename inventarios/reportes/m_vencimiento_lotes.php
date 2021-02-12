<?php
class VencimientoLotesModel extends Model {

	function getAlmacenes() {
		global $sqlca;

		$sql = "
		SELECT
		    ch_almacen,
		    ch_almacen||' - '||ch_nombre_almacen
		FROM
		    inv_ta_almacenes
		WHERE
		    ch_clase_almacen = '1'
		ORDER BY
		    ch_almacen;
		";
	
		if ($sqlca->query($sql) < 0) 
			return false;
	
		$result = Array();
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
	    	$a = $sqlca->fetchRow();
	    	$result[$a[0]] = $a[1];
		}
		return $result;
    }

	function obtieneNombreEstacion($almacen) {
		global $sqlca;
	
		$sql = "SELECT 
				ch_almacen, 
				trim(ch_nombre_almacen)
			FROM 
				inv_ta_almacenes
			WHERE 
				ch_clase_almacen='1'
				AND ch_almacen='$almacen';";

		if ($sqlca->query($sql) < 0) 
			return false;	

		$a = $sqlca->fetchRow();
		$nombre = $a[0]." - ".$a[1];

		return $nombre;
	}

    function getFechaSistemaPA() {
		global $sqlca;
		$sqlca->query("SELECT TO_CHAR(da_fecha, 'DD/MM/YYYY') AS fe_sistema FROM pos_aprosys WHERE ch_poscd = 'A' ORDER BY da_fecha DESC LIMIT 1;");
		$row = $sqlca->fetchRow();
		return $row['fe_sistema'];
    }

	function getLoteVencimiento($nu_almacen, $fe_inicial, $fe_final, $sTipoOrdenFVencimiento, $sTipoOrdenFEmision, $nu_estado) {
		global $sqlca;

		$arrResult = array();
		$condEstado = '';
		$OrderByFecha = ($sTipoOrdenFVencimiento == 'checked' ? 'pv.fe_vencimiento ASC' : 'pv.fe_emision ASC');

		if($nu_estado == '1')
			$condEstado = "AND pv.nu_estado = '1'";
		elseif($nu_estado=='2')
			$condEstado = "AND pv.nu_estado = '2'";
		elseif($nu_estado=='3')
			$condEstado = "AND pv.nu_estado = '3'";

		$status = $sqlca->query("
		SELECT
			ALMA.ch_almacen || ' ' || ALMA.ch_nombre_almacen AS no_almacen,
			pv.id_almacen AS nu_almacen,
			pv.no_lote AS no_lote,
			TO_CHAR(pv.fe_emision, 'DD/MM/YYYY HH24:mi:ss') AS fe_emision,
			TO_CHAR(pv.fe_vencimiento, 'DD/MM/YYYY') AS fe_vencimiento,
			prov.pro_razsocial AS no_proveedor,
			cpag.pro_cab_seriedocumento||'-'||cpag.pro_cab_numdocumento AS nu_documento,
			art.art_descbreve AS no_producto,
			dev.mov_cantidad AS ss_cantidad,
			(CASE
				WHEN pv.nu_estado = '1' THEN 'CON STOCK'
				WHEN pv.nu_estado = '2' THEN 'VENDIDO'
			ELSE
				'CADUCADO'
			END) no_estado,
			pv.nu_estado,
			pv.id_nu_formulario,
			pv.id_no_producto,
			pv.id_lote
		FROM
			inv_pedido_vencimiento AS pv
			JOIN inv_ta_almacenes AS ALMA ON(pv.id_almacen = ALMA.ch_almacen)
			LEFT JOIN inv_movialma AS mov ON (mov.mov_numero=pv.id_nu_formulario AND mov.art_codigo=pv.id_no_producto)
			LEFT JOIN inv_ta_compras_devoluciones AS dev ON (dev.mov_numero=mov.mov_numero AND dev.art_codigo=mov.art_codigo)
			LEFT JOIN cpag_ta_cabecera AS cpag ON (cpag.pro_cab_tipdocumento=dev.cpag_tipo_pago AND cpag.pro_cab_seriedocumento=dev.cpag_serie_pago AND cpag.pro_cab_numdocumento=dev.cpag_num_pago)
			LEFT JOIN int_proveedores AS prov ON (prov.pro_codigo=cpag.pro_codigo)
			LEFT JOIN int_articulos AS art ON (art.art_codigo=pv.id_no_producto)
		WHERE
			to_char(pv.fe_emision,'YYYY-MM-DD') BETWEEN '" . $fe_inicial . "' AND '" . $fe_final . "'
			AND pv.id_almacen = '" . $nu_almacen . "'
			" . $condEstado . "
		ORDER BY
			" . $OrderByFecha . "
		");

		$arrResult['estado'] = FALSE;
		$arrResult['result'] = '';
	    $arrResult['cantidad_registros'] = 0;

		if($status < 0)
			$arrResult['mensaje'] = 'Error SQL - function getLoteVencimiento';
		else if($status == 0){
			$arrResult['mensaje'] = 'No se encontró ningún registro';
		} else{
			$arrResult['estado'] = TRUE;
			$arrResult['result'] = $sqlca->fetchAll();
			$arrResult['cantidad_registros'] = $status;//status = Tambien contiene la cantidad de registros
		}
		return $arrResult;
    }

	function modificarEstado($formulario, $articulo, $lote, $estado, $usuario, $ip) {
		global $sqlca;

		
			if($estado == '1') {
				$std = "1";
			}elseif($estado == '2') {
				$std = "2";
			}else {
				$std = "3";
			}

				$sql = "UPDATE 	
							inv_pedido_vencimiento
						SET 	
							nu_estado='".$std."', 
							no_usuario='".trim($usuario)."',
							ip_usuario='".trim($ip)."', 
							fe_actualizacion = now()
						WHERE 	
							id_nu_formulario='".trim($formulario)."'
							AND id_no_producto='".trim($articulo)."'
							AND id_lote=".trim($lote).";";
				//echo $sql;

				if ($sqlca->query($sql) < 0){
					return 2;
				}else {
					return 1;
				}
	} 


	function Editar($id_formulario, $id_producto, $id_lote, $lote, $vencimiento, $usuario, $ip) {
		global $sqlca;

				$sql = "UPDATE
							inv_pedido_vencimiento
						SET
							fe_vencimiento='".trim($vencimiento)."',
							no_lote='".trim($lote)."',
							no_usuario='".trim($usuario)."',
							ip_usuario='".trim($ip)."',
							fe_actualizacion = now()
						WHERE
							id_nu_formulario='".trim($id_formulario)."'
							AND id_no_producto='".trim($id_producto)."'
							AND id_lote=".trim($id_lote).";";
				//echo $sql;

				if ($sqlca->query($sql) < 0){
					return 2;
				}else {
					return 1;
				}
	} 

	function obtenerFila($formulario, $articulo, $lote) {
		global $sqlca;

		$arrResult = array();

		$status = $sqlca->query("
		SELECT
			ALMA.ch_almacen || ' ' || ALMA.ch_nombre_almacen AS no_almacen,
			pv.id_almacen AS nu_almacen,
			pv.no_lote AS no_lote,
			TO_CHAR(pv.fe_emision, 'DD/MM/YYYY HH24:mi:ss') AS fe_emision,
			TO_CHAR(pv.fe_vencimiento, 'DD/MM/YYYY') AS fe_vencimiento,
			prov.pro_razsocial AS no_proveedor,
			cpag.pro_cab_seriedocumento||'-'||cpag.pro_cab_numdocumento AS nu_documento,
			art.art_descbreve AS no_producto,
			dev.mov_cantidad AS ss_cantidad,
			(CASE
				WHEN pv.nu_estado = '1' THEN 'CON STOCK'
				WHEN pv.nu_estado = '2' THEN 'VENDIDO'
			ELSE
				'CADUCADO'
			END) no_estado,
			pv.nu_estado,
			pv.id_nu_formulario,
			pv.id_no_producto,
			pv.id_lote
		FROM
			inv_pedido_vencimiento AS pv
			JOIN inv_ta_almacenes AS ALMA ON(pv.id_almacen = ALMA.ch_almacen)
			LEFT JOIN inv_movialma AS mov ON (mov.mov_numero=pv.id_nu_formulario AND mov.art_codigo=pv.id_no_producto)
			LEFT JOIN inv_ta_compras_devoluciones AS dev ON (dev.mov_numero=mov.mov_numero AND dev.art_codigo=mov.art_codigo)
			LEFT JOIN cpag_ta_cabecera AS cpag ON (cpag.pro_cab_tipdocumento=dev.cpag_tipo_pago AND cpag.pro_cab_seriedocumento=dev.cpag_serie_pago AND cpag.pro_cab_numdocumento=dev.cpag_num_pago)
			LEFT JOIN int_proveedores AS prov ON (prov.pro_codigo=cpag.pro_codigo)
			LEFT JOIN int_articulos AS art ON (art.art_codigo=pv.id_no_producto)
		WHERE
			pv.id_nu_formulario 	= '" . pg_escape_string($formulario) . "'
			AND pv.id_no_producto 	= '" . pg_escape_string($articulo) . "'
			AND pv.id_lote 			= " . pg_escape_string($lote) . "
		LIMIT 1;
		");

		$arrResult['estado'] = FALSE;
		$arrResult['result'] = '';
	    $arrResult['cantidad_registros'] = 0;

		if($status < 0)
			$arrResult['mensaje'] = 'Error SQL - function getLoteVencimiento';
		else if($status == 0){
			$arrResult['mensaje'] = 'No se encontró ningún registro';
		} else{
			$arrResult['estado'] = TRUE;
			$arrResult['result'] = $sqlca->fetchAll();
			$arrResult['cantidad_registros'] = $status;//status = Tambien contiene la cantidad de registros
		}
		return $arrResult;
	}

	function getTotalVencidos() {
		global $sqlca;
		$today = date("Y-m-d 00:00:00");
		$sqlca->query("SELECT COUNT(id_nu_formulario) FROM inv_pedido_vencimiento WHERE fe_vencimiento < '".$today."';");
		$row = $sqlca->fetchRow();
		return $row[0];
    }



}
