<?php

class RegistroOficialModel extends Model
{
    function obtenerRubros()
    {
	global $sqlca;

	$sql = "SELECT
		    trim(ch_codigo_rubro) as codigo_rubro,
		    ch_descripcion
		FROM
		    cpag_ta_rubros
		ORDER BY
		    ch_codigo_rubro
		;
		";
	if ($sqlca->query($sql) < 0) {
	    return false;
	}
	
	$result = Array();

	for ($i = 0; $i < $sqlca->numrows(); $i++) {
	    $a = $sqlca->fetchRow();
	    
	    $result[$a[0]] = $a[0] . " - " . $a[1];
	}
	
	return $result;
    }
    
    function reporte($params)
    {
	global $sqlca;
	
	list($desde_dia,$desde_mes,$desde_ano) = sscanf($params['desde'], "%2s/%2s/%4s");
	list($hasta_dia,$hasta_mes,$hasta_ano) = sscanf($params['hasta'], "%2s/%2s/%4s");

	$sql = "SELECT
		    cab.pro_cab_fechaemision as emision,
		    trim(tab.tab_car_03) as td,
		    cab.pro_cab_seriedocumento as serie,
		    cab.pro_cab_numdocumento as numero,
		    pro.pro_ruc as ruc,
		    trim(pro.pro_codigo)||'-'||pro.pro_razsocial as rsocial,
		    cab.pro_cab_impinafecto as inafecto,
		    cab.pro_cab_impafecto as afecto,
		    cab.pro_cab_tipimpto1 as timpuesto1,
		    cab.pro_cab_tipimpto2 as timpuesto2,
		    cab.pro_cab_tipimpto3 as timpuesto3,
		    cab.pro_cab_impto1 as impuesto1,
		    cab.pro_cab_impto2 as impuesto2,
		    cab.pro_cab_impto3 as impuesto3,
		    trim(cab.pro_cab_rubrodoc)||'-'||rub.ch_descripcion as rubro,
		    cab.pro_cab_imptotal as total,
		    trim(cab.pro_cab_numreg) as numreg,
		    cab.pro_cab_almacen||'-'||trim(alm.ch_nombre_almacen) as almacen,
		    cab.pro_cab_tcambio as tc,
		    cab.pro_cab_moneda as moneda,
		    rub.ch_percepcion_tipo,
		    rub.ch_percepcion_porcentaje
		FROM
		    cpag_ta_cabecera cab,
		    int_proveedores pro,
		    int_tabla_general tab,
		    inv_ta_almacenes alm,
		    cpag_ta_rubros rub
		WHERE
			cab.pro_cab_fechaemision BETWEEN '" . pg_escape_string($desde_ano."-".$desde_mes."-".$desde_dia) . "' AND '" . pg_escape_string($hasta_ano."-".$hasta_mes."-".$hasta_dia) . "'
		    ";
	if ($params['tipo'] != 'TODOS') {
	    $sql .= "AND cab.pro_cab_tipdocumento='" . pg_escape_string($params['tipo']) . "' ";
	}
	if ($params['proveedor'] != '') {
	    $sql .= "AND cab.pro_codigo='" . pg_escape_string($params['proveedor']) . "' ";
	}
	if ($params['almacen'] != 'TODOS') {
	    $sql .= "AND cab.pro_cab_almacen='" . pg_escape_string($params['almacen']) . "' ";
	}
	if ($params['rubro'] != 'TODOS') {
	    $sql .= "AND cab.pro_cab_rubrodoc='" . pg_escape_string($params['rubro']) . "' ";
	}
	$sql .= "
		    AND pro.pro_codigo=cab.pro_codigo
		    AND tab.tab_tabla='08'
		    AND tab.tab_elemento=lpad(cab.pro_cab_tipdocumento, 6, '0')
		    AND alm.ch_almacen=cab.pro_cab_almacen
		    AND rub.ch_codigo_rubro=cab.pro_cab_rubrodoc
		ORDER BY
		    cab.pro_cab_rubrodoc,
		    cab.pro_cab_almacen,
		    cab.pro_cab_fechaemision
		;
		";												
	if ($sqlca->query($sql) < 0) {
	    return false;
	}
	
	$result = array();

	for ($i = 0; $i < $sqlca->numrows(); $i++) {
	    $a = $sqlca->fetchRow();
	    
	    $emision = $a['emision'];
	    $td = $a['td'];
	    $serie = $a['serie'];
	    $numero = $a['numero'];
	    $ruc = $a['ruc'];
	    $rsocial = $a['rsocial'];
	    $inafecto = $a['inafecto'];
	    $afecto = $a['afecto'];
	    $timpuesto1 = $a['timpuesto1'];
	    $timpuesto2 = $a['timpuesto2'];
	    $timpuesto3 = $a['timpuesto3'];
	    $impuesto1 = $a['impuesto1'];
	    $impuesto2 = $a['impuesto2'];
	    $impuesto3 = $a['impuesto3'];
	    $rubro = $a['rubro'];
	    $total = $a['total'];
	    $numreg = $a['numreg'];
	    $almacen = $a['almacen'];
	    $tc = $a['tc'];
	    $moneda = $a['moneda'];
	    $ch_percepcion_tipo = $a['ch_percepcion_tipo'];
	    $ch_percepcion_porcentaje = $a['ch_percepcion_porcentaje'];
	    
	    $key = $td.$serie.$numero.$rsocial;

	    $result['rubros'][$rubro]['estaciones'][$almacen]['documentos'][$key]['emision'] = $emision;
	    $result['rubros'][$rubro]['estaciones'][$almacen]['documentos'][$key]['td'] = $td;
	    $result['rubros'][$rubro]['estaciones'][$almacen]['documentos'][$key]['serie'] = $serie;
	    $result['rubros'][$rubro]['estaciones'][$almacen]['documentos'][$key]['numero'] = $numero;
	    $result['rubros'][$rubro]['estaciones'][$almacen]['documentos'][$key]['ruc'] = $ruc;
	    $result['rubros'][$rubro]['estaciones'][$almacen]['documentos'][$key]['proveedor'] = $rsocial;
	    
	    /* Para el caso de facturas en dolares, guardar el total y convertir todo a soles */
	    if ($moneda == '02') {
		$result['rubros'][$rubro]['estaciones'][$almacen]['documentos'][$key]['total_usd'] = $total;
		$result['rubros'][$rubro]['estaciones'][$almacen]['totales']['total_usd'] += $total;
		$result['rubros'][$rubro]['totales']['total_usd'] += $total;
		$result['totales']['total_usd'] += $total;
		$total = round($total * $tc, 2);
		$inafecto = round($inafecto * $tc, 2);
		$afecto = round($afecto * $tc, 2);
		$impuesto1 = round($impuesto1 * $tc, 2);
		$impuesto2 = round($impuesto2 * $tc, 2);
		$impuesto3 = round($impuesto3 * $tc, 2);
	    }
	    
	    $base_gravado = $afecto; //round($total - ($impuesto1+$impuesto2+$impuesto3+$inafecto), 2);
	    $base_compartido = 0;	/* FALTA PONER */
	    $base_imponible = 0;	/* FALTA PONER. Deberia definirse como: (Base Dest. Comp + Base Dest. Grav) */
	    $igv_compartido = 0;	/* FALTA PONER */
	    $renta_4ta = 0;		/* FALTA PONER */
	    $solidaridad = 0;		/* FALTA PONER */

	    /* Calculo de percepcion */
	    if ($ch_percepcion_tipo != 0) {
		$subtotal = $base_gravado + $base_compartido;
		$percepcion = round(($subtotal*($ch_percepcion_porcentaje/100))-$subtotal, 2);
	    }
	    
	    $result['rubros'][$rubro]['estaciones'][$almacen]['documentos'][$key]['inafecto'] = $inafecto;
	    $result['rubros'][$rubro]['estaciones'][$almacen]['documentos'][$key]['base_gravado'] = $base_gravado;
	    $result['rubros'][$rubro]['estaciones'][$almacen]['documentos'][$key]['base_compartido'] = $base_compartido;
	    $result['rubros'][$rubro]['estaciones'][$almacen]['documentos'][$key]['base_imponible'] = $base_imponible;
	    
	    $result['rubros'][$rubro]['estaciones'][$almacen]['documentos'][$key]['igv_gravado'] = $impuesto1;
	    $result['rubros'][$rubro]['estaciones'][$almacen]['documentos'][$key]['igv_compartido'] = $igv_compartido;
	    
	    $result['rubros'][$rubro]['estaciones'][$almacen]['documentos'][$key]['percepcion'] = $percepcion;
	    $result['rubros'][$rubro]['estaciones'][$almacen]['documentos'][$key]['renta4ta'] = $renta_4ta;
	    $result['rubros'][$rubro]['estaciones'][$almacen]['documentos'][$key]['solidaridad'] = $solidaridad;
	    $result['rubros'][$rubro]['estaciones'][$almacen]['documentos'][$key]['total'] = $total;
	    
	    $result['rubros'][$rubro]['estaciones'][$almacen]['documentos'][$key]['registro'] = $numreg;
	    
	    /* Calculo subtotal sucursal */
	    $result['rubros'][$rubro]['estaciones'][$almacen]['totales']['inafecto'] += $inafecto;
	    $result['rubros'][$rubro]['estaciones'][$almacen]['totales']['base_gravado'] += $base_gravado;
	    $result['rubros'][$rubro]['estaciones'][$almacen]['totales']['base_compartido'] += $base_compartido;
	    $result['rubros'][$rubro]['estaciones'][$almacen]['totales']['base_imponible'] += $base_imponible;
	    $result['rubros'][$rubro]['estaciones'][$almacen]['totales']['igv_gravado'] += $impuesto1;
	    $result['rubros'][$rubro]['estaciones'][$almacen]['totales']['igv_compartido'] += $igv_compartido;
	    $result['rubros'][$rubro]['estaciones'][$almacen]['totales']['percepcion'] += $percepcion;
	    $result['rubros'][$rubro]['estaciones'][$almacen]['totales']['renta4ta'] += $renta4ta;
	    $result['rubros'][$rubro]['estaciones'][$almacen]['totales']['solidaridad'] += $solidaridad;
	    $result['rubros'][$rubro]['estaciones'][$almacen]['totales']['total'] += $total;

	    /* Calcula subtotal por rubro */
	    $result['rubros'][$rubro]['totales']['inafecto'] += $inafecto;
	    $result['rubros'][$rubro]['totales']['base_gravado'] += $base_gravado;
	    $result['rubros'][$rubro]['totales']['base_compartido'] += $base_compartido;
	    $result['rubros'][$rubro]['totales']['base_imponible'] += $base_imponible;
	    $result['rubros'][$rubro]['totales']['igv_gravado'] += $impuesto1;
	    $result['rubros'][$rubro]['totales']['igv_compartido'] += $igv_compartido;
	    $result['rubros'][$rubro]['totales']['percepcion'] += $percepcion;
	    $result['rubros'][$rubro]['totales']['renta4ta'] += $renta4ta;
	    $result['rubros'][$rubro]['totales']['solidaridad'] += $solidaridad;
	    $result['rubros'][$rubro]['totales']['total'] += $total;
	    
	    /* Calcula total general */
	    $result['totales']['inafecto'] += $inafecto;
	    $result['totales']['base_gravado'] += $base_gravado;
	    $result['totales']['base_compartido'] += $base_compartido;
	    $result['totales']['base_imponible'] += $base_imponible;
	    $result['totales']['igv_gravado'] += $impuesto1;
	    $result['totales']['igv_compartido'] += $igv_compartido;
	    $result['totales']['percepcion'] += $percepcion;
	    $result['totales']['renta4ta'] += $renta4ta;
	    $result['totales']['solidaridad'] += $solidaridad;
	    $result['totales']['total'] += $total;
	}
	
	return $result;
    }
}

?>