<?php

include('../include/reportes2.inc.php');

Class AnalisisMovCliReporteTemplate
{//Inicio :: Clase SobraFaltaReporteTemplate

    function ReportePDF($reporte_array,$Fechas)
    {
    //print_r($reporte_array);
   // print_r($reporte_array);
    $Cabecera1 = array( 
                    "CAB1"            =>  " ",
                    "CAB2"            =>  " ",
                    "CAB3"            =>  " ",
                    "CAB4"            =>  " ",
                    "CAB5"            =>  " ",
                    "CAB6"            =>  " ",
		    "IMPORTE S"     => "IMPORTE S/.",
		    "IMPORTE D"     => "IMPORTE US$/."
		    );
    
    $Cabecera2 = array(
                    "CLIENTE"       =>  "CLIENTE",
                    "FECHA"         =>  "FECHA",
                    "ACCION"        =>  "ACCION",
                    "TIPO DOCUMENTO"=>  "T. DOC.",
                    "DOCUMENTO"     =>  "DOCUMENTO",
                    "MO"            =>  "MO",
                    "CARGO SOL"     =>  "CARGO",
                    "ABONO SOL"     =>  "ABONO",
                    "CARGO DOL"     =>  "CARGO",
                    "ABONO DOL"     =>  "ABONO",
                    "REFERENCIA"    =>  "REFERENCIA",
                    "VOUCHER"       =>  "VOUCHER"
                      );
    $fontsize = 7;

    $reporte = new CReportes2("L");
    $reporte->SetMargins(5, 5, 5);
    $reporte->SetFont("courier", "", $fontsize);
    $reporte->definirColumna("CAB1", $reporte->TIPO_TEXTO, 40, "L", "Cabecera1");
    $reporte->definirColumna("CAB2", $reporte->TIPO_TEXTO, 10, "L", "Cabecera1");
    $reporte->definirColumna("CAB3", $reporte->TIPO_TEXTO, 10, "L", "Cabecera1");
    $reporte->definirColumna("CAB4", $reporte->TIPO_TEXTO, 8, "L", "Cabecera1");
    $reporte->definirColumna("CAB5", $reporte->TIPO_TEXTO, 14, "L", "Cabecera1");
    $reporte->definirColumna("CAB6", $reporte->TIPO_TEXTO, 10, "L", "Cabecera1");
    $reporte->definirColumna("IMPORTE S", $reporte->TIPO_TEXTO, 20, "C", "Cabecera1");
    $reporte->definirColumna("IMPORTE D", $reporte->TIPO_TEXTO, 40, "C", "Cabecera1");

    $reporte->definirColumna("CLIENTE", $reporte->TIPO_TEXTO, 40, "L");
    $reporte->definirColumna("FECHA", $reporte->TIPO_TEXTO, 10, "L");
    $reporte->definirColumna("ACCION", $reporte->TIPO_TEXTO, 10, "L");
    $reporte->definirColumna("TIPO DOCUMENTO", $reporte->TIPO_TEXTO, 8, "L");
    $reporte->definirColumna("DOCUMENTO", $reporte->TIPO_TEXTO, 10, "L");
    $reporte->definirColumna("MO", $reporte->TIPO_TEXTO, 2, "C");
    $reporte->definirColumna("CARGO SOL", $reporte->TIPO_IMPORTE, 15, "R");
    $reporte->definirColumna("ABONO SOL", $reporte->TIPO_IMPORTE, 15, "R");
    $reporte->definirColumna("CARGO DOL", $reporte->TIPO_IMPORTE, 15, "R");
    $reporte->definirColumna("ABONO DOL", $reporte->TIPO_IMPORTE, 15, "R");
    $reporte->definirColumna("REFERENCIA", $reporte->TIPO_TEXTOO, 25, "L");
    $reporte->definirColumna("VOUCHER", $reporte->TIPO_TEXTO, 15, "L");
    
    $reporte->definirColumna("DESCRIPCION", $reporte->TIPO_TEXTO, 80, "R", "_totCliente");
    $reporte->definirColumna("TOT CARGO SOL", $reporte->TIPO_IMPORTE, 15, "R", "_totCliente");
    $reporte->definirColumna("TOT ABONO SOL", $reporte->TIPO_IMPORTE, 15, "R", "_totCliente");
    $reporte->definirColumna("TOT CARGO DOL", $reporte->TIPO_IMPORTE, 15, "R", "_totCliente");
    $reporte->definirColumna("TOT ABONO DOL", $reporte->TIPO_IMPORTE, 15, "R", "_totCliente");

    $reporte->definirCabecera(1, "L", "ACOSA-OFICINA CENTRAL");
    $reporte->definirCabecera(1, "C", "ANALISIS MOVIMIENTO DE CLIENTES");
    $reporte->definirCabecera(1, "R", "PAG.%p");
    $reporte->definirCabecera(2, "R", " ");
    $reporte->definirCabecera(3, "L", "Usuario : %u");
    $reporte->definirCabecera(3, "C", "Del: ".$Fechas['DESDE']." Al: ".$Fechas['HASTA']."");
    $reporte->definirCabecera(3, "R", "%f");
    $reporte->definirCabecera(4, "R", " ");
    
    $reporte->definirCabeceraPredeterminada($Cabecera1, "Cabecera1");
    $reporte->definirCabeceraPredeterminada($Cabecera2);
    
    $reporte->AddPage();
    
    $datos = array();
    $reporte->Ln();
    $x=0;
    foreach($reporte_array as $llave => $valores)
    {
        foreach($valores as $clientes => $valor)
        {
           if($clientesRep && $clientesRep == $clientes){
             $datos['CLIENTE'] = " ";
             $x++;
	   }else{
	     $x=0;
	     $datos['CLIENTE'] = $clientes;
	   }

	   $clientesRep = $clientes;

	       $datos['FECHA']     = $valor['FECHA'];
	       $datos['ACCION']    = $valor['ACCION'];
	       $datos['TIPO DOCUMENTO'] = $valor['TIPO DOCUMENTO'];
	       $datos['DOCUMENTO'] = $valor['DOCUMENTO'];
	       $datos['MO']        = $valor['MONEDA'];
	       $datos['CARGO SOL'] = $valor['CARGO SOLES'];
	       $datos['ABONO SOL'] = $valor['ABONO SOLES'];
	       $datos['CARGO DOL'] = $valor['CARGO DOLARES'];
	       $datos['ABONO DOL'] = $valor['ABONO DOLARES'];
	       $datos['REFERENCIA']= $valor['DOC REFERENCIA'];
	       $datos['VOUCHER']   = $valor['VOUCHER'];

	    $reporte->nuevaFila($datos);
	    
	   $datosTotales['CLIENTE']        = "TOTALES GENERALES";
	   $datosTotales['FECHA']          = " ";
	   $datosTotales['ACCION']         = " ";
	   $datosTotales['TIPO DOCUMENTO'] = " ";
	   $datosTotales['DOCUMENTO']      = " ";
	   $datosTotales['MO']             = " ";
	   $datosTotales['CARGO SOL']      += $valor['CARGO SOLES'];
	   $datosTotales['ABONO SOL']      += $valor['ABONO SOLES'];
	   $datosTotales['CARGO DOL']      += $valor['CARGO DOLARES'];
	   $datosTotales['ABONO DOL']      += $valor['ABONO DOLARES'];
	   
	}
    }
    
    	$reporte->Ln();
    	$reporte->lineaH();
    	$reporte->Ln();
	$reporte->nuevaFila($datosTotales);
	$reporte->Ln();
	$reporte->lineaH();

    $reporte->Output("/acosa/ccobrar/analisis_movimiento_clientes.pdf", "F");
    return '<script> window.open("/acosa/ccobrar/analisis_movimiento_clientes.pdf","miwin","width=750,height=550,scrollbars=yes, resizable=yes, menubar=no");</script>';
    }
}//Fin :: Clase SobraFaltaReporteTemplate
