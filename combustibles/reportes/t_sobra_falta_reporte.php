<?php
/*
    Template para reporte de Sobrantes y Faltantes
    @MATT
*/

Class SobraFaltaReporteTemplate extends Template
{//Inicio :: Clase SobraFaltaReporteTemplate

    function ReportePDF($reporte_array)
    {
    $Tanques     = VariosModel::tanquesCodCBArray();
    $Totales = $reporte_array['Totales'];
    $Totales2 = $reporte_array['Totales2'];
    //print_r($reporte_array);
    $fechai = $_REQUEST['reporte']['dia_i'].'/'.$_REQUEST['reporte']['mes_i'].'/'.$_REQUEST['reporte']['anio_i'];
    $fechaf = $_REQUEST['reporte']['dia_f'].'/'.$_REQUEST['reporte']['mes_f'].'/'.$_REQUEST['reporte']['anio_f'];
    //echo "FECHA INI : $fechai\n";
    //echo "FECHA FIN : $fechaf\n";
    //print_r($Totales);
    //print_r($_REQUEST['reporte']);
     //print_r($Totales5);

    $Cabecera = array( 
		    "ESTACION"          => "ESTACION",
		    "01"                => "84-OCTANOS",
		    "05"                => "90-OCTANOS",
		    "02"                => "95-OCTANOS",
		    "06"                => "97-OCTANOS",
		    "TOTAL GASOLINA"    => "TOT. GASOLINA",
		    "03"                => "D2-PETROLEO",
		    "04"                => "D1-KEROSENE",
		    "TOTAL DIESEL"      => "TOT. DIESEL",
		    "TOTAL GALONES"     => "TOT. GALONES",
		    "07"                => "GLP LITROS"
		    //"TOTAL COSTO"       => "TOT COSTO"
		    );

    $Totales_new = array_merge_recursive($Totales, $Totales2);
    //print_r($Totales_new);
    $fontsize = 7;

    $reporte = new CReportes2("P");
    $reporte->SetMargins(5, 5, 5);
    $reporte->SetFont("courier", "", $fontsize);
	
    foreach($Cabecera as $key => $value)
    {
        if($key == 'ESTACION')
        {
            $reporte->definirColumna("".$key."", $tipo->TIPO_TEXT, 12, "L");
        }else{
            $reporte->definirColumna("".$key."", $tipo->TIPO_IMPORTE, 11, "R");
        }
	
    }

    $reporte->definirCabecera(1, "L", "ACOSA-OFICINA CENTRAL");
    $reporte->definirCabecera(1, "C", "RESUMEN GENERAL DE SOBRANTES Y FALTANTES");
    $reporte->definirCabecera(1, "R", "PAG.%p");
    $reporte->definirCabecera(2, "C", "Del: ".$fechai." Al: ".$fechaf."");
    $reporte->definirCabecera(2, "R", "%f");
    $reporte->definirCabecera(3, "R", " ");
    $reporte->definirCabeceraPredeterminada($Cabecera);

    $total_datos = array();
    //$total_datos['01']="";
    //print_r($total_datos);
    echo "IMPRIMIR PDF";
    $reporte->AddPage();
    $TotalesFinal = array();
    foreach($Totales_new as $estacion => $valores)
    {
      //echo "LLAVE : $estacion => VALOR : $valores \n";
      //$arrCalc = array();
      if(ereg("^([0-9]{1,3})", $estacion)){
        $estacion = trim(substr($estacion, 4, 15));
      }elseif(ereg('Venta', $estacion)){ 
        $estacion = trim(substr($estacion, 0, 8));
      }elseif(ereg('Porcentaje', $estacion)){
        $estacion = trim(substr($estacion, 0, 12));
      }
      foreach($Tanques as $llave => $valor)
      {
	if(!array_key_exists($llave, $valores))
	{
	  $valores[$llave] = " ";
	}
      }
      //print_r($valores);
      $total_datos['ESTACION'] = $estacion;
      foreach($valores as $key => $value)
      {

        $total_datos[$key] = money_format('%.2n', (round($value, 2)));

        //echo "TANQUE : ".$Tanques[$key]." \n";
        if(ereg("^([A-Z])", $estacion) && !ereg('Porcentaje', $estacion) && !ereg('Venta', $estacion) &&  ($key == $Tanques[$key] || ereg('TOTAL', $key)) ){
          //echo " LLAVE : $key => VALOR : $value\n";
          $TotalesFinal['Totales->'][$key] += money_format('%.2n', (round($value, 2)));
        }

        if(ereg('Venta', $estacion) && ($key == $Tanques[$key] || ereg('TOTAL', $key))){
          //echo "VALOR : $value\n";
          $TotalesFinal['Ventas->'][$key] += money_format('%.2n', (round($value, 2)));
        }

        if(ereg('Porcentaje', $estacion) &&  ($key == $Tanques[$key] || ereg('TOTAL', $key)) ){
          //echo "VALOR : $value\n";
          $TotalesFinal['Porcentaje->'][$key] += money_format('%.2n', (round($value, 2)));
        }

      }
      if(!ereg('Porcentaje', $estacion) && !ereg('Venta', $estacion))
      $reporte->Ln();
      //print_r($total_datos);
      $reporte->nuevaFila($total_datos);
    }
    $reporte->Ln();
    $reporte->lineaH();
    $reporte->Ln();
    foreach($TotalesFinal as $campos => $valores)
    {
      $total_datos['ESTACION'] = $campos;
      foreach($valores as $key => $value)
      {
        $total_datos[$key] = money_format('%.2n', (round($value, 2)));
      }
    $reporte->nuevaFila($total_datos);
    }
    //$reporte->nuevaFila($total_datos);
    //print_r($TotalesFinal);
    $reporte->Output("/acosa/combustibles/reporte_general_sobrantes_faltantes.pdf", "F");
    return '<iframe src="/acosa/combustibles/reporte_general_sobrantes_faltantes.pdf" width="900" height="300"></iframe>';
    }
}//Fin :: Clase SobraFaltaReporteTemplate

