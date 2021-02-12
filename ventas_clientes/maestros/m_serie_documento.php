<?php

class SerieDocumentoModel extends Model {
	
	function obtieneListaEstaciones() {
		global $sqlca;

	        $sql = "
			SELECT 
				ch_almacen, 
				trim(ch_nombre_almacen)
			FROM 
				inv_ta_almacenes
			WHERE 
				ch_clase_almacen='1'
			ORDER BY 
				ch_almacen;
		";

		if ($sqlca->query($sql) < 0)
			return false;

		$result = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {

			$a = $sqlca->fetchRow();
			$result[$a[0]] = $a[0] . " - " . $a[1];

        	}

        	return $result;

	}

	function Paginacion($almacen){
		global $sqlca;

		$cond = '';
		if($almacen != NULL)
			$cond = "AND d.ch_almacen = '$almacen'";

		$sql = "
			SELECT
				num_tipdocumento tipo,
				num_seriedocumento serie,
				num_descdocumento nombre,
				num_longdocumento longitud,
				num_numactual numero,
				a.ch_nombre_almacen almacen
			FROM 
				int_num_documentos d
				LEFT JOIN inv_ta_almacenes a ON(d.ch_almacen = a.ch_almacen)
			WHERE
				d.num_tipdocumento IN('10','35','11','20')
				$cond
			ORDER BY
				num_tipdocumento,
				num_seriedocumento;
			";

		echo $sql;

		if ($sqlca->query($sql) < 0)
			return false;
	    
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$resultado[$i]['tipo']		= $a[0];
			$resultado[$i]['serie']		= $a[1];
			$resultado[$i]['nombre']	= $a[2];
			$resultado[$i]['longitud']	= $a[3];
			$resultado[$i]['numero']	= $a[4];
			$resultado[$i]['almacen']	= $a[5];
		}

		return $resultado;

  	}

	function agregar($almacen, $tipo, $serie, $numero) {
		global $sqlca;
		
		$validar = SerieDocumentoModel::ValidarRegistro($tipo, $serie);

		if ($validar == 1){

			$sql = "
				INSERT INTO int_num_documentos(
							num_tipdocumento,
							num_seriedocumento,
						        num_descdocumento,
						        num_longdocumento,
						        num_numactual,
						        num_fecactualiz,
						        ch_almacen
				)VALUES(
							'$tipo',
						        '$serie',
						        (SELECT
								tab_descripcion || ' $serie'
							FROM
								int_tabla_general
							WHERE
								tab_tabla = '08'
								AND tab_elemento<>'000000'
								AND tab_car_03 != ''
								AND SUBSTR(trim(tab_elemento),5,2) = '$tipo'),
						        '10',
						        '$numero',
						        now(),
						        '$almacen');
			";

			echo $sql;

			if ($sqlca->query($sql) < 0)
				return false;

			return 1;//AGREGO

		}else{

			return 2;//EXISTE

		}

	}

	function recuperarRegistroArray($tipo, $serie){
	  	global $sqlca;
		
		$registro = array();

		$sql = "
			SELECT
				ch_almacen almacen,
				num_tipdocumento tipo,
				num_seriedocumento serie,
				num_descdocumento nombre,
				num_numactual numero
			FROM
				int_num_documentos
			WHERE
				num_tipdocumento 	= '$tipo'
				AND num_seriedocumento 	= '$serie';

			";
			 
		$sqlca->query($sql);

		while( $reg = $sqlca->fetchRow()){
			$registro = $reg;
		}
		    
		return $registro;

	}

	function actualizar($tipo, $serie, $numero){
		global $sqlca;

		$sql = "
			UPDATE 
				int_num_documentos
			SET 
				num_numactual 		= '$numero'
			WHERE
				num_tipdocumento	= '$tipo'
				AND num_seriedocumento 	= '$serie';
		";

		if ($sqlca->query($sql) < 0) 
			return false;

		return true;

 	}

	function Eliminar($tipo, $serie){
		global $sqlca;

		$sql2 = "SELECT count(*) FROM fac_ta_factura_cabecera WHERE ch_fac_tipodocumento = '$tipo' AND ch_fac_seriedocumento = '$serie';";

		$sqlca->query($sql);

		$row = $sqlca->fetchRow();

		if($row[0] < 1){

			$sql = "DELETE FROM int_num_documentos WHERE num_tipdocumento	= '$tipo' AND num_seriedocumento = '$serie';";

			if ($sqlca->query($sql) < 0) 
				return false;

		}

		return true;

 	}

	function ValidarRegistro($tipo, $serie){
		global $sqlca;

		$sql = "SELECT count(*) FROM int_num_documentos WHERE num_tipdocumento = '$tipo' AND num_seriedocumento = '$serie'";

		echo $sql;

		if ($sqlca->query($sql) < 0) 
			return false;

		$a = $sqlca->fetchRow();
		if($a[0]>=1){
			return 0;//EXISTE
		}else{
			return 1;//NO EXISTE
		}

	}

	function Documentos(){
		global $sqlca;

		$documentos = "
				SELECT
					SUBSTR(trim(tab_elemento),5,2),
					tab_descripcion
				FROM
					int_tabla_general
				WHERE
					tab_tabla = '08'
					AND tab_elemento<>'000000'
					AND tab_car_03 != ''
				ORDER BY
					tab_elemento;
				";

		if($sqlca->query($documentos) < 0)
			return false;

		$resultado = array();
	
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    	$a = $sqlca->fetchRow();
		    	$resultado[$a[0]] = $a[0] . " - " .$a[1];
		}
		
		return $resultado;
	
	}

}
