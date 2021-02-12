<?php
include "../valida_sess.php";
include "../config.php";
?><html>
<head>
<title>Ayuda</title>
<link rel="stylesheet" href="/sistemaweb/sistemaweb.css" type="text/css">
<script language="JavaScript" src="js/helper.js"></script>
</head>
<body leftmargin="0" topmargin="0"
<div id="header">&nbsp;</div>
<div id="content">
    <div id="content_title">&nbsp;</div>
    <div id="content_body">&nbsp;</div>
    <div id="content_footer">&nbsp;</div>
</div>
<div id="footer">&nbsp;</div>
<iframe id="control" name="control" scrolling="no" src="control.php?rqst=HELPER.<?php echo htmlentities($_REQUEST['action']); ?>&dstname=<?php echo htmlentities($_REQUEST['dstname']); ?>" frameborder="1" width="10" height="10"></iframe>
</body>
</html>