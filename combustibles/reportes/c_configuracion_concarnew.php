<?php

class ConfigurarConcarNewController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action']) ? $this->action = $_REQUEST['action']:$this->action='';
	}

	function Run() {
		include 'reportes/m_configuracion_concarnew.php';
		include 'reportes/t_configuracion_concarnew.php';
		$this->Init();
		$result = '';
		$result_f = '';
		$buscar = false;


		switch($this->action) {

			case "Actualizar":
				echo "<script>console.log('" . json_encode($_REQUEST) . "')</script>";

				$var = ConfigurarConcarModel::Actualizar(
					//I. Ventas Combustibles y GLP
					$_REQUEST['venta_subdiario'],		
					$_REQUEST['venta_cuenta_cliente'],	
					$_REQUEST['venta_cuenta_cliente_glp'],	
					$_REQUEST['venta_cuenta_impuesto_account_111'], 	
					$_REQUEST['venta_cuenta_ventas'],	
					$_REQUEST['venta_cuenta_ventas_glp'],

					//II. Ventas de Tiendas y Productos
					$_REQUEST['venta_subdiario_market'],	
					$_REQUEST['venta_cuenta_cliente_mkt'],
					$_REQUEST['venta_cuenta_impuesto_account_211'],
					$_REQUEST['venta_cuenta_ventas_mkt'],	

					//III. Cuentas por Cobrar Combustibles y GLP
					$_REQUEST['ccobrar_subdiario'],
					$_REQUEST['ccobrar_cuenta_cliente'],
					$_REQUEST['ccobrar_cuenta_caja'],
					$_REQUEST['ccobrar_cuenta_cliente_new'],
					$_REQUEST['ccobrar_cuenta_caja_new'],

					//IV. Cuentas por Cobrar Market
					$_REQUEST['ccobrar_subdiario_mkt'],	
					$_REQUEST['ccobrar_cuenta_cliente_mkt'],	
					$_REQUEST['ccobrar_cuenta_caja_mkt'],	

					//V. Ventas Documentos Manuales
					$_REQUEST['venta_subdiario_docManual'],	
					$_REQUEST['venta_cuenta_cliente_dMa'],	
					$_REQUEST['venta_cuenta_cliente_glp2'],
					$_REQUEST['venta_cuenta_impuesto_account_611'],
					$_REQUEST['venta_cuenta_ventas_dMa'],	
					$_REQUEST['venta_cuenta_ventas_glp2'],	

					//Sucursal
					$_REQUEST['id_cencos_comb'],		
					$_REQUEST['id_centro_costo_glp'],	
					$_REQUEST['id_centrocosto'],	
					$_REQUEST['id_centro_cos_dma'],
					$_REQUEST['cod_cliente'],

					//VI. Compras Documentos Manuales
					$_REQUEST['compra_subdiario_comb'],
					$_REQUEST['compra_subdiario_glp'],
					$_REQUEST['compra_subdiario_mkt'],
					$_REQUEST['compra_cuenta_proveedor_comb'],
					$_REQUEST['compra_cuenta_proveedor_glp'],
					$_REQUEST['compra_cuenta_proveedor_mkt'],
					$_REQUEST['compra_cuenta_impuesto'],
					$_REQUEST['compra_cuenta_mercaderia_comb'],
					$_REQUEST['compra_cuenta_mercaderia_glp'],
					$_REQUEST['compra_cuenta_mercaderia_mkt']
				);

				$updTarjetasCredito = ConfigurarConcarModel::actualizarCuentasTarjetasCredito($_REQUEST);

				$var2 = ConfigurarConcarModel::Actualizar2($_REQUEST['codigo_concar_84'],$_REQUEST['codigo_concar_90'],$_REQUEST['codigo_concar_97'],$_REQUEST['codigo_concar_d2'],$_REQUEST['codigo_concar_95'],$_REQUEST['codigo_concar_glp'],$_REQUEST['codigo_concar_m']);

				$datos = ConfigurarConcarModel::Buscar();
				$datosEquivalenciaProducto = ConfigurarConcarModel::BuscarEquivalenciaProducto();
				// echo "<script>console.log('" . json_encode($datos) . "')</script>";
				// echo "<script>console.log('" . json_encode($datosEquivalenciaProducto) . "')</script>";
				$arrResponseTarjetasCredito = ConfigurarConcarModel::obtenerCuentasTarjetasCredito();
				if ( $arrResponseTarjetasCredito['sStatus'] != 'success' ) {
					?><script>alert("<?php echo $arrResponseTarjetasCredito['sMessage']; ?> ");</script><?php
				}
				$result	= ConfigurarConcarTemplate::formSearch($datos, $datosEquivalenciaProducto, $arrResponseTarjetasCredito);
				$buscar = true;
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", $result_f);
				

				break;

			default:
				$datos = ConfigurarConcarModel::Buscar();
				$datosEquivalenciaProducto = ConfigurarConcarModel::BuscarEquivalenciaProducto();
				echo "<script>console.log('" . json_encode($datos) . "')</script>";
				echo "<script>console.log('" . json_encode($datosEquivalenciaProducto) . "')</script>";
				$arrResponseTarjetasCredito = ConfigurarConcarModel::obtenerCuentasTarjetasCredito();
				if ( $arrResponseTarjetasCredito['sStatus'] != 'success' ) {
					?><script>alert("<?php echo $arrResponseTarjetasCredito['sMessage']; ?> ");</script><?php
				}
				$result	= ConfigurarConcarTemplate::formSearch($datos, $datosEquivalenciaProducto, $arrResponseTarjetasCredito);
				$buscar = true;
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", $result_f);

				break;

		}

		$this->visor->addComponent("ContentT", "content_title", ConfigurarConcarTemplate::titulo());

	}
}

