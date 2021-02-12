<?php
include "../menu_princ.php";
require("../clases/funciones.php");
include("include/functions.inc.php");

$funcion = new class_funciones;
$clase_error = new OpensoftError;
$clase_error->_error();
$conector_id=$funcion->conectar("","","","","");

$auxiliar = explode('/',$_REQUEST['c_fec_desde']);
$DESDE = $auxiliar[2].'/'.$auxiliar[1].'/'.$auxiliar[0];
														
$auxiliar1 = explode('/',$_REQUEST['c_fec_hasta']);
$HASTA = $auxiliar1[2].'/'.$auxiliar1[1].'/'.$auxiliar1[0];					

$auxiliar2 = explode('/',$_REQUEST['c_fec_liquidacion']);
$LIQUIDACION = $auxiliar2[2].'/'.$auxiliar2[1].'/'.$auxiliar2[0];					
$fecha_liquidacion = $auxiliar2[2]."-".$auxiliar2[1]."-".$auxiliar2[0];

$serie_final = $_POST['c_serie'];

function validaDia($dia) {
	global $sqlca;

	$sql = "SELECT CASE WHEN ch_poscd='A' THEN ch_posturno ELSE ch_posturno-1 END FROM pos_aprosys where da_fecha='$dia'";
	if ($sqlca->query($sql) < 0) 
		return false;
	$a = $sqlca->fetchRow();
	$maxturno = $a[0];
		
	if(trim($maxturno) == "")
		$maxturno = 0;
		
	$sql = "SELECT 1 FROM pos_consolidacion WHERE dia='$dia' AND turno=$maxturno";
	if ($sqlca->query($sql) < 0) 
		return false;						
	$a = $sqlca->fetchRow();	
	
	if($a[0]==1) {
		return 0; // no puede cambiar
	} else {
		return 1; // si puede cambiar
	}
}

if (($_POST['accion'] == "Liquidar")){

	$flag = validaDia($fecha_liquidacion);
		
	if($flag == 0) {
		?><script>alert("<?php echo 'No se puede liquidar en esa fecha, ya ha sido consolidada!'; ?> ");</script><?php
	} else {
		$sql1 = "SELECT * FROM vales_temp where desde='$DESDE' and hasta='$HASTA' and fec_liquidacion='$LIQUIDACION'";
		$sql  = pg_query($conector_id, "SELECT * FROM vales_temp where desde='$DESDE' and hasta='$HASTA' and fec_liquidacion='$LIQUIDACION'");
		$registrosliquidacion=pg_num_rows($sql);
		if ($registrosliquidacion>0){
			switch($accion){
				case "Liquidar":
				$rs = pg_exec("select util_fn_tipo_cambio_dia(to_date('$c_fec_liquidacion','dd/mm/yyyy'))");
				$temporal = pg_exec($conector_id, "SELECT distinct(ch_cliente) as ch_cliente FROM vales_temp ");
				while ($row = pg_fetch_array($temporal)){
					$valor=trim($row["ch_cliente"]);
					$sql1 = pg_exec($conector_id, "SELECT * FROM FAC_PRECIOS_CLIENTES WHERE ((dt_fecha_inicio between '$DESDE' and '$HASTA') or (dt_fecha_fin between '$DESDE' and '$HASTA')) and habilitado='f' and  ch_codigo_cliente_grupo='$valor' limit 1");
					$verificainautorizar=pg_num_rows($sql1);
					if ($verificainautorizar >0) {
						$preciosinautorizar=1;
						break;
					}
				}
				if($registrosliquidacion) {
					if($preciosinautorizar!=1) {
						if($rs != null){
							$documento=substr($documento,strlen($documento)-2,strlen($documento));
							$liquidacion_antes = obtieneNumeroDocumento("LV");
							$q = "select VENTAS_LIQUIDACION_VALES('$DESDE','$HASTA','$documento','$serie_final','$LIQUIDACION')";
							$rs = pg_exec($q); 
							$liquidacion_despues = obtieneNumeroDocumento("LV");
							print_r($q);
						}else{
							echo "else".$registrosliquidacion;
							print "<script>alert('No se ha definido tipo de cambio del dia $c_fec_liquidacion')</script>";
						}
					}else{
						print "<script>alert('NO SE PUEDE LIQUIDAR EXISTEN PRECIOS ESPECIALES QUE NO ESTA AUTORIZADOS')</script>";
					}
				}else{
					print "<script>alert('La fecha DESDE Y HASTA o la FECHA DE LIQUIDACION han sido modificadas No puede liquidar VERIFIQUE LAS FECHAS o Cargue de nuevo los Clientes')</script>";
				}
				break;
			}
		}else{
			 print "<script>alert('NO EXISTEN VALES SELECCIONADOS, VERIFIQUE FECHA O CLIENTES')</script>";
		}
	}	
}elseif($_REQUEST['accion']=='Tipo'){
	$queryx = "select num_seriedocumento,num_numactual from int_num_documentos where num_tipdocumento='".$_REQUEST['c_tipo']."'";
	$sqlx = pg_query($conector_id, $queryx);
	$registrosliquidacionx=pg_num_rows($sqlx);
}
$hoy = date("d/m/Y");
if($c_fec_desde==""){$c_fec_desde=$hoy;}
if($c_fec_hasta==""){$c_fec_hasta=$hoy;}
if($c_fec_liquidacion==""){$c_fec_liquidacion=$hoy;}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<script language="JavaScript">
<!--
if (history.forward(1)){location.replace(history.forward(1))};
function mandarDatos(form,opcion){
		var pasa = false;
	if(opcion=="Liquidar"){
		for (i=0; i<form.documento.length; i++)  { 
			if (form.documento[i].checked){
				pasa = form.documento[i].value;
			}
		}
			if(!pasa){
			alert("No se ha elegido el documento");
			pasa = false;
			}
		if(form.c_fec_desde.value=='' || form.c_fec_hasta.value==''){
			alert("Error en el rango de fechas");
			pasa = false;
		}
	}
	if(pasa){
		form.accion.value=opcion;
		form.submit();
	}
}

function obtenerSerieDocumento(){
	var fec_desde = document.getElementsByName('c_fec_desde')[0].value;
	var fec_hasta = document.getElementsByName('c_fec_hasta')[0].value;
	var fec_liquidacion = document.getElementsByName('c_fec_liquidacion')[0].value;
	//var fec_desde = "11";
	//var fec_hasta = "12";
	var tipo_unico = document.getElementsByName('documento')[0];
	if (tipo_unico.value=='000010' && tipo_unico.checked){
	document.location.href = "vta_liquidacion_vales.php?accion=Tipo&c_tipo=10&c_fec_desde="+fec_desde+"&c_fec_hasta="+fec_hasta+"&c_fec_liquidacion="+fec_liquidacion;
	}else{
	document.location.href = "vta_liquidacion_vales.php?accion=Tipo&c_tipo=35&c_fec_desde="+fec_desde+"&c_fec_hasta="+fec_hasta+"&c_fec_liquidacion="+fec_liquidacion;
	}
}
//-->
</script>
<link type="text/css" href="/sistemaweb/css/sistemaweb.css" rel="stylesheet" >
<title>Liquidacion de Vales de Credito</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<script language="JavaScript" src="/sistemaweb/clases/calendario.js"></script>
<script language="JavaScript" src="/sistemaweb/clases/overlib_mini.js"></script>
<form name="form1" method="post" >
Liquidacion de Vales
<table width="75%" border="1">
	<tr>
		<td width="19%">&nbsp;</td>
		<td width="20%">&nbsp;</td>
		<td width="20%">&nbsp;</td>
		<td width="35%">&nbsp;</td>
		<td width="6%">&nbsp;</td>
	</tr>
	<tr> 
		<td height="23">&nbsp;</td>
		<td width="25%">Desde 
		<input type="text" name="c_fec_desde" size="13" value="<?php echo $c_fec_desde;?>">&nbsp;<a href="javascript:show_calendar('form1.c_fec_desde');"><img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a><div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"/></td>
		<td width="25%">Hasta      
		<input type="text" name="c_fec_hasta" size="13" value="<?php echo $c_fec_hasta;?>">&nbsp;<a href="javascript:show_calendar('form1.c_fec_hasta');"><img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a></td>
		<td><input type="button" name="btn_clientes" value="Clientes" onClick="javascript:window.open('vta_registrar_clientes.php?c_fec_liquidacion='+c_fec_liquidacion.value+'&c_fec_desde='+c_fec_desde.value+'&c_fec_hasta='+c_fec_hasta.value+'&borrar=borrar','win_clientes','width=800,height=450,scrollbars=yes,menubar=yes,left=0,top=0');"></td>
		<td>&nbsp;</td>
	</tr>
	<tr> 
		<td height="23">&nbsp;</td>
		<td>Factura&nbsp;<input type="radio" name="documento" value="000010" <?php if($_REQUEST['c_tipo']=='10') echo 'checked';?> onclick="obtenerSerieDocumento();"> <br>
		Boleta&nbsp;&nbsp;&nbsp;<input type="radio" name="documento" value="000035" <?php if($_REQUEST['c_tipo']=='35') echo 'checked';?> onclick="obtenerSerieDocumento();"></td>
		<td>
		<?php 
		//print_r($sqlx);	
		if($_REQUEST['c_tipo'] == '10' || $_REQUEST['c_tipo'] == '35') {
			if ($registrosliquidacionx == 1) {
				$row = pg_fetch_array($sqlx);
				echo 'Serie: '.$row[0];
				echo '<input type="hidden" name="c_serie" value="'.$row[0].'">';
		  	} elseif($_POST['c_serie']) {
				echo 'Serie: '.$_POST['c_serie'];
				echo '<input type="hidden" name="c_serie" value="'.$_POST['c_serie'].'">';
			} else {
				echo 'Serie: <select name="c_serie">';
				while($row = pg_fetch_array($sqlx)) {
					echo '<option value="'.trim($row[0]).'">'.$row[0].' #'.$row[1].'</option>';
				}
				echo '</select>';
			}
		}?>
		</td>
		<td></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td>Fecha de Liquidacion</td>
		<td><input type="text" name="c_fec_liquidacion" size="13" value="<?php echo $c_fec_liquidacion;?>">&nbsp;<a href="javascript:show_calendar('form1.c_fec_liquidacion');"><img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a></td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td><input type="button" name="btn_buscar" value="Liquidar Vales" onClick="javascript:mandarDatos(form1,'Liquidar');"></td>
		<td><input type="value" name="accion" ></td>
		<td>&nbsp;</td>
	</tr>
</table>
<?php
if (($_POST['accion'] == "Liquidar")) {
	if(($registrosliquidacion) and (!$preciosinautorizar)) { 
		?>
		<script language="javascript">
		window.open("generar_liquidacion_vales_new.php?desde=<?php echo $liquidacion_antes; ?>&hasta=<?php echo $liquidacion_despues; ?>", "xxx");
		</script>
		<?php
		$query = "DELETE FROM vales_temp";
		$sqlca->query($query);
	} else { 
		$query = "DELETE FROM vales_temp";
		$sqlca->query($query);
	}
} else {
	$query = "DELETE FROM vales_temp";
	$sqlca->query($query);
}
?>
</form>
</body>
</html>
<?php pg_close();?>	
