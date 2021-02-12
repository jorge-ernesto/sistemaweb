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
    
//OBTENEMOS FECHA 
$dia     = $_GET['fecha'];  
$fecha     = explode('-', $_GET['fecha']);
$pos_trans = "pos_trans" . $fecha[0] . $fecha[1];


//ELIMINAMOS DATA ANTERIOR
eliminar_inv_movialma($dia);
eliminar_fac_ta_factura_detalle($dia);
eliminar_fac_ta_factura_cabebcera($dia);

//LLAMAMOS FUNCION
$dia_restaurado = post_fn_restaura_ventas($dia, $pos_trans);
if($dia == $dia_restaurado){
    echo "Se restauro el día $dia correctamente";
}


//FUNCION
function post_fn_restaura_ventas($dia, $pos_trans){
    $ncl = new NuclearFactory();
    $sql = "SELECT post_fn_restaura_ventas('$dia', '$pos_trans')";        
    $data = $ncl->findById($sql);    
    // echo "<pre>";
    // print_r($data);
    // echo "</pre>";   
    return $data['post_fn_restaura_ventas'];    
}

function eliminar_fac_ta_factura_cabebcera($dia){
    $ncl = new NuclearFactory();
    $sql = "delete from fac_ta_factura_cabecera WHERE DATE(dt_fac_fecha) = '$dia' AND ch_fac_tipodocumento='45';";
    // echo "<pre>";
    // echo $sql;
    // echo "</pre>";
    $ncl->execute($sql);    
}
function eliminar_fac_ta_factura_detalle($dia){
    $ncl = new NuclearFactory();
    $sql = "delete from fac_ta_factura_detalle
            where ch_fac_tipodocumento   IN (select ch_fac_tipodocumento from fac_ta_factura_cabecera WHERE DATE(dt_fac_fecha) = '$dia' AND ch_fac_tipodocumento='45' ORDER BY ch_fac_numerodocumento)
            and   ch_fac_seriedocumento  IN (select ch_fac_seriedocumento from fac_ta_factura_cabecera WHERE DATE(dt_fac_fecha) = '$dia' AND ch_fac_tipodocumento='45' ORDER BY ch_fac_numerodocumento)
            and   ch_fac_numerodocumento IN (select ch_fac_numerodocumento from fac_ta_factura_cabecera WHERE DATE(dt_fac_fecha) = '$dia' AND ch_fac_tipodocumento='45' ORDER BY ch_fac_numerodocumento)
            and   cli_codigo             IN (select cli_codigo from fac_ta_factura_cabecera WHERE DATE(dt_fac_fecha) = '$dia' AND ch_fac_tipodocumento='45' ORDER BY ch_fac_numerodocumento)
            and   ch_fac_tipodocumento = '45';";
    // echo "<pre>";
    // echo $sql;
    // echo "</pre>";
    $ncl->execute($sql);    
}
function eliminar_inv_movialma($dia){
    $ncl = new NuclearFactory();
    $sql = "delete from inv_movialma WHERE DATE(mov_fecha) = '$dia' AND tran_codigo='45';";
    // echo "<pre>";
    // echo $sql;
    // echo "</pre>";
    $ncl->execute($sql);    
}

die();
