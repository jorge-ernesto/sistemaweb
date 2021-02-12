<?php

class InterfaceMovModel extends Model {

	function ListadoAlmacenes($codigo) {
		global $sqlca;

		$cond = '';
		if ($codigo != "")
		    $cond = "AND trim(ch_sucursal) = '" . pg_escape_string($codigo) . "'";

		$query = "SELECT ch_almacen FROM inv_ta_almacenes WHERE trim(ch_clase_almacen)='1' " . $cond . " ORDER BY ch_almacen";

		if ($sqlca->query($query) <= 0)
		    return $sqlca->get_error();

		$numrows = $sqlca->numrows();

		$x = 0;

		while ($reg = $sqlca->fetchRow()) {
		    if ($numrows > 1) {
		        if ($x < $numrows - 1) {
		            $conc = ".";
		        } else {
		            $conc = "";
		        }
		    }
		    $listado['' . $codigo . ''] .= $reg[0] . $conc;
		    $x++;
		}
		return $listado;
	}

    function MostarClientes($Fechapos, $fecha) {
        global $sqlca;

		$fecha 			= explode("/", $fecha);
		$fechagloabal 	= $fecha[2] . "-" . $fecha[1] . "-" . $fecha[0];
		$mes_buscar 	= $fecha[2] . "-" . $fecha[1];

		$sql_ruc = "
			(SELECT distinct(trim(cli_codigo)) as ruc FROM fac_ta_factura_cabecera WHERE to_char(dt_fac_fecha,'YYYY-MM') = '$mes_buscar')
			UNION
			(SELECT distinct(ruc) as ruc FROM pos_trans$Fechapos WHERE trim(ruc) != '' AND to_char(dia,'YYYY-MM') = '$mes_buscar')
		";

		if ($sqlca->query($sql_ruc) < 0) {
			echo "error de consulta para los Clientes";
			return $sqlca->get_error();
    	}

       	$array_ruc = array();

        while ($reg = $sqlca->fetchRow())
			$array_ruc[] = $reg;

		$array_vuelta = array();
		$in = 0;

		foreach ($array_ruc as $key => $value) {
			$ruc_find = $value['ruc'];
			$data_rs = InterfaceMovModel::EncontraCliente($ruc_find);
			if (count($data_rs) > 0) {
        		$array_vuelta[$in] = $data_rs;
        		$in++;
    		}
        }
		return $array_vuelta;
	}

	function EncontraCliente($ruc) {
        global $sqlca;

        $sql_siigo = "
			((
				SELECT
					lpad(cl.cli_ruc, 13, '0') as ruc_tmp,
					(SELECT ch_sucursal FROM int_ta_sucursales limit 1) as sucursal,
				    	lpad(cl.cli_razsocial::text, 60, ' ') as cli_razsocial,
					CASE WHEN cli_contacto IS NULL THEN
						lpad('NO HAY CONTACTO',50,' ')
					ELSE
						lpad(cli_contacto::text,50,' ') 
					END as cli_contacto,
                    			CASE WHEN cli_direccion IS NULL THEN
						lpad('NO HAY DIRRECION',100,' ') 
                    			ELSE
                    				lpad(cli_direccion::text,100,' ') 
                    			END as cli_direccion,
				    	CASE WHEN cli_telefono1 IS NULL THEN 
				    		'00000000000'
				    	ELSE  
				    		lpad(cli_telefono1,11,'0') 
				    	END as tele,
				    	'00000000000' as tele2,
				    	'00000000000' as tele3,
				    	'00000000000' as tele4,
				    	'00000000000' as fax,
				    	'000000' as apartadeo_aereo,
				    	CASE WHEN cli_email IS NULL THEN
				    		lpad('',100,' ') 
				    	ELSE
				   		lpad('',100,' ') 
				    	END as email,
				    	'E' as sexo,
				    	'1' as cod_clas_tributario,
				    	'N' as tipo_identificacion,
				    	'00000000000' as cupo_credito,
				    	'00' as lista_precio,
				    	'0001' as cod_vendedor,
				    	'0001' as cod_ciudad,
				    	'00000000000' as por_decuento,
				    	'000' as periodo_pago,
				    	lpad(' ',30,'0') as observacion,
				    	'001' as codigo_pais,
				    	' ' as digito_verificacion,
				    	'001' as calificacion,
				    	'00000' as actividad_economica,
				    	CASE WHEN cli_fpago_credito IN('01','02','03','04','05','08','09','21') THEN
				    		'0003'
				    	ELSE
						'0001'
				    	END as forma_de_pago,
		            		'0000' as cobrador,
				    	CASE WHEN char_length(trim(cli_ruc)) = '11' THEN
				    		'02'
				    	ELSE
						'01'
				    	END as tipo_persona,
				    	'N' as declarante,
				    	'N' as agente_rentendor,
				    	'N' as auto_rentendor,
				    	'N' as beneficiario_retivo,
				    	'N' as agente_retentor,
				    	'A' as estado,
				    	'N' as ente_publico,
				    	'0000000000' as cod_ente_publico,
				    	CASE WHEN char_length(trim(cli_ruc)) = '11' THEN
				    		'S'
				    	ELSE
						'N'
				    	END as razon_social,
				   	lpad('',15,' ') as nombre1,
				    	lpad('',15,' ') as nombre2,
				    	lpad('',15,' ') as apellido1,
				    	lpad('',15,' ') as apellido2,
				    	lpad('',20,' ') as numero_ide_extranjera,
				    	lpad('',3,' ') as ruta,
				    	lpad('',10,' ') as registro,
				    	'00000000' as fecha_vencimiento,
				    	'00000000' as fecha_cumple,
				    	' ' as ts,
				    	lpad('',10,' ') as autorizacion_imprenta,
				    	lpad('',11,' ') as autorizacion_contribuyente,
				    	CASE WHEN char_length(trim(cli_ruc)) = '11' THEN
				    		'06'
				    			WHEN char_length(trim(cli_ruc)) = '8' THEN
				    				'01'
				    	ELSE
						'00'
					END as tc,
					'RUC' as info
				FROM
					int_clientes cl
				WHERE
					trim(cl.cli_ruc) = trim('$ruc')
			) UNION (
				SELECT
					lpad(r.ruc,13,'0') as ruc_tmp,
				    	(SELECT ch_sucursal FROM int_ta_sucursales limit 1) as sucursal,
				    	lpad(r.razsocial::text,60,' ') as cli_razsocial,
				    	lpad('NO HAY CONTACTO',50,' ') as cli_contacto,
				    	lpad('NO HAY DIRRECION',100,' ') as cli_direccion,
				    	'00000000000' as tele,
				    	'00000000000' as tele2,
				    	'00000000000' as tele3,
				    	'00000000000' as tele4,
				    	'00000000000' as fax,
				    	'000000' as apartadeo_aereo,
				    	lpad('',100,' ') as email,
				    	'E' as sexo,
				    	'1' as cod_clas_tributario,
				    	'N' as tipo_identificacion,
				    	'00000000000' as cupo_credito,
				    	'00' as lista_precio,
				    	'0001' as cod_vendedor,
				    	'0001' as cod_ciudad,
				    	'00000000000' as por_decuento,
				    	'000' as periodo_pago,
				    	lpad(' ',30,'0') as observacion,
				    	'001' as codigo_pais,
				    	' ' as digito_verificacion,
				    	'001' as calificacion,
				    	'00000' as actividad_economica,
				    	'0001' as forma_de_pago,
				    	'0000' as cobrador,
				    	'02' as tipo_persona,
				    	'N' as declarante,
				    	'N' as agente_rentendor,
				    	'N' as auto_rentendor,
				    	'N' as beneficiario_retivo,
				    	'N' as agente_retentor,
				    	'A' as estado,
				    	'N' as ente_publico,
				    	'0000000000' as cod_ente_publico,
				    	CASE WHEN char_length(trim(r.ruc)) = '11' THEN
				    		'S'
				    	ELSE
						'N'
				    	end as razon_social,
				    	lpad('',15,' ') as nombre1,
				    	lpad('',15,' ') as nombre2,
				    	lpad('',15,' ') as apellido1,
				    	lpad('',15,' ') as apellido2,
				    	lpad('',20,' ') as numero_ide_extranjera,
				    	lpad('',3,' ') as ruta,
				    	lpad('',10,' ') as registro,
				    	'00000000' as fecha_vencimiento,
				    	'00000000' as fecha_cumple,
				    	' ' as ts,
				    	lpad('',10,' ') as autorizacion_imprenta,
				    	lpad('',11,' ') as autorizacion_contribuyente,
				    	CASE WHEN char_length(trim(ruc)) = '11' THEN
				    		'06'
				    			WHEN char_length(trim(ruc)) = '8' THEN
				    				'01'
				    	ELSE
						'00'
				    	END as tc,
					'CLIENTE' as info
				FROM
					ruc r
				WHERE
					trim(r.ruc) = trim('$ruc')
			))

			LIMIT 1;

		";

    	if ($sqlca->query($sql_siigo) < 0)
			return null;

        $reg = $sqlca->fetchRow();

        return $reg;
	}

	function SetDatamain($tipo) {
		global $sqlca;

        $query="
		SELECT
			id,
			sucursal,
			centrocosto,
			tipo_producto,
			tipo_documento,
			cuenta,
			cuenta_tickes,
			cuenta_descripcion,
			naturaleza,
			codigo_producto,
			tipo_asiento,
			cod_rapido_bus,
			almacen,
			codigo_producto_siigo,
			serietickesboleta,
			serietickesfactura,
			nuvmseriefactura,
			nucpseriefactura,
			nucpseriefacturaglp,
			nu_serie_siigo_consolidado_x_dia,
			nu_ruc_empresa,
			subcentrocosto
		FROM
			configsigo
		WHERE
			tipo_documento = '" . $tipo . "'
		ORDER BY
			tipo_documento DESC,
			codigo_producto,
			naturaleza;
		";

		if ($sqlca->query($query) <= 0)
			return false;

		$array_econfig_siigo = array();

		while ($reg = $sqlca->fetchRow())
			$array_econfig_siigo[trim($reg['codigo_producto'])][] = $reg;

		return $array_econfig_siigo;
	}

  
	function getdata_postran($fecha_pos, $fecha, $iAlmacen) {
	    global $sqlca;

	    //puede ser que se deba modificar este areglo para cada estacion
        $array_multiples_ya_existente = array();
    	//primero buscamos los tickest boleta,factura que esten 
    	//extornada(A) o las de market devluestas(D),para busacr 
    	//su pareja para y que no se muestra la 

        try {
            $trans_obviar		= "(";
    		$sql_extorto_devo	= "
    		SELECT
                ptmp.trans,
                ptmp.caja,
                ptmp.td,
                ptmp.turno,
                ptmp.codigo,
                abs(ptmp.precio) as precio,
                abs(ptmp.igv) as igv,
                abs(ptmp.importe) as importe,
                ptmp.pump,
                ptmp.fpago,
                ptmp.ruc,
                ptmp.fecha
            FROM
            	pos_trans" . $fecha_pos . " ptmp
            WHERE
            	ptmp.tm IN('A')
                AND ptmp.td IN('B','F')
                AND ptmp.tipo = 'C'
			";

            if ($sqlca->query($sql_extorto_devo) < 0) {
				$trans_obviar.= "'-1' )";
			} else {
				$array_extornos_dev = array();

                while ($reg = $sqlca->fetchRow())
					$array_extornos_dev[] = $reg;

				foreach ($array_extornos_dev as $data)
					$trans_obviar.= "'" . InterfaceMovModel::getTrans($data, $fecha_pos) . "',";

				$trans_obviar = substr($trans_obviar, 0, -1);
				$trans_obviar.= ")";
			}

            //ACABA SE ACABA DE GENERAR UN SQL PARA FILTAR LOS TRANS INVALIDOS
			//generamos los tickes que sean vendidos y  que los trans anulados y su pareja no aparescan en la consulta que se realiza
			$data_postrans_generar = array();
            $tipo_producto = "";

            $sql_postra = "
			SELECT
				td AS tipo_comprobante,
				SUBSTR(es, 1, 1)||TO_CHAR(dia,'YYMMdd')||CASE WHEN td = 'F' THEN '2' ELSE '1' END||p.fpago||FIRST(SCCTC.id)||SUBSTR(codigo, LENGTH(codigo), 3) AS num_documento,
				'' AS ruc_dni,
				trim(codigo) AS cod_producto,
				to_char(dia,'YYYYMMdd') AS fecha_doc,
				FIRST(int_a.art_descripcion) AS des_producto,
				replace(to_char(SUM(ROUND(importe, 2)),'0000000000000.99'),'.','') AS TOT,
				replace(to_char(SUM(ROUND((importe - igv), 2)),'0000000000000.99'),'.','') AS IMP,
				(CASE
					WHEN SUM(ROUND(importe, 2)) > (SUM(ROUND((importe - igv), 2)) + SUM(ROUND(igv, 2))) THEN replace(to_char(SUM(ROUND(igv, 2)) + (SUM(ROUND(importe, 2)) - (SUM(ROUND((importe - igv), 2)) + SUM(ROUND(igv, 2)))),'00000000000.99'),'.','')
					WHEN SUM(ROUND(importe, 2)) < (SUM(ROUND((importe - igv), 2)) + SUM(ROUND(igv, 2))) THEN replace(to_char(SUM(ROUND(igv, 2)) - ((SUM(ROUND((importe - igv), 2)) + SUM(ROUND(igv, 2))) - SUM(ROUND(importe, 2))),'00000000000.99'),'.','')
					ELSE replace(to_char(SUM(ROUND(igv, 2)),'00000000000.99'),'.','')
				END) AS IGV,
				(CASE
					WHEN SUM(ROUND(importe, 2)) > (SUM(ROUND((importe - igv), 2)) + SUM(ROUND(igv, 2))) THEN replace(to_char(SUM(ROUND(igv, 2)) + (SUM(ROUND(importe, 2)) - (SUM(ROUND((importe - igv), 2)) + SUM(ROUND(igv, 2)))),'00000000000.99'),'.','')
					WHEN SUM(ROUND(importe, 2)) < (SUM(ROUND((importe - igv), 2)) + SUM(ROUND(igv, 2))) THEN replace(to_char(SUM(ROUND(igv, 2)) - ((SUM(ROUND((importe - igv), 2)) + SUM(ROUND(igv, 2))) - SUM(ROUND(importe, 2))),'00000000000.99'),'.','')
					ELSE replace(to_char(SUM(ROUND(igv, 2)),'00000000000.99'),'.','')
				END) AS cuenta_base_igv_2,
				replace(to_char(SUM(cantidad),'0000000000.99999'),'.','') AS cantidad,
				replace(to_char(SUM(importe) / util_fn_tipo_cambio_dia(dia::DATE),'0000000000000.99'),'.','') AS TOT_E,
				replace(to_char(SUM(importe - igv)/util_fn_tipo_cambio_dia(dia::DATE),'0000000000000.99'),'.','') AS IMP_E,
				replace(to_char(SUM(igv) / util_fn_tipo_cambio_dia(dia::DATE),'0000000000000.99'),'.','') AS IGV_E,
				replace(to_char(SUM(importe - igv) / SUM(cantidad),'0000000000000.99999'),'.','') AS precio_unitario,
				replace(to_char((SUM(importe - igv) / SUM(cantidad)) / util_fn_tipo_cambio_dia(dia::DATE),'0000000000000.99999'),'.','') AS precio_unitario_extranjero,
				replace(to_char(util_fn_tipo_cambio_dia(dia::DATE),'00000000.9999999') ,'.','') AS nu_tipocambio,
				p.tipo AS tipo_producto,
				FIRST(SCCTC.nu_cuenta_contable_ticket) AS nu_cuenta_contable_ticket,
				FIRST(SCCTC.nu_cuenta_flujo_efectivo) AS nu_cuenta_flujo_efectivo,
				FIRST(LINEA.tab_car_05) AS nu_cuenta_sub_centro_costo
			FROM
				pos_trans" . $fecha_pos . " AS p
				LEFT JOIN int_articulos AS int_a ON (p.codigo = int_a.art_codigo)
				LEFT JOIN siigo_cuentas_contables_tarjeta_credito AS SCCTC ON (p.at = SCCTC.nu_tipo_tarjeta_credito)
				LEFT JOIN int_tabla_general AS LINEA ON (LINEA.tab_tabla ='20' AND tab_elemento <> '000000' AND LINEA.tab_elemento = int_a.art_linea)
			WHERE
				p.es = '" . trim($iAlmacen) . "'
				AND p.tipo = 'C'
				AND p.td IN('F','B')
				AND TO_CHAR(p.dia, 'dd/MM/YYYY') BETWEEN '" . $fecha . "' AND '" . $fecha . "'
			GROUP BY
				p.es,
				p.dia,
				p.tipo,
				p.td,
				p.fpago,
				p.at,
				int_a.art_linea,
				p.codigo
			ORDER BY
				p.es,
				p.dia,
				num_documento;
            ";

            if ($sqlca->query($sql_postra) < 0)
				return array(array("Error en la consulta a postrans", $sql_postra));

			while ($reg = $sqlca->fetchRow())
				$data_postrans_generar['K'.$reg['num_documento']][] = $reg;

			return $data_postrans_generar;
		} catch (Exception $e) {
			throw $e;
		}
	}

	function DatosPrimerArchivoGNV($fecha_factura) {
		global $sqlca; //942382

		//puede ser que se deba modificar este areglo para cada estacion
		$data = InterfaceMovModel::SetDatamain("pos_trans");
		$centro_costo = $data['centro_costo'];
		$array_cuenta_contable = $data['array_cuenta_contable'];
		$array_productos = $data['array_productos'];
		$cliente_default = $data['cliente_default'];
		$plan_contable = $data['plan_contable'];
		$extra = $data['extra'];
		$array_multiples_ya_existente = array();

        	try {
	            	$sql = "SELECT c_invoiceheader_id  FROM c_invoiceheader  WHERE  to_char(created,'YYYYMM')='$fecha_factura'  limit 1;  ";

	            	if ($sqlca->query($sql) < 0) {
	        	        shell_exec("echo 'Error al consultar cabecera de GNV ($sql)'.>> error.log");
		                return array();
	            	}
	           	$data_postrans_generar = array();

	            	while ($reg = $sqlca->fetchRow()) {
	                	$data_postrans_generar[] = $reg;
	            	}

            		$count			= 0;
            		$data_ok_archivo	= array();

           		foreach ($data_postrans_generar as $fac_cabecera) {

		        	$c_invoiceheader_id	= $fac_cabecera['c_invoiceheader_id'];
		        	$num_documento		= trim($data['num_documento']);

		        	if (!in_array($num_documento, $array_multiples_ya_existente)) {

					$data_multiple = InterfaceMovModel::getGNVHermanos($c_invoiceheader_id);
					$data_ok_archivo[] = InterfaceMovModel::ConstruirRegistroMultiple_Registro($array_cuenta_contable, $data_multiple, $array_productos, $centro_costo, $cliente_default, $plan_contable);
					array_push($array_multiples_ya_existente, trim($data['num_documento']));

				}

			}

			return $data_ok_archivo;

		} catch (Exception $e) {
			throw $e;
		}

	}

    function getFacturasCompras($fecha_factura){
        global $sqlca;

		try {
			$sql = "
			SELECT
				'P' AS tipo_comprobante,
				cpag.codigo_comprobante,
				cpag.num_documento,
				cpag.ruc,
				inv.art_codigo AS cod_producto,
				cpag.femision AS fecha_doc,
				art.art_descripcion AS des_producto,
				replace(to_char(cpag.TOT + cpag.PER,'0000000000000.99'),'.','') AS TOT,
				replace(to_char(inv.nubixproducto,'0000000000000.99'),'.','') AS IMP,--BASE IMPONIBLE
				replace(to_char(cpag.IGV, '0000000000000.99'),'.','') AS IGV,
				replace(to_char(cpag.PER, '0000000000000.99'),'.','') AS PER,
				replace(to_char(cpag.IGV, '0000000000000.99'),'.','') AS cuenta_base_igv_2,
				CASE WHEN inv.art_codigo = '11620307' THEN
				replace(to_char(ROUND(inv.mov_cantidad/3.785411784,5), '0000000000.99999'),'.','')
				ELSE replace(to_char(inv.mov_cantidad, '0000000000.99999'),'.','') END AS cantidad,
				CASE WHEN inv.art_codigo = '11620307' THEN
				replace(to_char(ROUND(inv.mov_costounitario*3.785411784,5), '0000000000000.99999'),'.','') 
				ELSE replace(to_char(inv.mov_costounitario, '0000000000000.99999'),'.','') END AS precio_unitario,
				replace(to_char(cpag.nutipocambio,'00000000.9999999') ,'.','') AS nu_tipocambio,
				cpag.nuregistrocompra,
				cpag.nutipomoneda,
				cpag.norazsocial,
				inv.nu_cuenta_sub_centro_costo
			FROM
				(SELECT 
					'P' AS tipo_comprobante,
					c.pro_cab_seriedocumento AS codigo_comprobante,
					c.pro_cab_numdocumento AS num_documento,
					to_char(c.pro_cab_fechaemision, 'YYYYMMdd') AS femision,
					c.pro_cab_tipdocumento,
					p.pro_codigo AS ruc_dni,
					p.pro_ruc AS ruc,
					c.pro_cab_impto1 AS IGV,
					c.pro_cab_imptotal AS TOT,
					rubro.ch_descripcion_breve AS rubro,
					c.pro_cab_fecharegistro,
					c.pro_cab_tcambio AS tc,
					c.pro_cab_impafecto AS IMP,
					(CASE WHEN c.regc_sunat_percepcion IS NULL THEN 0.00 ELSE c.regc_sunat_percepcion END) AS PER,
					c.pro_cab_numreg AS nuregistrocompra,
					tc.tca_venta_libre AS nutipocambio,
					(CASE WHEN c.pro_cab_moneda = '01' OR c.pro_cab_moneda = '1' THEN '00' ELSE '01' END) AS nutipomoneda,
					p.pro_razsocial AS norazsocial
				FROM
					cpag_ta_cabecera c
					INNER JOIN cpag_ta_detalle d ON(c.pro_cab_tipdocumento = d.pro_cab_tipdocumento AND c.pro_cab_seriedocumento = d.pro_cab_seriedocumento AND c.pro_cab_numdocumento = d.pro_cab_numdocumento AND c.pro_codigo = d.pro_codigo)
					LEFT JOIN int_proveedores p ON(c.pro_codigo = p.pro_codigo)
					LEFT JOIN inv_ta_almacenes a ON(c.pro_cab_almacen = a.ch_almacen)
					LEFT JOIN cpag_ta_rubros rubro ON(rubro.ch_codigo_rubro = c.pro_cab_rubrodoc)
					INNER JOIN int_tipo_cambio tc ON(tc.tca_fecha = c.pro_cab_fechaemision)
				WHERE
					to_char(c.pro_cab_fechaemision,'DD/MM/YYYY') = '" . $fecha_factura . "'
				) AS cpag INNER JOIN
				(SELECT   
					inv.mov_numero,
					inv.art_codigo,
					inv.mov_docurefe,
					inv.mov_tipdocuref,
					inv.mov_entidad,
					inv.mov_cantidad ,
					inv.mov_costounitario,
					ROUND(inv.mov_costototal, 2) AS nubixproducto,
					LINEA.tab_car_05 AS nu_cuenta_sub_centro_costo
				FROM
					inv_movialma AS inv
					LEFT JOIN int_articulos AS int_a ON (inv.art_codigo = int_a.art_codigo)
					LEFT JOIN int_tabla_general AS LINEA ON (LINEA.tab_tabla ='20' AND tab_elemento <> '000000' AND LINEA.tab_elemento = int_a.art_linea)
				WHERE
					to_char(mov_fecha,'DD/MM/YYYY') = '" . $fecha_factura . "'
					AND tran_codigo IN('21')
				) AS inv ON (inv.mov_entidad = cpag.ruc_dni AND (cpag.codigo_comprobante || cpag.num_documento) = inv.mov_docurefe)
				INNER JOIN int_articulos art ON (inv.art_codigo = art.art_codigo);
            ";

			if ($sqlca->query($sql) < 0) {
                $d_error['errorf'] = TRUE;
                return $d_error;
			}

			$data_postrans_generar = array();

			while ($reg = $sqlca->fetchRow())
                $data_postrans_generar['K'.$reg['num_documento']][] = $reg;

			return $data_postrans_generar;

		} catch (Exception $e) {
			throw $e;
		}

	}

   	function getFacturasManuales($fecha_factura) {
        global $sqlca; //942382

		//puede ser que se deba modificar este areglo para cada estacion
		$configuracion_cuentas			= InterfaceMovModel::SetDatamain();
		$centro_costo 					= $data['centro_costo'];
		$array_cuenta_contable 			= $data['array_cuenta_contable'];
		$array_productos 				= $data['array_productos'];
		$cliente_default 				= $data['cliente_default'];
		$plan_contable 					= $data['plan_contable'];
		$extra 							= $data['extra'];
		$array_multiples_ya_existente 	= array();
		
		/*
		(CASE WHEN PT.usr IS NULL OR PT.usr = '' THEN
			RFC.nu_numerodoc
		ELSE
			''--SUBSTR(TRIM(PT.usr), 6)
		END) AS nu_numerodoc
		*/

		try {
    		$sql = "
			SELECT
				(CASE
					WHEN c.ch_fac_tipodocumento = '10' THEN 'F'
					WHEN c.ch_fac_tipodocumento = '35' THEN 'B'
					WHEN c.ch_fac_tipodocumento = '20' THEN 'NC'
                ELSE
					c.ch_fac_tipodocumento
	        	END) as tipo_comprobante,
	       		TRIM(c.ch_fac_seriedocumento) AS codigo_comprobante,
	        	lpad(c.ch_fac_numerodocumento::text,11,'0') as num_documento,
	        	cli.cli_codigo as ruc_dni,
	        	(select ch_sucursal from int_ta_sucursales limit 1) as sucursal,
	        	trim(int_a.art_codigo)  AS cod_producto,
	        	to_char(c.dt_fac_fecha,'YYYYMMdd') as fecha_doc,
	        	int_a.art_descripcion  as des_producto,
				replace(to_char((SELECT
									SUM(round(i.nu_fac_importeneto,2)) + round(c.nu_fac_impuesto1  ,2)
								FROM
									fac_ta_factura_detalle i 
								WHERE
									i.ch_fac_tipodocumento 			= d.ch_fac_tipodocumento
									AND i.ch_fac_seriedocumento 	= d.ch_fac_seriedocumento 
									AND i.ch_fac_numerodocumento 	= d.ch_fac_numerodocumento
								LIMIT 1),
				'0000000000000.99'),'.','') as TOT,
	        	replace(to_char(round(d.nu_fac_valortotal/1.18,2),'0000000000000.99'),'.','')  as IMP,
	        	replace(to_char(round( c.nu_fac_impuesto1  ,2),'0000000000000.99'),'.','')  as IGV,
	        	replace(to_char(round(c.nu_fac_impuesto1 ,2),'00000000000.99'),'.','')  as cuenta_base_igv_2,
	        	replace(to_char(round(d.nu_fac_cantidad,4),'0000000000.99999'),'.','') as cantidad,
	        	replace(to_char((round(c.nu_fac_valortotal,2))/util_fn_tipo_cambio_dia(c.dt_fac_fecha::date),'0000000000000.99'),'.','')  as TOT_E,
	        	replace(to_char((round(d.nu_fac_importeneto,2))/util_fn_tipo_cambio_dia(c.dt_fac_fecha::date),'0000000000000.99'),'.','')  as IMP_E,
	        	replace(to_char((round(c.nu_fac_impuesto1,2))/util_fn_tipo_cambio_dia(c.dt_fac_fecha::date),'0000000000000.99'),'.','')  as IGV_E,
	        	replace(to_char(d.nu_fac_precio,'0000000000000.99999'),'.','') as precio_unitario,
	        	replace(to_char((d.nu_fac_precio)/util_fn_tipo_cambio_dia(c.dt_fac_fecha::date),'0000000000000.99999'),'.','') as precio_unitario_extranjero,
	        	replace(to_char(util_fn_tipo_cambio_dia(c.dt_fac_fecha::date),'00000000.9999999') ,'.','')  as nu_tipocambio,
				cli.cli_razsocial AS norazsocial,
				c.ch_fac_credito AS notipocredito,
				'' AS nu_cuenta_contable_ticket,
				'0000' AS nu_cuenta_flujo_efectivo,
				LINEA.tab_car_05 AS nu_cuenta_sub_centro_costo,
				(CASE
					WHEN RFC.nu_tipodoc = '10' THEN 'F'
					WHEN RFC.nu_tipodoc = '35' THEN 'B'
				ELSE
					''
	        	END) as ref_tipo_comprobante,
	        	RFC.nu_seriedoc,
	        	RFC.nu_numerodoc
    		FROM
				fac_ta_factura_cabecera AS c
        		INNER JOIN fac_ta_factura_detalle AS d ON (c.ch_fac_tipodocumento = d.ch_fac_tipodocumento AND c.ch_fac_seriedocumento = d.ch_fac_seriedocumento AND c.ch_fac_numerodocumento = d.ch_fac_numerodocumento AND c.cli_codigo = d.cli_codigo)
        		LEFT JOIN fac_ta_factura_complemento AS com ON(c.ch_fac_tipodocumento = com.ch_fac_tipodocumento AND c.ch_fac_seriedocumento = com.ch_fac_seriedocumento AND c.ch_fac_numerodocumento = com.ch_fac_numerodocumento AND c.cli_codigo = com.cli_codigo)
        		LEFT JOIN (
				SELECT
					ch_fac_tipodocumento AS nu_tipodoc,
					ch_fac_seriedocumento AS nu_seriedoc,
					ch_fac_numerodocumento AS nu_numerodoc,
					dt_fac_fecha AS fe_emision
				FROM
					fac_ta_factura_cabecera
				) AS RFC ON (
					RFC.nu_numerodoc = (string_to_array(com.ch_fac_observacion2, '*'))[1]
					AND RFC.nu_seriedoc = (string_to_array(com.ch_fac_observacion2, '*'))[2]
					AND RFC.nu_tipodoc = (string_to_array(com.ch_fac_observacion2, '*'))[3]
				)
        		LEFT JOIN int_articulos AS int_a ON (d.art_codigo = int_a.art_codigo)
        		LEFT JOIN int_clientes AS cli ON (cli.cli_codigo = c.cli_codigo)
        		LEFT JOIN int_tabla_general AS LINEA ON (LINEA.tab_tabla ='20' AND LINEA.tab_elemento <> '000000' AND LINEA.tab_elemento = int_a.art_linea)
			WHERE
				to_char(c.dt_fac_fecha,'DD/MM/YYYY') = '$fecha_factura'
				AND trim(c.ch_fac_tipodocumento) IN('10','35', '20')
				AND trim(int_a.art_codigo) IN('11620301','11620302','11620303','11620304','11620305','11620307')
			ORDER BY
				num_documento ASC;
			";

			if ($sqlca->query($sql) < 0) {
                		$d_error['errorf'] = TRUE;
                		return $d_error;
			}

			$data_postrans_generar = array();

			while ($reg = $sqlca->fetchRow()) {
				$data_postrans_generar['K'.$reg['num_documento']][] = $reg;
			}

			return $data_postrans_generar;

		} catch (Exception $e) {
			throw $e;
		}

	}

	function autorelleno($value, $cantidad, $relleno, $orden){
		
		$value	= trim($value);
		$lon	= strlen($value);
		$s		= "";
		
		if($orden == 'LEFT'){
			$s = STR_PAD_LEFT;
		}else if($orden == 'RI'){
			$s = STR_PAD_RIGHT;
		}
		
		if($lon > $cantidad){
			return substr($value, 0, $cantidad);
		}else{
			return str_pad($value, $cantidad, $relleno, $s);
		}
		
	}

    function procesariInformacion($tickes, $accion = "", $c_v, $year, $month) {

		$secuencia							= 1;
		$count 								= 0;
		$data_postrans_siigo 				= array();
		$data_postrans_siigo_2 				= array();
		$data_postrans_siigo_inventario 	= array();
		$data_postrans_siigo_inventario_2 	= array();
		$inv 								= 0;
		$configuracion_cuentas				= InterfaceMovModel::SetDatamain($c_v);
		$data_invoice_trans					= array();
		$numero_documento					= 1;

		foreach ($tickes as $keytickes => $valuetickes) {
			if($c_v == "CP"){
				InterfaceMovModel::procesarDataMultipleCompra($valuetickes, $configuracion_cuentas, $data_invoice_trans, $accion, $numero_documento, $year, $month);
				//InterfaceMovModel::procesarDataMultipleCompra($valuetickes, $configuracion_cuentas, $data_invoice_trans, $accion, $numero_documento, $year, $month);
				$numero_documento++;
			}else{
				//InterfaceMovModel::procesarDataMultipleVenta($valuetickes, $configuracion_cuentas, $data_invoice_trans, $accion);
				InterfaceMovModel::procesarDataMultipleVenta($valuetickes, $configuracion_cuentas, $data_invoice_trans, $accion);
			}
		}
		return ($data_invoice_trans);
	}
   
	function procesarDataMultipleCompra($tickes, $configuracion_cuentas, &$data_invoice_trans, $accion, $numero_documento, $year, $month){//INICIO COMPRA
   		
		$only_detail				= false;
   		$correlativo				= 1;
		$valor_movi_extranjero		= 0;
		
		foreach ($tickes as $key => $valuetickes) {

   			$cod_producto	= trim($valuetickes['cod_producto']);
			$cuentacont		= $configuracion_cuentas[$cod_producto];
			
			foreach($cuentacont as $regcuenta){
				
				if($only_detail && ($regcuenta['cod_rapido_bus'] == 'TOT' || $regcuenta['cod_rapido_bus'] == 'IGV' || $regcuenta['cod_rapido_bus'] == 'PER' )){
					continue;
				}

				$cuenta_descrip			= trim(strtolower($regcuenta['cod_rapido_bus']));
				$valor_movi				= $valuetickes[$cuenta_descrip];
				$noproducto 			= $valuetickes['norazsocial'];

				if($valuetickes['nutipomoneda'] == '01')
					$valor_movi_extranjero	= $valuetickes[$cuenta_descrip];

				if($regcuenta['cod_rapido_bus'] == 'MER' || $regcuenta['cod_rapido_bus'] == 'COS'){
					$valor_movi				= $valuetickes['imp'];
					$noproducto 			= $valuetickes['des_producto'];
					if($valuetickes['nutipomoneda'] == '01')
						$valor_movi_extranjero	= $valuetickes['imp'];
				}

				if($regcuenta['cod_rapido_bus'] == 'IMP')
					$noproducto 			= $valuetickes['des_producto'];

				//VALIDACION PARA X TIPO DE PRODUCTO LIQUIDOS Y GLP, PARA COLOCAR SERIE DE LAS COMPRAS

				if($cod_producto != '11620307')
					$nucpseriefactura = $regcuenta['nucpseriefactura'];
				else
					$nucpseriefactura = $regcuenta['nucpseriefacturaglp'];

				$tmp				= array();
				$tmp[]				= $siigo_tipo_documento = InterfaceMovModel::validartipodocumento($valuetickes['tipo_comprobante'], 'CP');
				$codigo_comprobante	= trim($valuetickes['codigo_comprobante']);
				$nudocumento		= $year.$month."".InterfaceMovModel::autorelleno($valuetickes['nuregistrocompra'], 4,'0','LEFT');

				$tmp[] 		= $siigo_serie_documento 	= InterfaceMovModel::autorelleno($nucpseriefactura, 3, '0', 'LEFT');
				$tmp[]		= $siigo_numero_documento	= InterfaceMovModel::autorelleno($nudocumento, 11, '0', 'LEFT');
				$tmp[]		= $siigo_correlativo		= InterfaceMovModel::autorelleno($correlativo,5,'0','LEFT');
				$tmp[]		= $siigo_ruc_dni			= InterfaceMovModel::autorelleno($valuetickes['ruc'],13,'0','LEFT');
				$tmp[]		= $siigo_sucursa			= InterfaceMovModel::autorelleno(000,3,'0','LEFT');//Nelly de Picorp indico que este campo va vacio
				$tmp[]		= $siigo_cuentacontable		= InterfaceMovModel::autorelleno($regcuenta['cuenta'],10,'0','LEFT');
				$tmp[]		= $siigo_cod_producto		= InterfaceMovModel::autorelleno($regcuenta['codigo_producto_siigo'],13,'0','LEFT');
				$tmp[]		= $siigo_fecha_documento	= InterfaceMovModel::autorelleno($valuetickes['fecha_doc'],8,'0','LEFT');
				$tmp[]		= $siigo_centro_costo		= InterfaceMovModel::autorelleno($regcuenta['centrocosto'],4,'0','LEFT');
				//$tmp[]		= $siigo_sub_centro_costo	= "000";
				$tmp[] 		= $siigo_sub_centro_costo	= InterfaceMovModel::autorelleno($valuetickes['nu_cuenta_sub_centro_costo'],3,'0','LEFT');
				$tmp[]		= $siigo_nombre_producto	= InterfaceMovModel::autorelleno($noproducto,50,' ','RI');
				$tmp[]		= $siigo_naturaleza			= InterfaceMovModel::autorelleno($regcuenta['naturaleza'],1,'0','LEFT');
				$tmp[]		= $siigo_valor_movimiento	= InterfaceMovModel::autorelleno($valor_movi,15,'0','LEFT');//condicion
				$tmp[]		= $siigo_retencion			= "000000000000000";
				$tmp[]		= $siigo_vendedor			= "0001";
				$tmp[]		= $siigo_ciudad				= "0001";
				$tmp[]		= $siigo_zona				= "000";
				$tmp[]		= $siigo_almacen			= InterfaceMovModel::autorelleno($regcuenta['almacen'],4,'0','LEFT');
				$tmp[]		= $siigo_ubicacion			= "000";		
				$tmp[]		= $siigo_cantidad			= InterfaceMovModel::autorelleno($valuetickes['cantidad'],15,'0','LEFT');

				if($regcuenta['cod_rapido_bus'] == 'TOT' || $regcuenta['cod_rapido_bus'] == 'IGV'){//solo el 12 lleva el valor de cruze
					$tmp[]	= $siigo_documento_cruce			= InterfaceMovModel::autorelleno($siigo_tipo_documento,1,'0','LEFT');
					//$tmp[] 	= $siigo_serie_documento 			= InterfaceMovModel::autorelleno($nucpseriefactura, 3, '0', 'LEFT');
					$tmp[]	= $siigo_documento_cruce_serie	= InterfaceMovModel::autorelleno($codigo_comprobante,3,'','LEFT');
					$tmp[]	= $siigo_documento_cruce_numero		= InterfaceMovModel::autorelleno($valuetickes['num_documento'],11,'0','LEFT');
					$tmp[]	= $siigo_documento_cruce_secuencia	= InterfaceMovModel::autorelleno("001",3,'0','LEFT');
				}else{
					$tmp[]	= $siigo_documento_cruce			= InterfaceMovModel::autorelleno("",1,' ','LEFT');
					$tmp[]	= $siigo_documento_cruce_serie		= InterfaceMovModel::autorelleno("",3,' ','LEFT');
					$tmp[]	= $siigo_documento_cruce_numero		= InterfaceMovModel::autorelleno("0",11,'0','LEFT');
					$tmp[]	= $siigo_documento_cruce_secuencia	= InterfaceMovModel::autorelleno("0",3,'0','LEFT');
				}
				
				$tmp[] = $siigo_documento_cruce_fecha=InterfaceMovModel::autorelleno($valuetickes['fecha_doc'],8,'0','LEFT');

				if($regcuenta['cod_rapido_bus'] == 'TOT' ){
					$tmp[] = $siigo_forma_pago = InterfaceMovModel::autorelleno("0001",4,'0','LEFT');
				}else{
					$tmp[] = $siigo_forma_pago = InterfaceMovModel::autorelleno("0",4,'0','LEFT');
				}

				$tmp[]=$siigo_banco							=InterfaceMovModel::autorelleno("0",2,'0','LEFT');
				$tmp[]=$siigo_pedido_documento				=InterfaceMovModel::autorelleno("",1,' ','LEFT');
				$tmp[]=$siigo_pedido_codigo_compra			=InterfaceMovModel::autorelleno("0",3,'0','LEFT');
				$tmp[]=$siigo_pedido_numero					=InterfaceMovModel::autorelleno("0",11,'0','LEFT');
				$tmp[]=$siigo_pedido_secuencia				=InterfaceMovModel::autorelleno("0",3,'0','LEFT');
				$tmp[]=$siigo_codigo_moneda					=InterfaceMovModel::autorelleno($valuetickes['nutipomoneda'], 2,'0','LEFT');
				$tmp[]=$siigo_tipo_cambio					=InterfaceMovModel::autorelleno($valuetickes['nu_tipocambio'],15,'0','LEFT');
				$tmp[]=$siigo_valor_extranjero				=InterfaceMovModel::autorelleno($valor_movi_extranjero,15,'0','LEFT');
				$tmp[]=$siigo_concepto_nomina				=InterfaceMovModel::autorelleno("0",3,'0','LEFT');
				$tmp[]=$siigo_cantidad_pago					=InterfaceMovModel::autorelleno("0",11,'0','LEFT');
				$tmp[]=$siigo_porcentaje_desc_mov			=InterfaceMovModel::autorelleno("0",4,'0','LEFT');
				$tmp[]=$siigo_valor_desc_mov				=InterfaceMovModel::autorelleno("0",13,'0','LEFT');
				$tmp[]=$siigo_porcentaje_cargo_mov			=InterfaceMovModel::autorelleno("0",4,'0','LEFT');
				$tmp[]=$siigo_valor_cargo_mov				=InterfaceMovModel::autorelleno("0",13,'0','LEFT');
				$tmp[]=$siigo_porcentaje_igv				=InterfaceMovModel::autorelleno("0",4,'0','LEFT');
				$tmp[]=$siigo_valor_igv						=InterfaceMovModel::autorelleno("0",13,'0','LEFT');
				$tmp[]=$siigo_indicardor_nomina				=InterfaceMovModel::autorelleno("",1,' ','LEFT');
				$tmp[]=$siigo_numero_pago					=InterfaceMovModel::autorelleno("0",1,'0','LEFT');
				$tmp[]=$siigo_numero_checke					=InterfaceMovModel::autorelleno("0",11,'0','LEFT');
				$tmp[]=$siigo_numero_tipo_movi				=InterfaceMovModel::autorelleno("S",1,' ','LEFT');
				$tmp[]=$siigo_nombre_computador				=InterfaceMovModel::autorelleno("",4,' ','LEFT');
				$tmp[]=$siigo_estado_comprobante			=InterfaceMovModel::autorelleno("",1,' ','LEFT');
				$tmp[]=$siigo_ecuador1						=InterfaceMovModel::autorelleno("0",2,'0','LEFT');
				$tmp[]=$siigo_ecuador_credito				=InterfaceMovModel::autorelleno("0",2,'0','LEFT');
				$tmp[]=$siigo_numero_comprobante_prov		=InterfaceMovModel::autorelleno($codigo_comprobante,4,'0','LEFT');//""
				$tmp[]=$siigo_numero_doc_comprobante_prov	=InterfaceMovModel::autorelleno($valuetickes['num_documento'],11,'0','LEFT');
				$tmp[]=$siigo_prefijo_doc_prov				=InterfaceMovModel::autorelleno("",10,' ','LEFT');
				$tmp[]=$siigo_decha_doc_prov				=InterfaceMovModel::autorelleno($valuetickes['fecha_doc'],8,'0','LEFT');
				$tmp[]=$siigo_unitario_soles				=InterfaceMovModel::autorelleno("0",18,'0','LEFT');
				$tmp[]=$siigo_unitarios_extranjero			=InterfaceMovModel::autorelleno("0",18,'0','LEFT');
				$tmp[]=$siigo_indicardo_mov					=InterfaceMovModel::autorelleno("",1,' ','LEFT');
				$tmp[]=$siigo_depreciacion_activo			=InterfaceMovModel::autorelleno("0",3,'0','LEFT');
				$tmp[]=$siigo_secuencia_trans				=InterfaceMovModel::autorelleno("0",2,'0','LEFT');
				$tmp[]=$siigo_autorizacion_imprenta			=InterfaceMovModel::autorelleno("0",10,'0','LEFT');
				$tmp[]=$siigo_secuencia_marca				=InterfaceMovModel::autorelleno("",1,' ','LEFT');
				$tmp[]=$siigo_numero_caja					=InterfaceMovModel::autorelleno("0",3,'0','LEFT');
				$tmp[]=$siigo_numero_punto_obt				=InterfaceMovModel::autorelleno("0",14,'0','LEFT');
				$tmp[]=$siigo_cantidad_dos					=InterfaceMovModel::autorelleno("0",15,'0','LEFT');
				$tmp[]=$siigo_cantidad_alt_dos				=InterfaceMovModel::autorelleno("0",15,'0','LEFT');
				$tmp[]=$siigo_metodo_depreciacion			=InterfaceMovModel::autorelleno("",1,' ','LEFT');
				$tmp[]=$siigo_cant_factor_coversion			=InterfaceMovModel::autorelleno("0",18,'0','LEFT');
				$tmp[]=$siigo_operador_factor_coversion		=InterfaceMovModel::autorelleno("0",1,'0','LEFT');
				$tmp[]=$siigo_factor_coversion				=InterfaceMovModel::autorelleno("0",10,'0','LEFT');
				$tmp[]=$siigo_fecha_caducidad				=InterfaceMovModel::autorelleno("0",8,'0','LEFT');
				$tmp[]=$siigo_codigo_ice					=InterfaceMovModel::autorelleno("0",2,'0','LEFT');
				$tmp[]=$siigo_codigo_retencion				=InterfaceMovModel::autorelleno("",6,' ','LEFT');
				$tmp[]=$siigo_clase_retencion				=InterfaceMovModel::autorelleno("0",4,'0','LEFT');
				$tmp[]=$siigo_extra							="                                            ";
				$tmp[]=$siigocomprobante_fiscal				=InterfaceMovModel::autorelleno("0",38,'0','LEFT');
				$tmp[]=$siigo_tipo_letra					=InterfaceMovModel::autorelleno("",1,' ','LEFT');
				$tmp[]=$siigo_estado_letra					=InterfaceMovModel::autorelleno("",1,' ','LEFT');
				$tmp[]=$siigo_valor_movimiento_f			=InterfaceMovModel::autorelleno("0",18,'0','LEFT');
				$tmp[]=$siigo_valor_movimiento_fe			=InterfaceMovModel::autorelleno("0",18,'0','LEFT');
				$tmp[]=$siigo_codigo_mp						=InterfaceMovModel::autorelleno("0",3,'0','LEFT');
				$tmp[]=$siigo_jamas_usado					="0000000000000000000                    000000000000000000000000000000000000000000000000";
				
				//echo implode($tmp)."\n";

				$correlativo++;
				$data_invoice_trans[]=$tmp;

			}

			$only_detail=TRUE;
		}

   	}//FIN DE COMPRA

	function procesarDataMultipleVenta($tickes, $configuracion_cuentas, &$data_invoice_trans, $accion){//INICIO VENTA
   		$only_detail = false;
        $correlativo = 1;
		$nu_documento_key = '';

		foreach ($tickes as $key => $valuetickes) {
   			$cod_producto	= trim($valuetickes['cod_producto']);
			$cuentacont		= $configuracion_cuentas[$cod_producto];
			
			foreach($cuentacont as $regcuenta){
				if($only_detail && ($regcuenta['cod_rapido_bus'] == 'TOT' || $regcuenta['cod_rapido_bus'] == 'IGV' )){
					continue;
				}
				
				$cuenta_descrip			= trim(strtolower($regcuenta['cod_rapido_bus']));
				$valor_movi				= $valuetickes[$cuenta_descrip];
				$valor_movi_extranjero	= $valuetickes[$cuenta_descrip."_e"];

				if($accion == 'manual')
					$noproducto 			= $valuetickes['norazsocial'];
				else
					$noproducto 			= $valuetickes['des_producto'];

				if($regcuenta['cod_rapido_bus'] == 'MER'){
					$valor_movi 			= 0.00;
					$valor_movi_extranjero 	= 0.00;
					$noproducto 			= $valuetickes['des_producto'];
				}

				if($regcuenta['cod_rapido_bus'] == 'IMP')
					$noproducto 			= $valuetickes['des_producto'];

				$tmp				= array();
				$tmp[]				= $siigo_tipo_documento = InterfaceMovModel::validartipodocumento($valuetickes['tipo_comprobante'], 'VT');
				$codigo_comprobante	= trim($valuetickes['codigo_comprobante']);

				if($siigo_tipo_documento == 'F'){
					if($accion == 'postrans'){
						$tmp[] = $siigo_serie_documento=InterfaceMovModel::autorelleno($regcuenta['serietickesfactura'],3,'0','LEFT');
						//$tmp[] = $siigo_serie_documento=InterfaceMovModel::autorelleno($regcuenta['nu_serie_siigo_consolidado_x_dia'],3,'0','LEFT');
					}else if($accion == 'manual'){
						$tmp[] = $siigo_serie_documento=InterfaceMovModel::autorelleno($regcuenta['nuvmseriefactura'],3,'0','LEFT');
					}
				}else if($siigo_tipo_documento == 'Q'){
					if($accion == 'postrans'){
						$tmp[] = $siigo_serie_documento=InterfaceMovModel::autorelleno($regcuenta['serietickesboleta'],3,'0','LEFT');
						//$tmp[] = $siigo_serie_documento=InterfaceMovModel::autorelleno($regcuenta['nu_serie_siigo_consolidado_x_dia'],3,'0','LEFT');
					}else if($accion == 'manual'){
						$tmp[] = $siigo_serie_documento=InterfaceMovModel::autorelleno($codigo_comprobante,3,'0','LEFT');
					}
				}else{
					$tmp[] = $siigo_serie_documento=InterfaceMovModel::autorelleno('000',3,'0','LEFT');
				}
		
				$tmp[]		= $siigo_numero_documento	= InterfaceMovModel::autorelleno($valuetickes['num_documento'],11,'0','LEFT');
				$tmp[]		= $siigo_correlativo		= InterfaceMovModel::autorelleno($correlativo,5,'0','LEFT');

				//$cliente	= ($siigo_tipo_documento == 'Q' ) ? '0099999999999':InterfaceMovModel::autorelleno($valuetickes['ruc_dni'],13,'0','LEFT');
				//$cliente	= ($siigo_tipo_documento == 'Q' ) ? '0099999999999':InterfaceMovModel::autorelleno($regcuenta['nu_ruc_empresa'],13,'0','LEFT');

				if($siigo_tipo_documento == 'Q') {
					$cliente = '0099999999999';
				} else {
					if($accion == 'postrans')
						$cliente	= InterfaceMovModel::autorelleno($regcuenta['nu_ruc_empresa'],13,'0','LEFT');
					else if($accion == 'manual')
						$cliente	= InterfaceMovModel::autorelleno($valuetickes['ruc_dni'],13,'0','LEFT');
				}

				$tmp[]		= $siigo_ruc_dni		= $cliente;
				$tmp[]		= $siigo_sucursal		= InterfaceMovModel::autorelleno($regcuenta['centrocosto'],3,'0','LEFT');		

				if($accion == 'postrans'){
					//$tmp[] = $siigo_cuentacontable=InterfaceMovModel::autorelleno($regcuenta['cuenta_tickes'],10,'0','LEFT');
					if($regcuenta["naturaleza"] == 'C')
						$tmp[] = $siigo_cuentacontable=InterfaceMovModel::autorelleno($regcuenta['cuenta_tickes'],10,'0','LEFT');
					else
						$tmp[] = $siigo_cuentacontable=InterfaceMovModel::autorelleno($valuetickes['nu_cuenta_contable_ticket'],10,'0','LEFT');
				}else{
					$tmp[] = $siigo_cuentacontable=InterfaceMovModel::autorelleno($regcuenta['cuenta'],10,'0','LEFT');
				}

				$tmp[] = $siigo_cod_producto		= InterfaceMovModel::autorelleno($regcuenta['codigo_producto_siigo'],13,'0','LEFT');
				$tmp[] = $siigo_fecha_documento		= InterfaceMovModel::autorelleno($valuetickes['fecha_doc'],8,'0','LEFT');
				$tmp[] = $siigo_centro_costo		= InterfaceMovModel::autorelleno($regcuenta['centrocosto'],4,'0','LEFT');
				//$tmp[] = $siigo_sub_centro_costo	= "000";
				$tmp[] = $siigo_sub_centro_costo	= InterfaceMovModel::autorelleno($valuetickes['nu_cuenta_sub_centro_costo'],3,'0','LEFT');
				$tmp[] = $siigo_nombre_producto		= InterfaceMovModel::autorelleno($noproducto,50,' ','RI');
				$tmp[] = $siigo_naturaleza			= InterfaceMovModel::autorelleno($regcuenta['naturaleza'],1,'0','LEFT');
				$tmp[] = $siigo_valor_movimiento	= InterfaceMovModel::autorelleno($valor_movi,15,'0','LEFT');//condicion
				$tmp[] = $siigo_retencion			= "000000000000000";
				$tmp[] = $siigo_vendedor			= "0001";
				$tmp[] = $siigo_ciudad				= "0001";
				$tmp[] = $siigo_zona				= "000";
				$tmp[] = $siigo_almacen				= InterfaceMovModel::autorelleno($regcuenta['almacen'],4,'0','LEFT');
				$tmp[] = $siigo_ubicacion			= "000";		
				$tmp[] = $siigo_cantidad			= InterfaceMovModel::autorelleno($valuetickes['cantidad'],15,'0','LEFT');//EXCEL FILA U
			
				//if($regcuenta['cod_rapido_bus'] == 'TOT' || ($accion == 'manual' && $regcuenta['cod_rapido_bus'] == 'IGV' && $valuetickes['notipocredito'] == 'S')){//solo tipo de cuenta 12 y 40 (facturas ventas manuales al credito) lleva el valor de cruze
				if($regcuenta['cod_rapido_bus'] == 'TOT' || ($accion == 'manual' && $regcuenta['cod_rapido_bus'] == 'IGV' )){
					$tmp[] = $siigo_documento_cruce				= InterfaceMovModel::autorelleno($siigo_tipo_documento,1,'0','LEFT');
					$tmp[] = $siigo_documento_cruce_serie		= InterfaceMovModel::autorelleno($siigo_serie_documento,3,'','LEFT');
					if ( $valuetickes['notipocredito'] == 'S' )
						$tmp[] = $siigo_documento_cruce_numero		= InterfaceMovModel::autorelleno($valuetickes['num_documento'],11,'0','LEFT');
					else if ( $valuetickes['ref_tipo_comprobante'] != '' )
						$tmp[] = $siigo_documento_cruce_numero		= InterfaceMovModel::autorelleno($valuetickes['nu_numerodoc'],11,'0','LEFT');
					//$tmp[] = $siigo_documento_cruce_numero		= InterfaceMovModel::autorelleno($nu_documento_key,11,'0','LEFT');
					$tmp[] = $siigo_documento_cruce_secuencia	= InterfaceMovModel::autorelleno("001",3,'0','LEFT');
				}else{
					$tmp[] = $siigo_documento_cruce				= InterfaceMovModel::autorelleno("",1,' ','LEFT');
					$tmp[] = $siigo_documento_cruce_serie		= InterfaceMovModel::autorelleno("",3,' ','LEFT');
					$tmp[] = $siigo_documento_cruce_numero		= InterfaceMovModel::autorelleno("0",11,'0','LEFT');
					$tmp[] = $siigo_documento_cruce_secuencia	= InterfaceMovModel::autorelleno("0",3,'0','LEFT');
				}

				$tmp[] = $siigo_documento_cruce_fecha = InterfaceMovModel::autorelleno($valuetickes['fecha_doc'],8,'0','LEFT');

				if($regcuenta['cod_rapido_bus']=='TOT' ){
					$tmp[] = $siigo_forma_pago = InterfaceMovModel::autorelleno("0001",4,'0','LEFT');
				}else{
					$tmp[] = $siigo_forma_pago = InterfaceMovModel::autorelleno("0",4,'0','LEFT');
				}
	
				$tmp[] = $siigo_banco						= InterfaceMovModel::autorelleno("0",2,'0','LEFT');
				$tmp[] = $siigo_pedido_documento			= InterfaceMovModel::autorelleno("",1,' ','LEFT');
				$tmp[] = $siigo_pedido_codigo_compra		= InterfaceMovModel::autorelleno("0",3,'0','LEFT');
				$tmp[] = $siigo_pedido_numero				= InterfaceMovModel::autorelleno("0",11,'0','LEFT');
				$tmp[] = $siigo_pedido_secuencia			= InterfaceMovModel::autorelleno("0",3,'0','LEFT');
				$tmp[] = $siigo_codigo_moneda				= InterfaceMovModel::autorelleno("01",2,'0','LEFT');
				$tmp[] = $siigo_tipo_cambio					= InterfaceMovModel::autorelleno($valuetickes['nu_tipocambio'],15,'0','LEFT');
				$tmp[] = $siigo_valor_extranjero			= InterfaceMovModel::autorelleno($valor_movi_extranjero,15,'0','LEFT');
				$tmp[] = $siigo_concepto_nomina				= InterfaceMovModel::autorelleno("0",3,'0','LEFT');
				$tmp[] = $siigo_cantidad_pago				= InterfaceMovModel::autorelleno("0",11,'0','LEFT');
				$tmp[] = $siigo_porcentaje_desc_mov			= InterfaceMovModel::autorelleno("0",4,'0','LEFT');
				$tmp[] = $siigo_valor_desc_mov				= InterfaceMovModel::autorelleno("0",13,'0','LEFT');
				$tmp[] = $siigo_porcentaje_cargo_mov		= InterfaceMovModel::autorelleno("0",4,'0','LEFT');
				$tmp[] = $siigo_valor_cargo_mov				= InterfaceMovModel::autorelleno("0",13,'0','LEFT');
				$tmp[] = $siigo_porcentaje_igv				= InterfaceMovModel::autorelleno("0",4,'0','LEFT');
				$tmp[] = $siigo_valor_igv					= InterfaceMovModel::autorelleno("0",13,'0','LEFT');
				$tmp[] = $siigo_indicardor_nomina			= InterfaceMovModel::autorelleno("",1,' ','LEFT');
				$tmp[] = $siigo_numero_pago					= InterfaceMovModel::autorelleno("0",1,'0','LEFT');
				$tmp[] = $siigo_numero_checke				= InterfaceMovModel::autorelleno("0",11,'0','LEFT');
				$tmp[] = $siigo_numero_tipo_movi			= InterfaceMovModel::autorelleno("S",1,' ','LEFT');//comdiciom
				$tmp[] = $siigo_nombre_computador			= InterfaceMovModel::autorelleno("",4,' ','LEFT');
				$tmp[] = $siigo_estado_comprobante			= InterfaceMovModel::autorelleno("",1,' ','LEFT');
				$tmp[] = $siigo_ecuador1					= InterfaceMovModel::autorelleno("0",2,'0','LEFT');
				$tmp[] = $siigo_ecuador_credito				= InterfaceMovModel::autorelleno("0",2,'0','LEFT');
				$tmp[] = $siigo_numero_comprobante_prov		= InterfaceMovModel::autorelleno("",4,' ','LEFT');
				$tmp[] = $siigo_numero_doc_comprobante_prov	= InterfaceMovModel::autorelleno("0",11,'0','LEFT');
				$tmp[] = $siigo_prefijo_doc_prov			= InterfaceMovModel::autorelleno("",10,' ','LEFT');
				$tmp[] = $siigo_decha_doc_prov				= InterfaceMovModel::autorelleno("0",8,'0','LEFT');
				$tmp[] = $siigo_unitario_soles				= InterfaceMovModel::autorelleno("0",18,'0','LEFT');
				$tmp[] = $siigo_unitarios_extranjero		= InterfaceMovModel::autorelleno("0",18,'0','LEFT');
				$tmp[] = $siigo_indicardo_mov				= InterfaceMovModel::autorelleno("",1,' ','LEFT');
				$tmp[] = $siigo_depreciacion_activo			= InterfaceMovModel::autorelleno("0",3,'0','LEFT');
				$tmp[] = $siigo_secuencia_trans				= InterfaceMovModel::autorelleno("0",2,'0','LEFT');
				$tmp[] = $siigo_autorizacion_imprenta		= InterfaceMovModel::autorelleno("0",10,'0','LEFT');
				$tmp[] = $siigo_secuencia_marca				= InterfaceMovModel::autorelleno("",1,' ','LEFT');
				$tmp[] = $siigo_numero_caja					= InterfaceMovModel::autorelleno("0",3,'0','LEFT');
				$tmp[] = $siigo_numero_punto_obt			= InterfaceMovModel::autorelleno("0",14,'0','LEFT');
				$tmp[] = $siigo_cantidad_dos				= InterfaceMovModel::autorelleno("0",15,'0','LEFT');
				$tmp[] = $siigo_cantidad_alt_dos			= InterfaceMovModel::autorelleno("0",15,'0','LEFT');
				$tmp[] = $siigo_metodo_depreciacion			= InterfaceMovModel::autorelleno("",1,' ','LEFT');
				$tmp[] = $siigo_cant_factor_coversion		= InterfaceMovModel::autorelleno("0",18,'0','LEFT');
				$tmp[] = $siigo_operador_factor_coversion	= InterfaceMovModel::autorelleno("0",1,'0','LEFT');
				$tmp[] = $siigo_factor_coversion			= InterfaceMovModel::autorelleno("0",10,'0','LEFT');
				$tmp[] = $siigo_fecha_caducidad				= InterfaceMovModel::autorelleno("0",8,'0','LEFT');
				$tmp[] = $siigo_codigo_ice					= InterfaceMovModel::autorelleno("0",2,'0','LEFT');
				$tmp[] = $siigo_codigo_retencion			= InterfaceMovModel::autorelleno("",6,' ','LEFT');
				$tmp[] = $siigo_clase_retencion				= InterfaceMovModel::autorelleno("0",4,'0','LEFT');
				$tmp[] = $siigo_extra						= "                                            ";
				$tmp[] = $siigocomprobante_fiscal			= InterfaceMovModel::autorelleno("0",38,'0','LEFT');
				$tmp[] = $siigo_tipo_letra					= InterfaceMovModel::autorelleno("",1,' ','LEFT');
				$tmp[] = $siigo_estado_letra				= InterfaceMovModel::autorelleno("",1,' ','LEFT');
				$tmp[] = $siigo_valor_movimiento_f			= InterfaceMovModel::autorelleno("0",18,'0','LEFT');
				$tmp[] = $siigo_valor_movimiento_fe			= InterfaceMovModel::autorelleno("0",18,'0','LEFT');
				$tmp[] = $siigo_codigo_mp					= InterfaceMovModel::autorelleno("0",3,'0','LEFT');//EXCEL FILA "CE" = CODIGO DE MEDIO DE PAGO
				$tmp[] = $siigo_base_transaccion			= InterfaceMovModel::autorelleno("0",15,'0','LEFT');
				$tmp[] = $siigo_actividades_flujo_efectivo	= InterfaceMovModel::autorelleno($valuetickes['nu_cuenta_flujo_efectivo'],4,'0','LEFT');//SOLO SE USA PARA FORMA DE PAGO EN EFECTIVO Y TICKETS
				$tmp[] = $siigo_jamas_usado					= "                    000000000000000000000000000000000000000000000000";

				$correlativo++;
				$data_invoice_trans[] = $tmp;
			}
			$only_detail = TRUE;
		}
   	}//FIN DE VENTA
   
   	function validartipodocumento($tidodoc,$es_venta){
    	
		$tidodoc=trim($tidodoc);

		if($tidodoc=="B" && $es_venta=='VT'){
			return "Q";
		}

		if($tidodoc=="F" && $es_venta=='VT'){
			return "F";
		}
		
		if($tidodoc=="P" && $es_venta=='CP'){
			return "P";
		}

		return "E";    	

	}
    
	function getTrans($dataBuscar, $fecha) {
        global $sqlca;
	
	    $sql = "
		SELECT
        	ptmp.trans
        FROM
			pos_trans" . $fecha . " AS ptmp
		WHERE
	        ptmp.caja = '" . $dataBuscar[1] . "'
    	   	AND ptmp.td = '" . $dataBuscar[2] . "'
        	AND ptmp.turno = '" . $dataBuscar[3] . "'
        	AND trim(ptmp.codigo) = trim('" . $dataBuscar[4] . "')
        	AND ptmp.precio = " . $dataBuscar[5] . "
        	AND ptmp.igv = " . $dataBuscar[6] . "
        	AND ptmp.importe = " . $dataBuscar[7] . "
        	AND ptmp.pump = '" . $dataBuscar[8] . "'
        	AND ptmp.fpago = '" . $dataBuscar[9] . "'
        	AND trim(ptmp.ruc) = trim('" . $dataBuscar[10] ."') 
        	AND ptmp.tm NOT IN('A')
		LIMIT 1;
		";

		if ($sqlca->query($sql) < 0) {
			shell_exec("echo 'Error getTrans ($sql)'.>> error.log");
		}
		$trans = "-1";
		while ($reg = $sqlca->fetchRow())
			$trans = $reg[0];
		return $trans;
	}

	function getGNVHermanos($c_invoiceheader_id) {
        global $sqlca;
        $sql = "SELECT  
                        case when c.c_doctype_id ='10' then 'F'
                        when c.c_doctype_id ='35' then 'B'
                        end as td,
                        trim(substring(c.documentno from 0 for position('-' in c.documentno))) as cod_comprobante,

                        lpad(trim(substring(c.documentno from (position('-' in c.documentno)::INTEGER)+1 for 25))::TEXT,11,'0') as num_documento,
                        '0000000000000' as ruc_dni,
                        (select ch_sucursal from int_ta_sucursales limit 1) as sucursal,
                        '11620308'  as cod_producto,
                        to_char(c.created,'YYYYMMdd') as fecha_doc,
                       'VENTA DE GNV' as des_producto,

                        replace(to_char(de.linetotal,'0000000000000.99'),'.','')  as cuenta_importe,
                        replace(to_char((de.linetotal/1.18),'0000000000000.99'),'.','')  as cuenta_base_imponible,
                        replace(to_char(((de.linetotal*0.18)/1.18),'0000000000000.99'),'.','')  as cuenta_base_igv,
                        replace(to_char(((de.linetotal*0.18)/1.18),'00000000000.99'),'.','')  as cuenta_base_igv_2,
                        replace(to_char(de.quantity,'0000000000.99999'),'.','') as cantidad,


                        replace(to_char((de.linetotal)/util_fn_tipo_cambio_dia(c.created::date),'0000000000000.99'),'.','')  as cuenta_importe_extranjera,--enci es la cuenta(10,12,42)
                        replace(to_char((de.linetotal/1.18)/util_fn_tipo_cambio_dia(c.created::date),'0000000000000.99'),'.','')  as cuenta_base_imponible_extranjera,
                        replace(to_char(((de.linetotal*0.18)/1.18)/util_fn_tipo_cambio_dia(c.created::date),'0000000000000.99'),'.','')  as cuenta_base_igv_extranjera,


                        replace(to_char(de.unitprice,'0000000000000.99999'),'.','') as precio_unitario,
                        replace(to_char((de.unitprice)/util_fn_tipo_cambio_dia(c.created::date),'0000000000000.99999'),'.','') as precio_unitario_extranjero,

                        replace(to_char(util_fn_tipo_cambio_dia(c.created::date),'00000000.9999999') ,'.','')  as nu_tipocambio,

                        '10' as cantidad_registro,
                        'GNV' as tipo_producto

                        FROM c_invoiceheader c
                        INNER JOIN c_invoicedetail de  on c.c_invoiceheader_id=de.c_invoiceheader_id where c.c_invoiceheader_id='$c_invoiceheader_id'  limit 2;

                        ";

        if ($sqlca->query($sql) < 0) {
            echo "Error obtener datos del hermano de la venta de GNV .";
            shell_exec("echo 'Error obtener datos del hermano de la venta de GNV($sql)'.>> error.log");
        }
        $trans = array();
        while ($reg = $sqlca->fetchRow()) {
            $trans[] = $reg;
        }
        return $trans;
	}
	
	function obtieneVentasSiigo($desde, $hasta, $estaciones, $bResumido){
		global $sqlca;
	
		$propiedad = InterfaceMovModel::obtenerPropiedadAlmacenes($estaciones);
		$almacenes = InterfaceMovModel::obtieneListaEstaciones();
		//echo "<script>console.log('" . json_encode($estaciones) . "')</script>";
		//echo "<script>console.log('" . json_encode($propiedad) . "')</script>";
		//echo "<script>console.log('" . json_encode($almacenes) . "')</script>";

		$where_codigo_almacen = ($estaciones != "" ? "AND CTC.ch_sucursal = '" . pg_escape_string($estaciones) . "'" : "");		
$sql = "
SELECT
 CTC.ch_sucursal,
 SUM(CTC.nu_ventavalor) as nu_ventavalor,
 SUM(CTC.nu_ventagalon) as nu_ventagalon,
 FIRST(PTA.cantidad) AS ss_afericion_cantidad,
 FIRST(PTA.importe) AS ss_afericion_soles,
 CTC.ch_codigocombustible,
 CTC.dt_fechaparte,
 FIRST(FACMAN.cantidad) AS facman_cantidad,
 FIRST(FACMAN.importe) AS facman_valor,
 FIRST(CTCN.ch_nombrecombustible) as ch_nombrecombustible,
 SUM(CTC.nu_descuentos) as nu_descuentosvalor
FROM
 comb_ta_contometros AS CTC
 LEFT JOIN comb_ta_combustibles CTCN ON CTC.ch_codigocombustible = CTCN.ch_codigocombustible
 LEFT JOIN (
 SELECT
  dia,
  codigo,
  SUM(cantidad) AS cantidad,
  SUM(importe) AS importe
 FROM
  pos_ta_afericiones
 WHERE
  es = '" . pg_escape_string($estaciones) . "'
  AND dia BETWEEN to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') AND to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
 GROUP BY
  1,2
 ) AS PTA ON (CTC.dt_fechaparte = PTA.dia AND CTC.ch_codigocombustible = PTA.codigo)
 LEFT JOIN (
 SELECT 
  fc.dt_fac_fecha as dia, 
  fd.art_codigo as codigo, 
  SUM(fd.nu_fac_cantidad) as cantidad, 
  SUM(fd.nu_fac_valortotal) as importe
 FROM 
  fac_ta_factura_cabecera fc
 INNER JOIN fac_ta_factura_detalle fd ON fc.ch_fac_seriedocumento = fd.ch_fac_seriedocumento AND fc.ch_fac_numerodocumento = fd.ch_fac_numerodocumento
 INNER JOIN comb_ta_combustibles CTCN ON fd.art_codigo = CTCN.ch_codigocombustible
 WHERE 
  fc.ch_almacen = '" . pg_escape_string($estaciones) . "'
  AND fc.dt_fac_fecha BETWEEN to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') AND to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
 GROUP BY 
  1,2
 ) AS FACMAN ON (CTC.dt_fechaparte = FACMAN.dia AND CTC.ch_codigocombustible = FACMAN.codigo)
WHERE
 CTC.dt_fechaparte BETWEEN to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') AND to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
 ". $where_codigo_almacen ."
 AND CTC.nu_ventavalor > 0
 AND CTC.nu_ventagalon > 0
 --AND CTCN.ch_nombrecombustible = 'D2 DIESEL' --Prueba
GROUP BY
 1,6,7
ORDER BY
 ch_sucursal,
 dt_fechaparte;
";
error_log($sql);

	/* Obtenemos data de ventas para asientos contables de Siigo */
	$dataVentas = Array();

	if ($sqlca->query($sql) < 0) return false;		
	for ($i = 0; $i < $sqlca->numrows(); $i++) {
		$a = $sqlca->fetchRow();						
			
		$ch_sucursal          = trim($a[0]);
		$nu_ventavalor        = (float)$a[1]; //Ventas valor
		$nu_ventagalon        = (float)$a[2]; //Ventas cantidad
		$fQuantityAfericion   = (float)$a[3]; //Afericiones cantidad
		$fAmountAfericion     = (float)$a[4]; //Afericiones valor
		$ch_codigocombustible = $a[5];
		$dt_fechaparte        = $a[6];
		$facman_cantidad      = (float)$a[7]; //Facturas manuales cantidad
		$facman_importe       = (float)$a[8]; //Facturas manuales importe
		$ch_nombrecombustible = $a[9];
		$nu_descuentosvalor   = $a[10];       //Descuentos valor
		
		@$dataVentas[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible]['ch_sucursal']          = $ch_sucursal;
		@$dataVentas[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible]['nu_ventavalor']        = $nu_ventavalor;
		@$dataVentas[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible]['nu_ventagalon']        = $nu_ventagalon;
		@$dataVentas[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible]['fQuantityAfericion']   = $fQuantityAfericion;
		@$dataVentas[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible]['fAmountAfericion']     = $fAmountAfericion;
		@$dataVentas[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible]['ch_codigocombustible'] = $ch_codigocombustible;
		@$dataVentas[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible]['dt_fechaparte']        = $dt_fechaparte;
		@$dataVentas[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible]['facman_cantidad']      = $facman_cantidad;
		@$dataVentas[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible]['facman_importe']       = $facman_importe;
		@$dataVentas[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible]['ch_nombrecombustible'] = $ch_nombrecombustible;					
		@$dataVentas[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible]['nu_descuentosvalor']   = $nu_descuentosvalor;
		//Ventas reales
		@$dataVentas[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible]['nu_ventavalor_real']   = $nu_ventavalor - $fAmountAfericion; //+ $nu_descuentosvalor
		@$dataVentas[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible]['nu_ventagalon_real']   = $nu_ventagalon - $fQuantityAfericion;
	}
	$dataVentas = $dataVentas[$estaciones];
	//echo "<script>console.log('************************************ FORMULA PARA ASIENTOS: VENTAS DIARIAS - AFERICIONES - DESCUENTOS - FACTURAS MANUALES ************************************')</script>";	
	//echo "<script>console.log('************************************ VENTAS DIARIAS - AFERICIONES - DESCUENTOS ************************************')</script>";	
	//echo "<script>console.log('". json_encode($dataVentas) ."')</script>";

	/* Descontamos ventas manuales */
	$dataVentas = InterfaceMovModel::descontamosFacturasManualesMenores($dataVentas);
	//echo "<script>console.log('************************************ Descontamos Facturas Manuales ************************************')</script>";	
	//echo "<script>console.log('". json_encode($dataVentas) ."')</script>";
	
	$dataVentas = InterfaceMovModel::descontamosFacturasManualesMayores($dataVentas);
	//echo "<script>console.log('************************************ Descontamos Facturas Manuales ************************************')</script>";	
	//echo "<script>console.log('". json_encode($dataVentas) ."')</script>";

	/* Formatear asientos */
	$cuentas_contables = array(
		"7001010100" => "7001010100", //IMP
		"1001010000" => "1001010000", //TOT
		"4001010100" => "4001010100", //IGV
		"2001010100" => "2001010100", //MER
	);
	$configuracion_cuentas = InterfaceMovModel::SetDatamain("VT");
	$dataSiigo = Array();
	
	foreach ($dataVentas as $key=>$fecha) {
		//echo "<script>console.log('************************************ $key ************************************')</script>";	
		//echo "<script>console.log('". json_encode($fecha) ."')</script>";	
		$total = 0;
		$imponible = 0;
		$total2 = 0;
		$total3 = 0;
		$total4 = 0;
		$imponible3 = 0;
		$igv3 = 0;

		foreach ($cuentas_contables as $key2=>$cuenta) {	
			//echo "<script>console.log('". json_encode($cuenta) ."')</script>";
			foreach ($fecha as $key3=>$combustible) {		
				$a                  = $combustible;
				$cuentacont	        = $configuracion_cuentas[$a['ch_codigocombustible']];
				$dataCuentaContable = InterfaceMovModel::procesarCuentaContable($cuentacont);
				//echo "<script>console.log('". json_encode($a) ."')</script>";	

				$ch_sucursal          = trim($a['ch_sucursal']);
				$nu_ventavalor        = (float)$a['nu_ventavalor'];      //Ventas valor
				$nu_ventagalon        = (float)$a['nu_ventagalon'];      //Ventas cantidad
				$fQuantityAfericion   = (float)$a['fQuantityAfericion']; //Afericiones cantidad
				$fAmountAfericion     = (float)$a['fAmountAfericion'];   //Afericiones valor
				$ch_codigocombustible = $a['ch_codigocombustible'];
				$dt_fechaparte        = $a['dt_fechaparte'];
				$facman_cantidad      = (float)$a['facman_cantidad'];    //Facturas manuales cantidad
				$facman_importe       = (float)$a['facman_importe'];     //Facturas manuales importe
				$ch_nombrecombustible = $a['ch_nombrecombustible'];
				$nu_descuentosvalor   = $a['nu_descuentosvalor'];        //Descuentos valor
				
				//Ventas reales
				$nu_ventavalor_real   = $a['nu_ventavalor_real'];        //Ventas reales valor (Aun falta quitar las facturas manuales)
				$nu_ventagalon_real   = $a['nu_ventagalon_real'];        //Ventas reales cantidad (Aun falta quitar las facturas manuales)
				//Fecha
				$numero_documento = explode("-", $dt_fechaparte);
				$numero_documento = $numero_documento[0].$numero_documento[1].$numero_documento[2];
				//Ano, mes, dia
				$ano_mes_dia   = explode("-", $dt_fechaparte);
				$ano_documento = $ano_mes_dia[0];
				$mes_documento = $ano_mes_dia[1];
				$dia_documento = $ano_mes_dia[2];

				if($cuenta == "7001010100"){												
					//Imponible
					$total     = round($nu_ventavalor_real, 2);
					$imponible = round($total/1.18, 2);
					$cantidad  = $nu_ventagalon_real;
					@$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['tipo_comprobante']      = "F";               
					@$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['codigo_comprobante']    = "002";
					@$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['numero_documento']      = InterfaceMovModel::autorelleno($numero_documento, 11, '0', 'LEFT');
					@$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['cuenta_contable']       = InterfaceMovModel::autorelleno($cuenta, 10, '0', 'LEFT');           
					@$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['debito_credito']        = "C";			    
					@$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['valor_secuencia']       = InterfaceMovModel::autorelleno($imponible, 13, '0', 'LEFT');        
					@$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['ano_documento']         = $ano_documento;    
					@$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['mes_documento']         = $mes_documento;    
					@$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['dia_documento']         = $dia_documento;    
					@$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['ch_nombrecombustible']  = $ch_nombrecombustible;
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['codigo_vendedor']       = "0001"; /* aaa */
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['codigo_ciudad']         = "0001";    
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['codigo_zona']           = "001";    
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['secuencia']             = "1 - 12"; //???
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['centro_costo']          = InterfaceMovModel::autorelleno($dataCuentaContable[$cuenta]['centrocosto'], 4, '0', 'LEFT');
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['sub_centro_costo']      = InterfaceMovModel::autorelleno($dataCuentaContable[$cuenta]['subcentrocosto'], 3, '0', 'LEFT');
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['NIT']                   = InterfaceMovModel::autorelleno("20428254687", 13, '0', 'LEFT');
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['sucursal']              = InterfaceMovModel::autorelleno($dataCuentaContable[$cuenta]['sucursal'], 3, '0', 'LEFT');
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['descripcion_secuencia'] = InterfaceMovModel::autorelleno($ch_nombrecombustible, 50, ' ', 'LEFT'); /* Aqui me quede */
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['numero_cheque']         = ""; //???
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['comprobante_anulado']   = "N";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['codigo_devolucion']     = "0";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['forma_pago']            = "0";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['valor_secuencia']       = "0.00";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['valor_secuencia_2']     = "0.00";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['valor_secuencia_3']     = "0.00";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['porcentaje_iva']        = "18";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['valor_iva']             = $total;
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['base_retencion']        = "0.00000";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['base_cuentas_reteiva']  = "0.00";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['porcentaje_aiu']        = "0.00000";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['base_iva_aiu']          = "0.00000";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['linea_producto']        = ""; //???
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['grupo_producto']        = ""; //???
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['codigo_producto']       = ""; //???
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['cantidad']              = $cantidad;
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['cantidad_2']            = "0.00000";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['codigo_bodega']         = "3";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['codigo_ubicacion']      = "0";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['cant_fac_conversion']   = "0.00000";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['oper_fac_conversion']   = "0";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['val_fac_conversion']    = "0.00000";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['grupos_activos']        = "";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['codigo_activo']         = "";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['adicion_mejora']        = "";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['veces_adicionales']     = "0";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['veces_niff']            = "0";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['numero_proveedor']      = "0";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['prefijo_proveedor']     = "";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['ano_proveedor']         = "";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['mes_proveedor']         = "";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['dia_proveedor']         = "";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['tipo_doc_pedido']       = "";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['cod_comp_pedido']       = "0";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['num_comp_pedido']       = "0";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['sec_pedido']            = "0";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['cod_moneda']            = "1";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['tasa_cambio']           = ""; //???
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['valor_secuencia']       = ""; //???
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['tipo_moneda_elab']      = "0";	
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['tipo_comp_cruce']       = "0";	
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['num_comp_cruce']        = "0";	
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['num_venc_cruce']        = "0";	
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['ano_venc_cruce']        = "";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['mes_venc_cruce']        = "";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['dia_venc_cruce']        = "";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['concepto_nomina']       = "0";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['tipo_pago']             = "0";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['doc_origen_prov']       = "";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['ano_detraccion']        = "";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['mes_detraccion']        = "";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['dia_detraccion']        = "";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['indi_tip_letra']        = "";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['est_asig_letra']        = "";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['cod_med_pago']          = "0";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['act_flujo_efec']        = "0";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['num_deposito']          = "0";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['porc_igv_detraccion']   = "0.00";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['base_cal_detraccion']   = "0.00";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['val_igv_detraccion']    = "0.00";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['cod_tasa_detraccion']   = "";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['doc_trans_bancaria']    = "0";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['item_afecto_inafecto']  = "S";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['ano_emision']           = "";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['mes_emision']           = "";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['dia_emision']           = "";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['doc_orig_preimpreso']   = "0";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['cod_sec_transaccion']   = "0";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['tip_operacion']         = "0";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['desc_comentarios']      = "";
					// @$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_7001010100"]['desc_larga']            = "";
				}
	
				if($cuenta == "1001010000"){
					//Total acumulado
					$total2 += round($nu_ventavalor_real, 2);					
					@$dataSiigo[$ch_sucursal][$dt_fechaparte]["TODOS_1001010000"]['tipo_comprobante']     = "F";               
					@$dataSiigo[$ch_sucursal][$dt_fechaparte]["TODOS_1001010000"]['codigo_comprobante']   = "002";               
					@$dataSiigo[$ch_sucursal][$dt_fechaparte]["TODOS_1001010000"]['numero_documento']     = InterfaceMovModel::autorelleno($numero_documento, 11, '0', 'LEFT');
					@$dataSiigo[$ch_sucursal][$dt_fechaparte]["TODOS_1001010000"]['cuenta_contable']      = InterfaceMovModel::autorelleno($cuenta, 10, '0', 'LEFT');           
					@$dataSiigo[$ch_sucursal][$dt_fechaparte]["TODOS_1001010000"]['debito_credito']       = "D";			   
					@$dataSiigo[$ch_sucursal][$dt_fechaparte]["TODOS_1001010000"]['valor_secuencia']      = InterfaceMovModel::autorelleno($total2, 13, '0', 'LEFT');          
					@$dataSiigo[$ch_sucursal][$dt_fechaparte]["TODOS_1001010000"]['ano_documento']        = $ano_documento;    
					@$dataSiigo[$ch_sucursal][$dt_fechaparte]["TODOS_1001010000"]['mes_documento']        = $mes_documento;    
					@$dataSiigo[$ch_sucursal][$dt_fechaparte]["TODOS_1001010000"]['dia_documento']        = $dia_documento;      
					@$dataSiigo[$ch_sucursal][$dt_fechaparte]["TODOS_1001010000"]['ch_nombrecombustible'] = $ch_nombrecombustible; 
				}
	
				if($cuenta == "4001010100"){
					//Imponible acumulado
					$total3      = round($nu_ventavalor_real, 2);								
					$imponible3 += round($total3/1.18, 2);				
					//Total acumulado
					$total4 += round($nu_ventavalor_real, 2);
					//Total acumulado - imponible acumulado
					$igv3   = $total4 - $imponible3;					
					@$dataSiigo[$ch_sucursal][$dt_fechaparte]["TODOS_4001010100"]['tipo_comprobante']     = "F";               
					@$dataSiigo[$ch_sucursal][$dt_fechaparte]["TODOS_4001010100"]['codigo_comprobante']   = "002";             
					@$dataSiigo[$ch_sucursal][$dt_fechaparte]["TODOS_4001010100"]['numero_documento']     = InterfaceMovModel::autorelleno($numero_documento, 11, '0', 'LEFT');
					@$dataSiigo[$ch_sucursal][$dt_fechaparte]["TODOS_4001010100"]['cuenta_contable']      = InterfaceMovModel::autorelleno($cuenta, 10, '0', 'LEFT');
					@$dataSiigo[$ch_sucursal][$dt_fechaparte]["TODOS_4001010100"]['debito_credito']       = "C";			   
					@$dataSiigo[$ch_sucursal][$dt_fechaparte]["TODOS_4001010100"]['valor_secuencia']      = InterfaceMovModel::autorelleno($igv3, 13, '0', 'LEFT');                       
					@$dataSiigo[$ch_sucursal][$dt_fechaparte]["TODOS_4001010100"]['ano_documento']        = $ano_documento;    
					@$dataSiigo[$ch_sucursal][$dt_fechaparte]["TODOS_4001010100"]['mes_documento']        = $mes_documento;    
					@$dataSiigo[$ch_sucursal][$dt_fechaparte]["TODOS_4001010100"]['dia_documento']        = $dia_documento;    
					@$dataSiigo[$ch_sucursal][$dt_fechaparte]["TODOS_4001010100"]['ch_nombrecombustible'] = $ch_nombrecombustible; 
				}
	
				if($cuenta == "2001010100"){
					@$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_2001010100"]['tipo_comprobante']     = "F";               
					@$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_2001010100"]['codigo_comprobante']   = "002";             
					@$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_2001010100"]['numero_documento']     = InterfaceMovModel::autorelleno($numero_documento, 11, '0', 'LEFT');
					@$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_2001010100"]['cuenta_contable']      = InterfaceMovModel::autorelleno($cuenta, 10, '0', 'LEFT');           
					@$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_2001010100"]['debito_credito']       = "C";			    
					@$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_2001010100"]['valor_secuencia']      = InterfaceMovModel::autorelleno("0.00", 13, '0', 'LEFT');              
					@$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_2001010100"]['ano_documento']        = $ano_documento;    
					@$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_2001010100"]['mes_documento']        = $mes_documento;    
					@$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_2001010100"]['dia_documento']        = $dia_documento;    
					@$dataSiigo[$ch_sucursal][$dt_fechaparte][$ch_codigocombustible."_2001010100"]['ch_nombrecombustible'] = $ch_nombrecombustible; 
				}
			}
		}
	}
	//echo "<script>console.log('************************************ dataSiigo ************************************')</script>";	
	//echo "<script>console.log('" . json_encode($dataSiigo) . "')</script>";

	error_log( json_encode($dataSiigo) );
	return $dataSiigo;	
	}

	function obtenerPropiedadAlmacenes($sCodigoAlmacen){
		global $sqlca;

		//Se aumento where el 30/11/2018
		$where_codigo_almacen = ($sCodigoAlmacen != "" ? "AND ch_almacen = '" . pg_escape_string($sCodigoAlmacen) . "'" : "");

		$sql = "
SELECT
 ch_almacen,
 'S' AS ch_almacen_propio
FROM
 inv_ta_almacenes
WHERE
 ch_clase_almacen = '1'
 " . $where_codigo_almacen;

		if ($sqlca->query($sql) < 0) return false;
	
		$result = Array();
	
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();    
			$result[$a[0]] = $a[1];
		}
		return $result;
	}

	function obtieneListaEstaciones(){
		global $sqlca;
	
		$sql = "
SELECT
 ch_almacen,
 trim(ch_nombre_almacen)
FROM
 inv_ta_almacenes
WHERE
 ch_clase_almacen='1'
ORDER BY
 ch_almacen;
		";

		if ($sqlca->query($sql) < 0) return false;
	
		$result = Array();
	
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    $a = $sqlca->fetchRow();
		    $result[$a[0]] = $a[0] . " - " . $a[1];
		}
	
		return $result;
	}
	
	function procesarCuentaContable($cuentacont){
		$dataCuenta = Array();
		foreach ($cuentacont as $key=>$value){
			$cuenta = $value['cuenta'];

			@$dataCuenta[$cuenta]['sucursal'] = $value['sucursal'];
			@$dataCuenta[$cuenta]['centrocosto'] = $value['centrocosto'];
			@$dataCuenta[$cuenta]['tipo_producto'] = $value['tipo_producto'];
			@$dataCuenta[$cuenta]['tipo_documento'] = $value['tipo_documento'];
			@$dataCuenta[$cuenta]['cuenta'] = $value['cuenta'];
			@$dataCuenta[$cuenta]['cuenta_tickes'] = $value['cuenta_tickes'];
			@$dataCuenta[$cuenta]['cuenta_descripcion'] = $value['cuenta_descripcion'];
			@$dataCuenta[$cuenta]['naturaleza'] = $value['naturaleza'];
			@$dataCuenta[$cuenta]['codigo_producto'] = $value['codigo_producto'];
			@$dataCuenta[$cuenta]['tipo_asiento'] = $value['tipo_asiento'];
			@$dataCuenta[$cuenta]['cod_rapido_bus'] = $value['cod_rapido_bus'];
			@$dataCuenta[$cuenta]['almacen'] = $value['almacen'];
			@$dataCuenta[$cuenta]['codigo_producto_siigo'] = $value['codigo_producto_siigo'];
			@$dataCuenta[$cuenta]['serietickesboleta'] = $value['serietickesboleta'];
			@$dataCuenta[$cuenta]['nuvmseriefactura'] = $value['nuvmseriefactura'];
			@$dataCuenta[$cuenta]['nucpseriefactura'] = $value['nucpseriefactura'];
			@$dataCuenta[$cuenta]['serietickesfacnucpseriefacturaglptura'] = $value['nucpseriefacturaglp'];
			@$dataCuenta[$cuenta]['nu_serie_siigo_consolidado_x_dia'] = $value['nu_serie_siigo_consolidado_x_dia'];
			@$dataCuenta[$cuenta]['nu_ruc_empresa'] = $value['nu_ruc_empresa'];
			@$dataCuenta[$cuenta]['subcentrocosto'] = $value['subcentrocosto'];
		}
		return $dataCuenta;
	}

	/*	 
	 * Descontamos facturas manuales 
	 * Descontamos facturas manuales cuya cantidad es menor a la cantidad de (VENTAS DIARIAS - AFERICIONES - DESCUENTOS)
	 */
	function descontamosFacturasManualesMenores($dataVentas){
		foreach ($dataVentas as $key=>$fecha) {
			foreach ($dataVentas[$key] as $key2=>$combustible) {
				if($dataVentas[$key][$key2]['facman_cantidad'] <= $dataVentas[$key][$key2]['nu_ventagalon_real']){
					$dataVentas[$key][$key2]['nu_ventagalon_real'] = $dataVentas[$key][$key2]['nu_ventagalon_real'] - $dataVentas[$key][$key2]['facman_cantidad'];
					$dataVentas[$key][$key2]['nu_ventavalor_real'] = $dataVentas[$key][$key2]['nu_ventavalor_real'] - $dataVentas[$key][$key2]['facman_importe'];					
					$dataVentas[$key][$key2]['facman_cantidad'] = 0;
					$dataVentas[$key][$key2]['facman_importe'] = 0;
				}
			}		
		}
		return $dataVentas;
	}	

	/*	 
	 * Descontamos facturas manuales 
	 * Descontamos facturas manuales cuya cantidad es mayor a la cantidad de (VENTAS DIARIAS - AFERICIONES - DESCUENTOS)
	 * Aqui ocurre que debemos restar a dias anteriores con el mismo tipo de combustible, por que la cantidad no se pude cubrir en el día
	 */
	function descontamosFacturasManualesMayores($dataVentas){
		foreach ($dataVentas as $key=>$fecha) {
			foreach ($dataVentas[$key] as $key2=>$combustible) {
				$dia = 1;				

				if($dataVentas[$key][$key2]['facman_cantidad'] > $dataVentas[$key][$key2]['nu_ventagalon_real']){
					/* Prueba */
					// $dataVentas[$key][$key2]['facman_cantidad'] = 1000000;
					// $dataVentas[$key][$key2]['facman_importe'] = 1000000;
					/* Fin Prueba */

					$dataVentas[$key][$key2]['facman_cantidad_para_quitar_en_dia_anterior'] = $dataVentas[$key][$key2]['facman_cantidad'] - $dataVentas[$key][$key2]['nu_ventagalon_real'];
					$dataVentas[$key][$key2]['facman_importe_para_quitar_en_dia_anterior'] = $dataVentas[$key][$key2]['facman_importe'] - $dataVentas[$key][$key2]['nu_ventavalor_real'];					
					$dataVentas[$key][$key2]['nu_ventagalon_real'] = 0.00;
					$dataVentas[$key][$key2]['nu_ventavalor_real'] = 0.00;
					$dataVentas[$key][$key2]['facman_cantidad_dias_atras'] = $dia++;
	
					$fechaDiaAtras = InterfaceMovModel::obtenerDiaAtras($key);					
					if($dataVentas[$key][$key2]['facman_cantidad_para_quitar_en_dia_anterior'] > $dataVentas[$fechaDiaAtras][$key2]['nu_ventagalon_real'] && isset($dataVentas[$fechaDiaAtras])){
						$dataVentas[$key][$key2]['facman_cantidad_para_quitar_en_dia_anterior'] = $dataVentas[$key][$key2]['facman_cantidad_para_quitar_en_dia_anterior'] - $dataVentas[$fechaDiaAtras][$key2]['nu_ventagalon_real'];
						$dataVentas[$key][$key2]['facman_importe_para_quitar_en_dia_anterior'] = $dataVentas[$key][$key2]['facman_importe_para_quitar_en_dia_anterior'] - $dataVentas[$fechaDiaAtras][$key2]['nu_ventavalor_real'];						
						$dataVentas[$fechaDiaAtras][$key2]['nu_ventagalon_real'] = 0.00;
						$dataVentas[$fechaDiaAtras][$key2]['nu_ventavalor_real'] = 0.00;
						$dataVentas[$key][$key2]['facman_cantidad_dias_atras'] = $dia++;
						
						for ($i=0; $i<100; $i++) { 	
							$fechaDiaAtras = InterfaceMovModel::obtenerDiaAtras($fechaDiaAtras);					
							if($dataVentas[$key][$key2]['facman_cantidad_para_quitar_en_dia_anterior'] > $dataVentas[$fechaDiaAtras][$key2]['nu_ventagalon_real'] && isset($dataVentas[$fechaDiaAtras])){
								$dataVentas[$key][$key2]['facman_cantidad_para_quitar_en_dia_anterior'] = $dataVentas[$key][$key2]['facman_cantidad_para_quitar_en_dia_anterior'] - $dataVentas[$fechaDiaAtras][$key2]['nu_ventagalon_real'];
								$dataVentas[$key][$key2]['facman_importe_para_quitar_en_dia_anterior'] = $dataVentas[$key][$key2]['facman_importe_para_quitar_en_dia_anterior'] - $dataVentas[$fechaDiaAtras][$key2]['nu_ventavalor_real'];						
								$dataVentas[$fechaDiaAtras][$key2]['nu_ventagalon_real'] = 0.00;
								$dataVentas[$fechaDiaAtras][$key2]['nu_ventavalor_real'] = 0.00;
								$dataVentas[$key][$key2]['facman_cantidad_dias_atras'] = $dia++;																					
							}else{
								$dataVentas[$fechaDiaAtras][$key2]['nu_ventagalon_real'] = $dataVentas[$fechaDiaAtras][$key2]['nu_ventagalon_real'] - $dataVentas[$key][$key2]['facman_cantidad_para_quitar_en_dia_anterior'];
								$dataVentas[$fechaDiaAtras][$key2]['nu_ventavalor_real'] = $dataVentas[$fechaDiaAtras][$key2]['nu_ventavalor_real'] - $dataVentas[$key][$key2]['facman_importe_para_quitar_en_dia_anterior'];
								break;
							}
						}
					}else{
 						$dataVentas[$fechaDiaAtras][$key2]['nu_ventagalon_real'] = $dataVentas[$fechaDiaAtras][$key2]['nu_ventagalon_real'] - $dataVentas[$key][$key2]['facman_cantidad_para_quitar_en_dia_anterior'];
						$dataVentas[$fechaDiaAtras][$key2]['nu_ventavalor_real'] = $dataVentas[$fechaDiaAtras][$key2]['nu_ventavalor_real'] - $dataVentas[$key][$key2]['facman_importe_para_quitar_en_dia_anterior'];
					}
				}
			}
		}
		return $dataVentas;
	}

	function obtenerDiaAtras($key){
		$fecha = explode("-", $key);
		$dia   = $fecha[2] - 1;
		$dia   = InterfaceMovModel::autorelleno($dia, 2, '0', 'LEFT');
		return $fecha[0]."-".$fecha[1]."-".$dia;
	}
}
