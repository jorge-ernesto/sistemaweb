<?php
include("funcion-texto.php");

$con = pg_connect("dbname=integrado user=postgres password=postgres host=127.0.0.1");

/*HECHO POR MIGUEL*/
            $Mrs = pg_exec($con,"select to_char(util_fn_fechaactual_aprosys() , 'dd/mm/yyyy') ");
            $MA  = pg_fetch_array($Mrs,0);
            $fec_exp_saldos = $MA[0];
            
	    print $fec_exp_saldos;
	    print "select INFCONSUL_FN_EXPSALDOS(to_date('$fec_exp_saldos','dd/mm/yyyy'))";
	    pg_exec($con,"select INFCONSUL_FN_EXPSALDOS(to_date('$fec_exp_saldos','dd/mm/yyyy') )  " );
/*HECHO POR MIGUEL*/

/*
$rs = pg_exec("select to_char(hora,'dd/mm/yyyy HH24:mm:ss') as hora
,tralad as LADO,tragra as MANG,tragal as GALONES
,tratot as TOTAL ,ntrans as TRAN from pos_nbastra limit 80");

$L[0] = 20;
$L[1] = 4;
$L[2] = 4;
$L[3] = 10;
$L[4] = 10;
$L[5] = 4;

//imprimirTexto($rs,$L,"/tmp/reporte.txt","Reporte de Prueba");

echo "Listo !!!";

$rs = pg_exec("select art_codigo,art_descripcion from int_articulos limit 10");
$T[0] = " Codigo";
$T[1] = "Descripcion";

$L[0] = 15;
$L[1] = 30;
//$tb = rs_a_html($rs);

//imprimir2($tb,$L,$T,"/tmp/prueba13.txt","Reporte del 13");

*/
pg_close($con);

//echo $tb;



?>
<script src="/sistemaweb/utils/cintillo.js"></script>
<form name='form1' method='post' action=''>
<input type='button' name='btn1' onclick="javascript:mostrarCintillo('0140000065','01');">
</form>
