<?php

class ReporteTurnoModel extends Model
{
    function reporte_turno() 
    {
		global $sqlca;
		
	     $sql="SELECT * FROM 
				(SELECT
					t.dia,
					sum(t.cantidad) as cantidad,
					sum(t.importe) as importe,
					t.turno,
					t.codigo,
					t.tipo
				FROM
					pos_trans201301 t
					JOIN int_articulos art ON (art.art_codigo = t.codigo)
				WHERE
					date_part('day', t.dia) BETWEEN 01 AND 02
					AND t.tipo = 'C'
				GROUP BY
					t.tipo,
					t.dia,
					t.turno,
					t.codigo
				ORDER BY
					t.dia,
					t.turno,
					t.codigo,
					t.tipo
				) AS C
				UNION 
				(SELECT
					t.dia,
					sum(t.cantidad) as cantidad,
					sum(t.importe) as importe,
					t.turno,
					'Market'::text tipo,
					t.tipo
				FROM
					pos_trans201301 t
					JOIN int_articulos art ON (art.art_codigo = t.codigo)
				WHERE
					date_part('day', t.dia) BETWEEN 01 AND 02
					AND t.tipo = 'M'
				GROUP BY
					t.tipo,
					t.dia,
					t.turno
				ORDER BY
					t.dia,
					t.turno,
					t.tipo)
			ORDER BY
				dia,
				turno,
				tipo,
				codigo;
		";
            
		if ($sqlca->query($sql) < 0) 
			return null;	
		
		$result = array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();

			$result[$i]['dia']	= $a[0];
			$result[$i]['turno']	= $a[3];
			$result[$i]['total'][trim($a[4]).'cantidad'] = $a[1];
			$result[$i]['total'][trim($a[4]).'importe'] = $a[2];

			/*if($dia != $resultado[$i]['dia']){
				if($turno != $resultado[$i]['turno']){
					@$result['total']['dia']['turno'][$turno][$codigo.'cantidad']	= $a[1];
					@$result['total']['dia']['turno'][$turno][$codigo.'importe']	= $a[2];
					$turno = $a[3];
				}
				$dia = $a[0];
			}

			/*$reg["dia"]=date("Y-m-d", strtotime($reg["dia"]));
			$reg['totales'][$ch_codigocombustible.'_galones'] += $nu_ventagalon;
			//@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales'][$ch_codigocombustible.'_galones'] += $nu_ventagalon;
    			$registro[] = $reg;*/
			
            	}

		return $result; 
		
	}
    
    function obtieneListaEstaciones() {

		global $sqlca;
	
		$sql = "SELECT
				ch_almacen,
				trim(ch_nombre_almacen)
			FROM
				inv_ta_almacenes
			WHERE
				ch_clase_almacen='1'
			ORDER BY
				ch_almacen";

		if ($sqlca->query($sql) < 0) 
			return false;	
		
	     while($reg = $sqlca->fetchRow()){
                $registro[] = $reg;
            }

            return $registro; 
             
		

    	}
		
}

