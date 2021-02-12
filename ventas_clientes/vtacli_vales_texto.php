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

$v_ilimit=0;
$v_sqlprn ="select * from vtacli_vales_reporte ";
$v_xsqlprn=pg_exec($v_sqlprn);
$v_ilimit =pg_numrows($v_xsqlprn);
echo $v_ilimit;


?>
<html>
<head>
<title>SISTEMAWEB</title>
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
- <?php echo $nombre_almacen;?>
</div>
EJECUTA
<?php
        $col[0]=10;
        $col[1]=12;
        $col[2]=45;
        $col[3]=6;
		$col[4]=10;
		$col[5]=12;
        $col[6]=20;
        $col[7]=12;

        $nom[0]=str_pad( "Fechah" ,$col[0] );
        $nom[1]=str_pad( "N.Desp" ,$col[1] );
        $nom[2]=str_pad( "Articulo" ,$col[2] );
        $nom[3]=str_pad( "Trabajado" ,$col[3] );
        $nom[4]=str_pad( "Cantidad" ,$col[4] );
        $nom[5]=str_pad( "Importe " ,$col[5] );
        $nom[6]=str_pad( "Numeros Vales" ,$col[6] );
        $nom[7]=str_pad( "Total" ,$col[7] );


	$cabecera="<table>";


		/* AGREGADO POR FRED PARA QUE APARESCA LA ESTACION EN EL REPORTE.. */
		$cabecera=$cabecera."<tr>";
		$sql_almacen = "select trim(ch_nombre_almacen) from inv_ta_almacenes where trim(ch_almacen)='".trim($almacen)."'";
		$cabecera=$cabecera."<td>     ".$almacen." - ".pg_result(pg_query($conector_id, $sql_almacen),0,0)." </td>";
		$cabecera=$cabecera."</tr>";
		/* AGREGADO POR FRED PARA QUE APARESCA LA ESTACION EN EL REPORTE.. */


	$cabecera=$cabecera."<tr>";
        $cabecera=$cabecera."<td> DETALLE DE CONSUMO AL CREDITO Desde: ".$v_fecha_desde." Hasta: ".$v_fecha_hasta." </td>";
	$cabecera=$cabecera."</tr>";

	$cabecera=$cabecera. "<tr>";
        $cabecera=$cabecera. "<td>".str_pad( "Fecha" ,$col[0] )."</td>";
        $cabecera=$cabecera. "<td>".str_pad( "N.Desp" ,$col[1] )."</td>";
        $cabecera=$cabecera. "<td>".str_pad( "Articulo" ,$col[2] )."</td>";
        $cabecera=$cabecera. "<td>".str_pad( "Trabaj" ,$col[3] )."</td>";
        $cabecera=$cabecera. "<td>".str_pad( "Cantidad" ,$col[4] )."</td>";
        $cabecera=$cabecera. "<td>".str_pad( "Importe " ,$col[5] )."</td>";
        $cabecera=$cabecera. "<td>".str_pad( "Numeros Vales" ,$col[6] )."</td>";
        $cabecera=$cabecera. "<td>".str_pad( "Total" ,$col[7] )."</td>";
	$cabecera=$cabecera. "</tr>";


	$v_irow=0;
	$v_tot_cli_cant_doc=0;
	$v_tot_cli_impo=0;
	$v_cli_cant_doc=0;
	$v_cli_impo=0;

	$v_antes=" ";
	$linea="";
	$v_clave=" ";

	if($v_ilimit>0)
		{
                $v_irow=0;
		while($v_irow<$v_ilimit)
			{
			$a0=pg_result($v_xsqlprn,$v_irow,0);
			$a1=pg_result($v_xsqlprn,$v_irow,1);
			$a2=pg_result($v_xsqlprn,$v_irow,2);
			$a3=pg_result($v_xsqlprn,$v_irow,3);

                        $linea=$linea."<tr>";
                        $linea=$linea."<td>CLIENTE: ".$a0." - ".$a1." Placa:".$a2." Odometro:".$a3."</td>";
                        $linea=$linea."</tr>";
			$v_antes=$a0.$a2;

			while ($v_irow<$v_ilimit and $v_antes==$a0.$a2)
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


				
				$linea=$linea. "<tr>";
                                $linea=$linea. "<td>".str_pad( $a4 ,$col[0] )."</td>"; 
                                $linea=$linea. "<td>".str_pad( $a5 ,$col[1] )."</td>"; 
                                $linea=$linea. "<td>".str_pad( substr(trim($a6)." - ".trim($a7),0,$col[2]-2) ,$col[2] )."</td>";
                                $linea=$linea. "<td>".str_pad( "*" ,$col[3] )."</td>"; 
                                $linea=$linea. "<td>".str_pad( number_format($a8, 2, '.', ''), $col[4], " ", STR_PAD_LEFT )."</td>";
                                $linea=$linea. "<td>".str_pad( number_format($a9, 2, '.', ''), $col[5], " ", STR_PAD_LEFT )."</td>";

                                $linea=$linea. "<td>";

				/***   CODIGO AGREGADO POR FRED PARA QUE APARESCA EL NRO DE VALE Y SU IMPORTE ***/
				$sql_vales  = "select ch_numeval, nu_importe from val_ta_complemento
                                                WHERE trim(ch_sucursal)||trim(dt_fecha)||trim(ch_documento) ='".trim($almacen).trim($a4).trim($a5)."'";

				$nro_vales = pg_query($conector_id, $sql_vales);
				$count = 0;
				if(pg_num_rows($nro_vales)>0)
				{
					while($count<pg_num_rows($nro_vales))
					{

                                                $linea=$linea. str_pad( pg_result($nro_vales,$count,0)."-".pg_result($nro_vales,$count,1), $col[6] );
                                                $count++;
                                                if ( $count<pg_num_rows($nro_vales) )
                                                {
                                                        $linea=$linea."</tr>";
                                                        $linea=$linea."<tr>".str_pad( " " ,$col[0]+$col[1]+$col[2]+$col[3]+$col[4]+$col[5]+7 );
                                                }


					}
				}
				/***   CODIGO AGREGADO POR FRED PARA QUE APARESCA EL NRO DE VALE Y SU IMPORTE ***/

                                $linea=$linea."</td>";


                                $linea=$linea. "<td>".str_pad( number_format($a10, 2, '.', ''), $col[7], " ", STR_PAD_LEFT )."</td>";
				$linea=$linea. "</tr>";





				$v_cli_cant_doc++;
				$v_cli_impo=$v_cli_impo+$a9;
				$v_irow++;
				//antes de regresar al bucle tiene que comprobar el dato
				if ($v_irow<$v_ilimit )
				{
					$a0=pg_result($v_xsqlprn,$v_irow,0);
					$a2=pg_result($v_xsqlprn,$v_irow,2);
				}
			}


			$linea=$linea. "<tr>";
			$linea=$linea. "<td>".str_pad( " " ,$col[0] )."</td>"; 
                        $linea=$linea. "<td>".str_pad( "TOTAL CLIENTE :" ,$col[1]+$col[2]+1 )."</td>";
                        $linea=$linea. "<td>".str_pad( "Doc:".$v_cli_cant_doc ,$col[3] )."</td>";
                        $linea=$linea. "<td>".str_pad( " " ,$col[4] )."</td>";
                        $linea=$linea. "<td>".str_pad( " " ,$col[5] )."</td>"; 
                        $linea=$linea. "<td>".str_pad( ">-->", $col[6] )."</td>";
                        $linea=$linea. "<td>".str_pad(  trim(number_format($v_cli_impo, 2, '.', '')), $col[7], " ", STR_PAD_LEFT )."</td>";
			$linea=$linea. "</tr>";

			$v_tot_cli_cant_doc=$v_tot_cli_cant_doc+$v_cli_cant_doc;
                        $v_tot_cli_impo    =$v_tot_cli_impo+$v_cli_impo;
                        $v_cli_cant_doc    =0;
                        $v_cli_impo        =0;

			}

		$linea=$linea. "<tr>";
                $linea=$linea. "<td>".str_pad( " " ,$col[0] )."</td>"; 
                $linea=$linea. "<td>".str_pad( "TOTAL GENERAL :" , $col[1]+$col[2]+1 )."</td>";
                $linea=$linea. "<td>".str_pad( "Doc:".$v_tot_cli_cant_doc ,$col[3] )."</td>";
                $linea=$linea. "<td>".str_pad( " " ,$col[4] )."</td>";
                $linea=$linea. "<td>".str_pad( " " ,$col[5] )."</td>"; 
                $linea=$linea. "<td>".str_pad( ">=====>", $col[6] )."</td>";
                $linea=$linea. "<td>".str_pad(  trim(number_format($v_tot_cli_impo, 2, '.', '')), $col[7], " ", STR_PAD_LEFT )."</td>";
		$linea=$linea. "</tr>";
		
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


        imprimir2( $cabecera.$linea, $col, $nom, "/tmp/imprimir/vtacli_vales.txt", "Venta VALES" );

	echo "EJECUTADO imprimir2";
        // exec("smbclient //server14nw/epson -c 'print /tmp/imprimir/vta_manguera.txt' -P -N -I 192.168.1.1 ");
        exec("smbclient //".$v_server."/".$v_printer." -c 'print /tmp/imprimir/vtacli_vales.txt' -N -I ".$v_ipprint." ");
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
