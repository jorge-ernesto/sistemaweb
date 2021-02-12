<?php

class RegistroVentaModel extends Model {

    function search($desde, $hasta, $estacion) {
        global $sqlca;

        list($desde_dia, $desde_mes, $desde_ano) = sscanf($desde, "%2s/%2s/%4s");
        list($hasta_dia, $hasta_mes, $hasta_ano) = sscanf($hasta, "%2s/%2s/%4s");

        /* $saldos = RegistroVentaModel::saldoInicial($desde, $art_desde, $estacion, $linea);
          $formularios = RegistroVentaModel::obtenerTiposFormularios(); */

        $ventas = RegistroVentaModel::DatosRegistroVentas();


        $resultado = Array();
        $anteriores = Array();



        return $ventas;
    }

    function searchbacku($desde, $hasta, $estacion) {
        global $sqlca;

        list($desde_dia, $desde_mes, $desde_ano) = sscanf($desde, "%2s/%2s/%4s");
        list($hasta_dia, $hasta_mes, $hasta_ano) = sscanf($hasta, "%2s/%2s/%4s");

        /* $saldos = RegistroVentaModel::saldoInicial($desde, $art_desde, $estacion, $linea);
          $formularios = RegistroVentaModel::obtenerTiposFormularios(); */

        RegistroVentaModel::DatosRegistroVentas();


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
			    	--tran.codigo_sunat   
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
            $resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['tran_codigo'] = $formularios[$tran_codigo];
            $resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['tipoopera'] = $tipoopera;

            if ($mov_naturaleza < 3) {
                $anteriores[$mov_almacen][$art_codigo]['cant_anterior'] += $mov_cantidad;
                $cant_actual = $anteriores[$mov_almacen][$art_codigo]['cant_anterior'];
                $resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_cant_entrada'] = $mov_cantidad;
                $resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_unit_entrada'] = $mov_costounitario;
                $resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_cost_entrada'] = $mov_costototal;
                $resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['totales']['cant_entrada'] += $mov_cantidad;
                $resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['totales']['cost_entrada'] += $mov_costototal;
            } else {
                $anteriores[$mov_almacen][$art_codigo]['cant_anterior'] -= $mov_cantidad;
                $cant_actual = $anteriores[$mov_almacen][$art_codigo]['cant_anterior'];
                $resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_cant_salida'] = $mov_cantidad;
                $resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_unit_salida'] = $mov_costopromedio;
                $resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_cost_salida'] = $mov_costototal;
                $resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['totales']['cant_salida'] += $mov_cantidad;
                $resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['totales']['cost_salida'] += $mov_costototal;
            }

            $resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_costounitario'] = $mov_costounitario;
            $resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_cant_actual'] = $cant_actual;
            $resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_val_unit_act'] = $mov_costopromedio;
            $resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_total_act'] = $cant_actual * $mov_costopromedio;

            $resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['totales']['cantidad_total'] += $cant_actual;
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

    /* Metods usados  */

    function DatosRegistroVentas() {
        global $sqlca;
        /* $sql = "select  ps.dia,ps.td,cfg.nroserie, ps.trans,ps.ruc,importe as total,(importe-igv) as importe,igv
          from  pos_trans201212 ps inner join pos_cfg  cfg on ps.caja=cfg.pos "; */
        $fechainicio = "2012-11-01";
        $fechafinal = "2012-11-30";
        $ano_mes = "201211";
        $almacen="009";
  

         $sql = "SELECT 
                        T.trans as trans, 
                        T.dia::date as emision, 
                        T.dia::date as vencimiento,
                        (CASE WHEN td='B' then '03' else '01' end) as tipo, 
                        T.caja as serie,
                        T.trans::text as numero,
                        (CASE WHEN char_length(trim(T.ruc::Text))=11 then '6' 
                         when char_length(trim(T.ruc::Text))=8 then '1'
                        else '0' end) as tipodi, 
                        R.ruc as ruc,
                        R.razsocial as cliente,
                        sum(T.importe-T.igv) imponible,
                        sum(T.igv) as igv, 
                        sum(T.importe) as importe,
                        TC.tca_compra_libre as tipocambio
		FROM	
                        pos_trans$ano_mes T
                        LEFT JOIN int_tipo_cambio TC ON (TC.tca_fecha = T.dia)
                        LEFT JOIN ruc R ON (R.ruc = T.ruc)
                WHERE               
                         T.es='$almacen'
                        GROUP BY 
                T.trans, 
                T.td,
                T.dia, 
                T.caja, 
                R.ruc, 
                R.razsocial, 
                TC.tca_compra_libre, T.ruc ;";
        if ($sqlca->query($sql, "_regventas") < 0)
            return null;

        $resultado = Array();
        $i = 0;
      
$cantidad=$sqlca->numrows("_regventas");

        for (; $i < $cantidad; $i++) {
            $array = $sqlca->fetchRow("_regventas");
            $resultado[$i]['num_correlativo'] = $array['trans'];
            $resultado[$i]['fecha_emision'] = $array['emision'];
            $resultado[$i]['fecha_vencimiento'] = $array['vencimiento'];
            $resultado[$i]['tipo_docu'] = $array['tipodi'];
            $resultado[$i]['tipo'] = $array['tipo'];
            $resultado[$i]['nroserie'] = $array['serie'];
            $resultado[$i]['trans'] = $array['numero'];
            $resultado[$i]['ruc'] = $array['ruc'];
            $resultado[$i]['nombre'] = $array['cliente'];
            $resultado[$i]['imponible'] = $array['imponible'];
            $resultado[$i]['importe'] = $array['importe'];
            $resultado[$i]['igv'] = $array['igv'];
            $resultado[$i]['tipocambio'] = $array['tipocambio'];
        }


         
              $sql = "SELECT 
				Cab.ch_fac_numerodocumento as trans, 
				Cab.dt_fac_fecha as emision, 
				Cab.dt_fac_fecha as vencimiento,
				(CASE WHEN Cab.ch_fac_tipodocumento='35' then '03' else 
				(CASE WHEN Cab.ch_fac_tipodocumento='10' then '01' else 
				(CASE WHEN Cab.ch_fac_tipodocumento='20' then '07' else '08' end) end) end ) as tipo, 
				Cab.ch_fac_seriedocumento  as serie,
				Cab.ch_fac_numerodocumento as numero,
				(CASE WHEN Cli.cli_codigo='9999' then '1' else '6' end) as tipodi, 
				Cli.cli_codigo as ruc,
				Cli.cli_razsocial as cliente,
				sum(Cab.nu_fac_valorbruto) as imponible,
				sum(Cab.nu_fac_impuesto1) as igv, 
				sum(Cab.nu_fac_valortotal) as importe,
				Cab.nu_tipocambio as tipocambio
			FROM	
				fac_ta_factura_cabecera Cab 
				LEFT JOIN int_clientes Cli ON (Cli.cli_codigo = Cab.cli_codigo)
			WHERE
				Cab.ch_fac_tipodocumento IN ('10', '35', '11', '20')   and cab.ch_almacen='$almacen'
				AND Cab.dt_fac_fecha BETWEEN '$fechainicio' AND '$fechafinal'
                        GROUP BY 
				Cab.ch_fac_numerodocumento, 
				Cab.ch_fac_seriedocumento, 
				Cab.ch_fac_tipodocumento, 
				Cab.dt_fac_fecha, 					
				Cli.cli_codigo, 
				Cli.cli_razsocial, 
				Cab.nu_tipocambio ;";
        
         if ($sqlca->query($sql) < 0)
            return null;

        for ($i=0; $i < $sqlca->numrows(); $i++) {
  
            $array = $sqlca->fetchRow();
            $resultado[$cantidad]['num_correlativo'] = "M-" . $array['trans'];
            $resultado[$cantidad]['fecha_emision'] = $array['emision'];
            $resultado[$cantidad]['fecha_vencimiento'] = $array['vencimiento'];
            $resultado[$cantidad]['tipo'] = $array['tipo'];
            $resultado[$cantidad]['tipo_docu'] = $array['tipodi'];
            $resultado[$cantidad]['nroserie'] = $array['serie'];
            $resultado[$cantidad]['trans'] = $array['numero'];
            $resultado[$cantidad]['ruc'] = $array['ruc'];
            $resultado[$cantidad]['nombre'] = $array['cliente'];
            $resultado[$cantidad]['imponible'] = $array['imponible'];
            $resultado[$cantidad]['importe'] = $array['importe'];
            $resultado[$cantidad]['igv'] = $array['igv'];
            $resultado[$cantidad]['tipocambio'] = $array['tipocambio'];
            
            $cantidad++;
        }

        return $resultado;
    }

    public function Nombre($codigo) {
        $nombre = RegistroVentaModel::obtenerProveedor($codigo);
        if ($nombre == null) {
            $nombre = RegistroVentaModel::obtenerCliente($codigo);
            if ($nombre == null) {
                $nombre = RegistroVentaModel::obtenerRuc($codigo);
                if ($nombre == null) {
                    $nombre = "-";
                }
            }
        }
        return $nombre;
    }

    /* Metods usados  */

    function saldoInicial($desde, $art_desde, $estacion, $linea) {
        global $sqlca;

        list($dia, $mes, $ano) = sscanf($desde, "%2s/%2s/%4s");

        if ($mes == 1) {
            $mes = 12;
            $ano--;
        } else {
            $mes--;
        }

        if (strlen($mes) == 1)
            $mes = "0" . $mes;

        $sql = "SELECT
			    	sa.stk_almacen,
			    	sa.art_codigo,
			    	sa.stk_stock" . $mes . ",
			    	sa.stk_costo" . $mes . "
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

        //echo "\n\nSaldo Inicial:".$sql;

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

        if ($dia > 1) {
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
				    	inv.mov_fecha BETWEEN '" . ($ano . "-" . $mes . "-01") . " 00:00:00' AND '" . ($ano . "-" . $mes . "-" . ($dia - 1)) . " 23:59:59' ";

            if (trim($art_desde) != "")
                $sql .= "AND inv.art_codigo='" . pg_escape_string($art_desde) . "'  ";

            if (trim($linea) != "")
                $sql .= "AND art.art_linea='$linea'  ";

            if ($estacion != "TODAS")
                $sql .= "AND inv.mov_almacen='" . pg_escape_string($estacion) . "' ";

            $sql .= " ORDER BY
					inv.mov_almacen,
					inv.art_codigo,
					inv.mov_fecha;";

            //echo "\n\nSaldo Inicial 2: ".$sql;

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

        $sql = "SELECT trim(tran_codigo) as tran_codigo, trim(tran_descripcion) as tran_descripcion FROM inv_tipotransa ORDER BY tran_codigo;";
        if ($sqlca->query($sql, "_formularios") < 0)
            return null;

        $resultado = Array();
        for ($i = 0; $i < $sqlca->numrows("_formularios"); $i++) {
            $array = $sqlca->fetchRow("_formularios");
            $resultado[$array[0]] = $array[0] . " - " . $array[1];
        }

        $resultado['TODOS'] = "Todos los tipos";
        return $resultado;
    }

    function obtenerProveedor($codigo) {
        global $sqlca;

        $sql = "SELECT pro_razsocial FROM int_proveedores WHERE pro_codigo='" . pg_escape_string($codigo) . "';";
        if ($sqlca->query($sql, "_proveedor") < 0)
            return null;

        $a = $sqlca->fetchRow("_proveedor");
        return $a[0];
    }

    function obtenerCliente($codigo) {
        global $sqlca;

        $sql = "SELECT cli_razsocial FROM int_clientes WHERE cli_codigo='" . pg_escape_string($codigo) . "';";
        if ($sqlca->query($sql, "_cliente") < 0)
            return null;

        $a = $sqlca->fetchRow("_cliente");
        return $a[0];
    }

    function obtenerRuc($codigo) {
        global $sqlca;

        $sql = "SELECT razsocial,ruc FROM ruc WHERE ruc='" . pg_escape_string($codigo) . "';";
        if ($sqlca->query($sql, "_ruc") < 0)
            return null;

        $a = $sqlca->fetchRow("_ruc");
        return $a[0];
    }

    function obtenerDescripcion($codigo) {
        global $sqlca;

        $sql = "SELECT art_descripcion FROM int_articulos WHERE art_codigo='" . pg_escape_string($codigo) . "';";
        if ($sqlca->query($sql, "_articulo") < 0)
            return null;

        $a = $sqlca->fetchRow("_articulo");
        return $a[0];
    }

    function datosEmpresa() {
        global $sqlca;

        $sql = "SELECT p1.par_valor, p2.par_valor, p3.par_valor FROM int_parametros p1, int_parametros p2, int_parametros p3 WHERE p1.par_nombre='razsocial' and p2.par_nombre='ruc' and p3.par_nombre='dires';";
        if ($sqlca->query($sql) < 0)
            return null;

        $res = Array();
        $a = $sqlca->fetchRow();
        $res['razsocial'] = $a[0];
        $res['ruc'] = $a[1];
        $res['direccion'] = $a[2];

        return $res;
    }

    function unidadMedida($codigo) {
        global $sqlca;

        $sql = "SELECT 
				tab.tab_car_03 
			FROM 
				int_tabla_general tab 
				LEFT JOIN int_articulos art ON (tab.tab_tabla='34' AND tab.tab_elemento=art.art_unidad) 
			WHERE 
				trim(art.art_codigo)='" . trim($codigo) . "';";

        if ($sqlca->query($sql) < 0)
            return null;
        $a = $sqlca->fetchRow();

        return $a[0];
    }

}

