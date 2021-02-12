<?php
class ActDepositosPosController extends Controller{

	function Init(){
	        $this->visor = new Visor();
        	isset($_REQUEST['action'])?$this->action=$_REQUEST['action']:$this->action='';
	}

	function Run(){

        include 'movimientos/m_act_depositos_pos.php';
        include 'movimientos/t_act_depositos_pos.php';  
        include 'movimientos/m_consolidacion.php';

        $this->Init();
	
		$ip = "";
		$result = "";
		$result_f = "";
		$form_search = false;
		$listado = false;
		$editar = false;
		$actualizar = false;

		/* Get IP and USER */
		$ip = "";

		if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"),"unknown"))
			$ip = getenv("HTTP_CLIENT_IP");
		else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
			$ip = getenv("HTTP_X_FORWARDED_FOR");
		else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
			$ip = getenv("REMOTE_ADDR");
		else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
			$ip = $_SERVER['REMOTE_ADDR'];

		$usuario = $_SESSION['auth_usuario'];

		switch($this->action){

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
			$turno		= "";

			$resultados	= ActDepositosPosModel::busqueda($almacen, $fecha, $turno);
			$result		= ActDepositosPosTemplate::formSearch($fecha);

			$consolidado = 'Fecha: '.$fecha;

			if ($resultados==="CONSOLIDADO")
				$result_f = "<center><blink style='color: red'><<< Consolidado $consolidado >>></blink></center>";
			else if ($resultados == FALSE)
				$result_f = "<center><blink style='color: red'><<< No hay Datos >>></blink></center>";
			else
				$result_f = ActDepositosPosTemplate::listado($resultados);

		}

		if ($listado) {

			$fecha = $_REQUEST['ch_fecha'];
			$turno = $_REQUEST['ch_turno'];

			$resultados = ActDepositosPosModel::busqueda($_REQUEST['ch_almacen'], $fecha, $turno);

			if($turno == 'TODOS')
				$consolidado = 'Fecha: '.$fecha;
			else
				$consolidado = 'Fecha: '.$fecha.' Turno: '.$turno;

			if ($resultados==="CONSOLIDADO")
				$result_f = "<center><blink style='color: red'><<< Consolidado $consolidado >>></blink></center>";
			else if ($resultados == FALSE)
				$result_f = "<center><blink style='color: red'><<< No hay Datos >>></blink></center>";
			else
				$result_f = ActDepositosPosTemplate::listado($resultados);
		}

		if ($editar) {
			$fila =  ActDepositosPosModel::obtenerFila($_REQUEST['ch_almacen'],$_REQUEST['dt_dia'],$_REQUEST['ch_posturno'],$_REQUEST['ch_codigo_trabajador'],$_REQUEST['ch_numero_documento'],$_REQUEST['ch_numero_correl']);
			$trab = ActDepositosPosModel::obtenerTrabajadores();
			$result_f = ActDepositosPosTemplate::formEdit($fila[''],$trab,$_SESSION['auth_usuario'],$ip);
		}

		if ($actualizar) {
			error_log( json_encode( array( $_REQUEST['ch_almacen'],$_REQUEST['dt_dia'],$_REQUEST['ch_posturno'],$_REQUEST['ch_codigo_trabajador'],$_REQUEST['ch_numero_documento'], $_REQUEST['nvalida'], $_REQUEST['ndia'], $_REQUEST['nturno'], $_REQUEST['ncodtrab'], $usuario, $ip,$_REQUEST['ch_numero_correl'] ) ) );
			$response =  ActDepositosPosModel::actualizarFila($_REQUEST['ch_almacen'],$_REQUEST['dt_dia'],$_REQUEST['ch_posturno'],$_REQUEST['ch_codigo_trabajador'],$_REQUEST['ch_numero_documento'], $_REQUEST['nvalida'], $_REQUEST['ndia'], $_REQUEST['nturno'], $_REQUEST['ncodtrab'], $usuario, $ip,$_REQUEST['ch_numero_correl']);
			if ($response)
				$result_f = "<center><blink style='color: blue'>Actualizado correctamente</blink></center>";
			else
				$result_f = "<center><blink style='color: red'>Error al actualizar datos</blink></center>";
		}

		$this->visor->addComponent("ContentT", "content_title", ActDepositosPosTemplate::titulo());
		if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);

	}

}
