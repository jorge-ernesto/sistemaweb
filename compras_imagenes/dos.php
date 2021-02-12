<html>
<head> <title>Ejemplo 22</title></head>
<body>
 <h1> Ejemplo de Formulario 3 </h1>
<p>

<?php
	$var1=$_FILES['fichero_usuario']['name'];
   #Mostramos información del fichero recibido
   print "El fichero recibido está $var1 <br>\n";
   print "El nombre del fichero recibido es $var1 <br>\n";


   #mostramos el contenido
   print "El contenido del fichero recibido es: <br>\n";

   #Abrimos el fichero remoto
   $archivo = fopen("$fichero_usuario", "r");
   if (!$archivo) {
     echo "<p>No se pudo abrir el archivo remoto.\n";
     exit;
   }

   #Mostramos el fichero línea a línea
   $i=0;
   while (!feof($archivo)) 
   {
     $linea = fgets($archivo, 1024);
     print "LINEA $i: $linea <BR>";
     $i++;
   }

   #Cerramos el fichero 
   fclose($archivo);
?>
</body>
</html>

