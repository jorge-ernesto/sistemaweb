<?php
include "../valida_sess.php";
//include "../config.php";
?>
<html>
<head>
<title>Sistema de Cuentas x Cobrar - Obtener Cancelaciones</title>
  <link rel="stylesheet" href="/acosa/css/acosa.css" type="text/css">
  <link rel="stylesheet" href="/acosa/css/formulario.css" type="text/css">
  <script type="text/javascript" language="JavaScript1.2" src="/acosa/images/stm31.js"></script>
  <script type="text/javascript" language="JavaScript1.2" src="/acosa/ventas_clientes/js/sisventascli.js"></script>
  <script type="text/javascript" language="JavaScript1.2" src="/acosa/ventas_clientes/js/validacion.js"></script>
	<script language="JavaScript" src="/acosa/ventas_clientes/js/sismaestros.js"></script>
</head>
<body leftmargin="0" topmargin="0">
<div id="logo" style="position:absolute; right:10px;"><img src="/acosa/images/logocia.jpeg" height="45" width="90"></div>
<div id="header" align="center">&#160;</div>
<div id="content">
  <div id="menu"><?php include("include/menu.inc.php"); ?></div>
  <div id="content_title" >&#160;</div>
  <div id="content_body" >&#160;</div>
	<div id="resultados_grid" >&#160;</div>
  <div id="content_footer">&#160;</div>
</div>
<div id="footer">&#160;</div>
<iframe frameborder="1" id="control" name="control" scrolling="no" src="control.php?rqst=OBTCANCELACION.OBTENERCANCELACION" width="10" height="10"></iframe>
</body>
</html>