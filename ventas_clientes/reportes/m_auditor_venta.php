<?php
class AuditorVentaModel extends Model {
    function venta_combustible_contrometros($ano, $mes, $estacion) {
        global $sqlca;

        $query_stacion = ($estacion == "TODAS") ? '' : " AND ch_sucursal='$estacion'";
        $query_stacion2 = ($estacion == "TODAS") ? '' : " AND es='$estacion'";
        $fecha_busqueda = $ano . "-" . $mes;

        $sql = "
             SELECT c.*,a.importe_afericon  FROM (
                    SELECT 
                    ch_sucursal,
                    dt_fechaparte ,
                    SUM(nu_ventavalor) as importe_valor,
                    SUM( nu_descuentos) as importe_descuento
                    FROM  comb_ta_contometros 
                    WHERE  to_char(dt_fechaparte,'YYYY-MM')='$fecha_busqueda'
                    $query_stacion
                    GROUP BY ch_sucursal,dt_fechaparte 
                    ORDER BY ch_sucursal,dt_fechaparte
                ) c
                LEFT JOIN
                (SELECT 
                        es,
                        dia,
                        sum(importe) importe_afericon 
                        FROM pos_ta_afericiones p
                        WHERE to_char(dia,'YYYY-MM')='$fecha_busqueda'
                        $query_stacion2
                        GROUP BY es,dia
                        ORDER BY es,dia) a
                ON c.ch_sucursal=a.es and c.dt_fechaparte=a.dia
                            ;                  
                
                ";
//        echo $sql;
        if ($sqlca->query($sql) < 0)
            return -1;

        $resultado = Array();

        for ($i = 0; $i < $sqlca->numrows(); $i++) {
            $a = $sqlca->fetchRow();

            $mov_almacen = trim($a['ch_sucursal']);
            $dt_fechaparte = trim($a['dt_fechaparte']);
            $importe_valor = $a['importe_valor'];

            $importe_descuento = $a['importe_descuento'];
            $importe_afericon = $a['importe_afericon'];



            $resultado[$mov_almacen][$dt_fechaparte]['importe_combustible'] = $importe_valor;
            $resultado[$mov_almacen][$dt_fechaparte]['importe_descuento'] = $importe_descuento;
            $resultado[$mov_almacen][$dt_fechaparte]['importe_afericon'] = $importe_afericon;
        }

        return $resultado;
    }

    function venta_combustible_tickes($ano, $mes, $estacion) {
        global $sqlca;

        $query_stacion = ($estacion == "TODAS") ? '' : " AND es='" . $estacion . "'";
        $query_factura = ($estacion == "TODAS") ? '' : " AND ch_almacen='" . $estacion . "'";
        $pos_transAAAAMM = $ano . "" . $mes;
        $date = $mes."-".$ano;

        $sql = "
SELECT 
 b.es,
 b.dia,
 b.monto AS monto_boleta,
 f.monto AS monto_facturas,
 n.monto AS monto_nota_despachos,
 a.monto AS monto_afericiones,
 des_ext.descuento_extornos AS monto_descuento_extornos,
 vm.monto AS monto_vales_manuales,
 dm.monto AS monto_documento_manuales
FROM
			(
                    SELECT es,to_char(dia,'YYYY-MM-DD') as dia,SUM(importe) as monto FROM pos_trans$pos_transAAAAMM 
                    WHERE td in('B') and tipo='C'
                    $query_stacion
                    GROUP BY es,dia
                    ORDER BY es,dia
                    ) b
                LEFT JOIN 
                (
                    SELECT es,to_char(dia,'YYYY-MM-DD') as dia,SUM(importe) as monto FROM pos_trans$pos_transAAAAMM 
                    WHERE td in('F') and tipo='C'
                    $query_stacion
                    GROUP BY es,dia
                    ORDER BY es,dia
                ) f
                ON (b.dia=f.dia and b.es=f.es)
                LEFT JOIN
                (
                    SELECT es,to_char(dia,'YYYY-MM-DD') as dia,SUM(importe) as monto FROM pos_trans$pos_transAAAAMM
                    WHERE td in('N') and tipo='C'
                    $query_stacion
                    GROUP BY es,dia
                    ORDER BY es,dia
                ) n
                ON (b.dia=n.dia and b.es=n.es)
                LEFT JOIN
                (
                    SELECT es,to_char(dia,'YYYY-MM-DD') as dia,SUM(importe) as monto FROM pos_trans$pos_transAAAAMM
                    WHERE td in('A') and tipo='C'
                    $query_stacion
                    GROUP BY es,dia
                    ORDER BY es,dia
                ) a
                ON (b.dia=a.dia and b.es=a.es)
                LEFT JOIN
                (
                SELECT es,to_char(dia,'YYYY-MM-DD') as dia,SUM(importe) as descuento_extornos FROM pos_trans$pos_transAAAAMM
                WHERE tm in('A') and tipo='C' AND grupo='D'
                $query_stacion
                GROUP BY es,dia
                ORDER BY es,dia
                ) des_ext
                ON (b.dia=des_ext.dia and b.es=des_ext.es)

    LEFT JOIN(
		SELECT
            VC.ch_sucursal AS es,
            TO_CHAR(VC.dt_fecha,'YYYY-MM-DD') AS dia,
            SUM(VC.nu_importe) AS monto
		FROM
			val_ta_cabecera VC
            LEFT JOIN val_ta_detalle VD ON (VC.ch_documento = VD.ch_documento AND VC.dt_fecha = VD.dt_fecha AND VC.ch_sucursal = VD.ch_sucursal)
        WHERE
			TO_CHAR(VC.dt_fecha,'MM-YYYY') = '$date'
			AND VD.ch_articulo IN ('11620301','11620302','11620303','11620304','11620305','11620307')
            AND VC.ch_documento NOT IN (
            SELECT
     			caja||'-'||trans::VARCHAR id
			FROM
                pos_trans" . $pos_transAAAAMM . "
			WHERE
				td = 'N'
				AND tipo = 'C'
			GROUP BY
				id)
			AND VC.ch_documento NOT IN (
            SELECT
				trans::VARCHAR id
			FROM
                pos_trans" . $pos_transAAAAMM . "
			WHERE
				td = 'N'
				AND tipo = 'C'
			GROUP BY
				id)
            AND VC.ch_documento NOT IN (
            SELECT
                '00-'||caja||'-'||trans::VARCHAR id
            FROM
                pos_trans" . $pos_transAAAAMM . "
            WHERE
                td = 'N'
                AND tipo = 'C'
            GROUP BY
                id)
            AND VC.ch_documento NOT IN (
            SELECT
                trans::VARCHAR id
            FROM
                pos_trans" . $pos_transAAAAMM . "
            WHERE
                td = 'N'
                AND tipo = 'C'
            GROUP BY
                id)
            AND VC.ch_documento NOT IN (
            SELECT
                '10-'||caja||'-'||trans::VARCHAR id
            FROM
                pos_trans" . $pos_transAAAAMM . "
            WHERE
                td = 'N'
                AND tipo = 'C'
            GROUP BY
                id)
		GROUP BY
			es,
			dia
		ORDER BY
			es,
			dia
	) vm ON (b.dia = vm.dia AND b.es = vm.es)
    LEFT JOIN(
		SELECT
			ch_almacen AS es,
			TO_CHAR(dt_fac_fecha,'YYYY-MM-DD') AS dia,
			SUM(nu_fac_valortotal) AS monto
		FROM
			fac_ta_factura_cabecera
		WHERE
			TO_CHAR(dt_fac_fecha,'MM-YYYY')	= '$date'
			AND ch_fac_cd_impuesto3		= 'S'
			$query_factura
		GROUP BY
			es,
			dia
		ORDER BY
			es,
			dia
		) dm ON (b.dia = dm.dia AND b.es = dm.es)
";

		//echo "TICKETS: \n".$sql;

        	if ($sqlca->query($sql) < 0)
            		return -1;

        	$resultado = Array();

        	for ($i = 0; $i < $sqlca->numrows(); $i++) {

		    	$a = $sqlca->fetchRow();

		    	$mov_almacen			= trim($a['es']);
		    	$dt_fechaparte			= trim($a['dia']);
		    	$monto_boleta			= $a['monto_boleta'];
		    	$monto_facturas			= $a['monto_facturas'];
		    	$monto_nota_despachos		= $a['monto_nota_despachos'];
		    	$monto_afericiones		= $a['monto_afericiones'];
		    	$monto_descuento_extornos	= $a['monto_descuento_extornos'];
		    	$monto_vales_manuales		= $a['monto_vales_manuales'];
		    	$monto_documento_manuales	= $a['monto_documento_manuales'];

		    	$resultado[$mov_almacen][$dt_fechaparte]['monto_boleta']		= $monto_boleta;
		    	$resultado[$mov_almacen][$dt_fechaparte]['monto_facturas']		= $monto_facturas;
		    	$resultado[$mov_almacen][$dt_fechaparte]['monto_nota_despachos']	= $monto_nota_despachos;
		    	$resultado[$mov_almacen][$dt_fechaparte]['monto_afericiones']		= $monto_afericiones;
		    	$resultado[$mov_almacen][$dt_fechaparte]['monto_descuento_extornos']	= $monto_descuento_extornos;
		    	$resultado[$mov_almacen][$dt_fechaparte]['monto_vales_manuales']	= $monto_vales_manuales;
		    	$resultado[$mov_almacen][$dt_fechaparte]['monto_documento_manuales']	= $monto_documento_manuales;
	
        	}

        	return $resultado;

    	}

    function venta_market_tickes($ano, $mes, $estacion) {
        global $sqlca;

        $query_stacion = ($estacion == "TODAS") ? '' : " AND  es='$estacion'";
        $pos_transAAAAMM = $ano . "" . $mes;

        $sql = "
               SELECT 
               b.es,
               b.dia,
               b.monto as monto_boleta,
               f.monto  as monto_facturas ,
               n.monto  as monto_nota_despachos
               
               FROM (
                    SELECT es,to_char(dia,'YYYY-MM-DD') as dia,SUM(importe) as monto FROM pos_trans$pos_transAAAAMM 
                    WHERE td in('B') and tipo='M'
                    $query_stacion
                    GROUP BY es,dia
                    ORDER BY es,dia
                    ) b
                LEFT JOIN 
                (
                    SELECT es,to_char(dia,'YYYY-MM-DD') as dia,SUM(importe) as monto FROM pos_trans$pos_transAAAAMM 
                    WHERE td in('F') and tipo='M'
                    $query_stacion
                    GROUP BY es,dia
                    ORDER BY es,dia
                ) f
                ON (b.dia=f.dia and b.es=f.es)
                LEFT JOIN
                (
                    SELECT es,to_char(dia,'YYYY-MM-DD') as dia,SUM(importe) as monto FROM pos_trans$pos_transAAAAMM
                    WHERE td in('N') and tipo='M'
                    $query_stacion
                    GROUP BY es,dia
                    ORDER BY es,dia
                ) n
                ON (b.dia=n.dia and b.es=n.es)
                

                
                ";
        //echo $sql;
        if ($sqlca->query($sql) < 0)
            return -1;

        $resultado = Array();

        for ($i = 0; $i < $sqlca->numrows(); $i++) {
            $a = $sqlca->fetchRow();

            $mov_almacen = trim($a['es']);
            $dt_fechaparte = trim($a['dia']);
            $monto_boleta = $a['monto_boleta'];
            $monto_facturas = $a['monto_facturas'];
            $monto_nota_despachos = $a['monto_nota_despachos'];




            $resultado[$mov_almacen][$dt_fechaparte]['monto_boleta'] = $monto_boleta;
            $resultado[$mov_almacen][$dt_fechaparte]['monto_facturas'] = $monto_facturas;
            $resultado[$mov_almacen][$dt_fechaparte]['monto_nota_despachos'] = $monto_nota_despachos;
        }

        return $resultado;
    }

	function venta_refleja_registro_venta_detallado($ano, $mes, $estacion) {
        	global $sqlca;

		$query_stacion = ($estacion == "TODAS") ? '' : " AND  es='$estacion'";
		$pos_transAAAAMM = $ano . "" . $mes;

		$sql="
			SELECT 
				es,
				to_char(dia,'YYYY-MM-DD') as dia,
				SUM(importe) as monto_registro_detallado
		        FROM
				pos_trans$pos_transAAAAMM
		        WHERE
				td in('B','F') 
			        $query_stacion
		        GROUP BY
				es,
				dia
		        ORDER BY
				es,
				dia;
		        ";

		//echo $sql;

		if ($sqlca->query($sql) < 0)
		    return -1;

		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {

			$a = $sqlca->fetchRow();

			$mov_almacen = trim($a['es']);
			$dt_fechaparte = trim($a['dia']);
			$monto_registro_detallado = $a['monto_registro_detallado'];
			$resultado[$mov_almacen][$dt_fechaparte]['monto_registro_detallado'] = $monto_registro_detallado;

		}

		return $resultado;

	}

    function venta_vales_tickes($ano, $mes, $estacion) {
        global $sqlca;

        $query_stacion = ($estacion == "TODAS") ? '' : " AND  es='$estacion'";
        $AAAAMM = $ano . "-" . $mes;

        $sql = "
              
                SELECT 
                        val.ch_sucursal,
                        val.dt_fecha,
                        val.importe,
                        val_sin_liq.importe_sin_liquidar ,
                        n_e.nu_importe_efectivo,
                        importe_liq.nu_importe_liquidado

                         FROM (SELECT 
                         ch_sucursal,
                         dt_fecha,sum(nu_importe) as importe
                         FROM val_ta_cabecera 
                        WHERE to_char(dt_fecha,'YYYY-MM')='$AAAAMM'
                        GROUP BY ch_sucursal,dt_fecha 
                        ORDER BY ch_sucursal,dt_fecha ) val
                        LEFT JOIN 
                        (SELECT 
                        ch_sucursal,
                        dt_fecha,
                        sum(nu_importe) as importe_sin_liquidar  FROM val_ta_cabecera 
                        WHERE to_char(dt_fecha,'YYYY-MM')='$AAAAMM'
                        AND   (ch_liquidacion is null OR ch_liquidacion='')
                        GROUP BY ch_sucursal,dt_fecha 
                        ORDER BY ch_sucursal,dt_fecha ) val_sin_liq
                        ON 
                        (val.ch_sucursal=val_sin_liq.ch_sucursal AND  val.dt_fecha=val_sin_liq.dt_fecha)
                        LEFT JOIN (
                        SELECT
                        v.ch_sucursal,
                        v.dt_fecha,
                        SUM( v.nu_importe) AS nu_importe_efectivo
                        FROM
                        val_ta_cabecera v INNER JOIN int_clientes c on (v.ch_cliente = c.cli_codigo)
                        WHERE
                        to_char(dt_fecha,'YYYY-MM')='$AAAAMM'
                        AND c.cli_ndespacho_efectivo = 1 
                        GROUP BY v.ch_sucursal,dt_fecha 
                        ORDER BY v.ch_sucursal,dt_fecha 
                        ) n_e 
                        ON (val.ch_sucursal=n_e.ch_sucursal AND  val.dt_fecha=n_e.dt_fecha)
                        
LEFT JOIN (
SELECT
v.ch_sucursal,
v.dt_fecha,
SUM( v.nu_importe) AS nu_importe_liquidado
FROM
val_ta_cabecera v 
WHERE
to_char(dt_fecha,'YYYY-MM')='$AAAAMM'
AND trim(v.ch_liquidacion)='LIQ'
GROUP BY v.ch_sucursal,dt_fecha 
ORDER BY v.ch_sucursal,dt_fecha 
) importe_liq
ON (val.ch_sucursal=importe_liq.ch_sucursal AND  val.dt_fecha=importe_liq.dt_fecha)
                

                
                ";
        //echo $sql;
        if ($sqlca->query($sql) < 0)
            return -1;

        $resultado = Array();

        for ($i = 0; $i < $sqlca->numrows(); $i++) {
            $a = $sqlca->fetchRow();

            $mov_almacen = trim($a['ch_sucursal']);
            $dt_fechaparte = trim($a['dt_fecha']);
            $importe = $a['importe'];
            $importe_sin_liquidar = $a['importe_sin_liquidar'];
            $nu_importe_efectivo = $a['nu_importe_efectivo'];
            $nu_importe_liquidado = $a['nu_importe_liquidado'];

            $resultado[$mov_almacen][$dt_fechaparte]['importe'] = $importe;
            $resultado[$mov_almacen][$dt_fechaparte]['importe_sin_liquidar'] = $importe_sin_liquidar;
            $resultado[$mov_almacen][$dt_fechaparte]['nu_importe_efectivo'] = $nu_importe_efectivo;
            $resultado[$mov_almacen][$dt_fechaparte]['nu_importe_liquidado'] = $nu_importe_liquidado;
        }

        return $resultado;
    }

    function venta_facturadas_liquidadas_anticipadas_normales($ano, $mes, $estacion) {
        global $sqlca;

        $query_stacion = ($estacion == "TODAS") ? '' : " AND  es='$estacion'";
        $AAAAMM = $ano . "-" . $mes;
        $posAAAAMM = $ano . "" . $mes;

        $sql = "
              SELECT * FROM (
              
SELECT
(
case when (es is null) then
(select ch_sucursal from int_ta_sucursales limit 1)
else
es
end
) as es,
fecha
 FROM(select es,to_char(pos.da_fecha,'YYYY-MM-DD') as fecha from (SELECT es,
to_char(dia,'YYYY-MM-DD')::TEXT as fecha 
FROM pos_trans$posAAAAMM 
GROUP BY  es,dia
ORDER BY es,dia) as d
RIGHT JOIN pos_aprosys pos on d.fecha=pos.da_fecha::TEXT  WHERE to_char(da_fecha,'YYYY-MM')='$AAAAMM'
) as t
                        
                        ) p
                        LEFT JOIN 
                        (
                        SELECT 
                        ch_almacen,
                        dt_fac_fecha::TEXT,
                        SUM(nu_fac_valortotal) as importe_liquidada
                        FROM fac_ta_factura_cabecera  
                        WHERE 
                        ch_fac_tipodocumento in('10','35')  
                        AND  (ch_liquidacion is not  null AND length(trim(ch_liquidacion))=10)
                        AND (ch_fac_anticipo is null OR length(trim(ch_fac_anticipo))=0  OR trim(ch_fac_anticipo) in ('N'))
                        AND to_char(dt_fac_fecha,'YYYY-MM')='$AAAAMM' 
                        GROUP BY ch_almacen, dt_fac_fecha
                        ORDER BY ch_almacen, dt_fac_fecha
                        ) liq
                        ON  (p.es=liq.ch_almacen AND p.fecha=liq.dt_fac_fecha)
                        LEFT JOIN 
                        (SELECT 
                        ch_almacen,
                        dt_fac_fecha::TEXT,
                         SUM(nu_fac_valortotal) as importe_anticipos
                        FROM fac_ta_factura_cabecera  
                        where ch_fac_tipodocumento in('10','35') and  ch_fac_anticipo='S'
                        AND to_char(dt_fac_fecha,'YYYY-MM')='$AAAAMM'
                        GROUP BY ch_almacen, dt_fac_fecha
                        ORDER BY ch_almacen, dt_fac_fecha
                        ) anticipos
                        ON  (p.es=anticipos.ch_almacen AND p.fecha=anticipos.dt_fac_fecha)
                        LEFT JOIN
                        
                        (SELECT 
                        ch_almacen,
                        dt_fac_fecha::TEXT,
                        SUM(nu_fac_valortotal) as monto_normal
                        FROM fac_ta_factura_cabecera  
                        WHERE 
                        ch_fac_tipodocumento in('10','35')  
                        AND  (ch_liquidacion is null OR length(trim(ch_liquidacion))=0)
                        AND (ch_fac_anticipo is null OR length(trim(ch_fac_anticipo))=0 OR trim(ch_fac_anticipo) in ('N'))
                        AND to_char(dt_fac_fecha,'YYYY-MM')='$AAAAMM' 
                        GROUP BY ch_almacen, dt_fac_fecha
                        ORDER BY ch_almacen, dt_fac_fecha
                        ) manuales
                        ON  (p.es=manuales.ch_almacen AND p.fecha=manuales.dt_fac_fecha)
                        LEFT JOIN
                     (SELECT 
                        ch_almacen,
                        dt_fac_fecha::TEXT,
                        SUM(nu_fac_valortotal) as monto_normal_nc
                        FROM fac_ta_factura_cabecera  
                        WHERE 
                        ch_fac_tipodocumento in('20')  
                        AND  (ch_liquidacion is null OR length(trim(ch_liquidacion))=0)
                        AND (ch_fac_anticipo is null OR length(trim(ch_fac_anticipo))=0 OR trim(ch_fac_anticipo) in ('N'))
                        AND to_char(dt_fac_fecha,'YYYY-MM')='$AAAAMM' 
                        GROUP BY ch_almacen, dt_fac_fecha
                        ORDER BY ch_almacen, dt_fac_fecha
                        ) manuales_nc
                        ON  (p.es=manuales_nc.ch_almacen AND p.fecha=manuales_nc.dt_fac_fecha)
                        




                ";
        //echo $sql;
        if ($sqlca->query($sql) < 0)
            return -1;

        $resultado = Array();

        for ($i = 0; $i < $sqlca->numrows(); $i++) {
            $a = $sqlca->fetchRow();

            $mov_almacen = trim($a['es']);
            $dt_fechaparte = trim($a['fecha']);
            $importe_liquidada = $a['importe_liquidada'];
            $importe_anticipos = $a['importe_anticipos'];
            $monto_normal = $a['monto_normal'];
            $monto_normal_nc = $a['monto_normal_nc'];

            $resultado[$mov_almacen][$dt_fechaparte]['importe_liquidada'] = $importe_liquidada;
            $resultado[$mov_almacen][$dt_fechaparte]['importe_anticipos'] = $importe_anticipos;
            $resultado[$mov_almacen][$dt_fechaparte]['monto_normal'] = $monto_normal;
            $resultado[$mov_almacen][$dt_fechaparte]['monto_normal_nc'] = $monto_normal_nc;
        }

        return $resultado;
    }

    function ventas_vales_facturadas($fecha_factura, $fecha_filtro) {
        global $sqlca;



        $sql = "
              SELECT 
                    dt_fecha,
                    ch_numeval,
                    ch_fac_tipodocumento,
                    ch_fac_seriedocumento,
                    ch_fac_numerodocumento,
                    ch_liquidacion,
                    fecha_liquidacion,
                    ch_cliente,
                    nu_fac_valortotal
            FROM val_ta_complemento_documento 
            WHERE 
            ch_liquidacion in(
            SELECT 
            ch_liquidacion
            FROM fac_ta_factura_cabecera  
            WHERE 
            ch_fac_tipodocumento in('10','35')  
            AND to_char(dt_fac_fecha,'YYYY-MM')='$fecha_factura' 
            AND  (ch_liquidacion is not  null AND length(trim(ch_liquidacion))=10)
            )
            AND fecha_liquidacion in('$fecha_filtro')

            ORDER BY dt_fecha asc
                    ;



                ";

        if ($sqlca->query($sql) < 0)
            return -1;

        $resultado = Array();

        for ($i = 0; $i < $sqlca->numrows(); $i++) {
            $a = $sqlca->fetchRow();

            $resultado[] = $a;
        }

        return $resultado;
    }

    function diferencia_de_vales($fecha_factura, $fecha_filtro) {
        global $sqlca;

$fecha_filtro=  str_replace("-", "", $fecha_filtro);

        $sql = "
            SELECT 
                 to_char(fecha,'YYYY-MM-DD') as fecha,
                 trans as vale,
                 sum(importe) as importe 
            FROM pos_trans$fecha_filtro  where td in('N') 
            AND to_char(dia,'YYYY-MM-DD')='$fecha_factura' 
            GROUP BY fecha,vale
            ORDER BY vale,fecha
;      
";
       // echo $sql;
        $resultado_pos_trans = array();

        if ($sqlca->query($sql) < 0) {
            $resultado_pos_trans[0] = 0;
        }

        for ($i = 0; $i < $sqlca->numrows(); $i++) {
            $a = $sqlca->fetchRow();

            $resultado_pos_trans[] = $a;
        }
        //---------------------------
        $sql = "
      
        SELECT 
                dt_fecha as fecha,
                ch_documento as vale,
                sum(nu_importe) as importe
        FROM val_ta_cabecera  where dt_fecha in('$fecha_factura') 
        GROUP BY  dt_fecha,ch_documento
        ORDER BY  ch_documento,dt_fecha
        ;      
";

        $resultado_valta = Array();
        if ($sqlca->query($sql) < 0) {
            $resultado_valta[0] = 0;
        }


        for ($i = 0; $i < $sqlca->numrows(); $i++) {
            $a = $sqlca->fetchRow();

            $resultado_valta[] = $a;
        }
        
          //---------------------------
        $sql = "
      
        SELECT 
            dt_fecha as fecha,
            ch_numeval as vale,
            sum(nu_fac_valortotal) as  importe
        FROM val_ta_complemento_documento  
        where dt_fecha in('$fecha_factura')
        GROUP BY  dt_fecha,ch_numeval
        ORDER BY  ch_numeval,dt_fecha;
";

        $resultado_valta_complemento = Array();
        if ($sqlca->query($sql) < 0) {
            $resultado_valta_complemento[0] = 0;
        }


        for ($i = 0; $i < $sqlca->numrows(); $i++) {
            $a = $sqlca->fetchRow();

            $resultado_valta_complemento[] = $a;
        }

        return array($resultado_pos_trans,$resultado_valta,$resultado_valta_complemento);;
    }

    function getdescripcion_linea() {

        global $sqlca;



        $sql = "SELECT 
                tab_elemento,tab_descripcion 
                FROM int_tabla_general 
                WHERE tab_tabla ='20' ;";



        if ($sqlca->query($sql) < 0)
            return -1;

        $resultado = Array();

        for ($i = 0; $i < $sqlca->numrows(); $i++) {
            $a = $sqlca->fetchRow();
            $tab_elemento = trim($a['tab_elemento']);
            $resultado[$tab_elemento]['tab_descripcion'] = trim($a['tab_descripcion']);
        }

        return $resultado;
    }

    function obtenerEstaciones() {
        global $sqlca;

        $sql = "SELECT ch_almacen, ch_nombre_almacen FROM inv_ta_almacenes WHERE ch_clase_almacen='1' ORDER BY ch_almacen;";
        if ($sqlca->query($sql, "_estaciones") < 0)
            return null;

        $resultado = Array();
        for ($i = 0; $i < $sqlca->numrows("_estaciones"); $i++) {
            $array = $sqlca->fetchRow("_estaciones");
            $resultado[$array[0]] = $array[0] . " - " . $array[1];
        }

        $resultado['TODAS'] = "Todas las estaciones";
        return $resultado;
    }

    function obtenerDescripcionAlmacen($codigo) {
        global $sqlca;

        $sql = "SELECT trim(ch_nombre_almacen) FROM inv_ta_almacenes WHERE ch_almacen='$codigo';";
        if ($sqlca->query($sql, "_almacenes") < 0)
            return null;

        $a = $sqlca->fetchRow("_almacenes");
        return $a[0];
    }

    function obtenerTiposFormularios() {
        global $sqlca;

        $sql = "SELECT  trim(tran_codigo) as tran_codigo,trim(format_sunat) as tran_descripcion FROM inv_tipotransa ORDER BY tran_codigo;";
        if ($sqlca->query($sql, "_formularios") < 0)
            return null;

        $resultado = Array();
        for ($i = 0; $i < $sqlca->numrows("_formularios"); $i++) {
            $array = $sqlca->fetchRow("_formularios");
            $resultado[$array[0]] = $array[1];
            // $resultado[$array[0]] = $array[0] . " - " . $array[1];
        }

        $resultado['TODOS'] = "Todos los tipos";
        return $resultado;
    }

    function getUltimoDiaMes($elAnio, $elMes) {

        return date("d", (mktime(0, 0, 0, $elMes + 1, 1, $elAnio) - 1));
    }

    function saldoInicial_2_ELIMINAR($ano, $mes, $estacion) {
        global $sqlca;

        if ($mes == 1 || $mes == "01") {
            $mes = 12;
            $ano--;
        } else {
            $mes--;
        }
        $mes = str_pad($mes, 2, '0', STR_PAD_LEFT);
        $query_stacion = ($estacion == "TODAS") ? '' : "AND sa.stk_almacen='$estacion'";
        $sql = "SELECT
			    	sa.stk_almacen,
			    	sa.art_codigo,
			    	sa.stk_stock" . $mes . ",
			    	sa.stk_costo" . $mes . "
			FROM
			    	inv_saldoalma sa 
			    	LEFT JOIN int_articulos art ON (sa.art_codigo=art.art_codigo)  
			WHERE
				sa.stk_periodo='$ano' 
                                $query_stacion
                        ORDER BY
			    	sa.stk_periodo,
			    	sa.stk_almacen,
			    	sa.art_codigo;
                                    
                
                ";
        if ($sqlca->query($sql) < 0)
            return -1;

        $resultado = Array();

        for ($i = 0; $i < $sqlca->numrows(); $i++) {
            $a = $sqlca->fetchRow();

            $stk_almacen = $a[0];
            $art_codigo = $a[1];
            $stk_stock = $a[2];
            $stk_costo = $a[3];

            $resultado['almacenes'][$stk_almacen]['codigos'][$art_codigo]['stk_stock'] = $stk_stock;
            $resultado['almacenes'][$stk_almacen]['codigos'][$art_codigo]['stk_costopromedio'] = $stk_costo;
            $resultado['almacenes'][$stk_almacen]['codigos'][$art_codigo]['stk_costounitario'] = $stk_costo;
            $resultado['almacenes'][$stk_almacen]['codigos'][$art_codigo]['stk_costototal'] = $stk_stock * $stk_costo;
        }

        if ($mes == 12) {
            $mes = 1;
            $ano++;
        } else {
            $mes++;
        }


        $query_stacion = ($estacion == "TODAS") ? '' : "AND inv.mov_almacen='" . pg_escape_string($estacion) . "'";
        $sql = "SELECT
					inv.mov_cantidad,
					inv.mov_costototal,
					inv.mov_costopromedio,
					inv.mov_costounitario,
					inv.mov_naturaleza,
					inv.mov_almacen,
					inv.art_codigo
			    	FROM
					inv_movialma inv 
					LEFT JOIN int_articulos art ON (inv.art_codigo=art.art_codigo) 
			   	WHERE
				    	inv.mov_fecha BETWEEN '" . ($ano . "-" . $mes . "-01") . " 00:00:00' AND '" . ($ano . "-" . $mes . "-" . ($dia - 1)) . " 23:59:59'
                                        $query_stacion  
                                ORDER BY
                                        inv.mov_almacen,
                                        inv.art_codigo,
                                        inv.mov_fecha;
";






        if ($sqlca->query($sql) < 0)
            return $resultado;

        for ($i = 0; $i < $sqlca->numrows(); $i++) {
            $a = $sqlca->fetchRow();

            $mov_cantidad = $a[0];
            $mov_costototal = $a[1];
            $mov_costopromedio = $a[2];
            $mov_costounitario = $a[3];
            $mov_naturaleza = $a[4];
            $mov_almacen = $a[5];
            $art_codigo = $a[6];

            if ($mov_naturaleza < 3) {
                $resultado['almacenes'][$mov_almacen]['codigos'][$art_codigo]['stk_stock'] += $mov_cantidad;
                $resultado['almacenes'][$mov_almacen]['codigos'][$art_codigo]['stk_costounitario'] = $mov_costounitario;
            } else {
                $resultado['almacenes'][$mov_almacen]['codigos'][$art_codigo]['stk_stock'] -= $mov_cantidad;
            }
            $resultado['almacenes'][$mov_almacen]['codigos'][$art_codigo]['stk_costounitario'] = $mov_costounitario;
            $resultado['almacenes'][$mov_almacen]['codigos'][$art_codigo]['stk_costopromedio'] = $mov_costopromedio;
            $resultado['almacenes'][$mov_almacen]['codigos'][$art_codigo]['stk_costototal'] = $resultado['almacenes'][$mov_almacen]['codigos'][$art_codigo]['stk_stock'] * $resultado['almacenes'][$mov_almacen]['codigos'][$art_codigo]['stk_costopromedio'];
        }


        return $resultado;
    }

    function verificarDocumentosOficinaEnviadosEBI($arrParams){
        global $sqlca;

        $where_almacen = ($arrParams['sAlmacen'] != 'TODAS' ? "AND ch_almacen='" . $arrParams['sAlmacen'] . "'" : '');
        $dFechaEmision = $arrParams['dFechaEmision'];

        $sql = "
SELECT
 CASE WHEN nu_fac_recargo3='0' THEN COUNT(*) END AS nu_cantidad_registrado,
 CASE WHEN nu_fac_recargo3='1' THEN COUNT(*) END AS nu_cantidad_completado,
 CASE WHEN nu_fac_recargo3='2' THEN COUNT(*) END AS nu_cantidad_anulado,
 CASE WHEN nu_fac_recargo3='4' THEN COUNT(*) END AS nu_cantidad_completado_error,
 CASE WHEN nu_fac_recargo3='6' THEN COUNT(*) END AS nu_cantidad_anulado_error
FROM
 fac_ta_factura_cabecera 
WHERE
 LENGTH(ch_fac_seriedocumento)=4
 " . $where_almacen . "
 AND dt_fac_fecha='" . $dFechaEmision . "'
 AND nu_fac_recargo3 IN('0','1','2','4','6')
GROUP BY
 nu_fac_recargo3;
        ";

        $iStatusSQL = $sqlca->query($sql);
        if ( (int)$iStatusSQL > 0 ) {
            return array(
                'sStatus' => 'success',
                'arrData' => $sqlca->fetchAll(),
            );
        } else if ( $iStatusSQL == 0 ) {
            return array(
                'sStatus' => 'warning',
                'sMessage' => 'Enviados',
            );
        } else {
            return array(
                'sStatus' => 'danger',
                'sMessage' => 'Problemas al obtener registros',
                'sMessageSQL' => $sqlca->get_error(),
                'sql' => $sql,
            );
        }
    }
}

