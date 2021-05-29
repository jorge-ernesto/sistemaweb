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
