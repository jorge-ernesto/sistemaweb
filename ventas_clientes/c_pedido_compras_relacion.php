<?php

session_start();

include_once('/sistemaweb/include/dbsqlca.php');
$sqlca = new pgsqlDB('localhost', 'postgres', 'postgres', 'integrado');
include('/sistemaweb/include/mvc_sistemaweb.php');

include('reportes/c_pedido_compras.php');
include('reportes/t_pedido_compras.php');
include('reportes/m_pedido_compras.php');

$objmodel = new PedidoComprasModel();
$objtem = new PedidoComprasTemplate();
$objcomn = new PedidoComprasController("");


//print_r($_REQUEST);
//$accion = $_REQUEST['accion']; completar_pedido

$almacen 	= $_REQUEST['almacen'];
$producto	= $_REQUEST['producto'];
$tipopedido	= $_REQUEST['tipopedido'];

$data 	= PedidoComprasModel::completarpedido($almacen, $producto, $tipopedido);

$data1 = $data['mes_3'];
$data2 = $data['mes_2'];
$data3 = $data['mes_1'];
$data4 = $data['stk_actual'];
$data5 = $data['stk_minimo'];
$data6 = $data['stk_maximo'];
$data7 = $data['cantidad'];
$data8 = $data['sugerido'];

echo "{'dato1':'$data1','dato2':'$data2','dato3':'$data3','dato4':'$data4','dato5':'$data5','dato6':'$data6','dato7':'$data7','dato8':'$data8'}";


		


