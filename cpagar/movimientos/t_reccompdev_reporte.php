<?php
/*
    Template para reporte 
    @MATT
*/

Class RecCompDevReporteTemplate extends Template
{//Inicio :: Clase RecCompDevReporteTemplate

    function ReportePDF($reporte_array)
    {
    //print_r($reporte_array['datos']);
    $reporte = new CReportes2("L");
    $Almacenes = VariosModel::almacenCBArray();
    $CabeceraFin['FECHA']           = array("TIPO"=>$reporte->TIPO_TEXTO, "TAMANIO"=>"10", "POSICION"=>"L");
    $CabeceraFin['PROVEEDOR']       = array("TIPO"=>$reporte->TIPO_TEXTO, "TAMANIO"=>"40", "POSICION"=>"L");
    $CabeceraFin['DOCUM REF']       = array("TIPO"=>$reporte->TIPO_TEXTO, "TAMANIO"=>"12", "POSICION"=>"L");
    $CabeceraFin['ALMACEN']         = array("TIPO"=>$reporte->TIPO_TEXTO, "TAMANIO"=>"12", "POSICION"=>"L");
    $CabeceraFin['ARTICULO']        = array("TIPO"=>$reporte->TIPO_TEXTO, "TAMANIO"=>"40", "POSICION"=>"L");
    $CabeceraFin['CANTIDAD']        = array("TIPO"=>$reporte->TIPO_IMPORTE, "TAMANIO"=>"8", "POSICION"=>"R");
    $CabeceraFin['COSTO UNITARIO']  = array("TIPO"=>$reporte->TIPO_IMPORTE, "TAMANIO"=>"8", "POSICION"=>"R");
    $CabeceraFin['COSTO TOTAL']     = array("TIPO"=>$reporte->TIPO_IMPORTE, "TAMANIO"=>"8", "POSICION"=>"R");
    $CabeceraFin['NRO MOV']         = array("TIPO"=>$reporte->TIPO_TEXTO, "TAMANIO"=>"12", "POSICION"=>"L");
    $CabeceraFin['FACTURA PROV']    = array("TIPO"=>$reporte->TIPO_TEXTO, "TAMANIO"=>"14", "POSICION"=>"L");

    //print_r($CabeceraFin);
    
    $Cabecera = array( 
		    "FECHA"           => "FECHA",
		    "PROVEEDOR"       => "PROVEEDOR",
		    "DOCUM REF"       => "DOCUM. REF.",
		    "ALMACEN"         => "ALMACEN",
		    "ARTICULO"        => "ARTICULO",
		    "CANTIDAD"        => "CANT.",
		    "COSTO UNITARIO"  => "C. U.",
		    "COSTO TOTAL"     => "C. T.",
		    "NRO MOV"         => "NRO MOV",
		    "FACTURA PROV"    => "FACT. PROV."
		    );

    $fontsize = 8;

    
    $reporte->SetMargins(5, 5, 5);
    $reporte->SetFont("courier", "", $fontsize);
    foreach($CabeceraFin as $campo => $datos)
    {
      $reporte->definirColumna($campo, $datos['TIPO'], $datos['TAMANIO'], $datos['POSICION']);
    }

    $reporte->definirCabecera(1, "L", "ACOSA-OFICINA CENTRAL");
    $reporte->definirCabecera(1, "C", "RECORD DE COMPRAS Y DEVOLUCIONES");
    $reporte->definirCabecera(1, "R", "PAG.%p");
    $reporte->definirCabecera(2, "C", "Del: ".$_REQUEST['busqueda']['fecha_ini']." Al: ".$_REQUEST['busqueda']['fecha_fin']."");
    $reporte->definirCabecera(2, "R", "%f");
    $reporte->definirCabecera(3, "R", " ");
    $reporte->definirCabeceraPredeterminada($Cabecera);

    $total_datos = array();
    echo "IMPRIMIR PDF";
    $reporte->AddPage();
    $reporte->Ln();
    foreach($reporte_array['datos'] as $reg => $valores)
    {
        $total_datos['FECHA']           = $valores['mov_fecha'];
        $total_datos['PROVEEDOR']       = trim($valores['mov_entidad'])." ".@$valores['pro_razsocial'].@$valores['pro_rsocialbreve'];
        $total_datos['DOCUM REF']       = $valores['mov_tipdocuref']." ".$valores['mov_docurefe'];
        $total_datos['ALMACEN']         = substr($Almacenes[$valores['mov_almacen']],4,15);
        $total_datos['ARTICULO']        = trim($valores['art_codigo'])." ".@$valores['art_descripcion'].@$valores['art_descbreve'];
        $total_datos['CANTIDAD']        = money_format("%.2n",round($valores['mov_cantidad'],2));
        $total_datos['COSTO UNITARIO']  = $valores['mov_costounitario'];
        $total_datos['COSTO TOTAL']     = $valores['mov_costototal'];
        $total_datos['NRO MOV']         = $valores['mov_numero'];
        $total_datos['FACTURA PROV']    = $valores['factura'];
      
      $reporte->nuevaFila($total_datos);
    }
    
    
    $reporte->Ln();
    $reporte->lineaH();
    $reporte->Ln();
    
    $reporte->Output("/sistemaweb/cpagar/reporte_record_compras_devoluciones.pdf", "F");
    return '<iframe src="/sistemaweb/cpagar/reporte_record_compras_devoluciones.pdf" width="900" height="300"></iframe>';
    }
}//Fin :: Clase RecCompDevReporteTemplate

?>