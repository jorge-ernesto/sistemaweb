<?php
include("/sistemaweb/global.php");
include("../menu_princ.php");
include("../functions.php");
include("js/funciones.php");
require("../clases/funciones.php");
include("store_procedures.php");

extract($_REQUEST);

$funcion = new class_funciones;
$clase_error = new OpensoftError;
$coneccion = $funcion->conectar("","","","","");

include("js/inv_addmov_support.php");

$CI = pg_exec("SELECT par_valor FROM int_parametros WHERE par_nombre = 'actualiza_precio';");
$AI = pg_fetch_array($CI,0);
$mostrar_ventanita = $AI["par_valor"];

$CI = pg_exec("SELECT par_valor FROM int_parametros WHERE par_nombre = 'complementario';");
$AI = pg_fetch_array($CI,0);
$complementario = $AI["par_valor"];

$PZ = pg_exec("SELECT par_valor FROM int_parametros WHERE par_nombre='precio_centralizado';");
$PC = pg_fetch_array($PZ,0);
$preciocentralizado = $PC["par_valor"];

$fm = $_GET['fm'];
$nro_mov = $_POST['nro_mov'];

$resultado = 0;
$referencia_nc="";

switch($accion) {

	case "Ingresar": 		/* Aqui estamos insertando un item de un array */

		/*?><script>alert("<?php echo 'sess '.$_REQUEST['fecha'] ; ?> ");</script><?php-*/


		if($naturaleza == 1 || $naturaleza == 2) {    
	    		$almacen_interno = $alma_des;	
		} else {
			$almacen_interno = $alma_ori;
		}    

		$ITEMS = agregarItems($ITEMS,$fm,$almacen_interno,$alma_ori,$alma_des,$cod_proveedor,$tipo_doc,$serie_doc,$num_doc,$cod_articulo,$des_art_campo,
			$art_cantidad,$art_costo_uni,$nro_mov,$valor,$naturaleza,$des_pro,$des_doc,$art_stock,$tmp_total_sin_igv,$tmp_total_con_igv);
			
		session_register("ITEMS");
		break;

	case "TerminarIngreso": 	/* Insertamos en la tabla inv_movialma */

		global $tipo_formularioBD;
		global $numeroBD ;
		global $fecha_ordenBD;
		global $turnoBD;
		global $scopBD;
		global $observBD;
		global $ipBD;
		global $usuarioBD;

		/*$fecha_actual = fecha_aprosys();
		list($dia,$mes,$anio) = explode("/",$fecha_actual); 
		$fecha_form = $anio."-".$mes."-".$dia;*/
		

		$referencia_nc=$_REQUEST['nc'];
		
		$diad = $_REQUEST['fecha'];
		
		$valida = Consolidacion($diad,$alma_des);
		
		if($valida == 1){					
			?><script>alert('Dia Consolidado!');</script><?php
			break;
		}
		

		if ($complementario =='1') {
			echo "paso la valiacion de complemeteos.<br/>";
		    $CI = pg_exec("SELECT count(turno_recepcion) AS valor FROM inv_movialma_complemento WHERE tran_codigo='".trim($fm)."' and mov_numero='".trim($nro_mov)."' and mov_fecha='".trim($_REQUEST['fecha'])."';");
		    $AI = pg_fetch_array($CI,0);
		    $tamanio = $AI["valor"];
		} else {
		    $tamanio = 1;
		} 

		if($tamanio >= 1 || trim($fm) != "21") {
			echo "Listo para poder  insertar o modificar el programa.<br/>";
			$ITEMS = modificarItems($CANTIDADES,$COSTOS,$ITEMS,$items,$TOTAL_CON_IGV,$TOTAL_SIN_IGV);
			unset($ITEMS);
			session_register("ITEMS");
			$saludo = "hola";
			insertarRegistros($ITEMS,$_REQUEST['fecha'],$referencia_nc);
	
			$CP = $ITEMS;
			$ITEMS = null;
	
			unset($ITEMS);
			session_register("ITEMS");
			session_register("CP");
			session_register("saludo");
		} else {
			?><script>alert('Faltan los Datos Complementarios');</script><?php
		}
		break;

	case "Eliminar": 		/* Eliminamos determinados registros del array */

		global $tipo_formularioBD;
		global $numeroBD ;
		global $fecha_ordenBD;
		global $turnoBD;
		global $scopBD;
		global $observBD;
		global $ipBD;
		global $usuarioBD;

		$tipo_formularioBD = "";
		$numeroBD = "";
		$fecha_ordenBD = "";
		$turnoBD  = "";
		$scopBD   = "";
		$observBD = "";
		$ipBD = "";
		$usuarioBD = "";

		$ITEMS = eliminarItems($ITEMS,$items);
		session_register("ITEMS");
		break;

	case "CancelarIngreso": 	/* Eliminamos todos los registros del array para comenzar denuevo */

		/*$fecha_actual = fecha_aprosys();
		list($dia,$mes,$anio) = explode("/",$fecha_actual); 
		$fecha_form = $anio."-".$mes."-".$dia;*/

		$resultado  = pg_exec("DELETE FROM inv_movialma_complemento WHERE tran_codigo='".trim($fm)."' and mov_numero='".trim($nro_mov)."' and mov_fecha='".trim($_REQUEST['fecha'])."';");

		$ITEMS = null;

		unset($ITEMS);

		session_register("ITEMS");

		break;

	case "GuardarCambios":

		global $tipo_formularioBD;
		global $numeroBD ;
		global $fecha_ordenBD;
		global $turnoBD;
		global $scopBD;
		global $observBD;
		global $ipBD;
		global $usuarioBD;

		/*$fecha_actual = fecha_aprosys();
		list($dia,$mes,$anio) = explode("/",$fecha_actual); 
		$fecha_form = $anio."-".$mes."-".$dia;*/

		if ($complementario =='1') {
		    $CI = pg_exec("SELECT count(turno_recepcion) AS valor FROM inv_movialma_complemento WHERE tran_codigo='".trim($fm)."' and mov_numero='".trim($nro_mov)."' and mov_fecha='".trim($_REQUEST['fecha'])."';");
		    $AI = pg_fetch_array($CI,0);
		    $tamanio = $AI["valor"];
		} else {
		    $tamanio = 1;
		} 

		if($tamanio >= 1 || trim($fm) != "21") {
			/*$resultado=pg_exec("delete from inv_movialma_complemento where tran_codigo='".trim($tipo_formularioBD)."' and mov_numero='".trim($numeroBD)."' and mov_fecha='".$fecha_ordenBD."';");
			$resultado=pg_exec("insert into inv_movialma_complemento (tran_codigo,mov_numero,mov_fecha,turno_recepcion,numero_scop,observacion, auditoria_ip,auditoria_usuario) values ('".trim($tipo_formularioBD)."','".trim($numeroBD)."','".$fecha_ordenBD."','".$turnoBD."','".$scopBD."','".$observBD."','".$ipBD."','".$usuarioBD."');");

			$tipo_formularioBD = "";
			$numeroBD = "";
			$fecha_ordenBD = "";
			$turnoBD = "";
			$scopBD = "";
			$observBD = "";
			$ipBD = "";
			$usuarioBD = "";*/

			$ITEMS = modificarItems($CANTIDADES,$COSTOS,$ITEMS,$items,$TOTAL_CON_IGV,$TOTAL_SIN_IGV);
			unset($ITEMS);
			session_register("ITEMS");
		} else {
			?><script>alert('Faltan los Datos Complementarios');</script><?php
		}
		break;
}

$V = inicializarVariables($fm,trim($almacen));
$natu 	= $V["natu"];
$valor 	= $V["valor"];
$enti	= $V["enti"]; 
$ref	= $V["ref"];
$nro_mov  = $V["nro_mov"];
$des_form = $V["des_form"];
$flag_ori = $V["flag_ori"];
$flag_des = $V["flag_des"];

$readonly_alma_des = $V["readonly_alma_des"];
$readonly_alma_ori = $V["readonly_alma_ori"];
$ayuda_alma_des = $V["ayuda_alma_des"];
$ayuda_alma_ori = $V["ayuda_alma_ori"];

$readonly_costo_uni = $V["readonly_costo_uni"];

/* Por default deben de aparecer los almacenes de origen y destino de inv_tipo_transa y hacer algunos filtros */
if($alma_ori == "" ||  $alma_des == "") {
	$DEF = almacenesDefault($fm);
	$alma_ori = $DEF["tran_origen"];
	$alma_des = $DEF["tran_destino"];
	$alma_ori_campo = $DEF["tran_origen_campo"];
	$alma_des_campo = $DEF["tran_destino_campo"];
	
	if(trim($alma_ori) != "") {
		$read_ori="readonly='yes'";
	}
	if(trim($alma_des) != "") {
		$read_des="readonly='yes'";
	}
}
	
if(trim($almacen) == "") {
	?><script language="JavaScript">
		//alert('No se ha registrado el almacen debes volver a entrar');
		//esto lo he comentado gean location.href='/sistemaweb/login.php';
	</script><?php
}

/* Saca el numero de Orden solo para mostrar */
$numero_orden = numeroOrden("01",trim($almacen),"select");
$numero_orden = completarCeros($numero_orden,8,"0");

/* Existe una variable autocompletar la cual se pone a yes y se manda cuando se quiere autocompletar algun campo */
if($autocompletar == "yes") {
	/*$AUTO = autocompletarDatos($cod_articulo,$valor,trim($almacen));
	//$cod_articulo = $R["cod_art"];
	$descripcion_articulo= $AUTO["des_art"];
	$descripcion_articulo = str_replace("'"," ",$descripcion_articulo);
	$descripcion_articulo = str_replace("\""," ",$descripcion_articulo);
	$art_costo_uni = $AUTO["costo_uni"];*/
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>

<script language="JavaScript">
	var cadenas_de_ejecucion = new Array();
	
	function mostrarAyudaAlmacenes(url,flag,cod,des,consulta,des_campo) {
		url = url+'?flag='+flag+'&cod='+cod+'&des='+des+'&consulta='+consulta+'&des_campo='+des_campo;
		window.open(url,'miwin','width=500,height=350,scrollbars=yes,menubar=no,left=390,top=20');
	}

	function mostrarAyuda(url,cod,des,consulta,des_campo,valor,fm, almacen){
		url = url+"?cod="+cod+"&des="+des+"&consulta="+consulta+"&des_campo="+des_campo+"&valor="+valor+"&fm="+fm+"&almacen="+almacen;
		window.open(url,'miwin','width=500,height=350,scrollbars=yes,menubar=no,left=290,top=20');
	}


function mostrarAyudaOrdenCompra(url,prov,alma,many_alma,cod,fm,form) {
		var mandar = true;
		var msg = "msg";

		if(form.serie_doc.value=="") {
		 	mandar = false;
			msg = "Numero de documento incompleto";
		}

		if(form.num_doc.value=="") {
			mandar = false;
			msg = "Numero de documento incompleto";
		}
	
		if(form.cod_proveedor.value=="") {
			mandar = false;
			msg = "Proveedor no seleccionado";
		}
	
		if(form.alma_ori.value=="") {
			mandar = false;
			msg = "Almacen Origen no seleccionado";
		}

		if(form.alma_des.value=="") {
			mandar = false;
			msg = "Almacen Destino no seleccionado";
		}
	
	
		var documento_referencia = form.serie_doc.value+form.num_doc.value;
		var alma_ori = form.alma_ori.value;
		var alma_des = form.alma_des.value;
		var tipo_doc = form.tipo_doc.value;
		var serie_doc = form.serie_doc.value;
		var num_doc = form.num_doc.value;
		var nro_mov = form.nro_mov.value;
		var naturaleza = form.naturaleza.value;
		var des_pro = form.des_pro.value;
		var des_doc = form.des_doc.value;
	
		url = url+"?proveedor="+prov+"&cod_almacen="+alma+"&many_alma="+many_alma+"&cod="+cod+"&fm="+fm+"&documento_referencia="+documento_referencia+"&tran_valor="+form.valor.value+"&alma_ori="+alma_ori+"&alma_des="+alma_des+"&tipo_doc="+tipo_doc+"&serie_doc="+serie_doc+"&num_doc="+num_doc+"&nro_mov="+nro_mov+"&naturaleza="+naturaleza+"&des_doc="+des_doc+"&des_pro="+des_pro;	
		
		//alert(url);
		if(mandar) {
			window.open(url,'miwin','width=800,height=600,scrollbars=yes,menubar=no,left=0,top=20');
		} else {
			alert(msg);
		}
	}

	function mostrarAyudaGLP(url,mov_numero,tran_codigo,art_codigo,mov_fecha,txtcantidad) {
		url = url+"?mov_numero="+mov_numero+"&tran_codigo="+tran_codigo+"&art_codigo="+art_codigo+"&mov_fecha="+mov_fecha+"&txtcantidad="+txtcantidad;
		window.open(url,'miwin','width=500,height=350,scrollbars=yes,menubar=no,left=290,top=20');
	}
	
	function calculando(form, valor) {
		var costin = form.art_costo_uni.value;
		var totsinigv = Math.round(costin*valor*100)/100;
		var totconigv = Math.round(costin*valor*1.18*100)/100;
		form.tmp_total_con_igv.value = totconigv;
		form.tmp_total_sin_igv.value = totsinigv;
	}
	function validarNC(){
			var tipodoc=document.getElementById("tipo_doc").value;
		if(tipodoc=="000020" || tipodoc=="20" || tipodoc==20 ){
			estado=false;
			referencia=prompt("Ingrese Porfavor los datos N.CREDITO ,en el formato TD-SERIE-CORRELATIVO \n -Los TD 10,35 ");
			canre=referencia.trim().length;
			textre=referencia.trim();
			
			
			if(canre>6 && textre.split("-").length==3 ){
				var datare=textre.split("-");
				
				
				if(datare[0]=="10" || datare[0]=="35"){
				estado=true;
				
				}else{
					validarNC();
				}
			}else{
				validarNC();
			}
			var valor_nc=document.getElementById("nc").value=textre;
			
		}
		return estado;
	}

	function mandarDatos(form,accion) { //Solo al Guardar cambios o Terminar Ingreso
		form.accion.value= accion;

		var estado=true;
		var tipodoc=document.getElementById("tipo_doc").value;

		if(tipodoc=="000020" || tipodoc=="20" || tipodoc==20 ){
			estado=validarNC();
		}
			
		if(estado==true ){
			if(validarFormulario(form,accion)) {
				form.submit();
			}
		}
	}

	function mandarDatos2(form,accion){
		
		form.accion.value= accion;
		if(validarFormulario(form,accion)) {
			form.submit();
		}
	}

	function mandarDatosComplementarios(form,accion,fm,t_formulario,formulario,fecha) {
		form.accion.value= accion;
		if(validarFormulario(form,accion)) {
			if (fm=="21") {
				window.open('/sistemaweb/inventarios/datos_complementarios.php'+'?t_formulario='+t_formulario+'&num='+fm+'&formulario='+formulario+'&fecha='+fecha, "Datos Complementarios", "width=500,height=400,scrollbars=NO,resizable=NO");
			}
			form.submit();
		}
	}

	function mandarDatosArticulo(form,accion,fm,orden){
		form.accion.value= accion;
		if(validarFormulario(form,accion)) {
			if (fm=="01") {
				window.open('/sistemaweb/inventarios/datos_articulo.php'+'?articulo='+document.getElementById("form1").cod_articulo.value+'&orden='+orden+'&cant='+document.getElementById("form1").art_cantidad.value+'&costo='+document.getElementById("form1").art_costo_uni.value , "Datos del Articulo" , "width=500,height=400,scrollbars=NO,resizable=NO");
			}
			form.submit();
		}
	}
	
	function getObj(name, nest) {
		if (document.getElementById) {
			return document.getElementById(name).style;
		} else if (document.all) {
			return document.all[name].style;
		} else if (document.layers) {
			if (nest != '') {
				return eval('document.'+nest+'.document.layers["'+name+'"]');
			}
		} else {
			return document.layers[name];
		}
	}

	function showLayer(layerName, nest) {
		var x = getObj(layerName, nest);
		x.visibility = "visible";
	}

	function hideLayer(layerName, nest) {
		var x = getObj(layerName, nest);
		x.visibility = "hidden";
	}

	function mostrarFila(fila) {
		showLayer(fila);
	}

	function ocultarFila(fila) {
		hideLayer(fila);
	}

	function cceros(v_var,v_lon,k_var) {

		var v_var2= v_var.value.replace(/^\s*|\s*$/g,"");
		var lon1  = v_var.value.length;
		var lon2  = v_lon-lon1
	
		for(i=0;i<lon2;i++) {
			v_var2='0'+v_var2;
		}
		eval("document.form1."+k_var+".value=v_var2");
	}

	function completarArticulo(campo,form) {
		var long_campo = campo.value.length;
		if(long_campo==13){
			form.autocompletar.value = 'yes';
			form.submit();
		}
	}

	function validarFormulario(form,tipo) {
		var bol = true;
		if(tipo=="Ingresar" ) {
			var msg = "";
			if(form.alma_ori.value=="") {			
				bol = false;			
				msg = "El almacen de Origen esta en blanco"; 
			}
			if(form.alma_des.value=="") {			
				bol = false;			
				msg = "El almacen de Destino esta en blanco";
			}	
			if(form.entidad_validacion.value=="P") {
				if(form.cod_proveedor.value=="") {		
					bol = false;			
					msg = "Codigo de Proveedor en blanco";
				}
			}
	
			if(form.tipo_doc.value==""){			
				bol=false;			
				msg = "Tipo de Documento en blanco";
			}
			if(form.serie_doc.value==""){			
				bol=false;			
				msg = "Serie del Documento en blanco";
			}
			if(form.num_doc.value==""){			
				bol=false;			
				msg = "Numero del documento en blanco";
			}
			if(form.cod_articulo.value==""){		
				bol=false;			
				msg = "Articulo no selecccionado";
			}
			if(form.art_cantidad.value==""){		
				bol=false;			
				msg = "Cantidad de Articulo no ingresada";
			}
			if(form.art_costo_uni.value=="") {		
				bol=false;			
				msg = "Costo untario no ingresado";
			}
		} 
		
		if(tipo=="TerminarIngreso") {
			if(form.tipo_doc.value=="") {			
				bol=false;			
				msg = "Tipo de Documento en blanco";
			}
			if(form.serie_doc.value=="") {			
				bol=false;			
				msg = "Serie del Documento en blanco";
			}
			if(form.num_doc.value=="") {				
				bol=false;		
				msg = "Numero del documento en blanco";
			}
			if(form.alma_ori.value=="") {			
				bol=false;			
				msg = "El almacen de Origen esta en blanco";
			}
			if(form.alma_des.value=="") {			
				bol=false;			
				msg = "El almacen de Destino esta en blanco";
			}
		}
	
		if(!bol) {
			alert(msg);
		}
	
		return bol;
	}

	function completarCampos(cod_articulo,form) {
		var articulo = cod_articulo.value;
		var long_campo = cod_articulo.value.length;
		var tmp = "";
		if(articulo == null || articulo == "") {
			
		} else {
			if(long_campo<13 && long_campo>0 && articulo!='11620301' && articulo!='11620302' && articulo!='11620303' && articulo!='11620304' && articulo!='11620305' && articulo!='11620306' && articulo!='11620307'){
				for(i=0;i<13-long_campo;i++) {
					articulo = '0' + articulo;
				}
				cod_articulo.value = articulo;
				completarArticulo(cod_articulo,form)
			}		
		}
	}

	function ltrim(s) {
	   	return s.replace(/^\s+/, "");
	}

	function rtrim(s) {
	   	return s.replace(/\s+$/, "");
	}

	function trim(s) {
	   	return rtrim(ltrim(s));
	}

	function chiches(ifr,chiche,codigo,campo,campo_codigo) {				
		if(chiche=="AlmacenOrigen" || chiche=="AlmacenDestino") {
			chiche="Almacenes";
		}
		var url = "inv_addmov_iframe.php?chiche="+chiche+"&codigo="+trim(codigo)+"&campo="+campo+"&campo_codigo="+campo_codigo;
		var c_valor = document.form1.valor.value;
		var c_naturaleza = document.form1.naturaleza.value;
		var c_alma_ori = document.form1.alma_ori.value;
		var c_alma_des = document.form1.alma_des.value;
		url = url+"&c_valor="+c_valor+"&c_naturaleza="+c_naturaleza+"&c_alma_ori="+c_alma_ori+"&c_alma_des="+c_alma_des;
		ifr.location = url;
	}

	function CalcularTotales(costounitario){
		var cantidad, totsinigv, totconigv;

		cantidad = document.getElementsByName('art_cantidad')[0].value;

		totsinigv = (cantidad * costounitario);
		totconigv = ((cantidad * costounitario) * 1.18);

		document.getElementsByName('tmp_total_sin_igv')[0].value = totsinigv.toFixed(2);
		document.getElementsByName('tmp_total_con_igv')[0].value = totconigv.toFixed(2);

	}

	function util_costeo(c_total_con_igv,c_total_sin_igv,c_costo_uni,c_cantidad_item,c_porcentaje_igv,c_opcion) {	

		if(document.form1.valor.value=="S"){ 	
			total_con_igv = parseFloat(c_total_con_igv.value);
			total_sin_igv = parseFloat(c_total_sin_igv.value);
			cantidad_item = parseFloat(c_cantidad_item.value);
			porcentaje_igv = parseFloat(c_porcentaje_igv.value);

			if(c_opcion=="SIN_IGV") {
				total_con_igv = total_sin_igv*(1+(porcentaje_igv/100));
				costo_uni = total_sin_igv/cantidad_item;
		
				c_costo_uni.value = costo_uni.toFixed(6);
				c_total_con_igv.value = total_con_igv.toFixed(2);
			}
	
			if(c_opcion=="CON_IGV") {
				total_sin_igv = total_con_igv/(1+(porcentaje_igv/100));
				costo_uni = total_sin_igv/cantidad_item;
				c_costo_uni.value = costo_uni.toFixed(6);
				c_total_sin_igv.value = total_sin_igv.toFixed(2);
			}	
		}		
	}

	function util_costeo2(c_total_con_igv,c_total_sin_igv,c_costo_uni,c_cantidad_item,c_porcentaje_igv,c_opcion,c_marca) {
	
		if(document.form1.valor.value=="S") {	
			c_marca.checked = true;
			total_con_igv = parseFloat(c_total_con_igv.value);
			total_sin_igv = parseFloat(c_total_sin_igv.value);
			cantidad_item = parseFloat(c_cantidad_item.value);
			porcentaje_igv = parseFloat(c_porcentaje_igv.value);
	
			if(c_opcion=="SIN_IGV") {
				total_con_igv = total_sin_igv*(1+(porcentaje_igv/100));
				costo_uni = total_sin_igv/cantidad_item;		
				c_costo_uni.value = costo_uni.toFixed(6);
				c_total_con_igv.value = total_con_igv.toFixed(2);
			}
	
			if(c_opcion=="CON_IGV") {
				total_sin_igv = total_con_igv/(1+(porcentaje_igv/100));
				costo_uni = total_sin_igv/cantidad_item;
				c_costo_uni.value = costo_uni.toFixed(6);
				c_total_sin_igv.value = total_sin_igv.toFixed(2);
			}	
		}
	}

	function utilAplicarDescuento(c_total_con_igv,c_total_sin_igv,c_costo_uni,c_cantidad_item,c_porcentaje_igv,c_descuento,c_marca,c_total_con_igv_base) {
	
		if(document.form1.valor.value=="S") {	
			total_con_igv = parseFloat(c_total_con_igv_base.value);
			total_sin_igv = parseFloat(c_total_sin_igv.value);
			cantidad_item = parseFloat(c_cantidad_item.value);
			porcentaje_igv = parseFloat(c_porcentaje_igv.value);
			descuento = parseFloat(c_descuento.value);
	
			if(isNaN(descuento)){
				descuento = parseFloat("0.0");
			}	
			total_con_igv = total_con_igv - (total_con_igv*(descuento/100));
			total_sin_igv = total_con_igv/(1+(porcentaje_igv/100));
			costo_uni = total_sin_igv/cantidad_item;
	
			c_total_con_igv.value = total_con_igv.toFixed(2);
			c_total_sin_igv.value = total_sin_igv.toFixed(2);
			c_costo_uni.value = costo_uni.toFixed(6);
	
			c_marca.checked=true;
	
		}
	}

	function util_descuentos() {
		for(i=0;i<cadenas_de_ejecucion.length;i++){	
			eval(cadenas_de_ejecucion[i]);
		}
	}

	function validar(e,tipo){

		tecla=(document.all)?e.keyCode:e.which;

		if (tecla==13 || tecla==8 || tecla== 0)
			return true;

		switch(tipo){
			/*letras y numeros, puntos */
			case 1: patron=/[A-Z a-z0-9./:,;.-]/;break;
			/*solo numeros enteros */
			case 2: patron=/[0-9]/;break;
			/*solo numeros dobles*/
			case 3: patron=/[0-9./]/;break;
			/*solo letras*/
			case 4: patron=/[A-Z a-z]/;break;
			/*telefonos y faxes*/
			case 5: patron=/[0-9/-]/;break;
		}

		teclafinal=String.fromCharCode(tecla);
		return patron.test(teclafinal);
	}


</script>
	<title>Ingreso x Compras</title>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<script src="/sistemaweb/js/jquery-2.0.3.js" type="text/javascript"></script>
		<link rel="stylesheet" href="/sistemaweb/css/jquery-ui.css" />
		<script src="/sistemaweb/js/jquery-ui.js"></script>
		<script  type="text/javascript">

			$(document).ready(function(){
				$( "#fecha" ).datepicker({
					changeMonth: true,
	            	changeYear: true,
	            	onSelect:function(fecha,obj){

	            		var nu_almacen_destino 	= $('#nu_almacen_destino').val();
						var fecha_v  			= $('#fecha').val();

						$('#btn_guardar_cambios').prop("disabled", false);
						$('#btn_term_ing').prop("disabled", false);	
						$('#btn_ingresar').prop("disabled", false);

						if(fecha_v > $('#fecha_aprosys').val()){
							alert('La fecha de ingreso no puede ser mayor a la Fecha Sistema');
							$('#btn_guardar_cambios').prop("disabled", true);
							$('#btn_term_ing').prop("disabled", true);
							$('#btn_ingresar').prop("disabled", true);
						}else if(fecha_v.substr(5,2) < $('#mes_apro').val()){
							alert('El mes debe de ser el mismo al de la Fecha Sistema !');
							$('#btn_guardar_cambios').prop("disabled", true);
							$('#btn_term_ing').prop("disabled", true);
						}else if(fecha_v.substr(5,2) > $('#mes_apro').val()){
							alert('El mes debe de ser el mismo al de la Fecha Sistema !');
							$('#btn_guardar_cambios').prop("disabled", true);
							$('#btn_term_ing').prop("disabled", true);
						} else {			
							$.ajax({
								type: "POST",
								url: "forms/fecha.php",
								data:{fecha:fecha, nu_almacen_destino: nu_almacen_destino},
								success:function (response){
									if(response.length > 98 || response.length > 69){
										$('#btn_guardar_cambios').prop("disabled", true);
										$('#btn_term_ing').prop("disabled", true);
									}
									$("#resultado").html(response);
								}
							});
						}
					}
				});
			    
				$( "#fecha" ).datepicker("option", "dateFormat","yy-mm-dd");
				$('#fecha').val($('#fecha_ini').val());

		   	 });

		</script>
</head>
<body>
<!--<div id="cargardor" style="position: absolute;display: none"><img src="/sistemaweb/ventas_clientes/liquidacion_vales/cg.gif" /></div>-->
<CENTER>INGRESAR MOVIMIENTOS</CENTER><br/>
<script src="/sistemaweb/js/calendario.js" type="text/javascript" ></script>
<script src="/sistemaweb/js/overlib_mini.js" type="text/javascript" ></script>
<form action="" method="post" name="form1" id="form1">
	<input  type='hidden' value='' name='nc' id='nc' />
<table width="734" border="0" cellpadding="0" cellspacing="0">

	<tr>
		<td height="21">
		<div align="left">FECHA SISTEMA</div></td>
	  	<?php
			$fecha_actual = fecha_aprosys();
			$dia = substr($fecha_actual,6,4)."-".substr($fecha_actual,3,2)."-".substr($fecha_actual,0,2);
		?>
		<input type="hidden" id="mes_apro" name = "mes_apro" value='<?php echo substr($fecha_actual,3,2); ?>' />
		<input type="hidden" id="fecha_aprosys" name = "fecha_aprosys" value='<?php echo $dia; ?>' />
      		<td colspan="2"><?php echo $dia;?></td>
      		<td>&nbsp;</td>

	<tr> 
      		<td width="217"><div align="left">FORMULARIO</div></td>
      		<td colspan="2"><?php echo $fm;?> - <?php echo $des_form;?> <input type="hidden" name="fm" value="<?php echo $fm;?>"><?php echo "NATURALEZA ".$natu;?></td>
      		<td width="128">&nbsp;</td>
    	</tr>
    	<tr> 
      		<td><div align="left">N&deg; FORMULARIO</div></td>
      		<td colspan="2"><?php echo $nro_mov;?> <input type="hidden" name="nro_mov" value="<?php echo $nro_mov;?>">
        	<input type="hidden" name="valor" value="<?php echo $valor;?>">
        	<input type="hidden" name="naturaleza" value="<?php echo $natu;?>"></td>
      		<td>&nbsp;</td>
    	</tr>

<!-- VALIDACION DE FECHA DE FORMULARIOS -->

    	<tr>
      		<td height="24"><div align="left">ALMACEN ORIGEN</div></td>
      		<td width="154"><input type="text" name="alma_ori" size="6" value="<?php echo $alma_ori;?>" <?php echo $read_ori;?> >
        	<?php if(trim($alma_ori)=="" or $fm=='08' or $fm=='16'){ ?>
        		<img src="../images/help.gif" width="16" height="15" onMouseOver="this.style.cursor='hand'" onClick="javascript:mostrarAyudaAlmacenes('js/ayuda_almacenes.php','<?php echo $flag_ori;?>','alma_ori','almaori','alma_ori_campo');"></td>
        	<?php } ?>
        	<input type="hidden" name="almacen_interno" value="<?php echo trim($almacen);?>"></td>
      		<td width="252" id="almaori"><?php echo $alma_ori_campo;?> </td>
      		<td><input type="hidden" name="alma_ori_campo" value="<?php echo $alma_ori_campo;?>"></td>
    	</tr>
    	<tr>
      		<td><div align="left">ALMACEN DESTINO</div></td>
      		<td><input type="text" id="nu_almacen_destino" name="alma_des" size="6" value="<?php echo trim($alma_des);?>" 
		<?php echo $read_des;?> >
        	<?php if(trim($alma_des)==""){ ?>
        		<img src="../images/help.gif" width="16" height="15" onMouseOver="this.style.cursor='pointer'" onClick="javascript:mostrarAyudaAlmacenes('js/ayuda_almacenes.php','<?php echo $flag_des;?>','alma_des','almades','alma_des_campo');"></td>	
      		<?php } ?>
      		<td id="almades"><?php echo $alma_des_campo;?></td>
      		<td><input type="hidden" name="alma_des_campo" value="<?php echo $alma_des_campo;?>"></td>
	
    	</tr>
    	<tr>
			<td height="21">
				<div align="left">FECHA EMISION</div></td>
			<td>
				<input type="hidden" id="fecha_ini" name = "fecha_ini" value='<?php echo $_REQUEST['fecha']; ?>' />
				<input maxlength="10" size="10" type='text' name ='fecha' id='fecha' class='fecha_formato' value='<?php echo $_REQUEST['fecha']; ?>' /></td>
			<td align="left"><span id="resultado"></span>
    	</tr>
    	<!-- <?php /*PIDE O NO PROVEEDORES*/if($enti=="P"){ ?> -->
    	<tr>
      		<td><div align="left">PROVEEDOR</div></td>
      		<td><input type="text" name="cod_proveedor" size="12" value="<?php echo $cod_proveedor;?>"  >
        	<img src="../images/help.gif" width="16" height="15" onMouseOver="this.style.cursor='pointer'" onclick="javascript:mostrarAyuda('js/lista_ayuda.php','cod_proveedor','despro','proveedores','des_pro');"></td>
      		<td id="despro"><?php echo $des_pro;?></td>
      		<td><input type="hidden" name="des_pro" value="<?php echo $des_pro;?>" ></td>
    	</tr>
    	<!-- <?php  } ?> -->
    	<!-- <?php  /*PIDE O NO TIPO Y # DE DOCUMENTO*/ 
  	if(trim($ref)=="S"){ ?> -->
    	<tr>
      		<td><div align="left">TIPO Y No DE DOCUMENTO</div></td>
      		<td> 
        	<input type="text" id="tipo_doc" name="tipo_doc" size="12" value="<?php echo $tipo_doc;?>" onblur="javascript:cceros(document.form1.tipo_doc,6 ,'tipo_doc' );" <?php if($enti=="P"){ ?>  onFocus="javascript:chiches(iframe1,'Proveedores',cod_proveedor.value,'des_pro','cod_proveedor');" <?php } ?>> 
        	<img src="../images/help.gif" width="16" height="15" onMouseOver="this.style.cursor='pointer'" onclick="javascript:mostrarAyuda('js/lista_ayuda.php','tipo_doc','desdoc','documentos','des_doc');"><br> 
        	<input type="text" name="serie_doc" size="4" value="<?php echo $serie_doc;?>" onkeypress = "return validar(event,1);" maxlength="4" onblur="javascript:cceros(document.form1.serie_doc, 4, 'serie_doc');" onFocus="javascript:chiches(iframe1,'Documentos',tipo_doc.value,'des_doc','tipo_doc');">
        	- 
        	<input type="text" name="num_doc" size="10" value="<?php echo $num_doc;?>" onkeypress = "return validar(event,2);" maxlength="8" onblur="javascript:cceros(document.form1.num_doc,8,'num_doc');" ></td>
      		<td id="desdoc"><?php echo $des_doc;?></td>
      		<td><input type="hidden" name="des_doc" value="<?php echo $des_doc;?>"></td>
    	</tr>
   	<!--<?php  } ?>-->
    	<!-- <?php /*PIDE O NO INGRESO DIRECTO NO */
  	if($natu=="2"){ ?> -->
    	<tr>
      		<td height="21"> <?php echo $_GET['cod'];?>
        	<div align="left">INGRESO DIRECTO ?</div></td>
      		<td>SI 
        	<input type="radio" name="ing_dir" value="SI" onClick="javascript:ocultarFila('ord_compra') ,ingdir.value='SI' , mostrarFila('numero_orden'),chiches(iframe1,'AlmacenDestino',alma_des.value,'almades','alma_des') ;" <?php if($ingdir=="SI"){echo "checked";} ?>>
        	| NO 
        	<input type="radio" name="ing_dir" value="NO" onClick="javascript:mostrarFila('ord_compra') , ingdir.value='NO', ocultarFila('numero_orden'),chiches(iframe1,'AlmacenDestino',alma_des.value,'almades','alma_des') ;" <?php if($ingdir=="NO"){echo "checked";} ?>></td>
      		<td id="ord_compra">Orden de Compra   <?php echo " -> ".$x_prov; ?>                                                              
        	<img src="../images/help.gif" width="16" height="15" onMouseOver="this.style.cursor='hand'" onClick="javascript:mostrarAyudaOrdenCompra('/sistemaweb/inventarios/js/cpag_ayuda_orddev.php',document.form1.cod_proveedor.value,document.form1.alma_des.value,'uno','form1.orden_compra','<?php echo $fm;?>',form1);" ></td>
      		<td><input type="hidden" name="ingdir" value="<?php echo $ingdir;?>" >
        	<input type="hidden" name="orden_compra" value="<?php echo $orden_compra;?>">
      		</td>
    	</tr>
    	<?php if(trim($fm)=="21"){ ?>
    	<tr>
    		<td colspan="2">&nbsp;</td>                                         
    		<td><input name="btn_complementarios" value="Datos Complementarios" onClick="javascript:mandarDatosComplementarios(form1,'Complementarios','<?php echo $fm;?>','<?php echo $fm;?> - <?php echo $des_form;?>','<?php echo $nro_mov;?>','<?php echo $fecha_actual;?>');"></td>
    	</tr>
    	<?php } ?>
    	<!-- <?php  } ?> -->
</table>
<br>
<div id="numero_orden" style="border-top: 1px solid rgb(0,0,0);"> 

<?php if($fm=="01" and trim($preciocentralizado)=="1"){ 
	$vis = "readonly=\"true\"";
} else {
	$vis = " ";
} ?>

<?php if($fm=="01"){ echo "Numero de Orden ".$numero_orden;} ?>
</div>
<table width="753" border="0" cellpadding="0" cellspacing="0">
	<tr>
      		<td width="154"><font size="1">CODIGO</font></td>
      		<td width="106"><font size="1">DESCRIPCION </font></td>
      		<td width="154"><font size="1">CANTIDAD </font></td>
      		<td width="51"><font size="1">STOCK</font></td>
      		<td><div align="center"><font size="1">TOTAL CON IGV</font></div></td>
      		<td><div align="center"><font size="1">TOTAL SIN IGV</font></div></td>
      		<td><div align="center"><font size="1">COSTO UNITARIO</font></div></td>
      		<td colspan="3">IGV
        	<?php if($c_porcentaje_igv==""){
	    		$CI = pg_exec("select floor(util_fn_igv()) as igv");
	    		$AI = pg_fetch_array($CI,0);
	    		$c_porcentaje_igv = $AI["igv"];
	    	}
		?> 
        	<input name="c_porcentaje_igv" type="text" value="<?php echo $c_porcentaje_igv;?>" size="5" readonly="true"> 
        	<br> 
        	<?php if($c_descuento==""){$c_descuento=0;} ?>
        	<input type="button" name="btn_descuento" value="Descuento" onClick="javascript:util_descuentos();" > 
        	<input name="c_descuento" type="text" value="<?php echo $c_descuento;?>" size="5"></td>
    	</tr>
    	<tr> 
      		<td> 
			<input type="text" name="cod_articulo" size="12" value="<?php echo $cod_articulo;?>">
			<img src="../images/help.gif" width="16" height="15" onMouseOver="this.style.cursor='pointer'" onclick="javascript:mostrarAyuda('js/lista_ayuda.php','cod_articulo','des_articulo','articulos2','des_art_campo','<?php echo $valor;?>','<?php echo $fm;?>', '<?php echo trim($alma_des);?>');"> 
			<input type="hidden" name="des_art_campo">
			<input type="hidden" name="entidad_validacion" value="<?php echo $enti;?>">
		</td>
		<td ><div id="des_articulo"><?php echo $descripcion_articulo;?></div> </td>
      		<td><div align="center"> 
		  	<input type="text" id="art_cantidad" name="art_cantidad" size="10" onFocus="javascript:chiches(iframe1,'Articulos',cod_articulo.value,'des_art_campo','cod_articulo');" onKeyUp="javascript:calculando(form1, this.value);">
			<?php if(trim($fm)=="21"){ ?>
		  	<!--<img src="../images/help.gif" width="16" height="15" onMouseOver="this.style.cursor='pointer'" onclick="javascript:mostrarAyudaGLP('js/calculo_ayuda_glp.php','<?php echo $nro_mov; ?>','<?php echo $fm; ?>',document.form1.cod_articulo.value,'<?php echo $_REQUEST['fecha'];?>','art_cantidad');"> -->
		  	<img src="../images/help.gif" width="16" height="15" onMouseOver="this.style.cursor='pointer'" onclick="javascript:mostrarAyudaGLP('js/calculo_ayuda_glp.php','<?php echo $nro_mov; ?>','<?php echo $fm; ?>',document.form1.cod_articulo.value,document.form1.fecha.value,'art_cantidad');">
			<?php } ?>
			</div>
		</td>
      		<td><div align="center">
		  	<input name="art_stock" type="text" id="art_stock"  value="" size="10" readonly="true">
			</div></td>
		<td><div align="left">
          		<input size="10" type="text" id="tmp_total_con_igv" name="tmp_total_con_igv" <?php echo $vis; ?> value="<?php echo $A["total_con_igv"];?>" 
	  		onKeyUp="javascript:util_costeo(tmp_total_con_igv,tmp_total_sin_igv,art_costo_uni,art_cantidad,c_porcentaje_igv,'CON_IGV');">
      			</div>
		</td>
      		<td><div align="left"><input size="10" type="text" id="tmp_total_sin_igv" name="tmp_total_sin_igv" <?php echo $vis; ?>  value="<?php echo $A["total_sin_igv"];?>" 
			onKeyUp="javascript:util_costeo(tmp_total_con_igv,tmp_total_sin_igv,art_costo_uni,art_cantidad,c_porcentaje_igv,'SIN_IGV');">
      			</div>
		</td>

		<td>
			<input  size="10" type="text" name="art_costo_uni" onKeyUp="CalcularTotales(this.value)" value="<?php echo $art_costo_uni;?>" <?php echo $vis; ?>  <?php echo $readonly_costo_uni;?> >
		</td>

		<?php if ($mostrar_ventanita=='1') { ?>
      		<td colspan="3"> <input name="btn_ingresar" type="button" value="Ingresar" onClick="javascript:mandarDatosArticulo(form1,'Ingresar','<?php echo $fm;?>','<?php echo $nro_mov;?>');">
		<?php } else { ?>
      		<td colspan="3"> <input id="btn_ingresar" name="btn_ingresar" type="button" value="Ingresar" onClick="javascript:mandarDatos2(form1,'Ingresar');">
		<?php } ?>
      		</td>
    	</tr>
    	<tr> 
      		<td><font size="1">CODIGO</font> <input type="hidden" name="autocompletar"></td>
      		<td><font size="1">DESCRIPCION </font></td>
      		<td><font size="1">CANTIDAD </font></td>
      		<td><font size="1">STOCK</font></td>
      		<td width="56"><div align="center"><font size="1">TOTAL CON IGV</font></div></td>
      		<td width="56"><div align="center"><font size="1">TOTAL SIN IGV</font></div></td>
      		<td width="55"><div align="center"><font size="1">COSTO UNITARIO</font></div></td>
      		<td width="57"><font size="1">TOTAL</font></td>
      		<td width="46"><font size="1">ORD. DE COMPRA</font></td>
      		<td width="69"><font size="1">Marcar</font></td>
    	</tr>

    	<?php
		for($i=0;$i<count($ITEMS);$i++){ 

			$A				= $ITEMS[$i];
			$total				= ($A[10] * $A[11]);
			$sum_total			= $sum_total + $total;
			$last_total_total		= $sum_total;
			$_SESSION["last_total_total"]	= $last_total_total;
//			$stock				=  stockArticulo("actual", "actual", $A[8], trim($almacen));
			$stock				=  stockArticulo("actual", "actual", $A[8], trim($alma_des));

	?>

    	<tr> 
      		<td><?php echo $A[8];?></td>
      		<td><?php echo $A[9];?></td>
      		<td><input size="10" type="text" name="CANTIDADES[]" value="<?php echo $A[10];?>" id="c_cantidad_item<?php echo "_$i";?>"></td>
      		<td><?php echo $stock;?></td>
		<td><input type="hidden" name="c_total_con_igv_base<?php echo "_$i";?>" value="<?php echo $A["total_con_igv"];?>">
	  		<input size="10" type="text" name="TOTAL_CON_IGV[]" id="c_total_con_igv<?php echo "_$i";?>" 
	  		value="<?php echo $A["total_con_igv"];?>" 
	  		onKeyUp="javascript:util_costeo2(c_total_con_igv<?php echo "_$i";?>,c_total_sin_igv<?php echo "_$i";?>,c_costo_uni<?php echo "_$i";?>,c_cantidad_item<?php echo "_$i";?>,c_porcentaje_igv,'CON_IGV',c_marca<?php echo "_$i";?>);"></td>
      		<td><input size="10" type="text" name="TOTAL_SIN_IGV[]" id="c_total_sin_igv<?php echo "_$i";?>" 
	  		value="<?php echo $A["total_sin_igv"];?>" onKeyUp="javascript:util_costeo2(c_total_con_igv<?php echo "_$i";?>,c_total_sin_igv<?php echo "_$i";?>,c_costo_uni<?php echo "_$i";?>,c_cantidad_item<?php echo "_$i";?>,c_porcentaje_igv,'SIN_IGV',c_marca<?php echo "_$i";?>);"></td>
      		<td><input size="10" type="text" name="COSTOS[]" value="<?php echo $A[11];?>" id="c_costo_uni<?php echo "_$i";?>"></td>
      		<td><?php echo $total;?> </td>
      		<td><?php echo $numero_orden;?></td>
      		<td><input type="checkbox" name="items[]" value="<?php echo $i;?>" id="c_marca<?php echo "_$i";?>"></td>
    	</tr>
<script>
	cadenas_de_ejecucion[<?php echo $i;?>]='utilAplicarDescuento(document.form1.c_total_con_igv<?php echo "_$i";?>,document.form1.c_total_sin_igv<?php echo "_$i";?>,document.form1.c_costo_uni<?php echo "_$i";?>,document.form1.c_cantidad_item<?php echo "_$i";?>,document.form1.c_porcentaje_igv,document.form1.c_descuento,document.form1.c_marca<?php echo "_$i";?>,document.form1.c_total_con_igv_base<?php echo "_$i";?>)';	
</script>
<?php } ?>
	<tr> 
      		<td>TOTALES</td>
      		<td>&nbsp;</td>
      		<td colspan="2">&nbsp; </td>
      		<td>&nbsp;</td>
      		<td colspan="2">&nbsp;</td>
      		<td><input type="hidden" name="total_total" value="<?php echo $sum_total;?>"> 
        	<?php echo $sum_total;?></td>
      		<td colspan="2">&nbsp;</td>
    	</tr>
    	<tr> 
      		<td> <input name="btn_eliminar" type="submit" id="btn_eliminar" value="Eliminar" onClick="javascript:mandarDatos2(form1,'Eliminar');"></td>
      		<td><input type="button" name="btn_mod_cabe" value="Cancelar Ingreso" onClick="javascript:mandarDatos2(form1,'CancelarIngreso');"></td>
      		<td colspan="2"><input type="button" id="btn_guardar_cambios" name="btn_guardar_cambios" value="Guardar Cambios" onClick="javascript:mandarDatos(form1,'GuardarCambios');"></td>
      		<td colspan="3"><input type="button" id="btn_term_ing" name="btn_term_ing" value="Terminar Ingreso" onClick="javascript:mandarDatos(form1,'TerminarIngreso');"> 
        	<input type="hidden" name="accion"></td>
      		<td colspan="3">&nbsp;</td>
    	</tr>
</table>
</form>
<?php //para mostrar u ocultar la ayuda para ordenes de compras en caso de ingresos no directos
	if($ing_dir == "" && $natu != "1" && $natu != "4") {
		?><script language="JavaScript">
			ocultarFila('ord_compra');
			ocultarFila('numero_orden');
		</script> <?php
	}
?>
<div align="center"></div>
<?php 
	if($autocompletar == "yes") {
		?><script language="JavaScript">
		form1.des_art_campo.value='<?php echo $descripcion_articulo;?>';
		form1.art_cantidad.focus();
		</script>
		<?php
	}
	
	if($accion == "Ingresar") {
		?><script language="JavaScript">
		document.getElementById("form1").cod_articulo.value='';
		document.getElementById("form1").cod_articulo.focus();
		</script>
		<?php
	}		
?>
<form method="post" action="completarCampos.php" target="iframe1" name="form7">
<input type="hidden" name="campo">
<input type="hidden" name="articulo">
</form>
<iframe name="iframe1" height="0" width="0"></iframe>

<?php
	if($fm == "01" && trim($accion) == "") {
		?><script language="JavaScript">
		document.getElementById("form1").alma_des.focus();
		</script>
		<?php
	}
?>

</body>
</html>
<?php pg_close();?>

