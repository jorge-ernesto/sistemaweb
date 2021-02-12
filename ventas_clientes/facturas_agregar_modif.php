<?php
//echo $_REQUEST["m_clave"] ;
//echo $m_clave;
/*
*	CAMBIOS EN LA BASE DE DATOS PARA QUE FUNQUE ESTA OPCION...
*
*		Insertar un registro que
*		ponga por defecto el numero de serie de este formulario
*		desde el int_num_documentos;
*/
//echo "variable oficina ".$oficina ;

if($boton=="Terminar")
{  if ($programa=="facturas-oficina_principal")
	header('Location:facturas-oficina_principal.php');
   else  
	header('Location:facturas_principal.php');
	
   exit;
}

include("../valida_sess.php");
include("../functions.php");
//include("funciones.php");
include("fac_funciones.php");
require("../clases/funciones.php");


$funcion = new class_funciones;
// crea la clase para controlar errores
$clase_error = new OpensoftError;

// conectar con la base de datos
$conector_id=$funcion->conectar("","","","","");


include("inc_top.php");
echo otorgarAlmacen($conector_id, $almacen)."<br>";
echo "INGRESO MANUAL DE FACTURAS";



//$m_clave='10002033A-0001';

$sql_mostrar = "SELECT
					trim(dt_fac_fecha), trim(ch_fac_tipodocumento), trim(ch_fac_seriedocumento)
					, trim(ch_fac_numerodocumento), trim(cli_codigo)
					, trim(ch_almacen), trim(ch_fac_moneda), round(nu_tipocambio,2)
					, ch_fac_credito, ch_fac_forma_pago
				FROM fac_ta_factura_cabecera
				WHERE
				ch_fac_tipodocumento||trim(ch_fac_seriedocumento)||ch_fac_numerodocumento||trim(cli_codigo)='$m_clave' ";

$xsql_mostrar = pg_query($conector_id, $sql_mostrar);
$result = pg_fetch_array($xsql_mostrar,0);


$new_fecha = $result[0];
$new_fecha=$funcion->date_format($result[0],'DD/MM/YYYY');
$new_tipo_documento = $result[1];
$new_serie_documento = $result[2];
$new_numero_actual = $result[3];

$new_cliente = $result[4];
$new_almacen = $result[5];


$new_moneda = $result[6];
$new_tipo_cambio = $result[7];
$new_credito = $result[8];
$new_forma_pago = $result[9];

$sql_mostrar_ruc = "select ch_fac_ruc, ch_fac_nombreclie from fac_ta_factura_complemento
								where
								ch_fac_tipodocumento||trim(ch_fac_seriedocumento)||ch_fac_numerodocumento||trim(cli_codigo)='$m_clave' ";
//echo $sql_mostrar_ruc;
$xsql_mostrar_ruc = pg_query($conector_id, $sql_mostrar_ruc);
if(pg_num_rows($xsql_mostrar_ruc)>0)
{
	$new_ruc = pg_result($xsql_mostrar_ruc,0,0);
	$new_razon = pg_result($xsql_mostrar_ruc,0,1);
}

$v_estado="disabled";

if(strlen(trim($new_tipo_documento))==0) {
	$new_tipo_documento="000010";
}

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

if(strlen($new_moneda)==0) {
	$new_moneda = '01';
}

if(strlen(trim($new_serie_documento))==0)
{
	$sql_orden="select par_valor from int_parametros where par_nombre='serie_fac_default'";
	$xsql_orden=pg_query($conector_id, $sql_orden);
	if(pg_num_rows($xsql_orden)>0) {
	$new_serie_documento=pg_result($xsql_orden,0,0);
	} else {
		echo "NRO serie no Seteado en INT_PARAMETROS";
		exit;
	}
}



if($igv=="")
{
	$query = "select tab_num_01 from int_tabla_general where tab_tabla='17' and tab_elemento='000009'";
	$xsql = pg_query($conector_id, $query);
	$igv= "1.".round(pg_result($xsql, 0, 0),0);
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


$m_neto = round(($m_importe/$igv),2);
$m_impuesto = round(($m_importe-$m_neto),2);
$m_precio = round((($m_importe-$m_impuesto)/$m_cantidadpedida),4);


switch($boton)
{
	case Agregar:
//		echo "aqui codigo pa' agregar --- m_clave".$m_clave_det;

		$sql = "select trim(pre_lista_precio) from fac_lista_precios
		where art_codigo='$v_art_codigo'";
		$pre_precio = pg_result(pg_query($conector_id, $sql),0 ,0);

		$act_precio = $m_precio;
		$cantidad = $m_cantidadpedida ;
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

			,'$pre_precio',$cantidad,$act_precio
			,$act_neto,$act_impuesto,$act_importe)";

//		echo "<br>".$query;

			$v_art_codigo = "";
			$c_descArticulo= "";

			$m_cantidadpedida = "";
			$m_precio = "";
			$m_neto = "";
			$m_impuesto = "";
			$m_importe = "";
			pg_exec($conector_id, $query);
		break;


	case Eliminar:
		//echo "aqui codigo pa' eliminar".$m_clave_det;
		//echo $idp;
		$sql_del = "DELETE from fac_ta_factura_detalle
					WHERE
					trim(ch_fac_tipodocumento)||trim(ch_fac_seriedocumento)||trim(ch_fac_numerodocumento)||trim(cli_codigo)||trim(art_codigo)='$m_clave_det'";
		pg_exec($conector_id, $sql_del);
		break;

		
	case Guardar:
		echo "guardar datos.-..";

		//$new_numero_actual = trim(obtenerNumeroActual($conector_id, $new_tipo_documento, $new_serie_documento));

		$new_fecha_bak= $funcion->date_format(trim($new_fecha),'YYYY-MM-DD');

		$query = "insert into FAC_TA_FACTURA_CABECERA
				(CH_FAC_TIPODOCUMENTO, CH_FAC_SERIEDOCUMENTO, CH_FAC_NUMERODOCUMENTO
				,CLI_CODIGO, DT_FAC_FECHA, CH_PUNTO_VENTA
				, CH_ALMACEN, CH_FAC_MONEDA, NU_TIPOCAMBIO

				,NU_FAC_VALORBRUTO, NU_FAC_IMPUESTO1, NU_FAC_VALORTOTAL

				,CH_FAC_CREDITO, CH_FAC_FORMA_PAGO)

				VALUES

				('$new_tipo_documento', '$new_serie_documento','$new_numero_actual'
				,'$new_cliente','$new_fecha_bak','$new_almacen'
				,'$new_almacen','$new_moneda',$new_tipo_cambio

				,$total_neto,$total_impuesto,$total_importe
				,'$new_credito','".substr($new_forma_pago,0,2)."')";

			//echo $query."<br>";
			$xquery = pg_exec($conector_id, $query);

			//$xquery = false;
			if($xquery)
			{
				foreach($articulosFacturacion as $k => $v)
				{
					//echo 'Artículo: '.$k.' cantidad: '.$v.'<br>';//precio: '.$prec.' neto: '.$neto.' impuesto: '.$imp.' total: '.$tov.'<br>';
					$resultado = calcularImpuestos($conector_id, $k, $v);
					$act_precio = $resultado[0];
					$act_neto = $resultado[1];
					$act_impuesto = $resultado[2];
					$act_importe = $resultado[3];

					$sql = "select trim(pre_lista_precio) from fac_lista_precios
							where art_codigo='$k'";

					$pre_precio = pg_result(pg_query($conector_id, $sql),0 ,0);

					$query = "insert into FAC_TA_FACTURA_DETALLE
						(CH_FAC_TIPODOCUMENTO, CH_FAC_SERIEDOCUMENTO, CH_FAC_NUMERODOCUMENTO
						,CLI_CODIGO, ART_CODIGO

						,PRE_LISTA_PRECIO, NU_FAC_CANTIDAD, NU_FAC_PRECIO
						,NU_FAC_IMPORTENETO, NU_FAC_IMPUESTO1, NU_FAC_VALORTOTAL ) values

						('$new_tipo_documento','$new_serie_documento','$new_numero_actual'
						,'$new_cliente','$k'

						,'$pre_precio',$v,$act_precio
						,$act_neto,$act_impuesto,$act_importe)";

			//		echo "<br>".$query;
			//		$xsql_detalle = pg_query($conector_id, $query);
					/*echo "
						<tr>
							<td><input type='radio' name='idp' value='$k'>
							<td>$k
							<td width='230'>".obtenerDescripcion($conector_id, $k)."
							<td align='right'>$v
							<td align='right'>$act_precio
							<td align='right'>$act_neto
							<td align='right'>$act_impuesto
							<td align='right'>$act_importe";*/

				}
				unset($articulosFacturacion);
    		}
			else {
				echo "ERROR EN CABECEAR";
			}
		//unset($articulosFacturacion["$idp"]);
		break;
}

if($xsql_detalle){
	echo '<script language="javascript">';
//	echo 'alert("Factura Agregada")';
	echo "location.href='facturas_principal.php';";
	echo '</script>';
}

$_SESSION['articulosFacturacion']=$articulosFacturacion;


?>
<head>
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

function ponerFecha(){
	var today = new Date()
	var year = today.getYear()
	if(year<1000) year+=1900

	if(dia<10){ dia = "0"+dia; }

//	formular.fecha_val.value = (today.getDate() + "/" + (today.getMonth()+1) + "/" + year);
	formular.fecha_val.value = (dia + "/" + (today.getMonth()+1) + "/" + year);
//	formular.v_fecha.value = (today.getDate() + "/" + (today.getMonth()+1) + "/" + year)

}

function setearNumero(numero)
{
	formular.new_numero_actual.value = numero;
}

function manejarFoco()
{
	if(formular.new_cliente.value=='')
	{
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
		<td align="right">TIPO DOC.
		<td>:
		<td><?php
		combitoTablaGeneral($conector_id, "new_tipo_documento","08",$new_tipo_documento,"onChange='submit()' $v_estado");
		?>

		<td>SERIE
		<td>:
		<td><?php comboSerieDocumentos($conector_id, $new_serie_documento, "new_serie_documento", $new_tipo_documento, "onChange='submit();' $v_estado "  ); ?>

		<td>NUMERO
		<td>:
		<th><?php echo $new_numero_actual; ?>
<tr>
		<td>MONEDA
		<td>:
		<td><?php comboTablaGeneral($conector_id, "new_moneda", 'MONE', $new_moneda, $v_estado); ?>

		<td>TIPO CAMBIO
		<td>:
		<td colspan="2"><input <?php echo $v_estado; ?> type="text" name="new_tipo_cambio" size="6" maxlength="6" value="<?php echo $new_tipo_cambio; ?>">

	    <tr>
		<td>CREDITO
		<td>:
		<td><?php echo combosino($new_credito, "new_credito", "onChange='submit();' $v_estado"); ?>

		<td>FORMA PAGO
		<td>:
		<td colspan="2"><?php echo comboFormaPago($conector_id, $new_forma_pago, "new_forma_pago", $new_credito, $v_estado); ?>


	    <tr>
		<td>ALMACEN
		<td>:
		<td colspan="7"><?php echo combo_Almacenes($conector_id, $new_almacen, "new_almacen", " where ch_clase_almacen='1' ",$v_estado); ?>


	<tr>
		<td colspan="7"><hr>

	<tr>
		<td>FECHA
		<td>:
		<td><input <?php echo $v_estado; ?> type="text" size="10" maxlength="10" onKeyup="validarFecha(this);" name="new_fecha" value="<?php echo $new_fecha; ?>">
		<a href="javascript:show_calendar('formular.new_fecha');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;">
		<img src="/sistemaweb/images/show-calendar.gif" width="20" height="15" border="0"></a>

	<tr>
		<td>RUC
		<td>:
		<td colspan="8">
		<input type="text" name="new_ruc" value="<?php echo $new_ruc; ?>" size="12" maxlength="11" onChange="javascript:mostrarProcesar('procesar_ayuda1.php',this.value,'formular.new_razon','ruc_cliente','')" onkeyup='validarNumeroDecimales(this)' <?php echo $v_estado; ?>>
		NOMBRE :
		<input type="text" name="new_razon" size="35" maxlength="40" value='<?php echo trim($new_razon); ?>' onChange="javascript:mostrarProcesar('procesar_ayuda1.php',this.value,formular.new_ruc.value,'razon_social','')" <?php echo $v_estado; ?>>


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


		<td><input tabindex="1" type="text" name="v_art_codigo" size="15" maxlength="13" value='<?php echo $v_art_codigo; ?>' onChange="javascript:mostrarProcesar('procesar_ayuda1.php',this.value,'formular.c_descArticulo','articulos','')" onfocus="formular.v_art_codigo.select()">
		<img src="/sistemaweb/images/help.gif" width="16" height="15"  onClick="javascript:mostrarAyuda('/sistemaweb/maestros/ayuda/lista_ayuda.php','formular.v_art_codigo','formular.c_descArticulo','articulos')">
		<td><input type="text" name="c_descArticulo" size="45" readonly="true" value='<?php echo $c_descArticulo; ?>'></td>


		<th><input tabindex="2" name="m_cantidadpedida" type="text" size='8' maxlength="6" value="<?php echo $m_cantidadpedida;?>"  onkeyup='validarNumeroDecimales(this)' onBlur="javascript:calcularImporte(<?php echo $igv; ?>)" onfocus="formular.m_cantidadpedida.select()"></th>
		<th><input readonly name="m_precio" type="text" size='8' maxlength="4" value="<?php echo $m_precio;?>"  onkeyup='validarNumeroDecimales(this)'></th>


		<th><input readonly name="m_neto" type="text" size='8' maxlength="4" value="<?php echo $m_neto;?>"  onkeyup='validarNumeroDecimales(this)' ></th>
		<th><input readonly name="m_impuesto" type="text" size='7' maxlength="4" value="<?php echo $m_impuesto;?>"  onkeyup='validarNumeroDecimales(this)' ></th>


		<th><input tabindex="3"  name="m_importe" type="text" size='8'  maxlength="10" value="<?php echo $m_importe;?>" onBlur="javascript:calcularImporte(<?php echo $igv; ?>)" onfocus="formular.m_importe.select()"></th>
		<th><input tabindex="4" type="submit" name="boton" value="Agregar" ></th>
</table>
<?php
$sql_mostrar_detalles = "
	SELECT
	ch_fac_tipodocumento||trim(ch_fac_seriedocumento)||ch_fac_numerodocumento||trim(cli_codigo)||trim(det.art_codigo)
	,det.art_codigo, art.art_descripcion, nu_fac_cantidad, round(nu_fac_precio,2), round(nu_fac_importeneto,2),round(nu_fac_impuesto1,2), round(nu_fac_valortotal,2)
	FROM fac_ta_factura_detalle det, int_articulos art
	WHERE
	ch_fac_tipodocumento||trim(ch_fac_seriedocumento)||ch_fac_numerodocumento||trim(cli_codigo)='$m_clave'
	and det.art_codigo=art.art_codigo";

// echo $sql_mostrar_detalles ;
$xsql_mostrar_detalles = pg_query($conector_id, $sql_mostrar_detalles);

if (pg_num_rows($xsql_mostrar_detalles)>0)
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

	$i = 0;
	$total_neto = 0;
	$total_impuesto = 0;
	$total_importe = 0;

	while($i<pg_num_rows($xsql_mostrar_detalles))
	{
		//echo 'Artículo: '.$k.' cantidad: '.$v.'<br>';//precio: '.$prec.' neto: '.$neto.' impuesto: '.$imp.' total: '.$tov.'<br>';
		$rs = pg_fetch_array($xsql_mostrar_detalles);

		$m_clave_det = $rs[0];
		$articulo = $rs[1];
		$articulo_descripcion = $rs[2];

		$cantidad = $rs[3];
		$act_precio = $rs[4];
		$act_neto = $rs[5];
		$act_impuesto = $rs[6];
		$act_importe = $rs[7];

		$total_neto += $act_neto;
		$total_impuesto += $act_impuesto;
		$total_importe += $act_importe;

		echo "
			<tr>
				<td><input type='radio' name='m_clave_det' value='$m_clave_det'>
				<td>$articulo
				<td width='230'>$articulo_descripcion
				<td align='right'>$cantidad
				<td align='right'>$act_precio
				<td align='right'>$act_neto
				<td align='right'>$act_impuesto
				<td align='right'>$act_importe";
		$i++;
	}
	echo "
		<tr>
			<td><td align='center'><input type='submit' name='boton' value='Eliminar'>
			<td>&nbsp;<th colspan='2'>TOTALES :";
	echo "
			<th align='right'><input type='hidden' name='total_neto' value='$total_neto'>$total_neto
			<th align='right'><input type='hidden' name='total_impuesto' value='$total_impuesto'>$total_impuesto
			<th align='right'><input type='hidden' name='total_importe' value='$total_importe'>$total_importe";

	$sql_upd = "update fac_ta_factura_cabecera set
							NU_FAC_VALORBRUTO='$total_neto'
							, NU_FAC_IMPUESTO1='$total_impuesto'
							, NU_FAC_VALORTOTAL='$total_importe'
						where
							trim(ch_fac_tipodocumento)||trim(ch_fac_seriedocumento)||trim(ch_fac_numerodocumento)||trim(cli_codigo)='$m_clave'
							";
//		echo $sql_upd;
	pg_exec($conector_id, $sql_upd);
}
?>
	<tr>
		<td>
		<td><td>
		<td colspan="5" align="center"><input tabindex="5" type="submit" name="boton" value="Terminar">
</table>


</form>
<script language="javascript">
	calcularImporte(<?php echo $igv; ?>);
</script>
</body>
</html>
<?php
$clase_error->_error();
 pg_close($conector_id); ?>
