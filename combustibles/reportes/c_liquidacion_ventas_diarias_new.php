<?php

date_default_timezone_set('UTC');

class liquidacion_ventas_diariasController extends Controller{

	function __construct($visor){
		include("m_liquidacion_ventas_diarias_new.php");
		$this->modelo=new liquidacion_ventas_diariasModel();
		$this->visor=$visor;
	}

	function post(){

		$_POST["almacen"] 	=$_POST['almacen'];
		$_POST["dia_desde"]	=(empty($_POST["dia_desde"]))?date("d"):$_POST["dia_desde"];
		$_POST["dia_hasta"]	=(empty($_POST["dia_hasta"]))?date("d"):$_POST["dia_hasta"];
		$_POST["mes"]		=(empty($_POST["mes"]))?date("m"):$_POST["mes"];
		$_POST["anio"]		=(empty($_POST["anio"]))?date("Y"):$_POST["anio"];
		$_POST["fecha_del"]	=$_POST["anio"]."-".$_POST["mes"]."-".$_POST["dia_desde"];
		$_POST["fecha_al"]	=$_POST["anio"]."-".$_POST["mes"]."-".$_POST["dia_hasta"];

	}
    
	function run(){
		ob_start();
		$this->vista();
		$this->visor->addComponent("Content", "content_body", ob_get_contents());
	}
    
	function f($number){
		return number_format($number, 2, '.', ',');
	}
    
	function vista(){

	        $this->post();
        	$almacen			= $this->modelo->listado_almacen();
        	$venta_combustible		= $this->modelo->venta_combustible();
        	$diferencia_precio		= $this->modelo->diferencia_precio();
        	$descuentos			= $this->modelo->descuentos();
        	$consumo_interno		= $this->modelo->consumo_interno();
        	$afericiones			= $this->modelo->afericiones();
        	$venta_productos_promo		= $this->modelo->venta_productos_promociones();
        	$vales_credito			= $this->modelo->vales_credito();
        	$tarjetas_credito		= $this->modelo->tarjetas_credito();
        	$depositos_pos			= $this->modelo->depositos_pos();
        	$sobrantes_faltantes		= $this->modelo->sobrantes_faltantes();
        	$depositos_bancarios		= $this->modelo->depositos_bancarios();
        	$sobrantes_faltantes_manuales	= $this->modelo->sobrantes_faltantes_manuales();

	        include("t_liquidacion_ventas_diarias_new.php");

	}

}

