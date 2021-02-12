<?php
session_start();

if(!session_is_registered("usuario")){
  Header("Location: /");
}
include("config.php");
?>
<!-- Roberto Palma Garcia - rpalma@pcprices.com.pe - 99656728 -->
<html>
<head>
<title>NCA</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script language="JavaScript">
<!--
function MM_swapImgRestore() { //v3.0
  var i,x,a=document.MM_sr; for(i=0;a&&i<a.length&&(x=a[i])&&x.oSrc;i++) x.src=x.oSrc;
}

function MM_preloadImages() { //v3.0
  var d=document; if(d.images){ if(!d.MM_p) d.MM_p=new Array();
    var i,j=d.MM_p.length,a=MM_preloadImages.arguments; for(i=0; i<a.length; i++)
    if (a[i].indexOf("#")!=0){ d.MM_p[j]=new Image; d.MM_p[j++].src=a[i];}}
}

function MM_findObj(n, d) { //v4.0
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
  if(!x && document.getElementById) x=document.getElementById(n); return x;
}

function MM_swapImage() { //v3.0
  var i,j=0,x,a=MM_swapImage.arguments; document.MM_sr=new Array; for(i=0;i<(a.length-2);i+=3)
   if ((x=MM_findObj(a[i]))!=null){document.MM_sr[j++]=x; if(!x.oSrc) x.oSrc=x.src; x.src=a[i+2];}
}
//-->
</script>
</head>
<body bgcolor="#D3F3EE" text="#000000" marginheight="0" marginwidth="0" rightmargin="0" leftmargin=0 topmargin=0 bottommargin="0">
<table align="top" cellpadding="0" cellspacing="0" border="0">
  <tr>
    <td><img src='cabecera.jpg' ></td>
  </tr>
</table>
<table border="0" cellspacing="0" cellpadding="0" align="left"><tr>
  <?php
   $sql="select nomfuncion,codfuncion,img,img1 from funcion where codnivel=0 order by orden";
   $query=pg_exec($coneccion,$sql);
   $row=pg_numrows($query); 
   $bb=1;
  if($row>0) {
   for($x=0;$x<$row;$x++)
   {
     $var=pg_result($query,$x,0);
     $cod=pg_result($query,$x,1);
	 $ruta=pg_result($query,$x,2);
	 $ruta1=pg_result($query,$x,3);
     $aux="ss";   
   ?>
    
    <td>
        <a href="sublinks.php?pmAux=<?php echo $aux; ?>&pmTitulo=<?php echo $var; ?>&pmFuncion=<?php 
	echo $cod; ?>" target="leftFrame" onMouseOut="MM_swapImgRestore()" 
	onMouseOver="MM_swapImage('Image<?php echo $bb; ?>','','<?php echo $ruta1; ?>',1)">
	<img name="Image<?php echo $bb; ?>" border="0" src="<?php echo $ruta; ?>"></a>
    </td>
   <?php  
    $bb++;  
   }
  }
   ?> 
   
   <td><a href="finsesion.php" target="_top" onMouseOut="MM_swapImgRestore()" 
	onMouseOver="MM_swapImage('Image9','','menu/salir-2.gif',1)">
	<img name="Image9" border="0" src="menu/salir.gif" >	</a>
   </td>
 
</table>
</body>
</html>
<?php
pg_close($coneccion);
