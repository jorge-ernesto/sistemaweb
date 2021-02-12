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
		    		''::text tipo,
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
			
			
		 while($reg = $sqlca->fetchRow()){
		     $reg["dia"]=date("Y-m-d", strtotime($reg["dia"]));
                $registro[] = $reg;
            }

            return $registro; 
		
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

