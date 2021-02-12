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
// $v_xsqlalma=pg_exec("select trim(tab_elemento) as cod,tab_descripcion from int_tabla_general  where tab_tabla='ALMA' and tab_elemento!='000000' and tab_car_02='1' order by cod");
$v_xsqlalma=pg_exec("select trim(ch_almacen) as cod,ch_nombre_almacen from inv_ta_almacenes  where ch_clase_almacen='1' order by cod");
$k_var = pg_fetch_row($v_xsqlalma,0);
$k_almacen=trim($k_var[0]);
$k_almacen_desc=$k_var[1];


if($boton=="Ins" or $boton=="Agregar")
	{
	// carga el ultimo numero orden de compra en que se quedo la tabla de numeradores de documentos
//	echo $m_tipdoc;
//	echo $m_serie;
	$sql="select int_sp_numero_documento_ins( '$m_tipdoc', '$m_serie' )";
	$xsql=pg_query( $conector_id,  $sql );
	$m_orden=pg_result($xsql, 0, 0 );
//	echo $m_orden;
	$m_proveedor=trim($m_proveedor);
	$m_tipdoc=trim($m_tipdoc);
	$m_serie=trim($m_serie);
	$m_almacen=trim($m_almacen);
	$m_fecha=trim($m_fecha);
	$m_tcambio=round($m_tcambio,2);
	$m_recargo1=round($m_recargo1,2);

	$okgraba=true;


	if(strlen($m_proveedor)>0)
		{
		$xsqlprov=pg_query($conector_id,"select PRO_CODIGO,PRO_RAZSOCIAL from INT_PROVEEDORES where PRO_CODIGO='".$m_proveedor."' ");
		if(pg_numrows($xsqlprov)>0)
			{
			$m_proveedor=pg_result($xsqlprov,0,0);
			$m_descprov=pg_result($xsqlprov,0,1);
			}
		else
			{
			$v_mensaje=" No se puede Agregar \\n No Existe Proveedor !!! ";	$okgraba=false;
			}
		}
	else
		{
		$v_mensaje=" No se puede Agregar \\n Proveedor Vacio !!! ";	$okgraba=false;
		}

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
			$v_mensaje=" No se puede Agregar \\n No Existe Articulo !!! ";	$okgraba=false;
			}
		}
	else
		{
		$v_mensaje=" No se puede Agregar \\n Articulo Vacio !!! ";	$okgraba=false;
		}

	if ($okgraba)
		{
		if (strlen($m_fecha) > 0)
			{
			$m_fecha=$funcion->date_format($m_fecha,'YYYY-MM-DD');
			$v_ins_fecha="COM_CAB_FECHAORDEN,";
			$v_val_fecha="'$m_fecha',";
			}
		else
			{
			$m_fecha=" ";
			$v_ins_fecha=" ";
			$v_val_fecha=" ";
			}
		$m_moneda=trim($m_moneda);
		$m_tcambio=trim($m_tcambio);
		$m_formapago=trim($m_formapago);
		$m_recargo1=trim($m_recargo1);
		$m_comentario=trim($m_comentario);

		$sql="insert into COM_CABECERA ( PRO_CODIGO, NUM_TIPDOCUMENTO, NUM_SERIEDOCUMENTO, COM_CAB_NUMORDEN, COM_CAB_ALMACEN,
					".$v_ins_fecha." COM_CAB_MONEDA, COM_CAB_TIPCAMBIO, COM_CAB_FORMAPAGO, COM_CAB_IMPORDEN, COM_CAB_RECARGO1,
					COM_CAB_OBSERVACION, COM_CAB_ESTADO ,COM_CAB_CREDITO)
				values( '$m_proveedor','$m_tipdoc', '$m_serie','$m_orden','$m_almacen',
					".$v_val_fecha."'$m_moneda','$m_tcambio','$m_formapago', 0,'$m_recargo1',
					'$m_comentario', '1' ,'$m_credito')";
//		echo $sql;
		$xsql=pg_query( $conector_id,  $sql );

		$sql="insert into COM_DETALLE ( PRO_CODIGO, NUM_TIPDOCUMENTO, NUM_SERIEDOCUMENTO, COM_CAB_NUMORDEN, ART_CODIGO,
					COM_DET_CANTIDADPEDIDA, COM_DET_PRECIO, COM_DET_DESCUENTO1, COM_DET_ESTADO )
					values ('$m_proveedor','$m_tipdoc', '$m_serie','$m_orden', '$v_art_codigo',
					".$m_cantidadpedida.",".$m_precio.", ".$m_descuento1.", '1') ";
//		echo $sql;
		$xsql=pg_query( $conector_id,  $sql );


		$sql="select PRO_CODIGO||NUM_TIPDOCUMENTO||NUM_SERIEDOCUMENTO||COM_CAB_NUMORDEN as CLAVE
						from COM_CABECERA
						where PRO_CODIGO='$m_proveedor' and NUM_TIPDOCUMENTO='$m_tipdoc' and NUM_SERIEDOCUMENTO='$m_serie' and COM_CAB_NUMORDEN='$m_orden' ";
		$xsql=pg_query( $conector_id,  $sql );
		$m_clave=pg_result($xsql,0,0);

		echo("<script>");
		echo("	location.href='cmpr_ordencom_2.php?m_clave=".$m_clave."' " );
//		echo("	location.href='cmpr_ordencom_2.php?m_proveedor=".$m_proveedor."&m_tipdoc=".$m_tipdoc."&m_serie=".$m_serie."&m_orden=".$m_orden."' " );
		echo("</script>");
		}
	else {
		echo('<script languaje="JavaScript"> ');
		echo('alert("'.$v_mensaje.'"); ');
		echo('</script>');
		}

	}

if($boton=="Eliminar")
	{
	$sqleli="delete from COM_DETALLE where
				PRO_CODIGO='$m_proveedor',
				NUM_TIPDOCUMENTO='$m_tipdoc',
				NUM_SERIEDOCUMENTO='$m_serie',
				COM_CAB_NUMORDEN='$m_orden',
				COM_CAB_ALMACEN='$m_almacen',
				ART_CODIGO='$r_articulo' ";
	$xsqleli=pg_query($conector_id,$sqleli);
	}

if($boton=="Regresar")
	{
	echo('<script languaje="JavaScript">');
	echo("	location.href='cmpr_ordencom.php' ");
	echo('</script>');
	}


if($boton=="Modificar cabecera")
	{

	$sqlupdc="update COM_CABECERA set
				PRO_CODIGO='$m_proveedor',
				NUM_TIPDOCUMENTO='$m_tipdoc',
				NUM_SERIEDOCUMENTO='$m_serie',
				COM_CAB_NUMORDEN='$m_orden',
				COM_CAB_ALMACEN='$m_almacen',
				COM_CAB_FECHAORDEN='$m_fecha',
				COM_CAB_MONEDA='$m_moneda',
				COM_CAB_TIPCAMBIO='$m_tcambio',
				COM_CAB_FORMAPAGO='$m_formapago',
				COM_CAB_IMPORDEN=0,
				COM_CAB_RECARGO1='$m_recargo1',
				COM_CAB_OBSERVACION='$m_comentario',
				COM_CAB_ESTADO='1'
				where PRO_CODIGO='$m_proveedor' and NUM_TIPDOCUMENTO='$m_tipdoc' and NUM_SERIEDOCUMENTO='$m_serie' and COM_CAB_NUMORDEN='$m_orden' ";
	$xsqlupdc=pg_query($conector_id,$sqlupdc);
	}

?>



<script language="JavaScript1.2">
var digitos=10 //cantidad de digitos buscados
var puntero=0
var buffer=new Array(digitos) //declaración del array Buffer
var cadena=""

function buscar_op(obj,objfoco){
	var letra = String.fromCharCode(event.keyCode)
	if(puntero >= digitos){
		cadena=""; 
		puntero=0; 
		} 
	//si se presiona la tecla ENTER, borro el array de teclas presionadas y salto a otro objeto... 
	if (event.keyCode == 13){ 
		borrar_buffer(); 
		if(objfoco!=0) objfoco.focus(); //evita foco a otro objeto si objfoco=0 
		} 
	//sino busco la cadena tipeada dentro del combo... 
	else
		{ 
		buffer[puntero]=letra; 
		//guardo en la posicion puntero la letra tipeada
		cadena=cadena+buffer[puntero]; //armo una cadena con los datos que van ingresando al array 
		puntero++; 
		//barro todas las opciones que contiene el combo y las comparo la cadena...
		for (var opcombo=0;opcombo < obj.length;opcombo++){ 
			if(obj[opcombo].text.substr(0,puntero).toLowerCase()==cadena.toLowerCase())
				{ 
				obj.selectedIndex=opcombo; 
				} 
			} 
		} 
		event.returnValue = false; //invalida la acción de pulsado de tecla para evitar busqueda del primer caracter 
	} 

function borrar_buffer()
	{ 
	//inicializa la cadena buscada 
	cadena=""; 
	puntero=0; 
	}
	
</script> 
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


function activa(){
	// carga de frente el formulario con el foco en diad
	document.formular.m_fecha.select()
	document.formular.m_fecha.focus()
}


</script> 
</head>
<BODY>
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<form name='formular' action="../compras/cmpr_ordencom_1.php" method="post">

<input type="hidden" name="m_tipdoc" value="01">
<input type="hidden" name="m_serie" value="<?php echo $almacen; ?>">
<input type="hidden" name="m_orden" value="<?php echo $m_orden; ?>">

<input type="hidden" name="m_tcambio" value="<?php echo $m_tcambio; ?>">
<input type="hidden" name="m_recargo1" value="<?php echo $m_recargo1; ?>">

<?php 

if (is_null($m_fecha))
	{
	$m_almacen=$almacen;
	$m_fecha=date("d/m/Y");
	$m_recargo1=0;
	$m_tcambio=0;
	$m_cantidadpedida=0;
	$m_precio=0;
	$m_descuento1=0;
	}
?>


<table border="1">
	<tr>
		<th width="500">ORDENES DE COMPRA</th>
	</tr>
</table>
<table border="1">
	<tr> 
		<th width="100" >N&deg; NUMERO </th>      
		<td>:</td>
		<td>&nbsp;<?php echo $m_orden;?></td>

		<th>FECHA</th>
		<td>:</td>
		<td>		

		<p>
		<input type="text" name="m_fecha" size="16" maxlength="10" value='<?php echo $m_fecha ; ?>'  tabindex="1" onKeyUp="javascript:validarFecha(this)" onblur="javascript:validarFecha(this)" >
		<a href="javascript:show_calendar('formular.m_fecha');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;">
	    <img src="/sistemaweb/images/show-calendar.gif" width=24 height=22 border=0></a>
		</p>


		</td>
		
	</tr>


	<tr>
		<th>ALMACEN</th>
		<td>:</td>

		<td width="118" valign="top">
		<select name="m_almacen" tabindex="2">
			<?php
			for($i=0;$i<pg_numrows($v_xsqlalma);$i++){
				$k_alma1 = pg_result($v_xsqlalma,$i,0);
				$k_alma2 = pg_result($v_xsqlalma,$i,1);
				if (trim($k_alma1)==trim($m_almacen)) {
					echo "<option value='".$k_alma1."' selected >".$k_alma1." -- ".$k_alma2." </option>";
					}
				else {
					echo "<option value='".$k_alma1."' >".$k_alma1." -- ".$k_alma2." </option>";
					}
		  		}
			?>


        </select>
		</td>


		<th>PROVEEDOR</th>
		<td>:</td>
		<?php
		if(strlen($m_proveedor)>0)
			{
			$sqlprov="select PRO_CODIGO,PRO_RAZSOCIAL
						from INT_PROVEEDORES
						where PRO_CODIGO='".$m_proveedor."' ";
			$xsqlprov=pg_query($conector_id,$sqlprov);
			if(pg_numrows($xsqlprov)>0)
				{
				$m_proveedor=pg_result($xsqlprov,0,0);
				$m_descprov=pg_result($xsqlprov,0,1);
				}
			else
				{
				echo('<script languaje="JavaScript"> ');
				echo('alert(" No Existe Proveedor !!! "); ');
				echo('</script>');
				}
			}

		?>

		<td width="300" valign="top">
		<input name="m_proveedor" type="text" size="15" maxlength="12" value="<?php echo $m_proveedor;?>"  onblur='submit()' tabindex="3">
		<input name="imgprov" type="image" src="/sistemaweb/images/help.gif" width="16" height="15" border="0" onClick="abreprov()">
		&nbsp; <?php echo $m_descprov; ?> </td>

		</td>
	</tr>
	<tr> 
		<th>MONEDA</th>
		<td>:</td>

		<td width="118" valign="top">  
		<select name="m_moneda" onblur='submit()' >
			<?php
			$v_xsqlmone=pg_exec("select tab_elemento as cod,tab_descripcion from int_tabla_general  where tab_tabla='MONE' and tab_elemento!='000000' order by cod");
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
		<td><input name="m_tcambio" type="text" value="<?php echo $m_tcambio;?>" size="5" maxlength="6" onkeyup='validarNumeroDecimales(this)'  tabindex="5">
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
		<td><input name="m_recargo1" type="text" value="<?php echo $m_recargo1;?>" size="16" maxlength="10" onkeyup='validarNumeroDecimales(this)'  tabindex="6">
		</td>


		<th>COMENTARIO</th>
		<td>:</td>
		<td><input name="m_comentario" type="text" value="<?php echo $m_comentario;?>" size="20" maxlength="20" tabindex="7">
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
			echo ("<input type='radio' name='m_credito' value='S' ".$checksi."  onclick='javascript:m_formapago.value=\" \";submit();' tabindex='8'>SI");
			echo ("<input type='radio' name='m_credito' value='N' ".$checkno."  onclick='javascript:m_formapago.value=\" \";submit();' tabindex='8'>NO");
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
		<select name="m_formapago" tabindex="2">
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
</table>



<table border="1" cellpadding="0" cellspacing="0">
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

		<th><input type="text" name="v_art_codigo" size='19' maxlength="13" value="<?php echo $v_art_codigo;?>"   onblur='submit()' onkeyup='validarNumeroEntero(this)' tabindex="10"  >
			<input name="imgarti" type="image" src="/sistemaweb/images/help.gif" width="16" height="15" border="0" onClick="abrearti()"></th>
		<td>&nbsp; <?php echo $v_art_descripcion; ?> </td>

		<th><input name="m_cantidadpedida" type="text" size='15'  maxlength="15" value="<?php echo $m_cantidadpedida;?>"  onkeyup='validarNumeroDecimales(this)'  tabindex="11"  ></th>

		<th><input name="m_precio" type="text" size='15' maxlength="15" value="<?php echo $m_precio;?>"  onkeyup='validarNumeroDecimales(this)'  tabindex="12" ></th>
		<th><input name="m_descuento1" type="text" size='15' maxlength="15" value="<?php echo $m_descuento1;?>" onkeyup='validarNumeroDecimales(this)' tabindex="13" ></th>

		<th><input type="submit" name="boton" value="Agregar" tabindex="14"></th>
	</tr>

    <?php
	$sql3="select   DET.ART_CODIGO,
					ART.ART_DESCRIPCION,
					DET.COM_DET_CANTIDADPEDIDA,
					DET.COM_DET_PRECIO,
					DET.COM_DET_DESCUENTO1,
					DET.COM_DET_IMPARTICULO
					from COM_DETALLE DET, INT_ARTICULOS ART
					where DET.ART_CODIGO=ART.ART_CODIGO and DET.PRO_CODIGO='".$m_proveedor."' and DET.NUM_TIPDOCUMENTO='".$m_tipdoc."' and DET.NUM_SERIEDOCUMENTO='".$m_serie."' and DET.COM_CAB_NUMORDEN='".$m_orden."' " ;
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
		echo "<tr>";
		echo "<td><input type='radio' name='r_articulo' value='".$ad0."'></td>";
		echo "<td>".$ad0."</td>";
		echo "<td>".$ad1."</td>";
		echo "<td><p align='right'>".$ad2."</p></td>";
		echo "<td><p align='right'>".$ad3."</p></td>";
		echo "<td><p align='right'>".$ad4."</p></td>";
		echo "<td><p align='right'>".$ad5."</p></td>";
		echo "</tr>";
		$irow3++;
		}

	?>
	<tr> 
		<td>&nbsp;</td>
		<td><input type="submit" name="boton" value="Eliminar"></td>
		<td><input type="submit" name="boton" value="Modificar cabecera">
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


