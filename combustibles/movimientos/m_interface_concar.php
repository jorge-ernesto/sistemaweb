<?php

/*
  Fecha de creacion     : ?
  Autor                 : Rocío Rosales
  Fecha de modificacion : 28/02/2012 2:20pm.
  Modificado por        : Néstor Hernández Loli

  Néstor: Bajo requerimiento de Servigrifos se pidió actualizar la interfaz Concar
  para que trabajase con inserciones SQL directas a la base de datos, ello motiva
  un cambio en el código de este archivo.
 */

function CSVFromQuery($sql, $file, $headers, $columnas, $texto) {
    global $sqlca;

    if ($sqlca->query($sql) <= 0) {
        return false;
    }
    $numrows = $sqlca->numrows();

    $fh = fopen($file, "w");
    if ($fh === false) {
        return false;
    }
    $line = "";

    foreach ($headers as $num => $val) {
        $line .= (($num == 0) ? "" : ",") . $val;
    }

    fwrite($fh, $line . "\r\n");

    fwrite($fh, $texto . "\r\n");

    fclose($fh);
    return true;
}

class InterfaceConcarModel extends Model {

    function ListadoAlmacenes($codigo) {
        global $sqlca;
        $cond = ' ';
        if ($codigo != "") {
            $cond = " AND trim(ch_sucursal) = '" . pg_escape_string($codigo) . "' ";
        }

        $query = "SELECT ch_almacen FROM inv_ta_almacenes WHERE trim(ch_clase_almacen)='1'"
                . $cond . "ORDER BY ch_almacen";

        if ($sqlca->query($query) <= 0) {
            return $sqlca->get_error();
        }
        $numrows = $sqlca->numrows();

        $x = 0;
        while ($reg = $sqlca->fetchRow()) {
            if ($numrows > 1) {
                if ($x < $numrows - 1) {
                    $conc = ".";
                } else {
                    $conc = "";
                }
            }

            $listado[' ' . $codigo . ' '] .= trim($reg[0]) . $conc;
            $x++;
        }
        return $listado;
    }

    function interface_fn_opensoft_concar_ventas($FechaIni, $FechaFin, $CodAlmacen) {
        ///////////////////////// VENTAS ///////////////////////////////
        global $sqlca;

        $modulo = "TODOS";
        $FechaIni_Original = $FechaIni;
        $FechaFin_Original = $FechaFin;

        //N: Dividiendo la fecha para convertirla a formato yyyy/MM/dd
        $FechaDiv = explode("/", $FechaIni);
        $FechaIni = $FechaDiv[2] . "-" . $FechaDiv[1] . "-" . $FechaDiv[0];

        //N: Obteniendo cuál tabla capturar los datos
        $postrans = "pos_trans" . $FechaDiv[2] . $FechaDiv[1];

        //N: El nombre del archivo
        $ZipFile = "Ventas" . $CodAlmacen . "del" . $FechaDiv[2] . $FechaDiv[1] . $FechaDiv[0] . "al";

        $FechaDiv = explode("/", $FechaFin);
        $FechaFin = $FechaDiv[2] . "-" . $FechaDiv[1] . "-" . $FechaDiv[0];


        $ZipFile .= $FechaDiv[2] . $FechaDiv[1] . $FechaDiv[0] . ".zip";

        //N: Proceso de validación
        if (("pos_trans" . $FechaDiv[2] . $FechaDiv[1]) != $postrans) {
            return "INVALID_DATE";
        }
        if (strlen($FechaIni) < 10 || strlen($FechaFin) < 10) {
            return "INVALID_DATE";
        }
        //N: ?
        if (($sqlca->query("SELECT tipo FROM pos_cfg WHERE es='$CodAlmacen';") <= 0 )
                && ($CodAlmacen != "all")) {
            return "INVALID_DATE";
        }
        $reg = $sqlca->fetchRow();
        $TipoAlmacen = trim($reg[0]);

        $ExportDir = "/home/data/";

        if ($TipoAlmacen == "M" || $TipoAlmacen == "m") {
            $prefijo_tipo = "m-";
            $nombre_tipo = "Market";
        } else {
            $prefijo_tipo = "t-";
            $nombre_tipo = "Grifos";
        }
        //N : fin de ?
        //N: Obteniendo datos de la configuración
        $ventaCC = pg_exec("SELECT venta_cuenta_cliente from concar_config;");
        $vcc_array = pg_fetch_row($ventaCC, 0);
        $vccliente = $vcc_array[0];

        $razsoc_sql = pg_exec("SELECT par_valor from int_parametros where par_nombre= 'desces';");
        $razsoc_array = pg_fetch_row($razsoc_sql, 0);
        $razsoc = $razsoc_array[0];

        /*         * ************* Detalles de Ventas PLAYA************* */

        if ($modulo == "TODOS" || $modulo == "VALES") {

            /*             * ****** VENTAS - COMBUSTIBLES - BOLETAS  ******* */
            $sql = "select * from 
                (select to_char(date(dia),'YYMMDD') as dia, 
                    venta_cuenta_cliente::text as DCUENTA, '99999999999'::text as codigo, 
                    ' '::text as trans, '1'::text as tip, 'D'::text as ddh, 
                    round(sum(importe),2) as importe, 
                    'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||'-" . $razsoc . "'::text as venta , 
                    es as sucursal, '00'||MIN(cast(trans as integer))||' - 00'||MAX(cast(trans as integer))::text as dnumdoc,
		   venta_subdiario::text as subdiario, id_cencos_comb::text as DCENCOS 
                   from pos_trans" . $FechaDiv[2] . $FechaDiv[1] . ", concar_config
	           where td = 'B' 
                   and tipo = 'C' 
                   and  date (dia) 
                   between to_date('$FechaIni_Original','DD/MM/YYYY') and to_date('$FechaFin_Original','DD/MM/YYYY')";

            if ($CodAlmacen != "all") {
                $sql .= " and es = '" . $CodAlmacen . "' ";
            }

            $sql .= "group by dia, es, venta_cuenta_cliente, subdiario, id_cencos_comb Order by dia) as A 
                UNION
                (select to_char(date(dia),'YYMMDD') as dia, 
                venta_cuenta_impuesto::text as DCUENTA, '99999999999'::text as codigo, 
		' '::text as trans, '1'::text as tip, 'H'::text as ddh, 
                round(sum(igv),2) as importe, 
		'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||'-" . $razsoc . "'::text as venta , 
		es as sucursal, '00'||MIN(cast(trans as integer))||' - 00'||MAX(cast(trans as integer))::text as dnumdoc,
		venta_subdiario::text as subdiario, id_cencos_comb::text as DCENCOS
		from pos_trans" . $FechaDiv[2] . $FechaDiv[1] . ", concar_config
		where td = 'B' 
                and tipo='C' 
                and date(dia) 
                between to_date('$FechaIni_Original','DD/MM/YYYY') and to_date('$FechaFin_Original','DD/MM/YYYY')";

            if ($CodAlmacen != "all") {
                $sql .= " and es = '" . $CodAlmacen . "' ";
            }

            $sql .= "group by dia, es, venta_cuenta_impuesto, subdiario, id_cencos_comb Order by dia) 
                  UNION 
                  (select to_char(date(dia),'YYMMDD') as dia, 
                  venta_cuenta_ventas::text as DCUENTA, codigo_concar,  
                  ' '::text as trans, '1'::text as tip, 'H'::text as ddh, 
                  round(sum(importe-igv),2) as importe, 
                  'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||'-" . $razsoc . "'::text as venta, 
		  es as sucursal, ' '::text as dnumdoc, 
                  venta_subdiario::text as subdiario,id_cencos_comb::text as DCENCOS
		  from pos_trans" . $FechaDiv[2] . $FechaDiv[1] . ", concar_config, interface_equivalencia_producto
		  where td = 'B' 
                  and tipo='C' 
                  and date(dia) 
                  between to_date('$FechaIni_Original','DD/MM/YYYY') and to_date('$FechaFin_Original','DD/MM/YYYY') 
                  and codigo=art_codigo";

            if ($CodAlmacen != "all") {
                $sql .= " and es = '" . $CodAlmacen . "' ";
            }

            /*             * ****** VENTAS - COMBUSTIBLES - FACTURAS  ******* */
            $sql .= "group by dia,codigo_concar,es, venta_cuenta_ventas, subdiario, id_cencos_comb Order by dia) 
		   UNION 
                   (select to_char(date(dia),'YYMMDD') as dia, 
                   venta_cuenta_cliente::text as DCUENTA, ruc::text as codigo, 
                   trans::text as trans, '1'::text as tip, 'D'::text as ddh, 
		   round(importe, 2) as importe, 
		   'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||'-" . $razsoc . "'::text as venta , 
		   es as sucursal ,  '00'||trans::text as dnumdoc,
	           venta_subdiario::text as subdiario, id_cencos_comb::text as DCENCOS
		   from pos_trans" . $FechaDiv[2] . $FechaDiv[1] . ", concar_config
	           where td = 'F' 
                   and tipo='C'
                   and date(dia) 
                   between to_date('$FechaIni_Original','DD/MM/YYYY') and to_date('$FechaFin_Original','DD/MM/YYYY') 
                   and importe>0";

            if ($CodAlmacen != "all") {
                $sql .= " and es = '" . $CodAlmacen . "' ";
            }
            $sql .= ") UNION 
                (select to_char(date(dia),'YYMMDD') as dia,  
                  venta_cuenta_impuesto::text as DCUENTA, ruc::text as codigo, 
		  trans::text as trans, '1'::text as tip,  'H'::text as ddh, 
		  round(igv,2) as importe, 
                  'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||'-" . $razsoc . "'::text as venta , 
		  es as sucursal, '00'||trans::text as dnumdoc,
		  venta_subdiario::text as subdiario, id_cencos_comb::text as DCENCOS
                  from pos_trans" . $FechaDiv[2] . $FechaDiv[1] . ", concar_config
                  where td = 'F' 
                  and tipo='C' 
                  and date(dia) 
                  between to_date('$FechaIni_Original','DD/MM/YYYY') and to_date('$FechaFin_Original','DD/MM/YYYY') 
                  and importe>0 ";

            if ($CodAlmacen != "all") {
                $sql .= " and es = '" . $CodAlmacen . "' ";
            }

            $sql .= ") UNION 
                (select to_char(date(dia),'YYMMDD') as dia, 
                venta_cuenta_ventas::text as DCUENTA, codigo_concar, 
                trans::text as trans, '1'::text as tip, 'H'::text as ddh, 
                (round(importe,2)-round(igv,2)) as importe, 
                'VENTA COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||'-" . $razsoc . "'::text as venta, 
		es as sucursal, '00'||trans::text as dnumdoc,
		venta_subdiario::text as subdiario, id_cencos_comb::text as DCENCOS
		from pos_trans" . $FechaDiv[2] . $FechaDiv[1] . ", concar_config, interface_equivalencia_producto
                where td = 'F' 
                and tipo='C' 
                and date(dia) 
                between to_date('$FechaIni_Original','DD/MM/YYYY') and to_date('$FechaFin_Original','DD/MM/YYYY') 
                and importe>0 
                and codigo=art_codigo";

            if ($CodAlmacen != "all") {
                $sql .= " and es = '" . $CodAlmacen . "' ";
            }
            $sql .= ") 
                ORDER BY dia, tip, trans, ddh, DCUENTA;";

            $headers = array(
                0 => "DSUBDIA.C.4",
                1 => "DCOMPRO.C.6",
                2 => "DSECUE.C.4",
                3 => "DFECCOM.C.6",
                4 => "DCUENTA.C.12",
                5 => "DCODANE.C.18",
                6 => "DCENCOS.C.6",
                7 => "DCODMON.C.2",
                8 => "DDH.C.1",
                9 => "DIMPORT.N.14.2",
                10 => "DTIPDOC.C.2",
                11 => "DNUMDOC.C.20",
                12 => "DFECDOC.C.6",
                13 => "DFECVEN.C.6",
                14 => "DAREA.C.3",
                15 => "DFLAG.C.1",
                16 => "DDATE.D",
                17 => "DXGLOSA.C.30",
                18 => "DUSIMPOR.N.14.2",
                19 => "DMNIMPOR.N.14.2",
                20 => "DCODARC.C.2",
                21 => "DFECCOM2.C.8",
                22 => "DFECDOC2.C.8",
                23 => "DVANEXO.C.1",
                24 => "DCODANE2.C.18"
            );

            $q1 = "Create table tmp_concar 
                   (dsubdia character varying(7), 
                   dcompro character varying(6), 
                   dsecue character varying(7), 
                   dfeccom character varying(6), 
                   dcuenta character varying(12), 
                   dcodane character varying(18), 
                   dcencos character varying(6), 
                   dcodmon character varying(2), 
                   ddh character varying(1), 
                   dimport numeric(14,2), 
                   dtipdoc character varying(2), 
                   dnumdoc character varying(20), 
		   dfecdoc character varying(8), 
                   dfecven character varying(8), 
                   darea character varying(3), 
                   dflag character varying(1), 
                   ddate date not null, 
                   dxglosa character varying(40),
		   dusimport numeric(14,2), 
                   dmnimpor numeric(14,2), 
                   dcodarc character varying(2), 
                   dfeccom2 character varying(8), 
                   dfecdoc2 character varying(8), 
                   dvanexo character varying(1), 
                   dcodane2 character varying(18));";
            $sqlca->query($q1);

            $texto = "";
            $correlativo = 0;
            $contador = '0000';
            $k = 0;

            if ($sqlca->query($sql) > 0) {
                $correlativo = $FechaDiv[1] * 10000;
                while ($reg = $sqlca->fetchRow()) {
                    if (trim($reg[1]) == $vccliente) {
                        $k = 1;
                        $correlativo = $correlativo + 1;
                        if ($FechaDiv[1] >= 01 && $FechaDiv[1] < 10) {
                            $correlativo2 = "0" . $correlativo;
                        } else {
                            $correlativo2 = $correlativo;
                        }
                    } else {
                        $k = $k + 1;
                    }
                    if (trim($reg[9]) == '') {
                        $reg[9] = $xtradat;
                    } else {
                        $xtradat = trim($reg[9]);
                    }
                    $contador = " 000" . $k . " ";
                    $dfecdoc = "20" . substr($reg[0], 0, 2) . substr($reg[0], 2, 2) .
                            substr($reg[0], 4, 2);

                    $texto .= trim($reg[10]) . "," . $correlativo2 . "," .
                            $contador . "," . trim($reg[0]) . "," . trim($reg[1]) .
                            "," . trim($reg[2]) . "," . trim($reg[11]) . "," .
                            'MN' . "," . trim($reg[5]) . "," . trim($reg[6]) .
                            "," . 'TK' . "," . trim($reg[9]) . "," .
                            trim($reg[0]) . "," . trim($reg[0]) . "," . 'S' .
                            "," . 'S' . "," . $dfecdoc . "," . trim($reg[7]) .
                            "," . '0' . "," . trim($reg[6]) . "," . 'X' . "," .
                            $dfecdoc . "," . $dfecdoc . "," . 'P' . "," . ' ' .
                            "\r\n";
                    $q2 = pg_exec("Insert into tmp_concar values ('" . trim($reg[10]) . "', '" . $correlativo2 . "', '" . $contador . "', '" . trim($reg[0]) . "', '" . trim($reg[1]) . "', '" . trim($reg[2]) . "', '" . trim($reg[11]) . "', 'MN', '" . trim($reg[5]) . "', '" . trim($reg[6]) . "', 'TK', '" . trim($reg[9]) . "', '" . trim($reg[0]) . "', '" . trim($reg[0]) . "', 'S', 'S', '" . $dfecdoc . "', '" . trim($reg[7]) . "', '0', '" . trim($reg[6]) . "', 'X', '" . $dfecdoc . "', '" . $dfecdoc . "', 'P', ' '); ");
                }
            }


            // creando el vector de diferencia
            $c = 0;
            $imp = 0;
            $flag = 0;
            $que = "Select * from tmp_concar; ";
            if ($sqlca->query($que) > 0) {
                while ($reg = $sqlca->fetchRow()) {
                    if (trim($reg[4]) == '121101') {
                        if ($flag == 1) {
                            $vec[$c] = $imp;
                            $c = $c + 1;
                        }
                        $imp = trim($reg[9]);
                    } else {
                        $imp = round(($imp - $reg[9]), 2);
                        $flag = 1;
                    }
                }
                $vec[$c] = 0;
            }

            // actualizar tabla tmp_concar sumando las diferencias al igv
            $k = 0;
            if ($sqlca->query($que) > 0) {
                while ($reg = $sqlca->fetchRow()) {
                    if (trim($reg[4] == '401110')) {
                        $dif = $reg[9] + $vec[$k];
                        $k = $k + 1;
                        $sale = pg_exec("Update tmp_concar set dimport = " . $dif . " 
                            where dcompro = '" . trim($reg[1]) . "' 
                           and dcuenta='401110'
                           and dcodane='99999999999';");
                    }
                }
            }

            // pasando la nueva tabla a texto2
            $qfinal = "Select * from tmp_concar order by dcompro, dcuenta; ";
            $texto2 = "";
            if ($sqlca->query($qfinal) > 0) {
                while ($reg = $sqlca->fetchRow()) {
                    $texto2 .= $reg[0] . "," . $reg[1] . "," . $reg[2] . "," . $reg[3] . "," . $reg[4] . "," . $reg[5] . "," . $reg[6] . "," . $reg[7] . "," . $reg[8] . "," . $reg[9] . ",";
                    $texto2 .= $reg[10] . "," . $reg[11] . "," . $reg[12] . "," . $reg[13] . "," . $reg[14] . "," . $reg[15] . "," . $reg[16] . "," . $reg[17] . "," . $reg[18] . "," . $reg[9] . ",";
                    $texto2 .= $reg[20] . "," . $reg[21] . "," . $reg[22] . "," . $reg[23] . "," . $reg[24] . "\r\n";
                }
            }

            CSVFromQuery($qfinal, $ExportDir . "CDVENTAP.csv", $headers, 25, $texto2);
            $q5 = pg_exec("Drop table tmp_concar");


            /*             * *************** Cabecera de Ventas PLAYA ********** */

            $headers = Array(0 => "CSUBDIA.C.4",
                1 => "CCOMPRO.C.6",
                2 => "CFECCOM.C.6",
                3 => "CCODMON.C.2",
                4 => "CSITUA.C.1",
                5 => "CTIPCAM.N.10.4",
                6 => "CGLOSA.C.40",
                7 => "CTOTAL.N.14.2",
                8 => "CTIPO.C.1",
                9 => "CFLAG.C.1",
                10 => "CDATE.D",
                11 => "CHORA.C.8",
                12 => "CUSER.C.10",
                13 => "CFECCOM2.C.10",
                14 => "COPCION.C.1",
                15 => "CFECCAM.C.6",
                16 => "CFORM.C.1",
                17 => "CTIPCOM.C.2",
                18 => "CEXTOR.C.1",
                19 => "CORIG.C.2"
            );

            $correlativo = 0;
            ;
            $texto = '';
            if ($sqlca->query($sql) > 0) {
                $correlativo = $FechaDiv[1] * 10000;
                while ($reg = $sqlca->fetchRow()) {
                    if (trim($reg[1]) == $vccliente) {
                        $correlativo = $correlativo + 1;
                        if ($FechaDiv[1] >= 01 && $FechaDiv[1] < 10) {
                            $correlativo2 = "0" . $correlativo;
                        } else {
                            $correlativo2 = $correlativo;
                        }

                        $dfecdoc = "20" . substr($reg[0], 0, 2) . substr($reg[0], 2, 2) . substr($reg[0], 4, 2);
                        $texto .= trim($reg[10]) . "," . $correlativo2 . "," . trim($reg[0]) . "," . 'MN' . "," . 'X' . "," . '0' . ",";
                        $texto .= 'VTA PLAYA' . trim($reg[0]) . "," . '0' . "," . 'V' . "," . 'S' . "," . $dfecdoc . "," . '00:00:00' . "," . 'OS' . "," . $dfecdoc . "," . 'S' . "," . ' ' . "," . ' ' . "," . ' ' . "," . ' ' . "," . ' ';
                        $texto .= "\r\n";
                    }
                }
                CSVFromQuery($sql, $ExportDir . "CCVENTAP.csv", $headers, 20, $texto);
            }


            /*             * ************* Detalles de Ventas MARKET ************* */

            /*             * ****** VENTAS - MARKET - BOLETAS  ******* */

            $sql = " select * from 
                (select to_char(date(dia),'YYMMDD') as dia,  
                 venta_cuenta_cliente::text as DCUENTA,  '99999999999'::text as codigo, 
                 ' '::text as trans, '2'::text as tip, 'D'::text as ddh, 
                 round(sum(importe),2) as importe,  
                 'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||'-" . $razsoc . "'::text as venta , 
		 es as sucursal,  
                 '00'||MIN(cast(trans as integer))||' - 00'||MAX(cast(trans as integer))::text as dnumdoc,
                 venta_subdiario_market::text as subdiario, id_centrocosto::text as DCENCOS
		 from pos_trans" . $FechaDiv[2] . $FechaDiv[1] . ", concar_config
		 where td = 'B' and tipo = 'M' 
                 and date (dia) 
                between to_date('$FechaIni_Original','DD/MM/YYYY') and to_date('$FechaFin_Original','DD/MM/YYYY')";

            if ($CodAlmacen != "all") {
                $sql .= " and es = '" . $CodAlmacen . "' ";
            }

            $sql .= "group by dia, es, venta_cuenta_cliente, subdiario, id_centrocosto 
                Order by dia) as K
		UNION 
                (select to_char(date(dia),'YYMMDD') as dia,  
                venta_cuenta_impuesto::text as DCUENTA, 
                '99999999999'::text as codigo,  
                ' '::text as trans,  '2'::text as tip, 'H'::text as ddh, 
		 round(sum(igv),2) as importe, 
                 'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||'-" . $razsoc . "'::text as venta , 
		 es as sucursal ,  
                '00'||MIN(cast(trans as integer))||' - 00'||MAX(cast(trans as integer))::text as dnumdoc,
		 venta_subdiario_market::text as subdiario, 
                 id_centrocosto::text as DCENCOS 
                 from pos_trans" . $FechaDiv[2] . $FechaDiv[1] . ", concar_config
                 where td = 'B' 
                 and tipo='M' 
                 and date(dia) between to_date('$FechaIni_Original','DD/MM/YYYY') and to_date('$FechaFin_Original','DD/MM/YYYY')";
            if ($CodAlmacen != "all") {
                $sql .= " and es = '" . $CodAlmacen . "' ";
            }

            $sql .= "group by dia, es, venta_cuenta_impuesto, subdiario, id_centrocosto
                Order by dia) 
                UNION 
                (select to_char(date(dia),'YYMMDD') as dia, 
		venta_cuenta_ventasm::text as DCUENTA, 
                codigo_concar::text as codigo, 
                ' '::text as trans,  '2'::text as tip, 'H'::text as ddh, 
		round(sum(importe-igv),2) as importe,  
                'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||'-" . $razsoc . "'::text as venta, 
		es as sucursal , 
                '00'||MIN(cast(trans as integer))||' - 00'||MAX(cast(trans as integer))::text::text as dnumdoc,
		venta_subdiario_market::text as subdiario,
                id_centrocosto::text as DCENCOS
		from pos_trans" . $FechaDiv[2] . $FechaDiv[1] . ", concar_config, interface_equivalencia_producto
		where td = 'B' 
                and tipo='M' 
                and date(dia) 
                between to_date('$FechaIni_Original','DD/MM/YYYY') and to_date('$FechaFin_Original','DD/MM/YYYY') 
                and art_codigo='MARKET'";

            if ($CodAlmacen != "all") {
                $sql .= " and es = '" . $CodAlmacen . "' ";
            }

            /*             * ****** VENTAS - PLAYA - FACTURAS  ******* */
            $sql .= " group by dia, es, venta_cuenta_ventasm, subdiario, id_centrocosto, codigo_concar 
                Order by dia) 
                UNION 
                (select to_char(date(dia),'YYMMDD') as dia, 
		venta_cuenta_cliente::text as DCUENTA, 
                ruc::text as codigo, 
                trans::text as trans, '2'::text as tip,  'D'::text as ddh, 
		round(sum(importe),2) as importe, 
                'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||'-" . $razsoc . "'::text as venta , 
                es as sucursal,  '00'||trans::text as dnumdoc,
                venta_subdiario_market::text as subdiario,
                id_centrocosto::text as DCENCOS
		from pos_trans" . $FechaDiv[2] . $FechaDiv[1] . ", concar_config
		where td = 'F' 
                and tipo='M'
                and date(dia) 
               between to_date('$FechaIni_Original','DD/MM/YYYY') and to_date('$FechaFin_Original','DD/MM/YYYY') and importe>0";

            if ($CodAlmacen != "all") {
                $sql .= " and es = '" . $CodAlmacen . "' ";
            }

            $sql .= " group by dia, trans,ruc,es, venta_cuenta_cliente, subdiario, id_centrocosto
                order by dia) 
                UNION
                (select to_char(date(dia),'YYMMDD') as dia, 
		venta_cuenta_impuesto::text as DCUENTA,  
		ruc::text as codigo, 
                trans::text as trans, '2'::text as tip, 'H'::text as ddh, 
		round(sum(igv),2) as importe, 
		'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||'-" . $razsoc . "'::text as venta , 
		es as sucursal , 
		'00'||trans::text as dnumdoc,
		venta_subdiario_market::text as subdiario,
		id_centrocosto::text as DCENCOS
		from pos_trans" . $FechaDiv[2] . $FechaDiv[1] . ", concar_config
		where td = 'F' and tipo='M' and date(dia) between to_date('$FechaIni_Original','DD/MM/YYYY') and to_date('$FechaFin_Original','DD/MM/YYYY') and importe>0";
            if ($CodAlmacen != "all") {
                $sql .= " and es = '" . $CodAlmacen . "' ";
            }

            $sql .= " group by dia, trans, es, venta_cuenta_impuesto, subdiario, ruc, id_centrocosto
		order by dia) 
                UNION 
                (select to_char(date(dia),'YYMMDD') as dia, 
		venta_cuenta_ventasm::text as DCUENTA,  
		codigo_concar::text as codigo,
		trans::text as trans, '2'::text as tip, 'H'::text as ddh, 
                round(sum(importe-igv),2) as importe, 
		'VENTA MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||'-" . $razsoc . "'::text as venta, 
		es as sucursal, 
		'00'||trans::text as dnumdoc,
		venta_subdiario_market::text as subdiario,
		id_centrocosto::text as DCENCOS
		from pos_trans" . $FechaDiv[2] . $FechaDiv[1] . ", concar_config, interface_equivalencia_producto
		where td = 'F' 
                and tipo='M' 
                and date(dia) between to_date('$FechaIni_Original','DD/MM/YYYY') and to_date('$FechaFin_Original','DD/MM/YYYY') 
                and importe>0  
                and art_codigo='MARKET'";

            if ($CodAlmacen != "all") {
                $sql .= " and es = '" . $CodAlmacen . "' ";
            }
            $sql .= " group by dia, trans, es, venta_cuenta_ventasm, subdiario, id_centrocosto, codigo_concar
		   order by dia) 
                   ORDER BY dia, tip, trans, ddh, DCUENTA;";

            $headers = Array(0 => "DSUBDIA.C.4",
                1 => "DCOMPRO.C.6",
                2 => "DSECUE.C.4",
                3 => "DFECCOM.C.6",
                4 => "DCUENTA.C.12",
                5 => "DCODANE.C.18",
                6 => "DCENCOS.C.6",
                7 => "DCODMON.C.2",
                8 => "DDH.C.1",
                9 => "DIMPORT.N.14.2",
                10 => "DTIPDOC.C.2",
                11 => "DNUMDOC.C.20",
                12 => "DFECDOC.C.6",
                13 => "DFECVEN.C.6",
                14 => "DAREA.C.3",
                15 => "DFLAG.C.1",
                16 => "DDATE.D",
                17 => "DXGLOSA.C.30",
                18 => "DUSIMPOR.N.14.2",
                19 => "DMNIMPOR.N.14.2",
                20 => "DCODARC.C.2",
                21 => "DFECCOM2.C.8",
                22 => "DFECDOC2.C.8",
                23 => "DVANEXO.C.1",
                24 => "DCODANE2.C.18"
            );

            $texto = "";
            $correlativo = 0;
            $contador = '0000';
            $k = 0;

            if ($sqlca->query($sql) > 0) {
                $correlativo = $FechaDiv[1] * 10000;
                while ($reg = $sqlca->fetchRow()) {
                    if (trim($reg[1]) == $vccliente) {
                        $k = 1;
                        $correlativo = $correlativo + 1;
                        if ($FechaDiv[1] >= 01 && $FechaDiv[1] < 10) {
                            $correlativo2 = "0" . $correlativo;
                        } else {
                            $correlativo2 = $correlativo;
                        }
                    } else {
                        $k = $k + 1;
                    }
                    if (trim($reg[9]) == '') {
                        $reg[9] = $xtradat;
                    } else {
                        $xtradat = trim($reg[9]);
                    }
                    $contador = " 000" . $k . " ";
                    $dfecdoc = "20" . substr($reg[0], 0, 2) . substr($reg[0], 2, 2) . substr($reg[0], 4, 2);
                    $texto .= trim($reg[10]) . "," . $correlativo2 . "," . $contador . "," . trim($reg[0]) . "," . trim($reg[1]) . "," . trim($reg[2]) . "," . trim($reg[11]) . "," . 'MN' . "," . trim($reg[5]) . "," . trim($reg[6]) . "," . 'TK' . "," . trim($reg[9]) . ",";
                    $texto .= trim($reg[0]) . "," . trim($reg[0]) . "," . 'S' . "," . 'S' . "," . $dfecdoc . "," . trim($reg[7]) . "," . '0' . "," . trim($reg[6]) . "," . 'X' . "," . $dfecdoc . "," . $dfecdoc . "," . 'P' . "," . ' ';
                    $texto .= "\r\n";
                }
            }
            CSVFromQuery($sql, $ExportDir . "CDVENTAM.csv", $headers, 25, $texto);


            /*             * ************* Cabecera de Ventas MARKET ************* */

            $headers = Array(0 => "CSUBDIA.C.4",
                1 => "CCOMPRO.C.6",
                2 => "CFECCOM.C.6",
                3 => "CCODMON.C.2",
                4 => "CSITUA.C.1",
                5 => "CTIPCAM.N.10.4",
                6 => "CGLOSA.C.40",
                7 => "CTOTAL.N.14.2",
                8 => "CTIPO.C.1",
                9 => "CFLAG.C.1",
                10 => "CDATE.D",
                11 => "CHORA.C.8",
                12 => "CUSER.C.10",
                13 => "CFECCOM2.C.10",
                14 => "COPCION.C.1",
                15 => "CFECCAM.C.6",
                16 => "CFORM.C.1",
                17 => "CTIPCOM.C.2",
                18 => "CEXTOR.C.1",
                19 => "CORIG.C.2"
            );

            $correlativo = 0;
            ;
            $texto = '';
            if ($sqlca->query($sql) > 0) {
                $correlativo = $FechaDiv[1] * 10000;
                while ($reg = $sqlca->fetchRow()) {
                    if (trim($reg[1]) == $vccliente) {
                        $correlativo = $correlativo + 1;
                        if ($FechaDiv[1] >= 01 && $FechaDiv[1] < 10) {
                            $correlativo2 = "0" . $correlativo;
                        } else {
                            $correlativo2 = $correlativo;
                        }

                        $dfecdoc = "20" . substr($reg[0], 0, 2) . substr($reg[0], 2, 2) . substr($reg[0], 4, 2);
                        $texto .= trim($reg[10]) . "," . $correlativo2 . "," . trim($reg[0]) . "," . 'MN' . "," . 'X' . "," . '0' . ",";
                        $texto .= 'VTA MARKET' . trim($reg[0]) . "," . '0' . "," . 'V' . "," . 'S' . "," . $dfecdoc . "," . '00:00:00' . "," . 'OS' . "," . $dfecdoc . "," . 'S' . "," . ' ' . "," . ' ' . "," . ' ' . "," . ' ' . "," . ' ';
                        $texto .= "\r\n";
                    }
                }
                CSVFromQuery($sql, $ExportDir . "CCVENTAM.csv", $headers, 20, $texto);
            }
        }

        /*         * ************* Lista de Clientes RUC ************* */

        $headers = array(0 => "AVANEXO.C.1",
            1 => "ACODANE.C.18",
            2 => "ADESANE.C.40",
            3 => "AREFANE.C.50",
            4 => "ARUC.C.18",
            5 => "ACODMON.C.2",
            6 => "AESTADO.C.1",
            7 => "ADATE.D",
            8 => "AHORA.C.6",
            9 => "AVRETE.C.1",
            10 => "APORRE.N.7.3"
        );

        $sql = "select 'C' as AVANEXO,  ruc as ACODANE, razsocial as ADESANE,
		'X' as AREFANE, ruc as RUC,'X' as ACODMON, 'V' as AESTADO,
		substring(fecha::text from 1 for 4) || substring(fecha::text from 6 for 2) || substring(fecha::text from 9 for 2) as ADATE,
		substring(fecha::text from 12 for 6) as AHORA,
                'X' as AVRETE, 'X' as APORRE
		from ruc
		where date(fecha) between to_date('$FechaIni_Original','DD/MM/YYYY') and to_date('$FechaFin_Original','DD/MM/YYYY')";
        if ($CodAlmacen != "all") {
            $sql .= " and ch_sucursal = '" . $CodAlmacen . "' ";
        }

        $sql .= "order by fecha;";

        $texto = '';
        if ($sqlca->query($sql) > 0) {
            while ($reg = $sqlca->fetchRow()) {
                if (trim($reg[2]) == '')
                    $reg[2] = "X";
                $texto .= trim($reg[0]) . "," . trim($reg[1]) . "," . trim($reg[2]) . "," . trim($reg[3]) . "," . trim($reg[4]) . "," . trim($reg[5]) . ",";
                $texto .= trim($reg[6]) . "," . trim($reg[7]) . "," . trim($reg[8]) . "," . trim($reg[9]) . "," . trim($reg[10]);
                $texto .= "\r\n";
            }
            CSVFromQuery($sql, $ExportDir . "CLIENTES.csv", $headers, 11, $texto);
        }
        return $ZipFile;
        //return $hello;
    }

    function interface_fn_opensoft_concar_cuentascobrar($FechaIni, $FechaFin, $CodAlmacen) {  /////////////////// cuentas por cobrar ////////////////////
        global $sqlca;

        $modulo = "TODOS";
        $FechaIni_Original = $FechaIni;
        $FechaFin_Original = $FechaFin;
        $FechaDiv = explode("/", $FechaIni);
        $FechaIni = $FechaDiv[2] . "-" . $FechaDiv[1] . "-" . $FechaDiv[0];
        $postrans = "pos_trans" . $FechaDiv[2] . $FechaDiv[1];
        $ZipFile = "CuentasxCobrar" . $CodAlmacen . "del" . $FechaDiv[2] . $FechaDiv[1] . $FechaDiv[0] . "al";
        $FechaDiv = explode("/", $FechaFin);
        $FechaFin = $FechaDiv[2] . "-" . $FechaDiv[1] . "-" . $FechaDiv[0];
        $ZipFile .= $FechaDiv[2] . $FechaDiv[1] . $FechaDiv[0] . ".zip";

        if (("pos_trans" . $FechaDiv[2] . $FechaDiv[1]) != $postrans) {
            return "INVALID_DATE";
        }

        if (strlen($FechaIni) < 10 || strlen($FechaFin) < 10) {
            return "INDALID_DATE";
        }

        if (($sqlca->query("SELECT tipo FROM pos_cfg WHERE es='$CodAlmacen';") <= 0)
                && ($CodAlmacen != "all")) {
            return "INVALID_DATE";
        }

        $reg = $sqlca->fetchRow();

        $TipoAlmacen = trim($reg[0]);

        $ExportDir = "/home/data/";

        if ($TipoAlmacen == "M" || $TipoAlmacen == "m") {
            $prefijo_tipo = "m-";
            $nombre_tipo = "Market";
        } else {
            $prefijo_tipo = "t-";
            $nombre_tipo = "Grifos";
        }

        $cobrarCC = pg_exec("SELECT ccobrar_cuenta_caja from concar_config;");
        $ccc_array = pg_fetch_row($cobrarCC, 0);
        $cccaja = $ccc_array[0];

        $razsoc_sql = pg_exec("SELECT par_valor from int_parametros where par_nombre= 'desces';");
        $razsoc_array = pg_fetch_row($razsoc_sql, 0);
        $razsoc = $razsoc_array[0];


        /*         * *************** Detalles de COBRAR PLAYA ********** */

        if ($modulo == "TODOS" || $modulo == "VALES") {
            /**             * ***** COBRAR - PLAYA - BOLETAS  ******* */
            $sql = "select * from 
                (select to_char(date(dia),'YYMMDD') as dia, 
		ccobrar_cuenta_caja::text as DCUENTA,  '0004'::text as codigo, 
		' '::text as trans,  '1'::text as tip,'D'::text as ddh, 
                round(sum(importe),2) as importe, 
                'PARTE COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||'-" . $razsoc . "'::text as venta , 
		es as sucursal, 
		'00'||MIN(cast(trans as integer))||' - 00'||MAX(cast(trans as integer))::text as dnumdoc,
		ccobrar_subdiario::text as subdiario,
		id_centrocosto::text as DCENCOS
		from pos_trans" . $FechaDiv[2] . $FechaDiv[1] . ", concar_config
		where td = 'B' 
                and tipo = 'C' 
                and  date (dia) between to_date('$FechaIni_Original','DD/MM/YYYY') and to_date('$FechaFin_Original','DD/MM/YYYY')";

            if ($CodAlmacen != "all") {
                $sql .= " and es = '" . $CodAlmacen . "' ";
            }

            $sql .= "group by dia,es, ccobrar_cuenta_caja, subdiario, id_centrocosto
                Order by dia) as A 
                UNION 
                (select to_char(date(dia),'YYMMDD') as dia, 
                ccobrar_cuenta_cliente::text as DCUENTA, '99999999999'::text as codigo, 
		' '::text as trans, '1'::text as tip, 'H'::text as ddh, 
		round(sum(importe),2) as importe, 
		'PARTE COMBUSTIBLE' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||'-" . $razsoc . "'::text as venta , 
		es as sucursal, 
		'00'||MIN(cast(trans as integer))||' - 00'||MAX(cast(trans as integer))::text as dnumdoc,
		ccobrar_subdiario::text as subdiario,
		id_centrocosto::text as DCENCOS
		from pos_trans" . $FechaDiv[2] . $FechaDiv[1] . ", concar_config
		where td = 'B' 
                and tipo = 'C' 
                and  date (dia) between to_date('$FechaIni_Original','DD/MM/YYYY') and to_date('$FechaFin_Original','DD/MM/YYYY')";

            if ($CodAlmacen != "all") {
                $sql .= " and es = '" . $CodAlmacen . "' ";
            }
            /*             * ****** COBRAR - PLAYA - FACTURAS  ******* */
            $sql .= "group by dia, es, ccobrar_cuenta_cliente, subdiario, id_centrocosto 
                Order by dia) 
                UNION 
                (select to_char(date(dia),'YYMMDD') as dia, 
		ccobrar_cuenta_caja::text as DCUENTA, 
		'0004'::text as codigo, trans::text as trans,'1'::text as tip,
		'D'::text as ddh, 
		round (importe, 2) as importe, 
		PARTE COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||'-" . $razsoc . "'::text as venta , 
		es as sucursal , 
		'00'||trans::text as dnumdoc,
		ccobrar_subdiario::text as subdiario,
		id_centrocosto::text as DCENCOS
		from pos_trans" . $FechaDiv[2] . $FechaDiv[1] . ", concar_config
		where td = 'F' 
                and tipo='C'
                and date(dia) between to_date('$FechaIni_Original','DD/MM/YYYY') and to_date('$FechaFin_Original','DD/MM/YYYY') 
                and importe>0";

            if ($CodAlmacen != "all") {
                $sql .= " and es = '" . $CodAlmacen . "' ";
            }

            $sql .= " UNION 
                (select to_char(date(dia),'YYMMDD') as dia,  
                ccobrar_cuenta_cliente::text as DCUENTA, 
                ruc::text as codigo, trans::text as trans, '1'::text as tip,
		'H'::text as ddh, 
		round(importe,2) as importe, 
                'PARTE COMBUSTIBLE ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||'-" . $razsoc . "'::text as venta , 
		es as sucursal, 
		'00'||trans::text as dnumdoc,
		ccobrar_subdiario::text as subdiario,
		id_centrocosto::text as DCENCOS
		from pos_trans" . $FechaDiv[2] . $FechaDiv[1] . ", concar_config
		where td = 'F' and tipo='C' and date(dia) between to_date('$FechaIni_Original','DD/MM/YYYY') and to_date('$FechaFin_Original','DD/MM/YYYY') 
                    and importe>0";

            if ($CodAlmacen != "all") {
                $sql .= " and es = '" . $CodAlmacen . "' ";
            }

            $sql .= ") ORDER BY dia, tip, trans, ddh, codigo;";

            $sql2 = $sql;
            $headers = Array(0 => "DSUBDIA.C.4",
                1 => "DCOMPRO.C.6",
                2 => "DSECUE.C.4",
                3 => "DFECCOM.C.6",
                4 => "DCUENTA.C.12",
                5 => "DCODANE.C.18",
                6 => "DCENCOS.C.6",
                7 => "DCODMON.C.2",
                8 => "DDH.C.1",
                9 => "DIMPORT.N.14.2",
                10 => "DTIPDOC.C.2",
                11 => "DNUMDOC.C.20",
                12 => "DFECDOC.C.6",
                13 => "DFECVEN.C.6",
                14 => "DAREA.C.3",
                15 => "DFLAG.C.1",
                16 => "DXGLOSA.C.30",
                17 => "DDATE.D",
                18 => "DCODANE2.C.18",
                19 => "DUSIMPOR.N.14.2",
                20 => "DMNIMPOR.N.14.2",
                21 => "DCODARC.C.2",
                22 => "DVANEXO.C.1"
            );

            $texto = "";
            $correlativo = 0;
            $contador = '0000';
            $k = 0;

            if ($sqlca->query($sql) > 0) {
                $correlativo = $FechaDiv[1] * 10000;

                while ($reg = $sqlca->fetchRow()) {
                    if (trim($reg[1]) == $cccaja) {
                        $k = 1;
                        $correlativo = $correlativo + 1;
                        if ($FechaDiv[1] >= 01 && $FechaDiv[1] < 10) {
                            $correlativo2 = "0" . $correlativo;
                        } else {
                            $correlativo2 = $correlativo;
                        }
                    } else {
                        $k = $k + 1;
                    }
                    $contador = " 000" . $k . " ";
                    $dfecdoc = "20" . substr($reg[0], 0, 2) . substr($reg[0], 2, 2) . substr($reg[0], 4, 2);
                    $texto .= trim($reg[10]) . "," . $correlativo2 . "," . $contador . "," . trim($reg[0]) . "," . trim($reg[1]) . "," . trim($reg[2]) . "," . trim($reg[11]) . "," . 'MN' . "," . trim($reg[5]) . "," . trim($reg[6]) . "," . 'TK' . "," . trim($reg[9]) . ",";
                    $texto .= trim($reg[0]) . "," . trim($reg[0]) . "," . 'S' . "," . 'S' . "," . trim($reg[7]) . "," . ' ' . "," . ' ' . "," . ' ' . "," . ' ' . "," . ' ' . "," . ' ';
                    $texto .= "\r\n";
                }
            }
            CSVFromQuery($sql, $ExportDir . "CDCOBRAP.csv", $headers, 23, $texto);

            /*             * *************** Cabecera COBRAR PLAYA ********** */

            $headers = Array(0 => "CSUBDIA.C.4",
                1 => "CCOMPRO.C.6",
                2 => "CFECCOM.C.6",
                3 => "CCODMON.C.2",
                4 => "CSITUA.C.1",
                5 => "CTIPCAM.N.10.4",
                6 => "CGLOSA.C.40",
                7 => "CTOTAL.N.14.2",
                8 => "CTIPO.C.1",
                9 => "CFLAG.C.1",
                10 => "CDATE.D",
                11 => "CHORA.C.6",
                12 => "CFECCAM.C.6",
                13 => "CUSER.C.5",
                14 => "CORIG.C.2",
                15 => "CFORM.C.1",
                16 => "CTIPCOM.C.2",
                17 => "CEXTOR.C.1"
            );

            $correlativo = 0;
            ;
            $texto = '';
            if ($sqlca->query($sql) > 0) {
                $correlativo = $FechaDiv[1] * 10000;
                while ($reg = $sqlca->fetchRow()) {
                    if (trim($reg[1]) == $cccaja) {
                        $correlativo = $correlativo + 1;
                        if ($FechaDiv[1] >= 01 && $FechaDiv[1] < 10) {
                            $correlativo2 = "0" . $correlativo;
                        } else {
                            $correlativo2 = $correlativo;
                        }
                        $dfecdoc = "20" . substr($reg[0], 0, 2) . substr($reg[0], 2, 2) . substr($reg[0], 4, 2);
                        $texto .= trim($reg[10]) . "," . $correlativo2 . "," . trim($reg[0]) . "," . 'MN' . "," . 'X' . "," . '0' . ",";
                        $texto .= 'COB PLAYA' . trim($reg[0]) . "," . '0' . "," . 'V' . "," . 'S' . "," . ' ' . "," . ' ' . "," . ' ' . "," . ' ' . "," . ' ' . "," . ' ' . "," . ' ' . "," . ' ';
                        $texto .= "\r\n";
                    }
                }
                CSVFromQuery($sql, $ExportDir . "CCCOBRAP.csv", $headers, 18, $texto);
            }

            /*             * *************** Detalles de COBRAR MARKET ********** */

            /*             * ****** COBRAR - MARKET - BOLETAS  ******* */

            $sql = "select * from 
                (select to_char(date(dia),'YYMMDD') as dia,  
                ccobrar_cuenta_caja::text as DCUENTA,  codigo_concar::text as codigo, 
		' '::text as trans, '2'::text as tip,  'D'::text as ddh,  
                round(sum(importe),2) as importe,  'PARTE MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||'-" . $razsoc . "'::text as venta , 
		es as sucursal, '00'||MIN(cast(trans as integer))||' - 00'||MAX(cast(trans as integer))::text as dnumdoc,
		ccobrar_subdiario::text as subdiario, id_centrocosto::text as DCENCOS
		from pos_trans" . $FechaDiv[2] . $FechaDiv[1] . ", concar_config, interface_equivalencia_producto
		where td = 'B' 
                and tipo = 'M'
                and  date (dia) between to_date('$FechaIni_Original','DD/MM/YYYY') and to_date('$FechaFin_Original','DD/MM/YYYY') 
                and art_codigo='MARKET'";
            if ($CodAlmacen != "all") {
                $sql .= " and es = '" . $CodAlmacen . "' ";
            }

            $sql .= " group by dia,es, ccobrar_cuenta_caja, subdiario, id_centrocosto, codigo_concar 
                Order by dia) as M
                UNION 
                (select to_char(date(dia),'YYMMDD') as dia,  
                ccobrar_cuenta_cliente::text as DCUENTA, 
		'99999999999'::text as codigo, 
		' '::text as trans, 
		'2'::text as tip,
               'H'::text as ddh, 
		round(sum(importe),2) as importe, 
		'PARTE MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||'-" . $razsoc . "'::text as venta , 
		es as sucursal, 
		'00'||MIN(cast(trans as integer))||' - 00'||MAX(cast(trans as integer))::text as dnumdoc,
		ccobrar_subdiario::text as subdiario,
		id_centrocosto::text as DCENCOS
		from pos_trans" . $FechaDiv[2] . $FechaDiv[1] . ", concar_config
		where td = 'B' and tipo = 'M' and  date (dia) between to_date('$FechaIni_Original','DD/MM/YYYY') and to_date('$FechaFin_Original','DD/MM/YYYY')";
            if ($CodAlmacen != "all") {
                $sql .= " and es = '" . $CodAlmacen . "' ";                                /*                 * ****** COBRAR - MARKET - FACTURAS  ******* */
            }

            $sql .= "group by dia, es, ccobrar_cuenta_cliente, subdiario, id_centrocosto 
                Order by dia) 
                UNION 
                (select to_char(date(dia),'YYMMDD') as dia,     
                ccobrar_cuenta_caja::text as DCUENTA, 
                codigo_concar::text as codigo, 
                trans::text as trans,'2'::text as tip, 'D'::text as ddh, 
                round (sum(importe), 2) as importe, 
                'PARTE MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||'-" . $razsoc . "'::text as venta , 
		es as sucursal , 
		'00'||trans::text as dnumdoc,
		ccobrar_subdiario::text as subdiario,
		id_centrocosto::text as DCENCOS
		from pos_trans" . $FechaDiv[2] . $FechaDiv[1] . ", concar_config, interface_equivalencia_producto
		where td = 'F' 
                and tipo='M' 
                and date(dia) between to_date('$FechaIni_Original','DD/MM/YYYY') and to_date('$FechaFin_Original','DD/MM/YYYY') 
                and importe>0 
                and art_codigo='MARKET'";

            if ($CodAlmacen != "all") {
                $sql .= " and es = '" . $CodAlmacen . "' ";
            }

            $sql .= "group by dia, trans, es, ccobrar_cuenta_caja, subdiario, id_centrocosto, codigo_concar order by dia)  
                UNION 
                (select to_char(date(dia),'YYMMDD') as dia,  
                ccobrar_cuenta_cliente::text as DCUENTA, ruc::text as codigo, 
                trans::text as trans,  '2'::text as tip, 'H'::text as ddh,  
                round(sum(importe),2) as importe,  
                'PARTE MARKET ' || substring(dia::text from 9 for 2) || '-' || substring(dia::text from 6 for 2) || '-' || substring(dia::text from 3 for 2) ||'-" . $razsoc . "'::text as venta , 
		es as sucursal , 
		'00'||trans::text as dnumdoc,
		ccobrar_subdiario::text as subdiario,
		id_centrocosto::text as DCENCOS
		from pos_trans" . $FechaDiv[2] . $FechaDiv[1] . ", concar_config
		where td = 'F' 
                and tipo='M' 
                and date(dia) between to_date('$FechaIni_Original','DD/MM/YYYY') and to_date('$FechaFin_Original','DD/MM/YYYY') 
                and importe > 0";

            if ($CodAlmacen != "all") {
                $sql .= " and es = '" . $CodAlmacen . "' ";
            }

            $sql .= " group by dia, trans, es, ccobrar_cuenta_cliente, subdiario, ruc, id_centrocosto 
                order by dia)  ORDER BY dia, tip, trans, ddh, codigo;";


            $sql2 = $sql;
            $headers = Array(0 => "DSUBDIA.C.4",
                1 => "DCOMPRO.C.6",
                2 => "DSECUE.C.4",
                3 => "DFECCOM.C.6",
                4 => "DCUENTA.C.12",
                5 => "DCODANE.C.18",
                6 => "DCENCOS.C.6",
                7 => "DCODMON.C.2",
                8 => "DDH.C.1",
                9 => "DIMPORT.N.14.2",
                10 => "DTIPDOC.C.2",
                11 => "DNUMDOC.C.20",
                12 => "DFECDOC.C.6",
                13 => "DFECVEN.C.6",
                14 => "DAREA.C.3",
                15 => "DFLAG.C.1",
                16 => "DXGLOSA.C.30",
                17 => "DDATE.D",
                18 => "DCODANE2.C.18",
                19 => "DUSIMPOR.N.14.2",
                20 => "DMNIMPOR.N.14.2",
                21 => "DCODARC.C.2",
                22 => "DVANEXO.C.1"
            );

            $texto = "";
            $correlativo = 0;
            $contador = '0000';
            $k = 0;

            if ($sqlca->query($sql) > 0) {
                $correlativo = $FechaDiv[1] * 10000;

                while ($reg = $sqlca->fetchRow()) {
                    if (trim($reg[1]) == $cccaja) {
                        $k = 1;
                        $correlativo = $correlativo + 1;
                        if ($FechaDiv[1] >= 01 && $FechaDiv[1] < 10) {
                            $correlativo2 = "0" . $correlativo;
                        } else {
                            $correlativo2 = $correlativo;
                        }
                    } else {
                        $k = $k + 1;
                    }
                    $contador = " 000" . $k . " ";
                    $dfecdoc = "20" . substr($reg[0], 0, 2) . substr($reg[0], 2, 2) . substr($reg[0], 4, 2);
                    $texto .= trim($reg[10]) . "," . $correlativo2 . "," . $contador . "," . trim($reg[0]) . "," . trim($reg[1]) . "," . trim($reg[2]) . "," . trim($reg[11]) . "," . 'MN' . "," . trim($reg[5]) . "," . trim($reg[6]) . "," . 'TK' . "," . trim($reg[9]) . ",";
                    $texto .= trim($reg[0]) . "," . trim($reg[0]) . "," . 'S' . "," . 'S' . "," . trim($reg[7]) . "," . ' ' . "," . ' ' . "," . ' ' . "," . ' ' . "," . ' ' . "," . ' ';
                    $texto .= "\r\n";
                }
            }
            CSVFromQuery($sql, $ExportDir . "CDCOBRAM.csv", $headers, 23, $texto);


            /*             * *************** Cabecera de COBRAR MARKET ********** */

            $headers = Array(0 => "CSUBDIA.C.4",
                1 => "CCOMPRO.C.6",
                2 => "CFECCOM.C.6",
                3 => "CCODMON.C.2",
                4 => "CSITUA.C.1",
                5 => "CTIPCAM.N.10.4",
                6 => "CGLOSA.C.40",
                7 => "CTOTAL.N.14.2",
                8 => "CTIPO.C.1",
                9 => "CFLAG.C.1",
                10 => "CDATE.D",
                11 => "CHORA.C.6",
                12 => "CFECCAM.C.6",
                13 => "CUSER.C.5",
                14 => "CORIG.C.2",
                15 => "CFORM.C.1",
                16 => "CTIPCOM.C.2",
                17 => "CEXTOR.C.1"
            );

            $correlativo = 0;
            ;
            $texto = '';
            if ($sqlca->query($sql) > 0) {
                $correlativo = $FechaDiv[1] * 10000;
                while ($reg = $sqlca->fetchRow()) {
                    if (trim($reg[1]) == $cccaja) {
                        $correlativo = $correlativo + 1;
                        if ($FechaDiv[1] >= 01 && $FechaDiv[1] < 10) {
                            $correlativo2 = "0" . $correlativo;
                        } else {
                            $correlativo2 = $correlativo;
                        }

                        $dfecdoc = "20" . substr($reg[0], 0, 2) . substr($reg[0], 2, 2) . substr($reg[0], 4, 2);
                        $texto .= trim($reg[10]) . "," . $correlativo2 . "," . trim($reg[0]) . "," . 'MN' . "," . 'X' . "," . '0' . ",";
                        $texto .= 'COB MARKET' . trim($reg[0]) . "," . '0' . "," . 'V' . "," . 'S' . "," . ' ' . "," . ' ' . "," . ' ' . "," . ' ' . "," . ' ' . "," . ' ' . "," . ' ' . "," . ' ';
                        $texto .= "\r\n";
                    }
                }
                CSVFromQuery($sql, $ExportDir . "CCCOBRAM.csv", $headers, 18, $texto);
            }
        }
        return $ZipFile;
    }

}

