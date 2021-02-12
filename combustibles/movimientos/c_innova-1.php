<?php
require 't_innova-1.php';
require 'm_innova-1.php';
class c_innova_1 {
	function __construct() {

	}

	public function getLocaltime() {
		$localtime = localtime();
		$localtime['year'] = '20'.substr($localtime[5], -2);
		$localtime['day'] = $localtime[3];
		if (strlen($localtime['day']) < 2 ) {
			$localtime['day'] = '0'.$localtime['day'];
		}

		$localtime['month'] = $localtime[4] + 1;
		if (strlen($localtime['month']) < 2 ) {
			$localtime['month'] = '0'.$localtime['month'];
		}
		return $localtime;
	}

	public function index() {
		$t_innova_1 = new t_innova_1();
		$m_innova_1 = new m_innova_1();
		$data['warehouse'] = $m_innova_1->getWarehouse();
		$localtime = $this->getLocaltime();
		$data['consult_initial_date'] = $localtime;
		$data['tableConfiguration'] = $m_innova_1->getTableConfiguration();
		$t_innova_1->index($data);
	}
	
	public function viewDetailConfiguration() {
		$t_innova_1 = new t_innova_1();
		$m_innova_1 = new m_innova_1();
		$req['table_id'] = $_POST['table_id'];
		$detailTable = $m_innova_1->getDetailTableConfigurationById($req);
		$t_innova_1->viewDetailTableConfiguration($detailTable);
	}

	public function preview($req) {
		error_log('Inicia la previsualización de informacion Innova, '.$this->getMemoryUsage());
		$m_innova_1 = new m_innova_1();
		$t_innova_1 = new t_innova_1();

		$m_innova_1->setIsDebug(true);
		$m_innova_1->setIsViewTableName(true);
		$user = $m_innova_1->getUserIdByChLogin();
		if ($user['error']) {
			$t_innova_1->statusRequest('Error [', array(
				'error' => true,
				'code' => -1,
				'message' => 'No se encontró sesión',
			));
			exit;
		}

		//validar que se reciba la fecha y no esté vacía
		$arr_initial_date = explode('/', $req['initial_date']);
		$t_innova_1->inputDateHidden('in-export-initial-date', $req['initial_date']);
		$arr_final_date = explode('/', $req['final_date']);
		$t_innova_1->inputDateHidden('in-export-final-date', $req['final_date']);

		$req['initial_date'] = $arr_initial_date[2].'-'.$arr_initial_date[1].'-'.$arr_initial_date[0];
		$req['final_date'] = $arr_final_date[2].'-'.$arr_final_date[1].'-'.$arr_final_date[0];
		$req['pos_trans'] = 'pos_trans'.$arr_initial_date[2].$arr_initial_date[1];

		$req['last_day_close'] = $m_innova_1->getLastDayClose();//resolver lo del día creado
		$req['tax'] = $m_innova_1->getTax();

		$req['tableName'] = 'getBPartner';
		$m_innova_1->getBPartner($req);

		$req['tableName'] = 'getInvoiceHeaderSaleCash';
		$data['invoiceHeaderSaleCash'] = $m_innova_1->getInvoiceHeaderSaleCash($req);
		$data['invoiceHeaderSaleCash']['isViewTableName'] = $m_innova_1->isViewTableName;
		$data['invoiceHeaderSaleCash']['tableTitle'] = 'Facturas de venta(Cabecera)';
		$data['invoiceHeaderSaleCash']['mode'] = 0;//cabecera
		$t_innova_1->tableDinamic($data['invoiceHeaderSaleCash']);

		$req['tableName'] = 'getInvoiceDetailSaleCash';
		$data['invoiceDetailSaleCash'] = $m_innova_1->getInvoiceDetailSaleCash($req);
		$data['invoiceDetailSaleCash']['isViewTableName'] = $m_innova_1->isViewTableName;
		$data['invoiceDetailSaleCash']['tableTitle'] = 'Facturas de venta(Detalle)';
		$data['invoiceDetailSaleCash']['mode'] = 1;//detalle
		$t_innova_1->tableDinamic($data['invoiceDetailSaleCash']);

		$m_innova_1->ticketHead = array();

		error_log('Por finalizar previsualizacion de informacion SAP, '.$this->getMemoryUsage());

		unset($data, $req, $connectionData, $hanaInstance, $t_innova_1, $m_innova_1);
		error_log('Finaliza la previsualizacion de informacion SAP, '.$this->getMemoryUsage());
	}

	public function export($req) {
		error_log('Inicia la previsualización de informacion Innova, '.$this->getMemoryUsage());
		$m_innova_1 = new m_innova_1();
		$t_innova_1 = new t_innova_1();

		$m_innova_1->setIsDebug(true);
		$m_innova_1->setIsViewTableName(true);
		$user = $m_innova_1->getUserIdByChLogin();
		if ($user['error']) {
			$t_innova_1->statusRequest('Error [', array(
				'error' => true,
				'code' => -1,
				'message' => 'No se encontró sesión',
			));
			exit;
		}

		//validar que se reciba la fecha y no esté vacía
		$arr_initial_date = explode('/', $req['initial_date']);
		$arr_final_date = explode('/', $req['final_date']);

		$req['initial_date'] = $arr_initial_date[2].'-'.$arr_initial_date[1].'-'.$arr_initial_date[0];
		$req['final_date'] = $arr_final_date[2].'-'.$arr_final_date[1].'-'.$arr_final_date[0];
		$req['pos_trans'] = 'pos_trans'.$arr_initial_date[2].$arr_initial_date[1];

		$req['last_day_close'] = $m_innova_1->getLastDayClose();
		$req['tax'] = $m_innova_1->getTax();

		$req['tableName'] = 'getBPartner';
		$m_innova_1->getBPartner($req);

		if ($req['mode'] == '0') {
			$req['tableName'] = 'getInvoiceHeaderSaleCash';
			$data['invoiceHeaderSaleCash'] = $m_innova_1->getInvoiceHeaderSaleCash($req);
			$data['invoiceHeaderSaleCash']['isViewTableName'] = $m_innova_1->isViewTableName;
			$data['invoiceHeaderSaleCash']['filename'] = 'invoiceheader_'.$arr_initial_date[2].$arr_initial_date[1].$arr_initial_date[0];
			$t_innova_1->lines($data['invoiceHeaderSaleCash'], $req);
		} else if ($req['mode'] == '1') {
			$req['tableName'] = 'getInvoiceDetailSaleCash';
			$data['invoiceHeaderSaleCash'] = $m_innova_1->getInvoiceDetailSaleCash($req);
			$data['invoiceHeaderSaleCash']['isViewTableName'] = $m_innova_1->isViewTableName;
			$data['invoiceHeaderSaleCash']['filename'] = 'invoicedetail_'.$arr_initial_date[2].$arr_initial_date[1].$arr_initial_date[0];
			$t_innova_1->lines($data['invoiceHeaderSaleCash'], $req);
		}

		error_log('Por finalizar previsualizacion de informacion SAP, '.$this->getMemoryUsage());

		unset($data, $req, $connectionData, $hanaInstance, $t_innova_1, $m_innova_1);
		error_log('Finaliza la previsualizacion de informacion SAP, '.$this->getMemoryUsage());
	}

	public function getMemoryUsage() {
		return (memory_get_usage(true) / 1024 / 1024);
	}

	public function viewDetailConfigurationUPD($arrDataPOST) {
		$m_innova_1 = new m_innova_1();
		$t_innova_1 = new t_innova_1();
		$detailTable = $m_innova_1->updDetailTableConfigurationById($arrDataPOST);
		$t_innova_1->viewDetailTableConfiguration($detailTable);
	}
	
	public function viewDetailConfigurationSAVE($arrDataPOST) {
		$m_innova_1 = new m_innova_1();
		$t_innova_1 = new t_innova_1();
		$detailTable = $m_innova_1->saveDetailTableConfigurationById($arrDataPOST);
		$t_innova_1->viewDetailTableConfiguration($detailTable);
	}
}