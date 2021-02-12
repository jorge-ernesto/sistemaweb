<?php
ini_set("memory_limit", '3G');
ini_set("upload_max_filesize", "15M");
ini_set("post_max_size", "15M");
ini_set('max_execution_time', '700');
ini_set('max_input_time', '700');

date_default_timezone_set('America/Lima');

class TicketsGNVController extends Controller {

	function Init() {
		$this->visor = new Visor();
		$this->task = @$_REQUEST["task"];
		$this->action = isset($_REQUEST["action"])?$_REQUEST["action"]:'';
	}

	function Run() {
		include('movimientos/m_tickets_gnv.php');
		include('movimientos/t_tickets_gnv.php');

		$objModelGNV = new TicketsGNVModel();
		$objTemplateGNV = new TicketsGNVTemplate();

		$this->Init();
		$result   = "";
		$result_f = "";
		$form_search = false;

		switch ($this->action) { 
			case "Cargar Datos":
				$sCodeWarehouse = $_SESSION['almacen'];
				$arrVersionExcelGNV	= $_REQUEST["version"];

				$filename 	= $_FILES['ubica']['name'];
				$resultado 	= $objModelGNV->extension($filename);
				$tamano 	= $_FILES['ubica']['size']/1024/1024;
				$tamano 	= substr($tamano,0,5);

				if($_FILES['ubica']["error"] > 1) {
					echo "<script>alert('Error al ubicar el archivo')</script>";
				} else if($tamano >= 15 ) {
					echo "<script>alert('Error el archivo debe ser menor a 15MB')</script>";
				} else if($resultado != 'xls') {
					?><script>alert("<?php echo 'Error la extension debe de ser .xls' ; ?> ");</script><?php
				}else {
					//ahora con la funcion move_uploaded_file lo guardaremos en el destino que queramos
					move_uploaded_file($_FILES['ubica']['tmp_name'],"/sistemaweb/combustibles/" . $_FILES['ubica']['name']);

					$sPathFileExcelGNV = "/sistemaweb/combustibles/" . $_FILES['ubica']['name'];

        			$objModelGNV->managerTransaction('BEGIN');

        			$arrDataHeader = array(
        				'sFileName' => $sPathFileExcelGNV,
						'sNameTable' => 'c_invoiceheader',
        				'arrTableColumns' => 
						array(
							"created",
							"createdby",
							"updated",
							"updatedby",
							"isactive",
							"c_org_id",
							"c_bpartner_id",
							"c_currency_id",
							"issale",
							"c_tendertype_id",
							"status",
							"documentno",
							"c_doctype_id",
							"documentserial",
						),
						'iStartExcel' => 8,//RECIBO
						'sVersionExcelGNV' => $arrVersionExcelGNV,
						'sCodeWarehouse' => $sCodeWarehouse,
        			);
					$arrResponseHeader = $objModelGNV->xls2sql($arrDataHeader);

					if ( $arrResponseHeader["sStatus"] != 'success' ) {
                        $result_f = $arrResponseHeader["sMessage"];
                        unlink($sPathFileExcelGNV);
					} else {
	        			$arrDataDetail = array(
	        				'sFileName' => $sPathFileExcelGNV,
							'sNameTable' => 'c_invoicedetail',
	        				'arrTableColumns' => 
							array(
								"created",
								"createdby",
								"updated",
								"updatedby",
								"isactive",
								"c_invoiceheader_id",
								"c_product_id",
								"unitprice",
								"linetotal",
								"quantity",
							),
							'iStartExcel' => 8,//RECIBO
							'sVersionExcelGNV' => $arrVersionExcelGNV,
	        			);
						$arrResponseDetail = $objModelGNV->xls2sqldet($arrDataDetail);

						if ( $arrResponseDetail["sStatus"] != 'success' ) {
	                        $result_f = $arrResponseDetail["sMessage"];
	                        unlink($sPathFileExcelGNV);
						} else {
							$objModelGNV->managerTransaction('COMMIT');
	                        $result_f = 'Datos cargados satisfactoriamente';
	                        unlink($sPathFileExcelGNV);
						}
					}
				}
			break;

			default:
				$form_search = true;
			break;
		}
	
		if($form_search) {
			$result = $objTemplateGNV->FormCargar();
		}

		$this->visor->addComponent("ContentT", "content_title", $objTemplateGNV->titulo());
		if($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
		if($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);	
	}
}
