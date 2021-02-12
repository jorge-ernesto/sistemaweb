<?php
	
	class PuntosFidelizaManualController extends Controller{
		
		function Init(){
			//Verificar seguridad
			$this->visor = new Visor();
			$this->task = @$_REQUEST["task"];
			$this->action = isset($_REQUEST["action"])?$_REQUEST["action"]:'';
			//otros variables de entorno
		}
		
		function Run(){
			$this->Init();
			$result = '';
			$bolMensaje ='0';	
			include('promociones/m_puntosfidelizamanual.php');
			include('promociones/t_puntosfidelizamanual.php');
			include('../include/paginador_new.php'); 
			require("../clases/funciones.php");	
			$funcion = new class_funciones;
			$this->visor->addComponent('ContentT', 'content_title',PuntosFidelizaManualTemplate::titulo());
			if(!$_REQUEST['rxp'] && !$_REQUEST['pagina']){
				$_REQUEST['rxp'] = 20;
				$_REQUEST['pagina'] = 0;
			}

			$fechaini = date("d/m/Y");
			$fechafin = date("d/m/Y");

			switch ($this->request){//task
				case 'PUNTOSFIDELIZAMANUAL':
					$tablaNombre = 'PUNTOSFIDELIZAMANUAL';
					$listado = false;
					switch ($this->action){
						case 'Buscar':
							$almacen 	= trim($_REQUEST['almacen']);
							$tipobusqueda = $_REQUEST['tipobusqueda'];
							$filtro1 = strtoupper(trim($_REQUEST['fechainicio']));	
							$filtro2 = strtoupper(trim($_REQUEST['fechafin']));
							$busqueda = PuntosFidelizaManualModel::tmListado($almacen, $filtro1, $filtro2, $tipobusqueda, $_REQUEST['rxp'], $_REQUEST['pagina']);
							$result = PuntosFidelizaManualTemplate::listado($busqueda['datos']);
							$this->visor->addComponent("ListadoB", "resultados_grid", $result);
							break;	

						case 'Excel':
							$almacen 	= trim($_REQUEST['almacen']);
							$tipobusqueda = $_REQUEST['tipobusqueda'];
							$filtro1 = strtoupper(trim($_REQUEST['fechainicio']));	
							$filtro2 = strtoupper(trim($_REQUEST['fechafin']));

							$busqueda 	= PuntosFidelizaManualModel::tmListado($almacen, $filtro1, $filtro2, $tipobusqueda, $_REQUEST['rxp'], $_REQUEST['pagina']);

							ob_end_clean();
							$buff = " PUNTOS MANUALES - FIDELIZACION \n\n";
							$buff .= "SUCURSAL; TARJETA; NOMBRE; PUNTOS; FECHA; HORA; USUARIO \n";		

							foreach($busqueda['datos'] as $A)
								$buff .= "{$A[7]}; {$A[1]}; {$A[2]}; {$A[3]}; {$A[4]}; {$A[5]}; {$A[6]} \n";

							header("Content-type: text/csv");
							header("Content-Disposition: attachment; filename=\"puntos_manuales.csv\""); 
							header("Cache-Control: no-cache, must-revalidate");
							header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

							die($buff);

						break;

						case 'Nuevo':
							//TITULO
							$_REQUEST['titulo'] ='INGRESAR PUNTOS FIDELIZACION MANUALMENTE';
							$result = PuntosFidelizaManualTemplate::formPuntosfidelizamanual(array());
							$this->visor->addComponent("ContentB", "content_body", $result);
						break;	
						case 'Guardar':
							$listado = false;
							$exito="";
							//1.- CAPTURAMOS LOS VALORES DEL FORMULARIO
							$puntosfideliza['idpunto'] = trim($_REQUEST['idpunto']);
							$puntosfideliza['busquedatarjeta'] = trim($_REQUEST['busquedatarjeta']);
							$puntosfideliza['puntos'] = trim($_REQUEST['puntos']);	
							//2.- CAPTURAMOS VALORES DE CONTROL
							$usuario=$_SESSION['auth_usuario'];
							$sucursal =$_SESSION['almacen'];	
							
							$result = PuntosFidelizaManualModel::ingresarpuntosfidelizamanual(
										$puntosfideliza['busquedatarjeta'],
										$puntosfideliza['puntos']
									);
							
							$exito= ($result=="0")?"0":"1";
							if ($exito =="1"){
								$_REQUEST['titulo'] ='INGRESAR PUNTOS FIDELIZACION MANUALMENTE';
								$result = PuntosFidelizaManualTemplate::formPuntosfidelizamanual(array());
								$this->visor->addComponent("ContentB", "content_body", $result);
								$result = PuntosFidelizaManualTemplate::errorResultado('SE GRABO/ACTUALIZO CORRECTAMENTE LOS DATOS');
								$this->visor->addComponent("error", "error_body", $result);	
							}else{
								$result =  PuntosFidelizaManualTemplate::errorResultado('ERROR: AL REGISTRAR EL REGISTRO, VERIQUE LOS DATOS');
								$this->visor->addComponent("error", "error_body", $result);
							}	
						break;	
						
						case 'Modificar':
							//1.- CAPTURAMOS LOS VALORES DEL FORMULARIO
							$_REQUEST['titulo'] ='MODIFICAR PUNTOS FIDELIZACION MANUALMENTE';
							$puntosfideliza['idpunto'] = trim($_REQUEST['idpunto']);
							$puntosfideliza['busquedatarjeta'] = trim($_REQUEST['busquedatarjeta']);
							$puntosfideliza['puntos'] = trim($_REQUEST['puntos']);		
							$result = PuntosFidelizaManualTemplate::formPuntosfidelizamanual($puntosfideliza);
							$this->visor->addComponent("ContentB", "content_body", $result);
						break;	
						case 'Eliminar':
							$result = PuntosxProductoModel::eliminarPuntosxProducto(trim($_REQUEST['idpunto']), trim($_REQUEST['idarticulo']));
							$listado  = PuntosFidelizaManualModel::tmListado('','','','default',$_REQUEST['rxp'],$_REQUEST['pagina']);
							$result    = PuntosFidelizaManualTemplate::formBuscar("", $fechaini, $fechafin, $listado['paginacion']);   
							$result   .= PuntosFidelizaManualTemplate::listado($listado['datos']);
							$this->visor->addComponent("ListadoB", "resultados_grid", $result);
						break;

						default:
							//listado
							$listado = true;
							break;
					}
					if ($listado) {
						$listado = PuntosFidelizaManualModel::tmListado('','','','',$_REQUEST['rxp'],$_REQUEST['pagina']);
						$result = PuntosFidelizaManualTemplate::formBuscar("", $fechaini, $fechafin, $listado['paginacion']); 
						$result .= PuntosFidelizaManualTemplate::listado($listado['datos']);
						$this->visor->addComponent("ContentB", "content_body", $result);
					}
			}
		}
	}
