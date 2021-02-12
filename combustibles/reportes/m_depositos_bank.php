<?php

class DepositosBankModel extends Model {

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
				ch_almacen;";

		if ($sqlca->query($sql) < 0) 
			return false;	
		$result = Array();
	
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    	$a = $sqlca->fetchRow();
		    	$result[$a[0]] = $a[0] . " - " . $a[1];
		}
	
		return $result;

    	}

	function Consolidacion($dia,$almacen){
		
		global $sqlca;

		$turno = 0;

		$diav = substr($dia,6,4)."-".substr($dia,3,2)."-".substr($dia,0,2);

		$sql = " SELECT validar_consolidacion('$diav',$turno,'$almacen') ";

		$sqlca->query($sql);

		$estado = $sqlca->fetchRow();

		echo "devolvio:\n";
		var_dump($estado);

		if($estado[0] == 1){
			return 1;//Consolidado
		}else{
			return 0;//No consolidado
		}


		/*global $sqlca;

		$query = "select count(*) from pos_consolidacion where dia = to_date('$fecha','DD/MM/YYYY')";

		echo $query;

		if ($sqlca->query($query) < 0) 
			return false;
		$a = $sqlca->fetchRow();
		if($a[0]>=1){
			return 1;
		}else{
			return 0;
		}
		*/
	}

	function ConsolidacionA($dia,$almacen){
		
		global $sqlca;

		$turno = 0;

		$sql = " SELECT validar_consolidacion('$dia',$turno,'$almacen') ";

		$sqlca->query($sql);

		$estado = $sqlca->fetchRow();

		echo "devolvio:\n";
		var_dump($estado);

		if($estado[0] == 1){
			return 1;//Consolidado
		}else{
			return 0;//No consolidado
		}



		/* global $sqlca;

		$query = "select count(*) from pos_consolidacion where dia = '$fecha'";

		echo $query;

		if ($sqlca->query($query) < 0) 
			return false;
		$a = $sqlca->fetchRow();
		if($a[0]>=1){
			return 1;
		}else{
			return 0;
		}
		*/
	}
	
	function Paginacion($pp, $pagina, $fecha, $fecha2){

		global $sqlca;

		$query = "SELECT
				depo.ch_almacen almacen, 
				to_char(depo.d_system,'DD/MM/YYYY') fecha,          
				bank.name nombre,
				depo.doc_number docu,
				depo.reference refe,
				depo.amount total,
				depo.c_cashdeposit_id as id
			FROM 
				c_cashdeposit depo 
				INNER JOIN c_bank bank ON (depo.c_bank_id = bank.c_bank_id)";

		if($fecha != ''){
		$query .= "
			WHERE
				depo.d_system BETWEEN to_date('$fecha','DD/MM/YYYY') AND to_date('$fecha2','DD/MM/YYYY')";
		}
			
		$query .= "
			ORDER BY 
				depo.d_system";

		$resultado_1 = $sqlca->query($query);
		$numrows = $sqlca->numrows();

		$paginador = new paginador($numrows,$pp, $pagina);
	
		$listado2['partir'] 		= $paginador->partir();
		$listado2['fin'] 		= $paginador->fin();
		$listado2['numero_paginas'] 	= $paginador->numero_paginas();
		$listado2['pagina_previa'] 	= $paginador->pagina_previa();
		$listado2['pagina_siguiente'] 	= $paginador->pagina_siguiente();
		$listado2['pp'] 		= $paginador->pp;
		$listado2['paginas'] 		= $paginador->paginas();
		$listado2['primera_pagina'] 	= $paginador->primera_pagina();
		$listado2['ultima_pagina'] 	= $paginador->ultima_pagina();

		$query .= " LIMIT " . pg_escape_string($pp) . " ";
		$query .= " OFFSET " . pg_escape_string($paginador->partir());

		echo $query;

		if ($sqlca->query($query) < 0)
			return false;
	    
    		$listado[] = array();
		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$resultado[$i]['almacen']	= $a[0];
			$resultado[$i]['fecha']		= $a[1];
			$resultado[$i]['nombre']	= $a[2];
			$resultado[$i]['docu'] 		= $a[3];
			$resultado[$i]['refe'] 		= $a[4];
			$resultado[$i]['total'] 	= $a[5];
			$resultado[$i]['id'] 		= $a[6];
			
		}
		
		$query = "COMMIT";
		$sqlca->query($query);

		$listado['datos']      = $resultado;        
		$listado['paginacion'] = $listado2;

		return $listado;
  	}

	function agregar($almacen,$hoy,$moneda,$banco,$docu,$refe,$total){
		global $sqlca;
		
		//$validar = DepositosBankModel::ValidaRegistro($codigo, $hoy);

		$anio = substr($hoy,6,4);
		$mes = substr($hoy,3,2);
		$dia = substr($hoy,0,2);

		$fecha = $anio."-".$mes."-"."$dia";

		//if ($validar == 1){
	
		$query2 = "INSERT INTO
					c_cashdeposit(
							ch_almacen,          
							d_system,
							c_bank_id,          
							doc_number,
							reference,
							amount,
							c_currency_id,
							createdby
		      	   )VALUES(
							'$almacen',
						        '$fecha',
						        '$banco',
					    	        '$docu',
					    	        '$refe',
					    	        '$total',
							'1',
							'1');";


			echo $query2;
			
			if ($sqlca->query($query2) < 0) 
				return 0;
			else
				return 1;
		/*}else{
			return 2;
		}*/

	}
	
	function eliminarRegistro($idregistro){
		global $sqlca;

		/*$anio = substr($hoy,6,4);
		$mes = substr($hoy,3,2);
		$dia = substr($hoy,0,2);

		$fecha = $anio."-".$mes."-"."$dia";*/

		$query = "DELETE FROM c_cashdeposit WHERE c_cashdeposit_id = '$idregistro';";

		echo $query;

		$sqlca->query($query);
		return 'OK';

	}

	function actualizar($moneda,$banco,$docu,$refe,$total,$idred){
		global $sqlca;

			$query = "UPDATE 
					c_cashdeposit
				  SET 
					doc_number  	 = '$docu',
					reference	 = '$refe',
					amount   	 = '$total'
				  WHERE 
					c_cashdeposit_id = '$idred';";

			echo $query;

			$result = $sqlca->query($query);
			return '';
 	}
	
	function recuperarRegistroArray($idregistro){
	  	global $sqlca;
		
		    $registro = array();
		    $query = "SELECT
					depo.c_cashdeposit_id idred,
					depo.ch_almacen almacen,  
					depo.d_system fecha,          
					bank.name nombre,
					depo.doc_number docu,
					depo.reference refe,
					depo.amount total
				FROM 
					c_cashdeposit depo 
					INNER JOIN c_bank bank ON (depo.c_bank_id = bank.c_bank_id)
				WHERE
					c_cashdeposit_id = '$idregistro';";

		    //echo $query;
			 
		    $sqlca->query($query);

		    while( $reg = $sqlca->fetchRow()){
				$registro = $reg;
			}
		    
		    return $registro;
	  }

	/*function ValidaRegistro($almacen,$hoy){
		global $sqlca;

		$almacen = $_REQUEST['ch_almacen'];

		$anio = substr($hoy,6,4);
		$mes = substr($hoy,3,2);
		$dia = substr($hoy,0,2);

		$fecha = $anio."-".$mes."-"."$dia";

		$query = "select count(*) from c_cashdeposit where ch_almacen = '$almacen' and d_system = '$fecha';";

		//echo $query;

		if ($sqlca->query($query) < 0) 
			return false;
		$a = $sqlca->fetchRow();
		if($a[0]>=1){
			return 0;
		}else{
			return 1;
		}

	}*/

}
