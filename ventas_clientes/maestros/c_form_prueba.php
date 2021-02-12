<?php
class FormPruebaController extends Controller
{
    function Init(){
      //Verificar seguridad
      $this->visor = new Visor();
      $this->task = @$_REQUEST["task"];
      $this->action = isset($_REQUEST["action"])?$_REQUEST["action"]:'';
      $this->datos = isset($_REQUEST["datos"])?$_REQUEST["datos"]:'';
      //otros variables de entorno
    }
    
    function Run()
    {
      $this->Init();
      include "t_form_prueba.php";
      include "m_form_prueba.php";
      include('../include/paginador_new.php');
     // $this->visor->addComponent("Titulo", "content_title", "Formulario de prueba");
      switch($this->request)
      {
        case 'FORMPRUEBA':
          $registros = ModelFormPrueba::tmListado();
          $listado = TemplateFormPrueba::FormBusqueda();
          $listado .= TemplateFormPrueba::listado($registros['datos']);
          
          $this->visor->addComponent("ContentB", "content_body", $listado);
        break;

	default:
	  $this->visor->addComponent('ContentB', 'content_body', '<h2>TAREA "'.$this->request.'" NO CONOCIDA EN REGISTROS</h2>');
	break;
      }
    }
}

