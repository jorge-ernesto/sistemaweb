				<?php header("Content-type: application/vnd.ms-excel") ?>
    			<?php header("Content-Disposition: attachment; filename=reporte.xls"); 
    			    include_once('../include/mvc_acosa.php');
					include_once('../include/dbsqlca.php');
					include_once('../include/class.form.php');
					include_once('../include/class.form2.php');
					include_once('../include/reportes2.inc.php');
					include_once('../include/documentos.inc.php');
					include_once('../include/libexcel/Worksheet.php');
					include_once('../include/libexcel/Workbook.php');
														
					$sqlca = new pgsqlDB('localhost','postgres', 'postgres', 'integrado'); 
					
					$serie = $_REQUEST['serie'];
				    $fecha_ini = $_REQUEST['fecha_ini'];
				    $fecha_fin = $_REQUEST['fecha_fin'];
				    $codigo = $_REQUEST['codigo'];
				    $tipo_doc = $_REQUEST['tipo'];
				    $num_doc = $_REQUEST['numero'];
					$tmp = date("d/m/Y");
					global $sqlca;
				
					if($fecha_ini == $tmp && $fecha_fin == $tmp)
					{	
				     	$cond1 .= "AND det.dt_fac_fecha  = '$tmp'";
					}
					if($codigo != '')
					{
						$cond2 .= "AND det.cli_codigo  = '$codigo'";
					}
					if($tipo_doc != '')
					{
						$cond3 .= "AND det.ch_fac_tipodocumento  = '$tipo_doc'";
					}
					if($num_doc != '')
					{
						$cond4 .= "AND det.ch_fac_numerodocumento  = '$num_doc'";
					}
					if($serie != '')
					{
						$cond5 .= "AND det.ch_fac_seriedocumento  = '$serie'";
					}
					
					$query = "select ".
		          "det.cli_codigo as CLIENTE ".
				  ",cli.cli_razsocial as RAZON_SOCIAL ".
				  ",det.dt_fac_fecha as FECHA".
				  ",det.ch_fac_seriedocumento as SERIE ".
				  ", iif(det.ch_fac_tipodocumento = '10','FACTURA',iif(det.ch_fac_tipodocumento = '20','N/CREDITO',iif(det.ch_fac_tipodocumento = '11', 'N/DEBITO',iif(det.ch_fac_tipodocumento = '35', 'BOL/VENTA',NULL)))) as TIPO ".
				  ", det.ch_fac_numerodocumento as NUMERO ".
	              ", det.nu_fac_valorbruto as VALOR_VENTA ".
	              ", det.nu_fac_impuesto1 as IGV ".
	              ", det.nu_fac_valortotal as TOTAL_VENTA ".
				  ", det.ch_fac_credito as CREDITO ".
				  ", det.ch_fac_anticipo as ANTICIPO ".
				  "from fac_ta_factura_cabecera as det, int_clientes as cli ".
		          "where (det.dt_fac_fecha >= to_date('$fecha_ini','dd/mm/yyyy') AND det.dt_fac_fecha <= to_date('$fecha_fin','dd/mm/yyyy'))".
				  "AND ch_fac_tipodocumento <> '45' ".
				  "AND (det.ch_fac_seriedocumento = '001' OR det.ch_fac_seriedocumento = '501' )".
				  "AND det.cli_codigo = cli.cli_codigo ".
				  "".$cond1."".
				  "".$cond2."".
				  "".$cond3."".
				  "".$cond4."".
				  "".$cond5."".
				  " order by det.cli_codigo, det.dt_fac_fecha DESC, det.ch_fac_seriedocumento, det.ch_fac_numerodocumento";
			  	  $sqlca->query($query);
				  while($reg = $sqlca->fetchRow()){
					$registro[] = $reg;
				  }
			    
    			   // $rs = FacturasModel::ModelReportePDF(array('codigo'=>$codigo,'fecha_ini'=>$fecha_ini,'fecha_fin'=>$fecha_fin,'serie'=>$serie,'tipo'=>$tipo,'numero'=>$numero));
    			  // print_r($rs);
    			?>
    			<html>
				<head>
					<link rel="stylesheet" href="../cpagar/js/style.css" type="text/css" media="screen"/>
					<link rel="stylesheet" href="/sistemaweb/cpagar/js/print.css" type="text/css" media="print"/>
					<title>FACTURAS</title><table border="1" cellspacing="1" cellpadding="2" width="100%">
				</head>
				<body>
			    <tr class="letra_cabecera" bgcolor="#BBBBBB"> 
			      <td height="39" ><div align="center"><font size="2">FECHA</font></div></td>
			      <td ><div align="center"><font size="2">CLIENTE</font></div></td>
			      <td ><div align="center"><font size="2">RAZON SOCIAL</font></div></td>
			      <td ><div align="center"><font size="2">SERIE</font></div></td>
			      <td ><div align="center"><font size="2">TIPO</font></div></td>
			      <td ><div align="center"><font size="2">NUMERO</font></div></td>
			      <td ><div align="center"><font size="2">VALOR VENTA</font></div></td>
			      <td ><div align="center"><font size="2">IGV</font></div></td>
			      <td ><div align="center"><font size="2">TOTAL</font></div></td>
			      <td ><div align="center"><font size="2">CREDITO</font></div></td>
			      <td ><div align="center"><font size="2">ANTICIPO</font></div></td>
			    </tr>
			    <?php
			    for($i=0;$i<count($registro);$i++){
			    	//$row = $registro->fetchrow();
			    ?>	
  					<tr class="letra_cabecera">
  					<td width="13%" ><div align="center"><font size="2"><?php echo $registro[$i]['fecha']?></font></div></td>
  					<td ><div align="center"><font size="2"><?php echo $registro[$i]['cliente']?></font></div></td>
  					<td ><div align="left"><font size="2"><?php echo $registro[$i]['razon_social']?></font></div></td>
  					<td ><div align="center"><font size="2"><?php echo $registro[$i]['serie']?></font></div></td>
  					<td ><div align="center"><font size="2"><?php echo $registro[$i]['tipo']?></font></div></td>
  					<td ><div align="center"><font size="2"><?php echo $registro[$i]['numero']?></font></div></td>
  					<td ><div align="right"><font size="2"><?php echo $registro[$i]['valor_venta']?></font></div></td>
  					<td ><div align="right"><font size="2"><?php echo $registro[$i]['igv']?></font></div></td>
  					<td ><div align="right"><font size="2"><?php echo $registro[$i]['total_venta']?></font></div></td>
  					<td ><div align="center"><font size="2"><?php echo $registro[$i]['credito']?></font></div></td>
  					<td ><div align="center"><font size="2"><?php echo ($registro[$i]['anticipo']==''?'N':'S')?></font></div></td>
  					</tr>
  					
  					<?php
			    } 
			    
    			?>
    			</table></body></html>