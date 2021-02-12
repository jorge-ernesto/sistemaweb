<?php

class TarjetasMagneticasModel extends Model {

	function ModelReportePDF($filtro=array()) {
		global $sqlca;

		$fecha_ini = $filtro['fecha_ini']; 
		$desde = substr($fecha_ini,6,4)."-".substr($fecha_ini,3,2)."-".substr($fecha_ini,0,2);
		$fecha_fin = $filtro['fecha_fin'];
		$hasta = substr($fecha_fin,6,4)."-".substr($fecha_fin,3,2)."-".substr($fecha_fin,0,2);
		$combo 	   = $filtro['combo'];
		$tipmovi   = $filtro['tipmovi'];
		$radio     = $filtro['modo'];
		$codigo    = trim($filtro['codigo']);
		$registro  = array();	
		$cond1 = "";
		$cond2 = "";
		
		if($combo!="01") 
			$cond1 =  " AND det.cli_codigo = '$codigo' ";
		
		if($tipmovi!="4") 
			$cond2 =  " AND det.ch_tipmovimiento = '$tipmovi' ";

		$sql = "SELECT
				trim(det.cli_codigo)||' - '||trim(cli.cli_razsocial) as cliente,
			        det.ch_seriedocumento,
       				det.ch_comprobante,
				det.ch_tipmovimiento, 
				det.ch_tipdocumento,
				mone.desc_mone, 
				gen.tab_desc_breve||' '||trim(cab.ch_seriedocumento)||trim(cab.ch_numdocumento) as documento,
				to_char(det.dt_fechamovimiento,'dd/mm/yyyy') as fecha,
				det.ch_numdocumento   as documento,
				trim(gen.tab_desc_breve)||' - '||trim(det.ch_numdocreferencia) as documento_referencia,
				tmcc.desc_docu as accion,
				det.ch_glosa as ch_glosa,  
				CASE WHEN cab.ch_moneda='01' THEN 'S/.' END as monetotal,  
				cab.nu_importetotal as cabtotal, 

				CASE WHEN DET.CH_TIPMOVIMIENTO = '1' THEN IIF(DET.CH_MONEDA='01' ,DET.NU_IMPORTEMOVIMIENTO,DET.NU_IMPORTEMOVIMIENTO*DET.NU_TIPOCAMBIO)
					ELSE NULL
					END as cargo_soles,		

				CASE WHEN DET.CH_TIPMOVIMIENTO!='1' THEN IIF(DET.CH_MONEDA='01' ,DET.NU_IMPORTEMOVIMIENTO,DET.NU_IMPORTEMOVIMIENTO*DET.NU_TIPOCAMBIO)
					ELSE NULL
					END as abono_soles,

				cab.dt_fechavencimiento as fecha_vencimiento

     			FROM 
     				ccob_ta_cabecera as cab

					inner join
						ccob_ta_detalle as det on(cab.ch_tipdocumento = det.ch_tipdocumento and cab.ch_seriedocumento = det.ch_seriedocumento and cab.ch_numdocumento = det.ch_numdocumento)
					inner join
						int_clientes as cli on(cab.cli_codigo = cli.cli_codigo)

					left join int_tabla_general as gen on(cab.ch_tipdocumento = substring(trim(tab_elemento) for 2 from length(trim(tab_elemento))-1) and tab_tabla ='08'), 	

				     	(SELECT substring(trim(tab_elemento) for 2 from length(trim(tab_elemento))-1) as cod_mone, tab_desc_breve as desc_mone 
			     			FROM int_tabla_general WHERE tab_tabla ='MONE') as mone,

				     	(SELECT substring(trim(tab_elemento) for 1 from length(trim(tab_elemento))) as cod_docu, tab_desc_breve as desc_docu
				     		FROM int_tabla_general WHERE tab_tabla ='TMCC') as tmcc     
     			WHERE 
     				det.ch_moneda = mone.cod_mone
		     		AND det.ch_tipmovimiento = tmcc.cod_docu
		     		AND det.dt_fechamovimiento BETWEEN '$desde' AND '$hasta' 
		     		$cond1 $cond2
			ORDER BY 
		     			1,8;";

			/*if($tipmovi == '1'){
				$sql .= " AND cab.nu_importesaldo > 0 AND det.ch_tipmovimiento = '1'";
			}elseif($tipmovi == '2'){
				$sql .= " AND det.ch_tipmovimiento = '2'";
			}elseif($tipmovi == '3'){
				$sql .= " AND det.ch_tipmovimiento = '3'";
			}
		     	$sql .="*/

		
		if ($sqlca->query($sql) < 0) 
			return false;

		$DatosArrayFinal = array();
	  	$DatosArray      = array();
	  	$x = 0;

		for($i=0;$i<$sqlca->numrows();$i++) {
			$A = $sqlca->fetchRow();
			$DatosArray['CLIENTE'] 	        = $A["cliente"];
			$DatosArray['RAZSOCIAL']        = $A["raz_social"];
			$DatosArray['FECHA']            = $A["fecha"];
			$DatosArray['ACCION']           = $A["accion"];
			$DatosArray['TIPO DOCUMENTO']   = $A["desc_docu"];
			$DatosArray['DOCUMENTO']        = trim($A["ch_seriedocumento"]).$A["documento"];
			$DatosArray['MONEDA']           = $A["desc_mone"];
			$DatosArray['CARGO SOLES']      = $A["cargo_soles"];
			$DatosArray['ABONO SOLES']      = $A["abono_soles"];
			$DatosArray['FECHA VENCIMIENTO'] = $A["fecha_vencimiento"];
			$DatosArray['DOC REFERENCIA']  	= $A["documento_referencia"];
			$DatosArray['VOUCHER']         	= $A["ch_comprobante"];
			$DatosArray['GLOSA']         	= $A["ch_glosa"];
			$DatosArray['MONETOTAL']       	= $A["monetotal"];
			$DatosArray['CABTOTAL']       	= $A["cabtotal"];
			$DatosArray['FECHA_INI']       	= $fecha_ini;
			$DatosArray['FECHA_FIN']       	= $fecha_fin;
			$DatosArray['COMBO']           	= $combo;
			$DatosArray['TIPMOVI']          = $tipmovi;
			$DatosArray['RADIO']           	= $radio;

			if($codigo != "" && $codigo == $A["cliente"]) {
				$x++;
				$TotCDolares += $A["cargo_soles"];
				$TotADolares += $A["abono_soles"];
			} else {
				if ($x > 0) {
					$DatosArray['TOT_CARGO_SOLES']     = $TotCDolares;
					$DatosArray['TOT_ABONO_SOLES']     = $TotADolares;
					$TotCSoles   = " ";
					$TotASoles   = " ";
				}
				$TotCSoles    = " ";
				$TotASoles    = " ";
				$TotCDolares += $A["cargo_soles"];
				$TotADolares += $A["abono_soles"];
				$x = 0;
				$codigo = "";
			}
			$DatosArrayFinal[$i] = $DatosArray;
			$codigo = $A["cliente"];
			$elegir = $A["accion"];
		}

		return $DatosArrayFinal;
  	}
}
