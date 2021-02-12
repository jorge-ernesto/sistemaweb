<?php
/*session_start();
include("../valida_sess.php");
include("../utils/acceso_sistem.php");
include("../config.php");
require("../clases/funciones.php");
include("../combustibles/inc_top.php");*/
include("../menu_princ.php");
include("../functions.php");
include("../utils/acceso_sistem.php");
require("../clases/funciones.php");

$funcion = new class_funciones;
// crea la clase para controlar errores
$clase_error = new OpensoftError;
// conectar con la base de datos
$conector_id=$funcion->conectar("","","","","");


$sql = "select lado, prod1, prod2, prod3, prod4, carga from pos_cmblados order by lado";


$ip_remote = $_SERVER['REMOTE_ADDR'];

//echo $boton;
switch ($boton) {

   case Agregar:
		if(strlen($new_lado)>0)
		{
			$okgraba=true;

			/*
			$new_lado=strtoupper(trim($new_lado));
			if(strlen($new_codigo)>0)
			{
				$okgraba=false; $mensaje="Error!! \\n Codigo debe ser Numerico";
			}*/
			/*
			$new_nombre=strtoupper(trim($new_nombre));
			if(strlen($new_nombre)==0) { $okgraba=false; $mensaje="Error!! \\n Nombre Vacio"; }

			$new_breve_nombre=strtoupper(trim($new_breve_nombre));
			if(strlen($new_breve_nombre)==0) { $okgraba=false; $mensaje="Error!! \\n Nombre Breve Vacio"; }

			$ip_remote = $_SERVER['REMOTE_ADDR'];
			*/
			$sql_busc = "select lado from pos_cmblados where trim(lado)='$new_lado'";
			$xsqlbusc=pg_query($conector_id,$sql_busc);

			if(pg_num_rows($xsqlbusc)>0)
			{
				$okgraba=false; $mensaje="Error!! \\n Codigo ya Existe";
			}

			if($okgraba==true)
			{
					$sql_insert = "INSERT INTO POS_CMBLADOS
						(LADO,PROD1,PROD2,
						PROD3,PROD4, CARGA, AUDITOR) values
						('$new_lado',
						'$new_prod1','$new_prod2','$new_prod3',
						'$new_prod4','$new_carga','$usuario')";
					//echo $sql_insert;
					$xsql_insert = pg_exec($conector_id, $sql_insert);
					if($xsql_insert)
					{
						$new_lado="";
						$new_prod1="";
						$new_prod2="";
						$new_prod3="";
						$new_prod4="";
					}
			}else{
				echo '<script>alert("'.$mensaje.'")</script>';
			}
		}else{
			echo '<script>alert("Error!! \\n Lado Vacio")</script>';
		}
      break;

   case Modificar:
		$xsqlm=pg_query($conector_id,$sql);
		$ilimitm=pg_num_rows($xsqlm);
		$irowm=0;
		while($irowm<$ilimitm) {
			$am0=pg_result($xsqlm,$irowm,0);
			$idm[$am0]=$am0;
			//echo "--".$idm[$am0]."--".$idp[$am0]."--<br>";
			if($idm[$am0]==$idp[$am0]) {

				/*if($v_depreciacion[$am0]=="")
				{ $v_depreciacion[$am0]="null"; }

				if($v_revaluacion[$am0]=="")
				{ $v_revaluacion[$am0]="null"; }

				if($v_secuencia[$am0]=="")
				{ $v_secuencia[$am0]="null"; }
*/
				$sqlupd=" update POS_CMBLADOS set
					prod1='".$prod1[$am0]."',
					prod2='".$prod2[$am0]."',
					prod3='".$prod3[$am0]."',
					prod4='".$prod4[$am0]."',
					auditor='$usuario'
					where trim(lado)='".$idm[$am0]."'";
				//echo $sqlupd;
				$xsqlupd=pg_exec($conector_id,$sqlupd);
			}
			$irowm++;
		}
      break;


   case Eliminar:
		$xsqlm=pg_exec($conector_id,$sql);
		$ilimitm=pg_numrows($xsqlm);
		$irowm=0;
		while($irowm<$ilimitm) {
			$am0=pg_result($xsqlm,$irowm,0);
			$idm[$am0]=$am0;
			if($idm[$am0]==$idp[$am0]) {
				$sqlupd="delete from pos_cmblados
						 where trim(lado) ='".$idm[$am0]."'";
				$xsqlupd=pg_exec($conector_id,$sqlupd);
			}
			$irowm++;
		}
      break;
 }



?>

<script language="javascript">

function agregar()
{
	var n_lado = formular.new_lado.value;
	if(n_lado>0)
	{
		document.formular.submit();
	}
	else {
		alert("LADO VACIO");
	}
}

</script>

<form name="formular" action="" method="post">

<table border="1">
	<tr>
		<th width="20">
		<th width="40">LADO
		<th width="50">PROD1
		<th width="50">PROD2
		<th width="50">PROD3
		<th width="50">PROD4
		<th width="60">
	<tr>
		<td>
		<td><input type='text' name='new_lado' value='<?php echo $new_lado; ?>'size='5' maxlength='2'>
		<td><input type='text' name='new_prod1' value='<?php echo $new_prod1; ?>'size='5' maxlength='2'>
		<td><input type='text' name='new_prod2' value='<?php echo $new_prod2; ?>'size='5' maxlength='2'>
		<td><input type='text' name='new_prod3' value='<?php echo $new_prod3; ?>'size='5' maxlength='2'>
		<td><input type='text' name='new_prod4' value='<?php echo $new_prod4; ?>'size='5' maxlength='2'>
		<td><input type="submit" name="boton" value="Agregar">
	<tr>
		<th>&nbsp;
		<th>LADO
		<th>PROD1
		<th>PROD2
		<th>PROD3
		<th>PROD4
		<th>CARGA
	<tr>
		<td>&nbsp;
		<td colspan="2"><input type="submit" name="boton" value="Modificar">
		<td><input type="submit" name="boton" value="Eliminar">
		<td>&nbsp;
		<td>&nbsp;
		<td>&nbsp;
<?php
	$xsql = pg_query($conector_id, $sql);
	$i=0;
	while($i<pg_num_rows($xsql))
	{
		$rs = pg_fetch_array($xsql, $i);
		$a= $rs[0];

		echo "<tr>
				<td><input type='checkbox' name='idp[$a]' value='$rs[0]'>
				<td>$a
				<td><input type='text' name='prod1[$a]' value='$rs[1]' size='5' maxlength='2'>
				<td><input type='text' name='prod2[$a]' value='$rs[2]' size='5' maxlength='2'>
				<td><input type='text' name='prod3[$a]' value='$rs[3]' size='5' maxlength='2'>
				<td><input type='text' name='prod4[$a]' value='$rs[4]' size='5' maxlength='2'>
				<td>&nbsp;$rs[5]
				";
		$i++;
	}
?>
	<tr>
		<td>&nbsp;
		<td colspan="2"><input type="submit" name="boton" value="Modificar">
		<td><input type="submit" name="boton" value="Eliminar">
		<td>&nbsp;
		<td>&nbsp;
		<td>&nbsp;
</table>
</form>
<?php pg_close($conector_id); ?>
