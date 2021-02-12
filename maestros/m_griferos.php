<?php
/*include("../config.php");
include("../combustibles/inc_top.php");
include("../functions.php");*/
include("../menu_princ.php");
include("../functions.php");
include("../utils/acceso_sistem.php");
require("../clases/funciones.php");
?>

<html>
</head>
<body>
<h2 style="color:#336699;" align="center">CONFIGURACION DE GRIFEROS</td>
<p>
</p>
<form action='m_surtid_edit.php' method='post' name="form1">

</select>

    <p>
    </p>

<table border="0" cellpadding="0">
        <tr>
            <th class="grid_cabecera" width="20">&nbsp;</th>
            <th class="grid_cabecera" width="100">CODIGO</th>
            <th class="grid_cabecera" width="150">NOMBRE</th>
            <th class="grid_cabecera" width="120">DESC BREVE</th>
            <th class="grid_cabecera" width="80">CATEGORIA</th>
            <!--<th class="grid_cabecera" width="10">&nbsp;</th>-->
        </tr>

<!--<table border="1" cellspacing="0" cellpadding="0">

 <tr>
    <td>&nbsp;</td>
    <td><input name="boton" type="submit"  value="Agregar"></td>
    <td>&nbsp;</td>
    <td><input name="boton" type="submit"  value="Modificar"></td>
    <td>&nbsp;</td>
    <td><input name="boton" type="submit" value="Eliminar"></td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
 </tr>

  <tr>
    <th>&nbsp;</th>
    <th>CODIGO</th>
    <th>NOMBRE</th>
    <th>DESC BREVE</th>
    <th>CATEGORIA</th>
  </tr>-->
</table>
</body>
</html>
<?php include("../close_connect.php"); ?>