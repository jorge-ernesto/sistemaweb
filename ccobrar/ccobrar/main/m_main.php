<?php
  // Modelo Principal o de defecto

  Class MainModel extends Model{
    function MenuSistema($user){
      //Ejecutar logica de seleccion de menu ...
      return join( '', file( './include/menu.inc.php' ) );
    }

    function TituloSistema($user) {
      return 'Sistema de Cuentas por Cobrar';
    }
  }
