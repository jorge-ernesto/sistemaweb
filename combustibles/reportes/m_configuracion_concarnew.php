<?php
class ConfigurarConcarModel extends Model {
	
	public function obtenerCuentasTarjetasCredito(){
		global $sqlca;

		$sql = "SELECT * FROM concar_config_caja ORDER BY idtipodoc, idformapago;";
		$iStatusSQL = $sqlca->query($sql);
		$iStatusSQL = (int)$iStatusSQL;		

		if ( $iStatusSQL > 0 ){
			return array(
				'sStatus' => 'success',
				'sMessage' => 'Datos encontrados',
				'arrData' => $sqlca->fetchAll(),
			);
		} else if ( $iStatusSQL == 0 ){
			return array(
				'sStatus' => 'warning',
				'sMessage' => 'No hay registros de cuentas de tarjeta de crédito',
			);
		} else {
			return array(
				'sStatus' => 'danger',
				'sMessage' => 'Problemas al obtener cuentas de tarjeta de crédito',
				'sMessageSQL' => $sqlca->get_error(),
				'sql' => $sql,
			);
		}
	}

	public function actualizarCuentasTarjetasCredito($arrPOST){
		global $sqlca;

		$iCount = count($arrPOST['arrCuentaCaja10']);
		$sql='';
		for ($i=0; $iCount >= $i; $i++) {
			$sql = "UPDATE concar_config_caja SET cuenta10='" . $arrPOST['arrCuentaCaja10'][$i] . "', cuenta12='" . $arrPOST['arrCuentaCaja12'][$i] . "' WHERE id_config='" . $i . "';";
			if ($sqlca->query($sql) < 0)
				return false;
		}
	}

	function Buscar(){
		global $sqlca;

		$query="
			SELECT 	
				*									
			FROM 
				concar_confignew
			ORDER BY
			 	module, category, subcategory;
			";

//		echo $query;

		if ($sqlca->query($query) < 0)
			return false;

		$resultado = Array();

		/*Estructura de la data (Module, Category, Subcategory)*/
		while ($reg = $sqlca->fetchRow()) {		
			$resultado['account_'.$reg[2].$reg[3].$reg[4]] = array(
				'concar_confignew_id' => $reg[0],
				'ch_sucursal'			 => $reg[1],
				'module'		          => $reg[2],
				'category' 		       => $reg[3],
				'subcategory'			 => $reg[4],
				'account'		       => $reg[5],	
			);
		}

		return $resultado;
  	}

	function BuscarEquivalenciaProducto(){
		global $sqlca;

		$query="
			SELECT 	
				*									
			FROM 
				interface_equivalencia_producto
			ORDER BY
				art_codigo;
			";

//		echo $query;

		if ($sqlca->query($query) < 0)
			return false;		

		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$resultado[$i]['art_codigo']			 = $a[0];
			$resultado[$i]['codigo_iridium']		 = $a[1];
			$resultado[$i]['dt_fecha_actualiza'] = $a[2];
			$resultado[$i]['codigo_concar'] 		 = $a[3];		
		}

		return $resultado;
  	}

	function Actualizar(
		//I. Ventas Combustibles y GLP
		$venta_subdiario,		
		$venta_cuenta_cliente,	
		$venta_cuenta_cliente_glp,	
		$venta_cuenta_impuesto_account_111, 	
		$venta_cuenta_ventas,	
		$venta_cuenta_ventas_glp,	

		//II. Ventas de Tiendas y Productos
		$venta_subdiario_market,	
		$venta_cuenta_cliente_mkt,
		$venta_cuenta_impuesto_account_211,
		$venta_cuenta_ventas_mkt,	

		//III. Cuentas por Cobrar Combustibles y GLP
		$ccobrar_subdiario,
		$ccobrar_cuenta_cliente,
		$ccobrar_cuenta_caja,	
		$ccobrar_cuenta_cliente_new,
		$ccobrar_cuenta_caja_new,

		//IV. Cuentas por Cobrar Market
		$ccobrar_subdiario_mkt,	
		$ccobrar_cuenta_cliente_mkt,	
		$ccobrar_cuenta_caja_mkt,	

		//V. Ventas Documentos Manuales
		$venta_subdiario_docManual,	
		$venta_cuenta_cliente_dMa,	
		$venta_cuenta_cliente_glp2,
		$venta_cuenta_impuesto_account_611,
		$venta_cuenta_ventas_dMa,	
		$venta_cuenta_ventas_glp2,	

		//Sucursal
		$id_cencos_comb,		
		$id_centro_costo_glp,	
		$id_centrocosto,	
		$id_centro_cos_dma,
		$cod_cliente,

		//VI. Compras Documentos Manuales
		$compra_subdiario_comb,
		$compra_subdiario_glp,
		$compra_subdiario_mkt,
		$compra_cuenta_proveedor_comb,
		$compra_cuenta_proveedor_glp,
		$compra_cuenta_proveedor_mkt,
		$compra_cuenta_impuesto,
		$compra_cuenta_mercaderia_comb,
		$compra_cuenta_mercaderia_glp,
		$compra_cuenta_mercaderia_mkt
	){

		global $sqlca;

		//I. Ventas Combustibles y GLP
		$upd = "
					UPDATE concar_confignew SET account ='$venta_subdiario'                   WHERE module = '1' AND category = '0' AND subcategory = '0';
					UPDATE concar_confignew SET account ='$venta_cuenta_cliente'              WHERE module = '1' AND category = '1' AND subcategory = '0';
					UPDATE concar_confignew SET account ='$venta_cuenta_cliente_glp'          WHERE module = '1' AND category = '2' AND subcategory = '0';
					UPDATE concar_confignew SET account ='$venta_cuenta_impuesto_account_111' WHERE module = '1' AND category = '1' AND subcategory = '1';
					UPDATE concar_confignew SET account ='$venta_cuenta_ventas'               WHERE module = '1' AND category = '1' AND subcategory = '2';					
					UPDATE concar_confignew SET account ='$venta_cuenta_ventas_glp'           WHERE module = '1' AND category = '2' AND subcategory = '1';					
				";		
		$sqlca->query($upd);

		//II. Ventas de Tiendas y Productos
		$upd = "
					UPDATE concar_confignew SET account ='$venta_subdiario_market'            WHERE module = '2' AND category = '0' AND subcategory = '0';
					UPDATE concar_confignew SET account ='$venta_cuenta_cliente_mkt'          WHERE module = '2' AND category = '1' AND subcategory = '0';
					UPDATE concar_confignew SET account ='$venta_cuenta_impuesto_account_211' WHERE module = '2' AND category = '1' AND subcategory = '1';
					UPDATE concar_confignew SET account ='$venta_cuenta_ventas_mkt'           WHERE module = '2' AND category = '1' AND subcategory = '2';					
				";
		$sqlca->query($upd);
				
		//III. Cuentas por Cobrar Combustibles y GLP
		$upd = "
					UPDATE concar_confignew SET account ='$ccobrar_subdiario'          WHERE module = '3' AND category = '0' AND subcategory = '0';
					UPDATE concar_confignew SET account ='$ccobrar_cuenta_cliente'     WHERE module = '3' AND category = '1' AND subcategory = '0';
					UPDATE concar_confignew SET account ='$ccobrar_cuenta_caja'        WHERE module = '3' AND category = '1' AND subcategory = '1';
					UPDATE concar_confignew SET account ='$ccobrar_cuenta_cliente_new' WHERE module = '3' AND category = '2' AND subcategory = '0';					
					UPDATE concar_confignew SET account ='$ccobrar_cuenta_caja_new'    WHERE module = '3' AND category = '2' AND subcategory = '1';										
				";
		$sqlca->query($upd);

		//IV. Cuentas por Cobrar Market
		$upd = "
					UPDATE concar_confignew SET account ='$ccobrar_subdiario_mkt'      WHERE module = '4' AND category = '0' AND subcategory = '0';
					UPDATE concar_confignew SET account ='$ccobrar_cuenta_cliente_mkt' WHERE module = '4' AND category = '1' AND subcategory = '0';
					UPDATE concar_confignew SET account ='$ccobrar_cuenta_caja_mkt'    WHERE module = '4' AND category = '1' AND subcategory = '1';										
				";
		$sqlca->query($upd);

		//V. Ventas Documentos Manuales		
		$upd = "
					UPDATE concar_confignew SET account ='$venta_subdiario_docManual'         WHERE module = '6' AND category = '0' AND subcategory = '0';
					UPDATE concar_confignew SET account ='$venta_cuenta_cliente_dMa'          WHERE module = '6' AND category = '1' AND subcategory = '0';
					UPDATE concar_confignew SET account ='$venta_cuenta_cliente_glp2'         WHERE module = '6' AND category = '2' AND subcategory = '0';										
					UPDATE concar_confignew SET account ='$venta_cuenta_impuesto_account_611' WHERE module = '6' AND category = '1' AND subcategory = '1';										
					UPDATE concar_confignew SET account ='$venta_cuenta_ventas_dMa'           WHERE module = '6' AND category = '1' AND subcategory = '2';										
					UPDATE concar_confignew SET account ='$venta_cuenta_ventas_glp2'          WHERE module = '6' AND category = '2' AND subcategory = '1';										
				";
		$sqlca->query($upd);
		
		//Sucursal
		$upd = "
					UPDATE concar_confignew SET account ='$id_cencos_comb'      WHERE module = '0' AND category = '2' AND subcategory = '0';
					UPDATE concar_confignew SET account ='$id_centro_costo_glp' WHERE module = '0' AND category = '2' AND subcategory = '1';
					UPDATE concar_confignew SET account ='$id_centrocosto'      WHERE module = '0' AND category = '2' AND subcategory = '2';										
					UPDATE concar_confignew SET account ='$id_centro_cos_dma'   WHERE module = '0' AND category = '2' AND subcategory = '3';										
					UPDATE concar_confignew SET account ='$cod_cliente'         WHERE module = '0' AND category = '1' AND subcategory = '1';															
				";
		$sqlca->query($upd);

		//VI. Compras Documentos Manuales
		$upd = "
					UPDATE concar_confignew SET account ='$compra_subdiario_comb'         WHERE module = '5' AND category = '0' AND subcategory = '0';
					UPDATE concar_confignew SET account ='$compra_subdiario_glp'          WHERE module = '5' AND category = '0' AND subcategory = '1';
					UPDATE concar_confignew SET account ='$compra_subdiario_mkt'          WHERE module = '5' AND category = '0' AND subcategory = '2';										
					UPDATE concar_confignew SET account ='$compra_cuenta_proveedor_comb'  WHERE module = '5' AND category = '1' AND subcategory = '0';										
					UPDATE concar_confignew SET account ='$compra_cuenta_proveedor_glp'   WHERE module = '5' AND category = '2' AND subcategory = '0';															
					UPDATE concar_confignew SET account ='$compra_cuenta_proveedor_mkt'   WHERE module = '5' AND category = '3' AND subcategory = '0';															
					UPDATE concar_confignew SET account ='$compra_cuenta_impuesto'        WHERE module = '5' AND category = '3' AND subcategory = '1';															
					UPDATE concar_confignew SET account ='$compra_cuenta_mercaderia_comb' WHERE module = '5' AND category = '1' AND subcategory = '1';															
					UPDATE concar_confignew SET account ='$compra_cuenta_mercaderia_glp'  WHERE module = '5' AND category = '2' AND subcategory = '1';															
					UPDATE concar_confignew SET account ='$compra_cuenta_mercaderia_mkt'  WHERE module = '5' AND category = '3' AND subcategory = '2';															
				";
		$sqlca->query($upd);
		
 	}

	function Actualizar2($codigo_concar_84,$codigo_concar_90,$codigo_concar_97,$codigo_concar_d2,$codigo_concar_95,$codigo_concar_glp,$codigo_concar_m){
		global $sqlca;

		$up1 = "UPDATE interface_equivalencia_producto SET codigo_concar ='$codigo_concar_84' WHERE art_codigo = '11620301'";
		$sqlca->query($up1);
		$up2 = "UPDATE interface_equivalencia_producto SET codigo_concar ='$codigo_concar_90' WHERE art_codigo = '11620302'";
		$sqlca->query($up2);
		$up3 = "UPDATE interface_equivalencia_producto SET codigo_concar ='$codigo_concar_97' WHERE art_codigo = '11620303'";
		$sqlca->query($up3);
		$up4 = "UPDATE interface_equivalencia_producto SET codigo_concar ='$codigo_concar_d2' WHERE art_codigo = '11620304'";
		$sqlca->query($up4);
		$up5 = "UPDATE interface_equivalencia_producto SET codigo_concar ='$codigo_concar_95' WHERE art_codigo = '11620305'";
		$sqlca->query($up5);
		$up6 = "UPDATE interface_equivalencia_producto SET codigo_concar ='$codigo_concar_glp' WHERE art_codigo = '11620307'";
		$sqlca->query($up6);
		$up7 = "UPDATE interface_equivalencia_producto SET codigo_concar ='$codigo_concar_m' WHERE art_codigo = 'M'";
		$sqlca->query($up7);

	}
	
}
