<?php
include("../config.php");
include("../functions.php");
tipoform($fm,$coneccion);
$updnatu=$naturform;
//echo "naturaleza ".$naturform ;
if(trim($naturform)=="1" or trim($naturform)=="2") {
	if(strlen($almacen)>0) {
		$flag_bot_almacd="F";
		$fdest=" disabled";
		$fdest1=" readonly style='background-color: #CCCCCC' ";
		 /**/
		  $updalmacd=$almacen;
    	 // $updalmac=$almacen;
	}
   	$ofcentral_almo="2";
	$ofcentral_almd="1";
	$addalma1=" and tab_car_02='2' ";  $addalma2=""; 	$flagcito="A";
    if ( is_null( $updalmaco ) ) { $updalmaco=$entorig; }
	if ( is_null( $updalmacd ) ) { $updalmacd=$entdest; }	
	$updalmac=$updalmacd;
	//echo "entro carajo".$naturform ;
} elseif(trim($naturform)=="3" or trim($naturform)=="4") {
		if(strlen($almacen)>0) {
		$flag_bot_almaco="F";
		$forig=" disabled";
		$forig1=" readonly style='background-color: #CCCCCC' ";
		// $updalmaco=$almacen;
		//$updalmac=$almacen;
	 	}
   	$ofcentral_almo="1";
	$ofcentral_almd="3";
	$forig2=" readonly style='background-color: #CCCCCC' ";
	$addalma1="";
	$addalma2=" and (tab_car_02='3' or tab_car_02='1') ";
	$flagcito="B";
    if ( is_null( $updalmaco ) ) { $updalmaco=$entorig; }	
    if ( is_null( $updalmacd ) ) { $updalmacd=$entdest; }	
	$updalmac=$updalmaco;
} else { 
		$fdest="";  	$flagcito="A";   
		}

//echo "carajo".$ofcentral_almo ;

if($boton=="Insertar") {
	max_nro_mov($coneccion,$fm);
	$newcodart=$artic;
    if(strlen(trim($newcodart))>0) {
		$newcodartic=$newcodart;
		valida_existe_art($coneccion,$newcodart);
	//	echo "el valor del flages".$f_valart;
	}else{
		$msg_art=" C�d Art�culo ";
	}

	if(strlen(trim($updalmaco1))>0) {
		$updalmaco=$updalmaco1;
		valida_almo($coneccion,$updalmaco1);
	}
	if(strlen(trim($updalmacd1))>0) {
		$updalmacd=$updalmacd1;
		valida_almd($coneccion,$updalmacd1);
	}
	if(strlen(trim($updtipodocref1))>0) {
		$updtipodocref=$updtipodocref1;
		valida_docr($coneccion,$updtipodocref1);
	}
	if(strlen(trim($updprov1))>0) {
		$updprov=$updprov1;
		valida_prov($coneccion,$updprov1);
	}

	$updnrodocref=$updnrodocref1.$updnrodocref2;
//	echo $updnrodocref.$updnrodocref1.$updnrodocref2;
//    echo $updalmac."-".$updalmaco."-".$updalmacd;
	if(strlen(trim($newcostounit))==0) { $newcostounit=0; }
	if(strlen(trim($msg_art))==0 and strlen($f_valart)==0 and strlen($f_almo)==0 and strlen($f_almd)==0 and strlen($f_prov)==0 and strlen($f_tipodoc)==0) {
	inserta_item_mov($coneccion,$nromov,$fm,$newcodartic,$fecmov,$updalmac,$updalmaco,$updalmacd,$updnatu,$updtipodocref,$updnrodocref,$updprov,$newcant,$flagcito,$newcostounit,$entform);
	echo $sqlinsdet;
//	echo $msg_insert;
?>
<script>
location.href='inv_updmov.php?fm=<?php echo $fm;?>&nromov=<?php echo $nromov;?>&flg=A';
</script>
<?php
	} else {  $cadena_tot=$msg_art ." ".$f_almo." ".$f_almd." ".$f_prov." ".$f_tipodoc." ".$f_valart." incorrectos !!!";
?>
<script>
	alert(" <?php echo $cadena_tot; ?> ")
</script>
<?php
	}
}elseif($boton=="Eliminar") {
    $sqlcant="select mov_cantidad from inv_movialma where mov_numero='$nromov' and tran_codigo='$fm' and art_codigo='$codart' ";
	$xsqlcant=pg_exec($coneccion,$sqlcant);
	if(pg_numrows($xsqlcant)>0) { $cantart=pg_result($xsqlcant,0,0);

	$sqlinsdet="delete from inv_movialma where mov_numero='$nromov' and tran_codigo='$fm' and art_codigo='$codart' ";
	$xsqlinsdet=pg_exec($coneccion,$sqlinsdet);

	recalcula_costo_art($codart,$cantart);
	}
}elseif($boton=="Regresar") {
?>
<script languaje="JavaScript">
//	location.href='movdalmacen.php?fm=<?php echo $fm;?>&nromov=<?php echo $nromov;?>';
location.href='inv_movdalmacen.php?fm=<?php echo $fm;?>&flg=A';
</script>
<?php
} elseif($boton=="Modificar cabecera") {
	$sqlupdc="update inv_movialma set mov_almacen='$updalmac',mov_almaorigen='$updalmaco',mov_almadestino='$updalmacd',
	mov_tipdocuref='$updtipodocref',mov_docurefe='$updnrodocref1$updnrodocref2',mov_entidad='$updprov'
	where mov_numero='$nromov' and tran_codigo='$fm' ";
//	echo $sqlupdc;
	$xsqlupdc=pg_exec($coneccion,$sqlupdc);
}

include("inc_top.php");

if($flg=="A") {
 $movfecha=date("Y-m-d");
}

?>
<script language="JavaScript1.2">
var digitos=10 //cantidad de digitos buscados
var puntero=0
var buffer=new Array(digitos) //declaraci�n del array Buffer
var cadena=""

function buscar_op(obj,objfoco){
   var letra = String.fromCharCode(event.keyCode)
   if(puntero >= digitos){
       cadena="";
       puntero=0;
    }
   //si se presiona la tecla ENTER, borro el array de teclas presionadas y salto a otro objeto...
   if (event.keyCode == 13){
       borrar_buffer();
       if(objfoco!=0) objfoco.focus(); //evita foco a otro objeto si objfoco=0
    }
   //sino busco la cadena tipeada dentro del combo...
   else{
       buffer[puntero]=letra;
       //guardo en la posicion puntero la letra tipeada
       cadena=cadena+buffer[puntero]; //armo una cadena con los datos que van ingresando al array
       puntero++;

       //barro todas las opciones que contiene el combo y las comparo la cadena...
       for (var opcombo=0;opcombo < obj.length;opcombo++){
          if(obj[opcombo].text.substr(0,puntero).toLowerCase()==cadena.toLowerCase()){
          obj.selectedIndex=opcombo;
          }
       }
    }
   event.returnValue = false; //invalida la acci�n de pulsado de tecla para evitar busqueda del primer caracter
}

function borrar_buffer(){
   //inicializa la cadena buscada
    cadena="";
    puntero=0;
}
</script>
<script language="JavaScript" src="js/miguel.js"></script>
<html><head>

<script language="javascript">
var miPopup
function abrealmao(){
    miPopup = window.open("almaco.php?ofcentral_almo=<?php echo $ofcentral_almo;?>","miwin","width=500,height=400,scrollbars=yes")
    miPopup.focus()
}
function abrealmad(){
    miPopup = window.open("almad.php?ofcentral_almd=<?php echo $ofcentral_almd;?>","miwin","width=500,height=400,scrollbars=yes")
    miPopup.focus()
}
function abreprov() {
    miPopup = window.open("prov.php","miwin","width=500,height=400,scrollbars=yes")
    miPopup.focus()
}
function abretipodoc() {
    miPopup = window.open("tipodoc.php","miwin","width=500,height=400,scrollbars=yes")
    miPopup.focus()
}
function abreart(){
    miPopup = window.open("escogeart.php","miwin","width=600,height=350,scrollbars=yes")
    miPopup.focus()
}
function enviadatos(){
	document.formular.submit()
}
</script>
</head><body>

<form name='formular' action="inv_addmov.php?fm=<?php echo $fm;?>" method="post">
<input type="hidden" name="fm" value="<?php echo $fm;?>">
<input type="hidden" name="fecmov" value="<?php echo $movfecha;?>">
<input type="hidden" name="nromov" value="<?php echo $nromov;?>">

  <table border="1">
    <tr> 
      <th width="211">FORMULARIO</th>
      <td width="8">:</td>
      <td colspan="2">&nbsp;<?php echo $fm."-".$descform;?></td>
    </tr>
    <tr> 
      <th>N&deg; FORMULARIO</th>
      <td>:</td>
      <td colspan="2">&nbsp;<?php echo $nromov;?></td>
    </tr>
    <tr> 
      <th>FECHA</th>
      <td>:</td>
      <td colspan="2">&nbsp;<?php echo $movfecha;?> <input type="hidden" name="movfecha" value="<?php echo $movfecha;?>"> 
        <input type="submit" name="boton3" value="Ok" onClick="enviadatos()"></td>
    </tr>
    <tr> 
      <th>ALMACEN ORIGEN</th>
      <td>:</td>
      <td colspan="2"><input name="updalmaco" type="text" size="6" maxlength="3" value='<?php echo $updalmaco;?>' <?php echo $forig1; ?>> 
        <?php if($flag_bot_almaco=="F"){ ?>
        <?php }else{ ?>
        <input name="imgalmac0" type="image" src="/sistemaweb/images/help.gif" width="16" height="15" border="0" onClick="abrealmao()"> 
        <?php } ?>
        <?php
if(strlen($updalmaco)>0) {
  $sqlao="select tab_elemento,tab_descripcion from int_tabla_general where tab_tabla='ALMA' and tab_elemento like '%".$updalmaco."%' ";
//  echo $sqlao;
  $xsqlao=pg_exec($coneccion,$sqlao);
  $ilimitao=pg_numrows($xsqlao);
  if($ilimitao>0){
  $codao=pg_result($xsqlao,0,0);
  $descao=pg_result($xsqlao,0,1);	echo $descao;
  }
}
?>
      </td>
    </tr>
    <tr> 
      <th>ALMACEN DESTINO</th>
      <td>:</td>
      <td colspan="2"><input name="updalmacd" type="text" size="6" maxlength="3"  value='<?php echo $updalmacd;?>' <?php echo $fdest1; ?>> 
        <?php if($flag_bot_almacd=="F"){ ?>
        <?php }else{ ?>
        <input name="imgalmac0" type="image" src="/sistemaweb/images/help.gif" width="16" height="15" border="0" onClick="abrealmad()"> 
        <?php } ?>
        <?php
if(strlen($updalmacd)>0) {
  $sqlad="select tab_elemento,tab_descripcion from int_tabla_general where tab_tabla='ALMA' and tab_elemento like '%".$updalmacd."%' ";
//  echo $sqlao;
  $xsqlad=pg_exec($coneccion,$sqlad);
  $ilimitad=pg_numrows($xsqlad);
  if($ilimitad>0) {
  $codad=pg_result($xsqlad,0,0);
  $descad=pg_result($xsqlad,0,1);	echo $descad;
  }
}
?>
      </td>
    </tr>
    <?php if($entform=="P") {
?>
    <input type="hidden" name="entform" value="<?php echo $entform;?>">
    <tr> 
      <th>PROVEEDOR</th>
      <td>:</td>
      <td colspan="2"><input name="updprov" type="text" size="15" maxlength="12" value="<?php echo $updprov;?>"> 
        <input name="imgalmac02" type="image" src="/sistemaweb/images/help.gif" width="16" height="15" border="0" onClick="abreprov()"> 
        <?php
if(strlen($updprov)>0) {
$sqlprov="select pro_razsocial from int_proveedores where pro_codigo='".$updprov."' ";
//echo $sqlprov;
$xsqlprov=pg_exec($coneccion,$sqlprov);
if(pg_numrows($xsqlprov)>0) { $razsoc=pg_result($xsqlprov,0,0); echo $razsoc; }
}
?>
      </td>
    </tr>
    <?php } ?>
    <tr> 
      <th>TIPO Y No DE DOCUMENTO</th>
      <td>:</td>
      <td colspan="2"> <input name="updtipodocref" type="text" value="<?php echo $updtipodocref;?>" size="5" maxlength="2"> 
        <input name="imgalmac03" type="image" src="/sistemaweb/images/help.gif" width="16" height="15" border="0" onClick="abretipodoc()"> 
        <?php
if(strlen($updtipodocref)>0) {
$sqltipdoc="select tab_descripcion from int_tabla_general where tab_tabla='08' and tab_elemento like '%".$updtipodocref."%' ";
$xsqltipdoc=pg_exec($coneccion,$sqltipdoc);
if(pg_numrows($xsqltipdoc)>0) { $desctipo=pg_result($xsqltipdoc,0,0); echo $desctipo; }

}

?>
        <br> <input type="text" name="updnrodocref1" size="5" value='<?php echo $updnrodocref1;?>' maxlength="3">
        - 
        <input type="text" name="updnrodocref2" size="10" value='<?php echo $updnrodocref2;?>' maxlength="7"> 
      </td>
    </tr>
    <tr> 
      <td><div align="center"><strong>INGRESO DIRECTO ?</strong></div></td>
      <td>:</td>
      <td width="99">SI 
        <input type="radio" name="ing_dir" value="SI" onClick="javascript:ocultarFila('ord_compra') ,ingdir.value='SI' ;" <?php if($ingdir=="SI"){echo "checked";} ?>>
        | NO 
        <input type="radio" name="ing_dir" value="NO" onClick="javascript:mostrarFila('ord_compra') , ingdir.value='NO';" <?php if($ingdir=="NO"){echo "checked";} ?>></td>
      <td width="266" id="ord_compra">Orden de Compra 
        <input type="text" name="orden_compra">
        <input name="imgalmac04" type="image" src="/sistemaweb/images/help.gif" width="16" height="15" border="0" onClick="javascript:mostrarAyudaOrdenCompra('js/cpag_ayuda_orddev.php',updprov.value,updalmacd.value,'uno','formular.orden_compra','<?php echo $fm;?>')"></td>
    </tr>
  </table>
  <table border="1" cellpadding="0" cellspacing="0">
    <tr>
      <th>&nbsp;</th>
      <th>CODIGO</th>
      <th>DESCRIPCION
        <input type="hidden" name="ingdir" value="<?php echo $ingdir;?>"></th>
      <th>CANTIDAD</th>
      <th>COSTO UNITARIO</th>
      <th>&nbsp;</th>
    </tr>
    <tr>
      <th>&nbsp;</th>
<?php
if(strlen($artic)>0) {
	$xsqlart=pg_exec($coneccion,"select art_codigo,art_descripcion from int_articulos where art_codigo like '%".$artic."%' ");
	if(pg_numrows($xsqlart)>0) { $artic=pg_result($xsqlart,0,0); $descart=pg_result($xsqlart,0,1); }
}
?>
      <th><input type="text" name="artic" size='19' maxlength="13" value="<?php echo $artic;?>"><input name="imglinea" type="image" src="/sistemaweb/images/help.gif" width="16" height="15" border="0" onClick="abreart()"></th>
      <td>&nbsp;
<?php echo $descart; ?>
	  </td>
      <th><input name="newcant" type="text" size='15' maxlength="15"></th>
      <th><input name="newcostounit" type="text" size='15' maxlength="15" <?php echo $forig2; ?>></th>
      <th><input type="submit" name="boton" value="Insertar"></th>
    </tr>
    <?php
$sql3="select m.art_codigo,a.art_descripcion,m.mov_cantidad,m.mov_costounitario
        from inv_movialma m,int_articulos a
        where m.art_codigo=a.art_codigo and m.mov_numero='$nromov' and tran_codigo='$fm' ";
		//echo $sql3;
		$xsql3=pg_exec($coneccion,$sql3);
		$ilimit3=pg_numrows($xsql3);
		while($irow3<$ilimit3) {
			$ad0=pg_result($xsql3,$irow3,0);
			$ad1=pg_result($xsql3,$irow3,1);
			$ad2=pg_result($xsql3,$irow3,2);
			$ad3=pg_result($xsql3,$irow3,3);
			echo "<tr><td><input type='radio' name='codart' value='".$ad0."'></td></td><td>".$ad0."</td><td>".$ad1."</td><td><p align='right'>".$ad2."</p></td><td><p align='right'>".$ad3."</p></td></tr>";
			$irow3++;
		}
?>
    <tr>
      <td>&nbsp;</td>
      <td><input type="submit" name="boton" value="Eliminar"></td>
      <td><input type="submit" name="boton" value="Modificar cabecera">
        &nbsp;&nbsp;
        <input type="submit" name="boton" value="Regresar"></td>
      <td>&nbsp;</td>
      <td><input type="button" name="Submit" value="Submit" onClick="javascript:alert(ingdir.value);"></td>
      <td>&nbsp;</td>
    </tr>
  </table>
</form>
<script language="JavaScript">
//alert(document.formular.ingdir.value);
iniciarFormulario(document.formular.ingdir);
</script>
</body>
</html>
<?php pg_close($coneccion); ?>