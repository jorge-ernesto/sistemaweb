<?php


// activamos todos los errores
//error_reporting(E_ALL);

//include('../clases/funciones.php');
require("../clases/funciones.php");


$error = new AdminError;

// Esto produce dos Notices
echo $data[constante_que_no_existe];

// Esto produce un Warning
$handler = "No soy un handler adecuado";
$data = fgets($handler, 23);

// Esto produce un Fatal Error
//funcion_que_no_existe();

$funcion = new class_funciones;



$error = new OpensoftError;

// conectar con la base de datos
$conector_id=$funcion->conectar("","","","","");

	$sql="insert into COM_DETALLE ( PRO_CODIGO, NUM_TIPDOCUMENTO, NUM_SERIEDOCUMENTO, COM_CAB_NUMORDEN, ART_CODIGO,
					COM_DET_CANTIDADPEDIDA, COM_DET_PRECIO, COM_DET_DESCUENTO1, COM_DET_ESTADO )
					values ('00S016','01', '001','00000071', '0000000000002',
					1,10, 10, '1') ";
		$xsql=pg_query( $conector_id,  $sql );



