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

	public function get_status_balance_regeneration() {
		global $sqlca;

		$iStatusTable = $sqlca->query("SELECT 1 FROM information_schema.tables WHERE table_schema = 'public' AND table_name = 'tmp_item'");
		$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'info', 'sMessage' => 'No hay ningún proceso de regeneración de saldos');
		if ((int)$iStatusTable == 1){ //Existe tabla
			//Debe de haber al menos un item en estado procesando
			$iStatus = $sqlca->query("SELECT COUNT(*) AS nu_cantidad FROM tmp_item WHERE art_modifica_articulo=1;");
			$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'info', 'sMessage' => 'No hay ningún proceso de regeneración de saldos');
			if ( (int)$iStatus > 0 ) {// Hay item(s) en estado procesando
				$iStatus = $sqlca->query("SELECT COUNT(*) AS nu_cantidad FROM int_articulos WHERE art_plutipo IN('1', '4') AND art_estado='0';");
				$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'danger', 'sMessage' => 'Problemas al buscar artículos (SQL)');
				if ((int)$iStatus == 0) {
		        	$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'warning', 'sMessage' => 'No hay ningún item', 'iCantidadTotalItem' => 0);
				} else {
					$row = $sqlca->fetchRow();
					$iCantidadTotalItem = $row['nu_cantidad'];
					$arrResponse = array('iStatus' => 1, 'sStatus' => 'success', 'sMessage' => 'Ejecutando proceso de regeneración', 'iCantidadTotalItem' => $iCantidadTotalItem);
				}
			}
		}
		return $arrResponse;
	}

	public function execute_balance_regeneration($arrPOST) {
		global $sqlca;

		$iStatus = $sqlca->query("SELECT COUNT(*) AS nu_cantidad FROM int_articulos WHERE art_plutipo IN('1', '4') AND art_estado='0';");//art_estado=0 > Activo
		$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'danger', 'sMessage' => 'Problemas al buscar artículos (SQL)');
		if ((int)$iStatus == 0) {
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

		$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'warning', 'sMessage' => 'No hay ningún item procesado', 'iCantidadItemProcesado' => 0);
		$iStatusTable = $sqlca->query("SELECT 1 FROM information_schema.tables WHERE table_schema='public' AND table_name='tmp_item'");
		if ((int)$iStatusTable == 1){ //Existe tabla
			//1 = item procesado
			$iStatus = $sqlca->query("SELECT COUNT(*) AS nu_cantidad FROM tmp_item WHERE art_modifica_articulo=1;");
			$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'danger', 'sMessage' => 'Problemas al buscar items procesados (SQL)');
			if ( (int)$iStatus > 0 ) {// Si hay registros
				$row = $sqlca->fetchRow();
				$iCantidadItemProcesado = $row['nu_cantidad'];
				$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'success', 'sMessage' => 'cantidad de items procesados', 'iCantidadItemProcesado' => $iCantidadItemProcesado);
			}
		}
		return $arrResponse;	
	}

	public function stop_process_balance() {
		global $sqlca;
		
		$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'success', 'sMessage' => 'El proceso de regeneración de saldos ha culminado');
		$iStatusTable = $sqlca->query("SELECT 1 FROM information_schema.tables WHERE table_schema = 'public' AND table_name = 'tmp_item'");
		if ((int)$iStatusTable == 1){ //Existe tabla
			$iStatus = $sqlca->query("DROP TABLE tmp_item;");
			if ( (int)$iStatus < 0 ) {
				$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'danger', 'sMessage' => 'Problemas al limpiar items procesados (SQL)');
			}
		}
		return $arrResponse;	
	}

	public function text_clean($str) {
		return strip_tags(stripslashes($str));
	}
}

