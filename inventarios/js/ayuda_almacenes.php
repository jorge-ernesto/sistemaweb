<?php

include("config.php");

if($lista==""){
	if($Buscar=="Buscar"){ //presion� buscar

		if($busca!=""){ //defini� que buscar
			$q = "select
					ch_almacen,
					ch_nombre_almacen
				from
					inv_ta_almacenes 
				where	
					ch_nombre_almacen like '%$busca%' AND
					ch_clase_almacen='$flag'";

			$rs = pg_exec($q);

		//$rs = ayuda($consulta,$busca,$busca);

		}else{ //presion� buscar dejando en blanco el campo de busqueda

			$q="select
					ch_almacen,
					ch_nombre_almacen
				from
					inv_ta_almacenes
				where
					ch_clase_almacen='$flag'";

			$rs = pg_exec($q);

		}

	}else{
		//$rs = combo("proveedores");
		$q="select
				ch_almacen,
				ch_nombre_almacen
			from
				inv_ta_almacenes
			where
				ch_clase_almacen = '$flag'";

		$rs = pg_exec($q);

	}
}else{
	if($Buscar!="Buscar"){
		//$rs = pg_exec("select trim(pro_codigo), pro_razsocial from int_proveedores 
		//where trim(pro_codigo)='$lista'");

		$q="select
			ch_almacen,
			ch_nombre_almacen,
			ch_almacen || ' -- ' || ch_nombre_almacen
		    from
			inv_ta_almacenes
		    where
			ch_almacen='$lista' 
			and ch_clase_almacen='$flag'";

		$rs = pg_exec($q);
		$A = pg_fetch_array($rs,0);
		$desc1 = $A[1];

	?>
		<script language="JavaScript">

			var obj = opener.document.getElementById('<?php echo $des; ?>');
			obj.innerHTML = '<?php echo $desc1; ?>';


			window.close();

		</script>
	<?php

	}
}

pg_close();

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<title>Ayuda de Almacenes</title>
<head>
<script language="JavaScript">
function pasarValorOpener(lista,form,cod,des,des_campo){

	var valor = lista.value;
	//var index = lista.selectedIndex;
	//var descrip = lista.options[index].text;

	//opener.document.form1.cod.value = valor;
	//alert("opener.document."+cod+".value = '"+valor+"'");

	//<!--alorOpener(lista,form1,'alma_ori','alma_ori')"-->
	eval("opener.document.form1."+cod+".value = '"+valor+"'");
	//eval("opener.document.form1."+des_campo+".value = '"+descrip+"'");
	//opener.document.all(des).innerText = descrip;
	//obj.innerHTML = '<?php echo $desc1; ?>';
	form.submit();
	//window.close();
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
  <input type="hidden" name="flag" value="<?php echo $flag;?>">
  <input type="text" name="des_campo" value="<?php echo $des_campo;?>">
  <br>
  <select name="lista" size="12">
  <?php
	for($i=0;$i<pg_numrows($rs);$i++){
		$A = pg_fetch_array($rs,$i);	
	 		print "<option value='$A[0]'>$A[0] -- $A[1]</option>";
 	}
?>
  </select>


  <br>
  <input type="button" name="Seleccionar" value="Seleccionar" onClick="javascript:pasarValorOpener(lista,form1,'<?php echo $cod;?>','<?php echo $des;?>','<?php echo $des_campo;?>')">
</form>
</body>
</html>

