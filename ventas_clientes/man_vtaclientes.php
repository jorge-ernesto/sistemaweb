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
		header("Location: man_vtaclientes_agregar.php?nuevo=ok");
		break;

	case Modificar:
		if($m_clave!="")
		{
			header("Location: man_vtaclientes_agregar.php?m_clave=$m_clave");
		}
		break;

	case Anular:
		if($m_clave!="")
		{
	/*			$sqldel="DELETE FROM FAC_TA_FACTURA_CABECERA
					WHERE
					trim(CH_FAC_TIPODOCUMENTO)||trim(CH_FAC_SERIEDOCUMENTO)||trim(CH_FAC_NUMERODOCUMENTO)||trim(CLI_CODIGO) = '$m_clave'";
	*/
		}
		break;
}




/*$count = pg_query($conector_id, "select count(*) from FAC_TA_FACTURA_CABECERA where CH_ALMACEN='$almacen'");
$cant_reg = pg_result($count,0,0);
$cant_pag = round(($cant_reg/20),0);
*/
//$count = pg_query($conector_id, $sql);



/*if($boton=="Consultar")
{*/
if(strlen(trim($criterio))==0)
{
	$sqladd=" nomusu like '%$buscar%'";
} else {
	$sqladd=" $criterio like '%$buscar%'";
}

//echo $sqladd." *-*--- ".$pagina;
//}

$count = pg_query($conector_id, "select count(*) from pos_fptshe1 where $sqladd");
$cant_reg = pg_result($count,0,0);
//echo "<br>REG".$cant_reg."<br>";
$cant_pag = round(($cant_reg/40),0);


if($pagina==0) {
	$pagina=1;
	$inicio=0;
	$limite=40;
}
else {
	$inicio=($pagina-1)*40;
	$limite=$limite+40;
}

$sql = "select
			  numtar, codcli, numtar, nomusu, numpla, estblo, estres, 
			  nu_limite_galones, nu_galones_acumulados

		from pos_fptshe1 she

		where
		    ".$sqladd."
		ORDER by codcli, numtar
		LIMIT ".$limite." OFFSET ".$inicio;


echo "<!--$sql-->";


include("inc_top.php"); 
?>

<script languaje='javascript'>
	function noImplementado()
	{
		alert('Opcion No Implementada..! \n llama a Cachi al #2759');
	}
</script>


<?php 
echo otorgarAlmacen($conector_id, $almacen);?><br>
CLIENTES - TARJETAS MAGNETICAS .: 
<form name="formular" method="post" action="" >

  <table>
    <tr> 
      <th width="129">Busqueda Rapida.:</th>
      <th width="120"><input type="text" name="buscar" maxlength="20" value="<?php echo $buscar; ?>"></th>
      <th width="82"><input type="submit" name="boton" value="Consultar"></th>
    <tr> 

		<?php 	
			if($criterio=="numtar")
			{
			?>
			  <th><div align="center"> 
	          <input type="radio" name="criterio" value="codcli">
    	      Cod.Cliente</div></th>

			  <th><input type="radio" name="criterio" value="numtar" checked>
        	  Nro Tarjeta</th>
		      
			  <th><div align="center"> 
	          <input type="radio" name="criterio" value="nomusu">
    	      Usuario</div></th>
			<?php
			}
			else if($criterio=="nomusu"){
				?>
				  <th><div align="center"> 
			      <input type="radio" name="criterio" value="codcli">
	    	      Cod.Cliente</div></th>
				  <th><input type="radio" name="criterio" value="numtar">
				  Nro Tarjeta</th>
				  <th><div align="center"> 
				  <input type="radio" name="criterio" value="nomusu" checked>
				  Usuario</div></th>
				<?php
			} else {
				?>
				  <th><div align="center"> 
				  <input type="radio" name="criterio" value="codcli" checked>
	    	      Cod.Cliente</div></th>
				  <th><div align="center">
				  <input type="radio" name="criterio" value="numtar">
				  Nro Tarjeta</div></th>
				  <th><div align="center"> 
				  <input type="radio" name="criterio" value="nomusu">
				  Usuario</div></th>
				<?php
			}
		?>
      <th>&nbsp;</th>
  </table>
	    <input type="hidden" name="fm" value='<?php echo $fm;?>'><hr>
		PAGINAS
		<?php
			$a=0;
			while($cant_pag>=$a)
			{
				//echo "<a href='".$_SERVER["PHP_SELF"]."?pagina=".($a+1)."'> ".($a+1)."</a>";
				if(($pagina-1)!=$a)
				{
					echo "<a href='".$_SERVER["PHP_SELF"]."?pagina=".($a+1)."&diad=$diad&mesd=$mesd&anod=$anod&diaa=$diaa&mesa=$mesa&anoa=$anoa&criterio=$criterio&buscar=$buscar'> ".($a+1)."</a>";
					//'$fechad 00:00:00' and '".$fechaa." 23:59:59' ";
				} else {
					echo "&nbsp;<font color=#000000 size='2'>".($a+1)."</font>&nbsp;";
				}
				$a++;
			}
		?>

  <table border="1" cellspacing="0" cellpadding="1">
    <tr> 
      <th width="1">&nbsp;</th>
      <th width="112">COD. CLIENTE</th>
      <th width="116">NRO. TARJETA</th>
      <th width="76">USUARIO </th>
      <th width="73">CLIENTE </th>
      <th width="52">PLACA</th>
      <th width="11">B</th>
      <th width="11">T</th>
      <th width="11"><div align="center">Limite<br>
          Galones</div></th>
      <th width="11"><div align="center">Galones<br>
          Acumulados</div></th>
    <tr> 
      <th>&nbsp;</th>
      <th colspan="2"><INPUT type="submit" name="boton" value="Agregar"></th>
      <th><INPUT type="submit" name="boton" value="Modificar"></th>
      <th><INPUT type="button" name="boton" value="Anular" onClick="javascript:noImplementado()"></th>
      <th>&nbsp;</th>
    </tr>
    <?php
		$xsql = pg_query($conector_id, $sql);
		$i=0;
		while($i<pg_num_rows($xsql))
		{
			$rs = pg_fetch_array($xsql);
			$a = $rs[0];

			$sql = "SELECT cli_razsocial from INT_CLIENTES where cli_codigo='".$rs[1]."'";
			//echo $sql;
			@$xquery = pg_exec($conector_id, $sql);
			@$razon_social = pg_result($xquery,0,0);

			?>
	    <tr bgcolor="#CCCC99"
				onMouseOver="this.style.backgroundColor='#FFFFCC';this.style.cursor='hand';"
				onMouseOut="this.style.backgroundColor='#CCCC99'"> 
      <?php
			echo "
				<td><input type='radio' name='m_clave' value='$a'>
				<td align='center'>$rs[1]
				<td align='center'>$rs[2]
				<td>$rs[3]

				<td>$razon_social
				<td>$rs[4]

				<td>$rs[5]
				<td>$rs[6]
				<td>$rs[7]

				<td>$rs[8]
				<td>$rs[9]
				";
		$i++;
		}
	?>
    <tr> 
      <th>&nbsp;</th>
      <th colspan="2"><INPUT type="submit" name="boton" value="Agregar"></th>
      <th><INPUT type="submit" name="boton" value="Modificar"></th>
      <th><INPUT type="button" name="boton" value="Anular" onClick="javascript:noImplementado()"></th>
      <th>&nbsp;</th>
  </table>
</table>
</form>



<?php pg_close($conector_id);?>
