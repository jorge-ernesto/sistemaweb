<?php
include_once("/sistemaweb/include/config.php");
include_once("/sistemaweb/include/dbsqlca.php");

global $db_host, $db_user, $db_password, $db_name;
$sqlca = new pgsqlDB($db_host, $db_user, $db_password, $db_name);

$sql = "select prom_vencer_puntos()";
$sqlca->query($sql);

