<html>
<link rel="stylesheet" href="/sistemaweb/sistemaweb.css" type="text/css">
<head>
<title>Sistema de Ventas</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<script type="text/javascript" language="JavaScript1.2" src="/sistemaweb/images/stm31.js">
</script>

</head>
<body leftmargin="0" topmargin="0">
<center>Usuario: <?php echo $usuario->obtenerUsuario(); ?>, Almacen: <?php echo $usuario->obtenerAlmacenActual(); ?></center>
<img src="/sistemaweb/images/logocia.jpeg" width="90" height="40" align="right">
<?php
include "/sistemaweb/ventas_clientes/include/menu.inc.php";
