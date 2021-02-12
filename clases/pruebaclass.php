<?php
require("funciones.php");
$miconex = new class_funciones;
if ($miconex->configurar() ) {
	echo ( "se encontro en TAB_LOGUEO");
	}
else
	{
	echo ( "no se encontro en TAB_LOGUEO");
	}
