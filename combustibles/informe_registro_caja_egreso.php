<?php

session_start();

include_once('/sistemaweb/include/dbsqlca.php');
$sqlca = new pgsqlDB('localhost', 'postgres', 'postgres', 'integrado');
include('/sistemaweb/include/mvc_sistemaweb.php');

include('movimientos/m_egreso_caja.php');
include('movimientos/c_egreso_caja.php');
include('movimientos/t_egreso_caja.php');

$objmodel = new RegistroCajasModel();
$objtem = new RegistroCajasTemplate();
$objcomn = new RegistroCajaController();


$accion = $_REQUEST['accion'];
$id_recibo = $_REQUEST['id_recibo'];
$sucursal = $_REQUEST['sucursal'];
try {
    ?>
<link rel="stylesheet" href="/sistemaweb/css/sistemaweb.css" type="text/css">
        <link rel="stylesheet" href="/sistemaweb/css/vales_liquidacion.css" type="text/css">
<?php
    
    $data_cabecera = RegistroCajasModel::DetalleReporteRecibo($id_recibo,$sucursal);
    $data_detalle = RegistroCajasModel::DetalleReporteRecibo_complemento_registro($id_recibo,$sucursal);
    $data_medios_pago = RegistroCajasModel::DetalleReporteRecibo_medio_pago($id_recibo,$sucursal);

    RegistroCajasTemplate::viewtabla_detalle_recibo($data_cabecera,$data_detalle,$data_medios_pago);
} catch (Exception $r) {

    echo "{'estado':'error','mes':'" . $r->getMessage() . "'}";
}
