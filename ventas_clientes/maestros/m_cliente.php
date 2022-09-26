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


class ClienteModel extends Model {

	function CIFKey() {
		global $sqlca;
		$registro = array();
		$query = "SELECT par_valor FROM int_parametros WHERE par_nombre = 'ocs_tid_apikey';";

		$sqlca->query($query);

		$reg = $sqlca->fetchRow();
		if ($reg && is_array($reg) && isset($reg[0])) {
			return $reg[0];
		}
		return NULL;
	}

	function validarCodigo($codigo) {
		global $sqlca;

		if(!empty($codigo)) {
			$query = "SELECT cli_codigo FROM int_clientes WHERE cli_codigo = '".$codigo."' LIMIT 1";

			$result  = $sqlca->query($query);
			$numrows = $sqlca->numrows();
	
			if($numrows > 0) {
				$rows = $sqlca->fetchRow();
				if($codigo == $rows[0]) {
					return '<blink style="color: red">C&oacute;digo ya existe, ingrese otro.<blink>';
				}
				return '<blink>El &uacute;ltimo c&oacute;digo ingresado es : '.$rows[0].' <blink>';
			}
		} else {
			return '<blink style="color: red">Debe Ingresar el C&oacute;digo.</blink>';
		}
	}
  
	function validarCodigoShell($codigo) {
		global $sqlca;

		if(!empty($codigo)) {

			if(strlen($codigo) <= 1)
				$codigo = "00".$codigo;
			elseif(strlen($codigo) <= 2)
				$codigo = "0".$codigo;

			/*if($_REQUEST['registroid']) {

				$AddSel = ", cli_codigo ";
				$AddWhere = " AND cli_codigo='".$_REQUEST['registroid']."' ";

			}*/

			$query = "SELECT trim(cli_grupo) FROM int_clientes WHERE cli_grupo ~ '".$codigo."' ORDER BY cli_grupo DESC LIMIT 1";

			$result  = $sqlca->query($query);
			$numrows = $sqlca->numrows();	

			if($numrows > 0) {
				$rows = $sqlca->fetchRow();
				/*if($_REQUEST['registroid'] && $codigo == $rows[0] && $_REQUEST['registroid'] == trim($rows[1])) {
					return '';
				} elseif($_REQUEST['registroid'] != trim($rows[1])) {
					return '<blink>El C&oacute;digo Shell ingresado ya existe, ingrese otro.<blink>';
				}*/
				if($codigo == $rows[0]) {
					return '<blink style="color: red">C&oacute;digo Shell ya existe, ingrese otro.<blink>';
				}
			}
		
		}
	
		/*} else {
			return '<blink style="color: red">Debe Ingresar el C&oacute;digo Shell.</blink>';
		}*/

	}

	function validarRuc($codigo) {
		global $sqlca;

		if(!empty($codigo)) {
			$query = "SELECT trim(cli_ruc) FROM int_clientes WHERE cli_ruc = '".$codigo."' ORDER BY cli_ruc DESC LIMIT 1";

			$result = $sqlca->query($query);
			$numrows = $sqlca->numrows();	

	 		if($numrows > 0) {
				$rows = $sqlca->fetchRow();
				if($codigo == $rows[0]) {
					return '<blink style="color: red">Nro. Ruc ya existe, ingrese otro.<blink>';
				}
			}
		} else {
			return '<blink style="color: red">Debe Ingresar el Nro. de RUC</blink>';
		}
	}

	//function guardarRegistro($datos, $datosxml) {
	function guardarRegistro($datos) { //FUNCIONALIDAD PARA GUARDAR O EDITAR CLIENTE
		global $sqlca;

		$ip_remoto = $_SERVER["REMOTE_ADDR"];
		$datos['cli_fecactualiz'] = 'now()';
		$datos['fecha_replicacion'] = 'now()';
		$datos['flg_replicacion'] = '0';
		$datos['cli_fpago_credito'] = substr(trim($datos['cli_fpago_credito']),-2,2);
		$datos['cli_grupo'] = ($datos['cli_grupo']==''?'null':$datos['cli_grupo']);
		$datos['cli_ruc'] = ($datos['cli_ruc']==''?'null':$datos['cli_ruc']);
		$datos['cli_moneda'] = ($datos['cli_moneda']==''?'null':$datos['cli_moneda']);
		$datos['cli_telefono1'] = ($datos['cli_telefono1']==''?'null':$datos['cli_telefono1']);
		$datos['cli_telefono2'] = ($datos['cli_telefono2']==''?'null':$datos['cli_telefono2']);
		$datos['cli_contacto'] = ($datos['cli_contacto']==''?'null':$datos['cli_contacto']);
		$datos['cli_email'] = ($datos['cli_email']==''?'null':$datos['cli_email']);
		$datos['cli_descuento'] = ($datos['cli_descuento']==''?'01':$datos['cli_descuento']);
		$datos['cli_tipo'] = ($datos['cli_tipo']=='' ? 'AC' : $datos['cli_tipo']);
		$datos['cli_creditosol'] = ($datos['cli_creditosol']==''?'null':round($datos['cli_creditosol'],2));
		$datos['cli_creditodol'] = ($datos['cli_creditodol']==''?'null':$datos['cli_creditodol']);
		$datos['cli_comp_direccion'] = ($datos['cli_comp_direccion']==''?'null':$datos['cli_comp_direccion']);
		$datos['cli_mantenimiento'] = ($datos['cli_mantenimiento']==''?0.00:$datos['cli_mantenimiento']);

		$datos['cli_razsocial'] = pg_escape_string($datos['cli_razsocial']);
		$datos['cli_rsocialbreve'] = pg_escape_string($datos['cli_rsocialbreve']);
		$datos['cli_direccion'] = pg_escape_string($datos['cli_direccion']);

		// Tipos de agente - 31/07/2019
		$datos['cli_estado'] = ($datos['cbo-sTipoAgente']=='N'?null:$datos['cbo-sTipoAgente']);

		/**
		* OPENSOFT-XX: Venta adelantada
			- TIPOS DE CLIENTES:
		 		ANTICIPO:                     cli_ndespacho_efectivo = '0' / cli_anticipo = 'S'
		 		CREDITO:                      cli_ndespacho_efectivo = '0' / cli_anticipo = 'N'
		 		NOTA DE DESPACHO EN EFECTIVO: cli_ndespacho_efectivo = '1' / cli_anticipo = 'N'
		 		VENTA ADELANTADA:             cli_ndespacho_efectivo = '1' / cli_anticipo = 'S'
		*/

		//Cargamos configuracion del tipo de cliente para guardar o modificar
		if ($datos['tcliente'] == 0) { //Anticipo
			$datos['cli_ndespacho_efectivo'] = '0';
			$datos['cli_anticipo']           = 'S';
		} elseif ($datos['tcliente'] == 1) { //Credito
			$datos['cli_ndespacho_efectivo'] = '0';
			$datos['cli_anticipo']           = 'N';
		} elseif ($datos['tcliente'] == 2) { //Nota de despacho en efectivo
			$datos['cli_ndespacho_efectivo'] = '1';
			$datos['cli_anticipo']           = 'N';
		} elseif ($datos['tcliente'] == 3) { //Venta adelantada
			$datos['cli_ndespacho_efectivo'] = '1';
			$datos['cli_anticipo']           = 'S';
		}

		if (isset($_REQUEST["registroid"]) && $_REQUEST["registroid"] != "") {
			$registroid = trim($_REQUEST["registroid"]);

			/*if ($sqlca->perform('int_clientes', $datos, 'update', "cli_codigo = '" . $registroid . "'") >= 0) {
				echo "ENTRO";
				var_dump($sqlca->perform('int_clientes', $datos, 'update', "cli_codigo = '" . $registroid . "'"));
				return 'OK';
			} else { 
				//return $sqlca->get_error();
				return 'error';
			}*/

			$sql = "
UPDATE
 int_clientes
SET
 cli_razsocial = '" . $datos['cli_razsocial'] . "',
 cli_rsocialbreve = '" . $datos['cli_rsocialbreve'] . "',
 cli_contacto = '" . $datos['cli_contacto'] . "',
 cli_ruc = '" . $datos['cli_ruc'] . "',
 cli_tipo = '" . $datos['cli_tipo'] . "',
 cli_direccion = '" . $datos['cli_direccion'] . "',
 cli_comp_direccion = '" . $datos['cli_comp_direccion'] . "',
 cli_distrito = '" . $datos['cli_distrito'] . "',
 cli_email = '" . $datos['cli_email'] . "',
 cli_telefono1 = '" . $datos['cli_telefono1'] . "',
 cli_telefono2 = '" . $datos['cli_telefono2'] . "',
 cli_grupo = '" . $datos['cli_grupo'] . "',
 cli_fpago_credito = '" . $datos['cli_fpago_credito'] ."',
 cli_moneda = " . $datos['cli_moneda'] . ",
 cli_lista_precio = '" . $datos['cli_lista_precio'] ."',
 cli_descuento = " . $datos['cli_descuento'] . ",
 cli_creditosol = " . $datos['cli_creditosol'] . ",
 cli_creditodol = " . $datos['cli_creditodol'] . ",
 cli_mantenimiento = " . $datos['cli_mantenimiento'] . ",
 cli_anticipo = '" . $datos['cli_anticipo'] . "',
 cli_ndespacho_efectivo = " . $datos['cli_ndespacho_efectivo'] . ",
 cli_fecactualiz = " . $datos['cli_fecactualiz'] . ",
 fecha_replicacion = " . $datos['fecha_replicacion'] . ",
 flg_replicacion = " . $datos['flg_replicacion'] . ",
 cli_estado = '" . $datos['cli_estado'] . "'
WHERE
 cli_codigo = '" . $_REQUEST['registroid'] . "';
";
			if ($sqlca->query($sql)<=0) {
				return 'OK';
			}
		} else {
			//$datos['cli_estado_desc']=($datos['cli_descuento']=='01'?1:0);
			$datos['cli_estado_desc'] = 1;

			/*if ($sqlca->perform('int_clientes', $datos, 'insert') >= 0) {      
				return 'OK';
			} else { 
				//return $sqlca->get_error(); 
				return 'error';
			}*/

			$sql = "
INSERT INTO int_clientes(
 cli_codigo,
 cli_razsocial,
 cli_rsocialbreve,
 cli_contacto,
 cli_ruc,

 cli_tipo,
 cli_direccion,
 cli_comp_direccion,
 cli_distrito,
 cli_email,

 cli_telefono1,
 cli_telefono2,
 cli_grupo,
 cli_fpago_credito,
 cli_moneda,

 cli_lista_precio,
 cli_descuento,
 cli_creditosol,
 cli_creditodol,
 cli_mantenimiento,

 cli_anticipo,
 cli_ndespacho_efectivo,
 cli_fecactualiz,
 fecha_replicacion,
 flg_replicacion,

 cli_estado_desc,
 cli_estado
) VALUES (
'" . strip_tags(stripslashes($datos['cli_codigo'])) . "',
'" . $datos['cli_razsocial'] . "',
'" . $datos['cli_rsocialbreve'] . "',
'" . $datos['cli_contacto'] . "',
'" . $datos['cli_ruc'] . "',

'" . $datos['cli_tipo'] . "',
'" . $datos['cli_direccion'] . "',
'" . $datos['cli_comp_direccion'] . "',
'" . $datos['cli_distrito'] . "',
'" . $datos['cli_email'] . "',

'" . $datos['cli_telefono1'] . "',
'" . $datos['cli_telefono2'] . "',
'" . $datos['cli_grupo'] . "',
'" . $datos['cli_fpago_credito'] . "',
'" . $datos['cli_moneda'] . "',

'" . $datos['cli_lista_precio'] . "',
'" . $datos['cli_descuento'] . "',
" . $datos['cli_creditosol'] . ",
" . $datos['cli_creditodol'] . ",
" . $datos['cli_mantenimiento'] . ",

'" . $datos['cli_anticipo'] . "',
" . $datos['cli_ndespacho_efectivo'] . ",
" . $datos['cli_fecactualiz'] . ",
" . $datos['fecha_replicacion'] . ",
" . $datos['flg_replicacion'] . ",

" . $datos['cli_estado_desc'] . ",
'" . $datos['cli_estado'] . "'
);
";

			//error_log($sql);

			if ($sqlca->query($sql) == 0) {
				return 'OK';
			}

				/*$query_funcion = "select interface_central_fn_maestros_consulta( to_date( to_char( now(),'yyyy-mm-dd'),'yyyy-mm-dd' ) )" ;

				if ($sqlca->query($query_funcion) < 0) {

				} else { 
					return $sqlca->get_error();
				}*/
				//return 'OK';
		}
    	//return '<error>Error</error>';
  	}

	/**
	* @param client: Codigo de cliente
	* @param lim: Limite de credio en soles o saldo
	* @param ctype: Variable pasada al llamar la funcion, verificar lo que se envia
		- ctype:  
			2: Efectivo
			1: Anticipo
			0: Credito
			4: Venta adelantada
	*/
	function obtenerLCDisponible($client,$lim,$ctype) { //ANALIZAR LINEA O SALDO DISPONIBLE
		global $sqlca;
		
		if($ctype == 4){ //Es venta adelantada
			$limit = $lim;

			//Facturas del cliente con flag anticipo
			$sql = "SELECT
							COALESCE(sum(f.nu_fac_valortotal),0)
						FROM
							fac_ta_factura_cabecera f
						WHERE
							f.cli_codigo = '{$client}'
							AND f.ch_fac_anticipo = 'S' --Flag anticipo
							AND f.nu_tipo_pago = '06' --Tipo de pago: Credito";
			$sqlca->query($sql);
			error_log("Es venta adelantada: ".$sql);
			if ($rr = $sqlca->fetchRow())
				$limit = $rr[0]; //Obtiene nuevo limite de credito en soles

			//Vales consumidos por el cliente o vales liquidados
			$sql = "SELECT
							COALESCE(sum(h.nu_importe),0)
						FROM
							val_ta_cabecera h
						WHERE
							h.ch_cliente = '{$client}'
							AND TRIM(ch_liquidacion) = 'LIQ'";
			$sqlca->query($sql);
			error_log("Es venta adelantada: ".$sql);
			if ($rr = $sqlca->fetchRow())
				$limit -= $rr[0];
			
			return $limit;
		}

		$limit  = $lim;
		if ($limit == NULL || $limit == 0 || $ctype == 2) //Limite de credito en soles == vacio / Limite de credito en soles == 0 / Es efectivo
			$limit = NULL;

		// Money already paid by the client
		if ($ctype == 1) { //Es anticipo
			$limit = 0;

			$sql =	"	SELECT" .
				"		COALESCE(sum(h.nu_importesaldo),0)" .
				"	FROM" .
				"		ccob_ta_cabecera h" .
				"	WHERE" .
				"		h.cli_codigo = '{$client}'" .
				"		AND h.ch_tipdocumento = '21';";
			$sqlca->query($sql);
			error_log("Es anticipo: ".$sql);
			if ($rr = $sqlca->fetchRow())
				$limit = $rr[0]; //Obtiene nuevo limite de credito en soles
		}

		// Clients sales not invoices
		if (($ctype == 0 && $limit != NULL) || $ctype == 1) { //Es credito && Limite de credito en soles != vacio / Es anticipo
			$sql =	"	SELECT" .
				"		COALESCE(sum(h.nu_importe),0)" .
				"	FROM" .
				"		val_ta_cabecera h" .
				"	WHERE" .
				"		h.ch_cliente = '{$client}'" .
				"		AND ch_liquidacion IS NULL;";
			$sqlca->query($sql);
			error_log("Es credito && Limite de credito en soles != vacio / Es anticipo: ".$sql);
			if ($rr = $sqlca->fetchRow())
				$limit -= $rr[0];
		}

		if ($ctype == 1) { //Es anticipo
			// Preapid invoiced
			$sql =	"	SELECT" .
				"		COALESCE(sum(h.nu_importesaldo),0)" .
				"	FROM" .
				"		ccob_ta_cabecera h" .
				"	WHERE" .
				"		h.cli_codigo = '{$client}'" .
				"		AND h.ch_tipdocumento = '22';";
			$sqlca->query($sql);
			error_log("Es anticipo: ".$sql);
			if ($rr = $sqlca->fetchRow())
				$limit -= $rr[0];
		} else if ( $ctype == 0 && $limit != NULL ) { //Es credito && Limite de credito en soles != vacio
			// Payment pending invoices
			$sql =	"	SELECT" .
				"		COALESCE(sum(x.xv),0)" .
				"	FROM" .
				"		(SELECT" .
				"			CASE" .
				"				WHEN h.ch_tipdocumento = '20' THEN sum(h.nu_importesaldo) * -1" .
				"				ELSE sum(h.nu_importesaldo)" .
				"			END AS xv" .
				"		FROM" .
				"			ccob_ta_cabecera h" .
				"	WHERE" .
				"			h.cli_codigo = '{$client}'" .
				"			AND h.ch_tipdocumento IN ('10','11','20','35')" .
				"		GROUP BY" .
				"			h.ch_tipdocumento" .
				"	) x;";
			$sqlca->query($sql);
			error_log("Es credito && Limite de credito en soles != vacio: ".$sql);
			if ($rr = $sqlca->fetchRow())
				$limit -= $rr[0];
		}

		if ($ctype == 1) { //Es anticipo - NC
		// Preapid invoiced
			$sql =	"	SELECT" .
				"		COALESCE(sum(h.nu_importesaldo),0)" .
				"	FROM" .
				"		ccob_ta_cabecera h" .
				"	WHERE" .
				"		h.cli_codigo = '{$client}'" .
				"		AND h.ch_tipdocumento = '20';";
			$sqlca->query($sql);
			error_log("Es anticipo - NC: ".$sql);
			if ($rr = $sqlca->fetchRow())
				$limit -= $rr[0];
		}
		return $limit;
	}

	function recuperarRegistroArray($registroid) {
		global $sqlca;

		$registro = array();

		$query = "
SELECT
 cli_codigo, --0
 cli_razsocial,
 cli_rsocialbreve,
 cli_direccion,
 cli_contacto,
 cli_comp_direccion,
 cli_ruc,
 cli_telefono1,
 cli_telefono2,
 cli_moneda,
 cli_distrito,
 cli_email,
 cli_lista_precio,
 cli_mantenimiento,
 cli_creditosol, --14
 cli_creditodol, --15
 cli_anticipo, --16
 cli_fpago_credito,
 cli_grupo,
 cli_tipo,
 cli_descuento,
 cli_ndespacho_efectivo, --21
 cli_estado
FROM
 int_clientes
WHERE 
 TRIM(cli_codigo)='".trim($registroid)."'
			";
			//echo $query;
    		$sqlca->query($query);

// Codigo   0
// Anticipo 16 N S
// Efectivo 21 0 1
// Credito  14
// Disponible 22
    		if ($reg = $sqlca->fetchRow()) {
			$registro = $reg;
			error_log( 'Cliente: '.json_encode( $registro ) );

			if ($registro[21] == "1" && $registro[16] == "N") { //Es efectivo
				$ctype = 2;
			} else {
				if ($registro[16] == "S" && $registro[21] == "0") { //Es anticipo
					$ctype = 1;
				} else if ($registro[16] == "N" && $registro[21] == "0") { //Es credito
					$ctype = 0;
				} else if ($registro[16] == "S" && $registro[21] == "1") { //Es venta adelantada
					$ctype = 4;
				} else {
					$ctype = 0;
				}
			}
			error_log("CType: ".$ctype);

			error_log( json_encode( array( $registro[0],$registro[14],$ctype ) ) );
			$registro[22] = ClienteModel::obtenerLCDisponible($registro[0],$registro[14],$ctype);
			error_log("Linea disponible: ".$registro[22]);
			if ($registro[22] === NULL)
				$registro[22] = "Ilimitado";
/*
			if ($registro[21] == "1")
				$registro[22] = "ND Efectivo";
			else if ($registro[16] == "N" && ($registro[14] == NULL || $registro[14] == 0))
				$registro[22] = "Ilimitado";
			else {
				$limit = $registro[14];
				if ($limit == NULL || $registro[16] == "S")
					$limit = 0;

// Vales no liquidados
				$sql =	"
SELECT
	COALESCE(sum(h.nu_importe),0)
FROM
	val_ta_cabecera h
WHERE
	h.ch_cliente = '" . addslashes($registro[0]) . "'
	AND ch_liquidacion IS NULL;";

				$sqlca->query($sql);
				if ($rr = $sqlca->fetchRow())
					$limit -= $rr[0];

				if ($registro[16] == "S") {
					$limit -= $registro[14];
// ANTICIPOS
//  Pagos de anticipos
					$sql = "
SELECT
	COALESCE(sum(h.nu_importesaldo),0)
FROM
	ccob_ta_cabecera h
WHERE
	h.cli_codigo = '" . addslashes($registro[0]) . "'
	AND h.ch_tipdocumento = '21';";
					$sqlca->query($sql);
					if ($rr = $sqlca->fetchRow())
						$limit -= $rr[0];

//  Facturacion de consumos
					$sql = "
SELECT
	COALESCE(sum(h.nu_importesaldo),0)
FROM
	ccob_ta_cabecera h
WHERE
	h.cli_codigo = '" . addslashes($registro[0]) . "'
	AND h.ch_tipdocumento = '22';";
					$sqlca->query($sql);
					if ($rr = $sqlca->fetchRow())
						$limit -= $rr[0];

					if ($registro[14] == NULL || $registro[14] == 0)
						$limit = "Ilimitado";
					$registro[22] = $limit;
				} else {
// CREDITOS
//  Cuentas por Cobrar
					$sql = "
SELECT
	COALESCE(sum(x.xv),0)
FROM
	(SELECT
		CASE
			WHEN h.ch_tipdocumento = '20' THEN sum(h.nu_importesaldo) * -1
			ELSE sum(h.nu_importesaldo)
		END AS xv
	FROM
		ccob_ta_cabecera h
	WHERE
		h.cli_codigo = ?
		AND h.ch_tipdocumento IN ('10','11','20','35')
	GROUP BY
		h.ch_tipdocumento
) x;";
					$sqlca->query($sql);
					if ($rr = $sqlca->fetchRow())
						$limit -= $rr[0];

					$registro[22] = $limit;
				}
			}*/
		}


    		return $registro;
  	}

	function CategoriasCBArray() {
		return array('AC'=>'ACTIVO', 'IN'=>'INACTIVO');  // ANTES ERA:  array('JU'=>'JUICIO',''=>'INACTIVO','AC'=>'ACTIVO')
	}

  	function tmListado($filtro=array(),$pp, $pagina) {
		global $sqlca;

		/* TIPOS DE CLIENTES 
		cli_anticipo = S ANTICIPO
		cli_ndespacho_efectivo = 1 EFECTIVO
		cli_ndespacho_efectivo = 0 CREDITO
		*/

		$cond = '';
		$cond2 = "";

		$filtro["tcliente"] = isset($filtro["tcliente"]) ? $filtro["tcliente"] : '0';
		if (isset($filtro["codigo"])) {
			if ($filtro["codigo"] == "") {
				if ($filtro["tcliente"] == "0") //TODOS
					$cond2 = "";
				elseif ($filtro["tcliente"] == "1")//CREDITO
					$cond2 = "WHERE cli_ndespacho_efectivo = '0' AND cli_anticipo = 'N'";
				elseif ($filtro["tcliente"] == "2")//EFECTIVO
					$cond2 = "WHERE cli_ndespacho_efectivo = '1' AND cli_anticipo = 'N'";
				elseif ($filtro["tcliente"] == "S")//ANTICIPO
					$cond2 = "WHERE cli_anticipo = 'S' AND cli_ndespacho_efectivo='0'";
				elseif ($filtro["tcliente"] == "3")//VENTA ADELANTADA
					$cond2 = "WHERE cli_ndespacho_efectivo = '1' AND cli_anticipo = 'S'";
			}

			if ($filtro["codigo"] != "" && $filtro["tcliente"] != "0") {
				$cond = "WHERE
					trim(cli_codigo)||''||trim(cli_razsocial)||''||trim(cli_ruc) ~ '".$filtro["codigo"]."'";
				if ($filtro["tcliente"] == "1")//CREDITO
					$cond .= "AND cli_ndespacho_efectivo = '0' AND cli_anticipo = 'N'";
				elseif ($filtro["tcliente"] == "2")//EFECTIVO
					$cond .= "AND cli_ndespacho_efectivo = '1' AND cli_anticipo = 'N'";
				elseif ($filtro["tcliente"] == "S")//ANTICIPO
					$cond .= "AND cli_anticipo = 'S' AND cli_ndespacho_efectivo='0'";
				elseif ($filtro["tcliente"] == "3")//VENTA ADELANTADA
					$cond .= "AND cli_ndespacho_efectivo = '1' AND cli_anticipo = 'S'";
			} elseif ($filtro["codigo"] != "") {
				$cond = "WHERE
					trim(cli_codigo)||''||trim(cli_razsocial)||''||trim(cli_ruc) ~ '".$filtro["codigo"]."'";
			}
		}

		$cond3 = "";
		if (isset($filtro["tarmag"])) {
			if ($filtro["tarmag"] == "D") {
				$cond3 = "cli_grupo,";
			}
		}

		$query = "
		SELECT
			cli_codigo,
			cli_razsocial,
			cli_rsocialbreve,
			cli_ruc,
			cli_direccion,
			cli_telefono1,
			cli_grupo,
			(CASE
				WHEN cli_ndespacho_efectivo = '0' AND cli_anticipo = 'N' THEN 'CREDITO'
				WHEN cli_ndespacho_efectivo = '1' AND cli_anticipo = 'N' THEN 'EFECTIVO'
				WHEN cli_ndespacho_efectivo = '0' AND cli_anticipo = 'S' THEN 'ANTICIPO'
				WHEN cli_ndespacho_efectivo = '1' AND cli_anticipo = 'S' THEN 'VENTA ADELANTADA'
			END) AS tipo_cliente,
			cli_distrito,
			cli_creditosol,
			cli_creditodol,
			cli_fpago_credito,
			cli_limite_consumo
		FROM 
			int_clientes ".
			" ".(isset($filtro['elemento'])?'':$cond)." ".
			" ".(isset($filtro['elemento'])?'':$cond2)." ".
				"
		ORDER BY
			$cond3
			cli_razsocial,
			cli_tipo desc
		";

		$resultado_1 = $sqlca->query($query);
		$numrows = $sqlca->numrows();

		/*if (!isset($filtro['elemento'])) {
			if($pp && $pagina)
				$paginador = new paginador($numrows,$pp, $pagina);
			else
	   			$paginador = new paginador($numrows,100,0);*/

		$paginador = new paginador($numrows, $pp, $pagina);

		$listado2['partir'] = $paginador->partir();
		$listado2['fin'] = $paginador->fin();	
		$listado2['numero_paginas'] = $paginador->numero_paginas();
		$listado2['pagina_previa'] = $paginador->pagina_previa();
		$listado2['pagina_siguiente'] = $paginador->pagina_siguiente();
		$listado2['pp'] = $paginador->pp;
		$listado2['paginas'] = $paginador->paginas();
		$listado2['primera_pagina'] = $paginador->primera_pagina();
		$listado2['ultima_pagina'] = $paginador->ultima_pagina();
	
		if ($pp > 0) {
	    	$query .= "LIMIT " . pg_escape_string($pp) . " ";
	    }
		if ($pagina > 0) {
			$skip = $pp * ($pagina - 1);
	    	$query .= "OFFSET " . $skip;
		}

		//echo $query;

		if ($sqlca->query($query) <= 0) {
				return $sqlca->get_error();
		}

		$listado = array();

		while ($reg = $sqlca->fetchRow()) {
			$reg['cli_codigo'] = trim($reg['cli_codigo']);
			if (!empty($reg['cli_codigo'])) {
				$listado['datos'][] = $reg;
			}
		}
		
		$query = "COMMIT";
		$sqlca->query($query);

   		$listado['paginacion'] = $listado2;
    
    	return $listado;
  	}


	function tmListadoTotal() {
		global $sqlca;

		$query = "
		SELECT
			cli_codigo,
			cli_razsocial,
			cli_rsocialbreve,
			cli_ruc,
			cli_direccion,
			cli_telefono1,
			cli_grupo,
			(CASE
				WHEN cli_ndespacho_efectivo = '0' AND cli_anticipo = 'N' THEN 'CREDITO'
				WHEN cli_ndespacho_efectivo = '1' AND cli_anticipo = 'N' THEN 'EFECTIVO'
				WHEN cli_ndespacho_efectivo = '0' AND cli_anticipo = 'S' THEN 'ANTICIPO'
			END) AS tipo_cliente,
			cli_distrito,
			cli_creditosol,
			cli_creditodol,
			cli_fpago_credito,
			cli_limite_consumo
		FROM 
			int_clientes 
		ORDER BY
			cli_razsocial,
			cli_tipo desc
		";

		$resultado_1 = $sqlca->query($query);
		$numrows = $sqlca->numrows();

		if ($sqlca->query($query) <= 0) {
				return $sqlca->get_error();
		}

		$listado = array();

		while ($reg = $sqlca->fetchRow()) {
			$reg['cli_codigo'] = trim($reg['cli_codigo']);
			if (!empty($reg['cli_codigo'])) {
				$listado['datos'][] = $reg;
			}
		}
		
		$query = "COMMIT";
		$sqlca->query($query);
    
    		return $listado;
  	}

	function FormaPagoCBArray($condicion = '') {
    		global $sqlca;

    		$cbArray = array();
    		$query = "SELECT tab_elemento, tab_descripcion  FROM int_tabla_general ".
            		 "WHERE tab_tabla = '96' AND tab_elemento<>'000000'";
	    	$query .= ($condicion!=''?' AND '.$condicion:'').' order by 1';

	    	if ($sqlca->query($query) <= 0)
	      		return $cbArray;

	    	while($result = $sqlca->fetchRow()) {
	      		$cbArray[trim($result["tab_elemento"])] = $result["tab_elemento"].' '.$result["tab_descripcion"];
	    	}
	    	ksort($cbArray);

	    	return $cbArray;
  	}

  	function ListaPreciosCBArray($condicion = '') {
    		global $sqlca; 

    		$cbArray = array();
    		$query = "SELECT tab_elemento, tab_descripcion  FROM int_tabla_general ".
             		"WHERE tab_tabla = 'LPRE' AND tab_elemento<>'000000'";
    		$query .= ($condicion!=''?' AND '.$condicion:'').' order by 1';
    	
    		if ($sqlca->query($query) <= 0)
      			return $cbArray;

    		while($result = $sqlca->fetchRow()){
      			$cbArray[trim($result["tab_elemento"])] = $result["tab_elemento"].' '.$result["tab_descripcion"];
    		}
    		ksort($cbArray);
    	
    		return $cbArray;
  	}

  	function DistritoCBArray($condicion = '') {
    		global $sqlca;

    		$cbArray = array();
    		$query = "SELECT tab_elemento, tab_descripcion  FROM int_tabla_general ".
            		 "WHERE tab_tabla = '02' AND tab_elemento<>'000000'";
    		$query .= ($condicion!=''?' AND '.$condicion:'').' order by 1';

    		if ($sqlca->query($query) <= 0)
      			return $cbArray;

    		while($result = $sqlca->fetchRow()) {
      			$cbArray[trim($result["tab_elemento"])] = $result["tab_elemento"].' '.$result["tab_descripcion"];
    		}
    		ksort($cbArray);

    		return $cbArray;
  	}
  
  	function RubrosCBArray($condicion = '') {
    		global $sqlca;

    		$cbArray = array();
    		$query = "SELECT tab_elemento, tab_descripcion  FROM int_tabla_general ".
            		 "WHERE tab_tabla = 'RCPG' AND tab_elemento<>'000000'";
    		$query .= ($condicion!=''?' AND '.$condicion:'').' order by 1';
    		
    		if ($sqlca->query($query) <= 0)
      			return $cbArray;

    		while($result = $sqlca->fetchRow()) {
      			$cbArray[trim($result["tab_elemento"])] = $result["tab_elemento"].' '.$result["tab_descripcion"];
    		}
   		ksort($cbArray);

    		return $cbArray;
  	}
  
  	function CuentasCBArray($condicion = '') {
   		global $sqlca;

    		$cbArray = array();
    		$query = "SELECT tab_elemento, tab_descripcion  FROM int_tabla_general ".
             		"WHERE tab_tabla = '03' AND tab_elemento<>'000000'";
    		$query .= ($condicion!=''?' AND '.$condicion:'').' order by 1';
    	
    		if ($sqlca->query($query) <= 0)
      			return $cbArray;

    		while($result = $sqlca->fetchRow()) {
      			$cbArray[trim($result["tab_elemento"])] = $result["tab_elemento"].' '.$result["tab_descripcion"];
    		}
    		ksort($cbArray);
    		
    		return $cbArray;
  	}
  
  	function TipoCtaBanCBArray($condicion = '') {
    		global $sqlca;

    		$cbArray = array();
    		$query = "SELECT tab_elemento, tab_descripcion  FROM int_tabla_general ".
       		      	"WHERE tab_tabla = 'TCBC' AND tab_elemento<>'000000'";
    		$query .= ($condicion!=''?' AND '.$condicion:'').' order by 1';

    		if ($sqlca->query($query) <= 0)
      			return $cbArray;

    		while($result = $sqlca->fetchRow()) {
      			$cbArray[trim($result["tab_elemento"])] = $result["tab_elemento"].' '.$result["tab_descripcion"];
    		}
    		ksort($cbArray);
    		
    		return $cbArray;
  	}

  	function AgregarCuenta($dato, $dato2, $dato3, $dato4, $dato5, $contador) {
  		$datos = $GLOBALS['CUENTAS'];
    		if($dato != '') {
        		for($i = 0; $i < $contador+1; $i++){
	    			$T = $datos[$i];
	   			if($T['codigo_banco'] == $dato && $T['nro_cuenta_bancaria'] == $dato3) { 	
		    			$mensaje = "Este articulo ya ha sido ingresado antes";
				}
			}
			if(!$mensaje) {
				$var = $contador+1;
			    	$datos[$var]['codigo_banco']                   = $dato;
			    	$datos[$var]['descrip_banco']                  = $dato2;
			    	$datos[$var]['nro_cuenta_bancaria']            = $dato3;
			    	$datos[$var]['tipo_cuenta_bancaria']           = $dato4;
			    	$datos[$var]['descrip_tipo_cuenta_bancaria']   = $dato5;
			}
    		}

    		return $datos;
  	}

   	function DescuentosCBArray($condicion = '') {
    		global $sqlca;

    		$cbArray = array();
    		$query = "SELECT ".
				    "substring(tab.tab_elemento for 2 from length(tab_elemento)-1 ) AS cod_descuento, ".
				    "tab.tab_descripcion AS des_descuento, ".
				    "round((tab_num_01/100),6) AS porc_descuento ".
				    "FROM  int_tabla_general tab ".
				    "WHERE tab_tabla= 'DESC' ".
				    "AND tab_elemento<>'000000' ".
    
    		$query .= ($condicion!=''?' AND '.$condicion:'').' ORDER BY des_descuento DESC';

    		if ($sqlca->query($query) <= 0)
      			return $cbArray;

    		while($result = $sqlca->fetchRow()) {
      			$cbArray['Datos'][trim($result["cod_descuento"])] = $result["cod_descuento"].' '.$result["des_descuento"];
      			$cbArray['Desc'][trim($result["cod_descuento"])]  = $result["porc_descuento"];
    		}
    		ksort($cbArray);

    		return $cbArray;
  	}

	/**
	* Verificar la siguiente información:
	- Notas de Despacho
	- Documento manuales de Venta
	- Tarjeta magéticas / placas
	*/

	function verify_credit_vouchers_sales_invoice_plates($arrDataGET){
		global $sqlca;

		$sql = "SELECT COUNT(*) AS nu_existe FROM val_ta_cabecera WHERE ch_cliente = '" . $arrDataGET["iCodigoCliente"] . "'";
		$iStatus = $sqlca->query($sql);

		//Verificar notas de despacho
		if ($iStatus < 0)
	    	$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'danger', 'sColor' => 'red', 'sMessage' => 'Problemas al ejecutar SQL - verify_plates()');
	   	else {
    		$row = $sqlca->fetchRow();
    		$iExisteDocumento = (int)$row["nu_existe"];
    		$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'success', 'sMessage' => 'No tiene ninguna nota de despacho');
		    if ($iExisteDocumento > 0)
		    	$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'warning', 'sColor' => 'orange', 'sMessage' => 'El cliente tiene nota(s) de despacho(s), verificar en la opción Ventas -> Vales de Crédito -> Registro de Vales');
		    else {//Verificar documentos manuales de venta
				$sql = "SELECT COUNT(*) AS nu_existe FROM fac_ta_factura_cabecera WHERE cli_codigo = '" . $arrDataGET["iCodigoCliente"] . "'";
				$iStatus = $sqlca->query($sql);
				if ($iStatus < 0)
		    		$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'danger', 'sColor' => 'red', 'sMessage' => 'Problemas al ejecutar SQL - verify_plates()');
			   	else {
		    		$row = $sqlca->fetchRow();
		    		$iExisteDocumento = (int)$row["nu_existe"];
		    		$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'success', 'sMessage' => 'No tiene ningún documento manual de venta');
				    if ($iExisteDocumento > 0)
				    	$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'warning', 'sColor' => 'orange', 'sMessage' => 'El cliente tiene documento(s) manual(es) de venta, verificar en la opción Ventas -> Facturas de Venta');
				    else {//Verificar si tiene placa / tarjeta magnética asignada
						$sql = "SELECT COUNT(*) AS nu_existe FROM pos_fptshe1 WHERE codcli = '" . $arrDataGET["iCodigoCliente"] . "'";
						$iStatus = $sqlca->query($sql);

						if ($iStatus < 0)
					    	$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'danger', 'sColor' => 'red', 'sMessage' => 'Problemas al ejecutar SQL - verify_plates()');
					   	else {
				    		$row = $sqlca->fetchRow();
				    		$iExisteDocumento = (int)$row["nu_existe"];
				    		$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'success', 'sMessage' => 'No tiene ninguna placa / tarjeta asignada');
						    if ($iExisteDocumento > 0)
						    	$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'warning', 'sColor' => 'orange', 'sMessage' => 'El cliente tiene placa / tarjeta asignada(s), verificar en la opción Ventas -> Reglas de Ventas -> Tarjeta Magnéticas');
						}
					} // /. Verificar si tiene placa / tarjeta magnética asignada
			    }
			} // /. Verificar documentos manuales de venta
		} // /. Verificar notas de despacho
	    return $arrResponse;
	}

	function delete_partner($arrDataGET){
		global $sqlca;

		$sql = "DELETE FROM int_clientes WHERE cli_codigo = '" . $arrDataGET["iCodigoCliente"] . "'";
		$iStatus = $sqlca->query($sql);

		$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'danger', 'sMessage' => 'Problemas al eliminar cliente SQL - delete_partner()');
		if ($iStatus >= 0)
			$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'success', 'sMessage' => 'Cliente eliminado satisfactoriamente');
	    return $arrResponse;
	}
}
