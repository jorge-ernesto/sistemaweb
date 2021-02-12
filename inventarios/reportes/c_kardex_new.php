<?php

date_default_timezone_set('UTC');

class KardexActController extends Controller {

	function Init() {

        	$this->visor = new Visor();
        	isset($_REQUEST['action']) ? $this->action = $_REQUEST['action'] : $this->action = "";

    	}

	function Run() {

	        ob_start();

		include 'reportes/m_kardex_new.php';
		include 'reportes/t_kardex_new.php';

		$this->Init();

		$result = "";
		$result_f = "";
		$form_search = false;
		$reporte = false;

        	switch ($this->action) {

			case "Buscar":

		        	$reporte = true;

		        break;

		    	case "PDF":

				$resultado	= KardexActModel::search($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['art_desde'], $_REQUEST['estacion'], $_REQUEST['art_linea'], $_REQUEST['tipoventa']);

				if($resultado=="INVALID_DATE"){
					?><script>alert("<?php echo 'A\361o y mes deben ser iguales en ambas fechas.'; ?> ");</script><?php;
				}else{
					$vacio = '';	
				}

				$resulta	= KardexActModel::movialma($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['art_desde'], $_REQUEST['estacion'], $_REQUEST['art_linea']);
				$resta		= KardexActModel::saldosProductos($_REQUEST['desde'], $_REQUEST['art_desde'], $_REQUEST['estacion'], $_REQUEST['art_linea']);
				$result		= KardexActTemplate::reportePDF($resultado, $resulta, $resta, $_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['tipo_reporte']);

				$mi_pdf = "/sistemaweb/ventas_clientes/reportes/pdf/Kardex.pdf";
				header('Content-type: application/pdf');
				header('Content-Disposition: attachment; filename="' . "Kardex.pdf" . '"');
				readfile($mi_pdf);

		        break;

		    	case "Libro-Electronico":

				$resultado	= KardexActModel::search($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['art_desde'], $_REQUEST['estacion'], $_REQUEST['art_linea'], $_REQUEST['tipoventa']);

				if($resultado=="INVALID_DATE"){
					?><script>alert("<?php echo 'A\361o y mes deben ser iguales en ambas fechas.'; ?> ");</script><?php;
				}else{
					$vacio = '';
				}		

				$v		= KardexActModel::datosEmpresa();
				$resulta	= KardexActModel::movialma($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['art_desde'], $_REQUEST['estacion'], $_REQUEST['art_linea']);
				$resta		= KardexActModel::saldosProductos($_REQUEST['desde'], $_REQUEST['art_desde'], $_REQUEST['estacion'], $_REQUEST['art_linea']);

				if (trim($_REQUEST['tipo_reporte']) == "CONTABLE") {
				    $this->GenerarLibrosElectronicosValorizado($resultado, $v, $_REQUEST['hasta'], $_REQUEST['art_desde'], $resulta, $resta);
				} else if (trim($_REQUEST['tipo_reporte']) == "FISICO") {
				    $this->GenerarLibrosElectronicosFisico($resultado, $v, $_REQUEST['hasta'], $_REQUEST['art_desde']);
				}

		        break;

		    	case "Excel":

				$resultado = KardexActModel::search($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['art_desde'], $_REQUEST['estacion'], $_REQUEST['art_linea'], $_REQUEST['tipoventa']);

				if($resultado=="INVALID_DATE"){
					?><script>alert("<?php echo 'A\361o y mes deben ser iguales en ambas fechas.'; ?> ");</script><?php;
				}else{
					$vacio = '';	
				}

				$result_f = KardexActTemplate::reporteExcel($resultado, $_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['tipo_reporte']);

		        break;

		    	default:
		        	$form_search = true;

        	}

		if ($form_search)
			$result = KardexActTemplate::formSearch();

		if ($reporte) {

        		$resultado	= KardexActModel::search($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['art_desde'], $_REQUEST['estacion'], $_REQUEST['art_linea'], $_REQUEST['tipoventa']);

			if($resultado=="INVALID_DATE"){
				?><script>alert("<?php echo 'A\361o y mes deben ser iguales en ambas fechas.'; ?> ");</script><?php;
			}else{
				$vacio = '';
			}

			$resulta	= KardexActModel::movialma($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['art_desde'], $_REQUEST['estacion'], $_REQUEST['art_linea']);

			$resta		= KardexActModel::saldosProductos($_REQUEST['desde'], $_REQUEST['art_desde'], $_REQUEST['estacion'], $_REQUEST['art_linea']);

			$result_f	= KardexActTemplate::listado($resultado, $resulta, $resta, $_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['art_desde'], $_REQUEST['estacion'], $_REQUEST['tipo_reporte'], $_REQUEST['art_linea']);

		}

        	$this->visor->addComponent("ContentT", "content_title", KardexActTemplate::Titulo());
        	if ($result != "")
            		$this->visor->addComponent("ContentB", "content_body", $result);
        	if ($result_f != "")
            		$this->visor->addComponent("ContentF", "content_footer", $result_f);

	}


	function GenerarLibrosElectronicosValorizado($resultado, $v, $anio, $mes, $resulta, $resta) {

        $fecha = null;
        $array_estado_sald_ini_fin = array();

        $metodo_evaluacion = 1; //PROMEDIO PONDERADO (REGISTRO 16)
        $tipo_existencia = "01";
        $anexo_sunat = KardexActModel::anexo_sunat();

        foreach ($resultado['almacenes'] as $mov_almacen => $almacen) {

            $contador = 0;

            foreach ($almacen['articulos'] as $art_codigo => $articulo) {

                if ($fecha == NULL) {
                    $fecha = str_replace("-", "", substr($articulo['movimientos'][0]['mov_fecha'], 0, 7)) . "00";
                }

                $codigo_unidades_medida = KardexActModel::unidadMedida($art_codigo);
                $codigo_unidades_medida = $codigo_unidades_medida[0];
                $descripcion_detalle = KardexActModel::obtenerDescripcion($art_codigo);

                if (empty($codigo_unidades_medida)) {
                    $codigo_unidades_medida = '99';
                }



                $array_estado_sald_ini_fin[$art_codigo] = array("sal_ini" => FALSE, "sal_final" => FALSE);

                foreach ($articulo['movimientos'] as $i => $movimiento) {
                    $contador++;
                    $fecha = substr($movimiento['mov_fecha'], 0, 10);
                    if ($array_estado_sald_ini_fin[$art_codigo]['sal_ini'] == FALSE) {
                        $PLETXTLINI = $this->ImprimirLiniaPLE_A($articulo['saldoinicial'], $movimiento, $anexo_sunat, $art_codigo, $descripcion_detalle, $codigo_unidades_medida, $metodo_evaluacion, $contador);
                        $PLETXTLINI = $PLETXTLINI['registro'];
                        if (!is_null($PLETXTLINI)) {
                              echo implode("|", $PLETXTLINI) . "|" . PHP_EOL;
                        }
                        $array_estado_sald_ini_fin[$art_codigo]['sal_ini'] = TRUE;
                    }

                    $PLETXTLINI = $this->ImprimirLiniaPLE_M($movimiento, $anexo_sunat, $art_codigo, $descripcion_detalle, $codigo_unidades_medida, $metodo_evaluacion, $contador);
                    $PLETXTLINI = $PLETXTLINI['registro'];
                    if (!is_null($PLETXTLINI)) {
                          echo implode("|", $PLETXTLINI) . "|" . PHP_EOL;
                    }
                }
            }
        }


        $producto = "";

        for ($t = 0; $t < count($resulta); $t++) {
            for ($j = 0; $j < count($resta); $j++) {
                if ($resta[$j]['codigo'] == $resulta[$t]['codigo'] and $resta[$j]['codigo'] != $resulta[$t + 1]['codigo']) {
                    $producto[$j] = $resta[$j]['codigo'];
                }
            }
        }

        for ($j = 0; $j < count($resta); $j++) {

            if ($resta[$j]['codigo'] != $producto[$j]) {

                if ($resta[$j]['stock'] > 0) {

                    $art_codigo = trim($resta[$j]['codigo']);

                    $saldo_iniciales['cant_anterior'] = $resta[$j]['stock'];
                    $saldo_iniciales['unit_anterior'] = $resta[$j]['costo'];
                    $saldo_iniciales['costo_total'] = $resta[$j]['total'];

                   

                    if ($fecha == NULL) {
                        $fecha = str_replace("-", "", substr($articulo['movimientos'][0]['mov_fecha'], 0, 7)) . "00";
                    }

                    $codigo_unidades_medida = KardexActModel::unidadMedida($art_codigo);
                    $codigo_unidades_medida = $codigo_unidades_medida[0];
                    $descripcion_detalle = KardexActModel::obtenerDescripcion($art_codigo);

                    if (empty($codigo_unidades_medida)) {
                        $codigo_unidades_medida = '99';
                    }

                     $PLETXTLINI = $this->ImprimirLiniaPLE_A($saldo_iniciales, $movimiento, $anexo_sunat, $art_codigo, $descripcion_detalle, $codigo_unidades_medida, $metodo_evaluacion, 1);
                    $PLETXTLINI = $PLETXTLINI['registro'];

                    if (!is_null($PLETXTLINI)) {
                         echo implode("|", $PLETXTLINI) . "|" . PHP_EOL;
                    }
                }
            }
        }

        $ruc = $v['ruc'];
        $ntickets = $contador;




        $estado_info = 1;
        if ($ntickets > 1) {
            $estado_info = 1;
        }
        $ano1 = substr($anio, 6) . "" . substr($anio, 3, 2);
        $nombre_archivo = "LE" . $ruc . "" . $ano1 . "" . $mes . "00130100001" . $estado_info . "11.txt";
        header("Content-type: text/plain");
        header("Content-Disposition: attachment; filename=\"$nombre_archivo\"");
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
        die($cade);
    }

    function ImprimirLiniaPLE_A($saldo_iniciales, $movimiento, $anexo_sunat, $art_codigo, $descripcion_detalle, $codigo_unidades_medida, $metodo_evaluacion, $correlativo) {



        $fecha = substr($movimiento['mov_fecha'], 0, 10);
        $fecha_transacion = substr($movimiento['mov_fecha'], 0, 7) . "-01";
        $tipo_operacion = trim(substr($movimiento['tran_codigo'], 0, strpos($movimiento['tran_codigo'], "-")));
        if ($tipo_operacion == "02" && $this->getTipoComprobantePago_10($movimiento['codigo_tipo_tansa'], $movimiento['mov_tipdocuref']) == "00") {//si es COMPRA y si su documento es anulado
            return array("registro" => null);
        } else {

            $mov_almadestino = $movimiento['mov_almadestino'];

            $PLETXT[0] = $this->getPeriodo($fecha);
            $PLETXT[1] = $saldo_iniciales['codigo_CUO'] . "" . $movimiento['mov_numero']; //Almacen-Articulo-Mes-Mov_numero
            $PLETXT[2] = "A1";
            $PLETXT[3] = $anexo_sunat;
            $PLETXT[4] = "9"; //codigo de catalogo de existencia(GS1 (EAN-UCC)=3) y OTROS=9
            $PLETXT[5] = "01"; //tipo de existencia(01 es MERCADERIA)

            $PLETXT[6] = trim($art_codigo);
            $PLETXT[7] = $this->getCodigoexitencia_OSCE($art_codigo);
            $PLETXT[8] = $this->formato_fecha($fecha_transacion);


            $PLETXT[9] = "16"; //Tipo de documento de traslado (00)=OTROS
            $PLETXT[10] = "0"; //Serie 
            $PLETXT[11] = "0"; //Numero Correlativo
            $PLETXT[12] = $this->es_documeto_traslado($tipo_operacion); //Tipo de Opercacion


            $PLETXT[13] = trim($descripcion_detalle);
            $PLETXT[14] = $codigo_unidades_medida; //$codigo_unidades_medida;
            $PLETXT[15] = $metodo_evaluacion;

            $PLETXT[16] = $this->es_vacio('0');
            $PLETXT[17] = $this->es_vacio('0');
            $PLETXT[18] = $this->es_vacio('0');

            $PLETXT[19] = $this->es_vacio('0');
            $PLETXT[20] = $this->es_vacio('0');
            $PLETXT[21] = $this->es_vacio('0');


            $PLETXT[22] = $this->es_vacio($saldo_iniciales['cant_anterior']);
            $PLETXT[23] = $this->es_vacio(abs($saldo_iniciales['unit_anterior']));
            $PLETXT[24] = $this->es_vacio($saldo_iniciales['costo_total']);
            $PLETXT[25] = "1";

            return array("registro" => $PLETXT);
        }
    }

    function ImprimirLiniaPLE_M($movimiento, $anexo_sunat, $art_codigo, $descripcion_detalle, $codigo_unidades_medida, $metodo_evaluacion, $correlativo) {
        $fecha = substr($movimiento['mov_fecha'], 0, 10);
        $tipo_operacion = trim(substr($movimiento['tran_codigo'], 0, strpos($movimiento['tran_codigo'], "-")));
        if ($tipo_operacion == "02" && $this->getTipoComprobantePago_10($movimiento['codigo_tipo_tansa'], $movimiento['mov_tipdocuref']) == "00") {//si es COMPRA y si su documento es anulado
            return array("registro" => null);
        } else {

            $mov_almadestino = $movimiento['mov_almadestino'];

            $PLETXT[0] = $this->getPeriodo($fecha);
            $PLETXT[1] = $movimiento['mov_numero'] . "" . trim($movimiento['tipodocu']) . "" . trim($movimiento['seriedocu']) . "" . trim($movimiento['numdocu']) . "" . $correlativo;
            $PLETXT[2] = "M1";
            $PLETXT[3] = $anexo_sunat;
            $PLETXT[4] = "9"; //codigo de catalogo de existencia(GS1 (EAN-UCC)=3) y OTROS=9
            $PLETXT[5] = "01"; //tipo de existencia(01 es MERCADERIA)

            $PLETXT[6] = trim($art_codigo); //. "-" . $movimiento['codigo_tipo_tansa'] . "-" . $movimiento['mov_tipdocuref']; //codigo del producto
            $PLETXT[7] = $this->getCodigoexitencia_OSCE($art_codigo);
            $PLETXT[8] = $this->formato_fecha($fecha);


            $PLETXT[9] = $this->getTipoComprobantePago_10($movimiento['codigo_tipo_tansa'], $movimiento['mov_tipdocuref'], $movimiento['tipodocu']); //$this->es_documeto_traslado($movimiento['tipodocu']);
            $data_serie_num = $this->getValidarDocumento($tipo_operacion, $movimiento['seriedocu'], $movimiento['numdocu'], $movimiento['mov_numero'], $mov_almadestino);
            $PLETXT[10] = $this->es_documeto_traslado($data_serie_num['serie']);
            $PLETXT[11] = $this->es_documeto_traslado($data_serie_num['numero']);
            $PLETXT[12] = $this->es_documeto_traslado($tipo_operacion);


            $PLETXT[13] = trim($descripcion_detalle);
            $PLETXT[14] = $codigo_unidades_medida; //$codigo_unidades_medida;
            $PLETXT[15] = $metodo_evaluacion;

            $PLETXT[16] = $this->es_vacio($movimiento['mov_cant_entrada']);
            $PLETXT[17] = $this->es_vacio($movimiento['mov_unit_entrada']);
            $PLETXT[18] = $this->es_vacio($movimiento['mov_cost_entrada']);

            $PLETXT[19] = $this->es_vacio($movimiento['mov_cant_salida']);
            $PLETXT[20] = $this->es_vacio($movimiento['mov_unit_salida']);
            $PLETXT[21] = $this->es_vacio($movimiento['mov_cost_salida']);

            $PLETXT[22] = $this->es_vacio($movimiento['mov_cant_actual']);
            $PLETXT[23] = $this->es_vacio($movimiento['mov_val_unit_act']);
            $PLETXT[24] = $this->es_vacio($movimiento['mov_total_act']);
            $PLETXT[25] = "1";

            return array("registro" => $PLETXT);
        }
    }

    /*  function GenerarLibrosElectronicosFisico($resultado, $v, $anio, $mes) {
      $fecha = null;
      // var_dump($resultado);
      foreach ($resultado['almacenes'] as $mov_almacen => $almacen) {
      // echo $mov_almacen . "----";


      $contador = 0;
      foreach ($almacen['articulos'] as $art_codigo => $articulo) {
      if ($fecha == NULL) {
      $fecha = str_replace("-", "", substr($articulo['movimientos'][0]['mov_fecha'], 0, 7)) . "00";
      }
      $codigo_unidades_medida = KardexActModel::unidadMedida($art_codigo);
      $descripcion_detalle = KardexActModel::obtenerDescripcion($art_codigo);


      $codigo_unidades_medida = $codigo_unidades_medida[0];

      if (empty($codigo_unidades_medida)) {
      $codigo_unidades_medida = '99';
      }

      $catologo_existencia = 3;

      $tipo_existencia = "01";

      foreach ($articulo['movimientos'] as $i => $movimiento) {
      $contador++;
      $fecha = substr($movimiento['mov_fecha'], 0, 10);
      $tipo_operacion = trim(substr($movimiento['tran_codigo'], 0, strpos($movimiento['tran_codigo'], "-")));
      $cade.= $this->formato_fecha_periodo($fecha) . "," . $mov_almacen . "," . $catologo_existencia;
      $cade.="," . $tipo_existencia . "," . trim($art_codigo);
      $cade.="," . $this->formato_fecha($fecha) . "," . $this->es_documeto_traslado($movimiento['tipodocu']) . "," . $this->es_documeto_traslado($movimiento['seriedocu']) . "," . $this->es_documeto_traslado($movimiento['numdocu']) . "," . $this->es_documeto_traslado($tipo_operacion);
      $cade.="," . trim($descripcion_detalle) . "," . $codigo_unidades_medida; //se elimina el metodo de evaluacion
      $cade.="," . $this->es_vacio($movimiento['mov_cant_entrada']);
      $cade.="," . $this->es_vacio($movimiento['mov_cant_salida']);
      $cade.=",1"; //periodo al qu pertenece

      $cade.= "\n";
      }

      // $reporte->lineaH();
      //$reporte->nuevaFila($articulo['totales'], "_totales");
      }
      }

      $ruc = $v['ruc'];
      $ntickets = $contador;




      $estado_info = 0;
      if ($ntickets > 1) {
      $estado_info = 1;
      }
      $ano1 = substr($anio, 6) . "" . substr($anio, 3, 2);
      $nombre_archivo = "LE" . $ruc . "" . $ano1 . "" . $mes . "00120100001" . $estado_info . "11.txt";
      header("Content-type: text/plain");
      header("Content-Disposition: attachment; filename=\"$nombre_archivo\"");
      header("Cache-Control: no-cache, must-revalidate");
      header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
      die($cade);
      }
     */

    function getCodigoexitencia_OSCE($valor) {
        $articulo = trim($valor);

        if ($articulo == "11620301" ||
                $articulo == "11620302" || $articulo == "11620303" ||
                $articulo == "11620304" || $articulo == "11620305" ||
                $articulo == "11620306" || $articulo == "11620307") {
            return "15000000";
        }
        return "99000000";
    }

    function getTipoComprobantePago_10($tipo_trans, $mov_tipdocuref, $tipodocu) {
        $tipo_trans = trim($tipo_trans);
        $mov_tipdocuref = trim($mov_tipdocuref);

        if ($tipo_trans == "25" || $tipo_trans == "45") {
            return "12";
        } else if ($tipo_trans == "21" || $tipo_trans == "01") {//COMPRA-son Ingreso puede ser compra o ingreso de combustible
            if ($mov_tipdocuref == "35") {
                return "03";
            } else if ($mov_tipdocuref == "10") {
                return "01";
            } else if ($mov_tipdocuref == "09") {
                return "09";
            } else {
                return "00";
            }
        } else if ($tipo_trans == "10") {//VENTA- venta con factura 
            return "01";
        } else if ($tipo_trans == "35") { //VENTA-  venta con boleta
            return "03";
        } else {
            $tipodocu = trim($tipodocu);

            if (!empty($tipodocu)) {
                return $tipodocu;
            }

            return "00";
        }

        return "00";
    }

    function getValidarDocumento($tipo_operacion_efectuada, $seriedocu, $numdocu, $mov_numero_v, $mov_almadestino_v) {
        $tipo_operacion = trim($tipo_operacion_efectuada);
        if ($tipo_operacion == '01' || $tipo_operacion == '02' || $tipo_operacion == '05' || $tipo_operacion == '06') {

            if ($tipo_operacion == '01') {
                return array("serie" => $mov_almadestino_v, "numero" => $mov_numero_v);
            } else if ($tipo_operacion == '02') {
                return array("serie" => str_replace("-", "", $seriedocu), "numero" => str_replace("-", "", $numdocu));
            }
        } else {
            return array("serie" => "0", "numero" => "0");
        }
    }

    function es_vacio($valor) {
        if (empty($valor) || is_null($valor) || trim(trim($valor)) == 0) {
            return "0.00";
        }
        return round($valor, 8);
    }

    function es_documeto_traslado($valor) {
        if (empty($valor) || is_null($valor) || trim(trim($valor)) == 0) {
            return "00";
        }
        return $valor;
    }

    function getPeriodo($fecha) {
        return str_replace("-", "", substr($fecha, 0, 7)) . "00";
    }

    function formato_fecha($fecha) {
        $date = explode("-", $fecha);
        return $date[2] . "/" . $date[1] . "/" . $date[0];
    }

    function validar_descripcion_existencia($de_existencia) {
        if (empty($de_existencia) || is_null($de_existencia) || trim(trim($de_existencia)) == 0) {
            return "-";
        }
        return $de_existencia;
    }

}

