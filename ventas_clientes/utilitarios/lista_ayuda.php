<?php
extract($_REQUEST);
	include('../../start.php');
 	include('../facturacion/m_ticketspos.php');
	
	// 1.- Evalua si se ha seleccionado algun Producto
	if($lista ==""){
		//if($Buscar=="Buscar"){
			if($busca=="")$busca = " ";
			$listado = TicketsPosModel::listarArticulos(strtoupper(trim($busca)));
			$registro = $listado['datosarticulos'];
		//}
	}else{
		if($Buscar!="Buscar"){
			$objArticulo= TicketsPosModel::obtenerArticulo(trim($lista),'1');
			$descripcion = $objArticulo[1];
		
	?>
	<script language="JavaScript">
		opener.document.<?php echo $des;?>.value = '<?php echo $descripcion;?>';
		window.close();
		</script>
	<?php }else{
		if($busca=="")$busca = " ";
			$listado = TicketsPosModel::listarArticulos(strtoupper(trim($busca)));
			$registro = $listado['datosarticulos'];
	 	}
	 }?>
	
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<title>Ayuda Artículos</title>
<head>
<script language="JavaScript">
function pasarValorOpener(lista,form,art_cod,des){
	var valor = lista.value;
	try{
		eval("opener.document."+art_cod+".value = '"+valor+"'");
		form.submit();
	}catch(e){
		alert(e);
	}

}
</script>

<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="/sistemaweb/css/sistemaweb.css" type="text/css">
</head>

<body bgcolor="#E8F0EA">
<form name="form1" method="post" action="">
  <table bgcolor="#FFFFFF" width="100%" border="0">
    <tr>
      <td height="17" colspan="4" class="form_cabecera">&nbsp;BUSCAR ART&Iacute;CULO </td>
    </tr>
    <tr>
      <td height="12" colspan="4"></td>
    </tr>
    <tr>
      <td width="112" height="17" class="form_texto">DESCRIPCI&Oacute;N</td>
      <td width="11" height="17" class="form_texto">:</td>
      <td width="174" height="17"><input type="text" name="busca" size="50px"></td>
      <td width="899" height="17"><input type="submit" name="Buscar" value="Buscar">
        <input type="hidden" name="des" value="<?php echo $des;?>">
        <input type="hidden" name="cod" value="<?php echo $cod;?>">
        <input type="hidden" name="consulta" value="<?php echo $consulta;?>"></td>
    </tr>
    <tr>
      <td colspan="4"><select name="lista" size="12">
        <?php	
	 foreach($registro as $reg){
	 echo "<option value='".$reg["art_codigo"]."'>".$reg["art_codigo"]." -- ".$reg["art_descripcion"]."</option>";
	} 

  ?>
      </select></td>
    </tr>
    <tr>
      <td height="12" colspan="4"></td>
    </tr>
    <tr>
      <td><input type="button" name="Seleccionar" value="Seleccionar" onClick="javascript:pasarValorOpener(lista,form1,'<?php echo $cod;?>','<?php echo $des;?>')"></td>
      <td>&nbsp;</td>
      <td>  
      <td>  
    </tr>
  </table>

</form>
</body>
</html>

