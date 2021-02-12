<?php/*
include("../valida_sess.php");
include("../menu_princ.php");
include("../functions.php");
include("../funcjch.php");


require("../clases/funciones.php");
$funcion = new class_funciones;

// crea la clase para controlar errores
$clase_error = new OpensoftError;
$clase_error->_error();

// conectar con la base de datos
$conector_id=$funcion->conectar("","","","","");



if ( is_null($almacen) or trim($almacen)=="")
	{
	$almacen="001";
	}


// Busqueda de Almacenes

$sql1="select trim(ch_almacen) as cod,ch_nombre_almacen from inv_ta_almacenes  where ch_clase_almacen='1' order by cod";

$v_xsqlalma=pg_exec($conector_id,$sql1);





if (is_null($v_fecha_desde) or is_null($v_fecha_hasta) )
	{
	$v_fecha_desde=date("d/m/Y");
	$v_fecha_hasta=date("d/m/Y");
	$v_turno_desde=0;
	$v_turno_hasta=0;
	}
$v_ilimit=0;





if ($boton=='Imprimir')
	{
	// Limpia la tabla del reporte
	//limpia_tabla();
	// Aqui carga los contometros del combex desde la
	// fecha/turno inicio en una tabla temporal

	// caso avance
	//carga_contometros("A");
	
	// caso dia y turno especifico
	//carga_contometros("2004-04-30", "1");


	$v_xsqlcont=pg_exec($conector_id,"truncate tempo" );


$funcion->date_format($v_fecha_desde,)

$tabla="trans";


*/
/*

	$v_sqlcont="select documento, ch_nombre1, ch_apellido_paterno from "$tabla"
				where to_char(dia,'yyyy-mm-dd')||to_char(turno,'99')
				between '".$funcion->date_format($v_fecha_desde,'YYYY-MM-DD')."'||to_char(".$v_turno_desde.",'99')
				and '".$funcion->date_format($v_fecha_hasta,'YYYY-MM-DD')."'||to_char(".$v_turno_hasta.",'99') 
				group by dia, turno ";
				
	$v_xsqlcont=pg_exec($conector_id,$v_sqlcont);
	$v_ilimit=pg_numrows($v_xsqlcont);
	//if (false)

	if ($v_ilimit>0)
		{
		$v_irow=0;
		while ($v_irow<$v_ilimit){

			$v_fecha_act=pg_result($v_xsqlcont,$v_irow,0);
			$v_turno_act=pg_result($v_xsqlcont,$v_irow,1);

			// si la fecha es igual que la que se pide entonces verificar el turno
			// si la fecha no es igual asigna el turno actual al primero de la lista

			// caso de avance= carga_postrans("A");
			$v_sqlfunc="select combex_fn_reporte_contometros('".$funcion->date_format($v_fecha_act,'YYYY-MM-DD')."',".$v_turno_act.") ";
			// echo $v_sqlfunc."<br>";
			$v_xsql=pg_exec( $v_sqlfunc );
			$v_xsql=pg_exec("select * from vista_reporte_combex_contometros");
			$v_ilim2=pg_numrows($v_xsql);
			if ($v_ilim2>0) {
				$v_irow2=0;
				while ($v_irow2<$v_ilim2){
					$v_lado=trim(pg_result($v_xsql,$v_irow2,0));
					if (strlen($v_lado)>1){
						$v_lado=$v_lado;
						}
					else {
						$v_lado="0".$v_lado;
						}
					$v_mang=trim(pg_result($v_xsql,$v_irow2,1));
					if (strlen($v_mang)>1){
						$v_mang=substr($v_mang,1,1);
						$v_surt=$v_mang;
						}
					else {
						$v_surt="0".$v_mang;
						}
*/					
/*					
					$v_cantini=pg_result($v_xsql,$v_irow2,2);
					$v_valoini=pg_result($v_xsql,$v_irow2,3);
					$v_pini=pg_result($v_xsql,$v_irow2,4);
					$v_cantfin=pg_result($v_xsql,$v_irow2,5);
					$v_valofin=pg_result($v_xsql,$v_irow2,6);
					$v_pfin=pg_result($v_xsql,$v_irow2,7);
					$v_cantcon=$v_cantfin-$v_cantini;
					// $v_impocon=$v_valofin-$v_valoini;
					$v_impocon=$v_cantcon*$v_pfin;

					$v_xsql2=pg_exec( "select trim(prod".$v_mang.") from pos_cmblados where lado='".$v_lado."' " );
					$v_prod=pg_result($v_xsql2,0,0);
					$v_xsql2=pg_exec( "select ch_nombrebreve, ch_codigocombustible  from comb_ta_combustibles where ch_codigocombex='".$v_prod."' " );
					if (pg_numrows($v_xsql2)>0)
						{
						$v_prod=pg_result($v_xsql2,0,0);
						$v_codcom=pg_result($v_xsql2,0,1);
						}
					else
						{
						$v_prod="No Prod"; 
						$v_codcom="No Existe"; 
						}
					
					// aqui verifica si ya existe el lado y surt en la tabla
					if (pg_numrows(pg_exec("select tmpprec from tempo where tmplado='".$v_lado."' and tmpsurt='".$v_surt."'"))>0)
						{
						// si ya fue creado antes entonces el cini ya existe 
						// y no se considera actualizar el contometro inicial ni el precio inicial pero si el calculo
						// tmpcini=$v_cantini,tmpprei=$v_pini,
						
						$sql2="update tempo
							set tmppref=$v_pfin,
										tmpcfin=$v_cantfin,
										tmpcantcon=tmpcantcon+$v_cantcon,
										tmpimpocon=tmpimpocon+$v_impocon 
							where tmplado='".$v_lado."' and tmpsurt='".$v_surt."' ";
						//echo $sql2;
						$v_sql2=pg_exec($sql2);
						}
					else
						{
						$v_sql2=pg_exec("insert into tempo
											( tmplado, tmpsurt, tmpprod, tmpprei, tmppref, tmpcini, tmpcfin, tmpcantcon, tmpimpocon, tmpcanttik, tmpimpotik, tmp_codigo_combustible )
										values 
											( '".$v_lado."', '".$v_surt."', '".$v_prod."', $v_pini, $v_pfin, $v_cantini, $v_cantfin, $v_cantcon, $v_impocon, 0, 0 , '".$v_codcom."' ) ");
						}


					$v_irow2++;
					}
				}
			$v_irow++;
			}
		} // fin del if de carga de contometros
*/	
/*



	// aqui comienza la carga de las transacciones desde las tablas pos_transxxx
	if ($v_tipo=="AVANCE"){
		$v_sql="select trim(pump), trim(codigo), precio, cantidad, importe from pos_transtmp where tipo='C'";
		}
	else{
		//calcula la fecha de los parametros del reporte
		$v_anomes = substr( $v_fecha_desde, 6, 4 ) . substr( $v_fecha_desde, 3, 2 );
                // echo $v_fecha_desde;
                // echo $v_anomes;
	
		$v_sql = "select trim(pump), trim(codigo), precio, sum(cantidad), sum(importe) 
					from pos_trans".$v_anomes." 
					where tipo='C' and to_char(dia,'yyyy-mm-dd')||to_char(to_number(trim(turno),'99'),'99') 
							between '".$funcion->date_format($v_fecha_desde,'YYYY-MM-DD')."'||to_char(".$v_turno_desde.",'99') 
							and '".$funcion->date_format($v_fecha_hasta,'YYYY-MM-DD')."'||to_char(".$v_turno_hasta.",'99') group by pump,codigo,precio 
					";
                //echo $v_sql;
		}
	
	$v_sql_pos_trans=pg_exec($v_sql);
	$v_lim=pg_numrows($v_sql_pos_trans);
	$v_row=0;
	
	// $v_row=$v_lim;
	while ($v_row<$v_lim){
		$lado=pg_result($v_sql_pos_trans,$v_row,0);
		$surt=pg_result($v_sql_pos_trans,$v_row,1);
		$mang="00";
		
		$v_xsql2=pg_exec( "select ch_nombrebreve from comb_ta_combustibles where ch_codigocombustible='".$surt."' " );
		$v_prod=pg_result($v_xsql2,0,0);
			
		$prec=pg_result($v_sql_pos_trans,$v_row,2);
		$canttik=pg_result($v_sql_pos_trans,$v_row,3);
		$impotik=pg_result($v_sql_pos_trans,$v_row,4);
		
		if (pg_numrows(pg_exec("select tmpprec from tempo where tmplado='".$lado."' and tmp_codigo_combustible='".$surt."'"))>0)
			{
			$v_sql2=pg_exec("update tempo 
						set tmpprec=$prec, 
							tmpcanttik=tmpcanttik+$canttik,
							tmpimpotik=tmpimpotik+$impotik 
						where tmplado='".$lado."' and tmp_codigo_combustible='".$surt."'");
			}
*/
/*		else
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
*/
?>
<html><link rel="stylesheet" href="<?php echo $v_path_url; ?>acosa.css" type="text/css">
<head>
<title>SISTEMAWEB</title>
<script language="JavaScript" src="js/miguel.js"></script>
<script language="JavaScript" src="/sistemaweb/clases/calendario.js"></script>
<script language="JavaScript" src="/sistemaweb/clases/overlib_mini.js"></script>
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

<!--- <body onfocus="mueveReloj('f_repo.reloj'); activa()">  --->

<body>
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<form name="f_repo" method="post">

<div align="center"><font face="Arial, Helvetica, sans-serif">

REPORTE DE TICKETS DE VENTA POR TRABAJADOR  Desde: <?php echo $v_fecha_desde; ?> Turno: <?php echo $v_turno_desde; ?> 
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
		<th colspan="4">Reporte Por : RANGO DE FECHAS </th>
	</tr>



	<tr>
	     <th colspan="4">Almacen :
             <select name="cod_almacen">
             <?php
             if($cod_almacen!=""){ print "<option value='$sucursal_val' selected>$sucursal_val -- $sucursal_dis</option>";}

                        for($i=0;$i<pg_numrows($rs1);$i++){
                                $B = pg_fetch_row($rs1,$i);
                           print "<option value='$B[0]' >$B[0] -- $B[1]</option>";
                        }
              ?>
              </select>
	      </th> 

	</tr>

	<tr>
		<th> AÃ± :</th>
		<th>
		§<input type="text"name=±anio" size="16" maxlength="10" value='<?php echo $anio; ?>'>
		</th>
	</tr>
	
	<tr>
		<th> Mes :</th>
		<th>
		§<input type="text" name"mes±" size="16" maxlength="10" value='<?php echo $mes; ?>'>
		</th>
	</tr>

	<tr>	
		<th>Dia :</th>
		<th>
		§<input type="text" namediad±" size="16" maxlength="10" value='<?php echo $diad; ?>'>
		</th>

		<th>
		§<input type="text" namediaa±" size="16" maxlength="10" value='<?php echo $diaa; ?>'>
		</th>

	</tr>

	<tr>
		<th colspan="4"><input type="submit" name="boton" tabindex=5 value="Imprimir"></th>
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
	REPORTE DE TICKETS DE VENTA POR TRABAJADOR Desde: ".$v_fecha_desde."
	Hasta: ".$v_fecha_hasta." <P></P>	</th>";
	echo "</tr>";
	echo "<tr>";
	echo "	<th width='85'>Cod. Trabaj.</td>";
	echo "	<th width='85'>Trabajador</td>";
	echo "	<th width='106' align='center'>Tipo de Doc.</td>";
	echo "	<th width='100' align='center'>Ticket</td>";
	echo "	<th width='100' align='center'>Cantidad Tickets</td>";
	echo "	<th width='100' align='center'>Turno</td>";
	echo "	<th width='100' align='center'>Hora</td>";
	echo "	<th width='100' align='center'>RUCl</td>";
	echo "	<th width='100' align='center'>Codigo</td>";
	echo "	<th width='100' align='center'>Descripcion</td>";
	echo "	<th width='100' align='center'>Cantidad</td>";
	echo "	<th width='100' align='center'>Precio</td>";
	echo "	<th width='100' align='center'> Firma -</td>";
	
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
