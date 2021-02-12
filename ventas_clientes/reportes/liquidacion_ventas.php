<?php

include "../../menu_princ.php";

$nuevofecha = split('/',$_REQUEST['dia']);
$dia	    = $nuevofecha[0];
$mes	    = $nuevofecha[1];
$ano	    = $nuevofecha[2];

include ("/sistemaweb/functions.php");
require("/sistemaweb/clases/funciones.php");
include ("functions.php");

$funcion     = new class_funciones;
$conector_id = $funcion->conectar("","","","","");

if (strlen($dia) == 0) {
	rangodefechas();
	$dia = $zdiaa; 
	$mes = $zmesa; 
	$ano = $zanoa;
}
$fecha = $ano."-".$mes."-".$dia;

$sql = "SELECT
		sum(case when ch_fac_tipodocumento!='20' THEN nu_fac_valortotal else nu_fac_valortotal*-1 end) AS total
	FROM
		fac_ta_factura_cabecera
	WHERE
		ch_fac_seriedocumento='$almacen'
		AND dt_fac_fecha='".pg_escape_string($fecha)."'
	GROUP BY
		dt_fac_fecha";

$x_tienda = pg_query($conector_id, $sql);

$sql = "SELECT
		SUM(CASE WHEN ch_codigocombustible='11620307' THEN (CASE WHEN nu_ventagalon!=0 THEN (nu_ventavalor-((nu_ventavalor/nu_ventagalon)*nu_afericionveces_x_5*5)) ELSE 0 END) ELSE 0 END) as glp,
		SUM(CASE WHEN ch_codigocombustible!='11620307' THEN (CASE WHEN nu_ventagalon!=0 THEN  (nu_ventavalor-((nu_ventavalor/nu_ventagalon)*nu_afericionveces_x_5*5)) ELSE 0 END) ELSE 0 END) as otros
	FROM
		COMB_TA_CONTOMETROS
	WHERE
		ch_sucursal='".pg_escape_string($almacen)."'
		AND dt_fechaparte='".pg_escape_string($fecha)."'
	GROUP BY 
		dt_fechaparte ;";

$x_combustible = pg_query($conector_id, $sql);

$sql = "SELECT
		sum(CASE WHEN ch_cliente='0002' THEN nu_importe ELSE 0 END) AS llamagas,
		sum(CASE WHEN ch_cliente!='0002' THEN nu_importe ELSE 0 END) AS otros
	FROM
		val_ta_cabecera
	WHERE
		ch_sucursal='".pg_escape_string($almacen)."'
		AND dt_fecha='".pg_escape_string($fecha)."'
	GROUP BY
		dt_fecha ;";

$x_credito = pg_query($conector_id, $sql);

$sql = "SELECT
		sum(case when ruc='20100366747' THEN (case when importe<0 then importe else 0 end) else 0 end) as llamagas,
		sum(case when ruc!='20100366747' THEN (case when importe<0 then importe else 0 end) else 0 end) as otros
	FROM
		pos_trans" . $ano . $mes . "
	WHERE
		dia='" . $ano . "-" . $mes . "-" . $dia ."'
		AND tipo='C' ;";

$x_descuentos = pg_query($conector_id, $sql);
    
$sql = "SELECT 
		nu_caj_importe as importe,
		ch_caj_glosa as glosa
	FROM
		caj_ta_movimiento_detalle
	WHERE
		dt_fecha_parte='" . $ano . "-" . $mes . "-" . $dia . "' ;";

$x_depositos = pg_query($conector_id, $sql);

?>
<link href="styles/tabla.css" rel="stylesheet" type="text/css">
<BR>LIQUIDACION DE VENTA
<script language="JavaScript" src="/sistemaweb/js/calendario.js"></script>
<script language="JavaScript" src="/sistemaweb/js/overlib_mini.js"></script>
<hr noshade>
<form action="" method="post" name = "frm">
	<table border="1">
		<tr>
			<th colspan="5">CONSULTAR POR FECHA</th>
		</tr>
		<tr>
			<th><input type="text" name="dia" size="10" value="<?php echo $dia.'/'.$mes.'/'.$ano ?>" readonly="true"/>
			&nbsp;<a href="javascript:show_calendar('frm.dia');"><img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a><div id="overDiv" style="position:absolute; visibility:hidden; z-index:0;"></th>
			<th><input type="submit" name="boton" value="Consultar"></th>
    		</tr>
		<th colspan="5">ALMACEN :
		<select name="almacen">
		<?php
			$almacen_sql = "select ch_almacen, ch_nombre_almacen from inv_ta_almacenes";
			$x_almacen   = pg_query($conector_id, $almacen_sql);
			$i = 0;

			while($i<pg_num_rows($x_almacen)) {
				$rs 		= pg_fetch_array($x_almacen,$i);
				$codigo 	= $rs[0];
				$desc_almacen 	= $rs[1];
				if ($almacen == trim($rs[0])) {
					echo "<option selected value=".trim($rs[0]).">".$rs[0]." ".$rs[1]."</option>";
				} else {
					echo "<option value=".trim($rs[0]).">".$rs[0]." ".$rs[1]."</option>";
				}
				$i++;
			}
		?>
		</select>
	</table>
<input type="hidden" name="fm" value='<?php echo $fm;?>'><br>
</form>

<?php
$comb 			= pg_fetch_all($x_combustible);
$tienda 		= pg_fetch_all($x_tienda);
$credito 		= pg_fetch_all($x_credito);
$descuentos 		= pg_fetch_all($x_descuentos);
$depositos 		= pg_fetch_all($x_depositos);

$total_glp 		= number_format($comb[0]['glp'], 2);
$total_otros 		= number_format($comb[0]['otros'], 2);
$total_market 		= number_format($tienda[0]['total'], 2);
$total_ventas 		= number_format($comb[0]['glp']+$comb[0]['otros']+$tienda[0]['total'], 2);

$credito_llamagas 	= number_format($credito[0]['llamagas'], 2);
$credito_otros 		= number_format($credito[0]['otros'], 2);

$descuentos_llamagas	= number_format(abs($descuentos[0]['llamagas']), 2);
$descuentos_otros 	= number_format(abs($descuentos[0]['otros']), 2);
?>

<table width="800px" cellspacing="0" cellpadding="3" border="1">
	<tr>
		<td width="85%" style="font-size:1.5em">+ Ventas GLP</td>
		<td width="*"><p align="right" style="font-size:1.5em"><?php echo htmlentities($total_glp); ?></p></td>
	</tr>
	<tr>
		<td style="font-size:1.5em">+ Ventas Combustibles</td>
		<td><p align="right" style="font-size:1.5em"><?php echo htmlentities($total_otros); ?></p></td>
	</tr>
	<tr>
		<td style="font-size:1.5em">+ Ventas Market</td>
		<td><p align="right" style="font-size:1.5em"><?php echo htmlentities($total_market); ?></p></td>
	</tr>
	<tr>
		<td style="font-size:1.5em"><b>TOTAL VENTAS</b></td>
		<td><p align="right" style="font-size:1.5em"><b><?php echo htmlentities($total_ventas); ?></b></p></td>
	</tr>
	<tr>
		<td style="font-size:1.5em">- Credito Llama Gas</td>
		<td><p align="right" style="font-size:1.5em"><?php echo htmlentities($credito_llamagas); ?></p></td>
	</tr>
	<tr>
		<td style="font-size:1.5em">- Descuentos Llama Gas</td>
		<td><p align="right" style="font-size:1.5em"><?php echo htmlentities($descuentos_llamagas); ?></p></td>
	</tr>
	<tr>
		<td style="font-size:1.5em">- Credito Clientes varios</td>
		<td><p align="right" style="font-size:1.5em"><?php echo htmlentities($credito_otros); ?></p></td>
	</tr>
	<tr>
		<td style="font-size:1.5em">- Descuentos Clientes varios</td>
		<td><p align="right" style="font-size:1.5em"><?php echo htmlentities($descuentos_otros); ?></p></td>
	</tr>
	<tr>
		<td style="font-size:1.5em">+ Sobrante</td>
		<td style="font-size:1.5em">&nbsp;</td>
	</tr>
	<tr>
		<td style="font-size:1.5em">- Gastos</td>
		<td style="font-size:1.5em">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="2" style="font-size:1.5em">&nbsp;</td>
	</tr>
	<tr>
		<td style="font-size:1.5em">+ Cancelacion Creditos Llama Gas</td>
		<td style="font-size:1.5em">&nbsp;</td>
    	</tr>
    	<tr>
		<td colspan="2" style="font-size:1.5em">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="2" style="font-size:1.5em">&nbsp;</td>
    	</tr>
    	<tr>
		<td colspan="2" style="font-size:1.5em">&nbsp;</td>
    	</tr>
    	<tr>
		<td colspan="2" style="font-size:1.5em">&nbsp;</td>
    	</tr>
    	<tr>
		<td colspan="2" style="font-size:1.5em">&nbsp;</td>
    	</tr>
	<tr>
		<td colspan="2" style="font-size:1.5em">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="2" style="font-size:1.5em">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="2" style="font-size:1.5em">&nbsp;</td>
	</tr>
	<tr>
		<td style="font-size:1.5em"><b>SALDO DEL DIA</td>
		<td style="font-size:1.5em">&nbsp;</td>
	</tr>
</table>

<table border="1" width="800px">
	<tr>
		<td colspan="2"><p align="center" style="font-size:1.5em">Depositos</p></td>
	</tr>
	<tr>
		<td style="font-size:1.5em" width="85%">Descripcion</td>
		<td style="font-size:1.5em" width="*">Importe</td>
	</tr>
	<?php
	$total = 0;

	foreach ($depositos as $fila) {
		$glosa = $fila['glosa'];
		$total += $fila['importe'];
		$importe = number_format($fila['importe'], 2);
	?>
	<tr>
		<td style="font-size:1.5em"><?php echo htmlentities($glosa); ?> </td>
		<td><p align="right" style="font-size:1.5em"><?php echo htmlentities($importe); ?></p></td>
	</tr>
	<?php
	}
	?>
	<tr>
		<td style="font-size:1.5em"><b>TOTAL</b></td>
		<td><p align="right" style="font-size:1.5em"><?php echo number_format($total, 2); ?></p></td>
	</tr>
	<tr>
		<td colspan="2" style="font-size:1.5em">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="2" style="font-size:1.5em">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="2" style="font-size:1.5em">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="2" style="font-size:1.5em">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="2" style="font-size:1.5em">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="2" style="font-size:1.5em">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="2" style="font-size:1.5em">&nbsp;</td>
	</tr>
</table><br>
</body>
</html>
