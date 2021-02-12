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

Class VtaConsumoxPlacaTemplate
{//Inicio :: Clase VtaConsumoxPlacaTemplate

    function ReportePDF($reporte_array, $Fechas)
    {
    echo "<!--";
    print_r($reporte_array);
    echo "-->";
    
    $reporte = new CReportes2("P");
    $Almacenes = VariosModel::almacenCBArray();
    $CabeceraFin['FECHA']             = array("TIPO"=>$reporte->TIPO_TEXTO, "TAMANIO"=>"10", "POSICION"=>"L");
    $CabeceraFin['ESTACION']          = array("TIPO"=>$reporte->TIPO_TEXTO, "TAMANIO"=>"12", "POSICION"=>"L");
    $CabeceraFin['TRANSACCION']       = array("TIPO"=>$reporte->TIPO_TEXTO, "TAMANIO"=>"10", "POSICION"=>"L");
    $CabeceraFin['ODOMETRO']          = array("TIPO"=>$reporte->TIPO_TEXTO, "TAMANIO"=>"8", "POSICION"=>"L");
    $CabeceraFin['CODIGO']            = array("TIPO"=>$reporte->TIPO_TEXTO, "TAMANIO"=>"12", "POSICION"=>"L");
    $CabeceraFin['TIPO DE PRODUCTO']  = array("TIPO"=>$reporte->TIPO_TEXTO, "TAMANIO"=>"40", "POSICION"=>"L");
    $CabeceraFin['PRECIO UNITARIO']   = array("TIPO"=>$reporte->TIPO_IMPORTE, "TAMANIO"=>"8", "POSICION"=>"R");
    $CabeceraFin['CANT GALON']        = array("TIPO"=>$reporte->TIPO_IMPORTE, "TAMANIO"=>"8", "POSICION"=>"R");
    $CabeceraFin['IMPORTE']           = array("TIPO"=>$reporte->TIPO_IMPORTE, "TAMANIO"=>"8", "POSICION"=>"R");

    //print_r($CabeceraFin);
    
    $Cabecera = array( 
		    "FECHA"             => "FECHA",
		    "ESTACION"          => "ESTACION",
		    "TRANSACCION"       => "TRANSACCION",
		    "ODOMETRO"          => "ODOMETRO",
		    "CODIGO"            => "CODIGO",
		    "TIPO DE PRODUCTO"  => "TIPO DE PRODUCTO",
		    "PRECIO UNITARIO"   => "P. UNIT",
		    "CANT GALON"        => "C / GAL.",
		    "IMPORTE"           => "IMPORTE"
		    );

    $fontsize = 7.5;

    
    $reporte->SetMargins(5, 5, 5);
    $reporte->SetFont("courier", "", $fontsize);
    foreach($CabeceraFin as $campo => $datos)
    {
      $reporte->definirColumna($campo, $datos['TIPO'], $datos['TAMANIO'], $datos['POSICION']);
    }
    $reporte->definirColumna("CABECERA CLIENTE", $tipo->TIPO_TEXT, 60, "L", "_cabecera");
    //$reporte->definirColumna("TOTALES CLIENTE", $tipo->TIPO_TEXT, 60, "L", "_totclie");
    //$reporte->definirColumna("TOTALES PLACA", $tipo->TIPO_TEXT, 60, "L", "_totplaca");
    
    $reporte->definirCabecera(1, "L", "OFICINA CENTRAL");
    $reporte->definirCabecera(1, "C", "CONSUMOS POR PLACA DE VEHICULO");
    $reporte->definirCabecera(1, "R", "PAG.%p");
    $reporte->definirCabecera(2, "C", "Del: ".$Fechas['DESDE']." Al: ".$Fechas['HASTA']."");
    $reporte->definirCabecera(2, "R", "%f");
    $reporte->definirCabecera(3, "R", " ");
    $reporte->definirCabeceraPredeterminada($Cabecera);

    $total_datos = array();
    //echo "IMPRIMIR PDF";
    $reporte->AddPage();
    $reporte->Ln();
    foreach($reporte_array as $reg => $valores)
    {
      //echo "<!-- REG : $reg => VALORES : $valores -->\n";
        if(!empty($valores['CABECERA'])){
          $arrayCab = Array("CABECERA CLIENTE"=>"PLACA : ".$valores['CABECERA']['PLACA']." CLIENTE : ".trim($valores['CABECERA']['COD CLIENTE'])." - ".trim($valores['CABECERA']['DESC CLIENTE']));
          $reporte->lineaH();
          $reporte->nuevaFila($arrayCab, "_cabecera");
          $reporte->lineaH();
        }
        if(!empty($valores['DETALLE'])){
	  $total_datos['FECHA']           = $valores['DETALLE']['FECHA'];
	  $total_datos['ESTACION']        = $valores['DETALLE']['ESTACION'];
	  $total_datos['TRANSACCION']     = $valores['DETALLE']['TRANSACCION'];
	  $total_datos['ODOMETRO']        = $valores['DETALLE']['ODOMETRO'];
	  $total_datos['CODIGO']          = $valores['DETALLE']['CODIGO'];
	  $total_datos['TIPO DE PRODUCTO']= $valores['DETALLE']['TIPO DE PRODUCTO'];
	  $total_datos['PRECIO UNITARIO'] = $valores['DETALLE']['PRECIO UNITARIO'];
	  $total_datos['CANT GALON']      = $valores['DETALLE']['CANT GALON'];
	  $total_datos['IMPORTE']         = $valores['DETALLE']['IMPORTE'];
        }else{
	  $total_datos['FECHA']           = " ";
	  $total_datos['ESTACION']        = " ";
	  $total_datos['TRANSACCION']     = " ";
	  $total_datos['ODOMETRO']        = " ";
	  $total_datos['CODIGO']          = " ";
	  $total_datos['TIPO DE PRODUCTO']= " ";
	  $total_datos['PRECIO UNITARIO'] = " ";
	  $total_datos['CANT GALON']      = " ";
	  $total_datos['IMPORTE']         = " ";
        }
        
        if(!empty($valores['TOTALES PLACA'])){
          //echo "<!-- ENTRO ".$valores['TOTALES CLIENTE']['TOT CLIENTE']." -->\n";
	  $total_datos['FECHA']           = " ";
	  $total_datos['ESTACION']        = " ";
	  $total_datos['TRANSACCION']     = " ";
	  $total_datos['ODOMETRO']        = " ";
	  $total_datos['CODIGO']          = " ";
	  $total_datos['TIPO DE PRODUCTO']= "TOTAL POR PLACA ".$valores['TOTALES PLACA']['TOT PLACA'];
	  $total_datos['PRECIO UNITARIO'] = " ";
	  $total_datos['CANT GALON']      = $valores['TOTALES PLACA']['TOTAL CANTIDAD'];
	  $total_datos['IMPORTE']         = $valores['TOTALES PLACA']['TOTAL IMPORTE'];
        }

        if(!empty($valores['TOTALES CLIENTE'])){
          //echo "<!-- ENTRO ".$valores['TOTALES CLIENTE']['TOT CLIENTE']." -->\n";
	  $total_datos['FECHA']           = " ";
	  $total_datos['ESTACION']        = " ";
	  $total_datos['TRANSACCION']     = " ";
	  $total_datos['ODOMETRO']        = " ";
	  $total_datos['CODIGO']          = " ";
	  $total_datos['TIPO DE PRODUCTO']= "TOTAL CLIENTE ".$valores['TOTALES CLIENTE']['TOT CLIENTE'];
	  $total_datos['PRECIO UNITARIO'] = " ";
	  $total_datos['CANT GALON']      = $valores['TOTALES CLIENTE']['TOTAL CANTIDAD'];
	  $total_datos['IMPORTE']         = $valores['TOTALES CLIENTE']['TOTAL IMPORTE'];
        }
        if(!empty($reg['TOT GEN'])){
        $reporte->lineaH();
	  $total_datos['FECHA']           = " ";
	  $total_datos['ESTACION']        = " ";
	  $total_datos['TRANSACCION']     = " ";
	  $total_datos['ODOMETRO']        = " ";
	  $total_datos['CODIGO']          = " ";
	  $total_datos['TIPO DE PRODUCTO']= "TOTAL GENERAL ".$valores['VALES'];
	  $total_datos['PRECIO UNITARIO'] = " ";
	  $total_datos['CANT GALON']      = " ";
	  $total_datos['IMPORTE']         = $valores['TOTAL'];
	$reporte->lineaH();
        }
        //print_r($total_datos);
      $reporte->nuevaFila($total_datos);
    }
    
    
    $reporte->Ln();
    $reporte->lineaH();
    $reporte->Ln();
    
    $reporte->Output("/sistemaweb/ventas_clientes/reporte_consumo_x_placa_vehiculo.pdf", "F");
    return '<script> window.open("/sistemaweb/ventas_clientes/reporte_consumo_x_placa_vehiculo.pdf","miwin","width=750,height=550,scrollbars=yes, resizable=yes, menubar=no");</script>';
    }
}//Fin :: Clase VtaConsumoxPlacaTemplate

