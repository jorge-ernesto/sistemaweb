<?php
if($usuario->obtenerUsuario()!="SISTEMAS" ){
   Header("Location: /sistemaweb/login.php");
}
