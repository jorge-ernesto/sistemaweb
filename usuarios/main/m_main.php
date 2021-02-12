<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
  // Modelo Principal o de defecto

  Class MainModel extends Model{
    function MenuContabilidad($user){
      //Ejecutar logica de seleccion de menu ...
      return join( '', file( './include/menu.inc.php' ) );
    }

    function TituloContabilidad($user) {
      return 'Sistema de Inventarios';
    }
  }
