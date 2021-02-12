<?php
session_start();
include("/sistemaweb/func_print_now.php");
include("/sistemaweb/config.php");
include("/sistemaweb/functions.php");
include("paginar.php");

$sql = "SELECT
		par1.par_valor,
		par2.par_valor,
		par3.par_valor 
	FROM
		int_parametros par1,
		int_parametros par2,
		int_parametros par3 
	WHERE
		par1.par_nombre='print_netbios' 	
		AND par2.par_nombre='print_name' 
		AND par3.par_nombre='print_server'";

$xsql = pg_query($coneccion, $sql);

$print_netbios = trim(pg_result($xsql,0,0));
$print_name = trim(pg_result($xsql,0,1));
$print_server= trim(pg_result($xsql,0,2));

if($imprimir!="paginar")
	$imprimir = "ok";

if($imprimir == "ok"){
	$cmd = "smbclient //$print_netbios/$print_name -c 'print $archivo' -N -I $print_server ";
	exec($cmd);
}elseif($imprimir == "lpr") {
		//El siguiente SQL puede ser modificado a acorde a obtener los datos de la impresora 
		$sql =	"select print_name, print_server from list_impresoras";
		$xsql = pg_query($coneccion, $sql);
		$nombreimp = trim(pg_result($xsql,0,0));
		$ip = trim(pg_result($xsql,0,1));
		
		$smbc = "lpr -H $ip -P $nombreimp $archivo";
		echo $smbc;
		exec ($smbc);
}elseif($imprimir == "paginar"){

	//echo "<br>HOLAA".$_SESSION['ip_printer_default']."<br>";	
	/*
		Las siguientes variables comentadas, son URL de archivos en TXT que formaran parte del reporte
		y deben ser pasadas al archivo como variables en los Links 
		
		$cabecera = url del archivo TXT que contiene la cabecera del reporte
		$cuerpo = url del archivo TXT que contiene el detalle del reporte
		$lineas = al nro de lineas que tendrá el cuerpo del reporte sin contar la cabecera
		$archivo_final = Reportes TXT generado al final con la paginacion respectiva
	ejem.:
	$cabecera = "cabecera.txt";
	$cuerpo = "cuerpo.txt";
	$lineas_cuerpo  = 50;
	$archivo_final = "reporte_final.txt";
	*/

	if(strlen(trim($lineas_cuerpo))==0)	{
		$lineas_cuerpo=50;
	}
	
	formarReporte($cabecera,$cuerpo,$lineas_cuerpo,$archivo_final);
	
	print_now($archivo_final);	
}
echo "IMPRIMIENDO DIRECTAMENTE....";
?>
<SCRIPT language="javascript">
	setTimeout("window.close()",2000);
</SCRIPT>
<?php
	pg_close($coneccion);
