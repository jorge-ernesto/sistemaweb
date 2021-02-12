<?php
//include("../valida_sess.php");
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

extract($_REQUEST);

if ( is_null($almacen) or trim($almacen)=="")
	{
	$almacen="001";
	}

// carga los almacenes en un dropdown 
$v_xsqlalma=pg_exec("select trim(ch_almacen) as cod,ch_nombre_almacen from inv_ta_almacenes  where ch_clase_almacen='1' order by cod");

//formato de fecha ingresada

if (is_null($_REQUEST['diad1']) or is_null($_REQUEST['diaa1']) )
	{
	$_fecha_desde=date("d/m/Y");
	$_fecha_hasta=date("d/m/Y");
	}else{
		$_fecha_desde=$_REQUEST['diad1'];
		$_fecha_hasta=$_REQUEST['diaa1'];
	}

	//fecha para asignar tabla

       $v_anomes = substr($_fecha_desde,6,4).substr($_fecha_desde,3,2);

	//$f=trim($ano1).trim($mes1);
	
	$tabla="pos_trans".$v_anomes ;

	$codtraba = "";
	$codarti = "";
	
	if ($boton=='Imprimir')
        
	{   $codtraba = trim($_REQUEST['codtrabajador']);
	    $codarti = trim($_REQUEST['codarticulo']);


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

                $sql1="select distinct trim(documento) from ".$tabla." where documento is not null
			" . (($codtraba=="")?"":"AND documento='$codtraba'")."
		";
                $result=pg_exec($conector_id, $sql1);

                $n_f_codigos=pg_numrows($result);


}

?>
<html>
<head>
<title>Documento sin t&iacute;tulo</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<script language="JavaScript" src="/sistemaweb/clases/calendario.js"></script>
<script language="JavaScript" src="/sistemaweb/clases/overlib_mini.js"></script>
<form name="form1" method="post" action="">
  <p>&nbsp;</p>
  <p align="center"><h2 align="center" style="font-size:14px">REPORTE DE VENTA X TRABAJADOR Desde: <?php echo $_fecha_desde; ?> Hasta: <?php echo $_fecha_hasta; ?></h2>
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
<table border="0">
    <tr>
      <td colspan="4"><strong>Rango por Fechas del mismo periodo : </strong></td>
    </tr>
    <tr>
      <td colspan="4"><strong>Almacen : 
        <select name="select"><?php 
		for($i=0;$i<pg_numrows($v_xsqlalma);$i++){
			$A = pg_fetch_array($v_xsqlalma,$i);
			echo '<option value="'.$A[0].'">'.$A[1].'</option>';
		}
		?>
        </select>
      </strong></td>
    </tr>	
    	    <tr>
	      <td width="25%" style="text-align:right">D&iacute;a : </td>
	      <td width="25%"><input name="diad1" type="text" size="10" value='<?php echo $_fecha_desde; ?>'/><a href="javascript:show_calendar('form1.diad1');"><img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a><div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"/></td>
	      <td width="25%" style="text-align:right"><strong>al </strong></td>
	      <td width="25%"><input name="diaa1" type="text" size="10" value='<?php echo $_fecha_hasta; ?>'><a href="javascript:show_calendar('form1.diaa1');"><img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a></td>
	    </tr>
	<tr>
		<td style="text-align:right">Cod. Art&iacute;culo</td><td><input type="text" name="codarticulo" size="13" value="<?php echo $codarti; ?>"></td>
		<td style="text-align:right">Cod. Trabajador</td><td><input type="text" name="codtrabajador" size="13" value="<?php echo $codtraba; ?>"></td>	
	</tr>

	    <tr>
	      <td colspan="4"><div align="center">
		<input type="submit" name="boton" value="Imprimir">
	</div></td>
	    </tr>
	   
        		<!-- <a href="#" onClick="javascript:window.open('/sistemaweb/clases/imprime_samba.php?v_server=<?php echo $v_server; ?>&v_printer=<?php echo $v_printer; ?>&v_ipprint=<?php echo $v_ipprint; ?>&v_archivo=<?php echo $v_archivo; ?>','miwin','width=800,height=450,scrollbars=yes,menubar=yes,left=0,top=0');"> Impresion Reporte </a>
        		
        </td>

        	<td colspan=2>
		        <a href="#" onClick="javascript:window.open('venta_x_trabajador_exportar.php?_fecha_desde=<?php echo $_fecha_desde;?>&_fecha_hasta=<?php echo $_fecha_hasta;?>','miwin','width=800,height=450,scrollbars=yes,menubar=yes,left=0,top=0');">Exportar </a>
		        -->
        
</table>
</form>

<table border=1>
</tr>
	<td><b>Cod. Trabajador</b></td>
	<td colspan="12"><b>Nombre</b></td>
	<tr><b>
	<td colspan="2"></td>
	<td>TD</td>
	<td>Trans</td>
	<td>Caja</td>
	<td>Turno</td>
	<td><center>Dia / Hora</center></td>
	<td>RUC</td>
	<td>Codigo</td>
	<td>Descripcion</td>
	<td>Cant.</td>
	<td>Precio</td>
	<td>Total</td></b>
</tr>

	<?php

	$col[0]=13;
        $col[1]=10;
        $col[2]=2;
        $col[3]=5;
        $col[4]=2;
        $col[5]=2;
        $col[6]=9;
        $col[7]=12;
        $col[8]=14;
        $col[9]=30;
        $col[10]=3;
        $col[11]=6;
        $col[12]=6;
        $col[13]=6;

        $nom[0]= "Cod.Trab";
        $nom[1]= "Nombre";
        $nom[2]= "TD";
        $nom[3]= "Trans";
        $nom[4]= "Caja";
        $nom[5]= "T";
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
//COMENZANDO A ALMACENAR A ARCHIVO PARA IMPRIMIR
			
			//--------------------------INICIO-----------------------------
			$linea=$linea."<br>REPORTE DE VENTA POR TRABAJADOR<br>";
			$linea=$linea."Fecha: ".$_fecha_desde." al ".$_fecha_hasta."<br>";
                        $linea=$linea."ALMACEN: ".$almacen." -- ".$v_descalma." ";
                        $linea=$linea."<br>";
			//--------------------------TABLA------------------------------

//$linea=$linea. "<td>".str_pad( $a0 ,$col[0] )."</td>";

			$linea=$linea."<table>";
                        $linea=$linea."<tr>";
			$linea=$linea."<td>Cod.Trab</td>";
                        $linea=$linea."<td>Nombre</td>";
                        $linea=$linea."</tr>";
                        $linea=$linea."<tr>";
			$linea=$linea."<td>      </td>";
                        $linea=$linea."<td>TD</td>";
                        $linea=$linea."<td>Trans</td>";
                        $linea=$linea."<td>Caj</td>";
                        $linea=$linea."<td>T</td>";
                        $linea=$linea."<td>    Dia   /  Hora   </td>";
                        $linea=$linea."<td>    RUC     </td>";
                        $linea=$linea."<td>   Codigo   </td>";
                        $linea=$linea."<td> Descripcion                   </td>";
                        $linea=$linea."<td>Cant</td>";
                        $linea=$linea."<td>Precio</td>";
                        $linea=$linea."<td>Total</td>";
                        $linea=$linea."<td>Firma</td>";
                        $linea=$linea."</tr>";
			$linea=$linea."<tr></tr>";
	

		if($n_f_codigos>0){

			$conta1=0;

                        while($conta1<$n_f_codigos){

                        $documento=pg_result($result,$conta1,0);

//consulta para capturar nombre completo de trabajador

			$sql2="select ch_nombre1, ch_apellido_paterno 
			       from pla_ta_trabajadores 
			       where trim(ch_codigo_trabajador)=trim('".$documento."')"  ;
			       // and (ch_puesto like '039   ' or ch_puesto like '501   ' or ch_puesto like '509   ' or ch_puesto like '511   ')";
//echo $sql2;
			
			$result2=pg_exec($conector_id, $sql2);			
			$n_f_nombres=pg_numrows($result2);
	
				$conta2=0;
					
				while($conta2<$n_f_nombres){
				
					$nombre=trim(pg_result($result2,$conta2,0))." ".trim(pg_result($result2,$conta2,1));
				
								
				echo "<tr><td><b>".$documento."</b></td><td colspan='12'><b>".$nombre."</b></td></tr>";
//-------------------------------------------------------------------------------------------------------------------
// IMPRESION 
				$linea=$linea."<tr><td>".str_pad( $documento , 6 );
				$linea=$linea.str_pad( $nombre ,6 )."</td></tr>";

//-------------------------------------------------------------------------------------------------------------------				

		///consulta para generar reporte----
			
		$sql3 = "select td, trans, caja, turno, date_trunc('second',fecha), ruc, codigo, cantidad, precio, round(importe,2) 
			from ".$tabla." 
			where trim(documento) = trim('".$documento."') and 
			date_trunc('day',dia) 
				between '".$funcion->date_format($_fecha_desde,'YYYY-MM-DD')."' and '".$funcion->date_format($_fecha_hasta,'YYYY-MM-DD')."'
			-----
			" . (($codarti=="")?"":"AND codigo='$codarti'")."
			-----
			order by fecha" ;

// echo $sql3;
				$result3=pg_exec($conector_id,$sql3);
				$n_f_trans=pg_numrows($result3);

					$conta3=0;	
					$cant_total= 0;
					$cantidad = 0;
					$imp_total=0;
					$importe = 0;
					

					while($conta3<$n_f_trans){
					
					//obteniendo CODIGO de la consulta sql3
					$codigo=pg_result($result3,$conta3,6);
					// consultado ala tabla articulo para poder obtener descripcion

					$sql4="select art_descripcion from int_articulos where art_codigo='".$codigo."'";
					//recojer descripcion y empesar a mostrar todo
					$result4=pg_exec($conector_id, $sql4);
					
					$descripcion=trim(pg_result($result4,0,0));


					//variables
					$td=pg_result($result3,$conta3,0);
					$trans=pg_result($result3,$conta3,1);
					$caja=pg_result($result3,$conta3,2);
					$turno=pg_result($result3,$conta3,3);
				
					$dia=pg_result($result3,$conta3,4);
					//para extraer la hora
					$hora=substr($dia,11,19);
					$ruc=pg_result($result3,$conta3,5);
					$cantidad=trim(pg_result($result3,$conta3,7));
					$precio=trim(pg_result($result3,$conta3,8));
					$importe=trim(pg_result($result3,$conta3,9));
					
					
					$cant_total=$cant_total + $cantidad;
					$imp_total=round($imp_total + $importe,2);

					echo "<tr>";

					echo "<td colspan='2'> </td>";
					echo "<td>".$td."</td>";
					echo "<td>".$trans."</td>";
					echo "<td>".$caja."</td>";
					echo "<td>".$turno."</td>";
					echo "<td>".$dia."</td>";
					echo "<td>".$ruc."</td>";
					echo "<td>".$codigo."</td>";
					echo "<td>".$descripcion."</td>";
					echo "<td>".number_format($cantidad,0,'','')."</td>";
					echo "<td>".number_format($precio,2,'.','')."</td>";
					echo "<td>".number_format($importe,2,'.','')."</td>";
					echo"</tr>";
					

					$linea=$linea."<tr><td>      </td>";
					$linea=$linea."<td>".str_pad( $td, $col[2])."</td>";
					$linea=$linea."<td>".str_pad( $trans, $col[3])."</td>";
                                        $linea=$linea."<td>".str_pad( $caja, $col[4])."</td>";
                                        $linea=$linea."<td>".str_pad( $turno, $col[5])."</td>";
                                        $linea=$linea."<td>".str_pad( $dia, $col[6])."</td>";
                                        $linea=$linea."<td>".str_pad( $ruc, $col[7])."</td>";
                                        $linea=$linea."<td>".str_pad( $codigo, $col[8])."</td>";
                                        $linea=$linea."<td>".str_pad( $descripcion, $col[9])."</td>";
                                        $linea=$linea."<td>".str_pad( number_format($cantidad,0,'',''), $col[10], " ", STR_PAD_LEFT)."</td>";
                                        $linea=$linea."<td>".str_pad(number_format($precio,2,'.',''), $col[11], " ", STR_PAD_LEFT)."</td>";
                                        $linea=$linea."<td>".str_pad(number_format($importe,2,'.',''), $col[12], " ", STR_PAD_LEFT)."</td>";

                                        $linea=$linea."</tr>";
					

					$conta3++;
					}
			
				$espacio="          ";
				echo "<tr><td colspan='9'></td><td><b>TOTAL TRAB. --></b></td>";
				echo "<td><b>".$cant_total."</b></td><td></td><td><b>".$imp_total."</b></td></tr>";
				
	$linea=$linea."<tr><td>".$espacio."</td><td>".$espacio."</td><td>".$espacio."</td><td>".$espacio."</td><td>".$espacio."</td><td>".$espacio."</td><td>".$espacio."</td><td>        TOTAL TRAB.  --></td>";
				$linea=$linea."<td> ".str_pad($cant_total, $col[11] )."</td><td> </td><td>".str_pad(number_format($imp_total,2,'.',''), $col[12], " ", STR_PAD_LEFT)."</td>";
				
				$linea=$linea."<td>    __________ </td></tr>";
				
	
				$conta2++;
				}
			
                        $conta1++; 
                        }

               

}

$linea=$linea."</table>";
//     echo $v_archivo ;

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

