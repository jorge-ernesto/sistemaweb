<?php
//include("../valida_sess.php");
//include("inc_top_compras.php");
include "../menu_princ.php";
include("../functions.php");

?>
<html><link rel="stylesheet" href="<?php echo $v_path_url; ?>sistemaweb.css" type="text/css">
<head>
</head>
<body>
<form>
<div align="center">
  <p>CONSULTA COMPRAS POR ALMACEN </p>

<table width="990">
<td width="483" align="left">
       <input name="radiobutton1" type="radio" value="radiobutton" checked>
       Articulo
       <input name="radiobutton1" type="radio" value="radiobutton">
       Orden Compra
       <input name="radiobutton1" type="radio" value="radiobutton">
	 Proveedor

  </td>
  <td width="495" align="right">
       <input name="radiobutton2" type="radio" value="radiobutton" checked>
       Atendido Parcial
       <input name="radiobutton2" type="radio" value="radiobutton">
       Atendido Completo
       <input name="radiobutton2" type="radio" value="radiobutton">
       Sin Atencion
       <input name="radiobutton2" type="radio" value="radiobutton">
       Todos

</td>  
</table>
      <div align="left">
  <table width="990" border="1">
    <tr>
      <th width="31" scope="col"><div align="center">
        <input name="Input" type="checkbox" value="">
      </div></th>
      <th width="157" scope="col"><div align="left">Codigo Almacen/Articulo</div></th>
      <th width="307" scope="col">Nombre</th>
      <th width="77" scope="col">Requerido</th>
      <th width="71" scope="col">Atendido</th>
      <th width="72" scope="col">Precio</th>
      <th width="72" scope="col">Costos</th>
      <th width="78" scope="col">Stock</th>
      <th width="67" scope="col">Estado</th>
    </tr>
    <tr>
      <td><div align="center">
        <input name="Input2" type="checkbox" value="" checked>
      </div></td>
      <td>002</td>
      <td>ALMACEN CASTILLA </td>
      <td><div align="right">100.00</div></td>
      <td><div align="right">43.00</div></td>
      <td><div align="right">30.00</div></td>
      <td><div align="right">20.50</div></td>
      <td><div align="right">500.60</div></td>
      <td><div align="center">At.Parcial</div></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td> ARTICULO PRUEBA 1      </td>
      <td>&nbsp;</td>
      <td><div align="right">25.00</div></td>
      <td><div align="right">13.00</div></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td><div align="center">At.Parcial</div></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td> ARTICULO PRUEBA 2 </td>
      <td>&nbsp;</td>
      <td><div align="right">75.00</div></td>
      <td><div align="right">30.00</div></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td><div align="center">At.Parcial</div></td>
    </tr>
    <tr>
      <td><div align="center">
        <input name="Input3" type="checkbox" value="">
      </div></td>
      <td>003</td>
      <td>ALMACEN MAGDALENA </td>
      <td><div align="right">100.10</div></td>
      <td><div align="right">200.20</div></td>
      <td><div align="right">30.00</div></td>
      <td><div align="right">20.50</div></td>
      <td><div align="right">500.60</div></td>
      <td><div align="center">50%</div></td>
    </tr>
    <tr>
      <td><div align="center">
        <input name="Input4" type="checkbox" value="">
      </div></td>
      <td>004</td>
      <td>ALMACEN FAUCETT </td>
      <td><div align="right">100.10</div></td>
      <td><div align="right">200.20</div></td>
      <td><div align="right">30.00</div></td>
      <td><div align="right">20.50</div></td>
      <td><div align="right">500.60</div></td>
      <td><div align="center">50%</div></td>
    </tr>
    <tr>
      <td><div align="center">
        <input name="Input5" type="checkbox" value="">
      </div></td>
      <td>005</td>
      <td>ALMACEN BRENA </td>
      <td><div align="right">100.10</div></td>
      <td><div align="right">200.20</div></td>
      <td><div align="right">30.00</div></td>
      <td><div align="right">20.50</div></td>
      <td><div align="right">500.60</div></td>
      <td><div align="center">50%</div></td>
    </tr>
    <tr>
      <td><div align="center">
        <input name="Input6" type="checkbox" value="">
      </div></td>
      <td>006</td>
      <td>ALMACEN LA PERLA</td>
      <td><div align="right">100.10</div></td>
      <td><div align="right">200.20</div></td>
      <td><div align="right">30.00</div></td>
      <td><div align="right">20.50</div></td>
      <td><div align="right">500.60</div></td>
      <td><div align="center">50%</div></td>
    </tr>
    <tr>
      <td><div align="center">
        <input name="Input7" type="checkbox" value="">
      </div></td>
      <td>009</td>
      <td>ALMACEN SUCRE</td>
      <td><div align="right">100.10</div></td>
      <td><div align="right">200.20</div></td>
      <td><div align="right">30.00</div></td>
      <td><div align="right">20.50</div></td>
      <td><div align="right">500.60</div></td>
      <td><div align="center">50%</div></td>
    </tr>
    <tr>
      <td><div align="center">
        <input name="Input8" type="checkbox" value="">
      </div></td>
      <td>010</td>
      <td>ALMACEN RISSO </td>
      <td><div align="right">100.10</div></td>
      <td><div align="right">200.20</div></td>
      <td><div align="right">30.00</div></td>
      <td><div align="right">20.50</div></td>
      <td><div align="right">500.60</div></td>
      <td><div align="center">50%</div></td>
    </tr>
    <tr>
      <td><div align="center">
        <input name="Input9" type="checkbox" value="">
      </div></td>
      <td>012</td>
      <td>ALMACEN SAN LUIS </td>
      <td><div align="right">100.10</div></td>
      <td><div align="right">200.20</div></td>
      <td><div align="right">30.00</div></td>
      <td><div align="right">20.50</div></td>
      <td><div align="right">500.60</div></td>
      <td><div align="center">50%</div></td>
    </tr>
    <tr>
      <td><div align="center">
        <input name="Input10" type="checkbox" value="">
      </div></td>
      <td>013</td>
      <td>ALMACEN LA MARINA </td>
      <td><div align="right">100.10</div></td>
      <td><div align="right">200.20</div></td>
      <td><div align="right">30.00</div></td>
      <td><div align="right">20.50</div></td>
      <td><div align="right">500.60</div></td>
      <td><div align="center">50%</div></td>
    </tr>
    <tr>
      <td><div align="center">
        <input name="Input11" type="checkbox" value="">
      </div></td>
      <td>014</td>
      <td>ALMACEN ORRANTIA </td>
      <td><div align="right">100.10</div></td>
      <td><div align="right">200.20</div></td>
      <td><div align="right">30.00</div></td>
      <td><div align="right">20.50</div></td>
      <td><div align="right">500.60</div></td>
      <td><div align="center">50%</div></td>
    </tr>
    <tr>
      <td><div align="center">
        <input name="Input12" type="checkbox" value="">
      </div></td>
      <td>015</td>
      <td>ALMACEN HIPODROMO </td>
      <td><div align="right">100.10</div></td>
      <td><div align="right">200.20</div></td>
      <td><div align="right">30.00</div></td>
      <td><div align="right">20.50</div></td>
      <td><div align="right">500.60</div></td>
      <td><div align="center">50%</div></td>
    </tr>
    <tr>
      <td><div align="center">
        <input name="Input13" type="checkbox" value="">
      </div></td>
      <td>016</td>
      <td>ALMACEN ALEGRIA </td>
      <td><div align="right">100.10</div></td>
      <td><div align="right">200.20</div></td>
      <td><div align="right">30.00</div></td>
      <td><div align="right">20.50</div></td>
      <td><div align="right">500.60</div></td>
      <td><div align="center">50%</div></td>
    </tr>
    <tr>
      <td><div align="center">
        <input name="Input14" type="checkbox" value="">
      </div></td>
      <td>017</td>
      <td>ALMACEN EJERCITO</td>
      <td><div align="right">100.10</div></td>
      <td><div align="right">200.20</div></td>
      <td><div align="right">30.00</div></td>
      <td><div align="right">20.50</div></td>
      <td><div align="right">500.60</div></td>
      <td><div align="center">50%</div></td>
    </tr>
    <tr>
      <td><div align="center">
        <input name="Input15" type="checkbox" value="">
      </div></td>
      <td>018</td>
      <td>ALMACEN SAN ISIDRO </td>
      <td><div align="right">100.10</div></td>
      <td><div align="right">200.20</div></td>
      <td><div align="right">30.00</div></td>
      <td><div align="right">20.50</div></td>
      <td><div align="right">500.60</div></td>
      <td><div align="center">50%</div></td>
    </tr>
  </table>
  </div>
  <p><BR>
  
  </p>
</div>
</form>
</body>
</html>
