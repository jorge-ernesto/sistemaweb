<?php
	echo "<pre>";
	var_dump($_REQUEST);
	echo "</pre>";

	$bStatusNewParameter=true;
	if ( !isset($_REQUEST['amp;from']) || !isset($_REQUEST['amp;to']) )
		$bStatusNewParameter=false;

    if ($bStatusNewParameter==false){
		if (!isset($_REQUEST['from']) || !isset($_REQUEST['to']))
			die("ERR_INVALID_ARGS_RANGED");
	}

	if ($bStatusNewParameter){
		$CxBegin = $_REQUEST['amp;from'];
		$CxEnd = $_REQUEST['amp;to'];
	} else {
		$CxBegin = $_REQUEST['from'];
		$CxEnd = $_REQUEST['to'];
	}
	
	var_dump($CxBegin);



