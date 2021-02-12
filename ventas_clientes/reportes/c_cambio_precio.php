<?php

session_start();
include_once('/sistemaweb/include/dbsqlca.php');
$sqlca = new pgsqlDB('localhost', 'postgres', 'postgres', 'integrado');
include('/sistemaweb/include/mvc_sistemaweb.php');

include('t_cambio_precio.php');
include('m_cambio_precio.php');

$objmodel = new cambio_precioModel();
$objtem = new cambio_precioTemplate();

$accion = $_REQUEST['accion'];
try {
    if ($accion == "selecionabtn") {
        $fecha_inicio = $_REQUEST['fecha_inicio'];
        $fecha_final = $_REQUEST['fecha_final'];
        $cliente_dia = cambio_precioModel::registros_cambio_precio($fecha_inicio, $fecha_final);
        $lista = cambio_precioModel::lista_producto();
        $cantidad=cambio_precioModel::cantidad_producto();
        cambio_precioTemplate::CrearTablaCambio_Precio($cliente_dia, $lista, $cantidad);
        return;
    }
} catch (Exception $r) {
    echo $r->getMessage();
}
