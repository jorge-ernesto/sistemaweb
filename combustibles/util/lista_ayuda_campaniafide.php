<?php
 extract($_REQUEST);
	include('../../start.php');
 	include('../promociones/m_campaniafide.php');
	
	// 1.- Evalua si se ha seleccionado algun Producto
	if($lista ==""){
			if($busca=="")$busca = " ";
			$listado = CampaniaFideModel::listarCampanias(strtoupper(trim($busca)));
			$registro = $listado['datoscampanias'];

	}else{
		if($Buscar!="Buscar"){
			$objArticulo= CampaniaFideModel::listarCampanias(strtoupper(trim($busca)));
			$descripcion = $objArticulo[1];
		
	?>
	<script language="JavaScript">
		//opener.document.<?php echo $des;?>.value = '<?php echo $descripcion;?>';
		window.close();
		</script>
	<?php }else{
		if($busca=="")$busca = " ";
			$listado = CampaniaFideModel::listarCampanias(strtoupper(trim($busca)));
			$registro = $listado['datoscampanias'];
	 	}
	 }?>
	
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<title>Ayuda Campañas Fidelización</title>
<head>
<script language="JavaScript">
function pasarValorOpener(lista,form,cod,des){
//alert("pasando...");
var valor = lista.value;
var texto = lista.options[lista.selectedIndex].text
var tamanio = texto.length;
var descripcion ="";

	/*for(i=tamanio-1;i<=tamanio-1;i--){
		if(texto.charAt(i-1)=='-' && texto.charAt(i)==' ') {
			break;
			}
		else{
		descripcion=  texto.charAt(i)+descripcion;
			
		 }
	}*/
try{
eval("opener.document."+cod+".value = '"+valor+"'");
eval("opener.document."+des+".value = '"+texto+"'");

form.submit();
//window.close();

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
<tr><td height="17" colspan="4" class="form_cabecera">&nbsp;BUSCAR CAMPAÑA FIDELIZACIÓN</td>
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
  <input type="hidden" name="consulta" value="<?php echo $consulta;?>"></td></tr>
<tr>
  <td colspan="4"><select name="lista" size="12">
    <?php	
	 foreach($registro as $reg){
	 echo "<option value='".$reg["id_campana"]."'>".$reg["ch_campana_descripcion"]."</option>";
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
  <td></tr>
</table>
</form>
</body>
</html>

