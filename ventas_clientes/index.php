<?php
include "../include/valida_sess.php";
//include "../config.php";
?>
<html>
<head>
<title>Sistema de Ventas - Maestro de Tarjetas Magneticas</title>
  <link rel="stylesheet" href="/sistemaweb/css/sistemaweb.css" type="text/css">
  <link rel="stylesheet" href="/sistemaweb/css/formulario.css" type="text/css">
  <script type="text/javascript" language="JavaScript1.2" src="/sistemaweb/images/stm31.js"></script>
  <script type="text/javascript" language="JavaScript1.2" src="/sistemaweb/ventas_clientes/js/sisventascli.js"></script>
  <script type="text/javascript" language="JavaScript1.2" src="/sistemaweb/ventas_clientes/js/validacion.js"></script>
</head>
<body leftmargin="0" topmargin="0">
<div id="logo" style="position:absolute; right:10px;"><img src="/sistemaweb/images/logocia.jpeg" height="45" width="90"></div>
<div id="header" align="center">&#160;</div>
<div id="content">
  <div id="menu"><?php include("include/menu.inc.php"); ?></div>
  <br>
  <div id="content_title" >&#160;</div>
  <div id="content_body" >&#160;</div>
  <div id="content_footer">&#160;</div>
</div>
<div id="footer">&#160;</div>
<iframe id="control" name="control" scrolling="no" src="control.php" width="10" height="10"></iframe>
</body>
</html>