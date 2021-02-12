<?php
/**
 *Modificado por Nestor Hernandez Loli a motivo de seleccion del almacen 
 */
class DaotModel extends Model {

    function generarDaot($anio, $base, $ruc) {
        global $sqlca;

        $sql = "CREATE TEMPORARY TABLE tmpDaot (ruc text, razsocial text, caja character varying(4), trans character varying(10), dia timestamp without time zone, turno character varying(1), vventa numeric, igv numeric, total numeric );";
        $sqlca->query($sql);

        for ($i = 1; $i <= 12; $i++) {
            $mes = str_pad($i, 2, "0", STR_PAD_LEFT);
            $sql = "INSERT INTO tmpDaot 
					SELECT 
						trim(both from max(p.ruc)) as ruc, 
						max(r.razsocial) as razsocial, 
						p.caja, 
						p.trans, 
						p.dia, 
						p.turno, 
						sum(p.importe-p.igv) as vventa, 
						sum(p.igv) as igv, 
						sum(p.importe) as total 
					FROM 
						pos_trans" . $anio . $mes . " p 
						LEFT JOIN ruc r ON (r.ruc=p.ruc) 
					WHERE 
						p.td='F' AND p.es = '" . $_SESSION["almacen"] . "' 
					GROUP BY 
						p.trans, 
						p.dia, 
						p.turno, 
						p.caja 
					ORDER BY 
						p.dia, 
						p.turno, 
						p.caja, 
						p.trans;";
            $sqlca->query($sql);
        }

        $sql = "INSERT INTO tmpdaot 
			SELECT 
				CASE WHEN c.cli_codigo='9999' THEN trim(both from com.ch_fac_ruc) 
				ELSE trim(both from cli.cli_ruc) 
				END as ruc, 
	
				CASE WHEN c.cli_codigo='9999' THEN com.ch_fac_nombreclie 
				ELSE cli.cli_razsocial 
				END as razsocial, 
				
				c.ch_fac_seriedocumento as caja, 
				c.ch_fac_numerodocumento as trans, 
				c.dt_fac_fecha as dia, 
				null as turno, 
				c.nu_fac_valortotal-c.nu_fac_impuesto1 as vventa, 
				c.nu_fac_impuesto1 as igv, 
				c.nu_fac_valortotal as total 
			FROM 
				fac_ta_factura_cabecera c 
				RIGHT JOIN int_clientes cli ON (cli.cli_codigo=c.cli_codigo) 
				LEFT JOIN fac_ta_factura_complemento com ON (	com.ch_fac_tipodocumento=c.ch_fac_tipodocumento AND 
										com.ch_fac_seriedocumento=c.ch_fac_seriedocumento AND 
										com.ch_fac_numerodocumento=c.ch_fac_numerodocumento AND 
										com.cli_codigo=c.cli_codigo) 
			WHERE 
				c.ch_fac_tipodocumento='10' AND 
				c.dt_fac_fecha>'" . ($anio - 1) . "-12-31' AND 
				c.dt_fac_fecha<'" . ($anio + 1) . "-01-01' AND 
                                c.ch_almacen = '" . $_SESSION["almacen"] . "' AND 
				c.ch_fac_anulado IS NULL;";
				
	echo $sql;
	
      //      file_put_contents("/sistemaweb/consultas.txt", $sql, FILE_APPEND);
        $sqlca->query($sql);

        $sql = "INSERT INTO tmpdaot 
			SELECT 
				CASE WHEN c.cli_codigo='9999' THEN trim(both from com.ch_fac_ruc) 
				ELSE trim(both from cli.cli_ruc) 
				END as ruc,
				
				CASE WHEN c.cli_codigo='9999' THEN com.ch_fac_nombreclie 
				ELSE cli.cli_razsocial 
				END as razsocial, 
	
				c.ch_fac_seriedocumento as caja, 
				c.ch_fac_numerodocumento as trans, 
				c.dt_fac_fecha as dia, 
				null as turno, 
				-c.nu_fac_valortotal+c.nu_fac_impuesto1 as vventa, 
				-c.nu_fac_impuesto1 as igv, 
				-c.nu_fac_valortotal as total 
			FROM 
				fac_ta_factura_cabecera c 
				RIGHT JOIN int_clientes cli ON (cli.cli_codigo=c.cli_codigo) 
				LEFT JOIN fac_ta_factura_complemento com ON (	com.ch_fac_tipodocumento=c.ch_fac_tipodocumento AND 
										com.ch_fac_seriedocumento=c.ch_fac_seriedocumento AND 
										com.ch_fac_numerodocumento=c.ch_fac_numerodocumento AND 
										com.cli_codigo=c.cli_codigo) 
			WHERE 
				c.ch_fac_tipodocumento='20' AND 
				c.dt_fac_fecha>'" . ($anio - 1) . "-12-31' AND 
				c.dt_fac_fecha<'" . ($anio + 1) . "-01-01' AND 
                                 c.ch_almacen = '" . $_SESSION["almacen"] . "' AND 
				c.ch_fac_anulado is null;";
            //file_put_contents("/sistemaweb/consultas.txt", $sql, FILE_APPEND);
        $sqlca->query($sql);

        $sql = "CREATE TEMPORARY TABLE tmpDaotResumen AS 
			SELECT 
				ruc as ruc, max(razsocial) as razsocial, sum(vventa) as vventa, sum(igv) as igv, sum(total) as total 
			FROM 
				tmpDaot 
			GROUP BY 
				ruc 
			ORDER BY 
				ruc;";
        $sqlca->query($sql);

        $sql = "CREATE TEMPORARY TABLE tmpDaotResumenFiltrado AS
			SELECT 
				ruc, razsocial, vventa, igv, total 
			FROM 
				tmpDaotResumen 
			WHERE
				vventa >= " . $base . "
			ORDER BY 
				ruc;";
        
        $sqlca->query($sql);

        $sql = "CREATE TEMPORARY TABLE tmpDaotDetalleRuc AS
			SELECT 
				ruc, razsocial, caja, trans, dia, turno, vventa, igv, total 
			FROM 
				tmpDaot 
			WHERE
				ruc = '" . pg_escape_string($ruc) . "'
			ORDER BY 
				dia ASC;";
        $sqlca->query($sql);

        $sql = "COPY tmpDaot TO '/sistemaweb/ventas_clientes/daot/daot" . $anio . "detalle.csv' WITH DELIMITER ',';";
        $sqlca->query($sql);

        $sql = "COPY tmpDaotDetalleRuc TO '/sistemaweb/ventas_clientes/daot/daot" . $anio . "detalleruc.csv' WITH DELIMITER ',';";
        $sqlca->query($sql);

        $sql = "COPY tmpDaotResumen TO '/sistemaweb/ventas_clientes/daot/daot" . $anio . "resumen.csv' WITH DELIMITER ',';";
        $sqlca->query($sql);

        $sql = "COPY tmpDaotResumenFiltrado TO '/sistemaweb/ventas_clientes/daot/daot" . $anio . "resumenfiltrado.csv' WITH DELIMITER ',';";
        $sqlca->query($sql);
    }

    function buscarDetalladoDaot($base, $ruc) {
        global $sqlca;
        $sql = "SELECT 
				ruc, razsocial, vventa, igv, total 
			FROM 
				tmpDaotResumen 
			WHERE
				vventa >= " . $base . " ";

        if ($ruc != '') {
            $sql .= "AND ruc = '" . $ruc . "' ";
        }

        $sql .= "ORDER BY 
				ruc;";

        if ($sqlca->query($sql) < 0)
            return false;

        $resultado = Array();

        for ($i = 0; $i < $sqlca->numrows(); $i++) {
            $a = $sqlca->fetchRow();

            $resultado[$i]['ruc'] = $a[0];
            $resultado[$i]['razsocial'] = $a[1];
            $resultado[$i]['vventa'] = $a[2];
            $resultado[$i]['igv'] = $a[3];
            $resultado[$i]['total'] = $a[4];
        }

        return $resultado;
    }

    function buscarDaotxRuc() {
        global $sqlca;
        $sql = "SELECT 
				ruc, razsocial, caja, trans, to_char(dia, 'DD/MM/YYYY') as dia, turno, vventa, igv, total 
			FROM 
				tmpDaotDetalleRuc";

        if ($sqlca->query($sql) < 0)
            return false;

        $resultado = Array();

        for ($i = 0; $i < $sqlca->numrows(); $i++) {
            $a = $sqlca->fetchRow();

            $resultado['ruc'][$a[0]]['ruc'] = $a[0];
            $resultado['ruc'][$a[0]]['razsocial'] = $a[1];
            $resultado['ruc'][$a[0]]['documentos'][$i]['caja'] = $a[2];
            $resultado['ruc'][$a[0]]['documentos'][$i]['trans'] = $a[3];
            $resultado['ruc'][$a[0]]['documentos'][$i]['dia'] = $a[4];
            $resultado['ruc'][$a[0]]['documentos'][$i]['turno'] = $a[5];
            $resultado['ruc'][$a[0]]['documentos'][$i]['vventa'] = $a[6];
            $resultado['ruc'][$a[0]]['documentos'][$i]['igv'] = $a[7];
            $resultado['ruc'][$a[0]]['documentos'][$i]['total'] = $a[8];

            $resultado['ruc'][$a[0]]['totales']['vventa'] += $a[6];
            $resultado['ruc'][$a[0]]['totales']['igv'] += $a[7];
            $resultado['ruc'][$a[0]]['totales']['total'] += $a[8];

            $resultado['totales']['vventa'] += $a[6];
            $resultado['totales']['igv'] += $a[7];
            $resultado['totales']['total'] += $a[8];
        }
        return $resultado;
    }

    function cabeceraReporte() {
        global $sqlca;
        $sql = "SELECT
				(SELECT par_valor FROM int_parametros WHERE par_nombre='razsocial')||' '||(SELECT par_valor FROM int_parametros WHERE par_nombre='desces') AS cabecera
			FROM 
				int_parametros
			LIMIT 1";

        if ($sqlca->query($sql) < 0)
            return false;

        $a = $sqlca->fetchRow();

        return $a[0];
    }

}

