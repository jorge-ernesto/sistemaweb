<?php

include("/sistemaweb/valida_sess.php");
include("/sistemaweb/functions.php");
require("/sistemaweb/clases/funciones.php");
$funcion = new class_funciones;
// crea la clase para controlar errores
$clase_error = new OpensoftError;
$clase_error->_error();
// conectar con la base de datos
$conector_id = $funcion->conectar("","","","","");

$accion = $_REQUEST['accion'];

if($accion == 'GetPrecioSurtidor'){

	$cod_almacen = $_REQUEST['cod_almacen'];
	$id_surtidor = $_REQUEST['id_surtidor'];

	$sql = "
		SELECT
			c.nu_preciocombustible nuprecio,
			s.nu_contometrogalon nulecturasgalones,
			s.nu_contomtrovalor nulecturasoles
		FROM
			comb_ta_surtidores s
			JOIN comb_ta_combustibles c ON (s.ch_codigocombustible = c.ch_codigocombustible)
		WHERE
			s.ch_surtidor		= '".$id_surtidor."'
			AND s.ch_sucursal	= trim('".$cod_almacen."')
		ORDER BY
			s.ch_surtidor;
	";

	$sqlca->query($sql);

	$rows = $sqlca->fetchRow();

	$rows = json_encode($rows);

	echo $rows;

//	echo '{"data":'.$rows.'"}';

//	echo "{'msg':'" . $cadena . "', 'msg2':'" . $cadena2 . "', 'msg3' : '".$cadena3."'}";

}

