<?php

class ReporteGeneralModel extends Model {

	function obtenerSeries() {
		global $sqlca;
			
		$sql = "SELECT                                                                                                                                        
				substring(TRIM(tab_elemento) for 2 from length(TRIM(tab_elemento))-1) as cod_docu,                                                        
				tab_desc_breve as desc_docu                                                                                                               
			FROM                                                                                                                                          
				int_tabla_general                                                                                                                         
			WHERE                                                                                                                                         
				tab_tabla ='08'
			ORDER BY
				cod_docu;";
						
		if ($sqlca->query($sql) < 0)
			return false;
			
		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {

			$a = $sqlca->fetchRow();
			$resultado[$i]['cod_docu'] 	= $a[0];
			$resultado[$i]['desc_docu'] 	= $a[1];

		}
			
		return $resultado;
	}

  	function busquedaGrupo($fecha_hasta,$seriesdocumentos,$dia_vencimiento,$vale,$codcliente,$cliente,$categoria,$serie){
	global $sqlca;

		$query = "SELECT DISTINCT
				cli.cli_grupo||' - '||TRIM(cli.cli_razsocial)||' TIPO: '||COALESCE(cli.cli_tipo,'IN')||' LIMITE: '||COALESCE(cli.cli_creditosol,0) as grupo,
				gen.tab_desc_breve||' '||TRIM(cab.ch_seriedocumento)||TRIM(cab.ch_numdocumento) as documento,
				TRIM(cab.ch_seriedocumento)||TRIM(cab.ch_numdocumento) as num_documento, 
				cab.nu_importetotal as importe,
				cab.dt_fechaemision as fechaemision,";

		if ($dia_vencimiento == "N") 
		    $query .= " cab.dt_fechavencimiento as fechavencimiento, ";
		else
           	    $query .= " cab.dt_fechavencimiento||' '||'Dias venc: '||nu_dias_vencimiento as fechavencimiento,";
		
 		    $query .= " CASE WHEN cab.ch_moneda='01' THEN 'S/.' END as monetotal,
				alma.ch_nombre_breve_almacen as almacen,
				cab.nu_importesaldo as saldo,
				cab.ch_tipdocumento as tipo
			FROM
				ccob_ta_cabecera as cab
					INNER JOIN
						ccob_ta_detalle as det on(cab.ch_tipdocumento = det.ch_tipdocumento and cab.ch_seriedocumento = det.ch_seriedocumento and cab.ch_numdocumento = det.ch_numdocumento)
					INNER JOIN
						int_clientes as cli on(cab.cli_codigo = cli.cli_codigo)
					LEFT JOIN
						int_num_documentos as docu on(cab.ch_tipdocumento = docu.num_tipdocumento)
					LEFT JOIN
						inv_ta_almacenes as alma on(alma.ch_almacen = docu.ch_almacen and alma.ch_sucursal = docu.ch_almacen)
					LEFT JOIN int_tabla_general as gen ON(cab.ch_tipdocumento = substring(TRIM(tab_elemento) for 2 from length(TRIM(tab_elemento))-1) and tab_tabla ='08'),
					(SELECT substring(TRIM(tab_elemento) for 2 from length(TRIM(tab_elemento))-1) as cod_mone, tab_desc_breve as desc_mone FROM int_tabla_general WHERE tab_tabla ='MONE') as mone
			WHERE
				det.dt_fechamovimiento <= TO_DATE('$fecha_hasta','DD/MM/YYYY')
				AND cab.nu_importesaldo > 0";

		if (count($seriesdocumentos) > 0) { 
			$query .= "
				AND cab.ch_tipdocumento IN (";
			for ($i = 0; $i < count($seriesdocumentos); $i++) {
				$serdocu = $seriesdocumentos[$i];
				if ($i > 0)
					$query .= ",";
				$query .= "'" . pg_escape_string($serdocu) . "'";
			}
			$query .= ") ";
		}else{
			$query .= "
				AND cab.ch_tipdocumento in ('01','09','10','11','15','20','21','22','35','55','56','59','60','70','71','72','99')";
		}

		
		if ($cliente == "N"){
		    $query .= "
				AND cli.cli_grupo = '$codcliente' ";
		}

		if ($categoria != "T") {
		    $query .= " AND cli.cli_tipo = '$categoria' ";
		}
			
		if ($serie != "") {
		    $query .= " AND cab.ch_seriedocumento = '$serie' ";
		}

		$query 	.= "
			ORDER BY
				grupo,
				fechaemision desc;";

		echo $query;
 	
		if ($sqlca->query($query)<=0)
			return $sqlca->get_error();
	    
		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
		
			$resultado[$i]['grupo']			= $a[0];
			$resultado[$i]['documento']		= $a[1];
			$resultado[$i]['num_documento'] 	= $a[2];
			$resultado[$i]['importe']		= $a[3];
			$resultado[$i]['fechaemision'] 		= $a[4];
			$resultado[$i]['fechavencimiento']	= $a[5];
			$resultado[$i]['monetotal']		= $a[6];
			$resultado[$i]['almacen']		= $a[7];
			$resultado[$i]['saldo']			= $a[8];
			$resultado[$i]['tipo']			= $a[9];
			
		}
		
		return $resultado;
  	}		

	function busquedaCliente($fecha_hasta,$seriesdocumentos,$dia_vencimiento,$vale,$codcliente,$cliente,$categoria,$serie){
	global $sqlca;

		$query = "SELECT DISTINCT
				cli.cli_codigo||' - '||TRIM(cli.cli_razsocial)||' TIPO: '||COALESCE(cli.cli_tipo,'IN')||' LIMITE: '||COALESCE(cli.cli_creditosol,0) as grupo,
				gen.tab_desc_breve||' '||TRIM(cab.ch_seriedocumento)||TRIM(cab.ch_numdocumento) as documento,
				TRIM(cab.ch_seriedocumento)||TRIM(cab.ch_numdocumento) as num_documento, 
				cab.nu_importetotal as importe,
				cab.dt_fechaemision as fechaemision,";

		if ($dia_vencimiento == "N") {
		    $query .= "
				cab.dt_fechavencimiento as fechavencimiento, ";
		}else{
           	    $query .= "
				cab.dt_fechavencimiento||' '||'Dias venc: '||nu_dias_vencimiento as fechavencimiento,";
		}
		
 		    $query .= "
				CASE WHEN cab.ch_moneda='01' THEN 'S/.' END as monetotal,
				cli.cli_creditosol as credito,
				alma.ch_nombre_breve_almacen as almacen,
				cab.nu_importesaldo as saldo,
				cab.ch_tipdocumento as tipo
			FROM
				ccob_ta_cabecera as cab
					INNER JOIN
						ccob_ta_detalle as det on(cab.ch_tipdocumento = det.ch_tipdocumento and cab.ch_seriedocumento = det.ch_seriedocumento and cab.ch_numdocumento = det.ch_numdocumento)
					INNER JOIN
						int_clientes as cli on(cab.cli_codigo = cli.cli_codigo)
					LEFT JOIN
						int_num_documentos as docu on(cab.ch_tipdocumento = docu.num_tipdocumento)
					LEFT JOIN
						inv_ta_almacenes as alma on(alma.ch_almacen = docu.ch_almacen and alma.ch_sucursal = docu.ch_almacen)
					LEFT JOIN int_tabla_general as gen
						ON(cab.ch_tipdocumento = substring(TRIM(tab_elemento) for 2 from length(TRIM(tab_elemento))-1) and tab_tabla ='08'),
					(SELECT substring(TRIM(tab_elemento) for 2 from length(TRIM(tab_elemento))-1) as cod_mone, tab_desc_breve as desc_mone FROM int_tabla_general WHERE tab_tabla ='MONE') as mone
			WHERE
				det.dt_fechamovimiento <= to_date('$fecha_hasta','DD/MM/YYYY')
				AND cab.nu_importesaldo > 0";

		if (count($seriesdocumentos) > 0) { 
			$query .= "
				AND cab.ch_tipdocumento IN (";
			for ($i = 0; $i < count($seriesdocumentos); $i++) {
				$serdocu = $seriesdocumentos[$i];
				if ($i > 0)
					$query .= ",";
				$query .= "'" . pg_escape_string($serdocu) . "'";
			}
			$query .= ") ";
		}else{
			$query .= "
				AND cab.ch_tipdocumento in ('01','09','10','11','15','20','21','22','35','55','56','59','60','70','71','72','99')";
		}

		
		if ($cliente == "N"){
		    $query .= "
				AND cli.cli_codigo = '$codcliente' ";
		}

		/*if ($precancelado == "N") {
		    $query .= " and cab.ch_precancelado = '$precancelado' ";
		}else{
           	    $query .= " and cab.ch_precancelado = '$precancelado' ";
		}*/

		if ($categoria != "T") {
		    $query .= "
				AND cli.cli_tipo = '$categoria' ";
		}
			
		if ($serie != "") {
		    $query .= "
				AND cab.ch_seriedocumento = '$serie' ";
		}

		$query 	.= "
			ORDER BY
				grupo,
				fechaemision desc;";

		echo $query;
 	
		if ($sqlca->query($query)<=0)
			return $sqlca->get_error();
	    
		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
		
			$resultado[$i]['grupo']			= $a[0];
			$resultado[$i]['documento']		= $a[1];
			$resultado[$i]['num_documento'] 	= $a[2];
			$resultado[$i]['importe']		= $a[3];
			$resultado[$i]['fechaemision'] 		= $a[4];
			$resultado[$i]['fechavencimiento']	= $a[5];
			$resultado[$i]['monetotal']		= $a[6];
			$resultado[$i]['credito']		= $a[7];
			$resultado[$i]['almacen']		= $a[8];
			$resultado[$i]['saldo']			= $a[9];
			$resultado[$i]['tipo']			= $a[10];
			
		}
		
		return $resultado;

  	}
	
	function busquedaClienteVales($fecha_hasta,$cliente,$codcliente){
	global $sqlca;

		$query="
			SELECT DISTINCT
				cli.cli_codigo||' - '||TRIM(cli.cli_razsocial)||' TIPO: '||COALESCE(cli.cli_tipo,'IN')||' LIMITE: '||COALESCE(cli.cli_creditosol,0) as grupo,
				'Vale: '||valc.ch_documento as documentoval,
				valc.nu_importe as importeval,
				valc.dt_fecha as fecha
			FROM
				val_ta_cabecera as valc
					INNER JOIN
						int_clientes as cli ON(valc.ch_cliente = cli.cli_codigo)
			WHERE
				valc.dt_fecha <= TO_DATE('$fecha_hasta','DD/MM/YYYY')
				AND valc.ch_liquidacion IS NULL";

		if ($cliente == "N"){
		    $query.= "
				AND cli.cli_codigo = '$codcliente' ";
		}

		$query.="
			ORDER BY
				grupo;";

		echo $query;
 	
		if ($sqlca->query($query)<=0)
			return $sqlca->get_error();
	    
		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
		
			$resultado[$i]['grupo']				= $a[0];
			$resultado[$i]['documentoval']			= $a[1];
			$resultado[$i]['importeval']			= $a[2];
			$resultado[$i]['fecha'] 			= $a[3];
			
		}
	
		return $resultado;
  	}
}
