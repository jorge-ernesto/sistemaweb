<?php
session_start();
include("../config.php");
include("/sistemaweb/utils/funcion-texto.php");
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


$rs = pg_exec("select g.mov_numero as num_mov ,  to_char(g.dt_guia_fecha,'dd/mm/yyyy') as mov_fecha
	, ' ' as num_compra
	, g.ch_guia_entidad as entidad	, g.art_codigo||' - '||g.ch_codigo_activo, trim(a.art_descripcion) as art_des
	, trunc(g.nu_guia_cantidad,3) as cantidad	,trunc(g.nu_costounitario,3) as costo
	, trunc(g.nu_costounitario*g.nu_guia_cantidad,3) as total ,	g.mov_almaorigen as alma_ori
	, g.mov_almadestino as alma_des	,	'09' as tip_docref
	, g.ch_guia_numero as docref
	from inv_ta_guias_remision g, 	int_articulos a 
	where a.art_codigo=g.art_codigo and g.mov_numero='$num_mov' ");
	
	

    $tit1 = "<p>sistemaweb - ESTACION {almacen}</p><p align='center'>FORMULARIO {desc_form} - {fecha:hora}</p>";


    $cab1 = "<tr><td>{Formulario}</td><td>{Fecha}</td><td>{Numero O/C}</td><td>{Prov}</td><td></td><td></td><td></td><td></td></tr>";	  
	  
	  
	$cab2 = "<tr><td>{Codigo}</td><td>{Descripcion}</td><td>{Cantidad}</td><td>{Costo}</td><td>{Total}</td><td>{Origen}</td><td>{Destino}</td><td>{Doc. Ref}</td></tr>";
	
				
	$det1 = "<tr><td>{num_mov}</td><td>{mov_fecha}</td><td>{num_compra}</td><td>{entidad}</td><td></td><td></td><td></td><td></td></tr>";

	$det2 = "<tr><td>{art_codigo}</td><td>{art_des}</td><td>{cantidad}</td><td>{costo}</td><td>{total}</td><td>{alma_ori}</td><td>{alma_des}</td><td>{tip_docref}-{docref}</td></tr>";


	$total = "<tr><td>{art_codigo}</td><td>{art_des}</td><td>{cantidad}</td><td>{costo}</td><td><strong>{total}</strong></td><td>{alma_ori}</td><td>{alma_des}</td><td>{tip_docref}-{docref}</td></tr>";
	

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Cintillo </title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../sistemaweb.css" rel="stylesheet" type="text/css">
</head>

<body>
<?php/*?>

<p>sistemaweb - ESTACION {almacen}</p>
<p align="center"> FORMULARIO {tip_form} {desc_form} - {fecha:hora}</p>

<?php*/?>
<?php/*?>
<table width="770" height="47" border="0">
  <tr> 
    <td width="87">Codigo</td>
    <td width="112">Descripcion</td>
    <td width="87">Cantidad</td>
    <td width="76">Costo</td>
    <td width="59">Total </td>
    <td width="101">Origen</td>
    <td width="94">Destino</td>
    <td width="120">Doc. Ref</td>
  </tr>
  <tr> 
    <td height="20">{num_mov}</td>
    <td>{mov_fecha}</td>
    <td>{num_compra}</td>
    <td>{entidad}</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr> 
    <td height="20">{art_codigo}</td>
    <td>{art_des}</td>
    <td>{cantidad}</td>
    <td>{costo}</td>
    <td>{total}</td>
    <td>{alma_ori}</td>
    <td>{alma_des}</td>
    <td>{tip_docref}-{docref}</td>
  </tr>
</table>
<?php*/?>
<?php
    $tb_texto="";

	print "<table>";
	$tb_texto = "<table>";
	$T=0;
	for($i=0;$i<pg_numrows($rs);$i++){
		
		$A = pg_fetch_array($rs,$i);
		
		$deta1 = $det1;
		$deta2 = $det2;
		 
		 $deta1 = str_replace("{num_mov}",str_pad($A["num_mov"],15),$deta1);
		 $deta1 = str_replace("{mov_fecha}",str_pad($A["mov_fecha"],15),$deta1);
		 $deta1 = str_replace("{num_compra}",str_pad($A["num_compra"],15),$deta1);
		 $deta1 = str_replace("{entidad}",str_pad($A["entidad"],10),$deta1);
		 
		 
		 
		 if($i==0){
	
		    $cab1 = str_replace("{Formulario}",str_pad("Formulario",15),$cab1);
		    $cab1 = str_replace("{Fecha}",str_pad("Fecha",15),$cab1);
		    $cab1 = str_replace("{Numero O/C}",str_pad("Numero O/C",15),$cab1);
		    $cab1 = str_replace("{Prov}",str_pad("Prov",10),$cab1);

		    
		    $cab2 = str_replace("{Codigo}",str_pad("Codigo",15),$cab2);
		    $cab2 = str_replace("{Descripcion}",str_pad("Descripcion",40),$cab2);
		    $cab2 = str_replace("{Cantidad}",str_pad("Cantidad",10),$cab2);
		    $cab2 = str_replace("{Costo}",str_pad("Costo",10),$cab2);
		    $cab2 = str_replace("{Total}",str_pad("Total",10),$cab2);
		    $cab2 = str_replace("{Origen}",str_pad("Origen",8),$cab2);
		    $cab2 = str_replace("{Destino}",str_pad("Destino",8),$cab2);
		    $cab2 = str_replace("{Doc. Ref}",str_pad("Doc. Ref",11),$cab2);
		    
		    
		    $tit1 = str_replace("{almacen}",$desc_alma,$tit1);
		    $tit1 = str_replace("{desc_form}",$desc_mov,$tit1);
		    $tit1 = str_replace("{fecha:hora}",date("d/m/Y"),$tit1);
		    print $tit1;
		    $tb_texto=$tb_texto.$tit1;
		    
		    print $cab1;
		    $tb_texto=$tb_texto.$cab1;
		    
		    print $cab2;
		    $tb_texto=$tb_texto.$cab2;
		    
		    print $deta1;
		    $tb_texto=$tb_texto.$deta1;
		    
		}
		 
		 $deta2 = str_replace("{art_codigo}",str_pad($A["art_codigo"],15),$deta2);
		 $deta2 = str_replace("{art_des}",str_pad($A["art_des"],40),$deta2);
		 $deta2 = str_replace("{cantidad}",str_pad($A["cantidad"],10),$deta2);
		 $deta2 = str_replace("{costo}",str_pad($A["costo"],10),$deta2);
		 $deta2 = str_replace("{total}",str_pad($A["total"],10),$deta2);
		 $deta2 = str_replace("{alma_ori}",str_pad($A["alma_ori"],8),$deta2);
		 $deta2 = str_replace("{alma_des}",str_pad($A["alma_des"],8),$deta2);
		 $deta2 = str_replace("{tip_docref}",str_pad($A["tip_docref"],2),$deta2);
		 $deta2 = str_replace("{docref}",str_pad($A["docref"],9),$deta2);
		 $deta2 = str_replace("- null","",$deta2);
		 $deta2 = str_replace("null -","",$deta2);
		 
		 $T = $T + $A["total"];
		 
		 print $deta2;
		 $tb_texto=$tb_texto.$deta2;
	}
	
	/*Para el total*/
	$total = str_replace("{art_codigo}",str_pad("TOTAL",15),$total);
	$total = str_replace("{art_des}",str_pad("",40),$total);
	$total = str_replace("{cantidad}",str_pad("",10),$total);
	$total = str_replace("{costo}",str_pad("",10),$total);
	$total = str_replace("{total}",str_pad($T,10),$total);
	$total = str_replace("{alma_ori}",str_pad("",8),$total);
	$total = str_replace("{alma_des}",str_pad("",8),$total);
	$total = str_replace("{tip_docref}",str_pad("",2),$total);
	$total = str_replace("{docref}",str_pad("",9),$total);
		
	/*Para el total*/
	
	print $total;
	
	print "</table>";
	$tb_texto = $tb_texto."\n".$total."</table>";

	

	/*Para la impresion a texto*/
	if($accion=="Imprimir"){
	    $rs = pg_exec("select par_valor as print_server from int_parametros 
	    where par_nombre ='print_server' ");
	    $A = pg_fetch_array($rs,0);
	    $print_server =  $A["print_server"];
	    
	    $rs = pg_exec("select par_valor as print_netbios from int_parametros 
	    where par_nombre ='print_netbios' ");
	    $A = pg_fetch_array($rs,0);
	    $print_netbios =  $A["print_netbios"];
	    
	    
	    $rs = pg_exec("select par_valor as print_name from int_parametros 
	    where par_nombre ='print_name' ");
	    $A = pg_fetch_array($rs,0);
	    $print_name =  $A["print_name"];
	    	    
	    //imprimir2($tb_texto,$L,$C,"/tmp/carajo.txt","Titulo");
		$tb_texto = "raya".$tb_texto."raya\n\n";
		$txt = trim($tip_mov).trim($num_mov).".txt";
		imprimir3($tb_texto,"/tmp/".$txt,false);
		
		exec("smbclient //".$print_netbios."/".$print_name." -c 'print /tmp/".$txt."' -P -N -I ".$print_server." ");
		pg_close();
		
		print   "<script>window.close();</script>";
		
	
	}
?>
<form name="form1" method="post">
    <input name="num_mov"  type="hidden" value="<?php echo $num_mov;?>">
    <input name="tip_mov"  type="hidden" value="<?php echo $tip_mov;?>">
    <input name="accion" type="submit" value="Imprimir">
</form>
    <script>form1.accion.focus();</script>
</body>
</html>
<?php
pg_close();
?>
