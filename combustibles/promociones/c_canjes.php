<?php
/*error_reporting(E_ALL);
ini_set('display_errors', 1);*/

class CanjesController extends Controller
{
    function Init()
    {
        $this->visor = new Visor();
        isset($_REQUEST['action'])?$this->action=$_REQUEST['action']:$this->action="";
    }

    function Run(){
        include "promociones/m_canjes.php";
        include "promociones/t_canjes.php";

        $this->Init();

        $result = "";
        $result_f = "";
        $form_search = false;
        $form_search_canje = false;

        switch ($this->action) {
    	    case "Buscar":
        		$tarjeta = CanjesModel::obtenerDatos($_REQUEST['busquedatarjeta']);
        		if ($tarjeta != null)
        		    $result_f = CanjesTemplate::mostrarDatos($tarjeta);
        		else
        		    $result_f = CanjesTemplate::mostrarErrorBusqueda();
        		break;
    	    case "Canjear":
                if(isset($_REQUEST['tarjeta'])) {
                    $card = $_REQUEST['tarjeta'];
            		$tarjeta = CanjesModel::obtenerDatos($card);

                    if ($tarjeta==null||$tarjeta['es_titular']!='1'){
                        return;
                    }
                    
                    $response = CanjesModel::realizarCanje($card,$_REQUEST['articulo'],$_REQUEST['observaciones']);
                    $success = $response['success'];
                    $impresion = $response['impresion'];

            		if ($success == false){
                        $result_f = CanjesTemplate::mostrarError();
                    }elseif($success == true && $impresion == true){
                        $result_f = CanjesTemplate::mostrarExito();
                    }elseif($success == true && $impresion == false){
                        $result_f = CanjesTemplate::mostrarExitoSinImpresion();
                    }
                } else {
                    echo 'Error al enviar la tarjeta!';
                }
    		break;
            default:
                $form_search = true;
                break;
        }

        if ($form_search)
            $result = CanjesTemplate::formBuscar();
    	if ($form_search_canje)
    	    $result = CanjesTemplate::formBuscarCanje($_REQUEST['busquedatarjeta']);

        $this->visor->addComponent("ContentT", "content_title", CanjesTemplate::titulo());
        if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
        if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);
    }
}

