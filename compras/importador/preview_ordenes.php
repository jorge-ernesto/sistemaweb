<?php
include("../../valida_sess.php");
include("../../functions.php");
include("funciones_importador.php");
require("../../clases/funciones.php");

$funcion = new class_funciones;
// crea la clase para controlar errores
$clase_error = new OpensoftError;
$clase_error->_error();
// conectar con la base de datos
$conector_id=$funcion->conectar("","","","","");
echo '<link rel="stylesheet" href="/sistemaweb/sistemaweb.css" type="text/css">';
echo '<body bgcolor="#959672">';

if($visualizar=="no" || $clave=="")
{
	echo "<P>
	<CENTER>SELECCIONE UNA ORDEN DE COMPRA PARA <BR>
			VISUALIZAR LOS ARTICULOS</CENTER>";
	exit;
}


$sql = "SELECT trim(barras), unid, precio, round(unid*precio,2)
		FROM compras_tmp
		WHERE
			trim(ruc)||trim(ser)||trim(factu)='$clave'";

//echo $sql;

?>

<table width="550" cellspacing="1" bgcolor="#FFFFCD">
<!--		<tr>
			<th width="100" bgcolor="#CDCE9C">CODIGO
			<th width="230" bgcolor="#CDCE9C">DESCRIPCION
			<th width="60" bgcolor="#CDCE9C">CANT.
			<th width="60" bgcolor="#CDCE9C">PRECIO
		#FFFFCD
		</tr>  --->
		<?php

			$xsql = pg_query($conector_id, $sql);
			$i=0;
			while($i<pg_num_rows($xsql))
			{

				$rs = pg_fetch_array($xsql, $i);

				$rs[0]=completarCeros(trim($rs[0]),13,'0');

				echo "<tr>";
				echo "
					<td width='95' bgcolor='#CDCE9C'>$rs[0]
					<td width='230' bgcolor='#CDCE9C'>".descripcionArticulo($conector_id, $rs[0])."
					<td width='60' bgcolor='#CDCE9C' align='right'>$rs[1]&nbsp;&nbsp;&nbsp;
					<td width='60' bgcolor='#CDCE9C' align='right'>$rs[2]&nbsp;&nbsp;&nbsp;
					<td width='60' bgcolor='#CDCE9C' align='right'>$rs[3]&nbsp;&nbsp;&nbsp;
					";
				$i++;
				$total_cantidad += $rs[1];
				$total_importe += $rs[3];
			}
			echo "<tr>";
			echo "
				<td width='95' bgcolor='#FFFFCD'>
				<th width='230' bgcolor='#FFFFCD' align='right'>TOTAL :&nbsp;&nbsp;&nbsp;
				<th width='60' bgcolor='#CDCE9C' align='right'>$total_cantidad&nbsp;&nbsp;&nbsp;
				<th width='60' bgcolor='#CDCE9C' align='right'>&nbsp;
				<th width='60' bgcolor='#CDCE9C' align='right'>$total_importe&nbsp;&nbsp;&nbsp;
				";

		?>

</table>
</body>
