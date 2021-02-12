<?php
require_once("/sistemaweb/lib/usuarios.inc.php");
class SelfPwdController extends Controller {
	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action'])?$this->action=$_REQUEST['action']:$this->action='';
	}

	function Run() {
		require("permisos/m_selfpwd.php");
		require("permisos/t_selfpwd.php");

		$result = "";
		$result_f = "";

		$this->Init();

		switch ($this->action) {
			case "Cambiar":
				$res = SelfPwdModel::changeSelfPassword($_REQUEST['pwd1'],$_REQUEST['pwd2']);
				SelfPwdTemplate::showSelfPwdChangeResult($res,$result,$result_f);
				break;
			default:
				$result = SelfPwdTemplate::showSelfPwdChangeForm();
				$result_f = " ";
				break;
		}

		$this->visor->addComponent("ContentT", "content_title",SelfPwdTemplate::titulo());
		if ($result != "") $this->visor->addComponent("ContentB","content_body", $result);
		if ($result_f != "") $this->visor->addComponent("ContentF","content_footer", $result_f);	
	}
}
