<?php
class GroupMasterController extends Controller {
    function Init() {
	$this->visor = new Visor();
	isset($_REQUEST['action'])?$this->action=$_REQUEST['action']:$this->action='';
    }
    
    function Run() {
	include "maestros/m_grpmaster.php";
	include "maestros/t_grpmaster.php";
	
	$this->Init();
	
	$result = "";
	$result_f = "";
	
	$form_search = false;
	$listado = false;

		switch ($this->action) {
		    case "DoAgregar":
				$success = GroupMasterModel::agregar($_REQUEST['ch_grupo'], $_REQUEST['ch_nombre']);
				$result = GroupMasterTemplate::formAgregar(1, $success);
				break;
		    case "Agregar":
				$result = GroupMasterTemplate::formAgregar();
				$result_f = " ";
				break;
		    case "DoModificar":
				$gid = $_REQUEST['gid'];
				$ch_grupo = $_REQUEST['ch_grupo'];
				$ch_nombre = $_REQUEST['ch_nombre'];
				$success = GroupMasterModel::updateGrupo($gid, $ch_grupo, $ch_nombre);
				$usuario = GroupMasterModel::obtieneGrupo($gid);
				$result = GroupMasterTemplate::formModificar($usuario, $gid, 1, $success);
				break;
		    case "Modificar":
				$grupo = GroupMasterModel::obtieneGrupo($_REQUEST['gid']);
				$result = GroupMasterTemplate::formModificar($grupo, $_REQUEST['gid']);
				break;
		    case "Delete":
				$success = GroupMasterModel::borrarGrupos($_REQUEST['gids']);
				$grupos = GroupMasterModel::obtenergrupos(0,0);
				$result_f = GroupMasterTemplate::listado($grupos, 1, $success);
				break;
		    case "Buscar":
				break;
		    default:
				$form_search = true;
				$listado = true;
				break;
		}
	
	if ($form_search) {
	    $result = GroupMasterTemplate::formSearch();
	}

	if ($listado) {
	    $grupos = GroupMasterModel::obtenerGrupos(0, 0);
	    $result_f = GroupMasterTemplate::listado($grupos);
	}

	$this->visor->addComponent("ContentT", "content_title", GroupMasterTemplate::Titulo());
	if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
	if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);
    }
}
