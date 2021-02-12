<?php
include("config.php");

if($lista==""){
	if($Buscar=="Buscar"){
		if($busca!=""){
		//$q = "select trim(pro_codigo), pro_razsocial from int_proveedores where pro_razsocial like '%$busca%' ";
		//$rs = pg_exec($q);
		$rs = ayuda($consulta,$busca,$busca);
		}else{
		//$rs = combo("proveedores");
		$rs = ayuda($consulta,"null","null");
		}
	}else{
		//$rs = combo("proveedores");
		$rs = ayuda($consulta,"null","null");
	}
}else{
	if($Buscar!="Buscar"){
	//$rs = pg_exec("select trim(pro_codigo), pro_razsocial from int_proveedores 
	//where trim(pro_codigo)='$lista'");
	$rs = ayuda($consulta,$lista,"null");
	$A = pg_fetch_array($rs,0);
	$desc1 = $A[1];
	$desc1 = str_replace("'"," ",$desc1);
	$desc1 = str_replace("\""," ",$desc1);
	
	$costo_unitario = costoUnitario($lista);
	$costo_promedio = costoPromedio("actual","actual",$lista,trim($almacen));
	$art_stock = stockArticulo("actual","actual",$lista,trim($almacen));
	echo "************".$cod."----".$consulta."<br>";
	echo "************".$des."<br>";
	echo "************".$des_campo."<br>";
	
	if($consulta=="rubros_cp"){
		print "<script>opener.document.form1.cod_rubro.value='".trim($A[0])."';</script>";
	}
	
	?>
		<script>
		
		var valor = '<?php echo $valor;?>';
		//alert('<?php echo $desc1;?>');
		//opener.document.<?php echo $des;?>.value = '<?php echo $desc1;?>';
		opener.document.all("<?php echo $des;?>").innerText = '<?php echo $desc1;?>';
		//eval("opener.document.form1.<?php echo $des_campo;?>.value = '<?php echo $desc1;?>'");
		//eval("opener.document.form1.<?php echo $des;?>.value = '<?php echo $desc1;?>'");
		var obj = opener.document.getElementById('<?php echo $des; ?>');
		obj.innerHTML = '<?php echo $desc1; ?>';
		//alert("opener.document.form1.<?php echo $des_campo;?>.value = '<?php echo $desc1?>'");
		opener.document.form1.art_stock.value = '<?php echo $art_stock;?>';
		
		if(valor=="S"){
		opener.document.form1.art_costo_uni.value='<?php echo $costo_unitario;?>';
		}else{
		opener.document.form1.art_costo_uni.value='<?php echo $costo_promedio;?>';
		}
		window.close();
		</script>
	<?php
		
	
	
	}
}

pg_close();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<title>Ayuda </title>
<head>
<script language="JavaScript">
function pasarValorOpener(lista,form,cod,des,des_campo){
var valor = lista.value;
//alert(valor);
//opener.document.form1.cod_proveedor.value = valor;
//alert("opener.document."+cod+".value = '"+valor+"'");
eval("opener.document.form1."+cod+".value = '"+valor+"'");
//alert("opener.document.form1."+cod+".value = '"+valor+"'");
form.submit();
}

</script>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="/sistemaweb/css/sistemaweb.css" type="text/css">
</head>

<body>
<form name="form1" method="post" action="">
  Busqueda 
  <input type="text" name="busca">
  <input type="submit" name="Buscar" value="Buscar">
  <input type="hidden" name="des" value="<?php echo $des;?>">
  <input type="hidden" name="cod" value="<?php echo $cod;?>">
  <input type="hidden" name="consulta" value="<?php echo $consulta;?>">
  <input type="hidden" name="des_campo" value="<?php echo $des_campo;?>">
  <input type="hidden" name="valor" value="<?php echo $valor;?>">
  <br>
  <select name="lista" size="12">
  <?php for($i=0;$i<pg_numrows($rs);$i++){
  		$A = pg_fetch_array($rs,$i);	
  		print "<option value='$A[0]'>$A[0] -- $A[1] || $A[2] || $A[3]</option>";
  } ?>
  </select>
  <br>
  <input type="button" name="Seleccionar" value="Seleccionar" onClick="javascript:pasarValorOpener(lista,form1,'<?php echo $cod;?>','<?php echo $des;?>','<?php echo $des_campo;?>')">
</form>
</body>
</html>
