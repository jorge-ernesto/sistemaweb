<?php

class DifInvController extends Controller
{
    function Init()
    {
	$this->visor = new Visor();
	isset($_REQUEST['action'])?$this->action=$_REQUEST['action']:$this->action="";
    }
    
    function Run()
    {
	include "reportes/m_difinv.php";
	include "reportes/t_difinv.php";
	include "reportes/m_formproces.php";
	
	$this->Init();
	
	$result = "";
	$result_f = "";
	$form_search = false;
	$listado = false;

	switch ($this->action) {
	    case "Importar":
		$result = DifInvTemplate::formImportar();
		break;
	    case "DoImportar":
		$errors = DifInvModel::importarStocks($_REQUEST['estaciones'], $_REQUEST['fecha']);
		$result = DifInvTemplate::reporteImportacion($errors);
		break;
	    case "Buscar":
		$listado = true;
		break;
	    case "pdf":
		$resultados = DifInvModel::search($_REQUEST['periodo'], $_SESSION['dif_inv_estaciones']);
		DifInvTemplate::reportePDF($resultados, $_REQUEST['periodo']);
		exit;
	    default:
		$form_search = true;
		break;
	}


	if ($form_search) {
	    $result = DifInvTemplate::formSearch();
	}	
	
	if ($listado) {
	    $resultados = DifInvModel::search($_REQUEST['periodo'], $_REQUEST['estaciones']);
	    $_SESSION['dif_inv_estaciones'] = $_REQUEST['estaciones'];
	    $result_f = DifInvTemplate::listado($resultados, $_REQUEST['periodo']);
	}
	
	$this->visor->addComponent("ContentT", "content_title", DifInvTemplate::Titulo());
	if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
	if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);
    }
    
}

?>