<html>
<head>
<title>Interface Exactus</title>
  <link rel="stylesheet" href="/sistemaweb/sistemaweb.css" type="text/css">
  <script language="JavaScript1.2" src="/sistemaweb/images/stm31.js"></script>
  <script language="JavaScript" src="js/combustibles.js"></script>
<script>
function confirmarLink(pregunta, accionY){
  if(confirm(pregunta)){
     document.getElementById('control').src = accionY;
  }
}
</script>
</head>
<body leftmargin="0" topmargin="0">
<?php include "../menu_princ.php"; ?>
<div id="content">
<script src="/sistemaweb/js/calendario.js" type="text/javascript" ></script>
<script src="/sistemaweb/js/overlib_mini.js" type="text/javascript" ></script>
    <div id="content_title">&nbsp;</div>
    <div id="content_body">&nbsp;</div>
    <div id="content_footer">&nbsp;</div>
</div>
<div id="footer">&nbsp;</div>
<iframe id="control" name="control" scrolling="no" src="control.php?rqst=MOVIMIENTOS.INTERFAZEXACTUS&task=INTERFAZEXACTUS" frameborder="1" width="5" height="5"></iframe>
</body>
</html>
