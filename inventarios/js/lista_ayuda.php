<?php
include("config.php");
include_once("funciones.php");

$almacen = $_REQUEST['almacen'];

if($lista=="") {
	if($Buscar=="Buscar") {
		if($busca!="") {
			//$q = "select trim(pro_codigo), pro_razsocial from int_proveedores where pro_razsocial like '%$busca%' ";
			//$rs = pg_exec($q);
			$rs = ayuda($consulta,$busca,$busca);
		} else {
			//$rs = combo("proveedores");
			$rs = ayuda($consulta,"null","null");
		}
	} else {
		if($valor == 'S' and $fm == '21' || $fm == '27' || $fm == '28') {

			$z = "SELECT
					art_codigo,
					art_descripcion
				FROM
					int_articulos
				WHERE
					art_codigo in('11620301','11620302','11620303','11620304','11620305','11620306','11620307')
				ORDER BY
					art_codigo";

			$rs = pg_exec($z);
		} else {
			
			if(trim($consulta) == 'articulos2' && $_REQUEST['fm'] == '05' || $_REQUEST['fm'] == '18' || $_REQUEST['fm'] == '16')
				$consulta = 'articulos_otros';

			$rs = ayuda($consulta,"null","null");
		}
	}

} else {
	if($Buscar!="Buscar") {
		$rs = ayuda($consulta,$lista,"null");
		$A = pg_fetch_array($rs,0);
		$desc1 = $A[1];
		$desc1 = str_replace("'"," ",$desc1);
		$desc1 = str_replace("\""," ",$desc1);
?>

<script>
	var almacen	= opener.document.form1.alma_des.value;
	document.cookie	='variable='+almacen+'; expires=thu, 2 Aug 2031 20:47:11 UTC; path=/';
</script>

<?php
if($consulta == 'articulos2')
	if(empty($almacen))
		$almacen = $_COOKIE["variable"];

	$costo_unitario	= costoUnitario($lista);
	$costo_promedio	= costoPromedio("actual","actual",$lista,trim($almacen));
	$art_stock	= stockArticulo("actual","actual",$lista,trim($almacen));

	if($consulta=="rubros_cp") {
		print "<script>opener.document.form1.cod_rubro.value='".trim($A[0])."';</script>";
	}
	/*if($consulta=="proveedores"){
		echo "<script>alert(".'HOLA'.");</script>";
	}*/
	//if ($consulta != "proveedores" and $consulta != "documentos") {
	//echo "<script>alert('".$consulta."');</script>";
?>
<script>
	//if ('<?php echo $consulta;?>' == 'articulos2') {
		//alert('<?php echo $consulta;?>');
		var valor = '<?php echo $valor;?>';
		//opener.document.all("<?php echo $des;?>").innerText = '<?php echo $desc1;?>';
		var obj = opener.document.getElementById('<?php echo $des; ?>');
		obj.innerHTML = '<?php echo $desc1; ?>';

		opener.document.form1.art_stock.value = '<?php echo $art_stock;?>';
		opener.document.form1.art_costo_uni.value='<?php echo $costo_unitario;?>';
		window.close();
	//}
</script>

<?php
	//}	
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
	Descripci&oacute;n art&iacute;culo: 
	<input type="text" name="busca" style="text-transform:uppercase;" onkeyup="javascript:this.value=this.value.toUpperCase();">
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

