<?php
include "../valida_sess.php";
include "../config.php";
?><html>
<head>
<title>Sistema de Ventas - Facturaciones</title>
  <link rel="stylesheet" href="/sistemaweb/css/sistemaweb.css" type="text/css">
  <link rel="stylesheet" href="/sistemaweb/css/formulario.css" type="text/css">
  <script language="JavaScript" src="js/sisfacturacion.js"></script>
  <script language="JavaScript" src="js/ventas.js"></script>
</head>
<body leftmargin="0" topmargin="0">
<img src="/sistemaweb/images/logocia.jpeg" width="90" height="45" align="right">
<div id="content">
    <div id="menu"><?php include "include/menu.inc.php"; ?></div>
    <div id="content_title">&nbsp;</div>
    <div id="content_body">&nbsp;</div>
    <div id="content_footer">&nbsp;</div>
</div>
<div id="footer">&nbsp;</div>
<iframe id="control" name="control" scrolling="no" src="control.php?rqst=FACTURACION.REIMPRESION" frameborder="1" width="10" height="10"></iframe>
</body>
</html>