<?php

class CuentasBancariasModel extends Model {

	function ObtenerBancos() {

		global $sqlca;
	
		$sql = "
			SELECT
				c_bank_id,
				initials
			FROM
				c_bank
			ORDER BY
				initials;
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
	
	function Paginacion($pp, $pagina, $banco){
		global $sqlca;

		if($banco == "TODOS")
			$condicion = "";
		else if(!empty($banco)){
			$condicion ="
			WHERE
				cu.c_bank_id = '$banco'
			";
		}else{
			$condicion = "";	
		}

		$query = "
			SELECT
				bank.initials AS banco,
				cu.c_bank_account_id AS ncuenta,
				CASE WHEN cu.c_currency_id = '1' THEN 'Soles S/' ELSE 'Dolares $' END AS currency,
				cu.name AS name,
				cu.initials AS ini,
				cu.c_bank_id
			FROM 
				c_bank_account cu 
				INNER JOIN c_bank bank ON (cu.c_bank_id = bank.c_bank_id)
				$condicion	
			ORDER BY 
				cu.created";

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
			$resultado[$i]['banco']		= $a[0];
			$resultado[$i]['ncuenta']	= $a[1];
			$resultado[$i]['currency']	= $a[2];
			$resultado[$i]['nombre'] 	= $a[3];
			$resultado[$i]['ini'] 		= $a[4];
			$resultado[$i]['idbanco']	= $a[5];
		}
		
		$query = "COMMIT";
		$sqlca->query($query);

		$listado['datos']      = $resultado;        
		$listado['paginacion'] = $listado2;

		return $listado;
  	}

	function agregar($ncuenta,$banco,$currency,$name,$ini){
		global $sqlca;

		$vali = CuentasBancariasModel::ValidarCuenta($ncuenta);

		if($vali == 1){
	
			$ins = "INSERT INTO
						c_bank_account(
								c_bank_account_id,          
								c_bank_id,
								created,          
								createdby,
								c_currency_id,
								name,
								initials
						)VALUES(
								'$ncuenta',
								'$banco',
								now(),
						    	        '0',
						    	        '$currency',
						    	        '$name',
						    	        '$ini'
						);";


			echo $ins;
			$sqlca->query($ins);
			return 1;
		}else{

			return 2;	

		}

	}
	
	function eliminarRegistro($ncuenta, $idbanco){
		global $sqlca;

		$del = "DELETE FROM c_bank_account WHERE c_bank_account_id = '$ncuenta' AND c_bank_id = $idbanco;";

//		echo $del;

		$sqlca->query($del);

		return 'OK';

	}

	function actualizar($ncuenta, $name, $ini, $idbanco){
		global $sqlca;

			$up = "
				UPDATE 
					c_bank_account
				SET 
					name	 		= '$name',
					initials 		= '$ini'	
				WHERE 
					c_bank_account_id 	= '$ncuenta'
					AND c_bank_id 		= $idbanco;
				";

//			echo $up;

			$result = $sqlca->query($up);
			return '';
 	}
	
	function recuperarRegistroArray($ncuenta, $idbanco){
	  	global $sqlca;
		
		$registro = array();
		$query = "SELECT
				bank.initials AS banco,
				cu.c_bank_account_id AS ncuenta,
				CASE WHEN cu.c_currency_id = '1' THEN 'Soles S/' ELSE 'Dolares $' END AS currency,
				cu.name AS name,
				cu.initials AS ini,
				cu.c_bank_id as idbanco
			FROM 
				c_bank_account cu 
				INNER JOIN c_bank bank ON (cu.c_bank_id = bank.c_bank_id)
			WHERE
				cu.c_bank_account_id = '$ncuenta'
				AND cu.c_bank_id = $idbanco;
			";
		 
//		echo $query;

		$sqlca->query($query);

		while( $reg = $sqlca->fetchRow()){
			$registro = $reg;
		}
		    
		return $registro;
	  }

	function ValidarCuenta($ncuenta){
	  	global $sqlca;

		$ncuenta = $_REQUEST['ncuenta'];

		$vali = "SELECT count(*) FROM c_bank_account WHERE c_bank_account_id = '$ncuenta';";

		if ($sqlca->query($vali) < 0) 
			return false;
		$a = $sqlca->fetchRow();
		if($a[0]>=1){
			return 0;
		}else{
			return 1;
		}

	}

	function TipoMoneda(){
		global $sqlca;

		$curre="SELECT
				substr(tab_elemento,6) currency,
				tab_descripcion || ' ' || tab_desc_breve mone
			FROM
				int_tabla_general
			WHERE
				tab_tabla = '04'
				AND tab_elemento != '000000'
			ORDER BY
				1;
			";

		if($sqlca->query($curre) < 0)
			return false;

		$resultado = array();
	
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    	$a = $sqlca->fetchRow();
		    	$resultado[$a[0]] = $a[1];
		}
		
		return $resultado;
	}

}
