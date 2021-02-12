<?php

class ClubesController extends Controller
{
    function Init()
    {
        $this->visor = new Visor();
        isset($_REQUEST['action'])?$this->action=$_REQUEST['action']:$this->action="";
    }

    function Run()
    {
        include "maestros/m_clubes.php";
        include "maestros/t_clubes.php";

        $this->Init();

        $result = "";
        $result_f = "";
        $form_search = true;
        $listado = true;

        switch ($this->action) {
            case "Borrar":
                ClubesModel::borrar($_REQUEST['clubes']);
                $listado = true;
                break;
            case "Guardar":
                ClubesModel::guardar($_REQUEST['clubes'], $_REQUEST['descripciones']);
                $listado = true;
                break;
            case "Agregar":
                ClubesModel::agregar($_REQUEST['tab_elemento'], $_REQUEST['tab_descripcion']);
                $listado = true;
                $form_search = true;
                break;
            default:
                $form_search = true;
                $listado = true;
                break;
        }

        if ($listado) {
            $resultados = ClubesModel::obtenerClubes();
            $result_f = ClubesTemplate::listado($resultados);
        }

        if ($form_search) {
            $result = ClubesTemplate::formAgregar();
        }

        $this->visor->addComponent("ContentT", "content_title", ClubesTemplate::titulo());
        if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
        if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);
    }
}

