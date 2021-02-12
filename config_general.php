<?php
	$v_host			= "localhost";
	$v_url			= "http://$v_host/sistemaweb/";
	$v_path_linux	= "/var/www/html/sistemaweb/";
	$v_path_url		= "/sistemaweb/";

	$v_db			= "integrado";
	$v_user			= "postgres";

	$rutadbf		= "/var/www/html/pccombex/td/bastra.dbf";
	$tamPag			= 15;
	$rutaprint		= "/tmp/imprimir/";

	$fecha 			= getdate();
	$dia 			= date("d");
	$mes 			= date("m");
	$year			= date("Y");
	$hoy 			= $dia.'/'.$mes.'/'.$year;

	if($diad==""){$diad = "01";}
	if($diaa==""){$diaa = "29";}
	if($mesd==""){$mesd = $mes;}
	if($mesa==""){$mesa = $mes;}
	if($anod==""){$anod = $year;}
	if($anoa==""){$anoa = $year;}
