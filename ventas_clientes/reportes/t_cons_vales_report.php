<?php
  /*
    Templates para reportes Contables Balance
    @TBCA
  */

class ConsvalesPDFTemplate extends Template {

  function reporteComprobacion($datos_array)
  {
  $reporte_array = $datos_array['DATOS ARTICULOS'];
    $Cabecera = array( 
		    "PRODUCTOS"   => "PRODUCTOS",
		    "GALONESC"    => "GALONES CONSUMIDOS",
		    "PRECIOC"     => "PRECIO CONTRATADO",
		    "MONTOC"      => "MONTO CONTRATADO",
		    "MONTOF"      => "MONTO FACTURADO",
		    "DIFERENCIA"  => "DIFERENCIA"
		    );

    //$Totales_new = array_merge_recursive($Totales, $Totales2);
    //print_r($Totales_new);
    $fontsize = 7.5;

    $reporte = new CReportes2();
    $reporte->SetMargins(5, 5, 5);
    $reporte->SetFont("courier", "", $fontsize);
    
	
    $reporte->definirColumna("PRODUCTOS", $tipo->TIPO_TEXT, 30, "L");
    $reporte->definirColumna("GALONESC", $tipo->TIPO_IMPORTE, 20, "R");
    $reporte->definirColumna("PRECIOC", $tipo->TIPO_IMPORTE, 18, "R");
    $reporte->definirColumna("MONTOC", $tipo->TIPO_IMPORTE, 18, "R");
    $reporte->definirColumna("MONTOF", $tipo->TIPO_IMPORTE, 18, "R");
    $reporte->definirColumna("DIFERENCIA", $tipo->TIPO_IMPORTE, 18, "R");
    
    
    $reporte->definirCabecera(1, "L", "OFICINA CENTRAL");
    $reporte->definirCabecera(1, "C", "DIFERENCIA DE PRECIOS");
    $reporte->definirCabecera(1, "R", "PAG.%p");
    $reporte->definirCabecera(2, "R", " ");
    $reporte->definirCabecera(3, "L", "Consumos correspondientes a la Factura ".$datos_array['FACTURA']."");
    $reporte->definirCabecera(3, "R", "%f");
    $reporte->definirCabecera(4, "R", " ");
    $reporte->definirCabeceraPredeterminada($Cabecera);
    
    $reporte->AddPage();
/*
    [ch_articulo] => 0000077501873
    [art_descripcion] => 2CERRITOS ALMENDRA CONFITADAS
    [cantidad] => 1.0000
    [importe] => 2.0000
    [nu_precio_especial] => 
*/
    $datos = array();
    foreach($reporte_array as $llave => $valores)
    {
            //echo "llave : $llave => valor : ".$valores."\n";
	    if($datos['CLIENTE']=="*SUB-TOTAL*")
	    {
	       $reporte->lineaH();
	       $reporte->Ln();

	    }
	       $MontoContratado = ($valores['cantidad'] * $valores['nu_precio_especial']);
	       $Diferencia = ($MontoContratado - $valores['importe']);
	       
	       $datos['PRODUCTOS'] = $valores['art_descripcion'];
	       $datos['GALONESC']  = money_format('%.2n',$valores['cantidad']);
	       $datos['PRECIOC']   = money_format('%.2n',$valores['nu_precio_especial']);
	       $datos['MONTOC']    = money_format('%.2n',$MontoContratado);
	       $datos['MONTOF']    = money_format('%.2n',$valores['importe']);
	       $datos['DIFERENCIA']= money_format('%.2n',$Diferencia);

	       //$reporte->Ln();
	       //$reporte->lineaH();
	    /*----Generando los Totales---*/
	    $Totales['PRODUCTOS']    = "TOTAL";
	    $Totales['GALONESC']     += $valores["cantidad"];
	    $Totales['PRECIOC']      = " ";
	    $Totales['MONTOC']       += $MontoContratado;
	    $Totales['MONTOF']       += $valores["importe"];
	    $Totales['DIFERENCIA']   += $Diferencia;
	    
	    /*----------------------------*/
	    $reporte->nuevaFila($datos);
    }
    //print_r($Totales);
    $Totales['GALONESC'] = money_format('%.2n',$Totales['GALONESC']);
    $Totales['MONTOC'] = money_format('%.2n',$Totales['MONTOC']);
    $Totales['MONTOF'] = money_format('%.2n',$Totales['MONTOF']);
    $Totales['DIFERENCIA'] = money_format('%.2n',$Totales['DIFERENCIA']);
    $reporte->Ln();
    $reporte->lineaH();
    $reporte->nuevaFila($Totales);
    $reporte->lineaH();
    
    $reporte->Output("/sistemaweb/ventas_clientes/reportes/pdf/reporte_consumos_vales.pdf", "F");
    return '<iframe src="/sistemaweb/ventas_clientes/reportes/pdf/reporte_consumos_vales.pdf" width="900" height="300"></iframe>';
  }
  
}

