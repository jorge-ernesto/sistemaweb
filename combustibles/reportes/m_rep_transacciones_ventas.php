<?php
class ModelReporteTransaccionVenta extends Model {
	function GetAlmacen() {
		global $sqlca;
		try {
			$sql = "
				SELECT
					ch_almacen as nualmacen,
					TRIM(ch_almacen) || ' - ' || TRIM(ch_nombre_almacen) as noalmacen
				FROM
					inv_ta_almacenes
				WHERE
					ch_clase_almacen = '1'
				ORDER BY
					ch_almacen;
			";

			if($sqlca->query($sql) <= 0){
				throw new Exception("Error Almacen");
			}

			while($reg = $sqlca->fetchRow()){
				$registro[] = $reg;
			}
			return $registro;

		} catch(Exception $e) {
			throw $e;
		}
	}

	function GetEmpresa($nualmacen){
		global $sqlca;
		$cond = NULL;
		
		if($nualmacen != 'T'){
			$cond = "
			WHERE
				ch_sucursal = '".$nualmacen."'";
		}

		try {

			$query = "
				SELECT
				    razsocial as norazsocial
				FROM
					int_ta_sucursales
				$cond
				LIMIT 1;
			";

			if($sqlca->query($query) <= 0){
				throw new Exception("Error Empresa");
			}

			$data = $sqlca->fetchRow();

			return $data['norazsocial'];

		}catch(Exception $e){
			throw $e;
		}
	}

	function SearchTransaccionVenta($data){
		global $sqlca;

		$cmbnualmacen 	= $data['cmbnualmacen'];
		$txtnofechaini 	= $data['txtnofechaini'];
		$txtnofechafin 	= $data['txtnofechafin'];
		$rdnotipo 		= $data['rdnotipo'];

		$postrans 		= "pos_trans".substr($txtnofechaini,6,4).substr($txtnofechaini,3,2);

		$condalmacent 	= NULL;
		$condalmacendm 	= NULL;

		if($cmbnualmacen != 'T'){
			$condalmacent	= "AND PT.es = '".$cmbnualmacen."'";
			$condalmacendm	= "AND FC.ch_almacen = '".$cmbnualmacen."'";
		}

		try {

			$registro = array();

			$sql = "
				SELECT 
					to_char(PA.fapertura,'DD/MM/YYYY') as fapertura,
					PTB.nuregistrotb AS nuregistrotb,
					PTB.nuimportetb AS nuimportetb,
					PTF.nuregistrotf AS nuregistrotf,
					PTF.nuimportetf AS nuimportetf,
					FCB.nuregistrodmb AS nuregistrodmb,
					FCB.nuimportedmb AS nuimportedmb,
					FCF.nuregistrodmf AS nuregistrodmf,
					FCF.nuimportedmf AS nuimportedmf,
					FCNC.nuregistrodmnc AS nuregistrodmnc,
					FCNC.nuimportedmnc AS nuimportedmnc,
					FCND.nuregistrodmnd AS nuregistrodmnd,
					FCND.nuimportedmnd AS nuimportedmnd
				FROM	
					(SELECT
						da_fecha AS fapertura
					FROM
						pos_aprosys
					WHERE
						da_fecha BETWEEN TO_DATE('$txtnofechaini', 'DD/MM/YYYY') and TO_DATE('$txtnofechafin', 'DD/MM/YYYY')
					GROUP BY
						da_fecha
					) PA

					LEFT JOIN --POS_TRANS-BOLETA

					(SELECT
						PT.dia AS femision,
						COUNT(DISTINCT PT.caja||PT.trans) AS nuregistrotb,
						SUM(PT.importe) nuimportetb
					FROM
						$postrans PT
					WHERE
						PT.td 		= 'B'
						AND PT.dia 	BETWEEN TO_DATE('$txtnofechaini', 'DD/MM/YYYY') and TO_DATE('$txtnofechafin', 'DD/MM/YYYY')
						$condalmacent
					GROUP BY
						PT.dia
					) PTB ON (PTB.femision = PA.fapertura)

					LEFT JOIN --POS_TRANS-FACTURA

					(SELECT
						PT.dia AS femision,
						COUNT(DISTINCT PT.caja||PT.trans) AS nuregistrotf,
						SUM(PT.importe) nuimportetf
					FROM
						$postrans PT
					WHERE
						PT.td 		= 'F'
						AND PT.dia 	BETWEEN TO_DATE('$txtnofechaini', 'DD/MM/YYYY') and TO_DATE('$txtnofechafin', 'DD/MM/YYYY')
						$condalmacent
					GROUP BY
						PT.dia
					) PTF ON (PTF.femision = PA.fapertura)

					LEFT JOIN --FAC_TA_FACTURA-BOLETA

					(SELECT
						FC.dt_fac_fecha as femision,
						COUNT(FC.*) nuregistrodmb,
						SUM(FC.nu_fac_valortotal) nuimportedmb
					FROM
						fac_ta_factura_cabecera FC
					WHERE
						FC.ch_fac_tipodocumento	= '35'
						AND FC.dt_fac_fecha BETWEEN TO_DATE('$txtnofechaini', 'DD/MM/YYYY') and TO_DATE('$txtnofechafin', 'DD/MM/YYYY')
						$condalmacendm
					GROUP BY
						FC.dt_fac_fecha
					) FCB ON (FCB.femision = PA.fapertura)

					LEFT JOIN --FAC_TA_FACTURA-FACTURA

					(SELECT
						FC.dt_fac_fecha as femision,
						COUNT(FC.*) nuregistrodmf,
						SUM(FC.nu_fac_valortotal) nuimportedmf
					FROM
						fac_ta_factura_cabecera FC
					WHERE
						FC.ch_fac_tipodocumento = '10'
						AND FC.dt_fac_fecha BETWEEN TO_DATE('$txtnofechaini', 'DD/MM/YYYY') and TO_DATE('$txtnofechafin', 'DD/MM/YYYY')
						$condalmacendm
					GROUP BY
						FC.dt_fac_fecha
					) FCF ON (FCF.femision = PA.fapertura)

					LEFT JOIN --FAC_TA_FACTURA-NOTA-CREDITO

					(SELECT
						FC.dt_fac_fecha as femision,
						COUNT(FC.*) nuregistrodmnc,
						SUM(FC.nu_fac_valortotal) nuimportedmnc
					FROM
						fac_ta_factura_cabecera FC
					WHERE
						FC.ch_fac_tipodocumento = '20'
						AND FC.dt_fac_fecha BETWEEN TO_DATE('$txtnofechaini', 'DD/MM/YYYY') and TO_DATE('$txtnofechafin', 'DD/MM/YYYY')
						$condalmacendm
					GROUP BY
						FC.dt_fac_fecha
					) FCNC ON (FCNC.femision = PA.fapertura)

					LEFT JOIN --FAC_TA_FACTURA-NOTA-DEBITO

					(SELECT
						FC.dt_fac_fecha as femision,
						COUNT(FC.*) nuregistrodmnd,
						SUM(FC.nu_fac_valortotal) nuimportedmnd
					FROM
						fac_ta_factura_cabecera FC
					WHERE
						FC.ch_fac_tipodocumento = '11'
						AND FC.dt_fac_fecha BETWEEN TO_DATE('$txtnofechaini', 'DD/MM/YYYY') and TO_DATE('$txtnofechafin', 'DD/MM/YYYY')
						$condalmacendm
					GROUP BY
						FC.dt_fac_fecha
					) FCND ON (FCND.femision = PA.fapertura)

				ORDER BY
					fapertura;
				";

			//echo $sql;
			if ($sqlca->query($sql) <= 0) {
            	throw new Exception("No se encontro ningun registro");
			}
       
			while ($reg = $sqlca->fetchRow()) {
				$registro[] = $reg;
			}

			return $registro;
		}catch(Exception $e){
			throw $e;
		}
	}
}

