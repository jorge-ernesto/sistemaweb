<?php
function registrarItems($art_descripcion,$art_codigo,$art_cantidad,	$art_costo_uni,$art_costo_total,	$art_costo_uni_dol,$art_costo_total_dol,$ord_compra
,$ord_almacen,$items,$cod_proveedor
,$com_tipo_compra , $com_serie_compra , $com_num_compra 
,$tran_codigo , $mov_fecha  
,$num_guia , $fecha
,$ITEMS_CD){
	
	$agregar = true;
	$ITEMS = $ITEMS_CD;
		for($i=0;$i<count($items);$i++){
			$n = $items[$i];
			$R["art_descripcion"] = $art_descripcion[$n];
			$R["art_codigo"] 	  =	$art_codigo[$n];
			$R["art_cantidad"]	  =	$art_cantidad[$n];
			$R["art_costo_uni"]	  =	$art_costo_uni[$n];
			$R["art_costo_total"] = $art_costo_total[$n];

			$R["art_costo_uni_dol"]	  =	$art_costo_uni_dol[$n];
			$R["art_costo_total_dol"] = $art_costo_total_dol[$n];
			
			$R["ord_compra"]	  = $ord_compra;
			$R["ord_almacen"]	  = $ord_almacen;
			
			$R["cod_proveedor"]	  = $cod_proveedor;
			$R["com_tipo_compra"] = $com_tipo_compra[$n]; 
			$R["com_serie_compra"] = $com_serie_compra[$n];
			$R["com_num_compra"] = $com_num_compra[$n];
			$R["tran_codigo"]	=	$tran_codigo[$n];
			//echo "Registrando tran_Codigo".$R["tran_codigo"];
			$R["mov_fecha"]		=	$mov_fecha[$n];
			//echo "$n.- Registrando orden $ord_compra - articulo ".$art_codigo[$n]." - con fecha ".$mov_fecha[$n]."<br> ";
			
			$R["fecha"]		=	$fecha[$n];
			//echo "num_guia ".$num_guia[$n];
			$R["num_guia"]	=	$num_guia[$n];
			
			//echo $art_codigo[$n];
			//if(verificarRepetidos($R,$ITEMS)){
				$ITEMS[count($ITEMS)] = $R;	
			/*}else{
				//mostrarAlerta("Este articulo con este documento ya han sido registrados");
				$agregar = false;
			}*/
			
		} //fin del for
  
  $ITEMS_CD = $ITEMS; 

	return $ITEMS_CD;
}
	
	
function registrarItemsEdit($art_descripcion,$art_codigo,$art_cantidad,	$art_costo_uni,$art_costo_total,	$art_costo_uni_dol,$art_costo_total_dol,$ord_compra
,$ord_almacen,$items,$cod_proveedor
,$com_tipo_compra , $com_serie_compra , $com_num_compra 
,$tran_codigo , $mov_fecha  
,$num_guia , $fecha
,$ITEMS_CD){
	
  $agregar = true;
  $ITEMS = $ITEMS_CD;
    for($i=0;$i<count($items);$i++){
      $n = $items[$i];
      $R["art_descripcion"] = $art_descripcion[$n];
      $R["art_codigo"] 	  =	$art_codigo[$n];
      $R["art_cantidad"]	  =	$art_cantidad[$n];
      $R["art_costo_uni"]	  =	$art_costo_uni[$n];
      $R["art_costo_total"] = $art_costo_total[$n];

      $R["art_costo_uni_dol"]	  =	$art_costo_uni_dol[$n];
      $R["art_costo_total_dol"] = $art_costo_total_dol[$n];
      
      $R["ord_compra"]	  = $ord_compra[$n];
      $R["ord_almacen"]	  = $ord_almacen;
      
      $R["cod_proveedor"]	  = $cod_proveedor;
      $R["com_tipo_compra"] = $com_tipo_compra[$n]; 
      $R["com_serie_compra"] = $com_serie_compra[$n];
      $R["com_num_compra"] = $com_num_compra[$n];
      $R["tran_codigo"]	=	$tran_codigo[$n];
      //echo "Registrando tran_Codigo".$R["tran_codigo"];
      $R["mov_fecha"]		=	$mov_fecha[$n];
      //echo "$n.- Registrando orden $ord_compra - articulo ".$art_codigo[$n]." - con fecha ".$mov_fecha[$n]."<br> ";
      
      $R["fecha"]		=	$fecha[$n];
      //echo "num_guia ".$num_guia[$n];
      $R["num_guia"]	=	$num_guia[$n];
      
      //echo $art_codigo[$n];
      //if(verificarRepetidos($R,$ITEMS)){
	      $ITEMS[count($ITEMS)] = $R;	
      /*}else{
	      //mostrarAlerta("Este articulo con este documento ya han sido registrados");
	      $agregar = false;
      }*/
	    
    } //fin del for

  $ITEMS_CD = $ITEMS; 

	return $ITEMS_CD;
}
function verificarRepetidos($I,$TMP){
	
  $ret = true;
  for($i=0;$i<count($I);$i++){
    $art_codigo = trim($I["art_codigo"]);
    $ord_compra = trim($I["ord_compra"]);

    for($a=0;$a<count($TMP);$a++){
      $B = $TMP[$a];
      if(trim($B["art_codigo"])==$art_codigo &&	trim($B["ord_compra"])==$ord_compra){
      $ret = false;
      //echo "SI HAY REPETIDOS";
      
      }else{
      
      }
      /*echo "VERIFICANDO REPETIDOS <br>";
      echo $B["art_codigo"]."==".$art_codigo ."&&".$B["ord_compra"]."==".$ord_compra;
      */
    }
  
  }

return $ret;
}

function eliminarItems($ITEMS,$items){
  for($i=0;$i<count($items);$i++){
    $posi = $items[$i];
    array_splice($ITEMS,$posi-$i,1);

  }
return $ITEMS;
}

function mostrarAlerta($mensaje){

    ?>
    <script language="JavaScript" >alert('<?php echo $mensaje;?>');</script>
    <?php
}

function pasarValor($total_dev,$total_cmp,$tasa_cambio,$cod_documento){
	
  /*Aqui definimos si cual suma y cual resta*/
  $tipo_ope = definirOperacion($cod_documento);
  /**/
  if($tipo_ope=="A"){$cal = $total_cmp - $total_dev;}
  if($tipo_ope=="C"){$cal = $total_dev - $total_cmp;}
  
  $_SESSION["cal"] = $cal;
  //$cal = $cal / $tasa_cambio;
  ?><script language="JavaScript">
  var tasa_cambio = parseFloat(opener.document.form1.tasa_cambio.value);
  var monto_imp = parseFloat('<?php echo $cal;?>');
  //alert("dividiendo "+monto_imp+" entre "+tasa_cambio);
  //monto_imp = monto_imp/tasa_cambio;
  opener.document.form1.monto_imp.value = monto_imp;
  //opener.saludar('Gudelia');
  opener.document.form1.cal.value = monto_imp;
  
  opener.document.form1.monto_compras.value = '<?php echo $total_cmp;?>';
  opener.document.form1.monto_devoluciones.value = '<?php echo $total_dev;?>';

  opener.calcularMontos(opener.document.form1);
  
  
  window.focus();
  
  </script><?php

}

function definirOperacion($cod_documento){
	$rs = pg_exec("select tab_car_02 from int_tabla_general where tab_tabla='08' and trim(tab_elemento)='$cod_documento'");
	$A = pg_fetch_array($rs,0);
	$flg_ope = $A[0];
	
	return $flg_ope;
}

/*documentos es o ordenes de compra o numero de movimiento*/
function actualizarArray($documentos,$articulos,$costos,$costos_dol,$AR){

	$TMP[0] = 0;
	for($i=0;$i<count($AR);$i++){
		$A = $AR[$i];
		
		if($A['ord_compra']==$documentos[$i] && $A['art_codigo']==$articulos[$i]){
				$A['art_costo_uni'] = $costos[$i];
				$A["art_costo_total"] = $A["art_costo_uni"]*$A["art_cantidad"];

				$A['art_costo_uni_dol'] = $costos_dol[$i];
				$A["art_costo_total_dol"] = $A["art_costo_uni_dol"]*$A["art_cantidad"];
				
		}
		
		$TMP[$i] = $A;
	}
	return $TMP;
	
}
	
?>