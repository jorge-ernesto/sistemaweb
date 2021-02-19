<?php
include("/sistemaweb/valida_sess.php");
include("/sistemaweb/functions.php");

date_default_timezone_set('America/Lima');

class HelperClass {
    function __construct(){
    }

    public function numberFormat($Ss_Value, $Nu_Decimal, $Ss_Punto, $Ss_Coma){
        return number_format($Ss_Value, $Nu_Decimal, $Ss_Punto, $Ss_Coma);
    }

    public function Months(){
        return (object)array(
            (object)array(
                'mes'   => 'Enero',
                'valor' => '01'
            ),
            (object)array(
                'mes'   => 'Febrero',
                'valor' => '02'
            ),
            (object)array(
                'mes'   => 'Marzo',
                'valor' => '03'
            ),
            (object)array(
                'mes'   => 'Abril',
                'valor' => '04'
            ),
            (object)array(
                'mes'   => 'Mayo',
                'valor' => '05'
            ),
            (object)array(
                'mes'   => 'Junio',
                'valor' => '06'
            ),
            (object)array(
                'mes'   => 'Julio',
                'valor' => '07'
            ),
            (object)array(
                'mes'   => 'Agosto',
                'valor' => '08'
            ),
            (object)array(
                'mes'   => 'Setiembre',
                'valor' => '09'
            ),
            (object)array(
                'mes'   => 'Octubre',
                'valor' => '10'
            ),
            (object)array(
                'mes'   => 'Noviembre',
                'valor' => '11'
            ),
            (object)array(
                'mes'   => 'Diciembre',
                'valor' => '12'
            ),  
        );
    }

    public function getSystemYearStart($Fe_Sistema){
        $years = array();
        $y = explode('-', $Fe_Sistema);
        $y = $y[0];
        
        for($i = $y; $i <= date('Y'); $i++){
            $years[] = (object)array(
                'year' => $i
            );
        }
        
        return (object)$years;
    }

    public function getAllDateFormat($sTypeDate = ''){
        $arrFecha = localtime(time(), true);

        $iYear = (1900 + $arrFecha['tm_year']);
        $iMonth = (strlen(1 + $arrFecha['tm_mon']) > 1 ? (1 + $arrFecha['tm_mon']) : '0' . (1 + $arrFecha['tm_mon']));
        $iDay = (strlen($arrFecha['tm_mday']) > 1 ? $arrFecha['tm_mday'] : '0' . $arrFecha['tm_mday']);
        
        $iHour = $arrFecha['tm_hour'];
        $iMinute = $arrFecha['tm_min'];
        $iSecond = $arrFecha['tm_sec'];
        
        if ($sTypeDate == 'dia')
            $dHoy_Hour = $iDay;
        else if ($sTypeDate == 'mes')
            $dHoy_Hour = $iMonth;
        else if ($sTypeDate == 'año')
            $dHoy_Hour = $iYear;
        else if ($sTypeDate == 'fecha_inicial_ymd')
            $dHoy_Hour = $iYear . '-' . $iMonth . '-01';
        else if ($sTypeDate == 'fecha_ymd')
            $dHoy_Hour = $iYear . '-' . $iMonth . '-' . $iDay;
        else if ($sTypeDate == 'fecha_inicial_dmy')
            $dHoy_Hour = '01/' . $iMonth . '/' . $iYear;
        else if ($sTypeDate == 'fecha_dmy')
            $dHoy_Hour =  $iDay . '/' . $iMonth . '/' . $iYear;
        else if ($sTypeDate == 'fecha_hora')
            $dHoy_Hour = $iYear . '-' . $iMonth . '-' . $iDay . ' ' . $iHour . ':' . $iMinute . ':' . $iSecond;
        else if ($sTypeDate == 'hora')
            $dHoy_Hour = $iHour . ':' . $iMinute . ':' . $iSecond;
        return $dHoy_Hour;
    }

    public function getWareHouse($cond_where_almacen = ''){
        global $sqlca;

        $cond_where_almacen = (!empty($cond_where_almacen) ? " AND ch_almacen = '" . $cond_where_almacen . "'" : '' );
        $iStatus = $sqlca->query("
SELECT
 ch_almacen AS id,
 ch_nombre_almacen AS name
FROM
 inv_ta_almacenes
WHERE
 ch_clase_almacen='1'
" . $cond_where_almacen);

        $arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'danger', 'sMessage' => 'error SQL - function getWareHouse()', 'sMessageSQL' => $sqlca->get_error());
        if ($iStatus == 0)
            $arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'warning', 'sMessage' => 'Sin Datos', 'arrData' => 0);
        else if ((int)$iStatus > 0)
            $arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'success', 'sMessage' => 'Registros Encontrados', 'arrData' => $sqlca->fetchAll());
        return $arrResponse;
    }

    public function getParametersTable($cond_where = ''){
        global $sqlca;

        $cond_where = (!empty($cond_where) ? " WHERE par_nombre = '" . $cond_where . "'" : '' );
        $iStatus = $sqlca->query("SELECT * FROM int_parametros" . $cond_where);

        if ((int)$iStatus > 0)
            return array('iStatus' => $iStatus, 'sStatus' => 'success', 'sMessage' => 'Registros Encontrados', 'arrData' => $sqlca->fetchAll());
        else if ($iStatus == 0)
            return array('iStatus' => $iStatus, 'sStatus' => 'warning', 'sMessage' => 'No se encontro parámetro de configuración', 'arrData' => 0);
        return array('iStatus' => $iStatus, 'sStatus' => 'danger', 'sMessage' => 'error SQL - function getParametersTable()', 'sMessageSQL' => $sqlca->get_error());
    }

    public function getGeneralTable($value, $cond_where = '', $order_by = '', $other_campo_id = '') {
        global $sqlca;
        $campo_id = (!empty($other_campo_id) ? $other_campo_id : 'tab_elemento');
        $cond_tab_car_03 = (!empty($cond_where) ? $cond_where : '');

        $iStatus = $sqlca->query("
SELECT
 *,
 " . $campo_id . " AS id,
 tab_descripcion AS name,
 tab_desc_breve AS short_name
FROM
 int_tabla_general
WHERE
 tab_tabla = '" . $value . "'
 AND tab_elemento != '000000'
 " . $cond_tab_car_03 . "
ORDER BY
 " . $order_by);

        $sql = "
SELECT
 *,
 " . $campo_id . " AS id,
 tab_descripcion AS name,
 tab_desc_breve AS short_name
FROM
 int_tabla_general
WHERE
 tab_tabla = '" . $value . "'
 AND tab_elemento != '000000'
 " . $cond_tab_car_03 . "
ORDER BY
 " . $order_by;
        error_log($sql);

        $arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'danger', 'sMessage' => 'error SQL - function getGeneralTable()', 'arrData' => NULL);
        if ($iStatus == 0)
            $arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'warning', 'sMessage' => 'Sin Datos', 'arrData' => 0);
        else if ((int)$iStatus > 0)
            $arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'success', 'sMessage' => 'Registros Encontrados', 'arrData' => $sqlca->fetchAll());
        return $arrResponse;
    }

    function getCatalogo09Sunat_NC(){
        $catalogo = array("iStatus" => "", "sStatus" => "success", "sMessage" => "Registros Encontrados");
        $catalogo['arrData'][] = array( "id" => "000001", "name" => "Anulación de la operación" );
        $catalogo['arrData'][] = array( "id" => "000002", "name" => "Anulación por error en el RUC" );
        $catalogo['arrData'][] = array( "id" => "000003", "name" => "Corrección por error en la descripción" );
        $catalogo['arrData'][] = array( "id" => "000004", "name" => "Descuento global" );
        $catalogo['arrData'][] = array( "id" => "000005", "name" => "Descuento por ítem" );
        $catalogo['arrData'][] = array( "id" => "000006", "name" => "Devolución total" );
        $catalogo['arrData'][] = array( "id" => "000007", "name" => "Devolución por ítem" );
        $catalogo['arrData'][] = array( "id" => "000008", "name" => "Bonificación" );
        $catalogo['arrData'][] = array( "id" => "000009", "name" => "Disminución en el valor" );
        $catalogo['arrData'][] = array( "id" => "000010", "name" => "Otros Conceptos" );
        $catalogo['arrData'][] = array( "id" => "000011", "name" => "Ajustes de operaciones de exportación" );
        $catalogo['arrData'][] = array( "id" => "000012", "name" => "Ajustes afectos al IVAP" );
        $catalogo['iStatus'] = count($catalogo['arrData']);
        return $catalogo;
    }

    function getCatalogo10Sunat_ND(){
        $catalogo = array("iStatus" => "", "sStatus" => "success", "sMessage" => "Registros Encontrados");
        $catalogo['arrData'][] = array( "id" => "000001", "name" => "Intereses por mora" );
        $catalogo['arrData'][] = array( "id" => "000002", "name" => "Aumento en el valor" );
        $catalogo['arrData'][] = array( "id" => "000003", "name" => "Penalidades/ otros conceptos" );
        $catalogo['arrData'][] = array( "id" => "000011", "name" => "Ajustes de operaciones de exportación" );
        $catalogo['arrData'][] = array( "id" => "000012", "name" => "Ajustes afectos al IVAP" );
        $catalogo['iStatus'] = count($catalogo['arrData']);
        return $catalogo;
    }

    function executeCreacionCampo(){
        global $sqlca;        
        
        //VALIDAMOS QUE EL CAMPO EXISTA        
        $iStatus = $sqlca->query("SELECT EXISTS(SELECT ch_cat_sunat FROM fac_ta_factura_complemento);");
        $arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'danger', 'sMessage' => 'No se creo campo', 'arrData' => NULL);
                
        //NO EXISTE
        if((int)$iStatus < 0){
            //CREAMOS CAMPO
            $iStatus = $sqlca->query("ALTER TABLE fac_ta_factura_complemento ADD COLUMN ch_cat_sunat CHARACTER VARYING(2);");            
            
            //SI SE EJECUTO CORRECTAMENTE
            if((int)$iStatus == 0){
                $arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'success', 'sMessage' => 'Creamos campo correctamente', 'arrData' => NULL);
            }
        }
        return $arrResponse;
    }

    public function getExchangeRate($cond_where = ''){
        global $sqlca;

        $cond_fecha = (!empty($cond_where) ? "WHERE tca_fecha = '" . $cond_where . "'" : '' );
        $iStatus = $sqlca->query("SELECT tca_venta_oficial AS ss_tc_venta FROM int_tipo_cambio " . $cond_fecha . " LIMIT 1");

        $arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'danger', 'sMessage' => 'error SQL - function getExchangeRate()', 'arrData' => NULL);
        if ($iStatus == 0)
            $arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'warning', 'sMessage' => 'Sin Datos', 'arrData' => 0);
        else if ((int)$iStatus > 0)
            $arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'success', 'sMessage' => 'Registros Encontrados', 'arrData' => $sqlca->fetchRow());
        return $arrResponse;
    }

    public function getTax() {
        global $sqlca;

        $iStatus = $sqlca->query("SELECT util_fn_igv();");

        $arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'danger', 'sMessage' => 'error SQL - function getTax()', 'arrData' => NULL);
        if ($iStatus == 0)
            $arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'warning', 'sMessage' => 'Sin Datos', 'arrData' => 0);
        else if ((int)$iStatus > 0) {
            $row = $sqlca->fetchRow();
            $row['ss_impuesto'] = (double)$row['util_fn_igv'];
            $row['ss_impuesto'] = (1 + ($row['ss_impuesto'] / 100));
            $arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'success', 'sMessage' => 'Registros Encontrados', 'arrData' => $row['ss_impuesto']);
        }
        return $arrResponse;
    }

    function getCurrentSystemDate() {
        global $sqlca;

        $iStatus = $sqlca->query("SELECT TO_CHAR(da_fecha - integer '1', 'DD/MM/YYYY') AS fe_sistema FROM pos_aprosys WHERE ch_poscd='A' ORDER BY da_fecha DESC LIMIT 1;");

        $arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'danger', 'sMessage' => 'error SQL - function getCurrentSystemDate()', 'arrData' => NULL);
        if ($iStatus == 0)
            $arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'warning', 'sMessage' => 'Sin Datos', 'arrData' => 0);
        else if ((int)$iStatus > 0) {
            $row = $sqlca->fetchRow();
            $arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'success', 'sMessage' => 'Datos encontrados', 'arrData' => $row['fe_sistema']);
        }
        return $arrResponse;
    }

    function getSystemYearStartDB() {
        global $sqlca;
        $iStatusSQL = $sqlca->query("SELECT da_fecha AS fe_sistema FROM pos_aprosys ORDER BY da_fecha ASC LIMIT 1;");
        if ((int)$iStatusSQL > 0) {
            $row = $sqlca->fetchRow();
            return array('sStatus' => 'success', 'dFechaEmision' => $row['fe_sistema']);
        } else if ($iStatusSQL == 0) {
            return array('sStatus' => 'warning', 'sMessage' => 'Sin Datos');
        }
        return array('sStatus' => 'danger', 'sMessage' => 'error SQL - function getSystemYearStartDB()', 'sMessageSQL' => $sqlca->get_error());
    }

    function verify_consolidation($arrPost){
        global $sqlca;

        $arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'danger', 'sMessage' => 'error SQL - function verify_consolidation()');
        //dia format(YYYY-MM-DD), turno, almacen
        $iStatus = $sqlca->query("SELECT validar_consolidacion('" . $arrPost['dFecha'] . "', " . $arrPost['iTurno'] . ", '" . $arrPost['iAlmacen'] . "');");
        if ((int)$iStatus > 0) {
            $row = $sqlca->fetchRow();
            //Si devuelve 0, no está consolidado o no existe el registro en BD
            $arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'success', 'sMessage' => 'No consolidado');
            if ($row["validar_consolidacion"] == '1' )//Consolidado
                $arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'warning', 'sMessage' => 'Día Consolidado');
        }
        // error_log("****** Analisis para guardar documentos, etapa 1 ******");
        // error_log(json_encode($arrResponse));
        return $arrResponse;
    }

    function getUserIP(){
        $sNombreUsuario = "";
        $sIp = "";

        if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"),"unknown"))
            $sIp = getenv("HTTP_CLIENT_IP");
        else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
            $sIp = getenv("HTTP_X_FORWARDED_FOR");
        else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
            $sIp = getenv("REMOTE_ADDR");
        else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
            $sIp = $_SERVER['REMOTE_ADDR'];

        $sNombreUsuario = $_SESSION['auth_usuario'];

        $arrResponse = array(
            'sNombreUsuario' => $sNombreUsuario,
            'sIp' => $sIp,
        );
        return $arrResponse;
    }
    
    function obtenerUltimoDiaConsolidado($arrPost){
        global $sqlca;

        $sql = "SELECT * FROM pos_consolidacion WHERE almacen='" . pg_escape_string($arrPost['sIdAlmacen']) . "' ORDER BY almacen, dia LIMIT 1";
        if ( $sqlca->query($sql)>0 ) {
            return array('sStatus' => 'success', 'arrData' => $sqlca->fetchAll(),'sCssStyle'=>'
            color: black;
            background-color: #FFFFFF;
            border-color: #ffeeba;
            ');
        } else if ( $sqlca->query($sql)==0 ){
            return array('sStatus' => 'warning', 'sql' => $sql, 'sMessage' => 'No hay registros de consolidacion - Código de Almacen ' . $arrPost['sIdAlmacen'],
            'sCssStyle'=>'
            font-size:12px;
            color: #856404;
            background-color: #fff3cd;
            border-color: #ffeeba;
            '
            );
        }
        return array('sStatus' => 'danger', 'sql' => $sql, 'sMessage' => 'Problemas al obtener registros de consolidacion', 'sMessageSQL' => $sqlca->get_error(),
        'sCssStyle'=>'
        font-size:12px;
        color: #721c24;
        background-color: #f8d7da;
        border-color: #f5c6cb;
        ');
    }

    function array_debug($text){
        echo "<pre>";
        print_r($text);
        echo "<pre>";
    }
}