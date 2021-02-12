<?php
session_start();
if(!session_is_registered("usuario")) {
  Header("Location: /sistemaweb/login.php");
  exit;
}
$user=$usuario;
