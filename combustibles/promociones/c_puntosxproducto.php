<?php
	
	class PuntosxProductoController extends Controller{
		
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
			include('promociones/m_puntosxproducto.php');
			include('promociones/t_puntosxproducto.php');
			include('../include/paginador_new.php'); 
			require("../clases/funciones.php");	
			$funcion = new class_funciones;
				

			$this->visor->addComponent('ContentT', 'content_title',PuntosxProductoTemplate::titulo());
			if(!$_REQUEST['rxp'] && !$_REQUEST['pagina'])
			{
				$_REQUEST['rxp'] = 100;
				$_REQUEST['pagina'] = 0;
			}
			switch ($this->request){//task
				case 'PUNTOSXPRODUCTO':
					$tablaNombre = 'PUNTOSXPRODUCTO';
					$listado = false;
					//evaluar y ejecutar $action
					switch ($this->action){	
						case 'Buscar':
							//Listo	
							$tipobusqueda = $_REQUEST['tipobusqueda'];
							$filtro = strtoupper(trim($_REQUEST['busqueda']));	
							$busqueda = PuntosxProductoModel::tmListado($filtro,$tipobusqueda,$_REQUEST['rxp'],$_REQUEST['pagina']);
							$result = PuntosxProductoTemplate::listado($busqueda['datos']);
							$this->visor->addComponent("ListadoB", "resultados_grid", $result);
							break;	
						case 'Nuevo':
							//TITULO
							$_REQUEST['titulo'] ='NUEVO';
							$result = PuntosxProductoTemplate::formPuntosxProducto(array());
							$this->visor->addComponent("ContentB", "content_body", $result);
						break;
						case 'Guardar':
							$listado = false;
							$exito="";
							//1.- CAPTURAMOS LOS VALORES DEL FORMULARIO
							$puntosxproducto['idcampania'] = trim($_REQUEST['idcampania']);
							$puntosxproducto['idarticulo'] = trim($_REQUEST['idarticulo']);
							$puntosxproducto['puntossol'] = trim($_REQUEST['puntossol']);
							$puntosxproducto['puntosunidad'] = trim($_REQUEST['puntosunidad']);	
							//2.- CAPTURAMOS VALORES DE CONTROL
							$usuario=$_SESSION['auth_usuario'];
							$sucursal =$_SESSION['almacen'];	
							

							if($_REQUEST['accion']=='actualizarPuntosxProducto'){
								$result = PuntosxProductoModel::actualizarPuntosxProducto(
														$puntosxproducto['idcampania'] ,
														$puntosxproducto['idarticulo'],
														$puntosxproducto['puntossol'],
														$puntosxproducto['puntosunidad']
														);
							}else{
								$result = PuntosxProductoModel::ingresarPuntosxProducto(
														$puntosxproducto['idcampania'] ,
														$puntosxproducto['idarticulo'],
														$puntosxproducto['puntossol'],
														$puntosxproducto['puntosunidad']
														);
							}
							$exito= ($result=="0")?"0":"1";
							if ($exito =="1"){
								$_REQUEST['titulo'] ='INGRESAR PUNTOS POR PRODUCTO';
								$result = PuntosxProductoTemplate::formPuntosxProducto(array());
								$this->visor->addComponent("ContentB", "content_body", $result);
								$result = PuntosxProductoTemplate::errorResultado('� SE GRABO/ACTUALIZO CORRECTAMENTE LOS DATOS !');
								$this->visor->addComponent("error", "error_body", $result);	
							}else{
								$result =  PuntosxProductoTemplate::errorResultado('� ERROR: AL REGISTRAR EL REGISTRO, VERIQUE LOS DATOS !');
								$this->visor->addComponent("error", "error_body", $result);
							}	
						break;	
						case 'Modificar':
							//1.- CAPTURAMOS LOS VALORES DEL FORMULARIO
							$_REQUEST['titulo'] ='MODIFICAR PUNTOS POR PRODUCTO';
							$puntosxproducto['idcampania'] = trim($_REQUEST['idcampania']);
							$puntosxproducto['descampania'] = trim($_REQUEST['descampania']);
							$puntosxproducto['idarticulo'] = trim($_REQUEST['idarticulo']);
							$puntosxproducto['desarticulo'] = trim($_REQUEST['desarticulo']);
							$puntosxproducto['puntossol'] = trim($_REQUEST['puntossol']);
							$puntosxproducto['puntosunidad'] = trim($_REQUEST['puntosunidad']);	
							$result = PuntosxProductoTemplate::formPuntosxProducto($puntosxproducto);
							$this->visor->addComponent("ContentB", "content_body", $result);
						break;	
						case 'Eliminar':
							$result = PuntosxProductoModel::eliminarPuntosxProducto(trim($_REQUEST['idcampania']), trim($_REQUEST['idarticulo']));
							$listado  = PuntosxProductoModel::tmListado(' ','default',$_REQUEST['rxp'],$_REQUEST['pagina']);
							$result    = PuntosxProductoTemplate::formBuscar($listado['paginacion']);   
							$result   .= PuntosxProductoTemplate::listado($listado['datos']);
							$this->visor->addComponent("ListadoB", "resultados_grid", $result);
						break;
						default:
							//listado
							$listado = true;
							break;
					}
					if ($listado) { 
						$listado  = PuntosxProductoModel::tmListado(' ','default',$_REQUEST['rxp'],$_REQUEST['pagina']);
						$result   = PuntosxProductoTemplate::formBuscar($listado['paginacion']);   
						$result   .= PuntosxProductoTemplate::listado($listado['datos']);
						$this->visor->addComponent("ContentB", "content_body", $result);
					}	
				
			}
		}
	}
	
?>
