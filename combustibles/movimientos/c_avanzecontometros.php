<?php

class AvanzeContometrosController extends Controller {
	function Init() {
		$this->visor = new Visor();
	}

    	function Run() {
		include 'movimientos/m_avanzecontometros.php';
		include 'movimientos/t_avanzecontometros.php';

		$this->Init();

		$reporte = AvanzeContometrosModel::obtenerReporte();
		$result = AvanzeContometrosTemplate::mostrarReporte($reporte);

		$this->visor->addComponent("ContentT", "content_title", AvanzeContometrosTemplate::titulo());
		$this->visor->addComponent("ContentB", "content_body", $result);
	}
}
