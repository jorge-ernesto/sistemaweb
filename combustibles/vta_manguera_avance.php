<?php
include("../functions.php");
include("../funcjch.php");
include("/sistemaweb/utils/funcion-texto.php");

require("../clases/funciones.php");
$funcion = new class_funciones;

// crea la clase para controlar errores
$clase_error = new OpensoftError;
$clase_error->_error();

// conectar con la base de datos
$conector_id=$funcion->conectar("","","","","");

$v_fecha_act='2005-01-01';
$v_turno_act=0;

$v_ilimit=0;

// Limpia la tabla del reporte
//limpia_tabla();
// Aqui carga los contometros del combex desde la
// fecha/turno inicio en una tabla temporal

// caso avance
//carga_contometros("A");

// caso dia y turno especifico
//carga_contometros("2004-04-30", "1");

$v_xsqlcont=pg_exec($conector_id,"truncate tempo" );

// si la fecha es igual que la que se pide entonces verificar el turno
// si la fecha no es igual asigna el turno actual al primero de la lista

// caso de avance= carga_postrans("A");
$v_sqlfunc="select combex_fn_reporte_avance('".$v_fecha_act."',".$v_turno_act.") ";

// echo $v_sqlfunc."<br>";
$v_xsql=pg_exec( $v_sqlfunc );
$v_xsql=pg_exec("select * from vista_reporte_combex_contometros");
$v_ilim2=pg_numrows($v_xsql);
if ($v_ilim2>0)
	{
	$v_irow2=0;
	while ($v_irow2<$v_ilim2)
		{
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

	// aqui comienza la carga de las transacciones desde las tablas pos_transxxx
	//calcula la fecha de los parametros del reporte
	$v_sql = "select trim(pump), trim(codigo), precio, sum(cantidad), sum(importe)
			from pos_transtmp
			where tipo='C'
			group by pump,codigo,precio
			";
	//echo $v_sql;

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
		else
			{
			//$v_sql2=pg_exec("insert into tempo (tmplado, tmpsurt, tmpprod, tmpprec, tmpcanttik, tmpimpotik, tmpcantcon, tmpimpocon, tmp_codigo_combustible )
			//				values ('".$lado."', '".$mang."', '".$v_prod."', $prec, $canttik, $impotik, 0, 0 , '".$surt."' ) ");
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

	
	
	
	
	
	

$col[0]=4;
$col[1]=4;
$col[2]=8;
$col[3]=7;
$col[4]=10;
$col[5]=12;
$col[6]=7;
$col[7]=7;
$col[8]=12;
$col[9]=12;
$col[10]=10;
$col[11]=12;
$col[12]=10;
$col[13]=10;


$nom[0]= "Lado";
$nom[1]= "Mang";
$nom[2]= "Producto";
$nom[3]= "PrecTks";
$nom[4]= "Cant Tckts";
$nom[5]= "Import Tckts";
$nom[6]= "PrecIni";
$nom[7]= "PrecFin";
$nom[8]= "ContomGalIni";
$nom[9]= "ContomGalFin";
$nom[10]= "CantContom";
$nom[11]= "ImportContom";
$nom[12]= "DifxCantid";
$nom[13]= "DifxSoles ";



$cabecera="<table>";
$cabecera=$cabecera."<tr>";
$cabecera=$cabecera."<td><BR>REPORTE COMPARATIVO VENTA MANGUERAS AVANCE </td>";
$cabecera=$cabecera."</tr>";

$cab_date=date ("d/m/Y - H:i:s", time());
$cabecera=$cabecera."<tr>";
$cabecera=$cabecera."<td>FECHA:".$cab_date."</td>";
$cabecera=$cabecera."</tr>";

$cabecera=$cabecera. "<tr>";
$cabecera=$cabecera. "<td>Lado</td>";
$cabecera=$cabecera. "<td>Mang</td>";
$cabecera=$cabecera. "<td>Producto</td>";
$cabecera=$cabecera. "<td>PrecTks</td>";
$cabecera=$cabecera. "<td>Cant Tckts</td>";
$cabecera=$cabecera. "<td>Import Tckts</td>";
$cabecera=$cabecera. "<td>PrecIni</td>";
$cabecera=$cabecera. "<td>PrecFin</td>";
$cabecera=$cabecera. "<td>ContomGalIni</td>";
$cabecera=$cabecera. "<td>ContomGalFin</td>";
$cabecera=$cabecera. "<td>CantContom</td>";
$cabecera=$cabecera. "<td>ImportContom</td>";
$cabecera=$cabecera. "<td>DifxCantid</td>";
$cabecera=$cabecera. "<td>DifxSoles </td>";

//echo "	<td width='100'>Cont.S/.Inicial</td>";	
//echo "	<td width='100'>Cont.S/.Final</td>";
$cabecera=$cabecera. "</tr>";

$linea="";
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

			$a2 = substr($a2,0,8);


			$linea=$linea. "<tr>";
			$linea=$linea. "<td>".str_pad( $a0 ,$col[0] )."</td>"; 
			$linea=$linea. "<td>".str_pad( $a1 ,$col[1] )."</td>";
			$linea=$linea. "<td>".str_pad( $a2 ,$col[2] )."</td>";
			$linea=$linea. "<td>".str_pad( number_format($a3, 2, '.', ''), $col[3], " ", STR_PAD_LEFT )."</td>";
			$linea=$linea. "<td>".str_pad( number_format($a4, 2, '.', ''), $col[4], " ", STR_PAD_LEFT )."</td>";
			$linea=$linea. "<td>".str_pad( number_format($a5, 2, '.', ''), $col[5], " ", STR_PAD_LEFT )."</td>";
			$linea=$linea. "<td>".str_pad( number_format($a6, 2, '.', ''), $col[6], " ", STR_PAD_LEFT )."</td>";
			$linea=$linea. "<td>".str_pad( number_format($a7, 2, '.', ''), $col[7], " ", STR_PAD_LEFT )."</td>";
			$linea=$linea. "<td>".str_pad( number_format($a8, 2, '.', ''), $col[8], " ", STR_PAD_LEFT )."</td>";
			$linea=$linea. "<td>".str_pad( number_format($a9, 2, '.', ''), $col[9], " ", STR_PAD_LEFT )."</td>";
			$linea=$linea. "<td>".str_pad( number_format($a10, 2, '.', ''), $col[10], " ", STR_PAD_LEFT )."</td>";
			$linea=$linea. "<td>".str_pad( number_format($a11, 2, '.', ''), $col[11], " ", STR_PAD_LEFT )."</td>";
			$linea=$linea. "<td>".str_pad( number_format($a12, 2, '.', ''), $col[12], " ", STR_PAD_LEFT )."</td>";
			$linea=$linea. "<td>".str_pad( number_format($a13, 2, '.', ''), $col[13], " ", STR_PAD_LEFT )."</td>";
			$linea=$linea. "</tr>";
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

		$linea=$linea. "<tr>";
		$linea=$linea. "<td>".str_pad( " " ,$col[0] )."</td>"; 
		$linea=$linea. "<td>".str_pad( " " ,$col[1] )."</td>";
		$linea=$linea. "<td>".str_pad( "SUBTOTAL" ,$col[2] )."</td>";
		$linea=$linea. "<td>".str_pad( " " ,$col[3] )."</td>";
		$linea=$linea. "<td>".str_pad(  trim(number_format($stcanttik, 2, '.', '')), $col[4], " ", STR_PAD_LEFT )."</td>";
		$linea=$linea. "<td>".str_pad(  trim(number_format($stimpotik, 2, '.', '')), $col[5], " ", STR_PAD_LEFT )."</td>";
		$linea=$linea. "<td>".str_pad( " " ,$col[6] )."</td>";
		$linea=$linea. "<td>".str_pad( " " ,$col[7] )."</td>";
		$linea=$linea. "<td>".str_pad( " " ,$col[8] )."</td>";
		$linea=$linea. "<td>".str_pad( " " ,$col[9] )."</td>";
		$linea=$linea. "<td>".str_pad( trim(number_format($stcantcon, 2, '.', '') ), $col[10], " ", STR_PAD_LEFT ) ."</td>";
		$linea=$linea. "<td>".str_pad( trim(number_format($stimpocon, 2, '.', '') ), $col[11], " ", STR_PAD_LEFT ) ."</td>";
		$linea=$linea. "<td>".str_pad( trim(number_format($stcantdif, 2, '.', '') ), $col[12], " ", STR_PAD_LEFT ) ."</td>";
		$linea=$linea. "<td>".str_pad( trim(number_format($stimpodif, 2, '.', '') ), $col[13], " ", STR_PAD_LEFT ) ."</td>";
		$linea=$linea. "</tr>";
		$stcanttik=0;
		$stimpotik=0;
		$stcantcon=0;
		$stimpocon=0;			
		$stcantdif=0;
		$stimpodif=0;			
		}
	$linea=$linea. "<tr>";
		$linea=$linea. "<td>".str_pad( " " ,$col[0] )."</td>"; 
		$linea=$linea. "<td>".str_pad( " " ,$col[1] )."</td>"; 
		$linea=$linea. "<td>".str_pad( "TOTAL" ,$col[2] )."</td>";
		$linea=$linea. "<td>".str_pad( " " ,$col[3] )."</td>";
		$linea=$linea. "<td>".str_pad( trim(number_format($tcanttik, 2, '.', '')), $col[4], " ", STR_PAD_LEFT )."</td>";
		$linea=$linea. "<td>".str_pad( trim(number_format($timpotik, 2, '.', '')), $col[5], " ", STR_PAD_LEFT )."</td>";
		$linea=$linea. "<td>".str_pad( " " ,$col[6] )."</td>";
		$linea=$linea. "<td>".str_pad( " " ,$col[7] )."</td>";
		$linea=$linea. "<td>".str_pad( " " ,$col[8] )."</td>";
		$linea=$linea. "<td>".str_pad( " " ,$col[9] )."</td>";
		$linea=$linea. "<td>".str_pad( trim(number_format($tcantcon, 2, '.', '')), $col[10], " ", STR_PAD_LEFT ) ."</td>";
		$linea=$linea. "<td>".str_pad( trim(number_format($timpocon, 2, '.', '')), $col[11], " ", STR_PAD_LEFT ) ."</td>";
		$linea=$linea. "<td>".str_pad( trim(number_format($tcantdif, 2, '.', '')), $col[12], " ", STR_PAD_LEFT ) ."</td>";
		$linea=$linea. "<td>".str_pad( trim(number_format($timpodif, 2, '.', '')), $col[13], " ", STR_PAD_LEFT ) ."</td>";
	$linea=$linea. "</tr>";


	$tcanttik=0;
	$timpotik=0;
	$tcantcon=0;
	$timpocon=0;
	$tcantdif=0;
	$timpodif=0;			
	}
$linea=$linea. "</table>";

$v_sqlprn ="select par_valor from int_parametros where trim(par_nombre)='print_netbios' ";
$v_xsqlprn=pg_exec($v_sqlprn);
$v_server =pg_result($v_xsqlprn,0,0);

$v_sqlprn ="select par_valor from int_parametros where trim(par_nombre)='print_name' ";
$v_xsqlprn=pg_exec($v_sqlprn);
$v_printer=pg_result($v_xsqlprn,0,0);

$v_sqlprn ="select par_valor from int_parametros where trim(par_nombre)='print_server' ";
$v_xsqlprn=pg_exec($v_sqlprn);
$v_ipprint=pg_result($v_xsqlprn,0,0);


imprimir2( $cabecera.$linea, $col, $nom, "/tmp/imprimir/vta_manguera_avance.txt", "Venta Manguera" );

echo "EJECUTADO imprimir2";
// exec("smbclient //server14nw/epson -c 'print /tmp/imprimir/vta_manguera.txt' -N -I 192.168.1.1 ");
exec("smbclient //".$v_server."/".$v_printer." -c 'print /tmp/imprimir/vta_manguera_avance.txt' -N -I ".$v_ipprint." ");
echo "envio impresora";

// comprueba si la conexion existe y la cierra
if ($conector_id) pg_close($conector_id);
// restaura el control de errores original
$clase_error->_error();
