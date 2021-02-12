<?php
/*
    Template para reporte de Sobrantes y Faltantes
    @MATT
*/
include('../include/reportes2.inc.php');

Class EstCuentaGenReporteTemplate
{//Inicio :: Clase SobraFaltaReporteTemplate

    function ReportePDF($reporte_array,$Fechas)
    {
    /*echo "<!--";
    print_r($reporte_array);
    echo "-->";*/
        //print_r($Totales2);
    $Cabecera = array( 
		    "CLIENTE"           => "CLIENTE",
		    "DOCUMENTO"         => "DOCUMENTO",
		    "F. EMISION"        => "F. EMISION",
		    "MO"                => "MONEDA",
		    "IMPORTE"           => "IMPORTE",
		    "VENCIMIENTO" 		=> "VENCIMIENTO",
		    "PRECANCELADO" 		=> "PRECANCELADO",
		    "SUCURSAL" 			=> "SUCURSAL",
		    "DOLARES"           => "SALDO DOLARES",
		    "SOLES"             => "SALDO SOLES",
		    "CREDITO"           => "CRDEDITO"
		    );

    $fontsize = 7;

    $reporte = new CReportes2();
    $reporte->SetMargins(5, 5, 5);
    $reporte->SetFont("courier", "", $fontsize);
    
	
    $reporte->definirColumna("CLIENTE", $reporte->TIPO_TEXTO, 9, "L");
    $reporte->definirColumna("DOCUMENTO", $reporte->TIPO_TEXTO, 20, "L");
    $reporte->definirColumna("F. EMISION", $reporte->TIPO_TEXTO, 10, "L");
    $reporte->definirColumna("MO", $reporte->TIPO_TEXTO, 2, "L");
    $reporte->definirColumna("IMPORTE", $reporte->TIPO_TEXTO, 10, "R");
    $reporte->definirColumna("VENCIMIENTO", $reporte->TIPO_TEXTO, 25, "L");
    $reporte->definirColumna("PRECANCELADO", $reporte->TIPO_TEXTO, 12, "L");
    $reporte->definirColumna("SUCURSAL", $reporte->TIPO_TEXTO, 15, "L");
    $reporte->definirColumna("DOLARES", $reporte->TIPO_IMPORTE, 10, "R");
    $reporte->definirColumna("SOLES", $reporte->TIPO_IMPORTE, 15, "R");
    $reporte->definirColumna("CREDITO", $reporte->TIPO_IMPORTE, 15, "R");
    
    $reporte->definirColumna("CODIGO", $reporte->TIPO_TEXTO, 9, "L","_DETALLE");
    $reporte->definirColumna("DESCRIPCION", $reporte->TIPO_TEXTO, 50, "L","_DETALLE");
    $reporte->definirColumna("CREDITO", $reporte->TIPO_TEXTO, 50, "L","CREDITO");

    $reporte->definirCabecera(1, "L", "SISTEMAWEB");
    $reporte->definirCabecera(1, "C", "ESTADO DE CUENTA GENERAL");
    $reporte->definirCabecera(1, "R", "PAG.%p");
    $reporte->definirCabecera(2, "C", " Al: ".$Fechas['HASTA']."");
    $reporte->definirCabecera(2, "R", "%f");
    $reporte->definirCabecera(3, "C", "TASA DE CAMBIO: ".$_REQUEST['c_tasa_cambio']);
    $reporte->definirCabecera(4, "R", " ");
    
    $reporte->definirCabeceraPredeterminada($Cabecera);
    
    $reporte->AddPage();
   
   // print_r($reporte_array);
    $datos = array();
    $auxi = '';
    foreach($reporte_array as $llave => $valores)
    {
    	
        foreach($valores as $clientes => $valor)
        {
        	if($datos['CLIENTE']=="*SUB-TOTAL*"){
		       $datos['SUCURSAL'] = 'TOTAL SALDO';
		       $reporte->lineaH();
		       $reporte->Ln();
		    }

		    $datos['CLIENTE'] = $clientes - $credito;
		    		       $datos['SUCURSAL'] = 'TOTAL SALDO';
		    if($datos['CLIENTE']!="*SUB-TOTAL*" && $datos['CLIENTE']!="*TOTAL*" && $datos['CLIENTE']!="*TOTAL GENERAL*" && $datos['CLIENTE']!="*DOLARES*" && $datos['CLIENTE']!="*SOLES*"){
		       if (substr($auxi,0,6) != substr($clientes,0,6)){
		       $datos['SUCURSAL'] = 'TOTAL SALDO';
		       		$datos2['CODIGO']=substr($clientes,0,6);
		       		$datos2['DESCRIPCION'] = substr(trim($clientes),15,strlen(trim($clientes)));
		       		$reporte->nuevaFila($datos2,"_DETALLE");
		       		$auxi=$clientes;
		       }
		       $datos['SUCURSAL'] = 'TOTAL SALDO';
		       $credito 	 	= $datos["CREDITO"];
		       $datos['CLIENTE']='';	       	
		       $datos['DOCUMENTO'] = $valor['DOCUMENTOS'];
		       $datos['F. EMISION'] = $valor['FECHA EMISION'];
		       $datos['MO'] = $valor['MONEDA'];
		       $datos['IMPORTE'] = $valor['IMPORTE'];
		       $datos['VENCIMIENTO'] = $valor['FECHA VENC. Y DIAS VENCIDOS'];
		       $datos['PRECANCELADO'] = ($_REQUEST['c_precancelado']=='S'?$valor["PRECANCELADO"]:'');
		       $datos['SUCURSAL'] = ($_REQUEST['c_precancelado']=='S'?$valor["SUCURSAL"]:'');
		       $datos['DOLARES'] = ($valor['SALDO DOLARES']==''?'':(($valor['TIPO']=='21' || $valor['TIPO']=='20')?'-':'')).($valor['SALDO DOLARES']=='0.00'?'':$valor['SALDO DOLARES']);
		       $datos['SOLES'] = (($valor['TIPO']=='21' || $valor['TIPO']=='20')?'-':'').$valor['SALDO SOLES'];
		    }elseif(ereg("*TOTAL*", $datos['CLIENTE'])){
		       $datos['DOCUMENTO'] = " ";
		       $datos['F. EMISION'] = " ";
		       $datos['MO'] = " ";
		       $datos['IMPORTE'] = " ";
		       $datos['VENCIMIENTO'] = " ";
		       $datos['PRECANCELADO'] = '';
		       $datos['SUCURSAL'] = '';
		       $datos['DOLARES'] = $valor['DOLARES'];
		       $datos['SOLES'] = $valor['SOLES'];
		    }else{
		       $datos['SUCURSAL'] = $datos['CLIENTE'];
		       $datos['CLIENTE'] =' ';	
		       $datos['DOCUMENTO'] = " ";
		       $datos['F. EMISION'] = " ";
		       $datos['MO'] = " ";
		       $datos['IMPORTE'] = " ";
		       $datos['VENCIMIENTO'] = " ";
		       $datos['PRECANCELADO'] = '';
		       $datos['DOLARES'] = $valor['DOLARES'];
		       $datos['SOLES'] = $valor['SOLES'];
		       $reporte->Ln();
		       $reporte->lineaH();
		       
		    }
		    $reporte->nuevaFila($datos);
		    if($datos['CLIENTE']==" ")
		    {
		         $reporte->Ln();
		    }
		}
    }
    $reporte->Output("/sistemaweb/ccobrar/estado_cuenta_general.pdf", "F");
    return '<script> window.open("/sistemaweb/ccobrar/estado_cuenta_general.pdf","miwin","width=750,height=550,scrollbars=yes, resizable=yes, menubar=no");</script>';
    }
}//Fin :: Clase SobraFaltaReporteTemplate
