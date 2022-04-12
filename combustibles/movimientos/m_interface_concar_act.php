<?php

function muestraError($mssql,$msg) {
	echo "<script>alert('" . $msg ."');</script>";
}

function verificaRUCS($rucs, $tabruc) {
	 
	$k = 0;
	$flag = 0;

	for ($i = 0; $i < count($rucs); $i++) {			

		if(trim($rucs[$i]['raz']) == ''){
			$rucs[$i]['raz'] = "X";
		}

		if(substr($rucs[$i]['ruc'],0,2)=="10" or substr($rucs[$i]['ruc'],0,2)=="15"){
			$atiptra = "N";
		}else{
			$atiptra = "J";		
		}

		$razo = $rucs[$i]['raz'];	
		$razonsocial = str_replace("'", "''",$razo);
						
		$inserta[$k] = "INSERT INTO $tabruc
						(avanexo,acodane,adesane,arefane,aruc,acodmon,aestado,atiptra,adocide,anumide) 
				VALUES
						('C', '".trim($rucs[$i]['ruc'])."', '".substr($razonsocial,0,39)."', 'X', '".trim($rucs[$i]['ruc'])."', 'MN','V','".$atiptra."','6', '".trim($rucs[$i]['ruc'])."');
				";
		$k++;		
	}
	return $inserta;
}

function ejecutarInsert($inserts, $tabcab, $tabdet, $tabcan) {
	// Parametros de conexión a SQL SERVER
	$objModel = new InterfaceConcarActModel();
	$Parametros = $objModel->obtenerParametros();

	$MSSQLDBHost = $Parametros[0];
	$MSSQLDBUser = $Parametros[1];
	$MSSQLDBPass = $Parametros[2];
	$MSSQLDBName = $Parametros[3];

	$temp_file = tempnam(sys_get_temp_dir(), 'phpmssqlbridge');
	//$temp_file = "/tmp/imprimir/phpmssqlbridge.log";
	$descriptorspec = array(
		0 => array("pipe", "r"),
		1 => array("file", $temp_file, "a")
	);

	$procpipes = array();

	$process = proc_open("java -jar /usr/local/lib/phpmssqlbridge.jar {$MSSQLDBHost} {$MSSQLDBUser} {$MSSQLDBPass} {$MSSQLDBName}", $descriptorspec, $procpipes);

	$arrInsertsHeader = @$inserts['tipo']['cabecera'];
	$arrInsertsDetail = @$inserts['tipo']['detalle'];
	$arrInsertsClient = @$inserts['clientes'];

	if (!is_resource($process)) {
		return 0;
	}

	if ( count($arrInsertsHeader) > 0 && count($arrInsertsDetail) > 0 ) {
		$k1 = "DELETE FROM ".$tabcab.";";//Cabecera
		$k2 = "DELETE FROM ".$tabdet.";";//Detalle
	} else {
		return 0;
	}

	if ( !empty($tabcan) ){
		$k3 = "DELETE FROM ".$tabcan.";";//Cliente
	}

	// Limpia tablas SQL
	fwrite($procpipes[0],$k1."\r\n");
	echo $k1 . "\r\n";
	fwrite($procpipes[0],$k2."\r\n");
	echo $k2 . "\r\n";
	fwrite($procpipes[0],$k3."\r\n");
	echo $k3 . "\r\n";

	//LOG - Insert's
	echo "<pre>";
	print_r($arrInsertsHeader);
	print_r($arrInsertsDetail);
	echo "</pre>";

	for ($i=0; $i<count($arrInsertsHeader); $i++) {
		fwrite($procpipes[0],$arrInsertsHeader[$i] . "\r\n");
	}

	for ($d=0; $d<count($arrInsertsDetail); $d++) {
		fwrite($procpipes[0],$arrInsertsDetail[$d] . "\r\n");
	}

	for ($c=0; $c<count($arrInsertsClient); $c++) {
		fwrite($procpipes[0],$arrInsertsClient[$c] . "\r\n");
	}

/*
	$ultima_linea = system("java -jar /usr/local/lib/phpmssqlbridge.jar {$MSSQLDBHost} {$MSSQLDBUser} {$MSSQLDBPass} {$MSSQLDBName}", $retval);

	// Imprimir informacion adicional
	echo '
	</pre>
	<hr />Ultima linea de la salida: ' . $ultima_linea . '
	<hr />Valor de retorno: ' . $retval;
*/

    fclose($procpipes[0]);
	$prv = proc_close($process);
	return 1;	
}

class InterfaceConcarActModel extends Model {

	function getVersionConcar() {
		global $sqlca;

		$_SESSION['es_requerimiento_concar_nuevomundo'] = false;
		$_SESSION['es_requerimiento_concar_etissa'] = false;

		$sqlca->query("SELECT par_valor FROM int_parametros WHERE par_nombre = 'version_concar';");
		$a = $sqlca->fetchRow(); 

		/* Versiones Concar
		 * Concar 1: Original
		 * Concar 2: Nuevomundo
		 * Concar 3: Etissa
		 */
		if ($a[0] == "Nuevo_Mundo") { 
			$_SESSION['es_requerimiento_concar_nuevomundo'] = true;
		} elseif ($a[0] == "Etissa") {
			$_SESSION['es_requerimiento_concar_etissa'] = true;
		}
	}

	function getMigracionConcar() {
		global $sqlca;

		//OBTENEMOS PARAMETROS PARA MIGRACION
		$iStatus = $sqlca->query("SELECT par_valor FROM int_parametros WHERE par_nombre = 'migracion_concar' LIMIT 1;");				

		//SE EJECUTO LA QUERY
		if ((int)$iStatus > 0) {					
			$row = $sqlca->fetchRow();
			return $row['par_valor'];
		}

		return 0;
	} 

	function delete_concar_confignew() {
		global $sqlca;

		//INICIAMOS TRANSACCION
		$sqlca->query("BEGIN;");

		//ELIMINAMOS TABLA Y SECUENCIA
		$iStatus = $sqlca->query("
			DROP TABLE concar_confignew;
			DROP SEQUENCE seq_concar_confignew_id;
		");

		if ((int)$iStatus < 0) {
			$sqlca->query("ROLLBACK;");							
		}else{
			$sqlca->query("COMMIT;");			
		}
	} 

	function create_concar_confignew() {
		global $sqlca;

		//INICIAMOS TRANSACCION
		$sqlca->query("BEGIN;");		               

		//VERIFICAMOS SI EXISTE TABLA	
        $iStatus = $sqlca->query("
            SELECT EXISTS(
                SELECT column_name
                FROM   information_schema.columns
                WHERE  table_schema='public'
                AND    table_name='concar_confignew'
                AND    column_name='concar_confignew_id'            
            );
        ");

		//SE EJECUTO LA FUNCTION EXISTS
		if ((int)$iStatus > 0) {
            $row = $sqlca->fetchRow();
            $exists = $row['exists'];

            //NO EXISTE
            if($exists == "f"){
                //CREAMOS SECUENCIA Y TABLA
                $iStatus = $sqlca->query("
					-- SEQUENCE: public.seq_concar_confignew_id
					-- DROP SEQUENCE public.seq_concar_confignew_id;
					CREATE SEQUENCE public.seq_concar_confignew_id
						INCREMENT 1
						START 1
						MINVALUE 1
						MAXVALUE 9223372036854775807
						CACHE 1;
					ALTER SEQUENCE public.seq_concar_confignew_id
						OWNER TO postgres;	
						
					-- Table: public.concar_confignew
					-- DROP TABLE public.concar_confignew;				
					CREATE TABLE public.concar_confignew
					(
						concar_confignew_id numeric(20,0) NOT NULL DEFAULT nextval('seq_concar_confignew_id'::regclass),
						ch_sucursal character varying(3) COLLATE pg_catalog.".'default'." NOT NULL,
						module numeric(2,0) NOT NULL,
						category numeric(2,0) NOT NULL,
						subcategory numeric(2,0),
						account character varying(12) COLLATE pg_catalog.".'default'." NOT NULL,
						CONSTRAINT concar_confignew_pkey PRIMARY KEY (concar_confignew_id)
					)				
					TABLESPACE pg_default;				
					ALTER TABLE public.concar_confignew
						OWNER to postgres;
				");
								
				//SE EJECUTO CORRECTAMENTE
				if((int)$iStatus == 0){
					$sqlca->query("COMMIT;");		
					return array('iStatus' => $iStatus, 'sStatus' => 'success', 'sMessage' => 'Creamos secuencia y tabla correctamente', 'arrData' => NULL);
                }else{
					$sqlca->query("ROLLBACK;");		
					return array('iStatus' => $iStatus, 'sStatus' => 'danger', 'sMessage' => 'No se pudo crear secuencia y tabla correctamente', 'arrData' => NULL);
				}
            }
        }
	}

	/*
	concar_confignew Values
		module Values
		0 Global
			category
			0 Codigos Globales Empresa
						0 Codigo de Empresa
			1 Codigos Globales 
						0 Subdiario dia
						1 Codigo de Cliente
						2 Codigo de Caja
			2 Codigos Globales Centro de Costo
						0 Centro de Costo Combustible
						1 Centro de Costo GLP
						2 Centro de Costo Market
						3 Centro de Costo Documentos Manuales
			3 Codigos Globales Codigo de Anexo
						0 Codigo de Anexo
						1 Codigo de Anexo Lubricante
						2 Codigo de Anexo GLP
						
	*/
	function insert_module0_global() {
		global $sqlca;

		//VERIFICAMOS QUE NO HAYA DATA INSERTADA
		$iStatus = $sqlca->query("SELECT count(*) as resultado_cantidad FROM concar_confignew WHERE module = '0'");

		//SE EJECUTO LA QUERY
		if ((int)$iStatus > 0) {
			$row = $sqlca->fetchRow();
			$resultado_cantidad = $row['resultado_cantidad'];

			//SI LA CANTIDAD ES 0, INSERTAMOS
			if($resultado_cantidad == 0){

				//OBTENEMOS SUCURSALES
				$iStatus = $sqlca->query("SELECT ch_sucursal FROM int_ta_sucursales LIMIT 1");				

				//SE EJECUTO LA QUERY
				if ((int)$iStatus > 0) {					
					$row = $sqlca->fetchRow();
					$ch_sucursal = $row['ch_sucursal'];										

					//OBTENEMOS INFORMACION DE CONCAR_CONFIG PARA EL MODULO GLOBAL, ES IMPORTANTE ASEGURARNOS DE QUE ESTOS CAMPOS TENGAN DATA					
					$iStatus = $sqlca->query("SELECT 	
													cod_empresa
													,subdiario_dia													
													,cod_cliente													
													,cod_caja										
													,id_cencos_comb
													,id_centro_costo_glp
													,id_centrocosto
													,id_centro_cos_dMa
													,codane
													,codane_lubri
													,codane_glp2										
												FROM concar_config;");	
						
					//SE EJECUTO LA QUERY
					if ((int)$iStatus > 0) {
						$row = $sqlca->fetchRow();
						$cod_empresa         = $row['cod_empresa'];
						$subdiario_dia       = $row['subdiario_dia'];	
						$cod_cliente         = $row['cod_cliente'];											
						$cod_caja            = $row['cod_caja'];
						$id_cencos_comb      = $row['id_cencos_comb'];
						$id_centro_costo_glp = $row['id_centro_costo_glp'];
						$id_centrocosto      = $row['id_centrocosto'];
						$id_centro_cos_dMa   = $row['id_centro_cos_dma']; //En SQL todos los campos estan en minusculas
						$codane              = $row['codane'];
						$codane_lubri        = $row['codane_lubri'];
						$codane_glp2         = $row['codane_glp2'];

						//INICIAMOS TRANSACCION
						$sqlca->query("BEGIN;");

						//INSERTAMOS DATA
						$iStatus = $sqlca->query("
							INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 0, 0, 0, '$cod_empresa');
							INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 0, 1, 0, '$subdiario_dia');
							INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 0, 1, 1, '$cod_cliente');
							INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 0, 1, 2, '$cod_caja');
							INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 0, 2, 0, '$id_cencos_comb');
							INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 0, 2, 1, '$id_centro_costo_glp');
							INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 0, 2, 2, '$id_centrocosto');
							INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 0, 2, 3, '$id_centro_cos_dMa');							
							INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 0, 3, 0, '$codane');
							INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 0, 3, 1, '$codane_lubri');
							INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 0, 3, 2, '$codane_glp2');
						");

						if ((int)$iStatus < 0) {
							$sqlca->query("ROLLBACK;");							
						}else{
							$sqlca->query("COMMIT;");			
						}
					}
				}
			}
		}		
	}

	/*
	concar_confignew Values
		module Values
		1 Ventas Combustible
			category
			0 Cuentas Configuracion
						0 Subdiario de Venta Combustible
			1 Cuentas Combustible
						0 Cuenta Combustible Cliente
						1 Cuenta Combustible Impuesto
						2 Cuenta Combustible Ventas
			2 Cuentas GLP
						0 Cuenta GLP Cliente
						1 Cuenta GLP Ventas
	*/
	function insert_module1_ventas_combustible() {
		global $sqlca;
			
		//VERIFICAMOS QUE NO HAYA DATA INSERTADA
		$iStatus = $sqlca->query("SELECT count(*) as resultado_cantidad FROM concar_confignew WHERE module = '1'");

		//SE EJECUTO LA QUERY
		if ((int)$iStatus > 0) {
			$row = $sqlca->fetchRow();
			$resultado_cantidad = $row['resultado_cantidad'];

			//SI LA CANTIDAD ES 0, INSERTAMOS
			if($resultado_cantidad == 0){

				//OBTENEMOS SUCURSALES
				$iStatus = $sqlca->query("SELECT ch_sucursal FROM int_ta_sucursales LIMIT 1");				

				//SE EJECUTO LA QUERY
				if ((int)$iStatus > 0) {					
					$row = $sqlca->fetchRow();
					$ch_sucursal = $row['ch_sucursal'];										

					//OBTENEMOS INFORMACION DE CONCAR_CONFIG PARA EL MODULO VENTAS COMBUSTIBLE
					$iStatus = $sqlca->query("SELECT 
													venta_subdiario
													,venta_cuenta_cliente 
													,venta_cuenta_impuesto
													,venta_cuenta_ventas
													,venta_cuenta_cliente_glp
													,venta_cuenta_ventas_glp
												FROM concar_config;");	
						
					//SE EJECUTO LA QUERY
					if ((int)$iStatus > 0) {
						$row = $sqlca->fetchRow();
						$venta_subdiario          = $row['venta_subdiario'];
						$venta_cuenta_cliente     = $row['venta_cuenta_cliente'];	
						$venta_cuenta_impuesto    = $row['venta_cuenta_impuesto'];											
						$venta_cuenta_ventas      = $row['venta_cuenta_ventas'];
						$venta_cuenta_cliente_glp = $row['venta_cuenta_cliente_glp'];
						$venta_cuenta_ventas_glp  = $row['venta_cuenta_ventas_glp'];

						//INICIAMOS TRANSACCION
						$sqlca->query("BEGIN;");

						//INSERTAMOS DATA
						$iStatus = $sqlca->query("
							INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 1, 0, 0, '$venta_subdiario');
							INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 1, 1, 0, '$venta_cuenta_cliente');
							INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 1, 1, 1, '$venta_cuenta_impuesto');
							INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 1, 1, 2, '$venta_cuenta_ventas');
							INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 1, 2, 0, '$venta_cuenta_cliente_glp');
							INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 1, 2, 1, '$venta_cuenta_ventas_glp');
						");

						if ((int)$iStatus < 0) {
							$sqlca->query("ROLLBACK;");							
						}else{
							$sqlca->query("COMMIT;");			
						}
					}
				}
			}
		}
	}

	/*
	concar_confignew Values
		module Values
		2 Ventas Market
			category
			0 Cuentas Configuracion
						0 Subdiario de Venta Market
			1 Cuentas Market
						0 Cuenta Market Cliente
						1 Cuenta Market Impuesto
						2 Cuenta Market Ventas
			2 Cuentas Lubricante
						0 Cuenta Lubricante Cliente
						1 Cuenta Lubricante Ventas
	*/
	function insert_module2_ventas_market() {
		global $sqlca;
			
		//VERIFICAMOS QUE NO HAYA DATA INSERTADA
		$iStatus = $sqlca->query("SELECT count(*) as resultado_cantidad FROM concar_confignew WHERE module = '2'");

		//SE EJECUTO LA QUERY
		if ((int)$iStatus > 0) {
			$row = $sqlca->fetchRow();
			$resultado_cantidad = $row['resultado_cantidad'];

			//SI LA CANTIDAD ES 0, INSERTAMOS
			if($resultado_cantidad == 0){

				//OBTENEMOS SUCURSALES
				$iStatus = $sqlca->query("SELECT ch_sucursal FROM int_ta_sucursales LIMIT 1");				

				//SE EJECUTO LA QUERY
				if ((int)$iStatus > 0) {					
					$row = $sqlca->fetchRow();
					$ch_sucursal = $row['ch_sucursal'];										

					//OBTENEMOS INFORMACION DE CONCAR_CONFIG PARA EL MODULO VENTAS MARKET
					$iStatus = $sqlca->query("SELECT
													venta_subdiario_market
													,venta_cuenta_cliente_mkt
													,venta_cuenta_impuesto
													,venta_cuenta_ventas_mkt
													,venta_cuenta_cliente_lubri
													,venta_cuenta_ventas_lubri
												FROM
													concar_config;");	
						
					//SE EJECUTO LA QUERY
					if ((int)$iStatus > 0) {
						$row = $sqlca->fetchRow();
						$venta_subdiario_market     = $row['venta_subdiario_market'];
						$venta_cuenta_cliente_mkt   = $row['venta_cuenta_cliente_mkt'];	
						$venta_cuenta_impuesto      = $row['venta_cuenta_impuesto'];											
						$venta_cuenta_ventas_mkt    = $row['venta_cuenta_ventas_mkt'];
						$venta_cuenta_cliente_lubri = $row['venta_cuenta_cliente_lubri'];
						$venta_cuenta_ventas_lubri  = $row['venta_cuenta_ventas_lubri'];

						//INICIAMOS TRANSACCION
						$sqlca->query("BEGIN;");

						//INSERTAMOS DATA
						$iStatus = $sqlca->query("
							INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 2, 0, 0, '$venta_subdiario_market');
							INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 2, 1, 0, '$venta_cuenta_cliente_mkt');
							INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 2, 1, 1, '$venta_cuenta_impuesto');
							INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 2, 1, 2, '$venta_cuenta_ventas_mkt');
							INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 2, 2, 0, '$venta_cuenta_cliente_lubri');
							INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 2, 2, 1, '$venta_cuenta_ventas_lubri');
						");

						if ((int)$iStatus < 0) {
							$sqlca->query("ROLLBACK;");							
						}else{
							$sqlca->query("COMMIT;");			
						}
					}
				}
			}
		}
	}	

	/*
	concar_confignew Values
		module Values
		3 Cta. Cobrar Combustible
			category
			0 Cuentas Configuracion
						0 Subdiario de Cta Cobrar Combustible
			1 Cuentas Cobrar Combustible
						0 Cuenta Cobrar Combustible Cliente
						1 Cuenta Cobrar Combustible Caja
			2 Cuentas Cobrar GLP
						0 Cuenta Cobrar GLP Cliente
						1 Cuenta Cobrar GLP Caja
	*/
	function insert_module3_cta_cobrar_combustible() {
		global $sqlca;
			
		//VERIFICAMOS QUE NO HAYA DATA INSERTADA
		$iStatus = $sqlca->query("SELECT count(*) as resultado_cantidad FROM concar_confignew WHERE module = '3'");

		//SE EJECUTO LA QUERY
		if ((int)$iStatus > 0) {
			$row = $sqlca->fetchRow();
			$resultado_cantidad = $row['resultado_cantidad'];

			//SI LA CANTIDAD ES 0, INSERTAMOS
			if($resultado_cantidad == 0){

				//OBTENEMOS SUCURSALES
				$iStatus = $sqlca->query("SELECT ch_sucursal FROM int_ta_sucursales LIMIT 1");				

				//SE EJECUTO LA QUERY
				if ((int)$iStatus > 0) {					
					$row = $sqlca->fetchRow();
					$ch_sucursal = $row['ch_sucursal'];										

					//OBTENEMOS INFORMACION DE CONCAR_CONFIG PARA EL MODULO CTA. COBRAR COMBUSTIBLE
					$iStatus = $sqlca->query("SELECT
													ccobrar_subdiario
													,ccobrar_cuenta_cliente
													,ccobrar_cuenta_caja
													,ccobrar_cuenta_cliente_new
													,ccobrar_cuenta_caja_new
												FROM
													concar_config;");	
						
					//SE EJECUTO LA QUERY
					if ((int)$iStatus > 0) {
						$row = $sqlca->fetchRow();
						$ccobrar_subdiario          = $row['ccobrar_subdiario'];
						$ccobrar_cuenta_cliente     = $row['ccobrar_cuenta_cliente'];	
						$ccobrar_cuenta_caja        = $row['ccobrar_cuenta_caja'];											
						$ccobrar_cuenta_cliente_new = $row['ccobrar_cuenta_cliente_new'];
						$ccobrar_cuenta_caja_new    = $row['ccobrar_cuenta_caja_new'];

						//INICIAMOS TRANSACCION
						$sqlca->query("BEGIN;");

						//INSERTAMOS DATA
						$iStatus = $sqlca->query("
							INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 3, 0, 0, '$ccobrar_subdiario');
							INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 3, 1, 0, '$ccobrar_cuenta_cliente');
							INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 3, 1, 1, '$ccobrar_cuenta_caja');
							INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 3, 2, 0, '$ccobrar_cuenta_cliente_new');
							INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 3, 2, 1, '$ccobrar_cuenta_caja_new');
						");

						if ((int)$iStatus < 0) {
							$sqlca->query("ROLLBACK;");							
						}else{
							$sqlca->query("COMMIT;");			
						}
					}
				}
			}
		}
	}

	/*
	concar_confignew Values
		module Values
		4 Cta. Cobrar Market
			category
			0 Cuentas Configuracion
						0 Subdiario de Cta Cobrar Market
			1 Cuentas Cobrar Market
						0 Cuenta Cobrar Market Cliente
						1 Cuenta Cobrar Market Caja
	*/
	function insert_module4_cta_cobrar_market() {
		global $sqlca;
			
		//VERIFICAMOS QUE NO HAYA DATA INSERTADA
		$iStatus = $sqlca->query("SELECT count(*) as resultado_cantidad FROM concar_confignew WHERE module = '4'");

		//SE EJECUTO LA QUERY
		if ((int)$iStatus > 0) {
			$row = $sqlca->fetchRow();
			$resultado_cantidad = $row['resultado_cantidad'];

			//SI LA CANTIDAD ES 0, INSERTAMOS
			if($resultado_cantidad == 0){

				//OBTENEMOS SUCURSALES
				$iStatus = $sqlca->query("SELECT ch_sucursal FROM int_ta_sucursales LIMIT 1");				

				//SE EJECUTO LA QUERY
				if ((int)$iStatus > 0) {					
					$row = $sqlca->fetchRow();
					$ch_sucursal = $row['ch_sucursal'];										

					//OBTENEMOS INFORMACION DE CONCAR_CONFIG PARA EL MODULO CTA. COBRAR MARKET
					$iStatus = $sqlca->query("SELECT
													ccobrar_subdiario_mkt
													,ccobrar_cuenta_cliente_mkt
													,ccobrar_cuenta_caja_mkt
												FROM
													concar_config;");	
						
					//SE EJECUTO LA QUERY
					if ((int)$iStatus > 0) {
						$row = $sqlca->fetchRow();
						$ccobrar_subdiario_mkt      = $row['ccobrar_subdiario_mkt'];
						$ccobrar_cuenta_cliente_mkt = $row['ccobrar_cuenta_cliente_mkt'];	
						$ccobrar_cuenta_caja_mkt    = $row['ccobrar_cuenta_caja_mkt'];											

						//INICIAMOS TRANSACCION
						$sqlca->query("BEGIN;");

						//INSERTAMOS DATA
						$iStatus = $sqlca->query("
							INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 4, 0, 0, '$ccobrar_subdiario_mkt');
							INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 4, 1, 0, '$ccobrar_cuenta_cliente_mkt');
							INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 4, 1, 1, '$ccobrar_cuenta_caja_mkt');
						");

						if ((int)$iStatus < 0) {
							$sqlca->query("ROLLBACK;");							
						}else{
							$sqlca->query("COMMIT;");			
						}
					}
				}
			}
		}
	}

	/*
	concar_confignew Values
		module Values
		5 Compras
			category
			0 Cuentas Configuracion
						0 Subdiario de Compra Combustible
						1 Subdiario de Compra GLP
						2 Subdiario de Compra Market
			1 Cuentas Combustible
						0 Cuenta Compra Proveedor Combustible
						1 Cuenta Compra BI Combustible
			2 Cuentas GLP
						0 Cuenta Compra Proveedor GLP
						1 Cuenta Compra BI GLP
			3 Cuentas Market
						0 Cuenta Compra Proveedor Market
						1 Cuenta Compra Impuesto
						2 Cuenta Compra BI Market
	*/
	function insert_module5_compras() {
		global $sqlca;
			
		//VERIFICAMOS QUE NO HAYA DATA INSERTADA
		$iStatus = $sqlca->query("SELECT count(*) as resultado_cantidad FROM concar_confignew WHERE module = '5'");

		//SE EJECUTO LA QUERY
		if ((int)$iStatus > 0) {
			$row = $sqlca->fetchRow();
			$resultado_cantidad = $row['resultado_cantidad'];

			//SI LA CANTIDAD ES 0, INSERTAMOS
			if($resultado_cantidad == 0){

				//OBTENEMOS SUCURSALES
				$iStatus = $sqlca->query("SELECT ch_sucursal FROM int_ta_sucursales LIMIT 1");				

				//SE EJECUTO LA QUERY
				if ((int)$iStatus > 0) {					
					$row = $sqlca->fetchRow();
					$ch_sucursal = $row['ch_sucursal'];										

					//OBTENEMOS INFORMACION DE CONCAR_CONFIG PARA EL MODULO COMPRAS
					$iStatus = $sqlca->query("SELECT 
													compra_subdiario
													,compra_cuenta_proveedor
													,compra_cuenta_impuesto
													,compra_cuenta_mercaderia
												FROM concar_config;");	
						
					//SE EJECUTO LA QUERY
					if ((int)$iStatus > 0) {
						$row = $sqlca->fetchRow();
						$compra_subdiario         = $row['compra_subdiario'];
						$compra_cuenta_proveedor  = $row['compra_cuenta_proveedor'];	
						$compra_cuenta_impuesto   = $row['compra_cuenta_impuesto'];											
						$compra_cuenta_mercaderia = $row['compra_cuenta_mercaderia'];

						//INICIAMOS TRANSACCION
						$sqlca->query("BEGIN;");

						//INSERTAMOS DATA
						$iStatus = $sqlca->query("
							INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 5, 0, 0, '$compra_subdiario');
							INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 5, 0, 1, '$compra_subdiario');
							INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 5, 0, 2, '$compra_subdiario');
							INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 5, 1, 0, '$compra_cuenta_proveedor');
							INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 5, 1, 1, '$compra_cuenta_mercaderia');
							INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 5, 2, 0, '$compra_cuenta_proveedor');
							INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 5, 2, 1, '$compra_cuenta_mercaderia');
							INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 5, 3, 0, '$compra_cuenta_proveedor');
							INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 5, 3, 1, '$compra_cuenta_impuesto');
							INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 5, 3, 2, '$compra_cuenta_mercaderia');
							INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 5, 3, 3, 'INAFECTO');
							INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 5, 3, 4, 'PERCEPCION');
						");

						if ((int)$iStatus < 0) {
							$sqlca->query("ROLLBACK;");							
						}else{
							$sqlca->query("COMMIT;");			
						}
					}
				}
			}
		}
	}

	/*
	concar_confignew Values
		module Values
		6 Ventas Manuales
			category
			0 Cuentas Configuracion
						0 Subdiario de Ventas Documentos Manuales
			1 Cuentas Documentos Manuales Combustible
						0 Cuenta Documentos Manuales Combustible Cliente
						1 Cuenta Documentos Manuales Impuesto
						2 Cuenta Documentos Manuales Combustible Ventas
			2 Cuentas Documentos Manuales GLP
						0 Cuenta Documentos Manuales GLP Cliente
						1 Cuenta Documentos Manuales GLP Ventas
	*/
	function insert_module6_ventas_manuales() {
		global $sqlca;
			
		//VERIFICAMOS QUE NO HAYA DATA INSERTADA
		$iStatus = $sqlca->query("SELECT count(*) as resultado_cantidad FROM concar_confignew WHERE module = '6'");

		//SE EJECUTO LA QUERY
		if ((int)$iStatus > 0) {
			$row = $sqlca->fetchRow();
			$resultado_cantidad = $row['resultado_cantidad'];

			//SI LA CANTIDAD ES 0, INSERTAMOS
			if($resultado_cantidad == 0){

				//OBTENEMOS SUCURSALES
				$iStatus = $sqlca->query("SELECT ch_sucursal FROM int_ta_sucursales LIMIT 1");				

				//SE EJECUTO LA QUERY
				if ((int)$iStatus > 0) {					
					$row = $sqlca->fetchRow();
					$ch_sucursal = $row['ch_sucursal'];										

					//OBTENEMOS INFORMACION DE CONCAR_CONFIG PARA EL MODULO VENTAS DOCUMENTOS MANUALES
					$iStatus = $sqlca->query("SELECT
													venta_subdiario_docManual
													,venta_cuenta_cliente_dMa
													,venta_cuenta_impuesto
													,venta_cuenta_ventas_dMa
													,venta_cuenta_cliente_glp2
													,venta_cuenta_ventas_glp2
												FROM
													concar_config;");	
						
					//SE EJECUTO LA QUERY
					if ((int)$iStatus > 0) {
						$row = $sqlca->fetchRow();
						error_log( json_encode( $row ) ); //Usamos el indice numerico, ya que Postgres retorna el nombre del dato en minusculas
						$venta_subdiario_docManual = $row[0];
						$venta_cuenta_cliente_dMa  = $row[1];	
						$venta_cuenta_impuesto     = $row[2];											
						$venta_cuenta_ventas_dMa   = $row[3];
						$venta_cuenta_cliente_glp2 = $row[4];
						$venta_cuenta_ventas_glp2  = $row[5];

						//INICIAMOS TRANSACCION
						$sqlca->query("BEGIN;");

						//INSERTAMOS DATA
						$iStatus = $sqlca->query("
							INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 6, 0, 0, '$venta_subdiario_docManual');
							INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 6, 1, 0, '$venta_cuenta_cliente_dMa');
							INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 6, 1, 1, '$venta_cuenta_impuesto');
							INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 6, 1, 2, '$venta_cuenta_ventas_dMa');
							INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 6, 2, 0, '$venta_cuenta_cliente_glp2');
							INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 6, 2, 1, '$venta_cuenta_ventas_glp2');
						");

						if ((int)$iStatus < 0) {
							$sqlca->query("ROLLBACK;");							
						}else{
							$sqlca->query("COMMIT;");			
						}
					}
				}
			}
		}
	}

	/*
	concar_confignew Values
		module Values
		8 Liquidacion de Caja
			category
			0 Cuentas Configuracion
						0 Subdiario de Liquidacion de Caja
			1 Cuentas Caja
						0 Cuenta Caja Combustible  
						1 Cuenta Caja GLP 		  
						2 Cuenta Caja Market       
			2 Cuentas Efectivo
						0 Cuenta Ticket Efectivo	 					
			3 Cuentas Tarjeta
						0 Cuenta Tarjetas Credito
						1 Cuenta Tarjeta Visa
						2 Cuenta Tarjeta American Express
						3 Cuenta Tarjeta Mastercard
						4 Cuenta Tarjeta Dinners
						5 Cuenta Tarjeta CMR
						6 Cuenta Tarjeta Ripley
						7 Cuenta Tarjeta Cheques Otros
						8 Cuenta Tarjeta Cheques BBVA
						9 Cuenta Tarjeta Metroplazos	
			4 Cuentas Codigo Anexo
						0 Codigo de Anexo de Combustible
						1 Codigo de Anexo de GLP
						2 Codigo de Anexo de Market		
	*/
	function insert_module8_liquidacion_caja() {
		global $sqlca;
			
		//VERIFICAMOS QUE NO HAYA DATA INSERTADA
		$iStatus = $sqlca->query("SELECT count(*) as resultado_cantidad FROM concar_confignew WHERE module = '8'");

		//SE EJECUTO LA QUERY
		if ((int)$iStatus > 0) {
			$row = $sqlca->fetchRow();
			$resultado_cantidad = $row['resultado_cantidad'];

			//SI LA CANTIDAD ES 0, INSERTAMOS
			if($resultado_cantidad == 0){

				//OBTENEMOS SUCURSALES
				$iStatus = $sqlca->query("SELECT ch_sucursal FROM int_ta_sucursales LIMIT 1");				

				//SE EJECUTO LA QUERY
				if ((int)$iStatus > 0) {					
					$row = $sqlca->fetchRow();
					$ch_sucursal = $row['ch_sucursal'];

					//INICIAMOS TRANSACCION
					$sqlca->query("BEGIN;");		

					//INSERTAMOS DATA
					$iStatus = $sqlca->query("
						INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 8, 0, 0, '71');
						INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 8, 1, 0, '101101');
						INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 8, 1, 1, '101102');
						INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 8, 1, 2, '101103');
						INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 8, 2, 0, '103101');
						INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 8, 3, 0, '-');
						INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 8, 3, 1, '162401');
						INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 8, 3, 2, '162402');
						INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 8, 3, 3, '162403');
						INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 8, 3, 4, '162404');
						INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 8, 3, 5, '162405');
						INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 8, 3, 6, '162406');
						INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 8, 3, 7, '162407');
						INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 8, 3, 8, '162408');
						INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 8, 3, 9, '162409');
						INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 8, 4, 0, '0001');
						INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 8, 4, 1, '0002');
						INSERT INTO public.concar_confignew (concar_confignew_id, ch_sucursal, module, category, subcategory, account) VALUES (nextval('seq_concar_confignew_id'), '$ch_sucursal', 8, 4, 2, '0003');
					");

					if ((int)$iStatus < 0) {
						$sqlca->query("ROLLBACK;");							
					}else{
						$sqlca->query("COMMIT;");			
					}
				}
			}			
		}
	}

	function obtenerParametros() {
		global $sqlca;

		//$defaultparams = Array("0.0.0.0:1433","zidigital","20512963545","RSCONCAR_PRUEBA");

		$sql="
SELECT
 p1.par_valor,
 p2.par_valor,
 p3.par_valor,
 p4.par_valor
FROM
 int_parametros p1
 LEFT JOIN int_parametros p2 ON p2.par_nombre='concar_username'
 LEFT JOIN int_parametros p3 ON p3.par_nombre='concar_password'
 LEFT JOIN int_parametros p4 ON p4.par_nombre='concar_dbname'
WHERE
 p1.par_nombre='concar_ip'
		";

		if ($sqlca->query($sql) < 0)
			return $defaultparams;

		if ($sqlca->numrows() != 1)
			return $defaultparams;

		$reg = $sqlca->fetchRow();

		return Array($reg[0],$reg[1],$reg[2],$reg[3]);
	}


	function agregaFecha($tipo, $almacen, $inicio, $fin) {
		global $sqlca;
		
		if($tipo == "1")
			$tip = "VEN"; //tipo : '1' => VENTAS Y CUENTAS POR COBRAR
		else
			$tip = "COM"; //tipo : '2' => COMPRAS

		$sql = "INSERT INTO mig_concar (tipo,ch_almacen,fecha_inicio,fecha_fin,ch_usuario, fec_actualizacion) 
				VALUES ('$tip','$almacen',to_date('$inicio','DD/MM/YYYY'),to_date('$fin','DD/MM/YYYY'),'{$_SESSION['auth_usuario']}', now());";		

		if ($sqlca->query($sql)<=0)
			return $sqlca->get_error();
	}
	
	function verificaFecha($tipo, $almacen, $inicio, $fin) {
		global $sqlca;
		
		if($tipo == "1")
			$tip = "VEN"; //tipo : '1' => VENTAS Y CUENTAS POR COBRAR
		else
			$tip = "COM"; //tipo : '2' => COMPRAS

		$sql = "SELECT 1 FROM mig_concar WHERE ('$inicio' BETWEEN fecha_inicio AND fecha_fin OR '$fin' BETWEEN fecha_inicio AND fecha_fin) AND ch_almacen = '$almacen' AND tipo='$tip';";
		
		if ($sqlca->query($sql)<=0)
			return $sqlca->get_error();

		if ($sqlca->numrows() != 0)
			return 5;
	}

	function obtenerAlmacenes($codigo) {
		global $sqlca;
		
		$cond = '';
		if ($codigo != "") 
			$cond = "AND trim(ch_sucursal) = '".pg_escape_string($codigo)."' ";
		
		$sql = "SELECT ch_almacen, trim(ch_nombre_almacen) FROM inv_ta_almacenes WHERE ch_clase_almacen='1' ".$cond." ORDER BY ch_almacen;";

		if ($sqlca->query($sql) < 0) 
			return false;
			
		$result = Array();
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$result[$a[0]] = $a[0] . " - " . $a[1];
		}
		return $result;
	}

	function Empresa() {
		global $sqlca;
		
		$sql = "SELECT cod_empresa FROM concar_config;";
		//$sql = "SELECT TRIM(account) as cod_empresa FROM concar_confignew WHERE module = 0 and category = 0 and subcategory = 0;";

		if ($sqlca->query($sql) < 0) 
			return false;
			
		$result = Array();
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$result[$a[0]] = $a[0];
		}
		return $result;
	}

	function interface_clientes_combustibles($FechaIni, $FechaFin, $almacen, $codEmpresa, $num_actual) { // CLIENTES COMBUSTIBLES
		global $sqlca;

		if(trim($num_actual)=="")
			$num_actual = 0;
		$FechaIni_Original = $FechaIni;
		$FechaFin_Original = $FechaFin;
		$FechaDiv = explode("/", $FechaIni);
		$FechaIni = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];
		$anio1 	  = $FechaDiv[2];
		$mes1 	  = $FechaDiv[1];
		$dia1 	  = $FechaDiv[0];				
		$postrans = "pos_trans".$FechaDiv[2].$FechaDiv[1];
		$FechaDiv = explode("/", $FechaFin);
		$FechaFin = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];
		$anio2 	  = $FechaDiv[2];
		$mes2 	  = $FechaDiv[1];
		$dia2 	  = $FechaDiv[0];				
		$Anio 	  = substr($FechaDiv[2],2,3);
		
		if ($anio1 != $anio2 || $mes1 != $mes2) {
			return 3; // Fechas deben ser mismo mes y año
		} else {
			if ($dia1 > $dia2) 
				return 4; // Fecha inicio debe ser menor que fecha final					
		}

		$Anio = trim($Anio);
		$codEmpresa = trim($codEmpresa);

		$TabCan    = "CAN".$codEmpresa;

			
		//*************** Lista de Clientes RUC **************

		$cli = "
			SELECT 
				t.ruc as ruc, 
				SUBSTRING(first(r.razsocial),0,39) as razsocial
			FROM 
				$postrans t 
				LEFT JOIN ruc r USING (ruc) 
			WHERE 
				t.td = 'F'
				AND t.tipo = 'C'
				AND date(t.dia) BETWEEN '$FechaIni' AND '$FechaFin'
				AND t.es = '$almacen'
			GROUP BY 
				t.ruc;
			";

		//echo "CLIENTES COMBUSTIBLES: \n\n".$cli."\n\n";			
				
		if ($sqlca->query($cli) <= 0){
			$arrInserts['clientes'][0] = array();
			$arrClientesCombustible = array($arrInserts, $TabCan);
			return $arrClientesCombustible;
		}

		$rs = Array();
		$c7 = 0;

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$rs[$i]['ruc'] = $a[0];
			$rs[$i]['raz'] = $a[1];
			++$c7;
		}	
	
		trigger_error("sql-CAN0081: {$c7}");
		$finalrucs = verificaRUCS($rs, $TabCan);
			
		for($j=0; $j<count($finalrucs); $j++) {
			$arrInserts['clientes'][$j] = $finalrucs[$j];
		}

		$arrClientesCombustible = array($arrInserts, $TabCan);
		return $arrClientesCombustible;
	}

	function interface_clientes_market($FechaIni, $FechaFin, $almacen, $codEmpresa, $num_actual) { // CLIENTES MARKET
		global $sqlca;

		if(trim($num_actual)=="")
			$num_actual = 0;
		$FechaIni_Original = $FechaIni;
		$FechaFin_Original = $FechaFin;
		$FechaDiv = explode("/", $FechaIni);
		$FechaIni = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];
		$anio1 	  = $FechaDiv[2];
		$mes1 	  = $FechaDiv[1];
		$dia1 	  = $FechaDiv[0];				
		$postrans = "pos_trans".$FechaDiv[2].$FechaDiv[1];
		$FechaDiv = explode("/", $FechaFin);
		$FechaFin = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];
		$anio2 	  = $FechaDiv[2];
		$mes2 	  = $FechaDiv[1];
		$dia2 	  = $FechaDiv[0];				
		$Anio 	  = substr($FechaDiv[2],2,3);
		
		if ($anio1 != $anio2 || $mes1 != $mes2) {
			return 3; // Fechas deben ser mismo mes y año
		} else {
			if ($dia1 > $dia2) 
				return 4; // Fecha inicio debe ser menor que fecha final					
		}

		$Anio = trim($Anio);
		$codEmpresa = trim($codEmpresa);

		$TabCan    = "CAN".$codEmpresa;

			
		//*************** Lista de Clientes RUC **************

		$cli = "SELECT 
				t.ruc as ruc, 
				SUBSTRING(first(r.razsocial),0,39) as razsocial
			FROM 
				$postrans t 
				LEFT JOIN ruc r USING (ruc) 
			WHERE 
				t.td = 'F'
				AND t.tipo = 'M'
				AND date(t.dia) BETWEEN '$FechaIni' AND '$FechaFin'
				AND t.es = '$almacen'
				AND t.ruc NOT IN(SELECT 
							t.ruc
						FROM 
							$postrans t 
							LEFT JOIN ruc r USING (ruc) 
						WHERE 
							t.td = 'F'
							AND t.tipo = 'C'
							AND date(t.dia) BETWEEN '$FechaIni' AND '$FechaFin'
							AND t.es = '$almacen'
						GROUP BY 
							t.ruc)
			GROUP BY 
				t.ruc;
			";

		//echo "CLIENTES MARKET: \n\n".$cli."\n\n";			
				
		if ($sqlca->query($cli) <= 0){
			$arrInserts['clientes'][0] = array();
			$arrClientesMarket = array($arrInserts, $TabCan);
			return $arrClientesMarket;
		}
			
		$rs = Array();
		$c7 = 0;

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$rs[$i]['ruc'] = $a[0];
			$rs[$i]['raz'] = $a[1];
			++$c7;
		}	
	
		trigger_error("sql-CAN0081: {$c7}");
		$finalrucs = verificaRUCS($rs, $TabCan);
			
		for($j=0; $j<count($finalrucs); $j++) {
			$arrInserts['clientes'][$j] = $finalrucs[$j];
		}

		$arrClientesMarket = array($arrInserts, $TabCan);
		return $arrClientesMarket;
	}

	function interface_clientes_documentos($FechaIni, $FechaFin, $almacen, $codEmpresa, $num_actual) { // CLIENTES DOCUMENTOS MANUALES
		global $sqlca;

		if(trim($num_actual)=="")
			$num_actual = 0;
		$FechaIni_Original = $FechaIni;
		$FechaFin_Original = $FechaFin;
		$FechaDiv = explode("/", $FechaIni);
		$FechaIni = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];
		$anio1 	  = $FechaDiv[2];
		$mes1 	  = $FechaDiv[1];
		$dia1 	  = $FechaDiv[0];				
		$postrans = "pos_trans".$FechaDiv[2].$FechaDiv[1];
		$FechaDiv = explode("/", $FechaFin);
		$FechaFin = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];
		$anio2 	  = $FechaDiv[2];
		$mes2 	  = $FechaDiv[1];
		$dia2 	  = $FechaDiv[0];				
		$Anio 	  = substr($FechaDiv[2],2,3);
		
		if ($anio1 != $anio2 || $mes1 != $mes2) {
			return 3; // Fechas deben ser mismo mes y año
		} else {
			if ($dia1 > $dia2) 
				return 4; // Fecha inicio debe ser menor que fecha final					
		}

		$Anio = trim($Anio);
		$codEmpresa = trim($codEmpresa);

		$TabCan    = "CAN".$codEmpresa;

			
		//*************** Lista de Clientes RUC **************

		$cli = "SELECT 
				cli.cli_ruc as ruc,
				SUBSTRING(first(cli.cli_razsocial),0,39) as razsocial
			FROM 
				fac_ta_factura_cabecera cab
				LEFT JOIN int_clientes cli USING (cli_codigo)
			WHERE 
				cab.ch_fac_tipodocumento IN ('10','35')
				AND cab.dt_fac_fecha BETWEEN '$FechaIni' AND '$FechaFin'
				AND cab.ch_almacen = '$almacen'
				AND cli_codigo NOT IN(SELECT 
					t.ruc
				FROM 
					$postrans t 
					LEFT JOIN ruc r USING (ruc) 
				WHERE 
					t.td = 'F'
					AND date(t.dia) BETWEEN '$FechaIni' AND '$FechaFin'
					AND t.es = '$almacen'
				GROUP BY 
					t.ruc)
			GROUP BY
				cli.cli_ruc;
			";

		//echo "CLIENTES DOCUMENTOS MANUALES: \n\n".$cli."\n\n";			
				
		if ($sqlca->query($cli) <= 0){
			$arrInserts['clientes'][0] = array();
			$arrClientesManuales = array($arrInserts, $TabCan);
			return $arrClientesManuales;
		}

		$rs = Array();
		$c7 = 0;

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$rs[$i]['ruc'] = $a[0];
			$rs[$i]['raz'] = $a[1];
			++$c7;
		}	
	
		trigger_error("sql-CAN0081: {$c7}");
		$finalrucs = verificaRUCS($rs, $TabCan);
			
		for($j=0; $j<count($finalrucs); $j++) {
			$arrInserts['clientes'][$j] = $finalrucs[$j];
		}

		$arrClientesManuales = array($arrInserts, $TabCan);
		return $arrClientesManuales;
	}

	function interface_ventas_combustible($FechaIni, $FechaFin, $almacen, $codEmpresa, $num_actual) { // VENTAS COMBUSTIBLE
		global $sqlca;

		$clientes = InterfaceConcarActModel::interface_clientes_combustibles($FechaIni, $FechaFin, $almacen, $codEmpresa, $num_actual);
		
		if(trim($num_actual)=="")
			$num_actual = 0;
		$FechaIni_Original = $FechaIni;
		$FechaFin_Original = $FechaFin;
		$FechaDiv = explode("/", $FechaIni);
		$FechaIni = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];
		$anio1 	  = $FechaDiv[2];
		$mes1 	  = $FechaDiv[1];
		$dia1 	  = $FechaDiv[0];				
		$postrans = "pos_trans".$FechaDiv[2].$FechaDiv[1];
		$FechaDiv = explode("/", $FechaFin);
		$FechaFin = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];
		$anio2 	  = $FechaDiv[2];
		$mes2 	  = $FechaDiv[1];
		$dia2 	  = $FechaDiv[0];				
		$Anio 	  = substr($FechaDiv[2],2,3);
		
		if ($anio1 != $anio2 || $mes1 != $mes2) {
			return 3; // Fechas deben ser mismo mes y año
		} else {
			if ($dia1 > $dia2) 
				return 4; // Fecha inicio debe ser menor que fecha final					
		}

		$Anio = trim($Anio);
		$codEmpresa = trim($codEmpresa);

		$TabSqlCab = "CC".$codEmpresa.$Anio; // Tabla SQL Cabecera
		$TabSqlDet = "CD".$codEmpresa.$Anio; // Tabla SQL Detalle
		$TabCan    = "CAN".$codEmpresa;

// 		$sql = "
// SELECT
//  venta_subdiario, 
//  venta_cuenta_cliente, 
//  venta_cuenta_impuesto, 
//  venta_cuenta_ventas, 
//  id_cencos_comb, 
//  subdiario_dia,
//  venta_cuenta_cliente_glp,
//  venta_cuenta_ventas_glp,
//  id_centro_costo_glp,
//  cod_cliente,
//  cod_caja    
// FROM 
//  concar_config;
// 		";

// 		if ($sqlca->query($sql) < 0) 
// 			return false;	
	
// 		$a = $sqlca->fetchRow();
// 		$vcsubdiario = $a[0];
// 		$vccliente   = $a[1];
// 		$vcimpuesto  = $a[2];
// 		$vcventas    = $a[3];	
// 		$vccencos    = $a[4]; 
// 		$opcion      = $a[5];
// 		$vclienteglp = $a[6];
// 		$vventasglp  = $a[7];
// 		$cencosglp   = $a[8];
// 		$cod_cliente = $a[9];
// 		$cod_caja    = $a[10];

		$sql = "
SELECT
 	c1.account AS venta_subdiario
	,c2.account AS venta_cuenta_cliente
	,c3.account AS venta_cuenta_impuesto
	,c4.account AS venta_cuenta_ventas
	,c5.account AS id_cencos_comb
	,c6.account AS subdiario_dia
	,c7.account AS venta_cuenta_cliente_glp
	,c8.account AS venta_cuenta_ventas_glp
	,c9.account AS id_centro_costo_glp
	,c10.account AS cod_cliente
	,c11.account AS cod_caja
FROM 
 	concar_confignew c1
 	LEFT JOIN concar_confignew c2 ON   c2.module = 1   AND c2.category = 1   AND c2.subcategory = 0   --Cuenta Combustible Cliente
	LEFT JOIN concar_confignew c3 ON   c3.module = 1   AND c3.category = 1   AND c3.subcategory = 1   --Cuenta Combustible Impuesto
	LEFT JOIN concar_confignew c4 ON   c4.module = 1   AND c4.category = 1   AND c4.subcategory = 2   --Cuenta Combustible Ventas

	LEFT JOIN concar_confignew c5 ON   c5.module = 0   AND c5.category = 2   AND c5.subcategory = 0   --Centro de Costo Combustible
	LEFT JOIN concar_confignew c6 ON   c6.module = 0   AND c6.category = 1   AND c6.subcategory = 0   --Subdiario dia
	
	LEFT JOIN concar_confignew c7 ON   c7.module = 1   AND c7.category = 2   AND c7.subcategory = 0   --Cuenta GLP Cliente
	LEFT JOIN concar_confignew c8 ON   c8.module = 1   AND c8.category = 2   AND c8.subcategory = 1   --Cuenta GLP Ventas

	LEFT JOIN concar_confignew c9 ON   c9.module = 0   AND c9.category = 2   AND c9.subcategory = 1   --Centro de Costo GLP
	LEFT JOIN concar_confignew c10 ON   c10.module = 0   AND c10.category = 1   AND c10.subcategory = 1   --Codigo de Cliente
	LEFT JOIN concar_confignew c11 ON   c11.module = 0   AND c11.category = 1   AND c11.subcategory = 2   --Codigo de Caja
WHERE   
	c1.module = 1   AND c1.category = 0   AND c1.subcategory = 0;   --Subdiario de Venta Combustible
		";

		if ($sqlca->query($sql) < 0) 
			return false;	
	
		$a = $sqlca->fetchRow();
		$vcsubdiario = $a[0];
		$vccliente   = $a[1];
		$vcimpuesto  = $a[2];
		$vcventas    = $a[3];	
		$vccencos    = $a[4]; 
		$opcion      = $a[5];
		$vclienteglp = $a[6];
		$vventasglp  = $a[7];
		$cencosglp   = $a[8];
		$cod_cliente = $a[9];
		$cod_caja    = $a[10];

		$sql = "
			SELECT * FROM (
				SELECT 
					to_char(date(dia),'YYMMDD') as dia,
					'$vccliente'::text as DCUENTA,
					'$cod_cliente'::text as codigo,
					' '::text as trans,
					'1'::text as tip,
					'D'::text as ddh, 
					round(sum(importe),2) as importe, 
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal, 
					'$cod_caja'|| cfp.ch_posz_pos || '-' ||MIN(cast(trans as integer))||'-'||MAX(cast(trans as integer))::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'A'::text as tip2,
					'TK'::TEXT as doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					td 		= 'B' 
					AND tipo	= 'C'
					AND codigo	!= '11620307'
					AND es		= '$almacen'
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
					AND t.usr = ''
				GROUP BY 
					dia,
					es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos 
				ORDER BY 
					dia
			) as A

			UNION 

			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vcimpuesto'::text as DCUENTA, 
					''::text as codigo, 
					' '::text as trans, 
					'1'::text as tip, 
					'H'::text as ddh, 
					round(sum(igv),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal , 
					'$cod_caja'|| cfp.ch_posz_pos || '-' ||MIN(cast(trans as integer))||'-'||MAX(cast(trans as integer))::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'A'::text as tip2,
					'TK'::TEXT as doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					td = 'B' 
					AND tipo='C'  AND codigo!='11620307' 
					AND date(dia) between '$FechaIni' AND '$FechaFin' 
					AND es = '$almacen'
					AND t.usr = ''
				GROUP BY 
					dia,
					es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos 
				ORDER BY 
					dia
			)
			UNION
			(	SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vcventas'::text as DCUENTA, 
					codigo_concar, 
					' '::text as trans, 
					'1'::text as tip, 
					'H'::text as ddh, 
					round(sum(importe-igv),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal , 				
					''::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'A'::text as tip2,
					'TK'::TEXT as doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal),
					interface_equivalencia_producto
				WHERE 
					td = 'B' 
					AND tipo='C'  AND codigo!='11620307' 
					AND date(dia) between '$FechaIni' AND '$FechaFin' 
					AND codigo=art_codigo  
					AND es = '$almacen'
					AND t.usr = ''
				GROUP BY 
					dia,
					codigo_concar,
					es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos
				ORDER BY 
					dia 
			)--FIN DE LETRA A
			UNION--INICIO DE LETRA B
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 		
					'$vclienteglp'::text as DCUENTA, 
					'$cod_cliente'::text as codigo, 
					' '::text as trans, 
					'1'::text as tip, 
					'D'::text as ddh, 
					round(sum(importe),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal, 
					'$cod_caja'|| cfp.ch_posz_pos || '-' ||MIN(cast(trans as integer))||'-'||MAX(cast(trans as integer))::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'B'::text as tip2,
					'TK'::TEXT as doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					td = 'B' 
					AND tipo = 'C'  AND codigo='11620307' 
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND es = '$almacen'
					AND t.usr = ''
				GROUP BY 
					dia,
					es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos	
				ORDER BY 
					dia
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vcimpuesto'::text as DCUENTA, 
					''::text as codigo, 
					' '::text as trans, 
					'1'::text as tip, 
					'H'::text as ddh, 
					round(sum(igv),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal , 
					'$cod_caja'|| cfp.ch_posz_pos || '-' ||MIN(cast(trans as integer))||'-'||MAX(cast(trans as integer))::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'B'::text as tip2,
					'TK'::TEXT as doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					td = 'B' 
					AND tipo='C'  AND codigo='11620307' 
					AND date(dia) between '$FechaIni' AND '$FechaFin' 
					AND es = '$almacen'
					AND t.usr = ''
				GROUP BY 
					dia,
					es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vventasglp'::text as DCUENTA, 
					codigo_concar, 
					' '::text as trans, 
					'1'::text as tip, 
					'H'::text as ddh, 
					round(sum(importe-igv),2) as importe, 
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal , 				
					''::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$cencosglp'::text as DCENCOS,
					'B'::text as tip2,
					'TK'::TEXT as doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal),
					interface_equivalencia_producto
				WHERE 
					td = 'B' 
					AND tipo = 'C'
					AND codigo ='11620307' 
					AND date(dia) between '$FechaIni' AND '$FechaFin' 
					AND codigo 	= art_codigo
					AND es 		= '$almacen'
					AND t.usr 	= ''
				GROUP BY 
					dia,
					codigo_concar,
					es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos
			)--FIN TICKET BOLETA DE VENTA
			UNION --INICIO BOLETA ELECTRONICA DE VENTA
			(
			SELECT 
				to_char(date(dia),'YYMMDD') as dia,
				'$vccliente'::text as DCUENTA,
				'$cod_cliente'::text as codigo,
				' '::text as trans,
				'1'::text as tip,
				'D'::text as ddh, 
				round(sum(importe),2) as importe, 
				cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
				es as sucursal,
				SUBSTR(TRIM(t.usr), 0, 5) || '-' || MIN(SUBSTR(TRIM(t.usr), 7)) || '/' || MAX(SUBSTR(TRIM(t.usr), 7))::text as dnumdoc,
				'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
				''::text as DCENCOS,
				'C'::text as tip2,
				'BV'::TEXT as doctype,
				''::text AS documento_referencia,
				''::text AS fe_referencia,
				0 AS nu_igv_referencia,
				0 AS nu_bi_referencia
			FROM 
				$postrans t
				LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
			WHERE 
				td 			= 'B' 
				AND tipo	= 'C'
				AND codigo	!= '11620307'
				AND es		= '$almacen'
				AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
				AND t.usr != ''
				AND t.tm IN ('V') --Agregado Requerimiento Concar, Ticket TF-0000005844
			GROUP BY 
				dia,
				es,
				cfp.nu_posz_z_serie,
				cfp.ch_posz_pos,
				SUBSTR(TRIM(usr), 0, 5)
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vcimpuesto'::text as DCUENTA, 
					''::text as codigo, 
					' '::text as trans, 
					'1'::text as tip, 
					'H'::text as ddh, 
					round(sum(igv),2) as importe, 
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal,
					SUBSTR(TRIM(t.usr), 0, 5) || '-' || MIN(SUBSTR(TRIM(t.usr), 7)) || '/' || MAX(SUBSTR(TRIM(t.usr), 7))::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'C'::text as tip2,
					'BV'::TEXT as doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					td = 'B' 
					AND tipo='C'  AND codigo!='11620307' 
					AND date(dia) between '$FechaIni' AND '$FechaFin' 
					AND es = '$almacen'
					AND t.usr != ''
					AND t.tm IN ('V') --Agregado Requerimiento Concar, Ticket TF-0000005844
				GROUP BY 
					dia,
					es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos,
					SUBSTR(TRIM(usr), 0, 5)
			) 

			UNION 
			
			(	SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vcventas'::text as DCUENTA, 
					codigo_concar, 
					' '::text as trans, 
					'1'::text as tip, 
					'H'::text as ddh, 
					round(sum(importe-igv),2) as importe, 
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal , 				
					''::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'C'::text as tip2,
					'BV'::TEXT as doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal),
					interface_equivalencia_producto
				WHERE 
					td = 'B' 
					AND tipo='C'  AND codigo!='11620307' 
					AND date(dia) between '$FechaIni' AND '$FechaFin' 
					AND codigo=art_codigo  
					AND es = '$almacen'
					AND t.usr != ''
					AND t.tm IN ('V') --Agregado Requerimiento Concar, Ticket TF-0000005844
				GROUP BY 
					dia,
					codigo_concar,
					es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos
				ORDER BY 
					dia 
			)--FIN DE LETRA C
			UNION--INICIO DE LETRA D
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 		
					'$vclienteglp'::text as DCUENTA, 
					'$cod_cliente'::text as codigo, 
					' '::text as trans, 
					'1'::text as tip, 
					'D'::text as ddh, 
					round(sum(importe),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal, 
					SUBSTR(TRIM(t.usr), 0, 5) || '-' || MIN(SUBSTR(TRIM(t.usr), 7)) || '/' || MAX(SUBSTR(TRIM(t.usr), 7))::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'D'::text as tip2,
					'BV'::TEXT as doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					td = 'B' 
					AND tipo = 'C'  AND codigo='11620307' 
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND es = '$almacen' 
					AND t.usr != ''
					AND t.tm IN ('V') --Agregado Requerimiento Concar, Ticket TF-0000005844
				GROUP BY 
					dia,
					es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos,
					SUBSTR(TRIM(usr), 0, 5)
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vcimpuesto'::text as DCUENTA, 
					''::text as codigo, 
					' '::text as trans, 
					'1'::text as tip, 
					'H'::text as ddh,
					round(sum(igv),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal,
					SUBSTR(TRIM(t.usr), 0, 5) || '-' || MIN(SUBSTR(TRIM(t.usr), 7)) || '/' || MAX(SUBSTR(TRIM(t.usr), 7))::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'D'::text as tip2,
					'BV'::TEXT as doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					td = 'B' 
					AND tipo='C'  AND codigo='11620307' 
					AND date(dia) between '$FechaIni' AND '$FechaFin' 
					AND es = '$almacen' 
					AND t.usr != ''
					AND t.tm IN ('V') --Agregado Requerimiento Concar, Ticket TF-0000005844
				GROUP BY 
					dia,
					es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos,
					SUBSTR(TRIM(usr), 0, 5)
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vventasglp'::text as DCUENTA, 
					codigo_concar, 
					' '::text as trans, 
					'1'::text as tip, 
					'H'::text as ddh, 
					round(sum(importe-igv),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal,
					''::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$cencosglp'::text as DCENCOS,
					'D'::text as tip2,
					'BV'::TEXT as doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal),
					interface_equivalencia_producto
				WHERE 
					td = 'B' 
					AND tipo='C'  AND codigo='11620307' 
					AND date(dia) between '$FechaIni' AND '$FechaFin' 
					AND codigo=art_codigo  
					AND es = '$almacen'
					AND t.usr != ''
					AND t.tm IN ('V') --Agregado Requerimiento Concar, Ticket TF-0000005844
				GROUP BY 
					dia,
					codigo_concar,
					es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos
			)--FIN BOLETAS ELECTRONICAS DE VENTA Y LETRA DE D
			UNION --INICIO TICKETS FACTURAS DE VENTA Y LETRA DE E
			(
				SELECT
					to_char(date(dia),'YYMMDD') as dia, 
					'$vccliente'::text as DCUENTA, 
					ruc::text as codigo, 
					trans::text as trans,
					'1'::text as tip, 
					'D'::text as ddh, 
					round(sum(importe), 2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal,
					'$cod_caja'|| cfp.ch_posz_pos || '-' ||trans::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'E'::text as tip2,
					'TK'::TEXT as doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					td = 'F' 
					AND tm = 'V'
					AND tipo='C'  AND codigo!='11620307'  
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
					AND es = '$almacen'
					AND t.usr = ''
				GROUP BY
					dia,
					ruc,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					trans
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vcimpuesto'::text as DCUENTA, 
					''::text as codigo, 
					trans::text as trans, 
					'1'::text as tip,  
					'H'::text as ddh, 
					round(sum(igv),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal , 
					'$cod_caja'|| cfp.ch_posz_pos || '-' ||trans::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'E'::text as tip2,
					'TK'::TEXT as doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					td = 'F' 
					AND tm = 'V'
					AND tipo='C'   AND codigo!='11620307'  
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
					AND es = '$almacen'
					AND t.usr = ''
				GROUP BY
					dia,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					trans
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vcventas'::text as DCUENTA, 
					codigo_concar, 
					trans::text as trans,
					'1'::text as tip, 
					'H'::text as ddh, 
					(round(sum(importe),2)-round(sum(igv),2)) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal , 
					'$cod_caja'|| cfp.ch_posz_pos || '-' ||trans::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'E'::text as tip2,
					'TK'::TEXT as doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal),
					interface_equivalencia_producto
				WHERE 
					td = 'F' 
					AND tm = 'V'
					AND tipo='C'   AND codigo!='11620307' 
					AND date(dia) between '$FechaIni' AND '$FechaFin' 
					AND codigo=art_codigo 
					AND es = '$almacen'
					AND t.usr = ''
				GROUP BY
					dia,
					codigo_concar,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					trans
			)--FIN DE LETRA E
			UNION--INICIO DE LETRA F
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vclienteglp'::text as DCUENTA, 
					ruc::text as codigo, 
					trans::text as trans,
					'1'::text as tip, 
					'D'::text as ddh, 
					round(sum(importe), 2) as importe, 
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal , 
					'$cod_caja'|| cfp.ch_posz_pos || '-' ||trans::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'F'::text as tip2,
					'TK'::TEXT as doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					td = 'F' 
					AND tm = 'V'
					AND tipo='C'  AND codigo='11620307'  
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND es = '$almacen'
					AND t.usr = ''
				GROUP BY
					dia,
					ruc,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					trans  
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vcimpuesto'::text as DCUENTA, 
					''::text as codigo, 
					trans::text as trans, 
					'1'::text as tip,  
					'H'::text as ddh, 
					round(sum(igv),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal , 
					'$cod_caja'|| cfp.ch_posz_pos || '-' ||trans::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'F'::text as tip2,
					'TK'::TEXT as doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					td = 'F' 
					AND tm = 'V'
					AND tipo='C'   AND codigo='11620307'  
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
					AND es = '$almacen'
					AND t.usr = ''
				GROUP BY
					dia,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					trans 
			)
			
			UNION 
			
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vventasglp'::text as DCUENTA, 
					codigo_concar, 
					trans::text as trans,
					'1'::text as tip, 
					'H'::text as ddh, 
					(round(sum(importe),2)-round(sum(igv),2)) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal , 
					'$cod_caja'|| cfp.ch_posz_pos || '-' ||trans::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$cencosglp'::text as DCENCOS,
					'F'::text as tip2,
					'TK'::TEXT as doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal),
					interface_equivalencia_producto
				WHERE 
					td = 'F' 
					AND tm = 'V'
					AND tipo='C'   AND codigo='11620307' 
					AND date(dia) between '$FechaIni' AND '$FechaFin'
					AND codigo=art_codigo 
					AND es = '$almacen'
					AND t.usr = ''
				GROUP BY
					dia,
					codigo_concar,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					trans  
			)--FIN DE TICKETS FACTURAS DE VENTAS Y LETRA F
			UNION --INICIO FACTURAS ELECTRONICAS DE VENTAS Y LETRA G
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vccliente'::text as DCUENTA, 
					ruc::text as codigo, 
					SUBSTR(TRIM(t.usr), 6)::text as trans,
					'1'::text as tip, 
					'D'::text as ddh, 
					round(sum(importe), 2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal , 
					SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'G'::text as tip2,
					'FT'::TEXT as doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					td = 'F' 
					AND tm = 'V'
					AND tipo='C'  AND codigo!='11620307'  
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND es = '$almacen'
					AND t.usr != ''
				GROUP BY
					dia,
					ruc,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					t.usr
			)
			UNION 
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia,
					'$vcimpuesto'::text as DCUENTA,
					''::text as codigo,
					SUBSTR(TRIM(t.usr), 6)::text as trans,
					'1'::text as tip,
					'H'::text as ddh,
					round(sum(igv),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal,
					SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'G'::text as tip2,
					'FT'::TEXT as doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					td = 'F' 
					AND tm = 'V'
					AND tipo='C'   AND codigo!='11620307'  
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
					AND es = '$almacen'
					AND t.usr != ''
				GROUP BY
					dia,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					t.usr
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vcventas'::text as DCUENTA, 
					codigo_concar, 
					SUBSTR(TRIM(t.usr), 6)::text as trans,
					'1'::text as tip, 
					'H'::text as ddh, 
					(round(sum(importe),2)-round(sum(igv),2)) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal,
					SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'G'::text as tip2,
					'FT'::TEXT as doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal),
					interface_equivalencia_producto
				WHERE 
					td = 'F' 
					AND tm = 'V'
					AND tipo='C'   AND codigo!='11620307' 
					AND date(dia) between '$FechaIni' AND '$FechaFin'
					AND codigo=art_codigo 
					AND es = '$almacen'
					AND t.usr != ''
				GROUP BY
					dia,
					codigo_concar,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					t.usr
			)--FIN DE LETRA G
			UNION--INICIO DE LETRA H
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vclienteglp'::text as DCUENTA, 
					ruc::text as codigo, 
					SUBSTR(TRIM(t.usr), 6)::text as trans,
					'1'::text as tip,
					'D'::text as ddh,
					round(sum(importe), 2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal , 
					SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'H'::text as tip2,
					'FT'::TEXT as doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					td = 'F' 
					AND tm = 'V'
					AND tipo='C'  AND codigo='11620307'  
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND es = '$almacen'
					AND t.usr != ''
				GROUP BY
					dia,
					ruc,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					t.usr
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vcimpuesto'::text as DCUENTA, 
					''::text as codigo, 
					SUBSTR(TRIM(t.usr), 6)::text as trans,
					'1'::text as tip,  
					'H'::text as ddh, 
					round(sum(igv),2) as importe,  
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal,
					SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'H'::text as tip2,
					'FT'::TEXT as doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					td = 'F' 
					AND tm = 'V'
					AND tipo='C'   AND codigo='11620307'  
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
					AND es = '$almacen'
					AND t.usr != ''
				GROUP BY
					dia,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					t.usr
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vventasglp'::text as DCUENTA, 
					codigo_concar, 
					SUBSTR(TRIM(t.usr), 6)::text as trans,
					'1'::text as tip, 
					'H'::text as ddh, 
					(round(sum(importe),2)-round(sum(igv),2)) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal,
					SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$cencosglp'::text as DCENCOS,
					'H'::text as tip2,
					'FT'::TEXT as doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal),
					interface_equivalencia_producto
				WHERE 
					td = 'F' 
					AND tm = 'V'
					AND tipo='C'   AND codigo='11620307' 
					AND date(dia) between '$FechaIni' AND '$FechaFin' 
					AND codigo=art_codigo
					AND es = '$almacen'
					AND t.usr != ''
				GROUP BY
					dia,
					codigo_concar,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					t.usr
			)--FIN DE FACTURAS ELECTRONICAS DE VENTAS Y LETRA H
			UNION --INICIO DE TICKETS FACTURA DE EXTORNOS Y LETRA I
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vclienteglp'::text as DCUENTA, 
					ruc::text as codigo, 
					trans::text as trans,
					'1'::text as tip,
					'H'::text as ddh,
					-round(sum(importe), 2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal,
					'$cod_caja'|| cfp.ch_posz_pos || '-' ||trans::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'I'::text as tip2,
					'TK'::TEXT as doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					td 			= 'F'
					AND tm 		= 'A'
					AND tipo 	= 'C'
					AND codigo 	= '11620307'
					AND es 		= '$almacen'
					AND t.usr 	= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY
					dia,
					ruc,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					trans  
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vcimpuesto'::text as DCUENTA, 
					''::text as codigo, 
					trans::text as trans, 
					'1'::text as tip,  
					'D'::text as ddh, 
					-round(sum(igv),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal , 
					'$cod_caja'|| cfp.ch_posz_pos || '-' ||trans::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS ,
					'I'::text as tip2,
					'TK'::TEXT as doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					td = 'F' 
					AND tm = 'A'
					AND tipo='C'   AND codigo='11620307'  
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
					AND es = '$almacen'
					AND t.usr = ''
				GROUP BY
					dia,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					trans 
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vventasglp'::text as DCUENTA, 
					codigo_concar, 
					trans::text as trans,
					'1'::text as tip, 
					'D'::text as ddh, 
					-(round(sum(importe),2)-round(sum(igv),2)) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal , 
					'$cod_caja'|| cfp.ch_posz_pos || '-' ||trans::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$cencosglp'::text as DCENCOS ,
					'I'::text as tip2,
					'TK'::TEXT as doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal),
					interface_equivalencia_producto
				WHERE 
					td = 'F' 
					AND tm = 'A'
					AND tipo='C'   AND codigo='11620307' 
					AND date(dia) between '$FechaIni' AND '$FechaFin' 
					AND codigo=art_codigo 
					AND es = '$almacen'
					AND t.usr = ''
				GROUP BY
					dia,
					codigo_concar,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					trans  
			)--FIN DE LETRA I
			UNION--INICIO DE LETRA J
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vclienteglp'::text as DCUENTA, 
					ruc::text as codigo, 
					trans::text as trans,
					'1'::text as tip,
					'H'::text as ddh,
					-round(sum(importe), 2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal , 
					'$cod_caja'|| cfp.ch_posz_pos || '-' ||trans::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'D'::text as tip2,
					'TK'::TEXT as doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					td = 'F' 
					AND tm = 'A'
					AND tipo='C'  AND codigo != '11620307'  
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND es = '$almacen'
					AND t.usr = ''
				GROUP BY
					dia,
					ruc,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					trans  
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vcimpuesto'::text as DCUENTA, 
					''::text as codigo, 
					trans::text as trans, 
					'1'::text as tip,  
					'D'::text as ddh, 
					-round(sum(igv),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal , 
					'$cod_caja'|| cfp.ch_posz_pos || '-' ||trans::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS ,
					'D'::text as tip2,
					'TK'::TEXT as doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					td = 'F' 
					AND tm = 'A'
					AND tipo='C'   AND codigo != '11620307'  
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
					AND es = '$almacen'
					AND t.usr = ''
				GROUP BY
					dia,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					trans 
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vventasglp'::text as DCUENTA, 
					codigo_concar, 
					trans::text as trans,
					'1'::text as tip, 
					'D'::text as ddh, 
					-(round(sum(importe),2)-round(sum(igv),2)) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal , 
					'$cod_caja'|| cfp.ch_posz_pos || '-' ||trans::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$cencosglp'::text as DCENCOS ,
					'D'::text as tip2,
					'TK'::TEXT as doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal),
					interface_equivalencia_producto
				WHERE 
					td = 'F' 
					AND tm = 'A'
					AND tipo='C'   AND codigo != '11620307' 
					AND date(dia) between '$FechaIni' AND '$FechaFin' 
					AND codigo=art_codigo 
					AND es = '$almacen'
					AND t.usr = ''
				GROUP BY
					dia,
					codigo_concar,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					trans
			)--FIN DE TICKETS FACTURA DE EXTORNOS
			UNION --INICIO DE FACTURAS ELECTRONICAS DE EXTORNOS
			(
				SELECT 
					to_char(DATE(dia),'YYMMDD') as dia, 
					'$vclienteglp'::text as DCUENTA, 
					ruc::text as codigo, 
					SUBSTR(TRIM(t.usr), 6)::text as trans,
					'1'::text as tip,
					'H'::text as ddh,
					-round(sum(importe), 2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal,
					SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS ,
					'D'::text as tip2,
					'NA'::TEXT as doctype,
					SUBSTR(TRIM(ext.fe1), 0, 5)||'-'||SUBSTR(TRIM(ext.fe1), 6) AS documento_referencia,
					FIRST(ext.fe_referencia),
					SUM(ext.nu_igv_referencia),
					SUM(ext.nu_bi_referencia)
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
					LEFT JOIN
						(SELECT
							venta_tickes.feoriginal AS fe1,
							venta_tickes.ticketextorno AS fe2,
							venta_tickes.feextorno AS fe3,
							FIRST(venta_tickes.fe_referencia) AS fe_referencia,
							SUM(venta_tickes.nu_igv_referencia) AS nu_igv_referencia,
							SUM(venta_tickes.nu_bi_referencia) AS nu_bi_referencia
						FROM
						(SELECT 
							extorno.origen AS cadenaorigen,
							p.trans AS ticketoriginal,
							extorno.trans1 AS ticketextorno,
							p.usr AS feoriginal,
							extorno.usr AS feextorno,
							TO_CHAR(p.dia, 'DD/MM/YYYY') AS fe_referencia,
							p.igv AS nu_igv_referencia,
							(p.importe - p.igv) AS nu_bi_referencia
						FROM
							$postrans p
							INNER JOIN (
							SELECT 
								(dia || caja || trim(to_char(rendi_gln,'99999999'))) as registro,
								(dia || caja || trans) as origen,
								trans as trans1,
								usr
							FROM
								$postrans
							WHERE
								es 		= '" . $almacen . "'
								AND tm 	= 'A'
								AND td 	= 'F'
								AND usr != ''
								AND DATE(dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
								AND codigo = '11620307'
							) as extorno ON(dia || caja || trim(to_char(trans,'99999999'))) = extorno.registro
							AND es 	= '" . $almacen . "'
							AND tm = 'V'
							AND td = 'F'
							AND p.trans < extorno.trans1
							AND p.usr != ''
							AND p.codigo = '11620307'
							AND DATE(p.dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
						) AS venta_tickes
						GROUP BY
							venta_tickes.cadenaorigen,
							venta_tickes.ticketoriginal,
							venta_tickes.ticketextorno,
							venta_tickes.feoriginal,
							venta_tickes.feextorno
					) AS ext ON(ext.fe2 = t.trans AND ext.fe3 = t.usr)
				WHERE 
					td 			= 'F' 
					AND tm 		= 'A'
					AND tipo 	= 'C'
					AND codigo 	= '11620307'
					AND es 		= '" . $almacen . "'
					AND t.usr != ''
					AND DATE(dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
				GROUP BY
					dia,
					ruc,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					t.usr,
					ext.fe1
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vcimpuesto'::text as DCUENTA, 
					''::text as codigo, 
					SUBSTR(TRIM(t.usr), 6)::text as trans,
					'1'::text as tip,  
					'D'::text as ddh, 
					-round(sum(igv),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal , 
					SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS ,
					'D'::text as tip2,
					'NA'::TEXT as doctype,
					SUBSTR(TRIM(ext.fe1), 0, 5)||'-'||SUBSTR(TRIM(ext.fe1), 6) AS documento_referencia,
					FIRST(ext.fe_referencia),
					SUM(ext.nu_igv_referencia),
					SUM(ext.nu_bi_referencia)
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
					LEFT JOIN
						(SELECT
							venta_tickes.feoriginal AS fe1,
							venta_tickes.ticketextorno AS fe2,
							venta_tickes.feextorno AS fe3,
							FIRST(venta_tickes.fe_referencia) AS fe_referencia,
							SUM(venta_tickes.nu_igv_referencia) AS nu_igv_referencia,
							SUM(venta_tickes.nu_bi_referencia) AS nu_bi_referencia
						FROM
						(SELECT 
							extorno.origen AS cadenaorigen,
							p.trans AS ticketoriginal,
							extorno.trans1 AS ticketextorno,
							p.usr AS feoriginal,
							extorno.usr AS feextorno,
							TO_CHAR(p.dia, 'DD/MM/YYYY') AS fe_referencia,
							p.igv AS nu_igv_referencia,
							(p.importe - p.igv) AS nu_bi_referencia
						FROM
							$postrans p
							INNER JOIN (
							SELECT 
								(dia || caja || trim(to_char(rendi_gln,'99999999'))) as registro,
								(dia || caja || trans) as origen,
								trans as trans1,
								usr
							FROM
								$postrans
							WHERE
								es 		= '" . $almacen . "'
								AND tm 	= 'A'
								AND td 	= 'F'
								AND usr != ''
								AND DATE(dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
								AND codigo = '11620307'
							) as extorno ON(dia || caja || trim(to_char(trans,'99999999'))) = extorno.registro
							AND es 	= '" . $almacen . "'
							AND tm = 'V'
							AND td = 'F'
							AND p.trans < extorno.trans1
							AND p.usr != ''
							AND p.codigo = '11620307'
							AND DATE(p.dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
						) AS venta_tickes
						GROUP BY
							venta_tickes.cadenaorigen,
							venta_tickes.ticketoriginal,
							venta_tickes.ticketextorno,
							venta_tickes.feoriginal,
							venta_tickes.feextorno
					) AS ext ON(ext.fe2 = t.trans AND ext.fe3 = t.usr)
				WHERE 
					td 			= 'F' 
					AND tm 		= 'A'
					AND tipo 	= 'C'
					AND codigo 	= '11620307'  
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
					AND es = '$almacen'
					AND t.usr != ''
				GROUP BY
					dia,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					t.usr,
					ext.fe1
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vventasglp'::text as DCUENTA, 
					codigo_concar, 
					SUBSTR(TRIM(t.usr), 6)::text as trans,
					'1'::text as tip, 
					'D'::text as ddh, 
					-(round(sum(importe),2)-round(sum(igv),2)) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal , 
					SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$cencosglp'::text as DCENCOS,
					'D'::text as tip2,
					'NA'::TEXT as doctype,
					SUBSTR(TRIM(ext.fe1), 0, 5)||'-'||SUBSTR(TRIM(ext.fe1), 6) AS documento_referencia,
					FIRST(ext.fe_referencia),
					SUM(ext.nu_igv_referencia),
					SUM(ext.nu_bi_referencia)
				FROM 
					$postrans t
					LEFT JOIN
						(SELECT
							venta_tickes.feoriginal AS fe1,
							venta_tickes.ticketextorno AS fe2,
							venta_tickes.feextorno AS fe3,
							FIRST(venta_tickes.fe_referencia) AS fe_referencia,
							SUM(venta_tickes.nu_igv_referencia) AS nu_igv_referencia,
							SUM(venta_tickes.nu_bi_referencia) AS nu_bi_referencia
						FROM
						(SELECT 
							extorno.origen AS cadenaorigen,
							p.trans AS ticketoriginal,
							extorno.trans1 AS ticketextorno,
							p.usr AS feoriginal,
							extorno.usr AS feextorno,
							TO_CHAR(p.dia, 'DD/MM/YYYY') AS fe_referencia,
							p.igv AS nu_igv_referencia,
							(p.importe - p.igv) AS nu_bi_referencia
						FROM
							$postrans p
							INNER JOIN (
							SELECT 
								(dia || caja || trim(to_char(rendi_gln,'99999999'))) as registro,
								(dia || caja || trans) as origen,
								trans as trans1,
								usr
							FROM
								$postrans
							WHERE
								es 		= '" . $almacen . "'
								AND tm 	= 'A'
								AND td 	= 'F'
								AND usr != ''
								AND DATE(dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
								AND codigo = '11620307'
							) as extorno ON(dia || caja || trim(to_char(trans,'99999999'))) = extorno.registro
							AND es 	= '" . $almacen . "'
							AND tm = 'V'
							AND td = 'F'
							AND p.trans < extorno.trans1
							AND p.usr != ''
							AND p.codigo = '11620307'
							AND DATE(p.dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
						) AS venta_tickes
						GROUP BY
							venta_tickes.cadenaorigen,
							venta_tickes.ticketoriginal,
							venta_tickes.ticketextorno,
							venta_tickes.feoriginal,
							venta_tickes.feextorno
					) AS ext ON(ext.fe2 = t.trans AND ext.fe3 = t.usr)
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal),
					interface_equivalencia_producto
				WHERE 
					td = 'F' 
					AND tm = 'A'
					AND tipo='C'   AND codigo='11620307' 
					AND date(dia) between '$FechaIni' AND '$FechaFin' 
					AND codigo=art_codigo 
					AND es = '$almacen'
					AND t.usr != ''
				GROUP BY
					dia,
					codigo_concar,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					t.usr,
					ext.fe1
			)
			UNION--EXTORNOS DE FACTURAS ELECTRONICAS - LIQUIDOS
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vclienteglp'::text as DCUENTA, 
					ruc::text as codigo, 
					SUBSTR(TRIM(t.usr), 6)::text as trans,
					'1'::text as tip,
					'H'::text as ddh,
					-round(sum(importe), 2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal , 
					SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS ,
					'D'::text as tip2,
					'NA'::TEXT as doctype,
					SUBSTR(TRIM(ext.fe1), 0, 5)||'-'||SUBSTR(TRIM(ext.fe1), 6) AS documento_referencia,
					FIRST(ext.fe_referencia),
					SUM(ext.nu_igv_referencia),
					SUM(ext.nu_bi_referencia)
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
					LEFT JOIN
						(SELECT
							venta_tickes.feoriginal AS fe1,
							venta_tickes.ticketextorno AS fe2,
							venta_tickes.feextorno AS fe3,
							FIRST(venta_tickes.fe_referencia) AS fe_referencia,
							SUM(venta_tickes.nu_igv_referencia) AS nu_igv_referencia,
							SUM(venta_tickes.nu_bi_referencia) AS nu_bi_referencia
						FROM
						(SELECT 
							extorno.origen AS cadenaorigen,
							p.trans AS ticketoriginal,
							extorno.trans1 AS ticketextorno,
							p.usr AS feoriginal,
							extorno.usr AS feextorno,
							TO_CHAR(p.dia, 'DD/MM/YYYY') AS fe_referencia,
							p.igv AS nu_igv_referencia,
							(p.importe - p.igv) AS nu_bi_referencia
						FROM
							$postrans p
							INNER JOIN (
							SELECT 
								(dia || caja || trim(to_char(rendi_gln,'99999999'))) as registro,
								(dia || caja || trans) as origen,
								trans as trans1,
								usr
							FROM
								$postrans
							WHERE
								es 		= '" . $almacen . "'
								AND tm 	= 'A'
								AND td 	= 'F'
								AND usr != ''
								AND DATE(dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
								AND codigo != '11620307'
							) as extorno ON(dia || caja || trim(to_char(trans,'99999999'))) = extorno.registro
							AND es 	= '" . $almacen . "'
							AND tm = 'V'
							AND td = 'F'
							AND p.trans < extorno.trans1
							AND p.usr != ''
							AND p.codigo != '11620307'
							AND DATE(p.dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
						) AS venta_tickes
						GROUP BY
							venta_tickes.cadenaorigen,
							venta_tickes.ticketoriginal,
							venta_tickes.ticketextorno,
							venta_tickes.feoriginal,
							venta_tickes.feextorno
					) AS ext ON(ext.fe2 = t.trans AND ext.fe3 = t.usr)
				WHERE 
					td 			= 'F' 
					AND tm 		= 'A'
					AND tipo 	= 'C'
					AND codigo != '11620307'  
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND es = '$almacen'
					AND t.usr != ''
				GROUP BY
					dia,
					ruc,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					t.usr,
					ext.fe1
			)
			
			UNION 
			
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vcimpuesto'::text as DCUENTA, 
					''::text as codigo, 
					SUBSTR(TRIM(t.usr), 6)::text as trans,
					'1'::text as tip,  
					'D'::text as ddh, 
					-round(sum(igv),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal , 
					SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS ,
					'D'::text as tip2,
					'NA'::TEXT as doctype,
					SUBSTR(TRIM(ext.fe1), 0, 5)||'-'||SUBSTR(TRIM(ext.fe1), 6) AS documento_referencia,
					FIRST(ext.fe_referencia),
					SUM(ext.nu_igv_referencia),
					SUM(ext.nu_bi_referencia)
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
					LEFT JOIN
						(SELECT
							venta_tickes.feoriginal AS fe1,
							venta_tickes.ticketextorno AS fe2,
							venta_tickes.feextorno AS fe3,
							FIRST(venta_tickes.fe_referencia) AS fe_referencia,
							SUM(venta_tickes.nu_igv_referencia) AS nu_igv_referencia,
							SUM(venta_tickes.nu_bi_referencia) AS nu_bi_referencia
						FROM
						(SELECT 
							extorno.origen AS cadenaorigen,
							p.trans AS ticketoriginal,
							extorno.trans1 AS ticketextorno,
							p.usr AS feoriginal,
							extorno.usr AS feextorno,
							TO_CHAR(p.dia, 'DD/MM/YYYY') AS fe_referencia,
							p.igv AS nu_igv_referencia,
							(p.importe - p.igv) AS nu_bi_referencia
						FROM
							$postrans p
							INNER JOIN (
							SELECT 
								(dia || caja || trim(to_char(rendi_gln,'99999999'))) as registro,
								(dia || caja || trans) as origen,
								trans as trans1,
								usr
							FROM
								$postrans
							WHERE
								es 		= '" . $almacen . "'
								AND tm 	= 'A'
								AND td 	= 'F'
								AND usr != ''
								AND DATE(dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
								AND codigo != '11620307'
							) as extorno ON(dia || caja || trim(to_char(trans,'99999999'))) = extorno.registro
							AND es 	= '" . $almacen . "'
							AND tm = 'V'
							AND td = 'F'
							AND p.trans < extorno.trans1
							AND p.usr != ''
							AND p.codigo != '11620307'
							AND DATE(p.dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
						) AS venta_tickes
						GROUP BY
							venta_tickes.cadenaorigen,
							venta_tickes.ticketoriginal,
							venta_tickes.ticketextorno,
							venta_tickes.feoriginal,
							venta_tickes.feextorno
					) AS ext ON(ext.fe2 = t.trans AND ext.fe3 = t.usr)
				WHERE 
					td 			= 'F' 
					AND tm 		= 'A'
					AND tipo 	= 'C'
					AND codigo != '11620307'  
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
					AND es = '$almacen'
					AND t.usr != ''
				GROUP BY
					dia,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					t.usr,
					ext.fe1
			)
			
			UNION 
			
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vventasglp'::text as DCUENTA, 
					codigo_concar, 
					SUBSTR(TRIM(t.usr), 6)::text as trans,
					'1'::text as tip, 
					'D'::text as ddh, 
					-(round(sum(importe),2)-round(sum(igv),2)) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal , 
					SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$cencosglp'::text as DCENCOS ,
					'D'::text as tip2,
					'NA'::TEXT as doctype,
					SUBSTR(TRIM(ext.fe1), 0, 5)||'-'||SUBSTR(TRIM(ext.fe1), 6) AS documento_referencia,
					FIRST(ext.fe_referencia),
					SUM(ext.nu_igv_referencia),
					SUM(ext.nu_bi_referencia)
				FROM 
					$postrans t
					LEFT JOIN
						(SELECT
							venta_tickes.feoriginal AS fe1,
							venta_tickes.ticketextorno AS fe2,
							venta_tickes.feextorno AS fe3,
							FIRST(venta_tickes.fe_referencia) AS fe_referencia,
							SUM(venta_tickes.nu_igv_referencia) AS nu_igv_referencia,
							SUM(venta_tickes.nu_bi_referencia) AS nu_bi_referencia
						FROM
						(SELECT 
							extorno.origen AS cadenaorigen,
							p.trans AS ticketoriginal,
							extorno.trans1 AS ticketextorno,
							p.usr AS feoriginal,
							extorno.usr AS feextorno,
							TO_CHAR(p.dia, 'DD/MM/YYYY') AS fe_referencia,
							p.igv AS nu_igv_referencia,
							(p.importe - p.igv) AS nu_bi_referencia
						FROM
							$postrans p
							INNER JOIN (
							SELECT 
								(dia || caja || trim(to_char(rendi_gln,'99999999'))) as registro,
								(dia || caja || trans) as origen,
								trans as trans1,
								usr
							FROM
								$postrans
							WHERE
								es 		= '" . $almacen . "'
								AND tm 	= 'A'
								AND td 	= 'F'
								AND usr != ''
								AND DATE(dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
								AND codigo != '11620307'
							) as extorno ON(dia || caja || trim(to_char(trans,'99999999'))) = extorno.registro
							AND es 	= '" . $almacen . "'
							AND tm = 'V'
							AND td = 'F'
							AND p.trans < extorno.trans1
							AND p.usr != ''
							AND p.codigo != '11620307'
							AND DATE(p.dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
						) AS venta_tickes
						GROUP BY
							venta_tickes.cadenaorigen,
							venta_tickes.ticketoriginal,
							venta_tickes.ticketextorno,
							venta_tickes.feoriginal,
							venta_tickes.feextorno
					) AS ext ON(ext.fe2 = t.trans AND ext.fe3 = t.usr)
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal),
					interface_equivalencia_producto
				WHERE 
					td 			= 'F' 
					AND tm 		= 'A'
					AND tipo 	= 'C'
					AND codigo != '11620307' 
					AND date(dia) between '$FechaIni' AND '$FechaFin' 
					AND codigo = art_codigo 
					AND es = '$almacen'
					AND t.usr != ''
				GROUP BY
					dia,
					codigo_concar,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					t.usr,
					ext.fe1
			)
UNION--EXTORNOS DE BOLETAS ELECTRÓNICAS GLP TOTAL
(SELECT
 to_char(date(dia),'YYMMDD') as dia, 
 '$vclienteglp'::text as DCUENTA, 
 '$cod_cliente'::text as codigo, 
 SUBSTR(TRIM(t.usr), 6)::text as trans,
 '1'::text as tip,
 'H'::text as ddh,
 -round(sum(importe), 2) as importe,
 cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
 es as sucursal,
 SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
 '$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
 ''::text as DCENCOS,
 'D'::text as tip2,
 'NA'::TEXT as doctype,
 SUBSTR(TRIM(ext.fe1), 0, 5)||'-'||SUBSTR(TRIM(ext.fe1), 6) AS documento_referencia,
 FIRST(ext.fe_referencia),
 SUM(ext.nu_igv_referencia),
 SUM(ext.nu_bi_referencia)
FROM 
 $postrans AS t
 LEFT JOIN
	(SELECT
		venta_tickes.feoriginal AS fe1,
		venta_tickes.ticketextorno AS fe2,
		venta_tickes.feextorno AS fe3,
		FIRST(venta_tickes.fe_referencia) AS fe_referencia,
		SUM(venta_tickes.nu_igv_referencia) AS nu_igv_referencia,
		SUM(venta_tickes.nu_bi_referencia) AS nu_bi_referencia
	FROM
	(SELECT 
		extorno.origen AS cadenaorigen,
		p.trans AS ticketoriginal,
		extorno.trans1 AS ticketextorno,
		p.usr AS feoriginal,
		extorno.usr AS feextorno,
		TO_CHAR(p.dia, 'DD/MM/YYYY') AS fe_referencia,
		p.igv AS nu_igv_referencia,
		(p.importe - p.igv) AS nu_bi_referencia
	FROM
		$postrans p
		INNER JOIN (
		SELECT 
			(dia || caja || trim(to_char(rendi_gln,'99999999'))) as registro,
			(dia || caja || trans) as origen,
			trans as trans1,
			usr
		FROM
			$postrans
		WHERE
			es 		= '" . $almacen . "'
			AND tm 	= 'A'
			AND td 	= 'B'
			AND usr != ''
			AND DATE(dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
			AND codigo = '11620307'
		) as extorno ON(dia || caja || trim(to_char(trans,'99999999'))) = extorno.registro
		AND es 	= '" . $almacen . "'
		AND tm = 'V'
		AND td = 'B'
		AND p.trans < extorno.trans1
		AND p.usr != ''
		AND p.codigo = '11620307'
		AND DATE(p.dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
	) AS venta_tickes
	GROUP BY
		venta_tickes.cadenaorigen,
		venta_tickes.ticketoriginal,
		venta_tickes.ticketextorno,
		venta_tickes.feoriginal,
		venta_tickes.feextorno
 ) AS ext ON(ext.fe2 = t.trans AND ext.fe3 = t.usr)
 LEFT JOIN pos_z_cierres AS cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
WHERE 
 td = 'B'
 AND tm = 'A'
 AND tipo = 'C'
 AND codigo = '11620307'
 AND date(dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
 AND es = '" . $almacen . "'
 AND t.usr != ''
GROUP BY
 dia,
 ruc,
 cfp.nu_posz_z_serie,
 es,
 cfp.ch_posz_pos,
 t.usr,
 ext.fe1
)
UNION--EXTORNOS DE BOLETAS ELECTRÓNICAS GLP IGV
(SELECT 
 to_char(date(dia),'YYMMDD') as dia, 
 '$vcimpuesto'::text as DCUENTA, 
 ''::text as codigo, 
 SUBSTR(TRIM(t.usr), 6)::text as trans,
 '1'::text as tip,  
 'D'::text as ddh, 
 -round(sum(igv),2) as importe,
 cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
 es as sucursal,
 SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
 '$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
 ''::text as DCENCOS,
 'D'::text as tip2,
 'NA'::TEXT as doctype,
 SUBSTR(TRIM(ext.fe1), 0, 5)||'-'||SUBSTR(TRIM(ext.fe1), 6) AS documento_referencia,
 FIRST(ext.fe_referencia),
 SUM(ext.nu_igv_referencia),
 SUM(ext.nu_bi_referencia)
FROM 
 $postrans AS t
 LEFT JOIN
	(SELECT
		venta_tickes.feoriginal AS fe1,
		venta_tickes.ticketextorno AS fe2,
		venta_tickes.feextorno AS fe3,
		FIRST(venta_tickes.fe_referencia) AS fe_referencia,
		SUM(venta_tickes.nu_igv_referencia) AS nu_igv_referencia,
		SUM(venta_tickes.nu_bi_referencia) AS nu_bi_referencia
	FROM
	(SELECT 
		extorno.origen AS cadenaorigen,
		p.trans AS ticketoriginal,
		extorno.trans1 AS ticketextorno,
		p.usr AS feoriginal,
		extorno.usr AS feextorno,
		TO_CHAR(p.dia, 'DD/MM/YYYY') AS fe_referencia,
		p.igv AS nu_igv_referencia,
		(p.importe - p.igv) AS nu_bi_referencia
	FROM
		$postrans p
		INNER JOIN (
		SELECT 
			(dia || caja || trim(to_char(rendi_gln,'99999999'))) as registro,
			(dia || caja || trans) as origen,
			trans as trans1,
			usr
		FROM
			$postrans
		WHERE
			es 		= '" . $almacen . "'
			AND tm 	= 'A'
			AND td 	= 'B'
			AND usr != ''
			AND DATE(dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
			AND codigo = '11620307'
		) as extorno ON(dia || caja || trim(to_char(trans,'99999999'))) = extorno.registro
		AND es 	= '" . $almacen . "'
		AND tm = 'V'
		AND td = 'B'
		AND p.trans < extorno.trans1
		AND p.usr != ''
		AND p.codigo = '11620307'
		AND DATE(p.dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
	) AS venta_tickes
	GROUP BY
		venta_tickes.cadenaorigen,
		venta_tickes.ticketoriginal,
		venta_tickes.ticketextorno,
		venta_tickes.feoriginal,
		venta_tickes.feextorno
 ) AS ext ON(ext.fe2 = t.trans AND ext.fe3 = t.usr)
 LEFT JOIN pos_z_cierres AS cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
WHERE 
 td = 'B'
 AND tm = 'A'
 AND tipo = 'C'
 AND codigo = '11620307'
 AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
 AND es = '$almacen'
 AND t.usr != ''
GROUP BY
 dia,
 cfp.nu_posz_z_serie,
 es,
 cfp.ch_posz_pos,
 t.usr,
 ext.fe1
)
UNION--EXTORNOS DE BOLETAS ELECTRÓNICAS GLP SUBTOTAL
(SELECT 
 to_char(date(dia),'YYMMDD') as dia, 
 '$vventasglp'::text as DCUENTA, 
 codigo_concar, 
 SUBSTR(TRIM(t.usr), 6)::text as trans,
 '1'::text as tip, 
 'D'::text as ddh, 
 -(round(sum(importe),2)-round(sum(igv),2)) as importe,
 cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
 es as sucursal,
 SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
 '$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
 '$cencosglp'::text as DCENCOS,
 'D'::text as tip2,
 'NA'::TEXT as doctype,
 SUBSTR(TRIM(ext.fe1), 0, 5)||'-'||SUBSTR(TRIM(ext.fe1), 6) AS documento_referencia,
 FIRST(ext.fe_referencia),
 SUM(ext.nu_igv_referencia),
 SUM(ext.nu_bi_referencia)
FROM 
 $postrans AS t
 LEFT JOIN
	(SELECT
		venta_tickes.feoriginal AS fe1,
		venta_tickes.ticketextorno AS fe2,
		venta_tickes.feextorno AS fe3,
		FIRST(venta_tickes.fe_referencia) AS fe_referencia,
		SUM(venta_tickes.nu_igv_referencia) AS nu_igv_referencia,
		SUM(venta_tickes.nu_bi_referencia) AS nu_bi_referencia
	FROM
	(SELECT 
		extorno.origen AS cadenaorigen,
		p.trans AS ticketoriginal,
		extorno.trans1 AS ticketextorno,
		p.usr AS feoriginal,
		extorno.usr AS feextorno,
		TO_CHAR(p.dia, 'DD/MM/YYYY') AS fe_referencia,
		p.igv AS nu_igv_referencia,
		(p.importe - p.igv) AS nu_bi_referencia
	FROM
		$postrans p
		INNER JOIN (
		SELECT 
			(dia || caja || trim(to_char(rendi_gln,'99999999'))) as registro,
			(dia || caja || trans) as origen,
			trans as trans1,
			usr
		FROM
			$postrans
		WHERE
			es 		= '" . $almacen . "'
			AND tm 	= 'A'
			AND td 	= 'B'
			AND usr != ''
			AND DATE(dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
			AND codigo = '11620307'
		) as extorno ON(dia || caja || trim(to_char(trans,'99999999'))) = extorno.registro
		AND es 	= '" . $almacen . "'
		AND tm = 'V'
		AND td = 'B'
		AND p.trans < extorno.trans1
		AND p.usr != ''
		AND p.codigo = '11620307'
		AND DATE(p.dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
	) AS venta_tickes
	GROUP BY
		venta_tickes.cadenaorigen,
		venta_tickes.ticketoriginal,
		venta_tickes.ticketextorno,
		venta_tickes.feoriginal,
		venta_tickes.feextorno
 ) AS ext ON(ext.fe2 = t.trans AND ext.fe3 = t.usr)
 LEFT JOIN pos_z_cierres AS cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal),
 interface_equivalencia_producto
WHERE 
 td = 'B'
 AND tm = 'A'
 AND tipo = 'C'
 AND codigo = '11620307'
 AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
 AND codigo = art_codigo 
 AND es = '$almacen'
 AND t.usr != ''
GROUP BY
 dia,
 codigo_concar,
 cfp.nu_posz_z_serie,
 es,
 cfp.ch_posz_pos,
 t.usr,
 ext.fe1
)
UNION--EXTORNOS DE BOLETAS ELECTRÓNICAS LIQUIDOS TOTAL
(SELECT
 to_char(date(dia),'YYMMDD') as dia, 
 '$vclienteglp'::text as DCUENTA,
 '$cod_cliente'::text as codigo, 
 SUBSTR(TRIM(t.usr), 6)::text as trans,
 '1'::text as tip,
 'H'::text as ddh,
 -round(sum(importe), 2) as importe,
 cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
 es as sucursal,
 SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
 '$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
 ''::text as DCENCOS,
 'D'::text as tip2,
 'NA'::TEXT as doctype,
 SUBSTR(TRIM(ext.fe1), 0, 5)||'-'||SUBSTR(TRIM(ext.fe1), 6) AS documento_referencia,
 FIRST(ext.fe_referencia),
 SUM(ext.nu_igv_referencia),
 SUM(ext.nu_bi_referencia)
FROM 
 $postrans AS t
 LEFT JOIN
	(SELECT
		venta_tickes.feoriginal AS fe1,
		venta_tickes.ticketextorno AS fe2,
		venta_tickes.feextorno AS fe3,
		FIRST(venta_tickes.fe_referencia) AS fe_referencia,
		SUM(venta_tickes.nu_igv_referencia) AS nu_igv_referencia,
		SUM(venta_tickes.nu_bi_referencia) AS nu_bi_referencia
	FROM
	(SELECT 
		extorno.origen AS cadenaorigen,
		p.trans AS ticketoriginal,
		extorno.trans1 AS ticketextorno,
		p.usr AS feoriginal,
		extorno.usr AS feextorno,
		TO_CHAR(p.dia, 'DD/MM/YYYY') AS fe_referencia,
		p.igv AS nu_igv_referencia,
		(p.importe - p.igv) AS nu_bi_referencia
	FROM
		$postrans p
		INNER JOIN (
		SELECT 
			(dia || caja || trim(to_char(rendi_gln,'99999999'))) as registro,
			(dia || caja || trans) as origen,
			trans as trans1,
			usr
		FROM
			$postrans
		WHERE
			es 		= '" . $almacen . "'
			AND tm 	= 'A'
			AND td 	= 'B'
			AND usr != ''
			AND DATE(dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
			AND codigo != '11620307'
		) as extorno ON(dia || caja || trim(to_char(trans,'99999999'))) = extorno.registro
		AND es 	= '" . $almacen . "'
		AND tm = 'V'
		AND td = 'B'
		AND p.trans < extorno.trans1
		AND p.usr != ''
		AND p.codigo != '11620307'
		AND DATE(p.dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
	) AS venta_tickes
	GROUP BY
		venta_tickes.cadenaorigen,
		venta_tickes.ticketoriginal,
		venta_tickes.ticketextorno,
		venta_tickes.feoriginal,
		venta_tickes.feextorno
 ) AS ext ON(ext.fe2 = t.trans AND ext.fe3 = t.usr)
 LEFT JOIN pos_z_cierres AS cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
WHERE 
 td = 'B'
 AND tm = 'A'
 AND tipo = 'C'
 AND codigo != '11620307'
 AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
 AND es = '$almacen'
 AND t.usr != ''
GROUP BY
 dia,
 ruc,
 cfp.nu_posz_z_serie,
 es,
 cfp.ch_posz_pos,
 t.usr,
 ext.fe1
)
UNION--EXTORNOS DE BOLETAS ELECTRÓNICAS LIQUIDOS IGV	
(SELECT
 to_char(date(dia),'YYMMDD') as dia, 
 '$vcimpuesto'::text as DCUENTA, 
 ''::text as codigo, 
 SUBSTR(TRIM(t.usr), 6)::text as trans,
 '1'::text as tip,  
 'D'::text as ddh, 
 -round(sum(igv),2) as importe,
 cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
 es as sucursal,
 SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
 '$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
 ''::text as DCENCOS,
 'D'::text as tip2,
 'NA'::TEXT as doctype,
 SUBSTR(TRIM(ext.fe1), 0, 5)||'-'||SUBSTR(TRIM(ext.fe1), 6) AS documento_referencia,
 FIRST(ext.fe_referencia),
 SUM(ext.nu_igv_referencia),
 SUM(ext.nu_bi_referencia)
FROM 
 $postrans AS t
 LEFT JOIN
	(SELECT
		venta_tickes.feoriginal AS fe1,
		venta_tickes.ticketextorno AS fe2,
		venta_tickes.feextorno AS fe3,
		FIRST(venta_tickes.fe_referencia) AS fe_referencia,
		SUM(venta_tickes.nu_igv_referencia) AS nu_igv_referencia,
		SUM(venta_tickes.nu_bi_referencia) AS nu_bi_referencia
	FROM
	(SELECT 
		extorno.origen AS cadenaorigen,
		p.trans AS ticketoriginal,
		extorno.trans1 AS ticketextorno,
		p.usr AS feoriginal,
		extorno.usr AS feextorno,
		TO_CHAR(p.dia, 'DD/MM/YYYY') AS fe_referencia,
		p.igv AS nu_igv_referencia,
		(p.importe - p.igv) AS nu_bi_referencia
	FROM
		$postrans p
		INNER JOIN (
		SELECT 
			(dia || caja || trim(to_char(rendi_gln,'99999999'))) as registro,
			(dia || caja || trans) as origen,
			trans as trans1,
			usr
		FROM
			$postrans
		WHERE
			es 		= '" . $almacen . "'
			AND tm 	= 'A'
			AND td 	= 'B'
			AND usr != ''
			AND DATE(dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
			AND codigo != '11620307'
		) as extorno ON(dia || caja || trim(to_char(trans,'99999999'))) = extorno.registro
		AND es 	= '" . $almacen . "'
		AND tm = 'V'
		AND td = 'B'
		AND p.trans < extorno.trans1
		AND p.usr != ''
		AND p.codigo != '11620307'
		AND DATE(p.dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
	) AS venta_tickes
	GROUP BY
		venta_tickes.cadenaorigen,
		venta_tickes.ticketoriginal,
		venta_tickes.ticketextorno,
		venta_tickes.feoriginal,
		venta_tickes.feextorno
 ) AS ext ON(ext.fe2 = t.trans AND ext.fe3 = t.usr)
 LEFT JOIN pos_z_cierres AS cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
WHERE 
 td = 'B'
 AND tm = 'A'
 AND tipo = 'C'
 AND codigo != '11620307'
 AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
 AND es = '$almacen'
 AND t.usr != ''
GROUP BY
 dia,
 cfp.nu_posz_z_serie,
 es,
 cfp.ch_posz_pos,
 t.usr,
 ext.fe1
)
UNION--EXTORNOS DE BOLETAS ELECTRÓNICAS LIQUIDOS SUBTOTAL
(SELECT 
 to_char(date(dia),'YYMMDD') as dia, 
 '$vventasglp'::text as DCUENTA, 
 codigo_concar, 
 SUBSTR(TRIM(t.usr), 6)::text as trans,
 '1'::text as tip, 
 'D'::text as ddh, 
 -(round(sum(importe),2)-round(sum(igv),2)) as importe,
 cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
 es as sucursal,
 SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
 '$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
 '$cencosglp'::text as DCENCOS,
 'D'::text as tip2,
 'NA'::TEXT as doctype,
 SUBSTR(TRIM(ext.fe1), 0, 5)||'-'||SUBSTR(TRIM(ext.fe1), 6) AS documento_referencia,
 FIRST(ext.fe_referencia),
 SUM(ext.nu_igv_referencia),
 SUM(ext.nu_bi_referencia)
FROM 
 $postrans AS t
 LEFT JOIN
	(SELECT
		venta_tickes.feoriginal AS fe1,
		venta_tickes.ticketextorno AS fe2,
		venta_tickes.feextorno AS fe3,
		FIRST(venta_tickes.fe_referencia) AS fe_referencia,
		SUM(venta_tickes.nu_igv_referencia) AS nu_igv_referencia,
		SUM(venta_tickes.nu_bi_referencia) AS nu_bi_referencia
	FROM
	(SELECT 
		extorno.origen AS cadenaorigen,
		p.trans AS ticketoriginal,
		extorno.trans1 AS ticketextorno,
		p.usr AS feoriginal,
		extorno.usr AS feextorno,
		TO_CHAR(p.dia, 'DD/MM/YYYY') AS fe_referencia,
		p.igv AS nu_igv_referencia,
		(p.importe - p.igv) AS nu_bi_referencia
	FROM
		$postrans p
		INNER JOIN (
		SELECT 
			(dia || caja || trim(to_char(rendi_gln,'99999999'))) as registro,
			(dia || caja || trans) as origen,
			trans as trans1,
			usr
		FROM
			$postrans
		WHERE
			es 		= '" . $almacen . "'
			AND tm 	= 'A'
			AND td 	= 'B'
			AND usr != ''
			AND DATE(dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
			AND codigo != '11620307'
		) as extorno ON(dia || caja || trim(to_char(trans,'99999999'))) = extorno.registro
		AND es 	= '" . $almacen . "'
		AND tm = 'V'
		AND td = 'B'
		AND p.trans < extorno.trans1
		AND p.usr != ''
		AND p.codigo != '11620307'
		AND DATE(p.dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
	) AS venta_tickes
	GROUP BY
		venta_tickes.cadenaorigen,
		venta_tickes.ticketoriginal,
		venta_tickes.ticketextorno,
		venta_tickes.feoriginal,
		venta_tickes.feextorno
 ) AS ext ON(ext.fe2 = t.trans AND ext.fe3 = t.usr)
 LEFT JOIN pos_z_cierres AS cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal),
 interface_equivalencia_producto
WHERE 
 td = 'B'
 AND tm = 'A'
 AND tipo = 'C'
 AND codigo != '11620307'
 AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
 AND codigo = art_codigo
 AND es = '$almacen'
 AND t.usr != ''
GROUP BY
 dia,
 codigo_concar,
 cfp.nu_posz_z_serie,
 es,
 cfp.ch_posz_pos,
 t.usr,
 ext.fe1
)
			ORDER BY dia, venta, tip2, tip, trans, DCUENTA, ddh;";
		
		echo "<pre>";
		echo "VENTAS COMBUSTIBLE: \n\n".$sql."\n\n";	
		echo "</pre>";
		
		$q1 = "CREATE TABLE tmp_concar (dsubdia character varying(7), dcompro character varying(6), dsecue character varying(7), dfeccom character varying(6), dcuenta character varying(12), dcodane character varying(18),
			dcencos character varying(6), dcodmon character varying(2), ddh character varying(1), dimport numeric(14,2), dtipdoc character varying(2), dnumdoc character varying(30), 
			dfecdoc character varying(8), dfecven character varying(8), darea character varying(3), dflag character varying(1), ddate date not null, dxglosa character varying(40),
			dusimport numeric(14,2), dmnimpor numeric(14,2), dcodarc character varying(2), dfeccom2 character varying(8), dfecdoc2 character varying(8), dvanexo character varying(1), dcodane2 character varying(18), documento_referencia character varying(15) NULL, fe_referencia DATE NULL, nu_igv_referencia numeric(20,4), nu_bi_referencia numeric(20,4));";

		$sqlca->query($q1);

		$data = array();
		$correlativo = 0;
		$contador = '0000';  
		$k = 0; 		
	  
		echo "<script>console.log('" . json_encode( array($subdia) ) . "')</script>";
		echo "<script>console.log('" . json_encode( array($opcion) ) . "')</script>";
		echo "<script>console.log('" . json_encode( array($vccliente) ) . "')</script>";
		if ($sqlca->query($sql)>0) {
			$correlativo = ($FechaDiv[1] * 10000) + $num_actual;			
			while ($reg = $sqlca->fetchRow()) {		
				$data[] = $reg;		
	
				//reiniciar el numerador cuando sea un dia diferente
				if($subdia != $reg[10]){
					$correlativo = 0;
					$correlativo = ($FechaDiv[1] * 10000) + $num_actual;
					$subdia = $reg[10];
				}

				// validando si subdiario se toma completo ($opcion=0) o con el día ($opcion=1)
				if($opcion==0) {
					$reg[10]=substr($reg[10],0,-2);
				}
															
				// if ((substr($reg[1],0,3) == substr($vccliente,0,3)) && $reg[12] != "C") { 
				if (substr($reg[1],0,3) == substr($vccliente,0,3)) { 
					$k=1;

					$correlativo = $correlativo + 1;
					if($FechaDiv[1]>=1 && $FechaDiv[1]<10){
						$correlativo2 = "0".$correlativo;
					}else {
						$correlativo2 = $correlativo;
					}
				} else {
					$k = $k+1;					
				}
				if(trim($reg[9]) == ''){
					$reg[9] = $xtradat;
				} else {
					$xtradat = trim($reg[9]);
				}			
				if($k<=9){
					$contador = "000".$k;
				}elseif($k>9 and $k<=99){
					$contador = "00".$k;
				} else {
					$contador = "0".$k;
				}

				$dfecdoc = "20".substr($reg[0],0,2).substr($reg[0],2,2).substr($reg[0],4,2);				
			
				if(empty($reg[15]))
					$fe_referencia = '01/01/1999';
				else
					$fe_referencia = trim($reg[15]);

				$q2 = $sqlca->query("INSERT INTO tmp_concar VALUES ('".trim($reg[10])."', '".trim($correlativo2)."', '".trim($contador)."', '".trim($reg[0])."', '".trim($reg[1])."', '".trim($reg[2])."', '".trim($reg[11])."', 'MN', '".trim($reg[5])."', '".trim($reg[6])."', '".trim($reg[13])."', '".trim($reg[9])."', '".trim($reg[0])."', '".trim($reg[0])."', 'S', 'S', '".$dfecdoc."', '".trim($reg[7])."', '0', '".trim($reg[6])."', 'X', '".$dfecdoc."', '".$dfecdoc."', 'P', ' ', '" . trim($reg[14]) . "', '" . $fe_referencia . "', " . trim($reg[16]) . ", " . trim($reg[17]) . ");", "concar_insert");	
			}
		}
		echo "<script>console.log('****** Etapa 1 ******')</script>";
		echo "<script>console.log('" . json_encode($data) . "')</script>";

		/* Verificar data */
		// $dataTmpConcar = array();
		// $que = "SELECT * FROM tmp_concar ORDER BY dsubdia, dcompro, dcuenta, dsecue;";
		// if ($sqlca->query($que)>0){
		// 	while ($reg = $sqlca->fetchRow()){
		// 		$dataTmpConcar[] = $reg;
		// 	}			
		// }
		sleep(2);
		echo "<script>console.log('****** Etapa 2 ******')</script>";
		// echo "<script>console.log('" . json_encode($dataTmpConcar) . "')</script>";
		/* Fin verificar data */

		// creando el vector de diferencia
		$c = 0;
		$imp = 0;
		$flag = 0;
		$que = "SELECT * FROM tmp_concar ORDER BY dsubdia, dcompro, dcuenta, dsecue;";
		if ($sqlca->query($que)>0){
			while ($reg = $sqlca->fetchRow()){
				if (substr($reg[4],0,3) == substr($vccliente,0,3)){
					if ($flag == 1) {
						$vec[$c] = $imp;
						$c = $c + 1;
					}
					$imp = trim($reg[9]);
				} else {
					$imp = round(($imp-$reg[9]), 2);
					$flag = 1;
				}
			}
			$vec[$c] = 0;
		}

		// actualizar tabla tmp_concar sumando las diferencias al igv13818
		$k = 0;
		if ($sqlca->query($que)>0){
			while ($reg = $sqlca->fetchRow()){
				if (trim($reg[4] == $vcimpuesto)){
					$dif = $reg[9] + $vec[$k];
					$k = $k + 1;
					$sale = $sqlca->query("UPDATE tmp_concar SET dimport = ".$dif." WHERE dcompro = '".trim($reg[1])."' AND dcuenta='$vcimpuesto' and trim(dcodane)='' AND dsubdia = '".trim($reg[0])."';", "queryaux1"); // antes: dcodane='99999999999', con ultimo cambio : dcodane=''
				}
			}
		}

		// pasando la nueva tabla a texto2
		$qfinal = "SELECT * FROM tmp_concar ORDER BY dsubdia, dcompro, dcuenta, dsecue; ";					
		$arrInserts = Array();
		$pd = 0; 

		$fe_emision_referencia = "''";
		$no_tipo_documento_referencia = '';

		if ($sqlca->query($qfinal)>0) {
			while ($reg = $sqlca->fetchRow()){
				$sNumeroCuenta = substr($reg['dcuenta'], 0, 2);
				//if($reg['dcuenta'] == '121203'){
				if ( $sNumeroCuenta == '12' ) {
					if(!empty($reg['documento_referencia'])){
						if(substr($reg['documento_referencia'],0,1) == 'F')
							$no_tipo_documento_referencia = 'FT';
						else if (substr($reg['documento_referencia'],0,1) == 'B')
							$no_tipo_documento_referencia = 'BV';
					}else{
						$no_tipo_documento_referencia = '';
					}
					if($reg['fe_referencia'] == '1999-01-01')
						$fe_emision_referencia = "''";
					else
						$fe_emision_referencia = "convert(datetime, '".substr($reg['fe_referencia'],0,19)."', 120)";
				}

				$ins = "INSERT INTO $TabSqlDet (
						dsubdia, dcompro, dsecue, dfeccom, dcuenta, dcodane, dcencos, dcodmon, ddh, dimport, dtipdoc, dnumdoc, dfecdoc,dfecven, darea, dflag, dxglosa, dusimpor, dmnimpor, dcodarc, dcodane2, dtipcam, dmoncom, dcolcom, dbascom, dinacom, digvcom, dtpconv, dflgcom, danecom, dtipaco, dmedpag, dtidref, dndoref, dfecref, dbimref, digvref, dtipdor, dnumdor, dfecdo2
						) VALUES (
						'".$reg[0]."', '".$reg[1]."', '".$reg[2]."', '".$reg[3]."', '".$reg[4]."', '".$reg[5]."', '".$reg[6]."', '".$reg[7]."', '".$reg[8]."', ".$reg[9].", '".$reg[10]."', '".$reg[11]."', '".$reg[13]."', '".$reg[13]."', '', '".$reg[15]."', '".$reg[17]."', 0,0,'','',0,'','',0,0,0,'','','','','','" . $no_tipo_documento_referencia . "', '" . $reg['documento_referencia'] . "' , $fe_emision_referencia, " . $reg['nu_bi_referencia'] . ", " . $reg['nu_igv_referencia'] . ",'','', GETDATE());
				";
				$arrInserts['tipo']['detalle'][$pd] = $ins;
				$pd++;						
			}
		}

		//+++++++++++++++++++++ Cabecera de Ventas PLAYA +++++++++++++++++++++
		
		$correlativo=0;		
		$pc = 0;
			
		if ($sqlca->query($sql)>0) {
			$correlativo = ($FechaDiv[1] * 10000) + $num_actual;
			while ($reg = $sqlca->fetchRow()) {
	
				//reiniciar el numerador cuando sea un dia diferente
				if($subdia != $reg[10]){
					$correlativo = 0;
					$correlativo = ($FechaDiv[1] * 10000) + $num_actual;
					$subdia = $reg[10];
				}

				if (substr($reg[1],0,3) == substr($vccliente,0,3)) { 					
					$correlativo = $correlativo + 1;
					if($FechaDiv[1]>=01 && $FechaDiv[1]<10){
						$correlativo2 = "0".$correlativo;
					}else {
						$correlativo2 = $correlativo;
					}
					$dfecdoc = "20".substr($reg[0],0,2).substr($reg[0],2,2).substr($reg[0],4,2);	
					$cabglosa = substr($reg[0],4,2)."-".substr($reg[0],2,2)."-".substr($reg[0],0,2);

					if($opcion==0) {
						$reg[10]=substr($reg[10],0,-2);
					}

					$ins = "INSERT INTO $TabSqlCab
						(	CSUBDIA, CCOMPRO, CFECCOM, CCODMON, CSITUA, CTIPCAM, CGLOSA, CTOTAL, CTIPO, CFLAG, CFECCAM, CORIG, CFORM, CTIPCOM, CEXTOR
						) VALUES (
	       						'".$reg[10]."', '".$correlativo2."', '".$reg[0]."', 'MN', '', 0, '".$reg[7]."', '".$reg[6]."', 'V', 'S', '', '','','',''
	       					);";
					$arrInserts['tipo']['cabecera'][$pc] = $ins;
					$pc++;	
				}													
			}
		}			

		$q5 = $sqlca->query("DROP TABLE tmp_concar");
				
		$arrInserts['clientes'] = $clientes[0]["clientes"];

		$rstado = ejecutarInsert($arrInserts, $TabSqlCab, $TabSqlDet,$clientes[1]);

		return $rstado;		
	}

	function interface_ventas_market($FechaIni, $FechaFin, $almacen, $codEmpresa, $num_actual) {// VENTAS MARKET
		global $sqlca;

		$clientes = InterfaceConcarActModel::interface_clientes_market($FechaIni, $FechaFin, $almacen, $codEmpresa, $num_actual);

		if(trim($num_actual)=="")
			$num_actual = 0;

		$FechaIni_Original = $FechaIni;
		$FechaFin_Original = $FechaFin;
		$FechaDiv = explode("/", $FechaIni);
		$FechaIni = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];
		$anio1 	  = $FechaDiv[2];
		$mes1 	  = $FechaDiv[1];
		$dia1 	  = $FechaDiv[0];				
		$postrans = "pos_trans".$FechaDiv[2].$FechaDiv[1];
		$FechaDiv = explode("/", $FechaFin);
		$FechaFin = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];
		$anio2 	  = $FechaDiv[2];
		$mes2 	  = $FechaDiv[1];
		$dia2 	  = $FechaDiv[0];				
		$Anio 	  = substr($FechaDiv[2],2,3);
		
		if ($anio1 != $anio2 || $mes1 != $mes2) {
			return 3;// Fechas deben ser mismo mes y año
		} else {
			if ($dia1 > $dia2) 
				return 4;// Fecha inicio debe ser menor que fecha final					
		}

		$Anio 		= trim($Anio);
		$codEmpresa = trim($codEmpresa);

		$TabSqlCab 	= "CC".$codEmpresa.$Anio;// Tabla SQL Cabecera
		$TabSqlDet 	= "CD".$codEmpresa.$Anio;// Tabla SQL Detalle
		$TabCan    	= "CAN".$codEmpresa;
		
		$val = InterfaceConcarActModel::verificaFecha("1", $almacen, $FechaIni, $FechaFin);

		if ($val == 5)
			return 5;		
		
// 		$sql = "
// SELECT
//  venta_subdiario_market,
//  venta_cuenta_cliente_mkt,
//  venta_cuenta_impuesto,
//  venta_cuenta_ventas_mkt,
//  id_centrocosto,
//  subdiario_dia,
//  venta_cuenta_cliente_lubri,
//  codane_lubri,
//  venta_cuenta_ventas_lubri,
//  cod_cliente,
//  cod_caja
// FROM
//  concar_config;
// 		";

// 		if ($sqlca->query($sql) < 0) 
// 			return false;	
	
// 		$a = $sqlca->fetchRow();
// 		$vmsubdiario = $a[0];
// 		$vmcliente   = $a[1];
// 		$vmimpuesto  = $a[2];
// 		$vmventas    = $a[3];	
// 		$vmcencos    = $a[4];	
// 		$opcion      = $a[5];				
// 		$cuentalub   = $a[6];
// 		$codanelub   = $a[7];
// 		$ventaslub   = $a[8];
// 		$cod_cliente = $a[9];
// 		$cod_caja    = $a[10];

		$sql = "
SELECT
	c1.account AS venta_subdiario_market
	,c2.account AS venta_cuenta_cliente_mkt
	,c3.account AS venta_cuenta_impuesto
	,c4.account AS venta_cuenta_ventas_mkt
	,c5.account AS id_centrocosto
	,c6.account AS subdiario_dia
	,c7.account AS venta_cuenta_cliente_lubri
	,c8.account AS codane_lubri
	,c9.account AS venta_cuenta_ventas_lubri
	,c10.account AS cod_cliente
	,c11.account AS cod_caja
FROM
	concar_confignew c1
	LEFT JOIN concar_confignew c2 ON   c2.module = 2   AND c2.category = 1   AND c2.subcategory = 0   --Cuenta Market Cliente
	LEFT JOIN concar_confignew c3 ON   c3.module = 2   AND c3.category = 1   AND c3.subcategory = 1   --Cuenta Market Impuesto
	LEFT JOIN concar_confignew c4 ON   c4.module = 2   AND c4.category = 1   AND c4.subcategory = 2   --Cuenta Market Ventas

	LEFT JOIN concar_confignew c5 ON   c5.module = 0   AND c5.category = 2   AND c5.subcategory = 2   --Centro de Costo Market
	LEFT JOIN concar_confignew c6 ON   c6.module = 0   AND c6.category = 1   AND c6.subcategory = 0   --Subdiario dia

	LEFT JOIN concar_confignew c7 ON   c7.module = 2   AND c7.category = 2   AND c7.subcategory = 0   --Cuenta Lubricante Cliente
	LEFT JOIN concar_confignew c8 ON   c8.module = 0   AND c8.category = 3   AND c8.subcategory = 1   --Codigo de Anexo Lubricante
	LEFT JOIN concar_confignew c9 ON   c9.module = 2   AND c9.category = 2   AND c9.subcategory = 1   --Cuenta Lubricante Ventas

	LEFT JOIN concar_confignew c10 ON   c10.module = 0   AND c10.category = 1   AND c10.subcategory = 1   --Codigo de Cliente
	LEFT JOIN concar_confignew c11 ON   c11.module = 0   AND c11.category = 1   AND c11.subcategory = 2   --Codigo de Caja
WHERE
	c1.module = 2   AND c1.category = 0   AND c1.subcategory = 0;   --Subdiario de Venta Market
		";

		if ($sqlca->query($sql) < 0) 
			return false;	
	
		$a = $sqlca->fetchRow();
		$vmsubdiario = $a[0];
		$vmcliente   = $a[1];
		$vmimpuesto  = $a[2];
		$vmventas    = $a[3];	
		$vmcencos    = $a[4];	
		$opcion      = $a[5];				
		$cuentalub   = $a[6];
		$codanelub   = $a[7];
		$ventaslub   = $a[8];
		$cod_cliente = $a[9];
		$cod_caja    = $a[10];

		$sql = "
		SELECT * FROM 
			(
				SELECT 
					to_char(date(t.dia),'YYMMDD') as dia, 
					'$vmcliente'::text as DCUENTA, 
					'$cod_cliente'::text as codigo, 
					' '::text as trans, 
					'2'::text as tip, 
					'D'::text as ddh, 
					round(sum(t.importe),2) as importe, 
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,	
					t.es as sucursal, 
					'$cod_caja'|| cfp.ch_posz_pos || '-' ||MIN(cast(t.trans as integer))||'-'||MAX(cast(t.trans as integer))::text as dnumdoc,
					'$vmsubdiario'||substring(t.dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'A'::text as tip2,
					'TK'::TEXT AS doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t 
					LEFT JOIN int_articulos art ON (t.codigo=art.art_codigo) 
					LEFT JOIN (
						SELECT   ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal, FIRST(nu_posz_z_serie) AS nu_posz_z_serie
						FROM     pos_z_cierres 
						WHERE    dt_posz_fecha_sistema::date BETWEEN '$FechaIni' AND '$FechaFin' AND ch_sucursal = '$almacen'
						GROUP BY ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal
					) cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					t.td = 'B' 
					AND t.tipo = 'M' AND art.art_tipo!='01' 
					AND date(t.dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND t.es = '$almacen'
					AND t.usr = ''
				GROUP BY 
					t.dia,
					t.es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos
			) as K
			UNION
			(
			SELECT 
				to_char(date(t.dia),'YYMMDD') as dia, 
				'$vmimpuesto'::text as DCUENTA, 
				''::text as codigo,
				' '::text as trans,
				'2'::text as tip,
				'H'::text as ddh,
				round(sum(t.igv),2) as importe,
				cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,	
				t.es as sucursal , 
				'$cod_caja'|| cfp.ch_posz_pos || '-' ||MIN(cast(t.trans as integer))||'-'||MAX(cast(t.trans as integer))::text as dnumdoc,
				'$vmsubdiario'||substring(t.dia::text from 9 for 2)::text as subdiario,
				''::text as DCENCOS,
				'A'::text as tip2,
				'TK'::TEXT AS doctype,
				''::text AS documento_referencia,
				''::text AS fe_referencia,
				0 AS nu_igv_referencia,
				0 AS nu_bi_referencia
			FROM 
				$postrans  t 
				LEFT JOIN int_articulos art ON (t.codigo=art.art_codigo) 
				LEFT JOIN (
					SELECT   ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal, FIRST(nu_posz_z_serie) AS nu_posz_z_serie
					FROM     pos_z_cierres 
					WHERE    dt_posz_fecha_sistema::date BETWEEN '$FechaIni' AND '$FechaFin' AND ch_sucursal = '$almacen'
					GROUP BY ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal
				) cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
			WHERE
				t.td = 'B' 
				AND t.tipo='M'  AND art.art_tipo!='01' 
				AND date(t.dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				AND t.es = '$almacen'
				AND t.usr = ''
			GROUP BY
				t.dia,
				t.es,
				cfp.nu_posz_z_serie,
				cfp.ch_posz_pos
			)
			UNION
			(
				SELECT 
					to_char(date(t.dia),'YYMMDD') as dia, 
					'$vmventas'::text as DCUENTA, 
					''::text as codigo,--701101C01 solo se activa cuando no tipo!='01' y es base imponible
					' '::text as trans, 
					'2'::text as tip,  
					'H'::text as ddh, 
					round(sum(t.importe-t.igv),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,	
					t.es as sucursal , 
					'$cod_caja'|| cfp.ch_posz_pos || '-' ||MIN(cast(t.trans as integer))||'-'||MAX(cast(t.trans as integer))::text::text as dnumdoc,
					'$vmsubdiario'||substring(t.dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS,
					'A'::text as tip2,
					'TK'::TEXT AS doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans  t 
					LEFT JOIN int_articulos art ON (t.codigo=art.art_codigo)
					LEFT JOIN interface_equivalencia_producto q ON (art.art_codigo = q.art_codigo)
					LEFT JOIN (
						SELECT   ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal, FIRST(nu_posz_z_serie) AS nu_posz_z_serie
						FROM     pos_z_cierres 
						WHERE    dt_posz_fecha_sistema::date BETWEEN '$FechaIni' AND '$FechaFin' AND ch_sucursal = '$almacen'
						GROUP BY ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal
					) cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					t.td = 'B' 
					AND t.tipo='M'  AND art.art_tipo!='01' 
					AND date(t.dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND q.art_codigo IS NULL
					AND t.es = '$almacen'
					AND t.usr = ''
				GROUP BY 
					t.dia,
					t.es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos
			)
			UNION--FIN DE LETRA A
			(
				SELECT 
					to_char(date(t.dia),'YYMMDD') as dia, 
					'$cuentalub'::text as DCUENTA, 
					'$cod_cliente'::text as codigo, 
					' '::text as trans, 
					'2'::text as tip, 
					'D'::text as ddh, 
					round(sum(t.importe),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					t.es as sucursal, 
					'$cod_caja'|| cfp.ch_posz_pos || '-' ||MIN(cast(t.trans as integer))||'-'||MAX(cast(t.trans as integer))::text as dnumdoc,
					'$vmsubdiario'||substring(t.dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'B'::text as tip2,
					'TK'::TEXT AS doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t 
					LEFT JOIN int_articulos art ON (t.codigo=art.art_codigo) 
					LEFT JOIN (
						SELECT   ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal, FIRST(nu_posz_z_serie) AS nu_posz_z_serie
						FROM     pos_z_cierres 
						WHERE    dt_posz_fecha_sistema::date BETWEEN '$FechaIni' AND '$FechaFin' AND ch_sucursal = '$almacen'
						GROUP BY ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal
					) cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					t.td = 'B' 
					AND t.tipo = 'M' AND art.art_tipo='01' 
					AND date(t.dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND t.es = '$almacen'
					AND t.usr = ''
				GROUP BY 
					t.dia,
					t.es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos
			)
			UNION
			(
				SELECT 
					to_char(date(t.dia),'YYMMDD') as dia, 
					'$vmimpuesto'::text as DCUENTA, 
					''::text as codigo, 
					' '::text as trans, 
					'2'::text as tip,  
					'H'::text as ddh, 
					round(sum(t.igv),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					t.es as sucursal , 
					'$cod_caja'|| cfp.ch_posz_pos || '-' ||MIN(cast(t.trans as integer))||'-'||MAX(cast(t.trans as integer))::text as dnumdoc,
					'$vmsubdiario'||substring(t.dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'B'::text as tip2,
					'TK'::TEXT AS doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans  t 
					LEFT JOIN int_articulos art ON (t.codigo=art.art_codigo) 
					LEFT JOIN (
						SELECT   ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal, FIRST(nu_posz_z_serie) AS nu_posz_z_serie
						FROM     pos_z_cierres 
						WHERE    dt_posz_fecha_sistema::date BETWEEN '$FechaIni' AND '$FechaFin' AND ch_sucursal = '$almacen'
						GROUP BY ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal
					) cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					t.td = 'B' 
					AND t.tipo='M'  AND art.art_tipo='01' 
					AND date(t.dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND t.es = '$almacen'
					AND t.usr = ''
				GROUP BY 
					t.dia,
					t.es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos
			)
			UNION
			(
				SELECT 
					to_char(date(t.dia),'YYMMDD') as dia, 
					'$ventaslub'::text as DCUENTA, 
					''::text as codigo, 
					' '::text as trans, 
					'2'::text as tip,  
					'H'::text as ddh, 
					round(sum(t.importe-t.igv),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					t.es as sucursal , 
					'$cod_caja'|| cfp.ch_posz_pos || '-' ||MIN(cast(t.trans as integer))||'-'||MAX(cast(t.trans as integer))::text::text as dnumdoc,
					'$vmsubdiario'||substring(t.dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS,
					'B'::text as tip2,
					'TK'::TEXT AS doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans  t 
					LEFT JOIN int_articulos art ON (t.codigo=art.art_codigo) 
					LEFT JOIN interface_equivalencia_producto q ON (art.art_codigo = q.art_codigo)
					LEFT JOIN (
						SELECT   ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal, FIRST(nu_posz_z_serie) AS nu_posz_z_serie
						FROM     pos_z_cierres 
						WHERE    dt_posz_fecha_sistema::date BETWEEN '$FechaIni' AND '$FechaFin' AND ch_sucursal = '$almacen'
						GROUP BY ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal
					) cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					t.td = 'B' 
					AND t.tipo='M'  AND art.art_tipo='01' 
					AND date(t.dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND q.art_codigo IS NULL
					AND t.es = '$almacen'
					AND t.usr = ''
				GROUP BY 
					t.dia,
					t.es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos
			)--FIN DE TICKETS BOLETAS Y LETRA B
			UNION--INICIO DE BOLETAS ELECTRONICAS Y LETRA C
			(
				SELECT 
					to_char(date(t.dia),'YYMMDD') as dia, 
					'$vmcliente'::text as DCUENTA, 
					'$cod_cliente'::text as codigo, 
					' '::text as trans, 
					'2'::text as tip, 
					'D'::text as ddh, 
					round(sum(t.importe),2) as importe, 
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,	
					t.es as sucursal,
					SUBSTR(TRIM(t.usr), 0, 5) || '-' || MIN(SUBSTR(TRIM(t.usr), 7)) || '/' || MAX(SUBSTR(TRIM(t.usr), 7))::text as dnumdoc,
					'$vmsubdiario'||substring(t.dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'C'::text as tip2,
					'BV'::TEXT AS doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans AS t 
					LEFT JOIN int_articulos AS art ON (t.codigo=art.art_codigo) 
					LEFT JOIN (
						SELECT   ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal, FIRST(nu_posz_z_serie) AS nu_posz_z_serie
						FROM     pos_z_cierres 
						WHERE    dt_posz_fecha_sistema::date BETWEEN '$FechaIni' AND '$FechaFin' AND ch_sucursal = '$almacen'
						GROUP BY ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal
					) AS cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					t.td = 'B' 
					AND t.tipo = 'M'
					AND art.art_tipo!='01' 
					AND date(t.dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND t.es = '$almacen'
					AND t.usr != ''
					AND t.tm='V'
				GROUP BY 
					t.dia,
					t.es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos,
					SUBSTR(TRIM(usr), 0, 5)
			)
			UNION
			(
				SELECT 
					to_char(date(t.dia),'YYMMDD') as dia, 
					'$vmimpuesto'::text as DCUENTA, 
					''::text as codigo,
					' '::text as trans,
					'2'::text as tip,
					'H'::text as ddh,
					round(sum(t.igv),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,	
					t.es as sucursal , 
					SUBSTR(TRIM(t.usr), 0, 5) || '-' || MIN(SUBSTR(TRIM(t.usr), 7)) || '/' || MAX(SUBSTR(TRIM(t.usr), 7))::text as dnumdoc,
					'$vmsubdiario'||substring(t.dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'C'::text as tip2,
					'BV'::TEXT AS doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans AS t 
					LEFT JOIN int_articulos AS art ON (t.codigo=art.art_codigo) 
					LEFT JOIN (
						SELECT   ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal, FIRST(nu_posz_z_serie) AS nu_posz_z_serie
						FROM     pos_z_cierres 
						WHERE    dt_posz_fecha_sistema::date BETWEEN '$FechaIni' AND '$FechaFin' AND ch_sucursal = '$almacen'
						GROUP BY ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal
					) AS cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					t.td = 'B' 
					AND t.tipo='M'
					AND art.art_tipo!='01' 
					AND date(t.dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND t.es = '$almacen'
					AND t.usr != ''
					AND t.tm='V'
				GROUP BY 
					t.dia,
					t.es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos,
					SUBSTR(TRIM(usr), 0, 5)
			)
			UNION
			(
				SELECT 
					to_char(date(t.dia),'YYMMDD') as dia, 
					'$vmventas'::text as DCUENTA, 
					''::text as codigo, 
					' '::text as trans, 
					'2'::text as tip,  
					'H'::text as ddh, 
					round(sum(t.importe-t.igv),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,	
					t.es as sucursal,
					SUBSTR(TRIM(t.usr), 0, 5) || '-' || MIN(SUBSTR(TRIM(t.usr), 7)) || '/' || MAX(SUBSTR(TRIM(t.usr), 7))::text as dnumdoc,
					'$vmsubdiario'||substring(t.dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS,
					'C'::text as tip2,
					'BV'::TEXT AS doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans  t 
					LEFT JOIN int_articulos art ON (t.codigo=art.art_codigo)
					LEFT JOIN interface_equivalencia_producto q ON (art.art_codigo = q.art_codigo)
					LEFT JOIN (
						SELECT   ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal, FIRST(nu_posz_z_serie) AS nu_posz_z_serie
						FROM     pos_z_cierres 
						WHERE    dt_posz_fecha_sistema::date BETWEEN '$FechaIni' AND '$FechaFin' AND ch_sucursal = '$almacen'
						GROUP BY ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal
					) AS cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					t.td = 'B' 
					AND t.tipo='M'
					AND art.art_tipo!='01' 
					AND date(t.dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND q.art_codigo IS NULL
					AND t.es = '$almacen'
					AND t.usr != ''
					AND t.tm='V'
				GROUP BY 
					t.dia,
					t.es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos,
					SUBSTR(TRIM(usr), 0, 5)
			)--FIN DE LETRA C
			UNION--INICIO DE LETRA D
			(
				SELECT 
					to_char(date(t.dia),'YYMMDD') as dia, 
					'$cuentalub'::text as DCUENTA, 
					'$cod_cliente'::text as codigo, 
					' '::text as trans, 
					'2'::text as tip, 
					'D'::text as ddh, 
					round(sum(t.importe),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					t.es as sucursal, 
					SUBSTR(TRIM(t.usr), 0, 5) || '-' || MIN(SUBSTR(TRIM(t.usr), 7)) || '/' || MAX(SUBSTR(TRIM(t.usr), 7))::text as dnumdoc,
					'$vmsubdiario'||substring(t.dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'D'::text as tip2,
					'BV'::TEXT AS doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t 
					LEFT JOIN int_articulos art ON (t.codigo=art.art_codigo) 
					LEFT JOIN (
						SELECT   ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal, FIRST(nu_posz_z_serie) AS nu_posz_z_serie
						FROM     pos_z_cierres 
						WHERE    dt_posz_fecha_sistema::date BETWEEN '$FechaIni' AND '$FechaFin' AND ch_sucursal = '$almacen'
						GROUP BY ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal
					) cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					t.td = 'B' 
					AND t.tipo = 'M'
					AND art.art_tipo='01' 
					AND date(t.dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND t.es = '$almacen'
					AND t.usr != ''
					AND t.tm='V'
				GROUP BY 
					t.dia,
					t.es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos,
					SUBSTR(TRIM(usr), 0, 5)
			)
			UNION
			(
				SELECT 
					to_char(date(t.dia),'YYMMDD') as dia, 
					'$vmimpuesto'::text as DCUENTA, 
					''::text as codigo, 
					' '::text as trans, 
					'2'::text as tip,  
					'H'::text as ddh, 
					round(sum(t.igv),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					t.es as sucursal , 
					SUBSTR(TRIM(t.usr), 0, 5) || '-' || MIN(SUBSTR(TRIM(t.usr), 7)) || '/' || MAX(SUBSTR(TRIM(t.usr), 7))::text as dnumdoc,
					'$vmsubdiario'||substring(t.dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'D'::text as tip2,
					'BV'::TEXT AS doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans  t 
					LEFT JOIN int_articulos art ON (t.codigo=art.art_codigo) 
					LEFT JOIN (
						SELECT   ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal, FIRST(nu_posz_z_serie) AS nu_posz_z_serie
						FROM     pos_z_cierres 
						WHERE    dt_posz_fecha_sistema::date BETWEEN '$FechaIni' AND '$FechaFin' AND ch_sucursal = '$almacen'
						GROUP BY ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal
					) cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					t.td = 'B' 
					AND t.tipo='M'
					AND art.art_tipo='01' 
					AND date(t.dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND t.es = '$almacen'
					AND t.usr != ''
					AND t.tm='V'
				GROUP BY 
					t.dia,
					t.es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos,
					SUBSTR(TRIM(usr), 0, 5)
			)
			UNION
			(
				SELECT 
					to_char(date(t.dia),'YYMMDD') as dia, 
					'$ventaslub'::text as DCUENTA, 
					''::text as codigo, 
					' '::text as trans, 
					'2'::text as tip,  
					'H'::text as ddh, 
					round(sum(t.importe-t.igv),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					t.es as sucursal , 
					SUBSTR(TRIM(t.usr), 0, 5) || '-' || MIN(SUBSTR(TRIM(t.usr), 7)) || '/' || MAX(SUBSTR(TRIM(t.usr), 7))::text as dnumdoc,
					'$vmsubdiario'||substring(t.dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS,
					'D'::text as tip2,
					'BV'::TEXT AS doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans AS t 
					LEFT JOIN int_articulos art ON (t.codigo=art.art_codigo) 
					LEFT JOIN interface_equivalencia_producto q ON (art.art_codigo = q.art_codigo)
					LEFT JOIN (
						SELECT   ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal, FIRST(nu_posz_z_serie) AS nu_posz_z_serie
						FROM     pos_z_cierres 
						WHERE    dt_posz_fecha_sistema::date BETWEEN '$FechaIni' AND '$FechaFin' AND ch_sucursal = '$almacen'
						GROUP BY ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal
					) cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					t.td = 'B' 
					AND t.tipo='M'
					AND art.art_tipo='01' 
					AND date(t.dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND q.art_codigo IS NULL
					AND t.es = '$almacen'
					AND t.usr != ''
					AND t.tm='V'
				GROUP BY 
					t.dia,
					t.es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos,
					SUBSTR(TRIM(usr), 0, 5)
			)--FIN DE BOLETAS ELECTRONICAS Y LETRA D
			UNION--INICIO DE TICKETS FACTURAS Y LETRA E
			(
				SELECT 
					to_char(date(t.dia),'YYMMDD') as dia, 
					'$vmcliente'::text as DCUENTA,  
					t.ruc::text as codigo, 
					t.trans::text as trans,
					'2'::text as tip, 
					'D'::text as ddh, 
					round(sum(t.importe),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					t.es as sucursal, 
					'$cod_caja'|| cfp.ch_posz_pos || '-' ||t.trans::text as dnumdoc,
					'$vmsubdiario'||substring(t.dia::text from 9 for 2)::text as subdiario,             
					''::text as DCENCOS,
					'E'::text as tip2,
					'TK'::TEXT AS doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans  t 
					LEFT JOIN int_articulos art ON (t.codigo=art.art_codigo) 
					LEFT JOIN (
						SELECT   ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal, FIRST(nu_posz_z_serie) AS nu_posz_z_serie
						FROM     pos_z_cierres 
						WHERE    dt_posz_fecha_sistema::date BETWEEN '$FechaIni' AND '$FechaFin' AND ch_sucursal = '$almacen'
						GROUP BY ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal
					) cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					t.td = 'F' 
					AND t.tipo='M'  AND art.art_tipo!='01' 
					AND date(t.dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND t.importe>0 
					AND t.es = '$almacen'
					AND t.usr = ''
				GROUP BY 
					t.dia,
					t.trans,
					t.ruc,
					t.es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos
			)
			UNION
			(
				SELECT 
					to_char(date(t.dia),'YYMMDD') as dia, 
					'$vmimpuesto'::text as DCUENTA,  
					''::text as codigo, 
					t.trans::text as trans, 
					'2'::text as tip, 
					'H'::text as ddh, 
					round(sum(t.igv),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,	
					t.es as sucursal , 
					'$cod_caja'|| cfp.ch_posz_pos || '-' ||t.trans::text as dnumdoc,
					'$vmsubdiario'||substring(t.dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'E'::text as tip2,
					'TK'::TEXT AS doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans  t 
					LEFT JOIN int_articulos art ON (t.codigo=art.art_codigo) 
					LEFT JOIN (
						SELECT   ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal, FIRST(nu_posz_z_serie) AS nu_posz_z_serie
						FROM     pos_z_cierres 
						WHERE    dt_posz_fecha_sistema::date BETWEEN '$FechaIni' AND '$FechaFin' AND ch_sucursal = '$almacen'
						GROUP BY ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal
					) cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					t.td = 'F' 
					AND t.tipo='M'  AND art.art_tipo!='01' 
					AND date(t.dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND t.importe>0 
					AND t.es = '$almacen'
					AND t.usr = ''
				GROUP BY 
					t.dia,
					t.trans,
					t.ruc,
					t.es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos
			)
			UNION
			(
				SELECT 
					to_char(date(t.dia),'YYMMDD') as dia, 
					'$vmventas'::text as DCUENTA,  
					''::text as codigo,
					t.trans::text as trans,
					'2'::text as tip,  
					'H'::text as ddh, 
					round(sum(t.importe-t.igv),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,	
					t.es as sucursal , 
					'$cod_caja'|| cfp.ch_posz_pos || '-' ||t.trans::text as dnumdoc,
					'$vmsubdiario'||substring(t.dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS,
					'E'::text as tip2,
					'TK'::TEXT AS doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t 					 
					LEFT JOIN int_articulos art ON (t.codigo=art.art_codigo) 
					LEFT JOIN interface_equivalencia_producto q ON (art.art_codigo = q.art_codigo)
					LEFT JOIN (
						SELECT   ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal, FIRST(nu_posz_z_serie) AS nu_posz_z_serie
						FROM     pos_z_cierres 
						WHERE    dt_posz_fecha_sistema::date BETWEEN '$FechaIni' AND '$FechaFin' AND ch_sucursal = '$almacen'
						GROUP BY ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal
					) cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					t.td = 'F' 
					AND t.tipo='M'
					AND art.art_tipo!='01' 
					AND date(t.dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND t.importe>0  
					AND q.art_codigo IS NULL
					AND t.es = '$almacen'
					AND t.usr = ''
				GROUP BY 
					t.dia,
					t.trans,
					t.ruc,
					t.es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos
			)--FIN DE LETRA E
			UNION--INICIO DE LETRA F
			(
				SELECT 
					to_char(date(t.dia),'YYMMDD') as dia, 
					'$cuentalub'::text as DCUENTA,  
					t.ruc::text as codigo, 
					t.trans::text as trans,
					'2'::text as tip, 
					'D'::text as ddh, 
					round(sum(t.importe),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,	
					t.es as sucursal, 
					'$cod_caja'|| cfp.ch_posz_pos || '-' ||t.trans::text as dnumdoc,
					'$vmsubdiario'||substring(t.dia::text from 9 for 2)::text as subdiario,             
					''::text as DCENCOS,
					'F'::text as tip2,
					'TK'::TEXT AS doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t 
					LEFT JOIN int_articulos art ON (t.codigo=art.art_codigo) 
					LEFT JOIN (
						SELECT   ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal, FIRST(nu_posz_z_serie) AS nu_posz_z_serie
						FROM     pos_z_cierres 
						WHERE    dt_posz_fecha_sistema::date BETWEEN '$FechaIni' AND '$FechaFin' AND ch_sucursal = '$almacen'
						GROUP BY ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal
					) cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					t.td = 'F' 
					AND t.tipo='M'
					AND art.art_tipo='01' 
					AND date(t.dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND t.importe>0 
					AND t.es = '$almacen'
					AND t.usr = ''
				GROUP BY 
					t.dia,
					t.trans,
					t.ruc,
					t.es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos
			)	
			UNION
			(
				SELECT 
					to_char(date(t.dia),'YYMMDD') as dia, 
					'$vmimpuesto'::text as DCUENTA,  
					''::text as codigo, 
					t.trans::text as trans, 
					'2'::text as tip, 
					'H'::text as ddh, 
					round(sum(t.igv),2) as importe, 
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					t.es as sucursal , 
					'$cod_caja'|| cfp.ch_posz_pos || '-' ||t.trans::text as dnumdoc,
					'$vmsubdiario'||substring(t.dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'F'::text as tip2,
					'TK'::TEXT AS doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans  t 
					LEFT JOIN int_articulos art ON (t.codigo=art.art_codigo) 
					LEFT JOIN (
						SELECT   ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal, FIRST(nu_posz_z_serie) AS nu_posz_z_serie
						FROM     pos_z_cierres 
						WHERE    dt_posz_fecha_sistema::date BETWEEN '$FechaIni' AND '$FechaFin' AND ch_sucursal = '$almacen'
						GROUP BY ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal
					) cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					t.td = 'F' 
					AND t.tipo='M'
					AND art.art_tipo='01' 
					AND date(t.dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND t.importe>0 
					AND t.es = '$almacen'
					AND t.usr = ''
				GROUP BY 
					t.dia,
					t.trans,
					t.ruc,
					t.es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos
			)
			UNION
			(
				SELECT 
					to_char(date(t.dia),'YYMMDD') as dia, 
					'$ventaslub'::text as DCUENTA,  
					''::text as codigo,
					t.trans::text as trans,
					'2'::text as tip,  
					'H'::text as ddh, 
					round(sum(t.importe-t.igv),2) as importe, 
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,	
					t.es as sucursal , 
					'$cod_caja'|| cfp.ch_posz_pos || '-' ||t.trans::text as dnumdoc,
					'$vmsubdiario'||substring(t.dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS,
					'F'::text as tip2,
					'TK'::TEXT AS doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t 					 
					LEFT JOIN int_articulos art ON (t.codigo=art.art_codigo)
					LEFT JOIN interface_equivalencia_producto q ON (art.art_codigo = q.art_codigo)
					LEFT JOIN (
						SELECT   ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal, FIRST(nu_posz_z_serie) AS nu_posz_z_serie
						FROM     pos_z_cierres 
						WHERE    dt_posz_fecha_sistema::date BETWEEN '$FechaIni' AND '$FechaFin' AND ch_sucursal = '$almacen'
						GROUP BY ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal
					) cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					t.td = 'F' 
					AND t.tipo='M'
					AND art.art_tipo='01' 
					AND date(t.dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND t.importe>0  
					AND q.art_codigo IS NULL
					AND t.es = '$almacen'
					AND t.usr = ''
				GROUP BY 
					t.dia,
					t.trans,
					t.ruc,
					t.es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos
			)--FIN DE TICKETS FACTURAS Y LETRA F
			UNION--INICIO DE FACTURAS ELECTRONICAS Y LETRA G
			(
				SELECT 
					to_char(date(t.dia),'YYMMDD') as dia, 
					'$vmcliente'::text as DCUENTA,  
					t.ruc::text as codigo, 
					SUBSTR(TRIM(t.usr), 6)::text as trans,
					'2'::text as tip, 
					'D'::text as ddh, 
					round(sum(t.importe),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					t.es as sucursal, 
					SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
					'$vmsubdiario'||substring(t.dia::text from 9 for 2)::text as subdiario,             
					''::text as DCENCOS,
					'G'::text as tip2,
					'FT'::TEXT AS doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans  t 
					LEFT JOIN int_articulos art ON (t.codigo=art.art_codigo) 
					LEFT JOIN (
						SELECT   ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal, FIRST(nu_posz_z_serie) AS nu_posz_z_serie
						FROM     pos_z_cierres 
						WHERE    dt_posz_fecha_sistema::date BETWEEN '$FechaIni' AND '$FechaFin' AND ch_sucursal = '$almacen'
						GROUP BY ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal
					) cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					t.td = 'F' 
					AND t.tipo='M'
					AND art.art_tipo!='01' 
					AND date(t.dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND t.importe>0 
					AND t.es = '$almacen'
					AND t.usr != ''
					AND t.tm='V'
				GROUP BY 
					t.dia,
					t.trans,
					t.ruc,
					t.es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos,
					t.usr
			)	
			UNION
			(
				SELECT 
					to_char(date(t.dia),'YYMMDD') as dia, 
					'$vmimpuesto'::text as DCUENTA,  
					''::text as codigo, 
					SUBSTR(TRIM(t.usr), 6)::text as trans,
					'2'::text as tip, 
					'H'::text as ddh, 
					round(sum(t.igv),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,	
					t.es as sucursal , 
					SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
					'$vmsubdiario'||substring(t.dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'G'::text as tip2,
					'FT'::TEXT AS doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans  t 
					LEFT JOIN int_articulos art ON (t.codigo=art.art_codigo) 
					LEFT JOIN (
						SELECT   ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal, FIRST(nu_posz_z_serie) AS nu_posz_z_serie
						FROM     pos_z_cierres 
						WHERE    dt_posz_fecha_sistema::date BETWEEN '$FechaIni' AND '$FechaFin' AND ch_sucursal = '$almacen'
						GROUP BY ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal
					) cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					t.td = 'F' 
					AND t.tipo='M'
					AND art.art_tipo!='01' 
					AND date(t.dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND t.importe>0 
					AND t.es = '$almacen'
					AND t.usr != ''
					AND t.tm='V'
				GROUP BY 
					t.dia,
					t.trans,
					t.ruc,
					t.es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos,
					t.usr
			)
			UNION
			(
				SELECT 
					to_char(date(t.dia),'YYMMDD') as dia, 
					'$vmventas'::text as DCUENTA,  
					''::text as codigo,
					SUBSTR(TRIM(t.usr), 6)::text as trans,
					'2'::text as tip,  
					'H'::text as ddh, 
					round(sum(t.importe-t.igv),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,	
					t.es as sucursal , 
					SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
					'$vmsubdiario'||substring(t.dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS,
					'G'::text as tip2,
					'FT'::TEXT AS doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans t 					 
					LEFT JOIN int_articulos art ON (t.codigo=art.art_codigo) 
					LEFT JOIN interface_equivalencia_producto q ON (art.art_codigo = q.art_codigo)
					LEFT JOIN (
						SELECT   ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal, FIRST(nu_posz_z_serie) AS nu_posz_z_serie
						FROM     pos_z_cierres 
						WHERE    dt_posz_fecha_sistema::date BETWEEN '$FechaIni' AND '$FechaFin' AND ch_sucursal = '$almacen'
						GROUP BY ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal
					) cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					t.td = 'F' 
					AND t.tipo='M'
					AND art.art_tipo!='01' 
					AND date(t.dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND t.importe>0  
					AND q.art_codigo IS NULL
					AND t.es = '$almacen'
					AND t.usr != ''
					AND t.tm='V'
				GROUP BY 
					t.dia,
					t.trans,
					t.ruc,
					t.es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos,
					t.usr
			)--FIN DE LETRA G
			UNION--INICIO DE LETRA H
			(
				SELECT 
					to_char(date(t.dia),'YYMMDD') as dia, 
					'$cuentalub'::text as DCUENTA,  
					t.ruc::text as codigo, 
					SUBSTR(TRIM(t.usr), 6)::text as trans,
					'2'::text as tip, 
					'D'::text as ddh, 
					round(sum(t.importe),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,	
					t.es as sucursal, 
					SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
					'$vmsubdiario'||substring(t.dia::text from 9 for 2)::text as subdiario,             
					''::text as DCENCOS ,
					'H'::text as tip2,
					'FT'::TEXT AS doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans AS t 
					LEFT JOIN int_articulos AS art ON (t.codigo=art.art_codigo) 
					LEFT JOIN (
						SELECT   ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal, FIRST(nu_posz_z_serie) AS nu_posz_z_serie
						FROM     pos_z_cierres 
						WHERE    dt_posz_fecha_sistema::date BETWEEN '$FechaIni' AND '$FechaFin' AND ch_sucursal = '$almacen'
						GROUP BY ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal
					) AS cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					t.td = 'F' 
					AND t.tipo='M'
					AND art.art_tipo='01' 
					AND date(t.dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND t.importe>0 
					AND t.es = '$almacen'
					AND t.usr != ''
					AND t.tm='V'
				GROUP BY 
					t.dia,
					t.trans,
					t.ruc,
					t.es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos,
					t.usr
			)		
			UNION
			(
				SELECT 
					to_char(date(t.dia),'YYMMDD') as dia, 
					'$vmimpuesto'::text as DCUENTA,  
					''::text as codigo, 
					SUBSTR(TRIM(t.usr), 6)::text as trans,
					'2'::text as tip, 
					'H'::text as ddh, 
					round(sum(t.igv),2) as importe, 
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					t.es as sucursal , 
					SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
					'$vmsubdiario'||substring(t.dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS ,
					'H'::text as tip2,
					'FT'::TEXT AS doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans AS t 
					LEFT JOIN int_articulos AS art ON (t.codigo=art.art_codigo) 
					LEFT JOIN (
						SELECT   ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal, FIRST(nu_posz_z_serie) AS nu_posz_z_serie
						FROM     pos_z_cierres 
						WHERE    dt_posz_fecha_sistema::date BETWEEN '$FechaIni' AND '$FechaFin' AND ch_sucursal = '$almacen'
						GROUP BY ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal
					) AS cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					t.td = 'F' 
					AND t.tipo='M'
					AND art.art_tipo='01' 
					AND date(t.dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND t.importe>0 
					AND t.es = '$almacen'
					AND t.usr != ''
					AND t.tm='V'
				GROUP BY 
					t.dia,
					t.trans,
					t.ruc,
					t.es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos,
					t.usr
			)
			UNION
			(
				SELECT 
					to_char(date(t.dia),'YYMMDD') as dia, 
					'$ventaslub'::text as DCUENTA,  
					''::text as codigo,
					SUBSTR(TRIM(t.usr), 6)::text as trans,
					'2'::text as tip,  
					'H'::text as ddh, 
					round(sum(t.importe-t.igv),2) as importe, 
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,	
					t.es as sucursal , 
					SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
					'$vmsubdiario'||substring(t.dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS ,
					'H'::text as tip2,
					'FT'::TEXT AS doctype,
					''::text AS documento_referencia,
					''::text AS fe_referencia,
					0 AS nu_igv_referencia,
					0 AS nu_bi_referencia
				FROM 
					$postrans AS t
					LEFT JOIN int_articulos AS art ON (t.codigo=art.art_codigo)
					LEFT JOIN interface_equivalencia_producto AS q ON (art.art_codigo = q.art_codigo)
					LEFT JOIN (
						SELECT   ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal, FIRST(nu_posz_z_serie) AS nu_posz_z_serie
						FROM     pos_z_cierres 
						WHERE    dt_posz_fecha_sistema::date BETWEEN '$FechaIni' AND '$FechaFin' AND ch_sucursal = '$almacen'
						GROUP BY ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal
					) AS cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					t.td = 'F' 
					AND t.tipo='M'
					AND art.art_tipo='01' 
					AND date(t.dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND t.importe>0  
					AND q.art_codigo IS NULL
					AND t.es = '$almacen'
					AND t.usr != ''
					AND t.tm = 'V'
				GROUP BY 
					t.dia,
					t.trans,
					t.ruc,
					t.es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos,
					t.usr
			)--FIN DE LETRA H
			UNION --INICIO DE FACTURAS ELECTRONICAS DE EXTORNOS != '01'
			(
				SELECT 
					to_char(DATE(dia),'YYMMDD') as dia, 
					'$vmcliente'::text as DCUENTA, 
					ruc::text as codigo, 
					SUBSTR(TRIM(t.usr), 6)::text as trans,
					'1'::text as tip,
					'H'::text as ddh,
					-round(sum(importe), 2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal,
					SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS ,
					'I'::text as tip2,
					'NA'::TEXT as doctype,
					SUBSTR(TRIM(ext.fe1), 0, 5)||'-'||SUBSTR(TRIM(ext.fe1), 6) AS documento_referencia,
					FIRST(ext.fe_referencia),
					SUM(ext.nu_igv_referencia),
					SUM(ext.nu_bi_referencia)
				FROM 
					$postrans AS t
					LEFT JOIN int_articulos AS art ON (t.codigo=art.art_codigo)
					LEFT JOIN (
						SELECT   ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal, FIRST(nu_posz_z_serie) AS nu_posz_z_serie
						FROM     pos_z_cierres 
						WHERE    dt_posz_fecha_sistema::date BETWEEN '$FechaIni' AND '$FechaFin' AND ch_sucursal = '$almacen'
						GROUP BY ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal
					) AS cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
					LEFT JOIN
						(SELECT
							venta_tickes.feoriginal AS fe1,
							venta_tickes.ticketextorno AS fe2,
							venta_tickes.feextorno AS fe3,
							FIRST(venta_tickes.fe_referencia) AS fe_referencia,
							SUM(venta_tickes.nu_igv_referencia) AS nu_igv_referencia,
							SUM(venta_tickes.nu_bi_referencia) AS nu_bi_referencia
						FROM
						(SELECT 
							extorno.origen AS cadenaorigen,
							p.trans AS ticketoriginal,
							extorno.trans1 AS ticketextorno,
							p.usr AS feoriginal,
							extorno.usr AS feextorno,
							TO_CHAR(p.dia, 'DD/MM/YYYY') AS fe_referencia,
							p.igv AS nu_igv_referencia,
							(p.importe - p.igv) AS nu_bi_referencia
						FROM
							$postrans AS p
							LEFT JOIN int_articulos AS art ON (p.codigo=art.art_codigo)
							INNER JOIN (
							SELECT 
								(dia || caja || trim(to_char(rendi_gln,'99999999'))) as registro,
								(dia || caja || trans) as origen,
								trans as trans1,
								usr
							FROM
								$postrans AS t
								LEFT JOIN int_articulos AS art ON (t.codigo=art.art_codigo)
							WHERE
								es 		= '" . $almacen . "'
								AND tm 	= 'D'
								AND td 	= 'F'
								AND usr != ''
								AND DATE(dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
								AND art.art_tipo != '01'
							) as extorno ON(dia || caja || trim(to_char(trans,'99999999'))) = extorno.registro
							AND es 	= '" . $almacen . "'
							AND tm = 'V'
							AND td = 'F'
							AND p.trans < extorno.trans1
							AND p.usr != ''
							AND art.art_tipo != '01'
							AND DATE(p.dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
						) AS venta_tickes
						GROUP BY
							venta_tickes.cadenaorigen,
							venta_tickes.ticketoriginal,
							venta_tickes.ticketextorno,
							venta_tickes.feoriginal,
							venta_tickes.feextorno
					) AS ext ON(ext.fe2 = t.trans AND ext.fe3 = t.usr)
				WHERE 
					td 			= 'F' 
					AND tm 		= 'D'
					AND tipo 	= 'M'
					AND art.art_tipo != '01'
					AND es 		= '" . $almacen . "'
					AND t.usr != ''
					AND DATE(dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
				GROUP BY
					dia,
					ruc,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					t.usr,
					ext.fe1
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmimpuesto'::text as DCUENTA, 
					''::text as codigo, 
					SUBSTR(TRIM(t.usr), 6)::text as trans,
					'1'::text as tip,  
					'D'::text as ddh, 
					-round(sum(igv),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal , 
					SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS ,
					'I'::text as tip2,
					'NA'::TEXT as doctype,
					SUBSTR(TRIM(ext.fe1), 0, 5)||'-'||SUBSTR(TRIM(ext.fe1), 6) AS documento_referencia,
					FIRST(ext.fe_referencia),
					SUM(ext.nu_igv_referencia),
					SUM(ext.nu_bi_referencia)
				FROM 
					$postrans AS t
					LEFT JOIN int_articulos AS art ON (t.codigo=art.art_codigo)
					LEFT JOIN (
						SELECT   ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal, FIRST(nu_posz_z_serie) AS nu_posz_z_serie
						FROM     pos_z_cierres 
						WHERE    dt_posz_fecha_sistema::date BETWEEN '$FechaIni' AND '$FechaFin' AND ch_sucursal = '$almacen'
						GROUP BY ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal
					) AS cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
					LEFT JOIN
						(SELECT
							venta_tickes.feoriginal AS fe1,
							venta_tickes.ticketextorno AS fe2,
							venta_tickes.feextorno AS fe3,
							FIRST(venta_tickes.fe_referencia) AS fe_referencia,
							SUM(venta_tickes.nu_igv_referencia) AS nu_igv_referencia,
							SUM(venta_tickes.nu_bi_referencia) AS nu_bi_referencia
						FROM
						(SELECT 
							extorno.origen AS cadenaorigen,
							p.trans AS ticketoriginal,
							extorno.trans1 AS ticketextorno,
							p.usr AS feoriginal,
							extorno.usr AS feextorno,
							TO_CHAR(p.dia, 'DD/MM/YYYY') AS fe_referencia,
							p.igv AS nu_igv_referencia,
							(p.importe - p.igv) AS nu_bi_referencia
						FROM
							$postrans AS p
							LEFT JOIN int_articulos AS art ON (p.codigo=art.art_codigo)
							INNER JOIN (
							SELECT 
								(dia || caja || trim(to_char(rendi_gln,'99999999'))) as registro,
								(dia || caja || trans) as origen,
								trans as trans1,
								usr
							FROM
								$postrans AS t
								LEFT JOIN int_articulos AS art ON (t.codigo=art.art_codigo)
							WHERE
								es 		= '" . $almacen . "'
								AND tm 	= 'D'
								AND td 	= 'F'
								AND usr != ''
								AND DATE(dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
								AND art.art_tipo != '01'
							) as extorno ON(dia || caja || trim(to_char(trans,'99999999'))) = extorno.registro
							AND es 	= '" . $almacen . "'
							AND tm = 'V'
							AND td = 'F'
							AND p.trans < extorno.trans1
							AND p.usr != ''
							AND art.art_tipo != '01'
							AND DATE(p.dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
						) AS venta_tickes
						GROUP BY
							venta_tickes.cadenaorigen,
							venta_tickes.ticketoriginal,
							venta_tickes.ticketextorno,
							venta_tickes.feoriginal,
							venta_tickes.feextorno
					) AS ext ON(ext.fe2 = t.trans AND ext.fe3 = t.usr)
				WHERE 
					td 			= 'F' 
					AND tm 		= 'D'
					AND tipo 	= 'M'
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
					AND es = '$almacen'
					AND t.usr != ''
					AND art.art_tipo != '01'
				GROUP BY
					dia,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					t.usr,
					ext.fe1
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmventas'::text as DCUENTA, 
					''::text AS codigo_concar,
					SUBSTR(TRIM(t.usr), 6)::text as trans,
					'1'::text as tip, 
					'D'::text as ddh, 
					-(round(sum(importe),2)-round(sum(igv),2)) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal , 
					SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS,
					'I'::text as tip2,
					'NA'::TEXT as doctype,
					SUBSTR(TRIM(ext.fe1), 0, 5)||'-'||SUBSTR(TRIM(ext.fe1), 6) AS documento_referencia,
					FIRST(ext.fe_referencia),
					SUM(ext.nu_igv_referencia),
					SUM(ext.nu_bi_referencia)
				FROM 
					$postrans AS t
					LEFT JOIN int_articulos AS art ON (t.codigo=art.art_codigo)
					LEFT JOIN
						(SELECT
							venta_tickes.feoriginal AS fe1,
							venta_tickes.ticketextorno AS fe2,
							venta_tickes.feextorno AS fe3,
							FIRST(venta_tickes.fe_referencia) AS fe_referencia,
							SUM(venta_tickes.nu_igv_referencia) AS nu_igv_referencia,
							SUM(venta_tickes.nu_bi_referencia) AS nu_bi_referencia
						FROM
						(SELECT 
							extorno.origen AS cadenaorigen,
							p.trans AS ticketoriginal,
							extorno.trans1 AS ticketextorno,
							p.usr AS feoriginal,
							extorno.usr AS feextorno,
							TO_CHAR(p.dia, 'DD/MM/YYYY') AS fe_referencia,
							p.igv AS nu_igv_referencia,
							(p.importe - p.igv) AS nu_bi_referencia
						FROM
							$postrans AS p
							LEFT JOIN int_articulos AS art ON (p.codigo=art.art_codigo)
							INNER JOIN (
							SELECT 
								(dia || caja || trim(to_char(rendi_gln,'99999999'))) as registro,
								(dia || caja || trans) as origen,
								trans as trans1,
								usr
							FROM
								" . $postrans . " AS t
								LEFT JOIN int_articulos AS art ON (t.codigo=art.art_codigo)
							WHERE
								es 		= '" . $almacen . "'
								AND tm 	= 'D'
								AND td 	= 'F'
								AND usr != ''
								AND DATE(dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
								AND art.art_tipo != '01'
							) as extorno ON(dia || caja || trim(to_char(trans,'99999999'))) = extorno.registro
							AND es 	= '" . $almacen . "'
							AND tm = 'V'
							AND td = 'F'
							AND p.trans < extorno.trans1
							AND p.usr != ''
							AND art.art_tipo != '01'
							AND DATE(p.dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
						) AS venta_tickes
						GROUP BY
							venta_tickes.cadenaorigen,
							venta_tickes.ticketoriginal,
							venta_tickes.ticketextorno,
							venta_tickes.feoriginal,
							venta_tickes.feextorno
					) AS ext ON(ext.fe2 = t.trans AND ext.fe3 = t.usr)
					LEFT JOIN (
						SELECT   ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal, FIRST(nu_posz_z_serie) AS nu_posz_z_serie
						FROM     pos_z_cierres 
						WHERE    dt_posz_fecha_sistema::date BETWEEN '$FechaIni' AND '$FechaFin' AND ch_sucursal = '$almacen'
						GROUP BY ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal
					) cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					td = 'F' 
					AND tm = 'D'
					AND tipo='M'
					AND date(dia) between '$FechaIni' AND '$FechaFin' 
					AND codigo=art.art_codigo 
					AND es = '$almacen'
					AND t.usr != ''
					AND art.art_tipo != '01'
				GROUP BY
					dia,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					t.usr,
					ext.fe1
			)
			UNION--EXTORNOS DE FACTURAS ELECTRONICAS - ='01'
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmcliente'::text as DCUENTA, 
					ruc::text as codigo, 
					SUBSTR(TRIM(t.usr), 6)::text as trans,
					'1'::text as tip,
					'H'::text as ddh,
					-round(sum(importe), 2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal , 
					SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS ,
					'J'::text as tip2,
					'NA'::TEXT as doctype,
					SUBSTR(TRIM(ext.fe1), 0, 5)||'-'||SUBSTR(TRIM(ext.fe1), 6) AS documento_referencia,
					FIRST(ext.fe_referencia),
					SUM(ext.nu_igv_referencia),
					SUM(ext.nu_bi_referencia)
				FROM 
					$postrans AS t
					LEFT JOIN int_articulos AS art ON (t.codigo=art.art_codigo)
					LEFT JOIN (
						SELECT   ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal, FIRST(nu_posz_z_serie) AS nu_posz_z_serie
						FROM     pos_z_cierres 
						WHERE    dt_posz_fecha_sistema::date BETWEEN '$FechaIni' AND '$FechaFin' AND ch_sucursal = '$almacen'
						GROUP BY ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal
					) AS cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
					LEFT JOIN
						(SELECT
							venta_tickes.feoriginal AS fe1,
							venta_tickes.ticketextorno AS fe2,
							venta_tickes.feextorno AS fe3,
							FIRST(venta_tickes.fe_referencia) AS fe_referencia,
							SUM(venta_tickes.nu_igv_referencia) AS nu_igv_referencia,
							SUM(venta_tickes.nu_bi_referencia) AS nu_bi_referencia
						FROM
						(SELECT 
							extorno.origen AS cadenaorigen,
							p.trans AS ticketoriginal,
							extorno.trans1 AS ticketextorno,
							p.usr AS feoriginal,
							extorno.usr AS feextorno,
							TO_CHAR(p.dia, 'DD/MM/YYYY') AS fe_referencia,
							p.igv AS nu_igv_referencia,
							(p.importe - p.igv) AS nu_bi_referencia
						FROM
							$postrans AS p
							LEFT JOIN int_articulos AS art ON (p.codigo=art.art_codigo)
							INNER JOIN (
							SELECT 
								(dia || caja || trim(to_char(rendi_gln,'99999999'))) as registro,
								(dia || caja || trans) as origen,
								trans as trans1,
								usr
							FROM
								" . $postrans . " AS t
								LEFT JOIN int_articulos AS art ON (t.codigo=art.art_codigo)
							WHERE
								es 		= '" . $almacen . "'
								AND tm 	= 'D'
								AND td 	= 'F'
								AND usr != ''
								AND DATE(dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
								AND art.art_tipo = '01'
							) as extorno ON(dia || caja || trim(to_char(trans,'99999999'))) = extorno.registro
							AND es 	= '" . $almacen . "'
							AND tm = 'V'
							AND td = 'F'
							AND p.trans < extorno.trans1
							AND p.usr != ''
							AND art.art_tipo = '01'
							AND DATE(p.dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
						) AS venta_tickes
						GROUP BY
							venta_tickes.cadenaorigen,
							venta_tickes.ticketoriginal,
							venta_tickes.ticketextorno,
							venta_tickes.feoriginal,
							venta_tickes.feextorno
					) AS ext ON(ext.fe2 = t.trans AND ext.fe3 = t.usr)
				WHERE 
					td 			= 'F' 
					AND tm 		= 'D'
					AND tipo 	= 'M'
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND es = '$almacen'
					AND t.usr != ''
					AND art.art_tipo = '01'
				GROUP BY
					dia,
					ruc,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					t.usr,
					ext.fe1
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmimpuesto'::text as DCUENTA, 
					''::text as codigo, 
					SUBSTR(TRIM(t.usr), 6)::text as trans,
					'1'::text as tip,  
					'D'::text as ddh, 
					-round(sum(igv),2) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal , 
					SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS ,
					'J'::text as tip2,
					'NA'::TEXT as doctype,
					SUBSTR(TRIM(ext.fe1), 0, 5)||'-'||SUBSTR(TRIM(ext.fe1), 6) AS documento_referencia,
					FIRST(ext.fe_referencia),
					SUM(ext.nu_igv_referencia),
					SUM(ext.nu_bi_referencia)
				FROM 
					$postrans AS t
					LEFT JOIN int_articulos AS art ON (t.codigo=art.art_codigo)
					LEFT JOIN (
						SELECT   ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal, FIRST(nu_posz_z_serie) AS nu_posz_z_serie
						FROM     pos_z_cierres 
						WHERE    dt_posz_fecha_sistema::date BETWEEN '$FechaIni' AND '$FechaFin' AND ch_sucursal = '$almacen'
						GROUP BY ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal
					) AS cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
					LEFT JOIN
						(SELECT
							venta_tickes.feoriginal AS fe1,
							venta_tickes.ticketextorno AS fe2,
							venta_tickes.feextorno AS fe3,
							FIRST(venta_tickes.fe_referencia) AS fe_referencia,
							SUM(venta_tickes.nu_igv_referencia) AS nu_igv_referencia,
							SUM(venta_tickes.nu_bi_referencia) AS nu_bi_referencia
						FROM
						(SELECT 
							extorno.origen AS cadenaorigen,
							p.trans AS ticketoriginal,
							extorno.trans1 AS ticketextorno,
							p.usr AS feoriginal,
							extorno.usr AS feextorno,
							TO_CHAR(p.dia, 'DD/MM/YYYY') AS fe_referencia,
							p.igv AS nu_igv_referencia,
							(p.importe - p.igv) AS nu_bi_referencia
						FROM
							$postrans AS p
							LEFT JOIN int_articulos AS art ON (p.codigo=art.art_codigo)
							INNER JOIN (
							SELECT 
								(dia || caja || trim(to_char(rendi_gln,'99999999'))) as registro,
								(dia || caja || trans) as origen,
								trans as trans1,
								usr
							FROM
								$postrans AS t
								LEFT JOIN int_articulos AS art ON (t.codigo=art.art_codigo)
							WHERE
								es 		= '" . $almacen . "'
								AND tm 	= 'D'
								AND td 	= 'F'
								AND usr != ''
								AND DATE(dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
								AND art.art_tipo = '01'
							) as extorno ON(dia || caja || trim(to_char(trans,'99999999'))) = extorno.registro
							AND es 	= '" . $almacen . "'
							AND tm = 'V'
							AND td = 'F'
							AND p.trans < extorno.trans1
							AND p.usr != ''
							AND art.art_tipo = '01'
							AND DATE(p.dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
						) AS venta_tickes
						GROUP BY
							venta_tickes.cadenaorigen,
							venta_tickes.ticketoriginal,
							venta_tickes.ticketextorno,
							venta_tickes.feoriginal,
							venta_tickes.feextorno
					) AS ext ON(ext.fe2 = t.trans AND ext.fe3 = t.usr)
				WHERE 
					td 			= 'F' 
					AND tm 		= 'D'
					AND tipo 	= 'M'
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
					AND es = '$almacen'
					AND t.usr != ''
					AND art.art_tipo = '01'
				GROUP BY
					dia,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					t.usr,
					ext.fe1
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$ventaslub'::text as DCUENTA, 
					'' AS codigo_concar, 
					SUBSTR(TRIM(t.usr), 6)::text as trans,
					'1'::text as tip, 
					'D'::text as ddh, 
					-(round(sum(importe),2)-round(sum(igv),2)) as importe,
					cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
					es as sucursal , 
					SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS,
					'J'::text as tip2,
					'NA'::TEXT as doctype,
					SUBSTR(TRIM(ext.fe1), 0, 5)||'-'||SUBSTR(TRIM(ext.fe1), 6) AS documento_referencia,
					FIRST(ext.fe_referencia),
					SUM(ext.nu_igv_referencia),
					SUM(ext.nu_bi_referencia)
				FROM 
					$postrans AS t
					LEFT JOIN int_articulos AS art ON (t.codigo=art.art_codigo)
					LEFT JOIN
						(SELECT
							venta_tickes.feoriginal AS fe1,
							venta_tickes.ticketextorno AS fe2,
							venta_tickes.feextorno AS fe3,
							FIRST(venta_tickes.fe_referencia) AS fe_referencia,
							SUM(venta_tickes.nu_igv_referencia) AS nu_igv_referencia,
							SUM(venta_tickes.nu_bi_referencia) AS nu_bi_referencia
						FROM
						(SELECT 
							extorno.origen AS cadenaorigen,
							p.trans AS ticketoriginal,
							extorno.trans1 AS ticketextorno,
							p.usr AS feoriginal,
							extorno.usr AS feextorno,
							TO_CHAR(p.dia, 'DD/MM/YYYY') AS fe_referencia,
							p.igv AS nu_igv_referencia,
							(p.importe - p.igv) AS nu_bi_referencia
						FROM
							$postrans AS p
							LEFT JOIN int_articulos AS art ON (p.codigo=art.art_codigo)
							INNER JOIN (
							SELECT 
								(dia || caja || trim(to_char(rendi_gln,'99999999'))) as registro,
								(dia || caja || trans) as origen,
								trans as trans1,
								usr
							FROM
								" . $postrans . " AS t
								LEFT JOIN int_articulos AS art ON (t.codigo=art.art_codigo)
							WHERE
								es 		= '" . $almacen . "'
								AND tm 	= 'D'
								AND td 	= 'F'
								AND usr != ''
								AND DATE(dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
								AND art.art_tipo = '01'
							) as extorno ON(dia || caja || trim(to_char(trans,'99999999'))) = extorno.registro
							AND es 	= '" . $almacen . "'
							AND tm = 'V'
							AND td = 'F'
							AND p.trans < extorno.trans1
							AND p.usr != ''
							AND art.art_tipo = '01'
							AND DATE(p.dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
						) AS venta_tickes
						GROUP BY
							venta_tickes.cadenaorigen,
							venta_tickes.ticketoriginal,
							venta_tickes.ticketextorno,
							venta_tickes.feoriginal,
							venta_tickes.feextorno
					) AS ext ON(ext.fe2 = t.trans AND ext.fe3 = t.usr)
					LEFT JOIN (
						SELECT   ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal, FIRST(nu_posz_z_serie) AS nu_posz_z_serie
						FROM     pos_z_cierres 
						WHERE    dt_posz_fecha_sistema::date BETWEEN '$FechaIni' AND '$FechaFin' AND ch_sucursal = '$almacen'
						GROUP BY ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal
					) cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					td 			= 'F' 
					AND tm 		= 'D'
					AND tipo 	= 'M'
					AND date(dia) between '$FechaIni' AND '$FechaFin' 
					AND codigo = art.art_codigo 
					AND es = '$almacen'
					AND t.usr != ''
					AND art.art_tipo = '01'
				GROUP BY
					dia,
					cfp.nu_posz_z_serie,
					es,
					cfp.ch_posz_pos,
					t.usr,
					ext.fe1
			)
UNION--EXTORNOS DE BOLETAS ELECTRÓNICAS != '01' TOTAL
(SELECT
 to_char(date(dia),'YYMMDD') as dia, 
 '$vmcliente'::text as DCUENTA, 
 '$cod_cliente'::text as codigo,
 SUBSTR(TRIM(t.usr), 6)::text as trans,
 '1'::text as tip,
 'H'::text as ddh,
 -round(sum(importe), 2) as importe,
 cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
 es as sucursal,
 SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
 '$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
 ''::text as DCENCOS,
 'K'::text as tip2,
 'NA'::TEXT as doctype,
 SUBSTR(TRIM(ext.fe1), 0, 5)||'-'||SUBSTR(TRIM(ext.fe1), 6) AS documento_referencia,
 FIRST(ext.fe_referencia),
 SUM(ext.nu_igv_referencia),
 SUM(ext.nu_bi_referencia)
FROM 
 $postrans AS t
 LEFT JOIN int_articulos AS art ON (t.codigo=art.art_codigo)
 LEFT JOIN
	(SELECT
		venta_tickes.feoriginal AS fe1,
		venta_tickes.ticketextorno AS fe2,
		venta_tickes.feextorno AS fe3,
		FIRST(venta_tickes.fe_referencia) AS fe_referencia,
		SUM(venta_tickes.nu_igv_referencia) AS nu_igv_referencia,
		SUM(venta_tickes.nu_bi_referencia) AS nu_bi_referencia
	FROM
	(SELECT 
		extorno.origen AS cadenaorigen,
		p.trans AS ticketoriginal,
		extorno.trans1 AS ticketextorno,
		p.usr AS feoriginal,
		extorno.usr AS feextorno,
		TO_CHAR(p.dia, 'DD/MM/YYYY') AS fe_referencia,
		p.igv AS nu_igv_referencia,
		(p.importe - p.igv) AS nu_bi_referencia
	FROM
		$postrans AS p
		LEFT JOIN int_articulos AS art ON (p.codigo=art.art_codigo)
		INNER JOIN (
		SELECT 
			(dia || caja || trim(to_char(rendi_gln,'99999999'))) as registro,
			(dia || caja || trans) as origen,
			trans as trans1,
			usr
		FROM
			$postrans AS t
			LEFT JOIN int_articulos AS art ON (t.codigo=art.art_codigo)
		WHERE
			es 		= '" . $almacen . "'
			AND tm 	= 'D'
			AND td 	= 'B'
			AND usr != ''
			AND DATE(dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
			AND art.art_tipo != '01'
		) as extorno ON(dia || caja || trim(to_char(trans,'99999999'))) = extorno.registro
		AND es 	= '" . $almacen . "'
		AND tm = 'V'
		AND td = 'B'
		AND p.trans < extorno.trans1
		AND p.usr != ''
		AND art.art_tipo != '01'
		AND DATE(p.dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
	) AS venta_tickes
	GROUP BY
		venta_tickes.cadenaorigen,
		venta_tickes.ticketoriginal,
		venta_tickes.ticketextorno,
		venta_tickes.feoriginal,
		venta_tickes.feextorno
 ) AS ext ON(ext.fe2 = t.trans AND ext.fe3 = t.usr)
 LEFT JOIN (
	SELECT   ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal, FIRST(nu_posz_z_serie) AS nu_posz_z_serie
	FROM     pos_z_cierres 
	WHERE    dt_posz_fecha_sistema::date BETWEEN '$FechaIni' AND '$FechaFin' AND ch_sucursal = '$almacen'
	GROUP BY ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal
 ) AS cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
WHERE 
 td = 'B'
 AND tm = 'D'
 AND tipo = 'M'
 AND date(dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
 AND es = '" . $almacen . "'
 AND t.usr != ''
 AND art.art_tipo != '01'
GROUP BY
 dia,
 ruc,
 cfp.nu_posz_z_serie,
 es,
 cfp.ch_posz_pos,
 t.usr,
 ext.fe1
)
UNION--EXTORNOS DE BOLETAS ELECTRÓNICAS !=01 IGV
(SELECT 
 to_char(date(dia),'YYMMDD') as dia, 
 '$vmimpuesto'::text as DCUENTA, 
 ''::text as codigo, 
 SUBSTR(TRIM(t.usr), 6)::text as trans,
 '1'::text as tip,  
 'D'::text as ddh, 
 -round(sum(igv),2) as importe,
 cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
 es as sucursal,
 SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
 '$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
 ''::text as DCENCOS,
 'K'::text as tip2,
 'NA'::TEXT as doctype,
 SUBSTR(TRIM(ext.fe1), 0, 5)||'-'||SUBSTR(TRIM(ext.fe1), 6) AS documento_referencia,
 FIRST(ext.fe_referencia),
 SUM(ext.nu_igv_referencia),
 SUM(ext.nu_bi_referencia)
FROM 
 $postrans AS t
 LEFT JOIN int_articulos AS art ON (t.codigo=art.art_codigo)
 LEFT JOIN
	(SELECT
		venta_tickes.feoriginal AS fe1,
		venta_tickes.ticketextorno AS fe2,
		venta_tickes.feextorno AS fe3,
		FIRST(venta_tickes.fe_referencia) AS fe_referencia,
		SUM(venta_tickes.nu_igv_referencia) AS nu_igv_referencia,
		SUM(venta_tickes.nu_bi_referencia) AS nu_bi_referencia
	FROM
	(SELECT 
		extorno.origen AS cadenaorigen,
		p.trans AS ticketoriginal,
		extorno.trans1 AS ticketextorno,
		p.usr AS feoriginal,
		extorno.usr AS feextorno,
		TO_CHAR(p.dia, 'DD/MM/YYYY') AS fe_referencia,
		p.igv AS nu_igv_referencia,
		(p.importe - p.igv) AS nu_bi_referencia
	FROM
		$postrans p
		LEFT JOIN int_articulos AS art ON (p.codigo=art.art_codigo)
		INNER JOIN (
		SELECT 
			(dia || caja || trim(to_char(rendi_gln,'99999999'))) as registro,
			(dia || caja || trans) as origen,
			trans as trans1,
			usr
		FROM
			$postrans AS t
			LEFT JOIN int_articulos AS art ON (t.codigo=art.art_codigo)
		WHERE
			es 		= '" . $almacen . "'
			AND tm 	= 'D'
			AND td 	= 'B'
			AND usr != ''
			AND DATE(dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
 			AND art.art_tipo != '01'
		) as extorno ON(dia || caja || trim(to_char(trans,'99999999'))) = extorno.registro
		AND es 	= '" . $almacen . "'
		AND tm = 'V'
		AND td = 'B'
		AND p.trans < extorno.trans1
		AND p.usr != ''
		AND art.art_tipo != '01'
		AND DATE(p.dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
	) AS venta_tickes
	GROUP BY
		venta_tickes.cadenaorigen,
		venta_tickes.ticketoriginal,
		venta_tickes.ticketextorno,
		venta_tickes.feoriginal,
		venta_tickes.feextorno
 ) AS ext ON(ext.fe2 = t.trans AND ext.fe3 = t.usr)
 LEFT JOIN (
	SELECT   ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal, FIRST(nu_posz_z_serie) AS nu_posz_z_serie
	FROM     pos_z_cierres 
	WHERE    dt_posz_fecha_sistema::date BETWEEN '$FechaIni' AND '$FechaFin' AND ch_sucursal = '$almacen'
	GROUP BY ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal
 ) AS cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
WHERE 
 td = 'B'
 AND tm = 'D'
 AND tipo = 'M'
 AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
 AND es = '$almacen'
 AND t.usr != ''
 AND art.art_tipo != '01'
GROUP BY
 dia,
 cfp.nu_posz_z_serie,
 es,
 cfp.ch_posz_pos,
 t.usr,
 ext.fe1
)
UNION--EXTORNOS DE BOLETAS ELECTRÓNICAS !='01' SUBTOTAL
(SELECT 
 to_char(date(dia),'YYMMDD') as dia, 
 '$vmventas'::text as DCUENTA, 
 '' AS codigo_concar, 
 SUBSTR(TRIM(t.usr), 6)::text as trans,
 '1'::text as tip, 
 'D'::text as ddh, 
 -(round(sum(importe),2)-round(sum(igv),2)) as importe,
 cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
 es as sucursal,
 SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
 '$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
 '$vmcencos'::text as DCENCOS,
 'K'::text as tip2,
 'NA'::TEXT as doctype,
 SUBSTR(TRIM(ext.fe1), 0, 5)||'-'||SUBSTR(TRIM(ext.fe1), 6) AS documento_referencia,
 FIRST(ext.fe_referencia),
 SUM(ext.nu_igv_referencia),
 SUM(ext.nu_bi_referencia)
FROM 
 $postrans AS t
 LEFT JOIN int_articulos AS art ON (t.codigo=art.art_codigo)
 LEFT JOIN
	(SELECT
		venta_tickes.feoriginal AS fe1,
		venta_tickes.ticketextorno AS fe2,
		venta_tickes.feextorno AS fe3,
		FIRST(venta_tickes.fe_referencia) AS fe_referencia,
		SUM(venta_tickes.nu_igv_referencia) AS nu_igv_referencia,
		SUM(venta_tickes.nu_bi_referencia) AS nu_bi_referencia
	FROM
	(SELECT 
		extorno.origen AS cadenaorigen,
		p.trans AS ticketoriginal,
		extorno.trans1 AS ticketextorno,
		p.usr AS feoriginal,
		extorno.usr AS feextorno,
		TO_CHAR(p.dia, 'DD/MM/YYYY') AS fe_referencia,
		p.igv AS nu_igv_referencia,
		(p.importe - p.igv) AS nu_bi_referencia
	FROM
		$postrans AS p
		LEFT JOIN int_articulos AS art ON (p.codigo=art.art_codigo)
		INNER JOIN (
		SELECT 
			(dia || caja || trim(to_char(rendi_gln,'99999999'))) as registro,
			(dia || caja || trans) as origen,
			trans as trans1,
			usr
		FROM
			$postrans AS t
			LEFT JOIN int_articulos AS art ON (t.codigo=art.art_codigo)
		WHERE
			es 		= '" . $almacen . "'
			AND tm 	= 'D'
			AND td 	= 'B'
			AND usr != ''
			AND DATE(dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
 			AND art.art_tipo != '01'
		) as extorno ON(dia || caja || trim(to_char(trans,'99999999'))) = extorno.registro
		AND es 	= '" . $almacen . "'
		AND tm = 'V'
		AND td = 'B'
		AND p.trans < extorno.trans1
		AND p.usr != ''
		AND art.art_tipo != '01'
		AND DATE(p.dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
	) AS venta_tickes
	GROUP BY
		venta_tickes.cadenaorigen,
		venta_tickes.ticketoriginal,
		venta_tickes.ticketextorno,
		venta_tickes.feoriginal,
		venta_tickes.feextorno
 ) AS ext ON(ext.fe2 = t.trans AND ext.fe3 = t.usr)
 LEFT JOIN (
	SELECT   ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal, FIRST(nu_posz_z_serie) AS nu_posz_z_serie
	FROM     pos_z_cierres 
	WHERE    dt_posz_fecha_sistema::date BETWEEN '$FechaIni' AND '$FechaFin' AND ch_sucursal = '$almacen'
	GROUP BY ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal
 ) AS cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
WHERE 
 td = 'B'
 AND tm = 'D'
 AND tipo = 'M'
 AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
 AND codigo = art.art_codigo 
 AND es = '$almacen'
 AND t.usr != ''
 AND art.art_tipo != '01'
GROUP BY
 dia,
 cfp.nu_posz_z_serie,
 es,
 cfp.ch_posz_pos,
 t.usr,
 ext.fe1
)
UNION--EXTORNOS DE BOLETAS ELECTRÓNICAS ='01' TOTAL
(SELECT
 to_char(date(dia),'YYMMDD') as dia, 
 '$vmcliente'::text as DCUENTA,
 '$cod_cliente'::text as codigo,
 SUBSTR(TRIM(t.usr), 6)::text as trans,
 '1'::text as tip,
 'H'::text as ddh,
 -round(sum(importe), 2) as importe,
 cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
 es as sucursal,
 SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
 '$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
 ''::text as DCENCOS,
 'L'::text as tip2,
 'NA'::TEXT as doctype,
 SUBSTR(TRIM(ext.fe1), 0, 5)||'-'||SUBSTR(TRIM(ext.fe1), 6) AS documento_referencia,
 FIRST(ext.fe_referencia),
 SUM(ext.nu_igv_referencia),
 SUM(ext.nu_bi_referencia)
FROM 
 $postrans AS t
 LEFT JOIN int_articulos AS art ON (t.codigo=art.art_codigo)
 LEFT JOIN
	(SELECT
		venta_tickes.feoriginal AS fe1,
		venta_tickes.ticketextorno AS fe2,
		venta_tickes.feextorno AS fe3,
		FIRST(venta_tickes.fe_referencia) AS fe_referencia,
		SUM(venta_tickes.nu_igv_referencia) AS nu_igv_referencia,
		SUM(venta_tickes.nu_bi_referencia) AS nu_bi_referencia
	FROM
	(SELECT 
		extorno.origen AS cadenaorigen,
		p.trans AS ticketoriginal,
		extorno.trans1 AS ticketextorno,
		p.usr AS feoriginal,
		extorno.usr AS feextorno,
		TO_CHAR(p.dia, 'DD/MM/YYYY') AS fe_referencia,
		p.igv AS nu_igv_referencia,
		(p.importe - p.igv) AS nu_bi_referencia
	FROM
		$postrans p
		LEFT JOIN int_articulos AS art ON (p.codigo=art.art_codigo)
		INNER JOIN (
		SELECT 
			(dia || caja || trim(to_char(rendi_gln,'99999999'))) as registro,
			(dia || caja || trans) as origen,
			trans as trans1,
			usr
		FROM
			$postrans AS t
			LEFT JOIN int_articulos AS art ON (t.codigo=art.art_codigo)
		WHERE
			es 		= '" . $almacen . "'
			AND tm 	= 'D'
			AND td 	= 'B'
			AND usr != ''
			AND DATE(dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
 			AND art.art_tipo = '01'
		) as extorno ON(dia || caja || trim(to_char(trans,'99999999'))) = extorno.registro
		AND es 	= '" . $almacen . "'
		AND tm = 'V'
		AND td = 'B'
		AND p.trans < extorno.trans1
		AND p.usr != ''
		AND art.art_tipo = '01'
		AND DATE(p.dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
	) AS venta_tickes
	GROUP BY
		venta_tickes.cadenaorigen,
		venta_tickes.ticketoriginal,
		venta_tickes.ticketextorno,
		venta_tickes.feoriginal,
		venta_tickes.feextorno
 ) AS ext ON(ext.fe2 = t.trans AND ext.fe3 = t.usr)
 LEFT JOIN (
	SELECT   ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal, FIRST(nu_posz_z_serie) AS nu_posz_z_serie
	FROM     pos_z_cierres 
	WHERE    dt_posz_fecha_sistema::date BETWEEN '$FechaIni' AND '$FechaFin' AND ch_sucursal = '$almacen'
	GROUP BY ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal
 ) AS cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
WHERE 
 td = 'B'
 AND tm = 'D'
 AND tipo = 'M'
 AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
 AND es = '$almacen'
 AND t.usr != ''
 AND art.art_tipo = '01'
GROUP BY
 dia,
 ruc,
 cfp.nu_posz_z_serie,
 es,
 cfp.ch_posz_pos,
 t.usr,
 ext.fe1
)
UNION--EXTORNOS DE BOLETAS ELECTRÓNICAS ='01' IGV	
(SELECT
 to_char(date(dia),'YYMMDD') as dia, 
 '$vmimpuesto'::text as DCUENTA, 
 ''::text as codigo, 
 SUBSTR(TRIM(t.usr), 6)::text as trans,
 '1'::text as tip,  
 'D'::text as ddh, 
 -round(sum(igv),2) as importe,
 cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
 es as sucursal,
 SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
 '$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
 ''::text as DCENCOS,
 'L'::text as tip2,
 'NA'::TEXT as doctype,
 SUBSTR(TRIM(ext.fe1), 0, 5)||'-'||SUBSTR(TRIM(ext.fe1), 6) AS documento_referencia,
 FIRST(ext.fe_referencia),
 SUM(ext.nu_igv_referencia),
 SUM(ext.nu_bi_referencia)
FROM 
 $postrans AS t
 LEFT JOIN int_articulos AS art ON (t.codigo=art.art_codigo)
 LEFT JOIN
	(SELECT
		venta_tickes.feoriginal AS fe1,
		venta_tickes.ticketextorno AS fe2,
		venta_tickes.feextorno AS fe3,
		FIRST(venta_tickes.fe_referencia) AS fe_referencia,
		SUM(venta_tickes.nu_igv_referencia) AS nu_igv_referencia,
		SUM(venta_tickes.nu_bi_referencia) AS nu_bi_referencia
	FROM
	(SELECT 
		extorno.origen AS cadenaorigen,
		p.trans AS ticketoriginal,
		extorno.trans1 AS ticketextorno,
		p.usr AS feoriginal,
		extorno.usr AS feextorno,
		TO_CHAR(p.dia, 'DD/MM/YYYY') AS fe_referencia,
		p.igv AS nu_igv_referencia,
		(p.importe - p.igv) AS nu_bi_referencia
	FROM
		$postrans AS p
		LEFT JOIN int_articulos AS art ON (p.codigo=art.art_codigo)
		INNER JOIN (
		SELECT 
			(dia || caja || trim(to_char(rendi_gln,'99999999'))) as registro,
			(dia || caja || trans) as origen,
			trans as trans1,
			usr
		FROM
			$postrans AS t
			LEFT JOIN int_articulos AS art ON (t.codigo=art.art_codigo)
		WHERE
			es 		= '" . $almacen . "'
			AND tm 	= 'D'
			AND td 	= 'B'
			AND usr != ''
			AND DATE(dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
 			AND art.art_tipo = '01'
		) as extorno ON(dia || caja || trim(to_char(trans,'99999999'))) = extorno.registro
		AND es 	= '" . $almacen . "'
		AND tm = 'V'
		AND td = 'B'
		AND p.trans < extorno.trans1
		AND p.usr != ''
		AND art.art_tipo = '01'
		AND DATE(p.dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
	) AS venta_tickes
	GROUP BY
		venta_tickes.cadenaorigen,
		venta_tickes.ticketoriginal,
		venta_tickes.ticketextorno,
		venta_tickes.feoriginal,
		venta_tickes.feextorno
 ) AS ext ON(ext.fe2 = t.trans AND ext.fe3 = t.usr)
 LEFT JOIN (
	SELECT   ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal, FIRST(nu_posz_z_serie) AS nu_posz_z_serie
	FROM     pos_z_cierres 
	WHERE    dt_posz_fecha_sistema::date BETWEEN '$FechaIni' AND '$FechaFin' AND ch_sucursal = '$almacen'
	GROUP BY ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal
 ) AS cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
WHERE 
 td = 'B'
 AND tm = 'D'
 AND tipo = 'M'
 AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
 AND es = '$almacen'
 AND t.usr != ''
 AND art.art_tipo = '01'
GROUP BY
 dia,
 cfp.nu_posz_z_serie,
 es,
 cfp.ch_posz_pos,
 t.usr,
 ext.fe1
)
UNION--EXTORNOS DE BOLETAS ELECTRÓNICAS ='01' SUBTOTAL
(SELECT 
 to_char(date(dia),'YYMMDD') as dia, 
 '$ventaslub'::text as DCUENTA, 
 '' AS codigo_concar,
 SUBSTR(TRIM(t.usr), 6)::text as trans,
 '1'::text as tip, 
 'D'::text as ddh, 
 -(round(sum(importe),2)-round(sum(igv),2)) as importe,
 cfp.nu_posz_z_serie|| ' - ' || cfp.ch_posz_pos as venta,
 es as sucursal,
 SUBSTR(TRIM(t.usr), 0, 5) || '-' ||SUBSTR(TRIM(t.usr), 6)::text as dnumdoc,
 '$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
 '$vmcencos'::text as DCENCOS,
 'L'::text as tip2,
 'NA'::TEXT as doctype,
 SUBSTR(TRIM(ext.fe1), 0, 5)||'-'||SUBSTR(TRIM(ext.fe1), 6) AS documento_referencia,
 FIRST(ext.fe_referencia),
 SUM(ext.nu_igv_referencia),
 SUM(ext.nu_bi_referencia)
FROM 
 $postrans AS t
 LEFT JOIN int_articulos AS art ON (t.codigo=art.art_codigo)
 LEFT JOIN
	(SELECT
		venta_tickes.feoriginal AS fe1,
		venta_tickes.ticketextorno AS fe2,
		venta_tickes.feextorno AS fe3,
		FIRST(venta_tickes.fe_referencia) AS fe_referencia,
		SUM(venta_tickes.nu_igv_referencia) AS nu_igv_referencia,
		SUM(venta_tickes.nu_bi_referencia) AS nu_bi_referencia
	FROM
	(SELECT 
		extorno.origen AS cadenaorigen,
		p.trans AS ticketoriginal,
		extorno.trans1 AS ticketextorno,
		p.usr AS feoriginal,
		extorno.usr AS feextorno,
		TO_CHAR(p.dia, 'DD/MM/YYYY') AS fe_referencia,
		p.igv AS nu_igv_referencia,
		(p.importe - p.igv) AS nu_bi_referencia
	FROM
		$postrans p
		LEFT JOIN int_articulos AS art ON (p.codigo=art.art_codigo)
		INNER JOIN (
		SELECT 
			(dia || caja || trim(to_char(rendi_gln,'99999999'))) as registro,
			(dia || caja || trans) as origen,
			trans as trans1,
			usr
		FROM
			$postrans AS t
			LEFT JOIN int_articulos AS art ON (t.codigo=art.art_codigo)
		WHERE
			es 		= '" . $almacen . "'
			AND tm 	= 'D'
			AND td 	= 'B'
			AND usr != ''
			AND DATE(dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
 			AND art.art_tipo = '01'
		) as extorno ON(dia || caja || trim(to_char(trans,'99999999'))) = extorno.registro
		AND es 	= '" . $almacen . "'
		AND tm = 'V'
		AND td = 'B'
		AND p.trans < extorno.trans1
		AND p.usr != ''
		AND art.art_tipo = '01'
		AND DATE(p.dia) BETWEEN '" . $FechaIni . "' AND '" . $FechaFin . "'
	) AS venta_tickes
	GROUP BY
		venta_tickes.cadenaorigen,
		venta_tickes.ticketoriginal,
		venta_tickes.ticketextorno,
		venta_tickes.feoriginal,
		venta_tickes.feextorno
 ) AS ext ON(ext.fe2 = t.trans AND ext.fe3 = t.usr)
 LEFT JOIN (
	SELECT   ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal, FIRST(nu_posz_z_serie) AS nu_posz_z_serie
	FROM     pos_z_cierres 
	WHERE    dt_posz_fecha_sistema::date BETWEEN '$FechaIni' AND '$FechaFin' AND ch_sucursal = '$almacen'
	GROUP BY ch_posz_pos, dt_posz_fecha_sistema, nu_posturno, ch_sucursal
 ) AS cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
WHERE 
 td = 'B'
 AND tm = 'D'
 AND tipo = 'M'
 AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
 AND codigo = art.art_codigo
 AND es = '$almacen'
 AND t.usr != ''
 AND art.art_tipo = '01'
GROUP BY
 dia,
 cfp.nu_posz_z_serie,
 es,
 cfp.ch_posz_pos,
 t.usr,
 ext.fe1
)
			ORDER BY dia, venta, tip2, tip, trans, DCUENTA;";

		echo "<pre>";
		echo "\n\nVENTAS MARKET: ".$sql."\n\n";	
		echo "</pre>";

		$centimo = "CREATE TABLE tmp_concar_centimo (dsubdia character varying(7), dcompro character varying(6), dsecue character varying(7), dfeccom character varying(6), dcuenta character varying(12), dcodane character varying(18),
			dcencos character varying(6), dcodmon character varying(2), ddh character varying(1), dimport numeric(14,2), dtipdoc character varying(2), dnumdoc character varying(30), 
			dfecdoc character varying(8), dfecven character varying(8), darea character varying(3), dflag character varying(1), ddate date not null, dxglosa character varying(40),
			dusimport numeric(14,2), dmnimpor numeric(14,2), dcodarc character varying(2), dfeccom2 character varying(8), dfecdoc2 character varying(8), dvanexo character varying(1), dcodane2 character varying(18), documento_referencia character varying(15) NULL, fe_referencia DATE NULL, nu_igv_referencia numeric(20,4), nu_bi_referencia numeric(20,4));";

		$sqlca->query($centimo);

		$correlativo = 0;
		$contador = '0000';  
		$k = 0;  
		$md = 0;     
		
		if ($sqlca->query($sql)<=0) { 	
			return NULL;	
		}

		if ($sqlca->query($sql)>0) { 												
			$correlativo = ($FechaDiv[1] * 10000) + $num_actual;
			while ($reg = $sqlca->fetchRow()) {
				//reiniciar el numerador cuando sea un dia diferente
				/*
				if($subdia != $reg[10]){
					$correlativo = 0;
					$correlativo = ($FechaDiv[1] * 10000) + $num_actual;
					$subdia = $reg[10];
				}
				*/
				// validando si subdiario se toma completo ($opcion=0) o con el día ($opcion=1)
				if($opcion==0) {
					$reg[10]=substr($reg[10],0,-2);
				}

				if (substr($reg[1],0,3) == substr($vmcliente,0,3)) { 
					$k=1;
					$correlativo = $correlativo + 1;
					if($FechaDiv[1]>=1 && $FechaDiv[1]<10) {
						$correlativo2 = "0".$correlativo;
					}else {
						$correlativo2 = $correlativo;
					}
				} else {
					$k = $k + 1;						
				}
				if(trim($reg[9]) == '') {
					$reg[9] = $xtradat;
				}else{
					$xtradat = trim($reg[9]);
				}		
				if($k<=9){
					$contador = "000".$k;
				}elseif($k>9 and $k<=99){
					$contador = "00".$k;
				} else {
					$contador = "0".$k;
				}
							
				$dfecdoc = "20".substr($reg[0],0,2).substr($reg[0],2,2).substr($reg[0],4,2);

				if(empty($reg[15]))
					$fe_referencia = '01/01/1999';
				else
					$fe_referencia = trim($reg[15]);

				//$centimo2 = $sqlca->query("INSERT INTO tmp_concar_centimo VALUES ('".trim($reg[10])."', '".trim($correlativo2)."', '".trim($contador)."', '".trim($reg[0])."', '".trim($reg[1])."', '".trim($reg[2])."', '".trim($reg[11])."', 'MN', '".trim($reg[5])."', '".trim($reg[6])."', '".trim($reg[13])."', '".trim($reg[9])."', '".trim($reg[0])."', '".trim($reg[0])."', '', 'S', '".$dfecdoc."', '".trim($reg[7])."', '0', '".trim($reg[6])."', 'X', '".$dfecdoc."', '".$dfecdoc."', 'P', ' ');", "concar_insert");	
				$centimo2 = $sqlca->query("INSERT INTO tmp_concar_centimo VALUES ('".trim($reg[10])."', '".trim($correlativo2)."', '".trim($contador)."', '".trim($reg[0])."', '".trim($reg[1])."', '".trim($reg[2])."', '".trim($reg[11])."', 'MN', '".trim($reg[5])."', '".trim($reg[6])."', '".trim($reg[13])."', '".trim($reg[9])."', '".trim($reg[0])."', '".trim($reg[0])."', 'S', 'S', '".$dfecdoc."', '".trim($reg[7])."', '0', '".trim($reg[6])."', 'X', '".$dfecdoc."', '".$dfecdoc."', 'P', ' ', '" . trim($reg[14]) . "', '" . $fe_referencia . "', " . trim($reg[16]) . ", " . trim($reg[17]) . ");", "concar_insert");	
			
			}
		}

		// creando el vector de diferencia
		$c = 0;
		$imp = 0;
		$flag = 0;

		$diferencia = "SELECT * FROM tmp_concar_centimo;";

		if ($sqlca->query($diferencia)>0){
			while ($reg = $sqlca->fetchRow()){
				if (substr($reg[4],0,3) == substr($vmcliente,0,3)){
					if ($flag == 1) {
						$vec[$c] = $imp;
						$c = $c + 1;
					}
					$imp = trim($reg[9]);
				} else {
					$imp = round(($imp-$reg[9]), 2);
					$flag = 1;
				}
			}
			$vec[$c] = 0;
		}			

		// actualizar tabla tmp_concar sumando las diferencias al igv
		$k = 0;
		if ($sqlca->query($diferencia)>0){
			while ($reg = $sqlca->fetchRow()){
				if (trim($reg[4] == $vmimpuesto)){
					$dif = $reg[9] + $vec[$k];
					$k = $k + 1;
					$sale = $sqlca->query("UPDATE tmp_concar_centimo SET dimport = ".$dif." WHERE dcompro = '".trim($reg[1])."' AND dcuenta='$vmimpuesto' and trim(dcodane)='' AND dsubdia = '".trim($reg[0])."';", "queryaux1"); // antes: dcodane='99999999999', con ultimo cambio : dcodane=''
				}
			}
		}

		// pasando la nueva tabla a texto2
		$qfinal = "SELECT * FROM tmp_concar_centimo ORDER BY dsubdia, dcompro, dcuenta, dsecue; ";				
		$arrInserts = Array();
		$pd = 0;
		if ($sqlca->query($qfinal)>0) {
			while ($reg = $sqlca->fetchRow()){
				$sNumeroCuenta = substr($reg['dcuenta'], 0, 2);
				$no_tipo_documento_referencia = '';
				$fe_emision_referencia = "''";
				$sSerieNumeroDocumento = '';
				$fBaseImponible = 0.00;
				$fIGV = 0.00;
				if ( $sNumeroCuenta == '12' ) {
					$sSerieNumeroDocumento = $reg['documento_referencia'];
					$fBaseImponible = $reg['nu_bi_referencia'];
					$fIGV = $reg['nu_igv_referencia'];
					if(!empty($reg['documento_referencia'])){
						if(substr($reg['documento_referencia'],0,1) == 'F')
							$no_tipo_documento_referencia = 'FT';
						else if (substr($reg['documento_referencia'],0,1) == 'B')
							$no_tipo_documento_referencia = 'BV';
					}
					if($reg['fe_referencia'] != '1999-01-01'){
						$fe_emision_referencia = "convert(datetime, '".substr($reg['fe_referencia'],0,19)."', 120)";
					}
				}

				$ins = "
INSERT INTO " . $TabSqlDet . "(
dsubdia, dcompro, dsecue, dfeccom, dcuenta, dcodane, dcencos, dcodmon, ddh, dimport, dtipdoc, dnumdoc, dfecdoc, 
dfecven, darea, dflag, dxglosa, dusimpor, dmnimpor, dcodarc, dcodane2, dtipcam, dmoncom, dcolcom, dbascom, dinacom, 
digvcom, dtpconv, dflgcom, danecom, dtipaco, dmedpag, dtidref, dndoref, dfecref, dbimref, digvref, dtipdor, dnumdor, dfecdo2
) VALUES (
'".$reg[0]."', '".$reg[1]."', '".$reg[2]."', '".$reg[3]."', '".$reg[4]."', '".$reg[5]."', '".$reg[6]."', '".$reg[7]."', '".$reg[8]."', ".$reg[9].", '".$reg[10]."', '".$reg[11]."', '".$reg[13]."',
'".$reg[13]."', '', '".$reg[15]."', '".$reg[17]."', 0,0,'','',0,'','',0,0,
0,'','','','','','".$no_tipo_documento_referencia."','".$sSerieNumeroDocumento."', ".$fe_emision_referencia.", ".$fBaseImponible.",".$fIGV.",'','', GETDATE());
				";
				$arrInserts['tipo']['detalle'][$pd] = $ins;
				$pd++;
			}
		}

		$correlativo=0;
		$mc = 0;
 
		if ($sqlca->query($sql)>0) {
			$correlativo = ($FechaDiv[1] * 10000) + $num_actual;
			while ($reg = $sqlca->fetchRow()) {	

				//reiniciar el numerador cuando sea un dia diferente
				/*
				if($subdia != $reg[10]){
					$correlativo = 0;
					$correlativo = ($FechaDiv[1] * 10000) + $num_actual;
					$subdia = $reg[10];
				}
				*/
				// validando si subdiario se toma completo ($opcion=0) o con el día ($opcion=1)
				if($opcion==0) {
					$reg[10]=substr($reg[10],0,-2);
				}
														
				if (substr($reg[1],0,3) == substr($vmcliente,0,3)) { 					
					$correlativo = $correlativo + 1;

					if($FechaDiv[1]>=1 && $FechaDiv[1]<10){
						$correlativo2 = "0".$correlativo;
					}else {
						$correlativo2 = $correlativo;
					}

					$ins = "INSERT INTO $TabSqlCab 
						( 	CSUBDIA, CCOMPRO, CFECCOM, CCODMON, CSITUA, CTIPCAM, CGLOSA, CTOTAL, CTIPO, CFLAG, CFECCAM, CORIG, CFORM, CTIPCOM, CEXTOR
						) VALUES (
       							'".$reg[10]."', '".$correlativo2."', '".$reg[0]."', 'MN', '', 0, '".$reg[7]."', '".$reg[6]."', 'V', 'S', '','','','',''
						);";
					$arrInserts['tipo']['cabecera'][$mc] = $ins;
					$mc++;	
				}													
			}
		}

		$q5 = $sqlca->query("DROP TABLE tmp_concar_centimo");
		$arrInserts['clientes'] = $clientes[0]["clientes"];

		$rstado = ejecutarInsert($arrInserts, $TabSqlCab, $TabSqlDet,$clientes[1]);

		return $rstado;		
	}
	
	function interface_cobrar_combustible($FechaIni, $FechaFin, $almacen, $codEmpresa, $num_actual) {// CUENTAS POR COBRAR COMBUSTIBLE
		global $sqlca;

		/**
		 * Requerimiento nuevomundo:
		 * Mostrar detalle para documentos factura o boleta pagados con tarjeta. 
		 * Obtenemos cuenta de concar_config_caja si fuera factura o boleta y si pago con tarjeta mostramos dicha cuenta
		 */
		//Obtenemos parametros para version concar
		InterfaceConcarActModel::getVersionConcar();
		$detalle_boletas_nuevomundo = false;
		if ($_SESSION['es_requerimiento_concar_nuevomundo'] == true) {
			$detalle_boletas_nuevomundo = true;
		}

		if(trim($num_actual)=="")
			$num_actual = 0;

		$FechaIni_Original = $FechaIni;
		$FechaFin_Original = $FechaFin;
		$FechaDiv = explode("/", $FechaIni);
		$FechaIni = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];
		$anio1 	  = $FechaDiv[2];
		$mes1 	  = $FechaDiv[1];
		$dia1 	  = $FechaDiv[0];				
		$postrans = "pos_trans".$FechaDiv[2].$FechaDiv[1];
		$FechaDiv = explode("/", $FechaFin);
		$FechaFin = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];
		$anio2 	  = $FechaDiv[2];
		$mes2 	  = $FechaDiv[1];
		$dia2 	  = $FechaDiv[0];				
		$Anio 	  = substr($FechaDiv[2],2,3);
		
		if ($anio1 != $anio2 || $mes1 != $mes2) {
			return 3; // Fechas deben ser mismo mes y año
		} else {
			if ($dia1 > $dia2) 
				return 4; // Fecha inicio debe ser menor que fecha final					
		}

		$Anio		= trim($Anio);
		$codEmpresa	= trim($codEmpresa);
		$TabSqlCab 	= "CC".$codEmpresa.$Anio; // Tabla SQL Cabecera
		$TabSqlDet 	= "CD".$codEmpresa.$Anio; // Tabla SQL Detalle
		$TabCan    	= "CAN".$codEmpresa;

		// VALIDANDO FECHA SI YA HA SIDO MIGRADA O NO		
		$val = InterfaceConcarActModel::verificaFecha("1", $almacen, $FechaIni, $FechaFin);

		if ($val == 5)
			return 5;
		
// 		$sql = "
// SELECT
//  ccobrar_subdiario,
//  venta_cuenta_cliente,
//  venta_cuenta_impuesto,
//  venta_cuenta_ventas,
//  id_cencos_comb,
//  subdiario_dia,
//  cod_cliente,
//  cod_caja
// FROM
//  concar_config;
// 		";

// 		if ($sqlca->query($sql) < 0) 
// 			return false;	
	
// 		$a = $sqlca->fetchRow();
// 		$vcsubdiario = $a[0];
// 		$vccliente   = $a[1];
// 		$vcimpuesto  = $a[2];
// 		$vcventas    = $a[3];	
// 		$vccencos    = $a[4];
// 		$opcion      = $a[5];
// 		$cod_cliente = $a[6];
// 		$cod_caja    = $a[7];
			
		$sql = "
SELECT
	c1.account AS ccobrar_subdiario
	,c2.account AS venta_cuenta_cliente
	,c3.account AS venta_cuenta_impuesto
	,c4.account AS venta_cuenta_ventas
	,c5.account AS id_cencos_comb
	,c6.account AS subdiario_dia
	,c7.account AS cod_cliente
	,c8.account AS cod_caja
FROM
	concar_confignew c1
	LEFT JOIN concar_confignew c2   ON c2.module = 1   AND c2.category = 1   AND c2.subcategory = 0   --Cuenta Combustible Cliente
	LEFT JOIN concar_confignew c3   ON c3.module = 1   AND c3.category = 1   AND c3.subcategory = 1   --Cuenta Combustible BI
	LEFT JOIN concar_confignew c4   ON c4.module = 1   AND c4.category = 1   AND c4.subcategory = 2   --Cuenta Combustible Ventas
	
	LEFT JOIN concar_confignew c5   ON c5.module = 0   AND c5.category = 2   AND c5.subcategory = 0   --Centro de Costo Combustible
	LEFT JOIN concar_confignew c6   ON c6.module = 0   AND c6.category = 1   AND c6.subcategory = 0   --Subdiario dia
	
	LEFT JOIN concar_confignew c7   ON c7.module = 0   AND c7.category = 1   AND c7.subcategory = 1   --Codigo de Cliente
	LEFT JOIN concar_confignew c8   ON c8.module = 0   AND c8.category = 1   AND c8.subcategory = 2   --Codigo de Caja
WHERE
	c1.module = 3   AND c1.category = 0   AND c1.subcategory = 0;   --Subdiario de Cta Cobrar Combustible
		";

		if ($sqlca->query($sql) < 0) 
			return false;	
	
		$a = $sqlca->fetchRow();
		$vcsubdiario = $a[0];
		$vccliente   = $a[1];
		$vcimpuesto  = $a[2];
		$vcventas    = $a[3];	
		$vccencos    = $a[4];
		$opcion      = $a[5];
		$cod_cliente = $a[6];
		$cod_caja    = $a[7];

		$sql = "
		SELECT * FROM (
		SELECT
			to_char(date(dia),'YYMMDD') as dia, 		
			'$vccliente'::text as DCUENTA, 
			'$cod_cliente'::text as codigo, 
			' '::text as trans, 
			'1'::text as tip, 
			'D'::text as ddh, 
			ROUND(SUM(importe),2) as importe, 
			'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta,
			es as sucursal, 
			'$cod_caja'|| caja || '-' ||MIN(cast(trans as integer))||'-'||MAX(cast(trans as integer))::text as dnumdoc,
			'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
			'$vccencos'::text as DCENCOS,
			'A'::text as tip2,
			at as tarjeta,
			'B'::text as tipo,
			'TK'::TEXT AS doctype,
			'COMBU'::TEXT AS No_Tipo_Producto,
			''::TEXT AS documento_referencia
		FROM 
			$postrans
		WHERE 
			td			= 'B' 
			AND tipo	= 'C'
			AND codigo	!= '11620307'
			AND es 		= '$almacen'
			AND at		= ''
			AND tm 		= 'V'
			AND usr 	= ''
			AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
			AND trans NOT IN (SELECT
				rendi_gln
			FROM
				$postrans
			WHERE
				td = 'B' 
				AND tipo	= 'C'
				AND codigo	!= '11620307'
				AND es 		= '$almacen'
				AND tm 		= 'A'
				AND usr 	= ''
				AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
				AND rendi_gln IN(
				SELECT
					trans
				FROM
					$postrans
				WHERE 
					td = 'B' 
					AND tipo	= 'C'
					AND codigo	!= '11620307'
					AND es 		= '$almacen'
					AND at		= ''
					AND tm 		= 'V'
					AND usr 	= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
				)
			)
		GROUP BY 
			at,
			dia,
			es,
			caja
		) as A
		UNION
		(
		SELECT
			to_char(date(dia),'YYMMDD') as dia, 		
			'$vccliente'::text as DCUENTA, 
			'$cod_cliente'::text as codigo, 
			' '::text as trans, 
			'1'::text as tip, 
			'D'::text as ddh, 
			ROUND(SUM(importe),2) as importe, 
			'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
			es as sucursal, 
			'$cod_caja'|| caja || '-' ||cast(trans as integer)::text as dnumdoc,
			'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
			'$vccencos'::text as DCENCOS,
			'A'::text as tip2,
			at as tarjeta,
			'B'::text as tipo,
			'TK'::TEXT AS doctype,
			'COMBU'::TEXT AS No_Tipo_Producto,
			''::TEXT AS documento_referencia
		FROM 
			$postrans 
		WHERE 
			td			= 'B' 
			AND tipo	= 'C'
			AND codigo	!= '11620307' 
			AND es 		= '$almacen'
			AND at		!= ''
			AND tm 		= 'V'
			AND usr 	= ''
			AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
			AND trans NOT IN (SELECT
				rendi_gln
			FROM
				$postrans
			WHERE
				td = 'B' 
				AND tipo	= 'C'
				AND codigo	!= '11620307'
				AND es 		= '$almacen'
				AND tm 		= 'A'
				AND usr 	= ''
				AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
				AND rendi_gln IN(
				SELECT
					trans
				FROM
					$postrans
				WHERE 
					td = 'B' 
					AND tipo	= 'C'
					AND codigo	!= '11620307'
					AND es 		= '$almacen'
					AND at		!= ''
					AND tm 		= 'V'
					AND usr 	= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
				)
			)
		GROUP BY 
			trans,
			at,
			dia,
			es,
			caja
		)
		UNION
		(
		SELECT
			to_char(date(dia),'YYMMDD') as dia, 
			'$vcimpuesto'::text as DCUENTA, 
			'$cod_cliente'::text as codigo, 
			' '::text as trans, 
			'1'::text as tip, 
			'H'::text as ddh, 
			round(sum(igv),2) as importe, 
			'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
			es as sucursal , 
			'$cod_caja'|| caja || '-' ||MIN(cast(trans as integer))||'-'||MAX(cast(trans as integer))::text as dnumdoc,
			'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
			'$vccencos'::text as DCENCOS,
			'A'::text as tip2,
			at as tarjeta,
			'B'::text as tipo,
			'TK'::TEXT AS doctype,
			'COMBU'::TEXT AS No_Tipo_Producto,
			''::TEXT AS documento_referencia
		FROM 
			$postrans 
		WHERE 
			td 			= 'B'
			AND tipo	= 'C'
			AND codigo	!= '11620307'
			AND es 		= '$almacen'
			AND tm 		= 'V'
			AND usr 	= ''
			AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
			AND trans NOT IN (SELECT rendi_gln FROM $postrans WHERE
			td = 'B' 
			AND tipo	= 'C'
			AND codigo	!= '11620307'
			AND es 		= '$almacen'
			AND tm 		= 'A'
			AND usr 	= ''
			AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin')
		GROUP BY 
			at,
			dia,
			es,
			caja
		)
		UNION
		(
		SELECT 
			to_char(date(dia),'YYMMDD') as dia, 
			'$vcventas'::text as DCUENTA, 
			codigo_concar, 
			' '::text as trans, 
			'1'::text as tip, 
			'H'::text as ddh, 
			round(sum(importe-igv),2) as importe, 
			'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
			es as sucursal , 				
			''::text as dnumdoc,
			'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
			'$vccencos'::text as DCENCOS,
			'A'::text as tip2,
			at as tarjeta,
			'B'::text as tipo,
			'TK'::TEXT AS doctype,
			'COMBU'::TEXT AS No_Tipo_Producto,
			''::TEXT AS documento_referencia
		FROM 
			$postrans p
			LEFT JOIN interface_equivalencia_producto i ON(p.codigo = i.art_codigo)
		WHERE 
			td 			= 'B'
			AND tipo	= 'C'
			AND codigo	!= '11620307'
			AND es 		= '$almacen'
			AND tm 		= 'V'
			AND usr 	= ''
			AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
			AND trans NOT IN (SELECT rendi_gln FROM $postrans WHERE
			td = 'B' 
			AND tipo	= 'C'
			AND codigo	!= '11620307'
			AND es 		= '$almacen'
			AND tm 		= 'A'
			AND usr 	= ''
			AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin')
		GROUP BY 
			at,
			dia,
			codigo_concar,
			es,
			caja
		)
		UNION
		(
		SELECT 
			to_char(date(dia),'YYMMDD') as dia, 		
			'$vccliente'::text as DCUENTA, 
			'$cod_cliente'::text as codigo, 
			' '::text as trans, 
			'1'::text as tip, 
			'D'::text as ddh, 
			round(sum(importe),2) as importe, 
			'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
			es as sucursal, 
			'$cod_caja'|| caja || '-' ||MIN(cast(trans as integer))||'-'||MAX(cast(trans as integer))::text as dnumdoc,
			'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
			'$vccencos'::text as DCENCOS,
			'B'::text as tip2,
			at as tarjeta,
			'B'::text as tipo,
			'TK'::TEXT AS doctype,
			'GLP'::TEXT AS No_Tipo_Producto,
			''::TEXT AS documento_referencia
		FROM 
			$postrans 
		WHERE
			td 			= 'B'
			AND tipo 	= 'C'
			AND codigo	= '11620307'
			AND es 		= '$almacen'
			AND at 		= ''
			AND tm 		= 'V'
			AND usr 	= ''
			AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
			AND trans NOT IN (SELECT
				rendi_gln
			FROM
				$postrans
			WHERE
				td = 'B' 
				AND tipo	= 'C'
				AND codigo	= '11620307'
				AND es 		= '$almacen'
				AND tm 		= 'A'
				AND usr 	= ''
				AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
				AND rendi_gln IN(
				SELECT
					trans
				FROM
					$postrans
				WHERE 
					td = 'B' 
					AND tipo	= 'C'
					AND codigo	= '11620307'
					AND es 		= '$almacen'
					AND at		= ''
					AND tm 		= 'V'
					AND usr 	= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
				)
			)
		GROUP BY 
			at,
			dia,
			es,
			caja
		)
		UNION
		(
		SELECT
			to_char(date(dia),'YYMMDD') as dia, 		
			'$vccliente'::text as DCUENTA, 
			'$cod_cliente'::text as codigo, 
			' '::text as trans, 
			'1'::text as tip, 
			'D'::text as ddh, 
			round(sum(importe),2) as importe, 
			'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
			es as sucursal, 
			'$cod_caja'|| caja || '-' ||cast(trans as integer)::text as dnumdoc,
			'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
			'$vccencos'::text as DCENCOS,
			'B'::text as tip2,
			at as tarjeta,
			'B'::text as tipo,
			'TK'::TEXT AS doctype,
			'GLP'::TEXT AS No_Tipo_Producto,
			''::TEXT AS documento_referencia
		FROM 
			$postrans 
		WHERE
			td 			= 'B'
			AND tipo 	= 'C'
			AND codigo	= '11620307'
			AND es 		= '$almacen'
			AND at 		!= ''
			AND tm 		= 'V'
			AND usr 	= ''
			AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
			AND trans NOT IN (SELECT
				rendi_gln
			FROM
				$postrans
			WHERE
				td = 'B' 
				AND tipo	= 'C'
				AND codigo	= '11620307'
				AND es 		= '$almacen'
				AND tm 		= 'A'
				AND usr 	= ''
				AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
				AND rendi_gln IN(
				SELECT
					trans
				FROM
					$postrans
				WHERE 
					td = 'B' 
					AND tipo	= 'C'
					AND codigo	= '11620307'
					AND es 		= '$almacen'
					AND at		!= ''
					AND tm 		= 'V'
					AND usr 	= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
				)
			)
		GROUP BY 
			trans,
			at,
			dia,
			es,
			caja
		)
		UNION
		(
		SELECT
			to_char(date(dia),'YYMMDD') as dia, 
			'$vcimpuesto'::text as DCUENTA, 
			'$cod_cliente'::text as codigo, 
			' '::text as trans, 
			'1'::text as tip, 
			'H'::text as ddh, 
			round(sum(igv),2) as importe, 
			'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
			es as sucursal , 
			'$cod_caja'|| caja || '-' ||MIN(cast(trans as integer))||'-'||MAX(cast(trans as integer))::text as dnumdoc,
			'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
			'$vccencos'::text as DCENCOS,
			'B'::text as tip2,
			at as tarjeta,
			'B'::text as tipo,
			'TK'::TEXT AS doctype,
			'GLP'::TEXT AS No_Tipo_Producto,
			''::TEXT AS documento_referencia
		FROM 
			$postrans 
		WHERE
			td 		= 'B'
			AND tipo	= 'C'
			AND codigo	= '11620307'
			AND es 		= '$almacen'
			AND tm 		= 'V'
			AND usr 	= ''
			AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
			AND trans NOT IN (SELECT rendi_gln FROM $postrans WHERE
			td = 'B' 
			AND tipo	= 'C'
			AND codigo	= '11620307'
			AND es 		= '$almacen'
			AND tm 		= 'A'
			AND usr 	= ''
			AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin')
		GROUP BY 
			at,
			dia,
			es,
			caja
		)
		UNION
		(
		SELECT 
			to_char(date(dia),'YYMMDD') as dia, 
			'$vcventas'::text as DCUENTA, 
			codigo_concar, 
			' '::text as trans, 
			'1'::text as tip, 
			'H'::text as ddh, 
			round(round(sum(importe),2)-round(sum(igv),2),2) as importe, 
			'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
			es as sucursal , 				
			''::text as dnumdoc,
			'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
			'$vccencos'::text as DCENCOS,
			'B'::text as tip2,
			at as tarjeta,
			'B'::text as tipo,
			'TK'::TEXT AS doctype,
			'GLP'::TEXT AS No_Tipo_Producto,
			''::TEXT AS documento_referencia
		FROM 
			$postrans p
			LEFT JOIN interface_equivalencia_producto i ON(p.codigo = i.art_codigo)
		WHERE 
			td 			= 'B'
			AND tipo	= 'C'
			AND codigo	= '11620307'
			AND es 		= '$almacen'
			AND tm 		= 'V'
			AND usr 	= ''
			AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
			AND trans NOT IN (SELECT rendi_gln FROM $postrans WHERE
			td = 'B' 
			AND tipo	= 'C'
			AND codigo	= '11620307'
			AND es 		= '$almacen'
			AND tm 		= 'A'
			AND usr 	= ''
			AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin')
		GROUP BY 
			at,
			dia,
			codigo_concar,
			trans,
			caja,
			sucursal
		)--FIN DE TICKETS BOLETAS DE VENTAS";
		
		if($detalle_boletas_nuevomundo == true){
			$sql .= "	
			UNION--INICIO DE BOLETAS ELECTRONICAS DE VENTAS
			(
				--BOLETAS PAGADAS EN EFECTIVO (COMBUSTIBLE)
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 		
					'$vccliente'::text as DCUENTA, 
					'$cod_cliente'::text as codigo, 
					' '::text as trans, 
					'1'::text as tip, 
					'D'::text as ddh, 
					ROUND(SUM(importe),2) as importe, 
					'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '/' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
					es as sucursal,
					SUBSTR(TRIM(usr), 0, 5) || '-' || MIN(SUBSTR(TRIM(usr), 7)) || '/' || MAX(SUBSTR(TRIM(usr), 7))::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'A'::text as tip2,
					at as tarjeta,
					'B'::text as tipo,
					'BV'::TEXT AS doctype,
					'COMBU'::TEXT AS No_Tipo_Producto,
					''::TEXT AS documento_referencia
				FROM 
					$postrans 
				WHERE 
					td			= 'B' 
					AND tipo	= 'C'
					AND codigo	!= '11620307' 
					AND es 		= '$almacen'
					AND at		= ''
					AND tm 		= 'V'
					AND usr 	!= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY 
					at,
					dia,
					es,
					caja,
					SUBSTR(TRIM(usr), 0, 5)
			)
			UNION
			(
				--BOLETAS PAGADAS CON TARJETA (COMBUSTIBLE)
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 		
					'$vccliente'::text as DCUENTA, 
					'$cod_cliente'::text as codigo, 
					' '::text as trans, 
					'1'::text as tip, 
					'D'::text as ddh, 
					round(sum(importe),2) as importe, 
					'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
					es as sucursal,
					SUBSTR(TRIM(usr), 0, 5) || '-' ||SUBSTR(TRIM(usr), 7)::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'B'::text as tip2,
					at as tarjeta,
					'B'::text as tipo,
					'BV'::TEXT AS doctype,
					'COMBU'::TEXT AS No_Tipo_Producto,
					''::TEXT AS documento_referencia
				FROM 
					$postrans 
				WHERE
					td 			= 'B'
					AND tipo 	= 'C'
					AND codigo	!= '11620307'
					AND es 		= '$almacen'
					AND at 		!= ''
					AND tm 		= 'V'
					AND usr 	!= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY
					at,
					dia,
					es,
					caja,
					usr
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vcimpuesto'::text as DCUENTA, 
					'$cod_cliente'::text as codigo, 
					' '::text as trans, 
					'1'::text as tip, 
					'H'::text as ddh, 
					round(sum(igv),2) as importe, 
					'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
					es as sucursal , 
					SUBSTR(TRIM(usr), 0, 5) || '-' || MIN(SUBSTR(TRIM(usr), 7)) || '/' || MAX(SUBSTR(TRIM(usr), 7))::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'A'::text as tip2,
					at as tarjeta,
					'B'::text as tipo,
					'BV'::TEXT AS doctype,
					'COMBU'::TEXT AS No_Tipo_Producto,
					''::TEXT AS documento_referencia
				FROM 
					$postrans 
				WHERE 
					td 			= 'B'
					AND tipo	= 'C'
					AND codigo	!= '11620307'
					AND es 		= '$almacen'
					AND tm 		= 'V'
					AND usr 	!= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
				GROUP BY 
					at,
					dia,
					es,
					caja,
					SUBSTR(TRIM(usr), 0, 5)
			)
			UNION
			(	SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vcventas'::text as DCUENTA, 
					codigo_concar, 
					' '::text as trans, 
					'1'::text as tip, 
					'H'::text as ddh, 
					round(sum(importe-igv),2) as importe, 
					'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
					es as sucursal , 				
					''::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'A'::text as tip2,
					at as tarjeta,
					'B'::text as tipo,
					'BV'::TEXT AS doctype,
					'COMBU'::TEXT AS No_Tipo_Producto,
					''::TEXT AS documento_referencia
				FROM 
					$postrans p
					LEFT JOIN interface_equivalencia_producto i ON(p.codigo = i.art_codigo)
				WHERE 
					td 		= 'B'
					AND tipo	= 'C'
					AND codigo	!= '11620307'
					AND es 		= '$almacen'
					AND tm 		= 'V'
					AND usr 	!= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY 
					at,
					dia,
					codigo_concar,
					es,
					caja
			)
			UNION--FIN DE COMBUSTIBLES Y EMPIEZA GLP
			(
				--BOLETAS PAGADAS EN EFECTIVO (GLP)
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 		
					'$vccliente'::text as DCUENTA, 
					'$cod_cliente'::text as codigo, 
					' '::text as trans, 
					'1'::text as tip, 
					'D'::text as ddh, 
					round(sum(importe),2) as importe, 
					'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '/' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
					es as sucursal, 
					SUBSTR(TRIM(usr), 0, 5) || '-' || MIN(SUBSTR(TRIM(usr), 7)) || '/' || MAX(SUBSTR(TRIM(usr), 7))::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'B'::text as tip2,
					at as tarjeta,
					'B'::text as tipo,
					'BV'::TEXT AS doctype,
					'GLP'::TEXT AS No_Tipo_Producto,
					''::TEXT AS documento_referencia
				FROM 
					$postrans 
				WHERE
					td 			= 'B'
					AND tipo 	= 'C'
					AND codigo	= '11620307'
					AND es 		= '$almacen'
					AND at 		= ''
					AND tm 		= 'V'
					AND usr 	!= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY
					at,
					dia,
					es,
					caja,
					SUBSTR(TRIM(usr), 0, 5)
			)
			UNION
			(
				--BOLETAS PAGADAS CON TARJETA (GLP)
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 		
					'$vccliente'::text as DCUENTA, 
					'$cod_cliente'::text as codigo, 
					' '::text as trans, 
					'1'::text as tip, 
					'D'::text as ddh, 
					round(sum(importe),2) as importe, 
					'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
					es as sucursal, 
					SUBSTR(TRIM(usr), 0, 5) || '-' ||SUBSTR(TRIM(usr), 7)::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'B'::text as tip2,
					at as tarjeta,
					'B'::text as tipo,
					'BV'::TEXT AS doctype,
					'GLP'::TEXT AS No_Tipo_Producto,
					''::TEXT AS documento_referencia
				FROM 
					$postrans 
				WHERE
					td 			= 'B'
					AND tipo 	= 'C'
					AND codigo	= '11620307'
					AND es 		= '$almacen'
					AND at 		!= ''
					AND tm 		= 'V'
					AND usr 	!= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY
					at,
					dia,
					es,
					caja,
					usr
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vcimpuesto'::text as DCUENTA, 
					'$cod_cliente'::text as codigo, 
					' '::text as trans, 
					'1'::text as tip, 
					'H'::text as ddh, 
					round(sum(igv),2) as importe, 
					'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
					es as sucursal,
					SUBSTR(TRIM(usr), 0, 5) || '-' || MIN(SUBSTR(TRIM(usr), 7)) || '/' || MAX(SUBSTR(TRIM(usr), 7))::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'B'::text as tip2,
					at as tarjeta,
					'B'::text as tipo,
					'BV'::TEXT AS doctype,
					'GLP'::TEXT AS No_Tipo_Producto,
					''::TEXT AS documento_referencia
				FROM 
					$postrans 
				WHERE
					td 			= 'B'
					AND tipo	= 'C'
					AND codigo	= '11620307'
					AND es 		= '$almacen'
					AND tm 		= 'V'
					AND usr 	!= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
				GROUP BY 
					at,
					dia,
					es,
					caja,
					SUBSTR(TRIM(usr), 0, 5)
			)
			UNION
			(	SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vcventas'::text as DCUENTA, 
					codigo_concar, 
					' '::text as trans, 
					'1'::text as tip, 
					'H'::text as ddh, 
					round(sum(importe-igv),2) as importe, 
					'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
					es as sucursal , 				
					''::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'A'::text as tip2,
					at as tarjeta,
					'B'::text as tipo,
					'BV'::TEXT AS doctype,
					'GLP'::TEXT AS No_Tipo_Producto,
					''::TEXT AS documento_referencia
				FROM 
					$postrans p
					LEFT JOIN interface_equivalencia_producto i ON(p.codigo = i.art_codigo)
				WHERE 
					td 			= 'B'
					AND tipo	= 'C'
					AND codigo	= '11620307'
					AND es 		= '$almacen'
					AND tm 		= 'V'
					AND usr 	!= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY 
					at,
					dia,
					codigo_concar,
					es,
					caja
			)--FIN DE BOLETAS ELECTRONICAS DE VENTAS
			";
		}else{
			$sql .= "
			UNION--INICIO DE BOLETAS ELECTRONICAS DE VENTAS ¡¡¡AGRUPADAS COMO ASIENTOS DE VENTAS COMBUSTIBLES!!!
			(				
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 		
					'$vccliente'::text as DCUENTA, 
					'$cod_cliente'::text as codigo, 
					' '::text as trans, 
					'1'::text as tip, 
					'D'::text as ddh, 
					ROUND(SUM(importe),2) as importe, 
					'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '/' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| '' ::text as venta , 
					t.es as sucursal,
					SUBSTR(TRIM(t.usr), 0, 5) || '-' || MIN(SUBSTR(TRIM(t.usr), 7)) || '/' || MAX(SUBSTR(TRIM(t.usr), 7))::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'A'::text as tip2,
					'' as tarjeta,
					'B'::text as tipo,
					'BV'::TEXT AS doctype,
					'COMBU'::TEXT AS No_Tipo_Producto,
					''::TEXT AS documento_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					td			= 'B' 
					AND tipo	= 'C'
					AND codigo	!= '11620307' 
					AND es 		= '$almacen'	
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 				
					AND t.usr 	!= ''	
					AND t.tm    = 'V'									
				GROUP BY 
					dia,
					es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos,
					SUBSTR(TRIM(usr), 0, 5)
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 		
					'$vccliente'::text as DCUENTA, 
					'$cod_cliente'::text as codigo, 
					' '::text as trans, 
					'1'::text as tip, 
					'D'::text as ddh, 
					ROUND(SUM(importe),2) as importe, 
					'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '/' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| '' ::text as venta , 
					t.es as sucursal,
					SUBSTR(TRIM(t.usr), 0, 5) || '-' || MIN(SUBSTR(TRIM(t.usr), 7)) || '/' || MAX(SUBSTR(TRIM(t.usr), 7))::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'A'::text as tip2,
					'' as tarjeta,
					'B'::text as tipo,
					'BV'::TEXT AS doctype,
					'GLP'::TEXT AS No_Tipo_Producto,
					''::TEXT AS documento_referencia
				FROM 
					$postrans t
					LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE 
					td			= 'B' 
					AND tipo	= 'C'
					AND codigo	= '11620307' 
					AND es 		= '$almacen'	
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 				
					AND t.usr 	!= ''	
					AND t.tm    = 'V'
				GROUP BY 
					dia,
					es,
					cfp.nu_posz_z_serie,
					cfp.ch_posz_pos,
					SUBSTR(TRIM(usr), 0, 5)
			)--FIN DE BOLETAS ELECTRONICAS DE VENTAS ¡¡¡AGRUPADAS COMO ASIENTOS DE VENTAS COMBUSTIBLES!!!	
			";
		}	
			
		$sql .= "
			UNION--INICIO DE TICKETS FACTURAS DE VENTAS
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vccliente'::text as DCUENTA, 
					ruc::text as codigo, 
					trans::text as trans,
					'1'::text as tip, 
					'D'::text as ddh, 
					round(sum(importe), 2) as importe, 
					'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
					es as sucursal , 
					'$cod_caja'|| caja || '-' ||trans::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'C'::text as tip2,
					at as tarjeta,
					'F'::text as tipo,
					'TK'::TEXT AS doctype,
					'COMBU'::TEXT AS No_Tipo_Producto,
					''::TEXT AS documento_referencia
				FROM 
					$postrans 
				WHERE 
					td 			= 'F' 
					AND tm 		= 'V'
					AND tipo	= 'C'
					AND codigo	!= '11620307'
					AND es 		= '$almacen'
					AND usr 	= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
					AND trans NOT IN (SELECT rendi_gln FROM $postrans WHERE
					td = 'F'
					AND tipo	= 'C'
					AND codigo	!= '11620307'
					AND es 		= '$almacen'
					AND tm 		= 'A'
					AND usr 	= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin')
				GROUP BY 
					at,
					dia,
					ruc,
					trans,
					caja,
					sucursal 
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vcimpuesto'::text as DCUENTA, 
					ruc::text as codigo, 
					trans::text as trans, 
					'1'::text as tip,  
					'H'::text as ddh, 
					round(sum(igv),2) as importe, 
					'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
					es as sucursal , 
					'$cod_caja'|| caja || '-' ||trans::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'C'::text as tip2,
					at as tarjeta,
					'F'::text as tipo,
					'TK'::TEXT AS doctype,
					'COMBU'::TEXT AS No_Tipo_Producto,
					''::TEXT AS documento_referencia
				FROM 
					$postrans 
				WHERE 
					td 			= 'F'
					AND tm 		= 'V'
					AND tipo	= 'C'
					AND codigo 	!= '11620307'
					AND es 		= '$almacen'
					AND usr 	= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
					AND trans NOT IN (SELECT rendi_gln FROM $postrans WHERE
					td = 'F'
					AND tipo	= 'C'
					AND codigo	!= '11620307'
					AND es 		= '$almacen'
					AND tm 		= 'A'
					AND usr 	= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin')
				GROUP BY 
					at,
					dia,
					ruc,
					trans,
					caja,
					sucursal 
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vcventas'::text as DCUENTA, 
					codigo_concar, 
					trans::text as trans,
					'1'::text as tip, 
					'H'::text as ddh, 
					round(round(sum(importe),2)-round(sum(igv),2),2) as importe, 
					'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
					es as sucursal , 
					'$cod_caja'|| caja || '-' ||trans::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'C'::text as tip2,
					at as tarjeta,
					'F'::text as tipo,
					'TK'::TEXT AS doctype,
					'COMBU'::TEXT AS No_Tipo_Producto,
					''::TEXT AS documento_referencia
				FROM 
					$postrans p
					LEFT JOIN interface_equivalencia_producto i ON(p.codigo = i.art_codigo)
				WHERE 
					td			= 'F'
					AND tm		= 'V'
					AND tipo	= 'C'
					AND codigo	!= '11620307'
					AND es 		= '$almacen'
					AND usr 	= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
					AND trans NOT IN (SELECT rendi_gln FROM $postrans WHERE
					td = 'F'
					AND tipo	= 'C'
					AND codigo	!= '11620307'
					AND es 		= '$almacen'
					AND tm 		= 'A'
					AND usr 	= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin')
				GROUP BY 
					at,
					dia,
					codigo_concar,
					trans,
					caja,
					sucursal 
			)
			UNION--FIN DE GLP E INICIO DE SOLO COMBUSTIBLES
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vccliente'::text as DCUENTA, 
					ruc::text as codigo, 
					trans::text as trans,
					'1'::text as tip, 
					'D'::text as ddh, 
					round(sum(importe), 2) as importe, 
					'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
					es as sucursal , 
					'$cod_caja'|| caja || '-' ||trans::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'D'::text as tip2,
					at as tarjeta,
					'F'::text as tipo,
					'TK'::TEXT AS doctype,
					'GLP'::TEXT::TEXT AS No_Tipo_Producto,
					''::TEXT AS documento_referencia
				FROM 
					$postrans 
				WHERE
					td 			= 'F' 
					AND tm 		= 'V'
					AND tipo	= 'C'
					AND codigo	= '11620307'
					AND es 		= '$almacen'
					AND usr 	= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
					AND trans NOT IN (SELECT rendi_gln FROM $postrans WHERE
					td = 'F'
					AND tipo	= 'C'
					AND codigo	= '11620307'
					AND es 		= '$almacen'
					AND tm 		= 'A'
					AND usr 	= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin')
				GROUP BY
					at,
					dia,
					ruc,
					trans,
					caja,
					sucursal 
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vcimpuesto'::text as DCUENTA, 
					ruc::text as codigo, 
					trans::text as trans, 
					'1'::text as tip,  
					'H'::text as ddh, 
					round(sum(igv),2) as importe, 
					'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
					es as sucursal , 
					'$cod_caja'|| caja || '-' ||trans::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'D'::text as tip2,
					at as tarjeta,
					'F'::text as tipo,
					'TK'::TEXT AS doctype,
					'GLP'::TEXT AS No_Tipo_Producto,
					''::TEXT AS documento_referencia
				FROM 
					$postrans 
				WHERE 
					td 			= 'F'
					AND tm 		= 'V'
					AND tipo	= 'C'
					AND codigo	= '11620307'
					AND es 		= '$almacen'
					AND usr 	= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
					AND trans NOT IN (SELECT rendi_gln FROM $postrans WHERE
					td = 'F'
					AND tipo	= 'C'
					AND codigo	= '11620307'
					AND es 		= '$almacen'
					AND tm 		= 'A'
					AND usr 	= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin')
				GROUP BY 
					at,
					dia,
					ruc,
					trans,
					caja,
					sucursal 
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vcventas'::text as DCUENTA, 
					codigo_concar, 
					trans::text as trans,
					'1'::text as tip, 
					'H'::text as ddh, 
					round(round(sum(importe),2)-round(sum(igv),2),2) as importe, 
					'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
					es as sucursal , 
					'$cod_caja'|| caja || '-' ||trans::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'D'::text as tip2,
					at as tarjeta,
					'F'::text as tipo,
					'TK'::TEXT AS doctype,
					'GLP'::TEXT AS No_Tipo_Producto,
					''::TEXT AS documento_referencia
				FROM 
					$postrans p
					LEFT JOIN interface_equivalencia_producto i ON(p.codigo = i.art_codigo)
				WHERE 
					td 			= 'F' 
					AND tm 		= 'V'
					AND tipo	= 'C'
					AND codigo	= '11620307'
					AND es 		= '$almacen'
					AND usr 	= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
					AND trans NOT IN (SELECT rendi_gln FROM $postrans WHERE
					td = 'F'
					AND tipo	= 'C'
					AND codigo	= '11620307'
					AND es 		= '$almacen'
					AND tm 		= 'A'
					AND usr 	= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin')
				GROUP BY 
					at,
					dia,
					codigo_concar,
					trans,
					caja,
					sucursal 
			)--FIN DE TICKETS FACTURAS DE VENTAS
			UNION--INICIO FACTURAS ELECTRONICAS DE VENTAS
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vccliente'::text as DCUENTA, 
					ruc::text as codigo, 
					SUBSTR(TRIM(usr), 6) as trans,
					'1'::text as tip, 
					'D'::text as ddh, 
					round(sum(importe), 2) as importe, 
					'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
					es as sucursal,
					SUBSTR(TRIM(usr), 0, 5) || '-' ||SUBSTR(TRIM(usr), 6)::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'C'::text as tip2,
					at as tarjeta,
					'F'::text as tipo,
					'FT'::TEXT AS doctype,
					'COMBU'::TEXT AS No_Tipo_Producto,
					''::TEXT AS documento_referencia
				FROM 
					$postrans 
				WHERE 
					td 			= 'F' 
					AND tm 		= 'V'
					AND tipo	= 'C'
					AND codigo	!= '11620307'
					AND es 		= '$almacen'
					AND usr 	!= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
				GROUP BY 
					at,
					dia,
					ruc,
					caja,
					sucursal,
					usr
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vcimpuesto'::text as DCUENTA, 
					ruc::text as codigo, 
					SUBSTR(TRIM(usr), 6) as trans,
					'1'::text as tip,  
					'H'::text as ddh, 
					round(sum(igv),2) as importe, 
					'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
					es as sucursal , 
					SUBSTR(TRIM(usr), 0, 5) || '-' ||SUBSTR(TRIM(usr), 6)::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'C'::text as tip2,
					at as tarjeta,
					'F'::text as tipo,
					'FT'::TEXT AS doctype,
					'COMBU'::TEXT AS No_Tipo_Producto,
					''::TEXT AS documento_referencia
				FROM 
					$postrans 
				WHERE 
					td 			= 'F'
					AND tm 		= 'V'
					AND tipo	= 'C'
					AND codigo 	!= '11620307'
					AND es 		= '$almacen'
					AND usr 	!= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
				GROUP BY 
					at,
					dia,
					ruc,
					caja,
					sucursal,
					usr
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vcventas'::text as DCUENTA, 
					codigo_concar, 
					SUBSTR(TRIM(usr), 6) as trans,
					'1'::text as tip, 
					'H'::text as ddh, 
					round(round(sum(importe),2)-round(sum(igv),2),2) as importe, 
					'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
					es as sucursal,
					SUBSTR(TRIM(usr), 0, 5) || '-' ||SUBSTR(TRIM(usr), 6)::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'C'::text as tip2,
					at as tarjeta,
					'F'::text as tipo,
					'FT'::TEXT AS doctype,
					'COMBU'::TEXT AS No_Tipo_Producto,
					''::TEXT AS documento_referencia
				FROM 
					$postrans p
					LEFT JOIN interface_equivalencia_producto i ON(p.codigo = i.art_codigo)
				WHERE 
					td			= 'F'
					AND tm		= 'V'
					AND tipo	= 'C'
					AND codigo	!= '11620307'
					AND es 		= '$almacen'
					AND usr 	!= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
				GROUP BY 
					at,
					dia,
					codigo_concar,
					caja,
					sucursal,
					usr
			)
			UNION--FIN DE GLP E INICIO DE SOLO COMBUSTIBLES
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vccliente'::text as DCUENTA, 
					ruc::text as codigo, 
					SUBSTR(TRIM(usr), 6) as trans,
					'1'::text as tip, 
					'D'::text as ddh, 
					round(sum(importe), 2) as importe, 
					'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
					es as sucursal , 
					SUBSTR(TRIM(usr), 0, 5) || '-' ||SUBSTR(TRIM(usr), 6)::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'D'::text as tip2,
					at as tarjeta,
					'F'::text as tipo,
					'FT'::TEXT AS doctype,
					'GLP'::TEXT AS No_Tipo_Producto,
					''::TEXT AS documento_referencia
				FROM 
					$postrans 
				WHERE
					td 			= 'F' 
					AND tm 		= 'V'
					AND tipo	= 'C'
					AND codigo	= '11620307'
					AND es 		= '$almacen'
					AND usr 	!= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
				GROUP BY
					at,
					dia,
					ruc,
					caja,
					sucursal,
					usr
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vcimpuesto'::text as DCUENTA, 
					ruc::text as codigo, 
					SUBSTR(TRIM(usr), 6) as trans,
					'1'::text as tip,  
					'H'::text as ddh, 
					round(sum(igv),2) as importe, 
					'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
					es as sucursal , 
					SUBSTR(TRIM(usr), 0, 5) || '-' ||SUBSTR(TRIM(usr), 6)::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'D'::text as tip2,
					at as tarjeta,
					'F'::text as tipo,
					'FT'::TEXT AS doctype,
					'GLP'::TEXT AS No_Tipo_Producto,
					''::TEXT AS documento_referencia
				FROM 
					$postrans 
				WHERE 
					td 			= 'F'
					AND tm 		= 'V'
					AND tipo	= 'C'
					AND codigo	= '11620307'
					AND es 		= '$almacen'
					AND usr 	!= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
				GROUP BY 
					at,
					dia,
					ruc,
					caja,
					sucursal,
					usr
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vcventas'::text as DCUENTA, 
					codigo_concar, 
					SUBSTR(TRIM(usr), 6) as trans,
					'1'::text as tip, 
					'H'::text as ddh, 
					round(round(sum(importe),2)-round(sum(igv),2),2) as importe, 
					'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
					es as sucursal , 
					SUBSTR(TRIM(usr), 0, 5) || '-' ||SUBSTR(TRIM(usr), 6)::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'D'::text as tip2,
					at as tarjeta,
					'F'::text as tipo,
					'FT'::TEXT AS doctype,
					'GLP'::TEXT AS No_Tipo_Producto,
					''::TEXT AS documento_referencia
				FROM 
					$postrans p
					LEFT JOIN interface_equivalencia_producto i ON(p.codigo = i.art_codigo)
				WHERE 
					td 			= 'F' 
					AND tm 		= 'V'
					AND tipo	= 'C'
					AND codigo	= '11620307'
					AND es 		= '$almacen'
					AND usr 	!= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY 
					at,
					dia,
					codigo_concar,
					caja,
					sucursal,
					usr
			)--FIN DE FACTURAS ELECTRONICAS DE VENTAS
			UNION--INICIO EXTORNOS LIQUIDOS Y GLP BOLETAS ELECTRONICAS
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vccliente'::text as DCUENTA,
					'$cod_cliente'::text as codigo,
					SUBSTR(TRIM(usr), 6) as trans,
					'1'::text as tip, 
					'D'::text as ddh, 
					-round(sum(importe), 2) as importe, 
					'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
					es as sucursal , 
					SUBSTR(TRIM(usr), 0, 5) || '-' ||SUBSTR(TRIM(usr), 6)::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'E'::text as tip2,
					at as tarjeta,
					'B'::text as tipo,
					'NA'::TEXT AS doctype,
					'COMBU'::TEXT AS No_Tipo_Producto,
					(SELECT SUBSTR(TRIM(usr), 0, 5) || '-' ||SUBSTR(TRIM(usr), 6)::text FROM $postrans pt WHERE pt.trans = FIRST(t.rendi_gln) LIMIT 1) as documento_referencia
				FROM 
					$postrans t
				WHERE
					td 			= 'B'
					AND tm 		= 'A'
					AND tipo	= 'C'
					AND codigo	!= '11620307'
					AND es 		= '$almacen'
					AND usr 	!= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY
					at,
					dia,
					ruc,
					caja,
					sucursal,
					usr
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vccliente'::text as DCUENTA, 
					'$cod_cliente'::text as codigo, 
					SUBSTR(TRIM(usr), 6) as trans,
					'1'::text as tip, 
					'D'::text as ddh, 
					-round(sum(importe), 2) as importe, 
					'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
					es as sucursal,
					SUBSTR(TRIM(usr), 0, 5) || '-' ||SUBSTR(TRIM(usr), 6)::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'E'::text as tip2,
					at as tarjeta,
					'B'::text as tipo,
					'NA'::TEXT AS doctype,
					'GLP'::TEXT AS No_Tipo_Producto,
					(SELECT SUBSTR(TRIM(usr), 0, 5) || '-' ||SUBSTR(TRIM(usr), 6)::text FROM $postrans pt WHERE pt.trans = FIRST(t.rendi_gln) LIMIT 1) as documento_referencia
				FROM 
					$postrans t
				WHERE
					td 			= 'B'
					AND tm 		= 'A'
					AND tipo	= 'C'
					AND codigo	= '11620307'
					AND es		= '$almacen'
					AND usr 	!= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY
					at,
					dia,
					ruc,
					caja,
					sucursal,
					usr
			)--FIN EXTORNOS LIQUIDOS Y GLP BOLETAS ELECTRONICAS
			UNION--INICIO EXTORNOS FACTURAS ELECTRONICAS
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia,
					'$vccliente'::text as DCUENTA,
					ruc::text as codigo,
					SUBSTR(TRIM(usr), 6) as trans,
					'1'::text as tip, 
					'D'::text as ddh, 
					-round(sum(importe), 2) as importe, 
					'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
					es as sucursal , 
					SUBSTR(TRIM(usr), 0, 5) || '-' ||SUBSTR(TRIM(usr), 6)::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'E'::text as tip2,
					at as tarjeta,
					'F'::text as tipo,
					'NA'::TEXT AS doctype,
					'COMBU'::TEXT AS No_Tipo_Producto,
					(SELECT SUBSTR(TRIM(usr), 0, 5) || '-' ||SUBSTR(TRIM(usr), 6)::text FROM $postrans pt WHERE pt.trans = FIRST(t.rendi_gln) LIMIT 1) as documento_referencia
				FROM 
					$postrans t
				WHERE
					td 			= 'F'
					AND tm 		= 'A'
					AND tipo	= 'C'
					AND codigo	!= '11620307'
					AND es 		= '$almacen'
					AND usr 	!= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY
					at,
					dia,
					ruc,
					caja,
					sucursal,
					usr
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vccliente'::text as DCUENTA, 
					ruc::text as codigo, 
					SUBSTR(TRIM(usr), 6) as trans,
					'1'::text as tip, 
					'D'::text as ddh, 
					-round(sum(importe), 2) as importe, 
					'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja ::text as venta , 
					es as sucursal , 
					SUBSTR(TRIM(usr), 0, 5) || '-' ||SUBSTR(TRIM(usr), 6)::text as dnumdoc,
					'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vccencos'::text as DCENCOS,
					'E'::text as tip2,
					at as tarjeta,
					'F'::text as tipo,
					'NA'::TEXT AS doctype,--15 Numero de Columna
					'GLP'::TEXT AS No_Tipo_Producto,
					(SELECT SUBSTR(TRIM(usr), 0, 5) || '-' ||SUBSTR(TRIM(usr), 6)::text FROM $postrans pt WHERE pt.trans = FIRST(t.rendi_gln) LIMIT 1) as documento_referencia
				FROM 
					$postrans t
				WHERE
					td 			= 'F'
					AND tm 		= 'A'
					AND tipo	= 'C'
					AND codigo	= '11620307'
					AND es		= '$almacen'
					AND usr 	!= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY
					at,
					dia,
					ruc,
					caja,
					sucursal,
					usr
			)
			ORDER BY dia, venta, tip2, tip, trans, DCUENTA, ddh;";

		echo "<pre>";
		echo "\n\n CTA. COBRAR CUENTAS: ".$sql."\n\n";
		echo "<pre>";
	
		$q1 = "CREATE TABLE tmp_concar (dsubdia character varying(7), dcompro character varying(8), dsecue character varying(7), dfeccom character varying(6), dcuenta character varying(12), dcodane character varying(18),
			dcencos character varying(6), dcodmon character varying(2), ddh character varying(1), dimport numeric(14,2), dtipdoc character varying(2), dnumdoc character varying(30), 
			dfecdoc character varying(8), dfecven character varying(8), darea character varying(3), dflag character varying(1), ddate date not null, dxglosa character varying(40),
			dusimport numeric(14,2), dmnimpor numeric(14,2), dcodarc character varying(2), dfeccom2 character varying(8), dfecdoc2 character varying(8), dvanexo character varying(1), dcodane2 character varying(18),
			tarjeta character varying(1), td character varying(1), no_tipo_producto character varying(5), doctype character varying(5), docrefer character varying(20));";

		$sqlca->query($q1);

		$correlativo = 0;
		$contador = '0000';  
		$k = 0;       
			
		if ($sqlca->query($sql)<=0) {
			$sqlca->get_error();
			error_log("Estapa 0");
			return NULL;	
		}

		if ($sqlca->query($sql)>0) {
			$correlativo = ($FechaDiv[1] * 10000) + $num_actual;			
			while ($reg = $sqlca->fetchRow()) {	
				
				//reiniciar el numerador cuando sea diferente dia
				/*if($subdia != $reg[10]){
					$correlativo = 0;
					$correlativo = ($FechaDiv[1] * 10000) + $num_actual;
					$subdia = $reg[10];
				}*/

				// validando si subdiario se toma completo ($opcion=0) o con el día ($opcion=1)
				if($opcion==0) {
					$reg[10]=substr($reg[10],0,-2);
				}
																	
				if (substr($reg[1],0,3) == substr($vccliente,0,3)) { 
					$k=1;
					$correlativo = $correlativo + 1;
					if($FechaDiv[1]>=1 && $FechaDiv[1]<10){
						$correlativo2 = "0".$correlativo;
					}else {
						$correlativo2 = $correlativo;
					}
				} else {
					$k = $k+1;						
				}

				if(trim($reg[9]) == ''){
					$reg[9] = $xtradat;
				} else {
					$xtradat = trim($reg[9]);
				}
										
				if($k<=9){
					$contador = "000".$k;
				}elseif($k>9 and $k<=99){
					$contador = "00".$k;
				} else {
					$contador = "0".$k;
				}

				$dfecdoc = "20".substr($reg[0],0,2).substr($reg[0],2,2).substr($reg[0],4,2);				
			
				if($reg[12] == 'E')
					$extorno = "A";
				else
					$extorno = "S";
					
				if(empty($reg[13]) || $reg[13] == '' || $reg[13] == NULL)
					$reg[13] = "0";

				$q2 = $sqlca->query("INSERT INTO tmp_concar VALUES ('".trim($reg[10])."', '".trim($correlativo2)."', '".trim($contador)."', '".trim($reg[0])."', '".trim($reg[1])."', '".trim($reg[2])."', '".trim($reg[11])."', 'MN', '".trim($reg[5])."', '".trim($reg[6])."', '".trim($reg[15])."', '".trim($reg[9])."', '".trim($reg[0])."', '".trim($reg[0])."', '".trim($extorno)."', 'S', '".$dfecdoc."', '".trim($reg[7])."', '0', '".trim($reg[6])."', 'X', '".$dfecdoc."', '".$dfecdoc."', 'P', ' ', '".$reg[13]."', '".trim($reg[14])."', '".trim($reg[16])."', '".trim($reg[15])."', '".trim($reg[17])."');", "concar_insert");

			}
		}	
				
		// creando el vector de diferencia
		$c = 0;
		$imp = 0;
		$flag = 0;
		$que = "SELECT * FROM tmp_concar; ";

		if ($sqlca->query($que)>0){
			while ($reg = $sqlca->fetchRow()){
				if (substr($reg[4],0,3) == substr($vccliente,0,3)){
					if ($flag == 1) {
						$vec[$c] = $imp;
						$c = $c + 1;
					}
					$imp = trim($reg[9]);
				} else {
					$imp = round(($imp-$reg[9]), 2);
					$flag = 1;
				}
			}
			$vec[$c] = 0;
		}
			
		// actualizar tabla tmp_concar sumando las diferencias al igv
		$k = 0;
		if ($sqlca->query($que)>0){
			while ($reg = $sqlca->fetchRow()){
				if (trim($reg[4] == $vcimpuesto)){
					$dif = $reg[9] + $vec[$k];
					$k = $k + 1;
					$sale = $sqlca->query("UPDATE tmp_concar SET dimport = ".$dif." WHERE dcompro = '".trim($reg[1])."' AND dcuenta='$vcimpuesto' and dcodane='99999999999';", "queryaux2");
				}
			}
		}	
		
		// return false; //

		//******************************** CUENTAS POR COBRAR *****************************	
		// Datos de cuenta por cobrar

		// $sql_cc = "SELECT 
		// 				ccobrar_subdiario, 
		// 				ccobrar_cuenta_cliente, 
		// 				ccobrar_cuenta_caja, 
		// 				codane, 
		// 				subdiario_dia,
		// 				ccobrar_cuenta_cliente_new, 
		// 				ccobrar_cuenta_caja_new 
		// 			FROM 
		// 				concar_config;";

		// if ($sqlca->query($sql_cc) < 0){
		// 	error_log('Etapa 1: Obtenemos datos de cuenta por cobrar');
		// 	return false;	
		// }
	
		// $a = $sqlca->fetchRow();
		// $ccsubdiario = $a[0];
		// $cchaber 	= $a[1];
		// $ccdebe 		= $a[2];
		// $codane 		= $a[3];
		// $opcion      = $a[4];
		// $cchaberglp  = $a[5];//GLP
		// $ccdebeglp 	= $a[6];//GLP

		$sql_cc = "
					SELECT 
						c1.account AS ccobrar_subdiario
						,c2.account AS ccobrar_cuenta_cliente
						,c3.account AS ccobrar_cuenta_caja
						,c4.account AS codane
						,c5.account AS subdiario_dia
						,c6.account AS ccobrar_cuenta_cliente_new
						,c7.account AS ccobrar_cuenta_caja_new
					FROM 
						concar_confignew c1
						LEFT JOIN concar_confignew c2 ON   c2.module = 3   AND c2.category = 1   AND c2.subcategory = 0   --Cuenta Cobrar Combustible Cliente
						LEFT JOIN concar_confignew c3 ON   c3.module = 3   AND c3.category = 1   AND c3.subcategory = 1   --Cuenta Cobrar Combustible Caja
						
						LEFT JOIN concar_confignew c4 ON   c4.module = 0   AND c4.category = 3   AND c4.subcategory = 0   --Codigo de Anexo
						LEFT JOIN concar_confignew c5 ON   c5.module = 0   AND c5.category = 1   AND c5.subcategory = 0   --Subdiario dia
						
						LEFT JOIN concar_confignew c6 ON   c6.module = 3   AND c6.category = 2   AND c6.subcategory = 0   --Cuenta Cobrar GLP Cliente
						LEFT JOIN concar_confignew c7 ON   c7.module = 3   AND c7.category = 2   AND c7.subcategory = 1   --Cuenta Cobrar GLP Caja
					WHERE 
						c1.module = 3   AND c1.category = 0   AND c1.subcategory = 0;   --Subdiario de Cta Cobrar Combustible
					";

		if ($sqlca->query($sql_cc) < 0){
			error_log('Etapa 1: Obtenemos datos de cuenta por cobrar');
			return false;	
		}
	
		$a = $sqlca->fetchRow();
		$ccsubdiario 	= $a[0];
		$cchaber 		= $a[1];
		$ccdebe 		= $a[2];
		$codane 		= $a[3];
		$opcion         = $a[4];
		$cchaberglp     = $a[5];//GLP
		$ccdebeglp 		= $a[6];//GLP		

		$where_agrupar_documentos_efectivo = "";

		// Datos de tarjetas para concar					
		if($detalle_boletas_nuevomundo == true){
			$sql_cc = "SELECT
						id_config, idtipodoc, idformapago, descripcion, cuenta10, cuenta12 
					FROM  
						concar_config_caja 
					ORDER BY 
						idtipodoc, idformapago;";
				
			if ($sqlca->query($sql_cc) < 0){
				error_log('Etapa 2: Obtenemos datos de tarjetas para concar');
				return false;
			}

			$data_tarjetas_concar = array();
			while ($reg = $sqlca->fetchRow()) {
				/*
				* TIPO DE DOCUMENTO 
				* B: Boleta 
				* F: Factura
				* 
				* FORMA DE PAGO
				* 0: Ticket efectivo
				* 1, 2, 3, 4, 5: Tipos de tarjetas
				* 
				*/ 
				$data_tarjetas_concar[$reg['idtipodoc'] . "-" . $reg['idformapago']] = $reg;
			}
			error_log(json_encode($data_tarjetas_concar));

			$where_agrupar_documentos_efectivo = "AND tarjeta = '0'";
		}				

		$sqlcc = "
			SELECT * FROM(		
				SELECT 
					dfeccom as dfeccom,
					(CASE WHEN no_tipo_producto = 'COMBU' THEN '$cchaber' ELSE '$cchaberglp' END) AS dcuenta,
					(CASE WHEN no_tipo_producto = 'COMBU' THEN '0002' ELSE '0004' END) as dsecue,
					dcodane as dcodane,
					dcencos as dcencos,
					'H' as ddh,	
					dimport as dimport,
					dnumdoc as dnumdoc,
					'COB. '||(CASE WHEN no_tipo_producto = 'COMBU' THEN 'COMBUSTIBLES' ELSE 'GLP' END) || ' ' || TO_CHAR(ddate, 'DD/MM/YYYY')  as dxglosa,
					'$ccsubdiario'::text as subday,
					'' as doctype,
					tarjeta as tarjeta, --Para identificar el tipo de tarjeta
					td as td, --Para identificar el tipo de documento
					doctype as dtipdoc --Para identificar el tipo de documento formateado (BOLETA: BV, FACTURA: FT, NOTA DE CREDITO: NA)
				FROM 
					tmp_concar 
				WHERE 
					ddh='D' AND dvanexo='P'	AND doctype != 'NA'
				) as A
				UNION
				(
				SELECT 
					dfeccom as dfeccom,
					(CASE WHEN no_tipo_producto = 'COMBU' THEN '$cchaber' ELSE '$cchaberglp' END) AS dcuenta,
					(CASE WHEN no_tipo_producto = 'COMBU' THEN '0002' ELSE '0004' END) as dsecue,
					dcodane as dcodane,
					dcencos as dcencos,
					'D' as ddh,	
					dimport as dimport,
					dnumdoc as dnumdoc, --NOTAS DE CREDITO
					'COB. '||(CASE WHEN no_tipo_producto = 'COMBU' THEN 'COMBUSTIBLES' ELSE 'GLP' END) || ' ' || TO_CHAR(ddate, 'DD/MM/YYYY')  as dxglosa,
					'$ccsubdiario'::text as subday,
					'NA' as doctype,
					tarjeta as tarjeta, --Para identificar el tipo de tarjeta
					td as td, --Para identificar el tipo de documento
					doctype as dtipdoc --Para identificar el tipo de documento formateado (BOLETA: BV, FACTURA: FT, NOTA DE CREDITO: NA)
				FROM 
					tmp_concar 
				WHERE 
					ddh='D' AND dvanexo='P'	AND doctype = 'NA' --NOTAS DE CREDITO
				)
				UNION
				(	
				SELECT 
					t.dfeccom as dfeccom,
					(CASE WHEN t.no_tipo_producto = 'COMBU' THEN '$ccdebe' ELSE '$ccdebeglp' END) AS dcuenta,
					(CASE WHEN t.no_tipo_producto = 'COMBU' THEN '0001' ELSE '0003' END) as dsecue,
					'".$codane."' as dcodane,
					t.dcencos as dcencos,
					'D' as ddh,
					SUM(t.dimport) - ( SELECT 
										 	COALESCE(SUM(tc.dimport),0)
										 FROM 
										 	tmp_concar tc
										 WHERE 
										 	tc.ddh='D' AND tc.dvanexo='P' AND tc.doctype = 'NA'
									 		AND tc.no_tipo_producto = t.no_tipo_producto
									 		AND tc.ddate = t.ddate
								   ) as dimport, --NO SUMAMOS FACTURAS ORIGINALES DE LAS NOTAS DE CREDITO
					'000000' as dnumdoc,
					'COB. '||(CASE WHEN t.no_tipo_producto = 'COMBU' THEN 'COMBUSTIBLES' ELSE 'GLP' END) || ' ' || TO_CHAR(t.ddate, 'DD/MM/YYYY')  as dxglosa,
					'$ccsubdiario'::text as subday,
					'' as doctype,
					'' as tarjeta, --Para identificar el tipo de tarjeta
					'' as td, --Para identificar el tipo de documento
					'VR' as dtipdoc --Para identificar el tipo de documento formateado (BOLETA: BV, FACTURA: FT, NOTA DE CREDITO: NA)
				FROM 
					tmp_concar t
				WHERE 
					t.ddh='D' AND t.dvanexo='P' AND t.doctype != 'NA'
					$where_agrupar_documentos_efectivo --SOLO AGRUPA DOCUMENTOS QUE FUERON PAGADOS EN EFECTIVO
				GROUP BY 
					t.dfeccom,t.dcencos,t.no_tipo_producto, t.ddate
				)
				ORDER BY dfeccom, dsecue, dcuenta, dcodane desc;
		";
			
		echo "<pre>";
		echo "\n\n QUERY FINAL CTAS. COBRAR COMBUSTIBLES: ".$sqlcc."\n\n";			
		echo "</pre>";
		// return false; //
			
		if ($sqlca->query($sqlcc) < 0){
			error_log('Etapa 3: QUERY FINAL CTAS. COBRAR COMBUSTIBLES');
			return false;	
		} 
	
		$md = 0;
		$pc = 0;
		$arrInserts = Array();
		$counter = ($FechaDiv[1] * 10000) + $num_actual;
		$counterserie = ($FechaDiv[1] * 10000) + $num_actual;
		$cons = 0;//secuencial de detalles cobrar
		$diaserie = '';
		//$dxglosa = '';
		$no_documento = null;
		$nu_secue = null;
		$nu_dia = null;
		$cod_dia = null;
		$nu_subdia = null;

		while ($reg = $sqlca->fetchRow()) {

			// validando si subdiario se toma completo ($opcion=0) o con el día ($opcion=1)
			if($opcion==0) {
				$reg[9]=substr($reg[9],0,-2);
			}

			// if($reg[8] != $dxglosa){
			// 	$counter = $counter + 1;
			// 	$dxglosa = $reg[8];
			// }

			if($reg[1] == $ccdebe || $reg[1] == $ccdebeglp){
				$counter = $counter + 1;
			}

			if($FechaDiv[1]>=1 && $FechaDiv[1]<10){
				$secue = "0".$counter;
			} else {
				$secue = $counter;
			}

			$nu_subdia = $reg[9].substr($reg[0], -2);

			if($reg[5]=="D" && $reg[10]==""){ //INGRESAMOS IMPORTE TOTALIZADO EN CABECERA
				$ins1 = "INSERT INTO $TabSqlCab
					(
						CSUBDIA, CCOMPRO, CFECCOM, CCODMON, CSITUA, CTIPCAM, CGLOSA, CTOTAL,CTIPO, CFLAG, CFECCAM, CORIG, CFORM, CTIPCOM, CEXTOR
					) VALUES (
						'".$nu_subdia."', '".$secue."', '".$reg[0]."', 'MN', '', 0, '".$reg[8]."', '".$reg[6]."', 'V', 'S', '', '','','',''
					);";

				$arrInserts['tipo']['cabecera'][$pc] = $ins1;
				$pc++;
			}

			if($secue != $nu_secue){
				$cons = 0;
				$nu_secue = $secue;
			}
			
			if($detalle_boletas_nuevomundo == true){
				//SI ES BOLETA O FACTURA Y SI PAGO CON TARJETA
				if(($reg['td'] == 'B' || $reg['td'] == 'F') && $reg['tarjeta'] != '0' && $reg['doctype'] != 'NA'){ 
					//reiniciar el numerador cuando sea diferente dia DSECUE
					$cons++;
					if($cons>0 and $cons<=9) {
						$secdet = "000".$cons;				
					} elseif ($cons>=10 and $cons<=99){
						$secdet = "00".$cons;
					} elseif ($cons>=100 and $cons<=999){
						$secdet = "0".$cons;
					} else {
						$secdet = $cons;
					}
								
					$cuenta10 = $data_tarjetas_concar[$reg['td'] . "-" . $reg['tarjeta']]['cuenta10']; //Obtenemos cuenta de concar_config_caja si fuera factura y si pago con tarjeta
					$ddh = "D"; //Obtenemos D (Debe) o H (Haber)
					if(empty($cuenta10) || $cuenta10 == '' || $cuenta10 == NULL){
						$cuenta10 = $reg[1];					
						$ddh = "D";
					}
					
					//Agregamos insert adicional para documentos pagados con tarjeta con la cuenta cambiada y obtenida de concar_config_caja
					$ins2 = "INSERT INTO $TabSqlDet 
					(	dsubdia, dcompro, dsecue, dfeccom, dcuenta, dcodane, dcencos, dcodmon, ddh, dimport, dtipdoc, dnumdoc, dfecdoc, dfecven, 
						darea, dflag, dxglosa, dusimpor, dmnimpor, dcodarc, dcodane2, dtipcam, dmoncom, dcolcom, dbascom, dinacom, digvcom, dtpconv, 
						dflgcom, danecom, dtipaco, dmedpag, dtidref, dndoref, dfecref, dbimref, digvref, dtipdor, dnumdor, dfecdo2
					) VALUES ( 
						'".$nu_subdia."', '".$secue."', '".$secdet."', '".$reg[0]."', '".$cuenta10."', '".$reg[3]."', '', 'MN', 
						'".$ddh."', ".$reg[6].", '".$reg[13]."', '".$reg[7]."', '".$reg[0]."', '".$reg[0]."', '','S', '".$reg[8]."', 
						0,0,'','',0,'','',0,0,0,'','','','','','','', GETDATE(), 0,0,'','', GETDATE()
					);";
					$arrInserts['tipo']['detalle'][$md] = $ins2;
					$md++;
				}
				//CERRAR SI ES BOLETA O FACTURA Y SI PAGO CON TARJETA
			}			

			//reiniciar el numerador cuando sea diferente dia DSECUE
			$cons++;

			if($cons>0 and $cons<=9) {
				$secdet = "000".$cons;				
			} elseif ($cons>=10 and $cons<=99){
				$secdet = "00".$cons;
			} elseif ($cons>=100 and $cons<=999){
				$secdet = "0".$cons;
			} else {
				$secdet = $cons;
			}
				
			$cuenta10 = $reg[1]; //Obtenemos cuenta por defecto		
			$ddh = $reg[5]; //Obtenemos D (Debe) o H (Haber)

			$ins2 = "INSERT INTO $TabSqlDet 
				(	dsubdia, dcompro, dsecue, dfeccom, dcuenta, dcodane, dcencos, dcodmon, ddh, dimport, dtipdoc, dnumdoc, dfecdoc, dfecven, 
					darea, dflag, dxglosa, dusimpor, dmnimpor, dcodarc, dcodane2, dtipcam, dmoncom, dcolcom, dbascom, dinacom, digvcom, dtpconv, 
					dflgcom, danecom, dtipaco, dmedpag, dtidref, dndoref, dfecref, dbimref, digvref, dtipdor, dnumdor, dfecdo2
				) VALUES ( 
					'".$nu_subdia."', '".$secue."', '".$secdet."', '".$reg[0]."', '".$cuenta10."', '".$reg[3]."', '', 'MN', 
					'".$ddh."', ".$reg[6].", '".$reg[13]."', '".$reg[7]."', '".$reg[0]."', '".$reg[0]."', '','S', '".$reg[8]."', 
					0,0,'','',0,'','',0,0,0,'','','','','','','', GETDATE(), 0,0,'','', GETDATE()
				);";
		
			$arrInserts['tipo']['detalle'][$md] = $ins2;

			$md++;

		}

		$q5 = $sqlca->query("DROP TABLE tmp_concar");
		$rstado = ejecutarInsert($arrInserts, $TabSqlCab, $TabSqlDet, '');
		return $rstado;
	}

	function interface_liquidacion_caja($FechaIni, $FechaFin, $almacen, $codEmpresa, $num_actual) {// CUENTAS LIQUIDACION DE CAJA
		global $sqlca;

		if(trim($num_actual)=="")
			$num_actual = 0;

		$FechaIni_Original = $FechaIni;
		$FechaFin_Original = $FechaFin;
		$FechaDiv = explode("/", $FechaIni);
		$FechaIni = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];
		$anio1 	  = $FechaDiv[2];
		$mes1 	  = $FechaDiv[1];
		$dia1 	  = $FechaDiv[0];				
		$postrans = "pos_trans".$FechaDiv[2].$FechaDiv[1];
		$FechaDiv = explode("/", $FechaFin);
		$FechaFin = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];
		$anio2 	  = $FechaDiv[2];
		$mes2 	  = $FechaDiv[1];
		$dia2 	  = $FechaDiv[0];				
		$Anio 	  = substr($FechaDiv[2],2,3);
		
		if ($anio1 != $anio2 || $mes1 != $mes2) {
			return 3; // Fechas deben ser mismo mes y año
		} else {
			if ($dia1 > $dia2) 
				return 4; // Fecha inicio debe ser menor que fecha final					
		}

		$Anio		= trim($Anio);
		$codEmpresa	= trim($codEmpresa);
		$TabSqlCab 	= "CC".$codEmpresa.$Anio; // Tabla SQL Cabecera
		$TabSqlDet 	= "CD".$codEmpresa.$Anio; // Tabla SQL Detalle
		$TabCan    	= "CAN".$codEmpresa;

		// VALIDANDO FECHA SI YA HA SIDO MIGRADA O NO		
		$val = InterfaceConcarActModel::verificaFecha("1", $almacen, $FechaIni, $FechaFin);

		if ($val == 5)
			return 5;

// 		$sql = "
// SELECT 
//  venta_subdiario,
//  --venta_cuenta_cliente,
//  --venta_cuenta_impuesto,
//  --venta_cuenta_ventas,
//  id_cencos_comb,
//  subdiario_dia,
//  cod_cliente,
//  cod_caja
// FROM
//  concar_config;
// 		";

// 		if ($sqlca->query($sql) < 0) 
// 			return false;	
	
// 		$a = $sqlca->fetchRow();
// 		$vcsubdiario = '71'; //Esto puede traerse de forma dinamica, ya sea ventas, cobranzas, etc
// 		//$vccliente   = $a[1];
// 		//$vcimpuesto  = $a[2];
// 		//$vcventas    = $a[3];
// 		$vccencos    = $a[1];
// 		$opcion      = $a[2];
// 		$cod_cliente = $a[3];
// 		$cod_caja    = $a[4];

		//OBTENEMOS CUENTAS DEL MODULO GLOBALES
		$sql = "
			SELECT
				TRIM(c1.account) as subdiario_liquidacion
				,TRIM(c2.account) as id_cencos_comb 
				,TRIM(c3.account) as subdiario_dia 
				,TRIM(c4.account) as cod_cliente
				,TRIM(c5.account) as cod_caja
			FROM
				concar_confignew c1 
				LEFT JOIN concar_confignew c2 ON   c2.module = 0   AND c2.category = 2   AND c2.subcategory = 0   --Centro de Costo Combustible
				LEFT JOIN concar_confignew c3 ON   c3.module = 0   AND c3.category = 1   AND c3.subcategory = 0   --Subdiario dia
				LEFT JOIN concar_confignew c4 ON   c4.module = 0   AND c4.category = 1   AND c4.subcategory = 1   --Codigo de Cliente
				LEFT JOIN concar_confignew c5 ON   c5.module = 0   AND c5.category = 1   AND c5.subcategory = 2   --Codigo de Caja
			WHERE
				c1.module = 8   AND c1.category = 0   AND c1.subcategory = 0;   --Subdiario de Liquidacion de Caja
		";

		if ($sqlca->query($sql) < 0) 
			return false;	
	
		$a = $sqlca->fetchRow();
		$vcsubdiario = $a[0];
		$vccencos    = $a[1];
		$opcion      = $a[2];
		$cod_cliente = $a[3];
		$cod_caja    = $a[4];	
		//CERRAR OBTENEMOS CUENTAS DEL MODULO GLOBALES	

		//OBTENEMOS CUENTAS DEL MODULO LIQUIDACION DE CAJA
		$sql = "
			SELECT
				TRIM(c1.account) as combustible_cuenta_caja
				,TRIM(c2.account) as glp_cuenta_caja
				,TRIM(c3.account) as market_cuenta_caja
				,TRIM(c4.account) as cuenta_ticket_efectivo
				,TRIM(c5.account) as cuenta_tarjeta_visa
				,TRIM(c6.account) as cuenta_tarjeta_american_express
				,TRIM(c7.account) as cuenta_tarjeta_mastercard
				,TRIM(c8.account) as cuenta_tarjeta_dinners
				,TRIM(c9.account) as cuenta_tarjeta_cmr
				,TRIM(c10.account) as cuenta_tarjeta_ripley
				,TRIM(c11.account) as cuenta_tarjeta_cheques_otros
				,TRIM(c12.account) as cuenta_tarjeta_cheques_bbva
				,TRIM(c13.account) as cuenta_tarjeta_metroplazos
				,TRIM(c14.account) as combustible_cod_anexo
				,TRIM(c15.account) as glp_cod_anexo
				,TRIM(c16.account) as market_cod_anexo				
			FROM
				concar_confignew c1 
				LEFT JOIN concar_confignew c2 ON   c2.module = 8   AND c2.category = 1   AND c2.subcategory = 1   --Cuenta Caja GLP
				LEFT JOIN concar_confignew c3 ON   c3.module = 8   AND c3.category = 1   AND c3.subcategory = 2   --Cuenta Caja Market				
				LEFT JOIN concar_confignew c4 ON   c4.module = 8   AND c4.category = 2   AND c4.subcategory = 0   --Cuenta Ticket Efectivo				
				LEFT JOIN concar_confignew c5 ON   c5.module = 8   AND c5.category = 3   AND c5.subcategory = 1   --Cuenta Tarjeta Visa
				LEFT JOIN concar_confignew c6 ON   c6.module = 8   AND c6.category = 3   AND c6.subcategory = 2   --Cuenta Tarjeta American Express
				LEFT JOIN concar_confignew c7 ON   c7.module = 8   AND c7.category = 3   AND c7.subcategory = 3   --Cuenta Tarjeta Mastercard
				LEFT JOIN concar_confignew c8 ON   c8.module = 8   AND c8.category = 3   AND c8.subcategory = 4   --Cuenta Tarjeta Dinners
				LEFT JOIN concar_confignew c9 ON   c9.module = 8   AND c9.category = 3   AND c9.subcategory = 5   --Cuenta Tarjeta CMR
				LEFT JOIN concar_confignew c10 ON   c10.module = 8   AND c10.category = 3   AND c10.subcategory = 6   --Cuenta Tarjeta Ripley
				LEFT JOIN concar_confignew c11 ON   c11.module = 8   AND c11.category = 3   AND c11.subcategory = 7   --Cuenta Tarjeta Cheques Otros
				LEFT JOIN concar_confignew c12 ON   c12.module = 8   AND c12.category = 3   AND c12.subcategory = 8   --Cuenta Tarjeta Cheques BBVA
				LEFT JOIN concar_confignew c13 ON   c13.module = 8   AND c13.category = 3   AND c13.subcategory = 9   --Cuenta Tarjeta Metroplazos				
				LEFT JOIN concar_confignew c14 ON   c14.module = 8   AND c14.category = 4   AND c14.subcategory = 0   --Codigo de Anexo de Combustible
				LEFT JOIN concar_confignew c15 ON   c15.module = 8   AND c15.category = 4   AND c15.subcategory = 1   --Codigo de Anexo de GLP
				LEFT JOIN concar_confignew c16 ON   c16.module = 8   AND c16.category = 4   AND c16.subcategory = 2   --Codigo de Anexo de Market
			WHERE
				c1.module = 8   AND c1.category = 1   AND c1.subcategory = 0; --Cuenta Caja Combustible  
		";

		if ($sqlca->query($sql) < 0) 
			return false;	
			
		$a = $sqlca->fetchRow();				
		$combustible_cuenta_caja         = $a['combustible_cuenta_caja'];
		$glp_cuenta_caja                 = $a['glp_cuenta_caja'];
		$market_cuenta_caja              = $a['market_cuenta_caja'];

		$cuenta_ticket_efectivo          = $a['cuenta_ticket_efectivo'];

		$cuenta_tarjeta_visa             = $a['cuenta_tarjeta_visa'];
		$cuenta_tarjeta_american_express = $a['cuenta_tarjeta_american_express'];
		$cuenta_tarjeta_mastercard       = $a['cuenta_tarjeta_mastercard'];
		$cuenta_tarjeta_dinners          = $a['cuenta_tarjeta_dinners'];
		$cuenta_tarjeta_cmr              = $a['cuenta_tarjeta_cmr'];
		$cuenta_tarjeta_ripley           = $a['cuenta_tarjeta_ripley'];
		$cuenta_tarjeta_cheques_otros    = $a['cuenta_tarjeta_cheques_otros'];
		$cuenta_tarjeta_cheques_bbva     = $a['cuenta_tarjeta_cheques_bbva'];
		$cuenta_tarjeta_metroplazos      = $a['cuenta_tarjeta_metroplazos'];

		$combustible_cod_anexo           = $a['combustible_cod_anexo'];
		$glp_cod_anexo                   = $a['glp_cod_anexo'];
		$market_cod_anexo                = $a['market_cod_anexo'];
		//CERRAR OBTENEMOS CUENTAS DEL MODULO LIQUIDACION DE CAJA

		//OBTENEMOS WHERE PARA ASIENTOS MARKET
		$sqlD0 = "
		SELECT
			PT1.ch_sucursal,
			PT1.dt_dia,
			PT1.ch_posturno,
			PT1.ch_codigo_trabajador
		FROM
			pos_historia_ladosxtrabajador PT1
		WHERE
			PT1.ch_tipo = 'M'
			AND PT1.dt_dia BETWEEN '$FechaIni' AND '$FechaFin'
			AND PT1.ch_codigo_trabajador NOT IN (
			SELECT
				PT2.ch_codigo_trabajador
			FROM
				pos_historia_ladosxtrabajador PT2
			WHERE
				PT2.ch_tipo = 'C'
				AND PT2.dt_dia=PT1.dt_dia
				AND PT2.ch_posturno=PT1.ch_posturno
			GROUP BY
				PT2.ch_sucursal,
				PT2.dt_dia,
				PT2.ch_codigo_trabajador
			)
		GROUP BY
			PT1.ch_sucursal,
			PT1.dt_dia,
			PT1.ch_posturno,
			PT1.ch_codigo_trabajador;
		";
			
		if ($sqlca->query($sqlD0) < 0) 
			return false;
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$d = $sqlca->fetchRow();
			$textoarmado = $textoarmado."(ch_almacen = '".trim($d[0])."' AND dt_dia = '".trim($d[1])."' AND ch_posturno = '".trim($d[2])."' AND ch_codigo_trabajador = '".trim($d[3])."') OR ";
		}

		$textofinal = substr($textoarmado,0,-3);
		//CERRAR OBTENEMOS WHERE PARA ASIENTOS MARKET

		$sql = "
		--INICIO DE ASIENTOS PARA COMBUSTIBLE
		SELECT * FROM ( --TARJETAS AGRUPADAS
			SELECT
				to_char(date(dia),'YYMMDD') as dia, 		
				CASE
					WHEN t.at = '1' THEN $cuenta_tarjeta_visa::text
					WHEN t.at = '2' THEN $cuenta_tarjeta_american_express::text
					WHEN t.at = '3' THEN $cuenta_tarjeta_mastercard::text
					WHEN t.at = '4' THEN $cuenta_tarjeta_dinners::text
					WHEN t.at = '5' THEN $cuenta_tarjeta_cmr::text
					WHEN t.at = '6' THEN $cuenta_tarjeta_ripley::text
					WHEN t.at = '7' THEN $cuenta_tarjeta_cheques_otros::text
					WHEN t.at = '8' THEN $cuenta_tarjeta_cheques_bbva::text
					WHEN t.at = '9' THEN $cuenta_tarjeta_metroplazos::text
				END as DCUENTA,
				'$cod_cliente'::text as codigo, 
				' '::text as trans, 
				'1'::text as tip, 
				'D'::text as ddh, 
				SUM(t.importe)-SUM(COALESCE(t.km,0)) as importe,
				'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '/' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| '' ::text as venta , 
				FIRST(t.es) as sucursal, 
				''::text as dnumdoc,
				'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
				'$vccencos'::text as DCENCOS,
				'A'::text as tip2,
				t.at as tarjeta,
				''::text as tipo,
				''::TEXT AS doctype,
                'COMBU'::TEXT AS No_Tipo_Producto,
				''::TEXT AS documento_referencia,
				--
				t.at as at, 
				g.tab_descripcion as descripcion			
			FROM
				$postrans t
				LEFT JOIN int_tabla_general g ON (g.tab_tabla='95' AND g.tab_elemento='00000'||t.at) 
			WHERE
				t.codigo != '11620307'
				AND t.es = '$almacen'    
				AND t.dia between '$FechaIni' and '$FechaFin'
				AND t.tipo = 'C'
				AND t.fpago = '2'  
				AND t.td != 'N'
			GROUP BY 
				t.at,g.tab_descripcion,t.dia
			ORDER BY 
				t.at	
		) as A
		UNION ALL --TOTAL EFECTIVOS
		( 			
			SELECT 
				to_char(date(D.dt_dia),'YYMMDD') as dia, 		
				$cuenta_ticket_efectivo::text as DCUENTA,
				'$cod_cliente'::text as codigo, 
				' '::text as trans, 
				'1'::text as tip, 
				'D'::text as ddh, 
				sum(D.importe) as importe,
				'VENTA COMBUSTIBLE ' || substring(D.dt_dia::text from 9 for 2) || '/' || substring(D.dt_dia::text from 6 for 2) || '-' || substring(D.dt_dia::text from 3 for 2) ||' / '|| '' ::text as venta , 
				FIRST(ch_almacen) as sucursal, 
				''::text as dnumdoc,
				'$vcsubdiario'||substring(D.dt_dia::text from 9 for 2)::text as subdiario,
				'$vccencos'::text as DCENCOS,
				'A'::text as tip2,
				'' as tarjeta,
				''::text as tipo,
				''::TEXT AS doctype,
                'COMBU'::TEXT AS No_Tipo_Producto,
				''::TEXT AS documento_referencia,
				--
				''::text as at,
				'EFECTIVO SOLES'::text as descripcion  
			FROM 
				(   
					SELECT
						pos.ch_almacen,pos.dt_dia,pos.ch_posturno,pos.ch_codigo_trabajador, CASE WHEN ch_moneda='01' THEN sum(nu_importe) WHEN ch_moneda!='01' THEN sum(pos.nu_importe * tc.tca_venta_oficial) END as importe            
					FROM
						pos_depositos_diarios pos
						LEFT JOIN int_tipo_cambio tc ON (pos.dt_dia = tc.tca_fecha)
					WHERE
						ch_almacen='$almacen'
						and ch_valida='S' 
						and dt_dia between '$FechaIni' and '$FechaFin'
					GROUP BY
						ch_almacen,dt_dia,ch_posturno,ch_codigo_trabajador,ch_moneda
				) D
			INNER JOIN (
					SELECT 
						ch_sucursal,dt_dia,ch_posturno,ch_codigo_trabajador
					FROM 
						pos_historia_ladosxtrabajador PT
						INNER JOIN (SELECT lado,prod1 FROM pos_cmblados) L ON L.lado = PT.ch_lado and prod1!='GL'
					GROUP BY 
						ch_sucursal,dt_dia,ch_posturno,ch_codigo_trabajador
			) T ON (T.ch_sucursal = D.ch_almacen and T.ch_posturno = D.ch_posturno and T.ch_codigo_trabajador = D.ch_codigo_trabajador and T.dt_dia = D.dt_dia)
			GROUP BY 
				D.dt_dia
		) 
		--INICIO DE ASIENTOS PARA GLP
		UNION ALL --TARJETAS AGRUPADAS
		( 			
			SELECT
				to_char(date(dia),'YYMMDD') as dia, 		
				CASE
					WHEN t.at = '1' THEN $cuenta_tarjeta_visa::text
					WHEN t.at = '2' THEN $cuenta_tarjeta_american_express::text
					WHEN t.at = '3' THEN $cuenta_tarjeta_mastercard::text
					WHEN t.at = '4' THEN $cuenta_tarjeta_dinners::text
					WHEN t.at = '5' THEN $cuenta_tarjeta_cmr::text
					WHEN t.at = '6' THEN $cuenta_tarjeta_ripley::text
					WHEN t.at = '7' THEN $cuenta_tarjeta_cheques_otros::text
					WHEN t.at = '8' THEN $cuenta_tarjeta_cheques_bbva::text
					WHEN t.at = '9' THEN $cuenta_tarjeta_metroplazos::text
				END as DCUENTA,
				'$cod_cliente'::text as codigo, 
				' '::text as trans, 
				'1'::text as tip, 
				'D'::text as ddh, 
				SUM(t.importe)-SUM(COALESCE(t.km,0)) as importe,
				'VENTA GLP ' || substring(dia::text from 9 for 2) || '/' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| '' ::text as venta , 
				FIRST(t.es) as sucursal, 
				''::text as dnumdoc,
				'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
				'$vccencos'::text as DCENCOS,
				'A'::text as tip2,
				t.at as tarjeta,
				''::text as tipo,
				''::TEXT AS doctype,
                'GLP'::TEXT AS No_Tipo_Producto,
				''::TEXT AS documento_referencia,
				--
				t.at as at, 
				g.tab_descripcion as descripcion			
			FROM
				$postrans t
				LEFT JOIN int_tabla_general g ON (g.tab_tabla='95' AND g.tab_elemento='00000'||t.at) 
			WHERE
				t.codigo = '11620307'
				AND t.es = '$almacen'    
				AND t.dia between '$FechaIni' and '$FechaFin'
				AND t.tipo = 'C'
				AND t.fpago = '2'  
				AND t.td != 'N'
			GROUP BY 
				t.at,g.tab_descripcion,t.dia
			ORDER BY 
				t.at
		) 
		UNION ALL --TOTAL EFECTIVOS
		( 			
			SELECT 
				to_char(date(D.dt_dia),'YYMMDD') as dia, 		
				$cuenta_ticket_efectivo::text as DCUENTA,
				'$cod_cliente'::text as codigo, 
				' '::text as trans, 
				'1'::text as tip, 
				'D'::text as ddh, 
				sum(D.importe) as importe,
				'VENTA GLP ' || substring(D.dt_dia::text from 9 for 2) || '/' || substring(D.dt_dia::text from 6 for 2) || '-' || substring(D.dt_dia::text from 3 for 2) ||' / '|| '' ::text as venta , 
				FIRST(ch_almacen) as sucursal, 
				''::text as dnumdoc,
				'$vcsubdiario'||substring(D.dt_dia::text from 9 for 2)::text as subdiario,
				'$vccencos'::text as DCENCOS,
				'A'::text as tip2,
				'' as tarjeta,
				''::text as tipo,
				''::TEXT AS doctype,
                'GLP'::TEXT AS No_Tipo_Producto,
				''::TEXT AS documento_referencia,
				--
				''::text as at,
				'EFECTIVO SOLES'::text as descripcion  
			FROM 
				(   
					SELECT
						pos.ch_almacen,pos.dt_dia,pos.ch_posturno,pos.ch_codigo_trabajador, CASE WHEN ch_moneda='01' THEN sum(nu_importe) WHEN ch_moneda!='01' THEN sum(pos.nu_importe * tc.tca_venta_oficial) END as importe            
					FROM
						pos_depositos_diarios pos
						LEFT JOIN int_tipo_cambio tc ON (pos.dt_dia = tc.tca_fecha)
					WHERE
						ch_almacen='$almacen'
						and ch_valida='S' 
						and dt_dia between '$FechaIni' and '$FechaFin'
					GROUP BY
						ch_almacen,dt_dia,ch_posturno,ch_codigo_trabajador,ch_moneda
				) D
			INNER JOIN (
					SELECT 
						ch_sucursal,dt_dia,ch_posturno,ch_codigo_trabajador
					FROM 
						pos_historia_ladosxtrabajador PT
						INNER JOIN (SELECT lado,prod1 FROM pos_cmblados) L ON L.lado = PT.ch_lado and prod1='GL'
					GROUP BY 
						ch_sucursal,dt_dia,ch_posturno,ch_codigo_trabajador
			) T ON (T.ch_sucursal = D.ch_almacen and T.ch_posturno = D.ch_posturno and T.ch_codigo_trabajador = D.ch_codigo_trabajador and T.dt_dia = D.dt_dia)
			GROUP BY 
				D.dt_dia
		)
		--INICIO DE ASIENTOS PARA MARKET
		UNION ALL --TARJETAS AGRUPADAS
		( 			
			SELECT
				to_char(date(dia),'YYMMDD') as dia, 		
				CASE
					WHEN t.at = '1' THEN $cuenta_tarjeta_visa::text
					WHEN t.at = '2' THEN $cuenta_tarjeta_american_express::text
					WHEN t.at = '3' THEN $cuenta_tarjeta_mastercard::text
					WHEN t.at = '4' THEN $cuenta_tarjeta_dinners::text
					WHEN t.at = '5' THEN $cuenta_tarjeta_cmr::text
					WHEN t.at = '6' THEN $cuenta_tarjeta_ripley::text
					WHEN t.at = '7' THEN $cuenta_tarjeta_cheques_otros::text
					WHEN t.at = '8' THEN $cuenta_tarjeta_cheques_bbva::text
					WHEN t.at = '9' THEN $cuenta_tarjeta_metroplazos::text
				END as DCUENTA,
				'$cod_cliente'::text as codigo, 
				' '::text as trans, 
				'1'::text as tip, 
				'D'::text as ddh, 
				SUM(t.importe)-SUM(COALESCE(t.km,0)) as importe,
				'MKT ' || substring(dia::text from 9 for 2) || '/' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| '' ::text as venta , 
				FIRST(t.es) as sucursal, 
				''::text as dnumdoc,
				'$vcsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
				'$vccencos'::text as DCENCOS,
				'A'::text as tip2,
				t.at as tarjeta,
				''::text as tipo,
				''::TEXT AS doctype,
                'MKT'::TEXT AS No_Tipo_Producto,
				''::TEXT AS documento_referencia,
				--
				t.at as at, 
				g.tab_descripcion as descripcion			
			FROM
				$postrans t
				LEFT JOIN int_tabla_general g ON (g.tab_tabla='95' AND g.tab_elemento='00000'||t.at) 
			WHERE				
				t.es = '$almacen'    
				AND t.dia between '$FechaIni' and '$FechaFin'
				AND t.tipo = 'M'
				AND t.fpago = '2'  
				AND t.td != 'N'
			GROUP BY 
				t.at,g.tab_descripcion,t.dia
			ORDER BY 
				t.at
		)
		UNION ALL --TOTAL EFECTIVOS
		( 	
			SELECT 
				to_char(date(D.dt_dia),'YYMMDD') as dia, 		
				$cuenta_ticket_efectivo::text as DCUENTA,
				'$cod_cliente'::text as codigo, 
				' '::text as trans, 
				'1'::text as tip, 
				'D'::text as ddh, 
				sum(D.importe) as importe,
				'MKT ' || substring(D.dt_dia::text from 9 for 2) || '/' || substring(D.dt_dia::text from 6 for 2) || '-' || substring(D.dt_dia::text from 3 for 2) ||' / '|| '' ::text as venta , 
				FIRST(ch_almacen) as sucursal, 
				''::text as dnumdoc,
				'$vcsubdiario'||substring(D.dt_dia::text from 9 for 2)::text as subdiario,
				'$vccencos'::text as DCENCOS,
				'A'::text as tip2,
				'' as tarjeta,
				''::text as tipo,
				''::TEXT AS doctype,
				'MKT'::TEXT AS No_Tipo_Producto,
				''::TEXT AS documento_referencia,
				--
				''::text as at,
				'EFECTIVO SOLES'::text as descripcion  
			FROM 
			(		
				SELECT
					ch_almacen,dt_dia,ch_posturno,ch_codigo_trabajador, CASE WHEN ch_moneda='01' THEN sum(nu_importe) WHEN ch_moneda!='01' THEN sum(nu_importe * tpc.tca_venta_oficial) END as importe   
				FROM
					pos_depositos_diarios
					LEFT JOIN int_tipo_cambio tpc ON (tpc.tca_fecha=dt_dia)
				WHERE
					ch_almacen = '$almacen'
					AND ch_valida='S'
					AND (
						$textofinal
					)
				GROUP BY
					ch_almacen,dt_dia,ch_posturno,ch_codigo_trabajador,ch_moneda
			) D
			GROUP BY 
				D.dt_dia
		)	
		ORDER BY dia,no_tipo_producto,at;";

		echo "<pre>";
		echo "\n\n ASIENTOS LIQUIDACION CAJA: ".$sql."\n\n";
		echo "<pre>";

		$q1 = "CREATE TABLE tmp_concar (dsubdia character varying(7), dcompro character varying(8), dsecue character varying(7), dfeccom character varying(6), dcuenta character varying(12), dcodane character varying(18),
        dcencos character varying(6), dcodmon character varying(2), ddh character varying(1), dimport numeric(14,2), dtipdoc character varying(2), dnumdoc character varying(30), 
        dfecdoc character varying(8), dfecven character varying(8), darea character varying(3), dflag character varying(1), ddate date not null, dxglosa character varying(40),
        dusimport numeric(14,2), dmnimpor numeric(14,2), dcodarc character varying(2), dfeccom2 character varying(8), dfecdoc2 character varying(8), dvanexo character varying(1), dcodane2 character varying(18),
        tarjeta character varying(1), td character varying(1), no_tipo_producto character varying(5), doctype character varying(5), docrefer character varying(20));";

		$sqlca->query($q1);

		$dia = NULL;
		$correlativo = 0;
		$contador = '0000';  
		$k = 0;       
			
		if ($sqlca->query($sql)<=0) {
			$sqlca->get_error();
			error_log("Estapa 0");
			return NULL;	
		}

		error_log("************************FechaDiv*********************");
		error_log(json_encode($FechaDiv));

		if ($sqlca->query($sql)>0) {
			$correlativo = ($FechaDiv[1] * 10000) + $num_actual;			
			while ($reg = $sqlca->fetchRow()) {	
				
				//reiniciar el numerador cuando sea diferente dia
				/*if($subdia != $reg[10]){
					$correlativo = 0;
					$correlativo = ($FechaDiv[1] * 10000) + $num_actual;
					$subdia = $reg[10];
				}*/
	
				// validando si subdiario se toma completo ($opcion=0) o con el día ($opcion=1)
				if($opcion==0) {
					$reg[10]=substr($reg[10],0,-2);
				}
				
				if ($reg[0] != $dia) { 
					$dia = $reg[0];
					$k=1;
					$correlativo = $correlativo + 1;
					if($FechaDiv[1]>=1 && $FechaDiv[1]<10){
						$correlativo2 = "0".$correlativo;
					}else {
						$correlativo2 = $correlativo;
					}
				} else {
					$k = $k+1;						
				}
	
				if(trim($reg[9]) == ''){
					$reg[9] = $xtradat;
				} else {
					$xtradat = trim($reg[9]);
				}
										
				if($k<=9){
					$contador = "000".$k;
				}elseif($k>9 and $k<=99){
					$contador = "00".$k;
				} else {
					$contador = "0".$k;
				}
	
				$dfecdoc = "20".substr($reg[0],0,2).substr($reg[0],2,2).substr($reg[0],4,2);				
			
				if($reg[12] == 'E')
					$extorno = "A";
				else
					$extorno = "S";
					
				if(empty($reg[13]) || $reg[13] == '' || $reg[13] == NULL)
					$reg[13] = "0"; //AQUI CONVIERTE at '' de pagos en efectivo a 0
	
				$q2 = $sqlca->query("INSERT INTO tmp_concar VALUES ('".trim($reg[10])."', '".trim($correlativo2)."', '".trim($contador)."', '".trim($reg[0])."', '".trim($reg[1])."', '".trim($reg[2])."', '".trim($reg[11])."', 'MN', '".trim($reg[5])."', '".trim($reg[6])."', '".trim($reg[15])."', '".trim($reg[9])."', '".trim($reg[0])."', '".trim($reg[0])."', '".trim($extorno)."', 'S', '".$dfecdoc."', '".trim($reg[7])."', '0', '".trim($reg[6])."', 'X', '".$dfecdoc."', '".$dfecdoc."', 'P', ' ', '".$reg[13]."', '".trim($reg[14])."', '".trim($reg[16])."', '".trim($reg[15])."', '".trim($reg[17])."');", "concar_insert");
	
			}
		}

		//******************************** ASIENTOS LIQUIDACION CAJA *****************************	
		// Datos de asientos liquidacion caja

		// $sql_cc = "SELECT codane, subdiario_dia FROM concar_config;";

		// if ($sqlca->query($sql_cc) < 0){
		// 	error_log('Etapa 1: Obtenemos datos de cuenta por cobrar');
		// 	return false;	
		// }

		// $a = $sqlca->fetchRow();
		// $codane 		= $a[0];
		// $opcion      = $a[1];

		$sql_cc = "
			SELECT
				TRIM(c1.account) as codane
				,TRIM(c2.account) as subdiario_dia
			FROM
				concar_confignew c1 
				LEFT JOIN concar_confignew c2 ON   c2.module = 0   AND c2.category = 1   AND c2.subcategory = 0   --Subdiario dia
			WHERE
				c1.module = 0   AND c1.category = 3   AND c1.subcategory = 0;   --Codigo de Anexo
		";

		if ($sqlca->query($sql_cc) < 0){
			error_log('Etapa 1: Obtenemos datos de cuenta por cobrar');
			return false;	
		}	

		$a = $sqlca->fetchRow();
		$codane    = $a[0];
		$opcion    = $a[1];

		$sqlcc = "
			SELECT * FROM(		
				SELECT 
					dfeccom as dfeccom,
					dcuenta AS dcuenta,
					(CASE WHEN no_tipo_producto = 'COMBU' THEN '0002' WHEN no_tipo_producto = 'GLP' THEN '0004' WHEN no_tipo_producto = 'MKT' THEN '0006' END) as dsecue,
					--dcodane as dcodane,
					(CASE WHEN no_tipo_producto = 'COMBU' THEN '$combustible_cod_anexo' WHEN no_tipo_producto = 'GLP' THEN '$glp_cod_anexo' WHEN no_tipo_producto = 'MKT' THEN '$market_cod_anexo' END) as dcodane,
					dcencos as dcencos,
					'D' as ddh,	
					dimport as dimport,
					--dnumdoc as dnumdoc,
					dfeccom as dnumdoc,
					'LIQ. '||(CASE WHEN no_tipo_producto = 'COMBU' THEN 'COMBUSTIBLES' WHEN no_tipo_producto = 'GLP' THEN 'GLP' WHEN no_tipo_producto = 'MKT' THEN 'MARKET' END) || ' ' || TO_CHAR(ddate, 'DD/MM/YYYY')  as dxglosa,
					'$vcsubdiario'::text as subday,
					'' as doctype,
					tarjeta as tarjeta,
					td as td,
					'VR' as dtipdoc
				FROM 
					tmp_concar 
				WHERE 
					ddh='D' AND dvanexo='P'	AND doctype != 'NA'
				) as A
				UNION ALL
				(	
				SELECT 
					t.dfeccom as dfeccom,
					CASE
						WHEN no_tipo_producto = 'COMBU' THEN '$combustible_cuenta_caja'::text
						WHEN no_tipo_producto = 'GLP' THEN '$glp_cuenta_caja'::text
						WHEN no_tipo_producto = 'MKT' THEN '$market_cuenta_caja'::text        
					END as dcuenta,
					(CASE WHEN no_tipo_producto = 'COMBU' THEN '0001' WHEN no_tipo_producto = 'GLP' THEN '0003' WHEN no_tipo_producto = 'MKT' THEN '0005' END) as dsecue,
					--'$codane' as dcodane,
					(CASE WHEN no_tipo_producto = 'COMBU' THEN '$combustible_cod_anexo' WHEN no_tipo_producto = 'GLP' THEN '$glp_cod_anexo' WHEN no_tipo_producto = 'MKT' THEN '$market_cod_anexo' END) as dcodane,
					t.dcencos as dcencos,
					'H' as ddh,
					SUM(t.dimport) as dimport, 
					--'000000' as dnumdoc,
					dfeccom as dnumdoc,
					'LIQ. '||(CASE WHEN no_tipo_producto = 'COMBU' THEN 'COMBUSTIBLES' WHEN no_tipo_producto = 'GLP' THEN 'GLP' WHEN no_tipo_producto = 'MKT' THEN 'MARKET' END) || ' ' || TO_CHAR(ddate, 'DD/MM/YYYY')  as dxglosa,
					'$vcsubdiario'::text as subday,
					'' as doctype,
					'' as tarjeta, --Para identificar el tipo de tarjeta
					'' as td, --Para identificar el tipo de documento
					'VR' as dtipdoc --Para identificar el tipo de documento formateado (BOLETA: BV, FACTURA: FT, NOTA DE CREDITO: NA)
				FROM 
					tmp_concar t
				WHERE 
					t.ddh='D' AND t.dvanexo='P' AND t.doctype != 'NA'
				GROUP BY 
					t.dfeccom,t.dcencos,t.no_tipo_producto, t.ddate
				)
				ORDER BY dfeccom, dsecue, dcuenta, dcodane desc;
		";
			
		echo "<pre>";
		echo "\n\n QUERY FINAL CTAS. COBRAR COMBUSTIBLES: ".$sqlcc."\n\n";			
		echo "</pre>";
		// return false; //
			
		if ($sqlca->query($sqlcc) < 0){
			error_log('Etapa 3: QUERY FINAL CTAS. COBRAR COMBUSTIBLES');
			return false;	
		} 

		$md = 0;
		$pc = 0;
		$arrInserts = Array();
		$counter = ($FechaDiv[1] * 10000) + $num_actual;
		$counterserie = ($FechaDiv[1] * 10000) + $num_actual;
		$cons = 0;//secuencial de detalles cobrar
		$diaserie = '';
		//$dxglosa = '';
		$no_documento = null;
		$nu_secue = null;
		$nu_dia = null;
		$cod_dia = null;
		$nu_subdia = null;

		while ($reg = $sqlca->fetchRow()) {

			// validando si subdiario se toma completo ($opcion=0) o con el día ($opcion=1)
			if($opcion==0) {
				$reg[9]=substr($reg[9],0,-2);
			}

			// if($reg[8] != $dxglosa){
			// 	$counter = $counter + 1;
			// 	$dxglosa = $reg[8];
			// }

			if($reg[1] == $combustible_cuenta_caja || $reg[1] == $glp_cuenta_caja || $reg[1] == $market_cuenta_caja){
				$counter = $counter + 1;
			}

			if($FechaDiv[1]>=1 && $FechaDiv[1]<10){
				$secue = "0".$counter;
			} else {
				$secue = $counter;
			}

			$nu_subdia = $reg[9].substr($reg[0], -2);

			if($reg[5]=="H" && $reg[10]==""){ //INGRESAMOS IMPORTE TOTALIZADO EN CABECERA
				$ins1 = "INSERT INTO $TabSqlCab
					(
						CSUBDIA, CCOMPRO, CFECCOM, CCODMON, CSITUA, CTIPCAM, CGLOSA, CTOTAL,CTIPO, CFLAG, CFECCAM, CORIG, CFORM, CTIPCOM, CEXTOR
					) VALUES (
						'".$nu_subdia."', '".$secue."', '".$reg[0]."', 'MN', '', 0, '".$reg[8]."', '".$reg[6]."', 'V', 'S', '', '','','',''
					);";

				$arrInserts['tipo']['cabecera'][$pc] = $ins1;
				$pc++;
			}

			if($secue != $nu_secue){
				$cons = 0;
				$nu_secue = $secue;
			}

			//reiniciar el numerador cuando sea diferente dia DSECUE
			$cons++;

			if($cons>0 and $cons<=9) {
				$secdet = "000".$cons;				
			} elseif ($cons>=10 and $cons<=99){
				$secdet = "00".$cons;
			} elseif ($cons>=100 and $cons<=999){
				$secdet = "0".$cons;
			} else {
				$secdet = $cons;
			}
				
			$cuenta10 = $reg[1]; //Obtenemos cuenta por defecto		
			$ddh = $reg[5]; //Obtenemos D (Debe) o H (Haber)

			$ins2 = "INSERT INTO $TabSqlDet 
				(	dsubdia, dcompro, dsecue, dfeccom, dcuenta, dcodane, dcencos, dcodmon, ddh, dimport, dtipdoc, dnumdoc, dfecdoc, dfecven, 
					darea, dflag, dxglosa, dusimpor, dmnimpor, dcodarc, dcodane2, dtipcam, dmoncom, dcolcom, dbascom, dinacom, digvcom, dtpconv, 
					dflgcom, danecom, dtipaco, dmedpag, dtidref, dndoref, dfecref, dbimref, digvref, dtipdor, dnumdor, dfecdo2
				) VALUES ( 
					'".$nu_subdia."', '".$secue."', '".$secdet."', '".$reg[0]."', '".$cuenta10."', '".$reg[3]."', '', 'MN', 
					'".$ddh."', ".$reg[6].", '".$reg[13]."', '".$reg[7]."', '".$reg[0]."', '".$reg[0]."', '','S', '".$reg[8]."', 
					0,0,'','',0,'','',0,0,0,'','','','','','','', GETDATE(), 0,0,'','', GETDATE()
				);";
		
			$arrInserts['tipo']['detalle'][$md] = $ins2;

			$md++;

		}

		$q5 = $sqlca->query("DROP TABLE tmp_concar");
		$rstado = ejecutarInsert($arrInserts, $TabSqlCab, $TabSqlDet, '');
		return $rstado;
	}
	
	function interface_cobrar_market($FechaIni, $FechaFin, $almacen, $codEmpresa, $num_actual) {  // CUENTAS POR COBRAR MARKET
		global $sqlca;

		if(trim($num_actual)=="")
			$num_actual = 0;

		$FechaIni_Original = $FechaIni;
		$FechaFin_Original = $FechaFin;
		$FechaDiv = explode("/", $FechaIni);
		$FechaIni = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];
		$anio1 	  = $FechaDiv[2];
		$mes1 	  = $FechaDiv[1];
		$dia1 	  = $FechaDiv[0];				
		$postrans = "pos_trans".$FechaDiv[2].$FechaDiv[1];
		$FechaDiv = explode("/", $FechaFin);
		$FechaFin = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];
		$anio2 	  = $FechaDiv[2];
		$mes2 	  = $FechaDiv[1];
		$dia2 	  = $FechaDiv[0];				
		$Anio 	  = substr($FechaDiv[2],2,3);
		
		if ($anio1 != $anio2 || $mes1 != $mes2) {
			return 3; //Fechas deben ser mismo mes y año
		} else {
			if ($dia1 > $dia2) 
				return 4; //Fecha inicio debe ser menor que fecha final					
		}

		$Anio = trim($Anio);
		$codEmpresa = trim($codEmpresa);
		
		$TabSqlCab = "CC".$codEmpresa.$Anio; // Tabla SQL Cabecera
		$TabSqlDet = "CD".$codEmpresa.$Anio; // Tabla SQL Detalle
		$TabCan    = "CAN".$codEmpresa;
		
		$val = InterfaceConcarActModel::verificaFecha("1", $almacen, $FechaIni, $FechaFin);

		if ($val == 5)
			return 5;		
		
// 		$sql = "
// SELECT
//  ccobrar_subdiario_mkt,
//  venta_cuenta_cliente_mkt,
//  venta_cuenta_impuesto,
//  venta_cuenta_ventas_mkt,
//  id_centrocosto,
//  subdiario_dia,
//  cod_cliente,
//  cod_caja
// FROM
//  concar_config;
// 		";

// 		if ($sqlca->query($sql) < 0) 
// 			return false;	
	
// 		$a = $sqlca->fetchRow();
// 		$vmsubdiario = $a[0];
// 		$vmcliente   = $a[1];
// 		$vmimpuesto  = $a[2];
// 		$vmventas    = $a[3];	
// 		$vmcencos    = $a[4];	
// 		$opcion      = $a[5];
// 		$cod_cliente = $a[6];				
// 		$cod_caja    = $a[7];

		$sql = "
SELECT
	c1.account AS ccobrar_subdiario_mkt
	,c2.account AS venta_cuenta_cliente_mkt
	,c3.account AS venta_cuenta_impuesto
	,c4.account AS venta_cuenta_ventas_mkt
	,c5.account AS id_centrocosto
	,c6.account AS subdiario_dia
	,c7.account AS cod_cliente
	,c8.account AS cod_caja
FROM
 	concar_confignew c1
	LEFT JOIN concar_confignew c2 ON   c2.module = 2   AND c2.category = 1   AND c2.subcategory = 0   --Cuenta Market Cliente
	LEFT JOIN concar_confignew c3 ON   c3.module = 2   AND c3.category = 1   AND c3.subcategory = 1   --Cuenta Market Impuesto
	LEFT JOIN concar_confignew c4 ON   c4.module = 2   AND c4.category = 1   AND c4.subcategory = 2   --Cuenta Market Ventas

	LEFT JOIN concar_confignew c5 ON   c5.module = 0   AND c5.category = 2   AND c5.subcategory = 2   --Centro de Costo Market
	LEFT JOIN concar_confignew c6 ON   c6.module = 0   AND c6.category = 1   AND c6.subcategory = 0   --Subdiario dia

	LEFT JOIN concar_confignew c7 ON   c7.module = 0   AND c7.category = 1   AND c7.subcategory = 1   --Codigo de Cliente
	LEFT JOIN concar_confignew c8 ON   c8.module = 0   AND c8.category = 1   AND c8.subcategory = 2   --Codigo de Caja
WHERE
	c1.module = 4   AND c1.category = 0   AND c1.subcategory = 0;   --Subdiario de Cta Cobrar Market
		";

		if ($sqlca->query($sql) < 0) 
			return false;	
	
		$a = $sqlca->fetchRow();
		$vmsubdiario = $a[0];
		$vmcliente   = $a[1];
		$vmimpuesto  = $a[2];
		$vmventas    = $a[3];	
		$vmcencos    = $a[4];	
		$opcion      = $a[5];
		$cod_cliente = $a[6];				
		$cod_caja    = $a[7];

		$sql = "
		SELECT * FROM 
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmcliente'::text as DCUENTA, 
					'$cod_cliente'::text as codigo, 
					' '::text as trans, 
					'2'::text as tip, 
					'D'::text as ddh, 
					round(sum(importe),2) as importe, 
					'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja::text as venta, 
					es as sucursal, 
					'$cod_caja'|| caja || '-' ||MIN(cast(trans as integer))||'-'||MAX(cast(trans as integer))::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS,
					at as tarjeta,
					'B'::text as tipo,
					'TK'::TEXT AS doctype
				FROM 
					$postrans 
				WHERE 
					td 		= 'B' 
					AND tipo 	= 'M' 
					AND es 		= '$almacen'
					AND fpago IN('1', '')
					AND usr 	= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY 
					at,
					dia,
					es,
					caja
			) as K
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmcliente'::text as DCUENTA, 
					'$cod_cliente'::text as codigo, 
					' '::text as trans, 
					'2'::text as tip, 
					'D'::text as ddh, 
					round(sum(importe)-first(COALESCE(km,0)),2) as importe, 
					'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja::text as venta, 
					es as sucursal, 
					'$cod_caja'|| caja || '-' ||cast(trans as integer)::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS,
					at as tarjeta,
					'B'::text as tipo,
					'TK'::TEXT AS doctype
				FROM 
					$postrans 
				WHERE 
					td 		= 'B' 
					AND tipo 	= 'M' 
					AND es 		= '$almacen'
					AND fpago='2'
					AND usr 	= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY
					trans, 
					at,
					dia,
					es,
					caja
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmimpuesto'::text as DCUENTA, 
					'$cod_cliente'::text as codigo, 
					' '::text as trans, 
					'2'::text as tip,  
					'H'::text as ddh, 
					round(sum(igv),2) as importe, 
					'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja::text as venta, 
					es as sucursal , 
					'$cod_caja'|| caja || '-' ||MIN(cast(trans as integer))||'-'||MAX(cast(trans as integer))::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS,
					at as tarjeta,
					'B'::text as tipo,
					'TK'::TEXT AS doctype
				FROM 
					$postrans 
				WHERE 
					td 		= 'B' 
					AND tipo	= 'M'
					AND es 		= '$almacen'
					AND usr 	= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
				GROUP BY 
					at,
					dia,
					es,
					caja
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmventas'::text as DCUENTA, 
					codigo_concar::text as codigo, 
					' '::text as trans, 
					'2'::text as tip,  
					'H'::text as ddh, 
					round(sum(importe-igv),2) as importe, 
					'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja::text as venta, 
					es as sucursal , 
					'$cod_caja'|| caja || '-' ||MIN(cast(trans as integer))||'-'||MAX(cast(trans as integer))::text::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS,
					at as tarjeta,
					'B'::text as tipo,
					'TK'::TEXT AS doctype
				FROM 
					$postrans
					LEFT JOIN interface_equivalencia_producto q ON (q.art_codigo = 'MARKET')
				WHERE 
					td 		= 'B' 
					AND tipo	= 'M'
					AND es 		= '$almacen'
					AND usr 	= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY 
					at,
					dia,
					es,
					codigo_concar,
					caja
			)
			UNION--INICIO DE BOLETAS ELECTRONICAS VENTA = V
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmcliente'::text as DCUENTA, 
					'$cod_cliente'::text as codigo, 
					' '::text as trans, 
					'2'::text as tip, 
					'D'::text as ddh, 
					round(sum(importe),2) as importe, 
					'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja::text as venta, 
					es as sucursal,
					SUBSTR(TRIM(usr), 0, 5) || '-' || MIN(SUBSTR(TRIM(usr), 7)) || '/' || MAX(SUBSTR(TRIM(usr), 7))::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS,
					at as tarjeta,
					'B'::text as tipo,
					'BV'::TEXT AS doctype
				FROM 
					$postrans 
				WHERE 
					td 		= 'B' 
					AND tipo 	= 'M' 
					AND es 		= '$almacen'
					AND fpago IN('1', '')
					AND usr 	!= ''
					AND tm = 'V'
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY 
					at,
					dia,
					es,
					caja,
					SUBSTR(TRIM(usr), 0, 5)
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmcliente'::text as DCUENTA, 
					'$cod_cliente'::text as codigo, 
					' '::text as trans, 
					'2'::text as tip, 
					'D'::text as ddh, 
					round(sum(importe)-first(COALESCE(km,0)),2) as importe, 
					'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja::text as venta, 
					es as sucursal,
					SUBSTR(TRIM(usr), 0, 5) || '-' ||SUBSTR(TRIM(usr), 6)::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS,
					at as tarjeta,
					'B'::text as tipo,
					'BV'::TEXT AS doctype
				FROM 
					$postrans 
				WHERE 
					td 		= 'B' 
					AND tipo 	= 'M' 
					AND es 		= '$almacen'
					AND fpago='2'
					AND usr 	!= ''
					AND tm = 'V'
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY
					at,
					dia,
					es,
					caja,
					usr
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmimpuesto'::text as DCUENTA, 
					'$cod_cliente'::text as codigo, 
					' '::text as trans, 
					'2'::text as tip,  
					'H'::text as ddh, 
					round(sum(igv),2) as importe, 
					'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja::text as venta, 
					es as sucursal , 
					SUBSTR(TRIM(usr), 0, 5) || '-' || MIN(SUBSTR(TRIM(usr), 7)) || '/' || MAX(SUBSTR(TRIM(usr), 7))::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS,
					at as tarjeta,
					'B'::text as tipo,
					'BV'::TEXT AS doctype
				FROM 
					$postrans 
				WHERE 
					td 		= 'B' 
					AND tipo	= 'M'
					AND es 		= '$almacen'
					AND usr 	!= ''
					AND tm = 'V'
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
				GROUP BY 
					at,
					dia,
					es,
					caja,
					SUBSTR(TRIM(usr), 0, 5)
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmventas'::text as DCUENTA, 
					codigo_concar::text as codigo, 
					' '::text as trans, 
					'2'::text as tip,  
					'H'::text as ddh, 
					round(sum(importe-igv),2) as importe, 
					'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja::text as venta, 
					es as sucursal , 
					SUBSTR(TRIM(usr), 0, 5) || '-' || MIN(SUBSTR(TRIM(usr), 7)) || '/' || MAX(SUBSTR(TRIM(usr), 7))::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS,
					at as tarjeta,
					'B'::text as tipo,
					'BV'::TEXT AS doctype
				FROM 
					$postrans
					LEFT JOIN interface_equivalencia_producto q ON (q.art_codigo = 'MARKET')
				WHERE 
					td 		= 'B' 
					AND tipo	= 'M'
					AND es 		= '$almacen'
					AND usr 	!= ''
					AND tm = 'V'
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY 
					at,
					dia,
					es,
					codigo_concar,
					caja,
					SUBSTR(TRIM(usr), 0, 5)
			)--FIN DE BOLETAS ELECTRONICAS VENTA = V
			UNION--INICIO DE BOLETAS ELECTRONICAS TM (A y D)
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmcliente'::text as DCUENTA, 
					'$cod_cliente'::text as codigo, 
					' '::text as trans, 
					'2'::text as tip, 
					'H'::text as ddh, 
					-round(sum(importe),2) as importe, 
					'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja::text as venta, 
					es as sucursal,
					SUBSTR(TRIM(usr), 0, 5) || '-' || MIN(SUBSTR(TRIM(usr), 7)) || '/' || MAX(SUBSTR(TRIM(usr), 7))::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS,
					at as tarjeta,
					'B'::text as tipo,
					'BV'::TEXT AS doctype
				FROM 
					$postrans 
				WHERE 
					td 		= 'B' 
					AND tipo 	= 'M' 
					AND es 		= '$almacen'
					AND fpago IN('1', '')
					AND usr 	!= ''
					AND tm IN('A', 'D')
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY 
					at,
					dia,
					es,
					caja,
					SUBSTR(TRIM(usr), 0, 5)
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmcliente'::text as DCUENTA, 
					'$cod_cliente'::text as codigo, 
					' '::text as trans, 
					'2'::text as tip, 
					'H'::text as ddh, 
					-round(sum(importe)-first(COALESCE(km,0)),2) as importe, 
					'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja::text as venta, 
					es as sucursal,
					SUBSTR(TRIM(usr), 0, 5) || '-' ||SUBSTR(TRIM(usr), 6)::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS,
					at as tarjeta,
					'B'::text as tipo,
					'BV'::TEXT AS doctype
				FROM 
					$postrans 
				WHERE 
					td 		= 'B' 
					AND tipo 	= 'M' 
					AND es 		= '$almacen'
					AND fpago='2'
					AND usr 	!= ''
					AND tm IN('A', 'D')
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY
					at,
					dia,
					es,
					caja,
					usr
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmimpuesto'::text as DCUENTA, 
					'$cod_cliente'::text as codigo, 
					' '::text as trans, 
					'2'::text as tip,  
					'D'::text as ddh, 
					-round(sum(igv),2) as importe, 
					'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja::text as venta, 
					es as sucursal , 
					SUBSTR(TRIM(usr), 0, 5) || '-' || MIN(SUBSTR(TRIM(usr), 7)) || '/' || MAX(SUBSTR(TRIM(usr), 7))::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS,
					at as tarjeta,
					'B'::text as tipo,
					'BV'::TEXT AS doctype
				FROM 
					$postrans 
				WHERE 
					td 		= 'B' 
					AND tipo	= 'M'
					AND es 		= '$almacen'
					AND usr 	!= ''
					AND tm IN('A', 'D')
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin'
				GROUP BY 
					at,
					dia,
					es,
					caja,
					SUBSTR(TRIM(usr), 0, 5)
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmventas'::text as DCUENTA, 
					codigo_concar::text as codigo, 
					' '::text as trans, 
					'2'::text as tip,  
					'D'::text as ddh, 
					-round(sum(importe-igv),2) as importe, 
					'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja::text as venta, 
					es as sucursal , 
					SUBSTR(TRIM(usr), 0, 5) || '-' || MIN(SUBSTR(TRIM(usr), 7)) || '/' || MAX(SUBSTR(TRIM(usr), 7))::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS,
					at as tarjeta,
					'B'::text as tipo,
					'BV'::TEXT AS doctype
				FROM 
					$postrans
					LEFT JOIN interface_equivalencia_producto q ON (q.art_codigo = 'MARKET')
				WHERE 
					td 		= 'B' 
					AND tipo	= 'M'
					AND es 		= '$almacen'
					AND usr 	!= ''
					AND tm IN('A', 'D')
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY 
					at,
					dia,
					es,
					codigo_concar,
					caja,
					SUBSTR(TRIM(usr), 0, 5)
			)--FIN DE BOLETAS ELECTRONICAS TM (A y D)
			UNION--INICIO DE TICKETS FACTURAS TM VENTAS = V
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmcliente'::text as DCUENTA,  
					ruc::text as codigo, 
					trans::text as trans,
					'2'::text as tip, 
					'D'::text as ddh, 
					round(sum(importe),2) as importe,
					'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja::text as venta, 
					es as sucursal, 
					'$cod_caja'|| caja || '-' ||trans::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,             
					'$vmcencos'::text as DCENCOS,
					at as tarjeta,
					'F'::text as tipo,
					'TK'::TEXT AS doctype
				FROM 
					$postrans 
				WHERE 
					td 		= 'F'
					AND tipo	= 'M'
					AND es 		= '$almacen'
					AND importe > 0
					AND usr 	= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY 
					at,
					dia,
					trans,
					ruc,
					es,
					caja
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmimpuesto'::text as DCUENTA,  
					ruc::text as codigo, 
					trans::text as trans, 
					'2'::text as tip, 
					'H'::text as ddh, 
					round(sum(igv),2) as importe, 
					'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja::text as venta, 
					es as sucursal , 
					'$cod_caja'|| caja || '-' ||trans::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS,
					at as tarjeta,
					'F'::text as tipo,
					'TK'::TEXT AS doctype
				FROM 
					$postrans 
				WHERE 
					td 		= 'F'
					AND tipo	= 'M'
					AND es 		= '$almacen'
					AND importe > 0
					AND usr 	= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY 
					at,
					dia,
					trans,
					ruc,
					es,
					caja
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmventas'::text as DCUENTA,  
					codigo_concar::text as codigo,
					trans::text as trans,
					'2'::text as tip,  
					'H'::text as ddh, 
					round(sum(importe-igv),2) as importe, 
					'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja::text as venta, 
					es as sucursal , 
					'$cod_caja'|| caja || '-' ||trans::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS,
					at as tarjeta,
					'F'::text as tipo,
					'TK'::TEXT AS doctype
				FROM 
					$postrans
					LEFT JOIN interface_equivalencia_producto q ON (q.art_codigo = 'MARKET')
				WHERE 
					td		= 'F'
					AND tipo	= 'M'
					AND es 		= '$almacen'
					AND importe > 0
					AND usr 	= ''
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY 
					at,
					dia,
					trans,
					es,
					codigo_concar,
					caja
			)--FIN DE TICKETS FACTURAS
			UNION--INICIO DE FACTURAS ELECTRONIOS VENTAS TM = 'V'
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmcliente'::text as DCUENTA,  
					ruc::text as codigo, 
					SUBSTR(TRIM(usr), 6)::text as trans,
					'2'::text as tip, 
					'D'::text as ddh, 
					round(sum(importe),2) as importe,
					'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja::text as venta, 
					es as sucursal,
					SUBSTR(TRIM(usr), 0, 5) || '-' ||SUBSTR(TRIM(usr), 6)::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS,
					at as tarjeta,
					'F'::text as tipo,
					'FT'::TEXT AS doctype
				FROM 
					$postrans 
				WHERE 
					td 			= 'F'
					AND tipo	= 'M'
					AND es 		= '$almacen'
					AND usr 	!= ''
					AND tm ='V'
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY 
					at,
					dia,
					usr,
					ruc,
					es,
					caja
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmimpuesto'::text as DCUENTA,  
					ruc::text as codigo, 
					SUBSTR(TRIM(usr), 6)::text as trans,
					'2'::text as tip, 
					'H'::text as ddh, 
					round(sum(igv),2) as importe, 
					'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja::text as venta, 
					es as sucursal , 
					SUBSTR(TRIM(usr), 0, 5) || '-' ||SUBSTR(TRIM(usr), 6)::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS,
					at as tarjeta,
					'F'::text as tipo,
					'FT'::TEXT AS doctype
				FROM 
					$postrans 
				WHERE 
					td 		= 'F'
					AND tipo	= 'M'
					AND es 		= '$almacen'
					AND usr 	!= ''
					AND tm ='V'
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY 
					at,
					dia,
					usr,
					ruc,
					es,
					caja
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmventas'::text as DCUENTA,  
					codigo_concar::text as codigo,
					SUBSTR(TRIM(usr), 6)::text as trans,
					'2'::text as tip,  
					'H'::text as ddh, 
					round(sum(importe-igv),2) as importe, 
					'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja::text as venta, 
					es as sucursal,
					SUBSTR(TRIM(usr), 0, 5) || '-' ||SUBSTR(TRIM(usr), 6)::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS,
					at as tarjeta,
					'F'::text as tipo,
					'FT'::TEXT AS doctype
				FROM 
					$postrans
					LEFT JOIN interface_equivalencia_producto q ON (q.art_codigo = 'MARKET')
				WHERE 
					td			= 'F'
					AND tipo	= 'M'
					AND es 		= '$almacen'
					AND usr 	!= ''
					AND tm ='V'
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY 
					at,
					dia,
					usr,
					es,
					codigo_concar,
					caja
			)--FIN DE FACTURAS ELECTRONICOS VENTAS TM = 'V'
			UNION--INICIO DE FACTURAS ELECTRONIOS VENTAS TM IN('A','D')
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmcliente'::text as DCUENTA,  
					ruc::text as codigo, 
					SUBSTR(TRIM(usr), 6)::text as trans,
					'2'::text as tip, 
					'H'::text as ddh, 
					-round(sum(importe),2) as importe,
					'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja::text as venta, 
					es as sucursal,
					SUBSTR(TRIM(usr), 0, 5) || '-' ||SUBSTR(TRIM(usr), 6)::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS,
					at as tarjeta,
					'F'::text as tipo,
					'FT'::TEXT AS doctype
				FROM 
					$postrans 
				WHERE 
					td 			= 'F'
					AND tipo	= 'M'
					AND es 		= '$almacen'
					AND usr 	!= ''
					AND tm IN('A','D')
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY 
					at,
					dia,
					usr,
					ruc,
					es,
					caja
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmimpuesto'::text as DCUENTA,  
					ruc::text as codigo, 
					SUBSTR(TRIM(usr), 6)::text as trans,
					'2'::text as tip, 
					'D'::text as ddh, 
					-round(sum(igv),2) as importe, 
					'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja::text as venta, 
					es as sucursal , 
					SUBSTR(TRIM(usr), 0, 5) || '-' ||SUBSTR(TRIM(usr), 6)::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS,
					at as tarjeta,
					'F'::text as tipo,
					'FT'::TEXT AS doctype
				FROM 
					$postrans 
				WHERE 
					td 		= 'F'
					AND tipo	= 'M'
					AND es 		= '$almacen'
					AND usr 	!= ''
					AND tm IN('A','D')
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY 
					at,
					dia,
					usr,
					ruc,
					es,
					caja
			)
			UNION
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmventas'::text as DCUENTA,  
					codigo_concar::text as codigo,
					SUBSTR(TRIM(usr), 6)::text as trans,
					'2'::text as tip,  
					'D'::text as ddh, 
					-round(sum(importe-igv),2) as importe, 
					'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' / '|| caja::text as venta, 
					es as sucursal,
					SUBSTR(TRIM(usr), 0, 5) || '-' ||SUBSTR(TRIM(usr), 6)::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS,
					at as tarjeta,
					'F'::text as tipo,
					'FT'::TEXT AS doctype
				FROM 
					$postrans
					LEFT JOIN interface_equivalencia_producto q ON (q.art_codigo = 'MARKET')
				WHERE 
					td			= 'F'
					AND tipo	= 'M'
					AND es 		= '$almacen'
					AND usr 	!= ''
					AND tm IN('A','D')
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
				GROUP BY 
					at,
					dia,
					usr,
					es,
					codigo_concar,
					caja
			)--FIN DE FACTURAS ELECTRONICOS IN('A','D')
			ORDER BY venta,dia, tip, trans, ddh, DCUENTA;";
		
		echo "<pre>";
		echo "\n\n CTA. COBRAR MARKET: ".$sql."\n\n";
		echo "<pre>";

		$q1 = "CREATE TABLE tmp_concar (dsubdia character varying(7), dcompro character varying(6), dsecue character varying(7), dfeccom character varying(6), dcuenta character varying(12), dcodane character varying(18),
			dcencos character varying(6), dcodmon character varying(2), ddh character varying(1), dimport numeric(14,2), dtipdoc character varying(2), dnumdoc character varying(30), 
			dfecdoc character varying(8), dfecven character varying(8), darea character varying(3), dflag character varying(1), ddate date not null, dxglosa character varying(40),
			dusimport numeric(14,2), dmnimpor numeric(14,2), dcodarc character varying(2), dfeccom2 character varying(8), dfecdoc2 character varying(8), dvanexo character varying(1), dcodane2 character varying(18),
			tarjeta character varying(1), td character varying(1), doctype character varying(5));";

		$sqlca->query($q1);
		
		$correlativo = 0;
		$contador = '0000';
		$k = 0;
		$md = 0;

		if ($sqlca->query($sql)<=0) { 	
			return NULL;	
		}    
			
		if ($sqlca->query($sql)>0) { 												
			$correlativo = ($FechaDiv[1] * 10000) + $num_actual;
			while ($reg = $sqlca->fetchRow()) {

				//reiniciar el numerador cuando sea diferente dia
				if($subdia != $reg[10]){
					$correlativo = 0;
					$correlativo = ($FechaDiv[1] * 10000) + $num_actual;
					$subdia = $reg[10];
				}

				// validando si subdiario se toma completo ($opcion=0) o con el día ($opcion=1)
				if($opcion==0) {
					$reg[10]=substr($reg[10],0,-2);
				}
															
				if (trim($reg[1]) == $vmcliente) { 
					$k=1;
					$correlativo = $correlativo + 1;
					if($FechaDiv[1]>=1 && $FechaDiv[1]<10) {
						$correlativo2 = "0".$correlativo;
					}else {
						$correlativo2 = $correlativo;
					}
				} else {
					$k = $k + 1;						
				}
				if(trim($reg[9]) == '') {
					$reg[9] = $xtradat;
				}else{
					$xtradat = trim($reg[9]);
				}										
				if($k<=9){
					$contador = "000".$k;
				}elseif($k>9 and $k<=99){
					$contador = "00".$k;
				} else {
					$contador = "0".$k;
				}

				$dfecdoc = "20".substr($reg[0],0,2).substr($reg[0],2,2).substr($reg[0],4,2);
				
				if(empty($reg[12]) || $reg[12] == '' || $reg[12] == NULL)
					$reg[12] = "0";

				$qx = $sqlca->query("INSERT INTO tmp_concar values ('".trim($reg[10])."', '".trim($correlativo2)."', '".trim($contador)."', '".trim($reg[0])."', '".trim($reg[1])."', '".trim($reg[2])."', '".trim($reg[11])."', 'MN', '".trim($reg[5])."', '".trim($reg[6])."', '".trim($reg[14])."', '".trim($reg[9])."', '".trim($reg[0])."', '".trim($reg[0])."', 'S', 'S', '".$dfecdoc."', '".trim($reg[7])."', '0', '".trim($reg[6])."', 'X', '".$dfecdoc."', '".$dfecdoc."', 'M', ' ', '".$reg[12]."', '".trim($reg[13])."', '".trim($reg[14])."');", "qxs");

			}
		}			

		//******************************** CUENTAS POR COBRAR *****************************

		// $sql_cc = "SELECT 
		// 				ccobrar_subdiario_mkt, 
		// 				ccobrar_cuenta_cliente_mkt, 
		// 				ccobrar_cuenta_caja_mkt, 
		// 				codane, 
		// 				subdiario_dia 
		// 			FROM 
		// 				concar_config;";

		// if ($sqlca->query($sql_cc) < 0) 
		// 	return false;

		// $a = $sqlca->fetchRow();

		// $ccsubdiario = $a[0];
		// $cchaber     = $a[1];
		// $ccdebe 		= $a[2];		
		// $codane 		= $a[3];
		// $opcion      = $a[4];

		$sql_cc = "
					SELECT 
						c1.account AS ccobrar_subdiario_mkt
						,c2.account AS ccobrar_cuenta_cliente_mkt
						,c3.account AS ccobrar_cuenta_caja_mkt
						,c4.account AS codane
						,c5.account AS subdiario_dia 
					FROM 
						concar_confignew c1
						LEFT JOIN concar_confignew c2 ON   c2.module = 4   AND c2.category = 1   AND c2.subcategory = 0   --Cuenta Cobrar Market Cliente
						LEFT JOIN concar_confignew c3 ON   c3.module = 4   AND c3.category = 1   AND c3.subcategory = 1   --Cuenta Cobrar Market Caja

						LEFT JOIN concar_confignew c4 ON   c4.module = 0   AND c4.category = 3   AND c4.subcategory = 0   --Codigo de Anexo
						LEFT JOIN concar_confignew c5 ON   c5.module = 0   AND c5.category = 1   AND c5.subcategory = 0   --Subdiario dia
					WHERE 
						c1.module = 4   AND c1.category = 0   AND c1.subcategory = 0;   --Subdiario de Cta Cobrar Market
					";

		if ($sqlca->query($sql_cc) < 0) 
			return false;

		$a = $sqlca->fetchRow();

		$ccsubdiario = $a[0];
		$cchaber 	 = $a[1];
		$ccdebe 	 = $a[2];		
		$codane 	 = $a[3];
		$opcion      = $a[4];

		$sqlcc = "
		SELECT * FROM(		
		SELECT
			dfeccom as dfeccom,
			'$cchaber' AS dcuenta,
			'0002' as dsecue,
			dcodane as dcodane,
			dcencos as dcencos,
			'H' as ddh,	
			dimport as dimport,
			dnumdoc as dnumdoc,
			'COB. '||substring(dxglosa from 7 for 22) as dxglosa,
			'$ccsubdiario'::text as subday,
			doctype as doctype
		FROM 
			tmp_concar 
		WHERE 
			ddh='D' AND dvanexo='M'				
		) as A
		UNION
		(	
		SELECT 
			dfeccom as dfeccom,
			'$ccdebe' AS dcuenta,
			'0001' as dsecue,
			'".$codane."' as dcodane,
			dcencos as dcencos,
			'D' as ddh,
			SUM(dimport) as dimport,
			'000000' as dnumdoc,
			'COB. '||substring(dxglosa from 7 for 22) as dxglosa,
			'$ccsubdiario'::text as subday,
			'VR' as doctype
		FROM 
			tmp_concar
		WHERE 
			ddh='D' AND dvanexo='M'
		GROUP BY 
			dfeccom,dcencos,dxglosa
		)
		ORDER BY dfeccom, dxglosa, dcuenta, dcodane desc;
		";
			
		echo "\n\n FINAL CTA. COBRAR MARKET: ".$sqlcc."\n\n";			
			
		if ($sqlca->query($sqlcc) < 0) 
			return false;	
	
		$md = 0;
		$pc = 0;	
		$cons = 0;	
		$counter = ($FechaDiv[1] * 10000) + $num_actual;
		$counterserie = ($FechaDiv[1] * 10000) + $num_actual;
		$cons = 0;//secuencial de detalles cobrar
		$diaserie = '';
		$no_documento = null;
		$nu_secue = null;
		$nu_subdia = null;
		
		while ($reg = $sqlca->fetchRow()) {	
			// validando si subdiario se toma completo ($opcion=0) o con el día ($opcion=1)
			if($opcion==0) {
				$reg[9]=substr($reg[9],0,-2);
			}

			if($reg[1] == $ccdebe)	
				$counter = $counter + 1;

			if($FechaDiv[1]>=1 && $FechaDiv[1]<10){
				$secue = "0".$counter;
			} else {
				$secue = $counter;
			}
		
			$nu_subdia = $ccsubdiario.substr($reg[0], -2);

			if($reg[5]=="D"){
				$ins1 = "
				INSERT INTO $TabSqlCab (
					CSUBDIA, CCOMPRO, CFECCOM, CCODMON, CSITUA, CTIPCAM, CGLOSA, CTOTAL,CTIPO, CFLAG, CFECCAM, CORIG, CFORM, CTIPCOM, CEXTOR
				) VALUES (
					'".$nu_subdia."', '".$secue."', '".$reg[0]."', 'MN', '', 0, '".$reg[8]."', '".$reg[6]."', 'V', 'S', '', '','','',''
				);
				";
				$arrInserts['tipo']['cabecera'][$pc] = $ins1;
				$pc++;
			}

			//reiniciar el numerador cuando sea diferente dia DSECUE
			$cons++;

			if($cons>0 and $cons<=9) {
				$secdet = "000".$cons;				
			} elseif ($cons>=10 and $cons<=99){
				$secdet = "00".$cons;
			} elseif ($cons>=100 and $cons<=999){
				$secdet = "0".$cons;
			} else {
				$secdet = $cons;
			}
		
			$ins2 = "
			INSERT INTO $TabSqlDet (
				dsubdia, dcompro, dsecue, dfeccom, dcuenta, dcodane, dcencos, dcodmon, ddh, dimport, dtipdoc, dnumdoc, dfecdoc, dfecven, 
				darea, dflag, dxglosa, dusimpor, dmnimpor, dcodarc, dcodane2, dtipcam, dmoncom, dcolcom, dbascom, dinacom, digvcom, dtpconv, 
				dflgcom, danecom, dtipaco, dmedpag, dtidref, dndoref, dfecref, dbimref, digvref, dtipdor, dnumdor, dfecdo2
			) VALUES ( 
				'".$nu_subdia."', '".$secue."', '".$secdet."', '".$reg[0]."', '".$reg[1]."', '".$reg[3]."', '', 'MN', 
				'".$reg[5]."', ".$reg[6].", '".$reg[10]."', '".$reg[7]."', '".$reg[0]."', '".$reg[0]."', '','S', '".$reg[8]."', 
				0,0,'','',0,'','',0,0,0,'','','','','','','', GETDATE(), 0,0,'','', GETDATE()
			);
			";
			$arrInserts['tipo']['detalle'][$md] = $ins2;
			$md++;
		}
				
		$q5 = $sqlca->query("DROP TABLE tmp_concar");
		$rstado = ejecutarInsert($arrInserts, $TabSqlCab, $TabSqlDet, '');
		return $rstado;
	}
	
	function Ventas_docManual($FechaIni, $FechaFin, $almacen, $codEmpresa, $num_actual) {  // VENTAS DOC MANUAL
		global $sqlca;

		$clientes = InterfaceConcarActModel::interface_clientes_documentos($FechaIni, $FechaFin, $almacen, $codEmpresa, $num_actual);

		if(trim($num_actual) == "")
			$num_actual = 0;

		$FechaIni_Original = $FechaIni;
		$FechaFin_Original = $FechaFin;
		$FechaDiv = explode("/", $FechaIni);
		$FechaIni = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];
		$anio1 	  = $FechaDiv[2];
		$mes1 	  = $FechaDiv[1];
		$dia1 	  = $FechaDiv[0];				
		$postrans = "pos_trans".$FechaDiv[2].$FechaDiv[1];
		$FechaDiv = explode("/", $FechaFin);
		$FechaFin = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];
		$anio2 	  = $FechaDiv[2];
		$mes2 	  = $FechaDiv[1];
		$dia2 	  = $FechaDiv[0];				
		$Anio 	  = substr($FechaDiv[2],2,3);
		
		if ($anio1 != $anio2 || $mes1 != $mes2) {
			return 3;// Fechas deben ser mismo mes y año
		} else {
			if ($dia1 > $dia2) 
				return 4;// Fecha inicio debe ser menor que fecha final					
		}

		$Anio 		= trim($Anio);
		$codEmpresa = trim($codEmpresa);

		$TabSqlCab = "CC".$codEmpresa.$Anio; // Tabla SQL Cabecera
		$TabSqlDet = "CD".$codEmpresa.$Anio; // Tabla SQL Detalle
		$TabCan    = "CAN".$codEmpresa;
		
		$val = InterfaceConcarActModel::verificaFecha("1", $almacen, $FechaIni, $FechaFin);

		if ($val == 5)
			return 5;		
		
// 		$sql = "
// SELECT
//  venta_subdiario_docManual, 	
//  venta_cuenta_cliente_dMa, 	
//  venta_cuenta_impuesto, 	
//  venta_cuenta_ventas_dMa, 	
//  id_centro_cos_dMa, 	
//  subdiario_dia,	
//  venta_cuenta_cliente_glp2,
//  codane_glp2,
//  venta_cuenta_ventas_glp2,
//  cod_cliente 
// FROM 
//  concar_config;
// 		";

// 		if ($sqlca->query($sql) < 0) 
// 			return false;	
	
// 		$a = $sqlca->fetchRow();
// 		$vmsubdiario = $a[0];
// 		$vmcliente   = $a[1];
// 		$vmimpuesto  = $a[2];
// 		$vmventas    = $a[3];	
// 		$vmcencos    = $a[4];	
// 		$opcion      = $a[5];				
// 		$cuentalub   = $a[6];
// 		$codanelub   = $a[7];
// 		$ventaslub   = $a[8];
// 		$codcliente  = $a[9];

		$sql = "
SELECT
	c1.account AS venta_subdiario_docManual
	,c2.account AS venta_cuenta_cliente_dMa
	,c3.account AS venta_cuenta_impuesto
	,c4.account AS venta_cuenta_ventas_dMa
	,c5.account AS id_centro_cos_dMa
	,c6.account AS subdiario_dia
	,c7.account AS venta_cuenta_cliente_glp2
	,c8.account AS codane_glp2
	,c9.account AS venta_cuenta_ventas_glp2
	,c10.account AS cod_cliente
FROM 
 	concar_confignew c1
	LEFT JOIN concar_confignew c2 ON   c2.module = 6   AND c2.category = 1   AND c2.subcategory = 0   --Cuenta Documentos Manuales Combustible Cliente
	LEFT JOIN concar_confignew c3 ON   c3.module = 6   AND c3.category = 1   AND c3.subcategory = 1   --Cuenta Documentos Manuales Impuesto
	LEFT JOIN concar_confignew c4 ON   c4.module = 6   AND c4.category = 1   AND c4.subcategory = 2   --Cuenta Documentos Manuales Combustible Ventas

	LEFT JOIN concar_confignew c5 ON   c5.module = 0   AND c5.category = 2   AND c5.subcategory = 3   --Centro de Costo Documentos Manuales
	LEFT JOIN concar_confignew c6 ON   c6.module = 0   AND c6.category = 1   AND c6.subcategory = 0   --Subdiario dia

	LEFT JOIN concar_confignew c7 ON   c7.module = 6   AND c7.category = 2   AND c7.subcategory = 0   --Cuenta Documentos Manuales GLP Cliente
	LEFT JOIN concar_confignew c8 ON   c8.module = 0   AND c8.category = 3   AND c8.subcategory = 2   --Codigo de Anexo GLP
	LEFT JOIN concar_confignew c9 ON   c9.module = 6   AND c9.category = 2   AND c9.subcategory = 1   --Cuenta Documentos Manuales GLP Ventas
	
	LEFT JOIN concar_confignew c10 ON   c10.module = 0   AND c10.category = 1   AND c10.subcategory = 1   --Codigo de Cliente
WHERE
	c1.module = 6   AND c1.category = 0   AND c1.subcategory = 0;   --Subdiario de Ventas Documentos Manuales
		";

		if ($sqlca->query($sql) < 0) 
			return false;	
	
		$a = $sqlca->fetchRow();
		$vmsubdiario = $a[0];
		$vmcliente   = $a[1];
		$vmimpuesto  = $a[2];
		$vmventas    = $a[3];	
		$vmcencos    = $a[4];	
		$opcion      = $a[5];				
		$cuentalub   = $a[6];
		$codanelub   = $a[7];
		$ventaslub   = $a[8];
		$codcliente  = $a[9];

		$sql = "
			SELECT * FROM (
				SELECT 
					to_char(date(t.dt_fac_fecha),'YYMMDD') as dia, 
					'$vmcliente'::text as DCUENTA, 
					'$codcliente'::text as codigo, 
					' '::text as trans, 
					'3'::text as tip, 
					'D'::text as ddh, 
					round(sum(d.nu_fac_valortotal),2) as importe, --Agregado para que redondee correctamente
					'VENTA MANUALES ' || substring(t.dt_fac_fecha::text from 9 for 2) || '-' || substring(t.dt_fac_fecha::text from 6 for 2) || '-' || substring(t.dt_fac_fecha::text from 3 for 2) ||' '::text as venta , 
					t.ch_almacen as sucursal, 
					t.ch_fac_seriedocumento || '-' ||MIN(cast(t.ch_fac_numerodocumento as integer))||'-'||MAX(cast(t.ch_fac_numerodocumento as integer))::text as dnumdoc,
					'$vmsubdiario'::text as subdiario,
					''::text as DCENCOS ,
					'B'::text as tip2,
					t.ch_fac_tipodocumento as tipodoc,
					' '::text as tiporef,
					' '::text as serieref, 
					' '::text as docuref  
				FROM 
					fac_ta_factura_cabecera t
					INNER JOIN fac_ta_factura_detalle d ON (d.ch_fac_numerodocumento=t.ch_fac_numerodocumento AND d.ch_fac_seriedocumento=t.ch_fac_seriedocumento AND d.ch_fac_tipodocumento=t.ch_fac_tipodocumento)
					LEFT JOIN int_articulos art ON (d.art_codigo=art.art_codigo) 
				WHERE 
					t.ch_fac_tipodocumento = '35' 
					AND date(t.dt_fac_fecha) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND t.ch_almacen = '$almacen' 
				GROUP BY 
					t.dt_fac_fecha, t.ch_almacen, t.ch_fac_tipodocumento, t.ch_fac_seriedocumento
			) as k
			UNION(
				SELECT 
					to_char(date(t.dt_fac_fecha),'YYMMDD') as dia, 
					'$vmimpuesto'::text as DCUENTA, 
					''::text as codigo, 
					' '::text as trans, 
					'3'::text as tip,  
					'H'::text as ddh, 
					round(sum(d.nu_fac_impuesto1),2) as importe, --Agregado para que redondee correctamente
					'VENTA MANUALES ' || substring(t.dt_fac_fecha::text from 9 for 2) || '-' || substring(t.dt_fac_fecha::text from 6 for 2) || '-' || substring(t.dt_fac_fecha::text from 3 for 2) ||' '::text as venta , 
					t.ch_almacen as sucursal , 
					t.ch_fac_seriedocumento || '-' ||MIN(cast(t.ch_fac_numerodocumento as integer))||'-'||MAX(cast(t.ch_fac_numerodocumento as integer))::text as dnumdoc,
					'$vmsubdiario'::text as subdiario,
					''::text as DCENCOS ,
					'B'::text as tip2,
					t.ch_fac_tipodocumento as tipodoc,
					' '::text as tiporef,
					' '::text as serieref, 
					' '::text as docuref    
				FROM 
					fac_ta_factura_cabecera t
					INNER JOIN fac_ta_factura_detalle d ON (d.ch_fac_numerodocumento=t.ch_fac_numerodocumento AND d.ch_fac_seriedocumento=t.ch_fac_seriedocumento AND d.ch_fac_tipodocumento=t.ch_fac_tipodocumento)
					LEFT JOIN int_articulos art ON (d.art_codigo=art.art_codigo)
				WHERE 
					t.ch_fac_tipodocumento = '35' 
					AND date(t.dt_fac_fecha) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND t.ch_almacen = '$almacen' 
				GROUP BY 
					 t.dt_fac_fecha, t.ch_almacen, t.ch_fac_tipodocumento, t.ch_fac_seriedocumento
			)UNION(
				SELECT 
					to_char(date(t.dt_fac_fecha),'YYMMDD') as dia, 
					'$ventaslub'::text as DCUENTA, 
					FIRST(codigo_concar)::text as codigo, 
					' '::text as trans, 
					'3'::text as tip,  
					'H'::text as ddh, 
					CASE WHEN sum(d.nu_fac_importeneto) <= 0 THEN cast(0.00 as integer) ELSE round(sum(d.nu_fac_valortotal),2)-round(sum(d.nu_fac_impuesto1),2) END as importe, --Agregado para que redondee correctamente
					'VENTA MANUALES ' || substring(t.dt_fac_fecha::text from 9 for 2) || '-' || substring(t.dt_fac_fecha::text from 6 for 2) || '-' || substring(t.dt_fac_fecha::text from 3 for 2) ||' '::text as venta, 
					t.ch_almacen as sucursal , 
					t.ch_fac_seriedocumento || '-' ||MIN(cast(t.ch_fac_numerodocumento as integer))||'-'||MAX(cast(t.ch_fac_numerodocumento as integer))::text::text as dnumdoc,
					'$vmsubdiario'::text as subdiario,
					'$vmcencos'::text as DCENCOS ,
					'B'::text as tip2,
					t.ch_fac_tipodocumento as tipodoc,
					' '::text as tiporef,
					' '::text as serieref, 
					' '::text as docuref    
				FROM 
					fac_ta_factura_cabecera t
					INNER JOIN fac_ta_factura_detalle d ON (d.ch_fac_numerodocumento=t.ch_fac_numerodocumento AND d.ch_fac_seriedocumento=t.ch_fac_seriedocumento AND d.ch_fac_tipodocumento=t.ch_fac_tipodocumento)
					LEFT JOIN int_articulos art ON (d.art_codigo=art.art_codigo) 
					LEFT JOIN interface_equivalencia_producto q ON (art.art_codigo = q.art_codigo)
				WHERE 
					t.ch_fac_tipodocumento = '35'  
					AND date(t.dt_fac_fecha) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND t.ch_almacen = '$almacen' 
				GROUP BY 
					t.dt_fac_fecha, t.ch_almacen, t.ch_fac_tipodocumento, t.ch_fac_seriedocumento
			)UNION(--- EMPIEZA FACTURAS, NOTAS DE DEBITOS Y NOTAS DE CRÉDITOS
				SELECT 
					to_char(t.dt_fac_fecha,'YYMMDD') as dia, 
					'$vmcliente'::text as DCUENTA,  
					(CASE WHEN SUBSTRING(t.ch_fac_seriedocumento FROM '[A-Z]+') = 'B' THEN '$codcliente'::text ELSE FIRST(c.cli_ruc)::text END) as codigo, 
					t.ch_fac_numerodocumento::text as trans, 
					'3'::text as tip, 
					CASE
						WHEN t.ch_fac_tipodocumento = '20' THEN 'H'::text 
						ELSE 'D'
					END AS ddh,
					-- round(t.nu_fac_valortotal,2) as importe, --Agregado para que redondee correctamente
					round(sum(d.nu_fac_valortotal),2) as importe,
					'VENTA MANUALES ' || substring(t.dt_fac_fecha::text from 9 for 2) || '-' || substring(t.dt_fac_fecha::text from 6 for 2) || '-' || substring(t.dt_fac_fecha::text from 3 for 2) ||' '::text as venta , 
					t.ch_almacen as sucursal, 
					t.ch_fac_seriedocumento || '-' ||  substr(t.ch_fac_numerodocumento,2)::text as dnumdoc,
					'$vmsubdiario' as subdiario,             
					''::text as DCENCOS ,
					'D'::text as tip2,
					t.ch_fac_tipodocumento as tipodoc,
					(string_to_array(FIRST(r.ch_fac_observacion2), '*'))[3]::text as tiporef,
					(string_to_array(FIRST(r.ch_fac_observacion2), '*'))[2]::text as serieref,
					(string_to_array(FIRST(r.ch_fac_observacion2), '*'))[1]::text as docuref
				FROM 
					fac_ta_factura_cabecera t
					LEFT JOIN fac_ta_factura_detalle d ON (d.ch_fac_numerodocumento = t.ch_fac_numerodocumento AND d.ch_fac_seriedocumento = t.ch_fac_seriedocumento AND d.ch_fac_tipodocumento = t.ch_fac_tipodocumento)
					LEFT JOIN fac_ta_factura_complemento r ON (r.ch_fac_numerodocumento = t.ch_fac_numerodocumento AND r.ch_fac_seriedocumento = t.ch_fac_seriedocumento AND r.ch_fac_tipodocumento = t.ch_fac_tipodocumento)
					INNER JOIN int_clientes c ON (c.cli_codigo = t.cli_codigo)
					LEFT JOIN int_articulos art ON (d.art_codigo = art.art_codigo) 
   					LEFT JOIN interface_equivalencia_producto q ON (art.art_codigo = q.art_codigo)
				WHERE 
					t.ch_fac_tipodocumento IN ('10','11','20') 
					AND t.dt_fac_fecha BETWEEN '$FechaIni' AND '$FechaFin' 
					AND t.nu_fac_valortotal > 0.00 
					AND t.ch_almacen = '$almacen'
				GROUP BY 
					t.dt_fac_fecha, t.ch_almacen,t.ch_fac_seriedocumento, t.ch_fac_numerodocumento,t.nu_fac_valortotal, t.ch_fac_tipodocumento
				ORDER BY 
					t.dt_fac_fecha
			)UNION(
				SELECT 
					to_char(t.dt_fac_fecha,'YYMMDD') as dia, 
					'$vmimpuesto'::text as DCUENTA,  
					''::text as codigo, 
					t.ch_fac_numerodocumento::text as trans, 
					'3'::text as tip, 
					CASE
						WHEN t.ch_fac_tipodocumento = '20' THEN 'D'::text 
						ELSE 'H'
					END AS ddh,
					-- round(t.nu_fac_impuesto1,2) as importe, --Agregado para que redondee correctamente
					round(sum(d.nu_fac_impuesto1),2) as importe,
					'VENTA MANUALES ' || substring(t.dt_fac_fecha::text from 9 for 2) || '-' || substring(t.dt_fac_fecha::text from 6 for 2) || '-' || substring(t.dt_fac_fecha::text from 3 for 2) ||' '::text as venta , 
					t.ch_almacen as sucursal , 
					t.ch_fac_seriedocumento || '-' ||  substr(t.ch_fac_numerodocumento,2)::text as dnumdoc,
					'$vmsubdiario' as subdiario,
					''::text as DCENCOS ,
					'D'::text as tip2,
					t.ch_fac_tipodocumento as tipodoc,
					' '::text as tiporef,
					' '::text as serieref, 
					' '::text as docuref 
				FROM 
					fac_ta_factura_cabecera t
					LEFT JOIN fac_ta_factura_detalle d ON (d.ch_fac_numerodocumento = t.ch_fac_numerodocumento AND d.ch_fac_seriedocumento = t.ch_fac_seriedocumento AND d.ch_fac_tipodocumento = t.ch_fac_tipodocumento)
					INNER JOIN int_clientes c ON (c.cli_codigo = t.cli_codigo)
					LEFT JOIN int_articulos art ON (d.art_codigo = art.art_codigo) 
					LEFT JOIN interface_equivalencia_producto q ON (art.art_codigo = q.art_codigo)
				WHERE 
					t.ch_fac_tipodocumento IN ('10','11','20') 
					AND t.dt_fac_fecha BETWEEN '$FechaIni' AND '$FechaFin' 
					AND t.nu_fac_valortotal>0 
					AND t.ch_almacen = '$almacen' 
				GROUP BY 
					t.dt_fac_fecha, t.ch_almacen,t.ch_fac_seriedocumento, t.ch_fac_numerodocumento,t.nu_fac_impuesto1, t.ch_fac_tipodocumento
				ORDER BY 
					t.dt_fac_fecha
			)UNION(
				SELECT 
					to_char(t.dt_fac_fecha,'YYMMDD') as dia, 
					'$ventaslub'::text as DCUENTA,  
					'701101C01'::text as codigo,
					t.ch_fac_numerodocumento::text as trans, 
					'3'::text as tip,  
					CASE
						WHEN t.ch_fac_tipodocumento = '20' THEN 'D'::text 
						ELSE 'H'
					END AS ddh,
					--CASE WHEN sum(d.nu_fac_importeneto) is null THEN cast(0.00 as decimal) ELSE round(FIRST(t.nu_fac_valortotal),2)-round(FIRST(t.nu_fac_impuesto1),2) END as importe, --Agregado para que redondee correctamente
					CASE WHEN sum(d.nu_fac_importeneto) is null THEN cast(0.00 as decimal) ELSE round(sum(d.nu_fac_valortotal),2) - round(sum(d.nu_fac_impuesto1),2) END as importe, --CAMBIO
					'VENTA MANUALES ' || substring(t.dt_fac_fecha::text from 9 for 2) || '-' || substring(t.dt_fac_fecha::text from 6 for 2) || '-' || substring(t.dt_fac_fecha::text from 3 for 2) ||' '::text as venta, 
					t.ch_almacen as sucursal , 
					t.ch_fac_seriedocumento || '-' ||  substr(t.ch_fac_numerodocumento,2)::text as dnumdoc,
					'$vmsubdiario' as subdiario,
					'$vmcencos'::text as DCENCOS ,
					'D'::text as tip2,
					t.ch_fac_tipodocumento as tipodoc,
					' '::text as tiporef,
					' '::text as serieref, 
					' '::text as docuref  
				FROM 
					fac_ta_factura_cabecera t
					LEFT JOIN fac_ta_factura_detalle d ON (d.ch_fac_numerodocumento = t.ch_fac_numerodocumento AND d.ch_fac_seriedocumento = t.ch_fac_seriedocumento AND d.ch_fac_tipodocumento = t.ch_fac_tipodocumento)
					INNER JOIN int_clientes c ON (c.cli_codigo = t.cli_codigo)
					LEFT JOIN int_articulos art ON (d.art_codigo = art.art_codigo) 
					LEFT JOIN interface_equivalencia_producto q ON (art.art_codigo = q.art_codigo)
				WHERE 
					t.ch_fac_tipodocumento IN ('10','11','20') 
					AND t.dt_fac_fecha BETWEEN '$FechaIni' AND '$FechaFin' 
					AND t.nu_fac_valortotal > 0
					AND t.ch_almacen = '$almacen'
					--AND q.art_codigo IS NULL
				GROUP BY 
					t.dt_fac_fecha, t.ch_almacen,t.ch_fac_seriedocumento, t.ch_fac_numerodocumento,t.nu_fac_valorbruto, t.ch_fac_tipodocumento
				ORDER BY 
					t.dt_fac_fecha
			)UNION(--- EMPIEZA DOCUMENTOS ANULADOS FACTURAS, NOTAS DE DEBITOS Y NOTAS DE CRÉDITOS
				SELECT 
					to_char(t.dt_fac_fecha,'YYMMDD') as dia, 
					'$vmcliente'::text as DCUENTA,  
					'0001'::text as codigo,
					t.ch_fac_numerodocumento::text as trans, 
					'3'::text as tip, 
					'D'::text as ddh,
					round(t.nu_fac_valortotal,2) as importe, --Agregado para que redondee correctamente
					'VENTA MANUALES ' || substring(t.dt_fac_fecha::text from 9 for 2) || '-' || substring(t.dt_fac_fecha::text from 6 for 2) || '-' || substring(t.dt_fac_fecha::text from 3 for 2) ||' '::text as venta , 
					t.ch_almacen as sucursal, 
					t.ch_fac_seriedocumento || '-' ||  substr(t.ch_fac_numerodocumento,2)::text as dnumdoc,
					'$vmsubdiario' as subdiario,             
					''::text as DCENCOS ,
					'D'::text as tip2,
					t.ch_fac_tipodocumento as tipodoc,
					' '::text as tiporef,
					' '::text as serieref, 
					' '::text as docuref    
				FROM 
					fac_ta_factura_cabecera t
					LEFT JOIN fac_ta_factura_detalle d ON (d.ch_fac_numerodocumento = t.ch_fac_numerodocumento AND d.ch_fac_seriedocumento = t.ch_fac_seriedocumento AND d.ch_fac_tipodocumento = t.ch_fac_tipodocumento)
					INNER JOIN int_clientes c ON (c.cli_codigo = t.cli_codigo)
				WHERE 
					t.ch_fac_tipodocumento IN('10','11','20','35')
					AND t.dt_fac_fecha BETWEEN '$FechaIni' AND '$FechaFin'
					AND t.nu_fac_valortotal <= 0
				GROUP BY 
					t.dt_fac_fecha, t.ch_fac_numerodocumento,t.ch_fac_seriedocumento,c.cli_ruc,t.ch_almacen,t.nu_fac_valortotal, t.ch_fac_tipodocumento 
				ORDER BY 
					t.dt_fac_fecha
			)

		ORDER BY dia, tip2, tip, tipodoc, dnumdoc, DCUENTA, ddh;";

		echo "<pre>";
		echo "VENTAS MANUALES: \n\n".$sql."\n\n";	
		echo "</pre>";

		$centimo = "CREATE TABLE tmp_concar_centimo (dsubdia character varying(7), dcompro character varying(6), dsecue character varying(7), dfeccom character varying(6), dcuenta character varying(12), dcodane character varying(18),
			dcencos character varying(6), dcodmon character varying(2), ddh character varying(1), dimport numeric(14,2), dtipdoc character varying(2), dnumdoc character varying(20), 
			dfecdoc character varying(8), dfecven character varying(8), darea character varying(15), dflag character varying(1), ddate date not null, dxglosa character varying(40),
			dusimport numeric(14,2), dmnimpor numeric(14,2), dcodarc character varying(2), dfeccom2 character varying(8), dfecdoc2 character varying(8), dvanexo character varying(1), dcodane2 character varying(18), dtidref character varying(2), dndoref character varying(30), dfecref date);";

		$sqlca->query($centimo);
                  
		$correlativo = 0;
		$contador = '0000';  
		$k = 0;  
		$md = 0;     
			
		if ($sqlca->query($sql)>0) {										
			$correlativo = ($FechaDiv[1] * 10000) + $num_actual;
			while ($reg = $sqlca->fetchRow()) {
				//validando si subdiario se toma completo ($opcion=0) o con el día ($opcion=1)
				if($opcion==0) {
					$reg[10]=substr($reg[10],0,-2);
				}
															
				if (substr($reg[1],0,3) == substr($vmcliente,0,3)) { 
					$k=1;
					$correlativo = $correlativo + 1;
					if($FechaDiv[1]>=1 && $FechaDiv[1]<10) {
						$correlativo2 = "0".$correlativo;
					}else {
						$correlativo2 = $correlativo;
					}
				} else {
					$k = $k + 1;						
				}
				if(trim($reg[9]) == '') {
					$reg[9] = $xtradat;
				}else{
					$xtradat = trim($reg[9]);
				}		
				if($k<=9){
					$contador = "000".$k;
				}elseif($k>9 and $k<=99){
					$contador = "00".$k;
				} else {
					$contador = "0".$k;
				}
						
				$dfecdoc = "20".substr($reg[0],0,2).substr($reg[0],2,2).substr($reg[0],4,2);				
						
				if($reg[13] == '10')
					$tipodoc = 'FT';
				elseif($reg[13] == '11')
					$tipodoc = 'ND';
				elseif($reg[13] == '20')
					$tipodoc = 'NA';
				elseif($reg[13] == '35')
					$tipodoc = 'BV';

				if($reg[6] <= 0){
					$cflag = "N";
					$cabglosam = "ANULADA ".$tipodoc." - ".$reg[9];
				}else{
					$cflag = "S";
					$cabglosam = "VENTA MANUAL ".substr($reg[0],4,2)."-".substr($reg[0],2,2)."-".substr($reg[0],0,2);				
				}

				if(empty($reg[14]))
					$tiporef = "";
				else
					$tiporef = $reg[14];

				if(empty($reg[15]))
					$serieref = "";
				else
					$serieref = $reg[15];

				if(empty($reg[16]))
					$docuref = "";
				else
					$docuref = $reg[16];

				$centimo2 = $sqlca->query("INSERT INTO tmp_concar_centimo VALUES ('".trim($reg[10])."', '".trim($correlativo2)."', '".trim($contador)."', '".trim($reg[0])."', '".trim($reg[1])."', '".trim($reg[2])."', '".trim($reg[11])."', 'MN', '".trim($reg[5])."', '".trim($reg[6])."', '".trim($tipodoc)."', '".trim($reg[9])."', '".trim($reg[0])."', '".trim($reg[0])."','".trim($serieref)."', 'S', '".$dfecdoc."', '".trim($reg[7])."', '0', '".trim($reg[6])."', 'X', '".$dfecdoc."', '".$dfecdoc."', 'P', '".trim($docuref)."', '".trim($tiporef)."');", "concar_insert");
			}
		}
		error_log("PASO 1: Creacion de tmp_concar_centimo");

		// creando el vector de diferencia
		// $c = 0;
		// $imp = 0;
		// $flag = 0;
		// $diferencia = "SELECT * FROM tmp_concar_centimo ORDER BY dsubdia, dcompro, dcuenta, dsecue;";
		// if ($sqlca->query($diferencia)>0){
		// 	while ($reg = $sqlca->fetchRow()){
		// 		if (substr($reg[4],0,3) == substr($vmcliente,0,3)){
		// 			if ($flag == 1) {
		// 				$vec[$c] = $imp;
		// 				$c = $c + 1;
		// 			}
		// 			$imp = trim($reg[9]);
		// 		} else {
		// 			$imp = round(($imp-$reg[9]), 2);
		// 			$flag = 1;
		// 		}
		// 	}
		// 	$vec[$c] = $imp;
		// }

		// error_log( json_encode($vec) );
		// return false;

		// actualizar tabla tmp_concar sumando las diferencias al igv
		// $k = 0;
		// if ($sqlca->query($diferencia)>0){
		// 	while ($reg = $sqlca->fetchRow()){
		// 		if (trim($reg[4] == $vmimpuesto)){
		// 			$dif = $reg[9] + $vec[$k];
		// 			$k = $k + 1;
		// 			$sale = $sqlca->query("UPDATE tmp_concar_centimo SET dimport = ".$dif." WHERE dcompro = '".trim($reg[1])."' AND dcuenta='$vmimpuesto' and trim(dcodane)='' AND trim(dsubdia) = '".trim($reg[0])."';", "queryaux2"); // antes: dcodane='99999999999', con ultimo cambio : dcodane=''
		// 		}
		// 	}
		// }
		// error_log("PASO 2: Actualizamos tmp_concar_centimo");

		// return false;

		// Nueva forma de corrección de centimos
		// $arrData = array();
	    // $sSerieNumeroDocumento = '';
	    // $fTotal4070 = 0;
	    // $fTotal12 = 0;
		// if ($sqlca->query($diferencia)>0){
		// 	while ($reg = $sqlca->fetchRow()){
		//         if( substr($reg[4],0,2) == '12' && $sSerieNumeroDocumento != $reg[11]){
		//             $fTotal4070 = 0.00;
		//             $fTotal12 = $reg[9];
		//             $sSerieNumeroDocumento = $reg[11];
		//         }

		//         if( substr($reg[4],0,2) != '12' && $sSerieNumeroDocumento == $reg[11]){
		//             $fTotal4070 += $reg[9];
		//         }

		//         if ( substr($reg[4],0,2) == '40' && $sSerieNumeroDocumento == $reg[11] ){//Solo se restará a la cuenta IGV 40
		//             $arrData = array(
		//             	"subdia" => $reg[0],
		//             	"dcompro" => $reg[1],
		//                 "documento" => $reg[11],
		//                 "cuenta" => $reg[4],
		//                 "importe" => $reg[9],
		//             );
		//         }

		//         if ( $sSerieNumeroDocumento == $reg[11] && ($fTotal4070 == ($fTotal12 + 0.01) || $fTotal4070 == ($fTotal12 - 0.01)) ) {
		//             if( $fTotal12 > $fTotal4070) {
		//             	$fTotal = (double)$arrData['importe'] + 0.01;
		// 				$sale = $sqlca->query("UPDATE tmp_concar_centimo SET dimport = ".$fTotal." WHERE dcompro='".trim($arrData['dcompro'])."' AND dcuenta='" . $arrData['cuenta'] . "' AND trim(dcodane)='' AND trim(dsubdia)='".trim($arrData['subdia'])."';", "queryaux2");
		//             } else {
		//             	$fTotal = (double)$arrData['importe'] - 0.01;
		//             	$sale = $sqlca->query("UPDATE tmp_concar_centimo SET dimport = ".$fTotal." WHERE dcompro='".trim($arrData['dcompro'])."' AND dcuenta='" . $arrData['cuenta'] . "' AND trim(dcodane)='' AND trim(dsubdia)='".trim($arrData['subdia'])."';", "queryaux2");
		//             }
		//         }
		//     }
		// }
		// error_log("PASO 3: Actualizamos tmp_concar_centimo");

		// pasando la nueva tabla a texto2
		$qfinal = "SELECT * FROM tmp_concar_centimo ORDER BY dsubdia, dcodane2, dcompro, dcuenta, dsecue; ";
		$arrInserts = Array();
		$pd = 0;
		$rc = 0;

		if ($sqlca->query($qfinal)>0) {
			while ($reg = $sqlca->fetchRow()){

				if($reg[14] != ''){

					if($reg[25] == "01" || $reg[25] == "10")
						$dtidref = "10";
					elseif($reg[25] == "03" || $reg[25] == "35")
						$dtidref = "35";

					$reftip[$rc] 	= $dtidref;
					$refserie[$rc] 	= $reg[14];
					$refnum[$rc] 	= $reg[24];

					$rc++;
				}
			}
		}
		error_log("PASO 4: Verificamos reftip, refserie, refnum");
		error_log(json_encode( array( 
			"reftip" => $reftip, 
			"refserie" => $refserie, 
			"refnum" => $refnum 
		) ));

		/* ESTO ES PARA SACAR EL IGV Y BASE IMPONIBLE DE UNA NOTA DE CREDITO AMARRADA A UNA FACTURA MANUAL */

		$complemento = "
		SELECT * FROM (
			SELECT
				'".$vmcliente."'::TEXT AS tipo,
				ch_fac_seriedocumento||'-'||ch_fac_numerodocumento AS docu,
				nu_fac_impuesto1 AS igv,
				nu_fac_valorbruto AS bi,
				dt_fac_fecha AS fe_emision_ref,
				'' AS orden
			FROM
				fac_ta_factura_cabecera
			WHERE
		";

		if (count($refserie) > 0) { 
			$complemento .= "
				ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento IN(";
			for ($i = 0; $i < count($refserie); $i++) {
				$serdocu = $reftip[$i].$refserie[$i].$refnum[$i];
				if ($i > 0)
					$complemento .= ",";
				$complemento .= "'" . pg_escape_string($serdocu) . "'";
			}
			$complemento .= ") ";
		}else{
			$complemento .= "ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento IN('-')";
		}

		$complemento .= "
		) AS A UNION ALL(
		SELECT
			'".$vmcliente."'::TEXT AS tipo,
			usr AS docu,
			ROUND(igv, 2) AS igv,
			ROUND((importe - igv), 2) AS bi,
			fecha::date AS fe_emision_ref,
			SUBSTR(TRIM(usr), 6) AS orden
		FROM
			$postrans
		WHERE
		";
		if (count($refserie) > 0) { 
			$complemento .= "
				'10'||SUBSTR(TRIM(usr), 0, 5)||SUBSTR(TRIM(usr), 6) IN (";
			for ($i = 0; $i < count($refserie); $i++) {
				$serdocu = $reftip[$i].$refserie[$i].$refnum[$i];
				if ($i > 0)
					$complemento .= ",";
				$complemento .= "'" . pg_escape_string($serdocu) . "'";
			}
			$complemento .= ") OR '35'||SUBSTR(TRIM(usr), 0, 5)||SUBSTR(TRIM(usr), 6) IN (";
			for ($i = 0; $i < count($refserie); $i++) {
				$serdocu = $reftip[$i].$refserie[$i].$refnum[$i];
				if ($i > 0)
					$complemento .= ",";
				$complemento .= "'" . pg_escape_string($serdocu) . "'";
			}
			$complemento .= ")";
		}else{
			$complemento .= "'10'||SUBSTR(TRIM(usr), 0, 5)||SUBSTR(TRIM(usr), 6) IN ('-')";
			$complemento .= " OR '35'||SUBSTR(TRIM(usr), 0, 5)||SUBSTR(TRIM(usr), 6) IN ('-')";
		}

		$complemento .= "
		)
		ORDER BY
			orden;
		";

		echo "<pre>";
		echo "Complemento: \n\n".$complemento."\n\n";		
		echo "</pre>";

		$sqlca->query($complemento);

		$datos = array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$r = $sqlca->fetchRow();
			$datos[$i]['tipo'] 				= $r['tipo'];
			$datos[$i]['docu'] 				= $r['docu'];
			$datos[$i]['igv'] 				= $r['igv'];
			$datos[$i]['bi'] 				= $r['bi'];
			$datos[$i]['fe_emision_ref'] 	= $r['fe_emision_ref'];
		}

		$cr				= 0;
		$dtidref 		= null;
		$serieynumeref 	= null;
		$fe_emision_ref = "''";
		$compleigv 		= 0.00;
		$complebi 		= 0.00;

		echo "datos:";
		echo "<pre>";		
		print_r($datos);
		echo "</pre>";

		if ($sqlca->query($qfinal)>0) {
			while ($reg = $sqlca->fetchRow()){
				if($reg[9] <= 0)
					$cflag = 'N';
				else
					$cflag = 'S';

				if($reg[14] != ''){//SERIE REFERENCIA

					if($reg[25] == "01" || $reg[25] == "10")
						$dtidref = "FT";
					elseif($reg[25] == "03" || $reg[25] == "35")
						$dtidref = "BV";
					else
						$dtidref = "";

//echo "\n1:".$datos[$cr]['tipo'].'='.$reg[4]."\n";
//echo "2:".$datos[$cr]['docu'].'='.$reg['darea'].'-'.$reg[24]."\n";

					if($datos[$cr]['tipo'] == $reg[4] && $datos[$cr]['docu'] == $reg['darea'].'-'.$reg[24]){
						$serieynumeref 	= $reg['darea'].'-'.$reg[24];
						$fe_emision_ref = "convert(datetime, '".substr($datos[$cr]['fe_emision_ref'],0,19)."', 120)";
						$compleigv 		= $datos[$cr]['igv'];
						$complebi 		= $datos[$cr]['bi'];

						$cr++;

					}else{
						$dtidref = '';
						$serieynumeref = null;
						$fe_emision_ref = "''";
						$compleigv = 0.00;
						$complebi = 0.00;
					}
				}else{
					$dtidref = '';
					$serieynumeref = null;
					$fe_emision_ref = "''";
					$compleigv = 0.00;
					$complebi = 0.00;
				}

				$ins = "INSERT INTO $TabSqlDet 
					(	dsubdia, dcompro, dsecue, dfeccom, dcuenta, dcodane, dcencos, dcodmon, ddh, dimport, dtipdoc, dnumdoc, dfecdoc, 
						dfecven, darea, dflag, dxglosa, dusimpor, dmnimpor, dcodarc, dcodane2, dtipcam, dmoncom, dcolcom, dbascom, dinacom, 
						digvcom, dtpconv, dflgcom, danecom, dtipaco, dmedpag, dtidref, dndoref, dfecref, dbimref, digvref, dtipdor, dnumdor, dfecdo2
					) VALUES (
						'".$reg[0]."', '".$reg[1]."', '".$reg[2]."', '".$reg[3]."', '".$reg[4]."', '".$reg[5]."', '".$reg[6]."', '".$reg[7]."', '".$reg[8]."', ".$reg[9].", '".$reg[10]."', '".$reg[11]."', '".$reg[13]."', '".$reg[13]."', '', '".$cflag."', '".$reg[17]."', 0,0,'','',0,'','',0,0,0,'','','','','', '".$dtidref."', '".$serieynumeref."', $fe_emision_ref, ".$complebi.", ".$compleigv.",'','', GETDATE());";

				$arrInserts['tipo']['detalle'][$pd] = $ins;
				$pd++;					
			}
		}

		$correlativo=0;
		$mc = 0; 
		if ($sqlca->query($sql)>0) {		
			$correlativo = ($FechaDiv[1] * 10000) + $num_actual;
			while ($reg = $sqlca->fetchRow()) {	
				// validando si subdiario se toma completo ($opcion=0) o con el día ($opcion=1)
				if($opcion==0) {
					$reg[10]=substr($reg[10],0,-2);
				}		
															
				if (substr($reg[1],0,3) == substr($vmcliente,0,3)) { 							

					$correlativo = $correlativo + 1;

					if($FechaDiv[1]>=1 && $FechaDiv[1]<10){
						$correlativo2 = "0".$correlativo;
					}else {
						$correlativo2 = $correlativo;
					}
							
					$dfecdoc = "20".substr($reg[0],0,2).substr($reg[0],2,2).substr($reg[0],4,2);			
						
					if($reg[13] == '10')
						$tipodoc = 'FT';
					elseif($reg[13] == '11')
						$tipodoc = 'ND';
					elseif($reg[13] == '20')
						$tipodoc = 'NA';
					elseif($reg[13] == '35')
						$tipodoc = 'BV';

					if($reg[6] <= 0){
						$cflag = "N";
						$cabglosam = "ANULADA ".$tipodoc." - ".$reg[9];
					}else{
						$cflag = "S";
						$cabglosam = "VENTA MANUAL ".substr($reg[0],4,2)."-".substr($reg[0],2,2)."-".substr($reg[0],0,2);			
					}

					$ins = "INSERT INTO $TabSqlCab 
						( 	CSUBDIA, CCOMPRO, CFECCOM, CCODMON, CSITUA, CTIPCAM, CGLOSA, CTOTAL, CTIPO, CFLAG, CFECCAM, CORIG, CFORM, CTIPCOM, CEXTOR
						) VALUES (
       							'".$reg[10]."', '".$correlativo2."', '".$reg[0]."', 'MN', '', 0, '".$cabglosam."', '".$reg[6]."', 'M','".$cflag."', '','','','',''
						);";

					$arrInserts['tipo']['cabecera'][$mc] = $ins;
					$mc++;	
				}													
			}
		} 

		$q5 = $sqlca->query("DROP TABLE tmp_concar_centimo");

		$arrInserts['clientes'] = $clientes[0]["clientes"];

		$rstado = ejecutarInsert($arrInserts, $TabSqlCab, $TabSqlDet, $clientes[1]);	
		
		return $rstado;		
	}

	function Cobrar_docManual($FechaIni, $FechaFin, $almacen, $codEmpresa, $num_actual) {  // CUENTAS POR COBRAR DOC MANUAL
		global $sqlca;

		if(trim($num_actual)=="")
			$num_actual = 0;
		$FechaIni_Original = $FechaIni;
		$FechaFin_Original = $FechaFin;
		$FechaDiv = explode("/", $FechaIni);
		$FechaIni = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];
		$anio1 	  = $FechaDiv[2];
		$mes1 	  = $FechaDiv[1];
		$dia1 	  = $FechaDiv[0];				
		$postrans = "pos_trans".$FechaDiv[2].$FechaDiv[1];
		$FechaDiv = explode("/", $FechaFin);
		$FechaFin = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];
		$anio2 	  = $FechaDiv[2];
		$mes2 	  = $FechaDiv[1];
		$dia2 	  = $FechaDiv[0];				
		$Anio 	  = substr($FechaDiv[2],2,3);
		
		if ($anio1 != $anio2 || $mes1 != $mes2) {
			return 3; // Fechas deben ser mismo mes y año
		} else {
			if ($dia1 > $dia2) 
				return 4; // Fecha inicio debe ser menor que fecha final					
		}
				
///////////////////// de prueba luego borrar
//$Anio = "12";		
		
		$Anio = trim($Anio);
		$codEmpresa = trim($codEmpresa);

		$TabSqlCab = "CC".$codEmpresa.$Anio; // Tabla SQL Cabecera
		$TabSqlDet = "CD".$codEmpresa.$Anio; // Tabla SQL Detalle
		$TabCan    = "CAN".$codEmpresa;
		
		$val = InterfaceConcarActModel::verificaFecha("1", $almacen, $FechaIni, $FechaFin);
		if ($val == 5)
			return 5;		
		
		$sql = "SELECT 
				venta_subdiario_docManual, 	
				venta_cuenta_cliente_dMa, 	
				venta_cuenta_impuesto, 	
				venta_cuenta_ventas_dMa, 	
				id_centro_cos_dMa, 
				subdiario_dia 
			FROM 
				concar_config;";
		if ($sqlca->query($sql) < 0) 
			return false;	
	
		$a = $sqlca->fetchRow();
		$vmsubdiario = $a[0];
		$vmcliente   = $a[1];
		$vmimpuesto  = $a[2];
		$vmventas    = $a[3];	
		$vmcencos    = $a[4];	
		$opcion      = $a[5];				

		$sql = "SELECT * FROM 
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmcliente'::text as DCUENTA, 
					'99999999999'::text as codigo, 
					' '::text as trans, 
					'2'::text as tip, 
					'D'::text as ddh, 
					round(sum(importe),2) as importe, 
					'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' '::text as venta , 
					es as sucursal, 
					MIN(cast(trans as integer))||'-'||MAX(cast(trans as integer))::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS 
				FROM 
					$postrans 
				WHERE 
					td = 'B' 
					AND tipo = 'M' 
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND es = '$almacen' 
				GROUP BY 
					dia, es 
				ORDER BY 
					dia
			) as K 
			
			UNION
			
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmimpuesto'::text as DCUENTA, 
					'99999999999'::text as codigo, 
					' '::text as trans, 
					'2'::text as tip,  
					'H'::text as ddh, 
					round(sum(igv),2) as importe, 
					'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' '::text as venta , 
					es as sucursal , 
					MIN(cast(trans as integer))||'-'||MAX(cast(trans as integer))::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS 
				FROM 
					$postrans 
				WHERE 
					td = 'B' 
					AND tipo='M' 
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND es = '$almacen' 
				GROUP BY 
					 dia, es 
				ORDER BY 
					dia
			)
			
			UNION 
			
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmventas'::text as DCUENTA, 
					codigo_concar::text as codigo, 
					' '::text as trans, 
					'2'::text as tip,  
					'H'::text as ddh, 
					round(sum(importe-igv),2) as importe, 
					'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' '::text as venta, 
					es as sucursal , 
					MIN(cast(trans as integer))||'-'||MAX(cast(trans as integer))::text::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS 
				FROM 
					$postrans
				WHERE 
					td = 'B' 
					AND tipo='M' 
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND es = '$almacen' 
				GROUP BY 
					dia, es, codigo_concar 
				ORDER BY 
					dia
			) 
		
			UNION
			
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmcliente'::text as DCUENTA,  
					ruc::text as codigo, 
					trans::text as trans,
					'2'::text as tip, 
					'D'::text as ddh, 
					round(sum(importe),2) as importe,
					'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' '::text as venta , 
					es as sucursal, 
					''||trans::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,             
					'$vmcencos'::text as DCENCOS 
				FROM 
					$postrans 
				WHERE 
					td = 'F' 
					AND tipo='M' 
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND importe>0 
					AND es = '$almacen' 
				GROUP BY 
					dia, trans,ruc,es 
				ORDER BY 
					dia
			)
						
			UNION
			
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmimpuesto'::text as DCUENTA,  
					ruc::text as codigo, 
					trans::text as trans, 
					'2'::text as tip, 
					'H'::text as ddh, 
					round(sum(igv),2) as importe, 
					'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' '::text as venta , 
					es as sucursal , 
					''||trans::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS 
				FROM 
					$postrans 
				WHERE 
					td = 'F' 
					AND tipo='M' 
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND importe>0 
					AND es = '$almacen' 
				GROUP BY 
					dia, trans, es, ruc 
				ORDER BY 
					dia
			) 

			UNION
			
			(
				SELECT 
					to_char(date(dia),'YYMMDD') as dia, 
					'$vmventas'::text as DCUENTA,  
					codigo_concar::text as codigo,
					trans::text as trans,
					'2'::text as tip,  
					'H'::text as ddh, 
					round(sum(importe-igv),2) as importe, 
					'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||' '::text as venta, 
					es as sucursal , 
					''||trans::text as dnumdoc,
					'$vmsubdiario'||substring(dia::text from 9 for 2)::text as subdiario,
					'$vmcencos'::text as DCENCOS 
				FROM 
					$postrans
				WHERE 
					td = 'F' 
					AND tipo='M' 
					AND date(dia) BETWEEN '$FechaIni' AND '$FechaFin' 
					AND importe>0  
					AND es = '$almacen' 
				GROUP BY 
					dia, trans, es, codigo_concar 
				ORDER BY 
					dia
			) 

			ORDER BY dia, tip, trans, ddh, DCUENTA;";
						
		$q1 = "CREATE TABLE tmp_concar (dsubdia character varying(7), dcompro character varying(6), dsecue character varying(7), dfeccom character varying(6), dcuenta character varying(12), dcodane character varying(18),
			dcencos character varying(6), dcodmon character varying(2), ddh character varying(1), dimport numeric(14,2), dtipdoc character varying(2), dnumdoc character varying(20), 
			dfecdoc character varying(8), dfecven character varying(8), darea character varying(3), dflag character varying(1), ddate date not null, dxglosa character varying(40),
			dusimport numeric(14,2), dmnimpor numeric(14,2), dcodarc character varying(2), dfeccom2 character varying(8), dfecdoc2 character varying(8), dvanexo character varying(1), dcodane2 character varying(18));";
		$sqlca->query($q1);
		
		$correlativo = 0;
		$contador = '0000';  
		$k = 0;  
		$md = 0;     
			
		if ($sqlca->query($sql)>0) { 												
			$correlativo = ($FechaDiv[1] * 10000) + $num_actual;
			while ($reg = $sqlca->fetchRow()) {
				// validando si subdiario se toma completo ($opcion=0) o con el día ($opcion=1)
				if($opcion==0) {
					$reg[10]=substr($reg[10],0,-2);
				}
															
				if (trim($reg[1]) == $vmcliente) { 
					$k=1;
					$correlativo = $correlativo + 1;
					if($FechaDiv[1]>=1 && $FechaDiv[1]<10) {
						$correlativo2 = "0".$correlativo;
					}else {
						$correlativo2 = $correlativo;
					}
				} else {
					$k = $k + 1;						
				}
				if(trim($reg[9]) == '') {
					$reg[9] = $xtradat;
				}else{
					$xtradat = trim($reg[9]);
				}										
				if($k<=9){
					$contador = "000".$k;
				}elseif($k>9 and $k<=99){
					$contador = "00".$k;
				} else {
					$contador = "0".$k;
				}
				$dfecdoc = "20".substr($reg[0],0,2).substr($reg[0],2,2).substr($reg[0],4,2);
				
				$qx = $sqlca->query("INSERT INTO tmp_concar values ('".trim($reg[10])."', '".trim($correlativo2)."', '".trim($contador)."', '".trim($reg[0])."', '".trim($reg[1])."', '".trim($reg[2])."', '".trim($reg[11])."', 'MN', '".trim($reg[5])."', '".trim($reg[6])."', 'TK', '".trim($reg[9])."', '".trim($reg[0])."', '".trim($reg[0])."', 'S', 'S', '".$dfecdoc."', '".trim($reg[7])."', '0', '".trim($reg[6])."', 'X', '".$dfecdoc."', '".$dfecdoc."', 'M', ' ');", "qxs");
			}
		}			

		//******************************** CUENTAS POR COBRAR *****************************
		
		$sql_cc = "SELECT ccobrar_subdiario_mkt, ccobrar_cuenta_cliente_mkt, ccobrar_cuenta_caja_mkt, codane, subdiario_dia FROM concar_config;";
		if ($sqlca->query($sql_cc) < 0) 
			return false;		
		$a = $sqlca->fetchRow();
		$ccsubdiario	= $a[0];
		$cchaber 	= $a[1];
		$ccdebe 	= $a[2];		
		$codane 	= $a[3];
		$opcion         = $a[4];

		$sqlcc = "SELECT * FROM	
			
			(		
				SELECT 
					dfeccom as dfeccom,
					'".$cchaber."' as dcuenta,
					'0002' as dsecue,	
					dcodane as dcodane,
					dcencos as dcencos,
					'H' as ddh,	
					dimport as dimport,
					dnumdoc as dnumdoc,	
					'COB. '||substring(dxglosa from 7 for 22) as dxglosa,
					'$ccsubdiario'||substring(dfeccom::text from 5 for 2)::text as subday 	
				FROM 
					tmp_concar 
				WHERE 
					ddh='D' AND dvanexo='M'				
			) as A
			
			UNION
			
			(	
				SELECT 
					dfeccom as dfeccom,
					'".$ccdebe."' as dcuenta,
					'0001' as dsecue,
					'".$codane."' as dcodane,
					dcencos as dcencos,
					'D' as ddh,
					SUM(dimport) as dimport,
					'000000' as dnumdoc,
					'COB. '||substring(dxglosa from 7 for 22) as dxglosa,
					'$ccsubdiario'||substring(dfeccom::text from 5 for 2)::text as subday 
				FROM 
					tmp_concar 
				WHERE 
					ddh='D' AND dvanexo='M'
				GROUP BY 
					dfeccom,dcencos,dxglosa 	
			)
			ORDER BY dfeccom, dcuenta, dcodane desc;	";
			
		//echo "+++ ".$sqlcc." +++\n\n";			
			
		if ($sqlca->query($sqlcc) < 0) 
			return false;	
	
		$md = 0;
		$pc = 0;	
		$cons = 1;	
		$counter = ($FechaDiv[1] * 10000) + $num_actual;
		
		while ($reg = $sqlca->fetchRow()) {	
			// validando si subdiario se toma completo ($opcion=0) o con el día ($opcion=1)
		/*	if($opcion==0) {
				$reg[9]=substr($reg[9],0,-2);
			}*/
		
			if($reg[1] == $ccdebe)	
				$counter = $counter + 1;
			if($FechaDiv[1]>=1 && $FechaDiv[1]<10){
				$secue = "0".$counter;
			} else {
				$secue = $counter;
			}
			
			if($reg[5]=="D"){
				$ins1 = "INSERT INTO $TabSqlCab
					(
						CSUBDIA, CCOMPRO, CFECCOM, CCODMON, CSITUA, CTIPCAM, CGLOSA, CTOTAL,CTIPO, CFLAG, CFECCAM, CORIG, CFORM, CTIPCOM, CEXTOR
					) VALUES (
						'".$reg[9]."', '".$secue."', '".$reg[0]."', 'MN', '', 0, '".$reg[8]."', '".$reg[6]."', 'V', 'S', '', '','','',''
					);";
				$arrInserts['tipo']['cabecera'][$pc] = $ins1;
				$pc++;	
			}
			//$reg[6]:dimport
			
			if($reg[3]==$codane) {
				$cons = 1;
			} else {
				$cons++;
			}
			if($cons>0 and $cons<=9) {
				$secdet = "000".$cons;				
			} elseif ($cons>=10 and $cons<=99){
				$secdet = "00".$cons;
			} elseif ($cons>=100 and $cons<=999){
				$secdet = "0".$cons;
			} else {
				$secdet = $cons;
			}
			
			$ins2 = "INSERT INTO $TabSqlDet 
				(	dsubdia, dcompro, dsecue, dfeccom, dcuenta, dcodane, dcencos, dcodmon, ddh, dimport, dtipdoc, dnumdoc, dfecdoc, dfecven, 
					darea, dflag, dxglosa, dusimpor, dmnimpor, dcodarc, dcodane2, dtipcam, dmoncom, dcolcom, dbascom, dinacom, digvcom, dtpconv, 
					dflgcom, danecom, dtipaco, dmedpag, dtidref, dndoref, dfecref, dbimref, digvref, dtipdor, dnumdor, dfecdo2
				) VALUES ( 
					'".$reg[9]."', '".$secue."', '".$secdet."', '".$reg[0]."', '".$reg[1]."', '".$reg[3]."', '', 'MN', 
					'".$reg[5]."', ".$reg[6].", 'TK', '".$reg[7]."', '".$reg[0]."', '".$reg[0]."', '','S', '".$reg[8]."', 
					0,0,'','',0,'','',0,0,0,'','','','','','','', GETDATE(), 0,0,'','', GETDATE()
				);";
			$arrInserts['tipo']['detalle'][$md] = $ins2;
			$md++;												
		}
				
		$q5 = $sqlca->query("DROP TABLE tmp_concar");	
		
		$rstado = ejecutarInsert($arrInserts, $TabSqlCab, $TabSqlDet, $TabCan);	
		
		return $rstado;		
	}

	function interface_compras($FechaIni, $FechaFin, $almacen, $codEmpresa, $num_actual) { // COMPRAS
		global $sqlca;

		//Obtenemos parametros para version concar
		InterfaceConcarActModel::getVersionConcar();
		$es_desglose = false;
		if ($_SESSION['es_requerimiento_concar_etissa'] == true) {
			$es_desglose = true;
		}

		if(trim($num_actual)=="")
			$num_actual = 0;

		$FechaIni_Original = $FechaIni;
		$FechaFin_Original = $FechaFin;
		$FechaDiv = explode("/", $FechaIni);
		$FechaIni = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];
		$anio1 	  = $FechaDiv[2];
		$mes1 	  = $FechaDiv[1];
		$dia1 	  = $FechaDiv[0];				
		$postrans = "pos_trans".$FechaDiv[2].$FechaDiv[1];
		$FechaDiv = explode("/", $FechaFin);
		$FechaFin = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];
		$anio2 	  = $FechaDiv[2];
		$mes2 	  = $FechaDiv[1];
		$dia2 	  = $FechaDiv[0];				
		$Anio 	  = substr($FechaDiv[2],2,3);
		
		if ($anio1 != $anio2 || $mes1 != $mes2) {
			return 3; // Fechas deben ser mismo mes y año
		} else {
			if ($dia1 > $dia2) 
				return 4; // Fecha inicio debe ser menor que fecha final					
		}

		$Anio 		= trim($Anio);
		$codEmpresa = trim($codEmpresa);

		$TabSqlCab = "CC".$codEmpresa.$Anio; // Tabla SQL Cabecera
		$TabSqlDet = "CD".$codEmpresa.$Anio; // Tabla SQL Detalle
		$TabCan    = "CAN".$codEmpresa;

		// $sql = "
		// 	SELECT 
		// 		compra_subdiario,
		// 		id_cencos_comb, 
		// 		subdiario_dia,
		// 		id_centro_costo_glp,
		// 		cod_cliente,
		// 		cod_caja    
		// 	FROM 
		// 		concar_config;
		// ";

		// if ($sqlca->query($sql) < 0) 
		// 	return false;	
	
		// $a = $sqlca->fetchRow();
		// $vcsubdiario = $a[0];
		// $vccencos    = $a[1];
		// $opcion      = $a[2];
		// $cencosglp   = $a[3];
		// $cod_cliente = $a[4];
		// $cod_caja    = $a[5];

		//CUENTAS PARA LOS ASIENTOS
		// $compra_combustible_cuenta_proveedor = "421201";
		// $compra_combustible_cuenta_bi        = "201101";

		// $compra_glp_cuenta_proveedor         = "421202";
		// $compra_glp_cuenta_bi                = "201102";

		// $compra_market_cuenta_proveedor      = "421203";
		// $compra_cuenta_impuesto              = "401101";
		// $compra_market_cuenta_bi             = "201103";
		//CUENTAS PARA LOS ASIENTOS

		$sql = "
			SELECT 
				c1.account AS compra_subdiario
				,c2.account AS id_cencos_comb
				,c3.account AS subdiario_dia
				,c4.account AS id_centro_costo_glp
				,c5.account AS cod_cliente
				,c6.account AS cod_caja 

				,c7.account AS compra_combustible_cuenta_proveedor 
				,c8.account AS compra_combustible_cuenta_bi 

				,c9.account AS compra_glp_cuenta_proveedor 
				,c10.account AS compra_glp_cuenta_bi

				,c11.account AS compra_market_cuenta_proveedor 		
				,c12.account AS compra_cuenta_impuesto 		
				,c13.account AS compra_market_cuenta_bi 
				,c14.account AS compra_cuenta_inafecto 
				,c15.account AS compra_cuenta_percepcion 

				,c16.account AS compra_subdiario_glp
				,c17.account AS compra_subdiario_market
			FROM 
				concar_confignew c1
				LEFT JOIN concar_confignew c2 ON   c2.module = 0   AND c2.category = 2   AND c2.subcategory = 0   --Centro de Costo Combustible
				LEFT JOIN concar_confignew c3 ON   c3.module = 0   AND c3.category = 1   AND c3.subcategory = 0   --Subdiario dia
				LEFT JOIN concar_confignew c4 ON   c4.module = 0   AND c4.category = 2   AND c4.subcategory = 1   --Centro de Costo GLP
				LEFT JOIN concar_confignew c5 ON   c5.module = 0   AND c5.category = 1   AND c5.subcategory = 1   --Codigo de Cliente
				LEFT JOIN concar_confignew c6 ON   c6.module = 0   AND c6.category = 1   AND c6.subcategory = 2   --Codigo de Caja
			
				LEFT JOIN concar_confignew c7 ON   c7.module = 5   AND c7.category = 1   AND c7.subcategory = 0   --Cuenta Compra Proveedor Combustible
				LEFT JOIN concar_confignew c8 ON   c8.module = 5   AND c8.category = 1   AND c8.subcategory = 1   --Cuenta Compra BI Combustible
				
				LEFT JOIN concar_confignew c9 ON   c9.module = 5   AND c9.category = 2   AND c9.subcategory = 0       --Cuenta Compra Proveedor GLP
				LEFT JOIN concar_confignew c10 ON   c10.module = 5   AND c10.category = 2   AND c10.subcategory = 1   --Cuenta Compra BI GLP

				LEFT JOIN concar_confignew c11 ON   c11.module = 5   AND c11.category = 3   AND c11.subcategory = 0   --Cuenta Compra Proveedor Market
				LEFT JOIN concar_confignew c12 ON   c12.module = 5   AND c12.category = 3   AND c12.subcategory = 1   --Cuenta Compra Impuesto
				LEFT JOIN concar_confignew c13 ON   c13.module = 5   AND c13.category = 3   AND c13.subcategory = 2   --Cuenta Compra BI Market
				LEFT JOIN concar_confignew c14 ON   c14.module = 5   AND c14.category = 3   AND c14.subcategory = 3   --Cuenta Compra Inafecto
				LEFT JOIN concar_confignew c15 ON   c15.module = 5   AND c15.category = 3   AND c15.subcategory = 4   --Cuenta Compra Percepcion

				LEFT JOIN concar_confignew c16 ON   c16.module = 5   AND c16.category = 0   AND c16.subcategory = 1   --Subdiario de Compra GLP
				LEFT JOIN concar_confignew c17 ON   c17.module = 5   AND c17.category = 0   AND c17.subcategory = 2   --Subdiario de Compra Market
			WHERE
				c1.module = 5   AND c1.category = 0   AND c1.subcategory = 0;   --Subdiario de Compra Combustible
		";

		if ($sqlca->query($sql) < 0) 
			return false;	
	
		$a = $sqlca->fetchRow();
		$vcsubdiario = $a[0];
		$vccencos    = $a[1];
		$opcion      = $a[2];
		$cencosglp   = $a[3];
		$cod_cliente = $a[4];
		$cod_caja    = $a[5];


		$compra_combustible_cuenta_proveedor = $a[6];
		$compra_combustible_cuenta_bi        = $a[7];

		$compra_glp_cuenta_proveedor         = $a[8];
		$compra_glp_cuenta_bi                = $a[9];

		$compra_market_cuenta_proveedor      = $a[10];
		$compra_cuenta_impuesto              = $a[11];
		$compra_market_cuenta_bi             = $a[12];
		$compra_cuenta_inafecto              = $a[13];
		$compra_cuenta_percepcion            = $a[14];

		$vcsubdiario_glp                     = $a[15];
		$vcsubdiario_market                  = $a[16];

		//FUNCIONALIDAD PARA RECORRER UNO A UNO LOS REGISTROS DE COMPRAS Y GENERAR ASIENTOS, DE MODO QUE REEMPLAZAREMOS QUERY CON UNIONS
		$sql = "
			SELECT
				to_char(date(c.pro_cab_fechaemision),'YYMMDD') as dia,
				''::text as DCUENTA,
				c.pro_codigo::text as pro,
				c.pro_cab_numdocumento::text as trans,
				'1'::text as tip,
				'H'::text as ddh,	
				
				-- round(FIRST(CASE WHEN pro_cab_impinafecto IS NULL THEN c.pro_cab_imptotal ELSE c.pro_cab_imptotal + pro_cab_impinafecto END), 2) as importe_total,	
				-- round(FIRST(c.pro_cab_impto1), 2) as importe_igv,	
				-- round(FIRST(CASE WHEN pro_cab_impinafecto IS NULL THEN c.pro_cab_impafecto ELSE c.pro_cab_impafecto + pro_cab_impinafecto END), 2) as importe_bi,	
			
				round(FIRST(COALESCE(c.pro_cab_imptotal,0)), 2) as importe_total,	
				round(FIRST(COALESCE(c.pro_cab_impto1,0)), 2) as importe_impuesto,	
				round(FIRST(COALESCE(c.pro_cab_impafecto,0)), 2) as importe_bi,	
				round(FIRST(COALESCE(c.pro_cab_impinafecto,0)), 2) as importe_inafecto,	
				round(FIRST(COALESCE(c.regc_sunat_percepcion,0)), 2) as importe_percepcion,	
			
				'COMPRA '|| rubro.ch_descripcion_breve::text as venta,
				c.pro_cab_almacen as sucursal,
				c.pro_cab_seriedocumento|| '-' ||c.pro_cab_numdocumento::text as dnumdoc,
				'08'::TEXT AS subdiario,
				''::text as DCENCOS,
				'C'::text as tip2,
				c.pro_cab_tipdocumento::TEXT AS nutd,
				CASE										
					WHEN TRIM(FIRST(MOVI.art_codigo)) NOT IN (SELECT TRIM(ch_codigocombustible) FROM comb_ta_combustibles) THEN 'MARKET'
					WHEN TRIM(FIRST(MOVI.art_codigo)) IN (SELECT TRIM(ch_codigocombustible) FROM comb_ta_combustibles WHERE ch_codigocombustible = '11620307') THEN 'GLP'
					WHEN TRIM(FIRST(MOVI.art_codigo)) IN (SELECT TRIM(ch_codigocombustible) FROM comb_ta_combustibles WHERE ch_codigocombustible != '11620307') THEN 'COMBUSTIBLE'					
					ELSE CASE
								WHEN TRIM(rubro.ch_descripcion_breve)::text = 'COMBUSTIBLE' THEN 'COMBUSTIBLE'
								ELSE 'MARKET'
							END
				END AS tipo_documento,
				FIRST(c.pro_cab_tipdocumento) as pro_cab_tipdocumento,
				FIRST(c.pro_cab_seriedocumento) as pro_cab_seriedocumento,
				FIRST(c.pro_cab_numdocumento) as pro_cab_numdocumento
			FROM
				cpag_ta_cabecera c
				INNER JOIN cpag_ta_detalle d ON (c.pro_cab_tipdocumento = d.pro_cab_tipdocumento AND c.pro_cab_seriedocumento = d.pro_cab_seriedocumento AND c.pro_cab_numdocumento = d.pro_cab_numdocumento AND c.pro_codigo = d.pro_codigo)
				LEFT JOIN cpag_ta_rubros rubro ON(rubro.ch_codigo_rubro = c.pro_cab_rubrodoc)
				LEFT JOIN inv_movialma AS MOVI ON (c.pro_cab_tipdocumento = MOVI.mov_tipdocuref AND c.pro_cab_seriedocumento || '' || c.pro_cab_numdocumento = MOVI.mov_docurefe)
			WHERE
				date(c.pro_cab_fechaemision) BETWEEN '$FechaIni' AND '$FechaFin'
				AND c.pro_cab_almacen = '$almacen'				
				AND (	TRIM(MOVI.art_codigo) IN (SELECT TRIM(ch_codigocombustible) FROM comb_ta_combustibles WHERE ch_codigocombustible != '11620307')
						OR TRIM(MOVI.art_codigo) IN (SELECT TRIM(ch_codigocombustible) FROM comb_ta_combustibles WHERE ch_codigocombustible = '11620307')				
						OR TRIM(MOVI.art_codigo) NOT IN (SELECT TRIM(ch_codigocombustible) FROM comb_ta_combustibles) )
				--AND ( MOVI.art_codigo IS NOT NULL OR TRIM(rubro.ch_descripcion_breve)::text = 'SERVICIOS VARIOS' )
			GROUP BY
				dia,
				pro,
				subdiario,
				c.pro_cab_almacen,
				trans,
				c.pro_cab_seriedocumento,
				rubro.ch_descripcion_breve,
				c.pro_cab_tipdocumento
			ORDER BY
				dia, trans, pro, ddh DESC;
		";

		echo "<pre>";		
		echo "COMPRAS: \n\n".$sql."\n\n";	
		echo "</pre>";

		//RECORREMOS UNO A UNO LAS COMPRAS PARA GENERAR LOS ASIENTOS
		$data_asientos = array();
		if ($sqlca->query($sql)>0) {
			while ($reg = $sqlca->fetchRow()) {
								
				//DETERMINAMOS DE QUE TIPO ES LA COMPRA (COMBUSTIBLE, GLP O MARKET)
				$es_tipo = $reg['tipo_documento'];
				$cuenta_total      = "";
				$cuenta_impuesto   = "";
				$cuenta_bi         = "";
				$cuenta_inafecto   = "";
				$cuenta_percepcion = "";
				$subdiario         = "";
				if ( TRIM($es_tipo) == "COMBUSTIBLE" ) {
					$cuenta_total      = $compra_combustible_cuenta_proveedor;
					$cuenta_impuesto   = $compra_cuenta_impuesto;
					$cuenta_bi         = $compra_combustible_cuenta_bi;
					$cuenta_inafecto   = $compra_cuenta_inafecto;
					$cuenta_percepcion = $compra_cuenta_percepcion;
					$subdiario         = $vcsubdiario;
				} else if ( TRIM($es_tipo) == "GLP" ) {
					$cuenta_total      = $compra_glp_cuenta_proveedor;
					$cuenta_impuesto   = $compra_cuenta_impuesto;
					$cuenta_bi         = $compra_glp_cuenta_bi;
					$cuenta_inafecto   = $compra_cuenta_inafecto;
					$cuenta_percepcion = $compra_cuenta_percepcion;
					$subdiario         = $vcsubdiario_glp;
				} else if ( TRIM($es_tipo) == "MARKET" ) {
					$cuenta_total      = $compra_market_cuenta_proveedor;
					$cuenta_impuesto   = $compra_cuenta_impuesto;
					$cuenta_bi         = $compra_market_cuenta_bi;
					$cuenta_inafecto   = $compra_cuenta_inafecto;
					$cuenta_percepcion = $compra_cuenta_percepcion;
					$subdiario         = $vcsubdiario_market;
				}

				//CREAMOS LOS ASIENTOS POR CADA REGISTRO DE COMPRA
				//TOTAL
				$data_asientos[] = array(
					0 => $reg['dia'],
					1 => $cuenta_total,
					2 => $reg['pro'],
					3 => $reg['trans'],
					4 => $reg['tip'],
					5 => 'H', //ddh
					6 => round($reg['importe_impuesto'] + $reg['importe_bi'] + $reg['importe_inafecto'] + $reg['importe_percepcion'],2), //importe
					7 => $reg['venta'],
					8 => $reg['sucursal'],
					9 => $reg['dnumdoc'],
					10 => $subdiario,
					11 => $reg['dcencos'],
					12 => $reg['tip2'],
					13 => $reg['nutd'],
					//DATA PARA DESGLOSE
					14 => $reg['pro_cab_tipdocumento'], //TIPO DOCUMENTO
					15 => $reg['pro_cab_seriedocumento'], //SERIE
					16 => $reg['pro_cab_numdocumento'], //NUMERO
					17 => '-'
				);

				//IMPUESTO
				$data_asientos[] = array(
					0 => $reg['dia'],
					1 => $cuenta_impuesto,
					2 => $reg['pro'],
					3 => $reg['trans'],
					4 => $reg['tip'],
					5 => 'D',
					6 => $reg['importe_impuesto'],
					7 => $reg['venta'],
					8 => $reg['sucursal'],
					9 => $reg['dnumdoc'],
					10 => $subdiario,
					11 => $reg['dcencos'],
					12 => $reg['tip2'],
					13 => $reg['nutd'],
					//DATA PARA DESGLOSE
					14 => $reg['pro_cab_tipdocumento'], //TIPO DOCUMENTO
					15 => $reg['pro_cab_seriedocumento'], //SERIE
					16 => $reg['pro_cab_numdocumento'], //NUMERO
					17 => '-'
				);

				//INAFECTO SOLO SI EXISTE
				if ( $reg['importe_inafecto'] > 0 ) {					
					$data_asientos[] = array(
						0 => $reg['dia'],
						1 => $cuenta_inafecto,
						2 => $reg['pro'],
						3 => $reg['trans'],
						4 => $reg['tip'],
						5 => 'D',
						6 => $reg['importe_inafecto'],
						7 => $reg['venta'],
						8 => $reg['sucursal'],
						9 => $reg['dnumdoc'],
						10 => $subdiario,
						11 => $reg['dcencos'],
						12 => $reg['tip2'],
						13 => $reg['nutd'],	
						//DATA PARA DESGLOSE
						14 => $reg['pro_cab_tipdocumento'], //TIPO DOCUMENTO
						15 => $reg['pro_cab_seriedocumento'], //SERIE
						16 => $reg['pro_cab_numdocumento'], //NUMERO
						17 => '-'
					);
				}

				//PERCEPCION SOLO SI EXISTE
				if ( $reg['importe_percepcion'] > 0 ) {
					$data_asientos[] = array(
						0 => $reg['dia'],
						1 => $cuenta_percepcion,
						2 => $reg['pro'],
						3 => $reg['trans'],
						4 => $reg['tip'],
						5 => 'D',
						6 => $reg['importe_percepcion'],
						7 => $reg['venta'],
						8 => $reg['sucursal'],
						9 => $reg['dnumdoc'],
						10 => $subdiario,
						11 => $reg['dcencos'],
						12 => $reg['tip2'],
						13 => $reg['nutd'],	
						//DATA PARA DESGLOSE
						14 => $reg['pro_cab_tipdocumento'], //TIPO DOCUMENTO
						15 => $reg['pro_cab_seriedocumento'], //SERIE
						16 => $reg['pro_cab_numdocumento'], //NUMERO
						17 => '-'
					);
				}

				//BI
				$data_asientos[] = array(
					0 => $reg['dia'],
					1 => $cuenta_bi,
					2 => $reg['pro'],
					3 => $reg['trans'],
					4 => $reg['tip'],
					5 => 'D',
					6 => $reg['importe_bi'],
					7 => $reg['venta'],
					8 => $reg['sucursal'],
					9 => $reg['dnumdoc'],
					10 => $subdiario,
					11 => $reg['dcencos'],
					12 => $reg['tip2'],
					13 => $reg['nutd'],
					//DATA PARA DESGLOSE
					14 => $reg['pro_cab_tipdocumento'], //TIPO DOCUMENTO
					15 => $reg['pro_cab_seriedocumento'], //SERIE
					16 => $reg['pro_cab_numdocumento'], //NUMERO
					17 => ( TRIM($es_tipo) == "COMBUSTIBLE" || TRIM($es_tipo) == "GLP" ) && $es_desglose == true ? 'ES_DESGLOSE' : '-' //SI ES COMBUSTIBLE LA BASE IMPONIBLE SE DESGLOSA
				);
			}
		}
		echo "<pre>";
		print_r($data_asientos);
		echo "</pre>";

		//RECORREMOS LOS ASIENTOS DE COMPRAS
		$data_asientos_ = array();
		foreach ($data_asientos as $key => $value) {
			//INGRESAMOS LA INFORMACION EN EL NUEVO ARRAY
			if( $value['17'] == '-' ) {
				$data_asientos_[] = array(
					0 => $value[0],
					1 => $value[1],
					2 => $value[2],
					3 => $value[3],
					4 => $value[4],
					5 => $value[5],
					6 => $value[6],
					7 => $value[7],
					8 => $value[8],
					9 => $value[9],
					10 => $value[10],
					11 => $value[11],
					12 => $value[12],
					13 => $value[13],
				);
			}
			
			if ( $value[17] == 'ES_DESGLOSE' ) { //SI ES COMBUSTIBLE LA BASE IMPONIBLE SE DESGLOSA
				$sql_desglose = "
					SELECT 
						SUM(MOVI.mov_costototal) as mov_costototal,
						FIRST(q.codigo_concar) as codigo_concar
					FROM
						inv_movialma as MOVI 
						LEFT JOIN interface_equivalencia_producto q ON (MOVI.art_codigo = q.art_codigo)
					WHERE
						'".TRIM($value[14])."' = MOVI.mov_tipdocuref AND '".TRIM($value[15])."' || '' || '".TRIM($value[16])."' = MOVI.mov_docurefe
					GROUP BY 
						MOVI.art_codigo;
				";
				echo "<pre>";		
				echo "COMPRAS COMBUSTIBLE - DESGLOSE BI: \n\n".$sql_desglose."\n\n";
				echo "</pre>";

				if ($sqlca->query($sql_desglose)>0) {
					while ($regdes = $sqlca->fetchRow()) {
						$data_asientos_[] = array(
							0 => $value[0],
							1 => $regdes['codigo_concar'],
							2 => $value[2],
							3 => $value[3],
							4 => $value[4],
							5 => $value[5],
							6 => $regdes['mov_costototal'],
							7 => $value[7],
							8 => $value[8],
							9 => $value[9],
							10 => $value[10],
							11 => $value[11],
							12 => $value[12],
							13 => $value[13],
						);
					}
				} else {
					$data_asientos_[] = array(
						0 => $value[0],
						1 => $value[1],
						2 => $value[2],
						3 => $value[3],
						4 => $value[4],
						5 => $value[5],
						6 => $value[6],
						7 => $value[7],
						8 => $value[8],
						9 => $value[9],
						10 => $value[10],
						11 => $value[11],
						12 => $value[12],
						13 => $value[13],
					);
				}
			}
		}
		$data_asientos = $data_asientos_;

		echo "<pre>";
		print_r($data_asientos);
		echo "</pre>";
		//CERRAR FUNCIONALIDAD PARA RECORRER UNO A UNO LOS REGISTROS DE COMPRAS Y GENERAR ASIENTOS, DE MODO QUE REEMPLAZAREMOS QUERY CON UNIONS

		/* QUERY METODO VIEJO CON UNIONS
		$sql = "
			SELECT * FROM(
				--COMPRAS DE COMBUSTIBLE
				SELECT
					to_char(date(c.pro_cab_fechaemision),'YYMMDD') as dia,
					'$compra_combustible_cuenta_proveedor'::text as DCUENTA,
					c.pro_codigo::text as pro,
					c.pro_cab_numdocumento::text as trans,
					'1'::text as tip,
					'H'::text as ddh,
					round(FIRST(CASE WHEN pro_cab_impinafecto IS NULL THEN c.pro_cab_imptotal ELSE c.pro_cab_imptotal + pro_cab_impinafecto END), 2) as importe,
					'COMPRA '|| rubro.ch_descripcion_breve::text as venta,
					c.pro_cab_almacen as sucursal,
					c.pro_cab_seriedocumento|| '-' ||c.pro_cab_numdocumento::text as dnumdoc,
					'$vcsubdiario'::TEXT AS subdiario,
					--'$vcsubdiario'||substring(c.pro_cab_fechaemision::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'C'::text as tip2,
					c.pro_cab_tipdocumento::TEXT AS nutd
				FROM
					cpag_ta_cabecera c
					INNER JOIN cpag_ta_detalle d ON (c.pro_cab_tipdocumento = d.pro_cab_tipdocumento AND c.pro_cab_seriedocumento = d.pro_cab_seriedocumento AND c.pro_cab_numdocumento = d.pro_cab_numdocumento AND c.pro_codigo = d.pro_codigo)
					LEFT JOIN cpag_ta_rubros rubro ON(rubro.ch_codigo_rubro = c.pro_cab_rubrodoc)
					LEFT JOIN inv_movialma AS MOVI ON (c.pro_cab_tipdocumento = MOVI.mov_tipdocuref AND c.pro_cab_seriedocumento || '' || c.pro_cab_numdocumento = MOVI.mov_docurefe)
				WHERE
					date(c.pro_cab_fechaemision) BETWEEN '$FechaIni' AND '$FechaFin'
					AND c.pro_cab_almacen = '$almacen'
					AND TRIM(MOVI.art_codigo) IN (SELECT TRIM(ch_codigocombustible) FROM comb_ta_combustibles WHERE ch_codigocombustible != '11620307')
				GROUP BY
					dia,
					pro,
					subdiario,
					c.pro_cab_almacen,
					trans,
					c.pro_cab_seriedocumento,
					rubro.ch_descripcion_breve,
					c.pro_cab_tipdocumento
				) AS t

				UNION 

				(
				SELECT
					to_char(date(c.pro_cab_fechaemision),'YYMMDD') as dia,
					'$compra_cuenta_impuesto'::text as DCUENTA,	
					c.pro_codigo::text as pro,
					c.pro_cab_numdocumento::text as trans,
					'1'::text as tip,
					'D'::text as ddh,
					round(FIRST(c.pro_cab_impto1), 2) as importe,
					'COMPRA '|| rubro.ch_descripcion_breve::text as venta,
					c.pro_cab_almacen as sucursal,
					c.pro_cab_seriedocumento|| '-' ||c.pro_cab_numdocumento::text as dnumdoc,
					'$vcsubdiario'::TEXT AS subdiario,
					--'$vcsubdiario'||substring(c.pro_cab_fechaemision::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'C'::text as tip2,
					c.pro_cab_tipdocumento::TEXT AS nutd
				FROM
					cpag_ta_cabecera c
					INNER JOIN cpag_ta_detalle d ON (c.pro_cab_tipdocumento = d.pro_cab_tipdocumento AND c.pro_cab_seriedocumento = d.pro_cab_seriedocumento AND c.pro_cab_numdocumento = d.pro_cab_numdocumento AND c.pro_codigo = d.pro_codigo)
					LEFT JOIN cpag_ta_rubros rubro ON(rubro.ch_codigo_rubro = c.pro_cab_rubrodoc)
					LEFT JOIN inv_movialma AS MOVI ON (c.pro_cab_tipdocumento = MOVI.mov_tipdocuref AND c.pro_cab_seriedocumento || '' || c.pro_cab_numdocumento = MOVI.mov_docurefe)
				WHERE
					date(c.pro_cab_fechaemision) BETWEEN '$FechaIni' AND '$FechaFin'
					AND c.pro_cab_almacen = '$almacen'
					AND TRIM(MOVI.art_codigo) IN (SELECT TRIM(ch_codigocombustible) FROM comb_ta_combustibles WHERE ch_codigocombustible != '11620307')
				GROUP BY
					dia,
					pro,
					subdiario,
					c.pro_cab_almacen,
					trans,
					c.pro_cab_seriedocumento,
					rubro.ch_descripcion_breve,
					c.pro_cab_tipdocumento
				)

				UNION 

				(
				SELECT
					to_char(date(c.pro_cab_fechaemision),'YYMMDD') as dia,
					'$compra_combustible_cuenta_bi'::text as DCUENTA,	
					c.pro_codigo::text as pro,
					c.pro_cab_numdocumento::text as trans,
					'1'::text as tip,
					'D'::text as ddh,
					round(FIRST(CASE WHEN pro_cab_impinafecto IS NULL THEN c.pro_cab_impafecto ELSE c.pro_cab_impafecto + pro_cab_impinafecto END), 2) as importe,
					'COMPRA '|| rubro.ch_descripcion_breve::text as venta,
					c.pro_cab_almacen as sucursal,
					c.pro_cab_seriedocumento|| '-' ||c.pro_cab_numdocumento::text as dnumdoc,
					'$vcsubdiario'::TEXT AS subdiario,
					--'$vcsubdiario'||substring(c.pro_cab_fechaemision::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'C'::text as tip2,
					c.pro_cab_tipdocumento::TEXT AS nutd
				FROM
					cpag_ta_cabecera c
					INNER JOIN cpag_ta_detalle d ON (c.pro_cab_tipdocumento = d.pro_cab_tipdocumento AND c.pro_cab_seriedocumento = d.pro_cab_seriedocumento AND c.pro_cab_numdocumento = d.pro_cab_numdocumento AND c.pro_codigo = d.pro_codigo)
					LEFT JOIN cpag_ta_rubros rubro ON(rubro.ch_codigo_rubro = c.pro_cab_rubrodoc)
					LEFT JOIN inv_movialma AS MOVI ON (c.pro_cab_tipdocumento = MOVI.mov_tipdocuref AND c.pro_cab_seriedocumento || '' || c.pro_cab_numdocumento = MOVI.mov_docurefe)
				WHERE
					date(c.pro_cab_fechaemision) BETWEEN '$FechaIni' AND '$FechaFin'
					AND c.pro_cab_almacen = '$almacen'
					AND TRIM(MOVI.art_codigo) IN (SELECT TRIM(ch_codigocombustible) FROM comb_ta_combustibles WHERE ch_codigocombustible != '11620307')
				GROUP BY
					dia,
					pro,
					subdiario,
					c.pro_cab_almacen,
					trans,
					c.pro_cab_seriedocumento,
					rubro.ch_descripcion_breve,
					c.pro_cab_tipdocumento
				)

				UNION 

				(
				--COMPRA DE GLP
				SELECT
					to_char(date(c.pro_cab_fechaemision),'YYMMDD') as dia,
					'$compra_glp_cuenta_proveedor'::text as DCUENTA,
					c.pro_codigo::text as pro,
					c.pro_cab_numdocumento::text as trans,
					'1'::text as tip,
					'H'::text as ddh,
					round(FIRST(CASE WHEN pro_cab_impinafecto IS NULL THEN c.pro_cab_imptotal ELSE c.pro_cab_imptotal + pro_cab_impinafecto END), 2) as importe,
					'COMPRA '|| rubro.ch_descripcion_breve::text as venta,
					c.pro_cab_almacen as sucursal,
					c.pro_cab_seriedocumento|| '-' ||c.pro_cab_numdocumento::text as dnumdoc,
					'$vcsubdiario_glp'::TEXT AS subdiario,
					--'$vcsubdiario_glp'||substring(c.pro_cab_fechaemision::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'C'::text as tip2,
					c.pro_cab_tipdocumento::TEXT AS nutd
				FROM
					cpag_ta_cabecera c
					INNER JOIN cpag_ta_detalle d ON (c.pro_cab_tipdocumento = d.pro_cab_tipdocumento AND c.pro_cab_seriedocumento = d.pro_cab_seriedocumento AND c.pro_cab_numdocumento = d.pro_cab_numdocumento AND c.pro_codigo = d.pro_codigo)
					LEFT JOIN cpag_ta_rubros rubro ON(rubro.ch_codigo_rubro = c.pro_cab_rubrodoc)
					LEFT JOIN inv_movialma AS MOVI ON (c.pro_cab_tipdocumento = MOVI.mov_tipdocuref AND c.pro_cab_seriedocumento || '' || c.pro_cab_numdocumento = MOVI.mov_docurefe)
				WHERE
					date(c.pro_cab_fechaemision) BETWEEN '$FechaIni' AND '$FechaFin'
					AND c.pro_cab_almacen = '$almacen'
					AND TRIM(MOVI.art_codigo) IN (SELECT TRIM(ch_codigocombustible) FROM comb_ta_combustibles WHERE ch_codigocombustible = '11620307')
				GROUP BY
					dia,
					pro,
					subdiario,
					c.pro_cab_almacen,
					trans,
					c.pro_cab_seriedocumento,
					rubro.ch_descripcion_breve,
					c.pro_cab_tipdocumento
				)

				UNION 

				(
				SELECT
					to_char(date(c.pro_cab_fechaemision),'YYMMDD') as dia,
					'$compra_cuenta_impuesto'::text as DCUENTA,	
					c.pro_codigo::text as pro,
					c.pro_cab_numdocumento::text as trans,
					'1'::text as tip,
					'D'::text as ddh,
					round(FIRST(c.pro_cab_impto1), 2) as importe,
					'COMPRA '|| rubro.ch_descripcion_breve::text as venta,
					c.pro_cab_almacen as sucursal,
					c.pro_cab_seriedocumento|| '-' ||c.pro_cab_numdocumento::text as dnumdoc,
					'$vcsubdiario_glp'::TEXT AS subdiario,
					--'$vcsubdiario_glp'||substring(c.pro_cab_fechaemision::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'C'::text as tip2,
					c.pro_cab_tipdocumento::TEXT AS nutd
				FROM
					cpag_ta_cabecera c
					INNER JOIN cpag_ta_detalle d ON (c.pro_cab_tipdocumento = d.pro_cab_tipdocumento AND c.pro_cab_seriedocumento = d.pro_cab_seriedocumento AND c.pro_cab_numdocumento = d.pro_cab_numdocumento AND c.pro_codigo = d.pro_codigo)
					LEFT JOIN cpag_ta_rubros rubro ON(rubro.ch_codigo_rubro = c.pro_cab_rubrodoc)
					LEFT JOIN inv_movialma AS MOVI ON (c.pro_cab_tipdocumento = MOVI.mov_tipdocuref AND c.pro_cab_seriedocumento || '' || c.pro_cab_numdocumento = MOVI.mov_docurefe)
				WHERE
					date(c.pro_cab_fechaemision) BETWEEN '$FechaIni' AND '$FechaFin'
					AND c.pro_cab_almacen = '$almacen'
					AND TRIM(MOVI.art_codigo) IN (SELECT TRIM(ch_codigocombustible) FROM comb_ta_combustibles WHERE ch_codigocombustible = '11620307')
				GROUP BY
					dia,
					pro,
					subdiario,
					c.pro_cab_almacen,
					trans,
					c.pro_cab_seriedocumento,
					rubro.ch_descripcion_breve,
					c.pro_cab_tipdocumento
				)

				UNION 

				(
				SELECT
					to_char(date(c.pro_cab_fechaemision),'YYMMDD') as dia,
					'$compra_glp_cuenta_bi'::text as DCUENTA,	
					c.pro_codigo::text as pro,
					c.pro_cab_numdocumento::text as trans,
					'1'::text as tip,
					'D'::text as ddh,
					round(FIRST(CASE WHEN pro_cab_impinafecto IS NULL THEN c.pro_cab_impafecto ELSE c.pro_cab_impafecto + pro_cab_impinafecto END), 2) as importe,
					'COMPRA '|| rubro.ch_descripcion_breve::text as venta,
					c.pro_cab_almacen as sucursal,
					c.pro_cab_seriedocumento|| '-' ||c.pro_cab_numdocumento::text as dnumdoc,
					'$vcsubdiario_glp'::TEXT AS subdiario,
					--'$vcsubdiario_glp'||substring(c.pro_cab_fechaemision::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'C'::text as tip2,
					c.pro_cab_tipdocumento::TEXT AS nutd
				FROM
					cpag_ta_cabecera c
					INNER JOIN cpag_ta_detalle d ON (c.pro_cab_tipdocumento = d.pro_cab_tipdocumento AND c.pro_cab_seriedocumento = d.pro_cab_seriedocumento AND c.pro_cab_numdocumento = d.pro_cab_numdocumento AND c.pro_codigo = d.pro_codigo)
					LEFT JOIN cpag_ta_rubros rubro ON(rubro.ch_codigo_rubro = c.pro_cab_rubrodoc)
					LEFT JOIN inv_movialma AS MOVI ON (c.pro_cab_tipdocumento = MOVI.mov_tipdocuref AND c.pro_cab_seriedocumento || '' || c.pro_cab_numdocumento = MOVI.mov_docurefe)
				WHERE
					date(c.pro_cab_fechaemision) BETWEEN '$FechaIni' AND '$FechaFin'
					AND c.pro_cab_almacen = '$almacen'
					AND TRIM(MOVI.art_codigo) IN (SELECT TRIM(ch_codigocombustible) FROM comb_ta_combustibles WHERE ch_codigocombustible = '11620307')
				GROUP BY
					dia,
					pro,
					subdiario,
					c.pro_cab_almacen,
					trans,
					c.pro_cab_seriedocumento,
					rubro.ch_descripcion_breve,
					c.pro_cab_tipdocumento
				)

				UNION 

				(
				--COMPRA DE MARKET
				SELECT
					to_char(date(c.pro_cab_fechaemision),'YYMMDD') as dia,
					'$compra_market_cuenta_proveedor'::text as DCUENTA,
					c.pro_codigo::text as pro,
					c.pro_cab_numdocumento::text as trans,
					'1'::text as tip,
					'H'::text as ddh,
					round(FIRST(CASE WHEN pro_cab_impinafecto IS NULL THEN c.pro_cab_imptotal ELSE c.pro_cab_imptotal + pro_cab_impinafecto END), 2) as importe,
					'COMPRA '|| rubro.ch_descripcion_breve::text as venta,
					c.pro_cab_almacen as sucursal,
					c.pro_cab_seriedocumento|| '-' ||c.pro_cab_numdocumento::text as dnumdoc,
					'$vcsubdiario_market'::TEXT AS subdiario,
					--'$vcsubdiario_market'||substring(c.pro_cab_fechaemision::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'C'::text as tip2,
					c.pro_cab_tipdocumento::TEXT AS nutd
				FROM
					cpag_ta_cabecera c
					INNER JOIN cpag_ta_detalle d ON (c.pro_cab_tipdocumento = d.pro_cab_tipdocumento AND c.pro_cab_seriedocumento = d.pro_cab_seriedocumento AND c.pro_cab_numdocumento = d.pro_cab_numdocumento AND c.pro_codigo = d.pro_codigo)
					LEFT JOIN cpag_ta_rubros rubro ON(rubro.ch_codigo_rubro = c.pro_cab_rubrodoc)
					LEFT JOIN inv_movialma AS MOVI ON (c.pro_cab_tipdocumento = MOVI.mov_tipdocuref AND c.pro_cab_seriedocumento || '' || c.pro_cab_numdocumento = MOVI.mov_docurefe)
				WHERE
					date(c.pro_cab_fechaemision) BETWEEN '$FechaIni' AND '$FechaFin'
					AND c.pro_cab_almacen = '$almacen'
					AND TRIM(MOVI.art_codigo) NOT IN (SELECT TRIM(ch_codigocombustible) FROM comb_ta_combustibles)
				GROUP BY
					dia,
					pro,
					subdiario,
					c.pro_cab_almacen,
					trans,
					c.pro_cab_seriedocumento,
					rubro.ch_descripcion_breve,
					c.pro_cab_tipdocumento
				)

				UNION 

				(
				SELECT
					to_char(date(c.pro_cab_fechaemision),'YYMMDD') as dia,
					'$compra_cuenta_impuesto'::text as DCUENTA,	
					c.pro_codigo::text as pro,
					c.pro_cab_numdocumento::text as trans,
					'1'::text as tip,
					'D'::text as ddh,
					round(FIRST(c.pro_cab_impto1), 2) as importe,
					'COMPRA '|| rubro.ch_descripcion_breve::text as venta,
					c.pro_cab_almacen as sucursal,
					c.pro_cab_seriedocumento|| '-' ||c.pro_cab_numdocumento::text as dnumdoc,
					'$vcsubdiario_market'::TEXT AS subdiario,
					--'$vcsubdiario_market'||substring(c.pro_cab_fechaemision::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'C'::text as tip2,
					c.pro_cab_tipdocumento::TEXT AS nutd
				FROM
					cpag_ta_cabecera c
					INNER JOIN cpag_ta_detalle d ON (c.pro_cab_tipdocumento = d.pro_cab_tipdocumento AND c.pro_cab_seriedocumento = d.pro_cab_seriedocumento AND c.pro_cab_numdocumento = d.pro_cab_numdocumento AND c.pro_codigo = d.pro_codigo)
					LEFT JOIN cpag_ta_rubros rubro ON(rubro.ch_codigo_rubro = c.pro_cab_rubrodoc)
					LEFT JOIN inv_movialma AS MOVI ON (c.pro_cab_tipdocumento = MOVI.mov_tipdocuref AND c.pro_cab_seriedocumento || '' || c.pro_cab_numdocumento = MOVI.mov_docurefe)
				WHERE
					date(c.pro_cab_fechaemision) BETWEEN '$FechaIni' AND '$FechaFin'
					AND c.pro_cab_almacen = '$almacen'
					AND TRIM(MOVI.art_codigo) NOT IN (SELECT TRIM(ch_codigocombustible) FROM comb_ta_combustibles)
				GROUP BY
					dia,
					pro,
					subdiario,
					c.pro_cab_almacen,
					trans,
					c.pro_cab_seriedocumento,
					rubro.ch_descripcion_breve,
					c.pro_cab_tipdocumento
				)

				UNION 

				(
				SELECT
					to_char(date(c.pro_cab_fechaemision),'YYMMDD') as dia,
					'$compra_market_cuenta_bi'::text as DCUENTA,	
					c.pro_codigo::text as pro,
					c.pro_cab_numdocumento::text as trans,
					'1'::text as tip,
					'D'::text as ddh,
					round(FIRST(CASE WHEN pro_cab_impinafecto IS NULL THEN c.pro_cab_impafecto ELSE c.pro_cab_impafecto + pro_cab_impinafecto END), 2) as importe,
					'COMPRA '|| rubro.ch_descripcion_breve::text as venta,
					c.pro_cab_almacen as sucursal,
					c.pro_cab_seriedocumento|| '-' ||c.pro_cab_numdocumento::text as dnumdoc,
					'$vcsubdiario_market'::TEXT AS subdiario,
					--'$vcsubdiario_market'||substring(c.pro_cab_fechaemision::text from 9 for 2)::text as subdiario,
					''::text as DCENCOS,
					'C'::text as tip2,
					c.pro_cab_tipdocumento::TEXT AS nutd
				FROM
					cpag_ta_cabecera c
					INNER JOIN cpag_ta_detalle d ON (c.pro_cab_tipdocumento = d.pro_cab_tipdocumento AND c.pro_cab_seriedocumento = d.pro_cab_seriedocumento AND c.pro_cab_numdocumento = d.pro_cab_numdocumento AND c.pro_codigo = d.pro_codigo)
					LEFT JOIN cpag_ta_rubros rubro ON(rubro.ch_codigo_rubro = c.pro_cab_rubrodoc)
					LEFT JOIN inv_movialma AS MOVI ON (c.pro_cab_tipdocumento = MOVI.mov_tipdocuref AND c.pro_cab_seriedocumento || '' || c.pro_cab_numdocumento = MOVI.mov_docurefe)
				WHERE
					date(c.pro_cab_fechaemision) BETWEEN '$FechaIni' AND '$FechaFin'
					AND c.pro_cab_almacen = '$almacen'
					AND TRIM(MOVI.art_codigo) NOT IN (SELECT TRIM(ch_codigocombustible) FROM comb_ta_combustibles)
				GROUP BY
					dia,
					pro,
					subdiario,
					c.pro_cab_almacen,
					trans,
					c.pro_cab_seriedocumento,
					rubro.ch_descripcion_breve,
					c.pro_cab_tipdocumento
				)
			ORDER BY dia, trans, pro, ddh DESC;
		";
			
		echo "<pre>";		
		echo "COMPRAS: \n\n".$sql."\n\n";	
		echo "</pre>";
		return;
		*/
		// return;
		
		$q1 = "CREATE TABLE tmp_concar (dsubdia character varying(7), dcompro character varying(6), dsecue character varying(7), dfeccom character varying(6), dcuenta character varying(12), dcodane character varying(18),
			dcencos character varying(6), dcodmon character varying(2), ddh character varying(1), dimport numeric(14,2), dtipdoc character varying(2), dnumdoc character varying(20), 
			dfecdoc character varying(8), dfecven character varying(8), darea character varying(3), dflag character varying(1), ddate date not null, dxglosa character varying(40),
			dusimport numeric(14,2), dmnimpor numeric(14,2), dcodarc character varying(2), dfeccom2 character varying(8), dfecdoc2 character varying(8), dvanexo character varying(1), dcodane2 character varying(18));";

		$sqlca->query($q1);

		$correlativo 	= 0;
		$contador 		= '0000';  
		$k 				= 0;
		$subdia 		= null;
      		
		if ( count($data_asientos) > 0 ){
			$correlativo = ($FechaDiv[1] * 10000) + $num_actual;			
			foreach ($data_asientos as $key => $reg) {
	
				//reiniciar el numerador cuando sea un dia diferente
				/*if($subdia != $reg[10]){ ANTES 12/10/2016
					$correlativo 	= 0;
					$correlativo 	= ($FechaDiv[1] * 10000) + $num_actual;
					$subdia 		= $reg[10];
				}*/

				/*if($subdia != $reg[10]){
					$correlativo 	= $correlativo + 1;
					$subdia 		= $reg[10];
				}*/

				// validando si subdiario se toma completo ($opcion=0) o con el día ($opcion=1)
				if($opcion==0) {
					$reg[10]=substr($reg[10],0,-2);
				}
				
				//if (substr($reg[1],0,3) == substr($vccliente,0,3)) { 
				if (substr($reg[1],0,3) == substr($compra_combustible_cuenta_proveedor,0,3) || substr($reg[1],0,3) == substr($compra_glp_cuenta_proveedor,0,3) || substr($reg[1],0,3) == substr($compra_market_cuenta_proveedor,0,3)) { 
					$correlativo 	= $correlativo + 1;
					$k=1;
					if($FechaDiv[1]>=1 && $FechaDiv[1]<10){
						$correlativo2 = "0".$correlativo;
					}else {
						$correlativo2 = $correlativo;
					}
				} else {
					$k = $k+1;						
				}
				if(trim($reg[9]) == ''){
					$reg[9] = $xtradat;
				} else {
					$xtradat = trim($reg[9]);
				}			
				if($k<=9){
					$contador = "000".$k;
				}elseif($k>9 and $k<=99){
					$contador = "00".$k;
				} else {
					$contador = "0".$k;
				}

				if($reg[13] == '10')
					$tipodoc = 'FT';
				elseif($reg[13] == '11')
					$tipodoc = 'ND';
				elseif($reg[13] == '20')
					$tipodoc = 'NA';
				elseif($reg[13] == '35')
					$tipodoc = 'BV';

				$dfecdoc = "20".substr($reg[0],0,2).substr($reg[0],2,2).substr($reg[0],4,2);				
			
				$q2 = $sqlca->query("INSERT INTO tmp_concar VALUES ('".trim($reg[10])."', '".trim($correlativo2)."', '".trim($contador)."', '".trim($reg[0])."', '".trim($reg[1])."', '".trim($reg[2])."', '".trim($reg[11])."', 'MN', '".trim($reg[5])."', '".trim($reg[6])."', '".$tipodoc."', '".trim($reg[9])."', '".trim($reg[0])."', '".trim($reg[0])."', 'S', 'S', '".$dfecdoc."', '".trim($reg[7])."', '0', '".trim($reg[6])."', 'X', '".$dfecdoc."', '".$dfecdoc."', 'P', ' ');", "concar_insert");
			}
		}

		// creando el vector de diferencia
		$c = 0;
		$imp = 0;
		$flag = 0;
		$que = "SELECT * FROM tmp_concar ORDER BY dsubdia, dcompro, dsecue;  ";
		if ($sqlca->query($que)>0){
			while ($reg = $sqlca->fetchRow()){
				//if (substr($reg[4],0,3) == substr($vccliente,0,3)){
				if (substr($reg[4],0,3) == substr($compra_combustible_cuenta_proveedor,0,3) || substr($reg[4],0,3) == substr($compra_glp_cuenta_proveedor,0,3) || substr($reg[4],0,3) == substr($compra_market_cuenta_proveedor,0,3)){
					if ($flag == 1) {
						$vec[$c] = $imp;
						$c = $c + 1;
					}
					$imp = trim($reg[9]);
				} else {
					$imp = round(($imp-$reg[9]), 2);
					$flag = 1;
				}
			}
			$vec[$c] = 0;
		}

		// actualizar tabla tmp_concar sumando las diferencias al igv13818
		$k = 0;
		if ($sqlca->query($que)>0){
			while ($reg = $sqlca->fetchRow()){
				//if (trim($reg[4] == $vcimpuesto)){
				if (trim($reg[4] == $compra_cuenta_impuesto)){
					$dif = $reg[9] + $vec[$k];
					$k = $k + 1;
					$sale = $sqlca->query("UPDATE tmp_concar SET dimport = ".$dif." WHERE dcompro = '".trim($reg[1])."' AND dcuenta='$compra_cuenta_impuesto' and trim(dcodane)='' AND dsubdia = '".trim($reg[0])."';", "queryaux1"); // antes: dcodane='99999999999', con ultimo cambio : dcodane=''
				}
			}
		}

		// pasando la nueva tabla a texto2
		$qfinal = "SELECT * FROM tmp_concar ORDER BY dsubdia,dcompro,dsecue; ";					
		$arrInserts = Array();
		$pd = 0; 

		if ($sqlca->query($qfinal)>0) {
			while ($reg = $sqlca->fetchRow()){
				$ins = "INSERT INTO $TabSqlDet (dsubdia, dcompro, dsecue, dfeccom, dcuenta, dcodane, dcencos, dcodmon, ddh, dimport, dtipdoc, dnumdoc, dfecdoc,dfecven, darea, dflag, dxglosa, dusimpor, dmnimpor, dcodarc, dcodane2, dtipcam, dmoncom, dcolcom, dbascom, dinacom, digvcom, dtpconv, dflgcom, danecom, dtipaco, dmedpag, dtidref, dndoref, dfecref, dbimref, digvref, dtipdor, dnumdor, dfecdo2 ) VALUES ( '".$reg[0]."', '".$reg[1]."', '".$reg[2]."', '".$reg[3]."', '".$reg[4]."', '".$reg[5]."', '".$reg[6]."', '".$reg[7]."', '".$reg[8]."', ".$reg[9].", '".$reg[10]."', '".$reg[11]."', '".$reg[13]."', '".$reg[13]."', '', '".$reg[15]."', '".$reg[17]."', 0,0,'','',0,'','',0,0,0,'','','','','','','', GETDATE(), 0,0,'','', GETDATE());";
				$arrInserts['tipo']['detalle'][$pd] = $ins;
				$pd++;						
			}
		}

		//+++++++++++++++++++++ Cabecera de Compras +++++++++++++++++++++
		
		$correlativo 	= 0;		
		$pc 			= 0;
		$cons 			= 0;
			
		if ( count($data_asientos) > 0 ){
			$correlativo = ($FechaDiv[1] * 10000) + $num_actual;
			foreach ($data_asientos as $key => $reg) {

				//reiniciar el numerador cuando sea un dia diferente
				/*if($subdia != $reg[10]){ ANTES 12/10/2016
					$correlativo 	= 0;
					$correlativo 	= ($FechaDiv[1] * 10000) + $num_actual;
					$subdia 		= $reg[10];
				}*/

				/*if($subdia != $reg[10]){
					$correlativo 	= $correlativo + 1;
					$subdia 		= $reg[10];
				}*/

				if($opcion==0) {
					$reg[10]=substr($reg[10],0,-2);
				}

				//if (substr($reg[1],0,3) == substr($vccliente,0,3)) {
				if (substr($reg[1],0,3) == substr($compra_combustible_cuenta_proveedor,0,3) || substr($reg[1],0,3) == substr($compra_glp_cuenta_proveedor,0,3) || substr($reg[1],0,3) == substr($compra_market_cuenta_proveedor,0,3)) {

					$correlativo = $correlativo + 1;

					if($FechaDiv[1]>=01 && $FechaDiv[1]<10){
						$correlativo2 = "0".$correlativo;
					}else {
						$correlativo2 = $correlativo;
					}

					$ins = "INSERT INTO $TabSqlCab
						(	CSUBDIA, CCOMPRO, CFECCOM, CCODMON, CSITUA, CTIPCAM, CGLOSA, CTOTAL, CTIPO, CFLAG, CFECCAM, CORIG, CFORM, CTIPCOM, CEXTOR
						) VALUES (
	       						'".$reg[10]."', '".$correlativo2."', '".$reg[0]."', 'MN', '', 0, '".$reg[7]."', '".$reg[6]."', 'V', 'S', '', '','','',''
	       					);";
					$arrInserts['tipo']['cabecera'][$pc] = $ins;
					$pc++;	
				}													
			}
		}			

		$q5 = $sqlca->query("DROP TABLE tmp_concar");
				
		$rstado = ejecutarInsert($arrInserts, $TabSqlCab, $TabSqlDet);

		return $rstado;		
	}

}

?>

