<?php
	/* Definir variables iniciales
	   Incluir librerias
	   @TBCA  
	*/

	session_start();
	
	$rqst = 'MAIN.MAIN';
	if (isset($_REQUEST["rqst"])) 
	{
		$rqst = $_REQUEST["rqst"];
	}

	include_once('include/mvc_sistemaweb.php');
	include_once('include/dbsqlca.php');
	include_once('include/class.form.php');

	//define CONSTANTS
	define('ROWXPAGE', 20);
	define('OK', 'OK');

	//define global variables
	$sqlca = new pgsqlDB('localhost','postgres', 'conejitalinda777', 'integrado');
        $sqlca->cursor
        
?>
