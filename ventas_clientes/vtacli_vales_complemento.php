<?php
if($boton == "Regresar") {
	header("Location: vtacli_vales.php?v_fecha_desde=".$_REQUEST['v_fecha_desde']."&v_fecha_hasta=".$_REQUEST['v_fecha_hasta']."");
	exit;
}

include("../menu_princ.php");
include("../include/functions.php");
require("../clases/funciones.php");
$funcion = new class_funciones;
$clase_error = new OpensoftError;
$conector_id = $funcion->conectar("","","","","");

if($_REQUEST['m_almacen']) {
    /*$conector_repli_id = $funcion->conectar("","","acosa_backups","","");
    $Datos['Ip_Estacion']  = ObtenerIPAlmacen($conector_repli_id, trim($_REQUEST['m_almacen']));
    $Datos['Cod_Estacion'] = $_REQUEST['m_almacen'];
    //print_r($Datos);*/
}

if($boton == "Modificar cabecera") {
	$sql3 = "select
			CH_SUCURSAL,
			DT_FECHA,
			CH_CLIENTE,
			CH_DOCUMENTO,
			CH_PLANILLA,
			CH_PLACA,
			NU_ODOMETRO
			from VAL_TA_CABECERA
			where trim(CH_SUCURSAL||DT_FECHA||CH_DOCUMENTO)='".$m_clave."' " ;

	$xsql3 	 = pg_query($conector_id,$sql3);
	$ilimit3 = pg_numrows($xsql3);

	$cod_cliente  = trim(pg_result($xsql3,0,'CH_CLIENTE'));
	$cod_planilla = trim(pg_result($xsql3,0,'CH_PLANILLA'));
	$nro_placa    = trim(pg_result($xsql3,0,'CH_PLACA'));
	$odometro     = trim(pg_result($xsql3,0,'NU_ODOMETRO'));

	$sql3 = "select trim(ch_apellido_paterno)
			||' '||trim(ch_apellido_materno)
			||' '||trim(ch_nombre1)
			||' '||trim(ch_nombre2)
			as clave from pla_ta_trabajadores
			where ch_codigo_trabajador='".$cod_planilla."';" ;

	$xsql3 = pg_query($conector_id,$sql3);
	$cod_descplanilla = trim(pg_result($xsql3,0));

	$v_modificar_cabecera = "Grabar cabecera";
	$v_estado_cabecera = " ";
	$boton = " ";
} else {
	if ($boton == "Grabar cabecera" and strlen($m_cantidadpedida)>0 and strlen($m_precio)>0) {
		if(strlen($cod_cliente)>0) {
			$sqlprov = "select cli_codigo,cli_razsocial
						from INT_clientes
						where cli_codigo='".$cod_cliente."' ";
			$xsqlprov = pg_query($conector_id,$sqlprov);
		   	if(pg_numrows($xsqlprov)>0) {
				$cod_cliente  = pg_result($xsqlprov,0,0);
				$desc_cliente = pg_result($xsqlprov,0,1);
		    	}
		}
		$cod_cliente	= trim($cod_cliente);
		$cod_planilla	= trim($cod_planilla);
		$nro_placa	= trim($nro_placa);
		$odometro	= trim($odometro);

		$v_sql = "UPDATE val_ta_cabecera SET ".
					       "ch_cliente='$cod_cliente', ".
					       "ch_planilla='$cod_planilla', ".
					       "ch_placa='$nro_placa', ".
					       "nu_odometro=".$odometro.", ".
					       "ch_estado='1' ".
		       "WHERE trim(ch_sucursal||dt_fecha||ch_documento)='$m_clave'";

		$boton = " ";
		$v_modificar_cabecera = "Modificar cabecera";
		$v_estado_cabecera = "disabled";
	}
	if (is_null($v_modificar_cabecera)) {
		$v_modificar_cabecera = "Modificar cabecera";
		$v_estado_cabecera = "disabled";
	}
}

if($boton == "Modificar") {
    	$sql = "SELECT trim(ch_sucursal)||dt_fecha||trim(ch_documento)||trim(ch_numeval)
	    		,ch_numeval
			,nu_importe
			FROM val_ta_complemento 
			WHERE trim(CH_SUCURSAL)||DT_FECHA||trim(CH_DOCUMENTO)='".$m_clave."'";

    	$xsqlm   = pg_query($conector_id,$sql);
    	$ilimitm = pg_num_rows($xsqlm);
    	$irowm   = 0;

    	while($irowm<$ilimitm) {
		$am0 = pg_result($xsqlm,$irowm,0);
		$idm[$am0] = $am0;

		if($idm[$am0] == $idp[$am0]) {
	    		$nume_val = trim($m_nro_vale[$am0]);
	    		$nume_val = completarCeros($nume_val,10,"0");
    
	    		$sqlupd = "UPDATE val_ta_complemento SET ".
			   		"ch_numeval='".$nume_val."', ".
			   		"nu_importe='".$m_importe_vale[$am0]."' ".
		    		"WHERE trim(ch_sucursal)||dt_fecha||trim(ch_documento)||trim(ch_numeval)='".$idm[$am0]."'";  
			$rpt = pg_query($conector_id,$sqlupd); 

		}
		$irowm++;
    	}
}

if($boton == "Eliminar") {
    	$sql = "SELECT trim(ch_sucursal)||dt_fecha||trim(ch_documento)||trim(ch_numeval),
		    ch_numeval,
		    nu_importe 
	    	FROM val_ta_complemento 
	    	WHERE trim(ch_sucursal)||dt_fecha||trim(ch_documento)='".$m_clave."'";
    
	$xsqlm = pg_exec($conector_id,$sql);
	$ilimitm = pg_numrows($xsqlm);
	$irowm = 0;
	while($irowm < $ilimitm) {
	    $am0 = pg_result($xsqlm,$irowm,0);
	    $idm[$am0] = $am0;
	    if($idm[$am0] == $idp[$am0]) {
		    $sqlupd = "DELETE FROM val_ta_complemento ".
			    "WHERE ".
			    "trim(ch_sucursal)||dt_fecha||trim(ch_documento)||trim(ch_numeval)='".$idm[$am0]."'";
		    //echo $sqlupd;
		    $xsqlupd = pg_exec($conector_id,$sqlupd);
	    }
	    $irowm++;
	}
}

if($boton == "Ins" or $boton == "Agregar") {
	$okgraba = true;
        global $usuario;

	$m_almacen = trim($m_almacen);
	$fecha_val = $funcion->date_format($fecha_val,'YYYY-MM-DD');
	$nro_vale = trim($nro_vale);
	$v_nro_vale=trim($v_nro_vale);
	$v_nro_vale=completarCeros($v_nro_vale,10,"0");
	$v_importe=trim($v_importe);
	$usuario_nombre = substr($usuario->nombre,0,5);

	if(strlen(trim($v_importe)) == 0) {
		$v_mensaje = " No se puede Agregar \\n Importe en Cero !!! ";	$okgraba=false;
	}

	if(strlen(trim($v_nro_vale))==0) {
		$v_mensaje = " No se puede Agregar \\n NRO VALE vacio !!! ";	$okgraba=false;
	}

	$ip_remote = $_SERVER['REMOTE_ADDR'];

	if($okgraba) {
	    	$sql = "INSERT INTO val_ta_complemento ".
						"( ".
						"ch_sucursal, ".
						"dt_fecha, ".
						"ch_documento, ".
						"ch_numeval, ".
						"nu_importe, ".
						"ch_estado, ".
						"dt_fechaactualizacion, ".
						"ch_usuario, ".
						"ch_auditorpc ".
						") ".
					"VALUES ( ".
						"'$m_almacen', ".
						"'$fecha_val', ".
						"'$nro_vale', ".
						"'$v_nro_vale', ".
						"'$v_importe', ".
						"'1', ".
						"now(), ".
						"'$usuario_nombre', ".
						"'$ip_remote' ".
						")";
	    	//echo $sql;

	    	$xsql = pg_query($conector_id, $sql);
	    	$m_clave = $m_almacen.$fecha_val.$nro_vale;
	    	$boton = "";
	    	$v_nro_vale = "";
	    	$v_importe = "";
	    	echo("<script>");
	    	echo("	location.href='vtacli_vales_complemento.php?v_fecha_desde=".$_REQUEST['v_fecha_desde']."&v_fecha_hasta=".$_REQUEST['v_fecha_hasta']."&m_clave=".$m_clave."'" );
	    	echo("</script>");
	} else {
		echo('<script languaje="JavaScript"> ');
		echo('alert("'.$v_mensaje.'"); ');
		echo('</script>');
	}
}

?>
<html><head>
<script language="JavaScript" src="js/miguel.js"></script>
<script language="JavaScript" src="/sistemaweb/clases/calendario.js"></script>
<script language="JavaScript" src="/sistemaweb/clases/overlib_mini.js"></script>
<script language="JavaScript" src="/sistemaweb/compras/validacion.js"></script>

<script language="javascript">
var miPopup
function abrealma(){
	miPopup = window.open("../maestros/escogealmacen.php?k_variable=formular.m_almacen","miwin","width=500,height=400,scrollbars=yes")
	miPopup.focus()
	}

function abrecliente() {
	miPopup = window.open("../maestros/escogecliente.php?k_variable=formular.cod_cliente","miwin","width=500,height=400,scrollbars=yes")
	miPopup.focus()
	}

function abrearti() {
	miPopup = window.open("../maestros/escogearticulo.php?k_variable=formular.v_art_codigo","miwin","width=550,height=400,scrollbars=yes")
	miPopup.focus()
	}

function abretabla( tabla ,k_var ){
	miPopup = window.open("../maestros/escogetabla.php?m_tabla="+tabla+"&k_variable="+k_var+" ","miwin","width=600,height=350,scrollbars=yes")
	miPopup.focus()
	}

function enviadatos(){$v_nro_vale=completarCeros($v_nro_vale,10,"0");
	document.formular.submit()
	}

function enviapago(){
	document.formular.m_formapago.value=" ";
	}

function verifica_arti(arti){
	var vr1 = new Array();
	var valor = arti.value;
	var existe = false;

	for(i=0;i<vr1.length;i++){
		if( vr1[i]== valor){  existe = true;   }
		}
	if (!existe) {
		alert('El codigo de articulo no existe ');
		}
	}

</script>
</head>
<body>
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<form name="formular" action="vtacli_vales_complemento.php?m_clave=<?php echo $m_clave;?>" method="post">
<?php

//echo "ESTA ES LA CLAVE ".$m_clave;

$sql3 = "select CH_SUCURSAL,
			  DT_FECHA,
			  CH_CLIENTE,
			  CH_DOCUMENTO,
			  CH_PLANILLA,
			  CH_PLACA,
			  NU_ODOMETRO
			  from VAL_TA_CABECERA
			  where trim(CH_SUCURSAL||DT_FECHA||CH_DOCUMENTO)='".$m_clave."'";

$xsql3	 = pg_query($conector_id,$sql3);
$ilimit3 = pg_numrows($xsql3);

$m_almacen = pg_result($xsql3,0,'CH_SUCURSAL');
$fecha_val = $funcion->date_format(pg_result($xsql3,0,'DT_FECHA'),'DD/MM/YYYY');

$nro_vale 	= pg_result($xsql3,0,'CH_DOCUMENTO');
$cod_planilla  	= pg_result($xsql3,0,'CH_PLANILLA');
$nro_placa	= pg_result($xsql3,0,'CH_PLACA');
$odometro	= pg_result($xsql3,0,'NU_ODOMETRO');

if ($v_estado_cabecera == "disabled") {
	$cod_cliente = pg_result($xsql3,0,'CH_CLIENTE');

	$sql3 = "select trim(ch_apellido_paterno)
			||' '||trim(ch_apellido_materno)
			||' '||trim(ch_nombre1)
			||' '||trim(ch_nombre2)
			as clave from pla_ta_trabajadores
			where ch_codigo_trabajador='".$cod_planilla."';" ;

	$xsql3 = pg_query($conector_id,$sql3);
	$cod_descplanilla = trim(pg_result($xsql3,0));
}
$m_cantidadpedida = 0;
$m_precio = 0;

?>

<input type="hidden" name="fecha_val" value='<?php echo $fecha_val;?>'>
<input type="hidden" name="m_almacen" value='<?php echo $m_almacen;?>'>
<input type="hidden" name="nro_vale" value='<?php echo $nro_vale;?>'>
<input type="hidden" name="v_fecha_desde" value='<?php echo $_REQUEST['v_fecha_desde'];?>'>
<input type="hidden" name="v_fecha_hasta" value='<?php echo $_REQUEST['v_fecha_hasta'];?>'>

<input type="hidden" name="v_modificar_cabecera" value='<?php echo $v_modificar_cabecera;?>'>
<input type="hidden" name="v_estado_cabecera" value='<?php echo $v_estado_cabecera;?>'>

<table border="0" >
	<tr>
		<th width="500">VALES DE CREDITO - COMPLEMENTO</th>
	</tr>
</table>
<table border="0" >

	<tr>
		<th>ALMACEN</th>
		<td>:</td>
		<td>&nbsp;<?php echo $m_almacen;?>
		<?php
		if( strlen($m_almacen)>0 ) {
			$sqlalma = "select trim(ch_sucursal) as cod, ch_nombre_almacen
					  from inv_ta_almacenes
					  where ch_almacen like '%".$m_almacen."%' and ch_clase_almacen='1' ";

			$xsqlalma = pg_query($conector_id,$sqlalma);
			if(pg_numrows($xsqlalma)>0) {
				$m_descalma = pg_result($xsqlalma,0,1);
				echo $m_descalma;
			}
		}
		?>
		</td>
	<tr>
	<th>FECHA</th>
		<td>:</td>
		<td>
		<p>
		<input type="hidden" name="fecha_val" size="16" value="<?php echo $fecha_val; ?>">
		<?php echo $fecha_val; ?>
		</p>
		</td>
	<tr>
		<th>NUMERO VALE</th>
		<td>:</td>
		<td><?php echo $nro_vale; ?><input name="nro_vale" type="hidden" maxlength="10" value="<?php echo $nro_vale;?>">
		</td>
	<tr>
		<th>CLIENTE</th>
		<td>:</td>
		<td>&nbsp;
		<?php
		if(strlen($cod_cliente)>0) {
			$sqlprov = "select CLI_RAZSOCIAL
						from INT_CLIENTES
						where CLI_CODIGO='".$cod_cliente."'";
			$xsqlprov = pg_query($conector_id,$sqlprov);
			if(pg_numrows($xsqlprov)>0) {
				$desc_cliente = pg_result($xsqlprov,0,0);
			} else {
				echo('<script languaje="JavaScript"> ');
				echo('alert(" No Existe Cliente !!! "); ');
   				echo('</script>');
			}
			}
		?>

		<input name="cod_cliente" type="text" size="15" maxlength="12" value="<?php echo $cod_cliente;?>" <?php echo $v_estado_cabecera; ?>>
<!--		<img src="../images/help.gif" width="16" height="15" onClick="javascript:mostrarAyuda('lista_ayuda.php','formular.cod_cliente','formular.cod_cliente','clientes')" <?php echo $v_estado_cabecera; ?>>
	-->
		<input name="imgprov" type="image" src="/sistemaweb/images/help.gif" width="16" height="15" border="0" onClick="javascript:mostrarAyuda('lista_ayuda.php','formular.cod_cliente','formular.desc_cliente','clientes')" <?php echo $v_estado_cabecera; ?> >
		<input readonly size='30' type="text" name="desc_cliente" value="<?php echo trim($desc_cliente); ?>" <?php echo $v_estado_cabecera; ?>> </td>

		<th>NRO_PLACA</th>
		<td>:</td>
		<td><input name="nro_placa" type="text" value="<?php echo trim($nro_placa);?>" size="16" maxlength="10" <?php echo $v_estado_cabecera; ?> >
		</td>
	<tr>
		<th>TRABAJADOR</th>
		<td>:</td>
		<td>
		<input type="text" name="cod_planilla"  value='<?php echo $cod_planilla; ?>' maxlength="10" <?php echo $v_estado_cabecera; ?>>
		<img src="../images/help.gif" width="16" height="15" onClick="javascript:mostrarAyuda('lista_ayuda.php','formular.cod_planilla','formular.cod_descplanilla','trabajadores')" <?php echo $v_estado_cabecera; ?>>
		<input type="text" name="cod_descplanilla" size="35" readonly="true" value='<?php echo $cod_descplanilla; ?>' <?php echo $v_estado_cabecera; ?>>

		<th>ODOMETRO</th>
		<td>:</td>
		<td><input name="odometro" type="text" value="<?php echo $odometro;?>" size="16" maxlength="10" onkeyup='validarNumeroDecimales(this)' <?php echo $v_estado_cabecera; ?>>
		</td>
	</tr>
</table>

NRO VALES COMPLEMENTO :

<table border="1" cellpadding="0" cellspacing="0" >
	<tr>
		<th>&nbsp;</th>
		<th>NRO VALE</th>
		<th>IMPORTE</th>
	</tr>
	<tr>
		<th>&nbsp;</th>
		<th><input type="text" name="v_nro_vale" size='12' maxlength="10" value="<?php echo $v_nro_vale;?>" onkeyup='validarNumeroEntero(this)' >
		<th><input name="v_importe" type="text" size='10' maxlength="8" value="<?php echo $v_importe;?>" onkeyup='validarNumeroDecimales(this)' ></th>
		<th><input type="submit" name="boton" value="Agregar" ></th>
	</tr>
    <?php
	$sql3 = "SELECT trim(ch_sucursal)||dt_fecha||trim(ch_documento)||trim(ch_numeval)
					,ch_numeval
					,nu_importe
				FROM
					val_ta_complemento
				WHERE
					trim(CH_SUCURSAL)||DT_FECHA||trim(CH_DOCUMENTO)='".$m_clave."'";

	//echo $sql3;
	$xsql3 = pg_query($conector_id,$sql3);
	$ilimit3 = pg_numrows($xsql3);
	while($irow3 < $ilimit3) {
		$rs  = pg_fetch_array($xsql3, $irow3);
		$a   = $rs[0];
		$ad0 = $rs[0];
		$ad1 = $rs[1];
		$ad2 = $rs[2];

		echo "<tr>";
		echo "<td><input type='checkbox' name='idp[$a]' value='$ad0'></td>";
		echo "<td><input type='text' name='m_nro_vale[$a]' value='$ad1' maxlength='10' size='12'></td>";
		echo "<td><input type='text' name='m_importe_vale[$a]' value='$ad2' maxlength='8' size='10'></td>";
		echo "</tr>";
		$irow3++;
	}
	?>
	<tr>
		<td>&nbsp;</td>
		<td><input type="submit" name="boton" value="Eliminar"></td>
		<td><input type="submit" name="boton" value="Modificar">
		&nbsp;&nbsp;
		<input type="submit" name="boton" value="Regresar"></td>
	</tr>
</table>
</form>
</body>
</html>

<?php
if ($conector_id) pg_close($conector_id);
if ($conector_repli_id) pg_close($conector_repli_id);
$clase_error->_error();
