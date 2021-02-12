<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<script language="JavaScript">
function agregar(combo){
	var opcion = document.createElement("OPTION");	
	combo.options.add(opcion);
	if(navigator.appName=="Microsoft Internet Explorer"){
		//alert("Carajo");
	}
	opcion.value = 'Carajo';
	opcion.innerText = 'Carajo';
	combo.options.add(opcion);
	
}
</script>
<title>Untitled Document</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<form name="form1" method="post" action="">
  <select name="combo">
  </select>
  <input type="button" name="Submit" value="Submit" onClick="agregar(combo);">
</form>
</body>
</html>
