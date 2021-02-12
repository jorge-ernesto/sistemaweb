<?php
include("config.php");

function obtieneTrabajadores($almacen, $estado, $codigo) {
	if ($codigo != "") $condicion = "and ch_codigo_trabajador~'".pg_escape_string($codigo)."'";
	$query = "select trim(ch_codigo_trabajador), trim(ch_apellido_materno)||' '||trim(ch_apellido_paterno)||', '||trim(ch_nombre1)||' '||trim(ch_nombre2) from pla_ta_trabajadores where ch_almacen='$almacen' and ch_motivo_retiro='$estado' $condicion;";
	return pg_query($query);
}

function obtieneProveedores($codigo) {
	if ($codigo != "") $condicion = "WHERE TRIM(pro_codigo)||''||TRIM(pro_razsocial) ~ '".pg_escape_string($codigo)."'";
	$query = "select trim(pro_codigo), trim(pro_razsocial) from int_proveedores $condicion order by pro_razsocial;";
	return pg_query($query);
}

function obtieneClientes($codigo) {
	if ($codigo != "") $condicion = "WHERE TRIM(cli_codigo)||''||TRIM(cli_razsocial) ~ '".pg_escape_string($codigo)."'";
	$query = "select trim(cli_codigo), trim(cli_razsocial) from int_clientes $condicion order by cli_razsocial;";
	return pg_query($query);
}

function obtieneArticulos($codigo) {
	if ($codigo != "") $condicion = "WHERE TRIM(art_codigo)||''||TRIM(art_descripcion) ~ '".pg_escape_string($codigo)."'";
	$query = "select trim(art_codigo), trim(art_descripcion) from int_articulos $condicion order by art_descripcion;";
	return pg_query($query);
}

function obtieneLineas($codigo) {
	if ($codigo != "") $condicion = "AND tab_elemento~'".pg_escape_string($codigo)."'";
	$query = "select trim(tab_elemento), trim(tab_descripcion) from int_tabla_general where tab_tabla='20' $condicion order by tab_elemento;";
	return pg_query($query);
}

function obtieneNroTarjeta($codigo, $codcliente) {

    if ($codigo != "") 
    {
    $condicion = "WHERE numtar~'".pg_escape_string($codigo)."' ".
		 "AND codcli ~ '".pg_escape_string($codcliente)."'";    
    }
    if ($codcliente!="" && $codigo != "") 
    {
    //$condicion = "WHERE numtar~'".pg_escape_string($codigo)."' ".
	//	 "AND codcli ~ '".pg_escape_string($codcliente)."'";    
    }
    else
    {
    $condicion = "WHERE codcli~'".pg_escape_string($codcliente)."' ";
		 //"AND codcli ~ '".$codcliente."'";
    }
    $query = "SELECT trim(numtar), trim(codcli) FROM pos_fptshe1 $condicion ORDER BY numtar";
    //return pg_query($query);
    $result = pg_query($query);
    $numrows = pg_num_rows($result);
    /*if($numrows < 0);
    {
        echo "No se han encontrado Numeros de Tarjetas con el Cliente $codcliente \n";
    }*/
    //echo "NUMROWS : $numrows \n";
    //codcli, numtar
}

if (isset($_POST['lista']) && !isset($_POST['Buscar'])) {
?>
<script language="JavaScript">
<!--
<?php
    if ($_POST['consulta'] == "articulos") {
	$rs = obtieneArticulos($_POST['lista']);
	$a = pg_fetch_array($rs, 0);
	$desc1 = $a[1];
?>
    opener.document.<?php echo $_POST['des']; ?>.value = '<?php echo $desc1; ?>';
<?php
    }
    if ($_POST['consulta'] == "trabajadores") {
	$rs = obtieneTrabajadores($_SESSION['almacen'], 0, $_POST['lista']);
	$a = pg_fetch_array($rs, 0);
	$desc1 = $a[1];
?>
    opener.document.<?php echo $_POST['des']; ?>.value = '<?php echo $desc1; ?>';

<?php
    }
    if ($_POST['consulta'] == "proveedores") {
	$rs = obtieneProveedores($_POST['lista']);
	$a = pg_fetch_array($rs, 0);
	$desc1 = $a[1];
?>
    opener.document.<?php echo $_POST['des']; ?>.value = '<?php echo $desc1; ?>';

<?php
    }
    if ($_POST['consulta'] == "lineas") {
	$rs = obtieneLineas($_POST['lista']);
	$a = pg_fetch_array($rs, 0);
	$desc1 = $a[1];
?>
    opener.document.<?php echo $_POST['des']; ?>.value = '<?php echo $desc1; ?>';

<?php
    }
    if ($_POST['consulta'] == "clientes") {
	$rs = obtieneClientes($_POST['lista']);
	$a = pg_fetch_array($rs, 0);
	$desc1 = $a[1];
	if($_GET['des'])
	{
?>  
    opener.document.<?php echo $_POST['des']; ?>.value = '<?php echo $desc1; ?>';
<?php
        }
    }
?>
    window.close();
//-->
</script>
<?php
}

switch ($_GET['consulta']) {
    case "trabajadores":
	$rs = obtieneTrabajadores($_SESSION['almacen'], 0, $_POST['busca']);
	break;
    case "proveedores":
	$rs = obtieneProveedores($_POST['busca']);
	break;
    case "clientes":
	$rs = obtieneClientes($_POST['busca']);
	break;
    case "articulos":
	$rs = obtieneArticulos($_POST['busca']);
	break;
    case "lineas":
	$rs = obtieneLineas($_POST['busca']);
	break;
    case "tarjetas":
	$rs = obtieneNroTarjeta($_POST['busca'], $_GET['des']);
	break;	
}

/*if($lista==""){
	if($Buscar=="Buscar"){
		if($busca!=""){
		$rs = ayuda($_GET['consulta'],$busca,$busca);
		}else{
		$rs = ayuda($_GET['consulta'],"null","null");
		}
	}else{
		$rs = ayuda($_GET['consulta'],"null","null");
	}
}else{
	if($Buscar!="Buscar"){
	$rs = ayuda($_GET['consulta'],$lista,"null");
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
}*/

pg_close();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<title>Ayuda Proveedores</title>
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

function pasarValorOpenerSD(lista,form,cod){
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
  <input type="hidden" name="des" value="<?php echo $_GET['des'];?>">
  <input type="hidden" name="cod" value="<?php echo $_GET['cod'];?>">
  <input type="hidden" name="consulta" value="<?php echo $_GET['consulta'];?>">
  <br>
  <select name="lista" size="12">
  <?php for($i=0;$i<pg_numrows($rs);$i++){
  		$A = pg_fetch_array($rs,$i);
  		print "<option value='$A[0]'>$A[0] -- $A[1]</option>\n";
  } ?>
  </select>
  <br>
  <input type="button" name="Seleccionar" value="Seleccionar" onClick="javascript:pasarValorOpener(lista,form1,'<?php echo $cod;?>','<?php echo $des;?>')">
  <?php//}?>
  
</form>
</body>
</html>
