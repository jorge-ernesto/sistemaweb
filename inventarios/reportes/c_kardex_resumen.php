<?php

class KardexActController extends Controller {

	function Init() {
        	$this->visor = new Visor();
        	isset($_REQUEST['action']) ? $this->action = $_REQUEST['action'] : $this->action = "";
	}

	function Run() {

	        include 'reportes/m_kardex_resumen.php';	
	        include 'reportes/t_kardex_resumen.php';

        	$this->Init();

        	$result		= "";
        	$result_f	= "";
        	$form_search	= false;
        	$reporte	= false;
	
		switch ($this->action) {

			case "Buscar":
                		$reporte = true;
                	break;

			default:
				$form_search = true;

		}

		if ($form_search) {
			$result = KardexActTemplate::formSearch();
		}

		if ($reporte) {

			$accion = $_REQUEST['accion'];

			$ventas_inventario	= KardexActModel::ventas_inventario($_REQUEST['ano'], $_REQUEST['mes'], $_REQUEST['estacion']);
		    	$listado_productos	= KardexActModel::lista_productos($_REQUEST['ano'], $_REQUEST['mes'], $_REQUEST['estacion']);
		    	$saldos_inicial		= KardexActModel::saldo_inicial_mes_anterior($_REQUEST['ano'], $_REQUEST['mes'], $_REQUEST['estacion']);
		    	$saldos_final		= KardexActModel::saldo_inicial_mes_actual($_REQUEST['ano'], $_REQUEST['mes'], $_REQUEST['estacion']);
		    	$mermas			= KardexActModel::ingreso_ajuste_inventario($_REQUEST['ano'], $_REQUEST['mes'], $_REQUEST['estacion']);
			$linea 			= KardexActModel::getdescripcion_linea();

			if ($accion == "Normal" || $accion == "Agrupado") {
		        	$ingreso_inventario = KardexActModel::ingreso_inventario($_REQUEST['ano'], $_REQUEST['mes'], $_REQUEST['estacion']);
		        	$result_f = KardexActTemplate::listado($accion,$listado_productos, $saldos_inicial, $ingreso_inventario, $ventas_inventario, $saldos_final, $linea,$mermas);
		    	} else {
		        	$ingreso_inventario = KardexActModel::ingreso_inventario_detallado($_REQUEST['ano'], $_REQUEST['mes'], $_REQUEST['estacion']);
		        	$result_f = KardexActTemplate::listado_detallado($listado_productos, $saldos_inicial, $ingreso_inventario, $ventas_inventario, $saldos_final, $linea);
		    	}

		}

		$this->visor->addComponent("ContentT", "content_title", KardexActTemplate::Titulo());

		if ($result != "")
		    $this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != "")
		    $this->visor->addComponent("ContentF", "content_footer", $result_f);

    }

}

