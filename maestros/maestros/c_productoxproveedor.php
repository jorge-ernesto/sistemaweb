<?php
class ProductoxProveedorController extends Controller
{
	function Init()
	{
		$this->visor = new Visor();
		isset($_REQUEST['action'])?$this->action=$_REQUEST['action']:$this->action='';
		$this->datos = isset($_REQUEST["datos"])?$_REQUEST["datos"]:'';
	}
	
	function Run()
	{
		include "maestros/m_productoxproveedor.php";
		include "maestros/t_productoxproveedor.php";
	
		$this->Init();
	
		$result = "";
		$result_f = "";
	
		switch($this->action)
		{
			case "Agregar":
				$result = ProductoxProveedorTemplate::formProdxProv('','','','','', true);
				$result_f = ' ';
				break;
			case "Modificar":
				$registro = ProductoxProveedorModel::obtenerFila($_REQUEST['ch_proveedor'], $_REQUEST['ch_producto']);
				$result = ProductoxProveedorTemplate::formProdxProv($registro['ch_proveedor'],$registro['ch_producto'],$registro['ch_moneda'],$registro['ch_costounitario'],$registro['ch_fechacreacion'], false);
				$result_f = ' ';
				break;
			case "Buscar":
				$resultados = ProductoxProveedorModel::busqueda($_REQUEST['ch_almacen'], $_REQUEST['ch_mes'], $_REQUEST['ch_anio'], $_REQUEST['ch_proveedor'], $_REQUEST['ch_producto']);
				$result_f = ProductoxProveedorTemplate::listado($resultados);
				break;
			case "Guardar":
				if ($_REQUEST['nuevo'] == '1') {
	      				$resultado = ProductoxProveedorModel::guardarFila(	strtoupper($_REQUEST['ch_proveedor']),
												strtoupper($_REQUEST['ch_producto']),
												strtoupper($_REQUEST['ch_moneda']),
												strtoupper($_REQUEST['ch_costounitario']),
												strtoupper($_REQUEST['ch_fechacreacion']));
				}
				else {
	      				$resultado = ProductoxProveedorModel::actualizarFila(	strtoupper($_REQUEST['ch_proveedor']),
												strtoupper($_REQUEST['ch_producto']),
												strtoupper($_REQUEST['ch_moneda']),
												strtoupper($_REQUEST['ch_costounitario']),
												strtoupper($_REQUEST['ch_fechacreacion']));
				}
				$result = ProductoxProveedorTemplate::resultadoGrabar($resultado);
				break;
			case "Eliminar":
				$res = ProductoxProveedorModel::eliminarFila($_REQUEST['ch_proveedor'], $_REQUEST['ch_producto']);
				$result = ProductoxProveedorTemplate::mostrarResultadoEliminacion($res);
				$result_f = ' ';
				break;
			default:
				$result = ProductoxProveedorTemplate::formBuscar();
				break;
		}

		$this->visor->addComponent("ContentT", "content_title", ProductoxProveedorTemplate::titulo());
		if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);
	}
}
