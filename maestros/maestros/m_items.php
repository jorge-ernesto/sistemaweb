<?php

ini_set("memory_limit", '3G');
ini_set("upload_max_filesize", "15M");
ini_set("post_max_size", "15M");
ini_set('max_execution_time', '700');
ini_set('max_input_time', '700');

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


class ItemsModel extends Model {

	function obtenerSucursales($alm) {
		global $sqlca;
		
		if(trim($alm) == "")
			$cond = "";
		else
			$cond = " AND ch_almacen = '$alm'"; 
	
		$sql = "SELECT
			    ch_almacen,
			    ch_almacen||' - '||ch_nombre_almacen
			FROM
			    inv_ta_almacenes
			WHERE
			    ch_clase_almacen='1' $cond 
			ORDER BY
			    ch_almacen;";
	
		if ($sqlca->query($sql) < 0) 
			return false;
	
		$result = Array();
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    	$a = $sqlca->fetchRow();		    
		    	$result[$a[0]] = $a[1];
		}
	
		return $result;
    	}

	function extension($archivo){
		$partes = explode(".", $archivo);
		$extension = end($partes);

		return $extension;
	}

	function ObtieneItemSKU() {
        	global $sqlca;

		$sql = "SELECT  
				trim(tab_elemento),
                		trim(tab_elemento)||'  '||tab_descripcion AS descripcion 
                	FROM 
				int_tabla_general 
                	WHERE 
				tab_tabla = 'CSKU' 
	                	AND tab_elemento<>'000000'
	                ORDER BY 
	                	tab_descripcion";

		if ($sqlca->query($sql) < 0)
		    	return null;

		$cbArray = array();
		$cbArray["all"] = "[ Seleccionar ]";
		
		if ($sqlca->query($sql) <= 0)
		    	return $cbArray;

		while ($result = $sqlca->fetchRow()) {
		    	$cbArray[trim($result[0])] = $result[1];
		}

		return $cbArray;
    	}

    	function ObtieneCodManual($ejec, $long) {
		global $sqlca;

		$CodManual = $sqlca->functionDB("util_fn_corre_docs_long('IT','001','" . $ejec . "',$long)"); //Obtiene correlativo para codigo manual

		return $CodManual;
    	}

    	function ObtieneItemImpuestos() {
		global $sqlca;

		$sql = "SELECT 	
				trim(tab_elemento),
		               	trim(tab_elemento)||'  '||tab_descripcion AS descripcion 
		       	FROM 
				int_tabla_general 
		        WHERE 
				tab_tabla = '17' 
		        	AND tab_elemento<>'000000'
		        ORDER BY 
				tab_elemento DESC";

		if ($sqlca->query($sql) < 0)
		    	return null;

		$cbArray = array();
		$cbArray["all"] = "Inafecto";

		if ($sqlca->query($sql) <= 0)
		    	return $cbArray;

		while ($result = $sqlca->fetchRow()) {
		    	$cbArray[trim($result[0])] = $result[1];
		}

		return $cbArray;
    	}

    	function ObtieneItems($desde = 0, $cantidad = 0) {
		global $sqlca;

		$mes = date("m");
		$ano = date("Y");

		$sql = "SELECT
				art.art_codigo as art_codigo,
				trim(art.art_descripcion) as art_descripcion,
				CASE WHEN tab.tab_elemento IS NULL THEN '--- SIN LINEA ---' ELSE trim(tab.tab_descripcion) END AS art_linea,
				art.art_tipo,
				art.art_unidad,
				art.art_cod_ubicac,
				art.art_cod_sku,
				art.art_estado,
				inv_saldoalma.stk_stock" . $mes . "
			FROM
				int_articulos art
				LEFT JOIN Int_tabla_General tab ON (tab.tab_tabla = '20' AND tab.tab_elemento = art.art_linea)
				LEFT JOIN inv_saldoalma ON (art.art_codigo=inv_saldoalma.art_codigo AND inv_saldoalma.stk_periodo='" . pg_escape_string($ano) . "' AND inv_saldoalma.stk_almacen='" . $_SESSION['almacen'] . "')
			ORDER BY
				art.art_codigo ";

		if ($cantidad > 0)
		    	$sql .= "LIMIT " . pg_escape_string($cantidad) . " ";
		if ($desde > 0)
		    	$sql .= "OFFSET " . pg_escape_string($desde);

		//echo "QUERY : $sql \n";
		if ($sqlca->query($sql) < 0)
		    	return null;

		$result = Array();
		$i = 0;

        	while (($row = $sqlca->fetchRow()) != null) {
            		$result[$i] = $row;
            		$i++;
        	}

        	return $result;
    	}

    	function obtieneCuentaItems() {
		global $sqlca;

		$sql = "SELECT count(art.Art_Codigo) FROM int_articulos art LEFT JOIN Int_Tabla_General tab ON (tab.tab_tabla='20' AND tab.tab_elemento=art.Art_Linea) ;";

		if ($sqlca->query($sql) < 0)
		    	return 0;

		$r = $sqlca->fetchRow();

		return $r[0];
    	}

    function obtieneItem($codigo) {
		global $sqlca;

		$sql = "
		SELECT DISTINCT
			art.art_codigo,
      		art.art_descripcion,
	       	art.art_descbreve,
	       	art.art_clase,
	       	art.art_tipo,
	       	art.art_linea,
	       	art.art_unidad,
	       	art.art_presentacion,
	       	art.art_costoinicial,
	       	art.art_stockinicial,
	       	art.art_stockactual,
	       	art.art_costoactual,
	       	art.art_costoreposicion,
	       	art.art_margenutilidad,
	       	art.art_fecucompra,
	       	art.art_fecuventa,
	       	art.art_fecactuliz,
	       	art.art_estado,
	       	art.art_trasmision,
	       	art.art_impuesto1,
	       	art.art_stkgnrlmin,
	       	art.art_stkgnrlmax,
	       	art.art_promconsumo,
	       	art.art_plazoreposicprom,
	       	art.art_diasreposic,
	       	art.art_feccostorep,
	       	art.art_usuario,
	       	art.art_costopromedio,
	       	art.art_cod_ubicac,
	       	art.art_cod_sku,
	       	art.art_plutipo,
	       	art.art_impuesto1,
	       	art.nu_dias_minimo,
	       	art.nu_dias_maximo,
	       	art.art_acuenta,
	       	art.flg_replicacion,
	       	art.fecha_replicacion,
	       	fac.pre_precio_act1,
	       	fac.pre_lista_precio 
		FROM 
			int_articulos art, 
			fac_lista_precios fac  
		WHERE 
			art.art_codigo = fac.art_codigo  
			AND art.art_codigo = '" . pg_escape_string($codigo) . "'
			AND fac.pre_lista_precio ='01'
		";

		if ($sqlca->query($sql) < 0)
		    	return null;

		$numrows = $sqlca->numrows();
		if ($numrows <= 0) {
		    	$sql = "SELECT * FROM int_articulos WHERE art_codigo = '" . pg_escape_string($codigo) . "'";
		    	if ($sqlca->query($sql) < 0)
		        	return null;
		}
		return $sqlca->fetchRow();
    }

   	function ObtieneTablaGeneral($cod_tabla, $cod = '') {
        	global $sqlca;

		if ($cod != "")
		    	$sql_linea = " AND trim(tab_car_01) ~ '" . round($cod) . "' ";

		if ($cod_tabla == '34')
		    	$desc = "DESC";

			$sql = "SELECT
				trim(tab_elemento) as tab_elemento,
				trim(tab_descripcion) as tab_descripcion
			FROM 
				int_tabla_general
			WHERE 
				tab_tabla='" . pg_escape_string($cod_tabla) . "'
		        	AND tab_elemento<>'000000'
			    	$sql_linea
			ORDER BY 
				tab_elemento $desc ";

        	if ($sqlca->query($sql) < 0)
            		return null;

        	$resultado = Array();

		if ($cod_tabla != "TPLU")
		    	$resultado["all"] = "[ Seleccionar ]";

		while (($tipo = $sqlca->fetchRow()) != null) {
		    	$resultado[$tipo['tab_elemento']] = $tipo['tab_elemento'] . " - " . $tipo['tab_descripcion'];
		}

		return $resultado;
    	}

    	function ObtieneUbicaciones() {
		global $sqlca;

		$sql = "SELECT
			    	trim(cod_ubicac) as cod_ubicac,
			    	trim(desc_ubicac) as desc_ubicac
			FROM 
				inv_ta_ubicacion
			ORDER BY 
				cod_ubicac";

		if ($sqlca->query($sql) < 0)
		    	return null;

		$resultado = Array();
		$resultado["all"] = "[ Seleccionar ]";

		while (($ent = $sqlca->fetchRow()) != null) {
		    	$resultado[$ent['cod_ubicac']] = $ent['cod_ubicac'] . " - " . $ent['desc_ubicac'];
		}

		return $resultado;
    	}

	function grabarItem($codigo, $usuario) {
		global $sqlca;

		if ($_REQUEST['combomarcas'] == "all")
		    	return false;

		/*
		$badUTF8 	= htmlentities($_REQUEST["txtdescripcion"]);
		$txtdescripcion = iconv("utf-8", "utf-8//IGNORE", $badUTF8);

		$badUTF82 	= htmlentities($_REQUEST["txtdescbreve"]);
		$txtdescbreve 	= iconv("utf-8", "utf-8//IGNORE", $badUTF82);

         var_dump($_REQUEST["txtdescbreve"]);

		$hola = utf8_decode($_REQUEST["txtdescbreve"]);
		echo "\n";
		echo $hola;
		echo "\n";
		echo $txtdescripcion;
		*/

		$txtdescripcion 		= $_REQUEST["txtdescripcion"];
		$txtdescbreve 			= $_REQUEST["txtdescbreve"];


		$art_codigo 			= pg_escape_string($codigo);
		$art_descripcion 		= pg_escape_string($txtdescripcion);
		$art_descbreve 			= pg_escape_string($txtdescbreve);
		$art_tipo 				= pg_escape_string($_REQUEST['combotipos']);
		$art_linea 				= pg_escape_string($_REQUEST['combolineas']);
		$art_plutipo 			= pg_escape_string($_REQUEST['comboplu']);
		$art_impuesto1 			= pg_escape_string($_REQUEST['comboimp1']);
		$art_impuesto2 			= pg_escape_string($_REQUEST['comboimp2']);
		$art_cod_sku 			= pg_escape_string($_REQUEST['sku']);
		$art_estado 			= ($_REQUEST['activo'] == "S" ? "0" : "1");
		$art_marca 				= "'" . trim($_REQUEST['combomarcas']) . "'";
		$art_unidad 			= pg_escape_string($_REQUEST['combounidades']);
		$art_presentacion 		= pg_escape_string($_REQUEST['art_presentacion']);
		$art_cod_ubicac 		= pg_escape_string($_REQUEST['comboubicaciones']);
		$art_plazoreposicprom 	= $_REQUEST['plzreposicion'];
		$art_diasreposic 		= $_REQUEST['diasreposicion'];
		$art_costoinicial 		= $_REQUEST['art_costoinicial'];
		$art_costoreposicion 	= $_REQUEST['art_costoreposicion'];

		if ($art_tipo == "all") {
		    	return false;
		}
		
		if ($art_linea == "all") {
		    	return false;
		}

		if ($art_marca == "all") {
		    	return false;
		}
		
		if ($art_cod_sku == "all") {
		    	$art_cod_sku = "NULL";
		} else {
		    	$art_cod_sku = "'" . trim($art_cod_sku) . "'";
		}

		if ($art_unidad == "all") {
		    	return false;
		}
		if ($art_presentacion == "all") {
		    	return false;
		}

		if ($art_cod_ubicac == "all") {
		    	return false;
		}

		if ($_REQUEST['impuesto'] == "all") {
		    	$impuesto = "NULL";
		} else {
		    	$impuesto = "'" . trim($_REQUEST['impuesto']) . "'";
		}

		if ($art_plazoreposicprom == '') {
		    	$art_plazoreposicprom = "NULL";
		}

		if ($art_diasreposic == '') {
		    	$art_diasreposic = "NULL";
		}

		if ($art_costoinicial == '') {
		    	$art_costoinicial = "NULL";
		}

		if ($art_costoreposicion == '') {
		    	$art_costoreposicion = "NULL";
		}
		
		// Listando todas las listas de precio que hay
		$lprecios = Array();
		$sql2 = "SELECT tab_elemento FROM int_tabla_general WHERE tab_tabla='LPRE' AND trim(tab_elemento)!='' AND tab_elemento!='000000' AND tab_elemento IS NOT NULL;";
        	if ($sqlca->query($sql2)<=0)
			return $sqlca->get_error();
            	for($j=0; $j<$sqlca->numrows(); $j++) {
        		$a = $sqlca->fetchRow();
        		$lprecios[$j] = $a[0];
        	}
        	
		$sql = "BEGIN";
		$sqlca->query($sql);

		$sql = "UPDATE
				int_articulos
		   	SET
				art_descripcion='" . $art_descripcion . "',
				art_descbreve='" . $art_descbreve . "',
				art_tipo='" . $art_tipo . "',
				art_linea='" . $art_linea . "',
				art_clase=" . $art_marca . ",
				art_impuesto1 = " . $impuesto . ",
				art_plutipo='" . $art_plutipo . "',
				art_cod_sku=" . $art_cod_sku . ",
				art_estado='" . $art_estado . "',
				flg_replicacion = 0,
				art_fecactuliz = now(),
				art_usuario = '" . $usuario . "',
			";

		//if (intval($art_plutipo) == 1) {
		$sql .= "
			    	art_unidad='" . $art_unidad . "',
			    	art_cod_ubicac='" . $art_cod_ubicac . "',
			    	art_plazoreposicprom=" . $art_plazoreposicprom . ",
			    	art_costoinicial=" . $art_costoinicial . ",
			    	art_costoreposicion=" . $art_costoreposicion . ",
			    	art_presentacion = " . $art_presentacion . ",
			    	art_diasreposic=" . $art_diasreposic . "";
		//}

		$sql .= " 	WHERE art_codigo='" . $art_codigo . "'";

		//echo "QUERY : $sql \n";
		
		if ($sqlca->query($sql) < 0) {
		    	$sqlca->query("ROLLBACK");
		    	return false;
		}

		// Actualizar precio base del producto 
		/*$sql = "
				UPDATE
			    	fac_lista_precios 
				SET
				   	pre_precio_act1 	= '" . pg_escape_string($_REQUEST['precio']) . "',
				   	flg_replicacion 	= 0,
				   	pre_fecactualiz 	= now(),
				   	fecha_replicacion 	= now()
				WHERE
					pre_lista_precio 	= '1'
					AND art_codigo 		= '" . $art_codigo . "'
		;";*/

		if ($sqlca->query($sql) < 0) {
		    	$sqlca->query("ROLLBACK");
		    	return false;
		}

		$numrows = $sqlca->numrows_affected(); //Numero de registros afectados por Update
		
			if ($numrows <= 0) { //Si no se actualizó Agregamos el precio base del producto  
		    	
        		for($k=0; $k<count($lprecios); $k++) {
        			$sql = "INSERT INTO
						fac_lista_precios 
					(
					    	pre_lista_precio,
					    	art_codigo,
						pre_moneda,
						pre_precio_act1,
						pre_fecactualiz,
						flg_replicacion,
						fecha_replicacion
					)
					VALUES 
					(
						'".$lprecios[$k]."',
						'" . pg_escape_string($art_codigo) . "',
						'01',
						'" . pg_escape_string($_REQUEST['precio']) . "',
						now(),
						0,
						now()
				    	)";
				    	//echo $sql."\n\n";
			    	if ($sqlca->query($sql) < 0) {
					$sqlca->query("ROLLBACK");
					return false;
			    	}
        		}					    
			}

			$sqlca->query("COMMIT");

			return true;

    	}

    function ObtieneAlias($item) {
		global $sqlca;

		$sql = "SELECT codigo_alias FROM int_articulos_alias WHERE art_codigo='" . pg_escape_string($item['art_codigo']) . "'";
		if ($sqlca->query($sql) < 0)
		    return null;
		$i = 0;
		$resultado = Array();
		while (($row = $sqlca->fetchRow()) != null) {
	    	$resultado[$row['codigo_alias']]['codigo_alias_item'] = $row['codigo_alias'];
		}
		return $resultado;
    }

    	function obtieneEnlaces($item) {
		global $sqlca;

		$art_codigo = pg_escape_string($item['art_codigo']);

		$sql = "SELECT
			    	enla.ch_item_estandar as ch_item_estandar,
			    	enla.nu_cantidad_descarga as nu_cantidad_descarga,
			    	art.Art_Descripcion as art_descripcion
			FROM
			    	int_ta_enlace_items enla,
			    	Int_Articulos art
			WHERE
				enla.art_codigo='" . $art_codigo . "'
			    	AND art.art_codigo=enla.ch_item_estandar";

		if ($sqlca->query($sql) < 0)
		    	return null;

		$i = 0;
		$resultado = Array();

		while (($row = $sqlca->fetchRow()) != null) {
		    	$resultado[$row['ch_item_estandar']]['cantidad'] = $row['nu_cantidad_descarga'];
		    	$resultado[$row['ch_item_estandar']]['descripcion'] = $row['art_descripcion'];
		}

		return $resultado;
    	}

    function esItemEnlazado($item, $enlazado) {
		global $sqlca;

		$sql = "SELECT
			    	ch_item_estandar
			FROM
			    	int_ta_enlace_items
			WHERE
				art_codigo='" . pg_escape_string($item) . "'
			    	AND ch_item_estandar='" . pg_escape_string($enlazado) . "'";

		if ($sqlca->query($sql) < 0)
		    	return false;

		if ($sqlca->fetchRow() == null)
		    	return false;
		    	
		return true;
    }

    function esItemConAlias($item, $item_alias) {
		global $sqlca;

		$sql = "SELECT codigo_alias FROM int_articulos_alias WHERE art_codigo='" . pg_escape_string($item) . "' AND codigo_alias='" . pg_escape_string($item_alias) . "'";
		if ($sqlca->query($sql) < 0)
	    	return false;
		if ($sqlca->fetchRow() == null)
	    	return false;	    	
		return true;
    }

    function aliasItem($item, $enlace) {
		global $sqlca;

		$sql = "SELECT * FROM int_articulos_alias WHERE codigo_alias='" . pg_escape_string($enlace) . "' LIMIT 1";
		$iStatusSQL = (int)$sqlca->query($sql);
		if( $iStatusSQL < 0 ){
			return array(
			    'sStatus' => 'danger',
			    'sMessage' => 'Problemas al verificar alias',
			    'sMessageSQL' => $sqlca->get_error(),
			);
		} else if( $iStatusSQL == 0 ){
			$sql = "INSERT INTO int_articulos_alias (art_codigo, codigo_alias) VALUES('" . pg_escape_string($item) . "','" . pg_escape_string($enlace) . "')";
			$iStatusSQL = (int)$sqlca->query($sql);
			if ( $iStatusSQL < 0 ) {
				return array(
				    'sStatus' => 'warning',
				    'sMessage' => 'Problemas al agregar',
			  		'sMessageSQL' => $sqlca->get_error(),
				);
			}
			return array(
			    'sStatus' => 'success',
			    'sMessage' => 'Registro guardado',
			);
		} else {
			return array(
			    'sStatus' => 'warning',
			    'sMessage' => 'Ya se registro el id alias -> ' . trim($enlace),
			);
		}
    }

    function borrarAlias($item, $enlace) {
		global $sqlca;

		for ($i = 0; $i < count($enlace); $i++) {
			$iIdAlias = trim($enlace[$i]);
	    	$sql = "DELETE FROM int_articulos_alias WHERE art_codigo='" . pg_escape_string($item) . "' AND codigo_alias='" . pg_escape_string($iIdAlias) . "'";
	    	if ($sqlca->query($sql) < 0)
	        	return false;
		}
		return true;
    }

   	function esItemValido($art_codigo) {
       	global $sqlca;

		$sql = "SELECT art_codigo FROM int_articulos WHERE art_codigo='" . pg_escape_string($art_codigo) . "';";
		if ($sqlca->query($sql) < 0)
		    return false;
		if ($sqlca->numrows() > 0)
	    	return true;
		return false;
    }

    function enlazarItem($item, $enlace, $cantidad) {
		global $sqlca;

		if (!ItemsModel::esItemValido($item) || !ItemsModel::esItemValido($enlace))
		    return false;

		$sql = "INSERT INTO
			    	int_ta_enlace_items
				(
				   	art_codigo,
				    	ch_item_estandar,
				    	nu_cantidad_descarga,
				    	dt_fechactualizacion,
				    	flg_replicacion,
				    	fecha_replicacion
				)
			    	VALUES
				(
				    	'" . pg_escape_string($item) . "',
				    	'" . pg_escape_string($enlace) . "',
				    	'" . pg_escape_string($cantidad) . "',
				    	now(),
				    	0,
				    	now()
				)";

		if ($sqlca->query($sql) < 0)
		    return false;
		return true;
    }

    function borrarEnlace($item, $enlace) {
		global $sqlca;

		for ($i = 0; $i < count($enlace); $i++) {
	    	$sql = "DELETE FROM int_ta_enlace_items WHERE art_codigo='" . pg_escape_string($item) . "' AND ch_item_estandar='" . pg_escape_string($enlace[$i]) . "'";
	    	if ($sqlca->query($sql) < 0)
	        	return false;
		}
		return true;
    }

    function actualizarEnlace($item, $enlace, $cantidad) {
		global $sqlca;

		$sql = "UPDATE
			   	int_ta_enlace_items
			SET
			    	nu_cantidad_descarga='" . pg_escape_string($cantidad) . "',
			    	dt_fechactualizacion = now(),
			    	flg_replicacion = 0,
			    	fecha_replicacion = now()
			WHERE 
			    	art_codigo='" . pg_escape_string($item) . "'
				AND ch_item_estandar='" . pg_escape_string($enlace) . "'";

		if ($sqlca->query($sql) < 0)
		    	return false;

		//@INICIO:: Ejecutando la Funcion para Crear Textos para el Sistema de Consulta 	
		$query_funcion = "select interface_central_fn_maestros_consulta( to_date( to_char( now(),'yyyy-mm-dd'),'yyyy-mm-dd' ) )";
		if ($sqlca->query($query_funcion) < 0)
		    	return false;
		//@FIN:: Ejecutando la Funcion para Crear Textos para el Sistema de Consulta 	

		return true;
    }

    	function busqueda($desde, $cantidad, $codigo, $descripcion, $ubicacion, $linea, $orderby, $order, $articulos, $almacen) {
		global $sqlca;
		
		$mes = date("m");
		$ano = date("Y");

		switch ($orderby) {
		    	case 'art_codigo':
		    		default:
		        	$order_column = 'art.art_codigo';
		        	break;
		    	case 'art_descripcion':
				$order_column = 'art.art_descripcion';
				break;
		    	case 'art_precio':
				$order_column = 'f.pre_precio_act1 ';
				break;
		    	case 'art_linea':
				$order_column = 'tab.tab_descripcion';
				break;
		    	case 'art_tipo':
				$order_column = 'art.art_tipo';
				break;
		    	case 'art_unidad':
				$order_column = 'art.art_unidad';
				break;
		    	case 'art_ubicacion':
				$order_column = 'art.art_cod_ubicac';
				break;
		    	case 'art_sku':
				$order_column = 'art.art_cod_sku';
				break;
		    	case 'art_activo':
				$order_column = 'art.art_estado';
				break;
		    	case 'art_stock':
		        	$order_column = 'inv_saldoalma.stk_stock' . $mes . ' ';
		        	break;
		}

        	switch ($order) {
            		case 'asc':
            		case 'ASC':
            		default:
				$order_order = 'ASC';
				break;
            		case 'desc':
            		case 'DESC':
				$order_order = 'DESC';
				break;
        	}

		$articulos = str_replace(",", "','", $articulos);

		$campo = ", inv_saldoalma.stk_stock" . $mes . ", f.pre_precio_act1 ";

		if(empty($almacen))
			$almacen = $_SESSION["almacen"];

		$join = "LEFT JOIN
			      	inv_saldoalma ON (art.art_codigo = inv_saldoalma.art_codigo AND inv_saldoalma.stk_periodo='" . pg_escape_string($ano) . "' AND inv_saldoalma.stk_almacen='" . $almacen . "')
			 LEFT JOIN 
			 	fac_lista_precios f ON f.art_codigo=art.art_codigo and f.pre_lista_precio='01' ";

		$sql = "SELECT
			    art.art_codigo,
			    trim(art.Art_Descripcion) as art_descripcion,
			    CASE
				WHEN tab.tab_descripcion IS NULL THEN '--- SIN LINEA ---'
				ELSE trim(tab.tab_descripcion)
			    END AS tab_descripcion,
			    art.art_tipo,
			    art.art_unidad,
			    art.art_cod_ubicac,
			    art.art_cod_sku,
			    art.art_estado,
			    art.art_usuario
			    $campo
			FROM
			    Int_Articulos art
			    LEFT JOIN Int_tabla_General tab ON (tab.tab_tabla='20' AND tab.tab_elemento=art.art_linea)
			$join
			WHERE
				(1=1)
			";
        	if ($codigo != "")
            		$sql .= "AND art.art_codigo LIKE '%" . pg_escape_string($codigo) . "%' ";
        	if ($descripcion != "")
            		$sql .= "AND art.art_descripcion LIKE '%" . pg_escape_string($descripcion) . "%' ";
       		if ($ubicacion != "")
            		$sql .= "AND art.art_cod_ubicac = '" . pg_escape_string($ubicacion) . "' ";
        	if ($linea != "")
            		$sql .= "AND (art.art_linea = '" . pg_escape_string($linea) . "' OR upper(tab.tab_descripcion) LIKE '%" . pg_escape_string(strtoupper($linea)) . "%') ";
		if ($articulos != "")
            		$sql .= "AND art.art_codigo IN ('$articulos') ";

        	$sql .= "
			ORDER BY
		    	" . $order_column . " " . $order_order . "
			";

		//echo "QUERY : $sql \n";

		if ($cantidad > 0)
		    	$sql .= "LIMIT " . pg_escape_string($cantidad) . " ";
		if ($desde > 0)
		    	$sql .= "OFFSET " . pg_escape_string($desde);

		if ($sqlca->query($sql) < 0)
		    	return null;

		$result = Array();
		$i = 0;

		while (($row = $sqlca->fetchRow()) != null) {
		    	$result[$i] = $row;
		    	$i++;
		}

		return $result;
    	}

    	function busquedaObtieneCantidad($codigo, $descripcion, $ubicacion, $linea) {
		global $sqlca;

		$sql = "
			SELECT
				count(art.art_codigo)
			FROM
				int_articulos art,
				int_tabla_general tab
			WHERE
				tab.tab_tabla		= '20'
				AND tab.tab_elemento	= art.art_linea
		";

		if ($codigo != "")
		    $sql .= "AND art.art_codigo ~ '" . pg_escape_string($codigo) . "' ";

		if ($descripcion != "")
		    $sql .= "AND art.art_descripcion ~ '" . pg_escape_string(trim($descripcion)) . "' ";

		if ($ubicacion != "")
		    $sql .= "AND art.art_cod_ubicac = '" . pg_escape_string(trim($ubicacion)) . "' ";

		if ($linea != "")
		    $sql .= "AND art.art_linea = '" . pg_escape_string(trim($linea)) . "' ";

		//echo "QUERY : $sql";

		if ($sqlca->query($sql) < 0)
		    return 0;

		$r = $sqlca->fetchRow();

        	return $r[0];

    	}

    	function agregarItem($usuario) {
	    	global $sqlca;
	    	
	    	// Listando todas las listas de precio que hay
		$lprecios = Array();
		$sql2 = "SELECT tab_elemento FROM int_tabla_general WHERE tab_tabla='LPRE' AND trim(tab_elemento)!='' AND tab_elemento!='000000' AND tab_elemento IS NOT NULL;";
		if ($sqlca->query($sql2)<=0)
			return $sqlca->get_error();
		for($j=0; $j<$sqlca->numrows(); $j++) {
			$a = $sqlca->fetchRow();
			$lprecios[$j] = $a[0];
		}  
		// Fin de listado de precios      	

		/*
		$badUTF8 	= htmlentities($_REQUEST["descripcion"]);
		$descripcion = iconv("utf-8", "utf-8//IGNORE", $badUTF8);

		$badUTF82 	= htmlentities($_REQUEST["descbreve"]);
		$descbreve 	= iconv("utf-8", "utf-8//IGNORE", $badUTF82);
		*/

		var_dump($_REQUEST);

		$codigo 				= $_REQUEST['codigo'];
		$desc 					= $_REQUEST["descripcion"];
		$descbreve 				= $_REQUEST["descbreve"];
		$tipo 					= $_REQUEST['tipo'];
		$linea 					= $_REQUEST['linea'];
		$plutipo 				= $_REQUEST['plu'];
		$presentacion 			= $_REQUEST['unidad'];
		$imp1 					= "";
		$imp2 					= $_REQUEST['imp2'];
		$marca 					= "'" . $_REQUEST['marca'] . "'";
		$codsku 				= $_REQUEST['sku'];
		$ubicacion 				= $_REQUEST['ubicacion'];

		$listacodigos 			= $_REQUEST['listacod'];
        $listaprecios 			= $_REQUEST['listaprecio'];

		$art_costoinicial 		= $_REQUEST['art_costoinicial'];
		$art_costoreposicion 	= $_REQUEST['art_costoreposicion'];
		$art_presentacion 		= pg_escape_string($_REQUEST['art_presentacion']);

		if ($tipo == "all") {
		    	return false;
		}
		if ($linea == "all") {
		    	return false;
		}

		if ($marca == "all") {
		    	return false;
		}

		if ($codsku == "all") {
		    	$codsku = "NULL";
		} else {
		    	$codsku = "'" . trim($codsku) . "'";
		}
		if ($presentacion == "all") {
		    	return false;
		}

		if ($art_presentacion == "all") {
		    	return false;
		}

		if ($ubicacion == "all") {
		    	return false;
		}

		$art_estado = ($_REQUEST['activo'] == "S" ? "0" : "1");
		if ($_REQUEST['reposicion'] == "") {
		    	$plzrepo = "NULL";
		} else {
		    	$plzrepo = "'" . $_REQUEST['reposicion'] . "'";
		}
		if ($_REQUEST['dias'] == "") {
		    	$dias = "NULL";
		} else {
		    	$dias = "'" . $_REQUEST['dias'] . "'";
		}

		if ($_REQUEST['impuesto'] == "all") {
		    	$impuesto = "NULL";
		} else {
		    	$impuesto = "'" . trim($_REQUEST['impuesto']) . "'";
		}

		if ($art_costoinicial == '') {
		    	$art_costoinicial = "NULL";
		}

		if ($art_costoreposicion == '') {
		    	$art_costoreposicion = "NULL";
		}

		$sqlca->query("BEGIN");

		// Agregar codigo al maestro de items 
		$sql = "INSERT INTO
			    	int_articulos 
			    	(
					art_codigo,
					art_descripcion,
					art_descbreve,
					art_tipo,
					art_linea,
					art_unidad,
					art_presentacion,
					art_plutipo,
					art_plazoreposicprom,
					art_diasreposic,
					art_cod_ubicac,
					art_cod_sku,
					art_clase,
					art_impuesto1,
					flg_replicacion,
					art_estado,
					art_fecactuliz,
					art_costoinicial,
					art_costoreposicion,
					art_usuario
			    	)
				VALUES 
				(
				    	'" . pg_escape_string($codigo) . "',
				    	'" . pg_escape_string($desc) . "',
				    	'" . pg_escape_string($descbreve) . "',
				    	'" . pg_escape_string($tipo) . "',
				    	'" . pg_escape_string($linea) . "',
				    	'" . pg_escape_string($presentacion) . "',
				    	'" . pg_escape_string($art_presentacion) . "',
				    	'" . pg_escape_string($plutipo) . "',
				    	" . $plzrepo . ",
				    	" . $dias . ",
				    	'" . pg_escape_string($ubicacion) . "',
				    	" . $codsku . ",
				    	" . $marca . ",
				    	" . $impuesto . ",
				    	0,
				    	'" . pg_escape_string($art_estado) . "',
				    	now(),
				    	$art_costoinicial,
				    	$art_costoreposicion,
				    	'" . pg_escape_string($usuario) . "'
				)";
		//echo "QUERY : $sql \n";
		if ($sqlca->query($sql) < 0) {
		    	$sqlca->query("ROLLBACK");
		    	return 0;
		}

		// Agregar precio base del producto 
		/*for($k=0; $k<count($lprecios); $k++) {
			$sql = "INSERT INTO
					fac_lista_precios 
					(
						pre_lista_precio,
						art_codigo,
						pre_moneda,
						pre_precio_act1,
						pre_fecactualiz,
						flg_replicacion,
						fecha_replicacion
					)
					VALUES 
					(
						'".$lprecios[$k]."',
						'" . pg_escape_string($codigo) . "',
						'01',
						'" . pg_escape_string($precio) . "',
						now(),
					    	0,
					    	now()
					)";

			//echo "QUERY2 : $sql \n";
			if ($sqlca->query($sql) < 0) {
				$sqlca->query("ROLLBACK");
				return 0;
			}
		}
		*/
		for ($k = 0; $k < count($listacodigos); $k++) {

		if($listaprecios[$k] == '' && $listaprecios[$k] == NULL) {
			$precio = 0.00;
		}else{
			$precio = $listaprecios[$k];
		}

		$sql = "INSERT INTO
			    fac_lista_precios (
				pre_lista_precio,
				art_codigo,
				pre_moneda,
				pre_precio_act1,
				pre_precio_fec1,
				flg_replicacion
			    )
			VALUES (
				'" . $listacodigos[$k] . "',
				'" . pg_escape_string($codigo) . "',
				'1',
				'" . $precio . "',
				now(),
				0
			    )
			;";

		//echo "SQL : $sql ";
		if ($sqlca->query($sql) < 0) {
				$sqlca->query("ROLLBACK");
				return 0;
		}

		}


		$sqlca->query("COMMIT");
		return true;
    	}

    	function obtenerPreciosPorItem($codigo) {
		global $sqlca;
		
		$sql = "SELECT
			    pre_lista_precio,
			    pre_precio_act1
			FROM
			    fac_lista_precios
			WHERE
			    art_codigo='" . pg_escape_string($codigo) . "'
			;";
		if ($sqlca->query($sql) < 0)
		    return Array();

		while (($row = $sqlca->fetchRow()) != null) {
		    $result[$row[0]] = $row[1];
		}

		return $result;
    	}


    	function obtenerPreciosPorItemNew($codigo) {
		global $sqlca;
		
		$sql = "SELECT
			pre.pre_lista_precio,
			CASE WHEN tab.tab_descripcion IS NULL THEN 'LISTA NO AGREGADA' ELSE tab.tab_descripcion END,
			pre.pre_precio_act1
			FROM
			fac_lista_precios pre
			LEFT JOIN int_tabla_general tab 
			ON(pre.pre_lista_precio = tab.tab_elemento AND tab.tab_tabla='LPRE' AND tab.tab_elemento<>'000000') 
			WHERE
			pre.art_codigo='" . pg_escape_string($codigo) . "'
			UNION
			SELECT
			CASE WHEN pre.pre_lista_precio IS NULL THEN tab.tab_elemento ELSE pre.pre_lista_precio END,
			tab.tab_descripcion,
			pre.pre_precio_act1
			FROM
			fac_lista_precios pre
			RIGHT JOIN int_tabla_general tab 
			ON(pre.pre_lista_precio = tab.tab_elemento AND pre.art_codigo='" . pg_escape_string($codigo) . "') 
			WHERE
			tab.tab_tabla='LPRE' 
			AND tab.tab_elemento<>'000000'
			;";
			
		if ($sqlca->query($sql) < 0)
		    return Array();

		$result = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();

			$result[$i]['pre_lista'] 	= trim($a[0]);
			$result[$i]['tab_descr'] 	= $a[1];
			$result[$i]['pre_precio'] 	= $a[2];
		}
		
		return $result;

    	}

    	function obtenerPreciosPorItemNewAgregar() {
		global $sqlca;
		
		$sql = "SELECT 
				tab_elemento as pre_lista_precio, 
				tab_descripcion as tab_descripcion,
				'' as pre_precio_act1
				FROM int_tabla_general 
				WHERE 
				tab_tabla='LPRE' 
				AND tab_elemento<>'000000' 
				;";
			
		if ($sqlca->query($sql) < 0)
		    return Array();

		$result = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();

			$result[$i]['pre_lista'] 	= trim($a[0]);
			$result[$i]['tab_descr'] 	= $a[1];
			$result[$i]['pre_precio'] 	= $a[2];
		}
		
		return $result;

    	}


    	function obtenerPreciosPorItemLista($codigo, $lista) {
		global $sqlca;
		
		$sql = "SELECT
			    pre_precio_act1
			FROM
			    fac_lista_precios
			WHERE
				art_codigo='" . pg_escape_string($codigo) . "'
			    AND pre_lista_precio='" . pg_escape_string($lista) . "'
			;";
		if ($sqlca->query($sql) < 0)
		    return 0;
		$result = $sqlca->fetchRow();
		if ($result == null)
		    return 0;
		    
		return $result[0];
    	}

    	function obtienePrecioItem($codigo, $lista = "") {
		if ($lista == "")
		    $lista = $_SESSION['almacen'];
		if (strlen($lista) > 2)
		    $lista = substr($lista, -2);
		$precio = ItemsModel::obtenerPreciosPorItemLista($codigo, $lista);
		if ($precio == 0 && $lista != "01")
		    $precio = ItemsModel::obtenerPreciosPorItemLista($codigo, '01');
		    
		return $precio;
    	}

    	function precioAgregar($codigo, $lista, $precio) {
		global $sqlca;

		$sql = "INSERT INTO
			    fac_lista_precios (
				pre_lista_precio,
				art_codigo,
				pre_moneda,
				pre_precio_act1,
				pre_precio_fec1,
				flg_replicacion
			    )
			VALUES (
				'" . pg_escape_string($lista) . "',
				'" . pg_escape_string($codigo) . "',
				'1',
				'" . pg_escape_string($precio) . "',
				now(),
				0
			    )
			;";

		$sqlca->query($sql);

		//@INICIO:: Ejecutando la Funcion para Crear Textos para el Sistema de Consulta 	
		$query_funcion = "select interface_central_fn_maestros_consulta( to_date( to_char( now(),'yyyy-mm-dd'),'yyyy-mm-dd' ) )";
		//@FIN:: Ejecutando la Funcion para Crear Textos para el Sistema de Consulta 	
		//if ($sqlca->query($query_funcion) < 0) return false;

		DatGenEnviar();
    	}

    	function precioModificar($codigo, $listacodigos, $listaprecios) {
		global $sqlca;

		//var_dump($listacodigos);
		//var_dump($listaprecios);
		$sql = "BEGIN";
		$sqlca->query($sql);

		$sql = "DELETE FROM fac_lista_precios WHERE art_codigo='" . pg_escape_string($codigo) . "';";
	
		if ($sqlca->query($sql) < 0) {
		    	$sqlca->query("ROLLBACK");
		    	return 0;
		}


		for ($k = 0; $k < count($listacodigos); $k++) {

		if($listaprecios[$k] != '' && $listaprecios[$k] != NULL) {
			$sql = "INSERT INTO
			    fac_lista_precios (
				pre_lista_precio,
				art_codigo,
				pre_moneda,
				pre_precio_act1,
				pre_precio_fec1,
				flg_replicacion
			    )
			VALUES (
				'" . $listacodigos[$k] . "',
				'" . pg_escape_string($codigo) . "',
				'1',
				'" . $listaprecios[$k] . "',
				now(),
				0
			    )
			;";

		//echo "SQL : $sql ";
		if ($sqlca->query($sql) < 0) {
		    	$sqlca->query("ROLLBACK");
		    	return 0;
			}

		}

		}

		$sqlca->query("COMMIT");
		
        return 1;
    	}

    	function Validar($codigo) {
		global $sqlca;

		$sql = "SELECT 1 FROM fac_ta_factura_detalle WHERE art_codigo = '" . pg_escape_string($codigo) . "' LIMIT 1;";

	//echo $sql;
		if ($sqlca->query($sql) < 0)
		    return false;
		
		$row = $sqlca->fetchRow();

        	return $row[0];

    	}

    	function precioBorrar($codigo) {
		global $sqlca;

		//var_dump($listacodigos);

		$sql = "DELETE FROM fac_lista_precios WHERE art_codigo='" . pg_escape_string($codigo) . "';";
		
		//echo "SQL : $sql ";
		if ($sqlca->query($sql) < 0)
		    return 0;

		

		/*
		//@INICIO:: Ejecutando la Funcion para Crear Textos para el Sistema de Consulta 	
		$query_funcion = "select interface_central_fn_maestros_consultad( to_date( to_char( now(),'yyyy-mm-dd'),'yyyy-mm-dd' ) )";
		if ($sqlca->query($query_funcion) < 0)
		    return 0;
		//@FIN:: Ejecutando la Funcion para Crear Textos para el Sistema de Consulta 	
		*/
		//DatGenEnviar();
		
        	return 1;

		/*
		$sql = "DELETE FROM
			    fac_lista_precios
			WHERE
				art_codigo='" . pg_escape_string($codigo) . "'
			    AND pre_lista_precio='" . pg_escape_string($lista) . "'
			;";
		if ($sqlca->query($sql) < 0)
		    return 0;
		DatGenEnviar();
		
		return 1;
		*/
    	}

    	function esListaValida($lista) {
		global $sqlca;

		$sql = "SELECT
			    tab_elemento
			FROM
			    int_tabla_general
			WHERE
				tab_tabla='LPRE'
			    AND tab_elemento='" . pg_escape_string($lista) . "'
			;";

		if ($sqlca->query($sql) < 0)
		    return false;

		if ($sqlca->numrows() != 1)
		    return false;

		return true;
    	}

    	function ObtenerMargenLinea($linea) {
		global $sqlca;
		
		$sql = "SELECT coalesce(tab_num_01,0) FROM int_tabla_general WHERE tab_tabla='20' AND tab_elemento='" . pg_escape_string($linea) . "'";
		if ($sqlca->query($sql) < 0)
		    return false;
		if ($sqlca->numrows() != 1)
		    return false;
		$row = $sqlca->fetchRow();
		
		return $row[0];
    	}

    function busquedaExcel($codigo, $descripcion, $ubicacion, $linea, $almacen) {
		global $sqlca;
	
		$anio  	= date("Y");
		$mes  	= date("m");

		if(empty($almacen))
			$almacen = $_SESSION["almacen"];

		$cond_almacen = "AND sa.stk_almacen = '".$almacen."'";
		$sql_2 = "SELECT * FROM pos_aprosys LIMIT 1;";
		$iStatusSQL = $sqlca->query($sql_2);
		if ( $iStatusSQL <= 0 ) {
		    $cond_almacen = "";
		}

		$sql = "
SELECT
 art.art_linea codlinea,
 CASE WHEN tab.tab_descripcion IS NULL THEN '--- SIN LINEA ---' ELSE trim(tab.tab_descripcion) END AS linea,
 art.art_codigo as codigo,
 trim(art.art_descripcion) as descripcion,
 f.pre_precio_act1 as precio,
 art.art_tipo || ' - ' || ti.tab_descripcion as tipo,
 art.art_unidad as unidad,
 art.art_cod_ubicac || ' - ' || ubi.desc_ubicac as ubicacion,
 art.art_cod_sku as codsku,
 CASE WHEN art.art_estado='0' THEN 'SI' ELSE 'NO' END as estado,
 sa.stk_stock".$mes." stock,
 sa.stk_costo".$mes." costo,
 (CASE WHEN art.art_plutipo = '1' THEN 'ESTANDAR' ELSE 'PLU SALIENTE' END) AS notipoproducto
FROM
 int_articulos art
 LEFT JOIN int_tabla_general AS tab ON (tab.tab_tabla='20' AND tab.tab_elemento=art.art_linea)
 LEFT JOIN inv_saldoalma AS sa ON (art.art_codigo=sa.art_codigo AND sa.stk_periodo='" . $anio . "')
 LEFT JOIN fac_lista_precios AS f ON (f.art_codigo=art.art_codigo and f.pre_lista_precio='01')
 LEFT JOIN int_tabla_general AS ti ON(art.art_tipo = ti.tab_elemento AND ti.tab_tabla = '21' AND (ti.tab_elemento != '000000' AND ti.tab_elemento != ''))--TIPO DE ITEM
 LEFT JOIN inv_ta_ubicacion AS ubi ON(ubi.cod_ubicac = art.art_cod_ubicac AND ubi.cod_almacen = sa.stk_almacen)
WHERE
 (1=1)
 " . $cond_almacen;
if ($codigo != "")
 $sql .= "AND art.art_codigo LIKE '%".pg_escape_string($codigo)."%' ";
if ($descripcion != "")
 $sql .= "AND art.art_descripcion LIKE '%".pg_escape_string($descripcion)."%' ";
if ($ubicacion != "")
 $sql .= "AND art.art_cod_ubicac = '".pg_escape_string($ubicacion)."' ";
if ($linea != "")
 $sql .= "AND (art.art_linea = '".pg_escape_string($linea)."' OR upper(tab.tab_descripcion) LIKE '%".pg_escape_string(strtoupper($linea))."%') ";
$sql .= "
ORDER BY
 art.art_codigo;";

 		/*
		echo "<pre>";
		print_r($sql);
		echo "</pre>";
		*/

		if ($sqlca->query($sql) <= 0)
			return $sqlca->get_error();
	    
		$res = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$res[$i]['codlinea'] 		= $a[0];
			$res[$i]['linea'] 			= $a[1];
			$res[$i]['codigo'] 			= $a[2];
			$res[$i]['descripcion'] 	= $a[3];
			$res[$i]['precio'] 			= $a[4];
			$res[$i]['tipo'] 			= $a[5];
			$res[$i]['unidad'] 			= $a[6];
			$res[$i]['ubicacion'] 		= $a[7];
			$res[$i]['codsku'] 			= $a[8];
			$res[$i]['estado'] 			= $a[9];
			$res[$i]['stock'] 			= $a[10];
			$res[$i]['costo'] 			= $a[11];
			$res[$i]['notipoproducto'] 	= $a[12];
		}
		return $res;
    }

	function ValidarRegistrosExcel($codproducto, $codlinea, $marca, $codunidad, $codinventario){
		global $sqlca;
	
		// $sql = "
		// SELECT
		// 	count(*) articulo,
		// 	(SELECT tab_descripcion FROM int_tabla_general where tab_tabla = '20' AND tab_elemento = '".pg_escape_string($codlinea)."') linea,--LINEAS
		// 	(SELECT tab_elemento FROM int_tabla_general where tab_tabla = '23' AND TRIM(tab_descripcion) = TRIM('".pg_escape_string($marca)."')) codmarca,--MARCAS
		// 	(SELECT tab_descripcion FROM int_tabla_general where tab_tabla = '23' AND TRIM(tab_descripcion) = TRIM('".pg_escape_string($marca)."')) marca,--MARCAS
		// 	(SELECT tab_descripcion FROM int_tabla_general where tab_tabla = '34' AND tab_elemento = '".pg_escape_string($codunidad)."') unidad,--UNIDAD DE MEDIDA
		// 	(SELECT desc_ubicac FROM inv_ta_ubicacion where cod_ubicac = (LPAD(CAST('".pg_escape_string($codinventario)."' AS bpchar),6,'0')) AND cod_almacen = '".pg_escape_string($_SESSION['almacen'])."') inventario--UBICACION DE INVENTARIO
		// FROM
		// 	int_articulos
		// WHERE
		// 	art_codigo = '".pg_escape_string($codproducto)."';
		// ";

		$sql = "
			SELECT
				count(*) articulo,
				(SELECT tab_descripcion FROM int_tabla_general where tab_tabla = '20' AND tab_elemento = '".pg_escape_string($codlinea)."') linea,--LINEAS
				(SELECT tab_elemento FROM int_tabla_general where tab_tabla = '23' AND TRIM(tab_descripcion) = TRIM('".pg_escape_string($marca)."')) codmarca,--MARCAS
				(SELECT tab_descripcion FROM int_tabla_general where tab_tabla = '23' AND TRIM(tab_descripcion) = TRIM('".pg_escape_string($marca)."')) marca,--MARCAS
				(SELECT tab_descripcion FROM int_tabla_general where tab_tabla = '34' AND tab_elemento = '".pg_escape_string($codunidad)."') unidad,--UNIDAD DE MEDIDA
				(SELECT desc_ubicac FROM inv_ta_ubicacion where cod_ubicac = (LPAD(CAST('".pg_escape_string($codinventario)."' AS bpchar),6,'0')) AND cod_almacen = '".pg_escape_string($_SESSION['almacen'])."') inventario--UBICACION DE INVENTARIO
			FROM
				int_articulos
			WHERE
				art_codigo = '".pg_escape_string($codproducto)."';
			";
		
		echo $sql."\n";

		if($sqlca->query($sql) < 0)
			return false;

		$data = Array();

		if ($sqlca->numrows()==1){
			$data = $sqlca->fetchRow();
			return array($data);
		}

	}
    	
	function InsertarExcel($data, $usuario){
		global $sqlca;

		$resultados 	= count($data->sheets[0]['cells']);
		echo "<script>console.log('" . json_encode($resultados) . "')</script>"; //Agregado 2020-01-22            

		$a = 0;
		$b = 0;
		$c = 0;
		$d = 0;
		$ins = true;
		$codigoexcel	= "";

		for ($i = 2; $i <= $resultados; $i++) {

			/* VALIDACIONES DE REGISTROS DE EXCEL */								
			
			$codlinea	= $data->sheets[0]['cells'][$i][1];
			$marca		= $data->sheets[0]['cells'][$i][3];
			$codproducto	= $data->sheets[0]['cells'][$i][4];
			$producto	= $data->sheets[0]['cells'][$i][5];
			$codunidad	= $data->sheets[0]['cells'][$i][6];
			$codinventario	= $data->sheets[0]['cells'][$i][8];
			$precio		= $data->sheets[0]['cells'][$i][10];
			$costo		= $data->sheets[0]['cells'][$i][11];
			/*** Agregado 2020-01-22 ***/
			echo "<script>console.log('Query $i (codlinea): " . json_encode($codlinea) . "')</script>";
			echo "<script>console.log('Query $i (marca): " . json_encode($marca) . "')</script>";
			echo "<script>console.log('Query $i (codproducto): " . json_encode($codproducto) . "')</script>";
			echo "<script>console.log('Query $i (producto): " . json_encode($producto) . "')</script>";
			echo "<script>console.log('Query $i (codunidad): " . json_encode($codunidad) . "')</script>";
			echo "<script>console.log('Query $i (codinventario): " . json_encode($codinventario) . "')</script>";
			echo "<script>console.log('Query $i (precio): " . json_encode($precio) . "')</script>";
			echo "<script>console.log('Query $i (costo): " . json_encode($costo) . "')</script>";
			// die();
			/***/

			$datos = ItemsModel::ValidarRegistrosExcel($codproducto, $codlinea, $marca, $codunidad, $codinventario);			
			echo "<script>console.log('Query $i (ValidarRegistrosExcel): " . json_encode($datos) . "')</script>"; //Agregado 2020-01-22			

			if($codigoexcel == $codproducto){
				$d++;//ARTICULOS DUPLICADOS
			}elseif($datos[0]['articulo'] == "0"){				

				if($datos[0]['linea'] == NULL)
					$ins = false;

				if($datos[0]['marca'] == NULL)
					$ins = false;

				if($datos[0]['unidad'] == NULL)
					$ins = false;

				if($datos[0]['inventario'] == NULL)
					$ins = false;

				echo "<script>console.log('Query $i (ValidarRegistrosExcel): " . json_encode($ins) . "')</script>"; //Agregado 2020-01-22

				if($ins){

					$a++;//PRODUCTOS INSERTADOS

					$codproductob .= $codproducto.",";

					$sql = "
						INSERT INTO
							int_articulos(
									art_linea,
									art_clase,
									art_codigo,
									art_descripcion,
									art_descbreve,
									art_unidad,
									art_tipo,
									art_cod_ubicac,
									art_costoinicial,
									nu_dias_minimo,
									nu_dias_maximo,
									flg_replicacion,
									art_impuesto1,
									art_plutipo,
									art_estado,
									art_presentacion,
									art_modifica_articulo,
									art_usuario,
									art_fecactuliz
							)VALUES(
									'".pg_escape_string($codlinea)."',
									'".pg_escape_string($datos[0]['codmarca'])."',
									'".pg_escape_string($codproducto)."',
									'".pg_escape_string($producto)."',
									'".pg_escape_string(substr($producto,0,20))."',
									'".pg_escape_string($codunidad)."',
									'05',
									(LPAD(CAST('".pg_escape_string($codinventario)."' AS bpchar),6,'0')),
									'".pg_escape_string($costo)."',
									'15',
									'30',
									'0',
									'000009',
									'1',
									'0',
									'5',
									'0',
									'".pg_escape_string($usuario)."',
									now()
							)
						";
					
					echo "ARTICULO:\n".$sql."\n";

					if($sqlca->query($sql) < 0)
						return false;

					$sql = "
						INSERT INTO
							fac_lista_precios (
									pre_lista_precio,
									art_codigo,
									pre_moneda,
									pre_precio_act1,
									pre_precio_fec1,
									flg_replicacion
					    		)VALUES(
									'01',
									'" . pg_escape_string($codproducto) . "',
									'01',
									'" . pg_escape_string($precio) . "',
									now(),
									0
					    		);";

					//echo "PRECIO:\n".$sql."\n";

					if($sqlca->query($sql) < 0)
						return false;

				}else{
					$c++;//PRODUCTOS ERRADOS
				}

			}else{
				$b++;//PRODUCTOS EXISTENTES
			}

			$codigoexcel = $codproducto;

		}

		return array(true, $a, $b, $codproductob, $c, $d);

	}

	/**
	* Verificar la siguiente información historica para item:
	- Movimiento de inventario - inv_movialma - art_codigo
	- Notas de despacho - val_ta_detalle - ch_articulo
	- Documento manuales de Venta - fac_ta_factura_detalle - art_codigo
	- Orden de Compra - com_detalle - art_codigo
	*/

	public function checkHistoryItem($sIdItem){
		global $sqlca;

		$sql = "SELECT art_codigo FROM inv_movialma WHERE art_codigo = '" . pg_escape_string($sIdItem) . "' LIMIT 1";
    	$iStatusSQL = $sqlca->query($sql);
    	if ( (int)$iStatusSQL < 0 ) {
    		return array(
				'sMessageSQL' => $sqlca->get_error(),
				'sStatus' => 'danger',
				'sMessage' => 'Problemas al buscar ITEM en movimiento de inventario',
			);
    	} else if ( (int)$iStatusSQL >= 1 ) {
    		return array(
				'sStatus' => 'warning',
				'sMessage' => 'El ITEM tiene movimientos de inventario',
			);
    	} else if ( $iStatusSQL == 0 ) {
			$sql = "SELECT ch_articulo FROM val_ta_detalle WHERE ch_articulo = '" . pg_escape_string($sIdItem) . "' LIMIT 1";
	    	$iStatusSQL = $sqlca->query($sql);
	    	if ( (int)$iStatusSQL < 0 ) {
	    		return array(
					'sMessageSQL' => $sqlca->get_error(),
					'sStatus' => 'danger',
					'sMessage' => 'Problemas al buscar ITEM en notas de despacho',
				);
	    	} else if ( (int)$iStatusSQL >= 1 ) {
	    		return array(
					'sStatus' => 'warning',
					'sMessage' => 'El ITEM tiene movimientos en notas de despacho',
				);
	    	} else if ( $iStatusSQL == 0 ) {
				$sql = "SELECT art_codigo FROM fac_ta_factura_detalle WHERE art_codigo = '" . pg_escape_string($sIdItem) . "' LIMIT 1";
		    	$iStatusSQL = $sqlca->query($sql);
		    	if ( (int)$iStatusSQL < 0 ) {
		    		return array(
						'sMessageSQL' => $sqlca->get_error(),
						'sStatus' => 'danger',
						'sMessage' => 'Problemas al buscar ITEM en documentos manuales en venta',
					);
		    	} else if ( (int)$iStatusSQL >= 1 ) {
		    		return array(
						'sStatus' => 'warning',
						'sMessage' => 'El ITEM tiene movimientos en documentos manuales en venta',
					);
		    	} else if ( $iStatusSQL == 0 ) {
		    		$sql = "SELECT art_codigo FROM com_detalle WHERE art_codigo = '" . pg_escape_string($sIdItem) . "' LIMIT 1";
			    	$iStatusSQL = $sqlca->query($sql);
			    	if ( (int)$iStatusSQL < 0 ) {
			    		return array(
							'sMessageSQL' => $sqlca->get_error(),
							'sStatus' => 'danger',
							'sMessage' => 'Problemas al buscar ITEM en orden de compra',
						);
			    	} else if ( (int)$iStatusSQL >= 1 ) {
			    		return array(
							'sStatus' => 'warning',
							'sMessage' => 'El ITEM tiene movimientos en orden de compra',
						);
			    	} else if ( $iStatusSQL == 0 ){
			    		return array(
							'sStatus' => 'success',
							'sMessage' => 'El ITEM tiene ningún movimiento histórico',
						);
			    	}
		    	}
	    	}
    	}
	}

	public function deleteItem($sIdItem){
		global $sqlca;

        $this->managerTransaction("BEGIN");

		$sql = "DELETE FROM int_ta_enlace_items WHERE art_codigo = '" . pg_escape_string($sIdItem) .  "';";
		$iStatusSQL = $sqlca->query($sql);
		if ( (int)$iStatusSQL < 0 ) {
        	$this->managerTransaction("ROLLBACK");
			return array(
				'sMessageSQL' => $sqlca->get_error(),
				'sStatus' => 'danger',
				'sMessage' => 'Problemas al eliminar item (enlaces)',
			);
		}

		$sql = "DELETE FROM fac_lista_precios WHERE art_codigo = '" . pg_escape_string($sIdItem) .  "';";
		$iStatusSQL = $sqlca->query($sql);
		if ( (int)$iStatusSQL < 0 ) {
        	$this->managerTransaction("ROLLBACK");
			return array(
				'sMessageSQL' => $sqlca->get_error(),
				'sStatus' => 'danger',
				'sMessage' => 'Problemas al eliminar item (precio)',
			);
		}

		$sql = "DELETE FROM int_articulos WHERE art_codigo = '" . pg_escape_string($sIdItem) .  "';";
		$iStatusSQL = $sqlca->query($sql);
		if ( (int)$iStatusSQL < 0 ) {
        	$this->managerTransaction("ROLLBACK");
			return array(
				'sMessageSQL' => $sqlca->get_error(),
				'sStatus' => 'danger',
				'sMessage' => 'Problemas al eliminar item',
			);
		}

        $this->managerTransaction("COMMIT");
		return array(
			'sStatus' => 'success',
			'sMessage' => 'Item eliminado',
		);
	}

    public function managerTransaction($sNameTransaction){
    	global $sqlca;

    	try {
			$iStatusSQL = $sqlca->query($sNameTransaction);
			if ((int)$iStatusSQL < 0) {
			    return array(
			    	'sStatus' => 'danger',
			    	'sMessage' => 'Error al iniciar transacción SQL - function managerTransaction(' . $sNameTransaction . ')',
	                'sMessageSQL' => $sqlca->get_error(),
			   	);
			}
		    return array(
		    	'sStatus' => 'success',
		    	'sMessage' => $sNameTransaction . ' ejecutado satisfactoriamente'
		   	);
    	} catch (Exception $e) {
	        return array(
	            'sStatus' => 'danger',
	            'sMessage' => 'problemas con transacción ' . $sNameTransaction,
                'sMessagePHP' => $e->getMessage(),
	        );    		
    	}
    }

    public function getListaPrecio(){
    	global $sqlca;

    	$sql = "
SELECT
 PRE.pre_lista_precio AS id_lista_precio,
 LPRE.tab_descripcion AS no_lista_precio,
 ITEM.art_codigo AS nu_codigo_item,
 ITEM.art_descripcion AS no_nombre_item,
 PRE.pre_precio_act1
FROM
 fac_lista_precios AS PRE 
 JOIN int_articulos AS ITEM
  USING(art_codigo)
 JOIN int_tabla_general AS LPRE
  ON(PRE.pre_lista_precio = LPRE.tab_elemento AND tab_tabla='LPRE' AND tab_elemento!='000000')
ORDER BY 2;";

    	$iStatusSQL = $sqlca->query($sql);
		if ( (int)$iStatusSQL > 0 ) {
	        return array(
	            'sStatus' => 'success',
	            'arrData' => $sqlca->fetchAll(),
	        );
	    } else if ( $iStatusSQL == 0 ) {
	        return array(
	            'sStatus' => 'warning',
	            'sMessage' => 'No hay registros',
	        );
	    }
	    return array(
	        'sStatus' => 'danger',
	        'sMessage' => 'Problemas al obtener lista de precio',
	        'sql' => $sql,
	        'sMessageSQL' => $sqlca->get_error(),
	    );
    }
}
