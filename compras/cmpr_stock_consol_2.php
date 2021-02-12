<?php
			// $v_sql_alma="select tab_elemento, tab_desc_breve from int_tabla_general where tab_tabla='ALMA' and tab_car_02='1' order by tab_elemento ";
			$v_sql_alma="select trim(ch_almacen), ch_nombre_almacen from inv_ta_almacenes  where ch_clase_almacen='1' order by ch_almacen";
			$v_xsql_alma=pg_exec($conector_id, $v_sql_alma);
			// $v_fecha_inicial= $funcion->date_format(pg_result(pg_exec("select to_date('$v_fecha_stock','dd/mm/yyyy')-$v_dias_venta"),0,0),'DD/MM/YYYY') ;
			
			for($x=0; $x<pg_numrows($v_xsql_alma);$x++)
				{
				$v_almacen=pg_result($v_xsql_alma,$x,0);
				
				// comienzo por anadir todos los que existen en saldo inventario se presume que 
				// por cada transaccion generada en el sistema debe reflejarse al final en el inv_saldoalma

				// echo $v_almacen;
				
				$v_sqlareq_cab="insert into COM_TA_AREQ_CABECERA (DT_AREQ_FECHA_PROCESO,
					CH_AREQ_ALMACEN,CH_AREQ_LINEA,
					DT_AREQ_FECHA_STOCK,
					DT_AREQ_FECHA_VENTA_INICIAL,
					DT_AREQ_FECHA_VENTA_FINAL,
					CH_AREQ_NIVEL, DT_AREQ_FECHA_NIVEL,
					CH_AREQ_ESTADO, DT_AREQ_FECHA_ESTADO) 
					values (
					'".$funcion->date_format($v_fecha_proceso,'YYYY-MM-DD')."',
					'$v_almacen','$v_linea',
					'".$funcion->date_format($v_fecha_stock,'YYYY-MM-DD')."',
					'".$funcion->date_format($v_fecha_inicial,'YYYY-MM-DD')."',
					'".$funcion->date_format($v_fecha_final,'YYYY-MM-DD')."',
					'$v_nivel','$v_fecha_nivel','$v_estado','$v_fecha_estado')";
				$v_xsqlareq_cab=pg_exec($conector_id,$v_sqlareq_cab);
				
				// calculo de  stock por bloque 
				include("cmpr_stock_consol_3.php");
			
				// calculo de venta por bloque 
				include("cmpr_stock_consol_4.php");

				// calculo de requ por bloque 
				include("cmpr_stock_consol_5.php");
				
				}	// fin if
				
			$v_sqlareq_det="select DET.*,ART.ART_DESCBREVE,ART.ART_DESCRIPCION from COM_TA_AREQ_DETALLE DET, INT_ARTICULOS ART
			where DET.CH_AREQ_ARTICULO=ART.ART_CODIGO 
			and DT_AREQ_FECHA_PROCESO='".$funcion->date_format($v_fecha_proceso,'YYYY-MM-DD')."' 
			and CH_AREQ_LINEA='$v_linea' 
			order by CH_AREQ_ARTICULO, CH_AREQ_ALMACEN ";
			$v_almacen='';

?>