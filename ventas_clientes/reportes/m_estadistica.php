<?php

class EstadisticaModel extends Model { 

	function buscar($almacen, $desde1, $desde2, $hasta1, $hasta2) {
		global $sqlca;
		
		$TP_L = '000002';
	     	$TP_A = '000003';
	     	$TP_S = '000006';
	     	$TP_M = '000005';
	     	$TP_O = '000010';
	     	$TP_W = '000009';

	     	$G84 = '11620301';
     		$G90 = '11620302';
     		$G97 = '11620303';
     		$GD2 = '11620304';
     		$G95 = '11620305';
     		$KD  = '11620306';
     		$GLP = '11620307';
		
		$postrans1 = "pos_trans".substr($desde1,6,4).substr($desde1,3,2);
		$postrans2 = "pos_trans".substr($hasta1,6,4).substr($hasta1,3,2);
		
		if($almacen=="TODOS") 
			$cond = "";
		else 
			$cond = " AND t.es='$almacen' ";
	    
		$sql = "SELECT * FROM 
				(	SELECT 
						t.es, 
						'ANTERIOR'::text, 
						trim(t.codigo), 
						sum(t.cantidad) 
					FROM 
						$postrans1 t 
					WHERE 
						t.tm='V' 
						AND t.tipo='C'  and grupo!='D' 
						$cond 
						AND date(t.dia) BETWEEN to_date('$desde1','DD/MM/YYYY') AND to_date('$desde2','DD/MM/YYYY') 
					GROUP BY 
						1,3 
				) AS A 
				UNION
				(	SELECT 
						t.es, 
						'ACTUAL'::text, 
						trim(t.codigo), 
						sum(t.cantidad) 
					FROM 
						$postrans2 t 
					WHERE 
						t.tm='V' 
						AND t.tipo='C'  and grupo!='D' 
						$cond 
						AND date(t.dia) BETWEEN to_date('$hasta1','DD/MM/YYYY') AND to_date('$hasta2','DD/MM/YYYY') 
					GROUP BY 
						1,3 
				) 
				UNION
				(	SELECT 
						t.es, 
						'ANTERIOR'::text, 
						lpad(art.art_tipo,6,'0'),  
						sum(t.cantidad) 
					FROM 
						$postrans1 t
						LEFT JOIN int_articulos art ON (art.art_codigo=t.codigo) 
					WHERE 
						tm='V' 
						AND tipo='M' 
						$cond 
						AND date(dia) BETWEEN to_date('$desde1','DD/MM/YYYY') AND to_date('$desde2','DD/MM/YYYY') 
					GROUP BY 
						1,3 
				) 
				UNION
				(	SELECT 
						t.es, 
						'ACTUAL'::text, 
						lpad(art.art_tipo,6,'0'), 
						sum(cantidad) 
					FROM 
						$postrans2 t
						LEFT JOIN int_articulos art ON (art.art_codigo=t.codigo) 
					WHERE 
						tm='V' 
						AND tipo='M' 
						$cond 
						AND date(dia) BETWEEN to_date('$hasta1','DD/MM/YYYY') AND to_date('$hasta2','DD/MM/YYYY') 
					GROUP BY 
						1,3 
				)
			ORDER BY 1,2,3;";
		echo $sql;

		if ($sqlca->query($sql) <= 0)
			return $sqlca->get_error();
	    
		$res = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$res[$i]['almacen'] 	= $a[0];
			$res[$i]['codigo'] 	= $a[2];
			$res[$i]['estado'] 	= $a[1];
			$res[$i]['cantidad'] 	= $a[3];
		}
		
		$alm = "";
		$est = "";
		$v = -1;
		$vec = Array();
		$flag = 0;
		$totcom = 0;
		$totmar = 0;
		for($k=0; $k<count($res); $k++) {
			if($res[$k]['almacen'] != $alm) { 
				$alm = $res[$k]['almacen'];
				$flag = 1;				
			}
			if($res[$k]['estado'] != $est) {
				$est = $res[$k]['estado'];	
				$v++;				
				$vec[$v][0] =  $res[$k]['almacen'];					
				$vec[$v][1] =  $res[$k]['estado'];
			}
					
			if($res[$k]['codigo']==$G84) {
				$vec[$v][2] =  $res[$k]['cantidad'];  $vec[$v]['totcom'] = $vec[$v]['totcom']+ $res[$k]['cantidad'];
			}if($res[$k]['codigo']==$G90) {
				$vec[$v][3] =  $res[$k]['cantidad'];  $vec[$v]['totcom'] = $vec[$v]['totcom']+ $res[$k]['cantidad'];
			}if($res[$k]['codigo']==$G95) {
				$vec[$v][4] =  $res[$k]['cantidad'];  $vec[$v]['totcom'] = $vec[$v]['totcom']+ $res[$k]['cantidad'];
			}if($res[$k]['codigo']==$G97) {
				$vec[$v][5] =  $res[$k]['cantidad'];  $vec[$v]['totcom'] = $vec[$v]['totcom']+ $res[$k]['cantidad'];
			}if($res[$k]['codigo']==$GD2) {
				$vec[$v][6] =  $res[$k]['cantidad'];  $vec[$v]['totcom'] = $vec[$v]['totcom']+ $res[$k]['cantidad'];
			}if($res[$k]['codigo']==$KD) {
				$vec[$v][7] =  $res[$k]['cantidad']; 
			}if($res[$k]['codigo']==$GLP) {
				$vec[$v][8] =  $res[$k]['cantidad']; 	
			}if($res[$k]['codigo']==$TP_L) {
				$vec[$v][9] =  $res[$k]['cantidad']; $vec[$v]['totmar'] = $vec[$v]['totmar']+ $res[$k]['cantidad'];
			}if($res[$k]['codigo']==$TP_A) {
				$vec[$v][10] =  $res[$k]['cantidad']; $vec[$v]['totmar'] = $vec[$v]['totmar']+ $res[$k]['cantidad'];
			}if($res[$k]['codigo']==$TP_S) {
				$vec[$v][11] =  $res[$k]['cantidad']; $vec[$v]['totmar'] = $vec[$v]['totmar']+ $res[$k]['cantidad'];
			}if($res[$k]['codigo']==$TP_M) {
				$vec[$v][12] =  $res[$k]['cantidad']; $vec[$v]['totmar'] = $vec[$v]['totmar']+ $res[$k]['cantidad'];
			}if($res[$k]['codigo']==$TP_O or $res[$k]['codigo']==$TP_W or trim($res[$k]['codigo'])=="") {
				$vec[$v][13] =  $res[$k]['cantidad']; $vec[$v]['totmar'] = $vec[$v]['totmar']+ $res[$k]['cantidad'];
			}			
		}
print_r($vec);
		return $vec;
  	}
	
	function obtenerAlmacenes($almacen) {
		global $sqlca;
		
		if(trim($almacen)=="") 
			$cond = ""; 
		else 
			$cond = "AND ch_almacen='$almacen' ";
		
		$sql = "SELECT
			    	ch_almacen,
			    	ch_almacen||' - '||ch_nombre_almacen
			FROM
			    	inv_ta_almacenes
			WHERE
			    	ch_clase_almacen='1'  
			    	$cond 
			ORDER BY
			    	ch_almacen;";
	
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

	function nomAlmacen($almacen) {
		global $sqlca;
		
		$sql = "SELECT
			    	ch_almacen,
			    	ch_almacen||' - '||ch_nombre_almacen
			FROM
			    	inv_ta_almacenes
			WHERE
			    	ch_clase_almacen='1'  
			    	AND ch_almacen='$almacen' 
			ORDER BY
			    	ch_almacen;";
	
		if ($sqlca->query($sql) < 0) 
			return false;
	
		$a = $sqlca->fetchRow();		
	
		return $a[1];
    	}
}
