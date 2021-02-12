<?php
//include("../menu_princ.php");
include("../functions.php");
//include("js/funciones.php");
require("../clases/funciones.php");
//include("js/inv_addmov_support.php");
include("navsat_functions.php");

$funcion = new class_funciones;

$clase_error = new OpensoftError;
// conectar con la base de datos
$coneccion=$funcion->conectar("","","","","");
/*	
*
*	VERIFICAR ...!!
*	1.- que el siguiente query no debe tener resultado... en caso tenga algun resultado se debe actualizar el Costo Unitario(art_costoreposicion)
*		con su costo real (Generalmente se actualiza cuando se hace un Ingreso por Compras...
*
*		select art_codigo, trim(art_descripcion), art_costoinicial,art_costoactual, art_costoreposicion from int_articulos where trim(art_linea)='000072' and cast(art_costoreposicion as numeric)<=0;
*
*	2.- En la tabla INT_PARAMETROS debe registrar.: 
*			     par_nombre		|	  par_valor
*			--------------------+------------------
*			  prov_tarj_virtual	|    00N191   <codigo proveedor navsat>
*			  codes				|    014      <codigo de estacion>
*/

/*
*	VARIABLES QUE DEBE PASAR MIGUEL CON EL SISTEMA CAPTURADOR...
*	Opcionalmente puede pasar una fecha del $dia a importar en el formato YYYY-MM-DD 
*
*	ejm.:
*	inv_generar_navsat.php?serie_docu_ref=005&num_docu_ref=0001415&nro_lote=0100155406&dia=2004-10-08;
*
*	$serie_docu_ref='005';
*	$num_docu_ref='0001415';
*	$dia = '2004-10-07';
*   $nro_lote = '0100155406';
*/

if(strlen(trim($serie_docu_ref))>0 && strlen(trim($num_docu_ref))>0)
{
	if(strlen(trim($dia))==0){
		$sql_fec = "select to_char(da_fecha,'YYYY-MM-DD') from pos_aprosys where ch_poscd!='A' order by da_fecha desc limit 1";
		$dia = pg_result(pg_query($coneccion, $sql_fec),0,0);
	}
	//echo $dia;
	GenerarNavsat($coneccion, $serie_docu_ref, $num_docu_ref, $dia, $nro_lote);
}
