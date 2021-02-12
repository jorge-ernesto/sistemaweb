<?php

function os_odbcsql_escape($str) {
	return str_replace("'","''",$str);
}

class InterfaceSAPModel extends Model {

	function connectHanaSAP(){
		global $sqlca;
		$status = $sqlca->query("
		SELECT
			p1.par_valor AS hana_dbname,
      		p2.par_valor AS hana_username,
      		p3.par_valor AS hana_password
  		FROM
      		int_parametros p1
      		LEFT JOIN int_parametros p2 ON p2.par_nombre = 'hana_username'
      		LEFT JOIN int_parametros p3 ON p3.par_nombre = 'hana_password'
      		LEFT JOIN int_parametros p4 ON p4.par_nombre = 'hana_dbname'
  		WHERE
  			p1.par_nombre = 'hana_dbname';
		");

		$config['hana']['estado_opensoft'] = TRUE;
		if($status == 0){
			$config['hana']['estado_opensoft'] = FALSE;
			$config['hana']['mensaje_opensoft'] = 'No hay registros';
		}

		$row = $sqlca->fetchRow();
		$db_name = $row['hana_dbname'];
		$username = $row['hana_username'];
		$password = $row['hana_password'];

		// Try to connect
		$conn = odbc_connect("$db_name", "$username", "$password");
		$config['hana']['parametros_conexion'] = $conn;
		$config['hana']['estado_conexion'] = TRUE;
		$config['hana']['mensaje_conexion'] = "conectado";
		if (!$conn){
			$config['hana']['estado_conexion'] = FALSE;
			$config['hana']['mensaje_conexion'] = odbc_errormsg();
			return $config['hana'];
		}
		return $config['hana'];
	}

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

	//Obtener la fecha del ultimo del cierre dia - tabla PA = pos_aprosys
	function getLastDatePA() {
		global $sqlca;
		$sqlca->query("SELECT TO_CHAR(da_fecha - integer '1', 'DD/MM/YYYY') AS fe_sistema FROM pos_aprosys WHERE ch_poscd = 'A' ORDER BY da_fecha DESC LIMIT 1;");
		$row = $sqlca->fetchRow();
		return $row['fe_sistema'];
	}

	function getMontoImpuesto(){
		global $sqlca;
        $sqlca->query("SELECT util_fn_igv();");
        $row = $sqlca->fetchRow();
        settype($row['util_fn_igv'], "double");
        $ss_value = $row['util_fn_igv'];
        $ss_impuesto = (1 + ($ss_value / 100));
        return $ss_impuesto;
	}

	function getFactorBonus(){
		global $sqlca;
		$sqlca->query("SELECT par_valor AS ss_factor_bonus FROM int_parametros WHERE par_nombre='prom_factor_bonus';");
		$row = $sqlca->fetchRow();
		$ss_factor_bonus = $row['ss_factor_bonus'];
		if (empty($row['ss_factor_bonus']))//Verificar que valor se asignará cuando no tenga Puntos BONUS
			$ss_factor_bonus = 1;
		return $ss_factor_bonus;
	}

	function getSocios($connectHana, $nu_almacen, $fe_inicial, $fe_final) {
		global $sqlca;

		$condAlmacenFactura = (!empty($nu_almacen) ? "AND FC.ch_almacen = '" . $nu_almacen . "'" : NULL);
		$condAlmacenND = (!empty($nu_almacen) ? "AND VC.ch_sucursal = '" . $nu_almacen . "'" : NULL);

		$status = $sqlca->query("
		SELECT * FROM (
        SELECT
            CLI.cli_codigo AS Nu_Codigo_Cliente,
            CLI.cli_razsocial AS No_Nombre_Cliente,
            CLI.cli_ruc AS Nu_Documento_Identidad,
		    CLI.cli_telefono1 AS Nu_Telefono1,
		    CLI.cli_email AS Txt_Email,
		    CLI.cli_ndespacho_efectivo,
		    CLI.cli_anticipo,
		    CLI.cli_creditosol,
            CLI.cli_direccion AS Txt_Direccion,
            CLI.cli_contacto AS No_Contacto
        FROM
            fac_ta_factura_cabecera AS FC
            JOIN int_clientes AS CLI ON(FC.cli_codigo = CLI.cli_codigo)
        WHERE
            FC.ch_fac_tipodocumento IN ('10', '11', '20', '35')
            AND FC.dt_fac_fecha BETWEEN '" . $fe_inicial . "' AND '" . $fe_final . "'
            " . $condAlmacenFactura . "
        ) AS CLI
        UNION
        (
        SELECT
            CLI.cli_codigo AS Nu_Codigo_Cliente,
            CLI.cli_razsocial AS No_Nombre_Cliente,
            CLI.cli_ruc AS Nu_Documento_Identidad,
		    CLI.cli_telefono1 AS Nu_Telefono1,
		    CLI.cli_email AS Txt_Email,
		    CLI.cli_ndespacho_efectivo,
		    CLI.cli_anticipo,
		    CLI.cli_creditosol,
            CLI.cli_direccion AS Txt_Direccion,
            CLI.cli_contacto AS No_Contacto
        FROM
            val_ta_cabecera AS VC
            JOIN int_clientes AS CLI ON(VC.ch_cliente = CLI.cli_codigo)
        WHERE
            VC.dt_fecha BETWEEN '" . $fe_inicial . "' AND '" . $fe_final . "'
            " . $condAlmacenND . "
        );
        ");

		//Status Postgres SQL
		//-1 = PGSQL_ERROR_QUERY
		//0 = PGSQL_EMPTY_QUERY

		$arrResult['descripcion_tabla'] = 'Socio de Negocio';
		$arrResult['tabla'] = 'INTOCRD';
		$arrResult['codigo_error'] = '';
		$arrResult['cantidad_registros'] = $status;// status == Tambien contiene la cantidad de registros

		if($status < 0){
			$arrResult['estado'] = FALSE;
			$arrResult['mensaje'] = 'Error SQL - function getSocios';
		} else if($status == 0) {
			$arrResult['estado'] = TRUE;
			$arrResult['mensaje'] = 'No se encontró ningún registro';
		} else {
			$arrSocios = $sqlca->fetchAll();
			$arrResult = $this->sendSociosSAP($connectHana, $arrSocios);

			if($arrResult['codigo_error'] == '23000')
				$arrResult['mensaje'] = 'Ya se migro la información';
			if($arrResult['codigo_error'] == 'S1000')
				$arrResult['mensaje'] = 'No se pueden registrar los socios porque los tipos de datos de las columnas fueron cambiados en HANA - SAP';

			$arrResult['descripcion_tabla'] = 'Socio de Negocio';
			$arrResult['tabla'] = 'INTOCRD';
		}
		return $arrResult;
	}

	function sendSociosSAP($connectHana, $arrSocios){
		$No_Tipo_Persona = '';
		$Nu_Documento_Identidad = 0;
		$Nu_Tipo_Documento_Identidad = 0;//Otros según tabla SUNAT
		$datos_personales_cliente = '';
		$No_Primer_Nombre = '';
		$No_Segundo_Nombre = '';
		$No_Apellido_Paterno = '';
		$No_Apellido_Materno = '';
		$Nu_Telefono1 = 0;
		$Ss_Credito_Soles = 0;
		$cantidad_registros = 0;
		$datos_personales_contacto = '';
		$No_Nombre_Contacto = '';
		$No_Apellido_Contacto = '';
		foreach ($arrSocios as $row) {
			$No_Nombre_Cliente = os_odbcsql_escape($row['no_nombre_cliente']);
			$No_Tipo_Persona = (substr($Nu_Documento_Identidad, 0, 2) == "10" ? "TPN" : "TPJ");

			if ($No_Tipo_Persona == 'TPN'){
	        	$datos_personales_cliente = explode(' ',$No_Nombre_Cliente);
				if (isset($datos_personales_cliente[0]) != '')
					$No_Primer_Nombre = $datos_personales_cliente[0];
				if (isset($datos_personales_cliente[1]) != '')
					$No_Segundo_Nombre = $datos_personales_cliente[1];
				if (isset($datos_personales_cliente[2]) != '')
					$No_Apellido_Paterno = $datos_personales_cliente[2];
				if (isset($datos_personales_cliente[3]) != '')
					$No_Apellido_Materno = $datos_personales_cliente[3];
			}

			$Nu_Documento_Identidad = trim($row['nu_documento_identidad']);

			if(strlen($Nu_Documento_Identidad) == 11)
				$Nu_Tipo_Documento_Identidad = 6;//RUC
			else if(strlen($Nu_Documento_Identidad) == 8)
				$Nu_Tipo_Documento_Identidad = 1;//DNI
			
			if($row['cli_ndespacho_efectivo'] == '0' && $row['cli_anticipo'] == 'N' && $row['cli_creditosol'] > 0)
				$Ss_Credito_Soles = $row['cli_creditosol'];

			if(isset($row['nu_telefono1']))
				$Nu_Telefono1 = $row['nu_telefono1'];

			$No_Contacto = os_odbcsql_escape($row['no_contacto']);
	        $datos_personales_contacto = explode(' ', $No_Contacto);
			if (isset($datos_personales_contacto[0]) != '')
				$No_Nombre_Contacto = $datos_personales_contacto[0];
			if (isset($datos_personales_contacto[1]) != '')
				$No_Apellido_Contacto = $datos_personales_contacto[1];

			$sql = "
			INSERT INTO BDINT.INTOCRD(
				CARDCODE,
				CARDNAME,
				FEDERALTAXID,
				U_EXX_TIPOPER,
				U_EXO_TIPODOCU,
				U_EXX_APELLPAT,
				U_EXX_APELLMAT,
				U_EXX_PRIMERNO,
				U_EXX_SEGUNDNO,
				PHONE1,
				EMAILADDRESS,
				CREDITLIMIT,
				STREET,
				NAME,
				LASTNAME,
				ESTADO,
				ERRORMSG
			) VALUES (
				'" . trim($row['nu_codigo_cliente']) . "',
				'" . $No_Nombre_Cliente . "',
				'" . $Nu_Documento_Identidad . "',
				'" . $No_Tipo_Persona . "',
				'" . $Nu_Tipo_Documento_Identidad . "',
				'" . $No_Apellido_Paterno . "',
				'" . $No_Apellido_Materno . "',
				'" . $No_Primer_Nombre . "',
				'" . $No_Segundo_Nombre . "',
				" . $Nu_Telefono1 . ",
				'" . $row['txt_email'] . "',
				" . $Ss_Credito_Soles . ",
				'" . $row['txt_direccion'] . "',
				'" . $No_Nombre_Contacto . "',
				'" . $No_Apellido_Contacto . "',
				'P',
				''
			);
			";
			$stmt = odbc_exec($connectHana, $sql);
			if (!$stmt){
				odbc_rollback($connectHana);
				$arrResult['codigo_error'] = odbc_error();
				$arrResult['estado'] = FALSE;
				$arrResult['mensaje'] = odbc_errormsg();
				$arrResult['cantidad_registros'] = $cantidad_registros;
				return $arrResult;
				break;
			}
			++$cantidad_registros;
		}
		unset($arrSocios);
		$arrResult['codigo_error'] = '';
		$arrResult['estado'] = TRUE;
		$arrResult['mensaje'] = 'Registros cargados satisfactoriamente';
		$arrResult['cantidad_registros'] = $cantidad_registros;
		return $arrResult;
	}

	function getEmpleados($connectHana, $nu_almacen, $fe_inicial, $fe_final) {
		global $sqlca;

		$condAlmacenTrabajador = (!empty($nu_almacen) ? "AND PHLTRA.ch_sucursal = '" . $nu_almacen . "'" : NULL);

		$status = $sqlca->query("
        SELECT DISTINCT
            PHLTRA.ch_sucursal || TRA.ch_codigo_trabajador AS id_trabajador,
            TRA.ch_nombre1 AS no_empleado_primer,
            TRA.ch_nombre2 AS no_empleado_segundo,
            TRA.ch_apellido_paterno AS no_apellido_paterno,
            TRA.ch_apellido_materno AS no_apellido_materno
        FROM
            pla_ta_trabajadores AS TRA
            JOIN pos_historia_ladosxtrabajador AS PHLTRA USING (ch_codigo_trabajador)
        WHERE
        	PHLTRA.dt_dia BETWEEN '" . $fe_inicial . "' AND '" . $fe_final . "'
        	" . $condAlmacenTrabajador . "
        ");

		$arrResult['descripcion_tabla'] = 'Empleados';
		$arrResult['tabla'] = 'INTOHEM';
		$arrResult['codigo_error'] = '';
		$arrResult['cantidad_registros'] = $status;

		if($status < 0){
			$arrResult['estado'] = FALSE;
			$arrResult['mensaje'] = 'Error SQL - function getEmpleados';
		} else if($status == 0) {
			$arrResult['estado'] = TRUE;
			$arrResult['mensaje'] = 'No se encontró ningún registro';
		} else {
			$arrEmpleados = $sqlca->fetchAll();
			$arrResult = $this->sendEmpleados($connectHana, $arrEmpleados);

			if($arrResult['codigo_error'] == '23000')
				$arrResult['mensaje'] = 'Ya se migro la información';
			if($arrResult['codigo_error'] == 'S1000')
				$arrResult['mensaje'] = 'No se pueden registrar los empleados porque los tipos de datos de las columnas fueron cambiados en HANA - SAP';

			$arrResult['descripcion_tabla'] = 'Empleados';
			$arrResult['tabla'] = 'INTOHEM';
		}
		return $arrResult;
	}

	function sendEmpleados($connectHana, $arrEmpleados){
		$cantidad_registros = 0;
		$No_Primer_Nombre = '';
		$No_Segundo_Nombre = '';
		$No_Apellido_Paterno = '';
		$No_Apellido_Materno = '';
		foreach ($arrEmpleados as $row) {
			$No_Primer_Nombre = os_odbcsql_escape($row['no_apellido_paterno']);
			$No_Segundo_Nombre = os_odbcsql_escape($row['no_apellido_materno']);
			$No_Apellido_Paterno = os_odbcsql_escape($row['no_empleado_primer']);
			$No_Apellido_Materno = os_odbcsql_escape($row['no_empleado_segundo']);

			$sql = "
			INSERT INTO BDINT.INTOHEM(
				EXTEMPNO,
				NAME,
				LASTNAME,
				ESTADO,
				ERRORMSG,
				DOCENTRY
			) VALUES (
				'" . trim($row['id_trabajador']) . "',
				'" . trim($No_Primer_Nombre) . ' ' . trim($No_Segundo_Nombre) . "',
				'" . trim($No_Apellido_Paterno) . ' ' . trim($No_Apellido_Materno) . "',
				'P',
				'',
				0
			);
			";
			$stmt = odbc_exec($connectHana, $sql);
			if (!$stmt){
				odbc_rollback($connectHana);
				$arrResult['codigo_error'] = odbc_error();
				$arrResult['estado'] = FALSE;
				$arrResult['mensaje'] = odbc_errormsg();
				$arrResult['cantidad_registros'] = $cantidad_registros;
				return $arrResult;
				break;
			}
			++$cantidad_registros;
		}
		unset($arrEmpleados);
		$arrResult['codigo_error'] = '';
		$arrResult['estado'] = TRUE;
		$arrResult['mensaje'] = 'Registros cargados satisfactoriamente';
		$arrResult['cantidad_registros'] = $cantidad_registros;
		return $arrResult;
	}

	function getGuiasND($connectHana, $nu_almacen, $fe_inicial, $fe_final, $pos_transym, $nu_codigo_impuesto, $ss_impuesto, $ss_factor_bonus) {
		global $sqlca;

		$condPTAlmacen = (!empty($nu_almacen) ? "AND PT.es = '" . $nu_almacen . "'" : NULL);
		$sql_ND_Cabecera = "
		SELECT
			PT.es || PT.caja || PT.trans AS Id_Guia_ND,
			PT.cuenta AS Nu_Codigo_Cliente,
			PT.trans AS Nu_Ticket,
			PT.dia AS Fe_Emision,
			COALESCE(MTRABA.ch_codigo_trabajador, '0') AS Nu_Codigo_Empleado,
			CLI.cli_anticipo,
			CLI.cli_ndespacho_efectivo
		FROM
			" . $pos_transym . " AS PT
			JOIN int_clientes AS CLI ON(CLI.cli_codigo = PT.cuenta)
			LEFT JOIN pos_historia_ladosxtrabajador AS MTRABA ON(MTRABA.dt_dia = PT.dia AND MTRABA.ch_posturno::CHAR = PT.turno AND MTRABA.ch_lado = PT.pump)
		WHERE
			PT.td ='N'
			AND PT.grupo != 'D'
			AND PT.dia BETWEEN '" . $fe_inicial . "' AND '" . $fe_final . "'
			" . $condPTAlmacen . "
		";
		/**
		Observaciones - Guia Detalle - ND:
			- Opensoft: 
				Obtener el lado y manguera de la tabla comb_ta_surtidores
				Obtener el codigo de producto de la tabla comb_ta_contometros
			- SAP:
				% Descuento
		*/
		$sql_ND_Detalle = "
		SELECT * FROM (
		SELECT
			PT.es || PT.caja || PT.trans AS Id_Guia_ND,
			FIRST(SAPALMA.sap_codigo) AS ID_Almacen,
			PT.codigo AS Nu_Codigo_Producto,
			SUM(PT.cantidad) AS Qt_Cantidad,
			ROUND((SUM(PT.precio) + FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 0)))) / " . $ss_impuesto . ", 4) AS Ss_Precio_Venta_SIGV,
			ROUND((FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 0)) / " . $ss_impuesto . ") * 100) / ((SUM(PT.precio) + FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 1)))) / " . $ss_impuesto . "), 4) AS Po_Descuento,
			FIRST(SAPCC.sap_codigo) AS ID_Centro_Costo,
			FIRST(SAPLINEA.sap_codigo) AS ID_Linea_Negocio,
			FIRST(PT.pump) AS Nu_Lado,
			PT.caja AS Nu_Caja,
			FIRST(SURTIDOR.nu_manguera)::TEXT AS Nu_Manguera,
			FIRST(PT.turno) AS Nu_Turno,	
			TO_CHAR(FIRST(PT.fecha), 'HH12:MI:SS') AS Fe_Hora,
			FIRST(PT.placa) AS No_Placa,
			FIRST(PT.odometro) AS Nu_Kilometraje,
			TRUNC(SUM(PT.importe / " . $ss_factor_bonus . ")) AS Nu_Cantidad_Puntos_Bonus,
			FIRST(PT.indexa) AS Nu_Tarjeta_Bonus
		FROM
			" . $pos_transym . " AS PT
			JOIN comb_ta_surtidores AS SURTIDOR ON (PT.pump::INTEGER = SURTIDOR.ch_numerolado::INTEGER AND SURTIDOR.ch_codigocombustible = PT.codigo)
			JOIN inv_ta_almacenes AS ALMA ON (ALMA.ch_almacen = PT.es)
			JOIN int_ta_sucursales AS ORG ON (ORG.ch_sucursal = ALMA.ch_sucursal)
			JOIN sap_mapeo_tabla_detalle AS SAPCC ON (SAPCC.opencomb_codigo = ORG.ch_sucursal AND SAPCC.id_tipo_tabla = 1)
			JOIN sap_mapeo_tabla_detalle AS SAPALMA ON (SAPALMA.opencomb_codigo = PT.es AND SAPALMA.id_tipo_tabla = 2)
			JOIN int_articulos AS PRO ON (PRO.art_codigo = PT.codigo)
			LEFT JOIN sap_mapeo_tabla_detalle AS SAPLINEA ON (SAPLINEA.opencomb_codigo = PRO.art_linea AND SAPLINEA.id_tipo_tabla = 3)
			LEFT JOIN (
			SELECT
				PT.es,PT.caja,PT.trans,
				PT.precio AS precio_descuento
			FROM
				" . $pos_transym . " AS PT
			WHERE
				PT.td ='N'
				AND PT.tipo = 'C'
				AND PT.grupo = 'D'
				AND PT.dia BETWEEN '" . $fe_inicial . "' AND '" . $fe_final . "'
				" . $condPTAlmacen . "
			) AS PTDSCT ON (PT.es = PTDSCT.es AND PT.caja = PTDSCT.caja AND PT.trans = PTDSCT.trans)
		WHERE
			PT.td ='N'
			AND PT.tipo = 'C'
			AND PT.dia BETWEEN '" . $fe_inicial . "' AND '" . $fe_final . "'
			" . $condPTAlmacen . "
		GROUP BY
			PT.es,
			PT.caja,
			PT.trans,
			PT.codigo
		) AS A
		UNION
		(
		SELECT
			PT.es || PT.caja || PT.trans AS Id_Guia_ND,
			FIRST(SAPALMA.sap_codigo) AS ID_Almacen,
			PT.codigo AS Nu_Codigo_Producto,
			SUM(PT.cantidad) AS Qt_Cantidad,
			ROUND((SUM(PT.precio) + FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 0)))) / " . $ss_impuesto . ", 4) AS Ss_Precio_Venta_SIGV,
			ROUND((FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 0)) / " . $ss_impuesto . ") * 100) / ((SUM(PT.precio) + FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 1)))) / " . $ss_impuesto . "), 4) AS Po_Descuento,
			FIRST(SAPCC.sap_codigo) AS ID_Centro_Costo,
			FIRST(SAPLINEA.sap_codigo) AS ID_Linea_Negocio,
			FIRST(PT.pump) AS Nu_Lado,
			PT.caja AS Nu_Caja,
			''::TEXT AS Nu_Manguera,
			FIRST(PT.turno) AS Nu_Turno,	
			TO_CHAR(FIRST(PT.fecha), 'HH12:MI:SS') AS Fe_Hora,
			FIRST(PT.placa) AS No_Placa,
			FIRST(PT.odometro) AS Nu_Kilometraje,
			TRUNC(SUM(PT.importe / " . $ss_factor_bonus . ")) AS Nu_Cantidad_Puntos_Bonus,
			FIRST(PT.indexa) AS Nu_Tarjeta_Bonus
		FROM
			" . $pos_transym . " AS PT
			JOIN inv_ta_almacenes AS ALMA ON (ALMA.ch_almacen = PT.es)
			JOIN int_ta_sucursales AS ORG ON (ORG.ch_sucursal = ALMA.ch_sucursal)
			JOIN sap_mapeo_tabla_detalle AS SAPCC ON (SAPCC.opencomb_codigo = ORG.ch_sucursal AND SAPCC.id_tipo_tabla = 1)
			JOIN sap_mapeo_tabla_detalle AS SAPALMA ON (SAPALMA.opencomb_codigo = PT.es AND SAPALMA.id_tipo_tabla = 2)
			JOIN int_articulos AS PRO ON (PRO.art_codigo = PT.codigo)
			LEFT JOIN sap_mapeo_tabla_detalle AS SAPLINEA ON (SAPLINEA.opencomb_codigo = PRO.art_linea AND SAPLINEA.id_tipo_tabla = 3)
			LEFT JOIN (
			SELECT
				PT.es,PT.caja,PT.trans,
				PT.precio AS precio_descuento
			FROM
				" . $pos_transym . " AS PT
			WHERE
				PT.td ='N'
				AND PT.tipo = 'M'
				AND PT.grupo = 'D'
				AND PT.dia BETWEEN '" . $fe_inicial . "' AND '" . $fe_final . "'
				" . $condPTAlmacen . "
			) AS PTDSCT ON (PT.es = PTDSCT.es AND PT.caja = PTDSCT.caja AND PT.trans = PTDSCT.trans)
		WHERE
			PT.td ='N'
			AND PT.tipo = 'M'
			AND PT.dia BETWEEN '" . $fe_inicial . "' AND '" . $fe_final . "'
			" . $condPTAlmacen . "
		GROUP BY
			PT.es,
			PT.caja,
			PT.trans,
			PT.codigo
		);
		";

		$arrResult['descripcion_tabla'] = 'Guía de Cliente';
		$arrResult['tabla'] = 'INTODLN y INTDLN1';
		$arrResult['codigo_error'] = '';
		$arrResult['cantidad_registros'] = 0;

		if (($sqlca->query($sql_ND_Cabecera) < 0)) {
			$arrResult['estado'] = FALSE;
			$arrResult['mensaje'] = 'Error SQL - function getGuiasND Cabecera';
		} else if (($sqlca->query($sql_ND_Cabecera) == 0)) {
			$arrResult['estado'] = TRUE;
			$arrResult['mensaje'] = 'No se encontró ningún registro';
		} else {
			$arrNDCabecera = $sqlca->fetchAll();
			$arrResult = $this->sendGuiasNDCabecera($connectHana, $arrNDCabecera);
			$cantidad_registros = $arrResult['cantidad_registros'];

			if($arrResult['codigo_error'] == '23000')
				$arrResult['mensaje'] = 'Ya se migro la información';
			if($arrResult['codigo_error'] == 'S1000')
				$arrResult['mensaje'] = 'No se pueden registrar notas de despachos Cabecera porque los tipos de datos de las columnas fueron cambiados en HANA - SAP';

			if($arrResult['estado']){
				if (($sqlca->query($sql_ND_Detalle) < 0)) {
					$arrResult['estado'] = FALSE;
					$arrResult['mensaje'] = 'Error SQL - function getGuiasND Detalle';
				} else if (($sqlca->query($sql_ND_Detalle) == 0)) {
					$arrResult['estado'] = TRUE;
					$arrResult['mensaje'] = 'No se encontró ningún registro';
				} else {
					$arrNDDetalle = $sqlca->fetchAll();
					$arrResult = $this->sendGuiasNDDetalle($connectHana, $arrNDDetalle, $nu_codigo_impuesto);

					if($arrResult['codigo_error'] == '23000')
						$arrResult['mensaje'] = 'Ya se migro la información';
					if($arrResult['codigo_error'] == 'S1000')
						$arrResult['mensaje'] = 'No se pueden registrar notas de despachos Detalle porque los tipos de datos de las columnas fueron cambiados en HANA - SAP';
				}
			}
			$arrResult['descripcion_tabla'] = 'Guía de Cliente';
			$arrResult['tabla'] = 'INTODLN y INTDLN1';
			$arrResult['cantidad_registros'] = $cantidad_registros;
		}
		return $arrResult;
	}

	function sendGuiasNDCabecera($connectHana, $arrNDCabecera){
		$cantidad_registros = 1;
		$No_Anticipo_Cobrado = 'N';
		foreach ($arrNDCabecera as $row) {
			if($row['cli_anticipo'] == 'S' && $row['cli_ndespacho_efectivo'] == 0)
				$No_Anticipo_Cobrado = 'Y';
			$sql = "
			INSERT INTO BDINT.INTODLN(
				NOPERACION,
				CARDCODE,
				NTICKET,
				DOCDATE,
				SLPCODE,
				U_EXC_ANTICIPO,
				U_EXC_MARREG,
				U_EXC_COBRADO,
				U_EXC_TIPODOC,
				ESTADO,
				ERRORMSG,
				DOCENTRY
			) VALUES (
				'" . $row['id_guia_nd'] . "',
				'" . trim($row['nu_codigo_cliente']) . "',
				'" . $row['nu_ticket'] . "',
				'" . $row['fe_emision'] . "',
				'" . trim($row['nu_codigo_empleado']) . "',
				'" . $No_Anticipo_Cobrado . "',
				'',
				'" . $No_Anticipo_Cobrado . "',
				'01',
				'P',
				'',
				''
			);
			";
			$stmt = odbc_exec($connectHana, $sql);
			if (!$stmt){
				odbc_rollback($connectHana);
				$arrResult['codigo_error'] = odbc_error();
				$arrResult['estado'] = FALSE;
				$arrResult['mensaje'] = odbc_errormsg();
				$arrResult['cantidad_registros'] = $cantidad_registros;
				return $arrResult;
				break;
			}
			++$cantidad_registros;
		}
		unset($arrNDCabecera);
		$arrResult['codigo_error'] = '';
		$arrResult['estado'] = TRUE;
		$arrResult['mensaje'] = 'Registros cargadas satisfactoriamente';
		$arrResult['cantidad_registros'] = $cantidad_registros;
		return $arrResult;
	}

	function sendGuiasNDDetalle($connectHana, $arrNDDetalle, $nu_codigo_impuesto){
		$item = 1;
		foreach ($arrNDDetalle as $row) {
			$sql = "
			INSERT INTO BDINT.INTDLN1(
				WHSCODE,
				NOPERACION,
				ITEM,
				ITEMCODE,
				QUANTITY,
				PRICE,
				TAXCODE,
				DESCUENTO,
				OCRCODE,
				OCRCODE2,
				U_EXC_DISPENSADOR,
				U_EXC_CAJA,
				U_EXC_MANGUERA,
				U_EXC_TURNO,
				U_EXC_HORA,
				U_EXC_PLACA,
				U_EXC_KM,
				U_EXC_BONUS,
				U_EXC_NROTARJ
			) VALUES (
				'" . $row['id_almacen'] . "',
				'" . $row['id_guia_nd'] . "',
				" . $item . ",
				'" . trim($row['nu_codigo_producto']) . "',
				" . $row['qt_cantidad'] . ",
				" . $row['ss_precio_venta_sigv'] . ",
				'" . $nu_codigo_impuesto . "',
				" . $row['po_descuento'] . ",
				'" . $row['id_centro_costo'] . "',
				'" . $row['id_linea_negocio'] . "',
				'" . $row['nu_lado'] . "',
				'" . $row['nu_caja'] . "',
				'" . $row['nu_manguera'] . "',
				'" . $row['nu_turno'] . "',
				'" . $row['fe_hora'] . "',
				'" . $row['no_placa'] . "',
				'" . $row['nu_kilometraje'] . "',
				'" . $row['nu_cantidad_puntos_bonus'] . "',
				'" . $row['nu_tarjeta_bonus'] . "'
			);
			";
			$stmt = odbc_exec($connectHana, $sql);
			if (!$stmt){
				odbc_rollback($connectHana);
				$arrResult['codigo_error'] = odbc_error();
				$arrResult['estado'] = FALSE;
				$arrResult['mensaje'] = odbc_errormsg();
				return $arrResult;
				break;
			}
			++$item;
		}
		unset($arrNDDetalle);
		unset($item);
		$arrResult['codigo_error'] = '';
		$arrResult['estado'] = TRUE;
		$arrResult['mensaje'] = 'Registros cargadas satisfactoriamente';
		return $arrResult;
	}

	function getFacturasPosTransManualesVentas($connectHana, $nu_almacen, $fe_inicial, $fe_final, $nu_codigo_impuesto, $pos_transym, $ss_impuesto, $ss_factor_bonus) {
		global $sqlca;

		$condAlmacenFactura = (!empty($nu_almacen) ? "AND FC.ch_almacen = '" . $nu_almacen . "'" : NULL);
		$condPTAlmacen = (!empty($nu_almacen) ? "AND PT.es = '" . $nu_almacen . "'" : NULL);

		$sql_DMV_Cabecera = "
		SELECT * FROM (
		SELECT
			FC.ch_almacen || FC.ch_fac_tipodocumento || FC.ch_fac_seriedocumento || FC.ch_fac_numerodocumento AS Id_Factura,
			CLI.cli_codigo AS Nu_Codigo_Cliente,
			FC.ch_fac_seriedocumento AS Nu_Serie_Documento,
			FC.ch_fac_numerodocumento AS Nu_Numero_Documento,
			FC.dt_fac_fecha AS Fe_Emision,
			TD.tab_car_03 AS Nu_Tipo_Documento_SUNAT,
			'' AS Nu_Codigo_Trabajador,
			(CASE WHEN FC.ch_fac_anticipo = 'S' AND (CLI.cli_anticipo = 'S' AND CLI.cli_ndespacho_efectivo = '0') THEN 'Y' ELSE 'N' END) AS No_Anticipo,
			(CASE WHEN FC.ch_fac_anticipo = 'S' AND (CLI.cli_anticipo = 'S' AND CLI.cli_ndespacho_efectivo = '0') THEN 'Y' ELSE 'N' END) AS No_Documento_Cobrado
		FROM
			fac_ta_factura_cabecera AS FC
			JOIN int_clientes AS CLI ON(CLI.cli_codigo = FC.cli_codigo)
			JOIN int_tabla_general AS TD ON(FC.ch_fac_tipodocumento = SUBSTRING(TRIM(tab_elemento) for 2 from length(TRIM(tab_elemento))-1) AND tab_tabla ='08' AND tab_elemento != '000000')
		WHERE
			FC.ch_fac_tipodocumento IN ('10', '35')
			AND FC.dt_fac_fecha BETWEEN '" . $fe_inicial . "' AND '" . $fe_final . "'
			AND SUBSTRING(FC.ch_fac_seriedocumento FROM '[A-Z]+') != ''
			" . $condAlmacenFactura . "
		) AS A
		UNION ALL
		(
		SELECT
			PT.es || PT.caja || PT.trans AS Id_Factura,
			PT.ruc AS Nu_Codigo_Cliente,
			SUBSTR(TRIM(PT.usr), 0, 5) AS Nu_Serie_Documento,
			SUBSTR(TRIM(PT.usr), 6) AS Nu_Numero_Documento,
			PT.dia AS Fe_Emision,
			(CASE
				WHEN PT.tm = 'V' and PT.td = 'F' then '01'
				WHEN PT.tm = 'V' and PT.td = 'B' then '03'
				WHEN PT.tm = 'D' OR PT.tm = 'A' then '07'
			END) AS Nu_Tipo_Documento_SUNAT,
			MTRABA.ch_codigo_trabajador AS Nu_Codigo_Trabajador,
			'N' AS No_Anticipo,
			'N' AS No_Documento_Cobrado
		FROM
			" . $pos_transym . " AS PT
			LEFT JOIN pos_historia_ladosxtrabajador AS MTRABA ON(PT.dia = MTRABA.dt_dia AND PT.turno::INTEGER = MTRABA.ch_posturno AND PT.pump = MTRABA.ch_lado AND PT.tipo = MTRABA.ch_tipo)
		WHERE
			PT.td IN ('B', 'F')
			AND PT.usr != ''
			AND PT.dia BETWEEN '" . $fe_inicial . "' AND '" . $fe_final . "'
			" . $condPTAlmacen . "
		)
		";

		$sql_DMV_Detalle = "
		SELECT * FROM (
		SELECT
			FC.ch_almacen || FC.ch_fac_tipodocumento || FC.ch_fac_seriedocumento || FC.ch_fac_numerodocumento AS Id_Factura,
			SAPALMA.sap_codigo AS ID_Almacen,
			FD.art_codigo AS Nu_Codigo_Producto,
			FD.nu_fac_cantidad AS Qt_Cantidad,
			FD.nu_fac_precio AS Ss_Precio_Venta_SIGV,
			0 AS Po_Descuento,
			SAPCC.sap_codigo AS ID_Centro_Costo,
			SAPLINEA.sap_codigo AS ID_Linea_Negocio,
			'' AS Nu_Lado,
			'' AS Nu_Caja,
			''::TEXT AS Nu_Manguera,
			'' AS Nu_Turno,	
			'' AS Fe_Hora,
			'' AS No_Placa,
			''::TEXT AS Nu_Kilometraje,
			''::TEXT AS Nu_Cantidad_Puntos_Bonus,
			''::TEXT AS Nu_Tarjeta_Bonus
		FROM
			fac_ta_factura_cabecera AS FC
			JOIN fac_ta_factura_detalle AS FD USING (ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento, cli_codigo)
			JOIN inv_ta_almacenes AS ALMA ON (ALMA.ch_almacen = FC.ch_almacen)
			JOIN int_ta_sucursales AS ORG ON (ORG.ch_sucursal = ALMA.ch_sucursal)
			JOIN sap_mapeo_tabla_detalle AS SAPCC ON (SAPCC.opencomb_codigo = ORG.ch_sucursal AND SAPCC.id_tipo_tabla = 1)
			JOIN sap_mapeo_tabla_detalle AS SAPALMA ON (SAPALMA.opencomb_codigo = FC.ch_almacen AND SAPALMA.id_tipo_tabla = 2)
			JOIN int_articulos AS PRO ON (PRO.art_codigo = FD.art_codigo)
			LEFT JOIN sap_mapeo_tabla_detalle AS SAPLINEA ON (SAPLINEA.opencomb_codigo = PRO.art_linea AND SAPLINEA.id_tipo_tabla = 3)
		WHERE
			FC.ch_fac_tipodocumento IN ('10', '35')
			AND FC.dt_fac_fecha BETWEEN '" . $fe_inicial . "' AND '" . $fe_final . "'
			AND SUBSTRING(FC.ch_fac_seriedocumento FROM '[A-Z]+') != ''
			" . $condAlmacenFactura . "
		) AS A
		UNION ALL
		(
		SELECT
			PT.es || PT.caja || PT.trans AS Id_Factura,
			FIRST(SAPALMA.sap_codigo) AS ID_Almacen,
			PT.codigo AS Nu_Codigo_Producto,
			SUM(PT.cantidad) AS Qt_Cantidad,
			ROUND((SUM(PT.precio) + FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 0)))) / " . $ss_impuesto . ", 4) AS Ss_Precio_Venta_SIGV,
			ROUND((FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 0)) / " . $ss_impuesto . ") * 100) / ((SUM(PT.precio) + FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 1)))) / " . $ss_impuesto . "), 4) AS Po_Descuento,
			FIRST(SAPCC.sap_codigo) AS ID_Centro_Costo,
			FIRST(SAPLINEA.sap_codigo) AS ID_Linea_Negocio,
			FIRST(PT.pump) AS Nu_Lado,
			PT.caja AS Nu_Caja,
			FIRST(SURTIDOR.nu_manguera)::TEXT AS Nu_Manguera,
			FIRST(PT.turno) AS Nu_Turno,	
			TO_CHAR(FIRST(PT.fecha), 'HH12:MI:SS') AS Fe_Hora,
			FIRST(PT.placa) AS No_Placa,
			FIRST(PT.odometro)::TEXT AS Nu_Kilometraje,
			TRUNC(SUM(PT.importe / " . $ss_factor_bonus . "))::TEXT AS Nu_Cantidad_Puntos_Bonus,
			FIRST(PT.indexa)::TEXT AS Nu_Tarjeta_Bonus
		FROM
			" . $pos_transym . " AS PT
			JOIN comb_ta_surtidores AS SURTIDOR ON (PT.pump::INTEGER = SURTIDOR.ch_numerolado::INTEGER AND SURTIDOR.ch_codigocombustible = PT.codigo)
			JOIN inv_ta_almacenes AS ALMA ON (ALMA.ch_almacen = PT.es)
			JOIN int_ta_sucursales AS ORG ON (ORG.ch_sucursal = ALMA.ch_sucursal)
			JOIN sap_mapeo_tabla_detalle AS SAPCC ON (SAPCC.opencomb_codigo = ORG.ch_sucursal AND SAPCC.id_tipo_tabla = 1)
			JOIN sap_mapeo_tabla_detalle AS SAPALMA ON (SAPALMA.opencomb_codigo = PT.es AND SAPALMA.id_tipo_tabla = 2)
			JOIN int_articulos AS PRO ON (PRO.art_codigo = PT.codigo)
			LEFT JOIN sap_mapeo_tabla_detalle AS SAPLINEA ON (SAPLINEA.opencomb_codigo = PRO.art_linea AND SAPLINEA.id_tipo_tabla = 3)
			LEFT JOIN (
			SELECT
				PT.es,PT.caja,PT.trans,
				PT.precio AS precio_descuento
			FROM
				" . $pos_transym . " AS PT
			WHERE
				PT.td IN ('B', 'F')
				AND PT.tipo = 'C'
				AND PT.grupo = 'D'
				AND PT.usr != ''
				AND PT.dia BETWEEN '" . $fe_inicial . "' AND '" . $fe_final . "'
				" . $condPTAlmacen . "
			) AS PTDSCT ON (PT.es = PTDSCT.es AND PT.caja = PTDSCT.caja AND PT.trans = PTDSCT.trans)
		WHERE
			PT.td IN ('B', 'F')
			AND PT.tipo = 'C'
			AND PT.usr != ''
			AND PT.dia BETWEEN '" . $fe_inicial . "' AND '" . $fe_final . "'
			" . $condPTAlmacen . "
		GROUP BY
			PT.es,
			PT.caja,
			PT.trans,
			PT.codigo
		)
		UNION ALL
		(
		SELECT
			PT.es || PT.caja || PT.trans AS Id_Factura,
			FIRST(SAPALMA.sap_codigo) AS ID_Almacen,
			PT.codigo AS Nu_Codigo_Producto,
			SUM(PT.cantidad) AS Qt_Cantidad,
			ROUND((SUM(PT.precio) + FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 0)))) / " . $ss_impuesto . ", 4) AS Ss_Precio_Venta_SIGV,
			ROUND((FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 0)) / " . $ss_impuesto . ") * 100) / ((SUM(PT.precio) + FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 1)))) / " . $ss_impuesto . "), 4) AS Po_Descuento,
			FIRST(SAPCC.sap_codigo) AS ID_Centro_Costo,
			FIRST(SAPLINEA.sap_codigo) AS ID_Linea_Negocio,
			FIRST(PT.pump) AS Nu_Lado,
			PT.caja AS Nu_Caja,
			''::TEXT AS Nu_Manguera,
			FIRST(PT.turno) AS Nu_Turno,	
			TO_CHAR(FIRST(PT.fecha), 'HH12:MI:SS') AS Fe_Hora,
			FIRST(PT.placa) AS No_Placa,
			FIRST(PT.odometro)::TEXT AS Nu_Kilometraje,
			TRUNC(SUM(PT.importe / " . $ss_factor_bonus . "))::TEXT AS Nu_Cantidad_Puntos_Bonus,
			FIRST(PT.indexa)::TEXT AS Nu_Tarjeta_Bonus
		FROM
			" . $pos_transym . " AS PT
			JOIN inv_ta_almacenes AS ALMA ON (ALMA.ch_almacen = PT.es)
			JOIN int_ta_sucursales AS ORG ON (ORG.ch_sucursal = ALMA.ch_sucursal)
			JOIN sap_mapeo_tabla_detalle AS SAPCC ON (SAPCC.opencomb_codigo = ORG.ch_sucursal AND SAPCC.id_tipo_tabla = 1)
			JOIN sap_mapeo_tabla_detalle AS SAPALMA ON (SAPALMA.opencomb_codigo = PT.es AND SAPALMA.id_tipo_tabla = 2)
			JOIN int_articulos AS PRO ON (PRO.art_codigo = PT.codigo)
			LEFT JOIN sap_mapeo_tabla_detalle AS SAPLINEA ON (SAPLINEA.opencomb_codigo = PRO.art_linea AND SAPLINEA.id_tipo_tabla = 3)
			LEFT JOIN (
			SELECT
				PT.es,PT.caja,PT.trans,
				PT.precio AS precio_descuento
			FROM
				" . $pos_transym . " AS PT
			WHERE
				PT.td IN ('B', 'F')
				AND PT.tipo = 'M'
				AND PT.grupo = 'D'
				AND PT.usr != ''
				AND PT.dia BETWEEN '" . $fe_inicial . "' AND '" . $fe_final . "'
				" . $condPTAlmacen . "
			) AS PTDSCT ON (PT.es = PTDSCT.es AND PT.caja = PTDSCT.caja AND PT.trans = PTDSCT.trans)
		WHERE
			PT.td IN ('B', 'F')
			AND PT.tipo = 'M'
			AND PT.usr != ''
			AND PT.dia BETWEEN '" . $fe_inicial . "' AND '" . $fe_final . "'
			" . $condPTAlmacen . "
		GROUP BY
			PT.es,
			PT.caja,
			PT.trans,
			PT.codigo
		);
		";

		$arrResult['descripcion_tabla'] = 'Factura de Cliente';
		$arrResult['tabla'] = 'INTOINV y INTINV1';
		$arrResult['codigo_error'] = '';
		$arrResult['cantidad_registros'] = 0;

		if (($sqlca->query($sql_DMV_Cabecera) < 0)) {
			$arrResult['estado'] = FALSE;
			$arrResult['mensaje'] = 'Error SQL - function getFacturasPosTransManualesVentas Cabecera';
		} else if (($sqlca->query($sql_DMV_Cabecera) == 0)) {
			$arrResult['estado'] = TRUE;
			$arrResult['mensaje'] = 'No se encontró ningún registro';
		} else {
			$arrFMVCabecera = $sqlca->fetchAll();
			$arrResult = $this->sendFacturasPosTransManualesVentas($connectHana, $arrFMVCabecera);
			$cantidad_registros = $arrResult['cantidad_registros'];

			if($arrResult['codigo_error'] == '23000')
				$arrResult['mensaje'] = 'Ya se migro la información';
			if($arrResult['codigo_error'] == 'S1000')
				$arrResult['mensaje'] = 'No se pueden registrar documentos manuales de ventas / pos_transym - Cabecera, porque los tipos de datos de las columnas fueron cambiados en HANA - SAP';

			if($arrResult['estado']){
				if (($sqlca->query($sql_DMV_Detalle) < 0)) {
					$arrResult['estado'] = FALSE;
					$arrResult['mensaje'] = 'Error SQL - function getFacturasPosTransManualesVentas Detalle';
				} else if (($sqlca->query($sql_DMV_Detalle) == 0)) {
					$arrResult['estado'] = TRUE;
					$arrResult['mensaje'] = 'No se encontró ningún registro';
				} else {
					$arrFMVDetalle = $sqlca->fetchAll();
					$arrResult = $this->sendFacturasPosTransManualesVentasDetalle($connectHana, $arrFMVDetalle, $nu_codigo_impuesto);

					if($arrResult['codigo_error'] == '23000')
						$arrResult['mensaje'] = 'Ya se migro la información';
					if($arrResult['codigo_error'] == 'S1000')
						$arrResult['mensaje'] = 'No se pueden registrar documentos manuales / pos_transym de ventas - Detalle, porque los tipos de datos de las columnas fueron cambiados en HANA - SAP';
				}
			}

			$arrResult['descripcion_tabla'] = 'Factura de Cliente';
			$arrResult['tabla'] = 'INTOINV y INTINV1';
			$arrResult['cantidad_registros'] = $cantidad_registros;
		}
		return $arrResult;
	}

	function sendFacturasPosTransManualesVentas($connectHana, $arrFMVCabecera){
		$cantidad_registros = 1;
		foreach ($arrFMVCabecera as $row) {
			$nu_codigo_trabajador = trim($row['nu_codigo_trabajador']);
			$sql = "
			INSERT INTO BDINT.INTOINV(
				NOPERACION,
				CARDCODE,
				FOLIOPREF,
				FOLIONUM,
				DOCDATE,
				INDICATOR,
				SLPCODE,
				U_EXC_ANTICIPO,
				U_EXC_MARREG,
				U_EXC_COBRADO,
				ESTADO,
				ERRORMSG,
				DOCENTRY
			) VALUES (
				'" . $row['id_factura'] . "',
				'" . trim($row['nu_codigo_cliente']) . "',
				'" . trim($row['nu_serie_documento']) . "',
				" . $row['nu_numero_documento'] . ",
				'" . $row['fe_emision'] . "',
				'" . $row['nu_tipo_documento_sunat'] . "',
				'" . ($nu_codigo_trabajador == '' ? '-' : $nu_codigo_trabajador) . "',
				'" . $row['no_anticipo'] . "',
				'',
				'" . $row['no_documento_cobrado'] . "',
				'P',
				'',
				''
			);
			";
			$stmt = odbc_exec($connectHana, $sql);
			if (!$stmt){
				odbc_rollback($connectHana);
				$arrResult['codigo_error'] = odbc_error();
				$arrResult['estado'] = FALSE;
				$arrResult['mensaje'] = odbc_errormsg();
				$arrResult['cantidad_registros'] = $cantidad_registros;
				return $arrResult;
				break;
			}
			++$cantidad_registros;
		}
		unset($arrFMVCabecera);
		$arrResult['codigo_error'] = '';
		$arrResult['estado'] = TRUE;
		$arrResult['mensaje'] = 'Registros cargados satisfactoriamente';
		$arrResult['cantidad_registros'] = $cantidad_registros;
		return $arrResult;
	}

	function sendFacturasPosTransManualesVentasDetalle($connectHana, $arrFMVDetalle, $nu_codigo_impuesto){
		$item = 1;
		foreach ($arrFMVDetalle as $row) {
			$sql = "
			INSERT INTO BDINT.INTINV1(
				WHSCODE,
				NOPERACION,
				ITEM,
				ITEMCODE,
				QUANTITY,
				PRICE,
				TAXCODE,
				DESCUENTO,
				OCRCODE,
				OCRCODE2,
				U_EXC_DISPENSADOR,
				U_EXC_CAJA,
				U_EXC_MANGUERA,
				U_EXC_TURNO,
				U_EXC_HORA,
				U_EXC_PLACA,
				U_EXC_KM,
				U_EXC_BONUS,
				U_EXC_NROTARJ,
				SPLCODE
			) VALUES (
				'" . $row['id_almacen'] . "',
				'" . $row['id_factura'] . "',
				" . $item . ",
				'" . trim($row['nu_codigo_producto']) . "',
				" . $row['qt_cantidad'] . ",
				" . $row['ss_precio_venta_sigv'] . ",
				'" . $nu_codigo_impuesto . "',
				" . $row['po_descuento'] . ",
				'" . $row['id_centro_costo'] . "',
				'" . $row['id_linea_negocio'] . "',
				'" . $row['nu_lado'] . "',
				'" . $row['nu_caja'] . "',
				'" . $row['nu_manguera'] . "',
				'" . $row['nu_turno'] . "',
				'" . $row['fe_hora'] . "',
				'" . $row['no_placa'] . "',
				'" . $row['nu_kilometraje'] . "',
				'" . $row['nu_cantidad_puntos_bonus'] . "',
				'" . $row['nu_tarjeta_bonus'] . "',
				'-'
			);
			";
			$stmt = odbc_exec($connectHana, $sql);
			if (!$stmt){
				odbc_rollback($connectHana);
				$arrResult['codigo_error'] = odbc_error();
				$arrResult['estado'] = FALSE;
				$arrResult['mensaje'] = odbc_errormsg();
				return $arrResult;
				break;
			}
			++$item;
		}
		unset($item);
		unset($arrFMVDetalle);
		$arrResult['codigo_error'] = '';
		$arrResult['estado'] = TRUE;
		$arrResult['mensaje'] = 'Registros cargados satisfactoriamente';
		return $arrResult;
	}

	function getNCManualesPosTransVentas($connectHana, $nu_almacen, $fe_inicial, $fe_final, $pos_transym) {
		global $sqlca;

		$condAlmacenFactura = (!empty($nu_almacen) ? "AND FC.ch_almacen = '" . $nu_almacen . "'" : NULL);
		$condAlmacenPostrans = (!empty($nu_almacen) ? "AND PT.es = '" . $nu_almacen . "'" : NULL);

		$status = $sqlca->query("
		SELECT * FROM (
		SELECT
			FC.ch_almacen || FC.ch_fac_tipodocumento || FC.ch_fac_seriedocumento || FC.ch_fac_numerodocumento AS Id_NC,
			(CASE WHEN FC.ch_fac_tipodocumento IN ('11', '20') THEN
				CASE WHEN RPT.nu_serie_documento IS NULL OR RPT.nu_serie_documento = '' THEN
					TD.tab_car_03||RFC.nu_serie_documento||RFC.nu_numero_documento
				ELSE
					RPT.nu_tipo_documento||RPT.nu_serie_documento||RPT.nu_numero_documento
				END
			ELSE
				'-'
			END) AS Nu_Referencia_Documento_Inicial,
			FC.dt_fac_fecha AS Fe_Emision,
			(CASE WHEN RPT.nu_serie_documento IS NULL OR RPT.nu_serie_documento = '' THEN RFC.no_tipo_documento_origen::TEXT ELSE 'B'::TEXT END) AS no_tipo_documento_origen,
			(CASE WHEN FC.ch_fac_anticipo = 'S' AND (CLI.cli_anticipo = 'S' AND CLI.cli_ndespacho_efectivo = '0') THEN 'Y' ELSE 'N' END) AS No_Documento_Cobrado
		FROM
			fac_ta_factura_cabecera AS FC
			JOIN fac_ta_factura_complemento AS FCOM USING (ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento, cli_codigo)
			JOIN int_tabla_general AS TD ON(FC.ch_fac_tipodocumento = SUBSTRING(TRIM(tab_elemento) for 2 from length(TRIM(tab_elemento))-1) AND tab_tabla ='08' AND tab_elemento != '000000')
			JOIN int_clientes AS CLI ON(CLI.cli_codigo = FC.cli_codigo)
			JOIN inv_ta_almacenes AS ALMA ON (ALMA.ch_almacen = FC.ch_almacen)
			JOIN int_ta_sucursales AS ORG ON (ORG.ch_sucursal = ALMA.ch_sucursal)
			LEFT JOIN (
			SELECT
				ch_fac_tipodocumento AS nu_tipo_documento,
				ch_fac_seriedocumento AS nu_serie_documento,
				ch_fac_numerodocumento AS nu_numero_documento,
				(CASE WHEN ch_liquidacion != '' THEN 'B' ELSE 'A' END) AS no_tipo_documento_origen
			FROM
				fac_ta_factura_cabecera
				LEFT JOIN int_tabla_general AS TDSUNAT ON(ch_fac_tipodocumento = substring(TRIM(TDSUNAT.tab_elemento) for 2 from length(TRIM(TDSUNAT.tab_elemento))-1) AND TDSUNAT.tab_tabla ='08' AND TDSUNAT.tab_elemento != '000000')
			WHERE
				SUBSTRING(ch_fac_seriedocumento FROM '[A-Z]+') != ''
			) AS RFC ON (
				RFC.nu_numero_documento = (string_to_array(FCOM.ch_fac_observacion2, '*'))[1]
				AND RFC.nu_serie_documento = (string_to_array(FCOM.ch_fac_observacion2, '*'))[2]
				AND RFC.nu_tipo_documento = (string_to_array(FCOM.ch_fac_observacion2, '*'))[3]
			)
			LEFT JOIN (
			SELECT
				(CASE
					WHEN td = 'F' THEN '01'
					WHEN td = 'B' THEN '03'
				END) AS nu_tipo_documento,
				SUBSTR(usr, 0, 5) AS nu_serie_documento,
				SUBSTR(usr, 6) AS nu_numero_documento
			FROM
				" . $pos_transym . " AS PT
			WHERE
				dia BETWEEN '" . $fe_inicial . "' AND '" . $fe_final . "'
				AND tm IN ('V')
				AND td IN ('B','F')
				AND usr != ''
				AND grupo != 'D'
				" . $condAlmacenPostrans . "
			) AS RPT ON (
				RPT.nu_numero_documento = (string_to_array(FCOM.ch_fac_observacion2, '*'))[1]
				AND RPT.nu_serie_documento = (string_to_array(FCOM.ch_fac_observacion2, '*'))[2]
				AND RPT.nu_tipo_documento = (string_to_array(FCOM.ch_fac_observacion2, '*'))[3]
			)
		WHERE
			FC.ch_fac_tipodocumento = '20'
			AND FC.dt_fac_fecha BETWEEN '" . $fe_inicial . "' AND '" . $fe_final . "'
			AND SUBSTRING(FC.ch_fac_seriedocumento FROM '[A-Z]+') != ''
			" . $condAlmacenFactura . "
		) AS A
		UNION
		(
		SELECT
			PT.es || PT.caja || PT.trans AS Id_NC,
			PTNC.nu_tipo_documento_original||PTNC.nu_serie_numero_documento_original AS Nu_Referencia_Documento_Inicial,
			PT.dia AS Fe_Emision,
			'B' AS no_tipo_documento_origen,
			'N' AS No_Documento_Cobrado
		FROM
			" . $pos_transym . " AS PT
			LEFT JOIN (
			SELECT
				rendi_gln,
				(CASE
					WHEN td = 'B' THEN '03'
					WHEN td = 'F' THEN '01'
				END) AS nu_tipo_documento_original,
				usr AS nu_serie_numero_documento_original
			FROM
				" . $pos_transym . " AS PT
			WHERE
				dia BETWEEN '" . $fe_inicial . "' AND '" . $fe_final . "'
				AND tm IN ('V')
				AND td IN ('B','F')
				AND usr != ''
				AND grupo != 'D'
				" . $condAlmacenPostrans . "
			) AS PTNC ON (PTNC.rendi_gln = PT.trans)
		WHERE
			dia BETWEEN '" . $fe_inicial . "' AND '" . $fe_final . "'
			AND PT.tm IN ('A','D')
			AND PT.td IN ('B','F')
			AND PT.usr != ''
			AND PT.grupo != 'D'
			" . $condAlmacenPostrans . "
		)
		");

		$arrResult['descripcion_tabla'] = 'Nota de Créditos';
		$arrResult['tabla'] = 'INTORIN';
		$arrResult['codigo_error'] = '';
		$arrResult['cantidad_registros'] = $status;// status == Tambien contiene la cantidad de registros

		if($status < 0){
			$arrResult['estado'] = FALSE;
			$arrResult['mensaje'] = 'Error SQL - function getNCManualesPosTransVentas';
		} else if($status == 0) {
			$arrResult['estado'] = TRUE;
			$arrResult['mensaje'] = 'No se encontró ningún registro';
		} else {
			$arrNCMPTV = $sqlca->fetchAll();
			$arrResult = $this->sendNCManualesPosTransVentas($connectHana, $arrNCMPTV);

			if($arrResult['codigo_error'] == '23000')
				$arrResult['mensaje'] = 'Ya se migro la información';
			if($arrResult['codigo_error'] == 'S1000')
				$arrResult['mensaje'] = 'No se pueden registrar las NC / NC pos_trans porque los tipos de datos de las columnas fueron cambiados en HANA - SAP';

			$arrResult['descripcion_tabla'] = 'Notas de Crédito';
			$arrResult['tabla'] = 'INTORIN';
		}
		return $arrResult;
	}

	function sendNCManualesPosTransVentas($connectHana, $arrNCMPTV){
		$cantidad_registros = 1;
		foreach ($arrNCMPTV as $row) {
			$sql = "
			INSERT INTO BDINT.INTORIN(
				NOPERACION,
				NOPERACIONREF,
				DOCDATE,
				TIPO,
				U_EXC_COBRADO,
				ESTADO,
				ERRORMSG,
				DOCENTRY
			) VALUES (
				'" . $row['id_nc'] . "',
				'" . $row['nu_referencia_documento_inicial'] . "',
				'" . $row['fe_emision'] . "',
				'" . $row['no_tipo_documento_origen'] . "',
				'" . $row['no_documento_cobrado'] . "',
				'P',
				'',
				''
			);
			";
			$stmt = odbc_exec($connectHana, $sql);
			if (!$stmt){
				odbc_rollback($connectHana);
				$arrResult['codigo_error'] = odbc_error();
				$arrResult['estado'] = FALSE;
				$arrResult['mensaje'] = odbc_errormsg();
				$arrResult['cantidad_registros'] = $cantidad_registros;
				return $arrResult;
				break;
			}
			++$cantidad_registros;
		}
		unset($arrNCMPTV);
		$arrResult['codigo_error'] = '';
		$arrResult['estado'] = TRUE;
		$arrResult['mensaje'] = 'Registros cargados satisfactoriamente';
		$arrResult['cantidad_registros'] = $cantidad_registros;
		return $arrResult;
	}

	function getAnticipoManualesVentas($connectHana, $nu_almacen, $fe_inicial, $fe_final, $ss_impuesto) {
		global $sqlca;

		$condAlmacenCXCobrar = (!empty($nu_almacen) ? "AND CC.ch_sucursal = '" . $nu_almacen . "'" : NULL);

		$sql_AMV_Cabecera = "
		SELECT
			CC.ch_sucursal || CC.ch_tipdocumento || CC.ch_seriedocumento || CC.ch_numdocumento AS Id_Anticipo,
			CC.dt_fechaemision AS Fe_Emision,
			RFC.nu_tipo_documento AS Nu_Tipo_Documento_Origen,
			CC.ch_moneda AS id_moneda,
			CC.ch_seriedocumento AS Nu_Serie_Documento,
			CC.ch_numdocumento AS Nu_Numero_Documento
		FROM
			ccob_ta_cabecera AS CC
			JOIN (
			SELECT
				TD.tab_car_03 AS nu_tipo_documento,
				ch_fac_seriedocumento AS nu_serie_documento,
				ch_fac_numerodocumento AS nu_numero_documento
			FROM
				fac_ta_factura_cabecera
				JOIN int_tabla_general AS TD ON(ch_fac_tipodocumento = SUBSTRING(TRIM(tab_elemento) for 2 from length(TRIM(tab_elemento))-1) AND tab_tabla ='08' AND tab_elemento != '000000')
			WHERE
				ch_fac_anticipo = 'S'
				AND SUBSTRING(ch_fac_seriedocumento FROM '[A-Z]+') != ''
			) AS RFC ON (
				RFC.nu_tipo_documento = '10'
				AND RFC.nu_serie_documento = CC.ch_seriedocumento
				AND RFC.nu_numero_documento = CC.ch_numdocumento
			)
		WHERE
			CC.ch_tipdocumento = '21'
			AND CC.dt_fechaemision BETWEEN '" . $fe_inicial . "' AND '" . $fe_final . "'
			AND SUBSTRING(CC.ch_seriedocumento FROM '[A-Z]+') != ''
			" . $condAlmacenCXCobrar . "
		";

		$sql_AMV_Detalle = "
		SELECT
			CC.ch_sucursal || CC.ch_tipdocumento || CC.ch_seriedocumento || CC.ch_numdocumento AS Id_Anticipo,
			RFC.Nu_Codigo_Producto,
			RFC.Ss_Cantidad,
			RFC.Ss_Precio_Venta_SIGV,
			RFC.ID_Centro_Costo,
			RFC.ID_Linea_Negocio
		FROM
			ccob_ta_cabecera AS CC
			JOIN (
			SELECT
				FC.ch_fac_tipodocumento AS nu_tipo_documento,
				FC.ch_fac_seriedocumento AS nu_serie_documento,
				FC.ch_fac_numerodocumento AS nu_numero_documento,
				FD.art_codigo AS Nu_Codigo_Producto,
				FD.nu_fac_cantidad AS Ss_Cantidad,
				ROUND((FD.nu_fac_precio / " . $ss_impuesto . "), 4) AS Ss_Precio_Venta_SIGV,
				SAPCC.sap_codigo AS ID_Centro_Costo,
				SAPLINEA.sap_codigo AS ID_Linea_Negocio
			FROM
				fac_ta_factura_cabecera AS FC
				JOIN fac_ta_factura_detalle AS FD USING (ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento, cli_codigo)
				JOIN inv_ta_almacenes AS ALMA ON (ALMA.ch_almacen = FC.ch_almacen)
				JOIN int_ta_sucursales AS ORG ON (ORG.ch_sucursal = ALMA.ch_sucursal)
				JOIN sap_mapeo_tabla_detalle AS SAPCC ON (SAPCC.opencomb_codigo = ORG.ch_sucursal AND SAPCC.id_tipo_tabla = 1)
				JOIN int_articulos AS PRO ON (PRO.art_codigo = FD.art_codigo)
				LEFT JOIN sap_mapeo_tabla_detalle AS SAPLINEA ON (SAPLINEA.opencomb_codigo = PRO.art_linea AND SAPLINEA.id_tipo_tabla = 3)
			WHERE
				FC.ch_fac_tipodocumento = '10'		
				AND FC.ch_fac_anticipo = 'S'
				AND SUBSTRING(FC.ch_fac_seriedocumento FROM '[A-Z]+') != ''
			) AS RFC ON (
				RFC.nu_tipo_documento = '10'
				AND RFC.nu_serie_documento = CC.ch_seriedocumento
				AND RFC.nu_numero_documento = CC.ch_numdocumento
			)
		WHERE
			CC.ch_tipdocumento = '21'
			AND CC.dt_fechaemision BETWEEN '" . $fe_inicial . "' AND '" . $fe_final . "'
			AND SUBSTRING(CC.ch_seriedocumento FROM '[A-Z]+') != ''
			" . $condAlmacenCXCobrar . "
		";

		$arrResult['descripcion_tabla'] = 'Anticipos de Clientes';
		$arrResult['tabla'] = 'INTODPI y INTDPI1';
		$arrResult['codigo_error'] = '';
		$arrResult['cantidad_registros'] = 0;

		if (($sqlca->query($sql_AMV_Cabecera) < 0)) {
			$arrResult['estado'] = FALSE;
			$arrResult['mensaje'] = 'Error SQL - function getAnticipoManualesVentas Cabecera';
		} else if (($sqlca->query($sql_AMV_Cabecera) == 0)) {
			$arrResult['estado'] = TRUE;
			$arrResult['mensaje'] = 'No se encontró ningún registro';
		} else {
			$arrAMVCabecera = $sqlca->fetchAll();
			$arrResult = $this->sendAnticipoManualesVentasCabecera($connectHana, $arrAMVCabecera);
			$cantidad_registros = $arrResult['cantidad_registros'];

			if($arrResult['codigo_error'] == '23000')
				$arrResult['mensaje'] = 'Ya se migro la información';
			if($arrResult['codigo_error'] == 'S1000')
				$arrResult['mensaje'] = 'No se pueden registrar anticipos manuales de ventas - Cabecera, porque los tipos de datos de las columnas fueron cambiados en HANA - SAP';

			if($arrResult['estado']){
				if (($sqlca->query($sql_AMV_Detalle) < 0)) {
					$arrResult['estado'] = FALSE;
					$arrResult['mensaje'] = 'Error SQL - function getAnticipoManualesVentas Detalle';
				} else if (($sqlca->query($sql_AMV_Detalle) == 0)) {
					$arrResult['estado'] = TRUE;
					$arrResult['mensaje'] = 'No se encontró ningún registro';
				} else {
					$arrAMVDetalle = $sqlca->fetchAll();
					$arrResult = $this->sendAnticipoManualesVentasDetalle($connectHana, $arrAMVDetalle);

					if($arrResult['codigo_error'] == '23000')
						$arrResult['mensaje'] = 'Ya se migro la información';
					if($arrResult['codigo_error'] == 'S1000')
						$arrResult['mensaje'] = 'No se pueden registrar anticipos manuales de ventas - Detalle, porque los tipos de datos de las columnas fueron cambiados en HANA - SAP';
				}
			}
			$arrResult['descripcion_tabla'] = 'Anticipos de Clientes';
			$arrResult['tabla'] = 'INTODPI y INTDPI1';
			$arrResult['cantidad_registros'] = $cantidad_registros;
		}
		return $arrResult;
	}

	function sendAnticipoManualesVentasCabecera($connectHana, $arrAMVCabecera){
		$cantidad_registros = 1;
		$nu_codigo_moneda = '';
		foreach ($arrAMVCabecera as $row) {
			$nu_codigo_moneda = ($row['id_moneda'] == '01' ? 'SOL' : 'USD');
			$sql = "
			INSERT INTO BDINT.INTODPI(
				NOPERACION,
				DOCDATE,
				CARDCODE,
				U_EXC_MONEDA,
				FOLIOPREF,
				FOLIONUM,
				SLPCODE,
				ESTADO,
				ERRORMSG,
				DOCENTRY
			) VALUES (
				'" . $row['id_anticipo'] . "',
				'" . $row['fe_emision'] . "',
				'" . $row['nu_tipo_documento_origen'] . "',
				'" . $nu_codigo_moneda . "',
				'" . $row['nu_serie_documento'] . "',
				" . $row['nu_numero_documento'] . ",
				0,
				'P',
				'',
				''
			);
			";
			$stmt = odbc_exec($connectHana, $sql);
			if (!$stmt){
				odbc_rollback($connectHana);
				$arrResult['codigo_error'] = odbc_error();
				$arrResult['estado'] = FALSE;
				$arrResult['mensaje'] = odbc_errormsg();
				$arrResult['cantidad_registros'] = $cantidad_registros;
				return $arrResult;
				break;
			}
			++$cantidad_registros;
		}
		unset($arrAMVCabecera);
		$arrResult['codigo_error'] = '';
		$arrResult['estado'] = TRUE;
		$arrResult['mensaje'] = 'Registros cargados satisfactoriamente';
		$arrResult['cantidad_registros'] = $cantidad_registros;
		return $arrResult;
	}

	function sendAnticipoManualesVentasDetalle($connectHana, $arrAMVDetalle){
		$item = 1;
		$cantidad_registros = 1;
		foreach ($arrAMVDetalle as $row) {
			$sql = "
			INSERT INTO BDINT.INTDPI1(
				NOPERACION,
				ITEM,
				ITEMCODE,
				QUANTITY,
				PRICE,
				OCRCODE,
				OCRCODE2
			) VALUES (
				'" . $row['id_anticipo'] . "',
				" . $item . ",
				'" . trim($row['nu_codigo_producto']) . "',
				" . $row['ss_cantidad'] . ",
				" . $row['ss_precio_venta_sigv'] . ",
				'" . $row['id_centro_costo'] . "',
				'" . $row['id_linea_negocio'] . "',
			);
			";
			$stmt = odbc_exec($connectHana, $sql);
			if (!$stmt){
				odbc_rollback($connectHana);
				$arrResult['codigo_error'] = odbc_error();
				$arrResult['estado'] = FALSE;
				$arrResult['mensaje'] = odbc_errormsg();
				$arrResult['cantidad_registros'] = $cantidad_registros;
				return $arrResult;
				break;
			}
			++$item;
			++$cantidad_registros;
		}
		unset($arrAMVDetalle);
		$arrResult['codigo_error'] = '';
		$arrResult['estado'] = TRUE;
		$arrResult['mensaje'] = 'Registros cargados satisfactoriamente';
		$arrResult['cantidad_registros'] = $cantidad_registros;
		return $arrResult;
	}

	function getContometros($connectHana, $nu_almacen, $fe_inicial, $fe_final) {
		global $sqlca;
		//FUNCIONA DE FORMA DIARIA
		$status = $sqlca->query("
		SELECT
			SURTIDOR.ch_sucursal || PC.cnt AS Id_contometro,
			PC.dia AS Fe_Emision,
			PC.turno AS Nu_Turno,
			PARTE.ch_codigocombustible AS Nu_Codigo_Producto,
			(CASE
				WHEN PC.turno = 1 THEN
					PCANT.Nu_Contometro_Inicial_Soles
				WHEN PC.turno > 1 THEN
					(SELECT cnt_val FROM pos_contometros WHERE dia = PC.dia AND num_lado = PC.num_lado AND manguera = PC.manguera AND turno = PC.turno - integer '1')
			END) AS Nu_Contometro_Inicial_Soles,
			PC.cnt_val AS Nu_Contometro_Final_Soles,
			PC.manguera AS Nu_Manguera,
			(CASE
				WHEN PC.turno = 1 THEN
					PCANT.Nu_Contometro_Inicial_Cantidad
				WHEN PC.turno > 1 THEN
					(SELECT cnt_vol FROM pos_contometros WHERE dia = PC.dia AND num_lado = PC.num_lado AND manguera = PC.manguera AND turno = PC.turno - integer '1')
			END) AS Nu_Contometro_Inicial_Cantidad,
			PC.cnt_vol AS Nu_Contometro_Final_Cantidad,
			PC.num_lado AS Nu_Lado,
			SAPCC.sap_codigo AS ID_Centro_Costo
		FROM
			pos_contometros AS PC
			LEFT JOIN comb_ta_surtidores AS SURTIDOR ON (num_lado = SURTIDOR.ch_numerolado::INTEGER AND PC.manguera = SURTIDOR.nu_manguera)
			LEFT JOIN comb_ta_contometros AS PARTE ON (dia = PARTE.dt_fechaparte AND PARTE.ch_sucursal= SURTIDOR.ch_sucursal AND PARTE.ch_surtidor = SURTIDOR.ch_surtidor)
			JOIN sap_mapeo_tabla_detalle AS SAPCC ON (SAPCC.opencomb_codigo = SURTIDOR.ch_sucursal AND SAPCC.id_tipo_tabla = 1)
			LEFT JOIN (
			SELECT
				dia + integer '1' dia,
				turno,
				num_lado,
				manguera,
				MAX(cnt_val) AS Nu_Contometro_Inicial_Soles,
				MAX(cnt_vol) AS Nu_Contometro_Inicial_Cantidad
			FROM
				pos_contometros
			WHERE
				dia = (date '" . $fe_inicial . "' - integer '1')
				AND turno = (
				SELECT
					MAX(turno)
				FROM
					pos_contometros
				WHERE
					dia = (date '" . $fe_inicial . "' - integer '1')
				)
			GROUP BY
				1,2,3,4
			) AS PCANT USING(dia,num_lado,manguera)
		WHERE
			PC.dia BETWEEN '" . $fe_inicial . "' AND '" . $fe_final . "'
			AND PARTE.dt_fechaparte BETWEEN '" . $fe_inicial . "' AND '" . $fe_final . "'
		ORDER BY
			PC.dia,PC.turno,PC.num_lado,PC.manguera;
		");

		$arrResult['descripcion_tabla'] = 'Contómetro';
		$arrResult['tabla'] = 'INTCONTOM';
		$arrResult['codigo_error'] = '';
		$arrResult['cantidad_registros'] = $status;// status == Tambien contiene la cantidad de registros

		if($status < 0){
			$arrResult['estado'] = FALSE;
			$arrResult['mensaje'] = 'Error SQL - function getContometros';
		} else if($status == 0) {
			$arrResult['estado'] = TRUE;
			$arrResult['mensaje'] = 'No se encontró ningún registro';
		} else {
			$arrContometros = $sqlca->fetchAll();
			$arrResult = $this->sendContometros($connectHana, $arrContometros);

			if($arrResult['codigo_error'] == '23000')
				$arrResult['mensaje'] = 'Ya se migro la información';
			if($arrResult['codigo_error'] == 'S1000')
				$arrResult['mensaje'] = 'No se pueden registrar Contometros, porque los tipos de datos de las columnas fueron cambiados en HANA - SAP';

			$arrResult['descripcion_tabla'] = 'Contómetro';
			$arrResult['tabla'] = 'INTCONTOM';
		}
		return $arrResult;
	}

	function sendContometros($connectHana, $arrContometros){
		$cantidad_registros = 1;

		foreach ($arrContometros as $row) {
			$sql = "
			INSERT INTO BDINT.INTCONTOM(
				NOPERACION,
				U_EXC_FECHA,
				U_EXC_TURNO,
				U_EXC_ARTICULO,
				U_EXC_CONTINICIAL,
				U_EXC_CONTFINAL,
				U_EXC_CONTINICIALGAL,
				U_EXC_CONTFINALGAL,
				U_EXC_LADO,
				U_EXC_MANGUERA,
				OCRCODE,
				U_EXC_CAJA,
				U_EXC_CONT,
				U_EXC_TIPO,
				ESTADO,
				ERRORMSG
			) VALUES (
				'" . $row['id_contometro'] . "',
				'" . $row['fe_emision'] . "',
				'" . $row['nu_turno'] . "',
				'" . trim($row['nu_codigo_producto']) . "',
				" . $row['nu_contometro_inicial_soles'] . ",
				" . $row['nu_contometro_final_soles'] . ",
				" . $row['nu_contometro_inicial_cantidad'] . ",
				" . $row['nu_contometro_final_cantidad'] . ",
				'" . $row['nu_lado'] . "',
				'" . $row['nu_manguera'] . "',
				'" . $row['id_centro_costo'] . "',
				'',
				0.00,
				'2',
				'P',
				''
			);
			";
			$stmt = odbc_exec($connectHana, $sql);
			if (!$stmt){
				odbc_rollback($connectHana);
				$arrResult['codigo_error'] = odbc_error();
				$arrResult['estado'] = FALSE;
				$arrResult['mensaje'] = odbc_errormsg();
				$arrResult['cantidad_registros'] = $cantidad_registros;
				return $arrResult;
				break;
			}
			++$cantidad_registros;
		}
		unset($arrContometros);
		$arrResult['codigo_error'] = '';
		$arrResult['estado'] = TRUE;
		$arrResult['mensaje'] = 'Registros cargados satisfactoriamente';
		$arrResult['cantidad_registros'] = $cantidad_registros;
		return $arrResult;
	}

	function getCambiosPrecioPosTrans($connectHana, $nu_almacen, $fe_inicial, $fe_final, $pos_transym) {
		global $sqlca;

		$condAlmacenPT = (!empty($nu_almacen) ? "AND es = '" . $nu_almacen . "'" : NULL);

		$status = $sqlca->query("
		SELECT
			es||TO_CHAR(dia::DATE,'YYMMDD')||caja||turno||codigo AS id_cambio_precio,
			dia AS fe_emision,
			turno AS nu_turno,
			precio AS ss_precio,
			codigo AS nu_codigo_producto,
			SAPCC.sap_codigo AS ID_Centro_Costo
		FROM
			" . $pos_transym . " AS PT
			JOIN inv_ta_almacenes AS ALMA ON (PT.es = ALMA.ch_almacen)
			JOIN int_ta_sucursales AS ORG ON (ORG.ch_sucursal = ALMA.ch_sucursal)
			JOIN sap_mapeo_tabla_detalle AS SAPCC ON (SAPCC.opencomb_codigo = ORG.ch_sucursal AND SAPCC.id_tipo_tabla = 1)
		WHERE
			dia BETWEEN '" . $fe_inicial . "' AND '" . $fe_final . "'
			AND tipo = 'C'
			AND grupo != 'D'
			" . $condAlmacenPT . "
		GROUP BY
			es,
			dia,
			caja,
			turno,
			codigo,
			precio,
			SAPCC.sap_codigo;
		");

		$arrResult['descripcion_tabla'] = 'Cambio de Precios';
		$arrResult['tabla'] = 'INTCAMPREC';
		$arrResult['codigo_error'] = '';
		$arrResult['cantidad_registros'] = $status;//status == Tambien contiene la cantidad de registros

		if($status < 0){
			$arrResult['estado'] = FALSE;
			$arrResult['mensaje'] = 'Error SQL - function getCambiosPrecioPosTrans';
		} else if($status == 0) {
			$arrResult['estado'] = TRUE;
			$arrResult['mensaje'] = 'No se encontró ningún registro';
		} else {
			$arrCambiosPrecio = $sqlca->fetchAll();
			$arrResult = $this->sendCambiosPrecioPosTrans($connectHana, $arrCambiosPrecio);

			if($arrResult['codigo_error'] == '23000')
				$arrResult['mensaje'] = 'Ya se migro la información';
			if($arrResult['codigo_error'] == 'S1000')
				$arrResult['mensaje'] = 'No se pueden registrar Cambios de Precio, porque los tipos de datos de las columnas fueron cambiados en HANA - SAP';

			$arrResult['descripcion_tabla'] = 'Cambio de Precios';
			$arrResult['tabla'] = 'INTCAMPREC';
		}
		return $arrResult;
	}

	function sendCambiosPrecioPosTrans($connectHana, $arrCambiosPrecio){
		$cantidad_registros = 1;
		foreach ($arrCambiosPrecio as $row) {
			$sql = "
			INSERT INTO BDINT.INTCAMPREC(
				KEY,
				U_EXC_FECHA,
				U_EXC_TURNO,
				U_EXC_PRECIO,
				U_EXC_TIPOPROD,
				U_EXC_CCOSTO,
				ESTADO,
				ERRORMSG
			) VALUES (
				'" . $row['id_cambio_precio'] . "',
				'" . $row['fe_emision'] . "',
				'" . $row['nu_turno'] . "',
				" . $row['ss_precio'] . ",
				'" . trim($row['nu_codigo_producto']) . "',
				'" . $row['id_centro_costo'] . "',
				'P',
				''
			);
			";
			$stmt = odbc_exec($connectHana, $sql);
			if (!$stmt){
				odbc_rollback($connectHana);
				$arrResult['codigo_error'] = odbc_error();
				$arrResult['estado'] = FALSE;
				$arrResult['mensaje'] = odbc_errormsg();
				$arrResult['cantidad_registros'] = $cantidad_registros;
				return $arrResult;
				break;
			}
			++$cantidad_registros;
		}
		unset($arrCambiosPrecio);
		$arrResult['codigo_error'] = '';
		$arrResult['estado'] = TRUE;
		$arrResult['mensaje'] = 'Registros cargados satisfactoriamente';
		$arrResult['cantidad_registros'] = $cantidad_registros;
		return $arrResult;
	}

	function getBonus($connectHana, $nu_almacen, $fe_inicial, $fe_final, $ss_factor_bonus, $pos_transym) {
		global $sqlca;

		$condAlmacenPT = (!empty($nu_almacen) ? "AND PT.es = '" . $nu_almacen . "'" : NULL);

		$status = $sqlca->query("
		SELECT
			PT.es || PT.caja || PT.trans AS Id_Bonus,
			PT.dia AS Fe_Emision,
			PT.indexa AS Nu_Tarjeta_Bonus,
			TO_CHAR(PT.fecha, 'HH12:MI:SS') AS Fe_Hora,
			TRUNC(PT.importe / " . $ss_factor_bonus . ") AS Nu_Cantidad_Puntos_Bonus,
			PT.trans AS Nu_Ticket,
			(CASE WHEN PT.td = 'N' THEN PT.cuenta ELSE PT.ruc END) AS Nu_Codigo_Cliente,
			MTRABA.ch_codigo_trabajador AS Nu_Codigo_Trabajador,
			SAPCC.sap_codigo AS ID_Centro_Costo
		FROM
			" . $pos_transym . " AS PT
			LEFT JOIN pos_historia_ladosxtrabajador AS MTRABA ON(PT.dia = MTRABA.dt_dia AND PT.turno::INTEGER = MTRABA.ch_posturno AND PT.pump = MTRABA.ch_lado AND PT.tipo = MTRABA.ch_tipo)
			JOIN inv_ta_almacenes AS ALMA ON (PT.es = ALMA.ch_almacen)
			JOIN int_ta_sucursales AS ORG ON (ORG.ch_sucursal = ALMA.ch_sucursal)
			JOIN sap_mapeo_tabla_detalle AS SAPCC ON (SAPCC.opencomb_codigo = ORG.ch_sucursal AND SAPCC.id_tipo_tabla = 1)
		WHERE
			PT.td IN('F','B','N')
			AND TRIM(PT.indexa) != '' AND TRIM(PT.indexa) != '.' AND TRIM(PT.indexa) != '0'
			AND PT.grupo != 'D'
			AND PT.dia BETWEEN '" . $fe_inicial . "' AND '" . $fe_final . "'
			" . $condAlmacenPT . "
		");

		$arrResult['descripcion_tabla'] = 'Bonus';
		$arrResult['tabla'] = 'INTBONUS';
		$arrResult['codigo_error'] = '';
		$arrResult['cantidad_registros'] = $status;// status == Tambien contiene la cantidad de registros

		if($status < 0){
			$arrResult['estado'] = FALSE;
			$arrResult['mensaje'] = 'Error SQL - function getBonus';
		} else if($status == 0) {
			$arrResult['estado'] = TRUE;
			$arrResult['mensaje'] = 'No se encontró ningún registro';
		} else {
			$arrBonus = $sqlca->fetchAll();
			$arrResult = $this->sendBonus($connectHana, $arrBonus);

			if($arrResult['codigo_error'] == '23000')
				$arrResult['mensaje'] = 'Ya se migro la información';
			if($arrResult['codigo_error'] == 'S1000')
				$arrResult['mensaje'] = 'No se pueden registrar Bonus, porque los tipos de datos de las columnas fueron cambiados en HANA - SAP';

			$arrResult['descripcion_tabla'] = 'Bonus';
			$arrResult['tabla'] = 'INTBONUS';
		}
		return $arrResult;
	}

	function sendBonus($connectHana, $arrBonus){
		$cantidad_registros = 1;
		foreach ($arrBonus as $row) {
			$sql = "
			INSERT INTO BDINT.INTBONUS(
				NOPERACION,
				U_EXC_FECHA,
				U_EXC_NTARJETA,
				U_EXC_HORA,
				U_EXC_CANTPNTS,
				U_EXC_TICKET,
				U_EXC_CODCLI,
				U_EXC_EMPLEADO,
				U_EXC_CCOSTO,
				ESTADO,
				ERRORMSG
			) VALUES (
				'" . $row['id_bonus'] . "',
				'" . $row['fe_emision'] . "',
				'" . $row['nu_tarjeta_bonus'] . "',
				'" . $row['fe_hora'] . "',
				'" . $row['nu_cantidad_puntos_bonus'] . "',
				'" . $row['nu_ticket'] . "',
				'" . trim($row['nu_codigo_cliente']) . "',
				'" . trim($row['nu_codigo_trabajador']) . "',
				'" . $row['id_centro_costo'] . "',
				'P',
				''
			);
			";
			$stmt = odbc_exec($connectHana, $sql);
			if (!$stmt){
				odbc_rollback($connectHana);
				$arrResult['codigo_error'] = odbc_error();
				$arrResult['estado'] = FALSE;
				$arrResult['mensaje'] = odbc_errormsg();
				$arrResult['cantidad_registros'] = $cantidad_registros;
				return $arrResult;
				break;
			}
			++$cantidad_registros;
		}
		unset($arrBonus);
		$arrResult['codigo_error'] = '';
		$arrResult['estado'] = TRUE;
		$arrResult['mensaje'] = 'Registros cargados satisfactoriamente';
		$arrResult['cantidad_registros'] = $cantidad_registros;
		return $arrResult;
	}

	function getDepositos($connectHana, $nu_almacen, $fe_inicial, $fe_final) {
		global $sqlca;

		$condAlmacenDPOS = (!empty($nu_almacen) ? "AND DPOS.ch_almacen = '" . $nu_almacen . "'" : NULL);

		$status = $sqlca->query("
		SELECT DISTINCT ON (DPOS.ch_almacen || DPOS.dt_dia || DPOS.ch_posturno || DPOS.ch_codigo_trabajador || DPOS.ch_numero_documento)
			DPOS.ch_almacen || TO_CHAR(DPOS.dt_dia::DATE,'YYMMDD') || DPOS.ch_posturno || DPOS.ch_codigo_trabajador || DPOS.ch_numero_documento AS id_deposito,
			DPOS.dt_fecha AS Fe_emision,
			DPOS.ch_posturno AS Nu_Turno,
			DPOS.nu_importe AS Ss_Total,
			TRAFB.importe AS Ss_Faltante_Trabajador,
			MTRABA.ch_codigo_trabajador AS Nu_Codigo_Trabajador,
			LADOCAJA.s_pos_id AS Nu_Caja,
			DPOS.ch_moneda AS id_moneda,
			DPOS.nu_tipo_cambio AS Ss_Tipo_Cambio,
			DPOS.ch_numero_documento AS nu_voucher,
			(CASE
				WHEN (nu_mon200+nu_mon100+nu_mon50+nu_mon20+nu_mon10) > 0 AND (nu_mon5+nu_mon2+nu_mon1+nu_mon050+nu_mon020+nu_mon010) = 0 THEN 'Billetes'
				WHEN (nu_mon200+nu_mon100+nu_mon50+nu_mon20+nu_mon10) = 0 AND (nu_mon5+nu_mon2+nu_mon1+nu_mon050+nu_mon020+nu_mon010) > 0 THEN 'Monedas'
				WHEN (nu_mon200+nu_mon100+nu_mon50+nu_mon20+nu_mon10) > 0 AND (nu_mon5+nu_mon2+nu_mon1+nu_mon050+nu_mon020+nu_mon010) > 0 THEN 'Billetes y Monedas'
			ELSE
				'Ninguna'
			END) AS no_denominacion,
			SAPCC.sap_codigo AS ID_Centro_Costo
		FROM
			pos_depositos_diarios AS DPOS
			LEFT JOIN comb_diferencia_trabajador AS TRAFB ON (DPOS.dt_dia = TRAFB.dia AND DPOS.ch_posturno = TRAFB.turno AND DPOS.ch_codigo_trabajador = TRAFB.ch_codigo_trabajador AND TRAFB.importe < 0)
			LEFT JOIN pos_historia_ladosxtrabajador AS MTRABA ON(DPOS.ch_codigo_trabajador = MTRABA.ch_codigo_trabajador AND DPOS.dt_dia = MTRABA.dt_dia AND DPOS.ch_posturno = MTRABA.ch_posturno)
			LEFT JOIN f_pump AS LADO ON (MTRABA.ch_lado = LADO.name)
			LEFT JOIN f_pump_pos AS LADOCAJA USING (f_pump_id)
			JOIN inv_ta_almacenes AS ALMA USING (ch_almacen)
			JOIN int_ta_sucursales AS ORG ON (ORG.ch_sucursal = ALMA.ch_sucursal)
			JOIN sap_mapeo_tabla_detalle AS SAPCC ON (SAPCC.opencomb_codigo = ORG.ch_sucursal AND SAPCC.id_tipo_tabla = 1)
		WHERE
			DPOS.dt_dia BETWEEN '" . $fe_inicial . "' AND '" . $fe_final . "'
			AND (DPOS.ch_valida = 'S' OR DPOS.ch_valida = 's')
			" . $condAlmacenDPOS . "
		");

		$arrResult['descripcion_tabla'] = 'Depósitos';
		$arrResult['tabla'] = 'INTDEPOSITOS';
		$arrResult['codigo_error'] = '';
		$arrResult['cantidad_registros'] = $status;// status == Tambien contiene la cantidad de registros

		if($status < 0){
			$arrResult['estado'] = FALSE;
			$arrResult['mensaje'] = 'Error SQL - function getDepositos';
		} else if($status == 0) {
			$arrResult['estado'] = TRUE;
			$arrResult['mensaje'] = 'No se encontró ningún registro';
		} else {
			$arrDepositos = $sqlca->fetchAll();
			$arrResult = $this->sendDepositos($connectHana, $arrDepositos);

			if($arrResult['codigo_error'] == '23000')
				$arrResult['mensaje'] = 'Ya se migro la información';
			if($arrResult['codigo_error'] == 'S1000')
				$arrResult['mensaje'] = 'No se pueden registrar Depositos, porque los tipos de datos de las columnas fueron cambiados en HANA - SAP';

			$arrResult['descripcion_tabla'] = 'Depósitos';
			$arrResult['tabla'] = 'INTDEPOSITOS';
		}
		return $arrResult;
	}

	function sendDepositos($connectHana, $arrDepositos){
		$cantidad_registros = 1;
		$nu_codigo_moneda = '';
		/**
		Observaciones:
		- Campos:
			Tipo de Venta
				1 = GNV
				2 = Combustible
		*/
		foreach ($arrDepositos as $row) {
			$nu_codigo_moneda = ($row['id_moneda'] == '01' ? 'SOL' : 'USD');
			$sql = "
			INSERT INTO BDINT.INTDEPOSITOS(
				NOPERACION,
				U_EXC_FECHA,
				U_EXC_TURNO,
				U_EXC_MONTO,
				U_EXC_TIPO,
				U_EXC_FALT,
				U_EXC_CCOSTO,
				U_EXC_CTRABAJADOR,
				U_EXC_CAJA,
				U_EXC_MONEDA,
				U_EXC_TC,
				U_EXC_NVOUCHER,
				U_EXC_DENOMINACION,
				ESTADO,
				ERRORMSG
			) VALUES (
				'" . $row['id_deposito'] . "',
				'" . $row['fe_emision'] . "',
				'" . $row['nu_turno'] . "',
				" . $row['ss_total'] . ",
				'2',
				'" . $row['ss_faltante_trabajador'] . "',
				'" . $row['id_centro_costo'] . "',
				'" . trim($row['nu_codigo_trabajador']) . "',
				'" . $row['nu_caja'] . "',
				'" . $nu_codigo_moneda . "',
				" . $row['ss_tipo_cambio'] . ",
				'" . $row['nu_voucher'] . "',
				'" . $row['no_denominacion'] . "',
				'P',
				''
			);
			";
			$stmt = odbc_exec($connectHana, $sql);
			if (!$stmt){
				odbc_rollback($connectHana);
				$arrResult['codigo_error'] = odbc_error();
				$arrResult['estado'] = FALSE;
				$arrResult['mensaje'] = odbc_errormsg();
				$arrResult['cantidad_registros'] = $cantidad_registros;
				return $arrResult;
				break;
			}
			++$cantidad_registros;
		}
		unset($arrDepositos);
		$arrResult['codigo_error'] = '';
		$arrResult['estado'] = TRUE;
		$arrResult['mensaje'] = 'Registros cargados satisfactoriamente';
		$arrResult['cantidad_registros'] = $cantidad_registros;
		return $arrResult;
	}

	function getAfericiones($connectHana, $nu_almacen, $fe_inicial, $fe_final, $ss_impuesto, $nu_codigo_impuesto) {
		global $sqlca;

		$condAlmacen = (!empty($nu_almacen) ? "AND PTA.es = '" . $nu_almacen . "'" : NULL);

		$sql_AFE_Cabecera = "
		SELECT
			es || caja || trans AS Id_Afericion,
			dia AS Fe_Emision,
			veloc AS No_Velocidad
		FROM
			pos_ta_afericiones AS PTA
		WHERE
			dia BETWEEN '" . $fe_inicial . "' AND '" . $fe_final . "'
			" . $condAlmacen . "
		";

		$sql_AFE_Detalle = "
		SELECT
			es || caja || trans AS Id_Afericion,
			SAPALMA.sap_codigo AS ID_Almacen,
			PTA.codigo AS Nu_Codigo_Producto,
			PTA.cantidad AS Ss_Cantidad,
			ROUND((PTA.precio / " . $ss_impuesto . "), 4) AS Ss_Precio_Venta_SIGV,
			PTA.caja AS Nu_Caja,
			SURTIDOR.nu_manguera,
			PTA.turno AS Nu_Turno,
			TO_CHAR(PTA.fecha, 'HH12:MI:SS') AS Fe_Hora,
			PTA.lineas AS Nu_Linea_Calibracion,
			SAPCC.sap_codigo AS ID_Centro_Costo,
			SAPLINEA.sap_codigo AS ID_Linea_Negocio
		FROM
			pos_ta_afericiones AS PTA
			JOIN comb_ta_surtidores AS SURTIDOR ON (PTA.pump::INTEGER = SURTIDOR.ch_numerolado::INTEGER AND SURTIDOR.ch_codigocombustible = PTA.codigo)
			JOIN inv_ta_almacenes AS ALMA ON (ALMA.ch_almacen = PTA.es)
			JOIN int_ta_sucursales AS ORG ON (ORG.ch_sucursal = ALMA.ch_sucursal)
			JOIN sap_mapeo_tabla_detalle AS SAPCC ON (SAPCC.opencomb_codigo = ORG.ch_sucursal AND SAPCC.id_tipo_tabla = 1)
			JOIN sap_mapeo_tabla_detalle AS SAPALMA ON (SAPALMA.opencomb_codigo = PTA.es AND SAPALMA.id_tipo_tabla = 2)
			JOIN int_articulos AS PRO ON (PRO.art_codigo = PTA.codigo)
			LEFT JOIN sap_mapeo_tabla_detalle AS SAPLINEA ON (SAPLINEA.opencomb_codigo = PRO.art_linea AND SAPLINEA.id_tipo_tabla = 3)
		WHERE
			PTA.dia BETWEEN '" . $fe_inicial . "' AND '" . $fe_final . "'
			" . $condAlmacen . "
		";

		$arrResult['descripcion_tabla'] = 'Entradas / Salidas Inventario - Calibración';
		$arrResult['tabla'] = 'INTCALIB1 y INTCALIB1';
		$arrResult['codigo_error'] = '';
		$arrResult['cantidad_registros'] = 0;

		if (($sqlca->query($sql_AFE_Cabecera) < 0)) {
			$arrResult['estado'] = FALSE;
			$arrResult['mensaje'] = 'Error SQL - function getAfericiones Cabecera';
		} else if (($sqlca->query($sql_AFE_Cabecera) == 0)) {
			$arrResult['estado'] = TRUE;
			$arrResult['mensaje'] = 'No se encontró ningún registro';
		} else {
			$arrAfericionesCabecera = $sqlca->fetchAll();
			$arrResult = $this->sendAfericionesCabecera($connectHana, $arrAfericionesCabecera);
			$cantidad_registros = $arrResult['cantidad_registros'];

			if($arrResult['codigo_error'] == '23000')
				$arrResult['mensaje'] = 'Ya se migro la información';
			if($arrResult['codigo_error'] == 'S1000')
				$arrResult['mensaje'] = 'No se pueden registrar Afericion - Cabecera, porque los tipos de datos de las columnas fueron cambiados en HANA - SAP';

			if($arrResult['estado']){
				if (($sqlca->query($sql_AFE_Detalle) < 0)) {
					$arrResult['estado'] = FALSE;
					$arrResult['mensaje'] = 'Error SQL - function getAfericiones Detalle';
				} else if (($sqlca->query($sql_AFE_Detalle) == 0)) {
					$arrResult['estado'] = TRUE;
					$arrResult['mensaje'] = 'No se encontró ningún registro';
				} else {
					$arrAfericionesDetalle = $sqlca->fetchAll();
					$arrResult = $this->sendAfericionesDetalle($connectHana, $arrAfericionesDetalle, $nu_codigo_impuesto);

					if($arrResult['codigo_error'] == '23000')
						$arrResult['mensaje'] = 'Ya se migro la información';
					if($arrResult['codigo_error'] == 'S1000')
						$arrResult['mensaje'] = 'No se pueden registrar Afericion - Detalle, porque los tipos de datos de las columnas fueron cambiados en HANA - SAP';
				}
			}
			$arrResult['descripcion_tabla'] = 'Entradas / Salidas Inventario - Calibración';
			$arrResult['tabla'] = 'INTCALIB1 y INTCALIB1';
			$arrResult['cantidad_registros'] = $cantidad_registros;
		}
		return $arrResult;
	}

	function sendAfericionesCabecera($connectHana, $arrAfericionesCabecera){
		$cantidad_registros = 1;
		foreach ($arrAfericionesCabecera as $row) {
			$sql = "
			INSERT INTO BDINT.INTCALIB(
				NOPERACION,
				\"DocDate\",
				U_EXC_VELOCIDAD,
				ESTADO,
				ERRORMSG
			) VALUES (
				'" . $row['id_afericion'] . "',
				'" . $row['fe_emision'] . "',
				'" . trim($row['no_velocidad']) . "',
				'P',
				''
			);
			";
			$stmt = odbc_exec($connectHana, $sql);
			if (!$stmt){
				odbc_rollback($connectHana);
				$arrResult['codigo_error'] = odbc_error();
				$arrResult['estado'] = FALSE;
				$arrResult['mensaje'] = odbc_errormsg();
				$arrResult['cantidad_registros'] = $cantidad_registros;
				return $arrResult;
				break;
			}
			++$cantidad_registros;
		}
		unset($arrAfericionesCabecera);
		$arrResult['codigo_error'] = '';
		$arrResult['estado'] = TRUE;
		$arrResult['mensaje'] = 'Registros cargados satisfactoriamente';
		$arrResult['cantidad_registros'] = $cantidad_registros;
		return $arrResult;
	}

	function sendAfericionesDetalle($connectHana, $arrAfericionesDetalle, $nu_codigo_impuesto){
		$item = 1;
		foreach ($arrAfericionesDetalle as $row) {
			$sql = "
			INSERT INTO BDINT.INTCALIB1(
				WHSCODE,
				NOPERACION,
				ITEM,
				ITEMCODE,
				QUANTITY,
				PRICE,
				TAXCODE,
				OCRCODE,
				OCRCODE2,
				U_EXC_CAJA,
				U_EXC_MANGUERA,
				U_EXC_TURNO,
				U_EXC_HORA,
				U_EXC_LINEA
			) VALUES (
				'" . $row['id_almacen'] . "',
				'" . $row['id_afericion'] . "',
				" . $item . ",
				'" . trim($row['nu_codigo_producto']) . "',
				" . $row['ss_cantidad'] . ",
				" . $row['ss_precio_venta_sigv'] . ",
				'" . $nu_codigo_impuesto . "',
				'" . $row['id_centro_costo'] . "',
				'" . $row['id_linea_negocio'] . "',
				'" . trim($row['nu_caja']) . "',
				'" . $row['nu_manguera'] . "',
				'" . $row['nu_turno'] . "',
				'" . $row['fe_hora'] . "',
				'" . $row['nu_linea_calibracion'] . "'
			);
			";
			$stmt = odbc_exec($connectHana, $sql);
			if (!$stmt){
				odbc_rollback($connectHana);
				$arrResult['codigo_error'] = odbc_error();
				$arrResult['estado'] = FALSE;
				$arrResult['mensaje'] = odbc_errormsg();
				return $arrResult;
				break;
			}
			++$item;
		}
		unset($item);
		unset($arrAfericionesDetalle);
		$arrResult['codigo_error'] = '';
		$arrResult['estado'] = TRUE;
		$arrResult['mensaje'] = 'Registros cargados satisfactoriamente';
		return $arrResult;
	}

	function getAjustesInventario($connectHana, $nu_almacen, $fe_inicial, $fe_final, $ss_impuesto, $nu_codigo_impuesto) {
		global $sqlca;

		$condAlmacen = (!empty($nu_almacen) ? "AND mov_almacen = '" . $nu_almacen . "'" : NULL);

		$sql_AJUINV_Cabecera = "
		SELECT
			mov_almacen || mov_numero || tran_codigo || art_codigo AS Id_Ajuste_Inventario,
			mov_fecha AS Fe_Emision
		FROM
			inv_movialma
		WHERE
			tran_codigo = '17'
			AND mov_fecha BETWEEN '" . $fe_inicial . " 00:00:00' AND '" . $fe_final . " 23:59:59'
			" . $condAlmacen . "
		";

		$sql_AJUINV_Detalle = "
		SELECT
			mov_almacen || mov_numero || tran_codigo || art_codigo AS Id_Ajuste_Inventario,
			art_codigo AS Nu_Codigo_Producto,
			SAPALMA.sap_codigo AS ID_Almacen,
			mov_cantidad AS Ss_Cantidad,
			mov_costounitario AS Ss_Costo_Unitario_SIGV,
			SAPCC.sap_codigo AS ID_Centro_Costo,
			SAPLINEA.sap_codigo AS ID_Linea_Negocio
		FROM
			inv_movialma AS INV
			JOIN inv_ta_almacenes AS ALMA ON (ALMA.ch_almacen = INV.mov_almacen)
			JOIN int_ta_sucursales AS ORG ON (ORG.ch_sucursal = ALMA.ch_sucursal)
			JOIN sap_mapeo_tabla_detalle AS SAPCC ON (SAPCC.opencomb_codigo = ORG.ch_sucursal AND SAPCC.id_tipo_tabla = 1)
			JOIN sap_mapeo_tabla_detalle AS SAPALMA ON (SAPALMA.opencomb_codigo = PT.es AND SAPALMA.id_tipo_tabla = 2)
			JOIN int_articulos AS PRO USING (art_codigo)
			JOIN sap_mapeo_tabla_detalle AS SAPLINEA ON (SAPLINEA.opencomb_codigo = PRO.art_linea AND SAPLINEA.id_tipo_tabla = 3)
		WHERE
			tran_codigo = '17'
			AND mov_fecha BETWEEN '" . $fe_inicial . " 00:00:00' AND '" . $fe_final . " 23:59:59'
			" . $condAlmacen . "
		";

		$arrResult['descripcion_tabla'] = 'Ajustes de Inventarios';
		$arrResult['tabla'] = 'INTAJUSTE y INTAJUSTE1';
		$arrResult['codigo_error'] = '';
		$arrResult['cantidad_registros'] = 0;

		if (($sqlca->query($sql_AJUINV_Cabecera) < 0)) {
			$arrResult['estado'] = FALSE;
			$arrResult['mensaje'] = 'Error SQL - function getAjustesInventario Cabecera';
		} else if (($sqlca->query($sql_AJUINV_Cabecera) == 0)) {
			$arrResult['estado'] = TRUE;
			$arrResult['mensaje'] = 'No se encontró ningún registro';
		} else {
			$arrAjustesInventarioCabecera = $sqlca->fetchAll();
			$arrResult = $this->sendAjustesInventarioCabecera($connectHana, $arrAjustesInventarioCabecera);
			$cantidad_registros = $arrResult['cantidad_registros'];

			if($arrResult['codigo_error'] == '23000')
				$arrResult['mensaje'] = 'Ya se migro la información';
			if($arrResult['codigo_error'] == 'S1000')
				$arrResult['mensaje'] = 'No se pueden registrar Ajustes Inventario - Cabecera, porque los tipos de datos de las columnas fueron cambiados en HANA - SAP';

			if($arrResult['estado']){
				if (($sqlca->query($sql_AJUINV_Detalle) < 0)) {
					$arrResult['estado'] = FALSE;
					$arrResult['mensaje'] = 'Error SQL - function getAjustesInventario Detalle';
				} else if (($sqlca->query($sql_AJUINV_Detalle) == 0)) {
					$arrResult['estado'] = TRUE;
					$arrResult['mensaje'] = 'No se encontró ningún registro';
				} else {
					$arrAjustesInventarioDetalle = $sqlca->fetchAll();
					$arrResult = $this->sendAjustesInventarioDetalle($connectHana, $arrAjustesInventarioDetalle, $nu_codigo_impuesto);

					if($arrResult['codigo_error'] == '23000')
						$arrResult['mensaje'] = 'Ya se migro la información';
					if($arrResult['codigo_error'] == 'S1000')
						$arrResult['mensaje'] = 'No se pueden registrar Ajustes Inventario - Detalle, porque los tipos de datos de las columnas fueron cambiados en HANA - SAP';
				}
			}
			$arrResult['descripcion_tabla'] = 'Ajustes de Inventarios';
			$arrResult['tabla'] = 'INTAJUSTE y INTAJUSTE1';
			$arrResult['cantidad_registros'] = $cantidad_registros;
		}
		return $arrResult;
	}

	function sendAjustesInventarioCabecera($connectHana, $arrAjustesInventarioCabecera){
		$cantidad_registros = 1;
		$no_tipo_movimiento = '';
		/**
		Observaciones:
			- Tipo Movimiento
				E = Entrada
				S = Salida
			- Tipo Operación
				AJUSTE POR DIFERENCIA DE INVENTARIO
		*/
		foreach ($arrAjustesInventarioCabecera as $row) {
			$no_tipo_movimiento = ($row["ss_cantidad"] < 0 ? 'S' : 'E');
			$sql = "
			INSERT INTO BDINT.INTAJUSTE(
				NOPERACION,
				DOCDATE,
				TIPO,
				U_EXX_TIPOOPER,
				ESTADO,
				ERRORMSG
			) VALUES (
				'" . $row['id_ajuste_inventario'] . "',
				'" . $row['fe_emision'] . "',
				'" . $no_tipo_movimiento . "',
				'28',
				'P',
				''
			);
			";
			$stmt = odbc_exec($connectHana, $sql);
			if (!$stmt){
				odbc_rollback($connectHana);
				$arrResult['codigo_error'] = odbc_error();
				$arrResult['estado'] = FALSE;
				$arrResult['mensaje'] = odbc_errormsg();
				$arrResult['cantidad_registros'] = $cantidad_registros;
				return $arrResult;
				break;
			}
			++$cantidad_registros;
		}
		unset($arrAjustesInventarioCabecera);
		$arrResult['codigo_error'] = '';
		$arrResult['estado'] = TRUE;
		$arrResult['mensaje'] = 'Registros cargados satisfactoriamente';
		$arrResult['cantidad_registros'] = $cantidad_registros;
		return $arrResult;
	}

	function sendAjustesInventarioDetalle($connectHana, $arrAjustesInventarioDetalle, $nu_codigo_impuesto){
		$item = 1;
		foreach ($arrAjustesInventarioDetalle as $row) {
			$sql = "
			INSERT INTO BDINT.INTAJUSTE1(
				WHSCODE,
				NOPERACION,
				ITEM,
				ITEMCODE,
				QUANTITY,
				PRICE,
				TAXCODE,
				OCRCODE,
				OCRCODE2
			) VALUES (
				'" . $row['id_almacen'] . "',
				'" . $row['id_ajuste_inventario'] . "',
				" . $item . ",
				'" . trim($row['nu_codigo_producto']) . "',
				" . $row['ss_cantidad'] . ",
				" . $row['ss_costo_unitario_sigv'] . ",
				'" . $nu_codigo_impuesto . "',
				'" . $row['id_centro_costo'] . "',
				'" . $row['id_linea_negocio'] . "',
			);
			";
			$stmt = odbc_exec($connectHana, $sql);
			if (!$stmt){
				odbc_rollback($connectHana);
				$arrResult['codigo_error'] = odbc_error();
				$arrResult['estado'] = FALSE;
				$arrResult['mensaje'] = odbc_errormsg();
				return $arrResult;
				break;
			}
			++$item;
		}
		unset($item);
		unset($arrAjustesInventarioDetalle);
		$arrResult['codigo_error'] = '';
		$arrResult['estado'] = TRUE;
		$arrResult['mensaje'] = 'Registros cargados satisfactoriamente';
		return $arrResult;
	}

	function getEntradasSalidasInventario($connectHana, $nu_almacen, $fe_inicial, $fe_final) {
		global $sqlca;

		$condAlmacenMovi = (!empty($nu_almacen) ? "AND MOVI.mov_almacen = '" . $nu_almacen . "'" : NULL);
		$sql_ESI_Cabecera = "
		SELECT
			MOVI.mov_almacen || MOVI.mov_numero || MOVI.tran_codigo || MOVI.art_codigo AS id_movialma,
			MOVI.mov_fecha AS Fe_Emision,
			TD.tab_car_03 AS Nu_Tipo_Documento_SUNAT,
			SPLIT_PART(TM.format_sunat, '-', 1) AS Nu_Tipo_Movimiento_SUNAT,
			MOVI.mov_almaorigen AS Nu_Almacen_Origen,
			MOVI.mov_almadestino AS Nu_Almacen_Destino
		FROM
			inv_movialma AS MOVI
			JOIN int_tabla_general AS TD ON(MOVI.mov_tipdocuref = substring(TRIM(TD.tab_elemento) for 2 FROM length(TRIM(TD.tab_elemento))-1) AND TD.tab_tabla = '08' AND TD.tab_elemento <> '000000')
			JOIN inv_tipotransa AS TM ON(TM.tran_codigo = MOVI.tran_codigo)
		WHERE
			MOVI.tran_codigo IN ('01','07','08','21','27','28')
			AND MOVI.mov_fecha BETWEEN '" . $fe_inicial . " 00:00:00' AND '" . $fe_final . " 23:59:59'
			" . $condAlmacenMovi . "
		";

		$sql_ESI_Detalle = "
		SELECT
			mov_almacen || mov_numero || tran_codigo || art_codigo AS id_movialma,
			art_codigo AS Nu_Codigo_Producto,
			mov_cantidad AS Qt_Cantidad
		FROM
			inv_movialma AS MOVI
		WHERE
			tran_codigo IN ('01','07','08','21','27','28')
			AND mov_fecha BETWEEN '" . $fe_inicial . " 00:00:00' AND '" . $fe_final . " 23:59:59'
			" . $condAlmacenMovi . "
		";

		$arrResult['descripcion_tabla'] = 'Transferencia de Stock';
		$arrResult['tabla'] = 'INTOWTR y INTWTR1';
		$arrResult['codigo_error'] = '';
		$arrResult['cantidad_registros'] = 0;

		if (($sqlca->query($sql_ESI_Cabecera) < 0)) {
			$arrResult['estado'] = FALSE;
			$arrResult['mensaje'] = 'Error SQL - function getEntradasSalidasInventario Cabecera';
		} else if (($sqlca->query($sql_ESI_Cabecera) == 0)) {
			$arrResult['estado'] = TRUE;
			$arrResult['mensaje'] = 'No se encontró ningún registro';
		} else {
			$arrESInventariosCabecera = $sqlca->fetchAll();
			$arrResult = $this->sendEntradasSalidasInventarioCabecera($connectHana, $arrESInventariosCabecera);
			$cantidad_registros = $arrResult['cantidad_registros'];

			if($arrResult['codigo_error'] == '23000')
				$arrResult['mensaje'] = 'Ya se migro la información';
			if($arrResult['codigo_error'] == 'S1000')
				$arrResult['mensaje'] = 'No se pueden registrar Entrada y Salida Inventario - Cabecera, porque los tipos de datos de las columnas fueron cambiados en HANA - SAP';

			if($arrResult['estado']){
				if (($sqlca->query($sql_ESI_Detalle) < 0)) {
					$arrResult['estado'] = FALSE;
					$arrResult['mensaje'] = 'Error SQL - function getEntradasSalidasInventario Detalle';
				} else if (($sqlca->query($sql_ESI_Detalle) == 0)) {
					$arrResult['estado'] = TRUE;
					$arrResult['mensaje'] = 'No se encontró ningún registro';
				} else {
					$arrESInventariosDetalle = $sqlca->fetchAll();
					$arrResult = $this->sendEntradasSalidasInventarioDetalle($connectHana, $arrESInventariosDetalle);

					if($arrResult['codigo_error'] == '23000')
						$arrResult['mensaje'] = 'Ya se migro la información';
					if($arrResult['codigo_error'] == 'S1000')
						$arrResult['mensaje'] = 'No se pueden registrar Entrada y Salida Inventario - Detalle, porque los tipos de datos de las columnas fueron cambiados en HANA - SAP';
				}
			}
			$arrResult['descripcion_tabla'] = 'Transferencia de Stock';
			$arrResult['tabla'] = 'INTOWTR y INTWTR1';
			$arrResult['cantidad_registros'] = $cantidad_registros;
		}
		return $arrResult;
	}

	function sendEntradasSalidasInventarioCabecera($connectHana, $arrESInventariosCabecera){
		$cantidad_registros = 1;
		foreach ($arrESInventariosCabecera as $row) {
			$sql = "
			INSERT INTO BDINT.INTOWTR(
				NOPERACION,
				DOCDATE,
				INDICATOR,
				U_EXX_TIPOOPER,
				FILLER,
				TOWHSCODE,
				ESTADO,
				ERRORMSG,
				DOCENTRY
			) VALUES (
				'" . $row['id_movialma'] . "',
				'" . $row['fe_emision'] . "',
				'" . $row['nu_tipo_documento_sunat'] . "',
				'" . $row['nu_tipo_movimiento_sunat'] . "',
				'" . $row['nu_almacen_origen'] . "',
				'" . $row['nu_almacen_destino'] . "',
				'P',
				'',
				''
			);
			";
			$stmt = odbc_exec($connectHana, $sql);
			if (!$stmt){
				odbc_rollback($connectHana);
				$arrResult['codigo_error'] = odbc_error();
				$arrResult['estado'] = FALSE;
				$arrResult['mensaje'] = odbc_errormsg();
				$arrResult['cantidad_registros'] = $cantidad_registros;
				return $arrResult;
				break;
			}
			++$cantidad_registros;
		}
		unset($arrESInventariosCabecera);
		$arrResult['codigo_error'] = '';
		$arrResult['estado'] = TRUE;
		$arrResult['mensaje'] = 'Registros cargados satisfactoriamente';
		$arrResult['cantidad_registros'] = $cantidad_registros;
		return $arrResult;
	}

	function sendEntradasSalidasInventarioDetalle($connectHana, $arrESInventariosDetalle){
		$item = 1;
		foreach ($arrESInventariosDetalle as $row) {
			$sql = "
			INSERT INTO BDINT.INTWTR1(
				NOPERACION,
				ITEM,
				ITEMCODE,
				QUANTITY
			) VALUES (
				'" . $row['id_movialma'] . "',
				" . $item . ",
				'" . trim($row['nu_codigo_producto']) . "',
				" . $row['qt_cantidad'] . "
			);
			";
			$stmt = odbc_exec($connectHana, $sql);
			if (!$stmt){
				odbc_rollback($connectHana);
				$arrResult['codigo_error'] = odbc_error();
				$arrResult['estado'] = FALSE;
				$arrResult['mensaje'] = odbc_errormsg();
				return $arrResult;
				break;
			}
			++$item;
		}
		unset($item);
		unset($arrESInventariosDetalle);
		$arrResult['codigo_error'] = '';
		$arrResult['estado'] = TRUE;
		$arrResult['mensaje'] = 'Registros cargados satisfactoriamente';
		return $arrResult;
	}

	function getPagos($connectHana, $nu_almacen, $fe_inicial, $fe_final, $pos_transym, $ss_impuesto, $nu_codigo_impuesto) {
		global $sqlca;

		$condAlmacenPT = (!empty($nu_almacen) ? "AND PT.es = '" . $nu_almacen . "'" : NULL);
		$sql_ESI_Cabecera = "
		SELECT
			MOVI.mov_almacen || MOVI.mov_numero || MOVI.tran_codigo || MOVI.art_codigo AS id_movialma,
			MOVI.mov_fecha AS Fe_Emision,
			TD.tab_car_03 AS Nu_Tipo_Documento_SUNAT,
			SPLIT_PART(TM.format_sunat, '-', 1) AS Nu_Tipo_Movimiento_SUNAT,
			MOVI.mov_almaorigen AS Nu_Almacen_Origen,
			MOVI.mov_almadestino AS Nu_Almacen_Destino
		FROM
			inv_movialma AS MOVI
			JOIN int_tabla_general AS TD ON(MOVI.mov_tipdocuref = substring(TRIM(TD.tab_elemento) for 2 FROM length(TRIM(TD.tab_elemento))-1) AND TD.tab_tabla = '08' AND TD.tab_elemento <> '000000')
			JOIN inv_tipotransa AS TM ON(TM.tran_codigo = MOVI.tran_codigo)
		WHERE
			MOVI.tran_codigo IN ('01','07','08','21','27','28')
			AND MOVI.mov_fecha BETWEEN '" . $fe_inicial . " 00:00:00' AND '" . $fe_final . " 23:59:59'
			" . $condAlmacenMovi . "
		";

		$arrResult['descripcion_tabla'] = 'Pagos Recibidos';
		$arrResult['tabla'] = 'INTORCT';
		$arrResult['codigo_error'] = '';
		$arrResult['cantidad_registros'] = 0;

		if (($sqlca->query($sql_ESI_Cabecera) < 0)) {
			$arrResult['estado'] = FALSE;
			$arrResult['mensaje'] = 'Error SQL - function getPagos';
		} else if (($sqlca->query($sql_ESI_Cabecera) == 0)) {
			$arrResult['estado'] = TRUE;
			$arrResult['mensaje'] = 'No se encontró ningún registro';
		} else {
			$arrPagos = $sqlca->fetchAll();
			$arrResult = $this->sendPagos($connectHana, $arrPagos);

			if($arrResult['codigo_error'] == '23000')
				$arrResult['mensaje'] = 'Ya se migro la información';
			if($arrResult['codigo_error'] == 'S1000')
				$arrResult['mensaje'] = 'No se pueden registrar Pagos, porque los tipos de datos de las columnas fueron cambiados en HANA - SAP';

			$arrResult['descripcion_tabla'] = 'Pagos Recibidos';
			$arrResult['tabla'] = 'INTORCT';
		}
		return $arrResult;
	}

	function sendPagos($connectHana, $arrPagos){
		$cantidad_registros = 1;
		foreach ($arrPagos as $row) {
			$sql = "
			INSERT INTO BDINT.INTOWTR(
				NOPERACION,
				DOCDATE,
				INDICATOR,
				U_EXX_TIPOOPER,
				FILLER,
				TOWHSCODE,
				ESTADO,
				ERRORMSG,
				DOCENTRY
			) VALUES (
				'" . $row['id_movialma'] . "',
				'" . $row['fe_emision'] . "',
				'" . $row['nu_tipo_documento_sunat'] . "',
				'" . $row['nu_tipo_movimiento_sunat'] . "',
				'" . $row['nu_almacen_origen'] . "',
				'" . $row['nu_almacen_destino'] . "',
				'P',
				'',
				''
			);
			";
			$stmt = odbc_exec($connectHana, $sql);
			if (!$stmt){
				odbc_rollback($connectHana);
				$arrResult['codigo_error'] = odbc_error();
				$arrResult['estado'] = FALSE;
				$arrResult['mensaje'] = odbc_errormsg();
				$arrResult['cantidad_registros'] = $cantidad_registros;
				return $arrResult;
				break;
			}
			++$cantidad_registros;
		}
		unset($arrPagos);
		$arrResult['codigo_error'] = '';
		$arrResult['estado'] = TRUE;
		$arrResult['mensaje'] = 'Registros cargados satisfactoriamente';
		$arrResult['cantidad_registros'] = $cantidad_registros;
		return $arrResult;
	}
}
