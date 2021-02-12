<?php

class InterfaceSAPController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action'])?$this->action = $_REQUEST['action']:$this->action="";
    }
    
    function Run() {
	    ob_start();
		include 'movimientos/m_interface_sap.php';
		include 'movimientos/t_interface_sap.php';
		include '/sistemaweb/helper.php';

		$objModel = new InterfaceSAPModel();
		$objTemplate = new InterfaceSAPTemplate();
		$objHelper = new HelperClass();
		$arrCurrenteSystem = $objHelper->getCurrentSystemDate();
		$dCurrenteSystem = $arrCurrenteSystem['arrData'];
		$req['tax'] = $objModel->getTax();

		$this->Init();

		$result   = "";
		$result_f = "";

		$arrLadosForm = array();

		$formPrincipal 			= false;
		$viewListadoHTML 		= false;
		$viewListadoExcel 		= false;
		$viewListadoHTMLDetail 	= false;

		switch ($this->action) {
		    case "Buscar":
				$formPrincipal = true;
				$viewListadoHTML = true;
				$viewListadoHTMLDetail = false;
				break;

			case "Excel":
				$formPrincipal = true;
				$viewListadoHTML = true;
				$viewListadoExcel = true;
				$viewListadoHTMLDetail = false;
				break;

		    case "Tablas":
				$formPrincipal = false;
				$viewListadoHTML = false;
				$viewListadoExcel = false;
				$viewListadoHTMLDetail = true;
				break;

		    case "Save":
				$arrResponseModel = $objModel->saveConfiguration($_POST);
				if ( $arrResponseModel['sStatus']=='success' ){
					echo "<script type='text/javascript' charset='utf8'>alert('{$arrResponseModel["sMessage"]}');</script>";

					$arrResult = $objModel->getDetailTableConfiguration($_POST);
					if ($arrResult['bStatus']) {
						$result_f = $objTemplate->gridViewHTMLTableConfiguration($arrResult, '', $_POST);
					} else {
						echo '<script type="text/javascript" charset="utf8">alert("' . $arrResult["sMessage"] . '");window.close();</script>';
					}
				} else {
					echo "<script type='text/javascript' charset='utf8'>alert('{$arrResponseModel["sMessage"]}');window.close();</script>";
				}
				break;

			case "Delete":
				$arrResponseModel = $objModel->deleteConfiguration($_GET);
				if ( $arrResponseModel['sStatus']=='success' ){
					echo "<script type='text/javascript' charset='utf8'>alert('{$arrResponseModel["sMessage"]}');</script>";

					$arrResult = $objModel->getDetailTableConfiguration($_GET);
					if ($arrResult['bStatus']) {
						$result_f = $objTemplate->gridViewHTMLTableConfiguration($arrResult, '', $_GET);
					} else {
						echo '<script type="text/javascript" charset="utf8">alert("' . $arrResult["sMessage"] . '");window.close();</script>';
					}
				} else {
					echo "<script type='text/javascript' charset='utf8'>alert('{$arrResponseModel["sMessage"]}');window.close();</script>";
				}
				break;

	    	default:
				$formPrincipal = true;
				break;
		}

		if ($formPrincipal) {
			$sCodigoAlmacen = (isset($_POST['cbo-iAlmacen']) ? trim($_POST['cbo-iAlmacen']) : $_SESSION['almacen']);
			$dInicial = (isset($_POST['txt-dInicial']) ? trim($_POST['txt-dInicial']) : $dCurrenteSystem);
			$sGenerarBoleta = (isset($_POST['radio-sGenerarBoleta']) ? trim($_POST['radio-sGenerarBoleta']) : NULL);
			$sGenerate = (isset($_POST['cbo-iGenerate']) ? trim($_POST['cbo-iGenerate']) : '');
			$iTableConfiguration = (isset($_POST['cbo-iTableConfiguration']) ? trim($_POST['cbo-iTableConfiguration']) : '');

			$arrGenerate = explode('-', $sGenerate);
			$iGenerate = $arrGenerate[0];//Codigo
			$sGenerate = $arrGenerate[1];//Titulo

			$sBoletaDetallada = 'checked';
			$sBoletaAgrupada = '';
			
			if(isset($_POST['radio-sGenerarBoleta'])){
				$sBoletaDetallada = ($sGenerarBoleta == 'D' ? 'checked' : '');
				$sBoletaAgrupada = ($sGenerarBoleta == 'R' ? 'checked' : '');
			}

			$arrAlmacenes = $objModel->getAlmacenes();
			$arrTableConfiguration = $objModel->getTableConfiguration();
			$result = $objTemplate->formPrincipal(
				$arrAlmacenes,
				$sCodigoAlmacen,
				$dInicial,
				$dCurrenteSystem,
				$sBoletaDetallada,
				$sBoletaAgrupada,
				$iGenerate.'-'.$sGenerate,
				$arrTableConfiguration,
				$iTableConfiguration
			);
		}

		if ($viewListadoHTML) {
			// echo "<script>console.log('Entro')</script>";

			$dInicial = trim($dInicial);
			$dInicial = strip_tags($dInicial);
			$dInicial = explode('/', $dInicial);
			$sTablePostransYM = 'pos_trans' . $dInicial[2] . $dInicial[1]; 
			$dInicial = $dInicial[2] . '-' . $dInicial[1] . '-' . $dInicial[0];

			$arrPOST = array(
				'sCodigoAlmacen' => $sCodigoAlmacen,
				'dInicial' => $dInicial,
				'sTablePostransYM' => $sTablePostransYM,
				'sGenerarBoleta' => $sGenerarBoleta,
			);
			
			// echo "<script>console.log('" . json_encode(array($sGenerate, $sGenerarBoleta)) . "')</script>";
			// echo "<script>console.log('" . json_encode($arrPOST) . "')</script>";

			if ( ($sGenerate=='Cabecera' || $sGenerate=='Detalle') && $sGenerarBoleta=='D') {
				$arrResultDR = $objModel->getHeader($arrPOST);
			} else {
				$arrResultHeader = $objModel->getHeader($arrPOST);
				$arrResultDR = $this->generateGroupSaleInvoice($arrResultHeader);
			}

			if ( $sGenerate=='Cabecera' ){
				if($arrResultDR['bStatus']) {
					$sGenerate = ($sGenerarBoleta=='D' ? $sGenerate : $sGenerate . ' Agrupados');
					$result_f 	= $objTemplate->gridViewHTMLHeader($arrResultDR, $sGenerate);
				} else {
					echo '<script type="text/javascript">alert("' . $arrResultDR['sMessage'] . '");window.close();</script>';					
				}
			}
			if ( $sGenerate=='Detalle' ){
				if ($sGenerarBoleta=='D'){
					$arrResult = $objModel->getDetail($arrPOST);
					if($arrResult['bStatus']) {
						$result_f 	= $objTemplate->gridViewHTMLDetail($arrResult, $sGenerate, $req);
					} else {
						echo '<script type="text/javascript">alert("' . $arrResult['sMessage'] . '");window.close();</script>';
					}
				}
				if ($sGenerarBoleta=='R'){
					if ($arrResultDR['bStatus']) {
						foreach ($arrResultHeader['arrData'] as $row_detail_ticktB) {
							if ($row_detail_ticktB['indicator']=='03') {//Mostrar solo las boletas detalladas y luego agregar un campo al arreglo del for anterior del grupo de boletas agrupadas al que pertenece
								foreach ($arrResultDR['arrData'] as $row) {
									if ($row['indicator']=='03') {//Mostrar solo las boletas agrupadas
										$arrSerieNumIniFin = explode('-',$row['numatcard']);
										if ( 
											($row_detail_ticktB['numatcard'] >= $arrSerieNumIniFin[0].'-'.$arrSerieNumIniFin[1]) &&
											($row_detail_ticktB['numatcard'] <= $arrSerieNumIniFin[0].'-'.$arrSerieNumIniFin[2])
										) {
											$row_detail_ticktB['number_ini_fin'] = $row['numatcard'];
										}
									}
								}
								$arrResultGroupDetailB['arrData'][] = $row_detail_ticktB;
							}
						}
						$result_f = $objTemplate->gridViewHTMLGroupDetail($arrResultGroupDetailB, $sGenerate);
					} else {
						echo '<script type="text/javascript">alert("' . $arrResultDR['sMessage'] . '");window.close();</script>';					
					}
				}
			}
			if ( $sGenerate=='Resumen' ){
				$arrResult = $objModel->getResumen($arrPOST);
				if ( $arrResult['bStatus'] ) {
					$result_f = $objTemplate->gridViewHTMLResumen($arrResult, $sGenerate);
				} else {
					echo '<script type="text/javascript">alert("' . $arrResult['sMessage'] . '");window.close();</script>';
				}
			}
		}

		if ($viewListadoExcel) {
			if ( $sGenerate=='Cabecera' || $sGenerate=='Cabecera Agrupados' ){
				if ($sGenerate=='Cabecera Agrupados')
					$sGenerate='Cabecera_Agrupados';				
				if($arrResultDR['bStatus']) {
					$result_f = $objTemplate->gridViewExcelHeader($arrResultDR, $sGenerate);
				} else {
					echo '<script type="text/javascript">alert("' . $arrResultDR['sMessage'] . '");window.close();</script>';
				}
			}
			if ( $sGenerate=='Detalle' ){
				if ($sGenerarBoleta=='D'){
					if($arrResult['bStatus']) {
						$result_f = $objTemplate->gridViewExcelDetail($arrResult, $sGenerate, $req);
					} else {
						echo '<script type="text/javascript">alert("' . $arrResult['sMessage'] . '");window.close();</script>';
					}
				}
				if ($sGenerarBoleta=='R'){
					if ($arrResultDR['bStatus']) {
						$result_f = $objTemplate->gridViewExcelGroupDetail($arrResultGroupDetailB, $sGenerate);
					} else {
						echo '<script type="text/javascript">alert("' . $arrResult['sMessage'] . '");window.close();</script>';
					}
				}
			}
			if ( $sGenerate=='Resumen' ){
				if($arrResult['bStatus']) {
					$result_f = $objTemplate->gridViewExcelResumen($arrResult, $sGenerate);
				} else {
					echo '<script type="text/javascript">alert("' . $arrResult['sMessage'] . '");window.close();</script>';
				}
			}
		}

		if ($viewListadoHTMLDetail) {
			$sTableConfiguration = trim($_POST['cbo-iTableConfiguration']);
			$sTableConfiguration = strip_tags($sTableConfiguration);

			$arrTableConfiguration = explode('-', $sTableConfiguration);
			$iCodeTableConfiguration = $arrTableConfiguration[0];
			$sTitleTableConfiguration = $arrTableConfiguration[1];

			if ( $iCodeTableConfiguration=='0' ){
				echo "<script>alert('Seleccionar tabla');</script>";
				$result_f = "";
			} else {
				$arrPOST = array(
					'iCodeTableConfiguration' => $iCodeTableConfiguration,
				);
				$arrResult = $objModel->getDetailTableConfiguration($arrPOST);

				if ($arrResult['bStatus']) {
					$result_f = $objTemplate->gridViewHTMLTableConfiguration($arrResult, $sTitleTableConfiguration, $arrPOST);
				} else {
					echo '<script>alert("' . $arrResult["sMessage"] . '");window.close();</script>';
				}
			}
		}

		$this->visor->addComponent("ContentT", "content_title", $objTemplate->getTitulo());
		if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);
    }

    private function generateGroupSaleInvoice($arrResult){
        $sIDTipoDocumento = '';
        $sIDSerieDocumento = '';
        
        $iCounter = 0;
        $iDetener = 0;
        $imprimir=0;

        $fSubtotal = 0.00;
        $fImpuesto = 0.00;
        $fTotal = 0.00;

        $arrPlayaInvoiceSale = array();

    	$rows_bv = array();

    	foreach($arrResult["arrData"] as $row) {
    		if ( $iDetener == 0 && ($row["indicator"] != "03" || ($row["indicator"] == "03" && $row["doctotal"] >= 700.00)) ){//Solo entra si no es boleta ó solo si la boleta es mayor a 700 soles
    			$rows_["series"] = $row["series"];//codigo de serie de SAP
    			$rows_["cardcode"] = $row["cardcode"];
    			$rows_["docdate"] = $row["docdate"];
    			$rows_["docduedate"] = $row["docduedate"];
    			$rows_["taxdate"] = $row["taxdate"];
    			$rows_["numatcard"] = $row["numatcard"];
    			$rows_["folioprefixstring"] = $row["folioprefixstring"];
    			$rows_["folionumber"] = $row["folionumber"];
    			$rows_["paymentgroupcode"] = $row["paymentgroupcode"];
    			$rows_["indicator"] = $row["indicator"];
    			$rows_["doctotal"] = $row["doctotal"];
    			$rows_["salespersoncode"] = $row["salespersoncode"];
    			$rows_["comments"] = $row["comments"];
    			$rows_["journalmemo"] = $row["journalmemo"];
    			$rows_["u_exc_fecrecep"] = $row["u_exc_fecrecep"];
    			$rows_["u_ctg_numliq"] = $row["u_ctg_numliq"];
    			$rows_["u_exx_serie"] = '';
    			$rows_["u_exx_nroini"] = '';
    			$rows_["u_exx_nrofin"] = '';
                if ( count($rows_) > 0 )
                    $arrPlayaInvoiceSale['arrData'][] = $rows_;
    		}

    		if ( $row["indicator"] == '03' ) {
    			if ( $sIDSerieDocumento != $row["folioprefixstring"] && $row["doctotal"] < 700.00 ) {//BV INICIAL
	    			$rows_bv["series"] = $row["series"];//codigo de serie de SAP
	    			$rows_bv["cardcode"] = $row["cardcode"];
	    			$rows_bv["docdate"] = $row["docdate"];
	    			$rows_bv["docduedate"] = $row["docduedate"];
	    			$rows_bv["taxdate"] = $row["taxdate"];
	    			$rows_bv["folioprefixstring"] = '';
	    			$rows_bv["folionumber"] = '';
	    			$rows_bv["paymentgroupcode"] = $row["paymentgroupcode"];
	    			$rows_bv["indicator"] = $row["indicator"];
	    			$rows_bv["salespersoncode"] = $row["salespersoncode"];
	    			$rows_bv["comments"] = $row["comments"];
	    			$rows_bv["journalmemo"] = $row["journalmemo"];
	    			$rows_bv["u_exc_fecrecep"] = $row["u_exc_fecrecep"];
	    			$rows_bv["u_ctg_numliq"] = $row["u_ctg_numliq"];
	    			$rows_bv["u_exx_serie"] = $row["folioprefixstring"];
	    			$rows_bv["u_exx_nroini_"] = $row["folionumber"];

	    			if ( isset($rows_bv["numatcard"]) ){//Para no tomar el primer arreglo, ver otra forma
	    				$arrPlayaInvoiceSale['arrData'][] = $rows_bv;
	    			}

			        $fSubtotal = 0;
		        	$fImpuesto = 0;
		        	$fTotal = 0;
	    		}
                $sIDSerieDocumento = $row["folioprefixstring"];
            }
            
            if ( $row["indicator"] == "03" && $sIDSerieDocumento == $row["folioprefixstring"] && $row["doctotal"] < 700.00 ) {//BV FINAL
	    		$rows_bv["u_exx_nroini"] = $rows_bv["u_exx_nroini_"];
	    		$rows_bv["numatcard"] = $rows_bv["u_exx_serie"].'-'.$rows_bv["u_exx_nroini_"].'-'.$row["folionumber"];
	    		$rows_bv["u_exx_nrofin"] = $row["folionumber"];

				$fSubtotal += $row["importe"];
				$fImpuesto += $row["impuestos"];
	        	$fTotal += $row["doctotal"];

    			$rows_bv["importe"] = $fSubtotal;
    			$rows_bv["impuestos"] = $fImpuesto;
    			$rows_bv["doctotal"] = $fTotal;
            }
    	}
		$arrPlayaInvoiceSale['arrData'][] = $rows_bv;

    	$arrResult = array();
    	if (count($arrPlayaInvoiceSale['arrData']) > 0){
			$arrResult['bStatus'] = true;
			$arrResult['arrData'] = $arrPlayaInvoiceSale['arrData'];
    	} else {
			$arrResult['bStatus'] = false;
	    	$arrResult['sMessage'] = 'No se encontraron registros';    		
    	}
    	return $arrResult;
    }
}
