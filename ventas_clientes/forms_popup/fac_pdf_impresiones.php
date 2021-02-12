<?php
/**
* @author Miguel Angel Tavera Terán :: MATT
* @class ExportaraPDFDiferenciadePrecio :: clase para Generar el Archivo PDF de Diferencia de precios.
*/
include('/sistemaweb/include/reportes2.inc.php');

class ExportaraPDFDiferenciadePrecio {

/**
* @param reporte_array :: Contiene el arreglo de datos para el PDF
*/
function GenerarReportePDF($reporte_array){
  $cabecera = array(
		    'CH_NOMBRE_ITEM'=>'Combustibles',
		    'NU_CANTIDAD'=>'Glns. Consumido',
		    'NU_PRECIO_CONTRATADO'=>'Precio Contratado', 
		    'NU_MONTO_CONTRATADO'=>'Monto Contratado',
		    'NU_MONTO_FACTURA'=>'Monto Factura',
		    'NU_DIFERENCIA'=>'Diferencia'
		   );
	

  $fontsize = 9;
  $reporte = new CReportes2();
  $reporte->SetMargins(5, 5, 5);
  //$reporte->SetFont("courier", "", $fontsize);
  //$reporte->SetFont("arial", "B", $fontsize);
  $reporte->definirColumna("CH_NOMBRE_ITEM", $reporte->TIPO_TEXTO, 20, "L");
  $reporte->definirColumna("NU_CANTIDAD", $reporte->TIPO_IMPORTE, 20, "R");
  $reporte->definirColumna("NU_PRECIO_CONTRATADO", $reporte->TIPO_IMPORTE, 20, "R");
  $reporte->definirColumna("NU_MONTO_CONTRATADO", $reporte->TIPO_IMPORTE, 20, "R");
  $reporte->definirColumna("NU_MONTO_FACTURA", $reporte->TIPO_IMPORTE, 20, "R");
  $reporte->definirColumna("NU_DIFERENCIA", $reporte->TIPO_IMPORTE, 20, "R");

  $reporte->definirColumna("EMPRESA", $reporte->TIPO_TEXTO, 57, "L", "_cabeceraPrim");
  $reporte->definirColumna("TITULO", $reporte->TIPO_TEXTO, 60, "L", "_cabeceraPrim");
  $reporte->definirColumna("PAGINA", $reporte->TIPO_TEXTO, 19, "R", "_cabeceraPrim");
  
  $reporte->definirColumna("FECHA", $reporte->TIPO_TEXTO, 138, "R", "_cabeceraPrimFec");
  
  $reporte->definirColumna("FACTURA", $reporte->TIPO_TEXTO, 150, "L", "_cabeceraFactura");

  $reporte->definirColumna("CH_NOMBRE_ITEM", $reporte->TIPO_TEXTO, 20, "L", "_cabeceraSec");
  $reporte->definirColumna("NU_CANTIDAD", $reporte->TIPO_TEXTO, 20, "R", "_cabeceraSec");
  $reporte->definirColumna("NU_PRECIO_CONTRATADO", $reporte->TIPO_TEXTO, 20, "R", "_cabeceraSec");
  $reporte->definirColumna("NU_MONTO_CONTRATADO", $reporte->TIPO_TEXTO, 20, "R", "_cabeceraSec");
  $reporte->definirColumna("NU_MONTO_FACTURA", $reporte->TIPO_TEXTO, 20, "R", "_cabeceraSec");
  $reporte->definirColumna("NU_DIFERENCIA", $reporte->TIPO_TEXTO, 20, "R", "_cabeceraSec");

  $reporte->definirCabecera(1, "L", "OFICINA CENTRAL");
  $reporte->definirCabecera(1, "C", "DIFERENCIA DE PRECIOS");
  $reporte->definirCabecera(1, "R", "PAG.%p");
  $reporte->definirCabecera(2, "R", "%f");
  $reporte->definirCabecera(3, "R", " ");
  $reporte->definirCabeceraPredeterminada($cabecera);
  $reporte->AddPage();
  $datos=array();
  $c = 1;
  $cant = count($reporte_array);
  echo "<!--";
  //print_r($reporte_array);
  echo "-->";
  foreach($reporte_array as $key =>$ValoresGen)
  {

    foreach($ValoresGen as $llave => $valores)
    {
	/*Generando la segunda Cabecera de cada Columna*/
	$arrayCFact = array("FACTURA" => "Factura : ".trim($valores['ch_fac_seriedocumento'])."-".$valores['ch_nro_factura']."     Cliente : ".trim($valores['cliente'])."     Nro. N.C : ".trim($valores['ch_fac_seriedocumento'])."-".$valores['ch_fac_numerodocumento']."     F. Emision : ".trim($valores['dt_fac_fecha'])."");
	$datos['CH_NOMBRE_ITEM']       = $valores['ch_nombre_item'];
	$datos['NU_CANTIDAD']          = $valores['nu_cantidad'];
	$datos['NU_PRECIO_CONTRATADO'] = $valores['nu_precio_contratado'];
	$datos['NU_MONTO_CONTRATADO']  = $valores['nu_monto_contratado'];
	$datos['NU_MONTO_FACTURA']     = $valores['nu_monto_factura'];
	$datos['NU_DIFERENCIA']        = $valores['nu_diferencia'];
	
	if($valores['ch_nombre_item'] == 'TOTALES')
	{
	  $arrayCFact = array();
	  $reporte->Ln();
	  $reporte->lineaH();
	}
	
	if($NroFactura != $valores['ch_nro_factura'] && $valores['ch_nombre_item'] != 'TOTALES')
	{//Inicio -> If 1
	  if($c && $c%2==0)
	  {//Inicio -> If 2
	  
	    //For Para imprimir Lineas En Blanco
	    for($z=0; $z<=60; $z++)
	    $reporte->Ln();

	   /*Inicio -> Generando la segunda Cabecera de cada Columna*/
	    $arrayCabPrim = array(
				    "EMPRESA"=>"OFICINA CENTRAL",
				    "TITULO"=>"DIFERENCIA DE PRECIOS",
				    "PAGINA"=>"PAG. ".$reporte->ParseHeaderString('%p').""
				  );
	    $arrayCabPrimFec = array(
	                             "FECHA"=>$reporte->ParseHeaderString('%f')
	                           );
	    $arrayCabSec = array(
			      'CH_NOMBRE_ITEM'=>'Combustibles',
			      'NU_CANTIDAD'=>'Glns. Consumido',
			      'NU_PRECIO_CONTRATADO'=>'Precio Contratado', 
			      'NU_MONTO_CONTRATADO'=>'Monto Contratado',
			      'NU_MONTO_FACTURA'=>'Monto Factura',
			      'NU_DIFERENCIA'=>'Diferencia'
			    );

	   $reporte->nuevaFila($arrayCabPrim, "_cabeceraPrim");
	   $reporte->nuevaFila($arrayCabPrimFec, "_cabeceraPrimFec");
	   $reporte->Ln();
	   $reporte->lineaH();
	   $reporte->nuevaFila($arrayCabSec, "_cabeceraSec");
	   $reporte->lineaH();
	   /*Fin -> Generando la segunda Cabecera de cada Columna*/
	  }//Fin -> If 2
	  
	  $reporte->Ln();
	  $reporte->nuevaFila($arrayCFact, "_cabeceraFactura");
	  $reporte->lineaH();
	}//Fin -> If 1
	$NroFactura = $valores['ch_nro_factura'];
	$reporte->nuevaFila($datos);
    }
  if($c && $c!=$cant && $c%2==0)
    $reporte->AddPage();

  $c++; 
  }
    $reporte->Output("/sistemaweb/ventas_clientes/reportes/pdf/diferencia_precios_ventas.pdf", "F");
    return '<script> window.open("/sistemaweb/ventas_clientes/reportes/pdf/diferencia_precios_ventas.pdf","miwin","width=750,height=550,scrollbars=yes, resizable=yes, menubar=no");</script>';
}

}
