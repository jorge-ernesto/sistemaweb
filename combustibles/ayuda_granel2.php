<?php

include("config.php");
include("/sistemaweb/start.php");
include ("movimientos/c_venta_granel.php");
include ("movimientos/m_venta_granel.php");
include ("movimientos/t_venta_granel.php");

function obtieneTrabajadores($almacen, $estado, $codigo){

	if ($codigo != "") $condicion = "and ch_codigo_trabajador~'".pg_escape_string($codigo)."'";
		$query = "select trim(ch_codigo_trabajador), trim(ch_apellido_materno)||' '||trim(ch_apellido_paterno)||', '||trim(ch_nombre1)||' '||trim(ch_nombre2) from pla_ta_trabajadores where ch_almacen='$almacen' and ch_motivo_retiro='$estado' $condicion;";
	return pg_query($query);
}

function obtieneClientes($codigo) {
	global $sqlca;
	$nombre = $codigo;
	$codigo = $_POST['lista'];

/*?><script>alert("<?php echo '+++ la campania es: '.$codigo ; ?> ");</script><?php
?><script>alert("<?php echo '+++ la campania es: '.$nombre ; ?> ");</script><?php*/

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
		
	$sql = "Select
			AgendaAnexo.ID As codigo,
			Direccion_Despacho As Descripcion,
			Aux_Energigas_Clientes.Precio_Galon,
			Aux_Energigas_Clientes.Precio_Kilos
		FROM
			AgendaAnexo
		Inner Join
			Aux_Energigas_Clientes On AgendaAnexo.ID = Aux_Energigas_Clientes.Op_Anexo
		Inner Join
			Agenda On Aux_Energigas_Clientes.ID_Agenda = Agenda.ID_Agenda";

	if ($codigo != "" and $nombre != ""){
		$sql .= "
			WHERE AgendaAnexo.ID = '".pg_escape_string($codigo)."'";
	}elseif($nombre != "" and $codigo == ""){
		$sql .= "
			WHERE Agenda.RUC = '".pg_escape_string($nombre)."'";
	}

	//$sql2 = "SELECT COLUMN_NAME FROM information_schema.columns WHERE table_name = 'Aux_Energigas_Clientes'";
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
		$datos[$i]['xtra2'] = trim($row[3]);
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
		$clie = obtieneClientes($_POST['lista']);
		$des1 = $clie[0]['des'];
		$xtra1 = $clie[0]['xtra'];
		$xtra2 = $clie[0]['xtra2'];
	?>
		if(opener.document.<?php echo $_POST['cod']; ?>.value == ''){
			alert("<?php echo 'Debe de tener un codigo anexo seleccionar otro' ; ?> ");
		}
		
		if(opener.document.form_agregar.ruc.value == ''){
			alert("<?php echo 'Debes de seleccionar un cliente primero' ; ?> ");
			opener.document.<?php echo $_POST['cod']; ?>.value = '';
			opener.document.form_agregar.diranexo.value = '';
			opener.document.form_agregar.precio.value = '';
			window.close();
		}else{

			opener.document.form_agregar.diranexo.value = '<?php echo $des1; ?>';
			opener.document.form_agregar.precio.value = '<?php echo $xtra1; ?>';
			if(opener.document.form_agregar.precio.value == '0' || opener.document.form_agregar.precio.value <= 0){
				opener.document.form_agregar.producto.value = 'GLP-KLS';
				opener.document.form_agregar.nomproducto.value = 'GLP-KLS';
				opener.document.form_agregar.precio.value = '<?php echo $xtra2; ?>';
			}else if (opener.document.form_agregar.precio.value > 0){
				opener.document.form_agregar.producto.value = 'GLP-GLN';
				opener.document.form_agregar.nomproducto.value = 'GLP-GLN';
			}
			window.close();
		}
	//-->
	</script>
	<?php

} 

switch ($_GET['consulta']) {
    case "trabajadores":
	$rs = obtieneTrabajadores($_SESSION['almacen'], 0, $_POST['busca']);
	break;
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
	form.submit();
	//window.close();
}
</script>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="/sistemaweb/sistemaweb.css" type="text/css">
</head>
<body>
<form name="form1" method="post" action="">Busqueda 
  	<input type="text" name="busca">
  	<input type="submit" name="Buscar" value="Buscar">
  	<input type="hidden" name="cod" value="<?php echo $_GET['cod'];?>">
  	<input type="hidden" name="des" value="<?php echo $_GET['des'];?>">
  	<input type="hidden" name="xtra" value="<?php echo $_GET['xtra'];?>">
  	<input type="hidden" name="consulta" value="<?php echo $_GET['consulta'];?>">
  	<br>
  	<select name="lista" size="21"> 
  	<?php 
 	$datos = obtieneClientes($_GET['ruc']);
  	for($i=0;$i<count($datos);$i++){
  		$ccod = $datos[$i]['cod'];
  		$ddes = $datos[$i]['des'];
  		$xxtra = $datos[$i]['xtra'];
  		$xxtra2 = $datos[$i]['xtra2'];
  		print "<option value='$ccod'>$ccod -- $ddes</option>\n";
  	} ?>
  	</select> 	
  	<br>
  	<input type="button" name="Seleccionar" value="Seleccionar" onClick="javascript:pasarValorOpener(lista,form1,'<?php echo $cod;?>','<?php echo $des;?>','<?php echo $xtra;?>','<?php echo $xtra2;?>')">
</form>
</body>
</html>
