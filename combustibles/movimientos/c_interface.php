<?php

class InterfaceController extends Controller {
	function Init(){
		$this->visor = new Visor();
		isset($_REQUEST['action'])?$this->action = $_REQUEST['action']:$this->action="";
	}

	function Run() {
		$this->Init();
		$result = '';

		include('movimientos/m_interface.php');
		include('movimientos/t_interface.php');
		include('/sistemaweb/include/m_sisvarios.php');

		switch ($this->action) {
			case 'Procesar':

			$res = InterfaceModel::interface_fn_opensoft_copetrol($_REQUEST['desde'],$_REQUEST['hasta'],$_REQUEST['sucursal']);

			if (file_exists("/tmp/$res")){
				unlink("/tmp/$res");
			}

			$cmd = "zip -j -m /tmp/$res /home/data/*";							
							
			exec($cmd);

			header("Content-Type: application/x-zip-compressed");
			header('Content-Disposition: attachment; filename="' . $res . '"');
			readfile("/tmp/$res");

			break;

		}

	}
}
