<?php
include("../menu_princ.php");
include("../functions.php");
require("../clases/funciones.php");
$funcion = new class_funciones;
// crea la clase para controlar errores
$clase_error = new OpensoftError;
$clase_error->_error();
// conectar con la base de datos
$conector_id=$funcion->conectar("","","","","");

$sql = "SELECT    to_char(fecha, 'DD/MM/YYYY') || ' ' || to_char(fecha, 'HH:MI:SS'),
		  to_char(dia, 'DD/MM/YYYY'),
		  turno, 
		  count(manguera)||' '||' Mangueras' as mangueras,
		  fecha 
	FROM 	  pos_contometros
	GROUP BY  dia,fecha, turno
	ORDER BY  fecha desc";
?>

<?php
	$count = pg_query($conector_id, "select count(distinct fecha) from pos_contometros");
	$cant_reg = pg_result($count,0,0);
	$cant_pag = round(($cant_reg/16),0);

	if($pagina==0) {
		$pagina=1;
		$inicio=0;
		$limite=16;
	}
	else {
		$inicio=($pagina-1)*16;
		$limite=$limite+16;
	}

	$addsql = " LIMIT ".$limite." OFFSET ".$inicio;
?>

&nbsp;REPORTE CONTOMETROS - POR LADO<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php
	$a=0;
	echo "Paginas:";
	while($cant_pag>$a)
	{
		echo "[<a href='".$_SERVER["PHP_SELF"]."?pagina=".($a+1)."'>".($a+1)."</a>]";
		$a++;
	}
?>

<table>
	<tr>
		<td valign="top">
			<table border="0" cellpadding="0" cellspacing="1" bgcolor="#959672">
				<tr>
					<th width="120">FECHA
					<th width="120">FECHA SISTEMA
					<th width="45">TURNO
					<th width="90">DETALLES
				<?php
				$sql = $sql.$addsql;
				$xsql = pg_query($conector_id, $sql);
				$i=0;

				while($i<pg_num_rows($xsql))
				{
					$rs = pg_fetch_array($xsql, $i);
					//$fecha = date("d/m/Y t:m:s",$rs[0]);
					$fecha = $rs[0];

					echo "<tr bgcolor='#FFFFCD'>";
					echo "	<td align='center'>".substr($fecha, 0, 10)."<br/>".substr($fecha, 11, 8)."</td>
						<td align='center'>".substr($rs[1], 0, 10)."</td>
						<td align='center'>$rs[2]
						<td align='center'><a href='reporte_por_manguera.php?id=$rs[4]' target='detalle'>$rs[3]</a>";
					$i++;
				}
				?>
			</table>
		</td>
		<th vAlign="top" align="center" width="440">
			&nbsp;&nbsp;<iframe name="detalle" src="reporte_por_manguera.php?visualizar=no" frameborder="1" width="460" height="426" style="border: 1px solid; border-color: #959672"></IFRAME>
		</th>
	</tr>
</table>
<?php pg_close($conector_id); ?>

