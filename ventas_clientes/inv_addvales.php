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
		$msg_art=" Cód Artículo ";
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
var buffer=new Array(digitos) //declaración del array Buffer
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
   event.returnValue = false; //invalida la acción de pulsado de tecla para evitar busqueda del primer caracter
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

<form name='formular' action="inv_addvales.php?fm=<?php echo $fm;?>" method="post">
<input type="hidden" name="fm" value="<?php echo $fm;?>">
<input type="hidden" name="fecmov" value="<?php echo $movfecha;?>">
<input type="hidden" name="nromov" value="<?php echo $nromov;?>">

  <table border="0">
    <tr>
      <th width="211">ALMACEN</th>
      <td width="8">:</td>
	  <td colspan="2"><select name="almacen">
	  <?php
	  $sql_almacen="select ch_almacen, ch_nombre_almacen from inv_ta_almacenes";
	  $xsql_almacen=pg_exec($coneccion,$sql_almacen);

	  //$result_almacen = pg_fetch_row($xsql_almacen);
	 // echo $result_almacen[0];
		for($i1=0;$i1<pg_numrows($xsql_almacen);$i1++)
		{
			//	echo $rs[0];
			$result_almacen = pg_fetch_row($xsql_almacen,$i1);
	  		echo "<option value='".$result_almacen[0]."'>".$result_almacen[0]." - ".$result_almacen[1]."</option>";
		}
		echo "</select></td>";
		?>

	</tr>
    <tr>
      <th>PUNTO VENTA</th>
      <td>:</td>
      <td colspan="2"><input name='cliente' type='text' size='6' maxlength='3'><input name="imgalmac0" type="image" src="/sistemaweb/images/help.gif" width="16" height="15" border="0" ></td>
    </tr>
    <tr>
      <th>FECHA</th>
      <td>:</td>
	  <td colspan="2"><?php echo date("d/m/Y");?><input type="hidden" name="fecha" value="<?php echo date("d/m/Y");?>"</td>


		<!--<input type="submit" name="boton3" value="Ok" onClick="enviadatos()">--></td>

	</tr>
    <tr>
      <th>CLIENTE</th>
      <td>:</td>
      <td colspan="2"><input name="cliente" type="text" size="6" maxlength="3"><input name="imgalmac0" type="image" src="/sistemaweb/images/help.gif" width="16" height="15" border="0" onClick="abreclientes()">

      </td>
    </tr>
    <tr>
    <?php /*if($entform=="P") {

    <input type="hidden" name="entform" value="<?php echo $entform;?>">*/ ?>
    <tr>
      <th>NRO. VALE</th>
      <td>:</td>
      <td colspan="2"><input name="nota_despacho" type="text" size="15" maxlength="12">
        <th>COD. PLAN</th>
      <td>:</td>
      <td colspan="2"><input name="nota_despacho" type="text" size="15" maxlength="12">
        <TR>
		<th>NRO. PLACA</th>
      <td>:</td>
      <td colspan="2"><input name="nota_despacho" type="text" size="15" maxlength="12">
        <th>ODOMETRO</th>
      <td>:</td>
      <td colspan="2"><input name="nota_despacho" type="text" size="15" maxlength="12">
      </td>
    </tr>
     <tr>
    </tr>
  </table>

  <!--- ESTA ES LA SEGUNDA PARTE EN DONDE APARECEN
  EL SEGUNDO CUADRO SOBRE LOS DETALLES...
	-->

<table border="1" cellspacing="0">
<td></td>
<td width="100"><input type="button" value="Adicionar" action=""></td>
<td width="250"></td>
<td width="20"><input type="button" value="Eliminar" action=""></td>
<td width="30"></td>
<tr>
<td></td>
<th>ARTICULO</th>
<th>DESCRIPCION</th>
<th>CANTIDAD</th>
<th>IMPORTE</th>
<tr>
<?php
ECHO "
<td><input type='radio' value='num1'></td>
<td>asdf</td>
<td>bbb</td>
<td>ddd</td>
<td>144</td>
<tr>";
?>
<td></td>
<td><input type="button" value="Adicionar" action=""></td>
<td></td>
<td><input type="button" value="Eliminar" action=""></td>
<td></td>
<tr>



</table>
</body>
</html>
<?php pg_close($coneccion); ?>
