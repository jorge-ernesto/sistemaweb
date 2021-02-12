<?php

class DescuentosController extends Controller
{
    function Init()
    {
	    $this->visor = new Visor();
	    isset($_REQUEST['action'])?$this->action=$_REQUEST['action']:$this->action='';
    }

    function Run()
    {
	    include 'maestros/m_descuentos.php';
	    include 'maestros/t_descuentos.php';

	    $this->Init();

	    $result = "";
	    $result_f = "";
	    $resultados = null;
	    $form_search = false;
	    $listado = false;

	    switch ($this->action) {
            case "Editar":
                $descuento = DescuentosModel::obtenerDescuento($_REQUEST['descuento']);
                $result_f = DescuentosTemplate::formEditar($descuento);
                break;
            case "Modificar":
                if (DescuentosModel::modificarDescuento($_REQUEST['ch_correlativo'], $_REQUEST['ch_almacen'], $_REQUEST['art_codigo'], $_REQUEST['dt_inicio'], $_REQUEST['hr_inicio'], $_REQUEST['dt_fin'], $_REQUEST['hr_fin'], $_REQUEST['nu_descuento'], $_REQUEST['ch_codigo_club']))
                    $listado = true;
                break;
            case "Borrar":
                DescuentosModel::borrar($_REQUEST['descuento']);
                $listado = true;
                break;
	        case "Anadir":
		        $result_f = DescuentosTemplate::formAgregar();
		        break;
            case "Agregar":
                if (DescuentosModel::agregarDescuento($_REQUEST['ch_almacen'], $_REQUEST['art_codigo'], $_REQUEST['dt_inicio'], $_REQUEST['hr_inicio'], $_REQUEST['dt_fin'], $_REQUEST['hr_fin'], $_REQUEST['nu_descuento'], $_REQUEST['ch_codigo_club']))
                    $listado = true;
                break;
	        case "Copiar":
                $resultados = DescuentosModel::obtenerDescuentos();
                $result_f = DescuentosTemplate::listadoCopiar($resultados);
		        break;
            case "Hacer copia":
                DescuentosModel::copiar($_REQUEST['descuentos']);
                $listado = true;
                break;
	        default:
		        $form_search = true;
		        $listado = true;
		        break;
	    }

	    if ($form_search) {
	        $result = DescuentosTemplate::formSearch();
	    }
	    if ($listado) {
	        if (!$resultados) $resultados = DescuentosModel::obtenerDescuentos();
	        $result_f = DescuentosTemplate::listado($resultados);
	    }

	    $this->visor->addComponent("ContentT", "content_title", DescuentosTemplate::titulo());
	    if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
	    if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);
    }

}

