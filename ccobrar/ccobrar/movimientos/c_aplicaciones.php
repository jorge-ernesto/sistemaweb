<?php

class AplicacionesController extends Controller {
	function Init() {
		$this->visor = new Visor();
		$this->task = @$_REQUEST["task"];
		$this->action = isset($_REQUEST["action"])?$_REQUEST["action"]:'';
		$this->datos = isset($_REQUEST["datos"])?$_REQUEST["datos"]:'';
	}

	function Run() {
		$this->Init();
		$result = '';
		include('movimientos/m_aplicaciones.php');
		include('movimientos/t_aplicaciones.php');

		if ($_REQUEST['action']=='Ingresar'){
			$_SESSION['fec_aplicacion']=$_REQUEST['fecha'];
		}

		if ($_REQUEST['action']=='Interfaz')
			$this->visor->addComponent('ContentT', 'content_title', '<div align="center" style="color:#336699"><h2>Fecha de Aplicacion de Facturas</h2></div>');
		else
			$this->visor->addComponent('ContentT', 'content_title', AplicacionesTemplate::titulo());

		$montosArray = array();
		$montosArray = substr($_REQUEST['montos'],1,-1);
		$montosArray = explode('}{',$montosArray);
	
		global $tipo;
		switch ($this->request) {
			case 'APLICACIONES':
			$listado = false;

			switch ($this->action) {

				case 'Aplicacion':	

				$DatosAbonos	= AplicacionesModel::tmListadoAbonos($_REQUEST['clicodigo'], $_REQUEST['tipo']);
				$DatosCargo		= AplicacionesModel::tmSeleccionaCargoAbono($_REQUEST['registroid']);
				$result			= AplicacionesTemplate::formAplicaciones($DatosCargo['datos'][0],$DatosAbonos,$_REQUEST['tipo']);

				$this->visor->addComponent("ContentB", "content_body", $result);
				break;

				$montosArray = array();

				case 'Aplicar':

				$numero			= substr($_REQUEST['calcular'][0],-11);
				$numdoc			= $numero = explode('}',$numero);
				$DatosCargo		= AplicacionesModel::tmListadoFinalCargos($_REQUEST['registroid']);
				$DatosAbonos	= AplicacionesModel::tmListadoAbonos($_REQUEST['clicodigo'],'20');

				if ($_REQUEST['chkpormonto']=='S') {
					AplicacionesModel::AplicarporMonto($DatosCargo['datos'][0]['cli_codigo'], $_REQUEST['ch_tipdocumento'],$_REQUEST['ch_numdocumento'],$_REQUEST['monto']);
					$_REQUEST['TotalSaldoAbono']=$_REQUEST['monto'];
				} else {
					foreach($_REQUEST['calcular'] as $llave => $valor) {

						$montosArray = substr($valor,1,-1);
						$montosArray = explode('}{',$montosArray);

						if ($_REQUEST['chkpormontonota']=='S'){
							AplicacionesModel::ActualizarCargos($DatosCargo['datos'][0]['cli_codigo'], $_REQUEST['ch_tipdocumento'],$_REQUEST['ch_numdocumento'],$montosArray[1],$montosArray[2],$_REQUEST['monto']);
							AplicacionesModel::ActualizarCargosNC($DatosCargo['datos'][0]['cli_codigo'], $numdoc[0], $_REQUEST['monto']);
						} else {
							AplicacionesModel::ActualizarCargos($DatosCargo['datos'][0]['cli_codigo'], $_REQUEST['ch_tipdocumento'],$_REQUEST['ch_numdocumento'],$montosArray[1],$montosArray[2],$montosArray[0], $DatosCargo);
							AplicacionesModel::ActualizarCargosNC($DatosCargo['datos'][0]['cli_codigo'], $numdoc[0], $_REQUEST['monto']);
						}
					}
				}

				$listado = false;
				if ($_REQUEST['chkpormontonota']=='S') 
					$_REQUEST['TotalSaldoAbono']=$_REQUEST['monto'];
				$DatosAbonos	= AplicacionesModel::tmListadoAbonos($_REQUEST['registroid'], $_REQUEST['tipo']);
				$DatosCargo		= AplicacionesModel::tmSeleccionaCargoAbono($_REQUEST['registroid']);
				$result			= AplicacionesTemplate::formAplicaciones($DatosCargo['datos'][0],$DatosAbonos);
				$this->visor->addComponent("ContentB", "content_body", $result);
				break;

				case 'setRegistroCli':
				$result = AplicacionesTemplate::setRegistrosCliente($_REQUEST["codigocli"]);
				$this->visor->addComponent("desc_cliente", "desc_cliente", $result);
				break;

				case 'Buscar':
				$busqueda = AplicacionesModel::tmListadoCargos(@$_REQUEST["busqueda"]);
				$result = AplicacionesTemplate::listadoCargos($busqueda['datos']);
				$this->visor->addComponent("ListadoB", "resultados_grid", $result);
				break;

				case 'Interfaz':
				$result = AplicacionesTemplate::formInterfaz();
				$this->visor->addComponent("ContentB", "content_body", $result);
				break;

				case 'Ingresar':
				$listado=true;
				break;

				default:
				$listado = true;
				break;
			}
			if ($listado) {
				if ($_REQUEST['busqueda'])
					$listado    = AplicacionesModel::tmListadoCargos($_REQUEST['busqueda'],@$_REQUEST['rxp'],@$_REQUEST['pagina']);			
				$result      =  AplicacionesTemplate::formBuscar($listado['paginacion']);
				$result     .= AplicacionesTemplate::listadoCargos($listado['datos']);
				$this->visor->addComponent("ContentB", "content_body", $result);
			}
			break;

			case 'APLICACIONESDET':
			//Si hay detalles
			$montos = array();
			$montos['SALDO ABONO']  = $montosArray[0];
			$montos['OPERACION']    = $_REQUEST['operacion'];
			if(!empty($_REQUEST['total_saldo_abono']) && $_REQUEST['operacion']=='sumar') {
				$montos['TOTAL SALDO ABONO'] = $_REQUEST['total_saldo_abono']+$montos['SALDO ABONO'];
			} elseif(!empty($_REQUEST['total_saldo_abono']) && $_REQUEST['operacion']=='restar') {
				$montos['TOTAL SALDO ABONO'] = $_REQUEST['total_saldo_abono']-$montos['SALDO ABONO'];
			} else {
				$montos['TOTAL SALDO ABONO']  = $montos['SALDO ABONO'];
			}
			$montos['TOTAL IMPORTE SALDO'] = $_REQUEST['total_import_saldo'];
			$TotalMontos = AplicacionesTemplate::verTotales($montos);
			$this->visor->addComponent("Totales","Totales",$TotalMontos);
			break;

			case 'APLICACIONESOTROS':
			$montos = array();
			$montos['SALDO ABONO']  = $_REQUEST['montos'];
			$montos['OPERACION']    = $_REQUEST['operacion'];
			if(!empty($_REQUEST['total_saldo_abono']) && $_REQUEST['operacion']=='sumar') {
				$montos['TOTAL SALDO ABONO'] = $_REQUEST['total_saldo_abono']+$montos['SALDO ABONO'];
			} elseif(!empty($_REQUEST['total_saldo_abono']) && $_REQUEST['operacion']=='restar') {
				$montos['TOTAL SALDO ABONO'] = $_REQUEST['total_saldo_abono']-$montos['SALDO ABONO'];
			} else {
				$montos['TOTAL SALDO ABONO']  = $montos['SALDO ABONO'];
			}
			$montos['TOTAL IMPORTE SALDO'] = $_REQUEST['total_import_saldo'];
			$TotalMontos = AplicacionesTemplate::verTotales($montos);
			$this->visor->addComponent("Totales","Totales",$TotalMontos);
			break;

			default:
			$this->visor->addComponent('ContentB', 'content_body', '<h2>TAREA "'.$this->request.'" NO CONOCIDA EN REGISTROS</h2>');
			break;
		}
	}
}
