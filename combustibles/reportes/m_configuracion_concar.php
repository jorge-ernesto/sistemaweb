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
				venta_subdiario, 		-- VENTA DE COMBUSTIBLE	--
				venta_cuenta_cliente, 
				venta_cuenta_cliente_glp,
				venta_cuenta_impuesto, 
				venta_cuenta_ventas, 
				venta_cuenta_ventas_glp,
				venta_subdiario_market, 	-- VENTA DE MARKET
				venta_cuenta_cliente_mkt, 
				venta_cuenta_impuesto, 
				venta_cuenta_ventas_mkt,
				ccobrar_subdiario,		-- CUENTAS X COBRAR COMBUSTIBLE -- 10
				ccobrar_cuenta_cliente,
				ccobrar_cuenta_caja,
				ccobrar_cuenta_cliente_new,
				ccobrar_cuenta_caja_new,
				ccobrar_subdiario_mkt,		-- CUENTAS X COBRAR MARKET	-- 15
				ccobrar_cuenta_cliente_mkt,
				ccobrar_cuenta_caja_mkt,
				venta_subdiario_docManual, 	-- DOCUMENTOS MANUALES DE VENTAS -- 18
				venta_cuenta_cliente_dMa,
				venta_cuenta_cliente_glp2,
				venta_cuenta_impuesto,
				venta_cuenta_ventas_dMa,
				venta_cuenta_ventas_glp2,
				id_cencos_comb,
				id_centro_costo_glp,
				id_centrocosto,
				id_centro_cos_dma,
				codigo_iridium,
				codigo_concar,
				cod_cliente,
				compra_subdiario, -- DOCUMENTOS MANUALES DE COMPRAS 31
				compra_cuenta_proveedor,
				compra_cuenta_impuesto,
				compra_cuenta_mercaderia--36
			FROM
				concar_config,
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
			$resultado[$i]['venta_subdiario']				= $a[0];
			$resultado[$i]['venta_cuenta_cliente']			= $a[1];
			$resultado[$i]['venta_cuenta_cliente_glp']		= $a[2];
			$resultado[$i]['venta_cuenta_impuesto'] 		= $a[3];
			$resultado[$i]['venta_cuenta_ventas']			= $a[4];
			$resultado[$i]['venta_cuenta_ventas_glp']		= $a[5];
			$resultado[$i]['venta_subdiario_market']		= $a[6];
			$resultado[$i]['venta_cuenta_cliente_mkt']		= $a[7];
			$resultado[$i]['venta_cuenta_impuesto']			= $a[8];
			$resultado[$i]['venta_cuenta_ventas_mkt']		= $a[9];
			$resultado[$i]['ccobrar_subdiario']				= $a[10];
			$resultado[$i]['ccobrar_cuenta_cliente']		= $a[11];
			$resultado[$i]['ccobrar_cuenta_caja']			= $a[12];
			$resultado[$i]['ccobrar_cuenta_cliente_new']	= $a[13];
			$resultado[$i]['ccobrar_cuenta_caja_new']		= $a[14];
			$resultado[$i]['ccobrar_subdiario_mkt']			= $a[15];
			$resultado[$i]['ccobrar_cuenta_cliente_mkt']	= $a[16];
			$resultado[$i]['ccobrar_cuenta_caja_mkt']		= $a[17];
			$resultado[$i]['venta_subdiario_docManual']		= $a[18];
			$resultado[$i]['venta_cuenta_cliente_dMa']		= $a[19];
			$resultado[$i]['venta_cuenta_cliente_glp2']		= $a[20];
			$resultado[$i]['venta_cuenta_impuesto']			= $a[21];
			$resultado[$i]['venta_cuenta_ventas_dMa']		= $a[22];
			$resultado[$i]['venta_cuenta_ventas_glp2']		= $a[23];
			$resultado[$i]['id_cencos_comb']				= $a[24];
			$resultado[$i]['id_centro_costo_glp']			= $a[25];
			$resultado[$i]['id_centrocosto']				= $a[26];
			$resultado[$i]['id_centro_cos_dma']				= $a[27];
			$resultado[$i]['codigo_iridium']				= $a[28];
			$resultado[$i]['codigo_concar']					= $a[29];
			$resultado[$i]['cod_cliente']					= $a[30];
			$resultado[$i]['compra_subdiario']				= $a[31];
			$resultado[$i]['compra_cuenta_proveedor']		= $a[32];
			$resultado[$i]['compra_cuenta_impuesto']		= $a[33];
			$resultado[$i]['compra_cuenta_mercaderia']		= $a[34];
		}

		return $resultado;

  	}

	function Actualizar(
		$venta_subdiario,		
		$venta_cuenta_cliente,	
		$venta_cuenta_cliente_glp,	
		$venta_cuenta_impuesto, 	
		$venta_cuenta_ventas,	
		$venta_cuenta_ventas_glp,	
		$venta_subdiario_market,	
		$venta_cuenta_cliente_mkt,
		$venta_cuenta_ventas_mkt,	
		$ccobrar_subdiario,
		$ccobrar_cuenta_cliente,
		$ccobrar_cuenta_caja,	
		$ccobrar_cuenta_cliente_new,
		$ccobrar_cuenta_caja_new,
		$ccobrar_subdiario_mkt,	
		$ccobrar_cuenta_cliente_mkt,	
		$ccobrar_cuenta_caja_mkt,	
		$venta_subdiario_docManual,	
		$venta_cuenta_cliente_dMa,	
		$venta_cuenta_cliente_glp2,
		$venta_cuenta_ventas_dMa,	
		$venta_cuenta_ventas_glp2,	
		$id_cencos_comb,		
		$id_centro_costo_glp,	
		$id_centrocosto,	
		$id_centro_cos_dma,
		$cod_cliente,
		$compra_subdiario,
		$compra_cuenta_proveedor,
		$compra_cuenta_impuesto,
		$compra_cuenta_mercaderia
	){

		global $sqlca;

		$sql = "
			UPDATE 
				concar_config
			SET
				venta_subdiario = '".$venta_subdiario."',	
				venta_cuenta_cliente = '".$venta_cuenta_cliente."',	
				venta_cuenta_cliente_glp = '".$venta_cuenta_cliente_glp."',	
				venta_cuenta_impuesto = '".$venta_cuenta_impuesto."', 	
				venta_cuenta_ventas = '".$venta_cuenta_ventas."',	
				venta_cuenta_ventas_glp = '".$venta_cuenta_ventas_glp."',	
				venta_subdiario_market = '".$venta_subdiario_market."',	
				venta_cuenta_cliente_mkt = '".$venta_cuenta_cliente_mkt."',
				venta_cuenta_ventas_mkt = '".$venta_cuenta_ventas_mkt."',	
				ccobrar_subdiario = '".$ccobrar_subdiario."',
				ccobrar_cuenta_cliente = '".$ccobrar_cuenta_cliente."',
				ccobrar_cuenta_caja = '".$ccobrar_cuenta_caja."',
				ccobrar_cuenta_cliente_new = '".$ccobrar_cuenta_cliente_new."',
				ccobrar_cuenta_caja_new = '".$ccobrar_cuenta_caja_new."',
				ccobrar_subdiario_mkt = '".$ccobrar_subdiario_mkt."',	
				ccobrar_cuenta_cliente_mkt = '".$ccobrar_cuenta_cliente_mkt."',	
				ccobrar_cuenta_caja_mkt = '".$ccobrar_cuenta_caja_mkt."',	
				venta_subdiario_docManual = '".$venta_subdiario_docManual."',	
				venta_cuenta_cliente_dMa = '".$venta_cuenta_cliente_dMa ."',	
				venta_cuenta_cliente_glp2 = '".$venta_cuenta_cliente_glp2."',	
				venta_cuenta_ventas_dMa = '".$venta_cuenta_ventas_dMa."',	
				venta_cuenta_ventas_glp2 = '".$venta_cuenta_ventas_glp2."',	
				id_cencos_comb = '".$id_cencos_comb."',		
				id_centro_costo_glp = '".$id_centro_costo_glp."',	
				id_centrocosto = '".$id_centrocosto."',	
				id_centro_cos_dma = '".$id_centro_cos_dma."',
				cod_cliente = '".$cod_cliente."',
				compra_subdiario = '".$compra_subdiario."',
				compra_cuenta_proveedor = '".$compra_cuenta_proveedor."',
				compra_cuenta_impuesto = '".$compra_cuenta_impuesto."',
				compra_cuenta_mercaderia = '".$compra_cuenta_mercaderia."';
			";

		//echo "Update Concar: " . $sql;

		$sqlca->query($sql);
		
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
