<?php

$rar=array("uno","dos");
for($x=0;$x<count($rar);$x++ ){
  echo   $rar[$x];
}

for($x=0;$x<count($rar);$x++ ){
     $rar[$x]="jsjssj";
}
var_dump($rar);
