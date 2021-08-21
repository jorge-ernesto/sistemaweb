<?php
// Descomentar estas líneas, cuando estamos en modo - development
/*
error_reporting(-1);
ini_set('display_errors', 1);
*/
// Descomentar estas líneas, cuando estamos en modo - production

ini_set('display_errors', 0);
if (version_compare(PHP_VERSION, '5.3', '>='))
{
	error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
}
else
{
	error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE);
}

class MovimientoAlmacenCRUDModel {

	function BEGINTransacction() {
       	global $sqlca;

		try {
			$sql = "BEGIN;";
			if ($sqlca->query($sql) < 0) {
				throw new Exception("ERROR BEGIN");
			}
		} catch (Exception $e) {
			throw $e;
		}
	}

	function COMMITTransacction() {
		global $sqlca;

        	try {
			$sql = "COMMIT;";
			if ($sqlca->query($sql) < 0) {
				throw new Exception("ERROR COMMIT TRANSACION");
			}
		} catch (Exception $e) {
			throw $e;
		}
	}

	function ROLLBACKTransacction() {
		global $sqlca;

		try {
			$sql = "ROLLBACK;";
			if ($sqlca->query($sql) < 0) {
				throw new Exception("ERROR ROLLBACK");
			}
		} catch (Exception $e) {
			throw $e;
		}
	}

    function ObtenerEstaciones() {
		global $sqlca;
	
		try {
			$sql = "
			SELECT
				ch_almacen as almacen,
				trim(ch_nombre_almacen) as nombre,
				ch_clase_almacen as tipo
			FROM
				inv_ta_almacenes
			ORDER BY
				nombre;
			";

			if($sqlca->query($sql) <= 0)
				throw new Exception("Error no se encontro turnos en la fecha indicada");

			while($reg = $sqlca->fetchRow())
				$registro[] = $reg;

			return $registro;
		}catch(Exception $e){
			throw $e;
		}
    }

    function getFechaSistema() {
		global $sqlca;

		//$sql = "SELECT TO_CHAR(da_fecha, 'DD/MM/YYYY') AS fe_sistema FROM pos_aprosys WHERE ch_poscd = 'A' ORDER BY da_fecha ASC LIMIT 1;";
		$sql = "SELECT TO_CHAR(da_fecha, 'DD/MM/YYYY') AS fe_sistema FROM pos_aprosys WHERE ch_poscd = 'A' ORDER BY da_fecha DESC LIMIT 1;";

		if($sqlca->query($sql) <= 0)
			throw new Exception("Error");

		$row = $sqlca->fetchRow();

		return $row['fe_sistema'];
    }

    function getFechaSistemaInicio() {
		global $sqlca;
		$sql = "SELECT da_fecha AS fe_sistema_inicio FROM pos_aprosys WHERE ch_poscd = 'S' ORDER BY da_fecha ASC LIMIT 1;";
		
		if($sqlca->query($sql) <= 0)
			throw new Exception("Error");

		$row = $sqlca->fetchRow();
		return $row['fe_sistema_inicio'];
    }

	function getDocumentosRef(){
		global $sqlca;

		$sql = "
		SELECT
			SUBSTR(trim(tab_elemento),5,2) AS nu_tipo_documento,
			tab_descripcion AS no_tipo_documento
		FROM
			int_tabla_general
		WHERE
			tab_tabla = '08'
			AND tab_elemento NOT IN ('000000', '000001', '000015', '000021', '000022', '000070', '000071', '000072', '000099', '000100')
		ORDER BY
			2; 
		";

		if($sqlca->query($sql) < 0)
			return false;

		$resultado = array();
	
		while($reg = $sqlca->fetchRow())
			$registro[] = $reg;

		return $registro;
	}

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

	function getCierreInventario(){
		global $sqlca;

		$sql = "SELECT par_valor AS fe_cierre_year FROM int_parametros WHERE par_nombre = 'inv_ano_cierre';";
		$sqlca->query($sql);
		$row = $sqlca->fetchRow();

		$sql2 = "SELECT par_valor AS fe_cierre_month FROM int_parametros WHERE par_nombre = 'inv_mes_cierre';";
		$sqlca->query($sql2);
		$row2 = $sqlca->fetchRow();

		$response = array(
			'fe_cierre_year' => $row['fe_cierre_year'],
			'fe_cierre_month' => $row2['fe_cierre_month'],
		);

		return $response;
	}

    function getTipoMovimientoInventario($Nu_Tipo_Movimiento_Inventario) {
		global $sqlca;

		$sql = "
		SELECT
			tran_descripcion AS no_tipo_movimiento_inventario,
			tran_codigo || ' - ' || tran_descripcion AS no_tipo_movimiento_inventario_detallada,
			LPAD(CAST((tran_nform + 1) AS bpchar), 7, '0') AS nu_formulario,
			tran_origen AS nu_almacen_origen,
			tran_destino AS nu_almacen_destino,
			tran_origen || ' - ' || ALMAORIGEN.ch_nombre_almacen AS no_almacen_origen,
			tran_destino || ' - ' || ALMADESTINO.ch_nombre_almacen AS no_almacen_destino,
			tran_naturaleza AS nu_naturaleza_movimiento_inventario
		FROM
			inv_tipotransa INVTMI
			LEFT JOIN inv_ta_almacenes ALMAORIGEN ON (INVTMI.tran_origen = ALMAORIGEN.ch_almacen)
			LEFT JOIN inv_ta_almacenes ALMADESTINO ON (INVTMI.tran_destino = ALMADESTINO.ch_almacen)
		WHERE
			tran_codigo = '" . $Nu_Tipo_Movimiento_Inventario . "';
		";

		if($sqlca->query($sql) <= 0)
			throw new Exception("Error");
		$row = $sqlca->fetchAll();
		return $row;
    }

	function getListAll($data, $jqGridModel) {
		global $sqlca;

		$Nu_Tipo_Movimiento_Inventario = trim($data['Nu_Tipo_Movimiento_Inventario']);
		$Nu_Tipo_Movimiento_Inventario = strip_tags($Nu_Tipo_Movimiento_Inventario);

		$Nu_Almacen = trim($data['Nu_Almacen']);
		$Nu_Almacen = strip_tags($Nu_Almacen);

		$Fe_Inicial = trim($data['Fe_Inicial']);
		$Fe_Inicial = strip_tags($Fe_Inicial);
		$Fe_Inicial = explode("/", $Fe_Inicial);
		$Fe_Inicial = $Fe_Inicial[2] . "-" . $Fe_Inicial[1] . "-" . $Fe_Inicial[0];

		$Fe_Final = trim($data['Fe_Final']);
		$Fe_Final = strip_tags($Fe_Final);
		$Fe_Final = explode("/", $Fe_Final);
		$Fe_Final = $Fe_Final[2] . "-" . $Fe_Final[1] . "-" . $Fe_Final[0];

		$Nu_Documento = trim($data['Nu_Documento']);
		$Nu_Documento = strip_tags($Nu_Documento);

		$No_Producto = trim($data['No_Producto']);
		$No_Producto = strip_tags($No_Producto);

		$cond_almacen = NULL;
		$cond_Nu_Documento = NULL;
		$cond_No_Producto = NULL;

		if(!empty($Nu_Almacen))
			$cond_almacen = "AND MOVI.mov_almacen = '" . $Nu_Almacen . "'";

		if(!empty($Nu_Documento))
			$cond_Nu_Documento = "AND MOVI.mov_docurefe LIKE '%" . $Nu_Documento . "%'";

		if(!empty($No_Producto))
			$cond_No_Producto = "AND PRO.art_descripcion LIKE '%" . $No_Producto . "%'";

		$sqlca->query("
		SELECT
			COUNT(*) total
		FROM
			inv_movialma MOVI
		WHERE
			MOVI.tran_codigo = '" . $Nu_Tipo_Movimiento_Inventario . "'
			AND MOVI.mov_fecha BETWEEN '" . $Fe_Inicial . "' AND '" . $Fe_Final . "'
			" . $cond_almacen . "
			" . $cond_Nu_Documento . "
			" . $cond_No_Producto . "
		");

		$cantidad_registros = $sqlca->fetchRow();
		$paginador = $jqGridModel->Config($cantidad_registros["total"]);

		try {

			$sql = "
			SELECT 
				MOVI.mov_numero AS nu_formulario,
				TO_CHAR(MOVI.mov_fecha,'dd/mm/yyyy hh24:mi:ss') AS fe_emision,
				TO_CHAR(MOVI.mov_fecha,'dd/mm/yyyy') AS fe_sistema,
				PROVEE.pro_codigo AS id_proveedor,
				PROVEE.pro_razsocial AS no_razon_social,
				MOVI.tran_codigo AS nu_tipo_movimiento_inventario,
				MOVI.mov_docurefe AS nu_documento,
				MOVI.mov_tipdocuref AS nu_tipo_documento,
				TD.tab_desc_breve AS no_tipo_documento,
				SUBSTR(MOVI.mov_docurefe, 1, 4) AS nu_serie_documento,
				SUBSTR(MOVI.mov_docurefe, 5, 8) AS nu_numero_documento,
				MOVI.com_serie_compra || ' - ' || MOVI.com_num_compra AS nu_orden_compra,
				PRO.art_codigo AS nu_id_producto,
				PRO.art_descripcion AS no_producto,
				MOVI.mov_cantidad AS nu_cantidad,
				MOVI.mov_costounitario AS nu_costo_unitario,
				MOVI.mov_usuario AS no_usuario
			FROM
				inv_movialma AS MOVI
				LEFT JOIN int_articulos AS PRO ON (PRO.art_codigo = MOVI.art_codigo)
				LEFT JOIN int_proveedores AS PROVEE ON(MOVI.mov_entidad = PROVEE.pro_codigo)
				LEFT JOIN int_tabla_general AS TD ON(MOVI.mov_tipdocuref = substring(TRIM(TD.tab_elemento) for 2 FROM length(TRIM(TD.tab_elemento))-1) AND TD.tab_tabla = '08' AND TD.tab_elemento <> '000000')
			WHERE
				MOVI.tran_codigo = '" . $Nu_Tipo_Movimiento_Inventario . "'
				AND MOVI.mov_fecha::DATE BETWEEN '" . $Fe_Inicial . "' AND '" . $Fe_Final . "'
				" . $cond_almacen . "
				" . $cond_Nu_Documento . "
				" . $cond_No_Producto . "
			ORDER BY
				2 DESC,
				1
			LIMIT
				" . $paginador["limit"] . "
			OFFSET
				" . $paginador["start"];

			if ($sqlca->query($sql) < 0){
				return $response = array(
					'status' => 'danger',
					'message' => 'Problemas al buscar registros'
				);
			}

			if ($sqlca->query($sql) == 0){
				return $response = array(
					'status' => 'warning',
					'message' => 'No hay registros'
				);
			}

			$jqGridModel->DataSource($sqlca->fetchAll());
			$response = array(
				'status' => 'success',
				'message' => 'Registros encontrados satisfactoriamente',
				'data' => $jqGridModel
			);

			error_log($sql);

			return $response;

		}catch(Exception $e){
			throw $e;
		}
	}

	function getListAllExcel($data) {
		global $sqlca;

		$Nu_Tipo_Movimiento_Inventario = trim($data['Nu_Tipo_Movimiento_Inventario']);
		$Nu_Tipo_Movimiento_Inventario = strip_tags($Nu_Tipo_Movimiento_Inventario);

		$Nu_Almacen = trim($data['Nu_Almacen']);
		$Nu_Almacen = strip_tags($Nu_Almacen);

		$Fe_Inicial = trim($data['Fe_Inicial']);
		$Fe_Inicial = strip_tags($Fe_Inicial);
		$Fe_Inicial = explode("/", $Fe_Inicial);
		$Fe_Inicial = $Fe_Inicial[2] . "-" . $Fe_Inicial[1] . "-" . $Fe_Inicial[0];

		$Fe_Final = trim($data['Fe_Final']);
		$Fe_Final = strip_tags($Fe_Final);
		$Fe_Final = explode("/", $Fe_Final);
		$Fe_Final = $Fe_Final[2] . "-" . $Fe_Final[1] . "-" . $Fe_Final[0];

		$Nu_Documento = trim($data['Nu_Documento']);
		$Nu_Documento = strip_tags($Nu_Documento);

		$No_Producto = trim($data['No_Producto']);
		$No_Producto = strip_tags($No_Producto);

		$cond_almacen = NULL;
		$cond_Nu_Documento = NULL;
		$cond_No_Producto = NULL;

		if(!empty($almacen))
			$cond_almacen = "AND MOVI.mov_almacen = '" . $almacen . "'";

		if(!empty($Nu_Documento))
			$cond_Nu_Documento = "AND MOVI.mov_docurefe LIKE '%" . $Nu_Documento . "%'";

		if(!empty($No_Producto))
			$cond_No_Producto = "AND PRO.art_descripcion LIKE '%" . $No_Producto . "%'";

		try {

			$sql = "
			SELECT
				MOVI.mov_numero AS nu_formulario,
				TO_CHAR(MOVI.mov_fecha,'dd/mm/yyyy hh24:mi:ss') AS fe_emision,
				TO_CHAR(MOVI.mov_fecha,'dd/mm/yyyy') AS fe_sistema,
				PROVEE.pro_razsocial AS no_razon_social,
				MOVI.tran_codigo AS nu_tipo_movimiento_inventario,
				MOVI.mov_docurefe AS nu_documento,
				MOVI.mov_tipdocuref AS nu_tipo_documento,
				TD.tab_desc_breve AS no_tipo_documento,
				SUBSTR(MOVI.mov_docurefe, 1, 4) AS nu_serie_documento,
				SUBSTR(MOVI.mov_docurefe, 5, 8) AS nu_numero_documento,
				MOVI.com_serie_compra || ' - ' || MOVI.com_num_compra AS nu_orden_compra,
				PRO.art_codigo AS nu_id_producto,
				PRO.art_descripcion AS no_producto,
				MOVI.mov_cantidad AS nu_cantidad,
				MOVI.mov_costounitario AS nu_costo_unitario,
				MOVI.mov_usuario AS no_usuario
			FROM
				inv_movialma AS MOVI
				JOIN int_articulos AS PRO ON (PRO.art_codigo = MOVI.art_codigo)
				LEFT JOIN int_proveedores AS PROVEE ON(MOVI.mov_entidad = PROVEE.pro_codigo)
				LEFT JOIN int_tabla_general AS TD ON(MOVI.mov_tipdocuref = substring(TRIM(TD.tab_elemento) for 2 FROM length(TRIM(TD.tab_elemento))-1) AND TD.tab_tabla = '08' AND TD.tab_elemento <> '000000')
			WHERE
				MOVI.tran_codigo = '" . $Nu_Tipo_Movimiento_Inventario . "'
				AND MOVI.mov_fecha::DATE BETWEEN '" . $Fe_Inicial . "' AND '" . $Fe_Final . "'
				" . $cond_almacen . "
				" . $cond_Nu_Documento . "
				" . $cond_No_Producto . "
			ORDER BY
				2,1
				";

			if ($sqlca->query($sql) < 0){
				return $response = array(
					'status' => 'danger',
					'message' => 'Problemas al buscar registros'
				);
			}

			if ($sqlca->query($sql) == 0){
				return $response = array(
					'status' => 'warning',
					'message' => 'No hay registros'
				);
			}

			$response = array(
				'status' => 'success',
				'message' => 'Registros encontrados satisfactoriamente',
				'data' => $sqlca->fetchAll()
			);

			return $response;

		}catch(Exception $e){
			throw $e;
		}
	}

	public function compraAdd($arrFormData, $arrTableProductos, $arrConversionGLP, $arrRegistroCompras, $arrFormAgregarDocumentoReferencia, $arrDatosComplementarios, $arrFletes, $usuario, $ip, $enviar_orden_compra) {
		global $sqlca;

		//IGV
		$Nu_IGV = MovimientoAlmacenCRUDModel::getIgv();

		MovimientoAlmacenCRUDModel::BEGINTransacction();

		$response = array(
			'status' => 'success',
			'message' => 'Registro guardado satisfactoriamente'
		);

		try {

			$Nu_Tipo_Movimiento_Inventario 	= trim($arrFormData['Nu_Tipo_Movimiento_Inventario']);
			$Nu_Tipo_Movimiento_Inventario 	= strip_tags($Nu_Tipo_Movimiento_Inventario);

			$Nu_Almacen_Interno 			= trim($arrFormData['Nu_Almacen_Interno']);
			$Nu_Almacen_Interno 			= strip_tags($Nu_Almacen_Interno);

			$Nu_Almacen_Origen 				= trim($arrFormData['Nu_Almacen_Origen']);
			$Nu_Almacen_Origen 				= strip_tags($Nu_Almacen_Origen);

			$Nu_Almacen_Destino 			= trim($arrFormData['Nu_Almacen_Destino']);
			$Nu_Almacen_Destino 			= strip_tags($Nu_Almacen_Destino);

			$Nu_Naturaleza_Movimiento_Inventario = trim($arrFormData['Nu_Naturaleza_Movimiento_Inventario']);
			$Nu_Naturaleza_Movimiento_Inventario = strip_tags($Nu_Naturaleza_Movimiento_Inventario);

			if($Nu_Naturaleza_Movimiento_Inventario == "1" || $Nu_Naturaleza_Movimiento_Inventario == "2")
				$Nu_Almacen_Interno = $Nu_Almacen_Destino;
			else
				$Nu_Almacen_Interno = $Nu_Almacen_Origen;

			$Nu_Tipo_Cambio_Compra 			= trim($arrFormData['Nu_Tipo_Cambio_Compra']);
			$Nu_Tipo_Cambio_Compra 			= strip_tags($Nu_Tipo_Cambio_Compra);

			$Fe_Emision 					= trim($arrFormData['Fe_Emision']);
			$Fe_Emision 					= strip_tags($Fe_Emision);
			$Fe_Emision 					= explode("/", $Fe_Emision);
			$Fe_Emision 					= $Fe_Emision[2] . "-" . $Fe_Emision[1] . "-" . $Fe_Emision[0];

			$Fe_Emision_Registro_Compra 	= trim($arrFormData['Fe_Emision_Registro_Compra']);
			$Fe_Emision_Registro_Compra 	= strip_tags($Fe_Emision_Registro_Compra);
			$Fe_Emision_Registro_Compra 	= explode("/", $Fe_Emision_Registro_Compra);
			$Fe_Emision_Registro_Compra 	= $Fe_Emision_Registro_Compra[2] . "-" . $Fe_Emision_Registro_Compra[1] . "-" . $Fe_Emision_Registro_Compra[0];

			$Fe_Emsiion_RC = $Fe_Emision_Registro_Compra;

			$Fe_Sistema 					= trim($arrFormData['Fe_Sistema']);
			$Fe_Sistema 					= strip_tags($Fe_Sistema);

			$Nu_Documento_Identidad 		= trim($arrFormData['Nu_Documento_Identidad']);
			$Nu_Documento_Identidad 		= strip_tags($Nu_Documento_Identidad);

			$Nu_Tipo_Documento_Compra 		= trim($arrFormData['Nu_Tipo_Documento_Compra']);
			$Nu_Tipo_Documento_Compra 		= strip_tags($Nu_Tipo_Documento_Compra);

			$Nu_Serie_Compra 				= trim($arrFormData['Nu_Serie_Compra']);
			$Nu_Serie_Compra 				= strip_tags($Nu_Serie_Compra);

			$Nu_Numero_Compra 				= trim($arrFormData['Nu_Numero_Compra']);
			$Nu_Numero_Compra 				= strip_tags($Nu_Numero_Compra);

			//$Nu_Total_SIGV_Tot 				= trim($arrFormData['Nu_Total_SIGV_Tot']);
			//$Nu_Total_SIGV_Tot 				= strip_tags($Nu_Total_SIGV_Tot);
			$Nu_Total_SIGV 				= trim($arrFormData['Nu_Total_SIGV']);
			$Nu_Total_SIGV 				= strip_tags($Nu_Total_SIGV);

			/* Referencia Documento (ND O NC)*/
			$Nu_Tipo_Documento_Compra_Referencia = NULL;
			$Nu_Serie_Compra_Referencia = NULL;
			$Nu_Numero_Compra_Referencia = NULL;

			if ($Nu_Tipo_Documento_Compra == '11' || $Nu_Tipo_Documento_Compra == '20') {
				$Nu_Tipo_Documento_Compra_Referencia 	= trim($arrFormAgregarDocumentoReferencia['Nu_Tipo_Documento_Compra_Referencia']);
				$Nu_Tipo_Documento_Compra_Referencia 	= strip_tags($Nu_Tipo_Documento_Compra_Referencia);

				$Nu_Serie_Compra_Referencia 			= trim($arrFormAgregarDocumentoReferencia['Nu_Serie_Compra_Referencia']);
				$Nu_Serie_Compra_Referencia 			= strip_tags($Nu_Serie_Compra_Referencia);

				$Nu_Numero_Compra_Referencia 			= trim($arrFormAgregarDocumentoReferencia['Nu_Numero_Compra_Referencia']);
				$Nu_Numero_Compra_Referencia 			= strip_tags($Nu_Numero_Compra_Referencia);
			}

			/* SQL Instructions */
			$sql_insert_inv_movialma = NULL;
			$sql_upd_num_documento = NULL;

			$Nu_Formulario = NULL;

			/* Valores de Orden de Compra */
			$Nu_Tipo_Orden_Compra = NULL;
			$Nu_Serie_Orden_Compra = NULL;
			$Nu_Numero_Orden_Compra = NULL;

			$Nu_Precio_Compra = 0.00;//Con IGV
			$Nu_Impuesto_Compra = 0.00;//Con IGV
			$Nu_Total_Compra = 0.00;//Con IGV

			/* Actualizar mov_numero inv_movialma */
			$sql = "
UPDATE
 inv_tipotransa
SET
 tran_nform = (SELECT (tran_nform + 1) FROM inv_tipotransa WHERE tran_codigo = '" . $Nu_Tipo_Movimiento_Inventario . "')
WHERE
 tran_codigo = '" . $Nu_Tipo_Movimiento_Inventario . "'
RETURNING
 '" . $Nu_Almacen_Interno . "'||LPAD(CAST((tran_nform::INTEGER) AS bpchar), 7, '0') AS nu_formulario;
";
			$sqlca->query($sql);

			$row = $sqlca->fetchRow();
			$Nu_Formulario = $row["nu_formulario"];

			if ($enviar_orden_compra == '0'){
				/* conversion GLP compras */
				$Enviar_Conversion_GLP = trim($arrConversionGLP['Enviar_Conversion_GLP']);
				$Enviar_Conversion_GLP = strip_tags($Enviar_Conversion_GLP);

				if($Enviar_Conversion_GLP == 'true'){
					$Nu_Kilos = trim($arrConversionGLP['Nu_Kilos']);
					$Nu_Kilos = strip_tags($Nu_Kilos);

					$Nu_Gravedad_Especifica = trim($arrConversionGLP['Nu_Gravedad_Especifica']);
					$Nu_Gravedad_Especifica = strip_tags($Nu_Gravedad_Especifica);

					$Nu_Galones_GLP = trim($arrConversionGLP['Nu_Galones_GLP']);
					$Nu_Galones_GLP = strip_tags($Nu_Galones_GLP);

					$Nu_Litros_GLP = trim($arrConversionGLP['Nu_Litros_GLP']);
					$Nu_Litros_GLP = strip_tags($Nu_Litros_GLP);

					$sqlca->query("
					SELECT
						COUNT(*) AS nu_existe_calculo_glp
					FROM
						inv_calculo_glp
					WHERE
						tran_codigo 	= '" . $Nu_Tipo_Movimiento_Inventario . "'
						AND mov_fecha 	= '" . $Fe_Emision . "'
						AND mov_numero 	= '" . $Nu_Formulario . "'
						AND art_codigo 	= '11620307'
					");

					$exist = $sqlca->fetchRow();

					if($exist['nu_existe_calculo_glp'] == 1){
						$sql_upd_inv_calculo_glp = "
						UPDATE
							inv_calculo_glp
						SET
							kilos 	= " . $Nu_Kilos . ",
							ge 		= " . $Nu_Gravedad_Especifica . ",
							galones = " . $Nu_Galones_GLP . "
						WHERE
							tran_codigo 	= '" . $Nu_Tipo_Movimiento_Inventario . "'
							AND mov_fecha 	= '" . $Fe_Emision . "'
							AND mov_numero 	= '" . $Nu_Formulario . "'
							AND art_codigo 	= '11620307';
						";

						if ($sqlca->query($sql_upd_inv_calculo_glp) < 0){
							$response = array(
								'status' => 'error',
								'message' => 'Error al actualizar table: inv_calculo_glp'
							);
						}
					}else{
						$sql_ins_inv_calculo_glp = "
						INSERT INTO inv_calculo_glp(
							tran_codigo,
							mov_fecha,
							mov_numero,
							art_codigo,
							kilos,
							ge,
							galones
						) VALUES (
							'" . $Nu_Tipo_Movimiento_Inventario . "',
							'" . $Fe_Emision . "',
							'" . $Nu_Formulario . "',
							'11620307',
							" . $Nu_Kilos . ",
							" . $Nu_Gravedad_Especifica . ",
							" . $Nu_Galones_GLP . "
						);
						";

						if ($sqlca->query($sql_ins_inv_calculo_glp) < 0){
							$response = array(
								'status' => 'error',
								'message' => 'Error al insertar table: inv_calculo_glp'
							);
						}
					}
				}
			}

			if ( (($Nu_Tipo_Movimiento_Inventario == '07' || $Nu_Tipo_Movimiento_Inventario == '27') && $Nu_Naturaleza_Movimiento_Inventario == '2') ) { //Verificando tipo de naturaleza
				$response = array(
					'status' => 'error'
				);
				$messageRC = 'El Tipo de Naturaleza: <b>' . $Nu_Naturaleza_Movimiento_Inventario . '</b> solo son para los <b>tipos 01 y 21</b>';
			} else {
				//SI NO SE ENLAZO CON ORDEN(ES) DE COMPRA
				$Nu_Tipo_Orden_Compra = '01';
				if ($enviar_orden_compra == '0'){
					if ($Nu_Naturaleza_Movimiento_Inventario == '2') {
						$status = $sqlca->query("SELECT * FROM int_num_documentos WHERE num_tipdocumento = '01' AND num_seriedocumento = '" . $Nu_Almacen_Interno . "' LIMIT 1");
						
						if ( $status == 0 ) {
							$messageRC = 'No existen <b>serie(s)</b> para generar orden(es) de compra del <b>álmacen: ' . $Nu_Almacen_Interno . '</b>. Registrar serie soporte';
							$response = array(
								'status' => 'error',
								'message' => $messageRC,
							);
							//error_log('3 $messageRC: '.$messageRC);
						} else {
							$sqlca->query("
							UPDATE
								int_num_documentos
							SET
								num_numactual = (SELECT num_numactual::INTEGER + 1 FROM int_num_documentos WHERE num_tipdocumento = '01' AND num_seriedocumento = '" . $Nu_Almacen_Interno . "')
							WHERE
								num_tipdocumento = '01'
								AND num_seriedocumento 	= '" . $Nu_Almacen_Interno . "'
							RETURNING
								LPAD(CAST((num_numactual::INTEGER) AS bpchar), 8, '0') AS num_numactual;
							");

							$row = $sqlca->fetchRow();
							$Nu_Serie_Orden_Compra = $Nu_Almacen_Destino;
							$Nu_Numero_Orden_Compra = $row["num_numactual"];

							$addOrdenCompraCabecera = MovimientoAlmacenCRUDModel::addOrdenCompraCabecera(
								$Nu_Almacen_Interno,
								$Nu_Documento_Identidad,
								$Nu_Tipo_Orden_Compra,
								$Nu_Serie_Orden_Compra,
								$Nu_Numero_Orden_Compra,
								$Fe_Emision,
								$Nu_Codigo_Moneda = '01',
								$Nu_Total_SIGV,
								$Nu_Tipo_Cambio_Compra,
								$Nu_Percepcion = 0.00
							);//Codigo de Moneda Soles = 01

							if(!$addOrdenCompraCabecera){
								$response = array(
									'status' => 'error',
									'message' => 'Error al insertar cabecera table: com_cabecera'
								);
								//error_log('4 $messageRC: '.$response['message']);
							}
						}
					}
				}
			}

			$Fe_Emision_Locatime = localtime(time(),true); //Since 1900
			$Fe_Emision = $Fe_Emision . " " . $Fe_Emision_Locatime["tm_hour"] . ":" . $Fe_Emision_Locatime["tm_min"] . ":" . $Fe_Emision_Locatime["tm_sec"];

			for ($i = 0; $i < count($arrTableProductos); $i++) {
				$iSerieOrdenCompra 	= trim($arrTableProductos[$i]['iSerieOrdenCompra']);
				$iSerieOrdenCompra 	= strip_tags($iSerieOrdenCompra);

				$iNumeroOrdenCompra = trim($arrTableProductos[$i]['iNumeroOrdenCompra']);
				$iNumeroOrdenCompra = strip_tags($iNumeroOrdenCompra);

				$Nu_Id_Producto 	= trim($arrTableProductos[$i]['Nu_Id_Producto']);
				$Nu_Id_Producto 	= strip_tags($Nu_Id_Producto);

				$Nu_Cantidad 		= trim($arrTableProductos[$i]['Nu_Cantidad']);
				$Nu_Cantidad 		= strip_tags($Nu_Cantidad);

				$Nu_Costo_Unitario 	= trim($arrTableProductos[$i]['Nu_Costo_Unitario']);
				$Nu_Costo_Unitario 	= strip_tags($Nu_Costo_Unitario);

				$Nu_Total_SIGV 		= trim($arrTableProductos[$i]['Nu_Total_SIGV']);
				$Nu_Total_SIGV 		= strip_tags($Nu_Total_SIGV);

				$Nu_Margen_Real 	= trim($arrTableProductos[$i]['Nu_Margen_Real']);
				$Nu_Margen_Real 	= strip_tags($Nu_Margen_Real);

		 		$sql_insert_inv_movialma = "
				INSERT INTO inv_movialma (
					tran_codigo,
					mov_almacen,
					mov_almaorigen,
					mov_almadestino,
					mov_naturaleza,
					mov_fecha,
					mov_numero,
					mov_entidad,
					mov_tipdocuref,
					mov_docurefe,
					com_tipo_compra,
					com_serie_compra,
					com_num_compra,
					mov_usuario,
					art_codigo,
					mov_cantidad,
					mov_costounitario,
					mov_costototal,
					mov_docurefe2,
					mov_costo_participacion
				) VALUES (
					'" . $Nu_Tipo_Movimiento_Inventario . "',
					'" . $Nu_Almacen_Interno . "',
					'" . $Nu_Almacen_Origen . "',
					'" . $Nu_Almacen_Destino . "',
					'" . $Nu_Naturaleza_Movimiento_Inventario . "',
					'" . $Fe_Emision . "',
					'" . $Nu_Formulario . "',
					'" . $Nu_Documento_Identidad . "',
					'" . $Nu_Tipo_Documento_Compra . "',
					'" . $Nu_Serie_Compra . $Nu_Numero_Compra . "',
					'" . $Nu_Tipo_Orden_Compra . "',
					'" . ($enviar_orden_compra == '0' ? $Nu_Serie_Orden_Compra : $iSerieOrdenCompra) . "',
					'" . ($enviar_orden_compra == '0' ? $Nu_Numero_Orden_Compra : $iNumeroOrdenCompra) . "',
					'" . $usuario . "',
					'" . $Nu_Id_Producto . "',
					" . $Nu_Cantidad . ",
					" . $Nu_Costo_Unitario . ",
					" . $Nu_Total_SIGV . ",
					'" . $Nu_Serie_Compra_Referencia . $Nu_Numero_Compra_Referencia . "',
					" . $Nu_Margen_Real . "
				);
				";
				//error_log('5 $sql_insert_inv_movialma: '.$sql_insert_inv_movialma);

				if ($sqlca->query($sql_insert_inv_movialma) < 0){
					$response = array(
						'status' => 'error',
						'message' => 'Error al insertar movialma table: inv_movialma',
						'query' => $sql_insert_inv_movialma
					);
				}

				if ( $enviar_orden_compra == '0' ) {
					/* Enviar lote Pedido Vencimientos */
					$Nu_Lote 	= trim($arrTableProductos[$i]['Nu_Lote']);
					$Nu_Lote 	= strip_tags($Nu_Lote);

					if ($Nu_Lote != '') {
						$Fe_Vencimiento_Pedido 	= trim($arrTableProductos[$i]['Fe_Vencimiento_Pedido']);
						$Fe_Vencimiento_Pedido 	= strip_tags($Fe_Vencimiento_Pedido);

						$response_Lote_Pedido = MovimientoAlmacenCRUDModel::pedidoVencimiento(
							$Nu_Formulario,
							$Nu_Id_Producto,
							$Nu_Almacen_Interno,
							$Fe_Emision,
							$Nu_Lote,
							$Fe_Vencimiento_Pedido,
							'1',//Stock
							$ip,
							$usuario
						);
					}
				} else {
					//No podemos actualizar el estado de cabecera porque podría haber productos que todavia están pendientes por generar orden
					$sql_orden_detalle = "
					UPDATE
						com_detalle
					 SET
						com_det_cantidadatendida = com_det_cantidadatendida + " . $Nu_Cantidad . ",
						com_det_estado = '2',
						com_det_fechaentrega = NOW()
					 WHERE 
						pro_codigo = '" . $Nu_Documento_Identidad . "' AND
						num_tipdocumento = '" . $Nu_Tipo_Orden_Compra . "' AND
						num_seriedocumento = '" . $iSerieOrdenCompra . "' AND
						com_cab_numorden = '" . $iNumeroOrdenCompra . "' AND
						art_codigo = '" . $Nu_Id_Producto . "' AND
						current_user_id = '" . $usuario . "' AND
						com_det_estado = '3'
					";

					//error_log('6 $sql_orden_detalle: '.$sql_orden_detalle);
					if ($sqlca->query($sql_orden_detalle) < 0){
						$response = array(
							'status' => 'error',
							'message' => 'Error al actualizar table: com_detalle',
							'query' => $sql_orden_detalle
						);
					}
				}

				$addCostoUnitario = MovimientoAlmacenCRUDModel::CRUDCostoUnitario(
					$Nu_Documento_Identidad,
					$Nu_Id_Producto,
					$Nu_Costo_Unitario,
					$usuario,
					$ip,
					$Nu_Codigo_Moneda = '01'
				);//Codigo de Moneda Soles = 01

				if(!$addCostoUnitario){
					$response = array(
						'status' => 'error',
						'message' => 'Error al insertar costo unitario'
					);
				}

				if ( (($Nu_Tipo_Movimiento_Inventario == '07' || $Nu_Tipo_Movimiento_Inventario == '27') && $Nu_Naturaleza_Movimiento_Inventario == '2') ) { //Verificando tipo de naturaleza
					$response = array(
						'status' => 'error'
					);
					$messageRC = 'El Tipo de Naturaleza: <b>' . $Nu_Naturaleza_Movimiento_Inventario . '</b> solo son para los <b>tipos 01 y 21</b>';
					//error_log('8 $messageRC: '.$messageRC);
				} else {
					if ($enviar_orden_compra == '0'){
						if($Nu_Naturaleza_Movimiento_Inventario == "2"){
							$status = $sqlca->query("SELECT * FROM int_num_documentos WHERE num_tipdocumento = '01' AND num_seriedocumento = '" . $Nu_Almacen_Interno . "' LIMIT 1");
							
							if ( $status == 0 ) {
								$messageRC = 'No existen <b>serie(s)</b> para generar orden(es) de compra del <b>álmacen: ' . $Nu_Almacen_Interno . '</b>';
								$response = array(
									'status' => 'error',
									'message' => $messageRC,
								);
								//error_log('9 $messageRC: '.$messageRC);
							} else {
								$Nu_Precio_Compra 	= ($Nu_IGV * $Nu_Costo_Unitario);
								$Nu_Total_Compra 	= ($Nu_Cantidad * $Nu_Precio_Compra);
								$Nu_Impuesto_Compra = ($Nu_Total_Compra - ($Nu_Total_Compra / $Nu_IGV));
								
								$addOrdenCompraDetalle = MovimientoAlmacenCRUDModel::addOrdenCompraDetalle(
									$Nu_Documento_Identidad,
									$Nu_Tipo_Orden_Compra,
									$Nu_Serie_Orden_Compra,
									$Nu_Numero_Orden_Compra,
									$Fe_Emision,
									$Nu_Id_Producto,
									$Nu_Cantidad,
									$Nu_Precio_Compra,
									$Nu_Impuesto_Compra,
									$Nu_Total_Compra
								);

								if(!$addOrdenCompraDetalle){
									$response = array(
										'status' => 'error',
										'message' => 'Error al insertar detalle table: com_detalle'
									);
									//error_log('10 $response: '.$response['message']);
								}
							}
						}
					}
				}
			}

			/* Fletes */
			if ( isset($arrFletes['Enviar_Flete']) ) {
				$Enviar_Flete = trim($arrFletes['Enviar_Flete']);
				$Enviar_Flete = strip_tags($Enviar_Flete);
				if (  $Enviar_Flete == 'true' ) {
					$Fe_Flete = trim($arrFletes['Fe_Flete']);
					$Fe_Flete = strip_tags($Fe_Flete);

					$ID_Motivo_Traslado = trim($arrFletes['ID_Motivo_Traslado']);
					$ID_Motivo_Traslado = strip_tags($ID_Motivo_Traslado);

					$No_Placa = trim($arrFletes['No_Placa']);
					$No_Placa = strip_tags($No_Placa);

					$No_Licencia = trim($arrFletes['No_Licencia']);
					$No_Licencia = strip_tags($No_Licencia);

					$No_Certificado_Inscripcion = trim($arrFletes['No_Certificado_Inscripcion']);
					$No_Certificado_Inscripcion = strip_tags($No_Certificado_Inscripcion);

					$ID_Transportista_Proveedor = trim($arrFletes['ID_Transportista_Proveedor']);
					$ID_Transportista_Proveedor = strip_tags($ID_Transportista_Proveedor);

					$addFlete = MovimientoAlmacenCRUDModel::addFlete(
						$Nu_Tipo_Movimiento_Inventario,
						$Nu_Formulario,
						$Fe_Emision,
						$Fe_Flete,
						$ID_Motivo_Traslado,
						$No_Placa,
						$No_Licencia,
						$No_Certificado_Inscripcion,
						$ID_Transportista_Proveedor
					);

					if($addFlete == FALSE){
						$response = array(
							'status' => 'error',
							'message' => 'Error al insertar Flete'
						);
						//error_log('11 $response: '.$response['message']);
					}
				}
			}

			/* Datos Complementarios */
			$Enviar_Datos_Complementarios = trim($arrDatosComplementarios['Enviar_Datos_Complementarios']);
			$Enviar_Datos_Complementarios = strip_tags($Enviar_Datos_Complementarios);

			if ($Enviar_Datos_Complementarios == 'true') {
				
				$Fe_Recepcion = trim($arrDatosComplementarios['Fe_Recepcion']);
				$Fe_Recepcion = strip_tags($Fe_Recepcion);

				$Fe_Hora_Recepcion = trim($arrDatosComplementarios['Fe_Hora_Recepcion']);
				$Fe_Hora_Recepcion = strip_tags($Fe_Hora_Recepcion);

				$Nu_Turno_Recepcion = trim($arrDatosComplementarios['Nu_Turno_Recepcion']);
				$Nu_Turno_Recepcion = strip_tags($Nu_Turno_Recepcion);

				$Nu_Numero_Scop_Recepcion = trim($arrDatosComplementarios['Nu_Numero_Scop_Recepcion']);
				$Nu_Numero_Scop_Recepcion = strip_tags($Nu_Numero_Scop_Recepcion);

				$Txt_Observacion_Recepcion = trim($arrDatosComplementarios['Txt_Observacion_Recepcion']);
				$Txt_Observacion_Recepcion = strip_tags($Txt_Observacion_Recepcion);

				$addComplemento = MovimientoAlmacenCRUDModel::addComplemento(
					$Nu_Tipo_Movimiento_Inventario,
					$Nu_Formulario,
					$Fe_Emsiion_RC,
					$Fe_Recepcion,
					$Fe_Hora_Recepcion,
					$Nu_Turno_Recepcion,
					$Nu_Numero_Scop_Recepcion,
					$Txt_Observacion_Recepcion,
					$ip,
					$usuario
				);

				if($addComplemento == FALSE){
					$response = array(
						'status' => 'error',
						'message' => 'Error al insertar Datos Complementarios'
					);
					//error_log('12 $response: '.$response['message']);
				}
			}

			/* Registro de Compras */
			$Enviar_Regisro_Compra = trim($arrRegistroCompras['Enviar_Regisro_Compra']);
			$Enviar_Regisro_Compra = strip_tags($Enviar_Regisro_Compra);

			if($Enviar_Regisro_Compra == 'true'){
				$sqlca->query("
				SELECT
					COUNT(*) AS nu_existe_registro_compra
				FROM
					cpag_ta_cabecera
				WHERE
					pro_codigo = '" . $Nu_Documento_Identidad . "' AND
					pro_cab_tipdocumento 		= '" . $Nu_Tipo_Documento_Compra . "'
					AND pro_cab_seriedocumento 	= '" . $Nu_Serie_Compra . "'
					AND pro_cab_numdocumento 	= '" . $Nu_Numero_Compra . "';
				");

				$exist = $sqlca->fetchRow();
				$validacionRC = '';
				$messageRC = '';

				if($exist['nu_existe_registro_compra'] == 1){
					$validacionRC = 'error';
					$sqlca->query("
					SELECT
						tab_desc_breve AS no_tipo_documento
					FROM
						int_tabla_general
					WHERE
						tab_tabla = '08'
						AND substring(TRIM(tab_elemento) for 2 FROM length(TRIM(tab_elemento))-1) = '" . $Nu_Tipo_Documento_Compra . "'
					");

					$row = $sqlca->fetchRow();

					$messageRC = 'Ya existe documento: <b> ' . $row['no_tipo_documento'] . ' - ' . $Nu_Serie_Compra . ' - ' . $Nu_Numero_Compra . '</b> en el Registro de compras SUNAT';
					$response = array(
						'status' => 'error',
						'validacionRC' => $validacionRC,
						'message' => $messageRC//'messageRC'
					);
					//error_log('13 $response: '.$response['message']);
				}else{

					$Fe_Periodo_RC = trim($arrRegistroCompras['Fe_Periodo_RC']);
					$Fe_Periodo_RC = strip_tags($Fe_Periodo_RC);

					$Nu_Dias_Vencimiento_RC = trim($arrRegistroCompras['Nu_Dias_Vencimiento_RC']);
					$Nu_Dias_Vencimiento_RC = strip_tags($Nu_Dias_Vencimiento_RC);

					$Fe_Vencimiento_RC = trim($arrRegistroCompras['Fe_Vencimiento_RC']);
					$Fe_Vencimiento_RC = strip_tags($Fe_Vencimiento_RC);

					$Rubros_RC = trim($arrRegistroCompras['Rubros_RC']);
					$Rubros_RC = strip_tags($Rubros_RC);

					$Nu_TC_RC = trim($arrRegistroCompras['Nu_TC_RC']);
					$Nu_TC_RC = strip_tags($Nu_TC_RC);

					$Moneda_RC = trim($arrRegistroCompras['Moneda_RC']);
					$Moneda_RC = strip_tags($Moneda_RC);

					$Nu_BI_RC = trim($arrRegistroCompras['Nu_BI_RC']);
					$Nu_BI_RC = strip_tags($Nu_BI_RC);

					$Nu_IGV_RC = trim($arrRegistroCompras['Nu_IGV_RC']);
					$Nu_IGV_RC = strip_tags($Nu_IGV_RC);

					$Nu_Totacl_RC = trim($arrRegistroCompras['Nu_Totacl_RC']);
					$Nu_Totacl_RC = strip_tags($Nu_Totacl_RC);

					$Nu_Percepcion_RC = trim($arrRegistroCompras['Nu_Percepcion_RC']);
					$Nu_Percepcion_RC = strip_tags($Nu_Percepcion_RC);

					$Nu_Inafecto_IGV_RC = trim($arrRegistroCompras['Nu_Inafecto_IGV_RC']);
					$Nu_Inafecto_IGV_RC = strip_tags($Nu_Inafecto_IGV_RC);

					$Txt_Glosa_RC = trim($arrRegistroCompras['Txt_Glosa_RC']);
					$Txt_Glosa_RC = strip_tags($Txt_Glosa_RC);

					if(trim($Nu_Dias_Vencimiento_RC) == '')
						$Nu_Dias_Vencimiento_RC = 0;
					if(trim($Nu_Inafecto_IGV_RC) == '')
						$Nu_Inafecto_IGV_RC = 0.00;
					if(trim($Nu_Percepcion_RC) == '')
						$Nu_Percepcion_RC = 0.00;

					//$Nu_Saldo_RC = 0.00;
					$Nu_Saldo_RC = ($Nu_BI_RC + $Nu_IGV_RC) + $Nu_Percepcion_RC + $Nu_Inafecto_IGV_RC;

					if ($Nu_Tipo_Documento_Compra == '91') {//Comprobantes no domicialiados no suma el inafecto
						$Nu_BI_RC = 0;
						$Nu_IGV_RC = 0;
					}

					/*
					if($Nu_Tipo_Documento_Compra != '91'){//Comprobantes no domicialiados no suma el inafecto
						if($Nu_Percepcion_RC > 0 && $Nu_Inafecto_IGV_RC > 0)
							$Nu_Saldo_RC = ($Nu_BI_RC + $Nu_IGV_RC) + $Nu_Percepcion_RC + $Nu_Inafecto_IGV_RC;
						elseif($Nu_Percepcion_RC > 0)
							$Nu_Saldo_RC = ($Nu_BI_RC + $Nu_IGV_RC) + $Nu_Percepcion_RC;
						elseif($Nu_Inafecto_IGV_RC > 0)
							$Nu_Saldo_RC = ($Nu_BI_RC + $Nu_IGV_RC) + $Nu_Inafecto_IGV_RC;
						else
							$Nu_Saldo_RC = ($Nu_BI_RC + $Nu_IGV_RC);
					}else{
						$Nu_BI_RC = 0.00;
						$Nu_IGV_RC = 0.00;
						$Nu_Saldo_RC = ($Nu_BI_RC + $Nu_IGV_RC);
						$Nu_Totacl_RC = ($Nu_BI_RC + $Nu_IGV_RC) + $Nu_Inafecto_IGV_RC;
					}
					*/

					$Fe_Periodo_RC 	= explode("/", $Fe_Periodo_RC);
					$Fe_Periodo_RC 	= $Fe_Periodo_RC[2] . "-" . $Fe_Periodo_RC[1] . "-" . $Fe_Periodo_RC[0];

					$Fe_Vencimiento_RC 	= explode("/", $Fe_Vencimiento_RC);
					$Fe_Vencimiento_RC 	= $Fe_Vencimiento_RC[2] . "-" . $Fe_Vencimiento_RC[1] . "-" . $Fe_Vencimiento_RC[0];

					$getCorrelativoPeriodo = MovimientoAlmacenCRUDModel::getCorrelativoPeriodo($Fe_Periodo_RC);

					$addRegistroComprasCabecera = MovimientoAlmacenCRUDModel::addRegistroComprasCabecera(
						$Nu_Almacen_Interno,
						$Nu_Documento_Identidad,
						$Nu_Tipo_Documento_Compra,
						$Nu_Serie_Compra,
						$Nu_Numero_Compra,
						$Fe_Emsiion_RC,
						$Fe_Periodo_RC,
						$Nu_Dias_Vencimiento_RC,
						$Fe_Vencimiento_RC,
						$Rubros_RC,
						$Moneda_RC,
						$Nu_TC_RC,
						$Nu_BI_RC,
						$Nu_IGV_RC,
						$Nu_Inafecto_IGV_RC,
						$Nu_Percepcion_RC,
						$Nu_Totacl_RC,
						$Nu_Saldo_RC,
						$getCorrelativoPeriodo[1],
						$Txt_Glosa_RC,
						$Nu_Tipo_Documento_Compra_Referencia,
						$Nu_Serie_Compra_Referencia,
						$Nu_Numero_Compra_Referencia
					);

					if($addRegistroComprasCabecera == FALSE){
						$response = array(
							'status' => 'error',
							'message' => 'Error al insertar Registro de Compra cabecera'
						);
						//error_log('14 $response: '.$response['message']);
					}

					$addRegistrosComprasDetalle = MovimientoAlmacenCRUDModel::addRegistrosComprasDetalle(
						$Nu_Almacen_Interno,
						$Nu_Documento_Identidad,
						$Nu_Tipo_Documento_Compra,
						$Nu_Serie_Compra,
						$Nu_Numero_Compra,
						$Fe_Emsiion_RC,
						$Moneda_RC,
						$Nu_TC_RC,
						$Nu_Totacl_RC,
						$Nu_Tipo_Documento_Compra_Referencia,
						$Nu_Serie_Compra_Referencia,
						$Nu_Numero_Compra_Referencia
					);

					if($addRegistrosComprasDetalle == FALSE){
						$response = array(
							'status' => 'error',
							'message' => 'Error al insertar Registro de Compra detalle'
						);
						//error_log('15 $response: '.$response['message']);
					}

					$updComprasDevoluciones = MovimientoAlmacenCRUDModel::updComprasDevoluciones(
						$Nu_Documento_Identidad,
						$Nu_Tipo_Documento_Compra,
						$Nu_Serie_Compra,
						$Nu_Numero_Compra,
						$Fe_Emsiion_RC,
						$Nu_Tipo_Movimiento_Inventario,
						$Nu_Formulario,
						$ip,
						$Nu_Serie_Compra_Referencia,
						$Nu_Numero_Compra_Referencia
					);

					if($updComprasDevoluciones == FALSE){
						$response = array(
							'status' => 'error',
							'message' => 'Error al actualizar Compra de Devoluciones'
						);
						//error_log('16 $response: '.$response['message']);
					}
				}
			}

			if($response['status'] == 'error'){
				MovimientoAlmacenCRUDModel::ROLLBACKTransacction();
				/*$response = array(
					'status' => 'error',
					'validacionRC' => 'error',
					'message' => 'Error al procesar<br>Posible error<ul><li>Verificar detalle de productos: El cálculo del importe debe ser diferente a cero</li></ul>',
				);*/
				//error_log('17 $response: '.$response['message']);
			}else{
				MovimientoAlmacenCRUDModel::COMMITTransacction();
			}

			return $response;
	
		}catch(Exception $e){
			throw $e;
		}
	}

	function CRUDCostoUnitario($Nu_Documento_Identidad, $Nu_Id_Producto, $Nu_Costo_Unitario, $usuario, $ip, $Nu_Codigo_Moneda){
		global $sqlca;

		if($Nu_Documento_Identidad != "" && $Nu_Documento_Identidad != "1"){

			$sqlca->query("
			SELECT
				COUNT(*) AS existe_costo_unitario
			FROM
				com_rec_pre_proveedor
			WHERE
				pro_codigo 		= '" . $Nu_Documento_Identidad . "'
				AND art_codigo 	= '" . $Nu_Id_Producto . "';
			");

			$row = $sqlca->fetchRow();
			$sql = "";

			if($row["existe_costo_unitario"] == 1){
				$sql = "
				UPDATE
					com_rec_pre_proveedor 
				SET
					rec_precio				= " . $Nu_Costo_Unitario . ",
					rec_fecha_ultima_compra = now(),
					rec_usuario				= '" . $usuario . "',
					rec_ip					= '" . $ip . "'
				WHERE
					pro_codigo 		= '" . $Nu_Documento_Identidad . "'
					AND art_codigo 	= '" . $Nu_Id_Producto . "';
				";

				if ($sqlca->query($sql) < 0)
					return false;
			}else{
				$sql = "
				INSERT INTO com_rec_pre_proveedor(	
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
					'" . $Nu_Documento_Identidad ."',
					'" . $Nu_Id_Producto ."',
					'" . $Nu_Codigo_Moneda ."',
					" . $Nu_Costo_Unitario .",
					0.00,
					now(),
					now(),
					'" . $usuario . "',
					'" . $ip ."'
				);
				";
				if ($sqlca->query($sql) < 0)
					return false;
			}
		}
		return true;
	}

	function addOrdenCompraCabecera($Nu_Almacen_Interno, $Nu_Documento_Identidad, $Nu_Tipo_Documento_Compra, $Nu_Serie_Orden_Compra, $Nu_Numero_Orden_Compra, $Fe_Emision, $Nu_Codigo_Moneda, $Nu_Total_SIGV, $Nu_Tipo_Cambio_Compra, $Nu_Percepcion){
		global $sqlca;

		settype($Nu_Total_SIGV,"double");
		settype($Nu_Percepcion,"double");

		$sql="
		INSERT INTO com_cabecera(
			com_cab_almacen,
			pro_codigo,
			num_tipdocumento,
			num_seriedocumento,
			com_cab_numorden,
			com_cab_fechaorden,
			com_cab_fechaofrecida,
			com_cab_fecharecibida,
			com_cab_tipcambio,
			com_cab_credito,
			com_cab_formapago,
			com_cab_imporden,
			com_cab_recargo1,
			com_cab_estado,
			com_cab_transmision,
			com_cab_moneda,
			percepcion
		)VALUES(
			'" . $Nu_Almacen_Interno . "',
			'" . $Nu_Documento_Identidad . "',
			'" . $Nu_Tipo_Documento_Compra . "',
			'" . $Nu_Serie_Orden_Compra . "',
			'" . $Nu_Numero_Orden_Compra . "',
			'" . $Fe_Emision . "',
			'" . $Fe_Emision . "',
			'" . $Fe_Emision . "',
			" . $Nu_Tipo_Cambio_Compra . ",
			'N',
			'01',
			" . $Nu_Total_SIGV . ",
			0,
			'2',
			't',
			" . $Nu_Codigo_Moneda . ",
			" . $Nu_Percepcion . "
		);
		";

		/*
		--TABLA: Orden de compra - CAMPO: com_cab_estado -> VALOR: 'Inventario'
		Observaciones: Según el archivo de Orden de Compras
		WHEN com_cab_estado='1' THEN 'Pendiente' 
		WHEN com_cab_estado='2' THEN 'Inventario'
		WHEN com_cab_estado='3' THEN 'Procesando'
		WHEN com_cab_estado='4' THEN 'Facturado'
		WHEN com_cab_estado='5' THEN 'Cerrado'
		Else 'Otro' END as estado 
		*/

		//echo "<br>";
		//echo "Cabecera com_cabecera: " . $sql;
		if ($sqlca->query($sql) < 0)
			return false;
		return true;
	}

	function addOrdenCompraDetalle($Nu_Documento_Identidad, $Nu_Tipo_Documento_Compra, $Nu_Serie_Orden_Compra, $Nu_Numero_Orden_Compra, $Fe_Emision, $Nu_Id_Producto, $Nu_Cantidad, $Nu_Precio_Compra, $Nu_Impuesto_Compra, $Nu_Total_Compra){
		global $sqlca;

		//Bug, tabla com_detalle campo com_det_precio, si el valor < 0.01, mostrará error por el trigger com_tr_detalle_ins mensaje Subtotal en Cero no se puede Anadir
		//El problema es que esa tabla esta configurado para 2 decímales y la de inv_movialma hasta 4 decímales

		if ($Nu_Precio_Compra < 0.01)
			$Nu_Precio_Compra = 0.01;

		$sql = "
		INSERT INTO com_detalle(
			pro_codigo,
			num_tipdocumento,
			num_seriedocumento,
			com_cab_numorden,
			com_det_fechaentrega,
			art_codigo,
			com_det_cantidadpedida,
			com_det_cantidadatendida,
			com_det_precio,
			com_det_imparticulo,
			com_det_descuento1,
			com_det_estado,
			com_det_cd_impuesto1,
			com_det_impuesto1
		) VALUES (
			'" . $Nu_Documento_Identidad . "',
			'" . $Nu_Tipo_Documento_Compra . "',
			'" . $Nu_Serie_Orden_Compra . "',
			'" . $Nu_Numero_Orden_Compra . "',
			'" . $Fe_Emision . "',
			'" . $Nu_Id_Producto . "',
			" . $Nu_Cantidad . ",
			" . $Nu_Cantidad . ",
			" . $Nu_Precio_Compra . ",
			" . $Nu_Total_Compra . ",
			0.00,
			'2',
			'0009',
			" . $Nu_Impuesto_Compra . "
		);
		";

		//echo "<br>";
		//echo "Detalle com_detalle: " . $sql;
		if ($sqlca->query($sql) < 0)
			return false;
		return true;
	}

	function getCorrelativoPeriodo($dateact){
		global $sqlca;

		$year 	= substr($dateact,0,4);
		$month	= substr($dateact,5,2);

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

	function addRegistroComprasCabecera($Nu_Almacen_Interno, $Nu_Documento_Identidad, $Nu_Tipo_Compra, $Nu_Serie_Compra, $Nu_Numero_Compra, $Fe_Emision, $Fe_Periodo_RC, $Nu_Dias_Vencimiento_RC, $Fe_Vencimiento_RC, $Rubros_RC, $Moneda_RC, $Nu_TC_RC, $Nu_BI_RC, $Nu_IGV_RC, $Nu_Inafecto_IGV_RC, $Nu_Percepcion_RC, $Nu_Totacl_RC, $Nu_Saldo_RC, $getCorrelativoPeriodo, $Txt_Glosa_RC, $Nu_Tipo_Documento_Compra_Referencia, $Nu_Serie_Compra_Referencia, $Nu_Numero_Compra_Referencia){
		global $sqlca;

		$sql = "
		INSERT INTO cpag_ta_cabecera(
			pro_cab_almacen,
			pro_codigo,
			pro_cab_tipdocumento,
			pro_cab_seriedocumento,
			pro_cab_numdocumento,
			pro_cab_tipdocreferencia,
			pro_cab_numdocreferencia,
			pro_cab_fechaemision,
			pro_cab_fecharegistro,
			pro_cab_fechasaldo,
			pro_cab_dias_vencimiento,
			pro_cab_fechavencimiento,
			pro_cab_rubrodoc,
			pro_cab_moneda,
			pro_cab_tcambio,
			pro_cab_impafecto,
			pro_cab_impto1,
			pro_cab_impinafecto,
			regc_sunat_percepcion,
			pro_cab_imptotal,
			pro_cab_impsaldo,
			pro_cab_numreg,
			pro_cab_glosa,
			fecha_replicacion,
			pro_cab_tipcontable,
			plc_codigo
		) VALUES (
			'" . $Nu_Almacen_Interno . "',
			'" . $Nu_Documento_Identidad . "',
			'" . $Nu_Tipo_Compra . "',
			'" . $Nu_Serie_Compra . "',
			'" . $Nu_Numero_Compra . "',
			'" . $Nu_Tipo_Documento_Compra_Referencia . "',
			'" . $Nu_Serie_Compra_Referencia . $Nu_Numero_Compra_Referencia . "',
			'" . $Fe_Emision . "',
			'" . $Fe_Periodo_RC . "',
			'" . $Fe_Emision . "',
			'" . $Nu_Dias_Vencimiento_RC . "',
			'" . $Fe_Vencimiento_RC . "',
			'" . $Rubros_RC . "',
			'" . $Moneda_RC . "',
			" . $Nu_TC_RC . ",
			" . $Nu_BI_RC . ",
			" . $Nu_IGV_RC . ",
			" . $Nu_Inafecto_IGV_RC . ",
			" . $Nu_Percepcion_RC . ",
			" . $Nu_Totacl_RC . ",
			" . $Nu_Saldo_RC . ",
			'" . $getCorrelativoPeriodo . "',
			'" . $Txt_Glosa_RC . "',
			NOW(),
			UTIL_FN_TIPO_ACCION_CONTABLE('CP','" . $Nu_Tipo_Compra . "'),
			'42101'
		);
		";

		//echo "<br />";
		//echo "INSERT RC Cabecera: " . $sql;
		if ($sqlca->query($sql) < 0)
			return false;
		return true;
	}

	function addRegistrosComprasDetalle($Nu_Almacen_Interno, $Nu_Documento_Identidad, $Nu_Tipo_Compra, $Nu_Serie_Compra, $Nu_Numero_Compra, $Fe_Emision, $Moneda_RC, $Nu_TC_RC, $Nu_Totacl_RC, $Nu_Tipo_Documento_Compra_Referencia, $Nu_Serie_Compra_Referencia, $Nu_Numero_Compra_Referencia){
		global $sqlca;

		$sql = "
		INSERT INTO cpag_ta_detalle (
			pro_det_almacen,
			pro_codigo,
			pro_cab_tipdocumento,
			pro_cab_seriedocumento,
			pro_cab_numdocumento,
			pro_det_tipdocreferencia,
			pro_det_numdocreferencia,
			pro_det_identidad,
			pro_det_tipmovimiento,
			pro_det_fechamovimiento,
			pro_det_moneda,
			pro_det_tcambio,
			pro_det_grupoc,
			pro_det_impmovimiento
      	)VALUES(
			'" . $Nu_Almacen_Interno . "',
			'" . $Nu_Documento_Identidad . "',
			'" . $Nu_Tipo_Compra . "',
			'" . $Nu_Serie_Compra . "',
			'" . $Nu_Numero_Compra . "',
			'" . $Nu_Tipo_Documento_Compra_Referencia . "',
			'" . $Nu_Serie_Compra_Referencia . $Nu_Numero_Compra_Referencia . "',
			'001',
			'1',
			'" . $Fe_Emision . "',
			'" . $Moneda_RC . "',
			" . $Nu_TC_RC . ",
			NULL,
			" . $Nu_Totacl_RC . "
		);
		";

		//echo "<br />";
		//echo "INSERT RC Detalle: " . $sql;
		if ($sqlca->query($sql) < 0)
			return false;
		return true;
	}

	function updComprasDevoluciones($Nu_Documento_Identidad, $Nu_Tipo_Compra, $Nu_Serie_Compra, $Nu_Numero_Compra, $Fe_Emision, $Nu_Tipo_Movimiento_Inventario, $Nu_Formulario, $ip, $Nu_Serie_Compra_Referencia, $Nu_Numero_Compra_Referencia){
		global $sqlca;

		$sql = "
		UPDATE
			inv_ta_compras_devoluciones
		SET
			cpag_tipo_pago 			= '" . $Nu_Tipo_Compra . "',
			cpag_serie_pago 		= '" . $Nu_Serie_Compra . "',
			cpag_num_pago 			= '" . $Nu_Numero_Compra . "',
			mov_docurefe2  			= '" . $Nu_Serie_Compra_Referencia . $Nu_Numero_Compra_Referencia . "',
			ip_addr 				= '" . $ip . "',
			mov_fecha_actualizacion = NOW()
		WHERE
			tran_codigo 		= '" . $Nu_Tipo_Movimiento_Inventario . "'
			AND mov_entidad 	= '" . $Nu_Documento_Identidad . "'
			AND mov_fecha::DATE = '" . $Fe_Emision . "'
			AND mov_numero 		= '" . $Nu_Formulario . "'
		";

		//echo "<br />";
		//echo "UPDATE Compra Devoluciones: " . $sql;
		if ($sqlca->query($sql) < 0)
			return false;
		return true;
	}

	function addComplemento($Nu_Tipo_Movimiento_Inventario, $Nu_Formulario, $Fe_Emsiion_RC, $Fe_Recepcion, $Fe_Hora_Recepcion, $Nu_Turno_Recepcion, $Nu_Numero_Scop_Recepcion, $Txt_Observacion_Recepcion, $ip, $usuario){
		global $sqlca;

		$sql = "
		INSERT INTO inv_movialma_complemento(
			tran_codigo,
			mov_numero,
			mov_fecha,
			hora_recepcion,
			turno_recepcion,
			numero_scop,
			observacion,
			auditoria_ip,
			auditoria_usuario,
			auditoria_fecha
		) VALUES (
			'" . $Nu_Tipo_Movimiento_Inventario . "',
			'" . $Nu_Formulario . "',
			'" . $Fe_Emsiion_RC . "',
			'" . $Fe_Recepcion . ' ' . $Fe_Hora_Recepcion . "',
			" . $Nu_Turno_Recepcion . ",
			" . $Nu_Numero_Scop_Recepcion . ",
			'" . $Txt_Observacion_Recepcion . "',
			'" . $ip . "',
			'" . $usuario . "',
			NOW()
		);
		";

		//echo "<br />";
		//echo "INSERT Complemento: " . $sql;
		if ($sqlca->query($sql) < 0)
			return false;
		return true;
	}

	function addFlete($Nu_Tipo_Movimiento_Inventario, $Nu_Formulario, $Fe_Emision, $Fe_Flete, $ID_Motivo_Traslado, $No_Placa, $No_Licencia, $No_Certificado_Inscripcion, $ID_Transportista_Proveedor){
		global $sqlca;

		$sql = "
		INSERT INTO flete(
			tran_codigo,
			mov_numero,
			fe_emision,
			fe_flete,
			id_motivo_traslado,
			no_placa,
			no_licencia,
			no_certificado_inscripcion,
			id_transportista_proveedor
		) VALUES (
			'" . $Nu_Tipo_Movimiento_Inventario . "',
			'" . $Nu_Formulario . "',
			'" . $Fe_Emision . "',
			'" . $Fe_Flete . "',
			" . $ID_Motivo_Traslado . ",
			'" . $No_Placa . "',
			'" . $No_Licencia . "',
			'" . $No_Certificado_Inscripcion . "',
			'" . $ID_Transportista_Proveedor . "'
		);
		";

		//echo "<br />";
		//echo "INSERT Flete: " . $sql;
		if ($sqlca->query($sql) < 0)
			return false;
		return true;
	}

	function pedidoVencimiento($Nu_Formulario, $Nu_Id_Producto, $Nu_Almacen_Interno, $Fe_Emision, $Nu_Lote, $Fe_Vencimiento_Pedido, $Nu_Estado, $ip, $usuario){
		global $sqlca;

		$sql = "
		INSERT INTO inv_pedido_vencimiento(
			id_nu_formulario,
			id_no_producto,
			id_almacen,
			fe_emision,
			no_lote,
			fe_vencimiento,
			nu_estado,
			ip_usuario,
			no_usuario
		) VALUES (
			'" . $Nu_Formulario . "',
			'" . $Nu_Id_Producto . "',
			'" . $Nu_Almacen_Interno . "',
			'" . $Fe_Emision . "',
			'" . $Nu_Lote . "',
			'" . $Fe_Vencimiento_Pedido . "',
			'" . $Nu_Estado . "',
			'" . $ip . "',
			'" . $usuario . "'
		);
		";

		//echo "<br />";
		//echo "INSERT Pedido Vencimiento: " . $sql;
		if ($sqlca->query($sql) < 0)
			return false;
		return true;
	}

	function getTest() {
		global $sqlca;

		$sql = "SELECT 'kwn' AS username;";

		if($sqlca->query($sql) <= 0)
			throw new Exception("Error");

		$row = $sqlca->fetchRow();

		return $row['username'];
	}
}