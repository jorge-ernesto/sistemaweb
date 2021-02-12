<?php
require 'dbsqlca.php';
date_default_timezone_set('America/Lima');
$db_host = "localhost";
$db_name = "opensoft";
$db_user = "postgres";
$db_password = "postgres";
$sqlca = new pgsqlDB($db_host, $db_user, $db_password, $db_name);
define('SAVE_LOGS', true);

if (!$sqlca->connection) {
	_error_log(0, 'Error al conectar con opensoft');
	die(
		json_encode(
			array(
				'error' => true,
				'errorCode' => 100,
				//'errorMessage' => 'Connection',
			)
		)
	);
} else {
	_error_log(0, 'Conexion inicial con opensoft');
}

/**
 * Almacenar logs
 * type: {0: texto, 1: array}
 */
function _error_log($type, $process = '', $array = array()) {
	if(SAVE_LOGS) {
		$message = $process == '' ? "\n".$process : "\n".$process;
		if ($type == 1) {
			$message .= "\n---------------------";
		}
		foreach (array_keys($array) as $key => $id) {
			$message .= "\n".$id.' => '.$array[$id];
		}
		if ($type == 0 || $type == 1) {
			$message .= "\n---------------------\n";
		}
		$date = date('Ymd');
		error_log($message, 3, dirname(__FILE__)."/logs/exporter_$date.log");
	}
}

/**
 * 2017-08-31
 * ** Actualización para crear un archivo por día con los logs de exporter
 * + date_default_timezone_set para usar la funcion date
 * + $date = date('Ymd');
 * * error_log($message, 3, dirname(__FILE__)."/logs/exporter_$date.log");//modificado para conatenar el date
 */