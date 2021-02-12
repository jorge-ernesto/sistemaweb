<?php

include_once("/sistemaweb/include/config.php");
include_once("/sistemaweb/include/dbsqlca.php");

//$r = ob_start(true);
ob_start();

function SQLImplode($sql) {
    global $sqlca;

    if ($sqlca->query($sql) < 0)
        return FALSE;

    for ($i = 0; $i < $sqlca->numrows(); $i++) {
        $rR = $sqlca->fetchRow();
        foreach ($rR as $k => $v) {
            if (is_numeric($k))
                echo (($k == 0) ? "" : "|") . $v;
        }
        echo "\n";
    }
}

function SQLImplode2($sql) {
    global $sqlca;

    if ($sqlca->query($sql) < 0)
        return FALSE;

    for ($i = 0; $i < $sqlca->numrows(); $i++) {
        $rR = $sqlca->fetchRow();
        foreach ($rR as $k => $v) {
            if (is_numeric($k))
                echo (($k == 0) ? "" : "��") . $v;
        }
        echo "\n";
    }
}


/*
'aisgnar' el rango de fechas $_REQUEST['from'] y $_REQUEST['to'], $PosTransTable y otros
*/
function argRangedCheck() {
    global $CxBegin, $CxEnd, $PosTransTable, $BeginDate, $EndDate;
    global $BeginYear, $BeginMonth, $EndYear, $EndMonth;
    if (!isset($_REQUEST['from']) || !isset($_REQUEST['to']))
        die("ERR_INVALID_ARGS_RANGED");

    $CxBegin = $_REQUEST['from'];
    $CxEnd = $_REQUEST['to'];

    if (strlen($CxBegin) != 8 || strlen($CxEnd) != 8 || !is_numeric($CxBegin) || !is_numeric($CxEnd))
        die("ERR_INVALID_DATE");

    if (substr($CxBegin, 0, 6) != substr($CxEnd, 0, 6))
        die("ERR_DATE_DIFFERENT_MONTHS");

    $PosTransTable = "pos_trans" . substr($CxBegin, 0, 6);
    $BeginDate = substr($CxBegin, 0, 4) . "-" . substr($CxBegin, 4, 2) . "-" . substr($CxBegin, 6, 2);
    $EndDate   = substr($CxEnd, 0, 4) . "-" . substr($CxEnd, 4, 2) . "-" . substr($CxEnd, 6, 2);

    $BeginYear = substr($CxBegin, 0, 4); $BeginMonth = substr($CxBegin, 4, 2);
    $EndYear = substr($CxEnd, 0, 4); $EndMonth = substr($CxEnd, 4, 2);
}

function argKeyCheck() {
    global $CxSearchKey, $Cxdoctype;

    if (!isset($_REQUEST['sk']))
        die("ERR_INVALID_ARGS_KEY");

    $CxSearchKey = $_REQUEST['sk'];
    if ($CxSearchKey == "" || strlen($CxSearchKey) < 5)
        die("ERR_INVALID_KEY");
}

function argFE(){
	global $doctype, $documentserial;
    $doctype = NULL;
    if(isset($_REQUEST['doctype']))
	    $doctype = $_REQUEST['doctype'];
    $documentserial = NULL;
    if(isset($_REQUEST['documentserial']))
	    $documentserial = $_REQUEST['documentserial'];
}

global $db_host, $db_user, $db_password, $db_name;
$sqlca = new pgsqlDB($db_host, $db_user, $db_password, $db_name);

if (!isset($_REQUEST['mod']))
    die("ERR_INVALID_ARGS");

$CxModule = $_REQUEST['mod'];

switch ($CxModule) {

	case "IH": // NAME TABLE: c_invoiceheader CABECERA

		argRangedCheck();
$sql = "
SELECT
FIRST(a.ch_sucursal),
FIRST(a.ch_almacen),
FIRST(t.dia)::date,
FIRST(t.fecha),
(CASE WHEN FIRST(t.usr) != '' THEN SUBSTR(TRIM(t.usr), 6) else t.trans::TEXT END),
(CASE WHEN FIRST(t.usr) != '' THEN SUBSTR(TRIM(t.usr), 0, 5) else FIRST(cfp.nu_posz_z_serie) END),
FIRST(t.td),
1,
(CASE WHEN FIRST(t.ruc) IS NULL OR FIRST(t.ruc) = '' THEN 'GENERIC' ELSE FIRST(t.ruc) END),
(CASE WHEN FIRST(t.fpago) = '1' THEN '0' ELSE FIRST(t.at) END),
FIRST(split_part(t.text1, '|', 1)),
1,
FIRST(t.usr),
(CASE
	WHEN FIRST(t.tm) = 'V' AND FIRST(t.td) = 'F' THEN '10'
	WHEN FIRST(t.tm) = 'V' AND FIRST(t.td) = 'B' THEN '35'
	WHEN FIRST(t.tm) = 'D' OR FIRST(t.tm) = 'A' THEN '20'
END) AS _type,
t.placa,
t.turno,
(CASE
	WHEN FIRST(t.tm) = 'D' OR FIRST(t.tm) = 'A' THEN
	(SELECT
		(CASE
			WHEN FIRST(t0.tm) IS NOT NULL OR FIRST(t0.td) IS NOT NULL THEN
				FIRST(a0.ch_sucursal) || '-' || FIRST(a0.ch_almacen) || '-' ||
				(CASE
					WHEN FIRST(t0.tm) = 'V' AND FIRST(t0.td) = 'F' THEN '10'
					WHEN FIRST(t0.tm) = 'V' AND FIRST(t0.td) = 'B' THEN '35'
					WHEN FIRST(t0.tm) = 'D' OR FIRST(t0.tm) = 'A' THEN '20'
				END) || '-' || FIRST(t0.td) || '-' || FIRST(t0.usr)

				ELSE
				''
			END)

				FROM $PosTransTable t0 JOIN inv_ta_almacenes AS a0 ON (t0.es = a0.ch_almacen) WHERE t0.trans = FIRST(t.rendi_gln)::INTEGER
	)
END) as ref,
FIRST(t.rendi_gln)::INTEGER as rendi_gln
FROM
$PosTransTable AS t
JOIN inv_ta_almacenes AS a ON (t.es = a.ch_almacen)
LEFT JOIN pos_z_cierres AS cfp ON(t.caja = cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::DATE AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
WHERE
t.dia BETWEEN '$BeginDate' AND '$EndDate'
AND t.td IN ('B','F')
GROUP BY
t.trans,
t.caja,
t.fecha,
t.usr,
t.placa,
t.turno
ORDER BY
t.fecha;
";

		SQLImplode($sql);

		// Facturas de Venta Manuales

		$sql = "
		SELECT
			a.ch_sucursal,
			a.ch_almacen,
			h.dt_fac_fecha,
			h.dt_fac_fecha::timestamp,
			h.ch_fac_numerodocumento,
			trim(h.ch_fac_seriedocumento),
			h.ch_fac_tipodocumento,
			1,
			trim(c.cli_ruc),
			0,
			'-',
			1,
			'',
			'',
			'',
			'0'
		FROM
			fac_ta_factura_cabecera AS h
			JOIN inv_ta_almacenes AS a ON (h.ch_almacen = a.ch_almacen)
			JOIN int_clientes AS c ON (h.cli_codigo = c.cli_codigo)
		WHERE
			h.dt_fac_fecha BETWEEN '{$BeginDate}' AND '{$EndDate}'
			AND h.ch_fac_tipodocumento IN ('10','11','20','35');
		";

		SQLImplode($sql);

		// Facturas de Compra Manuales

		$sql = "
		SELECT 
			a.ch_sucursal,
            a.ch_almacen,
            cp.pro_cab_fechaemision,
            cp.pro_cab_fechaemision::timestamp,
            trim(replace(cp.pro_cab_numdocumento,'|','-')),
            trim(cp.pro_cab_seriedocumento),
            cp.pro_cab_tipdocumento,
            0,
            trim(cp.pro_codigo),
            0,
            '-',
            1,
            '',
            '',
            '',
			'0'
		FROM
			cpag_ta_cabecera AS cp
			JOIN inv_ta_almacenes AS a ON (cp.pro_cab_almacen = a.ch_almacen)
		WHERE
        	cp.pro_cab_fechaemision BETWEEN '{$BeginDate}' AND '{$EndDate}'
			AND cp.pro_cab_tipdocumento IN ('10','11','20','35');
		";

		SQLImplode($sql);
		$contenido = ob_get_contents();
		ob_end_clean();
		$comprimido = gzcompress($contenido);
		echo $comprimido;
	break;

   	case "ID": // InvoiceDetail - DETALLE

		argRangedCheck();

		// Tickets de Venta
		$sql = "
		SELECT
			a.ch_sucursal,
			a.ch_almacen,
			(CASE WHEN t.usr != '' THEN SUBSTR(TRIM(t.usr), 6) ELSE t.trans::TEXT END),
			(CASE WHEN t.usr != '' THEN SUBSTR(TRIM(t.usr), 0, 5) else cfp.nu_posz_z_serie END),
			t.td,
			trim(t.codigo),
			t.precio,
			t.cantidad,
			t.importe,
			1 AS activo
		FROM
			{$PosTransTable} AS t
			JOIN inv_ta_almacenes AS a ON (t.es = a.ch_almacen)
			LEFT JOIN pos_z_cierres AS cfp ON(t.caja = cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::DATE AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
		WHERE
			t.dia BETWEEN '{$BeginDate}' AND '{$EndDate}'
			AND t.td IN ('B','F')
		ORDER BY
			t.fecha;
		";

		SQLImplode($sql);

		// Facturas de Venta Manuales
		$sql = "
		SELECT
			a.ch_sucursal,
			a.ch_almacen,
			d.ch_fac_numerodocumento,
			trim(d.ch_fac_seriedocumento),
			d.ch_fac_tipodocumento,
			trim(d.art_codigo),
			d.nu_fac_precio,
			d.nu_fac_cantidad,
			d.nu_fac_valortotal
		FROM
			fac_ta_factura_detalle AS d
			JOIN fac_ta_factura_cabecera AS h USING (ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento, cli_codigo)
			JOIN inv_ta_almacenes AS a ON (h.ch_almacen = a.ch_almacen)
		WHERE
			h.dt_fac_fecha BETWEEN '{$BeginDate}' AND '{$EndDate}'
			AND h.ch_fac_tipodocumento IN ('10','11','20','35');
		";

		SQLImplode($sql);

		// Facturas de Compra
		$sql = "
		SELECT 
            a.ch_sucursal,
            a.ch_almacen,
            trim(replace(invc.cpag_num_pago,'|','-')),
            trim(invc.cpag_serie_pago),
            invc.cpag_tipo_pago,
            trim(invc.art_codigo),
            invc.mov_costounitario,
            invc.mov_cantidad,
            invc.mov_costototal
	    FROM
	     	cpag_ta_cabecera AS cp 
	     	INNER JOIN inv_ta_compras_devoluciones AS invc ON (
            cp.pro_cab_tipdocumento=invc.cpag_tipo_pago AND
            cp.pro_cab_seriedocumento=invc.cpag_serie_pago AND 
            cp.pro_cab_numdocumento=invc.cpag_num_pago AND
            cp.com_cab_numorden=invc.com_num_compra AND 
            cp.pro_codigo=invc.mov_entidad)
	     	JOIN inv_ta_almacenes AS a ON (invc.mov_almacen = a.ch_almacen)
        WHERE
        	cp.pro_cab_fechaemision BETWEEN '{$BeginDate} 00:00:00' AND '{$EndDate} 23:59:59'
			AND cp.pro_cab_tipdocumento IN ('10','11','20','35');
        ";

		SQLImplode($sql);
		$contenido = ob_get_contents();
		ob_end_clean();
		$comprimido = gzcompress($contenido);
		echo $comprimido;

	break;

    case "IT": // InvoiceTax
    
        argRangedCheck();

        // Tickets de Venta
		$sql = "
		SELECT
			FIRST(a.ch_sucursal),
			FIRST(a.ch_almacen),
		    (CASE WHEN FIRST(t.usr) != '' THEN SUBSTR(TRIM(t.usr), 6) ELSE t.trans::TEXT END),
			(CASE WHEN FIRST(t.usr) != '' THEN SUBSTR(TRIM(t.usr), 0, 5) ELSE FIRST(cfp.nu_posz_z_serie) END),
	        FIRST(t.td),
	        1,
	        SUM(t.importe - t.igv),
	        SUM(t.igv)
	    FROM
	        {$PosTransTable} AS t
	        JOIN inv_ta_almacenes AS a ON (t.es = a.ch_almacen)
	        LEFT JOIN pos_z_cierres AS cfp ON(t.caja = cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::DATE AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
	    WHERE
	        t.dia BETWEEN '{$BeginDate}' AND '{$EndDate}'
	        AND t.td IN ('B','F')
      	GROUP BY
	        t.trans,
	        t.caja,
	        t.usr;
		";

		SQLImplode($sql);

        // Facturas de Venta Manuales
        $sql = "
        SELECT
			a.ch_sucursal,
			a.ch_almacen,
			h.ch_fac_numerodocumento,
			trim(h.ch_fac_seriedocumento),
			h.ch_fac_tipodocumento,
			1,
			nu_fac_valortotal,
			nu_fac_impuesto1
		FROM
			fac_ta_factura_cabecera AS h
			JOIN inv_ta_almacenes AS a ON (h.ch_almacen = a.ch_almacen)
		WHERE
			h.dt_fac_fecha BETWEEN '{$BeginDate}' AND '{$EndDate}'
			AND h.ch_fac_tipodocumento IN ('10','11','20','35');
		";

        SQLImplode($sql);

        // Facturas de Compra -- Falta cambiar el valor del IGV
        $sql = "
        SELECT 
			first(a.ch_sucursal),
            first(a.ch_almacen),
            TRIM(REPLACE(inv.cpag_num_pago, '|', '-')),
            TRIM(inv.cpag_serie_pago),
            TRIM(inv.cpag_tipo_pago),
            0,
            SUM(inv.mov_costototal/1.18),
            SUM((inv.mov_costototal*0.18)/1.18)
        FROM
        	cpag_ta_cabecera AS cp 
	        INNER JOIN inv_ta_compras_devoluciones AS inv ON (
            cp.pro_cab_tipdocumento=inv.cpag_tipo_pago AND
            cp.pro_cab_seriedocumento=inv.cpag_serie_pago AND 
            cp.pro_cab_numdocumento=inv.cpag_num_pago AND
            cp.com_cab_numorden=inv.com_num_compra AND 
            cp.pro_codigo=inv.mov_entidad)
    	    JOIN inv_ta_almacenes AS a ON (inv.mov_almacen = a.ch_almacen)
        WHERE
        	cp.pro_cab_fechaemision BETWEEN '{$BeginDate} 00:00:00' AND '{$EndDate} 23:59:59'
			AND cp.pro_cab_tipdocumento IN ('10','11','20','35')
        GROUP BY
            inv.cpag_num_pago,
            inv.cpag_serie_pago,
            inv.cpag_tipo_pago;
		";

        SQLImplode($sql);
        $contenido = ob_get_contents();
        ob_end_clean();
        $comprimido = gzcompress($contenido);
        echo $comprimido;

	break;

    case "MH": // MovementHeader

        argRangedCheck();

		$sql = "
		SELECT
			a.ch_sucursal,
			a.ch_almacen,
			a.ch_almacen,
			'301',
			trim(c.cli_ruc),
			trim(h.ch_documento),
			'VC',
			'',
			first(h.dt_fecha),
			'VC',
			'',
			h.ch_documento,
			(CASE WHEN d.ch_articulo = '11620301' OR d.ch_articulo = '11620302' OR d.ch_articulo = '11620303' OR d.ch_articulo = '11620304' OR d.ch_articulo = '11620305' OR d.ch_articulo = '11620307' THEN
            	h.ch_lado
          	ELSE
            	'M'
			END) AS ch_lado
		FROM
			val_ta_cabecera h
          	JOIN val_ta_detalle d USING (ch_sucursal, dt_fecha, ch_documento)
			LEFT JOIN inv_ta_almacenes a ON (h.ch_sucursal = a.ch_almacen)
			LEFT JOIN int_clientes c ON (h.ch_cliente = c.cli_codigo)
		WHERE
			h.dt_fecha BETWEEN '{$BeginDate}' AND '{$EndDate}'
		GROUP BY
	        a.ch_sucursal,
	        a.ch_almacen,
	        c.cli_ruc,
	        h.ch_documento,
	        h.ch_lado,
	        d.ch_articulo;
		";

		SQLImplode($sql);

		$contenido = ob_get_contents();
		ob_end_clean();

		$comprimido = gzcompress($contenido);
		echo $comprimido;

	break;

    case "MD": // MovementDetail

		argRangedCheck();

		$sql = "	
		SELECT
			a.ch_sucursal,
			a.ch_almacen,
			a.ch_almacen,
			'301',
			trim(c.cli_ruc),
			trim(h.ch_documento),
			'VC',
			'',
			d.ch_articulo,
			CASE
				WHEN d.nu_precio_unitario IS NOT NULL AND d.nu_precio_unitario > 0 THEN d.nu_precio_unitario
				ELSE (d.nu_importe / d.nu_cantidad)
			END,
			d.nu_cantidad,
			d.nu_importe,
			h.dt_fecha,
			h.ch_lado
		FROM
			val_ta_cabecera h
			JOIN val_ta_detalle d USING (ch_sucursal, dt_fecha, ch_documento)
			LEFT JOIN inv_ta_almacenes a ON (h.ch_sucursal = a.ch_almacen)
			LEFT JOIN int_clientes c ON (h.ch_cliente = c.cli_codigo)
		WHERE
			h.dt_fecha BETWEEN '{$BeginDate}' AND '{$EndDate}';
		";

		SQLImplode($sql);

		$contenido = ob_get_contents();
		ob_end_clean();

		$comprimido = gzcompress($contenido);
		echo $comprimido;

	break;


    	case "BI": // Business Partner Information

        	argKeyCheck();

        	$sql = "
			(
				SELECT
					c.cli_ruc,
					c.cli_razsocial
				FROM
					int_clientes c
				WHERE
					c.cli_ruc = '{$CxSearchKey}'
			) UNION (
				SELECT
					p.pro_ruc,
					p.pro_razsocial
				FROM
					int_proveedores p
				WHERE
					p.pro_codigo = '{$CxSearchKey}'
			) UNION (
				SELECT
					r.ruc,
					r.razsocial
				FROM
					ruc r
				WHERE
					r.ruc = '{$CxSearchKey}'
			)
			LIMIT
				1;
		";

		SQLImplode($sql);
		$contenido = ob_get_contents();
		ob_end_clean();
		$comprimido = gzcompress($contenido);
		echo $comprimido;

        break;

    case "PI": // Product Information

        argKeyCheck();

        $sql = "
		SELECT
			trim(a.art_codigo),
			SUBSTR(a.art_descripcion, 0, 64)
		FROM
			int_articulos a
		WHERE
			a.art_codigo 	= '{$CxSearchKey}'
			AND art_estado 	= '0';
		";

        SQLImplode($sql);
        $contenido = ob_get_contents();
        ob_end_clean();
        $comprimido = gzcompress($contenido);
        echo $comprimido;

        break;

    case "PC":
    
        argRangedCheck();

        //corregido
        $sql = "
    	SELECT
			TRIM(FIRST(p.par_valor)),
			c.dia,
			c.turno,
			MAX(c.fecha)
		FROM
			pos_contometros c
			LEFT JOIN int_parametros p ON (p.par_nombre = 'codes')
		WHERE
			dia BETWEEN '{$BeginDate}' AND '{$EndDate}'
		GROUP BY
			c.dia,
			c.turno
		ORDER BY
			4 ASC;
		";
		error_log($sql);

        SQLImplode($sql);
        $contenido = ob_get_contents();
        ob_end_clean();
        $comprimido = gzcompress($contenido);
        echo $comprimido;

		break;
		
	case "PCB":

        argRangedCheck();

	    $postrans = "pos_trans".$fecha_pos[0]."".$fecha_pos[1];

	    $sql = "
SELECT
--(SELECT par_valor from int_parametros where par_nombre='codes' limit 1) as par_valor,
max(p.es) AS par_valor,
max(to_char(p.dia,'YYYY-MM-DD')) as dt_posz_fecha_sistema,
max(p.turno) as turno,
max(p.dt_posz_fecha_cierre) as dt_posz_fecha_cierre,
max(ff) as dt_posz_fecha_cierre1,
max(p.dia),
max(p.caja)
FROM
(SELECT 
max(es) as es,
max(dia) as dia,
min(trans) as i,
max(trans)as f,
max(fecha) as ff,
td,
caja,
turno,
max(sp.dt_posz_fecha_cierre) as dt_posz_fecha_cierre,
sp.nu_posz_z_serie as nu_posz_z_serie,

tm,
SUBSTR(TRIM(p.usr), 0, 5) _tmp

FROM
{$PosTransTable} p INNER JOIN pos_z_cierres sp on (p.caja::INTEGER=sp.ch_posz_pos::INTEGER AND p.turno::INTEGER=sp.nu_posturno::INTEGER and p.dia=sp.dt_posz_fecha_sistema)
WHERE
p.dia  BETWEEN '{$BeginDate}' AND '{$EndDate}'
AND p.td IN ('B','F')


GROUP BY
p.es,
p.dia,
p.turno,
p.caja,
p.td,
p.tm,
SUBSTR(TRIM(p.usr), 0, 5),
sp.nu_posz_z_serie

ORDER BY
caja,
td,
turno) AS p
GROUP BY

	p.es,
	p.dia,
	p.turno,
	p.caja,

	p.td,
	p.tm,
	p._tmp,
	p.nu_posz_z_serie

ORDER BY
p.turno;";

        /*$sql = "
		SELECT
--(SELECT par_valor from int_parametros where par_nombre='codes' limit 1) as par_valor,
max(p.es) AS par_valor,
max(to_char(p.dia,'YYYY-MM-DD')) as dt_posz_fecha_sistema,
max(p.turno) as t,
max(p.dt_posz_fecha_cierre) as dt_posz_fecha_cierre,
max(ff) as dt_posz_fecha_cierre1,
max(p.dia),
max(p.caja)
FROM
(SELECT 
max(es) as es,
max(dia) as dia,
min(trans) as i,
max(trans)as f,
max(fecha) as ff,
td,
caja,
turno,
max(sp.dt_posz_fecha_cierre) as dt_posz_fecha_cierre,
first(sp.nu_posz_z_serie) as nu_posz_z_serie
FROM
{$PosTransTable} p INNER JOIN pos_z_cierres sp on (p.caja::INTEGER=sp.ch_posz_pos::INTEGER AND p.turno::INTEGER=sp.nu_posturno::INTEGER and p.dia=sp.dt_posz_fecha_sistema)
WHERE
p.dia  BETWEEN '2017-12-07 00:00:00' AND '2017-12-07 23:59:59'
AND p.td IN ('B','F')
GROUP BY
caja,
td,
turno
ORDER BY
caja,
td,
turno) AS p
GROUP BY

	p.dia,
	p.turno,
	p.caja

ORDER BY
p.turno";*/
		
        SQLImplode($sql);
        $contenido = ob_get_contents();
        ob_end_clean();
        $comprimido = gzcompress($contenido);
        echo $comprimido;

        break;

    case "FT": //f_totalizer
        argRangedCheck();

        $sql = "
    	SELECT
			TRIM(FIRST(p.par_valor)),
			c.dia,
			c.turno,
			MAX(c.fecha),
			c.cnt_vol,
			c.cnt_val,
			c.num_lado,
			c.manguera
		FROM
			pos_contometros c
			LEFT JOIN int_parametros p ON (p.par_nombre = 'codes')
		WHERE
			dia BETWEEN '{$BeginDate}' AND '{$EndDate}'
			AND c.cnt_vol > 0.00
			AND c.cnt_val > 0.00
		GROUP BY
			c.dia,
			c.turno,
			c.cnt_vol,
			c.cnt_val,
			c.num_lado,
			c.manguera
		ORDER BY
			c.turno,
			c.num_lado,
			c.manguera;
		";

    	SQLImplode($sql);
    	$contenido = ob_get_contents();
		ob_end_clean();
    	$comprimido = gzcompress($contenido);
    	echo $comprimido;

        break;

    case "ZC": // c_sale_shift
        argRangedCheck();

        $sql = "	
		SELECT
			PT.es AS almacen,
			PT.dia AS dia,
			MIN(PT.trans) AS nu_numero_doc_ini,
			MAX(PT.trans) AS nu_numero_doc_fin,
			PT.turno,
			PT.caja,
			(CASE WHEN SUBSTR(TRIM(PT.usr), 0, 5) != '' THEN
				CASE
					WHEN PT.tm = 'V' AND PT.td = 'F' THEN '01'
					WHEN PT.tm = 'V' AND PT.td = 'B' THEN '03'
					ELSE '07'
				END
			ELSE
				'12'	
			END) AS doctype,
			(CASE WHEN SUBSTR(TRIM(PT.usr), 0, 5) != '' THEN
				SUBSTR(TRIM(PT.usr), 0, 5)
			ELSE
				PZC.nu_posz_z_serie
			END) AS documentserial,
			(CASE WHEN PT.td = 'B' THEN COUNT(PT.*) ELSE 0 END) AS nu_cantidad_boletas,
			(CASE WHEN PT.td = 'B' THEN SUM(PT.igv) ELSE 0 END) AS ss_impuesto_boletas,
			(CASE WHEN PT.td = 'B' THEN SUM(PT.importe) ELSE 0 END) AS ss_total_boletas,
			(CASE WHEN PT.td = 'F' THEN COUNT(PT.*) ELSE 0 END) AS nu_cantidad_facturas,
			(CASE WHEN PT.td = 'F' THEN SUM(PT.igv) ELSE 0 END) AS ss_impuesto_facturas,
			(CASE WHEN PT.td = 'F' THEN SUM(PT.importe) ELSE 0 END) AS ss_total_facturas,
			COUNT(PT.*) AS nu_cantidad_bf,
			SUM(PT.igv) AS ss_impuesto_bf,
			SUM(PT.importe) AS ss_total_bf,
			MAX(PZC.dt_posz_fecha_cierre) AS fe_last_cierre,
			MAX(PT.fecha) AS fe_last_cierre2
		FROM
			{$PosTransTable} AS PT
			INNER JOIN pos_z_cierres AS PZC ON(PZC.dt_posz_fecha_sistema = PT.dia AND PZC.ch_posz_pos = PT.caja AND PZC.nu_posturno::VARCHAR = PT.turno)
		WHERE
			dia BETWEEN '{$BeginDate}' AND '{$EndDate}'
			AND td IN ('B','F')
		GROUP BY
			PT.es,
			PT.dia,
			PT.turno,
			PT.caja,
			PT.td,
			PT.tm,
			SUBSTR(TRIM(PT.usr), 0, 5),
			PZC.nu_posz_z_serie
		ORDER BY
			PT.es,
			PT.dia,
			PT.turno,
			PT.caja,
			doctype DESC;
		";
		error_log($sql);

/*$sql = "
SELECT
almacen,
dia,
MIN(nu_numero_doc_ini) AS nu_numero_doc_ini,
MAX(nu_numero_doc_fin) AS nu_numero_doc_fin,
turno, 
caja,
string_agg(doctype, ',') AS doctype,--6
string_agg(documentserial, ',') AS documentserial,--7
SUM(nu_cantidad_boletas) AS nu_cantidad_boletas,
SUM(ss_impuesto_boletas) AS ss_impuesto_boletas,
SUM(ss_total_boletas) AS ss_total_boletas,
SUM(nu_cantidad_facturas) AS nu_cantidad_facturas,
SUM(ss_impuesto_facturas) AS ss_impuesto_facturas,
SUM(ss_total_facturas) AS ss_total_facturas,
SUM(nu_cantidad_bf) AS nu_cantidad_bf,
SUM(ss_impuesto_bf) AS ss_impuesto_bf,
SUM(ss_total_bf) AS ss_total_bf,
MAX(fe_last_cierre) AS fe_last_cierre,
MAX(fe_last_cierre2) AS fe_last_cierre2
FROM
(
SELECT
	PT.es AS almacen,
	PT.dia AS dia,
	MIN(PT.trans) AS nu_numero_doc_ini,
	MAX(PT.trans) AS nu_numero_doc_fin,
	PT.turno,
	PT.caja,
	(CASE WHEN SUBSTR(TRIM(PT.usr), 0, 5) != '' THEN
		CASE
			WHEN PT.tm = 'V' AND PT.td = 'F' THEN '01'
			WHEN PT.tm = 'V' AND PT.td = 'B' THEN '03'
			ELSE '07'
		END
	ELSE
		'12'	
	END) AS doctype,
	(CASE WHEN SUBSTR(TRIM(PT.usr), 0, 5) != '' THEN
		SUBSTR(TRIM(PT.usr), 0, 5)
	ELSE
		PZC.nu_posz_z_serie
	END) AS documentserial,
	(CASE WHEN PT.td = 'B' THEN COUNT(PT.*) ELSE 0 END) AS nu_cantidad_boletas,
	(CASE WHEN PT.td = 'B' THEN SUM(PT.igv) ELSE 0 END) AS ss_impuesto_boletas,
	(CASE WHEN PT.td = 'B' THEN SUM(PT.importe) ELSE 0 END) AS ss_total_boletas,
	(CASE WHEN PT.td = 'F' THEN COUNT(PT.*) ELSE 0 END) AS nu_cantidad_facturas,
	(CASE WHEN PT.td = 'F' THEN SUM(PT.igv) ELSE 0 END) AS ss_impuesto_facturas,
	(CASE WHEN PT.td = 'F' THEN SUM(PT.importe) ELSE 0 END) AS ss_total_facturas,
	COUNT(PT.*) AS nu_cantidad_bf,
	SUM(PT.igv) AS ss_impuesto_bf,
	SUM(PT.importe) AS ss_total_bf,
	MAX(PZC.dt_posz_fecha_cierre) AS fe_last_cierre,
	MAX(PT.fecha) AS fe_last_cierre2
FROM
	{$PosTransTable} AS PT
	LEFT JOIN pos_z_cierres AS PZC ON(PZC.dt_posz_fecha_sistema = PT.dia AND PZC.ch_posz_pos = PT.caja AND PZC.nu_posturno::VARCHAR = PT.turno)
WHERE
	dia BETWEEN '{$BeginDate}' AND '{$EndDate}'
	AND td IN ('B','F')
GROUP BY
	PT.es,
	PT.dia,
	PT.turno,
	PT.caja,
	PT.td,
	PT.tm,
	SUBSTR(TRIM(PT.usr), 0, 5),
	PZC.nu_posz_z_serie
ORDER BY
	PT.es,
	PT.dia,
	PT.turno,
	PT.caja,
	doctype DESC
) AS t

GROUP BY
almacen,
dia,
turno, 
caja
;";*/

        SQLImplode($sql);
        $contenido = ob_get_contents();
        ob_end_clean();
        $comprimido = gzcompress($contenido);
        echo $comprimido;

	break;

    case "INH": //i_inventoryheader

        argRangedCheck();

        $sql = "
        SELECT
			p.par_valor,--reemplazar por vacio para no correr los indices
			m.dt_fechamedicion,
			m.dt_fechactualizacion,
			m.ch_sucursal
		FROM
			pos_contometros AS c
			LEFT JOIN int_parametros AS p ON (p.par_nombre = 'codes'),
			comb_ta_mediciondiaria AS m 
		WHERE
			m.dt_fechamedicion BETWEEN '{$BeginDate}' AND '{$EndDate}'
		GROUP BY
			p.par_valor,
			m.dt_fechamedicion,
			m.dt_fechactualizacion,
			m.ch_sucursal;
		";

        SQLImplode($sql);
        $contenido = ob_get_contents();
        ob_end_clean();
        $comprimido = gzcompress($contenido);
        echo $comprimido;

        break;

    case "IND":

        argRangedCheck();

        $sql = "
        SELECT  
	    	par.par_valor,--reemplazar por vacio para no correr los indices
		  	ctm.dt_fechamedicion,
	    	ctm.dt_fechactualizacion,
	    	ct.ch_codigocombustible,
	    	ctm.nu_medicion 
	    FROM
			comb_ta_mediciondiaria AS ctm 
	        INNER JOIN comb_ta_tanques AS ct ON (ctm.ch_tanque = ct.ch_tanque)
			LEFT JOIN int_parametros AS par ON (par.par_nombre = 'codes')
	    WHERE
			ctm.dt_fechamedicion BETWEEN '{$BeginDate}' AND '{$EndDate}' 
	    ORDER BY
			ctm.dt_fechamedicion;
		";

		SQLImplode($sql);
        $contenido = ob_get_contents();
        ob_end_clean();
        $comprimido = gzcompress($contenido);
        echo $comprimido;

        break;

    case "DCP":

        $sql = "
        SELECT 
	        art_codigo,
	        MAX(pre_precio_act1),
	        FIRST(pre_fecactualiz)
	    FROM
	    	fac_lista_precios 
	    GROUP BY
	        art_codigo
	    ORDER BY
	    	art_codigo;
		";

        SQLImplode($sql);
        $contenido = ob_get_contents();
        ob_end_clean();
        $comprimido = gzcompress($contenido);
        echo $comprimido;

        break;

		case "POSCD":
			argRangedCheck();

			$sql = "SELECT ch_poscd FROM pos_aprosys WHERE da_fecha = '{$BeginDate}';";
			SQLImplode($sql);
			$contenido = ob_get_contents();
			ob_end_clean();
			$comprimido = gzcompress($contenido);
			echo $comprimido;
		break;

    case "SFE": // Series de Facturación Electrónica - TABLA = c_documentserial

        argFE();

        $sql = "SELECT doctype, documentserial FROM ebi_serial WHERE doctype = '{$doctype}' AND documentserial = '{$documentserial}';";
        SQLImplode($sql);
        $contenido = ob_get_contents();
        ob_end_clean();
        $comprimido = gzcompress($contenido);
        echo $comprimido;

        break;

	case "STK":
		//obtener cantidades y costos
		argRangedCheck();
		$sql = "SELECT
 sal.stk_almacen,
 sal.art_codigo,
 sal.stk_stock$BeginMonth,
 sal.stk_costo$BeginMonth
FROM inv_saldoalma sal
JOIN int_articulos art ON(sal.art_codigo = art.art_codigo)
JOIN inv_ta_almacenes alm ON(sal.stk_almacen = alm.ch_almacen)
WHERE art.art_estado = '0' AND sal.stk_periodo = '$BeginYear' AND alm.ch_clase_almacen = '1';";

		SQLImplode($sql);
		$contenido = ob_get_contents();
		ob_end_clean();
		$comprimido = gzcompress($contenido);
		echo $comprimido;

	break;

	case "PRODS":
		argRangedCheck();
		/*$sql = "SELECT
 TRIM(art.art_codigo) AS art_codigo,
 SUBSTR(art.art_descripcion, 0, 64) AS art_descripcion,
 (SELECT ch_almacen FROM inv_ta_almacenes WHERE ch_clase_almacen = '1') AS ch_almacen
FROM int_articulos art
WHERE art.art_estado = '0'
GROUP BY 1, 2, 3;";*/

		$sql = "SELECT
 TRIM(art.art_codigo) AS art_codigo,
 SUBSTR(art.art_descripcion, 0, 64) AS art_descripcion,
 sal.stk_almacen
FROM inv_saldoalma sal
JOIN int_articulos art ON(sal.art_codigo = art.art_codigo)
JOIN inv_ta_almacenes alm ON(sal.stk_almacen = alm.ch_almacen)
WHERE art.art_estado = '0' AND sal.stk_periodo = '$BeginYear' AND alm.ch_clase_almacen = '1'
;";

		SQLImplode($sql);
		$contenido = ob_get_contents();
		ob_end_clean();
		$comprimido = gzcompress($contenido);
		echo $comprimido;

	break;

	case "INVMOVH":
		argRangedCheck();
		$sql = "
SELECT
 FIRST(al_origen.ch_sigla_almacen) AS al_origen,
 FIRST(al_destino.ch_sigla_almacen) AS al_destino,
 FIRST(tipotransa.format_sunat) AS format_sunat,
 FIRST(tipotransa.tran_naturaleza) AS tran_naturaleza,
 FIRST(movialma.mov_fecha) AS fecha,
 FIRST(REPLACE(movialma.mov_docurefe, '|', '')) AS mov_docurefe,
 FIRST(movialma.mov_tipdocuref) AS mov_tipdocuref,
 FIRST(movialma.mov_entidad) AS mov_entidad,
 FIRST(movialma.com_serie_compra) AS com_serie_compra,
 FIRST(movialma.com_num_compra) AS com_num_compra,
 SUM(movialma.mov_cantidad) AS mov_cantidad,
 SUM(movialma.mov_costounitario) AS mov_costounitario,
 SUM(movialma.mov_costototal) AS mov_costototal,
 (CASE WHEN FIRST(proveedor.pro_ruc) IS NULL OR FIRST(proveedor.pro_ruc) = '' THEN 'GENERIC' ELSE FIRST(proveedor.pro_ruc) END) pro_ruc,
 movialma.mov_numero AS mov_numero,
 movialma.tran_codigo AS tran_codigo,
 FIRST(tipodocumento.tab_car_03) AS codigo_sunat
FROM
 inv_movialma movialma
 JOIN inv_tipotransa tipotransa ON (movialma.tran_codigo = tipotransa.tran_codigo)
 LEFT JOIN int_proveedores proveedor ON (movialma.mov_entidad = proveedor.pro_codigo)
 LEFT JOIN int_tabla_general AS tipodocumento ON(
  movialma.mov_tipdocuref = substring(TRIM(tipodocumento.tab_elemento) for 2 FROM length(TRIM(tipodocumento.tab_elemento))-1)
  AND tipodocumento.tab_tabla = '08'
  AND tipodocumento.tab_elemento <> '000000'
 )
 JOIN inv_ta_almacenes al_origen ON (movialma.mov_almaorigen = al_origen.ch_almacen)
 JOIN inv_ta_almacenes al_destino ON (movialma.mov_almadestino = al_destino.ch_almacen)
WHERE
 movialma.mov_fecha BETWEEN '{$BeginDate} 00:00:00' AND '{$EndDate} 23:59:59'
GROUP BY
 movialma.mov_numero,
 movialma.tran_codigo
ORDER BY
 fecha;
 		";

		SQLImplode($sql);
		$contenido = ob_get_contents();
		ob_end_clean();
		$comprimido = gzcompress($contenido);
		echo $comprimido;

	break;

	case "INVMOVD":
		argRangedCheck();
		$sql = "
SELECT
 al_origen.ch_sigla_almacen,
 al_destino.ch_sigla_almacen,
 movialma.mov_numero,
 movialma.tran_codigo,
 tipotransa.tran_naturaleza,
 REPLACE(movialma.mov_docurefe, '|', '') AS mov_docurefe,
 movialma.mov_tipdocuref,
 movialma.mov_entidad,
 movialma.com_serie_compra,
 movialma.com_num_compra,
 movialma.art_codigo,
 movialma.mov_cantidad AS mov_cantidad,
 movialma.mov_costounitario AS mov_costounitario,
 movialma.mov_costototal AS mov_costototal,
 movialma.mov_fecha
FROM
 inv_movialma movialma
 JOIN inv_tipotransa tipotransa ON (movialma.tran_codigo = tipotransa.tran_codigo)
 JOIN inv_ta_almacenes al_origen ON (movialma.mov_almaorigen = al_origen.ch_almacen)
 JOIN inv_ta_almacenes al_destino ON (movialma.mov_almadestino = al_destino.ch_almacen)
WHERE
 movialma.mov_fecha BETWEEN '{$BeginDate} 00:00:00' AND '{$EndDate} 23:59:59'
ORDER BY
 movialma.mov_fecha;
 		";

		SQLImplode($sql);
		$contenido = ob_get_contents();
		ob_end_clean();
		$comprimido = gzcompress($contenido);
		echo $comprimido;

	break;

	case "GETLINES":
		argRangedCheck();
		$sql = "SELECT
(SELECT ch_almacen FROM inv_ta_almacenes WHERE ch_clase_almacen = '1') AS ch_almacen,
tab_elemento,
tab_descripcion
FROM int_tabla_general WHERE tab_tabla = '20' ORDER BY tab_elemento;";

		SQLImplode($sql);
		$contenido = ob_get_contents();
		ob_end_clean();
		$comprimido = gzcompress($contenido);
		echo $comprimido;

	break;

	/**
	 * Obtene los productos con su respectivas lineas para hacer actualizaciones en Opensoft
	 */
	case "GETPRODANDLINES":
		argRangedCheck();
		$sql = "SELECT
 (SELECT ch_almacen FROM inv_ta_almacenes WHERE ch_clase_almacen = '1') AS ch_almacen,
 articulo.art_codigo,
 articulo.art_linea,
 linea.tab_descripcion
FROM int_articulos articulo
JOIN int_tabla_general linea ON (articulo.art_linea = linea.tab_elemento AND tab_tabla = '20');";

		SQLImplode($sql);
		$contenido = ob_get_contents();
		ob_end_clean();
		$comprimido = gzcompress($contenido);
		echo $comprimido;

	break;

    default:
        die("ERR_INVALID_MOD");
}

/**
 * Historial de cambios.
 * 2017-08-24:
 * Case IH
 * Seleccion de parte en una cadena al encontra barra "|" campo t.text1
 * Linea actualizada: FIRST(split_part(t.text1, '|', 1)),
 * ----
 * Actual (Anotado 17-12-20)
 */
?>