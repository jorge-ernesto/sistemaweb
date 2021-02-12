<?php
include_once('../include/m_sisvarios.php');
class MargenLineaController extends Controller
{
    function Init(){
	$this->visor = new Visor();
	isset($_REQUEST['action']) ? $this->action = $_REQUEST['action'] : $this->action = "";
    }
    
    function Run() {
	$this->Init();
	include "reportes/t_margenarticulo.php";
	include "reportes/m_margenarticulo.php";

	$result = '';
	$result_f = '';
	$codalma = '';
	$numlinea = '';
	$form_serch = false;

	switch ($this->action) {
		case 'Buscar':		
			$codalmacen = $_REQUEST['codalmacen'];
			$line = $_REQUEST['codlinea'];
			$resultados = MargenLineaModel::busqueda($line,$codalmacen);
			$detalle = MargenLineaModel::obtenerDetalleLinea($resultados, $codalmacen);
			$result_f = MargenLineaTemplate::listar($resultados, $detalle); 
			break;
		default:
			$form_search = true;
			$resultados = MargenLineaModel::busqueda($numlinea, $_SESSION['almacen']);
			$detalle = MargenLineaModel::obtenerDetalleLinea($resultados, $_SESSION['almacen']); // checar para quitar $codalma	
			$result_f = MargenLineaTemplate::listar($resultados, $detalle);  			
			break;
	}
	if ($form_search)
		$result = MargenLineaTemplate::encabeza();

	$this->visor->addComponent("ContentT", "content_title", MargenLineaTemplate::titulo());// prueba
	if ($result != '') $this->visor->addComponent("ContentB", "content_body", $result);
	if ($result_f != '') $this->visor->addComponent("ContentF", "content_footer", $result_f);
	
    }
}

