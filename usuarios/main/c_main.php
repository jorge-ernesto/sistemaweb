<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
  // Controlador Principal o de defecto

  Class MainController extends Controller{
    function Init(){
      //Verificar seguridad
      $this->visor = new Visor();
      $this->task = 'MAIN'; //testeo login
      //otros variables de entorno
    }

    function Run(){
      $this->Init();
      switch ($this->task){
        case 'MAIN':
          include ('main/m_main.php');
          //$this->visor->addComponent("Header", "header", MainModel::TituloContabilidad('JCP'));
          //$this->visor->addComponent("Menu", "menu", MainModel::MenuContabilidad('JCP'));
          $this->visor->addComponent("ContentB", "content_body", "Aqui es el escritorio");
//	  $this->visor->addComponent
          break;

        case 'LOGIN':
          break;
        
        default:
          break;
      }
    }
  }
