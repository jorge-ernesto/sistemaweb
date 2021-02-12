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
                echo (($k == 0) ? "" : "¿") . $v;
        }
        echo "\n";
    }
}

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
    case "IH": // InvoiceHeader	
        argRangedCheck();

        // Tickets de Venta
        $sql = "	SELECT
					first(a.ch_sucursal),
					first(a.ch_almacen),
					first(t.dia)::date,
					first(t.fecha),
					t.trans,
					first(p.nroserie),
					first(t.td),
					1,
					CASE
						WHEN first(t.ruc) IS NULL OR first(t.ruc) = '' THEN 'GENERIC'
						ELSE first(t.ruc)
					END,
					CASE
						WHEN first(t.fpago) = '1' THEN '0'
						ELSE first(t.at)
					END,
                                        first(t.text1),
                                        
                                        case when t.trans in(SELECT 
                                        first(trans) as id
                                        FROM  {$PosTransTable} where  tm!='A'  AND dia BETWEEN '{$BeginDate}' AND '{$EndDate}' 
                                        and (trim(caja)||dia||trim(turno)||trim(codigo)||trim(cantidad::TEXT)||abs(importe)||trim(ruc)||trim(pump)||trim(tipo)) in
                                        (SELECT 
                                        trim(caja)||dia||trim(turno)||trim(codigo)||trim(cantidad::TEXT)||abs(importe)||trim(ruc)||trim(pump)||trim(tipo) as iden  
                                        FROM  {$PosTransTable}  where tm='A'  AND dia BETWEEN '{$BeginDate}' AND '{$EndDate}' )
                                        GROUP BY (trim(caja)||dia||trim(turno)||trim(codigo)||trim(cantidad::TEXT)||abs(importe)||trim(ruc)||trim(pump)||trim(tipo))
                                        ORDER BY id
                                        ) then 
                                        '0'
                                        else
                                        '1'
                                        end as activo
                                        
				FROM
					{$PosTransTable} t
					JOIN inv_ta_almacenes a ON (t.es = a.ch_almacen)
					JOIN pos_cfg p ON (t.caja = p.pos)
				WHERE
					t.dia BETWEEN '{$BeginDate}' AND '{$EndDate}'
					AND t.td IN ('B','F') AND 
                                        t.tm!='A'
				GROUP BY
					t.trans,
					t.caja;";

        SQLImplode($sql);

        // echo $contenido;
        //echo "listo =>" . $contenido;
        // Facturas de Venta Manuales
        $sql = "	SELECT
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
				FROM
					fac_ta_factura_cabecera h
					JOIN inv_ta_almacenes a ON (h.ch_almacen = a.ch_almacen)
					JOIN int_clientes c ON (h.cli_codigo = c.cli_codigo)
				WHERE
					h.dt_fac_fecha BETWEEN '{$BeginDate}' AND '{$EndDate}'
					AND h.ch_fac_tipodocumento IN ('10','11','20','35');
                                        ";
        SQLImplode($sql);

        // Facturas de Compra
        $sql = "SELECT 
                                a.ch_sucursal,
                                a.ch_almacen,
                                cp.pro_cab_fechaemision,
                                cp.pro_cab_fechaemision::timestamp,
                                trim(replace(cp.pro_cab_numdocumento,'|','-')),
                                trim(cp.pro_cab_seriedocumento),
                                cp. pro_cab_tipdocumento,
                                0,
                                trim(cp.pro_codigo),
                                0,
                                '-',
                                1
                    FROM cpag_ta_cabecera  cp
                    JOIN inv_ta_almacenes a ON (cp.pro_cab_almacen = a.ch_almacen)
                    WHERE cp.pro_cab_fechaemision BETWEEN '{$BeginDate}' AND '{$EndDate}'";
        SQLImplode($sql);

        $contenido = ob_get_contents();
        ob_end_clean();
        $comprimido = gzcompress($contenido);
        echo $comprimido;


        break;

    case "ID": // InvoiceDetail
        argRangedCheck();

        // Tickets de Venta
        $sql = "	SELECT
					a.ch_sucursal,
					a.ch_almacen,
					t.trans,
					p.nroserie,
					t.td,
					trim(t.codigo),
					t.precio,
					t.cantidad,
					t.importe
				FROM
					{$PosTransTable} t
					JOIN inv_ta_almacenes a ON (t.es = a.ch_almacen)
					JOIN pos_cfg p ON (t.caja = p.pos)
				WHERE
					t.dia BETWEEN '{$BeginDate}' AND '{$EndDate}'
					AND t.td IN ('B','F') AND  t.tm!='A' ;";
        SQLImplode($sql);

        // Facturas de Venta Manuales
        $sql = "	SELECT
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
					AND h.ch_fac_tipodocumento IN ('10','11','20','35');";
        SQLImplode($sql);


        // Facturas de Compra
        $sql = "SELECT 
                                a.ch_sucursal,
                                a.ch_almacen,
                                trim(replace(invc.cpag_num_pago,'|','-')),
                                trim(invc.cpag_serie_pago),
                                invc.cpag_tipo_pago,
                                trim(invc.art_codigo),
                                invc.mov_costounitario,
                                invc.mov_cantidad,
                                invc.mov_costototal
                         FROM cpag_ta_cabecera cp 
                         INNER JOIN inv_ta_compras_devoluciones invc ON 
                                cp.pro_cab_tipdocumento=invc.cpag_tipo_pago AND
                                cp.pro_cab_seriedocumento=invc.cpag_serie_pago AND 
                                cp.pro_cab_numdocumento=invc.cpag_num_pago AND
                                cp.com_cab_numorden=invc.com_num_compra AND 
                                cp.pro_codigo=invc.mov_entidad
                         JOIN inv_ta_almacenes a ON (invc.mov_almacen = a.ch_almacen)
                         WHERE cp.pro_cab_fechaemision BETWEEN '{$BeginDate} 00:00:00' AND '{$EndDate} 23:59:59' ;";
        SQLImplode($sql);
        $contenido = ob_get_contents();
        ob_end_clean();
        $comprimido = gzcompress($contenido);
        echo $comprimido;

        break;

    case "IT": // InvoiceTax
        argRangedCheck();

        // Tickets de Venta
        $sql = "	SELECT
					first(a.ch_sucursal),
					first(a.ch_almacen),
					t.trans,
					first(p.nroserie),
					first(t.td),
					'1',
					sum(importe - igv),
					sum(igv)
				FROM
					{$PosTransTable} t
					JOIN inv_ta_almacenes a ON (t.es = a.ch_almacen)
					JOIN pos_cfg p ON (t.caja = p.pos)
				WHERE
					t.dia BETWEEN '{$BeginDate}' AND '{$EndDate}'
					AND t.td IN ('B','F') AND  t.tm!='A'
				GROUP BY
					t.trans,
					t.caja;";
        SQLImplode($sql);

        // Facturas de Venta Manuales
        $sql = "	SELECT
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
					AND h.ch_fac_tipodocumento IN ('10','11','20','35');";
        SQLImplode($sql);

        // Facturas de Compra
        $sql = "	SELECT 
                                   first(a.ch_sucursal),
                                    first(a.ch_almacen),
                                    trim(replace(inv.cpag_num_pago,'|','-')),
                                    trim(inv.cpag_serie_pago),
                                    trim(inv.cpag_tipo_pago),
                                    0,
                                    sum(inv.mov_costototal/1.18),
                                    sum((inv.mov_costototal*0.18)/1.18)
                                FROM cpag_ta_cabecera cp 
                                INNER JOIN inv_ta_compras_devoluciones inv ON 
                                    cp.pro_cab_tipdocumento=inv.cpag_tipo_pago AND
                                    cp.pro_cab_seriedocumento=inv.cpag_serie_pago AND 
                                    cp.pro_cab_numdocumento=inv.cpag_num_pago AND
                                    cp.com_cab_numorden=inv.com_num_compra AND 
                                    cp.pro_codigo=inv.mov_entidad
                                JOIN inv_ta_almacenes a ON (inv.mov_almacen = a.ch_almacen)
                                WHERE cp.pro_cab_fechaemision BETWEEN '{$BeginDate} 00:00:00' AND '{$EndDate} 23:59:59'
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

        $sql = "	SELECT
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
					h.ch_lado;";

        SQLImplode($sql);

        /* $sql =	"SELECT
          first(a.ch_sucursal),
          first(a.ch_almacen),
          first(m.mov_almaorigen),
          first(m.mov_almadestino),
          CASE
          WHEN first(t.tran_entidad) = 'C' THEN trim(first(c.cli_ruc))
          WHEN first(t.tran_entidad) = 'P' THEN trim(first(p.pro_ruc))
          ELSE 'SELF'
          END,
          trim(m.mov_numero),
          trim(m.tran_codigo),
          '',
          first(tt.fecha),
          first(m.mov_tipdocuref),
          '',
          first(m.mov_docurefe),
          ''
          FROM
          inv_movialma m
          JOIN inv_ta_almacenes a ON (m.mov_almacen = a.ch_almacen)
          JOIN {$PosTransTable} tt ON (a.ch_almacen = tt.es)
          JOIN inv_tipotransa t ON (m.tran_codigo = t.tran_codigo)
          LEFT JOIN int_proveedores p ON (m.mov_entidad = p.pro_codigo)
          LEFT JOIN int_clientes c ON (m.mov_entidad = c.cli_codigo)
          WHERE
          m.mov_fecha BETWEEN '{$BeginDate} 00:00:00' AND '{$EndDate} 23:59:59'
          AND m.tran_codigo IN ('07','08','11','12','14','16','17','18','24','26','27','28','54','55','56','99')
          GROUP BY
          m.mov_numero,
          m.tran_codigo;";
          SQLImplode($sql); */

        $contenido = ob_get_contents();
        ob_end_clean();
        $comprimido = gzcompress($contenido);
        echo $comprimido;

        break;

    case "MD": // MovementDetail
        argRangedCheck();

        $sql = "	SELECT
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
					d.nu_importe
				FROM
					val_ta_cabecera h
					JOIN val_ta_detalle d USING (ch_sucursal, dt_fecha, ch_documento)
					JOIN inv_ta_almacenes a ON (h.ch_sucursal = a.ch_almacen)
					LEFT JOIN int_clientes c ON (h.ch_cliente = c.cli_codigo)
				WHERE
					h.dt_fecha BETWEEN '{$BeginDate}' AND '{$EndDate}';";
        SQLImplode($sql);

        /* $sql =	"	SELECT
          a.ch_sucursal,
          a.ch_almacen,
          m.mov_almaorigen,
          m.mov_almadestino,
          CASE
          WHEN t.tran_entidad = 'C' THEN trim(c.cli_ruc)
          WHEN t.tran_entidad = 'P' THEN trim(p.pro_ruc)
          ELSE 'SELF'
          END,
          trim(m.mov_numero),
          trim(m.tran_codigo),
          '',
          m.art_codigo,
          m.mov_costounitario,
          CASE
          WHEN t.tran_naturaleza IN ('1','2') THEN m.mov_cantidad
          ELSE (m.mov_cantidad * -1)
          END,
          m.mov_costototal
          FROM
          inv_movialma m
          JOIN inv_ta_almacenes a ON (m.mov_almacen = a.ch_almacen)
          JOIN inv_tipotransa t ON (m.tran_codigo = t.tran_codigo)
          LEFT JOIN int_proveedores p ON (m.mov_entidad = p.pro_codigo)
          LEFT JOIN int_clientes c ON (m.mov_entidad = c.cli_codigo)
          WHERE
          m.mov_fecha BETWEEN '{$BeginDate} 00:00:00' AND '{$EndDate} 23:59:59'
          AND m.tran_codigo IN ('07','08','11','12','14','16','17','18','24','26','27','28','54','55','56','99');";
          SQLImplode($sql); */
        $contenido = ob_get_contents();
        ob_end_clean();
        $comprimido = gzcompress($contenido);
        echo $comprimido;

        break;

    /* case "FT":	// Totalizer
      die("ERR_NOT_IMPLEMENTED");
      break; */

    case "BI": // Business Partner Information
        argKeyCheck();

        $sql = "	(
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
					1;";
        SQLImplode($sql);

        $contenido = ob_get_contents();
        ob_end_clean();
        $comprimido = gzcompress($contenido);
        echo $comprimido;

        break;

    case "AFERI": // Business Partner Information
       argRangedCheck();

        $sql = "	
			
            SELECT last(venta_tickes.tickes_refe),venta_tickes.registro,venta_tickes.trans_ext FROM 
            (
                    SELECT 
                    (p.trans||'-'||p.caja) as tickes_refe,p.trans,extorno.trans as trans_ext,extorno.registro,extorno.trans1,p.fecha
                    FROM pos_trans201412 p
                    INNER JOIN 
                    (
                    SELECT 
                    (dia|| caja || td ||turno ||codigo ||tipo || pump || fpago ||  abs(cantidad) ||abs(precio)|| abs(igv) || abs(importe) ||ruc) as registro,
                    fecha,trans||'-'||caja as trans,
                    trans as trans1
                    FROM pos_trans201412  where tm='A' AND  dia  BETWEEN '{$BeginDate}' AND '{$EndDate}'
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

        $sql = "	SELECT
					trim(a.art_codigo),
					a.art_descripcion
				FROM
					int_articulos a
				WHERE
					a.art_codigo = '{$CxSearchKey}' and art_estado='0' ;";
        SQLImplode($sql);
        $contenido = ob_get_contents();
        ob_end_clean();
        $comprimido = gzcompress($contenido);
        echo $comprimido;
        break;

    case "PC":
        argRangedCheck();

        $sql = "	SELECT
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
					4 ASC;";
        SQLImplode($sql);
        $contenido = ob_get_contents();
        ob_end_clean();
        $comprimido = gzcompress($contenido);
        echo $comprimido;
        break;

    case "FT": //f_totalizer
        argRangedCheck();

        $sql = "	SELECT
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
					c.manguera;";
        SQLImplode($sql);
        $contenido = ob_get_contents();
        ob_end_clean();
        $comprimido = gzcompress($contenido);
        echo $comprimido;
        break;

    case "ZC": // c_sale_shift
        argRangedCheck();

        $sql = "	SELECT
					p.par_valor,
					pos.dt_posz_fecha_sistema,
					pos.nu_posz_z_numero,
					pos.nu_posz_tran_inicial,
					pos.nu_posz_tran_final,
					pos.nu_posz_b_transas,
					pos.nu_posz_b_total,
					pos.nu_posz_b_impuesto,
					pos.nu_posz_f_transas,
					pos.nu_posz_f_total,
					pos.nu_posz_f_impuesto,
					pos.nu_posz_t_transas,
					pos.nu_posz_t_total,
					pos.nu_posz_t_impuesto,
					pos.nu_posz_z_serie,
					pos.dt_posz_fecha_cierre,
					pos.ch_posz_pos
				FROM
					pos_contometros c
					LEFT JOIN int_parametros p ON (p.par_nombre = 'codes'),
					pos_z_cierres pos
				WHERE
					pos.dt_posz_fecha_sistema BETWEEN '{$BeginDate}' AND '{$EndDate}'
				GROUP BY
					p.par_valor,
					pos.dt_posz_fecha_sistema,
					pos.nu_posz_z_numero,
					pos.nu_posz_tran_inicial,
					pos.nu_posz_tran_final,
					pos.nu_posz_b_transas,
					pos.nu_posz_b_total,
					pos.nu_posz_b_impuesto,
					pos.nu_posz_f_transas,
					pos.nu_posz_f_total,
					pos.nu_posz_f_impuesto,
					pos.nu_posz_t_transas,
					pos.nu_posz_t_total,
					pos.nu_posz_t_impuesto,
					pos.nu_posz_z_serie,
					pos.dt_posz_fecha_cierre,
					pos.ch_posz_pos
				ORDER BY
					3 ASC;";
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

    default:
        die("ERR_INVALID_MOD");
}
?>

