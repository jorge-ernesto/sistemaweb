<?php

class Gasto_Model extends Model {

	function ObtenerGastos() {
		global $sqlca;

		$sql = "
			SELECT
				c_cash_operation_id,
				name
			FROM
				c_cash_operation
			WHERE
				accounts = '0'
			ORDER BY
				name;
			";

		if ($sqlca->query($sql) < 0) 
			return false;

		$result = Array();
	
		$result['TODOS'] = "Todos..";
	
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    	$a = $sqlca->fetchRow();
		    	$result[$a[0]] = $a[1];
		}

		return $result;

    	}
	
	function Paginacion($id) {
        	global $sqlca;

		if(!empty($id) && $id != 'TODOS'){
		$sql = "
				AND c_cash_operation_id = '$id'";
		}

        	$sql="
			SELECT
				c_cash_operation_id,
				name
			FROM
				c_cash_operation
			WHERE
				accounts = '0'
			".$sql;
		$sql .= "
			ORDER BY 
				name;
		";

		if ($sqlca->query($sql) < 0)
			return false;

		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$resultado[$i]['c_cash_operation_id']	= $a[0];
			$resultado[$i]['name']		= $a[1];
		}

		return $resultado;

	}

	function agregar($name) {
        	global $sqlca;

		$ins="
			INSERT INTO
					c_cash_operation(
							c_cash_operation_id,
							created,
							createdby,
							name,
							accounts,
							type
					)VALUES(
							(select max(c_cash_operation_id + 1) from c_cash_operation),
							now(),
							'1',
							'$name',
							'0',
							'1'
					);
		";

            	echo $ins;
		if ($sqlca->query($ins) < 0)
			return false;
		return 1;

	}

	function eliminarRegistro($id) {
        	global $sqlca;
	
		$del = "DELETE FROM c_cash_operation WHERE c_cash_operation_id = '$id';";

	        $sqlca->query($del);

        	return 'OK';

	}

	function actualizar($id,$name) {
        	global $sqlca;                                            
                                                     
		$up = "
			UPDATE 
				c_cash_operation
			SET 
				name	 		= '$name'
			WHERE
				c_cash_operation_id	= '$id';
		";

		$result = $sqlca->query($up);
		return '';
	}

	function recuperarRegistroArray($id) {
        	global $sqlca;

        	$registro = array();

        	$sql = "
				SELECT
					c_cash_operation_id,
					name
				FROM
					c_cash_operation
				WHERE
					c_cash_operation_id = '$id';
		";

        	$sqlca->query($sql);

        	while ($reg = $sqlca->fetchRow()) {
            		$registro = $reg;
		}

		return $registro;
	}

}
