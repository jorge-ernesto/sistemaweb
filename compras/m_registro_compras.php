<?php

class RegistroComprasModel extends Model {

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
	
	function Paginacion($pp, $pagina, $fecha, $fecha2, $estacion, $proveedor, $documento){

		global $sqlca;

		$query = "SELECT
				to_char(c.pro_cab_fecharegistro, 'DD/MM/YYYY') fregistro,
				to_char(c.pro_cab_fechaemision, 'DD/MM/YYYY') femision,
				a.ch_sigla_almacen cc,
				gen.tab_desc_breve||' '||c.pro_cab_seriedocumento||' - '||c.pro_cab_numdocumento documento, 
				p.pro_codigo||' '||p.pro_rsocialbreve proveedor,
				CASE WHEN d.pro_det_moneda = '01' THEN 'S/.' ELSE '$' END moneda,
				c.pro_cab_impto1 impuesto,
				c.pro_cab_imptotal total,
				c.pro_cab_impsaldo saldo,
				rubro.tab_descripcion rubro,
				to_char(c.pro_cab_fechavencimiento, 'DD/MM/YYYY') fvencimiento
			FROM
				cpag_ta_cabecera c
				INNER JOIN cpag_ta_detalle d ON (c.pro_cab_tipdocumento = d.pro_cab_tipdocumento AND c.pro_cab_seriedocumento = d.pro_cab_seriedocumento AND c.pro_cab_numdocumento = d.pro_cab_numdocumento AND c.pro_codigo = d.pro_codigo)
				LEFT JOIN int_proveedores p ON (c.pro_codigo = p.pro_codigo)
				LEFT JOIN inv_ta_almacenes a ON(c.pro_cab_almacen = a.ch_almacen)
				LEFT JOIN int_tabla_general as gen ON(c.pro_cab_tipdocumento = substring(TRIM(tab_elemento) for 2 from length(TRIM(tab_elemento))-1) and tab_tabla ='08')
				LEFT JOIN int_tabla_general as rubro ON(c.pro_cab_rubrodoc = rubro.tab_elemento and (rubro.tab_tabla='RCPG')) ";
			
		if($fecha != ''){
		$query .= "
			WHERE
				c.pro_cab_fecharegistro BETWEEN to_date('$fecha','DD/MM/YYYY') AND to_date('$fecha2','DD/MM/YYYY')
				AND c.pro_cab_almacen = '$estacion' ";
		}

		if($proveedor != '')
		$query .= "	AND c.pro_codigo = '$proveedor' ";
		
		if($documento != '')
		$query .= "	AND c.pro_cab_numdocumento = '$documento' ";

			
		$query .= "
			ORDER BY 
				femision";

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
			$resultado[$i]['fregistro']	= $a[0];
			$resultado[$i]['femision']	= $a[1];
			$resultado[$i]['cc']		= $a[2];
			$resultado[$i]['documento'] 	= $a[3];
			$resultado[$i]['proveedor'] 	= $a[4];
			$resultado[$i]['moneda'] 	= $a[5];
			$resultado[$i]['impuesto'] 	= $a[6];
			$resultado[$i]['total'] 	= $a[7];
			$resultado[$i]['saldo'] 	= $a[8];
			$resultado[$i]['rubro'] 	= $a[9];
			$resultado[$i]['fvencimiento']	= $a[10];
		}
		
		$query = "COMMIT";
		$sqlca->query($query);

		$listado['datos']      = $resultado;        
		$listado['paginacion'] = $listado2;

		return $listado;
  	}

	function ComprasDevolucion($proveedor){
		global $sqlca;

		$query="
				SELECT
					cab.tran_codigo AS tipo,
					to_char(cab.mov_fecha,'dd/mm/yyyy') AS fecha,
					cab.mov_numero movimiento,
					cab.com_num_compra compra,
					art.art_codigo||' - '||art.art_descripcion producto,
					cab.mov_cantidad cantidad,
					cab.mov_costounitario costo,
					cab.mov_costototal total
				FROM
					inv_ta_compras_devoluciones cab
					LEFT JOIN int_proveedores pro ON(cab.mov_entidad = pro.pro_codigo)
					LEFT JOIN int_articulos art ON(cab.art_codigo = art.art_codigo)
				WHERE
					cab.mov_entidad = '$proveedor'
					AND (cab.cpag_tipo_pago IS NULL OR cab.cpag_serie_pago IS NULL OR cab.cpag_num_pago IS NULL)
				ORDER BY
					fecha DESC;
			";

		echo $query;

		if ($sqlca->query($query) < 0)
			return false;
	    
		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$resultado[$i]['tipo']		= $a[0];
			$resultado[$i]['fecha']		= $a[1];
			$resultado[$i]['movimiento']	= $a[2];
			$resultado[$i]['compra'] 	= $a[3];
			$resultado[$i]['producto'] 	= $a[4];
			$resultado[$i]['cantidad'] 	= $a[5];
			$resultado[$i]['costo'] 	= $a[6];
			$resultado[$i]['total'] 	= $a[7];
		}

		return $resultado;

	}

	function AgregarComprasCabecera($estacion,$femision,$proveedor,$rubro,$tipo,$serie,$documento,$dvec,$fvencimiento,$tc,$moneda,$base,$impuesto,$total,$percepcion){
		global $sqlca;

		$query="
			INSERT INTO
				cpag_ta_cabecera(
							pro_cab_tipdocumento,
							pro_cab_seriedocumento,
							pro_cab_numdocumento,
							pro_codigo,
							pro_cab_fechaemision,
							pro_cab_fecharegistro,
							pro_cab_fechavencimiento, 
							pro_cab_dias_vencimiento, 
							pro_cab_tipcontable,
							plc_codigo,
							pro_cab_moneda,
							pro_cab_tcambio,
							pro_cab_imptotal,
							pro_cab_impsaldo,
							pro_cab_fechasaldo,
							pro_cab_grupoc,
							pro_cab_comprobantec,
							pro_cab_almacen,
							pro_cab_impafecto,
							pro_cab_impto1,
							pro_cab_rubrodoc,
							regc_sunat_percepcion
				) VALUES (
							'$tipo',
							'$serie',
							'$documento',
							'$proveedor',
							to_date('$femision','dd/mm/yyyy'), 
							to_date('$femision','dd/mm/yyyy'),
							to_date('$fvencimiento','dd/mm/yyyy'), 
							'$dvec',
							UTIL_FN_TIPO_ACCION_CONTABLE('CP','$tipo'),
							'42101',
							'$moneda', 
							$tc,
							$total, 
							$total, 
							now(),
							null, 
							null, 
							'$estacion',
							$base,
							$impuesto, 
							'$rubro',
							$percepcion
			)
		;";

		echo $query;
	
		$querydet = "
			INSERT INTO
				cpag_ta_detalle "."( ".
							"pro_cab_tipdocumento, ".
							"pro_cab_seriedocumento, ".
							"pro_cab_numdocumento, ".
							"pro_codigo, ".
							"pro_det_identidad, ".
							"pro_det_tipmovimiento, ".
							"pro_det_fechamovimiento, ".
							"pro_det_moneda, ".
							"pro_det_tcambio, ".
							"pro_det_impmovimiento, ".
							"pro_det_grupoc, ".
							"pro_det_almacen, ".
							"pro_det_glosa ".
		              ")".
		   "VALUES ".
		              "( ".
							"'$tipo', ". 
							"'$serie', ".
							"'$documento', ".
							"'$proveedor', ".
							"'001', ".
							"'1', ".
							"to_date('$femsision','dd/mm/yyyy'), ".
							"'$moneda', ".
							"$tc, ".
							"$total, ".
							"null, ".
							"'$estacion', ".
							"'$glosa')";

		echo $querydet;

		/*if(($sqlca->query($query) <= 0) || ($sqlca->query($querydet) <= 0))
			return false;
		else
			return true;*/

	}

	function ActualizarCompra($proveedor, $tipo, $serie, $documento, $ip){
		global $sqlca;

		$update="
			UPDATE
				inv_ta_compras_devoluciones
			SET
				cpag_tipo_pago 		= '$tipo',
				cpag_serie_pago 	= '$serie',
				cpag_num_pago 		= '$documento',
				mov_fecha_actualizacion = now(),
				ip_addr 		= '$ip'
			WHERE
				cab.mov_entidad = '$proveedor'
			";

		if($sqlca->query($update))
			return false;
		else
			return true;

	}
	
	function eliminarRegistro($ncuenta){
		global $sqlca;

		$del = "DELETE FROM c_bank_account WHERE c_bank_account_id = '$ncuenta';";

		//echo $del;

		$sqlca->query($del);

		return 'OK';

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

	function Rubros(){
		global $sqlca;

		$rubro = "SELECT
				trim(tab_elemento),
				tab_descripcion
			FROM
				int_tabla_general
			WHERE
				tab_tabla='RCPG'
				AND trim(tab_elemento)!='000000' ";

		if($sqlca->query($rubro) < 0)
			return false;

		$resultado = array();
	
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    	$a = $sqlca->fetchRow();
		    	$resultado[$a[0]] = $a[1];
		}
		
		return $resultado;

	}

	function Documentos(){
		global $sqlca;

		$documentos = "
				SELECT
					trim(tab_elemento),
					tab_descripcion
				FROM
					int_tabla_general
				WHERE
					tab_tabla = '08'
					AND tab_elemento<>'000000'
				ORDER BY
					tab_descripcion; 
				";

		if($sqlca->query($documentos) < 0)
			return false;

		$resultado = array();
	
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    	$a = $sqlca->fetchRow();
		    	$resultado[$a[0]] = $a[1];
		}
		
		return $resultado;
	
	}

	function ProveedorCBArray($condicion='') {
    		global $sqlca;
    		
    		$cbArray = array();
    		$query = "SELECT pro_codigo, pro_razsocial, pro_razsocial FROM int_proveedores ".
    		$query .= ($condicion!=''?' WHERE '.$condicion:'').' ORDER BY 2';
    		
    		if ($sqlca->query($query)<=0)
      			return $cbArray;
      			
    		while($result = $sqlca->fetchRow()){
      			$cbArray[trim($result["pro_codigo"])] = $result["pro_codigo"].' '.$result["pro_razsocial"];
    		}

    		ksort($cbArray);
    		
    		return $cbArray;

  	}

}
?>

