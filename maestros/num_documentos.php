<?php

include "../menu_princ.php";
include("../functions.php");
include("functions.php");
require("../clases/funciones.php");

$funcion = new class_funciones;
$clase_error = new OpensoftError;
$clase_error->_error();
$conector_id=$funcion->conectar("","","","","");

if(strlen($diaa)==0){
	rangodefechas();
	$diad=$zdiaa; $mesd=$zmesa; $anod=$zanoa;
	$diaa=$zdiaa; $mesa=$zmesa; $anoa=$zanoa;
}

$fechad=$anod."-".$mesd."-".$diad;
$fechaa=$anoa."-".$mesa."-".$diaa;

if(strlen(trim($ch_almacen))==0){
	$ch_almacen=$almacen;
}
?>
<script language="JavaScript">

function ventanaSecundaria (URL){
	window.open(URL,"ventana1","width=800, height=600, scrollbars=no, menubar=no, location=no")
}
</script>
<?php

$sql_1  = "select num_tipdocumento, 
		  num_seriedocumento, 
		  num_descdocumento, 
		  num_longdocumento, 
             trim(num_numactual), 
                  num_tipdocumento||num_seriedocumento 
             from int_num_documentos order by 

		  num_tipdocumento, 
                  num_seriedocumento" ;
$rs_sql = pg_exec($conector_id, $sql_1);
$n_sql  = pg_numrows($rs_sql);

if($boton=="Buscar"){
	$w_td=strlen($tip_doc);
	if($w_td>0){
		$sql="select num_tipdocumento, 
			     num_seriedocumento, 
  			     num_descdocumento, 
			     num_longdocumento, 
                   	trim(num_numactual), 
                             num_tipdocumento||num_seriedocumento 
                        from int_num_documentos
 
where num_tipdocumento='".$tip_doc."'order by num_tipdocumento, num_seriedocumento";

		$rs_sql=pg_exec($conector_id, $sql);
		$n_sql=pg_numrows($rs_sql);
	} else {	
		$sql="select num_tipdocumento, 
			     num_seriedocumento, 
 			     num_descdocumento, 
                             num_longdocumento, 
trim(num_numactual), num_tipdocumento||num_seriedocumento 
                        from int_num_documentos order by 

 num_tipdocumento, num_seriedocumento" ;
		$rs_sql=pg_exec($conector_id, $sql);
		$n_sql=pg_numrows($rs_sql);
	}
}

if($boton=="Modificar") {
	
	$i=0;
	while($i<$n_sql){	
		$n_a0=pg_result($rs_sql,$i,5);
		$n_clav[$n_a0]=$n_a0;
		if($n_clav[$n_a0]==$m_clav[$n_a0]){
			$a="update int_num_documentos set num_numactual='".$num[$n_a0]."' 
			where num_tipdocumento||num_seriedocumento='".$n_clav[$n_a0]."'";
			$rs_a=pg_exec($conector_id,$a);
		}
		$i++;
	}
	$rs_sql=pg_exec($conector_id, $sql_1);
	$n_sql=pg_numrows($rs_sql);
}	
?>

<html><link rel="stylesheet" href="/sistemaweb/sistemaweb.css" type="text/css">
<head> <title>SISTEMA OPENSOFT</title></head>
<!-- <body onfocus="mueveReloj('f_name.reloj'); activa()">-->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<body>
NUMERO DE DOCUMENTOS<BR>
<?php
//	echo "ESTACION ".trim(obtenerAlmacen($conector_id, $almacen));
?>
<form action="" method="post">

<table>
	<tr><td colspan="2"><center>Busqueda Segun Tipo de Documento</center></td></tr>
	<tr><td></td><td></td><tr>					
	<tr><td>Tipo Documento</td><td><input type='text' name='tip_doc'></td></tr>
	<tr><td></td><td></td><tr>					
	<tr><td colspan="2"><center><input type="submit" name="boton" value="Buscar"></center></td></tr>

</table>

<br>

<table>
	<tr>
	<td><input type="submit" name="boton" value="Modificar"></td>
	</tr>
</table>
<table border="1" cellpadding="0" cellspacing="0">
	<tr>
		<td></td>
		<td>TIPO DE DOC.</td>
		<td>CODIGO</td>
		<td>DESCRIPCION</td>
		<td>LONGITUD</td>
		<td>NUM. ACTUAL</td>		
	</tr>
<?php
	
$conta=0;
	
while($conta<$n_sql){	
	$a0=pg_result($rs_sql,$conta,0);
	$a1=pg_result($rs_sql,$conta,1);
	$a2=pg_result($rs_sql,$conta,2);
	$a3=pg_result($rs_sql,$conta,3);
	$num=pg_result($rs_sql,$conta,4);
	$a5=pg_result($rs_sql,$conta,5);

	echo "<tr>";
	echo "<td><input type='checkbox' name='m_clav[$a5]' value='".$a5."'></td>";
	echo "<td>".$a0."</td>";
	echo "<td>".$a1."</td>";
	echo "<td>".$a2."</td>";
	echo "<td>".$a3."</td>";
	echo "<td><input type='text' name='num[$a5]' value='".$num."'</td>";
	echo "</tr>";

	$conta++;	

}
?>
</table>
</form>
</body>
</html>
<?php
if ($conector_id) 
	pg_close($conector_id);
