<?php

class CancelacionController extends Controller {
	function Init() {
		$this->visor = new Visor();
		$this->task = @$_REQUEST["task"];
		$this->action=@$_REQUEST['action'];
	}

	function Run() {
		$this->Init();
		$result = '';
		include('m_obtcancelaciones.php');
		include('t_obtcancelaciones.php');		$this->visor->addComponent('ContentT', 'content_title', CancelacionTemplate::titulo());
		switch ($this->request) {
			case 'SINTAREA':
				switch ($this->action) {
				case 'Buscar':
					$contadorok = 0;
					$contadorerror = 0;
					$contadoreliminados = 0;
					$result = CancelacionTemplate::formBuscar();
					$result .= CancelacionTemplate::CabeceraListado();
					
							
				
					//$registros = CancelacionModel::ObtenerCancelaciones($_REQUEST['mes']);
					
					$registros = CancelacionModel::ObtenerCancelaciones($_REQUEST['datos']['fechaini'],$_REQUEST['datos']['fechafin']);
					
					//print_r($registros);
					//$connection = pg_connect("host=128.1.2.202 port=5432 dbname=acosaperu user=postgres password=postgres");
					foreach($registros as $clave=>$valor) {
						$estado = CancelacionModel::GrabarCancelaciones($valor);
						if ($estado == 1) {
							$result .= CancelacionTemplate::ListadoCancelaciones($valor,$estado);
							$contadorok++;
						} elseif ($estado == 0) {
							$result .= CancelacionTemplate::ListadoCancelaciones($valor,$estado);
							$contadorerror++;
						} elseif ($estado == 4){
							$result .= CancelacionTemplate::ListadoCancelaciones($valor,$estado);
							$contadoreliminados++;
						}
					}
					$result .= CancelacionTemplate::FinListadoCancelaciones($contadorok,$contadorerror,$contadoreliminados);
					$this->visor->addComponent("ContentB", "content_body", $result);
					break;
			}
			break;
			default:
				$result = CancelacionTemplate::formBuscar();
				$this->visor->addComponent("ContentB", "content_body", $result);
			break;
		}
	}
}

?>