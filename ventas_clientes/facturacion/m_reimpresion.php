<?php

class ReimpresionModel extends Model
{
    function obtenerTiposDocumentos()
    {
	global $sqlca;
	
	$sql = "SELECT
		    substr(tab_elemento, 5, 2),
		    trim(tab_descripcion)
		FROM
		    int_tabla_general
		WHERE
			tab_tabla='08'
		    AND tab_car_02 IS NOT NULL
		    AND tab_elemento<>'000000'
		ORDER BY
		    tab_elemento
		;
		";
	if ($sqlca->query($sql) < 0) return null;
	
	$result = Array();
	
	for($i = 0; $i < $sqlca->numrows(); $i++) {
	    $a = $sqlca->fetchRow();
	    
	    $result[$a[0]] = $a[0] . ' - ' . $a[1];
	}
	
	return $result;
    }
    
    function procesarDocumento($ch_tipodocumento, $ch_seriedocumento, $ch_numdocumento)
    {
	global $sqlca;
	global $usuario;
	
	$sql = "BEGIN;";
	$sqlca->query($sql);

	$sql = "LOCK TABLE
		    fac_ta_factura_cabecera,
		    fac_ta_factura_detalle,
		    int_num_documentos
		IN
		    EXCLUSIVE MODE
		;
		";
	echo $sql;
	if ($sqlca->query($sql) < 0) {
	    $sql = "ROLLBACK;";
	    $sqlca->query($sql);
	    return false;
	}

	$sql = "SELECT
		    *
		FROM
		    fac_ta_factura_cabecera
		WHERE
			ch_fac_tipodocumento='" . pg_escape_string($ch_tipodocumento) . "'
		    AND trim(ch_fac_seriedocumento)='" . pg_escape_string($ch_seriedocumento) . "'
		    AND ch_fac_numerodocumento='" . pg_escape_string($ch_numdocumento) . "'
		;
		";
	echo $sql;
	if ($sqlca->query($sql, "_cabecera") < 0 || $sqlca->numrows("_cabecera") == 0) {
	    $sql = "ROLLBACK;";
	    $sqlca->query($sql);
	    return false;
	}
	
	$sql = "SELECT
		    *
		FROM
		    fac_ta_factura_detalle
		WHERE
			ch_fac_tipodocumento='" . pg_escape_string($ch_tipodocumento) . "'
		    AND trim(ch_fac_seriedocumento)='" . pg_escape_string($ch_seriedocumento) . "'
		    AND ch_fac_numerodocumento='" . pg_escape_string($ch_numdocumento) . "'
		;
		";
	echo $sql;
	if ($sqlca->query($sql, "_detalle") < 0 || $sqlca->numrows("_detalle") == 0) {
	    $sql = "ROLLBACK;";
	    $sqlca->query($sql);
	    return false;
	}

	$sql = "SELECT
		    *
		FROM
		    fac_ta_factura_complemento
		WHERE
			ch_fac_tipodocumento='" . pg_escape_string($ch_tipodocumento) . "'
		    AND trim(ch_fac_seriedocumento)='" . pg_escape_string($ch_seriedocumento) . "'
		    AND ch_fac_numerodocumento='" . pg_escape_string($ch_numdocumento) . "'
		;
		";
	echo $sql;
	if ($sqlca->query($sql, "_complemento") < 0) {
	    $sql = "ROLLBACK;";
	    $sqlca->query($sql);
	    return false;
	}

	$sql = "SELECT
		    num_numactual
		FROM
		    int_num_documentos
		WHERE
			num_tipdocumento='" . pg_escape_string($ch_tipodocumento) . "'
		    AND num_seriedocumento='" . pg_escape_string($ch_seriedocumento) . "'
		;
		";
	echo $sql;
	if ($sqlca->query($sql) < 0) {
	    $sql = "ROLLBACK;";
	    $sqlca->query($sql);
	    return false;
	}

	/* Obtiene el numerador actual */
	$a = $sqlca->fetchRow();
	$num_numactual = $a[0]+1;

	/* Obtiene cabecera de la factura a anular */
	$cabecera = $sqlca->fetchRow("_cabecera");
	$ch_liquidacion = $cabecera['ch_liquidacion'];
	
	/* Inserta cabecera de la factura */
	$sql = "INSERT INTO
		    fac_ta_factura_cabecera
		VALUES
		(
		    '" . pg_escape_string($ch_tipodocumento) . "',
		    '" . pg_escape_string($ch_seriedocumento) . "',
		    '" . pg_escape_string(str_pad($num_numactual, 7, '0', STR_PAD_LEFT)) . "',
		    '" . pg_escape_string($cabecera['cli_codigo']) . "',
		    '" . pg_escape_string($cabecera['dt_fac_fecha']) . "',
		    '" . pg_escape_string($cabecera['ch_punto_venta']) . "',
		    '" . pg_escape_string($cabecera['ch_almacen']) . "',
		    '" . pg_escape_string($cabecera['ch_fac_moneda']) . "',
		    '" . pg_escape_string(number_format($cabecera['nu_tipocambio'], 4, '.', '')) . "',
		    '" . pg_escape_string(number_format($cabecera['nu_fac_valorbruto'], 4, '.', '')) . "',
		    '" . pg_escape_string($cabecera['ch_factipo_descuento1']) . "',
		    '" . pg_escape_string($cabecera['ch_factipo_descuento2']) . "',
		    '" . pg_escape_string($cabecera['ch_factipo_descuento3']) . "',
		    '" . pg_escape_string(number_format($cabecera['nu_fac_descuento1'], 4, '.', '')) . "',
		    '" . pg_escape_string(number_format($cabecera['nu_fac_descuento2'], 4, '.', '')) . "',
		    '" . pg_escape_string(number_format($cabecera['nu_fac_descuento3'], 4, '.', '')) . "',
		    '" . pg_escape_string($cabecera['ch_fac_tiporecargo1']) . "',
		    '" . pg_escape_string($cabecera['ch_fac_tiporecargo2']) . "',
		    '" . pg_escape_string($cabecera['ch_fac_tiporecargo3']) . "',
		    '" . pg_escape_string(number_format($cabecera['nu_fac_recargo1'], 4, '.', '')) . "',
		    '" . pg_escape_string(number_format($cabecera['nu_fac_recargo2'], 4, '.', '')) . "',
		    '" . pg_escape_string(number_format($cabecera['nu_fac_recargo3'], 4, '.', '')) . "',
		    '" . pg_escape_string($cabecera['ch_fac_cd_impuesto1']) . "',
		    '" . pg_escape_string($cabecera['ch_fac_cd_impuesto2']) . "',
		    '" . pg_escape_string($cabecera['ch_fac_cd_impuesto3']) . "',
		    '" . pg_escape_string(number_format($cabecera['nu_fac_impuesto1'], 4, '.', '')) . "',
		    '" . pg_escape_string(number_format($cabecera['nu_fac_impuesto2'], 4, '.', '')) . "',
		    '" . pg_escape_string(number_format($cabecera['nu_fac_impuesto3'], 4, '.', '')) . "',
		    '" . pg_escape_string(number_format($cabecera['nu_fac_valortotal'], 4, '.', '')) . "',
		    '" . pg_escape_string($cabecera['ch_fac_credito']) . "',
		    '" . pg_escape_string($cabecera['ch_fac_forma_pago']) . "',
		    '',
		    'N',
		    '" . pg_escape_string($cabecera['ch_fac_anticipo']) . "',
		    '" . pg_escape_string($cabecera['ch_fac_cab_identidad']) . "',
		    '0',
		    now(),
		    '" . pg_escape_string($cabecera['ch_liquidacion']) . "',
		    '" . pg_escape_string($cabecera['ch_descargar_stock']) . "'
		);
		";
	echo $sql;
	if ($sqlca->query($sql) < 0) {
	    $sql = "ROLLBACK;";
	    $sqlca->query($sql);
	    return false;
	}

	/* Procesa complemento si es que existe uno */
	if ($sqlca->numrows("_complemento") > 0) {
	    /* Obtiene el complemento */
	    $a = $sqlca->fetchRow("_complemento");

	    /* Inserta el complemento */
	    $sql = "INSERT INTO
			fac_ta_factura_complemento
		    VALUES
		    (
			'" . pg_escape_string($ch_tipodocumento) . "',
		        '" . pg_escape_string($ch_seriedocumento) . "',
		        '" . pg_escape_string(str_pad($num_numactual, 7, '0', STR_PAD_LEFT)) . "',
		        '" . pg_escape_string($a['cli_codigo']) . "',
		        '" . pg_escape_string($cabecera['dt_fac_fecha']) . "',
		        '" . pg_escape_string($a['ch_fac_observacion1']) . "',
		        '" . pg_escape_string($a['ch_fac_observacion2']) . "',
		        '" . pg_escape_string($a['ch_fac_observacion3']) . "',
			'" . pg_escape_string($a['ch_fac_ruc']) . "',
		        '" . pg_escape_string($a['nu_fac_direccion']) . "',
		        '" . pg_escape_string($a['nu_fac_complemento_direccion']) . "',
		        now(),
		        '" . pg_escape_string($usuario->obtenerUsuario()) . "',
		        '" . pg_escape_string($_SERVER['REMOTE_ADDR']) . "',
		        '" . pg_escape_string($a['ch_fac_nombreclie']) . "',
		        '0',
		        now(),
		        '" . number_format(pg_escape_string($a['int_cantvales']), 0, '', '') . "'
		    );
		    ";
	echo $sql;
	    if ($sqlca->query($sql) < 0) {
		$sql = "ROLLBACK;";
		$sqlca->query($sql);
		return false;
	    }
	}

	/* procesa detalle */
	for($i = 0; $i < $sqlca->numrows("_detalle"); $i++) {
	    $a = $sqlca->fetchRow("_detalle");
	    
	    $sql = "INSERT INTO
			fac_ta_factura_detalle
		    VALUES
		    (
			'" . pg_escape_string($ch_tipodocumento) . "',
		        '" . pg_escape_string($ch_seriedocumento) . "',
		        '" . pg_escape_string(str_pad($num_numactual, 7, '0', STR_PAD_LEFT)) . "',
		        '" . pg_escape_string($a['cli_codigo']) . "',
			'" . pg_escape_string($a['art_codigo']) . "',
			'" . pg_escape_string($a['pre_lista_precio']) . "',
			'" . pg_escape_string(number_format($a['nu_fac_cantidad'], 4, '.', '')) . "',
			'" . pg_escape_string(number_format($a['nu_fac_precio'], 4, '.', '')) . "',
			'" . pg_escape_string(number_format($a['nu_fac_importeneto'], 4, '.', '')) . "',
			'" . pg_escape_string($a['ch_factipo_descuento1']) . "',
			'" . pg_escape_string($a['ch_factipo_descuento2']) . "',
			'" . pg_escape_string($a['ch_factipo_descuento3']) . "',
			'" . pg_escape_string(number_format($a['nu_fac_descuento1'], 4, '.', '')) . "',
			'" . pg_escape_string(number_format($a['nu_fac_descuento2'], 4, '.', '')) . "',
			'" . pg_escape_string(number_format($a['nu_fac_descuento3'], 4, '.', '')) . "',
			'" . pg_escape_string($a['ch_fac_tiporecargo1']) . "',
			'" . pg_escape_string($a['ch_fac_tiporecargo2']) . "',
			'" . pg_escape_string($a['ch_fac_tiporecargo3']) . "',
			'" . pg_escape_string(number_format($a['nu_fac_recargo1'], 4, '.', '')) . "',
			'" . pg_escape_string(number_format($a['nu_fac_recargo2'], 4, '.', '')) . "',
			'" . pg_escape_string(number_format($a['nu_fac_recargo3'], 4, '.', '')) . "',
			'" . pg_escape_string($a['ch_fac_cd_impuesto1']) . "',
			'" . pg_escape_string($a['ch_fac_cd_impuesto2']) . "',
			'" . pg_escape_string($a['ch_fac_cd_impuesto3']) . "',
			'" . pg_escape_string(number_format($a['nu_fac_impuesto1'], 4, '.', '')) . "',
			'" . pg_escape_string(number_format($a['nu_fac_impuesto2'], 4, '.', '')) . "',
			'" . pg_escape_string(number_format($a['nu_fac_impuesto3'], 4, '.', '')) . "',
			'" . pg_escape_string(number_format($a['nu_fac_valortotal'], 4, '.', '')) . "',
			'" . pg_escape_string($a['ch_fac_glosa']) . "',
			'N',
			'" . pg_escape_string($a['ch_fac_estado']) . "',
			'" . pg_escape_string($a['ch_fac_det_identidad']) . "'
		    );
		    ";
	echo $sql;
	    if ($sqlca->query($sql) < 0) {
		$sql = "ROLLBACK;";
		$sqlca->query($sql);
		return false;
	    }
	}

	/* Avanza el numerador de documentos */
	$sql = "UPDATE
		    int_num_documentos
		SET
		    num_numactual='" . pg_escape_string($num_numactual) . "'
		WHERE
			num_tipdocumento='" . pg_escape_string($ch_tipodocumento) . "'
		    AND num_seriedocumento='" . pg_escape_string($ch_seriedocumento) . "'
		;
		";
	echo $sql;
	if ($sqlca->query($sql) < 0) {
	    $sql = "ROLLBACK;";
	    $sqlca->query($sql);
	    return false;
	}

	/* Anula factura antigua */
	$sql = "UPDATE
		    fac_ta_factura_cabecera
		SET
		    ch_fac_anulado='S'
		WHERE
			ch_fac_tipodocumento='" . pg_escape_string($ch_tipodocumento) . "'
		    AND trim(ch_fac_seriedocumento)='" . pg_escape_string($ch_seriedocumento) . "'
		    AND ch_fac_numerodocumento='" . pg_escape_string($ch_numdocumento) . "'
		;
		";
	echo $sql;
	if ($sqlca->query($sql) < 0) {
	    $sql = "ROLLBACK;";
	    $sqlca->query($sql);
	    return false;
	}

	$sql = "SELECT
		    *
		FROM
		    fac_ta_factura_cabecera
		WHERE
			ch_liquidacion='" . pg_escape_string($ch_liquidacion) . "'
		    AND ch_fac_tipodocumento='20'
		;
		";
	echo $sql;
	if ($sqlca->query($sql, "_cabecera") < 0) {
	    $sql = "ROLLBACK;";
	    $sqlca->query($sql);
	    return false;
	}

	/* Si existe una nota de credito asociada, actualiza su factura asociada */
	if ($ch_tipodocumento == '10' && $sqlca->numrows("_cabecera") > 0) {
	    $cabecera = $sqlca->fetchRow("_cabecera");
	    
	    $ncred_serie = $cabecera['ch_fac_seriedocumento'];
	    $ncred_numero = $cabecera['ch_fac_numerodocumento'];
	    $ncred_impreso = $cabecera['ch_fac_impreso'];
	    
	    /*
	     * Si la nota de credito no ha sido impresa, la modifica. Si 
	     * ya ha sido impresa, anula la actual y crea una nueva.
	     */
	    if ($ncred_impreso != 'S') {
		$sql = "UPDATE
			    fac_ta_factura_complemento
			SET
			    ch_fac_observacion3='" . pg_escape_string("REFERENCIA FACTURA " . $ch_seriedocumento . "-" . str_pad($num_numactual, 7, '0', STR_PAD_LEFT)) . "'
			WHERE
				ch_fac_tipodocumento='20'
			    AND ch_fac_seriedocumento='" . pg_escape_string($ncred_serie) . "'
			    AND ch_fac_numerodocumento='" . pg_escape_string($ncred_numero) . "'
			";
	echo $sql;
		if ($sqlca->query($sql) < 0) {
		    $sql = "ROLLBACK;";
		    $sqlca->query($sql);
		    return false;
		}
	    }
	    else {
		/* Obtiene numerador de la nota de credito */
		$sql = "SELECT
			    num_numactual
			FROM
			    int_num_documentos
			WHERE
				num_tipdocumento='20'
			    AND num_seriedocumento='" . pg_escape_string($cabecera['ch_fac_seriedocumento']) . "'
			;
			";
	echo $sql;
		if ($sqlca->query($sql) < 0) {
		    $sql = "ROLLBACK;";
		    $sqlca->query($sql);
		    return false;
		}

		/* Siguiente documento */
		$a = $sqlca->fetchRow();
		$ncred_numero_nuevo = $a[0] + 1;

		/* Inserta cabecera de la nota de credito */
		$sql = "INSERT INTO
			    fac_ta_factura_cabecera
			VALUES
			(
			    '20',
			    '" . pg_escape_string($ncred_serie) . "',
			    '" . pg_escape_string(str_pad($ncred_numero_nuevo, 7, '0', STR_PAD_LEFT)) . "',
			    '" . pg_escape_string($cabecera['cli_codigo']) . "',
			    '" . pg_escape_string($cabecera['dt_fac_fecha']) . "',
			    '" . pg_escape_string($cabecera['ch_punto_venta']) . "',
			    '" . pg_escape_string($cabecera['ch_almacen']) . "',
			    '" . pg_escape_string($cabecera['ch_fac_moneda']) . "',
			    '" . pg_escape_string(number_format($cabecera['nu_tipocambio'], 4, '.', '')) . "',
			    '" . pg_escape_string(number_format($cabecera['nu_fac_valorbruto'], 4, '.', '')) . "',
			    '" . pg_escape_string($cabecera['ch_factipo_descuento1']) . "',
			    '" . pg_escape_string($cabecera['ch_factipo_descuento2']) . "',
			    '" . pg_escape_string($cabecera['ch_factipo_descuento3']) . "',
			    '" . pg_escape_string(number_format($cabecera['nu_fac_descuento1'], 4, '.', '')) . "',
			    '" . pg_escape_string(number_format($cabecera['nu_fac_descuento2'], 4, '.', '')) . "',
			    '" . pg_escape_string(number_format($cabecera['nu_fac_descuento3'], 4, '.', '')) . "',
			    '" . pg_escape_string($cabecera['ch_fac_tiporecargo1']) . "',
			    '" . pg_escape_string($cabecera['ch_fac_tiporecargo2']) . "',
			    '" . pg_escape_string($cabecera['ch_fac_tiporecargo3']) . "',
			    '" . pg_escape_string(number_format($cabecera['nu_fac_recargo1'], 4, '.', '')) . "',
			    '" . pg_escape_string(number_format($cabecera['nu_fac_recargo2'], 4, '.', '')) . "',
			    '" . pg_escape_string(number_format($cabecera['nu_fac_recargo3'], 4, '.', '')) . "',
			    '" . pg_escape_string($cabecera['ch_fac_cd_impuesto1']) . "',
			    '" . pg_escape_string($cabecera['ch_fac_cd_impuesto2']) . "',
			    '" . pg_escape_string($cabecera['ch_fac_cd_impuesto3']) . "',
			    '" . pg_escape_string(number_format($cabecera['nu_fac_impuesto1'], 4, '.', '')) . "',
			    '" . pg_escape_string(number_format($cabecera['nu_fac_impuesto2'], 4, '.', '')) . "',
			    '" . pg_escape_string(number_format($cabecera['nu_fac_impuesto3'], 4, '.', '')) . "',
			    '" . pg_escape_string(number_format($cabecera['nu_fac_valortotal'], 4, '.', '')) . "',
			    '" . pg_escape_string($cabecera['ch_fac_credito']) . "',
			    '" . pg_escape_string($cabecera['ch_fac_forma_pago']) . "',
			    '" . pg_escape_string($cabecera['ch_fac_anulado']) . "',
			    'N',
			    '" . pg_escape_string($cabecera['ch_fac_anticipo']) . "',
			    '" . pg_escape_string($cabecera['ch_fac_cab_identidad']) . "',
			    '0',
			    now(),
			    '" . pg_escape_string($cabecera['ch_liquidacion']) . "',
			    '" . pg_escape_string($cabecera['ch_descargar_stock']) . "'
			);
			";
	echo $sql;
		if ($sqlca->query($sql) < 0) {
		    $sql = "ROLLBACK;";
		    $sqlca->query($sql);
		    return false;
		}

		$sql = "SELECT
			    *
			FROM
			    fac_ta_factura_detalle
			WHERE
				ch_fac_tipodocumento='20'
			    AND ch_fac_seriedocumento='" . pg_escape_string($ncred_serie) . "'
			    AND ch_fac_numerodocumento='" . pg_escape_string($ncred_numero) . "'
			;
			";
		echo $sql;
		if ($sqlca->query($sql, "_detalle") < 0 || $sqlca->numrows("_detalle") == 0) {
		    $sql = "ROLLBACK;";
		    $sqlca->query($sql);
		    return false;
		}

		/* procesa detalle */
		for($i = 0; $i < $sqlca->numrows("_detalle"); $i++) {
		    $a = $sqlca->fetchRow("_detalle");
	    
		    $sql = "INSERT INTO
				fac_ta_factura_detalle
			    VALUES
			    (
				'20',
		    		'" . pg_escape_string($ncred_serie) . "',
		    		'" . pg_escape_string(str_pad($ncred_numero_nuevo, 7, '0', STR_PAD_LEFT)) . "',
		    		'" . pg_escape_string($a['cli_codigo']) . "',
				'" . pg_escape_string($a['art_codigo']) . "',
				'" . pg_escape_string($a['pre_lista_precio']) . "',
				'" . pg_escape_string(number_format($a['nu_fac_cantidad'], 4, '.', '')) . "',
				'" . pg_escape_string(number_format($a['nu_fac_precio'], 4, '.', '')) . "',
				'" . pg_escape_string(number_format($a['nu_fac_importeneto'], 4, '.', '')) . "',
				'" . pg_escape_string($a['ch_factipo_descuento1']) . "',
				'" . pg_escape_string($a['ch_factipo_descuento2']) . "',
				'" . pg_escape_string($a['ch_factipo_descuento3']) . "',
				'" . pg_escape_string(number_format($a['nu_fac_descuento1'], 4, '.', '')) . "',
				'" . pg_escape_string(number_format($a['nu_fac_descuento2'], 4, '.', '')) . "',
				'" . pg_escape_string(number_format($a['nu_fac_descuento3'], 4, '.', '')) . "',
				'" . pg_escape_string($a['ch_fac_tiporecargo1']) . "',
				'" . pg_escape_string($a['ch_fac_tiporecargo2']) . "',
				'" . pg_escape_string($a['ch_fac_tiporecargo3']) . "',
				'" . pg_escape_string(number_format($a['nu_fac_recargo1'], 4, '.', '')) . "',
				'" . pg_escape_string(number_format($a['nu_fac_recargo2'], 4, '.', '')) . "',
				'" . pg_escape_string(number_format($a['nu_fac_recargo3'], 4, '.', '')) . "',
				'" . pg_escape_string($a['ch_fac_cd_impuesto1']) . "',
				'" . pg_escape_string($a['ch_fac_cd_impuesto2']) . "',
				'" . pg_escape_string($a['ch_fac_cd_impuesto3']) . "',
				'" . pg_escape_string(number_format($a['nu_fac_impuesto1'], 4, '.', '')) . "',
				'" . pg_escape_string(number_format($a['nu_fac_impuesto2'], 4, '.', '')) . "',
				'" . pg_escape_string(number_format($a['nu_fac_impuesto3'], 4, '.', '')) . "',
				'" . pg_escape_string(number_format($a['nu_fac_valortotal'], 4, '.', '')) . "',
				'" . pg_escape_string($a['ch_fac_glosa']) . "',
				'N',
				'" . pg_escape_string($a['ch_fac_estado']) . "',
				'" . pg_escape_string($a['ch_fac_det_identidad']) . "'
			    );
			    ";
		    echo $sql;
		    if ($sqlca->query($sql) < 0) {
			$sql = "ROLLBACK;";
			$sqlca->query($sql);
			return false;
		    }
		}

		/* Complemento de la nota de credito */
		$sql = "SELECT
			    *
			FROM
			    fac_ta_factura_complemento
			WHERE
				ch_fac_tipodocumento='20'
			    AND ch_fac_seriedocumento='" . pg_escape_string($ncred_serie) . "'
			    AND ch_fac_numerodocumento='" . pg_escape_string(str_pad($ncred_numero, 7, '0', STR_PAD_LEFT)) . "'
			;
			";
	echo $sql;
		if ($sqlca->query($sql, "_complemento") < 0) {
		    $sql = "ROLLBACK;";
		    $sqlca->query($sql);
		    return false;
		}

		/* Procesa complemento si es que existe uno */
		if ($sqlca->numrows("_complemento") > 0) {
		    /* Obtiene el complemento */
		    $a = $sqlca->fetchRow("_complemento");

		    /* Inserta el complemento */
		    $sql = "INSERT INTO
				fac_ta_factura_complemento
			    VALUES
			    (
				'20',
		    		'" . pg_escape_string($ncred_serie) . "',
		    		'" . pg_escape_string(str_pad($ncred_numero_nuevo, 7, '0', STR_PAD_LEFT)) . "',
		    		'" . pg_escape_string($a['cli_codigo']) . "',
		    		'" . pg_escape_string($cabecera['dt_fac_fecha']) . "',
		    		'" . pg_escape_string($a['ch_fac_observacion1']) . "',
		    		'" . pg_escape_string($a['ch_fac_observacion2']) . "',
		    		'REFERENCIA FACTURA " . pg_escape_string($ch_seriedocumento."-".str_pad($num_numactual, 7, '0', STR_PAD_LEFT)) . "',
				'" . pg_escape_string($a['ch_fac_ruc']) . "',
			        '" . pg_escape_string($a['nu_fac_direccion']) . "',
		    		'" . pg_escape_string($a['nu_fac_complemento_direccion']) . "',
		    		now(),
		    		'" . pg_escape_string($usuario->obtenerUsuario()) . "',
		    		'" . pg_escape_string($_SERVER['REMOTE_ADDR']) . "',
		    		'" . pg_escape_string($a['ch_fac_nombreclie']) . "',
		    		'0',
		    		now()
			    );
			    ";
	echo $sql;
		    if ($sqlca->query($sql) < 0) {
			$sql = "ROLLBACK;";
			$sqlca->query($sql);
			return false;
		    }

		    /* Anula nota de credito antigua */
		    $sql = "UPDATE
				fac_ta_factura_cabecera
			    SET
				ch_fac_anulado='S'
			    WHERE
				    ch_fac_tipodocumento='20'
				AND ch_fac_seriedocumento='" . pg_escape_string($ncred_serie) . "'
				AND ch_fac_numerodocumento='" . pg_escape_string($ncred_numero) . "'
			    ;
			    ";
		    echo $sql;
		    if ($sqlca->query($sql) < 0) {
			$sql = "ROLLBACK;";
			$sqlca->query($sql);
			return false;
		    }

		    /* Avanza el numerador de documentos */
		    $sql = "UPDATE
				int_num_documentos
			    SET
				num_numactual='" . pg_escape_string($ncred_numero_nuevo) . "'
			    WHERE
				    num_tipdocumento='20'
				AND num_seriedocumento='" . pg_escape_string($ncred_serie) . "'
			    ;
			    ";
		    echo $sql;
		    if ($sqlca->query($sql) < 0) {
			$sql = "ROLLBACK;";
	    		$sqlca->query($sql);
			return false;
		    }
		}
	    }
	}

	/* Puesto que todo ocurrio bien, no hay problema en aplicar cambios */
	$sql = "COMMIT;";
//	$sql = "ROLLBACK;";
	$sqlca->query($sql);
    }
}

