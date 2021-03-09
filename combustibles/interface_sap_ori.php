<?php
require '../valida_sess.php';
require '../config.php';

require 'movimientos/c_sap-1.php';

$sqlca->query("SELECT par_valor FROM int_parametros WHERE par_nombre = 'version_sap';");
$a = $sqlca->fetchRow(); 
if($a[0] == "2"){ //Versión 1: Original, Versión 2: Requerimiento SAP Energigas
	$_SESSION['es_requerimiento_sap_energigas'] = true; 
}else{
	$_SESSION['es_requerimiento_sap_energigas'] = false; 
}
$_SESSION['debug'] = true; 

if (!isset($_REQUEST['action'])) {
	$c_sap_1 = new c_sap_1();
	$c_sap_1->index();
} else {
	if (isset($_REQUEST['action'])) {
		$c_sap_1 = new c_sap_1();
		switch ($_REQUEST['action']) {
			case 'delete-exports_day':
				//consultar dias exportados
				$c_sap_1->delete_export_day($_REQUEST);
				break;
			case 'consult-exports':
				//consultar dias exportados
				$c_sap_1->consult($_REQUEST);
				break;
			case 'preview':
				//consultar re visualizacion de información				
				echo "<script>console.log('REQUEST: " . json_encode($_REQUEST) . "')</script>"; 				
				echo "<script>console.log('es_requerimiento_sap_energigas: " . json_encode($_SESSION['es_requerimiento_sap_energigas']) . "')</script>";
				$c_sap_1->preview($_REQUEST);
				break;
			case 'export':
				//Exportar información a SAP				
				$c_sap_1->export($_REQUEST);
				break;
			case 'consult-configuration':
				//Exportar información a SAP				
				$c_sap_1->viewDetailConfiguration($_REQUEST);
				break;
			case 'test-utf8':
				//Demo
				$c_sap_1->testInsertUtf8($_REQUEST);
				break;
			default:
				$c_sap_1->error404();
				break;
		}
	}
}