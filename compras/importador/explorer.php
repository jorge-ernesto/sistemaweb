<?php
if($boton=="Procesar" && $file!=""){
	header("Location: /sistemaweb/compras/importador/importar_compras.php?file=$file");
}
else if($boton=="Regresar"){
	header("Location: /sistemaweb/compras/cmpr_ordencom.php");
}
else if($boton=="Ver Procesados"){
	header("Location: /sistemaweb/compras/importador/importar_compras.php");
}

/*else if($boton=="Subir"){
	session_start();
	if (is_uploaded_file($_FILES['userfile']['tmp_name'])) {
		copy($_FILES['userfile']['tmp_name'], "/sistemaweb/ordenes_compra/$name");
	} else {
		echo "Possible file upload attack. Filename: ".$_FILES['userfile']['name'];
	}
	move_uploaded_file($_FILES['userfile']['tmp_name'], "/sistemaweb/ordenes_compra/$name");
}
*/

include("../../menu_princ.php");
//include("../../valida_sess.php");
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


$dir = "/sistemaweb/ordenes_compra/";
//$archivos = "*.*";

echo "ARCHIVOS A PROCESAR<p>";
echo "En&nbsp;: &nbsp;&nbsp;".$dir;
?>
<!--
<form name="import" enctype="multipart/form-data" action="" method="post">
	<input type="hidden" name="MAX_FILE_SIZE" value="1000000">
	Subir Archivo : <input name="userfile" type="file">
	<input name="boton" type="submit" value="Subir">
</form>
-->

<form name="formular" action="" method="POST">
<table border="1">
	<tr>
		<td>
		<th>NOMBRE
		<th>TAMAÑO
		<?php

			if(glob($dir."*.TXT"))
			{
				foreach(glob($dir."*.TXT") as $nombre_archivo)
				{
					echo "<tr>";
					echo "<td><input type='radio' name='file' value='".$nombre_archivo."'>";
					echo "<td>$nombre_archivo<td align='right'>".filesize($nombre_archivo)." Bytes";
				}
			}
		
			if(glob($dir."*.txt"))
			{
				foreach(glob($dir."*.txt") as $nombre_archivo)
				{
					echo "<tr>";
					echo "<td><input type='radio' name='file' value='".$nombre_archivo."'>";
					echo "<td>$nombre_archivo
						<td align='right'>".filesize($nombre_archivo)." Bytes" ;
				}
			}
		?>
	<tr>
		<td>
		<td><input type="submit" name="boton" value="Procesar">
		<input type="submit" name="boton" value="Ver Procesados">
		<td><input type="submit" name="boton" value="Regresar">
</table>
</form>
