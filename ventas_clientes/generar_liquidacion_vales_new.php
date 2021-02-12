<?php

include "../valida_sess.php";
include_once('/sistemaweb/include/dbsqlca.php');
include_once('/sistemaweb/include/reportes2.inc.php');

$sqlca = new pgsqlDB('localhost','postgres', 'postgres', 'integrado');

class LiquidacionPDFTemplate {
  
  function ObtNumVales($NroDocumento, $sucursal, $fecha) {
    global $sqlca;

	  $query = "
SELECT
 ch_numeval
FROM
 val_ta_complemento
WHERE
 ch_documento='".$NroDocumento."'
 AND ch_sucursal='".$sucursal."'
 AND dt_fecha='".$fecha."';
    ";

	  $sqlca->query($query);
    $contador = $sqlca->numrows();
    $c = 1;
    $x = 0;
    $z = 0;
    $cuentatodo=0;
    
    while($reg = $sqlca->fetchRow()){
      if($c == $contador)
        $coma = "";
      else
        $coma = ",";

      if($x>0 && ($x%4)==0){
        $z++;
        $registros['NUMEROS'][$z][] .= $reg['ch_numeval'].@$coma;
      }else{
        $registros['NUMEROS'][$z][] .= $reg['ch_numeval'].@$coma;
      }

      $c++;
      $x++;
    }
		
    if ($x==0) {
			$registros['NUMEROS'][0][]=" ";
    }
    
		$registros['CANT'] = $x;
		return $registros;
  }

  function GeneraDatos() {
    global $sqlca;

    for($liq = $_REQUEST['desde']; $liq<=$_REQUEST['hasta']; $liq++){
      $liquidacion .= "'".pg_escape_string(str_pad($liq, 10, "0", STR_PAD_LEFT))."'";
      if($liq!=$_REQUEST['hasta'])
        $liquidacion .=",";
    }
    
    $query = "
SELECT
 FIRST(val_liq.ch_numeval) AS ch_documento,
 FIRST(val_liq.ch_cliente) AS ch_cliente,
 FIRST(cli.cli_razsocial) AS Cli_RazSocial,
 FIRST(val_cab.dt_fecha) AS dt_fecha,
 SUM(val_liq.nu_fac_valortotal) AS nu_importe,
 val_det.ch_articulo AS ch_articulo, --AGRUPADO
 SUM(val_det.nu_cantidad) as art_cantidad,		
 SUM(val_det.nu_importe) as art_importe,
 art.art_descripcion AS art_descripcion, --AGRUPADO
 FIRST(val_liq.ch_liquidacion) AS ch_liquidacion, 
 FIRST(val_liq.ch_sucursal) AS ch_sucursal,
 (CASE WHEN val_det.nu_cantidad > 0 THEN ROUND(val_det.nu_importe / val_det.nu_cantidad, 4) ELSE 0 END) AS art_precio, --AGRUPADO
 FIRST(val_cab.ch_placa) AS placa,
 FIRST(pos.nomusu) AS conductor,
 (SELECT numpla FROM pos_fptshe1 WHERE pos_fptshe1.numpla = FIRST(val_cab.ch_placa) LIMIT 1),
 FIRST(val_liq.dt_fecha) AS fecha_insercion,
 FIRST(val_cab.fecha_replicacion) AS dt_fechaactualizacion
FROM
 val_ta_complemento_documento AS val_liq
 LEFT JOIN val_ta_cabecera AS val_cab ON(val_cab.ch_cliente = val_liq.ch_cliente AND val_liq.ch_numeval = val_cab.ch_documento AND val_liq.dt_fecha = val_cab.dt_fecha)
 LEFT JOIN val_ta_detalle AS val_det ON(val_liq.ch_numeval = val_det.ch_documento AND val_liq.art_codigo = val_det.ch_articulo AND val_liq.dt_fecha = val_det.dt_fecha)
 --LEFT JOIN val_ta_cabecera val_cab ON (val_cab.ch_cliente = val_liq.ch_cliente AND val_liq.ch_numeval = val_cab.ch_documento AND val_liq.dt_fecha = val_cab.dt_fecha AND val_liq.ch_sucursal = val_cab.ch_sucursal)
 --LEFT JOIN val_ta_detalle val_det ON (val_liq.ch_numeval = val_det.ch_documento AND val_liq.art_codigo = val_det.ch_articulo AND val_liq.dt_fecha = val_det.dt_fecha AND val_liq.ch_sucursal = val_det.ch_sucursal)
 LEFT JOIN int_clientes AS cli ON(cli.cli_codigo = val_liq.ch_cliente)
 LEFT JOIN int_articulos AS art ON(art.art_codigo = val_liq.art_codigo)
 LEFT JOIN pos_fptshe1 AS pos ON(pos.numpla = val_cab.ch_placa AND pos.numtar = val_cab.ch_tarjeta AND pos.codcli = val_cab.ch_cliente)
WHERE
 val_liq.ch_liquidacion IN(".$liquidacion.")
GROUP BY
 6,9,12
ORDER BY
 --val_liq.ch_liquidacion,
 --val_cab.ch_cliente,
 --val_cab.ch_documento;
 10,2,1;
    ";

    if($sqlca->query($query)<=0)
      return $sqlca->get_error();

    while($reg = $sqlca->fetchRow()){
      $registros['CLIENTES'][trim($reg[1])."  ".trim($reg[2])][] = $reg;
      $registrosFinal[$reg[9]]['CLIENTES'][trim($reg[1])."  ".trim($reg[2])][] = $reg;
    }
    
    $query =
"SELECT ".
 "ch_fac_tipodocumento, ".
 "cli_codigo, ".
 "ch_liquidacion, ".
 "SUBSTR(ch_fac_seriedocumento,0,5)||'-'||LPAD(trim(ch_fac_numerodocumento),7,'0') AS nro_documento, ".
 "LPAD(TRIM(ch_fac_numerodocumento),7,'0') AS ch_fac_numerodocumento ".
"FROM ".
 "fac_ta_factura_cabecera ".
"WHERE ".
 "ch_liquidacion IN(".$liquidacion.")
    ";

    $sqlca->query($query);
    while($reg = $sqlca->fetchRow()){
      $registrosDoc[$reg['ch_liquidacion']][$reg['ch_fac_tipodocumento']."-".$reg['ch_fac_numerodocumento']] = $reg;
    }

    $query =
"SELECT ".
 "ch_tipdocumento, ".
 "cli_codigo, ".
 "ch_numdocreferencia as ch_liquidacion, ".
 "SUBSTR(ch_seriedocumento,0,5)||'-'||LPAD(trim(ch_numdocumento),7,'0') as nro_documento, ".
 "LPAD(trim(ch_numdocumento),7,'0') as ch_numdocumento ".
"FROM ".
 "ccob_ta_cabecera ".
"WHERE ".
 "ch_numdocreferencia IN(".$liquidacion.") ";
              
    $sqlca->query($query);
    while($reg = $sqlca->fetchRow()){
      $registrosDoc[$reg['ch_liquidacion']][$reg['ch_tipdocumento']."-".$reg['ch_numdocumento']] = $reg;
    }

    $DatosFinales['DatosReg'] = $registrosFinal;
    $DatosFinales['DatosDoc'] = $registrosDoc;
    // echo "<script>console.log('REQUEST: " . json_encode($DatosFinales) . "')</script>";

    return $DatosFinales;
  }

  function getDireccionSucursal($str,$str2) {
		$result = '';
		$del = '|';
		return explode($del, $str);
	}

	function datosEmpresa($iAlmacen) {
		global $sqlca;
		/* Get ebiauth: Por almacen y si no encuentra que lo obtenga sin el almacen */
		$sqlca->query("
		SELECT
			SUCUR.ruc,
			SUCUR.razsocial,
			SUCUR.ch_direccion
		FROM
			inv_ta_almacenes ALMA
			JOIN int_ta_sucursales SUCUR ON (SUCUR.ch_sucursal = ALMA.ch_sucursal)
		WHERE
			SUCUR.ebikey IS NOT NULL AND SUCUR.ebikey != ''
			AND ALMA.ch_clase_almacen = '1'
        	AND ALMA.ch_sucursal = '" . $iAlmacen . "'
		");
		$row = $sqlca->fetchRow();

		if(trim($row['ruc']) != '') {
			$res['ruc'] 		= trim($row['ruc']);
			$res['razsocial'] 	= trim($row['razsocial']);
			$arrDireccion = $this->getDireccionSucursal(trim($row['ch_direccion']), '');
			$res['direccion']	= $arrDireccion[1] . " " . $arrDireccion[2] . " "  . $arrDireccion[3] . " - "  . $arrDireccion[4] . " - "  . $arrDireccion[5];
		} else {
			$sqlca->query("
			SELECT DISTINCT
				SUCUR.ruc,
				SUCUR.razsocial,
				SUCUR.ch_direccion
			FROM
				inv_ta_almacenes ALMA
				JOIN int_ta_sucursales SUCUR ON (SUCUR.ch_sucursal = ALMA.ch_sucursal)
			WHERE
				SUCUR.ebikey IS NOT NULL AND SUCUR.ebikey != ''
				AND ALMA.ch_clase_almacen = '1'
			");
			$row = $sqlca->fetchRow();

			$res['ruc'] 		= trim($row['ruc']);
			$res['razsocial'] 	= trim($row['razsocial']);
			$arrDireccion = $this->getDireccionSucursal(trim($row['ch_direccion']), '');
			$res['direccion']	= $arrDireccion[1] . " " . $arrDireccion[2] . " "  . $arrDireccion[3] . " - "  . $arrDireccion[4] . " - "  . $arrDireccion[5];
		}
		return $res;
	}

  function reportePdf(){
    $reporte_array = $this->GeneraDatos();
    
    $liquidacion = $_REQUEST['desde'];
    $liquidacion = "".pg_escape_string(str_pad($liquidacion, 10, "0", STR_PAD_LEFT))."";
    $liquidacion = $reporte_array['DatosDoc'][$liquidacion][10]['ch_liquidacion'];
    ksort($reporte_array['DatosDoc'][$liquidacion]);
    //$Factura = "NRO DE FACTURA : ".$reporte_array['DatosDoc'][$liquidacion][10]['nro_documento'];
    //$NotaCredito = "NRO DE NOTA DE CREDITO : ".$reporte_array['DatosDoc'][$liquidacion][20]['nro_documento'];
    $Cabecera = array( 
		    // "DT_FECHA"        => "FECHA",
		    // "CH_DOCUMENTO"    => "# DESPACHO",
		    "CH_ARTICULO"     => "ARTICULO",
		    // "CH_PLACA"        => "PLACA",
		    // "CH_CONDUCTOR"    => "CONDUCTOR",
		    "ART_DESCRIPCION" => "DESCRIPCION",
		    "ART_CANTIDAD"    => "CANTIDAD",
		    "ART_PRECIO"      => "PRECIO",
		    "ART_IMPORTE"     => "IMPORTE",
		    // "NUMVALES"        => "NUMERACION VALES",
		    );

    //$Totales_new = array_merge_recursive($Totales, $Totales2);
    //print_r($reporte_array);                  <font style='font-size:7px'>
    $fontsize = 7;

    $reporte = new CReportes2();
    $reporte->SetMargins(5, 5, 5);
    $reporte->SetFont("courier", "", $fontsize);
    
    $reporte->definirColumna("CABECERA CLIENTE", $tipo->TIPO_TEXT, 100, "L", "_cabecera");	
    // $reporte->definirColumna("DT_FECHA", $reporte->TIPO_TEXTO, 10, "L");
    // $reporte->definirColumna("CH_DOCUMENTO", $reporte->TIPO_TEXTO, 12, "L");
    $reporte->definirColumna("CH_ARTICULO", $reporte->TIPO_TEXTO, 15, "L");
    // $reporte->definirColumna("CH_PLACA", $reporte->TIPO_TEXTO, 8, "L");
    // $reporte->definirColumna("CH_CONDUCTOR", $reporte->TIPO_TEXTO, 21, "L");
    $reporte->definirColumna("ART_DESCRIPCION", $reporte->TIPO_TEXTO, 28, "L");
    $reporte->definirColumna("ART_CANTIDAD", $reporte->TIPO_TEXTO, 15, "L");
    $reporte->definirColumna("ART_PRECIO", $reporte->TIPO_TEXTO, 10, "L");
    $reporte->definirColumna("ART_IMPORTE", $reporte->TIPO_COSTO, 15, "L");
    // $reporte->definirColumna("NUMVALES", $reporte->TIPO_TEXTO, 100, "L");
    
    $reporte->definirColumna("TOTALES X LIQ", $reporte->TIPO_TEXTO, 91, "R", "_totliq");	
    $reporte->definirColumna("TOTDESPACHOS", $reporte->TIPO_TEXTO, 91, "L", "_totdespachos");	
    $reporte->definirColumna("TOTNROVALES", $reporte->TIPO_TEXTO, 91, "L", "_totnrovales");	
    
    $reporte->definirColumna("TOTALES", $reporte->TIPO_TEXTO, 91, "R", "_totales");	

    
    /* TITULO Y DATOS DE LA EMPRESA */
		$reporte->definirCabecera(1, "L", "SISTEMA WEB");
		$reporte->definirCabecera(1, "C", "LIQUIDACION DE FACTURAS");
		$reporte->definirCabecera(1, "R", "PAG.%p");
		$reporte->definirCabecera(2, "L", " ");
		$reporte->definirCabecera(3, "L", "RAZON SOCIAL: " . $data['razsocial']);
		$reporte->definirCabecera(4, "L", "         RUC: " . trim($data['ruc']));
		$reporte->definirCabecera(5, "L", "   DIRECCION: " . trim($data['direccion']));
		$reporte->definirCabecera(6, "L", " ");
		$reporte->definirCabecera(7, "L", "Nro de Liquidacion :  ");
		$reporte->definirCabeceraPredeterminada($Cabecera);
    
    //$reporte->AddPage();

    $datos = array();
    $c = 0;
    $x = 0;
		

    foreach ($reporte_array['DatosReg'] as $nro_liq => $valoresArray) {
      foreach ($valoresArray['CLIENTES'] as $llave => $valores) {
          $iAlmacen = trim($reporte_array['DatosReg'][$nro_liq]["CLIENTES"][trim($llave)][0]['ch_sucursal']);
          $data = $this->datosEmpresa($iAlmacen);
      }
    }

    foreach($reporte_array['DatosReg'] as $nro_liq => $valoresArray)
    {
      error_log("****");
      error_log($nro_liq);
      //echo "llave1 : $nro_liq => valor1 : ".$valoresArray."\n";
      if(!empty($nro_liq)){
      //echo "NRO LIQU. : $nro_liq <br>";
        ksort($reporte_array['DatosDoc'][$nro_liq]);
        //print_r($reporte_array['DatosDoc'][$nro_liq]);
//	$x=0;
	$reporte->templates = Array();
	$reporte->cabecera = Array();
	$reporte->cabeceraImagen = Array();
	$reporte->cabeceraSize = Array();
	$reporte->cab_default = Array();

	$reporte->definirColumna("CABECERA CLIENTE", $tipo->TIPO_TEXT, 100, "L", "_cabecera");	
	// $reporte->definirColumna("DT_FECHA", $reporte->TIPO_TEXTO, 10, "L");
	// $reporte->definirColumna("CH_DOCUMENTO", $reporte->TIPO_TEXTO, 12, "L");
	$reporte->definirColumna("CH_ARTICULO", $reporte->TIPO_TEXTO, 15, "L");
	// $reporte->definirColumna("CH_PLACA", $reporte->TIPO_TEXTO, 8, "L");
	// $reporte->definirColumna("CH_CONDUCTOR", $reporte->TIPO_TEXTO, 21, "L");
	$reporte->definirColumna("ART_DESCRIPCION", $reporte->TIPO_TEXTO, 28, "L");
	$reporte->definirColumna("ART_CANTIDAD", $reporte->TIPO_TEXTO, 15, "L");
	$reporte->definirColumna("ART_PRECIO", $reporte->TIPO_TEXTO, 10, "L");
	$reporte->definirColumna("ART_IMPORTE", $reporte->TIPO_COSTO, 15, "L");
	// $reporte->definirColumna("NUMVALES", $reporte->TIPO_TEXTO, 100, "L");

        $reporte->definirColumna("TOTALES X LIQ", $reporte->TIPO_TEXTO, 134, "R", "_totliq");	
	$reporte->definirColumna("TOTALES", $reporte->TIPO_TEXTO, 91, "R", "_totales");	
	$reporte->definirColumna("TOTDESPACHOS", $reporte->TIPO_TEXTO, 91, "L", "_totdespachos");	
        $reporte->definirColumna("TOTNROVALES", $reporte->TIPO_TEXTO, 91, "L", "_totnrovales");	
	
	$reporte->definirColumna("BLANCO", $reporte->TIPO_TEXTO, 91, "R", "_nrovales");	
	$reporte->definirColumna("NROVALES2", $reporte->TIPO_TEXTO, 91, "L", "_nrovales");	
	
	$reporte->definirCabecera(1, "L", "SISTEMA WEB");
  $reporte->definirCabecera(1, "C", "LIQUIDACION DE FACTURAS");
  $reporte->definirCabecera(1, "R", "PAG.%p");
  $reporte->definirCabecera(2, "L", " ");
  $reporte->definirCabecera(3, "L", "RAZON SOCIAL: " . $data['razsocial']);
  $reporte->definirCabecera(4, "L", "	        RUC: " . trim($data['ruc']));
  $reporte->definirCabecera(5, "L", "   DIRECCION: " . trim($data['direccion']));
  $reporte->definirCabecera(6, "L", " ");
  $reporte->definirCabecera(7, "R", " " . $nro_liq);
  
	foreach($reporte_array['DatosDoc'][$nro_liq] as $tipDoc => $value)
	{
	   error_log($tipDoc);
	   if (preg_match("/10-/i", $tipDoc)) {
	     if($cliente == $value['cli_codigo'] && !empty($tipDoc)){
                $CODF .= $value['nro_documento'].",";
                 $TIP='10';
                //$Factura = "FAC.: ".$CODF;
             }elseif($cliente != $value['cli_codigo'] && !empty($tipDoc)){
                $CODN = " ";
                $CODF = $value['nro_documento'].",";
                 $TIP='10';
                //$Factura = "FAC.: ".$CODF;
             }
	   }
	   
	   if (preg_match("/35-/i", $tipDoc)) {
	    // echo $cliente."br>;
			 if($cliente == $value['cli_codigo'] && !empty($tipDoc)){
                $CODF .= $value['nro_documento'].",";
                $TIP='35';
                //$Factura = "BOL.: ".$CODF;
             }elseif($cliente != $value['cli_codigo'] && !empty($tipDoc)){
                $CODN = " ";
                $CODF = $value['nro_documento'].",";
                $TIP='35';
               // $Factura = "BOL.: ".$CODF;
             }
	   }
	   
	   
	   
	   if (preg_match("/22-/i", $tipDoc)) {
	     if($cliente != $value['cli_codigo'] && !empty($tipDoc)){
                $CODN = " ";
                $CODF = "RESUMEN : ".$value['nro_documento'];
                //$Factura = "RESUMEN FAC.: ".$CODF;
             }
	   }
	   
	   if (preg_match("/20-/i", $tipDoc)) {
	     if($cliente == $value['cli_codigo'] && !empty($tipDoc))
             { 
               $CODN .= $value['nro_documento'].",";
               $NotaCredito = "N.C. : ".$CODN;
             }elseif($cliente != $value['cli_codigo'] && !empty($tipDoc)){
              $CODN = $value['nro_documento'].",";
               $NotaCredito = "N.C. : ".$CODN;
             }
	   }
	   
	   $cliente = $value['cli_codigo'];
	   $CODN?$MsgCodn="NC.: ".$CODN."":$MsgCodn=" ";
	   $reporte->definirCabecera(7, "L", "Nro de Liquidacion: ".$nro_liq.($TIP=="10"?"  FAC.: ":"  BOL.: ").$CODF."  ".$MsgCodn." ");
	}

	$reporte->definirCabecera(8, "R", " ");
	$reporte->definirCabeceraPredeterminada($Cabecera);
        $reporte->AddPage();
        
      }
      
      
      
      
      
      foreach($valoresArray['CLIENTES'] as $llave => $valores)
      {
			
			
	if(!empty($llave)){
	   
	    $Despachos = count($valores);
	    $arrayCab = Array("CABECERA CLIENTE"=>"CLIENTE : ".trim($llave));
	    $reporte->lineaH();
	    $reporte->Ln();
	    $reporte->nuevaFila($arrayCab, "_cabecera");
	    $reporte->lineaH();
	    $reporte->Ln();
	}
      
        $CantNroVales = 0;
				$CantNrodespacho = 0;
				$varNrodespacho="";
				$varNrovales="";
	
	
	

	
	
	foreach($valores as $key => $value){
	
	 //echo "llave : $key => valor : ".$value['ch_documento']."\n";
	  // $datos['DT_FECHA']        = $value['dt_fecha'];
	  // $datos['CH_DOCUMENTO']    = $value['ch_documento'];
	  $datos['CH_ARTICULO']     = $value['ch_articulo'];
	  // $datos['CH_PLACA']     = $value['placa'];
	  // $datos['CH_CONDUCTOR']     = $value['conductor'];
	  $datos['ART_DESCRIPCION'] = $value['art_descripcion'];
	  $datos['ART_CANTIDAD']     = $value['art_cantidad'];
	  $datos['ART_PRECIO']     = $value['art_precio'];
	  $datos['ART_IMPORTE']     = $value['art_importe'];
	  $ImporteTotal +=$datos['ART_IMPORTE']; 
		
		
	if ($varNrodespacho != $value['ch_documento'])
	{
			$CantNrodespacho++;
	}
	
	$varNrodespacho=$value['ch_documento'];
	

			
	
	
	
	
	  //print_r($datos);
  	  $NumVales = LiquidacionPDFTemplate::ObtNumVales($value['ch_documento'],$value['ch_sucursal'],$value['dt_fecha']);
			

			
				
          //echo "\n<br>CANT : ".$NumVales['CANT']."<br>\n";
          //print_r($NumVales['NUMEROS']);
          $x=0;
          foreach($NumVales['NUMEROS'] as $cod => $nro_vale)
          {
           $x++;
            //echo "COD : $cod<br>";
            //print_r($nro_vale);
            $NumValesFinal = "";
            foreach($nro_vale as $numeros)
            {
              $NumValesFinal.= $numeros;
							
							//echo $numeros;
            		
								if (($varNrovales!= $numeros) and ($numeros!=" "))
									{
										$CantNroVales++;
            			}
									
									$varNrovales=$numeros;
						}
            //$NumValesFinal .= 
            //echo "NUM FINAL : $NumValesFinal<br>";
            //echo "X = $x<br>";
            if($NumVales['CANT']>4 && $x>1){
              $ArrayNumeros = array("BLANCO"=>" ", "NROVALES2"=>$NumValesFinal);
              $reporte->nuevaFila($ArrayNumeros, "_nrovales");
            }else{
              $datos['NUMVALES']     = $NumValesFinal;
							
				
							
 	      $reporte->nuevaFila($datos);
           }
          }
	  //$datos['NUMVALES']     = $NumVales['NUMEROS'];
        $c++;
        $x++;
	}



      }
			

      
      //echo "CANT NRO VALES : $CantNroVales<br>";
	if(!empty($nro_liq)){
	    //echo "CANT : ".count($valores)."";
          $reporte->Ln();
	    $Total = 0;
	    for($i=0; $i<count($valores); $i++)
	    {
	       $Total += $valores[$i]['art_importe'];
	    }
	    $Total = number_format($Total, 4, ".", ",");
	    $arrayCab = Array("TOTALES X LIQ"=>"         TOTAL : ".$Total);
	    // $arrayDesp = Array("TOTDESPACHOS"=>"         CANTIDAD TOTAL DE DESPACHOS : ".$CantNrodespacho);
	    // $arrayVales = Array("TOTNROVALES"=>"         CANTIDAD TOTAL DE NROS DE VALES : ".$CantNroVales);
	    $reporte->lineaH();
	    $reporte->Ln();
	    $reporte->nuevaFila($arrayCab, "_totliq");
	    $reporte->nuevaFila($arrayDesp, "_totdespachos");
	    $reporte->nuevaFila($arrayVales, "_totnrovales");
	    $reporte->Ln();
	    $reporte->lineaH();
	    $reporte->Ln();
	    $x=0;
	}
	
	$TotCantNroVales += $CantNroVales;

    }
    //print_r($Totales);
    //echo "TotCantNroVales : $TotCantNroVales<br>";
    //$ImporteTotal = number_format($ImporteTotal, 4, ".", ",");
    //$Totales = $c;
    
		
		//$reporte->Ln();
    //$reporte->lineaH();
    //$reporte->Ln();
    //$arrayCab = Array("TOTALES"=>"  Total Cantidad de Notas de Despacho : ".$Totales."    TOT. IMPORTES : ".$ImporteTotal."");
    //$reporte->nuevaFila($arrayCab, "_totales");
    //$reporte->Ln();
    //$reporte->lineaH();
    
    return $reporte->Output("/sistemaweb/ventas_clientes/reportes/pdf/liquidacion_facturas.pdf", "I");
  }
}

// echo "<script>console.log('REQUEST: " . json_encode($_REQUEST) . "')</script>";

$ReportePdf = new LiquidacionPDFTemplate();

print_r($ReportePdf->reportePdf());



?>
