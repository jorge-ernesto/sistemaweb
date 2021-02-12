<?php
/*
    Template para reporte de Sobrantes y Faltantes
    @MATT
*/
include('../include/reportes2.inc.php');
include_once('../include/dbsqlca.php');
include('../include/m_sisvarios.php');
// Clase Modelo.. totalmente abstracta
class Model {
}

Class VtaRegVentasClientesTemplate
{//Inicio :: Clase VtaRegVentasClientesTemplate

    function ReportePDF($reporte_array, $Fechas)
    {
    echo "<!--";
    //print_r($reporte_array);
    echo "-->";
    
    $reporte = new CReportes2("P");
    $Almacenes = VariosModel::almacenCBArray();
    //$CabeceraFin['NRO']             = array("TIPO"=>$reporte->TIPO_TEXTO, "TAMANIO"=>"3", "POSICION"=>"L");
    $CabeceraFin['FECHA']             = array("TIPO"=>$reporte->TIPO_TEXTO, "TAMANIO"=>"10", "POSICION"=>"L");
    $CabeceraFin['DOC_SUNAT']         = array("TIPO"=>$reporte->TIPO_TEXTO, "TAMANIO"=>"4", "POSICION"=>"L");
    $CabeceraFin['DOCUMENTO']         = array("TIPO"=>$reporte->TIPO_TEXTO, "TAMANIO"=>"13", "POSICION"=>"L");
    $CabeceraFin['RUC']               = array("TIPO"=>$reporte->TIPO_TEXTO, "TAMANIO"=>"13", "POSICION"=>"L");
    $CabeceraFin['RAZON_SOCIAL']      = array("TIPO"=>$reporte->TIPO_TEXTO, "TAMANIO"=>"45", "POSICION"=>"L");
    $CabeceraFin['TIPO_CAMBIO']       = array("TIPO"=>$reporte->TIPO_TEXTO, "TAMANIO"=>"8", "POSICION"=>"R");
    $CabeceraFin['VALOR_NETO']        = array("TIPO"=>$reporte->TIPO_IMPORTE, "TAMANIO"=>"10", "POSICION"=>"R");
    $CabeceraFin['IMPUESTOS']         = array("TIPO"=>$reporte->TIPO_IMPORTE, "TAMANIO"=>"10", "POSICION"=>"R");
    $CabeceraFin['TOTAL_VENTA']       = array("TIPO"=>$reporte->TIPO_IMPORTE, "TAMANIO"=>"10", "POSICION"=>"R");
    $CabeceraFin['NUM_LIQUI']         = array("TIPO"=>$reporte->TIPO_TEXTO, "TAMANIO"=>"10", "POSICION"=>"L");

    //print_r($CabeceraFin);
    
    $Cabecera = array( 
                   // "NRO"        => "NRO",
		    "FECHA"        => "FECHA",
		    "DOC_SUNAT"    => "TD",
		    "DOCUMENTO"    => "DOCUMENTO",
		    "RUC"          => "RUC",
		    "RAZON_SOCIAL" => "RAZON SOCIAL",
		    "TIPO_CAMBIO"  => "TIP CAMB",
		    "VALOR_NETO"   => "VAL NET",
		    "IMPUESTOS"    => "IMPUESTO",
		    "TOTAL_VENTA"  => "TOT VEN",
		    "NUM_LIQUI"    => "NRO. LIQ."
		    );

    $fontsize = 6.7;

    
    $reporte->SetMargins(5, 5, 5);
    $reporte->SetFont("courier", "", $fontsize);
    foreach($CabeceraFin as $campo => $datos)
    {
      $reporte->definirColumna($campo, $datos['TIPO'], $datos['TAMANIO'], $datos['POSICION']);
    }
    //$reporte->definirColumna("CABECERA CLIENTE", $tipo->TIPO_TEXT, 60, "L", "_cabecera");
    //$reporte->definirColumna("TOTALES CLIENTE", $tipo->TIPO_TEXT, 60, "L", "_totclie");
    //$reporte->definirColumna("TOTALES PLACA", $tipo->TIPO_TEXT, 60, "L", "_totplaca");
    
    $reporte->definirCabecera(1, "L", "OFICINA CENTRAL");
    $reporte->definirCabecera(1, "C", "REGISTRO DE VENTAS POR CLIENTES");
    $reporte->definirCabecera(1, "R", "PAG.%p");
    $reporte->definirCabecera(2, "C", "Del: ".$Fechas['DESDE']." Al: ".$Fechas['HASTA']."");
    $reporte->definirCabecera(2, "R", "%f");
    $reporte->definirCabecera(3, "R", " ");
    $reporte->definirCabeceraPredeterminada($Cabecera);

    $total_datos = array();
    //echo "IMPRIMIR PDF";
    $reporte->AddPage();
    //$reporte->Ln();
    $x=1;
    foreach($reporte_array as $reg => $valores)
    {
      //echo "<!-- REG : $reg => VALORES : $valores -->\n";
      //$total_datos['NRO'] = $x;
	$FinalValorNeto += $valores['valor_neto'];
	$FinalImpuestos += $valores['impuestos'];
	$FinalTotalVentas += $valores['total_venta'];
      
        foreach($valores as $key => $value)
        {
          $total_datos[''.strtoupper($key).''] = $value;
        }
        if($x<=111){
          //echo "ENTRO $x";
          $ValorNeto += $valores['valor_neto'];
          $Impuestos += $valores['impuestos'];
          $TotalVentas += $valores['total_venta'];
        }
        
        if($x == 111)
        {
          //echo "ENTRO $x";
          $reporte->Ln();
          $reporte->lineaH();
          $total_datos['FECHA']         = " ";
          $total_datos['DOC_SUNAT']     = " ";
          $total_datos['DOCUMENTO']     = " ";
          $total_datos['RUC']           = " ";
          $total_datos['RAZON_SOCIAL']  = " VALOR POR PAGINA ";
          $total_datos['TIPO_CAMBIO']   = " ";
          $total_datos['VALOR_NETO']    = $ValorNeto;
          $total_datos['IMPUESTOS']     = $Impuestos;
          $total_datos['TOTAL_VENTA']   = $TotalVentas;
          $total_datos['NUM_LIQUI']     = " ";
          $x=0;
          $ValorNeto = 0;
        }

        $reporte->nuevaFila($total_datos);

        //print_r($total_datos);
        
    $x++;
    $y++;
    }
    
    $total_datos['FECHA']         = " ";
    $total_datos['DOC_SUNAT']     = " ";
    $total_datos['DOCUMENTO']     = " ";
    $total_datos['RUC']           = " ";
    $total_datos['RAZON_SOCIAL']  = " TOTALES  ";
    $total_datos['TIPO_CAMBIO']   = " ";
    $total_datos['VALOR_NETO']    = $FinalValorNeto;
    $total_datos['IMPUESTOS']     = $FinalImpuestos;
    $total_datos['TOTAL_VENTA']   = $FinalTotalVentas;
    $total_datos['NUM_LIQUI']     = " ";
    $reporte->Ln();
    $reporte->lineaH();
    $reporte->Ln();
    $reporte->nuevaFila($total_datos);
    $reporte->Ln();
    $reporte->lineaH();
    $reporte->Ln();
    
    $reporte->Output("/sistemaweb/ventas_clientes/reporte_registro_ventas_clientes.pdf", "F");
    return '<script> window.open("/sistemaweb/ventas_clientes/reporte_registro_ventas_clientes.pdf","miwin","width=750,height=550,scrollbars=yes, resizable=yes, menubar=no");</script>';
    }
}//Fin :: Clase VtaRegVentasClientesTemplate

