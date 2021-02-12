<?php
class SobrantesFaltantesTrabajadorController extends Controller {
	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action'])?$this->action=$_REQUEST['action']:$this->action='';
	}

	function Run() {
		include 'movimientos/m_sobrantes_faltantes_x_trabajador.php';
		include 'movimientos/t_sobrantes_faltantes_x_trabajador.php';

		$this->Init();

		$result = "";
		$result_f = "";
		$form_search = false;
		$listado = false;
		$editar = false;
		$actualizar = false;

		switch($this->action) {
			case "Buscar":			
				//$codtrabajador = $_REQUEST['codtrabajador'];	
				$ordenpor = $_REQUEST['ordenpor'];
				$almacenes = SobrantesFaltantesTrabajadorModel::obtenerAlmacenes();
				$tipos = SobrantesFaltantesTrabajadorModel::obtenerTrabajadores("", "");
				$resultados = SobrantesFaltantesTrabajadorModel::buscar($_REQUEST['almacen'],$_REQUEST['fecha'],$_REQUEST['fecha2'],$_REQUEST['codtrabajador'], $ordenpor);
				$result_f = SobrantesFaltantesTrabajadorTemplate::resultadosBusqueda($resultados,$almacenes,$tipos,$ordenpor);
				break;

			case "Agregar":
				$almacenes = SobrantesFaltantesTrabajadorModel::obtenerAlmacenes();
				$trabajadores = Array();
				$result = SobrantesFaltantesTrabajadorTemplate::formAgregar($almacenes, $trabajadores);
				$result_f = "&nbsp;";
				break;

			case "actualizaAgregar":
				$almacenes = SobrantesFaltantesTrabajadorModel::obtenerAlmacenes();
				$trabajadores = SobrantesFaltantesTrabajadorModel::obtenerTrabajadores($_REQUEST['dia'],$_REQUEST['turno']);
				$result = SobrantesFaltantesTrabajadorTemplate::formAgregar($almacenes, $trabajadores,$_REQUEST);
				$result_f = "&nbsp;";
				break;

			case "Guardar":
				
				$flag = SobrantesFaltantesTrabajadorModel::validaDia($_REQUEST['dia']);

				if($flag == 1){
					?><script>alert("<?php echo 'No se puede agregar. Fecha ya consolidada!'; ?> ");</script><?php
				}else{
	
					$res = SobrantesFaltantesTrabajadorModel::agregar($_REQUEST['almacen'],$_REQUEST['dia'],$_REQUEST['turno'],$_REQUEST['trabajador'],$_REQUEST['importe'],$_REQUEST['observacion']);
					if ($res==TRUE) {
						$almacenes = SobrantesFaltantesTrabajadorModel::obtenerAlmacenes();
						$result = SobrantesFaltantesTrabajadorTemplate::formSearch($almacenes);
						$result_f = "<script>alert('Se ha registrado correctamente');</script>";
					} else {
						$result_f = "<script>alert('No se pudo registrar. Intente nuevamente');</script>";
					}
				}

				break;

			case "Regresar":
				$almacenes = SobrantesFaltantesTrabajadorModel::obtenerAlmacenes();
				$result = SobrantesFaltantesTrabajadorTemplate::formSearch($almacenes);
				break;
			case "Importar":
				$result = SobrantesFaltantesTrabajadorTemplate::formImportar();
				$result_f = "";
				break;
			case "doImportar":
				$res = SobrantesFaltantesTrabajadorModel::importarDia($_REQUEST['fecha']);
				if ($res=="OK") {
					$almacenes = SobrantesFaltantesTrabajadorModel::obtenerAlmacenes();
					$result = SobrantesFaltantesTrabajadorTemplate::formSearch($almacenes);
					$result_f = "<script>alert('Importacion exitosa');document.getElementById('control').location.href='/sistemaweb/combustibles/control.php?rqst=MOVIMIENTOS.SOBRANTESFALTANTESTRABAJADOR';</script>";
				} else {
					$result_f = "<script>alert('Error al importar: $res');document.getElementById('control').location.href='/sistemaweb/combustibles/control.php?rqst=MOVIMIENTOS.SOBRANTESFALTANTESTRABAJADOR';</script>";
				}
				break;
			default:
				$almacenes = SobrantesFaltantesTrabajadorModel::obtenerAlmacenes();
				$result = SobrantesFaltantesTrabajadorTemplate::formSearch($almacenes);
				break;
		}

		$this->visor->addComponent("ContentT", "content_title", SobrantesFaltantesTrabajadorTemplate::titulo());
		if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);
	}

}
