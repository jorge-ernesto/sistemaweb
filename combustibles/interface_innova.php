
<?php
require '../valida_sess.php';
require '../config.php';

require 'movimientos/c_innova-1.php';

/**
 * Notes:
 * Completar documentos del 01/01/18 para poder ver mas registros de ese día (prueba)
 * Cuando son varias placas en facturas manuales, que debe mostrar? solo una?
 */

if (!isset($_REQUEST['action'])) {
	$c_innova_1 = new c_innova_1();
	$c_innova_1->index();
} else {
	if (isset($_REQUEST['action'])) {
		$c_innova_1 = new c_innova_1();
		switch ($_REQUEST['action']) {
			/**
			 * Pendiente...
			 */
			/*case 'consult-exports':
				//consultar dias exportados
				$c_innova_1->consult($_REQUEST);
				break;
			*/
			case 'preview':
				//consultar re visualizacion de información
				$c_innova_1->preview($_REQUEST);
				break;
			case 'export':
				//Exportar en .txt
				$c_innova_1->export($_REQUEST);
				break;
			case 'consult-configuration':
				//Exportar información a SAP
				$c_innova_1->viewDetailConfiguration($_REQUEST);
				break;
			case 'upd-configuration':
				//Exportar información a SAP
				$c_innova_1->viewDetailConfigurationUPD($_REQUEST);
				break;
			case 'save-configuration':
				//Exportar información a SAP
				$c_innova_1->viewDetailConfigurationSAVE($_REQUEST);
				break;
			default:
				$c_innova_1->error404();
				break;
		}
	}
}