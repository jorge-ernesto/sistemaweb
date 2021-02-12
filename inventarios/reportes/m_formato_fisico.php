<?php
class Formato_Fisico_Model extends Model {
	function ObtenerEstaciones() {
		global $sqlca;

		try {
			$sql = " SELECT
							ch_almacen as almacen,
							trim(ch_nombre_almacen) as nombre
						FROM
							inv_ta_almacenes
						WHERE
							ch_clase_almacen='1'
						ORDER BY
							ch_almacen;
					 ";

			if($sqlca->query($sql) <= 0) {
				throw new Exception("Error no se encontro turnos en la fecha indicada");
			}

			while($reg = $sqlca->fetchRow()) {
				$registro[] = $reg;
			}

			return $registro;

		} catch(Exception $e) {
			throw $e;
		}
	}

	function DatosServidorRemoto(){
		global $sqlca;
	
		$sql = " SELECT
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

		if($sqlca->query($sql) <= 0)
			return FALSE;

		if($sqlca->numrows()==1){
			$data = $sqlca->fetchRow();
			return Array(TRUE, $data[0], $data[1], $data[2], $data[3]);
		} else {
			return FALSE;// No existe
		}
	}

	/* se quito WHERE cod_almacen = '$almacen' */

	function Ubicaciones($almacen) {
		global $sqlca;
		
		try{

			$sql = " SELECT
							cod_ubicac codigo,
							desc_ubicac nombre
						FROM
							inv_ta_ubicacion
						WHERE
							cod_almacen = '$almacen'
						GROUP BY
							cod_ubicac,
							desc_ubicac
						ORDER BY
							cod_ubicac;
					 ";

			if($sqlca->query($sql) <= 0) {
				$registro[] = array("codigo"=>"No hay","nombre"=>"Ubicaciones");
			}

			while($reg = $sqlca->fetchRow()) {
				$registro[] = $reg;
			}

			return $registro;

		} catch(Exception $e) {
			throw $e;
		}
	}

	/*
	function UbicacionInventarioServidorRemoto($datasource, $database, $dbuser, $dbpass, $aud, $ubica, $almacen) {
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
	}*/

	function ObtenerReporte($almacen, $ubica, $stk, $orden, $hora) {
		global $sqlca;

		$fec = "SELECT da_fecha FROM pos_aprosys WHERE ch_poscd = 'A';";//Fecha Actual
		$sqlca->query($fec);
		$a = $sqlca->fetchRow();

		$feca = substr($a['da_fecha'],0,4);
		$fecm = substr($a['da_fecha'],5,2);
		$fecd = substr($a['da_fecha'],8,2);

		if($stk == "N") {
			$cond = "AND s.stk_stock".$fecm." != 0";
		}

		if($orden == "C") {
			$ord = "ORDER BY
					a.art_codigo";
		} else {
			$ord = "ORDER BY
					a.art_descripcion";
		}

		$sql = "SELECT par_valor FROM int_parametros WHERE par_nombre = 'lista precio';";

		if($sqlca->query($sql) < 0) 
			return false;

		$a = $sqlca->fetchRow();
		$listaprecio = trim($a[0]);

		$fecmov = $feca . "-" . $fecm . "-" . $fecd;
		$aud = $fecmov . " " . $hora . " - " . $_SESSION['auth_usuario'];

		//TRAER UBICACION DE INVENTARIO DEL SERVIDOR CENTRAL
		/*
		$central = Formato_Fisico_Model::DatosServidorRemoto();

		if($central[0]) {
			$ubicacion = Formato_Fisico_Model::UbicacionInventarioServidorRemoto($central[1], $central[2], $central[3], $central[4], $aud, $ubica, $almacen);
		} else {
			$sql = "
						UPDATE
							inv_ta_ubicacion
						SET
							flg_ubicac	= '1',
							audit		= '".$aud."'
						WHERE
							cod_ubicac 	= '".$ubica."'
							AND cod_almacen = '".$almacen."';
					 ";

			if($sqlca->query($sql) < 0) 
				return false;
		}*/

		try {
			$registro = array();
			$sql = "	SELECT 
							a.art_codigo codigo, 
							a.art_descripcion descripcion, 
							ROUND(p.pre_precio_act1,2) precio,
							ROUND(s.stk_stock".$fecm.",2) stkact,
							ROUND(stk_fisico".$fecm.",2) stkfisico,
							a.art_cod_ubicac ubica
						FROM 
							int_articulos a 
							LEFT JOIN inv_saldoalma s ON (a.art_codigo = s.art_codigo AND stk_periodo = '".$feca."' AND s.stk_almacen = '".$almacen."')
							LEFT JOIN fac_lista_precios p ON (a.art_codigo = p.art_codigo)
						WHERE
							a.art_cod_ubicac 	= '".$ubica."'
							AND pre_lista_precio 	= '".$listaprecio."'
							$cond
							$ord;
					 ";

			if ($sqlca->query($sql) <= 0) {
				throw new Exception("No se encontro ningun registro");
			}
       
			while ($reg = $sqlca->fetchRow()) {
				$registro[] = $reg;
			}

			return $registro;

		} catch(Exception $e) {
			throw $e;
		}
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
