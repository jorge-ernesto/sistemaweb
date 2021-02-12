<?php
include("../valida_sess.php");
include("../menu_princ.php");
include("../functions.php");
require("../clases/funciones.php");
$funcion = new class_funciones;
// crea la clase para controlar errores
$clase_error = new OpensoftError;
// conectar con la base de datos
$coneccion=$funcion->conectar("","","","","");

// $coneccion=pg_connect("host=".$v_host." port=5432 dbname=".$v_db." user=postgres");

// aqui guarda el tipo de modificacion breve si es grifo o oficina
$v_grifo=$v_grifo;

if($boton=="Regresar")
	{
	?>
	<script>
	location.href='maes_articulo.php';
	</script>
	<?php
	}

if($boton=="Grabar")
	{
	$okgraba=true;
	$addsql=" ";
         if ($v_grifo)
	         {
	         if (strlen(trim($v_codigosku)) > 0)
	                 {
	                 $sqlsku="select tab_elemento, tab_descripcion from int_tabla_general where tab_tabla='CSKU' and tab_elemento='".$v_codigosku."' ";
	                 $xsqlsku=pg_exec($coneccion,$sqlsku);
	                 if(pg_numrows($xsqlsku)==0)
	                         {
	                         echo('<script languaje="JavaScript"> ');
	                         echo('alert(" No Existe Codigo SKU !!! "); ');
	                         echo('</script>');
	                         $okgraba=true;
	                         $addsql=$addsql.",art_cod_sku=' '";
	                         }
	                 else
	                         {
	                         $addsql=$addsql.",art_cod_sku='".$v_codigosku."'";
	                         }
	                 }
	         else
	                 {
	                 $okgraba=true;
	                 $addsql=$addsql.",art_cod_sku=' '";
	                 }

	         if (strlen($v_ubicacion) > 0)
	                 {
	                 $sqlubi="select desc_ubicac from inv_ta_ubicacion where cod_almacen='".$almacen."' and trim(cod_ubicac)=trim('".$v_ubicacion."') ";
	                 //echo $sqlubi;
	                 $xsqlubi=pg_exec($coneccion,$sqlubi);
	                 if(pg_numrows($xsqlubi)==0)
	                         {
	                         echo('<script languaje="JavaScript"> ');
	                         echo('alert(" No Existe Ubicacion !!! "); ');
	                         echo('</script>');
	                         $okgraba=FALSE;
	                         }
	                 }

	         if ($okgraba)
	                 {
	                 $sqli="update int_articulos set art_cod_ubicac='".$v_ubicacion."' ".$addsql." where art_codigo='".$v_codigo_articulo."' ";
	                 //echo $sqli;
	                 $xsqli=pg_exec($coneccion,$sqli);

	                 }
		}
	else
		{
		if (strlen($v_precio)==0) {$v_precio=0; }

		if ($okgraba)
			{
			$lista=pg_result(pg_exec($coneccion,"select util_fn_cd_precio()"),0,0);
			$moneda='01';
			// echo $lista;
			$sql="select pre_precio_act1 from fac_lista_precios where art_codigo='".$v_codigo_articulo."' and pre_lista_precio='".$lista."'";
			if (pg_numrows(pg_query($coneccion,$sql))>0) 
				{
				$sql="update fac_lista_precios set pre_precio_act1=".$v_precio." where art_codigo='".$v_codigo_articulo."' and pre_lista_precio='".$lista."'";
				}
			else
				{
				$sql="insert into fac_lista_precios(pre_lista_precio,art_codigo,pre_moneda,pre_precio_fec1,
													pre_usuario,pre_precio_act1,pre_estado,pre_fecactualiz,
													pre_transmision) 
						values('".$lista."','".$v_codigo_articulo."','".$moneda."','now',
													'".$user."',".$v_precio.",'1','now',
													'1')";
				}
				
			
			echo $sql;
			$xsqlprecio=pg_exec($coneccion,$sql);
			}

		}
	}
?>

<html>
<head>
    <title>Formulario prefijos</title>
<script language="JavaScript" src="/sistemaweb/maestros/js/jaime.js"></script>
<script language="JavaScript" src="/sistemaweb/clases/reloj.js"></script>
<script language="JavaScript" src="/sistemaweb/compras/valfecha.js"></script>
<script language="JavaScript" src="/sistemaweb/clases/calendario.js"></script>
<script language="JavaScript" src="/sistemaweb/clases/overlib_mini.js"></script>
<script language="JavaScript" src="/sistemaweb/compras/validacion.js"></script>


<script language="javascript">
var miPopup

function enviadatos(){
	document.formular.submit()
}
</script>
</head>
<body>
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<p>MODIFICACION BREVE ARTICULO</p>
<form name='formular'  method="post">
<!--<input type="hidden" name='fupd' value="">
// no se necesita el fupd porque primero hago la seleccion de botones y luego la carga de datos
// porque si lo pongo antes de los botones la modificacion se refresca con los datos antiguos y no graba los cambios del
// formulario
-->


<?php
$sqlart="select art_descripcion, art_descbreve, art_cod_sku, art_cod_ubicac from int_articulos where art_codigo='".$idart."' ";

$xsqlart=pg_exec($coneccion,$sqlart);
$ilimitart=pg_numrows($xsqlart);
if($ilimitart>0)
	{
	$v_codigo_articulo=$idart;
	$v_desc_articulo=pg_result($xsqlart,0,0);
	$v_descbreve_articulo=pg_result($xsqlart,0,1);
	$v_codigosku=pg_result($xsqlart,0,2);
	$v_ubicacion=pg_result($xsqlart,0,3);
	$v_precio=pg_result(pg_exec($coneccion,"select util_fn_precio_articulo('".$v_codigo_articulo."')"),0,0);

	}


?>

<table border="1">
<tr>
<td>
	<table border="1" cellspacing="0" cellpadding="0">
		<tr>
	                 <td>Codigo de Item</td>
	                 <td>:</td>
	                 <td><input name="v_codigo_articulo" type="text" value="<?php echo $v_codigo_articulo;?>" maxlength="13" readonly></td>
		</tr>
	        <tr>
	        <td>Descripcion</td>
	        <td>:</td>
	        <td><input name="v_desc_articulo" type="text" value="<?php echo $v_desc_articulo; ?>" size="45" maxlength="55" readonly></td>
	        </tr>
         	<tr>
		<td>Descripcion Breve</td>
		<td>:</td>
		<td><input name="v_descbreve_articulo" type="text" value="<?php echo $v_descbreve_articulo;?>" size="40" maxlength="20" readonly></td>
		</tr>
                 <?php if ($v_grifo)
                 	{
                 ?>
		<tr>
			<td>Ubicacion</td>
			<td>:</td>
			<?php
			if(strlen($v_ubicacion)>0)
				{
				$sqlao="select cod_ubicac,desc_ubicac from inv_ta_ubicacion where cod_ubicac like '%".trim($v_ubicacion)."%' and cod_almacen='".$almacen."' ";
				// echo $sqlao;
				$xsqlao=pg_exec($coneccion,$sqlao);
				$ilimitao=pg_numrows($xsqlao);
				if($ilimitao>0)
					{
					$txtalma=pg_result($xsqlao,0,0);
					$v_desc_ubicacion=pg_result($xsqlao,0,1);
					}
				}
			?>
			<td><input name="v_ubicacion" type="text" value="<?php echo $v_ubicacion; ?>" size="10" maxlength="6" onblur="javascript:mostrarProcesar('/sistemaweb/maestros/ayuda/procesando.php',this.value,'formular.v_desc_ubicacion','ubicaciones<?php echo $almacen;?>')" >
			<img src="../images/help.gif" width="16" height="15"  onClick="javascript:mostrarAyuda('/sistemaweb/maestros/ayuda/lista_ayuda.php','formular.v_ubicacion','formular.v_desc_ubicacion','ubicaciones<?php echo $almacen;?>')">
			<input type="text" name="v_desc_ubicacion" tabindex=0 size="46" readonly="true" value='<?php echo $v_desc_ubicacion; ?>' >
			</td>
		</tr>

		<tr>
			<td>Codigo SKU</td>
			<td>:</td>
			<?php
			if(strlen(trim($v_codigosku))>0)
				{
				$sqlsku="select tab_elemento, tab_descripcion from int_tabla_general where tab_tabla='CSKU' and tab_elemento='".$v_codigosku."' ";
				$xsqlsku=pg_exec($coneccion,$sqlsku);
				$ilimitsku=pg_numrows($xsqlsku);
				if($ilimitsku>0)
					{
					$v_codigosku=pg_result($xsqlsku,0,0);
					$v_desc_sku=pg_result($xsqlsku,0,1);
			  		}
				else
					{
					echo('<script languaje="JavaScript"> ');
					echo('alert(" No Existe Codigo SKU !!! "); ');
					echo('</script>');
					}

				}
			?>
			<td><input name="v_codigosku" type="text" value="<?php echo $v_codigosku; ?>" size="10" maxlength="6" onblur="javascript:mostrarProcesar('/sistemaweb/maestros/ayuda/procesando.php',this.value,'formular.v_desc_sku','SKU')" >
			<img src="../images/help.gif" width="16" height="15"  onClick="javascript:mostrarAyuda('/sistemaweb/maestros/ayuda/lista_ayuda.php','formular.v_codigosku','formular.v_desc_sku','SKU')">
			<input type="text" name="v_desc_sku" tabindex=0 size="46" readonly="true" value='<?php echo $v_desc_sku; ?>' >
			</td>
		</tr>
		<?php
			}
		else
			{
			?>
			<tr>
			<td height="24">Precio1</td>
			<td>:</td>
			<td><input name="v_precio" type="text" value="<?php echo $v_precio;?>" onKeyPress="return esInteger(event)" style="text-align:right"></td>
			</tr>
			<?php
			}
		?>

	</table>
	</td>

	<td align="center">
	<input type="submit" name="boton" value="Grabar">
	<p>
	<input type="submit" name="boton" value="Regresar">
	</p>
</td>
</tr>
</table>
</form>
</body>
</html>
<?php pg_close($coneccion);?>
