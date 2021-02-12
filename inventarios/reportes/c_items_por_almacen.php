<?php

session_start();

include_once('/sistemaweb/include/dbsqlca.php');
$sqlca = new pgsqlDB('localhost', 'postgres', 'postgres', 'integrado');
include('/sistemaweb/include/mvc_sistemaweb.php');

include('t_items_por_almacen.php');
include('m_items_por_almacen.php');

/* Get Class Template y Model */

$template 	= new TemplateItemsPorAlmacen();
$model 	= new ModelItemsPorAlmacen();

/* Get Variables de Request */

$accion = $_REQUEST['accion'];
$_SESSION['data_excel']	= null;

try {

	if ($accion == "search") {

		$data = $model->search($_REQUEST);
		$template->ListaStockLinea($data, $_REQUEST, $fecha);

		$v_sqlx 	= "select par_valor from int_parametros where trim(par_nombre)='print_netbios' ";
		$v_xsqlx 	= pg_exec( $v_sqlx);
		$v_server	= pg_result($v_xsqlx,0,0);

		$v_sqlx 	= "select par_valor from int_parametros where trim(par_nombre)='print_name' ";
		$v_xsqlx 	= pg_exec($v_sqlx);
		$v_printer 	= pg_result($v_xsqlx,0,0);

		$v_sqlx 	= "select par_valor from int_parametros where trim(par_nombre)='print_server' ";
		$v_xsqlx	= pg_exec($v_sqlx);
		$v_ipprint	= pg_result($v_xsqlx,0,0);

		$v_archivo	= "/tmp/imprimir/reporte_de_stock.txt";
		$file 		= "/tmp/imprimir/Reporte_StkItemAlmacen";

		$fh = fopen($file, "w");
		fwrite($fh,"");
		fclose($fh);

	} else if ($accion == "exportExcel") {

		$data		= $model->search($_REQUEST);
		$nualmacen	= $model->GetAlmacen($_REQUEST['nualmacen']);

		$template->ListaStockLinea($data, $_REQUEST, $fecha);

		if(!empty($data)){
			$_SESSION['data_1010']	= $data;
			$_SESSION['noalmacen']	= $nualmacen[0]['noalmacen'];
			//$_SESSION['fecha_inicio']	= $_REQUEST['fecha_inicio'];
			$_SESSION['nuyear']			= $_REQUEST['nuyear'];
			$_SESSION['numonth']		= $_REQUEST['numonth'];
			//$_SESSION['fbuscar']	= $_REQUEST['fbuscar'];
			$_SESSION['p_stock']	= $_REQUEST['p_stock'];
			$_SESSION['c_stock']	= $_REQUEST['c_stock'];
			$_SESSION['n_stock']	= $_REQUEST['n_stock'];
			$_SESSION['utilidad'] = $_REQUEST['utilidad'];
			$_SESSION['simple'] = $_REQUEST['simple'];
		}

		//var_dump($_SESSION['data_1010']);

	} else if($accion == 'print') {
		$result = array();
		$arch = "/tmp/imprimir/Reporte_StkItemAlmacen";
		$result['data'] = $model->getDataPrint();
		$result['error'] = true;
		if($result['data']['error']) {
			$result['cmd'] = 'lpr -H '.$result['data']['data']['ip'].' -P '.$result['data']['data']['prn_samba'].' '.$arch;
			/*$fp = fopen("COMANDO.txt","a");
			fwrite($fp, "-".$smbc."-".PHP_EOL);
			fclose($fp);  	
			exec($result['cmd']);*/
		}
		echo json_encode($result);
	}

} catch (Exception $r) {
	echo $r->getMessage();
}

