<?php
include("../../valida_sess.php");
include("../../menu_princ.php");
//session_start();
include("../store_procedures.php");
include("../../clases/funciones.php");
include("impresion-utils.php");
include("/sistemaweb/cpagar/funciones.php");
include("/sistemaweb/func_print_now.php");
include("fac_pdf_impresiones.php");
include('../include/reportes2.inc.php');

$funcion = new class_funciones;
// crea la clase para controlar errores
$clase_error = new OpensoftError;
$clase_error->_error();
// conectar con la base de datos
$conector_id=$funcion->conectar("","","","","");

$tipo_documento = '20';
$c_serie="001";
$c_num_desde="0006074";
$c_num_hasta="0006076";
$arrayCFact=array();
$listado_cabecera=array();
$listado_detalle=array();
$x=1;
$y=1;

/*selecc que obtiene los numeros de las notas de credito, */
	
$query = "SELECT
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
		ORDER BY  fa.ch_fac_numerodocumento;";
		
		$rs_facturas = pg_exec($query);	
	
		
		while($row=pg_fetch_array($rs_facturas)){
			$num_docu   	= $row["ch_fac_numerodocumento"];
      		$tipo_docu  	= $row["ch_fac_tipodocumento"];
      		$serie_docu 	= $row["ch_fac_seriedocumento"];
      		$cli_docu   	= $row["cli_codigo"];
			
/*$listado_cabecera[$x]['ch_fac_seriedocumento']=$serie_docu;
			$listado_cabecera[$x]['ch_fac_numerodocumento']=$num_docu;
			$x++;*/
			
			
			$query = "SELECT ".
						"nd.ch_fac_tipodocumento, ".
						"nd.ch_fac_seriedocumento, ".
						"nd.ch_fac_numerodocumento, ".
						"nd.ch_nro_factura, ".
						"nd.ch_nombre_item, ".
						"nd.nu_cantidad, ".
						"nd.nu_precio_contratado, ".
						"nd.nu_monto_contratado,".
						"nd.nu_monto_factura, ".
						"nd.nu_diferencia ".
					"FROM ".
						  "fac_ta_ncredcomp nd, ".
						  "fac_ta_factura_cabecera fc ".
					"WHERE fc.ch_fac_tipodocumento = nd.ch_fac_tipodocumento ".
					"AND fc.ch_fac_seriedocumento= nd.ch_fac_seriedocumento ".
					"AND fc.ch_fac_numerodocumento= nd.ch_fac_numerodocumento ".
					"AND fc.ch_fac_numerodocumento = '".$num_docu."' ".
					"AND fc.ch_fac_tipodocumento = '".$tipo_docu."' ".
					"AND fc.ch_fac_seriedocumento = '".$serie_docu."' ";
					$rs_detalle = pg_exec($query);	
		//echo($rs_facturas);
		/*impresion codigo factura y serie*/
					echo('Documento: '.$num_docu.' tipo: '.$tipo_docu);
					while($row=pg_fetch_array($rs_detalle)){
						$listado_detalle[$y]['ch_nombre_item']=$row['ch_nombre_item'];
						$listado_detalle[$y]['nu_cantidad']=$row['nu_cantidad'];
						
					}
			
			
			
		}







    $reporte->Output("/sistemaweb/ventas_clientes/reportes/pdf/diferencia_precios_ventas.pdf", "F");
    echo '<script> window.open("/sistemaweb/ventas_clientes/reportes/pdf/diferencia_precios_ventas.pdf","miwin","width=750,height=550,scrollbars=yes, resizable=yes, menubar=no");</script>';		
		
		
