<?php
include("../valida_sess.php");
include("../menu_princ.php");
include("../functions.php");
include("/sistemaweb/utils/funcion-texto.php");
require("../clases/funciones.php");

$funcion = new class_funciones;
$clase_error = new OpensoftError;
$coneccion = $funcion->conectar("","","","","");

$v_grifo = true;

if ( is_null($almacen) or trim($almacen)=="") {
	$almacen = "001";
}

if ( trim($almacen)=="001") {
	$v_grifo = false;
}

if (is_null($v_fecha_desde) or is_null($v_fecha_hasta)) {
	$v_fecha_desde = date("d/m/Y");
	$v_fecha_hasta = date("d/m/Y");
}

if ( is_null($v_primra)) {
	$v_primra = "S";
}

if($txtcampo=="A") { 
	$ch  = " checked"; 
} elseif($txtcampo=="B") { 
	$ch1 = " checked"; 
} elseif($txtcampo=="C") { 
	$ch2 = " checked"; 
} elseif($txtcampo=="D") { 
	$ch3 = " checked"; 
} else { 
	$ch  = " checked"; 
}

$txtxbusqueda = strtoupper($txtxbusqueda);

if($boton == "buscar") {
	if ( strlen(trim($txtxbusqueda))<=0 and strlen(trim($v_turno_desde))<=0 and strlen(trim($v_turno_hasta))<=0 ) {
		$addsql = " WHERE
				pos_depositos_diarios.ch_almacen='".$almacen."'
				AND to_char(dt_dia,'yyyy-mm-dd') BETWEEN '".$funcion->date_format($v_fecha_desde,'YYYY-MM-DD')."'
				AND '".$funcion->date_format($v_fecha_hasta,'YYYY-MM-DD')."' ";
		$bddsql = " LIMIT 100";
	} else {
		$bddsql = " ";
		if (strlen(trim($v_turno_desde))<=0 and strlen(trim($v_turno_hasta))<=0) {
			if( $txtcampo == "A" ) {
				$addsql=" where pos_depositos_diarios.ch_codigo_trabajador||
						pla_ta_trabajadores.ch_apellido_paterno||
						pla_ta_trabajadores.ch_apellido_materno||
						pla_ta_trabajadores.ch_nombre1||
						pla_ta_trabajadores.ch_nombre2 like '%".$txtxbusqueda."%'
						and pos_depositos_diarios.ch_almacen='".$almacen."'
						and to_char(dt_dia,'yyyy-mm-dd')
						between '".$funcion->date_format($v_fecha_desde,'YYYY-MM-DD')."'
						and '".$funcion->date_format($v_fecha_hasta,'YYYY-MM-DD')."' ";

			} elseif($txtcampo=="B") {
				$addsql=" where pos_depositos_diarios.ch_numero_correl ='".$txtxbusqueda."'
						and pos_depositos_diarios.ch_almacen='".$almacen."'
						and to_char(dt_dia,'yyyy-mm-dd')
						between '".$funcion->date_format($v_fecha_desde,'YYYY-MM-DD')."'
						and '".$funcion->date_format($v_fecha_hasta,'YYYY-MM-DD')."' ";

			} elseif($txtcampo=="C") {
				$addsql=" WHERE
						pos_depositos_diarios.ch_numero_documento ='".$txtxbusqueda."'
						and pos_depositos_diarios.ch_almacen='".$almacen."'
						and to_char(dt_dia,'yyyy-mm-dd')
						between '".$funcion->date_format($v_fecha_desde,'YYYY-MM-DD')."'
						and '".$funcion->date_format($v_fecha_hasta,'YYYY-MM-DD')."' ";

			} elseif($txtcampo=="D") {
				$addsql=" WHERE
						pos_depositos_diarios.ch_serie1||pos_depositos_diarios.ch_serie2||pos_depositos_diarios.ch_serie3 like '%".$txtxbusqueda."%'
						and pos_depositos_diarios.ch_almacen='".$almacen."'
						and to_char(dt_dia,'yyyy-mm-dd')
						between '".$funcion->date_format($v_fecha_desde,'YYYY-MM-DD')."'
						and '".$funcion->date_format($v_fecha_hasta,'YYYY-MM-DD')."' ";
			} else {
				$addsql=" ";
			}
		} else {
			if( $txtcampo=="A" ) {			
				$addsql=" WHERE
						pos_depositos_diarios.ch_codigo_trabajador||
						pla_ta_trabajadores.ch_apellido_paterno||
						pla_ta_trabajadores.ch_apellido_materno||
						pla_ta_trabajadores.ch_nombre1||
						pla_ta_trabajadores.ch_nombre2 like '%".$txtxbusqueda."%'
						and pos_depositos_diarios.ch_almacen='".$almacen."'
						and to_char(dt_dia,'yyyy-mm-dd')||to_char(to_number(ch_posturno,'99'),'99')
						between '".$funcion->date_format($v_fecha_desde,'YYYY-MM-DD')."'||to_char(".$v_turno_desde.",'99')
						and '".$funcion->date_format($v_fecha_hasta,'YYYY-MM-DD')."'||to_char(".$v_turno_hasta.",'99') ";

			} elseif($txtcampo=="B") {
				$addsql=" WHERE
						pos_depositos_diarios.ch_numero_correl ='".$txtxbusqueda."'
						and pos_depositos_diarios.ch_almacen='".$almacen."'
						and to_char(dt_dia,'yyyy-mm-dd')||to_char(to_number(ch_posturno,'99'),'99')
						between '".$funcion->date_format($v_fecha_desde,'YYYY-MM-DD')."'||to_char(".$v_turno_desde.",'99')
						and '".$funcion->date_format($v_fecha_hasta,'YYYY-MM-DD')."'||to_char(".$v_turno_hasta.",'99') ";

			} elseif($txtcampo=="C") {
				$addsql=" WHERE
						pos_depositos_diarios.ch_numero_documento ='".$txtxbusqueda."'
						and pos_depositos_diarios.ch_almacen='".$almacen."'
						and to_char(dt_dia,'yyyy-mm-dd')||to_char(to_number(ch_posturno,'99'),'99')
						between '".$funcion->date_format($v_fecha_desde,'YYYY-MM-DD')."'||to_char(".$v_turno_desde.",'99')
						and '".$funcion->date_format($v_fecha_hasta,'YYYY-MM-DD')."'||to_char(".$v_turno_hasta.",'99') ";

			} elseif($txtcampo=="D") {
				$addsql=" WHERE
						pos_depositos_diarios.ch_serie1||pos_depositos_diarios.ch_serie2||pos_depositos_diarios.ch_serie3 like '%".$txtxbusqueda."%'
						and pos_depositos_diarios.ch_almacen='".$almacen."'
						and to_char(dt_dia,'yyyy-mm-dd')||to_char(to_number(ch_posturno,'99'),'99')
						between '".$funcion->date_format($v_fecha_desde,'YYYY-MM-DD')."'||to_char(".$v_turno_desde.",'99')
						and '".$funcion->date_format($v_fecha_hasta,'YYYY-MM-DD')."'||to_char(".$v_turno_hasta.",'99') ";

			} else {
				$addsql=" ";
			}
		}

		if ( strlen(trim($v_turno_desde))>0 and strlen(trim($v_turno_hasta))>0 and strlen(trim($txtxbusqueda))<=0 ) {
			$addsql = "where pos_depositos_diarios.ch_almacen='".$almacen."'
					and to_char(dt_dia,'yyyy-mm-dd')||to_char(to_number(ch_posturno,'99'),'99')
					between '".$funcion->date_format($v_fecha_desde,'YYYY-MM-DD')."'||to_char(".$v_turno_desde.",'99')
					and '".$funcion->date_format($v_fecha_hasta,'YYYY-MM-DD')."'||to_char(".$v_turno_hasta.",'99') ";
		} else {
			// entonces txtxbusq es verdad hace lo de los check
		}
	}

	// carga las variables para mandar el reporte a impresion texto
	$v_sqlx 	= "select par_valor from int_parametros where trim(par_nombre)='print_netbios' ";
	$v_xsqlx	= pg_exec( $v_sqlx);
	$v_server 	= pg_result($v_xsqlx,0,0);

	$v_sqlx 	= "select par_valor from int_parametros where trim(par_nombre)='print_name' ";
	$v_xsqlx	= pg_exec($v_sqlx);
	$v_printer	= pg_result($v_xsqlx,0,0);

	$v_sqlx 	= "select par_valor from int_parametros where trim(par_nombre)='print_server' ";
	$v_xsqlx	= pg_exec($v_sqlx);
	$v_ipprint	= pg_result($v_xsqlx,0,0);

	$v_archivo	= "/tmp/imprimir/vta_depositos.txt";
} else {
	if ($v_primra=="S") {
		$addsql = " WHERE
				pos_depositos_diarios.ch_almacen='".$almacen."'
				and to_char(dt_dia,'yyyy-mm-dd')
				between '".$funcion->date_format($v_fecha_desde,'YYYY-MM-DD')."'
				and '".$funcion->date_format($v_fecha_hasta,'YYYY-MM-DD')."' ";
		$bddsql=" limit 100";
		$v_primra="N";

	} else {
		if ( strlen(trim($txtxbusqueda))<=0 and strlen(trim($v_turno_desde))<=0 and strlen(trim($v_turno_hasta))<=0 ) {
			$addsql=" where pos_depositos_diarios.ch_almacen='".$almacen."'
					and to_char(dt_dia,'yyyy-mm-dd') between '".$funcion->date_format($v_fecha_desde,'YYYY-MM-DD')."'
					and '".$funcion->date_format($v_fecha_hasta,'YYYY-MM-DD')."' ";
			$bddsql=" limit 100";
		} else {
			$bddsql=" ";
			if (strlen(trim($v_turno_desde))<=0 and strlen(trim($v_turno_hasta))<=0) {
				if( $txtcampo=="A" ) {
					$addsql=" where pos_depositos_diarios.ch_codigo_trabajador||
							pla_ta_trabajadores.ch_apellido_paterno||
							pla_ta_trabajadores.ch_apellido_materno||
							pla_ta_trabajadores.ch_nombre1||
							pla_ta_trabajadores.ch_nombre2 like '%".$txtxbusqueda."%'
							and pos_depositos_diarios.ch_almacen='".$almacen."'
							and to_char(dt_dia,'yyyy-mm-dd')
							between '".$funcion->date_format($v_fecha_desde,'YYYY-MM-DD')."'
							and '".$funcion->date_format($v_fecha_hasta,'YYYY-MM-DD')."' ";

				} elseif($txtcampo=="B") {
					$addsql=" where pos_depositos_diarios.ch_numero_correl ='".$txtxbusqueda."'
						and pos_depositos_diarios.ch_almacen='".$almacen."'
						and to_char(dt_dia,'yyyy-mm-dd')
						between '".$funcion->date_format($v_fecha_desde,'YYYY-MM-DD')."'
						and '".$funcion->date_format($v_fecha_hasta,'YYYY-MM-DD')."' ";
				} elseif($txtcampo=="C") {
					$addsql=" where pos_depositos_diarios.ch_numero_documento ='".$txtxbusqueda."'
							and pos_depositos_diarios.ch_almacen='".$almacen."'
							and to_char(dt_dia,'yyyy-mm-dd')
							between '".$funcion->date_format($v_fecha_desde,'YYYY-MM-DD')."'
							and '".$funcion->date_format($v_fecha_hasta,'YYYY-MM-DD')."' ";
				} elseif($txtcampo=="D") {
					$addsql=" where pos_depositos_diarios.ch_serie1||pos_depositos_diarios.ch_serie2||pos_depositos_diarios.ch_serie3 like '%".$txtxbusqueda."%'
							and pos_depositos_diarios.ch_almacen='".$almacen."'
							and to_char(dt_dia,'yyyy-mm-dd')
							between '".$funcion->date_format($v_fecha_desde,'YYYY-MM-DD')."'
							and '".$funcion->date_format($v_fecha_hasta,'YYYY-MM-DD')."' ";
				} else {
					$addsql=" ";
				}
			} else {
				if( $txtcampo=="A" ) {
					$addsql=" where pos_depositos_diarios.ch_codigo_trabajador||
							pla_ta_trabajadores.ch_apellido_paterno||
							pla_ta_trabajadores.ch_apellido_materno||
							pla_ta_trabajadores.ch_nombre1||
							pla_ta_trabajadores.ch_nombre2 like '%".$txtxbusqueda."%'
							and pos_depositos_diarios.ch_almacen='".$almacen."'
							and to_char(dt_dia,'yyyy-mm-dd')||to_char(to_number(ch_posturno,'99'),'99')
							between '".$funcion->date_format($v_fecha_desde,'YYYY-MM-DD')."'||to_char(".$v_turno_desde.",'99')
							and '".$funcion->date_format($v_fecha_hasta,'YYYY-MM-DD')."'||to_char(".$v_turno_hasta.",'99') ";
				} elseif($txtcampo=="B") {
					$addsql=" where pos_depositos_diarios.ch_numero_correl ='".$txtxbusqueda."'
							and pos_depositos_diarios.ch_almacen='".$almacen."'
							and to_char(dt_dia,'yyyy-mm-dd')||to_char(to_number(ch_posturno,'99'),'99')
							between '".$funcion->date_format($v_fecha_desde,'YYYY-MM-DD')."'||to_char(".$v_turno_desde.",'99')
							and '".$funcion->date_format($v_fecha_hasta,'YYYY-MM-DD')."'||to_char(".$v_turno_hasta.",'99') ";
				} elseif($txtcampo=="C") {
					$addsql=" where pos_depositos_diarios.ch_numero_documento ='".$txtxbusqueda."'
							and pos_depositos_diarios.ch_almacen='".$almacen."'
							and to_char(dt_dia,'yyyy-mm-dd')||to_char(to_number(ch_posturno,'99'),'99')
							between '".$funcion->date_format($v_fecha_desde,'YYYY-MM-DD')."'||to_char(".$v_turno_desde.",'99')
							and '".$funcion->date_format($v_fecha_hasta,'YYYY-MM-DD')."'||to_char(".$v_turno_hasta.",'99') ";
				} elseif($txtcampo=="D") {
					$addsql=" where pos_depositos_diarios.ch_serie1||pos_depositos_diarios.ch_serie2||pos_depositos_diarios.ch_serie3 like '%".$txtxbusqueda."%'
							and pos_depositos_diarios.ch_almacen='".$almacen."'
							and to_char(dt_dia,'yyyy-mm-dd')||to_char(to_number(ch_posturno,'99'),'99')
							between '".$funcion->date_format($v_fecha_desde,'YYYY-MM-DD')."'||to_char(".$v_turno_desde.",'99')
							and '".$funcion->date_format($v_fecha_hasta,'YYYY-MM-DD')."'||to_char(".$v_turno_hasta.",'99') ";
				} else {
					$addsql=" ";
				}
			}

			if ( strlen(trim($v_turno_desde))>0 and strlen(trim($v_turno_hasta))>0 and strlen(trim($txtxbusqueda))<=0 ) {
				// solo tiene turno no los txtxbusq
				$addsql = "where pos_depositos_diarios.ch_almacen='".$almacen."'
						and to_char(dt_dia,'yyyy-mm-dd')||to_char(to_number(ch_posturno,'99'),'99')
						between '".$funcion->date_format($v_fecha_desde,'YYYY-MM-DD')."'||to_char(".$v_turno_desde.",'99')
						and '".$funcion->date_format($v_fecha_hasta,'YYYY-MM-DD')."'||to_char(".$v_turno_hasta.",'99') ";
			} else {
				// entonces txtxbusq es verdad hace lo de los check
			}
		}
	}

	// carga las variables para mandar el reporte a impresion texto
	$v_sqlx ="select par_valor from int_parametros where trim(par_nombre)='print_netbios' ";
	$v_xsqlx=pg_exec( $v_sqlx);
	$v_server =pg_result($v_xsqlx,0,0);

	$v_sqlx 	= "select par_valor from int_parametros where trim(par_nombre)='print_name' ";
	$v_xsqlx	= pg_exec($v_sqlx);
	$v_printer	= pg_result($v_xsqlx,0,0);

	$v_sqlx 	= "select par_valor from int_parametros where trim(par_nombre)='print_server' ";
	$v_xsqlx	= pg_exec($v_sqlx);
	$v_ipprint	= pg_result($v_xsqlx,0,0);

	$v_archivo	= "/tmp/imprimir/vta_depositos.txt";
}

if($boton=="Mod" or $boton=="modificar") {
/*
	$xsql=pg_exec($coneccion,$sql);
	$ilimit=pg_numrows($xsql);
	$irow=0;

	$sql2="select 	ch_valida,
			dt_dia,
			ch_posturno,
			pos_depositos_diarios.ch_codigo_trabajador,
			pos_depositos_diarios.ch_codigo_trabajador||' - '||trim(ch_apellido_paterno)||' '||trim(ch_apellido_materno)||' '||trim(ch_nombre1)||' '||trim(ch_nombre2) as nombre,
			dt_fecha,
			ch_numero_correl,
			ch_numero_documento,
			ch_moneda,
			nu_tipo_cambio,
			nu_importe,
			trim(pos_depositos_diarios.ch_almacen)||trim( to_char(dt_dia,'YYYY-mm-dd') )||trim(ch_posturno)||trim(pos_depositos_diarios.ch_codigo_trabajador)||trim(ch_numero_documento)
		from pos_depositos_diarios left join pla_ta_trabajadores
			on pos_depositos_diarios.ch_codigo_trabajador = pla_ta_trabajadores.ch_codigo_trabajador
		".$addsql." order by 2,3,7  ".$bddsql." ";

	$xsql2=pg_exec($coneccion,$sql2);
	$ilimit2=pg_numrows($xsql2);
	$irow2=0;
*/

	// echo $sql_depositos;
	//$sql2=$sql_depositos;

	$sql_depositos="SELECT
				ch_valida,
				dt_dia,
				ch_posturno,
				pos_depositos_diarios.ch_codigo_trabajador,
				trim(pos_depositos_diarios.ch_codigo_trabajador)||' - '||trim(ch_apellido_paterno)||' '||trim(ch_apellido_materno)||' '||trim(ch_nombre1)||' '||trim(ch_nombre2) as nombre,
				dt_fecha,
				ch_numero_correl,
				ch_numero_documento,
				ch_moneda,
				nu_tipo_cambio,
				nu_importe,
				trim(pos_depositos_diarios.ch_almacen)||trim( to_char(dt_dia,'YYYY-mm-dd') )||trim(ch_posturno)||trim(pos_depositos_diarios.ch_codigo_trabajador)||trim(ch_numero_documento)
			FROM
				pos_depositos_diarios 
				LEFT JOIN pla_ta_trabajadores ON pos_depositos_diarios.ch_codigo_trabajador = pla_ta_trabajadores.ch_codigo_trabajador
				".$addsql." 
			ORDER BY
				2,3,7 ".$bddsql." ";

	$xsql2	 = pg_exec($coneccion,$sql_depositos);
	$ilimit2 = pg_numrows($xsql2);
	$irow2	 = 0;

	while($irow2<$ilimit2) {
		$v_clave = pg_result($xsql2,$irow2,11);
		$xelem = "id_".$v_clave;
		$upd_valida = "valida_".$v_clave;

		if($$xelem==$v_clave) {
			$v_ch_valida="S";
			$sql1 = "UPDATE pos_depositos_diarios 
				SET ch_valida='".$v_ch_valida."'
				WHERE trim(ch_almacen)||trim( to_char(dt_dia,'YYYY-mm-dd') )||trim(ch_posturno)||trim(ch_codigo_trabajador)||trim(ch_numero_documento)='".$$xelem."' ";
			$xsql1=pg_exec($coneccion,$sql1);
		} else {
			$v_ch_valida=" ";
			$sql1=
			"update pos_depositos_diarios set
				ch_valida='".$v_ch_valida."'
			where
				trim(ch_almacen)||trim( to_char(dt_dia,'YYYY-mm-dd') )||trim(ch_posturno)||trim(ch_codigo_trabajador)||trim(ch_numero_documento)='".$v_clave."' ";
			//echo $sql1;
			$xsql1=pg_exec($coneccion,$sql1);
		}
		$irow2++;
	}
}
?>

<html><link rel="stylesheet" href="<?php echo $v_path_url; ?>sistemaweb.css" type="text/css">
<head>
<title>DEPOSITOS DIARIOS</title>
<script language="JavaScript" src="js/miguel.js"></script>
<script language="JavaScript" src="/sistemaweb/clases/calendario.js"></script>
<script language="JavaScript" src="/sistemaweb/clases/overlib_mini.js"></script>
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

<body>
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<form name="f_repo" method="post">
DEPOSITOS DIARIOS
ALMACEN ACTUAL <?php echo $almacen;?> 	<?php echo $v_descalma; ?>

<?php
if (is_null($v_fecha_desde) or is_null($v_fecha_hasta) )
	{
	$v_fecha_desde=date("d/m/Y");
	$v_fecha_hasta=date("d/m/Y");
	$v_turno_desde=0;
	$v_turno_hasta=0;
	}
?>

<input type="hidden" name="v_primra" value='<?php echo $v_primra; ?>'  >


<table border="1">
	<tr>
		<th colspan="7">POSICIONES : FECHA / TURNO </th>
	</tr>

	<tr>
		<th>FECHA :</th>
		<th>
		<p>
		<input type="text" name="v_fecha_desde" size="16" maxlength="10" value='<?php echo $v_fecha_desde ; ?>'  tabindex="1"  onKeyUp="javascript:validarFecha(this)" onblur="javascript:validarFecha(this)"  >
		<a href="javascript:show_calendar('f_repo.v_fecha_desde');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;" >
		<img src="/sistemaweb/images/show-calendar.gif" width=24 height=22 border=0></a>
		</p>
		</th>
		<th>TURNO :</th>
		<th>
		<input type="text" name="v_turno_desde" size="4" maxlength="2" value='<?php echo $v_turno_desde ; ?>'  tabindex="2"  >
		</th>
	</tr>

	<tr>
		<td>Busqueda Rapida: </td>
		<td><input name="boton" type="submit" value="buscar"></td>
	</tr>

</table>

<input type="hidden" name="varx" value="<?php echo $varx;?>">
<?php

$v_sqlalma="select trim(ch_almacen), ch_nombre_almacen from inv_ta_almacenes  where ch_almacen like '%".trim($almacen)."%' and ch_clase_almacen='1' ";
$v_xsqlalma=pg_query($coneccion,$v_sqlalma);
if(pg_numrows($v_xsqlalma)>0)	{	$v_descalma=pg_result($v_xsqlalma,0,1);	}

echo "<table border='1' cellpadding='0' cellspacing='0'>";
	echo "<tr>";
	echo "	<th>&nbsp;</th>";
	echo "	<th>VALIDA</th>";
	echo "	<th>DIA</th>";
	echo "	<th>TURNO</th>";
	echo "	<th>TRABAJADOR</th>";
	echo "	<th>FECHA</th>";
	echo "	<th>SEQ</th>";
	echo "	<th>NUM</th>";
	echo "	<th>MONEDA</th>";
	echo "	<th>CAMBIO</th>";
	echo "	<th>IMPORTE S/.</th>";
	echo "	<th>IMPORTE US$</th>";
	echo "</tr>";
	?>
	<tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>
		<input name='boton' type='submit' value='Mod' onMouseOver="this.style.backgroundColor='#FFFFCC';this.style.cursor='hand';" onMouseOut="this.style.backgroundColor='#c0c0c0';">
		</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	<?php
	$mes=date("m");
	$ano=date("Y");
	$totsol=0;
	$totdol=0;
	$totsolimp=0;
	$totdolimp=0;
	$sql_depositos="select 	ch_valida,
			dt_dia,
			ch_posturno,
			pos_depositos_diarios.ch_codigo_trabajador,
			trim(pos_depositos_diarios.ch_codigo_trabajador)||' - '||trim(ch_apellido_paterno)||' '||trim(ch_apellido_materno)||' '||trim(ch_nombre1)||' '||trim(ch_nombre2) as nombre,
			dt_fecha,
			ch_numero_correl,
			ch_numero_documento,
			ch_moneda,
			nu_tipo_cambio,
			nu_importe,
			trim(pos_depositos_diarios.ch_almacen)||trim( to_char(dt_dia,'YYYY-mm-dd') )||trim(ch_posturno)||trim(pos_depositos_diarios.ch_codigo_trabajador)||trim(ch_numero_documento)
		from pos_depositos_diarios left join pla_ta_trabajadores
			on pos_depositos_diarios.ch_codigo_trabajador = pla_ta_trabajadores.ch_codigo_trabajador
		".$addsql." order by 2,3,7 ".$bddsql." ";

	// echo $sql;
	$xsql_depositos=pg_exec($coneccion,$sql_depositos);
	$ilimit=pg_numrows($xsql_depositos);
	$irow=0;
	$cant=0;

	while($irow<$ilimit)
		{
		$v_valida            = pg_result($xsql_depositos,$irow,0);
		$v_dia               = pg_result($xsql_depositos,$irow,1);
		$v_turno             = pg_result($xsql_depositos,$irow,2);
		$v_trabajador        = pg_result($xsql_depositos,$irow,3);
		$v_trabajador_nombre = pg_result($xsql_depositos,$irow,4);
		$v_fecha             = pg_result($xsql_depositos,$irow,5);
		$v_correl            = pg_result($xsql_depositos,$irow,6);
		$v_documento         = pg_result($xsql_depositos,$irow,7);
		$v_moneda            = pg_result($xsql_depositos,$irow,8);
		$v_tipo_cambio       = pg_result($xsql_depositos,$irow,9);
		$v_importe           = pg_result($xsql_depositos,$irow,10);
		$v_clave             = pg_result($xsql_depositos,$irow,11);

		?>
		<tr bgcolor="#CCCC99" onMouseOver="this.style.backgroundColor='#FFFFCC';this.style.cursor='hand';" onMouseOut="this.style.backgroundColor='#CCCC99'"o"];">
		<?php

		if ( ($v_valida=="S") or ($v_valida=="s") )
			{
			echo "<td><input type='checkbox' name='id_".$v_clave."' value='".$v_clave."' checked></td>";
			}
		else
			{
			echo "<td><input type='checkbox' name='id_".$v_clave."' value='".$v_clave."' ></td>";
			}

		//echo "<td><input name='valida_".$v_clave."' type='text' size='5' maxlength='1'  align='center' value='".$v_valida."'></td>";
		echo "<td>&nbsp;".$v_valida."</td>";
		echo "<td>&nbsp;".$v_dia."</td>";
		echo "<td align='center'>&nbsp;".$v_turno."</td>";
		echo "<td align='left'>&nbsp;".$v_trabajador_nombre."</td>";
		echo "<td align='center'>&nbsp; ".$v_fecha."</td>";
		echo "<td align='center'>&nbsp; ".$v_correl."</td>";
		echo "<td align='center'>&nbsp; ".$v_documento."</td>";
		echo "<td align='center'>&nbsp; ".$v_moneda."</td>";
		echo "<td align='center'>&nbsp; ".$v_tipo_cambio."</td>";
		if($v_moneda=="01")
			{
			echo "<td align='right'>&nbsp; ".number_format($v_importe, 2, '.', '')."</td>";
			echo "<td align='right'>&nbsp;</td>";
			$totsol=$totsol+$v_importe;
			}
		else
			{
			echo "<td align='right'>&nbsp;</td>";
			echo "<td align='right'>&nbsp; ".number_format($v_importe, 2, '.', '')."</td>";
			$totdol=$totdol+$v_importe;
			}
		echo "</tr>";
		if( $v_valida=="S" or $v_valida=="s" )
			{
			if($v_moneda=="01")
				{
				$totsolimp=$totsolimp+$v_importe;
				}
			else
				{
				$totdolimp=$totdolimp+$v_importe;
				}

			$linea=$linea. "<tr>".str_pad( " ", $col[0]+$col[1]+$col[2]+$col[3]+$col[4]+$col[5]+$col[6]+$col[7]+$col[8]+$col[9]+$col[10]+11, "-", STR_PAD_LEFT )."</tr>";
			$linea=$linea. "<tr>";
			$linea=$linea. "<td>".str_pad( $v_dia               ,$col[0] )."</td>";
			$linea=$linea. "<td>".str_pad( $v_turno             ,$col[1] , " ", STR_PAD_BOTH )."</td>";
			$linea=$linea. "<td>".str_pad( $v_trabajador_nombre ,$col[2] )."</td>";
			$linea=$linea. "<td>".str_pad( $v_fecha             ,$col[3] )."</td>";
			$linea=$linea. "<td>".str_pad( trim($v_correl)      ,$col[4], " ", STR_PAD_LEFT  )."</td>";
			$linea=$linea. "<td>".str_pad( trim($v_documento)   ,$col[5], " ", STR_PAD_LEFT  )."</td>";
			$linea=$linea. "<td>".str_pad( trim($v_moneda)      ,$col[6], " ", STR_PAD_BOTH )."</td>";
			$linea=$linea. "<td>".str_pad( trim( number_format( $v_tipo_cambio , 4, '.', '') ), $col[7], " ", STR_PAD_LEFT )."</td>";
			if($v_moneda=="01")
				{
				$linea=$linea. "<td>".str_pad( number_format($v_importe, 2, '.', ''), $col[8], " ", STR_PAD_LEFT )."</td>";
				$linea=$linea. "<td>".str_pad( " " ,$col[9] )."</td>";
				}
			else
				{
				$linea=$linea. "<td>".str_pad( " " ,$col[8] )."</td>";
				$linea=$linea. "<td>".str_pad( number_format($v_importe, 2, '.', ''), $col[9], " ", STR_PAD_LEFT )."</td>";
				}
			$linea=$linea. "<td>|".str_pad( " ", $col[10], " ", STR_PAD_BOTH )."</td>";
			$linea=$linea. "</tr>";
			$cant++;
			}
		$irow++;
		}
	echo "<tr>";
	echo "<td>&nbsp;</td>";
	echo "<td>&nbsp;</td>";
	echo "<td>&nbsp;</td>";
	echo "<td align='center'>&nbsp;</td>";
	echo "<td align='left'>&nbsp; TOTAL DEPOSITOS: ---------> ( Cantidad ".number_format($cant, 0, '.', '')." )</td>";
	echo "<td align='center'>&nbsp;</td>";
	echo "<td align='center'>&nbsp;</td>";
	echo "<td align='center'>&nbsp;</td>";
	echo "<td align='center'>&nbsp;</td>";
	echo "<td align='center'>&nbsp;</td>";
	echo "<td align='right'>&nbsp;".number_format($totsol, 2, '.', '')."</td>";
	echo "<td align='right'>&nbsp;".number_format($totdol, 2, '.', '')."</td>";
	echo "</tr>";

	$linea = $linea. "<tr>".str_pad( " ", $col[0]+$col[1]+$col[2]+$col[3]+$col[4]+$col[5]+$col[6]+$col[7]+$col[8]+$col[9]+$col[10]+11, "=", STR_PAD_LEFT )."</tr>";
	$linea = $linea. "<tr>";
	$linea = $linea. "<td>".str_pad( " " ,$col[0] )."</td>";
	$linea = $linea. "<td>".str_pad( " " ,$col[1] )."</td>";
	$linea = $linea. "<td>".str_pad( "TOTAL DEPOSITOS ----> ( Cantidad ".number_format($cant, 0, '.', '')." )" ,$col[2] )."</td>";
	$linea = $linea. "<td>".str_pad( " " ,$col[3] )."</td>";
	$linea = $linea. "<td>".str_pad( " ", $col[4] )."</td>";
	$linea = $linea. "<td>".str_pad( " " ,$col[5] )."</td>";
	$linea = $linea. "<td>".str_pad( " " ,$col[6] )."</td>";
	$linea = $linea. "<td>".str_pad( " " ,$col[7] )."</td>";
	$linea = $linea. "<td>".str_pad( trim(number_format($totsolimp, 2, '.', '') ), $col[8], " ", STR_PAD_LEFT ) ."</td>";
	$linea = $linea. "<td>".str_pad( trim(number_format($totdolimp, 2, '.', '') ), $col[9], " ", STR_PAD_LEFT ) ."</td>";
	$linea = $linea. "</tr>";

	?>
	<tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>
		<input name='boton' type='submit' value='Mod' onMouseOver="this.style.backgroundColor='#FFFFCC';this.style.cursor='hand';" onMouseOut="this.style.backgroundColor='#c0c0c0';">
		</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	<?php
echo "</table>";
$linea = $linea. "</table>";
imprimir2( $linea, $col, $nom, $v_archivo, "Depositos Tirada" );
?>
</form>
</body>
</html>
<?php
pg_close($coneccion);
