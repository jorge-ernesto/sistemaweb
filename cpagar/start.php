<?php
/*
  Start.php
  Definir variables iniciales
  Incluir librerias
  @TBCA

*/

include_once('../valida_sess.php');

$rqst = 'MAIN.MAIN';
if (isset($_REQUEST["rqst"])) {
  $rqst = $_REQUEST["rqst"];
}

//load libraries
include_once('../include/mvc_sistemaweb.php');
include_once('../include/dbsqlca.php');
include_once('../include/class.form.php');
include_once('../include/class.form2.php');
include_once('../include/m_sisvarios.php');
include_once('../include/reportes2.inc.php');


//define CONSTANTS
define('ROWXPAGE', 20);
define('OK', 'OK');

//define global variables

$sqlca = new pgsqlDB('localhost','postgres', 'postgres', 'integrado');

//Authenticar usuario y definir ambiente

?>
