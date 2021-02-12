<?php
include("config.php");

include("/sistemaweb/start.php");
include ("movimientos/c_venta_granel.php");
include ("movimientos/m_venta_granel.php");
include ("movimientos/t_venta_granel.php");

/*function obtieneTrabajadores($almacen, $estado, $codigo){

	if ($codigo != "") $condicion = "and ch_codigo_trabajador~'".pg_escape_string($codigo)."'";
		$query = "select trim(ch_codigo_trabajador), trim(ch_apellido_materno)||' '||trim(ch_apellido_paterno)||', '||trim(ch_nombre1)||' '||trim(ch_nombre2) from pla_ta_trabajadores where ch_almacen='$almacen' and ch_motivo_retiro='$estado' $condicion;";
	return pg_query($query);
}*/

function obtieneClientes($codigo){

	$nombre = $codigo;
	$codigo = $_POST['lista'];

	$Parametros = VentaGranelModel::obtenerParametros();
		
	$MSSQLDBHost = $Parametros[0];
	$MSSQLDBUser = $Parametros[1];
	$MSSQLDBPass = $Parametros[2];
	$MSSQLDBName = $Parametros[3];

	$mssql = mssql_connect($MSSQLDBHost,$MSSQLDBUser,$MSSQLDBPass);		
		
	if ($mssql===FALSE) {

		return "Error al conectarse a la base de datos de Energigas";

	}				

	mssql_select_db($MSSQLDBName, $mssql);
		
	$sql = "SELECT DISTINCT
			Agenda.RUC,
			Case TipoPersona When 115 Then 'Natural' When 116 Then 'Juridico' End As TipoPersona,
			Replace(AgendaNombre, ',', '') As RazonSocial, 
			(Select Top 1 Direccion From Direcciones As D1 Where TipoDireccion = 127 And D1.Op = Agenda.ID_Agenda) As Direccion
		FROM
			Agenda
		INNER JOIN
			Aux_Energigas_Clientes On Agenda.ID_Agenda = Aux_Energigas_Clientes.ID_Agenda";

	if ($codigo != ""){
		$sql .= "
			WHERE Agenda.ID_Agenda = '".pg_escape_string($codigo)."'";
	}elseif($nombre != ""){
		$sql .= "
			WHERE Agenda.AgendaNombre like '%".pg_escape_string($nombre)."%' ";
	}
		
	$sql .=	
		"
		ORDER BY 
			Agenda.Ruc;";
	
	//echo $sql;

	$datos = Array();
	$res = mssql_query($sql, $mssql);

	if ($res===FALSE) {
		return "Error al obtener datos de la tabla Aux_Tesacom";
	}
			
	for ($i = 0; $i < mssql_num_rows($res); ++$i) {
        	$row = mssql_fetch_row($res);
        	$datos[$i]['cod']  = trim($row[0]);
		$datos[$i]['des']  = trim($row[2]);
		$datos[$i]['xtra'] = trim($row[3]);
     	}		
	
	mssql_free_result($res);
	mssql_close($mssql);
	
	return $datos;
}

if(isset($_POST['lista']) && !isset($_POST['Buscar'])) {
	
	?>
	<script language="JavaScript">
	<!--
	<?php
	   if ($_POST['consulta'] == "trabajadores") {
		$rs = obtieneTrabajadores($_SESSION['almacen'], 0, $_POST['lista']);
		$a = pg_fetch_array($rs, 0);
		$desc1 = $a[1];
	?>
	    opener.document.<?php echo $_POST['des']; ?>.value = '<?php echo $desc1; ?>';
	
	<?php

	    }

	    if ($_POST['consulta'] == "clientes") {
		
		$cli     	= obtieneClientes($_POST['lista']);
		$des1	 	= $cli[0]['des'];
		$xtra1  	= $cli[0]['xtra'];

		if($_GET['des']) {

	?>   opener.document.<?php echo $_POST['des']; ?>.value = '<?php echo $des1; ?>';
		//alert("<?php echo '+++ la campania es: '.$cli[0][1] ; ?> ");

	<?php
		}
		
		if($_GET['xtra']) {
	?>		opener.document.<?php echo $_POST['xtra']; ?>.value = '<?php echo $xtra1; ?>';	
	<?php
		}
	?>
		if(opener.document.<?php echo $_POST['xtra']; ?>.value == ''){
			alert("<?php echo 'El cliente '.$des1.' no tiene direccion fiscal seleccionar otro' ; ?> ");
			opener.document.<?php echo $_POST['cod']; ?>.value = '';
			opener.document.<?php echo $_POST['des']; ?>.value = '';
		}
	<?php	
	    }
	?>
	    window.close();
	//-->
	</script>
	<?php
} 

switch ($_GET['consulta']) {
   /* case "trabajadores":
	$rs = obtieneTrabajadores($_SESSION['almacen'], 0, $_POST['busca']);
	break;*/
    case "clientes":
	$datos = obtieneClientes($_POST['busca']);
	break;
}

pg_close();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<title>Ayuda Granel</title>
<head>
<script language="JavaScript">
function pasarValorOpener(lista,form,cod,des,xtra){
	var valor = lista.value;
	eval("opener.document."+cod+".value = '"+valor+"'");
	//eval("opener.document.form1."+cod+".value = '"+valor+"'");
	form.submit();
}
</script>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="/sistemaweb/sistemaweb.css" type="text/css">
</head>
<body>
<form name="form1" method="post" action="">Busqueda 
  	<input type="text" name="busca">
  	<input type="submit" name="Buscar" value="Buscar">
  	<input type="hidden" name="cod" value="<?php echo $_REQUEST['cod'];?>">
  	<input type="hidden" name="des" value="<?php echo $_REQUEST['des'];?>">
  	<input type="hidden" name="xtra" value="<?php echo $_REQUEST['xtra'];?>">
  	<input type="hidden" name="consulta" value="<?php echo $_REQUEST['consulta'];?>">
  	<br>
  	<select name="lista" size="12">
  	<?php 
  	//$datos = obtieneClientes("");
  	for($i=0;$i<count($datos);$i++){
  		$ccod  = $datos[$i]['cod'];
  		$ddes  = $datos[$i]['des'];
  		$xxtra = $datos[$i]['xtra'];
  		print "<option value='$ccod'>$ccod -- $ddes</option>\n";
  	} ?>
  	</select>
  	<br>
  	<input type="button" name="Seleccionar" value="Seleccionar" onClick="javascript:pasarValorOpener(lista,form1,'<?php echo $cod;?>','<?php echo $des;?>','<?php echo $xtra;?>')">
</form>
</body>
</html>
