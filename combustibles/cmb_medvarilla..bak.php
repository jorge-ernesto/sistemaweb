<?

include("../config.php");
include("../combustibles/inc_top.php");
include("../functions.php");
$irow=0;
$fecactualiz=date("Y-m-d");
$sql=" select ch_tanque, ch_sucursal,dt_fechamedicion,nu_medicion from comb_ta_mediciondiaria ";
 switch ($boton) {
   case Agregar:

		if(strlen($newcod)>0) {
			$xsqlbusc=pg_exec($coneccion,"select ch_sucursal,ch_tanque,dt_fechamedicion from comb_ta_mediciondiaria where 
			ch_sucursal='".$almacen."' and ch_tanque='".$newcod."' ");
			if(pg_numrows($xsqlbusc)==0){
				if(strlen($newprec)==0) { $newprec=0; }
					$sqlins="insert into comb_ta_mediciondiaria(ch_sucursal,ch_tanque,dt_fechamedicion,nu_medicion)
					values('".$almacen."','".$newcod."','".$newfecha."','".$newmedida."')";
					echo "$sqlins";
				$xsqlins=pg_exec($coneccion,$sqlins);
			}else{  ?>
			<script>alert(" El Codigo ya existe !!! ")</script>
<?			}
		}else{  ?>
			<script>alert(" Debe ingresar un Codigo válido")</script>
<?		}
      break;
   case Modificar:

		$xsqlm=pg_exec($coneccion,$sql);
		$ilimitm=pg_numrows($xsqlm);
		//$irow=0;
		while($irowm<$ilimitm) {
			$am0=pg_result($xsqlm,$irowm,0);
			$idm[$am0]=$am0;
			if($idm[$am0]==$idp[$am0]) {
				if(strlen($fechmed[$am0])==0) 
				{ $fechmed[$am0]=0; }
				if(strlen($nummed[$am0])==0) 
				{ $nummed[$am0]=0; }
				//echo "$newfecha";
				$sqlupd=" update comb_ta_mediciondiaria set ch_sucursal='".$almacen."',
				dt_fechamedicion='".$newfecha."', nu_medicion='".$newmedida."' where ch_tanque='".$idm[$am0]."' ";
				echo "$sqlupd";
				$xsqlupd=pg_exec($coneccion,$sqlupd);
				
			}      
			$irowm++;
		}

      break;
   case Eliminar:

		$xsqlm=pg_exec($coneccion,$sql);
		$ilimitm=pg_numrows($xsqlm);
		while($irowm<$ilimitm) {
			$am0=pg_result($xsqlm,$irowm,0);
			$idm[$am0]=$am0;
			if($idm[$am0]==$idp[$am0]) {
				$sqlupd=" delete from comb_ta_mediciondiaria where ch_tanque='".$idm[$am0]."' ";
				$xsqlupd=pg_exec($coneccion,$sqlupd);
			}
			$irowm++;
		}


      break;
// default:
 }

?>
<html>
<head>
<title>sistemaweb</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<marquee>CONFIGURACION DE TANQUES</marquee>
<hr noshade>
<br>

<form action='' method='post'>
  <table border="1" cellspacing="0" cellpadding="0">
    <tr> 
      <th>&nbsp;</th>
      <th>TANQUE</th>
      <th></th>
      <th>FECHA</th>
      <th>MEDIDA</th>
      <!--<th>FECHA ULT LECT</th>-->
    </tr>
    <tr> 
      <td>&nbsp;</td>
      <td>&nbsp;
        <input type='text' name='newcod' size='10' maxlength='2'></td>
      <td>&nbsp;
       <!-- <select name='newprod'>  -->
          <?  //Se esta mostrando el nombre del combustible
	$sqlprod=" select ch_codigocombustible,ch_nombrecombustible from comb_ta_combustibles order by ch_codigocombustible ";
	$xsqlprod=pg_exec($coneccion,$sqlprod);
	$ilimitprod=pg_numrows($xsqlprod);
	$irowprod=0;
	while($irowprod<$ilimitprod) {
		$p0=pg_result($xsqlprod,$irowprod,0);
		$p1=pg_result($xsqlprod,$irowprod,1);
		//echo "<option value='".$p0."'>".$p1."</option>";
		$irowprod++; 
	}  
?>
        </select> </td>
      <td>&nbsp;
        <input type='text' name='newfecha' size='15'></td>
      <td>&nbsp;
        <input type='text' name='newmedida' size='15'></td>
      <td>&nbsp;
        <input type='submit' name='boton' value='Agregar'></td>
    </tr>
    <tr> 
      <td>&nbsp;</td>
      <td><input name="boton" type="submit"  value="Modificar"></td>
      <td>&nbsp;</td>
      <td><input name="boton" type="submit" value="Eliminar"></td>
      <td>&nbsp;</td>
      
    </tr>
    <tr> 
      <th>&nbsp;</th>
      <th>TANQUE</th>
      <th>COMBUSTIBLE</th>
      <th>FECHA</th>
      <th>MEDIDA</th>
      <!--<th>FECHA ULT LECT</th>-->
    </tr>
    <?
$irow=0;
$sql=" select a.ch_tanque,b.ch_nombrecombustible,a.dt_fechamedicion,a.nu_medicion
from comb_ta_mediciondiaria a, comb_ta_combustibles b, comb_ta_tanques c
where a.ch_tanque=c.ch_tanque and b.ch_codigocombustible=c.ch_codigocombustible";
$xsql=pg_exec($coneccion,$sql);
$ilimit=pg_numrows($xsql);
while($irow<$ilimit) {
	$a0=pg_result($xsql,$irow,0);
	$a1=pg_result($xsql,$irow,1);
	$a2=pg_result($xsql,$irow,2);
	$a3=pg_result($xsql,$irow,3);
	$comb[$a0]=$a1;
	echo "<tr><td><input type='checkbox' name='idp[$a0]' value='".$a0."'>
	</td>
	<td>&nbsp;".$a0."</td><td>";
	$xsqlprod=pg_exec($coneccion,$sqlprod);
	echo "$a1";
	//$irowprod++;
	echo"</td>";
    echo "<td align='right'>&nbsp;<input type='text' name='fechmed[$a0]' value='".$a2."' style='text-align:right' size='15'></td><td align='right'>&nbsp;<input type='text' name='nummed[$a0]' value='".$a3."' style='text-align:right'  size='15'></td>";
    $irow++;
}	
?>
    <tr> 
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      
    </tr>
    <tr> 
      <td>&nbsp;</td>
      <td><input name="boton" type="submit"  value="Modificar"></td>
      <td>&nbsp;</td>
      <td><input name="boton" type="submit" value="Eliminar"></td>
      <td>&nbsp;</td>
      
    </tr>
	
  </table>
    <!--  prueba  -->
	
  <? 
	$modificar=$_POST["modificar"];
	//if(!isset("$modificar"))       
	{
  ?>
	<table width="117" height="59">
    <tr><td collspan="2"><div align="center">MEDIDA</div></td></tr>	 
<?
	$cons="select ch_tanque, nu_medicion from comb_ta_mediciondiaria";
	$rset=pg_exec($coneccion,$cons);
	while($fila=pg_fetch_row($rset))
	{   		
		echo"<tr><td><input name='numed' type='text' value='".$fila[1]."'></td></tr>";
    }
							}
	//else 
							{			
	$rset=pg_exec($coneccion,$cons);
	//while($fila=pg_fetch_row($rset))
	{  	$sqlupd=" update comb_ta_mediciondiaria set nu_medicion='".$numed."' where ch_tanque='".$fila[0]."' ";
		//$xsqlupd=pg_exec($coneccion,$sqlupd);
				
	}
			
								}
	?>   
	<tr>
	  <td><input name="modificar" type="submit" value="MODIFY"></td>
	
	</tr>	
	</table>
	<!-- hasta aqui es la prueba -->
  </table>
</form>
<br>
</body>
</html>  
<? include("../close_connect.php"); ?>