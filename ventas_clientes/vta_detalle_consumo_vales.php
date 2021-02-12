<?php
/*
 * Modificado por Néstor Hernández Loli el 24/02/2012
 * para cumplir requerimientos de Servigrifos
 */
include "../menu_princ.php";
include 'excel_detalle_consumo_vales.php';
$hoy = date("d/m/Y");
if ($c_fec_desde == "") {
    $c_fec_desde = $hoy;
}
if ($c_fec_hasta == "") {
    $c_fec_hasta = $hoy;
}

$col = 10;
$lin = str_repeat("=", 240);
$salto = chr(12);

switch ($accion) {

    case "Reporte":
        if ($c_est == "") {
            $c_est = "TODAS";
        }
        if ($c_cliente == "") {
            $c_cliente = "TODOS";
        }
        if ($c_num_liqui == "") {
            $c_num_liqui = "TODOS";
        }
        /*VENTAS_FN_REPORTE_DETALLE_CONSUMO_VALES*/
        $rs = REPORTE_DETALLE_CONSUMO_VALES($c_est, $c_fec_desde, $c_fec_hasta, $c_cliente, $c_num_liqui);
        $ruta = "LIBRO.XLS";
        crearExcelDetalleConsumo($rs, $ruta);
        break;

    case "Imprimir":
        $rs = pg_exec("	select par_valor as print_server from int_parametros where par_nombre ='print_server' 
				UNION
				select par_valor as print_netbios from int_parametros where par_nombre ='print_netbios' 
				UNION 
				select par_valor as print_name from int_parametros where par_nombre ='print_name' ");

        $print_server = pg_result($rs, 0, "print_server");
        $print_netbios = pg_result($rs, 1, "print_netbios");
        $print_name = pg_result($rs, 2, "print_name");

        $txt = "/tmp/reporte_ventas_diarias.txt";

        exec("smbclient //" . $print_netbios . "/" . $print_name . " -c 'print /tmp/" . $txt . "' -P -N -I " . $print_server . " ");
        pg_close();
        break;
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <title>Reporte de Ventas Diarias</title>
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
        <link href="reportes.css" rel="stylesheet" type="text/css">
        <script language="JavaScript">
            function mandarDatos(form,opt){
                form.accion.value = opt;
                form.submit();
            }
        </script>
        <script language="JavaScript" src="/sistemaweb/js/calendario.js"></script>
        <script language="JavaScript" src="/sistemaweb/js/overlib_mini.js"></script>
    </head>

    <body>
        <div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
        <form name="form1" action="" method="post">
            <table width="774" border="0" cellpadding="1" cellspacing="1">
                <tr> 
                    <td height="24" colspan="2">&nbsp;</td>
                    <td colspan="3"><div align="center">DETALLE DE CONSUMO DE VALES</div></td>
                    <td width="17%"> 
                        <!--<a href="#" onClick="javascript:accion.value='Imprimir',form1.submit();">IMPRIMIR</a>-->
                        <a href="#" onClick="javascript:window.open('generar_reporte_detalle_consumo_vales.php?c_fec_desde=<?php echo $c_fec_desde; ?>&c_fec_hasta=<?php echo $c_fec_hasta; ?>&accion=<?php echo $accion; ?>&c_opt_reporte=<?php echo $c_opt_reporte; ?>&c_est=<?php echo $c_est; ?>&c_cliente=<?php echo $c_cliente; ?>&c_num_liqui=<?php echo $c_num_liqui; ?>','winrep1','width=800,height=450,scrollbars=yes,menubar=yes,left=0,top=0');">IMPRESION</a> 
                    </td>
                </tr>
                <tr> 
                    <td width="15%"><div align="right">Estacion : </div></td>
                    <td width="7%"><input type="text" name="c_est" size="11" value="<?php echo $c_est; ?>"></td>
                    <td width="20%" rowspan="2">Desde: 
                        <input type="text" name="c_fec_desde" size="11" value="<?php echo $c_fec_desde; ?>"> <a href="javascript:show_calendar('form1.c_fec_desde');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;" ><img src="/sistemaweb/images/showcalendar.gif"  border=0></a></td>
                    <td width="20%" rowspan="2">Hasta: 
                        <input type="text" name="c_fec_hasta" size="11" value="<?php echo $c_fec_hasta; ?>"> <a href="javascript:show_calendar('form1.c_fec_hasta');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;" ><img src="/sistemaweb/images/showcalendar.gif"  border=0></a></td>
                    <td width="29%" rowspan="2"> <input type="radio" name="c_opt_reporte" value="RESUMIDO" <?php
if ($c_opt_reporte == "RESUMIDO") {
    echo "checked";
}
?> >
                        Resumido <br> <input type="radio" name="c_opt_reporte" value="DETALLADO" <?php
                                                        if ($c_opt_reporte == "DETALLADO") {
                                                            echo "checked";
                                                        }
?>>
                        Detallado </td>
                    <td rowspan="2">&nbsp;</td>
                </tr>
                <tr> 
                    <td><div align="right">Cliente: </div></td>
                    <td width="7%"><input type="text" name="c_cliente" size="11" value="<?php echo $c_cliente; ?>"></td>
                </tr>
                <tr> 
                    <td height="28" colspan="3"> Por numero de Liquidacion 
                        <input type="text" name="c_num_liqui" size="20" value="<?php echo $c_num_liqui; ?>"></td>
                    <td><input type="submit" name="btn_reporte" value="Reporte" onClick="javascript:mandarDatos(form1,'Reporte');">
                        <button name="btnExcel" onClick="javascript:parent.location.href='<?php echo $ruta; ?>';return false"
                        <?php
                        if (!isset($_REQUEST["btn_reporte"]))
                            echo " disabled = 'disabled' ";
                        ?>><img src = "/sistemaweb/images/excel_icon.png" />Excel</button>
                        <input type="hidden" name="accion"></td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            </table>
        </form>

        <table style="width:100%;" border="1">
            <tr> 
                <th style="width: 9%;">DESCRIPCION</th>
                <th style="width: 9%;"># LIQ</th>
                <th style="width: 9%;"># FACTURA</th>
                <th style="width: 9%;"># DESPACHO</th>
                <th style="width: 8%;">FECHA</th>
                <th style="width: 8%;">NUMERACION DE VALES</th>
                <th style="width: 9%;">PLACA</th>
                <th style="width: 9%;">COMBUSTIBLE</th>
                <th style="width: 7%;">OD&Oacute;METRO</th>
                <th style="width: 12%;">USUARIO</th>
                <th style="width: 6%;">CANTIDAD</th>
                <th style="width: 6%;">PRECIO</th>
                <th style="width: 6%;">IMPORTE</th>
            </tr>

		<?php

		$cli = "";
		$total_cliente = 0;
		$total_general = 0;
		$tickets = 0;

		if($rs != 0){
			for ($i = 0; $i < pg_numrows($rs); $i++) {
                		$A = pg_fetch_array($rs, $i);
				$tickets = pg_numrows($rs);
                ?>
                <?php
	                if ($cli != $A["c_ch_cliente"]) {
				$cli = $A["c_ch_cliente"];
				$total_cliente = 0;
		?>

                    <tr> 
                        <td colspan="13"><b>CLIENTE : <?php echo strtoupper($A["c_ch_cliente"] . " " . $A["c_cli_razsocial"]); ?></b></td>
                    </tr>

                <?php	} if($c_opt_reporte == "DETALLADO"){?>

                <tr> 

                    <td>&nbsp;<?php echo $A["c_ch_sucursal"] . " - " . $A["c_des_sucursal"]; ?></td>
                    <td>&nbsp;<?php echo $A["c_ch_liquidacion"]; ?></td>
                    <td>&nbsp;<?php if($A["c_ch_fac_numerodocumento"] != '')
					echo $A["c_ch_fac_numerodocumento"];
				elseif($A["c_ch_fac_numerodocumento"] == '')
					echo $A["c_ch_fac_numerodocumento2"]; ?>
		    </td>
                    <td>&nbsp;<?php echo $A["c_ch_documento"]; ?></td>
                    <td>&nbsp;<?php echo $A["c_dt_fecha"]; ?></td>
                    <td>&nbsp;<?php echo $A["c_vales"]; ?></td>
                    <td>&nbsp;<?php echo $A["c_ch_placa"]; ?></td>
                    <td>&nbsp;<?php echo $A["c_ch_articulo"]; ?></td>
                    <td>&nbsp;<?php echo $A["c_nu_odometro"]; ?></td>
                    <td>&nbsp;<?php echo  strtoupper($A["c_chofer"]); ?></td>
                    <td>&nbsp;<?php echo number_format($A["c_cantidad"], 3); ?></td>
                    <td align="right">&nbsp;<?php echo number_format($A["c_precio"], 3); ?></td>
                    <td align="right">&nbsp;<?php echo $A["c_nu_importe"]; ?></td>
                </tr>
                <?php } 
			$total_cantidad += $A["c_cantidad"]; 
			$total_cliente += $A["c_nu_importe"]; 
		?>
                <?php if (pg_result($rs, $i + 1, "c_ch_cliente") != $A["c_ch_cliente"]) {
                    ?>
                    <tr> 
                        <td align='right' colspan="12"><b>TOTAL CANTIDAD: </b></td>
			<td align='right'><b>
			<?php
		            echo number_format($total_cantidad, 2, '.', ',');
                    	?>
			</b></td></tr>
                    <tr> 
                        <td align='right' colspan="12"><b>TOTAL IMPORTE: </b></td>
			<td align='right'><b>
			<?php
		            $total_general_cantidad += $total_cantidad;
		            $total_general += $total_cliente;
		            echo number_format($total_cliente, 2, '.', ',');
                    	?>
			</b></td></tr>
                    <tr> 
                        <td align='right' colspan="13"><b>&nbsp;</b></td>
                <?php } ?>
            <?php } } ?>

            <tr>
		<td colspan="13">&nbsp;</td>
	    </tr>
            <tr> 
                <td colspan="13" align="center">&nbsp;<b>TOTAL GENERAL DOCUMENTOS:  <?php echo  $tickets; ?></b></td>
            <tr> 
                <td colspan="13" align="center">&nbsp;<b>TOTAL GENERAL CANTIDAD:  <?php echo  number_format($total_general_cantidad, 2, '.', ','); ?></b></td>
            <tr> 
                <td colspan="13" align="center">&nbsp;<b>TOTAL GENERAL IMPORTE:  <?php echo  number_format($total_general, 2, '.', ','); ?></b></td>
            </tr>

        </table>
    </body>
</html>

<?php pg_close();
