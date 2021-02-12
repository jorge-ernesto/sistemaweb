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

	function verificar_dia($fecha) {
		global $sqlca;
		$turno = 0;
		$almacen = $_SESSION['almacen'];
		$sql = "SELECT validar_consolidacion('" . $fecha . "', " . $turno . ",'" . $almacen . "');";
		error_log('SQL validar_consolidacion -> ' . $sql);
		$sqlca->query($sql);
		$estado = $sqlca->fetchRow();
		if($estado[0] == 1)
			return 1;//Consolidado
		return 0;//No consolidado
	}

	function obtenerDiaInicialSistema() {
		global $sqlca;
		$sql = "SELECT da_fecha FROM pos_aprosys WHERE ch_poscd='S' ORDER BY da_fecha ASC LIMIT 1";
		$sqlca->query($sql);
		$row = $sqlca->fetchRow();
		return $row['da_fecha'];
	}

	function obtenerSucursales($alm) {
        	global $sqlca;

		if (trim($alm) == "")
		    $cond = "";
		else
		    $cond = " AND ch_almacen = '$alm'";

        	$sql = "
			SELECT
				ch_almacen,
				ch_almacen||' - '||ch_nombre_almacen
			FROM
				inv_ta_almacenes
			WHERE
				ch_clase_almacen='1' $cond 
			ORDER BY
				ch_almacen;
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

    function tipo_cambio($fecha_actula) {
        global $sqlca;



        $sql = "SELECT tca_venta_libre from int_tipo_cambio where tca_moneda='02' and tca_fecha='$fecha_actula';";

        if ($sqlca->query($sql) < 0) {
            return "00.0";
        }
        $a = $sqlca->firstRow($sql);


        return $a[0];
    }

	function obtenerTipoDocumnetos_otros() {
        	global $sqlca;

	        $sql = "
			SELECT                                                                                                                                        
	                        substring(trim(tab_elemento) for 2 from length(trim(tab_elemento))-1) as cod_docu,                                                        
	                        tab_desc_breve as desc_docu                                                                                                               
		        FROM                                                                                                                                          
		                int_tabla_general                                                                                                                         
		        WHERE                                                                                                                                         
		                tab_tabla ='08'
				AND tab_elemento!='000000'
		        ORDER BY
		                desc_docu;
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

	function ObtenerTipoCambio($fecha) {
        	global $sqlca;

		$registro = array();

		$sql = "SELECT tca_venta_oficial FROM int_tipo_cambio WHERE tca_moneda = '02' AND tca_fecha = '$fecha'";

        	if ($sqlca->query($sql) <= 0)
			return false;

		while($reg = $sqlca->fetchRow()){
		        $registro[] = $reg;
		}

		return $registro;

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

	function obtenerOperacion() {
	        global $sqlca;

		$sql = "
			SELECT
				c_cash_operation_id,
				name,
				accounts
			FROM
				c_cash_operation
			WHERE
				type = '1';
		";

        if ($sqlca->query($sql) < 0)
            return false;

        $result = Array();

        for ($i = 0; $i < $sqlca->numrows(); $i++) {
            $a = $sqlca->fetchRow();
            $result[] = array($a[0], $a[1], $a[2]);
        }

        return $result;
    }

	function obtenerCaja() {
        	global $sqlca;

		$sql = "
			SELECT
				c_cash_id,
				name
			FROM
				c_cash;
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

		$sql = "
			SELECT
				num_numactual
			FROM
				int_num_documentos
			WHERE
				num_tipdocumento = 'VI'
				AND num_seriedocumento = '$alm'
			LIMIT 1;
		";

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
                    SELECT num_numactual FROM  int_num_documentos WHERE num_tipdocumento='VI' AND  num_seriedocumento='$alm' limit 1
                     )::INTEGER+1  where num_tipdocumento='VI' and  num_seriedocumento='$alm';";
            if ($sqlca->query($sql) < 0) {
                throw new Exception("Error al actualizar el numero de documento.");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

	function MostarResultadoDetalle($fecha_inicio, $fecha_final, $sucursal, $limit, $ruc, $operacion){
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
					mone.tab_desc_breve moneda,
		            		ic.pro_razsocial as cliente,
		            		ct.transaction,
		                        (
				                SELECT  
				                	SUM(cstd.amount)
						FROM
							c_cash_transaction cct 
					                INNER JOIN c_cash_transaction_payment cstd ON (cct.c_cash_transaction_id = cstd.c_cash_transaction_id)
				                WHERE
							cct.type = '1'
							AND cct.transaction = ct.transaction
							AND cct.ware_house = '$sucursal'
		                        ) as importe_neto,
					ct.rate,
					ct.reference,
					ct.ware_house
				FROM
					c_cash_transaction AS ct
					INNER JOIN c_cash AS c ON (ct.c_cash_id = c.c_cash_id)
					INNER JOIN c_cash_operation AS co ON (co.c_cash_operation_id = ct.c_cash_operation_id)
					LEFT JOIN int_proveedores AS ic ON (ct.bpartner = ic.pro_codigo)
					LEFT JOIN int_tabla_general AS mone ON (mone.tab_tabla ='04' AND mone.tab_elemento != '000000' AND mone.tab_elemento = (LPAD(CAST(ct.c_currency_id AS bpchar),6,'0')))
                   		WHERE
					d_system BETWEEN '$fecha_inicio' AND '$fecha_final'
					AND ct.ware_house = '$sucursal'
					AND co.type = 1
					AND ct.type = '1'
					$cond
					$cond2
				ORDER BY
					ct.c_cash_transaction_id DESC
				LIMIT $limit

			";

/*echo "<pre>";
echo $sql;
echo "</pre>";*/
		    	if ($sqlca->query($sql) < 0) {
		        	throw new Exception("No se encontro Resultados.");
		    	}

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
					$resultado[$i]['id_recibo'] 		= $a[7];
					$resultado[$i]['importe_neto'] 		= $a[8];
					$resultado[$i]['rate']		 	= $a[9];
					$resultado[$i]['referencia']		 	= $a[10];
					$resultado[$i]['ware_house']		 	= $a[11];
				
			}

		    	return $resultado;

		} catch (Exception $e) {
            		throw $e;
		}

	}

    	function obtenerClientes() {
		global $sqlca;

       		$sql = "SELECT TRIM(pro_codigo), TRIM(pro_razsocial) FROM int_proveedores ORDER BY pro_razsocial;";

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

        $sql = "SELECT TRIM(pro_codigo), TRIM(pro_razsocial) FROM int_proveedores WHERE TRIM(pro_razsocial) LIKE '%" . $keyword . "%' ORDER BY pro_razsocial;";

		if ($sqlca->query($sql) < 0)
		    return false;

		$items = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    $a = $sqlca->fetchRow();
		    $items[$a[0]] = $a[1];
		}
		return $items;
	}

	function DataCuentasCobrarDetalleRecivo($cli_codigo, $numero_docu) {
		global $sqlca;

		$query = "

			SELECT 
				pro_codigo,
		            	pro_cab_seriedocumento ||'-'|| pro_cab_numdocumento as serie_num,
		            	pro_cab_fechaemision,
                        	pro_cab_fechavencimiento,
				mone.tab_desc_breve as moneda,
		           	pro_cab_imptotal ,
		            	pro_cab_impsaldo,
		            	pro_cab_tipdocumento as tipo_doc,
		            	com_cab_numorden,
		            	pro_cab_moneda,
		            	pro_cab_tipdocumento,
				gen.tab_desc_breve as tipo
			FROM					
				cpag_ta_cabecera c
				LEFT JOIN int_tabla_general gen ON(c.pro_cab_tipdocumento = substring(TRIM(tab_elemento) for 2 from length(TRIM(tab_elemento))-1) and tab_tabla ='08')
				LEFT JOIN int_tabla_general mone ON (mone.tab_tabla ='04' and mone.tab_elemento != '000000' AND mone.tab_elemento = (LPAD(CAST(c.pro_cab_moneda AS bpchar),6,'0')))
			WHERE
				pro_cab_impsaldo > 0
				AND trim(pro_codigo) = trim('$cli_codigo')  
				AND pro_cab_seriedocumento ||'-'|| pro_cab_numdocumento in($numero_docu)
			ORDER BY
				pro_cab_fechavencimiento;

                    ";
	
		/*echo "<pre>";
		echo $query;
		echo "</pre>";*/

		if ($sqlca->query($query) <= 0)
			return false;

		$resultado = array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {

            		$a = $sqlca->fetchRow();

            		$resultado[$i]['cli_codigo'] 		= $a[0];
            		$resultado[$i]['serie_num'] 		= $a[1];
            		$resultado[$i]['dt_fechaemision'] 	= $a[2];
            		$resultado[$i]['dt_fechavencimiento']	= $a[3];
            		$resultado[$i]['moneda'] 		= $a[4];

	      		if($a[10]=="20"){
		    		$resultado[$i]['nu_importetotal'] = $a[5]*(-1);
		    		$resultado[$i]['nu_importesaldo'] = $a[6]*(-1);
			}else{
		   		$resultado[$i]['nu_importetotal'] = $a[5];
		    		$resultado[$i]['nu_importesaldo'] = $a[6];
			}
          
           		$resultado[$i]['tipo_doc'] 		= $a[7];
            		$resultado[$i]['ch_numdocreferencia'] 	= $a[8];
			$resultado[$i]['ch_moneda'] 		= $a[9];
			$resultado[$i]['tipo_emitido'] 		= $a[10];
			$resultado[$i]['tipo'] 			= $a[11];

          
		}

		return $resultado;

	}

	function DataCuentasCobrar($cli_codigo) {
        	global $sqlca;

        	$query = "
				SELECT
					pro_codigo,
					pro_cab_seriedocumento ||'-'|| pro_cab_numdocumento as serie_num,
					pro_cab_fechaemision,
					pro_cab_fechavencimiento,
		           		mone.tab_desc_breve as moneda,
					pro_cab_imptotal,
					pro_cab_impsaldo,
					pro_cab_tipdocumento,
					gen.tab_desc_breve as tipo,
					substring(mone.tab_elemento,5,2) as cod_moneda
				FROM
					cpag_ta_cabecera c
					LEFT JOIN int_tabla_general gen ON(c.pro_cab_tipdocumento = substring(TRIM(tab_elemento) for 2 from length(TRIM(tab_elemento))-1) and tab_tabla ='08')
					LEFT JOIN int_tabla_general mone ON (mone.tab_tabla ='04' and mone.tab_elemento != '000000' AND mone.tab_elemento = (LPAD(CAST(c.pro_cab_moneda AS bpchar),6,'0')))
				WHERE
					pro_cab_impsaldo > 0
					AND trim(pro_codigo) = trim('$cli_codigo')
				ORDER BY
					pro_cab_fechavencimiento

		";

		/*echo "<pre>";
		echo $query;
		echo "</pre>";*/


		if ($sqlca->query($query) <= 0)
			return false;

        	$resultado = array();

        	for ($i = 0; $i < $sqlca->numrows(); $i++) {

            		$a = $sqlca->fetchRow();

		    	$resultado[$i]['cli_codigo'] 		= $a[0];
		    	$resultado[$i]['serie_num'] 		= $a[1];
		    	$resultado[$i]['dt_fechaemision'] 	= $a[2];
		    	$resultado[$i]['dt_fechavencimiento'] 	= $a[3];
		   	$resultado[$i]['moneda'] 		= $a[4];

		    	if($a[7]=="20"){
		    		$resultado[$i]['nu_importetotal'] = $a[5]*(-1);
		    		$resultado[$i]['nu_importesaldo'] = $a[6]*(-1);
        		}else{
            			$resultado[$i]['nu_importetotal'] = $a[5];
            			$resultado[$i]['nu_importesaldo'] = $a[6];
        		}

			$resultado[$i]['pro_cab_tipdocumento']	= $a[7];
			$resultado[$i]['tipo'] 			= $a[8];
			$resultado[$i]['cod_moneda'] 		= $a[9];

		}

		return $resultado;

	}

    function capturar_secuencia($ch_tipdocumento, $ch_seriedocumento, $ch_numdocumento, $cli_codigo) {
        global $sqlca;
        $sql = "
             SELECT max(pro_det_identidad::INTEGER) as maximo FROM  cpag_ta_detalle
             WHERE   pro_cab_seriedocumento='$ch_seriedocumento' and  pro_cab_numdocumento='$ch_numdocumento' and pro_codigo='$cli_codigo' 
             and pro_det_tipmovimiento!='1' limit 1";


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

	function Insertarc_cash_transaction($createdby, $type, $d_system, $transaction, $c_cash_id, $c_cash_operation_id, $reference, $bpartner, $c_currency_id, $rate, $amount, $ware_house) {
        	global $sqlca;

		try {
			$sql = "
				INSERT INTO c_cash_transaction (
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
						$c_currency_id,
						'$rate',
						$amount,
						'$ware_house'
				);
			";

			if ($sqlca->query($sql) < 0) {
		        	throw new Exception("Error al insertar Cabecera de caja.");
			}

		} catch (Exception $e) {
			throw $e;
		}

	}

	function Insertarc_cash_transaction_detail($doc_type, $doc_serial_number, $doc_number, $reference, $amount, $c_currency_id) {
        	global $sqlca;

        	try {

			$sql = "
				INSERT INTO c_cash_transaction_detail (
            				c_cash_transaction_id,
				    	createdby,
				    	doc_type,
				    	doc_serial_number,
				    	doc_number,
				    	reference,
				    	amount,
				    	c_currency_id
				)VALUES(
					(select max(c_cash_transaction_id) from c_cash_transaction),
					'0',
					'$doc_type',
					'$doc_serial_number',
					'$doc_number',
					'$reference',
					$amount,
					$c_currency_id);

			";

			if ($sqlca->query($sql) < 0) {
				throw new Exception("Error al insertar en caja detalle.");
			}
	
		} catch (Exception $e) {
			throw $e;
		}

	}

    function Insertarc_cash_transaction_payment($c_cash_mpayment_id, $pay_number, $c_bank_id, $c_bank_account_id, $c_currency_id, $amount, $fecha) {
        global $sqlca;
        try {
            $query = "INSERT INTO c_cash_transaction_payment (
                    c_cash_transaction_id,
                    createdby,
                    c_cash_mpayment_id,
                    pay_number,
                    c_bank_id,
                    c_bank_account_id,
                    c_currency_id,
                    amount,created
                )
                VALUES((SELECT max(c_cash_transaction_id) FROM c_cash_transaction),'0','$c_cash_mpayment_id'
    ,'$pay_number','$c_bank_id','$c_bank_account_id','$c_currency_id','$amount','$fecha');";



            if ($sqlca->query($query) < 0) {
                throw new Exception("Error al insertar en medio de pago.");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

	function Insertarc_cuentas_x_cobrar_detalle($cli_codigo, $ch_tipdocumento, $ch_seriedocumento, $ch_numdocumento, $ch_identidad, $ch_tipmovimiento, $dt_fechamovimiento, $ch_moneda, $nu_tipocambio, $nu_importemovimiento, $ch_numdocreferencia, $ch_sucursal, $ch_glosa, $dt_fecha_actualizacion) {
		global $sqlca;

		try {
	
		    	if($ch_tipdocumento=="20"){
		        	$nu_importemovimiento=$nu_importemovimiento*(-1);
		    	}else{
		        	$nu_importemovimiento=$nu_importemovimiento;
		   	}

		    	$secuencia = RegistroCajasModel::capturar_secuencia($tipo_doc, $ch_seriedocumento, $ch_numdocumento, $cli_codigo);

		    	$sql = "
				INSERT INTO cpag_ta_detalle(
								pro_codigo,
								pro_cab_tipdocumento,
								pro_cab_seriedocumento,
								pro_cab_numdocumento,
                        					pro_det_identidad,
								pro_det_tipmovimiento,
                         					pro_det_fechamovimiento,
								pro_det_moneda,
                         					pro_det_tcambio,
								pro_det_impmovimiento,
                         					pro_det_numdocreferencia,
								pro_det_almacen,
								pro_det_glosa,
								pro_det_tipdocreferencia
				) VALUES (
                    						'$cli_codigo',
								'$ch_tipdocumento',
								'$ch_seriedocumento',
								'$ch_numdocumento',
								$secuencia,
								'$ch_tipmovimiento',
                                				'$dt_fechamovimiento',
								'$ch_moneda',
								'$nu_tipocambio',
								$nu_importemovimiento,
                                				'$ch_numdocreferencia',
								'$ch_sucursal',
								'',
								'01'
                    		);

			";


			if ($sqlca->query($sql) < 0)
				throw new Exception("Error al insertar Cuentas x cobrar.");

            		$sql_update = "
					UPDATE
						c_cash_transaction_detail
					SET
						createdby = '$secuencia'
            				WHERE
						c_cash_transaction_id IN(SELECT max(c_cash_transaction_id) FROM c_cash_transaction)
						AND doc_type		= '$ch_tipdocumento'
						AND doc_serial_number 	= '$ch_seriedocumento'
						AND doc_number 		= '$ch_numdocumento';
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

    function Anular_Registro_Egreso_Caja_solo_cliente($id_transacion, $tipo_accion) {
		global $sqlca;
		
		error_log(' ************* ANULAR RECIBO EGRESO  ************* ');

        try {
            $query = "
SELECT
 CC.c_cash_transaction_id,
 COPE.accounts AS id_enlace_documento,
 CC.createdby
FROM
 c_cash_transaction AS CC
 JOIN c_cash_operation AS COPE
  USING(c_cash_operation_id)
WHERE
 CC.type='1'
 AND CC.transaction IN('" . $id_transacion . "')
LIMIT 1;
			";

			error_log('SQL c_cash_transaction -> ' . $query);
            if ($sqlca->query($query) < 0) {
                throw new Exception("Error al buscar c_cash_transaction ($id_transacion).");
			}
			
            $rowsql = $sqlca->fetchRow();
            $c_cash_transaction_id = $rowsql[0];
            $iIDEnlaceDocumento = $rowsql['id_enlace_documento'];//2, es cuando se pagan documentos generados por facturas de compras
			$c_secuencia = $rowsql['createdby'];
			
            if ($iIDEnlaceDocumento == "2") {
                $query_detail = "
SELECT
 c_cash_transaction_detail_id,
 createdby,
 doc_type,
 doc_serial_number,
 doc_number,
 reference
FROM
 c_cash_transaction_detail
WHERE
 c_cash_transaction_id IN(" . $c_cash_transaction_id . ");
				";

				error_log('SQL c_cash_transaction_detail -> ' . $query_detail);
                if ($sqlca->query($query_detail) < 0) {
                    throw new Exception("Error al buscar c_cash_transaction_detail ($c_cash_transaction_id).");
                }

                $data_record = array();
                while ($rowdetalless = $sqlca->fetchRow()) {
                    $data_record[] = $rowdetalless;
                }

                foreach ($data_record as $rowdetalle) {
					$id_detalle_trnsation = $rowdetalle['c_cash_transaction_detail_id'];
					$sTipoDocumento = trim($rowdetalle['doc_type']);
                    $doc_serial_number = trim($rowdetalle['doc_serial_number']);
                    $doc_number = trim($rowdetalle['doc_number']);
                    $reference = trim($rowdetalle['reference']);
                    $c_secuencia = $rowdetalle['createdby']; //1 solo para clientes

                    $sql_cob_detalle = "
DELETE FROM
 cpag_ta_detalle
WHERE
 pro_cab_tipdocumento='" . $sTipoDocumento . "'
 AND pro_cab_seriedocumento='" . $doc_serial_number . "'
 AND pro_cab_numdocumento='" . $doc_number . "'
 AND pro_det_numdocreferencia='" . $reference . "'
 AND pro_det_tipmovimiento!='1'
 AND pro_det_identidad='" . $c_secuencia . "';
 					";
					
					error_log($sql_cob_detalle);
                    if ($sqlca->query($sql_cob_detalle) < 0) {
                        throw new Exception("Error al eliminar cpag_ta_detalle($doc_serial_number*$doc_number*$reference)");
                    }

					$sql_delete_payment = "
DELETE FROM
 c_cash_transaction_payment
WHERE
 c_cash_transaction_id IN(
 SELECT
  c_cash_transaction_id
 FROM
  c_cash_transaction
 WHERE
  type='1'
  AND c_cash_transaction_id IN(" . $c_cash_transaction_id . ")
 );
					";

					error_log($sql_delete_payment);
                    if ($sqlca->query($sql_delete_payment) < 0) {
                        throw new Exception("Error al eliminar  c_cash_transaction_payment");
                    }

					$sql_delete_detail = "
DELETE FROM
 c_cash_transaction_detail
WHERE
 c_cash_transaction_id IN(
 SELECT
  c_cash_transaction_id
 FROM
  c_cash_transaction
 WHERE
  type='1'
  AND c_cash_transaction_id IN(" . $c_cash_transaction_id . ")
 );
					";

					error_log($sql_delete_detail);
                    if ($sqlca->query($sql_delete_detail) < 0) {
                        throw new Exception("Error al eliminar  c_cash_transaction_detail");
                    }

                    $sql_monto_total = "
SELECT
 SUM(pro_det_impmovimiento) AS monto_cabe
FROM
 cpag_ta_detalle 
WHERE
 pro_cab_tipdocumento='" . $sTipoDocumento . "'
 AND pro_cab_seriedocumento='" . $doc_serial_number. "'
 AND pro_cab_numdocumento='" . $doc_number . "'
 AND pro_det_numdocreferencia='" . $reference . "'
 AND pro_det_tipmovimiento!='1'
					";
					
					error_log($sql_monto_total);
                    if ($sqlca->query($sql_monto_total) < 0) {
                        throw new Exception("Error al totalizar monto de cabecera.");
					}
					
                    $rowdmonto = $sqlca->fetchRow();
                    $monto_Cabe = $rowdmonto['monto_cabe'];
                    if ($monto_Cabe == "") {
                        $monto_Cabe = 0;
                    }

                    $sql_cpag_ta_cabecera = " 
UPDATE
 cpag_ta_cabecera
SET
 pro_cab_impsaldo = (pro_cab_imptotal + pro_cab_impinafecto + regc_sunat_percepcion)
WHERE
 pro_cab_tipdocumento='" . $sTipoDocumento . "'
 AND pro_cab_seriedocumento='" . $doc_serial_number . "'
 AND pro_cab_numdocumento='" . $doc_number . "';
					";
					 
					error_log($sql_cpag_ta_cabecera);
                    if ($sqlca->query($sql_cpag_ta_cabecera) < 0) {
                        throw new Exception("Error al actualizar monto.");
                    }
                }
                $sql_delete_trasanction_header = "
DELETE FROM
 c_cash_transaction
WHERE
 type='1'
 AND c_cash_transaction_id IN(" . $c_cash_transaction_id . ")
				";

				error_log($sql_delete_trasanction_header);
                if ($sqlca->query($sql_delete_trasanction_header) < 0) {
                    throw new Exception("Error al eliminar  c_cash_transaction");
                }
            } else {
				error_log(' ************* ELSE  ************* ');
				
                $sql_delete =
                        "DELETE FROM c_cash_transaction_payment where c_cash_transaction_id  in(SELECT  c_cash_transaction_id FROM c_cash_transaction where type='1'  and transaction in('$id_transacion') ) ";

                if ($sqlca->query($sql_delete) < 0) {
                    throw new Exception("Error al eliminar  c_cash_transaction_payment");
                }

                $sql_delete =
                        "DELETE FROM c_cash_transaction_detail where c_cash_transaction_id  in(SELECT  c_cash_transaction_id FROM c_cash_transaction where type='1'  and transaction in('$id_transacion') ) ";

                if ($sqlca->query($sql_delete) < 0) {
                    throw new Exception("Error al eliminar  c_cash_transaction_detail");
                }

                $sql_delete =
                        "DELETE FROM c_cash_transaction where type='1'  and transaction in('$id_transacion') ";
                if ($sqlca->query($sql_delete) < 0) {
                    throw new Exception("Error al eliminar  c_cash_transaction");
                }
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    function ActualizarMontosCabecera($cli_codigo, $ch_seriedocumento, $ch_numdocumento, $monto, $td) {
      	global $sqlca;

       	try {
       		$sql = "            
UPDATE
 cpag_ta_cabecera
SET
 pro_cab_impsaldo = (pro_cab_imptotal + pro_cab_impinafecto + regc_sunat_percepcion) - (
 SELECT
  SUM(pro_det_impmovimiento)
 FROM
  cpag_ta_detalle
 WHERE
  pro_codigo 					= '" . $cli_codigo . "'
  AND pro_cab_tipdocumento 		= '" . $td . "'
  AND pro_cab_seriedocumento 	= '" . $ch_seriedocumento . "'
  AND pro_cab_numdocumento		= '" . $ch_numdocumento . "'
  AND pro_det_tipmovimiento	!= '1'
 )
WHERE
pro_codigo 					= '" . $cli_codigo . "'
AND pro_cab_tipdocumento	= '" . $td . "'
AND pro_cab_seriedocumento	= '" . $ch_seriedocumento . "'
AND pro_cab_numdocumento	= '" . $ch_numdocumento . "';
			";

/*
echo "<pre>";
var_dump($sql);
echo "</pre>";
*/
			if ($sqlca->query($sql) < 0) {
	    		throw new Exception("Error al actualizar monto.");
			}
       	} catch (Exception $e) {
			throw $e;
		}
	}

    function fecha_aprosys() {
    	global $sqlca;

		$query="
			SELECT
				da_fecha
			FROM
				pos_aprosys
			ORDER BY
				da_fecha desc
			LIMIT 1;
		";

        	if ($sqlca->query($query) <= 0) {
			return false;
        	}

        	$resultado	= array();
        	$a		= $sqlca->fetchRow();

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

    function DetalleReporteRecibo($id_recibo,$sucursal) {
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
			c_cash_transaction cct  
	                LEFT JOIN int_clientes ic ON cct.bpartner=ic.cli_codigo 
	                INNER JOIN c_cash cs ON cct.c_cash_id=cs.c_cash_id
	                INNER JOIN c_cash_operation cso ON cct.c_cash_operation_id=cso.c_cash_operation_id
                WHERE
			transaction = '$id_recibo'
			AND cct.type = '1'
			AND cct.ware_house = '$sucursal'
		LIMIT 1;";

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

    	function DetalleReporteRecibo_complemento_registro($id_recibo,$sucursal) {
        	global $sqlca;

		$sql = "
		        SELECT 
		                gen.tab_desc_breve||' - '||cstd.doc_serial_number ||'-'||cstd.doc_number as serie,
		                cstd.reference,
		                mone.tab_desc_breve moneda,
		                cstd.amount,
				cab.pro_cab_impsaldo
			FROM
				c_cash_transaction cct 
			        INNER JOIN c_cash_transaction_detail cstd ON (cct.c_cash_transaction_id = cstd.c_cash_transaction_id)
				LEFT JOIN cpag_ta_cabecera cab ON (cct.bpartner = cab.pro_codigo AND cstd.doc_type = cab.pro_cab_tipdocumento AND cstd.doc_serial_number = cab.pro_cab_seriedocumento AND cstd.doc_number = cab.pro_cab_numdocumento)
				LEFT JOIN int_tabla_general mone ON (mone.tab_tabla ='04' and mone.tab_elemento != '000000' AND mone.tab_elemento = (LPAD(CAST(cstd.c_currency_id AS bpchar),6,'0')))
				LEFT JOIN int_tabla_general gen ON(cstd.doc_type = substring(TRIM(gen.tab_elemento) for 2 from length(TRIM(gen.tab_elemento))-1) AND gen.tab_elemento != '000000' and gen.tab_tabla ='08')
			WHERE
				cct.transaction = '$id_recibo'
				AND cct.type 	= '1'
				AND cct.ware_house = '$sucursal'
		";

/*echo "<pre>";
print_r($sql);
echo "</pre>";*/

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

	function DetalleReporteRecibo_medio_pago($id_recibo, $sucursal) {
        	global $sqlca;

        	$query = "
                	SELECT 
		                csm.name,
		                cstd.pay_number,
		                cstd.created,
		                b.name,
		                cba.c_bank_account_id,
		                mone.tab_desc_breve moneda,
		                cstd.amount,
		                cstd.c_cash_transaction_payment_id,
				cct.rate,
				cstd.c_currency_id cod_moneda_pay
			FROM
				c_cash_transaction cct 
			        INNER JOIN c_cash_transaction_payment cstd ON (cct.c_cash_transaction_id=cstd.c_cash_transaction_id)
			        INNER JOIN c_cash_mpayment csm ON (cstd.c_cash_mpayment_id=csm.c_cash_mpayment_id)
			        LEFT JOIN c_bank b ON (cstd.c_bank_id=b.c_bank_id)
			        LEFT JOIN c_bank_account cba ON (cstd.c_bank_account_id =cba.c_bank_account_id)
				LEFT JOIN int_tabla_general mone ON (mone.tab_tabla ='04' and mone.tab_elemento != '000000' AND mone.tab_elemento = (LPAD(CAST(cstd.c_currency_id AS bpchar),6,'0')))
		        WHERE
				cct.transaction = '$id_recibo'
				AND cct.type = '1'
				AND cct.ware_house = '$sucursal'";
/*echo "<pre>";
echo $query;
echo "</pre>";*/

		if ($sqlca->query($query) <= 0) {
		    return false;
		}

		$resultado = array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    	$a = $sqlca->fetchRow();
		    	$resultado[$i]['tipo_mp'] 		= $a[0];
		    	$resultado[$i]['pay_number'] 		= $a[1];
			$resultado[$i]['created'] 		= $a[2];
			$resultado[$i]['banco'] 		= $a[3];
		    	$resultado[$i]['cuenta_banco'] 		= $a[4];
		    	$resultado[$i]['moneda'] 		= $a[5];
		    	$resultado[$i]['importe'] 		= $a[6];
		    	$resultado[$i]['id_pay'] 		= $a[7];
		    	$resultado[$i]['rate'] 			= $a[8];
		    	$resultado[$i]['cod_moneda_pay'] 	= $a[9];
		}


        	return $resultado;

    	}

}

