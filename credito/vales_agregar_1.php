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
//	$sql="select int_sp_numero_documento_ins( '$m_tipdoc', '$m_serie' )";
//	$xsql=pg_query( $conector_id,  $sql );
//	$m_orden=pg_result($xsql, 0, 0 );
//	echo $m_orden;
//	$m_proveedor=trim($m_proveedor);
//	$m_tipdoc=trim($m_tipdoc);
//	$m_serie=trim($m_serie);

//VARIABLES DEL PRIMER FORMULARIO
	$m_almacen=trim($m_almacen);
	//$fecha_val=trim($fecha_val);
	$fecha_val=$funcion->date_format($fecha_val,'YYYY-MM-DD');
	$cod_cliente=trim($cod_cliente);
	$nro_vale=trim($nro_vale);
	$cod_planilla=trim($cod_planilla);
	$nro_placa=trim($nro_placa);
	$odometro=trim($odometro);
//VARIABLES DEL SEGUNDO FORMULARIO


	//$cod_vale=trim($cod_vale);
	$v_art_codigo=trim($v_art_codigo);
	$m_cantidadpedida=trim($m_cantidadpedida);
	$m_precio=trim($m_precio);
//	$m_tcambio=round($m_tcambio,2);
//	$m_recargo1=round($m_recargo1,2);

	$okgraba=true;

//  DE AQUI EN ADELANTE ES PARA VALIDAR LOS DATOS DEL FORMULARIO,
// okgraba DEFINE SI SE INGRESA O NO


	if($m_precio<0)
	{
		$v_mensaje=" No se puede Agregar \\n Precio Vacio !!! ";	$okgraba=false;
	}

	if(($m_cantidadpedida*$m_precio)<=0)
	{
		$v_mensaje=" No se puede Agregar \\n Subtotal en Cero !!! ";	$okgraba=false;
	}

	if(strlen($nro_vale)<=0)
	{
		$v_mensaje=" No se puede Agregar \\n Nro de Vale Vacio !!! ";	$okgraba=false;
	}

	if($odometro<=0)
	{
		$odometro=0;
	}

//ESTA PARTE VALIDA LA EXISTENCIA DE OTRO VALE DE CREDITO EN LA CABECERA

	$sql_pk="select CH_SUCURSAL||DT_FECHA||CH_DOCUMENTO as CLAVE
			from val_ta_cabecera
			where CH_SUCURSAL='$m_almacen' and DT_FECHA='$fecha_val' and CH_DOCUMENTO='$nro_vale' ";
	echo "ESTE EL PK : ".$sql_pk;
		$xsql_pk=pg_query( $conector_id,  $sql_pk );
	if(pg_numrows($xsql_pk)>0)
	{
		$v_mensaje=" No se puede Agregar \\n Ya existe un Vale de Credito con los mismos datos !!! "; $okgraba=false;
	}
//AQUI TERMINA ESTA VALIDACION












	if(strlen($cod_cliente)>0)
		{

		// verificar si existe el cliente y captura la descripcion del cliente

		$xsqlprov=pg_query($conector_id,"select CLI_CODIGO, CLI_RAZSOCIAL
										 from INT_CLIENTES where CLI_CODIGO='".$cod_cliente."' ");
		if(pg_numrows($xsqlprov)>0)
			{
			$cod_cliente=pg_result($xsqlprov,0,0);
			$desc_cliente=pg_result($xsqlprov,0,1);
			}
		else
			{
			$v_mensaje=" No se puede Agregar \\n No Existe Cliente !!! ";	$okgraba=false;
			}
		}
	else
		{
			$v_mensaje=" No se puede Agregar \\n Cliente Vacio !!! ";	$okgraba=false;
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
		if (strlen($fecha_val) > 0)
			{
			$fecha_val=$funcion->date_format($fecha_val,'YYYY-MM-DD');
			}
		else
			{
			$fecha_val=" ";
			}

			$sql="insert into val_ta_cabecera ( CH_SUCURSAL, DT_FECHA, CH_CLIENTE, CH_DOCUMENTO,
						CH_PLANILLA, CH_PLACA, NU_ODOMETRO, CH_ESTADO)
						values( '$m_almacen','$fecha_val', '$cod_cliente','$nro_vale',
						'$cod_planilla', '$nro_placa',$odometro,1)";
			echo "este es el insert de cabecera : ".$sql."<BR>";

			$xsql= pg_query( $conector_id,  $sql );
	/*		if($xsql)
				{
				echo('<script languaje="JavaScript"> ');
				echo('alert("Error en Ingreso"); ');
				echo('</script>');
				}
			else
			{*/


			$sql="INSERT INTO val_ta_detalle (CH_SUCURSAL, DT_FECHA, CH_DOCUMENTO, CH_ARTICULO,
			  		NU_CANTIDAD, NU_IMPORTE, CH_ESTADO)
			  		values ('$m_almacen', '$fecha_val', '$nro_vale', '$v_art_codigo','$m_cantidadpedida', '$m_precio', 1) ";
			echo "este es el insert de detalle : ".$sql."<BR>";
			$xsql=pg_query( $conector_id,  $sql);

			$sql="select CH_SUCURSAL||DT_FECHA||CH_DOCUMENTO as CLAVE
							from val_ta_cabecera
							where CH_SUCURSAL='$m_almacen' and DT_FECHA='$fecha_val' and CH_DOCUMENTO='$nro_vale' ";
			$xsql=pg_query( $conector_id,  $sql );
			$m_clave=pg_result($xsql,0,0);


			echo("<script>");
			echo("	location.href='vales_agregar_2.php?m_clave=".$m_clave."' " );
//			echo("	location.href='cmpr_ordencom_2.php?m_proveedor=".$m_proveedor."&m_tipdoc=".$m_tipdoc."&m_serie=".$m_serie."&m_orden=".$m_orden."' " );
			echo("</script>");}

	else
		{
			echo('<script languaje="JavaScript"> ');
			echo('alert("'.$v_mensaje.'"); ');
			echo('</script>');
		}
	}

/*if($boton=="Eliminar")
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
*/
if($boton=="Regresar")
	{
	echo('<script languaje="JavaScript">');
	echo("	location.href='cmpr_ordencom.php' ");
	echo('</script>');
	}

/*
if($boton=="Modificar cabecera")
	{

	$sqlupdc="update COM_CABECERA set
				PRO_CODIGO='$m_proveedor',
				NUM_TIPDOCUMENTO='$m_tipdoc',
				NUM_SERIEDOCUMENTO='$m_serie',
				COM_CAB_NUMORDEN='$m_orden',
				COM_CAB_ALMACEN='$m_almacen',
				COM_CAB_FECHAORDEN='$fecha_val',
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
*/
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

<script language="JavaScript" src="js/miguel.js"></script>
<script language="JavaScript" src="/sistemaweb/clases/reloj.js"></script>
<script language="JavaScript" src="/sistemaweb/compras/valfecha.js"></script>

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


function activa(){
	// carga de frente el formulario con el foco en diad
	document.formular.fecha_val.select()
	document.formular.fecha_val.focus()
}


</script>
</head>
<BODY>
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<form name='formular' action="vales_agregar_1.php" method="get">

<?php
/* <input type="hidden" name="m_tipdoc" value="01">
<input type="hidden" name="m_serie" value="<?php echo $almacen; ?>">
<input type="hidden" name="m_orden" value="<?php echo $m_orden; ?>">

<input type="hidden" name="m_tcambio" value="<?php echo $m_tcambio; ?>">
<input type="hidden" name="m_recargo1" value="<?php echo $m_recargo1; ?>">
*/
?>
<?php

if (is_null($fecha_val))
	{
	$m_almacen=$almacen;
	$fecha_val=date("d/m/Y");
	//$m_recargo1=0;
	//$m_tcambio=0;
	$m_cantidadpedida=0;
	$m_precio=0;
	//$m_descuento1=0;
	}
?>


<table border="0">
	<tr>
	<th width="400">VALES DE CREDITO</th>
	</tr>
</table>


<table border="0">

	<tr>
		<th width="100">PUNTO DE VENTA</th>
		<td>:</td>

		<td width="250" valign="top">
		<select name="m_almacen">
			<?php
			for($i=0;$i<pg_numrows($v_xsqlalma);$i++){
				$k_alma1 = pg_result($v_xsqlalma,$i,0);
				$k_alma2 = pg_result($v_xsqlalma,$i,1);
				if (trim($k_alma1)==trim($m_almacen)) {
					echo "<option value='".$k_alma1."' selected>".$k_alma1." -- ".$k_alma2." </option>";
					}
				else {
					echo "<option value='".$k_alma1."' >".$k_alma1." -- ".$k_alma2." </option>";
					}
		  		}
			?>
       	</select>
	<tr>
		</td>

		<th>FECHA</th>
		<td>:</td>
		<td>

		<p>
		<input type="text" name="fecha_val" size="16" maxlength="10" value='<?php echo $fecha_val ; ?>'   onKeyUp="javascript:validarFecha(this)" onblur="javascript:validarFecha(this)" >
		<a href="javascript:show_calendar('formular.fecha_val');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;">
	    <img src="/sistemaweb/images/show-calendar.gif" width=24 height=22 border=0></a>
		</p>


		</td>

	<TR>
		<th>CLIENTE</th>
		<td>:</td>
		<?php
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
			else
				{
				echo('<script languaje="JavaScript"> ');
				echo('alert(" No Existe Cliente !!! "); ');
				echo('</script>');
				}
			}

		?>

		<td valign="top">

		<!-- ESTA PARTE ES PARA COLOCAR LA AYUDA DE LOS CLIENTES

		<input type="text" name="cod_cliente"  maxlength="12" value='<?php echo $cod_cliente ; ?>' readonly="true">
		<input name="imgprov" type="image" src="/sistemaweb/images/help.gif" width="16" height="15" onClick="javascript:mostrarAyuda('lista_ayuda.php','formular.cod_cliente','formular.cod_cliente')">
 -->
		<input name="cod_cliente" type="text" size="15" maxlength="12" value="<?php echo $cod_cliente;?>" >
		<input name="imgprov" type="image" src="/sistemaweb/images/help.gif" width="16" height="15" border="0" onClick="abrecliente()">
		&nbsp; <?php echo $desc_cliente; ?> </td>

		<!-- ESTA PARTE CONCLUYE LA AYUDA DE CLIENTE -->
		</td>
	</tr>
	<tr>

		<th>NUMERO DE VALE</th>
		<td>:</td>
		<td><input type="text" name="nro_vale" maxlength="10" value="<?php echo $nro_vale;?>"></td>


		<th>COD. PLANILLA</th>
		<td>:</td>
		<td valign="top">

		<!-- ESTA PARTE ES PARA COLOCAR LA AYUDA DE LA PLANILLA -->

		<input name="cod_planilla" type="text" size="15" maxlength="10" value="<?php echo $cod_planilla;?>" >
		<input name="imgprov" type="image" src="/sistemaweb/images/help.gif" width="16" height="15" border="0" onClick="abreprov()">
		&nbsp; <?php //echo $desc_cliente; ?> </td>

		<!-- ESTA PARTE CONCLUYE LA AYUDA DE LA PLANILLA -->
		</td>


	<TR>

		<th>NRO. PLACA</th>
		<td>:</td>
		<td><input type="text" name="nro_placa"  maxlength="10" value="<?php echo $nro_placa;?>"></td>

		<th>ODOMETRO</th>
		<td>:</td>
		<td><input type="text" name="odometro" maxlength="15" value="<?php echo $odometro;?>"></td>

	<?php /*


*/
?>
</table>


<table border="1" cellpadding="0" cellspacing="0">
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
		<td>&nbsp; <?php echo $v_art_descripcion; ?> </td>

		<th><input name="m_cantidadpedida" type="text" size='15'  maxlength="15" value="<?php echo $m_cantidadpedida;?>"  onkeyup='validarNumeroDecimales(this)'  ></th>
		<th><input name="m_precio" type="text" size='15' maxlength="15" value="<?php echo $m_precio;?>"  onkeyup='validarNumeroDecimales(this)'  ></th>

		<th><input type="submit" name="boton" value="Agregar" ></th>
	</tr>

    <?php

// AQUI ESTA EL SELECT PARA MOSTRAR LOS DATOS
// DE LOS DETALLES DE LOS VALES DE CREDITO

	$sql3="select   DET.CH_ARTICULO,
					ART.ART_DESCRIPCION,
					DET.NU_CANTIDAD,
					DET.NU_IMPORTE

					from VAL_TA_DETALLE DET, INT_ARTICULOS ART
					where DET.CH_ARTICULO=ART.ART_CODIGO
					and DET.CH_SUCURSAL='".$m_almacen."'
					and DET.DT_FECHA='".$fecha_val."'
					and DET.CH_ARTICULO='".$v_art_codigo."'" ;
//echo $sql3;

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
		<td>&nbsp;</td>
		<td>
		<!-- <td><input type="submit" name="boton" value="Eliminar"></td>
		<td><input type="submit" name="boton" value="Modificar cabecera">-->
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


