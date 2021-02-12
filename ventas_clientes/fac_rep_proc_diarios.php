<?php
include "../valida_sess.php";
//include "../config.php";
?><html>
<head>
<title>Sistema de Cuentas x Cobrar - Aplicaciones</title>
  <link rel="stylesheet" href="/sistemaweb/css/sistemaweb.css" type="text/css">
  <link rel="stylesheet" href="/sistemaweb/css/formulario.css" type="text/css">
  <script type="text/javascript" language="JavaScript1.2" src="/sistemaweb/images/stm31.js"></script>
  <script type="text/javascript" language="JavaScript1.2" src="js/sisfacturacion.js"></script>
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
<iframe id="control" name="control" scrolling="no" src="control.php?rqst=FACTURACION.REP_PROC_DIA&task=REP_PROC_DIA" frameborder="1" width="5" height="5"></iframe>
</body>
</html>