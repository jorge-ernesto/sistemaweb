<?php
include("../menu_princ.php");
include("../include/functions.php"); 
require("../clases/funciones.php");

extract($_REQUEST);

$funcion = new class_funciones;
$clase_error = new OpensoftError;
$conector_id = $funcion->conectar("","","","","");

if($cod_almacen == "") {
	$cod_almacen = $almacen;
}

if(strlen($m_clave) > 0 && $boton == "Complemento") {
	echo('<script languaje="JavaScript">');
	echo("location.href='vtacli_vales_complemento.php?v_fecha_desde=".$_REQUEST['v_fecha_desde']."&v_fecha_hasta=".$_REQUEST['v_fecha_hasta']."&m_clave=".$m_clave."' " );
	echo('</script>');
}

$rsx2 = pg_query($conector_id, "SELECT ch_almacen ,ch_nombre_almacen FROM inv_ta_almacenes WHERE ch_clase_almacen='1' AND trim(ch_almacen)=trim('$cod_almacen') ORDER BY ch_nombre_almacen");	
$C = pg_fetch_array($rsx2,0);
$almacen_dis = $C[1];
$rsx1 = pg_query($conector_id, "SELECT ch_almacen ,ch_nombre_almacen FROM inv_ta_almacenes WHERE ch_clase_almacen='1' ORDER BY ch_nombre_almacen");

if($boton == "Ins" or $boton == "Agregar") {
	echo('<script languaje="JavaScript">' );
	echo(" location.href='vtacli_vales_agrega.php?v_fecha_desde=".$_REQUEST['v_fecha_desde']."&v_fecha_hasta=".$_REQUEST['v_fecha_hasta']."' ");
	echo('</script>');
}


if($boton == "Mod" or $boton == "Modificar") {
	if(strlen($m_clave) > 0) {
		//$que = "SELECT 1 from pos_consolidacion WHERE dia = (SELECT dt_fecha FROM val_ta_cabecera WHERE trim(ch_sucursal||dt_fecha||ch_documento)='".trim($m_clave)."') and turno = CAST((SELECT ch_turno FROM val_ta_cabecera WHERE trim(ch_sucursal||dt_fecha||ch_documento)='".trim($m_clave)."') as integer);" ;
		$dia = "SELECT dt_fecha FROM val_ta_cabecera WHERE trim(ch_sucursal||dt_fecha||ch_documento)='".trim($m_clave)."'";
		$ejecuta = pg_query($conector_id, $dia);
		$fecha = trim(pg_result($ejecuta, 0));

		$turno = 0;
		$almacen = $_SESSION['almacen'];

		$sql = " SELECT validar_consolidacion('$fecha',$turno,'$almacen') ";
		$ejecuta2 = pg_query($conector_id, $sql);
		$resu = trim(pg_result($ejecuta2, 0));

		if($resu == 1) {
			echo('<script languaje="JavaScript"> ');
			echo('alert(" No se puede modificar \\n Fecha y turno ya consolidados !!! ") ');
			echo('</script>');
		}else{
			echo('<script languaje="JavaScript">');
			echo("location.href='vtacli_vales_modif.php?v_fecha_desde=".$_REQUEST['v_fecha_desde']."&v_fecha_hasta=".$_REQUEST['v_fecha_hasta']."&m_clave=".trim($m_clave)."' " );
			echo('</script>');
		}
	}else{
		echo('<script languaje="JavaScript"> ');
		echo('alert(" Debe seleccionar un Vale !!! ") ');
		echo('</script>');
	}
}

if($boton == "Elim" or $boton == "Eliminar") {
	
         if(strlen($m_clave) > 0) {
		$dia = "SELECT dt_fecha FROM val_ta_cabecera WHERE trim(ch_sucursal||dt_fecha||ch_documento)='".trim($m_clave)."'";
		$ejecuta = pg_query($conector_id, $dia);
		$fecha = trim(pg_result($ejecuta, 0));

		$turno = 0;
		$almacen = $_SESSION['almacen'];

		$sql = " SELECT validar_consolidacion('$fecha',$turno,'$almacen') ";
		$ejecuta2 = pg_query($conector_id, $sql);
		$resu = trim(pg_result($ejecuta2, 0));

		if($resu == 1) {
			echo('<script languaje="JavaScript"> ');
			echo('alert(" No se puede modificar \\n Fecha y turno ya consolidados !!! ") ');
			echo('</script>');
		}else{
                    echo $m_clave;
                    $m_clave=trim($m_clave);
                    $cod_almacen=  substr($m_clave,0,3);
                    $cod_fecha=  substr($m_clave,3,10);

                    $cod_trans=  substr($m_clave,13);
                    $se=$cod_almacen."-(".$cod_fecha.")-".$cod_trans;
                    
                $sqlupd  = "DELETE FROM val_ta_complemento WHERE trim(ch_sucursal)=trim('".$cod_almacen."') and dt_fecha='".$cod_fecha."' and trim(ch_documento)='".$cod_trans."'";
		//echo $sqlupd;

                $xsqlupd = pg_query($conector_id,$sqlupd);
		$sqleli  = "DELETE FROM val_ta_detalle WHERE  trim(ch_sucursal)=trim('".$cod_almacen."') and dt_fecha='".$cod_fecha."' and trim(ch_documento)='".$cod_trans."' ";
		$xsqleli = pg_query($conector_id,$sqleli);
		$sqlela  = "DELETE FROM val_ta_cabecera WHERE trim(ch_sucursal)=trim('".$cod_almacen."') and dt_fecha='".$cod_fecha."' and trim(ch_documento)='".$cod_trans."' ";
		$xsqlela = pg_query($conector_id,$sqlela);
                
                echo('<script languaje="JavaScript"> ');
		echo('alert(" Se Elimino Correctamente '.$se.'") ');
		echo('</script>');
		}
	}else{
		echo('<script languaje="JavaScript"> ');
		echo('alert(" Debe seleccionar un Vale !!! ") ');
		echo('</script>');
	}
        
        
        
}

if($boton == "Imprimir" or $boton == "Print") {
	echo('<script languaje="JavaScript">');
	echo("	location.href='vtacli_vales_reporte.php' " );
	echo('</script>');
}

if(strlen($v_fecha_desde) == 0 or strlen($v_fecha_hasta) == 0 ) {
	$v_fecha_hasta = date("d/m/Y", time()-(24*60*60));
	$v_fecha_desde = $v_fecha_hasta;
}

if (false) {
	$sqladd = " AND vtc.dt_fecha between '$v_fecha_desde' and '$v_fecha_hasta' ";
	$v_sql  = "SELECT ch_sucursal, dt_fecha, ch_documento FROM VAL_TA_CABECERA WHERE true ".$sqladd." ".$bddsql." ";
	$v_xsql = pg_query($conector_id,$v_sql);
	$v_ilimit = pg_num_rows($v_xsql);
	if($v_ilimit > 0) { 
		$numeroRegistros = $v_ilimit; 
	}
}

if($boton == "Consultar" or (strlen($v_fecha_desde) > 0 and strlen($v_fecha_hasta) > 0)) {
	$v_xsqlesta = pg_query($conector_id, "select tab_elemento,tab_descripcion from int_tabla_general where tab_tabla='ESTV' and tab_elemento!='000000' order by tab_elemento" );
	$sqladd = " and vtc.dt_fecha between '".$funcion->date_format($v_fecha_desde,'YYYY-MM-DD')."' and '".$funcion->date_format($v_fecha_hasta,'YYYY-MM-DD')."' ";
	$v_cadena = '';
	$v_flag = true;
	for($i = 0; $i < pg_numrows($v_xsqlesta); $i++) {
		$v_elemento = trim(pg_result($v_xsqlesta,$i,0));
		$var = 'v_estado'.$v_elemento;
		if ($$var == $v_elemento) {
			if($v_flag) { 
				$v_cadena = "'".$v_elemento."'"; 
				$v_flag = false; 
			} else {
				$v_cadena = $v_cadena.",'".$v_elemento."'"; 
			}
		}
	}
  
	if ($v_cadena != '') {
		$sqladd = $sqladd." AND vtc.ch_liquidacion<>'' OR vtc.ch_estado in ( $v_cadena ) ";
	}
	$sql2 = "SELECT vtc.ch_sucursal, vtc.dt_fecha, vtc.ch_cliente, a.cli_razsocial, vtc.ch_documento, vtc.nu_importe, vtc.ch_planilla, vtc.ch_tarjeta, vtc.ch_placa, vtc.ch_estado, vtc.ch_turno, vtc.ch_caja, vtc.ch_lado from val_ta_cabecera vtc, int_clientes a where vtc.ch_cliente=a.cli_codigo ".$sqladd." ".$bddsql." ";
	$xsql2 = pg_query($conector_id,$sql2);
	$v_ilimit=pg_num_rows($xsql2);
	if($v_ilimit > 0) { 
		$numeroRegistros = $v_ilimit; 
	}
}
?>

<html><link rel="stylesheet" href="/sistemaweb/css/sistemaweb.css" type="text/css">
<head> <title>SISTEMAWEB</title>
<script language="JavaScript" src="/sistemaweb/js/calendario.js"></script>
<script language="JavaScript" src="/sistemaweb/js/overlib_mini.js"></script>
<script language="JavaScript" src="/sistemaweb/clases/reloj.js"></script>
<script language="JavaScript" src="/sistemaweb/compras/validacion.js"></script>
<script language="JavaScript" src="/sistemaweb/compras/valfecha.js"></script>
<script>
function activa(){
	document.f_name.v_fecha_desde.select()
	document.f_name.v_fecha_desde.focus()
}

function cargavalor(valor){
	document.f_name.m_clave.value = valor;
}

</script>
</head>
<body>
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<form name="f_name" action="" method="post">
VALES DE CREDITO DESDE <?php echo $v_fecha_desde ; ?> HASTA <?php echo $v_fecha_hasta; ?> <BR>
<?php
$v_sql  = "SELECT ch_nombre_almacen FROM inv_ta_almacenes WHERE ch_almacen LIKE '%".$almacen."%' AND ch_clase_almacen='1' ";
$v_xsql = pg_query($conector_id,$v_sql);
if(pg_numrows($v_xsql) > 0) {	
	$m_descalma=pg_result($v_xsql,0,0);	
}
?>
<!-- ALMACEN ORIGEN <?php echo $almacen;?> <?php echo $m_descalma; ?>  
<input type="text" name="reloj" size="10" style="background-color : Black; color : White; font-family : Verdana, Arial, Helvetica; font-size : 8pt; text-align : center;" onfocus="window.document.f_name.reloj.blur()" > -->
<input type="text" name="m_clave" size="16" maxlength="30" value='<?php echo trim($m_clave) ; ?>'  tabindex="1"   >
<br/>ESTACION: 
<select name="cod_almacen" >
<?php
print "<option value='$cod_almacen' >$cod_almacen -- $almacen_dis</option>";
for($i = 0; $i < pg_numrows($rsx1); $i++) {
	$B = pg_fetch_row($rsx1,$i);
	print "<option value='$B[0]' >$B[0] -- $B[1]</option>";
}
?>
</select>
<hr noshade><center>
<table border="0">
	<tr>
		<th colspan="7">CONSULTAR POR RANGO DE FECHAS </th>
	</tr>
	<tr>
		<th>DESDE :</th>
		<th>
		<p><input type="text" name="v_fecha_desde" size="16" maxlength="10" value='<?php echo $v_fecha_desde ; ?>'  tabindex="1"  onKeyUp="javascript:validarFecha(this)" onblur="javascript:validarFecha(this)"  >
		<a href="javascript:show_calendar('f_name.v_fecha_desde');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;" >
	    	<img src="/sistemaweb/images/showcalendar.gif" border=0></a></p>
		</th>

		<th>HASTA:</th>
		<th>
		<p><input type="text" name="v_fecha_hasta" size="16" maxlength="10" value='<?php echo $v_fecha_hasta ; ?>'  tabindex="2" onKeyUp="javascript:validarFecha(this)" onblur="javascript:validarFecha(this)">
		<a href="javascript:show_calendar('f_name.v_fecha_hasta');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;">
	    	<img src="/sistemaweb/images/showcalendar.gif" border=0></a></p>
		</th>

		<th>
		<?php
		$v_xsqlesta = pg_query($conector_id, "SELECT tab_elemento,tab_descripcion FROM int_tabla_general WHERE tab_tabla='ESTV' and tab_elemento!='000000' order by tab_elemento" );
		$v_var = 'CHECKED';
		for($i = 0; $i < pg_numrows($v_xsqlesta); $i++) {
			$v_elemento = trim(pg_result($v_xsqlesta,$i,0));
			$v_descripcion = trim(pg_result($v_xsqlesta,$i,1));
			$var = 'v_estado'.$v_elemento;
		    	if ($$var == $v_elemento) {
				echo "<input type='checkbox' name='v_estado$v_elemento' value='$v_elemento' $v_var > $v_descripcion ";
			} else {
				echo "<input type='checkbox' name='v_estado$v_elemento' value='$v_elemento' > $v_descripcion ";
			}
		}
		// construye la lista de los estados
		$lista = ' ';
		$v_xsqlesta = pg_query($conector_id, "SELECT tab_elemento,tab_descripcion FROM int_tabla_general WHERE tab_tabla='ESTV' and tab_elemento!='000000' ");
		for($i = 0; $i < pg_numrows($v_xsqlesta); $i++) {
			$v_elemento = trim(pg_result($v_xsqlesta,$i,0));
			$var = 'v_estado'.$v_elemento;
			if ($$var == $v_elemento) {
				$lista = $lista.'&'.$var.'='.$$var;
			} else {
				$lista = $lista.'&'.$var.'= ';
			}
		}
		?>
		</th>
		<th>
		N&#186; de Doc.<input type="text" name="ch_documento" size="12" maxlength="10" value='<?php echo $_REQUEST['ch_documento']?>'> 
		</th>
		<th><input type="submit" name="boton" value="Consultar"></th>
	</tr>
</table>

<?php
$bddsql=" ";
?>

<br/>
<table border="1" cellpadding="0" cellspacing="0">
	<tr>
		<td width="1">&nbsp;</td>
		<td colspan="12" bgcolor=#A9F5F2>&nbsp;&nbsp;
		<input type="submit" name="boton" value="Agregar">&nbsp;&nbsp;
		<input type="submit" name="boton" value="Modificar">&nbsp;&nbsp;
		<input type="submit" name="boton" value="Imprimir">&nbsp;&nbsp;
		<input type="submit" name="boton" value="Complemento">
                <input type="submit" name="boton" value="Eliminar">
		<?php if ($_SESSION['autorizacion']) echo '<input type="submit" name="boton" value="Eliminar">'; ?>
		</td>
	</tr>
	<tr>
		<th>&nbsp;&nbsp;</th>
		<th bgcolor=#F4FA58>&nbsp;SUCURSAL&nbsp;</th>
		<th bgcolor=#F4FA58>&nbsp;FECHA&nbsp;</th>
		<th bgcolor=#F4FA58>&nbsp;CLIENTE&nbsp;</th>
		<th bgcolor=#F4FA58>&nbsp;RAZON SOCIAL&nbsp;</th>
      		<th bgcolor=#F4FA58>&nbsp;DOCUMENTO&nbsp;</th>
		<th bgcolor=#F4FA58>&nbsp;Nro Trans.&nbsp;</th>
		<th bgcolor=#F4FA58>&nbsp;Nro Vales&nbsp;</th>
		<th bgcolor=#F4FA58>&nbsp;IMPORTE&nbsp;</th>
		<th bgcolor=#F4FA58>&nbsp;PLANILLA&nbsp;</th>
		<th bgcolor=#F4FA58>&nbsp;TARJETA&nbsp;</th>
		<th bgcolor=#F4FA58 width="50">&nbsp;PLACA&nbsp;</th>
      		<th bgcolor=#F4FA58 width="30">&nbsp;ODOMETRO&nbsp;</th>
	</tr>
    	<?php
	if(strlen($v_fecha_desde) == 0 or strlen($v_fecha_hasta) == 0 ) {
		$dia_actual = 1;
		$mes = date("m");
		$ano = date("Y");
		$v_fecha_desde = date("Y-m")."-01";
		$ultimo_dia = ultimoDia($mes,$ano);
		$v_fecha_hasta = date("Y-m")."-".$ultimo_dia;
		$v_fecha_desde = date("d/m/Y");
		$v_fecha_hasta = date("d/m/Y");
	}
		
	$sqladd = " AND trim(vtc.ch_sucursal)=trim('$cod_almacen') AND vtc.dt_fecha between '".$funcion->date_format($v_fecha_desde,'YYYY-MM-DD')."' and '".$funcion->date_format($v_fecha_hasta,'YYYY-MM-DD')."' ";
	$v_cadena = '';
	$v_flag = true;
	for($i = 0; $i < pg_numrows($v_xsqlesta); $i++) {
		$v_elemento = trim(pg_result($v_xsqlesta,$i,0));
		$var = 'v_estado'.$v_elemento;
		if ($$var == $v_elemento) {
			if($v_flag) { 
				$v_cadena = "'".$v_elemento."'"; 
				$v_flag = false; 
			} else {
				$v_cadena = $v_cadena.",'".$v_elemento."'"; 
			}
		}
	}
	if ($v_cadena!='') {
		$sqladd = $sqladd." AND vtc.ch_liquidacion<>'' OR vtc.ch_estado in ($v_cadena) ";
	}

        if($_REQUEST['ch_documento'] != '') {
		$sqladd = " AND vtc.ch_documento = '".$_REQUEST['ch_documento']."'";
        }
	$sql2 = "SELECT 
		    	vtc.ch_sucursal, 
		    	vtc.dt_fecha, 
		    	vtc.ch_cliente, 
		    	a.cli_razsocial, 
		    	vtc.ch_documento, 
		    	vtc.nu_importe, 
		    	vtc.ch_planilla, 
		    	vtc.ch_tarjeta, 
		    	vtc.ch_placa, 
		    	vtc.ch_estado,
		    	substr(vtc.ch_documento, length(trim(vtc.ch_documento))-3,4), 
		    	vtc.ch_turno, 
		    	vtc.ch_caja, 
		    	vtc.ch_lado,
		    	vtcd.ch_numeval 
              	FROM 
			val_ta_cabecera vtc
			INNER JOIN int_clientes a ON (a.cli_codigo=vtc.ch_cliente)
			LEFT JOIN val_ta_complemento vtcd ON(vtcd.ch_sucursal=vtc.ch_sucursal and vtcd.dt_fecha=vtc.dt_fecha and vtcd.ch_documento=vtc.ch_documento)
	      	WHERE 
			vtc.ch_cliente=a.cli_codigo"." ".$sqladd." ".
		   "ORDER BY 
			ch_sucursal,
			dt_fecha, 
			substr(vtc.ch_documento, length(trim(vtc.ch_documento))-3,4) DESC".$bddsql;		
	//echo $sql2;

	$xsql2 = pg_exec($conector_id,$sql2);
	$ilimit2 = pg_numrows($xsql2);
	if($ilimit2 > 0) {
		while($irow2 < $ilimit2) {
			$a0 = pg_result($xsql2,$irow2,0);
			$a1 = pg_result($xsql2,$irow2,1);
			$a2 = pg_result($xsql2,$irow2,2);
			$a3 = pg_result($xsql2,$irow2,3);
			$a4 = pg_result($xsql2,$irow2,4);
			$a5 = pg_result($xsql2,$irow2,5);
			$a6 = pg_result($xsql2,$irow2,6);
			$a7 = pg_result($xsql2,$irow2,7);
			$a8 = pg_result($xsql2,$irow2,8);
			$a9 = pg_result($xsql2,$irow2,9);
			$a10 = pg_result($xsql2,$irow2,10);
			$a11 = pg_result($xsql2,$irow2,11);
			$a12 = pg_result($xsql2,$irow2,12);
			$a13 = pg_result($xsql2,$irow2,13);
			$a14 = pg_result($xsql2,$irow2,14);
			if($nform == $a0) {
				//echo "1";
			  	//echo "<tr><td>&nbsp;<input type='radio' name='nform' value='".$a0."' onClick='cargavalor(\" ".$a0.$a1.$a4."\")' checked></td>";
				?>
				<tr bgcolor="" onMouseOver="this.style.backgroundColor='#FFFFCC';this.style.cursor='hand';" onMouseOut="this.style.backgroundColor=''">
				<?php
				echo "<td>&nbsp;<input type='radio' name='nform' value='".$a0."' onClick='cargavalor(\" ".$a0.$a1.$a4."\")' checked></td>";
			} else {
			  	?>
			  	<tr bgcolor="" onMouseOver="this.style.backgroundColor='#FFFFCC';this.style.cursor='hand';" onMouseOut="this.style.backgroundColor=''">
			  	<?php
			  	echo "<td>&nbsp;<input type='radio' name='nform' value='".$a0."' onClick='cargavalor(\"".$a0.$a1.$a4."\")'    ></td>";
			}
			echo "<td align='center'>&nbsp;".$a0."&nbsp;</td>";
			echo "<td align='center'>&nbsp;".$a1."&nbsp;</td>";
			echo "<td align='center'>&nbsp;".$a2."&nbsp;</td>";
			echo "<td align='center'>&nbsp;".$a3."&nbsp;</td>";
			echo "<td align='center'>&nbsp;".$a4."&nbsp;</td>";
			echo "<td align='center'>&nbsp;".$a10."&nbsp;</td>";
			echo "<td align='center'>&nbsp;".$a14."&nbsp;</td>";

			$sql_vales = "SELECT ch_numeval, nu_importe FROM val_ta_complemento WHERE trim(ch_sucursal)||trim(dt_fecha)||trim(ch_documento)='".trim($a0).trim($a1).trim($a4)."'";
			//se modifico query cabecera para mostrar numero de vale 
			echo "<td align='right'>&nbsp;".$a5."&nbsp;</td>";
			echo "<td align='center'>&nbsp;".$a6."&nbsp;</td>";
			echo "<td align='center'>&nbsp;".$a7."&nbsp;</td>";
			echo "<td align='center'>&nbsp;".$a8."&nbsp;</td>";
			echo "<td align='center'>&nbsp;".$a9."&nbsp;</td>";
			$irow2++;
		}
	}
	?>
	<tr><td>&nbsp;</td><td colspan="12">&nbsp;</td></tr>
	<tr>
		<td>&nbsp;</td>
		<td colspan="12" bgcolor=#A9F5F2>&nbsp;&nbsp;
                <input type="submit" name="boton" value="Eliminar">&nbsp;&nbsp;
		<input type="submit" name="boton" value="Agregar">&nbsp;&nbsp;
		<input type="submit" name="boton" value="Modificar">&nbsp;&nbsp;
		<input type="submit" name="boton" value="Imprimir">&nbsp;&nbsp;
		<input type="submit" name="boton" value="Complemento">
		<?php if ($_SESSION['autorizacion']) echo '<input type="submit" name="boton" value="Eliminar">'; ?>
		</td>
	</tr>
</table>
</center>
</form>
</body>
</html>
<?php
if ($conector_id) 
	pg_close($conector_id);
if ($conector_repli_id) 
	pg_close($conector_repli_id);
