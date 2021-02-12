 <?php
include "../valida_sess.php";
include "../config.php";
include "include/reportes.inc.php";

$reporte = new CReportes(5, 'P');


for ($liquidacion = $_REQUEST['desde']; $liquidacion <= $_REQUEST['hasta']; $liquidacion++) {


    $sql = "SELECT
		val_cab.ch_documento,
		val_cab.ch_cliente,
		clie.Cli_RazSocial,
		val_cab.dt_fecha,
		val_cab.nu_importe,
		val_det.ch_articulo,
		val_det.nu_cantidad,
		val_det.nu_importe as art_importe,
		art.art_descripcion
	    FROM
		val_ta_cabecera val_cab,
		val_ta_detalle val_det,
		int_articulos art,
		int_clientes clie
	    WHERE
		    val_cab.ch_liquidacion='" . pg_escape_string(str_pad($liquidacion, 10, "0", STR_PAD_LEFT)) . "'
		AND val_det.ch_sucursal=val_cab.ch_sucursal	
		AND val_det.dt_fecha=val_cab.dt_fecha
		AND val_det.ch_documento=val_cab.ch_documento
		AND art.Art_Codigo=val_det.ch_articulo
		AND clie.Cli_Codigo=val_cab.ch_cliente 
            ORDER BY clie.Cli_RazSocial, val_cab.ch_documento;";
//echo $sql;
    $rs = pg_query($sql);

    $sql = "SELECT ".
		  "ch_liquidacion, ".
		  "substr(ch_fac_seriedocumento,0,4)||'-'||lpad(trim(ch_fac_numerodocumento),7,'0') as nro_factura ".
	   "FROM ".
		 "fac_ta_factura_cabecera ".
	   "WHERE ".
		 "ch_liquidacion='" . pg_escape_string(str_pad($liquidacion, 10, "0", STR_PAD_LEFT)) . "'";
		 
    pg_numrows(pg_query($sql)) == 0 ? $dato_adi = " - pago adelantado" : $dato_adi = " ";
    $rs2 = pg_query($sql);
    $cli_codigo = pg_result($rs, 0, 1);
    $cli_descrip = pg_result($rs, 0, 2);
    $cabecera = Array(
	0	=>	Array(
				'texto'	=>	"LIQUIDACION DE FACTURAS",
				'estilo'=>	"C"
			),
	1	=>	Array(
				'texto' =>	"%f",
				'estilo'=>	"R"
			),
	2	=>	Array(
				'texto' =>	"Nro. Liquidacion: ".$liquidacion.$dato_adi."                                        Nro. Factura : ".pg_result($rs2, 0, 1)." ",
				'estilo'=>	"L"
			),
	3	=>	Array(
				'texto' =>	"Cliente: " . $cli_codigo . " " . $cli_descrip,
				'estilo'=>	"L"
			)
	);

    if (pg_numrows($rs) > 0) {
	$reporte->nuevoInforme(5);
	$reporte->definirColumna(0, "FECHA CONSUMO", $reporte->TIPO_STRING, 13);
	$reporte->definirColumna(1, "# VALE", $reporte->TIPO_STRING, 15);
	$reporte->definirColumna(2, "Articulo", $reporte->TIPO_STRING, 15);
	$reporte->definirColumna(3, "Descripcion", $reporte->TIPO_STRING, 30, "L");
	$reporte->definirColumna(4, "IMPORTE", $reporte->TIPO_STRING, 15);

	$reporte->ponerCabecera($cabecera);
    
	$val_num = "";
	$total = 0;
    }

    for ($i = 0; $i < pg_numrows($rs); $i++) {
	$A = pg_fetch_array($rs, $i);
	if($cli_codigo != $A[1]){
	   $cli_codigo_2 = $A[1];
	   $reporte->AddPage();
	 }
	
	$reporte->irFila($reporte->agregarFila());
	
	$reporte->poneValor($A[3], 0);
	$reporte->poneValor($A[0], 1);
	$reporte->poneValor($A[5], 2);
	$reporte->poneValor($A[8], 3);
	$reporte->poneValor($A[7], 4);
	
	$total += $A[7];
    }
    
    if($cli_codigo_2!='')
    //$reporte->AddPage();
      //print_r($cli_codigo_2);
    
    if (pg_numrows($rs) > 0) {
	$reporte->irFila($reporte->agregarFila());
	$reporte->poneValor("Total transacciones: " . pg_numrows($rs) . "  Total: ",3);
	$reporte->poneValor($total, 4);
	$reporte->generar();
    }
    
}

$reporte->mostrar();
