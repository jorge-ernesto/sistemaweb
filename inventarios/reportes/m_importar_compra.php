<?php

ini_set("upload_max_filesize", "15M");
set_time_limit(0);

class TipodeCambioModel extends Model {

	function getIgv(){
		global $sqlca;

		$sql = "
		SELECT
			1 + ROUND(tab_num_01 / 100,2) igv
		FROM
			int_tabla_general
		WHERE
			TRIM(tab_tabla||tab_elemento) = (SELECT par_valor FROM int_parametros WHERE TRIM(par_nombre) = 'igv actual')
		";

		if($sqlca->query($sql) <= 0)
			throw new Exception("Error");

		$row = $sqlca->fetchRow();
		return $row['igv'];
	}

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

	function DatosServidorRemoto(){
		global $sqlca;
	
		$sql = "
		SELECT
			p1.par_valor,
			p2.par_valor,
			p3.par_valor,
			p4.par_valor
		FROM
			int_parametros p1
			LEFT JOIN int_parametros p2 ON p2.par_nombre = 'central_db'
			LEFT JOIN int_parametros p3 ON p3.par_nombre = 'central_user'
			LEFT JOIN int_parametros p4 ON p4.par_nombre = 'central_password'
		WHERE
			p1.par_nombre = 'central_ip';
		";

		if ($sqlca->query($sql) <= 0)
			return FALSE;

		if($sqlca->numrows()==1){
			$data = $sqlca->fetchRow();
			return Array(TRUE, $reg[0],$reg[1],$reg[2],$reg[3]);
		}
		return FALSE;// No existe
	}

	function CostoUnitarioServidorRemoto($host, $db, $user, $pass, $codigo){
		$coneccion	= pg_connect("host=".$host." port=5432 dbname=".$db." user=".$user." password=".$pass."");
		$xsql = pg_exec($coneccion,"SELECT rec_precio FROM com_rec_pre_proveedor WHERE art_codigo = '$codigo'");
		$costo_unitario = Array();

		if(pg_numrows($xsql) > 0)
			$costo_unitario = pg_result($xsql,0,0);

		return $costo_unitario;
	}

	function extension($archivo){
		$partes = explode(".", $archivo);
		$extension = end($partes);
		return $extension;
	}

	function Formularios() {
		global $sqlca;

		$sql = "
			SELECT
				tran_codigo,
				tran_descripcion
			FROM
				inv_tipotransa
			WHERE
				tran_naturaleza = '2'
			ORDER BY
				tran_codigo;
		";

		if($sqlca->query($sql, "_formularios") < 0)
			return null;

		$resultado = Array();

		for($i = 0; $i < $sqlca->numrows("_formularios"); $i++) {
			$array = $sqlca->fetchRow("_formularios");
			$resultado[$array[0]] = $array[0] . " - " . $array[1];
		}
        $resultado['TODOS'] = "Todos los formularios";
		return $resultado;
	}

	function obtenerEstaciones() {
		global $sqlca;

		$sql = "
			SELECT
				ch_almacen,
				ch_nombre_almacen
			FROM
				inv_ta_almacenes
			WHERE
				ch_clase_almacen = '1'
			ORDER BY
				ch_almacen;
		";

		if($sqlca->query($sql, "_estaciones") < 0)
			return null;

		$resultado = Array();

		for($i = 0; $i < $sqlca->numrows("_estaciones"); $i++) {
			$array = $sqlca->fetchRow("_estaciones");
			$resultado[$array[0]] = $array[0] . " - " . $array[1];
		}
        $resultado['TODAS'] = "Todas las estaciones";
		return $resultado;
	}

	function Paginacion($pp, $pagina, $estacion, $fecha, $fecha2, $formulario, $documento, $codigo, $nombre){
		global $sqlca;

        list($desde_dia, $desde_mes, $desde_ano) = sscanf($fecha, "%2s/%2s/%4s");
        list($hasta_dia, $hasta_mes, $hasta_ano) = sscanf($fecha2, "%2s/%2s/%4s");

		$sql = "
		SELECT
			m.mov_numero,
			to_char(m.mov_fecha,'dd/mm/yyyy hh24:mi') AS mov_fecha,
			m.com_num_compra,
			m.mov_tipdocuref || ' - ' ||m.mov_docurefe,
			m.mov_almaorigen,
			m.mov_almadestino,
			m.mov_almacen,
			m.mov_cantidad,
			m.mov_costounitario,
			m.mov_costototal,
			m.art_codigo || ' - ' ||a.art_descripcion,
			m.tran_codigo tf,
			f.tran_descripcion df
		FROM 
			inv_movialma AS m
			LEFT JOIN int_articulos AS a ON (m.art_codigo = a.art_codigo)
			LEFT JOIN inv_tipotransa AS f ON (m.tran_codigo = f.tran_codigo)
		WHERE
		";

		if($fecha != '')
		$sql .= "m.mov_fecha BETWEEN '" . pg_escape_string($desde_ano . "-" . $desde_mes . "-" . $desde_dia) . " 00:00:00' AND '" . pg_escape_string($hasta_ano . "-" . $hasta_mes . "-" . $hasta_dia) . " 23:59:59'";

   		if($estacion != "TODAS" && !empty($estacion))
       		$sql .= " AND m.mov_almacen='" . pg_escape_string($estacion) . "'";

		if($formulario != "TODOS" && !empty($formulario))
			$sql .= " AND m.tran_codigo='" . pg_escape_string($formulario) . "'";
		//else
		//	$sql .= " AND m.tran_codigo IN('01','21')";

		if(isset($documento) && $documento != '')
			$sql .= " AND m.mov_docurefe LIKE '%" . pg_escape_string($documento). "%'";

		if( (isset($codigo) && $codigo != '') && (isset($nombre) && $nombre != ''))
			$sql .= " AND m.art_codigo = '" . pg_escape_string($codigo) . "'";

		$sql .= "
			ORDER BY
				m.mov_fecha DESC
		";

		$resultado_1 = $sqlca->query($sql);
		$numrows = $sqlca->numrows();

		$paginador = new paginador($numrows,$pp, $pagina);
	
		$listado2['partir'] 			= $paginador->partir();
		$listado2['fin'] 				= $paginador->fin();
		$listado2['numero_paginas'] 	= $paginador->numero_paginas();
		$listado2['pagina_previa'] 		= $paginador->pagina_previa();
		$listado2['pagina_siguiente'] 	= $paginador->pagina_siguiente();
		$listado2['pp'] 				= $paginador->pp;
		$listado2['paginas'] 			= $paginador->paginas();
		$listado2['primera_pagina'] 	= $paginador->primera_pagina();
		$listado2['ultima_pagina'] 		= $paginador->ultima_pagina();

		$sql .= " LIMIT " . pg_escape_string($pp) . " ";
		$sql .= " OFFSET " . pg_escape_string($paginador->partir());

		if ($sqlca->query($sql) < 0)
			return false;
	    
    	$listado[] = array();
		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$resultado[$i]['formulario']		= $a[0];
			$resultado[$i]['fecha']			= $a[1];
			$resultado[$i]['orden_compra']	 	= $a[2];
			$resultado[$i]['documento']	 	= $a[3];
			$resultado[$i]['almacen_origen'] 	= $a[4];
			$resultado[$i]['almacen_destino'] 	= $a[5];
			$resultado[$i]['almacen'] 		= $a[6];
			$resultado[$i]['cantidad'] 		= $a[7];
			$resultado[$i]['costo_unitario'] 	= $a[8];
			$resultado[$i]['total']		 	= $a[9];
			$resultado[$i]['articulo'] 		= $a[10];
			$resultado[$i]['tipo_movimiento'] 	= $a[11];
			$resultado[$i]['des_movimiento'] 	= $a[12];
			
		}
		
		$sql = "COMMIT";
		$sqlca->query($sql);

		$listado['datos']      = $resultado;        
		$listado['paginacion'] = $listado2;

		return $listado;

  	}

	function NumeroOrden($tipo_docu, $serie, $accion){
		$q	= "SELECT UTIL_FN_CORRE_DOCS('" . trim($tipo_docu) ."', '" . trim($serie) ."', '" . trim($accion) ."')";
		$rs	= pg_exec($q);
		$A	= pg_fetch_array($rs,0);
		$n	= $A[0];
		return $n;
	}

	function completarCeros($cadena, $long_final, $complemento){
		$long_inicial = strlen($cadena);
		for($i = 0; $i < $long_final - $long_inicial; $i++)
			$cadena = $complemento.$cadena ;
		return $cadena;
	}

	function IncrementarCorrelativo($cod_transa){
		$rs	= pg_exec("SELECT UTIL_FN_CORRE_FORM('" . trim($cod_transa) ."', 'insert')");
		$A	= pg_fetch_array($rs,0);
		$r	= $A[0];
		return $r;
	}

	function ValidarCompra($proveedor, $tipo, $serie, $numero, $codigo){
		global $sqlca;

		$proveedor	= trim($proveedor);
		$tipo		= trim($tipo);
		$docu_refe	= trim($serie).trim($numero);
		$codigo		= trim($codigo);

		if(empty($codigo)){
			$sql = "
			SELECT
				count(*) existe
			FROM
				inv_movialma
			WHERE
				mov_entidad			= '" . $proveedor . "'
				AND mov_tipdocuref	= '" . $tipo . "'
				AND mov_docurefe	= '" . $docu_refe . "'
			";
		} else {
			$sql = "
			SELECT
				count(*) existe,
				(SELECT art_codigo FROM int_articulos WHERE art_codigo = (LPAD(CAST('" . $codigo . "' AS bpchar),13,'0'))) codigo,	
				(SELECT art_descripcion FROM int_articulos WHERE art_codigo = (LPAD(CAST('" . $codigo . "' AS bpchar),13,'0'))) descripcion,
				(SELECT
					count(*)
				FROM
					inv_movialma
				WHERE
					mov_entidad			= '" . $proveedor . "'
					AND mov_tipdocuref	= '" . $tipo . "'
					AND mov_docurefe	= '" . $docu_refe . "') compra,
				(SELECT
					count(*)
				FROM
					inv_movialma
				WHERE
					mov_entidad			= '" . $proveedor . "'
					AND mov_tipdocuref	= '" . $tipo . "'
					AND mov_docurefe	= '" . $docu_refe . "'
					AND art_codigo		= (LPAD(CAST('" . $codigo . "' AS bpchar),13,'0'))) compraproducto			
			FROM
				int_proveedores
			WHERE
				pro_codigo = '" . $proveedor . "'
			";
		}

		if ($sqlca->query($sql) <= 0)
			return FALSE;

		$data = Array();

		if ($sqlca->numrows()==1){
			$data = $sqlca->fetchRow();
			return array($data);
		}
	}

	function InsertarExcel($data, $almacen, $tf, $codproveedor, $fecha, $tipo, $serie, $numero, $usuario, $ip, $codmoneda, $base, $tc, $cuentaspagar){
		global $sqlca;

		//get IGV
		$Nu_IGV = TipodeCambioModel::getIgv();

		$ine 		= 0;
		$exi 		= 0;
		$nue 		= 0;
		$dupli 		= 0;
		$codigoexcel	= '';

		$docu_refe = $serie.$numero;

		$resultados	= count($data->sheets[0]['cells']);

		if ( trim($tf)!='18' && trim($tf)!='99' ) {
			$nro_orden	= TipodeCambioModel::NumeroOrden("01", $almacen, "insert");
			$nro_orden	= TipodeCambioModel::completarCeros($nro_orden, 8, "0");
		}

		$nro_mov	= TipodeCambioModel::IncrementarCorrelativo($tf);
		$nro_mov 	= TipodeCambioModel::completarCeros($nro_mov, 7, "0");
		$nro_mov 	= $almacen.$nro_mov;
		
		if ( trim($tf)!='18' && trim($tf)!='99' ) {
			$validar_comprac = TipodeCambioModel::OrdenCompraCabecera($codproveedor, $tf, $almacen, $nro_orden, $fecha, $codmoneda, $base, $tc, $percepcion);

			if($validar_comprac == 0)
				return false;
		}

		/* FECHA + HORA */
		$Fe_Emision_Movialma = "";
		$Fe_Emision_Locatime = localtime(time(),true); //Since 1900
		$Fe_Emision_Movialma = $fecha . " " . $Fe_Emision_Locatime["tm_hour"] . ":" . $Fe_Emision_Locatime["tm_min"] . ":" . $Fe_Emision_Locatime["tm_sec"];

		for ($i = 9; $i <= ($resultados + 1); $i++) {
			$codigo		= $data->sheets[0]['cells'][$i][2];
			$costo		= $data->sheets[0]['cells'][$i][4];
			$cantidad	= $data->sheets[0]['cells'][$i][5];

			$codigo		= trim($codigo);
			$costo		= trim($costo);
			$cantidad	= trim($cantidad);

			if (strlen($codigo) > 0 && strlen($costo) > 0 && strlen($cantidad) > 0){
				//OBTENER COSTOS UNITARIOS DEL SERVIDOR CENTRAL POR PRODUCTO
				/*$central = TipodeCambioModel::DatosServidorRemoto();

				if($central[0])
					$costo = TipodeCambioModel::CostoUnitarioServidorRemoto($central[1], $central[2], $central[3], $central[4], $codigo);*/

				//VALIDAR DOCUMENTO, PROVEEDOR Y PRODUCTOS DEL EXCEL
				$validar =  TipodeCambioModel::ValidarCompra($codproveedor, $tipo, $serie, $numero, $codigo);

				if($validar[0]['codigo'] == "0"){
					$ine++;//INEXISTENTES
				} elseif($validar[0]['compraproducto'] >= "1"){
					$exi++;//EXISTENTES
				} elseif ($codigoexcel == $codigo) {
					$dupli++;//DUPLICADOS CODIGO DE ARTICULOS DEL EXCEL
				} else {
					/* Datos para inserccion o actualizacion */
					$codigo 		= trim($validar[0]['codigo']);
					$codproveedor 	= trim($codproveedor);
					$tipo 			= trim($tipo);
					$serie 			= trim($serie);
					$numero 		= trim($numero);
					$codigo 		= trim($codigo);
					$costo 			= trim($costo);
					$usuario 		= trim($usuario);
					$ip 			= trim($ip);
					$codmoneda 		= trim($codmoneda);
					$costo_total	= round(($cantidad * $costo), 4);

					$sql = "
					INSERT INTO inv_movialma (
						tran_codigo,
						mov_almacen,
						mov_almaorigen,
						mov_almadestino,
						mov_entidad,
						mov_tipdocuref,
						mov_docurefe,
						art_codigo,
						mov_cantidad,
						mov_costounitario,
						mov_numero,
						mov_fecha,
						mov_naturaleza,
						com_tipo_compra,
						com_serie_compra,
						com_num_compra,
						mov_costototal,
						mov_usuario
					) VALUES (
						'" . $tf . "',
						'" . $almacen . "',
						(SELECT tran_origen FROM inv_tipotransa WHERE tran_codigo = '" . $tf . "'),
						(SELECT tran_destino FROM inv_tipotransa WHERE tran_codigo = '" . $tf . "'),
						'" . $codproveedor . "',
						substring(trim('" . $tipo . "') from char_length(trim('" . $tipo . "'))-1 for 2),
						'" . $docu_refe . "',
						'" . $codigo . "',
						" . $cantidad . ",
						" . $costo . ",
						'" . $nro_mov . "',
						'" . $Fe_Emision_Movialma . "',
						(SELECT tran_naturaleza FROM inv_tipotransa WHERE tran_codigo = '" . $tf . "'),
						'01',
						'" . $almacen . "',
						'" . $nro_orden . "',
						" . $costo_total . ",
						'" . $usuario . "'
					);
					";

					if ($sqlca->query($sql) < 0)
						return false;

					if ( trim($tf)!='18' ) {
						$validar_costo = TipodeCambioModel::CostoUnitario($costo,$codigo,$codproveedor, $usuario, $ip, $codmoneda);
						if($validar_costo == 0)
							return false;
					}


					if ( trim($tf)!='18' && trim($tf)!='99' ) {
						$validar_comprad = TipodeCambioModel::OrdenCompraDetalle($almacen, $codproveedor, $tf, $nro_orden, $fecha, $codigo, $costo, $cantidad, $Nu_IGV);
		
						if($validar_comprad == 0)
							return false;

						if ($cuentaspagar == 'true'){
							$validar_acompra = TipodeCambioModel::ActualizarCompra($codproveedor, $tipo, $serie, $numero, $ip, $tf, $nro_mov, $fecha, $codigo);
							if($validar_acompra == 0)
								return false;
						}
					}

					$nue++;//CANTIDAD DE PRODUCTOS INSERTADOS

				}
				$codigoexcel = $codigo;
			}
		}
		return array(true, $ine, $exi, $nue, $dupli);
	}

	function CostoUnitario($costo_unitario,$codigo,$codproveedor, $usuario, $ip, $codmoneda){
		global $sqlca;

		$validar = "
		SELECT DISTINCT
			CASE WHEN EXISTS (SELECT art_codigo FROM com_rec_pre_proveedor WHERE pro_codigo = '" . $codproveedor . "' AND art_codigo = '" . $codigo . "') THEN 'existe' ELSE 'nulo' END
		FROM
			com_rec_pre_proveedor;";

		if ($sqlca->query($validar) < 0)
			return 0;

		$data = $sqlca->fetchRow();

		$sql = "";

		if($data[0] == "existe"){
			$sql.= "
			UPDATE
				com_rec_pre_proveedor 
			SET
				rec_precio		= " . $costo_unitario . ",
				rec_usuario		= '" . $usuario . "',
				rec_ip			= '" . $ip . "',
				rec_fecha_ultima_compra = now()
			WHERE
				pro_codigo 		= '" . $codproveedor ."'
				AND art_codigo 	= '" . $codigo ."';
			";
			if ($sqlca->query($sql) < 0)
				return 0;
		} else {
			$sql.= "
			INSERT INTO
			com_rec_pre_proveedor(	
				pro_codigo,
				art_codigo, 
				rec_moneda,
				rec_precio,
				rec_descuento1,
				rec_fecha_precio,
				rec_fecha_ultima_compra,
				rec_usuario,
				rec_ip
			) VALUES (			
				'" . $codproveedor . "',
				'" . $codigo . "',
				'" . $codmoneda . "',
				" . $costo_unitario . ",
				0.00,
				now(),
				now(),
				'" . $usuario . "',
				'" . $ip . "'
			);";
			if ($sqlca->query($sql) < 0)
				return 0;
		}
		return true;
	}

	function OrdenCompraCabecera($codproveedor, $tf, $almacen, $nro_orden, $fecha, $codmoneda, $base, $tc, $percepcion){
		global $sqlca;

		settype($base,"double");
		settype($percepcion,"double");

		$sql="
		INSERT INTO com_cabecera(
			pro_codigo,
			num_tipdocumento,
			num_seriedocumento,
			com_cab_numorden,
			com_cab_almacen,
			com_cab_fechaorden,
			com_cab_fechaofrecida,
			com_cab_fecharecibida,
			com_cab_tipcambio,
			com_cab_credito,
			com_cab_formapago,
			com_cab_recargo1,
			com_cab_estado,
			com_cab_transmision,
			com_cab_moneda,
			percepcion
		)VALUES(
			'" . $codproveedor . "',
			'" . $tf . "',
			'" . $almacen . "',
			'" . $nro_orden . "',
			'" . $almacen . "',
			'" . $fecha . "',
			'" . $fecha . "',
			'" . $fecha . "',
			" . $tc . ",
			'N',
			'01',
			0,
			'2',
			't',
			'" . $codmoneda . "',
			" . $percepcion . "
		);
		";
		if ($sqlca->query($sql) > 0)
			return 0;
		return true;
	}

	function OrdenCompraDetalle($almacen, $codproveedor, $tf, $nro_orden, $fecha, $codigo, $costo_unitario, $cantidad, $Nu_IGV){
		global $sqlca;

		$Nu_Precio_Compra 	= ($Nu_IGV * $costo_unitario);
		$Nu_Total_Compra 	= ($cantidad * $Nu_Precio_Compra);
		$Nu_Impuesto_Compra = ($Nu_Total_Compra - ($Nu_Total_Compra / $Nu_IGV));

		$sql = "
		INSERT INTO com_detalle(
			pro_codigo,
			num_tipdocumento,
			num_seriedocumento,
			com_cab_numorden,
			art_codigo,
			com_det_fechaentrega,
			com_det_cantidadpedida,
			com_det_cantidadatendida,
			com_det_precio,
			com_det_imparticulo,
			com_det_descuento1,
			com_det_estado,
			com_det_cd_impuesto1,
			com_det_impuesto1
		) VALUES (
			'" . $codproveedor . "',
			'" . $tf . "',
			'" . $almacen . "',
			'" . $nro_orden . "',
			'" . $codigo . "',
			'" . $fecha . "',
			" . $cantidad . ",
			" . $cantidad . ",
			" . $Nu_Precio_Compra . ",
			" . $Nu_Total_Compra . ",
			0.00,
			'2',
			'0009',
			" . $Nu_Impuesto_Compra . "
		);
		";
		if ($sqlca->query($sql) < 0)
			return 0;	
		return true;
	}
	
	function validaDia($dia, $almacen) {
		global $sqlca;

		$turno = 0;
		$sql = "SELECT validar_consolidacion('" . $dia . "', " . $turno . ",'" . $almacen . "');";
		$sqlca->query($sql);

		$estado = $sqlca->fetchRow();

		if($estado[0] == 1)
			return 1;//Consolidado
		return 0;//No consolidado
	}
	
	function FechaSistema() {
		global $sqlca;

		$sql = "SELECT da_fecha AS fecha FROM pos_aprosys WHERE ch_poscd = 'A' ORDER BY da_fecha DESC LIMIT 1;";
		$sqlca->query($sql);
		$data = $sqlca->fetchRow();
		return $data['fecha'];
	}

	function ValidarProveedor($codproveedor){
		global $sqlca;
		
		$sql = "SELECT pro_razsocial, pro_forma_pago FROM int_proveedores WHERE pro_codigo = '" . $codproveedor . "';";
		$sqlca->query($sql);
		if($sqlca->numrows()==1){
			$data = $sqlca->fetchRow();
			return array($data[0], $data[1]);
		}
		return false;// No existe
	}

	function TipoCambio($fecha){
		global $sqlca;

		$sql = "SELECT tca_venta_oficial tc FROM int_tipo_cambio WHERE tca_fecha = '" . $fecha ."' AND tca_moneda = '02'";

		if ($sqlca->query($sql) < 0)
			return false;

		$data = $sqlca->fetchRow();
		return $data['tc'];
	}

	function Limite(){
		global $sqlca;

		$limit = "SELECT par_valor valor FROM int_parametros WHERE par_nombre = 'limite_cpag';";

		if($sqlca->query($limit) < 0)
			return FALSE;

		if ($sqlca->numrows() < 1)
			return FALSE;

		$resultado = array();
	
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
	    	$a = $sqlca->fetchRow();
	    	$resultado['limite'] = $a[0];
		}
		return $resultado;
	}

	function Rubros(){
		global $sqlca;

		$sql = "
		SELECT
			ch_codigo_rubro codigo,
			ch_descripcion nombre,
			ch_tipo_item item
		FROM
			cpag_ta_rubros
		WHERE
			ch_tipo_item != '' 
		ORDER BY
			1;
		";

		if($sqlca->query($sql) < 0)
			return false;

		$registro = Array();

		while($reg = $sqlca->fetchRow())
			$registro[] = $reg;
		return $registro;
	}

	function Almacenes($almacen) {
		global $sqlca;

		$sql = "
		SELECT
			ch_almacen as almacen,
			trim(ch_nombre_almacen) as nombre
		FROM
			inv_ta_almacenes
		WHERE
			ch_clase_almacen='1'
		ORDER BY
			ch_almacen;
		";

		if ($sqlca->query($sql) < 0) 
			return false;
	
		$registro = Array();

		while($reg = $sqlca->fetchRow())
			$registro[] = $reg;
		return $registro;
    }

	function Correlativo() {
		global $sqlca;

		$sql = "SELECT MAX(pro_cab_numreg) FROM cpag_ta_cabecera;";

		if ($sqlca->query($sql) <= 0)
		    return false;

		$result = null;
		$a = $sqlca->fetchRow();

		if ($a != NULL)
			$result = str_pad($a[0] + 1, 10, "0", STR_PAD_LEFT);
		else
			$result = '0000000000';
		return $result;
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
			AND substring(tab_elemento for 2 from length(tab_elemento)-1 ) = '" . trim($dias) . "';
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

	function ValidacionCompras($tipo,$serie,$documento,$proveedor){
		global $sqlca;

		$sql = "
		SELECT
			count(*)
		FROM
			cpag_ta_cabecera
		WHERE
			pro_cab_tipdocumento 		= '" . $tipo . "'
			AND pro_cab_seriedocumento 	= '" . $serie . "'
			AND pro_cab_numdocumento 	= '" . $documento . "'
			AND pro_codigo 				= '" . $proveedor . "';
		";
		if ($sqlca->query($sql) < 0) 
			return 0;
		$a = $sqlca->fetchRow();

		if($a[0] >= 1)
			return 0; // ya se ingreso ..!!!
		return 1; // no se ingreso...!!!
	}

	/* REGISTRAR COMPRA SUNAT */
	function AgregarComprasCabecera($estacion,$femision,$proveedor,$rubro,$tipo,$serie,$documento,$dvec,$fvencimiento,$tc,$moneda,$base,$impuesto,$total,$percepcion,$correlativo,$contabilizar,$fperiodo, $fregistro){
		global $sqlca;

		settype($base,"double");
		settype($impuesto,"double");
		settype($total,"double");
		settype($percepcion,"double");

		$validar = TipodeCambioModel::ValidacionCompras($tipo,$serie,$documento,$proveedor);

		if($validar == 1){
			$query = "
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
				pro_cab_numreg,
				pro_cab_impinafecto,
				pro_cab_grupoc,
				fecha_replicacion
			) VALUES (
				'" . $tipo . "',
				'" . $serie . "',
				'" . $documento . "',
				'" . $proveedor . "',
				'" . $femision . "',
				'" . $fregistro . "',
				'" . $fvencimiento . "',
				'" . $dvec . "',
				UTIL_FN_TIPO_ACCION_CONTABLE('CP','" . $tipo . "'),
				'42101',
				'" . $moneda . "',
				" . $tc . ",
				" . $total . ",
				" . $total . ",
				now(),
				'" . $estacion . "',
				" . $base . ",
				" . $impuesto . ",
				'" . $rubro . "',
				" . $percepcion . ",
				'',
				'',
				" . $correlativo . ",
				0,
				'" . $contabilizar . "',
				to_date('" . $fperiodo . "','dd/mm/yyyy')
			);
			";
			if($sqlca->query($query) < 0)
				return 0;
			return true;
		}else{
			return 'existe';
		}
	}

	function AgregarComprasDetalle($estacion,$femision,$proveedor,$tipo,$serie,$documento,$tc,$moneda,$total){
		global $sqlca;

		settype($total,"double");

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
			'" . $tipo . "', 
			'" . $serie . "',
			'" . $documento . "',
			'" . $proveedor . "',
			'001',
			'1',
			'" . $femision . "',
			'" . $moneda . "',
			'" . $tc . "',
			" . $total . ",
			null,
			'" . $estacion . "',
			'',
			''
		);
		";
		if($sqlca->query($querydet) < 0)
			return 0;
		return true;
	}

	function ActualizarCompra($proveedor, $tipo, $serie, $documento, $ip , $tf, $formulario, $fecha, $codigo){
		global $sqlca;

		$update = "
		UPDATE
			inv_ta_compras_devoluciones
		SET
			cpag_tipo_pago 		= '" . $tipo . "',
			cpag_serie_pago 	= '" . $serie . "',
			cpag_num_pago 		= '" . $documento . "',
			ip_addr 			= '" . $ip . "',
			mov_fecha_actualizacion = now()
		WHERE
			mov_entidad		= '" . $proveedor . "'
			AND tran_codigo	= '" . $tf . "'
			AND mov_numero	= '" . $formulario . "'
			AND mov_fecha	= '" . $fecha . "'
			AND art_codigo	= '" . $codigo . "'
		";

		if($sqlca->query($update) < 0)
			return 0;
		return true;
	}
}
