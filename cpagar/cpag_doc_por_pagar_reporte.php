<?php

include("../valida_sess.php");
include("../config.php");
include("../functions.php");
require("../clases/funciones.php");

$funcion = new class_funciones;
	// crea la clase para controlar errores
	$clase_error = new OpensoftError;
	$clase_error->_error();
	// conectar con la base de datos
	$conector_id=$funcion->conectar("","","","","");
?>

<html>
<head>
<title>REPORTE - CUENTAS POR PAGAR</title>
</head>
<body><link rel="stylesheet" href="js/style.css" type="text/css">
<?php
	$var_pers="v_fecha_desde=".$v_fecha_desde."&v_fecha_hasta=".$v_fecha_hasta."&v_almacen=".$v_almacen."&v_proveedor=".$v_proveedor;
	include("../maestros/pagina.php");
?>

<table border="0" cellpadding="0" cellspacing="0">
	<tr class="letra_titulo">
		<td width='340' align="left" colspan="1">ALMACEN : <?php echo $v_almacen." - ".otorgarAlmacen($conector_id, $v_almacen);?></td>
		<th width="340" align="right">
		<a href="/sistemaweb/utils/impresiones.php?imprimir=paginar&cabecera=/sistemaweb/cpagar/cta_por_pagar_rep_cabecera.txt&cuerpo=/sistemaweb/cpagar/cta_por_pagar_rep.txt&archivo_final=/sistemaweb/cpagar/cta_por_pagar_texto.txt" target="_blank">Imprimir Texto</a>
		<!--input type="button" value="Imprimir" onclick="javascript:window.print();">&nbsp;&nbsp;-->
		<button type="submit" name="boton" value="Imprimir" onclick="javascript:window.print();"><img src="/sistemaweb/images/icon_imprimir.gif" alt="left" />  Imprimir</button>&nbsp;&nbsp;
		<button type="button" name="boton" value="excel" OnClick="location.href='/sistemaweb/cpagar/cuentas_por_pagar.csv';"><img src="/sistemaweb/images/excel_icon.png" alt="left" />  Excel</button>
	</tr>
	<tr class="letra_titulo">
		<td align="center" colspan="2">CUENTAS POR PAGAR</td>
	</tr>
	<tr class="letra_titulo">
		<td colspan="2">
		     <?php
			$ft_csv	= fopen('cuentas_por_pagar.csv','w');
			$ft_cab = fopen('cta_por_pagar_rep_cabecera.txt','w');
			$ft     = fopen('cta_por_pagar_rep.txt','w');

			if ($ft_cab>0) {
				$snewbuffer=$snewbuffer."                                                            REPORTE - CUENTAS POR PAGAR                          ";
				$snewbuffer=$snewbuffer."\n";
				$snewbuffer=$snewbuffer."                         ".$almacen." - ".trim(otorgarAlmacen($conector_id, $v_almacen))."  DEL :".$v_fecha_desde." AL :".$v_fecha_hasta.$adicional."\n";
				$snewbuffer=$snewbuffer."  FECHA     FECHA     TIPO   SERIE   NUMERO                                                          TOTAL      \n";
				$snewbuffer=$snewbuffer." REGISTRO  EMISION    DOC.    DOC.    DOC.            PROVEEDOR                   RUBRO      T.C.    DOLAR      VENTA    IMPUESTO   TOTAL    COSTEO\n";
				$snewbuffer=$snewbuffer."===================================================================================================================================================\n";
				fwrite($ft_cab,$snewbuffer);
				fclose($ft_cab);
				$snewbuffer="";
			}

			if ($ft_csv>0) {

			$snewbuffer_csv=$snewbuffer_csv."REPORTE - CUENTAS POR PAGAR                          ";
			$snewbuffer_csv.=$snewbuffer_csv."\n";
			$snewbuffer_csv.=$snewbuffer_csv.$almacen." - ".trim(otorgarAlmacen($conector_id, $v_almacen))." DEL:".$v_fecha_desde." AL :".$v_fecha_hasta.$adicional."\n\n";
			$snewbuffer_csv.=$snewbuffer_csv."  FECHA   ,  FECHA  ,  TIPO  , SERIE , NUMERO   ,                        ,                  ,         ,   TOTAL      \n";
			$snewbuffer_csv.=$snewbuffer_csv." REGISTRO , EMISION ,  DOC.  , DOC.  ,  DOC.    ,      PROVEEDOR         ,          RUBRO   ,   T.C.  ,  DOLAR   ,   VENTA  ,  IMPUESTO  , TOTAL  ,  COSTEO\n";
			$snewbuffer_csv.=$snewbuffer_csv." \n";

			}

			if($v_proveedor!=""){$adicional=" PROVEEDOR: ".$v_proveedor;}
			echo "DEL :".$v_fecha_desde." AL :".$v_fecha_hasta.$adicional; ?>
		</td>
	</tr>
</table>
<br>
<table border="0" cellspacing="1" cellpadding="1" bgcolor="#BBBBBB">
	<tr class="letra_cabecera">
		<td width="60">Fecha<br>Registro</td>
		<td width="60">Fecha<br>Emision</td>
		<td width="25">Tipo<br>Doc</td>
		<td width="25">Ser<br>Doc</td>
		<td width="60">Numero<br>Doc</td>
		<td width="190">Proveedor</td>
		<td width="60">Rubro</td>
		<td width="30">T.C.</td>
		<td width="45">Total<br>Dolar</td>
		<td width="45">V.<br>Venta</td>
		<td width="45">Imp.</td>
		<td width="45">Total</td>
		<td width="45">Inafecto</td>
	</tr>
<?php
$v_sql1 = "	SELECT
			trim(CAB.PRO_CAB_TIPDOCUMENTO)||trim(CAB.PRO_CAB_SERIEDOCUMENTO)||trim(CAB.PRO_CAB_NUMDOCUMENTO)||trim(CAB.PRO_CODIGO) as CLAVE
			,to_char(CAB.PRO_CAB_FECHAREGISTRO,'DD/MM/YYYY') as PRO_CAB_FECHAREGISTRO
			,to_char(CAB.PRO_CAB_FECHAEMISION,'DD/MM/YYYY') as PRO_CAB_FECHAEMISION
			,CAB.PRO_CAB_TIPDOCUMENTO
			,CAB.PRO_CAB_SERIEDOCUMENTO
			,CAB.PRO_CAB_NUMDOCUMENTO
			,CAB.PRO_CODIGO
			,PRO.PRO_RSOCIALBREVE
			,trim(CAB.PRO_CAB_MONEDA) as PRO_CAB_MONEDA
			,CAB.pro_cab_impinafecto
			,(CAST(TRIM(CAB.PRO_CAB_RUBRODOC) AS VARCHAR)||'-'||substr(trim(TAB2.TAB_DESCRIPCION),0,7)) AS PRO_CAB_RUBRODOC
		FROM
			CPAG_TA_CABECERA CAB,
			INT_PROVEEDORES PRO,
			INV_TA_ALMACENES ALM,
			INT_TABLA_GENERAL TAB2
		WHERE
			CAB.PRO_CODIGO = PRO.PRO_CODIGO
			AND CAB.PRO_CAB_ALMACEN = ALM.CH_ALMACEN
			AND CAB.PRO_CAB_ALMACEN='$v_almacen'
			AND CAB.PRO_CAB_RUBRODOC = TAB2.TAB_ELEMENTO and (TAB2.TAB_TABLA='RCPG')
			AND CAB.PRO_CAB_FECHAREGISTRO between '".$funcion->date_format($v_fecha_desde,'YYYY-MM-DD')."' AND '".$funcion->date_format($v_fecha_hasta,'YYYY-MM-DD')."'
			".$v_proveedor_ref.$v_almacen_ref."
		ORDER BY
			CAB.PRO_CAB_FECHAREGISTRO ".$bddsql." ";

$v_xsql1 	= pg_exec($conector_id,$v_sql1);
$v_ilimit1	= pg_numrows($v_xsql1);
$chk		= 'checked';
$v_irow1	= 0;

if($v_ilimit1>0) {

	while($v_irow1<$v_ilimit1) {

		$c_clave		= pg_result($v_xsql1,$v_irow1,'CLAVE');
		$c_fecha_registro	= trim(pg_result($v_xsql1,$v_irow1,'PRO_CAB_FECHAREGISTRO'));
		$c_fecha		= trim(pg_result($v_xsql1,$v_irow1,'PRO_CAB_FECHAEMISION'));
		$c_tipo			= pg_result($v_xsql1,$v_irow1,'PRO_CAB_TIPDOCUMENTO');
		$c_serie		= pg_result($v_xsql1,$v_irow1,'PRO_CAB_SERIEDOCUMENTO');
		$c_numero		= pg_result($v_xsql1,$v_irow1,'PRO_CAB_NUMDOCUMENTO');
		$c_proveedor		= trim(pg_result($v_xsql1,$v_irow1,'PRO_CODIGO'));
		$c_descprov		= trim(pg_result($v_xsql1,$v_irow1,'PRO_RSOCIALBREVE'));
		$c_moneda		= pg_result($v_xsql1,$v_irow1,'PRO_CAB_MONEDA');
		$c_rubro		= pg_result($v_xsql1,$v_irow1,'PRO_CAB_RUBRODOC');
		$c_inafecto		= pg_result($v_xsql1,$v_irow1,'pro_cab_impinafecto');

		if(trim($c_moneda)=='02') {

			$sql_x_moneda = "SELECT
						round(PRO_CAB_TCAMBIO,3),round(PRO_CAB_IMPTOTAL,2)
						,round((CAB.PRO_CAB_IMPAFECTO*PRO_CAB_TCAMBIO),2)
						,round((CAB.PRO_CAB_IMPTO1*PRO_CAB_TCAMBIO),2)
						,round((CAB.PRO_CAB_IMPTOTAL*PRO_CAB_TCAMBIO),2)
						,sum(round((mov_cantidad*mov_costounitario),2))
					FROM
						CPAG_TA_CABECERA CAB,
						INV_TA_COMPRAS_DEVOLUCIONES DEV
					WHERE
						CAB.PRO_CAB_TIPDOCUMENTO=DEV.CPAG_TIPO_PAGO AND
						CAB.PRO_CAB_SERIEDOCUMENTO=DEV.CPAG_SERIE_PAGO AND
						CAB.PRO_CAB_NUMDOCUMENTO=DEV.CPAG_NUM_PAGO AND
						pro_codigo=mov_entidad and
						trim(CAB.PRO_CAB_MONEDA)='02' and
						trim(CAB.PRO_CAB_TIPDOCUMENTO)||trim(CAB.PRO_CAB_SERIEDOCUMENTO)||trim(CAB.PRO_CAB_NUMDOCUMENTO)||trim(CAB.PRO_CODIGO)='$c_clave'
					GROUP BY
						PRO_CAB_TCAMBIO,
						PRO_CAB_IMPTOTAL,
						CAB.PRO_CAB_IMPAFECTO,
						CAB.PRO_CAB_IMPTO1,
						CAB.PRO_CAB_IMPTOTAL";

			$xsql_total 	= pg_query($conector_id, $sql_x_moneda);
			$rs 		= pg_fetch_array($xsql_total,0);

			$c_tcambio	= $rs[0];
			$c_total_dolar	= $rs[1];
			$c_v_venta	= $rs[2];
			$c_impuesto	= $rs[3];
			$c_total	= $rs[4];
			$c_costeo	= $rs[5];

		} else {

			$sql_x_moneda = "SELECT
						CAB.PRO_CAB_IMPAFECTO,
						CAB.PRO_CAB_IMPTO1,
						CAB.PRO_CAB_IMPTOTAL,
						sum(round((mov_cantidad*mov_costounitario),2))
					FROM
						CPAG_TA_CABECERA CAB,
						INV_TA_COMPRAS_DEVOLUCIONES DEV
					WHERE
						CAB.PRO_CAB_TIPDOCUMENTO = DEV.CPAG_TIPO_PAGO AND
						CAB.PRO_CAB_SERIEDOCUMENTO = DEV.CPAG_SERIE_PAGO AND
						CAB.PRO_CAB_NUMDOCUMENTO = DEV.CPAG_NUM_PAGO AND
						pro_codigo = mov_entidad AND
						trim(CAB.PRO_CAB_MONEDA)='01' AND
						trim(CAB.PRO_CAB_TIPDOCUMENTO)||trim(CAB.PRO_CAB_SERIEDOCUMENTO)||trim(CAB.PRO_CAB_NUMDOCUMENTO)||trim(CAB.PRO_CODIGO)='$c_clave'
					GROUP BY
						CAB.PRO_CAB_IMPAFECTO,
						CAB.PRO_CAB_IMPTO1,
						CAB.PRO_CAB_IMPTOTAL";

 			$xsql_total 	= pg_query($conector_id, $sql_x_moneda);
			$rs 		= pg_fetch_array($xsql_total,0);
			$c_tcambio	= "";
			$c_total_dolar	= "";
			$c_v_venta	= $rs[0];
			$c_impuesto	= $rs[1];
			$c_total	= $rs[2];
			$c_costeo	= $rs[3];

		}

		$dife="";

		if($c_v_venta!=$c_costeo){$dife="*";}

		echo '<tr class="letra_detalle">';
		echo "<td align='center'>".$c_fecha_registro."</td>";
		echo "<td align='center'>".$c_fecha."</td>";
		echo "<td align='center'>".$c_tipo."</td>";
		echo "<td align='center'>".$c_serie."</td>";
		echo "<td align='center'>".$c_numero."</td>";
		echo '<td>';
		echo $c_proveedor." - ".$c_descprov;
		echo '</td>';
		echo "<td>".$c_rubro."</td>";
		echo "<td align='right'>".$c_tcambio."</td>";
		echo "<td align='right'>".$c_total_dolar."</td>";
		echo "<td align='right'>".$dife.$c_v_venta."</td>";
		echo "<td align='right'>".$c_impuesto."</td>";
		echo "<td align='right'>".$c_total."</td>";
		echo "<td align='right'>".$c_inafecto."</td>";

		$v_irow1++;

		if($ft>0){
			$snewbuffer = $snewbuffer.$c_fecha_registro."  ".$c_fecha."  ".str_pad($c_tipo,6," ",STR_PAD_BOTH)." ".str_pad($c_serie,8," ",STR_PAD_BOTH)." ".str_pad(trim($c_numero),8," ",STR_PAD_BOTH)." ".str_pad($c_proveedor."-".substr($c_descprov,0,32),32," ");
			$snewbuffer = $snewbuffer."  ".str_pad($c_rubro,10," ",STR_PAD_BOTH).str_pad($c_tcambio,7," ", STR_PAD_LEFT).str_pad($c_total_dolar,10," ", STR_PAD_LEFT).str_pad($dif.$c_v_venta,10," ", STR_PAD_LEFT).str_pad($c_impuesto,10," ", STR_PAD_LEFT).str_pad($c_total,10," ", STR_PAD_LEFT).str_pad($c_inafecto,10," ", STR_PAD_LEFT)."\n";
		}

		if($ft_csv>0){
			$snewbuffer_csv = $snewbuffer_csv.$c_fecha_registro.",".$c_fecha.",".str_pad($c_tipo,6," ",STR_PAD_BOTH).",".str_pad($c_serie,8," ",STR_PAD_BOTH).", ".str_pad(trim($c_numero),8," ",STR_PAD_BOTH).",".str_pad($c_proveedor."-".substr($c_descprov,0,32),32," ");
			$snewbuffer_csv = $snewbuffer_csv.",".str_pad($c_rubro,10," ",STR_PAD_BOTH).",".str_pad($c_tcambio,7," ", STR_PAD_LEFT).",".str_pad($c_total_dolar,10," ", STR_PAD_LEFT).",".str_pad($dif.$c_v_venta,10," ", STR_PAD_LEFT).",".str_pad($c_impuesto,10," ", STR_PAD_LEFT).",".str_pad($c_total,10," ", STR_PAD_LEFT).",".str_pad($c_inafecto,10," ", STR_PAD_LEFT)."\n";
		}

		$total_total_dolares	+= $c_total_dolar;
		$total_v_venta 		+= $c_v_venta;
		$total_impuesto 	+= $c_impuesto;
		$total_total 		+= $c_total;
		$total_inafectos 	+= $c_inafecto;
	}
}

echo '<tr class="letra_detalle">';
echo "<td align='right' style='color:blue;font-weight:bold' colspan='8'>T O T A L : &nbsp;&nbsp;</td>";
echo "<td align='right' style='color:blue;font-weight:bold'>".number_format($total_total_dolares, 2, '.', ',')."</td>";
echo "<td align='right' style='color:blue;font-weight:bold'>".number_format($total_v_venta, 2, '.', ',')."</td>";
echo "<td align='right' style='color:blue;font-weight:bold'>".number_format($total_impuesto, 2, '.', ',')."</td>";
echo "<td align='right' style='color:blue;font-weight:bold'>".number_format($total_total, 2, '.', ',')."</td>";
echo "<td align='right' style='color:blue;font-weight:bold'>".number_format($total_inafectos, 2, '.', ',')."</td>";

if ($ft_csv>0) {
	$snewbuffer_csv .= $snewbuffer_csv."\n , , , , , , ,TOTAL:,".str_pad($total_total_dolares,10," ", STR_PAD_LEFT).",".str_pad($total_v_venta,10," ", STR_PAD_LEFT).",".str_pad($total_impuesto,10," ", STR_PAD_LEFT).",".str_pad($total_total,10," ", STR_PAD_LEFT).",".str_pad($total_inafectos,10," ", STR_PAD_LEFT)."\n";
}

fwrite($ft,$snewbuffer);
fclose($ft);
fwrite($ft_csv,$snewbuffer_csv);
fclose($ft_csv);

?>
</table>
</html>
