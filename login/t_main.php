<?php

class MainTemplate extends Template {
	function Header() {
		return '    <link rel="stylesheet" href="/sistemaweb/inicio.css" type="text/css"><h2 align="center">SISTEMA OpenSoft</h2><br/><h3 align="center">Administraci&oacute;n de Estaciones de Servicios</h3>';
	}

	function formLogin($bFail,$user,$almacenes){
		$result  = '<div align="center" style="padding-bottom: 0px; width: 100%;">';
		$result .= '<form method="post" target="control" name="login" action="control.php">';
		$result .= '<table class="estilo_tabla" width="100%" height= "180px" border="0">';
		$result .= '	<tr><td width="47%" align="center"><img src="/sistemaweb/images/tux_fin.png" ></td>';
		$result .= '		<td>';
		$result .= '			<table border="0" cellspacing="1" cellpadding="2" align="left">';
		$result .= '				<tr>';
		$result .= '					<th align="left">';
		$result .= '						<font>Usuario</font>';
		$result .= '					</th>';
		$result .= '					<th align="left">';
		$result .= '						<input class="estilo_cajas_texto" type="text" name="user" size="10" value="' . htmlentities($user) . '" />';
		$result .= '					</th>';
		$result .= '				</tr>';
		$result .= '			<tr>';
		$result .= '				<th align="left">';
		$result .= '					<font>Clave</font>';
		$result .= '				</th>';
		$result .= '				<th align="left">';
		$result .= '					<input class="estilo_cajas_texto" type="password" name="password" size="10">';
		$result .= '				</th>';
		$result .= '			</tr>';

		if ($bFail) {
			$result .= '			<tr>';
			$result .= '				<th colspan="2" align="center">';
			$result .= '					<font>Usuario o clave incorrecta</font>';
			$result .= '				</th>';
			$result .= '			</tr>';
		}

		$result .= '			<tr>';
		$result .= '				<th align="left">';
		$result .= '					<font>Sucursal</font>';
		$result .= '				</th>';
		$result .= '				<th align="left">';
		$result .= '					<select name="almacen" class="estilo_cajas_texto">';

		foreach($almacenes as $codigo => $descripcion)
			if ($descripcion!='')
				$result .= '<option value="' . htmlentities($codigo) . '">' . htmlentities($descripcion) . '</option>';

		$result .= '					</select>';
		$result .= '				</th>';
		$result .= '			</tr>';

		$result .= '			<tr>';
		$result .= '				<th colspan="2" align="center">';
		$result .= '					<input type="hidden" name="rqst" value="LOGIN.MAIN">';
		$result .= '					<input type="hidden" name="action" value="check">';
		$result .= '					<input class="estilo_botones" type="submit" name="submit" value="Ingresar">';
		$result .= '				</th>';
		$result .= '			</tr>';

		$result .= '			</table>';
		$result .= '		</td>';
		$result .= '	</tr>';
		$result .= '</table>';
	
		$result .= '</form>';
		$result .= '</div>';

		return $result;
	}
}

