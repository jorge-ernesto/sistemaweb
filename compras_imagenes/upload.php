<html>
<head>
<title>Ver Archivo</title>
</head>
<body>
<center>
<p><h3>Archivo UPLOAD</h3></p>
<font size=5>
<?php

require("../clases/funciones.php");
$funcion = new class_funciones;

// crea la clase para controlar errores
$clase_error = new OpensoftError;

// conectar con la base de datos
$conector_id=$funcion->conectar("","","","","");
	
    $sql = "SELECT id, name, picoid, adddate FROM pic";
    $hasil = pg_exec($conector_id, $sql);
    $row = pg_numrows($hasil);
    for  ($i=0; $i<$row; $i++)
    {
    $data = pg_fetch_row($hasil);
    echo $data[0].'   -   <a href="detail.php?id='.$data[0].'" target="_blank"'.">  $data[1] </a><br>";
    }
 ?>

<br><hr>
<h3>Upload File</h3>
<form id="data" method="post" action="input_file.php" enctype="multipart/form-data">
<p><font size=3><b>Archivo a Upload</b></font></p>
<input name="v_id" type="text" size="5" maxlength="1"><br><br>
<input name="testfile" type="file" size="50" maxlength="100000"><br><br>
<input name="submit" type="submit" value="enviar">
</form> </font> </center> 

<?php

// comprueba si la conexion existe y la cierra
if ($conector_id) pg_close($conector_id);

// restaura el control de errores original
$clase_error->_error();

?>

</body> </html>

