<?php

class LiquidacionValesModel extends Model {

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

    function ActualizarInt_documnetoAnticipo($num_acta_lv, $num_acta_documento, $tipo_doc, $serie_doc) {
        global $sqlca;
        try {

            $sql = "update int_num_documentos set  num_numactual='$num_acta_lv'   where num_tipdocumento='LV' ;";

            if ($sqlca->query($sql) < 0) {
                throw new Exception("No se pudo actualizar Numero de Liquidacion");
            }

            $sql = "update  int_num_documentos set num_numactual='$num_acta_documento' WHERE num_tipdocumento='22'  ;";
            if ($sqlca->query($sql) < 0) {
                throw new Exception("No se pudo actualizar Numero de Documento");
                shell_exec("echo 'No se pudo actualizar Numero de Documento' >>log_liquidacion.log");
            }

            return $registro;
        } catch (Exception $e) {
            throw $e;
        }
    }

    function ActualizarInt_documneto($num_acta_lv, $num_acta_documento, $tipo_doc, $serie_doc) {
        global $sqlca;
        try {

            $sql = "update int_num_documentos set  num_numactual='$num_acta_lv'   where num_tipdocumento='LV' ;";

            if ($sqlca->query($sql) < 0) {
                throw new Exception("No se pudo actualizar Numero de Liquidacion");
            }

            $sql = "update  int_num_documentos set num_numactual='$num_acta_documento' WHERE num_tipdocumento='$tipo_doc' and num_seriedocumento='$serie_doc' ;";

            if ($sqlca->query($sql) < 0) {
                throw new Exception("No se pudo actualizar Numero de Documento");
            }

            return $registro;
        } catch (Exception $e) {
            throw $e;
        }
    }

    function ObtenerdatosANTICIPOFactura() {
        global $sqlca;
        try {

            $sql = "SELECT  trim(num_numactual) AS numero_resumen_anticipo FROM int_num_documentos WHERE num_tipdocumento='22' limit 1";
            if ($sqlca->query($sql) < 0) {
                throw new Exception("Error Capturando Datos para factura con anticipo.");
            }
            $reg = $sqlca->fetchRow();
            $registro['num_act_documneto'] = ($reg['numero_resumen_anticipo'] + 1);

            return $registro;
        } catch (Exception $e) {
            throw $e;
        }
    }

    function Verificar_nota_despacho_efectivo($cli_codigo) {
        global $sqlca;
        try {

            $sql = "SELECT cli_ndespacho_efectivo FROM int_clientes WHERE trim(cli_codigo)='$cli_codigo' or  trim(cli_ruc)='$cli_codigo' LIMIT 1;";
            if ($sqlca->query($sql) < 0) {
                return 0;
            }
            $reg = $sqlca->fetchRow();
            $registro['cli_ndespacho_efectivo'] = $reg['cli_ndespacho_efectivo'];

            return $registro;
        } catch (Exception $e) {
            throw $e;
        }
    }

    function ObtenerdatosFactura($tipo_documento, $num_seriedocumento) {
        global $sqlca;
        try {
            $sql = "select num_numactual from int_num_documentos where num_tipdocumento='LV' limit 1;";

            if ($sqlca->query($sql) < 0) {
                throw new Exception("Error Capturando Nro Liquidacion");
            }
            $reg = $sqlca->fetchRow();
            $registro['num_LV'] = $reg['num_numactual'];

            $sql = "SELECT (num_numactual::int) as num_actual,ch_almacen,num_descdocumento
                FROM int_num_documentos WHERE num_tipdocumento='$tipo_documento' and num_seriedocumento='$num_seriedocumento' limit 1;";
            if ($sqlca->query($sql) < 0) {
                throw new Exception("Error Capturando Datos necesarios para generar la factura(Tipo-documento=$tipo_documento,Serie=$num_seriedocumento).");
            }

            $reg = $sqlca->fetchRow();
            $registro['num_act_documneto'] = $reg['num_actual'];
            $registro['ch_almacen'] = $reg['ch_almacen'];
            $registro['num_descdocumento'] = $reg['num_descdocumento'];

            $sql = " SELECT  nu_columna as cantidaditem FROM fac_ta_formatos_doc where ch_tipo_documento='$tipo_documento' and ch_parte_documento='A' limit 1;";
            if ($sqlca->query($sql) < 0) {
                throw new Exception("No se encontro valor por defecto de MAXITEMS");
            }
            $reg = $sqlca->fetchRow();
            $registro['cantidaditem'] = $reg['cantidaditem'];

            return $registro;
        } catch (Exception $e) {
            throw $e;
        }
    }

	function BuscarLiquidaciones($fecha_inicio, $fecha_final) {
	        global $sqlca;

		try {
    	$sql = "
        SELECT * FROM(
            SELECT
    			art_codigo::TEXT AS operacion,
    			v.ch_liquidacion,
    			ch_cliente,
    			(SELECT cli_ruc||'-'||cli_rsocialbreve FROM int_clientes c WHERE trim(c.cli_codigo) = trim(ch_cliente) or trim(c.cli_ruc)=trim(ch_cliente) limit 1) as nombre,
    			v.ch_fac_tipodocumento,
    			v.ch_fac_seriedocumento,
    			v.ch_fac_numerodocumento,
    			'PRODUCTO'::TEXT as opcion,
    			SUM(v.nu_fac_valortotal) as total,
    			(SELECT tab_desc_breve FROM int_tabla_general WHERE tab_tabla = '08' AND substr(tab_elemento,5,2) = v.ch_fac_tipodocumento) desc_tipodocumento,
    			SUM(c.nu_fac_impuesto1) as igv
    		FROM
    			val_ta_complemento_documento v
    			LEFT JOIN fac_ta_factura_cabecera c ON (c.ch_fac_tipodocumento = v.ch_fac_tipodocumento AND c.ch_fac_seriedocumento = v.ch_fac_seriedocumento AND c.ch_fac_numerodocumento = v.ch_fac_numerodocumento)
    		WHERE
    			accion = 'XPRODUCTO'
    			AND (fecha_liquidacion BETWEEN '$fecha_inicio' AND '$fecha_final')
    		GROUP BY
    			v.art_codigo,v.ch_liquidacion,ch_cliente,v.ch_fac_tipodocumento,v.ch_fac_seriedocumento,v.ch_fac_numerodocumento
    	) AS a
        UNION(
       		SELECT
        		ch_numeval::TEXT as operacion,
        		v.ch_liquidacion,
        	    ch_cliente,
        	    (select  cli_ruc||'-'||cli_rsocialbreve from int_clientes c where trim(c.cli_codigo)=trim(ch_cliente) or trim(c.cli_ruc)=trim(ch_cliente) limit 1) as nombre,
        		v.ch_fac_tipodocumento,
        		v.ch_fac_seriedocumento,
        	    v.ch_fac_numerodocumento,
        		'ND'::TEXT as opcion,
        	    sum(v.nu_fac_valortotal) as total,
        		(SELECT tab_desc_breve FROM int_tabla_general WHERE tab_tabla = '08' AND substr(tab_elemento,5,2) = v.ch_fac_tipodocumento) desc_tipodocumento,
        		SUM(c.nu_fac_impuesto1) as igv
           	FROM
        		val_ta_complemento_documento v
        		LEFT JOIN fac_ta_factura_cabecera c ON (c.ch_fac_tipodocumento = v.ch_fac_tipodocumento AND c.ch_fac_seriedocumento = v.ch_fac_seriedocumento AND c.ch_fac_numerodocumento = v.ch_fac_numerodocumento)
       		WHERE
        		accion = 'XNOTADES'
        		AND (fecha_liquidacion BETWEEN '$fecha_inicio' and '$fecha_final')
       		GROUP BY
    		    v.ch_numeval,v.ch_liquidacion,ch_cliente,v.ch_fac_tipodocumento,v.ch_fac_seriedocumento,v.ch_fac_numerodocumento
    	)UNION(
      		SELECT
        		v.ch_placa::TEXT AS operacion,
        		v.ch_liquidacion,
        	    v.ch_cliente,
        	    (select cli_ruc||'-'||cli_rsocialbreve from int_clientes c where trim(c.cli_codigo)=trim(v.ch_cliente) or trim(c.cli_ruc)=trim(v.ch_cliente) limit 1) as nombre,
        		v.ch_fac_tipodocumento,
        		v.ch_fac_seriedocumento,
        	    v.ch_fac_numerodocumento,
        		'PLACA'::TEXT as opcion,
        	    SUM(v.nu_fac_valortotal) as total,
        		(SELECT tab_desc_breve FROM int_tabla_general WHERE tab_tabla = '08' AND substr(tab_elemento,5,2) = v.ch_fac_tipodocumento) desc_tipodocumento,
        		SUM(c.nu_fac_impuesto1) as igv
          	FROM
        		val_ta_complemento_documento v
        		LEFT JOIN fac_ta_factura_cabecera c ON (c.ch_fac_tipodocumento = v.ch_fac_tipodocumento AND c.ch_fac_seriedocumento = v.ch_fac_seriedocumento AND c.ch_fac_numerodocumento = v.ch_fac_numerodocumento)
          	WHERE
    		    accion='XPLACA' AND (fecha_liquidacion between '$fecha_inicio' and '$fecha_final')
          	GROUP BY
    		    v.ch_placa,v.ch_liquidacion,v.ch_cliente,v.ch_fac_tipodocumento,v.ch_fac_seriedocumento,v.ch_fac_numerodocumento
    	)UNION(
          	SELECT
        		'-'::TEXT as operacion,
        		v.ch_liquidacion,
        	    v.ch_cliente,
        	    (select  cli_ruc||'-'||cli_rsocialbreve from int_clientes c where trim(c.cli_codigo)=trim(ch_cliente) or trim(c.cli_ruc)=trim(ch_cliente) limit 1) as nombre,
        		v.ch_fac_tipodocumento,
        		v.ch_fac_seriedocumento,
        	    v.ch_fac_numerodocumento,
        		'normal'::TEXT as opcion,
        	    sum(v.nu_fac_valortotal) as total,
        		(SELECT tab_desc_breve FROM int_tabla_general WHERE tab_tabla = '08' AND substr(tab_elemento,5,2) = v.ch_fac_tipodocumento) desc_tipodocumento,
        		SUM(c.nu_fac_impuesto1) as igv
          	FROM
        		val_ta_complemento_documento v
        		LEFT JOIN fac_ta_factura_cabecera c ON (c.ch_fac_tipodocumento = v.ch_fac_tipodocumento AND c.ch_fac_seriedocumento = v.ch_fac_seriedocumento AND c.ch_fac_numerodocumento = v.ch_fac_numerodocumento)
          	WHERE
    		    accion='XNORMAL' AND (fecha_liquidacion between '$fecha_inicio' and '$fecha_final')
          	GROUP BY
    		    v.ch_liquidacion,v.ch_cliente,v.ch_fac_tipodocumento,v.ch_fac_seriedocumento,v.ch_fac_numerodocumento
    	)UNION(
        	SELECT
        		'POR-COBRAR'::TEXT as operacion,
        		v.ch_liquidacion,
            	v.ch_cliente,
        		(select  cli_ruc||'-'||cli_rsocialbreve from int_clientes c where trim(c.cli_codigo)=trim(ch_cliente) or trim(c.cli_ruc)=trim(ch_cliente) limit 1) as nombre,
        		v.ch_fac_tipodocumento,
        		v.ch_fac_seriedocumento,
            	v.ch_fac_numerodocumento,
        		'normal'::TEXT as opcion,
            	sum(v.nu_fac_valortotal) as total,
        		(SELECT tab_desc_breve FROM int_tabla_general WHERE tab_tabla = '08' AND substr(tab_elemento,5,2) = v.ch_fac_tipodocumento) desc_tipodocumento,
        		SUM(c.nu_fac_impuesto1) as igv
        	FROM
        		val_ta_complemento_documento v
        		LEFT JOIN fac_ta_factura_cabecera c ON (c.ch_fac_tipodocumento = v.ch_fac_tipodocumento AND c.ch_fac_seriedocumento = v.ch_fac_seriedocumento AND c.ch_fac_numerodocumento = v.ch_fac_numerodocumento)
        	WHERE
    		    accion='XCOBRAR' AND (fecha_liquidacion BETWEEN '$fecha_inicio' and '$fecha_final')
        	GROUP BY
    		    v.ch_liquidacion,v.ch_cliente,v.ch_fac_tipodocumento,v.ch_fac_seriedocumento,v.ch_fac_numerodocumento
        )
        ORDER BY
            ch_fac_tipodocumento,
            ch_fac_seriedocumento,
            ch_fac_numerodocumento DESC;
	   ";
/*
echo "<pre>";
print_r($sql);
echo "</pre>";
*/
		    	if ($sqlca->query($sql) < 0) {
		        	throw new Exception("Error Data Liquidaciones");
		    	}

		    	while ($reg = $sqlca->fetchRow()) {
		        	$registro[] = $reg;
		    	}

		    	return $registro;

        	} catch (Exception $e) {
	            	throw $e;
       		}

	}

    function ObtenerdatosCliente($codigo) {
        global $sqlca;
        try {
            $sql = "SELECT cli_ruc,cli_codigo,cli_razsocial,cli_fpago_credito,cli_comp_direccion,cli_direccion,cli_anticipo from int_clientes where (trim(cli_codigo)=trim('$codigo') or trim(cli_ruc)=trim('$codigo')) limit 1;";

            if ($sqlca->query($sql) < 0) {
                throw new Exception("Error al Obtener datos del cliente.");
            }
            while ($reg = $sqlca->fetchRow()) {
                $registro[] = $reg;
            }

            return $registro[0];
        } catch (Exception $e) {
            throw $e;
        }
    }

    function ObtenerdatosSucurasles() {
        global $sqlca;
        try {
            $sql = "select ch_sucursal from int_ta_sucursales limit 1;";

            if ($sqlca->query($sql) < 0) {
                throw new Exception("Error al Obtener datos de la sucursal.");
            }
            while ($reg = $sqlca->fetchRow()) {
                $registro[] = $reg;
            }

            return $registro[0]['ch_sucursal'];
        } catch (Exception $e) {
            throw $e;
        }
    }

    function OrdenarChofer($num_tipdocumento) {
        global $sqlca;

        $sql = "SELECT  num_seriedocumento,num_numactual from int_num_documentos where num_tipdocumento='$num_tipdocumento'";

        if ($sqlca->query($sql) < 0) {
            return $sqlca->get_error();
        }
        while ($reg = $sqlca->fetchRow()) {
            $serie = $reg[0];
            $num_actual = $reg[1];
            $tipos[] = array($serie, $num_actual);
        }

        return $tipos;
    }

    function obtenerTiposDocumento($num_tipdocumento) {
        global $sqlca;

        $sql = "SELECT  num_seriedocumento,num_numactual from int_num_documentos where num_tipdocumento='$num_tipdocumento'";

        if ($sqlca->query($sql) < 0) {
            return $sqlca->get_error();
        }
        while ($reg = $sqlca->fetchRow()) {
            $serie = $reg[0];
            $num_actual = $reg[1];
            $tipos[] = array($serie, $num_actual);
        }

        return $tipos;
    }

    function MostarClienteVales_rangoFecha($fecha_inicio, $fecha_final) {
        global $sqlca;

        $consulta = "SELECT 
                            ch_cliente,
                            cli.cli_razsocial as rz,
                            count(*) as cantidadValesLiquidar,
                            (
                            SELECT sum(deta.nu_importe) FROM VAL_TA_CABECERA tmp LEFT JOIN VAL_TA_DETALLE deta 
                            on tmp.ch_sucursal=deta.ch_sucursal and tmp.dt_fecha=deta.dt_fecha and tmp.ch_documento=deta.ch_documento 
                            WHERE trim(tmp.ch_cliente)=trim(ca.ch_cliente) and (tmp.ch_liquidacion is null or trim(tmp.ch_liquidacion::TEXT)='') 
                            and tmp.dt_fecha <'$fecha_inicio'
                                                            ) as faltaliquidarAnterior,
                            (
                            SELECT sum(deta.nu_importe) FROM VAL_TA_CABECERA tmp LEFT JOIN VAL_TA_DETALLE deta 
                            on tmp.ch_sucursal=deta.ch_sucursal and tmp.dt_fecha=deta.dt_fecha and tmp.ch_documento=deta.ch_documento 
                            WHERE trim(tmp.ch_cliente)=trim(ca.ch_cliente) and (tmp.ch_liquidacion is null or trim(tmp.ch_liquidacion::TEXT)='') 
                            and tmp.dt_fecha >'$fecha_final'
                                ) as faltaliquidarposterio,
                            (SELECT sum(ROUND(deta.nu_importe,2)) FROM VAL_TA_CABECERA tmp 
                            LEFT JOIN VAL_TA_DETALLE  deta 
                            on tmp.ch_sucursal=deta.ch_sucursal and tmp.dt_fecha=deta.dt_fecha and tmp.ch_documento=deta.ch_documento
                            WHERE trim(tmp.ch_cliente)=trim(ca.ch_cliente) 
                            and (tmp.ch_liquidacion is null or trim(tmp.ch_liquidacion::TEXT)='') and tmp.dt_fecha between '$fecha_inicio' AND '$fecha_final'
                            ) as totalliquidar

                   FROM VAL_TA_CABECERA ca 
                   INNER JOIN int_clientes cli ON cli.cli_codigo=ca.ch_cliente
                   WHERE  ca.dt_fecha between '$fecha_inicio' AND '$fecha_final' 
                   AND (ca.ch_liquidacion is null or ca.ch_liquidacion='') 
                   GROUP by ca.ch_cliente,rz;
";

        $sqlca->query($consulta);
        $numrows = $sqlca->numrows();
        while ($reg = $sqlca->fetchRow()) {
            $registro[] = $reg;
        }

        return $registro;
    }

    /*
     */

    function MostarMontoDocumetos($DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO, $codigo_cliente, $FEC_LIQUIDACION, $NUM_LIQUIDACION, $codigo_cliente_) {
        global $sqlca;
        try {
            // $NUMERO_DOCUMENTO = str_pad($NUMERO_DOCUMENTO, 7, '0', STR_PAD_LEFT);
            $sql = " SELECT nu_fac_valorbruto,nu_fac_impuesto1,nu_fac_valortotal FROM fac_ta_factura_cabecera  WHERE 
                            ch_liquidacion='$NUM_LIQUIDACION' 
                            AND ch_fac_numerodocumento=LPAD(CAST('$NUMERO_DOCUMENTO' AS bpchar), 7, '0')
                            AND ch_fac_tipodocumento='$DOCUMENTO'
                            AND ch_fac_seriedocumento='$SERIE'
                            AND dt_fac_fecha='$FEC_LIQUIDACION'
                            AND cli_codigo='$codigo_cliente'
                            limit 1
                            ; ";

            $sqlca->query($sql);
            $registro = array();
            while ($reg = $sqlca->fetchRow()) {
                $registro[] = $reg;
            }

            if ($sqlca->query($sql) < 0) {
                throw new Exception("ERROR Mostar Datos de los documento Generado($DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO, $codigo_cliente)" . $sql);
            }
            return $registro[0];
        } catch (Exception $e) {
            throw $e;
        }
    }

    function ActualizarMontoDocumetos($DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO, $codigo_cliente, $FEC_LIQUIDACION, $NUM_LIQUIDACION) {
        global $sqlca;
        try {
            // $NUMERO_DOCUMENTO = str_pad($NUMERO_DOCUMENTO, 7, '0', STR_PAD_LEFT);
            $sql = " UPDATE fac_ta_factura_cabecera 
                                    SET nu_fac_valorbruto=(SELECT sum(nu_fac_importeneto) FROM fac_ta_factura_detalle
                                    WHERE ch_fac_tipodocumento='$DOCUMENTO' and  ch_fac_seriedocumento ='$SERIE'
                                    AND  ch_fac_numerodocumento=LPAD(CAST('$NUMERO_DOCUMENTO' AS bpchar),7,'0')
                                    AND cli_codigo='$codigo_cliente' ),
                                    nu_fac_impuesto1=(SELECT sum(nu_fac_impuesto1) FROM fac_ta_factura_detalle WHERE ch_fac_tipodocumento='$DOCUMENTO'
                                    AND  ch_fac_seriedocumento ='$SERIE'
                                    AND  ch_fac_numerodocumento=LPAD(CAST('$NUMERO_DOCUMENTO' AS bpchar),7,'0')
                                    AND cli_codigo='$codigo_cliente' ),
                                    nu_fac_valortotal=(SELECT sum(nu_fac_valortotal) FROM fac_ta_factura_detalle WHERE ch_fac_tipodocumento='$DOCUMENTO'
                                    AND  ch_fac_seriedocumento ='$SERIE'
                                    AND  ch_fac_numerodocumento=LPAD(CAST('$NUMERO_DOCUMENTO' AS bpchar),7,'0') AND cli_codigo='$codigo_cliente' )

                                    WHERE ch_fac_tipodocumento='$DOCUMENTO'
                                    AND  ch_fac_seriedocumento='$SERIE' AND ch_fac_numerodocumento=LPAD(CAST('$NUMERO_DOCUMENTO' AS bpchar),7,'0')
                                    AND cli_codigo='$codigo_cliente' AND ch_liquidacion='$NUM_LIQUIDACION' AND dt_fac_fecha='$FEC_LIQUIDACION'  ";

            if ($sqlca->query($sql) < 0) {
                throw new Exception("ERROR al actualizar los montos de la cabecera del documento($DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO, $codigo_cliente)" . $sql);
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    function InsertarDocumentocomplemto($DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO, $codigo_cliente, $FEC_LIQUIDACION, $cli_ruc, $cli_direccion, $cli_comp_direccion, $cli_razsocial) {
        global $sqlca;

        $cli_razsocial = htmlentities($cli_razsocial, ENT_QUOTES, 'UTF-8');

        try {
            $consulta = "
                INSERT INTO fac_ta_factura_complemento(
                    ch_fac_tipodocumento,
                    ch_fac_seriedocumento,
                    ch_fac_numerodocumento,
                    cli_codigo,
                    dt_fac_fecha,
                    ch_fac_ruc,
                    ch_usuario,
                    nu_fac_direccion,
                    nu_fac_complemento_direccion,
                    ch_fac_nombreclie,
                    dt_fechactualizacion,
                    fecha_replicacion
                )VALUES(
                    '".$DOCUMENTO."',
                    '".$SERIE."',
                    LPAD(CAST('".$NUMERO_DOCUMENTO."' AS bpchar), 7, '0'),
                    '".$codigo_cliente."',
                    '".$FEC_LIQUIDACION."',
                    '".$cli_ruc."',
                    'LIQ.VALES',
                    substr('".$cli_direccion."', 0, 100),
                    '".$cli_comp_direccion."',
                    '".str_replace("'", "''", $cli_razsocial)."',  
                    now(),
                    now()
                );
            ";

            shell_exec("echo '$consulta' >>log_liquidacion.log");

            $in = $sqlca->query($consulta);
            if ($in < 0) {
                throw new Exception("Error de Insercion de documento Complemento");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    function InsertarDocumentocabecera($DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO, $codigo_cliente, $num_liquidacion, $FEC_LIQUIDACION, $ALMACEN, $cli_fpago_credito) {
        global $sqlca;

        try {

            $consulta = "
            	INSERT INTO fac_ta_factura_cabecera(
	                ch_fac_tipodocumento,
	                ch_fac_seriedocumento,
	                ch_fac_numerodocumento,
	                cli_codigo,
	                ch_liquidacion,
	                ch_fac_credito,
	                dt_fac_fecha,
	                ch_punto_venta,
	                ch_almacen,
	                ch_fac_moneda,
	                nu_tipocambio,
	                ch_fac_cd_impuesto1,
	                ch_fac_forma_pago,
	                flg_replicacion,
	                nu_fac_impuesto1,
	                nu_fac_valorbruto,
                    nu_tipo_pago,
                    fe_vencimiento
				)VALUES(
                    '".$DOCUMENTO."',
                    '".$SERIE."',
                    LPAD(CAST('".$NUMERO_DOCUMENTO."' AS bpchar), 7, '0'),
                    '".$codigo_cliente."',
                    '".$num_liquidacion."',
                    'S',
                    '".$FEC_LIQUIDACION."',
                    '".$SERIE."',
                    '".$ALMACEN."',
                    '01',
                    util_fn_tipo_cambio_dia('".$FEC_LIQUIDACION."'),
                    substring(util_fn_cd_igv() for 2 from length(util_fn_cd_igv())-1),
                    '" . $cli_fpago_credito . "',
                    1,
                    NULL,
                    NULL,
                    '01',
                    '" . $FEC_LIQUIDACION . "'
				);
			";

            shell_exec("echo '$consulta' >>log_liquidacion.log");

            $in = $sqlca->query($consulta);
            if ($in < 0) {
                throw new Exception("Error al Insertar la cabecera del documento($DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO)");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }
//--------------cai
    function InsertarDocumentocabeceraExo($DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO, $codigo_cliente, $num_liquidacion, $FEC_LIQUIDACION, $ALMACEN, $cli_fpago_credito) {
        global $sqlca;

        try {

            $consulta = "
                INSERT INTO fac_ta_factura_cabecera(
                    ch_fac_tipodocumento,
                    ch_fac_seriedocumento,
                    ch_fac_numerodocumento,
                    cli_codigo,
                    ch_liquidacion,
                    ch_fac_credito,
                    dt_fac_fecha,
                    ch_punto_venta,
                    ch_almacen,
                    ch_fac_moneda,
                    nu_tipocambio,
                    ch_fac_cd_impuesto1,
                    ch_fac_forma_pago,
                    flg_replicacion,
                    nu_fac_impuesto1,
                    nu_fac_valorbruto,
                    ch_fac_tiporecargo2
                )VALUES(
                    '".$DOCUMENTO."',
                    '".$SERIE."',
                    LPAD(CAST('".$NUMERO_DOCUMENTO."' AS bpchar), 7, '0'),
                    '".$codigo_cliente."',
                    '".$num_liquidacion."',
                    'S',
                    '".$FEC_LIQUIDACION."',
                    '".$SERIE."',
                    '".$ALMACEN."',
                    '01',
                    util_fn_tipo_cambio_dia('".$FEC_LIQUIDACION."'),
                    substring(util_fn_cd_igv() for 2 from length(util_fn_cd_igv())-1),
                    '".$cli_fpago_credito."',
                    1,
                    NULL,
                    NULL,
                    'S'
                );
            ";

            shell_exec("echo '$consulta' >>log_liquidacion.log");

            $in = $sqlca->query($consulta);
            if ($in < 0) {
                throw new Exception("Error al Insertar la cabecera del documento($DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO)");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

     function InsertarDocumentoDetalleExo($DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO, $codigo_cliente, $art_codigo, $nu_importe, $nu_cantidad, $art_descripcion) {
        global $sqlca;
        try {

            if ($nu_importe == 0) {
                throw new Exception("Error Al procesar la divion BY ZERO($nu_importe/$nu_cantidad)");
            }

            $precio_producto = round($nu_importe / $nu_cantidad, 4);

            $consulta = "
            INSERT INTO fac_ta_factura_detalle(
                ch_fac_tipodocumento,
                ch_fac_seriedocumento,
                ch_fac_numerodocumento,
                cli_codigo,
                art_codigo,
                nu_fac_valortotal,
                nu_fac_cantidad,
                nu_fac_importeneto,
                ch_fac_cd_impuesto1,
                nu_fac_impuesto1,
                pre_lista_precio,
                nu_fac_precio,
                flg_replicacion,
                ch_art_descripcion,
                ch_fac_tiporecargo2
            )VALUES(
                '$DOCUMENTO',
                '$SERIE',
                LPAD(CAST('$NUMERO_DOCUMENTO' AS bpchar), 7, '0'),
                '$codigo_cliente',
                '$art_codigo',
                $nu_importe,
                $nu_cantidad,
                $nu_importe,
                substring(util_fn_cd_igv() for 2 from length(util_fn_cd_igv())-1),
                0.0000,
                '01',
                $precio_producto,
                1,
                '" . addslashes($art_descripcion) . "',
                'S'
            );
            ";

            error_log("Factura Detalle :" . $consulta);

            $in = $sqlca->query($consulta);

            if ($in < 0) {
                error_log("Factura Detalle ERRO:" . $consulta);
                throw new Exception("Error al Insertar el Detalle del Documento($DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO)");
            }

        } catch (Exception $e) {
            throw $e;
        }

    }
   
//-----------cai
    function InsertarDocumentoDetalle($DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO, $codigo_cliente, $art_codigo, $nu_importe, $nu_cantidad, $art_descripcion) {
       	global $sqlca;

		try {

            if ($nu_importe == 0) {
				throw new Exception("Error Al procesar la divion BY ZERO($nu_importe/$nu_cantidad)");
			}

		    $precio_producto = round($nu_importe / $nu_cantidad, 4);

		    $consulta = "
			INSERT INTO fac_ta_factura_detalle(
		    	ch_fac_tipodocumento,
		    	ch_fac_seriedocumento,
		    	ch_fac_numerodocumento,
		    	cli_codigo,
		    	art_codigo,
		    	nu_fac_valortotal,
		    	nu_fac_cantidad,
		    	nu_fac_importeneto,
		    	ch_fac_cd_impuesto1,
		    	nu_fac_impuesto1,
		    	pre_lista_precio,
		    	nu_fac_precio,
		    	flg_replicacion,
		    	ch_art_descripcion
	    	)VALUES(
		    	'$DOCUMENTO',
		    	'$SERIE',
		    	LPAD(CAST('$NUMERO_DOCUMENTO' AS bpchar), 7, '0'),
		    	'$codigo_cliente',
		    	'$art_codigo',
		    	$nu_importe,
		    	$nu_cantidad,
		    	($nu_importe / (1 + round(util_fn_igv() / 100, 2))),
		    	substring(util_fn_cd_igv() for 2 from length(util_fn_cd_igv())-1),
		    	$nu_importe - ($nu_importe / (1 + (round(util_fn_igv() / 100, 2)))),
		    	'01',
		    	$precio_producto,
		    	1,
		    	'" . addslashes($art_descripcion) . "'
			);
			";

			error_log("Factura Detalle :" . $consulta);

		    $in = $sqlca->query($consulta);

            if ($in < 0) {
            	error_log("Factura Detalle ERRO:" . $consulta);
            	throw new Exception("Error al Insertar el Detalle del Documento($DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO)");
            }

       	} catch (Exception $e) {
			throw $e;
       	}

    }

    	function InsertarDocumentoCCob_Cabecera($SERIE, $NUMERO_DOCUMENTO_ANTI, $codigo_cliente, $FEC_LIQUIDACION, $ch_liquidacion, $totalimporte) {
        	global $sqlca;

        try {
            $consulta = " INSERT INTO ccob_ta_cabecera
		          			(
							    cli_codigo,
							    ch_tipdocumento,
							    ch_seriedocumento,
							    ch_numdocumento,
							    ch_tipcontable,
							    dt_fechaemision,
							    dt_fecharegistro,
							    dt_fechavencimiento,
							    ch_moneda,
							    nu_tipocambio,
							    nu_importetotal,
							    nu_importesaldo,
							    dt_fechasaldo,
							    plc_codigo,
							    ch_tipdocreferencia,
							    ch_numdocreferencia,
							    ch_sucursal,
							    nu_importeafecto,
							    ch_tipoimpuesto1,
							    nu_impuesto1,
							    dt_fecha_actualizacion,
							    ch_auditorpc
		          			)
		     				VALUES
		          			(
							    '$codigo_cliente',
							    '22',
							    '$SERIE',
							    LPAD(CAST('$NUMERO_DOCUMENTO_ANTI' AS bpchar),7,'0'),
							    util_fn_tipo_accion_contable('CC','22'),
							    '$FEC_LIQUIDACION',
							    '$FEC_LIQUIDACION',
							    '$FEC_LIQUIDACION',
							    '01',
							    util_fn_tipo_cambio_dia('$FEC_LIQUIDACION'),
							    $totalimporte,
							    $totalimporte,
							    '$FEC_LIQUIDACION',
							    '12103',
							    '42',
                                                             LPAD(CAST('$ch_liquidacion' AS bpchar),10,'0'),
							    '001',
							    $totalimporte/(1+(round(util_fn_igv()/100,3))),
							    substring(util_fn_cd_igv() for 2 from length(util_fn_cd_igv())-1),
							    ($totalimporte - ($totalimporte/(1+(round(util_fn_igv()/100,3)))) ),
							    current_timestamp,
							    'VEN_LIQ_VALES'
		     				);
                    ";

            //echo $consulta;
            $in = $sqlca->query($consulta);
            if ($in < 0) {
                throw new Exception("Error al Insertar la cabecera de cuentas por cobrar($SERIE, $NUMERO_DOCUMENTO_ANTI)");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    function InsertarDocumentoCCob_CabeceraDetalle($SERIE, $NUMERO_DOCUMENTO_ANTI, $codigo_cliente, $FEC_LIQUIDACION, $ch_liquidacion, $totalimporte) {
        global $sqlca;
        try {

		

            $consulta = " INSERT INTO ccob_ta_detalle
                      				(
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
							ch_numdocreferencia,
							ch_sucursal,
							dt_fecha_actualizacion,
							ch_auditorpc,
							ch_tipdocreferencia
                      				)
             					VALUES
                      				(
							'$codigo_cliente',
							'22',
							'$SERIE',
							LPAD(CAST('$NUMERO_DOCUMENTO_ANTI' AS bpchar),7,'0'),
							'001',
							'1',
							'$FEC_LIQUIDACION',
							'01',
							util_fn_tipo_cambio_dia('$FEC_LIQUIDACION'),
							$totalimporte,
							'12103',
							LPAD(CAST('$ch_liquidacion' AS bpchar),10,'0'),
							'001',
							current_timestamp,
							'VEN_LIQ_VALES',
							'42'
                      				);
                      
                    ";

            //echo $consulta;
            $in = $sqlca->query($consulta);
            if ($in < 0) {
                throw new Exception("Error al Insertar la Detalle del Documneto X cobrar('22', $SERIE, $NUMERO_DOCUMENTO_ANTI)");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    function procesoDocumnetoLiquidarConAnticipo($SERIE, $NUMERO_DOCUMENTO_ANTI, $codigo_cliente, $FEC_LIQUIDACION, $ch_liquidacion, $totalimporte) {
        global $sqlca;
        try {
            $codigo_cliente = trim($codigo_cliente);

            LiquidacionValesModel::InsertarDocumentoCCob_Cabecera($SERIE, $NUMERO_DOCUMENTO_ANTI, $codigo_cliente, $FEC_LIQUIDACION, $ch_liquidacion, $totalimporte);
            LiquidacionValesModel::InsertarDocumentoCCob_CabeceraDetalle($SERIE, $NUMERO_DOCUMENTO_ANTI, $codigo_cliente, $FEC_LIQUIDACION, $ch_liquidacion, $totalimporte);
            LiquidacionValesModel::ActualizarInt_documnetoAnticipo($ch_liquidacion, $NUMERO_DOCUMENTO_ANTI, '22', $SERIE);
        } catch (Exception $e) {
            return $e;
        }
    }

    function procesoDocumnetoLiquidarUnicoClienteXNormal($DOCUMENTO, $SERIE, $FEC_LIQUIDACION, $datosDetalledocum, $datosCliente, $datos_inicales_documento, $cadena_vales, $f_inicio, $f_fina, $codigo_hermandad,$estado_exo,$transfe_grat) {
        	global $sqlca;
            $exonerada = LiquidacionValesModel::GetTaxOptional();
		try {

            	$datos_intefas_web	= array();
            	$tipocambio		= LiquidacionValesModel::validarTipoCambio($FEC_LIQUIDACION);
            	$datosFactura		= array();

            	foreach ($datosDetalledocum as $anticipo => $cliente) {
                	foreach ($cliente as $clie => $sucursal) {
                    		foreach ($sucursal as $sucur => $articulos) {
                        		$datosFactura[$clie][$anticipo] = count($articulos);
                    		}
                	}
            	}

            	$maxitemfactura = 0;

            	if ($datos_inicales_documento['cantidaditem'] > 0) {
                	$maxitem = $datos_inicales_documento['cantidaditem'];

			foreach ($datosFactura as $cliente => $anticipo) {
                    		foreach ($anticipo as $nombanticipo => $valor) {
                        		if ($maxitem == '1') {
                            			$datosFactura[$cliente][$nombanticipo] = $valor;
                        		} else {
                            			if ($valor % $maxitem == 0) {
		                        		$maxitemfactura = ($valor / $maxitem);
		                        		$datosFactura[$cliente][$nombanticipo] = $maxitemfactura;
                            			} else {
		                        		$maxitemfactura = (int) ($valor / $maxitem);
		                        		$datosFactura[$cliente][$nombanticipo] = ($maxitemfactura + 1);
                            			}
                        		}
                    		}
                	}
            	}

            	//Insercion de facturas Simultanea(harcord)
            	$array_cod_liquidacion	= array();
            	$num_liquidacion	= $datos_inicales_documento['num_LV'];
            	$NUMERO_DOCUMENTO 	= trim($datos_inicales_documento['num_act_documneto']);
            	$codigo_cliente 	= trim($datosCliente['cli_codigo']);
            	$ALMACEN 		= trim($datos_inicales_documento['ch_almacen']);
            	$cli_fpago_credito 	= trim($datosCliente['cli_fpago_credito']);
            	$cli_ruc 		= trim($datosCliente['cli_ruc']);
            	$cli_comp_direccion 	= trim($datosCliente['cli_comp_direccion']);
            	$cli_direccion 		= trim($datosCliente['cli_direccion']);
            	$cli_razsocial 		= trim($datosCliente['cli_razsocial']);
            	$areglo_datos_documento = array();
            	$inicio 		= 1;
            	$maxitem 		= $datos_inicales_documento['cantidaditem'];
            	$final 			= $datos_inicales_documento['cantidaditem'];

            	foreach ($datosFactura as $cliente => $anticipo) {

		        foreach ($anticipo as $nombanticipo => $valor) {

				for ($i = 0; $i < $valor; $i++) {

				        $num_liquidacion	= $num_liquidacion + 1;
				        $NUMERO_DOCUMENTO	= $NUMERO_DOCUMENTO + 1;
				        $num_liquidacion_cade	= str_pad($num_liquidacion, 10, '0', STR_PAD_LEFT);
                    if ($estado_exo==1 && $exonerada ==true || $transfe_grat==1){//cai
				        LiquidacionValesModel::InsertarDocumentocabeceraExo($DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO, $codigo_cliente, $num_liquidacion_cade, $FEC_LIQUIDACION, $ALMACEN, $cli_fpago_credito);
                    }else{
                        LiquidacionValesModel::InsertarDocumentocabecera($DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO, $codigo_cliente, $num_liquidacion_cade, $FEC_LIQUIDACION, $ALMACEN, $cli_fpago_credito);
                    }
                        //LiquidacionValesModel::InsertarDocumentocabecera($DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO, $codigo_cliente, $num_liquidacion_cade, $FEC_LIQUIDACION, $ALMACEN, $cli_fpago_credito);
                        LiquidacionValesModel::InsertarDocumentocomplemto($DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO, $codigo_cliente, $FEC_LIQUIDACION, $cli_ruc, $cli_direccion, $cli_comp_direccion, $cli_razsocial);
				        $areglo_datos_documento[]	= array("ini" => $inicio, "final" => $final, "num_doc" => $NUMERO_DOCUMENTO, "cli" => $codigo_cliente, "tipo_doc" => $DOCUMENTO, "serie" => $SERIE, "fecha_liquidacion" => $FEC_LIQUIDACION, "num_liquidacion" => $num_liquidacion_cade);
				        $tipo				= ($DOCUMENTO == '10') ? 'Factura' : 'Boleta';
				        $datos_intefas_web[]		= array("num_doc" => $NUMERO_DOCUMENTO, "cli" => $codigo_cliente, "tipo_doc" => $DOCUMENTO, "serie" => $SERIE, "fecha_liquidacion" => $FEC_LIQUIDACION, "num_liquidacion" => $num_liquidacion_cade, 'tipo' => $tipo, "rz" => $cli_razsocial);
				        $inicio 			= $final + 1;
				        $final 				= $final + $maxitem;

				        LiquidacionValesModel::ActualizarInt_documneto($num_liquidacion, $NUMERO_DOCUMENTO, $DOCUMENTO, $SERIE);
				        //actualizar int_documnetod
		            	}

		        }

            	}

            	$inicio_vales = 1;
            	$fecha_liqu_fact = '';

            	foreach ($datosDetalledocum as $anticipo => $cliente) {
                	foreach ($cliente as $clie => $sucursal) {
                    		foreach ($sucursal as $sucur => $articulos) {
                        		foreach ($articulos as $art_codigo => $valores) {
                            			foreach ($areglo_datos_documento as $value) {

                                			if ($inicio_vales >= $value['ini'] && $inicio_vales <= $value['final']) {
                                    				$NUMERO_DOCUMENTO = $value['num_doc'];
                                    				$NUMERO_LIQUIDACION = $value['num_liquidacion'];
                                    				$fecha_liqu_fact = $value['fecha_liquidacion'];
                                			}
                            			}

                    if ($estado_exo==1 && $exonerada ==true){//cai
                        LiquidacionValesModel::InsertarDocumentoDetalleExo($DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO, $codigo_cliente, $art_codigo, $valores['importe'], $valores['cantidad'], $valores['des']);
                    }else{
                        LiquidacionValesModel::InsertarDocumentoDetalle($DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO, $codigo_cliente, $art_codigo, $valores['importe'], $valores['cantidad'], $valores['des']);
                    }

                        //LiquidacionValesModel::InsertarDocumentoDetalle($DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO, $codigo_cliente, $art_codigo, $valores['importe'], $valores['cantidad'], $valores['des']);
						LiquidacionValesModel::ActualizarVales_Liquidacion($DOCUMENTO, $f_inicio, $f_fina, $codigo_cliente, $cadena_vales, $SERIE, $NUMERO_DOCUMENTO, $art_codigo, $NUMERO_LIQUIDACION, $fecha_liqu_fact, $valores['importe'], $codigo_hermandad);
						$inicio_vales++;
					}
				}
			}
		}

		foreach ($areglo_datos_documento as $actualizarMontos) {
                	LiquidacionValesModel::ActualizarMontoDocumetos($actualizarMontos['tipo_doc'], $actualizarMontos['serie'], $actualizarMontos['num_doc'], $actualizarMontos['cli'], $actualizarMontos['fecha_liquidacion'], $actualizarMontos['num_liquidacion']);
		}

		return $datos_intefas_web;

		} catch (Exception $e) {
			throw $e;
		}

	}

    function procesoDocumnetoLiquidarUnicoClienteXPlaca($DOCUMENTO, $SERIE, $FEC_LIQUIDACION, $datosDetalledocum, $datosCliente, $datos_inicales_documento, $cadena_vales, $f_inicio, $f_fina, $codigo_hermandad,$estado_exo,$transfe_grat) {
        global $sqlca;
        $exonerada = LiquidacionValesModel::GetTaxOptional();
        try {
            $datos_intefas_web = array();
            $tipocambio = LiquidacionValesModel::validarTipoCambio($FEC_LIQUIDACION);
            $datosFactura = array();

            foreach ($datosDetalledocum as $anticipo => $cliente) {
                foreach ($cliente as $clie => $sucursal) {
                    foreach ($sucursal as $sucur => $placas) {
                        foreach ($placas as $pla => $articulos) {
                            $datosFactura[$clie][$anticipo][$pla] = count($articulos);
                        }
                    }
                }
            }

            $maxitemfactura = 0;
            $datosFactura_tmp = array();
            if ($datos_inicales_documento['cantidaditem'] > 0) {
                $maxitem = $datos_inicales_documento['cantidaditem'];
                foreach ($datosFactura as $cliente => $anticipo) {
                    foreach ($anticipo as $nombanticipo => $Arra_placa) {
                        foreach ($Arra_placa as $Kpla => $valor) {
                            if ($maxitem == '1') {
                                $datosFactura_tmp[$cliente][$nombanticipo][$Kpla] = $valor;
                            } else {
                                if ($valor % $maxitem == 0) {
                                    $maxitemfactura = ($valor / $maxitem);
                                    $datosFactura_tmp[$cliente][$nombanticipo][$Kpla] = $maxitemfactura;
                                } else {
                                    $maxitemfactura = (int) ($valor / $maxitem);
                                    $datosFactura_tmp[$cliente][$nombanticipo][$Kpla] = ($maxitemfactura + 1);
                                }
                            }
                        }
                    }
                }
            }

            //ya paso por aqui
            //Insercion de facturas Simultanea(harcord)
            $array_cod_liquidacion = array();
            $num_liquidacion = $datos_inicales_documento['num_LV'];

            $NUMERO_DOCUMENTO = trim($datos_inicales_documento['num_act_documneto']);
            $codigo_cliente = trim($datosCliente['cli_codigo']);
            $ALMACEN = trim($datos_inicales_documento['ch_almacen']);
            $cli_fpago_credito = trim($datosCliente['cli_fpago_credito']);
            $cli_ruc = trim($datosCliente['cli_ruc']);
            $cli_comp_direccion = trim($datosCliente['cli_comp_direccion']);
            $cli_direccion = trim($datosCliente['cli_direccion']);
            $cli_razsocial = trim($datosCliente['cli_razsocial']);

            $areglo_datos_documento = array();
            $inicio = 1;
            $maxitem = $datos_inicales_documento['cantidaditem'];
            $final = $datos_inicales_documento['cantidaditem'];

            foreach ($datosFactura_tmp as $cliente => $anticipo) {
                foreach ($anticipo as $nombanticipo => $Arra_placa) {

                    foreach ($Arra_placa as $Kpla => $valor) {
                        for ($i = 0; $i < $valor; $i++) {
                            $num_liquidacion = $num_liquidacion + 1;
                            $NUMERO_DOCUMENTO = $NUMERO_DOCUMENTO + 1;
                            $num_liquidacion_cade = str_pad($num_liquidacion, 10, '0', STR_PAD_LEFT);
                            //LiquidacionValesModel::InsertarDocumentocabecera($DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO, $codigo_cliente, $num_liquidacion_cade, $FEC_LIQUIDACION, $ALMACEN, $cli_fpago_credito);
                        if ($estado_exo==1  && $exonerada ==true || $transfe_grat==1){//cai
                            LiquidacionValesModel::InsertarDocumentocabeceraExo($DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO, $codigo_cliente, $num_liquidacion_cade, $FEC_LIQUIDACION, $ALMACEN, $cli_fpago_credito);
                        }else{
                            LiquidacionValesModel::InsertarDocumentocabecera($DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO, $codigo_cliente, $num_liquidacion_cade, $FEC_LIQUIDACION, $ALMACEN, $cli_fpago_credito);
                        }
                            LiquidacionValesModel::InsertarDocumentocomplemto($DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO, $codigo_cliente, $FEC_LIQUIDACION, $cli_ruc, $cli_direccion, $cli_comp_direccion, $cli_razsocial);
                            $areglo_datos_documento[$Kpla][$i] = array("num_doc" => $NUMERO_DOCUMENTO, "cli" => $codigo_cliente, "tipo_doc" => $DOCUMENTO, "serie" => $SERIE, "fecha_liquidacion" => $FEC_LIQUIDACION, "num_liquidacion" => $num_liquidacion_cade, "max_item" => $maxitem, "num_fac" => $i, "ocupada" => 0);

                            $tipo = ($DOCUMENTO == '10') ? 'Factura' : 'Boleta';
                            $datos_intefas_web[] = array("num_doc" => $NUMERO_DOCUMENTO, "cli" => $codigo_cliente, "tipo_doc" => $DOCUMENTO, "serie" => $SERIE, "fecha_liquidacion" => $FEC_LIQUIDACION, "num_liquidacion" => $num_liquidacion_cade, 'tipo' => $tipo, "placa" => $Kpla, "rz" => $cli_razsocial);
                            $inicio = $final + 1;
                            $final = $final + $maxitem;
                            LiquidacionValesModel::ActualizarInt_documneto($num_liquidacion, $NUMERO_DOCUMENTO, $DOCUMENTO, $SERIE);
                        }
                    }
                }
            }

            $NUMERO_LIQUIDACION = '';
            $fecha_liqu_fact = '';
            foreach ($datosDetalledocum as $anticipo => $cliente) {
                foreach ($cliente as $clie => $sucursal) {
                    foreach ($sucursal as $sucur => $Aplaca) {
                        foreach ($Aplaca as $kplaca => $articulos) {
                            $inicio_vales = 1;
                            foreach ($articulos as $art_codigo => $valores) {

                                foreach ($areglo_datos_documento[$kplaca] as $index => $Arraydatos) {

                                    $ocupado = $Arraydatos['ocupada'];
                                    if ($ocupado < $Arraydatos['max_item']) {
                                        $fecha_liqu_fact = $Arraydatos['fecha_liquidacion'];
                                        $NUMERO_DOCUMENTO = $Arraydatos['num_doc'];
                                        $NUMERO_LIQUIDACION = $Arraydatos['num_liquidacion'];
                                        $areglo_datos_documento[$kplaca][$index]['ocupada'] = $areglo_datos_documento[$kplaca][$index]['ocupada'] + 1;
                                        break;
                                    }
                                }
                                //LiquidacionValesModel::InsertarDocumentoDetalle($DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO, $codigo_cliente, $art_codigo, $valores['importe'], $valores['cantidad'], $valores['des']);
                                if ($estado_exo==1  && $exonerada ==true){//cai
                                    LiquidacionValesModel::InsertarDocumentoDetalleExo($DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO, $codigo_cliente, $art_codigo, $valores['importe'], $valores['cantidad'], $valores['des']);
                                }else{
                                    LiquidacionValesModel::InsertarDocumentoDetalle($DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO, $codigo_cliente, $art_codigo, $valores['importe'], $valores['cantidad'], $valores['des']);
                                }
                                LiquidacionValesModel::ActualizarVales_LiquidacionXPlaca($DOCUMENTO, $f_inicio, $f_fina, $codigo_cliente, $cadena_vales, $SERIE, $NUMERO_DOCUMENTO, $kplaca, $art_codigo, $NUMERO_LIQUIDACION, $fecha_liqu_fact, $valores['importe'], $codigo_hermandad);

                                $inicio_vales++;
                            }
                        }
                    }
                }
            }

            foreach ($areglo_datos_documento as $actualizarMontosArray) {
                foreach ($actualizarMontosArray as $actualizarMontos) {
                    LiquidacionValesModel::ActualizarMontoDocumetos($actualizarMontos['tipo_doc'], $actualizarMontos['serie'], $actualizarMontos['num_doc'], $actualizarMontos['cli'], $actualizarMontos['fecha_liquidacion'], $actualizarMontos['num_liquidacion']);
                }
            }

            return $datos_intefas_web;
        } catch (Exception $e) {
            throw $e;
        }
    }

    function procesoDocumnetoLiquidarUnicoClienteXNotaDespacho($DOCUMENTO, $SERIE, $FEC_LIQUIDACION, $datosDetalledocum, $datosCliente, $datos_inicales_documento, $cadena_vales, $f_inicio, $f_fina, $codigo_hermandad,$estado_exo,$transfe_grat) {
        global $sqlca;
        $exonerada = LiquidacionValesModel::GetTaxOptional();
        try {
            $datos_intefas_web = array();
            $tipocambio = LiquidacionValesModel::validarTipoCambio($FEC_LIQUIDACION);
            $datosFactura = array();

            foreach ($datosDetalledocum as $anticipo => $cliente) {
                foreach ($cliente as $clie => $sucursal) {
                    foreach ($sucursal as $sucur => $ch_documentoArray) {
                        foreach ($ch_documentoArray as $chdoc => $articulos) {
                            $datosFactura[$clie][$anticipo][$chdoc] = count($articulos);
                        }
                    }
                }
            }

            $maxitemfactura = 0;
            $datosFactura_tmp = array();
            if ($datos_inicales_documento['cantidaditem'] > 0) {
                $maxitem = $datos_inicales_documento['cantidaditem'];
                foreach ($datosFactura as $cliente => $anticipo) {
                    foreach ($anticipo as $nombanticipo => $ch_documentoArray) {
                        foreach ($ch_documentoArray as $Kchdoc => $valor) {
                            if ($maxitem == '1') {
                                $datosFactura_tmp[$cliente][$nombanticipo][$Kchdoc] = $valor;
                            } else {
                                if ($valor % $maxitem == 0) {
                                    $maxitemfactura = ($valor / $maxitem);
                                    $datosFactura_tmp[$cliente][$nombanticipo][$Kchdoc] = $maxitemfactura;
                                } else {
                                    $maxitemfactura = (int) ($valor / $maxitem);
                                    $datosFactura_tmp[$cliente][$nombanticipo][$Kchdoc] = ($maxitemfactura + 1);
                                }
                            }
                        }
                    }
                }
            }

            //ya paso por aqui
            //Insercion de facturas Simultanea(harcord)
            $array_cod_liquidacion = array();
            $num_liquidacion = $datos_inicales_documento['num_LV'];

            $NUMERO_DOCUMENTO = trim($datos_inicales_documento['num_act_documneto']);
            $codigo_cliente = trim($datosCliente['cli_codigo']);
            $ALMACEN = trim($datos_inicales_documento['ch_almacen']);
            $cli_fpago_credito = trim($datosCliente['cli_fpago_credito']);
            $cli_ruc = trim($datosCliente['cli_ruc']);
            $cli_comp_direccion = trim($datosCliente['cli_comp_direccion']);
            $cli_direccion = trim($datosCliente['cli_direccion']);
            $cli_razsocial = trim($datosCliente['cli_razsocial']);

            $areglo_datos_documento = array();
            $inicio = 1;
            $maxitem = $datos_inicales_documento['cantidaditem'];
            $final = $datos_inicales_documento['cantidaditem'];

            foreach ($datosFactura_tmp as $cliente => $anticipo) {
                foreach ($anticipo as $nombanticipo => $ch_documentoArray) {

                    foreach ($ch_documentoArray as $Kchdoc => $valor) {
                        for ($i = 0; $i < $valor; $i++) {
                            $num_liquidacion = $num_liquidacion + 1;
                            $NUMERO_DOCUMENTO = $NUMERO_DOCUMENTO + 1;
                            $num_liquidacion_cade = str_pad($num_liquidacion, 10, '0', STR_PAD_LEFT);
                            //LiquidacionValesModel::InsertarDocumentocabecera($DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO, $codigo_cliente, $num_liquidacion_cade, $FEC_LIQUIDACION, $ALMACEN, $cli_fpago_credito);
                            if ($estado_exo==1 && $exonerada ==true || $transfe_grat==1){//cai
                                LiquidacionValesModel::InsertarDocumentocabeceraExo($DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO, $codigo_cliente, $num_liquidacion_cade, $FEC_LIQUIDACION, $ALMACEN, $cli_fpago_credito);
                            }else{
                                LiquidacionValesModel::InsertarDocumentocabecera($DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO, $codigo_cliente, $num_liquidacion_cade, $FEC_LIQUIDACION, $ALMACEN, $cli_fpago_credito);
                            }
                            LiquidacionValesModel::InsertarDocumentocomplemto($DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO, $codigo_cliente, $FEC_LIQUIDACION, $cli_ruc, $cli_direccion, $cli_comp_direccion, $cli_razsocial);
                            $areglo_datos_documento[$Kchdoc][$i] = array("num_doc" => $NUMERO_DOCUMENTO, "cli" => $codigo_cliente, "tipo_doc" => $DOCUMENTO, "serie" => $SERIE, "fecha_liquidacion" => $FEC_LIQUIDACION, "num_liquidacion" => $num_liquidacion_cade, "max_item" => $maxitem, "num_fac" => $i, "ocupada" => 0);
                            $tipo = ($DOCUMENTO == '10') ? 'Factura' : 'Boleta';
                            $datos_intefas_web[] = array("num_doc" => $NUMERO_DOCUMENTO, "cli" => $codigo_cliente, "tipo_doc" => $DOCUMENTO, "serie" => $SERIE, "fecha_liquidacion" => $FEC_LIQUIDACION, "num_liquidacion" => $num_liquidacion_cade, 'tipo' => $tipo, "ND" => $Kchdoc, "rz" => $cli_razsocial);

                            $inicio = $final + 1;
                            $final = $final + $maxitem;
                            LiquidacionValesModel::ActualizarInt_documneto($num_liquidacion, $NUMERO_DOCUMENTO, $DOCUMENTO, $SERIE);
                        }
                    }
                }
            }

            $NUMERO_LIQUIDACION = '';
            $Arraydatos = '';
            $fecha_liqu_fact = '';
            foreach ($datosDetalledocum as $anticipo => $cliente) {
                foreach ($cliente as $clie => $sucursal) {
                    foreach ($sucursal as $sucur => $Achdoc) {
                        foreach ($Achdoc as $kchdoc_ale => $articulos) {
                            $inicio_vales = 1;
                            foreach ($articulos as $art_codigo => $valores) {

                                foreach ($areglo_datos_documento[$kchdoc_ale] as $index => $Arraydatos) {

                                    $ocupado = $Arraydatos['ocupada'];
                                    if ($ocupado < $Arraydatos['max_item']) {
                                        $fecha_liqu_fact = $Arraydatos['fecha_liquidacion'];
                                        $NUMERO_DOCUMENTO = $Arraydatos['num_doc'];
                                        $NUMERO_LIQUIDACION = $Arraydatos['num_liquidacion'];
                                        $areglo_datos_documento[$kchdoc_ale][$index]['ocupada'] = $areglo_datos_documento[$kchdoc_ale][$index]['ocupada'] + 1;
                                        break;
                                    }
                                }
                                //LiquidacionValesModel::InsertarDocumentoDetalle($DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO, $codigo_cliente, $art_codigo, $valores['importe'], $valores['cantidad'], $valores['des']);
                                if ($estado_exo==1 && $exonerada ==true){//cai
                                    LiquidacionValesModel::InsertarDocumentoDetalleExo($DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO, $codigo_cliente, $art_codigo, $valores['importe'], $valores['cantidad'], $valores['des']);
                                }else{
                                    LiquidacionValesModel::InsertarDocumentoDetalle($DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO, $codigo_cliente, $art_codigo, $valores['importe'], $valores['cantidad'], $valores['des']);
                                }
                                LiquidacionValesModel::ActualizarVales_LiquidacionXNotaDespacho($DOCUMENTO, $f_inicio, $f_fina, $codigo_cliente, $cadena_vales, $SERIE, $NUMERO_DOCUMENTO, $kchdoc_ale, $NUMERO_LIQUIDACION, $art_codigo, $fecha_liqu_fact, $valores['importe'], $codigo_hermandad);

                                $inicio_vales++;
                            }
                        }
                    }
                }
            }

            foreach ($areglo_datos_documento as $actualizarMontosArray) {
                foreach ($actualizarMontosArray as $actualizarMontos) {
                    LiquidacionValesModel::ActualizarMontoDocumetos($actualizarMontos['tipo_doc'], $actualizarMontos['serie'], $actualizarMontos['num_doc'], $actualizarMontos['cli'], $actualizarMontos['fecha_liquidacion'], $actualizarMontos['num_liquidacion']);
                }
            }

            return $datos_intefas_web;
        } catch (Exception $e) {
            throw $e;
        }
    }

    function procesoDocumnetoLiquidarUnicoClienteXProducto($DOCUMENTO, $SERIE, $FEC_LIQUIDACION, $datosDetalledocum, $datosCliente, $datos_inicales_documento, $cadena_vales, $f_inicio, $f_fina, $codigo_hermandad,$estado_exo,$transfe_grat) {
        global $sqlca;
        $exonerada = LiquidacionValesModel::GetTaxOptional();
        try {
            $datos_intefas_web = array();
            $tipocambio = LiquidacionValesModel::validarTipoCambio($FEC_LIQUIDACION);
            $datosFactura = array();

            foreach ($datosDetalledocum as $anticipo => $cliente) {
                foreach ($cliente as $clie => $sucursal) {
                    foreach ($sucursal as $sucur => $articulos) {
                        $datosFactura[$clie][$anticipo] = count($articulos);
                    }
                }
            }
            $maxitemfactura = 0;
            if ($datos_inicales_documento['cantidaditem'] > 0) {
                $maxitem = $datos_inicales_documento['cantidaditem'];
                foreach ($datosFactura as $cliente => $anticipo) {
                    foreach ($anticipo as $nombanticipo => $valor) {
                        $datosFactura[$cliente][$nombanticipo] = $valor;
                    }
                }
            }

            //Insercion de facturas Simultanea(harcord)
            $array_cod_liquidacion = array();
            $num_liquidacion = $datos_inicales_documento['num_LV'];

            $NUMERO_DOCUMENTO = trim($datos_inicales_documento['num_act_documneto']);
            $codigo_cliente = trim($datosCliente['cli_codigo']);
            $ALMACEN = trim($datos_inicales_documento['ch_almacen']);
            $cli_fpago_credito = trim($datosCliente['cli_fpago_credito']);
            $cli_ruc = trim($datosCliente['cli_ruc']);
            $cli_comp_direccion = trim($datosCliente['cli_comp_direccion']);
            $cli_direccion = trim($datosCliente['cli_direccion']);
            $cli_razsocial = trim($datosCliente['cli_razsocial']);

            $areglo_datos_documento = array();
            $inicio = 1;
            /* $maxitem = $datos_inicales_documento['cantidaditem'];
              $final = $datos_inicales_documento['cantidaditem']; */

            $maxitem = 1;
            $final = 1;

            foreach ($datosFactura as $cliente => $anticipo) {
                foreach ($anticipo as $nombanticipo => $valor) {
                    for ($i = 0; $i < $valor; $i++) {
                        $num_liquidacion = $num_liquidacion + 1;
                        $NUMERO_DOCUMENTO = $NUMERO_DOCUMENTO + 1;
                        $num_liquidacion_cade = str_pad($num_liquidacion, 10, '0', STR_PAD_LEFT);
                        //LiquidacionValesModel::InsertarDocumentocabecera($DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO, $codigo_cliente, $num_liquidacion_cade, $FEC_LIQUIDACION, $ALMACEN, $cli_fpago_credito);
                        if ($estado_exo==1 && $exonerada ==true || $transfe_grat==1){//cai
                            LiquidacionValesModel::InsertarDocumentocabeceraExo($DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO, $codigo_cliente, $num_liquidacion_cade, $FEC_LIQUIDACION, $ALMACEN, $cli_fpago_credito);
                        }else{
                            LiquidacionValesModel::InsertarDocumentocabecera($DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO, $codigo_cliente, $num_liquidacion_cade, $FEC_LIQUIDACION, $ALMACEN, $cli_fpago_credito);
                        }
                        LiquidacionValesModel::InsertarDocumentocomplemto($DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO, $codigo_cliente, $FEC_LIQUIDACION, $cli_ruc, $cli_direccion, $cli_comp_direccion, $cli_razsocial);
                        $areglo_datos_documento[$num_liquidacion_cade] = array("ini" => $inicio, "final" => $final, "num_doc" => $NUMERO_DOCUMENTO, "cli" => $codigo_cliente, "tipo_doc" => $DOCUMENTO, "serie" => $SERIE, "fecha_liquidacion" => $FEC_LIQUIDACION, "num_liquidacion" => $num_liquidacion_cade);

                        $tipo = ($DOCUMENTO == '10') ? 'Factura' : 'Boleta';
                        $datos_intefas_web[$num_liquidacion_cade] = array("num_doc" => $NUMERO_DOCUMENTO, "cli" => $codigo_cliente, "tipo_doc" => $DOCUMENTO, "serie" => $SERIE, "fecha_liquidacion" => $FEC_LIQUIDACION, "num_liquidacion" => $num_liquidacion_cade, 'tipo' => $tipo, "rz" => $cli_razsocial);

                        $inicio = $final + 1;
                        $final = $final + $maxitem;
                        LiquidacionValesModel::ActualizarInt_documneto($num_liquidacion, $NUMERO_DOCUMENTO, $DOCUMENTO, $SERIE);
                    }
                }
            }

            $inicio_vales = 1;
            $NUMERO_LIQUIDACION = '';
            $serie_correalativas = array();
            $fecha_liqu_fact = '';
            foreach ($datosDetalledocum as $anticipo => $cliente) {
                foreach ($cliente as $clie => $sucursal) {
                    foreach ($sucursal as $sucur => $articulos) {
                        foreach ($articulos as $art_codigo => $valores) {
                            foreach ($areglo_datos_documento as $value) {

                                if ($inicio_vales >= $value['ini'] && $inicio_vales <= $value['final']) {
                                    $NUMERO_DOCUMENTO = $value['num_doc'];
                                    $NUMERO_LIQUIDACION = $value['num_liquidacion'];
                                    $fecha_liqu_fact = $value['fecha_liquidacion'];
                                }
                            }

                            //LiquidacionValesModel::InsertarDocumentoDetalle($DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO, $codigo_cliente, $art_codigo, $valores['importe'], $valores['cantidad'], $valores['des']);
                            if ($estado_exo==1 && $exonerada ==true){//cai
                                LiquidacionValesModel::InsertarDocumentoDetalleExo($DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO, $codigo_cliente, $art_codigo, $valores['importe'], $valores['cantidad'], $valores['des']);
                            }else{
                                LiquidacionValesModel::InsertarDocumentoDetalle($DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO, $codigo_cliente, $art_codigo, $valores['importe'], $valores['cantidad'], $valores['des']);
                            }
                            // $NUMERO_DOCUMENTO = str_pad($NUMERO_DOCUMENTO, 7, '0', STR_PAD_LEFT);
                            LiquidacionValesModel::ActualizarVales_LiquidacionXProducto($DOCUMENTO, $f_inicio, $f_fina, $codigo_cliente, $cadena_vales, $SERIE, $NUMERO_DOCUMENTO, $art_codigo, $NUMERO_LIQUIDACION, $fecha_liqu_fact, $valores['importe'], $codigo_hermandad);
                            $datos_intefas_web[$NUMERO_LIQUIDACION]['producto'] = $art_codigo;
                            $inicio_vales++;
                        }
                    }
                }
            }

            foreach ($areglo_datos_documento as $actualizarMontos) {
                LiquidacionValesModel::ActualizarMontoDocumetos($actualizarMontos['tipo_doc'], $actualizarMontos['serie'], $actualizarMontos['num_doc'], $actualizarMontos['cli'], $actualizarMontos['fecha_liquidacion'], $actualizarMontos['num_liquidacion']);
            }

            return $datos_intefas_web;
        } catch (Exception $e) {
            throw $e;
        }
    }

    function validarTipoCambio($FEC_LIQUIDACION) {
        global $sqlca;
        try {
            $sql = "select util_fn_tipo_cambio_dia('$FEC_LIQUIDACION')";

            $sqlca->query($sql);
            while ($reg = $sqlca->fetchRow()) {
                $registro[] = $reg;
            }
            if ($registro == NULL) {
                throw new Exception("No se registro tipo de cambio en la fecha " . $FEC_LIQUIDACION);
            } else {
                return $registro[0][0];
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    function ActualizarVales_LiquidacionXcobrar($DOCUMENTO, $fecha_inicio, $fecha_final, $ruc, $vales, $serie, $num_documneto, $articulo, $NUMERO_LIQUIDACION, $fecha_liquidacion, $cod_hermandad) {
        global $sqlca;
        try {

            //Esta pendiente lo de sucursal
            $sqlselect = " SELECT  
                            val_ta_cabecera.ch_sucursal,
                            val_ta_cabecera.dt_fecha,
                            val_ta_cabecera.ch_documento,
                            val_ta_cabecera.ch_cliente,
                            val_ta_cabecera.ch_placa,
                            val_ta_detalle.ch_articulo,
                            val_ta_detalle.nu_importe
                            FROM 
                            val_ta_cabecera,val_ta_detalle
                            WHERE val_ta_detalle.dt_fecha BETWEEN to_date('$fecha_inicio', 'YYYY-MM-DD') AND to_date('$fecha_final', 'YYYY-MM-DD')
                            AND val_ta_cabecera.ch_cliente='$ruc'
                            AND val_ta_cabecera.ch_sucursal=val_ta_detalle.ch_sucursal
                            AND val_ta_cabecera.dt_fecha=val_ta_detalle.dt_fecha
                            AND val_ta_cabecera.ch_documento=val_ta_detalle.ch_documento
                            AND (val_ta_cabecera.ch_liquidacion is null or val_ta_cabecera.ch_liquidacion='')
                            AND val_ta_cabecera.ch_documento in $vales and val_ta_detalle.ch_articulo='$articulo' 
                            group by 
                            val_ta_cabecera.ch_sucursal,
                            val_ta_cabecera.dt_fecha,
                            val_ta_cabecera.ch_documento,
                            val_ta_cabecera.ch_cliente,
                            val_ta_cabecera.ch_placa,
                            val_ta_detalle.ch_articulo,
                            val_ta_detalle.nu_importe ";
            //  echo $sqlselect;

            $estado = $sqlca->query($sqlselect);
            //echo $estado;
            if ($estado < 0) {
                throw new Exception('Error consulta las notas de despacho de liquidacion de Cuenta por COBRAR(' . $ruc . ')*');
            }

            $arrayvales = array();
            while ($reg = $sqlca->fetchRow()) {
                $arrayvales[] = $reg;
            }

            foreach ($arrayvales as $fila) {
                $ch_sucursal = trim($fila['ch_sucursal']);
                $dt_fecha = trim($fila['dt_fecha']);
                $ch_documento = trim($fila['ch_documento']);
                $ch_num_importe = $fila['nu_importe'];
                if ($ch_documento == '41') {
                    
                }
                $sql = "select count(*) as existente from val_ta_complemento_documento 
                       where ch_liquidacion='$NUMERO_LIQUIDACION' and ch_numeval='$ch_documento' 
                       and dt_fecha='$dt_fecha' and ch_fac_tipodocumento='$DOCUMENTO' 
                       and ch_fac_numerodocumento=LPAD(CAST('$num_documneto' AS bpchar), 7, '0')
                       and ch_fac_seriedocumento='$serie' and trim(art_codigo)=trim('$articulo');";
                $estado_s = $sqlca->query($sql);
                $regcount = $sqlca->fetchRow();
                if ($regcount['existente'] == 0) {

                    $consulta = "
                    Insert into val_ta_complemento_documento(
                    ch_sucursal,
                    dt_fecha,
                    ch_numeval,
                    ch_fac_tipodocumento,
                    ch_fac_seriedocumento,
                    ch_fac_numerodocumento,
                    ch_liquidacion,
                    art_codigo,
                    accion,
                    ch_placa,
                    fecha_liquidacion,
                    ch_cliente,
                    nu_fac_valortotal,
                    cod_hermandad) 
                    values('$ch_sucursal','$dt_fecha','$ch_documento','$DOCUMENTO','$serie',LPAD(CAST('$num_documneto' AS bpchar), 7, '0'),'$NUMERO_LIQUIDACION','$articulo','XCOBRAR','-','$fecha_liquidacion','$ruc','$ch_num_importe','$cod_hermandad');";

                    $estado = $sqlca->query($consulta);
                    if ($estado < 0) {
                        throw new Exception('Error Actualizacion NUMLIQUIDACION Normal en los vales (' . $ruc . ')');
                    }
                }
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    function ActualizarVales_Liquidacion($DOCUMENTO, $fecha_inicio, $fecha_final, $ruc, $vales, $serie, $num_documneto, $articulo, $NUMERO_LIQUIDACION, $fecha_liquidacion, $impo = 0, $cod_hermandad) {
        global $sqlca;
        try {

            //Esta pendiente lo de sucursal
            $sqlselect = "SELECT  
                            val_ta_cabecera.ch_sucursal,
                            val_ta_cabecera.dt_fecha,
                            val_ta_cabecera.ch_documento,
                            val_ta_cabecera.ch_cliente,
                            val_ta_cabecera.ch_placa,
                            val_ta_detalle.ch_articulo,
                             val_ta_detalle.nu_importe
                            FROM 
                            val_ta_cabecera,val_ta_detalle
                            WHERE val_ta_detalle.dt_fecha BETWEEN to_date('$fecha_inicio', 'YYYY-MM-DD') AND to_date('$fecha_final', 'YYYY-MM-DD')
                            AND val_ta_cabecera.ch_cliente='$ruc '
                            AND val_ta_cabecera.ch_sucursal=val_ta_detalle.ch_sucursal
                            AND val_ta_cabecera.dt_fecha=val_ta_detalle.dt_fecha
                            AND val_ta_cabecera.ch_documento=val_ta_detalle.ch_documento
                            AND (val_ta_cabecera.ch_liquidacion is null or val_ta_cabecera.ch_liquidacion='')
                            AND val_ta_cabecera.ch_documento in $vales and val_ta_detalle.ch_articulo='$articulo' 
                            group by 
                            val_ta_cabecera.ch_sucursal,
                            val_ta_cabecera.dt_fecha,
                            val_ta_cabecera.ch_documento,
                            val_ta_cabecera.ch_cliente,
                            val_ta_cabecera.ch_placa,
                            val_ta_detalle.ch_articulo,
                            val_ta_detalle.nu_importe;";

            $estado = $sqlca->query($sqlselect);
            if ($estado < 0) {
                throw new Exception('Error consulta las notas de despacho de liquidacion Normal(' . $ruc . ')');
            }
            $arrayvales = array();
            while ($reg = $sqlca->fetchRow()) {
                $arrayvales[] = $reg;
            }
            foreach ($arrayvales as $fila) {
                $ch_sucursal = trim($fila['ch_sucursal']);
                $dt_fecha = trim($fila['dt_fecha']);
                $ch_documento = trim($fila['ch_documento']);
                $ch_num_importe = $fila['nu_importe'];
                if ($ch_documento == '41') {
                    
                }
                $sql = "select count(*) as existente from val_ta_complemento_documento 
                       where ch_liquidacion='$NUMERO_LIQUIDACION' and ch_numeval='$ch_documento' 
                       and dt_fecha='$dt_fecha' and ch_fac_tipodocumento='$DOCUMENTO' 
                       and ch_fac_numerodocumento=LPAD(CAST('$num_documneto' AS bpchar), 7, '0')
                       and ch_fac_seriedocumento='$serie' and trim(art_codigo)=trim('$articulo');";
                $estado_s = $sqlca->query($sql);
                $regcount = $sqlca->fetchRow();

                if ($regcount['existente'] == 0) {

                    $consulta = "
                    INSERT INTO val_ta_complemento_documento(
	                    ch_sucursal,
	                    dt_fecha,
	                    ch_numeval,
	                    ch_fac_tipodocumento,
	                    ch_fac_seriedocumento,
	                    ch_fac_numerodocumento,
	                    ch_liquidacion,
	                    art_codigo,
	                    accion,
	                    ch_placa,
	                    fecha_liquidacion,
	                    ch_cliente,
	                    nu_fac_valortotal,
	                    cod_hermandad
	                ) VALUES ( 
	                	'" . $ch_sucursal . "',
	                	'" . $dt_fecha . "',
	                	'" . $ch_documento . "',
	                	'" . $DOCUMENTO . "',
	                	'" . $serie . "',
	                	LPAD(CAST('" . $num_documneto . "' AS bpchar), 7, '0'),
	                	'" . $NUMERO_LIQUIDACION . "',
	                	'" . $articulo . "',
	                	'XNORMAL',
	                	'-',
	                	'" . $fecha_liquidacion . "',
	                	'" . $ruc . "',
	                	'" . $ch_num_importe . "',
	                	'" . $cod_hermandad . "');
	               	";

                    $estado = $sqlca->query($consulta);
                    if ($estado < 0) {
                        throw new Exception('Error Actualizacion NUMLIQUIDACION tipo - Normal en los vales (' . $consulta . ')');
                    }
                }
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    function ActualizarVales_LiquidacionXProducto($DOCUMENTO, $fecha_inicio, $fecha_final, $ruc, $vales, $serie, $num_documneto, $articulo, $NUMERO_LIQUIDACION, $fecha_liquidacion, $impo = 0, $cod_hermandad) {
        global $sqlca;
        try {

            $sqlselect = "SELECT  
                            val_ta_cabecera.ch_sucursal,
                            val_ta_cabecera.dt_fecha,
                            val_ta_cabecera.ch_documento,
                            val_ta_cabecera.ch_cliente,
                            val_ta_cabecera.ch_placa,
                            val_ta_detalle.ch_articulo,
                            val_ta_detalle.nu_importe
                            FROM 
                            val_ta_cabecera,val_ta_detalle
                            WHERE val_ta_detalle.dt_fecha BETWEEN to_date('$fecha_inicio', 'YYYY-MM-DD') AND to_date('$fecha_final', 'YYYY-MM-DD')
                            AND val_ta_cabecera.ch_cliente='$ruc '
                            AND val_ta_cabecera.ch_sucursal=val_ta_detalle.ch_sucursal
                            AND val_ta_cabecera.dt_fecha=val_ta_detalle.dt_fecha
                            AND val_ta_cabecera.ch_documento=val_ta_detalle.ch_documento
                            AND (val_ta_cabecera.ch_liquidacion is null or val_ta_cabecera.ch_liquidacion='')
                            AND val_ta_cabecera.ch_documento in $vales and val_ta_detalle.ch_articulo='$articulo' 
                            group by 
                            val_ta_cabecera.ch_sucursal,
                            val_ta_cabecera.dt_fecha,
                            val_ta_cabecera.ch_documento,
                            val_ta_cabecera.ch_cliente,
                            val_ta_cabecera.ch_placa,
                            val_ta_detalle.ch_articulo,
                            val_ta_detalle.nu_importe;";

            $estado = $sqlca->query($sqlselect);
            if ($estado < 0) {
                throw new Exception('Error consulta las notas de despacho de liquidacion X producto(' . $ruc . ')');
            }
            $arrayvales = array();
            while ($reg = $sqlca->fetchRow()) {

                $arrayvales[] = $reg;
            }

            foreach ($arrayvales as $fila) {
                $ch_sucursal = trim($fila['ch_sucursal']);
                $dt_fecha = trim($fila['dt_fecha']);
                $ch_documento = trim($fila['ch_documento']);
                $ch_num_importe = $fila['nu_importe'];

                $sql = "select count(*) as existente from val_ta_complemento_documento 
                       where ch_liquidacion='$NUMERO_LIQUIDACION' and ch_numeval='$ch_documento' 
                       and dt_fecha='$dt_fecha' and ch_fac_tipodocumento='$DOCUMENTO' 
                       and ch_fac_numerodocumento=LPAD(CAST('$num_documneto' AS bpchar), 7, '0')
                       and ch_fac_seriedocumento='$serie' and trim(art_codigo)=trim('$articulo');";
                $estado_s = $sqlca->query($sql);

                $regcount = $sqlca->fetchRow();
                if ($regcount['existente'] == 0) {

                    $consulta = "
                    INSERT INTO val_ta_complemento_documento(
	                    ch_sucursal,
	                    dt_fecha,
	                    ch_numeval,
	                    ch_fac_tipodocumento,
	                    ch_fac_seriedocumento,
	                    ch_fac_numerodocumento,
	                    ch_liquidacion,
	                    art_codigo,
	                    accion,
	                    ch_placa,
	                    fecha_liquidacion,
	                    ch_cliente,
	                    nu_fac_valortotal,
	                    cod_hermandad
					) VALUES (
						'" . $ch_sucursal . "',
						'" . $dt_fecha . "',
						'" . $ch_documento . "',
						'" . $DOCUMENTO . "',
						'" . $serie . "',
						LPAD(CAST('" . $num_documneto . "' AS bpchar), 7, '0'),
						'" . $NUMERO_LIQUIDACION . "',
						'" . $articulo . "',
						'XPRODUCTO',
						'-',
						'" . $fecha_liquidacion. "',
						'" . $ruc . "',
						" . $ch_num_importe . ",
						'" . $cod_hermandad . "'
					);";

                    $estado = $sqlca->query($consulta);
                    if ($estado < 0) {
                        throw new Exception('Error Actualizacion NUMLIQUIDACION en los vales (' . $ruc . ')');
                    }
                }
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    function ActualizarVales_LiquidacionXPlaca($DOCUMENTO, $fecha_inicio, $fecha_final, $ruc, $vales, $serie, $num_documneto, $placa, $articulo, $NUMERO_LIQUIDACION, $fecha_liquidacion, $impo = 0, $cod_hermandad) {
        global $sqlca;
        try {

            $sqlselect = "SELECT  
                            val_ta_cabecera.ch_sucursal,
                            val_ta_cabecera.dt_fecha,
                            val_ta_cabecera.ch_documento,
                            val_ta_cabecera.ch_cliente,
                            val_ta_cabecera.ch_placa,
                            val_ta_detalle.ch_articulo,
                            val_ta_detalle.nu_importe
                            FROM 
                            val_ta_cabecera,val_ta_detalle
                            WHERE val_ta_detalle.dt_fecha BETWEEN to_date('$fecha_inicio', 'YYYY-MM-DD') AND to_date('$fecha_final', 'YYYY-MM-DD')
                            AND val_ta_cabecera.ch_cliente='$ruc '
                            AND val_ta_cabecera.ch_sucursal=val_ta_detalle.ch_sucursal
                            AND val_ta_cabecera.dt_fecha=val_ta_detalle.dt_fecha
                            AND val_ta_cabecera.ch_documento=val_ta_detalle.ch_documento
                            AND (val_ta_cabecera.ch_liquidacion is null or val_ta_cabecera.ch_liquidacion='')
                            AND val_ta_cabecera.ch_documento in $vales and trim(val_ta_cabecera.ch_placa)=trim('$placa')  and val_ta_detalle.ch_articulo='$articulo' 
                            group by 
                            val_ta_cabecera.ch_sucursal,
                            val_ta_cabecera.dt_fecha,
                            val_ta_cabecera.ch_documento,
                            val_ta_cabecera.ch_cliente,
                            val_ta_cabecera.ch_placa,
                            val_ta_detalle.ch_articulo,
                            val_ta_detalle.nu_importe;";

            $estado = $sqlca->query($sqlselect);
            if ($estado < 0) {
                throw new Exception('Error consulta las notas de despacho de liquidacion X NotaDespacho(' . $ruc . ')');
            }
            $arrayvales = array();
            while ($reg = $sqlca->fetchRow()) {

                $arrayvales[] = $reg;
            }

            foreach ($arrayvales as $fila) {
                $ch_sucursal = trim($fila['ch_sucursal']);
                $dt_fecha = trim($fila['dt_fecha']);
                $ch_documento = trim($fila['ch_documento']);
                $ch_num_importe = $fila['nu_importe'];

                $sql = "select count(*) as existente from val_ta_complemento_documento 
                       where ch_liquidacion='$NUMERO_LIQUIDACION' and ch_numeval='$ch_documento' 
                       and dt_fecha='$dt_fecha' and ch_fac_tipodocumento='$DOCUMENTO' 
                       and ch_fac_numerodocumento=LPAD(CAST('$num_documneto' AS bpchar), 7, '0')
                       and ch_fac_seriedocumento='$serie' and trim(art_codigo)=trim('$articulo');";
                $estado_s = $sqlca->query($sql);
                $regcount = $sqlca->fetchRow();
                if ($regcount['existente'] == 0) {
                    $consulta = "
                    Insert into val_ta_complemento_documento(
                    ch_sucursal,
                    dt_fecha,
                    ch_numeval,
                    ch_fac_tipodocumento,
                    ch_fac_seriedocumento,
                    ch_fac_numerodocumento,
                    ch_liquidacion,
                    art_codigo,
                    accion,
                    ch_placa,
                    fecha_liquidacion,
                    ch_cliente,
                    nu_fac_valortotal,
                    cod_hermandad) 
                    values('$ch_sucursal','$dt_fecha','$ch_documento','$DOCUMENTO','$serie',LPAD(CAST('$num_documneto' AS bpchar), 7, '0'),'$NUMERO_LIQUIDACION','$articulo','XPLACA','$placa','$fecha_liquidacion','$ruc','$ch_num_importe','$cod_hermandad');";

                    $estado = $sqlca->query($consulta);
                    if ($estado < 0) {
                        throw new Exception('Error Actualizacion NUMLIQUIDACION en los vales (' . $ruc . ')');
                    }
                }
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    function ActualizarVales_LiquidacionXNotaDespacho($DOCUMENTO, $fecha_inicio, $fecha_final, $ruc, $vales, $serie, $num_documneto, $chdoc_vale, $NUMERO_LIQUIDACION, $art_codigo, $fecha_liquidacion, $impo = 0, $cod_hermandad) {
        global $sqlca;
        try {

            $sqlselect = "SELECT  
                            val_ta_cabecera.ch_sucursal,
                            val_ta_cabecera.dt_fecha,
                            val_ta_cabecera.ch_documento,
                            val_ta_cabecera.ch_cliente,
                            val_ta_cabecera.ch_placa,
                            val_ta_detalle.ch_articulo,
                            val_ta_detalle.nu_importe
                            FROM 
                            val_ta_cabecera,val_ta_detalle
                            WHERE val_ta_detalle.dt_fecha BETWEEN to_date('$fecha_inicio', 'YYYY-MM-DD') AND to_date('$fecha_final', 'YYYY-MM-DD')
                            AND val_ta_cabecera.ch_cliente='$ruc '
                            AND val_ta_cabecera.ch_sucursal=val_ta_detalle.ch_sucursal
                            AND val_ta_cabecera.dt_fecha=val_ta_detalle.dt_fecha
                            AND val_ta_cabecera.ch_documento=val_ta_detalle.ch_documento
                            AND (val_ta_cabecera.ch_liquidacion is null or val_ta_cabecera.ch_liquidacion='')
                            AND val_ta_cabecera.ch_documento in $vales and trim(val_ta_cabecera.ch_documento)=trim('$chdoc_vale') 
                            AND val_ta_detalle.ch_articulo='$art_codigo'  
                            group by 
                            val_ta_cabecera.ch_sucursal,
                            val_ta_cabecera.dt_fecha,
                            val_ta_cabecera.ch_documento,
                            val_ta_cabecera.ch_cliente,
                            val_ta_cabecera.ch_placa,
                            val_ta_detalle.ch_articulo,
                            val_ta_detalle.nu_importe;";

            $estado = $sqlca->query($sqlselect);
            if ($estado < 0) {
                throw new Exception('Error consulta las notas de despacho de liquidacion X NotaDespacho(' . $ruc . ')');
            }
            $arrayvales = array();
            while ($reg = $sqlca->fetchRow()) {

                $arrayvales[] = $reg;
            }

            foreach ($arrayvales as $fila) {
                $ch_sucursal = trim($fila['ch_sucursal']);
                $dt_fecha = trim($fila['dt_fecha']);
                $ch_documento = trim($fila['ch_documento']);
                $ch_num_importe = $fila['nu_importe'];

                //$sql = "select count(*) as existente from val_ta_complemento_documento where ch_liquidacion='$NUMERO_LIQUIDACION';";

                $sql = "select count(*) as existente from val_ta_complemento_documento 
                       where ch_liquidacion='$NUMERO_LIQUIDACION' and ch_numeval='$ch_documento' 
                       and dt_fecha='$dt_fecha' and ch_fac_tipodocumento='$DOCUMENTO' 
                       and ch_fac_numerodocumento=LPAD(CAST('$num_documneto' AS bpchar), 7, '0')
                       and ch_fac_seriedocumento='$serie' and trim(art_codigo)=trim('$art_codigo');";
                $estado_s = $sqlca->query($sql);

                $regcount = $sqlca->fetchRow();
                if ($regcount['existente'] == 0) {
                    $consulta = "
                    Insert into val_ta_complemento_documento(
                    ch_sucursal,
                    dt_fecha,
                    ch_numeval,
                    ch_fac_tipodocumento,
                    ch_fac_seriedocumento,
                    ch_fac_numerodocumento,
                    ch_liquidacion,
                    art_codigo,
                    accion,
                    ch_placa,
                    fecha_liquidacion,
                    ch_cliente,
                    nu_fac_valortotal,
                    cod_hermandad) 
                    values('$ch_sucursal','$dt_fecha','$ch_documento','$DOCUMENTO','$serie',LPAD(CAST('$num_documneto' AS bpchar), 7, '0'),'$NUMERO_LIQUIDACION','$art_codigo','XNOTADES','-','$fecha_liquidacion','$ruc','$ch_num_importe','$cod_hermandad');";

                    $estado = $sqlca->query($consulta);
                    if ($estado < 0) {
                        throw new Exception('Error Actualizacion NUMLIQUIDACION en los vales (' . $ruc . ')');
                    }
                }
            }

            /* -------------------------- */
            /* $doc = $serie . "-" . $num_documneto;
              $consulta = "UPDATE
              val_ta_cabecera
              SET
              ch_liquidacion='$doc'
              FROM val_ta_detalle
              WHERE val_ta_detalle.dt_fecha BETWEEN to_date('$fecha_inicio', 'MM/DD/YYYY') AND to_date('$fecha_final', 'MM/DD/YYYY')
              AND val_ta_cabecera.ch_cliente='$ruc'
              AND val_ta_cabecera.ch_sucursal=val_ta_detalle.ch_sucursal
              AND val_ta_cabecera.dt_fecha=val_ta_detalle.dt_fecha
              AND val_ta_cabecera.ch_documento=val_ta_detalle.ch_documento
              AND (val_ta_cabecera.ch_liquidacion is null or val_ta_cabecera.ch_liquidacion='')
              AND val_ta_cabecera.ch_documento in $vales and val_ta_detalle.ch_articulo='$articulo' ;";
              $estado = $sqlca->query($consulta);
              if ($estado < 0) {
              throw new Exception('Error actualizar notas despacho(' . $ruc . ')');
              } */
        } catch (Exception $e) {
            throw $e;
        }
    }

    function EleminarCuentas_x_pagar_cliente_NDE($cli_codigo, $ch_tipdocumento, $ch_seriedocumento, $ch_numdocumento, $ch_numdocreferencia) {
        global $sqlca;
        try {

            $delete_detalle = "
                        DELETE FROM ccob_ta_detalle where 
                        trim(cli_codigo)='$cli_codigo' AND  
                        ch_tipdocumento='$ch_tipdocumento' AND 
                        ch_seriedocumento='$ch_seriedocumento' AND 
                        ch_numdocumento='$ch_numdocumento' AND 
                        ch_numdocreferencia='$ch_numdocreferencia' ;
";
//echo $delete_detalle;
            $estado = $sqlca->query($delete_detalle);
            if ($estado < 0) {
                throw new Exception('Error al eliminar cuentas por pagar detalle');
            }

            $delete = "
                        DELETE FROM ccob_ta_cabecera where 
                        trim(cli_codigo)='$cli_codigo' AND  
                        ch_tipdocumento='$ch_tipdocumento' AND 
                        ch_seriedocumento='$ch_seriedocumento' AND 
                        ch_numdocumento='$ch_numdocumento' AND 
                        ch_numdocreferencia='$ch_numdocreferencia' ;
";
            //	echo $delete;
            $estado = $sqlca->query($delete);
            if ($estado < 0) {
                throw new Exception('Error al eliminar cuentas por pagar');
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    function ActualizarValesGeneral($fecha_inicio, $fecha_final, $ruc, $vales) {
        global $sqlca;
        try {

            $consulta = "UPDATE
              val_ta_cabecera
              SET
              ch_liquidacion='LIQ'
              FROM val_ta_detalle
              WHERE val_ta_detalle.dt_fecha BETWEEN to_date('$fecha_inicio', 'YYYY-MM-DD') AND to_date('$fecha_final', 'YYYY-MM-DD')
              AND val_ta_cabecera.ch_cliente='$ruc'
              AND val_ta_cabecera.ch_sucursal=val_ta_detalle.ch_sucursal
              AND val_ta_cabecera.dt_fecha=val_ta_detalle.dt_fecha
              AND val_ta_cabecera.ch_documento=val_ta_detalle.ch_documento
              AND (val_ta_cabecera.ch_liquidacion is null or val_ta_cabecera.ch_liquidacion='')
              AND val_ta_cabecera.ch_documento in $vales ";
            $estado = $sqlca->query($consulta);
            if ($estado < 0) {
                throw new Exception('Error actualizar notas despacho(' . $ruc . ')');
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    	function LiquidacionValesUnicoCliente($fecha_inicio, $fecha_final, $ruc, $vales, $estado_nega) {
		global $sqlca;

		try {
			$consulta = "
					SELECT
                    				cli.cli_anticipo,
                    				cab.ch_cliente,
                    				det.ch_articulo as art_codigo,
                    				cab.ch_sucursal,
                    				sum(round(det.nu_importe,2)) importe,
                    				cli.cli_fpago_credito,
                    				cli.cli_ruc,
					    	cab.dt_fecha,
					    	cab.ch_liquidacion,
					    	sum(det.nu_cantidad) as nu_cantidad,
					    	FIRST(ar.art_descripcion) as desproducto,
					    	cab.ch_placa
					FROM
						val_ta_cabecera cab
						LEFT JOIN int_clientes cli ON (cli.cli_codigo = cab.ch_cliente)
						LEFT JOIN val_ta_detalle det ON (det.ch_sucursal = cab.ch_sucursal AND det.dt_fecha = cab.dt_fecha AND det.ch_documento = cab.ch_documento)
						LEFT JOIN int_articulos ar ON (ar.art_codigo = det.ch_articulo)
					WHERE
						trim(cab.ch_cliente) = trim('$ruc')
						AND cab.dt_fecha BETWEEN to_date('$fecha_inicio', 'YYYY-MM-DD') AND to_date('$fecha_final', 'YYYY-MM-DD')
						AND cab.ch_liquidacion IS NULL
						AND cab.ch_documento IN $vales
					GROUP BY
						cli.cli_anticipo,
						cab.ch_cliente,
						det.ch_articulo,
						cab.ch_sucursal,
						cli.cli_fpago_credito,
						cli.cli_ruc,
						cab.dt_fecha,
						cab.ch_liquidacion,
						cab.ch_placa;
			";

			shell_exec("echo '$consulta' >>log_liquidacion.log");
			$estado = $sqlca->query($consulta);

			if ($estado < 0) {
				throw new Exception('Error consulta de notas de despacho de Cliente (' . $ruc . ')');
			}

			$numrows = $sqlca->numrows();

			if ($numrows == 0) {
				throw new Exception("No se encontro Informacion con los datos de busqueda (" . $fecha_inicio . "< = $fecha_final, $ruc, $vales");
            		}

            		while ($reg = $sqlca->fetchRow()) {
                		if ($estado_nega == "1") {
                    			$registro[] = $reg;
                		} else {
                    			if ($reg['importe'] > 0) {
                        		$registro[] = $reg;
                    		}
                	}
            	}

			return $registro;
		} catch (Exception $e) {
            		throw $e;
		}

	}

	function LiquidacionValesUnicoClienteSoloVales($fecha_inicio, $fecha_final, $ruc, $vales) {
        global $sqlca;
        try {
            $consulta = "
            SELECT
                cli.cli_anticipo,
                cab.ch_cliente,
                det.ch_articulo as art_codigo,
                cab.ch_sucursal,
                 cab.ch_documento,
                sum(round(det.nu_importe,2)) importe,
                cli.cli_fpago_credito,
                cli.cli_ruc,
                cab.dt_fecha,
                cab.ch_liquidacion,
                sum(det.nu_cantidad) as nu_cantidad,
                (select art_descripcion from int_articulos ar where ar.art_codigo = det.ch_articulo limit 1) as desproducto,
                cab.ch_placa
            FROM
                val_ta_cabecera cab
                LEFT JOIN int_clientes cli ON (cli.cli_codigo = cab.ch_cliente)
                LEFT JOIN val_ta_detalle det ON (det.ch_sucursal = cab.ch_sucursal AND det.dt_fecha = cab.dt_fecha AND det.ch_documento = cab.ch_documento)
            WHERE
                trim(cab.ch_cliente) = trim('$ruc')
                AND cab.dt_fecha BETWEEN to_date('$fecha_inicio', 'YYYY-MM-DD') AND to_date('$fecha_final', 'YYYY-MM-DD')
                AND cab.ch_liquidacion is null and cab.ch_documento in $vales
            GROUP BY
                cli.cli_anticipo, cab.ch_cliente, det.ch_articulo, cab.ch_sucursal, cab.ch_documento, cli.cli_fpago_credito, cli.cli_ruc,
                cab.dt_fecha, cab.ch_liquidacion,cab.ch_placa;
            ";

            $estado = $sqlca->query($consulta);
            if ($estado < 0) {
                throw new Exception('Error consulta de notas de despacho de Cliente (' . $ruc . ')');
            }
            $numrows = $sqlca->numrows();
            if ($numrows == 0) {
                throw new Exception("No se encontro Informacion con los datos de busqueda (" . $fecha_inicio . "< = $fecha_final, $ruc, $vales");
            }
            while ($reg = $sqlca->fetchRow()) {
                $registro[] = $reg;
            }

            return $registro;
        } catch (Exception $e) {
            throw $e;
        }
    }

	function MostarValesDeUnCliente($fecha_inicio, $fecha_final, $ruc, $orderchofer) {
        global $sqlca;

        if(!empty($orderchofer))
            $orderby = "pf.nomusu,";

        $consulta = "
        SELECT
	    	cab.ch_cliente,
	    	cab.ch_documento,
	    	det.nu_importe,
	    	cli.cli_anticipo,
	    	cli.cli_fpago_credito,
	    	cli.cli_ruc,
	    	TO_CHAR(cab.dt_fecha, 'DD/MM/YYYY') AS dt_fecha,
	    	cab.ch_sucursal,
	    	cab.ch_liquidacion,
	    	coalesce(cli.cli_mantenimiento, 0) as cli_mantenimiento,
	    	SUM(det.nu_cantidad) as nu_cantidad,
	    	det.ch_articulo as art_codigo,
	    	FIRST(ar.art_descripcion) as desproducto,
	    	cab.ch_placa,
	    	TO_CHAR(cab.fecha_replicacion, 'DD/MM/YYYY hh24:mi:ss') AS fecha_replicacion,
            pf.nomusu AS nochofer
	    FROM
            val_ta_cabecera cab
	    	LEFT JOIN int_clientes cli ON (cli.cli_codigo = cab.ch_cliente)
	    	LEFT JOIN val_ta_detalle det ON (det.ch_sucursal = cab.ch_sucursal AND det.dt_fecha = cab.dt_fecha AND det.ch_documento = cab.ch_documento)
            LEFT JOIN int_articulos ar ON (ar.art_codigo = det.ch_articulo)
            LEFT JOIN pos_fptshe1 pf ON (pf.numtar = cab.ch_tarjeta)
	    WHERE
	    	TRIM(cab.ch_cliente) = TRIM('$ruc')
	    	AND cab.dt_fecha BETWEEN TO_DATE('" . $fecha_inicio . "', 'YYYY-MM-DD') AND TO_DATE('" . $fecha_final . "', 'YYYY-MM-DD')
	    	AND cab.ch_liquidacion IS NULL
	    GROUP BY
	    	1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 12,cab.ch_placa,cab.dt_fecha,cab.fecha_replicacion, pf.nomusu
	    ORDER BY
            $orderby
            cab.dt_fecha,
	    	cab.ch_documento;
        ";
/*
echo "<pre>";
echo $consulta;
echo "</pre>";
echo "<br>";
*/
    	$sqlca->query($consulta);

    	while ($reg = $sqlca->fetchRow()) {
        	$registro[] = $reg;
    	}

        return $registro;

   	}

    function validaDia($dia) {
        global $sqlca;

        $sql = "SELECT CASE WHEN ch_poscd = 'A' THEN ch_posturno ELSE ch_posturno-1 END FROM pos_aprosys where da_fecha = to_date('$dia', 'YYYY-MM-DD')";
        if ($sqlca->query($sql) < 0)
            return false;
        $a = $sqlca->fetchRow();
        $maxturno = $a[0];

        if (trim($maxturno) == "")
            $maxturno = 0;

        $sql = "SELECT 1 FROM pos_consolidacion WHERE dia = to_date('$dia', 'YYYY-MM-DD') AND turno = $maxturno";
        if ($sqlca->query($sql) < 0)
            return false;
        $a = $sqlca->fetchRow();

        if ($a[0] == 1) {
            return 0;
            // no puede cambiar
        } else {
            return 1;
            // si puede cambiar
        }
    }

    function AumentaCorreDoc($TipoDoc, $SerieDoc, $fecha, $Accion) {
        global $sqlca;

        if ($sqlca->functionDB("util_fn_corre_docs_fecha('" . $TipoDoc . "', '" . $SerieDoc . "', '" . $Accion . "'," . $fecha . ")")) {
            return OK;
        }
    }

    function ListadosVarios($Dato) {
        global $sqlca;

        $sqlca->query("BEGIN");
        $sqlca->functionDB("util_fn_combos('" . $Dato . "','ret')");
        $sqlca->query("FETCH ALL IN ret", 'registros');
        $sqlca->query("CLOSE ret");
        $sqlca->query("END");
        $cbArray = array();
        $x = 0;
        while ($reg = $sqlca->fetchRow('registros')) {
            if ($reg[0] != "000000") {
                $cbArray[trim($reg[0])] = trim($reg[0]) . " " . $reg[1];
            }
        }
        ksort($cbArray);

        return $cbArray;
    }

    function GetTaxOptional() {//SOLO PARA LAS EMPRESAS QUE SEAN EXONERADAS
        global $sqlca;
        $query = "SELECT par_valor taxoptional FROM int_parametros WHERE par_nombre = 'taxoptional';";
        $sqlca->query($query);
        $row = $sqlca->fetchRow();
        if(empty($row) || $row["taxoptional"] == "0")
            return false;

        return true;
    }


}

