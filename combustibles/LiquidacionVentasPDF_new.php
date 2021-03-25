<?php
	include "/sistemaweb/valida_sess.php";
	include "/sistemaweb/functions.php";
	require "/sistemaweb/clases/funciones.php";	
	include "functions.php";
	include "/sistemaweb/include/reportes2.inc.php";

	$funcion = new class_funciones;
	$conector_id=$funcion->conectar("","","","","");


	$fecha_del	= $_REQUEST["anio"]."-".$_REQUEST["mes"]."-".$_REQUEST["desde"];
	$fecha_al	= $_REQUEST["anio"]."-".$_REQUEST["mes"]."-".$_REQUEST["hasta"];

	/* COMBUSTIBLES */

	$sql = "SELECT
			SUM(CASE WHEN ch_codigocombustible!='11620307' THEN (CASE WHEN nu_ventagalon!=0 THEN nu_ventavalor ELSE 0 END) ELSE 0 END) as liquido,
			SUM(CASE WHEN ch_codigocombustible!='11620307' THEN (CASE WHEN nu_ventagalon!=0 THEN nu_ventagalon ELSE 0 END) ELSE 0 END) as liquido_canti,
			SUM(CASE WHEN ch_codigocombustible='11620307' THEN (CASE WHEN nu_ventagalon!=0 THEN nu_ventavalor ELSE 0 END) ELSE 0 END) as glp,
			SUM(CASE WHEN ch_codigocombustible='11620307' THEN (CASE WHEN nu_ventagalon!=0 THEN nu_ventagalon ELSE 0 END) ELSE 0 END) as glp_canti
		FROM
			comb_ta_contometros
		WHERE
			ch_sucursal='" . pg_escape_string($_REQUEST['almacen']) . "' AND 
			dt_fechaparte BETWEEN '" . pg_escape_string($fecha_del) . "' AND '" . pg_escape_string($fecha_al) . "'
		";
	
	$x_venta_combustible = pg_query($conector_id, $sql);
	
	/* DIFERENCIA PRECIO */

	$sql = "SELECT
			SUM(d.nu_fac_valortotal) AS ventatienda,
			SUM(d.nu_fac_cantidad) AS cantienda
		FROM
			fac_ta_factura_cabecera f 
			LEFT JOIN int_clientes c on f.cli_codigo=c.cli_codigo 
			LEFT JOIN fac_ta_factura_detalle d ON (f.ch_fac_tipodocumento=d.ch_fac_tipodocumento and f.ch_fac_seriedocumento=d.ch_fac_seriedocumento
								and f.ch_fac_numerodocumento=d.ch_fac_numerodocumento and f.cli_codigo=d.cli_codigo)
		WHERE
			f.ch_fac_seriedocumento='" . pg_escape_string($_REQUEST['almacen']) . "' AND 
			f.ch_fac_tipodocumento='45' AND
			f.dt_fac_fecha BETWEEN '" . pg_escape_string($fecha_del) . "' AND '" . pg_escape_string($fecha_al) . "'
			AND c.cli_ndespacho_efectivo != 1 ";

	$x_diferencia_precio = pg_query($conector_id, $sql);

	/* DESCUENTOS */

	$anio = $_REQUEST["anio"];
	$mes  = $_REQUEST["mes"];

	$sql="
		SELECT
			SUM(t.importe) importe
		FROM 
			pos_trans" . $anio . $mes . " t 
		WHERE 
			t.es = '" . pg_escape_string($_REQUEST['almacen']) . "'
			AND t.dia BETWEEN '" . pg_escape_string($fecha_del) . "' AND '" . pg_escape_string($fecha_al) . "'
			AND t.tipo = 'C'
			AND t.grupo = 'D'
			AND t.tm='V';
	";

	$x_descuentos = pg_query($conector_id, $sql);

	/* CONSUMO PROPIO */

	$sql = "SELECT   
			VC.ch_cliente as codcliente,
			C.cli_ruc as ruc,
			C.cli_razsocial	as cliente,
			round(sum(VD.galones),2) cantidad,
			sum(VC.nu_importe) as importe 
		FROM
			val_ta_cabecera VC 
			inner join (SELECT ch_sucursal,dt_fecha,ch_documento,sum(nu_cantidad) as galones FROM val_ta_detalle VTD GROUP BY ch_sucursal,dt_fecha,ch_documento) VD 
			on VC.ch_sucursal = VD.ch_sucursal and VC.dt_fecha = VD.dt_fecha and VC.ch_documento = VD.ch_documento 
	 		inner join (SELECT cli_codigo,cli_ruc,cli_razsocial FROM int_clientes WHERE cli_ndespacho_efectivo != 1) C 
			on VC.ch_cliente = C.cli_codigo 
		WHERE		
			VC.ch_sucursal='" . pg_escape_string($_REQUEST['almacen']) . "' 
			AND VC.dt_fecha BETWEEN '".pg_escape_string($fecha_del)."' AND '".pg_escape_string($fecha_al)."'

			AND VC.ch_estado='1'
		GROUP BY	
			VC.ch_cliente,
			C.cli_ruc,
			C.cli_razsocial 
		ORDER BY 	
			VC.ch_cliente";

	$x_consumo_propio = pg_query($conector_id, $sql);

	/* AFERICIONES */
	
	$sql="
			SELECT
				sum(af.importe) importe
			FROM 
				pos_ta_afericiones af
			WHERE
				af.es = '" . pg_escape_string($_REQUEST['almacen']) . "'
				AND af.dia BETWEEN '" . pg_escape_string($fecha_del) . "' AND '" . pg_escape_string($fecha_al) . "';
			";
	
	$x_afericiones = pg_query($conector_id, $sql);

	/* OTROS PRODUCTOS Y PROMOCIONES */

	$sql = "
			SELECT
				SUM(d.nu_fac_valortotal) AS ventatienda,
				SUM(d.nu_fac_cantidad) AS cantienda
			FROM
				fac_ta_factura_cabecera f 
				LEFT JOIN int_clientes c ON (f.cli_codigo = c.cli_codigo)
				LEFT JOIN fac_ta_factura_detalle d ON (f.ch_fac_tipodocumento = d.ch_fac_tipodocumento AND f.ch_fac_seriedocumento = d.ch_fac_seriedocumento AND f.ch_fac_numerodocumento = d.ch_fac_numerodocumento AND f.cli_codigo=d.cli_codigo)
			WHERE
				f.ch_fac_seriedocumento = '" . pg_escape_string($_REQUEST['almacen']) . "'
				AND f.ch_fac_tipodocumento = '45'
				AND f.dt_fac_fecha BETWEEN '" . pg_escape_string($fecha_del) . "' AND '" . pg_escape_string($fecha_al) . "'
				AND c.cli_ndespacho_efectivo != 1;
			";
	
	$x_ventas_productos_promo = pg_query($conector_id, $sql);

	/* VALES DE CREDITO */

	$sql="
			SELECT
				c.ch_cliente AS  codcliente,
		                cl.cli_ruc AS ruc,
                		cl.cli_razsocial AS cliente,
		                SUM(d.nu_cantidad)  AS cantidad,
        		        SUM(c.nu_importe) AS importe
        	        FROM
				val_ta_cabecera c
				JOIN val_ta_detalle d ON (c.ch_sucursal = d.ch_sucursal AND c.dt_fecha = d.dt_fecha AND c.ch_documento = d.ch_documento)
				JOIN int_clientes cl ON (c.ch_cliente = cl.cli_codigo)
	                WHERE
				c.dt_fecha BETWEEN '".pg_escape_string($fecha_del)."' AND '".pg_escape_string($fecha_al)."'
		                AND c.ch_sucursal = '".pg_escape_string($_REQUEST['almacen'])."'
		                AND c.ch_estado = '1'
                		AND cl.cli_ndespacho_efectivo != 1
			GROUP BY
		                c.ch_cliente,
                		cl.cli_ruc,
		                cl.cli_razsocial;
		";

	$x_vales_credito = pg_query($conector_id, $sql);

	/* TARJETAS DE CREDITO */

	$anio = $_REQUEST["anio"];
	$mes  = $_REQUEST["mes"];

	$sql="
		SELECT 
			g.tab_descripcion as descripciontarjeta,
			SUM(t.importe)-SUM(COALESCE(t.km,0)) as importe
		FROM
			pos_trans" . $anio . $mes . " t
			JOIN int_tabla_general g ON (g.tab_tabla='95' AND g.tab_elemento='00000'||t.at)
			LEFT JOIN int_clientes c on c.cli_ruc = t.ruc AND c.cli_ndespacho_efectivo != 1
		WHERE
			t.es = '".pg_escape_string($_REQUEST['almacen'])."'
			AND t.fpago = '2'
		AND t.dia BETWEEN '".pg_escape_string($fecha_del)."' AND '".pg_escape_string($fecha_al)."'
		GROUP BY
			1
		ORDER BY
			descripciontarjeta;
	";

	$x_tarjetas_credito = pg_query($conector_id, $sql);

	/* DEPOSITOS POS */	
	
	$sql="
		SELECT 
			SUM(
				CASE 
					WHEN ch_moneda='01'THEN nu_importe 
					WHEN ch_moneda='02'THEN nu_importe * nu_tipo_cambio
				END) AS importe
		FROM 
			pos_depositos_diarios
		WHERE 
			ch_almacen = '".pg_escape_string($_REQUEST['almacen'])."'
			AND (ch_valida = 'S' OR ch_valida = 's')
			AND dt_dia BETWEEN '".pg_escape_string($fecha_del)."' AND '".pg_escape_string($fecha_al)."';
	";

	$x_depositos_pos = pg_query($conector_id, $sql);
	
	/* SOBRANTES Y FALTANTES */	

	$sql="
		SELECT
			ROUND(SUM(importe),2) AS importe
		FROM
			comb_diferencia_trabajador
		WHERE
			dia BETWEEN '".pg_escape_string($fecha_del)."' AND '".pg_escape_string($fecha_al)."'
			AND flag = '0';
	";

	$x_sobrantes_faltantes = pg_query($conector_id, $sql);
	
	/* DEPOSITOS BANCARIOS */	

	$sql = "SELECT 
			cab.ch_fac_tipodocumento||' - '||cab.ch_fac_seriedocumento||' - '||cab.ch_fac_numerodocumento||' - '||cab.cli_codigo||' '||cli.cli_rsocialbreve AS documento, 
			cab.nu_fac_valortotal AS importe
		FROM
			fac_ta_factura_cabecera cab 
			LEFT JOIN int_clientes cli ON (cli.cli_codigo=cab.cli_codigo) 
		WHERE 
			cab.ch_fac_tipodocumento IN ('10','35') AND 
			cab.ch_almacen='" . pg_escape_string($_REQUEST['almacen']) . "' AND 
			cab.dt_fac_fecha BETWEEN '".pg_escape_string($fecha_del)."' AND '".pg_escape_string($fecha_al)."'
		ORDER BY 
			cab.ch_fac_tipodocumento, cab.ch_fac_seriedocumento, cab.ch_fac_numerodocumento, cab.cli_codigo";

	$x_depositos_bancarios = pg_query($conector_id, $sql);
		
	/* SOBRANTES Y FALTANTES MANUALES */	

	$sql="
		SELECT
			ROUND(SUM(importe),2) AS importe
		FROM
			comb_diferencia_trabajador
		WHERE
			dia BETWEEN '".pg_escape_string($fecha_del)."' AND '".pg_escape_string($fecha_al)."'
			AND flag = '1'
	";
	
	$x_sobrantes_faltantes_manuales = pg_query($conector_id, $sql);

	/***********************************************************************************************************************************/
	/*******************************VARIABLES*****************************************************************************************/
	/***********************************************************************************************************************************/

	$venta_combustible 		= pg_fetch_all($x_venta_combustible);
	$TVCombustible 			= $venta_combustible[0]['liquido'] + $venta_combustible[0]['glp']; //TVCombustible: Total Venta Combustible
	$TVCombustible_canti 		= $venta_combustible[0]['liquido_canti'] + $venta_combustible[0]['glp_canti']; //TVCombustible: Total Venta Combustible CANTIDAD
	$total_venta_combustible	= number_format($TVCombustible, 2);
	$total_canti_combustible	= number_format($TVCombustible_canti, 2);

	$descuentos	 		= pg_fetch_all($x_descuentos);

	$afericiones	 		= pg_fetch_all($x_afericiones);

	$venta_productos_promo 		= pg_fetch_all($x_ventas_productos_promo);

	$vales_credito	 		= pg_fetch_all($x_vales_credito);

	$tarjetas_credito	 	= pg_fetch_all($x_tarjetas_credito);

	$depositos_pos	 		= pg_fetch_all($x_depositos_pos);

	$sobrantes_faltantes 		= pg_fetch_all($x_sobrantes_faltantes);

	$depositos_bancarios 		= pg_fetch_all($x_depositos_bancarios);

	$sobrantes_faltantes_manuales	= pg_fetch_all($x_sobrantes_faltantes_manuales);

	/***********************************************************************************************************************************/
	/*******************************REPORTE PDF*****************************************************************************************/
	/***********************************************************************************************************************************/

	// NOMBRE ALMACEN

	$alm = "
		SELECT
			ch_almacen as codalma,
			TRIM(ch_nombre_almacen) as nomalma
		FROM
			inv_ta_almacenes
		WHERE
			ch_clase_almacen = '1'
			AND ch_almacen = '".pg_escape_string($_REQUEST['almacen'])."'
	";

	$x_alm = pg_query($conector_id, $alm);
	$almas = pg_fetch_all($x_alm);
	$nombre_almacen = $almas[0]['codalma']." - ".$almas[0]['nomalma'];

	$cab_def = Array(
		"field"		=>	"               DESCRIPCION",
		"quantity"	=>	"CANTIDAD",
		"value"		=>	"MONTO (S/.)"
	);

	$cab2 = Array(
		"producto"	=>	"PRODUCTO",
		"stk_ini"	=>	"STK. INI.",
		"compras"	=>	"COMPRAS",
		"ventas"	=>	"VENTAS",
		"porcentaje"	=>	" % ",
		"stk_fin"	=>	"STK. FIN",
		"medicion"	=>	"MEDICION",
		"dif_dia"	=>	"DIF.DIA",
		"dif_mes"	=>	"DIF.MES",
		"importe"	=>	"IMP. VENTA"
	);

	$desde	=	$_REQUEST['desde'];
	$hasta	=	$_REQUEST['hasta'];
	$mes	=	$_REQUEST['mes'];
	$anio 	=	$_REQUEST['anio'];

	$reporte = new CReportes2("P","pt","A4");

	$reporte->Ln();	
	$reporte->Ln();	 
	
	$reporte->definirCabecera(2, "L", " ");
	//$reporte->definirCabecera(2, "L", "Pagina %p                                                                            Fecha:".date("d/m/Y H:i"));
	$reporte->definirCabecera(2, "L", "Pagina %p                                                                          ");
	$reporte->definirCabecera(3, "L", "                              LIQUIDACION DE VENTA TOTAL DEL $desde/$mes/$anio AL $hasta/$mes/$anio" );
	$reporte->definirCabecera(4, "L", "");
	$reporte->definirCabecera(5, "L", $nombre_almacen);
	$reporte->definirCabecera(6, "L", "Fecha:".date("d/m/Y H:i"));
	$reporte->definirCabecera(7, "L", " ");

	$reporte->SetMargins(10,10,10);
	$reporte->SetFont("courier", "", 9);

	$reporte->definirColumna("field",$reporte->TIPO_TEXTO,65,"L");
	$reporte->definirColumna("quantity",$reporte->TIPO_TEXTO,15,"R");//212.21
	$reporte->definirColumna("value",$reporte->TIPO_TEXTO,15,"R");

	$reporte->definirColumna("producto",$reporte->TIPO_TEXTO,13,"L", "_inv");
	$reporte->definirColumna("stk_ini",$reporte->TIPO_IMPORTE,13,"R", "_inv");
	$reporte->definirColumna("compras",$reporte->TIPO_IMPORTE,13,"R", "_inv");
	$reporte->definirColumna("ventas",$reporte->TIPO_IMPORTE,13,"R", "_inv");
	$reporte->definirColumna("porcentaje",$reporte->TIPO_IMPORTE,8,"R", "_inv");
	$reporte->definirColumna("stk_fin",$reporte->TIPO_IMPORTE,13,"R", "_inv");
	$reporte->definirColumna("medicion",$reporte->TIPO_IMPORTE,13,"R", "_inv");
	$reporte->definirColumna("dif_dia",$reporte->TIPO_IMPORTE,8,"R", "_inv");
	$reporte->definirColumna("dif_mes",$reporte->TIPO_IMPORTE,8,"R", "_inv");
	$reporte->definirColumna("importe",$reporte->TIPO_IMPORTE,13,"R", "_inv");

	$reporte->definirCabeceraPredeterminada($cab_def);
	$reporte->AddPage();

	$reporte->Ln();	

	$reporte->nuevaFila(array("field"=>"     I. VENTA","value"=>' '));

	$reporte->nuevaFila(array("field"=>"     1. Venta de Combustible","value"=>' '));
	$reporte->Ln();	

	$reporte->nuevaFila(array("field"=>"        1.1 Liquido", "quantity"=>number_format($venta_combustible[0]["liquido_canti"],2,'.',','),"value"=>number_format($venta_combustible[0]["liquido"],2,'.',',')		));
	$reporte->Ln();	

	$reporte->nuevaFila(array("field"=>"        1.2 GLP","quantity"=>number_format($venta_combustible[0]["glp_canti"],2,'.',','), "value"=>number_format($venta_combustible[0]["glp"],2,'.',',')			));
	$reporte->Ln();	
	
	$reporte->nuevaFila(array("field"=>"                TOTAL VENTA BRUTA DE COMBUSTIBLE","quantity"=>$total_canti_combustible,"value"=>$total_venta_combustible));
	$reporte->Ln();	
	$reporte->lineaH();

	$reporte->nuevaFila(array("field"=>"     2. Incrementos / Descuentos ","value"=>' '));
	$reporte->Ln();	

	$reporte->nuevaFila(array("field"=>"        2.1 Diferencia Precios Vales", "quantity"=>"","value"=>$liquido		));
	$reporte->Ln();	

	$reporte->nuevaFila(array("field"=>"        2.2 Descuentos","quantity"=>"", "value"=>number_format($descuentos[0]['importe'],2,'.',',')			));
	$reporte->Ln();	
	
	$reporte->nuevaFila(array("field"=>"        2.3 Consumo Propio", "quantity"=>"","value"=>$liquido		));
	$reporte->Ln();

	$reporte->nuevaFila(array("field"=>"        2.4 Afericiones","quantity"=>"", "value"=>number_format($afericiones[0]['importe'],2,'.',',')		));
	$reporte->Ln();

	$reporte->nuevaFila(array("field"=>"                TOTAL VENTA NETA DE COMBUSTIBLE","quantity"=>$total_canti_combustible,"value"=>$total_venta_combustible));
	$reporte->Ln();	
	$reporte->lineaH();

	$reporte->nuevaFila(array("field"=>"     3. Venta de Otros productos y promociones","value"=>number_format($venta_productos_promo),2,'.',','));
	$reporte->Ln();	

	$reporte->nuevaFila(array("field"=>"                TOTAL VENTA NETA A LIQUIDAR", "quantity"=>"","value"=>$liquido		));
	$reporte->Ln();

	$reporte->nuevaFila(array("field"=>"  II. RUBROS DE LIQUIDACION","value"=>""		));

	$reporte->nuevaFila(array("field"=>"     1. Vales de Credito","value"=>""		));
	$reporte->Ln();	

 	$val_can = 0; 
	$val_imp = 0;

	foreach($vales_credito as $val) {
		$reporte->nuevaFila(array("field"=>"        ".$val['codcliente']."  ".$val['ruc']."  ".$val['cliente'],"quantity"=>number_format($val['cantidad'],2,'.',','),"value"=>number_format($val['importe'],2,'.',',')));
		$val_can = $val_can + $val['cantidad']; 
		$val_imp = $val_imp + $val['importe'];	
	}

	$reporte->Ln();		
	$reporte->nuevaFila(array("field"=>"                TOTAL VALES DE CREDITO","quantity"=>number_format($val_can,2,'.',','),"value"=>number_format($val_imp,2,'.',',')		));
	$reporte->Ln();	
	$reporte->lineaH();

	$reporte->nuevaFila(array("field"=>"     2. Tarjetas de Credito","value"=>' '		));
	$reporte->Ln();	

	foreach($tarjetas_credito as $t){
		$reporte->nuevaFila(array("field"=>"        ".$t['descripciontarjeta'],"value"=>number_format($t['importe'],2,'.',',')		));
		$reporte->Ln();
		$tot_imp = $tot_imp + $t['importe'];
	}

	$reporte->nuevaFila(array("field"=>"                TOTAL TARJETAS DE CREDITO","value"=>number_format($tot_imp,2,'.',',')		));
	$reporte->Ln();	
	$reporte->lineaH();

	$reporte->nuevaFila(array("field"=>"     3. Total Venta Contado","value"=>' '));
	$reporte->Ln();	

	$reporte->nuevaFila(array("field"=>"        Total Depositos POS", "quantity"=>"","value"=>number_format($depositos_pos[0]["importe"],2,'.',',')		));
	$reporte->Ln();	

	$reporte->nuevaFila(array("field"=>"        Faltante / Sobrante de Grifero","quantity"=>"", "value"=>number_format($sobrantes_faltantes[0]["importe"],2,'.',',')			));
	$reporte->Ln();

	$reporte->nuevaFila(array("field"=>"  III. CONCILACION DEPOSITOS POS","value"=>""		));
	$reporte->nuevaFila(array("field"=>"        Total Depositos POS", "quantity"=>"","value"=>number_format($depositos_pos[0]["importe"],2,'.',',')		));
	$reporte->Ln();

	$reporte->nuevaFila(array("field"=>"        Anomalia (Hermes)","quantity"=>"", "value"=>number_format($sobrantes_faltantes_manuales[0]["importe"],2,'.',',')		));
	$reporte->Ln();

	$reporte->Ln();
	$reporte->lineaH();

	$reporte->borrarCabecera();
	$reporte->borrarCabeceraPredeterminada();
	$reporte->Lnew();$reporte->Lnew();

	$reporte->Output("reporteLiquidacionVentasDiariasNew.pdf","I");
