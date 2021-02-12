<?php

class TicketsPosController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action'])?$this->action=$_REQUEST['action']:$this->action='';
	}

	function Run() {
		ob_start();
		include 'facturacion/m_ticketspos.php';
		include 'facturacion/t_ticketspos.php';
		include('../include/paginador_new.php');

		$this->Init();
		$result = "";
		$result_f = ""; 
		$result_f2  = "";
		$form_search = false;
		$listado = false;

		$ip = "";
		if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"),"unknown"))
			$ip = getenv("HTTP_CLIENT_IP");
		else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
			$ip = getenv("HTTP_X_FORWARDED_FOR");
		else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
			$ip = getenv("REMOTE_ADDR");
		else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
			$ip = $_SERVER['REMOTE_ADDR'];
		
		switch($this->action) {
		
            		case "Buscar":
				$listado = true;				
                		break;

			case "Acumulada":                		
				$file = "/tmp/imprimir/acumula_turno.txt";
				$fh = fopen($file, "w");
				fwrite($fh,"");
				fclose($fh); 
				$resu   = TicketsPosModel::acumuladoTurno($_REQUEST['ch_almacen'], $_REQUEST['ch_tipo_consulta'], $_REQUEST['ch_turno'], $_REQUEST['ch_periodo'], $_REQUEST['ch_mes'], $_REQUEST['ch_dia_desde'], $_REQUEST['ch_dia_hasta'], $_REQUEST['ch_caja'], $_REQUEST['ch_tipo']);
				$result_f  = TicketsPosTemplate::imprimir($resu);
				$file = "/tmp/imprimir/acumula_turno.txt";
				$cmd = TicketsPosModel::obtenerComandoImprimir($file);
				exec($cmd);
				?><script>alert('Imprimiendo la venta acumulada por turno');</script><?php
                		break;

			case "AcumuladaExcel":                		
				$resu   = TicketsPosModel::acumuladoTurno($_REQUEST['ch_almacen'], $_REQUEST['ch_tipo_consulta'], $_REQUEST['ch_turno'], $_REQUEST['ch_periodo'], $_REQUEST['ch_mes'], $_REQUEST['ch_dia_desde'], $_REQUEST['ch_dia_hasta'], $_REQUEST['ch_caja'], $_REQUEST['ch_tipo']);
				$result_f  = TicketsPosTemplate::repExcel($resu);
                		break;
				
	    		case "Bonus":
				$resultados = TicketsPosModel::reporte_bonus($ip,$_REQUEST['ch_tipo_consulta'], $_REQUEST['tm'], $_REQUEST['td'], $_REQUEST['Bonus'], $_REQUEST['ch_almacen'], $_REQUEST['ch_lado'], $_REQUEST['ch_caja'], $_REQUEST['ch_turno'], $_REQUEST['ch_periodo'], $_REQUEST['ch_mes'], $_REQUEST['ch_dia_desde'], $_REQUEST['ch_dia_hasta'], $_REQUEST['art_codigo'], $_REQUEST['ruc'], $_REQUEST['cuenta'], $_REQUEST['tarjeta'], $_REQUEST['ch_tipo']);
				ob_end_clean();
				$buff = "";
				for ($i = 0; $i < count($resultados); $i++) {
					$A = $resultados[$i];
					$buff .= "{$A['codigo']}".chr(13).chr(10);
				}
				header("Content-type: text/csv");
				header("Content-Disposition: attachment; filename=\"reporte_bonus.txt\"");
				header("Cache-Control: no-cache, must-revalidate");
				header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
				die($buff);	
				break;

            		case "Excel":
				$resultados = TicketsPosModel::busqueda($_REQUEST['ch_tipo_consulta'], $_REQUEST['tm'], $_REQUEST['td'], $_REQUEST['Bonus'], $_REQUEST['ch_almacen'], $_REQUEST['ch_lado'], $_REQUEST['ch_caja'], $_REQUEST['ch_turno'], $_REQUEST['ch_periodo'], $_REQUEST['ch_mes'], $_REQUEST['ch_dia_desde'], $_REQUEST['ch_dia_hasta'], $_REQUEST['art_codigo'], $_REQUEST['ruc'], $_REQUEST['cuenta'], $_REQUEST['tarjeta'], $_REQUEST['ch_tipo']);				
				ob_end_clean();
				$buff = "TM,TD,TRAN,Fecha,Turno,Descripcion,Cantidad,Precio,IGV,Importe,Tarjeta,Odometro,Placa,Cod. Cli.,Usuario,Caja,Lado,Bonus,RUC,Razon Social\n";//".base_convert((double)$A['cantidad'],10,10)."
				for ($i = 0; $i < count($resultados); $i++) {
					$A = $resultados[$i];
					$buff .= "{$A['tm']}, {$A['td']}, {$A['trans']}, {$A['fecha']}, {$A['turno']}, {$A['art_descripcion']}, {$A['cantidad']}, {$A['precio']} , {$A['igv']}, {$A['importe']}, {$A['tarjeta']}, {$A['odometro']}, {$A['placa']}, {$A['codcli']}, {$A['usr']}, {$A['caja']}, {$A['pump']}, {$A['bonus']}, {$A['ruc']}, {$A['razsocial']} \n";
				}
				header("Content-type: text/csv");
				header("Content-Disposition: attachment; filename=\"tickets.csv\""); 
				header("Cache-Control: no-cache, must-revalidate");
				header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
				die($buff);
				break;

            		default:
                		$form_search = true;
                		break;
        	}

		if ($form_search) {
			$result = TicketsPosTemplate::formSearch();		
		}

		if ($listado) {	
			$listado   = TicketsPosModel::tmListado($_REQUEST['rxp'],$_REQUEST['pagina'],$_REQUEST['ch_tipo_consulta'], $_REQUEST['tm'], $_REQUEST['td'], $_REQUEST['Bonus'], $_REQUEST['ch_almacen'], $_REQUEST['ch_lado'], $_REQUEST['ch_caja'], $_REQUEST['ch_turno'], $_REQUEST['ch_periodo'], $_REQUEST['ch_mes'], $_REQUEST['ch_dia_desde'], $_REQUEST['ch_dia_hasta'], $_REQUEST['art_codigo'], $_REQUEST['ruc'], $_REQUEST['cuenta'], $_REQUEST['tarjeta'], $_REQUEST['ch_tipo'], $_REQUEST['fpago']);
			$vec = array($_REQUEST['ch_tipo_consulta'], $_REQUEST['tm'][0], $_REQUEST['tm'][1], $_REQUEST['tm'][2], $_REQUEST['td'][0], $_REQUEST['td'][1], $_REQUEST['td'][2], $_REQUEST['Bonus'], $_REQUEST['ch_almacen'], $_REQUEST['ch_lado'], $_REQUEST['ch_caja'], $_REQUEST['ch_turno'], $_REQUEST['ch_periodo'], $_REQUEST['ch_mes'], $_REQUEST['ch_dia_desde'], $_REQUEST['ch_dia_hasta'], $_REQUEST['art_codigo'], $_REQUEST['ruc'], $_REQUEST['cuenta'], $_REQUEST['tarjeta'], $_REQUEST['ch_tipo'], $_REQUEST['fpago'][0], $_REQUEST['fpago'][1]);		
			$result_f2  = TicketsPosTemplate::formPag($listado['paginacion'], $vec);
			$result_f  = TicketsPosTemplate::listado($listado['datos'], $_REQUEST['ch_tipo_consulta']);
        }
        	
		$this->visor->addComponent("ContentT", "content_title", TicketsPosTemplate::titulo());
		if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f2.$result_f);
	}
}
