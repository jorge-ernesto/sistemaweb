
<?php

// store.php3 - by Florian Dittmer <dittmer@gmx.net>
// Example php script to demonstrate the storing of binary files into
// an sql database. More information can be found at http://www.phpbuilder.com/

require("../clases/funciones.php");
$funcion = new class_funciones;

// crea la clase para controlar errores
$clase_error = new OpensoftError;

// conectar con la base de datos
$conector_id=$funcion->conectar("","","","","");

?>

<html>
<head><title>Store binary data into SQL Database</title></head>
<body>

<?php
// code that will be executed if the form has been submitted:

if ($submit) {

    // connect to the database
    // (you may have to adjust the hostname,username or password)


    $data = addslashes(fread(fopen($form_data, "r"), filesize($form_data)));
    //$data = fread(fopen($form_data, "r"), filesize($form_data));
	echo $data;
	echo "<br>";
	echo strlen($data);
	echo "<br>";
	echo filesize($form_data);
	

    $result=pg_QUERY("INSERT INTO binary_data (id, description,bin_data,filename,filesize,filetype) ".
        "VALUES ( 3,'$form_description','$data','$form_data_name','$form_data_size','$form_data_type')");

    print "<p>This file has the following Database ID: ";


} else {

    // else show the form to submit new data:
?>

    <form method="post" action="<?php echo $PHP_SELF; ?>" enctype="multipart/form-data">
    File Description:<br>
    <input type="text" name="form_description"  size="40">
    <input type="hidden" name="MAX_FILE_SIZE" value="1000000">
    <br>File to upload/store in database:<br>
    <input type="file" name="form_data"  size="40">
    <p><input type="submit" name="submit" value="submit">
    </form>

<?php

}


// comprueba si la conexion existe y la cierra
if ($conector_id) pg_close($conector_id);

// restaura el control de errores original
$clase_error->_error();


?>

</body>
</html>

