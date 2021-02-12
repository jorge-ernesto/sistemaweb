<?php
include_once('../include/dbsqlca.php');
include_once('../include/documentos.inc.php');
include_once('../include/Classes/PHPExcel.php');
include("../functions.php");





/*
  $fecha_de = $diad.'-'.$mesd.'-'.$anod;
  $fecha_hasta = $diaa.'-'.$mesa.'-'.$anoa; */

$fecha_de="01-01-2012";
$fecha_hasta="01-09-2014";

$q3 = "	SELECT 
					cont.ch_numeroparte as parte, 
					cont.ch_codigocombustible, 
					cont.ch_tanque as tanque, 
					cont.ch_surtidor as manguera, 
					cont.nu_contometroinicialgalon, 
					cont.nu_contometrofinalgalon, 
					cont.nu_ventagalon, 
					cont.nu_contometroinicialvalor, 
					cont.nu_contometrofinalvalor, 
					cont.nu_ventavalor, 
					cont.nu_afericionveces_x_5, 
					cont.nu_consumogalon, 
					-cont.nu_descuentos, 
					comb.ch_nombrecombustible, 
					cont.dt_fechaparte, 
					cont.ch_responsable, 
					surt.ch_numerolado as lado	
				FROM 
					comb_ta_contometros cont
					LEFT JOIN comb_ta_surtidores surt ON (cont.ch_sucursal= surt.ch_sucursal and cont.ch_surtidor=surt.ch_surtidor)
					LEFT JOIN comb_ta_combustibles comb ON (cont.ch_codigocombustible=comb.ch_codigocombustible)				
				WHERE 				
					cont.dt_fechaparte >= to_date('$fecha_de','DD-MM-YYYY')	
					and cont.dt_fechaparte <=to_date('$fecha_hasta','DD-MM-YYYY')
					
				ORDER BY 
					parte,
					lado,
					manguera,
					tanque";
//echo $q3;
$rs3 = pg_exec($q3);
var_dump($rs3);

$q4 = "SELECT
					C.codigo as codigo,
					COMB.descripcion as descripcion,
					ROUND(COMB.total_cantidad,3) as total_cantidad,
					ROUND(COMB.total_venta,2) as total_venta,
					AFC.af_cantidad as af_cantidad,
					AFC.af_total as af_total,
					'0.000' as consumo_galon,
					'0.000' as consumo_valor,
					COMB.descuentos as descuentos,
					CASE WHEN AFC.af_cantidad IS NULL THEN COMB.total_cantidad ELSE COMB.total_cantidad - AFC.af_cantidad END as resumen,
					CASE WHEN AFC.af_cantidad IS NULL THEN (COMB.total_venta + COMB.descuentos) ELSE ((COMB.total_venta + COMB.descuentos) - AFC.af_total) END as neto_soles				
				FROM

					(SELECT ch_codigocombustible AS codigo FROM comb_ta_combustibles) C

					INNER JOIN 

					(SELECT
						comb.ch_codigocombustible AS codigo,
						cmb.ch_nombrecombustible AS descripcion,
						SUM(CASE WHEN comb.nu_ventagalon!=0 THEN comb.nu_ventavalor ELSE 0 END) AS total_venta,
						SUM(CASE WHEN comb.nu_ventagalon!=0 THEN comb.nu_ventagalon ELSE 0 END) AS total_cantidad,
						ROUND(SUM(comb.nu_descuentos),2) AS descuentos
					 FROM 
						comb_ta_contometros comb
						LEFT JOIN comb_ta_combustibles cmb ON (comb.ch_codigocombustible = cmb.ch_codigocombustible)
					 WHERE 	
						comb.ch_sucursal = '$almacen' 
						and comb.dt_fechaparte BETWEEN to_date('$fecha_de', 'DD/MM/YYYY') and to_date('$fecha_hasta', 'DD/MM/YYYY')
					GROUP BY 
						comb.ch_codigocombustible,
						cmb.ch_nombrecombustible
					) COMB on COMB.codigo = C.codigo
	
					LEFT JOIN

					(SELECT 
						af.codigo as codigo,
						SUM(af.importe) AS af_total,
						ROUND(SUM(af.cantidad), 3) AS af_cantidad
					FROM 
						pos_ta_afericiones af
					WHERE
						af.es = '$almacen' 
						AND af.dia BETWEEN to_date('$fecha_de', 'DD/MM/YYYY') and to_date('$fecha_hasta', 'DD/MM/YYYY')
					GROUP BY
						af.codigo
					)AFC ON AFC.codigo = C.codigo
				;";


$rs4 = pg_exec($q4);



pg_close();
?>

?>