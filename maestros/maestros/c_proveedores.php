<?php
  // Controlador del Modulo Generales

  Class ProveedoresController extends Controller{
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
      include('maestros/m_proveedores.php');
      include('maestros/t_proveedores.php');
      include('../include/paginador_new.php');
      $this->visor->addComponent('ContentT', 'content_title', ProveedoresTemplate::titulo());
      if(!$_REQUEST['rxp'] && !$_REQUEST['pagina'])
      {
         $_REQUEST['rxp'] = 100;
         $_REQUEST['pagina'] = 0;
      }
      switch ($this->request)
      {//task
      case 'PROVEEDOR':
      //echo "ENTRO";
	$tablaNombre = 'PROVEEDORES';
	$listado = false;
	//evaluar y ejecutar $action
	switch ($this->action)
	{
	    
	    case 'Agregar':
	    $result = ProveedoresTemplate::formProveedores(array(), false);
	    $this->visor->addComponent("ContentB", "content_body", $result);
	    break;
    
	    case 'Eliminar':
	    $result = ProveedoresModel::eliminarRegistro($_REQUEST["registroid"]);
	    if ($result == OK){
		$listado= true;
	    } else {
        $listado= true;
        $error = true;
        $msgError="El proveedor tiene movimientos, verificar los registros en Movimiento de Inventario";
		//$result = ProveedoresTemplate::errorResultado($result);
		//$this->visor->addComponent("ContentB", "content_body", $result);
	    }
	    break;
    
	    case 'Modificar':
	    $record = ProveedoresModel::recuperarRegistroArray($_REQUEST["registroid"]);
	    $registrosXml = ProveedoresModel::recuperarRegistrosXml($_REQUEST["registroid"]);
	    //print_r($registrosXml);
      $sAction='Modificar';
	    $result = ProveedoresTemplate::formProveedores($record,$registrosXml,$sAction);
	    $this->visor->addComponent("ContentB", "content_body", $result);
	    break;
    
	    case 'Guardar':
        $result = ProveedoresModel::guardarRegistro($this->datos, $_SESSION['CUENTAS']);
        //echo "RESULT : $result \n";
        if ($result == OK){
          $listado = true;
          unset($_SESSION['CUENTAS']);
          unset($_SESSION['TOTAL_CUENTAS']);
        } else {
          $result = ProveedoresTemplate::errorResultado($result);
          $this->visor->addComponent("error", "error_body", $result);
        }
	    break;

      case "update":
          $arrResponseModal = ProveedoresModel::actualizarProveedor($_POST);
      break;
    
	    case 'Buscar':
	    //Listo
	    $busqueda = ProveedoresModel::tmListado($_REQUEST["busqueda"],$_REQUEST['rxp'],$_REQUEST['pagina']);
	    $result = ProveedoresTemplate::listado($busqueda['datos']);
	    
	    $this->visor->addComponent("ListadoB", "resultados_grid", $result);
	    break;
    
	    default:
	    //listado
	    $listado = true;
	    unset($_SESSION['CUENTAS']);
	    unset($_SESSION['TOTAL_CUENTAS']);
	    //$this->visor->addComponent("ContentT","content_title",TarjetasMagneticasTemplate::titulo());
	    break;
	}
	if ($listado) 
	{
	    $listado    = ProveedoresModel::tmListado('',$_REQUEST['rxp'],$_REQUEST['pagina']);
	    $result     =  ProveedoresTemplate::formBuscar($listado['paginacion']);
	    $result     .= ProveedoresTemplate::listado($listado['datos']);

      if ($error)
        $result .= ProveedoresTemplate::errorResultado($msgError);

      $this->visor->addComponent("ContentB", "content_body", $result);
	}
      break;
      case 'PROVEEDORDET':
       //echo "ENTRO 2";
        switch($this->action)
        {
          case 'setRegistro'://Codigo CIIU
            $result = ProveedoresTemplate::setRegistros($_REQUEST["codigo"]);
            $this->visor->addComponent("desc_ciiu", "desc_ciiu", $result);
          break;
          case 'setRegistroFP'://Forma de Pago
            $result = ProveedoresTemplate::setRegistrosFormaPago($_REQUEST["codigofp"]);
            $this->visor->addComponent("desc_forma_pago", "desc_forma_pago", $result);
          break;
          case 'setRegistroDist'://Distritos
            $result = ProveedoresTemplate::setRegistrosDistrito($_REQUEST["codigodist"]);
            $this->visor->addComponent("desc_distrito", "desc_distrito", $result);
          break;
          case 'setRegistroRub'://Rubros
            $result = ProveedoresTemplate::setRegistrosRubro($_REQUEST["codigorub"]);
            $this->visor->addComponent("desc_rubro", "desc_rubro", $result);
          break;
          case 'setRegistroCodCta'://Cuentas de Bancos
            //echo "FIELDS-CONTROL : ".$_REQUEST["fields"]."\n";
            $result = ProveedoresTemplate::setRegistrosCuentas($_REQUEST["codigocta"]);
            $this->visor->addComponent("desc_cta[]", "desc_cta[]", $result);
          break;
          
          case 'setRegistroTipoCtaBan'://Cuentas de Bancos
            //echo "FIELDS-CONTROL : ".$_REQUEST["fields"]."\n";
            $result = ProveedoresTemplate::setRegistrosTipoCtaBan($_REQUEST["codigotipoctaban"]);
            $this->visor->addComponent("desc_tipoctaban[]", "desc_tipoctaban[]", $result);
          break;
          
          case 'AgregaCuenta':
            //echo " CONTADOR4 : ".$_SESSION["TOTAL_CUENTAS"]."\n";
            $contador = $_SESSION["TOTAL_CUENTAS"];
            $lista = ProveedoresModel::AgregarCuenta($_REQUEST['valor'], $_REQUEST['valor2'], $_REQUEST['valor3'], $_REQUEST['valor4'], $_REQUEST['valor5'],$contador);
            //$CUENTAS = $result;
            if($_REQUEST['dato_elimina'])
            {
                //echo " registro_id : ".$_REQUEST['registro_id']."\n";
                if($_REQUEST['registro_id']){
                    ProveedoresModel::eliminarRegistroXml($_REQUEST['registro_id'],$_REQUEST['dato_elimina']);
                }
                unset($lista[$_REQUEST['dato_elimina']]);
            }

            $_SESSION["CUENTAS"] = $lista;
            //print_r($_SESSION["CUENTAS"]);
            if(!$_REQUEST['dato_elimina'])
            {
                if(count($_SESSION["CUENTAS"]) > $_SESSION["TOTAL_CUENTAS"] && $_SESSION["TOTAL_CUENTAS"] != count($_SESSION["CUENTAS"]))
                {
                    $_SESSION["TOTAL_CUENTAS"] = count($_SESSION["CUENTAS"]);
                    
                }elseif($_SESSION["TOTAL_CUENTAS"] == count($_SESSION["CUENTAS"]))
                {
                    $_SESSION["TOTAL_CUENTAS"] = ($_SESSION["TOTAL_CUENTAS"]+1);
                }else{
                    $_SESSION["TOTAL_CUENTAS"] = ($_SESSION["TOTAL_CUENTAS"]+1);
                }
            }else{
                $_SESSION["TOTAL_CUENTAS"] = $_SESSION["TOTAL_CUENTAS"];
            }
            $result = ProveedoresTemplate::addCuentasBancarias($lista);
            //echo "RESULT : $result  RESUL FIN \n";
            $this->visor->addComponent("datos_agregados", "datos_agregados", $result);
          break;
          
	  case 'ValidarCodigo':
	    $result = ProveedoresModel::validarCodigo($_REQUEST["Codigo"]);
	    $this->visor->addComponent("MensajeValidacion", "MensajeValidacion", $result);
	  break;
	  case 'ValidarRuc':
	    $result = ProveedoresModel::validarRuc($_REQUEST["CodigoRuc"]);
	    $this->visor->addComponent("MensajeValidacionRuc", "MensajeValidacionRuc", $result);
	  break;

          default:
            //listar ultimos movimientos
          break;
        }
      break;


        case 'SUNAT'://Consulta de RUC SUNAT - services opensysperu
          switch($this->action) {
            case 'get_data_sunat':
              $cifkey = ProveedoresModel::CIFKey();
              if($cifkey === NULL) {
                //echo "<script>alert('La estacion no esta integrada con servidor CIF OCS');</script>\n";
                echo json_encode(array("operation"=>6,"message"=>"La estacion no esta integrada con servidor CIF OCS"));
                exit();
              }

              $this->ajax_validation_sunat($cifkey, $_POST);
              exit();
            break;
          }
        break;
        
      default:
        $this->visor->addComponent('ContentB', 'content_body', '<h2>TAREA "'.$this->request.'" NO CONOCIDA EN REGISTROS</h2>');
      break;
      }
    }

  function ajax_validation_sunat($key, $arrDataPOST){
    $tmp=$this->valruc_sunat(array("key" => $key, "taxid"=>$arrDataPOST["iTaxID"]));
    
    if($tmp["operation"]==1)
      echo json_encode($tmp);
      
    else if($tmp["operation"]==2)
      echo json_encode(array("operation"=>$tmp["operation"],"message"=>"Ha Ocurrido Un Error Con Servidor"));
    
    else if($tmp["operation"]==3)
      echo json_encode(array("operation"=>$tmp["operation"],"message"=>"RUC Invalido Por Sunat"));

    else if($tmp["operation"]==4)
      echo json_encode(array("operation"=>$tmp["operation"],"message"=>"Ruc En Cola Espere 15 Minutos"));

    else if($tmp["operation"]==5)
      echo json_encode(array("operation"=>$tmp["operation"],"message"=>"RUC Invalido"));
  }

    function valruc_sunat($data){
        $ch=curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://services.opensysperu.com/tid/pe/ruc/'.$data["key"].'/'.$data["taxid"]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $content=curl_exec($ch);
        $status=curl_getinfo($ch, CURLINFO_HTTP_CODE); 
        curl_close($ch);
        if($status=="200"){
            return $this->regular_expresion(array("content"=>$content,"taxid"=>$data["taxid"]));
        }
        return array("operation"=>2);
    }

    function regular_expresion($data){
        $tmp            =array();
        $pattern_queued ="/QUEUED/";
        $pattern_invalid="/INVALID/";
        
        if(preg_match($pattern_queued, $data["content"], $out_tmp))
            return array("operation"=>4);
        else if(preg_match($pattern_invalid, $data["content"], $out_tmp))
            return array("operation"=>5);
        
        foreach (
            array(
                "name"=>"/NAME\:([^\n]*)/",
                "streetName"=>"/FIELD\:streetName\:([^\n]*)/",
                "zone"=>"/FIELD\:zone\:([^\n]*)/",
                "location"=>"/FIELD\:location\:([^\n]*)/"
            ) as $key => $value) {
            if(preg_match($value, $data["content"], $out_tmp))
                $tmp[$key] = strip_tags($out_tmp[1]);
        }

        $tmp["operation"]=1;
        return $tmp;
    }
  }// /. Class