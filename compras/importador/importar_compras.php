<?php
/*
*
*			INSERT EN LA INT_NUM_DOCUMENTOS
*
*		insert into int_num_documentos (num_tipdocumento, num_seriedocumento,num_descdocumento,num_numactual,num_longdocumento, num_fecactualiz) values ('01','014','O/C ORRANTIA', 10, '0',now());
*
*		alter table com_cabecera ADD column com_factu char(7);
*		alter table com_cabecera ADD column com_ser char(3);
*
*				
*
*		crear el directorio
*
*			/sistemaweb/ordenes_compra/
*			con todos los permisos....
*
*/

extract($_REQUEST);


if($boton=="Regresar")
{
	header ('Location: /sistemaweb/compras/importador/explorer.php');
}

//include("../../valida_sess.php");
include("../../functions.php");
include("funciones_importador.php");
require("../../clases/funciones.php");
include("../../menu_princ.php");

$funcion = new class_funciones;
// crea la clase para controlar errores
$clase_error = new OpensoftError;
$clase_error->_error();
// conectar con la base de datos
$conector_id=$funcion->conectar("","","","","");


echo '<link rel="stylesheet" href="/sistemaweb/sistemaweb.css" type="text/css">';


//para trabajar en TEMPORAL durante todas las transacciones siguentes;
pg_query($conector_id, "BEGIN");

/**
*	$dir = esta variable contiene el directorio del archivo que se desee importar
* 			al sistema.
*	$file =nombre del archivo
**/
/*$dir = "/sistemaweb/ordenes_compra/";
$file = "sistemaweb.txt";
*/
/**
*	ESTE  IF  ES PARA VERIFICAR LA EXISTENCIA DEL ARCHIVO... Y SI EXISTE IMPORTA
*	LA INFORMACION CONTENIDA A LA TABLA "COMPRAS_TMP";
*
**/

if(file_exists($file) && strlen(trim($file))>0)
{
	pg_query($conector_id, "delete from compras_tmp");
	$sql = "copy compras_tmp FROM '".$file."' with delimiter as ',' null as 'null'";
	@$xsql_copy = pg_query($conector_id, $sql);

	if($xsql_copy)
	{
		$cmd = "mv ".escapeshellarg($file)." ".escapeshellarg($file).".proc";

		//echo $cmd;
		exec($cmd);
		/*if(!$exe_movi)
		{
			echo "
				<script language='javascript'>
					alert('Archivo $file \\n no pudo ser Renombrado');
				</script>";
		}*/
		//echo "IMPORTOOOO!!!!!!!";
	}else {
		echo "
			<script language='javascript'>
				alert('El archivo $file \\n esta da�do o corrupto!');
				location.href='/sistemaweb/compras/importador/explorer.php';
			</script>
			";
		pg_query($conector_id, "ROLLBACK");
		//location.href='redireccion.html';
		//header ('Location: /sistemaweb/compras/importador/explorer.php');
	}
}


if($boton=="Importar" && strlen(trim($clave))>0)
{
	$okgraba = true;

	$query = "SELECT
				RUC, ALMA, SER, FACTU,
				EMISION, BARRAS, UNID, M, PRECIO
			FROM COMPRAS_TMP
			WHERE trim(ruc)||trim(ser)||trim(factu)='$clave'";
	//echo $query;

	$xquery = pg_query($conector_id, $query);
	$i = 0;
	$rs_importar = pg_fetch_array($xquery, $i);

	//OBTENER EL CODIGO DE PROVEEDOR
	$sql = "select pro_codigo
			from int_proveedores where pro_ruc='$rs_importar[0]'";

	//echo $sql;

	$xsql = pg_query($conector_id,$sql);

	if(pg_num_rows($xsql)>0)
	{
		$cod_proveedor = trim(pg_result($xsql,0,0));
	}
	else
	{
		$okgraba = false;
		$mensaje="RUC de Proveedor \\n no Registrado";
	}

	//OBTENER EL CORRELATIVO DE LA O/C
	//$sql = "select int_sp_numero_documento_ins('01','$almacen')";
        //echo "ALMACEN : $almacen <br>";
	$sql = "select util_fn_corre_docs('01','$almacen','insert')";
        //echo "FUNCTION : $sql <br>";
	$num_orden = trim(pg_result(pg_query($conector_id, $sql),0,0));
	//echo "NUM1 : $num_orden <br>";
        $num_orden = completarCeros($num_orden,8,"0");
        //echo "NUM2 : $num_orden <br>";
	$fecha_ofrecida = $rs_importar[4];

	$ser = trim($rs_importar[2]);
	$factu = trim($rs_importar[3]);

	$sql = "SELECT tca_compra_oficial 
	        FROM int_tipo_cambio 
	        WHERE tca_moneda='$moneda' 
	        AND tca_fecha<='$fecha_ofrecida' 
	        ORDER BY tca_fecha desc LIMIT 1";
	//	echo $sql;
	$xsql = pg_query($conector_id,$sql);
	if(pg_num_rows($xsql)>0)
	{  $tipo_cambio = pg_result(pg_query($conector_id, $sql),0,0);
         echo $tipo_cambio ;
  	 }
	 else
	 {
         $tipo_cambio="0";
       }

	/* ULTIMA MODIF */
	$moneda = str_pad("0".($rs_importar[7]+0),6,"0",STR_PAD_LEFT);

	//if($tipo_cambio=='')
	//{
	//    $tipo_cambio="0";
	//}

	/* ULTIMA MODIF */

	$sql_cabecera= "INSERT INTO com_cabecera
				(PRO_CODIGO, NUM_TIPDOCUMENTO, NUM_SERIEDOCUMENTO, COM_CAB_NUMORDEN
				,COM_CAB_ALMACEN, COM_CAB_FECHAORDEN, COM_CAB_FECHAOFRECIDA, COM_CAB_MONEDA
				,COM_CAB_TIPCAMBIO,COM_CAB_CREDITO,COM_CAB_FORMAPAGO
				,COM_SER, COM_FACTU, COM_CAB_ESTADO, COM_CAB_IMPORDEN, COM_CAB_RECARGO1) values
				('$cod_proveedor','01','$almacen','$num_orden'
				,'$almacen',now(),'$fecha_ofrecida','$moneda'
				,'$tipo_cambio','N','01'
				,'$ser','$factu','1',0,0)";

	//echo "la cabecera:".$sql_cabecera;

	//Invoca una funcion para COMPROBAR si la orden de compra ya ha sido importada
	//en caso exista BORRA del compras_tmp los datos referido a esa orden
	if(comprobarCabecera($conector_id, trim($cod_proveedor).trim($ser).trim($factu),trim($rs_importar[0]).trim($ser).trim($factu)))
	{
		//echo "cabecear existe";
		$okgraba = false; $mensaje="ERROR: La orden ya \\n ha sido importada ";
	}else {
		//echo "cabecear NOOOO existe";
	  	echo "LA CABECERA".$sql_cabecera;
		$xsql_cabecera=pg_query($conector_id,$sql_cabecera);
		if(!$xsql_cabecera)
		{
			$mensaje="ERROR: \\n al ingresar cabecera ";
			$okgraba=false;
		}
	}

	//echo "antes del if";

	if($xsql_cabecera && $okgraba==true)
	{
	//	echo $sql_cabecera."<br>";
	//	echo "antes del while";
		while($i<pg_num_rows($xquery) && $okgraba==true)
		{
			$rs_importar = pg_fetch_array($xquery, $i);

			$cod_articulo = completarCeros(trim($rs_importar[5]),13,"0");
			//$cod_articulo = completarCeros(trim($cod_articulo),13,"0");
			$cant_pedida = $rs_importar[6];
			$precio = $rs_importar[8];

			$sql_detalle = "INSERT into COM_DETALLE
						(PRO_CODIGO, NUM_TIPDOCUMENTO, NUM_SERIEDOCUMENTO, COM_CAB_NUMORDEN, ART_CODIGO
						,COM_DET_CANTIDADPEDIDA, COM_DET_PRECIO, COM_DET_ESTADO) values
						('$cod_proveedor','01','$almacen','$num_orden','$cod_articulo'
						,'$cant_pedida','$precio','1')";
			//echo "<br>el detalle:".$sql_detalle;
			$xsql_detalle=pg_query($conector_id,$sql_detalle);

			if($xsql_detalle && $okgraba==true)
			{
				$clavedet = trim($clave).trim($cod_articulo);
				$sql_delete = "DELETE from compras_tmp
							WHERE trim(ruc)||trim(ser)||trim(factu)||trim(barras)='$clavedet'";
				//echo "<br>el delete:".$sql_delete."<br>";
				pg_query($conector_id, $sql_delete);
			} else {
				$okgraba=false; $mensaje="Codigo $cod_articulo del Articulo \\n No existe";
			}
			$i++;
		}
	} else{
		$okgraba=false;
	}
	/*
	*	El manejo de Errores de todo lo anterior
	*/
	if($mensaje!="" || $okgraba==false) {
		echo "<script language='javascript'>
				alert('$mensaje');
			</script>";
		pg_exec($conector_id,"ROLLBACK");
	}else {
		echo "<script language='javascript'>
				alert('Importacion Exitosa!');
			</script>";
		exec("rm ".$file);
		//$remo_exe =
		/*if(!$remo_exe)
		{
			echo "
				<script language='javascript'>
					alert('Archivo Origen \\n no pudo ser Borrado!!!');
				</script>";
		}*/
		pg_exec($conector_id,"COMMIT");
	}
}

if($boton=="Eliminar" && strlen(trim($clave))>0)
{
	$sql = "DELETE from compras_tmp
			WHERE
				trim(ruc)||trim(ser)||trim(factu)='$clave'";
	pg_exec($conector_id, $sql);
}


/*$sql = "SELECT distinct trim(ruc)||trim(ser)||trim(factu), ruc, ser, factu, emision
		FROM compras_tmp
		WHERE trim(alma)='$almacen'";*/
$sql = "
		SELECT
			distinct trim(ruc)||trim(ser)||trim(factu)
			, ruc, ser, factu, emision, pro_razsocial, tab_descripcion

		FROM int_tabla_general,compras_tmp LEFT JOIN
			int_proveedores
		ON trim(pro_ruc)=trim(ruc)

		WHERE trim(alma)='$almacen' and
		tab_tabla='MONE' and trim(tab_elemento)=lpad(trim(m),2,'0')";

//ECHO $sql;

?>
ORDENES DE COMPRAS IMPORTADAS<br>
<?php echo otorgarAlmacen($conector_id, $almacen); ?>
<table cellspacing="0" cellpadding="0">
	<tr>
		<th align="left">ARCHIVO
		<th align="left">:&nbsp;&nbsp; <?php echo $file; ?>
</table>
<form name="formular" method="POST">
<table>
	<tr>
	<td vAlign="top">
		<table border="1">
		<tr>
			<td>&nbsp;
			<th>RUC
			<th>PROVEEDOR
			<th>SER
			<th>FACTU
			<th>MONEDA
			<th>IMPORTE
			<th>EMISION
			<th>DETALLES
			<?php
			$xsql = pg_query($conector_id, $sql);
			$i=0;
			while($i<pg_num_rows($xsql))
			{
				$rs = pg_fetch_array($xsql,$i);

				$sql_importe = "select round(sum(unid*precio),2) from compras_tmp
								where
									trim(ruc)||trim(ser)||trim(factu)='$rs[0]'
								group by
									ruc, ser, factu";
				//echo $sql_importe;
				$xsql_importe = pg_query($conector_id, $sql_importe);
				$importe = pg_result($xsql_importe,0,0);

			?>
			<tr bgcolor=""
				onMouseOver="this.style.backgroundColor='#FFFFCC'; this.style.cursor='hand';"
				onMouseOut="this.style.backgroundColor=''">
			<?php
				echo "<td><input type='radio' name='clave' value='$rs[0]'>
					<td>$rs[1]
					<td>$rs[5]
					<td>$rs[2]
					<td>$rs[3]
					<td>$rs[6]
					<td>$importe
					<td>$rs[4]
					<th><a href='preview_ordenes.php?clave=$rs[0]' target='detalle'><img src='document.png' width=16 height=16 border=0></a>";
				$i++;
			}
			?>
		<tr>
			<td>
			<th><input type="submit" name="boton" value="Importar">
			<th><input type="submit" name="boton" value="Eliminar">
			<th><input type="submit" name="boton" value="Regresar">
			<th>
		</table>
	</td>
	<tr>
	<table border="1" bgcolor="">
		<tr>
			<th width="110" bgcolor="">CODIGO
			<th width="230" bgcolor="">DESCRIPCION
			<th width="60" bgcolor="">CANT.
			<th width="60" bgcolor="">PRECIO
			<th width="60" bgcolor="">IMPORTE
			<th width="10" bgcolor="">&nbsp;
		<tr>
			<th colspan="6"><iframe name="detalle" width="600" height="400" src="preview_ordenes.php?visualizar=no" frameborder="1" style="border: 1px solid; border-color: #959672"></IFRAME></th>
	</table>
	</td>
</table>
</form>
<?php
pg_exec($conector_id, "COMMIT");
pg_close($conector_id);
