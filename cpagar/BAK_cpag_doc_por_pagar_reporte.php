<?php
include("../valida_sess.php");
//include("../menu_princ.php");
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
	//$bddsql=" limit $tamPag offset $limitInf ";
?>

<table border="0" cellpadding="0" cellspacing="0">
	<tr class="letra_titulo">
		<td width='340' align="left" colspan="1">ALMACEN : <?php echo $almacen." - ".otorgarAlmacen($conector_id, $v_almacen);?></td>
		<th width="340" align="right"><a href="#" onClick="javascript:window.print();">IMPRIMIR</a></th>
	<tr class="letra_titulo">
		<td align="center" colspan="2">CUENTAS POR PAGAR</td>
	<tr class="letra_titulo">
		<td colspan="2"><?php
		if($v_proveedor!="")
		{
			$adicional=" PROVEEDOR: ".$v_proveedor;
		}
		echo "DEL :".$v_fecha_desde." AL :".$v_fecha_hasta.$adicional; ?></td>
</table>
<br>
<table border="0" cellspacing="1" cellpadding="1" bgcolor="#BBBBBB">
	<tr class="letra_cabecera">
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
		<td width="45">Costeo</td>
	</tr>
<?php
/*
$v_sql1="select CAB.PRO_CAB_TIPDOCUMENTO||CAB.PRO_CAB_SERIEDOCUMENTO||CAB.PRO_CAB_NUMDOCUMENTO||CAB.PRO_CODIGO as CLAVE,
				CAB.PRO_CAB_FECHAEMISION,
				CAB.pro_cab_almacen ,
				CAB.PRO_CAB_TIPDOCUMENTO,
				CAB.PRO_CAB_SERIEDOCUMENTO,
				CAB.PRO_CAB_NUMDOCUMENTO,
				CAB.PRO_CODIGO,
				PRO.PRO_RSOCIALBREVE,
				CAB.PRO_CAB_IMPTOTAL,
				CAB.PRO_CAB_IMPSALDO,
				TAB1.TAB_DESCRIPCION AS PRO_CAB_MONEDA,
				(CAST(TRIM(CAB.PRO_CAB_RUBRODOC) AS VARCHAR)||'-'||TAB2.TAB_DESCRIPCION) AS PRO_CAB_RUBRODOC,
				CAB.pro_cab_fechavencimiento

			from CPAG_TA_CABECERA CAB, INT_PROVEEDORES PRO, INV_TA_ALMACENES ALM,
					INT_TABLA_GENERAL TAB1, INT_TABLA_GENERAL TAB2

			where CAB.PRO_CODIGO = PRO.PRO_CODIGO
			and CAB.PRO_CAB_ALMACEN = ALM.CH_ALMACEN

			and CAB.PRO_CAB_MONEDA = TAB1.TAB_ELEMENTO and (TAB1.TAB_TABLA='MONE')
			and CAB.PRO_CAB_RUBRODOC = TAB2.TAB_ELEMENTO and (TAB2.TAB_TABLA='RCPG')

			and	CAB.PRO_CAB_FECHAEMISION  between '".$funcion->date_format($v_fecha_desde,'YYYY/MM/DD')."' and '".$funcion->date_format($v_fecha_hasta,'YYYY/MM/DD')."'
			".$v_proveedor_ref.$v_almacen_ref."
			order by
			CAB.PRO_CAB_FECHAEMISION
			".$bddsql."  ";
*/
$v_sql1 = "SELECT
    		trim(CAB.PRO_CAB_TIPDOCUMENTO)||trim(CAB.PRO_CAB_SERIEDOCUMENTO)||trim(CAB.PRO_CAB_NUMDOCUMENTO)||trim(CAB.PRO_CODIGO) as CLAVE
			, CAB.PRO_CAB_FECHAEMISION, CAB.PRO_CAB_TIPDOCUMENTO
		    , CAB.PRO_CAB_SERIEDOCUMENTO, CAB.PRO_CAB_NUMDOCUMENTO, CAB.PRO_CODIGO
	    	, PRO.PRO_RSOCIALBREVE, trim(CAB.PRO_CAB_MONEDA) as PRO_CAB_MONEDA
			, (CAST(TRIM(CAB.PRO_CAB_RUBRODOC) AS VARCHAR)||'-'||substr(trim(TAB2.TAB_DESCRIPCION),0,7)) AS PRO_CAB_RUBRODOC

			FROM
				CPAG_TA_CABECERA CAB, INT_PROVEEDORES PRO, INV_TA_ALMACENES ALM, INT_TABLA_GENERAL TAB2

			WHERE
				CAB.PRO_CODIGO = PRO.PRO_CODIGO
				and CAB.PRO_CAB_ALMACEN = ALM.CH_ALMACEN
				and CAB.PRO_CAB_RUBRODOC = TAB2.TAB_ELEMENTO and (TAB2.TAB_TABLA='RCPG')


			and	CAB.PRO_CAB_FECHAEMISION  between '".$funcion->date_format($v_fecha_desde,'YYYY-MM-DD')."' and '".$funcion->date_format($v_fecha_hasta,'YYYY-MM-DD')."'
			".$v_proveedor_ref.$v_almacen_ref."
			order by
			CAB.PRO_CAB_FECHAEMISION
			".$bddsql."  ";
//echo $v_sql1;


$v_xsql1 = pg_exec($conector_id,$v_sql1);
$v_ilimit1=pg_numrows($v_xsql1);

$chk='checked';
$v_irow1=0;

if($v_ilimit1>0) {
	while($v_irow1<$v_ilimit1) {

		$c_clave=pg_result($v_xsql1,$v_irow1,'CLAVE');
		$c_fecha=trim(pg_result($v_xsql1,$v_irow1,'PRO_CAB_FECHAEMISION'));
		$c_tipo=pg_result($v_xsql1,$v_irow1,'PRO_CAB_TIPDOCUMENTO');
		$c_serie=pg_result($v_xsql1,$v_irow1,'PRO_CAB_SERIEDOCUMENTO');
		$c_numero=pg_result($v_xsql1,$v_irow1,'PRO_CAB_NUMDOCUMENTO');
		$c_proveedor=pg_result($v_xsql1,$v_irow1,'PRO_CODIGO');
		$c_descprov=pg_result($v_xsql1,$v_irow1,'PRO_RSOCIALBREVE');
		$c_moneda=pg_result($v_xsql1,$v_irow1,'PRO_CAB_MONEDA');
		$c_rubro=pg_result($v_xsql1,$v_irow1,'PRO_CAB_RUBRODOC');

		if(trim($c_moneda)=='02') {
			$sql_x_moneda = "SELECT
								round(PRO_CAB_TCAMBIO,2),round(PRO_CAB_IMPTOTAL,2)
								, round((CAB.PRO_CAB_IMPAFECTO*PRO_CAB_TCAMBIO),2)
								, round((CAB.PRO_CAB_IMPTO1*PRO_CAB_TCAMBIO),2)
								, round((CAB.PRO_CAB_IMPTOTAL*PRO_CAB_TCAMBIO),2)
								, sum(round((mov_cantidad*mov_costounitario),2))
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
								PRO_CAB_TCAMBIO, PRO_CAB_IMPTOTAL, CAB.PRO_CAB_IMPAFECTO, CAB.PRO_CAB_IMPTO1, CAB.PRO_CAB_IMPTOTAL";

			$xsql_total = pg_query($conector_id, $sql_x_moneda);
			$rs = pg_fetch_array($xsql_total,0);

			$c_tcambio=$rs[0];
			$c_total_dolar=$rs[1];

			$c_v_venta=$rs[2];
			$c_impuesto=$rs[3];
			$c_total=$rs[4];

			$c_costeo=$rs[5];
		}
		else {
			$sql_x_moneda = "SELECT
							CAB.PRO_CAB_IMPAFECTO, CAB.PRO_CAB_IMPTO1, CAB.PRO_CAB_IMPTOTAL
							, sum(round((mov_cantidad*mov_costounitario),2))
						FROM
							CPAG_TA_CABECERA CAB,
							INV_TA_COMPRAS_DEVOLUCIONES DEV
						WHERE
							CAB.PRO_CAB_TIPDOCUMENTO=DEV.CPAG_TIPO_PAGO AND
							CAB.PRO_CAB_SERIEDOCUMENTO=DEV.CPAG_SERIE_PAGO AND
							CAB.PRO_CAB_NUMDOCUMENTO=DEV.CPAG_NUM_PAGO AND
							pro_codigo=mov_entidad and
							trim(CAB.PRO_CAB_MONEDA)='01' and
							trim(CAB.PRO_CAB_TIPDOCUMENTO)||trim(CAB.PRO_CAB_SERIEDOCUMENTO)||trim(CAB.PRO_CAB_NUMDOCUMENTO)||trim(CAB.PRO_CODIGO)='$c_clave'
						GROUP BY
							CAB.PRO_CAB_IMPAFECTO, CAB.PRO_CAB_IMPTO1, CAB.PRO_CAB_IMPTOTAL";

			$xsql_total = pg_query($conector_id, $sql_x_moneda);
			$rs = pg_fetch_array($xsql_total,0);

			$c_tcambio="";
			$c_total_dolar="";

			$c_v_venta=$rs[0];
			$c_impuesto=$rs[1];
			$c_total=$rs[2];

			$c_costeo=$rs[3];
		}

		//echo $sql_x_moneda;

		echo '<tr class="letra_detalle">';
		echo "<td align='center'>".$c_fecha."</td>";
		echo "<td align='center'>".$c_tipo."</td>";
		echo "<td align='center'>".$c_serie."</td>";
		echo "<td align='center'>".$c_numero."</td>";
//		echo "<td>&nbsp;".$c_proveedor."</td>";
		echo '<td>';
		echo $c_proveedor." - ".$c_descprov;
		echo '</td>';
		echo "<td>".$c_rubro."</td>";
		echo "<td align='right'>".$c_tcambio."</td>";
		echo "<td align='right'>".$c_total_dolar."</td>";
		echo "<td align='right'>".$c_v_venta."</td>";

		echo "<td align='right'>".$c_impuesto."</td>";
		echo "<td align='right'>".$c_total."</td>";
		echo "<td align='right'>".$c_costeo."</td>";
		$v_irow1++;

		$total_total_dolares += $c_total_dolar;
		$total_v_venta += $c_v_venta;
		$total_impuesto += $c_impuesto;
		$total_total += $c_total;
	}
}

	echo '<tr class="letra_detalle">';

	echo "<td align='right' colspan='8'>T O T A L : &nbsp;&nbsp;</td>";
	echo "<td align='right'>".$total_total_dolares."</td>";
	echo "<td align='right'>".$total_v_venta."</td>";
	echo "<td align='right'>".$total_impuesto."</td>";
	echo "<td align='right'>".$total_total."</td>";


?>
</table>
</html>
