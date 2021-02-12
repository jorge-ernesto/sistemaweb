<?php
//include("../config.php");
include("../menu_princ.php");
include("../functions.php");
/*obtenemos fecha*/
$nuevofechad = split('/',$_REQUEST['diasd']);
$diad=$nuevofechad[0];
$mesd=$nuevofechad[1];
$anod=$nuevofechad[2];
$nuevofechaa = split('/',$_REQUEST['diasa']);
$diaa=$nuevofechaa[0];
$mesa=$nuevofechaa[1];
$anoa=$nuevofechaa[2];


if($flg=="A") {
	rangodefechas();
	$diad=$zdiad; $mesd=$zmesd; $anod=$zanod; $diaa=$zdiaa; $mesa=$zmesa; $anoa=$zanoa;
	$fechad=$anod."/".$mesd."/".$diad." 00:00:00";
	$fechaa=$anoa."/".$mesa."/".$diaa." 23:59:59";
}
if($boton=="Buscar" and strlen($txtalma)>0) {
	$fechad=$anod."/".$mesd."/".$diad." 00:00:00";
	$fechaa=$anoa."/".$mesa."/".$diaa." 23:59:59";
//	echo $mesa."/".$diaa."/".$anoa;
	valida_fecha($diad,$mesd,$anod);
	$mens_valida_fechad=$mens_valida_fecha;
	valida_fecha($diaa,$mesa,$anoa);
	$mens_valida_fechaa=$mens_valida_fecha;
	$mens_valida_fechaf=$mens_valida_fechaaa." ".$mens_valida_fecha;
//	echo $mesa."/".$diaa."/".$anoa;
	if(strlen(trim($mens_valida_fechaf))>0) {
?>
<script language="JavaScript">
	alert(" <?php echo $mens_valida_fechaf; ?> ")
</script>
<?php
	}else{
		//$fechada=$anod."/".$mesd."/01";
		$fechad=$anod."/".$mesd."/".$diad." 00:00:00";
		$fechaa=$anoa."/".$mesa."/".$diaa." 23:59:59";
		$sql="select m.mov_fecha,m.mov_numero,m.tran_codigo,a.art_codigo,a.art_descripcion
		from inv_movialma m,int_articulos a where m.art_codigo=a.art_codigo and m.mov_fecha between '$fechad' and '$fechaa'
		and mov_almacen='".$txtalma."'
		order by a.art_descripcion ".$bddsql." ";
	//	echo $sql;
		$xsql=pg_exec($coneccion,$sql);
		$ilimit=pg_numrows($xsql);
		if($ilimit>0) {	$numeroRegistros=$ilimit; }
	}
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
<html><head></head>
<script language="javascript">
var miPopup
function abrealma(){
    miPopup = window.open("almac.php","miwin","width=500,height=400,scrollbars=yes");
    miPopup.focus();
}
function enviadatos(){
	document.formular.submit();
}
</script>
<body>
<script src="/sistemaweb/js/calendario.js" type="text/javascript" ></script>
<script src="/sistemaweb/js/overlib_mini.js" type="text/javascript" ></script>
<p>LISTADO DE MOVIMIENTOS DEL DIA</p>
<!--<form name='formular' action="" method="post"  onSubmit="return checkit(this)">-->
<form name='formular' action="inv_movdia.php" method="post">
  <table border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <th align="left">RANGO DE FECHAS </th>
       <th>Desde
        <input type="text" name="diasd" size="10" value="<?php echo $diad.'/'.$mesd.'/'.$anod ?>" readonly="true"/>
	&nbsp;<a href="javascript:show_calendar('formular.diasd');"><img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a><div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"/></div></th><th>
        &nbsp;&nbsp;Hasta
        <input type="text" name="diasa" size="10" value="<?php echo $diaa.'/'.$mesa.'/'.$anoa ?>" readonly="true"/>
	&nbsp;<a href="javascript:show_calendar('formular.diasa');"><img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a><div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></th>
    </tr>
    <tr>
      <th align="left">CODIGO DE ALMAC&Eacute;N&nbsp;</th>
      
<?php
if(strlen($txtalma)>0) {
  $sqlao="select tab_elemento,tab_descripcion from int_tabla_general where tab_tabla='ALMA' and tab_elemento like '%".$txtalma."%' ";
//  echo $sqlao;
  $xsqlao=pg_exec($coneccion,$sqlao);
  $ilimitao=pg_numrows($xsqlao);
  if($ilimitao>0){
//  $codao=pg_result($xsqlao,0,0);
    $txtalma=pg_result($xsqlao,0,0);
    $descao=pg_result($xsqlao,0,1);
  }
}
?>
      <td><input type="text" name="txtalma" size="10" value="<?php echo $txtalma;?>">
        <input name="imgalmaco" type="image" src="/sistemaweb/images/help.gif" width="16" height="15" border="0" onClick="abrealma()">
        <?php echo $descao; ?> </td>
    </tr>
    <tr>
      <th>&nbsp;</th>
      <th>&nbsp;
      <td><input name="boton" type="submit" value="Buscar"></td>
    </tr>
  </table>
</form>
<p align="center">&nbsp;</p>
<?php
if($boton=="Buscar" and strlen(trim($mens_valida_fechaf))==0 and strlen($txtalma)>0){
?>
<?php
$var_pers="diad=".$diad."&mesd=".$mesd."&anod=".$anod."&diaa=".$diaa."&mesa=".$mesa."&anoa=".$anoa."&artini=".$artini."&artfin=".$artfin."&txtalma=".$txtalma."&boton=Buscar";
include("../maestros/pagina.php");
$bddsql=" limit $tamPag offset $limitInf ";
?>
<table border="1" cellspacing="0" cellpadding="0">
  <tr bgcolor="#CCCC99">
    <th>CODIGO</th>
    <th>DESCRIPCION</th>
    <th>STOCK INICIAL</th>
    <th>VENTAS</th>
    <th>COMPRAS</th>
    <th>AJUSTES</th>
    <th>STOCK ACTUAL</th>
  </tr>
  <?php

  $ft=fopen('movdia.csv','w');
	if ($ft>0) {
		$snewbuffer="";
		$snewbuffer=$snewbuffer."LISTADO DE MOVIMIENTOS DEL DIA DEL ".$fechad." al ".$fechaa." \n";
		$snewbuffer=$snewbuffer."CODIGO,DESCRIPCION,STOCK INICIAL,VENTAS,COMPRAS,AJUSTES,STOCK ACTUAL \n";
		fwrite($ft,$snewbuffer);
		fclose($ft);
	}



 $sql="select m.mov_fecha,m.mov_numero,m.tran_codigo,a.art_codigo,a.art_descripcion,m.tran_codigo,m.mov_cantidad
		from inv_movialma m,int_articulos a where m.art_codigo=a.art_codigo and m.mov_fecha between '$fechad' and '$fechaa'
		and mov_almacen='".$txtalma."'
		order by a.art_descripcion ".$bddsql." ";
$xsql=pg_exec($coneccion,$sql);
$ilimit=pg_numrows($xsql);
while($irow<$ilimit) {
     $a0=pg_result($xsql,$irow,0);
	 $a1=pg_result($xsql,$irow,1);
	 $a2=pg_result($xsql,$irow,2);
	 $a3=pg_result($xsql,$irow,3);
	 $a4=pg_result($xsql,$irow,4);
	 $fm=pg_result($xsql,$irow,5);
	 $w0=pg_result($xsql,$irow,6);

	 if($irow==0 or $a3x==$a3) {
	 	if($fm=='05' or $fm=='08' or $fm=='11' or $fm=='14' or $fm=='21' or $fm=='24' or $fm=='25' or $fm=='28' or $fm=='45' or $fm=='46') {
			$totmovstkv[$a3]=$totmovstkv[$a3]-$w0;
			$totmovstkact[$a3]=$totmovstkact[$a3]-$totmovstkv[$a3];
		}elseif($fm=='01' or $fm=='07' or $fm=='12' or $fm=='16' or $fm=='18' or $fm=='19' or $fm=='23' or $fm=='26' or $fm=='27') {
			$totmovstkc[$a3]=$totmovstkc[$a3]+$w0;
			$totmovstkact[$a3]=$totmovstkact[$a3]+$totmovstkc[$a3];
		}elseif($fm=='17') { $totmovstka[$a3]=$totmovstka[$a3]+$w0;
			$totmovstkact[$a3]=$totmovstkact[$a3]+$totmovstka[$a3];
		}
	 } else {

	 }
	if($a3x==$a3) {
	} else {
	calcula_stkini($coneccion,$a3,$diad,$mesd,$anod,$txtalma);
	echo "<tr bgcolor=\"#CCCC99\" onMouseOver=\"this.style.backgroundColor='#FFFFCC';this.style.cursor='hand';\" onMouseOut=\"this.style.backgroundColor='#CCCC99'\"o\"];\"><td>&nbsp;".$a3."</td><td>&nbsp;".$a4."</td>";
    echo "<td align='right'>&nbsp;".number_format($stkini,2)."</td><td align='right'>&nbsp;".number_format($totmovstkv[$a3x],2)."</td>";
    echo "<td align='right'>&nbsp;".number_format($totmovstkc[$a3x],2)."</td><td align='right'>&nbsp;".number_format($totmovstka[$a3x],2)."</td>";
    echo "<td align='right'>&nbsp;".number_format($totmovstkact[$a3x],2)."</td></tr>";
	}
	$a3x=$a3;
	$irow++;
}

 $sql="select m.mov_fecha,m.mov_numero,m.tran_codigo,a.art_codigo,a.art_descripcion,m.tran_codigo,m.mov_cantidad
		from inv_movialma m,int_articulos a where m.art_codigo=a.art_codigo and m.mov_fecha between '$fechad' and '$fechaa'
		and mov_almacen='".$txtalma."' order by a.art_descripcion  ";
$xsql=pg_exec($coneccion,$sql);
$ilimit=pg_numrows($xsql);
$irow=0;
while($irow<$ilimit) {
     $a0=pg_result($xsql,$irow,0);
	 $a1=pg_result($xsql,$irow,1);
	 $a2=pg_result($xsql,$irow,2);
	 $a3=pg_result($xsql,$irow,3);
	 $a4=pg_result($xsql,$irow,4);
	 $fm=pg_result($xsql,$irow,5);
	 $w0=pg_result($xsql,$irow,6);

	 if($irow==0 or $a3x==$a3) {
	 	if($fm=='05' or $fm=='08' or $fm=='11' or $fm=='14' or $fm=='21' or $fm=='24' or $fm=='25' or $fm=='28' or $fm=='45' or $fm=='46') {
			$totmovstkv[$a3]=$totmovstkv[$a3]-$w0;
			$totmovstkact[$a3]=$totmovstkact[$a3]-$totmovstkv[$a3];
		}elseif($fm=='01' or $fm=='07' or $fm=='12' or $fm=='16' or $fm=='18' or $fm=='19' or $fm=='23' or $fm=='26' or $fm=='27') {
			$totmovstkc[$a3]=$totmovstkc[$a3]+$w0;
			$totmovstkact[$a3]=$totmovstkact[$a3]+$totmovstkc[$a3];
		}elseif($fm=='17') { $totmovstka[$a3]=$totmovstka[$a3]+$w0;
			$totmovstkact[$a3]=$totmovstkact[$a3]+$totmovstka[$a3];
		}
//		$totmovstkact[$a3]=$totmovstkact[$a3]+$totmovstkc[$a3]-$totmovstkv[$a3]+$totmovstka[$a3];
	 } else {

	 }
     if($a3x==$a3) {
	} else {
	calcula_stkini($coneccion,$a3,$diad,$mesd,$anod,$txtalma);
    $snewbuffer=$snewbuffer.$a3.",".$a4.",".number_format($stkini,2).",".number_format($totmovstkv[$a3x],2).",".number_format($totmovstkc[$a3x],2).",".number_format($totmovstka[$a3x],2).",".number_format($totmovstkact[$a3x],2)." \n";
    }
	$a3x=$a3;
	$irow++;

}
  fwrite($ft,$snewbuffer);
  fclose($ft);

 }
?>
</table>
<p><a href="movdia.csv" target="_blank">exportar a excel</a></p>
</body>
</html>
<?php pg_close($coneccion); ?>