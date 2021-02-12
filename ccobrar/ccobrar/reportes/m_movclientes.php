<?php
  // Modelo para Tarjetas Magneticas

Class MovClientesModel extends Model{

  function ObtenerClientesSaldo($moneda, $fecinicio, $fecfin, $todos, $cliente=''){
  	$q="SELECT ccob_fn_movimientos_clientes('".$moneda."', to_date('".$fecinicio."','dd/mm/yyyy'), to_date('".$fecfin."','dd/mm/yyyy'),'".$todos."','".$cliente."','ret')";
  	print_r($q);
  	pg_exec("begin");
	pg_exec($q); 
	$rs = pg_exec("fetch all in ret");
	pg_exec("close ret");
	pg_exec("end");
	$registro=array();
	while($reg = pg_fetch_array($rs)){
		array_push($registro,$reg);
	}
	return $registro;
  }
  
  function ObtenerMovimientosdeCliente($moneda, $cliente, $fecinicio, $fecfin){
  	$q="SELECT ccob_fn_movimientos_por_cliente('".$moneda."', to_date('".$fecinicio."','dd/mm/yyyy'), to_date('".$fecfin."','dd/mm/yyyy'),'".$cliente."','ret')";
  	pg_exec("begin");
	pg_exec($q); 
	$rs = pg_exec("fetch all in ret");
	pg_exec("close ret");
	pg_exec("end");
	$registro=array();
	while($reg = pg_fetch_array($rs)){
		array_push($registro,$reg);
	}
	return $registro;
  }
}

