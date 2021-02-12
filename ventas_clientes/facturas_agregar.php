<?php

/*
*		CAMBIOS EN LA BASE DE DATOS PARA QUE FUNQUE ESTA OPCION...
*
*		Insertar un registro que
*		ponga por defecto el numero de serie de este formulario
*		desde el int_num_documentos;
*
*		select par_valor from int_parametros where par_nombre='cliente_defecto'
*
*/


include("../valida_sess.php");
include("../functions.php");
//include("funciones.php");
include("fac_funciones.php");
require("../clases/funciones.php");


$funcion = new class_funciones;
// crea la clase para controlar errores
$clase_error = new OpensoftError;
$clase_error->_error();
// conectar con la base de datos
$conector_id=$funcion->conectar("","","","","");


include("inc_top.php");



echo otorgarAlmacen($conector_id, $almacen)."<br>";
echo "INGRESO MANUAL DE FACTURAS";


if(strlen(trim($m_cantidadpedida))==0) {
 	$m_cantidadpedida="1";
}

if(strlen($new_almacen)==0) {
	$sql_orden="select par_valor from int_parametros where par_nombre='codes'";
	$xsql_orden=pg_query($conector_id, $sql_orden);
	if(pg_num_rows($xsql_orden)>0){
		$new_almacen=pg_result($xsql_orden,0,0);
	}
}

if(strlen($new_fecha)==0 )
        {
        $new_fecha=date("d/m/Y");
        }

if(strlen($new_moneda)==0) {
	$new_moneda = '01';
}



if(strlen(trim($new_tipo_documento))==0) {
	$new_tipo_documento="000010";
}




/*if(strlen(trim($new_serie_documento))==0)
{*/
//	$sql_orden="select par_valor from int_parametros where par_nombre='serie_fac_default'";
	$sql_orden="select num_seriedocumento, num_numactual from int_num_documentos where num_tipdocumento='".substr($new_tipo_documento,4,6)."'";
	//echo "<br>".$sql_orden."<br>";
	$xsql_orden=pg_query($conector_id, $sql_orden);
	if(pg_num_rows($xsql_orden)>0) {
		$new_serie_documento=pg_result($xsql_orden,0,0);
		$new_numero_actual = pg_result($xsql_orden,0,1);
	} else {
		echo "NRO serie no Seteado en INT_NUM_DOCUMENTOS";
		exit;
	}

if($igv=="")
{
	$query = "select tab_num_01 from int_tabla_general where tab_tabla='17' and tab_elemento='000009'";
	$xsql = pg_query($conector_id, $query);
	$igv= "1.".round(pg_result($xsql, 0, 0),0);
}

if(strlen(trim($new_tipo_cambio))==0)
{
	$query = "select round(tca_venta_libre,2) from int_tipo_cambio order by tca_fecha desc limit 1";
	$xquery = pg_query($conector_id, $query);
	$new_tipo_cambio = trim(pg_result($xquery,0,0));
}

if(strlen(trim($v_art_codigo))>0)
{
	$query = "select ART_DESCRIPCION from INT_ARTICULOS where ART_CODIGO='".$v_art_codigo."'";
	$xquery = pg_query($conector_id, $query);
	if(pg_num_rows($xquery)>0)
	{
		$c_descArticulo=pg_result($xquery,0,0);
	}
}



/*
var cant = formular.m_cantidadpedida.value
var importe = formular.m_importe.value

//alert (igv);
var neto = Math.round((importe/igv)*100)/100;
var impuesto = Math.round((importe-neto)*100)/100;
var precio = Math.round(((importe-impuesto)/cant)*10000)/10000;

formular.m_precio.value = precio
formular.m_neto.value = neto
formular.m_impuesto.value = impuesto
*/
//php

	$m_neto = round(($m_importe/$igv),2);
	$m_impuesto = round(($m_importe-$m_neto),2);
	$m_precio = round((($m_importe-$m_impuesto)/$m_cantidadpedida),4);



switch($boton)
{
	case Agregar:
		$okgraba = true;

		if($m_importe<=0) {
			$okgraba = false; $mensaje = "Error: Importe en Cero";
		}

		if(strlen(trim($new_ruc))<=0 && $new_tipo_documento=='10') {
			$okgraba = false; $mensaje = "Error: Nro Ruc Vacio";
		}

		if(strlen(trim($new_tipo_cambio))<=0) {
			$okgraba = false; $mensaje = "Error: Tipo Cambio";
		}

		if(strlen(trim($m_cantidadpedida))<=0) {
			$okgraba = false; $mensaje = "Error: Cantidad Vacio";
		}

		if(strlen(trim($new_fecha))<=0) {
			$okgraba = false; $mensaje = "Error: Fecha Vacio";
		}

		if(strlen(trim($m_precio))<=0) {
			$okgraba = false; $mensaje = "Error: Precio Vacio";
		}

		if(strlen(trim($m_neto))<=0) {
			$okgraba = false; $mensaje = "Error: Neto Vacio";
		}

		if(strlen(trim($m_impuesto))<=0) {
			$okgraba = false; $mensaje = "Error: Impuesto Vacio";
		}

		$sql = "select trim(pre_lista_precio) from fac_lista_precios
					where trim(art_codigo)='".trim($v_art_codigo)."'";
		$xsql_pre_lista = pg_query($conector_id, $sql);

		if(pg_num_rows($xsql_pre_lista)>0) {
			$pre_precio = pg_result($xsql_pre_lista,0 ,0);
		} else {
			$okgraba = false; $mensaje = "Error: Pre Lista Precio Inexistente";
		}

		/*
		if($v_art_codigo)
		{
			if (!isset($articulosFacturacion)) {
				$articulosFacturacion[$v_art_codigo]=  $m_cantidadpedida;
			}
			else {
				foreach($articulosFacturacion as $k => $v)
				{
					if ($v_art_codigo==$k)
					{
						$articulosFacturacion[$k]+=$m_cantidadpedida;
						$encontrado=1;
					}
				}
				if (!$encontrado)
				{
					$articulosFacturacion[$v_art_codigo]=$m_cantidadpedida;
				}
			}
			$v_art_codigo = "";
			$c_descArticulo = "";
			$m_cantidadpedida = "";
			$m_importe ="";
			$m_impuesto="";
			$m_neto="";
			$m_precio="";
		}
	*/

		$new_cliente=trim(pg_result(pg_query($conector_id,"select par_valor from int_parametros where par_nombre='cliente_defecto'"),0,0));
		//echo "guardar datos....";

		$new_fecha_bak= $funcion->date_format(trim($new_fecha),'YYYY-MM-DD');

		//echo "<br>".$new_tipo_documento."<br>";

		$new_tipo_documento = substr($new_tipo_documento,4,6);

		$v_art_codigo = trim($v_art_codigo);
		$ip_remote = $_SERVER['REMOTE_ADDR'];


		if($okgraba==true)
		{
				$new_numero_actual = trim(obtenerNumeroActual($conector_id, $new_tipo_documento, $new_serie_documento));

				$new_numero_actual = str_pad(trim($new_numero_actual),7,"0",STR_PAD_LEFT);

				$query = "INSERT into FAC_TA_FACTURA_CABECERA
						(CH_FAC_TIPODOCUMENTO, CH_FAC_SERIEDOCUMENTO, CH_FAC_NUMERODOCUMENTO
						,CLI_CODIGO, DT_FAC_FECHA, CH_PUNTO_VENTA
						, CH_ALMACEN, CH_FAC_MONEDA, NU_TIPOCAMBIO
						,NU_FAC_VALORBRUTO, NU_FAC_IMPUESTO1, NU_FAC_VALORTOTAL
						,CH_FAC_CREDITO, CH_FAC_FORMA_PAGO)
					VALUES

					('$new_tipo_documento', '$new_serie_documento','$new_numero_actual'
					,'$new_cliente','$new_fecha_bak','$new_serie_documento'
					,'$new_almacen','$new_moneda',$new_tipo_cambio
					,0,0,0
					,'$new_credito','".substr($new_forma_pago,4,6)."')";

					//echo $query."<br>";
					$xquery = pg_exec($conector_id, $query);

					if($xquery)
					{
					//echo 'Artículo: '.$k.' cantidad: '.$v.'<br>';//precio: '.$prec.' neto: '.$neto.' impuesto: '.$imp.' total: '.$tov.'<br>';
					/*$resultado = calcularImpuestos($conector_id, $k, $v);*/

						$query = "insert into FAC_TA_FACTURA_COMPLEMENTO
								(CH_FAC_TIPODOCUMENTO, CH_FAC_SERIEDOCUMENTO, CH_FAC_NUMERODOCUMENTO
								,CLI_CODIGO, DT_FAC_FECHA
								,CH_FAC_RUC,CH_FAC_NOMBRECLIE
								,DT_FECHACTUALIZACION, CH_USUARIO, CH_AUDITORPC)
								VALUES
								('$new_tipo_documento', '$new_serie_documento','$new_numero_actual'
								,'$new_cliente','$new_fecha_bak'
								,'$new_ruc','$new_razon'
								,now(),'$usuario','$ip_remote')";

							//echo "<br>".$query."<br>";
						$xquery_com = pg_query($conector_id, $query);

						if($xquery_com)
						{
							$act_precio = $m_precio;
							$act_neto = $m_neto;
							$act_impuesto = $m_impuesto;
							$act_importe = $m_importe;

							$query = "insert into FAC_TA_FACTURA_DETALLE
								(CH_FAC_TIPODOCUMENTO, CH_FAC_SERIEDOCUMENTO, CH_FAC_NUMERODOCUMENTO
								,CLI_CODIGO, ART_CODIGO
								,PRE_LISTA_PRECIO, NU_FAC_CANTIDAD, NU_FAC_PRECIO
								,NU_FAC_IMPORTENETO, NU_FAC_IMPUESTO1, NU_FAC_VALORTOTAL ) values
								('$new_tipo_documento','$new_serie_documento','$new_numero_actual'
								,'$new_cliente','$v_art_codigo'
								,'$pre_precio',$m_cantidadpedida,$act_precio
								,$act_neto,$act_impuesto,$act_importe)";
								//echo $query;
								$xsql_detalle = pg_query($conector_id, $query);
						}
					}
			} else {
				echo "<script language='javascript'>
							alert('$mensaje');
						</script>";
		}
		break;
	case Eliminar:
		//echo $idp;
		unset($articulosFacturacion["$idp"]);
		break;
}


if($xsql_detalle) {
	$new_tipo_documento = trim($new_tipo_documento);
	$new_serie_documento = trim($new_serie_documento);
	$new_numero_actual = trim($new_numero_actual);
	$new_cliente = trim($new_cliente);

	$m_clave = $new_tipo_documento.$new_serie_documento.$new_numero_actual.$new_cliente;

	echo '<script language="javascript">';
	//	echo 'alert("Factura Agregada")';
	echo "location.href='facturas_agregar_modif.php?m_clave=$m_clave'";
	echo '</script>';
}

?>
<script language="JavaScript" src="js/miguel.js"></script>
<script language="JavaScript" src="js/validacion.js"></script>
<script language="JavaScript" src="/sistemaweb/clases/calendario.js"></script>
<script language="JavaScript" src="/sistemaweb/clases/overlib_mini.js"></script>
<script language="JavaScript" src="/sistemaweb/compras/validacion.js"></script>
<script language="javascript">

var miPopup;

function calcularImporte(igv)
{
	//document.promo.art6.DefaultValue("UNA ASAD");
	var cant = formular.m_cantidadpedida.value
	var importe = formular.m_importe.value

	//alert (igv);
	var neto = Math.round((importe/igv)*100)/100;
	var impuesto = Math.round((importe-neto)*100)/100;
	var precio = Math.round(((importe-impuesto)/cant)*10000)/10000;

	formular.m_precio.value = precio
	formular.m_neto.value = neto
	formular.m_impuesto.value = impuesto
	/*$neto = round($m_importe/$igv,2);
	$impuesto = $m_importe-$neto;*/
}


function ponerFecha() {
	var today = new Date();
	var year = today.getYear();
	var dia = today.getDate();

	if(year<1000){
		year+=1900;
	}

	if(dia<10) {
		dia = "0"+dia;
	}

//	formular.new_fecha.value = (today.getDate() + "/" + (today.getMonth()+1) + "/" + year)
	formular.new_fecha.value = (dia + "/" + (today.getMonth()+1) + "/" + year);
//	formular.v_fecha.value = (today.getDate() + "/" + (today.getMonth()+1) + "/" + year)
}



function setearNumero(numero) {
	formular.new_numero_actual.value = numero;
}


function manejarFoco()
{
	if(formular.new_cliente.value=='') {
		formular.new_cliente.focus();
	} else {
		formular.v_art_codigo.focus();
	}
}

</script>
</head>
<body onload="manejarFoco()">
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<form name="formular" action="" method="post">
<table border="0" cellpadding="1">
	<tr>
		<td>ALMACEN
		<td>:
		<td colspan="7"><?php echo combo_Almacenes($conector_id, $new_almacen, "new_almacen", "",""); ?>

	<tr>
		<td align="right">TIPO DOC.
		<td>:
		<td><?php
		combitoTablaGeneral($conector_id, "new_tipo_documento","08",$new_tipo_documento," onChange='submit()' ");
		?>

		<td>SERIE
		<td>:
		<td><?php
				if(strlen($new_tipo_documento)==2)
				{
					$new_tipo_documento="0000".$new_tipo_documento;
				}
		 		$new_numero_actual = comboSerieDocumentos($conector_id, $new_serie_documento, "new_serie_documento", substr($new_tipo_documento,4,6), " onChange='submit();'"); ?>

		<td>NUMERO
		<td>:
		<th><?php echo $new_numero_actual; ?>


	<tr>
		<td>MONEDA
		<td>:
		<td><?php comboTablaGeneral($conector_id, "new_moneda", 'MONE', $new_moneda,""); ?>

		<td>TIPO CAMBIO
		<td>:
		<td colspan="2"><input  type="text" name="new_tipo_cambio" size="6" maxlength="6" value="<?php echo $new_tipo_cambio; ?>">

		<tr>
		<td>CREDITO
		<td>:
		<td><?php echo combosino($new_credito, "new_credito"," onChange='submit();'"); ?>

		<td>FORMA PAGO
		<td>:
		<td colspan="2"><?php echo comboFormaPago($conector_id, $new_forma_pago, "new_forma_pago", $new_credito,""); ?>

	<tr>
		<td colspan="9"><hr>

	<tr>
		<td>FECHA
		<td>:
		<td><input tabindex="1" type="text" size="14" maxlength="10" onKeyup="validarFecha(this);" name="new_fecha" value="<?php echo $new_fecha; ?>">
        <a href="javascript:show_calendar('formular.new_fecha');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;">
        <img src="/sistemaweb/images/show-calendar.gif" border="0"></a>
    <tr>
		<td>RUC
		<td>:
		<td colspan="8">
		<input tabindex="2" type="text" name="new_ruc" value="<?php echo $new_ruc; ?>" size="17" maxlength="11" onChange="javascript:mostrarProcesar('procesar_ayuda1.php',this.value,'formular.new_razon','ruc_cliente','')" onkeyup='validarNumeroDecimales(this)'>
		NOMBRE :
		<input tabindex="3" type="text" name="new_razon" size="35" maxlength="30" value='<?php echo trim($new_razon); ?>' onChange="javascript:mostrarProcesar('procesar_ayuda1.php',this.value,formular.new_ruc.value,'razon_social','')">

</table>
<table border="1" cellspacing="0" cellpadding="0">
	<tr>
		<th width="100">ARTICULO</th>
		<th>DESCRIPCION</th>
		<th>CANT.</th>
		<th>PRECIO</th>
		<th>NETO</th>
		<th>IMPUESTO</th>
		<th>TOTAL</th>
	</tr>
	<tr>

		<td><input tabindex="4" type="text" name="v_art_codigo" size="18" maxlength="13" value='<?php echo $v_art_codigo; ?>' onChange="javascript:mostrarProcesar('procesar_ayuda1.php',this.value,'formular.c_descArticulo','articulos','')" onfocus="formular.v_art_codigo.select()">
		<!-- <input name="imgprov" type="image" src="/sistemaweb/images/help.gif" width="16" height="15" onClick="javascript:mostrarAyuda('lista_ayuda2.php','formular.v_art_codigo','formular.c_descArticulo','articulos')"> -->
		<img src="/sistemaweb/images/help.gif" width="16" height="15"  onClick="javascript:mostrarAyuda('/sistemaweb/maestros/ayuda/lista_ayuda.php','formular.v_art_codigo','formular.c_descArticulo','articulos')">
		<td><input type="text" name="c_descArticulo" size="45" readonly="true" value='<?php echo $c_descArticulo; ?>'></td>

		<th><input tabindex="5" name="m_cantidadpedida" type="text" size='7' maxlength="6" value="<?php echo $m_cantidadpedida;?>"  onkeyup='validarNumeroDecimales(this)' onBlur="javascript:calcularImporte(<?php echo $igv; ?>)" onfocus="formular.m_cantidadpedida.select()"></th>

		<th><input readonly name="m_precio" type="text" size='10' maxlength="9" value="<?php echo $m_precio;?>"  onkeyup='validarNumeroDecimales(this)'></th>
		<th><input readonly name="m_neto" type="text" size='8' maxlength="6" value="<?php echo $m_neto;?>" onkeyup='validarNumeroDecimales(this)'></th>
		<th><input readonly name="m_impuesto" type="text" size='8' maxlength="6" value="<?php echo $m_impuesto;?>" onkeyup='validarNumeroDecimales(this)'></th>

		<th><input tabindex="6" name="m_importe" type="text" size='10'  maxlength="10" value="<?php echo $m_importe;?>" onkeyup='validarNumeroDecimales(this)' onBlur="javascript:calcularImporte(<?php echo $igv; ?>)" onfocus="formular.m_importe.select();"></th>
		<th><input tabindex="7" type="submit" name="boton" value="Agregar" ></th>

</table>


<?php/*
if (isset($articulosFacturacion))
{
	echo 'ARTICULOS INGRESADOS:<br>';
	echo '<table border="1" cellspacing="0" cellpadding="0">
			<tr>
				<th width="25">&nbsp;
				<th width="74">ARTICULO
				<th>DESCRIPCION
				<th>CANT.
				<th>PRECIO
				<th>NETO
				<th>IMPUESTO
				<th>TOTAL';

	$total_neto = 0;
	$total_impuesto = 0;
	$total_importe = 0;

	foreach($articulosFacturacion as $k => $v)
	{
		//echo 'Artículo: '.$k.' cantidad: '.$v.'<br>';//precio: '.$prec.' neto: '.$neto.' impuesto: '.$imp.' total: '.$tov.'<br>';
		$resultado = calcularImpuestos($conector_id, $k, $v);
		$act_precio = $resultado[0];
		$act_neto = $resultado[1];
		$act_impuesto = $resultado[2];
		$act_importe = $resultado[3];

		$total_neto += $act_neto;
		$total_impuesto += $act_impuesto;
		$total_importe += $act_importe;

		echo "
			<tr>
				<td><input type='radio' name='idp' value='$k'>
				<td>$k
				<td width='230'>".obtenerDescripcion($conector_id, $k)."
				<td align='right'>$v
				<td align='right'>$act_precio
				<td align='right'>$act_neto
				<td align='right'>$act_impuesto
				<td align='right'>$act_importe";
	}
	echo "
		<tr>
			<td><td align='center'><input type='submit' name='boton' value='Eliminar'>
			<td>&nbsp;<th colspan='2'>TOTALES :";
	echo "
			<th align='right'><input type='hidden' name='total_neto' value='$total_neto'>$total_neto
			<th align='right'><input type='hidden' name='total_impuesto' value='$total_impuesto'>$total_impuesto
			<th align='right'><input type='hidden' name='total_importe' value='$total_importe'>$total_importe";
}
?>
	<tr>
		<td>
		<td><td>
<!--		<td colspan="5" align="center"><input type="submit" name="boton" value="Guardar">  --->
</table>

*/ ?>
</form>
<script language="javascript">
	calcularImporte(<?php echo $igv; ?>);
</script>
</body>
</html>
<?php
pg_close($conector_id);
