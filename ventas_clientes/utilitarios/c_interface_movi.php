<?php
  // Controlador del Modulo Generales

Class InterfaceMovController extends Controller{
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
      include('utilitarios/m_interface_movi.php');
      include('utilitarios/t_interface_movi.php');
      include('../include/m_sisvarios.php');
      $this->visor->addComponent('ContentT', 'content_title', InterfaceMovTemplate::titulo());

	switch ($this->request)
	{//task
	case 'INTERFACES':
		//$listado = false;
		$CbSucursales = VariosModel::sucursalCBArray();
		//print_r($CbSucursales);
		switch ($this->action)
		{
			case 'Actualizar':
			{
				
				//echo "SUCURSAL : ".$_REQUEST['datos']['sucursal']." \n";

			if($_REQUEST['datos']['modulos']=='VALES')
			{
				if($_REQUEST['datos']['sucursal']!='all')
				{
					$RegAlmac = InterfaceMovModel::ListadoAlmacenes($_REQUEST['datos']['sucursal']);
					ksort($RegAlmac);
					foreach($RegAlmac as $llave => $Codigos)
					{
						$Funcion = InterfaceMovModel::ActualizarInterfaces_vales($_REQUEST['datos']['fechaini'],$_REQUEST['datos']['fechafin'], $Codigos,$_REQUEST['datos']['modulos']);
						$Resultados[$llave] = $Funcion;
					}
				}
				else
				{
					
					ksort($CbSucursales);
					foreach($CbSucursales as $CodAlm => $DescripAlm)
					{
						if($CodAlm != "all")
						{
							$RegAlmac = InterfaceMovModel::ListadoAlmacenes($CodAlm);
							foreach($RegAlmac as $llave => $Codigos)
							{
								$Funcion = InterfaceMovModel::ActualizarInterfaces_vales($_REQUEST['datos']['fechaini'],$_REQUEST['datos']['fechafin'], $Codigos,$_REQUEST['datos']['modulos']);
								
								if($Funcion)
								

								
								$Resultados[$llave] = $Funcion;
							}
						}
					}
				}
			}
			else
			{				
			

			
				if($_REQUEST['datos']['sucursal']!='all')
				{
					$RegAlmac = InterfaceMovModel::ListadoAlmacenes($_REQUEST['datos']['sucursal']);
			
					ksort($RegAlmac);
					foreach($RegAlmac as $llave => $Codigos)
					{
/*jch
						$Funcion = InterfaceMovModel::ActualizarInterfaces($_REQUEST['datos']['fecha'], $Codigos,$_REQUEST['datos']['modulos']);
*/					
					IF ($_REQUEST['datos']['modulos']=="FACTURACION")
					{
					
						$anomesdia = '01'."/".$_REQUEST['datos']['mes']."/".date('Y');	
					}else
					{
						$anomesdia=$_REQUEST['datos']['fechaini'];
					}

					echo $anomesdia;
					echo $Codigos;
					
						
						$Funcion = InterfaceMovModel::ActualizarInterfaces($anomesdia,$Codigos,$_REQUEST['datos']['modulos']);
						//$Funcion = InterfaceMovModel::ActualizarInterfaces($_REQUEST['datos']['fechaini'], $Codigos,$_REQUEST['datos']['modulos']);
														

						$Resultados[$llave] = $Funcion;
					}
				}
				else
				{
					ksort($CbSucursales);
					
				
					
					
					foreach($CbSucursales as $CodAlm => $DescripAlm)
					{
						if($CodAlm != "all")
						{
							$RegAlmac = InterfaceMovModel::ListadoAlmacenes($CodAlm);
							foreach($RegAlmac as $llave => $Codigos)
							{
								$Funcion = InterfaceMovModel::ActualizarInterfaces($_REQUEST['datos']['fechaini'], $Codigos,$_REQUEST['datos']['modulos']);
								if($Funcion)
								$Resultados[$llave] = $Funcion;
							}
						}
					}
				}
			}
			
			}
			break;
		
			default:
			   $result = InterfaceMovTemplate::formInterfaceMov();
			   $this->visor->addComponent("ContentB", "content_body", $result);
			   //$listado = true;
			break;
			} //case actualizar

		break;
		case 'SUNATDET':
		//Si hay detalles
		break;
		
		default:
		$this->visor->addComponent('ContentB', 'content_body', '<h2>TAREA "'.$this->request.'" NO CONOCIDA EN REGISTROS</h2>');
		break;
		}   // case INTERFACES
	}
}
