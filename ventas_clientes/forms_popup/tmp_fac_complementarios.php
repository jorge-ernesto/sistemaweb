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

//$COMP = $_SESSION["ARR_COMP"];
$arrCodigo = explode(' ',$_REQUEST['registroid']);
//echo $cod_cliente."-".$accion."-";
switch($accion){

	case "Modificar":
		$COMP["comp_dir"]=$_REQUEST['c_comp_dir'];
		$COMP["razon_social"]=$_REQUEST['c_razon_social'];
		$COMP["direccion"]=$_REQUEST['c_direccion'];
		$COMP["ruc"]=$_REQUEST['c_ruc'];
		$COMP["obs1"]=$_REQUEST['c_obs1'];		
		$COMP["obs2"]=$_REQUEST['c_obs2'];		
		$COMP["obs3"]=$_REQUEST['c_obs3'];		
		$query = "select count(*) as total from TMP_FAC_TA_FACTURA_COMPLEMENTO 
		where trim(ch_fac_tipodocumento)||trim(ch_fac_seriedocumento)||trim(ch_fac_numerodocumento)||trim(cli_codigo)='".$arrCodigo[0].$arrCodigo[1].$arrCodigo[2].$arrCodigo[3]."'";
		$rs=pg_exec($query);
		$auxi = pg_fetch_array($rs);
		if ($auxi['total']>0){
			$query = "UPDATE TMP_FAC_TA_FACTURA_COMPLEMENTO SET NU_FAC_COMPLEMENTO_DIRECCION='".trim($COMP["comp_dir"])."'".
					", CH_FAC_NOMBRECLIE='".trim($COMP["razon_social"])."', NU_FAC_DIRECCION='".trim($COMP["direccion"])."', CH_FAC_RUC='".trim($COMP["ruc"])."'
					, CH_FAC_OBSERVACION1='".trim($COMP["obs1"])."', CH_FAC_OBSERVACION2='".trim($COMP["obs2"])."', CH_FAC_OBSERVACION3='".trim($COMP["obs3"])."'
					WHERE trim(ch_fac_tipodocumento)||trim(ch_fac_seriedocumento)||trim(ch_fac_numerodocumento)||trim(cli_codigo)='".$arrCodigo[0].$arrCodigo[1].$arrCodigo[2].$arrCodigo[3]."'";	
		}else{
			$query = "INSERT INTO TMP_FAC_TA_FACTURA_COMPLEMENTO (CH_FAC_TIPODOCUMENTO, 
			CH_FAC_SERIEDOCUMENTO, CH_FAC_NUMERODOCUMENTO, CLI_CODIGO, DT_FAC_FECHA, 
			CH_FAC_OBSERVACION1, CH_FAC_OBSERVACION2, CH_FAC_OBSERVACION3, CH_FAC_RUC, NU_FAC_DIRECCION, NU_FAC_COMPLEMENTO_DIRECCION,
			DT_FECHACTUALIZACION, CH_FAC_NOMBRECLIE) SELECT CH_FAC_TIPODOCUMENTO, CH_FAC_SERIEDOCUMENTO, 
			CH_FAC_NUMERODOCUMENTO, CLI_CODIGO, DT_FAC_FECHA, '".trim($COMP["obs1"])."','".trim($COMP["obs2"])."','".trim($COMP["obs3"])
			."','".trim($COMP["ruc"])."','".trim($COMP["direccion"])."','".trim($COMP["comp_dir"])."',NOW(),'".trim($COMP["razon_social"])."' 
			FROM FAC_TA_FACTURA_CABECERA 
			WHERE trim(ch_fac_tipodocumento)||trim(ch_fac_seriedocumento)||trim(ch_fac_numerodocumento)||trim(cli_codigo)='".$arrCodigo[0].$arrCodigo[1].$arrCodigo[2].$arrCodigo[3]."'";	
		}
		pg_exec($query);
		print "<script>window.close();</script>";
		break;
	
    case "Completar":
    	$query = "select * from TMP_FAC_TA_FACTURA_COMPLEMENTO 
    	where trim(ch_fac_tipodocumento)||trim(ch_fac_seriedocumento)||trim(ch_fac_numerodocumento)||trim(cli_codigo)='".$arrCodigo[0].$arrCodigo[1].$arrCodigo[2].$arrCodigo[3]."'";
    	$rs=pg_exec($query);
     if(pg_numrows($rs)>0){
     	$query="select ch_fac_nombreclie as cli_razsocial, nu_fac_direccion as cli_direccion, 
     	ch_fac_ruc as cli_ruc, nu_fac_complemento_direccion as cli_comp_direccion, ch_fac_observacion1, ch_fac_observacion2, ch_fac_observacion3 from TMP_FAC_TA_FACTURA_COMPLEMENTO
     	where trim(ch_fac_tipodocumento)||trim(ch_fac_seriedocumento)||trim(ch_fac_numerodocumento)||trim(cli_codigo)='".$arrCodigo[0].$arrCodigo[1].$arrCodigo[2].$arrCodigo[3]."'";
     	//$cod_cliente = $arrCodigo[3];
     }else{
     $query = "SELECT ".
			"trim(cli_razsocial) AS cli_razsocial, ".
			"cli_direccion, ".
			"cli_ruc, cli_comp_direccion ".
		"FROM int_clientes ".
		"WHERE cli_codigo='$cod_cliente' ";
		$COMP = $_SESSION["ARR_COMP"];
     }
     //print_r($query);
     
    if($_REQUEST['registroid'] == ""){
    //echo "Entro \n";
		if($cod_cliente!=""){
		    
		    $rs = pg_exec($query);
		    if(pg_numrows($rs)>0){	
			$A = pg_fetch_array($rs,0);
			if ($COMP['comp_dir']=='')
				$COMP["comp_dir"] = $A["cli_comp_direccion"]; 
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
		    //print_r('ENTRO 1');
		}
    }elseif($_REQUEST['registroid'] != "" ){
		if($cod_cliente!=""){
		   // print_r('ENTRO 2');
		    $rs = pg_exec($query);
		    if(pg_numrows($rs)>0){	
			$A = pg_fetch_array($rs,0);
			if ($COMP['comp_dir']=='')
				$COMP["comp_dir"] = $A["cli_comp_direccion"]; 
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
        $_SESSION["ARR_COMP"] = null;
	    $COMP = null;
	    unset($ARR_COMP);
	    unset($COMP);
	    print "<script>window.close();</script>";
	    break;
    
    case "Terminar":
	    $COMP["razon_social"] = $c_razon_social; 
		$COMP["direccion"] = $c_direccion;
		$COMP["ruc"] = $c_ruc;
		$COMP["comp_dir"] = $c_comp_dir;
		$COMP["obs1"] = $c_obs1;
		$COMP["obs2"] = $c_obs2;
		$COMP["obs3"] = $c_obs3;
		$_SESSION["ARR_COMP"] = $COMP;
	   	print "<script>window.close();</script>";
		break;
}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Datos Complementarios</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../../sistemaweb.css" rel="stylesheet" type="text/css">
<script>
function enviarDatos(form,tipo){
		form.accion.value=tipo;
		form.submit();
}
</script>
</head>

<body>
<form name="form1" method="post" action="">
  Complementos y Observaciones 
  <table width="91%" border="0">
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
      <td>COMPLEMENTO DIR. :</td>
      <td><input type="text" name="c_comp_dir" value="<?php echo  $COMP["comp_dir"];?>" size="20" maxlength="20"></td>
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td>R.U.C. :</td>
      <td><input type="text" name="c_ruc" value="<?php echo  $COMP["ruc"];?>" size="15" maxlength="15">
      </td>
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td>OBSERVACIONES (1) :</td>
      <td><input type="text" name="c_obs1" value="<?php echo  $COMP["obs1"];?>" size="40" maxlength="40"></td>
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td height="24">OBSERVACIONES (2) :</td>
      <td><input type="text" name="c_obs2" value="<?php echo  $COMP["obs2"];?>" size="40" maxlength="40"></td>
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td>OBSERVACIONES (3) :</td>
      <td><input type="text" name="c_obs3" value="<?php echo  $COMP["obs3"];?>" size="40" maxlength="40"></td>
      <td>&nbsp;</td>
    </tr>
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
</html>
<?php pg_close();?>