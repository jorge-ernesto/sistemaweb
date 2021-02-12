<?php

class SobraFaltaModel extends Model
{
    function TotalCosto($AlmCod="", $mes="",$anio)
    {
    global $sqlca;
	$query = "SELECT sum(stk_costo".$mes.") as TotalCosto 
		  FROM inv_saldoalma 
		  WHERE stk_periodo='".$anio."' 
		  AND stk_almacen='".$AlmCod."'";
        //echo "QUERY : $query \n";
	//if($sqlca->query($query, 'rows') <= 0)
	if ($sqlca->query($query,'rows') < 0) return -1;
	while($Row = $sqlca->fetchRow('rows'))
	{
            $TotalCosto = $Row[0];
	}
	//SELECT art_codigo, stk_costo11 FROM inv_saldoalma WHERE stk_periodo='2006' AND stk_almacen='002' AND art_codigo in ('11620301', '11620302', '11620303', '11620304', '11620305', '11620306', '11620307') ORDER BY 1;
	//$TotalCosto = $Row[0];
	//echo "TOTAL COSTO : $TotalCosto \n";
    return $TotalCosto;
    }

    function CostosTanques($ArtCod="", $AlmCod="", $Mes="", $Anio)
    {
    global $sqlca;
      $query = "SELECT ".
                       "stk_costo".$Mes." ".
               "FROM inv_saldoalma ".
               "WHERE stk_periodo='".$Anio."' ".
               "AND stk_almacen='".$AlmCod."' ".
               "AND art_codigo = '".$ArtCod."' ".
               "ORDER BY 1";
        echo "QUERY : $query \n";
	if ($sqlca->query($query,'rows') < 0) return -1;
	while($Row = $sqlca->fetchRow('rows'))
	{
            $CbCostos = $Row[0];
	}
    return $CbCostos;
    }
    
    function TanquesArray($cdSuc="", $cdTan="")
    {
    global $sqlca;
	$query = "SELECT  ch_tanque, 
			ch_sucursal, 
			ch_codigocombustible 
		FROM comb_ta_tanques 
		WHERE ch_tanque<>'1' 
		AND ch_tanque<>'2' 
		AND ch_tanque<>'3' 
		AND ch_tanque<>'4' 
		AND ch_tanque<>'5' 
		    ".@$cdSuc."
		    ".@$cdTan."
		ORDER BY ch_tanque,ch_sucursal"; 
	//echo "QUERY TANQUE : $query \n";
	//if ($sqlca->query($query,'tanques')<=0)
	if ($sqlca->query($query,'tanques') < 0) return -1;
	while($Rows = $sqlca->fetchRow('tanques'))
	{
	    $TanquesArray[$Rows[0]][$Rows[1]] = $Rows[2];
	}
    //print_r($TanquesArray);
    return $TanquesArray;
    }
    
    function ComprasArray($cdSuc="", $cdTan="", $cdAlm="", $fi, $ff)
    {
    global $sqlca;
        $TanquesArray = SobraFaltaModel::TanquesArray($cdSuc, $cdTan);
        foreach($TanquesArray as $codta => $valorArray)
        {
        //echo "ENTRO F1\n";
            foreach($valorArray as $llave => $artCod)
            {
               //echo "ENTRO => ARTCOD : $artCod\n";
		if($cdAlm || count($artCod))
		{
		  //echo "ENTRO IF F2\n";
		    $cdComb = " AND art_codigo='".$artCod."'";
		    $MovAlmArray = VariosModel::MoviAlmacenCBArray($cdAlm, $cdComb, $fi, $ff, $codta,'21');
		    if($MovAlmArray>0)
		    {
			foreach($MovAlmArray as $key => $value)
			{
			    $CompraArray[$key] = $value; //Arreglo para las compras
			}
		    }
		}
            }
        }
    return $CompraArray;
    }
    
    function MedicionArray($cdSuc="", $cdTan="", $fi, $ff)
    {
    global $sqlca;
        $query = "SELECT ch_sucursal AS sucursal, 
			ch_tanque AS tanque,
			to_char(dt_fechaparte,'DD-MM-YYYY') AS fecha, 
			sum(nu_afericionveces_x_5*5) AS medicion 
		FROM comb_ta_contometros
		WHERE dt_fechaparte >= to_date('$fi','dd-mm-yyyy')
		AND dt_fechaparte <= to_date('$ff','dd-mm-yyyy')
		".@$cdSuc."
		".@$cdTan."
		GROUP BY fecha, sucursal, tanque 
		ORDER BY sucursal, tanque, fecha";
        //echo "QUERY : $query \n";
        //if ($sqlca->query($query,'afericion')<=0)
        if ($sqlca->query($query,'afericion') < 0) return -1;
        while($Rows = $sqlca->fetchRow('afericion'))
        {
            $MedicionArray[$Rows[0]][$Rows[1]][$Rows[2]] = $Rows[3];
        }
    return $MedicionArray;
    }
    
    function SaldoArray($cdSuc="", $cdTan="", $fi, $ff)
    {
    global $sqlca;
    
        $query = "SELECT ch_sucursal AS sucursal, 
		           ch_tanque AS tanque,
                           to_char(dt_fechamedicion,'DD-MM-YYYY') AS fecha,
                           nu_medicion AS saldo
                FROM comb_ta_mediciondiaria
                WHERE dt_fechamedicion >= to_date('$fi','dd-mm-yyyy')
                AND dt_fechamedicion <= to_date('$ff','dd-mm-yyyy')
                ".@$cdSuc."
		".@$cdTan."
                GROUP BY fecha, saldo, sucursal, tanque 
                ORDER BY sucursal,tanque,fecha";
        //echo "QUERY : $query \n";
        //if ($sqlca->query($query,'saldos')<=0)
        if ($sqlca->query($query,'saldos') < 0) return -1;
        //return $cbArray;
        while($Rows = $sqlca->fetchRow('saldos'))
        {
            $SaldoArray[$Rows[0]][$Rows[1]][$Rows[2]] = $Rows[3];
        }
    return $SaldoArray;
    }
    
    function VentaArray($cdSuc="", $cdTan="", $fi, $ff)
    {
    global $sqlca;
	$query = "SELECT ch_sucursal AS sucursal, 
			 ch_tanque AS tanque,
			 to_char(dt_fechaparte,'DD-MM-YYYY') AS fecha,
		         sum(nu_ventagalon) AS venta
		  FROM comb_ta_contometros
		  WHERE dt_fechaparte >= to_date('$fi','dd-mm-yyyy')
		  AND dt_fechaparte <= to_date('$ff','dd-mm-yyyy')
		  ".@$cdSuc."
		  ".@$cdTan."
		  GROUP BY sucursal,tanque, fecha
		  ORDER BY sucursal,tanque, fecha";
        //echo "QUERY : $query \n";
        if ($sqlca->query($query,'venta') < 0) return -1;
        while($Rows = $sqlca->fetchRow('venta'))
        {
            $VentaArray[$Rows[0]][$Rows[1]][$Rows[2]] = $Rows[3];
        }
    //print_r($VentaArray);
    return $VentaArray;
    }
    
    function IngresoArray($cdSuc="", $cdTan="", $cdAlm="", $fi, $ff)
    {
    global $sqlca;
        $TanquesArray = SobraFaltaModel::TanquesArray($cdSuc, $cdTan);
        foreach($TanquesArray as $codta => $valorArray)
        {
            foreach($valorArray as $llave => $artCod)
            {
		if($cdAlm || count($artCod))
		{
		    $cdComb = " AND art_codigo='".$artCod."'";
		    $MovAlmArray = VariosModel::MoviAlmacenCBArray($cdAlm, $cdComb, $fi, $ff, $codta,'27');
		    if($MovAlmArray>0)
		    {
			foreach($MovAlmArray as $key => $value)
			{
			    $IngresoArray[$key] = $value; //Arreglo para las Ingreso
			}
		    }
		}
            }
        }
    
    return $IngresoArray;
    }
    
    function SalidaArray($cdSuc="", $cdTan="", $cdAlm="", $fi, $ff)
    {
    global $sqlca;
        $TanquesArray = SobraFaltaModel::TanquesArray($cdSuc, $cdTan);
        foreach($TanquesArray as $codta => $valorArray)
        {
            foreach($valorArray as $llave => $artCod)
            {
		if($cdAlm || count($artCod))
		{
		    $cdComb = " AND art_codigo='".$artCod."'";
		    $MovAlmArray = VariosModel::MoviAlmacenVarTCBArray($cdAlm, $cdComb, $fi, $ff, $codta,'22','28');
		    if($MovAlmArray>0)
		    {
			foreach($MovAlmArray as $key => $value)
			{
			    $SalidaArray[$key] = $value; //Arreglo para las Ingreso
			}
			
		    }
		}
            }
        }
    
    return $SalidaArray;
    }
        
    function SobraFaltaReporte($datos)
    {
         global $sqlca;

	$SucCB     = VariosModel::sucursalCBArray();
	$TanCB     = VariosModel::tanquesCBArray();
        $fechai = $datos['dia_i'].'-'.$datos['mes_i'].'-'.$datos['anio_i'];
        $fechaf = $datos['dia_f'].'-'.$datos['mes_f'].'-'.$datos['anio_f'];

        $time = mktime(0, 0, 0, $datos['mes_i'], $datos['dia_i'], $datos['anio_i']);
        $fechai_1 = date("d-m-Y", ($time-1));
        //echo "FECHA 2 ".$fechai_1."\n";
        
        //$CodTanCB = VariosModel::CodtanquesCBArray();
        
        if($datos['mes_f']<9) $datos['mes_f'] = '0'.$datos['mes_f'];
        
        
        if($datos['sucursal'] != "all")
        {
	  $query_Suc = " AND ch_sucursal=trim('".$datos['sucursal']."')";
	  $query_Alm = " AND mov_almacen=trim('".$datos['sucursal']."')";
	  //$ResultTotalesCosto[$datos['sucursal']] = SobraFaltaModel::TotalCosto($datos['sucursal'], $datos['mes_f'], $datos['anio_f']);
        }/*else{
	  foreach($SucCB as $llave => $valor)
	  {
	    if($llave != 'all')
	    {
	     //$ResultTotalesCosto[$llave] = SobraFaltaModel::TotalCosto($llave, $datos['mes_f'], $datos['anio_f']);
	     foreach($CodTanCB as $cod_tanque => $cod_art)
	     {
	       //echo "COD TAN : $cod_tanque => COD ART : $cod_art \n";
	       //$ResultCostoTanques[$cod_tanque] = SobraFaltaModel::CostosTanques($cod_art, $llave, $datos['mes_f'], $datos['anio_f']);
	     }
	    }
	  }
        }*/
        //print_r($ResultCostoTanques);
        
        if($datos['codtanque'] != "all")
        {
            $query_Tan = " AND ch_tanque='".$datos['codtanque']."'";
        }
       
        $SalArray       = SobraFaltaModel::SaldoArray($query_Suc,$query_Tan,$fechai_1,$fechaf);        
        $ComprasArray   = SobraFaltaModel::ComprasArray($query_Suc,$query_Tan,$query_Alm,$fechai,$fechaf);
        $AferArray      = SobraFaltaModel::MedicionArray($query_Suc,$query_Tan,$fechai,$fechaf);
        $VentArray      = SobraFaltaModel::VentaArray($query_Suc,$query_Tan,$fechai,$fechaf);
        $IngresoArray   = SobraFaltaModel::IngresoArray($query_Suc,$query_Tan,$query_Alm,$fechai,$fechaf);
        $SalidaArray    = SobraFaltaModel::SalidaArray($query_Suc,$query_Tan,$query_Alm,$fechai,$fechaf);
        $VarillaArray   = SobraFaltaModel::SaldoArray($query_Suc,$query_Tan,$fechai,$fechaf);
        //print_r($IngresoArray);
        
	$query = "SELECT ch_sucursal AS sucursal, ".
			"ch_tanque AS tanque, ".
			"to_char(dt_fechamedicion,'DD-MM-YYYY') AS fecha, ".
			"nu_medicion AS saldo, ".
			"to_char(dt_fechamedicion- interval '1 day','DD-MM-YYYY') AS fecha2 ".
		 "FROM comb_ta_mediciondiaria ".
		 "WHERE dt_fechamedicion >= to_date('$fechai','dd-mm-yyyy') ".
		 "AND dt_fechamedicion <= to_date('$fechaf','dd-mm-yyyy') ".
		 "".@$query_Suc." ".
		 "".@$query_Tan." ".
		 "GROUP BY fecha,fecha2,saldo, sucursal, tanque ".
		 "ORDER BY sucursal, tanque, fecha";

	echo "QUERY : $query \n";
	$sqlca->query($query,'todos');
	$numrows = $sqlca->numrows('todos');
	$Registros = array();
	$FechasArray = array();
	$VentMedArray = array();
	$ResultFinal = array();
	    if($numrows > 0)
	    {
	    $c = 0;
		while($SucRows = $sqlca->fetchRow('todos'))
		{
		  if($tanque == "")
		  {
		      $tanque = $SucRows[1];
		  }
  		  
  		  if($sucursal == "")
		  {
		      $sucursal = $SucRows[0];
		  }
                  //echo "SALDO ".$SucRows[3]."\n";
		  $FechasArray['SUCURSAL']    = trim($SucCB[$SucRows[0]]);
		  //$FechasArray4['SUCURSAL']   = $SucRows[0];
		  $FechasArray['TANQUE']      = $SucRows[1];
		  $FechasArray['FECHA 1']     = $SucRows[2];
		  $FechasArray['FECHA 2']     = $SucRows[4];
		  
		  $FechasArray['SALDO']       = $SalArray[$SucRows[0]][$SucRows[1]][$SucRows[4]];
		  
		  if($ComprasArray[$SucRows[1]][$SucRows[0]][$SucRows[2]])
		  {
		      $FechasArray['COMPRA']  = $ComprasArray[$SucRows[1]][$SucRows[0]][$SucRows[2]];
		  }else{
		      $FechasArray['COMPRA']  = "0.000";
		  }
		  
		  $FechasArray['MEDICION']    = $AferArray[$SucRows[0]][$SucRows[1]][$SucRows[2]];
		  $FechasArray['VENTA']       = $VentArray[$SucRows[0]][$SucRows[1]][$SucRows[2]];
		  
		  if($IngresoArray[$SucRows[1]][$SucRows[0]][$SucRows[2]])
		  {
		      $FechasArray['INGRESO']  = $IngresoArray[$SucRows[1]][$SucRows[0]][$SucRows[2]];
		  }else{
		      $FechasArray['INGRESO']  = "0.000";
		  }
		  
 		  if($SalidaArray[$SucRows[1]][$SucRows[0]][$SucRows[2]])
		  {
		      $FechasArray['SALIDA']  = $SalidaArray[$SucRows[1]][$SucRows[0]][$SucRows[2]];
		  }else{
		      $FechasArray['SALIDA']  = "0.000";
		  }

                  $FechasArray['PARTE'] = $FechasArray['SALDO']+$FechasArray['COMPRA']+$FechasArray['MEDICION']-$FechasArray['VENTA']+$FechasArray['INGRESO']-$FechasArray['SALIDA'];
                  $FechasArray['VARILLA']   = $VarillaArray[$SucRows[0]][$SucRows[1]][$SucRows[2]];
                  $FechasArray['DIARIA'] = $FechasArray['VARILLA'] - $FechasArray['PARTE'];
                  
                  if($tanque == $FechasArray['TANQUE'] && $sucursal == $SucRows[0])
                  {
                    //$FechasArray2[$c-1]['ACUMULADA'] = 0.00;				  
                    $FechasArray['ACUMULADA'] = $FechasArray['DIARIA'] + $FechasArray2[$c-1]['ACUMULADA'];
                    $FechasArray2[$c]['ACUMULADA'] = $FechasArray['ACUMULADA'];
                    $tanque = $FechasArray['TANQUE'];
                    $sucursal = $SucRows[0];
                  }else{
                    $FechasArray2[$c-1]['ACUMULADA'] = 0.00;
                    $FechasArray['ACUMULADA'] = $FechasArray['DIARIA'] + $FechasArray2[$c-1]['ACUMULADA'];
                    $FechasArray2[$c]['ACUMULADA'] = $FechasArray['ACUMULADA'];
                    $tanque = "";
                    $sucursal = "";
                  }
                  /*if($sucursal == $SucRows[0])
                  {
                    $FechasArray['ACUMULADA'] = $FechasArray['DIARIA'] + $FechasArray2[$c-1]['ACUMULADA'];
                    $FechasArray2[$c]['ACUMULADA'] = $FechasArray['ACUMULADA'];
                    $sucursal = $SucRows[0];
                  }else{
                    $FechasArray2[$c-1]['ACUMULADA'] = " ";
                    $FechasArray['ACUMULADA'] = $FechasArray['DIARIA'] + $FechasArray2[$c-1]['ACUMULADA'];
                    $FechasArray2[$c]['ACUMULADA'] = $FechasArray['ACUMULADA'];
                    $sucursal = "";
                  }*/
        	  //$VentMedArray['TOTAL VENTAS'] += $FechasArray['VENTA'];
		  //$VentMedArray['TOTAL MEDICION'] += $FechasArray['MEDICION'];

		  $Registros[$c]= $FechasArray;
		  $ResultFinal['Sucursales'] = $Registros;
		  
		  $ResultFinal['Totales'][$FechasArray['SUCURSAL']][$FechasArray['TANQUE']] = $FechasArray['ACUMULADA'];
		  $ResultFinal['Totales']['Ventas->'.$FechasArray['SUCURSAL']][$FechasArray['TANQUE']] += $FechasArray['VENTA'];
		  //echo "DATOS ACUMULADA : ".$ResultFinal['Totales'][$FechasArray['SUCURSAL']][$FechasArray['TANQUE']]."\n";
		  $ResultFinal['Totales']['Porcentaje->'.$FechasArray['SUCURSAL']][$FechasArray['TANQUE']] = ($ResultFinal['Totales'][$FechasArray['SUCURSAL']][$FechasArray['TANQUE']]/$ResultFinal['Totales']['Ventas->'.$FechasArray['SUCURSAL']][$FechasArray['TANQUE']])*100;
		  
		  $ResultFinal['Totales2'][$FechasArray['SUCURSAL']]['TOTAL GASOLINA'] = $ResultFinal['Totales'][$FechasArray['SUCURSAL']]['01']+$ResultFinal['Totales'][$FechasArray['SUCURSAL']]['05']+$ResultFinal['Totales'][$FechasArray['SUCURSAL']]['02']+$ResultFinal['Totales'][$FechasArray['SUCURSAL']]['06'];
  		  $ResultFinal['Totales2']['Ventas->'.$FechasArray['SUCURSAL']]['TOTAL GASOLINA'] = $ResultFinal['Totales']['Ventas->'.$FechasArray['SUCURSAL']]['01']+$ResultFinal['Totales']['Ventas->'.$FechasArray['SUCURSAL']]['05']+$ResultFinal['Totales']['Ventas->'.$FechasArray['SUCURSAL']]['02']+$ResultFinal['Totales']['Ventas->'.$FechasArray['SUCURSAL']]['06'];
  		  $ResultFinal['Totales2']['Porcentaje->'.$FechasArray['SUCURSAL']]['TOTAL GASOLINA'] = ($ResultFinal['Totales2'][$FechasArray['SUCURSAL']]['TOTAL GASOLINA']/$ResultFinal['Totales2']['Ventas->'.$FechasArray['SUCURSAL']]['TOTAL GASOLINA']*100);
		  
		  $ResultFinal['Totales2'][$FechasArray['SUCURSAL']]['TOTAL DIESEL'] = $ResultFinal['Totales'][$FechasArray['SUCURSAL']]['03']+$ResultFinal['Totales'][$FechasArray['SUCURSAL']]['04'];
		  $ResultFinal['Totales2']['Ventas->'.$FechasArray['SUCURSAL']]['TOTAL DIESEL'] = $ResultFinal['Totales']['Ventas->'.$FechasArray['SUCURSAL']]['03']+$ResultFinal['Totales']['Ventas->'.$FechasArray['SUCURSAL']]['04'];
		  $ResultFinal['Totales2']['Porcentaje->'.$FechasArray['SUCURSAL']]['TOTAL DIESEL'] =($ResultFinal['Totales2'][$FechasArray['SUCURSAL']]['TOTAL DIESEL']/$ResultFinal['Totales2']['Ventas->'.$FechasArray['SUCURSAL']]['TOTAL DIESEL']*100);
		  
		  $ResultFinal['Totales2'][$FechasArray['SUCURSAL']]['TOTAL GALONES'] = $ResultFinal['Totales2'][$FechasArray['SUCURSAL']]['TOTAL GASOLINA']+$ResultFinal['Totales2'][$FechasArray['SUCURSAL']]['TOTAL DIESEL'];
		  $ResultFinal['Totales2']['Ventas->'.$FechasArray['SUCURSAL']]['TOTAL GALONES'] = $ResultFinal['Totales2']['Ventas->'.$FechasArray['SUCURSAL']]['TOTAL GASOLINA']+$ResultFinal['Totales2']['Ventas->'.$FechasArray['SUCURSAL']]['TOTAL DIESEL'];
		  $ResultFinal['Totales2']['Porcentaje->'.$FechasArray['SUCURSAL']]['TOTAL GALONES'] = ($ResultFinal['Totales2'][$FechasArray['SUCURSAL']]['TOTAL GALONES']/$ResultFinal['Totales2']['Ventas->'.$FechasArray['SUCURSAL']]['TOTAL GALONES'])*100;
		  
		  //$ResultFinal['Totales2'][$FechasArray['SUCURSAL']]['VENTAS'] += $FechasArray['VENTA'];
		  //$ResultFinal['Totales2'][$FechasArray['SUCURSAL']]['MEDICIONES'] += $FechasArray['MEDICION'];
		  //$ResultFinal['Totales2'][$FechasArray['SUCURSAL']]['TOTALES VENTAS'] = $ResultFinal['Totales2'][$FechasArray['SUCURSAL']]['VENTAS'] - $ResultFinal['Totales2'][$FechasArray['SUCURSAL']]['MEDICIONES'];
		  //$ResultFinal['Totales2'][$FechasArray['SUCURSAL']]['PORCENTAJES VENTAS'] = money_format('%.2n',(($ResultFinal['Totales'][$FechasArray['SUCURSAL']][$FechasArray['TANQUE']]/$ResultFinal['Totales2'][$FechasArray['SUCURSAL']]['TOTALES VENTAS'])*100))." %";
		  
		  //if($datos['mes_f']<9) $datos['mes_f'] = '0'.$datos['mes_f'];
		  //$ResultFinal['Totales2'][$FechasArray['SUCURSAL']]['TOTAL COSTO'] = SobraFaltaModel::TotalCosto($SucRows[0], $datos['mes_f'], $datos['anio_f']);
		  
		  //$ResultFinal['Totales2'][$FechasArray['SUCURSAL']]['TOTAL COSTO'] = $ResultTotalesCosto[$SucRows[0]];
		  //$ResultFinal['Totales2']['Ventas->'.$FechasArray['SUCURSAL']]['TOTAL COSTO'] = " ";
		  
		   //$ResultFinal['Totales3']['Ventas->'][$FechasArray['TANQUE']] +=  $ResultFinal['Totales']['Ventas->'.$FechasArray['SUCURSAL']][$FechasArray['TANQUE']];
		  $c++;
		  //print_r($FechasArray);
		}
		
	    }
	//echo "QUERY :  $query \n";
	//print_r($FechasArray2);
    //print_r($ResultFinal);
    return $ResultFinal;
    }
    
}

