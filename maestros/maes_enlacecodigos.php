<?php
//session_start();

//include("../config.php");
include("../menu_princ.php");
include("../functions.php");
include("functions.php");

//include("../valida_sess.php");

// incluye config.php y inc_top_xxx
// protege para que no accedan a otro menu por usuario
// pero es necesario que haga coneccion de nuevo
//include("../menu_princ.php");
//include("../functions.php");

//$usuario = $;


$newcodigo			= $_POST['newcodigo'];
$newdescripcion_cod	= $_POST['newdescripcion_cod'];
$newcomponente		= $_POST['newcomponente'];
$newdescripcion_com	= $_POST['newdescripcion_com'];
$newcantidad		= $_POST['newcantidad'];
$filter				= $_POST['filter'];
$boton				= $_POST['boton'];
//$usuario 			= $_SESSION['auth_usuario'];
$ch_login = trim($usuario->obtenerUsuario());//Solo obtenen el nombre de usuario para registro, no altera el objeto $usuario
$ch_login = substr($ch_login, 0, 10);

$count = pg_query($coneccion, "SELECT COUNT(*) FROM int_ta_enlace_items;");
$cant_reg = pg_result($count,0,0);
$cant_pag = round(($cant_reg/10),0);

$pagina				= $_GET['pagina'];

if($pagina==0) {
	$pagina=1;
	$inicio=0;
	$limite=10;
} else {
	$inicio=($pagina-1)*10;
	$limite=$limite+10;
}

//echo $inicio.$limite.$cant_pag ;

$sql = "select e.art_codigo, e.ch_item_estandar, e.nu_cantidad_descarga, e.dt_fechactualizacion, 
     a.art_descripcion, a.art_plutipo
	 from int_ta_enlace_items e, int_articulos a
	 where
	 e.art_codigo = a.art_codigo and
	 ( e.art_codigo like '%".$filter."%' or e.ch_item_estandar like '%".$filter."%' or 
	   a.art_descripcion like '%".$filter."%' )
	 ORDER BY 1
	 LIMIT ".$limite."OFFSET ".$inicio;

//echo $sql;

switch ($boton) {
   case "Agregar":

	if(strlen($newcodigo) == 13 && strlen($newcomponente) == 13 && $newcantidad>0) {
		$xsqlbusc = pg_exec($coneccion,"select art_codigo, art_descripcion from int_articulos where art_codigo='".$newcodigo."' and art_plutipo='2'");
		$numFilas = pg_num_rows($xsqlbusc);

		if($numFilas>0) {
			$xsqlbusc = pg_exec($coneccion,"select art_descripcion from int_articulos where art_codigo='".$newcomponente."'");
			$numFilas = pg_num_rows($xsqlbusc);
					
				if($numFilas>0) {
					//	$query = "select art_codigo, art_descripcion from int_articulos where art_codigo='".$newcodigo."' and art_plutipo='2'";
					//	$xquery = pg_query($coneccion, $query)

				$sqlins = "insert into int_ta_enlace_items(art_codigo, ch_item_estandar, nu_cantidad_descarga, dt_fechactualizacion, ch_usuario, ch_auditorpc)
					values('".$newcodigo."','".$newcomponente."','".$newcantidad."', now(),'".$ch_login."','".$_SERVER['REMOTE_ADDR']."')";

					//echo "esto es el insert:".$sqlins;
					
						@$xsqlins = pg_query($coneccion,$sqlins);
						if(!$xsqlins) {
							?>
							<script> alert("Codigo y Componente ya \n estan registrados!!!") </script>
							<?php
						}
					} else {
						?>
						<script> alert("Codigo COMPONENTE no es Valido !!! ") </script>
						<?php
					}
				} else {
					?>
					<script> alert("Codigo NO Plu Saliente !!!") </script>
					<?php
				}
		} else {
					?>
					<script> alert(" Debe ingresar un Codigo válido") </script>
					<?php
		}
      break;

   	case "Modificar":

	foreach($_REQUEST['idp'] as $am0 => $v) {
		$sqlupd = " update int_ta_enlace_items SET
					nu_cantidad_descarga='".$_POST['cantidad'][$am0]."',
					dt_fechactualizacion = now(),
					ch_usuario = '".$ch_login."',
					ch_auditorpc = '".$_SERVER['REMOTE_ADDR']."'
					where art_codigo='".$_POST['codigo'][$am0]."' and ch_item_estandar='".$_POST['componente'][$am0]."'";
		$xsqlupd=pg_exec($coneccion,$sqlupd);
		}
    break;

    case "Eliminar":

		foreach($_REQUEST['idp'] as $am0 => $v) {
		$sqlupd="delete from int_ta_enlace_items where art_codigo='".$_POST['codigo'][$am0]."' and ch_item_estandar='".$_POST['componente'][$am0]."'";
		$xsqlupd=pg_exec($coneccion,$sqlupd);
	}

	break;
// default:
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Sistemaweb - OPENSOFT</title>
<script src="/sistemaweb/js/jquery-1.9.1.js" type="text/javascript"></script>
    <link rel="stylesheet" href="/sistemaweb/css/jquery-ui.css" />
    <link rel="stylesheet" href="/sistemaweb/helper/css/style.css" />
    <script src="/sistemaweb/js/jquery-ui.js"></script>
	<script type="text/javascript" src="/sistemaweb/helper/js/autocomplete.js" ></script>
	<script language="JavaScript" src="js/miguel.js"></script>

</head>

<body>
<h2 style="color:#336699;" align="center">ENLACE DE C&Oacute;DIGOS</tr>
<!--CONFIGURACI&Oacute;N <BR> ENLACE DE CODIG&Oacute;S <p>-->

<br><br>
<div align="center"> 
<form name="fitros" action="" method="post">
<table>
<tr>
	<th style="color:#336699;">Busqueda R&aacute;pida:</th>
	<th><input type='text' name='filter'></th>
	<th><button name="boton" type='submit' value='Buscar'>Buscar</button></th>
</tr>
</table>
</form>
</div>

	<?php
			$a=0;
			while($cant_pag>=$a)
			{
				//echo "<a href='".$_SERVER["PHP_SELF"]."?pagina=".($a+1)."'> ".($a+1)."</a>";
				if(($pagina-1)!=$a)
				{
					echo "<a href='".$_SERVER["PHP_SELF"]."?pagina=".($a+1)."'>[".($a+1)."]</a>";
					//'$fechad 00:00:00' and '".$fechaa." 23:59:59' ";
				} else {
					echo "&nbsp;<font color=#000000 size='2'>".($a+1)."</font>&nbsp;";
				}
				$a++;
			}
		?>

<hr>

<?php 
//echo "P&aacute;gina :[".$pagina."]";?>
<form name="enlace" action="" method="post">

<table border="0" cellpadding="0">
	<tr>
		<th class="grid_cabecera" width="20">&nbsp;</th>
		<th class="grid_cabecera" width="120">C&Oacute;DIGO</th>
		<th class="grid_cabecera" width="100">DESCRIPCI&Oacute;N</th>
		<th class="grid_cabecera" width="130">COMPONENTE</th>
		<th class="grid_cabecera" width="260">DESCRIPCI&Oacute;N</th>
		<th class="grid_cabecera" width="80">CANTIDAD</th>
		<th class="grid_cabecera" width="100">&nbsp;</th>
	</tr>
<tr>
	<td>&nbsp;</td>
	<td align="center"><input type='text' id="txt-Nu_Id_Producto_Saliente" name='newcodigo' size='35' maxlength='13' ></td>
	<td align="center"><input type='text' id="txt-No_Producto_Saliente" placeholder="Ingresar Codigo o Nombre" name='newdescripcion_cod' size='35' maxlength='35' class="mayuscula" autocomplete="off"  ></td>
	<td align="center"><input type='text' id="txt-Nu_Id_Producto" name='newcomponente' size='35' maxlength='13' ></td>
	<td align="center"><input type='text' id="txt-No_Producto" placeholder="Ingresar Codigo o Nombre" name='newdescripcion_com' size='50' maxlength='33' class="mayuscula" autocomplete="off"  ></td>
	<td align="center"><input type='text' name='newcantidad' size='13' maxlength='7'></td>
	<td><button name="boton" type="submit" value="Agregar">Agregar</button></td>
<tr>
	<th class="grid_cabecera" width="20">&nbsp;</th>
	<th class="grid_cabecera" width="120">C&Oacute;DIGO</th>
	<th class="grid_cabecera" width="100">DESCRIPCI&Oacute;N</th>
	<th class="grid_cabecera" width="130">COMPONENTE</th>
	<th class="grid_cabecera" width="260">DESCRIPCI&Oacute;N</th>
	<th class="grid_cabecera" width="80">CANTIDAD</th>
	<th class="grid_cabecera" width="100">FECHA</th>
</tr>

<?php

$xsql=pg_exec($coneccion,$sql);
$irow=0;
$ilimit=pg_numrows($xsql);
while($irow<$ilimit) {
	$a0=$irow ;
	//$a0=pg_result($xsql,$irow,0)."   ".pg_result($xsql,$irow,5)  ;

	// $sql_desc = "select art_descripcion from int_articulos where art_codigo='".$a0."'";
	// $xsql_desc = pg_query($coneccion,$sql_desc);
	// $descripcion_cod[$a0]=pg_result($xsql_desc,0,0);
	
	$codigo[$a0] = pg_result($xsql,$irow,0);
	$descripcion_cod[$a0]=pg_result($xsql,$irow,4);

	$c0=trim(pg_result($xsql,$irow,1));
	$componente[$a0]=trim(pg_result($xsql,$irow,1));

	$sql_desc = "select art_descripcion from int_articulos where art_codigo='".$componente[$a0]."'";
	$xsql_desc = pg_query($coneccion,$sql_desc);
	$descripcion_com[$a0]=pg_result($xsql_desc,0,0);

	$cantidad[$a0] = pg_result($xsql,$irow,2);
	$fecha[$a0] = pg_result($xsql, $irow, 3);
//echo "<tr bgcolor='CCCC99' onMouseOver=this.style.backgroundColor=#FFFFCC'; this.style.cursor='hand'; onMouseOut='this.style.backgroundColor="CCCC99"')
?>
	<tr onMouseOver="this.style.backgroundColor='#FFFFCC';this.style.cursor='hand';" onMouseOut="this.style.backgroundColor=''"o"];">
<?php
	//echo "<tr>
	echo "<td><input type='checkbox' name='idp[$a0]' value='".$a0."' ></td>";
	echo "<td><input readonly type='text' size='35' name='codigo[$a0]' value='".$codigo[$a0]."' maxlength='3'></td>";
	echo "<td><input readonly type='text' size='35' name='descripcion_cod[$a0]' value='".$descripcion_cod[$a0]."' maxlength='3'></td>";
	echo "<td align='center'><input type='text' size='35'  name='componente[$a0]' value='".$componente[$a0]."' maxlength='13'></td>";
	echo "<td align='right'><input type='text' size='50'  name='descripcion_com[$a0]' value='".$descripcion_com[$a0]."' maxlength='30'></td>";
	echo "<td align='center'><input type='text' size='13'  name='cantidad[$a0]' value='".$cantidad[$a0]."' maxlength='7'  style='color:black ' ></td>";
	//echo "<td align='right'><input type='text' size='3' style='text-align:right' name='clase[$a0]' value='".$clase[$a0]."' maxlength='1'></td>";
	echo "<td width='65' align='center'>".$fecha[$a0]."</td>";
	$irow++;
	;
}
?>
<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
<tr>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td><button name="boton" type="submit" value="Modificar" ><img src="/sistemaweb/icons/update2.png" align="right" />Modificar</button></td>
	<td><button name="boton" type="submit" value="Eliminar" ><img src="/sistemaweb/icons/delete.gif" align="right" />Eliminar</button></td>
</tr>
</table>
</form>
</body>
</html>
<?php
	include("../close_connect.php");
