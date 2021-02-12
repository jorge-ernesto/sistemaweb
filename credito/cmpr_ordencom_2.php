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


// Vuelve al Programa de seleccion;
if($boton=="Modificar cabecera") 
	{
	$sql3="select   PRO_CODIGO,
				NUM_TIPDOCUMENTO,
				NUM_SERIEDOCUMENTO,
				COM_CAB_NUMORDEN,
				COM_CAB_ALMACEN,
				COM_CAB_FECHAORDEN,
				COM_CAB_MONEDA,
				COM_CAB_TIPCAMBIO,
				COM_CAB_CREDITO,
				COM_CAB_FORMAPAGO,
				COM_CAB_IMPORDEN,
				COM_CAB_RECARGO1,
				COM_CAB_OBSERVACION,
				COM_CAB_ESTADO,
				COM_CAB_FECHAOFRECIDA,
				COM_CAB_FECHARECIBIDA 
				from COM_CABECERA
				where PRO_CODIGO||NUM_TIPDOCUMENTO||NUM_SERIEDOCUMENTO||COM_CAB_NUMORDEN='".$m_clave."' " ;

	$xsql3=pg_query($conector_id,$sql3);
	$ilimit3=pg_numrows($xsql3);

	$m_moneda=pg_result($xsql3,0,'COM_CAB_MONEDA'); 
	$m_tcambio=pg_result($xsql3,0,'COM_CAB_TIPCAMBIO'); 
	$m_credito=pg_result($xsql3,0,'COM_CAB_CREDITO');
	$m_formapago=pg_result($xsql3,0,'COM_CAB_FORMAPAGO');
	$m_recargo1=pg_result($xsql3,0,'COM_CAB_RECARGO1');
	$m_comentario=pg_result($xsql3,0,'COM_CAB_OBSERVACION');
	$m_fechaofrecida=$funcion->date_format( pg_result($xsql3,0,'COM_CAB_FECHAOFRECIDA'),'DD/MM/YYYY');
	$m_fecharecibida=$funcion->date_format( pg_result($xsql3,0,'COM_CAB_FECHARECIBIDA'),'DD/MM/YYYY');

	
	$v_modificar_cabecera="Grabar cabecera";
	$v_estado_cabecera=" ";
	$boton=" ";
	}
//if($boton=="Grabar cabecera") 
else
	{
	if ($boton=="Grabar cabecera" and strlen($m_moneda)>0 and strlen($m_formapago)>0 and strlen($m_credito)>0 )
		{
		$m_tcambio=round($m_tcambio,2);
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
								
		$v_sql="update COM_CABECERA set
						COM_CAB_MONEDA='$m_moneda',
						COM_CAB_TIPCAMBIO='$m_tcambio',
						COM_CAB_CREDITO='$m_credito',						
						COM_CAB_FORMAPAGO='$m_formapago',
						COM_CAB_RECARGO1='$m_recargo1', 
						COM_CAB_OBSERVACION='$m_comentario', 
						".$v_act_fechaofrecida."
						".$v_act_fecharecibida."
						COM_CAB_ESTADO='1' 
					where PRO_CODIGO||NUM_TIPDOCUMENTO||NUM_SERIEDOCUMENTO||COM_CAB_NUMORDEN='$m_clave'";
					
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
	$sqleli="delete from COM_DETALLE 
					where PRO_CODIGO||NUM_TIPDOCUMENTO||NUM_SERIEDOCUMENTO||COM_CAB_NUMORDEN||ART_CODIGO='$m_clavedet' ";
	$xsqleli=pg_query($conector_id,$sqleli);
	$boton=" ";
	}

if($boton=="Ins" or $boton=="Agregar") 
	{
	$m_tcambio=round($m_tcambio,2);
	$m_recargo1=round($m_recargo1,2);

	$sql="insert into COM_DETALLE ( PRO_CODIGO, NUM_TIPDOCUMENTO, NUM_SERIEDOCUMENTO, COM_CAB_NUMORDEN, ART_CODIGO,
				COM_DET_CANTIDADPEDIDA, COM_DET_PRECIO, COM_DET_DESCUENTO1, COM_DET_ESTADO )
				values ('$m_proveedor','$m_tipdoc', '$m_serie','$m_orden', '$v_art_codigo',
						'$m_cantidadpedida','$m_precio', '$m_descuento1', '1') ";
	$xsql=pg_query( $conector_id,  $sql );
	// en este momento ya cargo las longitudes correctas
	$m_clave=$m_proveedor.$m_tipdoc.$m_serie.$m_orden;
	$boton=" ";
	echo("<script>");
	echo("	location.href='cmpr_ordencom_2.php?m_clave=".$m_clave."' " );
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
	
function abreprov() {
	miPopup = window.open("../maestros/escogeproveedor.php?k_variable=formular.m_proveedor","miwin","width=500,height=400,scrollbars=yes") 
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
<form name='formular' action="../compras/cmpr_ordencom_2.php?m_clave=<?php echo $m_clave;?>" method="post">
<?php 

$sql3="select   PRO_CODIGO,
				NUM_TIPDOCUMENTO,
				NUM_SERIEDOCUMENTO,
				COM_CAB_NUMORDEN,
				COM_CAB_ALMACEN,
				COM_CAB_FECHAORDEN,
				COM_CAB_MONEDA,
				COM_CAB_TIPCAMBIO,
				COM_CAB_CREDITO,
				COM_CAB_FORMAPAGO,
				COM_CAB_IMPORDEN,
				COM_CAB_RECARGO1,
				COM_CAB_OBSERVACION,
				COM_CAB_ESTADO,
				COM_CAB_FECHAOFRECIDA,
				COM_CAB_FECHARECIBIDA 
				from COM_CABECERA
				where PRO_CODIGO||NUM_TIPDOCUMENTO||NUM_SERIEDOCUMENTO||COM_CAB_NUMORDEN='".$m_clave."' " ;
//					where det.art_codigo=art.art_codigo and det.pro_codigo='".$m_proveedor."' and det.num_tipdocumento='".$m_tipdoc."' and det.num_seriedocumento='".$m_serie."' and det.com_cab_almacen='".$m_almacen."' and det.com_cab_numorden='".$m_orden."' " ;

$xsql3=pg_query($conector_id,$sql3);
$ilimit3=pg_numrows($xsql3);

$m_proveedor=pg_result($xsql3,0,'PRO_CODIGO');
$m_tipdoc=pg_result($xsql3,0,'NUM_TIPDOCUMENTO'); 
$m_serie=pg_result($xsql3,0,'NUM_SERIEDOCUMENTO'); 
$m_orden=pg_result($xsql3,0,'COM_CAB_NUMORDEN'); 

$m_almacen=pg_result($xsql3,0,'COM_CAB_ALMACEN'); 
$m_fecha=$funcion->date_format(pg_result($xsql3,0,'COM_CAB_FECHAORDEN'),'DD/MM/YYYY'); 

if ($v_estado_cabecera=="disabled")
	{
	$m_moneda=pg_result($xsql3,0,'COM_CAB_MONEDA'); 
	$m_tcambio=pg_result($xsql3,0,'COM_CAB_TIPCAMBIO'); 
	$m_credito=pg_result($xsql3,0,'COM_CAB_CREDITO');
	$m_formapago=pg_result($xsql3,0,'COM_CAB_FORMAPAGO');
	$m_recargo1=pg_result($xsql3,0,'COM_CAB_RECARGO1');
	$m_comentario=pg_result($xsql3,0,'COM_CAB_OBSERVACION');
	$m_fechaofrecida=$funcion->date_format( pg_result($xsql3,0,'COM_CAB_FECHAOFRECIDA'),'DD/MM/YYYY');
	$m_fecharecibida=$funcion->date_format( pg_result($xsql3,0,'COM_CAB_FECHARECIBIDA'),'DD/MM/YYYY');
	}


//if (pg_result($xsql3,0,13)=="S") {$m_credito="SI";} else {$m_credito="NO";}
$m_cantidadpedida=0;
$m_precio=0;
$m_descuento1=0;
	
?>

<input type="hidden" name="m_clavedet" value='<?php echo $m_clavedet;?>'>
<input type="hidden" name="m_proveedor" value='<?php echo $m_proveedor;?>'>
<input type="hidden" name="m_tipdoc" value='<?php echo $m_tipdoc;?>'>
<input type="hidden" name="m_serie" value='<?php echo $m_serie;?>'>
<input type="hidden" name="m_almacen" value='<?php echo $m_almacen;?>'>
<input type="hidden" name="m_orden" value='<?php echo $m_orden;?>'>

<input type="hidden" name="v_modificar_cabecera" value='<?php echo $v_modificar_cabecera;?>'>
<input type="hidden" name="v_estado_cabecera" value='<?php echo $v_estado_cabecera;?>'>

<table border="1" >
	<tr> 	
		<th width="500">ORDENES DE COMPRA</th>
	</tr>
</table>
<table border="1" >
	<tr> 
		<th width="100" >N&deg; NUMERO </th>      
		<td>:</td>      
		<td>&nbsp;<?php echo $m_orden;?></td>

		<th>FECHA</th>
		<td>:</td>
		<td>&nbsp;<?php echo $m_fecha; ?></td>
		
	</tr>
	<tr> 
		<th>ALMACEN</th>
		<td>:</td>
		<td>&nbsp;<?php echo $m_almacen;?>
		<?php
		if( strlen($m_almacen)>0 )
			{
			// $sqlalma="select TAB_ELEMENTO, TAB_DESCRIPCION from INT_TABLA_GENERAL where TAB_TABLA='ALMA' and TAB_ELEMENTO like '%".$m_almacen."%' ";
			$sqlalma="select trim(ch_almacen) as cod,ch_nombre_almacen from inv_ta_almacenes  where ch_almacen like '%".$m_almacen."%' and  ch_clase_almacen='1' ";
			$xsqlalma=pg_query($conector_id,$sqlalma);
			if(pg_numrows($xsqlalma)>0)
				{
				$m_descalma=pg_result($xsqlalma,0,1);
				echo $m_descalma;
				}
			}
		?>
		</td>

		<th>PROVEEDOR</th>
		<td>:</td>
		<td>&nbsp;<?php echo $m_proveedor;?>
		<?php 
		if(strlen($m_proveedor)>0) 
			{
			$sqlprov="select PRO_RAZSOCIAL 
						from INT_PROVEEDORES 
						where PRO_CODIGO='".$m_proveedor."' ";
			$xsqlprov=pg_query($conector_id,$sqlprov);
			if(pg_numrows($xsqlprov)>0) 
				{
				$m_descprov=pg_result($xsqlprov,0,0); 
				echo $m_descprov; 
				}
			}
		?>
		</td>
	</tr>

	<tr> 
		<th>MONEDA</th>
		<td>:</td>

		<td width="118" valign="top">  
		<select name="m_moneda" onblur='submit()'  <?php echo $v_estado_cabecera; ?> >
			<?php
			$v_xsqlmone=pg_exec("select trim(tab_elemento) as cod,tab_descripcion from int_tabla_general  where tab_tabla='MONE' and tab_elemento!='000000' order by cod");
			for($i=0;$i<pg_numrows($v_xsqlmone);$i++){		
				$k_alma1 = pg_result($v_xsqlmone,$i,0);	
				$k_alma2 = pg_result($v_xsqlmone,$i,1);
				if ($k_alma1==$m_moneda) { 
					echo "<option value='".$k_alma1."' selected >".$k_alma1." -- ".$k_alma2." </option>";	
					} 
				else {
					echo "<option value='".$k_alma1."' >".$k_alma1." -- ".$k_alma2." </option>";	
					}
		  		}
			?>

		
        </select>
		</td>


		<?php
		if(strlen($m_moneda)>0 and strlen($m_tcambio)==0) 
			{
			$sqltcam="select TCA_COMPRA_OFICIAL from INT_TIPO_CAMBIO where TCA_MONEDA='".$m_moneda."' and TCA_FECHA='".$m_fecha."' ";
			$xsqltcam=pg_query($conector_id,$sqltcam);
			if(pg_numrows($xsqltcam)>0) 
				{ 
				$m_tcambio=pg_result($xsqltcam,0,0); 
				}
			}
		?>

		<th>T.Cambio</th>
		<td>:</td>
		<td><input name="m_tcambio" type="text" value="<?php echo $m_tcambio;?>" size="5" maxlength="6" onkeyup='validarNumeroDecimales(this)'  tabindex="5" <?php echo $v_estado_cabecera;?>  >
		<?php
		if(strlen($m_tcambio)>0) 
			{
			}
		else
			{
			$m_tcambio=0;
			}
		?>
		</td>
	</tr>


	<tr> 
		<th>CANON</th>
		<td>:</td>
		<td><input name="m_recargo1" type="text" value="<?php echo $m_recargo1;?>" size="16" maxlength="10" onkeyup='validarNumeroDecimales(this)' <?php echo $v_estado_cabecera; ?> >
		</td>
		
		<th>COMENTARIO</th>
		<td>:</td>
		<td><input name="m_comentario" type="text" value="<?php echo $m_comentario;?>" size="20" maxlength="20" <?php echo $v_estado_cabecera; ?> >
		</td>
	</tr>

	<tr> 
		<th>CREDITO</th>
		<td>:</td>
		
		<td>
			<?php 
			$checksi=" ";
			$checkno=" ";
			if ($m_credito=='S') 
				{
				$checksi="checked"; 
				}  
			else 
				{
				$checkno="checked"; 
				}
			echo ("<input type='radio' name='m_credito' value='S' ".$checksi."  onclick='javascript:m_formapago.value=\" \";submit();' tabindex='8' ".$v_estado_cabecera." >SI");
			echo ("<input type='radio' name='m_credito' value='N' ".$checkno."  onclick='javascript:m_formapago.value=\" \";submit();' tabindex='8' ".$v_estado_cabecera." >NO");

			?>
		</td>

		<th>FORMA PAGO</th>
		<td>:</td>
		<?php 
		if ($m_credito=='S') 
			{
			$m_tab="05";
			}
		else 
			{
			$m_tab="96";
			}
		?>

		<td width="118" valign="top">  
		<select name="m_formapago" tabindex="2" <?php echo $v_estado_cabecera; ?> >
			<?php 
			$v_sqlfpag="select substr(TAB_ELEMENTO,5,2) ,TAB_DESCRIPCION from INT_TABLA_GENERAL where TAB_TABLA='".$m_tab."' and tab_elemento!='000000' order by TAB_ELEMENTO ";
			$v_xsqlfpag=pg_query($conector_id,$v_sqlfpag);
			for($i=0;$i<pg_numrows($v_xsqlfpag);$i++){	
				$k_alma1 = pg_result($v_xsqlfpag,$i,0);	
				$k_alma2 = pg_result($v_xsqlfpag,$i,1);
				if ($k_alma1==$m_formapago) { 
					echo "<option value='".$k_alma1."' selected >".$k_alma1." -- ".$k_alma2." </option>";	
					} 
				else {
					echo "<option value='".$k_alma1."' >".$k_alma1." -- ".$k_alma2." </option>";	
					}
		  		}
			?>
		
        </select>
		</td>

	</tr>


	
	<tr> 
		<th>Fecha Ofrecida</th>
		<td>:</td>
		<td>		
		<p>
		<input type="text" name="m_fechaofrecida" size="16" maxlength="10" value='<?php echo $m_fechaofrecida ; ?>'  tabindex="1" <?php echo $v_estado_cabecera; ?> onKeyUp="javascript:validarFecha(this)" onblur="javascript:validarFecha(this)"  >
		<a href="javascript:show_calendar('formular.m_fechaofrecida');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;">
	    <img src="/sistemaweb/images/show-calendar.gif" width=24 height=22 border=0></a>
		</p>
		</td>

		<th>Fecha Recibida</th>
		<td>:</td>
		<td>		
		<p>
		<input type="text" name="m_fecharecibida" size="16" maxlength="10" value='<?php echo $m_fecharecibida ; ?>'  tabindex="1" <?php echo $v_estado_cabecera; ?> onKeyUp="javascript:validarFecha(this)" onblur="javascript:validarFecha(this)"  >
		<a href="javascript:show_calendar('formular.m_fecharecibida');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;">
	    <img src="/sistemaweb/images/show-calendar.gif" width=24 height=22 border=0></a>
		</p>
		</td>

		<?php
		if(strlen($m_fechaofrecida)==0) 
			{
			$m_fechaofrecida=" ";
			}
		if(strlen($m_fecharecibida)==0) 
			{
			$m_fecharecibida=" ";
			}
			
		?>
	
	</tr>
	
	
</table>



<table border="1" cellpadding="0" cellspacing="0" >
	<tr> 
		<th>&nbsp;</th>
		<th>CODIGO</th>
		<th>DESCRIPCION</th>
		<th>CANTIDAD</th>
		<th>COSTO UNITARIO</th>
		<th>DESCUENTO</th>
		<th>SUBTOTAL</th>
	</tr>
	<tr> 
		<th>&nbsp;</th>
		<?php
	
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
		
		<th><input name="m_cantidadpedida" type="text" size='15'  maxlength="15" value="<?php echo $m_cantidadpedida;?>" onkeyup='validarNumeroDecimales(this)'   ></th>
		
		<th><input name="m_precio" type="text" size='15' maxlength="15" value="<?php echo $m_precio;?>" onkeyup='validarNumeroDecimales(this)' ></th>
		<th><input name="m_descuento1" type="text" size='15' maxlength="15" value="<?php echo $m_descuento1;?>" onkeyup='validarNumeroDecimales(this)'  ></th>
		
		<th><input type="submit" name="boton" value="Agregar" ></th>
	</tr>
	
    <?php
	$sql3="select   DET.PRO_CODIGO||DET.NUM_TIPDOCUMENTO||DET.NUM_SERIEDOCUMENTO||DET.COM_CAB_NUMORDEN||DET.ART_CODIGO, 
					DET.ART_CODIGO, 
					ART.ART_DESCRIPCION,
					DET.COM_DET_CANTIDADPEDIDA,
					DET.COM_DET_PRECIO,
					DET.COM_DET_DESCUENTO1,
					DET.COM_DET_IMPARTICULO 
					from COM_DETALLE DET, INT_ARTICULOS ART 
					where DET.ART_CODIGO=ART.ART_CODIGO and DET.PRO_CODIGO||DET.NUM_TIPDOCUMENTO||DET.NUM_SERIEDOCUMENTO||DET.COM_CAB_NUMORDEN='".$m_clave."' " ;
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
		$ad5=pg_result($xsql3,$irow3,5);
		$ad6=pg_result($xsql3,$irow3,6);
		echo "<tr>";
		echo "<td><input type='radio' name='m_clavedet' value='".$ad0."'></td>";
		echo "<td>".$ad1."</td>";
		echo "<td>".$ad2."</td>";
		echo "<td><p align='right'>".$ad3."</p></td>";
		echo "<td><p align='right'>".$ad4."</p></td>";
		echo "<td><p align='right'>".$ad5."</p></td>";
		echo "<td><p align='right'>".$ad6."</p></td>";
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


