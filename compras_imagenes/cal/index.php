<?php
include ("calendario/calendario.php");
?>

<html>
<head>
	<title>Utilizaci�n del calendario</title>
	<script language="JavaScript" src="calendario/javascripts.js"></script>
</head>

<body>
<h1>Uso de la librer�a del calendario</h1>
Para seleccionar una fecha que se colocar�a en un campo de formulario
<br>
<br>
<br>
<form name="fcalen">
Fecha inicio: 
<?php
escribe_formulario_fecha_vacio("fecha1","fcalen");
?>
<br>
<br>
Fecha final:
<?php
escribe_formulario_fecha_vacio("fecha2","fcalen");
?>

</form>

</body>
</html>
	