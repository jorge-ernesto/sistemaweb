<?php

class PromocionesController extends Controller {

    function Init() {
	    $this->visor = new Visor();
    }

    function Run() {
	    $this->Init();
	    $Controlador = null;
	    switch ($this->request) {
		
		case "TARGPROMOCION":
			include "promociones/c_targpromocion.php";
			$Controlador = new TargpromocionController("TARGPROMOCION");
			break;

		case "CANJEITEM":
			include "promociones/c_canjeitem.php";
			$Controlador = new CanjeitemController("CANJEITEM");
			break;

		case "MOVPUNTOS":
			include "promociones/c_movpuntos.php";
			$Controlador = new MovpuntosController("MOVPUNTOS");
			break;

		case "MOVPUNTOSFIDELIZA":
			include "promociones/c_movpuntosfideliza.php";
			$Controlador = new MovPuntosFidelizaController("MOVPUNTOSFIDELIZA");
			break;

		case "RANKINGPUNTOSACUMULADOS":
			include "promociones/c_rankingpuntosacumulados.php";
			$Controlador = new RankingPuntosAcumuladosController("RANKINGPUNTOSACUMULADOS");
			break;		
	
		case "CANJES":
			include "promociones/c_canjes.php";
			$Controlador = new CanjesController("CANJES");
			break;

		case "CONSULTACANJES":
			include "promociones/c_consultacanjes.php";
			$Controlador = new ConsultaCanjesController("CONSULTACANJES");
			break;

		case "CAMPANIAFIDE":
			include "promociones/c_campaniafide.php";
			$Controlador = new CampaniaFideController("CAMPANIAFIDE");
			break;		

		case "HORARIOMULTI":
			include "promociones/c_horariomulti.php";
			$Controlador = new HorarioMultiController("HORARIOMULTI");
			break;

		case "PUNTOSXPRODUCTO":
			include "promociones/c_puntosxproducto.php";
			$Controlador = new PuntosxProductoController("PUNTOSXPRODUCTO");
			break;

		case "TIPOSCUENTA":
			include "promociones/c_tiposcuenta.php";
			$Controlador = new TiposCuentaController("TIPOSCUENTA");
			break;
		
		case "PUNTOSFIDELIZAMANUAL":
			include "promociones/c_puntosfidelizamanual.php";
			$Controlador = new PuntosFidelizaManualController("PUNTOSFIDELIZAMANUAL");
			break;

		case "RETENCIONES":
			include "promociones/c_retenciones.php";
			$Controlador = new RetencionesController("RETENCIONES");
			break;

		case "REPORTE_NUMTRANSFIDELIZA":
			include "promociones/c_reporte_numtransfideliza.php";
			$Controlador = new Reporte_NumTransFidelizaController("REPORTE_NUMTRANSFIDELIZA");
			break;

		case "TARJETASDUPLICADAS":
			include "promociones/c_tarjetasduplicadas.php";
			$Controlador = new TarjetasDuplicadasController("TARJETASDUPLICADAS");
			break;

		case 'DESCUENTOSFIDE':
			include('promociones/c_descuentos_fide.php');
			$Controlador = new DescuentosFideController("DESCUENTOSFIDE");
			break;

	        default:
		        $this->visor->addComponent("ContentB", "content_body", "<h2><b> Funcion de promociones no conocida".$this->request."</b></h2>");
		        break;
		}

		if ($Controlador != null) {
			$Controlador->Run();
			$this->visor = $Controlador->visor;
		}
	}
}
