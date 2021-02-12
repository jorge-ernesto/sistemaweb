<?php
/*
  Start.php
  Definir variables iniciales
  Incluir librerias
  @TBCA

*/

//session_start();

//include('../valida_sess.php');
include("/sistemaweb/valida_sess.php");
$rqst = 'MAIN.MAIN';
if (isset($_REQUEST["rqst"])) {
  $rqst = $_REQUEST["rqst"];
}
//Para guardar valores de uso frecuente
if (!isset($_SESSION["_cache"])) {
  $_SESSION["_cache"] = array();
}
$_cache = &$_SESSION["_cache"];

//Para guardar parametros de uso frecuente
if (!isset($_SESSION["_parametros"])) {
 $_SESSION["_parametros"]= array();
}
$_parametros = &$_SESSION["_parametros"];

//load libraries
include_once('../include/mvc_sistemaweb.php');
include_once('../include/dbsqlca.php');
include_once('../include/class.form.php');
include_once('../include/class.form2.php');
//include_once('../include/reportes2.inc.php');
include_once('../include/documentos.inc.php');
include_once('../include/libexcel/Worksheet.php');
include_once('../include/libexcel/Workbook.php');
include_once('../include/Classes/PHPExcel.php');
//include_once("lib/class_xmlrpc_acosa.php"); // This is the file containing all of the PHP functions

//define CONSTANTS
define('ROWXPAGE', 20);
define('OK', 'OK');

//define global variables

$sqlca = new pgsqlDB('localhost','postgres', 'postgres', 'integrado');

//Authenticar usuario y definir ambiente

