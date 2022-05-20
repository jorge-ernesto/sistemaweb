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

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once("/sistemaweb/include/config.php");
include_once("/sistemaweb/include/dbsqlca.php");

date_default_timezone_set('America/Lima');

global $db_host, $db_user, $db_password, $db_name;
$sqlca = new pgsqlDB($db_host, $db_user, $db_password, $db_name);

class O7Interface{
	
	private $iStatusDebug = false;
	private $fFactorConversionGLP = 3.785411784;

	function debugO7Interface($arrData){
		if ( isset($arrData->debug_o7) && $arrData->debug_o7 == true ) {
			$this->iStatusDebug = true;
			return $this->iStatusDebug;
		}
		return $this->iStatusDebug;
	}

    private function getAllDateFormat($sTypeDate = ''){
        $arrFecha = localtime(time(), true);

        $iYear = (1900 + $arrFecha['tm_year']);
        $iMonth = (strlen(1 + $arrFecha['tm_mon']) > 1 ? (1 + $arrFecha['tm_mon']) : '0' . (1 + $arrFecha['tm_mon']));
        $iDay = (strlen($arrFecha['tm_mday']) > 1 ? $arrFecha['tm_mday'] : '0' . $arrFecha['tm_mday']);
        
        $iHour = $arrFecha['tm_hour'];
        $iMinute = $arrFecha['tm_min'];
        $iSecond = $arrFecha['tm_sec'];
        
        if ($sTypeDate == 'dia')
            $dHoy_Hour = $iDay;
        else if ($sTypeDate == 'mes')
            $dHoy_Hour = $iMonth;
        else if ($sTypeDate == 'año')
            $dHoy_Hour = $iYear;
        else if ($sTypeDate == 'fecha_inicial_ymd')
            $dHoy_Hour = $iYear . '-' . $iMonth . '-01';
        else if ($sTypeDate == 'fecha_ymd')
            $dHoy_Hour = $iYear . '-' . $iMonth . '-' . $iDay;
        else if ($sTypeDate == 'fecha_inicial_dmy')
            $dHoy_Hour = '01/' . $iMonth . '/' . $iYear;
        else if ($sTypeDate == 'fecha_dmy')
            $dHoy_Hour =  $iDay . '/' . $iMonth . '/' . $iYear;
        else if ($sTypeDate == 'fecha_hora')
            $dHoy_Hour = $iYear . '-' . $iMonth . '-' . $iDay . ' ' . $iHour . ':' . $iMinute . ':' . $iSecond;
        else if ($sTypeDate == 'hora')
            $dHoy_Hour = $iHour . ':' . $iMinute . ':' . $iSecond;
        return $dHoy_Hour;
    }

    function validateToken(){
    	global $sqlca;

		$token_key_httpheader = null;
		$arrHeaders = apache_request_headers();
		if(isset($arrHeaders['Authorization'])){
			$arrMatches = array();
			preg_match('/Token token="(.*)"/', $arrHeaders['Authorization'], $arrMatches);
			if(isset($arrMatches[1])){
				$token_key_httpheader = $arrMatches[1];
			}
		}

		$sql = "SELECT par_valor FROM int_parametros WHERE par_nombre='o7_apikey'";
		$iStatusSQL = $sqlca->query($sql);
		if ((int)$iStatusSQL <= 0) {
		    return array(
		    	'sStatus' => 'danger',
		    	'sMessage' => 'No existe Token',
                'sMessageSQL' => (($this->iStatusDebug) ? $sqlca->get_error() : ""),
		   	);
		}
		$row = $sqlca->fetchRow();
		$token_key_db=$row['par_valor'];

		if ( $token_key_httpheader != $token_key_db ){
		    return array(
		    	'sStatus' => 'danger',
		    	'sMessage' => 'Token invalido',
		    	'token_key_httpheader' => (($this->iStatusDebug) ? $token_key_httpheader : ""),
		    	'token_key_db' => (($this->iStatusDebug) ? $token_key_db : ""),
		   	);
		}

	    return array(
	    	'sStatus' => 'success',
	    	'sMessage' => 'Token valido',
	   	);
    }

    function managerTransaction($sNameTransaction){
    	global $sqlca;

    	try {
			$iStatusSQL = $sqlca->query($sNameTransaction);
			if ((int)$iStatusSQL < 0) {
			    return array(
			    	'sStatus' => 'danger',
			    	'sMessage' => 'Error al iniciar transaccion SQL - function managerTransaction(' . $sNameTransaction . ')',
	                'sMessageSQL' => (($this->iStatusDebug) ? $sqlca->get_error() : ""),
			   	);
			}
		    return array(
		    	'sStatus' => 'success',
		    	'sMessage' => $sNameTransaction . ' ejecutado satisfactoriamente'
		   	);
    	} catch (Exception $e) {
	        return array(
	            'sStatus' => 'danger',
	            'sMessage' => 'problemas con transaccion ' . $sNameTransaction,
                'sMessagePHP' => $e->getMessage(),
	        );    		
    	}
    }

/*
#########################################################
GENERAR ITEM
#########################################################
*/
    function crudItem($arrData){
    	global $sqlca;
        try {
        	if ( isset($arrData->debug_o7) ) {
        		$this->debugO7Interface($arrData);
        	}

        	$arrResponseToken = $this->validateToken();
        	if ( $arrResponseToken['sStatus'] != "success" ) {
	            return $arrResponseToken;
        	}

        	$this->managerTransaction("BEGIN");

        	if ( $arrData->tipo_item != "1" && $arrData->tipo_item != "2" ) {
        		$this->managerTransaction("ROLLBACK");

	            return array(
	                'sStatus' => 'danger',
	                'sMessage' => 'Tipo de item no definido',
	            );
        	}

        	// Verificar familia
        	$arrDataSQL = array(
        		'sql' => "SELECT tab_elemento FROM int_tabla_general WHERE tab_tabla='21' AND tab_elemento!='000000' AND tab_elemento='" . pg_escape_string($arrData->id_tipo) . "' LIMIT 1",
        		'sMessageDanger' => 'Problemas al buscar el tipo de familia',
        		'sMessageWarning' => 'No existe el codigo ' . pg_escape_string($arrData->id_tipo) . ' en la tabla de familia',
        	);
        	$arrResponse = $this->checkRegister($arrDataSQL);
        	if ( $arrResponse['sStatus'] != "success" ) {
	            return $arrResponse;
        	}

        	// Verificar categoria / linea / subfamilia
        	$arrDataSQL = array(
        		'sql' => "SELECT tab_elemento FROM int_tabla_general WHERE tab_tabla='20' AND tab_elemento!='000000' AND tab_elemento='" . pg_escape_string($arrData->id_linea) . "' LIMIT 1",
        		'sMessageDanger' => 'Problemas al buscar linea / categoria / subfamilia',
        		'sMessageWarning' => 'No existe el codigo ' . pg_escape_string($arrData->id_linea) . ' en la tabla linea / categoria / subfamilia',
        	);
        	$arrResponse = $this->checkRegister($arrDataSQL);
        	if ( $arrResponse['sStatus'] != "success" ) {
	            return $arrResponse;
        	}

        	// Verificar marca
        	$arrDataSQL = array(
        		'sql' => "SELECT tab_elemento FROM int_tabla_general WHERE tab_tabla='23' AND tab_elemento!='000000' AND tab_elemento='" . pg_escape_string($arrData->id_marca) . "' LIMIT 1",
        		'sMessageDanger' => 'Problemas al buscar marca',
        		'sMessageWarning' => 'No existe el codigo ' . pg_escape_string($arrData->id_marca) . ' en la tabla marca',
        	);
        	$arrResponse = $this->checkRegister($arrDataSQL);
        	if ( $arrResponse['sStatus'] != "success" ) {
	            return $arrResponse;
        	}

        	// Verificar SKU
        	$arrDataSQL = array(
        		'sql' => "SELECT tab_elemento FROM int_tabla_general WHERE tab_tabla='CSKU' AND tab_elemento!='000000' AND tab_elemento='" . pg_escape_string($arrData->id_sku) . "' LIMIT 1",
        		'sMessageDanger' => 'Problemas al buscar SKU',
        		'sMessageWarning' => 'No existe el codigo ' . pg_escape_string($arrData->id_sku) . ' en la tabla SKU',
        	);
        	$arrResponse = $this->checkRegister($arrDataSQL);
        	if ( $arrResponse['sStatus'] != "success" ) {
	            return $arrResponse;
        	}

        	// Verificar unidad de medida
        	$arrDataSQL = array(
        		'sql' => "SELECT tab_elemento FROM int_tabla_general WHERE tab_tabla='34' AND tab_elemento!='000000' AND tab_elemento='" . pg_escape_string($arrData->id_unidad) . "' LIMIT 1",
        		'sMessageDanger' => 'Problemas al buscar unidad de medida',
        		'sMessageWarning' => 'No existe el codigo ' . pg_escape_string($arrData->id_unidad) . ' en la tabla unidad de medida',
        	);
        	$arrResponse = $this->checkRegister($arrDataSQL);
        	if ( $arrResponse['sStatus'] != "success" ) {
	            return $arrResponse;
        	}

        	// Verificar unidad de presentacion
        	$arrDataSQL = array(
        		'sql' => "SELECT tab_elemento FROM int_tabla_general WHERE tab_tabla='35' AND tab_elemento!='000000' AND tab_elemento='" . pg_escape_string($arrData->id_presentacion) . "' LIMIT 1",
        		'sMessageDanger' => 'Problemas al buscar unidad de presentacion',
        		'sMessageWarning' => 'No existe el codigo ' . pg_escape_string($arrData->id_presentacion) . ' en la tabla unidad de presentacion',
        	);
        	$arrResponse = $this->checkRegister($arrDataSQL);
        	if ( $arrResponse['sStatus'] != "success" ) {
	            return $arrResponse;
        	}

        	// Verificar ubicacion de inventario
        	$arrDataSQL = array(
        		'sql' => "SELECT cod_ubicac FROM inv_ta_ubicacion WHERE cod_ubicac='" . pg_escape_string($arrData->id_ubicacion) . "' LIMIT 1",
        		'sMessageDanger' => 'Problemas al buscar ubicación de inventario',
        		'sMessageWarning' => 'No existe el codigo ' . pg_escape_string($arrData->id_ubicacion) . ' en la tabla ubicación de inventario',
        	);
        	$arrResponse = $this->checkRegister($arrDataSQL);
        	if ( $arrResponse['sStatus'] != "success" ) {
	            return $arrResponse;
        	}

        	// Verificar ubicacion de inventario
        	$arrDataSQL = array(
        		'sql' => "SELECT tab_elemento FROM int_tabla_general WHERE tab_tabla='17' AND tab_elemento!='000000' AND tab_elemento='" . pg_escape_string($arrData->id_impuesto) . "' LIMIT 1",
        		'sMessageDanger' => 'Problemas al buscar impuesto',
        		'sMessageWarning' => 'No existe el codigo ' . pg_escape_string($arrData->id_impuesto) . ' en la tabla impuesto',
        	);
        	$arrResponse = $this->checkRegister($arrDataSQL);
        	if ( $arrResponse['sStatus'] != "success" ) {
	            return $arrResponse;
        	}

        	if ( $arrData->operacion == 'generar_item' ) {
	        	$arrDataSQL = array(
	        		'sql' => "SELECT art_codigo FROM int_articulos WHERE art_codigo='" . pg_escape_string($arrData->id_item) . "' LIMIT 1",
	        		'sMessageDanger' => 'Problemas al Verificar item',
	        		'sMessageWarning' => 'Item ya fue registrado',
	        	);
	        	$arrResponse = $this->checkItem($arrDataSQL);
	        	if ( $arrResponse['sStatus'] != "success" ) {
		            return $arrResponse;
	        	}

        		$sql="
INSERT INTO int_articulos (
art_plutipo,
art_codigo,
art_descripcion,
art_descbreve,
art_tipo,
art_linea,
art_clase,
art_cod_sku,
art_unidad,
art_presentacion,
art_cod_ubicac,
art_impuesto1,
art_estado,
art_usuario,
art_fecactuliz
) VALUES (
'" . pg_escape_string($arrData->tipo_item). "',
'" . pg_escape_string($arrData->id_item) . "',
'" . pg_escape_string($arrData->descripcion) . "',
'" . pg_escape_string($arrData->descripcion_breve) . "',
'" . pg_escape_string($arrData->id_tipo) . "',
'" . pg_escape_string($arrData->id_linea) . "',
'" . pg_escape_string($arrData->id_marca) . "',
'" . pg_escape_string($arrData->id_sku) . "',
'" . pg_escape_string($arrData->id_unidad) . "',
'" . pg_escape_string($arrData->id_presentacion) . "',
'" . pg_escape_string($arrData->id_ubicacion) . "',
'" . pg_escape_string($arrData->id_impuesto) . "',
'" . pg_escape_string($arrData->estado) . "',
'" . pg_escape_string($arrData->usuario) . "',
'" . $this->getAllDateFormat('fecha_ymd') . "'
);
        		";
	        	$iStatusSQL = $sqlca->query($sql);
	        	if ( (int)$iStatusSQL < 0 ) {
	        		$this->managerTransaction("ROLLBACK");

		            return array(
		                'sStatus' => 'danger',
		                'sMessage' => 'Problemas al generar Item',
		                'sMessageSQL' => (($this->iStatusDebug) ? $sqlca->get_error() : ""),
			            'SQL' => (($this->iStatusDebug) ? $sql : ""),
		            );
	        	}

	            if ( isset($arrData->lista_precios) ){
			        if ( !empty($arrData->lista_precios) ){
			        	$arrResponseListaPrecio = $this->crudListaPrecio($arrData->id_item, $arrData->lista_precios, '');
			            if ( $arrResponseListaPrecio['sStatus'] != "success" ){
	        				$this->managerTransaction("ROLLBACK");

				            return $arrResponseListaPrecio;
			            }
			        } else {
	        			$this->managerTransaction("ROLLBACK");

			            return array(
			                'sStatus' => 'danger',
			                'sMessage' => 'Lista de precio sin parametros',
			            );
			        }
	            }

	            if ( $arrData->tipo_item != "2" && isset($arrData->enlace_items) ) {
	    			$this->managerTransaction("ROLLBACK");

		            return array(
		                'sStatus' => 'danger',
		                'sMessage' => 'Los enlaces de item son exclusivos para el tipo de item(2 = PLU SALIENTE)',
		            );            	
	            }

	            if ( isset($arrData->enlace_items) ){
			        if ( !empty($arrData->enlace_items) ){
			        	$arrResponseEnlaceItem = $this->crudEnlaceItem($arrData->id_item, $arrData->enlace_items);
			            if ( $arrResponseEnlaceItem['sStatus'] != "success" ){
	        				$this->managerTransaction("ROLLBACK");

				            return $arrResponseEnlaceItem;
			            }
			        } else {
	        			$this->managerTransaction("ROLLBACK");

			            return array(
			                'sStatus' => 'danger',
			                'sMessage' => 'Enlace de items sin parametros',
			            );
			        }
	            }

	        	$this->managerTransaction("COMMIT");

	            return array(
	                'sStatus' => 'success',
	                'sMessage' => 'Item registrado',
	            );
	        } else {
        		// Verificar item
	        	$arrDataSQL = array(
	        		'sql' => "SELECT art_codigo FROM int_articulos WHERE art_codigo='" . pg_escape_string($arrData->id_item) . "' LIMIT 1",
	        		'sMessageDanger' => 'Problemas al buscar el item',
	        		'sMessageWarning' => 'No existe el item ' . pg_escape_string($arrData->id_item),
	        	);
	        	$arrResponse = $this->checkRegister($arrDataSQL);
	        	if ( $arrResponse['sStatus'] != "success" ) {
		            return $arrResponse;
	        	}

        		$sql="
UPDATE
 int_articulos
SET
 art_plutipo = '" . pg_escape_string($arrData->tipo_item). "',
 art_descripcion = '" . pg_escape_string($arrData->descripcion). "',
 art_descbreve = '" . pg_escape_string($arrData->descripcion_breve). "',
 art_tipo = '" . pg_escape_string($arrData->id_tipo). "',
 art_linea = '" . pg_escape_string($arrData->id_linea). "',
 art_clase = '" . pg_escape_string($arrData->id_marca). "',
 art_cod_sku = '" . pg_escape_string($arrData->id_sku). "',
 art_unidad = '" . pg_escape_string($arrData->id_unidad). "',
 art_presentacion = '" . pg_escape_string($arrData->id_presentacion). "',
 art_cod_ubicac = '" . pg_escape_string($arrData->id_ubicacion). "',
 art_impuesto1 = '" . pg_escape_string($arrData->id_impuesto). "',
 art_estado = '" . pg_escape_string($arrData->estado). "',
 art_usuario = '" . pg_escape_string($arrData->usuario). "',
 art_fecactuliz = '" . $this->getAllDateFormat('fecha_ymd') . "'
WHERE
 art_codigo = '" . pg_escape_string($arrData->id_item) . "'
        		";

	        	$iStatusSQL = $sqlca->query($sql);
	        	if ( (int)$iStatusSQL < 0 ) {
	        		$this->managerTransaction("ROLLBACK");

		            return array(
		                'sStatus' => 'danger',
		                'sMessage' => 'Problemas al modificar Item',
		                'sMessageSQL' => (($this->iStatusDebug) ? $sqlca->get_error() : ""),
			            'SQL' => (($this->iStatusDebug) ? $sql : ""),
		            );
	        	}

	        	$this->managerTransaction("COMMIT");

	            return array(
	                'sStatus' => 'success',
	                'sMessage' => 'Item modificado',
	            );
	        }// /. If and Else
        } catch (Exception $e) {
        	$this->managerTransaction("ROLLBACK");

            return array(
                'sStatus' => 'danger',
                'sMessage' => 'Problemas al generar Item',
                'sMessagePHP' => $e->getMessage(),
            );
        }
    }

    function crudListaPrecio($sIdItem, $arrListaPrecio, $sOperacion = ''){
    	global $sqlca;

    	try {
			foreach ($arrListaPrecio as $key => $row) {
				if( empty($row) ){
        			$this->managerTransaction("ROLLBACK");

		            return array(
		                'sStatus' => 'danger',
		                'sMessage' => 'Lista de precio vacia',
		            );
				}

				if( empty($row->id_lista_precio) || $row->id_lista_precio=='' ){
	        		$this->managerTransaction("ROLLBACK");

		            return array(
		                'sStatus' => 'danger',
		                'sMessage' => 'ID Lista de precio vacía',
		                'arrData' => $row,
		            );
				}

				// Verificar Lista de precio
	        	$arrDataSQL = array(
	        		'sql' => "SELECT tab_descripcion FROM int_tabla_general WHERE tab_tabla ='LPRE' AND tab_elemento!= '000000' AND tab_elemento = '" . pg_escape_string($row->id_lista_precio) . "' LIMIT 1",
	        		'sMessageDanger' => 'Problemas al buscar el tipo de lista de precio',
	        		'sMessageWarning' => 'No existe el codigo ' . pg_escape_string($row->id_lista_precio) . ' en la lista de precio',
	        	);
	        	$arrResponse = $this->checkRegister($arrDataSQL);
	        	if ( $arrResponse['sStatus'] != "success" ) {
		            return $arrResponse;
	        	}

	        	if ( isset($sOperacion) && !empty($sOperacion) ) {
	        		$row->operacion = 'modificar_lista_precio';
	        	}

				if( $row->operacion == 'generar_lista_precio'){
	 				$sql="
INSERT INTO fac_lista_precios (
pre_lista_precio,
art_codigo,
pre_moneda,
pre_precio_act1,
pre_usuario,
fecha_replicacion
) VALUES (
'" . pg_escape_string($row->id_lista_precio) . "',
'" . pg_escape_string($sIdItem) . "',
'" . pg_escape_string($row->id_moneda) . "',
" . pg_escape_string($row->precio_venta) . ",
'" . pg_escape_string($row->usuario) . "',
NOW()
);
	 				";
		        	$iStatusSQL = $sqlca->query($sql);
		        	if ( (int)$iStatusSQL<0 ) {
	        			$this->managerTransaction("ROLLBACK");

			            return array(
			                'sStatus' => 'danger',
			                'sMessage' => 'Problemas al generar Lista de precio',
			                'sMessageSQL' => (($this->iStatusDebug) ? $sqlca->get_error() : ""),
			                'SQL' => (($this->iStatusDebug) ? $sql : ""),
			            );
		        	}

		        	$sAction = 'registrada';
	        	} else {
	        		// Verificar item
		        	$arrDataSQL = array(
		        		'sql' => "SELECT art_codigo FROM int_articulos WHERE art_codigo = '" . pg_escape_string($row->id_item) . "' LIMIT 1",
		        		'sMessageDanger' => 'Problemas al buscar el item',
		        		'sMessageWarning' => 'No existe el item ' . pg_escape_string($row->id_item),
		        	);
		        	$arrResponse = $this->checkRegister($arrDataSQL);
		        	if ( $arrResponse['sStatus'] != "success" ) {
			            return $arrResponse;
		        	}

		        	// Verificar moneda
		        	$arrDataSQL = array(
		        		'sql' => "SELECT tab_elemento FROM int_tabla_general WHERE tab_tabla ='04' AND tab_elemento!= '000000' AND tab_elemento = '" . pg_escape_string(str_pad($row->id_moneda, 6, '0', STR_PAD_LEFT)) . "' LIMIT 1",
		        		'sMessageDanger' => 'Problemas al buscar el tipo de moneda',
		        		'sMessageWarning' => 'No existe el codigo ' . pg_escape_string($row->id_moneda) . ' en la tabla moneda',
		        	);
		        	$arrResponse = $this->checkRegister($arrDataSQL);
		        	if ( $arrResponse['sStatus'] != "success" ) {
			            return $arrResponse;
		        	}

	 				$sql="
UPDATE
 fac_lista_precios
SET
 pre_moneda = '" . pg_escape_string($row->id_moneda) . "',
 pre_precio_act1 = " . pg_escape_string($row->precio_venta) . ",
 pre_usuario = '" . pg_escape_string($row->usuario) . "',
 fecha_replicacion = now()
WHERE
 pre_lista_precio = '" . pg_escape_string($row->id_lista_precio) . "'
 AND art_codigo = '" . pg_escape_string($row->id_item) . "'
	 				";
		        	$iStatusSQL = $sqlca->query($sql);
		        	if ( (int)$iStatusSQL<0 ) {
	        			$this->managerTransaction("ROLLBACK");

			            return array(
			                'sStatus' => 'danger',
			                'sMessage' => 'Problemas al modificar Lista de precio',
			                'sMessageSQL' => (($this->iStatusDebug) ? $sqlca->get_error() : ""),
			                'SQL' => (($this->iStatusDebug) ? $sql : ""),
			            );
		        	}

		        	$sAction = 'modificada';
				}// /. If and Else
			}// /. Foreach
            return array(
                'sStatus' => 'success',
                'sMessage' => 'Lista de precio ' . $sAction,
            );
    	} catch (Exception $e) {
        	$this->managerTransaction("ROLLBACK");

            return array(
                'sStatus' => 'danger',
                'sMessage' => 'Problemas al generar Lista de precio',
                'sMessagePHP' => $e->getMessage(),
            );
    	}
    }

    function crudEnlaceItem($sIdItem, $arrEnlaceItems){
    	global $sqlca;

    	try {
			foreach ($arrEnlaceItems as $key => $row) {
				if( empty($row) ){
        			$this->managerTransaction("ROLLBACK");

		            return array(
		                'sStatus' => 'danger',
		                'sMessage' => 'Enlace de items vacío',
		            );
				}

				if( $row->operacion != "generar_enlace_item" ){
	        		$this->managerTransaction("ROLLBACK");

		            return array(
		                'sStatus' => 'danger',
		                'sMessage' => 'Operacion no definida para Enlace de Item',
		            );
				}

				// Verificar Item tipo ESTANDAR
	        	$arrDataSQL = array(
	        		'sql' => "SELECT art_codigo FROM int_articulos WHERE art_codigo = '" . pg_escape_string($row->id_item_enlace) . "' AND art_plutipo='1' LIMIT 1",
	        		'sMessageDanger' => 'Problemas al buscar Enlace de Item',
	        		'sMessageWarning' => 'Enlace de Item no existe item o es un tipo (2 = PLU SALIENTE)',
	        	);
	        	$arrResponse = $this->checkRegister($arrDataSQL);
	        	if ( $arrResponse['sStatus'] != "success" ) {
		            return $arrResponse;
	        	}

	        	// Verificar Item
	        	$arrDataSQL = array(
	        		'sql' => "SELECT art_codigo FROM int_ta_enlace_items WHERE ch_item_estandar = '" . pg_escape_string($row->id_item_enlace) . "' LIMIT 1",
	        		'sMessageDanger' => 'Problemas al verificar Enlace de Item',
	        		'sMessageWarning' => 'Enlace de Item ya fue registrado',
	        	);
	        	$arrResponse = $this->checkItem($arrDataSQL);
	        	if ( $arrResponse['sStatus'] != "success" ) {
		            return $arrResponse;
	        	}

	        	$arrSQLEnlaceItem[] = "(
'" . pg_escape_string($sIdItem) . "',
'" . pg_escape_string($row->id_item_enlace) . "',
" . pg_escape_string($row->cantidad_descargar) . ",
'" . pg_escape_string($row->usuario) . "',
NOW()
)
				";
			}

    		$sql = "
INSERT INTO int_ta_enlace_items (
art_codigo,
ch_item_estandar,
nu_cantidad_descarga,
ch_usuario,
fecha_replicacion
) VALUES " . implode(',', $arrSQLEnlaceItem);
			;

        	$iStatusSQL = $sqlca->query($sql);
        	if ( (int)$iStatusSQL < 0 ) {
    			$this->managerTransaction("ROLLBACK");

	            return array(
	                'sStatus' => 'danger',
	                'sMessage' => 'Problemas al generar Enlace de Item',
	                'sMessageSQL' => (($this->iStatusDebug) ? $sqlca->get_error() : ""),
	                'SQL' => (($this->iStatusDebug) ? $sql : ""),
	            );
        	}

            return array(
                'sStatus' => 'success',
                'sMessage' => 'Enlace de Item registrado',
            );    		
    	} catch (Exception $e) {
        	$this->managerTransaction("ROLLBACK");

            return array(
                'sStatus' => 'danger',
                'sMessage' => 'Problemas al generar Enlace de Item',
                'sMessagePHP' => $e->getMessage(),
            );
    	}
    }

/*
#########################################################
GENERAR MOVIMIENTO DE INVENTARIO
#########################################################
*/
    function crudMovimientoInventario($arrData){
    	global $sqlca;
        try {
        	if ( isset($arrData->debug_o7) ) {
        		$this->debugO7Interface($arrData);
        	}

        	$arrResponse = $this->validateToken();
        	if ( $arrResponse['sStatus'] != "success" ) {
	            return $arrResponse;
        	}

        	$this->managerTransaction("BEGIN");

        	$arrDataSQL = array(
        		'sql' => "SELECT tran_codigo FROM inv_movialma WHERE tran_codigo='" . pg_escape_string($arrData->id_tipo_movimiento) . "' AND mov_entidad='" . pg_escape_string($arrData->id_entidad) . "' AND mov_tipdocuref='" . pg_escape_string($arrData->tipo_comprobante) . "' AND mov_docurefe='" . pg_escape_string($arrData->serie) . pg_escape_string($arrData->numero) . "' AND art_codigo='" . pg_escape_string($arrData->id_item) . "' AND DATE(mov_fecha)='" . pg_escape_string($arrData->fecha_emision) . "' LIMIT 1",
        		'sMessageDanger' => 'Problemas al Verificar movimiento de inventario',
        		'sMessageWarning' => 'El registro ya fue existe',
        	);
        	$arrResponse = $this->checkItem($arrDataSQL);
        	if ( $arrResponse['sStatus'] != "success" ) {
	            return $arrResponse;
        	}

			// Verificar tipo de movimiento
        	$arrDataSQL = array(
        		'sql' => "SELECT tran_codigo FROM inv_tipotransa WHERE tran_codigo = '" . pg_escape_string($arrData->id_tipo_movimiento) . "' LIMIT 1;",
        		'sMessageDanger' => 'Problemas al verificar tipo de movimiento de inventario',
        		'sMessageWarning' => 'No se encontro el tipo de movimiento de inventario',
        	);
        	$arrResponse = $this->checkRegister($arrDataSQL);
        	if ( $arrResponse['sStatus'] != "success" ) {
	            return $arrResponse;
        	}

        	// Verificar que exista almacén origen
        	$arrDataSQL = array(
        		'sql' => "SELECT ch_almacen FROM inv_ta_almacenes WHERE ch_almacen ='" . pg_escape_string($arrData->id_almacen_origen) . "' LIMIT 1;",
        		'sMessageDanger' => 'Problemas al verificar almacén origen',
        		'sMessageWarning' => 'No existe almacén origen',
        	);
        	$arrResponse = $this->checkRegister($arrDataSQL);
        	if ( $arrResponse['sStatus'] != "success" ) {
	            return $arrResponse;
        	}

        	// Obtener número de formulario
			$sql = "
UPDATE
 inv_tipotransa
SET
 tran_nform = (SELECT (tran_nform + 1) FROM inv_tipotransa WHERE tran_codigo = '" . $arrData->id_tipo_movimiento . "')
WHERE
 tran_codigo = '" . $arrData->id_tipo_movimiento . "'
RETURNING
 '" . $arrData->id_almacen_origen . "'||LPAD(CAST((tran_nform::INTEGER) AS bpchar), 7, '0') AS formulario;
";
	    	$iStatusSQL = $sqlca->query($sql);
	    	if ( (int)$iStatusSQL < 0 ) {
	    		$this->managerTransaction("ROLLBACK");

	            return array(
	                'sStatus' => 'danger',
	                'sMessage' => 'Problemas al obtener número de formulario',
	                'sMessageSQL' => (($this->iStatusDebug) ? $sqlca->get_error() : ""),
	                'SQL' => (($this->iStatusDebug) ? $sql : ""),
	            );
	    	}

			$row = $sqlca->fetchRow();
			$sNumeroFormulario = $row["formulario"];

        	// Verificar que la fecha de emision no sea mayor a la actual
        	if ( $arrData->fecha_emision > $this->getAllDateFormat('fecha_ymd') . ' 23:59:59' ){
        		$this->managerTransaction("ROLLBACK");

	            return array(
	                'sStatus' => 'danger',
	                'sMessage' => 'La fecha de emision no puede ser mayor a la fecha actual',
	                'fecha_emision' => (($this->iStatusDebug) ? $arrData->fecha_emision : ""),
	                'fecha_actual' => (($this->iStatusDebug) ? $this->getAllDateFormat('fecha_ymd') : ""),
	            );
        	}

        	// Verificar que exista almacén destino
        	$arrDataSQL = array(
        		'sql' => "SELECT ch_almacen FROM inv_ta_almacenes WHERE ch_almacen ='" . pg_escape_string($arrData->id_almacen_destino) . "' LIMIT 1;",
        		'sMessageDanger' => 'Problemas al verificar almacén destino',
        		'sMessageWarning' => 'No existe almacén destino',
        	);
        	$arrResponse = $this->checkRegister($arrDataSQL);
        	if ( $arrResponse['sStatus'] != "success" ) {
	            return $arrResponse;
        	}

        	// Verificar tipo de naturaleza
        	if ( $arrData->tipo_naturaleza != "1" && $arrData->tipo_naturaleza != "3" ){
        		$this->managerTransaction("ROLLBACK");

	            return array(
	                'sStatus' => 'danger',
	                'sMessage' => 'Tipo de naturaleza no definida',
	            );
        	}

        	// Verificar tipo de comprobante
        	$arrDataSQL = array(
        		'sql' => "SELECT tab_elemento FROM int_tabla_general WHERE tab_tabla ='08' AND tab_elemento!='000000' AND SUBSTR(tab_elemento,5,2)='" . pg_escape_string($arrData->tipo_comprobante) . "' LIMIT 1;",
        		'sMessageDanger' => 'Problemas al verificar tipo de comprobante',
        		'sMessageWarning' => 'No existe tipo de comprobante',
        	);
        	$arrResponse = $this->checkRegister($arrDataSQL);
        	if ( $arrResponse['sStatus'] != "success" ) {
	            return $arrResponse;
        	}

        	// Verificar proveedor
        	$arrDataSQL = array(
        		'sql' => "SELECT pro_codigo FROM int_proveedores WHERE pro_codigo ='" . pg_escape_string($arrData->id_entidad) . "' LIMIT 1;",
        		'sMessageDanger' => 'Problemas al verificar proveedor',
        		'sMessageWarning' => 'No existe proveedor',
        	);
        	$arrResponse = $this->checkRegister($arrDataSQL);
        	if ( $arrResponse['sStatus'] != "success" ) {
	            return $arrResponse;
        	}

	        // Verificar ITEM
        	$arrDataSQL = array(
        		'sql' => "SELECT art_codigo FROM int_articulos WHERE art_codigo='" . pg_escape_string($arrData->id_item) . "' AND art_plutipo='1' AND art_estado='0' LIMIT 1;",
        		'sMessageDanger' => 'Problemas al verificar item',
        		'sMessageWarning' => 'Movimiento de inventario, no existe item ' . $arrData->id_item . ' o es un tipo (2 = PLU SALIENTE) o esta desactivado',
        	);
        	$arrResponse = $this->checkRegister($arrDataSQL);
        	if ( $arrResponse['sStatus'] != "success" ) {
	            return $arrResponse;
        	}

    		$sql = "
INSERT INTO inv_movialma(
mov_numero,
tran_codigo,
art_codigo,
mov_fecha,
mov_almacen,
mov_almaorigen,
mov_almadestino,
mov_naturaleza,
mov_tipdocuref,
mov_docurefe,
mov_tipoentidad,
mov_entidad,
mov_cantidad,
mov_costounitario,
mov_costopromedio,
mov_costototal,
mov_usuario
) VALUES (
'" . pg_escape_string($sNumeroFormulario) . "',
'" . pg_escape_string($arrData->id_tipo_movimiento) . "',
'" . pg_escape_string($arrData->id_item) . "',
'" . pg_escape_string($arrData->fecha_emision) . "',
'" . pg_escape_string($arrData->id_almacen_origen) . "',
'" . pg_escape_string($arrData->id_almacen_origen) . "',
'" . pg_escape_string($arrData->id_almacen_destino) . "',
'" . pg_escape_string($arrData->tipo_naturaleza) . "',
'" . pg_escape_string($arrData->tipo_comprobante) . "',
'" . pg_escape_string($arrData->serie) . pg_escape_string($arrData->numero) . "',
'" . pg_escape_string($arrData->tipo_identidad) . "',
'" . pg_escape_string($arrData->id_entidad) . "',
" . pg_escape_string($arrData->cantidad) . ",
" . pg_escape_string($arrData->costo_unitario) . ",
" . pg_escape_string($arrData->costo_promedio) . ",
" . pg_escape_string($arrData->total) . ",
'" . pg_escape_string($arrData->usuario) . "'
);
       		";

        	$iStatusSQL = $sqlca->query($sql);
        	if ( (int)$iStatusSQL < 0 ) {
        		$this->managerTransaction("ROLLBACK");

	            return array(
	                'sStatus' => 'danger',
	                'sMessage' => 'Problemas al generar movimiento de inventario',
	                'sMessageSQL' => (($this->iStatusDebug) ? $sqlca->get_error() : ""),
		            'SQL' => (($this->iStatusDebug) ? $sql : ""),
	            );
        	}

        	// Actualizando costo unitario por item, moneda y proveedor
        	$arrDataCostoUnitarioProveedor = array(
        		'id_entidad' => $arrData->id_entidad,
        		'id_item' => $arrData->id_item,
        		'id_moneda' => '01',//01 = Soles
        		'costo_unitario' => $arrData->costo_unitario,
        		'usuario' => $arrData->usuario,
        	);
		    $arrResponse = $this->crudCostoUnitarioProveedor($arrDataCostoUnitarioProveedor);
        	if ( $arrResponse['sStatus'] != "success" ) {
	            return $arrResponse;
        	}

            if ( isset($arrData->conversion_glp) ){
    			if ( $arrData->id_item == '11620307' ) {
			        if ( !empty($arrData->conversion_glp) ){
			        	$arrDataConversionGLP = array(
			        		'sNumeroFormulario' => $sNumeroFormulario,
			        		'sIdTipoMovimiento' => $arrData->id_tipo_movimiento,
			        		'dFechaEmision' 	=> $arrData->fecha_emision,
			        		'sIdItem' 			=> $arrData->id_item,
			        		'arrConversionGLP' 	=> $arrData->conversion_glp,
			        	);
			        	$arrResponse = $this->crudConversionGLP($arrDataConversionGLP);
			            if ( $arrResponse['sStatus'] != "success" ){
	        				$this->managerTransaction("ROLLBACK");

				            return $arrResponse;
			            }
			        } else {
	        			$this->managerTransaction("ROLLBACK");

			            return array(
			                'sStatus' => 'danger',
			                'sMessage' => 'Conversion de GLP sin parámetros',
			            );
			        }
	            }
		    }

        	$this->managerTransaction("COMMIT");

            return array(
                'sStatus' => 'success',
                'sMessage' => 'Movimiento de inventario registrado',
            );
        } catch (Exception $e) {
        	$this->managerTransaction("ROLLBACK");

            return array(
                'sStatus' => 'danger',
                'sMessage' => 'Problemas al generar movimiento de inventario',
                'sMessagePHP' => $e->getMessage(),
            );
        }
    }

    function crudCostoUnitarioProveedor($arrData){
    	global $sqlca;

    	try {
    		$arrData = (object)$arrData;
        	$arrDataSQL = array(
        		'sql' => "SELECT pro_codigo FROM com_rec_pre_proveedor WHERE pro_codigo='" . pg_escape_string($arrData->id_entidad) . "' AND art_codigo='" . pg_escape_string($arrData->id_item) . "' LIMIT 1;",
        		'sMessageDanger' => 'Problemas al verificar costo unitario de proveedor',
        		'sMessageWarning' => 'No existe costo unitario de proveedor',
        	);
	    	$iStatusSQL = $sqlca->query($arrDataSQL['sql']);
	    	if ( (int)$iStatusSQL < 0 ) {
	    		$this->managerTransaction("ROLLBACK");

	            return array(
	                'sStatus' => 'danger',
	                'sMessage' => $arrDataSQL['sMessageDanger'],
	                'sMessageSQL' => (($this->iStatusDebug) ? $sqlca->get_error() : ""),
	                'SQL' => (($this->iStatusDebug) ? $arrDataSQL['sql'] : ""),
	            );
	    	}

	    	if ( (int)$iStatusSQL > 0 ) {
 			$sql="
UPDATE
 com_rec_pre_proveedor
SET
 rec_precio = " . pg_escape_string($arrData->costo_unitario) . ",
 rec_fecha_ultima_compra = now(),
 rec_usuario = '" . pg_escape_string($arrData->usuario) . "'
WHERE
 pro_codigo = '" . $arrData->id_entidad . "'
 AND art_codigo = '" . $arrData->id_item . "';
";
 			} else if ( (int)$iStatusSQL == 0 ) {
 			$sql="
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
 '" . $arrData->id_entidad ."',
 '" . $arrData->id_item ."',
 '" . $arrData->id_moneda ."',
 " . $arrData->costo_unitario .",
 0.00,
 now(),
 now(),
 '" . $arrData->usuario . "',
 ''
);
 			";
 			}

        	$iStatusSQL = $sqlca->query($sql);
        	if ( (int)$iStatusSQL < 0 ) {
    			$this->managerTransaction("ROLLBACK");

	            return array(
	                'sStatus' => 'danger',
	                'sMessage' => 'Problemas al generar costo unitario de proveedor',
	                'sMessageSQL' => (($this->iStatusDebug) ? $sqlca->get_error() : ""),
	                'SQL' => (($this->iStatusDebug) ? $sql : ""),
	            );
        	}

            return array(
                'sStatus' => 'success',
                'sMessage' => 'costo unitario de proveedor registrado',
            );    		
    	} catch (Exception $e) {
        	$this->managerTransaction("ROLLBACK");

            return array(
                'sStatus' => 'danger',
                'sMessage' => 'Problemas al generar costo unitario de proveedor',
                'sMessagePHP' => $e->getMessage(),
            );
    	}
    }

    function crudConversionGLP($arrData){
    	global $sqlca;

    	try {
			foreach ($arrData['arrConversionGLP'] as $key => $row) {
				if( $row->operacion != "generar_conversion_glp" ){
	        		$this->managerTransaction("ROLLBACK");

		            return array(
		                'sStatus' => 'danger',
		                'sMessage' => 'Operacion no definida para Conversion de GLP',
		            );
				}

				if (
					(
						(int)$row->kilos == 0 &&
						(int)$row->gravedad_especifica == 0 &&
						(int)$row->galones == 0
					)
					||
					(
						empty($row->kilos) &&
						empty($row->gravedad_especifica) &&
						empty($row->galones)
					)
				) {
	        		$this->managerTransaction("ROLLBACK");

		            return array(
		                'sStatus' => 'danger',
		                'sMessage' => 'Datos para la conversion de GLP en 0 o sin valor',
		            );
				}

				if (
					(
						(float)$row->kilos > 0.0000 &&
						(float)$row->gravedad_especifica > 0.0000
					) && (int)$row->galones == 0
				) {
					$fKilos = $row->kilos;
					$fGravedadEspecifica = $row->gravedad_especifica;
	        		$fGalones = 0.0000;
				} else if (
					(float)$row->galones > 0.0000
					&&
					(
						(int)$row->kilos == 0 &&
						(int)$row->gravedad_especifica == 0
					)
				) {
					$fKilos = 0.0000;
					$fGravedadEspecifica = 0.0000;
	        		$fGalones = $row->galones;
				} else {
	        		$this->managerTransaction("ROLLBACK");

		            return array(
		                'sStatus' => 'danger',
		                'sMessage' => 'Calculo de operacion para la conversion de GLP no válida',
		            );
				}

 				$sql="
INSERT INTO inv_calculo_glp (
mov_numero,
tran_codigo,
art_codigo,
mov_fecha,
kilos,
ge,
galones
) VALUES (
'" . pg_escape_string($arrData['sNumeroFormulario']) . "',
'" . pg_escape_string($arrData['sIdTipoMovimiento']) . "',
'" . pg_escape_string($arrData['sIdItem']) . "',
'" . pg_escape_string($arrData['dFechaEmision']) . "',
" . pg_escape_string($fKilos) . ",
" . pg_escape_string($fGravedadEspecifica) . ",
" . pg_escape_string($fGalones) . "
);
	 			";

	        	$iStatusSQL = $sqlca->query($sql);
	        	if ( (int)$iStatusSQL < 0 ) {
	    			$this->managerTransaction("ROLLBACK");

		            return array(
		                'sStatus' => 'danger',
		                'sMessage' => 'Problemas al generar Conversion de GLP',
		                'sMessageSQL' => (($this->iStatusDebug) ? $sqlca->get_error() : ""),
		                'SQL' => (($this->iStatusDebug) ? $sql : ""),
		            );
	        	}

	            return array(
	                'sStatus' => 'success',
	                'sMessage' => 'Conversion de GLP registrado',
	            );
	        } // /. Foreach Conversión SOLO GLP
    	} catch (Exception $e) {
        	$this->managerTransaction("ROLLBACK");

            return array(
                'sStatus' => 'danger',
                'sMessage' => 'Problemas al generar Conversion de GLP',
                'sMessagePHP' => $e->getMessage(),
            );
    	}
    }

    private function checkItem($arrDataSQL){
    	global $sqlca;

    	$iStatusSQL = $sqlca->query($arrDataSQL['sql']);
    	if ( (int)$iStatusSQL < 0 ) {
    		$this->managerTransaction("ROLLBACK");

            return array(
                'sStatus' => 'danger',
                'sMessage' => $arrDataSQL['sMessageDanger'],
                'sMessageSQL' => (($this->iStatusDebug) ? $sqlca->get_error() : ""),
	            'SQL' => (($this->iStatusDebug) ? $arrDataSQL['sql'] : ""),
            );
    	}

    	if ( (int)$iStatusSQL > 0 ) {
    		$this->managerTransaction("ROLLBACK");

            return array(
                'sStatus' => 'warning',
                'sMessage' => $arrDataSQL['sMessageWarning'],
                'SQL' => (($this->iStatusDebug) ? $arrDataSQL['sql'] : ""),
            );
    	}

        return array('sStatus' => 'success');
    }

    private function checkRegister($arrDataSQL){
    	global $sqlca;

    	$iStatusSQL = $sqlca->query($arrDataSQL['sql']);
    	if ( (int)$iStatusSQL < 0 ) {
    		$this->managerTransaction("ROLLBACK");

            return array(
                'sStatus' => 'danger',
                'sMessage' => $arrDataSQL['sMessageDanger'],
                'sMessageSQL' => (($this->iStatusDebug) ? $sqlca->get_error() : ""),
                'SQL' => (($this->iStatusDebug) ? $arrDataSQL['sql'] : ""),
                'arrData' => $sqlca->fetchAll(),
            );
    	}

    	if ( (int)$iStatusSQL == 0 ) {
    		$this->managerTransaction("ROLLBACK");

            return array(
                'sStatus' => 'warning',
                'sMessage' => $arrDataSQL['sMessageWarning'],
                'SQL' => (($this->iStatusDebug) ? $arrDataSQL['sql'] : ""),
            );
    	}

        return array('sStatus' => 'success', 'sMessage' => 'Item encontrado', 'arrData' => $sqlca->fetchAll());
    }

    function getItem($arrData){
    	global $sqlca;
        try {
        	if ( isset($arrData->debug_o7) ) {
        		$this->debugO7Interface($arrData);
        	}

        	$arrResponseToken = $this->validateToken();
        	if ( $arrResponseToken['sStatus'] != "success" ) {
	            return $arrResponseToken;
        	}

        	$arrDataSQL = array(
        		'sql' => "SELECT * FROM int_articulos WHERE art_codigo='" . pg_escape_string($arrData->id_item) . "' LIMIT 1",
        		'sMessageDanger' => 'Problemas al obtener Item',
        		'sMessageWarning' => 'No existe Item',
        	);
        	$arrResponse = $this->checkRegister($arrDataSQL);
        	if ( $arrResponse['sStatus'] != "success" ) {
	            return $arrResponse;
        	}
        	return $arrResponse;
        } catch (Exception $e) {
            return array(
                'sStatus' => 'danger',
                'sMessage' => 'Problemas al obtener Item',
                'sMessagePHP' => $e->getMessage(),
            );
        }
    }

    function getListaPrecio($arrData){
    	global $sqlca;
        try {
        	if ( isset($arrData->debug_o7) ) {
        		$this->debugO7Interface($arrData);
        	}

        	$arrResponseToken = $this->validateToken();
        	if ( $arrResponseToken['sStatus'] != "success" ) {
	            return $arrResponseToken;
        	}

        	$arrDataSQL = array(
        		'sql' => "SELECT * FROM fac_lista_precios WHERE art_codigo='" . pg_escape_string($arrData->id_item) . "'",
        		'sMessageDanger' => 'Problemas al obtener Item',
        		'sMessageWarning' => 'No existe Item',
        	);
        	$arrResponse = $this->checkRegister($arrDataSQL);
        	if ( $arrResponse['sStatus'] != "success" ) {
	            return $arrResponse;
        	}
        	return $arrResponse;
        } catch (Exception $e) {
            return array(
                'sStatus' => 'danger',
                'sMessage' => 'Problemas al obtener Item',
                'sMessagePHP' => $e->getMessage(),
            );
        }
    }

    function getMovimientoInventario($arrData){
    	global $sqlca;
        try {
        	if ( isset($arrData->debug_o7) ) {
        		$this->debugO7Interface($arrData);
        	}

        	$arrResponseToken = $this->validateToken();
        	if ( $arrResponseToken['sStatus'] != "success" ) {
	            return $arrResponseToken;
        	}

			$arrFechaEmision = explode('/', $arrData->id);
	       	$dEmision = $arrFechaEmision[0] . '-' . $arrFechaEmision[1] . '-' . $arrFechaEmision[2];

        	$arrDataSQL = array(
        		'sql' => "SELECT * FROM inv_movialma WHERE mov_fecha::DATE='" . $dEmision . "' ORDER BY mov_fecha DESC;",
        		'sMessageDanger' => 'Problemas al obtener movimiento de inventario',
        		'sMessageWarning' => 'No existe Item',
        	);
        	$arrResponse = $this->checkRegister($arrDataSQL);
        	if ( $arrResponse['sStatus'] != "success" ) {
	            return $arrResponse;
        	}
        	return $arrResponse;
        } catch (Exception $e) {
            return array(
                'sStatus' => 'danger',
                'sMessage' => 'Problemas al obtener movimiento de inventario',
                'sMessagePHP' => $e->getMessage(),
            );
        }
    }
}

//get data by API
$data = json_decode(file_get_contents("php://input"));

if ( !isset($data->operacion) ) {
    echo json_encode(array(
        'sStatus' => 'danger',
        'sMessage' => 'Se debe de enviar el parámetro operacion',
        'arrData' => $data,
    ));
    exit();
}

if (
	isset($data->operacion) &&
	(
		$data->operacion != 'generar_item'
		&& $data->operacion != 'consultar_item'
		&& $data->operacion != 'modificar_item'
		&& $data->operacion != 'modificar_lista_precio'
		&& $data->operacion != 'consultar_lista_precio'
		&& $data->operacion != 'generar_movimiento_inventario'
		&& $data->operacion != 'consultar_movimiento_inventario'
	)
){
    echo json_encode(array(
        'sStatus' => 'danger',
        'sMessage' => 'Operacion ' . $data->operacion . ' no definida',
    ));
    exit();
}

//Invocamos clase
$obj07Interface = new O7Interface();

if ( $data->operacion == 'generar_item' ){
	echo json_encode($obj07Interface->crudItem($data));
    exit();
}

if ( $data->operacion == 'modificar_item' ){
	echo json_encode($obj07Interface->crudItem($data));
    exit();
}

if ( $data->operacion == 'consultar_item' ){
	echo json_encode($obj07Interface->getItem($data));
    exit();
}

if ( $data->operacion == 'modificar_lista_precio' ){
	echo json_encode($obj07Interface->crudListaPrecio('', $data->lista_precios, $data->operacion));
    exit();
}

if ( $data->operacion == 'consultar_lista_precio' ){
	echo json_encode($obj07Interface->getListaPrecio($data));
    exit();
}

if ( $data->operacion == 'generar_movimiento_inventario' ){
	echo json_encode($obj07Interface->crudMovimientoInventario($data));
    exit();
}

if ( $data->operacion == 'consultar_movimiento_inventario' ){
	echo json_encode($obj07Interface->getMovimientoInventario($data));
    exit();
}
