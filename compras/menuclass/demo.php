<html>
<head>
</head>
<body>
<?php
include ('menu.class.php');

$m = new menu();

$m->add(1,'Test','http://128.1.2.70/sistemaweb/compras/cmpr_stock_consol.php','',1,20,70);
	$m->add('1_1','Test','www.google.de','',0,20,70);
$m->add('2','Tests','www.google.de','',1,20,70);
	$m->add('2_1','sTest','www.google.de','',1,20,70);
		$m->add('2_1_1','Test','www.google.de','',0,20,70);
$m->add('3','Tests','www.google.de','',0,20,70);



echo $m->GetHtml();
?>
</body>
</html>
