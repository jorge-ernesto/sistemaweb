<?php

class InterfaceDominioModel extends Model {

	function obtenerAlmacenes($codigo) {
		global $sqlca;
		
		$cond = '';
		if ($codigo != "") 
			$cond = "AND trim(ch_sucursal) = '".pg_escape_string($codigo)."' ";
		
		$sql = "SELECT ch_almacen, trim(ch_nombre_almacen) FROM inv_ta_almacenes WHERE ch_clase_almacen='1' ".$cond." ORDER BY ch_almacen;";

		if ($sqlca->query($sql) < 0) 
			return false;
			
		$result = Array();
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$result[$a[0]] = $a[0] . " - " . $a[1];
		}
		return $result;
	}
      
  	function procesarInterface($almacen, $diad, $diah) {
    		global $sqlca;
    		    		
    		$FechaDiv 	= explode("/", $diad);
		$desde    	= $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];
		$FechaDiv2 	= explode("/", $diah);
		$hasta    	= $FechaDiv2[2]."-".$FechaDiv2[1]."-".$FechaDiv2[0];
		$anio 	  	= $FechaDiv[2];
		$mes 	  	= $FechaDiv[1];
		$postrans 	= "pos_trans".$FechaDiv[2].$FechaDiv[1];
		$resultados	= Array();
				    		
    		// TICKETS - POS_TRANSYYYYMM
    		$sql = "CREATE TABLE tmp_dominio_tickets AS  
			(    		
	    			SELECT 
					t.td as tipo,
					pos.nroserie as serie,
					t.trans as numero,
					t.dia::date as fecha,
					t.ruc as ruc,
					r.razsocial as razsocial,
					t.codigo as codigo,
					t.importe-t.igv as neto,
					t.igv as igv,
					t.importe as total
				FROM 
					$postrans t 
					LEFT JOIN ruc r ON (t.ruc=r.ruc) 
					LEFT JOIN pos_cfg pos ON (pos.pos=t.caja)
				WHERE 
					td IN ('F','B') 
					AND t.es='$almacen' 
					AND date(dia) BETWEEN '$desde' AND '$hasta' 
				ORDER BY
					fecha, tipo, serie, numero
			); ";	
		//echo $sql;			
		$sqlca->query($sql);
		$q1 = "COPY tmp_dominio_tickets to '/home/data/tickets.txt' WITH DELIMITER AS ',' NULL as ''";
		$sqlca->query($q1);
		$q1 = "DROP TABLE tmp_dominio_tickets";	
		$sqlca->query($q1);			
				
		// DOCUMENTOS - FAC_TA_FACTURA_CABECERA
    		$sql = "CREATE TABLE tmp_dominio_documentos AS  
    			(
    				SELECT 
					cab.ch_fac_tipodocumento as tipo,
					cab.ch_fac_seriedocumento as serie,
					cab.ch_fac_numerodocumento as numero,
					cab.dt_fac_fecha as fecha,
					cli.cli_ruc as ruc,
					cli.cli_razsocial as razsocial,
					det.art_codigo as codigo,
					det.nu_fac_importeneto as neto,
					det.nu_fac_impuesto1 as igv,
					det.nu_fac_valortotal as total
				FROM 
					fac_ta_factura_cabecera cab
					LEFT JOIN fac_ta_factura_detalle det ON (cab.ch_fac_tipodocumento=det.ch_fac_tipodocumento AND cab.ch_fac_seriedocumento=det.ch_fac_seriedocumento AND cab.ch_fac_numerodocumento=det.ch_fac_numerodocumento AND cab.cli_codigo=det.cli_codigo)
					LEFT JOIN fac_ta_factura_complemento com ON (cab.ch_fac_tipodocumento=com.ch_fac_tipodocumento AND cab.ch_fac_seriedocumento=com.ch_fac_seriedocumento AND cab.ch_fac_numerodocumento=com.ch_fac_numerodocumento AND cab.cli_codigo=com.cli_codigo)				
					LEFT JOIN int_clientes cli ON (cli.cli_codigo=cab.cli_codigo) 
				WHERE 
					cab.ch_almacen='$almacen' 
					AND cab.ch_fac_tipodocumento IN ('10','35','20','11') 
					AND date(cab.dt_fac_fecha) BETWEEN '$desde' AND '$hasta' 
				ORDER BY
					fecha, tipo, serie, numero
			);";								
		
		//echo $sql;
		$sqlca->query($sql);		
		$q2 = "COPY tmp_dominio_documentos to '/home/data/documentos.txt' WITH DELIMITER AS ',' NULL as ''";
		$sqlca->query($q2);
		$q2 = "DROP TABLE tmp_dominio_documentos";	
		$sqlca->query($q2);
		
    		return 1;
  	}
}
