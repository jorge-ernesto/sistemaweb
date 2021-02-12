<?php

class MAN_CASHOPEModel extends Model {

	function obtieneListaEstaciones() {

		global $sqlca;

		$sql = "SELECT
				ch_almacen,
				trim(ch_nombre_almacen)
			FROM
				inv_ta_almacenes
			WHERE
				ch_clase_almacen = '1'
			ORDER BY
				ch_almacen;";

		if ($sqlca -> query($sql) < 0)
			return false;
		$result = Array();

		for ($i = 0; $i < $sqlca -> numrows(); $i++) {
			$a = $sqlca -> fetchRow();
			$result[$a[0]] = $a[0] . " - " . $a[1];
		}

		return $result;
	}

	function Paginacion($pp, $pagina) {

		global $sqlca;

		$query = "SELECT 
                                c_cash_operation_id,
                                created,
                                createdby,
                                name ,
                                accounts ,
                                type 
                            FROM  c_cash_operation  ";

		$query .= "
			ORDER BY 
				 c_cash_operation_id  ";

		$resultado_1 = $sqlca -> query($query);
		$numrows = $sqlca -> numrows();

		$paginador = new paginador($numrows, $pp, $pagina);

		$listado2['partir'] = $paginador -> partir();
		$listado2['fin'] = $paginador -> fin();
		$listado2['numero_paginas'] = $paginador -> numero_paginas();
		$listado2['pagina_previa'] = $paginador -> pagina_previa();
		$listado2['pagina_siguiente'] = $paginador -> pagina_siguiente();
		$listado2['pp'] = $paginador -> pp;
		$listado2['paginas'] = $paginador -> paginas();
		$listado2['primera_pagina'] = $paginador -> primera_pagina();
		$listado2['ultima_pagina'] = $paginador -> ultima_pagina();

		$query .= " LIMIT " . pg_escape_string($pp) . " ";
		$query .= " OFFSET " . pg_escape_string($paginador -> partir());

		if ($sqlca -> query($query) < 0)
			return false;

		$listado[] = array();
		$resultado = Array();

		for ($i = 0; $i < $sqlca -> numrows(); $i++) {
			$a = $sqlca -> fetchRow();
			$resultado[$i]['c_cash_operation_id'] = $a[0];
			$resultado[$i]['created'] = $a[1];
			$resultado[$i]['createdby'] = $a[2];
			$resultado[$i]['name'] = $a[3];
			$resultado[$i]['accounts'] = $a[4];
			$resultado[$i]['type'] = $a[5];

		}

		$query = "COMMIT";
		$sqlca -> query($query);

		$listado['datos'] = $resultado;
		$listado['paginacion'] = $listado2;

		return $listado;
	}

	function agregar( $name, $accounts,$type) {
		global $sqlca;

			//$_REQUEST['cod_rubro'],$_REQUEST['descripcion_id'],$_REQUEST['desc_breve'],$_REQUEST['tipo_item']
			$ins = "INSERT INTO c_cash_operation( 
                                                    c_cash_operation_id ,
                                                     created ,
                                                    createdby ,
                                                     name ,
                                                     accounts,
                                                      type 
						)VALUES(
								(select max(c_cash_operation_id)+1 from c_cash_operation),
								now(),
								'1',
						    	'$name',
						    	$accounts,
						    	$type
						    	        
						);";

			echo $ins;
			$sqlca -> query($ins);
			return 1;
		
	}

	function eliminarRegistro($ncuenta) {
		global $sqlca;

		$del = "DELETE FROM c_cash_operation WHERE c_cash_operation_id = '$ncuenta';";

		//echo $del;

		$sqlca -> query($del);

		return 'OK';
	}

	function actualizar($id_operacion, $nombre_tipo, $tipo_I_E, $tipo_relacion) {
		global $sqlca;

		$up = "
				UPDATE 
					c_cash_operation
				SET 
					name	 	= '$nombre_tipo',
					type 		= '$tipo_I_E',
                    accounts	= '$tipo_relacion'
				WHERE 
					c_cash_operation_id 	= '$id_operacion';
				";

		echo $up;

		$result = $sqlca -> query($up);
		return '';
	}

	function recuperarRegistroArray($ncuenta) {
		global $sqlca;

		$registro = array();
		$query = "SELECT 
                        c_cash_operation_id,name,type,accounts  
                 FROM  c_cash_operation where c_cash_operation_id='$ncuenta' limit 1;";

		$sqlca -> query($query);

		while ($reg = $sqlca -> fetchRow()) {
			$registro = $reg;
		}

		return $registro;
	}

	function ValidarCuenta($ncuenta) {
		global $sqlca;

		$vali = "SELECT 
                        count(*)  
                    FROM  cpag_ta_rubros where ch_codigo_rubro='$ncuenta' limit 1;;";

		//echo $vali;

		if ($sqlca -> query($vali) < 0)
			return false;
		$a = $sqlca -> fetchRow();
		if ($a[0] >= 1) {
			return 0;
		} else {
			return 1;
		}
	}

	function TipoMoneda() {
		global $sqlca;

		$curre = "SELECT
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

		if ($sqlca -> query($curre) < 0)
			return false;

		$resultado = array();

		for ($i = 0; $i < $sqlca -> numrows(); $i++) {
			$a = $sqlca -> fetchRow();
			$resultado[$a[0]] = $a[1];
		}

		return $resultado;
	}

}
