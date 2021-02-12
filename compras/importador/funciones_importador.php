<?php
/**
*
*	Funcion que devuelve TRUE si la Orden de Compra existe.
*	o FALSE en el otro caso
*
*/

function comprobarCabecera($conector_id, $clave, $clave_ruc)
{
	$query = "select com_cab_numorden
			FROM com_cabecera
			WHERE trim(pro_codigo)||trim(com_ser)||trim(com_factu)='$clave'";
	$xquery = pg_query($conector_id, $query);
	if(pg_num_rows($xquery)>0)
	{
		$resultado=true;
		pg_query($conector_id, "DELETE FROM compras_tmp
							WHERE trim(ruc)||trim(ser)||trim(factu)='$clave_ruc'");
		pg_query($conector_id, "COMMIT");
		pg_query($conector_id, "BEGIN");
	}else{
		$resultado=false;
	}
	return $resultado;
}

function descripcionArticulo($conector_id, $cod_articulo)
{
	$sql = "select art_descripcion from int_articulos where art_codigo='$cod_articulo'";
	$xsql = pg_query($conector_id, $sql);
	if(pg_num_rows($xsql)>0) {
		$result = pg_result($xsql,0,0);
	} else {
		$result = "<font color='red'><strong>Codigo NO Registrado</strong></font>";
	}
	return $result;
}

