
<?php
pg_connect("dbname=acosa_replicacion user=postgres");

	if($por1==""){$por1 = 0;}
	$listo = false;
	$ar = array(0);
	$max = 0;
		
		$rs = pg_exec("select count(*) from acosa_estaciones where flg_activo =1");
		$total = pg_result($rs,0,0);
		
		$rs = pg_exec("select count(completado) from vista_progreso where completado=true");
		$completado = pg_result($rs,0,0);
		$por2 = ($completado*100)/$total;
		echo "por1 ".$por1."<br>";
		echo "por2 ".$por2."<br>";
		/*if($por2!=$por1){
			$por2 = $por2+$por1;
			
		}*/
		$ava = $por2-$por1;
		print "<script>parent.incrCount($ava);</script>";
		print "<script>parent.document.form1.por1.value='".round($por2)."';</script>";
		//if($por2<100){
			print "<script>parent.verificar();</script>";
		//}
	pg_close();

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Untitled Document</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
BODY!!!!!!!!!
<body>
</body>
</html>
