<?php
include("../valida_sess.php");
include("config.php");
include("../functions.php");
require("../clases/funciones.php");
if(!empty($_GET['dato']))
{
    $query = "SELECT trim(numtar) FROM pos_fptshe1  WHERE numtar = '".$_GET['dato']."'  ORDER BY numtar";
    $result = pg_query($query);
    $numrows = pg_num_rows($result);
    if($numrows > 0)
    {
       print_r('El N&uacute;mero ya existe, debe ingresar otro.');
    }else{
       print_r('El N&uacute;mero esta Disponible.');
    }
}else{
   print_r('Debe Ingresar el N&uacute;mero de Tarjeta.');
}
