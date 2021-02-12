<?php
ob_start();
include("../valida_sess.php");
include("../functions.php");
include("/sistemaweb/utils/funcion-texto.php");
require("../clases/funciones.php");

$funcion = new class_funciones;
$clase_error = new OpensoftError;
$coneccion = $funcion->conectar("","","","","");
$v_grifo = true;
$almacen = $usuario->obtenerAlmacenActual();

if ( is_null($almacen) or trim($almacen) == "") {
	$almacen = "001";
}

if ( trim($almacen) == "001") {
	$v_grifo = false;
}

if (is_null($v_fecha_desde) or is_null($v_fecha_hasta)) {
	$v_fecha_desde = date("d/m/Y");
	$v_fecha_hasta = date("d/m/Y");
	$v_turno_desde = 1;
	$v_turno_hasta = 1;
}
$tipotarjeta = @$_REQUEST['tipo_tarjeta'];
if($tipotarjeta=="T")
	$cond = "";
else
	$cond = " AND at='$tipotarjeta' ";

function tiposTarjeta() {
	global $sqlca;
	
	$q  = "SELECT substring(tab_elemento from 6 for 1), tab_descripcion FROM int_tabla_general WHERE tab_tabla='95' and tab_elemento!='000000';";
	if ($sqlca->query($q) < 0) 
		return false;
	$res = Array();
	for ($i = 0; $i < $sqlca->numrows(); $i++) {
		$a = $sqlca->fetchRow();
		$res[$i]['val'] = $a[0];
		$res[$i]['nom'] = $a[1];
	}
	return $res;
}

$v_anomes = substr( $v_fecha_desde, 6, 4 ) . substr( $v_fecha_desde, 3, 2 );

$v_sqlx    = "select par_valor from int_parametros where trim(par_nombre)='print_netbios' ";
$v_xsqlx   = pg_exec( $v_sqlx);
$v_server  = pg_result($v_xsqlx,0,0);

$v_sqlx    = "select par_valor from int_parametros where trim(par_nombre)='print_name' ";
$v_xsqlx   = pg_exec($v_sqlx);
$v_printer = pg_result($v_xsqlx,0,0);

$v_sqlx    = "select par_valor from int_parametros where trim(par_nombre)='print_server' ";
$v_xsqlx   = pg_exec($v_sqlx);
$v_ipprint = pg_result($v_xsqlx,0,0);

$v_archivo = "/tmp/imprimir/vta_tarjetas_credito.txt";

if ($_REQUEST['boton'] == "excel") {
	ob_end_clean();

	$buff 	 = "Tipo Trans,Num Tarj,Tipo Tarj,Hora Trans,Imp Trans,Num Trans,Fecha Tran,Hora Tran,Imp Tarj,caja\n";
	$mes 	 = date("m");
	$ano 	 = date("Y");
	$totsol	 = 0;
	$totdol	 = 0;
	$totsol1 = 0;
	$totsol2 = 0;

	$sql = "SELECT
			tipo,
			trim(text1),
			at || '-' || g.tab_descripcion,
			to_char(fecha,'HH24:MI'),
			round(soles_km,2),
			trans,
			to_char(fecha,'DD/MM/YYYY'),
			to_char(fecha,'HH24:MI'),
			round(sum(importe),2)
			,t.caja
		FROM
			pos_trans{$v_anomes} t
			LEFT JOIN int_tabla_general g ON trim(t.at)=substring(g.tab_elemento,6,6) AND g.tab_tabla='95'
		WHERE
			t.es = '$almacen'
			AND to_char(dia,'yyyy-mm-dd') || to_char(to_number(trim(turno),'99'),'99') BETWEEN '".$funcion->date_format($v_fecha_desde,'YYYY-MM-DD')."' || to_char(".$v_turno_desde.",'99') AND '".$funcion->date_format($v_fecha_hasta,'YYYY-MM-DD')."' || to_char(".$v_turno_hasta.",'99')
			AND fpago = '2'
			AND td != 'N'
		GROUP BY
			t.tipo,
			t.text1,
			t.at,
			g.tab_descripcion,
			t.usr,
			t.soles_km,
			t.trans,
			t.fecha,
			t.caja;";
	
	//echo $sql;

	$xsql   = pg_exec($coneccion,$sql);
	$ilimit = pg_numrows($xsql);
	$irow   = 0;

	while ($irow < $ilimit) {
		$v_data = pg_fetch_array($xsql,$irow);
		$buff  .= "{$v_data[0]},{$v_data[1]},{$v_data[2]},{$v_data[3]},{$v_data[4]},{$v_data[5]},{$v_data[6]},{$v_data[7]},{$v_data[8]},{$v_data[9]}\n";
		$irow++;
	}

	header("Content-type: text/csv");
	header("Content-Disposition: attachment; filename=\"reporte.csv\"");
	header("Cache-Control: no-cache, must-revalidate");
	header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

	die($buff);
}
ob_end_flush();
include("../menu_princ.php");
?>

<html><link rel="stylesheet" href="<?php echo $v_path_url; ?>sistemaweb.css" type="text/css">
<head>
<title>TARJETAS DE CREDITO</title>
<script language="JavaScript" src="js/miguel.js"></script>
<script language="JavaScript" src="/sistemaweb/js/calendario.js"></script>
<script language="JavaScript" src="/sistemaweb/js/overlib_mini.js"></script>
<script language="JavaScript" src="/sistemaweb/clases/reloj.js"></script>
<script language="JavaScript" src="/sistemaweb/compras/validacion.js"></script>
<script language="JavaScript" src="/sistemaweb/compras/valfecha.js"></script>
<script>
function activa(){
	document.f_repo.v_fecha_desde.select()
	document.f_repo.v_fecha_desde.focus()
}
</script>
</head>

<body><div align="center">
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<form name="f_repo" method="post" >
VENTAS CON TARJETAS DE CREDITO
ALMACEN ACTUAL <?php echo $almacen;?> <?php echo $v_descalma; ?>
<?php
if (is_null($v_fecha_desde) or is_null($v_fecha_hasta)) {
	$v_fecha_desde = date("d/m/Y");
	$v_fecha_hasta = date("d/m/Y");
	$v_turno_desde = 0;
	$v_turno_hasta = 0;
}
?>

<input type="hidden" name="v_primra" value='<?php echo $v_primra; ?>'  >
<table border="1">
	<tr>
		<th colspan="8">Reporte Por : RANGO DE FECHAS </th>
	</tr>
	<tr>
		<th>DESDE :</th>
		<th>
		<p>
		<input type="text" name="v_fecha_desde" size="16" maxlength="10" value='<?php echo $v_fecha_desde ; ?>'  tabindex="1"  onKeyUp="javascript:validarFecha(this)" onblur="javascript:validarFecha(this)"  >
		<a href="javascript:show_calendar('f_repo.v_fecha_desde');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;" >
		<img src="/sistemaweb/images/showcalendar.gif" border=0></a>
		</p>
		</th>
		<th>TURNO :</th>
		<th>
		<input type="text" name="v_turno_desde" size="4" maxlength="2" value='<?php echo $v_turno_desde ; ?>'  tabindex="2"  >
		</th>
	</tr>
	<tr>
		<th>HASTA:</th>
		<th>
		<p>
		<input type="text" name="v_fecha_hasta" size="16" maxlength="10" value='<?php echo $v_fecha_hasta ; ?>'  tabindex="3" onKeyUp="javascript:validarFecha(this)" onblur="javascript:validarFecha(this)">
		<a href="javascript:show_calendar('f_repo.v_fecha_hasta');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;">
		<img src="/sistemaweb/images/showcalendar.gif" border=0></a>
		</p>
		</th>
		<th>TURNO :</th>
		<th>
		<input type="text" name="v_turno_hasta" size="4" maxlength="2" value='<?php echo $v_turno_hasta ; ?>'  tabindex="4"  >
		</th>
	</tr>
	<tr>
		<th>Tipo:</th>
		<th colspan="3">
			<select name="tipo">
				<option value='T'>Todos</option>
				<option value='M'>Market</option>
				<option value='C'>Combustible</option>
			</select>
		</th>
	</tr>
	<tr>
		<th>Tipo de Tarjeta:</th>
		<th colspan="3">
			<select name="tipo_tarjeta">
			<?php 
			echo "<option value='T'>Todos</option>";
			$tt = tiposTarjeta();
			for ($k=0; $k<count($tt); $k++) { 
				echo "<option value='".$tt[$k]['val']."'>".$tt[$k]['nom']."</option>";
			}
			 ?>
			</select>
		</th>
	</tr>
	<tr>
		<th colspan="4"><button type="submit" name="boton" value="buscar"><img src="/sistemaweb/images/search.png" alt="left" />  Buscar</button>&nbsp;<button type="submit" name="boton" value="excel"><img src="/sistemaweb/images/excel_icon.png" alt="left" />  Excel</button></th>
	</tr>
	<tr>
		<td colspan="4" align="center">
		<a href="#" onClick="javascript:window.open('/sistemaweb/clases/imprime_samba.php?v_server=<?php echo $v_server; ?>&v_printer=<?php echo $v_printer; ?>&v_ipprint=<?php echo $v_ipprint; ?>&v_archivo=<?php echo $v_archivo; ?>','miwin','width=800,height=450,scrollbars=yes,menubar=yes,left=0,top=0');"> Impresion Texto </a>
		</td>

	</tr>
</table>
<input type="hidden" name="varx" value="<?php echo $varx;?>">
</form>
<?php
$v_sqlalma  = "SELECT trim(ch_almacen), ch_nombre_almacen FROM inv_ta_almacenes WHERE ch_almacen LIKE '%".trim($almacen)."%' AND ch_clase_almacen='1' ";
$v_xsqlalma = pg_query($coneccion,$v_sqlalma);

if(pg_numrows($v_xsqlalma)>0) {	
	$v_descalma = pg_result($v_xsqlalma,0,1);	
}

$col[0] = 10;
$col[1] = 10;
$col[2] = 20;
$col[3] = 10;
$col[4] = 10;
$col[5] = 10;
$col[6] = 10;
$col[7] = 10;
$col[8] = 10;

$nom[0] = "Tipo Trans";
$nom[1] = "Trans NUm";
$nom[2] = "Tipo Tarj";
$nom[3] = "Hora Trans";
$nom[4] = "Impo Trans";
$nom[5] = "Tarje Num";
$nom[6] = "Fecha Tarj";
$nom[7] = "Hora Tarj";
$nom[8] = "Impo Tarj";

$linea = "";

echo "<table border='1' cellpadding='0'>";
echo "<tr bgcolor='#D9F9B2' align='center'>";
echo "	<th>&nbsp;Tipo Trans</th>";
echo "	<th>&nbsp;Tarje Num</th>";//Trans Num
echo "	<th>&nbsp;Tipo Tarj</th>";
echo "	<th>&nbsp;Caja</th>";//Hora Trans
echo "	<th>&nbsp;Impo Trans</th>";
echo "	<th>&nbsp;Trans Num</th>";//Tarje Num
echo "	<th>&nbsp;Fecha Tarj</th>";
echo "	<th>&nbsp;Hora Tarj</th>";
echo "	<th>&nbsp;Impo Tarj</th>";
echo "</tr>";

$linea = $linea."<table>";
$linea = $linea."<tr>";
$linea = $linea."<td>VENTA CON TARJETAS CREDITO ESTACION:".trim($almacen)."-".$v_descalma."</td>";
$linea = $linea."</tr>";
$linea = $linea."<tr>";
$linea = $linea."<td>Desde: ".$v_fecha_desde." Turno: ".$v_turno_desde." Hasta: ".$v_fecha_hasta." Turno: ".$v_turno_hasta." </td>";
$linea = $linea."</tr>";
$linea = $linea."<tr>";
$linea = $linea."<th>Tipo:".$tipo."</th>";
$linea = $linea."</tr>";

$linea = $linea. "<tr>";
$linea = $linea. "<td>".str_pad( $nom[0], $col[0] )."</td>";
$linea = $linea. "<td>".str_pad( $nom[1], $col[1] , " ", STR_PAD_BOTH )."</td>";
$linea = $linea. "<td>".str_pad( $nom[2], $col[2] , " ", STR_PAD_BOTH )."</td>";
$linea = $linea. "<td>".str_pad( $nom[3], $col[3] , " ", STR_PAD_BOTH )."</td>";
$linea = $linea. "<td>".str_pad( $nom[4], $col[4] , " ", STR_PAD_BOTH )."</td>";
$linea = $linea. "<td>".str_pad( $nom[5], $col[5] , " ", STR_PAD_BOTH )."</td>";
$linea = $linea. "<td>".str_pad( $nom[6], $col[6] , " ", STR_PAD_BOTH )."</td>";
$linea = $linea. "<td>".str_pad( $nom[7], $col[7] , " ", STR_PAD_BOTH )."</td>";
$linea = $linea. "<td>".str_pad( $nom[8], $col[8] , " ", STR_PAD_LEFT )."</td>";
$linea = $linea. "</tr>";
?>
	<?php
	$mes		= date("m");
	$ano		= date("Y");
	$totsol		= 0;
	$totdol		= 0;
	$totsol1	= 0;
	$totsol2	= 0;
	
	$tipocond = $_REQUEST['tipo'];

	$sql_pos_trans	= "	SELECT
					tipo,
					text1,
					at||'-'||int_tabla_general.tab_descripcion,
					trim(usr),			
					round(soles_km,2),
					trans,
					to_char(fecha,'DD/MM/YYYY'),
					to_char(fecha,'HH24:MI'),
					round(sum(importe),2),
					caja			
				FROM
					pos_trans".$v_anomes."
					LEFT JOIN int_tabla_general ON trim(pos_trans".$v_anomes.".at)=substring(int_tabla_general.tab_elemento,6,6) 
					AND tab_tabla ='95'
				WHERE
					es='".$almacen."' $cond";
	if($tipocond == 'C'){
		$sql_pos_trans	.= "AND codigo != '11620307' ";
	}elseif($tipocond == 'GLP'){
		$sql_pos_trans	.= "AND codigo = '11620307' ";
	}
		$sql_pos_trans	.= "AND to_char(dia,'yyyy-mm-dd')||to_char(to_number(trim(turno),'99'),'99')
					BETWEEN '".$funcion->date_format($v_fecha_desde,'YYYY-MM-DD')."'||to_char(".$v_turno_desde.",'99')
					AND '".$funcion->date_format($v_fecha_hasta,'YYYY-MM-DD')."'||to_char(".$v_turno_hasta.",'99')
					AND fpago='2'
					AND td!='N'";

	if ($tipo == 'M') {
		$sql_pos_trans = $sql_pos_trans." AND tipo = 'M' ";
	}elseif($tipo == 'C'){
		$sql_pos_trans = $sql_pos_trans." AND tipo = 'C' ";
	}

	$sql_pos_trans  = $sql_pos_trans." GROUP BY tipo, text1, at,int_tabla_general.tab_descripcion, usr, soles_km, trans, fecha,caja ";

	//echo $sql_pos_trans;
	//echo $tipocond;

	$xsql_pos_trans = pg_exec($coneccion,$sql_pos_trans);
	$ilimit = pg_numrows($xsql_pos_trans);
	$irow = 0;

	while($irow < $ilimit) {
		$v_data = pg_fetch_array($xsql_pos_trans,$irow);
		echo "<tr>";
		echo "<td align=center>&nbsp;".$v_data[0]."</td>";
		echo "<td align=center>&nbsp;".$v_data[1]."</td>";
		echo "<td>&nbsp;".$v_data[2]."</td>";
		echo "<td align=right>&nbsp;".$v_data[9]."</td>";
		echo "<td align=right>&nbsp;".$v_data[4]."</td>";
		echo "<td align=center>&nbsp;".$v_data[5]."</td>";
		echo "<td align=right>&nbsp;".$v_data[6]."</td>";
		echo "<td align=right>&nbsp;".$v_data[7]."</td>";
		echo "<td align=right>&nbsp;".$v_data[8]."</td>";
		echo "</tr>";

		$linea = $linea. "<tr>";		
		$linea = $linea. "<td>".str_pad( trim($v_data[0]) ,$col[0], " ", STR_PAD_BOTH )."</td>";
		$linea = $linea. "<td>".str_pad( trim($v_data[1]) ,$col[1], " ", STR_PAD_BOTH )."</td>";
		$linea = $linea. "<td>".str_pad( trim($v_data[2]) ,$col[2] )."</td>";
		$linea = $linea. "<td>".str_pad( trim($v_data[3]) ,$col[3], " ", STR_PAD_LEFT )."</td>";
		$linea = $linea. "<td>".str_pad( number_format( $v_data[4], 2, '.', ''), $col[4], " ", STR_PAD_LEFT )."</td>";
		$linea = $linea. "<td>".str_pad( trim($v_data[5]) ,$col[5], " ", STR_PAD_BOTH )."</td>";
		$linea = $linea. "<td>".str_pad( trim($v_data[6]) ,$col[6], " ", STR_PAD_LEFT )."</td>";
		$linea = $linea. "<td>".str_pad( trim($v_data[7]) ,$col[7], " ", STR_PAD_LEFT )."</td>";
		$linea = $linea. "<td>".str_pad( number_format( $v_data[8], 2, '.', ''), $col[8], " ", STR_PAD_LEFT )."</td>";

		$totsol1 = $totsol1 + $v_data[4];
		$totsol2 = $totsol2 + $v_data[8];
		
		echo "</tr>";
		$linea = $linea. "</tr>";
		$irow++;
	}
		
	echo "<tr  bgcolor='#D9FFFF' >";
	echo "<td>&nbsp;</td>";
	echo "<td>&nbsp;</td>";
	echo "<td>&nbsp;</td>";
	echo "<td>&nbsp;</td>";
	echo "<td align='right' style='font-weight:bold'>&nbsp;".number_format($totsol1, 2, '.', '')."</td>";
	echo "<td>&nbsp;</td>";
	echo "<td>&nbsp;</td>";
	echo "<td>&nbsp;</td>";
	echo "<td align='right' style='font-weight:bold'>&nbsp;".number_format($totsol2, 2, '.', '')."</td>";
	echo "</tr>";

	$linea = $linea. "<tr>";
	$linea = $linea. "<td>".str_pad( " " ,$col[0] )."</td>";
	$linea = $linea. "<td>".str_pad( " " ,$col[1] )."</td>";
	$linea = $linea. "<td>".str_pad( " ", $col[2] )."</td>";
	$linea = $linea. "<td>".str_pad( " " ,$col[3] )."</td>";
	$linea = $linea. "<td style='font-weight:bold'>".str_pad( trim(number_format($totsol1, 2, '.', '') ), $col[4], " ", STR_PAD_LEFT ) ."</td>";
	$linea = $linea. "<td>".str_pad( " " ,$col[5] )."</td>";
	$linea = $linea. "<td>".str_pad( " " ,$col[6] )."</td>";
	$linea = $linea. "<td>".str_pad( " " ,$col[7] )."</td>";
	$linea = $linea. "<td style='font-weight:bold'>".str_pad( trim(number_format($totsol2, 2, '.', '') ), $col[8], " ", STR_PAD_LEFT ) ."</td>";
	$linea = $linea. "</tr>";

	echo "</table>";
	$linea = $linea. "</table>";

imprimir2( $linea, $col, $nom, $v_archivo, "Tarjetas Credito" );
?>
</div>
</body>
</html>
<?php
pg_close($coneccion);
