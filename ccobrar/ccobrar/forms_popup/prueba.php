<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<script>
function prueba(form){
		//alert(form.clave[0].value);
		//alert('sss');
		form.action="ccob_form_precancelacion_iframe.php";
		form.target="ifr1";
		form.submit();
}
</script>
<title>Untitled Document</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<form name="form1" method="post" action="" >
  <table width="75%" border="1">
    <tr>
      <td width="19%"><input type="checkbox" name="clave[]" value="uno"></td>
      <td width="30%">&nbsp;</td>
      <td width="18%">&nbsp;</td>
      <td width="11%">&nbsp;</td>
      <td width="22%">&nbsp;</td>
    </tr>
    <tr>
      <td><input type="checkbox" name="clave[]" value="dos"></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td><input type="checkbox" name="clave[]" value="tres"></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td><input type="checkbox" name="clave[]" value="cuatro"></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td><input type="checkbox" name="clave[]" value="cinco"></td>
      <td>&nbsp;</td>
      <td><input type="button" name="Submit" value="Submit" onClick="javascript:prueba(form1);"></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
  </table>
  <br>
  <iframe style="overflow:hidden" name="ifr1" height="50" width="50"></iframe>
</form>
</body>
</html>
