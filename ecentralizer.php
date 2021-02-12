<?php

include_once("/sistemaweb/include/config.php");
include_once("/sistemaweb/include/dbsqlca.php");

$r = ob_start(true);

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
    $EndDate = substr($CxEnd, 0, 4) . "-" . substr($CxEnd, 4, 2) . "-" . substr($CxEnd, 6, 2);
}

function argKeyCheck() {
    global $CxSearchKey;

    if (!isset($_REQUEST['sk']))
        die("ERR_INVALID_ARGS_KEY");

    $CxSearchKey = $_REQUEST['sk'];
    if ($CxSearchKey == "" || strlen($CxSearchKey) < 5)
        die("ERR_INVALID_KEY");
}

global $db_host, $db_user, $db_password, $db_name;
$sqlca = new pgsqlDB($db_host, $db_user, $db_password, $db_name);

if (!isset($_REQUEST['mod']))
    die("ERR_INVALID_ARGS");


$CxModule = $_REQUEST['mod'];


switch ($CxModule) {

	case "IH": // NAME TABLE: c_invoiceheader

		argRangedCheck();

		$sql = "
			SELECT
				FIRST(a.ch_sucursal),
				FIRST(a.ch_almacen),
				FIRST(t.dia)::date,
				FIRST(t.fecha)
				, case
				when FIRST(t.usr) != '' then
					SUBSTR(TRIM(t.usr), 6) --NUMERO
				else
					t.trans::TEXT --NUMERO DOCUMENTO ELECTRONICO
				end
				, case
				when FIRST(t.usr) != '' then
					SUBSTR(TRIM(t.usr), 0, 5)
				else
					FIRST(cfp.nu_posz_z_serie) --SERIE DOCUMENTO ELECTRONICO
				end
				,
				FIRST(t.td),
				1,
				(CASE WHEN FIRST(t.ruc) IS NULL OR FIRST(t.ruc) = '' THEN 'GENERIC' ELSE FIRST(t.ruc) END),
				(CASE WHEN FIRST(t.fpago) = '1' THEN '0' ELSE FIRST(t.at) END),
				FIRST(t.text1),
				1
				, FIRST(t.usr)
				, case
					when FIRST(t.tm) = 'V' and FIRST(t.td) = 'F' then '01'
					when FIRST(t.tm) = 'V' and FIRST(t.td) = 'B' then '03'
					when FIRST(t.tm) = 'D' OR FIRST(t.tm) = 'A' then '07'
				end as _type
			FROM
				{$PosTransTable} t
				JOIN inv_ta_almacenes a ON (t.es = a.ch_almacen)
				LEFT JOIN pos_z_cierres cfp ON(t.caja = cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::DATE AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
			WHERE
				t.dia BETWEEN '{$BeginDate}' AND '{$EndDate}'
				AND t.td IN ('B','F')
				AND t.tm IN ('V', 'D')
				AND t.trans||'-'||t.caja NOT IN (
				SELECT
					LAST(venta_tickes.tickes_refe)
				FROM (
					SELECT 
						(p.trans||'-'||p.caja) as tickes_refe,
						p.trans,
						extorno.trans as trans_ext,
						extorno.registro,
						extorno.trans1,
						p.fecha
					FROM
						{$PosTransTable} p
						INNER JOIN(
								SELECT 
									(dia|| caja || td ||turno ||codigo ||tipo || pump || fpago ||  abs(cantidad) ||abs(precio)|| abs(igv) || abs(importe) ||ruc) as registro,
									fecha,
									trans||'-'||caja as trans,
									trans as trans1
								FROM
									{$PosTransTable}
								WHERE
									tm = 'A'
									AND dia BETWEEN '{$BeginDate}' AND '{$EndDate}'
									AND td IN ('B','F')
								) AS extorno ON (p.dia|| p.caja || p.td ||p.turno ||p.codigo ||p.tipo || p.pump || p.fpago ||  abs(p.cantidad) ||abs(p.precio)|| abs(p.igv) || abs(p.importe) ||ruc) = extorno.registro
						AND p.tm IN ('V', 'D')
						AND p.td IN ('B','F')
						AND p.trans < extorno.trans1
					ORDER BY
						p.fecha asc
					) AS venta_tickes
				GROUP BY
					venta_tickes.registro,
					venta_tickes.trans_ext)
			GROUP BY
				t.trans,
				t.caja,
				t.fecha,
				t.usr
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
				1
				,'',''
			FROM
				fac_ta_factura_cabecera h
				JOIN inv_ta_almacenes a ON (h.ch_almacen = a.ch_almacen)
				JOIN int_clientes c ON (h.cli_codigo = c.cli_codigo)
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
            1
            ,'',''
		FROM
			cpag_ta_cabecera  cp
			JOIN inv_ta_almacenes a ON (cp.pro_cab_almacen = a.ch_almacen)
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

   	case "ID": // InvoiceDetail

		argRangedCheck();

		// Tickets de Venta
		$sql = "
			SELECT
				a.ch_sucursal,
				a.ch_almacen,
				t.trans,
				cfp.nu_posz_z_serie,
				t.td,
				trim(t.codigo),
				t.precio,
				t.cantidad,
				t.importe,
				1 as activo
			FROM
				{$PosTransTable} t
				JOIN inv_ta_almacenes a ON (t.es = a.ch_almacen)
				LEFT JOIN pos_z_cierres cfp ON(t.caja = cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::DATE AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
			WHERE
				t.dia BETWEEN '{$BeginDate}' AND '{$EndDate}'
				AND t.td IN ('B','F')
				AND t.tm IN ('V', 'D')
				AND t.trans||'-'||t.caja NOT IN (
				SELECT
					LAST(venta_tickes.tickes_refe)
				FROM (
					SELECT 
						(p.trans||'-'||p.caja) as tickes_refe,
						p.trans,
						extorno.trans as trans_ext,
						extorno.registro,
						extorno.trans1,
						p.fecha
					FROM
						{$PosTransTable} p
						INNER JOIN(
								SELECT 
									(dia|| caja || td ||turno ||codigo ||tipo || pump || fpago ||  abs(cantidad) ||abs(precio)|| abs(igv) || abs(importe) ||ruc) as registro,
									fecha,
									trans||'-'||caja as trans,
									trans as trans1
								FROM
									{$PosTransTable}
								WHERE
									tm = 'A'
									AND dia BETWEEN '{$BeginDate}' AND '{$EndDate}'
									AND td IN ('B','F')
								) AS extorno ON (p.dia|| p.caja || p.td ||p.turno ||p.codigo ||p.tipo || p.pump || p.fpago ||  abs(p.cantidad) ||abs(p.precio)|| abs(p.igv) || abs(p.importe) ||ruc) = extorno.registro
						AND p.tm IN ('V', 'D')
						AND p.td IN ('B','F')
						AND p.trans < extorno.trans1
					ORDER BY
						p.fecha asc
					) AS venta_tickes

				GROUP BY
					venta_tickes.registro,
					venta_tickes.trans_ext)
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
			fac_ta_factura_detalle d
			JOIN fac_ta_factura_cabecera h USING (ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento, cli_codigo)
			JOIN inv_ta_almacenes a ON (h.ch_almacen = a.ch_almacen)
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
	     	cpag_ta_cabecera cp 
	     	INNER JOIN inv_ta_compras_devoluciones invc ON 
            cp.pro_cab_tipdocumento=invc.cpag_tipo_pago AND
            cp.pro_cab_seriedocumento=invc.cpag_serie_pago AND 
            cp.pro_cab_numdocumento=invc.cpag_num_pago AND
            cp.com_cab_numorden=invc.com_num_compra AND 
            cp.pro_codigo=invc.mov_entidad
	     	JOIN inv_ta_almacenes a ON (invc.mov_almacen = a.ch_almacen)
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
	        case
	            when t.usr != '' then
	              SUBSTR(TRIM(t.usr), 6) --NUMERO
	            else
	             t.trans::TEXT --NUMERO DOCUMENTO ELECTRONICO
	        end,
	        case
	            when t.usr != '' then
	             SUBSTR(TRIM(t.usr), 0, 5)
	            else
	             FIRST(cfp.nu_posz_z_serie) --SERIE DOCUMENTO ELECTRONICO
	        end,
	        FIRST(t.td),
	        1,
	        sum(t.importe - t.igv),
	        sum(t.igv)
	      FROM
	        {$PosTransTable} t
	        JOIN inv_ta_almacenes a ON (t.es = a.ch_almacen)
	        LEFT JOIN pos_z_cierres cfp ON(t.caja = cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::DATE AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
	      WHERE
	        t.dia BETWEEN '{$BeginDate}' AND '{$EndDate}'
	        AND t.td IN ('B','F')
	        AND t.tm IN ('V', 'D')
	        AND t.trans||'-'||t.caja NOT IN (
	        SELECT
	          LAST(venta_tickes.tickes_refe)
	        FROM (
	          SELECT 
	            (p.trans||'-'||p.caja) as tickes_refe,
	            p.trans,
	            extorno.trans as trans_ext,
	            extorno.registro,
	            extorno.trans1,
	            p.fecha
	          FROM
	            {$PosTransTable} p
	            INNER JOIN(
	                SELECT 
	                  (dia|| caja || td ||turno ||codigo ||tipo || pump || fpago ||  abs(cantidad) ||abs(precio)|| abs(igv) || abs(importe) ||ruc) as registro,
	                  fecha,
	                  trans||'-'||caja as trans,
	                  trans as trans1
	                FROM
	                  {$PosTransTable}
	                WHERE
	                  tm = 'A'
	                  AND dia BETWEEN '{$BeginDate}' AND '{$EndDate}'
	                  AND td IN ('B','F')
	                ) AS extorno ON (p.dia|| p.caja || p.td ||p.turno ||p.codigo ||p.tipo || p.pump || p.fpago ||  abs(p.cantidad) ||abs(p.precio)|| abs(p.igv) || abs(p.importe) ||ruc) = extorno.registro
	            AND p.tm IN ('V', 'D')
	            AND p.td IN ('B','F')
	            AND p.trans < extorno.trans1
	          ORDER BY
	            p.fecha asc
	          ) AS venta_tickes
	        GROUP BY
	          venta_tickes.registro,
	          venta_tickes.trans_ext)
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
			fac_ta_factura_cabecera h
			JOIN inv_ta_almacenes a ON (h.ch_almacen = a.ch_almacen)
		WHERE
			h.dt_fac_fecha BETWEEN '{$BeginDate}' AND '{$EndDate}'
			AND h.ch_fac_tipodocumento IN ('10','11','20','35');
		";

        SQLImplode($sql);

        // Facturas de Compra
        $sql = "
        SELECT 
			first(a.ch_sucursal),
            first(a.ch_almacen),
            trim(replace(inv.cpag_num_pago,'|','-')),
            trim(inv.cpag_serie_pago),
            trim(inv.cpag_tipo_pago),
            0,
            sum(inv.mov_costototal/1.18),
            sum((inv.mov_costototal*0.18)/1.18)
        FROM
        	cpag_ta_cabecera cp 
	        INNER JOIN inv_ta_compras_devoluciones inv ON 
            cp.pro_cab_tipdocumento=inv.cpag_tipo_pago AND
            cp.pro_cab_seriedocumento=inv.cpag_serie_pago AND 
            cp.pro_cab_numdocumento=inv.cpag_num_pago AND
            cp.com_cab_numorden=inv.com_num_compra AND 
            cp.pro_codigo=inv.mov_entidad
    	    JOIN inv_ta_almacenes a ON (inv.mov_almacen = a.ch_almacen)
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
				h.ch_lado
			FROM
				val_ta_cabecera h
				LEFT JOIN inv_ta_almacenes a ON (h.ch_sucursal = a.ch_almacen)
				LEFT JOIN int_clientes c ON (h.ch_cliente = c.cli_codigo)
			WHERE
				h.dt_fecha BETWEEN '{$BeginDate}' AND '{$EndDate}'
			GROUP BY
				a.ch_sucursal,
				a.ch_almacen,
				c.cli_ruc,
				h.ch_documento,
				h.ch_lado;
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
				JOIN inv_ta_almacenes a ON (h.ch_sucursal = a.ch_almacen)
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

	case "AFERI": // Business Partner Information

	    argRangedCheck();

		$fecha_pos 	= explode("-", $BeginDate);
		$postrans 	= "pos_trans".$fecha_pos[0]."".$fecha_pos[1];

		$sql = "	
            	SELECT
					LAST(venta_tickes.tickes_refe),
					venta_tickes.registro,
					venta_tickes.trans_ext
				FROM (
					SELECT 
						(p.trans||'-'||p.caja) as tickes_refe,
						p.trans,
						extorno.trans as trans_ext,
						extorno.registro,
						extorno.trans1,
						p.fecha
					FROM
						$postrans p
						INNER JOIN(
								SELECT 
									(dia|| caja || td ||turno ||codigo ||tipo || pump || fpago ||  abs(cantidad) ||abs(precio)|| abs(igv) || abs(importe) ||ruc) as registro,
									fecha,
									trans||'-'||caja as trans,
									trans as trans1
								FROM
									$postrans
								WHERE
									tm = 'A'
									AND dia BETWEEN '{$BeginDate}' AND '{$EndDate}'
									AND td IN ('B','F')
								) AS extorno ON (p.dia|| p.caja || p.td ||p.turno ||p.codigo ||p.tipo || p.pump || p.fpago ||  abs(p.cantidad) ||abs(p.precio)|| abs(p.igv) || abs(p.importe) ||ruc) = extorno.registro
						AND p.tm = 'V'
						AND p.td IN ('B','F')
						AND p.trans < extorno.trans1
					ORDER BY
						p.fecha asc
					) AS venta_tickes
				GROUP BY
					venta_tickes.registro,
					venta_tickes.trans_ext;
		";

        	SQLImplode($sql);

		$contenido = ob_get_contents();

        	ob_end_clean();

        	$comprimido = gzcompress($contenido);

        	echo $comprimido;

	break;

	case "AFERIVALE": // Business Partner Information

        argRangedCheck();

        $fecha_pos = explode("-", $BeginDate);
        $postrans="pos_trans".$fecha_pos[0]."".$fecha_pos[1];
        $sql = "	
			
            SELECT last(venta_tickes.tickes_refe),venta_tickes.registro,venta_tickes.trans_ext FROM 
            (
                    SELECT 
                    (p.trans||'-'||p.caja) as tickes_refe,p.trans,extorno.trans as trans_ext,extorno.registro,extorno.trans1,p.fecha
                    FROM $postrans p
                    INNER JOIN 
                    (
                    SELECT 
                    (dia|| caja || td ||turno ||codigo ||tipo || pump || fpago ||  abs(cantidad) ||abs(precio)|| abs(igv) || abs(importe) ||ruc) as registro,
                    fecha,trans||'-'||caja as trans,
                    trans as trans1
                    FROM $postrans  where tm='A' AND  dia  BETWEEN '{$BeginDate}' AND '{$EndDate}' AND td='N'
                    ) as extorno 

                    ON 
                    (p.dia|| p.caja || p.td ||p.turno ||p.codigo ||p.tipo || p.pump || p.fpago ||  abs(p.cantidad) ||abs(p.precio)|| abs(p.igv) || abs(p.importe) ||ruc)=extorno.registro
                    AND tm='V'
                    AND p.trans<extorno.trans1

                    ORDER BY p.fecha asc
            ) AS venta_tickes

GROUP BY venta_tickes.registro,venta_tickes.trans_ext
;



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

        $sql = "
        	SELECT
				trim(first(p.par_valor)),
				c.dia,
				c.turno,
				max(c.fecha)
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
				(SELECT par_valor from int_parametros where par_nombre='codes' limit 1) as par_valor,
				max(to_char(p.dia,'YYYY-MM-DD')) as dt_posz_fecha_sistema,
				max(p.turno) as t,
				max(p.dt_posz_fecha_cierre) as dt_posz_fecha_cierre,
				max(ff) as dt_posz_fecha_cierre1
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
					{$PosTransTable}  p INNER JOIN pos_z_cierres sp on (p.caja::INTEGER=sp.ch_posz_pos::INTEGER AND p.turno::INTEGER=sp.nu_posturno::INTEGER and p.dia=sp.dt_posz_fecha_sistema)
				WHERE
					p.dia  BETWEEN '{$BeginDate}' AND '{$EndDate}'
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
				p.turno
			ORDER BY
				p.turno;
        ";
		
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
				trim(first(p.par_valor)),
				c.dia,
				c.turno,
				max(c.fecha),
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
				max(es) as par_valor,
				max(to_char(p.dia,'YYYY-MM-DD')) as dt_posz_fecha_sistema,
				0 as nu_posz_z_numero,
				min(p.i) as nu_posz_tran_inicial,
				max(p.f) as nu_posz_tran_final,
				max(p.cb) as nu_posz_b_transas,
				sum(p.importeboleta) as nu_posz_b_total,
				sum(p.igvboleta) as nu_posz_b_impuesto,
				max(p.cf) as nu_posz_f_transas,
				sum(p.importefactura) as nu_posz_f_total,
				sum(p.igvfactura) as nu_posz_f_impuesto,
				(max(p.cb)+max(p.cf)) as nu_posz_t_transas,
				sum(p.importefactura)+sum(p.importeboleta) as nu_posz_t_total,
				sum(p.igvfactura)+sum(p.igvboleta) as nu_posz_t_impuesto,
				first(p.nu_posz_z_serie) as nu_posz_z_serie,
				max(p.dt_posz_fecha_cierre) as dt_posz_fecha_cierre,
				p.caja as ch_posz_pos,
				max(ff) as dt_posz_fecha_cierre1
			FROM
				(SELECT 
					max(p.es) as es,
					max(dia) as dia,
					min(trans) as i,
					max(trans)as f,
					max(fecha) as ff,
					td,
					caja,
					turno,
					max(sp.dt_posz_fecha_cierre) as dt_posz_fecha_cierre,
					first(sp.nu_posz_z_serie) as nu_posz_z_serie,
					(CASE WHEN td = 'F' THEN count(*) ELSE 0 END) AS cf,
					(CASE WHEN td = 'B' THEN count(*) ELSE 0 END) AS cb,
					(CASE WHEN td = 'F' THEN SUM(importe) ELSE 0 END) AS importefactura,
					(CASE WHEN td = 'F' THEN SUM(igv) ELSE 0 END) AS igvfactura,
					(CASE WHEN td = 'B' THEN SUM(importe) ELSE 0 END) AS importeboleta,
					(CASE WHEN td = 'B' THEN SUM(igv) ELSE 0 END) AS igvboleta
				FROM
					{$PosTransTable} p
					INNER JOIN pos_z_cierres sp ON(p.caja::INTEGER = sp.ch_posz_pos::INTEGER AND p.turno::INTEGER=sp.nu_posturno::INTEGER and p.dia=sp.dt_posz_fecha_sistema)
				WHERE
					p.dia  BETWEEN '{$BeginDate}' AND '{$EndDate}'
					AND p.td IN ('B','F')
					AND p.tm IN ('V', 'D')
					AND p.trans||'-'||p.caja NOT IN (
					SELECT
						LAST(venta_tickes.tickes_refe)
					FROM (
						SELECT 
							(p.trans||'-'||p.caja) as tickes_refe,
							p.trans,
							extorno.trans as trans_ext,
							extorno.registro,
							extorno.trans1,
							p.fecha
						FROM
							{$PosTransTable} p
							INNER JOIN(
									SELECT 
										(dia|| caja || td ||turno ||codigo ||tipo || pump || fpago ||  abs(cantidad) ||abs(precio)|| abs(igv) || abs(importe) ||ruc) as registro,
										fecha,
										trans||'-'||caja as trans,
										trans as trans1
									FROM
										{$PosTransTable}
									WHERE
										tm = 'A'
										AND dia BETWEEN '{$BeginDate}' AND '{$EndDate}'
										AND td IN ('B','F')
									) AS extorno ON (p.dia|| p.caja || p.td ||p.turno ||p.codigo ||p.tipo || p.pump || p.fpago ||  abs(p.cantidad) ||abs(p.precio)|| abs(p.igv) || abs(p.importe) ||ruc) = extorno.registro
							AND p.tm IN ('V', 'D')
							AND p.td IN ('B','F')
							AND p.trans < extorno.trans1
						ORDER BY
							p.fecha asc
						) AS venta_tickes
					GROUP BY
						venta_tickes.registro,
						venta_tickes.trans_ext)
				GROUP BY
					caja,
					td,
					turno
				ORDER BY
					caja,
					td,
					turno
				) AS p
			GROUP BY
				p.caja,
				p.turno
			ORDER BY
				p.caja DESC,
				p.turno;
		";

        SQLImplode($sql);

        $contenido = ob_get_contents();

        ob_end_clean();

        $comprimido = gzcompress($contenido);

        echo $comprimido;

	break;

    case "INH": //i_inventoryheader
        argRangedCheck();

        $sql = "	SELECT
					p.par_valor,
					m.dt_fechamedicion,
					m.dt_fechactualizacion,
					m.ch_sucursal
				FROM
					pos_contometros c
					LEFT JOIN int_parametros p ON (p.par_nombre = 'codes'),
					comb_ta_mediciondiaria m 
				WHERE
					m.dt_fechamedicion BETWEEN '{$BeginDate}' AND '{$EndDate}'
				GROUP BY
					p.par_valor,
					m.dt_fechamedicion,
					m.dt_fechactualizacion,
					m.ch_sucursal;  ";
        SQLImplode($sql);
        $contenido = ob_get_contents();
        ob_end_clean();
        $comprimido = gzcompress($contenido);
        echo $comprimido;
        break;

    case "IND":
        argRangedCheck();

        $sql = "		SELECT  
				    	par.par_valor,
		        	  	ctm.dt_fechamedicion,
				    	ctm.dt_fechactualizacion,
				    	ct.ch_codigocombustible,
				    	ctm.nu_medicion 
			        FROM
					comb_ta_mediciondiaria ctm 
			                INNER JOIN comb_ta_tanques ct on (ctm.ch_tanque=ct.ch_tanque)
					LEFT JOIN int_parametros par ON (par.par_nombre = 'codes')
			        WHERE
					ctm.dt_fechamedicion between '{$BeginDate}' AND '{$EndDate}' 
			        ORDER BY
					ctm.dt_fechamedicion;";
        SQLImplode($sql);
        $contenido = ob_get_contents();
        ob_end_clean();
        $comprimido = gzcompress($contenido);
        echo $comprimido;
        break;
    case "DCP":
        //argRangedCheck();

        $sql = "SELECT 
                            art_codigo,
                            max(pre_precio_act1),
                            first(pre_fecactualiz)
                        FROM   fac_lista_precios 
                        GROUP BY
                                art_codigo
                        ORDER BY art_codigo ; ";
        SQLImplode($sql);
        $contenido = ob_get_contents();
        ob_end_clean();
        $comprimido = gzcompress($contenido);
        echo $comprimido;
        break;

		case "POSCD":
			argRangedCheck();

			$sql = "SELECT ch_poscd FROM pos_aprosys where da_fecha = '{$BeginDate}';";
			SQLImplode($sql);
			$contenido = ob_get_contents();
			ob_end_clean();
			$comprimido = gzcompress($contenido);
			echo $comprimido;
		break;

    default:
        die("ERR_INVALID_MOD");
}
?>