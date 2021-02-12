<?php

session_start();
include_once('/sistemaweb/include/dbsqlca.php');
$sqlca = new pgsqlDB('localhost', 'postgres', 'postgres', 'integrado');
include('/sistemaweb/include/mvc_sistemaweb.php');

include('TrabajorXisla/t_matricula_trabajador.php');
include('TrabajorXisla/m_matricula_trabajador.php');
include('TrabajorXisla/trabajor_isla_pdf.php');

$objmodel	= new matricula_personal_Model();
$objtem		= new matricula_personal_Template();
$pdf		= new trabajor_isla_pdf();

$accion = $_REQUEST['accion'];

try {

	if($accion == "fecha_servidor"){

        	$data = matricula_personal_Model::int_sucursales();
		$info = "";
	
		foreach ($data as $key => $value) {
			$info.="<option value=" . $value['ch_sucursal'] . " >" . $value['ch_nombre_sucursal'] . "</option>";
		}

		echo $info . "?" . date("Y-m-d");

	} else if ($accion == "matricula_buscar") {

        	$fecha_find	= $_REQUEST['fecha_inicio'];
        	$id_turno	= $_REQUEST['id_turno'];
        	$sucursal	= $_REQUEST['sucursal'];

        	$trabajadores_matriculado	= matricula_personal_Model::VerTrabajdor_X_Asignado($fecha_find, $id_turno, $sucursal);
        	$lados				= matricula_personal_Model::ObtenerLados($sucursal);
        	$trabajores			= matricula_personal_Model::ObtenerTrabajadores();
        	$punto_vt_market		= matricula_personal_Model::ObtenerPuntoMarket($sucursal);

        	matricula_personal_Template::CrearTablaMatricula($lados, $trabajores, $punto_vt_market, $trabajadores_matriculado);
        
	} else if ($accion == "btnmatricular_pv") {

		$fecha_find	= $_REQUEST['fecha_inicio'];
		$id_turno	= $_REQUEST['id_turno'];
		$sucursal	= $_REQUEST['sucursal'];//sucursal

		$trabajadores_matriculado	= matricula_personal_Model::VerTrabajdor_X_Asignado($fecha_find, $id_turno, $sucursal);
		$lados				= matricula_personal_Model::ObtenerLados($sucursal);
		$trabajores			= matricula_personal_Model::ObtenerTrabajadores();
		$punto_vt_market 		= matricula_personal_Model::ObtenerPuntoMarket($sucursal);
		$lados_relacionado 		= matricula_personal_Model::searchPV();

		matricula_personal_Template::CrearTablaMatricula_PV($lados, $trabajores, $punto_vt_market, $trabajadores_matriculado, $lados_relacionado);

        } else if ($accion == "guardar_info") {

    		$almacen	= $_REQUEST['sucursal']; //primero captura lo que viene
    		$dt_dia		= $_REQUEST['fecha_asignar'];
    		$ch_posturno	= $_REQUEST['turno'];
    		$data_comb	= $_REQUEST['data_envia'];
    		$data_envia_pv	= $_REQUEST['data_envia_pv'];
    		$no_select	= $_REQUEST['no_select'];

    		if ($almacen == NULL || empty($almacen)) {
    			$almacen = $_SESSION['almacen'];
    		}

            if ($almacen == NULL || empty($almacen)) {
    			$almacen = matricula_personal_Model::ObtenerSucursal();
            }

            if ($almacen == NULL || empty($almacen)) {
    			throw new Exception("ERROR ALMACEN SE PERDIO");
            }

            /*
            foreach ($data_comb as $valorcmb) {
                $ch_lado                = trim($valorcmb['lado']);
                $ch_codigo_trabajador   = trim($valorcmb['cod_tra']);

                // Validar que el trabajador no esté matriculado para LIQUIDOS y GLP
                $arrResponse = matricula_personal_Model::obtenerLadoxProducto($ch_lado);
                if ( $arrResponse['sStatus'] != 'success' ){
                    throw new Exception($arrResponse['sMessage']);
                }

                foreach ($arrResponse['arrData'] as $row) {
                    if (trim($row['product']) == '11620307' || trim($row['product']) == '11620306') {
                        $arrEmployeeGLP[] = $ch_codigo_trabajador;
                    }

                    if (trim($row['product']) != '11620307' && trim($row['product']) != '11620306') {
                        $arrEmployeeLiquidos[] = $ch_codigo_trabajador;
                    }
                }
                // ./ Validacion
            }

            $arrEmployeeGLP = array_unique($arrEmployeeGLP);
            $arrEmployeeLiquidos = array_unique($arrEmployeeLiquidos);

            $bStatusEmployeeGLPLiquidos = false;
            foreach ($arrEmployeeGLP as $row_employee_glp) {
                foreach ($arrEmployeeLiquidos as $row_employee_liquido) {
                    if ( $row_employee_glp == $row_employee_liquido){
                        $bStatusEmployeeGLPLiquidos = true;
                    }
                }
            }

            // Estado de trabajador para LIQUIDOS y GLP
            if ( $bStatusEmployeeGLPLiquidos ) {
                //echo "No se puede matricular el mismo trabajador para GLP y Liquidos";
            } else {
            */
            	foreach ($data_comb as $valorcmb) {
            		$ch_lado		= trim($valorcmb['lado']);
            		$ch_codigo_trabajador	= trim($valorcmb['cod_tra']);

            		$verifica2 = matricula_personal_Model::Obtenersusursaldesdelado($ch_lado);
            		if ($almacen == $verifica2){
            		  matricula_personal_Model::AsignarTrabajador($almacen, $dt_dia, $ch_posturno, $ch_lado, $ch_codigo_trabajador, 'C');
            		} else {
            		  throw new Exception("Almacen no corresponde al lado.");
        			}
            	}

            	foreach ($data_envia_pv as $valorcmb) {
            		$ch_lado		= trim($valorcmb['pv']);
            		$ch_codigo_trabajador	= trim($valorcmb['cod_tra']);

            		$verifica = matricula_personal_Model::Obtenersusursaldesdepv($ch_lado);

            		if ($almacen == $verifica){
            			matricula_personal_Model::AsignarTrabajador($almacen, $dt_dia, $ch_posturno, $ch_lado, $ch_codigo_trabajador, 'M');
            		}
            		else{
            			throw new Exception("Almacen no corresponde al punto de venta.");
        			}
            	}

            	foreach ($no_select as $valorcmb_noselect) {

    			$ch_lado		= trim($valorcmb_noselect['lado']);
                		$ch_codigo_trabajador	= trim($valorcmb_noselect['cod_tra']);
                		$tipo			= trim($valorcmb_noselect['tipo']);

                		matricula_personal_Model::EliminarTrabajador($almacen, $dt_dia, $ch_posturno, $ch_lado, $ch_codigo_trabajador, $tipo);
            	}
            	echo "PROCESO EXITOSO";
            //} // ./ Validacion de matricula de GLP y Liquido
    	} else if ($accion == "reporte_trabajor") {

        	$fecha_ini	= $_REQUEST['fecha_inicio'];
		$fecha_final	= $_REQUEST['fecha_final'];
		$cod_trabajor	= trim($_REQUEST['cod_trabajor']);
		$sucursal	= $_REQUEST['sucursal'];

		$registros	= matricula_personal_Model::ObtenerTrabajadores_Asignado($fecha_ini, $fecha_final, $cod_trabajor, $sucursal);

		matricula_personal_Template::CrearTablaReporte($registros, $fecha_ini, $fecha_final, $cod_trabajor, $sucursal);
		//matricula_personal_Template::CrearTablaReporte($registros);

	} else if ($accion == "pdf_trabajador") {

        	$f_ini		= $_REQUEST['fecha_ini'];
		$f_final	= $_REQUEST['fecha_final'];
		$cod_trabajor	= $_REQUEST['cod_trabajor'];

        	$resultado	= matricula_personal_Model:: search($f_ini, $f_final, $cod_trabajor);

        	trabajor_isla_pdf::TmpReportePDFFactura($resultado, $f_ini, $f_final);

        	header("Location: /sistemaweb/ventas_clientes/reportes/pdf/trabajador_x_isla.pdf");

	}

} catch (Exception $r) {
	echo $r->getMessage();
}

