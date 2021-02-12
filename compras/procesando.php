<?php
include("/sistemaweb/planillas/config.php");
$Moneda_art = trim($_REQUEST['moneda']);
$TCambio_art = trim($_REQUEST['tcambio']);
//echo "TR: $TCambio_art";
if($_REQUEST['cost'])
{

    $rs = ayudaOrdCompraCosto($consulta,trim($cod), "null",trim($prove) );
    $rs2 = ayuda($consulta,trim($cod), "null");
    if (pg_numrows($rs2)>0)
    {
	$A = pg_fetch_array($rs,0);
	$Moneda = trim($A[3]);
	//echo "TR: $Moneda";
	$B = pg_fetch_array($rs2,0);
	$desc1 = $B[1];
	$cost1 = $A[2];
	//echo "COST1: $cost1";
	if($Moneda_art && $TCambio_art)
	{
	   //echo "ENTRO2";
	    if(trim($Moneda_art) == trim($Moneda))
	    {
	       $cost1 = $cost1;
               //echo "ENTRO";
	    }
	    elseif($Moneda_art != $Moneda)
	    {
	     //echo "ENTRO2";
		if($Moneda_art == '01')
		{
		  //echo "ENTRO 01";
		//echo "TR1: $Moneda";
		//echo "TR2: $Moneda_art";
		//echo "TC: $TCambio_art";
		    //$cost1 = $cost1 * $TCambio_art;
		    $cost1 = $cost1 * $TCambio_art;
		}else{
		  //echo "ENTRO 02";
		    $cost1 = $cost1 / $TCambio_art;
		    //echo "12";
		}
	    }
	    else
	    {
	     //echo "ENTRO3";
	    
	    }
	}
	
	pg_close();
    }
    else
    {
	$desc1=' ';
	$cost1=' ';
    }
}else{
    $rs = ayuda($consulta,trim($cod), "null" );
    if (pg_numrows($rs)>0){
	$A = pg_fetch_array($rs,0);
	$desc1 = $A[1];
        pg_close();
    }
    else
    {
        $desc1=' ';
    }
}
?>

<HTML><HEAD>
<SCRIPT LANGUAGE="JavaScript">
<?php
if($_REQUEST['cost'])
{
?>
function timeclose()
	{
	opener.document.<?php echo $des; ?>.value = '<?php echo $desc1;?>';
	opener.document.<?php echo $cost; ?>.value = '<?php echo $cost1;?>';
	setTimeout("window.close()",500);
	}
<?php
}else{
?>
function timeclose()
	{
	opener.document.<?php echo $des; ?>.value = '<?php echo $desc1;?>';
	setTimeout("window.close()",500);
	}
	
<?php
}
?>
</SCRIPT>
</HEAD>
<BODY BGCOLOR=#fff111 onLoad=timeclose()>
<TABLE WIDTH=100% HEIGHT=100%><TR><TD ALIGN=center VALIGN=middle>
PROCENSANDO
</TD></TR></TABLE></BODY>
</HTML>