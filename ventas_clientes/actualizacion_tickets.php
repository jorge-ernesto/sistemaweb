<?php
include "../menu_princ.php";
include("../functions.php");
include("/sistemaweb/utils/funcion-texto.php");
require("../clases/funciones.php");

$funcion 	= new class_funciones;
$clase_error 	= new OpensoftError;
$clase_error->_error();
$conector_id	= $funcion->conectar("","","","","");

if($boton == 'Buscar') {
	if($modo == 'actual') {
		$sql_actual = "	SELECT
					trim(documento), 
					trans, 
					td, 
					caja, 
					turno, 
					dia, 
					trim(codigo), 
					to_char(trans,'999999999')||dia
				FROM
					pos_transtmp 
				WHERE
					td='".$td."'";

		if (trim($trans) != "")			
				$sql_actual .= " AND trans=$trans " ;
		$sql_actual .= "  ORDER BY dia " ;

                $rs_ticket    = pg_exec($sql_actual);
		$n_rs_tickets = pg_numrows($rs_ticket);

	} else if($modo == 'historico') {

                $f_desde = $diad2."-".$mes2."-".$periodo2;
                $f_hasta = $diaa2."-".$mes2."-".$periodo2;
                $n_tabla = "pos_trans".$periodo2.$mes2;

		$sql_hist = "	SELECT 
					trim(documento), 
					trans, 
					td, 
					caja, 
					turno, 
					dia, 
					trim(codigo), 
					trans||trim(codigo) 
				FROM
					".$n_tabla." 
				WHERE
					documento is not null 
					AND td='".$td."' 
					AND (to_char(dia, 'yyyy-mm-dd') 
					BETWEEN '".$funcion->date_format($f_desde, 'YYYY-MM-DD')."' AND '".$funcion->date_format($f_hasta,'YYYY-MM-DD')."')";

		if (trim($trans) != "")			
			$sql_hist .= " AND trans=$trans " ;
		$sql_hist .= " ORDER BY dia " ;

		$rs_ticket = pg_exec($sql_hist);
		$n_rs_tickets = pg_numrows($rs_ticket);
	}
}

switch($bt) {
	case Modificar: 
		if($modo == 'actual') {
			$sql_actual = "SELECT
						trim(documento), 
						trans, 
						td, 
						caja, 
						turno, 
						dia, 
						trim(codigo), 
						trans||trim(codigo) 
					FROM
						pos_transtmp 
					WHERE
						1=1 ";
			if (trim($trans) != "")			
				$sql_actual .= " AND trans=$trans " ;
			$sql_actual .= "  ORDER BY dia " ;

                	$rs_ticket   = pg_exec($sql_actual);
	                $n_rs_ticket = pg_numrows($rs_ticket);
			$conta_mod2  = 0;

	                while($conta_mod2 < $n_rs_ticket) {
				$am0 = pg_result($rs_ticket,$conta_mod2,7);
				$idm[$am0] = $am0;

				if($idm[$am0] == $idp[$am0]) {
		                        $sqlupd = "UPDATE pos_transtmp SET documento='".$m_codtrab[$am0]."' WHERE trans||trim(codigo)='".$idm[$am0]."'";
	                                $xsqlupd = pg_exec($sqlupd);
        	                }
	               	        $conta_mod2++;
			}		
		}
	
		if($modo == 'historico') {
			$f_desde = $diad2."-".$mes2."-".$periodo2;
			$f_hasta = $diaa2."-".$mes2."-".$periodo2;
	                $n_tabla = "pos_trans".$periodo2.$mes2;

               		$sql_hist = "	SELECT
						trim(documento), 
						trans, 
						td, 
						caja, 
						turno,
						dia, 
						trim(codigo), 
						trans||trim(codigo) 
					FROM
						".$n_tabla." 
					WHERE
						documento is not null 
						AND (to_char(dia, 'yyyy-mm-dd') 
						BETWEEN '".$funcion->date_format($f_desde, 'YYYY-MM-DD')."' AND '".$funcion->date_format($f_hasta,'YYYY-MM-DD')."') ";

			if (trim($trans) != "")			
				$sql_hist .= " AND trans=$trans " ;
			$sql_hist .= " ORDER BY dia " ;

	                $rs_ticket   = pg_exec($sql_hist);
                	$n_rs_ticket = pg_numrows($rs_ticket);
			$conta_mod1 = 0;

	                while($conta_mod1 < $n_rs_ticket) {
				$am0 = pg_result($rs_ticket, $conta_mod1, 7);
				$idm[$am0] = $am0;

				if($idm[$am0] == $idp[$am0]) {
		                        $sqlupd = "UPDATE ".$n_tabla." SET documento='".$m_codtrab[$am0]."' WHERE trans||trim(codigo)='".$idm[$am0]."'";
	                                $xsqlupd = pg_exec($sqlupd);
        	                }
	               	        $conta_mod1++;
			}		
		}
		break;
}

if($cod_almacen == "")
	$cod_almacen = $almacen;
if($diad2 == "")
 	$diad2 = "01";
if($diaa2=="")
	$diaa2 = "30";
if($mesa =="")
	$mes2 = date("m");

$rs1 = pg_exec("SELECT ch_almacen,ch_nombre_almacen FROM inv_ta_almacenes WHERE ch_clase_almacen='1' ORDER BY ch_nombre_almacen ");
$rs2 = pg_exec("SELECT trim(ch_almacen),ch_nombre_almacen FROM inv_ta_almacenes WHERE ch_clase_almacen='1' AND trim(ch_almacen)=trim('$cod_almacen') ORDER BY ch_nombre_almacen");
$A = pg_fetch_array($rs2,0);
$sucursal_val = $A[0];
$sucursal_dis = $A[1];
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<script>

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

function mostrarHis() {
	showLayer('his1');
	showLayer('his2');
	showLayer('his3');
	showLayer('his4');
	showLayer('his5');
	showLayer('his6');
}

function ocultarHis() {
	hideLayer('his1');
	hideLayer('his2');
	hideLayer('his3');
	hideLayer('his4');
	hideLayer('his5');
	hideLayer('his6');
}
</script>

<script>
function mostrarDespacho(num_trans,fecha_trans) {
	var url = '/sistemaweb/ventas_clientes/reimpresiones.php?nro_trans='+num_trans+'&fecha='+fecha_trans;
	window.open(url,'reimpresion','width=600,height=800,scrollbars=yes,menubar=no,left=100,top=20');
}
</script>
<title>Actualizacion de Tickets</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
ACTUALIZACION DE TICKETS
<form name="form1" method="post" action="">
<div align="center">
<table width="577" border="0" cellpadding="0" cellspacing="0">
	<tr valign="bottom"> 
        	<td colspan="6"><div align="left"> 
            	<p>Almacen: 
              	<select name="cod_almacen"><div align="center">
                <?php 
		if($cod_almacen != "") { 
			print "<option value='$sucursal_val' selected>$sucursal_val -- $sucursal_dis</option>"; 
		}
		for($i = 0; $i < pg_numrows($rs1); $i++) {
			$B = pg_fetch_row($rs1,$i);
			print "<option value='$B[0]' >$B[0] -- $B[1]</option>";
		}
		?>
              	</select>
           	</p>
        	</div></td>
      	</tr>
      	<tr> 
		<td width="40">Tip. Doc.:</td>
		<td width="129"><input name="td" type="radio" value="B">Boleta</td>
		<td width="65">Historico</td>
		<td width="133"><input type="radio" name="modo" value="historico" onClick="javascript:mostrarHis();" <?php if($modo=="historico"){ echo "checked"; }?>></td>
		<td width="64" id="his1">Periodo :</td>
	        <td width="92" id="his2"><input type="text" name="periodo2" value="<?php echo $periodo2; ?>" size="8" maxlength="4"></td>
      	</tr>
      	<tr> 
		<td></td>
		<td><input name="td" type="radio" value="F">Factura</d>
		<td>Actual </td>
		<td><input type="radio" name="modo" value="actual"  onClick="javascript:ocultarHis();" <?php if($modo=="actual"){echo "checked";}?> ></td>
		<td id="his3">Mes :</td>
		<td id="his4"><input type="text" name="mes2" size="4" maxlength="2" value="<?php echo $mes2; ?>"></td>
	</tr>
      	<tr> 
        	<td></td>
        	<td><input name="td" type="radio" value="N">Nota Despacho</td>
        	<td>Nro. Ticket</td>
        	<td><input type="text" name="trans" size="20" maxlength="13" value="<?php echo $trans; ?>"></td>
        	<td id="his5"> <div align="left">Desde el:</div></td>
        	<td id="his6"><input type="text" name="diad2" size="4" maxlength="2" value="<?php echo $diad2; ?>"> al 
        	<input type="text" name="diaa2" size="4" maxlength="2" value="<?php echo $diaa2 ;?>"> 
        	</td>
      	</tr>
      	<tr> 
        	<td>&nbsp;</td>
        	<td>&nbsp;</td>
        	<td>&nbsp;</td>
        	<td>&nbsp;</td>
        	<td id="his5">&nbsp;</td>
        	<td id="his6">&nbsp;</td>
	</tr>		
	</tr>		
	<tr valign="bottom"> 
		<td height="34" colspan="6"><input type="submit" name="boton" value="Buscar"</td>
	</tr>
</table>
</div>
<br>
<table width="1000" border="1" cellpadding="0" cellspacing="0">
	<tr>
		<td></td>
		<td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">Ticket</font></div></td>
		<td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">TD</font></div></td>
		<td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">Cod.Trabaj</font></div></td>
		<td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">Nombre</font></div></td>
		<td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">Caja</font></div></td>
		<td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">Turno</font></div></td>
		<td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">Hora/Fecha</font></div></td>
		<td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">Cod.Product</font></div></td>
		<td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">Descripcion</font></div></td>
		<td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">Cod. Trabajador Nuevo</font></div></td>
	</tr>
	<tr bgcolor="#CCCC99" onMouseOver=this.style.backgroundColor="#FFFFCC"; this.style.cursor="hand"; onMouseOut=this.style.backgroundColor="#CCCC99"> 
<?php 
if($n_rs_tickets > 0) {
	$conta1 = 0;
	while($conta1 < $n_rs_tickets) {
		$documento1	= pg_result($rs_ticket,$conta1,0);  //echo "documento1".$documento1."<br>"; 
		$trans1		= pg_result($rs_ticket,$conta1,1);
		$td1		= pg_result($rs_ticket,$conta1,2);
		$caja1		= pg_result($rs_ticket,$conta1,3);
		$turno1		= pg_result($rs_ticket,$conta1,4);
		$dia1		= pg_result($rs_ticket,$conta1,5);
		$codigo1	= pg_result($rs_ticket,$conta1,6);
		$a		= pg_result($rs_ticket,$conta1,7);

		$sql_nomb = "	SELECT 
					ch_nombre1, 
					ch_apellido_paterno 
				FROM 
					pla_ta_trabajadores 
				WHERE
					trim(ch_codigo_trabajador) = '".$documento1."'";

		$rs_sql_nomb	= pg_exec($sql_nomb);
		$nombre		= pg_result($rs_sql_nomb,0,0);
		$apellido	= pg_result($rs_sql_nomb,0,1);
		$nomb_ape1	= $nombre.$apellido;

		$sql_prod 	= "SELECT trim(art_descripcion) FROM int_articulos WHERE trim(art_codigo)='".$codigo1."'";
		$rs_prods 	= pg_exec($sql_prod);
		$descripcion1 	= pg_result($rs_prods,0,0);
		$m_codtrab 	= $documento1;

                echo "<tr>";
                echo "<td><input type='checkbox' name='idp[".$a."]' value='".$a."'></td>";
                echo "<td>".$trans1."</td>";
                echo "<td>".$td1."</td>";
                echo "<td>".$documento1."</td>";
                echo "<td>".$nomb_ape1."</td>";
                echo "<td>".$caja1."</td>";
                echo "<td>".$turno1."</td>";
                echo "<td>".$dia1."</td>";
                echo "<td>".$codigo1."</td>";
                echo "<td>".$descripcion1."</td>";
                echo "<td><input type='text' name='m_codtrab[".$a."]' size='12' maxlength='6' value=".$m_codtrab."></td>";
                echo "</tr>";

        	$conta1++;
        }
}

?>
	<tr>
		<td colspan="2"><input type="submit" name="bt" value="Modificar"></td>
	</tr>
</table>
<br>
</div>
</form>
<?php  
if($modo == "actual") {
?>
<script language="JavaScript">
	ocultarHis();
</script>
<?php  } ?>
</body>
</html>
