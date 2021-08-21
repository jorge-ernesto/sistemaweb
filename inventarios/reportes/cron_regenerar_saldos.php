<?php
include_once('/sistemaweb/include/dbsqlca.php');
$sqlca = new pgsqlDB('localhost', 'postgres', 'postgres', 'integrado');

//Parametros del CRON
$dMonthIni = $argv[1];
$dYearIni = $argv[2];
$sAlmacen = $argv[3];

$iStatusTable = $sqlca->query("SELECT 1 FROM information_schema.tables WHERE table_schema = 'public' AND table_name = 'tmp_item'");
if ((int)$iStatusTable == 1){ //Existe tabla
	$sqlca->query("UPDATE tmp_item SET art_modifica_articulo=0;");
} else {
	$sqlca->query("CREATE TABLE tmp_item AS SELECT art_codigo, art_modifica_articulo FROM int_articulos WHERE art_plutipo IN('1', '4') AND art_estado='0';");
}

$sqlca->query("SELECT art_codigo FROM int_articulos WHERE art_plutipo IN('1', '4') AND art_estado='0';");
$arrData = $sqlca->fetchAll();

foreach ($arrData as $key=>$row) {
	$sCodigoItem = $row['art_codigo'];
	$iStatus = $sqlca->query("SELECT inv_fn_regenera_item('" . $dYearIni . "', '" . $dMonthIni . "', '" . $sAlmacen . "', '" . $sCodigoItem . "', 'SI');");
	$sql_upd = "UPDATE tmp_item SET art_modifica_articulo=1 WHERE art_codigo = '" . $sCodigoItem . "';";

	/* Debug */
	error_log("******".$key."*******");
	error_log("******".$sql_upd."*******");
	/* Fin Debug */

	// Aqui hay un problema, deberíamos tener un identificador que nos avise cuando se culmine el proceso de la funcion
	// inv_fn_regenera_item por cada item, evaluar que se podría realizar.
	$sqlca->query($sql_upd);
}