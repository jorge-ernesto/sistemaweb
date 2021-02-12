<?php
function calcula_rango_venta_fecha($v_coneccion,$v_almacen,$v_art_codigo,$v_fecha_inicio,$v_fecha_final)
	{
	global $v_venta;
	global $v_sql_subs;
	$v_venta=0;
	$v_sql_subs="select sum(DET.NU_FAC_CANTIDAD) from 
	(select CH_FAC_TIPODOCUMENTO,CH_FAC_SERIEDOCUMENTO,CH_FAC_NUMERODOCUMENTO,CLI_CODIGO,CH_ALMACEN from FAC_TA_FACTURA_CABECERA where DT_FAC_FECHA between '$v_fecha_inicio' and '$v_fecha_final' ) as CAB, 
	FAC_TA_FACTURA_DETALLE as DET  
	where CAB.CH_FAC_TIPODOCUMENTO||CAB.CH_FAC_SERIEDOCUMENTO||CAB.CH_FAC_NUMERODOCUMENTO||CAB.CLI_CODIGO 
		= DET.CH_FAC_TIPODOCUMENTO||DET.CH_FAC_SERIEDOCUMENTO||DET.CH_FAC_NUMERODOCUMENTO||DET.CLI_CODIGO and
	DET.ART_CODIGO='$v_art_codigo' and CAB.CH_ALMACEN='$v_almacen'" ;
	$v_xsql_subs=pg_exec($v_coneccion,$v_sql_subs);
	if(pg_numrows($v_xsql_subs)>0) { $v_venta=round(pg_result($v_xsql_subs,0,0),4); }
	}
	
function calcula_requerimientosxatender($v_coneccion,$v_almacen,$v_art_codigo,$v_fecha_inicio,$v_fecha_final)
	{
	global $v_requerimiento;
	$v_requerimiento=0;
	$v_sql_subs="select sum(NU_REQ_CANTIDAD_REQUERIDA) from COM_TA_REQUERIMIENTOS 
	where DT_REQ_FECHA_REQUERIDA between '$v_fecha_inicio' and '$v_fecha_final' and ART_CODIGO='$v_art_codigo' and CH_REQ_ALMACEN='$v_almacen'" ;
	$v_xsql_subs=pg_exec($v_coneccion,$v_sql_subs);
	if(pg_numrows($v_xsql_subs)>0) { $v_requerimiento=round(pg_result($v_xsql_subs,0,0),4); }
	}

function calcula_stkactual($coneccion,$codart,$diad,$mesd,$anod,$almac) 
	{
	global $stkini;
	$mesk=str_pad(trim($mesd-1),2,'0', STR_PAD_LEFT) ; 
	$anok=$anod;
	if($mesk==0) { $mesk=12; $anok=$anod-1; }
	$sqlstk="select stk_stock".$mesk." from inv_saldoalma where stk_periodo='".$anok."' and stk_almacen='".$almac."' and art_codigo='".$codart."' ";
	$xsqlstk=pg_exec($coneccion,$sqlstk);
	if(pg_numrows($xsqlstk)>0) { $stkinimes=pg_result($xsqlstk,0,0); }
	$fecd=$anod."/".$mesd."/01";
	$diadx=$diad;
	$feca=$anod."/".$mesd."/".$diadx;
	if($diadx==0) { $diadx=31; $mesd=$mesd-1; if($mesd==0) { $mesd=12; $anod--; } }
  	$f1=10;
	$g1=25;
	while($f1<$g1)	
		{
		if(checkdate($mesd,$diadx,$anod)) 
			{ 
			$f1=30;  //echo $zmesd."/".$zdiad."/".$zanod;
			}
		else
			{
			$diadx--;
			}
		$f1++;
	  	}
  	$feca=$anod."/".$mesd."/".$diadx;
	$sql1="select mov_cantidad,tran_codigo from inv_movialma where mov_almacen='".$almac."' and art_codigo='".$codart."' and mov_fecha between '".$fecd."' and '".$feca."' ";

	$xsql1=pg_exec($coneccion,$sql1);
	$ilimit1=pg_numrows($xsql1);
	while($irow1<$ilimit1) 
		{
		$array = pg_fetch_array($xsql1, $irow1);
		$w0 = $array[0];
		$fm = $array[1];
		switch ($fm) {
		    case '05':
		    case '08':
		    case '11':
		    case '14':
		    case '21':
		    case '24':
		    case '25':
		    case '28':
		    case '45':
		    case '46':
			$summov -= $w0;
			break;
		    case '01':
		    case '07':
		    case '12':
		    case '16':
		    case '17':
		    case '18':
		    case '19':
		    case '23':
		    case '26':
		    case '27':
			$summov += $w0;
		}
		$irow1++;
		}
	$stkini=$stkinimes+$summov;
	}

