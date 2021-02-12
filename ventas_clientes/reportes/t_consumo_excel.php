<?php
/**
* Template para Generar un Archivo Excel
* @author MATT
* @class GeneraExcelConsumoTemplate
*/

class GeneraExcelConsumoTemplate extends Template
{//Inicio :: Clase GeneraExcelConsumoTemplate

  /**
  * @param array $datos_array -> Datos para ser enviados al documento Excel.
  * @return html para impresión de Iframe
  */
  function ReporteExcel($datos_array)
  {
   //print_r($datos_array);
    if($_REQUEST['action'] == true){
	  VariosModel::HeaderingExcel('reporte_acosa.xls');
    }
  
    $repExcel = new Workbook("-");
    
    //Creando la Primera Hoja de Trabajo
    $Hoja =& $repExcel->add_worksheet('Reporte de Vales por Cliente');
    
    //Creando el Formato de Presentación
    $formato =& $repExcel->add_format();
    
    $formato->set_size(6);
    $formato->set_align('vhequal_space');
    $formato->set_color('white');
    $formato->set_pattern();
    $formato->set_fg_color('navy');
    $formato->set_border(1);
    
    $formatocont =& $repExcel->add_format();
    $formatocont->set_size(6);
    //$formatocont->set_align('center');
    $formatocont->set_color('black');
    $formatocont->set_pattern();
    $formatocont->set_fg_color('white');

    //Asignando el tamaño de espacios por columna.
    $Hoja->set_column(0,0,6);//Fecha
    $Hoja->set_column(1,1,23);//Cliente
    $Hoja->set_column(2,2,6);//Nº de Vale
    $Hoja->set_column(3,3,6);//Placa
    $Hoja->set_column(4,4,18);//Artículo
    $Hoja->set_column(5,5,6);//Nº de Liquidación
    //$Hoja->set_column(6,6,6); //Odometro
    //$Hoja->set_column(7,7,9);//Nº de Tarjeta
    //$Hoja->set_column(8,8,3); //Código de Sunat
    $Hoja->set_column(6,6,5);//Cantidad
    $Hoja->set_column(7,7,5);//Precio
    $Hoja->set_column(8,8,5);//Importe
    
    //Escribiendo las Cabeceras
    $Hoja->write_string(0, 0, "Fecha", $formato);
    $Hoja->write_string(0, 1, "Cliente", $formato);
    $Hoja->write_string(0, 2, "Nro de Vale", $formato);
    $Hoja->write_string(0, 3, "Placa", $formato);
    $Hoja->write_string(0, 4, "Articulo", $formato);
    $Hoja->write_string(0, 5, "Nro de Liq.", $formato);
    //$Hoja->write_string(0, 6, "Odometro", $formato);
    //$Hoja->write_string(0, 7, "Nro Tarjeta", $formato);
    //$Hoja->write_string(0, 8, "C. S.", $formato);
    $Hoja->write_string(0, 6, "Cantidad", $formato);
    $Hoja->write_string(0, 7, "Precio", $formato);
    $Hoja->write_string(0, 8, "Importe", $formato);
    $x=1;
    foreach($datos_array as $reg => $valores)
    {
      //echo "X = $x => REG : $reg => VALORES : $valores \n";
      //dt_fecha | cliente | ch_documento | ch_placa | articulo | ch_liquidacion
      // | nu_odometro | ch_tarjeta | cod_sunat | nu_cantidad | nu_precio | nu_importe
      $Hoja->write($x, 0, trim($valores['dt_fecha']), $formatocont);
      $Hoja->write($x, 1, trim($valores['cliente']), $formatocont);
      $Hoja->write_string($x, 2, trim($valores['ch_documento']), $formatocont);
      $Hoja->write($x, 3, trim($valores['ch_placa']), $formatocont);
      $Hoja->write($x, 4, trim($valores['articulo']), $formatocont);
      $Hoja->write_string($x, 5, trim($valores['ch_liquidacion']), $formatocont);
      //$Hoja->write($x, 6, trim($valores['nu_odometro']), $formatocont);
      //$Hoja->write_string($x, 7, trim($valores['ch_tarjeta']), $formatocont);
      //$Hoja->write($x, 8, trim($valores['cod_sunat']), $formatocont);
      $Hoja->write($x, 6, money_format("%.2n",round($valores['nu_cantidad'],2)), $formatocont);
      $Hoja->write($x, 7, money_format("%.2n",round($valores['nu_precio'],2)), $formatocont);
      $Hoja->write($x, 8, money_format("%.2n",round($valores['nu_importe'],2)), $formatocont);
      //money_format("%.2n",round($valores['nu_importe'],2));
    $x++;
    }
    
    if($_REQUEST['action'] == true){
      $repExcel->close();
    }
  

  return '<iframe src="/sistemaweb/ventas_clientes/reportes/excel/reporte_acosa.xls" width="900" height="300"></iframe>';
  }

}//Fin :: Clase GeneraExcelConsumoTemplate


