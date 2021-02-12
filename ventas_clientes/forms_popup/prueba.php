<?php

include "CNumeroaLetra.php";

$numalet= new CNumeroaletra; 
$numalet->setNumero(161.96); 
//imprime numero con los valore por defecto 
//echo $numalet->letra(); 
?> 
<br> 
<?php 
//cambia a minusculas 
$numalet->setMayusculas(1); 
//cambia a femenino 
$numalet->setGenero(1); 
//cambia moneda 
$numalet->setMoneda("NUEVOS SOLES"); 
//cambia prefijo 
$numalet->setPrefijo(''); 
//cambia sufijo 
$numalet->setSufijo(''); 
//imprime numero con los cambios 
echo $numalet->letra();

