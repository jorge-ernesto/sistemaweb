<?php
/*
  Start.php
  Definir variables iniciales
  Incluir librerias
  @TBCA

*/

session_start();

$rqst = 'MAIN.MAIN';
if (isset($_REQUEST["rqst"])) {
  $rqst = $_REQUEST["rqst"];
}

//load libraries
include_once('lib/mvc_acosa.php');
include_once('lib/dbsqlca.php');
include_once('lib/class.form.php');

//define CONSTANTS
define('ROWXPAGE', 20);
define('OK', 'OK');

//define global variables

$sqlca = new pgsqlDB('localhost','postgres', 'postgres', 'integrado');

//Authenticar usuario y definir ambiente

?>