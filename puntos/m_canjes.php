<?php
class CanjesModel
{
    function obtenerDatos($tarjeta)
    {
	global $sqlca;
	
	if (trim($tarjeta)=='') return null;
	
	$sql = "SELECT
		    t.nu_tarjeta_numero,
		    t.ch_tarjeta_descripcion,
		    t.ch_tarjeta_placa,
		    t.ch_tarjeta_titular,
		    c.nu_cuenta_numero,
		    c.ch_cuenta_apellidos||' '||c.ch_cuenta_nombres as ch_cuenta_nombres,
		    c.ch_cuenta_dni,
		    c.ch_cuenta_ruc,
		    c.ch_cuenta_direccion,
		    c.ch_cuenta_telefono1,
		    c.nu_cuenta_puntos
		FROM
		    prom_ta_tarjetas t
		    RIGHT JOIN prom_ta_cuentas c ON (c.id_cuenta=t.id_cuenta)
		WHERE
		    t.nu_tarjeta_numero=to_number(substring('" . pg_escape_string($tarjeta) . "','([0-9]{1,15})'),'99999999999999')";
	$sqlca->query($sql);
	
	if (!$tarjeta=$sqlca->fetchRow())
	    return null;
	
	$array = array();
	$array['numero_tarjeta'] = $tarjeta['nu_tarjeta_numero'];
	$array['nombre_tarjeta'] = $tarjeta['ch_tarjeta_descripcion'];
	$array['placa_tarjeta'] = $tarjeta['ch_tarjeta_placa'];
	$array['es_titular'] = $tarjeta['ch_tarjeta_titular'];
	$array['numero_cuenta'] = $tarjeta['nu_cuenta_numero'];
	$array['nombre_cuenta'] = $tarjeta['ch_cuenta_nombres'];
	$array['dni_cuenta'] = $tarjeta['ch_cuenta_dni'];
	$array['ruc_cuenta'] = $tarjeta['ch_cuenta_ruc'];
	$array['direccion_cuenta'] = $tarjeta['ch_cuenta_direccion'];
	$array['telefono_cuenta'] = $tarjeta['ch_cuenta_telefono1'];
	$array['puntos_cuenta'] = $tarjeta['nu_cuenta_puntos'];
	
	return $array;
	
    }
    
//    function obtenerArticulosCanje($puntajeMaximo)
    function obtenerArticulosCanje($tarjeta)
    {
	global $sqlca;

	settype($tarjeta,"integer");

	$sql =	"	SELECT
				c.id_tipo_cuenta,
				c.nu_cuenta_puntos
			FROM
				prom_ta_tarjetas t
				JOIN prom_ta_cuentas c ON t.id_cuenta = c.id_cuenta
			WHERE
				t.nu_tarjeta_numero = $tarjeta;";
	//var_dump($sql);
	$rs = $sqlca->query($sql);
	if ($sqlca->numrows() < 1)
		return null;
	$row = $sqlca->fetchRow();
	$tipocuenta = $row['id_tipo_cuenta'];
	$puntajeMaximo = $row['nu_cuenta_puntos'];

	$sql = 	"	SELECT
				c.id_campana
			FROM
				prom_ta_campanas_tipocuenta ct
				JOIN prom_ta_campanas c ON ct.id_campana = c.id_campana
			WHERE
				ct.id_tipo_cuenta = $tipocuenta
				AND now() BETWEEN c.dt_campana_fecha_inicio AND c.dt_campana_fecha_fin
			ORDER BY
				dt_campana_fecha_inicio DESC
			LIMIT
				1;";
	$rs = $sqlca->query($sql);
	if ($sqlca->numrows() < 1)
		return null;
	$row = $sqlca->fetchRow();
	$campana = $row['id_campana'];

	$sql = "SELECT
		    id_item,
		    ch_item_descripcion,
		    nu_item_puntos
		FROM
		    prom_ta_items_canje
		WHERE
		    nu_item_puntos<='" . pg_escape_string($puntajeMaximo) . "'
		    AND (dt_item_fecha_vencimiento>=now() OR dt_item_fecha_vencimiento IS NULL)
		    AND id_campana = $campana
		ORDER BY
			ch_item_descripcion;";
	$sqlca->query($sql);

	$i = 0;
	$res = array();
	while ($registro = $sqlca->fetchRow())
	{
	    $res[$i++] = $registro;
	}
	
	return $res;
    }

    function obtenerInformacionCuentaTarjeta($tarjeta)
    {
	global $sqlca;

	$sql = "SELECT
		    t.id_tarjeta,
		    c.id_cuenta,
		    c.nu_cuenta_puntos,
		    t.nu_tarjeta_numero,
		    t.ch_tarjeta_descripcion,
		    c.nu_cuenta_numero,
		    c.ch_cuenta_apellidos||' '||c.ch_cuenta_nombres as ch_cuenta_nombres
		FROM
		    prom_ta_tarjetas t
		    RIGHT JOIN prom_ta_cuentas c ON (c.id_cuenta=t.id_cuenta)
		WHERE
		    t.nu_tarjeta_numero=to_number(substring('" . pg_escape_string($tarjeta) . "','([0-9]{1,15})'),'99999999999999')";
	$sqlca->query($sql);
	if (!$info = $sqlca->fetchRow())
	    return false;

	return $info;
    }
    
    function obtenerInformacionCanje($id)
    {
	global $sqlca;
	
	$sql = "SELECT
		    id_item,
		    nu_item_puntos,
		    art_codigo,
		    ch_item_descripcion
		FROM
		    prom_ta_items_canje
		WHERE
		    id_item='" . pg_escape_string($id) . "';";
	$sqlca->query($sql);
	if (!$item = $sqlca->fetchRow())
	    return false;

	return $item;	
    }
    
    function realizarCanje($tarjeta, $id_item, $observacion, $sucursal, $usuario, $razsocial1, $razsocial2)
    {
	global $sqlca;
	
	if ($observacion=='') $observacion=' ';
	
	$info = CanjesModel::obtenerInformacionCuentaTarjeta($tarjeta);
	$item = CanjesModel::obtenerInformacionCanje($id_item);
	
	if ($info == false || $item == false) return false;
	
	if ($info['nu_cuenta_puntos'] < $item['nu_item_puntos']) return false;
	
	$sql = "INSERT INTO
		    prom_ta_canjes
		    (
			id_tarjeta,
			id_item,
			nu_canje_puntaje_anterior,
			nu_canje_puntaje_canjeado,
			ch_canje_observacion,
			ch_sucursal,
			ch_usuario
		    )
		VALUES
		(
		    " . pg_escape_string($info['id_tarjeta']) . ",
		    " . pg_escape_string($item['id_item']) . ",
		    " . pg_escape_string($info['nu_cuenta_puntos']) . ",
		    " . pg_escape_string($item['nu_item_puntos']) . ",
		    '" . pg_escape_string($observacion) . "',
		    '" . pg_escape_string($sucursal) . "',
		    '" . pg_escape_string($usuario) . "'
		);";
	$sqlca->query($sql);

	$texto = CanjesModel::centrar($razsocial1, 40);
	$texto .= "\n";
	$texto .= CanjesModel::centrar($razsocial2, 40);
	$texto .= "\n\n";
	$texto .= CanjesModel::centrar('Canje por Promocion', 40);
	$texto .= "\n";
	$texto .= CanjesModel::centrar('===================', 40);
	$texto .= "\n\n";
	$texto .= "Fecha y hora: " . date("d/m/Y H:i:s") . "\n";
	$texto .= "Tarjeta: " . $info['nu_tarjeta_numero'] . "\n";
	$texto .= $info['ch_tarjeta_descripcion'] . "\n";
	$texto .= "Cuenta: " . $info['nu_cuenta_numero'] . "\n";
	$texto .= $info['ch_cuenta_nombres'] . "\n";
	$texto .= 'Codigo de Articulo: ' . $item['art_codigo'] . "\n";
	$texto .= $item['ch_item_descripcion'] . "\n";
	$texto .= 'Puntaje anterior: ' . $info['nu_cuenta_puntos'] . "\n";
	$texto .= 'Puntos del canje: ' . $item['nu_item_puntos'] . "\n";
	$texto .= 'Puntaje actual: ' . ($info['nu_cuenta_puntos'] - $item['nu_item_puntos']) . "\n\n\n\n\n\n\n\n\n";
	$texto .= "-------------\n";
	$texto .= "Firma Cliente\n\n\n\n\n\n\n\n\n\n\n\n";

	return $texto;	
    }

    function centrar($str, $len)
    {
	return str_pad($str, $len, " ", STR_PAD_BOTH);
    }
        
}

