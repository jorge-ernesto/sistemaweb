<?php
include('../Construct_base/cnx.php');
include('../Intranet/Sesiones/session_producto.php');	
?>
<HTML>
	<form name="frmEditarProducto" method="post" enctype="multipart/form-data">
		<table>
			<tr>
				<td>Buscar por Codigo de Producto:<input type="text" name="txtidproducto" id="txtidproducto" size="8"><BR></td>
				<td><INPUT TYPE="submit" name="btnbuscar" id="btnbuscar" value="Buscar" onclick='valcampoCodigoCP'><BR></td>
		</table>
	</FORM>

</HTML>


