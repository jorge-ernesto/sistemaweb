<?php
include("../valida_sess.php");
include("../functions.php");
require("../clases/funciones.php");
$funcion = new class_funciones;
// crea la clase para controlar errores
$clase_error = new OpensoftError;
$clase_error->_error();
// conectar con la base de datos
$conector_id=$funcion->conectar("","","","","");

switch($boton)
{
	case Agregar:
		if($oficina=="no"){
			header("Location: facturas_agregar.php");
		}else{
			header("Location: facturas-oficina_agregar.php");
		}
		break;
	case Modificar:
		if($m_clave!="")
		{
			header("Location: facturas_agregar_modif.php?m_clave=$m_clave");
		}
		break;
	case Anular:
		if($m_clave!="")
		{
/*			$sqldel="DELETE FROM FAC_TA_FACTURA_CABECERA
					WHERE
					trim(CH_FAC_TIPODOCUMENTO)||trim(CH_FAC_SERIEDOCUMENTO)||trim(CH_FAC_NUMERODOCUMENTO)||trim(CLI_CODIGO) = '$m_clave'";
	*/
			$sqlupd =
			"UPDATE FAC_TA_FACTURA_CABECERA SET CH_FAC_ANULADO='1'
				WHERE
			trim(CH_FAC_TIPODOCUMENTO)||trim(CH_FAC_SERIEDOCUMENTO)||trim(CH_FAC_NUMERODOCUMENTO)||trim(CLI_CODIGO)='$m_clave'";

			pg_exec($conector_id, $sqlupd);
		}
		break;
}


if(strlen($diaa)==0)
{
	rangodefechas();
	$diad=$zdiaa; $mesd=$zmesa; $anod=$zanoa;
	$diaa=$zdiaa; $mesa=$zmesa; $anoa=$zanoa;
}
$fechad=$anod."-".$mesd."-".$diad;
$fechaa=$anoa."-".$mesa."-".$diaa;






/*$count = pg_query($conector_id, "select count(*) from FAC_TA_FACTURA_CABECERA where CH_ALMACEN='$almacen'");
$cant_reg = pg_result($count,0,0);
$cant_pag = round(($cant_reg/20),0);
*/
//$count = pg_query($conector_id, $sql);



/*if($boton=="Consultar")
{*/
$sqladd="and DT_FAC_FECHA between '$fechad 00:00:00' and '".$fechaa." 23:59:59' ";
//echo $sqladd." *-*--- ".$pagina;
//}


$count = pg_query($conector_id, "select count(*) from FAC_TA_FACTURA_CABECERA where ch_almacen='$almacen' $sqladd");
$cant_reg = pg_result($count,0,0);
//echo "<br>REG".$cant_reg."<br>";
$cant_pag = round(($cant_reg/20),0);


if($pagina==0) {
	$pagina=1;
	$inicio=0;
	$limite=20;
}
else {
	$inicio=($pagina-1)*20;
	$limite=$limite+20;
}



$sql = "SELECT
			trim(CH_FAC_TIPODOCUMENTO)||trim(CH_FAC_SERIEDOCUMENTO)||trim(CH_FAC_NUMERODOCUMENTO)||trim(CLI_CODIGO),
			CH_FAC_TIPODOCUMENTO, CH_FAC_SERIEDOCUMENTO, CH_FAC_NUMERODOCUMENTO,
			CLI_CODIGO, DT_FAC_FECHA, CH_PUNTO_VENTA,
			CH_ALMACEN, CH_FAC_MONEDA, NU_TIPOCAMBIO,
			NU_FAC_VALORBRUTO,

			NU_FAC_VALORTOTAL, CH_FAC_CREDITO, CH_FAC_FORMA_PAGO,
			CH_FAC_ANULADO, CH_FAC_IMPRESO, CH_FAC_ANTICIPO,
			CH_FAC_CAB_IDENTIDAD
			, NU_FAC_IMPUESTO1

		FROM FAC_TA_FACTURA_CABECERA

		WHERE CH_ALMACEN='$almacen' ".$sqladd."
		ORDER BY DT_FAC_FECHA
		LIMIT ".$limite." OFFSET ".$inicio;

/*$count = pg_query($conector_id, "select count(*) from FAC_TA_FACTURA_CABECERA where CH_ALMACEN='$almacen'");
$cant_reg = pg_result($count,0,0);
$cant_pag = round(($cant_reg/20),0);
*/
/*$count = pg_query($conector_id, $sql);

$cant_reg = pg_num_rows($count);

$cant_pag = round(($cant_reg/20),0);

echo "<br>EL COUNT:$cant_reg <br>";

if($pagina==0) {
	$pagina=1;
	$inicio=0;
	$limite=20;
}
else {
	$inicio=($pagina-1)*20;
	$limite=$limite+20;
}
*/
?>

<?php include("inc_top.php"); ?>
<?php echo otorgarAlmacen($conector_id, $almacen);?><br>
FACTURAS&nbsp;:
<form name="formular" method="post" action="" >

<table border="1" cellspacing="3">
    <tr>
      <th colspan="5">CONSULTAR POR RANGO DE FECHAS</th>
    </tr>
    <tr>
      <th>DESDE :</th>
      <th><input type="text" name="diad" size="4" maxlength="2" value='<?php echo $diad;?>' onfocus="formular.diad.select()">
        /
        <input type="text" name="mesd" size="4" maxlength="2" value='<?php echo $mesd;?>' onfocus="formular.mesd.select()">
        /
        <input type="text" name="anod" size="6" maxlength="4"  value='<?php echo $anod;?>' onfocus="formular.anod.select()"></th>
      <th>HASTA:</th>
      <th><input type="text" name="diaa" size="4" maxlength="2"  value='<?php echo $diaa;?>' onfocus="formular.diaa.select()">
        /
        <input type="text" name="mesa" size="4" maxlength="2"  value='<?php echo $mesa;?>' onfocus="formular.mesa.select()">
        /
        <input type="text" name="anoa" size="6" maxlength="4"  value='<?php echo $anoa;?>' onfocus="formular.anoa.select()"></th>
		<th><input type="submit" name="boton" value="Consultar"></th>

	</table>
	    
  <input type="hidden" name="oficina" value='<?php echo $oficina;?>'>
  <input type="hidden" name="fm" value='<?php echo $fm;?>'><br>
		PAGINAS
		<?php
			$a=0;
			while($cant_pag>=$a)
			{
				//echo "<a href='".$_SERVER["PHP_SELF"]."?pagina=".($a+1)."'> ".($a+1)."</a>";
				if(($pagina-1)!=$a)
				{
					echo "<a href='".$_SERVER["PHP_SELF"]."?pagina=".($a+1)."&diad=$diad&mesd=$mesd&anod=$anod&diaa=$diaa&mesa=$mesa&anoa=$anoa'> ".($a+1)."</a>";
					//'$fechad 00:00:00' and '".$fechaa." 23:59:59' ";
				} else {
					echo "&nbsp;<font color=#000000 size='2'>".($a+1)."</font>&nbsp;";
				}
				$a++;
			}
		?>

<table border="1" cellspacing="0" cellpadding="1">
	<tr>
		<th>&nbsp;</th>
		<th>TIPO<BR>DOCU.</th>
		<th>SERIE<BR>DOCU.</th>
		<th>NUMERO<BR>DOCU.</th>
		<th>CODIGO<BR>CLIENTE</th>
		<th>FECHA</th>

		<th>PUNTO<BR>VENTA</th>
		<th>ALMACEN</th>
		<th>MONEDA</th>
		<th>TIPO<BR>CAMBIO</th>
		<th>VALOR<BR>BRUTO</th>
		<th>IMPUESTO</th>
		<th>VALOR<BR>TOTAL</th>
		<th>CREDITO</th>
		<th>FORMA<BR>PAGO</th>
		<th>ANULADO</th>
		<th>IMPRESO</th>

		<th>ANTICIPO</th>
		<th>INDENTIDAD</th>

	<tr>
		<th>&nbsp;</th>
		<th colspan="2"><INPUT type="submit" name="boton" value="Agregar"></th>
		<th colspan="2"><INPUT type="submit" name="boton" value="Modificar"></th>
		<th colspan="2"><INPUT type="submit" name="boton" value="Anular"></th>
		<th>&nbsp;</th><th>&nbsp;</th><th>&nbsp;</th><th>&nbsp;</th><th>&nbsp;</th>
		<th>&nbsp;</th><th>&nbsp;</th><th>&nbsp;</th><th>&nbsp;</th><th>&nbsp;</th>
		<th>&nbsp;</th>

	</tr>
	<?php
		$xsql = pg_query($conector_id, $sql);
		$i=0;
		while($i<pg_num_rows($xsql))
		{
			$rs = pg_fetch_array($xsql);

			$a = $rs[0];

			?>
			<tr bgcolor="#CCCC99"
				onMouseOver="this.style.backgroundColor='#FFFFCC';this.style.cursor='hand';"
				onMouseOut="this.style.backgroundColor='#CCCC99'"
				>
			<?php

			echo "
				<td><input type='radio' name='m_clave' value='$a'>
				<td>$rs[1]
				<td>$rs[2]
				<td>$rs[3]
				<td>$rs[4]

				<td>$rs[5]
				<td>$rs[6]
				<td>$rs[7]
				<td>$rs[8]
				<td>$rs[9]

				<td>$rs[10]

				<td>$rs[18]

				<td>$rs[11]
				<td>$rs[12]
				<td>$rs[13]
				<td>$rs[14]

				<td>$rs[15]
				<td>$rs[16]
				<td>$rs[17]
				";
		$i++;
		}
	?>
	<tr>
		<th>&nbsp;</th>
		<th colspan="2"><INPUT type="submit" name="boton" value="Agregar"></th>
		<th colspan="2"><INPUT type="submit" name="boton" value="Modificar"></th>
		<th colspan="2"><INPUT type="submit" name="boton" value="Anular"></th>
		<th>&nbsp;</th><th>&nbsp;</th><th>&nbsp;</th><th>&nbsp;</th><th>&nbsp;</th>
		<th>&nbsp;</th><th>&nbsp;</th><th>&nbsp;</th><th>&nbsp;</th><th>&nbsp;</th>
		<th>&nbsp;</th>
</table>
</table>
</form>



<?php pg_close($conector_id);?>
