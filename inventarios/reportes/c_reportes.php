<?php

class ReportesController extends Controller {

	function Init() {
		$this->visor = new Visor();
		$this->task = @$_REQUEST["task"];
		isset($_REQUEST['action'])?$this->action=$_REQUEST['action']:$this->action="";
	}

	function Run() {
        	$this->Init();
        	$Controlador = null;

        	switch ($this->request) {

            case "VENCIMIENTOLOTES":
                include "reportes/c_vencimiento_lotes.php";
                $Controlador = new VencimientoLotesController("VENCIMIENTOLOTES");
                break;

            case "PARTICIPAVENTALINEA":
                include "reportes/c_participaventalinea.php";
                $Controlador = new ParticipaVentaLineaController("PARTICIPAVENTALINEA");
                break;

            case "MARGENLINEA":
                include "reportes/c_margenlinea.php";
                $Controlador = new MargenLineaController("MARGENLINEA");
                break;

            case "MARGENARTICULO":
                include "reportes/c_margenarticulo.php";
                $Controlador = new MargenLineaController("MARGENARTICULO");
                break;

            case "FORMPROCES":
                include "reportes/c_formproces.php";
                $Controlador = new FormProcesController("FORMPROCES");
                break;

            case "CONSISTENCIA":
                include "reportes/c_consistencia.php";
                $Controlador = new ConsistenciaController("CONSISTENCIA");
                break;

            case "DIFINV":
                include "reportes/c_difinv.php";
                $Controlador = new DifInvController("DIFINV");
                break;

            case "FORMACU":
                include "reportes/c_formacu.php";
                $Controlador = new FormAcuController("FORMACU");
                break;

            case "KARDEX":
                include "reportes/c_kardex.php";
                $Controlador = new KardexController("KARDEX");
                break;

            case "TRANSF":
                include "reportes/c_transferencias.php";
                $Controlador = new TransferenciasController("TRANS");
                break;

            case "REPGUIA":
                include "reportes/c_repguia.php";
                $Controlador = new RepGuiaController("REPGUIA");
                break;

            case "TRANSDET":
                include "reportes/c_transdet.php";
                $Controlador = new TransDetController("TRANSDET");
                break;

            case "STKMINMAX":
                include "reportes/c_stkminmax.php";
                $Controlador = new StkMinMaxController("STKMINMAX");
                break;

            case "IMPORTARPRECIOS":
                include "reportes/c_importar_precios.php";
                $Controlador = new ImportarPreciosController("IMPORTARPRECIOS");
                break;

            case "FACTURASELECTRONICAS":
                include "reportes/c_facelectronicas.php";
                $Controlador = new FacturasElectronicasController("FACTURASELECTRONICAS");
                break;

		case "KARDEXACT":
        	        include "reportes/c_kardex_act.php";
        	        $Controlador = new KardexActController("KARDEXACT");
                break;

		case "KARDEXNEW":
        	        include "reportes/c_kardex_new.php";
        	        $Controlador = new KardexActController("KARDEXNEW");
                break;

		case "KARDEXRESUMEN":
			include "reportes/c_kardex_resumen.php";
	                $Controlador = new KardexActController("KARDEXRESUMEN");
                break;

		case "KARDEXVENTA":
			include "reportes/c_kardex_venta.php";
			$Controlador = new KardexVentaActController("KARDEXVENTA");
                break;

	case "AJUSTEINVENTARIO":
                	include "reportes/c_ajuste_inventario.php";
                	$Controlador = new AjusteInventarioController($this->task);
                break;

		case "IMPORTARCOMPRA":
                	include "reportes/c_importar_compra.php";
                	$Controlador = new TipodeCambioController($this->task);
                break;

            default:
                $this->visor->addComponent("ContentB", "content_body", "<h2><b>Funcion de reportes no conocida</b></h2>");
                break;
        }

        if ($Controlador != null) {
            $Controlador->Run();
            $this->visor = $Controlador->visor;
        }
    }

}

