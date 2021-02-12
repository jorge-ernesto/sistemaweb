<?php
/**
 * Template para reporte 
 * @author Miguel Angel Tavera Terán - MATT
 * @class ResuConsXclienteReporteTemplate -> Genera el reporte de Consumo
 * @return el pdf generado con data en un popup
*/
include('../include/reportes2.inc.php');
class ResuConsXclienteReporteTemplate
{

  function ReportePdf($DatosArray)
  {
    /*echo "<!--";
    print_r($DatosArray);
    echo "-->";*/
    $Cabecera = array
                (
                  "CLIENTE"   =>"CLIENTE",
                  "VENTA_84"  =>"VENTA 84",
                  "VENTA_90"  =>"VENTA 90",
                  "VENTA_95"  =>"VENTA 95",
                  "VENTA_97"  =>"VENTA 97",
                  "VENTA_D2"  =>"VENTA D2",
                  "VENTA_KD"  =>"VENTA KD",
                  "SUB_TOTAL" =>"SUB TOTAL",
                  "VENTA_GLP" =>"VENTA GLP",
                  "TOTAL"     =>"TOTAL",
                  "PORCENTAJE"=>"%",
                );
    $fontsize = 7;

    $reporte = new CReportes2("L");
    $reporte->SetMargins(2, 2, 2);
    $reporte->SetFont("courier", "", $fontsize);
    
    $reporte->definirColumna("CLIENTE", $reporte->TIPO_TEXTO, 42, "L");
    $reporte->definirColumna("VENTA_84", $reporte->TIPO_IMPORTE, 15, "R");
    $reporte->definirColumna("VENTA_90", $reporte->TIPO_IMPORTE, 15, "R");
    $reporte->definirColumna("VENTA_95", $reporte->TIPO_IMPORTE, 15, "R");
    $reporte->definirColumna("VENTA_97", $reporte->TIPO_IMPORTE, 15, "R");
    $reporte->definirColumna("VENTA_D2", $reporte->TIPO_IMPORTE, 15, "R");
    $reporte->definirColumna("VENTA_KD", $reporte->TIPO_IMPORTE, 15, "R");
    $reporte->definirColumna("SUB_TOTAL", $reporte->TIPO_IMPORTE, 15, "R");
    $reporte->definirColumna("VENTA_GLP", $reporte->TIPO_IMPORTE, 15, "R");
    $reporte->definirColumna("TOTAL", $reporte->TIPO_IMPORTE, 15, "R");
    $reporte->definirColumna("PORCENTAJE", $reporte->TIPO_CANTIDAD, 10, "R");

    $reporte->definirCabecera(1, "L", "OFICINA CENTRAL");
    $reporte->definirCabecera(1, "C", "RESUMO DE CONSUMO POR CLIENTE");
    $reporte->definirCabecera(1, "R", "PAG.%p");
    $reporte->definirCabecera(2, "R", " ");
    $reporte->definirCabecera(3, "L", "Usuario : %u");
    $reporte->definirCabecera(3, "C", "Del: ".$_REQUEST['c_fec_desde']." Al: ".$_REQUEST['c_fec_hasta']."");
    $reporte->definirCabecera(3, "R", "%f");
    $reporte->definirCabecera(4, "R", " ");
    
    $reporte->definirCabeceraPredeterminada($Cabecera);
    
    $reporte->AddPage();
    
    $datos = array();
    //$datosFilas = array();
    foreach($DatosArray as $Cliente => $Valores)
    {
      $datos["CLIENTE"] = $Cliente;
      if($Cliente=="TOTALES FINAL"){
	$reporte->Ln();
	$reporte->lineaH();
	$reporte->Ln();
      }
      
      foreach($Valores as $Nombre => $Valor)
      {

        $datos[$Nombre] = $Valor; 
      }
      $reporte->nuevaFila($datos);
    }
    
    $reporte->Output("/sistemaweb/ventas_clientes/reportes/pdf/resumen_consumo_x_cliente.pdf", "F");
    return '<script> window.open("/sistemaweb/ventas_clientes/reportes/pdf/resumen_consumo_x_cliente.pdf","miwin","width=750,height=550,scrollbars=yes, resizable=yes, menubar=no");</script>';

  }//Fin Funcion ReportePdf

}


