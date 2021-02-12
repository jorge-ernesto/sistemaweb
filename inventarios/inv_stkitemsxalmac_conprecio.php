<?php

include("../menu_princ.php");
include("../functions.php");
include("/sistemaweb/utils/funcion-texto.php");
include("../funcjch.php");
require("../clases/funciones.php");

$nuevofechad = split('/',$_REQUEST['diasd']);
$diad = $nuevofechad[0];
$mesd = $nuevofechad[1];
$anod = $nuevofechad[2];

$funcion = new class_funciones;
$clase_error = new OpensoftError;
$clase_error->_error();
$conector_id = $funcion->conectar("","","","","");

if($flg == "A") {
	$soloconstk = "S";
	$diad = date("d"); 
	$mesd = date("m"); 
	$anod = date("Y");
}

if ($boton == 'Buscar') {
        $v_sqlx   = "select par_valor from int_parametros where trim(par_nombre)='print_netbios' ";
        $v_xsqlx  = pg_exec( $v_sqlx);
        $v_server = pg_result($v_xsqlx,0,0);

        $v_sqlx   = "select par_valor from int_parametros where trim(par_nombre)='print_name' ";
        $v_xsqlx  = pg_exec($v_sqlx);
        $v_printer= pg_result($v_xsqlx,0,0);

        $v_sqlx   = "select par_valor from int_parametros where trim(par_nombre)='print_server' ";
        $v_xsqlx  = pg_exec($v_sqlx);
        $v_ipprint= pg_result($v_xsqlx,0,0);

        $v_archivo = "/tmp/imprimir/reporte_de_stock.txt";

        $sql1 = "select distinct trim(documento) from ".$tabla." where documento is not null";
        $result = pg_exec($conector_id, $sql1);
        $n_f_codigos = pg_numrows($result);
}

//if ($_REQUEST['boton']=="excel") {
	/*ob_end_clean();
	//$buff = "Tipo Trans,Num Tarj,Tipo Tarj,Hora Trans,Imp Trans,Num Trans,Fecha Tran,Hora Tran,Imp Tarj\n";
	//$mi_pdf = "/sistemaweb/inventarios/stkitemsxalmac.csv";
	header("Content-type: application/csv");
	header('Content-Disposition: attachment; filename="'."stkitemsxalmac.csv".'"');
	die($snewbuffer);
	//readfile($mi_pdf);*/
				//ob_end_clean();
				/*$mi_pdf = "/sistemaweb/inventarios/stkitemsxalmac.csv";
				header('Content-type: application/csv');
				header('Content-Disposition: attachment; filename="'."stkitemsxalmac.csv".'"');
				readfile($mi_pdf);*/
				//die($mi_pdf);
//}
//ob_end_flush();
?>

<html><link rel="stylesheet" href="<?php echo $v_path_url; ?>sistemaweb.css" type="text/css">
<head>
<title>integrado</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<script language="javascript">
	var miPopup
	function abrelinea(){
	    miPopup = window.open("escogelinea.php","miwin","width=500,height=400,scrollbars=yes")
	    miPopup.focus()
	}

	function descargar(){
	    location.href="/sistemaweb/inventarios/stkitemsxalmac.csv";
	}

	/*function abrealma(){
	    miPopup = window.open("almac.php","miwin","width=500,height=400,scrollbars=yes")
	    miPopup.focus()
	}*/

	function abrealma(){
	    miPopup = window.open("helpalma.php","miwin","width=500,height=400,scrollbars=yes")
	    miPopup.focus()
	}

	function enviadatos(){
	    document.formular.submit()
	}
</script>
</head>

<body>
STOCKS VALORIZADOS POR ALMACEN - LINEAS
<?php  
$lin = " "; 
$lin = $lin."                                 STOCKS VALORIZADOS POR ALMACEN - LINEAS	            <br> ";
?>

<hr noshade="noshade"/>
<script src="/sistemaweb/js/calendario.js" type="text/javascript"></script>
<script src="/sistemaweb/js/overlib_mini.js" type="text/javascript"></script>
<form name='formular' action="inv_stkitemsxalmac.php" method="post">
	<table border="1" cellspacing="0" cellpadding="0">
    		<tr>
      			<td>Fecha</td>
      			<td colspan="2">
				<input type="text" name="diasd" size="10" value="<?php echo $diad.'/'.$mesd.'/'.$anod ?>" readonly="true"/>
				&nbsp;
				<a href="javascript:show_calendar('formular.diasd');">
		   			<img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/>
				</a>
				<div id="overDiv" style="position:absolute; visibility:hidden; z-index:0;">
      			</td>
    		</tr>
    		<tr>
			<td>C&oacute;digo de Almac&eacute;n:</td>
      			<td colspan="2">
        			<?php
				if(strlen($txtalma)>0){
					$sqlao = "select tab_elemento,tab_descripcion from int_tabla_general where tab_tabla='ALMA' and tab_elemento like '%".$txtalma."%' ";
					$xsqlao = pg_exec($coneccion,$sqlao);
					$ilimitao = pg_numrows($xsqlao);
					if($ilimitao > 0) {
						$txtalma = pg_result($xsqlao,0,0);
					    	$descao = pg_result($xsqlao,0,1);
					}
				} else {
				     	$descao = "TODOS LOS ALMACENES";
				}
				?>
        			<input type="text" name="txtalma" size="10" value="<?php echo $txtalma;?>">
        			<input type="submit" name="boton" value="Ok"> <input name="imgalmac0" type="image" src="/sistemaweb/images/help.gif" width="16" height="15" border="0" onClick="abrealma()">
        			<?php echo $descao; ?>
			</td>
    		</tr>
    		<tr>
      			<td>C&oacute;digo de L&iacute;nea:</td>
      			<td colspan="2">
				<?php
				if(strlen($linea) > 0) {
					$sqllin    = "select tab_elemento,tab_descripcion,tab_car_03 from int_tabla_general where tab_tabla='20' and (tab_elemento like ';".$linea.";' or tab_descripcion like ';".$linea.";')";
				  	$xsqllin   = pg_exec($coneccion,$sqllin);
				  	$ilimitlin = pg_numrows($xsqllin);

					if($ilimitlin > 0) {
						$linea     = pg_result($xsqllin,0,0);
					    	$desclinea = pg_result($xsqllin,0,1);
					    	if($linea  == "000000") 	
							$linea = ""; 
					    	$flglinea  = pg_result($xsqllin,0,2); 
				  	}
				} else {
					$desclinea = "Todas las l�neas"; 
				} 
				?>
        			<input name='linea' type='text' value='<?php echo $linea;?>' size='10' maxlength='6'>
        			<input name="imglinea" type="image" src="/sistemaweb/images/help.gif" width="16" height="15" border="0" onclick="abrelinea()">
        			<?php echo $desclinea; ?>
			</td>
    		</tr>
    		<tr>
			<td>Stock Positivo  :</td>
			<td colspan="2"><input type='checkbox' name='p_stock' value='P'></td>
			<br><?php echo $s_stock; ?></br> 
    		</tr>
    		<tr>
			<td>Stock Cero  :</td>	
			<td colspan="2"><input type='checkbox' name='c_stock' value='C'></td>
    		</tr>
    		<tr>
			<td>Stock Negativo :</td>
			<td colspan="2"><input type='checkbox' name='n_stock' value='N'></td>
    		</tr>
    		<tr>
			<td>Datos de Utilidad :</td>
			<td colspan="2"><input type='checkbox' name='n_utilidad' value='U' checked></td>
    		</tr>
    		<tr>
			<td align="center">&nbsp;</td>
      			<!--<td align="center"><input name="boton" type="submit" value="Buscar"></td>-->
			<td align="center"><button type="submit" name="boton" value="Buscar"><img src="/sistemaweb/images/search.png" alt="left" />  Buscar</button></td>
			<td align="center"><button type="button" name="boton" value="excel" OnClick="location.href='/sistemaweb/inventarios/stkitemsxalmac.csv';"><img src="/sistemaweb/images/excel_icon.png" alt="left" />  Excel</button></td>
    		</tr>
    		<!--<tr>
			<td align="center">&nbsp;</td>
			<td align="center" colspan="2">
				<button type="button" name="boton" value="excel" OnClick="location.href='/sistemaweb/inventarios/stkitemsxalmac.csv';"><img src="/sistemaweb/images/excel_icon.png" alt="left" />  Excel</button>
			</td>
			<td align="center">
				<a href="#" onClick="javascript:window.open('/sistemaweb/clases/imprime_samba.php?v_server=<?php echo $v_server; ?>&v_printer=<?php echo $v_printer; ?>&v_ipprint=<?php echo $v_ipprint; ?>&v_archivo=<?php echo $v_archivo; ?>','miwin','width=800,height=450,scrollbars=yes,menubar=yes,left=0,top=0');"> Impresion Reporte </a>
			</td>
    		</tr>-->
	</table>
  	<p>&nbsp;</p>
  	<table border="1" cellspacing="0" cellpadding="0">
    		<tr>
			<th>CODIGO</th>
		        <th>ARTICULO</th>
		        <th>UNID</th>
		        <th>STK TOTAL</th>
		        <th>COSTO</th>
		        <th>VALOR TOTAL</th>
			<?php if($n_utilidad=="U") { ?>
				<th>PRECIO</th>
				<th>MARGEN</th>
				<th>VAL. MARGEN</th>
				<th>IGV</th>
				<th>TOTAL</th>
			<?php } ?>
    		</tr>
		<?php
		$alma_sql = "SELECT ch_almacen, ch_nombre_almacen FROM inv_ta_almacenes WHERE ch_almacen='".$txtalma."' AND ch_clase_almacen='1' ";
		$alma_sql_r = pg_exec($conector_id,$alma_sql);
		if(pg_numrows($alma_sql_r)>0){
			$v_codalma=pg_result($alma_sql_r,0,0);
			$v_descalma=pg_result($alma_sql_r,0,1);
		}	
                $lin=$lin."ALMACEN: ".$v_codalma." -- ".$v_descalma."<br> ";	
		$lin=$lin."Fecha: ".$diad." / ".$mesd." / ".$anod." <br>";

		$col[0] = 15;
		$col[1] = 35;
		$col[2] = 6;
		$col[3] = 7;
		$col[4] = 7;
		$col[5] = 7;

		$nom[0] = "    CODIGO   ";
		$nom[1] = "           ARTICULO            ";
		$nom[2] = "UNID ";
		$nom[3] =  "STK TOTAL";
		$nom[4] =  "    COSTO  ";
		$nom[5] =  " VALOR TOTAL";

		$lin = $lin."-------------------------------------------------------------------------------------------------- ";
		$lin = $lin."<table><tr>";
		$lin = $lin."<td>".str_pad($nom[0], $col[0])."</td>";
		$lin = $lin."<td>".str_pad($nom[1],$col[1])."</td>";
		$lin = $lin." <td>".str_pad($nom[2], $col[2])."</td>";
		$lin = $lin."<td>".str_pad($nom[3], $col[3])."</td>";
		$lin = $lin."<td>".str_pad($nom[4], $col[4])."</td>";
		$lin = $lin."<td>".str_pad($nom[5], $col[5])."</td>";
		$lin = $lin."<br>";
		$lin = $lin."-------------------------------------------------------------------------------------------------- ";
		$lin = $lin."<br>";

 		$ft = fopen('stkitemsxalmac.csv','w');
		if ($ft > 0) {
			$snewbuffer = " STOCKS VALORIZADOS POR ALMACEN - LINEAS \n\n";
			$snewbuffer = $snewbuffer."CODART,DESC. ARTICULO,UNID,STK TOTAL,COSTO,VALOR TOTAL ";
			if($n_utilidad == "U") 
				$snewbuffer = $snewbuffer.",PRECIO,MARGEN,VAL.MARGEN,IGV,TOTAL ";
			$snewbuffer = $snewbuffer." \n";
		}

		$sqladd = "";

		if($p_stock == "P") { 
			$sqladd = " and (s.stk_stock".$mesd." > '0')"; 
		} 
		if($c_stock=="C") { 
			$sqladd= "and (s.stk_stock".$mesd."= '0')"; 
		}
		if($n_stock=="N") { 
			$sqladd = "and (s.stk_stock".$mesd." < '0')"; 
		}
		if($p_stock == "P" && $c_stock == "C") { 
			$sqladd = "and (s.stk_stock".$mesd." >= '0' ) ";  
		}
		if($p_stock == "P" && $n_stock == "N") { 
			$sqladd = "and (s.stk_stock".$mesd." > '0' or s.stk_stock".$mesd." < '0')" ; 
		}
		if($c_stock == "C" && $n_stock == "N") { 
			$sqladd = "and (s.stk_stock".$mesd." <=' 0') "; 
		}
		if($p_stock == "P" && $c_stock == "C" && $n_stock=="N") { 
			$sqladd= "and (s.stk_stock".$mesd." >= '0' or s.stk_stock".$mesd."<= '0') " ; 
		}

		if (isset($txtalma)&&$txtalma!='') { 
			$sqladd .= " and (s.stk_almacen='".$txtalma."')"; 
		}

		$linea = substr($linea,4,2);

		if($n_utilidad == "U") { 
			$sql = "SELECT
					ar.art_codigo,
					trim(ar.art_descripcion) as descripcion,
					substring(trim(ar.art_unidad) from 4 for char_length(trim(ar.art_unidad))) as unidad,
					s.stk_stock".$mesd.",s.stk_costo".$mesd.",
					(s.stk_stock".$mesd."*s.stk_costo".$mesd.") as subtot,
					ar.art_linea,
					s.stk_almacen,
					a.ch_nombre_almacen,
					case when s.art_codigo in (SELECT ch_codigocombustible FROM comb_ta_combustibles) then 1 else 2 End as tipo, 

					case when s.art_codigo in (SELECT ch_codigocombustible FROM comb_ta_combustibles) then 
					case WHEN s.stk_costo".$mesd." > 0 THEN 
					round(100*((com.nu_preciocombustible / (1 + util_fn_igv()/100) / s.stk_costo".$mesd.") - 1),0)
					Else '0' End 
					else 
					case WHEN s.stk_costo".$mesd." > 0 THEN
					round(100*((p.pre_precio_act1 / (1 + util_fn_igv()/100) / s.stk_costo".$mesd.") - 1),0) 
					Else '0' End 
					End as margen, 

					case when s.art_codigo in (SELECT ch_codigocombustible FROM comb_ta_combustibles) then 
					round(round(com.nu_preciocombustible * s.stk_stock".$mesd.",4)/ (1 + util_fn_igv()/100),4)
					else 
					round(round(p.pre_precio_act1 * s.stk_stock".$mesd.",4)/ (1 + util_fn_igv()/100),4)
					End as valor_margen, 

					case when s.art_codigo in (SELECT ch_codigocombustible FROM comb_ta_combustibles) then 
					round(com.nu_preciocombustible * s.stk_stock".$mesd.",4) - round(round(com.nu_preciocombustible * s.stk_stock".$mesd.",4)/ (1 + util_fn_igv()/100),4)
					else 
					round(p.pre_precio_act1 * s.stk_stock".$mesd.",4) - round(round(p.pre_precio_act1 * s.stk_stock".$mesd.",4)/ (1 + util_fn_igv()/100),4)
					End as igv, 

					case when s.art_codigo in (SELECT ch_codigocombustible FROM comb_ta_combustibles) then 
					round(com.nu_preciocombustible * s.stk_stock".$mesd.",4)
					else 
					round(p.pre_precio_act1 * s.stk_stock".$mesd.",4)
					End as total,

					p.pre_precio_act1  

				FROM 
					inv_ta_almacenes a, 
					inv_saldoalma s,
					int_articulos ar 
					LEFT JOIN fac_lista_precios p ON p.art_codigo = ar.art_codigo
					LEFT JOIN comb_ta_combustibles com ON com.ch_codigocombustible = ar.art_codigo 
				WHERE
					(art_linea like '%".$linea."') and 
					(a.ch_clase_almacen='1') and (trim(s.stk_almacen)=trim(a.ch_almacen)) and (s.stk_periodo='".$anod."') 
					and (s.art_codigo=ar.art_codigo) 
					".$sqladd."
					and ar.art_plutipo!='2' and ar.art_plutipo!='3' 
				ORDER BY
					s.stk_almacen,
					tipo desc,
					ar.art_linea,
					s.art_codigo;";
		} else {
			$sql = "SELECT
					ar.art_codigo,
					trim(ar.art_descripcion) as descripcion,
					substring(trim(ar.art_unidad) from 4 for char_length(trim(ar.art_unidad))) as unidad,
					s.stk_stock".$mesd.",s.stk_costo".$mesd.",
					(s.stk_stock".$mesd."*s.stk_costo".$mesd.") as subtot,
					ar.art_linea,
					s.stk_almacen,
					a.ch_nombre_almacen,
					case when s.art_codigo in (SELECT ch_codigocombustible FROM comb_ta_combustibles) then 1 else 2 End as tipo 
				FROM 
					inv_ta_almacenes a, 
					inv_saldoalma s,
					int_articulos ar 
				WHERE
					(art_linea like '%".$linea."') and
					(a.ch_clase_almacen='1') and (trim(s.stk_almacen)=trim(a.ch_almacen)) and (s.stk_periodo='".$anod."') 
					and (s.art_codigo=ar.art_codigo)
					".$sqladd."
					and ar.art_plutipo!='2' and ar.art_plutipo!='3'
				ORDER BY
					s.stk_almacen,
					tipo desc,
					ar.art_linea,
					s.art_codigo;";
		}
		//echo $sql;
		$xsql	= pg_exec($coneccion,$sql);
		$ilimit	= pg_numrows($xsql);
		$xalma	= "";
		$xlinea	= "";
		$irow 	= 0;
		$pinto_market = 0;

		while($irow <= $ilimit) {
			$a0 = pg_result($xsql,$irow,0);
			$a1 = pg_result($xsql,$irow,1);
			$a2 = pg_result($xsql,$irow,2);
			$a3 = pg_result($xsql,$irow,3);
			$a4 = pg_result($xsql,$irow,4);
			$a5 = pg_result($xsql,$irow,5);
			$a6 = pg_result($xsql,$irow,6);
			$a7 = pg_result($xsql,$irow,7);
			$a8 = pg_result($xsql,$irow,8);
			$a9 = pg_result($xsql,$irow,9);

			$tot_lin[$a6]  = $tot_lin[$a6]  + $a5;
			$tot_tipo[$a9] = $tot_tipo[$a9] + $a5;
			$tot_alma[$a7] = $tot_alma[$a7] + $a5;

			if($n_utilidad == "U") { 
				$a10 = pg_result($xsql,$irow,10);
				$a11 = pg_result($xsql,$irow,11);
				$a12 = pg_result($xsql,$irow,12);
				$a13 = pg_result($xsql,$irow,13);
				$a14 = pg_result($xsql,$irow,14);

				$tot_lin_margen[$a6]=$tot_lin_margen[$a6]+$a11;
				$tot_lin_igv[$a6]=$tot_lin_igv[$a6]+$a12;
				$tot_lin_total[$a6]=$tot_lin_total[$a6]+$a13;

				$tot_tipo_margen[$a9]=$tot_tipo_margen[$a9]+$a11;
				$tot_tipo_igv[$a9]=$tot_tipo_igv[$a9]+$a12;
				$tot_tipo_total[$a9]=$tot_tipo_total[$a9]+$a13;

				$tot_alma_margen[$a7]=$tot_alma_margen[$a7]+$a11;
				$tot_alma_igv[$a7]=$tot_alma_igv[$a7]+$a12;
				$tot_alma_total[$a7]=$tot_alma_total[$a7]+$a13;
			}

			if(number_format($a10,2) <= 0 && $irow!=$ilimit && $n_utilidad=="U") {
				$color_negativo = " bgcolor='yellow' ";
			} else {
				$color_negativo = " ";
			}
			if($xalma!=$a7 && $irow!=$ilimit) {
				echo "<tr><td colspan='3' style='color: blue'><b>&nbsp;* ".$a8."</b></td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>";
				if($n_utilidad=="U") echo "<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>";
				echo "<tr/><tr><td colspan='3'>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>";
				if($n_utilidad=="U") echo "<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>";
				echo "</tr>";
				$snewbuffer=$snewbuffer."\n* ".$a8." \n";
				echo "<tr><td colspan='3' style='color: blue'><b>&nbsp;**MARKET</b></td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>";
				if($n_utilidad=="U") echo "<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>";
				echo "</tr>";
				$snewbuffer=$snewbuffer."\n**MARKET \n";
			}

			if($xlinea!=$a6 && $irow!=$ilimit) { 
				if($irow>0) {
					echo "<tr><td colspan='3'>&nbsp;</td><td>&nbsp;</td><td align='right'><b>&nbsp;TOTAL LINEA</b></td><td align='right'><b>&nbsp;".number_format($tot_lin[$xlinea],4)."</b></td>";
					if($n_utilidad=="U") echo "<td>&nbsp;</td><td>&nbsp;</td><td align='right'><b>&nbsp;".number_format($tot_lin_margen[$xlinea],4)."</b></td><td align='right'><b>&nbsp;".number_format($tot_lin_igv[$xlinea],4)."</b></td><td align='right'><b>&nbsp;".number_format($tot_lin_total[$xlinea],4)."</b></td>";
					echo "</tr>";
					$snewbuffer=$snewbuffer." , , , ,TOTAL LINEA ,".$tot_lin[$xlinea]."";
					if($n_utilidad=="U") $snewbuffer=$snewbuffer.", , ,".$tot_lin_margen[$xlinea].",".$tot_lin_igv[$xlinea].",".$tot_lin_total[$xlinea];
					$snewbuffer=$snewbuffer."\n";
				}
			}

			if($a9 == 1 && $irow!=$ilimit && $pinto_market == 0){
				echo "<tr><td colspan='3'>&nbsp;</td><td align='right' colspan='2' style='color: blue'><b>&nbsp; TOTAL MARKET</b></td><td align='right' style='color: blue'><b>&nbsp;".number_format($tot_tipo[2],4)."</b></td>";
				if($n_utilidad=="U") echo "<td>&nbsp;</td><td>&nbsp;</td><td align='right' style='color: blue'><b>&nbsp;".number_format($tot_tipo_margen[2],4)."</b></td><td align='right' style='color: blue'><b>&nbsp;".number_format($tot_tipo_igv[2],4)."</b></td><td align='right' style='color: blue'><b>&nbsp;".number_format($tot_tipo_total[2],4)."</b></td>";
				echo "</tr>";
				$snewbuffer=$snewbuffer." , , , ,TOTAL MARKET ,".$tot_tipo[2];
				if($n_utilidad=="U") $snewbuffer=$snewbuffer.", , ,".$tot_tipo_margen[2].",".$tot_tipo_igv[2].",".$tot_tipo_total[2];
				$snewbuffer=$snewbuffer."\n";

				echo "<tr><td colspan='3' style='color: blue'><b>&nbsp;**COMBUSTIBLE</b></td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>";
				if($n_utilidad=="U") echo "<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>";
				echo "</tr>";
				$snewbuffer=$snewbuffer."\n**COMBUSTIBLE \n";
				$pinto_market = 1;
			}

			if($xlinea!=$a6 && $irow!=$ilimit) { 
				nom_linea($coneccion,$a6);
				echo "<tr><td colspan='3'><b>&nbsp;*** ".$a6." - ".$zdesclin."</b></td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>";
				if($n_utilidad=="U") echo "<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>";
				echo "</tr>";
				$lin=$lin."<tr><td>".str_pad( $a6, $col[2])." - ".$zdesclin."</td><td>    </td></tr>";
				$lin=$lin."<br>";
				$snewbuffer=$snewbuffer."*** ".$a6." - ".$zdesclin." \n";
			}

			if($irow!=$ilimit){
			    	echo "<tr $color_negativo><td>&nbsp;".$a0."</td><td>&nbsp;".$a1."</td>";
			    	echo "<td align='center'>&nbsp;".$a2."&nbsp;</td><td align='right'>&nbsp;".$a3."</td>";
			    	echo "<td align='right'>&nbsp;".$a4."</td><td align='right'>&nbsp;".number_format($a5,4)."</td>";

				if($n_utilidad=="U") { 
					echo "<td align='right'>&nbsp;".number_format($a14,2)."</td>";
					echo "<td align='right'>&nbsp;".number_format($a10,0)."&nbsp;%</td>";
				    	echo "<td align='right'>&nbsp;".number_format($a11,4)."</td>";
				    	echo "<td align='right'>&nbsp;".number_format($a12,4)."</td>";
				    	echo "<td align='right'>&nbsp;".number_format($a13,4)."</td>";
				}
			    	echo "</tr>";
				$lin=$lin."<tr><td>".str_pad( $a0, $col[0])."</td><td>".str_pad( $a1, $col[1])."</td>";
				$lin=$lin."<td>".str_pad( $a2, $col[2])."</td><td>   ".str_pad( $a3, $col[3], " ", STR_PAD_LEFT)."</td>";
				$lin=$lin."<td>   ".str_pad( $a4, $col[4], " ", STR_PAD_LEFT)."</td><td>   ".str_pad(number_format($a5,4), $col[5], " ", STR_PAD_LEFT)."</td>";
				$lin=$lin."<td>   ".str_pad( number_format($a10,4), $col[10], " ", STR_PAD_LEFT)."</td><td>   ".str_pad(number_format($a11,4), $col[11], " ", STR_PAD_LEFT)."</td>";
				$lin=$lin."<td>   ".str_pad( number_format($a12,4), $col[12], " ", STR_PAD_LEFT)."</td><td>   ".str_pad(number_format($a13,4), $col[13], " ", STR_PAD_LEFT)."</td></tr>";
				$snewbuffer=$snewbuffer." ".trim($a0)." ".",".$a1.",".$a2.",".$a3.",".$a4.",".$a5;
				if($n_utilidad=="U") $snewbuffer=$snewbuffer.",".$a14.",".$a10.",".$a11.",".$a12.",".$a13;
				$snewbuffer=$snewbuffer." \n";
			}

			if($xlinea!=$a6 && $irow==$ilimit) { 
				if($irow>0) {
					echo "<tr><td colspan='3'>&nbsp;</td><td>&nbsp;</td><td align='right'><b>&nbsp;TOTAL LINEA</b></td><td align='right'><b>&nbsp;".number_format($tot_lin[$xlinea],4)."</b></td>";
					if($n_utilidad=="U") echo "<td>&nbsp;</td><td>&nbsp;</td><td align='right'><b>&nbsp;".number_format($tot_lin_margen[$xlinea],4)."</b></td><td align='right'><b>&nbsp;".number_format($tot_lin_igv[$xlinea],4)."</b></td><td align='right'><b>&nbsp;".number_format($tot_lin_total[$xlinea],4)."</b></td>";
					echo "</tr>";
					$snewbuffer=$snewbuffer." , , , ,TOTAL LINEA ,".$tot_lin[$xlinea]."";
					if($n_utilidad=="U") $snewbuffer=$snewbuffer.", , ,".$tot_lin_margen[$xlinea].",".$tot_lin_igv[$xlinea].",".$tot_lin_total[$xlinea];
					$snewbuffer=$snewbuffer."\n";
				}
			}

			if($xalma!=$a7 && $irow==$ilimit) {
				if($irow>0) {
					echo "<tr><td colspan='3'>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>";
					if($n_utilidad=="U") echo "<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>";
					echo "</tr>";

					echo "<tr><td colspan='3'>&nbsp;</td><td align='right' colspan='2' style='color: blue'><b>&nbsp; TOTAL COMBUSTIBLE</b></td><td align='right' style='color: blue'><b>&nbsp;".number_format($tot_tipo[1],4)."</b></td>";
					if($n_utilidad=="U") echo "<td>&nbsp;</td><td>&nbsp;</td><td align='right' style='color: blue'><b>&nbsp;".number_format($tot_tipo_margen[1],4)."</b></td><td align='right' style='color: blue'><b>&nbsp;".number_format($tot_tipo_igv[1],4)."</b></td><td align='right' style='color: blue'><b>&nbsp;".number_format($tot_tipo_total[1],4)."</b></td>";
					echo "</tr>";
					$snewbuffer=$snewbuffer." , , , ,TOTAL COMBUSTIBLE ,".$tot_tipo[1];
					if($n_utilidad=="U") $snewbuffer=$snewbuffer.", , ,".$tot_tipo_margen[1].",".$tot_tipo_igv[1].",".$tot_tipo_total[1];
					$snewbuffer=$snewbuffer."\n";

					echo "<tr><td colspan='3'>&nbsp;</td><td align='right' colspan='3'>&nbsp;</td>";
					if($n_utilidad=="U") echo "<td align='right' colspan='5'>&nbsp;</td>";
					echo "</tr>";
					echo "<tr><td colspan='3'>&nbsp;</td><td align='right' colspan='2' style='color: blue'><b>&nbsp;TOTAL ALMACEN ".$xa8."</b></td><td align='right' style='color: blue'><b>&nbsp;".number_format($tot_alma[$xalma],4)."</b></td>";
					if($n_utilidad=="U") echo "<td>&nbsp;</td><td>&nbsp;</td><td align='right' style='color: blue'><b>&nbsp;".number_format($tot_alma_margen[$xalma],4)."</b></td><td align='right' style='color: blue'><b>&nbsp;".number_format($tot_alma_igv[$xalma],4)."</b></td><td align='right' style='color: blue'><b>&nbsp;".number_format($tot_alma_total[$xalma],4)."</b></td>";
					echo "</tr>";
					$lin=$lin."<tr><td>                            </td><td>                           </td><td>TOTAL ALMACEN ".$xa8."    </td><td>   ".number_format($tot_alma[$xalma],4)."</td></tr>";
					$snewbuffer=$snewbuffer."\n";
					$snewbuffer=$snewbuffer." , , , ,TOTAL ALMACEN ".$xa8.",".$tot_alma[$xalma];
					if($n_utilidad=="U") $snewbuffer=$snewbuffer.", , ,".$tot_alma_margen[$xalma].",".$tot_alma_igv[$xalma].",".$tot_alma_total[$xalma];
				}
			}
		    	$irow++;
			$xalma=$a7;  
			$xlinea=$a6;  
			$xa8=$a8;
		}
		fwrite($ft,$snewbuffer);
		fclose($ft);
		?>

  		</table>
	</form>
</body>
</html>

<?php
imprimir2( $lin, $col, $nom, $v_archivo, "REPORTE DE STOCK" );
if ($conector_id) pg_close($conector_id);
$clase_error->_error();
