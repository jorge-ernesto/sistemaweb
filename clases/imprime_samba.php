<?php
$comando="smbclient //".$v_server."/".$v_printer." -c 'print ".$v_archivo."' -N -I ".$v_ipprint." ";
echo $comando ;
exec($comando);
?>
<script>window.close();</script>
