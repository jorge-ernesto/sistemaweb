<?php
include("../menu_princ.php");
include("../functions.php");
include("../funcjch.php");

require("../clases/funciones.php");
$funcion = new class_funciones;

extract($_REQUEST);

// crea la clase para controlar errores
$clase_error = new OpensoftError;
$clase_error->_error();

// conectar con la base de datos
$conector_id = $funcion->conectar("", "", "", "", "");

if ( is_null($almacen) or trim($almacen) == "") {
	$almacen = "001";
}

// carga los almacenes en un dropdown 
$v_xsqlalma = pg_exec("SELECT trim(ch_almacen) AS cod, ch_nombre_almacen FROM inv_ta_almacenes WHERE ch_clase_almacen = '1' ORDER BY cod");

if ( is_null($v_fecha_desde) or is_null($v_fecha_hasta) ) {
	$v_fecha_desde = date("d/m/Y");
	$v_fecha_hasta = date("d/m/Y");
	$v_turno_desde = 0;
	$v_turno_hasta = 0;
}

$v_ilimit = 0;

if ( $boton == 'Imprimir' ) { //Boton Imprimir
	// Limpia la tabla del reporte
	//limpia_tabla();
	// Aqui carga los contometros del combex desde la 
	// fecha/turno inicio en una tabla temporal

	// caso avance
	//carga_contometros("A");
	
	// caso dia y turno especifico
	//carga_contometros("2004-04-30", "1");

				
	$v_xsqlcont = pg_exec($conector_id,"truncate tempo" );

	
	$v_sqlcont = "
	SELECT
	 dia
	 , turno
	FROM
	 pos_contometros 
	WHERE
	 to_char(dia,'yyyy-mm-dd') || to_char(turno,'99') 
	 BETWEEN '".$funcion->date_format($v_fecha_desde,'YYYY-MM-DD')."'||to_char(".$v_turno_desde.",'99')
	 AND '".$funcion->date_format($v_fecha_hasta,'YYYY-MM-DD')."'||to_char(".$v_turno_hasta.",'99')  
	 GROUP BY dia, turno
	";
	echo "<pre>";
	echo $v_sqlcont;
	echo "</pre>";
				
	$v_xsqlcont = pg_exec($conector_id,$v_sqlcont);
	$v_ilimit = pg_numrows($v_xsqlcont);
	echo "<pre>";
	echo "v_ilim<br>";
	echo $v_ilimit;
	echo "</pre>";
	//if (false)

	$cantidad_acumulada = 0;
	$importe_acumulado = 0;

	if ( $v_ilimit > 0 ) {
		$v_irow = 0;
		while ($v_irow < $v_ilimit) {
			$v_fecha_act = pg_result($v_xsqlcont, $v_irow, 0);
			$v_turno_act = pg_result($v_xsqlcont, $v_irow, 1);

			echo "***********************************************";
			echo "<pre>";
			print_r( array( $v_fecha_act, $v_turno_act ) );
			echo "</pre>";

			// si la fecha es igual que la que se pide entonces verificar el turno
			// si la fecha no es igual asigna el turno actual al primero de la lista

			// caso de avance= carga_postrans("A");
			$v_sqlfunc = "
			SELECT combex_fn_reporte_contometros('".$funcion->date_format($v_fecha_act,'YYYY-MM-DD')."',".$v_turno_act.")";
			echo "<pre>";
			echo $v_sqlfunc;
			echo "</pre>";
			
			// echo $v_sqlfunc."<br>";
			$v_xsql = pg_exec( $v_sqlfunc );
			$v_xsql = pg_exec("SELECT * FROM vista_reporte_combex_contometros");
			echo "<pre>";
			echo "SELECT * FROM vista_reporte_combex_contometros";
			echo "</pre>";

			$v_ilim2 = pg_numrows($v_xsql);
			echo "<pre>";
			echo "v_ilim2: $v_ilim2<br>";

			if ( $v_ilim2 > 0 ) {
				$v_irow2 = 0;
				while ( $v_irow2 < $v_ilim2 ) {
					$v_lado = trim(pg_result($v_xsql, $v_irow2, 0));
					// echo "<pre>";
					// echo "v_lado<br>";
					// echo $v_lado;
					// echo "</pre>";
					if ( strlen($v_lado) > 1 ){
						$v_lado = $v_lado;
					} else {
						$v_lado = "0".$v_lado;
					}
					echo "-----<br>";
					echo "v_lado: $v_lado<br>";

					$v_mang = trim(pg_result($v_xsql, $v_irow2, 1));
					if ( strlen($v_mang) > 1 ) {
						$v_mang = substr($v_mang, 1, 1);
						$v_surt = $v_mang;
					} else {
						$v_surt = "0".$v_mang;
					}
					echo "v_mang: $v_mang<br>";
					echo "v_surt: $v_surt<br>";
					
					$v_cantini = pg_result($v_xsql, $v_irow2, 2);
					$v_valoini = pg_result($v_xsql, $v_irow2, 3);
					$v_pini = pg_result($v_xsql, $v_irow2, 4);
					$v_cantfin = pg_result($v_xsql, $v_irow2, 5);
					$v_valofin = pg_result($v_xsql, $v_irow2, 6);
					$v_pfin = pg_result($v_xsql, $v_irow2, 7);
					$v_cantcon = $v_cantfin - $v_cantini;
					$v_impocon = $v_valofin - $v_valoini; //ESTO VEO QUE CUADRA PERFECTAMENTE CON comb_ta_contometro
					// $v_impocon = $v_cantcon * $v_pfin;
					echo "v_cantini: $v_cantini<br>";
					echo "v_valoini: $v_valoini<br>";
					echo "v_pini:    $v_pini<br>";
					echo "v_cantfin: $v_cantfin<br>";
					echo "v_valofin: $v_valofin<br>";
					echo "v_pfin:    $v_pfin<br>";
					echo "v_cantcon: $v_cantcon<br>";
					echo "v_impocon: $v_impocon<br>";
					
					//Obtenemos cantidades acumuladas
					$cantidad_acumulada += $v_cantcon;
					$importe_acumulado += $v_impocon;

					$v_xsql2 = pg_exec( "SELECT trim(prod".$v_mang.") FROM pos_cmblados WHERE lado = '".$v_lado."' " );
					$v_prod = pg_result($v_xsql2,0,0);
					echo "v_prod: $v_prod<br>";

					$v_xsql2 = pg_exec( "SELECT ch_nombrebreve, ch_codigocombustible FROM comb_ta_combustibles WHERE ch_codigocombex = '".$v_prod."' " );
					if ( pg_numrows($v_xsql2) > 0 ) {
						$v_prod = pg_result($v_xsql2, 0, 0);
						$v_codcom = pg_result($v_xsql2, 0, 1);
					} else {
						$v_prod = "No Prod"; 
						$v_codcom = "No Existe"; 
					}
					echo "v_prod: $v_prod<br>";
					echo "v_codcom: $v_codcom<br>";
					
					// aqui verifica si ya existe el lado y surt en la tabla
					if ( pg_numrows(pg_exec("SELECT tmpprec FROM tempo WHERE tmplado = '".$v_lado."' AND tmpsurt = '".$v_surt."'")) > 0 ) {
						
						// Si ya fue creado antes entonces el cini ya existe 
						// y no se considera actualizar el contometro inicial ni el precio inicial pero si el calculo
						// tmpcini=$v_cantini,tmpprei=$v_pini,
						
						$sql2 = "
						UPDATE
						 tempo 
						SET
						 tmppref = $v_pfin,
						 tmpcfin = $v_cantfin,
						 tmpcantcon = tmpcantcon + $v_cantcon,
						 tmpimpocon = tmpimpocon + $v_impocon 
						WHERE tmplado = '".$v_lado."' AND tmpsurt = '".$v_surt."'
						";

						//echo $sql2;

						$v_sql2 = pg_exec($sql2);
					} else {
						$v_sql2=pg_exec("
							INSERT INTO tempo  (
							 tmplado
							 , tmpsurt
							 , tmpprod
							 , tmpprei
							 , tmppref
							 , tmpcini
							 , tmpcfin
							 , tmpcantcon
							 , tmpimpocon
							 , tmpcanttik
							 , tmpimpotik
							 , tmp_codigo_combustible
							) VALUES (
							 '".$v_lado."'
							 , '".$v_surt."'
							 , '".$v_prod."'
							 , $v_pini
							 , $v_pfin
							 , $v_cantini
							 , $v_cantfin
							 , $v_cantcon
							 , $v_impocon
							 , 0
							 , 0
							 , '".$v_codcom."'
							)
						");
					}

					$v_irow2++;
				}
			}

			$v_irow++;

			echo "*******************************************<br>";
			echo "cantidad_acumulada: $cantidad_acumulada<br>";
			echo "importe_acumulado: $importe_acumulado<br>";
		}

		echo "*******************************************<br>";
		echo "cantidad_acumulada: $cantidad_acumulada<br>";
		echo "importe_acumulado: $importe_acumulado<br>";
		echo "</pre>";
	} // Fin del if de carga de contometros

// Aqui comienza la carga de las transacciones desde las tablas pos_transxxx

if ( $v_tipo == "AVANCE" ) {
	$v_sql = "SELECT trim(pump), trim(codigo), precio, cantidad, importe FROM pos_transtmp WHERE tipo = 'C'";
} else { //Calcula la fecha de los parametros del reporte
	$v_anomes = substr( $v_fecha_desde, 6, 4 ) . substr( $v_fecha_desde, 3, 2 );
	
	// echo $v_fecha_desde;
	// echo $v_anomes;
		
	$v_sql = "SELECT trim(pump), trim(codigo), precio, sum(case when importe>0 then cantidad else 0 end), sum(case when importe>0 then importe else 0 end) 
					from pos_trans".$v_anomes." 
					where tipo='C' and to_char(dia,'yyyy-mm-dd')||to_char(to_number(trim(turno),'99'),'99') 
							between '".$funcion->date_format($v_fecha_desde,'YYYY-MM-DD')."'||to_char(".$v_turno_desde.",'99') 
							and '".$funcion->date_format($v_fecha_hasta,'YYYY-MM-DD')."'||to_char(".$v_turno_hasta.",'99') group by pump,codigo,precio 
					";	

		}

        //echo $v_sql;

        $v_sql_pos_trans = pg_exec($v_sql);
        $v_lim = pg_numrows($v_sql_pos_trans);
        $v_row = 0;

        // $v_row=$v_lim;

        while ($v_row<$v_lim){
		$lado=pg_result($v_sql_pos_trans,$v_row,0);
		$surt=pg_result($v_sql_pos_trans,$v_row,1);
		$mang="00";
		
		$v_xsql2=pg_exec( "SELECT ch_nombrebreve from comb_ta_combustibles where ch_codigocombustible='".$surt."' " );
		$v_prod=pg_result($v_xsql2,0,0);
			
		$prec=pg_result($v_sql_pos_trans,$v_row,2);
		$canttik=pg_result($v_sql_pos_trans,$v_row,3);
		$impotik=pg_result($v_sql_pos_trans,$v_row,4);
		
		if (pg_numrows(pg_exec("SELECT tmpprec from tempo where tmplado='".$lado."' and tmp_codigo_combustible='".$surt."'"))>0)
			{
			$v_sql2=pg_exec("update tempo 
						set tmpprec=$prec, 
							tmpcanttik=tmpcanttik+$canttik,
							tmpimpotik=tmpimpotik+$impotik 
						where tmplado='".$lado."' and tmp_codigo_combustible='".$surt."'");
			}
		else
			{
			$v_sql2=pg_exec("insert into tempo (tmplado, tmpsurt, tmpprod, tmpprec, tmpcanttik, tmpimpotik, tmpcantcon, tmpimpocon, tmp_codigo_combustible )
							values ('".$lado."', '".$mang."', '".$v_prod."', $prec, $canttik, $impotik, 0, 0 , '".$surt."' ) ");
			}
					
		$v_row++;
		}
	$v_sql="select trim(tmplado), trim(tmpsurt) from tempo";
	$v_xsql=pg_exec($v_sql);
	$v_lim=pg_numrows($v_xsql);
	$v_row=0;
	
	while ($v_row<$v_lim){
		$lado=pg_result($v_xsql,$v_row,0);
		$surt=pg_result($v_xsql,$v_row,1);
		$v_sql2=pg_exec("update tempo 
						set tmpcantdif=tmpcantcon-tmpcanttik,
							tmpimpodif=tmpimpocon-tmpimpotik 
						where tmplado='".$lado."' and tmpsurt='".$surt."'");
		
		$v_row++;
		}
	
	$v_sqlprn="select trim(tmplado), trim(tmpsurt),tmpprod, tmpprec, tmpcanttik, tmpimpotik, tmpprei, tmppref, tmpcini, tmpcfin, tmpcantcon, tmpimpocon, tmpcantdif, tmpimpodif 
					from tempo order by tmplado, tmpsurt";
	$v_xsqlprn=pg_exec($v_sqlprn);
	$v_ilimit=pg_numrows($v_xsqlprn);

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
function activa(){
	// carga de frente el formulario con el foco en diad
	document.f_repo.v_fecha_desde.select()
	document.f_repo.v_fecha_desde.focus()
	}

</script> 

</head>

<!--- <body onfocus="mueveReloj('f_repo.reloj'); activa()"> 
 --->

<body>
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<form name="f_repo" method="post">

<div align="center"><font face="Arial, Helvetica, sans-serif">

REPORTE COMPARATIVO VENTA MANGUERAS Desde: <?php echo $v_fecha_desde; ?> Turno: <?php echo $v_turno_desde; ?> 
Hasta: <?php echo $v_fecha_hasta; ?> Turno: <?php echo $v_turno_hasta; ?> <BR>
<?php

$v_sql="select trim(ch_almacen), ch_nombre_almacen from inv_ta_almacenes  where ch_almacen like '%".trim($almacen)."%' and ch_clase_almacen='1' ";
$v_xsql=pg_query($conector_id,$v_sql);
if(pg_numrows($v_xsql)>0)	{	$v_descalma=pg_result($v_xsql,0,1);	}
?>

ALMACEN ACTUAL <?php echo $almacen;?> 	<?php echo $v_descalma; ?> 
<input type="text" name="reloj" size="10" style="background-color : Black; color : White; font-family : Verdana, Arial, Helvetica; font-size : 8pt; text-align : center;" onfocus="window.document.f_repo.reloj.blur()" > 
</div>

<hr noshade>


<?php 
if ( is_null($v_almacen) )
	{
	$v_almacen=$almacen;
	}
	
if (is_null($v_fecha_desde) or is_null($v_fecha_hasta) )
	{
	$v_fecha_desde=date("d/m/Y");
	$v_fecha_hasta=date("d/m/Y");
	$v_turno_desde=0;
	$v_turno_hasta=0;
	}
?>


<table border="1">
	<tr> 
		<th colspan="7">Reporte Por : RANGO DE FECHAS </th>
	</tr>
	<tr> 
		<th>DESDE :</th>
		<th>
		<p>
		<input type="text" name="v_fecha_desde" size="16" maxlength="10" value='<?php echo $v_fecha_desde ; ?>'  tabindex="1"  onKeyUp="javascript:validarFecha(this)" onblur="javascript:validarFecha(this)"  >
		<a href="javascript:show_calendar('f_repo.v_fecha_desde');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;" >
	    <img src="/sistemaweb/images/showcalendar.gif"  border=0></a>
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
		<th colspan="7"><input type="submit" name="boton" tabindex=5 value="Imprimir">
		</th>

	</tr>

        <tr>
        <td colspan=2>
        <a href="#" onClick="javascript:window.open('vta_manguera_texto.php?v_fecha_desde=<?php echo $v_fecha_desde;?>&v_fecha_hasta=<?php echo $v_fecha_hasta;?>&v_turno_desde=<?php echo $v_turno_desde;?>&v_turno_hasta=<?php echo $v_turno_hasta;?>','miwin','width=800,height=450,scrollbars=yes,menubar=yes,left=0,top=0');"> Impresion Texto </a>
        </td>

        <td colspan=2>
        <a href="#" onClick="javascript:window.open('vta_manguera_exportar.php?v_fecha_desde=<?php echo $v_fecha_desde;?>&v_fecha_hasta=<?php echo $v_fecha_hasta;?>&v_turno_desde=<?php echo $v_turno_desde;?>&v_turno_hasta=<?php echo $v_turno_hasta;?>','miwin','width=800,height=450,scrollbars=yes,menubar=yes,left=0,top=0');">Exportar </a>
        </td>
        </tr>
</table>




<br>

<?php
	echo "<table width='990' border='2' cellspacing=0 height='81'>";
	echo "<tr>";
	echo "<th colspan=18 align='center'><font size='3.5'> <BR>
	REPORTE COMPARATIVO VENTA MANGUERAS Desde: ".$v_fecha_desde." Turno: ".$v_turno_desde." 
	Hasta: ".$v_fecha_hasta." Turno: ".$v_turno_hasta." <P></P>	</th>";
	echo "</tr>";
	echo "<tr>";
	echo "	<th width='85'>Lado</td>";
	echo "	<th width='85' align='center'>Mang</td>";
	echo "	<th width='106' align='center'>Producto</td>";
	echo "	<th width='100' align='center'>Precio Tickets</td>";
	echo "	<th width='100' align='center'>Cantidad Tickets</td>";	
	echo "	<th width='100' align='center'>Importe Tickets</td>";	
	echo "	<th width='100' align='center'>Precio Inicial</td>";
	echo "	<th width='100' align='center'>Precio Final</td>";
	echo "	<th width='100' align='center'>Contometro Cant. Inicial</td>";
	echo "	<th width='100' align='center'>Contometro Cant. Final</td>";
	echo "	<th width='100' align='center'>Cantidad Contometro</td>";	
	echo "	<th width='100' align='center'>Importe Contometro</td>";	
	echo "	<th width='100' align='center'>Diferencia x Cantidad</td>";	
	echo "	<th width='100' align='center'>Diferencia x Soles</td>";
	
	//echo "	<td width='100'>Cont.S/.Inicial</td>";	
	//echo "	<td width='100'>Cont.S/.Final</td>";
	echo "</tr>";

	
	$v_clave=" ";
	
	$tcanttik=0;
	$timpotik=0;
	$tcantcon=0;
	$timpocon=0;			
	$tcantdif=0;
	$timpodif=0;			
	$stcanttik=0;
	$stimpotik=0;
	$stcantcon=0;
	$stimpocon=0;			
	$stcantdif=0;
	$stimpodif=0;			
	
	if($v_ilimit>0) 
		{
		$v_irow=0;
		while($v_irow<$v_ilimit)
			{
			$a0=pg_result($v_xsqlprn,$v_irow,0);
			$a1=pg_result($v_xsqlprn,$v_irow,1);
			$v_clave=$a0;
			while($v_irow<$v_ilimit and $v_clave==$a0 )
				{
				$a0=pg_result($v_xsqlprn,$v_irow,0);
				$a1=pg_result($v_xsqlprn,$v_irow,1);
				$a2=pg_result($v_xsqlprn,$v_irow,2);
				$a3=pg_result($v_xsqlprn,$v_irow,3);
				$a4=pg_result($v_xsqlprn,$v_irow,4);
				$a5=pg_result($v_xsqlprn,$v_irow,5);
				$a6=pg_result($v_xsqlprn,$v_irow,6);
				$a7=pg_result($v_xsqlprn,$v_irow,7);
				$a8=pg_result($v_xsqlprn,$v_irow,8);
				$a9=pg_result($v_xsqlprn,$v_irow,9);
				$a10=pg_result($v_xsqlprn,$v_irow,10);
				$a11=pg_result($v_xsqlprn,$v_irow,11);
				$a12=pg_result($v_xsqlprn,$v_irow,12);
				$a13=pg_result($v_xsqlprn,$v_irow,13);
				
				echo "<tr>";
				echo "<td align='left' >&nbsp;".$a0." </td>"; 
				echo "<td align='center' >&nbsp;".$a1." </td>"; 
				echo "<td align='left' >&nbsp;".$a2." </td>";
				echo "<td align='center' >&nbsp;".number_format($a3, 2, '.', '')." </td>";
				echo "<td align='right' >&nbsp;".number_format($a4, 2, '.', '')." </td>";
				echo "<td align='right' >&nbsp;".number_format($a5, 2, '.', '')." </td>";
				echo "<td align='center' >&nbsp;".number_format($a6, 2, '.', '')." </td>";
				echo "<td align='center' >&nbsp;".number_format($a7, 2, '.', '')." </td>";
				echo "<td align='right' >&nbsp;".number_format($a8, 2, '.', '')." </td>";
				echo "<td align='right' >&nbsp;".number_format($a9, 2, '.', '')." </td>";
				echo "<td align='right' >&nbsp;".number_format($a10, 2, '.', '')." </td>";
				echo "<td align='right' >&nbsp;".number_format($a11, 2, '.', '')." </td>";
				echo "<td align='center' >&nbsp;".number_format($a12, 2, '.', '')." </td>";
				echo "<td align='center' >&nbsp;".number_format($a13, 2, '.', '')." </td>";
				echo "</tr>";
				$stcanttik=$stcanttik+$a4;
				$stimpotik=$stimpotik+$a5;
				$stcantcon=$stcantcon+$a10;
				$stimpocon=$stimpocon+$a11;
				$stcantdif=$stcantdif+$a12;
				$stimpodif=$stimpodif+$a13;

				$tcanttik=$tcanttik+$a4;
				$timpotik=$timpotik+$a5;
				$tcantcon=$tcantcon+$a10;
				$timpocon=$timpocon+$a11;
				$tcantdif=$tcantdif+$a12;
				$timpodif=$timpodif+$a13;


				
				$v_irow++;
				if ($v_irow<$v_ilimit )
					{
					$a0=pg_result($v_xsqlprn,$v_irow,0);
					$a1=pg_result($v_xsqlprn,$v_irow,1);
					}
				}
			
			echo "<tr>";
			echo "<th align='left' >&nbsp;</td>"; 
			echo "<th align='center' >&nbsp;</td>"; 
			echo "<th align='left' >&nbsp;SUBTOTAL</td>";
			echo "<th align='center' >&nbsp;</td>";
			echo "<th align='right' >&nbsp;".number_format($stcanttik, 2, '.', '')." </td>";
			echo "<th align='right' >&nbsp;".number_format($stimpotik, 2, '.', '')." </td>";
			echo "<th align='center' >&nbsp;</td>";
			echo "<th align='center' >&nbsp;</td>";
			echo "<th align='right' >&nbsp;</td>";
			echo "<th align='right' >&nbsp;</td>";
			echo "<th align='right' >&nbsp;".number_format($stcantcon, 2, '.', '')." </td>";
			echo "<th align='right' >&nbsp;".number_format($stimpocon, 2, '.', '')." </td>";
			echo "<th align='center' >&nbsp;".number_format($stcantdif, 2, '.', '')." </td>";
			echo "<th align='center' >&nbsp;".number_format($stimpodif, 2, '.', '')." </td>";
			echo "</tr>";
			$stcanttik=0;
			$stimpotik=0;
			$stcantcon=0;
			$stimpocon=0;			
			$stcantdif=0;
			$stimpodif=0;			
			}
		echo "<tr>";
		echo "<th align='left' >&nbsp;</td>"; 
		echo "<th align='center' >&nbsp;</td>"; 
		echo "<th align='left' >&nbsp;TOTAL</td>";
		echo "<th align='center' >&nbsp;</td>";
		echo "<th align='right' >&nbsp;".number_format($tcanttik, 2, '.', '')." </td>";
		echo "<th align='right' >&nbsp;".number_format($timpotik, 2, '.', '')." </td>";
		echo "<th align='center' >&nbsp;</td>";
		echo "<th align='center' >&nbsp;</td>";
		echo "<th align='right' >&nbsp;</td>";
		echo "<th align='right' >&nbsp;</td>";
		echo "<th align='right' >&nbsp;".number_format($tcantcon, 2, '.', '')." </td>";
		echo "<th align='right' >&nbsp;".number_format($timpocon, 2, '.', '')." </td>";
		echo "<th align='center' >&nbsp;".number_format($tcantdif, 2, '.', '')." </td>";
		echo "<th align='center' >&nbsp;".number_format($timpodif, 2, '.', '')." </td>";
		echo "</tr>";
		$tcanttik=0;
		$timpotik=0;
		$tcantcon=0;
		$timpocon=0;			
		$tcantdif=0;
		$timpodif=0;			
		}
	echo "</table>";

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
