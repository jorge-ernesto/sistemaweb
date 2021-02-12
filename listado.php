<?php
	$conexion 	= pg_connect("dbname=integrado user=postgres password=postgres");
	$q1			= pg_exec($conexion,"select * from prom_ta_items_canje order by id_item");
	$count		= pg_numrows($q1);

	echo "<table border='1' ><tr><b>Lista de datos de la tabla prom_ta_items_canje</b></tr>";
	echo "<tr>";
	echo "<td>id_item</td>";
	echo "<td>art_codigo</td>";
	echo "<td>ch_item_descripcion</td>";
	echo "<td>dt_item_fecha_creacion</td>";
	echo "<td>dt_item_fecha_vencimiento</td>";
	echo "<td>nu_item_puntos</td>";
	echo "<td>ch_item_observacion</td>";
	echo "<td>ch_usuario</td>";
	echo "<td>dt_fecha_actualiza: </td>";
	echo "<td>ch_sucursal</td>";
	echo "<td>id_campana</td>";
	echo "</tr>";

	for($i=0; $i<=$count-1; $i++)
	{
		echo "<tr>";
		$res=pg_fetch_array($q1, $i);
		echo "<td>$res[0]</td>";
		echo "<td>$res[1]</td>";
		echo "<td>$res[2]</td>";
		echo "<td>$res[3]</td>";
		echo "<td>$res[4]</td>";
		echo "<td>$res[5]</td>";
		echo "<td>$res[6]</td>";
		echo "<td>$res[7]</td>";
		echo "<td>$res[8]</td>";
		echo "<td>$res[9]</td>";
		echo "<td>$res[10]</td>";
		echo "</tr>";
	}
	echo "</table>";
