<?php
include("../valida_sess.php");
include("../menu_princ.php");
include("../functions.php");

require("../clases/funciones.php");
$funcion = new class_funciones;

// crea la clase para controlar errores
$clase_error = new OpensoftError;

// conectar con la base de datos
$conector_id=$funcion->conectar("","","","","");

if ( is_null($almacen) or trim($almacen)=="")
	{
	$almacen="001";
	}

// carga los almacenes en un dropdown
$v_xsqlalma=pg_exec("select trim(ch_almacen) as cod,ch_nombre_almacen from inv_ta_almacenes  where ch_clase_almacen='1' order by cod");

if($cod_almacen=="")
{
	$cod_almacen=$almacen;
}

$rsx2 = pg_exec("select ch_almacen ,ch_nombre_almacen from inv_ta_almacenes where ch_clase_almacen='1'
		and  trim(ch_almacen)=trim('$cod_almacen') order by ch_nombre_almacen");	
$C = pg_fetch_array($rsx2,0);
$almacen_dis = $C[1];
//FIN PARA COLOCAR EL ALMACEN POR DEFAULT O EL ULTIMO QUE SE HA SELECCIONADO
$rsx1 = pg_exec("select ch_almacen ,ch_nombre_almacen from inv_ta_almacenes where ch_clase_almacen='1'
		order by ch_nombre_almacen");

if (is_null($v_fecha_desde) or is_null($v_fecha_hasta) )
	{
	$v_fecha_desde=date("d/m/Y");
	$v_fecha_hasta=date("d/m/Y");
	}
$v_ilimit=0;

if ($boton=='Imprimir')
	{
	$v_par_cliente=" ";
	$v_par_trabajador=" ";
	$v_par_almacen=" and c.ch_sucursal='".$cod_almacen."' ";
	if ( strlen($v_cliente)>0)
		{
		$v_par_cliente=" and c.ch_cliente='".$v_cliente."' ";
		}

	if ( strlen($v_trabajador)>0)
		{
		$v_par_trabajador=" and c.ch_planilla='".$v_trabajador."' ";
		}

        $v_xsqlprn=pg_exec($conector_id,"drop table vtacli_vales_reporte;");

        $v_sqlprn="select c.ch_cliente, cli.cli_razsocial,
					c.ch_placa, c.nu_odometro,
					c.dt_fecha, c.ch_documento,
					d.ch_articulo, art.art_descripcion,
                                        d.nu_cantidad,  d.nu_importe as det_nu_importe,
                                        c.nu_importe as cab_nu_importe, c.ch_planilla
                                        into vtacli_vales_reporte
			from val_ta_cabecera c, val_ta_detalle d, int_clientes cli, int_articulos art
			where c.ch_sucursal=d.ch_sucursal and c.dt_fecha=d.dt_fecha and c.ch_documento=d.ch_documento
					and c.ch_cliente=cli.cli_codigo and d.ch_articulo=art.art_codigo and c.dt_fecha
					between '".$funcion->date_format($v_fecha_desde,'YYYY-MM-DD')."' and '".$funcion->date_format($v_fecha_hasta,'YYYY-MM-DD')."'
					".$v_par_cliente.$v_par_trabajador.$v_par_almacen."
			order by c.ch_cliente,c.ch_placa,c.dt_fecha";
        $v_xsqlprn=pg_exec($conector_id,$v_sqlprn);

        $v_sqlprn="select * from vtacli_vales_reporte";
	$v_xsqlprn=pg_exec($conector_id,$v_sqlprn);
	$v_ilimit=pg_numrows($v_xsqlprn);
	}

?>
<html><link rel="stylesheet" href="<?php echo $v_path_url; ?>acosa.css" type="text/css">
<head>
<title>SISTEMAWEB</title>
<script language="JavaScript" src="/sistemaweb/ventas_clientes/js/miguel.js"></script>
<script language="JavaScript" src="/sistemaweb/clases/calendario.js"></script>
<script language="JavaScript" src="/sistemaweb/clases/overlib_mini.js"></script>
<script language="JavaScript" src="/sistemaweb/clases/reloj.js"></script>
<script language="JavaScript" src="/sistemaweb/compras/validacion.js"></script>
<script language="JavaScript" src="/sistemaweb/compras/valfecha.js"></script>
<script>

function mostrarProcesar(url,cod,des,consulta){
url = url+"?cod="+cod+"&des="+des+"&consulta="+consulta;
window.open(url,'miwin','width=500,height=350,scrollbars=yes,menubar=no,left=390,top=20');
}

function activa(){
	// carga de frente el formulario con el foco en diad
//        document.f_repo.v_fecha_desde.select()
//        document.f_repo.v_fecha_desde.focus()
	}

</script>

</head>

<body onfocus="mueveReloj('f_repo.reloj');">

<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<form name="f_repo" method="post">
<div align="center"><font face="Arial, Helvetica, sans-serif">
REPORTE DE VALES DE LAS ESTACIONES Desde <?php echo $v_fecha_desde; ?> Hasta <?php echo $v_fecha_hasta; ?> <BR>
    <?php
$v_sql="select trim(ch_almacen), ch_nombre_almacen from inv_ta_almacenes  where ch_almacen like '%".trim($almacen)."%' and ch_clase_almacen='1' ";
$v_xsql=pg_query($conector_id,$v_sql);
if(pg_numrows($v_xsql)>0)	{	$v_descalma=pg_result($v_xsql,0,1);	}
?>ESTACION
    <?php //echo $almacen;?>
    <?php //echo $v_descalma; ?>
    <select name="cod_almacen" >
    <?php
	print "<option value='$cod_almacen' >$cod_almacen -- $almacen_dis</option>";
	for($i=0;$i<pg_numrows($rsx1);$i++)
	{
		$B = pg_fetch_row($rsx1,$i);
		print "<option value='$B[0]' >$B[0] -- $B[1]</option>";
	}
	?>
  </select>
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
	}
?>


<table border="1">
	<tr>
                <th colspan="7">Reporte Por : RANGO DE FECHAS / CLIENTE / TRABAJADOR </th>
	</tr>
	<tr>
		<th>DESDE :</th>
		<th>
		<p>
		<input type="text" name="v_fecha_desde" size="16" maxlength="10" value='<?php echo $v_fecha_desde ; ?>'  tabindex="1"  onKeyUp="javascript:validarFecha(this)" onblur="javascript:validarFecha(this)"  >
		<a href="javascript:show_calendar('f_repo.v_fecha_desde');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;" >
	    <img src="/sistemaweb/images/show-calendar.gif" width=24 height=22 border=0></a>
		</p>
		</th>
        </tr>
        <tr>

		<th>HASTA:</th>
		<th>
		<p>
		<input type="text" name="v_fecha_hasta" size="16" maxlength="10" value='<?php echo $v_fecha_hasta ; ?>'  tabindex="2" onKeyUp="javascript:validarFecha(this)" onblur="javascript:validarFecha(this)">
		<a href="javascript:show_calendar('f_repo.v_fecha_hasta');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;">
	    <img src="/sistemaweb/images/show-calendar.gif" width=24 height=22 border=0></a>
		</p>
		</th>

	</tr>
	<tr>

                <td><font size="-4" face="Arial, Helvetica, sans-serif">Cliente: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</font>
		<font size="-4" face="Arial, Helvetica, sans-serif">
                        <input type="text" name="v_cliente"  tabindex=3 value='<?php echo $v_cliente ; ?>' onblur="javascript:mostrarProcesar('procesando.php', this.value , 'f_repo.v_desclie','clientes')" >
			<img src="../images/help.gif" width="16" height="15" onClick="javascript:mostrarAyuda('lista_ayuda.php','f_repo.v_cliente','f_repo.v_desclie','clientes')">
			</font></td>
		<td><font size="-4" face="Arial, Helvetica, sans-serif">
			<input type="text" name="v_desclie" size="50" readonly="true">
			</font></td>

        </tr>
        <tr>

		<td><font size="-4" face="Arial, Helvetica, sans-serif">Trabajador: </font>
		<font size="-4" face="Arial, Helvetica, sans-serif">
                        <input type="text" name="v_trabajador"  tabindex=4  value='<?php echo $v_trabajador ; ?>' onblur="javascript:mostrarProcesar('procesando.php',this.value,'f_repo.v_desctra','trabajadores')" >
			<img src="../images/help.gif" width="16" height="15" onClick="javascript:mostrarAyuda('lista_ayuda.php','f_repo.v_trabajador','f_repo.v_desctra','trabajadores')">
			</font></td>
		<td><font size="-4" face="Arial, Helvetica, sans-serif">
			<input type="text" name="v_desctra" size="50" readonly="true">
			</font></td>

	</tr>
	<tr>
                <th colspan="2"><input type="submit" name="boton" tabindex=5 value="Imprimir">
		</th>

	</tr>

        <tr>
        <td colspan=1>
        <a href="#" onClick="javascript:window.open('vtacli_vales_texto.php?v_fecha_desde=<?php echo $v_fecha_desde;?>&v_fecha_hasta=<?php echo $v_fecha_hasta;?>','miwin','width=800,height=450,scrollbars=yes,menubar=yes,left=0,top=0');"> Impresion Texto </a>
        </td>

        <td colspan=1>
        <a href="#" onClick="javascript:window.open('vtacli_vales_exportar.php?v_fecha_desde=<?php echo $v_fecha_desde;?>&v_fecha_hasta=<?php echo $v_fecha_hasta;?>&v_cliente=<?php echo $v_cliente;?>','miwin','width=800,height=450,scrollbars=yes,menubar=yes,left=0,top=0');">Exportar </a>
        </td>
        </tr>

</table>

<br>


<table width="990" border="1" height="81">
        <?php
	echo "<tr>";
        echo "<th colspan=18 align='center'><font size='3.5'> <BR> DETALLE DE CONSUMO AL CREDITO Desde: ".$v_fecha_desde." Hasta: ".$v_fecha_hasta."  <P></P>     </th>";
	echo "</tr>";
        ?>
	<tr>
		<td width="85">Fecha</td>
		<td width="106" align="center">Nota Despacho</td>
		<td width="247">Codigo/Nombre Articulo</td>
		<td width="69" align="center">Trabajador</td>
		<td width="97" align="right">Cantidad</td>
		<td width="85" align="right">Importe</td>
		<td width="135" align="right">Numeros deVales</td>
		<td width="114" align="right">Total</td>
	</tr>

	<?php
	$v_irow=0;
	$v_tot_cli_cant_doc=0;
	$v_tot_cli_impo=0;
	$v_cli_cant_doc=0;
	$v_cli_impo=0;

	$v_antes=" ";


	if($v_ilimit>0)
		{
		while($v_irow<$v_ilimit)
			{
			$a0=pg_result($v_xsqlprn,$v_irow,0);
			$a1=pg_result($v_xsqlprn,$v_irow,1);
			$a2=pg_result($v_xsqlprn,$v_irow,2);
			$a3=pg_result($v_xsqlprn,$v_irow,3);
			echo "<tr>";
			echo "<th align='left' colspan=3>CLIENTE: ".$a0." - ".$a1."   </th>";
			echo "<th align='left' colspan=2>Placa:".$a2." </th>";
			echo "<th align='left' colspan=5>Odometro:".$a3."</th>";
			echo "</tr>";
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

				echo "<tr>";
				echo "<td>".$a4."</td>";
				echo "<td>".$a5."</td>";
				echo "<td>".$a6." - ".$a7."</td>";
				echo "<td>&nbsp;</td>";
				echo "<td align='right'>".number_format($a8, 2, '.', '')."</td>";
				echo "<td align='right'>".number_format($a9, 2, '.', '')."</td>";
				echo "<td align='right'>";

				/***   CODIGO AGREGADO POR FRED PARA QUE APARESCA EL NRO DE VALE Y SU IMPORTE ***/
				$sql_vales  = "select ch_numeval, nu_importe from val_ta_complemento
						WHERE
							trim(ch_sucursal)||trim(dt_fecha)||trim(ch_documento)
							='".trim($almacen).trim($a4).trim($a5)."'";
				//ECHO $sql_vales;
				$nro_vales = pg_query($conector_id, $sql_vales);
				$count = 0;
				if(pg_num_rows($nro_vales)>0)
				{
					while($count<pg_num_rows($nro_vales))
					{
						echo pg_result($nro_vales,$count,0)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
						echo pg_result($nro_vales,$count,1)."<br>";
						$count++;
					}
				}
				/***   CODIGO AGREGADO POR FRED PARA QUE APARESCA EL NRO DE VALE Y SU IMPORTE ***/

				echo "</td>";
				echo "<td align='right'>".number_format($a10, 2, '.', '')."</td>";
				echo "</tr>";
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
			echo "<tr>";
			echo "<td>&nbsp;</td>";
			echo "<th align='left' colspan=2> TOTAL CLIENTE:</th>";
			echo "<th>Doc:".$v_cli_cant_doc."</th>";
			echo "<td colspan=2>&nbsp;</td>";
			echo "<th align='right'>--></th>";
			echo "<th align='right'>".number_format($v_cli_impo, 2, '.', '')."</th>";
			echo "</tr>";
			$v_tot_cli_cant_doc=$v_tot_cli_cant_doc+$v_cli_cant_doc;
			$v_tot_cli_impo=$v_tot_cli_impo+$v_cli_impo;
			$v_cli_cant_doc=0;
			$v_cli_impo=0	;

			}
		echo "<tr>";
		echo "<td>&nbsp;</td>";
		echo "<th align='left' colspan=2>TOTAL GENERAL : </th>";
		echo "<th align='left' colspan=3>Doc:".$v_tot_cli_cant_doc."</th>";
		echo "<th align='right'>=========></th>";
		echo "<th align='right'>".number_format($v_tot_cli_impo, 2, '.', '')."</th>";
		echo "</tr>";
		}
	?>




</table>



</form>
</body>
</html>
<?php
// comprueba si la conexion existe y la cierra
if ($conector_id) pg_close($conector_id);
// restaura el control de errores original
$clase_error->_error();
