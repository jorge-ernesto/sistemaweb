<?php

class CuadroVentaLiquidacionController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action'])?$this->action = $_REQUEST['action']:$this->action="";
    }
    
    function Run() {
	    ob_start();
		include 'reportes/m_cuadro_venta_liquidacion.php';
		include 'reportes/t_cuadro_venta_liquidacion.php';
		include '/sistemaweb/helper.php';

		$objModel = new CuadroVentaLiquidacionModel();
		$objTemplate = new CuadroVentaLiquidacionTemplate();
		$objHelper = new HelperClass();

		$this->Init();

		//Obtener la fecha del ultimo del cierre de playa
		$dUltimoCierre = $objModel->getUltimaFechaCierrePlaya();

		$result = "";
		$result_f = "";

		$formPrincipal = FALSE;
		$viewListadoHTML = FALSE;

		switch ($this->action) {
		    case "Buscar":
				$formPrincipal = TRUE;
				$viewListadoHTML = TRUE;				
				break;

			case "Excel": //Boton Excel		
				/***Filtro de Fecha***/
				$filtro_fecha   = explode("/",$_POST['txt-dInicial']);
				$filtro_fecha_2 = explode("/",$_POST['txt-dFinal']);
				// echo "<script>console.log('" . json_encode($_POST['txt-dInicial']) . "')</script>";
				// echo "<script>console.log('" . json_encode($_POST['txt-dFinal']) . "')</script>";
				// echo "<script>console.log('" . json_encode($filtro_fecha[1]) . "')</script>";
				// echo "<script>console.log('" . json_encode($filtro_fecha_2[1]) . "')</script>";
				// echo "<script>console.log('" . json_encode($filtro_fecha[2]) . "')</script>";
				// echo "<script>console.log('" . json_encode($filtro_fecha_2[2]) . "')</script>";						
				if($filtro_fecha[1] != $filtro_fecha_2[1]){
					echo '<script language="javascript">alert("Solo usar fechas del mismo mes");</script>';
					die();
				}
				if($filtro_fecha[2] != $filtro_fecha_2[2]){
					echo '<script language="javascript">alert("Solo usar fechas del mismo mes");</script>';
					die();
				}
				/***/
				
				$nu_almacen = (isset($_POST['cbo-iAlmacen']) ? trim($_POST['cbo-iAlmacen']) : $_SESSION['almacen']);
				$fe_inicial = (isset($_POST['txt-dInicial']) ? trim($_POST['txt-dInicial']) : $dUltimoCierre);
				$fe_final = (isset($_POST['txt-dFinal']) ? trim($_POST['txt-dFinal']) : $dUltimoCierre); //Agregado 2020-01-02				

				$fe_inicial = trim($fe_inicial);
				$fe_inicial = strip_tags($fe_inicial);
				$fe_inicial = explode("/", $fe_inicial);

				$sTablePosTransYM = 'pos_trans'.$fe_inicial[2] . $fe_inicial[1];

				$dBusqueda= $fe_inicial[0] . "-" . $fe_inicial[1] . "-" . $fe_inicial[2];
				$dBusquedaAyer = date("d-m-Y",strtotime($dBusqueda."- 1 days"));
				$dBusquedaAyer = explode("-", $dBusquedaAyer);
				$dBusquedaAyer = $dBusquedaAyer[2] . "-" . $dBusquedaAyer[1] . "-" . $dBusquedaAyer[0];					

				$fe_inicial = $fe_inicial[2] . "-" . $fe_inicial[1] . "-" . $fe_inicial[0];

				/*Agregado 2020-01-02*/
				$fe_final = trim($fe_final);
				$fe_final = strip_tags($fe_final);
				$fe_final = explode("/", $fe_final);
				$fe_final = $fe_final[2] . "-" . $fe_final[1] . "-" . $fe_final[0];

				$arrPOST = array(
					'sIdAlmacen' => $nu_almacen,
					'dBusqueda' => $fe_inicial,
					'dBusqueda2' => $fe_final,
					'sTablePosTransYM' => $sTablePosTransYM,
					'dBusquedaAyer' => $dBusquedaAyer,
				);

				//$arrResultVentaCombustibleyOtrasVentas_Excel = array('hola' => 'aaa');		
				
				//Cuadro N° 1 - Venta Combustible y Otros
				$arrResultVentaCombustibleyOtrasVentas = $objModel->getVentaCombustibleyOtrasVentas($arrPOST);
				//Cuadro N° 2 - Detalle Liquidacion
				$arrResultDetalleLiquidacionNDCredito = $objModel->getDetalleLiquidacionNDCredito($arrPOST);
				$arrResultDetalleLiquidacionTCRED = $objModel->getDetalleLiquidacionTCRED($arrPOST);
				$arrResultDetalleLiquidacionEGRESOS = $objModel->getDetalleLiquidacionEGRESOS($arrPOST); //Gastos varios
				$arrResultDetalleLiquidacionINGRESOS = $objModel->getDetalleLiquidacionINGRESOS($arrPOST);
				$arrResultDetalleDepositosValidados = $objModel->getDepositosValidados($arrPOST['sIdAlmacen'],$arrPOST['dBusqueda'],"",$arrPOST['dBusqueda2'],"","",null,"00");
				$arrResultDetalleBBVA = $objModel->getDetalleBBVA($arrPOST['sIdAlmacen'],$arrPOST['dBusqueda'],$arrPOST['dBusqueda2']);
				$arrResultDetalleBCP = $objModel->getDetalleBCP($arrPOST['sIdAlmacen'],$arrPOST['dBusqueda'],$arrPOST['dBusqueda2']);
				$arrResultDetalleScotiabank = $objModel->getDetalleScotiabank($arrPOST['sIdAlmacen'],$arrPOST['dBusqueda'],$arrPOST['dBusqueda2']);
				$arrResultDetalleInterbank = $objModel->getDetalleInterbank($arrPOST['sIdAlmacen'],$arrPOST['dBusqueda'],$arrPOST['dBusqueda2']);
				$arrResultObtieneMarket = $objModel->obtieneMarket($arrPOST['dBusqueda'],$arrPOST['dBusqueda2'],$arrPOST['sIdAlmacen']);
				//Cuadro N° 3 - Ventas de crédito
				$arrResultDetalleNDCredito = $objModel->getDetalleNotasDespachoCredito($arrPOST);
				//Cuadro N° 4 - Control de Inventario
				$arrResultControlInventario           = $objModel->getControlInventario($arrPOST);
				$arrResultControlInventario_GASOHOL84 = $objModel->getControlInventarioNuevo($arrPOST['sIdAlmacen'],"11620301",$arrPOST['dBusqueda'],$arrPOST['dBusqueda2'],"Litros","No");
				$arrResultControlInventario_GASOGOL90 = $objModel->getControlInventarioNuevo($arrPOST['sIdAlmacen'],"11620302",$arrPOST['dBusqueda'],$arrPOST['dBusqueda2'],"Litros","No");
				$arrResultControlInventario_GASOGOL97 = $objModel->getControlInventarioNuevo($arrPOST['sIdAlmacen'],"11620303",$arrPOST['dBusqueda'],$arrPOST['dBusqueda2'],"Litros","No");
				$arrResultControlInventario_DIESELB5  = $objModel->getControlInventarioNuevo($arrPOST['sIdAlmacen'],"11620304",$arrPOST['dBusqueda'],$arrPOST['dBusqueda2'],"Litros","No");
				$arrResultControlInventario_GASOHOL95 = $objModel->getControlInventarioNuevo($arrPOST['sIdAlmacen'],"11620305",$arrPOST['dBusqueda'],$arrPOST['dBusqueda2'],"Litros","No");
				$arrResultControlInventario_GLP       = $objModel->getControlInventarioNuevo($arrPOST['sIdAlmacen'],"11620307",$arrPOST['dBusqueda'],$arrPOST['dBusqueda2'],"Litros","No");
                                
				$objTemplate->reporteExcel($arrResultVentaCombustibleyOtrasVentas, $arrResultDetalleLiquidacionNDCredito, $arrResultDetalleLiquidacionTCRED, $arrResultDetalleLiquidacionEGRESOS, $arrResultDetalleLiquidacionINGRESOS, $arrResultDetalleDepositosValidados, $arrResultDetalleBBVA, $arrResultDetalleBCP, $arrResultDetalleScotiabank, $arrResultDetalleInterbank, $arrResultObtieneMarket, $arrResultDetalleNDCredito, $arrResultControlInventario,
										   $arrResultControlInventario_GASOHOL84,
										   $arrResultControlInventario_GASOGOL90,
										   $arrResultControlInventario_GASOGOL97,
										   $arrResultControlInventario_DIESELB5,
										   $arrResultControlInventario_GASOHOL95,
										   $arrResultControlInventario_GLP);

				echo "<script>console.log('" . json_encode($arrPOST) . "')</script>";				
				echo "<script>console.log('Cuadro N° 1 - Venta Combustible y Otros')</script>";
				echo "<script>console.log('" . json_encode($arrResultVentaCombustibleyOtrasVentas) . "')</script>";
				echo "<script>console.log('Cuadro N° 2 - Detalle Liquidacion')</script>";
				echo "<script>console.log('" . json_encode($arrResultDetalleLiquidacionNDCredito) . "')</script>";
				echo "<script>console.log('" . json_encode($arrResultDetalleLiquidacionTCRED) . "')</script>";
				echo "<script>console.log('" . json_encode($arrResultDetalleLiquidacionEGRESOS) . "')</script>"; //Gastos varios
				echo "<script>console.log('" . json_encode($arrResultDetalleLiquidacionINGRESOS) . "')</script>";
				echo "<script>console.log('" . json_encode($arrResultDetalleDepositosValidados) . "')</script>";
				echo "<script>console.log('" . json_encode($arrResultDetalleBBVA) . "')</script>";
				echo "<script>console.log('" . json_encode($arrResultDetalleBCP) . "')</script>";
				echo "<script>console.log('" . json_encode($arrResultDetalleScotiabank) . "')</script>";
				echo "<script>console.log('" . json_encode($arrResultDetalleInterbank) . "')</script>";
				echo "<script>console.log('Market: " . json_encode($arrResultObtieneMarket) . "')</script>";
				echo "<script>console.log('Cuadro N° 3 - Ventas de crédito')</script>";
				echo "<script>console.log('" . json_encode($arrResultDetalleNDCredito) . "')</script>";
				echo "<script>console.log('Cuadro N° 4 - Control de Inventario')</script>";
				echo "<script>console.log('Cuadro 4 Anterior: " . json_encode($arrResultControlInventario) . "')</script>";
				echo "<script>console.log('GASOHOL84: " . json_encode($arrResultControlInventario_GASOHOL84) . "')</script>";
				echo "<script>console.log('GASOHOL90: " . json_encode($arrResultControlInventario_GASOGOL90) . "')</script>";
				echo "<script>console.log('GASOHOL97: " . json_encode($arrResultControlInventario_GASOGOL97) . "')</script>";
				echo "<script>console.log('DIESELB5: " . json_encode($arrResultControlInventario_DIESELB5) . "')</script>";
				echo "<script>console.log('GASOHOL95: " . json_encode($arrResultControlInventario_GASOHOL95) . "')</script>";
				echo "<script>console.log('GLP: " . json_encode($arrResultControlInventario_GLP) . "')</script>";			
				
                break;

			case "PDF":
				/***Filtro de Fecha***/
				$filtro_fecha   = explode("/",$_POST['txt-dInicial']);
				$filtro_fecha_2 = explode("/",$_POST['txt-dFinal']);
				// echo "<script>console.log('" . json_encode($_POST['txt-dInicial']) . "')</script>";
				// echo "<script>console.log('" . json_encode($_POST['txt-dFinal']) . "')</script>";
				// echo "<script>console.log('" . json_encode($filtro_fecha[1]) . "')</script>";
				// echo "<script>console.log('" . json_encode($filtro_fecha_2[1]) . "')</script>";
				// echo "<script>console.log('" . json_encode($filtro_fecha[2]) . "')</script>";
				// echo "<script>console.log('" . json_encode($filtro_fecha_2[2]) . "')</script>";						
				if($filtro_fecha[1] != $filtro_fecha_2[1]){
					echo '<script language="javascript">alert("Solo usar fechas del mismo mes");</script>';
					die();
				}
				if($filtro_fecha[2] != $filtro_fecha_2[2]){
					echo '<script language="javascript">alert("Solo usar fechas del mismo mes");</script>';
					die();
				}
				/***/

				$nu_almacen = (isset($_POST['cbo-iAlmacen']) ? trim($_POST['cbo-iAlmacen']) : $_SESSION['almacen']);
				$fe_inicial = (isset($_POST['txt-dInicial']) ? trim($_POST['txt-dInicial']) : $dUltimoCierre);
				$fe_final = (isset($_POST['txt-dFinal']) ? trim($_POST['txt-dFinal']) : $dUltimoCierre); //Agregado 2020-01-02				

				$fe_inicial = trim($fe_inicial);
				$fe_inicial = strip_tags($fe_inicial);
				$fe_inicial = explode("/", $fe_inicial);

				$sTablePosTransYM = 'pos_trans'.$fe_inicial[2] . $fe_inicial[1];

				$dBusqueda= $fe_inicial[0] . "-" . $fe_inicial[1] . "-" . $fe_inicial[2];
				$dBusquedaAyer = date("d-m-Y",strtotime($dBusqueda."- 1 days"));
				$dBusquedaAyer = explode("-", $dBusquedaAyer);
				$dBusquedaAyer = $dBusquedaAyer[2] . "-" . $dBusquedaAyer[1] . "-" . $dBusquedaAyer[0];					

				$fe_inicial = $fe_inicial[2] . "-" . $fe_inicial[1] . "-" . $fe_inicial[0];

				/*Agregado 2020-01-02*/
				$fe_final = trim($fe_final);
				$fe_final = strip_tags($fe_final);
				$fe_final = explode("/", $fe_final);
				$fe_final = $fe_final[2] . "-" . $fe_final[1] . "-" . $fe_final[0];

				$arrPOST = array(
					'sIdAlmacen' => $nu_almacen,
					'dBusqueda' => $fe_inicial,
					'dBusqueda2' => $fe_final,
					'sTablePosTransYM' => $sTablePosTransYM,
					'dBusquedaAyer' => $dBusquedaAyer,
				);

				//$arrResultVentaCombustibleyOtrasVentas_Excel = array('hola' => 'aaa');		
				
				//Cuadro N° 1 - Venta Combustible y Otros
				$arrResultVentaCombustibleyOtrasVentas = $objModel->getVentaCombustibleyOtrasVentas($arrPOST);
				//Cuadro N° 2 - Detalle Liquidacion
				$arrResultDetalleLiquidacionNDCredito = $objModel->getDetalleLiquidacionNDCredito($arrPOST);
				$arrResultDetalleLiquidacionTCRED = $objModel->getDetalleLiquidacionTCRED($arrPOST);
				$arrResultDetalleLiquidacionEGRESOS = $objModel->getDetalleLiquidacionEGRESOS($arrPOST); //Gastos varios
				$arrResultDetalleLiquidacionINGRESOS = $objModel->getDetalleLiquidacionINGRESOS($arrPOST);
				$arrResultDetalleDepositosValidados = $objModel->getDepositosValidados($arrPOST['sIdAlmacen'],$arrPOST['dBusqueda'],"",$arrPOST['dBusqueda2'],"","",null,"00");
				$arrResultDetalleBBVA = $objModel->getDetalleBBVA($arrPOST['sIdAlmacen'],$arrPOST['dBusqueda'],$arrPOST['dBusqueda2']);
				$arrResultDetalleBCP = $objModel->getDetalleBCP($arrPOST['sIdAlmacen'],$arrPOST['dBusqueda'],$arrPOST['dBusqueda2']);
				$arrResultDetalleScotiabank = $objModel->getDetalleScotiabank($arrPOST['sIdAlmacen'],$arrPOST['dBusqueda'],$arrPOST['dBusqueda2']);
				$arrResultDetalleInterbank = $objModel->getDetalleInterbank($arrPOST['sIdAlmacen'],$arrPOST['dBusqueda'],$arrPOST['dBusqueda2']);
				$arrResultObtieneMarket = $objModel->obtieneMarket($arrPOST['dBusqueda'],$arrPOST['dBusqueda2'],$arrPOST['sIdAlmacen']);
				//Cuadro N° 3 - Ventas de crédito
				$arrResultDetalleNDCredito = $objModel->getDetalleNotasDespachoCredito($arrPOST);
				//Cuadro N° 4 - Control de Inventario
				$arrResultControlInventario           = $objModel->getControlInventario($arrPOST);
				$arrResultControlInventario_GASOHOL84 = $objModel->getControlInventarioNuevo($arrPOST['sIdAlmacen'],"11620301",$arrPOST['dBusqueda'],$arrPOST['dBusqueda2'],"Litros","No");
				$arrResultControlInventario_GASOGOL90 = $objModel->getControlInventarioNuevo($arrPOST['sIdAlmacen'],"11620302",$arrPOST['dBusqueda'],$arrPOST['dBusqueda2'],"Litros","No");
				$arrResultControlInventario_GASOGOL97 = $objModel->getControlInventarioNuevo($arrPOST['sIdAlmacen'],"11620303",$arrPOST['dBusqueda'],$arrPOST['dBusqueda2'],"Litros","No");
				$arrResultControlInventario_DIESELB5  = $objModel->getControlInventarioNuevo($arrPOST['sIdAlmacen'],"11620304",$arrPOST['dBusqueda'],$arrPOST['dBusqueda2'],"Litros","No");
				$arrResultControlInventario_GASOHOL95 = $objModel->getControlInventarioNuevo($arrPOST['sIdAlmacen'],"11620305",$arrPOST['dBusqueda'],$arrPOST['dBusqueda2'],"Litros","No");
				$arrResultControlInventario_GLP       = $objModel->getControlInventarioNuevo($arrPOST['sIdAlmacen'],"11620307",$arrPOST['dBusqueda'],$arrPOST['dBusqueda2'],"Litros","No");
                                
				$objTemplate->reportePDF($arrResultVentaCombustibleyOtrasVentas, $arrResultDetalleLiquidacionNDCredito, $arrResultDetalleLiquidacionTCRED, $arrResultDetalleLiquidacionEGRESOS, $arrResultDetalleLiquidacionINGRESOS, $arrResultDetalleDepositosValidados, $arrResultDetalleBBVA, $arrResultDetalleBCP, $arrResultDetalleScotiabank, $arrResultDetalleInterbank, $arrResultObtieneMarket, $arrResultDetalleNDCredito, $arrResultControlInventario,
										 $arrResultControlInventario_GASOHOL84,
										 $arrResultControlInventario_GASOGOL90,
										 $arrResultControlInventario_GASOGOL97,
										 $arrResultControlInventario_DIESELB5,
										 $arrResultControlInventario_GASOHOL95,
										 $arrResultControlInventario_GLP);

				//echo "<script>console.log('" . json_encode($arrPOST) . "')</script>";				
				echo "<script>console.log('Cuadro N° 1 - Venta Combustible y Otros')</script>";
				echo "<script>console.log('" . json_encode($arrResultVentaCombustibleyOtrasVentas) . "')</script>";				
				echo "<script>console.log('Cuadro N° 2 - Detalle Liquidacion')</script>";
				echo "<script>console.log('" . json_encode($arrResultDetalleLiquidacionNDCredito) . "')</script>";
				echo "<script>console.log('" . json_encode($arrResultDetalleLiquidacionTCRED) . "')</script>";
				echo "<script>console.log('" . json_encode($arrResultDetalleLiquidacionEGRESOS) . "')</script>"; //Gastos varios
				echo "<script>console.log('" . json_encode($arrResultDetalleLiquidacionINGRESOS) . "')</script>";
				echo "<script>console.log('" . json_encode($arrResultDetalleDepositosValidados) . "')</script>";
				echo "<script>console.log('" . json_encode($arrResultDetalleBBVA) . "')</script>";
				echo "<script>console.log('" . json_encode($arrResultDetalleBCP) . "')</script>";
				echo "<script>console.log('" . json_encode($arrResultDetalleScotiabank) . "')</script>";
				echo "<script>console.log('" . json_encode($arrResultDetalleInterbank) . "')</script>";
				echo "<script>console.log('Market: " . json_encode($arrResultObtieneMarket) . "')</script>";
				echo "<script>console.log('Cuadro N° 3 - Ventas de crédito')</script>";
				echo "<script>console.log('" . json_encode($arrResultDetalleNDCredito) . "')</script>";
				echo "<script>console.log('Cuadro N° 4 - Control de Inventario')</script>";
				echo "<script>console.log('Cuadro 4 Anterior: " . json_encode($arrResultControlInventario) . "')</script>";
				echo "<script>console.log('GASOHOL84: " . json_encode($arrResultControlInventario_GASOHOL84) . "')</script>";
				echo "<script>console.log('GASOHOL90: " . json_encode($arrResultControlInventario_GASOGOL90) . "')</script>";
				echo "<script>console.log('GASOHOL97: " . json_encode($arrResultControlInventario_GASOGOL97) . "')</script>";
				echo "<script>console.log('DIESELB5: " . json_encode($arrResultControlInventario_DIESELB5) . "')</script>";
				echo "<script>console.log('GASOHOL95: " . json_encode($arrResultControlInventario_GASOHOL95) . "')</script>";
				echo "<script>console.log('GLP: " . json_encode($arrResultControlInventario_GLP) . "')</script>";
				
				break;

	    	default:
				$formPrincipal = TRUE;
				break;
		}

		if ($formPrincipal) {
			$nu_almacen = (isset($_POST['cbo-iAlmacen']) ? trim($_POST['cbo-iAlmacen']) : $_SESSION['almacen']);
			$fe_inicial = (isset($_POST['txt-dInicial']) ? trim($_POST['txt-dInicial']) : $dUltimoCierre);
			$fe_final = (isset($_POST['txt-dFinal']) ? trim($_POST['txt-dFinal']) : $dUltimoCierre); //Agregado 2020-01-02

			/***Filtro de Fecha***/
			$filtro_fecha   = explode("/",$_POST['txt-dInicial']);
			$filtro_fecha_2 = explode("/",$_POST['txt-dFinal']);
			// echo "<script>console.log('" . json_encode($_POST['txt-dInicial']) . "')</script>";
			// echo "<script>console.log('" . json_encode($_POST['txt-dFinal']) . "')</script>";
			// echo "<script>console.log('" . json_encode($filtro_fecha[1]) . "')</script>";
			// echo "<script>console.log('" . json_encode($filtro_fecha_2[1]) . "')</script>";
			// echo "<script>console.log('" . json_encode($filtro_fecha[2]) . "')</script>";
			// echo "<script>console.log('" . json_encode($filtro_fecha_2[2]) . "')</script>";						
			if($filtro_fecha[1] != $filtro_fecha_2[1]){
				echo '<script language="javascript">alert("Solo usar fechas del mismo mes");</script>';
				die();
			}
			if($filtro_fecha[2] != $filtro_fecha_2[2]){
				echo '<script language="javascript">alert("Solo usar fechas del mismo mes");</script>';
				die();
			}
			/***/

			$arrAlmacenes = $objModel->getAlmacenes();
			$result = $objTemplate->formPrincipal($arrAlmacenes, $nu_almacen, $fe_inicial, $fe_final, $dUltimoCierre); //Agregado 2020-01-02					
		}

		if ($viewListadoHTML) {
			$fe_inicial = trim($fe_inicial);
			$fe_inicial = strip_tags($fe_inicial);
			$fe_inicial = explode("/", $fe_inicial);

			$sTablePosTransYM = 'pos_trans'.$fe_inicial[2] . $fe_inicial[1];

			$dBusqueda= $fe_inicial[0] . "-" . $fe_inicial[1] . "-" . $fe_inicial[2];
			$dBusquedaAyer = date("d-m-Y",strtotime($dBusqueda."- 1 days"));
			$dBusquedaAyer = explode("-", $dBusquedaAyer);
			$dBusquedaAyer = $dBusquedaAyer[2] . "-" . $dBusquedaAyer[1] . "-" . $dBusquedaAyer[0];					

			$fe_inicial = $fe_inicial[2] . "-" . $fe_inicial[1] . "-" . $fe_inicial[0];

			/*Agregado 2020-01-02*/
			$fe_final = trim($fe_final);
			$fe_final = strip_tags($fe_final);
			$fe_final = explode("/", $fe_final);
			$fe_final = $fe_final[2] . "-" . $fe_final[1] . "-" . $fe_final[0];

			$arrPOST = array(
				'sIdAlmacen' => $nu_almacen,
				'dBusqueda' => $fe_inicial,
				'dBusqueda2' => $fe_final,
				'sTablePosTransYM' => $sTablePosTransYM,
				'dBusquedaAyer' => $dBusquedaAyer,
			);

			//Obtener el último día consolidado
			$arrResponseConsolidacion = $objHelper->obtenerUltimoDiaConsolidado($arrPOST);
			// echo "<pre>";
			// print_r($arrResponseConsolidacion);
			// echo "</pre>";

			//Cuadro N° 1 - Venta Combustible y Otros
			$arrResultVentaCombustibleyOtrasVentas = $objModel->getVentaCombustibleyOtrasVentas($arrPOST);
			echo "<pre>";
			print_r($arrResultVentaCombustibleyOtrasVentas);
			echo "</pre>";

			//Cuadro N° 2 - Detalle Liquidacion
			$arrResultDetalleLiquidacionNDCredito = $objModel->getDetalleLiquidacionNDCredito($arrPOST);
			$arrResultDetalleLiquidacionTCRED = $objModel->getDetalleLiquidacionTCRED($arrPOST);
			$arrResultDetalleLiquidacionEGRESOS = $objModel->getDetalleLiquidacionEGRESOS($arrPOST); //Gastos varios
			$arrResultDetalleLiquidacionINGRESOS = $objModel->getDetalleLiquidacionINGRESOS($arrPOST);
			$arrResultDetalleDepositosValidados = $objModel->getDepositosValidados($arrPOST['sIdAlmacen'],$arrPOST['dBusqueda'],"",$arrPOST['dBusqueda2'],"","",null,"00");
			$arrResultDetalleBBVA = $objModel->getDetalleBBVA($arrPOST['sIdAlmacen'],$arrPOST['dBusqueda'],$arrPOST['dBusqueda2']);
			$arrResultDetalleBCP = $objModel->getDetalleBCP($arrPOST['sIdAlmacen'],$arrPOST['dBusqueda'],$arrPOST['dBusqueda2']);
			$arrResultDetalleScotiabank = $objModel->getDetalleScotiabank($arrPOST['sIdAlmacen'],$arrPOST['dBusqueda'],$arrPOST['dBusqueda2']);
			$arrResultDetalleInterbank = $objModel->getDetalleInterbank($arrPOST['sIdAlmacen'],$arrPOST['dBusqueda'],$arrPOST['dBusqueda2']);
			$arrResultObtieneMarket = $objModel->obtieneMarket($arrPOST['dBusqueda'],$arrPOST['dBusqueda2'],$arrPOST['sIdAlmacen']);

			//Cuadro N° 3 - Ventas de crédito
			$arrResultDetalleNDCredito = $objModel->getDetalleNotasDespachoCredito($arrPOST);

			//Cuadro N° 4 - Control de Inventario
			$arrResultControlInventario           = $objModel->getControlInventario($arrPOST);
			$arrResultControlInventario_GASOHOL84 = $objModel->getControlInventarioNuevo($arrPOST['sIdAlmacen'],"11620301",$arrPOST['dBusqueda'],$arrPOST['dBusqueda2'],"Litros","No");
			$arrResultControlInventario_GASOGOL90 = $objModel->getControlInventarioNuevo($arrPOST['sIdAlmacen'],"11620302",$arrPOST['dBusqueda'],$arrPOST['dBusqueda2'],"Litros","No");
			$arrResultControlInventario_GASOGOL97 = $objModel->getControlInventarioNuevo($arrPOST['sIdAlmacen'],"11620303",$arrPOST['dBusqueda'],$arrPOST['dBusqueda2'],"Litros","No");
			$arrResultControlInventario_DIESELB5  = $objModel->getControlInventarioNuevo($arrPOST['sIdAlmacen'],"11620304",$arrPOST['dBusqueda'],$arrPOST['dBusqueda2'],"Litros","No");
			$arrResultControlInventario_GASOHOL95 = $objModel->getControlInventarioNuevo($arrPOST['sIdAlmacen'],"11620305",$arrPOST['dBusqueda'],$arrPOST['dBusqueda2'],"Litros","No");
			$arrResultControlInventario_GLP       = $objModel->getControlInventarioNuevo($arrPOST['sIdAlmacen'],"11620307",$arrPOST['dBusqueda'],$arrPOST['dBusqueda2'],"Litros","No");

			if ( $arrResultVentaCombustibleyOtrasVentas['sStatus']=='success' ) {
				$result_f = $objTemplate->gridViewHTML(
					$arrPOST,
					$arrResponseConsolidacion,
					$arrResultVentaCombustibleyOtrasVentas,
					$arrResultDetalleLiquidacionNDCredito,
					$arrResultDetalleLiquidacionTCRED,
					$arrResultDetalleLiquidacionEGRESOS,
					$arrResultDetalleLiquidacionINGRESOS,
					$arrResultDetalleNDCredito,

					$arrResultControlInventario,					
					$arrResultControlInventario_GASOHOL84,
					$arrResultControlInventario_GASOGOL90,
					$arrResultControlInventario_GASOGOL97,
					$arrResultControlInventario_DIESELB5,
					$arrResultControlInventario_GASOHOL95,
					$arrResultControlInventario_GLP,

					$arrResultDetalleDepositosValidados,
					$arrResultDetalleBBVA,
					$arrResultDetalleBCP,
					$arrResultDetalleScotiabank,
					$arrResultDetalleInterbank,
					$arrResultObtieneMarket
				);				
				echo "<script>console.log('" . json_encode($arrResultVentaCombustibleyOtrasVentas) . "')</script>"; //Agregado 2020-01-02
			} else {
				echo "<script charset='utf8'>alert('{$arrResultVentaCombustibleyOtrasVentas["sMessage"]}');</script>\n";
			}
		}

		$this->visor->addComponent("ContentT", "content_title", $objTemplate->getTitulo());
		if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);
    }
}
