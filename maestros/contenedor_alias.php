<?php
include "../valida_sess.php";
include "../config.php";
?><html>
<head>
<title>Sistema de Compras</title>
<link rel="stylesheet" href="/sistemaweb/acosa.css" type="text/css">
<script language="JavaScript1.2" src="/sistemaweb/images/stm31.js"></script>
<script language="JavaScript" src="/sistemaweb/maestros/js/compras.js"></script>
</head>
<body leftmargin="0" topmargin="0">
<div id="header">&nbsp;</div>
<div id="content">
    <div id="item_principal">&nbsp;</div>
    <div id="items_alias">&nbsp;</div>
    <div id="controles_principales">&nbsp;</div>
</div>
<div id="footer">&nbsp;</div>
<iframe id="control" name="control" scrolling="no" src="control.php?rqst=<?php
echo htmlentities($_REQUEST['rqst']);
echo "&action=" . htmlentities($_REQUEST['action']);
echo "&codigo=" . htmlentities($_REQUEST['codigo']);
?>" frameborder="1" width="10" height="10"></iframe>
</body>
</html>
