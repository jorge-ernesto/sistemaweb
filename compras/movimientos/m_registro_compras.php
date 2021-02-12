<?php

class RegistroComprasModel extends Model {

	function BEGINTransaccion() {
        global $sqlca;

		try {

			$sql = "BEGIN;";
			
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
	
			$sql = "COMMIT;";

			if ($sqlca->query($sql) < 0) {
				throw new Exception("No se pudo PROCESAR la TRANSACION");
			}

		} catch (Exception $e) {
			throw $e;
		}

	}

	function ROLLBACKTransaccion() {
		global $sqlca;

		try {

			$sql = "ROLLBACK;";

			if ($sqlca->query($sql) < 0) {
				throw new Exception("No se pudo REVERTIR el proceso.");
			}

		} catch (Exception $e) {
			throw $e;
		}
	}

    	function obtieneListaEstaciones() {

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

		if ($sqlca->query($sql) < 0) 
			return false;

		$result = Array();
	
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    	$a = $sqlca->fetchRow();
		    	$result[$a[0]] = $a[0] . " - " . $a[1];
		}

		return $result;

    	}

	function obtenerAlma($almacen) {
		global $sqlca;

		if (trim($almacen) == "") {
		    $almacen = $_SESSION['almacen'];
		}

		$sql1 = "SELECT ch_almacen FROM inv_ta_almacenes where ch_almacen = ch_sucursal;";

		if ($sqlca->query($sql1) < 0)
			return false;

		$a = $sqlca->fetchRow();
		$alma = $a['ch_almacen'];

		if ($almacen == $alma) {
		    $cond = "";
		} else {
		    $cond = "_market";
		}

		$q1 = "SELECT par_valor FROM int_parametros WHERE par_nombre='razsocial$cond';";

		if ($sqlca->query($q1) < 0)
			return false;

		$a = $sqlca->fetchRow();

		$razsocial = $a['par_valor'];

		$q2 = "SELECT par_valor FROM int_parametros WHERE par_nombre='ruc$cond';";

		if ($sqlca->query($q2) < 0)
			return false;

		$a = $sqlca->fetchRow();
		$ruc 	= $a['par_valor'];

		$resultado 		= Array();
		$resultado[0] 	= $razsocial;
		$resultado[1] 	= $ruc;

		return $resultado;

	}

	function Paginacion($pp, $pagina, $fecha, $fecha2, $estacion, $proveedor, $documento, $tdocu, $tmoneda){
		global $sqlca;

		$sql = "
			SELECT
				to_char(c.pro_cab_fecharegistro, 'DD/MM/YYYY') fregistro,
				to_char(c.pro_cab_fechaemision, 'DD/MM/YYYY') femision,
				a.ch_sigla_almacen cc,
				gen.tab_desc_breve||' '||LPAD(CAST(c.pro_cab_seriedocumento AS bpchar),4,'0')||' - '||c.pro_cab_numdocumento documento, 
				p.pro_ruc||' - '||p.pro_razsocial proveedor,
				mone.tab_desc_breve moneda,
				c.pro_cab_impto1 impuesto,
				c.pro_cab_imptotal total,
				c.pro_cab_impsaldo saldo,
				rubro.ch_descripcion_breve rubro,
				CASE WHEN
					gen.tab_car_03 = '14' THEN to_char(c.pro_cab_fechavencimiento, 'DD/MM/YYYY') 
				ELSE
					''
				END as fvencimiento,
				c.pro_cab_tipdocumento||' '||c.pro_cab_seriedocumento||' - '||c.pro_cab_numdocumento|| ' - ' ||c.pro_codigo eliminar,
				c.pro_cab_fecharegistro,
				c.regc_sunat_percepcion perce,
				c.pro_cab_tcambio tc,
				c.pro_cab_impinafecto inafecto,
				c.pro_cab_impafecto imponible,
				c.pro_cab_tipdocumento doctype,
				c.pro_cab_numreg corre,
				(SELECT to_char(nc.pro_cab_fechaemision, 'DD/MM/YYYY') FROM cpag_ta_cabecera nc WHERE nc.pro_codigo = c.pro_codigo AND nc.pro_cab_tipdocumento = c.pro_cab_tipdocreferencia AND nc.pro_cab_seriedocumento||nc.pro_cab_numdocumento = c.pro_cab_numdocreferencia) validanc,
				(CASE WHEN c.pro_cab_moneda = '02' OR c.pro_cab_moneda = '2' THEN ROUND((c.pro_cab_imptotal * c.pro_cab_tcambio), 2) ELSE 0.00 END) totald,
				(CASE WHEN c.pro_cab_moneda = '02' OR c.pro_cab_moneda = '2' THEN ROUND((c.pro_cab_impsaldo * c.pro_cab_tcambio), 2) ELSE 0.00 END) saldod,
				c.pro_cab_almacen
			FROM
				cpag_ta_cabecera c
				LEFT JOIN cpag_ta_cabecera nc ON(nc.pro_codigo = c.pro_codigo AND nc.pro_cab_tipdocumento = c.pro_cab_tipdocreferencia AND nc.pro_cab_seriedocumento||nc.pro_cab_numdocumento = c.pro_cab_numdocreferencia)
				LEFT JOIN int_proveedores p ON(c.pro_codigo = p.pro_codigo)
				LEFT JOIN inv_ta_almacenes a ON(c.pro_cab_almacen = a.ch_almacen)
				LEFT JOIN int_tabla_general as gen ON(c.pro_cab_tipdocumento = substring(TRIM(tab_elemento) for 2 from length(TRIM(tab_elemento))-1) and tab_tabla ='08')
				LEFT JOIN cpag_ta_rubros rubro ON(rubro.ch_codigo_rubro = c.pro_cab_rubrodoc)
				LEFT JOIN int_tabla_general AS mone ON(c.pro_cab_moneda = (substring(trim(mone.tab_elemento) for 2 from length(trim(mone.tab_elemento))-1)) AND mone.tab_tabla='04' AND mone.tab_elemento != '000000')
				LEFT JOIN int_tabla_general as gen2 ON((c.pro_cab_tipdocumento = substring(TRIM(gen2.tab_elemento) for 2 from length(TRIM(gen2.tab_elemento))-1) and gen2.tab_tabla ='08'))
			";

		if($fecha != ''){
		$sql .= "
			WHERE
				c.pro_cab_fechaemision::DATE BETWEEN to_date('$fecha','DD/MM/YYYY') AND to_date('$fecha2','DD/MM/YYYY') ";
		}

		if($estacion != '')
		$sql .= "	AND c.pro_cab_almacen = '$estacion' ";

		if($proveedor != '')
		$sql .= "	AND c.pro_codigo = '$proveedor' ";
		
		if($documento != '')
		$sql .= "	AND c.pro_cab_numdocumento = '$documento' ";

		if($tdocu != 'TODOS')
		$sql .= "	AND c.pro_cab_tipdocumento = '$tdocu' ";

		if($tmoneda != 'TODOS')
		$sql .= "	AND c.pro_cab_moneda = '$tmoneda' ";

		$sql .= "
			ORDER BY
				TO_CHAR(c.pro_cab_fechaemision, 'DD/MM/YYYY'),
				LPAD(CAST(c.pro_cab_numreg AS bpchar),10,'0')";

		$resultado_1 	= $sqlca->query($sql);
		$numrows 		= $sqlca->numrows();
	
		if($pp && $pagina)
			$paginador = new paginador($numrows, $pp, $pagina);
		else
			$paginador = new paginador($numrows, 100, 0);
	
		$listado2['partir'] 			= $paginador->partir();
		$listado2['fin'] 				= $paginador->fin();
		$listado2['numero_paginas'] 	= $paginador->numero_paginas();
		$listado2['pagina_previa'] 		= $paginador->pagina_previa();
		$listado2['pagina_siguiente'] 	= $paginador->pagina_siguiente();
		$listado2['pp'] 				= $paginador->pp;
		$listado2['paginas'] 			= $paginador->paginas();
		$listado2['primera_pagina'] 	= $paginador->primera_pagina();
		$listado2['ultima_pagina'] 		= $paginador->ultima_pagina();

		if ($pp > 0) {
			$sql .= "
				LIMIT " . pg_escape_string($pp) . " ";
		}

		if ($pagina > 0) {
			$iPartir = ($paginador->partir() < 0 ? 0 : $paginador->partir());
			$sql .= "
				OFFSET " . pg_escape_string($iPartir);
		}

		// echo "<pre>";
		// echo $sql;
		// echo "</pre>";

		if ($sqlca->query($sql) < 0)
			return false;
	    
    	$listado[] = array();
		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$resultado[$i]['fregistro']		= $a[0];
			$resultado[$i]['femision']		= $a[1];
			$resultado[$i]['cc']			= $a[2];
			$resultado[$i]['documento'] 	= $a[3];
			$resultado[$i]['proveedor'] 	= $a[4];
			$resultado[$i]['moneda'] 		= $a[5];
			$resultado[$i]['impuesto'] 		= $a[6];
			$resultado[$i]['total'] 		= $a[7];
			$resultado[$i]['saldo'] 		= $a[8];
			$resultado[$i]['rubro'] 		= $a[9];
			$resultado[$i]['fvencimiento']	= $a[10];
			$resultado[$i]['eliminar']		= $a[11];
			$resultado[$i]['perce']			= $a[13];
			$resultado[$i]['tc']			= $a[14];
			$resultado[$i]['inafecto']		= $a[15];
			$resultado[$i]['imponible']		= $a[16];
			$resultado[$i]['doctype']		= $a[17];
			$resultado[$i]['corre']			= $a[18];
			$resultado[$i]['validanc']		= $a[19];
			$resultado[$i]['totald']		= $a[20];
			$resultado[$i]['saldod']		= $a[21];
			$resultado[$i]['nu_almacen']	= $a[22];
		}
		
		$listado['datos']      = $resultado;        
		$listado['paginacion'] = $listado2;

		return $listado;
  	}

	function PaginacionPDF($fecha, $fecha2, $estacion, $proveedor, $documento, $tdocu, $type_ple){
		global $sqlca;

		if($type_ple == 'RCS')
			$ple = "AND c.pro_cab_tipdocumento NOT IN ('02', '11', '20', '91')";
		else if($type_ple == 'RCD')//Registro de Compras No Domiciliado
			$ple = "AND c.pro_cab_tipdocumento IN ('91')";
		else
			$ple = "AND c.pro_cab_tipdocumento NOT IN ('02', '91')";

		$query = "
			SELECT DISTINCT
				LPAD(CAST(c.pro_cab_numreg AS bpchar),10,'0') corre,
				to_char(c.pro_cab_fechaemision, 'DD/MM/YYYY') femision,
				CASE WHEN
					gen2.tab_car_03 = '14' THEN to_char(c.pro_cab_fechavencimiento, 'DD/MM/YYYY') 
				ELSE
					''
				END as fvencimiento,
				gen2.tab_car_03 tipo,
				LPAD(CAST(c.pro_cab_seriedocumento AS bpchar), 4, '0') serie,
				'' dsi,
				c.pro_cab_numdocumento numero,
				(CASE WHEN
					gen2.tab_car_03 IN ('01','07','08') THEN '6'
				ELSE
					''
				END) AS identidad,
				(CASE WHEN p.pro_ruc IS NULL THEN c.pro_codigo ELSE p.pro_ruc END) AS ruc,
				p.pro_razsocial AS razonsocial,
				CASE WHEN
					gen2.tab_car_03 in ('07') THEN --TF-0000005690: se quito el 08
					(CASE WHEN c.pro_cab_moneda = '01' OR c.pro_cab_moneda = '1' THEN -c.pro_cab_impafecto ELSE ROUND((-c.pro_cab_impafecto * c.pro_cab_tcambio), 2) END)
				ELSE
					(CASE WHEN c.pro_cab_moneda = '01' OR c.pro_cab_moneda = '1' THEN c.pro_cab_impafecto ELSE ROUND((c.pro_cab_impafecto * c.pro_cab_tcambio), 2) END)
				END AS imponible,
				CASE WHEN
					gen2.tab_car_03 in ('07') THEN --TF-0000005690: se quito el 08
					(CASE WHEN c.pro_cab_moneda = '01' OR c.pro_cab_moneda = '1' THEN -c.pro_cab_impto1 ELSE ROUND((-c.pro_cab_impto1 * c.pro_cab_tcambio), 2) END)
				ELSE
					(CASE WHEN c.pro_cab_moneda = '01' OR c.pro_cab_moneda = '1' THEN c.pro_cab_impto1 ELSE ROUND((c.pro_cab_impto1 * c.pro_cab_tcambio), 2) END)
				END AS impuesto,
				CASE WHEN
					gen2.tab_car_03 in ('07') THEN --TF-0000005690: se quito el 08
					(CASE WHEN c.pro_cab_moneda = '01' OR c.pro_cab_moneda = '1' THEN -c.pro_cab_imptotal ELSE ROUND((-c.pro_cab_imptotal * c.pro_cab_tcambio), 2) END)
				ELSE
					(CASE WHEN c.pro_cab_moneda = '01' OR c.pro_cab_moneda = '1' THEN c.pro_cab_imptotal ELSE ROUND((c.pro_cab_imptotal * c.pro_cab_tcambio), 2) END)
				END AS total,
				c.regc_sunat_percepcion perce,
				ROUND(c.pro_cab_tcambio,3) tc,
				gen.tab_car_03 tiporef,
				substr(c.pro_cab_numdocreferencia,1,4) serieref, 
				substr(c.pro_cab_numdocreferencia,5,8) docuref,
				to_char(c.pro_cab_fecharegistro, 'DD/MM/YYYY') fregistro,
				c.pro_cab_impinafecto inafecto,
				rubro.ch_tipo_item rubro,
				to_char(c.pro_cab_fecharegistro, 'DD/MM/YYYY') fperiodo,
				c.pro_cab_numreg id,
				(SELECT pay_number FROM c_cash_transaction_payment WHERE c_cash_mpayment_id = '8' AND c_cash_transaction_id IN (SELECT c_cash_transaction_id FROM c_cash_transaction_detail WHERE doc_type = d.pro_cab_tipdocumento AND doc_serial_number = d.pro_cab_seriedocumento AND doc_number = d.pro_cab_numdocumento) LIMIT 1) dnumero,
				(SELECT created FROM c_cash_transaction_payment WHERE c_cash_mpayment_id = '8' AND c_cash_transaction_id IN (SELECT c_cash_transaction_id FROM c_cash_transaction_detail WHERE doc_type = d.pro_cab_tipdocumento AND doc_serial_number = d.pro_cab_seriedocumento AND doc_number = d.pro_cab_numdocumento) LIMIT 1) dfecha,
				(CASE WHEN
					c.pro_cab_fechareferencia IS NULL
				THEN
					(SELECT to_char(nc.pro_cab_fechaemision, 'DD/MM/YYYY') FROM cpag_ta_cabecera nc WHERE nc.pro_codigo = c.pro_codigo AND nc.pro_cab_tipdocumento = c.pro_cab_tipdocreferencia AND nc.pro_cab_seriedocumento||nc.pro_cab_numdocumento = c.pro_cab_numdocreferencia)
				ELSE
					to_char(c.pro_cab_fechareferencia, 'DD/MM/YYYY')
				END
				) fecharef,
				(CASE WHEN c.pro_cab_moneda = '01' THEN 'PEN' ELSE 'USD' END) moneda,
				c.pro_cab_tipdocumento||c.pro_cab_seriedocumento||c.pro_cab_numdocumento idcompra,
				rubro.plc_codigo as codigo_bienes_servicios --TABLA 30: CLASIFICACIÓN DE LOS BIENES Y SERVICIOS ADQUIRIDOS
			FROM
				cpag_ta_cabecera c
				INNER JOIN cpag_ta_detalle d ON (c.pro_cab_tipdocumento = d.pro_cab_tipdocumento AND c.pro_cab_seriedocumento = d.pro_cab_seriedocumento AND c.pro_cab_numdocumento = d.pro_cab_numdocumento AND c.pro_codigo = d.pro_codigo)
				LEFT JOIN int_proveedores p ON (c.pro_codigo = p.pro_codigo)
				LEFT JOIN inv_ta_almacenes a ON(c.pro_cab_almacen = a.ch_almacen)
				LEFT JOIN int_tabla_general as gen ON((CASE WHEN c.pro_cab_tipdocumento = '20' THEN c.pro_cab_tipdocreferencia = substring(TRIM(tab_elemento) for 2 from length(TRIM(tab_elemento))-1) END) and tab_tabla ='08')
				LEFT JOIN int_tabla_general as gen2 ON((c.pro_cab_tipdocumento = substring(TRIM(gen2.tab_elemento) for 2 from length(TRIM(gen2.tab_elemento))-1) and gen2.tab_tabla ='08'))
				LEFT JOIN cpag_ta_rubros rubro ON(rubro.ch_codigo_rubro = c.pro_cab_rubrodoc)
		";
		
		if($fecha != ''){
		$query.="
			WHERE
				c.pro_cab_fecharegistro::DATE BETWEEN to_date('$fecha','DD/MM/YYYY') AND to_date('$fecha2','DD/MM/YYYY')
				$ple
			";
		}

		if($estacion != '')
		$query .= "	AND c.pro_cab_almacen = '$estacion' ";

		if($proveedor != '')
		$query .= "	AND c.pro_codigo = '$proveedor' ";
		
		if($documento != '')
		$query .= "	AND c.pro_cab_numdocumento = '$documento' ";

		if($tdocu != 'TODOS')
		$query .= "	AND c.pro_cab_tipdocumento = '$tdocu' ";

		$query .= "
			ORDER BY
				LPAD(CAST(c.pro_cab_numreg AS bpchar),10,'0'),
				to_char(c.pro_cab_fechaemision, 'DD/MM/YYYY') DESC; ";
/*
		echo "<pre>";
		echo $query;
		echo "</pre>";
*/
		if ($sqlca->query($query) < 0)
			return false;

		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$resultado[$i]['corre']			= $a[0];
			$resultado[$i]['femision']		= $a[1];
			$resultado[$i]['fvencimiento']	= $a[2];
			$resultado[$i]['tipo']			= $a[3];
			$resultado[$i]['serie'] 		= $a[4];
			$resultado[$i]['dsi'] 			= $a[5];
			$resultado[$i]['numero'] 		= $a[6];
			$resultado[$i]['identidad'] 	= $a[7];
			$resultado[$i]['ruc'] 			= $a[8];
			$resultado[$i]['razonsocial'] 	= $a[9];
			$resultado[$i]['imponible'] 	= $a[10];
			$resultado[$i]['impuesto']		= $a[11];
			$resultado[$i]['total']			= $a[12];
			$resultado[$i]['perce']			= $a[13];
			$resultado[$i]['tc']			= $a[14];
			$resultado[$i]['tiporef']		= $a[15];
			$resultado[$i]['serieref']		= $a[16];
			$resultado[$i]['docuref']		= $a[17];
			$resultado[$i]['fregistro']		= $a[18];
			$resultado[$i]['inafecto']		= $a[19];
			$resultado[$i]['rubro']			= $a[20];
			$resultado[$i]['fperiodo']		= $a[21];
			$resultado[$i]['id']			= $a[22];
			$resultado[$i]['dnumero']		= $a[23];
			$resultado[$i]['dfecha']		= $a[24];
			$resultado[$i]['fecharef']		= $a[25];
			$resultado[$i]['moneda']		= $a[26];
			$resultado[$i]['idcompra']		= $a[27];
			$resultado[$i]['codigo_bienes_servicios'] = ($a[28] == "" || $a[28] == NULL) ? 1 : $a[28];
		}
		
		return $resultado;
  	}

	function ComprasDevolucion($proveedor){
		global $sqlca;

		$query="
				SELECT
					cab.tran_codigo AS tipo,
					to_char(cab.mov_fecha,'dd/mm/yyyy') AS fecha,
					cab.mov_numero movimiento,
					cab.com_num_compra compra,
					art.art_codigo||' - '||art.art_descripcion producto,
					cab.mov_cantidad cantidad,
					cab.mov_costounitario costo,
					cab.mov_costototal total,
					cab.tran_codigo||cab.mov_numero||to_char(cab.mov_fecha,'dd/mm/yyyy')||cab.art_codigo as id,
					cab.mov_fecha
				FROM
					inv_ta_compras_devoluciones cab
					LEFT JOIN int_proveedores pro ON(cab.mov_entidad = pro.pro_codigo)
					LEFT JOIN int_articulos art ON(cab.art_codigo = art.art_codigo)
				WHERE
					cab.mov_entidad = '$proveedor'
					AND (cab.cpag_tipo_pago IS NULL OR cab.cpag_serie_pago IS NULL OR cab.cpag_num_pago IS NULL)
				ORDER BY
					cab.mov_fecha DESC;
			";

		//echo $query;

		if ($sqlca->query($query) < 0)
			return false;
	    
		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$resultado[$i]['tipo']		= $a[0];
			$resultado[$i]['fecha']		= $a[1];
			$resultado[$i]['movimiento']	= $a[2];
			$resultado[$i]['compra'] 	= $a[3];
			$resultado[$i]['producto'] 	= $a[4];
			$resultado[$i]['cantidad'] 	= $a[5];
			$resultado[$i]['costo'] 	= $a[6];
			$resultado[$i]['total'] 	= $a[7];
			$resultado[$i]['id']		= $a[8];
		}

		return $resultado;

	}

	function AgregarComprasCabecera($estacion,$femision,$proveedor,$rubro,$tipo,$serie,$documento,$dvec,$fvencimiento,$tc,$moneda,$base,$impuesto,$total,$percepcion,$tiporef,$serieref,$documentoref,$rubros,$inafecto,$fperiodo, $getcorrelativo, $txt_glosa){
		global $sqlca;

		settype($base,"double");
		settype($impuesto,"double");
		settype($total,"double");
		settype($percepcion,"double");
		settype($inafecto,"double");

		$numeroref = $serieref . $documentoref;

		$validar = RegistroComprasModel::ValidacionCompras($tipo,$serie,$documento,$proveedor);

		if($tipo != '91'){//Comprobantes no domicialiados no suma el inafecto
			if($percepcion > 0 && $inafecto > 0)
				$saldo = ($base + $impuesto) + $percepcion + $inafecto;
			elseif($percepcion > 0)
				$saldo = ($base + $impuesto) + $percepcion;
			elseif($inafecto > 0)
				$saldo = ($base + $impuesto) + $inafecto;
			else
				$saldo = ($base + $impuesto);
		}else{
			$base = 0.00;
			$impuesto = 0.00;
			$saldo = ($base + $impuesto) + $inafecto;
			$total = ($base + $impuesto);
		}

		if($validar == 1){
			$query="
			INSERT INTO cpag_ta_cabecera(
				pro_cab_tipdocumento,
				pro_cab_seriedocumento,
				pro_cab_numdocumento,
				pro_codigo,
				pro_cab_fechaemision,
				pro_cab_fecharegistro,
				pro_cab_fechavencimiento, 
				pro_cab_dias_vencimiento, 
				pro_cab_tipcontable,
				plc_codigo,
				pro_cab_moneda,
				pro_cab_tcambio,
				pro_cab_imptotal,
				pro_cab_impsaldo,
				pro_cab_fechasaldo,
				pro_cab_almacen,
				pro_cab_impafecto,
				pro_cab_impto1,
				pro_cab_rubrodoc,
				regc_sunat_percepcion,
				pro_cab_tipdocreferencia,
				pro_cab_numdocreferencia,
				pro_cab_impinafecto,
				fecha_replicacion,
				pro_cab_numreg,
				pro_cab_glosa
			) VALUES (
				'$tipo',
				'$serie',
				'$documento',
				'$proveedor',
				'$femision',
				'$fperiodo',
				to_date('$fvencimiento','dd/mm/yyyy'),
				'$dvec',
				UTIL_FN_TIPO_ACCION_CONTABLE('CP','$tipo'),
				'42101',
				'$moneda',
				$tc,
				$total,
				$saldo,
				now(),
				'$estacion',
				$base,
				$impuesto,
				'$rubros',
				$percepcion,
				'$tiporef',
				'$numeroref',
				$inafecto,
				now(),
				$getcorrelativo,
				'".$txt_glosa."'
			);
			";
			if($sqlca->query($query) < 0){
				return false;
			}else{
				return 'ingreso';
			}
		}else{
			return 'existe';
		}
	}

	function AgregarComprasDetalle($estacion,$femision,$proveedor,$rubro,$tipo,$serie,$documento,$dvec,$fvencimiento,$tc,$moneda,$base,$impuesto,$total,$percepcion,$tiporef,$serieref,$documentoref){
		global $sqlca;

		settype($total,"double");

		$numeroref = $serieref.$documentoref;

		$querydet = "
		INSERT INTO cpag_ta_detalle (
			pro_cab_tipdocumento,
			pro_cab_seriedocumento,
			pro_cab_numdocumento,
			pro_codigo,
			pro_det_identidad,
			pro_det_tipmovimiento,
			pro_det_fechamovimiento,
			pro_det_moneda,
			pro_det_tcambio,
			pro_det_impmovimiento,
			pro_det_grupoc,
			pro_det_almacen,
			pro_det_tipdocreferencia,
			pro_det_numdocreferencia
        )VALUES(
			'$tipo', 
			'$serie',
			'$documento',
			'$proveedor',
			'001',
			'1',
			to_date('$femision','dd/mm/yyyy'),
			'$moneda',
			'$tc',
			$total,
			null,
			'$estacion',
			'$tiporef',
			'$numeroref'
		);
		";
		if($sqlca->query($querydet) < 0)
			return false;
		else
			return true;
	}

	function recuperarRegistroArray($documento){
	  	global $sqlca;
		
		$registro = array();
		$query = "
		SELECT
			to_char(c.pro_cab_fechaemision, 'DD/MM/YYYY') fregistro,
			to_char(c.pro_cab_fecharegistro, 'DD/MM/YYYY') fperiodo,
			gen.tab_desc_breve||' '||c.pro_cab_seriedocumento||' - '||c.pro_cab_numdocumento documento, 
			p.pro_codigo||' '||p.pro_razsocial proveedor,
			c.pro_cab_moneda moneda,
			c.pro_cab_imptotal total,
			c.pro_cab_impafecto imponible,
			c.pro_cab_impto1 impuesto,
			c.regc_sunat_percepcion perce,
			c.pro_cab_impinafecto inafecto,
			c.pro_cab_tipdocreferencia tiporef,
			substr(c.pro_cab_numdocreferencia,1,4) serieref, 
			substr(c.pro_cab_numdocreferencia,5,8) docuref,
			LPAD(CAST(c.pro_cab_numreg AS bpchar),10,'0') correlativo,
			to_char(c.pro_cab_fechareferencia, 'DD/MM/YYYY') freferencia
		FROM
			cpag_ta_cabecera c
			LEFT JOIN int_proveedores p ON (c.pro_codigo = p.pro_codigo)
			LEFT JOIN inv_ta_almacenes a ON(c.pro_cab_almacen = a.ch_almacen)
			LEFT JOIN int_tabla_general as gen ON(c.pro_cab_tipdocumento = substring(TRIM(tab_elemento) for 2 from length(TRIM(tab_elemento))-1) and tab_tabla ='08')
		WHERE
			c.pro_cab_tipdocumento||' '||c.pro_cab_seriedocumento||' - '||c.pro_cab_numdocumento|| ' - ' ||c.pro_codigo ~ '$documento';
		";

		$sqlca->query($query);
		while( $reg = $sqlca->fetchRow())
			$registro = $reg;
		return $registro;
	}

	function ModificarRegistroCompras($fecha, $documento, $moneda, $imponible, $impuesto, $total, $perce, $inafecto, $tiporef, $serieref, $documentoref, $freferencia, $fecha2){
		global $sqlca;

		settype($base,"double");
		settype($impuesto,"double");
		settype($total,"double");
		settype($perce,"double");
		settype($inafecto,"double");

		$fecha = substr($fecha,6,4)."-".substr($fecha,3,2)."-".substr($fecha,0,2);

		if($perce > 0 && $inafecto > 0)
			$saldo = ($imponible + $impuesto) + $perce + $inafecto;
		elseif($perce > 0)
			$saldo = ($imponible + $impuesto) + $perce;
		elseif($inafecto > 0)
			$saldo = ($imponible + $impuesto) + $inafecto;
		else
			$saldo = ($imponible + $impuesto);

		$numeroref = $serieref . $documentoref;

		if(!empty($freferencia))
			$cond = ", pro_cab_fechareferencia = '$freferencia'";

		$upt = "
		UPDATE
			cpag_ta_cabecera
		SET
			pro_cab_fechaemision	= '$fecha',
			pro_cab_fecharegistro	= '$fecha2',
			pro_cab_moneda			= '$moneda',
			pro_cab_impafecto		= " . $imponible . ",
			pro_cab_impto1			= " . $impuesto . ",
			pro_cab_imptotal		= " . $total . ",
			regc_sunat_percepcion	= " . $perce . ",
			pro_cab_impsaldo		= " . $saldo . ",
			pro_cab_impinafecto		= " . $inafecto . ",
			pro_cab_numdocreferencia = '$numeroref',
			pro_cab_tipdocreferencia = '$tiporef'
			$cond
		WHERE
			pro_cab_tipdocumento||' '||pro_cab_seriedocumento||' - '||pro_cab_numdocumento|| ' - ' ||pro_codigo ~ '$documento';
		";

		echo '<pre>';
		var_dump($_POST);
		echo '</pre>';
		echo '<pre>';
		var_dump($upt);
		echo '</pre>';

		if($sqlca->query($upt) < 0)
			return false;
		else
			return true;
	}

	function ModificarRegistroComprasDet($documento,$moneda){
		global $sqlca;

		$upt = "
			UPDATE
				cpag_ta_detalle
			SET
				pro_det_moneda = '$moneda'
			WHERE
				pro_cab_tipdocumento||' '||pro_cab_seriedocumento||' - '||pro_cab_numdocumento|| ' - ' ||pro_codigo ~ '$documento';
			";

//		echo $upt;

		if($sqlca->query($upt) < 0)
			return false;
		else
			return true;

	}

	function ActualizarCompra($proveedor, $tipo, $serie, $documento, $ip ,$id){
		global $sqlca;

		$id = str_replace(",","','",$id);

		$update="
			UPDATE
				inv_ta_compras_devoluciones
			SET
				cpag_tipo_pago 		= '$tipo',
				cpag_serie_pago 	= '$serie',
				cpag_num_pago 		= '$documento',
				mov_fecha_actualizacion = now(),
				ip_addr 		= '$ip'
			WHERE
				mov_entidad = '$proveedor'
		";
				
		if($id != '')
		$update.="	AND tran_codigo||mov_numero||to_char(mov_fecha,'dd/mm/yyyy')||art_codigo IN('$id') ";
	
		echo $update;

		if($sqlca->query($update) < 0)
			return false;
		else
			return true;

	}

	function BuscarCorrelativo($dateact) {
		global $sqlca;

		$year 	= substr($dateact,6,4);
		$month	= substr($dateact,3,2);

		$dateact = $year."-".$month;

		$sql = "
			SELECT
				numerator
			FROM
				act_preseq
			WHERE
				dateact = '$dateact'
			ORDER BY
				numerator
			LIMIT 1;
		";

		if ($sqlca->query($sql) < 0)
			return false;

		$pre = $sqlca->fetchRow();

		if ($pre[0] != NULL)
			$result = $pre[0];
		else {
			
			$sql = "
				SELECT
					numerator + 1
				FROM
					act_day
				WHERE
					dateact = '$dateact'
				ORDER BY
					numerator
				LIMIT 1;
			";

			//echo $sql;

			if ($sqlca->query($sql) < 0)
				return false;

			$day = $sqlca->fetchRow();

			if ($day[0] != NULL)
				$result = $day[0];
			else
				$result = 1;
		}

	        return $result;

	}

	function ActDayCorrelativo($dateact){
		global $sqlca;

		$year 	= substr($dateact,6,4);
		$month	= substr($dateact,3,2);

		$dateact = $year."-".$month;

		$sql	= "SELECT numerator FROM act_preseq WHERE dateact = '$dateact' ORDER BY numerator LIMIT 1;";

		$sqlca->query($sql);

		$rowpre = $sqlca->fetchRow();

		$sql	= "SELECT numerator FROM act_day WHERE dateact = '$dateact' ORDER BY numerator LIMIT 1;";

		$sqlca->query($sql);

		$rowact = $sqlca->fetchRow();

		if($rowpre[0] != NULL && $rowact[0] != NULL){

			if($rowpre[0] > $rowact[0]){//TABLE PRESEQ Y ACTDAY

				$upd = "UPDATE act_day SET numerator = $rowpre[0] WHERE dateact = '$dateact';";

				echo "Update Day: \n".$upd;

				if($sqlca->query($upd) < 0)
					return false;

				$del = "DELETE FROM act_preseq WHERE dateact = '$dateact' AND numerator = $rowpre[0] RETURNING numerator";

				if($sqlca->query($del) < 0)
					return false;

				$getnumerator = $sqlca->fetchRow();

			}else{

				$del = "DELETE FROM act_preseq WHERE dateact = '$dateact' AND numerator = $rowpre[0] RETURNING numerator";

				if($sqlca->query($del) < 0)
					return false;

				$getnumerator = $sqlca->fetchRow();
				
			}

		} elseif($rowpre[0] != NULL && $rowact[0] == NULL){

			$ins = "INSERT INTO act_day (dateact,numerator) VALUES('$dateact', $rowpre[0]);";

			if($sqlca->query($ins) < 0)
				return false;

			$del = "DELETE FROM act_preseq WHERE dateact = '$dateact' AND numerator = $rowpre[0] RETURNING numerator";

			if($sqlca->query($del) < 0)
				return false;

			$getnumerator = $sqlca->fetchRow();

		} elseif($rowpre[0] == NULL && $rowact[0] != NULL){

			$upd = "UPDATE act_day SET numerator = numerator + 1 WHERE dateact = '$dateact' RETURNING numerator;";

			if($sqlca->query($upd) < 0)
				return false;

			$getnumerator = $sqlca->fetchRow();

		}else{

			$ins = "INSERT INTO act_day VALUES('$dateact', 1) RETURNING numerator;";

			if($sqlca->query($ins) < 0)
				return false;

			$getnumerator = $sqlca->fetchRow();

		}

		return array(true, $getnumerator[0]);

	}

	function ActCorrelativoPre($dateact, $numerator){
		global $sqlca;

		$year 		= substr($dateact,6,4);
		$month		= substr($dateact,3,2);
		$dateact 	= $year."-".$month;

		$sql = "INSERT INTO act_preseq VALUES('" . $dateact . "', " . $numerator. ");";

		echo "Insertar\n: ".$sql;

		if($sqlca->query($sql) < 0)
			return false;

		return true;

	}

	function ActualizarRegistroComprasDev($documento){
		global $sqlca;

		$del = "
			UPDATE
				inv_ta_compras_devoluciones
			SET
				cpag_tipo_pago = NULL,
				cpag_serie_pago = NULL,
				cpag_num_pago = NULL
			WHERE
				cpag_tipo_pago||' '||cpag_serie_pago||' - '||cpag_num_pago|| ' - ' ||mov_entidad ~ '$documento';
			";

		echo $del;

		if($sqlca->query($del) < 0)
			return false;
		else
			return true;

	}
	
	function EliminarRegistroComprasDet($documento){
		global $sqlca;

		$del = "
			DELETE FROM cpag_ta_detalle WHERE pro_cab_tipdocumento||' '||pro_cab_seriedocumento||' - '||pro_cab_numdocumento|| ' - ' ||pro_codigo ~ '$documento';
			";

		echo $del;

		if($sqlca->query($del) < 0)
			return false;
		else
			return true;

	}

	function EliminarRegistroComprasCab($documento){
		global $sqlca;

		$del = "
			DELETE FROM cpag_ta_cabecera WHERE pro_cab_tipdocumento||' '||pro_cab_seriedocumento||' - '||pro_cab_numdocumento|| ' - ' ||pro_codigo ~ '$documento';
			";

		echo $del;

		if($sqlca->query($del) < 0)
			return false;
		else
			return true;

	}

	function TipoMoneda(){
		global $sqlca;

		$curre="
			SELECT
				substr(tab_elemento,5) currency,
				tab_descripcion || ' ' || tab_desc_breve mone
			FROM
				int_tabla_general
			WHERE
				tab_tabla = '04'
				AND tab_elemento != '000000'
			ORDER BY
				1;
			";

		if($sqlca->query($curre) < 0)
			return false;

		$resultado = array();
	
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    	$a = $sqlca->fetchRow();
		    	$resultado[$a[0]] = $a[1];
		}
		
		return $resultado;
	}

	function Rubros(){
		global $sqlca;

		$rubro = "
			SELECT
				ch_codigo_rubro,
				ch_descripcion,
				ch_tipo_item
			FROM
				cpag_ta_rubros
			ORDER BY
				1;
			";

		if($sqlca->query($rubro) < 0)
			return false;

		$resultado = array();
	
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    	$a = $sqlca->fetchRow();
			if(empty($a[2]))
				$resultado[$a[2].$a[0]] = $a[0] . " - " .$a[1];
			else
				$resultado[$a[2]] = $a[0] . " - " .$a[1];
		}
		
		return $resultado;

	}

	function Documentos(){
		global $sqlca;

		$documentos = "
				SELECT
					SUBSTR(trim(tab_elemento),5,2),
					tab_descripcion
				FROM
					int_tabla_general
				WHERE
					tab_tabla = '08'
					AND tab_elemento<>'000000'
					AND tab_car_03 != ''
				ORDER BY
					tab_elemento; 
				";

		if($sqlca->query($documentos) < 0)
			return false;

		$resultado = array();
	
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    	$a = $sqlca->fetchRow();
		    	$resultado[$a[0]] = $a[0] . " - " .$a[1];
		}
		
		return $resultado;
	
	}

	function DocumentosRef(){
		global $sqlca;

		$documentos = "
				SELECT
					SUBSTR(trim(tab_elemento),5,2),
					tab_descripcion
				FROM
					int_tabla_general
				WHERE
					tab_tabla = '08'
					AND tab_elemento<>'000000'
				ORDER BY
					tab_elemento; 
				";

		if($sqlca->query($documentos) < 0)
			return false;

		$resultado = array();
	
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    	$a = $sqlca->fetchRow();
		    	$resultado[$a[0]] = $a[0] . " - " .$a[1];
		}
		
		return $resultado;
	
	}

	function Limite(){
		global $sqlca;

		$limit = "
				SELECT
					par_valor valor
				FROM
					int_parametros
				WHERE
					par_nombre = 'limite_cpag';
				";

		if($sqlca->query($limit) < 0)
			return false;

		$resultado = array();
	
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    	$a = $sqlca->fetchRow();
		    	$resultado['limite'] = $a[0];
		}
		
		return $resultado;
	
	}

	function Igv(){
		global $sqlca;

		$sql = "
		SELECT
			1 + ROUND(tab_num_01 / 100,2) igv
		FROM
			int_tabla_general
		WHERE
			TRIM(tab_tabla||tab_elemento) = (SELECT par_valor FROM int_parametros WHERE TRIM(par_nombre) = 'igv actual')
		";

		if($sqlca->query($sql) < 0)
			return false;

		$resultado = array();
	
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    	$a = $sqlca->fetchRow();
		    	$resultado['igv'] = $a[0];
		}
		
		return $resultado;
	
	}

	function FechaSistema(){
		global $sqlca;

		$fecha = "
			SELECT
				to_char(da_fecha,'DD/MM/YYYY')
			FROM
				pos_aprosys
			WHERE
				ch_poscd = 'A';
			";

		if($sqlca->query($fecha) < 0)
			return false;

		$resultado = array();
	
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    	$a = $sqlca->fetchRow();
		    	$resultado['fecha'] = $a[0];
		}
		
		return $resultado;

	}

	function ProveedorCBArray($condicion='') {
    		global $sqlca;
    		
    		$cbArray = array();
    		$query = "SELECT pro_codigo, pro_razsocial FROM int_proveedores ".
    		$query .= ($condicion!=''?' WHERE '.$condicion:'').' ORDER BY 2';

    		if ($sqlca->query($query)<=0)
      			return $cbArray;
      			
    		while($result = $sqlca->fetchRow()){
      			$cbArray[trim($result["pro_codigo"])] = $result["pro_razsocial"];
    		}

    		ksort($cbArray);
    		
    		return $cbArray;

  	}

	function ProveedorAdi($condicion='') {
    		global $sqlca;
    		
    		$cbArray = array();
    		$query = "SELECT pro_forma_pago, pro_grupo FROM int_proveedores ".
    		$query .= ($condicion!=''?' WHERE '.$condicion:'');

    		if ($sqlca->query($query)<=0)
      			return $cbArray;
      			
		$dias = array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$dias[$i]['num'] 	= $a[0];
			$dias[$i]['rubro'] 	= $a[1];
				
		}

		return $dias;
  	}

	function ProveedorDias($dias) {
    		global $sqlca;
    		
    		$cbArray = array();
    		$query = "
			SELECT
				cast(tab_num_01 as int) as dias 
			FROM 
				int_tabla_general 
			WHERE 
				tab_tabla = '96' 
				AND tab_elemento<>'000000'
				AND substring(tab_elemento for 2 from length(tab_elemento)-1 ) = '$dias';
			";

    		if ($sqlca->query($query)<=0)
      			return $cbArray;
      			
		$dias = array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$dias[$i]['dias'] = $a[0];
				
		}

    		return $dias;

  	}

	function ProveedorRubro($rubro) {
    		global $sqlca;
    		
    		$cbArray = array();
    		$query = "
			SELECT
				ch_codigo_rubro
			FROM
				cpag_ta_rubros
			WHERE
				ch_codigo_rubro = '$rubro';
			";

    		if ($sqlca->query($query)<=0)
      			return $cbArray;
      			
		$dias = array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$dias[$i]['rubro'] = $a[0];
				
		}

    		return $dias;

  	}

	function ValidacionCompras($tipo,$serie,$documento,$proveedor){
		global $sqlca;

		$sql = "
			SELECT
				count(*)
			FROM
				cpag_ta_cabecera
			WHERE
				pro_cab_tipdocumento 		= '$tipo' 
				AND pro_cab_seriedocumento 	= '$serie'
				AND pro_cab_numdocumento 	= '$documento'
				AND pro_codigo 			= '$proveedor';
			";

		echo $sql;

		if ($sqlca->query($sql) < 0) 
			return false;
	
		$a = $sqlca->fetchRow();	

		if($a[0]>=1) {
			return 0; // ya se ingreso ..!!!
		} else {
			return 1; // no se ingreso...!!!
		}
	}

	function BuscarRubros($rubro){
		global $sqlca;

		$rubro = "
			SELECT
				ch_codigo_rubro
			FROM
				cpag_ta_rubros
			WHERE
				ch_codigo_rubro||' - '||ch_descripcion = '$rubro'
			";

    		if ($sqlca->query($rubro)<=0)
      			return $cbArray;
      			
		$rubros = array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$rubros['rubro'] = $a[0];
				
		}

    		return $rubros;

	}

	function TipoCambio($fecha){
		global $sqlca;

		$anio = substr($fecha,6,4);
		$mes = substr($fecha,3,2);
		$dia = substr($fecha,0,2);

		$fecha = $anio."-".$mes."-"."$dia";

		$sql = "SELECT tca_venta_oficial tc FROM int_tipo_cambio WHERE tca_fecha = '$fecha' AND tca_moneda = '02'";

		echo $sql;

		if ($sqlca->query($sql) < 0)
			return false;

		$data = $sqlca->fetchRow();

		return $data['tc'];

	}

	function verifyConsolidacion($almacen, $femision) {
		global $sqlca;

		$anio 	= substr($femision,6,4);
		$mes 	= substr($femision,3,2);
		$dia 	= substr($femision,0,2);

		$fecha 	= $anio."-".$mes."-".$dia;
		
		$turno = 0;

		$sql = "SELECT validar_consolidacion('" . $fecha . "', " . $turno . ",'" . $almacen . "')";

		$sqlca->query($sql);

		$estado = $sqlca->fetchRow();

		if($estado[0] == 1)
			return false;//Consolidado

		return true;//No consolidado
	}

	// Verificar si en la opción de Combustibles -> Caja EGRESOS, se ha realizado un pago al documento de registro de compras
	// Si es true, entonces primero se debe de eliminar el pago y luego el documento.
	function verify_payments($arrDataGET){
		global $sqlca;

		$sql = "
SELECT
 COUNT(*) AS nu_existe
FROM
 cpag_ta_detalle
WHERE
 pro_det_almacen = '" . $arrDataGET["iAlmacen"] . "'
 AND pro_cab_tipdocumento||' '||pro_cab_seriedocumento||' - '||pro_cab_numdocumento||' - '||pro_codigo = '" . $arrDataGET["sDocumento"] . "'
 AND pro_det_tipmovimiento != '1';
		";

		$iStatus = $sqlca->query($sql);

		if ($iStatus < 0)
	    	$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'danger', 'sColor' => 'red', 'sMessage' => 'Problemas al ejecutar SQL - verify_payments()');
	   	else {
    		$row = $sqlca->fetchRow();
    		$iExisteDocumento = (int)$row["nu_existe"];
    		$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'success', 'sColor' => 'green', 'sMessage' => 'No existe pago de Caja EGRESOS');
		    if ($iExisteDocumento > 0)
		    	$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'warning', 'sColor' => 'orange', 'sMessage' => 'El documento presenta pagos / eliminaciones en la opción Caja EGRESOS');
		}

	    return $arrResponse;
	}
}
