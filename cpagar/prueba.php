<?php
$cadena = "110AS2-Empresa Tal-25/05/2004";
$A = strtok($cadena, "-");
while($A){

echo $A."<br>";
$R[$i] = $A;
$A = strtok("-");
$i++;
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<script language="JavaScript">
var str = "01-abcde";
str = str.substring(0,2);
alert(str);
</script>
<title>Pruebat</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="miguel.css" rel="stylesheet" type="text/css">
</head>

<body>
<form name="form1" method="post" >
<table width="614" border="0">
  <tr>
    <td width="87" class="resumen"><p>Noticia que debe tener otro color y fuentes, bueno en todo 
        caso debera ser distinta de lo normal</p>
      </td>
    <td width="285"><p> 
          <input type="button" name="Submit" value="Submit" onClick="combo.selectedIndex=campo2.value;">
          <br>
          Campo 1 
          <input type="text" name="campo1" value="<?php echo $campo1;?>">
          <font face="Georgia, Times New Roman, Times, serif"><br>
          <a href='#' title='Tooltyip sencillo'>Campo 2</a> 
          <input type="text" name="campo2">
          <select name="combo">
		  <option value="Uno">1</option>
		  <option value="Dos>">2</option>
		  <option value="Tres">3</option>
		  <option value="Cuatro">4</option>
          </select>
          </font> </p>
        </td>
      <td width="228"><font face="Georgia, Times New Roman, Times, serif"> 
        <input id="3" type="text" name="campoA" onKeyUp="javascript:campoB.value=campoA.value">
        <input id="campoB" type="text" name="campoB">
        </font></td>
  </tr>
  <tr>
    <td><font face="Georgia, Times New Roman, Times, serif">ssssssss</font></td>
    <td class="resena">Ricardo Pati&ntilde;o ofrece hoy una velada en la que interpretar&aacute; 
      m&uacute;sica del recuerdo, en el Caf&eacute; del Cerro.<br>
      Lugar: Grada 122 de las escalinatas Diego Noboa del cerro Santa Ana.<br>
      Hora: 21h00.<br>
      Entrada: Gratuita</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td class="nota">Guayaquil</td>
    <td class="campo">Agenda</td>
    <td>&nbsp;</td>
  </tr>
</table>
</form>
</body>
</html>
