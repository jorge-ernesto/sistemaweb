<?php
include("../valida_sess.php");
// incluye config.php y inc_top_xxx
// protege para que no accedan a otro menu por usuario
// pero es necesario que haga coneccion de nuevo
include("../menu_princ.php");
include("../functions.php");

$coneccion=pg_connect("host=".$v_host." port=5432 dbname=".$v_db." user=postgres");

$v_grifo=true;

if ( is_null($almacen) or trim($almacen)=="")
        {
        $almacen="001";
        }

if ( trim($almacen)=="001")
	{
	$v_grifo=false;
	}

if($txtcampo=="A"){ $ch=" checked"; } elseif($txtcampo=="B"){ $ch1=" checked"; } elseif($txtcampo=="C"){ $ch2=" checked"; } elseif($txtcampo=="D"){ $ch3=" checked"; } else { $ch=" checked"; }

$txtxbusqueda=strtoupper($txtxbusqueda);

if($boton=="buscar" or strlen(trim($txtxbusqueda))>0) {
	if($txtcampo=="A") {
		$txtxbusqueda=completarCeros($txtxbusqueda,13,"0");
		$addsql=" where int_articulos.art_codigo='".$txtxbusqueda."' ";
	} elseif($txtcampo=="B") {
		$addsql=" where int_articulos.art_descripcion like '".$txtxbusqueda."%' ";
	} elseif($txtcampo=="C") {
		$addsql=" where int_articulos.art_descripcion like '%".$txtxbusqueda."%' ";
	} elseif($txtcampo=="D") {
		$addsql=" where int_articulos.art_cod_sku like '%".$txtxbusqueda."%' ";
	} else {
		$addsql=" ";
	}
	//$bddsql=" limit $tamPag offset $limitInf ";
} else{   //$bddsql=" limit $tamPag offset $limitInf ";
	$fbuscar="A";
}

$sql="select art_codigo,art_descripcion,art_costoactual,art_stockactual,art_linea
 from int_articulos ".$addsql." order by 1 ";
$xsql=pg_exec($coneccion,$sql);
$ilimit=pg_numrows($xsql);
if($ilimit>0) {
        $numeroRegistros=$ilimit;
}



if($boton=="Agregar")
	{
	?>
	<script>
	location.href='maes_articulo_1.php';
	</script>
	<?php
	}

if($boton=="Eliminar")
        {
        $xsqlbuscamov=pg_exec($coneccion,"select art_codigo from inv_movialma where art_codigo='".$id."' ");
        if(pg_numrows($xsqlbuscamov)==0)
                {
                $xsqldelprecart=pg_exec($coneccion,"delete from fac_lista_precios where art_codigo='".$id."' ");
                $sql1="delete from int_articulos where art_codigo='".$id."'";
                $xsql1=pg_exec($coneccion,$sql1);
                }
        else
                {
                ?>
                <script>
                alert(" Existen movimientos con este codigo de artículo -  <?php echo $id; ?> !!! ")
                </script>
                <?php
                }
        }

if($boton=="Modificar Breve")
	{
	if(strlen($id)>0)
		{
		echo('<script languaje="JavaScript">');
		echo("	location.href='maes_articulo_modif_breve.php?idart=".$id."&v_grifo=".$v_grifo."' " );
		echo('</script>');
		}
	else
		{
		echo('<script languaje="JavaScript"> ');
		echo('alert(" Debe seleccionar una Articulo !!! ") ');
		echo('</script>');
		}
	}


if($boton=="Modificar")
{
if(strlen($id)>0)
	{
	echo('<script languaje="JavaScript">');
	echo("	location.href='maes_articulo_2.php?idart=".$id."&fupd=Z'; " );
	echo('</script>');
	}
else
	{
	echo('<script languaje="JavaScript"> ');
	echo('alert(" Debe seleccionar una Articulo !!! ") ');
	echo('</script>');
	}
}


?>
<html><link rel="stylesheet" href="<?php echo $v_path_url; ?>sistemaweb.css" type="text/css">
<head>
<title>MAESTRO DE ARTICULOS</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
</head>
<body>
<form action="" method="GET">
MAESTRO DE ARTICULOS
<table>
	<tr>
		<td>Busqueda r&aacute;pida: </td>
		<td><input size='25' name="txtxbusqueda" type="text" value="<?php echo $txtxbusqueda; ?>"></td>
		<td><input name="boton" type="submit" value="buscar"></td>
	</tr>
	<tr>
		<td colspan="3">
		<input name="txtcampo" type="radio" value="A" <?php echo $ch;?> > Código
		<input name="txtcampo" type="radio" value="B" <?php echo $ch1;?> > Descripcion(iniciales)
		<input name="txtcampo" type="radio" value="C" checked <?php echo $ch2;?> > Descripci&oacute;n(contenido)
		<input name="txtcampo" type="radio" value="D" <?php echo $ch3;?> > Codigo Sku
		</td>
		<td><a href="maes_imprimir_etiquetas.php" target="_blank"> - Etiquetas x Linea</a></td>
	</tr>
</table>

<?php
include("/sistemaweb/maestros/pagina.php");

$bddsql=" limit $tamPag offset $limitInf ";
//$bddsql=" limit 20 offset $limitInf ";
?>

<input type="hidden" name="varx" value="<?php echo $varx;?>">
  <table border='1' cellpadding='0' cellspacing='0'>
    <tr>
      <th>&nbsp;</th>
      <th>CODIGO</th>
      <th>DESCRIPCION</th>
      <th>PRECIO</th>
      <th>STOCK</th>
      <th>LINEA</th>
      <th>UBIC</th>
      <th>SKU</th>
    </tr>
        <tr>
                <td>&nbsp;</td>
                <td align='center'><input name='boton' type='submit' value='Agregar'></td>
                <td align='center'><input name='boton' type='submit' value='Modificar Breve'></td>
                <td colspan=2 align='center'><input name='boton' type='submit' value='Modificar'></td>
                <td align='center'><input name='boton' type='submit' value='Eliminar'></td>
        </tr>

<?php
//  $_POST['varx']=20;
/*$sql="select tab_tabla,tab_elemento,tab_descripcion,tab_desc_breve,tab_num_01
 from int_tabla_general where tab_tabla='".$_POST['varx']."' ";*/

$mes=date("m");
$ano=date("Y");

$sql="
select int_articulos.art_codigo,int_articulos.art_descripcion,
int_articulos.art_costoactual,
inv_saldoalma.stk_stock".$mes.",
int_articulos.art_linea,
int_articulos.art_cod_ubicac, 
int_articulos.art_cod_sku 
from int_articulos
left join inv_saldoalma 
on int_articulos.art_codigo=inv_saldoalma.art_codigo and 
	inv_saldoalma.stk_almacen='".$almacen."' and
	inv_saldoalma.stk_periodo='".$ano."' 
".$addsql." order by 1  ".$bddsql." ";


// echo $sql;
$xsql=pg_exec($coneccion,$sql);
$ilimit=pg_numrows($xsql);

while($irow<$ilimit) {
        $cod=pg_result($xsql,$irow,0);
        $desc=pg_result($xsql,$irow,1);

        $sqlprecio="select util_fn_precio_articulo(trim('".$cod."'))";
        $xsqlprecio=pg_exec( $coneccion, $sqlprecio );

        $precio=pg_result($xsqlprecio,0,0);

        $stock=pg_result($xsql,$irow,3);
        $linea=pg_result($xsql,$irow,4);
	$ubica=pg_result($xsql,$irow,5);
        $codsku=pg_result($xsql,$irow,6);
	?>
        <tr bgcolor="#CCCC99" onMouseOver="this.style.backgroundColor='#FFFFCC';this.style.cursor='hand';" onMouseOut="this.style.backgroundColor='#CCCC99'"o"];">
        <?php
        //  echo "<td><input type='checkbox' name='id_".$cod."' value='".$cod."'></td>";
        echo "<td><input type='radio' name='id' value='".$cod."'></td>";
        echo "<td>&nbsp;".$cod."</td>";
        echo "<td>&nbsp;".$desc."</td>";
        echo "<td align='right'>&nbsp;".$precio."</td>";
        echo "<td align='right'>&nbsp;".$stock."</td>";

        $nrocaract=6; $cadena=$linea;
        completaceros($nrocaract,$cadena);
        $linea=$cadena;
        $sqlopt="select tab_tabla,tab_elemento,tab_descripcion,tab_desc_breve,tab_num_01
                                from int_tabla_general where tab_tabla='20' and tab_elemento='".$linea."' ";
        $xsqlopt=pg_exec($coneccion,$sqlopt);
        $ilimitopt=pg_numrows($xsqlopt);
        if($ilimitopt>0)
                {
                $x0=pg_result($xsqlopt,0,1);
                $x1=pg_result($xsqlopt,0,2);
                }
        echo "<td>&nbsp;".$x0." - ".$x1."</td>";
	echo "<td align='center'>&nbsp; ".$ubica."</td>";
	echo "<td align='center'>&nbsp; ".$codsku."</td>";
	echo "</tr>";
        $irow++;
        }
        ?>
        <tr>
                <td>&nbsp;</td>
                <td align='center'><input name='boton' type='submit' value='Agregar'></td>
                <td align='center'><input name='boton' type='submit' value='Modificar Breve'></td>
                <td colspan=2 align='center'><input name='boton' type='submit' value='Modificar'></td>
                <td align='center'><input name='boton' type='submit' value='Eliminar'></td>
        </tr>
  </table>
</form>
</body>
</html>
<?php
pg_close($coneccion);
