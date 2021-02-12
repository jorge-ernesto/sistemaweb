<?php
include("config.php");
$rs = pg_exec("select des_alma,cantidad,importe from tmp_ventas_especiales");
pg_close();
header("Content-Type: application/vnd.ms-excel");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
?>
<table width="512" border="1">
  <tr>
    <td>Carajo</td>
    <td>Carajo</td>
    <td>Carajo</td>
  </tr>
  <?php for($i=0;$i<pg_numrows($rs);$i++){
  $A = pg_fetch_array($rs,$i);
  ?> 
  <tr>
    <td><?php echo $A[0];?></td>
    <td><?php echo $A[1];?></td>
    <td><?php echo $A[2];?></td>
  </tr>
  <?php } ?>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
</table>
