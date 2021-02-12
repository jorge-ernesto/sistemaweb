<?php
include("config.php");
$rs = ayuda($consulta,$cod, "null" );
if (pg_numrows($rs)>0){
	$A = pg_fetch_array($rs,0);
	$desc1 = $A[1];
	pg_close();
	}
else
	{
	$desc1=' ';
	}

?>

<HTML><HEAD>
<SCRIPT LANGUAGE="JavaScript">
function timeclose()
	{
	opener.document.<?php echo $des; ?>.value = '<?php echo $desc1;?>';
	setTimeout("window.close()",500);
	}
</SCRIPT>
</HEAD>
<BODY BGCOLOR=#fff111 onLoad=timeclose()>
<TABLE WIDTH=100% HEIGHT=100%><TR><TD ALIGN=center VALIGN=middle>
PROCENSANDO
</TD></TR></TABLE></BODY>
</HTML>
