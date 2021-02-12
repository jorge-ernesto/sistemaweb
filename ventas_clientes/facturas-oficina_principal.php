<?php
include("../valida_sess.php");
include("../functions.php");
require("../clases/funciones.php");
include("/sistemaweb/cpagar/funciones.php");
$funcion = new class_funciones;
// crea la clase para controlar errores
$clase_error = new OpensoftError;
$clase_error->_error();
// conectar con la base de datos
$conector_id=$funcion->conectar("","","","","");

//echo "Variable oficina ".$oficina ;

switch($boton)
{
	case "Agregar":
		if($oficina=="no"){
			header("Location: facturas_agregar.php");
		}else{
			header("Location: facturas-oficina_agregar.php");
		}
		//break;
	case "Modificar":
		if($m_clave!="")
		{  $m_clave = str_replace('#','',$m_clave) ;
		   echo $m_clave ;
		   $programa='facturas-oficina_principal' ;
		   header("Location: facturas_agregar_modif.php?m_clave=$m_clave&programa=$programa");
		}
		break;
	
	case "Anular":
		
		if($m_clave!=""){
		$L = separarCadena($m_clave,"#");	
		$cod_docu=$L[0];
		$serie_docu=$L[1];
		$num_docu=$L[2];
		$cod_cliente=$L[3];

		$qm1 = "select ventas_fn_eliminacion_documentos
		('$cod_docu','$serie_docu','$num_docu','$cod_cliente','ANULACION')";
		pg_exec($qm1);
		}
	break;
		
	case "Eliminar":
		
		if($m_clave!=""){
		$L = separarCadena($m_clave,"#");	
		$cod_docu=$L[0];
		$serie_docu=$L[1];
		$num_docu=$L[2];
		$cod_cliente=$L[3];

		$qm1 = "SELECT ventas_fn_eliminacion_documentos
		('$cod_docu','$serie_docu','$num_docu','$cod_cliente','ELIMINACION')";
		pg_exec($qm1);
		}
		
	break;
}

$w1 = "";
$w2 = "";
$w3 = "";
if($c_tipo_doc!=""){ $w1 = " and CH_FAC_TIPODOCUMENTO = '$c_tipo_doc' ";}
if($c_serie_doc!=""){ $w2 = " and CH_FAC_SERIEDOCUMENTO = '$c_serie_doc' "; }
if($c_num_doc!=""){ $w3 = "  and  CH_FAC_NUMERODOCUMENTO like  '%$c_num_doc%' ";}

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
$sqladd="AND dt_fac_fecha between '$fechad 00:00:00' AND '".$fechaa." 23:59:59' ";
//echo $sqladd." *-*--- ".$pagina;
//}


$count = pg_query($conector_id, "select count(*) from fac_ta_factura_cabecera WHERE ch_almacen='$almacen' $sqladd");
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



$sql = "SELECT ".
             "trim(ch_fac_tipodocumento)||trim(ch_fac_seriedocumento)||trim(ch_fac_numerodocumento)||trim(cli_codigo), ".
             "ch_fac_tipodocumento, ".
             "ch_fac_seriedocumento, ".
             "ch_fac_numerodocumento, ".
             "cli_codigo, ".
             "to_char(dt_fac_fecha,'dd/mm/yyyy') as dt_fac_fecha, ".
             "ch_punto_venta, ".
             "ch_almacen, ".
             "ch_fac_moneda, ".
             "nu_tipocambio, ".
             "nu_fac_valorbruto, ".
             "nu_fac_valortotal, ".
             "ch_fac_credito, ".
             "ch_fac_forma_pago, ".
             "ch_fac_anulado, ".
             "ch_fac_impreso, ".
             "ch_fac_anticipo, ".
             "ch_fac_cab_identidad, ".
             "nu_fac_impuesto1, ".
             "ch_liquidacion as liqui ".
	"FROM fac_ta_factura_cabecera ".
	"WHERE (ch_almacen='001' OR ch_almacen='501') ".
	" ".$sqladd." ".
	" ".$w1." ".
	" ".$w2." ".
	" ".$w3." ".
	"ORDER BY dt_fac_fecha ".
	"LIMIT ".$limite." ".
	"OFFSET ".$inicio;
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
      <th width="62">DESDE :</th>
      <th width="165"><input type="text" name="diad" size="4" maxlength="2" value='<?php echo $diad;?>' onfocus="formular.diad.select()">
        / 
        <input type="text" name="mesd" size="4" maxlength="2" value='<?php echo $mesd;?>' onfocus="formular.mesd.select()">
        / 
        <input type="text" name="anod" size="6" maxlength="4"  value='<?php echo $anod;?>' onfocus="formular.anod.select()"></th>
      <th width="58">HASTA:</th>
      <th width="185"><input type="text" name="diaa" size="4" maxlength="2"  value='<?php echo $diaa;?>' onfocus="formular.diaa.select()">
        / 
        <input type="text" name="mesa" size="4" maxlength="2"  value='<?php echo $mesa;?>' onfocus="formular.mesa.select()">
        / 
        <input type="text" name="anoa" size="6" maxlength="4"  value='<?php echo $anoa;?>' onfocus="formular.anoa.select()"></th>
      <th width="184"><div align="left"> 
          <input type="submit" name="boton" value="Consultar">
        </div></th>
    <tr> 
      <th>Tipo:</th>
      <th><input type="text" name="c_tipo_doc" value="<?php echo $c_tipo_doc;?>"></th>
      <th colspan="2"><div align="left">Serie: 
          <input type="text" name="c_serie_doc" value="<?php echo $c_serie_doc;?>">
        </div></th>
      <th><div align="left">Numero: 
          <input type="text" name="c_num_doc" value="<?php echo $c_num_doc;?>">
        </div></th>
    <tr> 
      <th>&nbsp;</th>
      <th>&nbsp;</th>
      <th colspan="2"><div align="left"> </div></th>
      <th>&nbsp;</th>
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

  <table width="1184" border="1" cellpadding="1" cellspacing="0">
    <tr>
		<th width="11">&nbsp;</th>
		<th width="90">TIPO<BR>DOCU.</th>
		<th width="160">SERIE<BR>DOCU.</th>
		<th width="324">NUMERO<BR>DOCU.</th>
		<th width="227">CODIGO<BR>CLIENTE</th>
		
      <th width="600">FECHA </th>

		<th width="180">PUNTO<BR>VENTA</th>
		
      <th width="50">ALMA</th>
		
      <th width="97">MO</th>
		<th width="221">TIPO<BR>CAMBIO</th>
		<th width="186">VALOR<BR>BRUTO</th>
		<th width="272">IMPUESTO</th>
		<th width="186">VALOR<BR>TOTAL</th>
		<th width="240">CREDITO</th>
		<th width="199">FORMA<BR>PAGO</th>
		<th width="259">ANULADO</th>
		<th width="243">IMPRESO</th>

		<th width="253">ANTICIPO</th>
		
      <th width="434">NUM. LIQ</th>

	<tr>
		<th>&nbsp;</th>
		<th colspan="2"><INPUT type="submit" name="boton" value="Agregar"></th>
		<th colspan="2"><INPUT type="submit" name="boton" value="Modificar"></th>
		
      <th colspan="2"><input type="submit" name="boton" value="Anular"></th>
		<th><input type="submit" name="boton" value="Eliminar"></th><th>&nbsp;</th><th>&nbsp;</th><th>&nbsp;</th><th>&nbsp;</th>
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
			$jc = trim($rs[1])."#".trim($rs[2])."#".trim($rs[3])."#".trim($rs[4]) ;
			
			?>
			<tr bgcolor="#CCCC99"
				onMouseOver="this.style.backgroundColor='#FFFFCC';this.style.cursor='hand';"
				onMouseOut="this.style.backgroundColor='#CCCC99'"
				>
			<?php

              // <td><input type='radio' name='m_clave' value='" . trim($rs[1])."#".trim($rs[2])."#".trim($rs[3])."#".trim($rs[4]). "'>
			echo "
				<td><input type='radio' name='m_clave' value='$jc'>
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
				<td>$rs[19]
				
				";
		$i++;
		}
	?>
	<tr>
		<th>&nbsp;</th>
		<th colspan="2"><INPUT type="submit" name="boton" value="Agregar"></th>
		<th colspan="2"><INPUT type="submit" name="boton" value="Modificar"></th>
		<th colspan="2"><INPUT type="submit" name="boton" value="Anular"></th>
		<th><input type="submit" name="boton" value="Eliminar"></th><th>&nbsp;</th><th>&nbsp;</th><th>&nbsp;</th><th>&nbsp;</th>
		<th>&nbsp;</th><th>&nbsp;</th><th>&nbsp;</th><th>&nbsp;</th><th>&nbsp;</th>
		<th>&nbsp;</th>
</table>
</table>
</form>



<?php pg_close($conector_id);?>
