<?php
  // Controlador del Modulo Generales

  Class FacturasController extends Controller{
    function Init(){
      //Verificar seguridad
      $this->visor = new Visor();
      $this->task = @$_REQUEST["task"];
      $this->action = isset($_REQUEST["action"])?$_REQUEST["action"]:'';
      $this->datos = isset($_REQUEST["datos"])?$_REQUEST["datos"]:'';
      //otros variables de entorno
    }

    function Run()
    {
      $this->Init();
      $result = '';
      include('facturacion/m_facturasespeciales.php');
      include('facturacion/t_facturasespeciales.php');
      include('../include/paginador_new.php');
      include('../include/m_sisvarios.php');
      $this->visor->addComponent('ContentT', 'content_title', FacturasTemplate::titulo());
      /*if(!$_REQUEST['rxp'] && !$_REQUEST['pagina'])
      {
         $_REQUEST['rxp'] = 100;
         $_REQUEST['pagina'] = 0;
      }*/
      switch ($this->request)
      {//task
      case 'ESPECIALES':
      //echo "ENTRO";
	$listado = false;
	//evaluar y ejecutar $action
	switch ($this->action)
	{
	    
	    /*case 'Generar Reporte':
			$record = FacturasModel::ModelReportePDF($_REQUEST['busqueda']);
			$result = FacturasTemplate::formReporte();
			$result .= FacturasTemplate::TmpReportePDF($record);
			$this->visor->addComponent("ContentB", "content_body", $result);
			break;*/
		
		/*case 'Reporte':
			$result = FacturasTemplate::formReporte();
		    $this->visor->addComponent("ContentB", "content_body", $result);
			break;*/
		
		case 'Agregar':
		    $result = FacturasTemplate::formFacturas(array());
		    $this->visor->addComponent("ContentB", "content_body", $result);
		    break;
    
	    case 'Eliminar':
		    $result = FacturasModel::eliminarRegistro($_REQUEST["registroid"]);
		    if ($result == 'OK'){
			$listado= true;
		    } else {
			$result = FacturasTemplate::errorResultado($result);
			$this->visor->addComponent("error", "error_body", $result);
		    }
		    break;
    
	    case 'Anular':
		    $result = FacturasModel::anulacionRegistro($_REQUEST["registroid"]);
		    if ($result == OK){
			$listado= true;
		    } else {
			$result = FacturasTemplate::errorResultado($result);
			$this->visor->addComponent("error", "error_body", $result);
		    }
		    break;
	    
	    case 'Modificar':
		    $registroid = FacturasModel::GenerarRegitroID();
		    $record = FacturasModel::recuperarRegistro($registroid);
		    $registrosArray = FacturasModel::recuperarArticulos($registroid);
		    $registrosComplemento = FacturasModel::recuperarComplemento($registroid);
		    if($registrosComplemento){
		       $_SESSION["ARR_COMP"] = $registrosComplemento;
		    }
		    $result = FacturasTemplate::formFacturas($record,$registrosArray);
		    $this->visor->addComponent("ContentB", "content_body", $result);
		    break;
    
	    case 'Guardar':
		    $result = FacturasModel::guardarRegistro($this->datos);
		    if($result!=OK){
		       $result = FacturasTemplate::errorResultado($result);
		       $this->visor->addComponent("error", "error_body", $result);
		    }
		 	$listado = true;
		    break;
    
	    case 'Actualizar':
	    	$registrosArray=array();
		    $numeros = count($_REQUEST['cod_articulo']);
		    for($i=0; $i<$numeros; $i++){
		    	array_push($registrosArray,array('cod_articulo'=> $_REQUEST['cod_articulo'][$i], 
		    	'pre_lista_precio'=>$_REQUEST['articulos']['pre_lista_precio'],
		    	'desc_articulo'=>$_REQUEST['desc_articulo'][$i],
		    	'cant_articulo'=>$_REQUEST['cant_articulo'][$i],
		    	'precio_articulo'=>$_REQUEST['precio_articulo'][$i],
		    	'neto_articulo'=>$_REQUEST['neto_articulo'][$i],
		    	'igv_articulo'=>$_REQUEST['igv_articulo'][$i],
		    	'dscto_articulo'=>$_REQUEST['dscto_articulo'][$i],
		    	'total_articulo'=>$_REQUEST['total_articulo'][$i]));
		    }
		   	$result = FacturasModel::actualizarRegistro($this->datos, $registrosArray);
		    $listado = true;
	    	break;    
	    	
	    case 'Buscar':
		    $busqueda = FacturasModel::tmListado($_REQUEST["busqueda"],$_REQUEST['rxp'],$_REQUEST['pagina']);
		    $result = FacturasTemplate::listado($busqueda['datos']);
	        $this->visor->addComponent("error", "error_body", " ");
		    $this->visor->addComponent("ListadoB", "resultados_grid", $result);
		    break;
    
	    case 'Autorizar':
	    	//print_r($_REQUEST['registroid']);
	    	$rs = FacturasModel::autorizarFactura($_REQUEST['registroid']);
	    	$listado=true;
	    	break;    
		    
	    default:
	    //listado
	    $listado = true;
	    $_REQUEST['busqueda']['codigo']='';
	    $_REQUEST['busqueda']['f_desde']=date('d/m/Y');
	    $_REQUEST['busqueda']['f_hasta']=date('d/m/Y');
	    unset($_SESSION['ARTICULOS']);
	    unset($_SESSION['TOTAL_ARTICULOS']);
	    unset($_SESSION["ARR_COMP"]);
	    //$this->visor->addComponent("ContentT","content_title",TarjetasMagneticasTemplate::titulo());
	    break;
	}
	
	if ($listado) 
	{
		//print_r($_REQUEST['busqueda'].' paginacion:'.$_REQUEST['paginacion']);
		$listado    = FacturasModel::tmListado((is_null($_REQUEST["busqueda"])?$_REQUEST['paginacion']:$_REQUEST['busqueda']),$_REQUEST['rxp'],$_REQUEST['pagina']);
	    $result     = FacturasTemplate::formBuscar($listado['paginacion']);
	    $result    .= FacturasTemplate::listado($listado['datos']);
        $this->visor->addComponent("error", "error_body", " ");
	    $this->visor->addComponent("ContentB", "content_body", $result);
	    unset($_SESSION['ARTICULOS']);
	    unset($_SESSION['TOTAL_ARTICULOS']);
	    unset($_SESSION["ARR_COMP"]);
	}
      break;
      case 'ESPECIALESDET':
       //echo "ENTRO 2";
        switch($this->action)
        {
          case 'setRegistro'://Codigo CIIU
            $result = FacturasTemplate::setRegistros($_REQUEST["codigo"], $_REQUEST["tdoc"]);
            //print_r($result);
            $this->visor->addComponent("desc_series_doc", "desc_series_doc", $result);
            //$this->visor->addComponent("Numero", "Numero", $result);
          break;
          case 'setRegistroFP'://Forma de Pago
            $result = FacturasTemplate::setRegistrosFormaPago($_REQUEST["codigofp"], $_REQUEST["fcred"]);
            $this->visor->addComponent("desc_forma_pago", "desc_forma_pago", $result);
          break;
          case 'setRegistroLPRE'://Lista de Precios
            $result = FacturasTemplate::setRegistrosListaPrecios($_REQUEST["codigolpre"]);
            $this->visor->addComponent("desc_lista_precios", "desc_lista_precios", $result);
          break;
          case 'setRegistroCli'://Distritos
            $result = FacturasTemplate::setRegistrosCliente($_REQUEST["codigocli"]);
            $this->visor->addComponent("desc_cliente", "desc_cliente", $result);
          break;
          case 'setRegistroDesc'://Rubros
            $result = FacturasTemplate::setRegistrosDesc($_REQUEST["codigodesc"]);
            $this->visor->addComponent("desc_descuento", "desc_descuento", $result);
          break;
          case 'setRegistroArt'://Cuentas de Bancos
            //echo "FIELDS-CONTROL : ".$_REQUEST["fields"]."\n";
            $result = FacturasTemplate::setRegistrosArticulos($_REQUEST["codigoart"], $_REQUEST["lprec"]);
            $this->visor->addComponent("desc_articulo[]", "desc_articulo[]", $result);
          break;

          case 'AgregaArticulo':
            //echo " CONTADOR4 : ".count($_SESSION["TOTAL_ARTICULOS"])."\n";
           
            $contador = $_SESSION["TOTAL_ARTICULOS"];
            $Datos['codigo']        = $_REQUEST['codigo'];
            $Datos['descripcion']   = $_REQUEST['descripcion'];
            $Datos['cantidad']      = $_REQUEST['cantidad'];
            $Datos['precio']        = ($_REQUEST['precio']==''?0.00:$_REQUEST['precio']);
            $Datos['neto']          = ($_REQUEST['neto']==''?0.00:$_REQUEST['neto']);
            $Datos['igv']           = ($_REQUEST['igv']==''?0.00:$_REQUEST['igv']);
            $Datos['dscto']         = ($_REQUEST['dscto']==''?0.00:$_REQUEST['dscto']);
            $Datos['total']         = ($_REQUEST['total']==''?0.00:$_REQUEST['total']);
            //$Datos['recargo'] = $_REQUEST['recargo'];
           // print_r($Datos);
            $lista = FacturasModel::AgregarArticulo($Datos,$contador);
            //print_r($TotalesLista);
            //$CUENTAS = $result;
            if($_REQUEST['dato_elimina'])
            {
                echo " registro_id : ".$_REQUEST['dato_elimina']."\n";
                if($_REQUEST['registroid']){
                    $art_codigo = $lista[$_REQUEST['dato_elimina']]['cod_articulo'];
                    $registroid = FacturasModel::GenerarRegitroID();
                    FacturasModel::eliminarArticuloDet($registroid,$art_codigo);
                    //unset($lista[$_REQUEST['dato_elimina']]);
                }
                //echo "CODIGO ARTICULO : ".$lista[$_REQUEST['dato_elimina']]['cod_articulo']."\n";
                unset($lista[trim($_REQUEST['dato_elimina'])]);
            }

            $_SESSION["ARTICULOS"] = $lista;
            //print_r($_SESSION['ARTICULOS']);
            $TotalesLista = FacturasModel::CalcularTotales($Datos);

            //$_SESSION["TOTALES"] = $Totaleslista;
            //print_r($_SESSION["ARTICULOS"]);
            if(!$_REQUEST['dato_elimina'])
            {
                if(count($_SESSION["ARTICULOS"]) > $_SESSION["TOTAL_ARTICULOS"] && $_SESSION["TOTAL_ARTICULOS"] != count($_SESSION["ARTICULOS"]))
                {
                    $_SESSION["TOTAL_ARTICULOS"] = count($_SESSION["ARTICULOS"]);
                    
                }elseif($_SESSION["TOTAL_ARTICULOS"] == count($_SESSION["ARTICULOS"]))
                {
                    $_SESSION["TOTAL_ARTICULOS"] = ($_SESSION["TOTAL_ARTICULOS"]+1);
                }else{
                    $_SESSION["TOTAL_ARTICULOS"] = ($_SESSION["TOTAL_ARTICULOS"]+1);
                }
            }else{
                $_SESSION["TOTAL_ARTICULOS"] = $_SESSION["TOTAL_ARTICULOS"]-1;
            }

            $TotalesLista['total_recargo'] = round($TotalesLista['total_neto_articulo'] - $TotalesLista['total_neto_articulo']/(1+$_REQUEST['recargo']/100),2);
            $TotalesLista["total_impuesto2"] = $_REQUEST["datos[nu_fac_impuesto2]"];
            $result = FacturasTemplate::addArticulos($lista);
          	$resultTotales = FacturasTemplate::addTotales($TotalesLista,$_REQUEST["datos"]);
           // }
            //echo "RESULT : $result  RESUL FIN \n";
            //echo "LLEGO VISOR\n";
            $this->visor->addComponent("datos_agregados", "datos_agregados", $result);
           // if ($_SESSION["TOTAL_ARTICULOS"]>0){
            $this->visor->addComponent("datos_agregados_totales", "datos_agregados_totales", $resultTotales);
           // }
          break;

          default:
            //listar ultimos movimientos
          break;
        }
      break;

      default:
        $this->visor->addComponent('ContentB', 'content_body', '<h2>TAREA "'.$this->request.'" NO CONOCIDA EN REGISTROS</h2>');
      break;
      }
    }
  }
