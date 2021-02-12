<?php
include("../cpagar/store_procedures.php");
pg_connect("dbname=integrado user=postgres");
session_start();

switch($accion){
	case "Filtrar":
		
		//El filtro realmente es borrar de la tabla temporal los articulos que no se quieren
		
		for($i=0;$i<count($items);$i++){
			$cod = $items[$i];
			
			pg_exec(" delete from tmp_etiquetas where art_codigo='$cod' ");
		}
		//$accion = "mostrarEtiquetas";
		
		$q = "select art_descripcion as descripcion,pre_precio_act1  as precio
		, art_codigo as codigo   
		from tmp_etiquetas";
		
		$rs2 = pg_exec($q);  
		
	break;
	
	case "Cancelar":
		$ARR = null;
		$_SESSION["AR_ETI"] = $ARR;
		//unset("AR_ETI");
		unset($AR_ETI);
	break;

	case "Seleccionar":
		pg_exec(" delete from tmp_etiquetas ");
		$q = "insert into tmp_etiquetas(art_descripcion,pre_precio_act1,art_codigo,art_linea)
		select a.art_descripcion ,b.pre_precio_act1  as precio
		, a.art_codigo as codigo   , a.art_linea 
		from int_articulos a, fac_lista_precios b where a.art_codigo=b.art_codigo 
		and trim(a.art_linea)=trim('$linea')";
		//echo $q;
		pg_exec($q);
		
		$q = "select art_descripcion as descripcion,pre_precio_act1  as precio
		, art_codigo as codigo   
		from tmp_etiquetas";
		
		$rs2 = pg_exec($q);  
		  
	break;
	
	case "mostrarEtiquetas":
		
		$q = "select art_descripcion as descripcion,pre_precio_act1  as precio
		, art_codigo as codigo   
		from tmp_etiquetas";
		
		$rs2 = pg_exec($q);  
		
		//Creamos un csv para que lo usen
		pg_exec("create temporary table tmp_etiquetas_csv
		(linea varchar(100), producto varchar(255), precio numeric(10,3))");
		for($i=0;$i<pg_numrows($rs2);$i++){
			$A = pg_fetch_array($rs2,$i);
			$descripcion = $A['descripcion'];
			$precio = $A['precio'];
			for($a=1;$a<=3;$a++){
				pg_exec("insert into tmp_etiquetas_csv(linea, producto, precio)   
				values ('$linea_des','$descripcion',$precio) ");
			}
		}
		//pg_exec(" update tmp_etiquetas_csv set producto=replace(producto,'.','') ");
		exec("rm -f /sistemaweb/tmp/*articulos-etiquetas.csv");
		pg_exec(" copy tmp_etiquetas_csv to '/sistemaweb/tmp/tmp-articulos-etiquetas.csv' 
		with delimiter as ',' ");
		
		exec("echo 'Tipo,Producto,Precio' > /sistemaweb/tmp/articulos-etiquetas.csv");
		
		exec("less /sistemaweb/tmp/tmp-articulos-etiquetas.csv >> /sistemaweb/tmp/articulos-etiquetas.csv");
		
		header("location: /sistemaweb/tmp/articulos-etiquetas.csv");
		
	break;
	
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<script>
function mandarDatos(opcion, form){
	form.accion=opcion;
	form.submit();
}
</script>
<title>Imprimiendo Etiquetas</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script>
function mandarDatos(opcion,form){
	var index = form.linea.selectedIndex;
	form.accion.value=opcion;
	form.linea_des.value=form.linea.options[index].text;
	form.linea_index.value=index;
	
	form.submit();
}
</script>
</head>

<body>
<form name="form1" method="post">
  <font color="#0066CC" face="Arial, Helvetica, sans-serif"><strong>Impresion de Etiquetas:</strong></font><br>
  <font color="#0033CC"><strong>Linea :</strong></font> 
  <select name="linea" >
    <option>Elija una linea</option>
	<?php 	$rs1 = combo("lineas");
		for($i=0;$i<pg_numrows($rs1);$i++){
			$A = pg_fetch_array($rs1,$i);
	?>
    <option value="<?php echo $A[0];?>"><?php echo $A[1];?></option>
    <?php }?>
  </select>
  <strong><font color="#009933" size="4" face="Arial Narrow"><?php echo $linea_des;?></font></strong> 
  <input type="hidden" name="linea_des">
  <?php if($linea_index==""){$linea_index=0;}?>
  <input type="hidden" name="linea_index" value="<?php echo $linea_index;?>">
  <input type="hidden" name="accion">
  <?php $ancho_color = "220";
  $ancho_laser = "250";
  if($imp==""){$imp=220;}
  ?>
  <input type="button" name="btn_seleccionar" value="Seleccionar" onClick="javascript:mandarDatos('Seleccionar',form1);">
  <input type="button" name="btn_filtrar" value="Filtrar" onClick="javascript:mandarDatos('Filtrar',form1);">
  <input type="button" name="btn_generar_etiquetas" value="Generar Etiquetas" onClick="javascript:mandarDatos('mostrarEtiquetas',form1);">
  <br>
  <?php if($accion=="mostrarEtiquetas"){?>
  <table height="141" width="700" border="0" cellpadding="0" cellspacing="0">
  <!--DWLayoutTable-->
  <!--{BEGIN FILA}-->
  <?php
		  
  ?>
  <?php for($i=0;$i<pg_numrows($rs2);$i++) { 
  $A = pg_fetch_array($rs2,$i);
  ?>
  <tr> 
    <td width="243" height="70" valign="top"><table width="225" border="1" cellpadding="0" cellspacing="0">
        <!--DWLayoutTable-->
        <tr> 
          <td width="92" height="20" valign="top"><img height="40" width="89" src="../images/logocia.jpeg">&nbsp;</td>
          <td width="131" valign="top"><strong><font size="+1"><?php echo $linea_des;?></font></strong></td>
        </tr>
        <tr> 
            <td height="65" colspan="2" valign="top">
<div>		<font size="+1"><?php echo $A["descripcion"];?></font></div>
            <div> 
                <div align="right">Precio --------- S/. <font size="+3"><?php echo $A["precio"];?></font><font size="+3"> 
                  </font></div>
            </div></td>
        </tr>
      </table></td>
    <td valign="top"> <table width="225" border="1" cellpadding="0" cellspacing="0">
        <!--DWLayoutTable-->
        <tr> 
          <td width="92" height="20" valign="top"><img height="40" width="89" src="../images/logocia.jpeg">&nbsp;</td>
          <td width="131" valign="top"><strong><font size="+1"><?php echo $linea_des;?></font></strong></td>
        </tr>
        <tr> 
            <td height="65" colspan="2" valign="top"> 
              <div><font size="+1"> 
                <?php echo $A["descripcion"];?></font></div>
            <div> 
                <div align="right">Precio --------- S/ .<font size="+3"> <?php echo $A["precio"];?></font></div>
            </div></td>
        </tr>
      </table></td>
    <td valign="top"><table width="225" border="1" cellpadding="0" cellspacing="0">
        <!--DWLayoutTable-->
        <tr> 
          <td width="93" height="31" valign="top"><img height="40" width="89" src="../images/logocia.jpeg">&nbsp;</td>
            <td width="160" valign="top"><strong><font size="+1"><?php echo $linea_des;?></font></strong></td>
        </tr>
        <tr> 
          <td height="65" colspan="2" valign="top"><div><font size="+1"><?php echo $A["descripcion"];?></font></div>
            <div> 
                <div align="right">Precio --------- S/ .<font size="+3"> <?php echo $A["precio"];?></font></div>
            </div></td>
        </tr>
      </table></td>
  </tr>
  <tr> 
    <?php } ?>
    <!-- {END FILA} -->
    <td height="2"></td>
    <td width="242"></td>
    <td width="279"></td>
  </tr>
</table>

<?php } 
else { ?>
  <table width="757" border="1">
    <tr>
	<td width="48">Eliminar</td>
	<td width="118">Codigo</td>
	  <td width="379">Descripcion</td>
	<td width="161">Linea</td>
</tr>

<?php if($linea!=""){  

for($i=0;$i<pg_numrows($rs2);$i++){
	$A = pg_fetch_array($rs2,$i);
?>
	<tr>
	<td width="48"><input type="checkbox" name="items[]" value="<?php echo $A["codigo"];?>"></td>
	<td width="118"><?php echo $A["codigo"];?></td>
	<td width="379"><?php echo $A["descripcion"];?></td>
	<td width="161"><?php echo $linea_des;?></td>
	</tr>
<?php } //fin del for
 }  //fin del if

} //fin del else
?>

</table>
<script>
	form1.linea.options[form1.linea_index.value].selected=true;
</script>
<?php pg_close(); ?>
</form>
</body>
</html>
