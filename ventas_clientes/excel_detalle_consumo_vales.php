<?php

include_once("/sistemaweb/include/libexcel/Workbook.php");
include_once("/sistemaweb/include/libexcel/Worksheet.php");

/*
 * Creado por Néstor Hernández Loli el 23/02/2012
 * para funcionalidad excel del detalle de consumo de vales
 */

/** 
 * Permite crear un excel en una ruta dada
 * @param type $res El resultado query de la consulta 
 */

function crearExcelDetalleConsumo($res, $ruta) {

	$libro = new Workbook($ruta);
	
	$formatoCabecera = &$libro->add_format();
	$formatoCabecera->set_size(11);
	$formatoCabecera->set_bold(1);
	$formatoCabecera->set_align('center');

	$formatoMoneda = &$libro->add_format();
	$formatoMoneda->set_num_format(2);
	$formatoMoneda->set_align("right");

	$formatoVisible = &$libro->add_format();
	$formatoVisible -> set_align("left");
	$formatoVisible->set_bold(1);

	$hoja = & $libro->add_worksheet('Hoja1');

	//Añadiendo columnas
	$hoja->set_column(0, 0, 14);
	$hoja->set_column(1, 1, 14);
	$hoja->set_column(2, 2, 14);
	$hoja->set_column(3, 3, 14);
	$hoja->set_column(4, 4, 14);
	$hoja->set_column(5, 5, 14);
	$hoja->set_column(6, 6, 12);
	$hoja->set_column(7, 7, 12);
	$hoja->set_column(8, 8, 25);
	$hoja->set_column(9, 9, 12);
	$hoja->set_column(10, 10, 12);
	$hoja->set_column(11, 11, 12);

	$hoja->set_zoom(100);
	$hoja->set_landscape(100);

	$cli = "";
	$totalCliente = 0;
	$totalGeneral = 0;
	$fila = 0;

	$nomcols = array("DESCRIPCION", "# LIQ", "# DESPACHO", "FECHA", "NUM DE VALES", "PLACA", "COMBUSTIBLE", "ODOMETRO", "USUARIO", "CANTIDAD", "PRECIO", "TOTAL");

	for ($i = 0; $i < sizeof($nomcols); $i++) {
		$hoja->write_string($fila, $i, $nomcols[$i], $formatoCabecera);
	}
	
	$fila+=1;

	for ($i = 0; $i < pg_numrows($res); $i++) {
		$a = pg_fetch_array($res, $i);
		if ($cli != $a["c_ch_cliente"]) {
			$cli = $a["c_ch_cliente"];
			$totalCliente = 0;
			//Combinando celdas
			//$hoja->merge_cells($fila, 0, $fila, 3);
			$hoja->write_string($fila, 0, "CLIENTE: " .  strtoupper($a["c_ch_cliente"]) ." " . $a["c_cli_razsocial"], $formatoCabecera);
			$fila+=1;
			//Las siguientes escrituras serán en la fila 3
			}

		$hoja->write_string($fila, 0, $a["c_ch_sucursal"] . " - " . $a["c_des_sucursal"]);
		$hoja->write_string($fila, 1, $a["c_ch_liquidacion"]);
		$hoja->write_string($fila, 2, $a["c_ch_documento"]);
		$hoja->write_string($fila, 3, $a["c_dt_fecha"]);
		$hoja->write_string($fila, 4, $a["c_vales"]);
		$hoja->write_string($fila, 5, $a["c_ch_placa"]);
		$hoja->write_string($fila, 6, $a["c_ch_articulo"]);
		$hoja->write_string($fila, 7, $a["c_nu_odometro"]);
		$hoja->write_string($fila, 8, strtoupper($a["c_chofer"]));
		$hoja->write_string($fila, 9, round($a["c_cantidad"], 3), $formatoMoneda);
		$hoja->write_string($fila, 10, round($a["c_precio"], 3), $formatoMoneda);
		$hoja->write_string($fila, 11, round($a["c_nu_importe"], 2), $formatoMoneda);
		$totalCliente += $a["c_nu_importe"];

		$fila+=1;

		if(pg_result($res, $i + 1, "c_ch_cliente") != $a["c_ch_cliente"]) {
			$totalGeneral += $totalCliente;
			//$hoja->merge_cells($fila, 0, $fila, 3);
			$hoja->write_string($fila, 0, "TOTAL: " . $totalCliente, $formatoVisible);
			$fila+=1;
		}
	}

	//Escribiendo el último lugar del documento excel
	//$hoja->merge_cells($fila + 1, 0, $fila + 1, 3);
	$hoja->write_string($fila + 1, 0, "TOTAL GENERAL: " . $totalGeneral, $formatoVisible);

	$libro->close();

	$cmd = "chmod 777 /sistemaweb/ventas_clientes/".$ruta;
	exec($cmd);
}
