<?php

class DepositosPosModel extends Model {

	function obtenerAlmacenes($almacen) {
		global $sqlca;
		
		$sql = "SELECT
			    	ch_almacen,
			    	ch_almacen||' - '||ch_nombre_almacen
			FROM
			    	inv_ta_almacenes
			WHERE
			    	ch_clase_almacen='1'
			ORDER BY
			    	ch_almacen;";
	
		if ($sqlca->query($sql) < 0) 
			return false;
	
		$result = Array();
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    	$a = $sqlca->fetchRow();		    
		    	$result[$a[0]] = $a[1];
		}
	
		return $result;
    	}

    	function busqueda($almacen, $dia1, $turno1, $dia2, $turno2, $busqueda, $find, $tipomoneda) {
		global $sqlca;
				
		if (trim($find) != "" and trim($busqueda) != "") {
			if ($find == "T") // trabajador
				$cond2 = " AND ptt.ch_codigo_trabajador||ptt.ch_apellido_paterno||ptt.ch_apellido_materno||ptt.ch_nombre1||ptt.ch_nombre2 LIKE '%".$busqueda."%' ";
			if ($find == "C") // correlativo
				$cond2 = " AND pdd.ch_numero_correl LIKE '%".$busqueda."%' ";
			if ($find == "D") // numero de documento
				$cond2 = " AND pdd.ch_numero_documento LIKE '%".$busqueda."%' ";
			if ($find == "S") // serie
				$cond2 = " AND pdd.ch_serie1||pdd.ch_serie2||pdd.ch_serie3 LIKE '%".$busqueda."%' ";												
		}		
				
		if (trim($turno1) == "" and trim($turno2) == "") {
			$cond = "
				pdd.dt_dia BETWEEN to_date('$dia1', 'dd/mm/yyyy') and to_date('$dia2', 'dd/mm/yyyy') ";
		} else {
			$cond = "
				pdd.dt_dia||to_char(pdd.ch_posturno,'99') BETWEEN to_date('$dia1', 'dd/mm/yyyy')||to_char($turno1,'99') and to_date('$dia2', 'dd/mm/yyyy')||to_char($turno2,'99') ";
		}

		if($tipomoneda == '00'){	// Todos - sin filtro
			$deno = "";
		}elseif($tipomoneda == '01'){	// Monedas - Solo soles(aqui no circulan monedas de dolares)
			$deno = " AND (pdd.ch_moneda = '01' AND pdd.nu_mon200+pdd.nu_mon100+pdd.nu_mon50+pdd.nu_mon20+pdd.nu_mon10 = 0 AND pdd.nu_mon5+pdd.nu_mon2+pdd.nu_mon1+pdd.nu_mon050+pdd.nu_mon020+pdd.nu_mon010 > 0)";
//			$deno = " AND ( pdd.nu_mon200+pdd.nu_mon100+pdd.nu_mon50+pdd.nu_mon20+pdd.nu_mon10 = 0 AND pdd.nu_mon5+pdd.nu_mon2+pdd.nu_mon1+pdd.nu_mon050+pdd.nu_mon020+pdd.nu_mon010 > 0 )";
		}elseif($tipomoneda == '02'){	// Billetes
			$deno = " 
AND (
 (pdd.ch_moneda = '01' AND pdd.nu_mon200+pdd.nu_mon100+pdd.nu_mon50+pdd.nu_mon20+pdd.nu_mon10 > 0 AND pdd.nu_mon5+pdd.nu_mon2+pdd.nu_mon1+pdd.nu_mon050+pdd.nu_mon020+pdd.nu_mon010 = 0) OR
 (pdd.ch_moneda = '02' AND pdd.nu_importe > 0 )
)";
//			$deno ="AND
//				(
//					pdd.ch_moneda != '01' AND pdd.nu_importe > 0
//					OR pdd.nu_mon200+pdd.nu_mon100+pdd.nu_mon50+pdd.nu_mon20+pdd.nu_mon10 > 0 AND pdd.nu_mon5+pdd.nu_mon2+pdd.nu_mon1+pdd.nu_mon050+pdd.nu_mon020+pdd.nu_mon010 = 0
//				) ";
		}elseif($tipomoneda == '03'){	// Monedas y Billetas
			$deno = "AND (pdd.ch_moneda = '01' AND  pdd.nu_mon200+pdd.nu_mon100+pdd.nu_mon50+pdd.nu_mon20+pdd.nu_mon10 > 0 AND pdd.nu_mon5+pdd.nu_mon2+pdd.nu_mon1+pdd.nu_mon050+pdd.nu_mon020+pdd.nu_mon010 > 0)";
//			$deno ="AND ( pdd.nu_mon200+pdd.nu_mon100+pdd.nu_mon50+pdd.nu_mon20+pdd.nu_mon10 > 0 AND pdd.nu_mon5+pdd.nu_mon2+pdd.nu_mon1+pdd.nu_mon050+pdd.nu_mon020+pdd.nu_mon010 > 0 )";
		}


		if($almacen != 'TODOS'){
			$cond3 = " AND pdd.ch_almacen='$almacen' ";
		}

//		pdd.ch_almacen='$almacen'

		$sql = "SELECT 	
				pdd.ch_almacen as almacen,
				pdd.ch_tipo_deposito as tipo,
				pdd.ch_valida as valida,
				pdd.dt_dia as dia,
				pdd.ch_posturno as turno,
				pdd.ch_codigo_trabajador as codtrab,
				TRIM(pdd.ch_codigo_trabajador)||' - '||TRIM(ch_apellido_paterno)||' '||TRIM(ch_apellido_materno)||' '||TRIM(ch_nombre1)||' '||trim(ch_nombre2) as trabajador,
				to_char(pdd.dt_fecha,'DD/MM/YYYY HH24:MI:SS') as fecha,
				to_char(pdd.ch_fecha_actualizo,'DD/MM/YYYY HH24:MI:SS') as fechaact,
				pdd.ch_numero_correl as seq,
				pdd.ch_numero_documento as num,
				pdd.ch_moneda as moneda,
				pdd.nu_tipo_cambio as cambio,
				CASE
					WHEN pdd.ch_moneda='01' THEN pdd.nu_importe
					ELSE 0
				END as importesoles,
				CASE
					WHEN pdd.ch_moneda='02' THEN pdd.nu_importe
					ELSE 0
				END as importedolares,
				pdd.ch_usuario as usuario,
				pdd.ch_ip as ip,
				pdd.ch_serie1 as observacion1,
				pdd.nu_mon200+pdd.nu_mon100+pdd.nu_mon50+pdd.nu_mon20+pdd.nu_mon10 AS bilbil,
				pdd.nu_mon5+pdd.nu_mon2+pdd.nu_mon1+pdd.nu_mon050+pdd.nu_mon020+pdd.nu_mon010 as monmon	
			FROM
				pos_depositos_diarios pdd
				LEFT JOIN pla_ta_trabajadores ptt ON (pdd.ch_codigo_trabajador = ptt.ch_codigo_trabajador)
			WHERE
				$cond
				$cond3
				$cond2
				$deno
			ORDER BY  
				fecha, turno, seq, codtrab";

		//echo $sql;

		if ($sqlca->query($sql) < 0) 
			return false;

		$resultado = Array();
		$res 	   = Array();

		$can    = 0;
		$sem    = 0;
		$totsol = 0;
		$totdol = 0;
		$semsol = 0;								
		$semdol = 0;
										
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();

		    	$resultado[$i]['almacen'] 	= $a[0];
		    	$resultado[$i]['tipo'] 		= $a[1];
		    	$resultado[$i]['valida'] 	= $a[2];
		    	$resultado[$i]['dia'] 		= $a[3];
		    	$resultado[$i]['turno'] 	= $a[4];
		    	$resultado[$i]['codtrab'] 	= $a[5];
		    	$resultado[$i]['trabajador'] 	= $a[6];
				$resultado[$i]['fecha'] 	= $a[7];
				$resultado[$i]['fechaact'] 	= $a[8];
		    	$resultado[$i]['seq'] 		= $a[9];
		    	$resultado[$i]['num'] 		= $a[10];
		    	$resultado[$i]['moneda'] 	= $a[11];
		    	$resultado[$i]['cambio'] 	= $a[12];
		    	$resultado[$i]['soles'] 	= $a[13];
		    	$resultado[$i]['dolares']	= $a[14];
		    	$resultado[$i]['usuario'] 	= $a[15];
		    	$resultado[$i]['ip'] 		= $a[16];
				$resultado[$i]['observacion1'] 	= $a[17];
				$resultado[$i]['bilbil'] 	= $a[18];
				$resultado[$i]['monmon'] 	= $a[19];
		    	
		    	if(trim($a[11])!="01" and $a[14]>0) {
		    		$resultado[$i]['denominacion'] 	= "Billetes";
				$billetes = $billetes + $a[13];
		    	} else {
			    	if($a[18]>0 and $a[19]==0){
						$resultado[$i]['denominacion'] 	= "Billetes";
					$billetes = $billetes + $a[13];
			    	} else {
			    		if($a[18]==0 and $a[19]>0){
							$resultado[$i]['denominacion'] 	= "Monedas";
						$monedas = $monedas + $a[13];
			    		} else {
			    			if($a[18]>0 and $a[19]>0){
								$resultado[$i]['denominacion'] 	= "B y M";
								$monedas = $monedas + $a[13];
			    			} else {
			    				$resultado[$i]['denominacion'] 	= "Ninguna";
			    			}
			    		}
			    	}
			}		    		
		    	
		    	if(trim($a[2])=="S" or trim($a[2])=="s") {		    	

				$sem++;	
				$semsol = $semsol + $a[13];								
				$semdol = $semdol + $a[14];

				if(trim($a[11])!="01" and $a[14]>0) {
					$sbilletes = $sbilletes + $a[13];
			    	} else {
				    	if($a[18]>0 and $a[19]==0){
						$sbilletes = $sbilletes + $a[13];
						} else {
			    			if($a[18]>0 and $a[19]>0){
			    				$resultado[$i]['denominacion'] 	= "B y M";
							$smonedas = $smonedas + $a[13];
			    			}
					}
				}
			}		

		    	$can++;
			$totsol = $totsol + $a[13];
			$totdol = $totdol + $a[14];			
		}

		$res['detalles'] 	  	= $resultado;
		$res['totales']['sem'] 	  	= $sem;
		$res['totales']['semsol']	= $semsol;
		$res['totales']['semdol']	= $semdol;
		$res['totales']['can'] 	  	= $can;
		$res['totales']['totsol'] 	= $totsol;
		$res['totales']['totdol'] 	= $totdol;	
		$res['totales']['billetes'] 	= $billetes;
		$res['totales']['sbilletes'] 	= $sbilletes;
		$res['totales']['monedas']	= $monedas;
		$res['totales']['smonedas'] 	= $smonedas;
		//$res['sql'] = $sql;

		return $res;
    	}

	function validar($conf, $vec_check, $val, $almacen, $dia, $turno) {
		global $sqlca;
		
		$flag = 0;
		
		// Validando dia y turno consolidados
		for ($i=0; $i<count($val); $i++) {		
			if ($vec_check[$i] == "S" and $conf[$i] == "F" and $flag == 0) {
				$d = explode("+", $val[$i]);			
				$dd = $d[1];
				$tt = $d[2]; 
				$rpta = DepositosPosModel::validaConsolidacion($dd, $tt, $almacen);
				if($rpta == 1) {
					$flag = 1;
					return 0;
				}
			}
		}
	
		for ($i=0; $i<count($val); $i++) {
		
			if ($vec_check[$i] == "S" and $conf[$i] == "F") {
			
				$d = explode("+", $val[$i]);	
				$almacen = $d[0];
				$dia = $d[1];
				$turno = $d[2];
				$trabajador = $d[3];				
				$documento = $d[4];
				
				$sql = "UPDATE 
						pos_depositos_diarios
					SET
						ch_valida = 'S',
						ch_fecha_actualizo = NOW()
					WHERE
						ch_almacen = '$almacen'
						AND dt_dia = '$dia'
						AND ch_posturno = $turno 
						AND ch_codigo_trabajador = '$trabajador'
						AND ch_numero_documento = '$documento';";
					echo "sql valida: ".$sql;
		
				if ($sqlca->query($sql)==-1) 
					return 0;
			}
		}		
			
		return 1;
	}
	
	function validaConsolidacion($dia, $turno, $almacen) {
		global $sqlca;

		$sql = " SELECT validar_consolidacion('$dia',$turno,'$almacen') ";

		echo $sql;

		$sqlca->query($sql);

		$estado = $sqlca->fetchRow();

		echo "devolvio:\n";
		var_dump($estado);

		if($estado[0] == 1){
			return 1;//Consolidado
		}else{
			return 0;//No consolidado
		}

	}
	
}
