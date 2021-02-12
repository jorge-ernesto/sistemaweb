<?php
class OrdenCompraModel extends Model {

  function ModelReportePDF($fecha,$fecha2,$almacen,$pendiente,$inventario,$procesando,$facturado,$cerrado){
    global $sqlca;
   	
	$query =	"	SELECT 		C.com_cab_numorden as orden
	,	A.ch_nombre_almacen as almacen 
	,	C.pro_codigo as proveedor
	,	P.pro_razsocial as nombre
	,	to_char(com_cab_fechaorden, 'DD/MM/YYYY') as fecha
	,	CASE WHEN ltrim(com_cab_moneda,'0')='1' THEN 'Soles' WHEN ltrim(com_cab_moneda,'0')='2' THEN 'Dolares' Else '-' END as moneda
	,	CASE WHEN cast(com_cab_tipcambio AS integer)!=0 THEN cast(com_cab_tipcambio AS varchar(10)) Else '-' END as T_Cambio
	,	com_cab_imporden as importe
	,	CASE 	WHEN com_cab_estado='1' THEN 'Pendiente' 
			WHEN com_cab_estado='2' THEN 'Inventario'
			WHEN com_cab_estado='3' THEN 'Procesando'
			WHEN com_cab_estado='4' THEN 'Facturado'
			WHEN com_cab_estado='5' THEN 'Cerrado'
			Else 'Otro' END as estado 
	,	CASE WHEN com_factu!= '' THEN com_factu Else '-' END  as factura 
FROM 		com_cabecera C
INNER JOIN 	int_proveedores P
ON		P.pro_codigo = C.pro_codigo
INNER JOIN 	inv_ta_almacenes A
ON		A.ch_almacen = C.com_cab_almacen 
WHERE		C.com_cab_fechaorden between to_date('".$fecha."', 'DD/MM/YYYY') and to_date('".$fecha2."', 'DD/MM/YYYY') ";

		if ($almacen != "TODAS") {
		    	$query .= " AND com_cab_almacen='" . pg_escape_string($almacen) . "' ";
		}

		if ($pendiente == "" and $inventario == "" and $procesando == "" and $facturado == "" and $cerrado == ""){
			//echo "TODOS";		
		}
		else{
			if ($pendiente == ""){
				$query .= " AND com_cab_estado<>'1' ";
			}
			if ($inventario == ""){
				$query .= " AND com_cab_estado<>'2' ";
			}
			if ($procesando == ""){
				$query .= " AND com_cab_estado<>'3' ";
			}
			if ($facturado == ""){
				$query .= " AND com_cab_estado<>'4' ";
			}
			if ($cerrado == ""){
				$query .= " AND com_cab_estado<>'5' ";
			}
		}

		$query .= "ORDER BY cast(C.com_cab_numorden as integer) DESC;";

	var_dump($query);
	//echo '&&&'.$query.'&&&';
         if ($sqlca->query($query) < 0) return null;
	$resultado = array();
	for ($i = 0; $i < $sqlca->numrows(); $i++) {
		$fila = $sqlca->fetchRow();
		$resultado[$i] = $fila;
	}
    return $resultado;
  }

  function ModelReportePDFPersonal($fecha,$fecha2,$almacen,$pendiente,$inventario,$procesando,$facturado,$cerrado){
    global $sqlca;
	$query =	"	SELECT 		C.com_cab_numorden as orden
	,	A.ch_nombre_almacen as almacen 
	,	C.pro_codigo as proveedor
	,	P.pro_razsocial as nombre
	,	to_char(com_cab_fechaorden, 'DD/MM/YYYY') as fecha
	,	CASE WHEN ltrim(com_cab_moneda,'0')='1' THEN 'Soles' WHEN ltrim(com_cab_moneda,'0')='2' THEN 'Dolares' Else '-' END as moneda
	,	CASE WHEN cast(com_cab_tipcambio AS integer)!=0 THEN cast(com_cab_tipcambio AS varchar(10)) Else '-' END as T_Cambio
	,	com_cab_imporden as importe
	,	CASE 	WHEN com_cab_estado='1' THEN 'Pendiente' 
			WHEN com_cab_estado='2' THEN 'Inventario'
			WHEN com_cab_estado='3' THEN 'Procesando'
			WHEN com_cab_estado='4' THEN 'Facturado'
			WHEN com_cab_estado='5' THEN 'Cerrado'
			Else 'Otro' END as estado 
	,	CASE WHEN com_factu!= '' THEN com_factu Else '-' END  as factura 
FROM 		com_cabecera C
INNER JOIN 	int_proveedores P
ON		P.pro_codigo = C.pro_codigo
INNER JOIN 	inv_ta_almacenes A
ON		A.ch_almacen = C.com_cab_almacen 
WHERE		C.com_cab_fechaorden between to_date('".$fecha."', 'DD/MM/YYYY') and to_date('".$fecha2."', 'DD/MM/YYYY') 
ORDER BY cast(C.com_cab_numorden as integer) DESC;";

	var_dump($query);
	//echo '&&&'.$query.'&&&';
         if ($sqlca->query($query) < 0) return null;
	$resultado = array();
	for ($i = 0; $i < $sqlca->numrows(); $i++) {
		$fila = $sqlca->fetchRow();
		$resultado[$i] = $fila;
	}
    return $resultado;
  }

	function obtenerAlmacenes() {
		global $sqlca;

		$sql =	"	SELECT
					ch_almacen,
					ch_almacen || ' - ' || ch_nombre_almacen
				FROM
					inv_ta_almacenes
				WHERE
					ch_clase_almacen='1';";

		if ($sqlca->query($sql) < 0)
			return false;

		$result = array();

		for ($i=0;$i<$sqlca->numrows();$i++) {
			$a = $sqlca->fetchRow();
			$result[$a[0]] = $a[1];
		}

		return $result;
	}


	function igv(){
	
		$host = "localhost";
					$data = "integrado";
					$user = "postgres"; //usuario de postgres
					$pass = "postgres"; //password de usuario de postgres
					$conn_string = "host=". $host . " dbname= " . $data . " user=" . $user . " password=" . $pass;
					$dbconn = pg_connect($conn_string);

					//validar la conexión
					if(!$dbconn) {
						$error_cDB= "Error al conectar a la Base de datos\n";
					
					}
		
		$sql_igv="SELECT (util_fn_igv()/100)+1 as igv";
		$res = pg_query($dbconn,$sql_igv);
		$igvv=array();
		$cc=0;
		while( $row = pg_fetch_array ( $res,$cc )) {
			$igvv[$cc][0]=$row[0];
			$cc++;
		}
		$c=0;
	
		return $igvv[0][0];
	}


	function obtenerArticulos($numero,$percepcion) {
		global $sqlca;
		
		$percepcion=($percepcion/100)+1;
		$igv=OrdenCompraModel::igv();
		$sql="select   	DET.ART_CODIGO, 
					ART.ART_DESCRIPCION,
					DET.COM_DET_CANTIDADPEDIDA,
					DET.COM_DET_PRECIO,
					DET.COM_DET_DESCUENTO1,
					DET.COM_DET_IMPARTICULO
					from COM_DETALLE DET, INT_ARTICULOS ART
					where DET.ART_CODIGO=ART.ART_CODIGO and DET.COM_CAB_NUMORDEN='".$numero."' " ;

		//echo '%%%'.$sql.'%%%';
		if ($sqlca->query($sql) < 0)
			return false;

		$result = Array();
		$total = $sqlca->numrows()-1;

		for ($i=0;$i<$sqlca->numrows();$i++) {
			$a = $sqlca->fetchRow();
			@$result['articulos'][$i][0] = $a[0];//codigo articulo
			@$result['articulos'][$i][1] = $a[1];//descripcion
			@$result['articulos'][$i][2] = $a[2];//cantidad
			@$result['articulos'][$i][3] = $a[3];//precio
			@$result['articulos'][$i][4] = $a[4];//descuento
			@$result['articulos'][$i][5] = $a[5];//importe
			@$result['articulos'][$total]['descuento'] += $a[4];//importe
			//@$result['articulos'][$total]['total']+= $a[5];
			$ttot += $a[5];//importe
		}
			if($percepcion!="" || $percepcion!=0){$per=$percepcion;$v_perc=$ttot*$per; $v_perc2=$v_perc-$ttot;} else{$per=0;$v_perc=0;$v_perc2=0;}
			@$result['articulos'][$total]['perce']=$v_perc;//percepcion
			@$result['articulos'][$total]['perce_i']=$v_perc2;//percepcion
			@$result['articulos'][$total]['total']=$ttot;//importe
			

		return $result;
	}

	function obtenerMoneda() {
		global $sqlca;
		$sql = "SELECT trim(tab_elemento) AS cod,tab_descripcion 
			  FROM int_tabla_general  
			  WHERE tab_tabla='MONE' 
			  AND tab_elemento!='000000' 
			  ORDER BY cod";
		$sqlca->query($sql,'moneda');
		//echo '$$$'.$sql.'$$$';
		$monedas = array();
		while($recordmon = $sqlca->fetchRow('moneda')){
			$monedas[round($recordmon['cod'])] = $recordmon['tab_descripcion'];
		}

		return $monedas;
	}

	function obtenerImportante() {
		global $sqlca;
		$sql = "SELECT par_valor 
			  FROM int_parametros  
			  WHERE par_nombre='orden_importante'";
		$sqlca->query($sql,'importante');
		$recordimp = $sqlca->fetchRow('importante');

		return $recordimp;
	}

	function obtenerCorreo() {
		global $sqlca;
		$sql = "SELECT par_valor 
			  FROM int_parametros  
			  WHERE par_nombre='correo'";
		$sqlca->query($sql,'correo');
		$recordcorreo = $sqlca->fetchRow('correo');

		return $recordcorreo;
	}

	function obtenerCorreoDestino() {
		global $sqlca;
		$sql = "SELECT par_valor 
			  FROM int_parametros  
			  WHERE par_nombre='correo_destino'";
		$sqlca->query($sql,'correo_destino');
		$recordcorreo_destino = $sqlca->fetchRow('correo_destino');

		return $recordcorreo_destino;
	}

	function obtenerClave() {
		global $sqlca;
		$sql = "SELECT par_valor 
			  FROM int_parametros  
			  WHERE par_nombre='clave'";
		$sqlca->query($sql,'clave');
		$recordclave = $sqlca->fetchRow('clave');

		return $recordclave;
	}

	function obtenerNombre() {
		global $sqlca;
		$sql = "SELECT par_valor 
			  FROM int_parametros  
			  WHERE par_nombre='nombre'";
		$sqlca->query($sql,'nombre');
		$recordnombre = $sqlca->fetchRow('nombre');

		return $recordnombre;
	}

	function obtenerProveedor($recordord) {
		global $sqlca;
		$sql = "SELECT  pro_razsocial,
				pro_ruc,
				  pro_rsocialbreve,
				  pro_grupo,
				  pro_direccion,
				  pro_comp_direcc,
				  pro_ruc,
				  pro_telefono1,
				  pro_telefono2,
				  pro_contacto
			 FROM int_proveedores 
			 WHERE pro_codigo='".trim($recordord['pro_codigo'])."'";
		$sqlca->query($sql,'proveedor');
		//echo '$$$'.$sql.'$$$';
		$recordpro = $sqlca->fetchRow('proveedor');

		return $recordpro;
	}

	function obtenerAlmacenUnico($recordord) {
		global $sqlca;
		if($recordord!=""){$where="WHERE ch_sucursal LIKE '".trim($recordord['com_cab_almacen'])."'";}else{$where="";}
		$sql = "SELECT trim(ch_sucursal) AS cod, ch_nombre_sucursal,
	       			ch_nombre_breve_sucursal, 
	       			ch_direccion, 
	       			ch_distrito, 
	       			ch_telefonos,
	       			razsocial 
	 		FROM int_ta_sucursales ".$where."
	 		";
		$sqlca->query($sql,'almacenes');
		//echo '$$$'.$sql.'$$$';
		$recordpro = $sqlca->fetchRow('almacenes');

		return $recordpro;
	}
	
	
	function obtenerAlmacen2($recordord) {
		global $sqlca;
		if($recordord!=""){$where="WHERE ch_almacen LIKE '".trim($recordord['com_cab_almacen'])."'";}else{$where="";}
		$sql = "SELECT ch_almacen, ch_nombre_almacen, ch_direccion_almacen
	 		FROM inv_ta_almacenes ".$where."
	 		";
		$sqlca->query($sql,'almacenes');
		//echo '$$$'.$sql.'$$$';
		$recordpro = $sqlca->fetchRow('almacenes');

		return $recordpro;
	}

	function obtenerFormaPago($m_tab) {
		global $sqlca;
		$sql = "SELECT substr(tab_elemento,5,2) ,tab_descripcion 
			  FROM int_tabla_general 
			  WHERE tab_tabla='".$m_tab."' 
			  AND tab_elemento!='000000' 
			  ORDER BY tab_elemento";
		$sqlca->query($sql,'fpagos');
		//echo '$$$'.$sql.'$$$';
		$fpagos = array();
		while($recordfpagos = $sqlca->fetchRow('fpagos'))
		{
		   $fpagos[$recordfpagos['substr']] = $recordfpagos['tab_descripcion'];//GENERAR EL ARREGLO
		}

		return $fpagos;
	}

	function obtenerDatosOrden($m_clave1,$m_clave2){
		global $sqlca,$sqlca2;
		$query = " 	SELECT   det.com_cab_numorden, 
				    det.art_codigo, 
				    art.art_descripcion,
				    substring(art.art_unidad from 4 for 3) as unidad,
				    det.com_det_cantidadpedida,
				    det.com_det_precio,
				    det.com_det_descuento1,
				    det.com_det_imparticulo
			    	FROM com_detalle det, int_articulos art
			    	WHERE 
			    	det.art_codigo=art.art_codigo 
			    	and det.com_cab_numorden='".$m_clave1."' 
			    	and det.num_seriedocumento='".$m_clave2."'";
		$sqlca->query($query,'ordenes');
		//echo '$$$'.$query.'$$$';
		$ordenes = array();
		
		
		
					$igv=OrdenCompraModel::igv();
		while($record = $sqlca->fetchRow('ordenes'))
		{
		   $ordenes[$c]['ITEM'] = ($c+1);		   
		   $ordenes[$c]['CODIGO'] = $record['art_codigo'];
		   $ordenes[$c]['DESCRIPCION'] = $record['art_descripcion'];
		   $ordenes[$c]['CANT'] = round($record['com_det_cantidadpedida']);
		   $ordenes[$c]['UNIDAD'] = $record['unidad'];
		   $precio_Sing=number_format($record['com_det_precio']/$igv,4);
		   $ordenes[$c]['PRECIO'] =  $precio_Sing;
		   $ordenes[$c]['DESCUENTO'] = $record['com_det_descuento1'];
		   $ordenes[$c]['VALOR VENTA'] = $record['com_det_imparticulo'];
		   $c++;
		}

		return $ordenes;
	}

	function obtenerCabecera($numero1,$numero2) {
		global $sqlca;

		$sql="SELECT pro_codigo,
		    num_tipdocumento,
		    num_seriedocumento,
		    com_cab_numorden,
		    com_cab_almacen,
		    com_cab_fechaorden,
		    com_cab_moneda,
		    com_cab_tipcambio,
		    com_cab_credito,
		    com_cab_formapago,
		    com_cab_imporden,
		    com_cab_recargo1,
		    com_cab_observacion,
		    com_cab_estado,
		    com_cab_fechaofrecida,
		    com_cab_fecharecibida,
		    com_cab_det_glosa,
		    percepcion,
		    percepcion_i
	 		FROM com_cabecera
         	WHERE com_cab_numorden='".$numero1."'
		 	AND num_seriedocumento='".$numero2."'";
		$sqlca->query($sql,'orden');
		//echo '$$$'.$sql.'$$$';
		$recordord = $sqlca->fetchRow('orden');

		return $recordord;
	}

	function eliminarArticulo($cod_numero,$numorden,$proveedor) {
		global $sqlca;
		$sql =	"	delete from COM_DETALLE
				    where ART_CODIGO='$cod_numero' and COM_CAB_NUMORDEN='$numorden' and pro_codigo='$proveedor';";
		//echo '%%%'.$sql.'%%%';
		if ($sqlca->query($sql) == 0)
			return false;
		$sqlca->numrows();
		return true;
	}

	function obtenerOrdenCorrelativa() {
		global $sqlca;
		$sql =	"SELECT par_valor FROM int_parametros WHERE trim(par_nombre)='codes'";
		if ($sqlca->query($sql) < 0)
			return false;
		$resul = '';
		for ($i=0;$i<$sqlca->numrows();$i++) {
			$a = $sqlca->fetchRow();
			$resul = $a[0];
		}
		$sql =	"SELECT lpad(util_fn_corre_docs('01','".$resul."', 'select' )::text,8,'0')";
		if ($sqlca->query($sql) < 0)
			return false;
		$result = array();
		for ($i=0;$i<$sqlca->numrows();$i++) {
			$a = $sqlca->fetchRow();
			$result = $a[0];
		}
		return $result;
	}

	function obtenerAlmacenBD($numero,$proveedor) {
		global $sqlca;
		$sql =	"SELECT com_cab_almacen FROM com_cabecera WHERE pro_codigo='".$proveedor."' and com_cab_numorden='".$numero."'";
		if ($sqlca->query($sql) < 0)
			return false;
		$result = array();
		for ($i=0;$i<$sqlca->numrows();$i++) {
			$a = $sqlca->fetchRow();
			$result = $a[0];
		}
		return $result;
	}

	function obtenerMonedaBD($numero,$proveedor) {
		global $sqlca;
		$sql =	"SELECT trim(com_cab_moneda) FROM com_cabecera WHERE pro_codigo='".$proveedor."' and com_cab_numorden='".$numero."'";
		if ($sqlca->query($sql) < 0)
			return false;
		$result = array();
		for ($i=0;$i<$sqlca->numrows();$i++) {
			$a = $sqlca->fetchRow();
			$result = $a[0];
		}
		return $result;
	}

	function obtenerCreditoBD($numero,$proveedor) {
		global $sqlca;
		$sql =	"SELECT com_cab_credito FROM com_cabecera WHERE pro_codigo='".$proveedor."' and com_cab_numorden='".$numero."'";
		if ($sqlca->query($sql) < 0)
			return false;
		$result = array();
		for ($i=0;$i<$sqlca->numrows();$i++) {
			$a = $sqlca->fetchRow();
			$result = $a[0];
		}
		return $result;
	}

	function obtenerFPagoBD($numero,$proveedor) {
		global $sqlca;
		$sql =	"SELECT com_cab_formapago FROM com_cabecera WHERE pro_codigo='".$proveedor."' and com_cab_numorden='".$numero."'";
		if ($sqlca->query($sql) < 0)
			return false;
		$result = array();
		for ($i=0;$i<$sqlca->numrows();$i++) {
			$a = $sqlca->fetchRow();
			$result = $a[0];
		}
		return $result;
	}

	function obtenerSerie(){
		global $sqlca;

		$sql =	"SELECT par_valor FROM int_parametros WHERE trim(par_nombre)='codes'";
		if ($sqlca->query($sql) < 0)
			return false;

		$result = array();
		for ($i=0;$i<$sqlca->numrows();$i++) {
			$a = $sqlca->fetchRow();
			$result = $a[0];
		}
		return $result;
	}

	function obtenerOrdenExistente($num_orden){
		global $sqlca;

		$sql =	"select count(pro_codigo) from com_cabecera where com_cab_numorden='$num_orden'";
		if ($sqlca->query($sql) < 0)
			return false;

		$result = array();
		for ($i=0;$i<$sqlca->numrows();$i++) {
			$a = $sqlca->fetchRow();
			$result = $a[0];
		}
		return $result;
	}

	function obtenerEstados() {

		$result = array();
		$result['0'] = 'Pendiente';
		$result['1'] = 'Inventario';
		$result['2'] = 'Procesando';
		$result['3'] = 'Facturado';
		$result['4'] = 'Cerrado';

		return $result;
	}

	function obtenerMonedas() {

		global $sqlca;
		$sql ="select trim(tab_elemento),tab_elemento || ' -- ' || tab_descripcion from int_tabla_general  where tab_tabla='MONE' and tab_elemento!='000000' order by tab_elemento";

		if ($sqlca->query($sql) < 0)
			return false;

		$result = array();
		for ($i=0;$i<$sqlca->numrows();$i++) {
			$a = $sqlca->fetchRow();
			$result[$a[0]] = $a[1];
			//echo '==='.$a[1].'===';
		}

		return $result;
	}

	function obtenerFPago($valor) {
		global $sqlca;
		$sql ="select substr(TAB_ELEMENTO,5,2), substr(TAB_ELEMENTO,5,2)  || ' -- ' || TAB_DESCRIPCION from INT_TABLA_GENERAL where TAB_TABLA='".$valor."' and tab_elemento!='000000' order by TAB_ELEMENTO";

		if ($sqlca->query($sql) < 0)
			return false;

		$result = array();
		for ($i=0;$i<$sqlca->numrows();$i++) {
			$a = $sqlca->fetchRow();
			$result[$a[0]] = $a[1];
		}

		return $result;
	}

	function buscar($almacen, $dia,$dia2,$pendiente,$inventario,$procesando,$facturado,$cerrado,$percepcion) {
		global $sqlca;
		//ESTADO
		//select tab_elemento,tab_descripcion from int_tabla_general where tab_tabla='ESTO' and tab_elemento!='000000' order by TAB_ELEMENTO
		
		

		$sql =	"	SELECT 		C.com_cab_numorden as OrdenCompra
	,	A.ch_nombre_almacen
	,	C.pro_codigo as proveedor
	,	P.pro_razsocial as nombre
	,	to_char(com_cab_fechaorden, 'DD/MM/YYYY') as fecha
	,	CASE WHEN ltrim(com_cab_moneda,'0')='1' THEN 'Soles' WHEN ltrim(com_cab_moneda,'0')='2' THEN 'Dolares' Else '-' END as moneda
	,	CASE WHEN cast(com_cab_tipcambio AS integer)!=0 THEN cast(com_cab_tipcambio AS varchar(10)) Else '-' END as T_Cambio
	,	com_cab_imporden as importe
	,	CASE 	WHEN com_cab_estado='1' THEN 'Pendiente' 
			WHEN com_cab_estado='2' THEN 'Inventario'
			WHEN com_cab_estado='3' THEN 'Procesando'
			WHEN com_cab_estado='4' THEN 'Facturado'
			WHEN com_cab_estado='5' THEN 'Cerrado'
			Else 'Otro' END as estado 
	,	CASE WHEN com_factu!= '' THEN com_factu Else '-' END  as factura
	,   C.num_seriedocumento as SerieCompra
FROM 		com_cabecera C
INNER JOIN 	int_proveedores P
ON		P.pro_codigo = C.pro_codigo
INNER JOIN 	inv_ta_almacenes A
ON		A.ch_almacen = C.com_cab_almacen 
WHERE		C.com_cab_fechaorden between to_date('".$dia."', 'DD/MM/YYYY') and to_date('".$dia2."', 'DD/MM/YYYY') ";

		if ($almacen != "TODAS") {
		    	$sql .= " AND com_cab_almacen='" . pg_escape_string($almacen) . "' ";
		}

		if ($pendiente == "" and $inventario == "" and $procesando == "" and $facturado == "" and $cerrado == ""){
			//echo "TODOS";		
		}
		else{
			if ($pendiente == ""){
				$sql .= " AND com_cab_estado<>'1' ";
			}
			if ($inventario == ""){
				$sql .= " AND com_cab_estado<>'2' ";
			}
			if ($procesando == ""){
				$sql .= " AND com_cab_estado<>'3' ";
			}
			if ($facturado == ""){
				$sql .= " AND com_cab_estado<>'4' ";
			}
			if ($cerrado == ""){
				$sql .= " AND com_cab_estado<>'5' ";
			}
		}

		$sql .= "ORDER BY cast(C.com_cab_numorden as integer) DESC;";

		echo '<<<'.$sql.'>>>';
		if ($sqlca->query($sql) < 0)
			return false;
if($percepcion!=""){$per=$percepcion;} else{$per=1;}
		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();

			$resultado[$i]['orden'] = $a[0];
			$resultado[$i]['almacen'] = $a[1];
			$resultado[$i]['proveedor'] = $a[2];
			$resultado[$i]['nombre'] = $a[3];
			$resultado[$i]['fecha'] = $a[4];
			$resultado[$i]['moneda'] = $a[5];
			$resultado[$i]['tipocambio'] = $a[6];
			$resultado[$i]['importe'] = $a[7]; //($a[7]*$igv)*$per;
			$resultado[$i]['estado'] = $a[8];
			$resultado[$i]['factura'] = $a[9];
			$resultado[$i]['serie'] = $a[10];
			
		}
		return $resultado;
	}


function ctrl_percepcion($num,$valor,$percepcion,$x){
	global $sqlca;
	//$val=1;										
	$val=Array();				
	$igv=OrdenCompraModel::igv();
	$sql =	"select valor from ctrl_com_cab_imporden where id='$num'";
		if ($sqlca->query($sql) < 0)
			{return false;}

		//$rex=Array();
		for ($i=0;$i<$sqlca->numrows();$i++) {
			$a = $sqlca->fetchRow();
			$rex = $a[0];
		}

		if($rex==""){			
			$sql="insert into ctrl_com_cab_imporden (id,valor) values ('$num',$valor)";	
			if ($sqlca->query($sql) < 0)
			{return false;}
			
			$val[0] = (($percepcion/100)+1) * $valor;//-$valor
			$val[1] = (($percepcion/100)+1) * $valor-$valor;
					
		}else{
			//$val=$val+$valor;
			$sql =	"select valor from ctrl_com_cab_imporden where id='$num'";
			if ($sqlca->query($sql) < 0)
			{return false;}

			//$rex2=Array();
			for ($i=0;$i<$sqlca->numrows();$i++) {
			$a = $sqlca->fetchRow();
			$rex2 = $a[0];
			}
			
			$valor2=$rex2+$valor;
			$val[0] = (($percepcion/100)+1) * $valor2; //-$valor2
			$val[1] = (($percepcion/100)+1) * $valor2-$valor2;
			
			$sql="UPDATE ctrl_com_cab_imporden SET valor=$valor2 where id='$num'";
			if ($sqlca->query($sql) < 0)
			{return false;}
			//$val=1;
			
		}	
	
	return $val;
}


function actualizar2($numero, $proveedor, $percepcion, $percepcion_i) {
		global $sqlca;
		$sql =	"	UPDATE com_cabecera
				SET	percepcion		= '$percepcion',
					percepcion_i		= '$percepcion_i'
				WHERE com_cab_numorden = '".$numero."' and pro_codigo = '".$proveedor."';";

		//echo '<<<'.$sql.'>>>';
		if ($sqlca->query($sql)<0){
			return FALSE;}
	return true;
}


function agregar($numero,$serie,$fecha, $almacen, $proveedor, $moneda, $tcambio, $m_credito, $fpago, $factura, $comentario, $fentrega, $glosa, $percepcion, $percepcion_i) {
		global $sqlca;

		$sql =	"	INSERT INTO
					com_cabecera
				(
					com_cab_numorden,
					num_seriedocumento,
					com_cab_fechaorden,
					com_cab_almacen,
					pro_codigo,
					com_cab_moneda,
					com_cab_tipcambio,
					com_cab_credito,
					com_cab_formapago,
					com_factu,
					com_cab_observacion,
					com_cab_fechaofrecida,
					com_cab_fecharecibida,
					com_cab_det_glosa,
					num_tipdocumento,
					com_cab_imporden,
					com_cab_recargo1,
					com_cab_estado,
					com_cab_transmision,
					com_ser,
					fecha_replicacion,
					percepcion,
					percepcion_i
				) VALUES (
					'$numero',
					'$serie',
					to_date('$fecha','DD/MM/YYYY'),
					'$almacen',
					'$proveedor',
					'$moneda',
					$tcambio,
					'$m_credito',
					'$fpago',
					'$factura',
					'$comentario',
					to_date('$fentrega','DD/MM/YYYY'),
					to_date('$fentrega','DD/MM/YYYY'),
					'$glosa',
					'01',
					0.00,
					0.00,
					'1',
					't',
					'',
					now(),
					'$percepcion',
					'$percepcion_i'
				);";

		//echo '<<<'.$sql.'>>>';
		if ($sqlca->query($sql)<0){
			return FALSE;}
		$sql =	"update int_num_documentos set num_numactual=to_char($numero,'99999999') 
			where num_tipdocumento = '01' and 
			num_seriedocumento = '$serie';";
		//echo '<<<'.$sql.'>>>';
		$sqlca->query($sql);
		return TRUE;
	}

	function actualizar($numero, $proveedor, $moneda, $tcambio, $m_credito, $fpago, $factura, $comentario, $fentrega, $glosa, $percepcion) {
		global $sqlca;

		$sql =	"	UPDATE com_cabecera
				SET
					com_cab_moneda		= '$moneda',
					com_cab_tipcambio	= $tcambio,
					com_cab_credito		= '$m_credito',
					com_cab_formapago	= '$fpago',
					com_factu		= '$factura',
					com_cab_observacion	= '$comentario',
					com_cab_fechaofrecida	= to_date('$fentrega','DD/MM/YYYY'),
					com_cab_fecharecibida	= to_date('$fentrega','DD/MM/YYYY'),
					com_cab_det_glosa	= '$glosa',
					percepcion		= '$percepcion'
				WHERE com_cab_numorden = '".$numero."' and pro_codigo = '".$proveedor."';";

		//echo '<<<'.$sql.'>>>';
		if ($sqlca->query($sql)<0){
			return FALSE;}
		return TRUE;
	}

	function agregardetalle($numero,$proveedor,$fecha, $articulo, $cantidad, $precio, $descuento, $subtotal, $serie) {
		global $sqlca;

		$sql="INSERT INTO 
				com_detalle  
				(	com_cab_numorden, 
					pro_codigo,
					com_det_fechaentrega, 
					art_codigo, 
					com_det_cantidadpedida, 
					com_det_precio,
					com_det_imparticulo, 
					com_det_descuento1, 
					num_tipdocumento, 
					num_seriedocumento, 
					com_det_estado,
					fecha_replicacion
				) VALUES (
					'".trim($numero)."',
					'".trim($proveedor)."',
					to_date('$fecha', 'DD/MM/YYYY'), 
					'$articulo',
					$cantidad, 
					$precio, 
					$subtotal, 
					$descuento,
					'01', 
					'$serie', 
					'1', 
					now()
				)";
		//echo '++'.$sql.'++';
		$sqlca->query($sql);
			
		return TRUE;
	}

	function importarDia($dia) {
		global $sqlca;

		$sqlca->query("BEGIN;");

		$sql =	"	SELECT
					ch_posturno,
					ch_poscd,
					da_fecha
				FROM
					pos_aprosys
				WHERE
					da_fecha = to_date('$dia','DD/MM/YYYY');";
		if ($sqlca->query($sql)<0)
			return "INTERNAL_ERROR_ID1";
		if ($sqlca->numrows()==0)
			return "INVALID_DATE";
		$r = $sqlca->fetchRow();

		if ($r[1]!='S')
			return "INVALID_DATE";

		$i = $r[0];
		settype($i,"int");
		if ($i==0 || $i==1)
			return "INVALID_DATE";

		for ($j=1;$j<$i;$j++)
			if (($res = SobrantesFaltantesTrabajadorModel::importarTurno($r[2],$j))!="OK") {
				$sqlca->query("ROLLBACK;");
				return $res;
			}

		$sqlca->query("COMMIT;");

		return "OK";
	}

	function importarTurno($dia,$turno) {
		global $sqlca;

		$sql =	"	SELECT
					trim(hlx.ch_lado),
					trim(hlx.ch_codigo_trabajador),
					hlx.ch_tipo,
					hlx.ch_sucursal,
					p.par_valor
				FROM
					pos_historia_ladosxtrabajador hlx
					LEFT JOIN int_parametros p ON p.par_nombre = 'diferencia_ignorada'
				WHERE
					hlx.dt_dia = '$dia'
					AND hlx.ch_posturno = '$turno';";

		if ($sqlca->query($sql)<0)
			return "INTERNAL_ERROR_IT1";

		$lados = Array();
		while ($reg = $sqlca->fetchRow())
			$lados[] = $reg;

		// Borra registros pre existentes de sobrantes y faltantes para esta fecha
		// Y ingresa los depositos de cada trabajador como sobrantes
		$trabajadores = Array();
		foreach ($lados as $reg) {
			$diferencia_ignorada = $reg[4];
			$trabajador = $reg[1];
			$es = $reg[3];
			// Si aún no ha ingresado los depositos del trabajador, los ingresa
			if (!isset($trabajadores[$trabajador])) {
				$sql =	"	DELETE FROM
							comb_diferencia_trabajador
						WHERE
							es = '{$reg[3]}'
							AND ch_codigo_trabajador = '{$reg[1]}'
							AND dia = '{$dia}'
							AND turno = '{$turno}'
							AND flag = 0;";

				if ($sqlca->query($sql)<0)
					return "INTERNAL_ERROR_IT2";

				$sql =	"	SELECT
							COALESCE(sum(
								CASE
									WHEN ch_moneda='01' THEN nu_importe
									ELSE nu_importe * nu_tipo_cambio
								END
							),0)
						FROM
							pos_depositos_diarios
						WHERE
							ch_valida='S'
							AND dt_dia = '$dia'
							AND ch_posturno = $turno
							AND ch_codigo_trabajador = '$trabajador';";
				if ($sqlca->query($sql)<0)
					return "INTERNAL_ERROR_IT3";
				$r = $sqlca->fetchRow();
				$deptr = $r[0];
				if (SobrantesFaltantesTrabajadorModel::upsertFaltantes($es,$trabajador,$dia,$turno,$deptr)==FALSE)
					return "INTERNAL_ERROR_IT4";
				$trabajadores[$trabajador] = $trabajador;

			}
		}

		// Hace el cálculo de sobrante/faltante por cada lado
		foreach ($lados as $reg) {
			// Prepara Variables
			$lado = $reg[0];
			$trabajador = $reg[1];
			$es = $reg[3];
			$fechax = explode("-",$dia);
			$postrans = "pos_trans{$fechax[0]}{$fechax[1]}";

			/*Previamente, se ha insertado la suma de depositos de cada trabajador como un sobrante*/

			if ($reg[2]=="C") {
				/* En Combustibles:
					Al sobrante actual, le resta la venta x contometros, y le aumenta:
					Afericiones, N/D, Tarjetad, Descuentos y Devoluciones
					de cada lado asignado al trabajador.*/

				// Obtiene la venta del lado
				$ladoi = $lado;
				settype($ladoi,"int");
				$sql =	"	SELECT
							sum(cnt_val) AS importe,
							min(cnt),
							min(fecha)
						FROM
							pos_contometros
						WHERE
							num_lado = $ladoi
							AND dia = '$dia'
							AND turno = '$turno';";
				if ($sqlca->query($sql)<0)
					return "INTERNAL_ERROR_IT5";
				$r = $sqlca->fetchRow();
				$final = $r[0];
				$sql =	"	SELECT
							sum(cnt_val) AS importe
						FROM
							pos_contometros
						WHERE
							num_lado = $ladoi
							AND cnt < {$r[1]}
							AND fecha < '{$r[2]}'
						GROUP BY
							dia,
							turno
						ORDER BY
							dia DESC,
							turno DESC
						LIMIT
							1;";
				if ($sqlca->query($sql)<0) {
					return "INTERNAL_ERROR_IT6";}
				$r = $sqlca->fetchRow();
				$inicial = $r[0];
//				$ventalado = $inicial - $final;	// Para tenerlop en negativo
				$ventalado = diferencia_contometros($inicial,$final)*-1;	//Para tenerlo en negativo

				if (SobrantesFaltantesTrabajadorModel::upsertFaltantes($es,$trabajador,$dia,$turno,$ventalado)==FALSE)
					return "INTERNAL_ERROR_IT7";

				// Agrega las demas variables de la formula
				$sql =	"	SELECT
							(SELECT COALESCE(sum(importe),0) FROM $postrans WHERE td='A' AND dia='$dia' AND tipo='C' AND pump='$lado' AND turno='$turno') +
							(SELECT COALESCE(sum(importe),0) FROM $postrans WHERE td='N' AND dia='$dia' AND tipo='C' AND pump='$lado' AND turno='$turno') +
							(SELECT COALESCE(sum(importe),0) FROM $postrans WHERE td IN ('F','B') AND fpago='2' AND dia='$dia' AND tipo='C' AND pump='$lado' AND turno='$turno') +
							(SELECT COALESCE(sum(importe),0)*-1 FROM $postrans WHERE td IN ('F','B') AND fpago='1' AND importe<0 AND dia='$dia' AND tipo='C' AND pump='$lado' AND turno='$turno') AS GranFormulaXD;";
/*
En la Formula Compensatoria: En la linea que obtiene Descuentos y Devoluciones (4) Creo que debería haber la condicion "AND fpago='1'",
pues los descuentos y devoluciones pagados con T/C son contabilizados en la linea anterior.

Lo he eliminado para que cuadre con el reporte venta x manguera x trabajador.

Queda pendiente la discusion de si va o no va.
*/
				if ($sqlca->query($sql)<0)
					return "INTERNAL_ERROR_IT8";
				$r = $sqlca->fetchRow();
				$aumento = $r[0];
				if (SobrantesFaltantesTrabajadorModel::upsertFaltantes($es,$trabajador,$dia,$turno,$aumento)==FALSE)
					return "INTERNAL_ERROR_IT9";
			} else {
				// En Market, Saca la diferencia entre depositos y suma de tickets, y la inserta como faltante
				$sql =	"	SELECT
							COALESCE(sum(importe)*-1,0)
						FROM
							$postrans
						WHERE
							trim(caja) = '$lado'
							AND es = '$es'
							AND dia = '$dia'
							AND turno = '$turno'
							AND tipo='M';";

				if ($sqlca->query($sql)<0)
					return "INTERNAL_ERROR_IT10";
				$r = $sqlca->fetchRow();
				$aumento = $r[0];
				if (SobrantesFaltantesTrabajadorModel::upsertFaltantes($es,$trabajador,$dia,$turno,$aumento)==FALSE)
					return "INTERNAL_ERROR_IT11";
			}
		}

		if ($diferencia_ignorada>0) {
			$sql =	"	DELETE FROM
						comb_diferencia_trabajador
					WHERE
						dia = '$dia'
						AND turno = '$turno'
						AND flag = 0
						AND abs(importe)<{$diferencia_ignorada};";

			if ($sqlca->query($sql)<0)
				return "INTERNAL_ERROR_IT12";
		}

		return "OK";
	}

	function upsertFaltantes($es,$trabajador,$dia,$turno,$importe) {
		global $sqlca;

		$sql =	"	SELECT
					id_diferencia_trabajador
				FROM
					comb_diferencia_trabajador
				WHERE
					es = '$es'
					AND ch_codigo_trabajador = '$trabajador'
					AND dia = '$dia'
					AND turno = '$turno'
					AND flag = 0;";

		if ($sqlca->query($sql)<0)
			return FALSE;

		if ($sqlca->numrows()==0) {
			$sql =	"	INSERT INTO
						comb_diferencia_trabajador
					(
						es,
						ch_codigo_trabajador,
						dia,
						turno,
						flag,
						importe,
						observacion
					) VALUES (
						'$es',
						'$trabajador',
						'$dia',
						$turno,
						0,
						$importe,
						''
					);";
		} else {
			$r = $sqlca->fetchRow();
			$sql =	"	UPDATE
						comb_diferencia_trabajador
					SET
						importe = importe + ($importe)
					WHERE
						id_diferencia_trabajador = {$r[0]};";
		}

		return ($sqlca->query($sql)>=0);
	}
}
?>
