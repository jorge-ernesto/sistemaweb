<?php
include("config.php");

if($lista==""){
	if($Buscar=="Buscar"){
		if($busca!=""){
		//$rs = ayuda($consulta,$busca,$busca);//anterior
		$rs = ayuda($consulta,strtoupper($busca),"null");//ahora
		
		}else{
		$rs = ayuda($consulta,"null","null");
		
		}
	}else{
		$rs = ayuda($consulta,"null","null");
	}
}else{
	if($Buscar!="Buscar"){
	$rs = ayuda($consulta,$lista,"null");
	$A = pg_fetch_array($rs,0);
	$desc1 = $A[1];
	?>
		<script language="JavaScript">
		//alert('<?php echo $des;?>');
		opener.document.<?php echo $des;?>.value = '<?php echo $desc1;?>';
		window.close();
		</script>
	<?php
	}
}

pg_close();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<title>Ayuda Trabajadores</title>
<head>
<script language="JavaScript">
function pasarValorOpener(lista,form,cod,des){
var valor = lista.value;
//alert(valor);
//opener.document.form1.cod_proveedor.value = valor;
//alert("form = '"+valor+"'");
//alert("opener.document."+cod+".value = '"+valor+"'");
eval("opener.document."+cod+".value = '"+valor+"'");
form.submit();
}

</script>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="/sistemaweb/sistemaweb.css" type="text/css">
</head>

<body>
<form name="form1" method="post" action="">
  Busqueda
  <input type="text" name="busca">
  <input type="submit" name="Buscar" value="Buscar">
  <input type="hidden" name="des" value="<?php echo $des;?>">
  <input type="hidden" name="cod" value="<?php echo $cod;?>">

  <input type="hidden" name="consulta" value="<?php echo $consulta;?>">
  <br>
  <select name="lista" size="12">
  <?php for($i=0;$i<pg_numrows($rs);$i++){
  		$A = pg_fetch_array($rs,$i);
  		print "<option value='$A[0]'>$A[0] -- $A[1]</option>";
  } ?>
  </select>
  <br>
  <input type="button" name="Seleccionar" value="Seleccionar" onClick="javascript:pasarValorOpener(lista,form1,'<?php echo $cod;?>','<?php echo $des;?>')">
</form>
</body>
</html>
