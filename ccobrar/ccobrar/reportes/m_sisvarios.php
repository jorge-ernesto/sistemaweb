<?php

class VariosModel extends Model {

	function noSiCBArray() {
		return array("N"=>"No", "S"=>"Si");
	}

	function HeaderingExcel($filename) {
		header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename=$filename" );
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
		header("Pragma: public");
	}
 
	function almacenCBArray() {
		global $sqlca;

		$query = "SELECT ch_almacen, ch_almacen||' '||ch_nombre_breve_almacen FROM inv_ta_almacenes WHERE ch_clase_almacen='1' ORDER BY 1";
		$cbArray = array();
    
		if ($sqlca->query($query) <= 0)
			return $cbArray;

		while($result = $sqlca->fetchRow()){
			$cbArray[trim($result[0])] = $result[1];
		}
		return $cbArray;
	}

	function TipTransaccionCBArray() {
		global $sqlca;

		$query = "SELECT tran_codigo, tran_codigo||' '||tran_descripcion FROM inv_tipotransa ORDER BY 1";
		$cbArray = array();

		if ($sqlca->query($query) <= 0)
			return $cbArray;

		while($result = $sqlca->fetchRow()){
			$cbArray[trim($result[0])] = $result[1];
		}
		return $cbArray;
	}

	function sucursalCBArray() {
		global $sqlca;

		$query = "SELECT DISTINCT ch_sucursal, ch_sucursal||' '||ch_nombre_breve_sucursal FROM int_ta_sucursales ORDER BY ch_sucursal";
		$cbArray = array();
		$cbArray['all']='[ Todas ]';

		if ($sqlca->query($query) <= 0)
			return $cbArray;

		while($result = $sqlca->fetchRow()){
			$cbArray[trim($result[0])] = $result[1];
		}

		return $cbArray;
	}

	function sucursalCBArray2() {
		global $sqlca;

		$query = "SELECT ch_sucursal, ch_sucursal||' '||ch_nombre_breve_sucursal FROM int_ta_sucursales ORDER BY ch_sucursal";
		$cbArray = array();

		if ($sqlca->query($query) <= 0)
			return $cbArray;

		while($result = $sqlca->fetchRow()) {
			$cbArray[trim($result[0])] = $result[1];
		}
		$cbArray['all']='[ Todas ]';

		return $cbArray;
	}
  
	function tanquesCBArray($cod = "002") {
		global $sqlca;

		$query = "SELECT DISTINCT 
				a.ch_tanque,
				a.ch_tanque||' '||b.ch_nombrecombustible
	      		FROM  
				comb_ta_tanques a,
				comb_ta_combustibles b
			WHERE 
				a.ch_codigocombustible=b.ch_codigocombustible 
				AND a.ch_codigocombustible=b.ch_codigocombustible 
				AND a.ch_sucursal='".$cod."'";

		$cbArray = array();
		$cbArray['all']='[ Todos ]';

		if ($sqlca->query($query) <= 0)
			return $cbArray;

		while($result = $sqlca->fetchRow()) {
			$cbArray[trim($result[0])] = $result[1];
		}

		return $cbArray;
	}
  
	function tanquesCodCBArray($cod = "002") {
		global $sqlca;

		$query = "SELECT DISTINCT 
				a.ch_tanque,
				a.ch_tanque 
			FROM  
				comb_ta_tanques a,
				comb_ta_combustibles b
			WHERE 
				a.ch_codigocombustible=b.ch_codigocombustible 
				AND a.ch_codigocombustible=b.ch_codigocombustible 
				AND a.ch_sucursal='".$cod."'";

		$cbArray = array();

		if ($sqlca->query($query) <= 0)
			return $cbArray;

		while($result = $sqlca->fetchRow()) {
			$cbArray[trim($result[0])] = $result[1];
		}

		return $cbArray;
	}

	function CodtanquesCBArray() {
		$cbArray = array();
		$cbArray['01'] = '11620301';
		$cbArray['02'] = '11620305';
		$cbArray['03'] = '11620304';
		$cbArray['04'] = '11620306';
		$cbArray['05'] = '11620302';
		$cbArray['06'] = '11620303';
		$cbArray['07'] = '11620307';

		return $cbArray;
	}

	function MoviAlmacenCBArray($cdAlm="", $cdCom="", $fi, $ff, $ct, $tcod) {
		global $sqlca;

		$query = "SELECT 
				mov_almacen,
				to_char(mov_fecha,'DD-MM-YYYY') AS fecha,
				sum(mov_cantidad) AS compra 
			FROM 
				inv_movialma 
			WHERE 
				tran_codigo='".$tcod."'
				".@$cdAlm."
				".@$cdCom."
				AND to_date(to_char(mov_fecha,'DD-MM-YYYY'),'DD-MM-YYYY') >= to_date('$fi','dd-mm-yyyy')
				AND to_date(to_char(mov_fecha,'DD-MM-YYYY'),'DD-MM-YYYY') <= to_date('$ff','dd-mm-yyyy')
				GROUP BY fecha,mov_almacen";

		if ($sqlca->query($query) <= 0)
			return $cbArray;

		while($result = $sqlca->fetchRow()) {
			$cbArray[$ct][$result[0]][$result[1]] = $result[2];
		}  

		return $cbArray;
	}

	function MoviAlmacenVarTCBArray($cdAlm="", $cdCom="", $fi, $ff, $ct, $tcod1, $tcod2) {
		global $sqlca;

		$query = "SELECT 
				mov_almacen,
				to_char(mov_fecha,'DD-MM-YYYY') AS fecha,
				sum(mov_cantidad) AS compra 
			FROM 
				inv_movialma 
			WHERE 
				(tran_codigo='".$tcod1."' or
				tran_codigo='".$tcod2."')
				".@$cdAlm."
				".@$cdCom."
				AND to_date(to_char(mov_fecha,'DD-MM-YYYY'),'DD-MM-YYYY') >= to_date('$fi','dd-mm-yyyy')
				AND to_date(to_char(mov_fecha,'DD-MM-YYYY'),'DD-MM-YYYY') <= to_date('$ff','dd-mm-yyyy')
				GROUP BY fecha,mov_almacen";

		if ($sqlca->query($query) <= 0)
			return $cbArray;

		while($result = $sqlca->fetchRow()) {
			$cbArray[$ct][$result[0]][$result[1]] = $result[2];
		}  

		return $cbArray;
	}
  
	function ListaGeneral($cod='', $valDef = array(), $CodDes = false) {
		global $sqlca;

		if($CodDes == true) 
			$addQuery = " trim(tab_elemento)||' '||trim(tab_descripcion)"; 
		else 
			$addQuery = " tab_descripcion"; 
    
		$query = "SELECT 
				tab_elemento, 
				".$addQuery."
			FROM 
				int_tabla_general
			WHERE 
				tab_tabla = '".$cod."' 
				AND tab_elemento!='000000' 
			ORDER BY 1";

		$cbArray = array();
		if(!empty($valDef)) 
			$cbArray[''.$valDef['value'].'']=''.$valDef['descripcion'].'';

		if ($sqlca->query($query) <= 0)
			return $cbArray;

		while($result = $sqlca->fetchRow()) {
			$cbArray[trim($result[0])] = $result[1];
		}

		return $cbArray;
	}
  
	function MarcasItemsCBArray($cod = "23") {
		global $sqlca;

		$query = "SELECT tab_elemento, tab_descripcion FROM int_tabla_general WHERE tab_tabla = '$cod' ORDER BY tab_descripcion";
		$cbArray = array();
		$cbArray['all']='[ Seleccionar ]';

		if ($sqlca->query($query) <= 0)
			return $cbArray;

		while($result = $sqlca->fetchRow()) {
			$cbArray[trim($result[0])] = $result[1];
		}

		return $cbArray;
	}

	function InicializarVariables($TipoDoc, $CodAlm) {
		global $sqlca;

		$query = "SELECT
				tran_codigo,
				tran_descripcion,
				trim(tran_naturaleza) as tran_naturaleza, 
				tran_valor, 
				tran_entidad, 
				tran_referencia, 
				tran_origen, 
				tran_destino, 
				tran_nform 
			FROM 
				inv_tipotransa
			WHERE 
				tran_codigo = '".$TipoDoc."'";

		$DataArray = array();

		if($sqlca->query($query) <= 0)
			return $DataArray;

		while($result = $sqlca->fetchRow()) {
			$DataArray[] = $result;
		}
    
		$ValoresArray["Descripcion"]    = $DataArray[0][1];
		$ValoresArray["Naturaleza"]     = $DataArray[0][2];
		$ValoresArray["Valor"]          = $DataArray[0][3];
		$ValoresArray["Entidad"]        = $DataArray[0][4];
		$ValoresArray["Referencia"]     = $DataArray[0][5];
		$ValoresArray["Origen"]         = $DataArray[0][6];
		$ValoresArray["Destino"]        = $DataArray[0][7];
		    
		$nro_mov = $DataArray[0][8]+1;
		$nro_mov = VariosModel::CompletarCeros($nro_mov,7,"0");
		$nro_mov = $CodAlm.$nro_mov;
		$ValoresArray["Nro_Movimiento"] = $nro_mov;

		return $ValoresArray;
	}
  
	function CompletarCeros($cadena, $long_final, $complemento) {
		$long_inicial = strlen($cadena);
		for($i = 0; $i < $long_final - $long_inicial; $i++) {
			$cadena = $complemento.$cadena ;
		}

		return $cadena;
	}

	function mesesCBArray() {
		$cbArray = array();
		$cbArray['1'] = 'ENERO';
		$cbArray['2'] = 'FEBRERO';
		$cbArray['3'] = 'MARZO';
		$cbArray['4'] = 'ABRIL';
		$cbArray['5'] = 'MAYO';
		$cbArray['6'] = 'JUNIO';
		$cbArray['7'] = 'JULIO';
		$cbArray['8'] = 'AGOSTO';
		$cbArray['9'] = 'SEPTIEMBRE';
		$cbArray['10'] = 'OCTUBRE';
		$cbArray['11'] = 'NOVIEMBRE';
		$cbArray['12'] = 'DICIEMBRE';
    
		return $cbArray;
	}

	function diasCBArray() {
		$cbArray= array();
		$dia    = date ("d"); 
		$mes    = date ("m"); 
		$anio   = date ("Y"); 
		$ultimo_dia = 28; //  Último Día en el caso del mes de Febrero y Años Bisiestos    
		//@@ Calculo para obtener el ùltimo dìa del mes actual con los años bisiestos 
		while(checkdate($mes, $ultimo_dia + 1, $anio)) {
			$ultimo_dia++;
		}

		$ultimo_dia = 31;

		for($i=1; $i <= $ultimo_dia; $i++) {
			if($i < 10) {
				$label = '0'.$i; //@@ Concatenar los valores 1 - 9 con 0
			} else {
				$label = $i;
			}        
			$cbArray[$label] = $label;	
		}

		return $cbArray;
	}
  
	function aniosCBArray() {
		$cbArray = array();
		$anio_actual   = date ("Y");
		for($i = $anio_actual-3; $i <= $anio_actual; $i++) {
			$cbArray[$i] = $i;
		}
		return $cbArray;
	}
  
	function tipoCambioLibre($fecha) {
		global $sqlca;

		$query = "SELECT tca_venta_libre FROM int_tipo_cambio WHERE tca_moneda='02' AND tca_fecha='".$fecha."'";
		$registro = $sqlca->firstRow($query);
		return $registro[0];
	}
  
	function diaactual() {
		global $sqlca;
		$query = "SELECT util_fn_fechaactual_aprosys();";
		$registro = $sqlca->firstRow($query);
		return $registro[0];
	}

	function ObtIgv() {
		global $sqlca;
		$Monto = $sqlca->functionDB("round((util_fn_igv()/100),4)");
		return $Monto;
	}
  
	function ObtCodIgv() {
		global $sqlca;
		$Monto = $sqlca->functionDB("substring(util_fn_cd_igv() for 2 from length(util_fn_cd_igv())-1)");
		return $Monto;
	}
  
	function ObtSigCodigo($SeqNombre) {
		global $sqlca;
    
		$query = "SELECT nextval('".$SeqNombre."')";
		$registro = $sqlca->firstRow($query);
		if(!$registro) 
			return "ERROR: CODIGO NO GENERADO"; 
		return $registro[0];
	}
  
	function tipoCambioCompra($fecha) {
		$tipo = '3.22';
		return $tipo;
	}

	function tipoCambioVenta($fecha) {
		$tipo = '3.30';
		return $tipo;
	}
  
/*	function ObtenerIPAlmacen($CodAlmancen) {
		global $sqlcabk;

		$query = "SELECT ip FROM int_estaciones WHERE lpad(id, 3, '0') = '".$CodAlmancen."'";
		$registro = $sqlcabk->firstRow($query);

		if(!$registro)
			return "ERROR: NO EXISTE ALMACEN"; 

		return $registro[0];
	}

	function SentenciasReplicacion($Sentencia, $Datos) {
		global $usuario, $sqlcabk;

		$Sentencia      = str_replace("'", "\'", $Sentencia);
		$Estado         = "completo";
		$CodUsuario     = $usuario->obtenerUsuario();
		$Ip_Destino     = $Datos['Ip_Estacion'];
		$Ip_Origen      = "128.1.2.170";
		$Cod_Estacion   = substr($Datos['Cod_Estacion'], 1, 2);
		$Sistema_Id     = $usuario->obtenerSistemaActual();

		$query = "INSERT INTO 
                         	replicacion_sentencias 	( 
                          	id,
                          	query, 
                          	estado, 
                          	codusuario, 
                   		ip_destino, 
                        	ip_origen, 
                        	cod_estacion, 
                        	sistema_id, 
                          	fecha_creado 
                          	) 
                          VALUES ( 
		               	nextval('s_replicacion_sentencias'), 
		                '".$Sentencia."', 
		           	'".$Estado."', 
		                '".$CodUsuario."', 
		                '".trim($Ip_Destino)."', 
		                '".trim($Ip_Origen)."', 
		                '".$Cod_Estacion."', 
		                '".$Sistema_Id."', 
		                now() 
			)";

		$Result = $sqlcabk->query($query);

		$query = "	INSERT INTO 
                            		cola 
				VALUES (
				        '".$Cod_Estacion."', 
				        '".trim($Ip_Origen)."',
				        '".trim($Ip_Destino)."', 
				        'sentencia_ready', 
				        '".$Sentencia."', 
				        true, 
				        now(),
				        now(),
				        'ENVIO', 
				        now(), 
				        '0', 
				        false, 
				        nextval('s_secuencia_ejecucion') 
				        )";
		$Result = $sqlcabk->query($query);
		return $Result;
	}*/
}
