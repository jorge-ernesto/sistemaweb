<?php
session_start();
include("../config.php");
include("/sistemaweb/utils/funcion-texto.php");
include("/sistemaweb/func_print_now.php");

$rs = pg_exec("select trim(ch_nombre_almacen) from inv_ta_almacenes where ch_almacen='".$_SESSION['almacen']."' ");
$D  = pg_fetch_array($rs,0);
$desc_alma = $D[0]." - ".$_SESSION['almacen'];

$rs = pg_exec("select trim(tran_descripcion) from inv_tipotransa where trim(tran_codigo)=trim('$tip_mov') ");
$D  = pg_fetch_array($rs,0);

$desc_mov = $tip_mov." - ".$D[0];

$tip_mov = $_GET['tip_mov'];
$num_mov = $_GET['num_mov'];
$datec = $_GET['fecha'];

$where_numero_orden_compra = "";
if ( isset($compra) && $compra != '' ){
	$where_numero_orden_compra = "AND com_num_compra = '" . $compra . "'";
}

$sql = "
SELECT
i.mov_numero as num_mov, 
to_char(i.mov_fecha,'dd/mm/yyyy hh24:mi') as mov_fecha, 
i.com_num_compra as num_compra, 
i.mov_entidad as entidad, 
(CASE WHEN i.mov_entidad ='' THEN '' ELSE pro.pro_razsocial END) AS razsoc, 
i.art_codigo as art_codigo, 
trim(a.art_descripcion) as art_des, 
round(i.mov_cantidad,3) as cantidad, 
round(i.mov_costounitario,4) as costo, 
round(i.mov_costounitario*i.mov_cantidad,4) as total,	
i.mov_almaorigen as alma_ori, 
i.mov_almadestino as alma_des,	
i.mov_tipdocuref as tip_docref, 
i.mov_docurefe as docref, 
round(pre_precio_act1,3) as precio_compra, 
round(g.tab_num_01,0) as margen, 
case WHEN i.mov_costounitario > 0 THEN round(100*((pre_precio_act1 / (1 + util_fn_igv()/100) / i.mov_costounitario) - 1),0) Else '0' End as margen_real,
case WHEN g.tab_num_01> (
case WHEN i.mov_costounitario > 0 THEN round(100*((pre_precio_act1 / (1 + util_fn_igv()/100) / i.mov_costounitario) - 1),0) Else '0' end) THEN ' (*) ' Else '&nbsp;' end as mayor, 
(util_fn_igv()/100) as IGV,
i.mov_fecha as datefecha
FROM
inv_movialma i
LEFT JOIN int_articulos a ON (a.art_codigo=i.art_codigo)
LEFT JOIN fac_lista_precios p ON (p.art_codigo = a.art_codigo AND p.pre_lista_precio=util_fn_cd_precio())
LEFT JOIN int_tabla_general g ON (g.tab_tabla='20' and g.tab_elemento = a.art_linea)
LEFT JOIN int_proveedores pro ON (i.mov_entidad = pro.pro_codigo)
WHERE
mov_numero = '$num_mov'
AND tran_codigo	= '$tip_mov'
AND to_char(i.mov_fecha,'dd/mm/yyyy hh24:mi') = '$datec'
" . $where_numero_orden_compra . "
ORDER BY
datefecha asc;
	";

	$rs = pg_exec($sql);

/*echo "<pre>";
echo $sql;
echo "</pre>";*/
			
//SACAMOS LOS ARTICULOS
/*$array_item_tmp=array();
for($i=0;$i<pg_numrows($rs);$i++) {
	$A = pg_fetch_array($rs,$i);
	$array_item_tmp[]="'".trim($A['art_codigo'])."'";
}
$articulos_find= implode($array_item_tmp, ",");

$sqlart_codigo="
SELECT * FROM (SELECT DET.art_codigo,DET.nu_fac_precio,CAB.dt_fac_fecha FROM fac_ta_factura_cabecera CAB INNER JOIN fac_ta_factura_detalle DET
ON (CAB.ch_fac_tipodocumento=DET.ch_fac_tipodocumento AND CAB.ch_fac_seriedocumento=DET.ch_fac_seriedocumento
AND CAB.ch_fac_numerodocumento=DET.ch_fac_numerodocumento AND CAB.cli_codigo=DET.cli_codigo)
AND DET.art_codigo in($articulos_find)
ORDER BY 
DET.art_codigo,
CAB.dt_fac_fecha DESC) AS T;
";

$rs_art = pg_exec($sqlart_codigo);
$array_precio_cercano=array();
for($ix=0;$ix<pg_numrows($rs_art);$ix++) {
	$A_tmp = pg_fetch_array($rs_art,$ix);
	$art_codigo=trim($A_tmp['art_codigo']);
	$dt_fac_fecha=trim($A_tmp['dt_fac_fecha']);
	$array_precio_cercano[$art_codigo][$dt_fac_fecha]=$A_tmp['nu_fac_precio'];
	}

function findPricecurrent($cod,$array_precio_cercano){
	$cod=trim($cod);
	if(count($array_precio_cercano)==0){
		return array("E"=>false);
	}
	$a_tmp=$array_precio_cercano[$cod];
	if(count($a_tmp)==0){
		return array("E"=>false);
	}
	$arrayprecio=array();
	foreach($a_tmp as $key => $p){
		
		$arrayprecio['fecha']=$key;
		$arrayprecio['precio']=$p;		
		break;
	}
	return array("E"=>true,"data"=>$arrayprecio);
}*/

//------------		
			
$tit1 = "<p>{almacen}</p><p align='center'>FORMULARIO {desc_form} - {fecha:hora}</p>";

$cab1 = "<tr>";
$cab1 .= "<th style='font-size:0.7em; color:black;background-color: #D9F9B2'>{Formulario}</th>";
$cab1 .= "<th style='font-size:0.7em; color:black;background-color: #D9F9B2'>{Fecha}</th>";
$cab1 .= "<th style='font-size:0.7em; color:black;background-color: #D9F9B2'>{Numero O/C}</th>";
$cab1 .= "<th style='font-size:0.7em; color:black;background-color: #D9F9B2' colspan='03'>{Prov}</th>";
$cab1 .= "<th style='font-size:0.7em; color:black;background-color: #D9F9B2'>{Origen}</th>";
$cab1 .= "<th style='font-size:0.7em; color:black;background-color: #D9F9B2'>{Destino}</th>";
$cab1 .= "<th style='font-size:0.7em; color:black;background-color: #D9F9B2'>{Doc. Ref}</th></tr>";	  

$cab2 = "<tr>";
$cab2 .= "<td colspan='9'>&nbsp;<td>";

$cab2 .= "<tr>";
$cab2 .= "<tr><th style='font-size:0.7em; color:black;background-color: #D9F9B2'>{Codigo}</th>";
$cab2 .= "<th style='font-size:0.7em; color:black;background-color: #D9F9B2'>{Descripcion}</th>";
$cab2 .= "<th style='font-size:0.7em; color:black;background-color: #D9F9B2'>{Costo}</th>";
$cab2 .= "<th style='font-size:0.7em; color:black;background-color: #D9F9B2'>{Cantidad}</th>";
$cab2 .= "<th style='font-size:0.7em; color:black;background-color: #D9F9B2'>{Total}</th>";
$cab2 .= "<th style='font-size:0.7em; color:black;background-color: #D9F9B2'>{margen}</th>";
$cab2 .= "<th style='font-size:0.7em; color:black;background-color: #D9F9B2'>{precio_compra}</th>";
$cab2 .= "<th style='font-size:0.7em; color:black;background-color: #D9F9B2'>{margen_real}</th>";
$cab2 .= "<th style='font-size:0.7em; color:black;background-color: #D9F9B2'>&nbsp;</th></tr>";

$det1 = "<tr>";
$det1 .= "<td style='background-color: #F2FFE2' align='center'>{num_mov}</td>";
$det1 .= "<td style='background-color: #F2FFE2' align='center'>{mov_fecha}</td>";
$det1 .= "<td style='background-color: #F2FFE2' align='center'>{num_compra}</td>";
$det1 .= "<td style='background-color: #F2FFE2' align='center' colspan='03'>{entidad}</td>";
$det1 .= "<td style='background-color: #F2FFE2' align='center'>{alma_ori}</td>";
$det1 .= "<td style='background-color: #F2FFE2' align='center'>{alma_des}</td>";
$det1 .= "<td style='background-color: #F2FFE2' align='center'>{tip_docref}-{docref}</td></tr>";

$det2 = "<tr bgcolor=''>";
$det2 .= "<td style='background-color: #F2FFE2' align='left'>{art_codigo}</td>";
$det2 .= "<td style='background-color: #F2FFE2' class='grid_detalle_impar' align='left'>{art_des}</td>";
$det2 .= "<td style='background-color: #F2FFE2' align='right'>{costo}</td>";
$det2 .= "<td style='background-color: #F2FFE2' align='right'>{cantidad}</td>";
$det2 .= "<td style='background-color: #F2FFE2' align='right'>{total}</td>";
$det2 .= "<td style='background-color: #F2FFE2' align='right'>{margen}</td>";
$det2 .= "<td style='background-color: #F2FFE2' align='right'>{precio_compra}</td>";
$det2 .= "<td style='background-color: #F2FFE2' align='right'>{margen_real}</td>";
$det2 .= "<th style='background-color: #F2FFE2' >{mayor}</th></tr>";

$total = "<tr>";
$total .= "<td colspan='9'>&nbsp;</td>";
$total .= "</tr>";

$total .= "<tr style='font-weight:bold'>";
$total .= "<td>{art_codigo}</td>";
$total .= "<td>{art_des}</td>";
$total .= "<td>{costo}</td>";
$total .= "<td>{cantidad}</td>";
$total .= "<td></td><td></td>";
$total .= "<td style='font-size:0.7em; color:black;background-color: #D9F9B2' align='center'>SUB TOTAL</td>";
$total .= "<td style='font-size:0.7em; color:black;background-color: #D9F9B2' align='center'>I.G.V.</td>";
$total .= "<td style='font-size:0.7em; color:black;background-color: #D9F9B2' align='center'>TOTAL</td>";
$total .= "<td>{margen_real}</td>";
$total .= "<td>{mayor}</td>";
$total .= "</tr>";

$total .= "<tr style='font-weight:bold'>";
$total .= "<td>{art_codigo}</td>";
$total .= "<td>{art_des}</td>";
$total .= "<td>{costo}</td>";
$total .= "<td>{cantidad}</td>";
$total .= "<td></td><td></td>";
$total .= "<td style='font-size:0.7em; color:black' align='right'>{total}</td>";
$total .= "<td style='font-size:0.7em; color:black' align='right'>{margen}</td>";
$total .= "<td style='font-size:0.7em; color:black' align='right' width='70'>{precio_compra}</td>";
$total .= "<td>{margen_real}</td>";
$total .= "<td>{mayor}</td>";
$total .= "</tr>";
	
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Cintillo </title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../sistemaweb.css" rel="stylesheet" type="text/css">
</head>

<body>
<?php

$tb_texto = "";
print "<center><table>";
$tb_texto = "<table>";
$ST  = 0;
$IGV = 0;
$T   = 0;

$log="";
for($i=0;$i<pg_numrows($rs);$i++) {
	$A = pg_fetch_array($rs,$i);
		
	$deta1 = $det1;
	$deta2 = $det2;

	$deta1 = str_replace("{num_mov}",str_pad($A["num_mov"],15),$deta1);
	$deta1 = str_replace("{mov_fecha}",str_pad($A["mov_fecha"],15),$deta1);
	$deta1 = str_replace("{num_compra}",str_pad($A["num_compra"],15),$deta1);
	$deta1 = str_replace("{entidad}",str_pad($A["entidad"].' - '.$A["razsoc"],10),$deta1);
	$deta1 = str_replace("{alma_ori}",str_pad($A["alma_ori"],8),$deta1);
	$deta1 = str_replace("{alma_des}",str_pad($A["alma_des"],8),$deta1);
	$deta1 = str_replace("{docref}",str_pad($A["docref"],9),$deta1);
	$deta1 = str_replace("{tip_docref}",str_pad($A["tip_docref"],2),$deta1);
		 
	if($i == 0) {

		$cab1 = str_replace("{Formulario}",str_pad("Formulario",15),$cab1);
		$cab1 = str_replace("{Fecha}",str_pad("Fecha",15),$cab1);
		$cab1 = str_replace("{Numero O/C}",str_pad("Numero O/C",15),$cab1);
		$cab1 = str_replace("{Prov}",str_pad("Proveedor",10),$cab1);
		$cab1 = str_replace("{Origen}",str_pad("Origen",8),$cab1);
		$cab1 = str_replace("{Destino}",str_pad("Destino",8),$cab1);
		$cab1 = str_replace("{Doc. Ref}",str_pad("Doc. Ref",11),$cab1);
		    
		$cab2 = str_replace("{Codigo}",str_pad("Codigo",15),$cab2);
		$cab2 = str_replace("{Descripcion}",str_pad("Descripcion",40),$cab2);
		$cab2 = str_replace("{Cantidad}",str_pad("Cantidad",10),$cab2);
		$cab2 = str_replace("{Costo}",str_pad("Costo",10),$cab2);
		$cab2 = str_replace("{Total}",str_pad("Sub-Total",10),$cab2);
		$cab2 = str_replace("{precio_compra}",str_pad("Precio",11),$cab2);
		$cab2 = str_replace("{margen}",str_pad("Margen",11),$cab2);
		$cab2 = str_replace("{margen_real}",str_pad("Margen Real",11),$cab2);
		$cab2 = str_replace("{mayor}",str_pad("      ",11),$cab2);
		    
		$tit1 = str_replace("{almacen}",$desc_alma,$tit1);
		$tit1 = str_replace("{desc_form}",$desc_mov,$tit1);
		$tit1 = str_replace("{fecha:hora}",date("d/m/Y"),$tit1);

		print $tit1;
		$tb_texto = $tb_texto.$tit1;
				    
		print $cab1;
		$tb_texto = $tb_texto.$cab1;

		print $deta1;
		$tb_texto = $tb_texto.$deta1;

		print $cab2;
		$tb_texto = $tb_texto.$cab2;
		    
	}

/*		$result= findPricecurrent($A["art_codigo"],$array_precio_cercano);
		$precio="";
		$fecha="";
		if($result['E']){
			$precio=$result['data']['precio'];
			$fecha=$result['data']['fecha'];
			$log.="<spam> El precio del ".$A["art_codigo"]." corresponde al fecha =>".$fecha."(FV) </spam><br/>";
		}else{
			$precio=$A["precio"];
			if(empty($precio) or !isset($precio)){
				$log.="<spam> No se encontro un precio en su  lista,ni en facturas de ventas </spam><br/>";
				$precio=0;
			}
			
		}
*/

	$deta2 = str_replace("{art_codigo}",str_pad($A["art_codigo"],15),$deta2);
	$deta2 = str_replace("{art_des}",str_pad($A["art_des"],40),$deta2);
	$deta2 = str_replace("{cantidad}",str_pad($A["cantidad"],10),$deta2);
	$deta2 = str_replace("{costo}",str_pad($A["costo"],10),$deta2);
	$deta2 = str_replace("{total}",str_pad($A["total"],10),$deta2);
	$deta2 = str_replace("{precio_compra}",str_pad($A["precio_compra"],2),$deta2);
	$deta2 = str_replace("{margen}",str_pad($A["margen"],2),$deta2);
	$deta2 = str_replace("{margen_real}",str_pad($A["margen_real"],2),$deta2);
	$deta2 = str_replace("{mayor}",str_pad($A["mayor"],2),$deta2);
		 
	$ST  = $ST + $A["total"];
	$IGV = $IGV + $A["total"]* $A["igv"];
	$T   = $T + $A["total"]* (1+$A["igv"]);
		 
	print $deta2;
	$tb_texto = $tb_texto.$deta2;
}
	
$total = str_replace("{art_codigo}",str_pad("",15),$total);
$total = str_replace("{art_des}",str_pad("",40),$total);
$total = str_replace("{cantidad}",str_pad("",10),$total);
$total = str_replace("{costo}",str_pad("",10),$total);
$total = str_replace("{total}",str_pad(number_format($ST, 4, '.', ''),8),$total);
$total = str_replace("{alma_ori}",str_pad("",8),$total);
$total = str_replace("{alma_des}",str_pad("",8),$total);
$total = str_replace("{tip_docref}",str_pad("",2),$total);
$total = str_replace("{precio_compra}",str_pad(number_format($T, 2, '.', ''),8),$total);
$total = str_replace("{margen}",str_pad(number_format($IGV, 4, '.', ''),8),$total);
$total = str_replace("{margen_real}",str_pad("",2),$total);
$total = str_replace("{mayor}",str_pad("",2),$total);
$total = str_replace("{docref}",str_pad("",9),$total);
		
print $total;

print "</table>";

$tb_texto = $tb_texto."\n".$total."</table></center>";

if($accion == "Imprimir") {

	$tb_texto = "raya".$tb_texto."raya\n\n";
	$txt = trim($tip_mov).trim($num_mov).".txt";
	$archivo = "/tmp/".$txt;
		
	imprimir3($tb_texto,$archivo,false);
	print_now($archivo);
	pg_close();
	print "<script>window.close();</script>";

}

?>
<form name="form1" method="post" background="FFFFFF" style="background-color: white !important;">
	<input name="num_mov" type="hidden" value="<?php echo $num_mov;?>">
	<input name="tip_mov" type="hidden" value="<?php echo $tip_mov;?>">
	<input name="datec" type="hidden" value="<?php echo $datec;?>">
	<input name="num_compra" type="hidden" value="<?php echo $compra;?>"><br/>
	<input name="accion" type="submit" value="Cerrar ventana" onclick="window.close();"
		style="
color:#126775;
border-radius: 8px;
padding:3px;
border: 1px solid #999;
border: inset 1px solid #333;
-webkit-box-shadow: 0px 0px 8px rgba(0, 0, 0, 0.3);
-moz-box-shadow: 0px 0px 8px rgba(0, 0, 0, 0.3);
box-shadow: 0px 0px 8px rgba(0, 0, 0, 0.3);
background-color:white;
font-weight:bold;
font-size:12px;
		"
	>
	<br/>
</form>
<script>form1.accion.focus();</script>
</body>
</html>
<?php
print $log;
pg_close();
