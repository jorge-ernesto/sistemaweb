<?php

ini_get('register_globals');

class FacturasModel extends Model {

	function GetTaxOptional(){//SOLO PARA LAS EMPRESAS QUE SEAN EXONERADAS
		global $sqlca;

		$query = "SELECT par_valor taxoptional FROM int_parametros WHERE par_nombre = 'taxoptional';";
		$sqlca->query($query);
		$row = $sqlca->fetchRow();
		if(empty($row) || $row["taxoptional"] == "0")
			return false;
		return true;
	}
	
	function obtenerTiposDocumento() {
		global $sqlca;

		$tipos["TODOS"] = "TODOS";
		$sql = "SELECT tab_elemento, tab_descripcion FROM int_tabla_general WHERE tab_tabla='08' AND (trim(tab_car_03)!='' OR tab_car_03 is not null);";

		if ($sqlca->query($sql) <= 0)
		    return $sqlca->get_error();
		while ($reg = $sqlca->fetchRow()) {
		    $val = substr($reg[0], 4, 5);
		    $des = $reg[1];
		    $tipos[$val] = $des;
		}

		return $tipos;
	}
	
	function getFormasPago() {
		global $sqlca;

		$arrFormaPagos = array();

		$sql = "
		SELECT
			substring(tab_elemento for 2 from length(tab_elemento)-1) AS nu_tipo_pago,
			tab_descripcion
		FROM
			int_tabla_general
		WHERE
			tab_tabla = '05'
			AND tab_elemento != ''
			AND tab_elemento<>'000000'
		ORDER BY
			tab_elemento";

		if ($sqlca->query($sql) <= 0)
		    return $sqlca->get_error();

		while ($row = $sqlca->fetchRow())
		    $arrFormaPagos[trim($row['nu_tipo_pago'])] = $row['tab_descripcion'];

		return $arrFormaPagos;
	}

	function ObtenerTipoCambio($fecha, $tipomoneda){
		global $sqlca;

		$y = substr($fecha,6,4);
		$m = substr($fecha,3,2);
		$d = substr($fecha,0,2);

		$fecha = $y."-".$m."-".$d;

		try {

			$registro = array();

			$sql = "SELECT tca_venta_oficial FROM int_tipo_cambio WHERE tca_moneda = '$tipomoneda' AND tca_fecha = '$fecha'";

			if($sqlca->query($sql) <= 0){
				throw new Exception("Error");
			}

			while($reg = $sqlca->fetchRow()){
			        $registro[] = $reg;
			}

			return $registro;

		}catch(Exception $e){
			throw $e;
		}

	}
	
    function ModelReportePDF($filtro = array()) {
        global $sqlca;

        $serie = $filtro['serie'];
        $fecha_ini = $filtro['fecha_ini'];
        $fecha_fin = $filtro['fecha_fin'];
        $codigo = $filtro['codigo'];
        $tipo_doc = $filtro['tipo'];
        $num_doc = $filtro['numero'];
        $tmp = date("d/m/Y");

        if ($fecha_ini == $tmp && $fecha_fin == $tmp) {
            $cond1 .= "AND det.dt_fac_fecha  = '$tmp'";
        }

        if ($codigo != '') {
            $cond2 .= "AND det.cli_codigo  = '$codigo'";
        }

        if ($tipo_doc != '') {
            $cond3 .= "AND det.ch_fac_tipodocumento  = '$tipo_doc'";
        }

        if ($num_doc != '') {
            $cond4 .= "AND det.ch_fac_numerodocumento  = '$num_doc'";
        }

        if ($serie != '') {
            $cond5 .= "AND det.ch_fac_seriedocumento  = '$serie'";
        }

        $query = "SELECT
		        	det.cli_codigo as CLIENTE,
		        	cli.cli_razsocial as RAZON_SOCIAL,
		        	det.dt_fac_fecha as FECHA,
		        	det.ch_fac_seriedocumento as SERIE, 
		        	iif(det.ch_fac_tipodocumento = '10','FACTURA',iif(det.ch_fac_tipodocumento = '20','N/CREDITO',iif(det.ch_fac_tipodocumento = '11', 'N/DEBITO',iif(det.ch_fac_tipodocumento = '35', 'BOL/VENTA',NULL)))) as TIPO, 
		        	det.ch_fac_numerodocumento as NUMERO, 
		        	det.nu_fac_valorbruto as VALOR_VENTA, 
		        	det.nu_fac_impuesto1 as IGV, 
		        	det.nu_fac_valortotal as TOTAL_VENTA, 
		        	det.ch_fac_credito as CREDITO 
		        FROM 
		        	fac_ta_factura_cabecera as det, 
		        	int_clientes as cli 
		        WHERE 
		        	(det.dt_fac_fecha >= to_date('$fecha_ini','dd/mm/yyyy') AND det.dt_fac_fecha <= to_date('$fecha_fin','dd/mm/yyyy'))
		        	AND ch_fac_tipodocumento <> '45' 
				AND det.cli_codigo = cli.cli_codigo 
		        	$cond1 
			        $cond2 
				$cond3 
				$cond4 
				$cond5 
		  	ORDER BY 
		  		det.cli_codigo, 
		  		det.dt_fac_fecha DESC, 
		  		det.ch_fac_seriedocumento, 
		  		det.ch_fac_numerodocumento";

        $sqlca->query($query);
        $numrows = $sqlca->numrows();
        while ($reg = $sqlca->fetchRow()) {
            $registro[] = $reg;
        }

        return $registro;
    }

    function GenerarRegitroID() {
        if (isset($_REQUEST["registroid"]) && $_REQUEST["registroid"] != "") {
            $Valores = explode(' ', $_REQUEST["registroid"]);
            $registroid['TipoDoc'] = trim($Valores[0]);
            $registroid['SerieDoc'] = trim($Valores[1]);
            $registroid['NroDoc'] = trim($Valores[2]);
            $registroid['CodCliente'] = trim($Valores[3]) . " " . trim($Valores[4]);
            //print_r($registroid);

            return $registroid;
        }
    }

	function validaDia($almacen, $dia) {
		global $sqlca;

		$dia 	= substr($dia,6,4)."-".substr($dia,3,2)."-".substr($dia,0,2);
		$turno 	= 0;
		$sql 	= " SELECT validar_consolidacion('" . $dia. "', " . $turno . ", '" . $almacen . "');";

		$sqlca->query($sql);

		$estado = $sqlca->fetchRow();

		if($estado[0] == 1){
			return 1;//Consolidado
		}else{
			return 0;//No consolidado
		}

	}


	function validaingreso ($tipo, $serie, $numero) {

    	global $sqlca;

		$sql = "SELECT count(*) from fac_ta_factura_cabecera WHERE ch_fac_tipodocumento = '".$tipo."' AND ch_fac_seriedocumento = '".$serie."' AND ch_fac_numerodocumento = '".$numero."' AND ch_almacen = '".$almacen."'" ;

		$sqlca->query($sql);

		$existe = $sqlca->fetchRow();

		if($existe[0] == 0){
			return 0;//No existe factura
		}else{
			return 1;//Existe factura
		}
    }

	function guardarRegistro($datos, $fecha_replicacion, $fe_vencimiento) {
		global $sqlca;

		$nuexonerado = $datos['nuexonerado'];

		unset($datos['nuexonerado']);//SE ELIMINA PORQUE NO DEJARA HACER INSERT EN fac_ta_factura_cabecera;

		$record	= FacturasModel::validaingreso($datos['ch_fac_tipodocumento'], $datos['ch_fac_seriedocumento'], $datos['ch_fac_numerodocumento'],$datos['ch_almacen']); //verifica que no haya ingresado
									
        if($record == 1){
			?><script>alert("<?php echo 'No se puede agregar. Ya existe el documento con esta numeración, recargar la página.'; ?> ");</script><?php
        }else{
        	$sqlca->query("SELECT ch_almacen FROM int_num_documentos WHERE num_seriedocumento = '" . $datos['ch_fac_seriedocumento'] . "' AND num_tipdocumento = '" . $datos['ch_fac_tipodocumento'] . "'");

			$reg = $sqlca->fetchRow();

			$flag = FacturasModel::validaDia($reg[0], $datos['dt_fac_fecha']);
    		if($flag == 1){
				?><script>alert("<?php echo 'No se puede agregar. Fecha ya consolidada!'; ?> ");</script><?php
    		}else{
    			if( $datos['nu_fac_valortotal'] == 0.00 || $datos['nu_fac_valortotal'] == '0.00'){
					?><script>alert("<?php echo 'El total del documento no puede ser cero'; ?> ");</script><?php
					return FALSE;
    			} else {
		        	//$datosArticulos = $GLOBALS['ARTICULOS'];

			    	$datos['ch_almacen'] 		= $reg[0];
			    	$datos['ch_punto_venta'] 	= $reg[0];
			    	$datos['dt_fac_fecha']		= "to_date('" . $datos['dt_fac_fecha'] . "','dd/mm/yyyy')";

			    	if ($datos['nu_tipo_pago'] == '06') {//Tipo de pago Crédito
						$datos['fe_vencimiento'] = "to_date('" . $fe_vencimiento . "','dd/mm/yyyy')";
					} else {
						$datos['fe_vencimiento'] = $datos['dt_fac_fecha'];
					}

			    	$datos['fecha_replicacion']	= "to_date('" . $fecha_replicacion . "','dd/mm/yyyy')";
			    	$datos['flg_replicacion'] 	= '0';
			    	$datos['nu_fac_impuesto2'] 	= ($datos['nu_fac_impuesto2'] == '' ? 0 : $datos['nu_fac_impuesto2']);

					if($nuexonerado == "S"){
						$datos['ch_fac_tiporecargo2']	= "S";
						$datos['nu_fac_valorbruto'] 	= $datos['nu_fac_valortotal'];
					}

					$datosArticulos = $_SESSION['ARTICULOS'];
					/* Carta fianza */
					/*
		        	foreach ($datosArticulos as $llave => $Valores) {
		        		$sql = "SELECT art_impuesto1 FROM int_articulos WHERE art_codigo = '" . TRIM($Valores['cod_articulo']) . "' LIMIT 1;";
						$sqlca->query($sql);
						$get_data_row = $sqlca->fetchRow();

						if($get_data_row['art_impuesto1'] == '' || $get_data_row['art_impuesto1'] == NULL){
							$datos['nu_fac_impuesto1'] 	= 0.00;
							$datos['nu_fac_valortotal']	= $datos['nu_fac_valorbruto'];
						}
					}
					*/

					$datosComplementarios = $_SESSION['ARR_COMP'];

					if (isset($_REQUEST["registroid"]) && $_REQUEST["registroid"] != "") {

						$registroid = FacturasModel::GenerarRegitroID();

						if ($sqlca->perform('fac_ta_factura_cabecera', $datos, 'update', "ch_fac_tipodocumento='" . $registroid['TipoDoc'] . "' AND ch_fac_seriedocumento='" . $registroid['SerieDoc'] . "' AND ch_fac_numerodocumento='" . $registroid['NroDoc'] . "' AND cli_codigo='" . $registroid['CodCliente'] . "'") >= 0) {

							if (!empty($datosArticulos)) {
								FacturasModel::InsertarArticulos($datos, $datosArticulos, $nuexonerado);
							}

							FacturasModel::guardarRegistroComplemento($datos, $datosComplementarios);

							if (!empty($datosArticulos) && $datos['ch_descargar_stock'] == 'S') {
								FacturasModel::InsertarInvMovAlm($datos, $datosArticulos);
							}

						} else {
							return $sqlca->get_error();
						}

		                		return OK;

			            	} else {

						if ($sqlca->perform('fac_ta_factura_cabecera', $datos, 'insert') >= 0) {

							if (!empty($datosArticulos)) {
								FacturasModel::InsertarArticulos($datos, $datosArticulos, $nuexonerado);
							}

							FacturasModel::guardarRegistroComplemento($datos, $datosComplementarios);

							if (!empty($datosArticulos) && $datos['ch_descargar_stock'] == 'S') {
								FacturasModel::InsertarInvMovAlm($datos, $datosArticulos);
							}

						} else {
							return $sqlca->get_error();
						}

						FacturasModel::AumentaCorreDoc($datos['ch_fac_tipodocumento'], $datos['ch_fac_seriedocumento'], $datos['dt_fac_fecha'], 'insert');
						$queryaux = "DELETE FROM tmp_anulado_eliminado WHERE ch_fac_seriedocumento = '" . $datos['ch_fac_seriedocumento'] . "' AND ch_fac_numerodocumento = '" . $datos['ch_fac_numerodocumento'] . "' AND ch_fac_tipodocumento = '" . $datos['ch_fac_tipodocumento'] . "'";
						$sqlca->query($queryaux);
						return OK;
					}
				}
			}
		}
	}

	function AumentaCorreDoc($TipoDoc, $SerieDoc, $fecha, $Accion) {
        	global $sqlca;

		if ($sqlca->functionDB("util_fn_corre_docs_fecha('" . $TipoDoc . "', '" . $SerieDoc . "', '" . $Accion . "'," . $fecha . ")")) {
			return OK;
		}
	}

	function InsertarArticulos($datos, $datosArticulos, $nuexonerado) {
        global $sqlca;

        foreach ($datosArticulos as $llave => $Valores) {

	    	$Articulos['ch_fac_tipodocumento'] 		= $datos['ch_fac_tipodocumento'];
	    	$Articulos['ch_fac_seriedocumento'] 	= $datos['ch_fac_seriedocumento'];
	    	$Articulos['ch_fac_numerodocumento'] 	= $datos['ch_fac_numerodocumento'];
	    	$Articulos['cli_codigo'] 				= $datos['cli_codigo'];
	    	$Articulos['art_codigo'] 				= $Valores['cod_articulo'];
	    	$Articulos['pre_lista_precio'] 			= $Valores['pre_lista_precio'];
	    	$Articulos['nu_fac_cantidad'] 			= $Valores['cant_articulo'];
	    	$Articulos['nu_fac_precio'] 			= $Valores['precio_articulo'];
	    	$Articulos['nu_fac_importeneto'] 		= $Valores['neto_articulo'];
	    	$Articulos['ch_factipo_descuento1'] 	= $datos['ch_factipo_descuento1'];
	    	$Articulos['nu_fac_descuento1']			= (empty($Valores['dscto_articulo']) ? 0.00 : $Valores['dscto_articulo']);
	    	$Articulos['ch_fac_cd_impuesto1'] 		= $datos['ch_fac_cd_impuesto1'];

			if($nuexonerado == "S"){
				$Articulos['ch_fac_tiporecargo2']	= "S";
				$Articulos['nu_fac_impuesto1'] 		= 0.00;
				$Articulos['nu_fac_valortotal']		= $Valores['neto_articulo'];
			}else{
			    $Articulos['nu_fac_impuesto1'] 		= $Valores['igv_articulo'];
			    $Articulos['nu_fac_valortotal'] 	= $Valores['neto_articulo'] + $Valores['igv_articulo'];
			}

	    	$Articulos['ch_art_descripcion'] 	= $Valores['desc_articulo'];

			FacturasModel::guardarRegistroArticulos($Articulos);

		}

       		return OK;

    	}

	function CostoPromedioArticulos($FechaDoc, $CodAlm, $CodArt) {
        	global $sqlca;
        	$Monto = $sqlca->functionDB("util_fn_costo_promedio_articulos(to_char(to_date('" . $FechaDoc . "','dd/mm/yyyy'),'yyyy'),to_char(to_date('" . $FechaDoc . "','dd/mm/yyyy'),'mm'),'" . $CodArt . "',lpad('" . $CodAlm . "',3,'0'))");
        	return $Monto;
	}

	function InsertarInvMovAlm($datos, $datosArticulos) {
       	global $sqlca;

    	$fechaDiv = explode("/", $_REQUEST['datos']['dt_fac_fecha']);
   		$datos['dt_fac_fecha_new'] = $fechaDiv[2] . "/" . $fechaDiv[1] . "/" . $fechaDiv[0];

    	$DatosInvTipotransa = VariosModel::InicializarVariables(trim($datos['ch_fac_tipodocumento']), trim($datos['ch_almacen']));

		$serie 			= $datos['ch_fac_seriedocumento'];
    	$nu_documento 	= null;
    	$sql 			= null;
    	$nu_tipo_item 	= null;

        foreach ($datosArticulos as $llave => $Valores) {

            $CostoPromedio = FacturasModel::CostoPromedio(trim($datos['dt_fac_fecha_new']), trim($datos['ch_almacen']), trim($Valores['cod_articulo']));

            $CostoPromedioArticulos = FacturasModel::CostoPromedioArticulos(trim($datos['dt_fac_fecha_new']), trim($datos['ch_almacen']), trim($Valores['cod_articulo']));

            if(strlen($datos['ch_fac_seriedocumento']) > 3)
             	$nu_documento = substr($datos['ch_fac_numerodocumento'], -6);
            else
            	$nu_documento = $datos['ch_fac_numerodocumento'];

            $InvMovAlm['mov_numero'] = $serie.$nu_documento;
            $InvMovAlm['tran_codigo'] = $datos['ch_fac_tipodocumento'];
            $InvMovAlm['art_codigo'] = $Valores['cod_articulo'];
            $InvMovAlm['mov_fecha'] = $datos['dt_fac_fecha'] . " +current_time";
            $InvMovAlm['mov_almacen'] = $datos['ch_almacen'];
            $InvMovAlm['mov_almaorigen'] = $datos['ch_almacen'];
            $InvMovAlm['mov_almadestino'] = $DatosInvTipotransa['Destino'];
            $InvMovAlm['mov_naturaleza'] = $DatosInvTipotransa['Naturaleza'];
            $InvMovAlm['mov_entidad'] = $datos['cli_codigo'];
            $InvMovAlm['mov_cantidad'] = $Valores['cant_articulo'];

            if ($CostoPromedio == 0.0000 || $CostoPromedio == '0.0000' || $CostoPromedio <= 0) {
                $InvMovAlm['mov_costounitario'] = $CostoPromedioArticulos;
                $InvMovAlm['mov_costopromedio'] = $CostoPromedioArticulos;
            } else {
                $InvMovAlm['mov_costounitario'] = $CostoPromedio;
                $InvMovAlm['mov_costopromedio'] = $CostoPromedio;
            }

            $InvMovAlm['mov_costototal'] = ($CostoPromedio * $Valores['cant_articulo']);
            $InvMovAlm['mov_fecha_actualizacion'] = "current_timestamp";

            /* Verificar si es un producto estandar o plu saliente */
            $sql = "SELECT art_plutipo FROM int_articulos WHERE art_codigo = '".trim($Valores['cod_articulo'])."'";
			$sqlca->query($sql);

			$nu_tipo_item = $sqlca->fetchRow();

			if(trim($nu_tipo_item['art_plutipo']) == "2"){//PLU SALIENTE
				$sql = "SELECT ch_item_estandar, nu_cantidad_descarga FROM int_ta_enlace_items WHERE art_codigo = '".trim($Valores['cod_articulo'])."'";
				$sqlca->query($sql);

				$datos_items_estandar = $sqlca->fetchAll();
				foreach ($datos_items_estandar as $rows)
					FacturasModel::guardarRegistroInvMovAlmPluSaliente($InvMovAlm, $rows);
			}else
            	FacturasModel::guardarRegistroInvMovAlm($InvMovAlm);
        }

        return OK;
    }

    function guardarRegistroInvMovAlmPluSaliente($datos, $datos_enlaces) {
        global $sqlca;

        $fechaDiv 		= explode("/", $_REQUEST['datos']['dt_fac_fecha']);
        $dt_fac_fecha 	= $fechaDiv[2] . "/" . $fechaDiv[1] . "/" . $fechaDiv[0];

		$sql = "
		SELECT
			COUNT(*) existe
		FROM
			inv_movialma
		WHERE
			trim(mov_almacen) 	= '" . $datos['mov_almacen'] . "'
			AND mov_numero 		= '" . $datos['mov_numero'] . "'
			AND tran_codigo 	= '" . $datos['tran_codigo'] . "'
			AND art_codigo 		= '" . $datos_enlaces['ch_item_estandar'] . "'
			AND mov_fecha::DATE = '" . $dt_fac_fecha . "'
		";

		$sqlca->query($sql);
		$movialmacrud = $sqlca->fetchRow();

		$datos['art_codigo'] = $datos_enlaces['ch_item_estandar'];

		if($movialmacrud['existe'] == 0){

			$datos['mov_cantidad'] 		= ($datos['mov_cantidad'] * $datos_enlaces['nu_cantidad_descarga']);
        	$datos['mov_costototal'] 	= ($datos['mov_costopromedio'] * ($datos['mov_cantidad'] * $datos_enlaces['nu_cantidad_descarga']));
			
			if ($sqlca->perform('inv_movialma', $datos, 'insert') >= 0) {
    			echo "\n Entro";
    			echo "<br/> Inserta Kardex: " . var_dump($datos);
            } else {
                return $sqlca->get_error();
            }
            return OK;
		}else{

			$sql = "
			SELECT
				mov_cantidad,
				mov_costopromedio
			FROM
				inv_movialma
			WHERE
				trim(mov_almacen) 	= '" . $datos['mov_almacen'] . "'
				AND mov_numero 		= '" . $datos['mov_numero'] . "'
				AND tran_codigo 	= '" . $datos['tran_codigo'] . "'
				AND art_codigo 		= '" . $rows['ch_item_estandar'] . "'
				AND mov_fecha::DATE = '" . $dt_fac_fecha . "'
			";

			$sqlca->query($sql);
			$get_data_row = $sqlca->fetchRow();

			$datos['mov_cantidad'] 		= (($datos['mov_cantidad'] + $get_data_row['mov_cantidad']) * $datos_enlaces['nu_cantidad_descarga']);
        	$datos['mov_costototal'] 	= ($datos['mov_costopromedio'] * (($datos['mov_cantidad'] + $get_data_row['mov_cantidad']) * $datos_enlaces['nu_cantidad_descarga']));

			if ($sqlca->perform('inv_movialma', $datos, 'update', "mov_numero='" . $datos['mov_numero'] . "' AND tran_codigo='" . $datos['tran_codigo'] . "' AND art_codigo='" . $datos['art_codigo'] . "' AND mov_fecha='" . $dt_fac_fecha . "'") >= 0) {
				echo "\n Entro";
   				echo "<br/> Modifica Kardex: " . var_dump($datos);
            } else {
                return $sqlca->get_error();
            }
            return OK;
		}
    }

    function guardarRegistroInvMovAlm($datos) {
        global $sqlca;

        $fechaDiv 		= explode("/", $_REQUEST['datos']['dt_fac_fecha']);
        $dt_fac_fecha 	= $fechaDiv[2] . "/" . $fechaDiv[1] . "/" . $fechaDiv[0];

		if (isset($_REQUEST["registroid"]) && $_REQUEST["registroid"] != "") {
            if ($sqlca->perform('inv_movialma', $datos, 'update', "mov_numero='" . $datos['mov_numero'] . "' AND tran_codigo='" . $datos['tran_codigo'] . "' AND art_codigo='" . $datos['art_codigo'] . "' AND mov_fecha='" . $dt_fac_fecha . "'") >= 0) {
                
            } else {
                return $sqlca->get_error();
            }
            return OK;
        } else {
            if ($sqlca->perform('inv_movialma', $datos, 'insert') >= 0) {
            } else {
                return $sqlca->get_error();
            }
            return OK;
        }
    }
    
	function guardarRegistroArticulos($datos) {
		global $sqlca;

        if (!empty($datos['pre_lista_precio']))
			$datos['pre_lista_precio'] = $_REQUEST['articulos']['pre_lista_precio'];
		else
			$datos['pre_lista_precio'] = $_REQUEST['articulos']['pre_lista_precio'];

		$sql = "SELECT art_impuesto1 FROM int_articulos WHERE art_codigo = '" . TRIM($datos['art_codigo']) . "' LIMIT 1;";
		$sqlca->query($sql);
		$get_data_row = $sqlca->fetchRow();

		if($get_data_row['art_impuesto1'] == '' || $get_data_row['art_impuesto1'] == NULL){
			$datos['nu_fac_impuesto1'] 	= 0.00;
			//$datos['nu_fac_valortotal']	= $datos['nu_fac_importeneto'];
			$datos['nu_fac_importeneto']	= $datos['nu_fac_valortotal'];
		}

		if (isset($_REQUEST["registroid"]) && $_REQUEST["registroid"] != "") {
			$registroid = FacturasModel::GenerarRegitroID();

			if ($sqlca->perform('fac_ta_factura_detalle', $datos, 'update', "art_codigo='" . $datos['art_codigo'] . "' AND ch_fac_tipodocumento='" . $registroid['TipoDoc'] . "' AND ch_fac_seriedocumento='" . $registroid['SerieDoc'] . "' AND ch_fac_numerodocumento='" . $registroid['NroDoc'] . "' AND cli_codigo='" . $registroid['CodCliente'] . "' ") >= 0) {
			} else {
				return $sqlca->get_error();
			}

			if ($sqlca->numrows_affected() > 0) {
			} else {
				$sqlca->perform('fac_ta_factura_detalle', $datos, 'insert');
			}
			return OK;
		} else {
			if ($sqlca->perform('fac_ta_factura_detalle', $datos, 'insert') >= 0) {
			} else {
				return $sqlca->get_error();
			}
			return OK;
		}
	}

    function pasaraContado($registroid) {
		global $sqlca;

		$sql = "
			UPDATE
				fac_ta_factura_cabecera
			SET
				ch_fac_credito = 'N'
			WHERE
				ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo = '".$registroid."'
		";

		if ($sqlca->query($sql) < 0)
			return false;
		return true;
	}

    function obtenerRecargoMantenimiento($codigo) {
		global $sqlca;

		$query = "select cli_mantenimiento from int_clientes where cli_codigo='" . $codigo . "'";
		$sqlca->query($query);
		$rs = $sqlca->fetchrow();

		return $rs[0];
    	}

    function obtenerListadePrecios($codigo) {
        global $sqlca;

        $query = "SELECT c.cli_lista_precio, t.tab_descripcion  FROM int_tabla_general t INNER JOIN int_clientes c on substring(c.cli_lista_precio for 2 from 1)=substring(t.tab_elemento for 2 from 1)
				WHERE t.tab_tabla = 'LPRE' and c.cli_codigo='" . $codigo . "'";
        $sqlca->query($query);
        $rs = $sqlca->fetchrow();

        return $rs;
    }

    function obtenerporcDesc($codigo) {
        global $sqlca;

        $query = "SELECT 
				substring(tab.tab_elemento for 2 from length(tab_elemento)-1) AS cod_descuento, 
				tab.tab_descripcion AS des_descuento, 
				round((tab_num_01/100),6) AS porc_descuento, 
				c.cli_estado_desc 
			FROM  
				int_tabla_general tab inner join int_clientes c on c.cli_descuento=substring(tab.tab_elemento for 2 from length(tab_elemento)-1)
			WHERE 
				tab_tabla= 'DESC' AND tab_elemento<>'000000' and c.cli_codigo='" . $codigo . "'";

        $sqlca->query($query);
        $rs = $sqlca->fetchrow();

        return $rs;
    }

    function obtenerComplementarios($codigo) {
        global $sqlca;

        $query = "SELECT 
				cli_codigo, cli_razsocial, cli_rsocialbreve, cli_grupo, cli_direccion, cli_ruc, cli_moneda, cli_fpago_credito, cli_fecultventa, 
				cli_telefono1, cli_telefono2, cli_telefono3, cli_contacto, cli_email, cli_fecactualiz, cli_estado, cli_trasmision, cli_tipo, 
				cli_creditosol, cli_creditodol, cli_salsol, cli_saldol, flg_replicacion, fecha_replicacion, cli_anticipo, 
				cli_distrito, cli_lista_precio, cli_mantenimiento, cli_descuento 
			FROM 
				int_clientes 
			WHERE 
				cli_codigo='" . $codigo . "'";

        $sqlca->query($query);
        $rs = $sqlca->fetchrow();

        return $rs;
    }

	function guardarRegistroComplemento($datos, $datosComplementarios = array()) {
        	global $sqlca;

		$Complementos['ch_fac_tipodocumento'] 			= $datos['ch_fac_tipodocumento'];
		$Complementos['ch_fac_seriedocumento'] 			= $datos['ch_fac_seriedocumento'];
		$Complementos['ch_fac_numerodocumento'] 		= $datos['ch_fac_numerodocumento'];
		$Complementos['cli_codigo'] 					= $datos['cli_codigo'];
		$Complementos['dt_fac_fecha'] 					= $datos['dt_fac_fecha'];
		$Complementos['ch_fac_observacion1'] 			= $datosComplementarios['obs1'] = FacturasModel::clearText($datosComplementarios['obs1']);
		$Complementos['ch_fac_observacion2'] 			= $datosComplementarios['obs2'];
		$Complementos['ch_fac_observacion3'] 			= $datosComplementarios['obs3'];
		$Complementos['ch_fac_ruc'] 					= $datosComplementarios['ruc'];
		$Complementos['nu_fac_direccion'] 				= $datosComplementarios['direccion'];
		$Complementos['nu_fac_complemento_direccion']	= $datosComplementarios['comp_dir'] = FacturasModel::clearText($datosComplementarios['comp_dir']);
		$Complementos['ch_fac_nombreclie'] 				= $datosComplementarios['razon_social'];
		$Complementos['dt_fechactualizacion'] 			= "now()";

		if ($sqlca->perform('fac_ta_factura_complemento', $Complementos, 'insert') >= 0) {
		} else {
			return $sqlca->get_error();
		}

		return OK;

	}

    function ObtTipoAccContable($TipoDoc) {
        global $sqlca;
        $TipAccCont = $sqlca->functionDB("util_fn_tipo_accion_contable('CC','$TipoDoc')");
        return $TipAccCont;
    }

    function CostoPromedio($FechaDoc, $CodAlm, $CodArt) {
        global $sqlca;
        $Monto = $sqlca->functionDB("util_fn_costo_promedio(to_char(to_date('" . $FechaDoc . "','dd/mm/yyyy'),'yyyy'),to_char(to_date('" . $FechaDoc . "','dd/mm/yyyy'),'mm'),'" . $CodArt . "',lpad('" . $CodAlm . "',3,'0'))");
        return $Monto;
    }

    	function guardarRegistroCcobCabecera($datos) {
        	global $sqlca;

        	if ($datos['ch_fac_credito'] != 'N') {

            $CcobCabecera['ch_tipdocumento'] = $datos['ch_fac_tipodocumento'];
            $CcobCabecera['ch_seriedocumento'] = $datos['ch_fac_seriedocumento'];
            $CcobCabecera['ch_numdocumento'] = $datos['ch_fac_numerodocumento'];
            $CcobCabecera['cli_codigo'] = $datos['cli_codigo'];
            $CcobCabecera['ch_tipcontable'] = FacturasModel::ObtTipoAccContable(trim($datos['ch_fac_tipodocumento']));
            $CcobCabecera['dt_fechaemision'] = $datos['dt_fac_fecha'];
            $CcobCabecera['dt_fecharegistro'] = $datos['dt_fac_fecha'];
            $CcobCabecera['dt_fechavencimiento'] = $datos['dt_fac_fecha'] . "+ interval '" . $_REQUEST['c_dias_pago'] . " day'";
            $CcobCabecera['nu_dias_vencimiento'] = $_REQUEST['c_dias_pago'];
            $CcobCabecera['ch_moneda'] = $datos['ch_fac_moneda'];
            $CcobCabecera['nu_tipocambio'] = $datos['nu_tipocambio'];
            $CcobCabecera['nu_importetotal'] = $datos['nu_fac_valorbruto'] + $datos['nu_fac_impuesto1'];
            $CcobCabecera['nu_importesaldo'] = $datos['nu_fac_valorbruto'] + $datos['nu_fac_impuesto1'];
            $CcobCabecera['dt_fechasaldo'] = $datos['dt_fac_fecha'];
            $CcobCabecera['plc_codigo'] = "-";
            $CcobCabecera['ch_sucursal'] = $datos['ch_almacen'];
            $CcobCabecera['nu_importeafecto'] = $datos['nu_fac_valorbruto'];
            $CcobCabecera['ch_tipoimpuesto1'] = $datos['ch_fac_cd_impuesto1'];
            $CcobCabecera['nu_impuesto1'] = $datos['nu_fac_impuesto1'];

            if (isset($_REQUEST["registroid"]) && $_REQUEST["registroid"] != "") {
                $registroid = FacturasModel::GenerarRegitroID();

                if ($sqlca->perform('ccob_ta_cabecera', $CcobCabecera, 'update', "ch_tipdocumento='" . $registroid['TipoDoc'] . "' AND ch_seriedocumento='" . $registroid['SerieDoc'] . "' AND ch_numdocumento='" . $registroid['NroDoc'] . "' AND cli_codigo='" . $registroid['CodCliente'] . "'") >= 0) {
                    
                } else {
                    return $sqlca->get_error();
                }
                return OK;
            } else {
                if ($sqlca->perform('ccob_ta_cabecera', $CcobCabecera, 'insert') >= 0) {
                    if ($datos['ch_fac_anticipo'] == 'S') {
                        $CcobCabecera['ch_tipdocumento'] = '21';
                        $CcobCabecera['ch_tipcontable'] = 'A';
                        if ($sqlca->perform('ccob_ta_cabecera', $CcobCabecera, 'insert') >= 0) {
                            
                        } else
                            return $sqlca->get_error();
                    }
                } else
                    return $sqlca->get_error();
            }

            return OK;
        }
    }

    function guardarRegistroCcobDetalle($datos) {
        global $sqlca;

        $CcobDetalle['ch_tipdocumento'] = $datos['ch_fac_tipodocumento'];
        $CcobDetalle['ch_seriedocumento'] = $datos['ch_fac_seriedocumento'];
        $CcobDetalle['ch_numdocumento'] = $datos['ch_fac_numerodocumento'];
        $CcobDetalle['cli_codigo'] = $datos['cli_codigo'];
        $CcobDetalle['ch_identidad'] = "1";
        $CcobDetalle['ch_tipmovimiento'] = "1";
        $CcobDetalle['dt_fechamovimiento'] = $datos['dt_fac_fecha'];
        $CcobDetalle['ch_moneda'] = $datos['ch_fac_moneda'];
        $CcobDetalle['nu_tipocambio'] = $datos['nu_tipocambio'];
        $CcobDetalle['nu_importemovimiento'] = $datos['nu_fac_valorbruto'] + $datos['nu_fac_impuesto1'];
        $CcobDetalle['plc_codigo'] = "-";
        $CcobDetalle['ch_sucursal'] = $datos['ch_almacen'];
        $CcobDetalle['dt_fecha_actualizacion'] = "now()";

        if (isset($_REQUEST["registroid"]) && $_REQUEST["registroid"] != "") {
            $registroid = FacturasModel::GenerarRegitroID();
            if ($sqlca->perform('ccob_ta_detalle', $CcobDetalle, 'update', "trim(ch_tipdocumento)='" . $registroid['TipoDoc'] . "' AND trim(ch_seriedocumento)='" . $registroid['SerieDoc'] . "' AND trim(ch_numdocumento)='" . $registroid['NroDoc'] . "' AND trim(cli_codigo)='" . $registroid['CodCliente'] . "'") >= 0) {
                
            } else {
                return $sqlca->get_error();
            }
            return OK;
        } else {
            if ($sqlca->perform('ccob_ta_detalle', $CcobDetalle, 'insert') >= 0) {
                if ($datos['ch_fac_anticipo'] == 'S') {
                    $CcobDetalle['ch_tipdocumento'] = '21';
                    if ($sqlca->perform('ccob_ta_detalle', $CcobDetalle, 'insert') >= 0) {
                        
                    }else
                        return $sqlca->get_error();
                }
            }else
                return $sqlca->get_error();

            return OK;
        }
    }

	function recuperarRegistro($registroid) {
        global $sqlca;

        $registro = array();

        $sql = "
		SELECT 
	        dt_fac_fecha as dt_fac_fecha, 
	        ch_fac_tipodocumento||trim(ch_fac_seriedocumento)||ch_fac_numerodocumento||trim(cli_codigo) as registroid, 
			trim(ch_fac_tipodocumento) as ch_fac_tipodocumento, 
			trim(ch_fac_seriedocumento) as ch_fac_seriedocumento, 
			trim(ch_fac_numerodocumento) as ch_fac_numerodocumento, 
			trim(cli_codigo) as cli_codigo, 
			trim(ch_almacen) as ch_almacen, 
			trim(ch_fac_moneda) as ch_fac_moneda, 
			round(nu_tipocambio,2) as nu_tipocambio, 
			ch_fac_credito, 
			ch_fac_forma_pago, 
			ch_factipo_descuento1,
			nu_fac_recargo2,  
			nu_fac_descuento1, 
			ch_fac_cd_impuesto1, 
			ch_fac_anticipo, 
			nu_fac_valorbruto, 
			nu_fac_impuesto1, 
			nu_fac_valortotal, 
			nu_fac_impuesto2, 
			ch_descargar_stock,
			ch_fac_tiporecargo3,
			fecha_replicacion,
			ch_fac_tiporecargo2,
			ch_fac_cd_impuesto3,
			TO_CHAR(fe_vencimiento, 'DD/MM/YYYY') AS fe_vencimiento,
			nu_tipo_pago
		FROM 
			fac_ta_factura_cabecera 
		WHERE
	    	ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo = '".$registroid."'
		";

		$sqlca->query($sql);

		while ($reg = $sqlca->fetchRow())
			$registro = $reg;

		return $registro;
	}

	function recuperarArticulos($registroid) {
	    global $sqlca;


        $sql = "
		SELECT 
			trim(det.art_codigo) as cod_articulo, 
			det.ch_art_descripcion as desc_articulo, 
			nu_fac_cantidad as cant_articulo, 
			round(nu_fac_precio,2) as precio_articulo, 
			round(nu_fac_importeneto,2) as neto_articulo, 
			round(nu_fac_impuesto1,2) as igv_articulo, 
			round(nu_fac_valortotal,2) as total_articulo, 
			nu_fac_descuento1 as dscto_articulo, 
			det.pre_lista_precio 
        FROM
        	fac_ta_factura_detalle det
        	LEFT JOIN int_articulos art ON(det.art_codigo = art.art_codigo)
        WHERE
      		ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo = '".$registroid."'
        ORDER BY
        	det.art_codigo;
		";

		$sqlca->query($sql);
		$registros = array();
		$x = 1;

		while ($reg = $sqlca->fetchRow()) {
			$registros[$x] = $reg;
			$x++;
		}

		return $registros;

	}

	function recuperarComplemento($registroid) {
        global $sqlca;

        $sql = "
		SELECT 
			trim(cli.cli_razsocial) as cli_razsocial, 
			dt_fac_fecha, 
			ch_fac_observacion1, 
			ch_fac_observacion2, 
			ch_fac_observacion3, 
			ch_fac_ruc, 
			nu_fac_direccion, 
			nu_fac_complemento_direccion 
	    FROM 
	       	fac_ta_factura_complemento com
			LEFT JOIN int_clientes cli ON (com.cli_codigo = cli.cli_codigo)
	    WHERE
			com.ch_fac_tipodocumento||com.ch_fac_seriedocumento||com.ch_fac_numerodocumento||com.cli_codigo = '".$registroid."';
		";

    	$sqlca->query($sql);

    	$registros	= array();
    	$reg		= $sqlca->fetchRow();

		if ($sqlca->numrows() > 0) {
			$registros["razon_social"]	= $reg["cli_razsocial"];
			$registros["direccion"]		= $reg["nu_fac_direccion"];
			$registros["ruc"]			= $reg["ch_fac_ruc"];
			$registros["comp_dir"]		= $reg["nu_fac_complemento_direccion"];
			$registros["obs1"]			= $reg["ch_fac_observacion1"];
			$registros["obs2"]			= $reg["ch_fac_observacion2"];
			$registros["obs3"]			= $reg["ch_fac_observacion3"];
		}

		return $registros;

	}

	function eliminarRegistro($registroid, $forma_eliminar) {
       	global $sqlca;

		$Valores 	= explode(' ', $registroid);
		$cod_docu 	= trim($Valores[0]);

		$query = "SELECT substring(TRIM(tab_elemento) for 2 FROM length(TRIM(tab_elemento))-1) FROM int_tabla_general WHERE tab_tabla = '08' AND tab_desc_breve = '" . $cod_docu. "'";
		$sqlca->query($query);
		$a 			= $sqlca->fetchRow();
		$cod_docu 	= trim($a[0]);

		$serie_docu 	= trim($Valores[1]);
		$num_docu 		= trim($Valores[2]);
		$cod_cliente 	= $Valores[3];

		if(isset($Valores[4]))
			$cod_cliente = $cod_cliente.' '.$Valores[4];

		$VARI = '';

		$q = "
		SELECT
			ch_almacen AS Nu_Almacen,
			to_char(date(dt_fac_fecha), 'DD/MM/YYYY') AS Fe_Emision
		FROM
			fac_ta_factura_cabecera
		WHERE
			ch_fac_tipodocumento		= '" . $cod_docu. "'
			AND ch_fac_seriedocumento	= '" . $serie_docu. "'
			AND ch_fac_numerodocumento	= '" . $num_docu. "'
			AND cli_codigo 				= '". $cod_cliente. "';
		";

		if ($sqlca->query($q) <= 0)
			return false;

        $row 	= $sqlca->fetchRow();
        $flag 	= FacturasModel::validaDia($row['nu_almacen'], $row['fe_emision']);

		if ($flag == 1) {
			?><script>alert("<?php echo 'No se puede eliminar. Fecha ya consolidada!'; ?> ");</script><?php
		} else {

			//Ver los vales a eliminar amarrado a la factura

			$sql_verificacion = "
				SELECT
					accion,
					fecha_liquidacion,
					cod_hermandad
				FROM
					val_ta_complemento_documento
				WHERE 
                    ch_fac_tipodocumento		='$cod_docu' 
                    AND ch_fac_seriedocumento	='$serie_docu' 
                    AND ch_fac_numerodocumento	='$num_docu' 
                    AND ch_cliente='$cod_cliente'
				LIMIT 1;
			";

			echo "Verificacion:\n".$sql_verificacion;

			$proceso = true;

			if ($sqlca->query($sql_verificacion) <= 0)
		        	$proceso = false;

			if ($proceso) {

				$reg = $sqlca->fetchRow();

				if (strcmp($reg['accion'], "XPLACA") == 0 || strcmp($reg['accion'], "XPRODUCTO") == 0) {

				    	try {
						$sqlca->query("BEGIN");
						$array_notas_despacho = array();
						$array_notas_facturas = array();
						$cod_hermandad = $reg['cod_hermandad'];
						$sql_notas_des = "SELECT trim(ch_numeval) as ch_numeval,ch_fac_tipodocumento,ch_fac_seriedocumento , ch_fac_numerodocumento   FROM val_ta_complemento_documento WHERE cod_hermandad='$cod_hermandad'  AND ch_cliente='$cod_cliente';";
						if ($sqlca->query($sql_notas_des) <= 0) {
						    throw new Exception("Error Select ");
						}
						while ($reg = $sqlca->fetchRow()) {
						    $array_notas_despacho[] = $reg['ch_numeval'];
						    $ch_fac_numerodocumento = trim($reg['ch_fac_numerodocumento']);
						    $array_notas_facturas['ID-' . $ch_fac_numerodocumento] = trim($reg['ch_fac_tipodocumento']) . "-" . trim($reg['ch_fac_seriedocumento']) . "-" . trim($reg['ch_fac_numerodocumento']);
						}
						//************LLENAMOS LAS FACTURAS
						$vales_actualizar = "('" . implode("','", $array_notas_despacho) . "')";


						foreach ($array_notas_facturas as $key => $valores) {
						    $datos_fac = explode("-", $valores);
						    $VARI = $sqlca->functionDB("ventas_fn_eliminacion_documentos('$datos_fac[0]','$datos_fac[1]','$datos_fac[2]','$cod_cliente','$forma_eliminar','ELIMINACION')");

						    $sql_delete = "DELETE  FROM val_ta_complemento_documento where trim(ch_cliente)=trim('$cod_cliente') AND trim(cod_hermandad)=trim('$cod_hermandad')
						            AND trim(ch_fac_tipodocumento)=trim('$datos_fac[0]') AND  trim(ch_fac_seriedocumento) =trim('$datos_fac[1]') AND  trim(ch_fac_numerodocumento)=trim('$datos_fac[2]');";

						    if ($sqlca->query($sql_delete) < 0) {
						        throw new Exception("Error Dele factura" . $sql_delete);
						    }
						}
						//aca se actualiza los vales.
						if($forma_eliminar == "2" || $forma_eliminar == 2){
							$sql_update = "update val_ta_cabecera set ch_liquidacion = NULL where ch_documento in $vales_actualizar  AND trim(ch_cliente)=trim('$cod_cliente');";

							if ($sqlca->query($sql_update) < 0) {
								throw new Exception("Error update" . $sql_update);
							}

						}
							$sqlca->query("COMMIT");

					} catch (Exception $e) {
						echo $e->getMessage();
						$sqlca->query("ROLLBACK");
						?> <script>alert("<?php echo 'Error en eliminacion de factura'; ?> ");</script><?php
						return false;
				    	}

				} else if (strcmp($reg['accion'], "XNORMAL") == 0 || strcmp($reg['accion'], "XNOTADES") == 0) {

					try {

						$sqlca->query("BEGIN");

						$array_notas_despacho 	= array();
						$array_notas_facturas 	= array();
						$cod_hermandad		= $reg['cod_hermandad'];

						$sql_notas_des = "
								SELECT
									trim(ch_numeval) as ch_numeval,
									ch_fac_tipodocumento,
									ch_fac_seriedocumento,
									ch_fac_numerodocumento
								FROM
									val_ta_complemento_documento
								WHERE
									trim(cod_hermandad) 			= trim('$cod_hermandad')
									AND ch_cliente 				= '$cod_cliente'
									AND trim(ch_fac_tipodocumento) 		= trim('$cod_docu')
									AND trim(ch_fac_seriedocumento) 	= trim('$serie_docu')
									AND trim(ch_fac_numerodocumento) 	= trim('$num_docu');
						";

						echo "\nVerificar: \n".$sql_notas_des;

						if ($sqlca->query($sql_notas_des) <= 0)
							throw new Exception("Error Select ");

						while ($reg = $sqlca->fetchRow()) {

							$array_notas_despacho[] = $reg['ch_numeval'];
							$ch_fac_numerodocumento = trim($reg['ch_fac_numerodocumento']);
							$array_notas_facturas['ID-' . $ch_fac_numerodocumento] = trim($reg['ch_fac_tipodocumento']) . "-" . trim($reg['ch_fac_seriedocumento']) . "-" . trim($reg['ch_fac_numerodocumento']);

						}

						//************LLENAMOS LAS FACTURAS
						$vales_actualizar = "('" . implode("','", $array_notas_despacho) . "')";

						//ACTUALIZAR LOS VALES
						if($forma_eliminar == "2" || $forma_eliminar == 2){

							$sql_update = "UPDATE val_ta_cabecera SET ch_liquidacion = NULL WHERE ch_documento IN $vales_actualizar AND trim(ch_cliente) = trim('$cod_cliente');";

							echo "\nActualizar Soltando Vales: \n".$sql_update;

							if ($sqlca->query($sql_update) < 0)
								throw new Exception("Error update" . $sql_update);

						}

						foreach ($array_notas_facturas as $key => $valores) {

					    		$datos_fac = explode("-", $valores);

					    		$VARI = $sqlca->functionDB("ventas_fn_eliminacion_documentos('$datos_fac[0]','$datos_fac[1]','$datos_fac[2]','$cod_cliente','$forma_eliminar','ELIMINACION')");

					    		$sql_delete = "
									DELETE FROM
										val_ta_complemento_documento
									WHERE
										trim(cod_hermandad)		= trim('$cod_hermandad')
										AND ch_cliente			= '$cod_cliente'
										AND trim(ch_fac_tipodocumento)	= trim('$datos_fac[0]')
										AND trim(ch_fac_seriedocumento) = trim('$datos_fac[1]')
										AND trim(ch_fac_numerodocumento)= trim('$datos_fac[2]');
							";

							echo "\n Eliminar Soltando Vales: \n".$sql_delete;

							if ($sqlca->query($sql_delete) < 0)
								throw new Exception("Error eliminar Liquidacion Soltando Vales" . $sql_delete);

						}

						$sqlca->query("COMMIT");

					} catch (Exception $e) {
						echo $e->getMessage();
						$sqlca->query("ROLLBACK");
						?> <script>alert("<?php echo 'Error en eliminacion tipo: N.D Normal de factura tiene documentos por cobrar'; ?> ");</script><?php
						return false;
					}
				}

			} else {
				$VARI = $sqlca->functionDB("ventas_fn_eliminacion_documentos('$cod_docu','$serie_docu','$num_docu','$cod_cliente','$forma_eliminar','ELIMINACION')");
			}

			if ($VARI == '') {
				return "El documento presenta cancelaciones o eliminaciones en cuentas por cobrar";
			}

			return 'OK';

		}

	}

	function anulacionRegistro($registroid) {//anular factura
        global $sqlca;

        $Valores 	= explode(' ', $registroid);
        $cod_docu 	= trim($Valores[0]);

		$query 		= "SELECT substring(TRIM(tab_elemento) for 2 FROM length(TRIM(tab_elemento))-1) FROM int_tabla_general WHERE tab_tabla = '08' AND tab_desc_breve = '" . $cod_docu. "'";	

		$sqlca->query($query);
		$a 			= $sqlca->fetchRow();

		$cod_docu 		= $a[0];
		$serie_docu 	= trim($Valores[1]);
		$num_docu 		= trim($Valores[2]);
		$cod_cliente	= trim($Valores[3]) . " " . trim($Valores[4]);
		$forma_eliminar = $_REQUEST['forma_eliminar'];

		$q = "
		SELECT
			ch_almacen AS Nu_Almacen,
			to_char(date(dt_fac_fecha), 'DD/MM/YYYY') AS Fe_Emision
		FROM
			fac_ta_factura_cabecera
		WHERE
			ch_fac_tipodocumento 		= '" . $cod_docu . "'
			AND ch_fac_seriedocumento 	= '" . $serie_docu . "'
			AND ch_fac_numerodocumento 	= '" . $num_docu . "'
			AND cli_codigo 				= '" . $cod_cliente . "';
		";

		if ($sqlca->query($q) <= 0)
			return false;

        $row 	= $sqlca->fetchRow();
        $flag 	= FacturasModel::validaDia($row['Nu_Almacen'], $row['Fe_Emision']);

        if ($flag == 1) {
            ?><script>alert("<?php echo 'No se puede anular registro. Fecha ya consolidada!'; ?> ");</script><?php
        } else {
            
            //////////////////////////
            $sql_verificacion = "SELECT accion,fecha_liquidacion,cod_hermandad  FROM val_ta_complemento_documento WHERE 
                                        ch_fac_tipodocumento='$cod_docu' 
                                        AND ch_fac_seriedocumento='$serie_docu' 
                                        AND ch_fac_numerodocumento='$num_docu' 
                                        AND ch_cliente='$cod_cliente' LIMIT 1;";
            $proceso = true;
            if ($sqlca->query($sql_verificacion) <= 0) {
                $proceso = false;
            }
            if ($proceso) {
                $reg = $sqlca->fetchRow();
                if (strcmp($reg['accion'], "XPLACA") == 0 || strcmp($reg['accion'], "XPRODUCTO") == 0) {
                    try {
                        $sqlca->query("BEGIN");
                        $array_notas_despacho = array();
                        $array_notas_facturas = array();
                        $cod_hermandad = $reg['cod_hermandad'];
                        $sql_notas_des = "SELECT trim(ch_numeval) as ch_numeval,ch_fac_tipodocumento,ch_fac_seriedocumento , ch_fac_numerodocumento   FROM val_ta_complemento_documento WHERE cod_hermandad='$cod_hermandad'  AND ch_cliente='$cod_cliente';";
                        if ($sqlca->query($sql_notas_des) <= 0) {
                            throw new Exception("Error Select ");
                        }
                        while ($reg = $sqlca->fetchRow()) {
                            $array_notas_despacho[] = $reg['ch_numeval'];
                            $ch_fac_numerodocumento = trim($reg['ch_fac_numerodocumento']);
                            $array_notas_facturas['ID-' . $ch_fac_numerodocumento] = trim($reg['ch_fac_tipodocumento']) . "-" . trim($reg['ch_fac_seriedocumento']) . "-" . trim($reg['ch_fac_numerodocumento']);
                        }
                        //************LLENAMOS LAS FACTURAS
                        $vales_actualizar = "('" . implode("','", $array_notas_despacho) . "')";


                        foreach ($array_notas_facturas as $key => $valores) {
                            $datos_fac = explode("-", $valores);
                            
                            $VARI = $sqlca->functionDB("ventas_fn_eliminacion_documentos('$datos_fac[0]','$datos_fac[1]','$datos_fac[2]','$cod_cliente','$forma_eliminar','ANULACION')");

                            $sql_delete = "DELETE  FROM val_ta_complemento_documento where trim(ch_cliente)=trim('$cod_cliente') AND trim(cod_hermandad)=trim('$cod_hermandad')
                                    AND trim(ch_fac_tipodocumento)=trim('$datos_fac[0]') AND  trim(ch_fac_seriedocumento) =trim('$datos_fac[1]') AND  trim(ch_fac_numerodocumento)=trim('$datos_fac[2]');";

                            if ($sqlca->query($sql_delete) < 0) {
                                throw new Exception("Error Dele factura" . $sql_delete);
                            }
                        }
                        //aca se actualiza los vales.
                     	if($forma_eliminar == "2" || $forma_eliminar == 2){
							$sql_update = "update val_ta_cabecera set ch_liquidacion = NULL where ch_documento in $vales_actualizar  AND trim(ch_cliente)=trim('$cod_cliente');";

							if ($sqlca->query($sql_update) < 0) {
								throw new Exception("Error update" . $sql_update);
							}

						}
							$sqlca->query("COMMIT");


                    } catch (Exception $e) {
                        echo $e->getMessage();
                        $sqlca->query("ROLLBACK");
                        ?> <script>alert("<?php echo 'Error en eliminacion de factura'; ?> ");</script><?php
                        return false;
                    }
                } else if (strcmp($reg['accion'], "XNORMAL") == 0 || strcmp($reg['accion'], "XNOTADES") == 0) {
                    try {
                        $sqlca->query("BEGIN");
                        $array_notas_despacho = array();
                        $array_notas_facturas = array();
                        $cod_hermandad = $reg['cod_hermandad'];

                        $sql_notas_des = "SELECT trim(ch_numeval) as ch_numeval,ch_fac_tipodocumento,ch_fac_seriedocumento , ch_fac_numerodocumento   FROM val_ta_complemento_documento WHERE trim(cod_hermandad)=trim('$cod_hermandad')  AND ch_cliente='$cod_cliente'
                      AND  trim(ch_fac_tipodocumento)=trim('$cod_docu') AND trim(ch_fac_seriedocumento)=trim('$serie_docu') AND  trim(ch_fac_numerodocumento)=trim('$num_docu');";

                        if ($sqlca->query($sql_notas_des) <= 0) {
                            throw new Exception("Error Select ");
                        }
                        while ($reg = $sqlca->fetchRow()) {
                            $array_notas_despacho[] = $reg['ch_numeval'];
                            $ch_fac_numerodocumento = trim($reg['ch_fac_numerodocumento']);
                            $array_notas_facturas['ID-' . $ch_fac_numerodocumento] = trim($reg['ch_fac_tipodocumento']) . "-" . trim($reg['ch_fac_seriedocumento']) . "-" . trim($reg['ch_fac_numerodocumento']);
                        }
                        //************LLENAMOS LAS FACTURAS
                        $vales_actualizar = "('" . implode("','", $array_notas_despacho) . "')";


                        foreach ($array_notas_facturas as $key => $valores) {
                            $datos_fac = explode("-", $valores);
                            $VARI = $sqlca->functionDB("ventas_fn_eliminacion_documentos('$datos_fac[0]','$datos_fac[1]','$datos_fac[2]','$cod_cliente','$forma_eliminar','ANULACION')");

                            $sql_delete = "DELETE  FROM val_ta_complemento_documento where trim(ch_cliente)=trim('$cod_cliente') AND trim(cod_hermandad)=trim('$cod_hermandad')
                                    AND trim(ch_fac_tipodocumento)=trim('$datos_fac[0]') AND  trim(ch_fac_seriedocumento) =trim('$datos_fac[1]') AND  trim(ch_fac_numerodocumento)=trim('$datos_fac[2]');";

                            if ($sqlca->query($sql_delete) < 0) {
                                throw new Exception("Error Dele factura" . $sql_delete);
                            }
                        }
                        //aca se actualiza los vales.
                       if($forma_eliminar == "2" || $forma_eliminar == 2){
							$sql_update = "update val_ta_cabecera set ch_liquidacion = NULL where ch_documento in $vales_actualizar  AND trim(ch_cliente)=trim('$cod_cliente');";

							if ($sqlca->query($sql_update) < 0) {
								throw new Exception("Error update" . $sql_update);
							}

						}
							$sqlca->query("COMMIT");



                    } catch (Exception $e) {
                        echo $e->getMessage();
                        $sqlca->query("ROLLBACK");
                        ?> <script>alert("<?php echo 'Error en eliminacion de factura'; ?> ");</script><?php
                        return false;
                    }
                }
            } else {
                $VARI = $sqlca->functionDB("ventas_fn_eliminacion_documentos('$cod_docu','$serie_docu','$num_docu','$cod_cliente','$forma_eliminar','ANULACION')");
                }
            ////}
            
            //////////////////////////////
            
            
            
            
            if ($VARI == '') {
                return "El documento presenta cancelaciones o eliminaciones en cuentas por cobrar";
            }
            
            
            return 'OK';
        }
    }

    function VerificaMontos($registroid) {
        global $sqlca;

        $query = "SELECT nu_importetotal, nu_importesaldo FROM ccob_ta_cabecera 
				WHERE trim(ch_tipdocumento)||trim(ch_seriedocumento)||trim(ch_numdocumento)||trim(cli_codigo)='" . trim($registroid) . "' 
		        	AND nu_importetotal != nu_importesaldo";
        $sqlca->query($query);
        $numrows = $sqlca->numrows();

        return $numrows;
    }

    function eliminarArticuloDet($registroid, $articuloid) {
        global $sqlca;

        if ($sqlca->perform('fac_ta_factura_detalle  ', ' ', 'delete', "ch_fac_tipodocumento='" . $registroid['TipoDoc'] . "' AND ch_fac_seriedocumento='" . $registroid['SerieDoc'] . "' AND ch_fac_numerodocumento='" . $registroid['NroDoc'] . "' AND cli_codigo='" . $registroid['CodCliente'] . "' AND trim(art_codigo)='trim($articuloid)'") >= 0) {
            
        } else {
            return $sqlca->get_error();
        }

        return OK;
    }

	function tmListado($f_desde, $f_hasta, $codigo, $buscar_tipo, $turno) {
        	global $sqlca;

        	$cond = '';

		if ($codigo != "")
			$cond = " AND trim(f.cli_codigo)||trim(c.cli_rsocialbreve)||trim(f.ch_fac_seriedocumento)||trim(f.ch_fac_numerodocumento) ~ '" . $codigo . "' ";

		if ($f_desde != "" && $f_hasta != "") {

			$fechaDesdeDiv = explode('/', $f_desde);
			$fechaHastaDiv = explode('/', $f_hasta);
			$f_desde = $fechaDesdeDiv[2] . "-" . $fechaDesdeDiv[1] . "-" . $fechaDesdeDiv[0];
			$f_hasta = $fechaHastaDiv[2] . "-" . $fechaHastaDiv[1] . "-" . $fechaHastaDiv[0];
			$cond2 = " AND dt_fac_fecha BETWEEN '" . $f_desde . "' AND '" . $f_hasta . "' ";

        	}

		if ($buscar_tipo != "TODOS")
			$cond3 = " AND trim(f.ch_fac_tipodocumento) = '$buscar_tipo' ";

		if ($turno != "0")
			$cond4 = " AND trim(f.ch_fac_tiporecargo3) = '$turno' ";

        	$sql = "
			SELECT 
				gen.tab_desc_breve, 
		        f.ch_fac_seriedocumento, 
				f.ch_fac_numerodocumento, 
				f.cli_codigo, 
				c.cli_rsocialbreve, 
				to_char(f.dt_fac_fecha,'dd/mm/yyyy') as dt_fac_fecha, 
				f.ch_punto_venta, 
				f.ch_fac_moneda, 
				f.nu_tipocambio, 
				f.nu_fac_valorbruto, 
				(CASE WHEN f.ch_fac_tiporecargo2 = 'S' AND f.nu_fac_impuesto1 = 0 THEN
					'0.00'
				ELSE
					(CASE WHEN f.nu_fac_descuento1 > 0 THEN
						ROUND(f.nu_fac_impuesto1 - ((f.nu_fac_descuento1 * (1 + (util_fn_igv()/100))) - f.nu_fac_descuento1), 2)
					ELSE
						f.nu_fac_impuesto1
					END)
				END) nu_fac_impuesto1,
				f.nu_fac_descuento1,
				(CASE
					WHEN f.nu_fac_descuento1 > 0 AND f.ch_fac_tiporecargo2 = 'S' AND f.nu_fac_impuesto1 = 0 THEN
						ROUND(f.nu_fac_valortotal - f.nu_fac_descuento1, 2)
					WHEN f.nu_fac_descuento1 > 0 AND f.nu_fac_impuesto1 > 0 THEN
						ROUND(f.nu_fac_valortotal - (f.nu_fac_descuento1 * (1 + (util_fn_igv()/100))), 2)
					ELSE
						ROUND(f.nu_fac_valortotal, 2)
				END) nu_fac_valortotal,
				f.ch_fac_credito, 
				f.ch_fac_anulado,
				f.ch_fac_anticipo, 
				f.ch_liquidacion as liqui, 
				f.ch_fac_cab_identidad, 
				trim(f.ch_fac_tipodocumento)||trim(f.ch_fac_seriedocumento)||trim(f.ch_fac_numerodocumento)||trim(f.cli_codigo) as codigo, 
				c.cli_mantenimiento, 
				f.nu_fac_recargo2,
				f.ch_fac_tipodocumento,
				f.nu_fac_recargo3 as status,
				(CASE
					WHEN CHAR_LENGTH(ch_fac_seriedocumento) = 3 AND ch_fac_anulado = 'S' THEN 'Anulado'
					WHEN CHAR_LENGTH(ch_fac_seriedocumento) = 3 AND ch_fac_anulado IS NULL THEN 'Completado'
				ELSE
					(CASE
						WHEN f.nu_fac_recargo3 IS NULL OR f.nu_fac_recargo3 = 0 THEN 'Registrado'
						WHEN f.nu_fac_recargo3 = 1 THEN 'Completado'
						WHEN f.nu_fac_recargo3 = 2 THEN 'Anulado'
						WHEN f.nu_fac_recargo3 = 3 THEN 'Completado - Enviado'
						WHEN f.nu_fac_recargo3 = 4 THEN 'Completado - Error'
						WHEN f.nu_fac_recargo3 = 5 THEN 'Anulado - Enviado'
						WHEN f.nu_fac_recargo3 = 6 THEN 'Anulado - Error'
					END)
				END) AS statusname,
				ch_almacen AS codalmacen,
				SUBSTRING(ch_fac_seriedocumento FROM '[A-Z]+') AS nofe,
				par.par_valor AS nuexonerado
		FROM 
	      	fac_ta_factura_cabecera f
			LEFT JOIN int_tabla_general AS gen ON(f.ch_fac_tipodocumento = substring(TRIM(tab_elemento) for 2 from length(TRIM(tab_elemento))-1) and tab_tabla ='08')
	       	LEFT JOIN int_clientes AS c ON (f.cli_codigo = c.cli_codigo )
			LEFT JOIN (SELECT par_valor FROM int_parametros WHERE par_nombre='taxoptional') AS par ON (par.par_valor = '1')
		WHERE
        	f.ch_fac_tipodocumento in ('10','20','35','11') 
         	" . $cond . " " . $cond2 . " " . $cond3 . " " . $cond4 . " 
        ORDER BY 
        	f.ch_fac_tipodocumento, 
        	f.ch_fac_seriedocumento, 
        	f.ch_fac_numerodocumento
        ";

		//echo $sql;

        $resultado_1 	= $sqlca->query($sql);
		$numrows 		= $sqlca->numrows();

		if ($pp && $pagina)
			$paginador = new paginador($numrows, $pp, $pagina);
		else
			$paginador = new paginador($numrows, 100, 0);

		$listado2['partir']		= $paginador->partir();
		$listado2['fin']		= $paginador->fin();
		$listado2['numero_paginas']	= $paginador->numero_paginas();
		$listado2['pagina_previa']	= $paginador->pagina_previa();
		$listado2['pagina_siguiente']	= $paginador->pagina_siguiente();
		$listado2['pp']			= $paginador->pp;
		$listado2['paginas']		= $paginador->paginas();
		$listado2['primera_pagina']	= $paginador->primera_pagina();
		$listado2['ultima_pagina']	= $paginador->ultima_pagina();

		if ($sqlca->query($sql) <= 0)
			return $sqlca->get_error();

		$listado = array();

		while ($reg = $sqlca->fetchRow())
			$listado['datos'][] = $reg;

		$listado['paginacion'] = $listado2;

		return $listado;

	}

	function tmListadoExcel($filtro = array(), $pp, $pagina, $buscar_tipo, $turno) {
        	global $sqlca;

        	$cond = '';

		if ($filtro["codigo"] != "")
			$cond = "AND trim(f.cli_codigo)||trim(c.cli_rsocialbreve)||trim(f.ch_fac_tipodocumento)||trim(f.ch_fac_seriedocumento)||trim(f.ch_fac_numerodocumento) ~ '" . $filtro["codigo"] . "' ";

		if ($filtro["f_desde"] != "" && $filtro["f_hasta"] != "") {
			$fechaDesdeDiv = explode('/', $filtro["f_desde"]);
			$fechaHastaDiv = explode('/', $filtro["f_hasta"]);
			$filtro['f_desde'] = $fechaDesdeDiv[2] . "-" . $fechaDesdeDiv[1] . "-" . $fechaDesdeDiv[0];
			$filtro['f_hasta'] = $fechaHastaDiv[2] . "-" . $fechaHastaDiv[1] . "-" . $fechaHastaDiv[0];
			$cond2 = "AND dt_fac_fecha BETWEEN '" . $filtro["f_desde"] . "' AND '" . $filtro["f_hasta"] . "' ";
		}

		if ($buscar_tipo != "TODOS")
			$cond3 = "AND trim(f.ch_fac_tipodocumento)='$buscar_tipo' ";

		if ($turno != "0")
			$cond4 = " AND trim(f.ch_fac_tiporecargo3) = '$turno' ";

		$query = "
			SELECT 
				f.ch_fac_tipodocumento, 
				f.ch_fac_seriedocumento, 
				f.ch_fac_numerodocumento, 
				f.cli_codigo, 
				d.art_codigo,
				c.cli_rsocialbreve, 
				to_char(f.dt_fac_fecha,'dd/mm/yyyy') as dt_fac_fecha, 
				f.ch_punto_venta, 
				f.ch_fac_moneda, 
				f.nu_tipocambio, 
				f.nu_fac_valorbruto, 
				f.nu_fac_impuesto1, 
				f.nu_fac_valortotal,
				f.ch_fac_credito, 
				f.ch_fac_anulado,
				f.ch_fac_anticipo, 
				f.ch_liquidacion as liqui, 
				d.nu_fac_cantidad ,
				d.nu_fac_importeneto,
				d.nu_fac_impuesto1,
				d.nu_fac_valortotal, 
				f.ch_fac_cab_identidad, 
				trim(f.ch_fac_tipodocumento)||trim(f.ch_fac_seriedocumento)||trim(f.ch_fac_numerodocumento)||trim(f.cli_codigo) as codigo, 
				c.cli_mantenimiento, 
				f.nu_fac_recargo2 
                	FROM 
				fac_ta_factura_cabecera f
				LEFT JOIN fac_ta_factura_detalle d ON (f.ch_fac_tipodocumento=d.ch_fac_tipodocumento AND f.ch_fac_seriedocumento=d.ch_fac_seriedocumento AND f.ch_fac_numerodocumento=d.ch_fac_numerodocumento)
				LEFT JOIN int_clientes c ON (f.cli_codigo = c.cli_codigo)
			WHERE
				f.ch_fac_tipodocumento in ('10','20','35','11') 
				" . $cond . " " . $cond2 . " " . $cond3 . "  " . $cond4 . " 
			ORDER BY
				f.ch_fac_tipodocumento, 
				f.ch_fac_seriedocumento, 
				f.ch_fac_numerodocumento
			";

                    //echo $query;

        $resultado_1 = $sqlca->query($query);
        $numrows = $sqlca->numrows();

        if ($pp && $pagina) {
            $paginador = new paginador($numrows, $pp, $pagina);
        } else {
            $paginador = new paginador($numrows, 100, 0);
        }

        $listado2['partir'] = $paginador->partir();
        $listado2['fin'] = $paginador->fin();
        $listado2['numero_paginas'] = $paginador->numero_paginas();
        $listado2['pagina_previa'] = $paginador->pagina_previa();
        $listado2['pagina_siguiente'] = $paginador->pagina_siguiente();
        $listado2['pp'] = $paginador->pp;
        $listado2['paginas'] = $paginador->paginas();
        $listado2['primera_pagina'] = $paginador->primera_pagina();
        $listado2['ultima_pagina'] = $paginador->ultima_pagina();

        if ($sqlca->query($query) <= 0) {
            return $sqlca->get_error();
        }

        $listado = array();
        while ($reg = $sqlca->fetchRow()) {
            $listado['datos'][] = $reg;
        }
        $listado['paginacion'] = $listado2;

        return $listado;
    }





    function ListadosVarios($Dato) {
        global $sqlca;

        $sqlca->query("BEGIN");
        $sqlca->functionDB("util_fn_combos('" . $Dato . "','ret')");
        $sqlca->query("FETCH ALL IN ret", 'registros');
        $sqlca->query("CLOSE ret");
        $sqlca->query("END");
        $cbArray = array();
        $x = 0;
        while ($reg = $sqlca->fetchRow('registros')) {
            if ($reg[0] != "000000") {
                $cbArray[trim($reg[0])] = trim($reg[0]) . " " . $reg[1];
            }
        }
        ksort($cbArray);

        return $cbArray;
    }

    function TiposSeriesCBArray($condicion = '', $codigo) {
        global $sqlca;

        $cbArray = array();

        $query = "
        	SELECT 
	        	trim(doc.num_seriedocumento) as serie, 
	        	trim(doc.num_descdocumento) AS descripcion, 
	        	trim(doc.num_tipdocumento) as cod_documento, 
	        	lpad(to_char((cast(trim(doc.num_numactual) as integer)+1), 'FM9999999'),7,'0') as numactual ,
		   		trim(doc.ch_almacen) || ' - ' || trim(ts.ch_nombre_almacen) as almacen,
		   		doc.num_fecactualiz as fechafin 
	        FROM 
	        	int_num_documentos doc 
	         	LEFT JOIN inv_ta_almacenes ts ON (doc.ch_almacen = ts.ch_almacen)
	        WHERE  
	        	trim(doc.num_tipdocumento) = '" . $codigo . "'" .
            	$query .= ($condicion != '' ? ' AND ' . $condicion : '') . ' 
	        ORDER BY
	        	doc.num_descdocumento'
		;

		//echo $query;

        if ($sqlca->query($query) <= 0)
            return $cbArray;

        while ($result = $sqlca->fetchRow()) {
            $cbArray['Datos'][trim($result["serie"])] = $result["descripcion"];
            $cbArray['Numeros'][trim($result["serie"])] = $result["numactual"];
            $cbArray['Almacen'][trim($result["serie"])] = $result["almacen"];
            $cbArray['Fechafin'][trim($result["serie"])] = $result["fechafin"];
        }

        ksort($cbArray);

        return $cbArray;
    }

    function FormaPagoCBArray($condicion = '', $codigo, $nu_codigo_cliente) {
        global $sqlca;

        $JOIN_cliente = "";

        if($codigo == "S"){
	        $JOIN_cliente = "
	        JOIN (
			SELECT cli_fpago_credito FROM int_clientes WHERE cli_codigo = '" . $nu_codigo_cliente . "'
			) AS CLIFP ON (CLIFP.cli_fpago_credito = SUBSTRING(TRIM(tab_elemento) FOR 2 FROM LENGTH(TRIM(tab_elemento))-1))
	        ";
	    }

        $codigo == "N" ? $codigo = '05' : $codigo = '96';
        $cbArray = array();

        $query = "
    	SELECT
        	substring(tab_elemento for 2 from length(tab_elemento)-1 ) AS tab_elemento, 
        	tab_descripcion, 
        	cast(tab_num_01 as int) as dias 
     	FROM 
     		int_tabla_general
     		" . $JOIN_cliente . "
        WHERE 
        	tab_tabla = '" . $codigo . "' 
        	AND tab_elemento <> '000000'
		";
        //$query .= ($condicion != '' ? ' AND ' . $condicion : '01') . ' ORDER BY 1';

        echo "FP: \n" . $query;

        if ($sqlca->query($query) <= 0)
            return $cbArray;
        while ($result = $sqlca->fetchRow()) {
            $cbArray['Datos'][trim($result["tab_elemento"])] = $result["tab_elemento"] . ' ' . $result["tab_descripcion"];
            $cbArray['Dias'][trim($result["tab_elemento"])] = $result["dias"];
        }
        ksort($cbArray);

        return $cbArray;
    }

    function DescuentosCBArray($condicion = '') {
        global $sqlca;

        $cbArray = array();
        $query = "SELECT 
		        	substring(tab.tab_elemento for 2 from length(tab_elemento)-1 ) AS cod_descuento, 
		        	tab.tab_descripcion AS des_descuento, 
		        	round((tab_num_01/100),6) AS porc_descuento 
		        FROM  
		        	int_tabla_general tab 
		        WHERE 
		        	tab_tabla= 'DESC' 
		        	AND tab_elemento<>'000000' " .
                $query .= ($condicion != '' ? ' AND ' . $condicion : '') . ' 
		        ORDER BY 
		        	des_descuento DESC';

        if ($sqlca->query($query) <= 0)
            return $cbArray;
        while ($result = $sqlca->fetchRow()) {
            $cbArray['Datos'][trim($result["cod_descuento"])] = $result["cod_descuento"] . ' ' . $result["des_descuento"];
            $cbArray['Desc'][trim($result["cod_descuento"])] = $result["porc_descuento"];
        }
        ksort($cbArray);

        return $cbArray;
    }

    function ArticulosCBArray($condicion = '', $codigo) {
        global $sqlca;

        $cbArray = array();
        $query = "SELECT 
		        	art.art_codigo, 
		        	art.art_descripcion, 
		        	fac.pre_precio_act1, art.art_modifica_articulo 
		        FROM 
		        	int_articulos art,
			        fac_lista_precios fac 
		        WHERE 
		        	art.art_codigo=fac.art_codigo 
		        	AND fac.pre_lista_precio= '" . $codigo . "' " .
                $query .= ($condicion != '' ? ' AND ' . $condicion : '') . ' ORDER BY art.art_codigo';

        //print_r($query);

        if ($sqlca->query($query) <= 0)
            return $cbArray;

        while ($result = $sqlca->fetchRow()) {
            $cbArray['DATOS_VER'][trim($result["art_codigo"])] = $result["art_codigo"] . ' ' . $result["art_descripcion"];
            $cbArray['DESCRIPCION'][trim($result["art_codigo"])] = $result["art_descripcion"];
            $cbArray['PRECIO'][trim($result["art_codigo"])] = $result["pre_precio_act1"];
            $cbArray['EDITABLE'][trim($result["art_codigo"])] = $result["art_modifica_articulo"];
        }

        ksort($cbArray);

        return $cbArray;
    }

    function ListaPreciosCBArray($condicion = '') {
        global $sqlca;

        $cbArray = array();
        $query = "SELECT tab_elemento, tab_descripcion  FROM int_tabla_general WHERE tab_tabla = 'LPRE' AND tab_elemento<>'000000'";
        $query .= ($condicion != '' ? ' AND ' . $condicion : '') . ' order by 1';

        if ($sqlca->query($query) <= 0)
            return $cbArray;
        while ($result = $sqlca->fetchRow()) {
            $cbArray[trim($result["tab_elemento"])] = $result["tab_elemento"] . ' ' . $result["tab_descripcion"];
        }
        ksort($cbArray);

        return $cbArray;
    }

    function ClientesCBArray($condicion = '') {
        global $sqlca;

        $cbArray = array();
        $query = "SELECT cli_codigo,cli_razsocial,cli_rsocialbreve FROM int_clientes" .
                $query .= ($condicion != '' ? ' WHERE cli_tipo = \'AC\' AND ' . $condicion : '') . ' ORDER BY 2';
        echo $query;

        if ($sqlca->query($query) <= 0)
            return $cbArray;

        while ($result = $sqlca->fetchRow()) {
            $cbArray[trim($result["cli_codigo"])] = $result["cli_codigo"] . ' ' . $result["cli_rsocialbreve"];
        }

        ksort($cbArray);

        return $cbArray;
    }

	function AgregarArticulo($DatosArray, $contador) {

        	$datos = $_SESSION['ARTICULOS'];

        	if ($DatosArray['codigo'] != '' && trim($DatosArray['cantidad']) != '' && trim($DatosArray['cantidad']) > 0) {

		    	for ($i = 0; $i < $contador + 1; $i++) {
		        	$T = $datos[$i];
		        	if ($T['cod_articulo'] == $DatosArray['codigo'])
		            		$mensaje = "Este articulo ya ha sido ingresado antes";
			}

		    	if (!$mensaje) {
				$datos[$DatosArray['codigo']]['cod_articulo']		= $DatosArray['codigo'];
				$datos[$DatosArray['codigo']]['desc_articulo'] 		= $DatosArray['descripcion'];
				$datos[$DatosArray['codigo']]['cant_articulo'] 		= $DatosArray['cantidad'];
				$datos[$DatosArray['codigo']]['precio_articulo'] 	= $DatosArray['precio'];
				$datos[$DatosArray['codigo']]['neto_articulo'] 		= $DatosArray['neto'];
				$datos[$DatosArray['codigo']]['igv_articulo'] 		= $DatosArray['igv'];
				$datos[$DatosArray['codigo']]['dscto_articulo'] 	= $DatosArray['dscto'];
				$datos[$DatosArray['codigo']]['total_articulo'] 	= $DatosArray['total'];
				$datos[$DatosArray['codigo']]['total_articulo2'] 	= $DatosArray['total2'];
				$datos[$DatosArray['codigo']]['pre_lista_precio'] 	= $_REQUEST['articulos']['pre_lista_precio'];
		    	}

		}

		return $datos;

	}

    function CalcularTotales($DatosArray) {

        $datos = $_SESSION['ARTICULOS'];

        if (empty($datos) && !$_REQUEST["registroid"]) {
            $datos['total_cant_articulo'] += round($DatosArray["cantidad"], 3);
            $datos['total_precio_articulo'] += round($DatosArray["precio"], 3);
            $datos['total_neto_articulo'] += $DatosArray["neto"] * (1 + $_REQUEST['recargo'] / 100);
            $datos['total_total_articulo'] += $DatosArray["total"] * (1 + $_REQUEST['recargo'] / 100);
            $datos['total_total_articulo2'] += $DatosArray["total2"] * (1 + $_REQUEST['recargo'] / 100);
            $datos['total_igv_articulo'] += $DatosArray["igv"] * (1 + $_REQUEST['recargo'] / 100);
            $datos['total_dscto_articulo'] += $DatosArray["dscto"];
            return $datos;
        } elseif (!empty($datos) && $_REQUEST["registroid"]) {
            foreach ($datos as $llave => $DatosSesion) {
                $datos['total_cant_articulo'] += round($DatosSesion["cant_articulo"], 3);
                $datos['total_precio_articulo'] += round($DatosSesion["precio_articulo"], 3);
                $datos['total_neto_articulo'] += $DatosSesion["neto_articulo"] * (1 + $_REQUEST['recargo'] / 100);
                $datos['total_igv_articulo'] += $DatosSesion["igv_articulo"] * (1 + $_REQUEST['recargo'] / 100);
                $datos['total_dscto_articulo'] += $DatosSesion["dscto_articulo"];
                $datos['total_total_articulo'] += $DatosSesion["total_articulo"] * (1 + $_REQUEST['recargo'] / 100);
                $datos['total_total_articulo2'] += $DatosSesion["total_articulo2"] * (1 + $_REQUEST['recargo'] / 100);
            }
            return $datos;
        } elseif (!empty($datos) || $_REQUEST["registroid"]) {
            if (!empty($_REQUEST["registroid"])) {
                $datos = $DatosArray;
            }
            foreach ($datos as $llave => $DatosSesion) {
                $datos['total_cant_articulo'] += round($DatosSesion["cant_articulo"], 3);
                $datos['total_precio_articulo'] += round($DatosSesion["precio_articulo"], 3);
                $datos['total_neto_articulo'] += $DatosSesion["neto_articulo"] * (1 + $_REQUEST['recargo'] / 100);
                $datos['total_igv_articulo'] += $DatosSesion["igv_articulo"] * (1 + $_REQUEST['recargo'] / 100);
                $datos['total_dscto_articulo'] += $DatosSesion["dscto_articulo"];
                $datos['total_total_articulo'] += $DatosSesion["total_articulo"] * (1 + $_REQUEST['recargo'] / 100);
                $datos['total_total_articulo2'] += $DatosSesion["total_articulo2"] * (1 + $_REQUEST['recargo'] / 100);
            }
            return $datos;
        } elseif (empty($datos) || $_REQUEST["registroid"]) {
            if (!empty($_REQUEST["registroid"])) {
                $datos = $DatosArray;
            }
            foreach ($datos as $llave => $DatosSesion) {
                $datos['total_cant_articulo'] += round($DatosSesion["cant_articulo"], 3);
                $datos['total_precio_articulo'] += round($DatosSesion["precio_articulo"], 3);
                $datos['total_neto_articulo'] += $DatosSesion["neto_articulo"] * (1 + $_REQUEST['recargo'] / 100);
                $datos['total_igv_articulo'] += $DatosSesion["igv_articulo"] * (1 + $_REQUEST['recargo'] / 100);
                $datos['total_dscto_articulo'] += $DatosSesion["dscto_articulo"];
                $datos['total_total_articulo'] += $DatosSesion["total_articulo"] * (1 + $_REQUEST['recargo'] / 100);
                $datos['total_total_articulo2'] += $DatosSesion["total_articulo2"] * (1 + $_REQUEST['recargo'] / 100);
            }
            return $datos;
        }
    }

	function TipoAfectacion(){//tipo de operaciones

		$tipoafectacion = Array();

		$tipoafectacion[0] = "Operaciones Gravadas";
		$tipoafectacion[1] = "Transferencia Gratuita";
		$tipoafectacion[2] = "Despacho Perdido";
		$tipoafectacion[3] = "Exonerado";

		return $tipoafectacion;

	}

	function obtieneTurnos(){

		$lado = Array();

		$lado[1] = "1";
		$lado[2] = "2";
		$lado[3] = "3";
		$lado[4] = "4";
		$lado[5] = "5";
		$lado[6] = "6";
		$lado[7] = "7";
		$lado[8] = "8";
		$lado[9] = "9";
		$lado[0] = "Seleccionar";

		return $lado;

	}

	function ActualizarFactura($tipo, $serie, $numero , $cli_codigo, $Nu_Tipo_Pago, $Fe_Emision, $No_Tipo_Credito, $Nu_Dias_Pago, $Fe_Vencimiento, $sTransferenciaGratuita){
		global $sqlca;

		$Nu_Tipo_Pago = trim($Nu_Tipo_Pago);
		$Nu_Tipo_Pago = strip_tags($Nu_Tipo_Pago);

		$sTransferenciaGratuita = trim($sTransferenciaGratuita);
		$sTransferenciaGratuita = strip_tags($sTransferenciaGratuita);

		$Fe_Emision = trim($Fe_Emision);
		$Fe_Emision = strip_tags($Fe_Emision);
		$Fe_Emision = explode("/", $Fe_Emision);
		$Fe_Emision = $Fe_Emision[2] . "-" . $Fe_Emision[1] . "-" . $Fe_Emision[0];

		if($Nu_Tipo_Pago == '06'){//Tipo de pago Crédito
			$Fe_Vencimiento = trim($Fe_Vencimiento);
			$Fe_Vencimiento = strip_tags($Fe_Vencimiento);
			$Fe_Vencimiento = explode("/", $Fe_Vencimiento);
			$Fe_Vencimiento = $Fe_Vencimiento[2] . "-" . $Fe_Vencimiento[1] . "-" . $Fe_Vencimiento[0];
		}else
			$Fe_Vencimiento = $Fe_Emision;

		$sql = "UPDATE fac_ta_factura_detalle SET cli_codigo = '".$cli_codigo."'  WHERE ch_fac_tipodocumento = '".$tipo."' AND ch_fac_seriedocumento = '".$serie."' AND ch_fac_numerodocumento = '".$numero."'";
		if($sqlca->query($sql) < 0)
			return false;

		$sql2 = "UPDATE fac_ta_factura_complemento SET cli_codigo = '".$cli_codigo."'  WHERE ch_fac_tipodocumento = '".$tipo."' AND ch_fac_seriedocumento = '".$serie."' AND ch_fac_numerodocumento = '".$numero."'";
		if($sqlca->query($sql2) < 0)
			return false;

		$sql3 = "UPDATE fac_ta_factura_cabecera SET cli_codigo = '".$cli_codigo."', nu_tipo_pago = '" . $Nu_Tipo_Pago . "', ch_fac_credito = '" . $No_Tipo_Credito . "', ch_fac_forma_pago = '" . $Nu_Dias_Pago . "', fe_vencimiento = '" . $Fe_Vencimiento . "' WHERE ch_fac_tipodocumento = '".$tipo."' AND ch_fac_seriedocumento = '".$serie."' AND ch_fac_numerodocumento = '".$numero."'";

		if($sqlca->query($sql3) < 0)
			return false;

		$sql4 = "UPDATE val_ta_complemento_documento SET ch_cliente = '".$cli_codigo."'  WHERE ch_fac_tipodocumento = '".$tipo."' AND ch_fac_seriedocumento = '".$serie."' AND ch_fac_numerodocumento = '".$numero."'";
		if($sqlca->query($sql4) < 0)
			return false;

		return true;
	}

	function ActualizarIGV($tipo, $serie, $numero , $cliente, $idigv){
		global $sqlca;

		if($idigv == "N"){
	
			//DETALLE
	
			$query2 = "
				UPDATE
					fac_ta_factura_detalle
				SET
					nu_fac_impuesto1	= 0,
					nu_fac_importeneto	= nu_fac_valortotal
				WHERE
					ch_fac_tipodocumento		= '" . pg_escape_string($tipo) . "'
					AND ch_fac_seriedocumento	= '" . pg_escape_string($serie) . "'
					AND ch_fac_numerodocumento	= '" . pg_escape_string($numero) . "'
					AND cli_codigo	 		= '" . pg_escape_string($cliente) . "'
			";


			$rs = pg_exec($query2);

			//CABECERA

			$query = "
				UPDATE
					fac_ta_factura_cabecera
				SET
					nu_fac_impuesto1	= 0,
					nu_fac_valorbruto	= nu_fac_valortotal,
					ch_fac_tiporecargo2 	= 'S',
					ch_fac_tiporecargo3	= 0
				WHERE
					ch_fac_tipodocumento		= '" . pg_escape_string($tipo) . "'
					AND ch_fac_seriedocumento	= '" . pg_escape_string($serie) . "'
					AND ch_fac_numerodocumento	= '" . pg_escape_string($numero) . "'
					AND cli_codigo	 		= '" . pg_escape_string($cliente) . "'
			";

			$rs = pg_exec($query);

		} else {
	
			//DETALLE
	
			$query2 = "
				UPDATE
					fac_ta_factura_detalle
				SET
					nu_fac_impuesto1	= ROUND(nu_fac_valortotal - (nu_fac_valortotal / 1.18), 2),
					nu_fac_importeneto	= ROUND((nu_fac_valortotal / 1.18), 2)
				WHERE
					ch_fac_tipodocumento		= '" . pg_escape_string($tipo) . "'
					AND ch_fac_seriedocumento	= '" . pg_escape_string($serie) . "'
					AND ch_fac_numerodocumento	= '" . pg_escape_string($numero) . "'
					AND cli_codigo	 		= '" . pg_escape_string($cliente) . "'
			";

			$rs = pg_exec($query2);

			//CABECERA

			$query = "
				UPDATE
					fac_ta_factura_cabecera
				SET
					nu_fac_impuesto1	= ROUND(nu_fac_valortotal - (nu_fac_valortotal / 1.18), 2),
					nu_fac_valorbruto	= ROUND((nu_fac_valortotal / 1.18), 2),
					ch_fac_tiporecargo2 	= NULL,
					ch_fac_tiporecargo3	= NULL
				WHERE
					ch_fac_tipodocumento		= '" . pg_escape_string($tipo) . "'
					AND ch_fac_seriedocumento	= '" . pg_escape_string($serie) . "'
					AND ch_fac_numerodocumento	= '" . pg_escape_string($numero) . "'
					AND cli_codigo	 		= '" . pg_escape_string($cliente) . "'
			";

			$rs = pg_exec($query);

		}

		return true;
	}

	function Action_Complete_FE($data){//FE = FACTURACION ELECTRONICA
		global $sqlca;

		/* VARIABLES */
		$registroid		= (empty($data["ch_fac_anulado"]) ? $data["registroid"] : $data["_id"]);
		$codalmacen		= $data["codalmacen"];
		$dt_fac_fecha 	= $data["dt_fac_fecha"];

		$sql = "
		SELECT
			ch_fac_observacion3 AS fe_referencia_documento
		FROM
			fac_ta_factura_complemento
		WHERE
			ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo = '" . $registroid . "'
			AND ch_fac_tipodocumento = '20'
		";

		if($sqlca->query($sql) < 0)
			return false;

		$row 						= $sqlca->fetchrow();
		$fe_referencia_documento 	= $row['fe_referencia_documento'];
		$month 						= substr($fe_referencia_documento,3,2);
		$year 						= substr($fe_referencia_documento,6,4);

		if(empty($fe_referencia_documento)){
			$_dt_fac_fecha = explode("/", $dt_fac_fecha);
			$month 	= $_dt_fac_fecha[1];
			$year 	= $_dt_fac_fecha[2];
		}

		$postrans 					= "pos_trans".$year.$month;

		/* VERIFICAR SI EXISTE LA TABLA POS_TRANS */
		$table_postrans 			= "";
		$columna_postrans 			= "
		(CASE WHEN fc.ch_fac_tipodocumento IN ('11', '20') THEN
			(string_to_array(com.ch_fac_observacion2, '*'))[2]||'-'||(string_to_array(com.ch_fac_observacion2, '*'))[1]
		ELSE
			'-'
		END)
		";

		$table_postrans_extra 			= "";
		$table_pago_sunat 				= "LEFT JOIN int_tabla_general AS TPAGO ON(TPAGO.tab_tabla = '05' AND TPAGO.tab_elemento<>'000000' AND fc.nu_tipo_pago = SUBSTRING(TRIM(TPAGO.tab_elemento) FOR 2 FROM LENGTH(TRIM(TPAGO.tab_elemento))-1))";

		$sql = "
        SELECT 1
        FROM   information_schema.tables
        WHERE  table_schema = 'public'
        AND    table_name = '" . $postrans . "'
        ";

         if($sqlca->query($sql) == 1){
         	$columna_postrans = "
         	(CASE WHEN fc.ch_fac_tipodocumento IN ('11', '20') THEN
				CASE WHEN PT.usr IS NULL OR PT.usr = '' THEN
					(string_to_array(com.ch_fac_observacion2, '*'))[2]||'-'||(string_to_array(com.ch_fac_observacion2, '*'))[1]
				ELSE
					PT.usr
				END
			ELSE
				'-'
			END)
         	";
			$table_postrans = "LEFT JOIN " . $postrans . " AS PT ON((string_to_array(com.ch_fac_observacion2, '*'))[2] = SUBSTR(TRIM(PT.usr), 0, 5) AND (string_to_array(com.ch_fac_observacion2, '*'))[1] = SUBSTR(TRIM(PT.usr), 6) AND PT.grupo != 'D')";
			$table_postrans_extra = "LEFT JOIN " . $postrans . " AS PT ON((string_to_array(com.ch_fac_observacion2, '*'))[2] = SUBSTR(TRIM(PT.usr), 0, 5) AND (string_to_array(com.ch_fac_observacion2, '*'))[1] = SUBSTR(TRIM(PT.usr), 6) AND PT.grupo != 'D' AND PT.td IN('B','F'))";
			$table_pago_sunat = "
			LEFT JOIN int_tabla_general AS TPAGO ON(TPAGO.tab_tabla = '05' AND TPAGO.tab_elemento<>'000000' AND fc.nu_tipo_pago = SUBSTRING(TRIM(TPAGO.tab_elemento) FOR 2 FROM LENGTH(TRIM(TPAGO.tab_elemento))-1))
			LEFT JOIN int_tabla_general AS TPAGOPT ON(TPAGOPT.tab_tabla = '05' AND TPAGOPT.tab_elemento<>'000000' AND TRIM(PT.fpago) = SUBSTRING(TRIM(TPAGOPT.tab_elemento), 6, 1))
			";
		}

		/* CARTA FIANZA */
		$status = $sqlca->query("
		SELECT
			1 AS nu_producto_inafecto
		FROM
			fac_ta_factura_cabecera AS FC
			JOIN fac_ta_factura_detalle AS FD USING (ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento, cli_codigo)
			JOIN int_articulos AS art ON (FD.art_codigo = art.art_codigo)
		WHERE
			FC.ch_fac_tipodocumento||FC.ch_fac_seriedocumento||FC.ch_fac_numerodocumento||FC.cli_codigo = '" . $registroid . "'
			AND (art.art_impuesto1 IS NULL OR art.art_impuesto1='')
		GROUP BY
			art.art_impuesto1;
		");

		if($status < 0){
			return FALSE;//Error SQL
		} else if($status == 0) {
			$nu_producto_inafecto = 0;//No se encontró ningún registro
		} else {
			$row = $sqlca->fetchrow();
			$nu_producto_inafecto = $row['nu_producto_inafecto'];
		}

		$status = $sqlca->query("
		SELECT
			1 AS nu_producto_igv
		FROM
			fac_ta_factura_cabecera AS FC
			JOIN fac_ta_factura_detalle AS FD USING (ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento, cli_codigo)
			JOIN int_articulos AS art ON (FD.art_codigo = art.art_codigo)
		WHERE
			FC.ch_fac_tipodocumento||FC.ch_fac_seriedocumento||FC.ch_fac_numerodocumento||FC.cli_codigo = '" . $registroid . "'
			AND art.art_impuesto1 !=''
		GROUP BY
			art.art_impuesto1;
		");

		if($status < 0){
			return FALSE;//Error SQL
		} else if($status == 0) {
			$nu_producto_igv = 0;//No se encontró ningún registro
		} else {
			$row = $sqlca->fetchrow();
			$nu_producto_igv = $row['nu_producto_igv'];
		}
		/* FIN CARTA FIANZA */

		$no_producto_impuesto = '';
		if($nu_producto_inafecto == '1' && $nu_producto_igv == 0)
			$no_producto_impuesto = 'PRODUCTO_INAFECTO';
		else if ($nu_producto_igv == '1' && $nu_producto_inafecto == 0)
			$no_producto_impuesto = 'PRODUCTO_IGV';
		else if($nu_producto_inafecto == '1' && $nu_producto_igv == '1')
			$no_producto_impuesto = 'PRODUCTO_IGV_INAFECTO';

		/* VARIABLES FE */
		//optype = 0 -- emitir
		//optype = 1 -- anular
		$optype = (empty($data["ch_fac_anulado"]) ? 0 : 1);

		/* OBTENER QUERY PARA FE */
		if($optype == 0){
			
		        $query = "
				SELECT
					ARRAY_TO_STRING(ARRAY_AGG(FE.TEXT),'\n') AS TEXT
				FROM
					(
					SELECT DISTINCT
						td.tab_car_03||
						'|'|| fc.ch_fac_seriedocumento||
						'|'|| '0'||fc.ch_fac_numerodocumento||
						'|'|| (CASE WHEN SUBSTR(fc.ch_fac_moneda,2,1)::INTEGER = 1 THEN 'PEN' ELSE 'USD' END)||
						'|'|| TO_CHAR(fc.dt_fac_fecha,'YYYY-MM-DD')||
						'|'|| (
							CASE
								WHEN fc.ch_fac_tiporecargo2 = 'S' AND fc.nu_fac_impuesto1 > 0 THEN '0.00'
							ELSE
								(CASE
								WHEN fc.nu_fac_descuento1 > 0 AND fc.ch_fac_tiporecargo2 = 'S' AND fc.nu_fac_impuesto1 = 0 THEN
									ROUND(fc.nu_fac_valortotal - fc.nu_fac_descuento1, 2)
								WHEN fc.nu_fac_descuento1 > 0 AND (fc.ch_fac_tiporecargo2 = '' OR fc.ch_fac_tiporecargo2 IS NULL) AND fc.nu_fac_impuesto1 > 0 THEN
									ROUND(fc.nu_fac_valortotal - (fc.nu_fac_descuento1 * (1 + (util_fn_igv()/100))), 2)
								ELSE
									ROUND(fc.nu_fac_valortotal,2)
								END)
							END)||
						'|'||(CASE
								WHEN fc.ch_fac_tipodocumento = '10' AND CHAR_LENGTH(TRIM(cli.cli_ruc)) = 11 THEN '6'
								WHEN fc.ch_fac_tipodocumento = '11' AND CHAR_LENGTH(TRIM(cli.cli_ruc)) = 11 THEN '6'
								WHEN fc.ch_fac_tipodocumento = '20' AND CHAR_LENGTH(TRIM(cli.cli_ruc)) = 11 THEN '6'
								WHEN fc.ch_fac_tipodocumento = '35' AND CHAR_LENGTH(TRIM(cli.cli_ruc)) >= 8 THEN '1'
							ELSE
								'0'
							END)||
						'|'|| (
							CASE
								WHEN CHAR_LENGTH(TRIM(cli.cli_ruc)) = 11 THEN cli.cli_ruc
								WHEN CHAR_LENGTH(TRIM(cli.cli_ruc)) = 8 THEN cli.cli_ruc
							ELSE
								'-'
							END)||
						'|'|| (CASE WHEN cli.cli_razsocial IS NULL THEN '-' ELSE cli.cli_razsocial END)||
						'|'|| "
						. $columna_postrans .
						" ||
						'|'|| (CASE
									WHEN fc.ch_fac_tipodocumento  = '11' THEN '03'
									WHEN fc.ch_fac_tipodocumento  = '20' THEN '10'
								ELSE
									'-'
								END)||
						'|'|| (CASE WHEN fc.ch_fac_tipodocumento IN ('11', '20') THEN com.ch_fac_observacion1 ELSE '-' END)||
						'|'|| (CASE WHEN fc.nu_fac_descuento1 > 0 THEN ROUND(fc.nu_fac_descuento1, 2) ELSE 0 END) AS TEXT
					FROM
						fac_ta_factura_cabecera AS fc
						LEFT JOIN fac_ta_factura_complemento AS com ON(fc.ch_fac_tipodocumento = com.ch_fac_tipodocumento AND fc.ch_fac_seriedocumento = com.ch_fac_seriedocumento AND fc.ch_fac_numerodocumento = com.ch_fac_numerodocumento AND fc.cli_codigo = com.cli_codigo)
						" . $table_postrans . "
						LEFT JOIN int_tabla_general AS td ON(fc.ch_fac_tipodocumento = substring(TRIM(tab_elemento) for 2 from length(TRIM(tab_elemento))-1) AND tab_tabla ='08' AND tab_elemento != '000000')
						LEFT JOIN int_clientes AS cli ON (cli.cli_codigo = fc.cli_codigo)
					WHERE
						fc.ch_fac_tipodocumento||fc.ch_fac_seriedocumento||fc.ch_fac_numerodocumento||fc.cli_codigo = '" . $registroid . "'

					UNION ALL

					SELECT
						'L'||
						'|'|| art.art_codigo||
						'|'|| ROUND(fd.nu_fac_cantidad, 4)||
						'|'|| CASE WHEN tu.tab_car_03 IS NULL OR tu.tab_car_03 = '' THEN 'NIU' ELSE tu.tab_car_03 END ||
						'|'|| 
						(CASE WHEN art.art_impuesto1 != '' THEN
							(CASE
								WHEN fc.ch_fac_tiporecargo2 = 'S' AND fc.nu_fac_impuesto1 = 0 THEN
									ROUND(fd.nu_fac_precio, 3)
								WHEN fc.ch_fac_tiporecargo2 = 'S' AND fc.nu_fac_impuesto1 > 0 THEN
									0.00
								ELSE
									ROUND(fd.nu_fac_precio / (1 + (util_fn_igv()/100)), 3)
							END)
						ELSE
							ROUND(fd.nu_fac_precio, 3)
						END)||
						'|'|| 
						(CASE WHEN art.art_impuesto1 != '' THEN
							(CASE WHEN fc.ch_fac_tiporecargo2 = 'S' AND fc.nu_fac_impuesto1 > 0 THEN
								ROUND(fd.nu_fac_precio / (1 + (util_fn_igv()/100)), 3)::TEXT
							ELSE
								'-'::TEXT
							END)
						ELSE
							'-'::TEXT
						END)||
						'|'|| art.art_descripcion||
						'|'|| 
						(CASE WHEN art.art_impuesto1 != '' THEN
							(CASE WHEN fc.ch_fac_tiporecargo2 = 'S' AND fc.nu_fac_impuesto1 > 0 THEN
								'0.00'
							ELSE
								ROUND(fd.nu_fac_importeneto, 2)
							END)
						ELSE
							ROUND(fd.nu_fac_importeneto, 2)
						END)||
						'|'|| 
						(CASE WHEN art.art_impuesto1 != '' THEN
							(CASE
								WHEN fc.ch_fac_tiporecargo2 = 'S' AND fc.nu_fac_impuesto1 > 0 THEN '110.00'
							ELSE
								(CASE
									WHEN fc.ch_fac_tiporecargo2 = 'S' AND fc.nu_fac_impuesto1 = 0 THEN '200.00'
								ELSE
									'10'||fd.nu_fac_impuesto1
								END)
							END)
						ELSE
							'200.00'
						END) ||
						'|'|| '-'||--ISC
						'|'|| '-'||--OTH
						'|'|| 0||
						'|'|| ROUND(fd.nu_fac_valortotal, 2) AS TEXT
					FROM
						fac_ta_factura_detalle AS fd
						JOIN fac_ta_factura_cabecera AS fc ON(fd.ch_fac_tipodocumento = fc.ch_fac_tipodocumento AND fd.ch_fac_seriedocumento = fc.ch_fac_seriedocumento AND fd.ch_fac_numerodocumento = fc.ch_fac_numerodocumento AND fd.cli_codigo = fc.cli_codigo)
						JOIN int_articulos AS art ON(fd.art_codigo = art.art_codigo)
						LEFT JOIN int_tabla_general as tu ON(tu.tab_tabla = '34' AND art.art_unidad = tu.tab_elemento)--UNIDAD DE MEDIDA
					WHERE
						fd.ch_fac_tipodocumento||fd.ch_fac_seriedocumento||fd.ch_fac_numerodocumento||fd.cli_codigo = '" . $registroid . "'

					UNION ALL";

					if($no_producto_impuesto == 'PRODUCTO_INAFECTO'){
					$query .= "
					SELECT
						'T'||
						'|'|| 'VAT' ||
						'|'|| '0' ||
						'|'|| (SELECT ROUND(tab_num_01,2) FROM int_tabla_general WHERE TRIM(tab_tabla||tab_elemento) = (SELECT par_valor FROM int_parametros WHERE TRIM(par_nombre) = 'igv actual')) AS TEXT
					FROM
						fac_ta_factura_cabecera AS fc
					WHERE
						fc.ch_fac_tipodocumento||fc.ch_fac_seriedocumento||fc.ch_fac_numerodocumento||fc.cli_codigo = '" . $registroid . "'
					";
					} else {
					$query .= "
					SELECT
						'T'||
						'|'|| 'VAT' ||
						'|'|| (CASE
								WHEN FIRST(fc.ch_fac_tiporecargo2) = 'S' AND SUM(FD.nu_fac_impuesto1) > 0 THEN
									'0'
								WHEN FIRST(fc.ch_fac_tiporecargo2) = 'S' AND SUM(FD.nu_fac_impuesto1) = 0 THEN
									'0'
								WHEN SUM(FD.nu_fac_descuento1) > 0 THEN
									ROUND(SUM(FD.nu_fac_impuesto1) - ((SUM(FD.nu_fac_descuento1) * (1 + (util_fn_igv()/100))) - SUM(FD.nu_fac_descuento1)), 2)
								ELSE
									ROUND(SUM(FD.nu_fac_impuesto1), 2)
							END) ||
						'|'|| (SELECT ROUND(tab_num_01,2) FROM int_tabla_general WHERE TRIM(tab_tabla||tab_elemento) = (SELECT par_valor FROM int_parametros WHERE TRIM(par_nombre) = 'igv actual')) AS TEXT
					FROM
						fac_ta_factura_cabecera AS fc
						JOIN fac_ta_factura_detalle AS FD USING (ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento, cli_codigo)
						JOIN int_articulos AS art ON (FD.art_codigo = art.art_codigo)
					WHERE
						fc.ch_fac_tipodocumento||fc.ch_fac_seriedocumento||fc.ch_fac_numerodocumento||fc.cli_codigo = '" . $registroid . "'
						AND art.art_impuesto1 != ''
					";
					}

					$query .="
					UNION ALL

					SELECT
						(CASE
							WHEN FIRST(FD.ch_fac_tiporecargo2) = 'S' AND SUM(FD.nu_fac_impuesto1 ) > 0 AND (SUM(FD.nu_fac_descuento1) < 1 OR SUM(FD.nu_fac_descuento1) IS NULL) THEN 
								'O'||
								'|'|| '1001' ||
								'|'|| '0.00' ||'\n'
								'O'||
								'|'|| '1004' ||
								'|'|| ROUND(SUM(FD.nu_fac_importeneto), 2)
							WHEN FIRST(FD.ch_fac_tiporecargo2) = 'S' AND SUM(FD.nu_fac_impuesto1)  = 0 AND (SUM(FD.nu_fac_descuento1)  < 1 OR SUM(FD.nu_fac_descuento1) IS NULL) THEN
								'O'||
								'|'|| '1003' ||
								'|'|| ROUND(SUM(FD.nu_fac_importeneto), 2)
							WHEN FIRST(FD.ch_fac_tiporecargo2) = 'S' AND SUM(FD.nu_fac_impuesto1) = 0 AND SUM(FD.nu_fac_descuento1) > 0 THEN
								'O'||
								'|'|| '1003' ||
								'|'|| ROUND(SUM(FD.nu_fac_importeneto - FD.nu_fac_descuento1), 2) ||'\n'
								'O'||
								'|'|| '2005' ||
								'|'|| ROUND(SUM(FD.nu_fac_descuento1), 2)
							WHEN (FIRST(FD.ch_fac_tiporecargo2) = '' OR FIRST(FD.ch_fac_tiporecargo2) IS NULL) AND SUM(FD.nu_fac_impuesto1) > 0 AND SUM(FD.nu_fac_descuento1) > 0 THEN
								'O'||
								'|'|| '1001' ||
								'|'|| ROUND(SUM(FD.nu_fac_importeneto - FD.nu_fac_descuento1), 2) ||'\n'
								'O'||
								'|'|| '2005' ||
								'|'|| ROUND(SUM(FD.nu_fac_descuento1), 2)
							WHEN (FIRST(FD.ch_fac_tiporecargo2)  = '' OR FIRST(FD.ch_fac_tiporecargo2) IS NULL) AND SUM(FD.nu_fac_impuesto1) > 0 AND (SUM(FD.nu_fac_descuento1) < 1 OR SUM(FD.nu_fac_descuento1) IS NULL) THEN 
								'O'||
								'|'|| '1001' ||
								'|'|| ROUND(SUM(FD.nu_fac_importeneto), 2)
						END)
					FROM
						fac_ta_factura_cabecera AS fc
						JOIN fac_ta_factura_detalle AS FD USING (ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento, cli_codigo)
						JOIN int_articulos AS art ON (FD.art_codigo = art.art_codigo)
					WHERE
						FD.ch_fac_tipodocumento||FD.ch_fac_seriedocumento||FD.ch_fac_numerodocumento||FD.cli_codigo = '" . $registroid . "'
						AND art.art_impuesto1 != ''

					UNION ALL

					SELECT
						'O'||
						'|'|| '1003' ||
						'|'|| ROUND(SUM(FD.nu_fac_importeneto), 2)
					FROM
						fac_ta_factura_cabecera AS fc
						JOIN fac_ta_factura_detalle AS FD USING (ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento, cli_codigo)
						JOIN int_articulos AS art ON (FD.art_codigo = art.art_codigo)
					WHERE
						FD.ch_fac_tipodocumento||FD.ch_fac_seriedocumento||FD.ch_fac_numerodocumento||FD.cli_codigo = '" . $registroid . "'
						AND (art.art_impuesto1 IS NULL OR art.art_impuesto1='')

					UNION ALL

					SELECT
						(CASE WHEN (string_to_array(com.nu_fac_complemento_direccion, '*'))[1] != '' AND (string_to_array(com.nu_fac_complemento_direccion, '*'))[2] != '' AND (string_to_array(com.nu_fac_complemento_direccion, '*'))[3] != '' AND (string_to_array(com.nu_fac_complemento_direccion, '*'))[4] != '' THEN
							'O'||
							'|'|| '2003' ||
							'|'|| (string_to_array(com.nu_fac_complemento_direccion, '*'))[2] ||
							'|'|| ROUND(fc.nu_fac_valorbruto - fc.nu_fac_descuento1, 2) ||
							'|'|| '-' ||
							'|'|| (string_to_array(com.nu_fac_complemento_direccion, '*'))[3]
						END)
					FROM
						fac_ta_factura_cabecera AS fc
						LEFT JOIN fac_ta_factura_complemento AS com ON(fc.ch_fac_tipodocumento = com.ch_fac_tipodocumento AND fc.ch_fac_seriedocumento = com.ch_fac_seriedocumento AND fc.ch_fac_numerodocumento = com.ch_fac_numerodocumento AND fc.cli_codigo = com.cli_codigo)
					WHERE
						fc.ch_fac_tipodocumento||fc.ch_fac_seriedocumento||fc.ch_fac_numerodocumento||fc.cli_codigo = '" . $registroid . "'

					UNION ALL

					SELECT
						(CASE WHEN PLACA.ch_placa != '-' THEN
							'X'||
							'|X0001'||
							'|'||PLACA.ch_placa
						END)
					FROM
						fac_ta_factura_cabecera AS fc
						JOIN val_ta_complemento_documento AS PLACA ON (PLACA.ch_fac_tipodocumento = fc.ch_fac_tipodocumento AND PLACA.ch_fac_seriedocumento = fc.ch_fac_seriedocumento AND PLACA.ch_fac_numerodocumento = fc.ch_fac_numerodocumento AND PLACA.ch_cliente = fc.cli_codigo)
					WHERE
						fc.ch_fac_tipodocumento||fc.ch_fac_seriedocumento||fc.ch_fac_numerodocumento||fc.cli_codigo = '" . $registroid . "'

					UNION ALL

					SELECT
						(CASE
							WHEN com.ch_fac_observacion1 != '' THEN
							'X'||
							'|X0009'||
							'|'||com.ch_fac_observacion1||
							(CASE
								WHEN fc.nu_tipo_pago != '' AND fc.ch_fac_tipodocumento NOT IN('11', '20') THEN
									'\n'||
									'X'||
									'|X0016'||
									'|'||TPAGO.tab_car_04||
									'\n'
									'X'||
									'|X0017'||
									'|'||fc.fe_vencimiento
								WHEN fc.nu_tipo_pago != '' AND fc.ch_fac_tipodocumento IN('11', '20') THEN
									'\n'||
									'X'||
									'|X0018'||
									'|'||CASE WHEN LENGTH((string_to_array(com.ch_fac_observacion2, '*'))[1]) > 7 THEN TO_CHAR(PT.fecha, 'YYYY-MM-DD') ELSE TO_CHAR(RFC.fe_emision, 'YYYY-MM-DD') END
							END)
						ELSE
							(CASE
								WHEN fc.nu_tipo_pago != '' AND fc.ch_fac_tipodocumento NOT IN('11', '20') THEN
									'X'||
									'|X0016'||
									'|'||TPAGO.tab_car_04||
									'\n'
									'X'||
									'|X0017'||
									'|'||fc.fe_vencimiento
								WHEN fc.nu_tipo_pago != '' AND fc.ch_fac_tipodocumento IN('11', '20') THEN
									'X'||
									'|X0018'||
									'|'||CASE WHEN LENGTH((string_to_array(com.ch_fac_observacion2, '*'))[1]) > 7 THEN TO_CHAR(PT.fecha, 'YYYY-MM-DD') ELSE TO_CHAR(RFC.fe_emision, 'YYYY-MM-DD') END
							END)
						END)
					FROM
						fac_ta_factura_cabecera AS fc
						LEFT JOIN fac_ta_factura_complemento AS com ON(fc.ch_fac_tipodocumento = com.ch_fac_tipodocumento AND fc.ch_fac_seriedocumento = com.ch_fac_seriedocumento AND fc.ch_fac_numerodocumento = com.ch_fac_numerodocumento AND fc.cli_codigo = com.cli_codigo)
						LEFT JOIN (
						SELECT
							ch_fac_tipodocumento AS nu_tipodoc,
							ch_fac_seriedocumento AS nu_seriedoc,
							ch_fac_numerodocumento AS nu_numerodoc,
							dt_fac_fecha AS fe_emision
						FROM
							fac_ta_factura_cabecera
						) AS RFC ON (
							RFC.nu_numerodoc = (string_to_array(com.ch_fac_observacion2, '*'))[1]
							AND RFC.nu_seriedoc = (string_to_array(com.ch_fac_observacion2, '*'))[2]
							AND RFC.nu_tipodoc = (string_to_array(com.ch_fac_observacion2, '*'))[3]
						)
						" . $table_postrans_extra . "
						" . $table_pago_sunat . "
					WHERE
						fc.ch_fac_tipodocumento||fc.ch_fac_seriedocumento||fc.ch_fac_numerodocumento||fc.cli_codigo = '".$registroid."'

				) AS FE --SIN ANULAR
			";

		}else{

			$query = "
				SELECT
					ARRAY_TO_STRING(ARRAY_AGG(FE.TEXT),'\n') AS TEXT
				FROM(
					SELECT
						TO_CHAR(fc.dt_fac_fecha,'YYYY-MM-DD')||
						'|'||td.tab_car_03||
						'|'||fc.ch_fac_seriedocumento||
						'|'||'0'||fc.ch_fac_numerodocumento AS TEXT
					FROM
						fac_ta_factura_cabecera AS fc
						LEFT JOIN int_tabla_general AS td ON(fc.ch_fac_tipodocumento = substring(TRIM(tab_elemento) for 2 from length(TRIM(tab_elemento))-1) AND tab_tabla ='08' AND tab_elemento != '000000')
					WHERE
						fc.ch_fac_tipodocumento||fc.ch_fac_seriedocumento||fc.ch_fac_numerodocumento||fc.cli_codigo = '" . $registroid . "'
				) AS FE --ANULADO
			";
		}
		/*
		echo "\n";
		echo $query;
		echo "\n";
		*/
		if($sqlca->query($query) < 0)
			return false;

		$rows = $sqlca->fetchRow();

		// QUERY - LEYENDA DE MONTO NUMEROS A LETRAS
		if($optype == 0){

			$query_E = "
				SELECT
					'E' AS notipo,
					(CASE WHEN fc.ch_fac_tiporecargo2 = 'S' AND fc.nu_fac_impuesto1 > 0 THEN
						'1002'
					WHEN fc.ch_fac_tiporecargo2 = 'S' AND fc.nu_fac_impuesto1 = 0 THEN 
						'2001'
					ELSE
						'1000'
					END) AS nucodigo,
					ROUND(fc.nu_fac_valortotal, 2) AS nutotal,
					mone.tab_descripcion AS nomoneda,
					(CASE WHEN (string_to_array(com.nu_fac_complemento_direccion, '*'))[1] != '' AND (string_to_array(com.nu_fac_complemento_direccion, '*'))[2] != '' AND (string_to_array(com.nu_fac_complemento_direccion, '*'))[3] != '' AND (string_to_array(com.nu_fac_complemento_direccion, '*'))[4] != '' THEN
						'1'
					ELSE
						'0'
					END) AS detraccion
				FROM
					fac_ta_factura_cabecera AS fc
					LEFT JOIN fac_ta_factura_complemento AS com ON(fc.ch_fac_tipodocumento = com.ch_fac_tipodocumento AND fc.ch_fac_seriedocumento = com.ch_fac_seriedocumento AND fc.ch_fac_numerodocumento = com.ch_fac_numerodocumento AND fc.cli_codigo = com.cli_codigo)
					JOIN int_tabla_general AS mone ON(fc.ch_fac_moneda = (substring(trim(mone.tab_elemento) for 2 from length(trim(mone.tab_elemento))-1)) AND mone.tab_tabla = '04' AND mone.tab_elemento != '000000')
				WHERE
					fc.ch_fac_tipodocumento||fc.ch_fac_seriedocumento||fc.ch_fac_numerodocumento||fc.cli_codigo = '".$registroid."'
			";

			if($sqlca->query($query_E) < 0)
				return false;

			$row = $sqlca->fetchRow();

			/* INI Get Detraccion */
			if($row['detraccion'] == '1'){
				$sql_detraccion = "
					SELECT
						(
						'E'||'|'||'2006'||'|'||'OPERACION SUJETA A DETRACCION'||'\n'
						'E'||'|'||'3000'||'|'||(string_to_array(nu_fac_complemento_direccion, '*'))[4]||'\n'
						'E'||'|'||'3001'||'|'||(string_to_array(nu_fac_complemento_direccion, '*'))[1]||'\n'
						) AS e_detraccion
					FROM
						fac_ta_factura_complemento
					WHERE
						ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo = '".$registroid."'
				";

				if($sqlca->query($sql_detraccion) < 0)
					return false;

				$row_detraccion = $sqlca->fetchRow();
			}
			/* END Detraccion */

			if($row["nucodigo"] == '1000' && $row['detraccion'] == '1'){
				$txt_tmp = "\n".$row["notipo"]."|".$row["nucodigo"]."|".FacturasModel::MontoMonetarioEnLetras($row["nutotal"],$row["nomoneda"]);
				$txt_tmp .= "\n".$row_detraccion["e_detraccion"];
			}else if($row["nucodigo"] == '1000' && $row['detraccion'] == '0')
				$txt_tmp = "\n".$row["notipo"]."|".$row["nucodigo"]."|".FacturasModel::MontoMonetarioEnLetras($row["nutotal"],$row["nomoneda"]);
			else if ($row["nucodigo"] == '1002' && $row['detraccion'] == '0')
				$txt_tmp = "\n".$row["notipo"]."|".$row["nucodigo"]."|TRANSFERENCIA GRATUITA DE UN BIEN Y/O SERVICIO PRESTADO GRATUITAMENTE";
			else//EXONERADOS
				$txt_tmp = "\n".$row["notipo"]."|".$row["nucodigo"]."|BIENES TRANSFERIDOS EN LA AMAZONIA REGION SELVA PARA SER CONSUMIDOS EN LA MISMA";

			$content = $rows["text"].$txt_tmp;
		}else
			$content = $rows["text"];

		$content = FacturasModel::TextClean($content);

		echo "\n";
		echo "\n";
		print_r($content);
		echo "\n";
		echo "\n";

		/* FIN OBTENER QUERY PARA FE */

		/* ENVIAR INFO FE */

        //status = 0 -- listo para enviar
        //status = 1 -- enviado
        //status = 2 -- error

		if($optype == 0){//EMITIR
			$callback = <<<EOT
			{
				"1":"UPDATE fac_ta_factura_cabecera SET nu_fac_recargo3 = 3  WHERE ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo = '''||'{$registroid}'||'''",
				"2":"UPDATE fac_ta_factura_cabecera SET nu_fac_recargo3 = 4  WHERE ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo = '''||'{$registroid}'||'''"
			}
EOT;
		}else if($optype == 1){//ANULAR
            $callback = <<<EOT
			{
				"1":"UPDATE fac_ta_factura_cabecera SET nu_fac_recargo3 = 5 WHERE ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo = '''||'{$registroid}'||'''",
				"2":"UPDATE fac_ta_factura_cabecera SET nu_fac_recargo3 = 6 WHERE ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo = '''||'{$registroid}'||'''"
			}
EOT;
		}

		/* Get ebiauth: Por almacen y si no encuentra que lo obtenga sin el almacen */
		$sqlca->query("
		SELECT
			SUCUR.ruc
		FROM
			inv_ta_almacenes ALMA
			JOIN int_ta_sucursales SUCUR ON (SUCUR.ch_sucursal = ALMA.ch_sucursal)
		WHERE
			SUCUR.ebikey IS NOT NULL AND SUCUR.ebikey != ''
			AND ALMA.ch_clase_almacen = '1'
        	AND ALMA.ch_sucursal = '" . $codalmacen . "'
		");
		$row = $sqlca->fetchRow();

		if(trim($row['ruc']) != '')
			$taxid = trim($row['ruc']);
		else{
			$sqlca->query("
			SELECT DISTINCT
				SUCUR.ruc
			FROM
				inv_ta_almacenes ALMA
				JOIN int_ta_sucursales SUCUR ON (SUCUR.ch_sucursal = ALMA.ch_sucursal)
			WHERE
				SUCUR.ebikey IS NOT NULL AND SUCUR.ebikey != ''
				AND ALMA.ch_clase_almacen = '1'
			");
			$row = $sqlca->fetchRow();

			$taxid = trim($row['ruc']);
		}

		$sql = "
		INSERT INTO ebi_queue(
			_id,
			created,
			taxid,
			optype,
			status,
			callback,
			content
		)VALUES(
			nextval('seq_ebi_queue_id'),
			now(),
			'".$taxid."',
			$optype,
			0,
			'".$callback."',
			'".$content."'								
		);
		";

		if($sqlca->query($sql) < 0){
			var_dump($sqlca->get_error());
			return false;
		}

		if($optype == 0)// 1 = COMPLETADO PARA BD OPENSOFT
			$sql = "UPDATE fac_ta_factura_cabecera SET nu_fac_recargo3 = 1 WHERE ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo = '".$registroid."'";
		else if($optype == 1)// 2 = ANULADO PARA BD OPENSOFT
			$sql = "UPDATE fac_ta_factura_cabecera SET nu_fac_recargo3 = 2 WHERE ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo = '".$registroid."'";

		if($sqlca->query($sql) < 0)
			return false;
		return true;
	}

	function TextClean($str) {
		return str_replace(array("'"), array(''), $str);
	}

	/* Limipiar Campo Observación para FE */
	function clearText($str) {
        return str_replace(array("\r","\n","|"), array('','. ',''), $str);
	}

	/* FUNCION DE NUMEROS A LETRAS */
	function MontoMonetarioEnLetras($monto,$text){

		$monto = str_replace(',', '', $monto);
		$pos = strpos($monto, '.');
		        
		if ($pos == false) {
		        $monto_entero = $monto;
		        $monto_decimal = '00';
		} else {
		        $monto_entero = substr($monto,0,$pos);
		        $monto_decimal = substr($monto,$pos,strlen($monto)-$pos);
		        $monto_decimal = $monto_decimal * 100;
		}
		$monto = (int)($monto_entero);
		$texto_con = " CON $monto_decimal/100 ".$text;

		return FacturasModel::NumerosALetras($monto).$texto_con;
	}

	function NumerosALetras($monto){

		$maximo                 = pow(10,9);
		$unidad                 = array(1=>"UNO", 2=>"DOS", 3=>"TRES", 4=>"CUATRO", 5=>"CINCO", 6=>"SEIS", 7=>"SIETE", 8=>"OCHO", 9=>"NUEVE" );
		$decena                 = array(10=>"DIEZ", 11=>"ONCE", 12=>"DOCE", 13=>"TRECE", 14=>"CATORCE", 15=>"QUINCE", 20=>"VEINTE", 30=>"TREINTA", 40=>"CUARENTA", 50=>"CINCUENTA", 60=>"SESENTA", 70=>"SETENTA", 80=>"OCHENTA", 90=>"NOVENTA");
		$prefijoDecena			= array(10=>"DIECI", 20=>"VEINTI", 30=>"TREINTA Y ", 40=>"CUARENTA Y ", 50=>"CINCUENTA Y ", 60=>"SESENTA Y ", 70=>"SETENTA Y ", 80=>"OCHENTA Y ", 90=>"NOVENTA Y ");
		$centena				= array(100=>"CIEN", 200=>"DOSCIENTOS", 300=>"TRESCIENTOS", 400=>"CUATROCIENTOS", 500=>"QUINIENTOS", 600=>"SEISCIENTOS", 700=>"SETECIENTOS", 800=>"OCHOCIENTOS", 900=>"NOVECIENTOS");        
		$prefijoCentena			= array(100=>"CIENTO ", 200=>"DOSCIENTOS ", 300=>"TRESCIENTOS ", 400=>"CUATROCIENTOS ", 500=>"QUINIENTOS ", 600=>"SEISCIENTOS ", 700=>"SETECIENTOS ", 800=>"OCHOCIENTOS ", 900=>"NOVECIENTOS ");
		$sufijoMiles			= "MIL";
		$sufijoMillon			= "UN MILLON";
		$sufijoMillones			= "MILLONES";
		$base					= strlen(strval($monto));
		$pren					= intval(floor($monto/pow(10,$base-1)));
		$prencentena			= intval(floor($monto/pow(10,3)));
		$prenmillar				= intval(floor($monto/pow(10,6)));
		$resto					= $monto%pow(10,$base-1);
		$restocentena			= $monto%pow(10,3);
		$restomillar			= $monto%pow(10,6);
		
		if (!$monto) return "";
		
		if (is_int($monto) && $monto > 0 && $monto < abs($maximo)) {
			switch ($base) {
		                case 1: return $unidad[$monto];
		                case 2: return array_key_exists($monto, $decena)  ? $decena[$monto]  : $prefijoDecena[$pren*10]   . FacturasModel::NumerosALetras($resto);
		                case 3: return array_key_exists($monto, $centena) ? $centena[$monto] : $prefijoCentena[$pren*100] . FacturasModel::NumerosALetras($resto);
		                case 4: case 5: case 6: return ($prencentena>1) ? FacturasModel::NumerosALetras($prencentena). " ". $sufijoMiles . " " . FacturasModel::NumerosALetras($restocentena) : $sufijoMiles. " " . FacturasModel::NumerosALetras($restocentena);
		                case 7: case 8: case 9: return ($prenmillar>1)  ? FacturasModel::NumerosALetras($prenmillar). " ". $sufijoMillones . " " . FacturasModel::NumerosALetras($restomillar)  : $sufijoMillon. " " . FacturasModel::NumerosALetras($restomillar);
		        }
		} else return false;
	}

	function Verify_NotaCredito($_id, $dt_fac_fecha) {
		global $sqlca;

		$sql = "
		SELECT
			ch_fac_observacion3 AS fe_referencia_documento
		FROM
			fac_ta_factura_complemento
		WHERE
			ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo = '" . $_id . "'
		";

		if($sqlca->query($sql) < 0)
			return false;

		$row = $sqlca->fetchrow();
		$fe_referencia_documento = $row['fe_referencia_documento'];
		$month = substr($fe_referencia_documento,3,2);
		$year = substr($fe_referencia_documento,6,4);
		$postrans = "pos_trans".$year.$month;

		$sql = "
	        SELECT 1
	        FROM   information_schema.tables
	        WHERE  table_schema = 'public'
	        AND    table_name = '" . $postrans . "'
        ";

        $columna_postrans = "";
        $table_postrans = "";

        if($sqlca->query($sql) == 1){
        	$columna_postrans = " + COUNT(RPT.*)";
        	$table_postrans = "
			LEFT JOIN (
			SELECT
				(CASE
					WHEN tm = 'V' AND td = 'F' THEN '10'
					WHEN tm = 'V' AND td = 'B' THEN '35'
					WHEN tm = 'D' AND tm = 'A' THEN '20'
				END) AS nu_tipodoc,
				SUBSTR(TRIM(usr), 0, 5) AS nu_seriedoc,
				SUBSTR(TRIM(usr), 6) AS nu_numerodoc,
				grupo AS grupo
			FROM
				" . $postrans . "
			) AS RPT ON (
				RPT.nu_numerodoc = (string_to_array(com.ch_fac_observacion2, '*'))[1]
				AND RPT.nu_seriedoc = (string_to_array(com.ch_fac_observacion2, '*'))[2]
				AND RPT.nu_tipodoc = (string_to_array(com.ch_fac_observacion2, '*'))[3]
				AND RPT.grupo != 'D'
			)
        	";
        }

		$sql = "
			SELECT
				COUNT(RFC.*) " . $columna_postrans . " AS registro
			FROM
				fac_ta_factura_cabecera AS fc
				LEFT JOIN fac_ta_factura_complemento AS com ON(fc.ch_fac_tipodocumento = com.ch_fac_tipodocumento AND fc.ch_fac_seriedocumento = com.ch_fac_seriedocumento AND fc.ch_fac_numerodocumento = com.ch_fac_numerodocumento AND fc.cli_codigo = com.cli_codigo)
				LEFT JOIN (
				SELECT
					ch_fac_tipodocumento AS nu_tipodoc,
					ch_fac_seriedocumento AS nu_seriedoc,
					ch_fac_numerodocumento AS nu_numerodoc
				FROM
					fac_ta_factura_cabecera
				) AS RFC ON (
					RFC.nu_numerodoc = (string_to_array(com.ch_fac_observacion2, '*'))[1]
					AND RFC.nu_seriedoc = (string_to_array(com.ch_fac_observacion2, '*'))[2]
					AND RFC.nu_tipodoc = (string_to_array(com.ch_fac_observacion2, '*'))[3]
				)
				" . $table_postrans . "
			WHERE
				fc.ch_fac_tipodocumento||fc.ch_fac_seriedocumento||fc.ch_fac_numerodocumento||fc.cli_codigo = '" . $_id . "'
		";

		if($sqlca->query($sql) < 0)
			return false;

		$row = $sqlca->fetchrow();

		if ($row['registro'] == 0)
			return false;
		return true;
	}

	function Verify_NotaCredito_Observacion($_id){
		global $sqlca;

		$sql = "SELECT
 com.ch_fac_observacion1 AS obs
FROM
 fac_ta_factura_complemento AS com
WHERE
 com.ch_fac_tipodocumento||com.ch_fac_seriedocumento||com.ch_fac_numerodocumento||com.cli_codigo = '" . $_id . "';";

		error_log($sql);
		if($sqlca->query($sql) < 0)
			return false;

		$row = $sqlca->fetchrow();

		if (empty($row['obs']) || $row['obs'] == NULL) {
			return false;
		} else {
			if ($row['obs'] == '' || strlen(trim($row['obs'])) < 8) {
				return false;
			}
		}

		return true;
	}

	function checkOriginDocument($_id) {
		error_log('>checkOriginDocument');
		global $sqlca;

		$sql = "SELECT
 com.ch_fac_observacion2 AS reference,
 com.ch_fac_observacion3 AS _date,
 com.cli_codigo AS client
FROM
 fac_ta_factura_complemento AS com
WHERE
 com.ch_fac_tipodocumento||com.ch_fac_seriedocumento||com.ch_fac_numerodocumento||com.cli_codigo = '" . $_id . "';";

		error_log('checkOriginDocument: '.$sql);
		if($sqlca->query($sql) < 0)
			return false;

		$row = $sqlca->fetchrow();
		if (empty($row['reference']) || $row['reference'] == NULL) {
			return false;
		}
		if (empty($row['_date']) || $row['_date'] == NULL) {
			return false;
		}
		if (empty($row['client']) || $row['client'] == NULL) {
			return false;
		}

		$reference = explode('*', $row['reference']);
		$serie = $reference[1];
		$number = $reference[0];
		$documenttype = $reference[2];
		$client = trim($row['client']);
		$_date = explode('/', $row['_date']);
		$year = $_date[2];
		$month = $_date[1];
		$day = $_date[0];
		$row = array();

		$sql = "SELECT
 ch_fac_tipodocumento AS documenttype
FROM
 fac_ta_factura_cabecera AS com
WHERE
 ch_fac_tipodocumento = '$documenttype'
 AND ch_fac_seriedocumento = '$serie'
 AND ch_fac_numerodocumento = '$number'
 AND cli_codigo = '$client'
 AND dt_fac_fecha = '$year-$month-$day';";

 		error_log('checkOriginDocument: '.$sql);
		if($sqlca->query($sql) < 0)
			return false;

		$row = $sqlca->fetchrow();
		if (empty($row['documenttype']) || $row['documenttype'] == NULL) {

			$sql = "SELECT 1
FROM information_schema.tables
WHERE table_schema = 'public'
 AND table_name = 'pos_trans$year$month';";

			if ($sqlca->query($sql) == 1) {
				$td = 'F';
				$where = "usr = '$serie-$number'
 AND ruc = '$client'
 AND td = '$td'";
				if ($documenttype == '35') {
					$td = 'B';
					$where = "usr = '$serie-$number'
 AND td = '$td'";
				}
				$sql = "SELECT
 usr AS document
FROM
 pos_trans$year$month
WHERE
 $where
GROUP BY 1;";

				error_log('checkOriginDocument: '.$sql);
				if($sqlca->query($sql) < 0)
					return false;

				$row = $sqlca->fetchrow();
				if (empty($row['document']) || $row['document'] == NULL) {
					return false;
				}
			} else {
				return false;
			}
		}

		return true;
	}

	function getOriginDocument($_id) {
		error_log('>getOriginDocument $_id: '.$_id);
		$res = array();
		global $sqlca;

		$sql = "SELECT
 com.ch_fac_observacion2 AS reference,
 com.ch_fac_observacion3 AS _date,
 com.cli_codigo AS client
FROM
 fac_ta_factura_complemento AS com
WHERE
 com.ch_fac_tipodocumento||com.ch_fac_seriedocumento||com.ch_fac_numerodocumento||com.cli_codigo = '" . $_id . "';";

		error_log('checkOriginDocument: '.$sql);
		if ($sqlca->query($sql) < 0) {
			return array('error' => true, 'errorCode' => 0);
		}

		$row = $sqlca->fetchrow();
		if (empty($row['reference']) || $row['reference'] == NULL) {
			return array('error' => true, 'errorCode' => 1);
		}
		if (empty($row['_date']) || $row['_date'] == NULL) {
			return array('error' => true, 'errorCode' => 2);
		}
		if (empty($row['client']) || $row['client'] == NULL) {
			return array('error' => true, 'errorCode' => 3);
		}

		$reference = explode('*', $row['reference']);
		$serie = $reference[1];
		$number = $reference[0];
		$documenttype = $reference[2];
		$client = trim($row['client']);
		$_date = explode('/', $row['_date']);
		$year = $_date[2];
		$month = $_date[1];
		$day = $_date[0];
		$row = array();

		$sql = "SELECT
 ch_fac_tipodocumento AS documenttype
FROM
 fac_ta_factura_cabecera AS com
WHERE
 ch_fac_tipodocumento = '$documenttype'
 AND ch_fac_seriedocumento = '$serie'
 AND ch_fac_numerodocumento = '$number'
 AND cli_codigo = '$client'
 AND dt_fac_fecha = '$year-$month-$day';";

 		error_log('getOriginDocument: '.$sql);
		if ($sqlca->query($sql) < 0)
			return array('error' => true, 'errorCode' => 11);

		$row = $sqlca->fetchrow();
		if (empty($row['documenttype']) || $row['documenttype'] == NULL) {

			$sql = "SELECT 1
FROM information_schema.tables
WHERE table_schema = 'public'
 AND table_name = 'pos_trans$year$month';";

			if ($sqlca->query($sql) == 1) {
				$td = 'F';
				$where = "usr = '$serie-$number'
 AND ruc = '$client'
 AND td = '$td'";
				if ($documenttype == '35') {
					$td = 'B';
					$where = "usr = '$serie-$number'
 AND td = '$td'";
				}
				$sql = "SELECT
 usr AS document
FROM
 pos_trans$year$month
WHERE
 $where
GROUP BY 1;";

				error_log('getOriginDocument: '.$sql);
				if ($sqlca->query($sql) < 0)
					return array('error' => true, 'errorCode' => 22);

				$row = $sqlca->fetchrow();
				if (empty($row['document']) || $row['document'] == NULL) {
					return array('error' => true, 'errorCode' => 23);
				} else {
					return array(
						'error' => false,
						'errorCode' => 2,
						'serie' => $serie,
						'number' => $number,
						'serie' => $serie,
						'date' => "$year-$month-$day",
					);//La referencia existe en postrans
				}
			} else {
				return array('error' => true, 'errorCode' => 21);//No existe postrans
			}
		} else {
			return array(
				'error' => false,
				'errorCode' => 1,
				'serie' => $serie,
				'number' => $number,
				'serie' => $serie,
				'date' => "$year-$month-$day",
			);
			return array('error' => false, 'errorCode' => 1);//La referencia existe en facturas manuales
		}
		//return true;
	}

	function Verify_LetraElectronica($_id){
		global $sqlca;

		$sql = "
		SELECT
			SUBSTRING(com.ch_fac_seriedocumento FROM '[A-Z]+') AS no_letra_original,
			SUBSTRING((string_to_array(com.ch_fac_observacion2, '*'))[2] FROM '[A-Z]+') AS no_letra_referencia
		FROM
			fac_ta_factura_complemento AS com
		WHERE
			com.ch_fac_tipodocumento||com.ch_fac_seriedocumento||com.ch_fac_numerodocumento||com.cli_codigo = '" . $_id . "';
		";

		if($sqlca->query($sql) < 0)
			return false;

		$row = $sqlca->fetchrow();

		if ($row['no_letra_original'] != $row['no_letra_referencia'])
			return false;

		return true;
	}

	function Verify_UnidadMedida($_id) {
		global $sqlca;

		$sql = "
		SELECT
			tu.tab_car_03 AS no_sunat_unidad
		FROM
			fac_ta_factura_detalle AS fd
			JOIN int_articulos AS art ON(fd.art_codigo = art.art_codigo)
			LEFT JOIN int_tabla_general as tu ON(tu.tab_tabla = '34' AND art.art_unidad = tu.tab_elemento)--UNIDAD DE MEDIDA
		WHERE
			fd.ch_fac_tipodocumento||fd.ch_fac_seriedocumento||fd.ch_fac_numerodocumento||fd.cli_codigo = '" . $_id . "'
		";

		if ($sqlca->query($sql) < 0)
			return false;

		$row = $sqlca->fetchAll();

		$Verify_UnidadMedida = true;

		for ($i = 0; $i < count($row); $i++) {
			if (empty($row[$i]['no_sunat_unidad'])) {
				return false;
				//$Verify_UnidadMedida = false;
			}
		}
		return true;
		//return $Verify_UnidadMedida;
	}

	function Verify_Status_Document_Electronic_Anulado($_id){
		global $sqlca;

		$sql = "
		SELECT
			nu_fac_recargo3 AS nu_estado,
			ch_fac_anulado AS no_anulado
		FROM
			fac_ta_factura_cabecera AS fc
		WHERE
			fc.ch_fac_tipodocumento||fc.ch_fac_seriedocumento||fc.ch_fac_numerodocumento||fc.cli_codigo = '" . $_id . "';
		";

		if($sqlca->query($sql) < 0)
			return false;

		$row = $sqlca->fetchrow();

		if ($row['nu_estado'] == 2)//1 = DOCUMENTO ELECTRONICO NO ANULADO Y 2 = DOCUMENTO ELECTRONICO ANULADO
			return false;

		return true;
	}

	function Verify_Status_Document_Electronic($_id){
		global $sqlca;

		$sql = "
		SELECT
			nu_fac_recargo3 AS nu_estado,
			ch_fac_anulado AS no_anulado
		FROM
			fac_ta_factura_cabecera AS fc
		WHERE
			fc.ch_fac_tipodocumento||fc.ch_fac_seriedocumento||fc.ch_fac_numerodocumento||fc.cli_codigo = '" . $_id . "';
		";

		if($sqlca->query($sql) < 0)
			return false;

		$row = $sqlca->fetchrow();

		if ($row['nu_estado'] == 1)//1 = DOCUMENTO ELECTRONICO NO ANULADO Y 2 = DOCUMENTO ELECTRONICO ANULADO
			return false;

		return true;
	}


	function managerTransaction($type) {
		global $sqlca;
		$sql = $type.";";
		$sqlca->query($sql);
	}

	/**
	 * Completado de documentos (F.E.)
	 */
	function actionCompleteFE($data) {
		//[E_INVOICE]
		global $sqlca;

		/* VARIABLES */
		$registroid	= (empty($data["ch_fac_anulado"]) ? $data["registroid"] : $data["_id"]);
		$codalmacen	= $data["codalmacen"];
		$dt_fac_fecha = $data["dt_fac_fecha"];

		$new_content = '';
		$content = '';

		$sql = "
SELECT
 ch_fac_observacion3 AS fe_referencia_documento
FROM
 fac_ta_factura_complemento
WHERE
 ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo = '" . $registroid . "'
 AND ch_fac_tipodocumento = '20';";

		if($sqlca->query($sql) < 0) {
			error_log('CFE, CODE: 0');
			error_log($sql);
			return false;
		}

		$row = $sqlca->fetchrow();
		$fe_referencia_documento = $row['fe_referencia_documento'];
		//echo 'fe_referencia_documento: '.$row['fe_referencia_documento'].' query: '.$sql;


		$ch_liquidacion = '';
		$ch_almacen = '';
		$typetax = '';
		$sql = "SELECT
 ch_liquidacion,
 ch_almacen,
 CASE WHEN ch_fac_tiporecargo2 IS NULL OR ch_fac_tiporecargo2 = '' THEN 0 -- NORMAL
  WHEN ch_fac_tiporecargo2 = 'S' AND nu_fac_impuesto1 = 0 THEN 1 -- EXO
  WHEN ch_fac_tiporecargo2 = 'S' AND nu_fac_impuesto1 > 0 THEN 2 -- TG
 END AS typetax
FROM fac_ta_factura_cabecera
WHERE
 ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo = '" . $registroid . "';";

		if($sqlca->query($sql) < 0) {
			error_log('CFE, CODE: 000');
			error_log($sql);
			return false;
		}

		$row = $sqlca->fetchrow();
		$ch_liquidacion = trim($row['ch_liquidacion']);
		$ch_almacen = trim($row['ch_almacen']);
		if ($row['typetax'] == '' || $row['typetax'] == NULL) {
			error_log('[E_INVOICE] CFE, ERROR AL IDENTIFICAR typetax');
			return false;
		} else {
			$typetax = $row['typetax'];
		}
		//$typetax = '';

		if (empty($fe_referencia_documento)) {
			$_dt_fac_fecha = explode("/", $dt_fac_fecha);
			$month = $_dt_fac_fecha[1];
			$year = $_dt_fac_fecha[2];
		} else {
			$month = substr($fe_referencia_documento,3,2);
			$year = substr($fe_referencia_documento,6,4);
		}

		$postrans = "pos_trans".$year.$month;
		//echo 'postrans: ('.$postrans.')';

		/* VERIFICAR SI EXISTE LA TABLA POS_TRANS */
		$table_postrans = "";
		$columna_postrans = "
(CASE WHEN fc.ch_fac_tipodocumento IN ('11', '20') THEN
 (string_to_array(com.ch_fac_observacion2, '*'))[2]||'-'||(string_to_array(com.ch_fac_observacion2, '*'))[1]
ELSE
 '-'
END)";

		$table_postrans_extra = "";
		$table_pago_sunat = "LEFT JOIN int_tabla_general AS TPAGO ON(TPAGO.tab_tabla = '05' AND TPAGO.tab_elemento<>'000000' AND fc.nu_tipo_pago = SUBSTRING(TRIM(TPAGO.tab_elemento) FOR 2 FROM LENGTH(TRIM(TPAGO.tab_elemento))-1))";


        $isExistPostrans = FacturasModel::checkExistPostrans($postrans);

		$isFreeTransfer = FacturasModel::checkFreeTransfer($registroid);

		if ($isExistPostrans) {
			//Existe pos_trans
         	$columna_postrans = "
(CASE WHEN fc.ch_fac_tipodocumento IN ('11', '20') THEN
 CASE WHEN PT.usr IS NULL OR PT.usr = '' THEN
  (string_to_array(com.ch_fac_observacion2, '*'))[2]||'-'||(string_to_array(com.ch_fac_observacion2, '*'))[1]
 ELSE
  PT.usr
 END
ELSE
 '-'
END)";

			$table_postrans = "LEFT JOIN " . $postrans . " AS PT ON((string_to_array(com.ch_fac_observacion2, '*'))[2] = SUBSTR(TRIM(PT.usr), 0, 5) AND (string_to_array(com.ch_fac_observacion2, '*'))[1] = SUBSTR(TRIM(PT.usr), 6) AND PT.grupo != 'D')";
			$table_postrans_extra = "LEFT JOIN " . $postrans . " AS PT ON((string_to_array(com.ch_fac_observacion2, '*'))[2] = SUBSTR(TRIM(PT.usr), 0, 5) AND (string_to_array(com.ch_fac_observacion2, '*'))[1] = SUBSTR(TRIM(PT.usr), 6) AND PT.grupo != 'D' AND PT.td IN('B','F'))";
			$table_pago_sunat = "
LEFT JOIN int_tabla_general AS TPAGO ON(TPAGO.tab_tabla = '05' AND TPAGO.tab_elemento<>'000000' AND fc.nu_tipo_pago = SUBSTRING(TRIM(TPAGO.tab_elemento) FOR 2 FROM LENGTH(TRIM(TPAGO.tab_elemento))-1))
LEFT JOIN int_tabla_general AS TPAGOPT ON(TPAGOPT.tab_tabla = '05' AND TPAGOPT.tab_elemento<>'000000' AND TRIM(PT.fpago) = SUBSTRING(TRIM(TPAGOPT.tab_elemento), 6, 1))";
		}

		/* CARTA FIANZA */
		$query = "
SELECT
 1 AS nu_producto_inafecto
FROM
 fac_ta_factura_cabecera AS FC
 JOIN fac_ta_factura_detalle AS FD USING (ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento, cli_codigo)
 JOIN int_articulos AS art ON (FD.art_codigo = art.art_codigo)
WHERE
 FC.ch_fac_tipodocumento||FC.ch_fac_seriedocumento||FC.ch_fac_numerodocumento||FC.cli_codigo = '" . $registroid . "'
 AND (art.art_impuesto1 IS NULL OR art.art_impuesto1='')
GROUP BY
 art.art_impuesto1;";
		$status = $sqlca->query($query);

		if ($status < 0) {
			error_log('CFE, CODE: 1');
			error_log($query);
			return false;//Error SQL
		} else if ($status == 0) {
			$nu_producto_inafecto = 0;//No se encontró ningún registro
		} else {
			$row = $sqlca->fetchrow();
			$nu_producto_inafecto = $row['nu_producto_inafecto'];
		}

		$query = "
SELECT
 1 AS nu_producto_igv
FROM
 fac_ta_factura_cabecera AS FC
JOIN fac_ta_factura_detalle AS FD USING (ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento, cli_codigo)
JOIN int_articulos AS art ON (FD.art_codigo = art.art_codigo)
WHERE
 FC.ch_fac_tipodocumento||FC.ch_fac_seriedocumento||FC.ch_fac_numerodocumento||FC.cli_codigo = '" . $registroid . "'
 AND art.art_impuesto1 !=''
GROUP BY
 art.art_impuesto1;";
		$status = $sqlca->query($query);

		if ($status < 0) {
			error_log('CFE, CODE: 2');
			error_log($sql);
			return false;//Error SQL
		} else if ($status == 0) {
			$nu_producto_igv = 0;//No se encontró ningún registro
		} else {
			$row = $sqlca->fetchrow();
			$nu_producto_igv = $row['nu_producto_igv'];
		}
		/* FIN CARTA FIANZA */

		$no_producto_impuesto = '';
		if($nu_producto_inafecto == '1' && $nu_producto_igv == 0)
			$no_producto_impuesto = 'PRODUCTO_INAFECTO';
		else if ($nu_producto_igv == '1' && $nu_producto_inafecto == 0)
			$no_producto_impuesto = 'PRODUCTO_IGV';
		else if($nu_producto_inafecto == '1' && $nu_producto_igv == '1')
			$no_producto_impuesto = 'PRODUCTO_IGV_INAFECTO';

		/* VARIABLES FE */
		//optype = 0 -- emitir
		//optype = 1 -- anular
		$optype = (empty($data["ch_fac_anulado"]) ? 0 : 1);

		/* OBTENER QUERY PARA FE */
		if ($optype == 0) {

			$_params = array(
				'postrans' => $postrans,
				'isExistPostrans' => $isExistPostrans,
				'registroid' => $registroid,
				'codalmacen' => $codalmacen,
				'isFreeTransfer' => $isFreeTransfer,
				'isfe' => true,
			);

			$resHead = FacturasModel::getDataHeadFE($_params);
			if ($resHead['error']) {
				error_log('CFE, CODE: 3 (Error al generar cabecera de documento.)');
				error_log($resHead['_line']['line']);
				return false;
			}
			/*echo '<br><br><pre>';
			var_dump($resHead);
			echo '</pre>';*/
			$_params['_total'] = $resHead['_total'];
			$new_content = $resHead['line'];//Linea de cabecera
			$doc_type_ocs = $resHead['doc_type_ocs'];


			$resLineL = FacturasModel::getDataLineLFE($_params);
			if ($resLineL['error']) {
				error_log('CFE, CODE: 4 (Error al generar lineas de items de documento.)');
				return false;
			}
			/*echo '<br>resLineL<hr>';
			var_dump($resLineL);
			echo '<hr>';*/
			$new_content .= $resLineL['line'];//Linea de 'L'(detalle de productos)


			//echo '$no_producto_impuesto: '.$no_producto_impuesto;
			$_params['no_producto_impuesto'] = $no_producto_impuesto;
			$resTax = FacturasModel::getDataTaxFE($_params);
			if ($resTax['error']) {
				error_log('CFE, CODE: 5 (Error al generar Tax de items de documento.)');
				return false;
			}
			/*echo '<br>resTax<hr>';
			var_dump($resTax);
			echo '<hr>';*/

			$new_content .= "\n".$resTax['line'];//Linea de T(Tax)


			//aun por definir las 'O'
			$resLineO = FacturasModel::getDataLineOFE($_params);
			/*echo '<br>resLineO<hr>';
			var_dump($resLineO);
			echo '<hr>';*/
			$__total = 0.0;

			if ($typetax == 0 || $typetax == 2) {
				if (isset($resLineO['line_O1001']['line'])) {//Deberia ser obligatorio en gravadas normal
					if ($resLineO['line_O1001']['valid']) {
						$new_content .= "\n".$resLineO['line_O1001']['line'];//Linea de 'O'
						//$_params['_total'] = $resLineO['_total'];
					} else {
						error_log('[E_INVOICE] NO VALIDO O1001');
						return false;
					}
				} else {
					error_log('[E_INVOICE] NO EXISTE O1001');
					return false;
				}
			}
			if ($typetax == 1) {
				if (isset($resLineO['line_O1003']['line'])) {//Deberia ser obligatorio en exoneradas
					if ($resLineO['line_O1003']['valid']) {
						$new_content .= "\n".$resLineO['line_O1003']['line'];//Linea de 'O'
					} else {
						error_log('[E_INVOICE] NO VALIDO O1003');
						return false;
					}
				} else {
					error_log('[E_INVOICE] NO EXISTE O1003');
					return false;
				}
			}
			if ($typetax == 2) {
				if (isset($resLineO['line_O1004']['line'])) {//Deberia ser obligatorio en gratuita
					if ($resLineO['line_O1004']['valid']) {
						$new_content .= "\n".$resLineO['line_O1004']['line'];//Linea de 'O'
					} else {
						error_log('[E_INVOICE] NO VALIDO O1004');
						return false;
					}
				} else {
					error_log('[E_INVOICE] NO EXISTE O1004');
					return false;
				}
			}
			if (isset($resLineO['line_O2005']['line'])) {
				if ($resLineO['line_O2005']['valid']) {
					$new_content .= "\n".$resLineO['line_O2005']['line'];//Linea de 'O'
				} else {
					error_log('[E_INVOICE] NO VALIDO O2005');
					return false;
				}
			}


			//echo 'new: '.$new_content;

			$query = "
	SELECT
	 ROUND(SUM(FD.nu_fac_importeneto), 2) AS net_amount
	FROM
	 fac_ta_factura_cabecera AS fc
	 JOIN fac_ta_factura_detalle AS FD USING (ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento, cli_codigo)
	 JOIN int_articulos AS art ON (FD.art_codigo = art.art_codigo)
	WHERE
	 FD.ch_fac_tipodocumento||FD.ch_fac_seriedocumento||FD.ch_fac_numerodocumento||FD.cli_codigo = '" . $registroid . "'
	 AND (art.art_impuesto1 IS NULL OR art.art_impuesto1 = '');";

			if ($sqlca->query($query) < 0) {
				error_log('CFE, CODE: 6');
				error_log($sql);
				return false;
			} else {
				$row = $sqlca->fetchrow();
				if ($row['net_amount'] != '') {
					$line_O1003 = FacturasModel::schemaLine(array(
						'O',
						'1003',
						$row['net_amount'],
					));
					if ($line_O1003['valid']) {
						$content .= "\n".$line_O1003['line'];
						//echo "\n".'$content(5): '.$content;
					} else {
						error_log('CFE, CODE: 7');
						error_log($line_O1003['line']);
						return false;
					}
				}
			}

			if (isset($line_O1003['line'])) {//linea opcional?
				if ($line_O1003['valid']) {
					$new_content .= "\n".$line_O1003['line'];
				} else {
					return false;
				}
			}

			$query = "
	SELECT
	com.nu_fac_complemento_direccion AS ref_detrac,
	fc.nu_fac_valorbruto AS gross_value,
	fc.nu_fac_descuento1 AS disc
	FROM
	fac_ta_factura_cabecera AS fc
	LEFT JOIN fac_ta_factura_complemento AS com ON(fc.ch_fac_tipodocumento = com.ch_fac_tipodocumento AND fc.ch_fac_seriedocumento = com.ch_fac_seriedocumento AND fc.ch_fac_numerodocumento = com.ch_fac_numerodocumento AND fc.cli_codigo = com.cli_codigo)
	WHERE
	fc.ch_fac_tipodocumento||fc.ch_fac_seriedocumento||fc.ch_fac_numerodocumento||fc.cli_codigo = '" . $registroid . "';";

			if ($sqlca->query($query) < 0) {
				error_log('CFE, CODE: 8');
				error_log($query);
				return false;
			} else {
				$row = $sqlca->fetchrow();
				if ($row['ref_detrac'] != '') {
					$ref_detrac = explode('*', $row['ref_detrac']);
					if ($ref_detrac[0] != '' && $ref_detrac[1] != '' && $ref_detrac[2] != '' && $ref_detrac[3] != '') {
						$det = $row['gross_value'] - $row['disc'];

						$line_O2003 = FacturasModel::schemaLine(array(
							'O',
							'2003',
							$ref_detrac[1],
							number_format((float)$det, 2, '.', ''),
							'-',
							$ref_detrac[2],

						));
						if ($line_O2003['valid']) {
							$content .= "\n".$line_O2003['line'];
							//echo "\n".'$content(6): '.$content;
						} else {
							error_log('CFE, CODE: 9');
							error_log($line_O2003['line']);
							return false;
						}
					}
				}
			}
			if (isset($line_O2003['line'])) {//linea opcional?
				if ($line_O2003['valid']) {
					$new_content .= "\n".$line_O2003['line'];
				} else {
					return false;
				}
			}

 			if ($doc_type_ocs == '10') {//SOLO SE INCLUYE LA PLACA CUANDO ES FACTURA
 				/*$query = "
SELECT
 DISTINCT(comp.ch_placa) AS plate
FROM
 fac_ta_factura_cabecera cab
 JOIN val_ta_complemento_documento comp ON (
  cab.ch_fac_numerodocumento = comp.ch_fac_numerodocumento
  AND cab.ch_fac_tipodocumento = comp.ch_fac_tipodocumento
  AND cab.ch_fac_seriedocumento = comp.ch_fac_seriedocumento
 AND cab.cli_codigo = comp.ch_cliente
)
WHERE
 cab.ch_fac_tipodocumento||cab.ch_fac_seriedocumento||cab.ch_fac_numerodocumento||cab.cli_codigo = '" . $registroid . "';";*/

				$query = "SELECT
	DISTINCT(c.ch_placa) AS plate
	FROM
	val_ta_complemento_documento d
	JOIN val_ta_cabecera c ON (d.ch_sucursal = c.ch_sucursal AND d.dt_fecha = c.dt_fecha AND d.ch_numeval = c.ch_documento)
	WHERE
	d.ch_liquidacion = '{$ch_liquidacion}' AND d.ch_sucursal = '{$ch_almacen}'
	AND octet_length(c.ch_placa) > 3;";

				//echo 'query (7): '.$query;
				if ($sqlca->query($query) < 0) {
					error_log('CFE, CODE: 10');
					error_log($query);
					return false;
				} else {
					$plate = '';
					$count = $sqlca->numrows();
					//echo 'query (7) count: '.$count;

					//if ($count == 1) {
						for ($i = 0; $i < $count; $i++) {
							$row = $sqlca->fetchRow();
							//echo '__plate: '.$row['plate'];
							$row['plate'] = trim($row['plate']);
							if ($row['plate'] != '' && substr($row['plate'], -1) != '-' && substr($row['plate'], 0, 1) != '-' && strlen($row['plate']) >= 3) {
								//echo '___plate: '.$row['plate'];
								$plate .= "\n";
								$plate .= 'X|X0001|'.$row['plate'];
								$content .= $plate;
								//echo "\n".'$content(7): '.$content;
							}
						}
					//}
					$new_content .= $plate;
				}
			}

			//verificar X

			$resLineX = FacturasModel::getDataLineXFE($_params);
			/*echo '<br>resLineX<hr>';
			var_dump($resLineX);
			echo '<hr>';*/

			if ($resLineX['line_X0009']['valid']) {
				$new_content .= "\n".$resLineX['line_X0009']['line'];//Linea de 'O'
			}
			if ($doc_type_ocs == '10' || $doc_type_ocs == '35') {
				if ($resLineX['line_X0016']['valid']) {
					$new_content .= "\n".$resLineX['line_X0016']['line'];//Linea de 'O'
				}
			}
			if ($doc_type_ocs == '10' || $doc_type_ocs == '35') {
				if ($resLineX['line_X0017']['valid']) {
					$new_content .= "\n".$resLineX['line_X0017']['line'];//Linea de 'O'
				}
			}
			if ($doc_type_ocs == '11' || $doc_type_ocs == '20') {	
				if ($resLineX['line_X0018']['valid']) {
					$new_content .= "\n".$resLineX['line_X0018']['line'];//Linea de 'O'
				} else {
					return false;
				}
			}

			$resLineAddX = FacturasModel::getDataLineXAddrFE($_params);
			/*echo '<br>resLineAddX<hr>';
			var_dump($resLineAddX);
			echo '<hr>';*/
			if (!$resLineAddX['error']) {
				$new_content .= "\n".$resLineAddX['line'];//Linea de 'O'
			}
			
			/*echo "\n".'FINAL (1): '.$content;
			echo "(\n\nNEW FINAL (1):\n$new_content\n\n)";*/
			
			//sin anular

		} else {

			$query = "
SELECT
 ARRAY_TO_STRING(ARRAY_AGG(FE.TEXT),'\n') AS TEXT
FROM (
 SELECT
  TO_CHAR(fc.dt_fac_fecha,'YYYY-MM-DD')||
  '|'||td.tab_car_03||
  '|'||fc.ch_fac_seriedocumento||
  '|'||'0'||fc.ch_fac_numerodocumento AS TEXT
 FROM
 fac_ta_factura_cabecera AS fc
 LEFT JOIN int_tabla_general AS td ON(fc.ch_fac_tipodocumento = substring(TRIM(tab_elemento) for 2 from length(TRIM(tab_elemento))-1) AND tab_tabla ='08' AND tab_elemento != '000000')
 WHERE
 fc.ch_fac_tipodocumento||fc.ch_fac_seriedocumento||fc.ch_fac_numerodocumento||fc.cli_codigo = '" . $registroid . "'
) AS FE --ANULADO
;";
		}

		//echo 'OPT: '.$optype;
		if ($optype == 0) {
			$rows['text'] = $new_content;
		} else {
			$resVoid = FacturasModel::getDataVoidFE($_params);
			/*echo '<br>resVoid<hr>';
			var_dump($resVoid);
			echo '<hr>';*/
			$rows['text'] = $resVoid['line'];
		}
		
		

		// QUERY - LEYENDA DE MONTO NUMEROS A LETRAS
		if ($optype == 0) {
			$_params['optype'] = $optype;
			$resLineE = FacturasModel::getDataLineEFE($_params, $rows["text"]);
			/*echo '<br>resLineE<hr>';
			var_dump($resLineE);
			echo '<hr>';*/

			$new_content = $resLineE['line'];
		} else {
			$new_content = $rows['text'];
		}

		/*	$content = $rows["text"].$txt_tmp;
		} else {
			$content = $rows["text"];
		}*/

		/*echo 'FINAL: (2) '.$content;
		echo "NEW FINAL: (2) $new_content";*/

		$new_content = FacturasModel::TextClean($new_content);

		/*echo "\n";
		echo "\n";
		print_r($content);
		echo "\n";
		echo "\n";*/

		/* FIN OBTENER QUERY PARA FE */

		/* ENVIAR INFO FE */

        //status = 0 -- listo para enviar
        //status = 1 -- enviado
        //status = 2 -- error

		if ($optype == 0) { //EMITIR
			$callback = <<<EOT
			{
				"1":"UPDATE fac_ta_factura_cabecera SET nu_fac_recargo3 = 3  WHERE ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo = '''||'{$registroid}'||'''",
				"2":"UPDATE fac_ta_factura_cabecera SET nu_fac_recargo3 = 4  WHERE ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo = '''||'{$registroid}'||'''"
			}
EOT;
		} else if ($optype == 1) {//ANULAR
            $callback = <<<EOT
			{
				"1":"UPDATE fac_ta_factura_cabecera SET nu_fac_recargo3 = 5 WHERE ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo = '''||'{$registroid}'||'''",
				"2":"UPDATE fac_ta_factura_cabecera SET nu_fac_recargo3 = 6 WHERE ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo = '''||'{$registroid}'||'''"
			}
EOT;
		}

		/* Get ebiauth: Por almacen y si no encuentra que lo obtenga sin el almacen */
		$query = "
SELECT
 SUCUR.ruc
FROM
 inv_ta_almacenes ALMA
 JOIN int_ta_sucursales SUCUR ON (SUCUR.ch_sucursal = ALMA.ch_sucursal)
WHERE
 SUCUR.ebikey IS NOT NULL AND SUCUR.ebikey != ''
 AND ALMA.ch_clase_almacen = '1'
 AND ALMA.ch_almacen = '" . $codalmacen . "';";
		$sqlca->query($query);
		$row = $sqlca->fetchRow();

		if (trim($row['ruc']) != '') {
			$taxid = trim($row['ruc']);
		} else {
			$query = "
SELECT DISTINCT
 SUCUR.ruc
FROM
inv_ta_almacenes ALMA
JOIN int_ta_sucursales SUCUR ON (SUCUR.ch_sucursal = ALMA.ch_sucursal)
WHERE
SUCUR.ebikey IS NOT NULL AND SUCUR.ebikey != ''
AND ALMA.ch_clase_almacen = '1';";
			$sqlca->query($query);
			$row = $sqlca->fetchRow();

			$taxid = trim($row['ruc']);
		}

		if (trim($taxid) == '') {
			error_log($query);
			error_log('CFE, CODE: 12');
			error_log($texid);
			return false;
		}

		$sql = "
INSERT INTO ebi_queue(
 _id,
 created,
 taxid,
 optype,
 status,
 callback,
 content
) VALUES (
 nextval('seq_ebi_queue_id'),
 now(),
 '".$taxid."',
 $optype,
 0,
 '".$callback."',
 '".$new_content."'
);";

		/*error_log('CONTENT: '.$new_content);
		echo "\nFINAL: (3) \n$content";//***prueba
		echo "\nNEW FINAL: (3) \n$new_content";
		echo "\nEbi: (3) \n$sql";*/
		FacturasModel::managerTransaction('begin');

		if($sqlca->query($sql) < 0) {
			error_log('Error insert EBI_QUEUE');
			error_log($sql);
			FacturasModel::managerTransaction('rollback');
			//var_dump($sqlca->get_error());
			return false;
		}


		if($optype == 0)// 1 = COMPLETADO PARA BD OPENSOFT
			$sql = "UPDATE fac_ta_factura_cabecera SET nu_fac_recargo3 = 1 WHERE ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo = '".$registroid."'";
		else if($optype == 1)// 2 = ANULADO PARA BD OPENSOFT
			$sql = "UPDATE fac_ta_factura_cabecera SET nu_fac_recargo3 = 2 WHERE ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo = '".$registroid."'";
		//echo "\nfac_ta_factura_cabecera: (3) \n$sql";
		if($sqlca->query($sql) < 0) {
			error_log('Error update fac_ta_factura_cabecera [ROLLBACK]');
			error_log($sql);
			FacturasModel::managerTransaction('rollback');
			return false;
		}

		FacturasModel::managerTransaction('commit');
		return true;//***prueba
	}

	function checkValidLineFe($data) {
		if ($data == '') {
			return false;
		}
		$data = explode('|', $data);
		for ($i = 0; $i < count($data); $i++) { 
			if (TRIM($data[$i]) == '') {
				return false;
			}
		}
		return true;
	}

	function checkExistPostrans($postrans) {
		global $sqlca;
		$sql = "SELECT 1 FROM information_schema.tables
WHERE table_schema = 'public' AND table_name = '" . $postrans . "';";

		if ($sqlca->query($sql) == 1) {
			error_log('Existe postrans');
			return true;
		} else {
			error_log('NO Existe postrans');
			return false;
		}
	}

	function checkFreeTransfer($registroid) {
		error_log('$registroid: '.$registroid);
		global $sqlca;
		$sql = "SELECT
fc.ch_fac_tiporecargo2 AS free_transfer--Transferencia gratuita
FROM
fac_ta_factura_cabecera AS fc
WHERE
fc.ch_fac_tipodocumento||fc.ch_fac_seriedocumento||fc.ch_fac_numerodocumento||fc.cli_codigo = '".$registroid."'
AND fc.ch_fac_tiporecargo2 = 'S' AND fc.nu_fac_impuesto1 > 0;";

		if ($sqlca->query($sql) == 1) {
			return true;
		} else {
			return false;
		}
	}

	function getDataHeadFE($params) {

		global $sqlca;
		$result = array();

		$doc_type_ocs = 0;
		$row_postrans = ", '' AS pt_ref_document, com.ch_fac_observacion2 AS cab_ref_document";
		$table_postrans = '';

		if ($params['isExistPostrans']) {
			$row_postrans = ", PT.usr AS pt_ref_document, com.ch_fac_observacion2 AS cab_ref_document";
			$table_postrans = "LEFT JOIN " . $params['postrans'] . " AS PT ON((string_to_array(com.ch_fac_observacion2, '*'))[2] = SUBSTR(TRIM(PT.usr), 0, 5) AND (string_to_array(com.ch_fac_observacion2, '*'))[1] = SUBSTR(TRIM(PT.usr), 6) AND PT.grupo != 'D')";
		}

		$query = "
SELECT
td.tab_car_03 AS doc_type,
TRIM(fc.ch_fac_seriedocumento) AS serie,
fc.ch_fac_numerodocumento AS number,--considerar un 0 mas
(CASE WHEN SUBSTR(fc.ch_fac_moneda,2,1)::INTEGER = 1 THEN 'PEN' ELSE 'USD' END) AS currency,
TO_CHAR(fc.dt_fac_fecha,'YYYY-MM-DD') AS date,
fc.ch_fac_tiporecargo2 AS free_transfer,--Transferencia gratuita
fc.nu_fac_impuesto1 AS igv,
(util_fn_igv()/100) AS cnf_igv_ocs,
fc.nu_fac_descuento1 AS disc,
fc.nu_fac_valortotal AS grand_total,
fc.ch_fac_tipodocumento AS doc_type_ocs,
TRIM(cli.cli_ruc) AS taxid,
cli.cli_razsocial AS bpartner_name,
com.ch_fac_observacion1 AS obs_ref,
fc.nu_fac_valorbruto AS taxable_operations
$row_postrans
FROM
fac_ta_factura_cabecera AS fc
LEFT JOIN fac_ta_factura_complemento AS com ON(fc.ch_fac_tipodocumento = com.ch_fac_tipodocumento AND fc.ch_fac_seriedocumento = com.ch_fac_seriedocumento AND fc.ch_fac_numerodocumento = com.ch_fac_numerodocumento AND fc.cli_codigo = com.cli_codigo)
" . $table_postrans . "
LEFT JOIN int_tabla_general AS td ON(fc.ch_fac_tipodocumento = substring(TRIM(tab_elemento) for 2 from length(TRIM(tab_elemento))-1) AND tab_tabla ='08' AND tab_elemento != '000000')
LEFT JOIN int_clientes AS cli ON (cli.cli_codigo = fc.cli_codigo)
WHERE
fc.ch_fac_tipodocumento||fc.ch_fac_seriedocumento||fc.ch_fac_numerodocumento||fc.cli_codigo = '" . $params['registroid'] . "';";

		if ($sqlca->query($query) < 0) {
			$result = array('error' => true, 'query' => $query);
		} else {
			$row = $sqlca->fetchrow();

			if ($params['isfe'] == false) {
				return $row;
			}

			$doc_type_ocs = $row['doc_type_ocs'];
			$row['free_transfer'] = trim($row['free_transfer']);
			//Total
			if ($row['free_transfer'] == 'S' && $row['igv'] > 0) {
				$grand_total = 0.00;
			} else {
				if ($row['disc'] > 0 && $row['free_transfer'] == 'S' && $row['igv'] == 0) {
					//$grand_total = $row['grand_total'] - $row['free_transfer'];
					$grand_total = $row['grand_total'] - $row['disc'];
				} else if ($row['disc'] > 0 && (!isset($row['free_transfer']) || $row['free_transfer'] == '')) {

					$v_igv = ($row['taxable_operations'] - $row['disc']) * ($row['cnf_igv_ocs']);
					$v_gt = $v_igv + ($row['taxable_operations'] - $row['disc']);

					$grand_total = $v_gt;
					//$grand_total = $row['grand_total'] - ($row['free_transfer'] * (1 + ($row['cnf_igv_ocs'] / 100)));

				} else {
					$grand_total = $row['grand_total'];
				}
				$grand_total = number_format((float)$grand_total, 2, '.', '');
			}

			//if (!is_numeric($row['taxid'])) {}
			if (!preg_match('/^[0-9]+$/', $row['taxid'])) {
				error_log('[E_INVOICE] getDataHeadFE ERROR TAXID NO VALID');
				return array(
					'error' => true,
					'_line' => array('line' => 'ERROR TAXID NO VALIDO'),
				);
			}

			//Tipo para documento de identidad
			/**
			 * Tipo	SN		CLI			TD	TDS	DIC
			 * F 	F001-9	12345678901	10	6	12345678901
			 * B 	B001-9	12345678	35	1	12345678
			 * NCF	F001-9	12345678901	35	6	12345678901
			 * NCB	B001-9	12345678	35	1	12345678
			 * NDF	F001-9	12345678901	11	6	12345678901
			 * NDB	B001-9	12345678	11	1	12345678
			 * B	B001-9	999999999	35	0	-
			 * NCB	B001-9	999999999	35	0	-
			 * NDB	B001-9	999999999	35	0	-
			 *
			 * Tipo: Tipo de documento
			 * SN: Serie-Numero
			 * CLI: Codigo de cliente(RUC, DNI, ETC)
			 * TD: Tipo de documento OCS
			 * Documento de Identidad del Cliente(FE)
			 */
			$doc_type_bpartner_sunat = 0;
			if ($row['doc_type_ocs'] == '10' && strlen($row['taxid']) == 11) {//factura
				$doc_type_bpartner_sunat = 6;
			} else if ($row['doc_type_ocs'] == '11' && strlen($row['taxid']) == 11) {//nota de debito
				$doc_type_bpartner_sunat = 6;
			} else if ($row['doc_type_ocs'] == '20' && strlen($row['taxid']) == 11) {//nota de credito
				$doc_type_bpartner_sunat = 6;
			} else if ($row['doc_type_ocs'] == '35' && strlen($row['taxid']) == 8) {//boleta
				$doc_type_bpartner_sunat = 1;
			}

			//Documento de identidad del cliente
 			$taxid = '-';
 			if (strlen($row['taxid']) == 11 || strlen($row['taxid']) == 8) {
 				$taxid = $row['taxid'];
 				if (strlen($row['taxid']) == 11 && substr($row['taxid'], 0, 1) == '9') {
 					$taxid = '-';
 					$doc_type_bpartner_sunat = 0;
 					//$row['bpartner_name'] = '-';
 				}
 			}

 			if ($doc_type_bpartner_sunat == 0) {
 				$taxid = '-';
 				//$row['bpartner_name'] = '-';
 			}

 			$ref_document = '-';
 			$obs_ref = '-';
 			if ($row['doc_type_ocs'] == '11' || $row['doc_type_ocs'] == '20') {
 				//$obs_ref = (TRIM($row['obs_ref']) != '' ? $row['obs_ref'] : '-');
 				$obs_ref = TRIM($row['obs_ref']);
 				if (!isset($row['pt_ref_document']) || $row['pt_ref_document'] == '') {
 					$ref_document = explode('*', $row['cab_ref_document']);
 					$ref_document = $ref_document[1].'-'.$ref_document[0];
 				} else {
 					$ref_document = $row['pt_ref_document'];
 				}

 				if (TRIM($ref_document) == '' || $ref_document == '-') {
 					error_log('[E_INVOICE] getDataHeadFE ERROR EN LA REFERENCIA NC/ND');
	 				return array(
						'error' => true,
						'_line' => array('line' => 'ERROR EN LA REFERENCIA NC/ND'),
					);
	 			}
 			}

			$reason_ref = '-';
			if ($row['doc_type_ocs'] == '11') {
				$reason_ref = '03';
			} else if ($row['doc_type_ocs'] == '20') {
				$reason_ref = '01';
			}

			$disc = 0;
			if ($row['disc'] > 0) {
				$disc = $row['disc'];//dound a 2 decimales
				$disc = number_format((float)$disc, 2, '.', '');
			}

			if ($params['isFreeTransfer']) {
				$grand_total = '0.00';
			}

			$_line = FacturasModel::schemaLine(array(
				$row['doc_type'],
				$row['serie'],
				'0'.$row['number'],
				$row['currency'],
				$row['date'],
				$grand_total,
				$doc_type_bpartner_sunat,
				$taxid,
				$row['bpartner_name'],
				$ref_document,
				$reason_ref,
				$obs_ref,
				$disc
			));

			if (!$_line['valid']) {
				$result = array(
				 	'error' => true,
				 	'_line' => $_line,
				);
			} else {
				$result = array(
					'error' => false,
					'line' => $_line['line'],
					'row' => $row,
					'query' => $query,
					'doc_type_ocs' => $doc_type_ocs,
					'_total' => $grand_total,
				);
			}
		}

		return $result;
	}

	/**
	 * Lineas tipo L(Detalle de cada producto en el comprobante a emitir)
	 * agregado typetax
	 */
	function getDataLineLFE($params) {

		global $sqlca;
		$result = array();
		$line = '';

		$query = "SELECT
 TRIM(art.art_codigo) AS upc,
 ROUND(fd.nu_fac_cantidad, 4) AS quantity,
 tu.tab_car_03 AS uom,--unidad de medida
 art.art_impuesto1 AS tax,
 fc.ch_fac_tiporecargo2 AS free_transfer,--Transferencia gratuita
 fc.nu_fac_impuesto1 AS igv,
 ROUND(fd.nu_fac_precio, 3) AS price,
 (1 + (util_fn_igv()/100)) AS _tax,
 art.art_descripcion AS product_name,
 ROUND(fd.nu_fac_valortotal, 2) AS total_value,
 ROUND(fd.nu_fac_importeneto, 2) AS amount
 ,CASE WHEN fc.ch_fac_tiporecargo2 IS NULL OR fc.ch_fac_tiporecargo2 = '' THEN 0 -- NORMAL
 WHEN fc.ch_fac_tiporecargo2 = 'S' AND fc.nu_fac_impuesto1 = 0 THEN 1 -- EXO
 WHEN fc.ch_fac_tiporecargo2 = 'S' AND fc.nu_fac_impuesto1 > 0 THEN 2 -- TG
 END AS typetax
FROM
fac_ta_factura_detalle AS fd
JOIN fac_ta_factura_cabecera AS fc ON(fd.ch_fac_tipodocumento = fc.ch_fac_tipodocumento AND fd.ch_fac_seriedocumento = fc.ch_fac_seriedocumento AND fd.ch_fac_numerodocumento = fc.ch_fac_numerodocumento AND fd.cli_codigo = fc.cli_codigo)
JOIN int_articulos AS art ON(fd.art_codigo = art.art_codigo)
LEFT JOIN int_tabla_general as tu ON(tu.tab_tabla = '34' AND art.art_unidad = tu.tab_elemento)--UNIDAD DE MEDIDA
WHERE
fd.ch_fac_tipodocumento||fd.ch_fac_seriedocumento||fd.ch_fac_numerodocumento||fd.cli_codigo = '" . $params['registroid'] . "';";


		error_log($query);
		$line = '';
		if($sqlca->query($query) < 0) {
			$result = array('error' => true, 'query' => $query);
		} else {

			if ($params['isfe'] == false) {
				return $sqlca->fetchAll();
			}

			while ($row = $sqlca->fetchRow()) {
				$line .= "\n".$row['text'];

				$row['igv'] = (float)$row['igv'];
				$row['free_transfer'] = trim($row['free_transfer']);

				$uom = (!isset($row['uom']) || $row['uom'] == '' ? 'NIU' : $row['uom']);

				if ($row['tax'] != '') {

					if ($row['free_transfer'] == 'S' && $row['igv'] == 0) {
						//exonerado
						$price = $row['price'];
					} else if ($row['free_transfer'] == 'S' && $row['igv'] > 0) {
						//gratuito
						$price = 0.00;
					} else {
						$price = $row['price'] / $row['_tax'];
					}
				} else {
					$price = $row['price'];
				}
				$price = number_format((float)$price, 3, '.', '');

				$val = '-';
				if ($row['tax'] != '') {
					if ($row['free_transfer'] == 'S' && $row['igv'] > 0) {
						$val = $row['price'] / $row['_tax'];
						$val = number_format((float)$val, 2, '.', '');
					}
				}

				if ($row['tax'] != '') {
					if ($row['free_transfer'] == 'S' && $row['igv'] > 0) {
						$amount = 0.00;
					} else {
						$amount = $row['amount'];
					}
				} else {
					$amount = $row['amount'];
				}

				if ($row['tax'] != '') {
					if ($row['free_transfer'] == 'S' && $row['igv'] > 0) {
						$imp = 110.00;
					} else {
						if ($row['free_transfer'] == 'S' && $row['igv'] == 0) {
							$imp = 200.00;
						} else {
							$imp = '10'.$row['igv'];
						}
					}
				} else {
					$imp = 200.00;
				}

				if ($params['isFreeTransfer']) {
					$price = '0';
					$amount = '0.00';
					$row['total_value'] = '0';
					$val = $row['price'];
					$imp = '110.00';
				}

				$_line = FacturasModel::schemaLine(array(
					'L',
					$row['upc'],
					$row['quantity'],
					$uom,
					$price,
					$val,
					$row['product_name'],
					$amount,
					$imp,
					'-',//--ISC
					'-',//--OTH
					0,
					$row['total_value'],
				));

				if (!$_line['valid']) {
					return $result = array(
					 	'error' => true,
					 	'line' => $_line['line'],
					 	'query' => $query,
					);
				} else {
					$line .= $_line['line'];
				}
				$_row = $row;
			}
			$result = array(
				'error' => false,
				'line' => $line,
				'row' => $_row,
				'query' => $query,
			);

			//$result = array('error' => false, 'query' => $query, 'line' => $line);
		}
		return $result;
	}

	function getDataTaxFE($params) {
		global $sqlca;
		$result = array();

		//echo 'no_producto_impuesto: '.$params['no_producto_impuesto'];

		if ($params['no_producto_impuesto'] == 'PRODUCTO_INAFECTO') {
			$query = "SELECT
 (SELECT ROUND(tab_num_01,2) FROM int_tabla_general WHERE TRIM(tab_tabla||tab_elemento) = (SELECT par_valor FROM int_parametros WHERE TRIM(par_nombre) = 'igv actual')) AS tax_now
FROM
 fac_ta_factura_cabecera AS fc
WHERE
 fc.ch_fac_tipodocumento||fc.ch_fac_seriedocumento||fc.ch_fac_numerodocumento||fc.cli_codigo = '" . $params['registroid'] . "';";

			$line = '';
			if ($sqlca->query($query) < 0) {
				$result = array('error' => true, 'query' => $query);
			} else {
				$row = $sqlca->fetchrow();
				$tax_now = $row['tax_now'];

				$_line = FacturasModel::schemaLine(array(
					'T',
					'VAT',
					'0',
					$tax_now,
				));

				if (!$_line['valid']) {
					$result = array(
					 	'error' => true,
					);
				} else {
					$result = array(
						'error' => false,
						'line' => $_line['line'],
						'row' => $row,
						'query' => $query
					);
				}
			}

		} else {

			$query = "SELECT
 FIRST(fc.ch_fac_tiporecargo2) AS free_transfer,--Transferencia gratuita
 SUM(FD.nu_fac_impuesto1) AS igv,
 SUM(FD.nu_fac_descuento1) AS disc,
 (SELECT ROUND(tab_num_01,2) FROM int_tabla_general WHERE TRIM(tab_tabla||tab_elemento) = (SELECT par_valor FROM int_parametros WHERE TRIM(par_nombre) = 'igv actual')) AS tax_now,
 util_fn_igv() AS util_fn_igv
FROM
fac_ta_factura_cabecera AS fc
JOIN fac_ta_factura_detalle AS FD USING (ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento, cli_codigo)
JOIN int_articulos AS art ON (FD.art_codigo = art.art_codigo)
WHERE
 fc.ch_fac_tipodocumento||fc.ch_fac_seriedocumento||fc.ch_fac_numerodocumento||fc.cli_codigo = '" . $params['registroid'] . "'
AND art.art_impuesto1 != '';";

			$line = '';
			if ($sqlca->query($query) < 0) {
				$result = array('error' => true, 'query' => $query);
			} else {

				$row = $sqlca->fetchrow();
				$tax_now = $row['tax_now'];
				$row['free_transfer'] = trim($row['free_transfer']);
				$val = 0;
				if ($row['free_transfer'] == 'S' && $row['igv'] > 0) {
					$val = 0;
				} else if ($row['free_transfer'] == 'S' && $row['igv'] == 0) {
					$val = 0;
				} else if ($row['disc'] > 0) {
					$val = $row['igv'] - (($row['disc'] * (1 + ($row['util_fn_igv'] / 100))) - $row['disc']);
					$val = number_format((float)$val, 2, '.', '');
				} else {
					$val = number_format((float)$row['igv'], 2, '.', '');
				}

				if ($params['isFreeTransfer']) {
					$_line = FacturasModel::schemaLine(array(
						'T',
						'VAT',
						0,
					));
				} else {
					$_line = FacturasModel::schemaLine(array(
						'T',
						'VAT',
						$val,
						$tax_now,
					));
				}

				if (!$_line['valid']) {
					$result = array(
					 	'error' => true,
					);
				} else {
					$result = array(
						'error' => false,
						'line' => $_line['line'],
						'row' => $row,
						'query' => $query
					);
				}
			}
		}

		return $result;
	}

	function getDataLineOFE($params) {
		global $sqlca;
		$result = array();
		$_value = 0.0;

		$query ="SELECT
 FIRST(fc.ch_fac_tiporecargo2) AS free_transfer,--Transferencia gratuita
 SUM(fc.nu_fac_impuesto1) AS igv,
 SUM(fc.nu_fac_descuento1) AS disc,
 SUM(FD.nu_fac_importeneto) AS net_amount
 , FIRST(fc.nu_fac_valortotal) AS grand_total
 , util_fn_igv() AS util_fn_igv
 , FIRST(fc.nu_fac_valorbruto) AS taxable_operations
 , (util_fn_igv()/100) AS cnf_igv_ocs
FROM
fac_ta_factura_cabecera AS fc
JOIN fac_ta_factura_detalle AS FD USING (ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento, cli_codigo)
JOIN int_articulos AS art ON (FD.art_codigo = art.art_codigo)
WHERE
FD.ch_fac_tipodocumento||FD.ch_fac_seriedocumento||FD.ch_fac_numerodocumento||FD.cli_codigo = '" . $params['registroid'] . "'
AND art.art_impuesto1 != '';";
		
		$line = '';
		if($sqlca->query($query) < 0) {
			error_log('[E_INVOICE] getDataLineOFE() query: '.$query);
			$result = array('error' => true, 'query' => $query);
		} else {
			$row = $sqlca->fetchrow();

			if ($params['isfe'] == false) {
				return $row;
			}

			$row['free_transfer'] = TRIM($row['free_transfer']);

			/* free_transfer |  igv   | disc | net_amount | grand_total 
			  ---------------+--------+------+------------+-------------
			   S             | 0.0000 | 0.00 |   517.5000 |      517.50
			*/

			error_log('[E_INVOICE] getDataLineOFE() free_transfer: ('.$row['free_transfer'].') igv: ('.$row['igv'].') disc: ('.$row['disc'].')');
			if ($row['free_transfer'] == 'S' && $row['igv'] > 0 && ($row['disc'] < 1 || !isset($row['disc']))) {
				$_value = '0.00';
				$result['line_O1001'] = FacturasModel::schemaLine(array(
					'O',
					'1001',
					$_value,
				));
				$result['line_O1004'] = FacturasModel::schemaLine(array(
					'O',
					'1004',
					number_format((float)$row['net_amount'], 2, '.', ''),
				));
			} else if ($row['free_transfer'] == 'S' && $row['igv'] == 0 && ($row['disc'] < 1 || !isset($row['disc']))) {
				$_value = number_format((float)$row['net_amount'], 2, '.', '');
				$result['line_O1003'] = FacturasModel::schemaLine(array(
					'O',
					'1003',
					$_value,
				));
			} else if ($row['free_transfer'] == 'S' && $row['igv'] == 0 && $row['disc'] > 0) {
				$value = $row['net_amount'] - $row['disc'];
				$_value = number_format((float)$value, 2, '.', '');
				$result['line_O1003'] = FacturasModel::schemaLine(array(
					'O',
					'1003',
					$_value,
				));
				$result['line_O2005'] = FacturasModel::schemaLine(array(
					'O',
					'2005',
					number_format((float)$row['disc'], 2, '.', ''),
				));
			} else if (($row['free_transfer'] == '' || !isset($row['free_transfer'])) && $row['igv'] > 0 && $row['disc'] > 0) {
				//exo
				//$v_igv = ($row['taxable_operations'] - $row['disc']) * ($row['cnf_igv_ocs']);
				//$value = $v_igv;
				$value = $row['net_amount'] - $row['disc'];//(19/05/18)
				//$value = $row['net_amount'];
				$_value = number_format((float)$value, 2, '.', '');
				$result['line_O1001'] = FacturasModel::schemaLine(array(
					'O',
					'1001',
					$_value,
				));
				/*
 free_transfer |   igv    |  disc  | net_amount | grand_total | util_fn_igv 
---------------+----------+--------+------------+-------------+-------------
               | 157.8800 | 100.00 |   877.1200 |     1035.00 |       18.00
				*/
				//1035.00 - ((100.00 * (1 + (18.00 / 100))) - 100.00)
				$_value = $row['grand_total'] - (($row['disc'] * (1 + ($row['util_fn_igv'] / 100)) ) - $row['disc']);
				$result['line_O2005'] = FacturasModel::schemaLine(array(
					'O',
					'2005',
					number_format((float)$row['disc'], 2, '.', ''),
				));
				error_log('[E_INVOICE] getDataLineOFE() .. LINE_O: '.number_format((float)$value, 2, '.', ''));
			} else if (($row['free_transfer'] == '' || !isset($row['free_transfer'])) && $row['igv'] > 0 && ($row['disc'] < 1 || !isset($row['disc']))) {
				//echo 'puto_free_transfer!';
				$value = $row['net_amount'] - $row['disc'];
				$_value = number_format((float)$row['net_amount'], 2, '.', '');
				$result['line_O1001'] = FacturasModel::schemaLine(array(
					'O',
					'1001',
					$_value,
				));
				$_value = $row['grand_total'];
				error_log('[E_INVOICE] getDataLineOFE() .. LINE_O: '.number_format((float)$row['net_amount'], 2, '.', ''));
				/*echo 'line_O1001:<pre>';
				var_dump($result['line_O1001']);
				echo '</pre>';*/
			}

			if ($params['isFreeTransfer']) {
				$_value = '';
				error_log('[E_INVOICE] getDataLineOFE() getDataLineOFE isFreeTransfer: true');
				$result['line_O1001'] = FacturasModel::schemaLine(array(
					'O',
					'1001',
					'0.00',
				));

				//line_O1004, es el total?
				/*
				$value = $row['grand_total'] - (($row['disc'] * (1 + ($row['util_fn_igv'] / 100)) ) - $row['disc']);
				$result['line_O1004'] = FacturasModel::schemaLine(array(
					'O',
					'1004',
					number_format((float)$value, 2, '.', ''),
				));
				*/
				$result['line_O1004'] = FacturasModel::schemaLine(array(
					'O',
					'1004',
					number_format((float)$row['grand_total'], 2, '.', ''),
				));
			} else {
				error_log('[E_INVOICE] getDataLineOFE() isFreeTransfer: false');
			}

			//$line = $row['text'];
			$result['error'] = false;
			$result['query'] = $query;
			$result['_total'] = $_value;
		}

		return $result;
	}

	function getDataLineXFE($params) {
		global $sqlca;
		$result = array();

		$row_postrans = "'' AS date_ref_pt, -- pos_rans(Puede ser opcional)";
		$table_postrans_extra = "";
		$table_pago_sunat = "LEFT JOIN int_tabla_general AS TPAGO ON(TPAGO.tab_tabla = '05' AND TPAGO.tab_elemento<>'000000' AND fc.nu_tipo_pago = SUBSTRING(TRIM(TPAGO.tab_elemento) FOR 2 FROM LENGTH(TRIM(TPAGO.tab_elemento))-1))";

		if ($params['isExistPostrans']) {
			$row_postrans = "TO_CHAR(PT.fecha, 'YYYY-MM-DD') AS date_ref_pt, -- pos_rans(Puede ser opcional)";
			$table_postrans_extra = "LEFT JOIN " . $params['postrans'] . " AS PT ON((string_to_array(com.ch_fac_observacion2, '*'))[2] = SUBSTR(TRIM(PT.usr), 0, 5) AND (string_to_array(com.ch_fac_observacion2, '*'))[1] = SUBSTR(TRIM(PT.usr), 6) AND PT.grupo != 'D' AND PT.td IN('B','F'))";
			$table_pago_sunat = "LEFT JOIN int_tabla_general AS TPAGO ON(TPAGO.tab_tabla = '05' AND TPAGO.tab_elemento<>'000000' AND fc.nu_tipo_pago = SUBSTRING(TRIM(TPAGO.tab_elemento) FOR 2 FROM LENGTH(TRIM(TPAGO.tab_elemento))-1))
LEFT JOIN int_tabla_general AS TPAGOPT ON(TPAGOPT.tab_tabla = '05' AND TPAGOPT.tab_elemento<>'000000' AND TRIM(PT.fpago) = SUBSTRING(TRIM(TPAGOPT.tab_elemento), 6, 1))";
		}

		$query = "SELECT
 com.ch_fac_observacion1 AS obs_ref,
 TRIM(fc.nu_tipo_pago) AS type_pay,
 fc.ch_fac_tipodocumento AS doc_type_ocs,
 TPAGO.tab_car_04 AS type_pay_sunat,
 fc.fe_vencimiento AS expiration_date,
 com.ch_fac_observacion2 AS ref,
 $row_postrans
 TO_CHAR(RFC.fe_emision, 'YYYY-MM-DD') AS date_ref_fc -- fac_ta_factura_cabecera
FROM
fac_ta_factura_cabecera AS fc
LEFT JOIN fac_ta_factura_complemento AS com ON(fc.ch_fac_tipodocumento = com.ch_fac_tipodocumento AND fc.ch_fac_seriedocumento = com.ch_fac_seriedocumento AND fc.ch_fac_numerodocumento = com.ch_fac_numerodocumento AND fc.cli_codigo = com.cli_codigo)
LEFT JOIN (
SELECT
ch_fac_tipodocumento AS nu_tipodoc,
ch_fac_seriedocumento AS nu_seriedoc,
ch_fac_numerodocumento AS nu_numerodoc,
dt_fac_fecha AS fe_emision
FROM
fac_ta_factura_cabecera
) AS RFC ON (
RFC.nu_numerodoc = (string_to_array(com.ch_fac_observacion2, '*'))[1]
AND RFC.nu_seriedoc = (string_to_array(com.ch_fac_observacion2, '*'))[2]
AND RFC.nu_tipodoc = (string_to_array(com.ch_fac_observacion2, '*'))[3]
)
" . $table_postrans_extra . "
" . $table_pago_sunat . "
WHERE
fc.ch_fac_tipodocumento||fc.ch_fac_seriedocumento||fc.ch_fac_numerodocumento||fc.cli_codigo = '".$params['registroid']."';";

		error_log('X SQL:');
		error_log($query);
		$line = '';
		if($sqlca->query($query) < 0) {
			$result = array('error' => true, 'query' => $query);
		} else {
			$row = $sqlca->fetchrow();

			//echo 'obs_ref['.$row['obs_ref'].'], type_pay['.$row['type_pay'].'], doc_type_ocs['.$row['doc_type_ocs'].']';
			if ($row['obs_ref'] != '') {
				$X0009 = FacturasModel::schemaLine(array(
					'X',
					'X0009',
					$row['obs_ref'],
				));

				if ($row['type_pay'] != '' && ($row['doc_type_ocs'] != '11' && $row['doc_type_ocs'] != '20')) {
					$X0016 = FacturasModel::schemaLine(array(
						'X',
						'X0016',
						$row['type_pay_sunat'],
					));
					$X0017 = FacturasModel::schemaLine(array(
						'X',
						'X0017',
						$row['expiration_date'],
					));
				} else if ($row['type_pay'] != '' && ($row['doc_type_ocs'] == '11' || $row['doc_type_ocs'] == '20')) {
					$date_ref = explode('*', $row['ref']);
					if (strlen($date_ref[0]) > 7 && $params['isExistPostrans']) {
						$X0018 = FacturasModel::schemaLine(array(
							'X',
							'X0018',
							$row['date_ref_pt'],
						));
					} else {
						$X0018 = FacturasModel::schemaLine(array(
							'X',
							'X0018',
							$row['date_ref_fc'],
						));
					}
				}
			} else {
				if ($row['type_pay'] != '' && ($row['doc_type_ocs'] != '11' || $row['doc_type_ocs'] != '20')) {
					$X0016 = FacturasModel::schemaLine(array(
						'X',
						'X0016',
						$row['type_pay_sunat'],
					));
					$X0017 = FacturasModel::schemaLine(array(
						'X',
						'X0017',
						$row['expiration_date'],
					));
				} else if ($row['type_pay'] != '' && ($row['doc_type_ocs'] == '11' || $row['doc_type_ocs'] == '20')) {
					$date_ref = explode('*', $row['ref']);
					if (strlen($date_ref[0]) > 7 && $params['isExistPostrans']) {
						$X0018 = FacturasModel::schemaLine(array(
							'X',
							'X0018',
							$row['date_ref_pt'],
						));
					} else {
						$X0018 = FacturasModel::schemaLine(array(
							'X',
							'X0018',
							$row['date_ref_fc'],
						));
					}
				}
			}
			

			$line = $row['text'];
			$result = array('error' => false, 'query' => $query,
				'line_X0009' => $X0009,
				'line_X0016' => $X0016,
				'line_X0017' => $X0017,
				'line_X0018' => $X0018,
			);
		}

		return $result;
	}

	function getDataLineXAddrFE($params) {
		global $sqlca;
		$result = array();
		$line = '';
		$query = "SELECT
 TRIM(ch_direccion_almacen) AS warehouse_addr,
 TRIM(ch_nombre_almacen) AS warehouse_name
FROM inv_ta_almacenes WHERE ch_clase_almacen = '1' AND ch_almacen = '".$params['codalmacen']."';";
		if ($sqlca->query($query) < 0) {
			$result = array('error' => true, 'query' => $query);
		} else {
			$row = $sqlca->fetchrow();
			if ($params['isfe'] == false) {
				return $row;
			}
			if (isset($row['warehouse_addr']) && $row['warehouse_addr'] != NULL && $row['warehouse_addr'] != '') {
				if (isset($row['warehouse_name']) && $row['warehouse_name'] != NULL && $row['warehouse_name'] != '') {
					$addr = FacturasModel::schemaLine(array(
						'X',
						'X0021',
						$params['codalmacen'],
						$row['warehouse_name'],
						$row['warehouse_addr'],
					));
					if ($addr['valid']) {
						$result = array('error' => false, 'query' => $query,
							'line' => $addr['line'],
						);
					} else {
						$result = array('error' => true, 'query' => $query);
					}
				} else {
					$result = array('error' => true, 'query' => $query);
				}
			} else {
				$result = array('error' => true, 'query' => $query);
			}
		}
		return $result;
	}

	function getDataVoidFE($params) {
		global $sqlca;

		$result = array();
		$query = "SELECT
 TO_CHAR(fc.dt_fac_fecha,'YYYY-MM-DD') AS date,
 td.tab_car_03 AS doc_type,
 fc.ch_fac_seriedocumento AS serie,
 fc.ch_fac_numerodocumento AS number
FROM
 fac_ta_factura_cabecera AS fc
 LEFT JOIN int_tabla_general AS td ON(fc.ch_fac_tipodocumento = substring(TRIM(tab_elemento) for 2 from length(TRIM(tab_elemento))-1) AND tab_tabla ='08' AND tab_elemento != '000000')
 WHERE
 fc.ch_fac_tipodocumento||fc.ch_fac_seriedocumento||fc.ch_fac_numerodocumento||fc.cli_codigo = '" . $params['registroid'] . "'
;";
		$line = '';
		if($sqlca->query($query) < 0) {
			$result = array('error' => true, 'query' => $query);
		} else {
			$row = $sqlca->fetchrow();

			$_line = FacturasModel::schemaLine(array(
				$row['date'],
				$row['doc_type'],
				$row['serie'],
				'0'.$row['number'],
			));

			if (!$_line['valid']) {
				$result = array(
				 	'error' => true,
				);
			} else {
				$result = array(
					'error' => false,
					'line' => $_line['line'],
					'row' => $row,
					'query' => $query
				);
			}
		}

		return $result;
	}

	function getDataLineEFE($params, $text) {
		global $sqlca;
		$result = array();

		if ($params['optype'] == 0) {

			$query = "
SELECT
 fc.ch_fac_tiporecargo2 AS free_transfer,--Transferencia gratuita
 fc.nu_fac_impuesto1 AS igv,
 fc.nu_fac_valortotal AS grand_total,
 mone.tab_descripcion AS currency_name,
 com.nu_fac_complemento_direccion AS ref_detrac
 ,fc.nu_fac_descuento1 AS disc
FROM
fac_ta_factura_cabecera AS fc
LEFT JOIN fac_ta_factura_complemento AS com ON(fc.ch_fac_tipodocumento = com.ch_fac_tipodocumento AND fc.ch_fac_seriedocumento = com.ch_fac_seriedocumento AND fc.ch_fac_numerodocumento = com.ch_fac_numerodocumento AND fc.cli_codigo = com.cli_codigo)
JOIN int_tabla_general AS mone ON(fc.ch_fac_moneda = (substring(trim(mone.tab_elemento) for 2 from length(trim(mone.tab_elemento))-1)) AND mone.tab_tabla = '04' AND mone.tab_elemento != '000000')
WHERE
fc.ch_fac_tipodocumento||fc.ch_fac_seriedocumento||fc.ch_fac_numerodocumento||fc.cli_codigo = '".$params['registroid']."';";

			if ($sqlca->query($query) < 0) {
				return array('error' => true, 'query' => $query);
			}

			$row = $sqlca->fetchRow();

			$row["notipo"] = 'E';

			$row['free_transfer'] = trim($row['free_transfer']);
			//nucodigo
			$row['nucodigo'] = '1000';
			if ($row["free_transfer"] == 'S' && $row["igv"] > 0) {//exo
				$row['nucodigo'] = '1002';
			} else if ($row["free_transfer"] == 'S' && $row["igv"] == 0) {//transferencia gratuita
				$row['nucodigo'] = '2001';
			}

			//detraccion
			$ref_detrac = explode('*', $row['ref_detrac']);
			if ($ref_detrac[0] != '' && $ref_detrac[1] != '' && $ref_detrac[2] != '' && $ref_detrac[3] != '') {
				$row['detraccion'] = '1';
			} else {
				$row['detraccion'] = '0';
			}

			/* INI Get Detraccion */
			if ($row['detraccion'] == '1') {
				$query_ = "
SELECT
nu_fac_complemento_direccion AS ref_detrac
FROM
fac_ta_factura_complemento
WHERE
ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento||cli_codigo = '".$params['registroid']."';";

				if ($sqlca->query($query_) < 0) {
					return array('error' => true, 'query' => $query, 'query_' => $query_);
				}

				$row_detraccion = $sqlca->fetchRow();
				$arr_ref_detrac = explode('*', $row_detraccion['ref_detrac']);
				$ref_detrac = "E|2006|OPERACION SUJETA A DETRACCION\n";
				$ref_detrac .= "E|3000|".$arr_ref_detrac[3]."\n";
				$ref_detrac .= "E|3001|".$arr_ref_detrac[0]."\n";
				$row_detraccion["e_detraccion"] = $ref_detrac;
			}
			/* END Detraccion */

			if ($row["nucodigo"] == '1000' && $row['detraccion'] == '1') {
				//$txt_tmp = "\n".$row["notipo"]."|".$row["nucodigo"]."|".FacturasModel::MontoMonetarioEnLetras($row["grand_total"],$row["currency_name"]);
				$txt_tmp = "\n".$row["notipo"]."|".$row["nucodigo"]."|".FacturasModel::MontoMonetarioEnLetras($params["_total"],$row["currency_name"]);
				$txt_tmp .= "\n".$row_detraccion["e_detraccion"];
			} else if ($row["nucodigo"] == '1000' && $row['detraccion'] == '0') {
				//$txt_tmp = "\n".$row["notipo"]."|".$row["nucodigo"]."|".FacturasModel::MontoMonetarioEnLetras($row["grand_total"],$row["currency_name"]);
				$txt_tmp = "\n".$row["notipo"]."|".$row["nucodigo"]."|".FacturasModel::MontoMonetarioEnLetras($params["_total"],$row["currency_name"]);
			} else if ($row["nucodigo"] == '1002' && $row['detraccion'] == '0') {
				$txt_tmp = "\nE|1000|CERO Y 00/100 ".$row["currency_name"];//
				//$txt_tmp = "\nE|1000|".FacturasModel::MontoMonetarioEnLetras($params["_total"],$row["currency_name"]);
				$txt_tmp .= "\n".$row["notipo"]."|".$row["nucodigo"]."|TRANSFERENCIA GRATUITA DE UN BIEN Y/O SERVICIO PRESTADO GRATUITAMENTE";
			} else { //EXONERADOS
				$row["_grand_total"] = $row["grand_total"] - $row["disc"];
				//$txt_tmp = "\nE|1000|".FacturasModel::MontoMonetarioEnLetras($row["_grand_total"],$row["currency_name"]);
				$txt_tmp = "\nE|1000|".FacturasModel::MontoMonetarioEnLetras($row["_grand_total"],$row["currency_name"]);
				$txt_tmp .= "\n".$row["notipo"]."|".$row["nucodigo"]."|BIENES TRANSFERIDOS EN LA AMAZONIA REGION SELVA PARA SER CONSUMIDOS EN LA MISMA";
			}
			$content = $text.$txt_tmp;
			if ($params['isFreeTransfer']) {
				//$content .= "\nE|1000|".FacturasModel::MontoMonetarioEnLetras($row["grand_total"],$row["currency_name"]);
				//$content .= "\nE|1002|TRANSFERENCIA GRATUITA DE UN BIEN Y/O SERVICIO PRESTADO GRATUITAMENTE";
			}
			return array('error' => false, 'line' => $content, 'query' => $query, 'query_' => $query_);
		} else {
			$content = $text;
			//aqui va?
			if ($params['isFreeTransfer']) {
				$content .= "\nE|1002|TRANSFERENCIA GRATUITA DE UN BIEN Y/O SERVICIO PRESTADO GRATUITAMENTE";
			}
			return array('error' => false, 'line' => $content, 'query' => $query, 'query_' => $query_);
		}
	}

	function schemaLine($params) {
		$line = '';
		$return = array();
		$count = count($params);
		for ($i = 0; $i < $count; $i++) { 
			$line .= $params[$i];
			if ($i != ($count -1)) {
				$line .= '|';
			}
		}
		return array(
			'valid' => FacturasModel::checkValidLineFe($line),
			'line' => $line,
		);
	}

	/**
	 * Validacion para el monto total de boletas
	 * Verificacion de datos de cliente(cli_ruc, cli_razsocial, cli_direccion) cuando el monto total de la factura excede los 700 soles
	 * @return ['error'] boolean
	 * @return ['code'] int
	 * @return ['message'] string
	 */
	function validTotalTicket($_id) {
		global $sqlca;

		$sql = "SELECT
 fc.ch_fac_tipodocumento AS doc_type_ocs,
 fc.nu_fac_valortotal AS grand_total,
 TRIM(bpartner.cli_ruc) AS taxid,
 bpartner.cli_razsocial AS name,
 bpartner.cli_direccion AS address
FROM
 fac_ta_factura_cabecera AS fc
 JOIN int_clientes bpartner ON (fc.cli_codigo = bpartner.cli_codigo)
WHERE
 fc.ch_fac_tipodocumento||fc.ch_fac_seriedocumento||fc.ch_fac_numerodocumento||fc.cli_codigo = '" . $_id . "';
";

		if ($sqlca->query($sql) < 0) {
			return array('error' => true, 'code' => -1, 'message' => 'Error, intentenlo en otro momento');
		}
		if ($sqlca->query($sql) == 0) {
			return array('error' => true, 'code' => 0, 'message' => 'No se encontró este documento');
		}

		$row = $sqlca->fetchrow();
		if ($row['doc_type_ocs'] == '35') {
			if ($row['grand_total'] > 700.00) {
				if (strlen($row['taxid']) != 8) {
					return array('error' => true, 'code' => 5, 'message' => 'Código de Cliente inválido');
				}
				if ($row['taxid'] != '' && trim($row['name']) != '' && trim($row['address']) != '') {
					return array('error' => false, 'code' => 4, 'message' => 'Cumple con las condiciones de una boleta que excede los 700 soles');
				} else {
					return array('error' => true, 'code' => 3, 'message' => 'El cliente debe tener Nombre y Dirección');
				}
			} else {
				return array('error' => false, 'code' => 2, 'message' => 'Es una boleta pero excede los 700 soles');
			}
		} else {
			return array('error' => false, 'code' => 1, 'message' => 'No es una boleta');
		}
	}

	function obtenerSucursal($almacen_id) {
		global $sqlca;

		$sql = "SELECT
			ruc, razsocial, ch_direccion, ebiauth, ebiurl
		FROM
			int_ta_sucursales
		WHERE
			ebiauth != '' AND ruc = (
			SELECT DISTINCT
				SUCUR.ruc
			FROM
				inv_ta_almacenes ALMA
			JOIN int_ta_sucursales SUCUR ON (
				SUCUR.ch_sucursal = ALMA.ch_sucursal
			)
			WHERE
				ebiauth != '' AND ALMA.ch_sucursal = '$almacen_id'
		);";

		if ($sqlca->query($sql) < 0) {
			return null;
		}

		$row = $sqlca->fetchrow();
		return  $row;
	}

	function getInfoInvoice($_id) {
		global $sqlca;

		$sql = "SELECT
			complemento.ch_fac_tipodocumento,
			complemento.cli_codigo,
			complemento.dt_fac_fecha AS dateacct, --2
			cabecera.ch_fac_moneda,
			moneda.tab_descripcion AS currency_name, --4
			clientes.cli_razsocial AS bpartner_name,
			clientes.cli_direccion, --6
			cabecera.nu_fac_valorbruto,
			cabecera.nu_fac_impuesto1, --8
			cabecera.nu_fac_valortotal,
			complemento.ch_fac_observacion2, --ref
			complemento.ch_fac_observacion1, --11: observacion
			_general.tab_descripcion AS documenttype_name, --12
			cabecera.ch_almacen AS warehouse_id,
			(string_to_array(complemento.nu_fac_complemento_direccion, '*'))[1] AS no_detraccion_cuenta,
			(string_to_array(complemento.nu_fac_complemento_direccion, '*'))[2] AS nu_detraccion_importe,
			(string_to_array(complemento.nu_fac_complemento_direccion, '*'))[3] AS nu_detraccion_porcentaje,
			(string_to_array(complemento.nu_fac_complemento_direccion, '*'))[4] AS nu_detraccion_codigo,
			cabecera.nu_tipo_pago,
			TPAGO.tab_descripcion AS no_tipo_pago,
			cabecera.fe_vencimiento,
			RFC.fe_emision
			,CASE WHEN cabecera.ch_fac_tiporecargo2 IS NULL OR cabecera.ch_fac_tiporecargo2 = '' THEN 0 -- NORMAL
			 WHEN cabecera.ch_fac_tiporecargo2 = 'S' AND cabecera.nu_fac_impuesto1 = 0 THEN 1 -- EXO
			 WHEN cabecera.ch_fac_tiporecargo2 = 'S' AND cabecera.nu_fac_impuesto1 > 0 THEN 2 -- TG
			 END AS typetax
			,CASE WHEN cabecera.ch_fac_tipodocumento = '10' THEN 1
			 ELSE 0 END AS is_invoice
			,clientes.cli_ruc AS ruc,
			cabecera.ch_liquidacion
		FROM
			fac_ta_factura_cabecera cabecera
			JOIN fac_ta_factura_complemento complemento ON (
			cabecera.ch_fac_seriedocumento = complemento.ch_fac_seriedocumento
			AND cabecera.ch_fac_numerodocumento = complemento.ch_fac_numerodocumento
			AND cabecera.ch_fac_tipodocumento = complemento.ch_fac_tipodocumento
			)
			JOIN int_tabla_general AS moneda ON (
				cabecera.ch_fac_moneda = (
					SUBSTRING (
						TRIM (moneda.tab_elemento) FOR 2
						FROM
						LENGTH (TRIM(moneda.tab_elemento)) - 1
					)
				)
				AND moneda.tab_tabla = '04'
				AND moneda.tab_elemento != '000000'
			)
			JOIN int_clientes clientes ON complemento.cli_codigo = clientes.cli_codigo
			JOIN int_tabla_general _general ON (
				complemento.ch_fac_tipodocumento = (
					SUBSTRING (
						TRIM (_general.tab_elemento) FOR 2
						FROM
						LENGTH (
								TRIM (_general.tab_elemento)
						) - 1
					)
				)
				AND _general.tab_tabla = '08'
				AND _general.tab_elemento != '000000'
			)
			LEFT JOIN (
			SELECT
				ch_fac_tipodocumento AS nu_tipodoc,
				ch_fac_seriedocumento AS nu_seriedoc,
				ch_fac_numerodocumento AS nu_numerodoc,
				dt_fac_fecha AS fe_emision
			FROM
				fac_ta_factura_cabecera
			) AS RFC ON (
				RFC.nu_numerodoc = (string_to_array(complemento.ch_fac_observacion2, '*'))[1]
				AND RFC.nu_seriedoc = (string_to_array(complemento.ch_fac_observacion2, '*'))[2]
				AND RFC.nu_tipodoc = (string_to_array(complemento.ch_fac_observacion2, '*'))[3]
			)
			LEFT JOIN int_tabla_general AS TPAGO ON(TPAGO.tab_tabla = '05' AND TPAGO.tab_elemento<>'000000' AND cabecera.nu_tipo_pago = SUBSTRING(TRIM(TPAGO.tab_elemento) FOR 2 FROM LENGTH(TRIM(TPAGO.tab_elemento))-1))
		WHERE
			cabecera.ch_fac_tipodocumento||cabecera.ch_fac_seriedocumento||cabecera.ch_fac_numerodocumento||cabecera.cli_codigo = '" . $_id . "';";

		if ($sqlca->query($sql) < 0) {
			return null;
		}

		$row = $sqlca->fetchrow();
		return  $row;
	}

	function getPlate($data) {
		global $sqlca;
		$result = '';

		$query = "SELECT
DISTINCT(c.ch_placa) AS plate
FROM
val_ta_complemento_documento d
JOIN val_ta_cabecera c ON (d.ch_sucursal = c.ch_sucursal AND d.dt_fecha = c.dt_fecha AND d.ch_numeval = c.ch_documento)
WHERE
d.ch_liquidacion = '".$data['ch_liquidacion']."' AND d.ch_sucursal = '".$data['ch_almacen']."'
AND octet_length(c.ch_placa) > 3;";

		error_log('getPlate: '.$query);

		if ($sqlca->query($query) < 0) {
			$result = null;
		} else {
			$count = $sqlca->numrows();

			$result = array('count' => $sqlca->numrows(), 'rows' => $sqlca->fetchAll());
		}
		return $result;
	}

	function getTax() {
		global $sqlca;
		$sql = "SELECT util_fn_igv();";
		if ($sqlca->query($sql) < 0) {
			return null;
		} else {
			return $sqlca->fetchRow();
		}
	}
}