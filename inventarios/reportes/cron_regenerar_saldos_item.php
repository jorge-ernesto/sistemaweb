<?php
include_once('/sistemaweb/include/dbsqlca.php');
$sqlca = new pgsqlDB('localhost', 'postgres', 'postgres', 'integrado');

require("/sistemaweb/clases/funciones.php");
$funcion	= new class_funciones;
$coneccion	= $funcion->conectar("","","","","");

// Verificar si se conecta a su central, en caso de que tenga replicador
$sql = "
SELECT
p1.par_valor,
p2.par_valor,
p3.par_valor,
p4.par_valor
FROM
int_parametros p1
LEFT JOIN int_parametros p2 ON p2.par_nombre = 'central_db'
LEFT JOIN int_parametros p3 ON p3.par_nombre = 'central_user'
LEFT JOIN int_parametros p4 ON p4.par_nombre = 'central_password'
WHERE
p1.par_nombre = 'central_ip';
";

$iStatusCentral = true;

if ($sqlca->query($sql) <= 0)
	$iStatusCentral = false;

if($sqlca->numrows()==1){
	$row = $sqlca->fetchRow();
	$arrCentral = Array($row[0], $row[1], $row[2], $row[3]);
} else {
	$iStatusCentral = false;	
}
// /. Verificar central

//Parametros del CRON
$dMonthIni = $argv[1];
$dYearIni = $argv[2];
$sAlmacen = $argv[3];

$sqlca->query("SELECT art_codigo FROM int_articulos WHERE art_plutipo IN('1', '4')");
$arrData = $sqlca->fetchAll();

foreach ($arrData as $row) {
	$sCodigoItem = $row['art_codigo'];
	$iStatus = $sqlca->query("SELECT inv_fn_regenera_item('" . $dYearIni . "', '" . $dMonthIni . "', '" . $sAlmacen . "', '" . $sCodigoItem . "', 'SI');");
	$sql_upd = "UPDATE int_articulos SET art_modifica_articulo=10 WHERE art_plutipo IN('1', '4') AND art_codigo = '" . $sCodigoItem . "';";
	if ( !$iStatusCentral ) { // Localmente
		// Aqui hay un problema, deberíamos tener un identificador que nos avise cuando se culmine el proceso de la funcion
		// inv_fn_regenera_item por cada item, evaluar que se podría realizar.
		$sqlca->query($sql_upd);
	} else { // Si es TRUE entonces modificamos en central
		/*
		$sHost = $arrCentral[0];
		$sDB = $arrCentral[1];
		$sUser = $arrCentral[2];
		$sPassword = $arrCentral[3];

		$conection_central = pg_connect("host=" . $sHost . " port=5432 dbname=" . $sDB . " user=" . $sUser . " password=" . $sPassword);
		$xsql_upd_central = pg_exec($conection_central, $sql_upd);
		*/
	}
}