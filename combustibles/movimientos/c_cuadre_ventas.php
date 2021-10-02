<?php
class CuadreVentasController extends Controller {
	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action'])?$this->action=$_REQUEST['action']:$this->action='';
	}

	function Run() {
		include 'movimientos/m_cuadre_ventas.php';
		include 'movimientos/t_cuadre_ventas.php';
		include 'movimientos/m_consolidacion.php';
		include 'movimientos/t_consolidacion.php';

		$this->Init();

		$result = "";
		$result_f = "";
		$result_x = "";
		$form_search = false;
		$listado = false;
		$editar = false;
		$actualizar = false;

		switch($this->action) {
			case "Imprimir":
			case "Procesar":
				$reporte = CuadreVentasModel::obtenerReporte($_REQUEST['dia1'],$_REQUEST['dia1'],$_REQUEST['turno1'],$_REQUEST['turno1'],$_REQUEST['trabajador']);
				// echo "Reporte";
				// echo "<script>console.log('" . json_encode($reporte) . "')</script>";
				if ($reporte===FALSE)
					$result_f = "<script>alert('Error al obtener el reporte. Verifique los parametros.')</script>";
				else {
					if ($this->action == "Procesar")
						$result_f = CuadreVentasTemplate::mostrarReporte($reporte);
					else {
						$eess = ConsolidacionModel::obtenerDatosEESS();
						$texto = ConsolidacionTemplate::generarWinchaReporte($reporte[0],$eess);echo $texto;
						$reporte = "";
						$cmd = ConsolidacionModel::obtenerComandoImprimir("");
						$procpipes = array(
							0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
							1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
							2 => array("pipe", "w")   // stderr is a file to write to
						);
						$pipes = Array();
						$proc = proc_open($cmd,$procpipes,$pipes);
						$ok = FALSE;
						if (is_resource($proc)) {
							fwrite($pipes[0],$texto);
							fclose($pipes[0]);
							proc_close($proc);
							$ok = TRUE;
						}
						if ($ok == TRUE) {
							$result_x = "<script>alert('Se ha mandado a imprimir la wincha+')</script>";
						} else {
							$result_x = "<script>alert('Error al imprimir el reporte')</script>";
						}
					}
				}
				break;
			default:
				$result = CuadreVentasTemplate::formSearch();
				$result_f = "";
				break;
		}

		$this->visor->addComponent("ContentT", "content_title", CuadreVentasTemplate::titulo());
		if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);
		if ($result_x != "") $this->visor->addComponent("ContentX", "content_x", $result_x);
	}
}
?>
