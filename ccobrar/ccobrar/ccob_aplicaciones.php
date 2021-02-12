<?php
include("../valida_sess.php");
include("../menu_princ.php");
include("../functions.php");
include("store_procedures.php");

require("../clases/funciones.php");
$funcion = new class_funciones;

// crea la clase para controlar errores
//$clase_error = new OpensoftError;

// conectar con la base de datos
$conector_id=$funcion->conectar("","","","","");
$hoy = date("d/m/Y");
if($c_fecha_aplicacion==""){$c_fecha_aplicacion=$hoy;}

switch($accion){

	case "Aplicar":
	
	pg_exec("SELECT ccob_fn_aplicaciones('$c_cod_documento_cargo','$c_num_documento_cargo','$c_cod_documento_abono','$c_num_documento_abono',$c_importe_aplicacion,TO_DATE('$c_fecha_aplicacion','DD/MM/YYYY') )");
	
	break;
	
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<script>
function completarCampos(iframe,accion,codigo_busqueda,form){
	//alert(codigo_busqueda);
	var ok = false;
	
	if(accion=="completar_documento_cargo"){
		if(form.c_cod_documento_cargo.value!=""){
		ok = true;
		}else{
			form.c_cod_documento_cargo.focus();
		}
	}

	
	if(accion=file:///home/sistemas/public_html/ccobrar/ccob_aplicaciones.php="completar_campos_cargo"){
		if(form.c_cod_documento_cargo.value!=''
		&& form.c_num_documento_cargo.value!=''){
		ok = true;
		//alert('--'+form.c_num_documento_cargo.value+'--');
		}else{
			form.c_num_documento_cargo.focus();
		}
	}
	
	
	if(accion=='completar_documento_abono'){
		if(form.c_num_documento_cargo.value!=''){
		ok = true;
		}else{
			form.c_cod_documento_cargo.focus();
		}
	}
	
	
	if(accion=='completar_campos_abono'){
		if(form.c_cod_documento_abono.value!='' 
		&& form.c_num_documento_abono.value!=''){
		ok = true;
		}else{
			form.c_cod_documento_abono.focus();
		}
	}
	
	
	if(ok){
	iframe.location='ccob_aplicaciones_iframe.php?accion='+accion+"&codigo_busqueda="+codigo_busqueda;
	}
}

function enviarDatos(form,accion){

	var pasa = false;
	if(accion=="Aplicar"){
		pasa = validarAplicacion(form);
	}

	if(pasa){
		form.accion.value=opcion;
		form.submit();
	}
}


function validarAplicacion(form){
	var res = true;
	var saldo_cargo = pasarNumerico(form.c_saldo_cargo.value);
	var saldo_abono = pasarNumerico(form.c_saldo_abono.value);
	var importe_aplicacion = pasarNumerico(form.c_importe_aplicacion.value);
	
	if(res){
		if(form.c_cod_documento_cargo.value==''){
			res = false;
			alert('No se ha especificado el tipo de documento de cargo');
		}
	}

	if(res){
		if(form.c_num_documento_cargo.value==''){
			res = false;
			alert('No se ha especificado el numero del documento de cargo');
		}
	}

	if(res){
		if(form.c_cod_documento_abono.value==''){
			res = false;
			alert('No se ha especificado el tipo de documento de abono');
		}
	}

	if(res){
		if(form.c_num_documento_abono.value==''){
			res = false;
			alert('No se ha especificado el numero del documento de abono');
		}
	}

	if(res){
		if(form.c_fecha_aplicacion.value==''){
			res = false;
			alert('No se ha especificado la fecha');
		}
	}

	if(res){
		if(form.c_importe_aplicacion.value==''){
			res = false;
			alert('No se ha especificado el importe a aplicar');
		}
	}



	if(res){
		if(saldo_cargo < saldo_abono){
			if(importe_aplicacion >saldo_cargo ){ 
				res = false;
				alert("importe a aplicar mayor que el saldo de cargo");
			}
		}
	}
	
	if(res){
		if(saldo_abono < saldo_cargo){
			if(importe_aplicacion >saldo_abono ){ 
				res = false;
				alert("importe a aplicar mayor que el saldo de abono");
			}
		}
	}
	
	
return res;
}

function pasarEntero(num){

        ret = parseInt(num);
        if(isNaN(ret)){
                //alert("Valor no permitido");
                ret = 0;
        }

return ret;
}

function pasarNumerico(num){

        ret = parseFloat(num);
        if(isNaN(ret)){
                //alert("Valor no permitido");
                ret = 0;
        }

return ret;
}

</script>
<title>Aplicaciones</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../acosa.css" rel="stylesheet" type="text/css">
</head>

<body>
<form name="form1" method="post" action="">
  <table width="97%" border="1">
    <tr> 
      <th colspan="3">DOCUMENTO DE CARGO</th>
      <th colspan="2"><input type="text" name="c_cod_cliente"></th>
      <th colspan="2"><input type="text" name="c_saldo_cargo"></th>
    </tr>
    <tr> 
      <td width="11%">DOCUMENTO:</td>
      <td width="6%"><input type="text" name="c_cod_documento_cargo" size="7" onBlur="javascript:completarCampos(ifr1,'completar_documento_cargo',this.value,form1);"></td>
      <td width="17%" id="fila_desc_documento_cargo">&nbsp;</td>
      <td width="7%">NRO.</td>
      <td width="18%"><input type="text" name="c_num_documento_cargo" onBlur="javascript:completarCampos(ifr1,'completar_campos_cargo',c_cod_documento_cargo.value+'&c_num_docu_cargo='+this.value,form1);"></td>
      <td width="17%">MONEDA :</td>
      <td width="24%" id="fila_moneda_cargo">&nbsp;</td>
    </tr>
    <tr> 
      <td>REFERENCIA:</td>
      <td colspan="2" id="fila_doc_ref_cargo">&nbsp;</td>
      <td>NRO.</td>
      <td id="fila_numdoc_ref_cargo">&nbsp;</td>
      <td>IMPORTE INICIAL :</td>
      <td id="fila_importe_inicial_cargo"></strong></td>
    </tr>
    <tr> 
      <td>FECHA DE RECEPCION:</td>
      <td colspan="2" id="fila_fecha_recepcion_cargo">&nbsp;</td>
      <td>FECHA VENTA:</td>
      <td id="fila_fecha_venta_cargo">&nbsp;</td>
      <td>SALDO ACTUAL:</td>
      <td id="fila_saldo_cargo">&nbsp;</td>
    </tr>
    <tr> 
      <td>CLIENTE:</td>
      <td colspan="6" id="fila_cliente_cargo">&nbsp;</td>
    </tr>
  </table>
  <br>
  <table width="97%" border="1">
    <tr> 
      <th height="23" colspan="3">DOCUMENTO DE ABONO</th>
      <th colspan="2"><input type="text" name="c_saldo_abono" ></th>
      <th colspan="2">&nbsp;</th>
    </tr>
    <tr> 
      <td width="11%">DOCUMENTO:</td>
      <td width="6%"><input type="text" name="c_cod_documento_abono" size="7" onBlur="javascript:completarCampos(ifr1,'completar_documento_abono',this.value,form1);"></td>
      <td width="17%" id="fila_desc_documento_abono">&nbsp;</td>
      <td width="7%">NRO.</td>
      <td width="18%"><input type="text" name="c_num_documento_abono" onBlur="javascript:completarCampos(ifr1,'completar_campos_abono',c_cod_documento_abono.value+'&c_num_docu_abono='+this.value+'&c_cod_cliente='+c_cod_cliente.value,form1);"></td>
      <td width="16%">MONEDA :</td>
      <td width="25%" id="fila_moneda_abono">&nbsp;</td>
    </tr>
    <tr> 
      <td>REFERENCIA:</td>
      <td colspan="2" id="fila_doc_ref_abono">&nbsp;</td>
      <td>NRO.</td>
      <td id="fila_numdoc_ref_abono">&nbsp;</td>
      <td>IMPORTE INICIAL :</td>
      <td id="fila_importe_inicial_abono">&nbsp;</td>
    </tr>
    <tr> 
      <td>FECHA DE RECEPCION:</td>
      <td colspan="2" id="fila_fecha_recepcion_abono">&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>SALDO ACTUAL:</td>
      <td id="fila_saldo_abono">&nbsp;</td>
    </tr>
  </table>
	
  <br>
  <table  width="97%" border="1">
    <tr> 
      <th width="16%">APLICACION</th>
      <td width="23%">&nbsp;</td>
      <td width="18%">&nbsp;</td>
      <td width="33%">&nbsp;</td>
      <td width="3%">&nbsp;</td>
      <td width="7%">&nbsp;</td>
    </tr>
    <tr> 
      <td>RUBRO:</td>
      <td><input type="text" name="c_rubro_aplicacion"></td>
      <td>FECHA: </td>
      <td><input type="text" name="c_fecha_aplicacion"></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td>EMISOR:</td>
      <td><input type="text" name="c_emisor_aplicacion"></td>
      <td>IMPORTE A APLICAR: </td>
      <td><input type="text" name="c_importe_aplicacion"></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td><div align="center">
          <input type="button" name="btn_aplicar" value="Aplicar" onClick="javascript:enviarDatos(form1,'Aplicar');">
        </div></td>
      <td><input type="hidden" name="accion" ></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
  </table>
  <iframe name="ifr1" width="800" height="400"></iframe>
</form>
</body>
</html>
