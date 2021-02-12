<?php

/*
  MVC sistemaweb
  Set de clases para desarrollo MVC en el sistema integrado de sistemaweb
  Autor: TBCA
*/

// Clase Modelo.. totalmente abstracta
class Model {
}

// Clase Visor
class Visor {
  var $components;
  function __construct(){
    $this->components = array();
  }

  function addComponent($name, $target, $content){
   $this->components[$name] = array("target"=>$target, "content"=>$content);
  }

  function showVisor(){
    $view = '';
    foreach($this->components as $name => $visor) {
      $view .= '<div id="'.$name.'">'.
                       $visor["content"].
                   '</div>'."\n";
      $view .= '<script type="text/javascript">'.
                      'if (top.document.getElementById("'.$visor["target"].'"))'.
                      'top.document.getElementById("'.$visor["target"].'").innerHTML = '.
                      'document.getElementById("'.$name.'").innerHTML;'.
                    '</script>'."\n";
    }
    return $view;
  }
}

//Tipo simple de visor ..
class Template{
}

// Clase Controlador
class Controller{
  var $visor;
  var $request;
  var $task;
  var $action;

  function __construct($request){
    $this->request = strtoupper($request);
    $visor = new Visor();
  }

  function Init(){
    //set enviroment
    //define action
  }

  function Run(){
    //execute action
  }

  function outputVisor(){
    return $this->visor->showVisor();
  }

}

?>