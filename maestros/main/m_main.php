<?php
  // Modelo Principal o de defecto

  Class MainModel extends Model{
    function MenuContabilidad($user){
      //Ejecutar logica de seleccion de menu ...
      return join( '', file( './include/menu.inc.php' ) );
    }

    function TituloContabilidad($user) {
      return 'Sistema de Compras';
    }
  }
