<?php
include("../config.php");
include("inc_top.php");
?>


<form action="" method="post">
<table border="1">
    <tr>
      <td>FORMULARIO</td>
      <td>:</td>
      <td><input type="text" name="textfield"></td>
    </tr>
    <tr>
      <td>N&deg; FORMULARIO</td>
      <td>:</td>
      <td><input type="text" name="textfield2"></td>
    </tr>
    <tr>
      <td>ALMACEN ORIGEN</td>
      <td>:</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>ALMACEN DESTINO</td>
      <td>:</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>N&deg; DOCUMENTO</td>
      <td>:</td>
      <td>&nbsp;</td>
    </tr>
  </table>
</form>
</body>
</html>
<?php pg_close($coneccion); ?>