<?php
/*
OCS PHP MSSQL to SQLSRV wrapper
This wrapper will emulate some of the MSSQL functions and wrap them to SQLSRV extension function

Emulated functions: 
mssql_close
mssql_connect - Note: $new_link ignored; new connection always created
mssql_escape
mssql_fetch_array
mssql_fetch_row
mssql_free_result
mssql_get_last_message
mssql_num_rows
mssql_query - $batch_size ignored; defaults used
mssql_select_db

mssql_scope_identity
*/

define("MSSQL_LAST_CONNECTION_KEY","_MSSQL_Last_Connection");
$GLOBALS[MSSQL_LAST_CONNECTION_KEY] = NULL;

if (!function_exists("mssql_connect")) {
	function mssql_connect($servername = NULL,$username = NULL,$password = NULL,$new_link = TRUE) {
		$servername = str_replace(":", ", ", $servername);
		$connectionInfo = array("CharacterSet" => "UTF-8", "UID" => $username, "PWD" => $password);
		$ret = sqlsrv_connect($servername,$connectionInfo);

		if($ret){
			error_log("Conexion establecida");
		}else{
			error_log("Conexion no se pudo establecer");
			error_log( json_encode( sqlsrv_errors() ) );
		}

		if ($ret === FALSE)
			return $ret;
		$GLOBALS[MSSQL_LAST_CONNECTION_KEY] = $ret;
		return $GLOBALS[MSSQL_LAST_CONNECTION_KEY];
	}
}

if (!function_exists("mssql_close")) {
	function mssql_close($link_identifier = NULL) {
		if ($link_identifier === NULL)
			$link_identifier = $GLOBALS[MSSQL_LAST_CONNECTION_KEY];
		return sqlsrv_close($link_identifier);
	}
}

if (!function_exists("mssql_close")) {
	function mssql_escape($str) {
		return str_replace("'","''",$str);
	}
}

if (!function_exists("mssql_select_db")) {
	function mssql_select_db($database_name,$link_identifier = NULL){
		if ($link_identifier === NULL)
			$link_identifier = $GLOBALS[MSSQL_LAST_CONNECTION_KEY];
		$sql = "USE " . $database_name;
		return sqlsrv_query($link_identifier,$sql);
	}
}

if (!function_exists("mssql_num_rows")) {
	function mssql_num_rows($result) {
		return sqlsrv_num_rows($result);
	}
}

if (!function_exists("mssql_query")) {
	function mssql_query($query,$link_identifier = NULL,$batch_size = 0) {
		if ($link_identifier === NULL)
			$link_identifier = $GLOBALS[MSSQL_LAST_CONNECTION_KEY];
		$ret = sqlsrv_query($link_identifier,$query);
		if ($ret === FALSE)
			return $ret;
		$mrq = sqlsrv_rows_affected($ret);
		$frq = sqlsrv_num_rows($ret);
		if ($mrq >= 0 || $frq <= 0) {
			sqlsrv_free_stmt($ret);
			return TRUE;
		}
		return $ret;
	}
}

if (!function_exists("mssql_get_last_message")) {
	function mssql_get_last_message() {
		$errors = sqlsrv_errors(SQLSRV_ERR_ERRORS);
		$err = NULL;
		foreach ($errors as $e)
			$err = $e;
		if ($err === NULL || !is_array($err))
			return "";
		return "SQLSTATE " . $err["SQLSTATE"] . " - Code " . $err["code"] . " - Message: " . $err["message"];
	}
}

if (!function_exists("mssql_free_result")) {
	function mssql_free_result($result) {
		return sqlsrv_free_stmt($result);
	}
}

if (!function_exists("mssql_fetch_array")) {
	function mssql_fetch_array($result,$result_type = MSSQL_BOTH) {
		switch ($result_type) {
			case MSSQL_ASSOC:
				$fetchType = SQLSRV_FETCH_ASSOC;
				break;
			case MSSQL_NUM:
				$fetchType = SQLSRV_FETCH_NUMERIC;
				break;
			case MSSQL_BOTH:
				$fetchType = SQLSRV_FETCH_BOTH;
				break;
			default:
				return FALSE;
		}
		return sqlsrv_fetch_array($result,$fetchType);
	}
}

if (!function_exists("mssql_fetch_row")) {
	function mssql_fetch_row($result) {
		return sqlsrv_fetch_array($result,SQLSRV_FETCH_NUMERIC);
	}
}
