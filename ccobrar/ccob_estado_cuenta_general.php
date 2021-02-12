<?php
//Este archivo no se esta usando -- Opcion Actual: Estado de Cuenta General
include("../valida_sess.php");
include("../menu_princ.php");
include("../functions.php");
include("store_procedures.php");

require("../clases/funciones.php");
$funcion = new class_funciones;

// crea la clase para controlar errores
// $clase_error = new OpensoftError;

// conectar con la base de datos
$conector_id=$funcion->conectar("","","","","");
$hoy = date("d/m/Y");
if($c_fec_hasta==""){$c_fec_hasta=$hoy;}


if($cod_moneda==""){$cod_moneda="02";}
$tasa_cambio = tipoCambio($cod_moneda,$hoy);
if($c_tasa_cambio==""){$c_tasa_cambio=$tasa_cambio;}	
	
if($_REQUEST['c_todos_clientes']=='N'){
    $Clien = "N";
    $display = "block;";
}else{
    $Clien = "S";
    $display = "hidden;";
}
if ( is_null($almacen) or trim($almacen)==""){
	$almacen="001";
}

switch($accion){

	case "Reporte":
		/*  PRECANCELADO      := SPLIT_PART(OPCIONES,''#'',1);
     		DIAS_VENCIMIENTO  := SPLIT_PART(OPCIONES,''#'',2);
     		GRUPOEMP_CLIENTE  := SPLIT_PART(OPCIONES,''#'',3);
     		TODOS_CLIENTES    := SPLIT_PART(OPCIONES,''#'',4);
     		COD_CLIENTE       := SPLIT_PART(OPCIONES,''#'',5);
     		SERIE             := SPLIT_PART(OPCIONES,''#'',6);
	*/
	if(count($c_documentos)>0){
		pg_exec("DELETE FROM acosa_temp.ccob_documentos_reporte_sunat_estado_cuenta_general ");
	}else{
		pg_exec("INSERT INTO acosa_temp.ccob_documentos_reporte_sunat_estado_cuenta_general 
			SELECT cod_docu, 
			       desc_docu
			FROM (
			SELECT substring(trim(tab_elemento) FOR 2 FROM length(trim(tab_elemento))-1) AS cod_docu,trim(tab_descripcion) as desc_docu
                        FROM int_tabla_general WHERE tab_tabla ='08') AS doc ");
	}
		for($i=0;$i<count($c_documentos);$i++){
			$temp_doc = $c_documentos[$i];
			pg_exec("INSERT INTO acosa_temp.ccob_documentos_reporte_sunat_estado_cuenta_general 
			SELECT cod_docu, 
			       desc_docu
			FROM (
			SELECT substring(trim(tab_elemento) FOR 2 FROM length(trim(tab_elemento))-1) AS cod_docu,trim(tab_descripcion) as desc_docu
                        FROM int_tabla_general WHERE tab_tabla ='08') AS doc
			WHERE cod_docu='$temp_doc'");
		}	
			$rsf = pg_exec("SELECT * FROM acosa_temp.ccob_documentos_reporte_sunat_estado_cuenta_general 
			                ORDER BY cod_documento");
			for($i=0;$i<pg_numrows($rsf);$i++){
				$A = pg_fetch_array($rsf,$i);
				$comp = $i+1<pg_numrows($rsf)? ",":"";
				$docs_seleccionados=$docs_seleccionados.$A["cod_documento"].$comp;
			}
			
		if($c_serie==""){$c_serie="TODOS";}
	
		$opciones = $opciones.trim($c_precancelado)."#";
		$opciones = $opciones.trim($c_dias_vcmt)."#";
		$opciones = $opciones.trim($c_grupoemp_cliente)."#";
		$opciones = $opciones.trim($c_todos_clientes)."#";
		$opciones = $opciones.trim($c_cod_cliente)."#";
		$opciones = $opciones.trim($c_serie);

		$rs = ESTADO_CUENTA_GENERAL($c_fec_desde,$c_fec_hasta,$opciones,$c_tasa_cambio,$c_categoria);
		
		break;

}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<script language="JavaScript">
function mandarDatos(form,opcion){
		form.accion.value=opcion;
		form.submit();
	}
	
function AbrirPopupReporPdf(datos) {
    miPopup = window.open("ccob_estado_cuenta_general_reporte.php?datos="+datos,"miwin","width=500,height=400,scrollbars=yes") 
    //miPopup.focus() 
}

function getObj(name, nest) {
if (document.getElementById){
return document.getElementById(name).style;
}else if (document.all){
return document.all[name].style;
}else if (document.layers){
if (nest != ''){
return eval('document.'+nest+'.document.layers["'+name+'"]');
}
}else{
return document.layers[name];
}
}

//Hide/show layers functions
function showLayer(layerName, nest){
var x = getObj(layerName, nest);
x.visibility = "visible";
}

function hideLayer(layerName, nest){
var x = getObj(layerName, nest);
x.visibility = "hidden";
}

function mostrarFila(fila){
showLayer(fila);
}

function ocultarFila(fila){
hideLayer(fila);
}


function mostrarCliente(opcion,fila_cod_cliente){
	if(opcion=="N"){
		mostrarFila(fila_cod_cliente);
	}
	if(opcion=="S"){
		ocultarFila(fila_cod_cliente);
	}
}
</script>
<title>ESTADO DE CUENTA GENERAL</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="/sistemaweb/css/sistemaweb.css" rel="stylesheet" type="text/css">
</head>

<body>
	<script language="JavaScript" src="/sistemaweb/js/calendario.js"></script>
  <script language="JavaScript" src="/sistemaweb/js/overlib_mini.js"></script>
<form name="form1" method="post" action="">
  <table width="100%" border="1" align="center">
    <tr> 
      <td width="23%">PRECANCELADO (S/N)</td>
      <td width="32%"><select name="c_precancelado">
      	  <OPTION value='N' <?php if($c_precancelado=="N"){echo "selected";} ?>>NO</OPTION>
          <OPTION value='S' <?php if($c_precancelado=="S"){echo "selected";} ?>>SI</OPTION>
        </select></td>
      <td width="34%">&nbsp;</td>
    </tr>
   <tr> 
      <td>HASTA LA FECHA</td>
      <td valign="top"><input type="text" name="c_fec_hasta" size="12" value="<?php echo $c_fec_hasta;?>"> <a href="javascript:show_calendar('form1.c_fec_hasta');"><img src="/sistemaweb/images/showcalendar.gif" border=0></a>&nbsp;&nbsp;<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div></td>
      <td>DOCUMENTOS</td>
    </tr>
    <tr> 
      <td>DIAS DE VENCIMIENTO(S/N)</td>
      <td><select name="c_dias_vcmt">
          <option value='N' <?php if($c_dias_vcmt=="N"){echo "selected";} ?>>NO</option>
          <option value='S' <?php if($c_dias_vcmt=="S"){echo "selected";} ?>>SI</option>
        </select></td>
      <td colspan="1" rowspan="3"><select name="c_documentos[]" multiple size="7">
          <?php $rsf = combo("documentos");
		for($i=0;$i<pg_numrows($rsf);$i++){
			$A = pg_fetch_array($rsf,$i);
			print "<option value='$A[0]'>$A[0] - $A[1]</option>";
		}
		?>
        </select> </td>
    </tr>
    <tr> 
      <td>TASA DE CAMBIO</td>
      <td><input type="text" name="c_tasa_cambio" size="12" value="<?php echo $c_tasa_cambio;?>"></td>
    </tr>
    <tr> 
      <td>CATEGORIA</td>
      <td><select name="c_categoria">
          <option value='A' <?php if($c_categoria=="A"){echo "selected";} ?>>ACTIVOS</option>
          <option value='J' <?php if($c_categoria=="J"){echo "selected";} ?>>EN JUICIO</option>
          <option value='I' <?php if($c_categoria=="I"){echo "selected";} ?>>INACTIVOS</option>
          <option value='T' <?php if($c_categoria=="J"){echo "selected";} ?>>TODOS</option>
        </select></td>
    </tr>
    <tr> 
      <td>POR GRUPO EMPRESARIAL <br>
        O CLIENTE</td>
      <td><select name="c_grupoemp_cliente">
          <option value='GRUPOEMP' <?php if($c_grupoemp_cliente=="GRUPOEMP"){echo "selected";} ?>>Grupo Empresarial</option>
          <option value='CLIENTE' <?php if($c_grupoemp_cliente=="CLIENTE"){echo "selected";} ?>>Cliente</option>
        </select></td>
      <td colspan="1">&nbsp;
      </td>
    </tr>
    <tr> 
      <td>TODOS LOS CLIENTES </td>
      <td><select name="c_todos_clientes" onChange="javascript:mostrarCliente(this.value,'fila_cod_cliente');">
          <option value='S' <?php if($c_todos_clientes=="S"){echo "selected";} ?>>SI</option>
          <option value='N' <?php if($c_todos_clientes=="N"){echo "selected";} ?>>NO</option>
        </select></td>
      <td><div id="fila_cod_cliente" style="display: <?php echo $display?>">&nbsp;CLIENTE <input type="text" name="c_cod_cliente" value="<?php echo @$_REQUEST['c_cod_cliente']?>"></div></td>
    </tr>
    <tr> 
      <td>SERIE</td>
      <td><input type="text" name="c_serie" value="<?php echo $c_serie;?>"></td>
     <!-- Este checkbox permite filtrar los vales-->
      <td colspan="1">Mostrar vales <input type="checkbox" name="chk_vales" /></td>
    </tr>
    <tr> 
      <td>&nbsp;</td>
      <td><div align="center"> 
          <input type="button" name="btn_reporte" value="Reporte" onClick="javascript:mandarDatos(form1,'Reporte');">&nbsp;&nbsp;&nbsp;
          <input type="hidden" name="accion" value="">
        </div></td>
      <td>&nbsp;</td>
    </tr>
  </table>
</form>
<table border="1" align="center">
  <tr style="font-weight:bold;">
    <td>CLIENTE</td>
    <td>DOCUMENTO</td>
    <td>N. LIQUIDACION</td>
    <td>F. EMISION</td>
    <td>MON</td>
    <td>IMPORTE</td>
    <td>F.VCMTO</td>
    <td>DOLARES</td>
    <td>SOLES</td>
  </tr>
  <tr style="font-weight:bold;">
    <td>&nbsp;</td>
    <td>Nro. Vale</td>
    <td>Nro. Liquidacion</td>
    <td>Fecha</td>
    <td>Moneda</td>
    <td>Importe</td>
    <td colspan="3">&nbsp;</td>
  </tr>
  <?php $cli_desc = ""; 
  $total_xcliente = 0;
  
  $DatosArrayFinal=array();
  $DatosArray=array();

  for($i=0;$i<pg_numrows($rs);$i++)
  {

  	$A = pg_fetch_array($rs,$i);
	//SI NO SE HA FILTRADO POR VALES NO SE MUESTRA
	if($A['ch_tipdocumento'] == '' && !isset($_REQUEST['chk_vales'])) {
			
	} else {
	
			//print_r($A);
		  	$factor = $_REQUEST['c_tasa_cambio'];
		  	if ($_REQUEST['c_precancelado']=='S'){
		  		if ($A['moneda']=='S/.'){
		  			$total_xcliente += (($A['ch_tipdocumento']=='10' or $A['ch_tipdocumento']=='35' or $A['ch_tipdocumento']=='22' or $A['ch_tipdocumento']=='11')?($A["saldo"]-$A["nu_importe_precancelado"])*1:($A["saldo"]-$A["nu_importe_precancelado"])*(-1));
					$totales += (($A['ch_tipdocumento']=='10' or $A['ch_tipdocumento']=='35' or $A['ch_tipdocumento']=='22' or $A['ch_tipdocumento']=='11')?($A["saldo"]-$A["nu_importe_precancelado"])*1:($A["saldo"]-$A["nu_importe_precancelado"])*(-1));
		  		}else{
		  			$total_xcliente += (($A['ch_tipdocumento']=='10' or $A['ch_tipdocumento']=='35' or $A['ch_tipdocumento']=='22' or $A['ch_tipdocumento']=='11')?($A["saldo"]-$A["nu_importe_precancelado"]*$factor)*1:($A["saldo"]-$A["nu_importe_precancelado"]*$factor)*(-1));
					$totales += (($A['ch_tipdocumento']=='10' or $A['ch_tipdocumento']=='35' or $A['ch_tipdocumento']=='22' or $A['ch_tipdocumento']=='11')?($A["saldo"]-$A["nu_importe_precancelado"]*$factor)*1:($A["saldo"]-$A["nu_importe_precancelado"]*$factor)*(-1));
					$total_xclientedolares += (($A['ch_tipdocumento']=='10' or $A['ch_tipdocumento']=='35' or $A['ch_tipdocumento']=='22' or $A['ch_tipdocumento']=='11')?round(($A["saldo"]-$A["nu_importe_precancelado"]*$factor)*1/$factor,2):round(($A["saldo"]-$A["nu_importe_precancelado"]*$factor)*(-1)/$factor,2));
					$totalesdolares += (($A['ch_tipdocumento']=='10' or $A['ch_tipdocumento']=='35' or $A['ch_tipdocumento']=='22' or $A['ch_tipdocumento']=='11')?round(($A["saldo"]-$A["nu_importe_precancelado"]*$factor)*1/$factor,2):round(($A["saldo"]-$A["nu_importe_precancelado"]*$factor)*(-1)/$factor,2));
		  		}
		  	}else{
		  		if ($A['moneda']=='S/.'){
		  			$total_xcliente += (($A['ch_tipdocumento']=='10' or $A['ch_tipdocumento']=='35' or $A['ch_tipdocumento']=='22' or $A['ch_tipdocumento']=='11')?($A["saldo"])*1:($A["saldo"])*(-1));
					$totales += (($A['ch_tipdocumento']=='10' or $A['ch_tipdocumento']=='35' or $A['ch_tipdocumento']=='22' or $A['ch_tipdocumento']=='11')?($A["saldo"])*1:($A["saldo"])*(-1));
		  		}else{
		  			$total_xcliente += (($A['ch_tipdocumento']=='10' or $A['ch_tipdocumento']=='35' or $A['ch_tipdocumento']=='22' or $A['ch_tipdocumento']=='11')?($A["saldo"])*1:($A["saldo"])*(-1));
					$totales += (($A['ch_tipdocumento']=='10' or $A['ch_tipdocumento']=='35' or $A['ch_tipdocumento']=='22' or $A['ch_tipdocumento']=='11')?($A["saldo"])*1:($A["saldo"])*(-1));
					$total_xclientedolares += (($A['ch_tipdocumento']=='10' or $A['ch_tipdocumento']=='35' or $A['ch_tipdocumento']=='22' or $A['ch_tipdocumento']=='11')?round(($A["saldo"])*1/$factor,2):round(($A["saldo"])*(1)/$factor,2));
					$totalesdolares += (($A['ch_tipdocumento']=='10' or $A['ch_tipdocumento']=='35' or $A['ch_tipdocumento']=='22' or $A['ch_tipdocumento']=='11')?round(($A["saldo"])*1/$factor,2):round(($A["saldo"])*(1)/$factor,2));
		  		}
		  	}
		  	$DatosArray['CODIGO'] 			   	 = $A["cliente"];
		        $DatosArray['CREDITO'] 		  	         = $A["credito"];
			$DatosArray['TIPO']				 = $A["ch_tipdocumento"];
			$DatosArray['DOCUMENTOS']      		         = $A["documento"];
			$DatosArray['FECHA EMISION']  			 = $A["fecha_emision"];
			$DatosArray['MONEDA']         			 = $A["moneda"];
			$DatosArray['IMPORTE']      			 = $A["importe"];
			$DatosArray['FECHA VENC. Y DIAS VENCIDOS'] 	 = $A["fecha_vcmt"]." ".$A["num_dias_vencidos"];
			$DatosArray['PRECANCELADO']			 = $A["dt_fecha_precancelado"];
			$DatosArray['SUCURSAL']				 = $A["ch_sucursal_precancelado"];
			$DatosArray['IMPORTE']      			 = $A["importe"];
			$DatosArray['SALDO DOLARES']  			 = money_format("%.2n",(($A['moneda']=='US$'?$A['saldo']/$factor:'')));
			$DatosArray['SALDO SOLES']    			 = round($A["saldo"],2);
			$DatosArrayFinal[][$A["cliente"]] = $DatosArray;
	
	  ?>
	  <tr> 
	    <td style="font-weight:bold;"> 
	      <?php
	      if($cli_desc!=$A["cliente"])
	      { 
		$cli_desc = $A["cliente"]; 
		echo $A["cliente"];
	
	      }
	      ?>
	    </td>
	    <td><?php echo $A["documento"];?></td>
	    <td><?php echo $A["ch_liquidacion"];?></td>
	    <td><?php echo $A["fecha_emision"];?></td>
	    <td><?php echo $A["moneda"];?></td>
	    <td align="right"><?php echo $A["importe"];?></td>
	    <td><?php echo $A["fecha_vcmt"]." ".$A["num_dias_vencidos"];?></td>
	    <td align="right"><?php echo ($A['moneda']=='US$'?$A['saldo']/$factor:'')?></td>
	    <td align="right"><?php echo round($A["saldo"],2);?></td>
	  </tr>
	  <?php /*
	    $sql = "SELECT ch_documento, ch_liquidacion, to_char(dt_fecha, 'dd/mm/yyyy') as dt_fecha, nu_importe from val_ta_cabecera where ch_liquidacion='" . $A["ch_liquidacion"] . "' order by dt_fecha, ch_documento";
	    $rs2 = pg_exec($sql);
	    for ($i2 = 0; $i2 < pg_numrows($rs2); $i2++)
	    {
		$val = pg_fetch_array($rs2, $i2);*/
		?>
	<!--	<tr>
		    <td style="font-weight:bold;"><?php if ($i2 == 0) echo "Detalle de vales:</td>"; ?></td>
		    <td>VALE: <?php echo $val["ch_documento"];?></td>
		    <td><?php echo $val["ch_liquidacion"];?></td>
		    <td><?php echo $val["dt_fecha"];?></td>
		    <td>S/.</td>
		    <td align="right"><?php echo $val["nu_importe"];?></td>
		</tr> -->
		<?php
	    /*}
	    if (pg_numrows($rs2) > 0) echo "<tr><td colspan=\"9\">&nbsp;</td></tr>";*/
	  ?>
	    <?php
	    if($A["cliente"]!=pg_result($rs,$i+1,"cliente")){
	    echo "<!--CLIENTE SUB TOTAL: ".$A['cliente']."-->\n";
	    ?>
	    <tr style="font-weight:bold;"> 
		<td colspan="4">&nbsp;</td>
		<td colspan="2">*SUB-TOTAL*</td>
		<td align="right">
		    <?php
		    $total_xclientedolares = ($total_xclientedolares==''?'0':$total_xclientedolares);
		    echo $total_xclientedolares;
		    $DatosArrayFinal[$A["cliente"]]['*SUB-TOTAL*']['DOLARES'] = $total_xclientedolares;
		    $total_xclientedolares = 0;
		    ?>
		</td>
		<td align="right">
		    <?php
		    $total_xcliente = ($total_xcliente==''?'0':$total_xcliente);
		    echo $total_xcliente;
		    $DatosArrayFinal[$A["cliente"]]['*SUB-TOTAL*']['SOLES'] = $total_xcliente;
		    $total_xcliente = 0;
		    ?>
		</td>
	    </tr>
	    <tr><td colspan="7" height="20"></td></tr>
	  <?php
	    }else{
	     
	    }//Fin del IF
    
}   //FIN DEL FILTRO POR VALES
  } //fin del for
  if($total_xcliente>0)
  { ?>
      <tr style="font-weight:bold;"> 
	<td colspan="4">&nbsp;</td>
	<td colspan="2">*SUB-TOTAL*</td>
	<td align="right">
	    <?php
	    echo $total_xclientedolares;
	    $DatosArrayFinal[$A["cliente"]]['*SUB-TOTAL*']['DOLARES'] = $total_xclientedolares;
	    $total_xclientedolares = 0;
	    ?>
	</td>
	<td align="right">
	    <?php
	    echo $total_xcliente;
	    $DatosArrayFinal[$A["cliente"]]['*SUB-TOTAL*']['SOLES'] = $total_xcliente;
	    $total_xcliente = 0;
	    ?>
	</td>
    </tr>
    
  <?php } ?>
   <tr style="font-weight:bold;"> 
	<td colspan="4">&nbsp;</td>
	<td colspan="2">*TOTAL GENERAL*</td>
	<td align="right">
	    <?php
	    echo $totalesdolares;
	    $DatosArrayFinal['TOTALES']['*TOTAL GENERAL*']['DOLARES'] = $totalesdolares;
	    ?>
	</td>
	<td align="right">
	    <?php
	    echo $totales;
	    $DatosArrayFinal['TOTALES']['*TOTAL GENERAL*']['SOLES'] = $totales;
	    ?>
	</td>
  </tr>
  <tr style="font-weight:bold;"> 
	<td colspan="4">&nbsp;</td>
	<td colspan="2">*DOLARES*</td>
	<td align="right">
	    <?php
	    echo $totalesdolares;
	    $DatosArrayFinal['TOTALES']['*DOLARES*']['DOLARES'] = $totalesdolares;
	    ?>
	</td>
	<td align="right">
	    <?php
	    echo round($totalesdolares*$factor,2);
	    $DatosArrayFinal['TOTALES']['*DOLARES*']['SOLES'] = round($totalesdolares*$factor,2);
	    ?>
	</td>
  </tr>
  <tr style="font-weight:bold;"> 
	<td colspan="4">&nbsp;</td>
	<td colspan="2">*SOLES*</td>
	<td align="right">
	    <?php
	    echo '';
	    $DatosArrayFinal['TOTALES']['*SOLES*']['DOLARES'] = '';
	    ?>
	</td>
	<td align="right">
	    <?php
	    echo $totales-round($totalesdolares*$factor,2);
	    $DatosArrayFinal['TOTALES']['*SOLES*']['SOLES'] = $totales-round($totalesdolares*$factor,2);
	    ?>
	</td>
  </tr>
  
      <tr style="font-weight:bold;"> 
	<td colspan="4">&nbsp;</td>
	<td colspan="2">*TOTAL*</td>
	<td align="right">
	    <?php
	    echo $totalesdolares;
	    $DatosArrayFinal['TOTALES']['*TOTAL*']['DOLARES'] = $totalesdolares;
	    ?>
	</td>
	<td align="right">
	    <?php
	    echo $totales;
	    $DatosArrayFinal['TOTALES']['*TOTAL*']['SOLES'] = $totales;
	    ?>
	</td>
    </tr>

  <?php
  //echo "<!--".$DatosArray." -->";
  //
  ?>
  <div style="display:none;">
  <?php
  if($DatosArrayFinal && isset($_REQUEST['c_precancelado']))
  {
    //$Fechas['DESDE'] = $_REQUEST['c_fec_desde'];
    $Fechas['HASTA'] = $_REQUEST['c_fec_hasta'];
    include_once('ccob_estado_cuenta_general_reporte.php');
    $reporte = EstCuentaGenReporteTemplate::ReportePDF($DatosArrayFinal,$Fechas);
    echo "$reporte";
  }
  ?>
  </div>
</table>
<script>
	mostrarCliente('<?php echo $Clien; ?>','fila_cod_cliente');
</script>
</body>
</html>
<?php pg_close(); ?>

