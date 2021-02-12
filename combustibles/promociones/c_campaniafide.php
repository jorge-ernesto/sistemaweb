<?php
	
class CampaniaFideController extends Controller {
	
	function Init() {
  		$this->visor = new Visor();
  		$this->task = @$_REQUEST["task"];
  		$this->action = isset($_REQUEST["action"])?$_REQUEST["action"]:'';
	}

    function Run() {
  		$this->Init();

  		$result = '';
  		$bolMensaje ='0';

  		include('promociones/m_campaniafide.php');
  		include('promociones/t_campaniafide.php');
  		include('../include/paginador_new.php');
  		require("../clases/funciones.php");

  		$funcion = new class_funciones;
  
  		$this->visor->addComponent('ContentT', 'content_title',CampaniaFideTemplate::titulo());
  		if(!$_REQUEST['rxp'] && !$_REQUEST['pagina']) {
     		$_REQUEST['rxp'] = 100;
     		$_REQUEST['pagina'] = 0;
  		}

		$fechaini = date("d/m/Y");
		$fechafin = date("d/m/Y");

      		switch ($this->request) {
      			case 'CAMPANIAFIDE':
				$tablaNombre = 'CAMPANIAFIDE';
				$listado = false;
	
				switch ($this->action){
					case 'Buscar':
						$filtro = strtoupper(trim($_REQUEST['busqueda']));
		    				$busqueda = CampaniaFideModel::tmListado($filtro,$_REQUEST['rxp'],$_REQUEST['pagina']);
		    				$result = CampaniaFideTemplate::listado($busqueda['datos']);
		   				$this->visor->addComponent("ListadoB", "resultados_grid", $result);
						break;	
					case 'Nueva':
						$_REQUEST['titulo'] ='NUEVA CAMPA&Ntilde;A';
						$listaTipoCuentas = CampaniaFideModel::listarTipoCuentas("");
						$result = CampaniaFideTemplate::formCampaniafide(array(),$listaTipoCuentas['datostipocuentas'],"", $fechaini, $fechafin);
						$this->visor->addComponent("ContentB", "content_body", $result);
						break;
					case 'Guardar':
						$listado = false;
						$exito="";
						//1.- CAPTURAMOS LOS VALORES DEL FORMULARIO
						$campania['idcampania'] = trim($_REQUEST['idcampania']);
						$campania['campaniadescripcion'] =strtoupper(trim($_REQUEST['campaniadescripcion']));
						$campania['campaniafechaini'] = trim($_REQUEST['campaniafechaini']);
						$campania['campaniafechafin'] = trim($_REQUEST['campaniafechafin']);
						$campania['campaniadiasven'] = (trim($_REQUEST['campaniadiasven'])==""?"0":$_REQUEST['campaniadiasven']);
						$campania['campaniaobjetivo'] = strtoupper(trim($_REQUEST['campaniaobjetivo']));
						$tipocli = $_REQUEST['campaniatiposcli'];
						$campania['campaniarepeticiones'] = trim($_REQUEST['campaniarepeticiones']);
						$campania['slogan'] = trim($_REQUEST['slogan']);
						$campania['saludacumple'] = trim($_REQUEST['saludacumple']);
						//2.- CAPTURAMOS VALORES DE CONTROL
						$usuario=$_SESSION['auth_usuario'];
						$sucursal =$_SESSION['almacen'];			
						if($_REQUEST['accion']=='actualizarcampania'){
							$result = CampaniaFideModel::actualizarCampania(
								$campania['idcampania'],
								$campania['campaniadescripcion'],								
								$campania['campaniafechafin'],
								$campania['campaniadiasven'],
								$campania['campaniaobjetivo'],
								$usuario,
								$sucursal,
								$campania['campaniarepeticiones'],
								$campania['slogan'],
								$campania['saludacumple']);
						} else {			
							$idcampania = CampaniaFideModel::nuevoIdCampania();
							$result = CampaniaFideModel::ingresarCampania(
								$idcampania['nuevo_id'],	
								$campania['campaniadescripcion'],
								$campania['campaniafechaini'],
								$campania['campaniafechafin'],
								$campania['campaniadiasven'],
								$campania['campaniaobjetivo'],
								$usuario,
								$sucursal,
								$campania['campaniarepeticiones'],
								$campania['slogan'],
								$campania['saludacumple']);
							 
							foreach($tipocli as $idtipocli){
								$resultDet =CampaniaFideModel::ingresarTipoCuenta($idcampania['nuevo_id'],$idtipocli);	
							}				
						}			
						$exito= ($result=="0")?"0":"1";

						if ($exito =="1"){
							/*$_REQUEST['titulo'] ='INGRESAR CAMPAÑA';
							$listaTipoCuentas = CampaniaFideModel::listarTipoCuentas("");
							$result = CampaniaFideTemplate::formCampaniafide(array(),$listaTipoCuentas['datostipocuentas']);
							$this->visor->addComponent("ContentB", "content_body", $result);
							$result = CampaniaFideTemplate::errorResultado('¡ SE GRABO/ACTUALIZO CORRECTAMENTE LOS DATOS !');
							$this->visor->addComponent("error", "error_body", $result);	*/
							$listado = true;
						} else {
							$result =  CampaniaFideTemplate::errorResultado('¡ ERROR: AL INGRESAR LA CAMPA&Ntilde;A,VERIQUE LOS DATOS !');
							$this->visor->addComponent("error", "error_body", $result);
						}	
						break;
		
					case 'MostrarCampania':
						//1.- CAPTURAMOS LOS VALORES DEL FORMULARIO
						$_REQUEST['titulo'] ='DATOS DE CAMPAÑA';
						$campania = CampaniaFideModel::obtenerCampania($_REQUEST['idcampania']);
						/*$campania['idcampania'] = trim($_REQUEST['idcampania']);
						$campania['campaniadescripcion'] =strtoupper(trim($_REQUEST['campaniadescripcion']));
						$campania['campaniafechacrea'] = trim($_REQUEST['campaniafechacrea']);
						$campania['campaniafechaini'] = trim($_REQUEST['campaniafechaini']);
						$campania['campaniafechafin'] = trim($_REQUEST['campaniafechafin']);
						$campania['campaniadiasven'] = (trim($_REQUEST['campaniadiasven'])==""?"0":$_REQUEST['campaniadiasven']);
						$campania['campaniaobjetivo'] = strtoupper(trim($_REQUEST['campaniaobjetivo']));
						$campania['campaniarepeticiones'] = trim($_REQUEST['campaniarepeticiones']);*/
						$tipo="1";
						$fechaini  = $campania['campaniafechaini'];
						$fechafin  = $campania['campaniafechafin'];

						$listaCampaniasTipo = CampaniaFideModel::listarCampaniasTipo($tipo,$campania['idcampania']);
						$result = CampaniaFideTemplate::formCampaniafide($campania,$listaCampaniasTipo['datoscampaniastipo'],"Modificar",$fechaini,$fechafin);
						$this->visor->addComponent("ContentB", "content_body", $result);
						break;

	    				default:
						$listado = true;
		  				break;
				}

				if ($listado) { 		
				    	$listado   = CampaniaFideModel::tmListado('',$_REQUEST['rxp'],$_REQUEST['pagina']);
				    	$result    = CampaniaFideTemplate::formBuscar($listado['paginacion']);   
					$result   .= CampaniaFideTemplate::listado($listado['datos']);
		  		    	$this->visor->addComponent("ContentB", "content_body", $result);
				}

		}
    	}
}
