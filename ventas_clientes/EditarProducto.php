<?php
include('../Construct_base/cnx.php');
include('../DAO/productoDAO.php');	
?>
<html>
	<form name="frmEditarProducto" method="post" enctype="multipart/form-data">
		<table border="1">
			<tr>
				<td>Buscar por Codigo de Producto:<input type="text" name="txtidproducto" id="txtidproducto" size="8">
				<INPUT TYPE="submit" name="btnbuscar" id="btnbuscar" value="Buscar" onclick='valcampoCodigoCP'><BR></td>
			</tr>
			<tr>
				<td>Nombre:<input type="text" name="txtnombre" id="txtnombre" size="30"><BR></td>
			</tr>				
			<tr>				
				<td>Stock:<input type="text" name="txtstock" id="txtstock" size="6"><BR></td>
			</tr>
			<tr>
				<td>Precio:<input type="text" name="txtprecio" id="txtprecio" size="8"><BR></td>
			</tr>
			<tr>
				<td>Marca:<input type="text" name="txtmarca" id="txtmarca" size="30"><BR></td>
			</tr>
			<tr>
				<td><INPUT TYPE="submit" name="btnModificar" id="btnModificar" value="Modificar" onclick='valcampoCodigoCP'>
				<INPUT TYPE="submit" name="btnEliminar" id="btnEliminar" value="Eliminar" onclick='valcampoCodigoCP'><BR>
				<?php
				      echo '<a href="'.$_SERVER['HTTP_REFERER'].'">
					    <INPUT TYPE="submit" name="btnRegresar" id="btnRegresar" value="Regresar" onclick="valcampoCodigoCP"></a>';
				?>
				</td>
				
			</tr>
			</tr>
		</table>
	</form>
</html>



