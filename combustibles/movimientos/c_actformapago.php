<?php

date_default_timezone_set('UTC');

class ActFormaPagoController extends Controller {

	function Init() {
	        $this->visor = new Visor();
	        isset($_REQUEST['action'])?$this->action=$_REQUEST['action']:$this->action='';
	}

	function Run() {
		include 'movimientos/m_actformapago.php';
		include 'movimientos/t_actformapago.php';

		$this->Init();
		$result 	= "";
		$result_f 	= "";
		$form_search 	= false;
		$listado 	= false;
		$editar 	= false;
		$actualizar 	= false;

		switch($this->action) {
			case "Buscar":
		        	$listado = true;
		        	break;
		    	case "edit":
				$editar = true;
				break;
		    	case "update":
				$actualizar = true;
				break;
		    	default:
		        	$form_search = true;
		        	break;
		}

		if ($form_search) {
			$almacen	= "";
			$fecha		= date('d/m/Y');
			$turno 		= '';

			$resultados	= ActFormaPagoModel::busqueda($almacen, $fecha, '', 'TODAS', 'TODAS');
		    $result		= ActFormaPagoTemplate::formSearch($fecha);

			$consolidado = 'Fecha: '.$fecha;

		    	if ($resultados==="CONSOLIDADO")
				$result_f = "<center><blink style='color: red'><<< Consolidado $consolidado >>></blink></center>";
			else if ($resultados == FALSE)
				$result_f = "<center><blink style='color: red'><<<  No hay Datos >>></blink></center>";
		    	else
			    	$result_f 	= ActFormaPagoTemplate::listado($resultados);

		}

		if ($listado) {

			$fecha = $_REQUEST['ch_fecha'];
			$turno = $_REQUEST['ch_turno'];

		    	$resultados = ActFormaPagoModel::busqueda($_REQUEST['ch_almacen'], $fecha, $turno, $_REQUEST['ch_caja'], $_REQUEST['ch_lado']);

			$consolidado = 'Fecha: '.$fecha.' Turno: '.$turno;

		    	if ($resultados==="CONSOLIDADO")
				$result_f = "<center><blink style='color: red'><<< Consolidado $consolidado >>></blink></center>";
			else if ($resultados == FALSE)
				$result_f = "<center><blink style='color: red'><<<  No hay Datos >>></blink></center>";
		    	else
				$result_f = ActFormaPagoTemplate::listado($resultados);

		}

		if ($actualizar) {
			$result = ((ActFormaPagoModel::actualizarFila($_REQUEST['oid'],$_REQUEST['caja'],$_REQUEST['tabla'],$_REQUEST['fpago'],$_REQUEST['tarjeta'],$_REQUEST['ntarjeta'],$_SESSION['auth_usuario'],$_REQUEST['td'],$_REQUEST['ntrabajador'], $_REQUEST['turno'])==false)?'<p styke="color:red;font-weight:bold;text-align:center;">Error al actualizar la fila</p>':'<p styke="font-weight:bold;text-align:center;">Fila actualizada</p>');
		}

		if ($editar) { 
			$fila		= ActFormaPagoModel::obtenerFila($_REQUEST['oid'],$_REQUEST['caja'],$_REQUEST['dia'],$_REQUEST['td'], $_REQUEST['turno']);
			$result_f	= ActFormaPagoTemplate::formEdit($fila);
		}

		$this->visor->addComponent("ContentT", "content_title", ActFormaPagoTemplate::titulo());
		if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);
	}
}
