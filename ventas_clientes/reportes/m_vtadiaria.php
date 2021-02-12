<?php

class VtaDiariaModel extends Model {

	function obtenerCenCos($almacen) {
		global $sqlca;
		
		$sql = "SELECT
			    	tab_elemento,
			    	tab_descripcion 
			FROM
			    	int_tabla_general
			WHERE
			    	tab_tabla='LPRE' AND tab_elemento!='000000'
			ORDER BY
			    	tab_descripcion;";
	
		if ($sqlca->query($sql) < 0) 
			return false;
	
		$result = Array();
		$result['TODOS'] = "TODOS";
		
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    	$a = $sqlca->fetchRow();		    
		    	$result[$a[0]] = $a[1];
		}		
	
		return $result;
    	}

    	function busqueda($almacen, $dia1, $turno1, $dia2, $turno2, $busqueda, $find) {
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
			$cond = " AND pdd.dt_dia BETWEEN to_date('$dia1', 'dd/mm/yyyy') and to_date('$dia2', 'dd/mm/yyyy') ";
		} else {
			$cond = " AND pdd.dt_dia||to_char(pdd.ch_posturno,'99') 
				BETWEEN to_date('$dia1', 'dd/mm/yyyy')||to_char($turno1,'99') and to_date('$dia2', 'dd/mm/yyyy')||to_char($turno2,'99') "; 
		}
		
		$sql = "SELECT 	
				pdd.ch_almacen as almacen,
				pdd.ch_tipo_deposito as tipo,
				pdd.ch_valida as valida,
				pdd.dt_dia as dia,
				pdd.ch_posturno as turno,
				pdd.ch_codigo_trabajador as codtrab,
				TRIM(pdd.ch_codigo_trabajador)||' - '||TRIM(ch_apellido_paterno)||' '||TRIM(ch_apellido_materno)||' '||TRIM(ch_nombre1)||' '||trim(ch_nombre2) as trabajador,
				to_char(pdd.dt_fecha,'DD/MM/YYYY HH24:MI:SS') as fecha,
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
				pdd.ch_almacen='$almacen'				
				$cond
				$cond2
			ORDER BY  
				dia, turno, codtrab, seq";

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
		    	$resultado[$i]['seq'] 		= $a[8];
		    	$resultado[$i]['num'] 		= $a[9];
		    	$resultado[$i]['moneda'] 	= $a[10];
		    	$resultado[$i]['cambio'] 	= $a[11];
		    	$resultado[$i]['soles'] 	= $a[12];
		    	$resultado[$i]['dolares']	= $a[13];
		    	$resultado[$i]['usuario'] 	= $a[14];
		    	$resultado[$i]['ip'] 		= $a[15];
		    	$resultado[$i]['observacion1'] 	= $a[16];
		    	
		    	if(trim($a[10])!="01" and $a[13]>0) {
		    		$resultado[$i]['denominacion'] 	= "Billetes";
		    	} else {
			    	if($a[17]>0 and $a[18]==0){
			    		$resultado[$i]['denominacion'] 	= "Billetes";
			    	} else {
			    		if($a[17]==0 and $a[18]>0){
			    			$resultado[$i]['denominacion'] 	= "Monedas";
			    		} else {
			    			if($a[17]>0 and $a[18]>0){
			    				$resultado[$i]['denominacion'] 	= "B y M";
			    			} else {
			    				$resultado[$i]['denominacion'] 	= "Ninguna";
			    			}
			    		}
			    	}
			}		    		
		    	
		    	if(trim($a[2])=="S" or trim($a[2])=="s") {		    	
				$sem++;	
				$semsol = $semsol + $a[12];								
				$semdol = $semdol + $a[13];	    	
			}
		    	$can++;
			$totsol = $totsol + $a[12];
			$totdol = $totdol + $a[13];			
		}
		$res['detalles'] 	  = $resultado;
		$res['totales']['sem'] 	  = $sem;
		$res['totales']['semsol'] = $semsol;
		$res['totales']['semdol'] = $semdol;
		$res['totales']['can'] 	  = $can;
		$res['totales']['totsol'] = $totsol;
		$res['totales']['totdol'] = $totdol;		

		return $res;
    	}

	function validar($conf, $vec_check, $val, $almacen, $dia, $turno) {
		global $sqlca;
	
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
						ch_valida = 'S'
					WHERE
						ch_almacen = '$almacen'
						AND dt_dia = '$dia'
						AND ch_posturno = $turno 
						AND ch_codigo_trabajador = '$trabajador'
						AND ch_numero_documento = '$documento';";
					//echo "sql valida: ".$sql;
		
				if ($sqlca->query($sql)==-1) 
					return 0;
			}
		}		
			
		return 1;
	}
}
?>
