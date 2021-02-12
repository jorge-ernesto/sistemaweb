#!/usr/bin/php
<?php
$dbconn = pg_connect("host=localhost dbname=integrado user=postgres");

if ($dbconn===FALSE)
	die("Error de conexion DB");

$sql = "
SELECT
	c.nu_cuenta_numero,
	c.ch_cuenta_nombres,
	c.ch_cuenta_apellidos,
	c.ch_cuenta_dni,
	c.ch_cuenta_ruc,
	c.ch_cuenta_direccion,
	c.ch_cuenta_telefono1,
	c.ch_cuenta_telefono2,
	c.ch_cuenta_email,
	c.nu_cuenta_puntos,
	c.ch_usuario,
	to_char(c.dt_fecha_actualiza,'YYYY-MM-DD HH24:MI:SS'),
	to_char(c.dt_fecha_creacion,'YYYY-MM-DD'),
	to_char(c.dt_fecha_vencimiento,'YYYY-MM-DD'),
	c.id_tipo_cuenta,
	to_char(c.dt_fecha_nacimiento,'YYYY-MM-DD'),
	c.ch_sucursal,
	c.ch_cuenta_vip,
	c.isactive
FROM
	prom_ta_cuentas c;";
$res = pg_query($dbconn,$sql);
if ($res===FALSE) {
	pg_close($dbconn);
	fclose($fh);
	die("Error al obtener cuentas");
}
