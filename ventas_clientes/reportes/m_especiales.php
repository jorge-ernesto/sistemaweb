<?php
class EspecialesModel extends Model{

		function obtenerLineas($codigo) {
			global $sqlca;
		
			$sql = "SELECT
					tab_elemento,
					tab_descripcion || ' - ' || tab_elemento
				FROM
					int_tabla_general
				WHERE
					tab_tabla='20'
					AND tab_elemento!='000000'";

			if(trim($codigo) != 'Codigo' and trim($codigo) != 'Descripcion' and trim($codigo) != '0x' and trim($codigo) != '')
				$sql .= " AND tab_elemento like '".$codigo."%' ";
			if(trim($codigo) == 'Codigo'){
				$sql .= "ORDER BY 1;";
			}else {
				$sql .= "ORDER BY 2;";
			}	
		
			if ($sqlca->query($sql) < 0)
				return false;
			
			$resultado = Array();

			for ($i = 0; $i < $sqlca->numrows(); $i++) {
				$a = $sqlca->fetchRow();
				$resultado[$a[0]] = $a[1];
			}
		
			return $resultado;
		}
		
		function obtenerTipos($codigo) {
		global $sqlca;
		
		$sql = "SELECT
				tab_elemento,
				tab_descripcion || ' - ' || tab_elemento
			FROM
				int_tabla_general
			WHERE
				tab_tabla='21'
				AND tab_elemento!='000000'";

			if(trim($codigo) != 'Codigo' and trim($codigo) != 'Descripcion' and trim($codigo) != '0x' and trim($codigo) != '')
				$sql .= " AND tab_elemento like '".$codigo."%' ";
			if(trim($codigo) == 'Codigo'){
				$sql .= "ORDER BY 1;";
			}else {
				$sql .= "ORDER BY 2;";
			}
							
			if ($sqlca->query($sql) < 0)
				return false;
			
			$resultado = Array();

			for ($i = 0; $i < $sqlca->numrows(); $i++) {
				$a = $sqlca->fetchRow();
				$resultado[$a[0]] = $a[1];
			}

			return $resultado;
		}
		
		function obtenerArticulos($codigo)
		{
			global $sqlca;
			
			$sql = "SELECT
							art_codigo,
							art_descripcion || ' - ' || art_codigo
					FROM
							int_articulos ";
			if(trim($codigo) != 'Codigo' and trim($codigo) != 'Descripcion' and trim($codigo) != '0x' and trim($codigo) != '')
				$sql .= " WHERE art_codigo like '".$codigo."%' ";
			if(trim($codigo) == 'Codigo'){
				$sql .= "ORDER BY 1;";
			}else{
				$sql .= "ORDER BY 2;";
			}
				
			if ($sqlca->query($sql) < 0)
				return false;
			
			$resultado = Array();

			for ($i = 0; $i < $sqlca->numrows(); $i++){
				$a = $sqlca->fetchRow();
				$resultado[$a[0]] = $a[1];
			}
			
			return $resultado;
		}
		
		function obtenerProveedores($codigo)
		{
			global $sqlca;
			
			$sql = "SELECT
							pro_codigo as codigo,
							pro_razsocial || ' - ' || pro_codigo as descripcion
					FROM
							int_proveedores ";
			if(trim($codigo) != 'Codigo' and trim($codigo) != 'Descripcion' and trim($codigo) != '0x' and trim($codigo) != '')
				$sql .= " WHERE pro_codigo like '".$codigo."%' ";
			if(trim($codigo) == 'Codigo'){
				$sql .= "ORDER BY codigo;";}
			else {
				$sql .= "ORDER BY descripcion;";}
			if ($sqlca->query($sql) < 0)
				return false;
			
			$resultado = Array();
			$resultado[000000] = "TODOS";
			for ($i = 0; $i < $sqlca->numrows(); $i++)
			{
				$a = $sqlca->fetchRow();
				$resultado[$a[0]] = $a[1];
			}
			
			return $resultado;
		}
		
		function obtenerAlmacenes() {
		global $sqlca;
			
		$sql = "SELECT
				ch_almacen,
				ch_nombre_almacen || ' - ' || ch_almacen
			FROM
				inv_ta_almacenes
			WHERE
				ch_clase_almacen='1'
			ORDER BY
				ch_nombre_almacen desc;";

			if ($sqlca->query($sql) < 0)
				return false;
			
			$resultado = Array();
			$resultado['TODOS'] = "TODOS";

			for ($i = 0; $i < $sqlca->numrows(); $i++) {
				$a = $sqlca->fetchRow();
				$resultado[$a[0]] = $a[1];
			}
			
			return $resultado;
		}


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
		
		function obtenerReporteVentas($desde, $hasta, $detallado, $conigv, $forma, $orden, $condicion, $lineas, $tipos, $articulos,$proveedores,$almacen)
		{

			global $sqlca; //$_REQUEST['codigo']

			// Paso 1: Comenzar transaccion
			$sql = "BEGIN;";
			$sqlca->query($sql);
			
			// Paso 2: poner toda la data relevante a una tabla temporal donde trabajaremos
			
			$sql = "CREATE TEMPORARY TABLE tmp_ventas_especiales AS (
				 SELECT
								cab.ch_almacen,
								alm.ch_nombre_almacen,
								cab.dt_fac_fecha,
								det.art_codigo,
								trim(art.art_descripcion) AS art_descripcion,
								art.art_linea,
								trim(tab.tab_descripcion) AS art_linea_desc,
								art.art_tipo,
								trim(tab2.tab_descripcion) AS art_tipo_desc,
								(case when det.ch_fac_tipodocumento!='20' THEN det.nu_fac_cantidad else det.nu_fac_cantidad*-1 end) as nu_fac_cantidad,
								(case when det.ch_fac_tipodocumento!='20' THEN det.nu_fac_importeneto else det.nu_fac_importeneto*-1 end) as nu_fac_importeneto,
								(case when det.ch_fac_tipodocumento!='20' THEN det.nu_fac_valortotal else det.nu_fac_valortotal*-1 end) as nu_fac_valortotal
				 FROM
								fac_ta_factura_cabecera cab,
								fac_ta_factura_detalle det,
								int_articulos art,
								int_tabla_general tab,
								int_tabla_general tab2,
								inv_ta_almacenes alm ";
								
			
			$sql .= "WHERE
							cab.dt_fac_fecha>=to_date('" . pg_escape_string($desde) . "', 'dd/mm/yyyy')
							AND cab.dt_fac_fecha<=to_date('" . pg_escape_string($hasta) . "', 'dd/mm/yyyy')
							AND cab.ch_fac_credito!='S'
							AND det.ch_fac_tipodocumento=cab.ch_fac_tipodocumento
							AND det.ch_fac_seriedocumento=cab.ch_fac_seriedocumento
							AND det.ch_fac_numerodocumento=cab.ch_fac_numerodocumento
							AND det.cli_codigo=cab.cli_codigo
							AND art.art_codigo=det.art_codigo
							AND tab.tab_elemento=art.art_linea
							AND tab.tab_tabla='20'
							AND tab2.tab_elemento=art.art_tipo
							AND tab2.tab_tabla='21'
							AND alm.ch_almacen=cab.ch_almacen ";

			if (trim($almacen)!="TODOS"){
				$sql .= " AND cab.ch_almacen='$almacen' ";
			}			

			if (count($lineas) > 0) {
				if($lineas[0] != '0'){
					$sql .= " AND art.art_linea in (";
					for ($i = 0; $i < count($lineas); $i++) {
						if ($i > 0)
							$sql .= ",";
						$sql .= "'" . pg_escape_string($lineas[$i]) . "'";
					}
					$sql .= ") ";
				}
			}
			
			if (count($tipos) > 0) {
				if($tipos[0] != '0'){
					$sql .= " AND art.art_tipo in (";
					for ($i = 0; $i < count($tipos); $i++) {
						if ($i > 0)
							$sql .= ",";
						$sql .= "'" . pg_escape_string($tipos[$i]) . "'";
					}
					$sql .= ") ";
				}
			}
			
			if (count($articulos) > 0) {
				if($articulos[0] != '0'){
					if($arituclos[0] != '0'){ 
						$sql .= " AND art.art_codigo in (";
						for ($i = 0; $i < count($articulos); $i++) {
							if ($i > 0)
								$sql .= ",";
							$sql .= "'" . pg_escape_string($articulos[$i]) . "'";
						}
						$sql .= ") ";
					}
				}
			}
			
			if (count($proveedores) > 0) {
				if ($proveedores[0] != '0') {
					$sql .= " AND prov.pro_codigo in (";
					for ($i = 0; $i < count($proveedores); $i++) {
						if ($i > 0)
							$sql .= ",";
						$sql .= "'" . pg_escape_string($proveedores[$i]) . "'";
					}
					$sql .= ") ";
				}
			}
			
			$sql .= ");";

			// echo "<pre>";
			// echo $sql;
			// echo "</pre>";

			$sqlca->query($sql);
			
			// Paso 3: Obtener lista de los almacenes con los que contamos (para hacer las columnas en orden)
			$sql = "SELECT
							distinct ch_almacen,
							ch_nombre_almacen
					FROM
							tmp_ventas_especiales
					ORDER BY
							ch_almacen;";
			
			$sqlca->query($sql);
			
			$almacenes = Array();
			for ($i = 0; $i < $sqlca->numrows(); $i++)
			{
				$a = $sqlca->fetchRow();
				$almacenes[$a[0]] = $a[1];
			}
			
			// Paso 4: obtener la informacion, ordenada de acuerdo a lo que se ha pedido
			$sql = "SELECT
							ch_almacen,
							" . (($detallado=="S")?"to_char(dt_fac_fecha,'DD/MM/YYYY'),":"' '::character,") . "
							art_codigo,
							max(art_descripcion),
							max(art_linea_desc),
							max(art_tipo_desc),
							sum(nu_fac_cantidad) as nu_fac_cantidad,
							sum(nu_fac_importeneto) as nu_fac_importeneto,
							sum(nu_fac_valortotal) as nu_fac_valortotal
					FROM
							tmp_ventas_especiales
					GROUP BY
							ch_almacen,
							" . (($detallado=="S")?"dt_fac_fecha,":"") . "
							art_codigo,
							art_descripcion,
							art_linea_desc,
							art_tipo_desc,
							nu_fac_cantidad,
							nu_fac_importeneto,
							nu_fac_valortotal,
							art_linea,
							art_tipo
					ORDER BY
							2,
							art_linea,";
			
			if ($orden == "total")
				$sql .= "	sum(nu_fac_importeneto) ";
			else
				$sql .= "	art_codigo ";
			
			$sqlca->query($sql);
			$resultado = Array();
			$resultado['almacenes'] = $almacenes;
			
			// Paso 5: mandamos todos los datos al array, previamente preparando la informacion
			for ($i = 0; $i < $sqlca->numrows(); $i++)
			{
				$a = $sqlca->fetchRow();
				$ch_almacen		= $a[0];
				$dt_fac_fecha		= $a[1];
				$art_codigo		= $a[2];
				$art_descripcion	= $a[3];
				$art_linea_desc		= $a[4];
				$art_tipo_desc		= $a[5];
				$nu_fac_cantidad	= $a[6];
				$nu_fac_importeneto	= $a[7];
				$nu_fac_valortotal	= $a[8];
				
				if ($forma == "detallado"){

					$resultado['fechas'][$dt_fac_fecha]['lineas'][$art_linea_desc]['codigos'][$art_codigo]['descripcion']		= $art_descripcion;
					$resultado['fechas'][$dt_fac_fecha]['lineas'][$art_linea_desc]['codigos'][$art_codigo][$ch_almacen."_cantidad"]	+= $nu_fac_cantidad;
					$resultado['fechas'][$dt_fac_fecha]['lineas'][$art_linea_desc]['codigos'][$art_codigo][$ch_almacen."_neto"]	+= $nu_fac_importeneto;
					$resultado['fechas'][$dt_fac_fecha]['lineas'][$art_linea_desc]['codigos'][$art_codigo][$ch_almacen."_total"]	+= $nu_fac_valortotal;
					
					// Calcular totales x codigo
					$resultado['fechas'][$dt_fac_fecha]['lineas'][$art_linea_desc]['codigos'][$art_codigo]['total_cantidad']+= $nu_fac_cantidad;
					$resultado['fechas'][$dt_fac_fecha]['lineas'][$art_linea_desc]['codigos'][$art_codigo]['total_neto'] 	+= $nu_fac_importeneto;
					$resultado['fechas'][$dt_fac_fecha]['lineas'][$art_linea_desc]['codigos'][$art_codigo]['total_total'] 	+= $nu_fac_valortotal;
					
					// Calcular totales x linea
					$resultado['fechas'][$dt_fac_fecha]['lineas'][$art_linea_desc][$ch_almacen."_cantidad"]		+= $nu_fac_cantidad;
					$resultado['fechas'][$dt_fac_fecha]['lineas'][$art_linea_desc][$ch_almacen."_neto"]		+= $nu_fac_importeneto;
					$resultado['fechas'][$dt_fac_fecha]['lineas'][$art_linea_desc][$ch_almacen."_total"]		+= $nu_fac_valortotal;
					$resultado['fechas'][$dt_fac_fecha]['lineas'][$art_linea_desc]['total_cantidad']		+= $nu_fac_cantidad;
					$resultado['fechas'][$dt_fac_fecha]['lineas'][$art_linea_desc]['total_neto']			+= $nu_fac_importeneto;
					$resultado['fechas'][$dt_fac_fecha]['lineas'][$art_linea_desc]['total_total']			+= $nu_fac_valortotal;
				}
				
				// Calcular totales x dia
				$resultado['fechas'][$dt_fac_fecha][$ch_almacen."_cantidad"]	+= $nu_fac_cantidad;
				$resultado['fechas'][$dt_fac_fecha][$ch_almacen."_neto"]	+= $nu_fac_importeneto;
				$resultado['fechas'][$dt_fac_fecha][$ch_almacen."_total"]	+= $nu_fac_valortotal;
				$resultado['fechas'][$dt_fac_fecha]['total_cantidad']		+= $nu_fac_cantidad;
				$resultado['fechas'][$dt_fac_fecha]['total_neto']		+= $nu_fac_importeneto;
				$resultado['fechas'][$dt_fac_fecha]['total_total']		+= $nu_fac_valortotal;
				
				// Calcular total general
				$resultado[$ch_almacen."_cantidad"]	+= $nu_fac_cantidad;
				$resultado[$ch_almacen."_neto"]		+= $nu_fac_importeneto;
				$resultado[$ch_almacen."_total"]	+= $nu_fac_valortotal;
				$resultado['total_cantidad']		+= $nu_fac_cantidad;
				$resultado['total_neto']		+= $nu_fac_importeneto;
				$resultado['total_total']		+= $nu_fac_valortotal;
			}
			
			// Paso 6: Terminamos la transaccion
			$sql = "END;";
			$sqlca->query($sql);
			
			return $resultado;
		}
	}
