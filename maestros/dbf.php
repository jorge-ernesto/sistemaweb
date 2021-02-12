<?php
include("../menu_princ.php");
include("../functions.php");
include("../utils/acceso_sistem.php");
?>
<html>
<head>
<title>sistemaweb</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<h1>CONVERCION DBF a PHP</h1>

<?php
$a = "";
$a .= "ESCRIBIENDO EN PHP";

$def = array(	array("date",     "D"),
  		array("name",     "C",  50),
  		array("age",      "N",   3, 0),
  		array("email",    "C", 128),
  		array("ismember", "L"));

$a .= "SE CREO ARRAY";

if (!dbase_create('/sistemaweb/maestros/MiDBF.dbf', $def)) {
  $a .= "Error, no se puede crear la base de datos\n";
} else {
  $a .= "BD creada\n";
}

$a .= "SE INTENTO CREAR BD";
echo $a."HOLA";
?>

</body>
</html>  
<?php include("../close_connect.php"); ?>
