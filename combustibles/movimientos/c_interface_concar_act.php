<?php

// Jar para ejecutar inserts en SQL: /usr/local/lib/phpmssqlbridge.jar
// Se debe copiar de la ubicacion local al cliente
// cd /usr/local/etc/ copiar freetds.conf

class InterfaceConcarActController extends Controller {

	function Init() {
		$this->visor = new Visor();
		$this->task = @$_REQUEST["task"];
		$this->action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : '';
		$this->datos = isset($_REQUEST["datos"]) ? $_REQUEST["datos"] : '';
    }

    function Run() {
		include 'movimientos/t_interface_concar_act.php';
		include 'movimientos/m_interface_concar_act.php';

		// get class template y model and save in $obj
		$objModel = new InterfaceConcarActModel();
		$objTemplate = new InterfaceConcarActTemplate();

		$this->Init();

		$this->visor->addComponent('ContentT', 'content_title', $objTemplate->titulo());

		$result = '';
		
		//CONFIGURACION AUTOMATICA PARA MIGRAR INFORMACION CONCAR_CONFIG A CONCAR_CONFIGNEW
		//Obtenemos parametro de int_parametros
		$realizar_migracion_concar = $objModel->getMigracionConcar();
		
		if($realizar_migracion_concar == 1):
			//Eliminamos tabla concar_confignew
			$objModel->delete_concar_confignew();	

			//Creacion de tabla concar_confignew
			$res = $objModel->create_concar_confignew();
			if($res['sStatus'] == 'success'):
				echo "<script>alert('".$res['sMessage']."');</script>";			
			endif;
			
			/*
			concar_confignew Values
				module Values
				0 Global
				1 Ventas Combustible
				2 Ventas Market
				6 Ventas Manuales	
				3 Cta. Cobrar Combustible
				4 Cta. Cobrar Market
				5 Compras
				8 Liquidacion de Caja			
			*/		
							
			$objModel->insert_module_global();
			$objModel->insert_module_liquidacion_caja();
		endif;
		//CERRAR CONFIGURACION AUTOMATICA PARA MIGRAR INFORMACION CONCAR_CONFIG A CONCAR_CONFIGNEW	

		switch ($this->task) {
			case 'INTERFAZCONCARACT':
            	switch ($this->action) {
		           	case 'Procesar':
		           		$fechaini 		= @$_REQUEST['fechaini'];
		           		$fechafin 		= @$_REQUEST['fechafin'];
		           		$almacen  		= @$_REQUEST['datos']['sucursal'];
		           		$comboempresas 	= @$_REQUEST['datos']['empresa'];
		           		$num_actual 	= @$_REQUEST['num_actual'];
		           		$tipo			= @$_REQUEST['comboTipo'];						

		           		switch ($tipo) {
		           			case "1":
		           				$res = $objModel->interface_ventas_combustible($fechaini,$fechafin,$almacen,$comboempresas,$num_actual);
							break;
		           			case "2":
		           				$res = $objModel->interface_ventas_market($fechaini,$fechafin,$almacen,$comboempresas,$num_actual);
							break;
		           			case "3":
		           				$res = $objModel->interface_cobrar_combustible($fechaini,$fechafin,$almacen,$comboempresas,$num_actual);
							break;
		           			case "4":
		           				$res = $objModel->interface_cobrar_market($fechaini,$fechafin,$almacen,$comboempresas,$num_actual);
							break;
		           			case "5":
		           				$res = $objModel->interface_compras($fechaini,$fechafin,$almacen,$comboempresas,$num_actual);
							break;
		           			case "6":
		           				$res = $objModel->Ventas_docManual($fechaini,$fechafin,$almacen,$comboempresas,$num_actual);
							break;
		           			case "7":
		           				$res = $objModel->Cobrar_docManual($fechaini,$fechafin,$almacen,$comboempresas,$num_actual);
							break;   
							case "8":
								$res = $objModel->interface_liquidacion_caja($fechaini,$fechafin,$almacen,$comboempresas,$num_actual);
						 	break;              			
		           			default:
		           			break;                   						
		           		}

						if ($res == 1) {
							$hora = date("d/m/Y H:m.s");
							echo "<script>alert('Se ingreso la informacion ".$hora." - ".$res."');</script>";
						} elseif ($res == 0) {
							?><script>alert("<?php echo 'No hay informacion.'; ?> ");</script><?php
						} elseif ($res == 3) {
							?><script>alert("<?php echo 'Anio y mes deben ser iguales en ambas fechas.'; ?> ");</script><?php
						} elseif ($res == 4) {
							?><script>alert("<?php echo 'Fecha inicial debe ser menor o igual a fecha final.'; ?> ");</script><?php
						} elseif ($res == 2) {
							?><script>alert("<?php echo 'Fecha invalida.'; ?> ");</script><?php
						} elseif ($res == 5) {
							?><script>alert("<?php echo 'El rango de fechas coincide con una migracion anterior.'; ?> ");</script><?php
						} else {
							?><script>alert("<?php echo 'Error en ingreso de informacion.'; ?> ");</script><?php
						}
						
						$CbSucursales	= $objModel->obtenerAlmacenes("");
						$Empresa		= $objModel->Empresa();
						$result			= $objTemplate->formInterfaceConcarAct(Array(), $CbSucursales ,$limitstart = null, $Empresa, $almacen);
						$this->visor->addComponent("ContentB", "content_body", $result);
					break;// /. Procesar

			    	default:
						$CbSucursales	= $objModel->obtenerAlmacenes("");
						$Empresa 		= $objModel->Empresa();
						echo "<pre>";
						var_dump($Empresa);
						echo "</pre>";
						$result 		= $objTemplate->formInterfaceConcarAct(Array(), $CbSucursales, $limitstart = null, $Empresa, $_SESSION['almacen']);
						$this->visor->addComponent("ContentB", "content_body", $result);
					break;// /. Default
				}
			break;
        
			default:
				$this->visor->addComponent('ContentB', 'content_body', '<h2>TAREA "' . $this->request . '" NO CONOCIDA EN INTERFACE CONCAR</h2>');
			break;

			/*
			case 'INTERFAZCONCARACT':
            	switch ($this->action) {
					case 'Buscar':
						$almacen		= $_REQUEST['almacen'];
						$CbSucursales	= $objModel->obtenerAlmacenes($almacen);
	          			$Empresa		= $objModel->Empresa();				
						$result			= $objTemplate->formInterfaceConcarAct(Array(), $CbSucursales, $limitstart = null, $Empresa, $almacen);

						$this->visor->addComponent("ContentB", "content_body", $result);
						break;
		        
		           	case 'Procesar':
		           		$fechaini 		= @$_REQUEST['fechaini'];
		           		$fechafin 		= @$_REQUEST['fechafin'];
		           		$almacen  		= @$_REQUEST['datos']['sucursal'];
		           		$comboempresas 	= @$_REQUEST['datos']['empresa'];
		           		$num_actual 	= @$_REQUEST['num_actual'];
		           		$tipo			= @$_REQUEST['comboTipo'];

		           		switch ($tipo) {
		           			case "1":
		           				$res = $objModel->interface_ventas_combustible($fechaini,$fechafin,$almacen,$comboempresas,$num_actual);
							break;
		           			case "2":
		           				$res = $objModel->interface_ventas_market($fechaini,$fechafin,$almacen,$comboempresas,$num_actual);
							break;
		           			case "3":
		           				$res = $objModel->interface_cobrar_combustible($fechaini,$fechafin,$almacen,$comboempresas,$num_actual);
							break;
		           			case "4":
		           				$res = $objModel->interface_cobrar_market($fechaini,$fechafin,$almacen,$comboempresas,$num_actual);
							break;
		           			case "5":
		           				$res = $objModel->interface_compras($fechaini,$fechafin,$almacen,$comboempresas,$num_actual);
							break;
		           			case "6":
		           				$res = $objModel->Ventas_docManual($fechaini,$fechafin,$almacen,$comboempresas,$num_actual);
							break;
		           			case "7":
		           				$res = $objModel->Cobrar_docManual($fechaini,$fechafin,$almacen,$comboempresas,$num_actual);
							break;                 			
		           			default:
		           			break;                   						
		           		}

						//trigger_error("ENTRO CABECERA ");
						if ($res == 1) {
							$hora = date("d/m/Y H:m.s");
							echo "<script>alert('Se ingreso la informacion ".$hora." - ".$res."');</script>";
						} elseif ($res == 0) {
							?><script>alert("<?php echo 'No hay informacion.'; ?> ");</script><?php;
						} elseif ($res == 3) {
							?><script>alert("<?php echo 'Anio y mes deben ser iguales en ambas fechas.'; ?> ");</script><?php;
						} elseif ($res == 4) {
							?><script>alert("<?php echo 'Fecha inicial debe ser menor o igual a fecha final.'; ?> ");</script><?php;
						} elseif ($res == 2) {
							?><script>alert("<?php echo 'Fecha invalida.'; ?> ");</script><?php;
						} elseif ($res == 5) {
							?><script>alert("<?php echo 'El rango de fechas coincide con una migracion anterior.'; ?> ");</script><?php;
						} else {
							?><script>alert("<?php echo 'Error en ingreso de informacion.'; ?> ");</script><?php;
						}
						
						$CbSucursales	= $objModel->obtenerAlmacenes("");
						$Empresa	= $objModel->Empresa($almacen);
						$result		= $objTemplate->formInterfaceConcarAct(Array(), $CbSucursales ,$limitstart = null, $Empresa, $almacen);
						$this->visor->addComponent("ContentB", "content_body", $result);
						break;
			                
			    	default:
						$CbSucursales	= $objModel->obtenerAlmacenes("");
						$Empresa	= $objModel->Empresa($_SESSION['almacen']);
						$result		= $objTemplate->formInterfaceConcarAct(Array(), $CbSucursales, $limitstart = null, $Empresa, $_SESSION['almacen']);
						$this->visor->addComponent("ContentB", "content_body", $result);
						break;
		       	}// /. switch action
			break;
        
			default:
				$this->visor->addComponent('ContentB', 'content_body', '<h2>TAREA "' . $this->request . '" NO CONOCIDA EN INTERFACE CONCAR</h2>');
			break;
			*/
		}// /. switch task
	}
}