<?php

class DifInvModel extends Model
{
    function search($periodo, $almacenes)
    {
	global $sqlca;

	list($mes, $ano) = sscanf($periodo, "%2s/%4s");

	$mes = pg_escape_string($mes);

	$sql = "SELECT
		    s.stk_stock" . $mes . ",
		    s.stk_fisico" . $mes . ",
		    (s.stk_fisico" . $mes . "-s.stk_stock" . $mes . ") as stk_diferencia,
		    round((s.stk_fisico" . $mes . "*s.stk_costo" . $mes . "),2) as stk_importe_fisico,
		    round((s.stk_stock" . $mes . "*s.stk_costo" . $mes . "),2) as stk_importe_stock,
		    round(((s.stk_fisico" . $mes . "-s.stk_stock" . $mes . ")*s.stk_costo" . $mes . "),2) as stk_importe_diferencia,
		    s.stk_costo" . pg_escape_string($mes) . ",
		    a.art_codigo,
		    a.art_descripcion,
		    s.stk_almacen,
		    w.ch_nombre_almacen
		FROM
		    inv_saldoalma s,
		    int_articulos a,
		    inv_ta_almacenes w
		WHERE
			s.art_codigo=a.art_codigo
		    AND a.art_plutipo in ('1', '4')
		    AND w.ch_almacen=s.stk_almacen
		    AND s.stk_periodo='" . pg_escape_string($ano) . "'
		    AND s.stk_fisico" . pg_escape_string($mes) . "!=s.stk_stock" . pg_escape_string($mes) . "
		";

	/*if ($almacenes[0] != "TODAS") {
	    $sql .= "	AND s.stk_almacen in (";
	    for ($i = 0; $i < count($almacenes); $i++) {
		if ($i > 0) $sql .= ",";
		$sql .= "'" . pg_escape_string($almacenes[$i]) . "'";
	    }
	    $sql .= ") ";
	}*/
	if ($almacenes!='TODAS') $sql .= " and s.stk_almacen = '".$almacenes."'";
	
	$sql .= "
		ORDER BY
		    s.stk_almacen,
		    a.art_codigo
		;";

	if ($sqlca->query($sql) < 0) return null;
	
	$resultado = Array();

	for ($i = 0; $i < $sqlca->numrows(); $i++) {
	    $a = $sqlca->fetchRow();
	    
	    $stk_stock = $a[0];
	    $stk_fisico = $a[1];
	    $stk_diferencia = $a[2];
	    $stk_importe_fisico = $a[3];
	    $stk_importe_stock = $a[4];
	    $stk_importe_diferencia = $a[5];
	    $stk_costo = $a[6];
	    $art_codigo = $a[7];
	    $art_descripcion = $a[8];
	    $stk_almacen = $a[9];
	    $ch_nombre_almacen = $a[10];

	    $resultado[$i]['stk_stock'] = $stk_stock;
	    $resultado[$i]['stk_fisico'] = $stk_fisico;
	    $resultado[$i]['stk_diferencia'] = $stk_diferencia;
	    $resultado[$i]['stk_importe_fisico'] = $stk_importe_fisico;
	    $resultado[$i]['stk_importe_stock'] = $stk_importe_stock;
	    $resultado[$i]['stk_importe_diferencia'] = $stk_importe_diferencia;
	    $resultado[$i]['stk_costo'] = $stk_costo;
	    $resultado[$i]['art_codigo'] = $art_codigo;
	    $resultado[$i]['art_descripcion'] = $art_descripcion;
	    $resultado[$i]['stk_almacen'] = $stk_almacen;
	    $resultado[$i]['ch_nombre_almacen'] = $ch_nombre_almacen;
	    $resultado[$i]['stk_periodo'] = $periodo;
	}
	
	return $resultado;
    }
    
	
	
	
 /* ------------- FUNCION PARA IMPORTAR LOS STOCKS DE LOS GRIFOS A SALDOS  --------------*/
    function importarStocks($estaciones, $fecha) {
/*---------------------------------------------------------------------------------------*/	
	
	/* CONEXIONES PARA BASE DE DATOS integrado Y sistemaweb REPLICACION*/
	$sqlca = new pgsqlDB("localhost", "postgres", "postgres", "sistemaweb_replicacion");
	$sqlca2 = new pgsqlDB("localhost", "postgres", "postgres", "integrado");

	/*EN VARIABLES SEPARADAS OBTIENE DE FECHA EL DD MM AA AA */
	list($dia, $mes, $dummy, $ano) = sscanf($fecha, "%2s/%2s/%2s%2s");
	
	/* SELECIONA LOS IP Y CODIGO DE ALMACEN(ES) DE BD sistemaweb_REPLICACION*/
	$sql = "SELECT
		    ip_estacion,
		    cod_estacion
		FROM
		    sistemaweb_estaciones
		";

	if ($estaciones[0] != "TODAS") {
	    $sql .= "WHERE
			cod_estacion in (";
	    for ($i = 0; $i < count($estaciones); $i++) {
		if ($i > 0) $sql .= ",";
		$sql .= "'" . pg_escape_string($estaciones[$i]) . "'";
	    }
	    $sql .= ")";
	 }
	
	$sql .= "
		;
		";
		
	if ($sqlca->query($sql) < 0) return false;


	$result = array();
	$fecha = $dia.$mes.$ano;

	$sql = "BEGIN;";
	$sqlca2->query($sql);

    /* ACTUALIZA STOCKS FISICO DE ALMACEN(ES) EN CERO */
	$sql = "UPDATE
		    inv_saldoalma
		SET
		    stk_fisico" . pg_escape_string($mes) . "='0'
		WHERE
			stk_periodo='" . pg_escape_string($dummy.$ano) . "'
		";

	if ($estaciones[0] != "TODAS") {
	    $sql .= "	AND stk_almacen in (";
	    for ($i = 0; $i < count($estaciones); $i++) {
		if	 ($i > 0) $sql .= ",";
		$sql .= "'" . pg_escape_string(str_pad($estaciones[$i], 3, "0", STR_PAD_LEFT)) . "'";
	    }
	    $sql .= ")";
	 }
	
	$sql .= ";";
	
	echo $sql;

	if ($sqlca2->query($sql) < 0) {
	    $sql = "ROLLBACK;";
	    $sqlca2->query($sql);
	    return false;
	}

    /* CREA TEMPORAL PARA COPIAR STOCKS DE TEXTO DE GRIFO */
	$sql = 'CREATE TEMPORARY TABLE
		    "tmp_importar_stock"
			(
			    "art_codigo" character(13) ,
			     "stk_fisico" numeric(15,4),
			     "ch_almacen" character(3) ,
				 constraint tmp_importar_stockpk primary key (art_codigo, ch_almacen) 
			)
		;
		';
		echo $sql;
	if ($sqlca2->query($sql) < 0) {
	    $sql = "ROLLBACK;";
	    $sqlca2->query($sql);
	    return false;
	}

    /* PARA TODOS LOS ALMACENES SELECCIONADOS */
	for ($i = 0; $i < $sqlca->numrows(); $i++) {
	    $a = $sqlca->fetchRow();
	    $command = "wget -t 2 -N -c -P /sistemaweb/tmp/stocks http://" . $a[0] . "/sistemaweb/tmp/stocks/" . str_pad($a[1], 2, "0", STR_PAD_LEFT) . $fecha  . ".stk";
	    
	    $ret = 0;
	    $dummy2 = Array();
	    
	    echo "Comando " . $command . "\n";
	    exec($command, $dummy2, $ret);
	    
//	    var_dump($dummy2);
	    
	    //echo "inicio de archivo....\n";
	    //file("/sistemaweb/tmp/stocks/" . str_pad($a[1], 2, "0", STR_PAD_LEFT) . $fecha . ".stk");
	    //echo "fin de archivo...\n";
	    $result[$a[1]] = $ret;
	    
	    if ($ret == 0) {
		$sql = "COPY
			    tmp_importar_stock
			FROM 
			    '/sistemaweb/tmp/stocks/" . str_pad($a[1], 2, "0", STR_PAD_LEFT) . $fecha  . ".stk'
			WITH
			    DELIMITER ','
			;
			";
			echo $sql;
		if ($sqlca2->query($sql) < 0) {
		    $result[$a[1]] = -1;
		    $sql = "ROLLBACK;";
		    $sqlca2->query($sql);
		    return false;
		}
					    
	    }
	    else
	    {
		$sql = "ROLLBACK;";
		$sqlca2->query($sql);
		return false;
	    }
	    
	}

/*	$sql = "ROLLBACK;";
	$sqlca2->query($sql);
	return false;*/


     /*  ELIMINA TODOS LOS REGISTROS SIN STOCK */	
	$sql = "DELETE FROM
		    tmp_importar_stock
   		WHERE
		    stk_fisico='0'
		;
		";
	if ($sqlca2->query($sql) < 0) {
	    $sql = "ROLLBACK;";
	    $sqlca2->query($sql);
	    return false;
	}
	
	
	$sql = "UPDATE inv_saldoalma  
		    SET stk_fisico" . $mes . "= tmp_importar_stock.stk_fisico
 		    WHERE inv_saldoalma.stk_almacen=tmp_importar_stock.ch_almacen
		      AND inv_saldoalma.stk_periodo='" . pg_escape_string($dummy.$ano) . "'
		      AND inv_saldoalma.art_codigo=tmp_importar_stock.art_codigo
		;
		";
		
	if ($sqlca2->query($sql) < 0) {
	    $sql = "ROLLBACK;";
	    $sqlca2->query($sql);
	    return false;
	}
	
	$sql = "COMMIT;";
	$sqlca2->query($sql);
	/*
    $sql = "SELECT
		    art_codigo,
		    stk_fisico,
		    ch_almacen
		FROM
		    tmp_importar_stock order by art_codigo
		;
		";
	
	if ($sqlca2->query($sql) < 0) return false;
	
	echo "resultados:" . $sqlca2->numrows();
	for ($i = 0; $i < $sqlca2->numrows(); $i++) {
	    $a = $sqlca2->fetchRow();
	    $art_codigo = $a[0];
	    $stk_fisico = $a[1];
	    $ch_almacen = $a[2];
	    
	    $sql = "UPDATE
			inv_saldoalma
		    SET
			stk_fisico" . $mes . "='" . pg_escape_string($stk_fisico) . "'
		    WHERE
			    stk_almacen='" . pg_escape_string($ch_almacen) . "'
			AND stk_periodo='" . pg_escape_string($dummy.$ano) . "'
			AND art_codigo='" . pg_escape_string($art_codigo) . "'
		    ;";
	    $sqlca2->query($sql, "_import");
   //	    echo $sql;
	} */
	
	return $result;
    }
}

