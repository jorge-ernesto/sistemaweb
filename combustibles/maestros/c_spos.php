<?php

/*
  Fecha de creacion     : Feb 24, 2012, 4:22:17 PM
  Autor                 : Nestor Hernandez Loli
  Fecha de modificacion :
  Modificado por        :

  Clase control del mantenimiento de la tabla spos
 * Bajo supervision de Alvaro Aguayo se indico que solo se
  permita listar actualizar datos, no eliminar, no agregar
 */

class SPosController extends Controller {

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
        require('maestros/m_spos.php');
        require('maestros/t_spos.php');
        include('../include/paginador_new.php');


        $template = new SPosTemplate();
        $modelo = new SPosModel();

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

                $s_pos_id = pg_escape_string($_REQUEST["s_pos_id"]);

                $printerserial = trim(pg_escape_string($_REQUEST["printerserial"]));

                $taxauthorization = trim(pg_escape_string($_REQUEST["taxauthorization"]));

                $warehouse = trim(pg_escape_string($_REQUEST["warehouse"]));

                $pricelist = trim(pg_escape_string($_REQUEST["pricelist"]));

                $ejectconfig = trim(pg_escape_string($_REQUEST["ejectconfig"]));

                $ejectlines = trim(pg_escape_string($_REQUEST["ejectlines"]));

                $errores = array();


                /* empty devuelve true s el valor es '', null o 0, o 0.0 */

                if (empty($printerserial)) {
                    $errores[] = "El campo Numero serie es requerido";
                }
                if (empty($taxauthorization)) {
                    $errores[] = "El campo Autorizacion Sunat es requerido";
                }
                if (empty($warehouse)) {
                    $errores[] = "El campo Almacen es requerido";
                }
                if (empty($pricelist)) {
                    $errores[] = "El campo Lista de precio es requerido";
                }

                if (!is_numeric($ejectconfig)) {
                    $errores[] = "El campo Eject debe ser un valor entero";
                }
                if (!is_numeric($ejectlines)) {
                    $errores[] = "El campo Lineas en blanco debe ser un valor entero";
                }

                $tipoGuardar = $_REQUEST['tipo_guardar'];
                if (empty($errores)) {

                    $ejectconfig = intval($ejectconfig);
                    $ejectlines = intval($ejectlines);

                    $f = 0;
                    if ($tipoGuardar == 'Actualizar') {
                        $f = $modelo->actualizarRegistro($s_pos_id, $printerserial, $taxauthorization, $warehouse, $pricelist, $ejectconfig, $ejectlines);
                    }

                    if ($f <= 0) {
                        $result = $template->errorResultado(array('Hubo un error al guardar los datos'));
                        $this->visor->addComponent("error", "error_body", $result . "<br>");
                    } else {

                        $datos = $modelo->obtenerRegistro($s_pos_id);

                        $result = $template->form($datos, $tipoGuardar);

                        $this->visor->addComponent("ContentB", "content_body", $result);
                        $result = $template->errorResultado(array('Se guardaron los datos correctamente'));
                        $this->visor->addComponent("error", "error_body", $result);
                    }
                } else {
                    $datos = $modelo->obtenerRegistro($s_pos_id);

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
