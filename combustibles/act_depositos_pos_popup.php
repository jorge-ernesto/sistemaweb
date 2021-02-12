<html>
<head>
<title>Sistema de Ventas - Actualizacion de Depositos POS</title>
<link rel="stylesheet" href="/sistemaweb/sistemaweb.css" type="text/css">
<link rel="stylesheet" href="/sistemaweb//css/sistemaweb.css" type="text/css">
<script language="JavaScript1.2" src="/sistemaweb/images/stm31.js"></script>
<script language="JavaScript" src="js/combustibles.js"></script>
</head>
<body>
<script src="/sistemaweb/js/calendario.js" type="text/javascript" ></script>
<script src="/sistemaweb/js/overlib_mini.js" type="text/javascript" ></script>
<script src="/sistemaweb/utils/cintillo.js" type="text/javascript" ></script>
<script src="/sistemaweb/menu/milonic_src.js" type="text/javascript"></script>
<script src="/sistemaweb/menu/mmenudom.js" type="text/javascript"></script>

<div id="content">
    <div id="content_title">&nbsp;</div>
    <div id="content_body">&nbsp;</div>
    <div id="content_footer">&nbsp;</div>
</div>
<div id="footer">&nbsp;</div>
<iframe id="control" name="control" scrolling="no" src="control.php?rqst=MOVIMIENTOS.ACTDEPOSITOSPOS&action=edit&ch_almacen=<?php echo urlencode($_REQUEST['ch_almacen']); ?>&dt_dia=<?php echo urlencode($_REQUEST['dt_dia']); ?>&ch_posturno=<?php echo urlencode($_REQUEST['ch_posturno']); ?>&ch_codigo_trabajador=<?php echo urlencode($_REQUEST['ch_codigo_trabajador']); ?>&ch_numero_documento=<?php echo urlencode($_REQUEST['ch_numero_documento']); ?>&ch_numero_correl=<?php echo urlencode($_REQUEST['ch_numero_correl']); ?>&tabla=<?php echo urlencode($_REQUEST['tabla']); ?>" frameborder="1" width="10" height="10"></iframe>
</body>
</html>