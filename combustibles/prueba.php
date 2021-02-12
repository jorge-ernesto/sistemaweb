
<?php
function storeProcedure($cursor){
	pg_exec("begin");
	pg_exec("select ref('$cursor')");
	$rs1 = pg_exec("fetch all in $cursor");
	pg_exec("commit");
	
	return $rs1;
}
?>

<?php
include("../config.php");
include("store_procedures.php");
$user = "gude";
$titulo = "mi gudesota";
$cabecera = "cabecera";
$D= "Aaa";

sacarExcelDifTrans($user,$titulo,$almacen,$cabecera,$D);
pg_close();
?>
<link href="miguel.css" rel="stylesheet" type="text/css">

<form name="form1" method="post" action="prueba.php">
  <table border="1" cellspacing="0" cellpadding="0">
    <tr>
      <td width="112" class="tbhead">titulo 1</td>
      <td width="75" class="tbhead">titulo 2</td>
      <td width="118" class="tbhead">titulo 3</td>
      <td width="132" class="tbhead">titulo 4</td>
      <td width="184" class="tbhead">titulo 5</td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
  </table>
  <p> 
    <select name="combo" onChange="javascrip:form1.submit()">
      <option>hola</option>
      <option>hola</option>
    </select>
  </p>
</form>
