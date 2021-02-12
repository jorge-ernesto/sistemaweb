<?php

class TrabajadorxIslaController extends Controller {

	function Init() {	      	
	      	$this->visor = new Visor();
	      	$this->task = @$_REQUEST["task"];
	      	$this->action = isset($_REQUEST["action"])?$_REQUEST["action"]:'';		
    	}

    	function Run() {
      		$this->Init();
      		$result = '';	
      		include('maestros/m_trabajadorxIsla.php');
      		include('maestros/t_trabajadorxIsla.php');
      		include('../include/paginador_new.php');	  
	  	require("../clases/funciones.php");	
      		$funcion = new class_funciones;
	  
      		$this->visor->addComponent('ContentT', 'content_title', TrabajadorxIslaTemplate::titulo());

      		if(!$_REQUEST['rxp'] && !$_REQUEST['pagina']) {
         		$_REQUEST['rxp'] = 100;
         		$_REQUEST['pagina'] = 0;
      		}

      		switch ($this->request){

      			case 'TRABAJADORXISLA':
				$tablaNombre = 'TRABAJADORXISLA';
				$listado = false;

				switch ($this->action) {
	     	
					case 'Agregar':
						$result = TrabajadorxIslaTemplate::formTrabajador(array());
						$this->visor->addComponent("ContentB", "content_body", $result);
						break;

			  		case 'Modificar':
						$variab = trim($_REQUEST["fecha"]);
						$variab2 = trim($_REQUEST["turno"]);
						$fconso = TrabajadorxIslaModel::validarConsolidacion($variab,$variab2);
						if($fconso == 1) {
							?><script>alert('No se puede modificar\nTurno y fecha ya consolidados.');</script><?php
						} else {
							$record = TrabajadorxIslaModel::recuperarRegistroArray(trim($_REQUEST["registroid"]),
													       trim($_REQUEST["fecha"]),
													       trim($_REQUEST["turno"]),
													       trim($_REQUEST["codIsla"]),
													       trim($_REQUEST["codTrab"]),
													       trim($_REQUEST["tipo"]));

							$result = TrabajadorxIslaTemplate::formTrabajador($record);
							$this->visor->addComponent("ContentB", "content_body", $result);
						}
						break;
	
					case 'TEXTO':
						$listado=false;
						break;
			
					case 'Guardar':
						$flag = 0;
						$listado = false;

						if ($_REQUEST['tipo'] == 'C') {
							$isla = $_REQUEST['isla1'];
						} else {
							$isla = $_REQUEST['isla2'];
						}

						$fechaa = trim($_REQUEST['fecha']);
						$turnoo = trim($_REQUEST['trab']['turno']);

						if($_REQUEST['accion'] == 'actualizar') {
							if ($isla == 'TODOS') {
								echo '<script name="accion">alert("Debe seleccionar una Isla/Lado") </script>';
						                $result = 'FALTO';
							} else {
								$flagConso = TrabajadorxIslaModel::validarConsolidacion($fechaa, $turnoo);
							    	if($flagConso == 1){
									?><script>alert('No se puede modificar\nTurno y fecha ya consolidados.');</script><?php
									$flag = 1;
							   	} else {
									$flag = 0;
									$result = TrabajadorxIslaModel::actualizarRegistro(trim($_REQUEST['trab']['codigosuc']),
														       trim($_REQUEST['fecha']),
														       trim($_REQUEST['trab']['turno']),
														       trim($isla),
														       strtoupper(trim($_REQUEST['codigotrab'])),
														       trim($_REQUEST['tipo']));
							   	}
							}			
						} else {
							if ($isla == 'TODOS') {
								echo'<script name="accion">alert("Debe seleccionar una Isla/Lado") </script>';
				                        	$result = 'FALTO';
							} else if (trim($_REQUEST['trab']['codigosuc']) == "") {
								echo "<script>alert(\"Error de validacion - cierre el navegador y vuelva a ingresar\");";
								$result = 'FALTO';
							} else {
						    		$flagConso = TrabajadorxIslaModel::validarConsolidacion($fechaa, $turnoo);
						    		if($flagConso == 1) {
									?><script>alert('No se puede agregar\nTurno y fecha ya consolidados.');</script><?php
									$flag = 2;
						    		} else {
									$flag = 0;
									$result = TrabajadorxIslaModel::ingresarRegistro(trim($_REQUEST['trab']['codigosuc']),
											       	trim($_REQUEST['fecha']),
											       	trim($_REQUEST['trab']['turno']),
											       	trim($isla),
											       	strtoupper(trim($_REQUEST['codigotrab'])),
											       	trim($_REQUEST['tipo']));
						   		}
							}
			
						}
		
						if ($result != '' and $result != 'FALTO') {
							$result = TrabajadorxIslaTemplate::errorResultado('ERROR: UBICACI?N DE TRABAJADOR YA EXISTENTE');
							$this->visor->addComponent("error", "error_body", $result);
						} else if ($result != 'FALTO' and $flag == 0) {		
							$registro = array();
							$registro["ch_sucursal"] = $_REQUEST['trab']['codigosuc'];
							$registro["dt_dia"] = $_REQUEST['fecha'];
							$registro["ch_posturno"] = $_REQUEST['trab']['turno'];
							$registro["ch_codigo_trabajador"] = $_REQUEST['codigotrab'];
							$registro["nombretrab"] = $_REQUEST['nombretrab'];
							$registro["ch_tipo"] = 'C';

							$result = TrabajadorxIslaTemplate::formTrabajador($registro);
							$this->visor->addComponent("ContentB", "content_body", $result);
							$result = TrabajadorxIslaTemplate::errorResultado('SE GRABO/ACTUALIZO CORRECTAMENTE LOS DATOS !!!');
							$this->visor->addComponent("error", "error_body", $result);

						} else if ($flag == 1) {
							$listado   = TrabajadorxIslaModel::tmListado("","","",$_REQUEST['rxp'],$_REQUEST['pagina'],FALSE);
					  		$result    = TrabajadorxIslaTemplate::formBuscar($listado['paginacion']);   
							$result   .= TrabajadorxIslaTemplate::listado($listado['datos']);
			  				$this->visor->addComponent("ContentB", "content_body", $result);
						} else if($flag == 2) {
							$result = TrabajadorxIslaTemplate::formTrabajador(array());
							$this->visor->addComponent("ContentB", "content_body", $result);
							break;
						}		
				    		break;

					case 'Eliminar':
						$dd  = $_REQUEST["fecha"];
						$variab  = substr($dd,8,2)."/".substr($dd,5,2)."/".substr($dd,0,4);
						$variab2 = trim($_REQUEST["turno"]);
						$fconso  = TrabajadorxIslaModel::validarConsolidacion($variab,$variab2);
						///$felim   = TrabajadorxIslaModel::validarEliminacion($dd);

						if($fconso == 1) {
							?><script>alert('No se puede eliminar\nTurno y fecha ya consolidados.');</script><?php
						//} elseif ($felim != 1) {
						/*	?><script>alert('No se puede eliminar\nFecha fuera del rango de 5 dias permitidos.');</script><?php*/
						} else {
							$record = TrabajadorxIslaModel::eliminarRegistro(trim($_REQUEST["registroid"]),
													trim($_REQUEST["fecha"]),
													trim($_REQUEST["turno"]),
													trim($_REQUEST["codIsla"]),
													trim($_REQUEST["codTrab"]),
													trim($_REQUEST["tipo"]));

							$fecha = $funcion->date_format($variab,'YYYY-MM-DD');
						    	$busqueda = TrabajadorxIslaModel::tmListado($fecha,$fecha,"",$_REQUEST['rxp'], $_REQUEST['pagina'],FALSE);
						    	$result = TrabajadorxIslaTemplate::listado($busqueda['datos']);
						    	$this->visor->addComponent("ListadoB", "resultados_grid", $result);
						}
						break;
    
				    	case 'Buscar':
						$desde = $funcion->date_format($_REQUEST['fdesde'],'YYYY-MM-DD');
						$hasta = $funcion->date_format($_REQUEST['fhasta'],'YYYY-MM-DD');
						$trabajador = $_REQUEST['trabajador'];
					    	$busqueda = TrabajadorxIslaModel::tmListado($desde,$hasta,$trabajador,$_REQUEST['rxp'], $_REQUEST['pagina'],FALSE);
					    	$result = TrabajadorxIslaTemplate::listado($busqueda['datos']);
					    	$this->visor->addComponent("ListadoB", "resultados_grid", $result);
					    	break;

				    	case 'Exportar':
						$desde = $funcion->date_format($_REQUEST['fdesde'],'YYYY-MM-DD');
						$hasta = $funcion->date_format($_REQUEST['fhasta'],'YYYY-MM-DD');
						$trabajador = $_REQUEST['trabajador'];
					    	$busqueda = TrabajadorxIslaModel::tmListado($desde,$hasta,$trabajador,$_REQUEST['rxp'], $_REQUEST['pagina'],TRUE);
						header("Content-Type: text/csv");
						header("Content-Disposition: attachment; filename=\"reporte.csv\"");
						header("Pragma: public");
						header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
						foreach ($busqueda['datos'] as $f)
							echo '="' . $f[0] . '","' . $f[1] . '","' . $f[2] . '","' . $f[6] . '",="' . $f[7] . '","' . $f[4] . ' - ' . $f[5] . "\"\r\n";
					    	die("");

				    	default:
					   	$listado = true;
					   	break;
				}

				if ($listado) {	
					$listado   = TrabajadorxIslaModel::tmListado("","","",$_REQUEST['rxp'],$_REQUEST['pagina'],FALSE);
					$result    = TrabajadorxIslaTemplate::formBuscar($listado['paginacion']);   
					$result   .= TrabajadorxIslaTemplate::listado($listado['datos']);
		  			$this->visor->addComponent("ContentB", "content_body", $result);
				}
      		}
    	}
}
