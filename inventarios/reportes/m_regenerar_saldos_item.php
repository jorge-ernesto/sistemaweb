<?php
// Descomentar estas líneas, cuando estamos en modo - development
/*
error_reporting(-1);
ini_set('display_errors', 1);
*/
// Descomentar estas líneas, cuando estamos en modo - production

ini_set('display_errors', 0);
if (version_compare(PHP_VERSION, '5.3', '>='))
{
	error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
}
else
{
	error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE);
}


class Regenerar_Saldos_Model extends Model {

    public function ObtenerEstaciones() {
		global $sqlca;
	
		try {
			$sql = "
				SELECT
					ch_almacen as almacen,
					trim(ch_nombre_almacen) as nombre
				FROM
					inv_ta_almacenes
				WHERE
					ch_clase_almacen = '1'
				ORDER BY
					ch_almacen;
				";

			if($sqlca->query($sql) <= 0){
				throw new Exception("Error no se encontro turnos en la fecha indicada");
			}

			while($reg = $sqlca->fetchRow()){
				$registro[] = $reg;
			}

			return $registro;

		}catch(Exception $e){
			throw $e;
		}
    }

    public function CierresInventario() {
		global $sqlca;
	
		try {
			$sql = "
			SELECT
				a.par_valor anio,
				m.par_valor mes
			FROM
				int_parametros a,
				int_parametros m
			WHERE
				a.par_nombre = 'inv_ano_cierre'
				AND m.par_nombre = 'inv_mes_cierre';
			";

			if($sqlca->query($sql) <= 0){
				throw new Exception("Error no se encontro Cierre de Inventario");
			}

			while($reg = $sqlca->fetchRow()){
				$registro[] = $reg;
			}
			return $registro;
		}catch(Exception $e){
			throw $e;
		}
    }

	public function execute_balance_regeneration($arrPOST) {
		global $sqlca;

		$iStatus = $sqlca->query("SELECT COUNT(*) AS nu_cantidad FROM int_articulos WHERE art_plutipo IN('1', '4');");

		$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'danger', 'sMessage' => 'Problemas al buscar artículos (SQL)');
		if ($iStatus === 0) {
        	$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'warning', 'sMessage' => 'No hay ningún item', 'iCantidadTotalItem' => 0);
		} else {
			// Limpiar valores $_POST
			$iWarehouse = Regenerar_Saldos_Model::text_clean($arrPOST["iWarehouse"]);
			$iYear = Regenerar_Saldos_Model::text_clean($arrPOST["iYear"]);
			$iMonth = Regenerar_Saldos_Model::text_clean($arrPOST["iMonth"]);

			$row = $sqlca->fetchRow();
			$iCantidadTotalItem = $row['nu_cantidad'];
			$arrResponse = array('iStatus' => 1, 'sStatus' => 'success', 'sMessage' => 'Ejecutando proceso de regeneración', 'iCantidadTotalItem' => $iCantidadTotalItem);
	        exec("php -f cron_regenerar_saldos.php -- " . escapeshellarg($iMonth) . " " . escapeshellarg($iYear) . " " . escapeshellarg($iWarehouse) . " > /dev/null &");
		}
		return $arrResponse;
	}

	public function verify_status_process_balance_item() {
		global $sqlca;
		//10 = procesado
		$iStatus = $sqlca->query("SELECT COUNT(*) AS nu_cantidad FROM int_articulos WHERE art_plutipo IN('1', '4') AND art_modifica_articulo=10;");
		$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'danger', 'sMessage' => 'Problemas al buscar items procesados (SQL)');
		if ($iStatus == 0) {//No hay registros
        	$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'warning', 'sMessage' => 'No hay ningún item procesado', 'iCantidadItemProcesado' => 0);
		} else if ( $iStatus > 0 ) {// Si hay registros
			$row = $sqlca->fetchRow();
			$iCantidadItemProcesado = $row['nu_cantidad'];
			$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'success', 'sMessage' => 'cantidad de items procesados', 'iCantidadItemProcesado' => $iCantidadItemProcesado);
		}
		return $arrResponse;	
	}

	public function stop_process_balance() {
		global $sqlca;
		//0 = finalizado
		$iStatus = $sqlca->query("UPDATE int_articulos SET art_modifica_articulo=0 WHERE art_plutipo IN('1', '4');");
		$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'danger', 'sMessage' => 'Problemas al actualizar items procesados (SQL)');
		if ( $iStatus >= 0 ) {
			$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'success', 'sMessage' => 'El proceso de regeneración de saldos ha culminado');
		}
		return $arrResponse;	
	}

	public function text_clean($str) {
		return strip_tags(stripslashes($str));
	}
}

