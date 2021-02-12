<?php
include("../valida_sess.php");
include("../menu_princ.php");
include("../functions.php");
include("/sistemaweb/utils/funcion-texto.php");
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


// carga los almacenes en un dropdown 
$sql_alma=pg_exec("select trim(ch_almacen) as cod,ch_nombre_almacen from inv_ta_almacenes  where ch_clase_almacen='1' order by cod");

        $result_alma=pg_exec($conector,$sql_alma);

        $n_f_alma=pg_numrows($result_alma);


//formato de fecha ingresada

if (is_null($_fecha_desde) or is_null($_fecha_hasta) )
	{
	$_fecha_desde=date("d/m/Y");
	$_fecha_hasta=date("d/m/Y");
	}


	$_fecha_desde=$diad1."/".$mes1."/".$ano1;
	$_fecha_hasta=$diaa1."/".$mes1."/".$ano1;
        

	//fecha para asignar tabla

	$f=trim($ano1).trim($mes1);
	
	$tabla="pos_trans".$f;

	if ($boton=='Imprimir')
        
	{


// valores para imprimir

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

        $v_archivo="/tmp/imprimir/venta_x_trabajador.txt";


	
		//evaluando documento --> codigo de trabajador

                $sql1="select distinct documento from ".$tabla." where documento is not null";
                $result=pg_exec($conector_id, $sql1);

                $n_f_codigos=pg_numrows($result);




}

?>



<html>
<head>
<title>Documento sin t&iacute;tulo</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body bgcolor="#C1C675">
<form name="form1" method="post" action="">
  <p>&nbsp;</p>
  <p align="center">REPORTE DE VENTA X TRABAJADOR Desde: <?php echo $_fecha_desde; ?> Hasta: <?php echo $_fecha_hasta; ?>
<BR>
      <?php

$v_sql="select trim(ch_almacen), ch_nombre_almacen from inv_ta_almacenes  where ch_almacen like '%".trim($almacen)."%' and ch_clase_almacen='1' ";
$v_xsql=pg_query($conector_id,$v_sql);
if(pg_numrows($v_xsql)>0)	{	$v_descalma=pg_result($v_xsql,0,1);	}
?>
  ALMACEN ACTUAL <?php echo $almacen;?> <?php echo $v_descalma; ?>
  <input type="text" name="reloj" size="10" style="background-color : Black; color : White; font-family : Verdana, Arial, Helvetica; font-size : 8pt; text-align : center;" onfocus="window.document.f_repo.reloj.blur()" >
  </p>
<hr noshade>
<table width="240" border="1">
    <tr>
      <td colspan="4"><strong>Rango por Fechas : </strong></td>
    </tr>
    <tr>
      <td colspan="4"><strong>Almacen : 
        <select name="almacenes">
		<?php	
			$s=0;
			while($s<$n_f_alma){
			
			$codi_almacen=pg_result($result_alma,$s,0);
			$nomb_alma=pg_result($result_alma,$s,0);
				
			echo "<option value=".$codi_almacen.">".$nomb_alma;
		
			$s++;	
			}
		
			?>
        </select>

      </strong></td>
    </tr>	
    <tr>
      <td width="52"><strong>A&ntilde;o : </strong></td>
      <td colspan="3"><input name="ano1" type="text" size="10" value='<?php echo $ano1; ?>'></td>
    </tr>
    <tr>
      <td><strong>Mes : </strong></td>
      <td colspan="3"><input name="mes1" type="text" size="10" value='<?php echo $mes1; ?>'</td>
    </tr>
	    <tr>
	      <td><strong>D&iacute;a : </strong></td>
	      <td width="60"><input name="diad1" type="text" size="10" value='<?php echo $diad1; ?>'></td>
	      <td width="31"><strong>al </strong></td>
	      <td width="69"><input name="diaa1" type="text" size="10" value='<?php echo $diaa1; ?>'></td>
	    </tr>
	    <tr>
	      <td colspan="4"><div align="center">
		<input type="submit" name="boton" value="Imprimir">
	</div></td>
	    </tr>
	    <tr>
        	<td colspan=2>
        		<a href="#" onClick="javascript:window.open('/sistemaweb/clases/imprime_samba.php?v_server=<?php echo $v_server; ?>&v_printer=<?php echo $v_printer; ?>&v_ipprint=<?php echo $v_ipprint; ?>&v_archivo=<?php echo $v_archivo; ?>','miwin','width=800,height=450,scrollbars=yes,menubar=yes,left=0,top=0');"> Impresion Reporte </a>
        </td>

        	<td colspan=2>
		        <a href="#" onClick="javascript:window.open('venta_x_trabajador_exportar.php?_fecha_desde=<?php echo $_fecha_desde;?>&_fecha_hasta=<?php echo $_fecha_hasta;?>','miwin','width=800,height=450,scrollbars=yes,menubar=yes,left=0,top=0');">Exportar </a>
        </td>
        </tr>





</table>
	  <p>&nbsp;</p>

</form>

<table border=1>
</tr>
<td<b>Cod. Trabajador<b></td>
<td colspan="12"><b>Nombre</b></td>
<tr><b>
<td colspan="2"></td>
<td>Tip.Doc</td>
<td>Ticket</td>
<td>Caja</td>
<td>Turno</td>
<td>Hora</td>
<td>RUC</td>
<td>Codigo</td>
<td>Descripcion</td>
<td>Cant.</td>
<td>Precio</td>
<td>Total</td></b>
</tr>

	<?php

	$col[0]=15;
        $col[1]=50;
        $col[2]=8;
        $col[3]=6;
        $col[4]=4;
        $col[5]=5;
        $col[6]=9;
        $col[7]=12;
        $col[8]=14;
        $col[9]=30;
        $col[10]=5;
        $col[11]=10;
        $col[12]=10;
        $col[13]=30;

        $nom[0]= "Cod. Trabajador";
        $nom[1]= "Nombre";
        $nom[2]= "Tip. Doc";
        $nom[3]= "Ticket";
        $nom[4]= "Caja";
        $nom[5]= "Turno";
        $nom[6]= "Hora";
        $nom[7]= "RUC";
        $nom[8]= "Codigo";
        $nom[9]= "Descripcion";
        $nom[10]= "Cant.";
        $nom[11]= "Precio";
        $nom[12]= "Total";
        $nom[13]= "Firma";

        $linea="";

//----------------------------------------------------------------------------------------------------------------------
//COEMSANDO A ALMACENAR A ARCHIVO PARA IMPRIMIR
			
			//--------------------------INICIO-----------------------------
			$linea=$linea."<br>REPORTE DE VENTA POR TRABAJADOR<br>";
			$linea=$linea."Fecha: ".$_fecha_desde." al ".$_fecha_hasta."<br>";
                        $linea=$linea."ALMACEN: ".$almacen." -- ".$v_descalma." ";
                        $linea=$linea."<br>";
			//--------------------------TABLA------------------------------

//$linea=$linea. "<td>".str_pad( $a0 ,$col[0] )."</td>";

/*

			$linea=$linea."<table>";
                        $linea=$linea."<tr>";
                        $linea=$linea."<td>".str_pad( $nom[0], $col[0])"."</td>";
                        $linea=$linea."<td>".str_pad( $nom[1], $col[1])".</td>";
                        $linea=$linea."</tr>";
                        $linea=$linea."<tr>";
			$linea=$linea."<td></td>";
			$linea=$linea."<td>".str_pad( $nom[2] , $col[2])"."</td>";
                        $linea=$linea."<td>".str_pad( $nom[3] , $col[3])"."</td>";
                        $linea=$linea."<td>".str_pad( $nom[4] , $col[4])"."</td>";
                        $linea=$linea."<td>".str_pad( $nom[5] , $col[5])"."</td>";
                        $linea=$linea."<td>".str_pad( $nom[6] , $col[6])"."</td>";
                        $linea=$linea."<td>".str_pad( $nom[7] , $col[7])"."</td>";
                        $linea=$linea."<td>".str_pad( $nom[8] , $col[8])"."</td>";
                        $linea=$linea."<td>".str_pad( $nom[9] , $col[9])"."</td>";
                        $linea=$linea."<td>".str_pad( $nom[10] , $col[10])"."</td>";
                        $linea=$linea."<td>".str_pad( $nom[11] , $col[11])"."</td>";
                        $linea=$linea."<td>".str_pad( $nom[12] , $col[12])"."</td>";
                        $linea=$linea."<td>".str_pad( $nom[13] , $col[13])"."</td>";
                        $linea=$linea."</tr>";
	*/
	
			$linea=$linea."<table>";
                        $linea=$linea."<tr>";
                        $linea=$linea."<td>Cod. Trabajador</td>";
                        $linea=$linea."<td>Nombre</td>";
                        $linea=$linea."</tr>";
                        $linea=$linea."<tr>";
			$linea=$linea."<td></td>";
                        $linea=$linea."<td>Tip.Doc.</td>";
                        $linea=$linea."<td>Ticket</td>";
                        $linea=$linea."<td>Caja</td>";
                        $linea=$linea."<td>Turno</td>";
                        $linea=$linea."<td>   Hora</td>";
                        $linea=$linea."<td>     RUC</td>";
                        $linea=$linea."<td>       Codigo</td>";
                        $linea=$linea."<td>      Descripcion</td>";
                        $linea=$linea."<td>                                    Cant.</td>";
                        $linea=$linea."<td>      Precio     </td>";
                        $linea=$linea."<td> Totalo</td>";
                        $linea=$linea."<td>      Firma</td>";
                        $linea=$linea."</tr>";
	

		if($n_f_codigos>0){

			$conta1=0;

                        while($conta1<$n_f_codigos){

                        $documento=pg_result($result,$conta1,0);

//consulta para capturar nombre completo de trabajador

			$sql2="select ch_nombre1, ch_apellido_paterno from pla_ta_trabajadores where trim(ch_codigo_trabajador)=trim(".$documento.")";

			
			$result2=pg_exec($conector_id, $sql2);

			$n_f_nombres=pg_numrows($result2);
				
				$conta2=0;
					
				while($conta2<$n_f_nombres){
				
					$nombre=pg_result($result2,$conta2,0).pg_result($result2,$conta2,1);
				
								
				echo "<tr><td><b>".$documento."</b></td><td colspan='12'><b>".$nombre."</b></td></tr>";
//-------------------------------------------------------------------------------------------------------------------
// IMPRESION 
				$linea=$linea."<tr><td>".str_pad( $documento , $col[0] )."</td>";
				$linea=$linea."<td>".str_pad( $nombre , $col[1] )."</td></tr>";

//-------------------------------------------------------------------------------------------------------------------				






		///consulta para generar reporte----

			
		$sql3 = "select td, trans, caja, turno, dia, ruc, codigo, cantidad, precio, round(importe,2) from ".$tabla." where trim(documento) = trim(".$documento.") and to_char(dia,'yyyy-mm-dd') between '".$funcion->date_format($_fecha_desde,'YYYY-MM-DD')."' and '".$funcion->date_format($_fecha_hasta,'YYYY-MM-DD')."'" ;

//	$sql3 = "select td, importe from ".$tabla." where trim(documento) = trim(".$documento.")";

				$result3=pg_exec($conector_id,$sql3);
				$n_f_trans=pg_numrows($result3);

					$conta3=0;	
					$cant_total= 0;
					$cantidad = 0;
					$imp_total=0;
					$importe = 0;
					

					while($conta3<$n_f_trans){
					
				//	$cant_total=$cant_total + $cantidad;
				//	$imp_total=$imp_total + $importe;

					//obteniendo CODIGO de la consulta sql3
					$codigo=pg_result($result3,$conta3,6);
					// consultado ala tabla articulo para poder obtener descripcion

					$sql4="select art_descripcion from int_articulos where art_codigo='".$codigo."'";
					//recojer descripcion y empesar a mostrar todo
					$result4=pg_exec($conector_id, $sql4);
					
					$descripcion=pg_result($result4,0,0);


					//variables
					$td=pg_result($result3,$conta3,0);
					$trans=pg_result($result3,$conta3,1);
					$caja=pg_result($result3,$conta3,2);
					$turno=pg_result($result3,$conta3,3);
				
					$dia=pg_result($result3,$conta3,4);
				//para extraer la hora
					$hora=substr($dia,12,19);
					$ruc=pg_result($result3,$conta3,5);
					$cantidad=pg_result($result3,$conta3,7);
					$precio=pg_result($result3,$conta3,8);
					$importe=pg_result($result3,$conta3,9);
					
					
					$cant_total=$cant_total + $cantidad;
					$imp_total=round($imp_total + $importe,2);

					echo "<tr>";

					echo "<td colspan='2'> </td>";
					echo "<td>".$td."</td>";
					echo "<td>".$trans."</td>";
					echo "<td>".$caja."</td>";
					echo "<td>".$turno."</td>";
					echo "<td>".$hora."</td>";
					echo "<td>".$ruc."</td>";
					echo "<td>".$codigo."</td>";
					echo "<td>".$descripcion."</td>";
					echo "<td>".number_format($cantidad,0,'','')."</td>";
					echo "<td>".number_format($precio,2,'.','')."</td>";
					echo "<td>".number_format($importe,2,'.','')."</td>";
					echo"</tr>";
					
//$linea=$linea. "<td>".str_pad( $a0 ,$col[0] )."</td>";
// $linea=$linea. "<td>".str_pad( number_format($a3, 2, '.', ''), $col[3], " ", STR_PAD_LEFT )."</td>";

					$linea=$linea."<tr><td></td>";
					$linea=$linea."<td>".str_pad( $td, $col[2])."</td>";
					$linea=$linea."<td>".str_pad( $trans, $col[3])."</td>";
                                        $linea=$linea."<td>".str_pad( $caja, $col[4])."</td>";
                                        $linea=$linea."<td>".str_pad( $turno, $col[5])."</td>";
                                        $linea=$linea."<td>".str_pad( $hora, $col[6])."</td>";
                                        $linea=$linea."<td>".str_pad( $ruc, $col[7])."</td>";
                                        $linea=$linea."<td>".str_pad( $codigo, $col[8])."</td>";
                                        $linea=$linea."<td>".str_pad( $descripcion, $col[9])."</td>";
                                        $linea=$linea."<td>".str_pad( number_format($cantidad,0,'',''), $col[10], " ", STR_PAD_LEFT)."</td>";
                                        $linea=$linea."<td>".str_pad( number_format($precio,2,'.',''), $col[11], " ", STR_PAD_LEFT)."</td>";
                                        $linea=$linea."<td>".str_pad( number_format($importe,2,'.',''), $col[12], " ", STR_PAD_LEFT)."</td>";

                                        $linea=$linea."</tr>";
					

					$conta3++;
					}
			
				$espacio="           ";
				echo "<tr><td colspan='9'></td><td><b>TOTAL TRAB. --></b></td>";
				echo "<td><b>".$cant_total."</b></td><td></td><td><b>".$imp_total."</b></td></tr>";
				
	$linea=$linea."<tr><td>".$espacio."</td><td>".$espacio."</td><td>".$espacio."</td><td>".$espacio."</td><td>".$espacio."</td><td>".$espacio."</td><td>".$espacio."</td><td>".$espacio."</td><td>            TOTAL TRAB.  --></td>";
				$linea=$linea."<td> ".str_pad( $cant_total, $col[11] )."</td><td>       </td><td>".str_pad($imp_total,2,'.','')."</td>";
				
				$linea=$linea."<td>    _____________</td></tr>";
				
	
				$conta2++;
				}
			
                        $conta1++; 
                        }

               

}

$linea=$linea."</table>";


	imprimir2( $linea, $col, $nom, $v_archivo, "Venta por Trabajador" );



?>
</table>
<br><br>

<p align="center">&nbsp;</p>
</body>
</html>

<?php
// comprueba si la conexion existe y la cierra
if ($conector_id) pg_close($conector_id);
// restaura el control de errores original
$clase_error->_error();

