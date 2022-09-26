<?php
// Descomentar estas líneas, cuando estamos en modo - development
/*
error_reporting(-1);
ini_set('display_errors', 1);
*/
// Descomentar estas líneas, cuando estamos en modo - production

ini_set('display_errors', 0);
if (version_compare(PHP_VERSION, '5.3', '>='))
{
	error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
}
else
{
	error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE);
}


/**
 * Información de Tablas
 * int_articulos - Maestro de Items
 * pos_transYYYYMM - Ventas en playa CABECERA y DETALLE, la opción no debe de depender de esta tabla, ya que cuando se instala solo para oficina no se cuenta con dicha tabla.
 **/
class modelSalesXEmployee {

	function array_debug($data){
		echo "<pre>";
		print_r($data);
		echo "</pre>";
	}

	public function getAllData($arrPost){
		global $sqlca;

	    $cond_almacen = (!empty($arrPost['iAlmacen']) ? "AND PT.es = '" . $arrPost['iAlmacen'] . "'" : '');
	    $cond_cliente = ((!empty($arrPost['iIdTrabajador']) && !empty($arrPost['sNombreTrabajador'])) ? "AND PERASIG.ch_codigo_trabajador = '" . $arrPost['iIdTrabajador'] . "'" : '');
	    $cond_item = ((!empty($arrPost['iIdItem']) && !empty($arrPost['sNombreItem'])) ? "AND PT.codigo = '" . $arrPost['iIdItem'] . "'" : '');

		$dInicial = trim($arrPost['dInicial']);
		$dInicial = strip_tags(stripslashes($dInicial));
		$dInicial = explode("/", $dInicial);
		$iMonthIni = $dInicial[1];
		$table_postransYM = 'pos_trans' . $dInicial[2] . $dInicial[1];
		$dInicial = $dInicial[2] . "-" . $dInicial[1] . "-" . $dInicial[0];

		$dFinal = trim($arrPost['dFinal']);
		$dFinal = strip_tags(stripslashes($dFinal));
		$dFinal = explode("/", $dFinal);
		$iMonthFin = $dFinal[1];
		$dFinal = $dFinal[2] . "-" . $dFinal[1] . "-" . $dFinal[0];

		if ( $iMonthIni != $iMonthFin ) {
			return array(
		    	'sStatus' => 'warning',
		    	'sMessage' => 'El rango de fecha debe ser dentro del mismo mes',
		   	);
		}

    	$sql = "
SELECT
 PERASIG.ch_codigo_trabajador AS id_trabajador,
 TRABA.ch_nombre1 AS no_nombre_trabajador,
 TRABA.ch_apellido_paterno AS no_apellido_paterno,
 TRABA.ch_apellido_materno AS no_apellido_materno,
 PT.td AS no_tipo_documento,
 PT.trans AS nu_id_trans,
 PT.caja AS nu_caja,
 PT.turno AS nu_turno,
 DATE_TRUNC('second', PT.fecha) AS fe_emision,
 PT.ruc AS nu_ruc,
 PT.codigo AS nu_id_item,
 ITEM.art_descripcion AS no_nombre_item,
 PT.cantidad AS qt_cantidad,
 PT.precio AS ss_precio,
 ROUND(PT.importe, 2) AS ss_total
FROM
 " . $table_postransYM . " AS PT
 JOIN int_articulos AS ITEM
  ON(ITEM.art_codigo = PT.codigo)
 LEFT JOIN pos_historia_ladosxtrabajador AS PERASIG
  ON(PERASIG.ch_sucursal = PT.es AND PERASIG.dt_dia = PT.dia AND PERASIG.ch_posturno::TEXT = PT.turno AND PERASIG.ch_lado = PT.caja AND PERASIG.ch_tipo = PT.tipo)
 JOIN pla_ta_trabajadores AS TRABA
  USING(ch_codigo_trabajador)
WHERE
 PT.tipo = 'M'
 AND PT.dia BETWEEN '" . $dInicial . " 00:00:00' AND '" . $dFinal . " 00:00:00'
 " . $cond_almacen . "
 " . $cond_cliente . "
 " . $cond_item . "
ORDER BY
 PERASIG.ch_codigo_trabajador,
 PT.fecha 
 		";

		echo "<pre>";
		echo $sql;
		echo "</pre>";

		$iStatusSQL = $sqlca->query($sql);
		if ((int)$iStatusSQL < 0){
			return array(
		    	'sStatus' => 'danger',
		    	'sMessage' => 'Problemas al obtener datos <br>' . $sqlca->get_error(),
                'sMessageSQL' => $sqlca->get_error(),
                'SQL' => $sql,
		   	);
		}

    	if ($iStatusSQL == 0) {
			return array(
				'sStatus' => 'warning',
				'sMessage' => 'No hay registros',
			);
		}

    	return array(
    		'sStatus' => 'success',
    		'sMessage' => 'Registros Encontrados',
    		'arrData' => $sqlca->fetchAll()
    	);
	}
}