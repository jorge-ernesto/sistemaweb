<?php
//include("../valida_sess.php");

include("../menu_princ.php");
include("../functions.php");

require("../clases/funciones.php");
$funcion = new class_funciones;

// crea la clase para controlar errores
$clase_error = new OpensoftError;

// conectar con la base de datos
$conector_id=$funcion->conectar("","","","","");


// Vuelve al Programa de seleccion;
//echo "AQUI VA LA FECHA".$fecha_val;
if($boton=="Modificar cabecera")
	{
	$sql3="select
			CH_SUCURSAL,
			DT_FECHA,
			CH_CLIENTE,
			CH_DOCUMENTO,
			CH_PLANILLA,
			CH_PLACA,
			NU_ODOMETRO
			from VAL_TA_CABECERA
			where trim(CH_SUCURSAL||DT_FECHA||CH_DOCUMENTO)='".$m_clave."' " ;

	$xsql3=pg_query($conector_id,$sql3);
	$ilimit3=pg_numrows($xsql3);

	/*$m_moneda=pg_result($xsql3,0,'COM_CAB_MONEDA');
	$m_tcambio=pg_result($xsql3,0,'COM_CAB_TIPCAMBIO');
	$m_credito=pg_result($xsql3,0,'COM_CAB_CREDITO');
	$m_formapago=pg_result($xsql3,0,'COM_CAB_FORMAPAGO');
	$m_recargo1=pg_result($xsql3,0,'COM_CAB_RECARGO1');
	$m_comentario=pg_result($xsql3,0,'COM_CAB_OBSERVACION');
	//$m_fechaofrecida=$funcion->date_format( pg_result($xsql3,0,'COM_CAB_FECHAOFRECIDA'),'DD/MM/YYYY');
	//$m_fecharecibida=$funcion->date_format( pg_result($xsql3,0,'COM_CAB_FECHARECIBIDA'),'DD/MM/YYYY');

*/
	$v_modificar_cabecera="Grabar cabecera";
	$v_estado_cabecera=" ";
	$boton=" ";
	}
//if($boton=="Grabar cabecera")
else
	{
	if ($boton=="Grabar cabecera" and strlen($m_cantidadpedida)>0 and strlen($m_precio)>0)
		{
/*		$m_tcambio=round($m_tcambio,2);
		$m_recargo1=round($m_recargo1,2);

		if (strlen($m_fechaofrecida) > 0)
			{
			$m_fechaofrecida=$funcion->date_format($m_fechaofrecida,'YYYY-MM-DD');
			$v_act_fechaofrecida="COM_CAB_FECHAOFRECIDA='$m_fechaofrecida',";
			}
		else
			{ $v_act_fechaofrecida=" "; }

		if (strlen($m_fecharecibida) > 0)
			{
			$m_fecharecibida=$funcion->date_format($m_fecharecibida,'YYYY-MM-DD' );
			$v_act_fecharecibida="COM_CAB_FECHARECIBIDA='$m_fecharecibida',";
			}
		else
			{ $v_act_fecharecibida=" "; }
*/

		if(strlen($cod_cliente)>0)
			{
			$sqlprov="select cli_codigo,cli_razsocial
						from INT_clientes
						where cli_codigo='".$cod_cliente."' ";
			$xsqlprov=pg_query($conector_id,$sqlprov);

		if(pg_numrows($xsqlprov)>0)
				{
				$cli_codigo=pg_result($xsqlprov,0,0);
				$desc_cliente=pg_result($xsqlprov,0,1);
				}
			}

		$cod_cliente=trim($cod_cliente);
		$cod_planilla=trim($cod_planilla);
		$nro_placa=trim($nro_placa);
		$odometro=trim($odometro);

		$v_sql="update VAL_TA_CABECERA set
						CH_CLIENTE='$cod_cliente',
						CH_PLANILLA='$cod_planilla',
						CH_PLACA='$nro_placa',
						NU_ODOMETRO=".$odometro.",
						CH_ESTADO='1'
				where trim(CH_SUCURSAL||DT_FECHA||CH_DOCUMENTO)='$m_clave'";

		$v_xsql=pg_query($conector_id, $v_sql);
		$boton=" ";
		$v_modificar_cabecera="Modificar cabecera";
		$v_estado_cabecera="disabled";
		}
	if (is_null($v_modificar_cabecera))
		{
		$v_modificar_cabecera="Modificar cabecera";
		$v_estado_cabecera="disabled";
		}
	}


if($boton=="Regresar")
	{
	echo('<script languaje="JavaScript">');
	echo("	location.href='cmpr_ordencom.php?'; ");
	echo('</script>');
	}

if($boton=="Eliminar")
	{
	$sqleli="delete from val_ta_detalle
					where trim(CH_SUCURSAL||DT_FECHA||CH_DOCUMENTO||CH_ARTICULO)='$m_clavedet' ";
	//echo "sql eliminar".$sqleli;
	$xsqleli=pg_query($conector_id,$sqleli);
	$boton=" ";
	}

if($boton=="Ins" or $boton=="Agregar")
	{

	$m_almacen=trim($m_almacen);
	$fecha_val=$funcion->date_format($fecha_val,'YYYY-MM-DD');
//	$fecha_val=trim($fecha_val);
//	$cod_cliente=trim($cod_cliente);
	$nro_vale=trim($nro_vale);
//	$cod_planilla=trim($cod_planilla);
//	$nro_placa=trim($nro_placa);
//	$odometro=trim($odometro);
//VARIABLES DEL SEGUNDO FORMULARIO
	//$cod_vale=trim($cod_vale);
	$v_art_codigo=trim($v_art_codigo);
	$m_cantidadpedida=trim($m_cantidadpedida);
	$m_precio=trim($m_precio);



	/*$sql="insert into val_ta_detalle (PRO_CODIGO, NUM_TIPDOCUMENTO, NUM_SERIEDOCUMENTO, COM_CAB_NUMORDEN, ART_CODIGO,
				COM_DET_CANTIDADPEDIDA, COM_DET_PRECIO, COM_DET_DESCUENTO1, COM_DET_ESTADO )
				values ('$m_proveedor','$m_tipdoc', '$m_serie','$m_orden', '$v_art_codigo',
						'$m_cantidadpedida','$m_precio', '$m_descuento1', '1') ";
	$xsql=pg_query( $conector_id,  $sql );
	// en este momento ya cargo las longitudes correctas
*/

	$sql="INSERT INTO val_ta_detalle (CH_SUCURSAL, DT_FECHA, CH_DOCUMENTO, CH_ARTICULO,
		  NU_CANTIDAD, NU_IMPORTE, CH_ESTADO) values
		  ('$m_almacen','$fecha_val', '$nro_vale', '$v_art_codigo','$m_cantidadpedida', '$m_precio', '1')";
	//echo "<script language='javascript'> alert '$sql';</script>";
	$xsql=pg_query( $conector_id, $sql );

	$m_clave=$m_almacen.$fecha_val.$nro_vale;
	$boton=" ";
	echo("<script>");
	echo("	location.href='vales_agregar_2.php?m_clave=".$m_clave."' " );
	echo("</script>");
	}


?>
<html><head>
<script language="JavaScript" src="/sistemaweb/clases/calendario.js"></script>
<script language="JavaScript" src="/sistemaweb/clases/overlib_mini.js"></script>
<script language="JavaScript" src="/sistemaweb/compras/validacion.js"></script>

<script language="javascript">
var miPopup
function abrealma(){
	miPopup = window.open("../maestros/escogealmacen.php?k_variable=formular.m_almacen","miwin","width=500,height=400,scrollbars=yes")
	miPopup.focus()
	}

function abrecliente() {
	miPopup = window.open("../maestros/escogecliente.php?k_variable=formular.cod_cliente","miwin","width=500,height=400,scrollbars=yes")
	miPopup.focus()
	}


function abrearti() {
	miPopup = window.open("../maestros/escogearticulo.php?k_variable=formular.v_art_codigo","miwin","width=550,height=400,scrollbars=yes")
	miPopup.focus()
	}

function abretabla( tabla ,k_var ){
	miPopup = window.open("../maestros/escogetabla.php?m_tabla="+tabla+"&k_variable="+k_var+" ","miwin","width=600,height=350,scrollbars=yes")
	miPopup.focus()
	}

function enviadatos(){
	document.formular.submit()
	}

function enviapago(){
	document.formular.m_formapago.value=" ";
	}

function verifica_arti(arti){
	var vr1 = new Array();
	var valor = arti.value;
	var existe = false;
	<?php
//	for($i=0;$i<pg_numrows($v_xsql);$i++){
//		$K = pg_fetch_row($v_xsql,$i);
//		print ' vr1['.$i.'] = "'.$K[0].'"; ';
//		}
	?>
	for(i=0;i<vr1.length;i++){
		if( vr1[i]== valor){  existe = true;   }
		}
	if (!existe) {
		alert('El código de articulo no existe ');
		}
	}

</script>
</head>
<body>
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<form name="formular" action="vales_agregar_2.php?m_clave=<?php echo $m_clave;?>" method="post">
<?php

//echo "ESTA ES LA CLAVE ".$m_clave;

$sql3="select CH_SUCURSAL,
			  DT_FECHA,
			  CH_CLIENTE,
			  CH_DOCUMENTO,
			  CH_PLANILLA,
			  CH_PLACA,
			  NU_ODOMETRO
			  from VAL_TA_CABECERA
			  where trim(CH_SUCURSAL||DT_FECHA||CH_DOCUMENTO)='".$m_clave."'";
//					where det.art_codigo=art.art_codigo and det.pro_codigo='".$m_proveedor."' and det.num_tipdocumento='".$m_tipdoc."' and det.num_seriedocumento='".$m_serie."' and det.com_cab_almacen='".$m_almacen."' and det.com_cab_numorden='".$m_orden."' " ;

$xsql3=pg_query($conector_id,$sql3);
$ilimit3=pg_numrows($xsql3);

$m_almacen=pg_result($xsql3,0,'CH_SUCURSAL');
$fecha_val=$funcion->date_format(pg_result($xsql3,0,'DT_FECHA'),'DD/MM/YYYY');
//$cod_cliente=pg_result($xsql3,0,'CH_CLIENTE');
$nro_vale=pg_result($xsql3,0,'CH_DOCUMENTO');

$cod_planilla=pg_result($xsql3,0,'CH_PLANILLA');
$nro_placa=pg_result($xsql3,0,'CH_PLACA');
$odometro=pg_result($xsql3,0,'NU_ODOMETRO');

if ($v_estado_cabecera=="disabled")
	{
	//$m_moneda=pg_result($xsql3,0,'COM_CAB_MONEDA');
	//$m_tcambio=pg_result($xsql3,0,'COM_CAB_TIPCAMBIO');
	//$m_credito=pg_result($xsql3,0,'COM_CAB_CREDITO');
	//$m_formapago=pg_result($xsql3,0,'COM_CAB_FORMAPAGO');
	//$m_recargo1=pg_result($xsql3,0,'COM_CAB_RECARGO1');
	//$m_comentario=pg_result($xsql3,0,'COM_CAB_OBSERVACION');
	//$m_fechaofrecida=$funcion->date_format( pg_result($xsql3,0,'COM_CAB_FECHAOFRECIDA'),'DD/MM/YYYY');
	//$m_fecharecibida=$funcion->date_format( pg_result($xsql3,0,'COM_CAB_FECHARECIBIDA'),'DD/MM/YYYY');
	}


//if (pg_result($xsql3,0,13)=="S") {$m_credito="SI";} else {$m_credito="NO";}
$m_cantidadpedida=0;
$m_precio=0;
//$m_descuento1=0;

?>


<input type="hidden" name="fecha_val" value='<?php echo $fecha_val;?>'>
<input type="hidden" name="m_almacen" value='<?php echo $m_almacen;?>'>
<input type="hidden" name="nro_vale" value='<?php echo $nro_vale;?>'>


<?php/*
<input type="hidden" name="m_clavedet" value='<?php echo $m_clavedet;?>'>
<input type="hidden" name="m_serie" value='<?php echo $m_serie;?>'>
<input type="hidden" name="m_orden" value='<?php echo $m_orden;?>'>
--*/?>

<input type="hidden" name="v_modificar_cabecera" value='<?php echo $v_modificar_cabecera;?>'>
<input type="hidden" name="v_estado_cabecera" value='<?php echo $v_estado_cabecera;?>'>

<table border="0" >
	<tr>
		<th width="500">VALES DE CREDITO</th>
	</tr>
</table>
<table border="0" >
	<?php /*<tr>
		<th width="100" >N&deg; NUMERO </th>
		<td>:</td>
		<td>&nbsp;<?php echo $m_orden;?></td>

		<th>FECHA</th>
		<td>:</td>
		<td>&nbsp;<?php echo $m_fecha; ?></td>

	</tr> */?>

	<tr>
		<th>ALMACEN</th>
		<td>:</td>
		<td>&nbsp;<?php echo $m_almacen;?>
		<?php
		if( strlen($m_almacen)>0 )
			{
			// $sqlalma="select TAB_ELEMENTO, TAB_DESCRIPCION from INT_TABLA_GENERAL where TAB_TABLA='ALMA' and TAB_ELEMENTO like '%".$m_almacen."%' ";
			$sqlalma="select trim(ch_sucursal) as cod, ch_nombre_almacen
					  from inv_ta_almacenes
					  where ch_almacen like '%".$m_almacen."%' and ch_clase_almacen='1' ";

			$xsqlalma=pg_query($conector_id,$sqlalma);
			if(pg_numrows($xsqlalma)>0)
				{
				$m_descalma=pg_result($xsqlalma,0,1);
				echo $m_descalma;
				}
			}
		?>
		</td>
	<tr>
	<th>FECHA</th>
		<td>:</td>
		<td>
		<p>


		<input type="hidden" name="fecha_val" size="16" value="<?php echo $fecha_val; ?>">
		<?php echo $fecha_val; ?>


		</p>
		</td>

	<tr>

		<th>CLIENTE</th>
		<td>:</td>
		<td>&nbsp;
		<?php
		if(strlen($cod_cliente)>0)
			{
			$sqlprov="select CLI_RAZSOCIAL
						from INT_CLIENTES
						where CLI_CODIGO='".$cod_cliente."'";
			$xsqlprov=pg_query($conector_id,$sqlprov);
			if(pg_numrows($xsqlprov)>0)
				{
				$desc_cliente=pg_result($xsqlprov,0,0);
				}
   		/*	else
   				{
				echo('<script languaje="JavaScript"> ');
				echo('alert(" No Existe Cliente !!! "); ');
   				echo('</script>');
			   	}*/
			}
		?>

		<input name="cod_cliente" type="text" size="15" maxlength="12" value="<?php echo $cod_cliente;?>" <?php echo $v_estado_cabecera; ?>>
		<input name="imgprov" type="image" src="/sistemaweb/images/help.gif" width="16" height="15" border="0" onClick="abrecliente()" <?php echo $v_estado_cabecera; ?>>
		&nbsp; <?php echo $desc_cliente; ?> </td>



	<!--	<input name="imgprov" type="image" src="/sistemaweb/images/help.gif" width="16" height="15" border="0" onClick="abreprov()">
		&nbsp; <?php echo $desc_cliente; ?> </td>  -->

		<tr>

		<th>NUMERO VALE</th>
		<td>:</td>
		<td><?php echo $nro_vale; ?><input name="nro_vale" type="hidden" maxlength="10" value="<?php echo $nro_vale;?>">
		</td>

		<th>COD. PLANILLA</th>
		<td>:</td>
		<td><input name="cod_planilla" type="text" value="<?php echo $cod_planilla;?>" size="16" maxlength="10" <?php echo $v_estado_cabecera; ?> >
		</td>

	<tr>
		<th>NRO_PLACA</th>
		<td>:</td>
		<td><input name="nro_placa" type="text" value="<?php echo $nro_placa;?>" size="16" maxlength="10" <?php echo $v_estado_cabecera; ?> >
		</td>

		<th>ODOMETRO</th>
		<td>:</td>
		<td><input name="odometro" type="text" value="<?php echo $odometro;?>" size="16" maxlength="10" onkeyup='validarNumeroDecimales(this)' <?php echo $v_estado_cabecera; ?>>
		</td>
	</tr>
</table>

<table border="1" cellpadding="0" cellspacing="0" >
	<tr>
		<th>&nbsp;</th>
		<th>ARTICULO</th>
		<th>DESCRIPCION</th>
		<th>CANTIDAD</th>
		<th>IMPORTE</th>


	</tr>
	<tr>
		<th>&nbsp;</th>
		<?php
		//echo "ESTO ES EL CODIGO DE ARTICULO".$v_art_codigo;
		if(strlen($v_art_codigo)>0)
			{
			$v_xsql=pg_query($conector_id,"select ART_CODIGO, ART_DESCRIPCION from INT_ARTICULOS where ART_CODIGO='".$v_art_codigo."' ");
			if(pg_numrows($v_xsql)>0)
				{
				$v_art_codigo=pg_result($v_xsql,0,0);
				$v_art_descripcion=pg_result($v_xsql,0,1);
				}
			else
				{
				echo('<script languaje="JavaScript"> ');
				echo('alert(" No Existe Articulo !!! "); ');
				echo('</script>');
				}
			}

		?>

		<th><input type="text" name="v_art_codigo" size='19' maxlength="13" value="<?php echo $v_art_codigo;?>"   onblur='submit()' onkeyup='validarNumeroEntero(this)' >
			<input name="imgarti" type="image" src="/sistemaweb/images/help.gif" width="16" height="15" border="0" onClick="abrearti()"></th>
		<td>&nbsp; <?php echo $v_art_descripcion; ?>	</td>

		<th><input name="m_cantidadpedida" type="text" size='15'  maxlength="15" value="<?php echo $m_cantidadpedida;?>" onkeyup='validarNumeroDecimales(this)' ></th>

		<th><input name="m_precio" type="text" size='15' maxlength="15" value="<?php echo $m_precio;?>" onkeyup='validarNumeroDecimales(this)' ></th>

		<th><input type="submit" name="boton" value="Agregar" ></th>
	</tr>


    <?php
	$sql3="select   trim(DET.CH_SUCURSAL||DET.DT_FECHA||DET.CH_DOCUMENTO||DET.CH_ARTICULO),
					DET.CH_ARTICULO,
					ART.ART_DESCRIPCION,
					DET.NU_CANTIDAD,
					DET.NU_IMPORTE
					from VAL_TA_DETALLE DET, INT_ARTICULOS ART
					where
					DET.CH_ARTICULO=ART.ART_CODIGO
					and trim(DET.CH_SUCURSAL||DET.DT_FECHA||DET.CH_DOCUMENTO)='".$m_clave."' " ;
//					where det.art_codigo=art.art_codigo and det.pro_codigo='".$m_proveedor."' and det.num_tipdocumento='".$m_tipdoc."' and det.num_seriedocumento='".$m_serie."' and det.com_cab_almacen='".$m_almacen."' and det.com_cab_numorden='".$m_orden."' " ;

	$xsql3=pg_query($conector_id,$sql3);
	$ilimit3=pg_numrows($xsql3);
	while($irow3<$ilimit3)
		{
		$ad0=pg_result($xsql3,$irow3,0);
		$ad1=pg_result($xsql3,$irow3,1);
		$ad2=pg_result($xsql3,$irow3,2);
		$ad3=pg_result($xsql3,$irow3,3);
		$ad4=pg_result($xsql3,$irow3,4);
		/*$ad5=pg_result($xsql3,$irow3,5);
		$ad6=pg_result($xsql3,$irow3,6);*/
		echo "<tr>";
		echo "<td><input type='radio' name='m_clavedet' value='".$ad0."'></td>";
		echo "<td>".$ad1."</td>";
		echo "<td>".$ad2."</td>";
		echo "<td><p align='right'>".$ad3."</p></td>";
		echo "<td><p align='right'>".$ad4."</p></td>";
		/*echo "<td><p align='right'>".$ad5."</p></td>";
		echo "<td><p align='right'>".$ad6."</p></td>";*/
		echo "</tr>";
		$irow3++;
		}

	?>
	<tr>
		<td>&nbsp;</td>
		<td><input type="submit" name="boton" value="Eliminar"></td>
		<td><input type="submit" name="boton" value="<?php echo $v_modificar_cabecera; ?>" >
		&nbsp;&nbsp;
		<input type="submit" name="boton" value="Regresar"></td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
</table>
</form>
</body>
</html>


<?php
// comprueba si la conexion existe y la cierra
if ($conector_id) pg_close($conector_id);

// restaura el control de errores original
$clase_error->_error();
?>
