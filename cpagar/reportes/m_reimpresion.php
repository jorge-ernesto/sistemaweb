<?php

class ReimpresionModel extends Model{

    function buscarCPAG($busqueda=array()){
	global $sqlca;
	$query ="";
	$where ="";
	$order ="";
	$limit ="";
			
	$query="select 
	    	    cab.pro_cab_tipdocumento as pro_cab_tipdocumento, 
		    cab.pro_cab_seriedocumento as pro_cab_seriedocumento, 
		    cab.pro_cab_numdocumento as pro_cab_numdocumento, 
		    cab.pro_codigo as pro_codigo, 
		    cab.pro_cab_fecharegistro,
		    cab.pro_cab_almacen,
		    cab.pro_cab_imptotal, 
		    cab.pro_cab_numreg,
		    reg.pro_cab_num_orden_registro
		from 
		    cpag_ta_cabecera cab 
		    left join cpag_ta_cabecera_registro reg
		    on
			    cab.pro_cab_tipdocumento = reg.pro_cab_tipdocumento 
			and cab.pro_cab_seriedocumento  = reg.pro_cab_seriedocumento 
			and cab.pro_cab_numdocumento    = reg.pro_cab_numdocumento 
			and cab.pro_codigo = reg.pro_codigo 
		where
		    cab.pro_cab_impsaldo=cab.pro_cab_imptotal 
			";
		
	foreach($busqueda as $col => $val)
	{
	    if ($col=='pro_codigo'){
		if (trim($val)!="") $where.= " AND cab.pro_codigo like '%" . pg_escape_string($val) . "%'";
	    }
	    if ($col=='pro_cab_almacen'){
		if (trim($val)!="") $where.= " AND cab.pro_cab_almacen='" . pg_escape_string($val) . "'";
	    }
	}

	$where.= " and cab.pro_cab_fecharegistro between '".pg_escape_string($busqueda["pro_cab_fecha_registro_ini"])."' and '".pg_escape_string($busqueda["pro_cab_fecha_registro_fin"])."' ";
	$order = " order by pro_cab_numreg, pro_cab_tipdocumento, pro_cab_seriedocumento, pro_cab_numdocumento ";
								
	if ($where=="")
	    $limit = " limit 1000 ";
	
	$query=$query.$where.$order.$limit;
	
	if ($sqlca->query($query)<=0){ 
	    return $sqlca->get_error();
	}
	$listado = array();
	while ($row = $sqlca->fetchRow()){
	    $listado[] = $row;
	}
	return $listado;
    }

    function buscarCPAGMarcar($busqueda=array(), $m_parametro=array() )
    {
	global $sqlca, $usuario;
	$query="";
	$where="";
	$ip_origen=$_SERVER["REMOTE_ADDR"];
	$query = "SELECT
			pro_cab_tipdocumento,
		   	pro_cab_seriedocumento,
			pro_cab_numdocumento,
			pro_codigo,
			'" . $usuario->obtenerUsuario() . "' as pro_cab_usuario,
			'$ip_origen' as pro_cab_ip_origen
		  From
			cpag_ta_cabecera cab
		  WHERE
			pro_cab_impsaldo=pro_cab_imptotal
			";
	
	foreach($busqueda as $col => $val)
	{
	    if ($col=='pro_codigo'){
		if (trim($val)!="") $where.= " AND cab.pro_codigo like '%$val%'";
	    }
	    if ($col=='pro_cab_almacen'){
		if (trim($val)!="") $where.= " AND cab.pro_cab_almacen='$val'";
	    }
	    if ($col=='pro_cab_numdocumento'){
	        if (trim($val)!="") $where .= " AND cab.pro_cab_numdocumento='" . pg_escape_string($val) . "'";
	    }
	}
		
	$where.= " and cab.pro_cab_fecharegistro between '".$m_parametro["pro_cab_fecha_registro_ini"]."' and '".$m_parametro["pro_cab_fecha_registro_fin"]."' ";
	
	if ($where == "")
	    $query = "";

	if ($sqlca->query($query.$where)<=0){
	    return $sqlca->get_error();
	}
	$datos=array();
	$listado = array();
	$listado['datos'] = $sqlca->fetchAll();
	$listado['filas'] = $sqlca->numrows();
	$listado['columnas'] = $sqlca->numfields();
	$query="";
	return $listado;
    }



    function guardarCPAGMarcado( $reg_listado)
    {
	global $sqlca;
	$regs_marcados = $reg_listado['datos'];
	$regs_num_filas = $reg_listado['filas'];
	$regs_num_columnas = $reg_listado['filas'];
	foreach($regs_marcados as $reg_key => $reg_marcado)
	{
	    $numero = ReimpresionModel::obtenerCorrelativo();
	    echo "numero: " . $numero;
	    $reg_marcado['pro_cab_num_orden_registro']  = $numero;
			

	    $sql = "INSERT INTO
			cpag_ta_cabecera_registro
		    VALUES
		    (
		        '" . pg_escape_string($reg_marcado['pro_cab_tipdocumento']) . "',
		        '" . pg_escape_string($reg_marcado['pro_cab_seriedocumento']) . "',
		        '" . pg_escape_string($reg_marcado['pro_cab_numdocumento']) . "',
			'" . pg_escape_string($reg_marcado['pro_codigo']) . "',
			'" . pg_escape_string($reg_marcado['pro_cab_usuario']) . "',
			'" . pg_escape_string($reg_marcado['pro_cab_ip_origen']) . "',
			'" . pg_escape_string($reg_marcado['pro_cab_num_orden_registro']) . "'
		    )
		    ";
	    if ($sqlca->query($sql) < 0)
	    //insertar nuevo
	    //if ($sqlca->perform('cpag_ta_cabecera_registro', $reg_marcado, 'insert')<0)
		return $sqlca->get_error();
	}
	return 'OK';
    }


    function NDOC_CBArray($num_documento='', $busqueda=array()){
	global $sqlca;
	$cbArray = array();

	$query ="";
	$where ="";
	$order ="";
	$limit ="";


	$sql = "SELECT
		    pro_cab_seriedocumento||pro_cab_numdocumento as pro_cab_numdocumento , 
		    pro_cab_imptotal,
		    pro_cab_numreg
		FROM
		    cpag_ta_cabecera 
		WHERE
			pro_cab_imptotal=pro_cab_impsaldo
		    AND pro_cab_seriedocumento||pro_cab_numdocumento like '%$num_documento%' 
		";

	if (trim($busqueda["pro_codigo"])!="") $where.= " AND pro_codigo like '%".$busqueda["pro_codigo"]."%'";	
	if (trim($busqueda["pro_cab_almacen"])!="") $where.= " AND pro_cab_almacen='".$busqueda["pro_cab_almacen"]."'";	
	$where.= " AND pro_cab_fecharegistro between '".$busqueda["pro_cab_fecha_registro_ini"]."' and '".$busqueda["pro_cab_fecha_registro_fin"]."' ";	
	$order = " order by pro_cab_numreg, pro_cab_tipdocumento, pro_cab_seriedocumento, pro_cab_numdocumento ";
	if ($where=="")
	    $limit = " limit 1000 ";


	if ($sqlca->query($sql.$where.$order.$limit)<=0)
	    return $cbArray;
    
	while($result = $sqlca->fetchRow()){
    	    $cbArray[ trim( $result["pro_cab_numdocumento"] ) ] = $result["pro_cab_numdocumento"].' '.$result["pro_cab_imptotal"];
	}
	ksort($cbArray);
	return $cbArray;
    }

    function agregarNuevoCorrelativoRegistro()
    {
	global $sqlca;
	
	$sql = "INSERT INTO
		    int_cpag_ta_numregistro
		VALUES
		(
		    '" . pg_escape_string(date("Y")) . "',
		    '" . pg_escape_string(date("m")) . "',
		    '1'
		);
		";
	if ($sqlca->query($sql, "_agregarcorrelativo") < 0) return false;
	return true;
    }
    
    function obtenerCorrelativo()
    {
	global $sqlca;
	
	$sql = "SELECT
		    numero
		FROM
		    int_cpag_ta_numregistro
		WHERE
			periodo='" . pg_escape_string(date("Y")) . "'
		    AND mes='" . pg_escape_string(date("m")) . "'
		;
		";
	if ($sqlca->query($sql, "_obtenercorrelativo") < 0) return -1;
	
	if($sqlca->numrows("_obtenercorrelativo") == 0) { ReimpresionModel::agregarNuevoCorrelativoRegistro(); return 1; }
	
	$a = $sqlca->fetchRow("_obtenercorrelativo");
	
	return $a[0];
    }

    function avanzarCorrelativo()
    {
	global $sqlca;
	
	$sql = "UPDATE
		    int_cpag_ta_numregistro
		SET
		    numero=numero+1
		WHERE
			periodo='" . pg_escape_string(date("Y")) . "'
		    AND mes='" . pg_escape_string(date("m")) . "'
		;
		";
	if ($sqlca->query($sql) < 0) return false;
	
	return true;
    }

    function cintillo()
    {
	global $usuario, $sqlca;
	
	/* Inicio de transaccion */
	$sql = "BEGIN;";
	$sqlca->query($sql);
	
	/* Bloqueo tabla de numerador de registro */
	$sql = "LOCK TABLE int_cpag_ta_numregistro IN EXCLUSIVE MODE";
	$sqlca->query($sql);
	
	/* Obtiene numero actual del correlativo */
	$numero = ReimpresionModel::obtenerCorrelativo();
	
	/* Actualiza el numero de registro de los documentos marcados */
	$sql = "UPDATE
		    cpag_ta_cabecera
		SET
		    pro_cab_numreg='" . pg_escape_string($numero) . "'
		WHERE
			pro_cab_tipdocumento=cpag_ta_cabecera_registro.pro_cab_tipdocumento
		    AND pro_cab_seriedocumento=cpag_ta_cabecera_registro.pro_cab_seriedocumento
		    AND pro_cab_numdocumento=cpag_ta_cabecera_registro.pro_cab_numdocumento
		    AND pro_codigo=cpag_ta_cabecera_registro.pro_codigo
		    AND cpag_ta_cabecera_registro.pro_cab_usuario='" . pg_escape_string($usuario->obtenerUsuario()) . "'
		    AND cpag_ta_cabecera_registro.pro_cab_ip_origen='" . pg_escape_string($_SERVER['REMOTE_ADDR']) . "'
		;
		";
	if ($sqlca->query($sql) < 0) {
	    $sql = "ROLLBACK;";
	    $sqlca->query($sql);
	    return false;
	}

	/* Actualiza el correlativo de cintillos */
	if (!ReimpresionModel::avanzarCorrelativo()) {
	    $sql = "ROLLBACK;";
	    $sqlca->query($sql);
	    return false;
	}

	/* Obtiene las cabeceras de los documentos marcados */
	$sql = "SELECT
		    cab.pro_cab_fecharegistro,
		    trim(cab.pro_codigo)||' - '||prov.pro_rsocialbreve::character(20) as pro_codigo,
		    cab.pro_cab_almacen||' - '||alma.ch_nombre_almacen as pro_cab_almacen,
		    cab.pro_cab_tipdocumento,
		    cab.pro_cab_seriedocumento,
		    cab.pro_cab_numdocumento,
		    cab.pro_cab_numreg,
		    cab.pro_cab_rubrodoc,
		    cab.pro_cab_fechaemision,
		    tab.tab_desc_breve as pro_cab_moneda,
		    cab.pro_cab_imptotal-cab.pro_cab_impto1 as pro_cab_valor,
		    cab.pro_cab_impto1,
		    cab.pro_cab_imptotal
		FROM
		    cpag_ta_cabecera cab,
		    cpag_ta_cabecera_registro reg,
		    int_tabla_general tab,
		    inv_ta_almacenes alma,
		    int_proveedores prov
		WHERE
			cab.pro_cab_tipdocumento=reg.pro_cab_tipdocumento
		    AND cab.pro_cab_seriedocumento=reg.pro_cab_seriedocumento
		    AND cab.pro_cab_numdocumento=reg.pro_cab_numdocumento
		    AND cab.pro_codigo=reg.pro_codigo
		    AND reg.pro_cab_usuario='" . pg_escape_string($usuario->obtenerUsuario()) . "'
		    AND reg.pro_cab_ip_origen='" . pg_escape_string($_SERVER['REMOTE_ADDR']) . "'
		    AND tab.tab_tabla='04'
		    AND tab.tab_elemento!='000000'
		    AND tab.tab_elemento=lpad(cab.pro_cab_moneda, 6, '0')
		    AND alma.ch_almacen=cab.pro_cab_almacen
		    AND prov.pro_codigo=cab.pro_codigo
		;
		";
	if ($sqlca->query($sql) < 0) {
	    $sql = "ROLLBACK;";
	    $sqlca->query($sql);
	    return false;
	}
	
	/* prepara el array en el que devolveremos el resultado */
	$result = Array();

	/* iteramos las cabeceras para obtener los detalles de cada una */	
	for($i = 0; $i < $sqlca->numrows(); $i++) {
	    $a = $sqlca->fetchRow();

	    $pro_cab_fecharegistro = $a[0];
	    $pro_codigo = $a[1];
	    $pro_cab_almacen = $a[2];
	    $pro_cab_tipdocumento = $a[3];
	    $pro_cab_seriedocumento = $a[4];
	    $pro_cab_numdocumento = $a[5];
	    $pro_cab_numreg = $a[6];
	    $pro_cab_rubrodoc = $a[7];
	    $pro_cab_fechaemision = $a[8];
	    $pro_cab_moneda = $a[9];
	    $pro_cab_valor = $a[10];
	    $pro_cab_impto1 = $a[11];
	    $pro_cab_imptotal = $a[12];

	    $sql = "SELECT
			cd.tran_codigo,
			cd.mov_numero,
			cd.mov_fecha,
			cd.art_codigo,
			trim(cd.com_serie_compra) as com_serie_compra,
			trim(cd.com_num_compra) as com_num_compra,
			cd.mov_tipdocuref,
			cd.mov_docurefe,
			cd.mov_cantidad,
			cd.mov_costounitario,
			round(cd.mov_costototal*(1+((select util_fn_igv())/100))) as mov_costototal,
			art.art_descripcion,
			substr(art.art_unidad, 4, 3) as art_unidad,
			util_fn_saldoalmacen(cd.art_codigo,cd.mov_almacen)  as stock,
			art.art_impuesto1 as impuesto,
			fl.pre_precio_act1 as stock
		    FROM
			inv_ta_compras_devoluciones cd,
		        int_articulos art,
		        fac_lista_precios fl
		    WHERE
			    cpag_tipo_pago='" . pg_escape_string($pro_cab_tipdocumento) . "'
		        AND cpag_serie_pago='" . pg_escape_string($pro_cab_seriedocumento) . "'
                	AND cpag_num_pago='" . pg_escape_string($pro_cab_numdocumento) . "'
		        AND mov_entidad='" . pg_escape_string(substr($pro_codigo, 0, 6)) . "'
			AND art.art_codigo=cd.art_codigo
		        AND art.art_codigo=fl.art_codigo
			AND pre_lista_precio='01'
		    ORDER BY
			cd.cpag_tipo_pago,
		        cd.mov_numero
		    ;
		    ";
	    if ($sqlca->query($sql, "_detalle") < 0) {
		$sql = "ROLLBACK;";
		$sqlca->query($sql);
		return false;
	    }

	    $detalle = Array();

	    /* iteramos las transacciones asociadas al documento */	    
	    for($o = 0; $o < $sqlca->numrows("_detalle"); $o++) {
		$a = $sqlca->fetchRow("_detalle");
		
		$tran_codigo = $a[0];
		$mov_numero = $a[1];
		$mov_fecha = $a[2];
		$art_codigo = $a[3];
		$com_serie_compra = $a[4];
		$com_num_compra = $a[5];
		$mov_tipdocuref = $a[6];
		$mov_docurefe = $a[7];
		$mov_cantidad = $a[8];
		$mov_costounitario = $a[9];
		$mov_costototal = $a[10];
		$art_descripcion = $a[11];
		$art_unidad = $a[12];
		$stock = $a[13];
		$impuesto = $a[14];
		$precio = $a[15];
		
		$key = $tran_codigo.$mov_numero.$mov_fecha;
		
		$detalle['detalle'][$key]['cabecera']['mov_numero'] = $mov_numero;
		$detalle['detalle'][$key]['cabecera']['com_num_compra'] = $com_serie_compra.$com_num_compra;
		$detalle['detalle'][$key]['cabecera']['mov_docurefe'] = $mov_tipdocuref."-".$mov_docurefe;
		$detalle['detalle'][$key]['cabecera']['mov_fecha'] = $mov_fecha;
		$detalle['detalle'][$key]['articulos'][$art_codigo]['art_codigo'] = $art_codigo;
		$detalle['detalle'][$key]['articulos'][$art_codigo]['art_descripcion'] = $art_descripcion;
		$detalle['detalle'][$key]['articulos'][$art_codigo]['com_num_compra'] = $com_serie_compra.$com_num_compra;
		$detalle['detalle'][$key]['articulos'][$art_codigo]['art_unidad'] = $art_unidad;
		$detalle['detalle'][$key]['articulos'][$art_codigo]['mov_cantidad'] = $mov_cantidad;
		$detalle['detalle'][$key]['articulos'][$art_codigo]['mov_costototal'] = $mov_costototal;
		$detalle['detalle'][$key]['articulos'][$art_codigo]['mov_costounitario'] = $mov_costounitario;
		$detalle['detalle'][$key]['articulos'][$art_codigo]['mov_precio'] = $precio;
		$detalle['detalle'][$key]['articulos'][$art_codigo]['mov_ganancia_unitaria'] = $precio-$mov_costounitario;
		$detalle['detalle'][$key]['articulos'][$art_codigo]['mov_ganancia_porcentaje'] = (($precio-$mov_costounitario)/$precio)*100;
		$detalle['detalle'][$key]['articulos'][$art_codigo]['stock'] = $stock;
		@$detalle['detalle'][$key]['total'] += $mov_costototal;
		@$detalle['total'] += $mov_costototal;
	    }

	    /* Clave para el array de documentos */
	    $key = $pro_cab_tipdocumento.$pro_cab_seriedocumento.$pro_cab_numdocumento.$pro_codigo;
	    
	    /* llena la cabecera de documentos */
	    $result['documentos'][$key] = $detalle;
	    $result['documentos'][$key]['cabecera']['pro_cab_fecharegistro'] = $pro_cab_fecharegistro;
	    $result['documentos'][$key]['cabecera']['pro_codigo'] = $pro_codigo;
	    $result['documentos'][$key]['cabecera']['pro_cab_almacen'] = $pro_cab_almacen;
	    $result['documentos'][$key]['cabecera']['pro_cab_documento'] = $pro_cab_tipdocumento.'-'.$pro_cab_seriedocumento.$pro_cab_numdocumento;
	    $result['documentos'][$key]['cabecera']['pro_cab_numreg'] = $pro_cab_numreg;
	    $result['documentos'][$key]['cabecera']['pro_cab_rubrodoc'] = $pro_cab_rubrodoc;
	    $result['documentos'][$key]['cabecera']['pro_cab_fechaemision'] = $pro_cab_fechaemision;
	    $result['documentos'][$key]['cabecera']['pro_cab_moneda'] = $pro_cab_moneda;
	    $result['documentos'][$key]['cabecera']['pro_cab_valor'] = $pro_cab_valor;
	    $result['documentos'][$key]['cabecera']['pro_cab_impuesto'] = $pro_cab_impto1;
	    $result['documentos'][$key]['cabecera']['pro_cab_imptotal'] = $pro_cab_imptotal;
	}

	/* Borrar los marcados para no volver a imprimir accidentalmente (salvo que se marquen intencionalmente) */
	$sql = "DELETE FROM
		    cpag_ta_cabecera_registro
		WHERE
			pro_cab_usuario='" . pg_escape_string($usuario->obtenerUsuario()) . "'
		    AND pro_cab_ip_origen='" . pg_escape_string($_SERVER['REMOTE_ADDR']) . "'
		;
		";
	if ($sqlca->query($sql) < 0) {
	    $sql = "ROLLBACK;";
	    $sqlca->query($sql);
	    return false;
	}

	$sql = "COMMIT;";
	$sqlca->query($sql);
	return $result;
    }
}	