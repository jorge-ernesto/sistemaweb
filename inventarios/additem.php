<?php
include("../../valida_sess.php");
include("../../config.php");
include("../../inc_top.php");
include("../../functions.php");

  if(strlen($escoglinea)>0) {
    $xsqlitserv=pg_exec($coneccion,"select tab_car_03 from int_tabla_general where tab_tabla='20' and tab_elemento='".$escoglinea."' ");
	if(pg_numrows($xsqlitserv)>0) { $itemserv=pg_result($xsqlitserv,0,0); if(trim($itemserv)=="S") { $itemserv="N"; } elseif(trim($itemserv)=="N"){ $itemserv="S"; } }
  }elseif(strlen($linea)>0) {
    $xsqlitserv=pg_exec($coneccion,"select tab_car_03 from int_tabla_general where tab_tabla='20' and tab_elemento='".$linea."' ");
	if(pg_numrows($xsqlitserv)>0) { $itemserv=pg_result($xsqlitserv,0,0); if(trim($itemserv)=="S") { $itemserv="N"; } elseif(trim($itemserv)=="N"){ $itemserv="S"; } }
  }


if($boton=="Regresar") {
?>
<script>
location.href='items.php';
</script>
<?php
}elseif($boton=="Siguiente") {


}elseif($boton=="Grabar") {
  
  $sqlai="select art_codigo from int_articulos where art_codigo='".$coditem."' ";
//  echo $sqlai;
  $xsqlai=pg_exec($coneccion,$sqlai);
  $ilimitai=pg_numrows($xsqlai);
  if($ilimitai==0) {
//  (art_codigo,art_descripcion,art_costoactual,art_stockactual,art_linea)
$fechoy=date("Y-m-d");
$estado="1";
$ftransmis="M";
$feccostrepos=$feccostreposa."/".$feccostreposm."/".$feccostreposd;
if(strlen($ctoreposic)==0) { $ctoreposic=0; }
if(strlen($stkactual)==0) { $stkactual=0; } 
if(strlen($ctoactual)==0) { $ctoactual=0; }
if(strlen($stkinicompra)==0) { $stkinicompra=0; }
if(strlen($costoinicompra)==0) { $costoinicompra=0; }
if(strlen($plazorepprom)==0) { $plazorepprom=0; }
if(strlen($diareposic)==0) { $diareposic=0; }
if(strlen($promconsumo)==0) { $promconsumo=0; }
if(strlen($stkgnrlmax)==0) { $stkgnrlmax=0; }
if(strlen($stkgnrlmin)==0) { $stkgnrlmin=0; }
if(strlen($imp1)==0) { $imp1=0; }
if(strlen($precio1)==0) { $precio1=0; }
if(strlen($precio2)==0) { $precio2=$precio1; }
if(strlen($precio3)==0) { $precio3=$precio1; }
if(strlen($precio4)==0) { $precio4=$precio1; }

if(strlen($feccostreposd)==0 and strlen($feccostreposm)==0 and strlen($feccostreposa)==0) {

}else{

valida_fecha($feccostreposd,$feccostreposm,$feccostreposa);

	if(strlen($mens_valida_fecha)>0) {
?>
<script>
alert("La fecha ingresada no es una fecha válida, por favor modifíquela !!! ")
</script>
<?php	}else{

		$addsql=" ,art_feccostorep='".$feccostrepos."' ";
	}
//art_margenutilidad='".$."',art_fecucompra='".$."',art_fecuventa='".$."',

}  
  $sqli="insert into int_articulos(art_codigo,art_descripcion,art_descbreve,art_clase,art_tipo,art_linea,art_unidad,art_presentacion,
  art_costoinicial,art_costoreposicion,art_fecactuliz,art_estado,art_trasmision,art_impuesto1,art_stkgnrlmin,art_stkgnrlmax,
  art_promconsumo,art_plazoreposicprom,art_diasreposic,art_cod_ubicac,art_usuario  ".$addsql1.") 
  values('".strtoupper($coditem)."','".strtoupper($desc)."','".strtoupper($descbreve)."','".$tipo."','".$tipo."','".$linea."','".$unidmanejo."','".$unidpresent."',
  ".$costoinicompra.",".$ctoreposic.",'".$fechoy."','".$estado."','".$ftransmis."','".$imp1."',".$stkgnrlmin.",".$stkgnrlmax.",
  ".$promconsumo.",".$plazorepprom.",".$diareposic.",'".$ubicac."','".$user."'  ".$addsql2.")";
   
//  echo $sqli;
  $xsqli=pg_exec($coneccion,$sqli);
  
  $moneda="02";
  $sqlprecios="insert into fac_lista_precios(pre_lista_precio,art_codigo,pre_moneda,pre_precio_fec1,
  pre_usuario,pre_precio_act1,pre_estado,pre_fecactualiz,pre_transmision) 
  values('".$moneda."','".strtoupper($coditem)."','".$moneda."','".$fechoy."','".$user."',".$precio1.",'1','".$fechoy."','1')";
  $xsqlprecios=pg_exec($coneccion,$sqlprecios);
?>
<script>
location.href='items.php';
</script>
<?php  } else {
//     echo "el codigo del item ya existe !!!";
?>
<script>
	alert(" El código de artículo <?php echo $coditem; ?>  ya existe, ingrese otro código !!!")
</script>
<?php
  }
 }
?>

<html> 
<head> 
    <title>Formulario prefijos</title> 
<script language="javascript"> 
var miPopup 
function abrelinea(){ 
    miPopup = window.open("escogelinea.php","miwin","width=500,height=400,scrollbars=yes") 
    miPopup.focus() 
} 
function abretipo(){ 
    miPopup = window.open("escogetipo.php","miwin","width=500,height=400,scrollbars=yes") 
    miPopup.focus() 
}
function abreunipres() {
    miPopup = window.open("escogeunipres.php","miwin","width=500,height=400,scrollbars=yes") 
    miPopup.focus() 
}
function abreuniman() {
    miPopup = window.open("escogeuniman.php","miwin","width=500,height=400,scrollbars=yes") 
    miPopup.focus() 
}
function abreubica(){ 
    miPopup = window.open("/sistemaweb/menu/procesos/ubicac.php","miwin","width=500,height=400,scrollbars=yes") 
    miPopup.focus() 
}
function enviadatos(){
	document.formular.submit()
}
</script> 
</head> 
<body> 
<p>ADICIONAR ART&Iacute;CULO</p>
<form name='formular' action="additem.php" method="post">
<!--<input type="hidden" name='fupd' value="">-->
<table border="0"><tr><td>
<table border="1" cellspacing="0" cellpadding="0">
          <tr> 
            <td>C&oacute;digo de Item</td>
            <td>:</td>
            <td><input name="coditem" type="text" value="<?php echo $coditem;?>" maxlength="13"></td>
<?php //onKeyPress="return esInteger(event)" ?>		
          </tr>
          <tr> 
            <td>Descripci&oacute;n</td>
            <td>:</td>
            <td><input name="desc" type="text" value="<?php echo $desc; ?>" size="45" maxlength="55"></td>
          </tr>
          <tr> 
            <td>Descripci&oacute;n Breve</td>
            <td>:</td>
            <td><input name="descbreve" type="text" value="<?php echo $descbreve;?>" size="40" maxlength="20"></td>
          </tr>
          <tr> 
            <td>Item de Servicios</td>
            <td>:</td>
            <td><input name="itemserv" type="text" value="<?php echo $itemserv; ?>" size="5" readonly> 
              <input type="submit" name="boton3" value="Ok" onClick="enviadatos()"></td>
          </tr>
          <tr> 
        <!--    <td>C&oacute;digo de Maq.Reg</td>
            <td>:</td>
            <td><input name="codmaqreg" type="text" value="<?php echo $codmaqreg; ?>"></td>
          </tr>-->
          <tr> 
            <td height="24">L&iacute;nea</td>
            <td>:</td>
            <td><p> 
                <input name='linea' type='text' value='<?php echo $linea;?>' size='10' maxlength='6'>
                <input name="imglinea" type="image" src="/sistemaweb/images/help.gif" width="16" height="15" border="0" onclick="abrelinea()">
                <?php
  $sqllin="select tab_elemento,tab_descripcion,tab_car_03 from int_tabla_general where tab_tabla='20' and (tab_elemento like '%".$linea."%' or tab_descripcion like '%".$linea."%')";
  $xsqllin=pg_exec($coneccion,$sqllin);
  $ilimitlin=pg_numrows($xsqllin);
  if($ilimitlin>0) {
    $codlinea=pg_result($xsqllin,0,0);	$desclinea=pg_result($xsqllin,0,1);	
	$flglinea=pg_result($xsqllin,0,2); echo $desclinea; //echo "<input type='hidden' name='flglinea' value='".$flglinea."'>";
  }
  ?>
            </td>
          </tr>
          <tr> 
            <td>Tipo</td>
            <td>:</td>
            <td><input name='tipo' type='text' value='<?php echo $tipo;?>' size='10' maxlength="6"> 
              <input name="imgtipo" type="image" src="/sistemaweb/images/help.gif" width="16" height="15" border="0" onclick="abretipo()"> 
              <?php
if(strlen($tipo)>0) {
  $sqlai="select tab_elemento,tab_descripcion from int_tabla_general where tab_tabla='21' and tab_elemento='".$tipo."' ";
  $xsqlai=pg_exec($coneccion,$sqlai);
  $ilimitai=pg_numrows($xsqlai);
  if($ilimitai>0) {
  $codtipo=pg_result($xsqlai,0,0);	
  $desctipo=pg_result($xsqlai,0,1);	echo $desctipo; //echo "<input type='hidden' name='htipo' value='".$codtipo."'>";
  } else {?>
<script>
alert(" El tipo con código <?php echo $tipo;?> no existe !!! ")
</script>
<?php  }
}
?>
            </td>
          </tr>
<!--          <tr> 
            <td>Tipo de Estad&iacute;stica</td>
            <td>:</td>
            <td><input name="tipoestad" type="text" value="<?php echo $tipoestad; ?>"> 
            </td>
          </tr>
          <tr> 
            <td>Marca</td>
            <td>:</td>
            <td><input name="marca" type="text" value="<?php echo $marca; ?>"></td>
          </tr>-->
          <tr> 
            <td>Unidad de Manejo</td>
            <td>:</td>
            <td><input name="unidmanejo" type="text" value="<?php echo $unidmanejo;?>" size="10" maxlength="6"> 
              <input name="imgunimanej" type="image" src="/sistemaweb/images/help.gif" width="16" height="15" border="0" onclick="abreuniman()"> 
              <?php
if(strlen($unidmanejo)>0) {
  $sqlai="select tab_descripcion from int_tabla_general where tab_tabla='34' and tab_elemento='".$unidmanejo."' ";
//  $sqlai="select tab_descripcion from int_tabla_general where tab_tabla='35' and tab_elemento='".$unidmanejo."' ";
//  echo $sqlai;
  $xsqlai=pg_exec($coneccion,$sqlai);
  $ilimitai=pg_numrows($xsqlai);
  if($ilimitai>0) {
  	$descunidmanejo=pg_result($xsqlai,0,0);		echo $descunidmanejo;
  } else {
	$nrocaract=6;
	$cadena=$unidmanejo;
	completaceros($nrocaract,$cadena);
	//echo $cadena;
	$unidmanejo=$cadena;
$sqlai="select tab_desc_breve from int_tabla_general where tab_tabla='34' and tab_elemento='".$unidmanejo."' ";
//  echo $sqlai;
  $xsqlai=pg_exec($coneccion,$sqlai);
  $ilimitai=pg_numrows($xsqlai);
    if($ilimitai>0) {
  	$descunidmanejo=pg_result($xsqlai,0,0);		echo $descunidmanejo;
    }
  }
}
?>
            </td>
          </tr>
          <tr> 
            <td>Impuesto 1</td>
            <td>:</td>
            <td><input name="imp1" type="text" value="<?php echo $imp1; ?>" size="10" style="text-align:right" maxlength="1"></td>
          </tr>
<!--          <tr> 
            <td>Impuesto 2</td>
            <td>:</td>
            <td><input name="imp2" type="text" value="<?php echo $imp2; ?>"></td>
          </tr>
          <tr> 
            <td>Impuesto 3</td>
            <td>&nbsp;</td>
            <td><input name="imp3" type="text" value="<?php echo $imp3; ?>"></td>
          </tr>
-->          <tr> 
            <td>Unidad de Presenta.</td>
            <td>:</td>
            <td><input name="unidpresent" type="text" value="<?php echo $unidpresent;?>" size="10" maxlength="6"> 
              <input name="imgpresta" type="image" src="/sistemaweb/images/help.gif" width="16" height="15" border="0" onclick="abreunipres()"> 
              <?php
if(strlen($unidpresent)>0) {

  $sqlai="select tab_desc_breve from int_tabla_general where tab_tabla='35' and tab_elemento='".$unidpresent."' ";
//  echo $sqlai;
  $xsqlai=pg_exec($coneccion,$sqlai);
  $ilimitai=pg_numrows($xsqlai);
  if($ilimitai>0) {
  	$descunidpresent=pg_result($xsqlai,0,0);		echo $descunidpresent;
  } else {
	$nrocaract=6;
	$cadena=$unidpresent;
	completaceros($nrocaract,$cadena);
	//echo $cadena;
	$unidpresent=$cadena;
	  $sqlai="select tab_desc_breve from int_tabla_general where tab_tabla='35' and tab_elemento='".$unidpresent."' ";
//  echo $sqlai;
  $xsqlai=pg_exec($coneccion,$sqlai);
  $ilimitai=pg_numrows($xsqlai);
  if($ilimitai>0) {
  	$descunidpresent=pg_result($xsqlai,0,0);		echo $descunidpresent;
  }
  }
}
?>
            </td>
          </tr>
          <tr>
            <td>Ubicaci&oacute;n</td>
            <td>:</td>
            <td><input name="ubicac" type="text" value="<?php echo $ubicac; ?>" size="10" maxlength="6">
<?php
if(strlen($ubicac)>0) {
  $sqlao="select cod_ubicac,desc_ubicac from inv_ta_ubicacion where cod_ubicac like '%".$ubicac."%' and cod_almacen='".$almacen."' ";
//  echo $sqlao;
  $xsqlao=pg_exec($coneccion,$sqlao);
  $ilimitao=pg_numrows($xsqlao);
  if($ilimitao>0){
//  $codao=pg_result($xsqlao,0,0);	
    $txtalma=pg_result($xsqlao,0,0);
    $descubic=pg_result($xsqlao,0,1);	
  }
}
?>
        <input name="imgubica" type="image" src="/sistemaweb/images/help.gif" width="16" height="15" border="0" onClick="abreubica()">
      <?php echo $descubic; ?></td>
          </tr>
        </table>
</td><td>
<?php if($itemserv=="N") { ?>

<table border="1" cellpadding="0" cellspacing="0">
          <tr> 
            <td>Stock General Mínimo</td>
            <td>:</td>
            <td><input type="text" name="stkgnrlmin" value="<?php echo $stkgnrlmin;?>" onKeyPress="return esIntspto(event)" style="text-align:right"></td>
          </tr>
          <tr> 
            <td>Stock General Máximo</td>
            <td>:</td>
            <td><input name="stkgnrlmax" type="text" value="<?php echo $stkgnrlmax;?>" onKeyPress="return esIntspto(event)" style="text-align:right"></td>
          </tr>
          <tr> 
            <td>Promedio de Consumo</td>
            <td>:</td>
            <td><input name="promconsumo" type="text" value="<?php echo $promconsumo; ?>" onKeyPress="return esIntspto(event)" style="text-align:right"></td>
          </tr>
          <tr> 
            <td>Plazo de Repos. Promedio</td>
            <td>:</td>
            <td><input name="plazorepprom" type="text" value="<?php echo $plazorepprom;?>" onKeyPress="return esIntspto(event)" style="text-align:right"></td>
          </tr>
          <tr> 
            <td>D&iacute;as de Reposici&oacute;n</td>
            <td>:</td>
            <td><input name="diareposic" type="text" value="<?php echo $diareposic; ?>" onKeyPress="return esIntspto(event)" style="text-align:right"></td>
          </tr>
          <tr> 
            <td>Costo Inicial Compra</td>
            <td>:</td>
            <td><input name="costoinicompra" type="text" value="<?php echo $costoinicompra;?>" onKeyPress="return esInteger(event)" style="text-align:right"></td>
          </tr>
<!--		  <tr> 
            <td>Stock Inicial Compra</td>
            <td>:</td>
            <td><input name="stkinicompra" type="text" value="<?php echo $stkinicompra;?>" onKeyPress="return esIntspto(event)" style="text-align:right"></td>
          </tr>-->
          <tr> 
            <td>Fecha Costo Reposic</td>
            <td>:</td>
            <td><input name="feccostreposd" type="text" style="text-align:right" value="<?php echo $feccostreposd;?>" size="4" maxlength="2">/
			<input name="feccostreposm" type="text" style="text-align:right" value="<?php echo $feccostreposm;?>" size="4" maxlength="2">/
			<input name="feccostreposa" type="text" style="text-align:right" value="<?php echo $feccostreposa;?>" size="6" maxlength="4">
			</td>
          </tr>
		  <td>Costo Reposic</td>
            <td>:</td>
            <td><input name="ctoreposic" type="text" value="<?php echo $ctoreposic;?>" onKeyPress="return esInteger(event)" style="text-align:right">

			</td>
          </tr>
 <tr> 
            <td height="24">Precio1</td>
            <td>:</td>
            <td><input name="precio1" type="text" value="<?php echo $precio1;?>" onKeyPress="return esInteger(event)" style="text-align:right"></td>
          </tr>
          <tr>
            <td>Precio2</td>
            <td>:</td>
            <td><input name="precio2" type="text" value="<?php echo $precio2;?>" onKeyPress="return esInteger(event)" style="text-align:right"> </td>
          </tr>
		  <tr>
            <td>Precio3</td>
            <td>:</td>
            <td><input name="precio3" type="text" value="<?php echo $precio3;?>" onKeyPress="return esInteger(event)" style="text-align:right"> </td>
          </tr>
		  <tr>
            <td>Precio4</td>
            <td>:</td>
            <td><input name="precio4" type="text" value="<?php echo $precio4;?>" onKeyPress="return esInteger(event)" style="text-align:right"> </td>
          </tr>
        </table>
<?php } ?>
</td></tr>
<tr>
      <td>&nbsp;</td>
      <td align="right">
<?php //if($boton=="Siguiente") { } else {?>  <!--    <input type="submit" name="boton" value="Siguiente"> --><?php //} ?>
<input type="submit" name="boton" value="Grabar">
<p><input type="submit" name="boton" value="Regresar"></p></td></tr>
</table>
</form>
</body>
</html>
<?php pg_close($coneccion);?>