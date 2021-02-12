<?php

class ExtornoController extends Controller {
	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action']) ? $this->action = $_REQUEST['action']:$this->action='';
    	}
    
    	function Run() {
		include 'movimientos/m_extorno.php';
		include 'movimientos/t_extorno.php';
	
		$this->Init();
	
		$result = '';
		$result_f = '';

		switch ($this->action) {
			case "Programar":
				$ticket = ExtornoModel::obtenerUltimoTicket($_REQUEST['lado']);
				if ($ticket === NULL) {
					$result_f = ExtornoTemplate::mostrarError(1);
					break;
				} else if ($ticket === FALSE) {
					$result_f = ExtornoTemplate::mostrarError(2);
					break;
				} else if ($ticket['nregtr'] === NULL) {
					$result_f = ExtornoTemplate::mostrarError(4);
					break;
				}
				$tds = ExtornoModel::obtenerTiposDoc();
				$result = ExtornoTemplate::formUltimoTicket($ticket,$tds);
				break;
			case "Siguiente":
				$ticket = ExtornoModel::obtenerUltimoTicket($_REQUEST['o_pump']);
				if ($ticket === NULL) {
					$result_f = ExtornoTemplate::mostrarError(1);
					break;
				} else if ($ticket === FALSE) {
					$result_f = ExtornoTemplate::mostrarError(2);
					break;
				} else if ($ticket['trans'] != $_REQUEST['o_trans']) {
					$result_f = ExtornoTemplate::mostrarError(3);
					break;
				}
				$tds = ExtornoModel::obtenerTiposDoc();
				$fps = ExtornoModel::obtenerFormasPago();
				$tts = ExtornoModel::obtenerTiposTarjeta();
				$result = ExtornoTemplate::formNuevoTicket($_REQUEST['td'],$ticket,$tds,$fps,$tts);
				break;
			case "Extornar":
				$ticket = ExtornoModel::obtenerUltimoTicket($_REQUEST['o_pump']);
				if ($ticket === NULL) {
					$result_f = ExtornoTemplate::mostrarError(1);
					break;
				} else if ($ticket === FALSE) {
					$result_f = ExtornoTemplate::mostrarError(2);
					break;
				} else if ($ticket['trans'] != $_REQUEST['o_trans']) {
					$result_f = ExtornoTemplate::mostrarError(3);
					break;
				}
				$r = ExtornoModel::extornar($ticket,$_REQUEST);
				$rx = ExtornoTemplate::mostrarError($r);
				if ($r==0)
					$result = $rx;
				else
					$result_f = $rx;
				break;
		    	default:
				$lados = ExtornoModel::obtenerLados();
				$result = ExtornoTemplate::initialForm($lados);
				break;
		}
		if ($result != '')
			$this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != '')
			$this->visor->addComponent("ContentF", "content_footer", $result_f);
	}
}
