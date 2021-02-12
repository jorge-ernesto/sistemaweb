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
		$w0=pg_result($xsql1,$irow1,0);
		$fm=pg_result($xsql1,$irow1,1);
		if($fm=='05' or $fm=='08' or $fm=='11' or $fm=='14' or $fm=='21' or $fm=='24' or $fm=='25' or $fm=='28' or $fm=='45' or $fm=='46')
			{
			$summov=$summov-$w0;
			}
		elseif($fm=='01' or $fm=='07' or $fm=='12' or $fm=='16' or $fm=='17' or $fm=='18' or $fm=='19' or $fm=='23' or $fm=='26' or $fm=='27')
			{
			$summov=$summov+$w0;
			}
		$irow1++;
		}
	$stkini=$stkinimes+$summov;
	}

