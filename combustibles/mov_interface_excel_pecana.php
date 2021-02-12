<?php
//include "../valida_sess.php";
//include "../config.php";
?><html>
<head>
<title>Interface PECANA</title>
	<link rel="stylesheet" href="/sistemaweb/sistemaweb.css" type="text/css">
	<script language="JavaScript1.2" src="/sistemaweb/images/stm31.js"></script>
	<script language="JavaScript" src="js/combustibles.js"></script>
	<script src="/sistemaweb/js/jquery-2.0.3.js" type="text/javascript"></script>
    <link rel="stylesheet" href="/sistemaweb/css/jquery-ui.css" />
    <script src="/sistemaweb/js/jquery-ui.js"></script>
    <script  type="text/javascript"> 

	window.onload = function() {
		var fecha		= new Date();
		var annosistema	= "2014";
		var anno		= fecha.getFullYear();
		var annoact		= anno + 6;
		var month 		= fecha.getMonth() + 1;		
		$(function() {
			for(var i = annosistema; i < annoact; i++){
				$("select[name=year]").append(new Option(i,i));
			}

			$( "select[name=year]" ).val( anno );

			if(month < 10){
				month = "0" + month;
			}
			$( "select[name=month]" ).val( month );
		});
	};
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
<iframe id="control" name="control" scrolling="no" src="control.php?rqst=MOVIMIENTOS.INTERFAZPECANA&task=INTERFAZPECANA" frameborder="1" width="5" height="5"></iframe>
</body>
</html>