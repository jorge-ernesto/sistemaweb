<?php
extract($_REQUEST);
include("../../menu_princ.php");
include("../store_procedures.php");
include("../../clases/funciones.php");
include("impresion-utils.php");
include("/sistemaweb/cpagar/funciones.php");
include("/sistemaweb/func_print_now.php");
include("fac_pdf_impresiones.php");

$meses = Array(
	"01"	=> "Enero",
	"02"	=>	"Febrero",
	"03"	=>	"Marzo",
	"04"	=>	"Abril",
	"05"	=>	"Mayo",
	"06"	=>	"Junio",
	"07"	=>	"Julio",
	"08"	=>	"Agosto",
	"09"	=>	"Setiembre",
	"10"	=>	"Octubre",
	"11"	=>	"Noviembre",
	"12"	=>	"Diciembre"
);

function formatMes($fecha) {
	list($dia,$mes,$ano) = explode("/", $fecha);
	return $mes;
}

function formatNameMes($fecha) {
	list($dia,$mes,$ano) = explode("/", $fecha);
	if ($mes == '01')
		$mes = 'Enero';
	else if ($mes == '02')
		$mes = 'Febrero';
	else if ($mes == '03')
		$mes = 'Marzo';
	else if ($mes == '04')
		$mes = 'Abril';
	else if ($mes == '05')
		$mes = 'Mayo';
	else if ($mes == '06')
		$mes = 'Junio';
	else if ($mes == '07')
		$mes = 'Julio';
	else if ($mes == '08')
		$mes = 'Agosto';
	else if ($mes == '09')
		$mes = 'Setiembre';
	else if ($mes == '10')
		$mes = 'Octubre';
	else if ($mes == '11')
		$mes = 'Noviembre';
	else if ($mes == '12')
		$mes = 'Diciembre';
	return $mes;
}

function formatDia($fecha) {
	list($dia,$mes,$ano) = explode("/", $fecha);
	return $dia;
}

function formatAno($fecha) {
	list($dia,$mes,$ano) = explode("/", $fecha);
	return $ano;
}

$funcion = new class_funciones;
$clase_error = new OpensoftError;
$clase_error->_error();
$conector_id = $funcion->conectar("","","","","");
$COMP = @$_SESSION["ARR_COMP"];

if($_REQUEST['c_documento'] == 'DP') {
	$tipo_documento = '20';

	switch($_REQUEST['accion']) {
		case "Generar":
		// Inicio de Cabecera

		unset($_SESSION['LISTADO']);
		$query = " SELECT
						fa.ch_fac_tipodocumento,
						fa.ch_fac_seriedocumento,
						fa.ch_fac_numerodocumento,
						fa.cli_codigo
					FROM
						fac_ta_factura_cabecera fa
					WHERE
						fa.ch_fac_tipodocumento='" . pg_escape_string($tipo_documento) . "'
						AND fa.ch_fac_seriedocumento='" . pg_escape_string($c_serie) . "'
						AND fa.ch_fac_numerodocumento BETWEEN '" . pg_escape_string($c_num_desde) . "' AND '" . pg_escape_string($c_num_hasta) . "'
					ORDER BY
						fa.ch_fac_numerodocumento;
					";

		echo "<!--QUERY 2 : $query-->\n";

		$rs_facturas = pg_exec($query);

		for($j=0;$j<pg_numrows($rs_facturas);$j++){
			$FAC = pg_fetch_array($rs_facturas,$j);
			$num_docu = $FAC["ch_fac_numerodocumento"];
			$tipo_docu = $FAC["ch_fac_tipodocumento"];
			$serie_docu = $FAC["ch_fac_seriedocumento"];
			$cli_docu = $FAC["cli_codigo"];

			$query = " SELECT
							ch_fac_observacion1,
							ch_fac_observacion2,
							ch_fac_observacion3,
							nu_fac_complemento_direccion
						FROM
							fac_ta_factura_complemento
						WHERE
							ch_fac_tipodocumento='" . pg_escape_string($tipo_docu) . "'
							AND ch_fac_seriedocumento='" . pg_escape_string($serie_docu) . "'
							AND ch_fac_numerodocumento='" . pg_escape_string($num_docu) . "'
							AND cli_codigo='" . pg_escape_string($cli_docu) . "'
						";

			echo "<!--QUERY 2 : $query-->\n"; 

			$rs2 = pg_query($query);

			if (pg_numrows($rs2) > 0) {
				$obs1 = pg_fetch_result($rs2, 0, 0);
				$obs2 = pg_fetch_result($rs2, 0, 1);
				$obs3 = pg_fetch_result($rs2, 0, 2);
				$comd = pg_fetch_result($rs2, 0, 3);
			}

			$query = " SELECT
								nu_fila,nu_columna,
								trim(ch_valor_campo) as ch_valor_campo,
								ch_secuencia,
								ch_mascara_campo
							FROM
								fac_ta_formatos_doc
							WHERE
								ch_tipo_documento='" . $c_documento . "'
								AND ch_parte_documento='C'
							ORDER BY
								nu_fila,
								nu_columna";

			echo "<!--QUERY 2 : $query-->\n";

			$rs = pg_exec($query);
			$datos = null;
			$ind = 0;

			for($i=0;$i<pg_numrows($rs);$i++) {
				$A = pg_fetch_array($rs,$i);
				$f["X"] = $A["nu_columna"];
				$f["Y"] = $A["nu_fila"];

				if(ereg("fa.", $A["ch_valor_campo"])) {
					$tmp = separarCadena($A["ch_valor_campo"],".");

					if($tmp[0]==$A["ch_valor_campo"]){
						eval($tmp[0].";");
					} else {
						$data = $FAC[$tmp[1]];
					}
				} else {
					if(ereg("data", $A["ch_valor_campo"])) {
						eval($A["ch_valor_campo"].";");
					} else {
						$data = $A["ch_valor_campo"];
					}
				}

				$f["data"] = $data;
				$datos[$i] = $f;
				$ind = $i;
			}
			// Fin de la cabecera

			$clinea = "3";
			$query = "	SELECT
								nd.ch_fac_tipodocumento,
								nd.ch_fac_seriedocumento,
								nd.ch_fac_numerodocumento,
								nd.ch_nro_factura,
								trim(cli.cli_codigo)||' - '||trim(cli.cli_rsocialbreve) as cliente,
								to_char(fc.dt_fac_fecha, 'DD/MM/YYYY') as dt_fac_fecha,
								nd.ch_nombre_item,
								nd.nu_cantidad,
								nd.nu_precio_contratado,
								nd.nu_monto_contratado,
								nd.nu_monto_factura,
								nd.nu_diferencia
							FROM
								fac_ta_ncredcomp nd,
								fac_ta_factura_cabecera fc,
								int_clientes cli
							WHERE
								fc.ch_fac_tipodocumento = nd.ch_fac_tipodocumento
								AND fc.ch_fac_seriedocumento= nd.ch_fac_seriedocumento
								AND fc.ch_fac_numerodocumento= nd.ch_fac_numerodocumento
								AND fc.cli_codigo= cli.cli_codigo
								AND fc.ch_fac_numerodocumento = '".$num_docu."'
								AND fc.ch_fac_tipodocumento = '".$tipo_documento."'
								AND fc.ch_fac_seriedocumento = '".$serie_docu."'";

			echo "<!--QUERY 3 : $query-->\n";

			$rs_det = pg_exec($query);

			while($reg = pg_fetch_array($rs_det)){
				$listado[] = $reg;
			}

			$query = " SELECT
								nu_fila,
								nu_columna,
								trim(ch_valor_campo) as ch_valor_campo,
								ch_secuencia,
								ch_mascara_campo
							FROM
								fac_ta_formatos_doc
							WHERE
								ch_tipo_documento='" . $c_documento . "'
								AND ch_parte_documento='I'
								ORDER BY
								nu_fila,
								nu_columna
						";

			echo "<!--QUERY DETALLE : ".$query."-->\n";

			$rs = pg_exec($query);

			$contador = pg_numrows($rs_det);
			$listado[$contador]['ch_nombre_item']='TOTALES';

			for($k=0;$k<$contador;$k++) {
				$DB = pg_fetch_array($rs_det,$k);

				for($i=0;$i<pg_numrows($rs);$i++) {
					$A = pg_fetch_array($rs,$i);
					$f["X"] = $A["nu_columna"];
					$f["Y"] = $A["nu_fila"]+$k;
					$tmp = separarCadena($A["ch_valor_campo"],".");

					if($tmp[0]==$A["ch_valor_campo"]) {
						eval($tmp[0]);
						$totales[0] = "TOTAL";

						if(ereg("nu_cantidad", $tmp[0])) {
							$totales[1] += round($DB["nu_cantidad"],2);
							$listado[$contador]['nu_cantidad']=$totales[1];
						} elseif(ereg("nu_monto_contratado", $tmp[0])) {
							$totales[3] += round($DB["nu_monto_contratado"],2);
							$listado[$contador]['nu_monto_contratado']=$totales[3];
						} elseif(ereg("nu_monto_factura", $tmp[0])) {
							$totales[4] += round($DB["nu_monto_factura"],2);
							$listado[$contador]['nu_monto_factura']=$totales[4];
						} elseif(ereg("nu_diferencia", $tmp[0])) {
							$totales[5] += round($DB["nu_diferencia"],2);
							$listado[$contador]['nu_diferencia']=$totales[5];
						}
					} else {
						$data = $DB[$tmp[1]];
						$data = trim($data);
					}

					$f["data"] = $data;
					$ind++;
					$datos[$ind] = $f;
					$Row = ($f["Y"])+1;
				}
			}

			$_SESSION['LISTADO'][] = $listado;
			$_SESSION['LISTADO'][] = $listado;

			echo ExportaraPDFDiferenciadePrecio::GenerarReportePDF($_SESSION['LISTADO']);
			echo "<!--";
			echo "-->";

			$listado=array();

			for($i=0;$i<pg_numrows($rs); $i++) {
				$A = pg_fetch_array($rs, $i);
				$f["X"] = $A["nu_columna"];
				$f["Y"] = $Row;

				if($totales[$i] == "TOTAL")
					$data = $totales[$i];
				else
					$data=aplicarMascara(round ($totales[$i], 2), $A["ch_mascara_campo"]);

				$f["data"] = $data;
				$ind++;
				$datos[$ind] = $f;
			}

			echo "<!--";
			//print_r($datos);
			echo "-->";
			$totales=array();
			//fin del detalle
		}
		break;
	}
} else {
	switch($_REQUEST['c_documento']) {
		case "":
			break;
		case "":
			break;

		default:
			switch($_REQUEST['accion']) {
				case "Generar":    	
				if ($_REQUEST['c_num_hasta']==''){
					$c_num_hasta = $_REQUEST['c_num_desde'];
				}

				$query = " SELECT
									ch_fac_tipodocumento,
									ch_fac_seriedocumento,
									ch_fac_numerodocumento,
									cli_codigo
								FROM
									fac_ta_factura_cabecera
								WHERE
									ch_fac_tipodocumento='" . pg_escape_string($c_documento) . "'
									AND ch_fac_seriedocumento='" . pg_escape_string($c_serie) . "'
									AND ch_fac_numerodocumento BETWEEN '" . pg_escape_string($c_num_desde) . "' AND '" . pg_escape_string($c_num_hasta) . "'
									ORDER BY
									ch_fac_numerodocumento;
							";

				echo "<!--QUERY DETALLE : ".$query."-->\n";
					$rs_facturas = pg_exec($query);

					if (pg_numrows($rs_facturas)<=0) {
						echo('No existen documentos dentro de ese rango');
						$imprimir=false;
					} else {
						if ($_REQUEST['reimprimir']=='on') {
							$query = " SELECT
												ch_fac_tipodocumento,
												ch_fac_seriedocumento,
												ch_fac_numerodocumento,
												cli_codigo
											FROM
												fac_ta_factura_cabecera
											WHERE
												ch_fac_tipodocumento='" . pg_escape_string($c_documento) . "'
												AND ch_fac_seriedocumento='" . pg_escape_string($c_serie) . "'
												AND ch_fac_numerodocumento BETWEEN '" . pg_escape_string($c_num_desde) . "' AND '" . pg_escape_string($c_num_hasta) . "'
											ORDER BY
												ch_fac_numerodocumento;";

							echo "<!--QUERY DETALLE : ".$query."-->\n";

							$rs_facturas = pg_exec($query);
							$imprimir=true;
						} else {
							$query = " SELECT
												ch_fac_tipodocumento,
												ch_fac_seriedocumento,
												ch_fac_numerodocumento,
												cli_codigo
											FROM
												fac_ta_factura_cabecera
											WHERE
												ch_fac_tipodocumento='" . pg_escape_string($c_documento) . "'
												AND ch_fac_seriedocumento='" . pg_escape_string($c_serie) . "'
												AND ch_fac_numerodocumento BETWEEN '" . pg_escape_string($c_num_desde) . "' AND '" . pg_escape_string($c_num_hasta) . "'
												and ch_fac_impreso is null
											ORDER BY
												ch_fac_numerodocumento;";

							echo "<!--QUERY DETALLE : ".$query."-->\n";

							$rs_facturas = pg_exec($query);
							$imprimir=true;
						}
					}

					if($imprimir) {
						if (pg_numrows($rs_facturas)==0) 
							echo('El documento ya ha sido impreso');

					
			for ($j = 0; $j < pg_numrows($rs_facturas); $j++) {
                        
				$FAC 		= pg_fetch_array($rs_facturas, $j);
				$num_docu 	= $FAC["ch_fac_numerodocumento"];
				$tipo_docu 	= $FAC["ch_fac_tipodocumento"];
				$serie_docu 	= $FAC["ch_fac_seriedocumento"];
				$cli_docu 	= $FAC["cli_codigo"];

		         //cabecera
		         $query="
					SELECT
						DISTINCT ON(liqui.ch_placa) liqui.ch_placa AS ch_fac_placa,
						cab.cli_codigo,
						ot.cli_razsocial as ch_fac_nombreclie,
						ot.cli_ruc as ch_fac_ruc,
						to_char(cab.dt_fac_fecha,'dd/mm/yyyy') as dt_fac_fecha,
						cab.ch_liquidacion,
						ot.cli_direccion as nu_fac_direccion,
						(coalesce(ot.cli_direccion,'')||' - '||trim(tab2.tab_descripcion)) as cli_distrito,
						trim(tab.tab_desc_breve) as tab_desc_breve,
						cab.nu_fac_valortotal,
						cab.nu_fac_impuesto1,
						tab3.tab_descripcion as forma_pago,
						to_char(cab.dt_fac_fecha::DATE + tab3.tab_num_01::INTEGER,'dd/mm/yyyy') as fec_vencimiento,
						par.par_valor as direc,
						cab.ch_fac_numerodocumento, 
						cab.ch_fac_seriedocumento,
						CASE WHEN cab.ch_fac_credito = 'S' THEN 'Credito' ELSE 'Contado' END as ch_fac_anticipo,
						CASE
							WHEN ch_fac_forma_pago = '01' THEN 'Factura a 07 dias'
							WHEN ch_fac_forma_pago = '02' THEN 'Factura a 15 dias'
							WHEN ch_fac_forma_pago = '03' THEN 'Factura a 30 dias'
							WHEN ch_fac_forma_pago = '04' THEN 'Factura a 45 dias'
							WHEN ch_fac_forma_pago = '05' THEN 'Factura a 60 dias'
							WHEN ch_fac_forma_pago = '06' THEN 'CONTRA/ENTREGA'
							WHEN ch_fac_forma_pago = '07' THEN 'CONSIGNACION'
							WHEN ch_fac_forma_pago = '08' THEN 'Factura a 05 dias'
							WHEN ch_fac_forma_pago = '09' THEN 'Factura a 10 dias'
							WHEN ch_fac_forma_pago = '10' THEN 'Factura a 03 dias'
							WHEN ch_fac_forma_pago = '11' THEN 'Factura a 21 dias'
							WHEN ch_fac_forma_pago = '13' THEN 'Factura a 0 dias'
							WHEN ch_fac_forma_pago = '14' THEN 'Factura a 01 dias'
							WHEN ch_fac_forma_pago = '18' THEN 'Factura a 18 dias'
							WHEN ch_fac_forma_pago = '21' THEN 'Factura a 04 dias'
						END as ch_fac_forma_pago,
						'18' as igv,
						'AGENTE DE RETENCION' rete
					FROM
						fac_ta_factura_complemento cli,
						fac_ta_factura_cabecera cab
						LEFT JOIN val_ta_complemento_documento liqui ON (liqui.ch_fac_tipodocumento = cab.ch_fac_tipodocumento AND liqui.ch_fac_seriedocumento = cab.ch_fac_seriedocumento AND liqui.ch_fac_numerodocumento = cab.ch_fac_numerodocumento AND liqui.ch_cliente = cab.cli_codigo),
						int_tabla_general tab,
						int_tabla_general tab2,
						int_tabla_general tab3,
						int_clientes ot,
						int_parametros par
					WHERE
					    cab.ch_fac_tipodocumento		= '" . pg_escape_string($tipo_docu) . "'
						AND cab.ch_fac_seriedocumento 	= '" . pg_escape_string($serie_docu) . "'
						AND cab.ch_fac_numerodocumento 	= '" . pg_escape_string($num_docu) . "'
						AND cab.cli_codigo 				= '" . pg_escape_string($cli_docu) . "'
						AND cli.cli_codigo				= cab.cli_codigo
						AND ot.cli_codigo 				= cab.cli_codigo
						AND cli.ch_fac_tipodocumento 	= cab.ch_fac_tipodocumento
						AND cli.ch_fac_seriedocumento 	= cab.ch_fac_seriedocumento
						AND cli.ch_fac_numerodocumento 	= cab.ch_fac_numerodocumento
						AND tab.tab_tabla 				= '04'
						AND tab.tab_elemento 			= lpad(cab.ch_fac_moneda,6,'0')
						AND tab2.tab_tabla 				= '02'
						AND tab2.tab_elemento 			= ot.cli_distrito 
						AND tab3.tab_tabla 				= '96' 
						AND tab3.tab_elemento 			= lpad(cab.ch_fac_forma_pago,6,'0')
						AND par.par_nombre 				= 'dires';
				";
				$rs = pg_exec($query);

		        $query2 = "
				SELECT 
					ch_fac_observacion1, 
					ch_fac_observacion2, 
					ch_fac_observacion3, 
					nu_fac_complemento_direccion 
			    FROM 
					fac_ta_factura_complemento 
			    WHERE 
			  		ch_fac_tipodocumento='" . pg_escape_string($tipo_docu) . "' 
		      		AND ch_fac_seriedocumento='" . pg_escape_string($serie_docu) . "' 
		      		AND ch_fac_numerodocumento='" . pg_escape_string($num_docu) . "' 
		      		AND cli_codigo='" . pg_escape_string($cli_docu) . "'
				";
		                
				//echo $query;

                $rs2 = pg_query($query2);
                if (pg_numrows($rs2) > 0) {
                    $obs1 = pg_fetch_result($rs2, 0, 0);
                    $obs2 = pg_fetch_result($rs2, 0, 1);
                    $obs3 = pg_fetch_result($rs2, 0, 2);
                    $comd = pg_fetch_result($rs2, 0, 3);
                }

                // Dirección de la empresa
                $d = "	SELECT par_valor FROM int_parametros where par_nombre='dires'; ";
                $dire = pg_query($d);
                if (pg_numrows($dire) > 0) {
                    $direc = pg_fetch_result($dire, 0, 0);
                }

                $DB = pg_fetch_array($rs, 0);
                $moneda = $DB['tab_desc_breve'];


                //DE ACA SE SACA LOS FORMATOS DE CORRESPONDIENTE ALA FACTURA
                //CABECERA

				$query="
				SELECT 
					nu_fila,
					nu_columna, 
			      	trim(ch_valor_campo) as ch_valor_campo, 
			      	ch_secuencia,  
			      	ch_mascara_campo 
				FROM 
					fac_ta_formatos_doc 
				WHERE 
					ch_tipo_documento 		= '" . pg_escape_string($tipo_docu) . "'
					AND ch_parte_documento 	= 'C'
				ORDER BY
					nu_fila, 
					nu_columna;
				";
		                
		                $rs = pg_exec($query);

		                $datos	= null;
		                $ind	= 0;

		                for ($i = 0; $i < pg_numrows($rs); $i++) {

					$A	= pg_fetch_array($rs, $i);
					$f["X"] = $A["nu_columna"];
					$f["Y"] = $A["nu_fila"];

					$tmp = separarCadena($A["ch_valor_campo"], ".");

		                    if ($tmp[0] == $A["ch_valor_campo"])
		                    	eval($tmp[0] . ";");
		                    else
		                    	$data = $DB[$tmp[1]];

		                    $f["data"] 	= $data;
		                    $f["TC"]	= "C";
		                    $f["seq"] 	= $A["ch_secuencia"];
		                    $datos[$i] 	= $f;
		                    $ind 	= $i;

		                }


                        //DETALLE DE PRODUCTOS
//					'" . pg_escape_string($moneda) . "' AS tab_desc_breve

                        $query="
				SELECT 
					det.art_codigo,
					TRIM(det.ch_art_descripcion) AS art_descripcion,
					substring(art.art_unidad from 4 for 3) AS art_unidad,
					ROUND(det.nu_fac_cantidad,3) AS nu_fac_cantidad,
					ROUND(det.nu_fac_precio,4) AS nu_fac_precio,
					det.nu_fac_valortotal AS nu_fac_valortotal
				FROM
					fac_ta_factura_detalle det,
					int_articulos art
				WHERE
					det.ch_fac_tipodocumento	= '" . pg_escape_string($tipo_docu) . "'
					AND det.ch_fac_seriedocumento	= '" . pg_escape_string($serie_docu) . "'
					AND det.ch_fac_numerodocumento	= '" . pg_escape_string($num_docu) . "'
					AND det.cli_codigo		= '" . pg_escape_string($cli_docu) . "'
					AND art.art_codigo		= det.art_codigo 
				ORDER BY
					det.art_codigo;
				";
                        
			 //print_r($query);
                        
                        $rs_det = pg_exec($query);

                        $query="
				SELECT nu_fila, " .
                                "nu_columna, " .
                                "trim(ch_valor_campo) as ch_valor_campo, " .
                                "ch_secuencia, " .
                                "ch_mascara_campo " .
                                "FROM fac_ta_formatos_doc " .
                                "WHERE ch_tipo_documento='" . pg_escape_string($tipo_docu) . "' " .
                                "AND ch_parte_documento='I' " .
                                "ORDER BY nu_fila,nu_columna";

                        $rs = pg_exec($query);
                        $s = 0;

                        for ($k = 0; $k < pg_numrows($rs_det); $k++) {
                            /* obtengo en un array el resultado el item $k */
                            $DB = pg_fetch_array($rs_det, $k);
                            /* recorro el formato que debe de ser de cada columna de los items */
                            for ($i = 0; $i < pg_numrows($rs); $i++) {
                                $A = pg_fetch_array($rs, $i);
                                if (strlen($DB["art_descripcion"]) > 38 and $A["ch_valor_campo"] == 'int_articulos.art_descripcion') {
                                    /* Si la descripcion tiene mas de 23 caracteres */
                                    $tmp = separarCadena($A["ch_valor_campo"], ".");
                                    $numero_lineas = floor(strlen($DB[$tmp[1]]) / 38);
                                    print_r('num_lin: ' . $numero_lineas);
                                    $numero_lineas += ($numero_lineas % 38 == 0 ? 0 : 1);
                                    for ($l = 0; $l < $numero_lineas; $l++) {
                                        $f["X"] = $A["nu_columna"];
                                        $f["Y"] = $A["nu_fila"] + $s;
                                        $data = substr($DB[$tmp[1]], 38 * $l, 38);
                                        $f["data"] = $data;
                                        $f["TC"] = "I";
                                        $f["seq"] = $A["ch_secuencia"];
                                        $ind++;
                                        $datos[$ind] = $f;
                                        $s++;
                                    }
                                    $s--;
                                } else {
                                    if ($A["ch_valor_campo"] == 'int_articulos.art_descripcion') {
                                        $numero_lineas = 1;
                                    }
                                    $f["X"] = $A["nu_columna"];
                                    //if ($A["ch_valor_campo"]=="int_articulos.art_descripcion" || $A["ch_valor_campo"]=="fac_ta_factura_detalle.art_codigo"){// esto estaba en comentario
                                    if ($A["ch_valor_campo"] == "int_articulos.art_descripcion" || $A["nu_columna"] == 0) {
                                        $f["Y"] = $A["nu_fila"] + $s;
                                        $tempo = $s;
                                    } else {
                                        $f["Y"] = $A["nu_fila"] + $tempo;
                                    }


                                    $tmp = separarCadena($A["ch_valor_campo"], ".");
                                    //print_r($tmp);
                                    if ($tmp[0] == $A["ch_valor_campo"]) {
                                        eval($tmp[0]);
                                    } else {
                                        $data = @$DB[$tmp[1]];
                                    }
                                    $f["data"] = $data;
                                    $f["TC"] = "I";
                                    $f["seq"] = $A["ch_secuencia"];
                                    $ind++;
                                    $datos[$ind] = $f;
                                }
                            }
                            //$s+=$numero_lineas;
                            $s++;
                        }
                       
                        //fin del detalle
	    	    
		    			//inicio del pie
				    $query = "
					SELECT
					    cab.cli_codigo,
					    cli.cli_razsocial,
					    cli.cli_ruc,
					    cab.ch_liquidacion,
					    cli.cli_direccion,
					    cli.cli_distrito,
					    to_char(ccob_ta_cabecera.dt_fechavencimiento,'dd/mm/yyyy') as dt_fac_fecha,
					    cab.nu_fac_valortotal,
					    trim(tab.tab_desc_breve) as tab_desc_breve,
					    coalesce(cab.nu_fac_recargo2,0) as nu_fac_recargo2,
					    coalesce(cab.nu_fac_impuesto1,0) as nu_fac_impuesto1,
					    trim(fac_ta_factura_complemento.ch_fac_observacion2) as ch_fac_observacion2,
					    fac_ta_factura_complemento.int_cantvales as int_cantvales,
					    to_char(ccob_ta_cabecera.dt_fechavencimiento,'dd/mm/yyyy') as dt_fechavencimiento, 
					    (cab.nu_fac_valorbruto - coalesce(cab.nu_fac_impuesto2,0)) as nu_base,
					    cab.nu_fac_valorbruto, 
					    cab.nu_fac_impuesto2,
					    cab.ch_fac_moneda,
					    '18' as igv,
					    fac_ta_factura_complemento.ch_fac_observacion1 as ch_fac_observacion1
					FROM
					    int_clientes cli,
					    int_tabla_general tab,
					    fac_ta_factura_cabecera cab
					    LEFT JOIN
						fac_ta_factura_complemento
					    ON
						    cab.ch_fac_numerodocumento = fac_ta_factura_complemento.ch_fac_numerodocumento 
						AND cab.ch_fac_seriedocumento = fac_ta_factura_complemento.ch_fac_seriedocumento
						AND cab.cli_codigo = fac_ta_factura_complemento.cli_codigo
					    LEFT JOIN
						ccob_ta_cabecera
					    ON
						    cab.ch_fac_numerodocumento = ccob_ta_cabecera.ch_numdocumento 
						AND cab.ch_fac_seriedocumento = ccob_ta_cabecera.ch_seriedocumento
						AND cab.cli_codigo = ccob_ta_cabecera.cli_codigo
					WHERE 
						cab.ch_fac_tipodocumento = '" . pg_escape_string($tipo_docu) . "'  
					    AND cab.ch_fac_seriedocumento = '" . pg_escape_string($serie_docu) . "'
					    AND cab.ch_fac_numerodocumento = '" . pg_escape_string($num_docu) . "' 
					    AND cab.cli_codigo = '" . pg_escape_string($cli_docu) . "'
					    AND cli.cli_codigo=cab.cli_codigo
					    AND tab.tab_tabla='04'
					    AND tab.tab_elemento=lpad(cab.ch_fac_moneda,6,'0');";
			echo "<!--QUERY DETALLE : ".$query."-->\n";
				      $rs_pie = pg_exec($query);
			    
				    $rs = pg_exec("select nu_fila,nu_columna,trim(ch_valor_campo) as ch_valor_campo
				    ,ch_secuencia,ch_mascara_campo from fac_ta_formatos_doc 
				    where ch_tipo_documento='" . $tipo_docu . "' and ch_parte_documento='P'	order by nu_fila,nu_columna");
			    
				    for($k=0;$k<pg_numrows($rs_pie);$k++){
					    $DB = pg_fetch_array($rs_pie,$k);
				    
					    for($i=0;$i<pg_numrows($rs);$i++){
						    $A = pg_fetch_array($rs,$i);

						    
                                if($A["ch_mascara_campo"]=='-'){//significa que hay glosa


						$tmp = separarCadena($A["ch_valor_campo"], "#");

                                //if ($tmp[0] == $A["ch_valor_campo"]) {
                                  //  $data = $tmp[0];
                                   // eval($tmp[0] . ";");
                               // } else {
                                   // $data = $DB[$tmp[1]];
                                    $data=$DB["ch_fac_observacion1"];
                              //  }
				$f["data"]=$data;
				$f["X"] = $A["nu_columna"];
                                $f["Y"] = $A["nu_fila"] + $k;
                                $f["TC"] = "P";
                                $f["seq"] = "1";


								if(strlen($f["data"])>40){
									$x=$f["X"];
									$y=$f["Y"];
									$cadenaglosa=explode(" ", $f["data"]);
									$glosatmp="";
									$comp=false;

									foreach ($cadenaglosa as $keyglosa => $glosa) {
									

										if(strlen($glosatmp)<=45 ){
											$glosatmp=$glosatmp." ".$glosa;
											//echo "==>".$glosatmp."*<br/>";
											$comp=false;
										}else{
											$ind++;
											$datos[$ind]=array("X"=>$x,"Y"=>$y,"TC"=>"P","seq"=>"1","data"=>$glosatmp);
											$y=$y+10;
											$glosatmp="";
											$glosatmp=$glosa;

											$comp=true;
										}
									}
									if(!$comp){


									$ind++;
									$datos[$ind]=array("X"=>$x,"Y"=>$y,"TC"=>"P","seq"=>"1","data"=>$glosatmp);
									$y=$y+10;
									$glosatmp="";

									
									}
									

								}else{
									 $ind++;
									$datos[$ind]=$f;
								}


                                }else{

				$f["X"] = $A["nu_columna"];
                                $f["Y"] = $A["nu_fila"] + $k;

                                $tmp = separarCadena($A["ch_valor_campo"], "#");

                                if ($tmp[0] == $A["ch_valor_campo"]) {
                                    $data = $tmp[0];
                                    eval($tmp[0] . ";");
                                } else {
                                    $data = $DB[$tmp[1]];
                                }

                                $f["data"] = $data;
                                $f["TC"] = "P";
                                $f["seq"] = $A["ch_secuencia"];
                                $ind++;
                                $datos[$ind] = $f;
                            						}

						


				    
					    }
			    
				    }
		    
		    //fin del pie
			switch (trim($tipo_docu)) {
	      		case '10':
				$clinea = "5";
	      			break;
	      		case '20':
				$clinea = "9";
	      			break;
	      		case '35':
				$clinea = "6.5";
	      			break;
	      		default:
	      			$clinea = "5";
	      			break;
	    	}
			
			if(isset($_REQUEST['pdf'])){
                include_once('impresion_documento.php');
				$obj=new impresion_documentos();
				$obj->TmpReportePDFFactura($datos,$tipo_docu);  
            }else{
				crearArchivoXY($datos,"/sistemaweb/tmp/factura-$num_docu.txt", $clinea);
		    	print_now("/sistemaweb/tmp/factura-".$num_docu.".txt");
            }

			$valor = "/sistemaweb/tmp/factura-" . $num_docu . ".txt";

			$sql = "
			UPDATE
				fac_ta_factura_cabecera
	    	SET
	    		ch_fac_impreso = 'S'
	    	WHERE
	    		ch_fac_tipodocumento 		= '" . pg_escape_string($tipo_docu) . "'
				AND ch_fac_seriedocumento 	= '" . pg_escape_string($serie_docu) . "'
				AND ch_fac_numerodocumento 	= '" . pg_escape_string($num_docu) . "';
			";
			pg_exec($sql);
	    } //for de facturas
	}
}
break;
}
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head>
		<title>Datos Complementarios</title>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<link href="../../sistemaweb.css" rel="stylesheet" type="text/css">
		<link href="../../css/formulario.css" rel="stylesheet" type="text/css">
		<script language="JavaScript">
		function ocultarObj(Campo, Obj) {

		}
		</script>
	</head>
	<body>
		<script language="JavaScript" src="/sistemaweb/ventas_clientes/js/ventas.js"></script>
		<div class="form" align="center">
			<form name="form1" method="post">
				<table border="0" class="form_body" cellpadding="1" cellspacing="1">
					<caption class="form_title">Impresion de Documentos</caption>
					<tr> 
						<td class="form_group">
							<table border="0">
								<tr>
									<td>
										<table border="0">
											<tr>
												<td class="form_label">Tipo de documento : </td>
												<td>
													<select name="c_documento" class="form_combo">
    												<?php	
													$rs = combo("documentos_sunat");
													for($i=0;$i<pg_numrows($rs);$i++) {
														$A = pg_fetch_array($rs,$i);
														print "<option value='$A[0]'";
														if ($A[0] == $_REQUEST['c_documento']) print " selected";
														print ">$A[1]</option>";
													}
													if ($_REQUEST['c_documento']=="DP") $Selected = "selected";
													echo '<option value="DP" '.@$Selected.'>DIFERENCIA DE PRECIOS</option>';
													?>
													</select>
												</td>
											</tr>
											<tr>
												<td class="form_label">
													<input type="checkbox" name="reimprimir" <?php (isset($_REQUEST['reimprimir'])?"checked":"") ?> onClick="ocultar_caja(this);" /> 
													Reimprimir Factura 
												</td>
												<td class="form_label">
													<input type="checkbox" name="pdf" <?php (isset($_REQUEST['pdf'])?"checked":"") ?> onClick="ocultar_caja(this);" /> 
													Generar pdf 
												</td>
											</tr>
										</table>
									</td>
									<td>
										<table border="0">
											<tr>
												<td colspan="2">
													<table border="0" width="100%" id='tbserie'>
														<tr>
															<td class="form_label" align="left">Serie</td>
															<td class="form_label">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; : <input name="c_serie" type="text" class="form_input" value="<?php echo $_REQUEST['c_serie']; ?>" size="4" maxlength="4" onKeyPress="return validar(event,2);"></td>
														</tr>
													</table>
												</td>
											</tr>
											<tr>
												<td class="form_label">Num. Inicial</td>
												<td class="form_label"> : <input name="c_num_desde" type="text" class="form_input" value="<?php echo $c_num_desde;?>" size="7" maxlength="7" onKeyPress="return validar(event,2);"></td>
											</tr>
											<tr>
												<td class="form_label">Num. Final</td>
												<td class="form_label"> : <input name="c_num_hasta" type="text" class="form_input" value="<?php echo $c_num_hasta;?>" size="7" maxlength="7" onKeyPress="return validar(event,2);"></td>
											</tr>
										</table>
									</td>
								</tr>
								<tr>
									<td colspan="2"><p align="center"><input type="submit" name="accion" value="Generar" class="form_button"></p></td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</form>
		</div>
	</body>
</html>
<?php pg_close();

