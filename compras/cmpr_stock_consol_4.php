<?php
			// calculo de venta por bloque
			pg_exec($conector_id,"truncate tmp2");
			pg_exec($conector_id,"begin");
			pg_exec($conector_id,"select calcula_venta_rango_fecha('$v_almacen','$v_linea','".$funcion->date_format($v_fecha_inicial,'YYYY-MM-DD')."','".$funcion->date_format($v_fecha_final,'YYYY-MM-DD')."', 'recordset')");
			$v_rs=pg_exec($conector_id,"fetch all in recordset");
			pg_exec($conector_id,"end");
			
			for($i=0;$i<pg_numrows($v_rs);$i++){
				$v_art_codigo = pg_result($v_rs,$i,0);
				$v_art_codigo_stock = 0;
				$v_art_codigo_venta = pg_result($v_rs,$i,1);
				$v_art_codigo_requerimiento=0;
				$v_sqlareq_det="insert into COM_TA_AREQ_DETALLE ( DT_AREQ_FECHA_PROCESO, CH_AREQ_ALMACEN, CH_AREQ_ARTICULO,
								CH_AREQ_LINEA, CH_AREQ_TIPO_ANADIDO, NU_AREQ_CANTIDAD_STOCK_ORI, 
								NU_AREQ_CANTIDAD_VENTA_ORI, NU_AREQ_CANTIDAD_REQ_ORI, NU_AREQ_CANTIDAD_REQ_APRO, 
								NU_AREQ_CANTIDAD_TRA_APRO, CH_AREQ_NIVEL, DT_AREQ_FECHA_NIVEL, CH_AREQ_ESTADO,
								DT_AREQ_FECHA_ESTADO, CH_AREQ_USUARIO ) 
								values ('".$funcion->date_format($v_fecha_proceso,'YYYY-MM-DD')."', '$v_almacen', '$v_art_codigo',
								'$v_linea', 'G', 
								$v_art_codigo_stock,
								$v_art_codigo_venta,
								$v_art_codigo_requerimiento,
								$v_art_codigo_requerimiento,
								0, '$v_nivel','$v_fecha_nivel','$v_estado','$v_fecha_estado', 'user')";
				$v_sqlareq_det="update COM_TA_AREQ_DETALLE 
								set NU_AREQ_CANTIDAD_VENTA_ORI=$v_art_codigo_venta 
								where DT_AREQ_FECHA_PROCESO='".$funcion->date_format($v_fecha_proceso,'YYYY-MM-DD')."'
								and CH_AREQ_ALMACEN='$v_almacen'
								and CH_AREQ_ARTICULO='$v_art_codigo'
								and CH_AREQ_LINEA='$v_linea'
								";
				$v_xsqlareq_det=pg_exec($conector_id,$v_sqlareq_det);
				}
				
