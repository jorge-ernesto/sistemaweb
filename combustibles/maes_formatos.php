<?php
include "../valida_sess.php";
//include "../config.php";
?><html>
<head>
<title>Guia Rapida</title>
  <link rel="stylesheet" href="/acosa/css/acosa.css" type="text/css">
  <link rel="stylesheet" href="/acosa/css/formulario.css" type="text/css">
  <script type="text/javascript" language="JavaScript1.2" src="/acosa/images/stm31.js"></script>
  <script type="text/javascript" language="JavaScript1.2" src="/acosa/combustibles/js/guia_rapida_2004.js"></script>
  <script type="text/javascript" language="JavaScript1.2" src="/acosa/combustibles/js/validacion.js"></script>
</head>
<body leftmargin="0" topmargin="0">
<img src="/acosa/images/logocia.jpeg" width="90" height="45" align="right">
<div id="content">
    <div id="menu"><?php include "include/menu.inc.php"; ?></div>
    <div id="content_title">&nbsp;</div>
    <div id="content_body">&nbsp;</div>
    <div id="content_footer">&nbsp;</div>
</div>
<div id="footer">&nbsp;</div>
<iframe id="control" name="control" scrolling="no" src="control.php?rqst=MAESTROS.GUIARAPIDA&task=GUIARAPIDA" frameborder="1" width="10" height="10"></iframe>
</body>
</html>