<?php
if(isset($_GET['v']) && $_GET['v'] == 'old') {


include("../menu_princ.php");
include("../functions.php");
include("/sistemaweb/utils/funcion-texto.php");
include("js/funciones.php");
include("store_procedures.php");
require("../clases/funciones.php");

extract($_REQUEST);

$funcion 	= new class_funciones;
$clase_error 	= new OpensoftError;
$coneccion 	= $funcion->conectar("","","","","");

$sqlalma  = "select ch_almacen, ch_nombre_almacen from inv_ta_almacenes where ch_clase_almacen='1' order by ch_almacen;";
$res_alma = pg_exec($sqlalma);
for ($j = 0; $j < pg_numrows($res_alma); $j++){
	$almas[$j]['cod'] = pg_result($res_alma,$j,0);
	$almas[$j]['nom'] = pg_result($res_alma,$j,1);
}
$almacen = $_POST['almacen'];

$sqlalma2  = "select ch_almacen, ch_nombre_almacen from inv_ta_almacenes where ch_clase_almacen='1' and ch_almacen='$almacen';";
$res_alma2 = pg_exec($sqlalma2);
$almacen_nombre = pg_result($res_alma,0,1);




if (is_null($v_fecha_desde) or is_null($v_fecha_hasta) )
	{
	$v_fecha_desde=date("d/m/Y");
	$v_fecha_hasta=date("d/m/Y");
	}

$v_ilimit=0;

if($boton=="buscar")
	{
	$rs = reporte_movdia( $funcion->date_format($v_fecha_desde,'YYYY-MM-DD')." 00:00:00", $funcion->date_format($v_fecha_hasta,'YYYY-MM-DD')." 23:59:59", $almacen);

	//var_dump($rs);

	$v_ilimit=pg_numrows($rs);
	
	// carga las variables para mandar el reporte a impresion texto
	$v_sqlx ="select par_valor from int_parametros where trim(par_nombre)='print_netbios' ";
	$v_xsqlx=pg_exec( $v_sqlx);
	$v_server =pg_result($v_xsqlx,0,0);

	$v_sqlx ="select par_valor from int_parametros where trim(par_nombre)='print_name' ";
	$v_xsqlx=pg_exec($v_sqlx);
	$v_printer=pg_result($v_xsqlx,0,0);

	$v_sqlx ="select par_valor from int_parametros where trim(par_nombre)='print_server' ";
	$v_xsqlx=pg_exec($v_sqlx);
	$v_ipprint=pg_result($v_xsqlx,0,0);

	$v_archivo="/tmp/imprimir/inv_movdia.txt";

	}
else
	{
	//$rs = reporte_movdia( $funcion->date_format($v_fecha_desde,'YYYY-MM-DD')." 00:00:00", $funcion->date_format($v_fecha_hasta,'YYYY-MM-DD')." 23:59:59",$almacen);
	}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Movimientos por Rangos de Fechas</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<script language="JavaScript" src="/sistemaweb/clases/calendario.js"></script>
<script language="JavaScript" src="/sistemaweb/clases/overlib_mini.js"></script>
<form name="form1" method="post" action="">
MOVIMIENTOS POR RANGO DE FECHA
ALMACEN ACTUAL <?php echo $almacen;?> 	<?php echo $almacen_nombre; ?>

<table border="1">
	<tr>
		<th colspan="7">Reporte Por : RANGO DE FECHAS </th>
	</tr>
	<tr>
		<th colspan="5">Almacen :
		<select name="almacen">
		<?php
			for($m = 0; $m < count($almas); $m++) {
				if(trim($almacen)==trim($almas[$m]['cod'])) {
					echo "<option selected value=".trim($almas[$m]['cod']).">".$almas[$m]['cod']." - ".$almas[$m]['nom']."</option>";
				} else {
					echo "<option value=".trim($almas[$m]['cod']).">".$almas[$m]['cod']." - ".$almas[$m]['nom']."</option>";
				}
			}
		?>
		</select></th>
	</tr>
	<tr>
		<th>DESDE :</th>
		<th>
		<p>
		<input type="text" name="v_fecha_desde" size="16" maxlength="10" value='<?php echo $v_fecha_desde ; ?>'  tabindex="1"  onKeyUp="javascript:validarFecha(this)" onblur="javascript:validarFecha(this)"  >
		<a href="javascript:show_calendar('form1.v_fecha_desde');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;" >
		<img src="/sistemaweb/images/showcalendar.gif" border=0></a>
		</p>
		</th>
	</tr>
	<tr>
		<th>HASTA:</th>
		<th>
		<p>
		<input type="text" name="v_fecha_hasta" size="16" maxlength="10" value='<?php echo $v_fecha_hasta ; ?>'  tabindex="3" onKeyUp="javascript:validarFecha(this)" onblur="javascript:validarFecha(this)">
		<a href="javascript:show_calendar('form1.v_fecha_hasta');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;">
		<img src="/sistemaweb/images/showcalendar.gif" border=0></a>
		</p>
		</th>
	</tr>
	<tr>
		<td colspan="2" align="center"><input name="boton" type="submit" value="buscar"></td>
	</tr>

	<tr>
		<td colspan=2>
		<a href="#" onClick="javascript:window.open('/sistemaweb/clases/imprime_samba.php?v_server=<?php echo $v_server; ?>&v_printer=<?php echo $v_printer; ?>&v_ipprint=<?php echo $v_ipprint; ?>&v_archivo=<?php echo $v_archivo; ?>','miwin','width=800,height=450,scrollbars=yes,menubar=yes,left=0,top=0');"> Impresion Texto </a>
	</td>

	</tr>
</table>

<?php


$timpotik=0;
$sttimpotik=0;
$stimpotik=0;

$col[0]=15;
$col[1]=30;
$col[2]=15;
$col[3]=15;
$col[4]=15;
$col[5]=15;
$col[6]=15;


$nom[0]= "CODIGO";
$nom[1]= "DESCRIPCION";
$nom[2]= "STK INICIAL";
$nom[3]= "ENTRADAS";
$nom[4]= "SALIDAS";
$nom[5]= "AJUSTES";
$nom[6]= "STK FINAL";

$linea="";

if($v_ilimit>0)
	{
	echo "<table border='1' cellpadding='0' cellspacing='0'>";
	//echo "<table width='990' border='2' cellspacing=0 height='81'>";
		echo "<tr>";
		echo "<th colspan=7 align='center'> <BR>
		REPORTE MOVIMIENTO ACUMULADO POR RANGO FECHA<BR>
		Desde: ".$v_fecha_desde." Hasta: ".$v_fecha_hasta.
		" Almacen : ".$almacen." - ".$almacen_nombre."<BR>&nbsp;</th> "  ;
		echo "</tr>";

		echo "<tr>";
		echo "	<th>CODIGO</th>";
		echo "	<th>DESCRIPCION</th>";
		echo "	<th>STK INICIAL</th>";
		echo "	<th>ENTRADAS</th>";
		echo "	<th>SALIDAS</th>";
		echo "	<th>AJUSTES</th>";
		echo "	<th>STK FINAL</th>";
		echo "</tr>";

		$linea=$linea."<table>";
		$linea=$linea."<tr>";
		$linea=$linea."<td>".str_pad("REPORTE MOVIMIENTO ACUMULADO POR RANGO FECHA",$col[0]+$col[1]+$col[2]+$col[3]+$col[4]+$col[5]+$col[6], " ", STR_PAD_BOTH )."</td>";
		$linea=$linea."</tr>";
		$linea=$linea."<tr>";
		$linea=$linea."<td>".str_pad("Desde: ".$v_fecha_desde." Hasta: ".$v_fecha_hasta." Almacen : ".$almacen." - ".$almacen_nombre ,$col[0]+$col[1]+$col[2]+$col[3]+$col[4]+$col[5]+$col[6], " ", STR_PAD_BOTH )."</td>";
		$linea=$linea."</tr>";

		$linea=$linea. "<tr>";
		$linea=$linea. "<td>".str_pad( "Codigo"       ,$col[0] )."</td>";
		$linea=$linea. "<td>".str_pad( "Descripcion"  ,$col[1] )."</td>";
		$linea=$linea. "<td>".str_pad( "Stk Inicial"  ,$col[2] , " ", STR_PAD_LEFT )."</td>";
		$linea=$linea. "<td>".str_pad( "Entradas"     ,$col[3] , " ", STR_PAD_LEFT )."</td>";
		$linea=$linea. "<td>".str_pad( "Salidas"      ,$col[4] , " ", STR_PAD_LEFT )."</td>";
		$linea=$linea. "<td>".str_pad( "Ajustes"      ,$col[5] , " ", STR_PAD_LEFT )."</td>";
		$linea=$linea. "<td>".str_pad( "Stk Final"    ,$col[6] , " ", STR_PAD_LEFT )."</td>";
		$linea=$linea. "</tr>";

		for($xi=0;$xi<$v_ilimit;$xi++)
			{
			$A = pg_fetch_array($rs,$xi);
			echo "<tr>";
			echo "<td>&nbsp;".substr($A[0],0,15)."</td>";
			echo "<td>&nbsp;".substr($A[1],0,30)."</td>";
			echo "<td align='right'>&nbsp; ".number_format($A[2]+$A[3], 2, '.', '')."</td>";
			echo "<td align='right'>&nbsp; ".number_format($A[4], 2, '.', '')."</td>";
			echo "<td align='right'>&nbsp; ".number_format($A[5], 2, '.', '')."</td>";
			echo "<td align='right'>&nbsp; ".number_format($A[6], 2, '.', '')."</td>";
			echo "<td align='right'>&nbsp; ".number_format($A[2]+$A[3]+$A[4]-$A[5]+$A[6], 2, '.', '')."</td>";
			echo "</tr>";

			$linea=$linea. "<tr>";
			$linea=$linea. "<td>".str_pad( substr($A[0],0,15) ,$col[0] )."</td>";
			$linea=$linea. "<td>".str_pad( substr($A[1],0,30) ,$col[1] )."</td>";
			$linea=$linea. "<td>".str_pad( number_format($A[2]+$A[3], 2, '.', ''), $col[2], " ", STR_PAD_LEFT )."</td>";
			$linea=$linea. "<td>".str_pad( number_format($A[4], 2, '.', ''), $col[3], " ", STR_PAD_LEFT )."</td>";
			$linea=$linea. "<td>".str_pad( number_format($A[5], 2, '.', ''), $col[4], " ", STR_PAD_LEFT )."</td>";
			$linea=$linea. "<td>".str_pad( number_format($A[6], 2, '.', ''), $col[5], " ", STR_PAD_LEFT )."</td>";
			$linea=$linea. "<td>".str_pad( number_format($A[2]+$A[3]+$A[4]-$A[5]+$A[6], 2, '.', ''), $col[6], " ", STR_PAD_LEFT )."</td>";
			$linea=$linea. "</tr>";
			}

	echo "</table>";
	
	$linea=$linea. "</table>";
	imprimir2( $linea, $col, $nom, $v_archivo, "Movimientos Diarios Inventarios" );	
	}

?>
</form>

</body>
</html>
<?php
pg_close();
} else { ?>

<!DOCTYPE html>
<html>
<head>
	<title>Movimientos Acumulados por Rango de Fecha - OpenSoft</title>
	<meta charset="UTF-8">
	<link rel="stylesheet" href="/sistemaweb/css/sistemaweb.css" type="text/css">
	<link rel="stylesheet" href="/sistemaweb/assets/css/style.css" type="text/css">
	<!--<script src="/sistemaweb/js/jquery-2.0.3.js" type="text/javascript"></script>-->
	<script src="/sistemaweb/assets/js/jquery/jquery-3.2.0.min.js" type="text/javascript"></script>
	<script src="/sistemaweb/inventarios/js/movimiento_por_dia.js"></script>
	
</head>
<body>
	<script src="/sistemaweb/js/calendario.js" type="text/javascript" ></script>
	<script src="/sistemaweb/js/overlib_mini.js" type="text/javascript" ></script>
	<script src="/sistemaweb/js/jquery-1.9.1.js" type="text/javascript"></script>
	<link rel="stylesheet" href="/sistemaweb/css/jquery-ui.css" />
	<link rel="stylesheet" href="/sistemaweb/helper/css/style.css" />
	<script src="/sistemaweb/js/jquery-ui.js"></script>
	<script type="text/javascript">

	$(window).load(function() {
		$( function() {
			//alert('hola');
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

			$.datepicker.setDefaults( $.datepicker.regional[ "es" ] );

			$( "#fecha_inicio" ).datepicker({
				changeMonth: true,
				changeYear: true,
			});
			$( "#fecha_final" ).datepicker({
				changeMonth: true,
				changeYear: true,
			});
		})
	})
	</script>

	<?php include "../menu_princ.php"; ?>
	<div id="footer">&nbsp;</div>
	<div id="cargardor" style="position: absolute;display: none"><img src="/sistemaweb/ventas_clientes/liquidacion_vales/cg.gif" /></div>

	<?php

	include('/sistemaweb/include/mvc_sistemaweb.php');
	include('reportes/t_mov_por_dia.php');
	include('reportes/m_mov_por_dia.php');

	//Variables de Entrada
	date_default_timezone_set('UTC');
	
	$hoy = date('d/m/Y');

	$model = new ModelMovPorDias;
	$template = new TemplateMovPorDias;

	$estaciones	= $model->GetAlmacen('T');
	$lineas		= $model->GetLinea();
	echo $template->Form($estaciones, $lineas, $hoy);
	?>
</body>
</html>

<?php }
