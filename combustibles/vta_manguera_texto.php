<?php
include("../valida_sess.php");
include("../functions.php");
 include("/sistemaweb/utils/funcion-texto.php");

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


// carga los almacenes en un dropdown 
$v_xsqlalma=pg_exec("select trim(ch_almacen) as cod,ch_nombre_almacen from inv_ta_almacenes  where ch_clase_almacen='1' and ch_almacen='".$almacen."' order by cod");
$nombre_almacen=pg_result($v_xsqlalma,0,1);

if (is_null($v_fecha_desde) or is_null($v_fecha_hasta) )
	{
	$v_fecha_desde=date("d/m/Y");
	$v_fecha_hasta=date("d/m/Y");
	$v_turno_desde=0;
	$v_turno_hasta=0;
	}
$v_ilimit=0;


	$v_sqlprn="select trim(tmplado), trim(tmpsurt),tmpprod, tmpprec, tmpcanttik, tmpimpotik, tmpprei, tmppref, tmpcini, tmpcfin, tmpcantcon, tmpimpocon, tmpcantdif, tmpimpodif 
					from tempo order by tmplado, tmpsurt";
	$v_xsqlprn=pg_exec($v_sqlprn);
	$v_ilimit=pg_numrows($v_xsqlprn);



?>
<html>
<head>
<title>sistemaweb</title>
<title>sistemaweb</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<form method='post' name="form2">
  <table width="767" border="1" cellpadding="0" cellspacing="0">
    <tr> 
      <td><a href="#" onClick="javascript:window.print();">Imprimir</a> </td>
    </tr>
  </table>
</form>
<div align="left"><font face="Arial, Helvetica, sans-serif">
SISTEMA INTEGRADO - <?php echo $nombre_almacen;?>
</div>
EJECUTA
<?php
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
	$cabecera=$cabecera."<td><BR>REPORTE COMPARATIVO VENTA MANGUERAS Desde: ".$v_fecha_desde." Turno: ".$v_turno_desde." Hasta: ".$v_fecha_hasta." Turno: ".$v_turno_hasta." </td>";
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


	imprimir2( $cabecera.$linea, $col, $nom, "/tmp/imprimir/vta_manguera.txt", "Venta Manguera" );

	echo "EJECUTADO imprimir2";
        // exec("smbclient //server14nw/epson -c 'print /tmp/imprimir/vta_manguera.txt' -N -I 192.168.1.1 ");
        exec("smbclient //".$v_server."/".$v_printer." -c 'print /tmp/imprimir/vta_manguera.txt' -N -I ".$v_ipprint." ");
	echo "envio impresora";
?>
ejecutado imprimir
<br>
<br>

<script>window.close();</script>
</form>
</body>
</html>
<?php 


// comprueba si la conexion existe y la cierra

if ($conector_id) pg_close($conector_id);
// restaura el control de errores original
$clase_error->_error();
