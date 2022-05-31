    <?php

    /*
    Templates para reportes Contables Balance
    @TBCA
    */
    //load libraries
    include "../valida_sess.php";
    include_once('/sistemaweb/include/dbsqlca.php');
    include_once('/sistemaweb/include/reportes2.inc.php');

    //define global variables

    $sqlca = new pgsqlDB('localhost', 'postgres', 'postgres', 'integrado');

    //Authenticar usuario y definir ambiente
    //echo "LLEGO";
    class LiquidacionPDFTemplate {

        function ObtNumVales($NroDocumento, $sucursal, $fecha) {
            global $sqlca;

            $query = "SELECT ch_numeval FROM val_ta_complemento 
                WHERE ch_documento='" . $NroDocumento . "' and ch_sucursal ='" . $sucursal . "' and dt_fecha ='" . $fecha . "';";
            $sqlca->query($query);
            //if($sqlca->query($query)<=0)
            //return $sqlca->get_error();
            $contador = $sqlca->numrows();
            $c = 1;
            $x = 0;
            $z = 0;
            $cuentatodo = 0;
            while ($reg = $sqlca->fetchRow()) {
                if ($c == $contador)
                    $coma = "";
                else
                    $coma = ",";


                if ($x > 0 && ($x % 4) == 0) {
                    $z++;
                    //echo "<br> IF C : $c => X : $x => Z : ".($z)."<br>";
                    $registros['NUMEROS'][$z][] .= $reg['ch_numeval'] . @$coma;
                } else {

                    //echo "<br>ELSE C : $c => X : $x => Z : $z<br>";
                    $registros['NUMEROS'][$z][] .= $reg['ch_numeval'] . @$coma;
                }
                $c++;
                $x++;
            }

            if ($x == 0) {

                $registros['NUMEROS'][0][] = " ";
            }
            $registros['CANT'] = $x;


            return $registros;
        }
        function GeneraDatosXCOBRAR($liquidacion, $ch_cliente) {
            global $sqlca;

            $sql = "SELECT TRIM(ch_numeval) AS ch_numeval FROM val_ta_complemento_documento WHERE ch_liquidacion='$liquidacion';";

            if ($sqlca->query($sql) <= 0) {
                return $sqlca->get_error();
            }
            $array_vales = array();
            while ($reg = $sqlca->fetchRow()) {
                $array_vales[] = "'" . $reg['ch_numeval'] . "'";
            }
            $vales_de_liquidacion = implode(',', $array_vales);


            $query = "
    SELECT
    val_cab.ch_documento,
    val_cab.ch_cliente,
    clie.cli_razsocial,
    val_cab.dt_fechaactualizacion,
    val_cab.nu_importe,
    val_det.ch_articulo,
    val_det.nu_cantidad as art_cantidad,
    val_det.nu_importe as art_importe,
    art.art_descripcion,
    val_cab.ch_liquidacion,
    val_cab.ch_sucursal,
    val_det.nu_precio_unitario as art_precio,
    val_cab.ch_placa as placa,
    pos.nomusu as conductor,
    val_cab.dt_fecha AS fecha_insercion,
    (SELECT ch_numeval FROM val_ta_complemento WHERE ch_documento = val_cab.ch_documento AND dt_fecha = val_cab.dt_fecha LIMIT 1) AS ch_numeval_manual
    FROM
    val_ta_cabecera AS val_cab,
    val_ta_detalle AS val_det,
    int_articulos AS art,
    int_clientes AS clie,
    pos_fptshe1 AS pos
    WHERE
    val_cab.ch_documento IN(" . $vales_de_liquidacion . ")
    AND TRIM(val_cab.ch_cliente)=TRIM('$ch_cliente')
    AND val_det.ch_sucursal=val_cab.ch_sucursal	
    AND val_det.dt_fecha=val_cab.dt_fecha
    AND val_det.ch_documento=val_cab.ch_documento
    AND art.Art_Codigo=val_det.ch_articulo
    AND clie.Cli_Codigo=val_cab.ch_cliente 
    AND pos.numtar=val_cab.ch_tarjeta
    ORDER BY 
    val_cab.ch_liquidacion,
    clie.cli_razsocial, 
    val_cab.ch_documento;
            ";

            // error_log("GeneraDatosXCOBRAR");
            // error_log($query);

            if ($sqlca->query($query) <= 0)
                return $sqlca->get_error();
            while ($reg = $sqlca->fetchRow()) {

                $registros['CLIENTES'][trim($reg[1]) . "  " . trim($reg[2])][] = $reg;
                //  $registrosFinal[$reg[9]]['CLIENTES'][trim($reg[1]) . "  " . trim($reg[2])][] = $reg;
                $registrosFinal[$liquidacion]['CLIENTES'][trim($reg[1]) . "  " . trim($reg[2])][] = $reg;
            }

            //OBTENEMOS FECHA DE LIQUIDACION
            $query = "
    SELECT 
        fecha_liquidacion 
    FROM 
        val_ta_complemento_documento 
    WHERE 
        ch_liquidacion = '$liquidacion'";                        
                    
            if ($sqlca->query($query) <= 0) {
                return $sqlca->get_error();
            }
            
            $regLiquidacion = $sqlca->fetchRow();                        
            $registrosFinal[$liquidacion]['LIQUIDACION']['fecha_liquidacion'] = $regLiquidacion[0];

            //print_r($registrosFinal);
            $query = "SELECT " .
                    "ch_fac_tipodocumento, " .
                    "cli_codigo, " .
                    "ch_liquidacion, " .
                    "substr(ch_fac_seriedocumento,0,4)||'-'||lpad(trim(ch_fac_numerodocumento),7,'0') as nro_documento, " .
                    "lpad(trim(ch_fac_numerodocumento),7,'0') as ch_fac_numerodocumento " .
                    "FROM " .
                    "fac_ta_factura_cabecera " .
                    "WHERE " .
                    "ch_liquidacion in ('" . $liquidacion . "') ";


    //print_r($query);
            $sqlca->query($query);
            //return $sqlca->get_error();

            while ($reg = $sqlca->fetchRow()) {
                $registrosDoc[$reg['ch_liquidacion']] [$reg['ch_fac_tipodocumento'] . "-" . $reg['ch_fac_numerodocumento']] = $reg;
            }

    /*
            $query = "SELECT " .
                    "ch_tipdocumento, " .
                    "cli_codigo, " .
                    "ch_numdocreferencia as ch_liquidacion, " .
                    "substr(ch_seriedocumento,0,5)||'-'||lpad(trim(ch_numdocumento),7,'0') as nro_documento, " .
                    "lpad(trim(ch_numdocumento),7,'0') as ch_numdocumento " .
                    "FROM " .
                    "ccob_ta_cabecera " .
                    "WHERE " .
                    "ch_numdocreferencia in ('" . $liquidacion . "') ";
    */

            $query = "
    SELECT
    '22' AS ch_tipdocumento,
    ch_cliente AS cli_codigo,
    ch_liquidacion,
    'FACTURA-'||cod_hermandad AS nro_documento,
    cod_hermandad AS ch_numdocumento
    FROM
    val_ta_complemento_documento
    WHERE
    ch_liquidacion IN('" . $liquidacion . "')
            ";

            $sqlca->query($query);

            while ($reg = $sqlca->fetchRow()) {
                $registrosDoc[$reg['ch_liquidacion']][$reg['ch_tipdocumento'] . "-" . $reg['ch_numdocumento']] = $reg;
            }

            $DatosFinales['DatosReg'] = $registrosFinal;
            $DatosFinales['DatosDoc'] = $registrosDoc;
            
            return $DatosFinales;
        }
        
        function GeneraDatosPRODUCTO($liquidacion, $ch_cliente, $produxto) {
            global $sqlca;
            /*

            */

            $sql = "select trim(ch_numeval) as ch_numeval from val_ta_complemento_documento where ch_liquidacion='$liquidacion';";

            if ($sqlca->query($sql) <= 0) {
                return $sqlca->get_error();
            }
            $array_vales = array();
            while ($reg = $sqlca->fetchRow()) {
                $array_vales[] = "'" . $reg['ch_numeval'] . "'";
            }
            $vales_de_liquidacion = implode(',', $array_vales);


            $query = "SELECT
            val_cab.ch_documento,
            val_cab.ch_cliente,
            clie.Cli_RazSocial,
            val_cab.fecha_replicacion AS dt_fechaactualizacion,
            val_cab.nu_importe,
            val_det.ch_articulo,
            val_det.nu_cantidad as art_cantidad,		
            val_det.nu_importe as art_importe,
            art.art_descripcion,
            val_cab.ch_liquidacion, 
            val_cab.ch_sucursal,
            val_det.nu_precio_unitario as art_precio,
            val_cab.ch_placa as placa,
            pos.nomusu as conductor,
            (SELECT ch_numeval FROM val_ta_complemento WHERE ch_documento = val_cab.ch_documento AND dt_fecha = val_cab.dt_fecha LIMIT 1) AS ch_numeval_manual
            FROM
            val_ta_cabecera val_cab,
            val_ta_detalle val_det,
            int_articulos art,
            int_clientes clie,
            pos_fptshe1 pos
            WHERE
            val_cab.ch_documento in (" . $vales_de_liquidacion . ") AND  trim(val_cab.ch_cliente)=trim('$ch_cliente')
                    AND val_det.ch_articulo='$produxto'
            AND val_det.ch_sucursal=val_cab.ch_sucursal	
            AND val_det.dt_fecha=val_cab.dt_fecha
            AND val_det.ch_documento=val_cab.ch_documento
            AND art.Art_Codigo=val_det.ch_articulo
            AND clie.Cli_Codigo=val_cab.ch_cliente 
            AND pos.numtar=val_cab.ch_tarjeta
            ORDER BY 
                    val_cab.ch_liquidacion,
                    clie.Cli_RazSocial, 
                    val_cab.ch_documento;";

            // error_log("GeneraDatosPRODUCTO");
            // error_log($query);

            if ($sqlca->query($query) <= 0)
                return $sqlca->get_error();
            while ($reg = $sqlca->fetchRow()) {

                $registros['CLIENTES'][trim($reg[1]) . "  " . trim($reg[2])][] = $reg;
                //  $registrosFinal[$reg[9]]['CLIENTES'][trim($reg[1]) . "  " . trim($reg[2])][] = $reg;
                $registrosFinal[$liquidacion]['CLIENTES'][trim($reg[1]) . "  " . trim($reg[2])][] = $reg;
            }

            //OBTENEMOS FECHA DE LIQUIDACION
            $query = "
    SELECT 
        fecha_liquidacion 
    FROM 
        val_ta_complemento_documento 
    WHERE 
        ch_liquidacion = '$liquidacion'";                        
                    
            if ($sqlca->query($query) <= 0) {
                return $sqlca->get_error();
            }
            
            $regLiquidacion = $sqlca->fetchRow();                        
            $registrosFinal[$liquidacion]['LIQUIDACION']['fecha_liquidacion'] = $regLiquidacion[0];

            //print_r($registrosFinal);
            $query = "SELECT " .
                    "ch_fac_tipodocumento, " .
                    "cli_codigo, " .
                    "ch_liquidacion, " .
                    "substr(ch_fac_seriedocumento,0,4)||'-'||lpad(trim(ch_fac_numerodocumento),7,'0') as nro_documento, " .
                    "lpad(trim(ch_fac_numerodocumento),7,'0') as ch_fac_numerodocumento " .
                    "FROM " .
                    "fac_ta_factura_cabecera " .
                    "WHERE " .
                    "ch_liquidacion in ('" . $liquidacion . "') ";


    //print_r($query);
            $sqlca->query($query);
            //return $sqlca->get_error();
            while ($reg = $sqlca->fetchRow()) {
                $registrosDoc[$reg['ch_liquidacion']] [$reg['ch_fac_tipodocumento'] . "-" . $reg['ch_fac_numerodocumento']] = $reg;
            }

            $query = "SELECT " .
                    "ch_tipdocumento, " .
                    "cli_codigo, " .
                    "ch_numdocreferencia as ch_liquidacion, " .
                    "substr(ch_seriedocumento,0,5)||'-'||lpad(trim(ch_numdocumento),7,'0') as nro_documento, " .
                    "lpad(trim(ch_numdocumento),7,'0') as ch_numdocumento " .
                    "FROM " .
                    "ccob_ta_cabecera " .
                    "WHERE " .
                    "ch_numdocreferencia in ('" . $liquidacion . "') ";

            $sqlca->query($query);

            while ($reg = $sqlca->fetchRow()) {
                $registrosDoc[$reg['ch_liquidacion']][$reg['ch_tipdocumento'] . "-" . $reg['ch_numdocumento']] = $reg;
            }

            $DatosFinales['DatosReg'] = $registrosFinal;
            $DatosFinales['DatosDoc'] = $registrosDoc;


            return $DatosFinales;
        }
        function GeneraDatosPLACA($liquidacion, $ch_cliente, $placa) {
            global $sqlca;
            /*

            */

            $sql = "select trim(ch_numeval) as ch_numeval from val_ta_complemento_documento where ch_liquidacion='$liquidacion';";

            if ($sqlca->query($sql) <= 0) {
                return $sqlca->get_error();
            }
            $array_vales = array();
            while ($reg = $sqlca->fetchRow()) {
                $array_vales[] = "'" . $reg['ch_numeval'] . "'";
            }
            $vales_de_liquidacion = implode(',', $array_vales);


            $query = "SELECT
            val_cab.ch_documento,
            val_cab.ch_cliente,
            clie.Cli_RazSocial,
            val_cab.fecha_replicacion AS dt_fechaactualizacion,
            val_cab.nu_importe,
            val_det.ch_articulo,
            val_det.nu_cantidad as art_cantidad,		
            val_det.nu_importe as art_importe,
            art.art_descripcion,
            val_cab.ch_liquidacion, 
            val_cab.ch_sucursal,
            val_det.nu_precio_unitario as art_precio,
            val_cab.ch_placa as placa,
            pos.nomusu as conductor,
            (SELECT ch_numeval FROM val_ta_complemento WHERE ch_documento = val_cab.ch_documento AND dt_fecha = val_cab.dt_fecha LIMIT 1) AS ch_numeval_manual,
            val_cab.dt_fecha AS fecha_insercion
            FROM
            val_ta_cabecera val_cab,
            val_ta_detalle val_det,
            int_articulos art,
            int_clientes clie,
            pos_fptshe1 pos
            WHERE
            val_cab.ch_documento in (" . $vales_de_liquidacion . ") AND  trim(val_cab.ch_cliente)=trim('$ch_cliente')
                    AND val_cab.ch_placa='$placa'
            AND val_det.ch_sucursal=val_cab.ch_sucursal	
            AND val_det.dt_fecha=val_cab.dt_fecha
            AND val_det.ch_documento=val_cab.ch_documento
            AND art.Art_Codigo=val_det.ch_articulo
            AND clie.Cli_Codigo=val_cab.ch_cliente 
            AND pos.numtar=val_cab.ch_tarjeta
            ORDER BY 
                    val_cab.ch_liquidacion,
                    clie.Cli_RazSocial, 
                    val_cab.ch_documento;";

            // error_log("GeneraDatosPLACA");
            // error_log($query);

            if ($sqlca->query($query) <= 0)
                return $sqlca->get_error();
            while ($reg = $sqlca->fetchRow()) {

                $registros['CLIENTES'][trim($reg[1]) . "  " . trim($reg[2])][] = $reg;
                //  $registrosFinal[$reg[9]]['CLIENTES'][trim($reg[1]) . "  " . trim($reg[2])][] = $reg;
                $registrosFinal[$liquidacion]['CLIENTES'][trim($reg[1]) . "  " . trim($reg[2])][] = $reg;
            }

            //OBTENEMOS FECHA DE LIQUIDACION
            $query = "
    SELECT 
        fecha_liquidacion 
    FROM 
        val_ta_complemento_documento 
    WHERE 
        ch_liquidacion = '$liquidacion'";                        
                    
            if ($sqlca->query($query) <= 0) {
                return $sqlca->get_error();
            }
            
            $regLiquidacion = $sqlca->fetchRow();                        
            $registrosFinal[$liquidacion]['LIQUIDACION']['fecha_liquidacion'] = $regLiquidacion[0];

            //print_r($registrosFinal);
            $query = "SELECT " .
                    "ch_fac_tipodocumento, " .
                    "cli_codigo, " .
                    "ch_liquidacion, " .
                    "substr(ch_fac_seriedocumento,0,4)||'-'||lpad(trim(ch_fac_numerodocumento),7,'0') as nro_documento, " .
                    "lpad(trim(ch_fac_numerodocumento),7,'0') as ch_fac_numerodocumento " .
                    "FROM " .
                    "fac_ta_factura_cabecera " .
                    "WHERE " .
                    "ch_liquidacion in ('" . $liquidacion . "') ";


    //print_r($query);
            $sqlca->query($query);
            //return $sqlca->get_error();
            while ($reg = $sqlca->fetchRow()) {
                $registrosDoc[$reg['ch_liquidacion']] [$reg['ch_fac_tipodocumento'] . "-" . $reg['ch_fac_numerodocumento']] = $reg;
            }

            $query = "SELECT " .
                    "ch_tipdocumento, " .
                    "cli_codigo, " .
                    "ch_numdocreferencia as ch_liquidacion, " .
                    "substr(ch_seriedocumento,0,5)||'-'||lpad(trim(ch_numdocumento),7,'0') as nro_documento, " .
                    "lpad(trim(ch_numdocumento),7,'0') as ch_numdocumento " .
                    "FROM " .
                    "ccob_ta_cabecera " .
                    "WHERE " .
                    "ch_numdocreferencia in ('" . $liquidacion . "') ";

            $sqlca->query($query);

            while ($reg = $sqlca->fetchRow()) {
                $registrosDoc[$reg['ch_liquidacion']][$reg['ch_tipdocumento'] . "-" . $reg['ch_numdocumento']] = $reg;
            }

            $DatosFinales['DatosReg'] = $registrosFinal;
            $DatosFinales['DatosDoc'] = $registrosDoc;


            return $DatosFinales;
        }

        function GeneraDatosND($liquidacion, $ch_cliente, $nota_despacho) {
            global $sqlca;
            /*

            */

            $sql = "select trim(ch_numeval) as ch_numeval from val_ta_complemento_documento where ch_liquidacion='$liquidacion';";

            if ($sqlca->query($sql) <= 0) {
                return $sqlca->get_error();
            }
            $array_vales = array();
            while ($reg = $sqlca->fetchRow()) {
                $array_vales[] = "'" . $reg['ch_numeval'] . "'";
            }
            $vales_de_liquidacion = implode(',', $array_vales);


            $query = "SELECT
            val_cab.ch_documento,
            val_cab.ch_cliente,
            clie.Cli_RazSocial,
            val_cab.fecha_replicacion AS dt_fechaactualizacion,
            val_cab.nu_importe,
            val_det.ch_articulo,
            val_det.nu_cantidad as art_cantidad,		
            val_det.nu_importe as art_importe,
            art.art_descripcion,
            val_cab.ch_liquidacion, 
            val_cab.ch_sucursal,
            val_det.nu_precio_unitario as art_precio,
            val_cab.ch_placa as placa,
            pos.nomusu as conductor,
            (SELECT ch_numeval FROM val_ta_complemento WHERE ch_documento = val_cab.ch_documento AND dt_fecha = val_cab.dt_fecha LIMIT 1) AS ch_numeval_manual
            FROM
            val_ta_cabecera val_cab,
            val_ta_detalle val_det,
            int_articulos art,
            int_clientes clie,
            pos_fptshe1 pos
            WHERE
            val_cab.ch_documento in (" . $vales_de_liquidacion . ") AND  trim(val_cab.ch_cliente)=trim('$ch_cliente')
                    AND val_cab.ch_documento='$nota_despacho'
            AND val_det.ch_sucursal=val_cab.ch_sucursal	
            AND val_det.dt_fecha=val_cab.dt_fecha
            AND val_det.ch_documento=val_cab.ch_documento
            AND art.Art_Codigo=val_det.ch_articulo
            AND clie.Cli_Codigo=val_cab.ch_cliente 
            AND pos.numtar=val_cab.ch_tarjeta
            ORDER BY 
                    val_cab.ch_liquidacion,
                    clie.Cli_RazSocial, 
                    val_cab.ch_documento;";

            // error_log("GeneraDatosND");
            // error_log($query);

    //print_r($query);
            if ($sqlca->query($query) <= 0)
                return $sqlca->get_error();
            while ($reg = $sqlca->fetchRow()) {

                $registros['CLIENTES'][trim($reg[1]) . "  " . trim($reg[2])][] = $reg;
                //  $registrosFinal[$reg[9]]['CLIENTES'][trim($reg[1]) . "  " . trim($reg[2])][] = $reg;
                $registrosFinal[$liquidacion]['CLIENTES'][trim($reg[1]) . "  " . trim($reg[2])][] = $reg;
            }

            //OBTENEMOS FECHA DE LIQUIDACION
            $query = "
    SELECT 
        fecha_liquidacion 
    FROM 
        val_ta_complemento_documento 
    WHERE 
        ch_liquidacion = '$liquidacion'";                        
                    
            if ($sqlca->query($query) <= 0) {
                return $sqlca->get_error();
            }
            
            $regLiquidacion = $sqlca->fetchRow();                        
            $registrosFinal[$liquidacion]['LIQUIDACION']['fecha_liquidacion'] = $regLiquidacion[0];

            //print_r($registrosFinal);
            $query = "SELECT " .
                    "ch_fac_tipodocumento, " .
                    "cli_codigo, " .
                    "ch_liquidacion, " .
                    "substr(ch_fac_seriedocumento,0,4)||'-'||lpad(trim(ch_fac_numerodocumento),7,'0') as nro_documento, " .
                    "lpad(trim(ch_fac_numerodocumento),7,'0') as ch_fac_numerodocumento " .
                    "FROM " .
                    "fac_ta_factura_cabecera " .
                    "WHERE " .
                    "ch_liquidacion in ('" . $liquidacion . "') ";


    //print_r($query);
            $sqlca->query($query);
            //return $sqlca->get_error();
            while ($reg = $sqlca->fetchRow()) {
                $registrosDoc[$reg['ch_liquidacion']] [$reg['ch_fac_tipodocumento'] . "-" . $reg['ch_fac_numerodocumento']] = $reg;
            }

            $query = "SELECT " .
                    "ch_tipdocumento, " .
                    "cli_codigo, " .
                    "ch_numdocreferencia as ch_liquidacion, " .
                    "substr(ch_seriedocumento,0,5)||'-'||lpad(trim(ch_numdocumento),7,'0') as nro_documento, " .
                    "lpad(trim(ch_numdocumento),7,'0') as ch_numdocumento " .
                    "FROM " .
                    "ccob_ta_cabecera " .
                    "WHERE " .
                    "ch_numdocreferencia in ('" . $liquidacion . "') ";

            $sqlca->query($query);

            while ($reg = $sqlca->fetchRow()) {
                $registrosDoc[$reg['ch_liquidacion']][$reg['ch_tipdocumento'] . "-" . $reg['ch_numdocumento']] = $reg;
            }

            $DatosFinales['DatosReg'] = $registrosFinal;
            $DatosFinales['DatosDoc'] = $registrosDoc;


                return $DatosFinales;
        }

        function GeneraDatosNormal($liquidacion, $ch_cliente) {
            global $sqlca;

            $query = "
    SELECT
    val_liq.ch_numeval AS ch_documento,
    val_liq.ch_cliente,
    cli.cli_razsocial,
    val_liq.nu_fac_valortotal AS nu_importe,
    val_det.ch_articulo,
    ROUND(val_det.nu_cantidad,3) as art_cantidad,		
    ROUND(val_det.nu_importe,2) as art_importe,
    art.art_descripcion,
    val_liq.ch_liquidacion, 
    val_liq.ch_sucursal,
    (CASE WHEN val_det.nu_cantidad > 0 THEN ROUND(val_det.nu_importe / val_det.nu_cantidad, 2) ELSE 0 END) AS art_precio,
    val_cab.ch_placa AS placa,
    (SELECT numpla FROM pos_fptshe1 WHERE pos_fptshe1.numpla = val_cab.ch_placa LIMIT 1),
    val_liq.dt_fecha AS fecha_insercion,
    pos.nomusu AS conductor,
    val_cab.fecha_replicacion AS dt_fechaactualizacion,
    val_com.ch_numeval AS ch_numeval_manual
    FROM
    val_ta_complemento_documento AS val_liq
    LEFT JOIN val_ta_cabecera AS val_cab ON(val_cab.ch_cliente = val_liq.ch_cliente AND val_liq.ch_numeval = val_cab.ch_documento AND val_liq.dt_fecha = val_cab.dt_fecha)
    LEFT JOIN val_ta_detalle AS val_det ON(val_liq.ch_numeval = val_det.ch_documento AND val_liq.art_codigo = val_det.ch_articulo AND val_liq.dt_fecha = val_det.dt_fecha)
    --LEFT JOIN val_ta_cabecera val_cab ON (val_cab.ch_cliente = val_liq.ch_cliente AND val_liq.ch_numeval = val_cab.ch_documento AND val_liq.dt_fecha = val_cab.dt_fecha AND val_liq.ch_sucursal = val_cab.ch_sucursal)
    --LEFT JOIN val_ta_detalle val_det ON (val_liq.ch_numeval = val_det.ch_documento AND val_liq.art_codigo = val_det.ch_articulo AND val_liq.dt_fecha = val_det.dt_fecha AND val_liq.ch_sucursal = val_det.ch_sucursal)
    LEFT JOIN val_ta_complemento AS val_com ON (val_liq.ch_numeval = val_com.ch_documento AND val_liq.dt_fecha = val_com.dt_fecha)
    LEFT JOIN int_clientes AS cli ON(cli.cli_codigo = val_liq.ch_cliente)
    LEFT JOIN int_articulos AS art ON(art.art_codigo = val_liq.art_codigo)
    LEFT JOIN pos_fptshe1 AS pos ON(pos.numpla = val_cab.ch_placa AND pos.numtar = val_cab.ch_tarjeta AND pos.codcli = val_cab.ch_cliente)
    WHERE
    val_liq.ch_liquidacion='" . $liquidacion . "'
    AND val_liq.ch_cliente='" . $ch_cliente . "'
    ORDER BY
    val_cab.fecha_replicacion;
            ";

            // error_log("GeneraDatosNormal");
            // error_log($query);
            
            // echo "<pre>";
            // echo $query; 
            // echo "</pre>";
            
            if ($sqlca->query($query) <= 0)
                return $sqlca->get_error();

            while ($reg = $sqlca->fetchRow()) {
                $registros['CLIENTES'][trim($reg[1]) . "  " . trim($reg[2])][] = $reg; // ???
                $registrosFinal[$liquidacion]['CLIENTES'][trim($reg[1]) . "  " . trim($reg[2])][] = $reg;
            }
            // echo "<script>console.log('registrosFinal: " . json_encode($registrosFinal) . "')</script>";

            //OBTENEMOS FECHA DE LIQUIDACION
            $query = "
    SELECT 
        fecha_liquidacion 
    FROM 
        val_ta_complemento_documento 
    WHERE 
        ch_liquidacion = '$liquidacion'";                        
                    
            if ($sqlca->query($query) <= 0) {
                return $sqlca->get_error();
            }
            
            $regLiquidacion = $sqlca->fetchRow();                        
            $registrosFinal[$liquidacion]['LIQUIDACION']['fecha_liquidacion'] = $regLiquidacion[0];

            $query =
    "SELECT " .
    "ch_fac_tipodocumento, " .
    "cli_codigo, " .
    "ch_liquidacion, " .
    "substr(ch_fac_seriedocumento,0,5)||'-'||lpad(trim(ch_fac_numerodocumento),7,'0') as nro_documento, " .
    "lpad(trim(ch_fac_numerodocumento),7,'0') as ch_fac_numerodocumento " .
    "FROM " .
    "fac_ta_factura_cabecera " .
    "WHERE " .
    "ch_liquidacion IN('" . $liquidacion . "') " .
    "AND cli_codigo='" . $ch_cliente . "'";

            // echo "<pre>";
            // echo $query; 
            // echo "</pre>";

            $sqlca->query($query);

            while ($reg = $sqlca->fetchRow()) {
                $registrosDoc[$reg['ch_liquidacion']][$reg['ch_fac_tipodocumento'] . "-" . $reg['ch_fac_numerodocumento']] = $reg;
            }
            // echo "<script>console.log('registrosDoc: " . json_encode($registrosDoc) . "')</script>";

                $query="
    SELECT " .
    "ch_tipdocumento, " .
    "cli_codigo, " .
    "ch_numdocreferencia as ch_liquidacion, " .
    "substr(ch_seriedocumento,0,5)||'-'||lpad(trim(ch_numdocumento),7,'0') as nro_documento, " .
    "lpad(trim(ch_numdocumento),7,'0') as ch_numdocumento " .
    "FROM " .
    "ccob_ta_cabecera " .
    "WHERE " .
    "ch_numdocreferencia IN('" . $liquidacion . "') " .
    "AND cli_codigo='" . $ch_cliente . "'";

            // echo "<pre>";
            // echo $query; 
            // echo "</pre>";

            $sqlca->query($query);

            while ($reg = $sqlca->fetchRow()) {
                $registrosDoc[$reg['ch_liquidacion']][$reg['ch_tipdocumento'] . "-" . $reg['ch_numdocumento']] = $reg;
            }
            // echo "<script>console.log('registrosDoc: " . json_encode($registrosDoc) . "')</script>";

            $DatosFinales['DatosReg'] = $registrosFinal;
            $DatosFinales['DatosDoc'] = $registrosDoc;
            // echo "<script>console.log('DatosFinales: " . json_encode($DatosFinales) . "')</script>";

            return $DatosFinales;

        }

        function getDireccionSucursal($str,$str2) {
            $result = '';
            $del = '|';
            return explode($del, $str);
        }

        function datosEmpresa($iAlmacen) {
            global $sqlca;
            /* Get ebiauth: Por almacen y si no encuentra que lo obtenga sin el almacen */
            $sqlca->query("
            SELECT
                SUCUR.ruc,
                SUCUR.razsocial,
                SUCUR.ch_direccion
            FROM
                inv_ta_almacenes ALMA
                JOIN int_ta_sucursales SUCUR ON (SUCUR.ch_sucursal = ALMA.ch_sucursal)
            WHERE
                SUCUR.ebikey IS NOT NULL AND SUCUR.ebikey != ''
                AND ALMA.ch_clase_almacen = '1'
                AND ALMA.ch_sucursal = '" . $iAlmacen . "'
            ");
            $row = $sqlca->fetchRow();

            if(trim($row['ruc']) != '') {
                $res['ruc'] 		= trim($row['ruc']);
                $res['razsocial'] 	= trim($row['razsocial']);
                $arrDireccion = $this->getDireccionSucursal(trim($row['ch_direccion']), '');
                $res['direccion']	= $arrDireccion[1] . " " . $arrDireccion[2] . " "  . $arrDireccion[3] . " - "  . $arrDireccion[4] . " - "  . $arrDireccion[5];
            } else {
                $sqlca->query("
                SELECT DISTINCT
                    SUCUR.ruc,
                    SUCUR.razsocial,
                    SUCUR.ch_direccion
                FROM
                    inv_ta_almacenes ALMA
                    JOIN int_ta_sucursales SUCUR ON (SUCUR.ch_sucursal = ALMA.ch_sucursal)
                WHERE
                    SUCUR.ebikey IS NOT NULL AND SUCUR.ebikey != ''
                    AND ALMA.ch_clase_almacen = '1'
                ");
                $row = $sqlca->fetchRow();

                $res['ruc'] 		= trim($row['ruc']);
                $res['razsocial'] 	= trim($row['razsocial']);
                $arrDireccion = $this->getDireccionSucursal(trim($row['ch_direccion']), '');
                $res['direccion']	= $arrDireccion[1] . " " . $arrDireccion[2] . " "  . $arrDireccion[3] . " - "  . $arrDireccion[4] . " - "  . $arrDireccion[5];
            }
            return $res;
        }

        function _datosEmpresa($iAlmacen) {
            global $sqlca;

            $result = array();
            $sql = "
            SELECT
                TRIM(A .ch_nombre_almacen) AS _a,
                A .ch_almacen AS _b,
                A .ch_direccion_almacen AS _c
            FROM
                inv_ta_almacenes A
            WHERE
                A .ch_almacen = '" . $iAlmacen . "'
            UNION ALL
            SELECT
                ruc as _a, razsocial as _b, ch_direccion as _c
            FROM
                int_ta_sucursales
            WHERE
                ruc = (
                    SELECT DISTINCT
                        SUCUR.ruc
                    FROM
                        inv_ta_almacenes ALMA
                    JOIN int_ta_sucursales SUCUR ON (
                        SUCUR.ch_sucursal = ALMA.ch_sucursal
                    )
                    WHERE
                        ebiauth != '' AND ALMA.ch_sucursal = '" . $iAlmacen . "'
                );
            ";
            if($sqlca->query($sql) < 0) {
                return null;
            } else {
                while ($val = $sqlca->fetchRow()) {
                    $result[] = array($val[0],$val[1],$val[2]);
                }

                $res['razsocial'] 	= $result[1][0];
                $res['ruc']			= $result[1][1];
                $arrDireccion = $this->getDireccionSucursal($result[1][2], $row[0][2]);
                $res['direccion']	= $arrDireccion[1] . " " . $arrDireccion[2] . " "  . $arrDireccion[3] . " - "  . $arrDireccion[4] . " - "  . $arrDireccion[5];
                return $res;
            }
        }

        function getOIDLogo(){
            global $sqlca;

            $result = array();
            $sql = "
                SELECT
                    par_valor
                FROM
                    int_parametros
                WHERE
                    par_nombre = 'oidlog'
                LIMIT 1;
            ";
            error_log($sql);

            if($sqlca->query($sql) < 0) {
                return null;
            } else {
                $row = $sqlca->fetchRow();
                return $row['par_valor'];
            }
        }

        function getImageLargeObject($logo_oid){            
            $dbconn = pg_connect("host=localhost user=postgres dbname=integrado port=5432") or die('Could not connect: ' . pg_last_error());

            //Comenzamos transaccion
            pg_query($dbconn, "BEGIN") or die('BEGIN failed: ' . pg_last_error());

            //Recurso de large object
            $lo_handle = pg_lo_open($dbconn, $logo_oid, "r") or die('pg_lo_open failed: ' . pg_last_error());
                        
            //Leemos large object
            $logo_data = pg_lo_read($lo_handle, '50000') or die('pg_lo_read failed: ' . pg_last_error());
            if ($logo_data === false)
                return "";

            //Cerramos transaccion
            pg_lo_close($lo_handle) or die('pg_lo_close failed: ' . pg_last_error());
            pg_query($dbconn, "COMMIT;")  or die('COMMIT failed: ' . pg_last_error());
            pg_close($dbconn);
            
            return $logo_data;
        }

        function reportePdf($num_liquidacion, $ch_cliente, $Factura, $forma, $parametro_opcional) {
            $reporte_array = array();

            error_log('reportePdf');
            error_log('forma: ' . $forma);
            error_log('parametro_opcional: ' . $parametro_opcional);

            if ($forma == 'normal' && $parametro_opcional != 'POR-COBRAR') { //Funcion que se ejecuta cuando Tipo Operacion = 'Cliente Normal' y que no es cliente ANTICIPO
                $reporte_array = $this->GeneraDatosNormal($num_liquidacion, $ch_cliente);
            } else if ($forma == 'ND') { //Funcion que se ejecuta cuando Tipo Operacion = 'Cliente Nota Despacho'
                $reporte_array = $this->GeneraDatosND($num_liquidacion, $ch_cliente, $parametro_opcional);
            } else if ($forma == 'PLACA') { //Funcion que se ejecuta cuando Tipo Operacion = 'Cliente Placa'
                $reporte_array = $this->GeneraDatosPLACA($num_liquidacion, $ch_cliente, $parametro_opcional);
            } else if ($forma == 'PRODUCTO') { //Funcion que se ejecuta cuando Tipo Operacion = 'Cliente Producto'
                $reporte_array = $this->GeneraDatosPRODUCTO($num_liquidacion, $ch_cliente, $parametro_opcional);
            } else if ($forma == 'XCOBRAR' || $parametro_opcional == 'POR-COBRAR') { //Si es 'XCOBRAR' o 'POR-COBRAR', esto ocurre cuando es liquidacion de cliente ANTICIPO
                $reporte_array = $this->GeneraDatosXCOBRAR($num_liquidacion, $ch_cliente);
            }
            // echo "<script>console.log('" . json_encode($reporte_array) . "')</script>";

            $liquidacion = $num_liquidacion;
            $liquidacion = $reporte_array['DatosDoc'][$liquidacion][10]['ch_liquidacion'];
            ksort($reporte_array['DatosDoc'][$liquidacion]);

            $Cabecera = array(
                "DT_FECHA" => "FECHA",
                "CH_DOCUMENTO" => "# DESPACHO",
                "CH_NUMEVAL_MANUAL" => "# MANUAL",
                "CH_ARTICULO" => "ARTICULO",
                "CH_PLACA" => "PLACA",
                "CH_CONDUCTOR" => "CONDUCTOR",
                "ART_DESCRIPCION" => "DESCRIPCION",
                "ART_CANTIDAD" => "CANTIDAD",
                "ART_PRECIO" => "PRECIO",
                "ART_IMPORTE" => "IMPORTE",
                "NUMVALES" => "NUMERACION VALES",
            );

            $fontsize = 6.5;

            $reporte = new CReportes2();
            $reporte->SetMargins(12.7, 12.7, 12.7); //Margenes estrechos en Word 0.5 pulgadas o 12.7 milimetros
            $reporte->SetFont("courier", "", $fontsize);

            $reporte->definirColumna("CABECERA CLIENTE", $tipo->TIPO_TEXT, 100, "L", "_cabecera");
            $reporte->definirColumna("DT_FECHA", $reporte->TIPO_TEXTO, 11, "L");
            $reporte->definirColumna("CH_DOCUMENTO", $reporte->TIPO_TEXTO, 11, "C");
            $reporte->definirColumna("CH_NUMEVAL_MANUAL", $reporte->TIPO_TEXTO, 11, "C");
            $reporte->definirColumna("CH_ARTICULO", $reporte->TIPO_TEXTO, 15, "L");
            $reporte->definirColumna("CH_PLACA", $reporte->TIPO_TEXTO, 10, "L");
            $reporte->definirColumna("CH_CONDUCTOR", $reporte->TIPO_TEXTO, 21, "L");
            $reporte->definirColumna("ART_DESCRIPCION", $reporte->TIPO_TEXTO, 18, "L");
            $reporte->definirColumna("ART_CANTIDAD", $reporte->TIPO_TEXTO, 11, "L");
            $reporte->definirColumna("ART_PRECIO", $reporte->TIPO_TEXTO, 11, "L");
            $reporte->definirColumna("ART_IMPORTE", $reporte->TIPO_COSTO, 11, "L");

            $reporte->definirColumna("TOTALES X LIQ", $reporte->TIPO_TEXTO, 91, "R", "_totliq");
            $reporte->definirColumna("TOTDESPACHOS", $reporte->TIPO_TEXTO, 91, "L", "_totdespachos");
            $reporte->definirColumna("TOTNROVALES", $reporte->TIPO_TEXTO, 91, "L", "_totnrovales");

            /* TITULO Y DATOS DE LA EMPRESA */
            $reporte->definirCabecera(1, "L", "SISTEMA WEB");
            $reporte->definirCabecera(1, "C", "LIQUIDACION DE FACTURAS");
            $reporte->definirCabecera(1, "R", "PAG.%p");
            $reporte->definirCabecera(2, "L", " ");
            $reporte->definirCabecera(3, "L", "RAZON SOCIAL: " . $data['razsocial']);
            $reporte->definirCabecera(4, "L", "         RUC: " . trim($data['ruc']));
            $reporte->definirCabecera(5, "L", "   DIRECCION: " . trim($data['direccion']));
            $reporte->definirCabecera(6, "L", " ");
            $reporte->definirCabecera(7, "L", "Nro de Liquidacion:  ");
            $reporte->definirCabeceraPredeterminada($Cabecera);

            $datos = array();
            $c = 0;
            $x = 0;


            foreach ($reporte_array['DatosReg'] as $nro_liq => $valoresArray) {
                foreach ($valoresArray['CLIENTES'] as $llave => $valores) {
                    $iAlmacen = trim($reporte_array['DatosReg'][$nro_liq]["CLIENTES"][trim($llave)][0]['ch_sucursal']);
                    $data = $this->datosEmpresa($iAlmacen);
                }
                $data['fecha_liquidacion'] = $valoresArray['LIQUIDACION']['fecha_liquidacion'];
            }

            foreach ($reporte_array['DatosReg'] as $nro_liq => $valoresArray) {
                //echo "llave1 : $nro_liq => valor1 : ".$valoresArray."\n";
                if (!empty($nro_liq)) {
                    //echo "NRO LIQU. : $nro_liq <br>";
                    ksort($reporte_array['DatosDoc'][$nro_liq]);
                    //print_r($reporte_array['DatosDoc'][$nro_liq]);
                    //	$x=0;
                    $reporte->templates = Array();
                    $reporte->cabecera = Array();
                    $reporte->cabeceraImagen = Array();
                    $reporte->cabeceraSize = Array();
                    $reporte->cab_default = Array();

                    $reporte->definirColumna("CABECERA CLIENTE", $tipo->TIPO_TEXT, 100, "L", "_cabecera");
                    $reporte->definirColumna("DT_FECHA", $reporte->TIPO_TEXTO, 11, "L");
                    $reporte->definirColumna("CH_DOCUMENTO", $reporte->TIPO_TEXTO, 11, "C");
                    $reporte->definirColumna("CH_NUMEVAL_MANUAL", $reporte->TIPO_TEXTO, 11, "C");
                    $reporte->definirColumna("CH_ARTICULO", $reporte->TIPO_TEXTO, 15, "L");
                    $reporte->definirColumna("CH_PLACA", $reporte->TIPO_TEXTO, 10, "L");
                    $reporte->definirColumna("CH_CONDUCTOR", $reporte->TIPO_TEXTO, 21, "L");
                    $reporte->definirColumna("ART_DESCRIPCION", $reporte->TIPO_TEXTO, 27, "L");
                    $reporte->definirColumna("ART_CANTIDAD", $reporte->TIPO_TEXTO, 11, "L");
                    $reporte->definirColumna("ART_PRECIO", $reporte->TIPO_TEXTO, 11, "L");
                    $reporte->definirColumna("ART_IMPORTE", $reporte->TIPO_TEXTO, 11, "L");
                // $reporte->definirColumna("NUMVALES", $reporte->TIPO_TEXTO, 100, "L");

                    
                    $reporte->definirColumna("TOTALES", $reporte->TIPO_TEXTO, 119, "R","_totliq");//_totales
                    $reporte->definirColumna("TOTALES X LIQ", $reporte->TIPO_TEXTO, 23, "R","_totliq");//_totliq
                    $reporte->definirColumna("TOTDESPACHOS", $reporte->TIPO_TEXTO, 91, "L", "_totdespachos");
                    $reporte->definirColumna("TOTNROVALES", $reporte->TIPO_TEXTO, 91, "L", "_totnrovales");

                    $reporte->definirColumna("BLANCO", $reporte->TIPO_TEXTO, 91, "R", "_nrovales");
                    $reporte->definirColumna("NROVALES2", $reporte->TIPO_TEXTO, 91, "L", "_nrovales");                    

                    //OBTENEMOS IMAGEN DESDE RUTA DE SERVIDOR
                    // $ruta = "/sistemaweb/logocliente.jpg";
 
                    //Comprueba si existe el archivo y la ubicacion del archivo
                    // $existe = false;
                    // if (file_exists($ruta)) {
                    //     $existe = true;
                    // }

                    // if($existe){
                    //     $reporte->definirCabeceraImagen(1,3,$ruta, 100, 50);                                                 
                    // }

                    //OBTENEMOS IMAGEN DESDE LARGE OBJECT EN BASE DE DATOS
                    $ruta = "";
                    $oid = $this->getOIDLogo();
                    $large_object = ( is_null($oid) || empty($oid) ) ? "" : $this->getImageLargeObject($oid);

                    //Comprueba si existe large object
                    $existe = false;
                    if($large_object != ""){
                        $existe = true;
                    }

                    if($existe){
                        $reporte->definirCabeceraImagenLargeObject(1,3,$ruta, 100, 50, $large_object);                                                 
                    }

                    $reporte->definirCabeceraSize(4, "C", "courier,B,15", "LIQUIDACION DE FACTURAS");                                             
                    $reporte->definirCabeceraSize(5, "R", " ", " ");
                    $reporte->definirCabecera(5, "L", "SISTEMA WEB");                    
                    $reporte->definirCabecera(5, "C", "                                                                  PAG. %p");                    

                    $reporte->definirCabecera(6, "L", " ");
                    $reporte->definirCabecera(7, "L", "RAZON SOCIAL: " . $data['razsocial']);
                    $reporte->definirCabecera(8, "L", "         RUC: " . trim($data['ruc']));
                    $reporte->definirCabecera(9, "L", "   DIRECCION: " . trim($data['direccion']));
                    $reporte->definirCabecera(10, "L", " ");                      

                    foreach ($reporte_array['DatosDoc'][$nro_liq] as $tipDoc => $value) {

                        if (preg_match("/10-/i", $tipDoc)) {
                            if ($cliente == $value['cli_codigo'] && !empty($tipDoc)) {
                                $CODF .= $value['nro_documento'] . ",";
                                $TIP = '10';
                                //$Factura = "FAC.: ".$CODF;
                            } elseif ($cliente != $value['cli_codigo'] && !empty($tipDoc)) {
                                $CODN = " ";
                                $CODF = $value['nro_documento'] . ",";
                                $TIP = '10';
                                //$Factura = "FAC.: ".$CODF;
                            }
                        }

                        if (preg_match("/35-/i", $tipDoc)) {
                            // echo $cliente."br>;
                            if ($cliente == $value['cli_codigo'] && !empty($tipDoc)) {
                                $CODF .= $value['nro_documento'] . ",";
                                $TIP = '35';
                                //$Factura = "BOL.: ".$CODF;
                            } elseif ($cliente != $value['cli_codigo'] && !empty($tipDoc)) {
                                $CODN = " ";
                                $CODF = $value['nro_documento'] . ",";
                                $TIP = '35';
                                // $Factura = "BOL.: ".$CODF;
                            }
                        }



                        if (preg_match("/22-/i", $tipDoc)) {
                            if ($cliente != $value['cli_codigo'] && !empty($tipDoc)) {
                                $CODN = " ";
                                $CODF = "RESUMEN: " . $value['nro_documento'];
                                //$Factura = "RESUMEN FAC.: ".$CODF;
                            }
                        }

                        if (preg_match("/20-/i", $tipDoc)) {
                            if ($cliente == $value['cli_codigo'] && !empty($tipDoc)) {
                                $CODN .= $value['nro_documento'] . ",";
                                $NotaCredito = "N.C. : " . $CODN;
                            } elseif ($cliente != $value['cli_codigo'] && !empty($tipDoc)) {
                                $CODN = $value['nro_documento'] . ",";
                                $NotaCredito = "N.C. : " . $CODN;
                            }
                        }

                        $cliente = $value['cli_codigo'];
                        $CODN ? $MsgCodn = "NC.: " . $CODN . "" : $MsgCodn = " ";
                        $reporte->definirCabecera(11, "L", "Nro de Liquidacion: " . $nro_liq . ($TIP == "10" ? "  FAC.: " : "  BOL.: ") . $CODF . "  " . $MsgCodn . " ");
                    }

                    $reporte->definirCabecera(12, "L", "Fecha de Liquidacion: " . trim($data['fecha_liquidacion']));
                    $reporte->definirCabecera(13, "R", " ");
                    $reporte->definirCabeceraPredeterminada($Cabecera);
                    $reporte->AddPage();
                }

                foreach ($valoresArray['CLIENTES'] as $llave => $valores) {
                    if (!empty($llave)) {
                        $Despachos = count($valores);
                        $arrayCab = Array("CABECERA CLIENTE" => "CLIENTE : " . trim($llave));
                        $reporte->lineaH();
                        $reporte->Ln();
                        $reporte->nuevaFila($arrayCab, "_cabecera");
                        $reporte->lineaH();
                        $reporte->Ln();
                    }

                    $CantNroVales = 0;
                    $CantNrodespacho = 0;
                    $varNrodespacho = "";
                    $varNrovales = "";

                    foreach ($valores as $key => $value) {

                        //echo "llave : $key => valor : ".$value['ch_documento']."\n";
                        /*
                        if ($value['dt_fechaactualizacion'] == ""){
                        $datos['DT_FECHA'] = $value['fecha_insercion'];
                        }else{
                        $datos['DT_FECHA'] = $value['dt_fechaactualizacion'];
                        }
                        */
                        $datos['DT_FECHA'] = $value['fecha_insercion'];
                        $datos['CH_DOCUMENTO'] = $value['ch_documento'];
                        $datos['CH_NUMEVAL_MANUAL'] = $value['ch_numeval_manual'];
                        $datos['CH_ARTICULO'] = $value['ch_articulo'];
                        $datos['CH_PLACA'] = $value['placa'];
                        $datos['CH_CONDUCTOR'] = $value['conductor'];
                        $datos['ART_DESCRIPCION'] = $value['art_descripcion'];
                        $datos['ART_CANTIDAD'] = $value['art_cantidad'];
                        $datos['ART_PRECIO'] = $value['art_precio'];
                        $datos['ART_IMPORTE'] = $value['art_importe'];
                        $ImporteTotal +=$datos['ART_IMPORTE'];


                        if ($varNrodespacho != $value['ch_documento']) {
                            $CantNrodespacho++;
                        }

                        $varNrodespacho = $value['ch_documento'];

                        $NumVales = LiquidacionPDFTemplate::ObtNumVales($value['ch_documento'], $value['ch_sucursal'], $value['dt_fecha']);

                        $x = 0;
                        foreach ($NumVales['NUMEROS'] as $cod => $nro_vale) {
                            $x++;
                            $NumValesFinal = "";
                            foreach ($nro_vale as $numeros) {
                                $NumValesFinal.= $numeros;
                                if (($varNrovales != $numeros) and ($numeros != " ")) {
                                    $CantNroVales++;
                                }

                                $varNrovales = $numeros;
                            }
                            //$NumValesFinal .= 
                            //echo "NUM FINAL : $NumValesFinal<br>";
                            //echo "X = $x<br>";
                            if ($NumVales['CANT'] > 4 && $x > 1) {
                                $ArrayNumeros = array("BLANCO" => " ", "NROVALES2" => $NumValesFinal);
                                $reporte->nuevaFila($ArrayNumeros, "_nrovales");
                            } else {
                                $datos['NUMVALES'] = $NumValesFinal;



                                $reporte->nuevaFila($datos);
                            }
                        }
                        //$datos['NUMVALES']     = $NumVales['NUMEROS'];
                        $c++;
                        $x++;
                    }
                }

                //echo "CANT NRO VALES : $CantNroVales<br>";
                if (!empty($nro_liq)) {
                    //echo "CANT : ".count($valores)."";
                    $reporte->Ln();
                    $Total = 0;
                    $importecantidad=0;
                    for ($i = 0; $i < count($valores); $i++) {
                        $Total += $valores[$i]['art_importe'];
                        $importecantidad +=$valores[$i]['art_cantidad'];
                    }
                    $Total = number_format($Total, 2, ".", ",");
                    $importecantidad = number_format($importecantidad, 3, ".", ",");

                    $arrayCab['TOTALES']=" TOTALES: ".$importecantidad;
                    $arrayCab['TOTALES X LIQ'] = $Total;
                    

                // $arrayCab = Array("TOTALES X LIQ" => "         TOTAL : " . $Total."","TOTALES"=>"jjdjfjdsjf");
                // $arrayCabd = Array("TOTALES" => "   " . $Total." ");
                    $arrayDesp = Array("TOTDESPACHOS" => "CANTIDAD TOTAL DE DESPACHOS : " . $CantNrodespacho);
                    $arrayVales = Array("TOTNROVALES" => "CANTIDAD TOTAL DE NROS DE VALES : " . $CantNroVales);
                    $reporte->lineaH();
                    $reporte->Ln();
                    $reporte->nuevaFila($arrayCab,"_totliq");//, "_totliq"
                // $reporte->nuevaFila($arrayCabd, "_totales");
                    $reporte->nuevaFila($arrayDesp, "_totdespachos");
                    $reporte->nuevaFila($arrayVales,"_totnrovales");
                    $reporte->Ln();
                    $reporte->lineaH();
                    $reporte->Ln();
                    $x = 0;
                }

                $TotCantNroVales += $CantNroVales;
            }

            return $reporte->Output("/sistemaweb/ventas_clientes/reportes/pdf/liquidacion_facturas.pdf", "I");
        }

    }

    // echo "<script>console.log('REQUEST: " . json_encode($_REQUEST) . "')</script>";

    $ch_liquidacion = $_REQUEST['ch_liquidacion'];
    $ch_cliente = $_REQUEST['ch_cliente'];
    $Factura = $_REQUEST['ch_documneto'];
    $forma = $_REQUEST['forma'];
    $parametro_opcinal = $_REQUEST['parametro_accion'];

    error_log( json_encode( array( $ch_liquidacion, $ch_cliente, $Factura, $forma, $parametro_opcinal ) ) );

    $ReportePdf = new LiquidacionPDFTemplate();
    print_r($ReportePdf->reportePdf($ch_liquidacion, $ch_cliente, $Factura, $forma, $parametro_opcinal));
