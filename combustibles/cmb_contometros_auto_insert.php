<?php
http_response_code(404);
die("Not Found");
include("../config.php");
extract($_REQUEST);
$fecha = $_REQUEST["fecha"];
switch($accion) {
	case "Importar":
		pg_exec("SELECT COMBEX_FN_CONTOMETROS_AUTO(to_date('" . $fecha . "','dd/mm/yyyy'))");
?>
		<script>			
			window.close();
		</script>
<?php
	break;
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<link href="../sistemaweb.css" rel="stylesheet" type="text/css">
<script language="JavaScript" src="../clases/calendario.js" ></script>
<script language="JavaScript" src="../clases/overlib_mini.js"></script>
<script language="JavaScript" src="../clases/reloj.js"></script>
<script language="JavaScript" src="../compras/validacion.js"></script>
<script language="JavaScript" src="../compras/valfecha.js"></script>
<script>
	function mandarDatos(form){
		if(form.fecha.value==''){
			alert('Debes especificar el dia dd/mm/yyyy');
		}else{
			form.accion.value='Importar';
			form.submit();
		}
	}
</script>
<title>Ingrese el dia a importar</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<DIV id=overDiv 
style="Z-INDEX: 1000; VISIBILITY: hidden; POSITION: absolute"></DIV>

<form method="post" action="" name="form1">
  <table width="545" border="0" cellpadding="0" cellspacing="0">
    <!--DWLayoutTable-->
    <tr> 
      <td width="24" height="13"></td>
      <td width="207"></td>
      <td width="65"></td>
      <td width="91"></td>
      <td width="158"></td>
    </tr>
    <tr> 
      <td height="19"></td>
      <td colspan="2" valign="top"><p>&nbsp;</p>
        <p>&nbsp;</p></td>
      <td>&nbsp;</td>
      <td></td>
    </tr>
    <tr> 
      <td height="23"></td>
      <td valign="top"><p>Ingrese el dia </p>
        <p>
          <input type="text" name="fecha" onKeyUp="javascript:validarFecha(this)">
          <a href="javascript:show_calendar('form1.fecha');"onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;" ><img src="../images/show-calendar.gif" width=24 height=22 border=0></a> 
        </p>
        <p> 
          <input name="btn_importar" type="button" id="btn_importar" value="Importar" onClick="javascript:mandarDatos(form1);">
          <input name="accion" type="hidden" id="boton2">
        </p></td>
      <td colspan="2" valign="top"><!--DWLayoutEmptyCell-->&nbsp; </td>
      <td></td>
    </tr>
    <tr> 
      <td height="154"></td>
      <td></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td></td>
    </tr>
  </table>
</form>
</body>
</html>
<?php
pg_close();
