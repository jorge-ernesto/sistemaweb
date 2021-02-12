<?php

	include('../../start.php');
 	include('../movimientos/m_targpromocion.php');
			
			$idcuenta = $_REQUEST['idcuenta'];
			//echo $idcuenta;
			$registro = TargpromocionModel::obtenerCuenta(trim($idcuenta),'1');
			//$registro = $listado['datosarticulos'];
		
	?>
	
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<title>Mostrar Cuenta</title>
<head>
<script language="JavaScript">
	window.name="venCuenta";
	function cerrar(){
	window.close();
	}



</script>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="/sistemaweb/css/sistemaweb.css" type="text/css">
</head>

<body bgcolor="#E8F0EA">
<form name="form1" method="post" action="">
  <table bgcolor="#FFFFFF" width="100%" border="0">
	<tr><td height="17" colspan="2" class="form_cabecera">&nbsp;DATOS DE CUENTA</td>
	</tr>
	<tr>
	  <td height="12" colspan="2" ></td>
    </tr>
	
	<tr><td width="14%" height="17" class="form_texto">NRO CUENTA</td>
	<td width="86%" height="17" class="form_texto">:&nbsp;<span class="form_valor_texto"><?php echo $registro['nu_cuenta_numero']?></span></td>
	</tr>
	
	<tr><td height="17" class="form_texto">TITULAR</td>
	<td height="17" class="form_texto">:&nbsp;<span class="form_valor_texto"><?php echo $registro['ch_cuenta_apellidos']." ".$registro['ch_cuenta_nombres']?></span></td>
	</tr>

	<tr><td height="17" colspan="2" class="form_texto">DNI :&nbsp;<span class="form_valor_texto"><?php echo $registro['ch_cuenta_dni']?></span>&nbsp;&nbsp; RUC :&nbsp;<span class="form_valor_texto"><?php echo $registro['ch_cuenta_ruc']?></span></td>
	</tr>
	
	<tr><td height="17" class="form_texto">DIRECCI&Oacute;N</td>
	<td height="17" class="form_texto">:&nbsp;<span class="form_valor_texto"><?php echo $registro['ch_cuenta_direccion']?></span></td>
	</tr>

	<tr><td height="17" class="form_texto">TEL&Eacute;FONO</td>
	<td height="17" class="form_texto">:&nbsp;<span class="form_valor_texto"><?php echo $registro['ch_cuenta_telefono1']?><?php echo ($registro['ch_cuenta_telefono2']==""?"":" | ".$registro['ch_cuenta_telefono2'])?></span></td></tr>
	
  <tr><td height="17" colspan="5"><hr></td><tr><td height="17" colspan="2" class="form_texto"><span class="form_pie">PUNTOS CUENTA :&nbsp;<?php echo $registro['nu_cuenta_puntos']?></span></td></tr>
  <tr>
    <td height="12" colspan="2" class="form_texto"></td>
  </tr>
  <tr>
    <td height="17" colspan="2" class="form_texto"><input name="CERRAR" type="button" id="CERRAR" onClick="javascript:cerrar();" value="CERRAR"></td>
  </tr>
  </table>
</form>
</body>
</html>

