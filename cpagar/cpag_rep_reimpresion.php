<?php
include "../valida_sess.php";
//include "../config.php";
?><html>
<head>
<title><?php echo $usuario->obtenerNombreSistemaActual(); ?> - Reimpresion de Cintillos</title>
  <link rel="stylesheet" href="/sistemaweb/css/sistemaweb.css" type="text/css">
  <link rel="stylesheet" href="/sistemaweb/css/formulario.css" type="text/css">
  <script type="text/javascript" language="JavaScript1.2" src="js/sismaestros.js"></script>
  <script language="JavaScript" src="/sistemaweb/clases/overlib_mini.js"></script></head>
  <script language="JavaScript" src="/sistemaweb/compras/validacion.js"></script>
  <script language="JavaScript" src="/sistemaweb/compras/valfecha.js"></script>
  <script language="JavaScript" src="/sistemaweb/js/sistemaweb.js"></script>

<div id="content">
    <div id="menu"><?php include "/sistemaweb/include/menu.php"; ?></div>
    <div id="content_title">&nbsp;</div>
    <div id="content_body">&nbsp;</div>
    <div id="error_detalle">&nbsp;</div>
    <div id="content_footer">&nbsp;</div>
</div>
<div id="footer">&nbsp;</div>
<iframe id="control" name="control" scrolling="no" src="control.php?rqst=REPORTES.REIMPRESION&task=parametros" frameborder="1" width="10" height="10"></iframe>
</body>
</html>
