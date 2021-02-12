<?php

session_start();
include_once('/sistemaweb/include/dbsqlca.php');
$sqlca = new pgsqlDB('localhost', 'postgres', 'postgres', 'integrado');
include('/sistemaweb/include/mvc_sistemaweb.php');

include('/sistemaweb/ventas_clientes/reporte_vales/t_reporte_vales.php');
include('/sistemaweb/ventas_clientes/reporte_vales/m_reporte_vales.php');
include('/sistemaweb/ventas_clientes/reporte_vales/c_reporte_vales.php');

$objmodel = new ReporteValesModel();
$objtem = new ReporteValesTemplate();

$accion = $_REQUEST['accion'];

try {
    if ($accion == "tipodocumento") {
        $tipo_doc_numero = trim($_REQUEST['documento']);
        $result = ReporteValesModel::obtenerTiposDocumento($tipo_doc_numero);
        $cmb_serie = "<select id='serie_doc'>";
        foreach ($result as $value) {
            $cmb_serie.="<option value='$value[0]'>" . $value[0] . "#" . $value[1] . "</option>";
        }
        $cmb_serie.="</select>";
        echo $cmb_serie;
    } else if ($accion == "selecionabtn") {
        $fecha_inicio = $_REQUEST['fecha_inicio'];
        $fecha_final = $_REQUEST['fecha_final'];
        $sNumeroDocumentoIdentidad = $_REQUEST['sNumeroDocumentoIdentidad'];
        $tipo_cal = $_REQUEST['tipo_cal'];
        $ordenar_por = $_REQUEST['ordenar_por'];

        $arrRequest = array(
            'sNumeroDocumentoIdentidad' => trim($_REQUEST['sNumeroDocumentoIdentidad']),
            'sNombreCliente' => trim($_REQUEST['sNombreCliente']),
            'iTipoCliente' => $_REQUEST['iTipoCliente'],
            'iDecimales' => $_REQUEST['iDecimales'],
        );
        $cliente_dia = ReporteValesModel::MostarClientedelDia($fecha_inicio, $fecha_final, $arrRequest);
        // error_log( json_encode($cliente_dia) );

        if ( $cliente_dia['sStatus']=='success' ) {
            $i=0;
            $sTipoCliente='';
            foreach ($cliente_dia['arrData'] as $fila){
                $id_limpio= trim($fila['ch_cliente']);
                $rason_z=trim($fila['cli_razsocial']);
                $iTipoClienteEfectivo=trim($fila['nu_tipo_efectivo']);
                $sTipoClienteAnticipo=trim($fila['no_tipo_anticipo']);

                $sTipoCliente = 'EFECTIVO';
                if ( $iTipoClienteEfectivo == '0' && $sTipoClienteAnticipo == 'N' ){
                    $sTipoCliente = 'CREDITO';
                } else if ( $iTipoClienteEfectivo == '0' && $sTipoClienteAnticipo == 'S' ){
                    $sTipoCliente = 'ANTICIPO';
                }

                $reg_x_cliente=ReporteValesModel::MostarClienteVales_rangoFecha($fecha_inicio, $fecha_final,$id_limpio,$ordenar_por);
                // error_log( json_encode($reg_x_cliente) );
                ReporteValesTemplate::CrearTablaSeleccionarCliente($reg_x_cliente,$id_limpio,$tipo_cal,$rason_z, $sTipoCliente, $_REQUEST['iDecimales']);                
            }

            /*if (count($result) == 0) {
                echo "ERROR_:Busqueda sin Resultado Verifique sus datos de Ingreso";
                return;
            }*/
            return;
            //ReporteValesTemplate::CrearTablaSeleccionarCliente($result);
        } else {
            echo $cliente_dia['sMessage'];
            return;
        }
    } else if ($accion == "ver_vales") {

        $fecha_inicio = $_REQUEST['fecha_inicio'];
        $fecha_final = $_REQUEST['fecha_final'];
        $ruc = $_REQUEST['ruc'];
        $vales_sele = $_REQUEST['valesselecionada'];
        $vales_sele = str_replace("'", "", $vales_sele);

        $result = ReporteValesModel::MostarValesDeUnCliente($fecha_inicio, $fecha_final, $ruc);
        $datos_cliente = ReporteValesModel::ObtenerdatosCliente($ruc);
        ReporteValesTemplate::CrearTablaVervales($result, $datos_cliente, $fecha_inicio, $fecha_final, $vales_sele);
    } else if ($accion == "buscar_liquidacion") {
        $fecha_inicio = $_REQUEST['fecha_inicio'];
        $fecha_final = $_REQUEST['fecha_final'];
        try {
            $datos_liquidaciones = ReporteValesModel::BuscarLiquidaciones($fecha_inicio, $fecha_final);
            if (count($datos_liquidaciones) == 0) {
                throw new Exception("Error");
            }
            ReporteValesTemplate::CrearTabladatosLiquidacionProducto_Busqueda($datos_liquidaciones,'');
        } catch (Exception $e) {
            echo "ERROR_:No se encontro liquidaciones disponibles";
            exit();
        }
    } else if ($accion == "liquidar_vales") {
        //formato fecha
        $fecha_inicio = $_REQUEST['fecha_inicio'];
        $fecha_final = $_REQUEST['fecha_final'];
        $codigo_hermandad = date("dnHis");


        $aregloOficialClientes = array();
        foreach ($_REQUEST['vales'] as $key => $value) {
            $cod_cliente = trim(substr($value, 0, strpos($value, "-")));

            $vales = trim(substr($value, strpos($value, "-") + 1));
            $cadena_vales = "(" . substr($value, strpos($value, "-") + 1, -1) . ")";
            if (strcmp($vales, 'NOALL') == 0) {
                continue;
            } else if (strcmp($vales, 'ALL') == 0) {
                $datosrs = ReporteValesModel::MostarValesDeUnCliente($fecha_inicio, $fecha_final, $cod_cliente);
                $cadena_vales = "(";
                foreach ($datosrs as $key => $value) {
                    $cadena_vales.= "'" . trim($value['ch_documento']) . "',";
                }
                $cadena_vales = substr($cadena_vales, 0, -1) . ")";
                $aregloOficialClientes[$cod_cliente] = $cadena_vales;
            } else {
                $aregloOficialClientes[$cod_cliente] = $cadena_vales;
            }
        }

        if (count($aregloOficialClientes) == 0) {
            echo "ERROR_:Debe Seleccionar Clientes o notas de Despachos.";
        }
    }
} catch (Exception $r) {
    echo $r->getMessage();
}
