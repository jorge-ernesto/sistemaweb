<?php
//include("../valida_sess.php");
include("../menu_princ.php");
include("../include/functions.php");
require("../clases/funciones.php");
$funcion = new class_funciones;

// crea la clase para controlar errores
$clase_error = new OpensoftError;

// conectar con la base de datos
$conector_id=$funcion->conectar("","","","","");

if ( is_null($almacen) or trim($almacen)==""){
	$almacen="001";
}

if($conector_id){
	$v_xsqlalma	= pg_exec($conector_id, "SELECT trim(ch_almacen) as cod, ch_nombre_almacen FROM inv_ta_almacenes  WHERE ch_clase_almacen='1' ORDER by cod");
	$k_var		= pg_fetch_row($v_xsqlalma,0);
	$k_almacen	= trim($k_var[0]);
	$k_almacen_desc	= $k_var[1];	
}

if($boton=="Ins" or $boton=="Agregar"){
	//carga el ultimo numero orden de compra en que se quedo la tabla de numeradores de documentos

	//VARIABLES DEL PRIMER FORMULARIO
    	$m_almacen			= trim($_REQUEST['m_almacen']);
    	$fecha_val			= $funcion->date_format($_REQUEST['fecha_val'],'YYYY-MM-DD');
    	$cod_cliente		= trim($_REQUEST['cod_cliente']);
    	$nro_vale			= trim($_REQUEST['numvale']);
    	$nro_placa			= trim($_REQUEST['nro_placa']);
    	$odometro			= trim($_REQUEST['odometro']);
    	$tarjeta			= trim($_REQUEST['tarjeta']);
    	$cajaa				= trim($_REQUEST['cajaa']);
    	$lado				= trim($_REQUEST['lado']);
    	$turno				= trim($_REQUEST['ch_turno']);
    	$v_art_codigo		= trim($_REQUEST['cod_producto']);
    	$m_cantidadpedida	= trim($_REQUEST['m_cantidadpedida']);
    	$m_precio			= trim($_REQUEST['m_precio']);
    	$preciouni			= trim($_REQUEST['preciouni']);
    	$nu_documento_chofer = trim($_REQUEST['nu_documento_chofer']);

    	if(empty($odometro))
		$odometro = 0;

		$sql = " SELECT validar_consolidacion('$fecha_val',$turno,'$m_almacen')";
		$sqlca->query($sql);
		$estado = $sqlca->fetchRow();
		
		if($estado[0] == 1){
			?><script language="javascript">alert('Dia consolidado!');</script><?php
			$okgraba = false;
		}else{
			$okgraba = true;//No consolidado
		}

	if($okgraba){

		$sql = "
		INSERT INTO val_ta_cabecera (
			ch_sucursal,
			dt_fecha,
			ch_cliente,
			ch_documento,
			ch_planilla,
			ch_placa,
			nu_odometro,
			ch_tarjeta,
			ch_caja,
			ch_lado,
			ch_turno,
			ch_estado,
			nu_documento_identidad_chofer
		) VALUES (
			'$m_almacen',
			'$fecha_val',
			'".trim($cod_cliente)."',
			'$nro_vale',
			null,
			'$nro_placa',
			$odometro,
			'$tarjeta',
			'$cajaa',
			'$lado',
			'$turno',
			1,
			'" . $nu_documento_chofer . "'
		);
		";

/*echo "<pre>";
print_r($sql);
echo "</pre>";
*/
		$xsql = pg_query($conector_id, $sql);

		$sql = "
			INSERT INTO val_ta_detalle (
					ch_sucursal, 
					dt_fecha, 
					ch_documento, 
					ch_articulo, 
					nu_cantidad, 
					nu_importe, 
					ch_estado, 
					nu_factor_igv,
					nu_precio_unitario 
			) VALUES ( 
					'$m_almacen', 
					'$fecha_val', 
					'$nro_vale', 
					'$v_art_codigo', 
					$m_cantidadpedida, 
					$m_precio, 
					1, 
					util_fn_igv_porarticulo('$v_art_codigo'),
					$preciouni 
			);

		";

		$xsql = pg_query($conector_id, $sql);

/*echo "<pre>";
print_r($sql);
echo "</pre>";*/

		?>

		<script language="javascript">
			alert('Registro guardado');
		</script>

		<?php

		echo('<script languaje="JavaScript">');
		echo("	location.href='vtacli_vales.php?v_fecha_desde=".$fecha_val."&v_fecha_hasta=".$fecha_val."' ");
		echo('</script>');

	}

}

if($boton=="Regresar"){
	echo('<script languaje="JavaScript">');
	echo("	location.href='vtacli_vales.php?v_fecha_desde=".$_REQUEST['v_fecha_desde']."&v_fecha_hasta=".$_REQUEST['v_fecha_hasta']."' ");
	echo('</script>');
}
?>

<html>
	<head>
		<meta name="description" content="The HTML5 Herald">
		<link rel="stylesheet" href="/sistemaweb/css/sistemaweb.css" type="text/css">
		<link rel="stylesheet" href="/sistemaweb/css/vales_liquidacion.css" type="text/css">
		<link rel="stylesheet" href="/sistemaweb/css/styles.css" type="text/css">
		<link rel="stylesheet" href="/sistemaweb/css/jquery-ui.css" />
		<script language="JavaScript1.2" src="/sistemaweb/images/stm31.js"></script>
		<script src="https://code.jquery.com/jquery-2.1.1.min.js" type="text/javascript"></script>
		<script src="/sistemaweb/js/jquery-2.0.3.js" type="text/javascript"></script>
		<script src="/sistemaweb/js/jquery-ui.js"></script>

		<script language="JavaScript" src="js/miguel.js"></script>
		<script language="JavaScript" src="/sistemaweb/clases/reloj.js"></script>
		<script language="JavaScript" src="/sistemaweb/compras/valfecha.js"></script>

		<script language="JavaScript" src="/sistemaweb/clases/calendario.js"></script>
		<script language="JavaScript" src="/sistemaweb/clases/overlib_mini.js"></script>
		<script language="JavaScript" src="/sistemaweb/compras/validacion.js"></script>
		<script language="JavaScript" src="/sistemaweb/ventas_clientes/js/tipcambio.js"></script>
		<style>

			#country-list{float:left;list-style:none;margin:0;padding:0;width:225px;}
			#country-list li{padding: 4px; background:#FAFAFA;border-bottom:#F0F0F0 1px solid;}
			#country-list li:hover{background:#F0F0F0;}

			/* CLIENTES */
			#clientes-list{
					float:left;
					list-style:none;
					margin:1;
					padding:1;
					width:300px;
			}

			#clientes-list li{
					padding: 2px;
					background:#FAFAFA;
					border-bottom:#F0F0F0 1px solid;
			}

			#clientes-list li:hover{
					background:#F0F0F0;
			}

			#desc_cliente{
					padding: 5px;
					border: #F0F0F0 1px solid;
					width:300px;
			}

			/* PRODUCTOS */
			#productos-list{float:left;list-style:none;margin:1;padding:1;width:300px;}
			#productos-list li{padding: 2px; background:#FAFAFA;border-bottom:#F0F0F0 1px solid;}
			#productos-list li:hover{background:#F0F0F0;}
			#desc_producto{padding: 5px;border: #F0F0F0 1px solid;width:300px;}

			/* PLACA */
			#nro_placa-list{float:left;list-style:none;margin:0;padding:0;width:80px;}
			#nro_placa-list li{padding: 2px; background:#FAFAFA;border-bottom:#F0F0F0 1px solid;}
			#nro_placa-list li:hover{background:#F0F0F0;}
			#nro_placa{padding: 5px;border: #F0F0F0 1px solid;width:80px;}

			/* TARJETAS */
			#card-list{float:left;list-style:none;margin:0;padding:0;width:80px;}
			#card-list li{padding: 2px; background:#FAFAFA;border-bottom:#F0F0F0 1px solid;}
			#card-list li:hover{background:#F0F0F0;}
			#tarjeta{padding: 5px;border: #F0F0F0 1px solid;width:80px;}

		</style>
		<script language="javascript">

var miPopup;

function enviadatos(){
	document.formular.submit()
}

$(function(){

	$.datepicker.regional['es'] = {
		    closeText: 'Cerrar',
		    prevText: '<Ant',
		    nextText: 'Sig>',
		    currentText: 'Hoy',
		    monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
		    monthNamesShort: ['Ene','Feb','Mar','Abr', 'May','Jun','Jul','Ago','Sep', 'Oct','Nov','Dic'],
		    dayNames: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
		    dayNamesShort: ['Dom','Lun','Mar','Mié','Juv','Vie','Sab'],
		    dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sá'],
		    weekHeader: 'Sm',
		    dateFormat: 'dd/mm/yy',
		    firstDay: 1,
		    isRTL: false,
		    showMonthAfterYear: false,
		    yearSuffix: ''
	};

    $.datepicker.setDefaults($.datepicker.regional['es']); 

	$( "#fecha_val" ).datepicker({
		changeMonth: true,
		changeYear: true,
		onSelect:function(fecha,obj){

			$('#cargardor').css({'display':'block'});
			$('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px', 'top': 200 + 'px'});

			var fecha = $('#fecha_val').val();
			var almacen = $('#m_almacen').val();

			$.ajax({

			  	type: "POST",
			    	url: "/sistemaweb/combustibles/reportes/c_descuentos_especiales.php",
			    	data:{
					accion			: "ActualizarPagos",
					fecha_inicial	: fecha,
					almacen			: almacen,
				},
			    	success:function(xm){

					if(xm == 'Error'){

						$('#cargardor').css({'display':'none'});
						$('#ch_turno').html("");
						$('#cajaa').html("");
						$('#tab_turnos').html("No hay Turnos - fecha: " + fecha + " Almacen: " + almacen);
						$('#tab_cajas').html("No hay Cajas - fecha: " + fecha + " Almacen: " + almacen);
						$('#tab_lados').html("No hay Lados - fecha: " + fecha + " Almacen: " + almacen);

					}else{

						var json=eval('('+xm+')');
						$('#cargardor').css({'display':'none'});
						$('#ch_turno').html(json.msg);
						$('#cajaa').html(json.msg2);
						$('#lado').html(json.msg3);
						$('#tab_turnos').html("");
						$('#tab_cajas').html("");
						$('#tab_lados').html("");
					}

				}

			});

		}
		
	});

	$("#desc_cliente").keyup(function(){
		$.ajax({
			type: "POST",
			url: "/sistemaweb/maestros/autocomplete_clientes.php",
			data:'keyword='+$(this).val(),
			beforeSend: function(){
				$("#desc_cliente").css("background","#FFF url(/sistemaweb/icons/loader.gif) no-repeat 200px");
			},
			success: function(data){
				$("#suggesstion-box").show();
				$("#suggesstion-box").html(data);
				$("#desc_cliente").css("background","#FFF");
			}
		});
	});

	$("#nro_placa").keyup(function(){

		var codcliente	= $('#cod_cliente').val();

		$.ajax({
			type	: "POST",
			url		: "/sistemaweb/maestros/verificacion.php",
			data	: {
					accion		: 'GetPlacaCliente',
					codcliente	: codcliente,
			},
			beforeSend: function(){
				$("#nro_placa").css("background","#FFF url(/sistemaweb/icons/loader.gif) no-repeat 60px");
			},
			success: function(data){
				$("#suggesstion-box-placa").show();
				$("#suggesstion-box-placa").html(data);
				$("#nro_placa").css("background","#FFF");
			}
		});

	});

	$("#tarjeta").keyup(function(){

		var codcliente	= $('#cod_cliente').val();

		$.ajax({
			type	: "POST",
			url	: "/sistemaweb/maestros/verificacion.php",
			data	: {
					accion		: 'GetTarjetas',
					codcliente	: codcliente,
			},
			beforeSend: function(){
				$("#tarjeta").css("background","#FFF url(/sistemaweb/icons/loader.gif) no-repeat 60px");
			},
			success: function(data){
				$("#suggesstion-box-card").show();
				$("#suggesstion-box-card").html(data);
				$("#tarjeta").css("background","#FFF");

			}
		});

	});

	$("#desc_producto").keyup(function(){
		$.ajax({
			type: "POST",
			url: "/sistemaweb/maestros/autocomplete_productos.php",
			data:'keyword='+$(this).val(),
			beforeSend: function(){
				$("#desc_producto").css("background","#FFF url(/sistemaweb/icons/loader.gif) no-repeat 165px");
			},
			success: function(data){
				$("#suggesstion-box-product").show();
				$("#suggesstion-box-product").html(data);
				$("#desc_producto").css("background","#FFF");
			}
		});
	});

	$("#numvale").keyup(function(){

		$(' #btn-addVale ').prop('disabled', false);
		$("#msgvale").html('');

		var almacen	= $('#m_almacen').val();
		var fecha	= $('#fecha_val').val();
		var numvale	= $(this).val();

		$.ajax({
			type	: "POST",
			url	: "/sistemaweb/maestros/verificacion.php",
			data	: {
					accion	: 'vvale',
					almacen	: almacen,
					fecha	: fecha,
					numvale	: numvale,
			},
			success: function(response){
				if(response.length > 0){
					$(' #btn-addVale ').prop('disabled', true);
					$("#msgvale").html(response);
				}
			}
		});
	});


	$( ".verificar" ).change(function() {

		var almacen	= $('#m_almacen').val();
		var fecha	= $('#fecha_val').val();
		var turno	= $(this).val();

		$.ajax({
			type	: "POST",
			url	: "/sistemaweb/inventarios/forms/fecha.php",
			data	:{
					almacen		: almacen,
					fecha		: fecha,
					turno		: turno
			},
			success:function(response){
				$("#msgconsolidacion").html(response);
			}
		});

	});


});

function SelectCliente(codigo, nombre) {
	$("#cod_cliente").val(codigo);
	$("#desc_cliente").val(nombre);
	$("#suggesstion-box").hide();
}

function SelectProduct(codigo, nombre) {
	$("#cod_producto").val(codigo);
	$("#desc_producto").val(nombre);
	$("#suggesstion-box-product").hide();
}

function SelectCard(nu_tarjeta, co_cliente) {

	$("#tarjeta").val(nu_tarjeta);
	$("#suggesstion-box-card").hide();

	$.ajax({
		type		: "POST",
		url			: "/sistemaweb/maestros/verificacion.php",
		dataType 	: "JSON",
		data		: {
			accion		: 'GetPlaca',
			codcliente	: co_cliente,
			numtar		: nu_tarjeta,
		},
		success: function(data){
			$("#nomusu").val(data['nomusu']);
			$("#nu_documento_chofer").val(data['nu_documento_chofer']);
			$("#nro_placa").val(data['nro_placa']);
		}
	});
}

function SelectPlaca(nro_placa, co_cliente) {

	$("#nro_placa").val(nro_placa);
	$("#suggesstion-box-placa").hide();

	$.ajax({
		type		: "POST",
		url			: "/sistemaweb/maestros/verificacion.php",
		dataType 	: "JSON",
		data		: {
			accion		: 'GetTarjeta',
			codcliente	: co_cliente,
			nro_placa	: nro_placa,
		},
		success: function(data){
			$("#nomusu").val(data['nomusu']);
			$("#nu_documento_chofer").val(data['nu_documento_chofer']);
			$("#tarjeta").val(data['numtar']);
		}
	});
}

</script>
</head>
<body>
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<form name='formular' action="vtacli_vales_agrega.php" method="post">
<input type="hidden" name="v_fecha_desde" value='<?php echo $_REQUEST['v_fecha_desde'];?>'>
<input type="hidden" name="v_fecha_hasta" value='<?php echo $_REQUEST['v_fecha_hasta'];?>'>

<?php
if (is_null($fecha_val)) {
	$m_almacen 		= $almacen;
	$fecha_val 		= date("d/m/Y");
	$m_cantidadpedida 	= 1;
	$m_precio 		= 0;
	$preciouni 		= 0;
	$cajaa 			= '1';
    	$lado 			= '01';
    	$turno 			= '1'; 
}
?>

<table border="0" align="center">
	<tr><th width="400" ><h2 style="font-family: Verdana,Arial,Helvetica,sans-serif;font-size: 14px;line-height: 14px;"><b>VALES DE CREDITO</th></tr>
</table>

<table border="0" align="center">
	<tr>
		<th width="100" align="right">Almac&eacute;n</th>
		<td>:</td>
		<td valign="top">
		<select id="m_almacen" name="m_almacen">
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
		</td>
	</tr>

	<tr>
		<td align="right">Fecha</td>
		<td>:</td>
		<td><input maxlength="10" size="12" type="text" name="fecha_val" id="fecha_val" class="fecha_formato" placeholder="Ingresar Fecha"/><span id="msgconsolidacion"></span></td>
	</tr>
        <tr>
       		<td align="right">Nro. Caja</td>
		<td>:</td>
		<td id="cajas">
			<select name="cajaa" id="cajaa">
			</select>
			<div id="tab_cajas" style="font-size:1.2em; color:red;"></div>
		</td>
	<tr>
	       	<td align="right">Turno: </td>
		<td>:</td>
		<td id="turno_final">
			<select id="ch_turno" name="ch_turno" class="verificar">
			</select>
			<div id="tab_turnos" style="font-size:1.2em; color:red;"></div>
		</td>

        </tr>
	<tr>
	       	<td align="right">Nro. Lado: </td>
		<td>:</td>
		<td id="lados">
			<select id="lado" name="lado">
			</select>
			<div id="tab_lados" style="font-size:1.2em; color:red;"></div>
		</td>

        </tr>
	<tr>
		<th align="right">Nro. Vale</th>
		<td>:</td>
		<td>
			<input type="text" title="Se necesita Nro. Vale" size="16" maxlength="15" id="numvale" name="numvale"  placeholder="Ingresar Nro. Vale" required>
			<span id="msgvale"></span>
		</td>
	</tr>

	<tr>
		<th align="right">Razon Social</th>
		<td>:</td>
		<td>
			<input type="text" title="Se necesita Razon Social" autocomplete="off" size="42" maxlength="40" id="desc_cliente" class="dropdown-header k-widget k-header" name="desc_cliente" placeholder="Ingresar Razon social" style="text-transform:uppercase;" onkeyup="javascript:this.value=this.value.toUpperCase();" required/>
			<div id="suggesstion-box"></div>
		</td>
	</tr>
	<tr>
		<td align="right">Ruc Cliente</td>
		<td>:</td>
		<td><input type="text" name="cod_cliente" id="cod_cliente" size="11" maxlength="11" readonly/></td>
	</tr>

	<tr>
		<th align="right">Nro. Placa</th>
		<td>:</td>
		<td>
			<input title="Se necesita Nro. Placa" autocomplete="off" type="text" id="nro_placa" name="nro_placa" size="11" maxlength="10" placeholder="Ingresar placa" value="<?php echo $nro_placa;?>" required>
			<div id="suggesstion-box-placa"></div>
		</td>
	</tr>

	<tr>		
		<th align="right">Nro. Tarjeta</th>
		<td>:</td>
		<td><input title="Se necesita Nro. Tarjeta" autocomplete="off" type="text" name="tarjeta" id="tarjeta" size="11" maxlength="10" placeholder="Ingresar tarjeta" required>
			<div id="suggesstion-box-card"></div>
		</td>
	</tr>

	<tr>
		<th align="right">Nombre Chofer</th>
		<td>:</td>
		<td>
			<input type="text" id="nomusu" name="nomusu" size="11" maxlength="10" placeholder="Ingresar vale" value="<?php echo $nomusu;?>" disabled>
		</td>
	</tr>

	<tr>
		<th align="right">Nro. Documento / Brevete Chofer</th>
		<td>:</td>
		<td>
			<input type="text" id="nu_documento_chofer" name="nu_documento_chofer" size="11" maxlength="10" value="<?php echo $nu_documento_chofer;?>" required>
		</td>
	</tr>

	<tr>
		<th align="right">Odometro</th>
		<td>:</td>
		<td><input type="text" name="odometro" size="16" maxlength="15" placeholder="Ingresar odometro" value="<?php echo $odometro;?>">
		<label>Opcional</label>
		</td>
		
	</tr>

	</table>
</table>
<br>
<table border="0" cellpadding="0" cellspacing="1" align="center">
	<tr>
		<th class="grid_cabecera">NOMBRE PRODUCTO</th>
		<th class="grid_cabecera">COD. PRODUCTO</th>
		<th class="grid_cabecera">CANTIDAD</th>
		<th class="grid_cabecera">PRECIO</th>
		<th class="grid_cabecera">IMPORTE</th>
	</tr>
	<tr>
		<td>
			<input type="text" title="Se necesita Nombre Producto" autocomplete="off" size="42" maxlength="40" id="desc_producto" name="desc_producto" style="text-transform:uppercase;" placeholder="Ingresar nombre producto" onkeyup="javascript:this.value=this.value.toUpperCase();" required/>
			<div id="suggesstion-box-product"></div>
		</td>
		<td>
			<input type="text" id="cod_producto" name="cod_producto" size="13" maxlength="13" readonly/>
		</td>
		<th>
			<input name="m_cantidadpedida" type="text" style="text-align:right" size='15' maxlength="15" value="<?php echo $m_cantidadpedida;?>" onkeyup="CalcularTotales();" onkeypress="return validar(event,3)">
		</th>
		<th>
			<input name="preciouni" type="text" style="text-align:right" size='15' maxlength="15" value="<?php echo $preciouni;?>" onkeyup="CalcularTotales();" onkeypress="return validar(event,3)">
		</th>
		<th>
			<input name="m_precio" type="text" style="text-align:right" size='15' maxlength="15" value="<?php echo $m_precio;?>" onkeypress="return validar(event,3)">
		</th>
	</tr>

	<tr>
		<td colspan="6" align="center"><br>
		<button type="submit" id="btn-addVale" name="boton" value="Agregar"><img src="/sistemaweb/icons/gadd.png" align="right" />Guardar</button>
		&nbsp;&nbsp;&nbsp;<button type="submit" name="boton" value="Regresar"><img src="/sistemaweb/icons/greturn.png" align="right" />Regresar</button>
	</tr>
</table>
</form>
</body>
</html>

<?php
if ($conector_id) pg_close($conector_id);
if ($conector_repli_id) pg_close($conector_repli_id);
$clase_error->_error();
