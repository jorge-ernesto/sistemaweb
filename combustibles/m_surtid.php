<?php
include("../config.php");
include("../pos_combustibles/inc_top.php");
include("../functions.php");
?>
<html>
<head>
<title>sistemaweb</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
CONFIGURACION DE SURTIDORES
<hr noshade><br>
<form action='' method='post'>
<table border="1" cellspacing="0" cellpadding="0">
  <tr>
    <th>&nbsp;</th>
    <th>SURTIDOR</th>
    <th>TANQUE</th>
    <th>PRODUCTO</th>
    <th>ULT LECT GAL</th>
    <th>FECHA ULT LECT</th>
    <th>HORA ULT LECT</th>
  </tr>
<?php
$irow=0;
$sql=" select ch_surtidor,ch_codigocombustible,ch_tanque
from comb_ta_surtidores ";
$xsql=pg_exec($coneccion,$sql);
$ilimit=pg_numrows($xsql);
while($irow<$ilimit) {
	$a0=pg_result($xsql,$irow,0);
	$a1=pg_result($xsql,$irow,1);
	$a2=pg_result($xsql,$irow,2);
	$a3=pg_result($xsql,$irow,3);
	$a4=pg_result($xsql,$irow,4);
	echo "<tr><td>&nbsp;</td><td>&nbsp;".$a0."</td><td>&nbsp;".$a1."</td>";
    echo "<td align='right'>&nbsp;".$a2."</td><td align='right'>&nbsp;".$a3."</td>";
    echo "<td align='right'>&nbsp;".$a4."</td><td>&nbsp;</td></tr>";
	$irow++;
}
?>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
</table>
</form>
<br>
<input name="boton" type="submit"  value="Modificar">&nbsp;&nbsp;&nbsp;
<input name="boton" type="submit" value="Eliminar">
</body>
</html>
<?php include("../close_connect.php"); ?>