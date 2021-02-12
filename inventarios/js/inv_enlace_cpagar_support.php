<?php


function inicializarFormulario($AR){
	
	$A = $AR[0];		
	$cod_documento 	= $A[5];
	$num_documento 	= $A[7];
	$cod_docurefe 	= "01";
	$num_docurefe 	= $_SESSION["last_nro_orden"];
	$monto_imp 		= $_SESSION["last_total_total"];
	$des_proveedor		= $A[15];
	$des_documento		= $A[16];
	$cod_proveedor		= $A[4];
	$serie_doc = $A[6];
	//$num_doc = $A[7];
	//$num_documento = $serie_doc.$num_doc;
		
	$R["cod_documento"] = $cod_documento;
	$R["num_documento"] = $num_documento;
	$R["cod_docurefe"]  = $cod_docurefe;
	$R["num_docurefe"]  = $num_docurefe;
	$R["monto_imp"]     = $monto_imp;
	$R["des_proveedor"] = $des_proveedor;
	$R["des_documento"] = $des_documento;
	$R["cod_proveedor"] = $cod_proveedor;
	$R["serie_doc"]     = $serie_doc;
				
	return $R;
}


function insertarCpag($fecha_doc,$fecha_reg,$des_proveedor,$cod_proveedor,$des_rubro,$cod_rubro
		,$des_documento,$cod_documento,$num_documento,$des_docurefe,$cod_docurefe,$num_docurefe
		,$fecha_ven,$des_moneda,$cod_moneda,$tasa_cambio,$imp_total,$monto_imp,$igv,$serie_doc
		,$c_montos_varios,$almacen_interno_ing_inv,$percepcion,$ncredito=null){

	include_once('/sistemaweb/include/dbsqlca.php');
	$sqlca = new pgsqlDB('localhost', 'postgres', 'postgres', 'integrado');

	$tiponc=null;
	$seriecore=null;

	if(!is_null($ncredito) || $ncredito!=null){
		$data=explode("-", $ncredito);
		$tiponc=$data[0];
		$seriecore=str_pad($data[1]."".$data[2],8,"0",STR_PAD_LEFT);
	
	}else{
		$tiponc=$cod_docurefe;
		$seriecore=$num_documento;
	}

	/* VARIABLE DE FECHA DE PERIODO */

	$dateact = $fecha_reg;

	
	if($c_montos_varios == "")
		$c_montos_varios = 0;

	//$almacen = $_SESSION["almacen"]; cambiado el 26/05/2005 para que se ingrese el almacen destino de inventarios
	$almacen   = $almacen_interno_ing_inv;
	$fecha_doc = "to_date('$fecha_doc','dd/mm/yyyy')";
	$fecha_reg = "to_date('$fecha_reg','dd/mm/yyyy')";
	$fecha_ven = "to_date('$fecha_ven','dd/mm/yyyy')";
	$plc_codigo = "42101";

	if($cod_moneda=="02" || $cod_moneda==2) {
		$plc_codigo="42102";
	}

	/* VALIDAR PERCEPCION */

	if(empty($percepcion) || $percepcion == NULL){
		$percepcion = 0.00;
		$saldo = $imp_total;
	}else if($percepcion > 0){
		$saldo = $imp_total + $percepcion;
	}

	/* FIN */

	/* FUNCTION NUMERADOR CORRELATIVO */

	$month 	= substr($dateact,3,2);
	$year	= substr($dateact,6,4);

	$dateact = $year."-".$month;

	$sql	= "SELECT numerator FROM act_preseq WHERE dateact = '$dateact' ORDER BY numerator LIMIT 1;";

	$sqlca->query($sql);

	$rowpre = $sqlca->fetchRow();

	$sql	= "SELECT numerator FROM act_day WHERE dateact = '$dateact' ORDER BY numerator LIMIT 1;";

	$sqlca->query($sql);

	$rowact = $sqlca->fetchRow();

	if($rowpre[0] != NULL && $rowact[0] != NULL){

		if($rowpre[0] > $rowact[0]){//TABLE PRESEQ Y ACTDAY

			$upd = "UPDATE act_day SET numerator = $rowpre[0] WHERE dateact = '$dateact';";

			echo "Update Day: \n".$upd;

			if($sqlca->query($upd) < 0)
				return false;

			$del = "DELETE FROM act_preseq WHERE dateact = '$dateact' AND numerator = $rowpre[0] RETURNING numerator";

			if($sqlca->query($del) < 0)
				return false;

			$getnumerator = $sqlca->fetchRow();

		}else{

			$del = "DELETE FROM act_preseq WHERE dateact = '$dateact' AND numerator = $rowpre[0] RETURNING numerator";

			if($sqlca->query($del) < 0)
				return false;

			$getnumerator = $sqlca->fetchRow();
			
		}

	} elseif($rowpre[0] != NULL && $rowact[0] == NULL){

		$ins = "INSERT INTO act_day (dateact,numerator) VALUES('$dateact', $rowpre[0]);";

		if($sqlca->query($ins) < 0)
			return false;

		$del = "DELETE FROM act_preseq WHERE dateact = '$dateact' AND numerator = $rowpre[0] RETURNING numerator";

		if($sqlca->query($del) < 0)
			return false;

		$getnumerator = $sqlca->fetchRow();

	} elseif($rowpre[0] == NULL && $rowact[0] != NULL){

		$upd = "UPDATE act_day SET numerator = numerator + 1 WHERE dateact = '$dateact' RETURNING numerator;";

		if($sqlca->query($upd) < 0)
			return false;

		$getnumerator = $sqlca->fetchRow();

	}else{

		$ins = "INSERT INTO act_day VALUES('$dateact', 1) RETURNING numerator;";

		if($sqlca->query($ins) < 0)
			return false;

		$getnumerator = $sqlca->fetchRow();

	}

	$q_cpag_cab = "INSERT INTO
				cpag_ta_cabecera
				(
					pro_cab_tipdocumento,
					pro_cab_seriedocumento,
					pro_cab_numdocumento,
					pro_codigo,
					pro_cab_fechaemision,
					pro_cab_fecharegistro,
					pro_cab_fechavencimiento,
					pro_cab_tipcontable,
					plc_codigo,
					pro_cab_imptotal,
					pro_cab_impsaldo,
					pro_cab_fechasaldo,
					pro_cab_tipdocreferencia,
					pro_cab_numdocreferencia,
					pro_cab_almacen,
					pro_cab_impafecto,
					pro_cab_tipimpto1,
					pro_cab_impto1,
					pro_cab_rubrodoc,
					com_cab_numorden,
					pro_cab_moneda,
					pro_cab_tcambio,
					pro_cab_impinafecto,
					regc_sunat_percepcion,
					pro_cab_numreg,
					fecha_replicacion
				) 
				VALUES 
				(
					substring(trim('$cod_documento') from char_length(trim('$cod_documento'))-1 for 2),
					trim('$serie_doc'),
					'$num_documento',
					'$cod_proveedor',
					$fecha_doc,
					$fecha_reg,
					$fecha_ven,
					UTIL_FN_TIPO_ACCION_CONTABLE('CP','$cod_documento'),
					'$plc_codigo',
					$imp_total,
					$saldo,
					$fecha_reg,
					'$tiponc',
					'$seriecore',
					trim('$almacen'),
					$monto_imp,
					'09',
					$igv,
					'$cod_rubro',
					'$num_docurefe',
					'$cod_moneda',
					$tasa_cambio,
					$c_montos_varios,
					$percepcion,
					$getnumerator[0],
					now()
				)
	";
/*
echo "<pre>";
print_r($q_cpag_cab);
echo "</pre>";
*/

	$q_cpag_det = "INSERT INTO 
				cpag_ta_detalle
				(
					pro_cab_tipdocumento,
					pro_cab_seriedocumento,
					pro_cab_numdocumento,
					pro_codigo,
					pro_det_identidad,
					pro_det_tipmovimiento,
					pro_det_fechamovimiento, 
					pro_det_moneda, 
					pro_det_tcambio,
					pro_det_impmovimiento,
					pro_det_tipdocreferencia, 
					pro_det_numdocreferencia, 
					pro_det_almacen
				) 
				VALUES 
				(
					substring(trim('$cod_documento') from char_length(trim('$cod_documento'))-1 for 2),
					'$serie_doc',
					'$num_documento',
					'$cod_proveedor',
					'1',
					'1',
					now(), 
					'$cod_moneda',  
					$tasa_cambio,
					$imp_total,
					'$tiponc',
					'$seriecore',
					'$almacen'
				)";

	pg_exec($q_cpag_cab);
	pg_exec($q_cpag_det);

	/*Destruimos las variables de ssesion para este ingreso*/
	unset($last_nro_orden);
	unset($last_total_total);
	unset($CP);
	
	/*Actualizamos inv_ta_compras_devoluciones*/
	$qu = "
		UPDATE
			inv_ta_compras_devoluciones
		SET  
			cpag_tipo_pago = substring(trim('$cod_documento') from char_length(trim('$cod_documento'))-1 for 2),
			cpag_serie_pago = '$serie_doc',
			cpag_num_pago = '$num_documento'
		WHERE
			mov_numero = '".$_SESSION["numero_movimiento"]."'
			AND mov_fecha::date = '".$_SESSION["fechacuenta"]."'
			AND trim(tran_codigo)= trim('".$_SESSION["tran_codigo"]."');
		";

	$tran_codigo = $_SESSION["tran_codigo"];

	?>
	<script>

		opener.location.href = '/sistemaweb/inventarios/inv_movdalmacen.php?fm=<?php echo $tran_codigo; ?>&flg=A';
		window.close();

	</script>
	<?php	

	pg_exec($qu);
	unset($numero_movimiento);
	unset($tran_codigo);

}

function getTipoAlmacen($cod_almacen){
	$ret = pg_result(pg_exec("select ch_tipo_sucursal from int_ta_sucursales where ch_sucursal=trim('$cod_almacen')"),0,0);
	return $ret;
}
