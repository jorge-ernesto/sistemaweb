<?php

class SerieDocumentoController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action']) ? $this->action = $_REQUEST['action']:$this->action='';
	}

	function Run() {
		include 'maestros/m_serie_documento.php';
		include 'maestros/t_serie_documento.php';
		include('../include/paginador_new.php');

		$this->Init();

		$result 	= '';
		$result_f 	= '';
		$buscar 	= false;

	      	if(!isset($_REQUEST['rxp'],$_REQUEST['pagina'])) {
			$_REQUEST['rxp'] 	= 30;
		 	$_REQUEST['pagina'] 	= 1;
	      	}

		switch($this->action) {

			case "Buscar":

				$busqueda    	= SerieDocumentoModel::Paginacion($_REQUEST['almacen']);
				$result		= SerieDocumentoTemplate::formSearch($_REQUEST['almacen']);
				$result_f 	= SerieDocumentoTemplate::resultadosBusqueda($busqueda, $_REQUEST['almacen']);

			break;

			case "Agregar":

				$result 	= SerieDocumentoTemplate::formAgregar($_REQUEST['fecha'],$_REQUEST['fecha2']);
				$result_f 	= "&nbsp;";
				break;

			case "Modificar":

				$resultado	= SerieDocumentoModel::recuperarRegistroArray($_REQUEST['tipo'], $_REQUEST['serie']);
				$result		= SerieDocumentoTemplate::formAgregar($resultado);
				$result_f	= "&nbsp;";
				break;

			case "Guardar":

				$almacen	= $_REQUEST['almacen'];
				$tipo 		= $_REQUEST['tipo'];
				$serie 		= $_REQUEST['serie'];
				$numero 	= $_REQUEST['numero'];

				/*
				if (strlen($serie) > 3 || !is_numeric(substr($serie,0,1))){ // Condición de no válido
					?><script>alert('Agregar series con 3 dígitos para doc. manuales');</script><?php
				} else {
				*/
					$res = SerieDocumentoModel::agregar($almacen, $tipo, $serie, $numero);
					if($res == 1){
						?><script>alert('Registro guardado correctamente');</script><?php
						$busqueda   = SerieDocumentoModel::Paginacion($almacen);
						$result     = SerieDocumentoTemplate::formSearch($almacen);
						$result_f 	= SerieDocumentoTemplate::resultadosBusqueda($busqueda, $_REQUEST['almacen']);
						$buscar		= true;
					}else if ($res == false)
						$result_f = "<center><p style='color:red;'><<< Error al insertar >>></center>";
					else
						$result_f = "<center><p style='color:red;'>Ya existe Documento: <br> Tipo: $tipo - Serie: $serie</center>";
				//}

				break;

			case "Actualizar":

				$var = SerieDocumentoModel::actualizar($_REQUEST['tipo'],$_REQUEST['serie'],$_REQUEST['numero']);

					if ($var){

						?><script>alert('Registro actualizado correctamente')</script><?php
						$busqueda    	= SerieDocumentoModel::Paginacion($_REQUEST['almacen']);
						$result     	= SerieDocumentoTemplate::formSearch($_REQUEST['almacen']);
						$result_f 	= SerieDocumentoTemplate::resultadosBusqueda($busqueda, $_REQUEST['almacen']);
						$buscar		= true;

					}

			break;

			case "Eliminar":

				$var = SerieDocumentoModel::Eliminar($_REQUEST['tipo'], $_REQUEST['serie']);

					$msg = $_REQUEST['nombre']." tiene movimientos. No se puede eliminar";

					if ($var){

						?><script>alert('Registro Eliminado correctamente')</script><?php
						$busqueda    	= SerieDocumentoModel::Paginacion($_REQUEST['almacen']);
						$result     	= SerieDocumentoTemplate::formSearch($_REQUEST['almacen']);
						$result_f 	= SerieDocumentoTemplate::resultadosBusqueda($busqueda, $_REQUEST['almacen']);
						$buscar		= true;

					}else{
						echo "<script>alert('{$msg}');</script>\n";
					}

			break;

			default:

				$busqueda    	= SerieDocumentoModel::Paginacion('');
				$result     	= SerieDocumentoTemplate::formSearch('');
				$result_f 	= SerieDocumentoTemplate::resultadosBusqueda($busqueda, $_REQUEST['almacen']);
				$buscar 	= true;

			break;

		}

		$this->visor->addComponent("ContentT", "content_title", SerieDocumentoTemplate::titulo());

		if ($result != '')
			$this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != '')
			$this->visor->addComponent("ContentF", "content_footer", $result_f);

	}
}
