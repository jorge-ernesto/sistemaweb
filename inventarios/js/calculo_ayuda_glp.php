<?php
include("config.php");

$sql =	"	SELECT
			kilos,
			ge,
			galones
		FROM
			inv_calculo_glp
		WHERE
			mov_numero = '" . $_REQUEST['mov_numero'] . "'
			AND tran_codigo = '" . $_REQUEST['tran_codigo'] . "'
			AND art_codigo = '" . $_REQUEST['art_codigo'] . "'
			AND mov_fecha = '" . $_REQUEST['mov_fecha'] . "'";

$res = pg_exec($sql);
if ($res===FALSE)
	die("<script>alert('No se pudo preparar el calculo. Intente nuevamente');window.close();</script>");

if (pg_num_rows($res)>0) {
	$r = pg_fetch_row($res);
	$kilos = $r[0];
	$ge = $r[1];
	$gal = $r[2];
} else {
	$kilos = 0;
	$ge = 0;
	$gal = 0;
}

if ($_REQUEST['action']=="Guardar") { 

	if ($kilos>0 && $ge>0) {
		$sql = "UPDATE
				inv_calculo_glp
			SET
				kilos = " . $_REQUEST['calculo_kilos'] . ",
				ge = " . $_REQUEST['calculo_ge'] . ",
				galones = " . $_REQUEST['calculo_galonesv'] . "
			WHERE
				mov_numero = '" . $_REQUEST['mov_numero'] . "'
				AND tran_codigo = '" . $_REQUEST['tran_codigo'] . "'
				AND art_codigo = '" . $_REQUEST['art_codigo'] . "'
				AND mov_fecha = to_date('" . $_REQUEST['mov_fecha'] . "','DD/MM/YYYY');";
	} else {

		$sql = "INSERT INTO
				inv_calculo_glp
			(
				mov_numero,
				tran_codigo,
				art_codigo,
				mov_fecha,
				kilos,
				ge,
				galones
			) VALUES (
				'" . $_REQUEST['mov_numero'] . "',
				'" . $_REQUEST['tran_codigo'] . "',
				'" . $_REQUEST['art_codigo'] . "',
				'" . $_REQUEST['mov_fecha'] . "',
				" . $_REQUEST['calculo_kilos'] . ",
				" . $_REQUEST['calculo_ge'] . ",
				" . $_REQUEST['calculo_galonesv'] . "
			);";

	}

/*echo "<pre>";
print_r($sql);
echo "</pre>";*/

	$res = pg_exec($sql);
	if ($res===FALSE)
		die("<script>alert('No se pudo guardar el calculo. Intente nuevamente');//window.close();</script>");
	die("<script>window.close();</script>");

}

pg_close();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<title>Ayuda </title>
<head>
<script language="JavaScript">
function pasarValorOpener(valor,form,cod){
	eval("opener.document.form1."+cod+".value = '"+valor+"'");
	form.submit();
}

function prevalidate() {

	var kilos = parseFloat(document.getElementById("calculo_kilos").value);
	var ge = parseFloat(document.getElementById("calculo_ge").value);

	if (kilos>0 && ge>0) {

		kilos = Math.round(kilos*100)/100;
		document.getElementById("calculo_kilos").value = kilos;
		ge = Math.round(ge*10000)/10000;
		document.getElementById("calculo_ge").value = ge;

		var litros = kilos/ge;
		var galones = litros/3.785411784;
		litros = Math.round(litros*100)/100;
		galones = Math.round(galones*100)/100;
		document.getElementById("calculo_litros").value = litros;
		document.getElementById("calculo_galonesv").value = galones;
		document.getElementById("savebutton").disabled="";
		return true;
	} else {
		document.getElementById("savebutton").disabled="disabled";
		return false;
	}
}

function prevalidate2() {
	var gal = parseFloat(document.getElementById("calculo_galonesv").value);
	if (gal>0) {
		var lit = Math.round(gal*3.785411784*100)/100;
		document.getElementById("calculo_litros").value = lit;
		//document.getElementById("calculo_kilos").value = 0;
		//document.getElementById("calculo_ge").value = 0;
		document.getElementById("savebutton").disabled="";
		return true;
	} else {
		document.getElementById("savebutton").disabled="disabled";
		return false;
	}
}

function submitvalidate() {
	if (prevalidate()==false && prevalidate2()==false)
		return false;
	document.getElementById("calculo_galones").value = document.getElementById("calculo_galonesv").value;
	eval("opener.document.form1.<?php echo $_REQUEST['txtcantidad']; ?>.value = '"+document.form1.calculo_litros.value+"'");
	return true;
}
</script>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="/sistemaweb/css/sistemaweb.css" type="text/css">
</head>

<body>
<form name="form1" method="post" action="calculo_ayuda_glp.php" onSubmit="return submitvalidate();">
 <input type="hidden" name="mov_numero" value="<?php echo $_REQUEST['mov_numero']; ?>" />
 <input type="hidden" name="tran_codigo" value="<?php echo $_REQUEST['tran_codigo']; ?>" />
 <input type="hidden" name="art_codigo" value="<?php echo $_REQUEST['art_codigo']; ?>" />
 <input type="hidden" name="mov_fecha" value="<?php echo $_REQUEST['mov_fecha']; ?>" />
 <input type="hidden" name="calculo_galones" id="calculo_galones" value="" />

 <table style="width:400px;">
  <tr>
   <td colspan="2" style="text-align:center;font-weight:bold;">Conversi&oacute;n de Unidades - GLP</td>
  </tr>
  <tr>
   <td style="width:50%;text-align:right;">Kilos:</td>
   <td style="width:50%;text-align:left;"><input type="input" name="calculo_kilos" id="calculo_kilos" value="<?php echo $kilos; ?>"  onBlur="prevalidate()"/></td>
  </tr>
  <tr>
  <td style="width:50%;text-align:right;">G.E.:</td>
   <td style="width:50%;text-align:left;"><input type="input" name="calculo_ge" id="calculo_ge" value="<?php echo $ge; ?>"  onBlur="prevalidate()"/></td>
  </tr>
  <tr>
   <td style="width:50%;text-align:right;">Galones:</td>
   <td style="width:50%;text-align:left;"><input type="input" name="calculo_galonesv" id="calculo_galonesv" value="<?php echo $gal; ?>" onBlur="prevalidate2()" /></td>
  </tr>
  <tr>
   <td style="width:50%;text-align:right;">Litros:</td>
   <td style="width:50%;text-align:left;"><input type="input" name="calculo_litros" id="calculo_litros" value="" disabled="disabled" /></td>
  </tr>
  <tr>
   <td colspan="2" style="text-align:center"><input type="submit" name="action" id="savebutton" value="Guardar"></td>
  </tr>
 </table>
 <script>prevalidate();</script>
</form>
</body>
</html>
