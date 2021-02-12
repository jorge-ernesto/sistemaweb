<?php
extract($_REQUEST);
	//include("../config.php");
	//include("inc_top.php");
include("../menu_princ.php");
	include("../functions.php");

	if($ano=="") { $ano=date("Y"); }
	if($mes=="") { $mes=date("m"); }
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <title>sistemaweb</title>
    <script language="javascript">
      var miPopup
      function abrelinea() {
        miPopup = window.open("escogelinea.php","miwin","width=500,height=400,scrollbars=yes")
        miPopup.focus()
      }

  /*function abreart(){
      miPopup = window.open("/sistemaweb/menu/archivos/escogeart.php","miwin","width=600,height=350,scrollbars=yes")
      miPopup.focus()
  }*/

      function abreart() {
        miPopup = window.open("escogeart.php","miwin","width=600,height=350,scrollbars=yes")
        miPopup.focus()
      }

      function enviadatos(){
        document.formular.submit()
      }

      function validar(){
    	var mes = document.formular.mes.value;

      var multi = mes*2/2;
  	//alert(mes+" - "+multi);

      	if(mes!=multi) {
          alert("El mes debe ser un numero menor a 12");
        } else {
      		document.formular.submit()
      	}
      }

    </script>
  </head>
  <body>
    <h2 align="center" style="color:#336699"><b> COMPOSICI&Oacute;N DEL STOCK DE CADA ARTICULO </b></h2>
    <div align="center">
      <form name='formular' action="" method="post">
      <p>
        <?php
          if($tiporep=="A") {
            $chk1=" checked"; $chk2="";
          } else if($tiporep=="B") {
            $chk2=" checked"; $chk1="";
          }
        ?>

        <input name="tiporep" type="radio" value="A" onclick="submit(this)" <?php echo $chk1;?>>
        STK GENERAL
        <input type="radio" name="tiporep" value="B" onclick="submit(this)" <?php echo $chk2;?>>
        POR LINEA
      </p>

    <table>
      <tr>
        <th align="right">
        <?php if($tiporep=="A") { ?>
		      A&Ntilde;O :</td><td><input type="text" maxlength="4" size="5" name="ano" value="<?php echo $ano; ?>"></td>
		<th>MES :</td><td><input type="text" maxlength="2" size="3" name="mes" value="<?php echo $mes; ?>"></td>
	<TR>
		<th align="right">COD. ARTICULO :</td>
		<td colspan="3">
        <?php
			if(strlen($artic)>0) {
				$xsqlart=pg_exec($coneccion,"select art_codigo,art_descripcion,art_stkgnrlmin,art_stkgnrlmax,art_stockactual from int_articulos where art_codigo like '%".$artic."%' ");
				if(pg_numrows($xsqlart)>0) { $artic=pg_result($xsqlart,0,0); $descart=pg_result($xsqlart,0,1); $artmin=pg_result($xsqlart,0,2);
				$artmax=pg_result($xsqlart,0,3); $artstk=pg_result($xsqlart,0,4); }
			}
		?>
        <input type="text" name="artic" size='19' maxlength="13" value="<?php echo $artic;?>">
        <input name="imglinea" type="image" src="/sistemaweb/images/help.gif" width="16" height="15" border="0" onClick="abreart()">
        &nbsp; </td><td><?php echo $descart; ?></td>
      <td>
        <!--<input name="boton" type="submit" value="Buscar" />-->
      </td>
    </tr>
	    <tr>
      <td>&nbsp;</td>
      <td colspan="2" align="center"><input type="button" name="boton" value="Buscar" onClick="javascript:validar()"/></td>
      <td>&nbsp;</td>
    </tr>
<?php }
 if($tiporep=="B") {?>
    <tr>
      <td> L&iacute;nea Art&iacute;culo: </td>
      <td>
        <?php
if(strlen($linea)>0) {
  $sqllin="select tab_elemento,tab_descripcion,tab_car_03 from int_tabla_general where tab_tabla='20' and (tab_elemento like '%".$linea."%' or tab_descripcion like '%".$linea."%')";
  $xsqllin=pg_exec($coneccion,$sqllin);
  $ilimitlin=pg_numrows($xsqllin);
  if($ilimitlin>0) {
    $linea=pg_result($xsqllin,0,0);	$desclinea=pg_result($xsqllin,0,1);
//	if($linea=="000000") { $linea=""; }
	$flglinea=pg_result($xsqllin,0,2); //echo "<input type='hidden' name='flglinea' value='".$flglinea."'>";
  }
}  ?>
        <input name='linea' type='text' value='<?php echo $linea;?>' size='10' maxlength='6'></td>
      <td> <input name="imglinea" type="image" src="/sistemaweb/images/help.gif" width="16" height="15" border="0" onclick="abrelinea()"></td>
      <td> <?php echo $desclinea; ?> </td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td><input type="submit" name="boton" value="Buscar"/></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
<?php } ?>
  </table>

<?php if(($tiporep=="A" and strlen($artic)>0) or ($tiporep=="B" and strlen($linea)>0)) {?>
<table>
  <tr>
    <th class="grid_cabecera">CODIGO</th>
    <th class="grid_cabecera">ALMACEN</th>
    <th class="grid_cabecera">FEC. ENTRADA</th>
    <th class="grid_cabecera">FEC. SALIDA</th>
    <th class="grid_cabecera">STK MIN.</th>
    <th class="grid_cabecera">STK. MAX.</th>
    <th class="grid_cabecera">STOCK</th>
  </tr>
<!--  <tr>
    <td><b>&nbsp;<?php echo $artic;?></b></td>
    <td><b>&nbsp;<?php echo $descart; ?></b></td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td align="right">&nbsp;<?php echo $artmin; ?></td>
    <td align="right">&nbsp;<?php echo $artmax; ?></td>
    <td align='right'>&nbsp;<?php echo number_format($artstk,2);?></td>
  </tr>-->
<?php
if($tiporep=="A")
{
	$sqlp="select a.ch_almacen,a.ch_nombre_almacen,s.stk_ucompra,s.stk_uventa,0,0,s.stk_stock".$mes.",ar.art_codigo,ar.art_descripcion,ar.art_stkgnrlmin,ar.art_stkgnrlmax
			FROM inv_ta_almacenes a, inv_saldoalma s,int_articulos ar where a.ch_clase_almacen='1' and s.art_codigo=ar.art_codigo and
			s.stk_almacen=a.ch_almacen and s.stk_periodo='".$ano."' and s.art_codigo='".$artic."' ";
}elseif($tiporep=="B"){
	$linea=substr($linea,4,2);
	$sqlp="select a.ch_almacen, a.ch_nombre_almacen, s.stk_ucompra, s.stk_uventa,
					0, 0, s.stk_stockactual, ar.art_codigo, ar.art_descripcion,
					ar.art_stkgnrlmin,ar.art_stkgnrlmax

				FROM inv_ta_almacenes a, inv_saldoalma s,int_articulos ar

				WHERE a.ch_clase_almacen='1' and ar.art_linea like '%".$linea."' and
				s.stk_almacen=a.ch_almacen and s.stk_periodo='".$ano."' and s.art_codigo=ar.art_codigo
				ORDER BY s.art_codigo";
}

//echo $sqlp;
$xsqlp=pg_exec($coneccion,$sqlp);
$irowp=0;  $ilimitp=pg_numrows($xsqlp); $xxpx="";
while($irowp<$ilimitp) {
	$p0=pg_result($xsqlp,$irowp,0);
	$p1=pg_result($xsqlp,$irowp,1);
	$p2=pg_result($xsqlp,$irowp,2);
	$p3=pg_result($xsqlp,$irowp,3);
	$p4=pg_result($xsqlp,$irowp,4);
	$p5=pg_result($xsqlp,$irowp,5);
	$p6=pg_result($xsqlp,$irowp,6);
	$p7=pg_result($xsqlp,$irowp,7);
	$p8=pg_result($xsqlp,$irowp,8);
	$p9=pg_result($xsqlp,$irowp,9);
	$p10=pg_result($xsqlp,$irowp,10);

	if($p7!=$xxpx){
		echo "<tr><td><b>&nbsp;".$p7."</b></td>";
		echo "<td><b>&nbsp;".$p8."</b></td>";
		echo "<td>&nbsp;</td><td>&nbsp;</td>";
		echo "<td align='right'>&nbsp;".$p9."</td>";
		echo "<td align='right'>&nbsp;".$p10."</td>";
		echo "<td align='right'>&nbsp;".number_format($p11,2)."</td></tr>";
	}

    echo "<tr><td>&nbsp;".$p0."</td>";
    echo "<td>&nbsp;".$p1."</td><td>&nbsp;".$p2."</td>";
    echo "<td>&nbsp;".$p3."</td><td align='right'>&nbsp;".$p4."</td>";
    echo "<td align='right'>&nbsp;".$p5."</td><td align='right'>&nbsp;".number_format($p6,2)."</td></tr>";
	$irowp++;
	$xxpx=$p7;
   }
}
?>

</table>
</form>
</div>
<p>&nbsp;</p>
</body>
</html>

<?php pg_close($coneccion); ?>
