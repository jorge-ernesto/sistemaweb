<?php
session_start();
if(!session_is_registered("usuario"))
{
  Header("Location: login.php");
}
?>
<html>
<head>
<title><?php echo "$title"; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<!--<body bgcolor="#F3FFF3" text="#000000">-->
<body bgcolor='#D3F3EE' marginheight="0" marginwidth="0" rightmargin="0" leftmargin=0 bottommargin="0" onload=window.open('detalle-s.php','mainFrame') >

<?php
include("/var/www/html/mozart/db.inc");
$var=date("Y-m-d");
	$sql70="select * from tabtipocambio where fecha='".$var."'";
	$query70=pg_exec($coneccion,$sql70);
    $ilimit70=pg_numrows($query70);
  if($ilimit70>0) {
	$compra=pg_result($query70,0,2);
    $venta=pg_result($query70,0,1);
  }
$sql="select nomfuncion,surl,tip,codnivel from funcion,derechos  
where codperfil='".$perfil."' and derechos.codfuncion like '".$pmFuncion."%'  
and funcion.codfuncion=derechos.codfuncion order by funcion.tip desc";
$query=pg_exec($coneccion,$sql);
$row=pg_numrows($query);
echo'<table border="1" bordercolor="#0E9E9E" cellpadding="0" cellspacing="0"><tr><td>';
echo'<table width="140" border="0" cellspacing="0" cellpadding="8" bgcolor="#FFFFFF">';
  echo'<tr><td><b>'.$pmTitulo.'</b></td></tr>';
  echo'<tr><td colspan="4" bgcolor="#000000" height="1"></td></tr>';
$contv=0; $conta=0;
for($x=0;$x<$row;$x++) 
{   
  $var=pg_result($query,$x,0);
  $dir=pg_result($query,$x,1);
  $tip=pg_result($query,$x,2);
  if($tip=='v'){$color="#00CC00"; $contv++;}
  if($tip=='a'){$color="#003399"; $conta++;}
  if($tip==''){$color="";}

  if($conta==1 and $contv!=0)
     {  echo'<tr><td colspan="4" bgcolor="#000000" height="1"></td></tr>';
     }
  echo'<tr>';
    echo'<td>';
     echo'<a href="'.$dir.'" target="mainFrame"><b>
<font color="'.$color.'" size="1" 
face="Verdana,Arial,Helvetica,sans-serif">'.$var.'</font></b></a>';
     echo'</td>';
    echo'</tr>';
} 

 echo'<tr><td colspan="4" bgcolor="#000000" height="1"></td></tr>';
 echo'</table></td></tr></table>';
?><br>
<!--<table border="1" bordercolor="#0E9E9E" cellpadding="0" cellspacing="0"><tr>
    <td>  -->
      <table bgcolor="#D3F3EE">
        <tr><td>  
            <div align="center"><font face="Verdana, Arial, Helvetica, sans-serif" size="1"><b>Hoy 
              es</b> 
              <?php echo date("d/m/Y"); ?>
              <b> 
              
        <hr noshade>
              tipo cambio<br>
        </b>compra: 
        <?php  echo number_format($venta,2);  ?>
        <br>
        venta : 
        <?php  echo number_format($compra,2);  ?><br>
Al <?php 
$xsql66=pg_exec($coneccion,"select * from tabtipocambio order by fecha desc");
if(pg_numrows($xsql66)==0) {
echo date("d/m/Y");
} else {
 $xsql67=pg_exec($coneccion,"select * from tabtipocambio where fecha='1992/01/01'");
 if(pg_numrows($xsql67)==0) {
   echo date("d/m/Y");
  } else {
 $fa=substr(pg_result($xsql66,1,0),0,4);
   $fm=substr(pg_result($xsql66,1,0),5,2);
   $fd=substr(pg_result($xsql66,1,0),8,2);
   $c1=$fd."/".$fm."/".$fa;
 echo $c1;
 }
}
?>
<hr noshade>
        </font></div>
          </td></tr></table>
<!--    </td></tr></table>-->
</body>
</html>
<?php
pg_close($coneccion);
