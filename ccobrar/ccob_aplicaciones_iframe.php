<?php
require("../clases/funciones.php");
include("../cpagar/funciones.php");
$funcion = new class_funciones;

// crea la clase para controlar errores
//$clase_error = new OpensoftError;

// conectar con la base de datos
$conector_id=$funcion->conectar("","","","","");

switch($accion){

	case "completar_documento_cargo":
		$rs = pg_exec("select tb.desc_docu from  
		(select substring(trim(tab_elemento) for 2 from length(trim(tab_elemento))-1) as cod_docu
        ,tab_descripcion as desc_docu from int_tabla_general where tab_tabla ='08' ) as tb 
		where tb.cod_docu='$codigo_busqueda' ");
		
		if(pg_numrows($rs)>0){
			$desc_docu = pg_result($rs,0,0);
		 	print "<script>parent.document.all('fila_desc_documento_cargo').innerText = '$desc_docu';</script>";
		}else{
			print "<script>
				parent.document.form1.c_cod_documento_cargo.value='';
				parent.document.form1.c_cod_documento_cargo.focus();
				alert('Codigo de Documento $codigo_busqueda no existe');
				parent.document.all('fila_desc_documento_cargo').innerText = '';
				</script>";
		}
	break;
	
	case "completar_documento_abono":
	
			$rs = pg_exec("select tb.desc_docu from  
			(select substring(trim(tab_elemento) for 2 from length(trim(tab_elemento))-1) as cod_docu
        	,tab_descripcion as desc_docu from int_tabla_general where tab_tabla ='08' ) as tb 
			where tb.cod_docu='$codigo_busqueda' ");
		
			if(pg_numrows($rs)>0){
					$desc_docu = pg_result($rs,0,0);
		 			print "<script>parent.document.all('fila_desc_documento_abono').innerText = '$desc_docu';</script>";
			}else{
					print "<script>
					parent.document.form1.c_cod_documento_abono.value='';
					parent.document.form1.c_cod_documento_abono.focus();
					alert('Codigo de Documento $codigo_busqueda no existe');
					parent.document.all('fila_desc_documento_abono').innerText = '';
					</script>";
			}
	
	break;
	
	case "completar_campos_cargo":
		
		$cod_docu = $codigo_busqueda;
		$num_docu = $c_num_docu_cargo;
		//echo "cod_docu $cod_docu - num_docu $num_docu_cargo";
			$q = "select trim(cab.ch_seriedocumento)||trim(cab.ch_numdocumento) as num_documento
			, docref.desc_docu as desc_doc_ref			,	mone.desc_mone 
			, cab.ch_tipdocreferencia as cod_doc_ref    ,	cab.ch_numdocreferencia as num_doc_ref 
			, cab.nu_importetotal	as importe_inicial   
			, to_char(cab.dt_fecharegistro,'dd/mm/yyyy') as fecha_recepcion
			, to_char(cab.dt_fechaemision,'dd/mm/yyyy') as fecha_venta
			, nu_importesaldo as saldo
			, cli.cli_rsocialbreve as cliente_cargo
			, trim(cab.cli_codigo) as cod_cliente
			from ccob_ta_cabecera cab, 
			(select substring(trim(tab_elemento) for 2 from length(trim(tab_elemento))-1) as cod_docu
        	,tab_descripcion as desc_docu from int_tabla_general where tab_tabla ='08' ) as docref ,
			(select substring(trim(tab_elemento) for 2 from length(trim(tab_elemento))-1) as cod_mone
            ,tab_descripcion as desc_mone from int_tabla_general where tab_tabla ='MONE') as mone
			, int_clientes cli
			where cab.ch_tipdocreferencia	=	docref.cod_docu
			and   cab.ch_moneda				=	mone.cod_mone
			and   cab.cli_codigo			=	cli.cli_codigo
			and	  cab.ch_tipdocumento		=	'$cod_docu'
			and	  trim(cab.ch_seriedocumento)||trim(cab.ch_numdocumento)		=	'$num_docu'
			
			";
			//echo $q;
			$rs = pg_exec($q);
		
			if(pg_numrows($rs)>0){
				$A = pg_fetch_array($rs,0);
				$desc_docu = pg_result($rs,0,0);		
				print "<script>
				parent.document.all('fila_moneda_cargo').innerText = '".$A["desc_mone"]."';
				parent.document.all('fila_doc_ref_cargo').innerText = '".$A["desc_doc_ref"]."';
				parent.document.all('fila_numdoc_ref_cargo').innerText = '".$A["num_doc_ref"]."';
				parent.document.all('fila_importe_inicial_cargo').innerText = '".$A["importe_inicial"]."';
				parent.document.all('fila_fecha_recepcion_cargo').innerText = '".$A["fecha_recepcion"]."';
				parent.document.all('fila_fecha_venta_cargo').innerText = '".$A["fecha_venta"]."';
				parent.document.all('fila_saldo_cargo').innerText = '".$A["saldo"]."';
				parent.document.all('fila_cliente_cargo').innerText = '".$A["cod_cliente"]." - ".$A["cliente_cargo"]."';
				parent.document.form1.c_cod_cliente.value='".$A["cod_cliente"]."';
				parent.document.form1.c_saldo_cargo.value = '".$A["saldo"]."';
				</script>";
				// 
			}else{
				print "<script>
				parent.document.form1.c_num_documento_cargo.value='';
				parent.document.form1.c_num_documento_cargo.focus();
				alert('Numero de Documento $num_docu no existe');
				
				parent.document.all('fila_moneda_cargo').innerText = '';
				parent.document.all('fila_doc_ref_cargo').innerText = '';
				parent.document.all('fila_numdoc_ref_cargo').innerText = '';
				parent.document.all('fila_importe_inicial_cargo').innerText = '';
				parent.document.all('fila_fecha_recepcion_cargo').innerText = '';
				parent.document.all('fila_fecha_venta_cargo').innerText = '';
				parent.document.all('fila_saldo_cargo').innerText = '';
				parent.document.all('fila_cliente_cargo').innerText = '';
				
				</script>";
			}
	
	break;
	
	case "completar_campos_abono":
	
		$cod_docu = $codigo_busqueda;
		$num_docu = $c_num_docu_abono;
		$cod_cliente = $c_cod_cliente;
		//echo "cod_docu $cod_docu - num_docu $num_docu_cargo";
			$q = "select trim(cab.ch_seriedocumento)||trim(cab.ch_numdocumento) as num_documento
			, docref.desc_docu as desc_doc_ref			,	mone.desc_mone 
			, cab.ch_tipdocreferencia as cod_doc_ref    ,	cab.ch_numdocreferencia as num_doc_ref 
			, cab.nu_importetotal	as importe_inicial   
			, to_char(cab.dt_fecharegistro,'dd/mm/yyyy') as fecha_recepcion
			, to_char(cab.dt_fechaemision,'dd/mm/yyyy') as fecha_venta
			, nu_importesaldo as saldo
			, cli.cli_rsocialbreve as cliente_cargo
			, trim(cab.cli_codigo) as cod_cliente
			from ccob_ta_cabecera cab, 
			(select substring(trim(tab_elemento) for 2 from length(trim(tab_elemento))-1) as cod_docu
        	,tab_descripcion as desc_docu from int_tabla_general where tab_tabla ='08' ) as docref ,
			(select substring(trim(tab_elemento) for 2 from length(trim(tab_elemento))-1) as cod_mone
            ,tab_descripcion as desc_mone from int_tabla_general where tab_tabla ='MONE') as mone
			, int_clientes cli
			where cab.ch_tipdocreferencia	=	docref.cod_docu
			and   cab.ch_moneda				=	mone.cod_mone
			and   cab.cli_codigo			=	cli.cli_codigo
			and	  cab.ch_tipdocumento		=	'$cod_docu'
			and	  trim(cab.ch_seriedocumento)||trim(cab.ch_numdocumento)		=	'$num_docu'
			
			";
			//echo $q;
			$rs = pg_exec($q);
		
			if(pg_numrows($rs)>0){
			
				$A = pg_fetch_array($rs,0);
				$desc_docu = pg_result($rs,0,0);		
				if($A["cod_cliente"]==$cod_cliente){
					print "<script>
					parent.document.all('fila_moneda_abono').innerText = '".$A["desc_mone"]."';
					parent.document.all('fila_doc_ref_abono').innerText = '".$A["desc_doc_ref"]."';
					parent.document.all('fila_numdoc_ref_abono').innerText = '".$A["num_doc_ref"]."';
					parent.document.all('fila_importe_inicial_abono').innerText = '".$A["importe_inicial"]."';
					parent.document.all('fila_fecha_recepcion_abono').innerText = '".$A["fecha_recepcion"]."';
					//parent.document.all('fila_fecha_venta_abono').innerText = '".$A["fecha_venta"]."';
					parent.document.all('fila_saldo_abono').innerText = '".$A["saldo"]."';
					parent.document.all('fila_cliente_abono').innerText = '".$A["cliente_cargo"]."';
					parent.document.form1.c_saldo_abono.value = '".$A["saldo"]."';
					</script>";
				}else{
					print "<script>alert('El documento $num_docu pertenece al cliente ".$A["cod_cliente"]." y no al $cod_cliente');
					parent.document.form1.c_num_documento_abono.value='';
					parent.document.form1.c_num_documento_abono.focus();
					parent.document.all('fila_moneda_abono').innerText = '';
					parent.document.all('fila_doc_ref_abono').innerText = '';
					parent.document.all('fila_numdoc_ref_abono').innerText = '';
					parent.document.all('fila_importe_inicial_abono').innerText = '';
					parent.document.all('fila_fecha_recepcion_abono').innerText = '';
					//parent.document.all('fila_fecha_venta_abono').innerText = '';
					parent.document.all('fila_saldo_abono').innerText = '';
					parent.document.all('fila_cliente_abono').innerText = '';
					</script>";
					
				}
				// 
			}else{
				
				print "<script>
				parent.document.form1.c_num_documento_abono.value='';
				parent.document.form1.c_num_documento_abono.focus();
				alert('Numero de Documento $num_docu no existe');
				
				parent.document.all('fila_moneda_abono').innerText = '';
				parent.document.all('fila_doc_ref_abono').innerText = '';
				parent.document.all('fila_numdoc_ref_abono').innerText = '';
				parent.document.all('fila_importe_inicial_abono').innerText = '';
				parent.document.all('fila_fecha_recepcion_abono').innerText = '';
				//parent.document.all('fila_fecha_venta_abono').innerText = '';
				parent.document.all('fila_saldo_abono').innerText = '';
				parent.document.all('fila_cliente_abono').innerText = '';
				</script>";
				
			}
	
	break;

}

pg_close();
