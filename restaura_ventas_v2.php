<?php

class NuclearFactory{
    public function __construct(){
        global $dbconn;        
        $dbconn = pg_connect("host=localhost dbname=integrado user=postgres password=") or die('No se ha podido conectar: '.pg_last_error());
        pg_set_client_encoding($dbconn, "utf8");
    }    
    function execute($sql){
        global $dbconn;
        $query = pg_query($dbconn, $sql);        
        return $query;
    }    
    function findById($sql){
        global $dbconn;
        $query = pg_query($dbconn, $sql);        
        $fila = pg_fetch_assoc($query);
        return $fila;
    }
    function executeWithFindByLastId($sql){
        global $dbconn;
        $query = pg_query($sql);       
        $fila = pg_fetch_assoc($query);
        return $fila;
    }
    function clearString($str){
        global $dbconn;
        $str = pg_escape_string($dbconn, trim($str));
        return htmlspecialchars($str);
    }
    function pg_last_error(){
        echo pg_last_error();
    }
    function validar_fecha($fecha){
        $valores = explode('-', $fecha);
        if(count($valores) == 3 && checkdate($valores[1], $valores[2], $valores[0])){
            return true;
        }
        return false;
    }
}

$ncl = new NuclearFactory();

//VALIDAMOS FECHA
if(!isset($_GET['fecha'])){ //Si no se especifica fecha en la url
    echo "Debe ingresar fecha";
    die();
}
if(isset($_GET['fecha'])){ //Si la fecha no es correcta
    $fecha = $_GET['fecha'];
    $fecha_validada = $ncl->validar_fecha($fecha);        
    if(!$fecha_validada){
        echo "Formato de fecha incorrecto";
        die();
    }
}
if(isset($_GET['fecha2'])){ //Si la fecha no es correcta
    $fecha2 = $_GET['fecha2'];
    $fecha_validada2 = $ncl->validar_fecha($fecha2);        
    if(!$fecha_validada2){
        echo "Formato de fecha incorrecto";
        die();
    }
}
    
//OBTENEMOS FECHA
$dia       = $_GET['fecha'];  
$dia2      = $_GET['fecha2'];
$fecha     = explode('-', $_GET['fecha']);
$pos_trans = "pos_trans" . $fecha[0] . $fecha[1];


try{
    beginTransaction();

    //OBTENEMOS ARRAY DE FECHAS
    $fechas = get_fechas($dia, $dia2, $pos_trans);
    
    foreach ($fechas as $key=>$fecha) {
        $dia = $fecha['dia'];

        //ELIMINAMOS DATA ANTERIOR
        eliminar_fac_ta_factura('detalle', $dia);
        eliminar_fac_ta_factura('cabecera', $dia);

        //OBTENEMOS REGISTROS DE POSTRANS
        $registros_postrans = get_registros_postrans($dia, $pos_trans);

        //RESTAURAMOS VENTAS
        foreach ($registros_postrans as $key2=>$registros) {
            if($registros['trans'] != NULL && $registros['tipo'] == 'M'){                        

                $numpk = verificamos_fac_ta_factura('cabecera', $registros);
                if($numpk == 0){
                    operacion_fac_ta_factura_cabecera('insertar', $registros);
                }else{
                    operacion_fac_ta_factura_cabecera('actualizar', $registros);
                }

                $numpk = verificamos_fac_ta_factura('detalle', $registros);
                if($numpk == 0){
                    operacion_fac_ta_factura_detalle('insertar', $registros);
                }else{
                    operacion_fac_ta_factura_detalle('actualizar', $registros);
                }

            }
        }

        //MOSTRAMOS LISTA DE DIAS RESTAURADOS
        echo "Se restauro el día $dia correctamente<br>";
    }

    //ACTUALIZAMOS FUNCTION
    $actualizar_function = isset($_GET['actualizar_function']) ? $_GET['actualizar_function'] : '';
    if($actualizar_function == 1){
        echo "Se actualizo correctamente function post_fn_inserta_ventas";
        createorreplace_trigger();
    }

    commit();
}catch(\Exception $e){    
    echo 'Excepción capturada ¡ROLLBACK!: ',  $e->getMessage(), "\n";    
    rollback();        
    die();
}


//FUNCION
function eliminar_fac_ta_factura($verificar, $dia){
    $ncl = new NuclearFactory();
    
    if($verificar == 'cabecera'){
        $sql = "delete from fac_ta_factura_cabecera WHERE DATE(dt_fac_fecha) = '$dia' AND ch_fac_tipodocumento='45';";
    }elseif($verificar == 'detalle'){
        $sql = "delete from fac_ta_factura_detalle
                where ch_fac_tipodocumento   IN (select ch_fac_tipodocumento from fac_ta_factura_cabecera WHERE DATE(dt_fac_fecha) = '$dia' AND ch_fac_tipodocumento='45' ORDER BY ch_fac_numerodocumento)
                and   ch_fac_seriedocumento  IN (select ch_fac_seriedocumento from fac_ta_factura_cabecera WHERE DATE(dt_fac_fecha) = '$dia' AND ch_fac_tipodocumento='45' ORDER BY ch_fac_numerodocumento)
                and   ch_fac_numerodocumento IN (select ch_fac_numerodocumento from fac_ta_factura_cabecera WHERE DATE(dt_fac_fecha) = '$dia' AND ch_fac_tipodocumento='45' ORDER BY ch_fac_numerodocumento)
                and   cli_codigo             IN (select cli_codigo from fac_ta_factura_cabecera WHERE DATE(dt_fac_fecha) = '$dia' AND ch_fac_tipodocumento='45' ORDER BY ch_fac_numerodocumento)
                and   ch_fac_tipodocumento = '45';";
    }    
    //echo "<pre>$sql<br></pre>";

    $query = $ncl->execute($sql);
    if(!$query){
        if($verificar == 'cabecera'){            
            $ncl->pg_last_error(); echo "<br>";
            throw new Exception("No se pudo eliminar data de fac_ta_factura cabecera");            
        }else{            
            $ncl->pg_last_error(); echo "<br>";
            throw new Exception("No se pudo eliminar data de fac_ta_factura detalle");            
        }
    }
}

function get_registros_postrans($dia, $pos_trans){
    $ncl = new NuclearFactory();
    $sql = "
            SELECT 
                *,
                (select art_linea       from int_articulos where art_codigo=PT.codigo) as artlinea,
                (select art_plutipo     from int_articulos where art_codigo=PT.codigo) as artplutipo,
                (select art_costoactual from int_articulos where art_codigo=PT.codigo) as costo_actual,                
                (select to_char(PT.dia,'yyymmdd')) as numdoc
            FROM 
                $pos_trans AS PT
            WHERE 
                PT.tipo          = 'M'
                AND DATE(PT.dia) = '$dia'
                AND PT.trans     IS NOT NULL
                
            UNION 

            SELECT 
                *,
                (select art_linea       from int_articulos where art_codigo=PT.codigo) as artlinea,
                (select art_plutipo     from int_articulos where art_codigo=PT.codigo) as artplutipo,
                (select art_costoactual from int_articulos where art_codigo=PT.codigo) as costo_actual,                
                (select to_char(PT.dia,'yyymmdd')) as numdoc
            FROM 
                pos_transtmp AS PT
            WHERE 
                PT.tipo          = 'M'
                AND DATE(PT.dia) = '$dia'
                AND PT.trans     IS NOT NULL;
            ";        
    //echo "<pre>$sql<br></pre>";

    $query = $ncl->execute($sql);   
    if(!$query){
        $ncl->pg_last_error(); echo "<br>";
        throw new Exception("No se pudo obtener datos de pos_trans");
    }else{
        $data = pg_fetch_all($query);
        if(!is_array($data) || is_null($data) || empty($data)){
            throw new Exception("Array de datos en pos_trans vacio");
        }
        return $data;
    }    
}

function get_fechas($dia, $dia2, $pos_trans){
    $ncl = new NuclearFactory();
    $sql = "
            SELECT 
                DATE(PT.dia) as dia
            FROM 
                $pos_trans AS PT
            WHERE 
                PT.tipo          = 'M'
                AND DATE(PT.dia) BETWEEN '$dia' AND '$dia2'
                AND PT.trans     IS NOT NULL
            GROUP BY 
                DATE(PT.dia);
            ";        
    //echo "<pre>$sql<br></pre>";

    $query = $ncl->execute($sql);   
    if(!$query){
        $ncl->pg_last_error(); echo "<br>";
        throw new Exception("Error al obtener fechas");
    }else{
        $data = pg_fetch_all($query);
        if(!is_array($data) || is_null($data) || empty($data)){
            throw new Exception("Array de fechas vacio");
        }
        return $data;
    }    
}

function verificamos_fac_ta_factura($verificar, $registros){
    $numdoc   = $registros['numdoc'];
    $sucursal = $registros['es'];
    $codigo   = $registros['codigo'];

    $ncl = new NuclearFactory();
    if($verificar == 'cabecera'){
        $sql = "select count(*) as numpk from  fac_ta_factura_cabecera
                where ch_fac_tipodocumento='45' and trim(ch_fac_seriedocumento)='$sucursal'
                and ch_fac_numerodocumento='$numdoc'  and cli_codigo='CLIENTEPOS';";
    }elseif($verificar == 'detalle'){
        $sql = "select count(*) as numpk from  fac_ta_factura_detalle
                where ch_fac_tipodocumento='45' and trim(ch_fac_seriedocumento)='$sucursal'
                and ch_fac_numerodocumento='$numdoc'  and cli_codigo='CLIENTEPOS'
                and art_codigo = '$codigo';";
    }
    //echo "<pre>$sql<br></pre>";
    $data = $ncl->findById($sql);    
    
    $numpk = $data['numpk'];
    if($numpk == NULL || $numpk < 0){
        if($verificar == 'cabecera'){
            $ncl->pg_last_error(); echo "<br>";
            throw new Exception("Fallo en el metodo verificamos_fac_ta_factura_cabecera");
        }else{
            $ncl->pg_last_error(); echo "<br>";
            throw new Exception("Fallo en el metodo verificamos_fac_ta_factura_detalle");    
        }
    }else{
        return $numpk;
    }    
}

function operacion_fac_ta_factura_cabecera($verificar, $registros){
    $sucursal     = $registros['es'];
    $numdoc       = $registros['numdoc'];
    $importe      = $registros['importe'];
    $igv          = $registros['igv'];
    $dia          = $registros['dia'];
    $codigo       = $registros['codigo'];
    $cantidad     = $registros['cantidad'];
    $precio       = $registros['precio'];

    $ncl = new NuclearFactory();

    if($verificar == 'insertar'){
        $sql = "insert into fac_ta_factura_cabecera
                (ch_fac_tipodocumento,ch_fac_seriedocumento
                ,ch_fac_numerodocumento,cli_codigo
                ,nu_fac_impuesto1,nu_fac_valorbruto
                ,nu_fac_valortotal
                ,ch_punto_venta,ch_almacen
                ,dt_fac_fecha
                ,ch_fac_moneda , ch_fac_forma_pago , ch_fac_credito )
                values
                ('45' ,'$sucursal'
                ,'$numdoc' ,'CLIENTEPOS'
                ,$igv,$importe-$igv
                ,$importe
                ,'$sucursal','$sucursal'
                ,'$dia' --Esto hay que revisar
                ,'01'   ,       '01'    ,       'N'
                );";
    }elseif($verificar == 'actualizar'){
        $sql = "update fac_ta_factura_cabecera
                set nu_fac_impuesto1 = nu_fac_impuesto1 + $igv
                ,nu_fac_valorbruto   = nu_fac_valorbruto + ($importe-$igv)
                ,nu_fac_valortotal   = nu_fac_valortotal + $importe
                where
                ch_fac_tipodocumento    = '45' and
                ch_fac_seriedocumento   = '$sucursal' and
                ch_fac_numerodocumento  = '$numdoc' and
                cli_codigo              = 'CLIENTEPOS';";
    }
    //echo "<pre>$sql<br></pre>";
    $query = $ncl->execute($sql);

    if(!$query){
        if($verificar == 'insertar'){
            $ncl->pg_last_error(); echo "<br>";
            throw new Exception("No se pudo insertar data de fac_ta_factura_cabecera");
        }else{
            $ncl->pg_last_error(); echo "<br>";
            throw new Exception("No se pudo actualizar data de fac_ta_factura_cabecera");
        }
    }
}

function operacion_fac_ta_factura_detalle($verificar, $registros){
    $sucursal     = $registros['es'];
    $numdoc       = $registros['numdoc'];
    $importe      = $registros['importe'];
    $igv          = $registros['igv'];
    $dia          = $registros['dia'];
    $codigo       = $registros['codigo'];
    $cantidad     = $registros['cantidad'];
    $precio       = $registros['precio'];

    $ncl = new NuclearFactory();

    if($verificar == 'insertar'){
        $sql = "insert into fac_ta_factura_detalle
                (ch_fac_tipodocumento,ch_fac_seriedocumento
                ,ch_fac_numerodocumento,cli_codigo
                ,art_codigo
                ,nu_fac_cantidad,nu_fac_precio
                ,nu_fac_importeneto , nu_fac_impuesto1
                ,nu_fac_valortotal)
                values
                ('45'   ,'$sucursal'
                ,'$numdoc' ,'CLIENTEPOS'
                ,'$codigo'
                ,$cantidad,$precio
                ,$importe-$igv , $igv
                ,$importe);";
    }elseif($verificar == 'actualizar'){
        $sql = "update fac_ta_factura_detalle
                set nu_fac_cantidad     = nu_fac_cantidad + $cantidad
                , nu_fac_precio         = ($importe-$igv) / $cantidad
                , nu_fac_importeneto    = nu_fac_importeneto + ($importe-$igv)
                , nu_fac_impuesto1      = nu_fac_impuesto1 + $igv
                , nu_fac_valortotal     = nu_fac_valortotal + $importe
                where ch_fac_tipodocumento      = '45'   and
                ch_fac_seriedocumento           = '$sucursal'       and
                ch_fac_numerodocumento          = '$numdoc'         and
                cli_codigo                              = 'CLIENTEPOS' and
                art_codigo                              = '$codigo';";
    }
    //echo "<pre>$sql<br></pre>";
    $query = $ncl->execute($sql);

    if(!$query){
        if($verificar == 'insertar'){
            $ncl->pg_last_error(); echo "<br>";
            throw new Exception("No se pudo insertar data de fac_ta_factura_detalle");
        }else{
            $ncl->pg_last_error(); echo "<br>";
            throw new Exception("No se pudo actualizar data de fac_ta_factura_detalle");
        }
    }
}

function createorreplace_trigger(){
    $ncl = new NuclearFactory();
    $sql = "
-- FUNCTION: public.post_fn_inserta_ventas()

CREATE OR REPLACE FUNCTION public.post_fn_inserta_ventas()
    RETURNS trigger
    LANGUAGE 'plpgsql'
    COST 100
    VOLATILE NOT LEAKPROOF
AS ".'$BODY$DECLARE'."
        tipodoc char(2);
        seriedoc char(4);
        numdoc  char(20);
        numeval char(10);
        cli_cod char(12);
        fila record;
        reg_serv record;
        reg_serv2 record;
        art_cod char(13);
        mes char(2);
        periodo char(4);
        numpk integer;
        alma_des char(3);
        natu char(3);
        actualiza char(1);
        artlinea char(6);
        artplutipo char(1);
        artplutipo2 char(1);
        w_cantidad numeric(15,2);
        w_cantidad2 numeric(15,2);
        costo_actual numeric(15,4);
        hoy date;
        alma_ori char(3);
        sucursal char(3);
BEGIN
        /* Actualizacion para multiples almacenes JCP 20-05-2011 */
        /* Se creo una variable para almacen origen*/

        /* codigo art_tipoplu 1 es plu normal 2 es plusaliente  3 no para la venta*/
        /* en el caso de tipoplu 1 es plu normal se refiere al articulo de venta sin componente y no servicios....
                chicle, cigarrillo, caramelo, etc.  */
        /* en el caso de tipoplu 2 es plu saliente se refiere al plu con componentes que se graba en facturacion  y
                en inventarios se graban sus componentes ejemplo promocion hamburguesa cocacola 3.50
                tambien se usa para el caso de servicios pero sin ningun componente por ejemplo lavados donde solo
                se guarda en facturacion pero como no tiene componentes no guarda nada en inventarios. */
        /* en el caso de tipoplu 3 es plu no para la venta que se refiere a los descuentos , etc cuando no se le permite
                al usuario digitar estos codigos solo se guarda en facturacion pero no en inventario*/

        select into mes         to_char(NEW.dia,'mm') ;
        select into periodo     to_char(NEW.dia,'yyyy') ;
        select into actualiza trim(actinv) from pos_cfg where trim(es)=trim(new.es) and
pos=NEW.caja;
        select into artlinea,artplutipo, costo_actual  art_linea,art_plutipo, art_costoactual from int_articulos where art_codigo=NEW.codigo;

        /*JCP 20-05-2011*/
        select into alma_ori NEW.es ;
        select into sucursal NEW.es ;

        select into numpk count(*) from int_parametros where par_nombre='punto_venta_almacen' ;

        IF numpk>0 THEN
           -- Seleccionar codigo de almacen de nuevo campo en pos_cfg
           select into alma_ori almacen from pos_cfg where pos=NEW.caja ;

        END IF ;
        /*JCP  */

        IF NEW.tm IN ('V','A') AND NEW.trans IS NOT NULL AND NEW.td='N' THEN --if1

                select into numdoc trim(NEW.caja) || '-' || trim(to_char(NEW.trans,'9999999999'));
                SELECT INTO numeval trim(to_char(COALESCE(NEW.inicial,NEW.trans),'9999999999'));
                RAISE NOTICE 'INSERTA_VENTAS NUMDOC %',numdoc;

                -- Busco en la cabecera haber si existe si no exsite lo creo  si ya existe no hace nada
                select into numpk count(*) from val_ta_cabecera
                where sucursal=trim(ch_sucursal) and to_char(NEW.dia,'yyyymmdd')=to_char(dt_fecha, 'yyyymmdd')
                and ch_documento=numdoc;

                IF numpk=0 THEN
                        -- agrego en vales cabecera
                        insert into val_ta_cabecera
                        (ch_sucursal, dt_fecha, ch_documento, ch_cliente, ch_glosa, ch_placa,
                        nu_odometro, ch_tarjeta, ch_caja, ch_turno, ch_lado, dt_fechaactualizacion, ch_planilla, ch_estado)
                        values
                        (sucursal, NEW.dia, numdoc, trim(NEW.proveedor),
                        'VALES CLIENTE POS COMB', trim(NEW.placa),
                        NEW.odometro, trim(NEW.tarjeta),  trim(NEW.caja), trim(NEW.turno), trim(NEW.pump), now(), trim(NEW.nombre), '1' );

                        /*Agregado en val complemento*/

                        insert into val_ta_complemento
                        (ch_sucursal    , dt_fecha      , ch_documento
                        ,ch_numeval
                        ,nu_importe     , ch_estado
                        ,dt_fechaactualizacion  ,ch_usuario     ,       ch_auditorpc    )
                        values
                        (sucursal               , NEW.dia       , numdoc
                        , numeval
                        , NEW.importe   , '1'
                        , now()                 ,'TRIV' ,       'TRIV');

                        /*Agregado en val complemento*/

                END IF;

                -- Busco en el detalle haber si existe si no exsite lo creo  si ya existe anade al actual
                select into numpk count(*) from  val_ta_detalle
                where sucursal=trim(ch_sucursal) and to_char(NEW.dia,'yyyymmdd')=to_char(dt_fecha, 'yyyymmdd')
                and ch_documento=numdoc and ch_articulo=NEW.codigo;

                IF numpk = 0 THEN --if2.1
                        --INSERTO EN EL DETALLE
                        insert into val_ta_detalle
                        (ch_sucursal, dt_fecha, ch_documento, ch_articulo,
                        nu_cantidad, nu_importe, ch_estado, dt_fechaactualizacion, nu_factor_igv)
                        values
                        (sucursal ,NEW.dia, numdoc, NEW.codigo,
                        NEW.cantidad, NEW.importe,'1', now(), util_fn_igv_porarticulo(NEW.codigo));
                ELSE
                        --ACTUALIZO EL DETALLE
                        update val_ta_detalle
                        set nu_cantidad=nu_cantidad+new.cantidad,
                        nu_importe=nu_importe+new.importe
                        where sucursal=trim(ch_sucursal) and to_char(NEW.dia,'yyyymmdd')=to_char(dt_fecha, 'yyyymmdd')
                        and ch_documento=numdoc and ch_articulo=NEW.codigo;

                END IF; --if2.1

        END IF;

        IF NEW.trans IS NOT NULL AND NEW.tipo='M' THEN --if1

                -- select into numdoc to_char(NEW.dia,'ymmdd') ||  substring(art.art_linea,5,2)  from int_articulos art where art.art_codigo=NEW.codigo;

                select into numdoc to_char(NEW.dia,'yyymmdd');

                --RAISE NOTICE 'SE SELECCIONA NUMERO DE DOCUMENTO %',numdoc;

                select into numpk count(*) from  fac_ta_factura_cabecera
                where ch_fac_tipodocumento='45' and trim(ch_fac_seriedocumento)=sucursal
                and ch_fac_numerodocumento=numdoc  and cli_codigo='CLIENTEPOS';

                IF numpk=0 THEN --if2 como no existe el registro lo crea
                        --INSERTO EN LA CABECERA
                        insert into fac_ta_factura_cabecera
                        (ch_fac_tipodocumento,ch_fac_seriedocumento
                         ,ch_fac_numerodocumento,cli_codigo
                        ,nu_fac_impuesto1,nu_fac_valorbruto
                        ,nu_fac_valortotal
                        ,ch_punto_venta,ch_almacen
                        ,dt_fac_fecha
                        ,ch_fac_moneda , ch_fac_forma_pago , ch_fac_credito )
                         values
                        ('45' ,sucursal
                        ,numdoc ,'CLIENTEPOS'
                        ,NEW.igv,NEW.importe-NEW.igv
                        ,NEW.importe
                        ,sucursal,sucursal
                        ,to_date(  to_char(NEW.dia,'dd/mm/yyyy')  , 'dd/mm/yyyy' )
                        ,'01'   ,       '01'    ,       'N'
                        );

                ELSE --el primary key esta insertado en la cabecera de facturas entons hay que actualizar
                        --ACTUALIZO LA CABECERA
                        update fac_ta_factura_cabecera
                        set nu_fac_impuesto1 = nu_fac_impuesto1 + NEW.igv
                        ,nu_fac_valorbruto   = nu_fac_valorbruto + (NEW.importe-NEW.igv)
                        ,nu_fac_valortotal   = nu_fac_valortotal + NEW.importe
                        where
                        ch_fac_tipodocumento    = '45' and
                        ch_fac_seriedocumento   = sucursal and
                        ch_fac_numerodocumento  = numdoc and
                        cli_codigo              = 'CLIENTEPOS';

                END IF; --if2

                select into numpk count(*) from  fac_ta_factura_detalle
                where ch_fac_tipodocumento='45' and trim(ch_fac_seriedocumento)=sucursal
                and ch_fac_numerodocumento=numdoc  and cli_codigo='CLIENTEPOS'
                and art_codigo = NEW.codigo;

                IF numpk = 0 THEN --if2.1
                        --INSERTO EN EL DETALLE
                        insert into fac_ta_factura_detalle
                        (ch_fac_tipodocumento,ch_fac_seriedocumento
                        ,ch_fac_numerodocumento,cli_codigo
                        ,art_codigo
                        ,nu_fac_cantidad,nu_fac_precio
                        ,nu_fac_importeneto , nu_fac_impuesto1
                        ,nu_fac_valortotal)
                         values
                        ('45'   ,sucursal
                        ,numdoc ,'CLIENTEPOS'
                        ,NEW.codigo
                        ,NEW.cantidad,NEW.precio
                        ,NEW.importe-NEW.igv , NEW.igv
                        ,NEW.importe);

                ELSE
                        --ACTUALIZO EL DETALLE
                        -- , nu_fac_precio         = (NEW.importe-NEW.igv) / NEW.cantidad
                        update fac_ta_factura_detalle
                        set nu_fac_cantidad     = nu_fac_cantidad + NEW.cantidad
                          , nu_fac_precio         = (NEW.importe-NEW.igv) / NEW.cantidad
                          , nu_fac_importeneto    = nu_fac_importeneto + (NEW.importe-NEW.igv)
                          , nu_fac_impuesto1      = nu_fac_impuesto1 + NEW.igv
                          , nu_fac_valortotal     = nu_fac_valortotal + NEW.importe
                        where ch_fac_tipodocumento      = '45'   and
                        ch_fac_seriedocumento           = sucursal       and
                        ch_fac_numerodocumento          = numdoc         and
                        cli_codigo                              = 'CLIENTEPOS' and
                        art_codigo                              = NEW.codigo;

                END IF; --if2.1

                /*INSERTAR Y ACTUALIZAR EN TABLAS VEN_TA_VENTA_MENSUALXITEM Y INV_MOVIALMA*/

                --VEN_TA_VENTA_MENSUALXITEM
                FOR fila IN execute 'select count(*) as numfilas from ven_ta_venta_mensualxitem where
                ch_periodo ='''||periodo||''' and ch_sucursal='''||sucursal||'''
                and art_codigo='''||NEW.codigo||'''  ' LOOP
                        numpk := fila.numfilas;
                END LOOP;

                IF numpk = 0 THEN --if3 primera vez que inserta en VEN_TA_VENTA_MENSUALXITEM

                        execute 'insert into ven_ta_venta_mensualxitem(ch_periodo,ch_sucursal,art_codigo
                        ,nu_can'||mes||' , nu_val'||mes||')
                        values ('''||periodo||''' , '''||sucursal||''' , '''||NEW.codigo||''' ,
                        '||NEW.cantidad||' , '||NEW.importe||' )';

                ELSE  --ya existia asi que hace update de este articulo
                        execute 'update ven_ta_venta_mensualxitem set
                        nu_can'||mes||' = nu_can'||mes||' +'||NEW.cantidad||'
                        ,nu_val'||mes||' = nu_val'||mes||' +'||NEW.importe||'
                        where ch_periodo='''||periodo||''' and trim(ch_sucursal)='''||sucursal||'''
                        and art_codigo='''||NEW.codigo||'''    ';

                END IF; --if3

                --INV_MOVIALMA

IF actualiza='S' and artplutipo!='3' THEN -- if0
-- actualizacion solo de movialma
-- las actualizaciones a invsaldo
-- se hacen cuando aniades a movialma por un trigger en movialma;

                select into alma_des,natu  tran_destino,tran_naturaleza
                from inv_tipotransa where tran_codigo='45';

                IF artplutipo='2' THEN

                        FOR REG_SERV IN select ch_item_estandar,nu_cantidad_descarga from INT_TA_ENLACE_ITEMS
                                        where art_codigo=NEW.codigo LOOP
                                select into artplutipo2 art_plutipo from int_articulos
                                        where art_codigo=reg_serv.ch_item_estandar;
                                w_cantidad:=REG_SERV.nu_cantidad_descarga*NEW.cantidad;
                                IF artplutipo2='2' THEN
                                        FOR REG_SERV2 IN select ch_item_estandar,nu_cantidad_descarga
                                                                from INT_TA_ENLACE_ITEMS
                                                                where art_codigo=reg_serv.ch_item_estandar LOOP
                                                w_cantidad2:=w_cantidad*REG_SERV2.nu_cantidad_descarga;
                                                -- actualizar(REG_SERV2.ch_item_estandar, w_cantidad2);
                                                select into numpk count(*) from inv_movialma where
                                                        mov_numero = alma_ori||numdoc and tran_codigo='45'
                                                        and art_codigo=REG_SERV2.ch_item_estandar  and
                                                        to_date(to_char(mov_fecha,'dd/mm/yyyy') , 'dd/mm/yyyy') =
                                                        to_date(to_char(NEW.dia,'dd/mm/yyyy') , 'dd/mm/yyyy')
                                                        and trim(mov_almacen)=alma_ori;
                                                IF numpk = 0 THEN --if4
                                                        insert into inv_movialma( mov_numero, tran_codigo, art_codigo
                                                                , mov_fecha, mov_almacen, mov_almaorigen
                                                                , mov_almadestino, mov_naturaleza , mov_cantidad
                                                                , mov_costounitario , mov_costopromedio, mov_costototal )
                                                                values ( alma_ori||numdoc      ,    '45'   ,                                                                            REG_SERV2.ch_item_estandar,
                                                                NEW.dia , alma_ori , alma_ori,
                                                                alma_des , natu , w_cantidad2,
                                                                costo_actual , costo_actual , costo_actual*w_cantidad2);
                                                ELSE
                                                        update inv_movialma set
                                                                mov_almaorigen=alma_ori, mov_almadestino=alma_des
                                                                , mov_cantidad=mov_cantidad+w_cantidad2
                                                                , mov_costounitario=costo_actual ,
                                                                mov_costopromedio=costo_actual,
                                                                mov_costototal=costo_actual*w_cantidad2
                                                                where mov_numero = alma_ori||numdoc and  tran_codigo='45'
                                                                and art_codigo=REG_SERV2.ch_item_estandar and
                                                                to_date(to_char(mov_fecha,'dd/mm/yyyy') , 'dd/mm/yyyy') =
                                                                to_date(to_char(NEW.dia,'dd/mm/yyyy') , 'dd/mm/yyyy')
                                                                and trim(mov_almacen)=alma_ori ;
                                                END IF; --if4

                                        END LOOP;
                                ELSE
                                        -- actualizar(REG_SERV.ch_item_estandar, w_cantidad );
                                        select into numpk count(*) from inv_movialma where
                                                mov_numero = alma_ori||numdoc and tran_codigo='45'
                                                and art_codigo=REG_SERV.ch_item_estandar  and
                                                to_date(to_char(mov_fecha,'dd/mm/yyyy') , 'dd/mm/yyyy') =
                                                to_date(to_char(NEW.dia,'dd/mm/yyyy') , 'dd/mm/yyyy')
                                                and trim(mov_almacen)=alma_ori;
                                        IF numpk = 0 THEN --if4
                                                insert into inv_movialma( mov_numero, tran_codigo, art_codigo
                                                        , mov_fecha, mov_almacen, mov_almaorigen
                                                        , mov_almadestino, mov_naturaleza , mov_cantidad
                                                        , mov_costounitario , mov_costopromedio, mov_costototal )
                                                        values ( alma_ori||numdoc      ,    '45'   ,                                                                            REG_SERV.ch_item_estandar,
                                                        NEW.dia , alma_ori , alma_ori,
                                                        alma_des , natu , w_cantidad,
                                                        costo_actual , costo_actual , costo_actual*w_cantidad);
                                        ELSE
                                                update inv_movialma set
                                                        mov_almaorigen=alma_ori, mov_almadestino=alma_des
                                                        , mov_cantidad=mov_cantidad+w_cantidad
                                                        , mov_costounitario=costo_actual , mov_costopromedio=costo_actual
                                                        , mov_costototal=costo_actual*w_cantidad
                                                         where mov_numero = alma_ori||numdoc and  tran_codigo='45'
                                                        and art_codigo=REG_SERV.ch_item_estandar and
                                                        to_date(to_char(mov_fecha,'dd/mm/yyyy') , 'dd/mm/yyyy') =
                                                        to_date(to_char(NEW.dia,'dd/mm/yyyy') , 'dd/mm/yyyy')
                                                        and trim(mov_almacen)=alma_ori ;
                                        END IF; --if4
                                END IF;
                                numpk := fila.numfilas;
                        END LOOP;

                ELSE

                        -- lo hace al principio en la consulta a int_articulos pero solo para el caso de plu normal
                        -- select into costo_actual art_costoactual from int_articulos where art_codigo=NEW.codigo;

                        select into numpk count(*) from inv_movialma where
                        mov_numero = alma_ori||numdoc and tran_codigo='45'
                        and art_codigo=NEW.codigo  and
                        to_date(to_char(mov_fecha,'dd/mm/yyyy') , 'dd/mm/yyyy') =
                        to_date(to_char(NEW.dia,'dd/mm/yyyy') , 'dd/mm/yyyy')
                        and trim(mov_almacen)=alma_ori;

                        IF numpk = 0 THEN --if4
                                insert into inv_movialma( mov_numero, tran_codigo, art_codigo
                                , mov_fecha, mov_almacen, mov_almaorigen
                                , mov_almadestino, mov_naturaleza , mov_cantidad
                                , mov_costounitario , mov_costopromedio, mov_costototal )
                                values ( alma_ori||numdoc      ,    '45'   ,    NEW.codigo
                                , NEW.dia , alma_ori , alma_ori
                                , alma_des , natu , NEW.cantidad
                                , costo_actual , costo_actual , costo_actual*NEW.cantidad);
                        ELSE
                                update inv_movialma set
                                mov_almaorigen=alma_ori, mov_almadestino=alma_des
                                , mov_cantidad=mov_cantidad+NEW.cantidad
                                , mov_costounitario=costo_actual , mov_costopromedio=costo_actual
                                , mov_costototal=costo_actual*NEW.cantidad
                                where mov_numero = alma_ori||numdoc and  tran_codigo='45'
                                and art_codigo=NEW.codigo and
                                to_date(to_char(mov_fecha,'dd/mm/yyyy') , 'dd/mm/yyyy') =
                                to_date(to_char(NEW.dia,'dd/mm/yyyy') , 'dd/mm/yyyy')
                                and trim(mov_almacen)=alma_ori ;
                        END IF; --if4
                END IF;

END IF; -- if actualiza inventarios?

        END IF; --if1

return NEW;

END;

".'$BODY$'.";

ALTER FUNCTION public.post_fn_inserta_ventas()
    OWNER TO postgres;

";

    $sql = str_replace("\r", "", $sql);
    $query = $ncl->execute($sql);
    if(!$query){
        $ncl->pg_last_error(); echo "<br>";
        throw new Exception("No se pudo actualizar trigger");
    }
}

function beginTransaction(){
    $ncl = new NuclearFactory();    
    $ncl->execute("BEGIN");
}
function commit(){
    $ncl = new NuclearFactory();    
    $ncl->execute("COMMIT");
}
function rollback(){
    $ncl = new NuclearFactory();    
    $ncl->execute("ROLLBACK");
}

die();
