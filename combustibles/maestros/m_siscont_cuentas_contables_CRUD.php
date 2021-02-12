<?php

class SISCONTCtaContablesCRUDModel extends Model {

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
    
    function listarCuentasContables() {
		global $sqlca;

		$status = $sqlca->query("
		SELECT
			nu_id,
			(CASE
				WHEN nu_tipooperacion = '02' THEN 'Ventas'
				WHEN nu_tipooperacion = '04' THEN 'Cobranza'
				ELSE 'Compras'
			END) AS no_tipo_operacion,
			nu_cuentacontable,
			no_flujoefectivo,
			nu_mediopago,
			(CASE WHEN no_tipolibro = 'V' THEN 'Ventas' END) no_tipo_libro,
			nu_tiposiscont,
			(CASE
				WHEN nu_fpago = '1' THEN 'Efectivo'
				WHEN nu_fpago = '2' THEN 'Tarjeta de Credito'
				ELSE ''
			END) AS no_forma_pago
		FROM
		    cuentas_contables_siscont
		ORDER BY
		    nu_tipooperacion,
		    nu_fpago,
		    nu_cuentacontable;
		");

		$arrResult['estado'] = FALSE;
		$arrResult['result'] = '';
	    $arrResult['cantidad_registros'] = 0;

		if($status < 0)
			$arrResult['mensaje'] = 'Error SQL - function listarCuentasContables';
		else if($status == 0)
			$arrResult['mensaje'] = 'No se encontró ningún registro';
		else{
			$arrResult['estado'] = TRUE;
			$arrResult['result'] = $sqlca->fetchAll();
			$arrResult['cantidad_registros'] = $status;//status = Tambien contiene la cantidad de registros
		}

		return $arrResult;
    }
    
    function obtenerCuentaContable($ID_Cuenta_Contable) {
		global $sqlca;

		$status = $sqlca->query("
		SELECT
			*
		FROM
		    cuentas_contables_siscont
		WHERE
			nu_id = '" . $ID_Cuenta_Contable . "'
		");

		$arrResult['estado'] = FALSE;
		$arrResult['result'] = '';
	    $arrResult['cantidad_registros'] = 0;

		if($status < 0)
			$arrResult['mensaje'] = 'Error SQL - function obtenerCuentaContable';
		else if($status == 0)
			$arrResult['mensaje'] = 'No se encontró ningún registro';
		else{
			$arrResult['estado'] = TRUE;
			$arrResult['result'] = $sqlca->fetchAll();
			$arrResult['cantidad_registros'] = $status;//status = Tambien contiene la cantidad de registros
		}
		return $arrResult;
    }
    
    function actualizarCuentaContable($arrData) {
		global $sqlca;		

		$set_update_no_flujoefectivo = null;
		$set_update_nu_mediopago = null;
		$set_update_no_tipolibro = null;
		$set_update_nu_fpago = null;

		$ID_Cuenta_Contable 	= trim($_POST['nu_id']);
		$ID_Cuenta_Contable 	= strip_tags($ID_Cuenta_Contable);

		$nu_tipooperacion 		= trim($_POST['nu_tipooperacion']);
		$nu_tipooperacion 		= strip_tags($nu_tipooperacion);
		
		$nu_cuentacontable 		= trim($_POST['nu_cuentacontable']);
		$nu_cuentacontable 		= strip_tags($nu_cuentacontable);
		
		if(isset($_POST['no_flujoefectivo'])){
			$no_flujoefectivo 		= trim($_POST['no_flujoefectivo']);
			$no_flujoefectivo 		= strip_tags($no_flujoefectivo);
			$set_update_no_flujoefectivo = ", no_flujoefectivo = '" . $no_flujoefectivo . "',";
		}
		
		if(isset($_POST['nu_mediopago'])){
			$nu_mediopago 		= trim($_POST['nu_mediopago']);
			$nu_mediopago 		= strip_tags($nu_mediopago);
			$set_update_nu_mediopago = "nu_mediopago = '" . $nu_mediopago . "',";
		}
		
		if(isset($_POST['no_tipolibro'])){
			$no_tipolibro 		= trim($_POST['no_tipolibro']);
			$no_tipolibro 		= strip_tags($no_tipolibro);
			$set_update_no_tipolibro = "no_tipolibro = '" . $no_tipolibro . "',";
		}
		
		$nu_tiposiscont 		= trim($_POST['nu_tiposiscont']);
		$nu_tiposiscont 		= strip_tags($nu_tiposiscont);
		
		if(isset($_POST['nu_fpago'])){
			$nu_fpago 		= trim($_POST['nu_fpago']);
			$nu_fpago 		= strip_tags($nu_fpago);
			$set_update_nu_fpago = "nu_fpago = '" . $nu_fpago . "'";
		}

		$status = $sqlca->query("
		UPDATE
		    cuentas_contables_siscont
		SET
			nu_tipooperacion = '" . $nu_tipooperacion . "',
			nu_cuentacontable = '" . $nu_cuentacontable . "',
			nu_tiposiscont = '" . $nu_tiposiscont . "'
			" . $set_update_no_flujoefectivo . "
			" . $set_update_nu_mediopago . "
			" . $set_update_no_tipolibro . "
			" . $set_update_nu_fpago . "
		WHERE
			nu_id = '" . $ID_Cuenta_Contable . "'
		");

		$arrResult['estado'] = FALSE;
		$arrResult['result'] = '';
	    $arrResult['cantidad_registros'] = 0;

		if($status < 0)
			$arrResult['mensaje'] = 'Error SQL - function obtenerCuentaContable';
		else if($status == 0)
			$arrResult['mensaje'] = 'No se encontró ningún registro';
		else{
			$arrResult['estado'] = TRUE;
			$arrResult['result'] = $sqlca->fetchAll();
			$arrResult['cantidad_registros'] = $status;//status = Tambien contiene la cantidad de registros
		}
		return $arrResult;
    }
}
