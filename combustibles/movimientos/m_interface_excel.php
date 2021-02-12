<?php

class InterfaceMovModel extends Model {

    function ListadoAlmacenes($codigo) {
        global $sqlca;

        $cond = '';
        if ($codigo != "") {
            $cond = "AND trim(ch_sucursal) = '" . pg_escape_string($codigo) . "' ";
        }
        $query = "SELECT ch_almacen FROM inv_ta_almacenes WHERE trim(ch_clase_almacen)='1' " . $cond . " ORDER BY ch_almacen";

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
            $listado['' . $codigo . ''] .= $reg[0] . $conc;
            $x++;
        }

        return $listado;
    }

    //piero35
    function ActualizarDatosFacturas($Fecha) {
        global $sqlca;


        $query = "SELECT  
                        CASE WHEN c.ch_fac_tipodocumento='10' then '10'
                        WHEN c.ch_fac_tipodocumento='35' then '03'
                        end as tipo_documento,
                        to_char(c.dt_fac_fecha,'dd-MM-YYYY') as fecha_dococumento,
                        trim(c.ch_fac_seriedocumento) as serie,
                        c.ch_fac_numerodocumento::text as num_dococumento_a,
                        c.ch_fac_numerodocumento::text as num_dococumento_b,
                        (select art_descripcion from int_articulos where trim(art_codigo)=trim(d.art_codigo) limit 1) as des_producto,
                        d.nu_fac_importeneto as importe,
                        d.nu_fac_impuesto1 igv,
                        '0' as otros,
                        d.art_codigo::text as cod_producto,
                        ((SELECT  cl.cli_ruc||'-'||cl.cli_rsocialbreve as ruc_tmp  FROM  int_clientes cl  WHERE trim(cl.cli_ruc) = trim(c.cli_codigo)) 
                         LIMIT  1) as ruc_dni_rz,
                        '18.00' as tasa,
                        (d.nu_fac_importeneto+d.nu_fac_impuesto1)as total_imponible,
                        '' as cuenta_contable,
                        'S' as tipo_moneda,
                        '0.00'as dimporte,
                        '0.00'as digv,
                        '0.00'as dotros,
                        '0.00'as dimportetotal,
                        '-' as cod,
                        case when ch_fac_credito='N' then '1'else '0'end as cancelado,
                        '-' as fecha_cancelado,
                        '05' as operacion,
                        '' as cuenta1,
                        '' as deposito_datrecom,
                        '' as fecha_datrecom,
                        to_char(c.dt_fac_fecha,'MM') as mes_emision,
                        d.nu_fac_cantidad  as galon,
                        '' as cuenta2,
                        '01' as analitico,
                        '-' as detratot,
                        char_length(c.cli_codigo::TEXT) as lon,
                        case when char_length(c.cli_codigo::TEXT)=11 then  '6'
                        when char_length(c.cli_codigo::TEXT)=8  then '8' 
                        else '0'
                        end as tipo_documneto,
                        case when trim(d.art_codigo)='11620304' then '31'
                        when trim(d.art_codigo)='11620301'then '27'
                        when trim(d.art_codigo)='11620302'then '28'
                        when trim(d.art_codigo)='11620303'then '29'
                        else d.art_codigo
                        end as cod_producto_grupo,

                        case when trim(d.art_codigo) in('11620304','11620301','11620302','11620303' )
                        then 'GLN'
                        else 'UN'
                        end as unidad_medida,
                        util_fn_tipo_cambio_dia(c.dt_fac_fecha) as tipo_cambio,
                        '-' as ad,
                        d.nu_fac_precio as precio_promedio,
                        '-' as oricom,
                        '0' as pigv,
                        '0' as ptotal,
                        '-' as ndfecha,
                        '-' as ndtipo,
                        '-' as ndserie,
                        '-' as ndnumero,
                        '1' as estado,--1 cuando no esta realcionda con not decredito
                        to_char(c.dt_fac_fecha,'YYYYMM') as periodo
                        FROM fac_ta_factura_cabecera c
                        LEFT JOIN fac_ta_factura_detalle d 
                        ON    c.ch_fac_tipodocumento=d.ch_fac_tipodocumento 
                        AND   c.ch_fac_seriedocumento=d.ch_fac_seriedocumento 
                        AND   c.ch_fac_numerodocumento=d.ch_fac_numerodocumento
                        AND   c.cli_codigo=d.cli_codigo
                        where   to_char(c.dt_fac_fecha,'YYYY-MM')= '$Fecha' and   c.ch_fac_tipodocumento in('35','10') ;

";

        if ($sqlca->query($query) < 0) {
            return array();
        }


        $result = array();
        while ($reg = $sqlca->fetchRow()) {
            $result[] = $reg;
        }

        return $result;
    }

    function ActualizarDatosPostrans($Fecha ) {
        global $sqlca;
        $Fecha = str_replace("-", "", $Fecha);

        $query = "SELECT  
                        '12' tipo_documento,
                        to_char(c.dia,'dd-MM-YYYY') as fecha_dococumento,
                        (SELECT nu_posz_z_serie FROM pos_z_cierres 
                        WHERE  ch_posz_pos=c.caja AND dt_posz_fecha_sistema=c.dia AND nu_posturno::TEXT=c.turno::TEXT limit 1) as serie,
                        c.trans::text as num_dococumento_a,
                        c.trans::text as num_dococumento_b,
                        (select art_descripcion from int_articulos where trim(art_codigo)=trim(c.codigo) limit 1) as des_producto,
                        c.importe as importe,
                        c.igv igv,
                        '0' as otros,
                        c.codigo::text as cod_producto,
                        case when((
                        (SELECT  cl.cli_ruc||'-'||cl.cli_rsocialbreve as ruc_tmp  FROM  int_clientes cl  WHERE trim(cl.cli_ruc) = trim(c.ruc))
                        UNION
                        (SELECT  cl.razsocial||'-'||cl.razsocial as ruc_tmp  FROM  ruc cl  WHERE trim(cl.ruc) = trim(c.ruc)))
                         LIMIT  1) is null then
                        '111'
                        else
                        ((
                        (SELECT  cl.cli_ruc||'-'||cl.cli_rsocialbreve as ruc_tmp  FROM  int_clientes cl  WHERE trim(cl.cli_ruc) = trim(c.ruc))
                        UNION
                        (SELECT  cl.razsocial||'-'||cl.razsocial as ruc_tmp  FROM  ruc cl  WHERE trim(cl.ruc) = trim(c.ruc)))
                         LIMIT  1)
                        end
                        as ruc_dni_rz,
                        c.ruc,
                        '18.00' as tasa,
                        (c.importe+c.igv)as total_imponible,
                        '' as cuenta_contable,
                        'S' as tipo_moneda,
                        '0.00'as dimporte,
                        '0.00'as digv,
                        '0.00'as dotros,
                        '0.00'as dimportetotal,
                        '-' as cod,
                        case when c.fpago='1' then '1'else '0'end as cancelado,
                        '-' as fecha_cancelado,
                        '05' as operacion,
                        '' as cuenta1,
                        '' as deposito_datrecom,
                        '' as fecha_datrecom,
                        to_char(c.dia,'MM') as mes_emision,
                        c.cantidad  as galon,
                        '' as cuenta2,
                        '01' as analitico,
                        '-' as detratot,

                        char_length(c.ruc::TEXT) as lon,
                        case when char_length(c.ruc::TEXT)=11 then  '6'
                        when char_length(c.ruc::TEXT)=8  then '8' 
                        else '0'
                        end as tipo_documneto,
                        case when trim(c.codigo)='11620304' then '31'
                        when trim(c.codigo)='11620301'then '27'
                        when trim(c.codigo)='11620302'then '28'
                        when trim(c.codigo)='11620303'then '29'
                        else c.codigo
                        end as cod_producto_grupo,

                        case when trim(c.codigo) in('11620304','11620301','11620302','11620303','11620305','11620306','11620307')
                        then 'GLN'
                        else 'UN'
                        end as unidad_medida,
                        util_fn_tipo_cambio_dia(c.dia::date) as tipo_cambio,
                        '-' as ad,
                        c.precio as precio_promedio,
                        '-' as oricom,
                        '0' as pigv,
                        '0' as ptotal,
                        '-' as ndfecha,
                        '-' as ndtipo,
                        '-' as ndserie,
                        '-' as ndnumero,
                        '1' as estado,--1 cuando no esta realcionda con not decredito
                        to_char(c.dia,'YYYYMM') as periodo



                        FROM pos_trans$Fecha c where c.td!='N' and c.tipo in('C','M') ;

";

        if ($sqlca->query($query) < 0) {
            return array();
            //return $sqlca->get_error();
        }


        $result = array();
        while ($reg = $sqlca->fetchRow()) {
            $result[] = $reg;
        }

        return $result;
    }

}

