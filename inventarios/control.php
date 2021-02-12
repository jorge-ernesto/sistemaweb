<?php
  /*
    Sistema de Contabilidad sistemaweb
    Principal
    @TBCA
  */

  include('start.php');

  $Controlador='';

	switch (substr($rqst, 0 ,strcspn($rqst,'.'))){

		case 'FORMS':
			//Entrada formularios
			include('forms/c_forms.php');
			$Controlador = new FormsController(substr($rqst, strcspn($rqst,'.')+1));
		break;

		case 'HELPER':
			include('helper/c_helper.php');
			$Controlador = new HelperController(substr($rqst, strcspn($rqst,'.')+1));
		break;

		case 'ARCHIVOS':
			include('archivos/c_archivos.php');
			$Controlador = new ArchivosController(substr($rqst, strcspn($rqst,'.')+1));
		break;

		case 'REPORTES':
			include('reportes/c_reportes.php');
			$Controlador = new ReportesController(substr($rqst, strcspn($rqst,'.')+1));
		break;

		default:
		    //Entrada de defecto o inicial
		    include('main/c_main.php');
		    $Controlador = new MainController(substr($rqst, strcspn($rqst,'.')+1));
		    break;
			//die("Controlador no conocido $rqst");
	}

  $Controlador->Run();
  echo $Controlador->outputVisor();

