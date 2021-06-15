<?php

class KardexVentaActModel extends Model {

    function saldo_inicial_mes_anterior($ano, $mes, $estacion) {
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
                                art.art_codigo,
                                uni.unidad_medida,
                                art.art_descripcion,
			    	sa.stk_almacen,
			    	sa.stk_stock" . $mes . ",
			    	sa.stk_costo" . $mes . "
			FROM
			    	inv_saldoalma sa 
			    	LEFT JOIN int_articulos art ON (sa.art_codigo=art.art_codigo)  
                                LEFT JOIN ( SELECT  tab.tab_elemento,tab_car_03 ||'-'|| tab_car_04  as unidad_medida FROM int_tabla_general tab WHERE tab.tab_tabla='34' ) uni ON uni.tab_elemento=art.art_unidad
			WHERE
				sa.stk_periodo='$ano' 
                                $query_stacion
                        ORDER BY
			    	sa.stk_periodo,
			    	sa.stk_almacen,
			    	sa.art_codigo;
                                    
                
                ";
        echo $sql;
        if ($sqlca->query($sql) < 0)
            return -1;

        $resultado = Array();

        for ($i = 0; $i < $sqlca->numrows(); $i++) {
            $a = $sqlca->fetchRow();

            $stk_almacen = trim($a['stk_almacen']);
            $art_codigo = trim($a['art_codigo']);
            $stk_stock = $a['stk_stock' . $mes];
            $stk_costo = $a['stk_costo' . $mes];

            $resultado['st_inicial'][$stk_almacen][$art_codigo]['stk_stock'] = $stk_stock;
            $resultado['st_inicial'][$stk_almacen][$art_codigo]['stk_costopromedio'] = $stk_costo;
            $resultado['st_inicial'][$stk_almacen][$art_codigo]['stk_costounitario'] = $stk_costo;
            $resultado['st_inicial'][$stk_almacen][$art_codigo]['stk_costototal'] = $stk_stock * $stk_costo;
        }

        return $resultado;
    }

    function saldo_inicial_mes_actual($ano, $mes, $estacion) {

        global $sqlca;


        $mes = str_pad($mes, 2, '0', STR_PAD_LEFT);
        $query_stacion = ($estacion == "TODAS") ? '' : "AND sa.stk_almacen='$estacion'";

        $sql = "SELECT
                                art.art_codigo,
                                uni.unidad_medida,
                                art.art_descripcion,
			    	sa.stk_almacen,
			    	sa.stk_stock" . $mes . ",
			    	sa.stk_costo" . $mes . "
			FROM
			    	inv_saldoalma sa 
			    	LEFT JOIN int_articulos art ON (sa.art_codigo=art.art_codigo)  
                                LEFT JOIN ( SELECT  tab.tab_elemento,tab_car_03 ||'-'|| tab_car_04  as unidad_medida FROM int_tabla_general tab WHERE tab.tab_tabla='34' ) uni ON uni.tab_elemento=art.art_unidad
			WHERE
				sa.stk_periodo='$ano' 
                                $query_stacion
                        ORDER BY
			    	sa.stk_periodo,
			    	sa.stk_almacen,
			    	sa.art_codigo;
                                    
                
                ";
        echo $sql;
        if ($sqlca->query($sql) < 0)
            return -1;

        $resultado = Array();

        for ($i = 0; $i < $sqlca->numrows(); $i++) {
            $a = $sqlca->fetchRow();

            $stk_almacen = trim($a['stk_almacen']);
            $art_codigo = trim($a['art_codigo']);
            $stk_stock = $a['stk_stock' . $mes];
            $stk_costo = $a['stk_costo' . $mes];

            $resultado['st_final'][$stk_almacen][$art_codigo]['stk_stock'] = $stk_stock;
            $resultado['st_final'][$stk_almacen][$art_codigo]['stk_costopromedio'] = $stk_costo;
            $resultado['st_final'][$stk_almacen][$art_codigo]['stk_costounitario'] = $stk_costo;
            $resultado['st_final'][$stk_almacen][$art_codigo]['stk_costototal'] = $stk_stock * $stk_costo;
        }

        return $resultado;
    }

    function ingreso_inventario($ano, $mes, $estacion) {
        global $sqlca;

        $ultimo_dia = KardexVentaActModel::getUltimoDiaMes($ano, $mes);
        $query_stacion = ($estacion == "TODAS") ? '' : " AND inv.mov_almacen='$estacion'";
        $fecha_inicio = $ano . "-" . $mes . "-01 00:00:00";
        $fecha_final = $ano . "-" . $mes . "-" . $ultimo_dia . " 23:59:59";
        $sql = "
                  SELECT  
                        inv.mov_almacen,
                        inv.art_codigo ,
                        sum(inv.mov_cantidad) as  mov_cantidad,
                        
                        --round((sum(inv.mov_costototal)/sum(inv.mov_cantidad)),4) as mov_costounitario,
                        CASE 
                            WHEN ( sum(inv.mov_costototal) = 0 AND sum(inv.mov_cantidad) = 0 ) THEN round(0,4)
                            ELSE round((sum(inv.mov_costototal)/sum(inv.mov_cantidad)),4)
                        END as mov_costounitario,

                        sum(inv.mov_costopromedio) as mov_costopromedio,
                        sum(inv.mov_costototal) as mov_costototal
                FROM inv_movialma  inv
                INNER JOIN inv_tipotransa inv_t ON inv.tran_codigo=inv_t.tran_codigo
                WHERE inv_t.tran_naturaleza  in ('1','2') 
                AND (inv.mov_fecha BETWEEN '$fecha_inicio' AND '$fecha_final') $query_stacion
                GROUP BY 
                        inv.mov_almacen,
                        inv.art_codigo
                        

                ORDER BY inv.art_codigo 
;                  
                
                ";
        echo $sql;
        if ($sqlca->query($sql) < 0)
            return -1;

        $resultado = Array();

        for ($i = 0; $i < $sqlca->numrows(); $i++) {
            $a = $sqlca->fetchRow();

            $mov_almacen = trim($a['mov_almacen']);
            $art_codigo = trim($a['art_codigo']);
            $mov_cantidad = $a['mov_cantidad'];
            $mov_costounitario = $a['mov_costounitario'];
            $mov_costopromedio = $a['mov_costopromedio'];
            $mov_costototal = $a['mov_costototal'];
            $tran_descripcion = $a['tran_descripcion'];

            $resultado[$mov_almacen][$art_codigo]['mov_cantidad'] = $mov_cantidad;
            $resultado[$mov_almacen][$art_codigo]['mov_costounitario'] = $mov_costounitario;
            $resultado[$mov_almacen][$art_codigo]['mov_costopromedio'] = $mov_costopromedio;
            $resultado[$mov_almacen][$art_codigo]['mov_costototal'] = $mov_costototal;
            $resultado[$mov_almacen][$art_codigo]['tran_descripcion'] = $tran_descripcion;
        }

        return $resultado;
    }

    function ingreso_ajuste_inventario($ano, $mes, $estacion) {
        global $sqlca;

        $ultimo_dia = KardexVentaActModel::getUltimoDiaMes($ano, $mes);
        $query_stacion = ($estacion == "TODAS") ? '' : " AND inv.mov_almacen='$estacion'";
        $fecha_inicio = $ano . "-" . $mes . "-01 00:00:00";
        $fecha_final = $ano . "-" . $mes . "-" . $ultimo_dia . " 23:59:59";
        $sql = "
                  SELECT  
                        inv.mov_almacen,
                        inv.art_codigo ,
                        sum(inv.mov_cantidad) as  mov_cantidad
                FROM inv_movialma  inv
                INNER JOIN inv_tipotransa inv_t ON inv.tran_codigo=inv_t.tran_codigo
                WHERE inv_t.tran_naturaleza  in ('1')  AND inv_t.tran_codigo='17'
                AND (inv.mov_fecha BETWEEN '$fecha_inicio' AND '$fecha_final') $query_stacion
                GROUP BY 
                        inv.mov_almacen,
                        inv.art_codigo
                ORDER BY inv.art_codigo 
;                  
                
                ";
        echo "Calculo de la MERMAS" . $sql;
        if ($sqlca->query($sql) < 0)
            return -1;

        $resultado = Array();

        for ($i = 0; $i < $sqlca->numrows(); $i++) {
            $a = $sqlca->fetchRow();

            $mov_almacen = trim($a['mov_almacen']);
            $art_codigo = trim($a['art_codigo']);
            $mov_cantidad = $a['mov_cantidad'];

            $resultado[$mov_almacen][$art_codigo]['mov_cantidad'] = $mov_cantidad;
        }
        return $resultado;
    }

    function ingreso_inventario_detallado($ano, $mes, $estacion) {
        global $sqlca;

        $ultimo_dia = KardexVentaActModel::getUltimoDiaMes($ano, $mes);
        $query_stacion = ($estacion == "TODAS") ? '' : " AND inv.mov_almacen='$estacion'";
        $fecha_inicio = $ano . "-" . $mes . "-01 00:00:00";
        $fecha_final = $ano . "-" . $mes . "-" . $ultimo_dia . " 23:59:59";
        $sql = "
                  SELECT  
                        inv.mov_almacen,
                        inv.art_codigo ,
                        inv.tran_codigo ,
                        sum(inv.mov_cantidad) as  mov_cantidad,
                       
                        --round((sum(inv.mov_costototal)/sum(inv.mov_cantidad)),4) as mov_costounitario,
                        CASE 
                            WHEN ( sum(inv.mov_costototal) = 0 AND sum(inv.mov_cantidad) = 0 ) THEN round(0,4)
                            ELSE round((sum(inv.mov_costototal)/sum(inv.mov_cantidad)),4)
                        END as mov_costounitario,

                        sum(inv.mov_costopromedio) as mov_costopromedio,
                        sum(inv.mov_costototal) as mov_costototal,
                        inv_t.tran_descripcion
                        --inv. mov_costounitario as jj
                FROM inv_movialma  inv
                INNER JOIN inv_tipotransa inv_t ON inv.tran_codigo=inv_t.tran_codigo
                WHERE inv_t.tran_naturaleza  in ('1','2') 
                AND (inv.mov_fecha BETWEEN '$fecha_inicio' AND '$fecha_final') $query_stacion
                GROUP BY 
                        inv.mov_almacen,
                        inv.art_codigo,
                        inv.tran_codigo,
                        inv_t.tran_descripcion
                       

                ORDER BY inv.art_codigo 
;                  
                
                ";
        echo $sql;
        if ($sqlca->query($sql) < 0)
            return -1;

        $resultado = Array();

        for ($i = 0; $i < $sqlca->numrows(); $i++) {
            $a = $sqlca->fetchRow();

            $mov_almacen = trim($a['mov_almacen']);
            $art_codigo = trim($a['art_codigo']);
            $tran_codigo = trim($a['tran_codigo']);
            $mov_cantidad = $a['mov_cantidad'];
            $mov_costounitario = $a['mov_costounitario'];
            $mov_costopromedio = $a['mov_costopromedio'];
            $mov_costototal = $a['mov_costototal'];
            $tran_descripcion = $a['tran_descripcion'];

            $resultado[$mov_almacen][$art_codigo][$tran_codigo]['mov_cantidad'] = $mov_cantidad;
            $resultado[$mov_almacen][$art_codigo][$tran_codigo]['mov_costounitario'] = $mov_costounitario;
            $resultado[$mov_almacen][$art_codigo][$tran_codigo]['mov_costopromedio'] = $mov_costopromedio;
            $resultado[$mov_almacen][$art_codigo][$tran_codigo]['mov_costototal'] = $mov_costototal;
            $resultado[$mov_almacen][$art_codigo][$tran_codigo]['tran_descripcion'] = $tran_descripcion;
        }

        return $resultado;
    }

    function ventas_inventario($ano, $mes, $estacion) {
        global $sqlca;

        $ultimo_dia = KardexVentaActModel::getUltimoDiaMes($ano, $mes);
        $query_stacion = ($estacion == "TODAS") ? '' : " AND inv.mov_almacen='$estacion'";
        $fecha_inicio = $ano . "-" . $mes . "-01 00:00:00";
        $fecha_final = $ano . "-" . $mes . "-" . $ultimo_dia . " 23:59:59";
        $sql = "
                  SELECT  
                        inv.mov_almacen,
                        inv.art_codigo ,
                        sum(inv.mov_cantidad) as  mov_cantidad,
                        
                        --round((sum(inv.mov_costototal)/sum(inv.mov_cantidad)),4) as mov_costounitario,
                        CASE 
                            WHEN ( sum(inv.mov_costototal) = 0 AND sum(inv.mov_cantidad) = 0 ) THEN round(0,4)
                            ELSE round((sum(inv.mov_costototal)/sum(inv.mov_cantidad)),4)
                        END as mov_costounitario,

                        sum(inv.mov_costopromedio) as mov_costopromedio,
                        sum(inv.mov_costototal) as mov_costototal
                FROM inv_movialma  inv
                INNER JOIN inv_tipotransa inv_t ON inv.tran_codigo=inv_t.tran_codigo
                WHERE inv_t.tran_naturaleza  in ('3','4') 
                AND (inv.mov_fecha BETWEEN '$fecha_inicio' AND '$fecha_final') $query_stacion
                GROUP BY 
                        inv.mov_almacen,
                        inv.art_codigo
                        

                ORDER BY inv.art_codigo 
;                  
                
                ";
        echo $sql;
        if ($sqlca->query($sql) < 0)
            return -1;

        $resultado = Array();

        for ($i = 0; $i < $sqlca->numrows(); $i++) {
            $a = $sqlca->fetchRow();

            $mov_almacen = trim($a['mov_almacen']);
            $art_codigo = trim($a['art_codigo']);
            $mov_cantidad = $a['mov_cantidad'];
            $mov_costounitario = $a['mov_costounitario'];
            $mov_costopromedio = $a['mov_costopromedio'];
            $mov_costototal = $a['mov_costototal'];
            $tran_descripcion = $a['tran_descripcion'];

            $resultado[$mov_almacen][$art_codigo]['mov_cantidad'] = $mov_cantidad;
            $resultado[$mov_almacen][$art_codigo]['mov_costounitario'] = $mov_costounitario;
            $resultado[$mov_almacen][$art_codigo]['mov_costopromedio'] = $mov_costopromedio;
            $resultado[$mov_almacen][$art_codigo]['mov_costototal'] = $mov_costototal;
            $resultado[$mov_almacen][$art_codigo]['tran_descripcion'] = $tran_descripcion;
        }

        return $resultado;
    }

    function ventas_producto($ano, $mes, $estacion) {
        global $sqlca;

        $ultimo_dia = KardexVentaActModel::getUltimoDiaMes($ano, $mes);
        $query_stacion = ($estacion == "TODAS") ? '' : " AND c.ch_almacen='$estacion'";
        $fecha_inicio = $ano . "-" . $mes . "-01 00:00:00";
        $fecha_final = $ano . "-" . $mes . "-" . $ultimo_dia . " 23:59:59";
        $sql = "
               SELECT * FROM ((
                SELECT
                        art.art_codigo as codigo,
                        art.art_descripcion as articulo,
                        round(sum(comb.nu_ventagalon),2) as cantidad,
                        round(sum(comb.nu_ventavalor),2) as importe,
                        comb.ch_sucursal as almacen,  
                        art.art_linea as linea
                FROM
                    comb_ta_contometros comb
                    LEFT JOIN int_articulos art ON (art.art_codigo=comb.ch_codigocombustible)
                    LEFT JOIN fac_lista_precios pre ON (pre.art_codigo=comb.ch_codigocombustible)
--WFAT                    LEFT JOIN com_rec_pre_proveedor pro ON (pro.art_codigo=comb.ch_codigocombustible) *Genera duplicidad de proveedores
                WHERE
                comb.dt_fechaparte BETWEEN '$fecha_inicio' AND '$fecha_final'

                AND comb.nu_ventavalor > 0
                AND comb.nu_ventagalon > 0
                GROUP BY
                almacen,
                codigo,
                articulo,
                linea
                ORDER BY
                linea,
                codigo
        )UNION
        (
                SELECT
                    art.art_codigo as codigo,
                    art.art_descripcion as articulo,
                    sum(d.nu_fac_cantidad) as cantidad,
                    sum(d.nu_fac_valortotal) as importe,
                    c.ch_almacen as almacen,  
                    art.art_linea as linea
                FROM
                fac_ta_factura_cabecera c 
                    LEFT JOIN fac_ta_factura_detalle d ON (c.ch_fac_tipodocumento=d.ch_fac_tipodocumento AND c.ch_fac_seriedocumento=d.ch_fac_seriedocumento AND c.ch_fac_numerodocumento=d.ch_fac_numerodocumento AND c.cli_codigo=d.cli_codigo)
                    LEFT JOIN int_articulos art ON (art.art_codigo=d.art_codigo)
                    LEFT JOIN fac_lista_precios pre ON (pre.art_codigo=art.art_codigo)		
--WFAT                    LEFT JOIN com_rec_pre_proveedor pro ON (pro.art_codigo=d.art_codigo) *Genera duplicidad de proveedores
                    LEFT JOIN int_ta_enlace_items plu ON (plu.art_codigo=d.art_codigo)
                WHERE
                c.dt_fac_fecha BETWEEN '$fecha_inicio' AND '$fecha_final'

                AND c.ch_fac_tipodocumento IN('45') AND pre.pre_lista_precio in (SELECT par_valor FROM int_parametros WHERE par_nombre = 'lista precio')
                $query_stacion
                AND d.art_codigo NOT IN (SELECT art_codigo FROM int_ta_enlace_items)
                GROUP BY
                almacen,
                codigo,
                articulo,
                linea
                ORDER BY
                linea,
                codigo))
                as ventas  
                ORDER BY ventas.codigo;
                                  
                
                ";
        echo $sql;
        if ($sqlca->query($sql) < 0)
            return -1;

        $resultado = Array();

        for ($i = 0; $i < $sqlca->numrows(); $i++) {
            $a = $sqlca->fetchRow();

            $mov_almacen = trim($a['almacen']);
            $art_codigo = trim($a['codigo']);
            $mov_cantidad = $a['cantidad'];
            $mov_importe = $a['importe'];
            $resultado[$mov_almacen][$art_codigo]['mov_cantidad'] = $mov_cantidad;
            $resultado[$mov_almacen][$art_codigo]['mov_importe'] = $mov_importe;
            $resultado[$mov_almacen][$art_codigo]['mov_precio'] = ($mov_cantidad == 0 || $mov_importe == 0) ? '0' : ($mov_importe / $mov_cantidad);
        }

        return $resultado;
    }

    function lista_productos($ano, $mes, $estacion) {
        global $sqlca;

        $ultimo_dia = KardexVentaActModel::getUltimoDiaMes($ano, $mes);
        $query_stacion = ($estacion == "TODAS") ? '' : " WHERE  inv.mov_almacen='$estacion'";
        $fecha_inicio = $ano . "-" . $mes . "-01 00:00:00";
        $fecha_final = $ano . "-" . $mes . "-" . $ultimo_dia . " 23:59:59";
        $sql = "
                SELECT
                    inv.mov_almacen,
                    art.art_linea,
                    inv.art_codigo ,
                    uni.unidad_medida,
                    art.art_descripcion
                    
                    
                FROM
                        inv_movialma inv 
                LEFT JOIN int_articulos art ON (inv.art_codigo=art.art_codigo) 
                LEFT JOIN ( SELECT  tab.tab_elemento,tab_car_03 ||'-'|| tab_car_04  as unidad_medida FROM int_tabla_general tab WHERE tab.tab_tabla='34' ) uni ON uni.tab_elemento=art.art_unidad

        $query_stacion
                GROUP BY 
                inv.mov_almacen,
                art.art_linea,
                inv.art_codigo ,
                uni.unidad_medida,
                art.art_descripcion
                
                ORDER BY 
                art.art_linea
                ;
                ";
        echo $sql;
        if ($sqlca->query($sql) < 0)
            return -1;

        $resultado = Array();

        for ($i = 0; $i < $sqlca->numrows(); $i++) {
            $a = $sqlca->fetchRow();
            $mov_almacen = trim($a['mov_almacen']);
            $art_linea = trim($a['art_linea']);
            $art_codigo = trim($a['art_codigo']);
            $art_descripcion = $a['art_descripcion'];
            $unidad_medida = $a['unidad_medida'];
            $tab_elemento = $a['tab_elemento'];




            $resultado[$mov_almacen][$art_linea][$art_codigo] = array("desc" => $art_descripcion, "cod" => trim($art_codigo), "unidades" => $unidad_medida, "linea" => $tab_elemento);
        }

        return $resultado;
    }

    function search($ano, $mes, $almacen) {
        global $sqlca;



        $saldos = KardexVentaActModel::saldoInicial($ano, $mes, $almacen);
        $formularios = KardexVentaActModel::obtenerTiposFormularios();

        $resultado = Array();
        $anteriores = Array();

        foreach ($saldos['almacenes'] as $cod_almacen => $almacen) {
            foreach ($almacen['codigos'] as $codigo => $articulo) {
                $resultado['almacenes'][$cod_almacen]['articulos'][$codigo]['saldoinicial']['cant_anterior'] = $articulo['stk_stock'];
                $resultado['almacenes'][$cod_almacen]['articulos'][$codigo]['saldoinicial']['unit_anterior'] = $articulo['stk_costounitario'];
                $resultado['almacenes'][$cod_almacen]['articulos'][$codigo]['saldoinicial']['costo_total'] = $articulo['stk_costototal'];

                $anteriores[$cod_almacen][$codigo]['cant_anterior'] = $articulo['stk_stock'];
                $anteriores[$cod_almacen][$codigo]['unit_anterior'] = $articulo['stk_costounitario'];
            }
        }

        $sql = "SELECT
				inv.mov_fecha,
			    	trim(inv.tran_codigo),
			    	inv.mov_numero,
			    	inv.mov_almaorigen,
			    	inv.mov_almadestino,
			    	inv.mov_entidad,
			    	inv.mov_docurefe,
			    	inv.mov_cantidad,
			    	inv.mov_costounitario,
			    	inv.mov_costototal,
			    	inv.mov_costopromedio,
			    	inv.art_codigo,
			    	inv.mov_naturaleza,
			    	inv.mov_almacen,
			    	tab.tab_car_03
			    	--tran.codigo_sunat   preguntar por este campo
			FROM
			    	inv_movialma inv 
			    	LEFT JOIN int_articulos art ON (inv.art_codigo=art.art_codigo)  
			    	LEFT JOIN inv_tipotransa tran ON (trim(tran.tran_codigo)= trim(inv.tran_codigo))  
			    	LEFT JOIN int_tabla_general tab ON (tab.tab_tabla='08' AND lpad(inv.mov_tipdocuref,6,'0')=tab.tab_elemento) 
			WHERE
				inv.mov_fecha BETWEEN '" . pg_escape_string($desde_ano . "-" . $desde_mes . "-" . $desde_dia) . " 00:00:00' AND '" . pg_escape_string($hasta_ano . "-" . $hasta_mes . "-" . $hasta_dia) . " 23:59:59'			    	
			    ";
        if (trim($art_desde) != "")
            $sql .= "AND inv.art_codigo='" . pg_escape_string($art_desde) . "'  ";

        if (trim($linea) != "")
            $sql .= "AND art.art_linea='$linea'  ";

        if ($estacion != "TODAS")
            $sql .= "AND inv.mov_almacen='" . pg_escape_string($estacion) . "' ";

        $sql .= "ORDER BY inv.mov_almacen,inv.art_codigo,date_trunc('day',inv.mov_fecha),inv.mov_naturaleza;";

        //echo "\n\nMovimientos: ".$sql;

        if ($sqlca->query($sql) < 0)
            return null;

        for ($i = 0; $i < $sqlca->numrows(); $i++) {
            $a = $sqlca->fetchRow();

            $mov_fecha = substr($a[0], 0, 19);
            $tran_codigo = $a[1];
            $mov_numero = $a[2];
            $mov_almaorigen = $a[3];
            $mov_almadestino = $a[4];
            $mov_entidad = $a[5];
            $mov_docurefe = $a[6];
            $mov_cantidad = $a[7];
            $mov_costounitario = $a[8];
            $mov_costototal = $a[9];
            $mov_costopromedio = $a[10];
            $art_codigo = $a[11];
            $mov_naturaleza = $a[12];
            $mov_almacen = $a[13];
            $tipodocu = $a[14];
            $tipoopera = $a[15];
            $seriedocu = substr($a[6], 0, 3);
            $numdocu = substr($a[6], -7);

            $resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_fecha'] = $mov_fecha;
            $resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['tipodocu'] = $tipodocu;
            $resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['seriedocu'] = $seriedocu;
            $resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['numdocu'] = $numdocu;
            $resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_numero'] = $mov_numero;
            if ($tran_codigo == "18" && $mov_cantidad < 0) {
                $resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['tran_codigo'] = $formularios[16];
            } else {
                $resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['tran_codigo'] = $formularios[$tran_codigo];
            }
            $resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['tipoopera'] = $tipoopera;

            if ($mov_naturaleza < 3) {
                $anteriores[$mov_almacen][$art_codigo]['cant_anterior'] += $mov_cantidad;
                $cant_actual = $anteriores[$mov_almacen][$art_codigo]['cant_anterior'];
                $resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_cant_entrada'] = $mov_cantidad;
                $resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_unit_entrada'] = $mov_costounitario;
                $resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_cost_entrada'] = $mov_costototal;
                $resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['totales']['cant_entrada'] += $mov_cantidad;
                $resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['totales']['uni_entrada'] += $mov_costounitario;
                $resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['totales']['cost_entrada'] += $mov_costototal;
            } else {
                $anteriores[$mov_almacen][$art_codigo]['cant_anterior'] -= $mov_cantidad;
                $cant_actual = $anteriores[$mov_almacen][$art_codigo]['cant_anterior'];
                $resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_cant_salida'] = $mov_cantidad;
                $resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_unit_salida'] = $mov_costopromedio;
                $resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_cost_salida'] = $mov_costototal;
                $resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['totales']['cant_salida'] += $mov_cantidad;
                $resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['totales']['uni_salida'] += $mov_costounitario;
                $resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['totales']['cost_salida'] += $mov_costototal;
            }

            $resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_docurefe'] = $mov_docurefe;
            $resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_cant_anterior'] = $anteriores[$mov_almacen][$art_codigo]['cant_anterior'];
            $resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_val_ant'] = $anteriores[$mov_almacen][$art_codigo]['unit_anterior'];

            $resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_costounitario'] = $mov_costounitario;
            $resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_cant_actual'] = $cant_actual;
            $resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_val_unit_act'] = $mov_costopromedio;
            $resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_total_act'] = $cant_actual * $mov_costopromedio;

            $resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['totales']['cantidad_total'] += $cant_actual;
            $resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['totales']['uni_total'] += $mov_costounitario;
            $resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['totales']['valor_total'] += ($cant_actual * $mov_costopromedio);
            $anteriores[$mov_almacen][$art_codigo]['cant_anterior'] = $cant_actual;
            $anteriores[$mov_almacen][$art_codigo]['unit_anterior'] = $mov_costounitario;
        }

        foreach ($resultado['almacenes'] as $ka => $va) {
            foreach ($va['articulos'] as $kb => $vb) {
                if (!isset($vb['movimientos']))
                    unset($resultado['almacenes'][$ka]['articulos'][$kb]);
            }
        }

        return $resultado;
    }

    function movialma($desde, $hasta, $art_desde, $estacion, $linea) {
        global $sqlca;

        list($desde_dia, $desde_mes, $desde_ano) = sscanf($desde, "%2s/%2s/%4s");
        list($hasta_dia, $hasta_mes, $hasta_ano) = sscanf($hasta, "%2s/%2s/%4s");

        $sql = "SELECT
				inv.art_codigo as codigo
			FROM 
				inv_movialma inv 
			    	LEFT JOIN int_articulos art ON (inv.art_codigo=art.art_codigo)  
			    	LEFT JOIN inv_tipotransa tran ON (trim(tran.tran_codigo)= trim(inv.tran_codigo))  
			    	LEFT JOIN int_tabla_general tab ON (tab.tab_tabla='08' AND lpad(inv.mov_tipdocuref,6,'0')=tab.tab_elemento)
			WHERE
				inv.mov_fecha BETWEEN '" . pg_escape_string($desde_ano . "-" . $desde_mes . "-" . $desde_dia) . " 00:00:00' AND '" . pg_escape_string($hasta_ano . "-" . $hasta_mes . "-" . $hasta_dia) . " 23:59:59' ";
        if (trim($art_desde) != "")
            $sql .= "AND inv.art_codigo='" . pg_escape_string($art_desde) . "'  ";

        if (trim($linea) != "")
            $sql .= "AND art.art_linea='$linea'  ";

        if ($estacion != "TODAS")
            $sql .= "AND inv.mov_almacen='" . pg_escape_string($estacion) . "' ";

        echo "\n\nCODIGOSSSSSSSSSSS:" . $sql;

        if ($sqlca->query($sql) < 0)
            return -1;

        $resultado = Array();

        for ($i = 0; $i < $sqlca->numrows(); $i++) {
            $a = $sqlca->fetchRow();

            $resultado[$i]['codigo'] = $a[0];
        }

        return $resultado;
    }

    function saldosProductos($ano, $mes, $art_desde, $estacion, $linea) {
        global $sqlca;

        list($dia, $mes, $ano) = sscanf($desde, "%2s/%2s/%4s"); //esta dando masl formato chingados
        if ($mes == 1) {
            $mes = 12;
            $ano--;
        } else {
            $mes--;
        }

        if (strlen($mes) == 1)
            $mes = "0" . $mes;

        $sql = "SELECT
			    	sa.stk_almacen as almacen,
			    	sa.art_codigo as codigo,
			    	sa.stk_stock" . $mes . " as stock,
			    	sa.stk_costo" . $mes . " as costo,
				round (sa.stk_stock07 * sa.stk_costo07,4) total
			FROM
			    	inv_saldoalma sa 
			    	LEFT JOIN int_articulos art ON (sa.art_codigo=art.art_codigo)  
			WHERE
				sa.stk_periodo='$ano' ";

        if (trim($art_desde) != "")
            $sql .= "AND sa.art_codigo='$art_desde' ";

        if (trim($linea) != "")
            $sql .= "AND art.art_linea='$linea' ";

        if ($estacion != "TODAS")
            $sql .= " AND sa.stk_almacen='$estacion' ";

        $sql .= "ORDER BY
			    	sa.stk_periodo,
			    	sa.stk_almacen,
			    	sa.art_codigo;";

        echo "\n\nSaldo Inicial:" . $sql;

        if ($sqlca->query($sql) < 0)
            return -1;

        $resultado = Array();

        for ($i = 0; $i < $sqlca->numrows(); $i++) {
            $a = $sqlca->fetchRow();

            $resultado[$i]['almacen'] = $a[0];
            $resultado[$i]['codigo'] = $a[1];
            $resultado[$i]['stock'] = $a[2];
            $resultado[$i]['costo'] = $a[3];
            $resultado[$i]['total'] = $a[4];
        }

        return $resultado;
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

}

