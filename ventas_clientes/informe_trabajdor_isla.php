<?php
session_start();

include_once('/sistemaweb/include/dbsqlca.php');
$sqlca = new pgsqlDB('localhost', 'postgres', 'postgres', 'integrado');
include('/sistemaweb/include/mvc_sistemaweb.php');
include('TrabajorXisla/t_matricula_trabajador.php');
include('TrabajorXisla/m_matricula_trabajador.php');

$objmodel	= new matricula_personal_Model();
$objtem		= new matricula_personal_Template();

$accion		= $_REQUEST['accion'];
$id_recibo	= $_REQUEST['id_recibo'];

try {
    
	?>
	    <link rel="stylesheet" href="/sistemaweb/css/sistemaweb.css" type="text/css">
	    <link rel="stylesheet" href="/sistemaweb/css/vales_liquidacion.css" type="text/css">
	    <script src="http://code.jquery.com/jquery-1.9.1.js"></script>
	    <script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
	    <script type="text/javascript">
		$(function(){
		             
		}
	    );
	    </script>

	<?php

    	$fecha		= $_REQUEST['fecha'];
    	$turno		= $_REQUEST['turno'];
    	$sucursal	= $_REQUEST['sucursal'];

    	$objmodel	= new matricula_personal_Model();
    	$objtem		= new matricula_personal_Template();
    	$data		= matricula_personal_Model::ObtenerreporteDetallado($fecha, $turno,$sucursal);

	matricula_personal_Template::CrearTablaReporteDetalle($data);

} catch (Exception $r) {
	echo "{'estado':'error','mes':'" . $r->getMessage() . "'}";
}

