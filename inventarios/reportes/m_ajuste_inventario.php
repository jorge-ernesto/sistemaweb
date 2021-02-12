<?php

class AjusteInventarioModel extends Model {

	function BEGINTransaccion() {
        	global $sqlca;

		try {

			$sql = "BEGIN;";

			if ($sqlca->query($sql) < 0) {
				throw new Exception("No se pudo INICIAR la TRANSACION");
			}

		} catch (Exception $e) {
			throw $e;
		}

	}

	function COMMITransaccion() {
		global $sqlca;

        	try {
	
			$sql = "COMMIT;";

			if ($sqlca->query($sql) < 0) {
				throw new Exception("No se pudo procesar la TRANSACION");
			}

		} catch (Exception $e) {
			throw $e;
		}

	}

	function ROLLBACKTransaccion() {
		global $sqlca;

		try {

			$sql = "ROLLBACK;";

			if ($sqlca->query($sql) < 0) {
				throw new Exception("No se pudo Retroceder el proceso.");
			}

		} catch (Exception $e) {
			throw $e;
		}
	}

	function DatosServidorRemoto(){
		global $sqlca;
	
		$sql = "
			SELECT
				p1.par_valor,
				p2.par_valor,
				p3.par_valor,
				p4.par_valor
			FROM
				int_parametros p1
				LEFT JOIN int_parametros p2 ON p2.par_nombre = 'central_db'
				LEFT JOIN int_parametros p3 ON p3.par_nombre = 'central_user'
				LEFT JOIN int_parametros p4 ON p4.par_nombre = 'central_password'
			WHERE
				p1.par_nombre = 'central_ip';
		";

		if ($sqlca->query($sql) <= 0)
			return FALSE;

		if($sqlca->numrows()==1){
			$data = $sqlca->fetchRow();
			return Array(TRUE, $data[0], $data[1], $data[2], $data[3]);
		}else{
			return FALSE;// No existe
		}

	}

	/*
	function UbicacionInventarioServidorRemoto($datasource, $database, $dbuser, $dbpass, $aud, $ubica, $almacen){
		$cadenaConexion = 'host='.$datasource.' user='.$dbuser.' password='.$dbpass.' port=5432 dbname='.$database;
		$conexion = pg_connect($cadenaConexion) or die("Error en la Conexión: ".pg_last_error());
		//echo "<h3>Conexion Exitosa - Energigas Central</h3><hr><br>";
		$sql = "
		UPDATE
			inv_ta_ubicacion
		SET
			flg_ubicac = '1',
			audit = '" . $aud . "'
		WHERE
			cod_ubicac = '" . $ubica . "'
			AND cod_almacen = '" . $almacen . "';
		";
		$xsql = pg_exec($conexion,$sql);
		return true;
	}
*/
	function Almacenes() {
		global $sqlca;
		
		$sql = "
			SELECT
				ch_almacen,
				ch_almacen||' - '||ch_nombre_almacen
			FROM
				inv_ta_almacenes
			WHERE
				ch_clase_almacen = '1'
			ORDER BY
				ch_almacen;
			";
	
		if ($sqlca->query($sql) < 0) 
			return false;
	
		$result = Array();
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    	$a = $sqlca->fetchRow();		    
		    	$result[$a[0]] = $a[1];
		}
	
		return $result;
    	}

	function obtenerAlmacenes($alm) {
		global $sqlca;
		
		$sql = "
			SELECT
				ch_almacen,
				ch_almacen||' - '||ch_nombre_almacen
			FROM
				inv_ta_almacenes
			WHERE
				ch_clase_almacen	= '1'
				AND ch_almacen		= '$alm'
			ORDER BY
				ch_almacen;
			";
	
		if ($sqlca->query($sql) < 0) 
			return false;
	
		$result = Array();
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    	$a = $sqlca->fetchRow();		    
		    	$result[$a[0]] = $a[1];
		}
	
		return $result;
    	}

    /*AND flg_ubicac	= '1' quitado para conexion remota 
		WHERE
			cod_almacen 	= '" . $almacen . "'*/

	function Ubicaciones($almacen) {
		global $sqlca;

		$sql = "
		SELECT
			cod_ubicac codigo,
			desc_ubicac nombre
		FROM
			inv_ta_ubicacion
		WHERE
			cod_almacen = '" . $almacen . "'
		GROUP BY
			cod_ubicac,
			desc_ubicac
		ORDER BY
			cod_ubicac;
		";

		if ($sqlca->query($sql) <= 0)
			return false;

		$result = array();

		for ($i=0;$i<$sqlca->numrows();$i++) {
			$a = $sqlca->fetchRow();
			$result[$a[0]] = $a[0] . " - " . $a[1];
		}

		return $result;
	}

	/*
	function UbicacionesRemoto($datasource, $database, $dbuser, $dbpass, $almacen){
		$cadenaConexion = 'host='.$datasource.' user='.$dbuser.' password='.$dbpass.' port=5432 dbname='.$database;
		$conexion = pg_connect($cadenaConexion) or die("Error en la Conexión: ".pg_last_error());
		//echo "<h3>Conexion Exitosa - Energigas Central</h3><hr><br>";
		$sql = "
		SELECT
			cod_ubicac codigo,
			desc_ubicac nombre
		FROM
			inv_ta_ubicacion
		WHERE
			cod_almacen 	= '" . $almacen . "'
			AND flg_ubicac	= '1'
		ORDER BY
			cod_ubicac;
		";

		$xsql = pg_exec($conexion,$sql);
		$ilimitp = pg_numrows($xsql);
		$result = array();
		$irowp = 1;
		while($irowp < $ilimitp) {
			$codigo = pg_result($xsql,$irowp,0);
			$nombre = pg_result($xsql,$irowp,1);
			$result[$codigo] = $codigo . " - " . $nombre;
			++$irowp;
		}
		return $result;
	}
	*/
	function ObtenerUbicaciones($almacen, $ubica) {
		global $sqlca;

		$sql = "
		SELECT
			cod_ubicac codigo,
			desc_ubicac nombre
		FROM
			inv_ta_ubicacion
		WHERE
			cod_almacen = '" . $almacen . "'
		AND cod_ubicac	= '" . $ubica . "';
		";

		if ($sqlca->query($sql) <= 0)
			return false;

		$result = array();

		for ($i=0;$i<$sqlca->numrows();$i++) {
			$a = $sqlca->fetchRow();
			$result[$a[0]] = $a[0] . " - " . $a[1];
		}

		return $result;
	}

	function buscar($almacen, $ubica, $orden) {
		global $sqlca;

		$fec = "SELECT da_fecha FROM pos_aprosys WHERE ch_poscd = 'A';";//Fecha Actual

		$sqlca->query($fec);
		$a = $sqlca->fetchRow();

		$feca = substr($a['da_fecha'],0,4);
		$fecm = substr($a['da_fecha'],5,2);
		$fecd = substr($a['da_fecha'],8,2);

		if($orden == "C"){
			$ord = "ORDER BY
					a.art_codigo";
		}else{
			$ord = "ORDER BY
					a.art_descripcion";
		}

		$sql = "SELECT
				trim(a.art_codigo) codigo, 
				a.art_descripcion producto, 
				round(s.stk_stock".$fecm.",2) stkact, 
				round(stk_fisico".$fecm.",2) stkfisico
			FROM 
				int_articulos a 
				LEFT JOIN inv_saldoalma s ON (a.art_codigo = s.art_codigo AND stk_periodo = '".$feca."' AND s.stk_almacen = '".$almacen."')
			WHERE
				a.art_cod_ubicac = '".$ubica."'
				$ord
			;";

		if ($sqlca->query($sql) <= 0)
			return false;

		$resultado = Array();
		
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();

			$resultado[$i]['codigo'] 	= $a[0];
			$resultado[$i]['producto'] 	= $a[1];
			$resultado[$i]['stkact'] 	= $a[2];
			$resultado[$i]['stkfisico'] 	= $a[3];

		}

		return $resultado;

	}

	function ProcesarAjustes($almacen, $producto, $stkfisico, $hora, $ubica){
		global $sqlca;

		$sql = "
SELECT
 tran_nform AS nro,
 TRIM(tran_naturaleza),
 tran_origen AS origen,
 tran_destino AS destino
FROM 
 inv_tipotransa 
WHERE
 tran_codigo = '17';
		";

		$sqlca->query($sql);

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();

			$resultado[$i]['nro']		= $a[0];
			$resultado[$i]['origen']	= $a[2];

			if(strlen($resultado[$i]['nro']) < 10){
				$rs	= ("0".$resultado[$i]['nro']) + 1;
				$upd = "UPDATE inv_tipotransa SET tran_nform = '". $rs . "' WHERE tran_codigo = '17';";
				if($sqlca->query($upd) < 0)
					return false;
				$nromov = $almacen . AjusteInventarioModel::completarCeros($rs,7,"0");//Numero de Formulario
				$origen	= $resultado[$i]['origen'];//Almacen origen
			}			
		}

		$fec = "SELECT da_fecha FROM pos_aprosys WHERE ch_poscd = 'A' ORDER BY da_fecha DESC LIMIT 1;";//Fecha Actual

		$sqlca->query($fec);
		$a = $sqlca->fetchRow();

		$feca = substr($a['da_fecha'],0,4);
		$fecm = substr($a['da_fecha'],5,2);
		$fecd = substr($a['da_fecha'],8,2);

		$fecmov = $feca."-".$fecm."-".$fecd;

		for ($i=0;$i<count($stkfisico);$i++) {

			/* COSTO DEL PRODUCTO */

			$precio = "
					SELECT
						art_costoreposicion
					FROM
						int_articulos
					WHERE
						art_codigo	= '{$producto[$i]}';
				";

			$sqlca->query($precio);
	
			$p = $sqlca->fetchRow();

			if($p[0] == NULL){
				$costo = 0.00;
				$total = 0.00;
			}else{
				$costo = $p[0];
				$total = $costo * $stkfisico[$i];
			}

			/* STOCK DEL PRODUCTO ACTUAL */

			$stocka = "
					SELECT
						s.stk_stock".$fecm."
					FROM 
						int_articulos a 
						LEFT JOIN inv_saldoalma s ON (a.art_codigo = s.art_codigo AND stk_periodo = '".$feca."' AND s.stk_almacen = '$almacen')
					WHERE
						a.art_codigo = '{$producto[$i]}'
						AND a.art_cod_ubicac = '$ubica';
				";

			$sqlca->query($stocka);
	
			$s = $sqlca->fetchRow();

			if($s[0] == NULL){
				$stocka = 0;
			}else{
				$stocka = $s[0];
			}

			$stock = $stkfisico[$i] - $stocka;

			if( (($stkfisico[$i] == '0' || $stkfisico[$i] == 0) && ($stocka == '0' || $stocka == 0)) ){

			}else{

				$ins = "
					INSERT INTO
						inv_movialma(
								mov_numero,
								tran_codigo,
								art_codigo,
								mov_fecha,
								mov_almacen,
								mov_almaorigen,
								mov_naturaleza,
								mov_cantidad,
								mov_costounitario,
								mov_costopromedio,
								mov_costototal
						)VALUES(
								'$nromov',
								'17',
								'{$producto[$i]}',
								'$fecmov',
								'$almacen',
								'$origen',
								'1',
								'$stock',
								$costo,
								$costo,
								$total
						);

					";

				if($sqlca->query($ins) < 0)
					return false;

			}

		}

		$fecmov = $fecd."/".$fecm."/".$feca;

		$aud = $fecmov." ".$hora."-".$_SESSION['auth_usuario'];

		//TRAER UBICACION DE INVENTARIO DEL SERVIDOR CENTRAL
		/*
		$central = AjusteInventarioModel::DatosServidorRemoto();

		if($central[0]){
			$ubicacion = AjusteInventarioModel::UbicacionInventarioServidorRemoto($central[1], $central[2], $central[3], $central[4], $aud, $ubica, $almacen);
		}else{

			$upd = "
				UPDATE
					inv_ta_ubicacion
				SET
					flg_ubicac	= '0',
					audit		= '".$aud."'
				WHERE
					cod_ubicac	= '".$ubica."'
					AND cod_almacen	= '".$almacen."';
				";

		}

		if($sqlca->query($upd) < 0)
			return false;
		*/
		return true;
		
	}

	function completarCeros($cadena, $long_final, $complemento){

		$long_inicial = strlen($cadena);

		for($i = 0; $i < $long_final - $long_inicial; $i++){
			$cadena = $complemento.$cadena ;
		}

		return $cadena;

	}

	function Reporte($almacen, $producto, $ubica){
		global $sqlca;

		$fec = "SELECT da_fecha FROM pos_aprosys WHERE ch_poscd = 'A';";//Fecha Actual

		$sqlca->query($fec);
		$a = $sqlca->fetchRow();

		$feca = substr($a['da_fecha'],0,4);
		$fecm = substr($a['da_fecha'],5,2);
		$fecd = substr($a['da_fecha'],8,2);

		$fecmov = $feca."-".$fecm."-".$fecd;

		$producto = str_replace(",","','",$producto);

		$query = "SELECT par_valor FROM int_parametros WHERE par_nombre = 'lista precio';";

		if ($sqlca->query($query) < 0) 
			return false;

		$a 		= $sqlca->fetchRow();
		$listaprecio 	= trim($a[0]); 
		
		$sql = "
			SELECT
				TRIM(a.art_codigo) codigo,
				TRIM(a.art_descripcion) nombre,
				p.pre_precio_act1 precio,
				s.stk_stock".$fecm." stkact,
				(CASE WHEN s.stk_stock".$fecm." IS NULL THEN 0.00 ELSE s.stk_stock".$fecm." END) - k.mov_cantidad varia,
				k.mov_cantidad stock,
				s.stk_costo".$fecm." costo,
				(k.mov_cantidad * p.pre_precio_act1) dife
			FROM 
				int_articulos a 
				LEFT JOIN inv_saldoalma s ON (a.art_codigo = s.art_codigo AND stk_periodo='".$feca."' AND s.stk_almacen = '$almacen')
				LEFT JOIN inv_movialma k ON (k.art_codigo = a.art_codigo AND k.mov_almacen = '$almacen')
				LEFT JOIN fac_lista_precios p ON (p.art_codigo = a.art_codigo)
			WHERE
				a.art_codigo IN('$producto')
				AND a.art_cod_ubicac 	= '".$ubica."'
				AND p.pre_lista_precio 	= '".$listaprecio."'
				AND k.tran_codigo 	= '17'
				AND k.mov_fecha		= '".$fecmov."'
				AND TO_CHAR(k.mov_fecha_actualizacion, 'DD/MM/YYYY HH24:MI:SS') >= (SELECT TO_CHAR(MAX(mov_fecha_actualizacion), 'DD/MM/YYYY HH24:MI:SS') FROM inv_movialma WHERE tran_codigo = '17')
			ORDER BY
				a.art_descripcion;
			";

		if ($sqlca->query($sql) < 0)
			return false;

		$resultado = Array();
		
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();

			$resultado[$i]['codigo'] 	= $a[0];
			$resultado[$i]['nombre'] 	= $a[1];
			$resultado[$i]['precio'] 	= $a[2];
			$resultado[$i]['stkact'] 	= $a[3];
			$resultado[$i]['varia'] 	= $a[4];
			$resultado[$i]['stock'] 	= $a[5];
			$resultado[$i]['costo'] 	= $a[6];
			$resultado[$i]['dife'] 		= $a[7];

		}

		return $resultado;
	}

	function FechaSistema(){
		global $sqlca;

		$fecha = "
			SELECT
				to_char(da_fecha,'DD/MM/YYYY')
			FROM
				pos_aprosys
			WHERE
				ch_poscd = 'A';
			";

		if($sqlca->query($fecha) < 0)
			return false;

		$resultado = array();
	
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    	$a = $sqlca->fetchRow();
		    	$resultado['fecha'] = $a[0];
		}
		
		return $resultado;

	}


}
