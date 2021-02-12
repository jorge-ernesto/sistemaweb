<?php
class EstadoCuentaModel extends Model {

	function ModelReportePDF($fecha) {
	global $sqlca;

		$sql = "SELECT DISTINCT                                                    
				 cab.cli_codigo as CLIENTE,                               
				 cli.cli_razsocial AS RAZONSOCIAL,                        
				 det.ch_tipdocumento AS TIPODOCUMENTO,                    
				 det.ch_seriedocumento AS SERIEDOCUMENTO,                 
				 det.ch_numdocumento AS NUMDOCUMENTO,                     
				 cab.ch_moneda AS MONEDA,                                 
				 cab.dt_fechaemision AS FECHAEMISION,                     
				 cab.dt_fechavencimiento AS FECHAVENCIMIENTO,             
				 cab.nu_importetotal AS IMPORTEINICIAL,                   
				 cab.nu_importesaldo AS SALDO                             
			FROM
				ccob_ta_cabecera cab,
				int_clientes cli,
				ccob_ta_detalle det   
			WHERE
				cab.ch_tipdocumento IN ('10','11','20','21','22')            
			 	AND cab.cli_codigo = cli.cli_codigo                        
				AND cab.ch_tipdocumento = det.ch_tipdocumento              
				AND cab.ch_seriedocumento = det.ch_seriedocumento          
				AND cab.ch_numdocumento = det.ch_numdocumento                                             
				AND det.dt_fechamovimiento <= to_date('$fecha','DD/MM/YYYY')
				AND cab.nu_importesaldo > 0 

		  	ORDER BY
				1,
				3,
				4,
				5; ";
	
		echo "....".$sql."....";

		if ($sqlca->query($sql)<=0)
			return $sqlca->get_error();
	    
		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();

			$resultado[$i]['CLIENTE']			= $a[0];
			$resultado[$i]['RAZONSOCIAL']			= $a[1];
			$resultado[$i]['TIPODOCUMENTO'] 		= $a[2];
			$resultado[$i]['SERIEDOCUMENTO']		= $a[3];
			$resultado[$i]['NUMDOCUMENTO'] 			= $a[4];
			$resultado[$i]['MONEDA']			= $a[5];
			$resultado[$i]['FECHAEMISION']			= $a[6];
			$resultado[$i]['FECHAVENCIMIENTO'] 		= $a[7];
			$resultado[$i]['IMPORTEINICIAL']		= $a[8];
			$resultado[$i]['SALDO']				= $a[9];
			
		}
		
		return $resultado;
		
	}

	function ModelReportePDFCLIENTE($fecha,$codcliente) {
	global $sqlca;

		$sql = "SELECT DISTINCT                                                    
				 cab.cli_codigo as CLIENTE,                               
				 cli.cli_razsocial AS RAZONSOCIAL,                        
				 det.ch_tipdocumento AS TIPODOCUMENTO,                    
				 det.ch_seriedocumento AS SERIEDOCUMENTO,                 
				 det.ch_numdocumento AS NUMDOCUMENTO,                     
				 cab.ch_moneda AS MONEDA,                                 
				 cab.dt_fechaemision AS FECHAEMISION,                     
				 cab.dt_fechavencimiento AS FECHAVENCIMIENTO,             
				 cab.nu_importetotal AS IMPORTEINICIAL,                   
				 cab.nu_importesaldo AS SALDO                             
			FROM
				ccob_ta_cabecera cab,
				int_clientes cli,
				ccob_ta_detalle det   
			WHERE
				cab.ch_tipdocumento IN ('10','11','20','21','22')            
			 	AND cab.cli_codigo = cli.cli_codigo                        
				AND cab.ch_tipdocumento = det.ch_tipdocumento              
				AND cab.ch_seriedocumento = det.ch_seriedocumento          
				AND cab.ch_numdocumento = det.ch_numdocumento                                             
				AND det.dt_fechamovimiento <= to_date('$fecha','DD/MM/YYYY')
				AND cab.nu_importesaldo > 0  ";

		if($todo != '01'){	
			$sql .= "AND cab.cli_codigo = '$codcliente'";
		}

		  $sql .= "ORDER BY
				1,
				3,
				4,
				5; ";
	
		echo "....".$sql."....";

		if ($sqlca->query($sql)<=0)
			return $sqlca->get_error();
	    
		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();

			$resultado[$i]['CLIENTE']			= $a[0];
			$resultado[$i]['RAZONSOCIAL']			= $a[1];
			$resultado[$i]['TIPODOCUMENTO'] 		= $a[2];
			$resultado[$i]['SERIEDOCUMENTO']		= $a[3];
			$resultado[$i]['NUMDOCUMENTO'] 			= $a[4];
			$resultado[$i]['MONEDA']			= $a[5];
			$resultado[$i]['FECHAEMISION']			= $a[6];
			$resultado[$i]['FECHAVENCIMIENTO'] 		= $a[7];
			$resultado[$i]['IMPORTEINICIAL']		= $a[8];
			$resultado[$i]['SALDO']				= $a[9];
			
		}
		
		return $resultado;
		
	}
}
