<?php
if($b_regresar=="Regresar") {
	header('Location: man_vtaclientes.php');
}

include("../valida_sess.php");
include("class.form2.php");

if(strlen(trim($boton))>0) {
	$ip_remoto = $_SERVER["REMOTE_ADDR"];
	
	switch($boton) {
		case Actualizar:

/*		var soloPara = formular.new_tipo.value;
		var limite_galones = formular.new_limite_galones.value;
		var limite_importe = formular.new_limite_importe.value;
		var control = formular.new_control.value;
		var nro_dia = formular.new_nro_dia.value;
*/
			$new_nro_dia = str_pad(trim($new_nro_dia), 2, "0", STR_PAD_LEFT);

			$sql = " UPDATE pos_fptshe1 SET 
							nomusu='$new_usuario', 
							numpla='$new_placa',
							ventar='$new_vencimiento',
							estblo='$new_bloqueada',
							segres='$new_segres',
							ch_tipo_producto='$new_tipo',
							nu_limite_galones='$new_limite_galones',
							nu_limite_importe='$new_limite_importe',
							ch_tipo_periodo_acumular='$new_control',
							ch_dia_de_corte='$new_nro_dia',
							dt_fecha_upd=now(),
							ch_ip_upd='$ip_remoto',
							ch_usuario_upd='$usuario' 
						WHERE numtar='$m_clave'
					";
			break;

		case Agregar:
			$sql = " INSERT INTO
							pos_fptshe1 (
								codcli, codcue, numtar, nomusu, numpla,
								ventar, estblo, segres, feccre, dt_fecha_upd,
								ch_ip_upd, ch_usuario_upd
							) VALUES (
								'$new_codigo', '$new_cta_pec', '$new_shell', '$new_usuario', '$new_placa',
								'$new_vencimiento', '$new_bloqueada', '$new_segres', now(), now(),
								'$ip_remoto', '$usuario')
					";
			var_dump($sql);
			exit();
			$mensaje = "Los datos se han ingresado correcamente"; 
			$enable = " disabled";
			break;
	}
}

//include("../valida_sess.php");
include("../functions.php");
require("../clases/funciones.php");
require("include/arrays.inc");
$funcion = new class_funciones;
// crea la clase para controlar errores
$clase_error = new OpensoftError;
$clase_error->_error();
// conectar con la base de datos
$conector_id=$funcion->conectar("","","","","");

if(strlen(trim($sql))>0) { 
	//echo $sql;
	pg_exec($conector_id,$sql); 
}

if($m_clave) {
	$accion = "Actualizar";
	$v_estado = "readonly";
} else {
	$accion = "Agregar";
}

$sql = " SELECT
				codcli, codcue ,numtar, nomusu, numpla, ventar, estblo, cli_grupo,
				to_char(feccre, 'DD/MM/YYYY'), estarj, segres, ch_tipo_producto,
				nu_limite_galones, nu_limite_importe, ch_tipo_periodo_acumular,
				ch_dia_de_corte, nu_galones_acumulados, nu_ant_galones_acumulados,
				nu_importe_acumulado, nu_ant_importe_acumulado 

			FROM pos_fptshe1 she, int_clientes clie
			
			WHERE trim(codcli)=trim(cli_codigo) AND numtar='$m_clave'
		";

if($m_clave!="") {
	$xsql = pg_query($conector_id, $sql);
	$rs = pg_fetch_array($xsql);
	
	$new_codigo = trim($rs[0]);
	$new_cta_pec = trim($rs[1]);
	$new_shell = trim($rs[2]);
	$new_usuario = trim($rs[3]);
	$new_placa = trim($rs[4]);
	$new_vencimiento = trim($rs[5]);
	$new_bloqueada = trim($rs[6]);
	
	$grupo_shell = trim($rs[7]);
	$fecha_registro = trim($rs[8]);
	
				
	
	if(trim($rs[9])=="1") {
		$estado = "EMITIDA";
	} else {
		$estado = "NO EMITIDA";
	}
	
	$new_segres = trim($rs[10]);
	
	$new_tipo = trim($rs[11]);
	$new_limite_galones = trim($rs[12]);
	$new_limite_importe = trim($rs[13]);
	$new_control = trim($rs[14]);
	$new_nro_dia = trim($rs[15]);

	$consumo_acum_galones=trim($rs[16]);
	$consumo_anterior_galones=trim($rs[17]);
	
	$consumo_acum_importe=trim($rs[18]);
	$consumo_anterior_importe=trim($rs[19]);

}

if(strlen(trim($new_nro_dia))==0) {
	$new_nro_dia="1";
}

include "inc_top.php";
?>

<head>
	<script language="JavaScript" src="js/obj-ajax.js"></script>
	<script language="JavaScript" src="js/miguel.js"></script>
	<script language="JavaScript">
	function Regresar() {
		location.href='man_vtaclientes.php';
	}

	function validarFormulario() {
		var valido = true;
		var mensaje = '';

		var codigo = document.formular.new_codigo
		var cta_pec = document.formular.new_cta_pec
		var shell = document.formular.new_shell
		var usuario = document.formular.new_usuario
		var placa = document.formular.new_placa
		var vencimiento = document.formular.new_vencimiento
		var bloqueada = document.formular.new_bloqueada
		var segres = document.formular.new_segres

		var limite_galones = document.formular.new_limite_galones
		var limite_importe = document.formular.new_limite_importe

		if(codigo.value=='') {
			valido = false; mensaje = 'CODIGO EN BLANCO'; codigo.focus();
		}
		if(cta_pec.value=='') {
			valido = false; mensaje = 'Cuenta PEC EN BLANCO'; cta_pec.focus();
		}
		if(shell.value=='') {
			valido = false; mensaje = 'Nro. Tarjeta EN BLANCO'; shell.focus();
		}
		if(usuario.value=='') {
			valido = false; mensaje = 'Usuario EN BLANCO'; usuario.focus()
		}
		if(placa.value=='') {
			valido = false; mensaje = 'Placa EN BLANCO'; placa.focus();
		}
		if(vencimiento.value=='') {
			valido = false; mensaje = 'Vencimiento EN BLANCO'; vencimiento.focus();
		}
		if(bloqueada.value=='') {
			valido = false; mensaje = 'Bloqueada EN BLANCO'; bloqueada.focus();
		}
		if(segres.value=='') {
			valido = false; mensaje = 'Segres EN BLANCO'; segres.focus();
		}

		if(limite_galones.value=='') {
			valido = false; mensaje = 'Limite de Galones \nEN BLANCO'; limite_galones.focus();
		}
		if(limite_importe.value=='') {
			valido = false; mensaje = 'Limite de Importe \nEN BLANCO'; limite_importe.focus();
		}

		if(valido==true) {
			document.formular.submit();
		} else {
			alert(mensaje);
		}
	}

	function cambiarDias() {
		var selec = document.formular.new_control.options;
		var combo = document.formular.new_nro_dia.options;
		combo.length = null;

		if (selec[0].selected == true) {
			if(1==<?php echo ($new_nro_dia); ?>) {
				combo[0] = new Option("Domingo","1","defauldSelected","defauldSelected");
			} else {
				combo[0] = new Option("Domingo","1","","");
			}

			if(2==<?php echo ($new_nro_dia); ?>) {
				combo[1] = new Option("Lunes","2","defauldSelected","defauldSelected");
			} else {
				combo[1] = new Option("Lunes","2","","");
			}

			if(3==<?php echo ($new_nro_dia); ?>) {
				combo[2] = new Option("Martes","3","defauldSelected","defauldSelected");
			} else {
				combo[2] = new Option("Martes","3","","");
			}

			if(4==<?php echo ($new_nro_dia); ?>) {
				combo[3] = new Option("Miercoles","4","defauldSelected","defauldSelected");
			} else {
				combo[3] = new Option("Miercoles","4","","");
			}

			if(5==<?php echo ($new_nro_dia); ?>) {
				combo[4] = new Option("Jueves","5","defauldSelected","defauldSelected");
			} else {
				combo[4] = new Option("Jueves","5","","");
			}

			if(6==<?php echo ($new_nro_dia); ?>) {
				combo[5] = new Option("Viernes","6","defauldSelected","defauldSelected");
			} else {
				combo[5] = new Option("Viernes","6","","");
			}

			if(7==<?php echo ($new_nro_dia); ?>) {
				combo[6] = new Option("Sabado","7","defauldSelected","defauldSelected");
			} else {
				combo[6] = new Option("Sabado","7","","");
			}
		}

		if(selec[1].selected == true) {
			var i=0;
			while(i<28) {
				i++;
				if(i==<?php echo ($new_nro_dia); ?>) {
					combo[i-1] = new Option(i,i,"defauldSelected","defauldSelected");
				} else {
					combo[i-1] = new Option(i,i,"","");
				}
			}
		}
	}

	function enviarData() {
		var soloPara = formular.new_tipo.value;
		var limite_galones = formular.new_limite_galones.value;
		var limite_importe = formular.new_limite_importe.value;
		var control = formular.new_control.value;
		var nro_dia = formular.new_nro_dia.value;
		
		alert(soloPara+' = '+limite_galones+' = '+limite_importe+' = '+control+' = '+nro_dia);
	}
	
</script>
<style type="text/css">
<!--
.borde-cuadro {
	border: 1px solid #006666;
}
-->
</style>
</head>


<body>
	<div align="center">Tarjeta de Credito Shell :: <?php echo $mensaje; ?></div>
	<form name="formular" method="post" action="">
		<table width="769" border="0" align="center">
			<tr> 
				<td width="400" bgcolor="#006666" class="borde-cuadro">
					<font color="#FFFFFF">DATOS DE TARJETA</font>
				</td>
				<td width="4">
					<font color="#FFFFFF">&nbsp;</font>
				</td>
				<td width="400" bgcolor="#006666" class="borde-cuadro">
					<font color="#FFFFFF">RESTRICCIONES DE DESPACHO</font>
				</td>
			</tr>
			<tr> 
				<td>
					<table width="400" border="0" class="borde-cuadro">
						<tr> 
							<td width="157">Cod. Cliente</td>
							<td width="8">:</td>
							<td>
								<input name="new_codigo" type="text" id="new_codigo" value="<?php echo $new_codigo; ?>" size="8" maxlength="6" <?php echo $v_estado; ?> >
								<input name="imgprov" type="image" src="/sistemaweb/images/help.gif" width="16" height="15" border="0" onClick="javascript:mostrarAyuda('lista_ayuda.php','formular.new_codigo', 'formular.desc_cliente','clientes')">
								<input readonly size='30' type="text" name="desc_cliente" style="font-size:8px;" value="<?php echo trim($desc_cliente); ?>">
							</td>
						</tr>
						<tr> 
							<td>Nro. Cuenta</td>
							<td>:</td>
							<td>
								<input name="new_cta_pec" type="text" id="new_cta_pec" value="<?php echo $new_cta_pec; ?>" size="13" maxlength="10" <?php echo $v_estado; ?> >
							</td>
						</tr>
						<tr> 
							<td>Nro. Tarjeta Shell</td>
							<td>:</td>
							<td>
								<input name="new_shell" type="text" id="new_shell" value="<?php echo $new_shell; ?>" size="13" maxlength="10" <?php echo $v_estado; ?>  onChange="javascript:cargarContenido(this.value, 'mensaje', 'verificar.php')"><br>
								<div id='mensaje' class="MsgError"></div><!--<input name="imgprov" type="image" src="/sistemaweb/images/help.gif" width="16" height="15" border="0" onClick="javascript:mostrarAyudaCD('lista_ayuda.php','formular.new_shell','formular.new_codigo','tarjetas')">-->
							</td>
						</tr>
						<tr> 
							<td>Usuario</td>
							<td>:</td>
							<td>
								<input name="new_usuario" type="text" id="new_usuario" value="<?php echo $new_usuario; ?>" size="40" maxlength="40">
							</td>
						</tr>
						<tr> 
							<td>Placa Vehiculo</td>
							<td>:</td>
							<td>
								<input name="new_placa" type="text" id="new_placa" value="<?php echo $new_placa; ?>" size="10" maxlength="8">
							</td>
						</tr>
						<tr> 
							<td>Vencimiento</td>
							<td>:</td>
							<td width="219">
								<input name="new_vencimiento" type="text" id="new_vencimiento" value="<?php echo $new_vencimiento; ?>" size="13" maxlength="10">
							</td>
						</tr>
						<tr>
							<td>T. Bloqueada S/N</td>
							<td>:</td>
							<td>
								<select name="new_bloqueada">
								<?php
								foreach($condincionSN as $llave => $valor) {
									if($llave == $new_bloqueada) {
										echo "<option value='$llave' selected>$valor</option>\n";
									} else {
										echo "<option value='$llave' >$valor</option>\n";
									}
								}
								?>	
								</select>
								<!--<input name="new_bloqueada" type="text" id="new_bloqueada" value="<?php //echo $new_bloqueada; ?>" size="2" maxlength="1">-->
							</td>
						</tr>
						<tr>
							<td colspan="3"><hr></td>
						</tr>
						<tr>
							<td>Grupo Tarj. Shell</td>
							<td>:</td>
							<td><?php echo $grupo_shell; ?></td>
						</tr>
						<tr>
							<td>Fecha de Registro</td>
							<td>:</td>
							<td><?php echo $fecha_registro; ?></td>
						</tr>
						<tr>
							<td>Estado</td>
							<td>:</td>
							<td><?php echo $estado; ?></td>
						</tr>
						<tr>
							<td>Genero a SEGRES S/N</td>
							<td>:</td>
							<td>
								<select name="new_segres">
								<?php
								foreach($condincionSN as $llave => $valor) {
									if($llave == $new_segres) {
										echo "<option value='$llave' selected>$valor</option>\n";
									} else {
										echo "<option value='$llave' >$valor</option>\n";
									}
								}
								?>
								</select>
								<!--<input name="new_segres" type="text" id="new_segres" value="<?php //echo $new_segres; ?>" size="2" maxlength="1">-->
							</td>
						</tr>
						<tr>
							<td>
								<div align="center"> 
									<input name="button" type="button" onClick="javascript:Regresar()" value="Regresar">
								</div>
							</td>
							<td><div align="center"></div></td>
							<td>
								<div align="center"> 
									<input name="button" type="button" onClick="javascript:validarFormulario()" value="<?php echo $accion; ?>" <?php echo $enable; ?>>
								</div>
							</td>
						</tr>
					</table>
				</td>
				<td>&nbsp;</td>
				<td valign="top">
					<table width="340" border="0" class="borde-cuadro">
						<tr> 
							<td width="193" height="27">Solo para</td>
							<td width="7">:</td>
							<td width="123">
								<select name="new_tipo">
									<option value="C" <?php if($new_tipo=="C"){ echo "selected"; } ?> >Combustible</option>
									<option value="M" <?php if($new_tipo=="M"){ echo "selected"; } ?> >Market</option>
									<option value="" <?php if($new_tipo==""){ echo "selected"; } ?>>Ambos</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>Limite Galones</td>
							<td>:</td>
							<td><input name="new_limite_galones" type="text" id="new_limite_galones" value="<?php echo $new_limite_galones; ?>" size="13" maxlength="10"></td>
						</tr>
						<tr>
							<td>Limite Importe</td>
							<td>:</td>
							<td><input name="new_limite_importe" type="text" id="new_limite_importe" value="<?php echo $new_limite_importe; ?>" size="13" maxlength="10"></td>
						</tr>
						<tr>
							<td>Control de Limite</td>
							<td>:</td>
							<td>
								<select name="new_control" onChange="cambiarDias()" >
									<option value="S" <?php if($new_control=="S"){ echo "selected"; } ?> >Semanal</option>
									<option value="M" <?php if($new_control=="M"){ echo "selected"; } ?> >Mensual</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>Dia de Semana o Mes de Corte</td>
							<td>:</td>
							<td>
								<select name="new_nro_dia" size="1" id="new_nro_dia">
									<option value="01">1</option>
								</select>
							</td>
						</tr>
					</table><br>
					<table width="340" border="0" class="borde-cuadro">
						<tr>
							<td width="195">Galones Acumulado Anterior</td>
							<td width="9">:</td>
							<td width="121"><?php echo $consumo_anterior_galones; ?></td>
						</tr>
						<tr>
							<td>Galones Acumulado al Perido</td>
							<td>:</td>
							<td><?php echo $consumo_acum_galones; ?></td>
						</tr>
						<tr>
							<td height="8"></td>
							<td></td>
							<td></td>
						</tr>
						<tr>
							<td>Importe Acumulado Anterior</td>
							<td>:</td>
							<td><php? echo $consumo_anterior_importe; ?></td>
						</tr>
						<tr> 
							<td>Importe Acumulado al Perido</td>
							<td>:</td>
							<td><?php echo $consumo_acum_importe; ?></td>
						</tr>
					</table><br>
				</td>
			</tr>
		</table><br>
		<input type="hidden" name="boton" value="<?php echo $accion; ?>">  
	</form>
</body>
<script language="JavaScript">
	cambiarDias();
</script>
<?php pg_close($conector_id); ?>
