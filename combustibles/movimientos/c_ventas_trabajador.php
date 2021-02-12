<?php

class VentasTrabajadorController extends Controller {

	function Init()	{
		$this->visor = new Visor();
		isset($_REQUEST['action'])?$this->action=$_REQUEST['action']:$this->action='';
	}
	
	function Run()	{
		include 'movimientos/m_ventas_trabajador.php';
		include 'movimientos/t_ventas_trabajador.php';
		$this->Init();
		
		$formulario_busqueda= "";
		$resultado_model	= "";
		$resultado_template	= "";

		$fecha		= $_REQUEST['dia'];
		$turno		= $_REQUEST['turno'];
		$trabajador	= $_REQUEST['trabajador'];
		
		$texto	= "";
		$eess	= Array();
		$fecha_vector	= explode("/",$fecha);
		$id = $fecha_vector[0].$fecha_vector[1].$fecha_vector[2].$turno;
		settype($id,"int");

		$imprimirwincha = true;
		
		switch($this->action) {
			case "Reporte":
				if(trim($fecha) == ""  || trim($turno) == "")
					$resultado_template = "<script>alert('Debe ingresar obligatoriamente el numero de Turno');</script>";
				else {
					//En caso se especifique un trabajador
					if (trim($trabajador)!="") {
						$formulario_busqueda= VentasTrabajadorTemplate::mostrarBusqueda($fecha,$turno,$trabajador,1,0);
						
						$resultado_model = VentasTrabajadorModel::obtenerReporteTurno($fecha,$turno,$trabajador);
						if ($resultado_model===FALSE || count($resultado_model) <= 0)
							$resultado_template = "<script>alert('No existen datos');</script>";
						else
							$resultado_template = VentasTrabajadorTemplate::mostrarReporte($resultado_model,"",0);
					}
					//Muestra todos los trabajadores de ese turno
					else {
					$trabajadores = VentasTrabajadorModel::obtenerTrabajadores($fecha_vector[2]."-".$fecha_vector[1]."-".$fecha_vector[0],$turno);
						if ($trabajadores===FALSE || count($trabajadores) <= 0)	{
							$formulario_busqueda= VentasTrabajadorTemplate::mostrarBusqueda($fecha,$turno,"",1,0);
							$resultado_template = "<script>alert('No existen datos');</script>";
						} else {
							// prepara el archivo para imprimir preconsolidacion
							$file = "/tmp/imprimir/PreConsolidacion_".$id;
							$fh = fopen($file, "w");
							fwrite($fh,"");
							fclose($fh);	//					

							$eess = VentasTrabajadorModel::obtenerDatosEESS();
							$consolidado = VentasTrabajadorModel::validarConsolidacion($fecha,$turno);
							if ($consolidado === FALSE) { //Turno aún no consolidado							
								$file = "/sistemaweb/combustibles/movimientos/query_consolidacion.txt";
								$fh = fopen($file, "w");
								fwrite($fh,"");
								fclose($fh);
								
								$file = "/tmp/imprimir/Consolidacion_".$id;
								$fh = fopen($file, "w");
								fwrite($fh,"");
								fclose($fh);
								
								$formulario_busqueda= VentasTrabajadorTemplate::mostrarBusqueda($fecha,$turno,"",2,1);
							}
							else //Turno consolidado
								$formulario_busqueda= VentasTrabajadorTemplate::mostrarBusqueda($fecha,$turno,"",3,1);
							
							$total = count($trabajadores);
							
							for($i = 0; $i < $total; $i++) {
								echo $trabajadores[$i]['codigo'];
								$resultado_model= VentasTrabajadorModel::obtenerReporteTurno($fecha,$turno,$trabajadores[$i]['codigo']);
								if ($resultado_model===FALSE || count($resultado_model) <= 0)
									$a = 0;
								else {
									if ($consolidado === FALSE) //Turno aún no consolidado
										$resultado_template .= "<br/>".VentasTrabajadorTemplate::mostrarReporte($resultado_model,$eess,1);
									else //Turno consolidado
										$resultado_template .= "<br/>".VentasTrabajadorTemplate::mostrarReporte($resultado_model,"",0);
								}
							}
						}
					}
				}
				break;

			case "Consolidar sin impresion":
				$imprimirwincha = false;

			case "Consolidar":
				if(VentasTrabajadorModel::validarConsolidacion($fecha,$turno) === FALSE) {
					$resultado_consolidacion = VentasTrabajadorModel::insertarConsolidacion($fecha,$turno);
					if ($resultado_consolidacion == "1" && $imprimirwincha) {
						$file = "/tmp/imprimir/Consolidacion_".$id;
						$cmd = VentasTrabajadorModel::obtenerComandoImprimir($file);
						exec($cmd);
						?><script>alert('Se completo la consolidacion del turno. Se ha enviado a imprimir las winchas.');</script><?php
					} else {
						echo "---".$resultado_consolidacion."---";
						?><script>alert('Error al consolidar el Turno');</script><?php
					}
				} else {
					?><script>alert('El turno ya fue consolidado');</script><?php
				}
				break;

			case "Nuevo Dia":						
				$formulario_busqueda = VentasTrabajadorTemplate::mostrarBusqueda($fecha,$turno,"",1,0);
				$resultado_template = " ";
				break;

			case "Imprimir Wincha":		
				$file = "/tmp/imprimir/PreConsolidacion_".$id;
				$cmd = VentasTrabajadorModel::obtenerComandoImprimir($file);
				exec($cmd);
				?><script>alert('Se ha enviado a imprimir las winchas.');</script><?php
				break;

			default:
				$formulario_busqueda= VentasTrabajadorTemplate::mostrarBusqueda(date("d/m/Y"),"","",1,0);
				$resultado_template	= "";
				break;
		}
		
		$this->visor->addComponent("ContentT", "content_title", VentasTrabajadorTemplate::titulo());
		
		if ($formulario_busqueda != "") 
			$this->visor->addComponent("ContentB", "content_body", $formulario_busqueda);
		
		if ($resultado_template != "") 
			$this->visor->addComponent("ContentF", "content_footer", $resultado_template);
	}
}
