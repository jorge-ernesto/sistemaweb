<?php
include("../menu_princ.php");
include("../functions.php");
include("/sistemaweb/utils/funcion-texto.php");
include("../funcjch.php");
require("../clases/funciones.php");

$funcion = new class_funciones;
$clase_error = new OpensoftError;
$clase_error->_error();
$conector_id=$funcion->conectar("","","","","");

if ( is_null($almacen) or trim($almacen) == "")	{
	$almacen = "001";
}

$v_xsqlalma = pg_exec("SELECT trim(ch_almacen) as cod, ch_nombre_almacen FROM inv_ta_almacenes WHERE ch_clase_almacen='1' ORDER BY cod");

if (is_null($v_fecha_desde) or is_null($v_fecha_hasta)) {
	$v_fecha_desde = date("d/m/Y");
	$v_fecha_hasta = date("d/m/Y");
	$v_turno_desde = 0;
	$v_turno_hasta = 0;
}
$v_ilimit = 0;

if ($boton == 'Imprimir') {
	// para generar el reporte por trabajador en vez de partir el reporte completo lo genero todo y luego imprimo por cada trabajador el resultado
	// Limpia la tabla del reporte -> limpia_tabla();
	// Aqui carga los contometros del combex desde la fecha/turno inicio en una tabla temporal
	// caso avance ->  carga_contometros("A");
	// caso dia y turno especifico -> carga_contometros("2004-04-30", "1");

	$v_xsqlcont = pg_exec($conector_id,"truncate tempo");

	if ($v_tipo == "AVANCE") {
		$v_sql = "SELECT trim(pump), trim(codigo), precio, cantidad, importe FROM pos_transtmp WHERE tipo='C'";
	} else {
		$v_anomes 	= substr( $v_fecha_desde, 6, 4).substr($v_fecha_desde, 3, 2);
                $v_sql 		= pg_exec("SELECT es FROM pos_cfg WHERE tipo='M'");
		$codigo_market 	= pg_result($v_sql,0,0) ;
		$v_sql 		= "	SELECT 
						trim(caja), 
						sum(importe)
					FROM 
						pos_trans".$v_anomes."
					WHERE 
						tipo='M' 
						AND es='".$codigo_market."'
						AND to_char(dia,'yyyy-mm-dd')||to_char(to_number(trim(turno),'99'),'99')
						BETWEEN '".$funcion->date_format($v_fecha_desde,'YYYY-MM-DD')."'||to_char(".$v_turno_desde.",'99')
						AND '".$funcion->date_format($v_fecha_hasta,'YYYY-MM-DD')."'||to_char(".$v_turno_hasta.",'99')
					GROUP BY
						caja ";
	}

	$v_sql_pos_trans = pg_exec($v_sql);
	$v_lim = pg_numrows($v_sql_pos_trans);
	$v_row = 0;

	$lado    = pg_result($v_sql_pos_trans,$v_row,0);
	$impotik = pg_result($v_sql_pos_trans,$v_row,1);

	$tmpsql = "INSERT INTO tempo (tmplado, tmpimpotik ) (".$v_sql.") " ;
	$v_sql2 = pg_exec($tmpsql);

	$sql2 = "	SELECT
				ch_codigo_trabajador, 
				ch_lado 
			FROM
				pos_historia_ladosxtrabajador
			WHERE
				ch_sucursal='".$almacen."'
				AND ch_tipo='M'
				AND to_char(dt_dia,'yyyy-mm-dd')||to_char(ch_posturno,'99')
				BETWEEN '".$funcion->date_format($v_fecha_desde,'YYYY-MM-DD')."'||to_char(".$v_turno_desde.",'99')
				AND '".$funcion->date_format($v_fecha_hasta,'YYYY-MM-DD')."'||to_char(".$v_turno_hasta.",'99') ";
	
	$x_sql2 = pg_exec($sql2);
	$v_lim  = pg_numrows($x_sql2);
	$v_row  = 0;

	while ($v_row < $v_lim) {
		$v_trabajador = pg_result($x_sql2,$v_row,0);
		$v_lado       = pg_result($x_sql2,$v_row,1);
		$v_sql3	      = pg_exec("UPDATE tempo SET tmp_codigo_trabajador = '".$v_trabajador."' WHERE tmplado='".$v_lado."' ");
		$v_row++;
	}

	$v_sqlprn  = "SELECT trim(tmplado), tmpimpotik, tmp_codigo_trabajador FROM tempo ORDER BY tmp_codigo_trabajador, tmplado ";
	$v_xsqlprn = pg_exec($v_sqlprn);
	$v_ilimit  = pg_numrows($v_xsqlprn);

	$v_sqlx    = "SELECT par_valor FROM int_parametros WHERE trim(par_nombre)='print_netbios' ";
	$v_xsqlx   = pg_exec( $v_sqlx);
	$v_server  = pg_result($v_xsqlx,0,0);

	$v_sqlx    = "SELECT par_valor FROM int_parametros WHERE trim(par_nombre)='print_name' ";
	$v_xsqlx   = pg_exec($v_sqlx);
	$v_printer = pg_result($v_xsqlx,0,0);

	$v_sqlx    = "SELECT par_valor FROM int_parametros where trim(par_nombre)='print_server' ";
	$v_xsqlx   = pg_exec($v_sqlx);
	$v_ipprint = pg_result($v_xsqlx,0,0);

	$v_archivo = "/tmp/imprimir/vta_select_trab.txt";	
}
?>

<html><link rel="stylesheet" href="<?php echo $v_path_url; ?>sistemaweb.css" type="text/css">
<head>
<title>sistemaweb</title>
<script language="JavaScript" src="js/miguel.js"></script>
<script language="JavaScript" src="/sistemaweb/js/calendario.js"></script>
<script language="JavaScript" src="/sistemaweb/js/overlib_mini.js"></script>
<script language="JavaScript" src="/sistemaweb/clases/reloj.js"></script>
<script language="JavaScript" src="/sistemaweb/compras/validacion.js"></script>
<script language="JavaScript" src="/sistemaweb/compras/valfecha.js"></script>
<script>

function activa() {
	document.f_repo.v_fecha_desde.select()
	document.f_repo.v_fecha_desde.focus()
}
</script>
</head>
<body>
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<form name="f_repo" method="post">
<div align="center"><font face="Arial, Helvetica, sans-serif">
REPORTE VENTA TIENDA  Desde: <?php echo $v_fecha_desde; ?> Turno: <?php echo $v_turno_desde; ?>
 Hasta: <?php echo $v_fecha_hasta; ?> Turno: <?php echo $v_turno_hasta; ?> <BR>

<?php
$v_sql  = "SELECT trim(ch_almacen), ch_nombre_almacen FROM inv_ta_almacenes WHERE ch_almacen LIKE '%".trim($almacen)."%' AND ch_clase_almacen='1' ";
$v_xsql = pg_query($conector_id,$v_sql);
if(pg_numrows($v_xsql) > 0) {	
	$v_descalma = pg_result($v_xsql,0,1);	
}
?>

ALMACEN ACTUAL <?php echo $almacen;?> 	<?php echo $v_descalma; ?>
<input type="text" name="reloj" size="10" style="background-color : Black; color : White; font-family : Verdana, Arial, Helvetica; font-size : 8pt; text-align : center;" onfocus="window.document.f_repo.reloj.blur()" >
</div>
<hr noshade>

<?php
if (is_null($v_almacen)) {
	$v_almacen = $almacen;
}

if (is_null($v_fecha_desde) or is_null($v_fecha_hasta)) {
	$v_fecha_desde = date("d/m/Y");
	$v_fecha_hasta = date("d/m/Y");
	$v_turno_desde = 0;
	$v_turno_hasta = 0;
}
?>

<table border="1">
	<tr>
		<th colspan="7">Reporte Por : RANGO DE FECHAS </th>
	</tr>
	<tr>
		<th>DESDE :</th>
		<th><p>
		<input type="text" name="v_fecha_desde" size="16" maxlength="10" value='<?php echo $v_fecha_desde ; ?>'  tabindex="1"  onKeyUp="javascript:validarFecha(this)" onblur="javascript:validarFecha(this)"  >
		<a href="javascript:show_calendar('f_repo.v_fecha_desde');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;" >
	    	<img src="/sistemaweb/images/showcalendar.gif" border=0></a></p>
		</th>
		<th>TURNO :</th>
		<th><input type="text" name="v_turno_desde" size="4" maxlength="2" value='<?php echo $v_turno_desde ; ?>'  tabindex="2"  >
		</th>
	</tr>
	<tr>
		<th>HASTA:</th>
		<th><p>
		<input type="text" name="v_fecha_hasta" size="16" maxlength="10" value='<?php echo $v_fecha_hasta ; ?>'  tabindex="3" onKeyUp="javascript:validarFecha(this)" onblur="javascript:validarFecha(this)">
		<a href="javascript:show_calendar('f_repo.v_fecha_hasta');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;">
	    	<img src="/sistemaweb/images/showcalendar.gif" border=0></a></p>
		</th>
		<th>TURNO :</th>
		<th><input type="text" name="v_turno_hasta" size="4" maxlength="2" value='<?php echo $v_turno_hasta ; ?>'  tabindex="4"  >
		</th>
	</tr>
	<tr>
		<th colspan="7"><input type="submit" name="boton" tabindex=5 value="Imprimir">
		</th>
	</tr>

        <tr>
		<td colspan=2>
		<a href="#" onClick="javascript:window.open('/sistemaweb/clases/imprime_samba.php?v_server=<?php echo $v_server; ?>&v_printer=<?php echo $v_printer; ?>&v_ipprint=<?php echo $v_ipprint; ?>&v_archivo=<?php echo $v_archivo; ?>','miwin','width=800,height=450,scrollbars=yes,menubar=yes,left=0,top=0');"> Impresion Texto </a>
		</td>

		<td colspan=2>
		<a href="#" onClick="javascript:window.open('vta_manguera_exportar.php?v_fecha_desde=<?php echo $v_fecha_desde;?>&v_fecha_hasta=<?php echo $v_fecha_hasta;?>&v_turno_desde=<?php echo $v_turno_desde;?>&v_turno_hasta=<?php echo $v_turno_hasta;?>','miwin','width=800,height=450,scrollbars=yes,menubar=yes,left=0,top=0');">Exportar </a>
		</td>
        </tr>
</table>
<br>

<?php
$v_clave = " ";

$timpotik   = 0;
$sttimpotik = 0;
$stimpotik  = 0;

$col[0] = 4;
$col[1] = 12;

$nom[0] = "CAJA";	
$nom[2] = "Import Tickt";

$linea  = "";

if($v_ilimit > 0) {
	$v_irow = 0;
	while($v_irow < $v_ilimit) {
		$a2 = pg_result($v_xsqlprn,$v_irow,2);
		$v_clavetrab = $a2;
		echo $a2;
		$nombre = pg_result( pg_exec("SELECT ch_codigo_trabajador||' - '||ch_apellido_paterno||ch_apellido_materno||ch_nombre1||ch_nombre2 FROM pla_ta_trabajadores WHERE ch_codigo_trabajador= '".$a2."' "  ),0,0);
		echo "<table width='990' border='2' cellspacing=0 height='81'>";
		echo "<tr>";
		echo "<th colspan=18 align='center'><font size='3.5'> <BR>
		REPORTE VENTA TIENDA Desde: ".$v_fecha_desde." Turno: ".$v_turno_desde."
		Hasta: ".$v_fecha_hasta." Turno: ".$v_turno_hasta." <BR>
		Trabajador: ".$nombre." <P></P>	</th> "  ;
		echo "</tr>";
		echo "<tr>";
		echo "	<th width='85'>Caja</td>";
		echo "	<th width='100' align='center'>Importe Tickets</td>";
		echo "</tr>";

		$linea = $linea."<table>";
		$linea = $linea."<tr>";
		$linea = $linea."<td>REPORTE VENTA TIENDA Desde: ".$v_fecha_desde." Turno: ".$v_turno_desde." Hasta: ".$v_fecha_hasta." Turno: ".$v_turno_hasta." </td>";
		$linea = $linea."</tr>";
		$linea = $linea."<tr>";
		$linea = $linea."<td>Trabajador: ".$nombre."  </td>";
		$linea = $linea."</tr>";
		$linea = $linea. "<tr>";
		$linea = $linea. "<td>Lado</td>";
		$linea = $linea. "<td>Import Tckts</td>";
		$linea = $linea. "</tr>";

		while($v_irow < $v_ilimit and $v_clavetrab == $a2 ) {
			$a2 = pg_result($v_xsqlprn,$v_irow,2);
			$a0 = pg_result($v_xsqlprn,$v_irow,0);
			$a1 = pg_result($v_xsqlprn,$v_irow,1);
			$v_clave = $a0;

			while($v_irow < $v_ilimit and $v_clavetrab == $a2 and $v_clave == $a0 ) {
				$a0 = pg_result($v_xsqlprn,$v_irow,0);
				$a1 = pg_result($v_xsqlprn,$v_irow,1);
				$a2 = pg_result($v_xsqlprn,$v_irow,2);

				echo "<tr>";
				echo "<td align='left' >&nbsp;".$a0." </td>";
				echo "<td align='right' >&nbsp;".number_format($a1, 2, '.', '')." </td>";
				echo "</tr>";

				$linea = $linea."<tr>";
				$linea = $linea."<td>".str_pad( $a0 ,$col[0] )."</td>";
				$linea = $linea."<td>".str_pad( number_format($a1, 2, '.', ''), $col[1], " ", STR_PAD_LEFT )."</td>";
				$linea = $linea."</tr>";

				$stimpotik  = $stimpotik + $a1;
				$sttimpotik = $sttimpotik + $a1;
				$timpotik   = $timpotik + $a1;

				$v_irow++;
				if ($v_irow < $v_ilimit  ) {
					$a2 = pg_result($v_xsqlprn,$v_irow,2);
					$a0 = pg_result($v_xsqlprn,$v_irow,0);
					$a1 = pg_result($v_xsqlprn,$v_irow,1);
				}
				if ($v_irow < $v_ilimit and $v_clavetrab == $a2 ) {
					$a2 = pg_result($v_xsqlprn,$v_irow,2);
					$a0 = pg_result($v_xsqlprn,$v_irow,0);
					$a1 = pg_result($v_xsqlprn,$v_irow,1);
				}
			}

			echo "<tr>";
			echo "<th align='center' >&nbsp;TOTAL CAJA</td>";
			echo "<th align='right' >&nbsp;".number_format($stimpotik, 2, '.', '')." </td>";
			echo "</tr>";

			$linea = $linea."<tr>";
			$linea = $linea."<td>".str_pad( "TOTAL CAJA" ,$col[0] )."</td>";
			$linea = $linea."<td>".str_pad( trim(number_format($stimpotik, 2, '.', '') ), $col[1], " ", STR_PAD_LEFT )."</td>";
			$linea = $linea."</tr>";
			$stimpotik = 0;

			if ($v_irow < $v_ilimit ) {
				$a2 = pg_result($v_xsqlprn,$v_irow,2);
				$a0 = pg_result($v_xsqlprn,$v_irow,0);
				$a1 = pg_result($v_xsqlprn,$v_irow,1);
			}
		}

		echo "<tr>";
		echo "<th align='left' >&nbsp;TOTAL TRAB</td>";
		echo "<th align='right' >&nbsp;".number_format($sttimpotik, 2, '.', '')." </td>";
		echo "</tr>";

		$linea = $linea."<tr>";
		$linea = $linea."<td>".str_pad( "TOTAL TRAB" ,$col[0] )."</td>";
		$linea = $linea."<td>".str_pad( trim(number_format($sttimpotik, 2, '.', '') ), $col[1], " ", STR_PAD_LEFT )."</td>";
		$linea = $linea."</tr>";

		$v_sqlprndev = "SELECT 
					trans, 
					substr(to_char(fecha,'YYYY-MM-DD'),1,19) as fechahora, 
					codigo, 
					substr(int_articulos.art_descripcion,1,20), 
					cantidad, 
					importe 
				FROM
					pos_trans".$v_anomes."
					LEFT JOIN int_articulos ON trim(pos_trans".$v_anomes.".codigo)=trim(int_articulos.art_codigo)
				WHERE
					es='".$almacen."'
					AND to_char(dia,'yyyy-mm-dd')||to_char(to_number(trim(turno),'99'),'99')
					BETWEEN '".$funcion->date_format($v_fecha_desde,'YYYY-MM-DD')."'||to_char(".$v_turno_desde.",'99')
					AND '".$funcion->date_format($v_fecha_hasta,'YYYY-MM-DD')."'||to_char(".$v_turno_hasta.",'99')
					AND tm='D'
					AND tipo='M'
					AND trim(caja) in (SELECT trim(tmplado) FROM tempo WHERE tmp_codigo_trabajador='".$v_clavetrab."' )
				ORDER BY
					fechahora";

		$v_xsqlprndev = pg_exec($v_sqlprndev);
		$v_ilimitdev  = pg_numrows($v_xsqlprndev);

		$col[0] = 5;
		$col[1] = 20;
		$col[2] = 13;
		$col[3] = 20;
		$col[4] = 10;
		$col[5] = 10;
		$col[6] = 10;

		echo "<tr>";
		echo "<th width='85'  align='center' colspan=8 >Devoluciones del Trabajador</td>";
		echo "</tr>";
		$linea = $linea."<tr>";
		$linea = $linea."<td>".str_pad( "Devoluciones del Trabajador "  ,$col[0]+$col[1]+$col[2]+$col[3]+$col[4]+$col[5] , " ", STR_PAD_BOTH )."</td>";
		$linea = $linea."</tr>";

		echo "<tr>";
		echo "	<th width='85'  align='center'>Trans</td>";
		echo "	<th width='85'  align='center'>Fecha Hora</td>";
		echo "	<th width='85'  align='center'>Codigo</td>";
		echo "	<th width='106' align='center'colspan=3>Nombre</td>";
		echo "	<th width='106' align='center'>&nbsp;Cantidad</td>";
		echo "	<th width='85'  align='center'>&nbsp;Importe</td>";
		echo "	<th width='85'  align='center'>&nbsp;Total</td>";
		echo "</tr>";
		$linea = $linea. "<tr>";
		$linea = $linea. "<td>".str_pad( "Trans"        ,$col[0] )."</td>";
		$linea = $linea. "<td>".str_pad( "Fecha Hora"   ,$col[1] , " ", STR_PAD_BOTH )."</td>";
		$linea = $linea. "<td>".str_pad( "Codigo"       ,$col[2] )."</td>";
		$linea = $linea. "<td>".str_pad( "Nombre"       ,$col[3] )."</td>";
		$linea = $linea. "<td>".str_pad( "Cantidad"     ,$col[4] , " ", STR_PAD_LEFT )."</td>";
		$linea = $linea. "<td>".str_pad( "Importe"      ,$col[5] , " ", STR_PAD_LEFT )."</td>";
		$linea = $linea. "<td>".str_pad( "Total"        ,$col[6] , " ", STR_PAD_LEFT )."</td>";
		$linea = $linea. "</tr>";
		$tot_trans = 0;
		$tot_tot = 0;

		$v_irowdev = 0;
		while ($v_irowdev < $v_ilimitdev) {
			$dev0  = pg_result( $v_xsqlprndev, $v_irowdev, 0 );
			$clave = $dev0;
			$dev1  = pg_result( $v_xsqlprndev, $v_irowdev, 1 );
			$dev2  = pg_result( $v_xsqlprndev, $v_irowdev, 2 );
			$dev3  = pg_result( $v_xsqlprndev, $v_irowdev, 3 );
			$dev4  = pg_result( $v_xsqlprndev, $v_irowdev, 4 );
			$dev5  = pg_result( $v_xsqlprndev, $v_irowdev, 5 );
			echo "<tr>";
			echo "<td align='left' >&nbsp;".$dev0." </td>";
			echo "<td align='left' >&nbsp;".$dev1." </td>";
			echo "<td align='left' >&nbsp;".$dev2." </td>";
			echo "<td align='left' colspan=3>&nbsp;".$dev3." </td>";
			echo "<td align='right'  >&nbsp;".number_format($dev4, 2, '.', '')." </td>";
			echo "<td align='right'  >&nbsp;".number_format($dev5, 2, '.', '')." </td>";
			$linea = $linea. "<tr>";
			$linea = $linea. "<td>".str_pad( $dev0 ,$col[0] )."</td>";
			$linea = $linea. "<td>".str_pad( $dev1 ,$col[1] )."</td>";
			$linea = $linea. "<td>".str_pad( $dev2 ,$col[2] )."</td>";
			$linea = $linea. "<td>".str_pad( $dev3 ,$col[3] )."</td>";
			$linea = $linea. "<td>".str_pad( number_format($dev4, 2, '.', ''), $col[4], " ", STR_PAD_LEFT )."</td>";
			$linea = $linea. "<td>".str_pad( number_format($dev5, 2, '.', ''), $col[5], " ", STR_PAD_LEFT )."</td>";
			$tot_trans = $tot_trans + $dev5;
			$tot_tot = $tot_tot + $dev5;
			$v_irowdev++;

			if ($v_irowdev < $v_ilimitdev)	{
				$dev0 = pg_result( $v_xsqlprndev, $v_irowdev, 0 );
				if ($clave != $dev0) {
					echo "<td align='right'  >&nbsp;".number_format($tot_trans, 2, '.', '')." </td>";
					$linea = $linea. "<td>".str_pad( number_format($tot_trans, 2, '.', ''), $col[6], " ", STR_PAD_LEFT )."</td>";
					$tot_trans=0;
				} else {
					echo "<td align='left' >&nbsp;</td>";
					$linea = $linea. "<td> </td>";
				}
			} else {
				echo "<td align='right'  >&nbsp;".number_format($tot_trans, 2, '.', '')." </td>";
				$linea = $linea. "<td>".str_pad( number_format($tot_trans, 2, '.', ''), $col[6], " ", STR_PAD_LEFT )."</td>";
				$tot_trans = 0;
			}
			echo "</tr>";
			$linea = $linea. "</tr>";
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
		$linea = $linea. "<tr>";
		$linea = $linea. "<td>".str_pad( " " ,$col[0] )."</td>";
		$linea = $linea. "<td>".str_pad( " " ,$col[1] )."</td>";
		$linea = $linea. "<td>".str_pad( " " ,$col[2] )."</td>";
		$linea = $linea. "<td>".str_pad( " " ,$col[3] )."</td>";
		$linea = $linea. "<td>".str_pad( " " ,$col[4] )."</td>";
		$linea = $linea. "<td>".str_pad( "Total"        ,$col[5] , " ", STR_PAD_LEFT )."</td>";
		$linea = $linea. "<td>".str_pad( number_format($tot_tot, 2, '.', ''), $col[6], " ", STR_PAD_LEFT )."</td>";
		$linea = $linea. "</tr>";

		$sql2 = "SELECT ch_codigo_trabajador, 
				ch_lado 
			FROM
				pos_historia_ladosxtrabajador
			WHERE
				ch_sucursal='".$almacen."'
				AND ch_tipo='M'
				AND to_char(dt_dia,'yyyy-mm-dd')||to_char(ch_posturno,'99')
				BETWEEN '".$funcion->date_format($v_fecha_desde,'YYYY-MM-DD')."'||to_char(".$v_turno_desde.",'99')
				AND '".$funcion->date_format($v_fecha_hasta,'YYYY-MM-DD')."'||to_char(".$v_turno_hasta.",'99') ";

		$v_sqlprnval = "SELECT 
					distinct trans, 
					proveedor, 
					int_clientes.cli_razsocial, 
					soles_km 
				FROM
					pos_trans".$v_anomes."
					LEFT JOIN int_clientes ON trim(pos_trans".$v_anomes.".proveedor)=trim(int_clientes.cli_codigo)
				WHERE
					es='".$almacen."'
					AND to_char(dia,'yyyy-mm-dd')||to_char(to_number(trim(turno),'99'),'99')
					BETWEEN '".$funcion->date_format($v_fecha_desde,'YYYY-MM-DD')."'||to_char(".$v_turno_desde.",'99')
					AND '".$funcion->date_format($v_fecha_hasta,'YYYY-MM-DD')."'||to_char(".$v_turno_hasta.",'99')
					AND td='N'
					AND tipo='M'
					AND trim(caja) in (SELECT trim(tmplado) FROM tempo WHERE tmp_codigo_trabajador='".$v_clavetrab."' )
				";

		$v_sqlprncre = "SELECT 
					distinct trans, 
					text1, 
					at||'-'||int_tabla_general.tab_descripcion, 
					soles_km 
				FROM
					pos_trans".$v_anomes."
					LEFT JOIN int_tabla_general ON trim(pos_trans".$v_anomes.".at)=
					substring(int_tabla_general.tab_elemento,6,6) and tab_tabla ='95'
				WHERE
					es='".$almacen."'
					AND to_char(dia,'yyyy-mm-dd')||to_char(to_number(trim(turno),'99'),'99')
					BETWEEN '".$funcion->date_format($v_fecha_desde,'YYYY-MM-DD')."'||to_char(".$v_turno_desde.",'99')
					AND '".$funcion->date_format($v_fecha_hasta,'YYYY-MM-DD')."'||to_char(".$v_turno_hasta.",'99')
					AND fpago='2'
					AND td!='N'
					AND tipo='M'
					AND trim(caja) in (SELECT trim(tmplado) FROM tempo WHERE tmp_codigo_trabajador='".$v_clavetrab."' )
				";

		$v_sqlprnafe = "SELECT
					trans, 
					importe 
				FROM
					pos_trans".$v_anomes."
				WHERE
					es='".$almacen."'
					AND to_char(dia,'yyyy-mm-dd')||to_char(to_number(trim(turno),'99'),'99')
					BETWEEN '".$funcion->date_format($v_fecha_desde,'YYYY-MM-DD')."'||to_char(".$v_turno_desde.",'99')
					AND '".$funcion->date_format($v_fecha_hasta,'YYYY-MM-DD')."'||to_char(".$v_turno_hasta.",'99')
					AND fpago='1'
					AND td='A'
					AND tipo='M'
					AND trim(caja) in (select trim(tmplado) from tempo where tmp_codigo_trabajador='".$v_clavetrab."' )
				";

		$v_sqlprnefe = "SELECT 
					sum(importe) 
				FROM
					pos_trans".$v_anomes."
					where es='".$almacen."'
					and to_char(dia,'yyyy-mm-dd')||to_char(to_number(trim(turno),'99'),'99')
					between '".$funcion->date_format($v_fecha_desde,'YYYY-MM-DD')."'||to_char(".$v_turno_desde.",'99')
					and '".$funcion->date_format($v_fecha_hasta,'YYYY-MM-DD')."'||to_char(".$v_turno_hasta.",'99')
					and fpago='1'
					and td!='A'
					and td!='N'
					and tipo='M'
					and trim(caja) in (select trim(tmplado) from tempo where tmp_codigo_trabajador='".$v_clavetrab."' )
					";

			$v_sqlprndep="select trim(ch_numero_correl), trim(ch_numero_documento), trim(ch_moneda), nu_tipo_cambio, nu_importe,
					case
					when trim(ch_moneda)=trim('01') then nu_importe
					when trim(ch_moneda)=trim('02') then nu_importe * nu_tipo_cambio
					end
					from pos_depositos_diarios
					where
					(ch_valida='S' or ch_valida='s' )
					and to_char(dt_dia,'yyyy-mm-dd')||to_char(ch_posturno,'99')
					between '".$funcion->date_format($v_fecha_desde,'YYYY-MM-DD')."'||to_char(".$v_turno_desde.",'99')
					and '".$funcion->date_format($v_fecha_hasta,'YYYY-MM-DD')."'||to_char(".$v_turno_hasta.",'99')
					and ch_codigo_trabajador='".$v_clavetrab."' ";
					// ch_almacen='".$almacen."' and

			// echo $v_sqlprnval;
			$v_xsqlprnval=pg_exec($v_sqlprnval);
			$v_ilimitval=pg_numrows($v_xsqlprnval);

			// echo $v_sqlprncre;
			$v_xsqlprncre=pg_exec($v_sqlprncre);
			$v_ilimitcre=pg_numrows($v_xsqlprncre);

			// echo $v_sqlprnafe;
			$v_xsqlprnafe=pg_exec($v_sqlprnafe);
			$v_ilimitafe=pg_numrows($v_xsqlprnafe);

			// echo $v_sqlprnefe;
			$v_xsqlprnefe=pg_exec($v_sqlprnefe);
			$v_ilimitefe=pg_numrows($v_xsqlprnefe);

			// echo $v_sqlprndep;
			$v_xsqlprndep=pg_exec($v_sqlprndep);
			$v_ilimitdep=pg_numrows($v_xsqlprndep);

			$col[0]=80;
			echo "<tr>";
			echo "	<th align='center' colspan=14>&nbsp;</td>";
			echo "</tr>";
			$linea=$linea. "<tr>";
			$linea=$linea. "</tr>";

			$col[0]=80;
			$col[1]=68;
			echo "<tr>";
			echo "	<th width='85'  align='center' colspan=7 >Vales Credito / Tarjetas Credito</td>";
			echo "	<th width='85'  align='center'>&nbsp;</td>";
			echo "	<th width='85'  align='center' colspan=7>Depositos</td>";
			echo "</tr>";
			$linea=$linea. "<tr>";
			$linea=$linea. "<td>".str_pad( "Vales Credito / Tarjetas Credito"  ,$col[0] , " ", STR_PAD_BOTH )."</td>";
			$linea=$linea. "<td>".str_pad( "Depositos"                                       ,$col[1] , " ", STR_PAD_BOTH )."</td>";
			$linea=$linea. "</tr>";

			$col[0]=6;
			$col[1]=16;
			$col[2]=40;
			$col[3]=12;
			$col[4]=10;
			$col[5]=7;
			$col[6]=10;
			$col[7]=7;
			$col[8]=10;
			$col[9]=12;
			$col[10]=12;

			echo "<tr>";
			echo "	<th width='85'  align='center'>Trans</td>";
			echo "	<th width='85'  align='center'>Cliente</td>";
			echo "	<th width='85'  align='center' colspan=4 >Nombre</td>";
			echo "	<th width='106' align='center'>Importe</td>";
			echo "	<th width='106' align='center'>&nbsp;</td>";
			echo "	<th width='85'  align='center'>DOC No.</td>";
			echo "	<th width='85'  align='center'>Ducto Observ</td>";
			echo "	<th width='85'  align='center'>Moneda</td>";
			echo "	<th width='85'  align='center'>T Cambio</td>";
			echo "	<th width='106' align='center'>Importe</td>";
			echo "	<th width='106' align='center'>Importe Soles</td>";
			echo "</tr>";
			$linea=$linea. "<tr>";
			$linea=$linea. "<td>".str_pad( "Trans"        ,$col[0] )."</td>";
			$linea=$linea. "<td>".str_pad( "Cliente"      ,$col[1] )."</td>";
			$linea=$linea. "<td>".str_pad( "Nombre"       ,$col[2] )."</td>";
			$linea=$linea. "<td>".str_pad( "Importe"      ,$col[3] , " ", STR_PAD_LEFT )."</td>";
			$linea=$linea. "<td>".str_pad( " "            ,$col[4] )."</td>";
			$linea=$linea. "<td>".str_pad( "DOC No."      ,$col[5] )."</td>";
			$linea=$linea. "<td>".str_pad( "Ducto Obs"    ,$col[6] )."</td>";
			$linea=$linea. "<td>".str_pad( "Moneda"       ,$col[7] )."</td>";
			$linea=$linea. "<td>".str_pad( "T.Cambio"     ,$col[8] , " ", STR_PAD_LEFT )."</td>";
			$linea=$linea. "<td>".str_pad( "Importe"      ,$col[9] , " ", STR_PAD_LEFT )."</td>";
			$linea=$linea. "<td>".str_pad( "Importe Soles",$col[10] , " ", STR_PAD_LEFT )."</td>";
			$linea=$linea. "</tr>";

			$v_irowval=0;
			$v_irowcre=0;
			$v_irowafe=0;
			$v_irowefe=0;

			$v_irowdep=0;
			$v_irowtot=0;
			$v_irowres=0;

			$v_irowdocus=0;

			$v_columna=0;

			$flag_cre='0';
			$flag_dep='0';
			$v_ilimittot=1;
			$v_ilimitres=7;

			// suma 1 fila mas para el total
			$v_columna1 = $v_ilimitval + $v_ilimitcre + $v_ilimitafe + $v_ilimittot ;
			$v_columna2 = $v_ilimitdep + $v_ilimittot + $v_ilimitres;

			$totala=0;
			$totalb=0;

			$totalvale=0;
			$totaltcre=0;
			$totaltafe=0;
			$totalefec=0;


			if ( $v_columna1 > $v_columna2 )
			{
				$v_columna=$v_columna1;
			}
			else
			{
				$v_columna=$v_columna2;
			}



			while ( $v_irowdocus < $v_columna )
				{
				if ( $v_irowval < $v_ilimitval )
					{
					$a3=pg_result( $v_xsqlprnval, $v_irowval, 3 );
					$totalvale=$totalvale+$a3;
					$totala=$totala+$a3;
					$v_irowval++;
					}
				else
					{
					if ( $v_irowcre < $v_ilimitcre )
						{
						$a3=pg_result( $v_xsqlprncre, $v_irowcre, 3 );
						$totaltcre=$totaltcre+$a3;
						$totala=$totala+$a3;
						$v_irowcre++;
						}
					else
						{
						if ( $v_irowafe < $v_ilimitafe )
							{
							$a1=pg_result( $v_xsqlprnafe, $v_irowafe, 1 );
							$totaltafe=$totaltafe+$a1;
							$totala=$totala+$a1;
							$v_irowafe++;
							}

						}
					}

				if ( $v_irowdep < $v_ilimitdep )
					{
					$b5=pg_result( $v_xsqlprndep, $v_irowdep, 5 );
					$totalb=$totalb+$b5;
					$v_irowdep++;
					}
				else
					{
					if ( $v_irowtot < $v_ilimittot )
						{
						$v_irowtot++;
						}
					else
						{
						if ( $v_irowres < $v_ilimitres )
							{
							$v_irowres++;
							}
						}
					}


				$v_irowdocus++;
				}


			$v_irowval=0;
			$v_irowcre=0;
			$v_irowafe=0;
			$v_irowefe=0;

			$v_irowdep=0;
			$v_irowtot=0;
			$v_irowres=0;

			$v_irowdocus=0;


			while ( $v_irowdocus < $v_columna )
				{
				echo "<tr>";
				$linea=$linea. "<tr>";
				if ( $v_irowval < $v_ilimitval )
					{
					$a0=pg_result( $v_xsqlprnval, $v_irowval, 0 );
					$a1=pg_result( $v_xsqlprnval, $v_irowval, 1 );
					$a2=pg_result( $v_xsqlprnval, $v_irowval, 2 );
					$a3=pg_result( $v_xsqlprnval, $v_irowval, 3 );
					echo "<td align='center' >&nbsp;".$a0." </td>";
					echo "<td align='center' >&nbsp;".$a1." </td>";
					echo "<td align='left'  colspan=4 >&nbsp;".$a2." </td>";
					echo "<td align='right'  >&nbsp;".number_format($a3, 2, '.', '')." </td>";
					$linea=$linea. "<td>".str_pad( $a0 ,$col[0] )."</td>";
					$linea=$linea. "<td>".str_pad( $a1 ,$col[1] )."</td>";
					$linea=$linea. "<td>".str_pad( $a2 ,$col[2] )."</td>";
					$linea=$linea. "<td>".str_pad( number_format($a3, 2, '.', ''), $col[3], " ", STR_PAD_LEFT )."</td>";
					// $totalvale=$totalvale+$a3;
					// $totala=$totala+$a3;
					$v_irowval++;
					}
				else
					{
					if ( $v_irowcre < $v_ilimitcre )
						{
						$a0=pg_result( $v_xsqlprncre, $v_irowcre, 0 );
						$a1=pg_result( $v_xsqlprncre, $v_irowcre, 1 );
						$a2=pg_result( $v_xsqlprncre, $v_irowcre, 2 );
						$a3=pg_result( $v_xsqlprncre, $v_irowcre, 3 );
						echo "<td align='center' >&nbsp;".$a0." </td>";
						echo "<td align='center' >&nbsp;".$a1." </td>";
						echo "<td align='left'  colspan=4 >&nbsp;".$a2." </td>";
						echo "<td align='right'  >&nbsp;".number_format($a3, 2, '.', '')." </td>";
						$linea=$linea. "<td>".str_pad( $a0 ,$col[0] )."</td>";
						$linea=$linea. "<td>".str_pad( $a1 ,$col[1] )."</td>";
						$linea=$linea. "<td>".str_pad( $a2 ,$col[2] )."</td>";
						$linea=$linea. "<td>".str_pad( number_format($a3, 2, '.', ''), $col[3], " ", STR_PAD_LEFT )."</td>";
						// $totaltcre=$totaltcre+$a3;
						// $totala=$totala+$a3;
						$v_irowcre++;
						}
					else
						{
						if ( $v_irowafe < $v_ilimitafe )
							{
							$a0=pg_result( $v_xsqlprnafe, $v_irowafe, 0 );
							$a1=pg_result( $v_xsqlprnafe, $v_irowafe, 1 );
							echo "<td align='center' >&nbsp;".$a0." </td>";
							echo "<td align='center' >&nbsp;</td>";
							echo "<td align='right'  colspan=4 >&nbsp;AFERICION </td>";
							echo "<td align='right'  >&nbsp;".number_format($a1, 2, '.', '')." </td>";
							$linea=$linea. "<td>".str_pad( $a0 ,$col[0] )."</td>";
							$linea=$linea. "<td>".str_pad( " " ,$col[1] )."</td>";
							$linea=$linea. "<td>".str_pad( " " ,$col[2] )."</td>";
							$linea=$linea. "<td>".str_pad( number_format($a1, 2, '.', ''), $col[3], " ", STR_PAD_LEFT )."</td>";
							// $totaltafe=$totaltafe+$a1;
							// $totala=$totala+$a1;
							$v_irowafe++;
							}
						else
							{
							if ($flag_cre=='1')
								{
								echo "<td align='center' >&nbsp;</td>";
								echo "<td align='center' >&nbsp;</td>";
								echo "<td align='left'  colspan=4 >&nbsp;</td>";
								echo "<td align='right'  >&nbsp;</td>";
								$linea=$linea. "<td>".str_pad( " " ,$col[0] )."</td>";
								$linea=$linea. "<td>".str_pad( " " ,$col[1] )."</td>";
								$linea=$linea. "<td>".str_pad( " " ,$col[2] )."</td>";
								$linea=$linea. "<td>".str_pad( " " ,$col[3] )."</td>";
								}
							else
								{
								echo "<td align='center' >&nbsp;</td>";
								echo "<td align='center' >&nbsp;</td>";
								echo "<td align='right'  colspan=4 >&nbsp;TOTAL CREDITO</td>";
								echo "<td align='right'  >&nbsp;".number_format( $totala, 2, '.', '')." </td>";
								$linea=$linea. "<td>".str_pad( " "            ,$col[0] )."</td>";
								$linea=$linea. "<td>".str_pad( " "            ,$col[1] )."</td>";
								$linea=$linea. "<td>".str_pad( "Total Credito"        ,$col[2] , " ", STR_PAD_LEFT )."</td>";
								$linea=$linea. "<td>".str_pad( number_format( $totala, 2, '.', '')        ,$col[3] , " ", STR_PAD_LEFT )."</td>";
								$flag_cre='1';
								}
							}
						}
					}

				echo "<td align='center' >&nbsp;</td>";
				$linea=$linea. "<td>".str_pad( " " ,$col[4] )."</td>";

				if ( $v_irowdep < $v_ilimitdep )
					{
					$b0=pg_result( $v_xsqlprndep, $v_irowdep, 0 );
					$b1=pg_result( $v_xsqlprndep, $v_irowdep, 1 );
					$b2=pg_result( $v_xsqlprndep, $v_irowdep, 2 );
					$b3=pg_result( $v_xsqlprndep, $v_irowdep, 3 );
					$b4=pg_result( $v_xsqlprndep, $v_irowdep, 4 );
					$b5=pg_result( $v_xsqlprndep, $v_irowdep, 5 );
					echo "<td align='center' >&nbsp;".$b0." </td>";
					echo "<td align='center' >&nbsp;".$b1." </td>";
					echo "<td align='center' >&nbsp;".$b2." </td>";
					echo "<td align='right'  >&nbsp;".number_format( $b3, 2, '.', '')." </td>";
					echo "<td align='right'  >&nbsp;".number_format( $b4, 2, '.', '')." </td>";
					echo "<td align='right'  >&nbsp;".number_format( $b5, 2, '.', '')." </td>";
					$linea=$linea. "<td>".str_pad( $b0 ,$col[5] )."</td>";
					$linea=$linea. "<td>".str_pad( $b1 ,$col[6] )."</td>";
					$linea=$linea. "<td>".str_pad( $b2 ,$col[7] )."</td>";
					$linea=$linea. "<td>".str_pad( number_format($b3, 2, '.', ''), $col[8], " ", STR_PAD_LEFT )."</td>";
					$linea=$linea. "<td>".str_pad( number_format($b4, 2, '.', ''), $col[9], " ", STR_PAD_LEFT )."</td>";
					$linea=$linea. "<td>".str_pad( number_format($b5, 2, '.', ''), $col[10], " ", STR_PAD_LEFT )."</td>";
					// $totalb=$totalb+$b5;
					$v_irowdep++;
					}
				else
					{
					if ( $v_irowtot < $v_ilimittot )
						{
						echo "<td align='center' >&nbsp;</td>";
						echo "<td align='center' >&nbsp;</td>";
						echo "<td align='center' >&nbsp;</td>";
						echo "<td align='right'  >&nbsp;TOTAL </td>";
						echo "<td align='center' >&nbsp;</td>";
						echo "<td align='right'  >&nbsp;".number_format( $totalb, 2, '.', '')." </td>";
						$linea=$linea. "<td>".str_pad( " "            ,$col[5] )."</td>";
						$linea=$linea. "<td>".str_pad( " "            ,$col[6] )."</td>";
						$linea=$linea. "<td>".str_pad( " "            ,$col[7] )."</td>";
						$linea=$linea. "<td>".str_pad( "Total"        ,$col[8] , " ", STR_PAD_LEFT )."</td>";
						$linea=$linea. "<td>".str_pad( " "            ,$col[9] )."</td>";
						$linea=$linea. "<td>".str_pad( number_format( $totalb, 2, '.', '')     ,$col[10] , " ", STR_PAD_LEFT )."</td>";
						$v_irowtot++;
						}
					else
						{
						if ( $v_irowres < $v_ilimitres )
							{
							if ($v_irowres==0)
								{
								echo "<td align='center' colspan=6 >&nbsp;RESUMEN</td>";
								$linea=$linea. "<td>".str_pad( "RESUMEN" ,$col[5]+$col[6]+$col[7]+$col[8]+$col[9]+$col[10] , " ", STR_PAD_BOTH )."</td>";
								}
							if ($v_irowres==1)
								{
								echo "<td align='left' colspan=5>&nbsp;VENTAS TIENDA</td>";
								echo "<td align='right' >&nbsp;".number_format( $sttimpotik, 2, '.', '')." </td>";
								$linea=$linea. "<td>".str_pad( "VENTAS TIENDA" , $col[5]+$col[6]+$col[7]+$col[8]+$col[9] )."</td>";
								$linea=$linea. "<td>".str_pad( trim(number_format( $sttimpotik, 2, '.', '') ) ,$col[10], " ", STR_PAD_LEFT )."</td>";
								}
							if ($v_irowres==2)
								{
								echo "<td align='center' >&nbsp;</td>";
								echo "<td align='left' colspan=2>&nbsp;VENTAS VALES</td>";
								echo "<td align='right'  >&nbsp;".number_format( $totalvale, 2, '.', '')." </td>";
								echo "<td align='center' >&nbsp;</td>";
								echo "<td align='right'  >&nbsp;</td>";
								$linea=$linea. "<td>".str_pad( " "            ,$col[5] )."</td>";
								$linea=$linea. "<td>".str_pad( "VENTAS VALES"            ,$col[6]+$col[7]," ", STR_PAD_RIGHT )."</td>";
								$linea=$linea. "<td>".str_pad( number_format( $totalvale, 2, '.', '')     ,$col[8] , " ", STR_PAD_LEFT )."</td>";
								$linea=$linea. "<td>".str_pad( " "            ,$col[9] )."</td>";
								$linea=$linea. "<td>".str_pad( " "            ,$col[10] )."</td>";
								}
							if ($v_irowres==3)
								{
								echo "<td align='left' >&nbsp;</td>";
								echo "<td align='left' colspan=2>&nbsp;VENTAS TARJ CRED</td>";
								echo "<td align='right'  >&nbsp;".number_format( $totaltcre, 2, '.', '')." </td>";
								echo "<td align='center' >&nbsp;</td>";
								echo "<td align='right'  >&nbsp;</td>";
								$linea=$linea. "<td>".str_pad( " "            ,$col[5] )."</td>";
								$linea=$linea. "<td>".str_pad( "VENTAS TARJ CRED"            ,$col[6]+$col[7]," ", STR_PAD_RIGHT )."</td>";
								$linea=$linea. "<td>".str_pad( number_format( $totaltcre, 2, '.', '')     ,$col[8] , " ", STR_PAD_LEFT )."</td>";
								$linea=$linea. "<td>".str_pad( " "            ,$col[9] )."</td>";
								$linea=$linea. "<td>".str_pad( " "            ,$col[10] )."</td>";
								}
							if ($v_irowres==99)
								{
								echo "<td align='left' >&nbsp;</td>";
								echo "<td align='left' colspan=2>&nbsp;VENTAS AFERIC</td>";
								echo "<td align='right'  >&nbsp;".number_format( $totaltafe, 2, '.', '')." </td>";
								echo "<td align='center' >&nbsp;</td>";
								echo "<td align='right'  >&nbsp;</td>";
								$linea=$linea. "<td>".str_pad( " "            ,$col[5] )."</td>";
								$linea=$linea. "<td>".str_pad( "VENTAS AFERIC"            ,$col[6]+$col[7]," ", STR_PAD_RIGHT )."</td>";
								$linea=$linea. "<td>".str_pad( number_format( $totaltafe, 2, '.', '')     ,$col[8] , " ", STR_PAD_LEFT )."</td>";
								$linea=$linea. "<td>".str_pad( " "            ,$col[9] )."</td>";
								$linea=$linea. "<td>".str_pad( " "            ,$col[10] )."</td>";
								}
							if ($v_irowres==4)
								{
								echo "<td align='left' colspan=5>&nbsp;TOTAL CREDITOS</td>";
								echo "<td align='right' >&nbsp;".number_format( $totala, 2, '.', '')." </td>";
								$linea=$linea. "<td>".str_pad( "TOTAL CREDITOS" , $col[5]+$col[6]+$col[7]+$col[8]+$col[9] )."</td>";
								$linea=$linea. "<td>".str_pad( trim(number_format( $totala, 2, '.', '') ) ,$col[10], " ", STR_PAD_LEFT )."</td>";

								}
							if ($v_irowres==5)
								{
								echo "<td align='left' colspan=5>&nbsp;TOTAL DEPOSITOS</td>";
								echo "<td align='right' >&nbsp;".number_format( $totalb, 2, '.', '')." </td>";
								$linea=$linea. "<td>".str_pad( "TOTAL DEPOSITOS" , $col[5]+$col[6]+$col[7]+$col[8]+$col[9] )."</td>";
								$linea=$linea. "<td>".str_pad( trim(number_format( $totalb, 2, '.', '') ) ,$col[10], " ", STR_PAD_LEFT )."</td>";
								}
							if ($v_irowres==6)
								{
								$totdif=$sttimpotik-$totala-$totalb;
								if ($totdif>0)
									{
									echo "<td align='left' colspan=5>&nbsp;DIFERENCIA FALTANTE</td>";
									echo "<td align='right' >&nbsp;".number_format( $sttimpotik-$totala-$totalb, 2, '.', '')." </td>";
									$linea=$linea. "<td>".str_pad( "DIFERENCIA FALTANTE" , $col[5]+$col[6]+$col[7]+$col[8]+$col[9] )."</td>";
									$linea=$linea. "<td>".str_pad( trim(number_format($sttimpotik-$totala-$totalb, 2, '.', '') ) ,$col[10], " ", STR_PAD_LEFT )."</td>";
									}
								else
									{
									echo "<td align='left' colspan=5>&nbsp;DIFERENCIA SOBRANTE</td>";
									echo "<td align='right' >&nbsp;".number_format( -($sttimpotik-$totala-$totalb), 2, '.', '')." </td>";
									$linea=$linea. "<td>".str_pad( "DIFERENCIA SOBRANTE" , $col[5]+$col[6]+$col[7]+$col[8]+$col[9] )."</td>";
									$linea=$linea. "<td>".str_pad( trim(number_format( -($sttimpotik-$totala-$totalb), 2, '.', '') ) ,$col[10], " ", STR_PAD_LEFT )."</td>";
									}
								}
							$v_irowres++;
							}
						else
							{
							echo "<td align='center' >&nbsp;</td>";
							echo "<td align='center' >&nbsp;</td>";
							echo "<td align='center' >&nbsp;</td>";
							echo "<td align='right'  >&nbsp;</td>";
							echo "<td align='center' >&nbsp;</td>";
							echo "<td align='right'  >&nbsp;</td>";
							$linea=$linea. "<td>".str_pad( " " ,$col[5] )."</td>";
							$linea=$linea. "<td>".str_pad( " " ,$col[6] )."</td>";
							$linea=$linea. "<td>".str_pad( " " ,$col[7] )."</td>";
							$linea=$linea. "<td>".str_pad( " " ,$col[8] )."</td>";
							$linea=$linea. "<td>".str_pad( " " ,$col[9] )."</td>";
							$linea=$linea. "<td>".str_pad( " " ,$col[10] )."</td>";
							}
						}
					}


				echo "</tr>";
				$linea=$linea. "</tr>";
				$v_irowdocus++;
				}


			$linea=$linea. "<tr><td>".str_pad( " " , 150 , "_", STR_PAD_LEFT)."</td></tr>";
			$linea=$linea. "<tr><td>".str_pad( " " , 150 , "_", STR_PAD_LEFT)."</td></tr>";
			$linea=$linea. "<tr><td>".str_pad( " " , 150 , "_", STR_PAD_LEFT)."</td></tr>";
			$linea=$linea. "<tr><td>".str_pad( " " , 150 , "_", STR_PAD_LEFT)."</td></tr>";
			$linea=$linea. "<tr><td>".str_pad( " " , 150 , "_", STR_PAD_LEFT)."</td></tr>";
			$linea=$linea. "<tr><td>".str_pad( " " , 150 , "_", STR_PAD_LEFT)."</td></tr>";
			$linea=$linea. "<tr><td>".str_pad( " " , 150 , "_", STR_PAD_LEFT)."</td></tr>";
			$linea=$linea. "<tr><td>".str_pad( " " , 150 , "_", STR_PAD_LEFT)."</td></tr>";
			$linea=$linea. "<tr><td>".str_pad( " " , 150 , "_", STR_PAD_LEFT)."</td></tr>";
			$linea=$linea. "<tr><td>".str_pad( " " , 150 , "_", STR_PAD_LEFT)."</td></tr>";
			$linea=$linea. "<tr><td>".str_pad( " " , 150 , "_", STR_PAD_LEFT)."</td></tr>";
			$linea=$linea. "<tr><td>".str_pad( " " , 150 , "_", STR_PAD_LEFT)."</td></tr>";
			$linea=$linea. "<tr><td>".str_pad( " " , 150 , "_", STR_PAD_LEFT)."</td></tr>";
			$linea=$linea. "<tr><td>".str_pad( " " , 150 , "_", STR_PAD_LEFT)."</td></tr>";
			$linea=$linea. "<tr><td>".str_pad( " " , 150 , "_", STR_PAD_LEFT)."</td></tr>";
			$linea=$linea. "<tr><td>".str_pad( " " , 150 , "_", STR_PAD_LEFT)."</td></tr>";
			$linea=$linea. "<tr><td>".str_pad( " " , 150 , "_", STR_PAD_LEFT)."</td></tr>";
			$linea=$linea. "<tr><td>".str_pad( " " , 150 , "_", STR_PAD_LEFT)."</td></tr>";
			$linea=$linea. "<tr><td>".str_pad( " " , 150 , "_", STR_PAD_LEFT)."</td></tr>";
			$linea=$linea. "<tr><td>".str_pad( " " , 150 , "_", STR_PAD_LEFT)."</td></tr>";


			$col[0]=4;
			$col[1]=12;
			$col[2]=12;

			echo "</table>";
			$linea=$linea. "</table>";

			$sttimpotik=0;
			}

		echo "<table width='990' border='2' cellspacing=0 height='81'>";
		echo "<tr>";
		echo "	<th width='85'>Caja</td>";
		echo "	<th width='100' align='center'>Importe Tickets</td>";
		echo "</tr>";

		echo "<tr>";
		echo "<th align='center' >&nbsp;TOTAL</td>";
		echo "<th align='right' >&nbsp;".number_format($timpotik, 2, '.', '')." </td>";
		echo "</tr>";

		echo "</table>";

		$timpotik=0;

		imprimir2( $linea, $col, $nom, $v_archivo, "Venta Market" );


		}

	echo "<br>";

?>


<br>
<br>


</form>
</body>
</html>
<?php
// comprueba si la conexion existe y la cierra
if ($conector_id) pg_close($conector_id);
// restaura el control de errores original
$clase_error->_error();
