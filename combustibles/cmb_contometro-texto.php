<?php
//session_start();
include("../config.php");
include("/sistemaweb/utils/funcion-texto.php");

include("../functions.php");
include("../valida_sess.php");



if($cod_almacen==""){
$and_cont="and cont.ch_sucursal='$almacen'";
}else{
$cod_almacen = trim($cod_almacen);
$and_cont="and cont.ch_sucursal='$cod_almacen'";
}


$q3 = "SELECT cont.ch_numeroparte as parte 
				, cont.ch_codigocombustible
				, cont.ch_tanque as tanque
                , cont.ch_surtidor as manguera
				, cont.nu_contometroinicialgalon 
				, cont.nu_contometrofinalgalon 
				, cont.nu_ventagalon 
				, cont.nu_contometroinicialvalor
				, cont.nu_contometrofinalvalor 
				, cont.nu_ventavalor 
				, cont.nu_afericionveces_x_5
				, cont.nu_consumogalon 
				, -cont.nu_descuentos 
				, comb.ch_nombrecombustible 
				, cont.dt_fechaparte
				, cont.ch_responsable
				, surt.ch_numerolado as lado
                FROM comb_ta_contometros cont, comb_ta_combustibles comb, comb_ta_surtidores surt
                WHERE cont.ch_codigocombustible = comb.ch_codigocombustible
                $and_cont and cont.ch_surtidor = surt.ch_surtidor
                and cont.dt_fechaparte >= to_date('$fechad','DD-MM-YYYY')
                and cont.dt_fechaparte <= to_date('$fechaa','DD-MM-YYYY')
                order by parte,lado,manguera,tanque" ;
                //echo $q3;



    $rs3 = pg_exec($q3);
/*
$q4 = "select comb.ch_codigocombustible as producto, comb.ch_nombrecombustible as descripcion
, sum(cont.nu_ventagalon) as venta_galon, sum(cont.nu_ventavalor) as venta_valor
, sum(cont.nu_afericionveces_x_5*5) as afer_gal,round( sum(cont.nu_afericionveces_x_5)*5*round( sum(cont.nu_ventavalor)/sum(cont.nu_ventagalon),3 ), 3) as afer_val
, sum(cont.nu_consumogalon) as consumo_galon, round( sum(cont.nu_consumogalon)*round(sum(cont.nu_ventavalor)/sum(cont.nu_ventagalon),3),3 ) as consumo_valor
, round ( sum(cont.nu_ventagalon) - sum(cont.nu_afericionveces_x_5*5) - sum(cont.nu_consumogalon) ,3) as resumen_galones
, round ( sum(cont.nu_ventavalor) - sum(cont.nu_afericionveces_x_5)*5*round( sum(cont.nu_ventavalor)/sum(cont.nu_ventagalon),3 ) - sum(cont.nu_consumogalon)*round(sum(cont.nu_ventavalor)/sum(cont.nu_ventagalon),3)  ,3) as neto_soles
from comb_ta_combustibles comb,comb_ta_contometros cont
where cont.ch_codigocombustible = comb.ch_codigocombustible
$and_cont
and cont.dt_fechaparte >= to_date('$fechad','DD-MM-YYYY')
and cont.dt_fechaparte <= to_date('$fechaa','DD-MM-YYYY')
group by producto,descripcion";*/
/*
$q4 = "select tb.producto,tb.descripcion
				,sum(venta_galon) as venta_galon
				,sum(venta_valor) as venta_valor 
				,sum(afer_gal) as afer_gal
				,sum(afer_val) as afer_val
				,sum(consumo_galon) as consumo_galon
				,sum(consumo_valor) as consumo_valor
				, sum(descuentos)
				,sum(resumen_galones) as resumen_galones
				,sum(neto_soles) as neto_soles from 
				(select comb.ch_codigocombustible as producto
				, comb.ch_nombrecombustible as descripcion
				, cont.ch_numeroparte  as parte
                , sum(cont.nu_ventagalon) as venta_galon, sum(cont.nu_ventavalor) as venta_valor
                , sum(cont.nu_afericionveces_x_5*5) as afer_gal,round( sum(cont.nu_afericionveces_x_5)*5*round( sum(cont.nu_ventavalor)/case sum(cont.nu_ventagalon) when 0 then 1 else sum(cont.nu_ventagalon) end,3 ), 3) as afer_val
                , sum(cont.nu_consumogalon) as consumo_galon, round( sum(cont.nu_consumogalon)*round(sum(cont.nu_ventavalor)/case sum(cont.nu_ventagalon) when 0 then 1 else sum(cont.nu_ventagalon) end,3),3 ) as consumo_valor
                , sum(-cont.nu_descuentos) as descuentos
                , round ( sum(cont.nu_ventagalon) - sum(cont.nu_afericionveces_x_5*5) - sum(cont.nu_consumogalon) ,3) as resumen_galones
                , round ( sum(cont.nu_ventavalor) - sum(cont.nu_afericionveces_x_5)*5*round( sum(cont.nu_ventavalor)/case sum(cont.nu_ventagalon) when 0 then 1 else sum(cont.nu_ventagalon) end,3 ) - sum(cont.nu_consumogalon)*round(sum(cont.nu_ventavalor)/case sum(cont.nu_ventagalon) when 0 then 1 else sum(cont.nu_ventagalon) end,3) - sum(-cont.nu_descuentos) ,3) as neto_soles
                from comb_ta_combustibles comb,comb_ta_contometros cont
                where cont.ch_codigocombustible = comb.ch_codigocombustible
                $and_cont
                and cont.dt_fechaparte >= to_date('$fechad','DD-MM-YYYY')
                and cont.dt_fechaparte <= to_date('$fechaa','DD-MM-YYYY')
                group by producto,descripcion,parte) as tb group by producto,descripcion";

                $rs4 = pg_exec($q4);
*/

$q4 = "	SELECT
					x.producto AS producto,
					min(x.descripcion) AS descripcion,
					sum(x.venta_galon) AS venta_galon,
					sum(x.venta_valor) AS venta_valor,
					sum(x.afer_gal) AS afer_gal,
					round(COALESCE(sum(x.afer_val),0),3) AS afer_val,
					sum(x.consumo_galon) AS consumo_galon,
					round(sum((x.consumo_galon * x.i_precio)),3) AS consumo_valor,
					sum(x.descuentos) AS descuentos,
					sum(x.resumen_galones) AS resumen_galones,
					round(sum(x.neto_soles_parcial) - sum((x.consumo_galon * x.i_precio)) - COALESCE(sum(x.afer_val),0),3) AS neto_soles
				FROM
					(SELECT
						cmb.ch_codigocombustible AS producto,
						cmb.ch_nombrecombustible AS descripcion,
						cont.ch_numeroparte AS parte,
						cont.nu_ventagalon AS venta_galon,
						cont.nu_ventavalor AS venta_valor,
						(cont.nu_afericionveces_x_5*5) AS afer_gal,
						(SELECT sum(importe) FROM pos_ta_afericiones af WHERE af.dia = cont.dt_fechaparte AND af.pump=lpad(srt.ch_numerolado,2,'0') AND af.codigo = srt.ch_codigocombustible AND af.es = cont.ch_sucursal) AS afer_val,
						cont.nu_consumogalon AS consumo_galon,
						-cont.nu_descuentos AS descuentos,
						(cont.nu_ventagalon - (cont.nu_afericionveces_x_5*5) - cont.nu_consumogalon) AS resumen_galones,
						(cont.nu_ventavalor + cont.nu_descuentos) AS neto_soles_parcial,
						round((cont.nu_ventavalor / CASE WHEN cont.nu_ventagalon = 0 THEN 1 ELSE cont.nu_ventagalon END),3) AS i_precio
					FROM
						comb_ta_contometros cont
						JOIN comb_ta_combustibles cmb ON (cont.ch_codigocombustible = cmb.ch_codigocombustible)
						JOIN comb_ta_surtidores srt ON (cont.ch_sucursal = srt.ch_sucursal AND cont.ch_surtidor = srt.ch_surtidor)
					WHERE
						1=1 $and_cont
						AND cont.dt_fechaparte BETWEEN to_date('$fechad','DD-MM-YYYY') AND to_date('$fechaa','DD-MM-YYYY')) x
				GROUP BY
					producto, descripcion;";

                $rs4 = pg_exec($q4);

if($action=="exportar"){
         for($i=0;$i<pg_numrows($rs4);$i++){
          $Q4 = pg_fetch_row($rs4,$i);
          $total_res_ven_gal = $total_res_ven_gal + $Q4[2];
          $total_res_ven_val = $total_res_ven_val + $Q4[3];
          $total_res_afe_gal = $total_res_afe_gal + $Q4[4];
          $total_res_afe_val = $total_res_afe_val + $Q4[5];
          $total_res_con_gal = $total_res_con_gal + $Q4[6];
          $total_res_con_val = $total_res_con_val + $Q4[7];
          $total_resumen_gal = $total_resumen_gal + $Q4[8];
          $total_neto_val          = $total_neto_val    + $Q4[9];
          }
        $titulo="PARTE DE MOVIMIENTO DE COMBUSTIBLES DEL: $fechad AL: $fechaa \n SUCURSAL: $cod_almacen";
        $cabecera = "Num. Parte , Cod. Art , Tanque , Manguera , Contometro Inicial (galones) , Contometro Final (galones), Galones Vendidos , Contómetro Inicial (Soles) , Contometro Final (soles) , Soles Vendidos , Afericiones ,Consumo ,Descripción , Fecha ";
        $head[0] = $cabecera;
        $head[1] = "Producto,Descripcion,Galones,Soles,Galones,Soles,Galones,Soles,Galones,Soles";

        $T[0] = $q3;
        $T[1] = $q4;
        $P[1] = ",,RESUMEN DE VENTA ,,RESUMEN DE AFERICIÓN , ,CONSUMO INTERNO, , RESUMEN, NETO ";
        $BA[0] = ",TOTAL,$total_res_ven_gal ,$total_res_ven_val ,$total_res_afe_gal,$total_res_afe_val,$total_res_con_gal,$total_res_con_val,$total_resumen_gal ,$total_neto_val ";
        $TI[0] = $titulo;
        $url = reporteExcel($user,$head,$T,$BA,$P,$TI);

        ?>
        <script language="JavaScript1.3" type="text/javascript">
        window.open('<?php echo $url;?>','miwin','width=10,height=35,scrollbars=yes');
        </script>

        <?php

}

$v_sqlprn ="select par_valor from int_parametros where trim(par_nombre)='print_netbios' ";
$v_xsqlprn=pg_exec( $v_sqlprn);
$v_server =pg_result($v_xsqlprn,0,0);

$v_sqlprn ="select par_valor from int_parametros where trim(par_nombre)='print_name' ";
$v_xsqlprn=pg_exec($v_sqlprn);
$v_printer=pg_result($v_xsqlprn,0,0);

$v_sqlprn ="select par_valor from int_parametros where trim(par_nombre)='print_server' ";
$v_xsqlprn=pg_exec($v_sqlprn);
$v_ipprint=pg_result($v_xsqlprn,0,0);



pg_close();

?>
<html>
<head>
<title>sistemaweb</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script language="JavaScript" type="text/JavaScript">

var miPopup
function abririPopup(){
        miPopup = window.open("prueba.php","miwin","width=500,height=400,scrollbars=yes")
        miPopup.focus()
        }

</script>
</head>

<body>

<form action='cmb_medvarilla-edit.php' method='post' name="form2">
  <table width="767" border="1" cellpadding="0" cellspacing="0">
    <tr>
      <td width="457"><a href="cmb_contometro-reporte.php?action=exportar&fechad=<?php echo $fechad;?>&fechaa=<?php echo $fechaa;?>&titulo=<?php echo $titulo;?>&cod_almacen=<?php echo $cod_almacen;?>" >Exportar
        a Excel</a></td>
      <td><a href="#" onClick="javascript:window.print();">Imprimir</a> </td>
    </tr>
  </table>
</form>

<div align="center"><font size="2" face="Arial, Helvetica, sans-serif">PARTE DE
  MOVIMIENTO DE COMBUSTIBLES DEL:<?php echo $fechad; ?> al <?php echo $fechaa;?></font><br>
  <div align="left"><font size="2" face="Arial, Helvetica, sans-serif">SUCURSAL: <?php echo $cod_almacen;?>
    </font></div>
</div>

<table width="98%" border="1" cellpadding="0" cellspacing="0">
  <tr>
    <td width="32" height="59"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong>Nro
        Parte</strong></font></div></td>
    <td width="32"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong>Cod.
        Art</strong></font></div></td>
    <td width="33"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong>Lado-Tq</strong></font></div></td>
    <td width="44"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong>Manguera</strong></font></div></td>
    <td width="54"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong>Contometro<br>
        Inicial (galones)</strong></font></div></td>
    <td width="54"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong>Contometro<br>
        Final (galones)</strong></font></div></td>
    <td width="46"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong>Galones<br>
        Vendidos </strong></font></div></td>
    <td width="53"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong>Cont&oacute;metro<br>
        Inicial (Soles)</strong></font></div></td>
    <td width="54"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong>Contometro<br>
        Final (Soles)</strong></font></div></td>
    <td width="46"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong>Soles<br>
        Vendidos </strong></font></div></td>
    <td width="50"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong>Afericiones</strong></font></div></td>
    <td width="44"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong>Consumo</strong></font></div></td>
    <td width="44"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong>Descuentos</strong></font></div></td>
    <td width="54"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong>Descripci&oacute;n</strong></font></div></td>
    <td width="105" valign="top"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong><br>
        Fecha</strong></font></div></td>
  </tr>

  <?php
        $col[0]=10;
        $col[1]=13;
        $col[2]=6;
        $col[3]=3;
	$col[4]=11;
	$col[5]=11;
	$col[6]=11;
	$col[7]=11;
	$col[8]=11;
	$col[9]=11;
	$col[10]=7;
	$col[11]=7;
	$col[12]=7;
	$col[13]=11;
	$col[14]=10;

        $nom[0]=str_pad( "Nro Parte" ,$col[0] );
        $nom[1]=str_pad( "Codigo Articulo" ,$col[1] );
        $nom[2]=str_pad( "Lado Tanque" ,$col[2] );
        $nom[3]=str_pad( "Manguera" ,$col[3] );
        $nom[4]=str_pad( "Cont Ini" ,$col[4] );
        $nom[5]=str_pad( "Cont Fin" ,$col[5] );
        $nom[6]=str_pad( "Galo vend" ,$col[6] );
        $nom[7]=str_pad( "Cont Ini" ,$col[7] );
        $nom[8]=str_pad( "Cont Fin" ,$col[8] );
        $nom[9]=str_pad( "Sol vend" ,$col[9] );
        $nom[10]=str_pad( "Afer" ,$col[10] );
        $nom[11]=str_pad( "Consu" ,$col[11] );
	$nom[12]=str_pad( "Descu" ,$col[12] );
        $nom[13]=str_pad( "Descripcion" ,$col[13] );
        $nom[14]=str_pad( "Fecha" ,$col[14] );



	$cabecera="<table>";
	$cabecera=$cabecera."<tr>";
	$cabecera=$cabecera."<td> PARTE DE MOVIMIENTO DE COMBUSTIBLES DEL: ".$fechad." AL: ".$fechaa." </td>";
	$cabecera=$cabecera."</tr>";
	$cabecera=$cabecera."<tr>";
	$cabecera=$cabecera."<td> SUCURSAL ".$cod_almacen."</td>";
	$cabecera=$cabecera."</tr>";

	$cabecera=$cabecera. "<tr><td>".str_pad( "-", $col[0]+$col[1]+$col[2]+$col[3]+$col[4]+$col[5]+$col[6]+$col[7]+$col[8]+$col[9]+$col[10]+$col[11]+$col[12]+$col[13]+$col[14]+14, "-", STR_PAD_LEFT )."</td></tr>";
	$cabecera=$cabecera. "<tr>";
        $cabecera=$cabecera. "<td>".str_pad( "No Parte" ,$col[0] )."</td>";
        $cabecera=$cabecera. "<td>".str_pad( "Cod.Arti" ,$col[1] )."</td>";
        $cabecera=$cabecera. "<td>".str_pad( "Ld-Tq" ,$col[2] )."</td>";
        $cabecera=$cabecera. "<td>".str_pad( "Mg" ,$col[3] )."</td>";
        $cabecera=$cabecera. "<td>".str_pad( "Contometro en Galones" ,$col[4]+$col[5]+1 )."</td>";
        $cabecera=$cabecera. "<td>".str_pad( "Galones" ,$col[6] )."</td>";
        $cabecera=$cabecera. "<td>".str_pad( "Contometro en Soles" ,$col[7]+$col[8]+1 )."</td>";
        $cabecera=$cabecera. "<td>".str_pad( "Soles" ,$col[9] )."</td>";
        $cabecera=$cabecera. "<td>".str_pad( "Aferic" ,$col[10] )."</td>";
        $cabecera=$cabecera. "<td>".str_pad( "Consumo" ,$col[11] )."</td>";
        $cabecera=$cabecera. "<td>".str_pad( "Descuent" ,$col[12] )."</td>";
        $cabecera=$cabecera. "<td>".str_pad( "Descripcion" ,$col[13] )."</td>";
        $cabecera=$cabecera. "<td>".str_pad( "Fecha" ,$col[14] )."</td>";
        $cabecera=$cabecera. "</tr>";
	$cabecera=$cabecera. "<tr>";
        $cabecera=$cabecera. "<td>".str_pad( " " ,$col[0] )."</td>";
        $cabecera=$cabecera. "<td>".str_pad( " " ,$col[1] )."</td>";
        $cabecera=$cabecera. "<td>".str_pad( " " ,$col[2] )."</td>";
        $cabecera=$cabecera. "<td>".str_pad( " " ,$col[3] )."</td>";
	$cabecera=$cabecera. "<td>".str_pad( "Inicial " ,$col[4] )."</td>";
	$cabecera=$cabecera. "<td>".str_pad( "Final " ,$col[5] )."</td>";
        $cabecera=$cabecera. "<td>".str_pad( "Vendidos" ,$col[6] )."</td>";
        $cabecera=$cabecera. "<td>".str_pad( "Inicial" ,$col[7] )."</td>";
        $cabecera=$cabecera. "<td>".str_pad( "Final" ,$col[8] )."</td>";
        $cabecera=$cabecera. "<td>".str_pad( "Vendidos" ,$col[9] )."</td>";
        $cabecera=$cabecera. "<td>".str_pad( " " ,$col[10] )."</td>";
        $cabecera=$cabecera. "<td>".str_pad( " " ,$col[11] )."</td>";
        $cabecera=$cabecera. "<td>".str_pad( " " ,$col[12] )."</td>";
        $cabecera=$cabecera. "<td>".str_pad( " " ,$col[13] )."</td>";
        $cabecera=$cabecera. "<td>".str_pad( " " ,$col[14] )."</td>";
        $cabecera=$cabecera. "</tr>";
	$cabecera=$cabecera. "<tr><td>".str_pad( "-", $col[0]+$col[1]+$col[2]+$col[3]+$col[4]+$col[5]+$col[6]+$col[7]+$col[8]+$col[9]+$col[10]+$col[11]+$col[12]+$col[13]+$col[14]+14, "-", STR_PAD_LEFT )."</td></tr>";

	$linea="";
//echo 'NO SALE NADA';

    for($i=0;$i<pg_numrows($rs3);$i++){
	$E = pg_fetch_row($rs3,$i);
//print $E;
	print '
	<tr>
	<td height="21"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif">'.$E[0].'</font></div></td>
	<td><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif">'.$E[1].'</font></div></td>
	<td><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif">'.$E[16].' - '.$E[2].'</font></div></td>
	<td><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif">'.$E[3].'</font></div></td>
	<td><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif">'.$E[4].'</font></div></td>
	<td><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif">'.$E[5].'</font></div></td>
	<td><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif">'.$E[6].'</font></div></td>
	<td><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif">'.$E[7].'</font></div></td>
	<td><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif">'.$E[8].'</font></div></td>
	<td><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif">'.$E[9].'</font></div></td>
	<td><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif">'.$E[10].'</font></div></td>
	<td><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif">'.$E[11].'</font></div></td>
	<td><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif">'.$E[12].'</font></div></td>
	<td><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif">'.$E[13].'</font></div></td>
	<td width="105" valign="top"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif">'.$E[14].'   '.$E[15].'</font></div></td>
	</tr>
	  ';
	$linea=$linea. "<tr>";
	$linea=$linea. "<td>".str_pad( $E[0] ,$col[0] )."</td>";
	$linea=$linea. "<td>".str_pad( $E[1] ,$col[1] )."</td>";
	$linea=$linea. "<td>".str_pad( $E[16].'-'.$E[2] ,$col[2] )."</td>";
	$linea=$linea. "<td>".str_pad( $E[3] ,$col[3] )."</td>";
	$linea=$linea. "<td>".str_pad( number_format($E[4], 2, '.', ''), $col[4], " ", STR_PAD_LEFT )."</td>";
	$linea=$linea. "<td>".str_pad( number_format($E[5], 2, '.', ''), $col[5], " ", STR_PAD_LEFT )."</td>";
	$linea=$linea. "<td>".str_pad( number_format($E[6], 2, '.', ''), $col[6], " ", STR_PAD_LEFT )."</td>";
	$linea=$linea. "<td>".str_pad( number_format($E[7], 2, '.', ''), $col[7], " ", STR_PAD_LEFT )."</td>";
	$linea=$linea. "<td>".str_pad( number_format($E[8], 2, '.', ''), $col[8], " ", STR_PAD_LEFT )."</td>";
	$linea=$linea. "<td>".str_pad( number_format($E[9], 2, '.', ''), $col[9], " ", STR_PAD_LEFT )."</td>";
	$linea=$linea. "<td>".str_pad( $E[10] ,$col[10] )."</td>";
	$linea=$linea. "<td>".str_pad( $E[11] ,$col[11] )."</td>";
	$linea=$linea. "<td>".str_pad( $E[12] ,$col[12] )."</td>";
	$linea=$linea. "<td>".str_pad( substr(trim($E[13]),0,$col[13]-1) ,$col[13] )."</td>";
	$linea=$linea. "<td>".str_pad( $E[14] ,$col[14] )."</td>";
	$linea=$linea. "</tr>";
	}
	$linea=$linea. "<tr><td>".str_pad( "=", $col[0]+$col[1]+$col[2]+$col[3]+$col[4]+$col[5]+$col[6]+$col[7]+$col[8]+$col[9]+$col[10]+$col[11]+$col[12]+$col[13]+$col[14]+14, "=", STR_PAD_LEFT )."</td></tr>";
	$linea=$linea."</table>";
        imprimir2( $cabecera.$linea, $col, $nom, "/tmp/imprimir/cmb_venta.txt", "Venta Combustible" );
        exec("smbclient //".$v_server."/".$v_printer." -c 'print /tmp/imprimir/cmb_venta.txt' -N -I ".$v_ipprint." ");

	  ?>
</table>

<br>
<table width="763" border="1" cellpadding="0" cellspacing="0">
  <tr>
    <td width="37"><div align="center"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font size="-7"></font></font></font></div></td>
    <td width="48"><div align="center"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font size="-7"></font></font></font></div></td>
    <td colspan="2"><div align="center"></div>
      <div align="center"><font size="-7" face="Arial, Helvetica, sans-serif"><strong>RESUMEN
        DE VENTA</strong></font></div></td>
    <td colspan="2"><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif"><strong>RESUMEN
        DE AFERICI&Oacute;N</strong></font></div>
      <div align="center"></div></td>
    <td colspan="2"> <div align="center"><font size="-7" face="Arial, Helvetica, sans-serif"><strong>CONSUMO
        INTERNO</strong></font></div>
      <div align="center"></div></td>
    <td> <div align="center"><font size="-7" face="Arial, Helvetica, sans-serif"><strong>DESCUENTOS</td>
    <td width="84"><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif"><strong>RESUMEN</strong></font></div></td>
    <td width="82"><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif"><strong>NETO</strong></font></div></td>
  </tr>
  <tr>
    <td><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif"><em>Producto</em></font></div></td>
    <td width="83"><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif"><em>Descripci&oacute;n</em></font></div></td>
    <td width="83"><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif"><em>Galones</em></font></div></td>
    <td width="83"><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif"><em>Soles</em></font></div></td>
    <td width="79"><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif"><em>Galones</em></font></div></td>
    <td width="79"><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif"><em>Soles</em></font></div></td>
    <td width="83"><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif"><em>Galones
        </em></font></div></td>
    <td width="83"><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif"><em>Soles</em></font></div></td>
    <td width="83"><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif"><em>Soles</em></font></div></td>
    <td><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif"><em>Galones</em></font></div></td>
    <td><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif"><em>Soles</em></font></div></td>
  </tr>

<?php
$col[0]=13;
$col[1]=11;
$col[2]=11;
$col[3]=11;
$col[4]=11;
$col[5]=11;
$col[6]=11;
$col[7]=11;
$col[8]=11;
$col[9]=11;
$col[10]=11;

$nom[0]=str_pad( "Producto" ,$col[0] );
$nom[1]=str_pad( "Descripcion" ,$col[1] );
$nom[2]=str_pad( "Galones" ,$col[2] );
$nom[3]=str_pad( "Soles" ,$col[3] );
$nom[4]=str_pad( "Galones" ,$col[4] );
$nom[5]=str_pad( "Soles" ,$col[5] );
$nom[6]=str_pad( "Galones" ,$col[6] );
$nom[7]=str_pad( "Soles" ,$col[7] );
$nom[8]=str_pad( "Soles" ,$col[8] );
$nom[9]=str_pad( "Galones" ,$col[9] );
$nom[10]=str_pad( "Soles" ,$col[10] );




$cabecera="<table>";
$cabecera=$cabecera. "<tr><td>".str_pad( "-", $col[0]+$col[1]+$col[2]+$col[3]+$col[4]+$col[5]+$col[6]+$col[7]+$col[8]+$col[9]+$col[10]+10, "-", STR_PAD_LEFT )."</td></tr>";
$cabecera=$cabecera. "<tr>";
$cabecera=$cabecera. "<td>".str_pad( "Producto" ,$col[0] )."</td>";
$cabecera=$cabecera. "<td>".str_pad( "Descripcion" ,$col[1] )."</td>";
$cabecera=$cabecera. "<td>".str_pad( "RESUMEN DE VENTA" ,$col[2]+$col[3]+1 )."</td>";
$cabecera=$cabecera. "<td>".str_pad( "RESUMEN DE AFERI" ,$col[4]+$col[5]+1 )."</td>";
$cabecera=$cabecera. "<td>".str_pad( "CONSUMO INTERNO" ,$col[6]+$col[7]+1 )."</td>";
$cabecera=$cabecera. "<td>".str_pad( "DESCUENTOS" ,$col[8] )."</td>";
$cabecera=$cabecera. "<td>".str_pad( "RESUMEN NETO" ,$col[9]+$col[10]+1 )."</td>";
$cabecera=$cabecera. "</tr>";
$cabecera=$cabecera. "<tr>";
$cabecera=$cabecera. "<td>".str_pad( " " ,$col[0] )."</td>";
$cabecera=$cabecera. "<td>".str_pad( " " ,$col[1] )."</td>";
$cabecera=$cabecera. "<td>".str_pad( "Galones" ,$col[2] )."</td>";
$cabecera=$cabecera. "<td>".str_pad( "Soles" ,$col[3] )."</td>";
$cabecera=$cabecera. "<td>".str_pad( "Galones" ,$col[4] )."</td>";
$cabecera=$cabecera. "<td>".str_pad( "Soles" ,$col[5] )."</td>";
$cabecera=$cabecera. "<td>".str_pad( "Galones" ,$col[6] )."</td>";
$cabecera=$cabecera. "<td>".str_pad( "Soles" ,$col[7] )."</td>";
$cabecera=$cabecera. "<td>".str_pad( "Soles" ,$col[8] )."</td>";
$cabecera=$cabecera. "<td>".str_pad( "Galones" ,$col[9] )."</td>";
$cabecera=$cabecera. "<td>".str_pad( "Soles" ,$col[10] )."</td>";
$cabecera=$cabecera. "</tr>";
$cabecera=$cabecera. "<tr><td>".str_pad( "-", $col[0]+$col[1]+$col[2]+$col[3]+$col[4]+$col[5]+$col[6]+$col[7]+$col[8]+$col[9]+$col[10]+10, "-", STR_PAD_LEFT )."</td></tr>";
$linea="";


for($i=0;$i<pg_numrows($rs4);$i++)
	{
	$Q4 = pg_fetch_row($rs4,$i);
	$total_res_ven_gal    = $total_res_ven_gal + $Q4[2];
	$total_res_ven_val    = $total_res_ven_val + $Q4[3];
	$total_res_afe_gal    = $total_res_afe_gal + $Q4[4];
	$total_res_afe_val    = $total_res_afe_val + $Q4[5];
	$total_res_con_gal    = $total_res_con_gal + $Q4[6];
	$total_res_con_val    = $total_res_con_val + $Q4[7];
	$total_res_descuentos = $total_res_descuentos + $Q4[8];	
	$total_resumen_gal    = $total_resumen_gal + $Q4[9];
	$total_neto_val       = $total_neto_val    + $Q4[10];
	print '
	<tr>
	<td><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif">'.$Q4[0].'</font></div></td>
	<td><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif">'.$Q4[1].'</font></div></td>
	<td><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif">'.$Q4[2].'</font></div></td>
	<td><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif">'.$Q4[3].'</font></div></td>
	<td><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif">'.$Q4[4].'</font></div></td>
	<td><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif">'.$Q4[5].'</font></div></td>
	<td><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif">'.$Q4[6].'</font></div></td>
	<td><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif">'.$Q4[7].'</font></div></td>
	<td><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif">'.$Q4[8].'</font></div></td>
	<td><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif">'.$Q4[9].'</font></div></td>
	<td><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif">'.$Q4[10].'</font></div></td>
	</tr>
	';
	$linea=$linea. "<tr>";
	$linea=$linea. "<td>".str_pad( $Q4[0] ,$col[0] )."</td>";
	$linea=$linea. "<td>".str_pad( substr(trim($Q4[1]),0,$col[1]-1) ,$col[1] )."</td>";
	$linea=$linea. "<td>".str_pad( number_format($Q4[2], 2, '.', ''), $col[2], " ", STR_PAD_LEFT )."</td>";
	$linea=$linea. "<td>".str_pad( number_format($Q4[3], 2, '.', ''), $col[3], " ", STR_PAD_LEFT )."</td>";
	$linea=$linea. "<td>".str_pad( number_format($Q4[4], 2, '.', ''), $col[4], " ", STR_PAD_LEFT )."</td>";
	$linea=$linea. "<td>".str_pad( number_format($Q4[5], 2, '.', ''), $col[5], " ", STR_PAD_LEFT )."</td>";
	$linea=$linea. "<td>".str_pad( number_format($Q4[6], 2, '.', ''), $col[6], " ", STR_PAD_LEFT )."</td>";
	$linea=$linea. "<td>".str_pad( number_format($Q4[7], 2, '.', ''), $col[7], " ", STR_PAD_LEFT )."</td>";
	$linea=$linea. "<td>".str_pad( number_format($Q4[8], 2, '.', ''), $col[8], " ", STR_PAD_LEFT )."</td>";
	$linea=$linea. "<td>".str_pad( number_format($Q4[9], 2, '.', ''), $col[9], " ", STR_PAD_LEFT )."</td>";
	$linea=$linea. "<td>".str_pad( number_format($Q4[10], 2, '.', ''), $col[10], " ", STR_PAD_LEFT )."</td>";
	$linea=$linea. "</tr>";
	}

$linea=$linea. "<tr><td>".str_pad( "-", $col[0]+$col[1]+$col[2]+$col[3]+$col[4]+$col[5]+$col[6]+$col[7]+$col[8]+$col[9]+$col[10]+10, "-", STR_PAD_LEFT )."</td></tr>";

print '
<tr>
<td><div align="center"><font size="2"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font size="-7"></font></font></font></font></div></td>
<td><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif">TOTAL :</font></div></td>
<td><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif">'.$total_res_ven_gal.'</font></div></td>
<td><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif">'.$total_res_ven_val.'</font></div></td>
<td><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif">'.$total_res_afe_gal.'</font></div></td>
<td><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif">'.$total_res_afe_val.'</font></div></td>
<td><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif">'.$total_res_con_gal.'</font></div></td>
<td><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif">'.$total_res_con_val.'</font></div></td>
<td><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif">'.$total_res_descuentos.'</font></div></td>
<td><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif">'.$total_resumen_gal.'</font></div></td>
<td><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif">'.$total_neto_val.'</font></div></td>
</tr>
  ';
$linea=$linea. "<tr>";
$linea=$linea. "<td>".str_pad( " " ,$col[0] )."</td>";
$linea=$linea. "<td>".str_pad( "TOTAL:" ,$col[1] )."</td>";
$linea=$linea. "<td>".str_pad( number_format($total_res_ven_gal, 2, '.', ''), $col[2], " ", STR_PAD_LEFT )."</td>";
$linea=$linea. "<td>".str_pad( number_format($total_res_ven_val, 2, '.', ''), $col[3], " ", STR_PAD_LEFT )."</td>";
$linea=$linea. "<td>".str_pad( number_format($total_res_afe_gal, 2, '.', ''), $col[4], " ", STR_PAD_LEFT )."</td>";
$linea=$linea. "<td>".str_pad( number_format($total_res_afe_val, 2, '.', ''), $col[5], " ", STR_PAD_LEFT )."</td>";
$linea=$linea. "<td>".str_pad( number_format($total_res_con_gal, 2, '.', ''), $col[6], " ", STR_PAD_LEFT )."</td>";
$linea=$linea. "<td>".str_pad( number_format($total_res_con_val, 2, '.', ''), $col[7], " ", STR_PAD_LEFT )."</td>";
$linea=$linea. "<td>".str_pad( number_format($total_res_descuentos, 2, '.', ''), $col[8], " ", STR_PAD_LEFT )."</td>";
$linea=$linea. "<td>".str_pad( number_format($total_resumen_gal, 2, '.', ''), $col[9], " ", STR_PAD_LEFT )."</td>";
$linea=$linea. "<td>".str_pad( number_format($total_neto_val, 2, '.', ''), $col[10], " ", STR_PAD_LEFT )."</td>";
$linea=$linea. "</tr>";
$linea=$linea. "<tr><td>".str_pad( "=", $col[0]+$col[1]+$col[2]+$col[3]+$col[4]+$col[5]+$col[6]+$col[7]+$col[8]+$col[9]+$col[10]+10, "=", STR_PAD_LEFT )."</td></tr>";

$linea=$linea."</table>";
imprimir2( $cabecera.$linea, $col, $nom, "/tmp/imprimir/cmb_venta_2.txt", "Venta Combustible" );
exec("smbclient //".$v_server."/".$v_printer." -c 'print /tmp/imprimir/cmb_venta_2.txt' -N -I ".$v_ipprint." ");


?>

</table>
<p>&nbsp;</p>
</body>
</html>
