<?php

class InterfaceSAPController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action'])?$this->action = $_REQUEST['action']:$this->action="";
    }

    function Run() {
		include 'reportes/m_interface_sap.php';
		include 'reportes/t_interface_sap.php';

		$objInterfaceSAPModel = new InterfaceSAPModel();
		$objInterfaceSAPTemplate = new InterfaceSAPTemplate();

		$this->Init();

		//Obtener la fecha del ultimo del cierre dia - tabla PA = pos_aprosys
		$dUltimoCierre = $objInterfaceSAPModel->getLastDatePA();

		$result   = '';
		$result_f = '';

		$formPrincipal 		= FALSE;
		$viewListadoHTML 	= FALSE;

		switch ($this->action) {
		    case 'Migrar':
				$formPrincipal = TRUE;
				$viewListadoHTML = TRUE;
				break;

	    	default:
				$formPrincipal = TRUE;
				break;
		}

		if ($formPrincipal) {
			$nu_almacen = (isset($_POST['cbo-iAlmacen']) ? trim($_POST['cbo-iAlmacen']) : $_SESSION['almacen']);
			$fe_inicial = (isset($_POST['txt-dInicial']) ? trim($_POST['txt-dInicial']) : $dUltimoCierre);
			$fe_final 	= (isset($_POST['txt-dFinal']) ? trim($_POST['txt-dFinal']) : $dUltimoCierre);

			$arrAlmacenes 	= $objInterfaceSAPModel->getAlmacenes();
			/**
			Observaciones:
				- TaxCode para el SAP es de la siguiente manera.
				if(> 0 || <= 1.18) then 'IGV' ELSE 0 'EXO' si a futuro si vamos comtemplar INAFECTO el valor es "INA"
			*/
			//Precargar datos OpenSoft
			$ss_impuesto = $objInterfaceSAPModel->getMontoImpuesto();
			$nu_codigo_impuesto = ($ss_impuesto > 0 || $ss_impuesto <= 1.18 ? 'IGV' : 'EXO');
			$ss_factor_bonus = $objInterfaceSAPModel->getFactorBonus();

			//Verificar conexion a Hana (Cuando carga opcion la opción) - SAP
			$arrHanaConexion 	= $objInterfaceSAPModel->connectHanaSAP();
			$result 			= $objInterfaceSAPTemplate->formPrincipal($arrAlmacenes, $nu_almacen, $fe_inicial, $fe_final, $dUltimoCierre, $arrHanaConexion['estado_conexion'], $arrHanaConexion['estado_opensoft']);
		}

		if ($viewListadoHTML) {
			$fe_inicial = trim($fe_inicial);
			$fe_inicial = strip_tags($fe_inicial);
			$fe_inicial = explode('/', $fe_inicial);
			$pos_transym = 'pos_trans' . $fe_inicial[2] . $fe_inicial[1];
			$fe_inicial = $fe_inicial[2] . '-' . $fe_inicial[1] . '-' . $fe_inicial[0];

			$fe_final = trim($fe_final);
			$fe_final = strip_tags($fe_final);
			$fe_final = explode('/', $fe_final);
			$fe_final = $fe_final[2] . '-' . $fe_final[1] . '-' . $fe_final[0];

			//Verificar conexion a Hana (cuanto ejecutamos la accion Migrar) - SAP
			if($arrHanaConexion['estado_conexion']){
				//*** Start Transaction ***//
				odbc_autocommit($arrHanaConexion['parametros_conexion'], false);
				$arrSocios = $objInterfaceSAPModel->getSocios($arrHanaConexion['parametros_conexion'], $nu_almacen, $fe_inicial, $fe_final);
				$arrData['hana'][0] = $arrSocios;
				$arrEmpleados = $objInterfaceSAPModel->getEmpleados($arrHanaConexion['parametros_conexion'], $nu_almacen, $fe_inicial, $fe_final);
				$arrData['hana'][1] = $arrEmpleados;
				$arrGuiasND = $objInterfaceSAPModel->getGuiasND($arrHanaConexion['parametros_conexion'], $nu_almacen, $fe_inicial, $fe_final, $pos_transym, $nu_codigo_impuesto, $ss_impuesto, $ss_factor_bonus);
				$arrData['hana'][2] = $arrGuiasND;
				$arrFMPTV = $objInterfaceSAPModel->getFacturasPosTransManualesVentas($arrHanaConexion['parametros_conexion'], $nu_almacen, $fe_inicial, $fe_final, $nu_codigo_impuesto, $pos_transym, $ss_impuesto, $ss_factor_bonus);
				$arrData['hana'][3] = $arrFMPTV;
				$arrNCMPTV = $objInterfaceSAPModel->getNCManualesPosTransVentas($arrHanaConexion['parametros_conexion'], $nu_almacen, $fe_inicial, $fe_final, $pos_transym);
				$arrData['hana'][4] = $arrNCMPTV;
				$arrAMV = $objInterfaceSAPModel->getAnticipoManualesVentas($arrHanaConexion['parametros_conexion'], $nu_almacen, $fe_inicial, $fe_final, $ss_impuesto);
				$arrData['hana'][5] = $arrAMV;
				$arrContometros = $objInterfaceSAPModel->getContometros($arrHanaConexion['parametros_conexion'], $nu_almacen, $fe_inicial, $fe_final);
				$arrData['hana'][6] = $arrContometros;
				$arrCambiosPrecio = $objInterfaceSAPModel->getCambiosPrecioPosTrans($arrHanaConexion['parametros_conexion'], $nu_almacen, $fe_inicial, $fe_final, $pos_transym);
				$arrData['hana'][7] = $arrCambiosPrecio;
				$arrBonus = $objInterfaceSAPModel->getBonus($arrHanaConexion['parametros_conexion'], $nu_almacen, $fe_inicial, $fe_final, $ss_factor_bonus, $pos_transym);
				$arrData['hana'][8] = $arrBonus;
				$arrDepositos = $objInterfaceSAPModel->getDepositos($arrHanaConexion['parametros_conexion'], $nu_almacen, $fe_inicial, $fe_final);
				$arrData['hana'][9] = $arrDepositos;
				$arrAjustesInventario = $objInterfaceSAPModel->getAjustesInventario($arrHanaConexion['parametros_conexion'], $nu_almacen, $fe_inicial, $fe_final, $ss_impuesto, $nu_codigo_impuesto);
				$arrData['hana'][10] = $arrAjustesInventario;
				//Opensoft solo se tomarán los siguientes movimientos, según lo coordinado con Centauro - David Prada
				$arrESInventarios = $objInterfaceSAPModel->getEntradasSalidasInventario($arrHanaConexion['parametros_conexion'], $nu_almacen, $fe_inicial, $fe_final);
				$arrData['hana'][11] = $arrESInventarios;//01 = Ingreso por compras, 07 y 08 = Salida y Entrada transferencias Market, 21 = Venta Combustible, 27 y 28 = Salida y Entrada transferencias Combustible
				$arrAfericiones = $objInterfaceSAPModel->getAfericiones($arrHanaConexion['parametros_conexion'], $nu_almacen, $fe_inicial, $fe_final, $ss_impuesto, $nu_codigo_impuesto);
				$arrData['hana'][12] = $arrAfericiones;
				$arrPagos = $objInterfaceSAPModel->getPagos($arrHanaConexion['parametros_conexion'], $nu_almacen, $fe_inicial, $fe_final, $pos_transym, $ss_impuesto, $nu_codigo_impuesto);
				$arrData['hana'][13] = $arrPagos;
				//**** Commit Transaction ****//
				if(
					($arrData['hana'][0]['estado'])
					AND ($arrData['hana'][1]['estado'])
					AND ($arrData['hana'][2]['estado'])
					AND ($arrData['hana'][3]['estado'])
					AND ($arrData['hana'][4]['estado'])
					AND ($arrData['hana'][5]['estado'])
					AND ($arrData['hana'][6]['estado'])
					AND ($arrData['hana'][7]['estado'])
					AND ($arrData['hana'][8]['estado'])
					AND ($arrData['hana'][9]['estado'])
					AND ($arrData['hana'][10]['estado'])
					AND ($arrData['hana'][11]['estado'])
					AND ($arrData['hana'][12]['estado'])
					AND ($arrData['hana'][13]['estado'])
				) {
					//*** Commit Transaction ***//
					odbc_commit($arrHanaConexion['parametros_conexion']);
				} else {
					odbc_rollback($arrHanaConexion['parametros_conexion']);
				}
				odbc_close($arrHanaConexion['parametros_conexion']);
			}
			$result_f = $objInterfaceSAPTemplate->gridViewHTML($arrData);
		}
		$this->visor->addComponent("ContentT", "content_title", $objInterfaceSAPTemplate->getTitulo());
		if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);
    }
}
