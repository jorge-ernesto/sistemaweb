<?php
include("/sistemaweb/valida_sess.php");
include("config.php");
include("inc_top.php");
include("../functions.php");
//include("../valida_sess.php");
//include("store_procedures.php");


function reporte_revdia_transaciones_2($fechad, $fechaa, $cod_almacen, $periodo, $mesa, $modo,$tipo, $cod_art, $add_sql){
	//--(ret,fechadd,fechaa,almacen,periodo,modo)

	if(strlen(trim($cod_art))==0 && $add_sql==" and ( TD='B' or TD='F' or TD='N' or TD='A' )"){
			//echo "<br>HOLA SIN ".$cod_art;
			$q = "select VEN_FN_REVISION_TICKETS('ret', '$fechad', '$fechaa', '$cod_almacen', '$periodo' , '$mesa', '$modo','$tipo')";
			//echo "AQUI TA CON TODOS LOS CAMPOS.: y sin articulo <BR>".$q;
			if(existeError($q,$fechad,$fechaa)){
				pg_exec("begin");
				pg_exec($q);

				$rs = pg_exec("fetch all in ret");
				pg_exec("end");
			}else {
			}
	}else{//echo "Ah ocurrido u error";
		//echo "HOLA CON ARTICULO";
		//name="modo" value="historico"
		if($cod_art){ $add_articulo = "and p.codigo='$cod_art'"; }

		if($modo=='actual')
		{
			$q = "select p.td, p.trans, to_char(p.fecha,'dd/mm/yyyy HH24:mi:ss')
					, CAST(p.codigo as varchar), a.art_descbreve, p.cantidad, p.importe

					,tm , precio, tarjeta, odometro, placa , caja, pump

				from int_articulos a, pos_transtmp p
				where a.art_codigo=p.codigo and p.trans is not null
					and p.es='$cod_almacen' and p.tipo='$tipo'

					$add_articulo $add_sql

				order by p.dia";
			//echo "AQUI LOS CAMPOS DISCRIMINADOS o/y sin articulo en modo actual <BR>".$q;
		$rs = pg_exec($q);
		}
		else if($modo='historico'){
			$q = "
				select p.td,p.trans,to_char(p.fecha,'dd/mm/yyyy HH24:mi:ss'),p.pump
				, a.art_descbreve ,p.cantidad,p.importe

				,tm , precio, tarjeta, odometro, placa, caja, pump

				from pos_trans".$periodo.$mesa." p,
				int_articulos a where a.art_codigo=p.codigo
				and p.dia >= to_date('$fechad','dd/mm/yyyy') and p.es = '$cod_almacen' and p.tipo='$tipo'

					$add_articulo $add_sql

				and p.dia <= to_date('$fechaa','dd/mm/yyyy') order by p.dia
			";
			//echo "AQUI TA LOS CAMPOS discriminados en HISTORICO.: o/y sin articulo <BR>".$q;
		$rs = pg_exec($q);
		}
	}
	return $rs;
}


if(strlen(trim($cod_articulo))>0 && strlen(trim($cod_articulo))!=8)
{
	$cod_articulo=completarCeros($cod_articulo,13,"0");
}









if($cod_almacen==""){$cod_almacen=$almacen;}
if($diad==""){$diad="01";}
if($diaa==""){$diaa="30";}
if($mesa ==""){$mesa = date("m");}
//PARA LLENAR EL COMBO
$rs1 = pg_exec("select ch_almacen ,ch_nombre_almacen from inv_ta_almacenes where ch_clase_almacen='1'
 order by ch_nombre_almacen ");
//PARA PONER EL VALOR DEFAULT DEL COMBO A LA ESTACION SELECCIONADA

$rs2 = pg_exec("select ch_almacen ,ch_nombre_almacen from inv_ta_almacenes where ch_clase_almacen='1'
		and trim(ch_almacen)=trim('$cod_almacen') order by ch_nombre_almacen");

$A = pg_fetch_array($rs2,0);
$sucursal_val = $A[0];
$sucursal_dis = $A[1];

if($modo!=""){
$fechad = $diad."/".$mesa."/".$periodo;
$fechaa = $diaa."/".$mesa."/".$periodo;


/*
VALORES POR DEFECTO ...
*/





//SACAMOS EL REPORTE
if($c_boleta) { $add_sql = $add_sql." or TD='B'"; } 
if($c_factura) { $add_sql = $add_sql." or TD='F'"; } 
if($c_despacho) { $add_sql = $add_sql." or TD='N'"; } 
if($c_afericiones) { $add_sql = $add_sql." or TD='A'"; } 
if($c_devoluciones) { $add_sql = $add_sql." and TM='D'"; } 

if(strlen(trim($add_sql))>0)
{
	$add_sql = " and ( ".substr($add_sql,4,strlen($add_sql))." )";
}

/*if($add_sql==" and ( TD='B' or TD='F' or TD='N' or TD='A' )")
{
	echo $add_sql;
}*/
$rsM = reporte_revdia_transaciones_2($fechad, $fechaa, $cod_almacen, $periodo, $mesa, $modo, "M", $cod_articulo, $add_sql);
$rsC = reporte_revdia_transaciones_2($fechad, $fechaa, $cod_almacen, $periodo, $mesa, $modo, "C", $cod_articulo, $add_sql);

}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<script>
//Gets a handle to all style parts of an object using ID to access it
function getObj(name, nest) {
if (document.getElementById){
return document.getElementById(name).style;
}else if (document.all){
return document.all[name].style;
}else if (document.layers){
if (nest != ''){
return eval('document.'+nest+'.document.layers["'+name+'"]');
}
}else{
return document.layers[name];
}
}

//Hide/show layers functions
function showLayer(layerName, nest){
var x = getObj(layerName, nest);
x.visibility = "visible";
}

function hideLayer(layerName, nest){
var x = getObj(layerName, nest);
x.visibility = "hidden";
}

function mostrarHis(){
showLayer('his1');
showLayer('his2');
showLayer('his3');
showLayer('his4');
showLayer('his5');
showLayer('his6');
}

function ocultarHis(){
hideLayer('his1');
hideLayer('his2');
hideLayer('his3');
hideLayer('his4');
hideLayer('his5');
hideLayer('his6');
}
</script>




<script>
function mostrarDespacho(num_trans,fecha_trans){
	var url = '/sistemaweb/ventas_clientes/reimpresiones.php?nro_trans='+num_trans+'&fecha='+fecha_trans;
	window.open(url,'reimpresion','width=600,height=800,scrollbars=yes,menubar=no,left=100,top=20');
}
</script>




<title>Revision Diaria de Transacciones</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body >
<marquee>
REVISION DIARIA DE TRANSACCIONES 
</marquee>
<form name="form1" method="post" action="">
  <div align="center">
    <table width="577" border="0" cellpadding="0" cellspacing="0">
      <tr valign="bottom"> 
        <td colspan="6"> <div align="left"> 
            <p>Almacen: 
              <select name="cod_almacen"><div align="center">
                <?php
				if($cod_almacen!=""){ print "<option value='$sucursal_val' selected>$sucursal_val -- $sucursal_dis</option>"; }
				for($i=0;$i<pg_numrows($rs1);$i++){
					$B = pg_fetch_row($rs1,$i);
					print "<option value='$B[0]' >$B[0] -- $B[1]</option>";
				}
			  ?>
              </select>
            </p>
          </div></td>
      </tr>
      <tr> 
        <td width="94">Tip. Doc .:</td>
        <td width="129"><input name="c_boleta" type="checkbox" value="B" <?php if($c_boleta) { echo "checked";} ?> >
          Boleta</td>
        <td width="65">Historico</td>
        <td width="133"><input type="radio" name="modo" value="historico" onClick="javascript:mostrarHis();" <?php if($modo=="historico"){echo "checked";}?>></td>
        <td width="64" id="his1">Periodo : </td>
        <td width="92" id="his2"><input type="text" name="periodo" value="<?php echo $periodo;?>" size="8" maxlength="4"></td>
      </tr>
      <tr> 
        <td><input type="checkbox" name="c_devoluciones" value="D" <?php if($c_devoluciones){ echo "checked";} ?>>
          Devoluciones</td>
        <td><input name="c_factura" type="checkbox" value="F" <?php if($c_factura){ echo "checked";} ?>>
          Factura</td>
        <td>Actual </td>
        <td><input type="radio" name="modo" value="actual"  onClick="javascript:ocultarHis();" <?php if($modo=="actual"){echo "checked";}?> ></td>
        <td id="his3">Mes :</td>
        <td id="his4"><input type="text" name="mesa" size="4" maxlength="2" value="<?php echo $mesa; ?>"></td>
      </tr>
      <tr> 
        <td><input name="c_afericiones" type="checkbox" value="A" <?php if($c_afericiones){ echo "checked";} ?> >
          Afericiones</td>
        <td><input name="c_despacho" type="checkbox" value="N" <?php if($c_despacho){ echo "checked";} ?> >
          Nota Despacho</td>
        <td>Articulo </td>
        <td><input type="text" name="cod_articulo" size="20" maxlength="13" value="<?php echo $cod_articulo;?>"></td>
        <td id="his5"> <div align="left">Desde el:</div></td>
        <td id="his6"> <input type="text" name="diad" size="4" maxlength="2" value="<?php echo $diad;?>">
          al 
          <input type="text" name="diaa" size="4" maxlength="2" value="<?php echo $diaa;?>"> 
        </td>
      </tr>
      <tr> 
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td id="his5">&nbsp;</td>
        <td id="his6">&nbsp;</td>
      </tr>
      <tr valign="bottom"> 
        <td height="34" colspan="6"><input type="submit" name="Submit" value="Generar Reporte"></td>
      </tr>
    </table>
  </div>

  <div align="left"><br>
    <strong><font size="2" face="Arial, Helvetica, sans-serif"><a href="#" onClick="javascript:window.open('reporte_revisiondia_transacciones.php?fechaa=<?php echo $fechaa;?>&fechad=<?php echo $fechad;?>&v_mes=<?php echo $mesa; ?>&cod_almacen=<?php echo $cod_almacen;?>&periodo=<?php echo $periodo;?>&modo=<?php echo $modo;?>&mes=<?php echo $mesa;?>&sucursal_dis=<?php echo $sucursal_dis;?>&cod_articulo=<?php echo $cod_articulo;?>&c_boleta=<?php echo $c_boleta;?>&c_factura=<?php echo $c_factura;?>&c_despacho=<?php echo $c_despacho;?>&c_afericiones=<?php echo $c_afericiones;?>&c_devoluciones=<?php echo $c_devoluciones;?>','miwin','width=800,height=350,scrollbars=yes,menubar=yes,left=0,top=0');">Exportar
    Reporte</a>
	<table width="1000" border="1" cellpadding="0" cellspacing="0">
      <tr>
		<td></td>
        <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">TM</font></div></td>

        <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">TD</font></div></td>
        <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">TRAN</font></div></td>
        <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">FECHA</font></div></td>
        <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">DESCRIPCION</font></div></td>
        <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">CANTIDAD</font></div></td>

        <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">PRECIO</font></div></td>

        <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">IMPORTE</font></div></td>

        <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">TARJETA</font></div></td>
        <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">ODOMETRO</font></div></td>
		<td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">PLACA</font></div></td>
		<td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">COD-CLI</font></div></td>
		<td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">USUARIO</font></div></td>
		<td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">CAJA</font></div></td>
		<td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">LADO</font></div></td>

      </tr>
      <!--<?php
	  $total_imp = 0;
	  for($k=1;$k<=2;$k++){
	  if($k==1){$rs = $rsM;}
	  if($k==2){$rs = $rsC;}
	  for($i=0;$i<pg_numrows($rs);$i++){
	  $A = pg_fetch_array($rs,$i);


	  $total_cant = $total_cant + $A[5];
 	  $total_imp = $total_imp + $A[6];


	  $url = "/sistemaweb/ventas_clientes/reimpresiones.php?nro_caja=".$A[12]."&nro_trans=".$A[1]."&dia_trans=".substr($A[2],0,10);

	  $sql_detalle = "SELECT codcli FROM pos_fptshe1 WHERE numtar='".$A[9]."'";
      $xsql = pg_exec($sql_detalle);
	  $codigo_cliente = trim(pg_result($xsql,0,0));

      $xsql = pg_exec("select cli_contacto from int_clientes where cli_codigo='".$A[12]."'");
	  $contacto = trim(pg_result($xsql,0,0));


	  print '
	  ?> -->
		<tr bgcolor="#CCCC99" onMouseOver=this.style.backgroundColor="#FFFFCC"; this.style.cursor="hand"; onMouseOut=this.style.backgroundColor="#CCCC99";
		onClick=window.open("'.$url.'","reimpresion","width=600,height=400,scrollbars=yes,menubar=no,left=60,top=20");
		 >


        <td><div align="center"><input type="radio" name="mclave" value="'.$A[1].$A[2].'"></div></td>

        <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">'.$A[7].'</font></div></td>

        <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">'.$A[0].'</font></div></td>
        <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">'.$A[1].'</font></div></td>
        <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">'.$A[2].'</font></div></td>
        <td><div align="left"><font size="-4" face="Arial, Helvetica, sans-serif">'.$A[3].'- '.$A[4].'</font></div></td>
        <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">'.$A[5].'</font></div></td>

        <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">'.$A[8].'</font></div></td>

        <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">'.$A[6].'</font></div></td>

        <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$A[9].'</font></div></td>
        <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$A[10].'</font></div></td>
        <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$A[11].'</font></div></td>

	    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$codigo_cliente.'</font></div></td>
		<td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$contacto.'</font></div></td>

		<td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$A[12].'</font></div></td>
		<td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$A[13].'</font></div></td>
      </tr>
     <!-- <?php  '
	 ;}
	 	} ?> -->
      <tr>
        <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;</font></td>	
		<td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;</font></td>	
        <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
        <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
        <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
        <td align="right"><font size="-4" face="Arial, Helvetica, sans-serif">TOTAL .:  </font></td>
        <td align="center"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo number_format($total_cant,2); ?></font></td>
        <td align="center"><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
		<td align="center"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo number_format($total_imp,2); ?></font></td>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
      </tr>
    </table>
    <br>
  </div>
</form>
<?php if($modo=="actual"){
?>
<script language="JavaScript">
	ocultarHis();
</script>
<?php }?>
</body>
</html>