<?php

include("/sistemaweb/valida_sess.php");
include("config.php");
include("inc_top.php");
include("../functions.php");
//include("../valida_sess.php");
include("store_procedures.php");
include("funciones.php");

if($action=="reporte"){
	pg_exec(" truncate tmp_ventas_especiales");
	switch ($ordx){
		case "total":
			$cho1="checked";
		break;
		case "almacen":
			$cho2="checked";
		break;
		case "producto":
			$cho3="checked";
		break;
	}
	
	switch ($cond){
		case "linea":
			$chc1="checked";
		break;
		case "tipo":
			$chc2="checked";
		break;
		case "codigo":
			$chc3="checked";
		break;
	}
	
	switch($detres){
		case "resumido": 
			$chdr1 = "checked";
			$com_art1 ="<!--";
			$com_art2 = "-->";
		break;
		case "detallado":
			$chdr2 = "checked";
			
		break;
	}
	
	if($conigv=="conigv") {
			$campo_valor = "total";
			$ch_conigv = "checked";
	}else{  $campo_valor = "importe"; }

	switch($cond){
		case "linea":
			$AR = $linea;
		break;
		case "tipo":
			$AR = $tipo;
		break;
		case "codigo":
			$AR = $codigo;
		break;
	}
	
	$fechad = $diad."/".$mesd."/".$anod;
	$fechaa = $diaa."/".$mesa."/".$anoa;
}
if($ordx!="almacen"){
	$cod_almacen="";
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

function mostrarFila(fila){
showLayer(fila);
}

function ocultarFila(fila){
hideLayer(fila);
}

function filtrarLista(form1,cond){
	var condicion = cond.value;
	var last = form1.cond_last_select.value;
	var arcond = new Array();
	arcond[0] = 'linea';
	arcond[1] = 'tipo';
	arcond[2] = 'codigo';
	//alert ('La condicion es '+condicion);
	//alert ('El ultimo seleccionado es '+last);
	if(last==condicion){
		//alert('Ya esta seleccionado');
		cond.checked=false;
		form1.cond_last_select.value = '';
		for(i=0;i<3;i++){
			ocultarFila(arcond[i]);
			ocultarFila('fila_condicion');	
		}
	}else{
		//alert('Esta opcion no estaba seleccionada');
		form1.cond_last_select.value = condicion;
			for(i=0;i<3;i++){
				if(arcond[i]!=condicion){
				ocultarFila(arcond[i]);
				ocultarFila('fila_condicion');	
				}
			}
		mostrarFila('fila_condicion');
		mostrarFila(condicion);
	
	}


}

function filtrarLista2(form1,cond){
	var condicion = cond.value;
	var last = form1.cond_last_select.value;
	var arcond = new Array();
	arcond[0] = 'almacen';
	//alert ('La condicion es '+condicion);
	//alert ('El ultimo seleccionado es '+last);
	if(last==condicion){
		//alert('Ya esta seleccionado');
		cond.checked=false;
		form1.cond_last_select.value = '';
		for(i=0;i<1;i++){
			ocultarFila(arcond[i]);
				
		}
	}else{
		//alert('Esta opcion no estaba seleccionada');
		form1.cond_last_select.value = condicion;
			for(i=0;i<1;i++){
				if(arcond[i]!=condicion){
				ocultarFila(arcond[i]);
					
				}
			}
		
		mostrarFila(condicion);
	
	}


}
</script>
<title>Ventas Especiales Sin Impuesto</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<form name="form1" method="post" action="generar_ventasesp_sinimp2.php?action=reporte">
  <div align="center"><strong>Ventas Especiales Sin Impuesto</strong><br>
  </div>
  <table width="854" border="1" bordercolor="#cccc99">
    <tr> 
      <td width="196" height="41"> <div align="right">Desde: 
          <input type="text" name="diad" size="4" maxlength="2" onKeyUp="javascript:( validarNumeroEntero(this) , validarDia(diad) )" value='<?php echo $diad;?>'>
          / 
          <input type="text" name="mesd" size="4" maxlength="2" onKeyUp="javascript:validarNumeroEntero(this) , validarMes(this)" value='<?php echo $mesd;?>'>
          / 
          <input type="text" name="anod" size="6" maxlength="4" onKeyUp="javascript:validarNumeroEntero(this) , validarYear(this)" value='<?php echo $anod;?>'>
          <strong><br>
          dia / mes / a&ntilde;o</strong> </div></td>
      <td colspan="3">Hasta: 
        <input type="text" name="diaa" size="4" maxlength="2" onKeyUp="javascript:validarNumeroEntero(this) , validarDia(this)" value='<?php echo $diaa;?>'>
        / 
        <input type="text" name="mesa" size="4" maxlength="2" onKeyUp="javascript:validarNumeroEntero(this) , validarMes(this)" value='<?php echo $mesa;?>'>
        / 
        <input type="text" name="anoa" size="6" maxlength="4" onKeyUp="javascript:validarNumeroEntero(this), validarYear(this)" value='<?php echo $anoa;?>'> 
        <br> <strong> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;dia 
        / mes / a&ntilde;o</strong> </td>
      <td width="373">&nbsp;</td>
    </tr>
    <tr> 
      <td><div align="right">Detallado por d&iacute;a :</div></td>
      <td colspan="3"><input type="checkbox" name="xdia" value="xdia"></td>
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td><div align="right">Totales con IGV :</div></td>
      <td colspan="3"><input type="checkbox" name="conigv" value="conigv" <?php echo $ch_conigv;?>></td>
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td><div align="right"> Forma del Reporte :</div></td>
      <td colspan="3"> <input type="radio" name="detres" value="resumido" <?php echo $chdr1;?>>
        Resumido 
        <input type="radio" name="detres" value="detallado" <?php echo $chdr2;?>>
        Detallado </td>
      <td>&nbsp;</td>
    </tr>
    <tr > 
      <td><div align="right"><strong>Ordenado por:</strong></div></td>
      <td width="71" bordercolor="#006666"><div align="left"> 
          <input type="radio" name="ordx" value="total" onDblClick="javascript: this.checked=false;" <?php echo $cho1;?> >
          Total </div></td>
      <td width="94" bordercolor="#006666"><input type="radio" name="ordx" value="almacen" onClick="javascript: filtrarLista2(form1,this);" <?php echo $cho2;?>>
        Almacen</td>
      <td width="86" bordercolor="#006666"><input type="radio" name="ordx" value="producto" onDblClick="javascript: this.checked=false;" <?php echo $cho3;?>>
        Producto</td>
      <td id="almacen"><input type="hidden" name="cond_last_select" value="algo">
        <select name="cod_almacen">
		<?php $rs = combo("almacenes"); 
		for($i=0;$i<pg_numrows($rs);$i++){
		$A = pg_fetch_array($rs,$i);
		print "<option value='$A[0]'>$A[1]</option>";
		}
		?>
        </select></td>
    </tr>
    <tr> 
      <td><div align="right"><strong>Condici&oacute;n:</strong></div></td>
      <td bordercolor="#006666"><input type="radio" name="cond" value="linea" onClick="javascript: filtrarLista(form1,this);" <?php echo $chc1;?>>
        Linea </td>
      <td bordercolor="#006666"><input type="radio" name="cond" value="tipo"  onClick="javascript: filtrarLista(form1,this);" <?php echo $chc2;?>>
        Tipo</td>
      <td bordercolor="#006666"><input type="radio" name="cond" value="codigo" onClick="javascript: filtrarLista(form1,this);" <?php echo $chc3;?>>
        C&oacute;digo </td>
      <td><input type="submit" name="Submit" value="Aceptar"></td>
    </tr>
    <tr id="fila_condicion"> 
      <td id="linea"><div align="right">Linea<br>
          <select name="linea[]" size="7" multiple>
            <?php $rs = combo("lineas");
		for($i=0;$i<pg_numrows($rs);$i++){
		$A = pg_fetch_array($rs,$i);
		print "<option value='$A[0]'>$A[1]</option>";
		}?>
          </select>
        </div></td>
      <td colspan="3" id="tipo"> <div align="center">Tipo<br>
          <select name="tipo[]" size="7" multiple>
            <?php $rs = combo("tipos");
		for($i=0;$i<pg_numrows($rs);$i++){
		$A = pg_fetch_array($rs,$i);
		print "<option value='$A[0]'>$A[1]</option>";
		}?>
          </select>
        </div></td>
      <td id="codigo">C&oacute;digo<br> <select name="codigo[]" size="7" multiple>
          <?php $rs = combo("articulos");
		/*for($i=0;$i<pg_numrows($rs);$i++){
		$A = pg_fetch_array($rs,$i);
		print "<option value='$A[0]'>$A[1]</option>";
		}*/?>
        </select> </td>
    </tr>
  </table>
  <!--AQUI EMPIEZA LA TABLA--><?php if($action=="reporte"){?>
  Del fechad hasta fechaa <?php echo $cod_almacen;?>
  <br>
  <table  border="1" bordercolor="#cccc99">
    <tr> 
      <!-- <?php $rs_alma = combo("almacenes");?>-->
      <td width="96" height="17" bordercolor="#006666"><strong><font size="-4" face="Arial, Helvetica, sans-serif">Descripci&oacute;n:</font></strong></td>
      <!--BEGIN FILA1   
	 <?php  for($i=0;$i<pg_numrows($rs_alma);$i++){
	 $A = pg_fetch_array($rs_alma,$i);
	 $almacen = $A[1];
	 print '
	 ?> -->
      <td colspan="2" bordercolor="#006666" ><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">'.$almacen.'</font></div></td>
      <!-- <?php ';} ?>   END FILA1 -->
      <td colspan="2" bordercolor="#006666"><font size="-4" face="Arial, Helvetica, sans-serif">TOTAL:</font></td>
    </tr>
    <tr bordercolor="#006666"> 
      <td height="17"><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
      <!-- BEGIN FILA2 <?php 
	  for($i=0;$i<pg_numrows($rs_alma);$i++){
	  print '
	  ?>
	  -->
      <td width="50"> <div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">cantidad</font></div></td>
      <td width="71"> <div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">'.$campo_valor.'</font></div></td>
      <!-- END FILA2 <?php ';} ?>-->
      <td width="38" bordercolor="#006666"><font size="-4" face="Arial, Helvetica, sans-serif">cantidad</font></td>
      <td width="33" bordercolor="#006666"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $campo_valor;?></font></div></td>
    </tr>
    <!--BEGIN FILA DE LINEAS-->
    <!-- <?php $rs_linea = lineas_venesp($cod_almacen,$ordx,$cond,$AR,$fechad,$fechaa,$xdia);  
	for($j=0;$j<pg_numrows($rs_linea);$j++){   
	 $L = pg_fetch_array($rs_linea,$j);
	 $cod_linea = $L[0];
	 $linea = $L[1];
	 $rs_art = articulos_venesp($cod_linea,$cod_almacen,$ordx,$xdia);
	
	 ?> -->
    <tr bordercolor="#006666"> 
      <td height="15"><font color="#0033CC" size="-4" face="Arial, Helvetica, sans-serif"><strong>Linea 
        :&nbsp;<?php echo $cod_linea.": ".$linea;?></strong></font></td>
      <td colspan="2" bordercolor="#006666"> <div align="right"></div></td>
      <!--<td colspan="2" bordercolor="#006666"></td>-->
    </tr>
    <?php // BEGIN ARTICULO
	for($n=0;$n<pg_numrows($rs_art);$n++){
	 $ART = pg_fetch_array($rs_art,$n);
	 $articulo = $ART[1];
	 $cod_articulo = $ART[0];
	 if($xdia=="xdia"){ $art_fecha = $ART[2];; }
	 $TTL_cant = 0;
	 $TTL_val  = 0;
		
	?>
    <?php echo $com_art1;?> 
    <?php // } ?>
    <?php echo $com_art2;?> 
    <!-- FIN ARTICULO-->
    <!-- Para el detalle por fecha-->
    <?php if($xdia=="xdia"){ ?>
    <?php } ?>
    <?php } //if del detalle de fechas?>
    <!-- Fin del detalle por fecha-->
    <!-- -->
    <!-- experimental-->
    <!-- experimental-->
    <!-- <?php 
	$TTG_cant = $TTG_cant + $TTL_cant;
	$TTG_val  = $TTG_val + $TTL_val;	
	} ?>-->
    <!-- FIN DE LINEAS-->
  </table>
  <!--FIN DE LA TABLA --><?php } ?>
  <p>&nbsp;</p>
  <p>&nbsp;</p>
  <table width="761" border="0">
    <tr> 
      <td width="64"><strong><font size="-4" face="Arial, Helvetica, sans-serif">Descripci&oacute;n:</font></strong></td>
      <td colspan="2"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">almacen</font></div></td>
      <td width="49"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;</font></div></td>
      <td width="53"><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
      <td width="38"><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
      <td width="451"><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
    </tr>
    <tr> 
      <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
      <td width="38"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">cantidad</font></div></td>
      <td width="38"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">importe</font></div></td>
      <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;</font></div></td>
      <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
      <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
      <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
    </tr>
    <tr> 
      <td height="15"><font size="-4" face="Arial, Helvetica, sans-serif">Linea 
        :&nbsp;</font></td>
      <td colspan="2"><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"></font></div></td>
      <td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"></font></div></td>
      <td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"></font></div></td>
      <td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"></font></div></td>
      <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
    </tr>
    <tr> 
      <td height="21"><font size="-4" face="Arial, Helvetica, sans-serif">articulo</font></td>
      <td colspan="2"><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;</font></div></td>
      <td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;</font></div></td>
      <td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;</font></div></td>
      <td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;</font></div></td>
      <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
    </tr>
  </table>
  <p>&nbsp;</p>
  <p><br>
    <br>
    <?php
  
  ?>
  </p>
</form>
</body>
<script>
if(!document.form1.cond.checked){
ocultarFila('fila_condicion');
}
ocultarFila('almacen');
</script>
</html>
