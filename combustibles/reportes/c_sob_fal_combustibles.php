<?php

session_start();
include_once('/sistemaweb/include/dbsqlca.php');
$sqlca = new pgsqlDB('localhost', 'postgres', 'postgres', 'integrado');
include('/sistemaweb/include/mvc_sistemaweb.php');


include('t_sob_fal_combustibles.php');
include('m_sob_fal_combustibles.php');


$objmodel = new sob_fal_combustibles_Model();
$objtem = new sob_fal_combustibles_Template();

$accion = $_REQUEST['accion'];
try {

   if ($accion == "executar_reporte") {
        $almacen    = $_REQUEST['almacen'];
        $tanque    = $_REQUEST['tanque'];
        $unidadmedida    = $_REQUEST['unidadmedida'];
        $fechad    = $_REQUEST['fecha_inicio'];
        $fechaa    = $_REQUEST['fecha_final'];
        $compras = 'Si';

        $REP = sob_fal_combustibles_Model::sobrantesyfaltantesReporte($almacen, $tanque, $fechad, $fechaa, $unidadmedida, $compras);
        echo "<script>console.log('" . json_encode($REP) . "')</script>";
        
        sob_fal_combustibles_Template::CrearTablaReporte($REP,$almacen);
    } 
    else if ($accion == "executar_reporte_excel") {
        $almacen    = $_REQUEST['almacen'];
        $tanque    = $_REQUEST['tanque'];
        $unidadmedida    = $_REQUEST['unidadmedida'];
        $fechad    = $_REQUEST['fecha_inicio'];
        $fechaa    = $_REQUEST['fecha_final'];
        $compras = 'Si';

        $datars = sob_fal_combustibles_Model::sobrantesyfaltantesReporte($almacen, $tanque, $fechad, $fechaa, $unidadmedida, $compras);

        $_SESSION['data_excel']=$datars;
        $_SESSION['almacen']=$almacen;
        $_SESSION['tanque']=$tanque;
        $_SESSION['unidadmedida']=$unidadmedida;
        $_SESSION['fecha_inicio']=$fechad;
        $_SESSION['fecha_final']=$fechaa;   
    }
    
    
} catch (Exception $r) {
    echo "<b style='color:red;with:18px;'>".$r->getMessage()."</b>";
}
