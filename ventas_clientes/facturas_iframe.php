<?php
require("../clases/funciones.php");

$funcion = new class_funciones;
// crea la clase para controlar errores
$clase_error = new OpensoftError;
$clase_error->_error();
// conectar con la base de datos
$conector_id=$funcion->conectar("","","","","");


switch($opcion){

	case "Tipo_cambio":
		
		if($fecha==""){
			print "<script>alert('No se ha indicado la fecha');</script>";
		}else{
		
			$rs = pg_exec("select util_fn_tipo_cambio_dia(to_date('$fecha','dd/mm/yyyy'))");
			$A = pg_fetch_array($rs,0);
			$tpc = $A[0];
		
			print "<script>parent.document.$campo.value='$tpc';</script>";
		}
	break;	
	
	case "Articulos":
	
		$rs = pg_exec("select art.art_descripcion, art.art_codigo ,lista.pre_precio_act1
		from int_articulos art , fac_lista_precios lista
		where  art.art_codigo=lista.art_codigo and lista.pre_lista_precio='$lista_precio'
		and art.art_codigo=lpad('$codigo',13,'0') ");
		
		if(pg_numrows($rs)>0){
			$A = pg_fetch_array($rs,0);
			
			print "<script>parent.document.$campo.value='".$A["art_codigo"]."';</script>";
			print "<script>parent.document.form1.c_des_articulo_item.value='".$A["art_descripcion"]."';</script>";
			print "<script>parent.document.form1.c_precio_item.value='".$A["pre_precio_act1"]."';</script>";
			print "<script>parent.document.form1.c_cantidad_item.focus();</script>";
		}else{
		
			print "<script>parent.document.form1.$campo.value='';</script>";
			print "<script>parent.document.form1.$campo.focus();</script>";
			print "<script>alert('Codigo de Articulo no encontrado');</script>";
		
		}
	
	break;
	
}

pg_close();
