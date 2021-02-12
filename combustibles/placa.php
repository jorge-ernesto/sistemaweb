<?php

include("config.php");
include("/sistemaweb/start.php");
include("movimientos/c_ruta_granel.php");
include("movimientos/m_ruta_granel.php");
include("movimientos/t_ruta_granel.php");

function obtieneClientes($codigo) {

	if ($codigo != "") {
		$condicion = ' WHERE marca like "%'.trim($codigo).'%"';
	}

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
		
	$sql = 'select placa,marca,id_placa from placa '.$condicion.';';
		
	$datos = Array();
	$res = mssql_query($sql, $mssql);

	if ($res===FALSE) {
		return "Error al obtener datos de la tabla Aux_Tesacom";
	}			

	for ($i = 0; $i < mssql_num_rows($res); ++$i) {
        	$row = mssql_fetch_row($res);
        	$datos[$i]['cod']  = trim($row[0]);
		$datos[$i]['des']  = trim($row[1]);
		$datos[$i]['xtra'] = trim($row[2]);
     	}			

	mssql_free_result($res);
	mssql_close($mssql);
	
	return $datos;
}

if (isset($_POST['lista']) && !isset($_POST['Buscar'])) {
	
	?>
	<script language="JavaScript">
	<!--
	<?php
	        if ($_POST['consulta'] == "placas") {
		//$clie = obtieneClientes();
		$clie = obtieneClientes($_POST['lista']);
		$dese1  = $clie[0]['des'];
		$xtra1  = $clie[0]['xtra'];
		if($_GET['des']) {
	?>  opener.document.<?php echo $_POST['des']; ?>.value = '<?php echo $xtra1; ?>';
		alert("<?php echo '+++ la campania es: '.$clie; ?> ");
	<?php
		}		
	   }
	?>
	    window.close();
	//-->
	</script>
	<?php
} 

/*switch ($_GET['consulta']) {
    case "placas":*/
	$datos = obtieneClientes($_POST['busca']);
	//break;
//}

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
	form.submit();
	window.close();
}
</script>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="/sistemaweb/sistemaweb.css" type="text/css">
</head>
<body>
<form name="form1" method="post" action="">B:
  	<input type="text" name="busca" value='<?php echo $_POST['busca']; ?>'>
  	<input type="submit" name="Buscar" value="Buscar">
  	<input type="hidden" name="cod" value="<?php echo $_GET['cod'];?>">
  	<input type="hidden" name="des" value="<?php echo $_GET['des'];?>">
  	<input type="hidden" name="xtra" value="<?php echo $_GET['xtra'];?>">
  	<input type="hidden" name="consulta" value="<?php echo $_GET['consulta'];?>">
  	<br>
  	<select name="lista" size="14" width="200" style="width: 200px">
  	<?php 
  	//$datos = obtieneClientes("");
  	for($i=0;$i<count($datos);$i++){
  		$ccod = $datos[$i]['cod'];
  		$ddes = $datos[$i]['des'];
  		$xxtra = $datos[$i]['xtra'];
  		print "<option value='$ccod'>$ccod -- $ddes</option>\n";
  	} ?>
  	</select>
  	<br>
  	<input type="button" name="Seleccionar" value="Seleccionar" onClick="javascript:pasarValorOpener(lista,form1,'<?php echo $cod;?>','<?php echo $des;?>','<?php echo $xtra;?>')">
</form>
</body>
</html>
