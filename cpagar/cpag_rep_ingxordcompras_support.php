<?php
	function reporteORD($opcion,$fechad,$fechaa,$cod_proveedor,$des_proveedor,$cod_articulo,$des_articulo,$opcion2,$codigo_almacen){
		
		$tran_codigo = "01"; //ORDENES DE COMPRA
		$and_ord = " AND to_number(dev.mov_naturaleza,'999') < 3 ";
		
		if($fechad!="" && $fechaa !=""){
			$and_fechas = " AND dev.mov_fecha >=to_date('$fechad','dd/mm/yyyy') 
								and dev.mov_fecha <= to_date('$fechaa','dd/mm/yyyy') ";
		}
		
		if($codigo_almacen!=""){
			$and_alma = " AND dev.mov_almacen=trim('$codigo_almacen') ";
		}
		
		if($cod_proveedor!=""){
			$and_pro = " AND dev.mov_entidad = '$cod_proveedor' ";
		}
	
		if($cod_articulo!=""){
			$and_art = " AND dev.art_codigo = '$cod_articulo' ";
		}
		
		switch($opcion){
			case "todos":
				//echo "TODOS";
				$and_opc = "";
				
				$and = $and_fechas.$and_pro.$and_art.$and_opc.$and_ord.$and_alma;
				$q1 = " select to_char(dev.mov_fecha,'dd/mm/yyyy'),dev.com_num_compra
				,dev.mov_entidad
				,dev.mov_tipdocuref,dev.mov_docurefe ,dev.mov_almacen
				,dev.art_codigo 
				,dev.mov_cantidad,dev.mov_costounitario 
				,dev.mov_costototal ,dev.mov_numero, to_char(to_date('1841-01-01','yyyy-mm-dd') , 'dd/mm/yyyy')
				,dev.cpag_tipo_pago || dev.cpag_serie_pago || dev.cpag_num_pago as factura 
				,art.art_descripcion , pro.pro_razsocial, dev.mov_fecha, dev.mov_numero 
				 from inv_ta_compras_devoluciones dev  , int_articulos art , int_proveedores pro
				 where 
				 dev.art_codigo = art.art_codigo 
				 and dev.mov_entidad = pro.pro_codigo 
				 and dev.cpag_tipo_pago is null and dev.cpag_serie_pago is null and dev.cpag_num_pago is null 
				 " . $and . "
				 UNION 
				 select to_char(dev.mov_fecha,'dd/mm/yyyy'),dev.com_num_compra
				,dev.mov_entidad
				,dev.mov_tipdocuref,dev.mov_docurefe ,dev.mov_almacen
				,dev.art_codigo 
				,dev.mov_cantidad,dev.mov_costounitario
				,dev.mov_costototal ,dev.mov_numero,to_char(cab.pro_cab_fechaemision,'dd/mm/yyyy')
				,dev.cpag_tipo_pago || dev.cpag_serie_pago || dev.cpag_num_pago as factura 
				,art.art_descripcion , pro.pro_razsocial, dev.mov_fecha, dev.mov_numero 
				 from inv_ta_compras_devoluciones dev,cpag_ta_cabecera cab , int_articulos art, int_proveedores pro 
				 where 
				 cab.pro_cab_tipdocumento = dev.cpag_tipo_pago 
				 and dev.art_codigo = art.art_codigo 
			  	 and dev.mov_entidad = pro.pro_codigo 
				 and cab.pro_cab_seriedocumento = dev.cpag_serie_pago 
				 and cab.pro_cab_numdocumento = dev.cpag_num_pago 
				 and cab.pro_codigo = dev.mov_entidad 
				 " . $and . "
				 ORDER BY 16 DESC, 17";
			break;
			
			case "pendientes":
				//echo "PENDIENTES";
				//$and_opc = " and dev.com_det_estado='1' or dev.com_det_estado='2' ";
				$and_opc = "  dev.cpag_tipo_pago is null and dev.cpag_serie_pago is null and dev.cpag_num_pago is null ";
			
				$and = $and_fechas.$and_pro.$and_art.$and_ord.$and_alma;
				
				$q1 = " select to_char(dev.mov_fecha,'dd/mm/yyyy'),dev.com_num_compra
				,dev.mov_entidad
				,dev.mov_tipdocuref,dev.mov_docurefe ,dev.mov_almacen
				,dev.art_codigo 
				,dev.mov_cantidad,dev.mov_costounitario
				,dev.mov_costototal ,dev.mov_numero,to_char(to_date('1841-01-01','yyyy-mm-dd') , 'dd/mm/yyyy')
				,trim(dev.cpag_tipo_pago) || trim(dev.cpag_serie_pago) || trim(dev.cpag_num_pago) as factura 
				,art.art_descripcion , pro.pro_razsocial, dev.mov_fecha, dev.mov_numero  
				 from inv_ta_compras_devoluciones dev ,  int_articulos art ,  int_proveedores pro 
				 where 
				 " . $and_opc . "
				 and dev.art_codigo = art.art_codigo 
				 and dev.mov_entidad =pro.pro_codigo 
				 " . $and . "
				 UNION 
				 select to_char(dev.mov_fecha,'dd/mm/yyyy'),dev.com_num_compra
				,dev.mov_entidad
				,dev.mov_tipdocuref,dev.mov_docurefe ,dev.mov_almacen
				,dev.art_codigo 
				,dev.mov_cantidad,dev.mov_costounitario
				,dev.mov_costototal ,dev.mov_numero,to_char(cab.pro_cab_fechaemision ,'dd/mm/yyyy') 
				,dev.cpag_tipo_pago || dev.cpag_serie_pago || dev.cpag_num_pago as factura
				,art.art_descripcion , pro.pro_razsocial, dev.mov_fecha, dev.mov_numero 
				 from inv_ta_compras_devoluciones dev,cpag_ta_cabecera cab ,  int_articulos art ,  int_proveedores pro 
				 where 
				 cab.pro_cab_tipdocumento = dev.cpag_tipo_pago 
				 and cab.pro_cab_seriedocumento = dev.cpag_serie_pago 
				 and cab.pro_cab_numdocumento = dev.cpag_num_pago 
				 and cab.pro_codigo = dev.mov_entidad 
				 and dev.art_codigo = art.art_codigo 
				 and dev.mov_entidad =pro.pro_codigo
				 and cab.pro_cab_fechaemision > '" . $fechaa . "'
				 " . $and . "
				 ORDER BY 16 DESC, 17";
			break;
			
			case "atendidos":
				//echo "ATENDIDOS";
				//$and_opc = " and dev.com_det_estado='3' ";
				$and_opc = " and dev.cpag_tipo_pago is not null and dev.cpag_serie_pago is not null and dev.cpag_num_pago is not null ";
			
				$and = $and_fechas.$and_pro.$and_art.$and_opc.$and_ord.$and_alma;
				$q1 = " select to_char(dev.mov_fecha,'dd/mm/yyyy'),dev.com_num_compra
				,dev.mov_entidad
				,dev.mov_tipdocuref,dev.mov_docurefe ,dev.mov_almacen
				,dev.art_codigo 
				,dev.mov_cantidad,dev.mov_costounitario
				,dev.mov_costototal ,dev.mov_numero,to_char(cab.pro_cab_fechaemision,'dd/mm/yyyy')  
				,dev.cpag_tipo_pago || dev.cpag_serie_pago || dev.cpag_num_pago as factura 
				 ,art.art_descripcion , pro.pro_razsocial, dev.mov_fecha, dev.mov_numero  
				 from inv_ta_compras_devoluciones dev,cpag_ta_cabecera cab ,  int_articulos art ,  int_proveedores pro
				 where 
				 cab.pro_cab_tipdocumento = dev.cpag_tipo_pago 
				 and cab.pro_cab_seriedocumento = dev.cpag_serie_pago 
				 and cab.pro_cab_numdocumento = dev.cpag_num_pago 
				 and cab.pro_codigo = dev.mov_entidad 
				 and cab.pro_cab_fechaemision <= '" . $fechaa . "'
				 and cab.pro_cab_fechaemision >= '" . $fechad . "'
				 and dev.art_codigo = art.art_codigo 
				 and dev.mov_entidad = pro.pro_codigo 
				 " . $and . "
				 ORDER BY 16 DESC, 17";
			break;
		}
		/*
		echo "<pre>";
		print_r($q1);
		echo "</pre>";
		*/
		/*
		$and = $and_fecha.$and_pro.$and_art.$and_opc.$and_ord;
		$q1 = " select to_char(dev.mov_fecha,'dd/mm/yyyy'),dev.com_num_compra
		,dev.mov_entidad
		,dev.mov_tipdocuref,dev.mov_docurefe ,dev.mov_almacen
		,dev.art_codigo 
		,dev.mov_cantidad,dev.mov_costounitario
		,dev.mov_costototal ,dev.mov_numero,cab.pro_cab_fecharegistro
		,cab.pro_cab_seriedocumento || cab.pro_cab_numdocumento as factura 
		 from inv_ta_compras_devoluciones dev,cpag_ta_cabecera cab 
		 where 
		 cab.pro_cab_tipdocumento = dev.cpag_tipo_pago 
		 and cab.pro_cab_seriedocumento = dev.cpag_serie_pago 
		 and cab.pro_cab_numdocumento = dev.cpag_num_pago 
		 and cab.pro_codigo = dev.mov_entidad 
		 and dev.tran_codigo = '$tran_codigo' 
		 $and ";
		*/
        //$rs = pg_exec($q1);
        error_log("Query");
        error_log($q1);
        $rs = pg_exec($q1);
	return $rs;
	}
	
	
	
	function reporteDEV($opcion,$fechad,$fechaa,$cod_proveedor,$des_proveedor,$cod_articulo,$des_articulo,$opcion2,$codigo_almacen){
	$tran_codigo = "05"; //DEVOLUCIONES
	$and_ord = " and dev.mov_naturaleza >= 3 ";	
		
		if(trim($fechad)!="" && trim($fechaa) !=""){
			$and_fechas = " and dev.mov_fecha >=to_date('$fechad','dd/mm/yyyy') 
								and mov_fecha <= to_date('$fechaa','dd/mm/yyyy') ";
		}
		
		if(trim($cod_proveedor)!=""){
			$and_pro = " and dev.mov_entidad = '$cod_proveedor' ";
		}
	
		if(trim($codigo_almacen)!=""){
			$and_alma = " and dev.mov_almacen=trim('$codigo_almacen') ";
		}
	
		if(trim($cod_articulo)!=""){
			$and_art = " and dev.art_codigo = '$cod_articulo' ";
		}
		
		switch(trim($opcion)){
			case "todos":
				$and_opc = "";
				$and = $and_fecha.$and_pro.$and_art.$and_opc.$and_ord.$and_alma;
		
				$q1 = " select to_char(dev.mov_fecha,'dd/mm/yyyy')
				,dev.mov_entidad as proveedor 
				,dev.mov_tipdocuref,dev.mov_docurefe ,dev.mov_almacen
				,dev.art_codigo 
				,dev.mov_cantidad,dev.mov_costounitario
				,dev.mov_costototal ,dev.mov_numero,to_char(to_date('1841-01-01','yyyy-mm-dd'), 'dd/mm/yyyy')  
				,dev.cpag_tipo_pago || dev.cpag_serie_pago || dev.cpag_num_pago as factura 
				 ,art.art_descripcion , pro.pro_razsocial 
				 from inv_ta_compras_devoluciones dev,  int_articulos art ,  int_proveedores pro 
				 where 
				 dev.art_codigo = art.art_codigo 
				 and dev.mov_entidad =pro.pro_codigo 
				 and dev.cpag_tipo_pago is null and dev.cpag_serie_pago is null and dev.cpag_num_pago is null 
				 $and 
				 UNION
				 select to_char(dev.mov_fecha,'dd/mm/yyyy')
				,dev.mov_entidad as proveedor 
				,dev.mov_tipdocuref,dev.mov_docurefe ,dev.mov_almacen
				,dev.art_codigo 
				,dev.mov_cantidad,dev.mov_costounitario
				,dev.mov_costototal ,dev.mov_numero,to_char(cab.pro_cab_fechaemision,'dd/mm/yyyy' ) 
				,dev.cpag_tipo_pago || dev.cpag_serie_pago || dev.cpag_num_pago as factura 
				 ,art.art_descripcion , pro.pro_razsocial 
				 from inv_ta_compras_devoluciones dev,cpag_ta_cabecera cab,  int_articulos art ,  int_proveedores pro 
				 where 
				 cab.pro_cab_tipdocumento = dev.cpag_tipo_pago 
				 and cab.pro_cab_seriedocumento = dev.cpag_serie_pago 
				 and cab.pro_cab_numdocumento = dev.cpag_num_pago 
				 and cab.pro_codigo = dev.mov_entidad 
				 and dev.art_codigo = art.art_codigo 
				 and dev.mov_entidad =pro.pro_codigo 
				 $and 
				 ";
		
				
			break;
			
			case "pendientes":
				//$and_opc = " and dev.com_det_estado='1' or dev.com_det_estado='2' ";
				$and_opc = " dev.cpag_tipo_pago is null and dev.cpag_serie_pago is null and dev.cpag_num_pago is null ";
			
				$and = $and_fecha.$and_pro.$and_art.$and_ord.$and_alma;
				
				$q1 = " select to_char(dev.mov_fecha,'dd/mm/yyyy'),dev.com_num_compra
				,dev.mov_entidad
				,dev.mov_tipdocuref,dev.mov_docurefe ,dev.mov_almacen
				,dev.art_codigo 
				,dev.mov_cantidad,dev.mov_costounitario
				,dev.mov_costototal ,dev.mov_numero,to_char(to_date('1841-01-01','yyyy-mm-dd') , 'dd/mm/yyyy')  
				,dev.cpag_tipo_pago || dev.cpag_serie_pago || dev.cpag_num_pago as factura 
				 ,art.art_descripcion , pro.pro_razsocial
				 from inv_ta_compras_devoluciones dev ,  int_articulos art ,  int_proveedores pro 
				 where 
				 $and_opc
				 $and 
				 and dev.art_codigo = art.art_codigo 
				 and dev.mov_entidad =pro.pro_codigo
				 UNION 
				 select to_char(dev.mov_fecha,'dd/mm/yyyy'),dev.com_num_compra
				,dev.mov_entidad
				,dev.mov_tipdocuref,dev.mov_docurefe ,dev.mov_almacen
				,dev.art_codigo 
				,dev.mov_cantidad,dev.mov_costounitario
				,dev.mov_costototal ,dev.mov_numero,to_char(cab.pro_cab_fechaemision,'dd/mm/yyyy')    
				,dev.cpag_tipo_pago || dev.cpag_serie_pago || dev.cpag_num_pago as factura 
				 ,art.art_descripcion , pro.pro_razsocial 
				 from inv_ta_compras_devoluciones dev,cpag_ta_cabecera cab ,  int_articulos art ,  int_proveedores pro 
				 where 
				 cab.pro_cab_tipdocumento = dev.cpag_tipo_pago 
				 and cab.pro_cab_seriedocumento = dev.cpag_serie_pago 
				 and cab.pro_cab_numdocumento = dev.cpag_num_pago 
				 and cab.pro_codigo = dev.mov_entidad 
				 and dev.tran_codigo = '$tran_codigo' 
				 and cab.pro_cab_fechaemision > '$fechaa' 
				 and dev.art_codigo = art.art_codigo 
				 and dev.mov_entidad =pro.pro_codigo
				 $and ";
			break;
			
			case "atendidos":
				//$and_opc = " and dev.com_det_estado='3' ";
				$and_opc = " and dev.cpag_tipo_pago is not null and dev.cpag_serie_pago is not null and dev.cpag_num_pago is not null ";
			
				$and = $and_fecha.$and_pro.$and_art.$and_opc.$and_ord.$and_alma;
				$q1 = " select to_char(dev.mov_fecha,'dd/mm/yyyy'),dev.com_num_compra
				,dev.mov_entidad
				,dev.mov_tipdocuref,dev.mov_docurefe ,dev.mov_almacen
				,dev.art_codigo 
				,dev.mov_cantidad,dev.mov_costounitario
				,dev.mov_costototal ,dev.mov_numero,to_char(cab.pro_cab_fechaemision, 'dd/mm/yyyy')   
				,dev.cpag_tipo_pago || dev.cpag_serie_pago || dev.cpag_num_pago as factura 
				 ,art.art_descripcion , pro.pro_razsocial 
				 from inv_ta_compras_devoluciones dev,cpag_ta_cabecera cab ,  int_articulos art ,  int_proveedores pro 
				 where 
				 cab.pro_cab_tipdocumento = dev.cpag_tipo_pago 
				 and cab.pro_cab_seriedocumento = dev.cpag_serie_pago 
				 and cab.pro_cab_numdocumento = dev.cpag_num_pago 
				 and cab.pro_codigo = dev.mov_entidad 
				 and dev.tran_codigo = '$tran_codigo' 
				 and cab.pro_cab_fechaemision <= '$fechaa' 
				 and cab.pro_cab_fechaemision >= '$fechad' 
				 and dev.art_codigo = art.art_codigo 
				 and dev.mov_entidad =pro.pro_codigo 
				 $and ";
				
			break;
		}
		/*
		$and = $and_fecha.$and_pro.$and_art.$and_opc;
		
		$q1 = " select to_char(dev.mov_fecha,'dd/mm/yyyy')
		,dev.mov_entidad as proveedor 
		,dev.mov_tipdocuref,dev.mov_docurefe ,dev.mov_almacen
		,dev.art_codigo 
		,dev.mov_cantidad,dev.mov_costounitario
		,dev.mov_costototal ,dev.mov_numero,cab.pro_cab_fecharegistro
		,cab.pro_cab_seriedocumento || cab.pro_cab_numdocumento as factura
		 from inv_ta_compras_devoluciones dev,cpag_ta_cabecera cab
		 where 
		 cab.pro_cab_tipdocumento = dev.cpag_tipo_pago 
		 and cab.pro_cab_seriedocumento = dev.cpag_serie_pago 
		 and cab.pro_cab_numdocumento = dev.cpag_num_pago 
		 and cab.pro_codigo = dev.mov_entidad 
		 and dev.tran_codigo = '$tran_codigo'  
		 $and ";
			*/
		
        //echo "CARAJO".$q1;
        error_log("Query");
        error_log($q1);
		$rs = pg_exec($q1);    
	return $rs;
	}
	
	
	
