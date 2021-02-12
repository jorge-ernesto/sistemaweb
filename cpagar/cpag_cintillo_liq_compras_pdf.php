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

class CintilloLiqComprasTemplate
{//Inicio :: Clase SobraFaltaReporteTemplate

    function ReportePDF($reporte_array,$Fechas)
    {
    
    echo "<!--";
    print_r($reporte_array);
    echo "-->";
    $IGV = VariosModel::ObtIgv();
    //echo "IGV : $IGV \n";
    $Cabecera1 = array(
                        "CFECHA"        => "FECHA",
                        "CPROVEEDOR"    => "PROVEEDOR",
                        "CALMACEN"      => "ALMACEN",
                        "CDOC.INT"      => "DOC.INT",
                        "CNROREGISTRO"  => "NRO REGISTRO"
                      );
    $Cabecera2 = array(
                        "CDRUBRO"       => "RUBRO",
                        "CDEMISION"     => "EMISION",
                        "CDMONEDA"      => "MONEDA",
                        "CDVALOR"       => "VALOR",
                        "CDIMPUESTOS"   => "IMPUESTOS",
                        "CDTOTAL"       => "TOTAL"
                      );
    $Detalle = array( 
		    "CODIGO"      => "CODIGO",
		    "DESCRIPCION" => "DESCRIPCION",
		    //"NRO_O_C"     => "NRO. O/C",
		    "UNI"         => "UNI",
		    "CANTIDAD"    => "CANT.",
		    "CDOLTOTAL"   => "US$ C TOT.",
		    "CTOTAL"      => "S/. C TOT.",
		    "CUNIIGV"     => "C.U. IGV",
		    "PREUNIIGV"   => "P.U. IGV",
		    "DIFIGV"      => "DIF. IGV",
		    "PORCENTAJE"  => "(%)",
		    "STOCK"       => "STOCK"
		    );
    
    //$Totales_new = array_merge_recursive($Totales, $Totales2);
    //print_r($Totales_new);
    $fontsize = 7;

    $reporte = new CReportes2('L','pt', array('250','580'));
    $reporte->SetMargins(5, 5, 5);
    $reporte->SetFont("courier", "", $fontsize);
    
	
    $reporte->definirColumna("CODIGO", $tipo->TIPO_TEXT, 13, "L");
    $reporte->definirColumna("DESCRIPCION", $tipo->TIPO_TEXT, 30, "L");
    //$reporte->definirColumna("NRO_O_C", $tipo->TIPO_TEXT, 10, "L");
    $reporte->definirColumna("UNI", $tipo->TIPO_TEXT, 6, "L");
    $reporte->definirColumna("CANTIDAD", $tipo->TIPO_IMPORTE, 6, "R");
    $reporte->definirColumna("CDOLTOTAL", $tipo->TIPO_IMPORTE, 10, "R");
    $reporte->definirColumna("CTOTAL", $tipo->TIPO_IMPORTE, 10, "R");
    $reporte->definirColumna("CUNIIGV", $tipo->TIPO_IMPORTE, 10, "R");
    $reporte->definirColumna("PREUNIIGV", $tipo->TIPO_IMPORTE, 10, "R");
    $reporte->definirColumna("DIFIGV", $tipo->TIPO_IMPORTE, 10, "R");
    $reporte->definirColumna("PORCENTAJE", $tipo->TIPO_IMPORTE, 10, "R");
    $reporte->definirColumna("STOCK", $tipo->TIPO_IMPORTE, 6, "R");
    
    
    $reporte->definirCabecera(1, "L", "ACOSA-OFICINA CENTRAL");
    $reporte->definirCabecera(1, "C", "CINTILLO DE LIQUIDACION DE COMPRAS");
    $reporte->definirCabecera(1, "R", "PAG.%p");
    //$reporte->definirCabecera(2, "C", "Del: ".$Fechas['DESDE']." Al: ".$Fechas['HASTA']."");
    $reporte->definirCabecera(2, "L", "ALMACEN : ".$reporte_array['ALMACEN']);
    $reporte->definirCabecera(2, "R", "%f");
    $reporte->definirCabecera(3, "R", " ");
    $reporte->definirCabecera(4, "R", " ");
    
    $DatosCabecera = 'FECHA : '.$reporte_array['CABECERA']['FECHA'].'  ';
    $DatosCabecera .= 'PROVEEDOR : '.$reporte_array['CABECERA']['PROVEEDOR'].'  ';
    $DatosCabecera .= 'DOC. INT. : '.$reporte_array['CABECERA']['DOC.INT'].'  ';
    $DatosCabecera .= 'NRO REGISTRO : '.$reporte_array['CABECERA']['NRO REGISTRO'].'  ';
    
    $DatosCabecera2 = 'RUBRO : '.$reporte_array['CABECERA DATOS']['RUBRO'].'  ';
    $DatosCabecera2 .= 'EMISION : '.$reporte_array['CABECERA DATOS']['EMISION'].'  ';
    $DatosCabecera2 .= 'MONEDA : '.$reporte_array['CABECERA DATOS']['MONEDA'].'  ';
    $DatosCabecera2 .= 'VALOR : '.$reporte_array['CABECERA DATOS']['VALOR'].'  ';
    $DatosCabecera2 .= 'IMPUESTO : '.$reporte_array['CABECERA DATOS']['IMPUESTOS'].'  ';
    $DatosCabecera2 .= 'TOTAL : '.$reporte_array['CABECERA DATOS']['TOTAL'].'  ';
    $DatosCabecera2 .= 'TIPO CAMBIO : '.$reporte_array['CABECERA DATOS']['TIPO CAMBIO'].'  ';
    
    $reporte->definirCabecera(5, "L", $DatosCabecera);
    $reporte->definirCabecera(6, "R", " ");
    $reporte->definirCabecera(7, "L", $DatosCabecera2);
    $reporte->definirCabecera(8, "R", " ");
    /*$reporte->Ln();
    $reporte->cell(0,10,'Fecha : '.$reporte_array['CABECERA']['FECHA'].'',1,0,'L');
    $reporte->Lnew();*/

    $reporte->definirCabeceraPredeterminada($Detalle);
    
    $reporte->AddPage();

    $datos = array();
    foreach($reporte_array['DETALLE'] as $llave => $valor)
    {
	

	if(!ereg("CAB", $llave, $regs))
	{

	    $datos['CODIGO']       = $valor['ART CODIGO'];
	    $datos['DESCRIPCION']  = $valor['ART DESCRIPCION'];
	    //$datos['NRO_O_C']      = $valor['NUM COMPRA'];
	    $datos['UNI']          = $valor['ART UNIDAD'];
	    $datos['CANTIDAD']     = money_format("%.2n", round($valor['CANTIDAD'],2));
	    //if(ereg('S/',$reporte_array['CABECERA DATOS']['MONEDA'])){
	      //echo "ENTRO IF\n";
	      $datos['CTOTAL']       = money_format("%.2n", round($valor['COSTO TOTAL'],2));
	      $datos['CDOLTOTAL']    = money_format("%.2n", round(($valor['COSTO TOTAL']/$reporte_array['CABECERA DATOS']['TIPO CAMBIO']),2));
	    /*}else{
	      //echo "ENTRO ELSE\n";
	      $datos['CDOLTOTAL']       = money_format("%.2n", round($valor['COSTO TOTAL'],2));
	      $datos['CTOTAL']    = money_format("%.2n", round(($valor['COSTO TOTAL']*$reporte_array['CABECERA DATOS']['TIPO CAMBIO']),2));
	    }*/
	    
	    $datos['CUNIIGV']      = money_format("%.4n", round(($valor['COSTO UNITARIO'] + ($valor['COSTO UNITARIO'] * $IGV)),4));
	    $datos['PREUNIIGV']    = money_format("%.2n", round($valor['PRECIO UNITARIO'],2));
	    $datos['DIFIGV']       = money_format("%.4n", round(($valor['PRECIO UNITARIO'] - $datos['CUNIIGV']),4));
	    $datos['PORCENTAJE']   = money_format("%.2n", round((($datos['DIFIGV']/$datos['PREUNIIGV']) * 100), 2));
	    $datos['STOCK']        = money_format("%.2n", round($valor['STOCK'], 2));
	    $reporte->nuevaFila($datos);

            $datosTot['CTOTAL'] += $datos['CTOTAL'];
            $datosTot['CDOLTOTAL'] += $datos['CDOLTOTAL'];
	}else{

	    $reporte->Ln();
	    $reporte->cell(0,10,'Formulario de Compra :'.$valor['FORMULARIO COMPRA'].'   Orden de Compra :'.$valor['ORDEN COMPRA'].'   Doc. Ext. :'.$valor['DOC EXT'].'    Fecha :'.$valor['FECHA'].'',1,0,'L');
	    $reporte->Lnew();
	}
	
	
    }
    $reporte->Ln();
    $reporte->LineaH();
    $datosTot['CTOTAL'] = money_format("%.2n", round($datosTot['CTOTAL'],2));
    $datosTot['CDOLTOTAL'] = money_format("%.2n", round($datosTot['CDOLTOTAL'],2));
    $reporte->nuevaFila($datosTot);
    $reporte->LineaH();
    $reporte->Output("/sistemaweb/cpagar/cintillo_liquidacion_compras.pdf", "F");
    return '<script> window.open("/sistemaweb/cpagar/cintillo_liquidacion_compras.pdf","miwin","width=750,height=550,scrollbars=yes, resizable=yes, menubar=no");</script>';
    }
}//Fin :: Clase SobraFaltaReporteTemplate
