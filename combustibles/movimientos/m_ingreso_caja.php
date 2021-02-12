<?php

class RegistroCajasModel extends Model {

    function IniciarTransaccion() {
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

	function tipo_cambio($fecha_actula) {
        global $sqlca;
		$sql = "SELECT tca_venta_oficial FROM int_tipo_cambio WHERE tca_moneda = '02' AND tca_fecha = '" . $fecha_actula . "';";
		if ($sqlca->query($sql) < 0)
			return "00.0";
		$a = $sqlca->firstRow($sql);
		return $a[0];
	}

    function obtenerSucursales($alm) {
        global $sqlca;

        if (trim($alm) == "")
            $cond = "";
        else
            $cond = " AND ch_almacen = '$alm'";

        $sql = "SELECT
			    ch_almacen,
			    ch_almacen||' - '||ch_nombre_almacen
			FROM
			    inv_ta_almacenes
			WHERE
			    ch_clase_almacen='1' $cond 
			ORDER BY
			    ch_almacen;";

        if ($sqlca->query($sql) < 0)
            return false;

        $result = Array();

        for ($i = 0; $i < $sqlca->numrows(); $i++) {
            $a = $sqlca->fetchRow();
            $result[] = array($a[0], $a[1]);
        }

        return $result;
    }


	function obtenerTipoDocumnetos_otros() {
	        global $sqlca;
	
        	$sql = "SELECT                                                                                                                                        
        	                substring(trim(tab_elemento) for 2 from length(trim(tab_elemento))-1) as cod_docu,                                                        
        	                tab_desc_breve as desc_docu                                                                                                               
        	        FROM                                                                                                                                          
        	                int_tabla_general                                                                                                                         
        	        WHERE                                                                                                                                         
        	                tab_tabla ='08'
				AND tab_elemento != '000000'
        	        ORDER BY
        	                cod_docu;
		";

		if ($sqlca->query($sql) < 0)
			return false;

		$result = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$result[] = array($a[0], $a[1]);
		}

		return $result;

	}

	function obtenerOperacion() {
        	global $sqlca;

		$sql = "SELECT c_cash_operation_id,name,accounts FROM c_cash_operation where type='0';";

		if ($sqlca->query($sql) < 0)
		    return false;

		$result = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$result[] = array($a[0], $a[1], $a[2]);
		}

        	return $result;

	}

	function obtenerCaja(){
        	global $sqlca;

		$sql = "SELECT c_cash_id, name FROM c_cash;";

		if ($sqlca->query($sql) < 0)
			return false;

		$result = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$result[] = array($a[0], $a[1]);
		}

		return $result;

	}

	function ObtenerMoneda(){
        	global $sqlca;

		$sql = "SELECT substring(tab_elemento,5,2), tab_descripcion FROM int_tabla_general WHERE tab_tabla ='04' AND tab_elemento!='000000' ORDER BY tab_elemento;";

		if ($sqlca->query($sql) < 0)
			return false;

		$result = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$result[] = array($a[0], $a[1]);
		}

		return $result;

	}

	function ObtenerCuentasDeBanco($id_banco, $id_moneda) {
        	global $sqlca;

		$id_moneda = substr($id_moneda, 1, 1);

	        $sql = "
			SELECT
				c.c_bank_account_id,
				c.name
			FROM
				c_bank_account c
			WHERE
				c_bank_id = '$id_banco'
				AND c_currency_id = $id_moneda;
			";

	        if ($sqlca->query($sql) < 0)
			return false;

	        $result = Array();

        	for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$result[] = array($a[0], $a[1]);
		}

		return $result;

	}

	function ObtenerCuentasDeBancoMoneda($id_banco) {
        	global $sqlca;

	        $sql = "
			SELECT
				substring(mone.tab_elemento,5,2),
				mone.tab_descripcion
			FROM
				c_bank_account c
				LEFT JOIN int_tabla_general mone ON (mone.tab_tabla ='04' and mone.tab_elemento != '000000' AND mone.tab_elemento = (LPAD(CAST(c.c_currency_id AS bpchar),6,'0')))
			WHERE
				c_bank_account_id = '$id_banco';
		";

	        if ($sqlca->query($sql) < 0)
			return false;

	        $result = Array();

        	for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$result[] = array($a[0], $a[1]);
		}

		return $result;

	}

	function obtenerBanco() {
       		global $sqlca;

        $sql = "select c_bank_id,initials from c_bank;";

        if ($sqlca->query($sql) < 0)
            return false;

        $result = Array();

        for ($i = 0; $i < $sqlca->numrows(); $i++) {
            $a = $sqlca->fetchRow();
            $result[] = array($a[0], $a[1]);
        }

        return $result;
    }

    function obtenerMedioPago() {
        global $sqlca;



        $sql = "select c_cash_mpayment_id,name,banking from c_cash_mpayment;";

        if ($sqlca->query($sql) < 0)
            return false;

        $result = Array();

        for ($i = 0; $i < $sqlca->numrows(); $i++) {
            $a = $sqlca->fetchRow();
            $result[] = array($a[0], $a[1], $a[2]);
        }

        return $result;
    }

    function ObtenerNroDocuemnto_Recibo($alm) {
        global $sqlca;



        $sql = "select num_numactual from  int_num_documentos where num_tipdocumento='VC' and  num_seriedocumento='$alm' limit 1;";

        if ($sqlca->query($sql) < 0) {
            return false;
        }
        $result = null;
        $a = $sqlca->fetchRow();
        if ($a != NULL) {
            $result = str_pad($a[0] + 1, 10, "0", STR_PAD_LEFT);
        } else {
            $result = '0000000000';
        }



        return $result;
    }

    function ActualizarNroDocuemnto_Recibo($alm) {
        global $sqlca;


        try {
            $sql = "UPDATE int_num_documentos SET num_numactual=(
                    SELECT num_numactual FROM  int_num_documentos WHERE num_tipdocumento='VC' AND  num_seriedocumento='$alm' limit 1
                     )::INTEGER+1  where num_tipdocumento='VC' and  num_seriedocumento='$alm';";
            if ($sqlca->query($sql) < 0) {
                throw new Exception("Error al actualizar el numero de documento.");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

	function MostarResultadoDetalle($fecha_inicio, $fecha_final, $sucursal, $limit, $ruc, $operacion) {
        	global $sqlca;

		if(!empty($ruc))
			$cond = "AND ct.bpartner = '$ruc'";

		if(!empty($operacion))
			$cond2 = "AND ct.c_cash_operation_id = '$operacion'";

        	try {

	          	$sql = "
				SELECT
					ct.d_system,
					LPAD(CAST(ct.transaction AS bpchar),10,'0'),
					c.name,
					co.name,
					ct.amount,
		           	mone.tab_desc_breve as moneda,
					SUBSTR(ic.cli_razsocial,0,40) as cliente,
					ct.transaction,
		            (
				        SELECT  
				         	SUM(cstd.amount)
						FROM
							c_cash_transaction cct 
					         INNER JOIN c_cash_transaction_payment cstd ON (cct.c_cash_transaction_id = cstd.c_cash_transaction_id)
				        WHERE
							cct.type = '0'
							AND cct.c_cash_transaction_id = ct.c_cash_transaction_id
		            ) as importe_neto,
					ct.rate,
					ct.c_cash_transaction_id,
					ct.reference
				FROM
					c_cash_transaction ct
					INNER JOIN c_cash c ON (ct.c_cash_id = c.c_cash_id)
					INNER JOIN c_cash_operation co ON (co.c_cash_operation_id = ct.c_cash_operation_id)
					LEFT JOIN int_clientes ic ON (ct.bpartner = ic.cli_codigo)
					LEFT JOIN int_tabla_general mone ON (mone.tab_tabla ='04' and mone.tab_elemento != '000000' AND mone.tab_elemento = (LPAD(CAST(ct.c_currency_id AS bpchar),6,'0')))
		                WHERE
					d_system BETWEEN '$fecha_inicio' AND '$fecha_final'
					AND ct.ware_house = '$sucursal'
					AND co.type = 0
					$cond
					$cond2
				ORDER BY
					ct.c_cash_transaction_id DESC
					LIMIT $limit;
			";

/*echo "<pre>";
echo $sql;
echo "</pre>";*/
			if ($sqlca->query($sql) < 0)
		        	throw new Exception("No se encontro Resultados.");

		    	$resultado = array();

		    	for ($i = 0; $i < $sqlca->numrows(); $i++) {
				$a = $sqlca->fetchRow();
				$resultado[$i]['d_system'] 		= $a[0];
				$resultado[$i]['num'] 			= $a[1];
				$resultado[$i]['caja'] 			= $a[2];
				$resultado[$i]['operacion'] 		= $a[3];
				$resultado[$i]['monto'] 		= $a[4];
				$resultado[$i]['moneda'] 		= $a[5];
				$resultado[$i]['cliente'] 		= $a[6];
				$resultado[$i]['id_recibo'] 		= $a[10];
				$resultado[$i]['importe_neto'] 		= $a[8];
				$resultado[$i]['rate']		 	= $a[9];
		        $resultado[$i]['referencia']		 	= $a[11];
		    	}

			return $resultado;

		} catch (Exception $e) {
			throw $e;
		}

	}

	function obtenerClientes() {
        	global $sqlca;

	        $sql = "SELECT TRIM(cli_codigo), trim(cli_razsocial) FROM int_clientes ORDER BY cli_razsocial;";

		if ($sqlca->query($sql) < 0)
		    return false;

		$items = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    $a = $sqlca->fetchRow();
		    $items[$a[0]] = $a[1];
		}

		$result = array();
		foreach ($items as $key => $value) {
		    array_push($result, array("id" => $key, "label" => $value, "value" => strip_tags($value)));
		}

		//json_encode is available in PHP 5.2 and above, or you can install a PECL module in earlier versions
		$data_codi = json_encode($result);

		return $data_codi;

	}

	function TraerClientes($keyword) {
    	global $sqlca;

        $sql = "SELECT TRIM(cli_codigo), trim(cli_razsocial) FROM int_clientes WHERE trim(cli_razsocial) LIKE '%$keyword%' ORDER BY cli_razsocial;";

		if ($sqlca->query($sql) < 0)
		    return false;

		$items = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    $a = $sqlca->fetchRow();
		    $items[$a[0]] = $a[1];
		}

		/*$result = array();
		foreach ($items as $key => $value) {
		    array_push($result, array("id" => $key, "label" => $value, "value" => strip_tags($value)));
		}

		//json_encode is available in PHP 5.2 and above, or you can install a PECL module in earlier versions
		$data_codi = json_encode($result);*/

		return $items;
	}

	function Verify_FC_BV($numero_docu) {
        global $sqlca;

        $data = NULL;
/*
        $query = "
SELECT
 COUNT(*) as verificar
FROM
 ccob_ta_detalle
WHERE
 ch_tipmovimiento='1'
 AND ch_tipdocumento||ch_seriedocumento||ch_numdocumento IN (
 SELECT
  ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento
 FROM
  fac_ta_factura_complemento
 WHERE
  ch_fac_tipodocumento IN ('11','20')
  AND (string_to_array(ch_fac_observacion2, '*'))[3]||' - '||(string_to_array(ch_fac_observacion2, '*'))[2]||' - '||(string_to_array(ch_fac_observacion2, '*'))[1] IN (". $numero_docu .")
);
		";

		//echo $query;

		if($sqlca->query($query) < 0)
			return false;

        $data = $sqlca->fetchrow();

		if ($data['verificar'] == 0){
		*/
			$sql = "
SELECT
 COUNT(*) AS nu_total_documento_referencias
FROM
 fac_ta_factura_complemento
WHERE
 ch_fac_tipodocumento IN('20','11')
 AND (string_to_array(ch_fac_observacion2, '*'))[3]||' - '||(string_to_array(ch_fac_observacion2, '*'))[2]||' - '||(string_to_array(ch_fac_observacion2, '*'))[1] IN (" . $numero_docu . ");
			";

/*
	echo "<pre>";
	echo $sql;
	echo "</pre>";
*/
			if($sqlca->query($sql) < 0)
				return false;

	        $row = $sqlca->fetchrow();

			if ($row['nu_total_documento_referencias'] <= 0)
				return false;
			else
				return true;
		/*
		}else{
			return false;
		}
		return true;
		*/
	}

	function verificarRelacionDocumentos($numero_docu) {
        global $sqlca;

        $sql = "
SELECT
 nu_tipo_pago
FROM
 fac_ta_factura_cabecera
WHERE
 ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento IN (
 SELECT
  ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento
 FROM
  fac_ta_factura_complemento
 WHERE
  (string_to_array(ch_fac_observacion2, '*'))[3]||' - '||(string_to_array(ch_fac_observacion2, '*'))[2]||' - '||(string_to_array(ch_fac_observacion2, '*'))[1] IN (". $numero_docu .")
 );
		";

	    $iStatusSQL = $sqlca->query($sql);
	    $arrResponse = array(
	        'status_sql' => $iStatusSQL,
	        'message_sql' => $sqlca->get_error(),
	        'sStatus' => 'danger',
	        'sMessage' => 'problemas al verificar documento de referencia',
	    );
	    if ( $iStatusSQL == 0 ) {
	        $arrResponse = array(
	            'sStatus' => 'warning',
	            'sMessage' => 'No hay registros'
	        );
	    } else if ( $iStatusSQL > 0 ) {
	        $arrRow = $sqlca->fetchrow();
	        if ( trim($arrRow["nu_tipo_pago"]) == "06" ) {// 06 = Crédito
				$arrResponse = array(
					'sStatus' => 'error',
					'sMessage' => 'Seleccionar correctamente documento segun la referencia',
				);
/*
		        $query = "
		SELECT
		 COUNT(*) as verificar
		FROM
		 ccob_ta_detalle
		WHERE
		 --ch_tipmovimiento !='1'
		 --AND 
		 ch_tipdocumento||ch_seriedocumento||ch_numdocumento IN (
		 SELECT
		  ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento
		 FROM
		  fac_ta_factura_complemento
		 WHERE
		  ch_fac_tipodocumento IN ('11','20')
		  AND (string_to_array(ch_fac_observacion2, '*'))[3]||' - '||(string_to_array(ch_fac_observacion2, '*'))[2]||' - '||(string_to_array(ch_fac_observacion2, '*'))[1] IN (". $numero_docu .")
		);
				";

				if($sqlca->query($query) < 0)
					return false;

		        $data = $sqlca->fetchrow();

				if ($data['verificar'] == 0){
					*/
			        $row = Array();
					$datarows = Array();

					$sql = "
		SELECT
		 ch_fac_tipodocumento AS nu_tipo,
		 ch_fac_seriedocumento AS nu_serie,
		 ch_fac_numerodocumento AS nu_documento
		FROM
		 fac_ta_factura_cabecera
		WHERE
		 ch_fac_tipodocumento IN ('11','20')
		 AND ch_fac_tipodocumento||' - '||ch_fac_seriedocumento||' - '||ch_fac_numerodocumento IN (" . $numero_docu . ")
		ORDER BY
		 ch_fac_tipodocumento,
		 ch_fac_seriedocumento,
		 ch_fac_numerodocumento
					";

					if($sqlca->query($sql) < 0)
						return false;

					$row = Array();

					for ($i = 0; $i < $sqlca->numrows(); $i++) {
				    	$data = $sqlca->fetchRow();
				    	$row[$i]['nu_tipo'] 		= $data['nu_tipo'];
				    	$row[$i]['nu_serie'] 		= $data['nu_serie'];
				    	$row[$i]['nu_documento'] 	= $data['nu_documento'];
					}

					$sql2 = "
		SELECT
		 ch_fac_tipodocumento AS nu_tipo,
		 ch_fac_seriedocumento AS nu_serie,
		 ch_fac_numerodocumento AS nu_documento
		FROM
		 fac_ta_factura_complemento
		WHERE
		 ch_fac_tipodocumento IN ('11','20')
		 AND (string_to_array(ch_fac_observacion2, '*'))[3]||' - '||(string_to_array(ch_fac_observacion2, '*'))[2]||' - '||(string_to_array(ch_fac_observacion2, '*'))[1] IN (" . $numero_docu . ")
		ORDER BY
		 ch_fac_tipodocumento,
		 ch_fac_seriedocumento,
		 ch_fac_numerodocumento
					";

/*
					echo "<pre>";
					print_r($sql2);
					echo "</pre>";
*/

					if($sqlca->query($sql2) < 0)
						return false;

			        $row2 = Array();

					for ($i = 0; $i < $sqlca->numrows(); $i++) {
				    	$data = $sqlca->fetchRow();
				    	$row2[$i]['nu_tipo'] 		= $data['nu_tipo'];
				    	$row2[$i]['nu_serie'] 		= $data['nu_serie'];
				    	$row2[$i]['nu_documento'] 	= $data['nu_documento'];
					}

					$mensaje = Array();
					$cantidad_error = 0;

					for ($g = 0; $g < count($row2); $g++) {
						if (
							trim($row[0]['nu_tipo']) == trim($row2[$g]['nu_tipo']) &&
							trim($row[0]['nu_serie']) == trim($row2[$g]['nu_serie']) &&
							trim($row[0]['nu_documento']) == trim($row2[$g]['nu_documento'])
						){
							$arrResponse = array(
								'sStatus' => 'success',
								'sMessage' => 'Datos encontrados',
							);
						}
					}
				//}// /. Validamos que el documento de referencia de N/C y N/D exista
			} else {
				$arrResponse = array(
					'sStatus' => 'success',
					'sMessage' => 'No se verifica información por ser una N/C o N/D en efectivo',
				);
			}
		}// /. Validando N/C o Débito en EFECTIVO
		return $arrResponse;
	}

	function DataCuentasCobrarDetalleRecivo($cli_codigo, $numero_docu) {
        global $sqlca;

		$query = "
SELECT
 cli_codigo,
 ch_seriedocumento ||'-'|| ch_numdocumento AS serie_num,
 dt_fechaemision,
 dt_fechavencimiento,
 mone.tab_desc_breve AS moneda,
 nu_importetotal,
 nu_importesaldo,
 ch_numdocreferencia,
 ch_moneda,
 ch_tipdocumento,
 gen.tab_desc_breve AS tipo
FROM
 ccob_ta_cabecera AS c
 LEFT JOIN int_tabla_general AS gen ON(c.ch_tipdocumento = substring(TRIM(tab_elemento) for 2 from length(TRIM(tab_elemento))-1) and tab_tabla ='08')
 LEFT JOIN int_tabla_general AS mone ON (mone.tab_tabla ='04' and mone.tab_elemento != '000000' AND mone.tab_elemento = (LPAD(CAST(c.ch_moneda AS bpchar),6,'0')))
WHERE
 nu_importesaldo > 0
 AND trim(cli_codigo) = trim('" . $cli_codigo . "')
 AND ch_tipdocumento ||' - '|| ch_seriedocumento ||' - '|| ch_numdocumento IN($numero_docu)
ORDER BY
 ch_tipdocumento,
 ch_seriedocumento,
 ch_numdocumento;
		";
/*
		echo "<pre>";
		echo $query;
		echo "</pre>";
*/
		if($sqlca->query($query) < 0)
			return false;

    	$resultado = array();

    	for ($i = 0; $i < $sqlca->numrows(); $i++) {
	    	$a = $sqlca->fetchRow();
	    	$resultado[$i]['cli_codigo'] 			= $a[0];
	    	$resultado[$i]['serie_num'] 			= $a[1];
	    	$resultado[$i]['dt_fechaemision'] 		= $a[2];
	    	$resultado[$i]['dt_fechavencimiento'] 	= $a[3];
	    	$resultado[$i]['moneda'] 				= $a[4];
	    	$resultado[$i]['nu_importetotal'] 		= $a[5];
	    	$resultado[$i]['nu_importesaldo'] 		= $a[6];
	    	$resultado[$i]['ch_numdocreferencia'] 	= $a[7];
	    	$resultado[$i]['cod_moneda'] 			= $a[8];
	    	$resultado[$i]['tipo_emitido'] 			= $a[9];
	    	$resultado[$i]['tipo'] 					= $a[10];
		}
        return $resultado;
	}

	function DataCuentasCobrar($cli_codigo) {
        global $sqlca;

    	$query = "
    	SELECT
			c.cli_codigo,
			ch_tipdocumento ||' - '|| ch_seriedocumento ||' - '|| ch_numdocumento AS serie_num,
			dt_fechaemision,
			dt_fechavencimiento,
			mone.tab_desc_breve as moneda,
			nu_importetotal,
			nu_importesaldo,
			gen.tab_desc_breve as tipo,
			substring(mone.tab_elemento,5,2) as cod_moneda,
			ch_seriedocumento ||' - '|| ch_numdocumento AS serie_num2,
			tipo_ref.tab_desc_breve AS no_tipo_ref,
			(string_to_array(com.ch_fac_observacion2, '*'))[3] as nu_tipo_ref,
			(string_to_array(com.ch_fac_observacion2, '*'))[2] as nu_serie_ref,
			(string_to_array(com.ch_fac_observacion2, '*'))[1] as nu_numero_ref
		FROM
			ccob_ta_cabecera c
			LEFT JOIN int_tabla_general gen ON(c.ch_tipdocumento = substring(TRIM(tab_elemento) for 2 from length(TRIM(tab_elemento))-1) and tab_tabla ='08')
			LEFT JOIN int_tabla_general mone ON (mone.tab_tabla ='04' and mone.tab_elemento != '000000' AND mone.tab_elemento = (LPAD(CAST(c.ch_moneda AS bpchar),6,'0')))
			LEFT JOIN fac_ta_factura_complemento AS com ON(c.ch_tipdocumento = com.ch_fac_tipodocumento AND c.ch_seriedocumento  = com.ch_fac_seriedocumento AND c.ch_numdocumento = com.ch_fac_numerodocumento)
			LEFT JOIN int_tabla_general tipo_ref ON((string_to_array(com.ch_fac_observacion2, '*'))[3] = substring(TRIM(tipo_ref.tab_elemento) for 2 from length(TRIM(tipo_ref.tab_elemento))-1) and tipo_ref.tab_tabla ='08')
		WHERE
			c.nu_importesaldo > 0
			AND trim(c.cli_codigo) = trim('$cli_codigo')
		ORDER BY
			ch_tipdocumento, nu_importetotal asc, serie_num;
		";
/*
		echo "<pre>";
		echo $query;
		echo "</pre>";
*/
		if ($sqlca->query($query) < 0)
			return false;

    	$resultado = array();

    	for ($i = 0; $i < $sqlca->numrows(); $i++) {
	    	$a = $sqlca->fetchRow();
	    	$resultado[$i]['cli_codigo'] 			= $a[0];
	    	$resultado[$i]['serie_num'] 			= $a[1];
	    	$resultado[$i]['dt_fechaemision'] 		= $a[2];
	    	$resultado[$i]['dt_fechavencimiento'] 	= $a[3];
	    	$resultado[$i]['moneda'] 				= $a[4];
	    	$resultado[$i]['nu_importetotal'] 		= $a[5];
			$resultado[$i]['nu_importesaldo'] 		= $a[6];
			$resultado[$i]['tipo'] 					= $a[7];
			$resultado[$i]['cod_moneda'] 			= $a[8];
			$resultado[$i]['serie_num2'] 			= $a[9];
			$resultado[$i]['no_tipo_ref'] 			= $a[10];
			$resultado[$i]['nu_tipo_ref'] 			= $a[11];
			$resultado[$i]['nu_serie_ref'] 			= $a[12];
			$resultado[$i]['nu_numero_ref'] 		= $a[13];
		}

		return $resultado;
	}

	function ReporteSubContometros($fecha, $fecha2, $estacion) {
        	global $sqlca;

       		$query = "SELECT
				round(sum(c.volume),2) cantidad,
				round(sum(c.volume * price),2) importe,
				p.ch_nombrebreve producto
			FROM
				f_grade m
				JOIN f_totalizerm c ON(c.f_grade_id = m.f_grade_id)
				JOIN comb_ta_combustibles p ON(m.product = p.ch_codigocombustible)
			WHERE
				c.systemdate BETWEEN to_date('$fecha', 'DD/MM/YYYY') and to_date('$fecha2', 'DD/MM/YYYY')
				AND c.warehouse = '$estacion'
			GROUP BY
				p.ch_nombrebreve;";

        echo $query;

        if ($sqlca->query($query) < 0)
            return false;

        $resultado = array();

        for ($i = 0; $i < $sqlca->numrows(); $i++) {
            $a = $sqlca->fetchRow();
            $resultado[$i]['cantidad'] = $a[0];
            $resultado[$i]['importe'] = $a[1];
            $resultado[$i]['producto'] = $a[2];
        }

        return $resultado;
    }

	function validaDia($dia) {
		global $sqlca;
		$turno = 0;
		$almacen = $_SESSION['almacen'];
		$sql = "SELECT validar_consolidacion('" . $dia . "', " . $turno . ",'" . $almacen . "');";
		$sqlca->query($sql);
		$estado = $sqlca->fetchRow();
		if($estado[0] == 1){
			return 1;//Consolidado
		}
		return 0;//No consolidado
	}

	function obtenerDiaInicialSistema() {
		global $sqlca;
		$sql = "SELECT da_fecha FROM pos_aprosys WHERE ch_poscd='S' ORDER BY da_fecha ASC LIMIT 1";
		$sqlca->query($sql);
		$row = $sqlca->fetchRow();
		return $row['da_fecha'];
	}

	function Insertarc_cash_transaction($createdby, $type, $d_system, $transaction, $c_cash_id, $c_cash_operation_id, $reference, $bpartner, $c_currency_id, $rate, $amount, $ware_house) {
		global $sqlca;

		try {
			$query="
				INSERT INTO c_cash_transaction(
							    	createdby,
							    	type,
							    	d_system,
							    	transaction,
							    	c_cash_id,
							    	c_cash_operation_id,
							    	reference,
							    	bpartner,
							    	c_currency_id,
							    	rate,
							    	amount,
							    	ware_house
				)VALUES(
								'$createdby',
								'$type',
								'$d_system',
								'$transaction',
								$c_cash_id,
								$c_cash_operation_id,
				    				'$reference',
								'$bpartner',
								'$c_currency_id',
								'$rate',
								$amount,
								'$ware_house'
				);";

			if ($sqlca->query($query) < 0) {
				throw new Exception("Error al insertar Cabecera de caja.");
			}

		} catch (Exception $e) {
			throw $e;
		}

	}

	function Insertarc_cash_transaction_detail($doc_type, $doc_serial_number, $doc_number, $reference, $amount, $c_currency_id) {
		global $sqlca;

		try {
			$query="
				INSERT INTO c_cash_transaction_detail(
									c_cash_transaction_id,
								    	createdby,
								    	doc_type,
								   	doc_serial_number,
								   	doc_number,
								   	reference,
								   	amount,
									c_currency_id
				)VALUES(
									(select CURRVAL('seq_c_cash_transaction_id')),
									'0',
									'$doc_type',
									'$doc_serial_number',
									'$doc_number',
									'$reference',
									'$amount',
									'$c_currency_id'
				);";

			if ($sqlca->query($query) < 0) {
				throw new Exception("Error al insertar en caja detalle.");
			}
		} catch (Exception $e) {
			throw $e;
		}

	}

	function Insertarc_cash_transaction_payment($c_cash_mpayment_id, $pay_number, $c_bank_id, $c_bank_account_id, $c_currency_id, $amount, $fecha_pay) {
		global $sqlca;

		try {
			$query = "INSERT INTO
					c_cash_transaction_payment(
									c_cash_transaction_id,
									createdby,
								    	c_cash_mpayment_id,
								    	pay_number,
								   	c_bank_id,
								    	c_bank_account_id,
								    	c_currency_id,
								    	amount,
									created
					)VALUES(
									(select CURRVAL('seq_c_cash_transaction_id')),
									'0',
									'$c_cash_mpayment_id',
									'$pay_number',
									'$c_bank_id',
									'$c_bank_account_id',
									'$c_currency_id',
									'$amount',
									'$fecha_pay'
					);
				";

			//echo $query;

			if ($sqlca->query($query) < 0) {
				throw new Exception("Error al insertar en medio de pago.");
			}
		} catch (Exception $e) {
			throw $e;
		}
	}

	function capturar_secuencia($ch_tipdocumento, $ch_seriedocumento, $ch_numdocumento, $cli_codigo) {
        global $sqlca;
        $sql = "
        SELECT
         max(ch_identidad::INTEGER) AS maximo
        FROM
         ccob_ta_detalle 
        WHERE
         ch_seriedocumento = '" . $ch_seriedocumento . "'
         AND ch_numdocumento = '" . $ch_numdocumento . "'
         AND cli_codigo = '" . $cli_codigo . "'
         AND ch_tipmovimiento!='1'
        LIMIT 1";

        if ($sqlca->query($sql) < 0) {
            throw new Exception("Error al insertar Cuentas x cobrar.");
        }

        $a = $sqlca->fetchRow();
        $secuencia = 0;
        if (is_null($a['maximo'])) {
            $secuencia = 2;
        } else {
            $secuencia = $a['maximo'] + 1;
        }
        return $secuencia;
    }

	function Insertarc_cuentas_x_cobrar_detalle($cli_codigo, $ch_tipdocumento, $ch_seriedocumento, $ch_numdocumento, $ch_identidad, $ch_tipmovimiento, $dt_fechamovimiento, $ch_moneda, $nu_tipocambio, $nu_importemovimiento, $ch_numdocreferencia, $ch_sucursal, $ch_glosa, $dt_fecha_actualizacion, $nu_serie, $nu_documento, $i, $all_data_documentos, $monto_pago_total, $arrParams){
        global $sqlca;

		try {
            $secuencia = RegistroCajasModel::capturar_secuencia($tipo_doc, $ch_seriedocumento, $ch_numdocumento, $cli_codigo);

        	$query = "
INSERT INTO ccob_ta_detalle(
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
 ch_numdocreferencia,
 ch_sucursal,
 ch_glosa,
 dt_fecha_actualizacion,
 rate_gn
)VALUES(
 '" . $cli_codigo . "',
 '" . $ch_tipdocumento . "',
 '" . $ch_seriedocumento . "',
 '" . $ch_numdocumento . "',
 " . $secuencia . ",
 '" . $ch_tipmovimiento . "',
 '" . $dt_fechamovimiento . "',
 '" . $ch_moneda . "',
 '" . $nu_tipocambio . "',
 " . $nu_importemovimiento . ",
 '" . $ch_numdocreferencia . "',
 '" . $ch_sucursal . "',
 '',
 now(),
 " . $arrParams['fTotalRetencion'] . "
);
			";

			if ($sqlca->query($query) < 0)
				throw new Exception("Error al insertar Cuentas x cobrar");

			$sql_update = "
UPDATE
 c_cash_transaction_detail
SET
 createdby='" . $secuencia . "'
WHERE
 c_cash_transaction_id IN(SELECT MAX(c_cash_transaction_id) FROM c_cash_transaction)
 AND doc_type='" . $ch_tipdocumento . "'
 AND doc_serial_number='" . $ch_seriedocumento . "'
 AND doc_number='" . $ch_numdocumento . "';
			";

			if ($sqlca->query($sql_update) < 0)
				throw new Exception("Error al UPDATE c_cash_transaction.");
  		} catch (Exception $e) {
			throw $e;
		}
	}

    function Insertarc_Cuenta_bancaria($c_bank_account_id, $c_bank_id, $name, $initials) {
        global $sqlca;
        try {
            $query = " INSERT INTO c_bank_account 
                    (c_bank_account_id,c_bank_id,created,createdby,c_currency_id,name,initials)
                   VALUES($c_bank_account_id,'$c_bank_id',now(),0,1,'$name','$initials');";



            if ($sqlca->query($query) < 0) {
                throw new Exception("Error al insertar Cuenta bancaria($c_bank_account_id).");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

	function Anular_Registro_Ingreso_Caja_solo_cliente($id_transacion, $tipo_accion, $nu_almacen) {
		global $sqlca;

		try {

			$query = "
			SELECT
				c_cash_transaction_id,
				c_cash_operation_id
			FROM
				c_cash_transaction
			WHERE
				type 						= '0'
				AND c_cash_transaction_id 	= " . $id_transacion . "
				AND ware_house 				= '".$nu_almacen."';
			";

			//echo $query;

			if ($sqlca->query($query) < 0)
				throw new Exception("Error al buscar c_cash_transaction ($id_transacion).");

			$rowsql 				= $sqlca->fetchRow();
			$c_cash_transaction_id 	= $rowsql[0];
			$c_cash_operation_id 	= $rowsql['c_cash_operation_id']; //1 SOLO PARA CLIENTES

			if ($c_cash_operation_id == "1") {

				$sql = "
					SELECT
						c_cash_transaction_detail_id,
						doc_type,
						doc_serial_number,
						doc_number,
						createdby,
						created,
						(SELECT round(sum(amount),2) FROM c_cash_transaction_payment WHERE c_cash_transaction_id = $c_cash_transaction_id) as amount
			                FROM
						c_cash_transaction_detail
					WHERE
						c_cash_transaction_id = $c_cash_transaction_id;

					";

				if ($sqlca->query($sql) < 0){
					throw new Exception("Error al buscar c_cash_transaction_detail ($c_cash_transaction_id).");
				}

				$datarow = array();

				while ($rowtrasanction = $sqlca->fetchRow()) {
					$datarow[] = $rowtrasanction;
				}

				foreach ($datarow as $row) {

					$id_detalle_trnsation 	= $row['c_cash_transaction_detail_id'];
					$doc_type 				= trim($row['doc_type']);
					$doc_serial_number		= trim($row['doc_serial_number']);
					$doc_number 			= trim($row['doc_number']);
					$createdby 				= trim($row['createdby']);
					$created 				= trim($row['created']);
					$amount 				= trim($row['amount']);

					$nu_monto_nota_credito = 0;

		        	if($doc_type == '10'){

			        	$get_nota_credito = "
				        	SELECT
								nu_fac_valortotal
							FROM
								fac_ta_factura_cabecera
							WHERE
								ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo = (
							SELECT
								ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo
							FROM
								fac_ta_factura_complemento
							WHERE
								cli_codigo = '$cli_codigo'
								AND (string_to_array(ch_fac_observacion2, '*'))[3] = '$td'
								AND (string_to_array(ch_fac_observacion2, '*'))[2] = '$ch_seriedocumento'
								AND (string_to_array(ch_fac_observacion2, '*'))[1] = '$ch_numdocumento'
							);
						";

						$sqlca->query($get_nota_credito);

				        $row 					= $sqlca->fetchRow();
				        $nu_monto_nota_credito 	= $row[0];

				    }

					$query = "
						UPDATE
							ccob_ta_cabecera
						SET
							nu_importesaldo = (nu_importesaldo + ((
												SELECT
													SUM(nu_importemovimiento)
												FROM
													ccob_ta_detalle
												WHERE
													ch_tipdocumento				= '$doc_type'
													AND ch_seriedocumento 		= '$doc_serial_number'
													AND ch_numdocumento 		= '$doc_number'
													AND ch_identidad 			= '$createdby'
													AND dt_fecha_actualizacion 	= '$created'
													AND ch_tipmovimiento 		!= '1')
												) + " . $nu_monto_nota_credito . " )
											)
						WHERE
						
							ch_tipdocumento			= '$doc_type'
							AND ch_seriedocumento 	= '$doc_serial_number'
							AND ch_numdocumento 	= '$doc_number';
						";

					if ($sqlca->query($sql) < 0){
						throw new Exception("Error al actualizar monto ccob_ta_cabecera");
					}

					$sql = "
						DELETE FROM
							ccob_ta_detalle 
						WHERE
							ch_tipdocumento				= '$doc_type'
							AND ch_seriedocumento 		= '$doc_serial_number'
							AND ch_numdocumento 		= '$doc_number'
							AND ch_identidad 			= '$createdby'
							AND dt_fecha_actualizacion 	= '$created'
							AND ch_tipmovimiento 		!= '1';
					";

					if ($sqlca->query($sql) < 0){
						throw new Exception("Error al eliminar  ccob_ta_detalle($doc_serial_number*$doc_number*$reference*$amount)");
					}

					$sql = "
						DELETE FROM
							c_cash_transaction_payment
						WHERE
							c_cash_transaction_id IN(SELECT c_cash_transaction_id FROM c_cash_transaction WHERE type='0' AND c_cash_transaction_id IN('$id_transacion'))
						";

					if ($sqlca->query($sql) < 0){
						throw new Exception("Error al eliminar  c_cash_transaction_payment");
					}

					$sql = "
						DELETE FROM
							c_cash_transaction_detail
						WHERE
							c_cash_transaction_id IN('$id_transacion')
					";

					if ($sqlca->query($sql) < 0){
						throw new Exception("Error al eliminar  c_cash_transaction_detail");
					}

				}

				$sql = "
					DELETE FROM
						c_cash_transaction
					WHERE
						type 						= 0
						AND ware_house 				= '" . $nu_almacen . "'
						AND c_cash_transaction_id 	= " . $id_transacion . ";
					";

				if ($sqlca->query($sql) < 0){
					throw new Exception("Error al eliminar  c_cash_transaction");
				}

			} else {

				//ELIMINA TICKETS DE VENTAS**************************************************

				$sql_delete = "DELETE FROM c_cash_transaction_payment WHERE c_cash_transaction_id = " . $c_cash_transaction_id . ";";

				if ($sqlca->query($sql_delete) < 0) {
				    throw new Exception("Error al eliminar  c_cash_transaction_payment");
				} else {
					$sql_delete = "DELETE FROM c_cash_transaction_detail WHERE c_cash_transaction_id = " . $c_cash_transaction_id . ";";
					if ($sqlca->query($sql_delete) < 0) {
					    throw new Exception("Error al eliminar  c_cash_transaction_detail");
					} else {
						$sql_delete = "DELETE FROM c_cash_transaction WHERE ware_house = '" . $nu_almacen . "' AND type = 0 AND c_cash_transaction_id = " . $id_transacion . ";";
						if ($sqlca->query($sql_delete) < 0) {
						    throw new Exception("Error al eliminar  c_cash_transaction");
						}
					}
				}
				
				//**************************************************
			}


        } catch (Exception $e) {
            throw $e;
        }
    }

    function Actualizarpayment($id_cash_payment, $pay_number) {
        global $sqlca;
        try {//-$monto
            $query = " 
                UPDATE c_cash_transaction_payment set pay_number='$pay_number' where c_cash_transaction_payment_id =$id_cash_payment;
                ;";


            if ($sqlca->query($query) < 0) {
                throw new Exception("Error al actualizar payment.");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

	function ActualizarMontosReferenciaCabecera($arrData) {
        global $sqlca;

        try {
	        // 1. Obtener datos de referencia
	        $sql = "
SELECT
 (string_to_array(ch_fac_observacion2, '*'))[3] AS nu_tipo_documento_referencia,
 (string_to_array(ch_fac_observacion2, '*'))[2] AS no_serie_documento_referencia,
 (string_to_array(ch_fac_observacion2, '*'))[1] AS nu_numero_documento_referencia
FROM
 fac_ta_factura_complemento
WHERE
 ch_fac_tipodocumento = '" . $arrData['sTipoDocumento'] . "'
 AND ch_fac_seriedocumento = '" . $arrData['sSerieDocumento'] . "'
 AND ch_fac_numerodocumento = '" . $arrData['sNumeroDocumento'] . "'
 AND cli_codigo = '" . $arrData['sIdEntidad'] . "'
LIMIT 1;
			";
	    	$iStatusSQL = $sqlca->query($sql);
	    	if ( (int)$iStatusSQL < 0 ) {
	            return array(
	                'sStatus' => 'danger',
	                'sMessage' => 'Problemas al obtener referencia',
	                'sMessageSQL' => $sqlca->get_error(),
	                'SQL' => $sql,
	            );
	    	}

	    	$row = $sqlca->fetchRow();
	    	$sTipoReferencia = $row['nu_tipo_documento_referencia'];
	    	$sSerieReferencia = $row['no_serie_documento_referencia'];
	    	$sNumeroReferencia = $row['nu_numero_documento_referencia'];

	    	// 2. Actualizar monto según referencia encontrada por N/C
			$sql = "
UPDATE
 ccob_ta_cabecera
SET
 nu_importesaldo = nu_importesaldo - " . $arrData['fAmountHeader'] . "
WHERE
 ch_tipdocumento = '" . $sTipoReferencia . "'
 AND ch_seriedocumento = '" . $sSerieReferencia . "'
 AND ch_numdocumento = '" . $sNumeroReferencia . "'
 AND cli_codigo = '" . $arrData['sIdEntidad'] . "';
			";

	    	$iStatusSQL = $sqlca->query($sql);
	    	if ( (int)$iStatusSQL < 0 ) {
	            return array(
	                'sStatus' => 'danger',
	                'sMessage' => 'Problemas al actualizar saldo - cabecera',
	                'sMessageSQL' => $sqlca->get_error(),
	                'SQL' => $sql,
	            );
	    	}

	        return array(
	            'sStatus' => 'success',
	            'sMessage' => 'Registro actualizado saldo - cabecera',
	        );
        } catch (Exception $e) {
            throw $e;
        }
    }


    function fecha_aprosys() {
       	global $sqlca;

        $query = "
                  select da_fecha from pos_aprosys order by da_fecha desc limit 1;";
        if ($sqlca->query($query) <= 0) {
            return false;
        }
        $resultado = array();
        $a = $sqlca->fetchRow();
        $resultado['da_fecha'] = $a[0];


        return $resultado['da_fecha'];
    }

    function ReporteCuentasBancarias() {
        global $sqlca;

        $query = "
                  SELECT 
                        cba.c_bank_account_id,cb.initials,cba.name ,cba.initials
                        FROM c_bank_account cba inner join c_bank cb on cba.c_bank_id=cb.c_bank_id 
                 ORDER BY cba.c_bank_id;";
        if ($sqlca->query($query) <= 0) {
            return false;
        }

        $resultado = array();

        for ($i = 0; $i < $sqlca->numrows(); $i++) {
            $a = $sqlca->fetchRow();
            $resultado[$i]['c_bank_account_id'] = $a[0];
            $resultado[$i]['initials'] = $a[1];
            $resultado[$i]['name'] = $a[2];
            $resultado[$i]['initials_cl'] = $a[3];
        }

        return $resultado;
    }

    function DetalleReporteRecibo($id_recibo) {
        global $sqlca;

        $query = "
        SELECT
            cct.ware_house,
            cct.transaction,
            cct.d_system,
            cs.name,
            cso.name,
            cct.reference,
            ic.cli_razsocial
        FROM
			c_cash_transaction AS cct  
			LEFT JOIN int_clientes AS ic ON (cct.bpartner = ic.cli_codigo)
			INNER JOIN c_cash AS cs ON (cct.c_cash_id = cs.c_cash_id)
			INNER JOIN c_cash_operation AS cso ON (cct.c_cash_operation_id = cso.c_cash_operation_id)
        WHERE
			cct.c_cash_transaction_id = '" . $id_recibo . "'
			AND cct.type = '0'
		LIMIT 1;
		";

        if ($sqlca->query($query) <= 0) {
            return false;
        }

        $resultado = array();

        for ($i = 0; $i < $sqlca->numrows(); $i++) {
            $a = $sqlca->fetchRow();
            $resultado[$i]['ware_house'] = $a[0];
            $resultado[$i]['transaction'] = $a[1];
            $resultado[$i]['d_system'] = $a[2];
            $resultado[$i]['name_caja'] = $a[3];
            $resultado[$i]['name_ope'] = $a[4];
            $resultado[$i]['reference'] = $a[5];
            $resultado[$i]['cli_razsocial'] = $a[6];
        }

        return $resultado;
    }

	function DetalleReporteRecibo_complemento_registro($id_recibo) {
        global $sqlca;

        $sql = "
		SELECT
			gen.tab_desc_breve||' - '||cstd.doc_serial_number ||' - '||cstd.doc_number as serie,
	        cstd.reference,
	        mone.tab_desc_breve as moneda,
	        cstd.amount,
			cab.nu_importesaldo
		FROM
			c_cash_transaction cct 
		    INNER JOIN c_cash_transaction_detail cstd ON (cct.c_cash_transaction_id=cstd.c_cash_transaction_id)
			LEFT JOIN ccob_ta_cabecera cab ON (cstd.doc_type = cab.ch_tipdocumento AND cstd.doc_serial_number = cab.ch_seriedocumento AND cstd.doc_number = cab.ch_numdocumento)
			LEFT JOIN int_tabla_general mone ON (mone.tab_tabla ='04' and mone.tab_elemento != '000000' AND mone.tab_elemento = (LPAD(CAST(cstd.c_currency_id AS bpchar),6,'0')))
			LEFT JOIN int_tabla_general gen ON(cstd.doc_type = substring(TRIM(gen.tab_elemento) for 2 from length(TRIM(gen.tab_elemento))-1) AND gen.tab_tabla ='08' AND gen.tab_elemento != '000000')
		WHERE
			cct.c_cash_transaction_id = '$id_recibo'
			AND cct.type = '0';
		";

    	if ($sqlca->query($sql) < 0)
        	return false;
        $resultado = array();

        for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
	    	$resultado[$i]['document'] 	= $a[0];
	    	$resultado[$i]['reference'] 	= $a[1];
	    	$resultado[$i]['moneda'] 	= $a[2];
	    	$resultado[$i]['amount'] 	= $a[3];
	    	$resultado[$i]['saldo'] 	= $a[4];
		}
		return $resultado;
	}

	function DetalleReporteRecibo_medio_pago($id_recibo) {
        	global $sqlca;

		$query = "
		SELECT
	        csm.name,
			cstd.pay_number,
			cstd.created,
			(CASE WHEN cstd.c_cash_mpayment_id != 4 THEN b.name::TEXT ELSE '' END) AS name,
			(CASE WHEN cstd.c_cash_mpayment_id != 4 THEN cba.c_bank_account_id::TEXT ELSE '' END) AS c_bank_account_id,
            mone.tab_desc_breve moneda,
	        cstd.amount,
	        cstd.c_cash_transaction_payment_id,
			cct.rate,
			cstd.c_currency_id cod_moneda_pay
		FROM
			c_cash_transaction cct 
	        INNER JOIN c_cash_transaction_payment cstd ON (cct.c_cash_transaction_id = cstd.c_cash_transaction_id)
	        INNER JOIN c_cash_mpayment csm ON (cstd.c_cash_mpayment_id = csm.c_cash_mpayment_id)
	        LEFT JOIN c_bank b ON (cstd.c_bank_id = b.c_bank_id)
	        LEFT JOIN c_bank_account cba ON (cstd.c_bank_account_id = cba.c_bank_account_id)
			LEFT JOIN int_tabla_general mone ON (mone.tab_tabla ='04' and mone.tab_elemento != '000000' AND mone.tab_elemento = (LPAD(CAST(cstd.c_currency_id AS bpchar),6,'0')))
		WHERE
			cct.c_cash_transaction_id = '" . $id_recibo . "'
			AND cct.type = '0';
		";

/*echo "<pre>";
echo $query;
echo "</pre>";*/

		if ($sqlca->query($query) <= 0)
			return false;

        	$resultado = array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    $a = $sqlca->fetchRow();
	    	$resultado[$i]['tipo_mp'] 		= $a[0];
	    	$resultado[$i]['pay_number'] 	= $a[1];
			$resultado[$i]['created'] 		= $a[2];
			$resultado[$i]['banco'] 		= $a[3];
	    	$resultado[$i]['cuenta_banco'] 	= $a[4];
	    	$resultado[$i]['moneda'] 		= $a[5];
	    	$resultado[$i]['importe'] 		= $a[6];
	    	$resultado[$i]['id_pay'] 		= $a[7];
	    	$resultado[$i]['rate'] 			= $a[8];
	    	$resultado[$i]['cod_moneda_pay'] = $a[9];
		}

		return $resultado;

	}

	function Verify_Amount_CxB($tipo, $serie, $numero) {
        global $sqlca;

        $nu_importe_nc 	= 0.00;
        $nu_importe_fc 	= 0.00;
        $nu_importe 	= 0.00;

		$sql = "
SELECT
 nu_fac_valortotal AS nu_importe_nc
FROM
 fac_ta_factura_cabecera
WHERE
 ch_fac_tipodocumento = '20'
 AND ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo IN (
 SELECT
  ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo
 FROM
  fac_ta_factura_complemento
 WHERE
  (string_to_array(ch_fac_observacion2, '*'))[3] = '".$tipo."'
  AND (string_to_array(ch_fac_observacion2, '*'))[2] = '".$serie."'
  AND (string_to_array(ch_fac_observacion2, '*'))[1] = '".$numero."'
)
		";
/*
echo "<pre>";
echo $sql;
echo "</pre>";
*/
		if($sqlca->query($sql) < 0)
			return false;

        $row = $sqlca->fetchrow();
		$nu_importe_nc = $row['nu_importe_nc'];

		$sql = "
SELECT
 nu_fac_valortotal AS nu_importe_fc
FROM
 fac_ta_factura_cabecera
WHERE
 ch_fac_tipodocumento = '".$tipo."'
 AND ch_fac_seriedocumento = '".$serie."'
 AND ch_fac_numerodocumento = '".$numero."'
		";
/*
echo "<pre>";
echo $sql;
echo "</pre>";
*/
		if($sqlca->query($sql) < 0)
			return false;

        $row 			= $sqlca->fetchrow();
		$nu_importe_fc 	= $row['nu_importe_fc'];
		//echo "NC"; var_dump($nu_importe_nc);
		//echo "FC"; var_dump($nu_importe_fc);
		//$nu_importe 	= $nu_importe_fc - $nu_importe_nc;//Antes < 2019-02-20
		$nu_importe = $nu_importe_nc;//Ahora
		return $nu_importe;
	}

	function Verify_Amount_ND($tipo, $serie, $numero) {
        global $sqlca;

        $nu_importe_nd 	= 0.00;
        $nu_importe_fc 	= 0.00;
        $nu_importe 	= 0.00;

		$sql = "
SELECT
 nu_fac_valortotal AS nu_importe_nd
FROM
 fac_ta_factura_cabecera
WHERE
 ch_fac_tipodocumento = '11'
 AND ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo IN (
 SELECT
  ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo
 FROM
  fac_ta_factura_complemento
 WHERE
  (string_to_array(ch_fac_observacion2, '*'))[3] = '".$tipo."'
  AND (string_to_array(ch_fac_observacion2, '*'))[2] = '".$serie."'
  AND (string_to_array(ch_fac_observacion2, '*'))[1] = '".$numero."'
)
		";
/*
echo "<pre>";
echo $sql;
echo "</pre>";
*/
		if($sqlca->query($sql) < 0)
			return false;

        $row = $sqlca->fetchrow();
		$nu_importe_nd = $row['nu_importe_nd'];

		$sql = "
SELECT
 nu_fac_valortotal AS nu_importe_fc
FROM
 fac_ta_factura_cabecera
WHERE
 ch_fac_tipodocumento = '".$tipo."'
 AND ch_fac_seriedocumento = '".$serie."'
 AND ch_fac_numerodocumento = '".$numero."'
		";
/*
echo "<pre>";
echo $sql;
echo "</pre>";
*/
		if($sqlca->query($sql) < 0)
			return false;

        $row 			= $sqlca->fetchrow();
		$nu_importe_fc 	= $row['nu_importe_fc'];
		//echo "NC"; var_dump($nu_importe_nd);
		//echo "FC"; var_dump($nu_importe_fc);
		//$nu_importe 	= $nu_importe_fc - $nu_importe_nd;//Antes < 2019-02-20
		$nu_importe = $nu_importe_nd;//Ahora
		return $nu_importe;
	}

	public function actualizarSaldoSolesaDolares($arrParams){
		global $sqlca;

		$sql="SELECT SUM(nu_importemovimiento / nu_tipocambio) AS ss_total FROM ccob_ta_detalle WHERE ch_moneda IN('01','1') AND ch_identidad != '1' AND cli_codigo='" . $arrParams['iCodigoCliente'] . "' AND ch_tipdocumento='" . $arrParams['iIDTipoDocumento'] . "' AND ch_seriedocumento='" . $arrParams['sSerieDocumento'] . "' AND ch_numdocumento='" . $arrParams['sNumeroDocumento'] . "'";
		$sqlca->query($sql);
		$row = $sqlca->fetchRow();
		$fTotSolesUSD = $row['ss_total'];

		$sql="SELECT SUM(nu_importemovimiento) AS ss_total FROM ccob_ta_detalle WHERE ch_moneda IN('02','2') AND ch_identidad != '1' AND cli_codigo='" . $arrParams['iCodigoCliente'] . "' AND ch_tipdocumento='" . $arrParams['iIDTipoDocumento'] . "' AND ch_seriedocumento='" . $arrParams['sSerieDocumento'] . "' AND ch_numdocumento='" . $arrParams['sNumeroDocumento'] . "'";
		$sqlca->query($sql);
		$row_dolares = $sqlca->fetchRow();
		$fTotUSD = $row_dolares['ss_total'];

		$fTotDolares = $fTotSolesUSD + $fTotUSD;
		$sql = "UPDATE ccob_ta_cabecera SET nu_importesaldo = nu_importetotal - " . $fTotDolares . " WHERE cli_codigo='" . $arrParams['iCodigoCliente'] . "' AND ch_tipdocumento='" . $arrParams['iIDTipoDocumento'] . "' AND ch_seriedocumento='" . $arrParams['sSerieDocumento'] . "' AND ch_numdocumento='" . $arrParams['sNumeroDocumento'] . "'";
		$sqlca->query($sql);
	}

	public function getPartner($arrParams){
		global $sqlca;

		$sql = "SELECT cli_estado FROM int_clientes WHERE cli_codigo='" . pg_escape_string($arrParams['sNumeroDocumentoIdentidad']) . "' LIMIT 1";

		$iStatusSQL = $sqlca->query($sql);
		if ( $iStatusSQL > 0 ) {
            $row = $sqlca->fetchRow();
			return array(
				'sStatus' => 'success',
				'sTipoAgente' => $row['cli_estado'],
			);
		} else if ( $iStatusSQL == 0 ) {
			return array(
				'sStatus' => 'warning',
				'sMessage' => 'No existe el cliente',
			);
		} else {
			return array(
				'sStatus' => 'danger',
				'sMessage' => 'Problemas al obtener cliente',
				'sMessageSQL' => $sqlca->get_error(),
				'SQL' => $sql,
			);
		}
	}

	public function getTaxRetention($arrParams){
		global $sqlca;

		$sql = "
SELECT
 tab_num_01 AS ss_tax_retention
FROM
 int_tabla_general
WHERE
 tab_tabla||tab_elemento=(SELECT par_valor FROM int_parametros WHERE par_nombre='" . $arrParams['sTaxRetention'] . "') LIMIT 1;
 		";

		$iStatusSQL = $sqlca->query($sql);
		if ( $iStatusSQL > 0 ) {
            $row = $sqlca->fetchRow();
            $row['fTaxRetention'] = (double)$row['ss_tax_retention'];
			$row['fTaxRetention'] = ($row['fTaxRetention'] / 100);
			return array(
				'sStatus' => 'success',
				'fTaxRetention' => $row['fTaxRetention'],
			);
		} else if ( $iStatusSQL == 0 ) {
			return array(
				'sStatus' => 'warning',
				'sMessage' => 'No se ha configurado la retencion',
			);
		} else {
			return array(
				'sStatus' => 'danger',
				'sMessage' => 'Problemas al obtener retencion - int_parametros',
				'sMessageSQL' => $sqlca->get_error(),
				'SQL' => $sql,
			);
		}
	}
}
