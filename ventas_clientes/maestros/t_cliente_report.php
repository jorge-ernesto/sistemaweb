<?php
  /*
    Templates para reportes 
    @TBCA
  */

class ClientesPDFTemplate extends Template {

  function reporte($datos_array)
  {
    //print_r($datos_array['datos']);
    $cbDistritos = VariosModel::ListaGeneral('02');
    //print_r($cbDistritos);
    $reporte_array = $datos_array['datos'];
    $Cabecera = array( 
		    "CODIGO"     	=> "CODIGO",
		    "RUC"         	=> "RUC",
		    "TIPO"	  	=> "TIPO",
		    "RAZSOC"      	=> "RAZON SOCIAL",
		    "DIRECCION"  	=> " DIRECCION",
		    "TELEFONO"    	=> " TELEFONO",
		    "DISTRITO"   	=> "DISTRITO",
  		    "CREDSOLES"         => "CREDITO SOLES",
		    "CREDDOLARES"       => "CREDITO DOLARES",
		    "FORMAPAGO"    	=> "FORMA DE PAGO",
		    "LDISPONIBLE"       => "LINEA DISPONIBLE"
		    );

    //$Totales_new = array_merge_recursive($Totales, $Totales2);
    //print_r($Totales_new);
    $fontsize = 7;

    $reporte = new CReportes2("L","pt","A3");
    $reporte->SetMargins(5, 5, 5);
    $reporte->SetFont("courier", "", $fontsize);
    
	
    $reporte->definirColumna("CODIGO", $reporte->TIPO_TEXTO, 15, "L");
    $reporte->definirColumna("RUC", $reporte->TIPO_TEXTO, 15, "L");
    $reporte->definirColumna("TIPO",$reporte->TIPO_TEXTO,5,"L");
    $reporte->definirColumna("RAZSOC", $reporte->TIPO_TEXTO, 50, "L");
    $reporte->definirColumna("DIRECCION", $reporte->TIPO_TEXTO, 50, "L");
    $reporte->definirColumna("TELEFONO", $reporte->TIPO_TEXTO, 10, "L");
    $reporte->definirColumna("DISTRITO", $reporte->TIPO_TEXTO, 30, "L");
    $reporte->definirColumna("CREDSOLES", $reporte->TIPO_TEXTO, 20, "L");
    $reporte->definirColumna("CREDDOLARES", $reporte->TIPO_TEXTO, 20, "L");
    $reporte->definirColumna("FORMAPAGO", $reporte->TIPO_TEXTO, 20, "L");
    $reporte->definirColumna("LDISPONIBLE", $reporte->TIPO_TEXTO, 20, "L");
    
    
    $reporte->definirCabecera(1, "L", "OFICINA CENTRAL");
    $reporte->definirCabecera(1, "C", "REPORTE DE CLIENTES");
    $reporte->definirCabecera(1, "R", "PAG.%p");
    $reporte->definirCabecera(2, "R", " ");
    //$reporte->definirCabecera(3, "L", "Consumos correspondientes a la Factura ".$datos_array['FACTURA']."");
    $reporte->definirCabecera(3, "R", "%f");
    $reporte->definirCabecera(4, "R", " ");
    $reporte->definirCabeceraPredeterminada($Cabecera);
    
    $reporte->AddPage();

    $datos = array();
    foreach($reporte_array as $llave => $valores)
    {
      /*foreach($valores as $key => $value)
      {*/
      //echo "llave : $llave => valor : ".$valores['cli_codigo']."\n";
	$datos['CODIGO']     	= $valores['cli_codigo'];
	$datos['RUC']        	= $valores['cli_ruc'];
	$datos['TIPO']	     	= $valores['cli_tipo'];
	$datos['RAZSOC']     	= $valores['cli_razsocial'];
	$datos['DIRECCION']  	= $valores['cli_direccion'];
	$datos['TELEFONO']   	= $valores['cli_telefono1'];
	$datos['DISTRITO']   	= $cbDistritos[trim($valores['cli_distrito'])];
	$datos['CREDSOLES']	= $valores['cli_creditosol'];
	$datos['CREDDOLARES']   = $valores['cli_creditodol'];
	$datos['FORMAPAGO']  	= $valores['cli_fpago_credito'];
	$datos['LDISPONIBLE']   = $valores['cli_limite_consumo'];
      //}
      //$reporte->Ln();
      //$reporte->lineaH();
      $reporte->nuevaFila($datos);
    }
    //print_r($Totales);

    $reporte->Output("/sistemaweb/ventas_clientes/reportes/pdf/reporte_clientes.pdf", "F");
		return '<script> window.open("/sistemaweb/ventas_clientes/reportes/pdf/reporte_clientes.pdf","miwin","width=750,height=550,scrollbars=yes, resizable=yes, menubar=no");</script>';
    //return '<iframe src="/sistemaweb/ventas_clientes/reportes/pdf/reporte_clientes.pdf" width="900" height="300"></iframe>';
  }
  
}


