<?php

class LiquidacionGastosController extends Controller {

    function Init() {
        $this->visor = new Visor();
        isset($_REQUEST['action']) ? $this->action = $_REQUEST['action'] : $this->action = '';
    }

    function Run() {
        include 'movimientos/m_liquidacion_gastos.php';
        include 'movimientos/t_liquidacion_gastos.php';

        $this->Init();

        $result = "";
        $result_f = "";
        $form_search = false;
        $listado = false;
        $editar = false;
        $actualizar = false;

        switch ($this->action) {
            case "Buscar":
                $almacenes = LiquidacionGastosModel::obtenerAlmacenes();
                $tipos = LiquidacionGastosModel::obtenerTipoGasto();
//				$result = LiquidacionGastosTemplate::formSearch($almacenes,$tipos);
                $resultados = LiquidacionGastosModel::buscar($_REQUEST['almacen'], $_REQUEST['fecha'], $_REQUEST['fecha2']);
                $result_f = LiquidacionGastosTemplate::resultadosBusqueda($resultados, $almacenes, $tipos);
                break;
            case "Agregar":
                $almacenes = LiquidacionGastosModel::obtenerAlmacenes(); //print_r($almacenes);
                $tipos = LiquidacionGastosModel::obtenerTipoGasto(); //print_r($tipos);
                $dias = LiquidacionGastosModel::obtenerDiasIngreso(); //print_r($dias);
                $result = LiquidacionGastosTemplate::formAgregar($almacenes, $tipos, $dias);
                $result_f = "&nbsp;";
                break;
            case "Guardar":
                $res = LiquidacionGastosModel::agregar($_REQUEST['almacen'], $_REQUEST['tipo_gasto'], $_REQUEST['fecha'], $_REQUEST['descripcion'], $_REQUEST['importe'], $_SESSION['auth_usuario']);
                if ($res == TRUE) {
                    $almacenes = LiquidacionGastosModel::obtenerAlmacenes();
                    $result = LiquidacionGastosTemplate::formSearch($almacenes);
                    $result_f = "<script>alert('Se ha registrado el gasto correctamente');</script>";
                } else {
                    $result_f = "<script>alert('No se pudo registrar el gasto. Intente nuevamente');</script>";
                }
                break;
            case "Regresar":
                $almacenes = LiquidacionGastosModel::obtenerAlmacenes();
                $result = LiquidacionGastosTemplate::formSearch($almacenes);

                break;
            case "Editar":
                $id = trim($_REQUEST['editar_text']);

                $almacenes = LiquidacionGastosModel::obtenerAlmacenes(); //print_r($almacenes);
                $databusqueda = LiquidacionGastosModel:: BuscargastoxLiquidacion($id);
                $tipos = LiquidacionGastosModel::obtenerTipoGasto(); //print_r($tipos);
                $dias = LiquidacionGastosModel::obtenerDiasIngreso(); //print_r($dias);
                $result = LiquidacionGastosTemplate::formEdit($almacenes, $tipos, $dias, $databusqueda);
                $result_f = "&nbsp;";

                break;
            case "Actualizar":
                $id = trim($_REQUEST['editar_text']);
                $almacen = $_REQUEST['almacen'];
                $tipo_gasto = $_REQUEST['tipo_gasto'];
                $fecha = $_REQUEST['fecha'];
                $descripcion = $_REQUEST['descripcion'];
                $importe = $_REQUEST['importe'];
                $usuario = $_SESSION['auth_usuario'];
                $estado = LiquidacionGastosModel::Actualizar($almacen, $tipo_gasto, $fecha, $descripcion, $importe, $usuario, $id);
                if ($estado) {
                    echo "<script type='text/javascript'>alert('Se actualizo correctamente');</script>";
                } else {
                    echo "<script type='text/javascript'>alert('malal');</script>";
                }
                $result_f = "&nbsp;";
                break;
            case "Eliminar":
                $id = trim($_REQUEST['editar_text']);
                $estado = LiquidacionGastosModel::Eliminar($id);
                if ($estado) {
                    echo "<script type='text/javascript'>alert('Se Elimino correctamente');</script>";
                }
                $result_f = "&nbsp;";
                break;
            default:
                $almacenes = LiquidacionGastosModel::obtenerAlmacenes();
                $result = LiquidacionGastosTemplate::formSearch($almacenes);
                break;
        }

        /* 		if ($listado) {
          $resultados = LiquidacionGastosModel::busqueda($_REQUEST['ch_almacen'], $_REQUEST['ch_fecha'], $_REQUEST['ch_turno']);
          $result_f = LiquidacionGastosTemplate::listado($resultados);
          }

          if ($actualizar) {
          $result = ((LiquidacionGastosModel::actualizarFila($_REQUEST['ch_almacen'],$_REQUEST['dt_dia'],$_REQUEST['ch_posturno'],$_REQUEST['ch_codigo_trabajador'],$_REQUEST['ch_numero_documento'],$_REQUEST['nvalida'],$_REQUEST['ndia'],$_REQUEST['nturno'],$_REQUEST['ncodtrab'])==false)?'<p  styke="color:red;font-weight:bold;text-align:center;">Error al actualizar la fila</p>':'<p styke="font-weight:bold;text-align:center;">Fila actualizada</p>');
          }

          if ($editar) {
          $fila =  LiquidacionGastosModel::obtenerFila($_REQUEST['ch_almacen'],$_REQUEST['dt_dia'],$_REQUEST['ch_posturno'],$_REQUEST['ch_codigo_trabajador'],$_REQUEST['ch_numero_documento']);
          $trab = LiquidacionGastosModel::obtenerTrabajadores();
          $result_f = LiquidacionGastosTemplate::formEdit($fila[''],$trab);
          } */


        $this->visor->addComponent("ContentT", "content_title", LiquidacionGastosTemplate::titulo());
        if ($result != "")
            $this->visor->addComponent("ContentB", "content_body", $result);
        if ($result_f != "")
            $this->visor->addComponent("ContentF", "content_footer", $result_f);
    }

}

