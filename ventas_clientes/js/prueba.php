
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<script>
function iniciar(iframe){
	var por1 = document.form1.por1.value;
	iframe.location='prueba1.php?por1='+por1;
} 

function verificar(){
	var total = 100;
	var por1 = parseFloat(document.form1.por1.value);
	
	if(por1<total){
	
		setTimeout('iniciar(document.iframe1)',3000);
		
	}
}
</script>
<title>Untitled Document</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<form name="form1" method="post" action="">
  <script language="JavaScript" src="../../clases/percent_bar.js"></script>
  <input type="button" name="Submit" value="button" onClick="verificar();">
  <input type="text" name="por1">
  <input type="button" name="Submit2" value="button" onClick="document.body.style.cursor='wait';">
  <input type="button" name="Submit22" value="button" onClick="document.body.style.cursor='';">
</form>
<iframe name="iframe1"></iframe>
</body>
</html>
