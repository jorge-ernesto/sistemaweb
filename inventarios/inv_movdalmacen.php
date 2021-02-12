<?php
extract($_REQUEST);

$fm = $_GET['fm'];
if($_POST) {
	
	$boton = $_POST['boton'];

	$fm = $_POST['fm'];
	if($boton=="Adicionar") {
?>
		<script languaje="JavaScript">
			location.href='inv_addmov.php?fm=<?php echo $fm;?>&flg=A';
		</script>
<?php
	} elseif($boton == "Modificar") {
		if(strlen($nform) > 0) {
?>
			<script languaje="JavaScript">
				location.href='inv_updmov.php?fm=<?php echo $fm;?>&nromov=<?php echo $nform;?>&flg=A';
			</script>
<?php 
		} else {  
?>
			<script languaje="JavaScript">
				alert(" Debe seleccionar un movimiento !!! ")
			</script>	
<?php   
		}
	} 
	
}

	require("../menu_princ.php");
	include("../functions.php");
	require("../clases/funciones.php");

	$funcion = new class_funciones;
	$clase_error = new OpensoftError;
	$coneccion = $funcion->conectar("","","","","");
	
	$query = "
			SELECT 
				trim(ch_almacen) AS cod,
				ch_nombre_almacen 
			FROM 
				inv_ta_almacenes  
			WHERE 
				ch_clase_almacen='1' 
			ORDER BY 
				cod";
	$v_xsqlalma = pg_exec($query);

	unset($numero_movimiento);
	unset($tran_codigo);

	tipoform($fm,$coneccion);

	if (isset($_REQUEST['diasd'])) {
		list($diad, $mesd, $anod) = explode('/',$_REQUEST['diasd']);
	} else {
		if (isset($_REQUEST['diad']))
			$diad = $_REQUEST['diad'];
		else
			$diad = "01";
		if (isset($_REQUEST['mesd']))
			$mesd = $_REQUEST['mesd'];
		else
			$mesd = @date("m");
		if (isset($_REQUEST['anod']))
			$anod = $_REQUEST['anod'];
		else
			$anod = @date("Y");
	}

	if (isset($_REQUEST['diasa'])) {
		list($diaa, $mesa, $anoa) = explode('/',$_REQUEST['diasa']);
	} else {
		if (isset($_REQUEST['diaa']))
			$diaa = $_REQUEST['diaa'];
		else
			$diaa = ultimoDia(@date("m"), @date("Y"));
		if (isset($_REQUEST['mesa']))
			$mesa = $_REQUEST['mesa'];
		else
			$mesa = @date("m");
		if (isset($_REQUEST['anoa']))
			$anoa = $_REQUEST['anoa'];
		else
			$anoa = @date("Y");
	}

	$fecini = $anod."-".$mesd."-".$diad." 00:00:00";
	$fecfin = $anoa."-".$mesa."-".$diaa." 23:59:59";

	$sqladd = " AND m.mov_fecha BETWEEN '$fecini' AND '$fecfin' ";

	if(isset($_REQUEST['m_almacen']) && $_REQUEST['m_almacen'] != '' && $_REQUEST['m_almacen'] != "all") {
		$sqlalm = " AND m.mov_almacen = '".$_REQUEST['m_almacen']."'";
	} else {
		$sqlalm = '';
		$_REQUEST['m_almacen'] = "all";
	}

	if(isset($_REQUEST['docref']) && $_REQUEST['docref'] != '') {
		$sqldocref = " AND m.mov_docurefe = '" . pg_escape_string($_REQUEST['docref']) . "' ";
	} else
		$sqldocref = "";

	if($_REQUEST['artic'] != '') {
		$sqlart = " AND m.art_codigo = '" . pg_escape_string($_REQUEST['artic']) . "' ";
	} else
		$sqlart = "";
	
	if($_REQUEST['boton'] == "Consultar" or (strlen($diad)>0 and strlen($mesd)>0 and strlen($anod)>0 and strlen($diaa)>0 and strlen($mesa)>0 and strlen($anoa)>0) or strlen($docref)>0 ){
		$sql2 = "
			SELECT 
				m.mov_numero,
				to_char(m.mov_fecha,'dd/mm/yyyy hh24:mi:ss') AS mov_fecha,
				m.mov_tipdocuref,
				m.mov_docurefe,
				m.mov_almaorigen,
				m.mov_almadestino,
				m.mov_almacen,
				m.art_codigo || ' - ' ||a.art_descripcion,
				m.mov_cantidad,
				m.mov_costounitario,
				m.com_serie_compra,
				m.com_num_compra,
				prove.pro_ruc|| ' - ' ||prove.pro_razsocial AS noproveedor
			FROM
				inv_movialma m
				LEFT JOIN int_proveedores prove ON(m.mov_entidad = prove.pro_codigo),
				int_articulos a
			WHERE 
				m.tran_codigo='$fm' AND m.art_codigo=a.art_codigo 
				" . $sqlalm . "
				" . $sqladd . "
				" . $sqldocref . "
				" . $sqlart;
	        
		$flg = "V";
		$xsql2 = pg_exec($coneccion,$sql2);
		$ilimit2 = pg_numrows($xsql2);	
	
		if($ilimit2 > 0) {
			$numeroRegistros = $ilimit2;
		}
	}

	if($boton == "Excel") {
			
		$sql2 = "
			SELECT 
				m.mov_numero,
				to_char(m.mov_fecha,'dd/mm/yyyy hh24:mi:ss') AS mov_fecha,
				m.mov_tipdocuref,
				m.mov_docurefe,
				m.mov_almaorigen,
				m.mov_almadestino,
				m.mov_almacen,
				m.art_codigo || ' - ' ||a.art_descripcion,
				m.mov_cantidad,
				m.mov_costounitario,
				m.com_serie_compra,
				m.com_num_compra,
				prove.pro_ruc|| ' - ' ||prove.pro_razsocial AS noproveedor
			FROM
				inv_movialma m
				LEFT JOIN int_proveedores prove ON(m.mov_entidad = prove.pro_codigo),
				int_articulos a
			WHERE 
				m.tran_codigo='$fm' AND m.art_codigo=a.art_codigo 
				" . $sqlalm . "
				" . $sqladd . "
				" . $sqlart . "
				" . $sqldocref;
	   
		$xsql2 = pg_exec($coneccion,$sql2);
		while ($reg = pg_fetch_row($xsql2)) { 
			$registro[] = $reg;
		}
				 
				$_SESSION['data_1010']	= $registro;
				$_SESSION['almacen']	= $_REQUEST['m_almacen'];
				$_SESSION['artic']	= $_REQUEST['artic'];
				$_SESSION['diasd'] = $_REQUEST['diasd'];
				$_SESSION['diasa'] = $_REQUEST['diasa'];
		?>
			<script languaje="JavaScript">
				location.href='/sistemaweb/inventarios/reporte_movalmacen_excel.php';
			</script>
		<?php 
		} 


	if ($_REQUEST['boton'] == "Consultar")
		unset($_REQUEST['pagina']);


	if($flg == "A") {
		rangodefechas();
		$diad = $zdiad; 
		$mesd = $zmesd; 
		$anod = $zanod; 
		
		$diaa = $zdiaa; 
		$mesa = $zmesa; 
		$anoa = $zanoa;

		$fechad = $anod."-".$mesd."-".$diad." 00:00:00";
		$fechaa = $anoa."-".$mesa."-".$diaa." 23:59:59";
	}
?>

<CENTER>MOVIMIENTOS DE <?php echo $descform; ?></CENTER><br/>
	<script src="/sistemaweb/js/calendario.js" type="text/javascript" ></script>
	<script src="/sistemaweb/js/overlib_mini.js" type="text/javascript" ></script>
	<script src="/sistemaweb/utils/cintillo.js" type="text/javascript" ></script>

	<script language="javascript">
		var miPopup
		function abreart(){
			miPopup = window.open("escogeart.php","miwin","width=600,height=350,scrollbars=yes")
			miPopup.focus()
		}

		function enviadatos() {}
	</script>

<form action="" method="post" name="formular">
	<table border="0" width="100%"> 
	<tr>
		<th>Desde :</th>
		<th align="left">
			<input type="text" name="diasd" size="10" value="<?php echo $diad.'/'.$mesd.'/'.$anod ?>" readonly="true"/>&nbsp;
			<a href="javascript:show_calendar('formular.diasd');">
				<img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/>
			</a>
			<div id="overDiv" style="position:absolute; visibility:hidden; z-index:0;">
		</th>
		<th>Hasta:</th>
		<th align="left">
			<input type="text" name="diasa" size="10" value="<?php echo $diaa.'/'.$mesa.'/'.$anoa ?>" readonly="true"/>&nbsp;
			<a href="javascript:show_calendar('formular.diasa');">
				<img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/>
			</a>
			<div id="overDiv" style="position:absolute; visibility:hidden; z-index:0;">
		</th>
		<th>Almacen : </th>
		<th align="left">
			<select name="m_almacen" tabindex="2">
				<option value="all">Todos los Almacenes</option>
				<?php
				for($i = 0; $i < pg_numrows($v_xsqlalma); $i++){		
					$k_alma1 = pg_result($v_xsqlalma,$i,0);	
					$k_alma2 = pg_result($v_xsqlalma,$i,1);
					if (trim($k_alma1) == trim($_REQUEST['m_almacen'])) { 
						echo "<option value='".$k_alma1."' selected >".$k_alma1." -- ".$k_alma2." </option>";	
					} else {
						echo "<option value='".$k_alma1."' >".$k_alma1." -- ".$k_alma2." </option>";	
					}
				}
				?>
			</select>	  
		</th>
		<th>No Doc. Ref :</th>
		<th align="left">
			<input type="text" name="docref" size="10" value="<?php echo $docref?>" />
		</th>
		<th>Articulo :</th>
		<th align="left">
			<input type="text" name="artic" value="<?php echo $artic; ?>" size="17" maxlength="13">
			<input name="imglinea" type="image" src="/sistemaweb/images/help.gif" width="16" height="15" border="0" onClick="abreart()">
			<?php
			if(strlen($artic) > 0) {
				$xsqlart = pg_exec($coneccion,"select art_descripcion from int_articulos where art_codigo='".$artic."' ");
					if(pg_numrows($xsqlart) > 0) { 
						$descart = pg_result($xsqlart,0,0);  
						echo $descart; 
					}
			}
			?>
		</th>
		<th>
			<input type="submit" name="boton" value="Consultar">
		</th>
	</tr>
	</table>
	<input type="hidden" name="fm" value='<?php echo $fm;?>'><br>	
	<?php
		$var_pers = "fm=".$fm."&diad=".$diad."&mesd=".$mesd."&anod=".$anod."&diaa=".$diaa."&mesa=".$mesa."&anoa=".$anoa."&m_almacen=".$_REQUEST['m_almacen'] . "&docref=" . $_REQUEST['docref'] . "&artic=" . $_REQUEST['artic'];
		include("../maestros/pagina.php");
		$bddsql = " limit $tamPag offset $limitInf ";
	?>
	<table width="10%">
		<tr>
			<td width="10%">
				<input type="submit" name="boton" value="Adicionar">
			</td>
			<td width="10%">
				<input type="submit" name="boton" value="Excel">
			</td>

		</tr>
	</table><br/><br/>
	
	<table border="0" cellpadding="0" cellspacing="0" width="100%" style="border: 1px solid;">
		
		<tr>
			<th style="border-bottom: 1px solid #336699;"></th>
			<th style="border-bottom: 1px solid #336699;">Nro. FORMULARIO</th>
			<th style="border-bottom: 1px solid #336699;">FECHA</th>
			<th style="border-bottom: 1px solid #336699;">PROVEEDOR</th>
			<th style="border-bottom: 1px solid #336699;">Nro. O/C</th>
			<th style="border-bottom: 1px solid #336699;">Nro. DOCUMENTO</th>
			<th style="border-bottom: 1px solid #336699;">ORI</th>
			<th style="border-bottom: 1px solid #336699;">DEST</th>
			<th style="border-bottom: 1px solid #336699;">ALM</th>
			<th style="border-bottom: 1px solid #336699;">ARTICULO</th>
			<th style="border-bottom: 1px solid #336699;">COSTO UNI.</th>
			<th style="border-bottom: 1px solid #336699;">CANTIDAD</th>
		</tr>
		
		<?php
		
			$sqladd = " AND m.mov_fecha BETWEEN '$fecini' AND '$fecfin' ";
		
			$sql2 = "SELECT 
					m.mov_numero,
					to_char(m.mov_fecha,'dd/mm/yyyy hh24:mi') AS mov_fecha,
					m.mov_tipdocuref,
					m.mov_docurefe,
					m.mov_almaorigen,
					m.mov_almadestino,
					m.mov_almacen,
					m.art_codigo || ' - ' ||a.art_descripcion,
					m.mov_cantidad,
					m.mov_costounitario,
					m.com_serie_compra,
					m.com_num_compra,
					prove.pro_ruc|| ' - ' ||prove.pro_razsocial AS noproveedor
				FROM 
					inv_movialma m
					LEFT JOIN int_proveedores prove ON(m.mov_entidad = prove.pro_codigo),
					int_articulos a
				WHERE 
					m.tran_codigo		= '$fm' 
					AND m.art_codigo	= a.art_codigo 
					".$sqlalm." 
					" . $sqladd . "
					" . $sqlart . "
					" . $sqldocref . "
				ORDER BY 
					m.mov_fecha desc  ".$bddsql."  ";
//echo "<pre>";
//print_r($sql2);
//echo "</pre>";

		
		
			if($ilimit2 > 0) {
				while($irow2 < $ilimit2) {
					$a0 = pg_result($xsql2,$irow2,0);
					$a1 = pg_result($xsql2,$irow2,1);
					$a2 = pg_result($xsql2,$irow2,2);
					$a3 = pg_result($xsql2,$irow2,3);
					$a4 = pg_result($xsql2,$irow2,4);
					$a5 = pg_result($xsql2,$irow2,5);
					$a6 = pg_result($xsql2,$irow2,6);
					$a7 = pg_result($xsql2,$irow2,7);
					$a8 = pg_result($xsql2,$irow2,8);
					$a9 = pg_result($xsql2,$irow2,9);
					$a10 = pg_result($xsql2,$irow2,10);
					$a11 = pg_result($xsql2,$irow2,11);
					$noproveedor	= pg_result($xsql2,$irow2,12);

					
					if($nform == $a0) {
		?>
						<tr bgcolor="" onMouseOver="this.style.backgroundColor='#4A9DFB';this.style.cursor='pointer';" onMouseOut="this.style.backgroundColor='';" >
						<?php echo "<td><input type='radio' name='nform' value='".$a0."' checked></td>";
					} else {
						
						?>
						<tr  bgcolor="" onMouseOver="this.style.backgroundColor='#4A9DFB';this.style.cursor='pointer';" onMouseOut="this.style.backgroundColor='';" onClick="javascript:mostrarCintillo('<?php echo $a0;?>','<?php echo $fm;?>','<?php echo substr($a1,0,16); ?>','<?php echo $a11;?>');">
						<?php echo "<td><input type='radio' name='nform' value='".$a0."'></td>";
					}

					$rows = "
							<td align='center'>".$a0."</td>
							<td align='center'>".$a1."</td>
							<td align='left'>".$noproveedor."</td>
							<td align='center'>".$a10." - ".$a11."</td>
							<td align='center'>".$a2." - ".$a3."</td>
							<td align='center'>".$a4."</td>
							<td align='center'>".$a5."</td>
							<td align='center'>".$a6."</td>
							<td align='left'>".$a7."</td>
							<td align='center'>".$a9."</td>
							<td align='center'>".$a8."</td>
						</tr>";

					echo $rows;

					/*echo "<td>".$a0."</td><td>&nbsp;".$a1."</td><td>".$a12."</td><td>&nbsp;".$a2." - ".$a3."</td>";
					echo "<td>".$a4."</td><td>&nbsp;".$a5."</td><td>&nbsp;".$a6."</td>";
					echo "<td>".$a7."</td><td><p align='right'>".$a8."</p></td><td><p align='center'>".$a9."</p></td><td>".$a10."</td></tr>";*/

					$irow2++;


				}

		

			} else {
			echo '<td colspan="13" align="center">NO EXISTEN ARTICULOS PARA LA CONSULTA !!!</td>';
			}
						?>
	</table><br/><br/>
	<table width="100%">
		<tr>
			<td width="50%">
				<input type="submit" name="boton" value="Adicionar">
			</td>
		</tr>
	</table>
</form>
<div style="border-top: 1px solid rgb(0,0,0);">
	<center><br/></center>
</div>

<?php 
	pg_close($coneccion);
