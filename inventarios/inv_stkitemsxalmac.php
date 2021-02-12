<?php
/*
error_reporting(E_ALL);
ini_set('display_errors', 1);
*/

if(isset($_GET['v']) && $_GET['v'] == 'old') {

//inicio de version antigua

include("../menu_princ.php");
include("../functions.php");
include("/sistemaweb/utils/funcion-texto.php");
include("../funcjch.php");
require("../clases/funciones.php");


if(isset($_POST['diasd'])) {
	$nuevofechad = explode('/',$_REQUEST['diasd']);
} else {
	$nuevofechad = explode('/',date('d/m/Y'));
}
$diad = $nuevofechad[0];
$mesd = $nuevofechad[1];
$anod = $nuevofechad[2];

$postrans = "pos_trans".$anod.$mesd;

$funcion = new class_funciones;
$clase_error = new OpensoftError;
$clase_error->_error();
$conector_id = $funcion->conectar("","","","","");

$flg = isset($_GET["flg"]) ? $_GET["flg"] : '';
$boton = isset($_POST["boton"]) ? $_POST["boton"] : '';

if($flg == "A") {
	$soloconstk = "S";
	$diad = date("d"); 
	$mesd = date("m"); 
	$anod = date("Y");
	$postrans = "pos_trans".$anod.$mesd;
}

if ($boton == "Buscar"){
	
    $v_sqlx 	= "select par_valor from int_parametros where trim(par_nombre)='print_netbios' ";
    $v_xsqlx 	= pg_exec( $v_sqlx);
    $v_server	= pg_result($v_xsqlx,0,0);

    $v_sqlx 	= "select par_valor from int_parametros where trim(par_nombre)='print_name' ";
    $v_xsqlx 	= pg_exec($v_sqlx);
    $v_printer 	= pg_result($v_xsqlx,0,0);

    $v_sqlx 	= "select par_valor from int_parametros where trim(par_nombre)='print_server' ";
    $v_xsqlx	= pg_exec($v_sqlx);
    $v_ipprint	= pg_result($v_xsqlx,0,0);
   
    $v_archivo	= "/tmp/imprimir/reporte_de_stock.txt";
	$file 		= "/tmp/imprimir/Reporte_StkItemAlmacen";

	$fh = fopen($file, "w");
	fwrite($fh,"");
	fclose($fh);	
}

if ($boton == "Imprimir") {
	global $sqlca;

	$arch = "/tmp/imprimir/Reporte_StkItemAlmacen";
	$sql  =	"SELECT
			trim(pc_samba),
			trim(prn_samba),
			trim(ip) 
		FROM
			pos_cfg 
		WHERE
			impcierre = true and tipo = 'M'
		ORDER BY
			tipo DESC,
			pos ASC";		

	$rs = $sqlca->query($sql);

	if ($rs < 0) {
		echo "Error consultando POS\n";
		return false;
	}

	if ($sqlca->numrows() < 1)
		return true;

	$row = $sqlca->fetchRow();
	$cmd = "lpr -H {$row[2]} -P {$row[1]} {$arch}";

	$fp = fopen("COMANDO.txt","a");
	fwrite($fp, "-".$smbc."-".PHP_EOL);
	fclose($fp);  	
	exec($cmd);
	?><script>alert("<?php echo 'Se mando a imprimir la wincha.'; ?>");</script><?php
}

function alinea($str, $tipo, $ll) {
	if ($tipo == 0)
		return ($str . espaciosA(($ll-strlen($str))));
	else if ($tipo == 1)
		return (espaciosA(($ll-strlen($str))) . $str);
	return (espaciosA((($ll/2)-(strlen($str)/2))) . $str . espaciosA((($ll/2)-(strlen($str)/2))));
}
?>

<html>
	<link rel="stylesheet" href="<?php echo $v_path_url; ?>sistemaweb.css" type="text/css">
	<head>
		<title>Items por Almacen</title>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		<script language="javascript">
			var miPopup

			function abrelinea(){
				miPopup = window.open("escogelinea.php","miwin","width=500,height=400,scrollbars=yes")
				miPopup.focus()
			}

			function descargar(){
				location.href="/sistemaweb/inventarios/stkitemsxalmac_new.csv";
			}

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
$lin = $lin."                                 STOCKS VALORIZADOS POR ALMACEN - LINEAS<br> ";
$texto_impresion = ""; 
$CRLF = "\r\n";
//$txtalma = $_POST["txtalma"];
$txtalma = isset($_POST["txtalma"]) ? $_POST["txtalma"] : '';
//$linea = $_POST["linea"];
$linea = isset($_POST["linea"]) ? $_POST["linea"] : '';
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
				if(strlen($txtalma) > 0){
					$sqlao    = "select tab_elemento,tab_descripcion from int_tabla_general where tab_tabla='ALMA' and tab_elemento like '%".$txtalma."%' ";
					$xsqlao   = pg_exec($coneccion,$sqlao);
					$ilimitao = pg_numrows($xsqlao);
					if($ilimitao > 0){
						$txtalma = pg_result($xsqlao,0,0);
					    	$descao  = pg_result($xsqlao,0,1);
					}
				}else{
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
				if(strlen($linea)>0) {
					$sqllin    = "select tab_elemento,tab_descripcion,tab_car_03 from int_tabla_general where tab_tabla='20' and (tab_elemento like ';".$linea.";' or tab_descripcion like ';".$linea.";')";
				  	$xsqllin   = pg_exec($coneccion,$sqllin);
				  	$ilimitlin = pg_numrows($xsqllin);

					if($ilimitlin > 0) {
						$linea     = pg_result($xsqllin,0,0);
					    	$desclinea = pg_result($xsqllin,0,1);
					    	if($linea == "000000") 	
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
			<br><?php echo isset($s_stock) ? $s_stock : ''; ?></br> 
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
      			<td align="center"><button type="submit" name="boton" value="Imprimir">Imprimir</button></td>
			<td align="center"><button type="submit" name="boton" value="Buscar"><img src="/sistemaweb/images/search.png" alt="left"/>Buscar</button></td>
			<td align="center"><button type="button" name="boton" value="Excel" OnClick="location.href='/sistemaweb/inventarios/stock_almacen_excel.php'" ><img src="/sistemaweb/images/excel_icon.png" alt="left" />  Excel</button></td>
			<!--<td align="center"><button type="button" name="boton" value="excel" OnClick="location.href='/sistemaweb/inventarios/stkitemsxalmac_new.csv';"><img src="/sistemaweb/images/excel_icon.png" alt="left" />  Excel</button></td>
	-->
    		</tr>
	</table>
  	<p>&nbsp;</p>
  	<table border="1" cellspacing="0" cellpadding="0">
    		<tr>
			<th>CODIGO</th>
		        <th>ARTICULO</th>
		        <th>UNID</th>
		        <th>STK TOTAL</th>
		        <th>COSTO</th>
		        <th>P. VENTA</th>
		        <th>VALOR TOTAL</th>
			<?php if($n_utilidad=="U") { ?>
				<th>MARGEN</th>
				<th>VAL. MARGEN</th>
				<th>IGV</th>
				<th>TOTAL</th>
			<?php } ?>
    		</tr>
		<?php
		$alma_sql   = "select ch_almacen, ch_nombre_almacen from inv_ta_almacenes  where ch_almacen='".$txtalma."' and ch_clase_almacen='1' ";
		$alma_sql_r = pg_exec($conector_id,$alma_sql);
		if (pg_numrows($alma_sql_r) > 0){
			$v_codalma  = pg_result($alma_sql_r,0,0);
			$v_descalma = pg_result($alma_sql_r,0,1);
		}	
		$lin = $lin."Fecha y Hora de impresion: ".date("d/m/Y H:i:s")." <br>";
                $lin = $lin."ALMACEN: ".$v_codalma." -- ".$v_descalma."<br> ";	
		$lin = $lin."Fecha: ".$diad." / ".$mesd." / ".$anod." <br>";

		$texto_impresion .= alinea("REPORTE DE STOCK",2,40).$CRLF;
		$texto_impresion .= alinea("STOCKS VALORIZADOS POR ALMACEN - LINEAS",2,40).$CRLF;
		$texto_impresion .= alinea("DE FECHA: ".$diad." / ".$mesd." / ".$anod,2,40).$CRLF;		
		$texto_impresion .= alinea("ALMACEN: ".$v_codalma." -- ".$v_descalma,2,40).$CRLF;
		$texto_impresion .= alinea("Fecha y Hora de impresion: ",2,40).$CRLF;	
		$texto_impresion .= alinea(date("d/m/Y H:i:s"),2,40).$CRLF;	
		$texto_impresion .= alinea("ARTICULO                      STOCK",2,40) . $CRLF;
		$texto_impresion .= alinea("----------------------------------------",2,40) . $CRLF;

		$col[0] = 15;
		$col[1] = 35;
		$col[2] = 6;
		$col[3] = 7;
		$col[4] = 7;
		$col[5] = 7;

		$nom[0] = "    CODIGO   ";
		$nom[1] = "           ARTICULO            ";
		$nom[2] = "UNID ";
		$nom[3] = "STK TOTAL";
		$nom[4] = "    COSTO  ";
		$nom[5] = " VALOR TOTAL";

		$lin = $lin."------------------------------------------------------------------------------------------------------- ";
		$lin = $lin."<table><tr>";
		$lin = $lin."<td>".str_pad($nom[0], $col[0])."</td>";
		$lin = $lin."<td>".str_pad($nom[1],$col[1])."</td>";
		$lin = $lin." <td>".str_pad($nom[2], $col[2])."</td>";
		$lin = $lin."<td>".str_pad($nom[3], $col[3])."</td>";
		$lin = $lin."<td>".str_pad($nom[4], $col[4])."</td>";
		$lin = $lin."<td>".str_pad($nom[5], $col[5])."</td>";
		$lin = $lin."<br>";
		$lin = $lin."------------------------------------------------------------------------------------------------------- ";
		$lin = $lin."<br>"; 

 		$ft = fopen('stkitemsxalmac_new.csv','w');
		if ($ft > 0) {
			$snewbuffer = " STOCKS VALORIZADOS POR ALMACEN - LINEAS \n\n";
			/*$snewbuffer = $snewbuffer.",CODIGO,DESCRIPCION,UNID,STK-TOTAL,COSTO,VALOR-TOTAL ";
			if ($n_utilidad == "U") 
				$snewbuffer = $snewbuffer.",MARGEN,VAL.MARGEN,IGV,TOTAL ";
			/$snewbuffer = $snewbuffer." \n";*/
		}
		$sqladd = "";

		//SOLO SI TIENEN STOCK POSITIVO  OSEA STOCK > 0
		if($p_stock == "P") 
			$sqladd = " and (s.stk_stock".$mesd." > '0')";  
		//PARA MOSTRAR STOCK EN CERO
		if($c_stock == "C") 
			$sqladd= "and (s.stk_stock".$mesd."= '0')"; 
		//PARA MOSTRAR SOLO NEGATIVOS
		if($n_stock == "N") 
			$sqladd= "and (s.stk_stock".$mesd." < '0')"; 

		if($p_stock == "P" && $c_stock == "C") 
			$sqladd = "and (s.stk_stock".$mesd." >= '0' ) ";  
		if($p_stock == "P" && $n_stock == "N") 
			$sqladd = "and (s.stk_stock".$mesd." > '0' or s.stk_stock".$mesd." < '0')" ; 
		if($c_stock == "C" && $n_stock == "N") 
			$sqladd = "and (s.stk_stock".$mesd." <=' 0') "; 
		if($p_stock == "P" && $c_stock == "C" && $n_stock == "N") 
			$sqladd = "and (s.stk_stock".$mesd." >= '0' or s.stk_stock".$mesd."<= '0') " ; 

		if (isset($txtalma) && $txtalma!='')  
			$sqladd .= " and (s.stk_almacen='".$txtalma."')"; 

		$linea = substr($linea,4,2);

		// obteniendo la lista precio actual 
		$query = "SELECT par_valor FROM int_parametros WHERE par_nombre='lista precio';";

		if ($sqlca->query($query) < 0) 
			return false;

		$a = $sqlca->fetchRow();
		$listaprecio = trim($a[0]); 

		if($n_utilidad=="U") { 
			$sql = "
			SELECT	
				ar.art_codigo,
				trim(ar.art_descripcion) as descripcion,
				substring(trim(ar.art_unidad) from 4 for char_length(trim(ar.art_unidad))) as unidad,
				round(s.stk_stock".$mesd.",2),
				s.stk_costo".$mesd.",
				(s.stk_stock".$mesd."*s.stk_costo".$mesd.") as subtot,
				ar.art_linea,
				s.stk_almacen,
				a.ch_nombre_almacen,
				case when s.art_codigo in (SELECT ch_codigocombustible FROM comb_ta_combustibles) then 1 else 2 End as tipo, 
				case when s.art_codigo in (SELECT ch_codigocombustible FROM comb_ta_combustibles) then 
					case WHEN s.stk_costo".$mesd." > 0 THEN round(100*((com.nu_preciocombustible / (1 + util_fn_igv()/100) / s.stk_costo".$mesd.") - 1),0)
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
				l.tab_descripcion AS DESCLINEA,
				(SELECT precio FROM ".$postrans." WHERE codigo = s.art_codigo LIMIT 1) AS nu_precio_venta
			FROM 
				inv_ta_almacenes a, 
				inv_saldoalma s,
				int_articulos ar 
				LEFT JOIN fac_lista_precios p on p.art_codigo = ar.art_codigo
				LEFT JOIN comb_ta_combustibles com on com.ch_codigocombustible = ar.art_codigo 
				LEFT JOIN int_tabla_general l ON (l.tab_tabla='20' AND (ar.art_linea = l.tab_elemento OR ar.art_linea = substr(l.tab_elemento,5,2)))
			WHERE
				(art_linea like '%".$linea."') 
				AND (a.ch_clase_almacen='1') 
				AND (trim(s.stk_almacen)=trim(a.ch_almacen)) 
				AND (s.stk_periodo='".$anod."') 
				AND (s.art_codigo=ar.art_codigo) 
				".$sqladd."
				AND ar.art_plutipo!='2' 
				AND ar.art_plutipo!='3' 
				AND p.pre_lista_precio='$listaprecio'
			ORDER BY
				s.stk_almacen,
				tipo desc,
				ar.art_linea,
				s.art_codigo;
			";
		} else{
			$sql = "
			SELECT
				ar.art_codigo,
				trim(ar.art_descripcion) as descripcion,
				substring(trim(ar.art_unidad) from 4 for char_length(trim(ar.art_unidad))) as unidad,
				s.stk_stock".$mesd.",
				s.stk_costo".$mesd.",
				(s.stk_stock".$mesd."*s.stk_costo".$mesd.") as subtot,
				ar.art_linea,
				s.stk_almacen,
				a.ch_nombre_almacen,
				case when s.art_codigo in (SELECT ch_codigocombustible FROM comb_ta_combustibles) then 1 else 2 End as tipo,
				l.tab_descripcion AS DESCLINEA,
				(SELECT precio FROM ".$postrans." WHERE codigo = s.art_codigo LIMIT 1) AS nu_precio_venta
			FROM 
				inv_ta_almacenes a, 
				inv_saldoalma s,
				int_articulos ar 
				LEFT JOIN int_tabla_general l ON (l.tab_tabla='20' AND (ar.art_linea = l.tab_elemento OR ar.art_linea = substr(l.tab_elemento,5,2)))
			WHERE
				(art_linea like '%".$linea."') 
				AND (a.ch_clase_almacen='1') 
				AND (trim(s.stk_almacen)=trim(a.ch_almacen)) 
				AND (s.stk_periodo='".$anod."') 
				AND (s.art_codigo=ar.art_codigo)
				".$sqladd."
				AND ar.art_plutipo!='2'
				AND ar.art_plutipo!='3'
			ORDER BY
				s.stk_almacen,
				tipo desc,
				ar.art_linea,
				s.art_codigo;
			";
		}
/*
echo "<pre>";
print_r($sql);
echo "</pre>";
*/
		$xsql		= pg_exec($coneccion,$sql);
		$ilimit		= pg_numrows($xsql);
		$_SESSION['A']=$sql;
		$_SESSION['U']=$n_utilidad;
		$xalma		= "";
		$xlinea		= "";
		$irow 		= 0;
		$pinto_market 	= 0;

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

			$tot_lin[$a6]	= $tot_lin[$a6]  + $a5;
			$tot_tipo[$a9]	= $tot_tipo[$a9] + $a5;
			$tot_alma[$a7]	= $tot_alma[$a7] + $a5;

			//P. VENTA
			$nu_precio_venta = pg_result($xsql,$irow,11);

			if ($n_utilidad == "U") {

				//P. VENTA
				$nu_precio_venta = pg_result($xsql,$irow,15);

				$a10 = pg_result($xsql,$irow,10);
				$a11 = pg_result($xsql,$irow,11);
				$a12 = pg_result($xsql,$irow,12);
				$a13 = pg_result($xsql,$irow,13);

				$tot_lin_margen[$a6]  = $tot_lin_margen[$a6]  + $a11;
				$tot_lin_igv[$a6]     = $tot_lin_igv[$a6]     + $a12;
				$tot_lin_total[$a6]   = $tot_lin_total[$a6]   + $a13;

				$tot_tipo_margen[$a9] = $tot_tipo_margen[$a9] + $a11;
				$tot_tipo_igv[$a9]    = $tot_tipo_igv[$a9]    + $a12;
				$tot_tipo_total[$a9]  = $tot_tipo_total[$a9]  + $a13;

				$tot_alma_margen[$a7] = $tot_alma_margen[$a7] + $a11;
				$tot_alma_igv[$a7]    = $tot_alma_igv[$a7]    + $a12;
				$tot_alma_total[$a7]  = $tot_alma_total[$a7]  + $a13;
			}

			$arreglo[$a] =  pg_result($xsql,$irow,0);

			if(number_format($a10,2) <= 0 && $irow != $ilimit && $n_utilidad == "U") {
				$color_negativo = " bgcolor='yellow' ";
			} else {
				$color_negativo = " ";
			}

			if ($xalma != $a7 && $irow != $ilimit) {
				echo "<tr><td colspan='3' style='color: blue'><b>&nbsp;* ".$a8."</b></td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>";
				if($n_utilidad == "U") 
					echo "<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>";
				echo "<tr/><tr><td colspan='3'>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>";
				if($n_utilidad == "U") 
					echo "<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>";
				echo "</tr>";
				$snewbuffer = $snewbuffer."\n*".trim($a8)."\n";

				if (trim($v_codalma) == "")
					$texto_impresion .= "*** ".$a8.$CRLF;
				echo "<tr><td colspan='3' style='color: blue'><b>&nbsp;**MARKET</b></td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>";
				if ($n_utilidad == "U") 
					echo "<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>";
				echo "</tr>";
				$snewbuffer = $snewbuffer."\n**MARKET\n"."\n";
				$texto_impresion .= "---> MARKET".$CRLF;

				$snewbuffer = $snewbuffer."COD. LINEA".","."LINEA".","."COD. PRODUCTO".","."PRODUCTO".","."COD. UNIDAD".","."STOCK-TOTAL".","."COSTO".","."VALOR-TOTAL";

				if ($n_utilidad == "U")
					$snewbuffer = $snewbuffer.","."MARGEN".","."VAL. MARGEM".","."IGV".","."TOTAL";

				$snewbuffer = $snewbuffer."\n";

			}

			if($xlinea != $a6 && $irow != $ilimit) { 
				if ($irow > 0) {
					echo "<tr bgcolor='#81F7F3'><td colspan='4'>&nbsp;</td><td>&nbsp;</td><td align='right'><b>&nbsp;TOTAL LINEA</b></td><td align='right'><b>&nbsp;".number_format($tot_lin[$xlinea],4)."</b></td>";
					if($n_utilidad == "U") 
						echo "<td>&nbsp;</td><td align='right'><b>&nbsp;".number_format($tot_lin_margen[$xlinea],4)."</b></td><td align='right'><b>&nbsp;".number_format($tot_lin_igv[$xlinea],4)."</b></td><td align='right'><b>&nbsp;".number_format($tot_lin_total[$xlinea],4)."</b></td>";
					echo "</tr>";
					/*$snewbuffer = $snewbuffer." "." "." "." "." "."TOTAL-LINEA: ".number_format($tot_lin[$xlinea], 4, '.', ',');
					if($n_utilidad == "U") 
						$snewbuffer = $snewbuffer." ".$tot_lin_margen[$xlinea]." ".$tot_lin_igv[$xlinea]." ".$tot_lin_total[$xlinea];
					/$snewbuffer = $snewbuffer."\n"."\n";*/
				}
			}

			if($a9 == 1 && $irow != $ilimit && $pinto_market == 0) {
				echo "<tr bgcolor='#81F781'><td colspan='4'>&nbsp;</td><td align='right' colspan='2' style='color: blue'><b>&nbsp; TOTAL MARKET</b></td><td align='right' style='color: blue'><b>&nbsp;".number_format($tot_tipo[2],4)."</b></td>";
				if ($n_utilidad == "U") 
					echo "<td>&nbsp;</td><td align='right' style='color: blue'><b>&nbsp;".number_format($tot_tipo_margen[2],4)."</b></td><td align='right' style='color: blue'><b>&nbsp;".number_format($tot_tipo_igv[2],4)."</b></td><td align='right' style='color: blue'><b>&nbsp;".number_format($tot_tipo_total[2],4)."</b></td>";
				echo "</tr>";

				$snewbuffer = $snewbuffer." "." "." "." "." "."TOTAL-MARKET: ".number_format($tot_tipo_margen[2], 4, '.', ',');
				if ($n_utilidad == "U") 
					$snewbuffer = $snewbuffer.", ,".$tot_tipo_margen[2].",".$tot_tipo_igv[2].",".$tot_tipo_total[2];
				$snewbuffer = $snewbuffer."\n";

				echo "<tr><td colspan='3' style='color: blue'><b>&nbsp;**COMBUSTIBLE</b></td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>";
				if ($n_utilidad == "U") 
					echo "<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>";
				echo "</tr>";
				$snewbuffer = $snewbuffer."\n**COMBUSTIBLE\n";
				$texto_impresion .= "---> COMBUSTIBLE".$CRLF;
				$pinto_market = 1;
			}

			if ($xlinea != $a6 && $irow != $ilimit) { 
				nom_linea($coneccion, $a6);
				echo "<tr><td colspan='3'><b>&nbsp;***".$a6." - ".$zdesclin."</b></td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>";//LINEA  ***
				if($n_utilidad=="U") 
					echo "<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>";
				echo "</tr>";
				$lin = $lin."<tr><td>".str_pad( $a6, $col[2])." - ".$zdesclin."</td><td>    </td></tr>";
				$lin = $lin."<br>";
				//$snewbuffer = $snewbuffer."***".trim($a6)."-".trim($zdesclin)."\n";
				//$snewbuffer = $snewbuffer."*** LINEA"."\n";
				$texto_impresion .= $CRLF;
				$texto_impresion .= "* ".$a6."-".substr($zdesclin,0,29).$CRLF;				
			}

			$columnas = Array(0 => 32, 1 => 8);

			if ($irow != $ilimit) {

				echo "<tr $color_negativo>";
				echo "<td>&nbsp;".$a0."</td><td>&nbsp;".$a1."</td>";
		    	echo "<td align='center'>&nbsp;".$a2."&nbsp;</td><td align='right'>&nbsp;".$a3."</td>";
		    	echo "<td align='right'>&nbsp;".$a4."</td><td align='right'>&nbsp;".$nu_precio_venta."</td><td align='right'>&nbsp;".number_format($a5,4)."</td>";

				if($n_utilidad == "U") { 
					echo "<td align='right'>&nbsp;".number_format($a10,0)."&nbsp;%</td>";
			    	echo "<td align='right'>&nbsp;".number_format($a11,4)."</td>";
			    	echo "<td align='right'>&nbsp;".number_format($a12,4)."</td>";
			    	echo "<td align='right'>&nbsp;".number_format($a13,4)."</td>";
				}

		    	echo "</tr>";

				$values = Array($a1, $a3);
				$texto_impresion .= alineaNC($values, $columnas).$CRLF;
			
				$lin = $lin."<tr><td>".str_pad( $a0, $col[0])."</td><td>".str_pad( $a1, $col[1])."</td>";
				$lin = $lin."<td>".str_pad( $a2, $col[2])."</td><td>   ".str_pad( $a3, $col[3], " ", STR_PAD_LEFT)."</td>";
				$lin = $lin."<td>   ".str_pad( $a4, $col[4], " ", STR_PAD_LEFT)."</td><td>   ".str_pad(number_format($a5,4), $col[5], " ", STR_PAD_LEFT)."</td>";
				$lin = $lin."<td>   ".str_pad( number_format($a10,4), $col[10], " ", STR_PAD_LEFT)."</td><td>   ".str_pad(number_format($a11,4), $col[11], " ", STR_PAD_LEFT)."</td>";
				$lin = $lin."<td>   ".str_pad( number_format($a12,4), $col[12], " ", STR_PAD_LEFT)."</td><td>   ".str_pad(number_format($a13,4), $col[13], " ", STR_PAD_LEFT)."</td></tr>";

				//EXCEL CODIGO DE LOS ARTICULOS
				$snewbuffer = $snewbuffer.$a6.",".trim($zdesclin).",".trim($a0)." ".",".$a1." ".",".$a2." ".",".$a3." ".",".$a4." ".",".$a5;

				if ($n_utilidad == "U") 
					$snewbuffer = $snewbuffer.",".$a10.",".$a11.",".$a12.",".$a13;

				$snewbuffer = $snewbuffer."\n";

			}

			if ($xlinea != $a6 && $irow == $ilimit) { 
				if($irow > 0) {
					echo "<tr bgcolor='#81F7F3'><td colspan='4'>&nbsp;</td><td>&nbsp;</td><td align='right'><b>&nbsp;TOTAL LINEA</b></td><td align='right'><b>&nbsp;".number_format($tot_lin[$xlinea],4)."</b></td>";
					if ($n_utilidad == "U") 
						echo "<td>&nbsp;</td><td align='right'><b>&nbsp;".number_format($tot_lin_margen[$xlinea],4)."</b></td><td align='right'><b>&nbsp;".number_format($tot_lin_igv[$xlinea],4)."</b></td><td align='right'><b>&nbsp;".number_format($tot_lin_total[$xlinea],4)."</b></td>";
					echo "</tr>";
					/*$snewbuffer = $snewbuffer." "." "." "." "." "."TOTAL-LINEA: ".number_format($tot_lin[$xlinea], 4, '.', ',');
					if($n_utilidad == "U") 
						$snewbuffer = $snewbuffer." ".$tot_lin_margen[$xlinea]." ".$tot_lin_igv[$xlinea]." ".$tot_lin_total[$xlinea];
					$snewbuffer = $snewbuffer."\n"."\n";*/
				}
			}

			if ($xalma != $a7 && $irow == $ilimit) {
				if($irow > 0) {
					echo "<tr><td colspan='3'>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>";
					if ($n_utilidad == "U") 
						echo "<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>";
					echo "</tr>";
					echo "<tr bgcolor='#81F781'><td colspan='4'>&nbsp;</td><td align='right' colspan='2' style='color: blue'><b>&nbsp; TOTAL COMBUSTIBLE</b></td><td align='right' style='color: blue'><b>&nbsp;".number_format($tot_tipo[1],4)."</b></td>";
					if ($n_utilidad == "U") 
						echo "<td>&nbsp;</td><td align='right' style='color: blue'><b>&nbsp;".number_format($tot_tipo_margen[1],4)."</b></td><td align='right' style='color: blue'><b>&nbsp;".number_format($tot_tipo_igv[1],4)."</b></td><td align='right' style='color: blue'><b>&nbsp;".number_format($tot_tipo_total[1],4)."</b></td>";
					echo "</tr>";
					/*$snewbuffer = $snewbuffer." "." "." "." "." "."TOTAL-COMBUSTIBLE: ".number_format($tot_tipo[1], 4, '.', ',');
					if($n_utilidad == "U") 
						$snewbuffer = $snewbuffer." ".$tot_tipo_margen[1]." ".$tot_tipo_igv[1]." ".$tot_tipo_total[1];
					$snewbuffer = $snewbuffer."\n";*/
					echo "<tr><td colspan='3'>&nbsp;</td><td align='right' colspan='3'>&nbsp;</td>";
					if ($n_utilidad == "U") 
						echo "<td align='right' colspan='4'>&nbsp;</td>";
					echo "</tr>";
					echo "<tr bgcolor='#F781F3'><td colspan='4'>&nbsp;</td><td align='right' colspan='2' style='color: blue'><b>&nbsp;TOTAL ALMACEN ".$xa8."</b></td><td align='right' style='color: blue'><b>&nbsp;".number_format($tot_alma[$xalma],4)."</b></td>";
					if ($n_utilidad == "U") 
						echo "<td>&nbsp;</td><td align='right' style='color: blue'><b>&nbsp;".number_format($tot_alma_margen[$xalma],4)."</b></td><td align='right' style='color: blue'><b>&nbsp;".number_format($tot_alma_igv[$xalma],4)."</b></td><td align='right' style='color: blue'><b>&nbsp;".number_format($tot_alma_total[$xalma],4)."</b></td>";
					echo "</tr>";
					$lin = $lin."<tr><td>                            </td><td>                           </td><td>TOTAL ALMACEN ".$xa8."    </td><td>   ".number_format($tot_alma[$xalma],4)."</td></tr>";
					$snewbuffer = $snewbuffer."\n";
					$snewbuffer = $snewbuffer." "." "." "." "." "."TOTAL-ALMACEN-".trim($xa8)." "." ".number_format($tot_alma[$xalma], 4, '.', ',');
					if($n_utilidad == "U") 
						$snewbuffer = $snewbuffer." ".$tot_alma_margen[$xalma]." ".$tot_alma_igv[$xalma]." ".$tot_alma_total[$xalma];
				}
			}
		    	$irow++;
			$xalma	= $a7;  
			$xlinea	= $a6;  
			$xa8	= $a8;
		}
		fwrite($ft,$snewbuffer);
		fclose($ft);
		?>
		<?php 
		$fh = fopen($file, "a");
		fwrite($fh,$texto_impresion.PHP_EOL.PHP_EOL.PHP_EOL);
		fclose($fh);
		?>
  		</table>
	</form>
</body>
</html>

<?php
imprimir2( $lin, $col, $nom, $v_archivo, "REPORTE DE STOCK" );
if ($conector_id) 
	pg_close($conector_id);
$clase_error->_error();
?>

<?php
function obtenerComandoImprimir($file) {
	global $sqlca;
		
	$sql =	"SELECT
			trim(pc_samba),
			trim(prn_samba),
			trim(ip) 
		FROM
			pos_cfg 
		WHERE
			impcierre = true and pos = (SELECT par_valor from int_parametros where par_nombre='pos_consolida')
		ORDER BY
			tipo DESC,
			pos ASC";

	$rs = $sqlca->query($sql);
	if ($rs < 0) {
		echo "Error consultando POS\n";
		return false;
	}
	if ($sqlca->numrows() < 1)
		return true;

	$row = $sqlca->fetchRow();
	$smbc="lpr -H {$row[2]} -P {$row[1]} {$file}";
	return $smbc;
}

function espaciosA($q) {
	$ret = "";
	for ($q; $q > 0; $q--)
		$ret .= " ";
	return $ret;
}

function alineaNC($valores, $columnas) {
        $res = "";
        for ($i = 0; $i < count($columnas); $i++) {
                $ancho = $columnas[$i];
                $valor = $valores[$i];
                if (is_numeric($valor))
                	$valor = alinea($valor,1,$ancho);
                else if (strlen($valor) > $ancho)
                        $valor = substr($valor,0,$ancho);
                else
                        $valor = alinea($valor,0,$ancho);
                $res .= $valor;
        }
        return $res;
}

//fin de version antegua

} else { ?>

<!DOCTYPE html>
<html>
<head>
	<title>Items por Almacen - OpenSoft</title>
	<meta charset="UTF-8">
	<link rel="stylesheet" href="/sistemaweb/css/sistemaweb.css" type="text/css">
	<link rel="stylesheet" href="/sistemaweb/assets/css/style.css" type="text/css">
	<!--<script src="/sistemaweb/js/jquery-2.0.3.js" type="text/javascript"></script>-->
	<script src="/sistemaweb/assets/js/jquery/jquery-3.2.0.min.js" type="text/javascript"></script>
	<script src="/sistemaweb/inventarios/js/items_por_almacen.js"></script>
	
</head>
<body>
	<script src="/sistemaweb/js/calendario.js" type="text/javascript" ></script>
	<script src="/sistemaweb/js/overlib_mini.js" type="text/javascript" ></script>
	<script src="/sistemaweb/js/jquery-1.9.1.js" type="text/javascript"></script>
	<link rel="stylesheet" href="/sistemaweb/css/jquery-ui.css" />
	<link rel="stylesheet" href="/sistemaweb/helper/css/style.css" />
	<script src="/sistemaweb/js/jquery-ui.js"></script>
	<script type="text/javascript">

	$(window).load(function() {
		$( function() {
			//alert('hola');
			$.datepicker.regional['es'] = {
				closeText: 'Cerrar',
				prevText: '<Ant',
				nextText: 'Sig>',
				currentText: 'Hoy',
				monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
				monthNamesShort: ['Ene','Feb','Mar','Abr', 'May','Jun','Jul','Ago','Sep', 'Oct','Nov','Dic'],
				dayNames: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
				dayNamesShort: ['Dom','Lun','Mar','Mié','Juv','Vie','Sab'],
				dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sá'],
				weekHeader: 'Sm',
				dateFormat: 'dd/mm/yy',
				firstDay: 1,
				isRTL: false,
				showMonthAfterYear: false,
				yearSuffix: ''
			};

			$.datepicker.setDefaults( $.datepicker.regional[ "es" ] );

			$( "#fecha_inicio" ).datepicker({
				changeMonth: true,
				changeYear: true,
			});
		})
	})
	</script>

	<?php include "../menu_princ.php"; ?>
	<div id="footer">&nbsp;</div>
	<div id="cargardor" style="position: absolute;display: none"><img src="/sistemaweb/ventas_clientes/liquidacion_vales/cg.gif" /></div>

	<?php

	include('/sistemaweb/include/mvc_sistemaweb.php');
	include('reportes/t_items_por_almacen.php');
	include('reportes/m_items_por_almacen.php');

	//Variables de Entrada

	$hoy = date('d/m/Y');

	$model = new ModelItemsPorAlmacen;
	$template = new TemplateItemsPorAlmacen;

	$estaciones	= $model->GetAlmacen('T');
	$lineas		= $model->GetLinea();
	echo $template->Form($estaciones, $lineas, $hoy);
	?>
</body>
</html>

<?php }