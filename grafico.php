<?php
session_start();
include("../config.php");
include("/sistemaweb/utils/funcion-texto.php");
include("/sistemaweb/func_print_now.php");
//require"/sistemaweb/login.php");
//$num_mov = "0140000271";
//$tip_mov = "01";
//echo $num_mov;

$rs = pg_exec("select trim(ch_nombre_almacen) from inv_ta_almacenes 
where ch_almacen='".$_SESSION['almacen']."' ");
$D = pg_fetch_array($rs,0);
$desc_alma = $D[0]." - ".$_SESSION['almacen'];

$rs = pg_exec("select trim(tran_descripcion) from inv_tipotransa where trim(tran_codigo)=trim('$tip_mov') ");
$D = pg_fetch_array($rs,0);

$desc_mov = $tip_mov." - ".$D[0];


//print "holaaaaaa<br>";
//echo "hola<br>".$_SESSION['ip_printer_default'];

//echo "holAAAAAAAAAAa<br>".$GLOBALS["ip_printer_default"];

//round((pre_precio_act1*0.81 - i.mov_costounitario)*100/i.mov_costounitario,0) 

$rs = pg_exec("select  i.mov_numero as num_mov , to_char(i.mov_fecha,'dd/mm/yyyy hh:mi:ss') as mov_fecha 
	, i.com_num_compra as num_compra
	, i.mov_entidad as entidad	
	, pro.pro_razsocial as razsoc, i.art_codigo as art_codigo 	, trim(a.art_descripcion) as art_des
	, round(i.mov_cantidad,3) as cantidad  , round(i.mov_costounitario,3) as costo	
	, round(i.mov_costounitario*i.mov_cantidad,3) as total   ,	i.mov_almaorigen as alma_ori
	, i.mov_almadestino as alma_des	,	i.mov_tipdocuref as tip_docref
	, i.mov_docurefe as docref, round(pre_precio_act1,3) as precio, round(g.tab_num_01,0) as margen, 
	case WHEN i.mov_costounitario > 0 THEN round(100*((pre_precio_act1 / (1 + util_fn_igv()/100) / i.mov_costounitario) - 1),0) 
	Else '0' End as margen_real,
	case WHEN g.tab_num_01> (
	case WHEN i.mov_costounitario > 0 THEN round(100*((pre_precio_act1 / (1 + util_fn_igv()/100) / i.mov_costounitario) - 1),0)  
	     Else '0' end) THEN ' (*) ' Else '&nbsp;' end as mayor, 

	(util_fn_igv()/100) as IGV 
	from inv_movialma i , int_articulos a , fac_lista_precios p, int_tabla_general g, int_proveedores pro
	where a.art_codigo=i.art_codigo and p.art_codigo = a.art_codigo and i.mov_entidad = pro.pro_codigo
	 and g.tab_tabla='20' and g.tab_elemento = a.art_linea and mov_numero='$num_mov' 
	and tran_codigo='$tip_mov' ");
	
	

    $tit1 = "<p>{almacen}</p><p align='center'>FORMULARIO {desc_form} - {fecha:hora}</p>";


    $cab1 = "<tr><th>{Formulario}</th><th>{Fecha}</th><th>{Numero O/C}</th><th colspan='03'>{Prov}</th><th>{Origen}</th><th>{Destino}</th><th>{Doc. Ref}</th></tr>";	  
	  
	  
	$cab2 = "<tr><td colspan='9'>&nbsp;<td><tr><tr><th>{Codigo}</th><th>{Descripcion}</th><th>{Costo}</th><th>{Cantidad}</th><th>{Total}</th><th>{margen}</th><th>{precio}</th><th>{margen_real}</th></tr>";
	
				
	$det1 = "<tr><td align='center'>{num_mov}</td><td align='center'>{mov_fecha}</td><td align='center'>{num_compra}</td><td align='center' colspan='03'>{entidad}</td><td align='center'>{alma_ori}</td><td align='center'>{alma_des}</td><td align='center'>{tip_docref}-{docref}</td></tr>";

	$det2 = "<tr><td align='center'>{art_codigo}</td><td>{art_des}</td><td align='right'>{costo}</td><td align='right'>{cantidad}</td><td align='right'>{total}</td><td align='right'>{margen}</td><td align='right'>{precio}</td><td align='right'>{margen_real}</td><th>{mayor}</th></tr>";


	$total = "<tr><td colspan='9'>&nbsp;</td></tr><tr style='font-weight:bold'><td>{art_codigo}</td><td>{art_des}</td><td>{costo}</td><td>{cantidad}</td><td align='right'>Sub Total</td><td align='right'>IGV</td><td align='right'>TOTAL</td><td>{margen_real}</td><td>{mayor}</td><td></td><td></td><td></td></tr>
<tr style='font-weight:bold'><td>{art_codigo}</td><td>{art_des}</td><td>{costo}</td><td>{cantidad}</td><td align='right'>{total}</td><td align='right'>{margen}</td><td align='right' width='70'>{precio}</td><td>{margen_real}</td><td>{mayor}</td><td></td><td></td><td></td></tr>";
	

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
    $tb_texto="";
	print "<center><table border='1'>";
	$tb_texto = "<table>";
	$ST = 0;
	$IGV = 0;
	$T = 0;
	for($i=0;$i<pg_numrows($rs);$i++){
		
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
		 
		 if($i==0){
	
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
		    $cab2 = str_replace("{precio}",str_pad("Precio",11),$cab2);
		    $cab2 = str_replace("{margen}",str_pad("Margen",11),$cab2);
		    $cab2 = str_replace("{margen_real}",str_pad("Margen Real",11),$cab2);
		    $cab2 = str_replace("{mayor}",str_pad("      ",11),$cab2);
		    
		    $tit1 = str_replace("{almacen}",$desc_alma,$tit1);
		    $tit1 = str_replace("{desc_form}",$desc_mov,$tit1);
		    $tit1 = str_replace("{fecha:hora}",date("d/m/Y"),$tit1);
		    print $tit1;
		    $tb_texto=$tb_texto.$tit1;
		    
		    print $cab1;
		    $tb_texto=$tb_texto.$cab1;

		    print $deta1;
		    $tb_texto=$tb_texto.$deta1;

		    print $cab2;
		    $tb_texto=$tb_texto.$cab2;
		    
		}
		 
		 $deta2 = str_replace("{art_codigo}",str_pad($A["art_codigo"],15),$deta2);
		 $deta2 = str_replace("{art_des}",str_pad($A["art_des"],40),$deta2);
		 $deta2 = str_replace("{cantidad}",str_pad($A["cantidad"],10),$deta2);
		 $deta2 = str_replace("{costo}",str_pad($A["costo"],10),$deta2);
		 $deta2 = str_replace("{total}",str_pad($A["total"],10),$deta2);
		 $deta2 = str_replace("{precio}",str_pad($A["precio"],2),$deta2);
		 $deta2 = str_replace("{margen}",str_pad($A["margen"],2),$deta2);
		 $deta2 = str_replace("{margen_real}",str_pad($A["margen_real"],2),$deta2);
		 $deta2 = str_replace("{mayor}",str_pad($A["mayor"],2),$deta2);
		 
		 $ST = $ST + $A["total"];
		 $IGV = $IGV + $A["total"]* $A["igv"];
		 $T = $T + $A["total"]* (1+$A["igv"]);
		 
		 print $deta2;
		 $tb_texto=$tb_texto.$deta2;
	}
	
	/*Para el total*/
	$total = str_replace("{art_codigo}",str_pad("",15),$total);
	$total = str_replace("{art_des}",str_pad("",40),$total);
	$total = str_replace("{cantidad}",str_pad("",10),$total);
	$total = str_replace("{costo}",str_pad("",10),$total);
	$total = str_replace("{total}",str_pad(number_format($ST, 2, '.', ''),8),$total);
	$total = str_replace("{alma_ori}",str_pad("",8),$total);
	$total = str_replace("{alma_des}",str_pad("",8),$total);
	$total = str_replace("{tip_docref}",str_pad("",2),$total);
	$total = str_replace("{precio}",str_pad(number_format($T, 2, '.', ''),8),$total);
	$total = str_replace("{margen}",str_pad(number_format($IGV, 2, '.', ''),8),$total);
	$total = str_replace("{margen_real}",str_pad("",2),$total);
	$total = str_replace("{mayor}",str_pad("",2),$total);
	$total = str_replace("{docref}",str_pad("",9),$total);
		
	/*Para el total*/
	print $total;

	print "</table>";

	$tb_texto = $tb_texto."\n".$total."</table></center>";

	/*Para la impresion a texto*/

if($accion=="Imprimir"){

		//imprimir2($tb_texto,$L,$C,"/tmp/carajo.txt","Titulo");
		$tb_texto = "raya".$tb_texto."raya\n\n";
		$txt = trim($tip_mov).trim($num_mov).".txt";
		//echo "<br>".$txt;
		$archivo="/tmp/".$txt;
		
		//imprimir3($tb_texto,"/tmp/".$txt,false);
		imprimir3($tb_texto,$archivo,false);

		print_now($archivo);
		pg_close();
		print "<script>window.close();</script>";
}
?>
<form name="form1" method="post">
    <input name="num_mov"  type="hidden" value="<?php echo $num_mov;?>">
    <input name="tip_mov"  type="hidden" value="<?php echo $tip_mov;?>">
    <br/><input name="accion" type="submit" value="Imprimir">
</form>
    <script>form1.accion.focus();</script>
</body>
</html>
<?php
pg_close();
