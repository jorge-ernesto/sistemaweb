<?php
include("../../valida_sess.php");
session_start();
include("../../clases/funciones.php");
$funcion = new class_funciones;
// crea la clase para controlar errores
$clase_error = new OpensoftError;
$clase_error->_error();
// conectar con la base de datos
$conector_id=$funcion->conectar("","","","","");

extract($_REQUEST);

//$COMP = $_SESSION["ARR_COMP"];
$arrCodigo = explode(' ',$_REQUEST['registroid']);
//echo $cod_cliente."-".$accion."-";

if(isset($_GET['c_obs2'])) {
	echo '2: '.$_GET['c_obs2'];
	exit();
}

if(isset($_GET['type'])) {
	$type = $_GET['type'];
}

switch($accion){

	case "Modificar":

		//$COMP["comp_dir"] 		= $_REQUEST['c_comp_dir'];
		if(!empty($_REQUEST['no_detraccion_cuenta']) && !empty($_REQUEST['nu_detraccion_importe']) && !empty($_REQUEST['nu_detraccion_porcentaje']) && !empty($_REQUEST['nu_detraccion_codigo']))
			$COMP["comp_dir"] 		= $_REQUEST['no_detraccion_cuenta'].'*'.$_REQUEST['nu_detraccion_importe'].'*'.$_REQUEST['nu_detraccion_porcentaje'].'*'.$_REQUEST['nu_detraccion_codigo'];
		else
			$COMP["comp_dir"] 		= null;

		$COMP["razon_social"] 	= $_REQUEST['c_razon_social'];
		$COMP["direccion"] 		= $_REQUEST['c_direccion'];
		$COMP["ruc"] 			= $_REQUEST['c_ruc'];
		$COMP["obs1"] 			= $_REQUEST['c_obs1'];

		if ($type == '11' || $type == '20')
			$COMP["obs2"] = $_REQUEST['doc_number'].'*'.$_REQUEST['doc_serie'].'*'.$_REQUEST['doc_type'];
		else
			$COMP["obs2"] = null;

		$COMP["obs3"] 			= $_REQUEST['c_obs3'];
		$COMP["obs1"] 			= str_replace(array("\r","\n","|"), array('','. ',''), $COMP["obs1"]);
		$COMP["comp_dir"] 		= str_replace(array("\r","\n","|"), array('','. ',''), $COMP["comp_dir"]);

		$query = "
			SELECT
				COUNT(*) AS total
			FROM
				fac_ta_factura_complemento 
			WHERE
				trim(ch_fac_tipodocumento)||trim(ch_fac_seriedocumento)||trim(ch_fac_numerodocumento)||trim(cli_codigo)='".$_REQUEST['registroid']."'
		";

		$rs 	= pg_exec($query);
		$auxi 	= pg_fetch_array($rs);

		if ($auxi['total']>0){
		echo "Actualizando registro....";
			$query = "UPDATE FAC_TA_FACTURA_COMPLEMENTO SET NU_FAC_COMPLEMENTO_DIRECCION='".trim($COMP["comp_dir"])."'".
					", CH_FAC_NOMBRECLIE='".trim($COMP["razon_social"])."', NU_FAC_DIRECCION='".trim($COMP["direccion"])."', CH_FAC_RUC='".trim($COMP["ruc"])."'
					, CH_FAC_OBSERVACION1='".trim($COMP["obs1"])."', CH_FAC_OBSERVACION2='".trim($COMP["obs2"])."', CH_FAC_OBSERVACION3='".trim($COMP["obs3"])."'
					WHERE trim(ch_fac_tipodocumento)||trim(ch_fac_seriedocumento)||trim(ch_fac_numerodocumento)||trim(cli_codigo)='".$_REQUEST['registroid']."'";	

		}else{
		echo "Insertando registro....";
			$query = "INSERT INTO FAC_TA_FACTURA_COMPLEMENTO (CH_FAC_TIPODOCUMENTO, 
			CH_FAC_SERIEDOCUMENTO, CH_FAC_NUMERODOCUMENTO, CLI_CODIGO, DT_FAC_FECHA, 
			CH_FAC_OBSERVACION1, CH_FAC_OBSERVACION2, CH_FAC_OBSERVACION3, CH_FAC_RUC, NU_FAC_DIRECCION, NU_FAC_COMPLEMENTO_DIRECCION,
			DT_FECHACTUALIZACION, CH_FAC_NOMBRECLIE) SELECT CH_FAC_TIPODOCUMENTO, CH_FAC_SERIEDOCUMENTO, 
			CH_FAC_NUMERODOCUMENTO, CLI_CODIGO, DT_FAC_FECHA, '".trim($COMP["obs1"])."','".trim($COMP["obs2"])."','".trim($COMP["obs3"])
			."','".trim($COMP["ruc"])."','".trim($COMP["direccion"])."','".trim($COMP["comp_dir"])."',NOW(),'".trim($COMP["razon_social"])."' 
			FROM FAC_TA_FACTURA_CABECERA 
			WHERE trim(ch_fac_tipodocumento)||trim(ch_fac_seriedocumento)||trim(ch_fac_numerodocumento)||trim(cli_codigo)='".$_REQUEST['registroid']."'";	
		}
		pg_exec($query);
		echo "<br/>proceso  Realizado....";
		print "<script>window.close();</script>";
		break;
	
	case "Completar":

		$query="
			SELECT
				*,
				(string_to_array(nu_fac_complemento_direccion, '*'))[1] AS no_detraccion_cuenta,
				(string_to_array(nu_fac_complemento_direccion, '*'))[2] AS nu_detraccion_importe,
				(string_to_array(nu_fac_complemento_direccion, '*'))[3] AS nu_detraccion_porcentaje,
				(string_to_array(nu_fac_complemento_direccion, '*'))[4] AS nu_detraccion_codigo
			FROM
				fac_ta_factura_complemento 
			WHERE
				trim(ch_fac_tipodocumento)||trim(ch_fac_seriedocumento)||trim(ch_fac_numerodocumento)||trim(cli_codigo)='".$_REQUEST['registroid']."'
		";

		$rs = pg_exec($query);

		if(pg_numrows($rs)>0){

			$query="
				SELECT
					ch_fac_nombreclie as cli_razsocial,
					nu_fac_direccion as cli_direccion, 
					ch_fac_ruc as cli_ruc,
					ch_fac_observacion1,
					ch_fac_observacion2,
					ch_fac_observacion3,
					(string_to_array(nu_fac_complemento_direccion, '*'))[1] AS no_detraccion_cuenta,
					(string_to_array(nu_fac_complemento_direccion, '*'))[2] AS nu_detraccion_importe,
					(string_to_array(nu_fac_complemento_direccion, '*'))[3] AS nu_detraccion_porcentaje,
					(string_to_array(nu_fac_complemento_direccion, '*'))[4] AS nu_detraccion_codigo
				FROM
					fac_ta_factura_complemento
				WHERE
					trim(ch_fac_tipodocumento)||trim(ch_fac_seriedocumento)||trim(ch_fac_numerodocumento)||trim(cli_codigo)='".$_REQUEST['registroid']."'
			";

		}else{

			$query="
				SELECT
					trim(cli_razsocial) AS cli_razsocial,
					cli_direccion,
					cli_ruc
				FROM
					int_clientes
				WHERE
					cli_codigo = '" . $cod_cliente . "'
			";

			$COMP = $_SESSION["ARR_COMP"];

		}
     
		if($_REQUEST['registroid'] == ""){

			if($cod_cliente!=""){
		    
		    	$rs = pg_exec($query);

		    	if(pg_numrows($rs) > 0){

					$A = pg_fetch_array($rs,0);

					if ($COMP['comp_dir']=='')
						$COMP["no_detraccion_cuenta"] = $A["no_detraccion_cuenta"];

					if ($COMP['comp_dir']=='')
						$COMP["nu_detraccion_importe"] = $A["nu_detraccion_importe"];

					if ($COMP['comp_dir']=='')
						$COMP["nu_detraccion_porcentaje"] = $A["nu_detraccion_porcentaje"];

					if ($COMP['comp_dir']=='')
						$COMP["nu_detraccion_codigo"] = $A["nu_detraccion_codigo"];

					if ($COMP['razon_social']=='')
						$COMP["razon_social"] = $A["cli_razsocial"];

					if ($COMP['direccion']=='')
						$COMP["direccion"] = $A["cli_direccion"];

					if ($COMP['ruc']=='')
						$COMP["ruc"] = $A["cli_ruc"];

					if ($COMP['obs1']=='')
						$COMP["obs1"] = $A["ch_fac_observacion1"];

					if ($COMP['obs2']=='')
						$COMP["obs2"] = $A["ch_fac_observacion2"];

					if ($COMP['obs3']=='')
						$COMP["obs3"] = $A["ch_fac_observacion3"];

					$_SESSION["ARR_COMP"] = $COMP;

				}
			}

    	}elseif($_REQUEST['registroid'] != "" ){

			if($cod_cliente!=""){

	    		$rs = pg_exec($query);

	    		if(pg_numrows($rs) > 0){

				$A = pg_fetch_array($rs,0);

				if ($COMP['comp_dir']=='')
					$COMP["no_detraccion_cuenta"] = $A["no_detraccion_cuenta"];

				if ($COMP['comp_dir']=='')
					$COMP["nu_detraccion_importe"] = $A["nu_detraccion_importe"];

				if ($COMP['comp_dir']=='')
					$COMP["nu_detraccion_porcentaje"] = $A["nu_detraccion_porcentaje"];

				if ($COMP['comp_dir']=='')
					$COMP["nu_detraccion_codigo"] = $A["nu_detraccion_codigo"];

				if ($COMP['razon social']=='')
					$COMP["razon_social"] = $A["cli_razsocial"]; 

				if ($COMP['direccion']=='')
					$COMP["direccion"] = $A["cli_direccion"];

				if ($COMP['ruc']=='')
					$COMP["ruc"] = $A["cli_ruc"];

				if ($COMP['obs1']=='')
					$COMP["obs1"] = $A["ch_fac_observacion1"];

				if ($COMP['obs2']=='')
					$COMP["obs2"] = $A["ch_fac_observacion2"];

				if ($COMP['obs3']=='')
					$COMP["obs3"] = $A["ch_fac_observacion3"];

				$_SESSION["ARR_COMP"] = $COMP;

			    }
			}
    	}

	break;
    
	case "Cancelar":

        $_SESSION["ARR_COMP"]	= null;
		$COMP					= null;

		unset($ARR_COMP);
		unset($COMP);

		print "<script>window.close();</script>";

	break;
    
	case "Terminar":

		$COMP["razon_social"]	= $c_razon_social; 
		$COMP["direccion"]		= $c_direccion;
		$COMP["ruc"] 			= $c_ruc;
		
		if(!empty($_REQUEST['no_detraccion_cuenta']) && !empty($_REQUEST['nu_detraccion_importe']) && !empty($_REQUEST['nu_detraccion_porcentaje']) && !empty($_REQUEST['nu_detraccion_codigo']))
			$COMP["comp_dir"] 		= $_REQUEST['no_detraccion_cuenta'].'*'.$_REQUEST['nu_detraccion_importe'].'*'.$_REQUEST['nu_detraccion_porcentaje'].'*'.$_REQUEST['nu_detraccion_codigo'];
		else
			$COMP["comp_dir"] 		= null;

		$COMP["obs1"] 			= $c_obs1;
		if ($type == '11' || $type == '20')
			$COMP["obs2"] = $_REQUEST['doc_number'].'*'.$_REQUEST['doc_serie'].'*'.$_REQUEST['doc_type'];
		else
			$COMP["obs2"] = null;

		$COMP["obs1"] 			= str_replace(array("\r","\n","|"), array('','. ',''), $COMP["obs1"]);
		$COMP["comp_dir"] 		= str_replace(array("\r","\n","|"), array('','. ',''), $COMP["comp_dir"]);
		$COMP["obs3"] 			= $c_obs3;
		$_SESSION["ARR_COMP"] 	= $COMP;

	   	print "<script>window.close();</script>";

	break;
}

if(isset($COMP["obs2"])) {
	$result 	= explode('*',$COMP["obs2"]);
	$_doc_type 	= isset($result[2]) ? $result[2] : '';
	$_doc_serie = isset($result[1]) ? $result[1] : '';
	$_doc_num 	= isset($result[0]) ? $result[0] : '';
}
?>

<!DOCTYPE html>
<html lang="en">
	<head>
	    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
		<title>Datos Complementarios</title>
		<link href="../../sistemaweb.css" rel="stylesheet" type="text/css">
	</head>
	<body>
	<form name="form1" method="post" action="">
		Complementos y Observaciones 
  		<table width="91%" border="0">
  			<input type="hidden" id="tipo_ref" value="<?php echo $type;?>">
    		<tr>
			    <td width="30%" height="22">RAZON SOCIAL :</td>
			    <td width="47%"><input type="text" name="c_razon_social" value="<?php echo $COMP["razon_social"];?>" size="50" ></td>
			    <td width="23%">&nbsp;</td>
    		</tr>
			<tr>
			    <td>DIRECCION :</td>
			    <td><input type="text" name="c_direccion" value="<?php echo  $COMP["direccion"];?>" size="30" maxlength="30"></td>
			    <td>&nbsp;</td>
    		</tr>
    		<tr>
      <td>R.U.C. :</td>
      <td><input type="text" name="c_ruc" value="<?php echo  $COMP["ruc"];?>" size="15" maxlength="15"></td>
      <td>&nbsp;</td>
    </tr>
	<tr>
		<td colspan="3">-</td>	
	</tr>
    <tr>
      <td>OBSERVACIONES (1) :</td>
      <td><input type="text" name="c_obs1" autocomplete="off" value="<?php echo  $COMP["obs1"];?>" size="140" maxlength="150"></td>
      <td>&nbsp;</td>
    </tr>
    <tr class="none"> 
      <td height="24">[numero_doc * serie_doc * tipo_doc)] :</td>
      <td><input type="text" name="c_obs2" value="<?php echo  $COMP["obs2"];?>" size="40" maxlength="40"></td>
    </tr>

    <tr <?php echo $type == 11 || $type == 20 ? '' : 'class="none"' ?>>
    	<td><label>Tipo de documento: </label></td>
    	<td>
    		<select name="doc_type">
    			<option value="" <?php echo $_doc_type == '' ? 'selected' : '' ?>>SELECCIONAR</option>
    			<option value="35" <?php echo $_doc_type == 35 ? 'selected' : '' ?>>BOLETA</option>
    			<option value="10" <?php echo $_doc_type == 10 ? 'selected' : '' ?>>FACTURA</option>
    		</select>
    	</td>
    </tr>
    <tr <?php echo $type == 11 || $type == 20 ? '' : 'class="none"' ?>>
    	<td><label>Serie: </label></td>
    	<td><input name="doc_serie" id="doc_serie" value="<?php echo $_doc_serie ?>" maxlength="4" size="6"></td>
    </tr>
    <tr <?php echo $type == 11 || $type == 20 ? '' : 'class="none"' ?>>
    	<td><label>N&uacute;mero de Documento: </label></td>
    	<td><input name="doc_number" id="doc_number" onblur="_checkCompleteDN()" value="<?php echo $_doc_num ?>" maxlength="8" size="10"></td>
    </tr>
    <tr <?php echo $type == 11 || $type == 20 ? '' : 'class="none"' ?>>
      <td>fecha doc original(dd/mm/aaaa) :</td>
      <td><input type="text" name="c_obs3" value="<?php echo  $COMP["obs3"];?>" size="40" maxlength="40"></td>
      <td>Ejem(dd/mm/yyyy)</td>
    </tr>
    <tr>
    	<td colspan="3"><div id="info_complentarios"></div><br></td>
    </tr>
    <!-- Inicio Detraccion -->
    <?php 
    	$detraccion = false;
    	$checkedSi 	= null;
    	$checkedNo 	= null;
    	if (!empty($COMP['no_detraccion_cuenta']) || !empty($COMP['nu_detraccion_importe']) || !empty($COMP['nu_detraccion_porcentaje'])){
    		$detraccion = true;
    		$checkedSi 	= "checked";
    		$checkedNo 	= null;
    	}else{
    		$checkedSi 	= null;
    		$checkedNo 	= "checked";
    	}
    ?>
    <tr>
    	<td>Detraccion: </td>
    	<td colspan="2">
    		Si: <input type="radio" onclick="javascript:Detraccion('<?php echo $detraccion; ?>');" name="yesno" id="yesCheck" <?php echo $checkedSi; ?>>
    		No: <input type="radio" onclick="javascript:Detraccion('<?php echo $detraccion; ?>');" name="yesno" id="noCheck" <?php echo $checkedNo; ?>>
    	</td>
    </tr>
    <?php if ($detraccion){ ?>
    <tr>
    	<td>
    		<div id="label-no_detraccion_cuenta" style="visibility:hidden">Nro. Cuenta: </div>
		</td>
    	<td>
	    	<div id="txt-no_detraccion_cuenta" style="visibility:hidden">
	    		<input type="text" id="no_detraccion_cuenta" name="no_detraccion_cuenta" value="<?php echo  $COMP["no_detraccion_cuenta"];?>" size="20" maxlength="20">
	    	</div>
	    </td>
	    <td>&nbsp;</td>
    </tr>
    <tr>
    	<td>
    		<div id="label-nu_detraccion_importe" style="visibility:hidden">Importe: </div>
		</td>
    	<td>
    		<div id="txt-nu_detraccion_importe" style="visibility:hidden">
	      		<input type="text" id="nu_detraccion_importe" name="nu_detraccion_importe" value="<?php echo  $COMP["nu_detraccion_importe"];?>" size="20" maxlength="20">
	      	</div>
	    </td>
	    <td>&nbsp;</td>
    </tr>
    <tr>
    	<td>
    		<div id="label-nu_detraccion_porcentaje" style="visibility:hidden">Porcentaje: </div>
		</td>
    	<td>
    		<div id="txt-nu_detraccion_porcentaje" style="visibility:hidden">
	      		<input type="number" id="nu_detraccion_porcentaje" name="nu_detraccion_porcentaje" value="<?php echo  $COMP["nu_detraccion_porcentaje"];?>" size="10" maxlength="10">
	      	</div>
	    </td>
	    <td>&nbsp;</td>
    </tr>
    <tr>
    	<td>
    		<div id="label-nu_detraccion_codigo" style="visibility:hidden">Codigo Bienes y Servicios: </div>
		</td>
    	<td>
    		<div id="txt-nu_detraccion_codigo" style="visibility:hidden">
	      		<input type="text" id="nu_detraccion_codigo" name="nu_detraccion_codigo" value="<?php echo  $COMP["nu_detraccion_codigo"];?>" size="3" minlength="3" maxlength="3">
			</div>	      		
	    </td>
	    <td>&nbsp;</td>
    </tr>
    <?php 
    }else{
    ?>
    <tr>
    	<td>
    		<div id="label-no_detraccion_cuenta" style="visibility:hidden">Nro. Cuenta: </div>
		</td>
    	<td>
	    	<div id="txt-no_detraccion_cuenta" style="visibility:hidden">
	      		<input type="text" id="no_detraccion_cuenta" name="no_detraccion_cuenta" value="<?php echo  $COMP["no_detraccion_cuenta"];?>" size="20" maxlength="20">
		    </div>
	    </td>
	    <td>&nbsp;</td>
    </tr>
    <tr>
    	<td>
    		<div id="label-nu_detraccion_importe" style="visibility:hidden">Importe: </div>
		</td>
    	<td>
	    	<div id="txt-nu_detraccion_importe" style="visibility:hidden">
	      		<input type="text" onkeypress="return validar(event,3)" id="nu_detraccion_importe" name="nu_detraccion_importe" value="<?php echo  $COMP["nu_detraccion_importe"];?>" size="20" maxlength="20">
		    </div>
	    </td>
	    <td>&nbsp;</td>
    </tr>
    <tr>
    	<td>
    		<div id="label-nu_detraccion_porcentaje" style="visibility:hidden">Porcentaje %: </div>
		</td>
    	<td>
	    	<div id="txt-nu_detraccion_porcentaje" style="visibility:hidden">
	      		<input type="tel" onkeypress="return validar(event,3)" id="nu_detraccion_porcentaje" name="nu_detraccion_porcentaje" value="<?php echo  $COMP["nu_detraccion_porcentaje"];?>" size="10" maxlength="10">
		    </div>
	    </td>
	    <td>&nbsp;</td>
    </tr>
    <tr>
    	<td>
    		<div id="label-nu_detraccion_codigo" style="visibility:hidden">Codigo Bienes y Servicios: </div>
		</td>
    	<td>
	    	<div id="txt-nu_detraccion_codigo" style="visibility:hidden">
	      		<input type="text" onkeypress="return validar(event,2)" id="nu_detraccion_codigo" name="nu_detraccion_codigo" value="<?php echo  $COMP["nu_detraccion_codigo"];?>" size="3" minlength="3" maxlength="3">
		    </div>
	    </td>
	    <td>&nbsp;</td>
    </tr>
    <!-- Fin Detraccion -->
    <?php } ?>
    <tr>
      <td>&nbsp;</td>
      <td><div align="center"><?php 
          if ($_REQUEST['modificar']=='S'){
          	echo('<input type="button" name="btn_modificar" value="Modificar" onClick="javascript:enviarDatos(form1,'."'Modificar'".')" >');
          }
           ?>
          <input type="button" name="btn_terminar" value="Terminar" onClick="javascript:enviarDatos(form1,'Terminar')">
          <input type="button" name="btn_cancelar" value="Cancelar" onClick="javascript:enviarDatos(form1,'Cancelar')">
          <input type="hidden" name="accion">
        </div></td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
  </table>
</form>
</body>
<footer>
	<script>
function enviarDatos(form,tipo) {
	
	var tipo_ref 					= document.getElementById("tipo_ref"); 
	var serial 						= document.getElementById("doc_serie");
	var number 						= document.getElementById("doc_number");

	var no_detraccion_cuenta 		= document.getElementById('no_detraccion_cuenta');
	var nu_detraccion_importe 		= document.getElementById('nu_detraccion_importe');
	var nu_detraccion_porcentaje 	= document.getElementById('nu_detraccion_porcentaje');
	var nu_detraccion_codigo 		= document.getElementById('nu_detraccion_codigo');

	if((tipo_ref.value == '11' || tipo_ref.value == 11) || (tipo_ref.value == '20' || tipo_ref.value == 20)) {
		if(_alphanumeric(serial)) {
			if(serial.value.length == 3 || serial.value.length == 4) {
				//alert('true ==4 '+serial.value+' : '+serial.value.length);
				if(_numeric(number)) {
					//alert('si es numerico');
					//if(number.value.length == 8) {
						//alert('Listo para enviar formulario');
						printHtmlById('info_complentarios','');
						form.accion.value=tipo;
						form.submit();
					/*} else {
						printHtmlById('info_complentarios','<div class="info info-error">Error, el <b>N&uacute;mero de Documento</b> debe tener 8 caracteres</div>');
						//alert('false !=8 '+number.value+' : '+number.value.length);
					}*/
				} else {
					printHtmlById('info_complentarios','<div class="info info-error">Error, solo puede ingresar n&uacute;meros en <b>N&uacute;mero de Documento</b></div>');
					//alert('no es numerico');
				}
			} else {
				printHtmlById('info_complentarios','<div class="info info-error">Error, la <b>Serie</b> debe tener 4 caracteres</div>');
				//alert('false !=4 '+serial.value+' : '+serial.value.length);
			}
		} else {
			//alert('false');
			printHtmlById('info_complentarios','<div class="info info-error">Error, la <b>Serie</b> solo puede contener numeros y letras</div>');
		}

	}else if(document.getElementById('yesCheck').checked){
		if(no_detraccion_cuenta.value.length > 0 && nu_detraccion_importe.value.length > 0 && nu_detraccion_porcentaje.value.length > 0 && nu_detraccion_codigo.value.length > 0){
			printHtmlById('info_complentarios','');
			form.accion.value=tipo;
			form.submit();
		}else{
			if(no_detraccion_cuenta.value.length === 0){
				printHtmlById('info_complentarios','<div class="info info-error">Error: Debe ingresar <b>Nro. Cuenta.</b></div>');
			}else if(_cuenta(no_detraccion_cuenta)==false){
				printHtmlById('info_complentarios','<div class="info info-error">Error <b>Nro. Cuenta</b>: Solo puede ingresar <b>numeros y signo(-).</b></div>');
			}else if(nu_detraccion_importe.value.length === 0){
				printHtmlById('info_complentarios','<div class="info info-error">Error: Debe ingresar <b>Importe.</b></div>');
			}else if(_decimal(nu_detraccion_importe)==false){
				printHtmlById('info_complentarios','<div class="info info-error">Error <b>Importe</b>: Solo puede ingresar <b>numeros y signo(.).</b></div>');
			}else if(nu_detraccion_porcentaje.value.length === 0){
				printHtmlById('info_complentarios','<div class="info info-error">Error: Debe ingresar <b>Porcentaje.</b></div>');
			}else if(_numeric(nu_detraccion_porcentaje)==false){
				printHtmlById('info_complentarios','<div class="info info-error">Error <b>Porcentaje</b>: Debe ingresar <b>solo numeros.</b></div>');
			}else if(nu_detraccion_codigo.value.length === 0){
				printHtmlById('info_complentarios','<div class="info info-error">Error: Debe ingresar <b>Codigo de Bienes y Servicios.</b></div>');
			}else if(_numeric(nu_detraccion_codigo)==false){
				printHtmlById('info_complentarios','<div class="info info-error">Error <b>Codigo</b>: Debe ingresar <b>solo numeros.</b></div>');
			}
		}
	}else{
		printHtmlById('info_complentarios','');
		form.accion.value=tipo;
		form.submit();
	}
}
function _alphanumeric(inputtxt) {
	var letterNumber = /^[0-9a-zA-Z]+$/;  
	if((inputtxt.value.match(letterNumber))) {  
   		return true;
	} else {
   		return false;   
  	}
}

function _cuenta(inputtxt) {
	var letterNumber = /^[0-9\-]+$/;  
	if((inputtxt.value.match(letterNumber))) {  
   		return true;
	} else {
   		return false;   
  	}
}

function _decimal(inputtxt) {
	var letterNumber = /^[0-9\.]+$/;  
	if((inputtxt.value.match(letterNumber))) {  
   		return true;
	} else {
   		return false;   
  	}
}

function _numeric(inputtxt) {
	var letterNumber = /^[0-9]+$/;  
	if((inputtxt.value.match(letterNumber))) {  
   		return true;
	} else {
   		return false;   
  	}
}

function printHtmlById(id,text) {
	document.getElementById(id).innerHTML = text;
}

function validar(e,tipo) {
	tecla=(document.all)?e.keyCode:e.which;
	if (tecla==13 || tecla==8)
		return true;
	switch(tipo){
		/*letras y numeros, puntos */
		case 1: patron=/[A-Z a-z0-9./:,;.-]/;break;
		/*solo numeros enteros */
		case 2: patron=/[0-9]/;break;
		/*solo numeros dobles o decímales*/
		case 3: patron=/^[0-9\.]+$/;break;
		/*solo letras*/
		case 4: patron=/[A-Z a-z]/;break;
	}
	teclafinal=String.fromCharCode(tecla);
	return patron.test(teclafinal);
}

/* Cuenta de Detraccion */
var valor = '<?php echo $detraccion; ?>';
Detraccion(valor);

function Detraccion(valor) {

	var no_detraccion_cuenta 		= document.getElementById('no_detraccion_cuenta');
	var nu_detraccion_importe 		= document.getElementById('nu_detraccion_importe');
	var nu_detraccion_porcentaje 	= document.getElementById('nu_detraccion_porcentaje');
	var nu_detraccion_codigo 		= document.getElementById('nu_detraccion_codigo');

    if (document.getElementById('yesCheck').checked){
    	document.getElementById('label-no_detraccion_cuenta').style.visibility 		= 'visible';
		document.getElementById('txt-no_detraccion_cuenta').style.visibility 		= 'visible';
		document.getElementById('label-nu_detraccion_importe').style.visibility 	= 'visible';
		document.getElementById('txt-nu_detraccion_importe').style.visibility 		= 'visible';
		document.getElementById('label-nu_detraccion_porcentaje').style.visibility 	= 'visible';
		document.getElementById('txt-nu_detraccion_porcentaje').style.visibility 	= 'visible';
		document.getElementById('label-nu_detraccion_codigo').style.visibility 		= 'visible';
		document.getElementById('txt-nu_detraccion_codigo').style.visibility 		= 'visible';
    }else{
    	no_detraccion_cuenta.value 		= '';
    	nu_detraccion_importe.value 	= '';
    	nu_detraccion_porcentaje.value 	= '';
    	nu_detraccion_codigo.value 		= '';
    	document.getElementById('label-no_detraccion_cuenta').style.visibility 		= 'hidden';
		document.getElementById('txt-no_detraccion_cuenta').style.visibility 		= 'hidden';
		document.getElementById('label-nu_detraccion_importe').style.visibility 	= 'hidden';
		document.getElementById('txt-nu_detraccion_importe').style.visibility 		= 'hidden';
		document.getElementById('label-nu_detraccion_porcentaje').style.visibility 	= 'hidden';
		document.getElementById('txt-nu_detraccion_porcentaje').style.visibility 	= 'hidden';
		document.getElementById('label-nu_detraccion_codigo').style.visibility 		= 'hidden';
		document.getElementById('txt-nu_detraccion_codigo').style.visibility 		= 'hidden';
	}
}
</script>
</footer>
</html>
<?php pg_close();?>
