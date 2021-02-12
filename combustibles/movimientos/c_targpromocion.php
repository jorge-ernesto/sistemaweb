<?php

  Class TargpromocionController extends Controller{
	
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
      include('movimientos/m_targpromocion.php');
      include('movimientos/t_targpromocion.php');
      include('../include/paginador_new.php'); 
	  require("../clases/funciones.php");	
      $funcion = new class_funciones;
	  
      $this->visor->addComponent('ContentT', 'content_title', TargpromocionTemplate::titulo());
      if(!$_REQUEST['rxp'] && !$_REQUEST['pagina'])
      {
         $_REQUEST['rxp'] = 100;
         $_REQUEST['pagina'] = 0;
      }
      switch ($this->request){//task
      case 'TARGPROMOCION':
	$tablaNombre = 'TARGPROMOCION';
	$listado = false;
	//evaluar y ejecutar $action
	//echo " action es: ".$this->action;
	switch ($this->action){
		
	     	
		case 'Nueva Cuenta':
			$listatiposcuentas = TargpromocionModel::listarTiposCuenta("",2);
			$result = TargpromocionTemplate::formCuentapromocion(array(),array(),$listatiposcuentas['datostipocuenta'],'0');
			$this->visor->addComponent("ContentB", "content_body", $result);
			break;
		
		case 'EliminarCuenta':
			$result = TargpromocionModel::eliminarCuenta(trim($_REQUEST['cuentaid']));
			$listado  = TargpromocionModel::tmListado(' ','default',$_REQUEST['rxp'],$_REQUEST['pagina']);
		    $result    = TargpromocionTemplate::formBuscar($listado['paginacion']);   
		    $result   .= TargpromocionTemplate::listado($listado['datos']);
  		    $this->visor->addComponent("ListadoB", "resultados_grid", $result);
			break;
			
		case 'Adicionar Tarjeta':
			//1. Capturando los valores de Cuenta
			$cuenta['nu_cuenta_numero'] = trim($_REQUEST['cuentanumero']);			
			$cuenta['ch_cuenta_nombres'] = strtoupper(trim( $_REQUEST['cuentanombres']));			
			$cuenta['ch_cuenta_apellidos'] = strtoupper(trim($_REQUEST['cuentaapellidos']));
			$cuenta['ch_cuenta_dni'] = strtoupper(trim($_REQUEST['cuentadni']));
			$cuenta['ch_cuenta_ruc'] = strtoupper(trim($_REQUEST['cuentaruc']));
			$cuenta['ch_cuenta_direccion'] = strtoupper(trim($_REQUEST['cuentadireccion']));
			$cuenta['ch_cuenta_telefono1'] = strtoupper(trim($_REQUEST['cuentatelefono1']));
			$cuenta['ch_cuenta_telefono2'] = strtoupper(trim($_REQUEST['cuentatelefono2']));
			$cuenta['ch_cuenta_email'] = strtoupper(trim($_REQUEST['cuentaemail']));
			$cuenta['nu_cuenta_puntos'] ="0";
			$usuario=$_SESSION['auth_usuario'];
			
			//2. Capturando los valores de tarjeta
			$tarjetanumero = strtoupper(trim($_REQUEST['tarjetanumero']));
			$tarjetadescripcion = strtoupper(trim($_REQUEST['tarjetadescripcion']));
 			$tarjetaplaca = strtoupper(trim($_REQUEST['tarjetaplaca']));
 			$tarjetafechavencimiento = strtoupper(trim($_REQUEST['tarjetafechaven']));
			$tarjetapuntos = "0";
			$tarjetacuenta = "1";//aca debe cambiar, solo pongo 1 de prueba
			$tarjetatitularSINO=strtoupper(trim($_REQUEST['tarjetatitular']));		
			if(trim($_REQUEST['idcuenta'])!=''){

					$objcuenta = array();
					$objcuenta =  TargpromocionModel::obtenerCuenta(trim($_REQUEST['idcuenta']),'1');
					$result =TargpromocionModel::insertarTarjeta($objcuenta['id_cuenta'],
														         $tarjetanumero,
																 $tarjetadescripcion,
																 $tarjetaplaca,
																 $tarjetafechavencimiento,
																 $tarjetapuntos,
																 $tarjetacuenta,
																 $tarjetatitularSINO,
																 $usuario);
																 									 
					$bolMensaje =($result=='0'?'0':'1');
					$listatiposcuentas = TargpromocionModel::listarTiposCuenta("",2);
					$listadoTarjetas = array();
					$listadoTarjetas = TargpromocionModel::listarTarjetas($objcuenta['id_cuenta'],'1');
					
					//$campolectura indica si los campos del formulario seran de lectura o no
					$camposlectura="1";	
					$result = TargpromocionTemplate::formCuentapromocion($objcuenta,
																		 $listadoTarjetas['datostarjeta'],
																		 $listatiposcuentas['datostipocuenta'],
																		 $camposlectura);
					$this->visor->addComponent("ContentB", "content_body", $result);
					if ($bolMensaje =="1"){

					$result = TargpromocionTemplate::errorResultado('SE GRABO/ACTUALIZO CORRECTAMENTE LOS DATOS !!!');
					$this->visor->addComponent("error", "error_body", $result);	
					}else{
				
					$result =  TargpromocionTemplate::errorResultado('ERROR: NUMERO TARJETA YA EXISTENTE, INGRESE OTRA');
					$this->visor->addComponent("error", "error_body", $result);
					}	
				
			
			}
			
		break;
		
		case 'ModificarTarjeta':
			$objcuenta = array();
			$objtarjeta = array();
			$objcuenta =  TargpromocionModel::obtenerCuenta($_REQUEST['cuentaid'],'1');
			$objtarjeta['id_tarjeta'] 		=trim($_REQUEST['tarjetaid']);
			$objtarjeta['nu_tarjeta_numero']	=trim($_REQUEST['numtarjeta']);
			$objtarjeta['ch_tarjeta_descripcion']	=trim($_REQUEST['desctarjeta']);
			$objtarjeta['ch_tarjeta_placa']		=trim($_REQUEST['placatarjeta']);
			$objtarjeta['dt_tarjeta_creacion']	=trim($_REQUEST['fechacre']);
			$objtarjeta['dt_tarjeta_vencimiento']	=trim($_REQUEST['fechaven']);
			$objtarjeta['ch_tarjeta_titular']	=trim($_REQUEST['titulartarjetaSINO']);	
			$objtarjeta['nu_tarjeta_puntos']	=trim($_REQUEST['ptstarjeta']);	
			
			$result = TargpromocionTemplate::formTarjetapromocion($objcuenta,$objtarjeta);
  			$this->visor->addComponent("ContentB", "content_body", $result);
		break;
		case 'EliminarTarjeta':
			$objcuenta = array();
			$listadoTarjetas = array();
			$listatiposcuentas = TargpromocionModel::listarTiposCuenta("",2);
			$resultEliminar = TargpromocionModel::eliminarTarjeta(trim($_REQUEST['cuentaid']),trim($_REQUEST['tarjetaid']));
			$objcuenta =  TargpromocionModel::obtenerCuenta(trim($_REQUEST['cuentaid']),'1');		
			$listadoTarjetas = TargpromocionModel::listarTarjetas(trim($_REQUEST['cuentaid']),'1');
			$camposlectura='1';
  			$result = TargpromocionTemplate::formCuentapromocion($objcuenta,$listadoTarjetas['datostarjeta'],$listatiposcuentas['datostipocuenta'],$camposlectura);
  			$this->visor->addComponent("ContentB", "content_body", $result);
		break;
	  	case 'ModificarCuenta':
			$objcuenta = array();
			$objtarjeta = array();
			$listatiposcuentas = TargpromocionModel::listarTiposCuenta("",2);
			$objcuenta =  TargpromocionModel::obtenerCuenta(trim($_REQUEST['cuentaid']),'1');
			$listadoTarjetas = array();
			$listadoTarjetas = TargpromocionModel::listarTarjetas(trim($_REQUEST['cuentaid']),'1');
			$camposlectura='0';
			$result = TargpromocionTemplate::formCuentapromocion($objcuenta,$listadoTarjetas['datostarjeta'],$listatiposcuentas['datostipocuenta'],$camposlectura);
  			$this->visor->addComponent("ContentB", "content_body", $result);
		break;
		case 'Guardar Cuenta':
			
			$listado = false;
			$listatiposcuentas = TargpromocionModel::listarTiposCuenta("",2);
			//1. Capturando los valores de Cuenta
			$cuenta['id_cuenta']=trim($_REQUEST['idcuenta']);
			$cuenta['nu_cuenta_numero'] = trim($_REQUEST['cuentanumero']);			
			$cuenta['ch_cuenta_nombres'] = strtoupper(trim( $_REQUEST['cuentanombres']));			
			$cuenta['ch_cuenta_apellidos'] = strtoupper(trim($_REQUEST['cuentaapellidos']));
			$cuenta['ch_cuenta_dni'] = strtoupper(trim($_REQUEST['cuentadni']));
			$cuenta['ch_cuenta_ruc'] = strtoupper(trim($_REQUEST['cuentaruc']));
			$cuenta['ch_cuenta_direccion'] = strtoupper(trim($_REQUEST['cuentadireccion']));
			$cuenta['ch_cuenta_telefono1'] = strtoupper(trim($_REQUEST['cuentatelefono1']));
			$cuenta['ch_cuenta_telefono2'] = strtoupper(trim($_REQUEST['cuentatelefono2']));
			$cuenta['ch_cuenta_email'] = strtoupper(trim($_REQUEST['cuentaemail']));
			$cuenta['id_tipo_cuenta'] = strtoupper(trim($_REQUEST['cuentatipo']));
			$usuario=$_SESSION['auth_usuario'];
		
			if($_REQUEST['accion']=='actualizarcuenta'){
			$result = TargpromocionModel::actualizarCuenta(
							       $cuenta['id_cuenta'],							       
							       $cuenta['nu_cuenta_numero'],
							       $cuenta['ch_cuenta_nombres'],
							       $cuenta['ch_cuenta_apellidos'],
							       $cuenta['ch_cuenta_dni'],
							       $cuenta['ch_cuenta_ruc'],
							       $cuenta['ch_cuenta_direccion'],
							       $cuenta['ch_cuenta_telefono1'],
							       $cuenta['ch_cuenta_telefono2'],
							       $cuenta['ch_cuenta_email'],
								   $cuenta['id_tipo_cuenta'],
							       $usuario);
				
			if($result =='1'){
			$objcuenta =  TargpromocionModel::obtenerCuenta(trim($cuenta['id_cuenta']),'1');
			$listadoTarjetas = TargpromocionModel::listarTarjetas(trim($cuenta['id_cuenta']),'1');
			$camposlectura='1';	
			$result = TargpromocionTemplate::formCuentapromocion($objcuenta,$listadoTarjetas['datostarjeta'],$listatiposcuentas['datostipocuenta'],$camposlectura);
			$this->visor->addComponent("ContentB", "content_body", $result);
				$bolMensaje = "1";	
			}
			
			
			}else{
				$result = TargpromocionModel::ingresarCuenta($cuenta['nu_cuenta_numero'],
								     $cuenta['ch_cuenta_nombres'],
								     $cuenta['ch_cuenta_apellidos'],
								     $cuenta['ch_cuenta_dni'],
								     $cuenta['ch_cuenta_ruc'],
								     $cuenta['ch_cuenta_direccion'],
								     $cuenta['ch_cuenta_telefono1'],
								     $cuenta['ch_cuenta_telefono2'],
								     $cuenta['ch_cuenta_email'],
									 $cuenta['id_tipo_cuenta'],
								     '0',
								     $usuario);
				echo "Al ingresar Cuenta, el Resultado es: ".$result." \n";
				if($result =='1'){
					echo " inserto exitosamente \n";
					$objcuenta = array();
					$objcuenta =  TargpromocionModel::obtenerCuenta($cuenta['nu_cuenta_numero'],'2');
					$camposlectura='1';
					$result = TargpromocionTemplate::formCuentapromocion($objcuenta,array(),$listatiposcuentas['datostipocuenta'],$camposlectura);
					$this->visor->addComponent("ContentB", "content_body", $result);
				$bolMensaje = "1";
				echo "mensajes es: ".$bolMensaje." \n";
				}

			}
	
			echo "mensajes antes de validar es: ".$bolMensaje." \n";
			if ($bolMensaje =="1"){
					echo "resultado true \n";
					$result = TargpromocionTemplate::errorResultado('¡ SE GRABO/ACTUALIZO CORRECTAMENTE LOS DATOS !');
					$this->visor->addComponent("error", "error_body", $result);	
			}else{
				
					$result =  TargpromocionTemplate::errorResultado('¡ ERROR: Nº DE CUENTA YA EXISTE, INGRESE OTRA !');
					$this->visor->addComponent("error", "error_body", $result);
			}		
		
		    break;
		case 'Guardar Tarjeta':
			$tarjetaid=strtoupper(trim($_REQUEST['idtarjeta']));
			$tarjetanumero = strtoupper(trim($_REQUEST['tarjetanumero']));
			$tarjetadescripcion = strtoupper(trim($_REQUEST['tarjetadescripcion']));
 			$tarjetaplaca = strtoupper(trim($_REQUEST['tarjetaplaca']));
 			$tarjetafechavencimiento = strtoupper(trim($_REQUEST['tarjetafechaven']));
			$tarjetacuenta = "1";//aca debe cambiar, solo pongo 1 de prueba
			$tarjetatitularSINO=strtoupper(trim($_REQUEST['tarjetatitular']));	
			$usuario=$_SESSION['auth_usuario'];
			
			$result = TargpromocionModel::modificarTarjeta(
							  $tarjetaid,
							  $tarjetanumero,
							  $tarjetadescripcion,
							  $tarjetaplaca,
							  $tarjetafechavencimiento,
							  $tarjetacuenta,
							  $tarjetatitularSINO,
							  $usuario);
			if($result=='1'){
						$listado  = TargpromocionModel::tmListado(' ','default',$_REQUEST['rxp'],$_REQUEST['pagina']);
		    $result    = TargpromocionTemplate::formBuscar($listado['paginacion']);   
		    $result   .= TargpromocionTemplate::listado($listado['datos']);
  		    $this->visor->addComponent("ContentB", "content_body", $result);
			
			}else{
				$result =  TargpromocionTemplate::errorResultado('¡ ERROR: Nº DE TARJETA YA EXISTE, INGRESE OTRA !');
				$this->visor->addComponent("error", "error_body", $result);
			}
							  

							  
		break;
    
	    case 'Buscar':
		    //Listo
			$tipobusqueda = $_REQUEST['tipobusqueda'];
			$filtro = strtoupper(trim($_REQUEST['busqueda']));
		    	$busqueda = TargpromocionModel::tmListado($filtro,$tipobusqueda,$_REQUEST['rxp'],$_REQUEST['pagina']);
		    	$result = TargpromocionTemplate::listado($busqueda['datos']);
		   	 $this->visor->addComponent("ListadoB", "resultados_grid", $result);
		    	break;
    
	    default:
		echo 'default'; 
		   //listado
		   echo "a";
		   $listado = true;
		   break;
	}

		if ($listado) { 
		    $listado  = TargpromocionModel::tmListado(' ','default',$_REQUEST['rxp'],$_REQUEST['pagina']);
		    $result    = TargpromocionTemplate::formBuscar($listado['paginacion']);   
		    $result   .= TargpromocionTemplate::listado($listado['datos']);
  		    $this->visor->addComponent("ContentB", "content_body", $result);
		}

  
      }
    }
  }

