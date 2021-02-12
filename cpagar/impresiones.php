<?php
//include ("/sistemaweb/utils/funcion-texto.php");

//paginarReporte('/sistemaweb/inventarios/prueba.txt');
//exec("cat /sistemaweb/inventarios/prueba.txt > /dev/lp0");


if($imprimir=="ok")
{
	//paginarReporte('/sistemaweb/inventarios/$archivo');
	exec("smbclient //server14nw/epson -c 'print /sistemaweb/cpagar/$archivo' -P -N -I 192.168.1.1 ");
}

echo "IMPRIMIENDO DIRECTAMENTE....";

?>
<script language="javascript">
	setTimeout("window.close()",1000);
</script>
