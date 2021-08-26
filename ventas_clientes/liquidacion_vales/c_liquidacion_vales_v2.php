<?php
class LiquidacionValesController extends Controller {
	function AgruparRegistoFacturaNormal($rsdata,$sucursal = "") {
		$factura_procesada = array();
		foreach ($rsdata as $value) {
			$cli_anticipo = trim($value['cli_anticipo']);
			$ch_cliente = trim($value['ch_cliente']);
			$art_codigo = trim($value['art_codigo']);
			$ch_sucursal = trim($value['ch_sucursal']);
			//$ch_sucursal
			$factura_procesada[$cli_anticipo][$ch_cliente][$sucursal][$art_codigo]['importe']+=$value['importe']; //IMPORTE
			$factura_procesada[$cli_anticipo][$ch_cliente][$sucursal][$art_codigo]['cantidad']+=round($value['nu_cantidad'],4); //CANTIDAD
			$factura_procesada[$cli_anticipo][$ch_cliente][$sucursal][$art_codigo]['des'] = $value['desproducto'];
			$factura_procesada[$cli_anticipo][$ch_cliente][$sucursal][$art_codigo]['cli_fpago_credito'] = $value['cli_fpago_credito'];
			$factura_procesada[$cli_anticipo][$ch_cliente][$sucursal][$art_codigo]['ch_fac_credito'] = 'S';
		}
		return $factura_procesada;
	}

	function AgruparRegistoFacturaXPlaca($rsdata) {
		try {
			$factura_procesada = array();
			foreach ($rsdata as $value) {
				$cli_anticipo = trim($value['cli_anticipo']);
				$ch_cliente = trim($value['ch_cliente']);
				$art_codigo = trim($value['art_codigo']);
				$ch_sucursal = trim($value['ch_sucursal']);
				$ch_placa = trim($value['ch_placa']);
				if(empty($ch_placa)) {
					throw new Exception("No se puede liquidar porque hay productos que no tienes Placas(CODPRP:$art_codigo,".$value['desproducto'].")");
				}

				$factura_procesada[$cli_anticipo][$ch_cliente][$ch_sucursal][$ch_placa][$art_codigo]['importe']+=$value['importe'];
				$factura_procesada[$cli_anticipo][$ch_cliente][$ch_sucursal][$ch_placa][$art_codigo]['cantidad']+=round($value['nu_cantidad'],4);
				$factura_procesada[$cli_anticipo][$ch_cliente][$ch_sucursal][$ch_placa][$art_codigo]['des'] = $value['desproducto'];
				$factura_procesada[$cli_anticipo][$ch_cliente][$ch_sucursal][$ch_placa][$art_codigo]['cli_fpago_credito'] = $value['cli_fpago_credito'];
				$factura_procesada[$cli_anticipo][$ch_cliente][$ch_sucursal][$ch_placa][$art_codigo]['ch_fac_credito'] = 'S';
			}
			return $factura_procesada;
		} catch (Exception $e) {
			throw $e;
		}
	}

	function AgruparRegistoFacturaXNotaDespacho($rsdata) {
		try {
			$factura_procesada = array();
			foreach ($rsdata as $value) {
				$cli_anticipo = trim($value['cli_anticipo']);
				$ch_cliente = trim($value['ch_cliente']);
				$art_codigo = trim($value['art_codigo']);
				$ch_sucursal = trim($value['ch_sucursal']);
				$ch_documento = trim($value['ch_documento']);

				$factura_procesada[$cli_anticipo][$ch_cliente][$ch_sucursal][$ch_documento][$art_codigo]['importe']+=$value['importe'];
				$factura_procesada[$cli_anticipo][$ch_cliente][$ch_sucursal][$ch_documento][$art_codigo]['cantidad']+=round($value['nu_cantidad'],4);
				$factura_procesada[$cli_anticipo][$ch_cliente][$ch_sucursal][$ch_documento][$art_codigo]['des'] = $value['desproducto'];
				$factura_procesada[$cli_anticipo][$ch_cliente][$ch_sucursal][$ch_documento][$art_codigo]['cli_fpago_credito'] = $value['cli_fpago_credito'];
				$factura_procesada[$cli_anticipo][$ch_cliente][$ch_sucursal][$ch_documento][$art_codigo]['ch_fac_credito'] = 'S';
			}
			return $factura_procesada;
		} catch (Exception $e) {
			throw $e;
		}
	}

	function AgruparRegistoFacturaXProducto($rsdata) {
		try {
			$factura_procesada = array();
			foreach ($rsdata as $value) {
				$cli_anticipo = trim($value['cli_anticipo']);
				$ch_cliente = trim($value['ch_cliente']);
				$art_codigo = trim($value['art_codigo']);
				$ch_sucursal = trim($value['ch_sucursal']);
				$ch_placa = trim($value['ch_placa']);

				$factura_procesada[$cli_anticipo][$ch_cliente][$ch_sucursal][$art_codigo]['importe']+=$value['importe'];
				$factura_procesada[$cli_anticipo][$ch_cliente][$ch_sucursal][$art_codigo]['cantidad']+=round($value['nu_cantidad'],4);
				$factura_procesada[$cli_anticipo][$ch_cliente][$ch_sucursal][$art_codigo]['des'] = $value['desproducto'];
				$factura_procesada[$cli_anticipo][$ch_cliente][$ch_sucursal][$art_codigo]['cli_fpago_credito'] = $value['cli_fpago_credito'];
				$factura_procesada[$cli_anticipo][$ch_cliente][$ch_sucursal][$art_codigo]['ch_fac_credito'] = 'S';
			}
			return $factura_procesada;
		} catch (Exception $e) {
			throw $e;
		}
	}
}
