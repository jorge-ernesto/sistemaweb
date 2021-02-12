<?php

include("config.php");
//include("../../functions.php");
//include("inv_addmov_support.php");

if(!session_is_registered("momento")){
	$momento = momentoActual();
	session_register("momento","momento");
}

//echo "<!-- ORIGEN : ".$_REQUEST['alma_ori']."-->\n";
//echo "<!-- DESTINO : ".$_REQUEST['alma_des']."-->\n";

switch($Grabar){
	case "Grabar los Cambios":
		$mov_num = correlativo_formulario($fm,"insert");
			for($i=0;$i<count($R);$i++){
			
				$T 		= $R[$i];
				$iord_comp 	= $T[0];
				$icod_articulo 	= $T[1];
				$ican_pedida 	= $T[3];
				$ican_atendida 	= $T[4];
				$icosto_uni 	= $T[5];
				$itipo_compra 	= $T[6];
				$iserie_compra 	= $T[7];

				//costo_total
				$icosto_total = $ican_atendida * $icosto_uni;

				/*AGREGADO*/	
				//$mov_num = completarCeros($mov_num,7,"0");
				//$mov_num = $almacen.$mov_num;

				$_SESSION["last_nro_orden"] = $iord_comp;

				/*AGREGADO*/
				    $rs = pg_exec("SELECT
							tran_origen,
				        	        tran_destino,
				   	                tran_naturaleza 
					           FROM
							inv_tipotransa 
				    		   WHERE
							tran_codigo='01'");

				    $Q = pg_fetch_array($rs,0);

				    $almaorigen  = $Q[0]; 
				    $almadestino = $Q[1];
				    $natu = $Q[2];
			    
				    //$almaorigen  = $alma_ori; 
				    //$almadestino = $alma_Des;
				    //$natu = $natu;
			    
			if($_REQUEST['alma_ori'] != ""){

				$almaorigen  = $_REQUEST['alma_ori'];

			}
			
			if($_REQUEST['alma_des'] != ""){

			     $almadestino  = $_REQUEST['alma_des'];

			}
			
			//Insertamos en Inventarios
			$qi = "INSERT INTO
					inv_movialma(
							mov_numero,
							tran_codigo,
							art_codigo,
							mov_fecha,
							mov_almacen,
							mov_almaorigen,
							mov_almadestino,
							mov_naturaleza,
							mov_tipoentidad,
							mov_entidad,
							mov_cantidad,
							mov_costounitario,
							mov_costopromedio,
							mov_costototal, 
							com_tipo_compra,
							com_serie_compra,
							com_num_compra,
							mov_tipdocuref,
							mov_docurefe) 
					 VALUES(
							'".$almacen.completarCeros($mov_num,7,"0")."',
							'$fm',
							'$icod_articulo',
							now(),
							'$cod_almacen'
							'$almaorigen',
							'$almadestino',
							'$natu',
							'13',
							'$proveedor',
							'$ican_atendida',
							'$icosto_uni',
							'$icosto_uni',
							'$icosto_total', 
							'$itipo_compra',
							'$iserie_compra',
							'$iord_comp',
							substring('$tipo_doc' , char_length('$tipo_doc')-1) ,
							'$documento_referencia'
						  ) ";

					echo "<!--QUERY : $qi -->\n";

					pg_exec($qi);

					//echo $qi;
					//Tambien en inv_ta_compras_devoluciones
					/*
					pg_exec("insert into inv_ta_compras_devoluciones(mov_numero,tran_codigo,art_codigo
					,mov_fecha,mov_almacen
					,mov_naturaleza,mov_tipoentidad,mov_entidad
					,mov_cantidad , mov_costounitario,mov_costototal 
					,com_tipo_compra,com_serie_compra,com_num_compra
					)values ('$mov_num','$fm','$icod_articulo',now(),'$cod_almacen'
					, '$natu', '13', '$proveedor'
					,$ican_atendida, $icosto_uni , $icosto_total 
					,'$itipo_compra' , '$iserie_compra' , '$iord_comp')
					");
					*/
			
					//ponemos la cantidad atendida en com_detalle
			
					pg_exec("UPDATE
							com_detalle
						 SET
							com_det_cantidadatendida = com_det_cantidadatendida + '$ican_atendida',
							com_det_estado='2',
							com_det_fechaentrega = now() 
						 WHERE 
							pro_codigo = '$proveedor' AND
							num_tipdocumento = '$itipo_compra' AND
							num_seriedocumento = '$iserie_compra' AND
							com_cab_numorden = '$iord_comp' AND
							art_codigo = '$icod_articulo' AND
							current_user_id = '$momento' AND
							com_det_estado = '3' ");
			
		}

		pg_exec("UPDATE
				com_detalle
			 SET
				com_det_estado='4',
				current_user_id=null 
			 WHERE
				current_user_id = '$momento' ");

		session_unregister("R");
		session_unregister("momento");

		unset($R);
		unset($momento);
		
		/*AGREGADO PARA ENLAZAR CON CUENTAS POR PAGAR*/
			$CP = $ITEMS2;
			$ITEMS2 = null;
			unset($ITEMS2);
			session_register("ITEMS2");
			session_register("CP");
			$_SESSION['numero_movimiento'] = $nro_mov;
			$_SESSION['tran_codigo'] = $fm;

		?>
			<script language="JavaScript" >
				//opener.document.form1.nromov.value='<?php echo $mov_num;?>';
				//opener.form1.submit();
				//opener.location.href='/sistemaweb/inventarios/inv_movdalmacen.php?fm=<?php echo $fm;?>&flag=A';
			
				//window.close();
			
			</script>
		<?php

		//preguntar("Enlazar con Cuentas por Pagar?","/sistemaweb/inventarios/js/inv_enlace_cpagar.php?almacen_interno_ing_inv=$almacen_interno",$fm,$tran_valor);
		
	break;
}

//Cuentas Por Pagar --------------------------------------------------------------------

switch($boton) {

	case "Buscar":
		//echo "Case Buscar"; and cab.com_cab_almacen='$cod_almacen' 
		$q1 = " select s.com_cab_numorden,s.art_descripcion from 
		( select cab.com_cab_numorden,art.art_descripcion 
		from com_cabecera cab,com_detalle det, int_articulos art
		where det.pro_codigo=cab.pro_codigo and det.num_tipdocumento=cab.num_tipdocumento 
		and det.num_seriedocumento=cab.num_seriedocumento 
		
		and det.com_cab_numorden=cab.com_cab_numorden and det.com_cab_numorden like '%$busca%'
		and art.art_codigo=det.art_codigo and det.pro_codigo='$proveedor' 
		and det.com_det_cantidadpedida <> det.com_det_cantidadatendida 
		and det.com_det_estado <> '5' and det.com_det_estado <> '3' 
		union
		select cab.com_cab_numorden,art.art_descripcion 
		from com_cabecera cab,com_detalle det, int_articulos art
		where det.pro_codigo=cab.pro_codigo and det.num_tipdocumento=cab.num_tipdocumento 
		and det.num_seriedocumento=cab.num_seriedocumento
		
		and det.com_cab_numorden=cab.com_cab_numorden and det.com_cab_numorden like '%$busca%'
		and art.art_codigo=det.art_codigo and det.pro_codigo='$proveedor' 
		and det.com_det_cantidadpedida <> det.com_det_cantidadatendida 
		and det.com_det_estado = '3' and det.current_user_id='$momento' 
		) as s order by s.com_cab_numorden";
		
		$rs1 = pg_exec($q1);
	break;
	
	case "Seleccionar":
		//echo "casew seleccionar"; and cab.com_cab_almacen='$cod_almacen' 
		$q1 = " select s.com_cab_numorden,s.art_descripcion from 
		( select cab.com_cab_numorden,art.art_descripcion 
		from com_cabecera cab,com_detalle det, int_articulos art
		where det.pro_codigo=cab.pro_codigo and det.num_tipdocumento=cab.num_tipdocumento 
		and det.num_seriedocumento=cab.num_seriedocumento 
		
		and det.com_cab_numorden=cab.com_cab_numorden and det.com_cab_numorden like '%$busca%'
		and art.art_codigo=det.art_codigo and det.pro_codigo='$proveedor' 
		and det.com_det_cantidadpedida <> det.com_det_cantidadatendida 
		and det.com_det_estado <> '5' and det.com_det_estado <> '3' 
		union
		select cab.com_cab_numorden,art.art_descripcion 
		from com_cabecera cab,com_detalle det, int_articulos art
		where det.pro_codigo=cab.pro_codigo and det.num_tipdocumento=cab.num_tipdocumento 
		and det.num_seriedocumento=cab.num_seriedocumento
		
		and det.com_cab_numorden=cab.com_cab_numorden and det.com_cab_numorden like '%$busca%'
		and art.art_codigo=det.art_codigo and det.pro_codigo='$proveedor' 
		and det.com_det_cantidadpedida <> det.com_det_cantidadatendida 
		and det.com_det_estado = '3' and det.current_user_id='$momento' 
		) as s order by s.com_cab_numorden";
	
		$rs1 = pg_exec($q1);
		
		$q2 = "select cab.com_cab_numorden,art.art_codigo,art.art_descripcion,det.com_det_cantidadpedida 
		,round( (det.com_det_imparticulo-det.com_det_impuesto1)/det.com_det_cantidadpedida ,4 ) as costo_unitario
		,det.num_tipdocumento,det.num_seriedocumento,det.com_det_cantidadatendida
		from com_cabecera cab,com_detalle det, int_articulos art
		where det.pro_codigo=cab.pro_codigo and det.num_tipdocumento=cab.num_tipdocumento 
		and det.num_seriedocumento=cab.num_seriedocumento
		and det.com_cab_numorden=cab.com_cab_numorden and det.com_cab_numorden='$lista' 
		and art.art_codigo=det.art_codigo and det.pro_codigo='$proveedor' 
		and det.com_det_cantidadpedida <> det.com_det_cantidadatendida";
		
		//echo $q2;
		$rs2 = pg_exec($q2);
	break;
	
	case "":
		//echo "case !!!! "; and cab.com_cab_almacen='$cod_almacen' 
		$q1 = " select s.com_cab_numorden,s.art_descripcion from 
		( select cab.com_cab_numorden,art.art_descripcion 
		from com_cabecera cab,com_detalle det, int_articulos art
		where det.pro_codigo=cab.pro_codigo and det.num_tipdocumento=cab.num_tipdocumento 
		and det.num_seriedocumento=cab.num_seriedocumento 
		
		and det.com_cab_numorden=cab.com_cab_numorden and det.com_cab_numorden like '%$busca%'
		and art.art_codigo=det.art_codigo and det.pro_codigo='$proveedor' 
		and det.com_det_cantidadpedida <> det.com_det_cantidadatendida 
		and det.com_det_estado <> '5' and det.com_det_estado <> '3' 
		union
		select cab.com_cab_numorden,art.art_descripcion 
		from com_cabecera cab,com_detalle det, int_articulos art
		where det.pro_codigo=cab.pro_codigo and det.num_tipdocumento=cab.num_tipdocumento 
		and det.num_seriedocumento=cab.num_seriedocumento 
		
		and det.com_cab_numorden=cab.com_cab_numorden and det.com_cab_numorden like '%$busca%'
		and art.art_codigo=det.art_codigo and det.pro_codigo='$proveedor' 
		and det.com_det_cantidadpedida <> det.com_det_cantidadatendida 
		and det.com_det_estado = '3' and det.current_user_id='$momento' 
		) as s order by s.com_cab_numorden";
		//echo $q1;
		/*Lo que se debe de listar son aquellas ordenes de compra que no esten en estado 3 (Esta siendo seleccionado
		por otra persona, los que tengan estado cinco tampoco se listan 
		y listar todas las demas que tengan cantidad pedida y atendida diferentes)*/
		//$ver=$q1;
		$rs1 = pg_exec($q1);
	break;
}

switch($Ingresar){
	case "Ingresar":
		    if(session_is_registered("R")){

		    	$r_limit = count($R);

		    }else{

			$r_limit = 0;

		    }
	
			    for($i=0;$i<count($ordenes);$i++){

					$k 		= $ordenes[$i];
					$S[0] 		= $ord_compra[$k];
					$S[1] 		= $cod_art[$k];
					$S[2] 		= $des_art[$k];
					$S[3] 		= $can_pedida[$k];
					$S[4] 		= $can_atendida[$k];
					$S[5] 		= $costo_uni[$k];
					$S[6] 		= $tipo_compra[$k];
					$S[7] 		= $serie_compra[$k];
					$S[8] 		= $can_pendiente[$k];
					$R[$i+$r_limit] = $S;

			/*echo "se llena la posicion ";
			echo $i+$r_limit."<br>";
			echo "el array tiene ".count($R);
			echo "el limite esta en ".$r_limit."<br>";
			echo "el articulo es ".$des_art[$k]."<br>";
			echo "el k esta en ".$k;
			*/
			/*Marcamos en la tabla con estado 3 y el momento que se grabo en la sesion*/
			pg_exec("UPDATE
					com_detalle
				 SET
					com_det_estado = '3',
					current_user_id = '$momento'
				 WHERE
					pro_codigo = '$proveedor' AND
					num_tipdocumento='".$S[6]."' AND
					num_seriedocumento='".$S[7]."' AND
					com_cab_numorden='".$S[0]."' AND
					art_codigo='".$S[1]."' ");			
			
			/*AGREGADO para enlazar con registro de compras */
			//$almacen_interno = trim($almacen);
			//modificado el 03/06/2005 Miguel Lam

			if($naturaleza==1 || $naturaleza==2){
   			 	$almacen_interno = $alma_des;
			}else{
		    		$almacen_interno = $alma_ori;
			}  

			$cod_proveedor	 = $proveedor;
			$cod_articulo 	 = $cod_art[$k];
			$des_art_campo 	 = $des_art[$k];
			$art_cantidad  	 = $can_atendida[$k];
			$art_costo_uni   = $costo_uni[$k];
			$articulo_stock  = $art_stock[$k];
			$valor 		 = $tran_valor;
			
			$ITEMS2 = agregarItems($ITEMS2,$fm,$almacen_interno,$alma_ori,$alma_des,$cod_proveedor,$tipo_doc,$serie_doc,$num_doc
			,$cod_articulo,$des_art_campo,$art_cantidad,$art_costo_uni,$nro_mov,$valor,$naturaleza,$des_pro,$des_doc,$articulo_stock
			);

			session_register("ITEMS2");
			/*AGREGADO*/
			
			}
		session_register("R", "R");
	//echo "la cuenta ".count($R);
			
	
	break;
	
}

switch($Cancelar){

	case "Cancelar":
		session_unregister("R");
		unset($R);
	break;
}

switch($Eliminar){
	case "Eliminar":
		
		for($i=0;$i<count($reg_ordenes);$i++){

			$k = $reg_ordenes[$i];
			$T = $R[$k];
			array_splice($R,$k,1);
		
			pg_exec("UPDATE
					com_detalle
				 SET
					com_det_estado = '1',
					current_user_id = null 
				 WHERE
					current_user_id = '$momento' AND
					pro_codigo='$proveedor' AND
					num_tipdocumento='".$T[6]."' AND
					num_seriedocumento='".$T[7]."' AND
					com_cab_numorden='".$T[0]."' AND
					art_codigo='".$T[1]."' ");
		}
		
		session_register("R", "R");
		
	break;

}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<title>Ordenes de Compra - Devoluciones</title>
	 <head>
		<script language="JavaScript">

			function pasarValorOpener(lista,form,cod,des){
				var valor = lista.value;

				//alert(valor);
				//opener.document.form1.cod_proveedor.value = valor;
				//alert("opener.document."+cod+".value = '"+valor+"'");

				eval("opener.document."+cod+".value = '"+valor+"'");
				form.submit();
			}

			function grabarCambio(form) {
	
				if(confirm(' Esta seguro de actualizar inventarios ?')){
					form.Grabar.value='Grabar los Cambios';
		
					form.submit();
		
				}else{
		
				}

			}

		</script>

		<script language="JavaScript" src="miguel.js"></script>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<link rel="stylesheet" href="/sistemaweb/sistemaweb.css" type="text/css">
	  </head>
<body>

<form name="form1" method="post" action="">
  <input type="text" name="busca">
  <input type="submit" name="boton" value="Buscar">
  Buscar Orden de compra <?php echo $documento_referencia." - - - ".$tran_valor;?> 
  <input type="hidden" name="alma_ori" value="<?php echo $alma_ori;?>">
  <input type="hidden" name="alma_des" value="<?php echo $alma_des;?>">
  <input type="hidden" name="tipo_doc" value="<?php echo $tipo_doc;?>">
  <input type="hidden" name="serie_doc" value="<?php echo $serie_doc;?>">
  <input type="hidden" name="num_doc" value="<?php echo $num_doc;?>">
  <input type="hidden" name="nro_mov" value="<?php echo $nro_mov;?>">
  <input type="hidden" name="naturaleza" value="<?php echo $naturaleza;?>">
  <input type="hidden" name="des_doc" value="<?php echo $des_doc;?>">
  <input type="hidden" name="des_pro" value="<?php echo $des_pro;?>">
  <br>
  <select name="lista" size="7">
  <?php
	for($i=0;$i<pg_numrows($rs1);$i++){
		$A = pg_fetch_array($rs1,$i);
  		print "<option value='$A[0]'>$A[0] - $A[2] - $A[1]</option>";
  	}
  ?>
  </select>
  <input type="hidden" name="cod_almacen" value="<?php echo $cod_almacen;?>">
  <input type="hidden" name="documento_referencia" value="<?php echo $documento_referencia;?>">
  <input type="hidden" name="tran_valor" value="<?php echo $tran_valor;?>">
  <br>
  <input type="submit" name="boton" value="Seleccionar">
  <!-- <input type="button" name="Button" value="Button" onClick="javascript:opener.location.href='/sistemaweb/inventarios/inv_movdalmacen.php?fm=<?php echo $fm;?>&flag=A';"> -->
  <table width="755" border="1">
    <tr> 
      <td width="102"><div align="center">Orden de Compra</div></td>
      <td width="182"><div align="center">Articulo</div></td>
      <td width="59"><div align="center">Cantidad Pedida</div></td>
      <td width="88"><div align="center">Candidad Atendida hasta la fecha</div></td>
      <td width="64">Candidad Atendida ahora</td>
      <td width="36">Costo <br>
        Uni.</td>
      <td width="37">Stock</td>
      <td width="68"><div align="center">Marcar</div></td>
      <td width="65"><input type="submit" name="Ingresar" value="Ingresar"></td>
    </tr>
    <!-- <?php for($i=0;$i<pg_numrows($rs2);$i++){
   
   $A = pg_fetch_array($rs2,$i);
   $mostrar = true;
   			for($j=0;$j<count($R);$j++){
   			$Q = $R[$j];			
   				if($Q[0]==$A[0] && $Q[1]==$A[1]){ $mostrar=false;
					
				}			
			}
   $T[0] = $A[0];
   $T[1] = $A[1];
   $T[2] = $A[2];
   $T[3] = $A[3];
   $T[4] = $A[4];
   $T[5] = $A[5];
   $T[6] = $A[6];
   $T[7] = $A[7];
   $T[8] = $A[8];
   if($mostrar){
   $sig = count($P);
   $P[$sig] = $T;
   }
   
   }
   
   for($i=0;$i<count($P);$i++){
   $A = $P[$i];
   $stock =  stockArticulo("actual","actual",$A[1],trim($almacen));
   ?> -->
    <tr> 
      <td height="25"> <div align="center"> 
          <input type="text" name="ord_compra[]" value="<?php echo $A[0];?>" readonly="yes" size="10">
        </div></td>
      <td><div align="center"> 
          <input type="hidden" name="cod_art[]" value="<?php echo $A[1];?>" readonly="yes">
          <input type="text" name="des_art[]" value="<?php echo $A[2];?>" readonly="yes" size="30">
        </div></td>
      <td><div align="center"> 
          <input type="text" name="can_pedida[]" value="<?php echo $A[3];?>" readonly="yes" size="10" onKeyUp="javascript:reflejarData(this,ped<?php echo $i;?>)">
          <br>
          <input type="hidden" name="ped<?php echo $i;?>" value="<?php echo $A[3];?>">
        </div></td>
      <td><div align="center"> 
          <input type="text" name="can_pendiente[]" value="<?php echo $A[7];?>" size="10"  readonly="yes" onKeyUp="javascript:reflejarData(this,ate<?php echo $i;?>)">
          <input type="hidden" name="ate<?php echo $i;?>" value="<?php echo $A[7];?>">
        </div></td>
      <td><input type="text" name="can_atendida[]" value="<?php echo $A[3]-$A[7];?>" size="10" onKeyUp="javascript:validarIngreso(ped<?php echo $i;?> , ate<?php echo $i;?> , this)"> 
      </td>
      <td><input type="text" name="costo_uni[]" size="7" value="<?php echo $A[4];?>"></td>
      <td><input type="text" name="art_stock[]" size="7" value="<?php echo $stock;?>" readonly="true"></td>
      <td><div align="center"> 
          <input type="checkbox" name="ordenes[]" value="<?php echo $i;?>" >
          <input type="hidden" name="tipo_compra[]" value="<?php echo $A[5];?>">
          <input type="hidden" name="serie_compra[]" value="<?php echo $A[6];?>">
        </div></td>
      <td></td>
    </tr>
    <!-- <?php 
	} ?> -->
    <tr> 
      <td><input type="submit" name="Ingresar" value="Ingresar"></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td colspan="2">&nbsp;</td>
      <td colspan="2">&nbsp;</td>
      <td><input type="hidden" name="fm" value="<?php echo $fm;?>"></td>
      <td>&nbsp;</td>
    </tr>
  </table>
  <br>

Registrados
  
  <table width="755" border="1">
    <tr> 
      <td width="100" height="43"> <div align="center">Orden de Compra</div></td>
      <td width="186"><div align="center">Articulo</div></td>
      <td width="59"><div align="center">Cantidad Pedida</div></td>
      <td width="63"><div align="center">Candidad Atendida hasta la fecha</div></td>
      <td width="63">Candidad Atendida ahora</td>
      <td width="36">Costo <br>
        Uni.</td>
      <td width="37">Stock</td>
      <td width="93"><div align="center">Marcar</div></td>
      <td width="64"><input type="submit" name="Eliminar" value="Eliminar"></td>
    </tr>
    <!-- <?php for($i=0;$i<count($R);$i++){
   $T = $R[$i];
   $total = ($T[5] * $T[4]);
	$sum_total = $sum_total + $total;
	$last_total_total = $sum_total;
	$_SESSION["last_total_total"] = $last_total_total;
	$stock =  stockArticulo("actual","actual",$T[1],trim($almacen));
   ?> -->
    <tr> 
      <td height="25"> <div align="center"> 
          <input type="text" name="reg_ord_compra[]" value="<?php echo $T[0];?>" readonly="yes" size="10">
        </div></td>
      <td><div align="center"> 
          <input type="hidden" name="reg_cod_art[]" value="<?php echo $T[1];?>" readonly="yes">
          <input type="text" name="reg_des_art[]" value="<?php echo $T[2];?>" readonly="yes" size="30">
        </div></td>
      <td><div align="center"> 
          <input type="text" name="reg_can_pedida[]" value="<?php echo $T[3];?>" readonly="yes" size="10">
        </div></td>
      <td><div align="center"> 
          <input type="text" name="reg_can_pendiente[]" value="<?php echo $T[8];?>" size="10" readonly="yes">
        </div></td>
      <td><input type="text" name="reg_can_atendida[]" value="<?php echo $T[4];?>" size="10" readonly="yes" ></td>
      <td><input type="text" name="reg_costo_uni[]" size="7" value="<?php echo $T[5];?>"  readonly="yes" ></td>
      <td><input type="text" name="reg_art_stock[]" size="7" value="<?php echo $stock;?>"  readonly="yes" ></td>
      <td><div align="center"> 
          <input type="checkbox" name="reg_ordenes[]" value="<?php echo $i;?>">
          <input type="hidden" name="reg_tipo_compra[]" value="<?php echo $T[6];?>">
          <input type="hidden" name="reg_serie_compra[]" value="<?php echo $T[7];?>">
        </div></td>
      <td><?php echo "tipo".$T[6]."-----serie".$T[7];?></td>
    </tr>
    <!-- <?php } ?> -->
    <tr> 
      <td><input type="submit" name="Eliminar" value="Eliminar"></td>
      <td><input type="submit" name="Cancelar" value="Cancelar"></td>
      <td>&nbsp;</td>
      <td colspan="2">Total :<?php echo $last_total_total?></td>
      <td colspan="2">&nbsp;</td>
      <td>&nbsp;</td>
      <td>.</td>
    </tr>
  </table>
  <p align="center"> 
    <input name="Btn_Grabar" type="button" onClick="javascript:grabarCambio(form1);" value="Grabar los Cambios">
    <input type="hidden" name="Grabar" value="">
  </p>
  <p>&nbsp; </p>
</form>
</body>
</html>
<?php pg_close();?>

