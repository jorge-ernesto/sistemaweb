<?php

class CancelacionModel extends Model{

	function ObtenerCancelaciones($Fecha_Inicio,$Fecha_Fin) {
		if ($mes == '00') {
			$resultados = array('Estado'=>0);
			return $resultados;
		} else {
			$contador_cancelaciones = 0;
			$contador_errores = 0;
			$row = array();
			$db_original = '/mnt/fox/cons01/intg/fpmovi90.dbf';
			$db_copia = '/acosa/ccobrar/datadbf/fpmovi90.dbf';
			$res=copy($db_original, $db_copia);
			chmod($db_copia,0777);
			$db = dbase_open($db_copia, 2);
			if ($db) {
				$numero_registros = dbase_numrecords($db);
				$datos = array();
				for ($i = 1; $i <= $numero_registros; $i++) {
					$row = dbase_get_record_with_names($db, $i);
					//print_r($row);
					
					$Fecha_Inicio=explode("/",$Fecha_Inicio);
					$Fecha_Fin=explode("/",$Fecha_Fin);
					
					$Fecha_Inicio=$Fecha_Inicio[2].$Fecha_Inicio[1].$Fecha_Inicio[0];
					$Fecha_Fin=$Fecha_Fin[2].$Fecha_Fin[1].$Fecha_Fin[0];
				
					if (trim($row['TIPM_CC']) == '2' ) {						
						
						if (trim($row['ACCI_CC']) >= trim($Fecha_Inicio) and trim($row['ACCI_CC']) <= trim($Fecha_Fin))
						{
							echo "comparo".$row['ACCI_CC'];
							echo "fecha comparada".trim($Fecha_Inicio);							
							$datos[] = $row;							
							$contador_cancelaciones++;
						}
						
					}
				}
				
			dbase_close($db);
			} else echo 'Fall la funcin open para el fichero dbase.';
			
		}
		//borrar copia del archivo dbf
		@unlink($db_copia);
		return $datos;
	}

	function GrabarCancelaciones($row) {
	//conexion
	global $sqlca;
	//$sql = "select ";
  	return $sqlca->functionDB("obtener_cancelacion('".trim($row['CODI_GC'])."','".trim($row['CDOC_GT'])."','"
  													.trim(substr($row['NDOC_GN'],0,3))."','".
  													trim(substr($row['NDOC_GN'],3,7))."','','".
  													trim($row['TIPM_CC'])."','".
  													$row['ACCI_CC']."','".
  													('0'.$row['MONE_GT'])."',".
  													(strlen(trim($row['RATE_GN'])) == 0?'NULL':$row['RATE_GN']).",".
  													$row['IMPO_CC'].",'".
  													$row['GRUP_CC']."','".
  													$row['CTRL_CP']."','','".
  													$row['CDOR_GT']."','".
  													((trim($row['TIPM_CC']) == '2')?trim($row['CTRL_CP']):trim($row['NDOR_GN']))."','001','".
  													trim($row['GLOSA'])."','".$row['deleted']."')");
  	
	//VERIFICA SI EXISTE EN CCOB_TA_DETALLE
		/*$verifica = "select count(*) as total from ccob_ta_detalle where trim(cli_codigo)='".
		trim($row['CODI_GC'])."' and trim(ch_tipdocumento)='".trim($row['CDOC_GT'])
		."' and trim(ch_seriedocumento)='".trim(substr($row['NDOC_GN'],0,3))."' and trim(ch_tipmovimiento)='".trim($row['TIPM_CC'])."' and trim(ch_numdocumento)='".trim(substr($row['NDOC_GN'],3,7))."'
		and trim(ch_comprobante)='".trim($row['CTRL_CP'])."'";
		$pgsql2=pg_exec($verifica);
		$resultado1 = pg_fetch_array($pgsql2);*/
	//SI NO EXISTE INSERTA
		//if ($resultado1['total']==0){
			//print_r($row['CODI_GC'].' DELETED: '.$row['deleted'].'./n');
			//if ($row['deleted']=='0') {
			//OBTENER EL AUTOINCREMENTADO E IMPORTE TOTAL
				//$query = "select nu_importetotal from ccob_ta_cabecera where cli_codigo='".
				//$row['CODI_GC']."' and ch_tipdocumento='".$row['CDOC_GT']
				//."' and ch_seriedocumento='".substr($row['NDOC_GN'],0,3)."' and ch_numdocumento='".substr($row['NDOC_GN'],3,7)."'";
				//$facimporte=pg_query($connection,$query);
				//$resultado=pg_fetch_array($facimporte);
				//$row['importefactura']=(is_null($resultado['nu_importetotal'])?'1':$resultado['nu_importetotal']+1);
				/*$querydetalle="select max(to_number(trim(ch_identidad),'99999999')) as auto from ccob_ta_detalle where cli_codigo='".
				$row['CODI_GC']."' and ch_tipdocumento='".$row['CDOC_GT']."' and ch_seriedocumento='".substr($row['NDOC_GN'],0,3)."' and ch_numdocumento='".substr($row['NDOC_GN'],3,7)."'";
				$pgsqldetalle=pg_query($connection,$querydetalle);
				$resultado=pg_fetch_array($pgsqldetalle);
				$row['auto']=(is_null($resultado['auto'])?'1':$resultado['auto']+1);*/
				//INSERTA EL REGISTRO
				/*$pgsql=pg_exec("INSERT INTO ccob_ta_detalle(
								cli_codigo, 
								ch_tipdocumento, 
								ch_seriedocumento, 
								ch_numdocumento, 
								ch_identidad,
								ch_tipmovimiento, 
								dt_fechamovimiento, 
								ch_moneda, 
								nu_tipocambio, 
								nu_importemovimiento, 
								ch_grupocontable, 
								ch_comprobante, 
								plc_codigo, 
								ch_tipdocreferencia, 
								ch_numdocreferencia,
								ch_sucursal, 
								ch_glosa, 
								dt_fecha_actualizacion,
								ch_usuario, 
								ch_auditorpc,
								emis_cc, 
								rubr_cc,
								tent_cc, 
								rate_gn, 
								tipp_gt,
								user_gn, 
								gcar_cc, 
								asie_cc,
								area_cc, 
								tigv_cp, 
								tisc_cp, 
								tcat_cp, 
								ccto_cc, 
								tipocanje, 
								divi_cc,
								cven_cc, 
								ctaa_cc
								)
								VALUES ('".trim($row['CODI_GC'])."','"
								.trim($row['CDOC_GT'])."','"
								.substr($row['NDOC_GN'],0,3)."','"
								.substr($row['NDOC_GN'],3,7)."', '"
								.$row['auto']."','"
								.$row['TIPM_CC']."','"
								.$row['ACCI_CC']."','"
								.('0'.$row['MONE_GT'])."',"
								.(strlen(trim($row['RATE_GN'])) == 0?'NULL':$row['RATE_GN']).","
								.$row['IMPO_CC'].",'"
								.$row['GRUP_CC']."','"
								.$row['CTRL_CP']."','','"
								.$row['CDOR_GT']."','"
								.((trim($row['TIPM_CC']) == '2')?$row['CTRL_CP']:$row['NDOR_GN'])."',
								'001','"
								.$row['GLOSA']."',"
								."NULL,"
								."NULL".","
								."NULL,'"
								.$row['EMIS_CC']."','"
								.$row['RUBR_CC']."','"
								.$row['TENT_CC']."',"
								.(strlen(trim($row['RATE_GN'])) == 0?'NULL':$row['RATE_GN']).",'"
								.$row['TIPP_GT']."','"
								.$row['USER_GN']."',
								'CON',"
								.(strlen(trim($row['ASIE_CC'])) == 0?'NULL':$row['ASIE_CC']).",'"
								.$row['AREA_CC']."',"
								.(strlen(trim($row['TIGV_CP'])) == 0?'NULL':$row['TIGV_CP']).","
								.(strlen(trim($row['TISC_CP'])) == 0?'NULL':$row['TISC_CP']).","
								.(strlen(trim($row['TCAT_CP'])) == 0?'NULL':$row['TCAT_CP']).",'"
								.$row['CCTO_CC']."','"
								.$row['TIPOCANJE']."','"
								.$row['DIVI_CC']."','"
								.$row['CVEN_CC']."','"
								.$row['CTAA_CC']."'"
								.");");*/
				//if ($pgsql === false){
				//No existe
				//	return 0;
				//} else {
				//Cancelado
				//	return 1;
				//}
			//}else {
			//	return 3;
			//}
		//} else {
			// Ya existe
			/*if ($row['deleted'] == 1) {
				$verifica = "delete from ccob_ta_detalle where cli_codigo='".
				$row['CODI_GC']."' and ch_tipdocumento='".$row['CDOC_GT']
				."' and ch_seriedocumento='".substr($row['NDOC_GN'],0,3)."' and ch_tipmovimiento='".$row['TIPM_CC']."' and ch_numdocumento='".substr($row['NDOC_GN'],3,7)."'
				and ch_comprobante='".$row['CTRL_CP']."'";		
				$pgsql2=pg_exec($verifica);
				return 4;
			}
			return 2;*/
		//}
	}

	function wget_dbf () {
		include( "/acosa/include/wget.inc.php" );
    $wget_sess = new wget_agent( "http://128.1.2.170/acosa/compartido/fpmovi90.dbf", "/acosa/ccobrar/datadbf" );
    $cls = new someClass();
    $wget_sess->wget_store_function( "someMethod", "cls" );
    $wget_sess->wget_script( "/tmp/finish_download" );
    $error = $wget_sess->get_error();
		if( !$error["error_no"] )
		{
				$wget_sess->wget_run();
		} else
				echo $error["error_msg"];
	}
}

class someClass
{
 function someMethod( $a1, $a2, $a3 )
 {
 }

}
	
	
?>