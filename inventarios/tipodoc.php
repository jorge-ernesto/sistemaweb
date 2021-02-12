<?php include("../config.php"); ?>
<html><link rel="stylesheet" href="<?php echo $v_path_url; ?>sistemaweb.css" type="text/css">
<head>
<title>integrado</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<script>
function escogtipodoc(){
    opener.document.formular.updtipodocref.value = document.formular1.updtipodocref.value
	window.opener.enviadatos()
	window.close()
}
</script>
</head>

<body>
TIPO DE DOCUMENTO 
<hr noshade>
<form name='formular1'>
<input name="updtipdoc1" type="text" size="15" maxlength="20" value="<?php echo $updtipdoc1;?>">
  <input type="submit" name="boton2" value="Ok"><br>
<select name="updtipodocref" size="10">
<?php
$updtipdoc1=strtoupper($updtipdoc1);
$sqltipdoc="select tab_elemento,tab_descripcion from int_tabla_general where tab_tabla='08' and (tab_elemento like '%".$updtipdoc1."%' or tab_descripcion like '%".$updtipdoc1."%') order by 2";
echo $sqltipdoc;
$xsqltipdoc=pg_exec($coneccion,$sqltipdoc);
$ilimittipdoc=pg_numrows($xsqltipdoc);
while($irowtipdoc<$ilimittipdoc) {
 $td0=pg_result($xsqltipdoc,$irowtipdoc,0);
 $td1=pg_result($xsqltipdoc,$irowtipdoc,1);
 if(substr($td0,4,2)==$movtipodocref) {
	echo "<option value='".substr($td0,4,2)."' selected>".$td1." - ".substr($td0,4,2)."</option>";
 }else{
	echo "<option value='".substr($td0,4,2)."'>".$td1." - ".substr($td0,4,2)."</option>";
 }
 $irowtipdoc++;
}
?>
        </select>
  <input type="Button" name="boton" value="Seleccionar" onclick="escogtipodoc()">
</form>
</body>
</html>
<?php pg_close($coneccion); ?>