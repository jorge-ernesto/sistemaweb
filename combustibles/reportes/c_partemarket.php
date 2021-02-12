<?php

class ParteMarketController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action']) ? $this->action = $_REQUEST['action']:$this->action='';
	}

	function Run() {
		include 'reportes/m_partemarket.php';
		include 'reportes/t_partemarket.php';
	
		$this->Init();
	
		$result = '';
		$result_f = '';
		$search_form = false;

		switch ($this->action) {
			case "Reporte":
				echo "Entro al Reporte";

				$fechainicial = $_REQUEST['desde'];
				$cortar = str_replace('/', '-', $fechainicial);
				$final = date('Ym', strtotime($cortar));
				$hoy = date("dm");

				$hanio = substr($_REQUEST['hasta'], 6, 4);
				$hmes = substr($_REQUEST['hasta'], 3, 2);
				$anio = substr($_REQUEST['desde'], 6, 4);
				$mes = substr($_REQUEST['desde'], 3, 2);

				$hdia = substr($_REQUEST['hasta'], 0, 2);
				$dia = substr($_REQUEST['desde'], 0, 2);

				if($hdia.$hmes == $hoy) {
					?><script>alert("<?php echo 'La fecha final no puede ser la actual mientras aun no cierre el dia' ; ?> ");</script><?php
				} else if($dia.$mes == $hoy) {
					?><script>alert("<?php echo 'La fecha inicial no puede ser la actual mientras aun no cierre el dia' ; ?> ");</script><?php
				} else if($anio.$mes != $hanio.$hmes) {
					?><script>alert("<?php echo 'Ambas fechas deben coincidir en el mismo mes' ; ?> ");</script><?php
				} else {
					$resultado0 = ParteMarketModel::obtieneTC($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion']);
					$resultado1 = ParteMarketModel::obtieneLineas($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion']);
					$resultado2 = ParteMarketModel::obtieneMarket($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion']);
					$resultado3 = ParteMarketModel::obtieneLineasTurno($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion'],$final);
					$result_f = ParteMarketTemplate::reporte($resultado0,$resultado1,$resultado2,$resultado3,$_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion']);
					
					echo "<script>console.log('ObtieneTC')</script>";
					echo "<script>console.log('" . json_encode($resultado0) . "')</script>";
					echo "<script>console.log('obtieneLineas')</script>";
					echo "<script>console.log('" . json_encode($resultado1) . "')</script>";
					echo "<script>console.log('obtieneMarket')</script>";
					echo "<script>console.log('" . json_encode($resultado2) . "')</script>";
					echo "<script>console.log('obtieneLineasTurno')</script>";
					echo "<script>console.log('" . json_encode($resultado3) . "')</script>";
				}
				break;

			case "pdf":
				$resultado0 = ParteMarketModel::obtieneTC($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion']);
				$resultado1 = ParteMarketModel::obtieneLineas($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion']);
				$resultado2 = ParteMarketModel::obtieneMarket($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion']);
				ParteMarketTemplate::reportePDF($resultado0,$resultado1,$resultado2,$_REQUEST['desde'], $_REQUEST['hasta']);
				break;
	
			case "Imprimir":
				$file = "/tmp/imprimir/acumula_linea_turno.txt";
				$fh = fopen($file, "w");
				fwrite($fh, "");
				fclose($fh);

				$resu	= ParteMarketModel::acumuladoTurno($_REQUEST['estacion'], $_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['turno']);
				$result_f = ParteMarketTemplate::imprimir($resu);
				$cmd = ParteMarketModel::obtenerComandoImprimir($file);

				exec($cmd);

				?><script>alert('Imprimiendo la venta acumulada linea por turno');</script><?php
				break;
	
			default:
				$search_form = true;
				break;
		}

		if($search_form)
	    		$result = ParteMarketTemplate::search_form();

		if($result != '') $this->visor->addComponent("ContentB", "content_body", $result);
		if($result_f != '') $this->visor->addComponent("ContentF", "content_footer", $result_f);

	}
}
