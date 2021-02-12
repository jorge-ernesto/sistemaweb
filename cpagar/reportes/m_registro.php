<?php

class RegistroModel extends Model
{
    function obtenerListaAlmacenes()
    {
	global $sqlca;
	
	$sql = "SELECT
		    ch_almacen,
		    ch_nombre_almacen
		FROM
		    inv_ta_almacenes
		WHERE
		    ch_clase_almacen='1'
		ORDER BY
		    ch_almacen
		;
		";
	if ($sqlca->query($sql) < 0) return false;
	
	$result = Array();
	
	for ($i = 0; $i < $sqlca->numrows(); $i++) {
	    $a = $sqlca->fetchRow();
	    
	    $result[$a[0]] = $a[0] . " - " . $a[1];
	}
	
	return $result;
    }
    
    function obtenerListaDocumentos()
    {
	global $sqlca;
	
	$sql = "SELECT
		    substr(tab_elemento, 5, 2),
		    tab_descripcion
		FROM
		    int_tabla_general
		WHERE
			tab_tabla='08'
		    AND tab_elemento!='000000'
		ORDER BY
		    tab_elemento
		;
		";
	if ($sqlca->query($sql) < 0) return false;
	
	$result = Array();
	
	for ($i = 0; $i < $sqlca->numrows(); $i++) {
	    $a = $sqlca->fetchRow();
	    $result[$a[0]] = $a[0] . " - " . $a[1];
	}
	
	return $result;
    }
    
    function buscar($params)
    {
	global $sqlca;

	$almacenes = RegistroModel::obtenerListaAlmacenes();
	$documentos = RegistroModel::obtenerListaDocumentos();
	$rubros = RegistroModel::obtenerListaRubros();

	$sql = "SELECT
		    pro_cab_fecharegistro,
		    pro_cab_fechaemision,
		    pro_cab_almacen,
		    pro_cab_tipdocumento,
		    trim(pro_cab_seriedocumento),
		    trim(pro_cab_numdocumento),
		    trim(pro_codigo),
		    pro_cab_moneda,
		    pro_cab_imptotal,
		    pro_cab_tcambio,
		    pro_cab_impto1,
		    (pro_cab_imptotal-pro_cab_impto1) as pro_cab_vv,
		    trim(pro_cab_rubrodoc),
		    pro_cab_fechavencimiento,
		    pro_cab_impinafecto
		FROM
		    cpag_ta_cabecera
		WHERE
			pro_cab_fecharegistro>=to_date('" . pg_escape_string($params['desde']) . "', 'DD/MM/YYYY')
		    AND pro_cab_fecharegistro<=to_date('" . pg_escape_string($params['hasta']) . "', 'DD/MM/YYYY')
		    ";
	if ($params['proveedor']!='') {
	    $sql .= "AND pro_codigo like '%" . pg_escape_string($params['proveedor']) . "%'
		    ";
	}
	
	if ($params['tipo']!='TODOS') {
	    $sql .= "AND pro_cab_tipdocumento='" . pg_escape_string($params['tipo']) . "'
		    ";
	}
	
	if ($params['numdoc']!='') {
	    $sql .= "AND pro_cab_numdocumento like '%" . pg_escape_string($params['numdoc']) . "%'
		    ";
	}
	
	if ($params['almacen']!='TODOS') {
	    $sql .= "AND pro_cab_almacen='" . pg_escape_string($params['almacen']) . "'
		";
	}

	$sql .= "ORDER BY
		    pro_cab_almacen,
		    pro_cab_fecharegistro
		 ;";

	if ($sqlca->query($sql) < 0) return false;

	$result = Array();
	
	for ($i = 0; $i < $sqlca->numrows(); $i++) {
	    $a = $sqlca->fetchRow();

	    $pro_cab_fecharegistro = $a[0];
	    $pro_cab_fechaemision = $a[1];
	    $pro_cab_almacen = $almacenes[$a[2]];
	    $pro_cab_tipdocumento = $documentos[$a[3]];
	    $pro_cab_seriedocumento = $a[4];
	    $pro_cab_numdocumento = $a[5];
	    $pro_codigo = $a[6] . " - " . RegistroModel::obtieneNombreProveedor($a[6]);
	    $pro_cab_moneda = ($a[7]=="01"?"Soles":"Dolares");
	    $pro_cab_imptotal = $a[8];
	    $pro_cab_tcambio = $a[9];
	    $pro_cab_impto1 = $a[10];
	    $pro_cab_vv = $a[11];
	    $pro_cab_rubrodoc = $rubros[$a[12]];
	    $pro_cab_fechavencimiento = $a[13];
	    $pro_cab_impinafecto = $a[14];

	    $result['almacenes'][$pro_cab_almacen]['fechas'][$pro_cab_fecharegistro]['documentos'][$pro_codigo.$pro_cab_seriedocumento.$pro_cab_numdocumento]['fecha_registro'] = $pro_cab_fecharegistro;
	    $result['almacenes'][$pro_cab_almacen]['fechas'][$pro_cab_fecharegistro]['documentos'][$pro_codigo.$pro_cab_seriedocumento.$pro_cab_numdocumento]['fecha_emision'] = $pro_cab_fechaemision;
	    $result['almacenes'][$pro_cab_almacen]['fechas'][$pro_cab_fecharegistro]['documentos'][$pro_codigo.$pro_cab_seriedocumento.$pro_cab_numdocumento]['almacen'] = $pro_cab_almacen;
	    $result['almacenes'][$pro_cab_almacen]['fechas'][$pro_cab_fecharegistro]['documentos'][$pro_codigo.$pro_cab_seriedocumento.$pro_cab_numdocumento]['tip_documento'] = $pro_cab_tipdocumento;
	    $result['almacenes'][$pro_cab_almacen]['fechas'][$pro_cab_fecharegistro]['documentos'][$pro_codigo.$pro_cab_seriedocumento.$pro_cab_numdocumento]['serie_documento'] = $pro_cab_seriedocumento;
	    $result['almacenes'][$pro_cab_almacen]['fechas'][$pro_cab_fecharegistro]['documentos'][$pro_codigo.$pro_cab_seriedocumento.$pro_cab_numdocumento]['num_documento'] = $pro_cab_numdocumento;
	    $result['almacenes'][$pro_cab_almacen]['fechas'][$pro_cab_fecharegistro]['documentos'][$pro_codigo.$pro_cab_seriedocumento.$pro_cab_numdocumento]['pro_codigo'] = $pro_codigo;
	    $result['almacenes'][$pro_cab_almacen]['fechas'][$pro_cab_fecharegistro]['documentos'][$pro_codigo.$pro_cab_seriedocumento.$pro_cab_numdocumento]['moneda'] = $pro_cab_moneda;
	    $result['almacenes'][$pro_cab_almacen]['fechas'][$pro_cab_fecharegistro]['documentos'][$pro_codigo.$pro_cab_seriedocumento.$pro_cab_numdocumento]['total'] = $pro_cab_imptotal;
	    $result['almacenes'][$pro_cab_almacen]['fechas'][$pro_cab_fecharegistro]['documentos'][$pro_codigo.$pro_cab_seriedocumento.$pro_cab_numdocumento]['vv'] = $pro_cab_vv;
	    $result['almacenes'][$pro_cab_almacen]['fechas'][$pro_cab_fecharegistro]['documentos'][$pro_codigo.$pro_cab_seriedocumento.$pro_cab_numdocumento]['igv'] = $pro_cab_impto1;
	    $result['almacenes'][$pro_cab_almacen]['fechas'][$pro_cab_fecharegistro]['documentos'][$pro_codigo.$pro_cab_seriedocumento.$pro_cab_numdocumento]['tc'] = $pro_cab_tcambio;
	    $result['almacenes'][$pro_cab_almacen]['fechas'][$pro_cab_fecharegistro]['documentos'][$pro_codigo.$pro_cab_seriedocumento.$pro_cab_numdocumento]['rubro'] = $pro_cab_rubrodoc;
	    $result['almacenes'][$pro_cab_almacen]['fechas'][$pro_cab_fecharegistro]['documentos'][$pro_codigo.$pro_cab_seriedocumento.$pro_cab_numdocumento]['vencimiento'] = $pro_cab_fechavencimiento;
	    $result['almacenes'][$pro_cab_almacen]['fechas'][$pro_cab_fecharegistro]['documentos'][$pro_codigo.$pro_cab_seriedocumento.$pro_cab_numdocumento]['inafecto'] = $pro_cab_inafecto;
	}
	return $result;
    }
    
    function obtieneNombreProveedor($codigo)
    {
	global $sqlca;
	
	$sql = "SELECT
		    trim(pro_razsocial)
		FROM
		    int_proveedores
		WHERE
		    pro_codigo='" . pg_escape_string($codigo) . "'
		;
		";
	if ($sqlca->query($sql, "_prov") < 0) return false;
	
	$a = $sqlca->fetchRow("_prov");
	
	return $a[0];
    }

    function obtenerListaRubros()
    {
	global $sqlca;
	
	$sql = "SELECT
		    trim(ch_codigo_rubro),
		    ch_descripcion
		FROM
		    cpag_ta_rubros
		ORDER BY
		    ch_codigo_rubro
		;
		";

	if ($sqlca->query($sql) < 0) return false;
	
	$result = Array();
	
	for ($i = 0; $i < $sqlca->numrows(); $i++) {
	    $a = $sqlca->fetchRow();
	    
	    $result[$a[0]] = $a[0] . " - " . $a[1];
	}
	
	return $result;
    }
}

?>