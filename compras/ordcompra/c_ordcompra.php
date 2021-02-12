<?php
  // Controlador Registros Contables

Class RegistrosController extends Controller{
  function Init(){
    //Verificar seguridad
    $this->visor = new Visor();
    isset($_REQUEST["action"])?$this->action = $_REQUEST["action"]:$this->action = '';
      //otros variables de entorno
  }

  function Run(){
    $this->Init();
    $result = '';
    include('movimientos/m_registros.php');
    include('movimientos/t_registros.php');
    $this->visor->addComponent('ContentT', 'content_title', RegistrosTemplate::titulo());
    switch ($this->request){//task
      case 'ORDCOMPRA':
        $listarAsientos = false;
        switch($this->action){
          case 'Agregar':
          break;

          case 'Buscar':
          break;

          case 'Guardar':
          break;

          case 'Modificar':
          break;

          case 'Guardar Detalle':
          break;

          case 'ModificarDetalle':
          break;

          case 'Actualizar Detalle':
          break;

          case 'Nuevo Detalle':
          break;
          case 'EliminarDetalle':
          break;
          default:
            //listar ultimos movimientos
            //$listarAsientos = true;
          break;
        }
      break;

      case 'ORDCOMPRADET':
        switch($this->action){
          case 'setPerfilPLC':
            $result = RegistrosTemplate::setPerfilPLC($_REQUEST["codigo"]);
            $this->visor->addComponent("plc_perfil", "plc_perfil", $result);
          break;

          default:
            //listar ultimos movimientos
          break;
        }
      break;

      default:
        $this->visor->addComponent('ContentB', 'content_body', '<h2>TAREA "'.$this->request.'" NO CONOCIDA EN REGISTROS</h2>');
      break;
    }
  }
}
