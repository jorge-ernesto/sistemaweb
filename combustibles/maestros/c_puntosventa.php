<?php
  // Controlador del Modulo Generales

class PuntoVentaController extends Controller{
	function Init(){
		//Verificar seguridad
		$this->visor = new Visor();
		$this->task = @$_REQUEST["task"];
		$this->action = isset($_REQUEST["action"])?$_REQUEST["action"]:'';
		}

	function Run(){
		$this->Init();
		$result = '';
		require('maestros/m_puntosventa.php');
		require('maestros/t_puntosventa.php');
		include('../include/paginador_new.php'); 
		require("../clases/funciones.php");	
		$funcion = new class_funciones;
		$this->visor->addComponent('ContentT', 'content_title', PuntoVentaTemplate::titulo());

		//switch ($this->request){//task
			//case 'PUNTOSVENTA':
				$tablaNombre = 'PUNTOSVENTA';
				$listado = false;
				//evaluar y ejecutar $action
				switch ($this->action){
				
					case 'Agregar':
						$result = PuntoVentaTemplate::formPuntosVenta(array());
						$this->visor->addComponent("ContentB", "content_body", $result);
						break;

					case 'Modificar':
						$listado  = PuntoVentaModel::tmListado();
						$result   = PuntoVentaTemplate::listado($listado['datos'],'','',$_REQUEST['pos']);
						$this->visor->addComponent("ContentB", "content_body", $result);
						$this->visor->addComponent("ContentF", "content_foot", $result);
						$listado = false;
						break;

					case 'ModificarLado':
						$listado  = PuntoVentaModel::tmListado();
						$result   = PuntoVentaTemplate::listado($listado['datos'],'','disabled',$_REQUEST['pos']);
						$this->visor->addComponent("ContentB", "content_body", $result);
						$this->visor->addComponent("ContentF", "content_foot", $result);
						$listado = false;
						break;

					case 'ModificarLadosMarket':
						$listado  = PuntoVentaModel::tmListado();
						$result = PuntoVentaTemplate::errorResultado('LOS MARKET NO POSEEN LADOS !');
						$this->visor->addComponent("ContentB", "content_body", $result);
						break;

					case 'Eliminar':
						$result = PuntoVentaModel::eliminarRegistro($_REQUEST["registroid"]);
						if ($result == OK){
							$listado= true;
						} else {
							$result = PuntoVentaTemplate::errorResultado($result);
							$this->visor->addComponent("ContentB", "content_body", $result);
						}
						break;

					case 'TEXTO':
						$listado=false;
						break;

					case 'GUARDAR':
						?><script>//alert('<?php echo "-".$_POST["L1"]."-".$_POST["L2"]."-".$_POST["L3"]."-".$_POST["L4"]."-".$_POST["L5"]."-".$_POST["L6"]."-".$_POST["L7"]."-".$_POST["L8"]."-".$_POST["L9"]."-".$_POST["L10"]."-".$_POST["L11"]."-".$_POST["L12"]."-".$_POST["L13"]."-".$_POST["L14"]."-".$_POST["L15"]."-".$_POST["L16"] ?>');</script><?php
						
						if($_POST["interf".$_REQUEST["seleccion"]] == ''){
							$result = PuntoVentaModel::actualizarRegistroLados(
									$_REQUEST["seleccion"],
									('' == $_POST["L1"]  ? 'N': 'S'),
									('' == $_POST["L2"]  ? 'N': 'S'),
									('' == $_POST["L3"]  ? 'N': 'S'),
									('' == $_POST["L4"]  ? 'N': 'S'),
									('' == $_POST["L5"]  ? 'N': 'S'),
									('' == $_POST["L6"]  ? 'N': 'S'),
									('' == $_POST["L7"]  ? 'N': 'S'),
									('' == $_POST["L8"]  ? 'N': 'S'),
									('' == $_POST["L9"]  ? 'N': 'S'),
									('' == $_POST["L10"]  ? 'N': 'S'),
									('' == $_POST["L11"]  ? 'N': 'S'),
									('' == $_POST["L12"]  ? 'N': 'S'),
									('' == $_POST["L13"]  ? 'N': 'S'),
									('' == $_POST["L14"]  ? 'N': 'S'),
									('' == $_POST["L15"]  ? 'N': 'S'),
									('' == $_POST["L16"]  ? 'N': 'S')
									);
						} else {
							$result = PuntoVentaModel::actualizarRegistro(
									$_REQUEST["seleccion"],
									$_POST["interf".$_REQUEST["seleccion"]],
									('' == $_POST["L1"]  ? 'N': 'S'),
									('' == $_POST["L2"]  ? 'N': 'S'),
									('' == $_POST["L3"]  ? 'N': 'S'),
									('' == $_POST["L4"]  ? 'N': 'S'),
									('' == $_POST["L5"]  ? 'N': 'S'),
									('' == $_POST["L6"]  ? 'N': 'S'),
									('' == $_POST["L7"]  ? 'N': 'S'),
									('' == $_POST["L8"]  ? 'N': 'S'),
									('' == $_POST["L9"]  ? 'N': 'S'),
									('' == $_POST["L10"]  ? 'N': 'S'),
									('' == $_POST["L11"]  ? 'N': 'S'),
									('' == $_POST["L12"]  ? 'N': 'S'),
									('' == $_POST["L13"]  ? 'N': 'S'),
									('' == $_POST["L14"]  ? 'N': 'S'),
									('' == $_POST["L15"]  ? 'N': 'S'),
									('' == $_POST["L16"]  ? 'N': 'S'),
									$_REQUEST["ip".$_REQUEST["seleccion"]],
									$_REQUEST["serie".$_REQUEST["seleccion"]],
									$_REQUEST["sunat".$_REQUEST["seleccion"]],
									$_REQUEST["nombrepos".$_REQUEST["seleccion"]],
									$_REQUEST["impre".$_REQUEST["seleccion"]],
									$_REQUEST["disp".$_REQUEST["seleccion"]],
									$_POST["eject".$_REQUEST["seleccion"]],
									$_REQUEST["line".$_REQUEST["seleccion"]],
									$_REQUEST["mens".$_REQUEST["seleccion"]]
									);
						}
						$listado  = PuntoVentaModel::tmListado();
						$result = PuntoVentaTemplate::errorResultado('DATOS ALMACENADOS CORRECTAMENTE !');
						$this->visor->addComponent("ContentB", "content_body", $result);
						break;
			
					default:
						$listado = true;
						break;
				}

				if ($listado) {
					$listado  = PuntoVentaModel::tmListado();
					$result   = PuntoVentaTemplate::listado($listado['datos'],'disabled','disabled','x');
					$this->visor->addComponent("ContentB", "content_body", $result);
				}
		//}
	}
}
?>
