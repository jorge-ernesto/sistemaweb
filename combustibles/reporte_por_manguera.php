<?php
include("../valida_sess.php");
include("../functions.php");
require("../clases/funciones.php");

$funcion = new class_funciones;
// crea la clase para controlar errores
$clase_error = new OpensoftError;
$clase_error->_error();
// conectar con la base de datos
$conector_id=$funcion->conectar("","","","","");

$id = $_REQUEST["id"];
?>

<link rel="stylesheet" href="<?php echo $v_path_url; ?>sistemaweb.css" type="text/css">

<?php
if($visualizar=="no") {
	echo "<body>";
	echo "</body>";
	exit;
}

$sql_cabecera = "
SELECT
	to_char(fecha, 'DD/MM/YYYY') || ' ' || to_char(fecha, 'HH:MI:SS AM') AS fecha,
	turno,
	to_char(dia, 'DD/MM/YYYY')
FROM
	pos_contometros
WHERE
	fecha = '" . $id . "'
";

$xsql_cabecera = pg_query($conector_id, $sql_cabecera);

?>

<h3 align="center" style="color:#336699;">DETALLE POR MANGUERAS</h3>
<div style="HEIGHT:600px; WIDTH:500px; OVERFLOW:auto">
	<table>
		<tr>
			<th align="right">FECHA SISTEMA:</th>
			<td><?php echo pg_result($xsql_cabecera,0,2); ?></td>
		<tr>
			<th align="right">FECHA y HORA:</th>
			<td><?php //echo date("d/m/Y t:m:s", pg_result($xsql_cabecera,0,0));
					echo pg_result($xsql_cabecera,0,0); ?></td>
		<tr>
			<th align="right">TURNO:</th>
			<td><?php echo pg_result($xsql_cabecera,0,1); ?></td>
		</tr>
	</table>


	<table border="0" cellpadding="0" cellspacing="1" bgcolor="#959672">
		<tr>
			<th width="40">LADO</th>
			<th width="80">MANGUERA</th>
			<th width="80">CANT. VOL.</th>
			<th width="80">CANT. VAL.</th>
			<th width="40">PRECIO</th>
			<th width="120">PRODUCTO</th>
		</tr>

<?php

	$sql = "
	SELECT
		surt.ch_numerolado,
		cont.manguera,
		ROUND(cont.cnt_vol,2),
		ROUND(cont.cnt_val,2),
		ROUND(comb.nu_preciocombustible,2),
		comb.ch_nombrecombustible
	FROM 
		pos_contometros AS cont
		JOIN comb_ta_surtidores AS surt
			ON (surt.ch_numerolado = cont.num_lado::VARCHAR AND surt.nu_manguera = cont.manguera)
		JOIN comb_ta_combustibles AS comb
			ON (surt.ch_codigocombustible  = comb.ch_codigocombustible)	
	WHERE
		cont.fecha = '" . $id . "';
	";

	$sql = $sql.$addsql;

	$xsql = pg_query($conector_id, $sql);
	$i=0;

	while($i<pg_num_rows($xsql)){
		$rs = pg_fetch_array($xsql, $i);

		echo "<tr bgcolor='#FFFFCD'>";

		echo "<td align='center'>$rs[0]</td>
				<td align='center'>$rs[1]</td>
				<td align='right'>$rs[2]&nbsp;&nbsp;</td>
				<td align='right'>$rs[3]&nbsp;&nbsp;</td>
				<td align='right'>$rs[4]&nbsp;&nbsp;</td>
				<td align='center'>$rs[5]</td>
			";
		$i++;
	}
	?>
	</table>
</div>

<?php pg_close($conector_id); ?>

