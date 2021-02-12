<?php

class MaestrosController extends Controller {

    function Init() {
        $this->visor = new Visor();
    }

    function Run() {
        $this->Init();
        $Controlador = null;
        switch ($this->request) {
            case "ITEMALIAS":
                include "maestros/c_item_alias.php";
                $Controlador = new CRUDItemAliasController("ITEMALIAS");
                break;
            case "SAPMAPEOTABLAS":
                include "maestros/c_sap_cuentas_contables_CRUD.php";
                $Controlador = new SAPMapeoTablasCRUDController("SAPMAPEOTABLAS");
                break;

            case "SISCONTCTACONTABLES":
                include "maestros/c_siscont_cuentas_contables_CRUD.php";
                $Controlador = new SISCONTCtaContablesCRUDController("SISCONTCTACONTABLES");
                break;

            case "SIIGOPRODUCTOS":
                include "maestros/c_siigo_productos.php";
                $Controlador = new SIIGOProductosCRUDController("SIIGOPRODUCTOS");
                break;

            case "CLUBES":
                include "maestros/c_clubes.php";
                $Controlador = new ClubesController("CLUBES");
                break;

            case "DESCUENTOS":
                include "maestros/c_descuentos.php";
                $Controlador = new DescuentosController("DESCUENTOS");
                break;
            case "GUIARAPIDA":
                include "maestros/c_guia_rapida_2004.php";
                $Controlador = new GuiaRapidaController("GUIARAPIDA");
                break;
            case "TRANSACCIONES":
                include "maestros/c_transacciones.php";
                $Controlador = new TransaccionesController("TRANSACCIONES");
                break;
            case "LADOS":
                include "maestros/c_lados.php";
                $Controlador = new LadosController("LADOS");
                break;
	    case "GEANMAESTRO":
                include "maestros/c_gean_maestro.php";
                $Controlador = new LadosController("GEANMAESTRO");
                break;
            case "PUNTOSVENTA":
                include "maestros/c_puntosventa.php";
                $Controlador = new PuntoVentaController("PUNTOSVENTA");
                break;
            case "INTELEC":
                include "maestros/c_intelec.php";
                $Controlador = new IntElecController("INTELEC");
                break;
            case "NEW_POS_PUNTO_VENTA":
                include "maestros/c_spos.php";
                $Controlador = new SPosController("");
                break;
            case "NEW_POS_LADOS":
                include "maestros/c_f_pump_pos.php";
                $Controlador = new FPumpPosController("");
                break;
            case "POS_DESCUENTO_RUC":
                include "maestros/c_pos_descuento_ruc.php";
                $Controlador = new PosDescuentoRucController("");
            default:
                $this->visor->addComponent("ContentB", "content_body", "<h2><b>Funcion de maestros no conocida" . $this->request . "</b></h2>");
                break;
        }

        if ($Controlador != null) {
            $Controlador->Run();
            $this->visor = $Controlador->visor;
        }
    }

}