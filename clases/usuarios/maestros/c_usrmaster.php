<?php

class UserMasterController extends Controller
{
    function Init()
    {
	$this->visor = new Visor();
	isset($_REQUEST['action'])?$this->action=$_REQUEST['action']:$this->action='';
    }
    
    function Run()
    {
	include "maestros/m_usrmaster.php";
	include "maestros/m_grpmaster.php";
	include "maestros/t_usrmaster.php";
	$this->Init();
	$result = "";
	$result_f = "";
	
	$form_search = false;
	$listado = false;

	switch ($this->action) {
	    case "DoAgregar":
		$success = UserMasterModel::agregar($_REQUEST['ch_login'], $_REQUEST['ch_nombre'], $_REQUEST['ch_email'], $_REQUEST['ch_password1'], $_REQUEST['ch_password2']);
		$result = UserMasterTemplate::formAgregar(1, $success);
		break;
	    case "Agregar":
		$result = UserMasterTemplate::formAgregar();
		$result_f = " ";
		break;
	    case "DoModificar":
		$uid = $_REQUEST['uid'];
		$ch_nombre = $_REQUEST['ch_nombre'];
		$ch_email = $_REQUEST['ch_email'];
		$ch_password1 = $_REQUEST['ch_password1'];
		$ch_password2 = $_REQUEST['ch_password2'];
		$pospassword = $_REQUEST['pospassword'];
		$success = UserMasterModel::updateUsuario($uid, $ch_nombre, $ch_email, $ch_password1, $ch_password2,$pospassword);
		$usuario = UserMasterModel::obtieneUsuario($uid);
		$result = UserMasterTemplate::formModificar($usuario, $uid, 1, $success);
		break;
	    case "BorrarGrupo":
		$success = UserMasterModel::borrarGrupoUsuario($_REQUEST['uid'], $_REQUEST['gids']);
	    case "Modificar":
		$usuario = UserMasterModel::obtieneUsuario($_REQUEST['uid']);
		$grupos = UserMasterModel::obtenerGruposPorUsuario($_REQUEST['uid']);
		$result = UserMasterTemplate::formModificar($usuario, $_REQUEST['uid']);
		$result_f = UserMasterTemplate::listadoGrupos($_REQUEST['uid'], $grupos);
		break;
	    case "Delete":
		$success = UserMasterModel::borrarUsuario($_REQUEST['uids']);
		$usuarios = UserMasterModel::obtenerusuarios(0,0);
		$result_f = UserMasterTemplate::listado($usuarios, 1, $success);
		break;
	    case "Buscar":
		break;
	    case "GrupoAgregar":
		$result_f = UserMasterTemplate::formGrupoAgregar($_REQUEST['uid']);
		break;
	    case "DoGrupoAgregar":
		$success = UserMasterModel::agregarGrupoUsuario($_REQUEST['uid'], $_REQUEST['gid']);
		break;
	    default:
		$form_search = true;
		$listado = true;
		break;
	}
	
	if ($form_search) {
	    $result = UserMasterTemplate::formSearch();
	}

	if ($listado) {
	    $usuarios = UserMasterModel::obtenerUsuarios(0, 0);
	    $result_f = UserMasterTemplate::listado($usuarios);
	}

	$this->visor->addComponent("ContentT", "content_title", UserMasterTemplate::Titulo());
	if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
	if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);
    }
}

