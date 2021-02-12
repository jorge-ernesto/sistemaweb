<?php

class DocumentosModel extends Model {

	function ObtenerAlmacenes() {
		global $sqlca;
		
		$sql = "
			SELECT
				ch_almacen,
				ch_almacen||' - '||ch_nombre_almacen
			FROM
				inv_ta_almacenes
			WHERE
				ch_clase_almacen = '1'
			ORDER BY
				ch_almacen;
			";
	
		if ($sqlca->query($sql) < 0) 
			return false;
	
		$result = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    	$a = $sqlca->fetchRow();		    
		    	$result[$a[0]] = $a[1];
		}
	
		return $result;

    	}
	
	function Paginacion($documentos){
		global $sqlca;

		if($documentos == "TODOS")
			$condicion = "";
		else if(!empty($documentos)){
			$condicion ="
			WHERE
				num_tipdocumento = '$documentos'
			";
		}else{
			$condicion = "";	
		}

		$query= "
			select 
			num_tipdocumento, 
		  	num_seriedocumento, 
		  	num_descdocumento, 
		  	num_longdocumento, 
                   trim(num_numactual),
			 
                  	num_tipdocumento||num_seriedocumento,
 			ch_almacen

             		from 

			int_num_documentos order by 

		  	num_tipdocumento, 
                  	num_seriedocumento" ;

		if ($sqlca->query($query) < 0)
			return false;

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$resultado[$i]['banco']			= $a[0];
			$resultado[$i]['num_tipdocumento']	= $a[1];
			$resultado[$i]['num_seriedocumento']	= $a[2];
			$resultado[$i]['num_descdocumento'] 	= $a[3];
			$resultado[$i]['ini'] 			= $a[4];
			$resultado[$i]['idbanco']		= $a[5];
			$resultado[$i]['ch_almacen']		= $a[6];
		}

		return $resultado;

  	}


}
