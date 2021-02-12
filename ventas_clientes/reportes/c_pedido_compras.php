<?php

class PedidoComprasController extends Controller {
	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action']) ? $this->action = $_REQUEST['action']:$this->action='';
	}

	function Run() {
	    ob_start();
		include 'reportes/m_pedido_compras.php';
		include 'reportes/t_pedido_compras.php';
	
		$this->Init();
		$result = '';
		$result_f = '';
		$search_form = false;

		if(getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"),"unknown"))
			$ip = getenv("HTTP_CLIENT_IP");
		else if(getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
			$ip = getenv("HTTP_X_FORWARDED_FOR");
		else if(getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
			$ip = getenv("REMOTE_ADDR");
		else if(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
			$ip = $_SERVER['REMOTE_ADDR'];

		switch ($this->action) { 
			case "Buscar":
				$busqueda = PedidoComprasModel::buscar($_REQUEST['almacen'],$_REQUEST['desde'],$_REQUEST['hasta']);
				$result_f = PedidoComprasTemplate::reporte($busqueda);
				$this->visor->addComponent("ContentF", "content_footer", $result_f);
				break;

			case "Agregar":
				$result	= PedidoComprasTemplate::formAgregarHeader();
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", "<div id='listado-pedido'></div>");
				break;

			case "ListarPedidoWithDataTables":
				$resultado = PedidoComprasModel::listar(
					$_POST['cab_almacen'],
					$_POST['cab_nropedido'],
					$_POST['sCodLinea'],
					$_POST['sCodProveedor'], 
					$_POST['cab_tipo'],
					$_POST['fecha'],
					$_POST['cab_observacion']
				);						

				$i = 0;
				foreach ($resultado['detalle'] as $key=>$fila) {
					$art_codigo      = $fila['art_codigo'];
					$art_descripcion = $fila['art_descripcion'];
					$mes_3           = $fila['mes_3'];
					$mes_2           = $fila['mes_2'];
					$mes_1           = $fila['mes_1'];
					$stk_actual      = $fila['stk_actual'];
					$stk_minimo      = $fila['stk_minimo'];
					$stk_maximo      = $fila['stk_maximo'];
					$cantidad        = $fila['cantidad'];
					$dias_repo       = $fila['dias_repo'];
					$sugerido        = $fila['sugerido'];

					if($_POST['modo'] == "C"){
						if($mes_3 > 0  || $mes_2 > 0 || $mes_1 > 0){							
							$onekeyup = "javascript:actPedido(this.value, '0.0000', 'pedido[" . $i . "]')";

							$listJson[] = array(
								"0" => '<input type="checkbox" name="vec_check['.$i.']" class="product-check" value="S" checked="">',
								"1" => '<span style="display:none">'.$art_codigo.'</span> 
										<input type="text" name="codigo['.$i.']" class="product-code" id="codigo['.$i.']" value="'.$art_codigo.'" size="18" maxlength="18" readonly="">',
								"2" => $art_descripcion,
								"3" => $mes_1,
								"4" => $mes_2,
								"5" => $mes_3,
								"6" => $stk_actual,
								"7" => '<input type="text" name="stk_minimo['.$i.']" class="stk-min" id="stk_minimo['.$i.']" value="'.$stk_minimo.'" onkeyup="'.$onekeyup.'" size="11" maxlength="11">',
								"8" => '<input type="text" name="stk_maximo['.$i.']" class="stk-max" id="stk_maximo['.$i.']" value="'.$stk_maximo.'" onkeyup="'.$onekeyup.'" size="11" maxlength="11">',
								"9" => '<input type="text" name="pedido['.$i.']" class="qty" id="pedido['.$i.']" value="'.$cantidad.'" size="15" maxlength="15">',
								"10" => $sugerido,
								"11" => ''
							);

							$i++;
						}
					}else{			
						$onekeyup = "javascript:actPedido(this.value, '0.0000', 'pedido[" . $i . "]')";
						
						$listJson[] = array(
							"0" => '<input type="checkbox" name="vec_check['.$i.']" class="product-check" value="S" checked="">',
							"1" => '<span style="display:none">'.$art_codigo.'</span> 
									<input type="text" name="codigo['.$i.']" class="product-code" id="codigo['.$i.']" value="'.$art_codigo.'" size="18" maxlength="18" readonly="">',
							"2" => $art_descripcion,
							"3" => $mes_1,
							"4" => $mes_2,
							"5" => $mes_3,
							"6" => $stk_actual,
							"7" => '<input type="text" name="stk_minimo['.$i.']" class="stk-min" id="stk_minimo['.$i.']" value="'.$stk_minimo.'" onkeyup="'.$onekeyup.'" size="11" maxlength="11">',
							"8" => '<input type="text" name="stk_maximo['.$i.']" class="stk-max" id="stk_maximo['.$i.']" value="'.$stk_maximo.'" onkeyup="'.$onekeyup.'" size="11" maxlength="11">',
							"9" => '<input type="text" name="pedido['.$i.']" class="qty" id="pedido['.$i.']" value="'.$cantidad.'" size="15" maxlength="15">',
							"10" => $sugerido,
							"11" => ''
						);	
						
						$i++;
					}					
				}				

				$json = array(
					"draw"            => 1,
					"recordsTotal"    => count($listJson),
					"recordsFiltered" => count($listJson),
					"data"            => $listJson
				);					 
				echo json_encode($json);
				break;
				
			case 'GuardarPedido':
				$res = array();
				if (isset($_POST['cab_almacen'], $_POST['sCodLinea'], $_POST['sCodProveedor'], $_POST['cab_nropedido'], $_POST['cab_tipo'], $_POST['cab_observacion'])) {
					$almacen = $_POST['cab_almacen'];
					$sCodLinea = $_POST['sCodLinea'];
					$sCodProveedor = $_POST['sCodProveedor'];
					$nropedido = $_POST['cab_nropedido'];
					$tipo = $_POST['cab_tipo'];
					$observacion = $_POST['cab_observacion'];
					$usuario = $_SESSION['auth_usuario'];

					$data = json_decode($_POST['data']);
					
					/*Verificamos data enviada*/
					// echo "<pre>";
					// print_r($data);
					// echo "<pre>";					
					/*Cerrar verificamos data enviada*/

					$products = array();
					foreach ($data->product_check as $key => $value) {
						$products[] = array(
							'product_check' => $data->product_check[$key],
							'product_code' => $data->product_code[$key],
							'stk_min' => $data->stk_min[$key],
							'stk_max' => $data->stk_max[$key],
							'qty' => $data->qty[$key],
						);
					}

					/*Verificamos data enviada*/
					// echo "<pre>";
					// print_r($products);
					// echo "<pre>";					
					/*Cerrar verificamos data enviada*/

					$r = PedidoComprasModel::insertarPedido(
						$almacen,
						$sCodLinea,
						$sCodProveedor,
						$nropedido,
						$tipo,
						$observacion,
						$usuario,
						$data->product_check,
						$data->product_code,
						$data->stk_min,
						$data->stk_max,
						$data->qty,
						$ip
					);

					if ($r == 1) {
						$res = array(
							'error' => false,
							'message' => 'Datos guardados correctamente.',
						);
					} else {
						error_log($r);
						$res = array(
							'error' => true,
							'message' => 'Error al guardar datos.',
						);
					}
				} else {
					$res = array(
						'error' => true,
						'message' => 'Error, datos incompletos',
					);
				}
				unset($products);
				echo json_encode($res);
				break;

			case "PDF":
				$resultado = PedidoComprasModel::buscarPDF($_REQUEST['id_cab']);
				$res = PedidoComprasTemplate::reportePDF($resultado); 
				$mi_pdf = "/sistemaweb/ventas_clientes/reportes/pdf/PedidoDeCompra.pdf";
				header('Content-type: application/pdf');
				header('Content-Disposition: attachment; filename="'."PedidoDeCompra.pdf".'"');
				readfile($mi_pdf);
				break;

			case "EXCEL":
				$arrResponseModelPedidoCompra = PedidoComprasModel::buscarPDF($_REQUEST['id_cab']);
				$res = PedidoComprasTemplate::gridViewEXCEL($arrResponseModelPedidoCompra);
				break;

			case 'Eliminar':				
				$arrResponseModelPedidoCompra = PedidoComprasModel::eliminarPedido($_REQUEST['id_cab']);												
				echo "<script>alert('Pedido eliminado correctamente')</script>";
				echo "<script>location.href='vta_pedido_compras.php'</script>";				
				break;

			case "EditarPedidoWithDataTables":
				$resultado = PedidoComprasModel::buscarDetalles($_POST['num_pedido'], $_POST['almacen'], $_POST['fecha'], $_POST['tipo'], $_POST['observacion']);				
				
				$i = 0;
				foreach ($resultado['detalle'] as $key=>$fila) {
					$num_cabecera    = $fila['num_cabecera'];
					$num_detalle     = $fila['num_detalle'];

					$art_codigo      = $fila['art_codigo'];
					$art_descripcion = $fila['art_descripcion'];
					$mes_3           = $fila['mes_3'];
					$mes_2           = $fila['mes_2'];
					$mes_1           = $fila['mes_1'];
					$stk_actual      = $fila['stk_actual'];
					$stk_minimo      = $fila['stk_minimo'];
					$stk_maximo      = $fila['stk_maximo'];
					$cantidad        = $fila['cantidad'];
					$dias_repo       = $fila['dias_repo'];
					$sugerido        = $fila['sugerido'];
					
					$onekeyup = "javascript:actPedido(this.value, '0.0000', 'pedido[" . $i . "]')";

					$listJson[] = array(
						"0" => '<input type="hidden" name="id_det['.$i.']" class="id_det" value="'.$num_detalle.'">
								<input type="checkbox" name="vec_check['.$i.']" class="product-check" value="S" checked="">',
						"1" => '<span style="display:none">'.$art_codigo.'</span> 
								<input type="text" name="codigo['.$i.']" class="product-code" id="codigo['.$i.']" value="'.$art_codigo.'" size="18" maxlength="18" readonly="">',
						"2" => $art_descripcion,
						"3" => $mes_1,
						"4" => $mes_2,
						"5" => $mes_3,
						"6" => $stk_actual,
						"7" => '<input type="text" name="stk_minimo['.$i.']" class="stk-min" id="stk_minimo['.$i.']" value="'.$stk_minimo.'" onkeyup="'.$onekeyup.'" size="11" maxlength="11">',
						"8" => '<input type="text" name="stk_maximo['.$i.']" class="stk-max" id="stk_maximo['.$i.']" value="'.$stk_maximo.'" onkeyup="'.$onekeyup.'" size="11" maxlength="11">',
						"9" => '<input type="text" name="pedido['.$i.']" class="qty" id="pedido['.$i.']" value="'.$cantidad.'" size="15" maxlength="15">',
						"10" => $sugerido,
						"11" => ''
					);	
					
					$i++;
				}				

				$json = array(
					"draw"            => 1,
					"recordsTotal"    => count($listJson),
					"recordsFiltered" => count($listJson),
					"data"            => $listJson
				);					 
				echo json_encode($json);
				break;								

			case 'ModificarPedido':
				$res = array();
				if (isset($_POST['cab_almacen'], $_POST['cab_nropedido'], $_POST['cab_tipo'], $_POST['cab_observacion'])) {
					$data = json_decode($_POST['data']);
					
					$almacen = $_POST['cab_almacen'];
					$usuario = $_SESSION['auth_usuario'];
					$vec_check = $data->vec_check;
					$id_det = $data->id_det;
					$codigo = $data->codigo;
					$stk_minimo = $data->stk_minimo;
					$stk_maximo = $data->stk_maximo;
					$cantidad = $data->pedido;
					$observacion = $_POST['cab_observacion'];					

					$r = PedidoComprasModel::modificarPedido($almacen, $codigo, $vec_check, $id_det, $usuario, $stk_minimo, $stk_maximo, $cantidad, $ip, $observacion);	
				
					if ($r == 1) {
						$res = array(
							'error' => false,
							'message' => 'Datos modificados correctamente.',
						);
					} else {
						error_log($r);
						$res = array(
							'error' => true,
							'message' => 'Error al guardar datos.',
						);
					}
				} else {
					$res = array(
						'error' => true,
						'message' => 'Error, datos incompletos',
					);
				}
				echo json_encode($res);
				break;	

			case 'ModificarViejo':
				$almacen = @$_REQUEST['cab_almacen'];
				$usuario = $_SESSION['auth_usuario'];
				$vec_check = @$_REQUEST['vec_check']; //vec
				$id_det = @$_REQUEST['id_det']; 
				$codigo = @$_REQUEST['codigo']; 
				$stk_minimo = @$_REQUEST['stk_minimo'];
				$stk_maximo = @$_REQUEST['stk_maximo'];
				$cantidad = @$_REQUEST['pedido']; // end vec
				$observacion = @$_REQUEST['observacion'];

				$res = PedidoComprasModel::modificarPedido($almacen, $codigo, $vec_check, $id_det, $usuario, $stk_minimo, $stk_maximo, $cantidad, $ip, $observacion);	
				
				if($res == 1) 
					echo '<script name="accion">alert("Se modificaron los datos.") </script>';
				else 
					echo '<script name="accion">alert("Error al modificar datos.") </script>';

				$result = PedidoComprasTemplate::searchForm();
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", "");
				break;						

			case "ListarViejo":
				$resultado = PedidoComprasModel::listar(
					$_REQUEST['almacen2'],
					$_REQUEST['nropedido'],
					$_REQUEST['nom_linea2'],
					$_REQUEST['nom_proveedor2'], 
					$_REQUEST['tipopedido'],
					$_REQUEST['fecha'],
					$_REQUEST['observacion']
				);
				echo "<script>console.log('" . json_encode($_REQUEST) . "')</script>";

				if($resultado == 0){ 
					echo '<script name="accion">alert("No hay información.'.$resultado.'") </script>';
					break;
				} else {
					$result_f 	= PedidoComprasTemplate::formAgregarBody($resultado, "A", $_REQUEST['modo']);
					$this->visor->addComponent("ContentF", "content_footer", $result_f);
					break;
				}
				break;
			
			case 'InsertarViejo':
				$almacen 		= @$_REQUEST['cab_almacen'];
				$sCodLinea 		= @$_REQUEST['sCodLinea'];
				$sCodProveedor = @$_REQUEST['sCodProveedor'];
				$nropedido 		= @$_REQUEST['cab_nropedido'];
				$tipo 			= @$_REQUEST['cab_tipo'];
				$observacion 	= @$_REQUEST['cab_observacion'];
				$usuario 		= $_SESSION['auth_usuario'];
				
				$vec_check 		= @$_REQUEST['vec_check']; 
				$codigo 			= @$_REQUEST['codigo'];
				$stk_minimo = @$_REQUEST['stk_minimo'];
				$stk_maximo = @$_REQUEST['stk_maximo'];
				$cantidad = @$_REQUEST['pedido'];

				$codigox = @$_REQUEST['codigox'];
				$descripcionx = @$_REQUEST['descripcionx'];
				$mes1x = @$_REQUEST['mes1x'];
				$mes2x = @$_REQUEST['mes2x'];
				$mes3x = @$_REQUEST['mes3x'];
				$stk_actualx = @$_REQUEST['stk_actualx'];
				$stk_minimox = @$_REQUEST['stk_minimox'];
				$stk_maximox = @$_REQUEST['stk_maximox'];
				$cantidadx = @$_REQUEST['pedidox'];

				settype($stk_minimox, "int");
				settype($stk_maximox, "int");
				settype($cantidadx, "int");

				if ($stk_minimox <= 0 || $stk_maximox <= 0 || $cantidadx <= 0) {
					?><script>alert("<?php echo 'El valor debe de ser mayor a 0' ; ?> ");</script><?php
				} else {
					$res = PedidoComprasModel::agregarItem($almacen, $sCodLinea, $sCodProveedor, $nropedido, $tipo, $observacion, $usuario, $vec_check, $codigo, $stk_minimo, $stk_maximo, $cantidad, $ip, $codigox, $descripcionx, $mes1x, $mes2x, $mes3x, $stk_actualx, $stk_minimox, $stk_maximox, $cantidadx);
					$result_f 	= PedidoComprasTemplate::formAgregarBody($res, "A", "");
					$this->visor->addComponent("ContentF", "content_footer", $result_f);
				}
				break;

			case 'GuardarPedidoViejo':
				$almacen 		= @$_REQUEST['cab_almacen'];
				$sCodLinea 		= @$_REQUEST['sCodLinea'];
				$sCodProveedor 	= @$_REQUEST['sCodProveedor'];
				$nropedido 		= @$_REQUEST['cab_nropedido'];
				$tipo 			= @$_REQUEST['cab_tipo'];
				$observacion 	= @$_REQUEST['cab_observacion'];
				$usuario 		= $_SESSION['auth_usuario'];
				$vec_check 		= @$_REQUEST['vec_check']; 
				$codigo 		= @$_REQUEST['codigo'];

				$stk_minimo = @$_REQUEST['stk_minimo'];
				$stk_maximo = @$_REQUEST['stk_maximo'];
				$cantidad = @$_REQUEST['pedido'];

				$res = PedidoComprasModel::ingresarPedido($almacen, $sCodLinea, $sCodProveedor, $nropedido, $tipo, $observacion, $usuario, $vec_check, $codigo, $stk_minimo, $stk_maximo, $cantidad, $ip);
				
				if($res == 1)
					echo '<script name="accion">alert("Datos guardados correctamente.") </script>';
				else 
					echo '<script name="accion">alert("Error al guardar datos.") </script>';	

				$result = PedidoComprasTemplate::searchForm();
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", "");
				break;

			case "EditarViejo":
				$resultado = PedidoComprasModel::buscarDetalles($_REQUEST['num_pedido'], $_REQUEST['almacen'], $_REQUEST['fecha'], $_REQUEST['tipo'], $_REQUEST['observacion']);
				$result = PedidoComprasTemplate::formAgregarBody($resultado, "E");				
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", "");
				break;	

			case 'Guardar':
				if (trim($proveedor)=="" or trim($articulo)=="" or trim($precio)==""){
					$result = '<script name="accion">alert("Falta completar datos") </script>';
										echo $result;
				} else {
					if (trim($_REQUEST['tipoguardar']) == "A")				    
						$result = PedidoComprasModel::ingresar("A", $proveedor, $articulo, $precio, $moneda, $usuario);	
						if($result == 4) $result = '<script name="accion">alert("El articulo ya existe, solo se debe editar.") </script>';
					else				    
						$result = PedidoComprasModel::ingresar("E", $proveedor, $articulo, $precio, $moneda, $usuario);	

					if($result == 1) $result = '<script name="accion">alert("Datos guardados correctamente.") </script>';
					if($result == 2) $result = '<script name="accion">alert("Articulo no encontrado.") </script>';
					if($result == 3) $result = '<script name="accion">alert("Proveedor no encontrado.") </script>';					
					echo $result;
				}
				break;

	    	default:
	    		$result = PedidoComprasTemplate::searchForm();
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", "");
				break;
		}		
	}
}
