<?php
function reporte_diftrans($cod_almacen,$trans_cod){
	pg_exec("begin");
	pg_exec("select reporte_diftrans('$trans_cod','$cod_almacen','ret')");
	//echo "select reporte_diftrans('$trans_cod','$cod_almacen','ret')";
	$rs = pg_exec("fetch all in ret");
	pg_exec("end");
	
	return $rs;
}

function sacarExcelDifTrans($user,$titulo,$almacen,$cabecera,$D){
		$user_temp = $user."_rep2";
		exec("echo -e $almacen >> /var/www/html/sistemaweb/tmp/$user_temp.txt");
		exec("echo -e $cabecera >> /var/www/html/sistemaweb/tmp/$user_temp.txt");
		for($i=0;$i<count($D);$i++){
		exec("echo -e $D[0] ,$D[1] ,$D[2] ,$D[3] ,$D[4] ,$D[5] ,$D[6] ,$D[7] ,$D[8] ,$D[9] ,$D[10] ,$D[11]  >> /var/www/html/sistemaweb/tmp/$user_temp.txt" );
		}
		exec("echo -e '' >> /var/www/html/sistemaweb/tmp/$user_temp.txt");
		
}
