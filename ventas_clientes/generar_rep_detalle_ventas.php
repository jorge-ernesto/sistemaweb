<?php
extract($_REQUEST);
//include("../valida_sess.php");
include("../menu_princ.php");
include("../functions.php");
include("/sistemaweb/utils/funcion-texto.php");
require("../clases/funciones.php");
$funcion = new class_funciones;

// crea la clase para controlar errores
$clase_error = new OpensoftError;

// conectar con la base de datos
$coneccion=$funcion->conectar("","","","","");

if( is_null($almacen) or trim($almacen)=="") {
	$almacen=$_POST["cod_almacen"];
}

$fecha = getdate();

$dia = $fecha['mday'];
$mes = $fecha['mon'];
$year = $fecha['year'];
$hoy = $dia.'/'.$mes.'/'.$year;

$fechad = "01/".$mes."/".$year;
$fechaa = "31/".$mes."/".$year;
//PARA COLOCAR EL ALMACEN POR DEFAULT O EL ULTIMO QUE SE HA SELECCIONADO

if($cod_almacen=="") {
	$cod_almacen = $almacen;
}

$rsx2 = pg_exec("SELECT ch_almacen ,ch_nombre_almacen FROM inv_ta_almacenes WHERE ch_clase_almacen='1'
	AND  trim(ch_almacen)=trim('$cod_almacen') ORDER BY ch_nombre_almacen");

//$rsx2 = pg_exec("select num_seriedocumento ,num_descdocumento from int_num_documentos where 
//trim(num_seriedocumento)=trim('$cod_almacen') AND num_tipdocumento='10' ORDER BY num_descdocumento");		
		
$C = pg_fetch_array($rsx2,0);
$almacen_dis = $C[1];
//FIN PARA COLOCAR EL ALMACEN POR DEFAULT O EL ULTIMO QUE SE HA SELECCIONADO
$rsx1 = pg_exec("SELECT ch_almacen ,ch_nombre_almacen FROM inv_ta_almacenes WHERE ch_clase_almacen='1'
		ORDER BY ch_nombre_almacen");

/*$rsx1 = pg_exec("select a.num_seriedocumento,a.num_descdocumento from int_num_documentos a, int_tabla_general b 
       where b.tab_tabla='08' and  '0000'||a.num_tipdocumento=b.tab_elemento and tab_car_03 is not null 
       order by num_seriedocumento");		*/
		
if(is_null($v_fecha_desde) or is_null($v_fecha_hasta)) {
	$v_fecha_desde=date("d/m/Y");
	$v_fecha_hasta=date("d/m/Y");
}

$v_ilimit=0;

//if($consultar=="Consultar") {
	//pg_exec("truncate trans_pend ");
	// carga las variables para mandar el reporte a impresion texto
$v_sqlx ="SELECT par_valor FROM int_parametros WHERE trim(par_nombre)='print_netbios' ";
$v_xsqlx=pg_exec( $v_sqlx);
$v_server =pg_result($v_xsqlx,0,0);

$v_sqlx ="SELECT par_valor FROM int_parametros WHERE trim(par_nombre)='print_name' ";
$v_xsqlx=pg_exec($v_sqlx);
$v_printer=pg_result($v_xsqlx,0,0);

$v_sqlx ="SELECT par_valor FROM int_parametros WHERE trim(par_nombre)='print_server' ";
$v_xsqlx=pg_exec($v_sqlx);
$v_ipprint=pg_result($v_xsqlx,0,0);

$v_archivo="/tmp/imprimir/vta_diaria.txt";
$v_archivo_detallado="/tmp/imprimir/vta_detalle.txt";

//}
//else {
/*	$diad = "01";
	$anod = $year;
	$mesd = $mes;
	$diaa = "31";
	$anoa = $year;
	$mesa = $mes;
	$fechad = $diad."/".$mesd."/".$anod;
	$fechaa = $diaa."/".$mesa."/".$anoa;
	*/
//}
//para la tabla de busqueda por fechas

?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<title>SISTEMAWEB</title>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<script language="JavaScript" src="/sistemaweb/clases/calendario.js"></script>
		<script language="JavaScript" src="/sistemaweb/clases/overlib_mini.js"></script>
		<script language="JavaScript" src="miguel-funciones.js"></script>

	    <link rel="stylesheet" href="/sistemaweb/assets/css/jquery-ui.css">
		<script type="text/javascript" src="/sistemaweb/assets/js/jquery/jquery-3.2.0.min.js"></script>
		<script charset="utf-8" type="text/javascript" src="/sistemaweb/assets/js/jquery/jquery-ui.js"></script>
		<script charset="utf-8" type="text/javascript">
			window.onload = function() {
				$(function() {
					$.datepicker.regional['es'] = {
						closeText: 'Cerrar',
						prevText: '<Ant',
						nextText: 'Sig>',
						currentText: 'Hoy',
						monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
						monthNamesShort: ['Ene','Feb','Mar','Abr', 'May','Jun','Jul','Ago','Sep', 'Oct','Nov','Dic'],
						dayNames: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
						dayNamesShort: ['Dom','Lun','Mar','Mié','Juv','Vie','Sab'],
						dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sá'],
						weekHeader: 'Sm',
						dateFormat: 'dd/mm/yy',
						firstDay: 1,
						isRTL: false,
						showMonthAfterYear: false,
						yearSuffix: ''
					};

					$.datepicker.setDefaults($.datepicker.regional['es']);

					$( "#txt-date-ini" ).datepicker({
						changeMonth: true,
						changeYear: true,
					})

					$( "#txt-date-fin" ).datepicker({
						changeMonth: true,
						changeYear: true,
					})
				});
			}

			var miPopup
			function abririPopup() {
				miPopup = window.open("prueba.php","miwin","width=500,height=400,scrollbars=yes")
				miPopup.focus()
			}
		</script>
	</head>
	<body>
		<h2 align="center" style="color:#336699">DETALLE DE VENTA DE TIENDA</h2>
		<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
		<form name="form1" method="post" action="generar_rep_detalle_ventas.php"> PUNTO DE VENTAS: 
			<select name="cod_almacen" >
			<?php
			print "<option value='$cod_almacen' >$cod_almacen -- $almacen_dis</option>";
			for($i=0;$i<pg_numrows($rsx1);$i++) {
				$B = pg_fetch_row($rsx1,$i);
				print "<option value='$B[0]' >$B[0] -- $B[1]</option>";
			}
			?>
			</select>
			<table align="center" border="0">
				<tr>
					<th colspan="7"> <h3> RANGO DE FECHAS </h3> </th>
				</tr>
				<tr>
					<th> DESDE: </th>
					<th>
						<p>
							<input type="text" id="txt-date-ini" name="v_fecha_desde" size="16" maxlength="10" value='<?php echo $v_fecha_desde ; ?>'  tabindex="1"  onKeyUp="javascript:validarFecha(this)" onblur="javascript:validarFecha(this)"  >
							<!--
							<a href="javascript:show_calendar('form1.v_fecha_desde');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;" >
								<img src="/sistemaweb/images/showcalendar.gif" border=0>
							</a>
						-->
						</p>
					</th>
				</tr>
				<tr>
					<th> HASTA: </th>
					<th>
						<p>
							<input type="text" id="txt-date-fin" name="v_fecha_hasta" size="16" maxlength="10" value='<?php echo $v_fecha_hasta ; ?>'  tabindex="3" onKeyUp="javascript:validarFecha(this)" onblur="javascript:validarFecha(this)">
							<!--
							<a href="javascript:show_calendar('form1.v_fecha_hasta');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;">
								<img src="/sistemaweb/images/showcalendar.gif" border=0>
							</a>
						-->
						</p>
					</th>
				</tr>
				<tr>
					<th colspan="7"><input type="submit" name="consultar" tabindex=5 value="Consultar"></th>
				</tr>
				<tr>
					<td colspan=2>
						<a href="#" onClick="javascript:window.open('/sistemaweb/clases/imprime_samba.php?v_server=<?php echo $v_server; ?>&v_printer=<?php echo $v_printer; ?>&v_ipprint=<?php echo $v_ipprint; ?>&v_archivo=<?php echo $v_archivo_detallado; ?>','miwin','width=800,height=450,scrollbars=yes,menubar=yes,left=0,top=0');"> Impresion Texto Detalle</a>
					</td>
					<td colspan=2>
						<a href="#" onClick="javascript:window.open('/sistemaweb/clases/imprime_samba.php?v_server=<?php echo $v_server; ?>&v_printer=<?php echo $v_printer; ?>&v_ipprint=<?php echo $v_ipprint; ?>&v_archivo=<?php echo $v_archivo; ?>','miwin','width=800,height=450,scrollbars=yes,menubar=yes,left=0,top=0');"> Impresion Texto Resumen</a>
					</td>
				</tr>
			</table>
			<p align="center">
				<strong>
					<font size="2" face="Arial, Helvetica, sans-serif">
						Detalle de Ventas del Punto <?php echo $cod_almacen." - ".$almacen_dis;?> DEL: <?php echo $v_fecha_desde;?> AL: <?php echo $v_fecha_hasta;?>
					</font>
				</strong>
			</p>
<!--
<strong>
<font size="2" face="Arial, Helvetica, sans-serif">
<a href="#" onClick="javascript:window.open('reporte_detalle_ventas.php?v_fecha_desde=<?php echo $v_fecha_desde;?>&v_fecha_hasta=<?php echo $v_fecha_hasta;?>&cod_almacen=<?php echo trim($cod_almacen);?>&almacen_dis=<?php echo $almacen_dis;?>','miwin','width=800,height=350,scrollbars=yes,menubar=yes,left=0,top=0');">
Exportar Reporte
</a>
</font>
</strong>
<br>

<a href="/sistemaweb/utils/impresiones.php?imprimir=ok&archivo=/sistemaweb/ventas_clientes/detalle_venta_resumido.txt" target="_blank">
Imprimir Resumen
</a>
<br>
-->
<!-- {Tabla}-->
<?php
	$cod_almacen = trim($cod_almacen);
	$q = "select 
			to_char(FIRST(c.dt_fac_fecha),'dd/mm/yyyy') as dt_fac_fecha,
			FIRST(trim(c.ch_fac_seriedocumento))        as serie,
			FIRST(c.ch_fac_numerodocumento)             as ch_fac_numerodocumento, 
			FIRST(c.ch_fac_tipodocumento)               as ch_fac_tipodocumento, 
			FIRST(c.cli_codigo)                         as cli_codigo,
			art.art_linea								art_linea
		from fac_ta_factura_cabecera c
			left join fac_ta_factura_detalle d on (c.ch_fac_tipodocumento = d.ch_fac_tipodocumento and c.ch_fac_seriedocumento = d.ch_fac_seriedocumento and c.ch_fac_numerodocumento = d.ch_fac_numerodocumento and c.cli_codigo = d.cli_codigo)
			left join int_articulos art on (d.art_codigo = art.art_codigo)
		where 
			c.dt_fac_fecha between '".$funcion->date_format($v_fecha_desde,'YYYY-MM-DD')."' and '".$funcion->date_format($v_fecha_hasta,'YYYY-MM-DD')."'
			and trim(c.ch_fac_seriedocumento) = '$cod_almacen'
			and c.ch_fac_tipodocumento = '45'
		group by
			art.art_linea
		order by 
			dt_fac_fecha,ch_fac_tipodocumento,ch_fac_numerodocumento";
	$rs2 = pg_exec($q);

	// echo "<pre>";
	// echo "EL QUERY !!".$q;
	// echo "</pre>";

	$lineadt="<table>";
			 	//	     1         2	 3	   4	     5	       6	 7	   8	     9	       10	 11	   12	     13	      14
                                // 012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456
				// FECHA       NRO.DOCUMENTO
				// CODIGO        DESCRIPCION            CANTIDAD    PRECIO   VAL.VENTA   IMPUESTOS       TOTAL
				// 99/99/9999  45  999-9999999 
				// 9999999999999 XXXXXXXXXXXXXXXXXXXX 999999.999 9999.9999 99999999.99 99999999.99 99999999.99
	$lineadt=$lineadt."<tr><td>                             DETALLE  DE  VENTAS  DE  TIENDA        </td></tr>";
	$lineadt=$lineadt."<tr><td>                             DEL: $v_fecha_desde  AL: $v_fecha_hasta    </td></tr>";
	$lineadt=$lineadt."<tr><td>PUNTO DE VENTA : ".$cod_almacen." - ".$almacen_dis."</td></tr>";
        $lineadt=$lineadt."<tr><td>FECHA       NRO.DOCUMENTO </td></tr>";
	$lineadt=$lineadt."<tr><td>CODIGO        DESCRIPCION            CANTIDAD    PRECIO   VAL.VENTA   IMPUESTOS       TOTAL</td></tr>";
	$lineadt=$lineadt."<tr><td>-------------------------------------------------------------------------------------------</td></tr>";
	$totalg_venta = 0;
	$totalg_imp = 0;
	$totalg = 0;

	for($j=0;$j<pg_numrows($rs2);$j++)
	{
		$B = pg_fetch_array($rs2,$j);

                $lineadt=$lineadt."<tr><td>".str_pad($B[0],11).str_pad($B[3],3," ",STR_PAD_LEFT).str_pad($B[1],4," ",STR_PAD_LEFT).str_pad($B[2],8," ",STR_PAD_LEFT)."</td></tr>";

		?>
		<font size="-4" face="Arial, Helvetica, sans-serif"><strong><em>
		<?php echo $B[0];?> -
		<?php echo $B[3];?> - <?php echo $B[1];?>
		<?php echo $B[2];?></em></strong> </font>


  		<table width="760" border="1" cellpadding="0" cellspacing="0">
			<tr>
			<td width="267"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"></font><font size="-4" face="Arial, Helvetica, sans-serif">ITEM</font></div></td>
			<td width="48"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">CANTIDAD</font></div></td>
			<td width="76"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">PREC.VENTA</font></div></td>
			<td width="67"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">PREC.LISTA</font></div></td>
			<td width="74"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">DIFERENCIA</font></div></td>
			<td width="72"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">VALOR VENTA </font></div></td>
			<td width="65"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">IMPUESTOS</font></div></td>
			<td width="73"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">TOTAL</font></div></td>
			</tr>
			<?php
			$T = 0;
			//$rs1 = reporte_detalle_ventas_fac($B[3],$B[1],$B[2],$B[4],$B[0]);
	
			$det1="select 
						det.art_codigo,
						art.art_descbreve,
						det.nu_fac_cantidad as CANTIDAD,
						det.nu_fac_precio AS PREC_VENTA,
						det.pre_lista_precio AS PREC_LISTA,
						det.nu_fac_importeneto AS VALOR_VENTA,
						det.nu_fac_impuesto1 AS IMPUESTOS,
						det.nu_fac_valortotal as TOTAL 
						from
						fac_ta_factura_detalle det, 
						int_articulos art, 
						fac_ta_factura_cabecera cab
						where det.art_codigo=art.art_codigo 
						and trim(det.ch_fac_tipodocumento)  = '".$B[3]."' 
						and trim(det.ch_fac_seriedocumento) = '".$B[1]."' 
						and det.ch_fac_numerodocumento      = '".$B[2]."' 
						and det.cli_codigo                  = '".$B[4]."' 
						and art.art_linea				    = '".$B[5]."'
						and det.ch_fac_tipodocumento   = cab.ch_fac_tipodocumento 
						and det.ch_fac_seriedocumento  = cab.ch_fac_seriedocumento  
						and det.ch_fac_numerodocumento = cab.ch_fac_numerodocumento 
						and det.cli_codigo             = cab.cli_codigo 
						and cab.dt_fac_fecha = '".$funcion->date_format($B[0],'YYYY-MM-DD')."';
						";		

			$rs1=pg_exec($det1);

			// echo "<pre>";
			// echo "EL QUERY det1!!".$det1;
			// echo "</pre>";

			$total_venta = 0;
			$total_imp = 0;
			$total = 0;
			for($i=0;$i<pg_numrows($rs1);$i++){
				$A = pg_fetch_array($rs1,$i);
				$total_venta = $total_venta + $A[5];
				$total_imp = $total_imp + $A[6];
				$total = $total + $A[7];
				$totalg_venta = $totalg_venta + $A[5];
				$totalg_imp = $totalg_imp + $A[6];
				$totalg = $totalg + $A[7];

				$T = $T + $total;
				$xx = $xx."xx";
                $lineadt=$lineadt."<tr><td>".str_pad($A[0],14).str_pad($A[1],21).str_pad($A[2],10," ",STR_PAD_LEFT).str_pad($A[3],10," ",STR_PAD_LEFT).str_pad($A[5],12," ",STR_PAD_LEFT).str_pad($A[6],12," ",STR_PAD_LEFT).str_pad($A[7],12," ",STR_PAD_LEFT)."</td></tr>";
				print '
				<!-- ?> -->
				<tr>
				<td><div align="left"><font size="-4" face="Arial, Helvetica, sans-serif">'.$A[0].' - <font face="Arial, Helvetica, sans-serif"><font size="-4" face="Arial, Helvetica, sans-serif">'.$A[1].'</font></font></font></div></td>
				<td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"><font size="-4" face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4" face="Arial, Helvetica, sans-serif">'.$A[2].'</font></font></font><font face="Arial, Helvetica, sans-serif"></font></font></div></td>
				<td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"><font size="-4" face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4" face="Arial, Helvetica, sans-serif">'.$A[3].'</font></font></font><font face="Arial, Helvetica, sans-serif"></font></font></div></td>
				<td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif">-</font></font></div></td>
				<td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif">-</font></div></td>
				<td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"><font size="-4" face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4" face="Arial, Helvetica, sans-serif">'.$A[5].'</font></font></font></font></div></td>
				<td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"><font size="-4" face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4" face="Arial, Helvetica, sans-serif">'.$A[6].'</font></font></font></font></div></td>
				<td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"><font size="-4" face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4" face="Arial, Helvetica, sans-serif">'.$A[7].'</font></font></font></font></div></td>
				</tr>
				';
			}
                $lineadt=$lineadt."<tr><td>".str_pad(" ",14).str_pad(" ",21).str_pad(" ",10," ",STR_PAD_LEFT).str_pad("TOTAL ->",10," ",STR_PAD_LEFT).str_pad(number_format($total_venta, 2, '.', ''),12," ",STR_PAD_LEFT).str_pad(number_format($total_imp, 2, '.', ''),12," ",STR_PAD_LEFT).str_pad(number_format($total, 2, '.', ''),12," ",STR_PAD_LEFT)."</td></tr>";
			?>
			<tr>
			<td height="28"> <div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"><strong>TOTAL</strong></font></div></td>
			<td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"></font></div></td>
			<td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"></font></div></td>
			<td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"></font></div></td>
			<td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"></font></div></td>
			<td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $total_venta;?></font></div></td>
			<td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $total_imp;?></font></div></td>
			<td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $total;?></font></div></td>
			</tr>
		
		<!-- {TABLA}-->
		<?php
	}
			?>
			<tr>
			<td height="28"> <div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"><strong>TOTAL GENERAL</strong></font></div></td>
			<td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"></font></div></td>
			<td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"></font></div></td>
			<td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"></font></div></td>
			<td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"></font></div></td>
			<td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $totalg_venta;?></font></div></td>
			<td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $totalg_imp;?></font></div></td>
			<td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $totalg;?></font></div></td>
			</tr>
		</table>
		<?php

	echo "----------".$T;
        $lineadt=$lineadt."<tr><td>".str_pad(" ",14).str_pad(" ",21).str_pad("TOTAL GENERAL ->",20," ",STR_PAD_LEFT).str_pad(number_format($totalg_venta, 2, '.', ''),12," ",STR_PAD_LEFT).str_pad(number_format($totalg_imp, 2, '.', ''),12," ",STR_PAD_LEFT).str_pad(number_format($totalg, 2, '.', ''),12," ",STR_PAD_LEFT)."</td></tr>";
	?>
	<br>
	<font size="-4" face="Arial, Helvetica, sans-serif">
	<!--SEGUNDO PROCEDIMIENTO -->
	<!-- FRED _ peque� modificacion para poder imprimir en formato Texto -->

	<!-- por siak falta un peque� cuadro donde se detalla los productos que se vendieron con un precio distinto al de venta  -->

	<?php
        $lineadt=$lineadt."</table>";
        $col[0]=100;
        $nom[0]= "LINEA";
	imprimir2( $lineadt, $col, $nom, $v_archivo_detallado, "Reporte Detalle" );



	$col[0]=80;
        $nom[0]= "LINEA";

	$linea="<table>";
	$linea=$linea."<tr><td>ALMACEN : ".$cod_almacen." - ".$almacen_dis."</td></tr>";
	$linea=$linea."<tr><td>                  R E S U M E N   D E T A L L E   D E   V E N T A             </td></tr>";
	$linea=$linea."<tr><td>                  RESUMEN POR TIPO DEL: $v_fecha_desde  AL: $v_fecha_hasta    </td></tr>";
	$linea=$linea."<tr><td>TIPO ITEM                   VALOR NETO     IMPUESTO      TOTAL     PORCENTAJE </td></tr>";

/*
	$ft=fopen('detalle_venta_resumido.txt','w');
	if ($ft>0)
	{
		$snewbuffer=$snewbuffer."                  R E S U M E N   D E T A L L E   D E   V E N T A                   \n";
		$snewbuffer=$snewbuffer."                  RESUMEN POR TIPO DEL: $v_fecha_desde  AL: $v_fecha_hasta \n\n";
		$snewbuffer=$snewbuffer."TIPO ITEM                   VALOR NETO     IMPUESTO      TOTAL     PORCENTAJE \n";
		$snewbuffer=$snewbuffer."==============================================================================\n";
	}
*/


	//ESTO ES EL ENLACE PARA LA IMPRESION DIRECTA DEL TEXTO
	//&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="impresiones.php?imprimir=ok&archivo=aj_inv_fisico.txt" target="_blank">Imprimir</a>
	?>

	RESUMEN POR TIPO DEL:<?php echo $v_fecha_desde;?> AL <?php echo $v_fecha_hasta;?>
	<table border="1" cellspacing="0" cellpadding="0">
		<tr>
		<td width="159"><div align="left"><font size="-4" face="Arial, Helvetica, sans-serif">TIPO ITEM</font></div></td>
		<td width="77"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">VALOR NETO </font></div></td>
		<td width="67"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">IMPUESTOS</font></div></td>
		<td width="52"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">TOTAL</font></div></td>
		<td width="109"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">PORCENTAJE</font></div></td>
		</tr>
		<!--BEGIN FILA
		<?php

		$rs22 = reporte_detalle_ventasxtipo($cod_almacen,$v_fecha_desde,$v_fecha_hasta);
		for($i=0;$i<pg_numrows($rs22);$i++)
		{
			$S = pg_fetch_array($rs22,$i);
			$total_vn2 = $total_vn2 + $S[1];
			$total_imp2 = $total_imp2 + $S[2];
			$total2 =  $total2 + $S[3];
		}
		echo "MIk ".pg_numrows($rs22);
		for($i=0;$i<pg_numrows($rs22);$i++)
		{
			$S = pg_fetch_array($rs22,$i);
			$porcentaje = 100*($S[1]/$total_vn2);
			$porcentaje = round($porcentaje,2);
			print '
			-->
			<tr>
			<td><div align="left"><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$S[0].'</font></div></td>
			<td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif">'.$S[1].'</font></div></td>
			<td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif">'.$S[2].'</font></div></td>
			<td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif">'.$S[3].'</font></div></td>
			<td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif">'.$porcentaje.'%</font></div></td>
			</tr>
			<!-- END FILA
			';
			$linea=$linea."<tr><td>".str_pad($S[0],23).str_pad($S[1],13," ",STR_PAD_LEFT).str_pad($S[2],13," ",STR_PAD_LEFT).str_pad($S[3],13," ",STR_PAD_LEFT).str_pad($porcentaje."%",13," ",STR_PAD_LEFT)."</td></tr>";

			//$snewbuffer=$snewbuffer.str_pad($S[0],23).str_pad($S[1],13," ",STR_PAD_LEFT).str_pad($S[2],13," ",STR_PAD_LEFT).str_pad($S[3],13," ",STR_PAD_LEFT).str_pad($porcentaje."%",13," ",STR_PAD_LEFT)."\n";
		}

		$linea=$linea."<tr><td>==============================================================================</td></tr>";
		//$snewbuffer=$snewbuffer."==============================================================================\n";
		?>
		-->

		<tr>
		<td><div align="left"><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;TOTALES	DEL RESUMEN</font></div></td>
		<td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $total_vn2;?></font></div></td>
		<td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;<?php echo $total_imp2;?></font></div></td>
		<td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $total2;?>&nbsp;</font></div></td>
		<td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif">100%&nbsp;</font></div></td>
		</tr>
	</table>
	<font size="-4" face="Arial, Helvetica, sans-serif">
	<?php
	$linea=$linea."<tr><td>".str_pad("TOTAL DE RESUMEN :",23).str_pad($total_vn2,13," ",STR_PAD_LEFT).str_pad($total_imp2,13," ",STR_PAD_LEFT).str_pad($total2,13," ",STR_PAD_LEFT).str_pad("100%",13," ",STR_PAD_LEFT)."</td></tr>";
/*
	$snewbuffer=$snewbuffer.str_pad("TOTAL DE RESUMEN :",23).str_pad($total_vn2,13," ",STR_PAD_LEFT).str_pad($total_imp2,13," ",STR_PAD_LEFT).str_pad($total2,13," ",STR_PAD_LEFT).str_pad("100%",13," ",STR_PAD_LEFT)."\n";
	// $snewbuffer=$snewbuffer."\t\t\t\t\t\t\t\t\t\t TOTAL \t\t".$totrep6." \t  ".$totrep7." \n";
	fwrite($ft,$snewbuffer);
	fclose($ft);
*/

	?>
	<!--FIN DEL SEGUNDO PROCEDIEMIENTO -->

	<?php

	$v_anomes = substr( $v_fecha_desde, 6, 4 ) . substr( $v_fecha_desde, 3, 2 );

	$xsqlcheck=pg_exec($coneccion,"select tablename from pg_tables where tablename='pos_trans".$v_anomes."' " );
	if(pg_numrows($xsqlcheck)>0)
		{
		$v_sqlprndev="select trans, to_char(fecha,'YYYY-MM-DD HH24:MM:SS') as fechahora, codigo, substr(int_articulos.art_descripcion,1,20), cantidad, importe 
				from pos_trans".$v_anomes."
				left join int_articulos on trim(pos_trans".$v_anomes.".codigo)=trim(int_articulos.art_codigo)
				where es='".$almacen."'
				and dia
				between '".$funcion->date_format($v_fecha_desde,'YYYY-MM-DD')." 00:00:00.0000'
				and '".$funcion->date_format($v_fecha_hasta,'YYYY-MM-DD')." 23:59:59.9999'
				and tm='D'
				and tipo='M'
				order by fechahora";		

		// echo $v_sqlprnval;
		$v_xsqlprndev=pg_exec($v_sqlprndev);
		$v_ilimitdev=pg_numrows($v_xsqlprndev);
	
	
		$col[0]=5;
		$col[1]=20;
		$col[2]=13;
		$col[3]=20;
		$col[4]=10;
		$col[5]=10;
		$col[6]=10;
	
		echo "<table width='600' border='2' cellspacing=0 height='81'>";
		echo "<tr>";
		echo "	<th width='85'  align='center' colspan=8 >Devoluciones del ".$v_fecha_desde." al ".$v_fecha_hasta."</td>";
		echo "</tr>";
	
		$linea=$linea. "<tr></tr>";
		$linea=$linea. "<tr>";
		$linea=$linea. "<td>".str_pad( "Devoluciones del Trabajador del ".$v_fecha_desde." al ".$v_fecha_hasta  ,$col[0]+$col[1]+$col[2]+$col[3]+$col[4]+$col[5] , " ", STR_PAD_BOTH )."</td>";
		$linea=$linea. "</tr>";
	
		echo "<tr>";
		echo "	<th width='85'  align='center'>Trans</td>";
		echo "	<th width='85'  align='center'>Fecha Hora</td>";
		echo "	<th width='85'  align='center'>Codigo</td>";
		echo "	<th width='106' align='center'colspan=3>Nombre</td>";
		echo "	<th width='106' align='center'>&nbsp;Cantidad</td>";
		echo "	<th width='85'  align='center'>&nbsp;Importe</td>";
		echo "	<th width='85'  align='center'>&nbsp;Total</td>";
		echo "</tr>";

		$linea=$linea. "<tr>";
		$linea=$linea. "<td>".str_pad( "Trans"        ,$col[0] )."</td>";
		$linea=$linea. "<td>".str_pad( "Fecha Hora"   ,$col[1] , " ", STR_PAD_BOTH )."</td>";
		$linea=$linea. "<td>".str_pad( "Codigo"       ,$col[2] )."</td>";
		$linea=$linea. "<td>".str_pad( "Nombre"       ,$col[3] )."</td>";
		$linea=$linea. "<td>".str_pad( "Cantidad"     ,$col[4] , " ", STR_PAD_LEFT )."</td>";
		$linea=$linea. "<td>".str_pad( "Importe"      ,$col[5] , " ", STR_PAD_LEFT )."</td>";
		$linea=$linea. "<td>".str_pad( "Total"        ,$col[6] , " ", STR_PAD_LEFT )."</td>";
		$linea=$linea. "</tr>";
		$tot_trans=0;
		$tot_tot=0;
	
	
		$v_irowdev=0;
		while ( $v_irowdev < $v_ilimitdev )
			{
			$dev0=pg_result( $v_xsqlprndev, $v_irowdev, 0 );
			$clave=$dev0;
			$dev1=pg_result( $v_xsqlprndev, $v_irowdev, 1 );
			$dev2=pg_result( $v_xsqlprndev, $v_irowdev, 2 );
			$dev3=pg_result( $v_xsqlprndev, $v_irowdev, 3 );
			$dev4=pg_result( $v_xsqlprndev, $v_irowdev, 4 );
			$dev5=pg_result( $v_xsqlprndev, $v_irowdev, 5 );
			echo "<tr>";
			echo "<td align='left' >&nbsp;".$dev0." </td>";
			echo "<td align='left' >&nbsp;".$dev1." </td>";
			echo "<td align='left' >&nbsp;".$dev2." </td>";
			echo "<td align='left' colspan=3>&nbsp;".$dev3." </td>";
			echo "<td align='right'  >&nbsp;".number_format($dev4, 2, '.', '')." </td>";
			echo "<td align='right'  >&nbsp;".number_format($dev5, 2, '.', '')." </td>";
			$linea=$linea. "<tr>";
			$linea=$linea. "<td>".str_pad( $dev0 ,$col[0] )."</td>";
			$linea=$linea. "<td>".str_pad( $dev1 ,$col[1] )."</td>";
			$linea=$linea. "<td>".str_pad( $dev2 ,$col[2] )."</td>";
			$linea=$linea. "<td>".str_pad( $dev3 ,$col[3] )."</td>";
			$linea=$linea. "<td>".str_pad( number_format($dev4, 2, '.', ''), $col[4], " ", STR_PAD_LEFT )."</td>";
			$linea=$linea. "<td>".str_pad( number_format($dev5, 2, '.', ''), $col[5], " ", STR_PAD_LEFT )."</td>";
			$tot_trans=$tot_trans+$dev5;
			$tot_tot=$tot_tot+$dev5;
			$v_irowdev++;
			if ($v_irowdev < $v_ilimitdev )
			{
				$dev0=pg_result( $v_xsqlprndev, $v_irowdev, 0 );
				if ($clave!=$dev0)
				{
					echo "<td align='right'  >&nbsp;".number_format($tot_trans, 2, '.', '')." </td>";
					$linea=$linea. "<td>".str_pad( number_format($tot_trans, 2, '.', ''), $col[6], " ", STR_PAD_LEFT )."</td>";
					//7757860160902  9.90
					$tot_trans=0;
				}
				else
				{
					echo "<td align='left' >&nbsp;</td>";
					$linea=$linea. "<td> </td>";
				}
			}
			else
			{
				echo "<td align='right'  >&nbsp;".number_format($tot_trans, 2, '.', '')." </td>";
				$linea=$linea. "<td>".str_pad( number_format($tot_trans, 2, '.', ''), $col[6], " ", STR_PAD_LEFT )."</td>";
				$tot_trans=0;
			}
			echo "</tr>";
			$linea=$linea. "</tr>";
			}
		echo "<tr>";
		echo "	<th width='85'  align='center'>&nbsp;</td>";
		echo "	<th width='85'  align='center'>&nbsp;</td>";
		echo "	<th width='85'  align='center'>&nbsp;</td>";
		echo "	<th width='106' align='center'colspan=3>&nbsp;</td>";
		echo "	<th width='106' align='center'>&nbsp;</td>";
		echo "	<th width='85'  align='right'>&nbsp;Total</td>";
		echo "	<th width='85'  align='right'>&nbsp;".number_format($tot_tot, 2, '.', '')."</td>";
		echo "</tr>";
		$linea=$linea. "<tr>";
		$linea=$linea. "<td>".str_pad( " " ,$col[0] )."</td>";
		$linea=$linea. "<td>".str_pad( " " ,$col[1] )."</td>";
		$linea=$linea. "<td>".str_pad( " " ,$col[2] )."</td>";
		$linea=$linea. "<td>".str_pad( " " ,$col[3] )."</td>";
		$linea=$linea. "<td>".str_pad( " " ,$col[4] )."</td>";
		$linea=$linea. "<td>".str_pad( "Total"        ,$col[5] , " ", STR_PAD_LEFT )."</td>";
		$linea=$linea. "<td>".str_pad( number_format($tot_tot, 2, '.', ''), $col[6], " ", STR_PAD_LEFT )."</td>";
		$linea=$linea. "</tr>";
		}

	echo "</table>";
	$linea=$linea."</table>";

	imprimir2( $linea, $col, $nom, $v_archivo, "Reporte Resumen" );
	?>


	</font><br>
	<p>&nbsp; </p>
</form>
<br>
</body>
</html>
<?php pg_close(); ?>
