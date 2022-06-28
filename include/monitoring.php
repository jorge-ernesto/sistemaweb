<?php
// Descomentar estas líneas, cuando estamos en modo - development
/*
error_reporting(-1);
ini_set('display_errors', 1);
*/
// Descomentar estas líneas, cuando estamos en modo - production

ini_set('display_errors', 0);
if (version_compare(PHP_VERSION, '5.3', '>='))
{
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
}
else
{
    error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE);
}

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once("/sistemaweb/include/config.php");
include_once("/sistemaweb/include/dbsqlca.php");

date_default_timezone_set('America/Lima');

global $db_host, $db_user, $db_password, $db_name;
$sqlca = new pgsqlDB($db_host, $db_user, $db_password, $db_name);

class MonitoringInterface{

    private function checkRegister($arrDataSQL){
        global $sqlca;

        $iStatusSQL = $sqlca->query($arrDataSQL['sql']);

        if ( (int)$iStatusSQL < 0 ) {
            return array(
                'sStatus' => 'danger',
                'sMessage' => $arrDataSQL['sMessageDanger'],
                'sMessageSQL' => $sqlca->get_error(),
                'SQL' => $arrDataSQL['sql'],
                'number_days' => NULL,
            );
        }

        if ( (int)$iStatusSQL == 0 ) {
            return array(
                'sStatus' => 'warning',
                'sMessage' => $arrDataSQL['sMessageWarning'],
                'SQL' => $arrDataSQL['sql'],
                'number_days' => NULL,
            );
        }

        return array(
            'sStatus' => 'success', 
            'sMessage' => 'Dia encontrado', 
            'arrData' => $sqlca->fetchAll()
        );
    }

    private function checkDiffDays($arrData){
        global $sqlca;
        try {
            $ultima_fecha = $arrData["arrData"][0]["ultima_fecha"];
            $fecha_actual = date('Y-m-d');

            $datetime1 = new DateTime($ultima_fecha);
            $datetime2 = new DateTime($fecha_actual);
            $interval = $datetime1->diff($datetime2);
            $number_days = $interval->days;

            return array(
                'sStatus' => 'success', 
                'sMessage' => 'Cantidad de dias determinados', 
                'number_days' => $number_days
            );
        } catch (Exception $e) {
            return array(
                'sStatus' => 'danger',
                'sMessage' => 'Problemas al determinar cantidad de dias',
                'sMessagePHP' => $e->getMessage(),
            );
        }
    }

    function delta(){
        global $sqlca;
        try {
            $arrDelta = array(
                "deltaConsolidacion" => NULL,
                "deltaVarillaje" => NULL,
                "deltaCompraComb" => NULL,
                "deltaMatricula" => NULL,
                "deltaDeposito" => NULL,
                "deltaCaja" => NULL,
                "deltaCompra" => NULL,
                "deltaValePendiente" => 0,
                "deltaFacturaPendiente" => 0,
                "deltaSistema" => NULL,
            );

            /**
             * deltaConsolidacion 
             */
            $arrDataSQL = array(
                'sql' => "SELECT DATE(dia) AS ultima_fecha, * FROM pos_consolidacion WHERE estado = '1' ORDER BY dia DESC LIMIT 1;",
                'sMessageDanger' => 'Problemas al buscar ultima consolidacion',
                'sMessageWarning' => 'No existe ultima consolidacion',
            );
            $arrData = $this->checkRegister($arrDataSQL);
            if ( $arrData['sStatus'] == "success" ) {
                $arrResponse = $this->checkDiffDays($arrData);              
                $arrDelta['deltaConsolidacion'] = $arrResponse['number_days'];
            }

            /**
             * deltaVarillaje 
             */
            $arrDataSQL = array(
                'sql' => "SELECT DATE(dt_fechamedicion) AS ultima_fecha, * FROM comb_ta_mediciondiaria ORDER BY dt_fechamedicion DESC LIMIT 1;",
                'sMessageDanger' => 'Problemas al buscar ultimo varillaje',
                'sMessageWarning' => 'No existe ultimo varillaje',
            );
            $arrData = $this->checkRegister($arrDataSQL);
            if ( $arrData['sStatus'] == "success" ) {
                $arrResponse = $this->checkDiffDays($arrData);
                $arrDelta['deltaVarillaje'] = $arrResponse['number_days'];
            }
            
            /**
             * deltaCompraComb 
             */
            $arrDataSQL = array(
                'sql' => "SELECT DATE(mov_fecha) AS ultima_fecha, * FROM inv_movialma WHERE tran_codigo = '21' ORDER BY mov_fecha DESC LIMIT 1;",
                'sMessageDanger' => 'Problemas al buscar ultima compra de combustible',
                'sMessageWarning' => 'No existe ultima compra de combustible',
            );
            $arrData = $this->checkRegister($arrDataSQL);
            if ( $arrData['sStatus'] == "success" ) {
                $arrResponse = $this->checkDiffDays($arrData);
                $arrDelta['deltaCompraComb'] = $arrResponse['number_days'];
            }

            /**
             * deltaMatricula 
             */
            $arrDataSQL = array(
                'sql' => "SELECT DATE(dt_dia) as ultima_fecha, * FROM pos_historia_ladosxtrabajador ORDER BY dt_dia DESC LIMIT 1;",
                'sMessageDanger' => 'Problemas al buscar ultima matricula',
                'sMessageWarning' => 'No existe ultima matricula',
            );
            $arrData = $this->checkRegister($arrDataSQL);
            if ( $arrData['sStatus'] == "success" ) {
                $arrResponse = $this->checkDiffDays($arrData);
                $arrDelta['deltaMatricula'] = $arrResponse['number_days'];
            }

            /**
             * deltaDeposito 
             */
            $arrDataSQL = array(
                'sql' => "SELECT DATE(dt_dia) as ultima_fecha, * FROM pos_depositos_diarios ORDER BY dt_dia DESC LIMIT 1;",
                'sMessageDanger' => 'Problemas al buscar ultimo deposito',
                'sMessageWarning' => 'No existe ultimo deposito',
            );
            $arrData = $this->checkRegister($arrDataSQL);
            if ( $arrData['sStatus'] == "success" ) {
                $arrResponse = $this->checkDiffDays($arrData);
                $arrDelta['deltaDeposito'] = $arrResponse['number_days'];
            }

            /**
             * deltaCaja 
             */
            $arrDataSQL = array(
                'sql' => "SELECT DATE(d_system) as ultima_fecha, * FROM c_cash_transaction ORDER BY d_system DESC LIMIT 1;",
                'sMessageDanger' => 'Problemas al ultimo movimiento de caja',
                'sMessageWarning' => 'No existe ultimo movimiento de caja',
            );
            $arrData = $this->checkRegister($arrDataSQL);
            if ( $arrData['sStatus'] == "success" ) {
                $arrResponse = $this->checkDiffDays($arrData);
                $arrDelta['deltaCaja'] = $arrResponse['number_days'];
            }

            /** 
             * deltaCompra 
             */
            $arrDataSQL = array(
                'sql' => "SELECT DATE(mov_fecha) AS ultima_fecha, * FROM inv_movialma WHERE tran_codigo = '01' ORDER BY mov_fecha DESC LIMIT 1;",
                'sMessageDanger' => 'Problemas al buscar ultima compra de mercaderia (tienda)',
                'sMessageWarning' => 'No existe ultima compra de mercaderia (tienda)',
            );
            $arrData = $this->checkRegister($arrDataSQL);
            if ( $arrData['sStatus'] == "success" ) {
                $arrResponse = $this->checkDiffDays($arrData);
                $arrDelta['deltaCompra'] = $arrResponse['number_days'];
            }

            /**
             * deltaValePendiente 
             */
            $arrDataSQL = array(
                'sql' => "SELECT DATE(dt_fecha) AS ultima_fecha, * FROM val_ta_cabecera WHERE ch_liquidacion IS NULL ORDER BY dt_fecha /*DESC*/ LIMIT 1;",
                'sMessageDanger' => 'Problemas al buscar vale más antiguo y no liquidado',
                'sMessageWarning' => 'No existe vale más antiguo y no liquidado',
            );
            $arrData = $this->checkRegister($arrDataSQL);
            if ( $arrData['sStatus'] == "success" ) {
                $arrResponse = $this->checkDiffDays($arrData);
                $arrDelta['deltaValePendiente'] = $arrResponse['number_days'];
            }

            /**
             * deltaFacturaPendiente 
             */
            $arrDataSQL = array(
                'sql' => "SELECT DATE(dt_fac_fecha) AS ultima_fecha, * FROM fac_ta_factura_cabecera WHERE nu_fac_recargo3 NOT IN ('3','5') ORDER BY dt_fac_fecha /*DESC*/ LIMIT 1;",
                'sMessageDanger' => 'Problemas al buscar documento electrónico de oficina más antiguo y no enviado',
                'sMessageWarning' => 'No existe documento electrónico de oficina más antiguo y no enviado',
            );
            $arrData = $this->checkRegister($arrDataSQL);
            if ( $arrData['sStatus'] == "success" ) {
                $arrResponse = $this->checkDiffDays($arrData);
                $arrDelta['deltaFacturaPendiente'] = $arrResponse['number_days'];
            }

            /**
             * deltaSistema 
             */
            $arrDataSQL = array(
                'sql' => "SELECT DATE(da_fecha) AS ultima_fecha, * FROM pos_aprosys WHERE ch_poscd = 'S' ORDER BY da_fecha DESC LIMIT 1;",
                'sMessageDanger' => 'Problemas al buscar ultimo dia cerrado',
                'sMessageWarning' => 'No existe ultimo dia cerrado',
            );
            $arrData = $this->checkRegister($arrDataSQL);
            if ( $arrData['sStatus'] == "success" ) {
                $arrResponse = $this->checkDiffDays($arrData);
                $arrDelta['deltaSistema'] = $arrResponse['number_days'];
            }
            
            return $arrDelta;
        } catch (Exception $e) {
            return array(
                'sStatus' => 'danger',
                'sMessage' => 'Problemas al obtener data',
                'sMessagePHP' => $e->getMessage(),
            );
        }
    }
}

//Invocamos clase
$objMonitoringInterface = new MonitoringInterface();

echo json_encode($objMonitoringInterface->delta());
exit();
