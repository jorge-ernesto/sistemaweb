<?php
include "../valida_sess.php";
include "../config.php";
include "store_procedures.php";
include "include/fpdf.php";
include "/sistemaweb/include/reportes2.inc.php";

$hoy = date("d/m/Y");
if ($_REQUEST['c_fec_desde'] == "") $desde = $hoy; else $desde = $_REQUEST['c_fec_desde'];
if ($_REQUEST['c_fec_hasta'] == "") $hasta = $hoy; else $hasta = $_REQUEST['c_fec_hasta'];

$rs_todas = REPORTE_CUADRE_FACTURACION_1($desde, $hasta, "TODAS");
$rs_anticipados = REPORTE_CUADRE_FACTURACION_1($desde, $hasta, "ANTICIPADOS");
$rs_facturas_contado = REPORTE_CUADRE_FACTURACION_1($desde, $hasta, "FACTURAS_CONTADO");
$rs_vales_no_facturados = REPORTE_CUADRE_FACTURACION_1($desde, $hasta, "VALES_NO_FACTURADOS");
$rs_vales_liquidados_no_facturados = REPORTE_CUADRE_FACTURACION_1($desde, $hasta, "VALES_LIQUIDADOS_NO_FACTURADOS");
$rs_vales_pago_adelantado = REPORTE_CUADRE_FACTURACION_1($desde, $hasta, "VALES_PAGO_ADELANTADO");
$rs_cuenta_manteminiento = REPORTE_CUADRE_FACTURACION_1($desde, $hasta, "CUENTA_MANTENIMIENTO");
$rs_ventas_credito_det = REPORTE_CUADRE_FACTURACION_2($desde, $hasta, "DETALLADO");

$rs_ventas_credito_res =  pg_query("SELECT
				    	round(SUM(importe_castilla),2)     as importe_castilla,
			            round(SUM(importe_magdalena),2)   as importe_magdalena,
					    round(SUM(importe_faucet),2)      as importe_faucet,
					    round(SUM(importe_brena),2)       as importe_brena,
					    round(SUM(importe_perla),2)       as importe_perla,
					    round(SUM(importe_sucre),2)       as importe_sucre,
					    round(SUM(importe_risso),2)       as importe_risso,
				    	round(SUM(importe_orrantia),2)    as importe_orrantia
				           FROM
					    TMP_VENTAS_FN_REPORTE_CUADRE_FACTURACION_22
					  ");

$fontsize = 7;
$reporte = new CReportes2("P");
$reporte->SetMargins(5, 5, 5);
$reporte->SetFont("courier", "", $fontsize);

if (true)
{

	/*********************** INICIO DEL INFORME DE TODOS DOCUMENTOS ***********************/
	$Cabecera = array( 
		"CLIENTES"	=> "CLIENTES",
		"FACTURAS"	=> "FACTURAS",
		"IMPORTES"	=> "IMPORTES",
		"VALES"		=> "VALES",
		"MANT"		=> "CTA. MANT.",
		"TOTAL"		=> "TOTAL",
		"DIF1"		=> "DIFERENCIA",
		"DIF2"		=> "DIFERENCIA"
		);
    $reporte->definirColumna("CLIENTES", $reporte->TIPO_TEXTO, 40, "L");
    $reporte->definirColumna("FACTURAS", $reporte->TIPO_TEXTO, 10, "R");
    $reporte->definirColumna("IMPORTES", $reporte->TIPO_IMPORTE, 12, "R");
    $reporte->definirColumna("VALES"   , $reporte->TIPO_IMPORTE, 12, "R");
    $reporte->definirColumna("MANT"    , $reporte->TIPO_IMPORTE, 12, "R");
    $reporte->definirColumna("TOTAL"   , $reporte->TIPO_IMPORTE, 12, "R");
    $reporte->definirColumna("DIF1"    , $reporte->TIPO_IMPORTE, 12, "R");
    $reporte->definirColumna("DIF2"    , $reporte->TIPO_IMPORTE, 13, "R");
    $reporte->definirCabecera(1, "L", "OFICINA CENTRAL");
    $reporte->definirCabecera(1, "C", "Reporte de Cuadre de facturacion - Todos los documentos");
    $reporte->definirCabecera(1, "R", "PAG.%p");
    $reporte->definirCabecera(2, "R", "%f");
    $reporte->definirCabecera(3, "R", " ");
    $reporte->definirCabeceraPredeterminada($Cabecera);
    $total_datos = array();
    $total_columna_todo = array();
    $total_columna_todo_anti = array();
	$ar_todas=array();
	$ar_todas=pg_fetch_all($rs_todas);
    $reporte->AddPage();
    $reporte->Ln();
    foreach($ar_todas as $reg => $valores)
    {
		echo "valores ".$valores."<br>";
		foreach($valores as $key => $value)
		{
			echo "clave ".$key."<br>";
			echo "valor ".$value."<br>";
			$total_datos[''.strtoupper($key).''] = $value;
			
			if(strtoupper($key) == 'IMPORTES'
				or strtoupper($key) == 'VALES'
				or strtoupper($key) == 'MANT'
				or strtoupper($key) == 'TOTAL'
				or strtoupper($key) == 'DIF1'
				or strtoupper($key) == 'DIF2'
			) 	{
				$total_columna_todo[''.strtoupper($key).''] += $value;
				$total_columna_todo_anti[''.strtoupper($key).''] += $value;
				}
			else 
				{
				$total_columna_todo[''.strtoupper($key).''] = " ";
				$total_columna_todo_anti[''.strtoupper($key).''] = " ";				
				}
			
		}
		$reporte->nuevaFila($total_datos);
		
    }
    $reporte->Ln();
    $reporte->lineaH();
    $reporte->Ln();
	$reporte->nuevaFila($total_columna_todo);
    $reporte->Ln();
    // return '<iframe src="/sistemaweb/reportes/cuadre.pdf" width="900" height="300"></iframe>';
	/******************************* FIN DEL INFORME DE TODOS DOCUMENTOS *****************************/
	
	
}	
	
	/*********************** INICIO DEL INFORME DE ANTICIPOS ***********************/
	if (pg_numrows($rs_anticipados) > 0) 
		{
		$Cabecera = array( 
			"CLIENTES"	=> "CLIENTES",
			"FACTURAS"	=> "FACTURAS",
			"VALES"	=> "VALES"
			);
		$reporte->definirColumna("CLIENTES", $reporte->TIPO_TEXTO, 40, "L");
		$reporte->definirColumna("FACTURAS", $reporte->TIPO_TEXTO, 10, "R");
		$reporte->definirColumna("VALES", $reporte->TIPO_IMPORTE, 12, "R");
		$reporte->definirCabecera(1, "L", "OFICINA CENTRAL");
		$reporte->definirCabecera(1, "C", "Reporte de Cuadre de Facturacion - Anticipos");
		$reporte->definirCabecera(1, "R", "PAG.%p");
		$reporte->definirCabecera(2, "R", "%f");
		$reporte->definirCabecera(3, "R", " ");
		$reporte->definirCabeceraPredeterminada($Cabecera);
		$total_datos = array();
	    $total_columna_anti = array();
		$ar_todas=array();
		$ar_todas=pg_fetch_all($rs_anticipados);
		$reporte->AddPage();
		$reporte->Ln();
		foreach($ar_todas as $reg => $valores)
		{
			foreach($valores as $key => $value)
			{
				$total_datos[''.strtoupper($key).''] = $value;
			
				if(strtoupper($key) == 'VALES'
				) 	{
					$total_columna_anti[''.strtoupper($key).''] += $value;
					$total_columna_todo_anti[''.strtoupper($key).''] += $value;
					}
				else {
					$total_columna_anti[''.strtoupper($key).''] = " ";
					$total_columna_todo_anti[''.strtoupper($key).''] = " ";	
					}

			}
			$reporte->nuevaFila($total_datos);
		}
		$reporte->Ln();
		$reporte->lineaH();
		$reporte->Ln();
		$reporte->nuevaFila($total_columna_anti);
		$reporte->Ln();
		$reporte->lineaH();
		$reporte->Ln();
		$reporte->nuevaFila($total_columna_todo_anti);
		$reporte->Ln();

		}
	/******************************* FIN DEL INFORME DE ANTICIPOS *****************************/

    $total_columna_conc = array();

	/******************************** INICIO DEL INFORME DE CONTADO *************************/
	if (pg_numrows($rs_facturas_contado) > 0) 
	{
		$reporte->templates = Array();
		$reporte->cabecera = Array();
		
		$reporte->cabeceraImagen = Array();
		
		$reporte->cabeceraSize = Array();
	    $reporte->cab_default = Array();

		$Cabecera = array( 
			"CLIENTES"	=> "CLIENTES",
			"FACTURAS"	=> "FACTURAS",
			"IMPORTES"	=> "IMPORTES",
			"ANTICIPADO"=> "ANTICIPADO",
			);
		$reporte->definirColumna("CLIENTES", $reporte->TIPO_TEXTO, 40, "L");
		$reporte->definirColumna("FACTURAS", $reporte->TIPO_TEXTO, 10, "R");
		$reporte->definirColumna("IMPORTES", $reporte->TIPO_IMPORTE, 12, "R");
		$reporte->definirColumna("ANTICIPADO", $reporte->TIPO_TEXTO, 12, "C");
				
		$reporte->definirCabecera(1, "L", "OFICINA CENTRAL");
		$reporte->definirCabecera(1, "C", "CONCILIACION - Contado Whiz y Otros");
		$reporte->definirCabecera(1, "R", "PAG.%p");
		$reporte->definirCabecera(2, "R", "%f");
		$reporte->definirCabecera(3, "R", " ");
		$reporte->definirCabeceraPredeterminada($Cabecera);
		
		$total_datos = array();
	    $total_columna_conc_cont = array();
		$ar_todas=array();
		$ar_todas=pg_fetch_all($rs_facturas_contado);
		echo "IMPRIMIR PDF";
		$reporte->AddPage();
		$reporte->Ln();
		foreach($ar_todas as $reg => $valores)
		{
			foreach($valores as $key => $value)
			{
				$total_datos[''.strtoupper($key).''] = $value;
			
				if(strtoupper($key) == 'IMPORTES'	)
				 	{
					$total_columna_conc_cont[''.strtoupper($key).''] += $value;
					$total_columna_conc[''.strtoupper($key).''] += $value;
					}
				else {
					$total_columna_conc_cont[''.strtoupper($key).''] = " ";
					$total_columna_conc[''.strtoupper($key).''] = " ";	
					}

			}
			$reporte->nuevaFila($total_datos);
		}
		
		$reporte->Ln();
		$reporte->lineaH();
		$reporte->Ln();
		$reporte->nuevaFila($total_columna_conc_cont);
		$reporte->Ln();
		$reporte->lineaH();
		$reporte->Ln();
		$reporte->nuevaFila($total_columna_conc);
		$reporte->Ln();
		
		
		}
	/************************************ FIN DEL INFORME DE CONTADO ***********************/





	/**************************** INICIO DEL INFORME DE VALES NO FACTURADOS ***********************/
	if (pg_numrows($rs_vales_no_facturados) > 0) {
		$reporte->templates = Array();
		$reporte->cabecera = Array();
		$reporte->cabeceraImagen = Array();
		$reporte->cabeceraSize = Array();
	    $reporte->cab_default = Array();
		$Cabecera = array( 
			"CLIENTES"	=> "CLIENTES",
			"IMPORTES"	=> "IMPORTES",
			);
		$reporte->definirColumna("CLIENTES", $reporte->TIPO_TEXTO, 40, "L");
		$reporte->definirColumna("IMPORTES", $reporte->TIPO_IMPORTE, 12, "R");
		$reporte->definirCabecera(1, "L", "OFICINA CENTRAL");
		$reporte->definirCabecera(1, "C", "CONCILIACION - Vales no Liquidacos no Facturados");
		$reporte->definirCabecera(1, "R", "PAG.%p");
		$reporte->definirCabecera(2, "R", "%f");
		$reporte->definirCabecera(3, "R", " ");
		$reporte->definirCabeceraPredeterminada($Cabecera);
		$total_datos = array();
	    $total_columna_conc_valesnofact = array();
		$ar_todas=array();
		$ar_todas=pg_fetch_all($rs_vales_no_facturados);
		echo "IMPRIMIR PDF";
		$reporte->AddPage();
		$reporte->Ln();
		foreach($ar_todas as $reg => $valores)
		{
		  foreach($valores as $key => $value)
		  {
		  $total_datos[''.strtoupper($key).''] = $value;
		    if(strtoupper($key) == 'IMPORTES'	)
		    {
		      $total_columna_conc_valesnofact[''.strtoupper($key).''] += $value;
		      $total_columna_conc[''.strtoupper($key).''] += $value;
		    }
		    else {
		      $total_columna_conc_valesnofact[''.strtoupper($key).''] = " ";
		      $total_columna_conc[''.strtoupper($key).''] = " ";	
		    }
		  }
		$reporte->nuevaFila($total_datos);
		}
		$reporte->Ln();
		$reporte->lineaH();
		$reporte->Ln();
		$reporte->nuevaFila($total_columna_conc_valesnofact);
		$reporte->Ln();
		$reporte->lineaH();
		$reporte->Ln();
		$reporte->nuevaFila($total_columna_conc);
		$reporte->Ln();
	}
	/******************************* FIN DEL INFORME DE VALES NO FACTURADOS *********************/

	/***************************** INICIO DEL INFORME DE VALES LIQUIDADOS NO FACTURADOS *************/
	if (pg_numrows($rs_vales_liquidados_no_facturados) > 0) {

		$reporte->templates = Array();
		$reporte->cabecera = Array();
		$reporte->cabeceraImagen = Array();
		$reporte->cabeceraSize = Array();
	    $reporte->cab_default = Array();

		$Cabecera = array( 
			"CLIENTES"	=> "CLIENTES",
			"IMPORTES"	=> "IMPORTES",
			);
		$reporte->definirColumna("CLIENTES", $reporte->TIPO_TEXTO, 40, "L");
		$reporte->definirColumna("IMPORTES", $reporte->TIPO_IMPORTE, 12, "R");
		$reporte->definirCabecera(1, "L", "OFICINA CENTRAL");
		$reporte->definirCabecera(1, "C", "CONCILIACION - Vales Liquidados no Facturados");
		$reporte->definirCabecera(1, "R", "PAG.%p");
		$reporte->definirCabecera(2, "R", "%f");
		$reporte->definirCabecera(3, "R", " ");
		$reporte->definirCabeceraPredeterminada($Cabecera);
		$total_datos = array();
	    $total_columna_conc_valesnofact = array();
		$ar_todas=array();
		$ar_todas=pg_fetch_all($rs_vales_liquidados_no_facturados);
		echo "IMPRIMIR PDF";
		$reporte->AddPage();
		$reporte->Ln();
		foreach($ar_todas as $reg => $valores)
		{
		  foreach($valores as $key => $value)
		  {
		  $total_datos[''.strtoupper($key).''] = $value;
		    if(strtoupper($key) == 'IMPORTES'	)
		    {
		      $total_columna_conc_valesnofact[''.strtoupper($key).''] += $value;
		      $total_columna_conc[''.strtoupper($key).''] += $value;
		    }
		    else {
		      $total_columna_conc_valesnofact[''.strtoupper($key).''] = " ";
		      $total_columna_conc[''.strtoupper($key).''] = " ";	
		    }
		  }
		$reporte->nuevaFila($total_datos);
		}
		$reporte->Ln();
		$reporte->lineaH();
		$reporte->Ln();
		$reporte->nuevaFila($total_columna_conc_valesnofact);
		$reporte->Ln();
		$reporte->lineaH();
		$reporte->Ln();
		$reporte->nuevaFila($total_columna_conc);
		$reporte->Ln();

	}
	/******************************** FIN DEL INFORME DE VALES LIQUIDADOS NO FACTURADOS ************/



	/***************************** INICIO DEL INFORME DE VALES PAGO ADELANTADO*************/
	if (pg_numrows($rs_vales_pago_adelantado) > 0) {

		$reporte->templates = Array();
		$reporte->cabecera = Array();
		$reporte->cabeceraImagen = Array();
		$reporte->cabeceraSize = Array();
	    $reporte->cab_default = Array();

		$Cabecera = array( 
			"CLIENTES"	=> "CLIENTES",
			"IMPORTES"	=> "IMPORTES",
			);
		$reporte->definirColumna("CLIENTES", $reporte->TIPO_TEXTO, 40, "L");
		$reporte->definirColumna("IMPORTES", $reporte->TIPO_IMPORTE, 12, "R");
		$reporte->definirCabecera(1, "L", "OFICINA CENTRAL");
		$reporte->definirCabecera(1, "C", "CONCILIACION - Vales de Pago Adelantado");
		$reporte->definirCabecera(1, "R", "PAG.%p");
		$reporte->definirCabecera(2, "R", "%f");
		$reporte->definirCabecera(3, "R", " ");
		$reporte->definirCabeceraPredeterminada($Cabecera);
		$total_datos = array();
                $total_columna_conc_valespagade = array();
		$ar_todas=array();
		$ar_todas=pg_fetch_all($rs_vales_pago_adelantado);
		echo "IMPRIMIR PDF";
		$reporte->AddPage();
		$reporte->Ln();
		foreach($ar_todas as $reg => $valores)
		{
		  foreach($valores as $key => $value)
		  {
		  $total_datos[''.strtoupper($key).''] = $value;
		    if(strtoupper($key) == 'IMPORTES'	)
		    {
		      $total_columna_conc_valespagade[''.strtoupper($key).''] += $value;
		      $total_columna_conc[''.strtoupper($key).''] += $value;
		    }
		    else {
		      $total_columna_conc_valespagade[''.strtoupper($key).''] = " ";
		      $total_columna_conc[''.strtoupper($key).''] = " ";	
		    }
		  }
		$reporte->nuevaFila($total_datos);
		}
		$reporte->Ln();
		$reporte->lineaH();
		$reporte->Ln();
		$reporte->nuevaFila($total_columna_conc_valespagade);
		$reporte->Ln();
		$reporte->lineaH();
		$reporte->Ln();
		$reporte->nuevaFila($total_columna_conc);
		$reporte->Ln();

	}
	/******************************** FIN DEL INFORME DE VALES PAGO ADELANTADO ************/

	/***************************** INICIO DEL INFORME DE CUENTA DE MANTEMINIENTO *************/
	if (pg_numrows($rs_cuenta_manteminiento) > 0) {

		$reporte->templates = Array();
		$reporte->cabecera = Array();
		$reporte->cabeceraImagen = Array();
		$reporte->cabeceraSize = Array();
	    $reporte->cab_default = Array();

		$Cabecera = array( 
		        "FACTURAS"	=> "FACTURAS",
			"CLIENTES"	=> "CLIENTES",
			"IMPORTES"	=> "IMPORTES",
			);
		$reporte->definirColumna("FACTURAS", $reporte->TIPO_TEXTO, 15, "L");
		$reporte->definirColumna("CLIENTES", $reporte->TIPO_TEXTO, 40, "L");
		$reporte->definirColumna("IMPORTES", $reporte->TIPO_IMPORTE, 12, "R");
		$reporte->definirCabecera(1, "L", "OFICINA CENTRAL");
		$reporte->definirCabecera(1, "C", "CONCILIACION - Cuenta de Manteminiento");
		$reporte->definirCabecera(1, "R", "PAG.%p");
		$reporte->definirCabecera(2, "R", "%f");
		$reporte->definirCabecera(3, "R", " ");
		$reporte->definirCabeceraPredeterminada($Cabecera);
		$total_datos = array();
                $total_columna_conc_ctamant = array();
		$ar_todas=array();
		$ar_todas=pg_fetch_all($rs_cuenta_manteminiento);
		echo "IMPRIMIR PDF";
		$reporte->AddPage();
		$reporte->Ln();
		foreach($ar_todas as $reg => $valores)
		{
		  foreach($valores as $key => $value)
		  {
		  $total_datos[''.strtoupper($key).''] = $value;
		    if(strtoupper($key) == 'IMPORTES'	)
		    {
		      $total_columna_conc_ctamant[''.strtoupper($key).''] += $value;
		      $total_columna_conc[''.strtoupper($key).''] += $value;
		    }
		    else {
		      $total_columna_conc_ctamant[''.strtoupper($key).''] = " ";
		      $total_columna_conc[''.strtoupper($key).''] = " ";	
		    }
		  }
		$reporte->nuevaFila($total_datos);
		}
		$reporte->Ln();
		$reporte->lineaH();
		$reporte->Ln();
		$reporte->nuevaFila($total_columna_conc_ctamant);
		$reporte->Ln();
		$reporte->lineaH();
		$reporte->Ln();
		$reporte->nuevaFila($total_columna_conc);
		$reporte->Ln();

	}
	/******************************** FIN DEL INFORME DE CUENTA DE MANTEMINIENTO ************/


	/***************************** INICIO DEL INFORME DE VENTAS POR CENTRO DE COSTO POR DIA *************/
	if (pg_numrows($rs_ventas_credito_det) > 0) {

		$reporte->templates = Array();
		$reporte->cabecera = Array();
		$reporte->cabeceraImagen = Array();
		$reporte->cabeceraSize = Array();
	    $reporte->cab_default = Array();
    
		$Cabecera = array( 
		        "DIA"        => "DIA",
			//"ADMINISTRA" => "ADMINISTRA",
			"IMPORTE_CASTILLA"   => "CASTILLA",
			"IMPORTE_MAGDALENA"  => "MAGDALENA",
			"IMPORTE_FAUCETT"    => "FAUCETT",
			"IMPORTE_BRENA"	     => "BRENA",
			"IMPORTE_PERLA"   => "LA PERLA",
			"IMPORTE_SUCRE"      => "SUCRE",
			"RISSO"      => "RISSO",
			"ORRANTIA"   => "ORRANTIA",
			);
		$reporte->definirColumna("DIA", $reporte->TIPO_TEXTO, 10, "L");
		//$reporte->definirColumna("ADMINISTRA", $reporte->TIPO_TEXTO, 12, "L");
		$reporte->definirColumna("IMPORTE_CASTILLA", $reporte->TIPO_IMPORTE, 12, "R");
		$reporte->definirColumna("IMPORTE_MAGDALENA", $reporte->TIPO_IMPORTE, 12, "R");
		$reporte->definirColumna("IMPORTE_FAUCETT", $reporte->TIPO_IMPORTE, 12, "R");
		$reporte->definirColumna("IMPORTE_BRENA", $reporte->TIPO_IMPORTE, 12, "R");
		$reporte->definirColumna("IMPORTE_PERLA", $reporte->TIPO_IMPORTE, 12, "R");
		$reporte->definirColumna("IMPORTE_SUCRE", $reporte->TIPO_IMPORTE, 12, "R");
		$reporte->definirColumna("RISSO", $reporte->TIPO_IMPORTE, 12, "R");
		$reporte->definirColumna("ORRANTIA", $reporte->TIPO_IMPORTE, 12, "R");
		
		$reporte->definirCabecera(1, "L", "OFICINA CENTRAL");
		$reporte->definirCabecera(1, "C", "CONCILIACION - Ventas por Centro de Costo por Dia");
		$reporte->definirCabecera(1, "R", "PAG.%p");
		$reporte->definirCabecera(2, "R", "%f");
		$reporte->definirCabecera(3, "R", " ");
		$reporte->definirCabeceraPredeterminada($Cabecera);
		$total_datos = array();
		$total_datos_t = array();
                $total_columna_conc_creddet = array();
		$ar_todas=array();
		$ar_todas=pg_fetch_all($rs_ventas_credito_det);
		echo "IMPRIMIR PDF";
		$reporte->AddPage();
		$reporte->Ln();
		foreach($ar_todas as $reg => $valores)
		{
		  foreach($valores as $key => $value)
		  {
		    $total_datos[''.strtoupper($key).''] = $value;
		  }
		$reporte->nuevaFila($total_datos);
		}
		
		$ar_todas_total=array();
		$ar_todas_total=pg_fetch_all($rs_ventas_credito_res);
			
		foreach($ar_todas_total as $reg => $valores)
		{
		  $total_datos_t['DIA'] = "TOTAL";
		  foreach($valores as $key => $value)
		  {
		    $total_datos_t[''.strtoupper($key).''] = $value;
		  }
		$reporte->Ln();
		$reporte->lineaH();
		$reporte->Ln();

		$reporte->nuevaFila($total_datos_t);
		$reporte->Ln();
		$reporte->lineaH();
		$reporte->Ln();
		}
		/*$reporte->Ln();
		$reporte->lineaH();
		$reporte->Ln();
		$reporte->nuevaFila($total_columna_conc_creddet);
		$reporte->Ln();
		$reporte->lineaH();
		$reporte->Ln();
		$reporte->nuevaFila($total_columna_conc);
		$reporte->Ln();*/

	}
	/******************************** FIN DEL INFORME DE VENTAS POR CENTRO DE COSTO POR DIA ************/



    $reporte->Output("/sistemaweb/reportes/cuadre.pdf", "F");
	echo('<script>');
	echo("	location.href='/sistemaweb/reportes/cuadre.pdf' " );
	echo('</script>');

