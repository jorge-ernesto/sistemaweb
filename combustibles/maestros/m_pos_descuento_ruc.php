<?php
// Descomentar estas líneas, cuando estamos en modo - development
/*
error_reporting(-1);
ini_set('display_errors', 1);
*/
// Descomentar estas líneas, cuando estamos en modo - production

ini_set('display_errors', 0);
if (version_compare(PHP_VERSION, '5.3', '>='))
{
	error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
}
else
{
	error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE);
}


/*
  Fecha de creacion     : Marzo 7, 2012, 5: 00 PM
  Autor                 : Nestor Hernandez Loli
  Fecha de modificacion :
  Modificado por        :

  Clase modelo del mantenimiento de la tabla  c_pos_descuento_ruc
 */

class PosDescuentoRucModel extends Model {

    function __construct() {
        
    }

    /*
     * Crea un registro en la tabla c_pos_descuento_ruc
     * Devuelve >=1 si la operacion tuvo exito
     */

    function guardarRegistro($ruc, $art_codigo, $descuento, $activo, $tipo) {
        global $sqlca;
        $query = "INSERT INTO
				pos_descuento_ruc(
						   ruc,
						   art_codigo,
						   descuento,
						   activo,
						   tipo)
				VALUES(
						   '$ruc',
						   '$art_codigo',
						   '$descuento',
						   '$activo',
	 					   '$tipo')";

	echo $query;

        $f = 0;
        $sqlca->query($query);
        $result = $sqlca->cursors['_default'];
        $f = pg_affected_rows($result);
        return $f;
    }

    /*
     * Actualiza un registro en la tabla c_pos_descuento_ruc
     * Devuelve >=1 si la operacion tuvo exito
     */


	function extension($archivo){
		$partes = explode(".", $archivo);
		$extension = end($partes);

		return $extension;
	}

	function ValidarExcel($nuproducto, $notd, $codcliente) {
		global $sqlca;

		if(!empty($codcliente)){
			//error_log('$1');
			if($notd == 'FACTURA'){
				//error_log('$2');
				$nutipo 		= '2';
				$condcliente 	= ", (SELECT count(*) FROM ruc WHERE ruc = '".$codcliente."') existe_cliente,
									(SELECT ruc || ' - ' || razsocial FROM ruc WHERE ruc = '".$codcliente."') nocliente,
									(SELECT count(*) FROM pos_descuento_ruc WHERE ruc = '".trim($codcliente)."' AND tipo = ".$nutipo." AND art_codigo = '".$nuproducto."') existe_descuento";
			}elseif($notd == 'BOLETA'){
				//error_log('$3');
				$nutipo 		= '3';
				$condcliente 	= ", '1' existe_cliente,
									".$codcliente." nocliente,
									(SELECT count(*) FROM pos_descuento_ruc WHERE ruc = '".trim($codcliente)."' AND tipo = ".$nutipo." AND art_codigo = '".$nuproducto."') existe_descuento";
			}else{
				//error_log('$4');
				$nutipo 		= '1';
				$condcliente 	= ", (SELECT count(*) FROM int_clientes WHERE cli_codigo = '".$codcliente."') existe_cliente,
									(SELECT cli_codigo || ' - ' || cli_rsocialbreve FROM int_clientes WHERE ruc = '".$codcliente."') nocliente,
									(SELECT count(*) FROM pos_descuento_ruc WHERE cli_codigo = '".trim($codcliente)."' AND tipo = ".$nutipo." AND art_codigo = '".$nuproducto."') existe_descuento";
			}
		}

		error_log('$condcliente: '.$condcliente);

		$sql ="
			SELECT
				count(*) existe,
				(SELECT art_descbreve FROM int_articulos WHERE art_codigo = '".trim($nuproducto)."' ) noproducto
				$condcliente
			FROM
				int_articulos
			WHERE
				art_codigo = '".trim($nuproducto)."'
		";
		//echo "\n" . $sql . "\n";

		$sqlca->query($sql);
		$data = Array();
		if ($sqlca->numrows()==1){
			$data = $sqlca->fetchRow();
			return array($data);
		}
	}

	function InsertarExcel($data, $usuario, $ip, $nuproducto, $notd, $nutd){
		global $sqlca;

		$arrCeldasNombres 	= $data->sheets[0]['cells'];
		$arrCeldas 			= $data->sheets[0]['cellsInfo'];

		$resultados = count($arrCeldas);
		$codcliente	= '';

		$a = 0;
		$b = 0;
		$c = 0;

		for ($i = 4; $i <= $resultados; $i++) {
			$codcliente	= $arrCeldas[$i][1]['raw'];
			$nuimporte	= $arrCeldas[$i][2]['raw'];

			$datos = PosDescuentoRucModel::ValidarExcel(trim($nuproducto), $notd, $codcliente);

			if($codigoexcel == $codcliente && $codigoexcel1 == $nuimporte){
				$a++;//CANTIDAD DE PRODUCTOS NO INSERTADOS
			} else {
				$b++;//CANTIDAD DE PRODUCTOS INSERTADOS
				$codclienteb .= $codcliente.",";
				$sql = "
					INSERT INTO
						pos_descuento_ruc(
						   ruc,
						   art_codigo,
						   descuento,
						   activo,
						   tipo,
						   usuario,
						   ip
						) VALUES (
							'".$codcliente."',
							'".$nuproducto."',
							".$nuimporte.",
							'1',
							'".$nutd."',
							'".TRIM($usuario)."',
							'".TRIM($ip)."'
						);
				";
				if ($sqlca->query($sql) < 0)
					return false;
			//} else {
				//$c++;//CANTIDAD DE PRODUCTOS EXISTENTES
			}
			$codigoexcel = $codcliente;
			$codigoexcel1 = $nuimporte;
		}
		return array(true, $a, $b, $c, $codclienteb);
	}

	function BuscarExcel($pp, $pagina, $nuproducto, $nutd, $codclientes) {
		global $sqlca;

		$codclientes = str_replace(",","','",$codclientes);

		$query = "
			SELECT
				ruc.pos_descuento_ruc_id,
				ruc.ruc,
				CASE
					WHEN ruc.tipo = 1 THEN cli.cli_razsocial
					WHEN ruc.tipo = 2 THEN r.razsocial
					WHEN ruc.tipo = 3 THEN 'Boleta'
					WHEN ruc.tipo = 5 THEN cli.cli_razsocial
				END AS cli_razsocial,
				ruc.art_codigo || ' - ' || art.art_descbreve as art_codigo,
				ruc.descuento,
				ruc.activo,
				CASE
					WHEN ruc.tipo = 1 THEN 'Nota de Despacho'
					WHEN ruc.tipo = 2 THEN 'Factura'
					WHEN ruc.tipo = 3 THEN 'Tarjeta de Descuento'
					WHEN ruc.tipo = '5' THEN 'Precio Pactado'
				END AS tipo,
			FROM
				pos_descuento_ruc ruc
				LEFT JOIN int_clientes cli ON(cli.cli_ruc = ruc.ruc)
				LEFT JOIN ruc r ON(r.ruc = ruc.ruc)
				LEFT JOIN int_articulos art ON (ruc.art_codigo = art.art_codigo)
			WHERE
				ruc.art_codigo 	= trim('".$nuproducto."')
				AND ruc.tipo 	= ".$nutd."
				AND ruc.ruc 	IN ('$codclientes')
			ORDER BY
				cli_razsocial,
				art_codigo;
		";

		print_r($query);

		$sqlca->query($query);
		$array = $sqlca->fetchAll();
		$numrows = sizeof($array);

		if ($pp && $pagina) {
		    $paginador = new paginador($numrows, $pp, $pagina);
		} else {
		    $paginador = new paginador($numrows, 100, 0);
		}

		$listado2['partir'] = $paginador->partir();
		$listado2['fin'] = $paginador->fin();
		$listado2['numero_paginas'] = $paginador->numero_paginas();
		$listado2['pagina_previa'] = $paginador->pagina_previa();
		$listado2['pagina_siguiente'] = $paginador->pagina_siguiente();
		$listado2['pp'] = $paginador->pp;
		$listado2['paginas'] = $paginador->paginas();
		$listado2['primera_pagina'] = $paginador->primera_pagina();
		$listado2['ultima_pagina'] = $paginador->ultima_pagina();

		if ($pp > 0) {
		    $query .= "LIMIT $pp";
		}
		if ($pagina > 0) {
		    $query .= "OFFSET " . $paginador->partir();
		}
	  
		$sqlca->query($query);
		$datos = $sqlca->fetchAll();

		$listado[] = array();
		$listado["datos"] = $datos;
		$listado['paginacion'] = $listado2;
		return $listado;
	}

	function actualizarRegistro($pos_descuento_ruc_id, $ruc, $art_codigo, $descuento, $activo, $tipo) {
 		global $sqlca;

		$query ="
			UPDATE
				pos_descuento_ruc
			SET
					ruc 		= '$ruc',
					art_codigo 	= '$art_codigo',
		           		descuento 	= $descuento,
					activo 		= '$activo',
					tipo 		= '$tipo'
			WHERE
					pos_descuento_ruc_id = $pos_descuento_ruc_id";

		$f = 0;
		$sqlca->query($query);
		$result = $sqlca->cursors['_default'];
		$f = pg_affected_rows($result);
		return $f;

	}

    /**
     * Busca un articulo por descripcion
     */
    	function buscarArticulosPorDescripcion($buscar) {
		global $sqlca;
		$buscar = strtolower($buscar);
		$query = "select art_codigo, art_descripcion, art_descbreve
		        from int_articulos where lower(art_descripcion) like '%$buscar%'
		        or lower(art_descbreve) like '%$buscar%'";
		$sqlca->query($query);
		$array = $sqlca->fetchAll();
		return $array;
	}

    /*
     * Buscar un articulo por codigo 
     */

	function buscarArticulosPorCodigo($buscar) {
		global $sqlca;

		$query = "select art_codigo, art_descripcion, art_descbreve 
		            from int_articulos where art_codigo = '$buscar'";
		$sqlca->query($query);
		$array = $sqlca->fetchAll();
		return $array;
	}

    /*
     * Borra un registro en la tabla c_pos_descuento_ruc
     * Devuelve >=1 si la operacion tuvo exito
     */

	function eliminarRegistro($idregistro) {
		global $sqlca;

		$query = "DELETE FROM pos_descuento_ruc WHERE pos_descuento_ruc_id = '$idregistro'";
		$f = 0;

		$sqlca->query($query);
		$result = $sqlca->cursors['_default'];
		$f = pg_affected_rows($result);

		return $f;
	}

    /*
     * Obtiene un registro de la tabla c_pos_descuento_ruc
     * Devuelve un array con los datos de la consulta
     */

	function obtenerRegistro($registroid) {
		global $sqlca;
		$query = "SELECT pos_descuento_ruc_id,ruc,art_codigo,descuento,activo 
		        FROM pos_descuento_ruc 
		        WHERE pos_descuento_ruc_id = '$registroid'";
		$sqlca->query($query);
		$registro = $sqlca->fetchRow();
		return $registro;
	}

    /*
     * Devuele el ID del último registro creado
     */

    	function obtenerUltimoID() {
		global $sqlca;
		$query = "SELECT max(pos_descuento_ruc_id)
		        FROM pos_descuento_ruc";
		$sqlca->query($query);
		$registro = $sqlca->fetchRow();

		$id = $registro[0];
		return $id;
	}

    /*
     * Genera un listado de datos para realizar paginacion
     * Devuelve un array con los datos de la consulta
     */

	function listado($filtro = array(), $pp, $pagina) {
		global $sqlca;
		
		if (!empty($filtro["cliente"])){
			$cond = "WHERE
					dr.ruc LIKE '%" . addslashes($filtro["cliente"]) . "%'
					OR cli.cli_razsocial LIKE '%" . addslashes($filtro["cliente"]) . "%'
					OR r.razsocial LIKE '%" . addslashes($filtro["cliente"]) . "%' ";
		}
	
		$query = "
			SELECT
				dr.pos_descuento_ruc_id,
				to_char(dr.fecha, 'YYYY-MM-DD') AS fecha,
				dr.ruc,
				CASE
					WHEN dr.tipo = 1 THEN cli.cli_razsocial
					WHEN dr.tipo = 2 THEN r.razsocial
					WHEN dr.tipo = 3 THEN 'Boleta'
					WHEN dr.tipo = 5 THEN cli.cli_razsocial
				END AS cli_razsocial,
				dr.art_codigo || ' - ' || art.art_descbreve as art_codigo,
				dr.descuento,
				dr.activo,
				CASE
					WHEN dr.tipo = 1 THEN 'Nota de Despacho'
					WHEN dr.tipo = 2 THEN 'Factura'
					WHEN dr.tipo = 3 THEN 'Tarjeta de Descuento'
					WHEN dr.tipo = '5' THEN 'Precio Pactado'
				END AS tipo
			FROM
				pos_descuento_ruc dr
				LEFT JOIN int_clientes cli ON (dr.ruc = cli.cli_ruc AND cli_tipo = 'AC')
				LEFT JOIN int_articulos art ON (dr.art_codigo = art.art_codigo)
				LEFT JOIN ruc r ON (dr.ruc = r.ruc)
				".$cond."
		    ORDER BY
				cli_razsocial,
				art_codigo
		";

		print_r($query);

		$sqlca->query($query);
		$array = $sqlca->fetchAll();
		$numrows = sizeof($array);

		if ($pp && $pagina) {
		    $paginador = new paginador($numrows, $pp, $pagina);
		} else {
		    $paginador = new paginador($numrows, 100, 0);
		}

		$listado2['partir'] = $paginador->partir();
		$listado2['fin'] = $paginador->fin();
		$listado2['numero_paginas'] = $paginador->numero_paginas();
		$listado2['pagina_previa'] = $paginador->pagina_previa();
		$listado2['pagina_siguiente'] = $paginador->pagina_siguiente();
		$listado2['pp'] = $paginador->pp;
		$listado2['paginas'] = $paginador->paginas();
		$listado2['primera_pagina'] = $paginador->primera_pagina();
		$listado2['ultima_pagina'] = $paginador->ultima_pagina();

		if ($pp > 0) {
		    $query .= "LIMIT $pp";
		}
		if ($pagina > 0) {
		    $query .= "OFFSET " . $paginador->partir();
		}
	  
		$sqlca->query($query);
		$datos = $sqlca->fetchAll();

		$listado[] = array();
		$listado["datos"] = $datos;
		$listado['paginacion'] = $listado2;
		return $listado;
	}

     /*
     * Buscar un por tipo 
     */
	function validarRuc($ruc) {
		global $sqlca;

		if(!empty($ruc)) {
			$query = "SELECT trim(cli_ruc) FROM int_clientes WHERE cli_ruc = '".$ruc."' ORDER BY cli_ruc DESC LIMIT 1";

			$result = $sqlca->query($query);
			$numrows = $sqlca->numrows();	
			return $result;
	 	/*	if($numrows > 0) {
				$rows = $sqlca->fetchRow();
				if($ruc != $rows[0]) {
					return '0';
				}
			}*/
		}
	}

	function buscarPorTipo($cliente, $tipo, $pp, $pagina) {
		global $sqlca;

		if(!empty($cliente) AND $tipo == "1"){//NOTA DESPACHO
			$condrazsocial = "
			WHERE
				trim(cli.cli_razsocial) ~ '".$cliente."'
				AND tipo = '".$tipo."'
			";
		}elseif(!empty($cliente) AND $tipo == "2"){//FACTURA
			$condrazsocial = "
			WHERE
				trim(r.razsocial) ~ '".$cliente."'
				AND tipo = '".$tipo."'
			";
		}elseif(!empty($cliente) AND $tipo == "3"){//TARJETA DE DESCUENTO
			$condrazsocial = "
			WHERE
				trim(ruc.ruc) ~ '".$cliente."'
				AND tipo = '".$tipo."'
			";
		}elseif(empty($cliente) AND $tipo != "T"){
			$condrazsocial = "
			WHERE
				tipo = '".$tipo."'
			";
		}else{
			$condrazsocial = "
			WHERE
				trim(ruc.ruc) ~ '".$cliente."'
			";
		}

		$query = "
			SELECT
				ruc.pos_descuento_ruc_id,
				to_char(ruc.fecha, 'YYYY-MM-DD') AS fecha,
				ruc.ruc,
				CASE
					WHEN ruc.tipo = 1 THEN cli.cli_razsocial
					WHEN ruc.tipo = 2 THEN r.razsocial
					WHEN ruc.tipo = 3 THEN 'Boleta'
					WHEN ruc.tipo = 5 THEN cli.cli_razsocial
				END AS cli_razsocial,
				ruc.art_codigo || ' - ' || art.art_descbreve as art_codigo,
				ruc.descuento,
				ruc.activo,
				CASE
					WHEN ruc.tipo = '1' THEN 'Nota de Despacho'
					WHEN ruc.tipo = '2' THEN 'Factura'
					WHEN ruc.tipo = '3' THEN 'Tarjeta de Descuento'
					WHEN ruc.tipo = '5' THEN 'Precio Pactado'
				END AS tipo
			FROM
				pos_descuento_ruc ruc
				LEFT JOIN int_clientes cli ON(cli.cli_ruc = ruc.ruc AND cli_tipo = 'AC')
				LEFT JOIN int_articulos art ON (ruc.art_codigo = art.art_codigo)
				LEFT JOIN ruc r ON (ruc.ruc = r.ruc)
			$condrazsocial
			ORDER BY
				cli_razsocial,
				art_codigo ";

		//print_r($query);

		$sqlca->query($query);
		$array = $sqlca->fetchAll();
		$numrows = sizeof($array);

		if ($pp && $pagina) {
		    $paginador = new paginador($numrows, $pp, $pagina);
		} else {
		    $paginador = new paginador($numrows, 100, 0);
		}

		$listado2['partir'] = $paginador->partir();
		$listado2['fin'] = $paginador->fin();
		$listado2['numero_paginas'] = $paginador->numero_paginas();
		$listado2['pagina_previa'] = $paginador->pagina_previa();
		$listado2['pagina_siguiente'] = $paginador->pagina_siguiente();
		$listado2['pp'] = $paginador->pp;
		$listado2['paginas'] = $paginador->paginas();
		$listado2['primera_pagina'] = $paginador->primera_pagina();
		$listado2['ultima_pagina'] = $paginador->ultima_pagina();

		if ($pp > 0) {
		    $query .= "LIMIT $pp";
		}
		if ($pagina > 0) {
		    $query .= "OFFSET " . $paginador->partir();
		}
	  
		$sqlca->query($query);
		$datos = $sqlca->fetchAll();

		$listado[] = array();
		$listado["datos"] = $datos;
		$listado['paginacion'] = $listado2;
		return $listado;
	
	}

}
