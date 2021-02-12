<?php
class CierreTurnoController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action'])?$this->action=$_REQUEST['action']:$this->action='';
	}

	function Run() {

		include 'maestros/m_cierre_turno.php';
		include 'maestros/t_cierre_turno.php';

		$this->Init();
		$result = "";
		$result_f = "";
		$buscar = false;
		$listado = false;
		
		switch($this->action) {

			case "Buscar":

				$listado = true;
				break;

			case "Agregar":

				$campo  = trim($_REQUEST['campo']);
				$result	= CierreTurnoTemplate::formAgregar($_REQUEST['fecha'], $_REQUEST['fecha2'],$campo);
				$result_f = "&nbsp;";
				break;

			case "Guardar":

					/*$horita1 = $_REQUEST['hora_inicial'];

					$hora1 = substr(date("Y-m-d ".$horita1),11,8);
				
					$horita2 = $_REQUEST['hora_final'];

					$hora2 = substr(date("Y-m-d ".$horita2),11,8);

					if ($hora2 >= "01:00:00" and $hora2 <= "23:59:59"){
						$horita2 = date('Y-m-d '.$hora2);
					}elseif($hora2 >= "00:00:00" and $hora2 <= "00:59:59"){
						$todayDate = date("Y-m-d");
						$date = strtotime(date("Y-m-d", strtotime($todayDate)) . " +1 day");
						$hour2 = date('Y-m-d ', $date);
						$horas2 = $hour2.$hora2;
					}

					if($hora1 >= "01:00:00" and $hora1 <= "23:59:59"){
						$todayDate = date("Y-m-d");
						$date = strtotime(date("Y-m-d", strtotime($todayDate)) . " +1 day");
						$hour1 = date('Y-m-d ', $date);
						$horas1 = $hour1.$hora1;
					}else{
						$horita1 = date('Y-m-d '.$hora1);
					}

					//me falta validar la cochina hora

					?><script>alert("<?php echo 'hora1: '.$horita1 ; ?> ");</script><?php

					?><script>alert("<?php echo 'hora2: '.$horita2 ; ?> ");</script><?php

					?><script>alert("<?php echo 'horas1: '.$horas1 ; ?> ");</script><?php

					?><script>alert("<?php echo 'horas2: '.$horas2 ; ?> ");</script><?php

					if($horas1 > $horas2 or $horita1 > $horita2 ){

						$result_f = "<script>alert('La hora inicial debe ser menor a la hora final');</script>";

					}*/

					if ($_REQUEST['hora_inicial'] == "" or $_REQUEST['hora_final'] == ""){

						$result_f = "<script>alert('Llenar el campo Hora Inicial');</script>";			

					}elseif($_REQUEST['hora_inicial'] == $_REQUEST['hora_final']){

						$result_f = "<script>alert('La hora inicial no puede ser igual a la hora final');</script>";
					}

					/*if($_REQUEST['hora_inicial'] > $_REQUEST['hora_final']){

						$result_f = "<script>alert('La hora inicial debe ser menor a la hora final');</script>";

					}else*/
					elseif($_REQUEST['hora_inicial'] == $_REQUEST['hora_final']){

						$result_f = "<script>alert('La hora inicial no puede ser igual a la hora final');</script>";

					}else{
						$res = CierreTurnoModel::insertar($_REQUEST['campo'],$_REQUEST['fecha'],$_REQUEST['hora_inicial'],$_REQUEST['hora_final'],$_REQUEST['fecha_actualizacion'],$_SESSION['auth_usuario']);
						/*?><script>alert("<?php echo '+++ la resultado es: '.$res ; ?> ");</script><?php*/
						if($res == 1){						
							?><script>alert('Registro guardado correctamente')</script><?php
							$result     = CierreTurnoTemplate::formSearch($_REQUEST['fecha'], $_REQUEST['fecha2'],$campo);	
						}else{
							?><script>alert('Ya existe hora de inicio y fin')</script><?php
							$result_f = "&nbsp;";
						}		
					}
				break;

			 default:

				$buscar = true;
				break;

		}

		if ($buscar){

		    	$result     = CierreTurnoTemplate::formSearch("","","");

		}

		if ($listado) {

			$campo      = trim($_REQUEST['campo']);

			$result     = CierreTurnoTemplate::formSearch($_REQUEST['fecha'], $_REQUEST['fecha2'],$campo);		
		    	$resultados = CierreTurnoModel::buscar($_REQUEST['fecha'], $_REQUEST['fecha2'], $campo);	    	
		    	$result_f   = CierreTurnoTemplate::resultadosBusqueda($resultados,$_REQUEST['fecha'], $_REQUEST['fecha2'],$campo);

		}

		$this->visor->addComponent("ContentT", "content_title", CierreTurnoTemplate::titulo());

		if ($result != "")
			$this->visor->addComponent("ContentB", "content_body", $result);

		if ($result_f == ""){

			$resultados = CierreTurnoTemplate::Formulario($fecha,$fecha2,"");
			$this->visor->addComponent("ContentF", "content_footer", CierreTurnoTemplate::resultadosBusqueda($resultados));

		}else{

			$this->visor->addComponent("ContentF", "content_footer", $result_f);
		}

	}

}
