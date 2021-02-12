<?php

function agregarItems($AR,$tipo_docu,$serie_guia,$num_guia,$art_codigo,$ch_codigo_activo,$des_item_campo,$tipo_guia,$cantidad_item,$cod_cliente,$cod_proveedor,
			$tipo_transa,$tipo_entidad,$mov_almacen,$alma_ori,$alma_des,$glosa1,$glosa2,$mov_numero,$mov_naturaleza,$valorizado,$cod_item) {

	$fila["tipo_docu"]		= $tipo_docu;
	$fila["serie_guia"] 		= $serie_guia;
	$fila["num_guia"] 		= $num_guia;
	$fila["art_codigo"] 		= $art_codigo;
	$fila["ch_codigo_activo"] 	= $ch_codigo_activo;
	
	$fila["des_item_campo"] 	= $des_item_campo;
	$fila["tipo_guia"]		= $tipo_guia;	
	$fila["cantidad_item"]		= $cantidad_item;
	$fila["valorizado"]		= $valorizado;

	$fila["tran_entidad"]		= $tipo_entidad;
		switch ($tipo_entidad) {
			case "C":
				$fila["ch_guia_entidad"] = $cod_cliente;
				$cod_proveedor		 = "null";
				break;
				
			case "P":
				$fila["ch_guia_entidad"] = $cod_proveedor;
				$cod_cliente		 = "null";
				break;
		}

	$fila["cod_proveedor"]		= $cod_proveedor;
	$fila["cod_cliente"]		= $cod_cliente;
	$fila["tipo_transa"]		= $tipo_transa;
	
	$fila["mov_almacen"]		= $mov_almacen; 
	$fila["mov_almaorigen"]		= $alma_ori;
	$fila["mov_almadestino"]	= $alma_des;
	$fila["glosa1"]			= $glosa1; 
	$fila["glosa2"]			= $glosa2;
	$fila["mov_numero"]		= $mov_numero;
	$fila["mov_naturaleza"]		= $mov_naturaleza;
	$fila["cod_item"]		= $cod_item;
	
	$agregar = true;
	
	//Verificamos que no esten ingresando el mismo articulo 2 veces
	for($i = 0; $i < count($AR); $i++) {
		$fila_tmp = $AR[$i];
		
		if($tipo_guia == "I") {		
			if($fila_tmp["art_codigo"] == $fila["art_codigo"]) {
				$agregar = false;
				$mensaje = "Articulo repetido";
				break;
			}		
		}
		
		if($tipo_guia == "A") {		
			if($fila_tmp["ch_codigo_activo"] == $fila["ch_codigo_activo"]) {
				$agregar = false;
				$mensaje = "Activo repetido";
				break;
			}		
		}		
	}
	
	if($agregar) {		
		$AR[count($AR)] = $fila;		
	} else {
		mostrarAlerta($mensaje);
	}
	
	return $AR;	
}

function eliminarItems($AR,$items) {
	for($i=0;$i<count($items);$i++) {
		array_splice($AR,$items[$i]-$i,1);
	}
	
	return $AR;
}

function modificarItems($AR){

}

function grabarDatos($AR) {
	for($i=0;$i<count($AR);$i++) {
		$fila = $AR[$i];	
		
		$tipo_docu		= $fila["tipo_docu"];
		$serie_guia 		= $fila["serie_guia"]; 		
		$num_guia		= $fila["num_guia"];
		$art_codigo		= $fila["art_codigo"];
		$ch_codigo_activo	= $fila["ch_codigo_activo"];
		$des_item_campo		= $fila["des_item_campo"];
		$tipo_guia		= $fila["tipo_guia"];	
		$cantidad_item		= $fila["cantidad_item"];
		$entidad		= $fila["tran_entidad"];
		$des_item_campo		= $fila["des_item_campo"];
		$tipo_guia		= $fila["tipo_guia"];	
		$cantidad_item		= $fila["cantidad_item"];
		$cod_proveedor		= $fila["cod_proveedor"];
		$cod_cliente		= $fila["cod_cliente"];
		$tipo_transa		= $fila["tipo_transa"];
		$ch_guia_entidad	= $fila["ch_guia_entidad"];		
		$mov_almacen		= $fila["mov_almacen"]; 
		$mov_almaorigen		= $fila["mov_almaorigen"];
		$mov_almadestino	= $fila["mov_almadestino"];
		$glosa1			= $fila["glosa1"]; 
		$glosa2			= $fila["glosa2"];
		$mov_naturaleza		= $fila["mov_naturaleza"];
		$valorizado		= $fila["valorizado"];
		$cod_item		= $fila["cod_item"];
		
		if($i == 0) {
			$correlativo = correlativo_documento('09',$serie_guia,'insert');
			$correlativo = str_pad($correlativo,10,"0",STR_PAD_LEFT);		
			$mov_numero  = correlativo_formulario($tipo_transa,"insert");
			$mov_numero  = str_pad($mov_numero,7,"0",STR_PAD_LEFT);
			$mov_numero  = trim($mov_almacen.$mov_numero);
			$mov_numero  = str_pad($mov_numero,10,"0",STR_PAD_LEFT);				
		}
		$num_guia = $correlativo;
		
		if($cantidad_item == "") { 
			$cantidad_item = 0; 
		}
		
		$nu_costounitario = costo_unitario_xvalorizacion($valorizado,$cod_item,$mov_almacen);
		$nu_costototal	  = $nu_costounitario * $cantidad_item;	
		
		$q = "	INSERT INTO
				inv_ta_guias_remision
				( 
					ch_guia_tipo_docu, 
					ch_guia_serie, 
					ch_guia_numero, 
					dt_guia_fecha, 
					art_codigo, 
					ch_codigo_activo, 
					nu_guia_cantidad, 
					tran_codigo, 
					ch_guia_entidad, 
					mov_almacen, 
					mov_almaorigen, 
					mov_almadestino, 
					ch_guia_glosa1, 
					ch_guia_glosa2, 
					mov_numero, 
					mov_naturaleza, 
					ch_guia_tipoentidad,
					nu_costounitario, 
					nu_costototal
				) 
				VALUES
				( 
					'$tipo_docu', 
					'$serie_guia',
					'$num_guia', 
					now(), 
					'$art_codigo',
					'$ch_codigo_activo', 
					$cantidad_item, 
					'$tipo_transa', 
					'$ch_guia_entidad', 
					'$mov_almacen',
					'$mov_almaorigen',
					'$mov_almadestino', 
					'$glosa1',
					'$glosa2',
					'$mov_numero', 
					'$mov_naturaleza',
					'$entidad',
					$nu_costounitario, 
					$nu_costototal
				)";
		//echo $q;		
		pg_exec($q);		
	}	
}

/* PARA DEFINIR LO QUE VAMOS A MOSTRAR O NO DEBEMOS INICIALIZAR CIERTAS VARIABLES, LO HACEMOS Y LAS METEMOS EN UN ARRAY INDEXADO POR SUS NOMBRES */
function inicializarVariables($fm,$almacen) {
	
	$q = "	SELECT 
			tran_codigo,
			tran_descripcion,
			trim(tran_naturaleza),
			tran_valor,
			tran_entidad,
			tran_referencia,
			tran_origen,
			tran_destino,
			tran_nform 
		FROM
			inv_tipotransa 
		WHERE
			tran_codigo='$fm'";
	
	$A = pg_fetch_array(pg_exec($q),0);
	
	$R["des_form"] 	= $A[1];
	$R["natu"] 	= $A[2];
	$R["valor"] 	= $A[3];
	$R["enti"] 	= $A[4];
	$R["ref"] 	= $A[5];
	$R["alma_ori"]	= $A[6];
	$R["alma_des"]	= $A[7];
	
	/* Para el numero del movimiento hay que completarlo con ceros y los 3 primeros son el almacen */
	$nro_mov = $A[8]+1;
	$nro_mov = completarCeros($nro_mov,7,"0");
	$nro_mov = $almacen.$nro_mov;
	$R["nro_mov"] = $nro_mov;
	
	/* LAS AYUDAS DE LOS ALMACENES DEPENDEN DE SI ES UNA ENTRADA O UNA SALIDA AQUI LAS CONFIGURO BASANDOME EN LA NATURALEZA 1 O 2 ENTRADA Y 3 O 4 SALIDA */
	if($A[2] == "1" or $A[2] == "2") {
		$R["flag_ori"] = "2";
		$R["flag_des"] = "1";
		$R["readonly_alma_des"] = "readonly='yes'";
		$R["readonly_alma_ori"] = "";
		$R["ayuda_alma_des"] = true;
		$R["ayuda_alma_ori"] = false;
		if($A[6] == "") { 
			$R["ayuda_alma_ori"] = true;  
		}
	}
	
	if($A[2] == "3" or $A[2] == "4") {
		$R["flag_ori"]	= "1";
		$R["flag_des"]	= "3";
		$R["readonly_alma_des"] = "";
		$R["readonly_alma_ori"] = "readonly='yes'";
		$R["ayuda_alma_des"] 	= false;
		$R["ayuda_alma_ori"] 	= true;
		if($A[7] == "") { 
			$R["ayuda_alma_des"] 	= false; 
		}
	}
	/* SI EL VALOR ES S DEBEMOS DE MOSTRAR EL COSTO UNITARIO que viene a ser int_articulos.costo_reposicion */
	if($A[3] == "N") {
		$R["readonly_costo_uni"] = "readonly='yes'";
	}
	
	return $R;
}

function almacenesDefault($fm) {
	$rs = pg_exec("select * from inv_tipotransa where tran_codigo='$fm'" );
	$A = pg_fetch_array($rs,0);
	$DEF["tran_origen"] = $A[6];
	$DEF["tran_destino"]= $A[7];
	
	$rs = pg_exec("select ch_almacen,ch_nombre_almacen from inv_ta_almacenes where ch_almacen ='".$A[6]."'");
	if(pg_numrows($rs)>0) {
		$A = pg_fetch_array($rs,0);
		$DEF["tran_origen_campo"] = $A[1];
	}

	$rs = pg_exec(" select ch_almacen,ch_nombre_almacen from inv_ta_almacenes where trim(ch_almacen) ='".$A[7]."' ");		
	if(pg_numrows($rs)>0) {
		$A = pg_fetch_array($rs,0);
		$DEF["tran_destino_campo"] = $A[1];
	}
	
	return $DEF; 	
}

function mostrarAlerta($mensaje) {
	?>
	<script language="JavaScript" >alert('<?php echo $mensaje;?>');</script>
	<?php
}

