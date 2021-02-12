<?php
include("../config.php");
?>
<html><link rel="stylesheet" href="<?php echo $v_path_url; ?>sistemaweb.css" type="text/css">
<head>
<title>FORMULARIO</title>
<script>
function escojform(){
    opener.document.formular.tipform.value = document.formular2.escogtipo.value
//	window.opener.enviadatos()
	window.close()
}
</script>
</head>
<body>
FORMULARIOS
<hr noshade>
<form name='formular2'>
<input name='tipo1' type='text' value='<?php echo $escogitem;?>' size='20' maxlength='6'>
<input type="submit" name="boton" value="Ok">
<?php

 $tipo1=strtoupper($tipo1);
  $sqlai="select tran_codigo,tran_descripcion from inv_tipotransa where tran_codigo like '%".$tipo1."%' or tran_descripcion like '%".$tipo1."%' order by 1";
//  echo $sqlai;
  $xsqlai=pg_exec($coneccion,$sqlai);
  $ilimitai=pg_numrows($xsqlai);
if($ilimitai>0) {  ?><br>
<select name="escogtipo" size="10">
<?php	$irowai=0;
while($irowai<$ilimitai) {
	$rit0=pg_result($xsqlai,$irowai,0);
	$rit1=pg_result($xsqlai,$irowai,1);
	echo "<option value='".$rit0."'>".$rit0."-".$rit1."</option>";
	$irowai++;
}
?>
</select>
<input type="Button" name="boton" value="Seleccionar" onclick="escojform()">
<?php
  }
//}
?>
</form>
</body>
</html>
<?php pg_close($coneccion); ?>