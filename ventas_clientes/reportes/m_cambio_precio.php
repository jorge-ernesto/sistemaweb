<?php

class cambio_precioModel extends Model {

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

    function ObtenerdatosCliente($codigo) {
        global $sqlca;
        try {
            $sql = "SELECT 
                cli_ruc,cli_codigo,cli_razsocial,cli_fpago_credito,cli_comp_direccion,cli_direccion,cli_anticipo 
                from int_clientes where (trim(cli_codigo)=trim('$codigo') or trim(cli_ruc)=trim('$codigo')) limit 1;";

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

    
    
    function cantidad_producto() {
        global $sqlca;
        
        $consulta = "SELECT count(*) from f_grade group by  product limit 1   ;";

        $sqlca->query($consulta);
        $numrows = $sqlca->numrows();
        while ($reg = $sqlca->fetchRow()) {
            $registro[] = $reg;
        }

        return $registro[0];
    }

	/* CONSULTA CAMBIO DE PRECIOS EN EL GRIFO */
	function registros_cambio_precio($dInicio, $dFin) {
        global $sqlca;

        $sql = "
SELECT
 TO_CHAR(PRICE.systemdate, 'DD/MM/YYYY') AS fe_sistema,
 TO_CHAR(PRICE.created, 'DD/MM/YYYY HH24:MI:SS') AS fe_cambio,
 COMB.ch_codigocombustible AS nu_codigo_item,
 COMB.ch_nombrecombustible AS no_nombre_item,
 PRICE.newprice AS ss_nuevo_precio
FROM
 f_pricechangelog AS PRICE
 JOIN comb_ta_combustibles AS COMB
  ON( SUBSTRING(COMB.ch_codigocombustible, 8, 1) = productid::TEXT)
WHERE
 PRICE.systemdate BETWEEN '" . $dInicio . "' AND '" . $dFin . "'
ORDER BY
PRICE.systemdate DESC,
PRICE.created DESC,
COMB.ch_codigocombustible DESC;
        ";

        // echo "<pre>";
        // echo $sql;
        // echo "</pre>";

        $iStatusSQL = $sqlca->query($sql);
        if ( (int)$iStatusSQL > 0 ) {
            return array(
                'sStatus' => 'success',
                'sMessage' => 'Registros encontrados',
                'arrData' => $sqlca->fetchAll(),
            );
        } else if ( $iStatusSQL == 0 ) {
            return array(
                'sStatus' => 'warning',
                'sMessage' => 'No hay registros',
            );
        }
        return array(
            'sStatus' => 'danger',
            'sMessage' => 'Problemas al obtener registros',
            'sMessageSQL' => $sqlca->get_error(),
            'sql' => $sql,
        );

        /*
        	$consulta = "
				SELECT * FROM(
						SELECT
							TO_CHAR(hora,'YYYY-MM-DD') as hora_text,
                					trapre,
                					f.product,
                					FIRST(hora) as hora_detalle
		            			FROM
							pos_nbastra_historico p  
							INNER JOIN f_grade f ON(p.tragra::INTEGER = f.f_grade_id) 
						WHERE
							TO_CHAR(hora,'YYYY-MM-DD') BETWEEN '$fecha_inicio' AND '$fecha_final'
						GROUP BY
							TO_CHAR(hora,'YYYY-MM-DD'),
							trapre,
							f.product
					        ORDER BY
							hora_text,
							f.product ASC
						) AS p

				ORDER BY
					p.hora_text,
					p.product,
					p.hora_detalle;

				";

		$sqlca->query($consulta);
		$numrows = $sqlca->numrows();

		while ($reg = $sqlca->fetchRow()) {
			$registro[] = $reg;
		}

		return $registro;
        */
	}

    function lista_producto() {
        global $sqlca;

        $consulta = "SELECT f_grade_id,product FROM f_grade ;";




        $sqlca->query($consulta);
        $numrows = $sqlca->numrows();
        while ($reg = $sqlca->fetchRow()) {
            $registro[] = $reg;
        }

        return $registro;
    }

    function MostarClienteVales_rangoFecha($fecha_inicio, $fecha_final, $id_cliente) {
        global $sqlca;

        /*
          SELECT
          vc.ch_placa,vc.dt_fecha,vc.ch_documento,vd.ch_articulo,sum(vd.nu_cantidad) as nu_cantidad,sum(vd.nu_importe) as nu_importe,
          sum(vd.nu_precio_unitario) as nu_precio_unitario
          FROM val_ta_cabecera vc
          INNER JOIN val_ta_detalle vd on  vc.ch_sucursal=vd.ch_sucursal AND  vc.dt_fecha=vd.dt_fecha AND vc.ch_documento=vd.ch_documento
          AND trim(vc.ch_cliente) ='$id_cliente' and vc.dt_fecha BETWEEN '$fecha_inicio' AND   '$fecha_final'
          GROUP BY vc.ch_placa,vc.dt_fecha,vc.ch_documento,vd.ch_articulo ORDER BY vc.dt_fecha,vd.ch_articulo
         * 
         */
        $consulta = "SELECT 
                            vc.ch_placa,vc.dt_fecha,vc.ch_documento,vd.ch_articulo,sum(vd.nu_cantidad) as nu_cantidad,sum(vd.nu_importe) as nu_importe,
                            sum(vd.nu_precio_unitario) as nu_precio_unitario
                            FROM val_ta_cabecera vc 
                            INNER JOIN val_ta_detalle vd on  vc.ch_sucursal=vd.ch_sucursal AND  vc.dt_fecha=vd.dt_fecha AND vc.ch_documento=vd.ch_documento 
                            AND trim(vc.ch_cliente) ='$id_cliente' and vc.dt_fecha BETWEEN '$fecha_inicio' AND   '$fecha_final'
                            GROUP BY vc.ch_placa,vc.dt_fecha,vc.ch_documento,vd.ch_articulo ORDER BY vc.ch_placa,vc.dt_fecha,vd.ch_articulo  
                            
";

//echo $consulta;


        $sqlca->query($consulta);
        $numrows = $sqlca->numrows();
        while ($reg = $sqlca->fetchRow()) {
            $registro[] = $reg;
        }

        return $registro;
    }

    function descripcion($cod_prod) {
        global $sqlca;


        $consulta = "SELECT art_descripcion FROM int_articulos where  art_codigo='$cod_prod'; 
                            
";

//echo $consulta;


        $sqlca->query($consulta);
        $numrows = $sqlca->numrows();
        while ($reg = $sqlca->fetchRow()) {
            $registro[] = $reg;
        }

        return $registro;
    }

    function Mostrarprecio_promedio($fecha, $tran_codigo, $art_codigo) {
        global $sqlca;

        $consulta = "
            SELECT mov_costounitario from inv_movialma 
            WHERE tran_codigo ='$tran_codigo' 
            and trim(art_codigo)='$art_codigo' 
            and to_char(mov_fecha,'YYYY-MM-dd')='$fecha' limit 1;
                            
";

//echo $consulta;


        $sqlca->query($consulta);
        $numrows = $sqlca->numrows();
        while ($reg = $sqlca->fetchRow()) {
            $registro[] = $reg;
        }

        return $registro;
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
							$codigo_cliente,
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



            $in = $sqlca->query($consulta);
            if ($in < 0) {
                throw new Exception("Error al Insertar la Detalle del Documneto X cobrar('22', $SERIE, $NUMERO_DOCUMENTO_ANTI)");
            }
        } catch (Exception $e) {
            throw $e

            ;
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

    function procesoDocumnetoLiquidarUnicoClienteXNormal($DOCUMENTO, $SERIE, $FEC_LIQUIDACION, $datosDetalledocum, $datosCliente, $datos_inicales_documento, $cadena_vales, $f_inicio, $f_fina, $codigo_hermandad) {
        global $sqlca;
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

            foreach ($datosFactura as $cliente => $anticipo) {
                foreach ($anticipo as $nombanticipo => $valor) {
                    for ($i = 0; $i < $valor; $i++) {
                        $num_liquidacion = $num_liquidacion + 1;
                        $NUMERO_DOCUMENTO = $NUMERO_DOCUMENTO + 1;
                        $num_liquidacion_cade = str_pad($num_liquidacion, 10, '0', STR_PAD_LEFT);
                        LiquidacionValesModel::InsertarDocumentocabecera($DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO, $codigo_cliente, $num_liquidacion_cade, $FEC_LIQUIDACION, $ALMACEN, $cli_fpago_credito);
                        LiquidacionValesModel::InsertarDocumentocomplemto($DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO, $codigo_cliente, $FEC_LIQUIDACION, $cli_ruc, $cli_direccion, $cli_comp_direccion, $cli_razsocial);
                        $areglo_datos_documento[] = array("ini" => $inicio, "final" => $final, "num_doc" => $NUMERO_DOCUMENTO,
                            "cli" => $codigo_cliente, "tipo_doc" => $DOCUMENTO, "serie" => $SERIE, "fecha_liquidacion" => $FEC_LIQUIDACION,
                            "num_liquidacion" => $num_liquidacion_cade);

                        $tipo = ($DOCUMENTO == '10') ? 'Factura' : 'Boleta';
                        $datos_intefas_web[] = array("num_doc" => $NUMERO_DOCUMENTO,
                            "cli" => $codigo_cliente, "tipo_doc" => $DOCUMENTO, "serie" => $SERIE,
                            "fecha_liquidacion" => $FEC_LIQUIDACION,
                            "num_liquidacion" => $num_liquidacion_cade, 'tipo' => $tipo, "rz" => $cli_razsocial);

                        $inicio = $final + 1;
                        $final = $final + $maxitem;
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

                            LiquidacionValesModel::InsertarDocumentoDetalle($DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO, $codigo_cliente, $art_codigo, $valores['importe'], $valores['cantidad'], $valores['des']);
                            // $NUMERO_DOCUMENTO = str_pad($NUMERO_DOCUMENTO, 7, '0', STR_PAD_LEFT);
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

    function procesoDocumnetoLiquidarUnicoClienteXPlaca($DOCUMENTO, $SERIE, $FEC_LIQUIDACION, $datosDetalledocum, $datosCliente, $datos_inicales_documento, $cadena_vales, $f_inicio, $f_fina, $codigo_hermandad) {
        global $sqlca;
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
                            LiquidacionValesModel::InsertarDocumentocabecera($DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO, $codigo_cliente, $num_liquidacion_cade, $FEC_LIQUIDACION, $ALMACEN, $cli_fpago_credito);
                            LiquidacionValesModel::InsertarDocumentocomplemto($DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO, $codigo_cliente, $FEC_LIQUIDACION, $cli_ruc, $cli_direccion, $cli_comp_direccion, $cli_razsocial);
                            $areglo_datos_documento[$Kpla][$i] = array("num_doc" => $NUMERO_DOCUMENTO,
                                "cli" => $codigo_cliente, "tipo_doc" => $DOCUMENTO, "serie" => $SERIE, "fecha_liquidacion" => $FEC_LIQUIDACION,
                                "num_liquidacion" => $num_liquidacion_cade, "max_item" => $maxitem, "num_fac" => $i, "ocupada" => 0);


                            $tipo = ($DOCUMENTO == '10') ? 'Factura' : 'Boleta';
                            $datos_intefas_web[] = array("num_doc" => $NUMERO_DOCUMENTO,
                                "cli" => $codigo_cliente, "tipo_doc" => $DOCUMENTO, "serie" => $SERIE,
                                "fecha_liquidacion" => $FEC_LIQUIDACION,
                                "num_liquidacion" => $num_liquidacion_cade, 'tipo' => $tipo, "placa" => $Kpla, "rz" => $cli_razsocial);
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
                                LiquidacionValesModel::InsertarDocumentoDetalle($DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO, $codigo_cliente, $art_codigo, $valores['importe'], $valores['cantidad'], $valores['des']);
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

    function procesoDocumnetoLiquidarUnicoClienteXNotaDespacho($DOCUMENTO, $SERIE, $FEC_LIQUIDACION, $datosDetalledocum, $datosCliente, $datos_inicales_documento, $cadena_vales, $f_inicio, $f_fina, $codigo_hermandad) {
        global $sqlca;
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
                            LiquidacionValesModel::InsertarDocumentocabecera($DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO, $codigo_cliente, $num_liquidacion_cade, $FEC_LIQUIDACION, $ALMACEN, $cli_fpago_credito);
                            LiquidacionValesModel::InsertarDocumentocomplemto($DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO, $codigo_cliente, $FEC_LIQUIDACION, $cli_ruc, $cli_direccion, $cli_comp_direccion, $cli_razsocial);
                            $areglo_datos_documento[$Kchdoc][$i] = array("num_doc" => $NUMERO_DOCUMENTO,
                                "cli" => $codigo_cliente, "tipo_doc" => $DOCUMENTO, "serie" => $SERIE, "fecha_liquidacion" => $FEC_LIQUIDACION,
                                "num_liquidacion" => $num_liquidacion_cade, "max_item" => $maxitem, "num_fac" => $i, "ocupada" => 0);
                            $tipo = ($DOCUMENTO == '10') ? 'Factura' : 'Boleta';
                            $datos_intefas_web[] = array("num_doc" => $NUMERO_DOCUMENTO,
                                "cli" => $codigo_cliente, "tipo_doc" => $DOCUMENTO, "serie" => $SERIE,
                                "fecha_liquidacion" => $FEC_LIQUIDACION,
                                "num_liquidacion" => $num_liquidacion_cade, 'tipo' => $tipo, "ND" => $Kchdoc, "rz" => $cli_razsocial);

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
                                LiquidacionValesModel::InsertarDocumentoDetalle($DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO, $codigo_cliente, $art_codigo, $valores['importe'], $valores['cantidad'], $valores['des']);
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

    function procesoDocumnetoLiquidarUnicoClienteXProducto($DOCUMENTO, $SERIE, $FEC_LIQUIDACION, $datosDetalledocum, $datosCliente, $datos_inicales_documento, $cadena_vales, $f_inicio, $f_fina, $codigo_hermandad) {
        global $sqlca;
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
                        LiquidacionValesModel::InsertarDocumentocabecera($DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO, $codigo_cliente, $num_liquidacion_cade, $FEC_LIQUIDACION, $ALMACEN, $cli_fpago_credito);
                        LiquidacionValesModel::InsertarDocumentocomplemto($DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO, $codigo_cliente, $FEC_LIQUIDACION, $cli_ruc, $cli_direccion, $cli_comp_direccion, $cli_razsocial);
                        $areglo_datos_documento[$num_liquidacion_cade] = array("ini" => $inicio, "final" => $final, "num_doc" => $NUMERO_DOCUMENTO,
                            "cli" => $codigo_cliente, "tipo_doc" => $DOCUMENTO, "serie" => $SERIE, "fecha_liquidacion" => $FEC_LIQUIDACION,
                            "num_liquidacion" => $num_liquidacion_cade);

                        $tipo = ($DOCUMENTO == '10') ? 'Factura' : 'Boleta';
                        $datos_intefas_web[$num_liquidacion_cade] = array("num_doc" => $NUMERO_DOCUMENTO,
                            "cli" => $codigo_cliente, "tipo_doc" => $DOCUMENTO, "serie" => $SERIE,
                            "fecha_liquidacion" => $FEC_LIQUIDACION,
                            "num_liquidacion" => $num_liquidacion_cade, 'tipo' => $tipo, "rz" => $cli_razsocial);

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


                            LiquidacionValesModel::InsertarDocumentoDetalle($DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO, $codigo_cliente, $art_codigo, $valores['importe'], $valores['cantidad'], $valores['des']);
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
            throw $e

            ;
        }
    }

    function ActualizarVales_LiquidacionXcobrar($DOCUMENTO, $fecha_inicio, $fecha_final, $ruc, $vales, $serie, $num_documneto, $articulo, $NUMERO_LIQUIDACION, $fecha_liquidacion, $cod_hermandad) {
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
                            val_ta_detalle.nu_importe ;";

            $estado = $sqlca->query($sqlselect);

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
                        throw new Exception('Error Actualizacion NUMLIQUIDACION en los vales (' . $ruc . ')');
                    }
                }
            }
        } catch (Exception $e) {
            throw $e

            ;
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
                    values('$ch_sucursal','$dt_fecha','$ch_documento','$DOCUMENTO','$serie',LPAD(CAST('$num_documneto' AS bpchar), 7, '0'),'$NUMERO_LIQUIDACION','$articulo','XNORMAL','-','$fecha_liquidacion','$ruc','$ch_num_importe','$cod_hermandad');";


                    $estado = $sqlca->query($consulta);
                    if ($estado < 0) {
                        throw new Exception('Error Actualizacion NUMLIQUIDACION en los vales (' . $ruc . ')');
                    }
                }
            }
        } catch (Exception $e) {
            throw $e

            ;
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
                    cod_hermandad
) 
                    values('$ch_sucursal','$dt_fecha','$ch_documento','$DOCUMENTO','$serie',LPAD(CAST('$num_documneto' AS bpchar), 7, '0'),'$NUMERO_LIQUIDACION','$articulo','XPRODUCTO','-','$fecha_liquidacion','$ruc','$ch_num_importe','$cod_hermandad');";

                    $estado = $sqlca->query($consulta);
                    if ($estado < 0) {
                        throw new Exception('Error Actualizacion NUMLIQUIDACION en los vales (' . $ruc . ')');
                    }
                }
            }
        } catch (Exception $e) {
            throw $e

            ;
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
            throw $e

            ;
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
            throw $e

            ;
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
            throw $e

            ;
        }
    }

    function LiquidacionValesUnicoCliente($fecha_inicio, $fecha_final, $ruc, $vales) {
        global $sqlca;
        try {
            $consulta = "SELECT
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
                    cli.cli_anticipo, cab.ch_cliente, art_codigo, cab.ch_sucursal, cli.cli_fpago_credito, cli.cli_ruc,
                    cab.dt_fecha, cab.ch_liquidacion,cab.ch_placa;
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
                if ($reg['importe'] > 0) {
                    $registro[] = $reg;
                }
            }


            return $registro;
        } catch (Exception $e) {
            throw $e

            ;
        }
    }

    function LiquidacionValesUnicoClienteSoloVales($fecha_inicio, $fecha_final, $ruc, $vales) {
        global $sqlca;
        try {
            $consulta = "SELECT
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
                    cli.cli_anticipo, cab.ch_cliente, art_codigo, cab.ch_sucursal, cab.ch_documento, cli.cli_fpago_credito, cli.cli_ruc,
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
            throw $e

            ;
        }
    }

    function MostarValesDeUnCliente($fecha_inicio, $fecha_final, $ruc) {
        global $sqlca;

        $consulta = "
                    SELECT
                    cab.ch_cliente,
                    cab.ch_documento,
                    det.nu_importe,
                    cli.cli_anticipo,
                    cli.cli_fpago_credito,
                    cli.cli_ruc,
                    cab.dt_fecha,
                    cab.ch_sucursal,
                    cab.ch_liquidacion,
                    coalesce(cli.cli_mantenimiento, 0) as cli_mantenimiento,
                    sum(det.nu_cantidad) as nu_cantidad,
                    det.ch_articulo as art_codigo,
                    (select art_descripcion from int_articulos ar where ar.art_codigo = det.ch_articulo limit 1) as desproducto,
                    cab.ch_placa,
                    cab.fecha_replicacion
                    FROM
                    val_ta_cabecera cab
                    LEFT JOIN int_clientes cli ON (cli.cli_codigo = cab.ch_cliente)
                    LEFT JOIN val_ta_detalle det ON (det.ch_sucursal = cab.ch_sucursal AND det.dt_fecha = cab.dt_fecha AND det.ch_documento = cab.ch_documento)
                    WHERE
                    trim(cab.ch_cliente) = trim('$ruc')
                    AND cab.dt_fecha BETWEEN to_date('" . $fecha_inicio . "', 'YYYY-MM-DD') AND to_date('" . $fecha_final . "', 'YYYY-MM-DD')
                    AND cab.ch_liquidacion is null
                    GROUP BY
                    1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 12,cab.ch_placa,cab.fecha_replicacion
                    ORDER BY
                    cab.ch_documento;
                    ";




        $sqlca->query($consulta);

        while ($reg = $sqlca->fetchRow()) {
            $registro[] = $reg;
        }

        return $registro;
    }

    function validaDia(
    $dia) {
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
            return 0; // no puede cambiar
        } else {
            return 1; // si puede cambiar
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

}

