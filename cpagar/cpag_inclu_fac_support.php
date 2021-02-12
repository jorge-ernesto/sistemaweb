<?php
include("../include/functions.php");

$sqlca = new pgsqlDB('localhost','postgres', 'postgres', 'integrado');
require("../clases/funciones.php");


function ObtNumeroReg($periodo)
{
global $sqlca;
    $query = "SELECT nu_registro_compra 
              FROM int_control_x_periodos 
              WHERE ch_periodo = '".$periodo."'";
    $number = $sqlca->firstRow($query);
    
    if($number == '')
    {
        $number = 1;
    }
    else
    {
        $number = $number + 1;
    }
    return $number;
}

function incluirFacturas1($AR_ORD,$AR_DEV,$pcod_documento,$pserie_doc,$pnum_documento)
{
global $sqlca;

$funcion = new class_funciones;
//$conector_repli_id = $funcion->conectar("","","acosa_backups","","");
//print_r($Datos);


    /*Los num serie y codigo de os documentos son los que pasan como parametros*/
    /*Primero con las ordenes de compra*/
    for($i=0;$i<count($AR_ORD);$i++)
		{
		
		$A = $AR_ORD[$i];
		$pro_codigo		=	$A["cod_proveedor"];
		$num_tipdocumento	=	$A["com_tipo_compra"];
		$num_seriedocumento	=	$A["com_serie_compra"];
		$com_cab_numorden	=	$A["com_num_compra"];
		$com_cab_almacen	=	$A["ord_almacen"];
		$art_codigo		=	$A["art_codigo"];
		$tran_codigo		=	$A["tran_codigo"];
		$mov_numero		=	$A["ord_compra"];
		$mov_fecha		=	$A["mov_fecha"];
		/*NO ACTUALIZABA COSTO UNITRARIO 11/12/2004*/
		$art_costo_uni		=	$A["art_costo_uni"]; 
                $art_costo_total        =       $A["art_costo_total"]; 
//		$Datos['Ip_Estacion']  = ObtenerIPAlmacen($conector_repli_id, trim($com_cab_almacen));
//		$Datos['Cod_Estacion'] = $com_cab_almacen;


		
		$q1 = "UPDATE com_cabecera ".
			"SET com_cab_estado='3' ".
			"WHERE pro_codigo = '$pro_codigo' ".
			"AND num_tipdocumento = trim('$num_tipdocumento') ".
			"AND num_seriedocumento = trim('$num_seriedocumento') ".
			"AND com_cab_numorden = '$com_cab_numorden' ".
			"AND com_cab_almacen = '$com_cab_almacen'";
		
		
		$q2 = "UPDATE com_detalle ".
			"SET com_det_estado='3' ".
			"WHERE pro_codigo = '$pro_codigo' ".
			"AND num_tipdocumento = '$num_tipdocumento' ".
			"AND num_seriedocumento = '$num_seriedocumento' ".
			"AND com_cab_numorden = '$com_cab_numorden' ".
			"AND art_codigo = '$art_codigo'";
			
		/*update en compras devoluciones con el array de ordenes de compra*/
		$q3 = "UPDATE inv_ta_compras_devoluciones ".
			"SET com_det_estado='3', ".
			"cpag_tipo_pago 	= substring('$pcod_documento' ".
			"FROM char_length('$pcod_documento')-1 for 2 ), ".
				"cpag_serie_pago	= '$pserie_doc', ".
				"cpag_num_pago 	=  '$pnum_documento', ".
				"mov_fecha_actualizacion	=	now(), ".
				"ip_addr = '".$_SERVER['REMOTE_ADDR']."', ".
				"mov_costounitario =  $art_costo_uni, ".
				"mov_costototal = $art_costo_total ".
			"WHERE tran_codigo = '$tran_codigo' ".
			"AND mov_almacen = '$com_cab_almacen' ".
			"AND com_num_compra = '$mov_numero' ".
			"AND to_char(mov_fecha,'dd/mm/yyyy') = '$mov_fecha' ".
			"AND art_codigo = '$art_codigo'";
	
		//echo $q3;//$pcod_documento,$pserie_doc,$pnum_documento
		//exec("echo $q3 > /tmp/update.txt");
		echo "\n";
		echo "<!--QUERY : $q3 -->\n";
		$sqlca->query($q1);
		$sqlca->query($q2);
		$sqlca->query($q3);
		
//        $SentenciasReplicacion = SentenciasReplicacion($conector_repli_id, $q1, $Datos);
//        $SentenciasReplicacion = SentenciasReplicacion($conector_repli_id, $q2, $Datos);
//        $SentenciasReplicacion = SentenciasReplicacion($conector_repli_id, $q3, $Datos);
		
		
		/*pg_exec($q1);
		pg_exec($q2);
		pg_exec($q3);*/
		}
	
	/*Ahora con las devoluciones*/
	
    for($i=0;$i<count($AR_DEV);$i++)
		{
		
		$A = $AR_DEV[$i];
		$mov_almacen	=	$A["ord_almacen"];
		$mov_numero	=	$A["ord_compra"]; //para las devoluciones no hay orden de compra sino que hay numero de mov
		$mov_fecha	=	$A["mov_fecha"];
		$art_codigo	=	$A["art_codigo"];
		$tran_codigo	=	$A["tran_codigo"];
		$art_costo_uni	=	$A["art_costo_uni"];

//		$Datos['Ip_Estacion']  = ObtenerIPAlmacen($conector_repli_id, trim($com_cab_almacen));
//		$Datos['Cod_Estacion'] = $com_cab_almacen;
	
		
		$q4 = "UPDATE inv_ta_compras_devoluciones ".
			  "SET com_det_estado = '3', ".
				"cpag_tipo_pago = substring('$pcod_documento' FOR 2 FROM length('$pcod_documento')-1), ".
				"cpag_serie_pago = '$pserie_doc', ".
				"cpag_num_pago = '$pnum_documento', ".
				"mov_fecha_actualizacion = now(), ".
				"ip_addr = '".$_SERVER['REMOTE_ADDR']."', ".
				"mov_costounitario = $art_costo_uni ".
			  "WHERE tran_codigo = '$tran_codigo' ".
			  "AND mov_almacen = '$mov_almacen' ".
			  "AND mov_numero = '$mov_numero' ".
			  "AND to_char(mov_fecha,'dd/mm/yyyy') = '$mov_fecha' ".
			  "AND art_codigo = '$art_codigo'";
		//echo $q4;	
		$sqlca->query($q4);
//		$SentenciasReplicacion = SentenciasReplicacion($conector_repli_id, $q4, $Datos);
		//pg_exec($q4);
		} 

}

/*Actualizamos en los cpag detalle y cabecera*/
function incluirFacturasEdicion($AR_ORD)
{	
global $sqlca;
$funcion = new class_funciones;
//$conector_repli_id = $funcion->conectar("","","acosa_backups","","");
echo "<!--";
//print_r($_REQUEST);
echo "-->";
/*-------Inicializando Variables destinadas al Insert----------*/
    $fec_docu       = $_REQUEST['fec_docu'];
    $fec_reg        = $_REQUEST['fec_reg'];
    $num_registro   = $_REQUEST['num_registro'];
    $cod_proveedor  = $_REQUEST['cod_proveedor'];
    $desc_proveedor = $_REQUEST['desc_proveedor'];
    $cod_rubro      = $_REQUEST['cod_rubro'];
    $desc_rubro     = trim($_REQUEST['desc_rubro']);
    $cod_documento  = $_REQUEST['cod_documento'];
    $des_documento  = $_REQUEST['des_documento'];
    $serie_doc      = $_REQUEST['serie_doc'];
    $num_documento  = $_REQUEST['num_documento'];
    $cod_docref     = $_REQUEST['cod_docref'];
    $des_docref     = $_REQUEST['des_docref'];
    $num_docurefe   = $_REQUEST['num_docurefe'];
    $fecha_ven      = $_REQUEST['fecha_ven'];
    $cod_unidad     = $_REQUEST['cod_unidad'];
    $des_unidad     = $_REQUEST['des_unidad'];
    $cod_moneda     = $_REQUEST['cod_moneda'];
    $tasa_cambio    = $_REQUEST['tasa_cambio'];
    $cal            = $_REQUEST['cal'];
    $monto_imp      = $_REQUEST['monto_imp'];
    $impuesto1      = $_REQUEST['impuesto1'];
    $monto_imp1     = $_REQUEST['monto_imp1'];
    $impuesto2      = $_REQUEST['impuesto2'];
    $monto_imp2     = $_REQUEST['monto_imp2'];
    $impuesto3      = $_REQUEST['impuesto3'];
    $monto_imp3     = $_REQUEST['monto_imp3'];
    $importe_total  = $_REQUEST['importe_final'];
    $grupo          = $_REQUEST['grupo'];
    $voucher        = $_REQUEST['voucher'];
    $emisor         = $_REQUEST['emisor'];
    $glosa          = $_REQUEST['glosa'];
    $c_montos_varios = $_REQUEST['c_montos_varios'];
/*---------------------------------------------------------------------*/

    if(isset($_REQUEST['percepcion']) && !empty($_REQUEST['percepcion']))
    {
	$impot         = $_REQUEST['percepcion'];
	$porcen_apli   = $_REQUEST['mnt_apli_percepcion'];
	$regc_sunat_percepcion = "'{".$porcen_apli.",".$impot.",0,0,00/00/0000}'";
    }else{
	$regc_sunat_percepcion = 'NULL';
    }

    if(isset($_REQUEST['detraccion']) && !empty($_REQUEST['detraccion']))
    {
	$impot         = $_REQUEST['detraccion'];
	$porcen_apli   = $_REQUEST['mnt_apli_detraccion'];
	$regc_sunat_detraccion = "'{".$porcen_apli.",".$impot.",0,0,00/00/0000}'";
    }else{
	$regc_sunat_detraccion = 'NULL';
    }

    if(isset($_REQUEST['retencion']) && !empty($_REQUEST['retencion']))
    {
        $importe_total  = $_REQUEST['importe_total'];
        
	$impot         = $_REQUEST['retencion'];
	$porcen_apli   = $_REQUEST['mnt_apli_retencion'];
	$regc_sunat_retencion = "'{".$porcen_apli.",".$impot.",0,0,00/00/0000}'";
    }else{
	$regc_sunat_retencion = 'NULL';
    }


	foreach($_REQUEST as $llave => $valor)
	{
		//echo "<!--LLAVE : $llave => VALOR : $valor -->\n";
	}
    
	$plc_codigo = "42101"; //CAMBIAR pro_det_identidad tambien lo estoy poniendo 
    
    if($cod_moneda=="02" || $cod_moneda==2){
	$plc_codigo = "42102";
    }
    
    $O = $AR_ORD[0];
    $num_orden_compra = $O["com_num_compra"];
    if(trim($c_montos_varios)==""){ $c_montos_varios=0; }
    
    $q1 = "UPDATE cpag_ta_cabecera SET ".
		 "pro_cab_tipdocumento = substring('$cod_documento' FROM char_length('$cod_documento')-1 for 2 ), ".
		 "pro_cab_seriedocumento = '$serie_doc', ".
		 "pro_cab_numdocumento = '$num_documento', ".
		 "pro_codigo = '$cod_proveedor', ".
		 "pro_cab_fechaemision = to_date('$fec_docu','dd/mm/yyyy'), ".
		 "pro_cab_fecharegistro = to_date('$fec_reg','dd/mm/yyyy'), ".
		 "pro_cab_fechavencimiento = to_date('$fecha_ven','dd/mm/yyyy'), ".
		 "pro_cab_dias_vencimiento = NULL, ".
		 "pro_cab_tipcontable = util_fn_tipo_accion_contable('CP','$cod_documento'), ".
		 "plc_codigo = '$plc_codigo', ".
		 "pro_cab_moneda = '$cod_moneda', ".
		 "pro_cab_tcambio = $tasa_cambio, ".
		 "pro_cab_imptotal = $importe_total, ".
		 "pro_cab_impsaldo = $importe_total, ".
		 "pro_cab_fechasaldo = now(), ".
		 "pro_cab_grupoc = NULL, ".
		 "pro_cab_comprobantec = NULL, ".
		 "pro_cab_tipdocreferencia = substring('$cod_docref' from char_length('$cod_docref')-1 for 2), ".
		 "pro_cab_numdocreferencia = '$num_docurefe', ".
		 "pro_cab_almacen = '$cod_unidad', ".
		 "pro_cab_glosa = '$glosa', ".
		 "pro_cab_impafecto = $monto_imp, ".
		 "pro_cab_tipimpto1 = '$impuesto1', ".
		 "pro_cab_tipimpto2 = '$impuesto2', ".
		 "pro_cab_tipimpto3 = '$impuesto3', ".
		 "pro_cab_impto1 = $monto_imp1, ".
		 "pro_cab_impto2 = $monto_imp2, ".
		 "pro_cab_impto3 = $monto_imp3, ".
		 "pro_cab_catdocumento = NULL, ".
		 "pro_cab_rubrodoc = '$cod_rubro', ".
		 "com_cab_numorden = '$num_orden_compra', ".
		 "pro_cab_numreg = $num_registro, ".
		 "pro_cab_impinafecto = $c_montos_varios, ".
		 "regc_sunat_percepcion = $regc_sunat_percepcion, ".
		 "regc_sunat_detraccion = $regc_sunat_detraccion, ".
		 "regc_sunat_retencion = $regc_sunat_retencion  ".
        "WHERE pro_cab_tipdocumento||pro_cab_seriedocumento||pro_cab_numdocumento||pro_codigo = '".$_REQUEST['regid']."' ";
			
    $q2 = "UPDATE  cpag_ta_detalle SET ".
                      "pro_cab_tipdocumento = substring('$cod_documento' FROM char_length('$cod_documento')-1 FOR 2 ), ".
                      "pro_cab_seriedocumento = '$serie_doc', ".
                      "pro_cab_numdocumento = '$num_documento', ".
                      "pro_codigo = '$cod_proveedor', ".
                      "pro_det_identidad = '001', ".
                      "pro_det_tipmovimiento = '1', ".
                      "pro_det_fechamovimiento = to_date('$fec_reg','dd/mm/yyyy'), ".
                      "pro_det_moneda = '$cod_moneda', ".
                      "pro_det_tcambio = $tasa_cambio, ".
                      "pro_det_impmovimiento = $importe_total, ".
                      "pro_det_grupoc = NULL, ".
                      "pro_det_comprobantec = NULL, ".
                      "pro_det_tipdocreferencia = substring('$cod_docref' FROM char_length('$cod_docref')-1 for 2), ".
                      "pro_det_numdocreferencia = '$num_docurefe', ".
                      "pro_det_almacen = '$cod_unidad', ".
                      "pro_det_glosa = '$glosa' ".
	   "WHERE pro_cab_tipdocumento||pro_cab_seriedocumento||pro_cab_numdocumento||pro_codigo = '".$_REQUEST['regid']."' ";
echo "retorno : " .     $sqlca->query($q1) . "\n";
echo "retorno : " .    $sqlca->query($q2) . "\n";
   
   echo "<!--QUERY 1 : $q1 -->";
   echo "<!--QUERY 2 : $q2 -->";
   
   // pg_exec($q1);
    //pg_exec($q2);
		
		
} 



/*Insertamos en los cpag detalle y cabecera*/
function incluirFacturas2($AR_ORD)
{	
global $sqlca;
$funcion = new class_funciones;
//$conector_repli_id = $funcion->conectar("","","acosa_backups","","");

//var_dump($_REQUEST);
/*-------Inicializando Variables destinadas al Insert----------*/
    $fec_docu       = $_REQUEST['fec_docu'];
    $fec_reg        = $_REQUEST['fec_reg'];
    $num_registro   = $_REQUEST['num_registro'];
    $cod_proveedor  = $_REQUEST['cod_proveedor'];
    $desc_proveedor = $_REQUEST['desc_proveedor'];
    $cod_rubro      = $_REQUEST['cod_rubro'];
    $desc_rubro     = trim($_REQUEST['desc_rubro']);
    $cod_documento  = $_REQUEST['cod_documento'];
    $des_documento  = $_REQUEST['des_documento'];
    $serie_doc      = $_REQUEST['serie_doc'];
    $num_documento  = $_REQUEST['num_documento'];
    $cod_docref     = $_REQUEST['cod_docref'];
    $des_docref     = $_REQUEST['des_docref'];
    $num_docurefe   = $_REQUEST['num_docurefe'];
    $fecha_ven      = $_REQUEST['fecha_ven'];
    $cod_unidad     = $_REQUEST['cod_unidad'];
    $des_unidad     = $_REQUEST['des_unidad'];
    $cod_moneda     = $_REQUEST['cod_moneda'];
    $tasa_cambio    = $_REQUEST['tasa_cambio'];
    $cal            = $_REQUEST['cal'];
    $monto_imp      = $_REQUEST['monto_imp'];
    $impuesto1      = $_REQUEST['impuesto1'];
    $monto_imp1     = $_REQUEST['monto_imp1'];
    $impuesto2      = $_REQUEST['impuesto2'];
    $monto_imp2     = $_REQUEST['monto_imp2'];
    $impuesto3      = $_REQUEST['impuesto3'];
    $monto_imp3     = $_REQUEST['monto_imp3'];
    $importe_total  = $_REQUEST['importe_final'];
    $grupo          = $_REQUEST['grupo'];
    $voucher        = $_REQUEST['voucher'];
    $emisor         = $_REQUEST['emisor'];
    $glosa          = $_REQUEST['glosa'];
    $c_montos_varios = $_REQUEST['c_montos_varios'];
/*---------------------------------------------------------------------*/

    if(isset($_REQUEST['percepcion']) && !empty($_REQUEST['percepcion']))
    {
	$impot         = $_REQUEST['percepcion'];
	$porcen_apli   = $_REQUEST['mnt_apli_percepcion'];
	$regc_sunat_percepcion = "'{".$porcen_apli.",".$impot.",0,0,00/00/0000}'";
    }else{
	$regc_sunat_percepcion = 'NULL';
    }

    if(isset($_REQUEST['detraccion']) && !empty($_REQUEST['detraccion']))
    {
	$impot         = $_REQUEST['detraccion'];
	$porcen_apli   = $_REQUEST['mnt_apli_detraccion'];
	$regc_sunat_detraccion = "'{".$porcen_apli.",".$impot.",0,0,00/00/0000}'";
    }else{
	$regc_sunat_detraccion = 'NULL';
    }

/*    if(isset($_REQUEST['retencion']) && !empty($_REQUEST['retencion']))
    {
        $importe_total  = $_REQUEST['importe_total'];
        
	$impot         = $_REQUEST['retencion'];
	$porcen_apli   = $_REQUEST['mnt_apli_retencion'];
	$regc_sunat_retencion = "'{".$porcen_apli.",".$impot.",0,0,00/00/0000}'";
    }else{
*/
	$regc_sunat_retencion = 'NULL';
	$importe_total = $_REQUEST['importe_total'];
    //}


	foreach($_REQUEST as $llave => $valor)
	{
		//echo "<!--LLAVE : $llave => VALOR : $valor -->\n";
	}
    
	$plc_codigo = "42101"; //CAMBIAR pro_det_identidad tambien lo estoy poniendo 
    
    if($cod_moneda=="02" || $cod_moneda==2){
	$plc_codigo = "42102";
    }
    
    $O = $AR_ORD[0];
    $num_orden_compra = $O["com_num_compra"];
    if(trim($c_montos_varios)==""){ $c_montos_varios=0; }
    
    //$q1 = "";
    
    $q1 = "INSERT INTO cpag_ta_cabecera 
		       (
			pro_cab_tipdocumento,
			pro_cab_seriedocumento,
			pro_cab_numdocumento,
			pro_codigo,
			pro_cab_fechaemision,
			pro_cab_fecharegistro,
			pro_cab_fechavencimiento, 
			pro_cab_dias_vencimiento, 
			pro_cab_tipcontable,
			plc_codigo,
			pro_cab_moneda,
			pro_cab_tcambio,
			pro_cab_imptotal, 
			pro_cab_impsaldo, 
			pro_cab_fechasaldo,
			pro_cab_grupoc, 
			pro_cab_comprobantec, 
			pro_cab_tipdocreferencia,
			pro_cab_numdocreferencia,
			pro_cab_almacen, 
			pro_cab_glosa,
			pro_cab_impafecto, 
			pro_cab_tipimpto1, 
			pro_cab_tipimpto2,
			pro_cab_tipimpto3,
			pro_cab_impto1, 
			pro_cab_impto2, 
			pro_cab_impto3, 
			pro_cab_catdocumento,
			pro_cab_rubrodoc, 
			com_cab_numorden, 
			pro_cab_numreg, 
			pro_cab_impinafecto,
			regc_sunat_percepcion, 
			regc_sunat_detraccion, 
			regc_sunat_retencion 
		       ) 
	   VALUES 
	               (
			substring('$cod_documento' FROM char_length('$cod_documento')-1 for 2 ), 
			'$serie_doc',
			'$num_documento',
			'$cod_proveedor', 
			to_date('$fec_docu','dd/mm/yyyy'), 
			to_date('$fec_reg','dd/mm/yyyy'),
			to_date('$fecha_ven','dd/mm/yyyy'), 
			null,  
			UTIL_FN_TIPO_ACCION_CONTABLE('CP','$cod_documento'),
			'$plc_codigo', 
			'$cod_moneda', 
			$tasa_cambio,
			$importe_total, 
			$importe_total, 
			now(),
			null, 
			null, 
			substring('$cod_docref' from char_length('$cod_docref')-1 for 2),
			'$num_docurefe',
			'$cod_unidad', 
			'$glosa',
			$monto_imp,
			'$impuesto1', 
			'$impuesto2',
			'$impuesto3', 
			$monto_imp1, 
			$monto_imp2,
			$monto_imp3, 
			null,
			'$cod_rubro',
			'$num_orden_compra', 
			$num_registro, 
			$c_montos_varios,
			$regc_sunat_percepcion,
			$regc_sunat_detraccion,
			$regc_sunat_retencion
			)";
			
    $q2 = "INSERT INTO cpag_ta_detalle ".
                      "( ".
                      "pro_cab_tipdocumento, ".
                      "pro_cab_seriedocumento, ".
                      "pro_cab_numdocumento, ".
                      "pro_codigo, ".
                      "pro_det_identidad, ".
                      "pro_det_tipmovimiento, ".
                      "pro_det_fechamovimiento, ".
                      "pro_det_moneda, ".
                      "pro_det_tcambio, ".
                      "pro_det_impmovimiento, ".
                      "pro_det_grupoc, ".
                      "pro_det_comprobantec, ".
                      "pro_det_tipdocreferencia, ".
                      "pro_det_numdocreferencia, ".
                      "pro_det_almacen, ".
                      "pro_det_glosa ".
                      ")".
           "VALUES ".
                      "( ".
		      "substring('$cod_documento' FROM char_length('$cod_documento')-1 FOR 2 ), ". 
		      "'$serie_doc', ".
		      "'$num_documento', ".
		      "'$cod_proveedor', ".
		      "'001', ".
		      "'1', ".
		      "to_date('$fec_reg','dd/mm/yyyy'), ".
		      "'$cod_moneda', ".
		      "$tasa_cambio, ".
		      "$importe_total, ".
		      "null, ".
		      "null, ".
		      "substring('$cod_docref' FROM char_length('$cod_docref')-1 for 2), ".
		      "'$num_docurefe', ".
		      "'$cod_unidad', ".
		      "'$glosa')";

echo "retorno : " .    $sqlca->query($q1) . "\n";
echo "error: " . $sqlca->error . "\n";
echo "retorno : " .    $sqlca->query($q2) . "\n";
echo "error: " . $sqlca->error . "\n";
   
   echo "<!--QUERY 1 : $q1 -->";
   echo "<!--QUERY 2 : $q2 -->";
   
   // pg_exec($q1);
    //pg_exec($q2);
		
		
} 

function getTipoAlmacen($cod_almacen){
	$ret = pg_result(pg_exec("select ch_tipo_sucursal from int_ta_sucursales where ch_sucursal=trim('$cod_almacen')"),0,0);
	//$r = pg_result(pg_exec("select * from int_ta_sucursales limit 1"),0,0);
	//print "SGSGSGSeee".$r;
	return $ret;
}

?>
