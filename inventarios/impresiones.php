<?php
if($imprimir=="ok")
{
	exec("smbclient //server14nw/epson -c 'print /sistemaweb/inventarios/$archivo' -P -N -I 192.168.1.1 ");
}

echo "IMPRIMIENDO DIRECTAMENTE....";

?>
<SCRIPT language="javascript">
	setTimeout("window.close()",1000);
</script>
