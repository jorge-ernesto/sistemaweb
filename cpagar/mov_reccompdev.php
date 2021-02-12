<?php
include "../valida_sess.php";
//include "../config.php";
?><html>
<head>
<title>Sistema de Cuentas por Pagar</title>
  <link rel="stylesheet" href="/sistemaweb/css/sistemaweb.css" type="text/css">
  <link rel="stylesheet" href="/sistemaweb/css/formulario.css" type="text/css">
  <script type="text/javascript" language="JavaScript1.2" src="/sistemaweb/images/stm31.js"></script>
  <script type="text/javascript" language="JavaScript1.2" src="/sistemaweb/js/sistemaweb.js"></script>
</head>
<body leftmargin="0" topmargin="0">
<img src="/sistemaweb/images/logocia.jpeg" width="90" height="45" align="right">
<div id="content">
    <div id="menu"><?php include "/sistemaweb/include/menu.php"; ?></div>
    <div id="content_title">&nbsp;</div>
    <div id="content_body">&nbsp;</div>
    <div id="content_footer">&nbsp;</div>
</div>
<div id="footer">&nbsp;</div>
<iframe id="control" name="control" scrolling="no" src="control.php?rqst=MOVIMIENTOS.RECCOMPDEV&task=RECCOMPDEV" frameborder="1" width="5" height="5"></iframe>
</body>
</html>