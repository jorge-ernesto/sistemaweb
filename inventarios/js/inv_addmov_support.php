<?php

session_start();

/*PARA DEFINIR LO QUE VAMOS A MOSTRAR O NO DEBEMOS INICIALIZAR CIERTAS VARIABLES,
LO HACEMOS Y LAS METEMOS EN UN ARRAY INDEXADO POR SUS NOMBRES*/
function inicializarVariables($fm,$almacen){
	
	$q ="SELECT
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
			tran_codigo = '$fm' ";
	//echo $q."<br>";
	$A = pg_fetch_array(pg_exec($q),0);

	//$R["cod_almacen"] = $A[0];        	
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
	$R["nro_mov"]	= $nro_mov;
	
	/* LAS AYUDAS DE LOS ALMACENES DEPENDEN DE SI ES UNA ENTRADA O UNA SALIDA
	AQUI LAS CONFIGURO BASANDOME EN LA NATURALEZA 1 O 2 ENTRADA Y
	3 O 4 SALIDA */
	if($A[2]=="1" or $A[2]=="2"){
		$R["flag_ori"] = "2";
		$R["flag_des"] = "1";
		$R["readonly_alma_des"] = "readonly='yes'";
		$R["readonly_alma_ori"] = "";
		$R["ayuda_alma_des"] = true;
		$R["ayuda_alma_ori"] = false;
		if($A[6]==""){ $R["ayuda_alma_ori"] = true;  }
		elseif($A[6]!=""){ $R["ayuda_alma_ori"] = true; }
	}
	
	if($A[2]=="3" or $A[2]=="4"){
		$R["flag_ori"]	= "1";
		$R["flag_des"]	= "3";
		$R["readonly_alma_des"] = "";
		$R["readonly_alma_ori"] = "readonly='yes'";
		$R["ayuda_alma_des"] 	= false;
		$R["ayuda_alma_ori"] 	= true;
		if($A[7]==""){ $R["ayuda_alma_des"] 	= false; }
	}
	/*SI EL VALOR ES S DEBEMOS DE MOSTRAR EL COSTO UNITARIO que viene a ser
	int_articulos.costo_reposicion*/
	
	if($A[3]=="N" && trim($A[0])!="16"){
	$R["readonly_costo_uni"] = "readonly='yes'";
	}	

	return $R;
}

//valida dia consolidado
function Consolidacion($dia,$almacen) {
		global $sqlca;

		$turno = 0;

		$sql = " SELECT validar_consolidacion('$dia',$turno,'$almacen') ";

		$sqlca->query($sql);

		$estado = $sqlca->fetchRow();
		
		if($estado[0] == 1){
			return 1;//Consolidado
		}else{
			return 0;//No consolidado
		}

}

/*Agrega registros a un array temporal*/

function agregarItems($ITEMS,$fm,$almacen_interno,$alma_ori,$alma_des,$cod_proveedor,$tipo_doc,$serie_doc
,$num_doc,$cod_articulo,$des_art_campo,$art_cantidad,$art_costo_uni,$nro_mov,$valor,$naturaleza
,$des_pro,$des_doc,$art_stock
,$tmp_total_sin_igv,$tmp_total_con_igv){
	
	$A[0] = $fm; //inv_movialma.tran_codigo
	$A[1] = $almacen_interno; //inv_movialma.mov_almacen
	$A[2] = $alma_ori; //inv_movialma.mov_almaorigen
	$A[3] = $alma_des; //inv_movialma.mov_almadestino
	$A[4] = $cod_proveedor; //inv_movialma.mov_entidad
	$A[5] = $tipo_doc; //inv_movialma.mov_tipdocuref
	$A[6] = $serie_doc; //inv_movialma.mov_docurefe(primeros3 digitos)
	$A[7] = $num_doc; //inv_movialma.mov_docurefe(siguientes 7 digitos)
	$A[8] = $cod_articulo; //inv_movialma.art_codigo
	$A[9] = $des_art_campo; //DESCRIPCIN SOLO PARA MOSTRARLA
	$A[10]= $art_cantidad; //inv_movialma.mov_cantidad
	$A[11]= $art_costo_uni; //inv_movialma.mov_costounitario
	$A[12]= $nro_mov; //inv_movialma.mov_numero
	$A[13]= $valor; // ESTE VALOR ES PARA MOSTRAR O NO CIERTAS COSAS, solo es vista
	$A[14]= $naturaleza;// ESTE VALOR ES PARA MOSTRAR O NO CIERTAS COSAS, solo es vista pero si atu=2 entocnes si hay un update a la tabla de articulos.costo_reposicion
	$A[15] = $des_pro;
	$A[16] = $des_doc;
	$A[17] = $art_stock;
	$A["total_sin_igv"] = $tmp_total_sin_igv;
	$A["total_con_igv"] = $tmp_total_con_igv;

	/*Debemos comprobar que un mismo articulo no se ingrese 2 veces al array*/
		$agregar = true;
		for($i=0;$i<count($ITEMS);$i++){
			$T = $ITEMS[$i];
			if($T[8]==$cod_articulo){ 	
				$agregar = false;
				$mensaje = "Este articulo ya ha sido ingresado antes";
				//echo $mensaje;
				}
		}
		
		/*Validacion para proveedores
		agregado el 16/11/2004 Caso Brena: Siempre le decia proveedorr invalido
		y en las otras estaciones funcionaba porque tenian una fila todo en blanco de proveedores*/
		
		$VL = inicializarVariables($fm,$almacen_interno);
		$validar_proveedor = $VL["enti"];

		/*Agregado para verificar el margen actual de la linea. 
		recalcula el margen con el costo unitario ingresado vs el precio venta y lo compara con el que tiene la linea.
		Version para Copetrol. En demas estaciones les sale un campo para actualizar el precio
		del articulo siempre y cuando tengan la opcion actualiza_precio = 1 en int_parametros. 15/03/17*/
/*
	$querymargen = pg_exec("select a.art_descripcion as descripcion,tg.tab_descripcion as linea, util_fn_precio_articulo('".$A[8]."') as precio, tg.tab_num_01 as margen from int_articulos a left join int_tabla_general tg on tg.tab_tabla='20' and tg.tab_elemento = a.art_linea where a.art_codigo = '".$A[8]."';");
		$X = pg_fetch_array($querymargen,0);
		$xdescripcion = $X["descripcion"];
		$xlinea = $X["linea"];
		$xprecio = $X["precio"];
		$xmargen = $X["margen"];

		$ymargen = number_format((((($xprecio/1.18)/$art_costo_uni)-1)*100),4);

		if($art_costo_uni != '0'){	
		if($xmargen != '0.0000'){	
		if($A[0] == '01' || $A[0] == '07' || $A[0] == '08'){
		if($ymargen < $xmargen){
		$agregar = false;
		if(!$agregar){
		$mensaje = "El costo unitario ingresado genera un margen menor al establecido por la linea, verificar precio de venta.                         Precio de venta actual: $xprecio. Margen actual de la linea: $xmargen. / Margen en base al costo ingresado: $ymargen.";}
		   }	
		  }
		 }
		}*/
		
		/*Debemos comprobar que el articulo y el proveedor realmente existe en la base de datos*/

		if($agregar){	
		$agregar = validarIngreso("articulo",$cod_articulo); if(!$agregar){$mensaje = "Codigo de Articulo no encontrado, quizas se ha ingresado el codigo mal ";}
		}
		
		if($agregar){
		    if(trim($validar_proveedor)=="P"){
		    $agregar = validarIngreso("proveedor",$cod_proveedor); if(!$agregar){$mensaje = "Codigo de Proveedor no encontrado, quizas se ha ingresado el codigo mal ";}
		    }
		}
		
		if($agregar){
		$agregar = validarIngreso("alma_ori",$alma_ori); if(!$agregar){$mensaje = "Codigo de Almacen Origen no encontrado, quizas se ha ingresado el codigo mal ";}
		}

		if($agregar){
		$agregar = validarIngreso("alma_des",$alma_des); if(!$agregar){$mensaje = "Codigo de Almacen Destinono encontrado, quizas se ha ingresado el codigo mal ";}
		}

		if($agregar){
		$agregar = validarIngreso("tipodoc",$tipo_doc); if(!$agregar){$mensaje = "Tipo de Documento no encontrado, quizas se ha ingresado el codigo mal ";}
		}
		
	/* Aqui ya agregamos segun se deba o no hacerlo */

	if($agregar){
		$ITEMS[count($ITEMS)] = $A;
	}else{
		mostrarAlerta($mensaje);
	}

return $ITEMS;

}

//Inserta los registros guardados en el array

function insertarRegistros($ITEMS,$mov_fecha,$nc="0"){

	/*crearOrdendeCompra($ITEMS) AHORA NOS TOCARIA CREAR LA ORDEN DE COMPRA*/
		//$nro_orden = crearOrdendeCompra($ITEMS);
	/* Vamos sacando los registros del array para insertarlos en la tabla inv_movialma */
	$nro_mov_roca = 0;
	$formulario = "";
	$tran_valor = "";
	$fechacuenta = "";
	
	for($i=0;$i<count($ITEMS);$i++){
	
		$A = $ITEMS[$i];
		$fm = $A[0]; //inv_movialma.tran_codigo
		$almacen_interno = $A[1]; //inv_movialma.mov_almacen
		$alma_ori = $A[2]; //inv_movialma.mov_almaorigen
		$alma_des = $A[3]; //inv_movialma.mov_almadestino
		$cod_proveedor = $A[4]; //inv_movialma.mov_entidad
		$tipo_doc = $A[5]; //inv_movialma.mov_tipdocuref
		$serie_doc = $A[6]; //inv_movialma.mov_docurefe(primeros3 digitos)
		$num_doc = $A[7]; //inv_movialma.mov_docurefe(siguientes 7 digitos)
		$cod_articulo = $A[8]; //inv_movialma.art_codigo
		$des_art_campo = $A[9]; //DESCRIPCIN SOLO PARA MOSTRARLA
		$art_cantidad = $A[10]; //inv_movialma.mov_cantidad
		$art_costo_uni = $A[11]; //inv_movialma.mov_costounitario
		//$nro_mov = $A[12]; //inv_movialma.mov_numero
		$val = $A[13]; //estos 2 campos valor y naturaleza los uso para decidir si pintar o no
		$na  = $A[14]; // y ademas para saber si actualizo o no articulos.costo_reposicion

		//$mov_fecha = "now()"; //inv_movialma.mov_fecha
		//$mov_fecha = "util_fn_fechaactual_aprosys() + current_time"; //inv_movialma.mov_fecha	

		$t      = microtime(true);
		$micro  = sprintf("%06d",($t - floor($t)) * 1000000);
		$hora   = date('H:i:s.'.$micro,$t);
		$_SESSION['fecha'] = $mov_fecha." ".$hora; //inv_movialma.mov_fecha

		/* nro_mov -->Se crea el numero con serie y numero y se hace el insert*/	
		$docu_refe = $serie_doc.$num_doc;
		/*Esta operacion la hago para agregarle el almacen al comienzo y completarlo con ceros*/
		if($i==0) {
			if($na=="2"){
				$nro_orden = crearOrdendeCompra($ITEMS); 
			}else{
				$nro_orden = "";
			}
			$nro_mov = incrementarCorrelativo($fm);
			$nro_mov_roca = $nro_mov;
			$formulario = $fm;
			$tran_valor = $val;
		}else{
			$nro_mov = $nro_mov_roca;
		}
		
		$nro_mov = completarCeros($nro_mov,7,"0");
		$nro_mov = $almacen_interno.$nro_mov;
		
		/*Sacamos el numero de la orden, el tipo es 01 y la serie es el almacen interno */
		if($na=="2") {
			$num_tipdocumento = "01";
		} else {
			$num_tipdocumento = "";
		}

		$mov_costototal = $art_cantidad * $art_costo_uni;

		$q = "
			INSERT INTO inv_movialma
				(
					tran_codigo,
					mov_almacen,
					mov_almaorigen,
					mov_almadestino,
					mov_entidad,
					mov_tipdocuref,
					mov_docurefe,
					art_codigo,
					mov_cantidad,
					mov_costounitario,
					mov_numero,
					mov_fecha,
					mov_naturaleza,
					com_tipo_compra,
					com_serie_compra,
					com_num_compra,
					mov_costototal,
					mov_usuario
				) VALUES (
					'$fm',
					'$almacen_interno',
					'$alma_ori',
					'$alma_des',
					'$cod_proveedor',
					substring(trim('$tipo_doc') from char_length(trim('$tipo_doc'))-1 for 2),
					'$docu_refe',
					'$cod_articulo',
					$art_cantidad,
					$art_costo_uni,
					'$nro_mov',
					'".$_SESSION['fecha']."',
					'$na',
					'$num_tipdocumento',
					'$almacen_interno',
					'$nro_orden',
					$mov_costototal,
					'".$_SESSION['auth_usuario']."'
				);
		";

		/*echo "<pre>";
		print_r($q);
		echo "</pre>";*/

		pg_exec($q);

		actualizarCostoUnitario($val,$na,$art_costo_uni,$cod_articulo,$cod_proveedor);
		
		try{

			$tipodocumento=(int)$tipo_doc;
		
			if($tipodocumento==20 || $tipodocumento=="20" ){

				$queryupdate="UPDATE inv_movialma SET mov_docurefe2='pppp' WHERE tran_codigo='$fm' AND mov_almacen='$almacen_interno' ";
				$queryupdate.=" AND mov_tipdocuref=substring(trim('$tipo_doc') from char_length(trim('$tipo_doc'))-1 for 2)  AND mov_docurefe='..$docu_refe' ";
				$estado=pg_exec($queryupdate);

				if($estado==FALSE){
					throw new Exception("Error no Actualizo la referencia del Nota de credito. ".$docu_refe);
				}
		
			}

		}catch(Exception $e){
			
			echo "==>".$e->getMessage();
		}

	}

	$_SESSION["numero_movimiento"]=$nro_mov;
	$_SESSION["tran_codigo"]=$fm;
	$_SESSION['fechacuenta'] = $mov_fecha; //para inv_ta_compra_devoluciones
	$fechaemision = $mov_fecha; //para cuentas por pagar

	preguntar("Enlazar con Cuentas por Pagar?","/sistemaweb/inventarios/js/inv_enlace_cpagar.php?almacen_interno_ing_inv=$almacen_interno&femision=$fechaemision&nc=$nc",$formulario,$tran_valor);
	
}

function actualizarCostoUnitario($val,$natu,$costo_uni,$art_codigo,$cod_proveedor){
	//if($val=="S" && $natu=="2"){
	if($natu == "2" || $natu == 2){
		$rs = pg_exec("SELECT DISTINCT
							(CASE WHEN
								EXISTS (select art_codigo  from com_rec_pre_proveedor where pro_codigo = '".$cod_proveedor."' AND trim(art_codigo)='".trim($art_codigo)."')
							THEN
								'existe' 
						    ELSE
						    	'nulo'
						    END)
					   	FROM
					   		com_rec_pre_proveedor");

		$A = pg_fetch_array($rs,0);
		$sql = "";

		if($A[0] == "existe"){
			$sql .= "
				UPDATE
					com_rec_pre_proveedor 
				SET
					rec_precio 				= ".$costo_uni.",
					rec_fecha_ultima_compra = NOW()
				WHERE
					pro_codigo = '".$cod_proveedor."'
					AND trim(art_codigo) = '".trim($art_codigo)."';
				";
		} else {
			$sql .= "INSERT INTO com_rec_pre_proveedor(	
					pro_codigo,
					art_codigo, 
					rec_moneda,
					rec_precio,
					rec_descuento1,
					rec_fecha_precio,
					rec_arti_prove,
					rec_fecha_ultima_compra
				) VALUES (			
					'$cod_proveedor',
					'$art_codigo',
					'01',
					'$costo_uni',
					'0.00',
					now(),
					'',
					now()
				)";
		}

		pg_exec($sql);

	}
}

function eliminarItems($ITEMS,$items){
	for($i=0;$i<count($items);$i++){
		$posi = $items[$i];
		array_splice($ITEMS,$posi,1);
	}
	return $ITEMS;
}
/*Sacamos los almacenes de destino y origen de inv_tipotransa*/
function almacenesDefault($fm){
	$rs = pg_exec("select * from inv_tipotransa where tran_codigo='$fm'" );
	$A = pg_fetch_array($rs,0);
	//echo "select * from inv_tipotransa where tran_codigo='$fm'" ;
	$DEF["tran_origen"] = $A[6];
	$DEF["tran_destino"]= $A[7];
	
	/*$rs = pg_exec("select trim(tab_elemento) as cod,tab_descripcion 
	from int_tabla_general  where tab_tabla='ALMA' and tab_car_02='1' 
	and trim(tab_elemento)='".$A[6]."' order by cod");*/
	$rs = pg_exec("select trim(ch_nombre_almacen) from inv_ta_almacenes  where ch_almacen='".$A[6]."'");

	if(pg_numrows($rs)>0){$B = pg_fetch_array($rs,0);
		$DEF["tran_origen_campo"] = $B[0];
	}
	/*$rs = pg_exec("select trim(tab_elemento) as cod,tab_descripcion  
	from int_tabla_general  where tab_tabla='ALMA' and tab_car_02='1'  
	and trim(tab_elemento)='".$A[7]."' order by cod");*/
$rs = pg_exec("select trim(ch_nombre_almacen) from inv_ta_almacenes  where ch_almacen='".$A[7]."'");
		if(pg_numrows($rs)>0){$B = pg_fetch_array($rs,0);
		$DEF["tran_destino_campo"] = $B[0];
		}
	return $DEF;
}
/*Cuando se realiza un ingreso directo se debe de crear �la orden de compra en el momento
insertamos en com_cabecera y com_detalle*/
function crearOrdendeCompra($ITEMS){
	$igv = sacarIgv();
	
	for($i=0;$i<count($ITEMS);$i++){
	
	$A = $ITEMS[$i];
	$fm = $A[0]; //inv_movialma.tran_codigo --> com_detalle.
	$almacen_interno = $A[1]; //inv_movialma.mov_almacen --> com_detalle.
	$alma_ori = $A[2]; //inv_movialma.mov_almaorigen --> com_detalle.
	$alma_des = $A[3]; //inv_movialma.mov_almadestino --> com_detalle.num_seriedocumento
	$cod_proveedor = $A[4]; //inv_movialma.mov_entidad --> com_detalle.pro_codigo
	$tipo_doc = $A[5]; //inv_movialma.mov_tipdocuref --> com_detalle.
	$serie_doc = $A[6]; //inv_movialma.mov_docurefe(primeros3 digitos) --> com_detalle.
	$num_doc = $A[7]; //inv_movialma.mov_docurefe(siguientes 7 digitos) --> com_detalle.
	$cod_articulo = $A[8]; //inv_movialma.art_codigo --> com_detalle.art_codigo
	$des_art_campo = $A[9]; //DESCRIPCIN SOLO PARA MOSTRARLA --> com_detalle.
	$art_cantidad = $A[10]; //inv_movialma.mov_cantidad --> com_detalle.
	$art_costo_uni = $A[11]; //inv_movialma.mov_costounitario --> com_detalle.
	$nro_mov = $A[12]; //inv_movialma.mov_numero --> com_detalle.
	$fecha = "util_fn_fechaactual_aprosys() + current_time";  //inv_movialma.mov_fecha --> com_detalle.com_det_fechaentrega
	//$fecha = "now()"; //inv_movialma.mov_fecha --> com_detalle.com_det_fechaentrega 	
	$num_tipdocumento = "01"; //			   --> com_detalle.num_tipdocumento
	$com_det_estado = "2"; //				   --> com_detalle.com_det_estado
	$tipo_cambio = 0; //CAMBIAR LUEGO!!!!!
	$com_det_precio = (1+$igv)*$art_costo_uni;
	$com_det_imparticulo = $com_det_precio * $art_cantidad ;
	$com_det_impuesto1 = $art_cantidad * $art_costo_uni * $igv;
	/* nro_mov -->Se crea el numero con serie y numero y se hace el insert*/	
		$docu_refe = $serie_doc.$num_doc;
		/*Esta operacion la hago para agregarle el almacen al comienzo y completarlo con ceros*/
		if($i==0){
		$nro_orden = numeroOrden($num_tipdocumento,trim($almacen_interno),"insert");
		$nro_orden = completarCeros($nro_orden,8,"0");
		}
	/*Primero creamos la cabecera, pero solo una vez y en el detalle varias veces*/
	$q_cab = "insert into com_cabecera(
			pro_codigo,num_tipdocumento,num_seriedocumento
			,com_cab_numorden,com_cab_almacen,com_cab_fechaorden
			,com_cab_fechaofrecida,com_cab_fecharecibida,com_cab_tipcambio
			,com_cab_credito,com_cab_formapago,com_cab_imporden
			,com_cab_recargo1,com_cab_estado,com_cab_transmision
			,com_cab_moneda) values(
			'$cod_proveedor','$num_tipdocumento','$almacen_interno'
			,'$nro_orden','$almacen_interno',$fecha
			,$fecha,$fecha,$tipo_cambio
			,'N','01',0
			,0,'2','t'
			,'000001')";
	
	$q_det = "insert into com_detalle(
			pro_codigo,num_tipdocumento,num_seriedocumento
			,com_cab_numorden,art_codigo,com_det_fechaentrega
			,com_det_cantidadpedida,com_det_cantidadatendida,com_det_precio
			,com_det_imparticulo,com_det_descuento1,com_det_estado
			,com_det_cd_impuesto1,com_det_impuesto1) values (
			'$cod_proveedor','$num_tipdocumento','$alma_des'
			,'$nro_orden','$cod_articulo',$fecha
			,$art_cantidad ,$art_cantidad ,$com_det_precio
			,$com_det_imparticulo,0,'2'
			,'01',$com_det_impuesto1)";
	
	
	if($i==0){ pg_exec($q_cab); //echo "crearOrdendeCompra qcab".$q_cab."<br>";
		//echo "<br>".$q_cab."<br>";	
	}
	pg_exec($q_det);

	//echo "<br>".$q_det."<br>";	
	}
	
	$_SESSION["last_nro_orden"] = $nro_orden;
	return $nro_orden;

}

function validarIngreso($tipo,$codigo){
	switch($tipo) {
	case "articulo":
	$q = "select * from int_articulos where art_codigo='$codigo' ";
	break;
	case "proveedor":
	$q = "select * from int_proveedores where pro_codigo='$codigo'";
	break;
	case "alma_ori":
	$q = " select * from inv_ta_almacenes where ch_almacen='$codigo' ";
 	break;
	case "alma_des":
	$q = " select * from inv_ta_almacenes where ch_almacen='$codigo' ";
	break;
	case "tipodoc":
	$q = "select trim(tab_elemento),tab_descripcion 
	from int_tabla_general where tab_tabla='08' 
	and  trim(tab_elemento)='$codigo'";
	break;
	}
	
	$rs = pg_exec($q);
	if(pg_numrows($rs)==0){
	return false;
	}else{return true;}
}

function mostrarAlerta($mensaje){

	?>
	<script language="JavaScript" >alert('<?php echo $mensaje;?>');</script>
	<?php
}

function preguntar($pregunta,$url,$formulario,$tran_valor){

		?><script>//alert('///<?php echo $pregunta."-".$url."-".$formulario."-".$tran_valor;?>///');</script><?php

		if($tran_valor=="S") {
		?>
			
			<script language="JavaScript">
				if(confirm('<?php echo $pregunta;?>')){
					//alert('URL <?php echo $url;?>');
					newwindow=window.open('<?php echo $url;?>','miwin','width=650,height=470,scrollbars=yes,menubar=no,left=130	,top=20');
					if (window.focus) {newwindow.focus()}  // abre el popup
					//alert('URL <?php echo $url;?>');		
				}else{
					location.href='/sistemaweb/inventarios/inv_movdalmacen.php?fm=<?php echo $formulario;?>&flag=A';
					window.close();
				}
			</script>

		<?php
		}else{
			?>
			<!-- <script>alert('TRAN_VALOR Z<?php echo $tran_valor;?>Z');</script> -->
			<script>location.href='/sistemaweb/inventarios/inv_movdalmacen.php?fm=<?php echo $formulario;?>&flag=A';</script>
			<?php
		}
	}

function completarCeros2($cadena, $long_final, $complemento){
	
	$long_inicial = strlen($cadena);
	for($i=0;$i<$long_final - $long_inicial;$i++){
	$cadena = $complemento.$cadena ;
	}
	return $cadena;

}

function incrementarCorrelativo($cod_transa){
	$rs = pg_exec("select UTIL_FN_CORRE_FORM('$cod_transa','insert')");
	$A = pg_fetch_array($rs,0);
	$r = $A[0];
	
	return $r;
}
/*Devuelve el correlativo para el documento o tambien lo puede incrementar*/
function numeroOrden($tipo_docu,$serie,$accion){
	$serie = trim($serie);
	$q = "select UTIL_FN_CORRE_DOCS('$tipo_docu','$serie','$accion')";
	$rs = pg_exec($q);
	$A = pg_fetch_array($rs,0);
	$n = $A[0];
	//echo $q;
	return $n;
}

/*Saco el igv*/
function sacarIgv(){
	$rs = pg_exec("select tab_num_01 from int_tabla_general where tab_tabla='IGV'");
	$A = pg_fetch_array($rs,0);
	$igv = $A[0];
	$igv = $igv/100;
	
	return $igv;
}

function autocompletarDatos($art_codigo,$valor,$cod_almacen){
	switch($valor){
		case "S":
			//$rs = pg_exec(" select art_costoreposicion from int_articulos where art_codigo='$art_codigo' ");
			$rs = pg_exec("SELECT rec_precio FROM com_rec_pre_proveedor WHERE art_codigo='".trim($art_codigo)."' ;");
		
			if(pg_numrows($rs)>0){
				$A = pg_fetch_array($rs,0);
				$costo_uni = $A[0];
			}
			break;

		case "N":
			$rs = pg_exec("SELECT UTIL_FN_SALDOALMACEN('$art_codigo','$cod_almacen') ");
			if(pg_numrows($rs)>0){
				$A = pg_fetch_array($rs,0);
				$costo_uni = $A[0];
			}
			break;
	}
	$rs = pg_exec("SELECT art_descripcion FROM int_articulos WHERE art_codigo ='$art_codigo' ");
	if(pg_numrows($rs)>0){
		$A = pg_fetch_array($rs,0);
		$des_art = $A[0];
		$R["costo_uni"] = $costo_uni;
		$R["des_art"] = $des_art;
		$R["cod_art"] = $art_codigo;
	} else { 
		mostrarAlerta("El codigo de Articulo no existe");
	}
	
	return $R;
}


function modificarItems($CANTIDADES,$COSTOS,$ITEMS,$items,$TOTAL_CON_IGV,$TOTAL_SIN_IGV){

    for($i=0;$i<count($items);$i++){
	$A = $ITEMS[$items[$i]];
	$A[10] = $CANTIDADES[$items[$i]];
	$A[11] = $COSTOS[$items[$i]];
	$A["total_con_igv"]=$TOTAL_CON_IGV[$items[$i]];
	$A["total_sin_igv"]=$TOTAL_SIN_IGV[$items[$i]];
	
	$ITEMS[$items[$i]] = $A;
    }    

    return $ITEMS;

}

//funcion original antes de agregar los chiches para el costeo y elk descuento
/*function modificarItems($CANTIDADES,$COSTOS,$ITEMS,$items){

    for($i=0;$i<count($items);$i++){
	$A = $ITEMS[$items[$i]];
	$A[10] = $CANTIDADES[$items[$i]];
	$A[11] = $COSTOS[$items[$i]];
	
	$ITEMS[$items[$i]] = $A;
    }    

    return $ITEMS;

}

function agregarItems($ITEMS,$fm,$almacen_interno,$alma_ori,$alma_des,$cod_proveedor,$tipo_doc,$serie_doc
,$num_doc,$cod_articulo,$des_art_campo,$art_cantidad,$art_costo_uni,$nro_mov,$valor,$naturaleza
,$des_pro,$des_doc,$art_stock
,$tmp_total_sin_igv,$tmp_total_con_igv){
	
	$A[0] = $fm; //inv_movialma.tran_codigo
	$A[1] = $almacen_interno; //inv_movialma.mov_almacen
	$A[2] = $alma_ori; //inv_movialma.mov_almaorigen
	$A[3] = $alma_des; //inv_movialma.mov_almadestino
	$A[4] = $cod_proveedor; //inv_movialma.mov_entidad
	$A[5] = $tipo_doc; //inv_movialma.mov_tipdocuref
	$A[6] = $serie_doc; //inv_movialma.mov_docurefe(primeros3 digitos)
	$A[7] = $num_doc; //inv_movialma.mov_docurefe(siguientes 7 digitos)
	$A[8] = $cod_articulo; //inv_movialma.art_codigo
	$A[9] = $des_art_campo; //DESCRIPCIN SOLO PARA MOSTRARLA
	$A[10]= $art_cantidad; //inv_movialma.mov_cantidad
	$A[11]= $art_costo_uni; //inv_movialma.mov_costounitario
	$A[12]= $nro_mov; //inv_movialma.mov_numero
	$A[13]= $valor; // ESTE VALOR ES PARA MOSTRAR O NO CIERTAS COSAS, solo es vista
	$A[14]= $naturaleza;// ESTE VALOR ES PARA MOSTRAR O NO CIERTAS COSAS, solo es vista pero si atu=2 entocnes si hay un update a la tabla de articulos.costo_reposicion
	$A[15] = $des_pro;
	$A[16] = $des_doc;
	$A[17] = $art_stock;
	
	/*
	for($i=0;$i<count($A);$i++){
		echo $A[$i];
	}
	*/
	/*Debemos comprobar que un mismo articulo no se ingrese 2 veces al array*/
	/*	$agregar = true;
		for($i=0;$i<count($ITEMS);$i++){
			$T = $ITEMS[$i];
			if($T[8]==$cod_articulo){ 	
				$agregar = false;
				$mensaje = "Este articulo ya ha sido ingresado antes";
				//echo $mensaje;
				}
		}
		
		/*Validacion para proveedores
		agregado el 16/11/2004 Caso Brena: Siempre le decia proveedorr invalido
		y en las otras estaciones funcionaba porque tenian una fila todo en blanco de proveedores*/
	/*	
		$VL = inicializarVariables($fm,$almacen_interno);
		$validar_proveedor = $VL["enti"];
	/*Debemos comprobar que el articulo y el proveedor realmente existe en la base de datos*/
	/*	if($agregar){	
		$agregar = validarIngreso("articulo",$cod_articulo); if(!$agregar){$mensaje = "Codigo de Articulo no encontrado, quizas se ha ingresado el codigo mal ";}
		}
		if($agregar){
		    if(trim($validar_proveedor)=="P"){
		    $agregar = validarIngreso("proveedor",$cod_proveedor); if(!$agregar){$mensaje = "Codigo de Proveedor no encontrado, quizas se ha ingresado el codigo mal ";}
		    }
		}
		if($agregar){
		$agregar = validarIngreso("alma_ori",$alma_ori); if(!$agregar){$mensaje = "Codigo de Almacen Oriogen no encontrado, quizas se ha ingresado el codigo mal ";}
		}
		if($agregar){
		$agregar = validarIngreso("alma_des",$alma_des); if(!$agregar){$mensaje = "Codigo de Almacen Destinono encontrado, quizas se ha ingresado el codigo mal ";}
		}
		if($agregar){
		$agregar = validarIngreso("tipodoc",$tipo_doc); if(!$agregar){$mensaje = "Tipo de Documento no encontrado, quizas se ha ingresado el codigo mal ";}
		}
		
	/*Aqui ya agregamos segun se deba o no hacerlo*/
	/*if($agregar){
	$ITEMS[count($ITEMS)] = $A;
	}else{
		mostrarAlerta($mensaje);
	}
return $ITEMS;
}

*/
