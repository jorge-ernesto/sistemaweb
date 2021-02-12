<?php

if(isset($_GET['v']) && $_GET['v'] == 'old') {
extract($_REQUEST);
include("../menu_princ.php");

if(!isset($mes)) { 
	$mes = date("m"); 
}
if(!isset($ano)) { 
	$ano = date("Y"); 
}

$txtdesc = strtoupper($txtdesc);

if(strlen(trim($txtcod)) > 0) { 
	$vwhere1 = " and s.art_codigo like '%$txtcod%' "; 
}
if(strlen(trim($txtdesc))>0) { 
	$vwhere2 = " and a.art_descripcion like '%$txtdesc%' "; 
}
if(strlen(trim($txtlinea))>0) { 
	$vwhere3 = " and a.art_linea like '%$txtlinea%' "; 
}

$vwhere = $vwhere1.$vwhere2.$vwhere3;
$sqlsaldos = "	SELECT
			s.art_codigo,
			a.art_descripcion,
			s.stk_stock".$mes.",
			s.stk_fisico".$mes.",
			s.stk_costo".$mes.",
			s.stk_stockinicial,
			s.stk_costoinicial
		FROM
			inv_saldoalma s,
			int_articulos a 
		WHERE
			s.stk_almacen='$alm' 
			AND s.art_codigo=a.art_codigo 
			AND stk_periodo='$ano' ".$vwhere." 
		ORDER BY
			1 ".$bddsql." ";

//echo $sqlsaldos."\n\n";
$xqlsaldos = pg_exec($coneccion,$sqlsaldos);
$ilimit = pg_numrows($xqlsaldos);

if($ilimit > 0) {
	$numeroRegistros = $ilimit;
}
?>

<html>
<head>
<title>Control de Inventarios</title>
</head>
<body>
<form action="inv_saldosmensuales.php" method="post">
<p align="center">SALDOS MENSUALES DE ALMACEN</p>
<table border="1">
	<tr>
		<th colspan="6">PERIODO DE CIERRE:</th>
		<th>&nbsp;</th>
	</tr>
    	<tr>
      		<th>A&Ntilde;O:</th>
      		<th><input name="ano" type="text" size="6" maxlength="4" onKeyPress="return esInteger(event)" value='<?php echo $ano;?>'></th>
      		<th>MES:</th>
      		<th><input name="mes" type="text" size="4" maxlength="2" onKeyPress="return esInteger(event)" value='<?php echo $mes;?>'></th>
      		<th>ALMACEN:</th>
      		<th><input name="alm" type="text" size="5" maxlength="3" onKeyPress="return esInteger(event)" value='<?php echo $alm;?>'></th>
      		<th><input name="boton" type="submit" value="Consultar"></th>
    	</tr>
    	<tr>
      		<th colspan="6">COD.ART.<input name="txtcod" type="text" size="20" maxlength="13" value="<?php echo $txtcod; ?>">
       		DESC.ART.
        	<input name="txtdesc" type="text" size="20" maxlength="20" value="<?php echo $txtdesc; ?>">
		LINEA DE ARTICULO
		<input name="txtlinea" type="text" size="20" maxlength="20" onKeyPress="return esInteger(event)" value="<?php echo $txtlinea; ?>">
      		<th>&nbsp;</th>
    	</tr>
</table>
<?php
if($flg != "A") {
	$ft = fopen('saldosmensuales.csv','w');
	if ($ft > 0) {
		$snewbuffer = "PERIODO DE CIERRE \n";
		$snewbuffer = $snewbuffer."PERIODOS DE CIERRE AÑO".$ano." MES ".$mes." \n";
		$snewbuffer = $snewbuffer."COD. ART :".$txtcod." DESC. ART. :".$txtdesc." LINEA. ART. :".$txtlinea." \n";
		$snewbuffer = $snewbuffer."CODIGO,DESCRIPCION,STOCK,S.FISICO,COSTO P.,STK.INI.AÑO,C.INI.AÑO,ALM,TIPO PLU \n";
	}

	$var_pers = "&alm=".$alm."&ano=".$ano."&mes=".$mes."&linea=".$txtlinea;
	include("../maestros/pagina.php");
	$bddsql = " limit $tamPag offset $limitInf ";
?>
<table border="1">
	<tr>
		<th>CODITEM</th>
      		<th>DESCRIPCION</th>
      		<th>STOCK</th>
      		<th>S.FISICO</th>
      		<th>COSTOP.</th>
      		<th>STK.INI.A&Ntilde;O</th>
      		<th>C.INI.A&Ntilde;O </th>
      		<th>ALM</th>
		<th>TIPO PLU</th>
    	</tr>
<?php
$sqlsaldos = "	SELECT
			s.art_codigo,
			a.art_descripcion,
			s.stk_stock".$mes.",
			s.stk_fisico".$mes.",
			s.stk_costo".$mes.",
			s.stk_stockinicial,
			s.stk_costoinicial,
			s.stk_almacen,
			a.art_plutipo
		FROM
			inv_saldoalma s,
			int_articulos a 
		WHERE
			s.stk_almacen='$alm' 
			AND s.art_codigo=a.art_codigo 
			AND stk_periodo='$ano' ".$vwhere." 
		ORDER BY
			1 ".$bddsql." ";
//echo $sqlsaldos."\n\n";
$xqlsaldos = pg_exec($coneccion,$sqlsaldos);
$ilimit = pg_numrows($xqlsaldos);

while($irow < $ilimit) {
	$a0 = pg_result($xqlsaldos,$irow,0);
	$a1 = pg_result($xqlsaldos,$irow,1);
	$a2 = pg_result($xqlsaldos,$irow,2);
	$a3 = pg_result($xqlsaldos,$irow,3);
	$a4 = pg_result($xqlsaldos,$irow,4);
	$a5 = pg_result($xqlsaldos,$irow,5);
	$a6 = pg_result($xqlsaldos,$irow,6);
	$a7 = pg_result($xqlsaldos,$irow,7);
	$a8 = pg_result($xqlsaldos,$irow,8);
?>	
<tr bgcolor="" onMouseOver="this.style.backgroundColor='#FFFFCC';this.style.cursor='hand';" onMouseOut="this.style.backgroundColor=''"o"];">
<?php
echo 	"<td>&nbsp;".$a0."</td>
	<td>&nbsp;".$a1."</td>
	<td align='right'>&nbsp;".$a2."</td>";

echo 	"<td align='right'>&nbsp;".$a3."</td>
	<td align='right'>&nbsp;".$a4."</td>
	<td align='right'>&nbsp;".$a5."</td>";

echo 	"<td align='right'>&nbsp;".$a6."</td>
	<td>&nbsp;".$a7."</td>
	<td>&nbsp;".$a8."</td></tr>";
	$irow++;
}
?>
<?php
$sqlsaldos = "	SELECT
			s.art_codigo,
			a.art_descripcion,
			s.stk_stock".$mes.",
			s.stk_fisico".$mes.",
			s.stk_costo".$mes.",
			s.stk_stockinicial,
			s.stk_costoinicial
		FROM
			inv_saldoalma s,
			int_articulos a 
		WHERE
			s.stk_almacen='$alm' 
			AND s.art_codigo=a.art_codigo 
			AND stk_periodo='$ano' ".$vwhere." 
		ORDER BY
			1 ";
// echo $sqlsaldos."\n\n";
$xqlsaldos = pg_exec($coneccion,$sqlsaldos);
$ilimit = pg_numrows($xqlsaldos);  
$irow = 0;

while($irow < $ilimit) {
	$a0 = pg_result($xqlsaldos,$irow,0);
	$a1 = pg_result($xqlsaldos,$irow,1);
	$a2 = pg_result($xqlsaldos,$irow,2);
	$a3 = pg_result($xqlsaldos,$irow,3);
	$a4 = pg_result($xqlsaldos,$irow,4);
	$a5 = pg_result($xqlsaldos,$irow,5);
	$a6 = pg_result($xqlsaldos,$irow,6);

	$snewbuffer = $snewbuffer.$a0.",".$a1.",".$a2.",".$a3.",".$a4.",".$a5.",".$a6.",".$alm." \n";
	$irow++;
}
?>
</table>

<button name="fm" value="" onClick="javascript:parent.location.href='saldosmensuales.csv';return false"><img src="/sistemaweb/images/excel_icon.png" alt="left"/> Excel</button>

<?php
fwrite($ft,$snewbuffer);
fclose($ft);
} ?>
</form>
</body>
</html>
<?php pg_close($coneccion); 



} else { ?>

<!DOCTYPE html>
<html>
<head>
	<title>Saldos Mensuales - OpenSoft</title>
	<meta charset="UTF-8">
	<link rel="stylesheet" href="/sistemaweb/css/sistemaweb.css" type="text/css">
	<link rel="stylesheet" href="/sistemaweb/assets/css/style.css" type="text/css">
	<!--<script src="/sistemaweb/js/jquery-2.0.3.js" type="text/javascript"></script>-->
	<script src="/sistemaweb/assets/js/jquery/jquery-3.2.0.min.js" type="text/javascript"></script>
	<script type="text/javascript" src="/sistemaweb/helper/js/autocomplete.js" ></script>
	<script src="/sistemaweb/js/jquery-1.9.1.js" type="text/javascript"></script>
    <link rel="stylesheet" href="/sistemaweb/css/jquery-ui.css" />
    <link rel="stylesheet" href="/sistemaweb/helper/css/style.css" />
    <script src="/sistemaweb/js/jquery-ui.js"></script>

	<script src="/sistemaweb/inventarios/js/saldos_mensuales.js"></script>
</head>
<body>
	<?php include "../menu_princ.php"; ?>
	<div id="footer">&nbsp;</div>
	<div id="cargardor" style="position: absolute;display: none"><img src="/sistemaweb/ventas_clientes/liquidacion_vales/cg.gif" /></div>

	<?php

	include('/sistemaweb/include/mvc_sistemaweb.php');
	include('reportes/t_saldos_mensuales.php');
	include('reportes/m_saldos_mensuales.php');

	//Variables de Entrada

	$hoy = date('d/m/Y');

	$model = new ModelSaldosMensuales;
	$template = new TemplateSaldosMensuales;

	$estaciones	= $model->GetAlmacen('T');
	$lineas		= $model->GetLinea();
	echo $template->Form($estaciones, $lineas, $hoy);

	?>
</body>
</html>

<?php }