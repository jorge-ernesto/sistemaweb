<?php
include("config.php");

function obtieneTrabajadores($almacen, $estado, $codigo) {
	if ($codigo != "") $condicion = "and ch_codigo_trabajador~'".pg_escape_string($codigo)."'";
	$query = "select trim(ch_codigo_trabajador), trim(ch_apellido_materno)||' '||trim(ch_apellido_paterno)||', '||trim(ch_nombre1)||' '||trim(ch_nombre2) from pla_ta_trabajadores where ch_almacen='$almacen' and ch_motivo_retiro='$estado' $condicion;";
	return pg_query($query);
}

function obtieneProveedores($codigo) {
	if ($codigo != "") $condicion = "WHERE pro_codigo='".pg_escape_string($codigo)."'";
	$query = "select trim(pro_codigo), trim(pro_razsocial) from int_proveedores $condicion order by pro_razsocial;";
	return pg_query($query);
}

function obtieneClientes($codigo) {
	if ($codigo != "") $condicion = "WHERE cli_codigo~'".pg_escape_string($codigo)."'";
	$query = "select trim(cli_codigo), trim(cli_razsocial) from int_clientes $condicion order by cli_razsocial;";
	return pg_query($query);
}

function obtieneArticulos($codigo, $almacen) {

	if ($codigo != "") $condicion = "AND art.art_codigo~'".pg_escape_string($codigo)."'"; else $condicion="";

	$sqlmes = "SELECT now() as mes, now() - interval '1 month' as mes1, now() - interval '2 month' as mes2, now() - interval '3 month' as mes3; ";	
	$rs 	= pg_query($sqlmes);
	$a 	= pg_fetch_array($rs, 0);
	$mes   	= substr($a['mes'], 5, 2);
	$anio  	= substr($a['mes'], 0, 4);
	$mes01 	= substr($a['mes1'], 5, 2);
	$anio01	= substr($a['mes1'], 0, 4);
	$mes02 	= substr($a['mes2'], 5, 2);
	$anio02	= substr($a['mes2'], 0, 4);
	$mes03 	= substr($a['mes3'], 5, 2);
	$anio03	= substr($a['mes3'], 0, 4);

	$sql = "SELECT
			art.art_codigo as codigo,
			art.art_descripcion as descripcion,

			(SELECT v2.nu_can$mes03 FROM ven_ta_venta_mensualxitem v2 WHERE art.art_codigo=v2.art_codigo AND v2.ch_sucursal='$almacen' AND v2.ch_periodo='$anio03') as mes3,
			(SELECT v3.nu_can$mes02 FROM ven_ta_venta_mensualxitem v3 WHERE art.art_codigo=v3.art_codigo AND v3.ch_sucursal='$almacen' AND v3.ch_periodo='$anio02') as mes2,
			(SELECT v4.nu_can$mes01 FROM ven_ta_venta_mensualxitem v4 WHERE art.art_codigo=v4.art_codigo AND v4.ch_sucursal='$almacen' AND v4.ch_periodo='$anio01') as mes1,

			(SELECT s.stk_stock$mes FROM inv_saldoalma s WHERE art.art_codigo=s.art_codigo AND s.stk_almacen='$almacen' AND s.stk_periodo='$anio') as stk_actual,

			mm.stk_minimo as stk_minimo,
			mm.stk_maximo as stk_maximo
		FROM
			int_articulos art
			LEFT JOIN ven_ta_venta_mensualxitem vta ON (vta.art_codigo=art.art_codigo AND vta.ch_sucursal='$almacen' AND vta.ch_periodo='$anio')
			LEFT JOIN inv_stkminmax mm ON (mm.art_codigo=art.art_codigo AND mm.ch_almacen='$almacen')
			LEFT JOIN com_rec_pre_proveedor com ON (art.art_codigo=com.art_codigo) 
			LEFT JOIN int_proveedores pro ON (pro.pro_codigo=com.pro_codigo) 
		WHERE 	
			art.art_estado='0'
			$condicion
		ORDER BY 
			codigo; ";

		return pg_query($sql);
}

function obtieneLineas($codigo) {
	if ($codigo != "") $condicion = "AND tab_elemento~'".pg_escape_string($codigo)."'";
	$query = "select trim(tab_elemento), trim(tab_descripcion) from int_tabla_general where tab_tabla='20' $condicion order by tab_elemento;";
	return pg_query($query);
}

if (isset($_POST['lista']) && !isset($_POST['Buscar'])) {
?>
<script language="JavaScript">
<!--
<?php
    if ($_POST['consulta'] == "articulos") {
	$rs = obtieneArticulos($_POST['lista'],$_SESSION['almacen']);
	$a = pg_fetch_array($rs, 0);
	$desz = $a[1];

	if(trim($a[2])=='') 
		$a[2]=0; 
	$mes1z = $a[2];

	if(trim($a[3])=='') 
		$a[3]=0; 
	$mes2z = $a[3];

	if(trim($a[4])=='') 
		$a[4]=0; 
	$mes3z = $a[4];

	if(trim($a[5])=='') 
		$a[5]=0; 
	$actualz = $a[5];

	if(trim($a[6])=='') 
		$a[6]=0; 
	$miniz = $a[6];

	if(trim($a[7])=='') 
		$a[7]=0; 
	$maxiz = $a[7];

	$cantiz = 0;
?>
    opener.document.<?php echo $_POST['des']; ?>.value = '<?php echo $desz; ?>';
    opener.document.<?php echo $_POST['mes3']; ?>.value = '<?php echo $mes3z; ?>';
    opener.document.<?php echo $_POST['mes2']; ?>.value = '<?php echo $mes2z; ?>';
    opener.document.<?php echo $_POST['mes1']; ?>.value = '<?php echo $mes1z; ?>';
    opener.document.<?php echo $_POST['actual']; ?>.value = '<?php echo $actualz; ?>';
    opener.document.<?php echo $_POST['mini']; ?>.value = '<?php echo $miniz; ?>';
    opener.document.<?php echo $_POST['maxi']; ?>.value = '<?php echo $maxiz; ?>';
    opener.document.<?php echo $_POST['canti']; ?>.value = '<?php echo $cantiz; ?>';
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
	$rs = obtieneArticulos($_POST['busca'],$_SESSION['almacen']);
	break;
    case "lineas":
	$rs = obtieneLineas($_POST['busca']);
	break;
    case "tarjetas":
	$rs = obtieneNroTarjeta($_POST['busca'], $_GET['des']);
	break;	
}

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
  <input type="hidden" name="mes3" value="<?php echo $_GET['mes3'];?>">
  <input type="hidden" name="mes2" value="<?php echo $_GET['mes2'];?>">
  <input type="hidden" name="mes1" value="<?php echo $_GET['mes1'];?>">
  <input type="hidden" name="actual" value="<?php echo $_GET['actual'];?>">
  <input type="hidden" name="mini" value="<?php echo $_GET['mini'];?>">
  <input type="hidden" name="maxi" value="<?php echo $_GET['maxi'];?>">
  <input type="hidden" name="canti" value="<?php echo $_GET['canti'];?>">
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
