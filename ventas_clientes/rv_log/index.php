<?php

// Ruta del directorio
$path    = './';
 
// Lee archivos y elimina . y ..
$files = array_diff(scandir($path), array('.', '..'));
 
// Mostramos archivos
echo "<h2>Index</h2>";

foreach($files as $file) {
	$fecha = date ("Y-m-d", filemtime($file));	
    echo "<a href='./$file'> ". $file . " ---> " . $fecha . PHP_EOL . "</a></br>";	
}
