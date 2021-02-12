<?php
/*
  Start.php
  Definir variables iniciales
  Incluir librerias
  @TBCA

*/
include_once('/sistemaweb/valida_sess.php');


$rqst = 'MAIN.MAIN';
if (isset($_REQUEST["rqst"])) {
  $rqst = $_REQUEST["rqst"];
}

//load libraries
include_once('../include/mvc_sistemaweb.php');
include_once('../include/dbsqlca.php');
include_once('../include/class.form.php');
include_once('../include/class.form2.php');
//include_once("lib/class_xmlrpc_acosa.php"); // This is the file containing all of the PHP functions


//define CONSTANTS
define('ROWXPAGE', 20);
define('OK', 'OK');

//define global variables

$sqlca = new pgsqlDB('localhost','postgres', 'postgres', 'integrado');

//Authenticar usuario y definir ambiente

