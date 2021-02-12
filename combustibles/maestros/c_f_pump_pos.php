<?php

//tail -f /usr/local/apache/logs/error_log
/*
  Fecha de creacion     : Marzo 6, 2012, 11:04 AM
  Autor                 : Nestor Hernandez Loli
  Fecha de modificacion :
  Modificado por        :

  Clase control del mantenimiento de la tabla f_pump_pos
  Bajo supervision de Alvaro Aguayo se indico que solo se
  permita listar actualizar datos, no eliminar, no agregar
 */

class FPumpPosController extends Controller {

    function Init() {
        //Verificar seguridad
        $this->visor = new Visor();
        $this->task = @$_REQUEST["task"];
        $this->action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : '';
        //otros variables de entorno
    }

    function Run() {
        $this->Init();
        $result = '';
        require('maestros/m_f_pump_pos.php');
        require('maestros/t_f_pump_pos.php');
        include('../include/paginador_new.php');


        $template = new FPumpPosTemplate();
        $modelo = new FPumpPosModel();

        $this->visor->addComponent('ContentT', 'content_title', $template->titulo());
        if (!$_REQUEST['rxp'] && !$_REQUEST['pagina']) {
            $_REQUEST['rxp'] = 100;
            $_REQUEST['pagina'] = 0;
        }

        $listado = false;
        //evaluar y ejecutar $action
        switch ($this->action) {
            case 'Modificar':
                $record = $modelo->obtenerRegistro($_REQUEST["registroid"]);
                $result = $template->form($record, 'Actualizar');
                $this->visor->addComponent("ContentB", "content_body", $result);
                break;
           
            case 'Guardar':
                $listado = false;
                $f_pump_pos_id = trim(pg_escape_string($_REQUEST['f_pump_pos_id']));
                $s_pos_id = trim(pg_escape_string($_REQUEST['s_pos_id']));

                $errores = array();

                $tipoGuardar = $_REQUEST['tipo_guardar'];
                if (empty($errores)) {
                    //Redondeando valores en caso que hayan puesto decimales

                    $f = 0;
                    if ($tipoGuardar == 'Actualizar') {
                        $f = $modelo->actualizarRegistro($f_pump_pos_id, $s_pos_id);
                    } 

                    if ($f <= 0) {
                        $result = $template->errorResultado(array('Hubo un error al guardar los datos'));
                        $this->visor->addComponent("error", "error_body", $result . "<br>");
                    } else {
                        $datos = $modelo->obtenerRegistro($f_pump_pos_id);

                        $result = $template->form($datos, $tipoGuardar);

                        $this->visor->addComponent("ContentB", "content_body", $result);
                        $result = $template->errorResultado(array('Se guardaron los datos correctamente'));
                        $this->visor->addComponent("error", "error_body", $result);
                    }
                } else {
                    $datos = $modelo->obtenerRegistro($f_pump_pos_id);

                    $result = $template->form($datos, $tipoGuardar);

                    $this->visor->addComponent("ContentB", "content_body", $result);
                    $result = $template->errorResultado($errores);

                    $this->visor->addComponent("error", "error_body", $result);
                }

                break;
            default:
                //listado
                $listado = true;
                break;
        }
        if ($listado) {
            $rxp = pg_escape_string($_REQUEST['rxp']);
            $pagina = pg_escape_string($_REQUEST['pagina']);
            $listado = $modelo->listado('', $rxp, $pagina);

            $result.= $template->formBuscar($listado["paginacion"]);
            $result .= $template->listado($listado['datos']);
            $this->visor->addComponent("ContentB", "content_body", $result);
        }
    }

}
