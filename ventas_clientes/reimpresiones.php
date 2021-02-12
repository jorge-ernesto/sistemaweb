<?php
include("/sistemaweb/valida_sess.php");
include("config.php");
include("../functions.php");

	$sql = "select ch_nombre_sucursal from int_ta_sucursales where ch_sucursal='$almacen'";
	$estacion = pg_result(pg_query($sql),0,0);

	function calcularIGV($importe) {
		$sql = "select UTIL_FN_IGV()";
		$igv = pg_result(pg_query($sql),0,0);
		$igv_calculado = ($importe-($importe/(($igv/100)+1)));
		return round($igv_calculado,2);
	}

	$sql = "select now(), to_char(now(), 'DD/MM/YYYY HH:mi')";
	$f_fecha = pg_result(pg_query($sql),0,1);
	$fecha = pg_result(pg_query($sql),0,0);
	$dia_trans=$_REQUEST['dia_trans'];
	$periodo = substr($dia_trans,6,4);
	$mes_trans = substr($dia_trans,3,2);
	$dia = substr($dia_trans,0,2);
	//list($periodo, $mes_trans, $dia) = sscanf($_REQUEST['dia_trans'], "%4s-%2s-%2s");

	if ($_REQUEST['tipo_consulta'] == 'actual')
		$tabla = "pos_transtmp";
	else
		$tabla = "pos_trans".$periodo.$mes_trans;

	$sql = "select caja, to_char(fecha, 'DD/MM/YYYY HH:mi') from " . pg_escape_string($tabla) . " where caja='" . $_REQUEST['nro_caja'] . "' and trans='" . $_REQUEST['nro_trans'] . "' and to_char(fecha, 'DD/MM/YYYY')='" . $_REQUEST['dia_trans'] . "' ";
	$rs = pg_query($sql);
	$nro_caja = pg_result($rs,0,0);
	$f_fecha = pg_result($rs,0,1);

	$directorio = "/sistemaweb/ventas_clientes";
	$archivo= "ticket_reimpresion.txt";
	$ancho = 65;

	$ft=fopen($archivo,'w');

	if ($ft>0) {
		$buffer_cabecera=$estacion."\n";
		$buffer_cabecera=$buffer_cabecera.$f_fecha."   TRANS. No. ".$nro_trans." / ".$nro_caja."\n";
		$buffer_cabecera=$buffer_cabecera.str_pad("-",$ancho,"-",STR_PAD_BOTH)."\n";
	}

	echo '<a href="/sistemaweb/utils/impresiones.php?imprimir=ok&archivo=/sistemaweb/ventas_clientes/ticket_reimpresion.txt" target="_blank">Imprimir Texto</a>'."<br>";
	echo $estacion."<br>";
	echo $f_fecha."   TRANS. No. ".$nro_trans." / ".$nro_caja."<br>";
	echo str_pad("-",60,"-",STR_PAD_BOTH)."<br>";

	$sql = "select trim(tipo) as tipo,
			codigo, art_descbreve as descripcion
			, round(cantidad,0) as cantidad
			, round(importe,2) as importe
			, pump , precio
		from " . pg_escape_string($tabla) . " trans, int_articulos art
		where
			trans.codigo=art.art_codigo
			and trim(caja)='$nro_caja'
			and trans='".trim($nro_trans)."'
			and to_char(fecha,'DD/MM/YYYY')='" . $_REQUEST['dia_trans'] . "' ";

	$xsql  = pg_query($sql);

	if(pg_result($xsql,0,0)=="M") {
		$i = 0;
		while($i<pg_num_rows($xsql)) {
			$rs = pg_fetch_array($xsql, $i);
			echo $rs['codigo']." ".str_pad($rs['descripcion'],30,"-",STR_PAD_RIGHT);
			echo "x".str_pad($rs['cantidad'],4,"-",STR_PAD_LEFT );
			echo str_pad($rs['importe'],12,"-",STR_PAD_LEFT )."<br>";

			$buffer_cuerpo=$buffer_cuerpo.$rs['codigo']." ".str_pad($rs['descripcion'],30," ",STR_PAD_RIGHT);
			$buffer_cuerpo=$buffer_cuerpo."x".str_pad($rs['cantidad'],4," ",STR_PAD_LEFT);
			$buffer_cuerpo=$buffer_cuerpo.str_pad($rs['importe'],12," ",STR_PAD_LEFT)."\n";
	
			$total_importe += $rs['importe'];
			$i++;
		}
	} else {
			$rs = pg_fetch_array($xsql, 0);
			echo "POSICION : ".$rs['pump']." ".str_pad(" COMBUSTIBLE ".$rs['descripcion'],30,"-",STR_PAD_RIGHT)."<br>";	
			echo str_pad(round(($rs['importe']/$rs['precio']),3)." GL. @ ".$rs['precio']." S/. Gl",40,"-",STR_PAD_RIGHT);
			echo str_pad($rs['importe'],12," ",STR_PAD_LEFT )."<br>";
			$buffer_cuerpo=$buffer_cuerpo."POSICION  :  ".$rs['pump']." ".str_pad("   COMBUSTIBLE  ".$rs['descripcion'],30," ",STR_PAD_RIGHT)."\n";
			$buffer_cuerpo=$buffer_cuerpo.str_pad(round(($rs['importe']/$rs['precio']),3)." GL.  @   ".$rs['precio']."  S/. Gl",49," ",STR_PAD_RIGHT);
			$buffer_cuerpo=$buffer_cuerpo.str_pad($rs['importe'],12," ",STR_PAD_LEFT )."\n";
			$total_importe += $rs['importe'];
			$i++;
	}

	echo str_pad(" TOTAL INC. IMP. ",49," ",STR_PAD_LEFT);
	echo str_pad(number_format($total_importe,2),12," ",STR_PAD_LEFT )."<br>";
	echo str_pad(" IMPUESTO ",49," ",STR_PAD_LEFT);
	echo str_pad(number_format(calcularIGV($total_importe),2),12," ",STR_PAD_LEFT )."<br>";
	echo str_pad("=",60,"=",STR_PAD_BOTH)."<br>";
	echo "<br>";

	$buffer_cuerpo=$buffer_cuerpo.str_pad(" TOTAL INC. IMP. ",49," ",STR_PAD_LEFT);
	$buffer_cuerpo=$buffer_cuerpo.str_pad(number_format($total_importe,2),12," ",STR_PAD_LEFT )."\n";
	$buffer_cuerpo=$buffer_cuerpo.str_pad(" IMPUESTO ",49," ",STR_PAD_LEFT);
	$buffer_cuerpo=$buffer_cuerpo.str_pad(number_format(calcularIGV($total_importe),2),12," ",STR_PAD_LEFT )."\n";
	$buffer_cuerpo=$buffer_cuerpo.str_pad("=",$ancho,"=",STR_PAD_BOTH)."\n";
	$buffer_cuerpo=$buffer_cuerpo."\n";

	fwrite($ft, $buffer_cabecera.$buffer_cuerpo);
	fclose($ft);

