<?php

class matricula_personal_Template extends Template {
	function titulo() {
		$titulo = '<div align="center"><h2>Facturaci&oacute;n</h2></div><hr>';
		return $titulo;
	}

	function errorResultado($errormsg) {
		return '<blink>' . $errormsg . '</blink>';
	}

	function FormularioPrincipal() {
?>

<div align="center">
	<h2 style="color:#336699;">Asignaci&oacute;n de Trabajadores</h2>
	<table border="0" style="width: 420px">
		<tr>
			<td align="right">Sucursal: </td>
			<td>
				<select id='id_scucursal' class='fecha_formato'></select>
			</td>
		</tr>
		<tr>
			<td align="right">Fecha Inicio: </td>
			<td><input type='text' id='fecha_inicio' class='fecha_formato' /></td>
		<tr>
		<tr>
			<td align="right">Turno: </td>
			<td>
				<select id="id_turno">
					<option value="-1">Seleccione</option>
					<option value="1" selected>Turno 1</option>
					<option value="2">Turno 2</option>
					<option value="3">Turno 3</option>
					<option value="4">Turno 4</option>
					<option value="5">Turno 5</option>
					<option value="6">Turno 6</option>
					<option value="7">Turno 7</option>
					<option value="8">Turno 8</option>
					<option value="9">Turno 9</option>
					<option value="10">Turno 10</option>
					<option value="11">Turno 11</option>
					<option value="12">Turno 12</option>
					<option value="13">Turno 13</option>
					<option value="14">Turno 14</option>
					<option value="15">Turno 15</option>
					<option value="16">Turno 16</option>
					<option value="17">Turno 17</option>
					<option value="18">Turno 18</option>
					<option value="19">Turno 19</option>
				</select>
			</td>
		</tr>
		<tr>
			<td colspan="2" align="center">
				<button id="btnmatricular_pv"><img align="right" src="/sistemaweb/images/search.png"/>Programar POS</button>
				<button id="btnmatricular"><img align="right" src="/sistemaweb/images/search.png"/>Programar Lados</button>
				<button id="id_regresar"><img align="right" src="/sistemaweb/icons/atra.gif">Regresar</button>
			</td>
		</tr>
	</table>
</div>
<div align="center" id="contenidoTablaSelecionar" style="margin-top: 30px;"></div>

<?php
	}

	function Inicio() {
?>
<div align="center">
	<h2 style="color:#336699;">Personal por Isla / Punto</h2>
	<table border='0' style="width: 420px">
		<tr>
			<td align="right">Sucursal: </td>
			<td>
				<select id='id_scucursal' class='fecha_formato'></select>
			</td>
		</tr>
		<tr>
			<td align="right">Fecha Inicio: </td>
			<td><input type='text' id='fecha_inicio' class='fecha_formato' /></td>
		</tr>
		<tr>
			<td align="right">Fecha Final: </td>
			<td><input type='text' id='fecha_final' class='fecha_formato' /></td>
		</tr>
		<tr>
			<td align="right">Cod trabajador: </td>
			<td><input type='text' id='cod_traba' class='fecha_formato' /></td>
		</tr>
		<tr>
			<td colspan="2" align="center">
				<button id="btnver_registro"><img align="right" src="/sistemaweb/images/search.png"/>Consultar</button>
				<button id="btnver_pdf" onclick="ver_tabajadores();"><img align="right" src="/sistemaweb/images/search.png"/>Ver reporte</button>
				<button id="btnnuevo_registro"><img align="right" src="/sistemaweb/images/search.png">Nueva Asignacion</button>
			</td>
		</tr>
	</table>
</div>
<div align="center" id="contenidoTablaSelecionar" style="width: auto;margin-top: 30px;"></div>

<?php
	}

	function CrearTablaMatricula($lados, $trabajores, $punto_vt_market, $selecionado) {
		/*$array_producto = array("11620301" => "COMB-84", "11620302" => "COMB-90", "11620303" => "COMB-97", "11620304" => "D2 PET", "11620305" => "COMB-95", "11620306" => "D2 S50", "11620307" => "GLP"); ?>*/
		$array_producto = array("11620301" => "COMB-84", "11620302" => "COMB-90", "11620303" => "COMB-97", "11620304" => "DIESEL", "11620305" => "COMB-95", "11620306" => "KEROSENE", "11620307" => "GLP"); ?>
		<div style="width: auto;border: 1px;">
			<table cellspacing="0" cellpadding="0" border="0">
				<thead>
					<th class="th_cabe">Lados</th>
					<th class="th_cabe">Productos</th>
					<th class="th_cabe">Trabajador</th>
				</thead>
				<tbody>
					<?php
					$areglo_producto_cmb = array();

					foreach ($lados as $value) {
						$areglo_producto_cmb[$value['name']].=$array_producto[$value['producto']] . ",";
					}

					$i = 0;

					foreach ($areglo_producto_cmb as $value => $valor_cadena) {
						$estila = "fila_registro_imppar";
						if ($i % 2 == 0) {
							$estila = "fila_registro_par";
						}

						echo "<tr class='$estila'>";
						echo "<td> <input type='hidden' class='lado_cmb' value='" . $value . "'>" . $value . "</td>";
						echo "<td>" . substr($valor_cadena, 0, -1) . "</td>";
						echo "<td><select id='cmb_lad" . $value . "'>";
						echo "<option   value='-1' >SELECCIONAR TRABAJADOR...</option>";

						foreach ($trabajores as $fila) {
							$estado		= FALSE;
							$lado		= trim($value);
							$cod_trab	= trim($fila['id_traba']);

							foreach ($selecionado as $sel) {
								if ($sel['ch_tipo'] == "C" && $lado == $sel['ch_lado'] && $cod_trab == $sel['ch_codigo_trabajador']) {
									$estado = TRUE;
								}
							}

							if ($estado == FALSE) {
								echo "<option   value='" . $fila['id_traba'] . "'>" . $fila['id_traba'] . "-" . $fila['nombre'] . "</option>";
							} else {
								echo "<option   value='" . $fila['id_traba'] . "' selected>" . $fila['id_traba'] . "-" . $fila['nombre'] . "</option>";
							}
						}

						echo "</select></td></tr>";
						$i++;
					}
?>
	
	</tbody>
	<tfoot>
		<tr><td colspan="3">&nbsp;</td></tr>
	</tfoot>
	</table>
</div>
<div style="width: auto;border: 1px;">
	<table cellspacing="0" cellpadding="0" border="0">	
		<thead>
			<th class="th_cabe" style="background-color: white !important;"></th>
			<th class="th_cabe">Tienda</th>
			<th class="th_cabe">Trabajador</th>
		</thead>
		<tbody>
		<?php
			$i = 0;

			foreach ($punto_vt_market as $value) {
				$estila = "fila_registro_imppar";
				if ($i % 2 == 0) {
					$estila = "fila_registro_par";
				}

				echo "<tr class='$estila'>";
				echo "<td style='background-color: white !important;'></td>";
				echo "<td> <input type='hidden' class='pv_market' value='" . $value['name'] . "'> PV " . $value['name'] . " - " . $value['printerserial'] . "</td>";
				echo "<td><select id='pv_" . $value['name'] . "'>";
				echo "<option   value='-1'>SELECCIONAR TRABAJADOR...</option>";

				foreach ($trabajores as $fila) {
					$estado = FALSE;
					$lado = trim($value['name']);
					$cod_trab = trim($fila['id_traba']);

					foreach ($selecionado as $sel) {
						if($sel['ch_tipo'] == "M" && $lado == $sel['ch_lado'] && $cod_trab == $sel['ch_codigo_trabajador']) {
							$estado = TRUE;
						}
					}

					if ($estado == FALSE) {
						echo "<option   value='" . $fila['id_traba'] . "'>" . $fila['id_traba'] . "-" . $fila['nombre'] . "</option>";
					} else {
						echo "<option   value='" . $fila['id_traba'] . "' selected>" . $fila['id_traba'] . "-" . $fila['nombre'] . "</option>";
					}
				}

				echo "</select></td></tr>";
				$i++;
			}
		?>

		</tbody>
		<tfoot>
			<tr><td>&nbsp;</td></tr>
			<tr><td colspan="3">
				<button  id='btnGuardar3' onclick="llamar_guardar()"><img align="right" src="/sistemaweb/images/search.png">ASIGNAR TRABAJADORES</button>
			</td></tr>
		</tfoot>
	</table>
</div>

<?php
	}

	function CrearTablaMatricula_PV($lados, $trabajores, $punto_vt_market, $selecionado, $lados_relacionado) {
		$array_producto = array("11620301" => "84", "11620302" => "90", "11620303" => "97", "11620304" => "D2",
			"11620305" => "95", "11620306" => "D1", "11620307" => "GLP");
			//$productos = matricula_personal_Model::Productos();
			//var_dump($productos);
?>

<div style="width: auto;border: 1px;">
	<table cellspacing="0" cellpadding="0" border="0">	
		<thead>
			<th class="th_cabe" style="background-color: white !important;"></th>
			<th class="th_cabe">Tienda</th>
			<th class="th_cabe">Trabajadores</th>
			<th class="th_cabe">Lados</th>
		</thead>
		<tbody>

		<?php
			$i = 0;
			foreach ($punto_vt_market as $value) {
				$estila = "fila_registro_imppar";
				if ($i % 2 == 0) {
					$estila = "fila_registro_par";
				}

				echo "<tr class='$estila'>";
				echo "<td style='background-color: white !important;'></td>";
				echo "<td> <input type='hidden' class='pv_market' value='" . $value['name'] . "'> <strong style='font-size:1.1em;'>POS: </strong>" . $value['name'] . " - " . $value['printerserial'] . "</td>";
				echo "<td><select id='pv_" . $value['name'] . "'>";
				echo "<option   value='-1'>..SELECCIONE TRABAJADOR..</option>";

				foreach ($trabajores as $fila) {
					$estado = FALSE;
					$lado = trim($value['name']);
					$cod_trab = trim($fila['id_traba']);
					foreach ($selecionado as $sel) {
						if ($sel['ch_tipo'] == "M" && $lado == $sel['ch_lado'] && $cod_trab == $sel['ch_codigo_trabajador']) {
							$estado = TRUE;
						}
					}

					if ($estado == FALSE) {
						echo "<option   value='" . $fila['id_traba'] . "'>" . $fila['id_traba'] . "-" . $fila['nombre'] . "</option>";
					} else {
						echo "<option   value='" . $fila['id_traba'] . "' selected>" . $fila['id_traba'] . "-" . $fila['nombre'] . "</option>";
					}
				}

				echo "</select></td>";
				echo "<td>";

				$lado_actual = $lados_relacionado[0]['lado'];
				$cadena_ini = "";
				$i = 0;
				$estado = TRUE;

				echo "<strong style='font-size:1.1em;'>LADOS: </strong>";

				foreach ($lados_relacionado as $valor_lados) {
					if ($valor_lados['s_pos_id'] == $value['s_pos_id']) {
						if ($estado === TRUE) {
							echo "&nbsp;&nbsp;&nbsp;";
							echo "<strong style='font-size:1.1em;'>" . $valor_lados['lado'] . ") </strong>";
							echo "<input class='lado_cmb' type='hidden' value=" . $valor_lados['lado'] . " pos=" . $value['name'] . ">";
						}

						if ($lado_actual == $valor_lados['lado']) {
							//echo "<strong>" . $array_producto[$valor_lados['product']] . "</strong>,";
							echo $array_producto[$valor_lados['product']] . ", ";

							$estado = FALSE;
						}
					}

					$lado_actual = $lados_relacionado[$i + 1]['lado'];
					if ($lado_actual == $valor_lados['lado']) {
						$estado = FALSE;
					} else {
						$estado = TRUE;
						//echo "<br/>";
					}
					$i++;
				}

				echo "</td>";
				echo "</tr>";
				$i++;
			}
		?>
	
		</tbody>
		<tfoot>
			<tr><td>&nbsp;</td></tr>
			<tr><td colspan="4" align="center">
				<button id='btnGuardar3' onclick="llamar_guardar_pv()"><img align="right" src="/sistemaweb/images/search.png">ASIGNAR TRABAJADORES</button>
			</td></tr>
		</tfoot>
	</table>
</div>

<?php
	}

	function CrearTablaMatricula_Actualizar($lados, $trabajores, $punto_vt_market, $selecionado, $fecha_find, $id_turno, $almacen) {
		/*$array_producto = array("11620301" => "COMB-84", "11620302" => "COMB-90", "11620303" => "COMB-97", "11620304" => "D2 PET", "11620305" => "COMB-95", "11620306" => "D2 S50", "11620307" => "GLP"); ?>*/
		$array_producto = array("11620301" => "COMB-84", "11620302" => "COMB-90", "11620303" => "COMB-97", "11620304" => "DIESEL", "11620305" => "COMB-95", "11620306" => "KEROSENE", "11620307" => "GLP"); ?>
		

		<div class='contenedorprincipal' style="width: 350px!important;border-color: red;">
			<div><h3 style="color: #336699; text-align: center;">ASIGNACION DE TRABAJADORES</h3></div>
			<div class="separacion">
				<div class='fila'>
					<div class='etiquetavales' style="float: left;">Sucursal: </div>
					<div style="float: left;"><input type='text' id='id_scucursal' class='fecha_formato' value="<?php echo $almacen; ?>" disabled=""/></div>
				</div>
				<div class='fila'>
					<div class='etiquetavales' style="float: left;">Fecha: </div>
					<div style="float: left;"><input type='text' id='fecha_inicio_actu' class='fecha_formato' disabled="" value="<?php echo $fecha_find; ?>"/></div>
				</div>
				<div class='fila'>
					<div class='etiquetavales' style="float: left;">Turno: </div>
					<div style="float: left;"><input id="id_turno"  disabled="" value="<?php echo $id_turno; ?>"/></div>
				</div>
			</div>
			<div class="separacion">
				<div class='etiquetavales' style="float: left;">&nbsp;</div>
				<div class='etiquetavales' style="float: left;margin-bottom: 2px;margin-right: 2px;text-align: center;width: auto;"><button id="id_regresar"><img align="right" src="/sistemaweb/icons/atra.gif">Regresar </button></div>
			</div>
		</div>
		<div class='contenedorprincipaltabla' id="contenidoTablaSelecionar">
			<div style="float: left;width: auto;border: 1px;">
				<table cellspacing="0" cellpadding="0" border="0">	
					<thead>
						<th class="th_cabe">Lados</th>
						<th class="th_cabe">Producto</th>
						<th class="th_cabe">Trabajor </th>
					</thead>
					<tbody>
					<?php
					$areglo_producto_cmb = array();
					foreach ($lados as $value) {
						$areglo_producto_cmb[$value['name']].=$array_producto[$value['producto']] . ",";
					}

					$i = 0;
					foreach ($areglo_producto_cmb as $value => $valor_cadena) {
						$estila = "fila_registro_imppar";
						if ($i % 2 == 0) {
							$estila = "fila_registro_par";
						}

						echo "<tr class='$estila'>";
						echo "<td> <input type='hidden' class='lado_cmb' value='" . $value . "'>" . $value . "</td>";
						echo "<td>" . substr($valor_cadena, 0, -1) . "</td>";
						echo "<td><select id='cmb_lad" . $value . "'>";
						echo "<option   value='-1' >..SELECCIONE TRABAJADOR..</option>";

						foreach ($trabajores as $fila) {
							$estado = FALSE;
							$lado = trim($value);
							$cod_trab = trim($fila['id_traba']);
							foreach ($selecionado as $sel) {
								if ($sel['ch_tipo'] == "C" && $lado == $sel['ch_lado'] && $cod_trab == $sel['ch_codigo_trabajador']) {
									$estado = TRUE;
								}
							}

							if ($estado == FALSE) {
								echo "<option   value='" . $fila['id_traba'] . "'>" . $fila['id_traba'] . "-" . $fila['nombre'] . "</option>";
							} else {
								echo "<option   value='" . $fila['id_traba'] . "' selected>" . $fila['id_traba'] . "-" . $fila['nombre'] . "</option>";
							}
						}

						echo "</select></td></tr>";
						$i++;
					}
					?>

					</tbody>
					<tfoot>
						<tr><td colspan="3">&nbsp;</td></tr>
					</tfoot>
				</table>
			</div>
			<div style="float: left;width: auto;border: 1px;">
				<table cellspacing="0" cellpadding="0" border="0">	
					<thead>
						<th class="th_cabe" style="background-color: white !important;"></th>
						<th class="th_cabe">Tienda</th>
						<th class="th_cabe">Trabajor </th>
					</thead>
					<tbody>
					<?php
					$i = 0;
					foreach ($punto_vt_market as $value) {
						$estila = "fila_registro_imppar";
						if ($i % 2 == 0) {
							$estila = "fila_registro_par";
						}
						echo "<tr class='$estila'>";
						echo "<td style='background-color: white !important;'></td>";
						echo "<td> <input type='hidden' class='pv_market' value='" . $value['name'] . "'> PV " . $value['name'] . " - " . $value['printerserial'] . "</td>";
						echo "<td><select id='pv_" . $value['name'] . "'>";
						echo "<option   value='-1'>..SELECCIONE TRABAJADOR..</option>";
						foreach ($trabajores as $fila) {
							$estado = FALSE;
							$lado = trim($value['name']);
							$cod_trab = trim($fila['id_traba']);
							foreach ($selecionado as $sel) {
								if ($sel['ch_tipo'] == "M" && $lado == $sel['ch_lado'] && $cod_trab == $sel['ch_codigo_trabajador']) {
									$estado = TRUE;
								}
							}

							if($estado == FALSE) {
								echo "<option   value='" . $fila['id_traba'] . "'>" . $fila['id_traba'] . "-" . $fila['nombre'] . "</option>";
							} else {
								echo "<option   value='" . $fila['id_traba'] . "' selected>" . $fila['id_traba'] . "-" . $fila['nombre'] . "</option>";
							}
						}

						echo "</select></td>";
						echo "</tr>";
						$i++;
					}
					?>
						
					</tbody>
					<tfoot>
						<tr><td>&nbsp;</td></tr>
						<tr><td colspan="3" >
							<button id='btnActualizar2' onclick="llamar_actualizar()"><img align="right" src="/sistemaweb/images/search.png">ASIGNAR TRABAJADORES</button>
						</td></tr>
					</tfoot>
				</table>
			</div>
			</div>

<?php
	}

	function CrearTablaReporte($data, $fecha_ini, $fecha_final, $cod_trabajor, $sucursal) {
		echo "<script>console.log('" . json_encode($data) . "')</script>";

?>

	<!--<div style="width: auto;border: 1px;">-->
	<!-- Requerimiento 2020-02-10 -->
	<table cellspacing="0" cellpadding="0" border="0">
		<thead>
			<th class="th_cabe">SUCURSALES</th>
			<th class="th_cabe">FECHA y HORA</th>
			<th class="th_cabe">TURNO </th>
			<th class="th_cabe">DETALLES </th>
			<th class="th_cabe">FECHA CREACIÓN</th>
			<th colspan="2" class="th_cabe">...</th>
		</thead>
		<tbody>
		<?php
		$i = 0;
		foreach ($data as $valor_cadena) {
			$estila = "fila_registro_imppar";
			if ($i % 2 == 0) {
				$estila = "fila_registro_par";
			}

			echo "<tr class='$estila'>";
			echo "<td>" . $valor_cadena['ch_sucursal'] . "</td>";
			echo "<td>" . $valor_cadena['dt_dia'] . "</td>";
			echo "<td>" . $valor_cadena['ch_posturno'] . "</td>";
			echo "<td><a href=javascript:verdetalle('" . $valor_cadena['dt_dia'] . "','" . $valor_cadena['ch_posturno'] . "','" . $valor_cadena['ch_sucursal'] . "')>" . $valor_cadena['cantidad_matricula'] . " lados Asignados,  " . $valor_cadena['cantidad_pv'] . "  Pto Venta </a></td>";
			echo "<td>" . $valor_cadena['fecha_replicacion'] . "</td>";
			echo "<td><a href=javascript:actualizar_trabajador('" . $valor_cadena['dt_dia'] . "','" . $valor_cadena['ch_posturno'] . "','" . $valor_cadena['ch_sucursal'] . "')><img align='middle' border='0' src='/sistemaweb/icons/anular.gif' title='Editar' alt='Editar'> </a></td>";
			echo "<td><a href=javascript:delete_trabajador('" . $valor_cadena['dt_dia'] . "','" . $valor_cadena['ch_posturno'] . "','" . $valor_cadena['ch_sucursal'] . "','".$fecha_ini."','".$fecha_final."','".$cod_trabajador."','".$sucursal."')><img align='middle' border='0' src='/sistemaweb/icons/delete.gif' title='Editar' alt='Editar'> </a></td>";
			echo "</tr>";

			$i++;
		}
		?>
		</tbody>
	</table>
</div>

<?php	
	}

	function CrearTablaReporteDetalle($data) {
?>  

<div style="float: left;width: auto;border: 1px;">
	<h2 align="center">Personal por Isla / Punto</h2>
		<table cellspacing="0" cellpadding="0" border="0">	
			<thead>
				<th class="th_cabe">CODIGO SUCURSAL</th>
				<th class="th_cabe">FECHA</th>
				<th class="th_cabe">TURNO </th>
				<th class="th_cabe">LADO</th>
				<th class="th_cabe">CODIGO TRABAJADOR </th>
				<th class="th_cabe">NOMBRE TRABAJADOR </th>
				<th class="th_cabe">TIPO</th>
			</thead>
			<tbody>
			<?php
			$i = 0;
			foreach ($data as $valor_cadena) {
				$estila = "fila_registro_imppar";
				if ($i % 2 == 0) {
					$estila = "fila_registro_par";
				}
				echo "<tr class='$estila'>";
				echo "<td>" . $valor_cadena['ch_sucursal'] . "</td>";
				echo "<td>" . $valor_cadena['dt_dia'] . "</td>";
				echo "<td>" . $valor_cadena['ch_posturno'] . "</td>";
				echo "<td>" . $valor_cadena['ch_lado'] . "</td>";
				echo "<td>" . $valor_cadena['ch_codigo_trabajador'] . "</td>";
				echo "<td>" . $valor_cadena['nombre'] . "</td>";
				echo "<td>" . $valor_cadena['tipo'] . "</td>";
				echo "</tr>";
				$i++;
			}
			?>
			</tbody>
		</table>
	</div>

<?php
	}

	function reportePDF($res) {
		//include('/sistemaweb/include/fpdf.php');
		//$nomalmacen = VarillasModel::obtenerSucursales($almacen);
	}
}
