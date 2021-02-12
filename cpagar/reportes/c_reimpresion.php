<?php

class ReimpresionController extends Controller{
    function Init()
    {
	$this->visor = new Visor();
	isset($_REQUEST["action"])?$this->action = $_REQUEST["action"]:$this->action = '';
    }

    function Run()
    {
	$this->Init();
	$result = '';
		
	include('reportes/m_reimpresion.php');
	include('reportes/t_reimpresion.php');
		
	$this->visor->addComponent('ContentT', 'content_title', ReimpresionTemplate::titulo() );
		
	switch ($this->request)
	{//task
    	    case 'PARAMETROS':
		$listarCPAG = false;
		switch($this->action)
		{
		    case 'Parametros':
			$result = ReimpresionTemplate::formBuscarCPAG();
			// luego los datos por defecto desde el modelo
			$lstCPAG = ReimpresionModel::buscarCPAG(@$_REQUEST["parametro"]);
			// aplica el template a la lista CPAG
			$result.= ReimpresionTemplate::listarCPAG($lstCPAG);
			$this->visor->addComponent("ContentB", "content_body", $result );
			break;
		    default:
			//listar ultimos movimientos
			$f_parametros= true;
		    break;
		}
		if($f_parametros)
		{
		    // primero el template del cuadro de busqueda
		    $result = ReimpresionTemplate::formParametros();
		    $this->visor->addComponent("ContentB", "content_body", $result );
		}
		break;
		
	    case 'CUADRO1':
	        $listarCPAG = false;
	        switch($this->action){
	    	    case 'Marcar':
		        $lstCPAG = ReimpresionModel::buscarCPAGMarcar( @$_REQUEST["busqueda"],@$_REQUEST["parametros"] );
		        $result=ReimpresionModel::guardarCPAGMarcado($lstCPAG);
		        if ($result == 'OK')
		        {
		    	    $lstCPAG = ReimpresionModel::buscarCPAG(@$_REQUEST["parametros"]);
			    $this->visor->addComponent("busqueda", "resultados_grid", ReimpresionTemplate::listarCPAG($lstCPAG));							
			}
			else
			{
			    $this->visor->addComponent("error", "error_detalle", $result);
			}
			break;
		    case 'Regresar':
		        $result = ReimpresionTemplate::formParametros();
		        $this->visor->addComponent("ContentB", "content_body", $result );
		        $listarCPAG = false;						
		        break;
		    case 'Imprimir':
			
		        break;
		    default:
		        //listar ultimos movimientos
		        $listarCPAG = true;
		        break;
		}
		if($listarCPAG)
		{
		    // primero el template del cuadro de busqueda
		    $result = ReimpresionTemplate::formBuscarCPAG();
		    // luego los datos por defecto desde el modelo
		    $lstCPAG = ReimpresionModel::buscarCPAG();
		    // aplica el template a la lista CPAG
		    $result.= ReimpresionTemplate::listarCPAG($lstCPAG);
		    $this->visor->addComponent("ContentB", "content_body", $result );
		}
		break;
	    case 'CUADRODET':
	        switch($this->action)
	        {
	            case 'setPerfilNDOC':
	        	// SE TIENE QUE HACER CON PARAMETROS POR QUE NO SE PUEDE PASAR EL ARRAY POR JAVASCRIPT
			$result = ReimpresionTemplate::setNDOC($_REQUEST["num_documento"], $_REQUEST["param1"], $_REQUEST["param2"], $_REQUEST["param3"], $_REQUEST["param4"]);
			$this->visor->addComponent("NumDoc_perfil", "NumDoc_perfil", $result);
			break;
		    default:
		        echo "defa";
		        break;
                }
        	break;
	    case 'IMPRESION':
		switch($this->action)
		{
		    case 'PDF':
			$results = ReimpresionModel::cintillo();
			ReimpresionTemplate::cintilloPDF($results);
			break;
		    default:
			break;
		}
		exit;
		break;
	    default:
	        $this->visor->addComponent('ContentB', 'content_body', '<h2>TAREA "'.$this->request.'" NO CONOCIDA EN REGISTROS</h2>');
	        break;
	}


    }
}
?>