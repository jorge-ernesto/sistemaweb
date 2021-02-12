<?php

ini_set("upload_max_filesize", "15M"); 

class ListaComprasModel extends Model { // Poder agregar eliminar editar proveedor, producto, precio

	function extension($archivo){
		$partes = explode(".", $archivo);
		$extension = end($partes);

		return $extension;
	}

	function mostrar($proveedor, $articulo) {
		global $sqlca;

		$articulo = str_replace(","," AS bpchar),13,'0')), (LPAD(CAST(", $articulo);

		$query = "
				SELECT
					p.pro_codigo,
					prov.pro_razsocial,
					p.art_codigo,
					a.art_descripcion,
					p.rec_precio,
					p.rec_moneda,
					to_char(rec_fecha_precio,'dd/mm/yyyy')
				FROM
					com_rec_pre_proveedor p
					LEFT JOIN int_proveedores prov on (p.pro_codigo=prov.pro_codigo)
					LEFT JOIN int_articulos a on (p.art_codigo=a.art_codigo)
				WHERE 	
					1=1
		";

		if ($proveedor != '') 
			$query .= "	AND p.pro_codigo = '".pg_escape_string($proveedor)."'";
		
		if ($articulo != '') 
			$query .= "	AND p.art_codigo IN ((LPAD(CAST('" . trim($articulo) . "' AS bpchar),13,'0')))";

		$query .= " ORDER BY 2, 4";

		if ($sqlca->query($query)<=0)
			return $sqlca->get_error();
	    
		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {

			$a = $sqlca->fetchRow();

			$resultado[$i]['cod_proveedor']	= $a[0];
			$resultado[$i]['nom_proveedor']	= $a[1];
			$resultado[$i]['cod_articulo'] 	= $a[2];
			$resultado[$i]['nom_articulo'] 	= $a[3];
			$resultado[$i]['precio'] 	= $a[4];		
			$resultado[$i]['moneda'] 	= $a[5];
			$resultado[$i]['ultima_compra'] = $a[6];

		}
		
		return $resultado;
  	}

	function ingresar($tipo, $proveedor, $articulo, $precio, $moneda, $rec_arti_prove, $usuario, $ip){ // $tipo:  "A" (agregar) -  "E" (editar)
		global $sqlca;

		$sql =	"	SELECT		art_codigo						
				FROM		int_articulos
				WHERE 		art_codigo='$articulo';";

		$sqlca->query($sql);

		if ($sqlca->numrows()==0)
			return 2;

		$sql2 =	"	SELECT		pro_codigo						
				FROM		int_proveedores
				WHERE 		pro_codigo='$proveedor';";

		$sqlca->query($sql2);
		if ($sqlca->numrows()==0)
			return 3;

//rec_arti_prove este campo es para guardar los articulos del proveedor.

		if ($tipo == "A") {
			$valida = ListaComprasModel::validar($proveedor, $articulo);	

			if ($valida == 1)
				return 4;

			$query = "	INSERT INTO	
							com_rec_pre_proveedor 
						(		
							pro_codigo, 
							art_codigo, 
							rec_moneda, 
							rec_precio, 
							rec_descuento1, 
							rec_fecha_precio,
							rec_fecha_ultima_compra,
							rec_arti_prove,
							rec_usuario,
							rec_ip
						)
						VALUES 
						(		
							'".trim($proveedor)."',
							'".trim($articulo)."',
							'".trim($moneda)."',
							".trim($precio).", 
							0, 
							now(),
							now(),
							'".trim($rec_arti_prove)."',
							'".trim($usuario)."',
							'".trim($ip)."'
						) ";

			echo "-- Sentencia INSERT: ".$query.' --';

			$sqlca->query($query);
			return 1;

		} else { 
			$edita = ListaComprasModel::editar($proveedor, $articulo, $precio, $moneda, $rec_arti_prove, $usuario, $ip);		
			return 1;
		}		
	} 

	function eliminar($proveedor, $articulo) {
		global $sqlca;

		$sql = "
			DELETE FROM
				com_rec_pre_proveedor 
			WHERE
				pro_codigo	= '".trim($proveedor)."' 
				AND art_codigo	= '".trim($articulo)."';
		";

		$sqlca->query($sql);
		return 1;
 	}

	function editar($proveedor, $articulo, $precio, $moneda, $rec_arti_prove, $usuario, $ip) {
		global $sqlca;

		$sql = "
			UPDATE
				com_rec_pre_proveedor
			SET
				pro_codigo		= '".trim($proveedor)."', 
				rec_precio		= ".trim($precio).",
				rec_moneda		= '".trim($moneda)."', 
				rec_fecha_precio	= now(),
				rec_arti_prove		= '".trim($rec_arti_prove)."',
				rec_usuario		= '".trim($usuario)."',
				rec_ip			= '".trim($ip)."'
			WHERE
				art_codigo	= '".trim($articulo)."'
				AND pro_codigo	= '".trim($proveedor)."';
		";

		$sqlca->query($sql);	
		return 1;
	} 

	function validar($proveedor, $articulo) {
		global $sqlca;

		$sql3 =	"
				SELECT
					art_codigo						
				FROM
					com_rec_pre_proveedor
				WHERE
					art_codigo = '".trim($articulo)."'
					AND pro_codigo = '".trim($proveedor)."';";

		$sqlca->query($sql3);
		if ($sqlca->numrows()==1)
			return 1;
		else
			return 0;
	}

	function InsertarExcel($data, $usuario, $ip, $codproveedor, $codmoneda){
		global $sqlca;

		$resultados 	= count($data->sheets[0]['cells']);
		$codigoexcel	= '';

		$a = 0;
		$b = 0;
		$c = 0;

		for ($i = 6; $i <= ($resultados + 1); $i++) {

			$correlativo	= $data->sheets[0]['cells'][$i][1];
			$codigo		= $data->sheets[0]['cells'][$i][2];
			$descripcion	= $data->sheets[0]['cells'][$i][3];
			$costo		= $data->sheets[0]['cells'][$i][4];

			if (strlen($codigo) > 0 && strlen($costo) > 0){

				//VALIDAR ARCHIVO EXCEL PRODUCTOS QUE EXISTAN B.D
				$datos	= ListaComprasModel::validarExcel($codigo, $codproveedor);


				if($codigoexcel == $codigo){
					//DUPLICADOS CODIGO DE ARTICULOS DEL EXCEL
				} elseif($datos[0]['existe'] == '0' && $datos[0]['costo'] == NULL){

					$a++;//CANTIDAD DE PRODUCTOS NO INSERTADOS

				} elseif($datos[0]['existe'] >= '1' && $datos[0]['costo'] == NULL) {

					$b++;//CANTIDAD DE PRODUCTOS INSERTADOS

					$codigob .= $codigo.",";


					$sql = "INSERT INTO	
							com_rec_pre_proveedor 
						(		
							pro_codigo, 
							art_codigo, 
							rec_moneda, 
							rec_precio, 
							rec_descuento1, 
							rec_fecha_precio,
							rec_fecha_ultima_compra,
							rec_usuario,
							rec_ip
						)
						VALUES 
						(		
							'".trim($codproveedor)."',
							(LPAD(CAST('".trim($codigo)."' AS bpchar),13,'0')),
							'".trim($codmoneda)."',
							".trim($costo).", 
							0, 
							now(),
							now(),
							'".trim($usuario)."',
							'".trim($ip)."'
						) ";

					//echo "INSERTAR: \n".$sql."\n";					

					if ($sqlca->query($sql) < 0)
						return false;

				} else {

					$c++;//CANTIDAD DE PRODUCTOS ACTUALIZADOS

					$sql = "
						UPDATE
							com_rec_pre_proveedor
						SET
							rec_usuario		= '".trim($usuario)."',
							rec_ip			= '".trim($ip)."',
							rec_precio		= ".trim($costo).",
							rec_fecha_ultima_compra = now()
						WHERE
							art_codigo 		= (LPAD(CAST('".trim($codigo)."' AS bpchar),13,'0'))
							AND pro_codigo  	= '".trim($codproveedor)."';
						";
				
					//echo "ACTUALIZAR: \n".$sql."\n";

					if ($sqlca->query($sql) < 0)
						return false;

				}

				$codigoexcel = $codigo;

			}

		}

		return array(true, $a, $b, $c, $codigob);

	}

	/* VALIDACIONES */

	function validarExcel($codigo, $proveedor) {
		global $sqlca;

		$sql ="
		SELECT
			count(*) existe,
			(SELECT art_codigo FROM int_articulos WHERE art_codigo = (LPAD(CAST('".trim($codigo)."' AS bpchar),13,'0'))) codigo,	
			(SELECT art_descripcion FROM int_articulos WHERE art_codigo = (LPAD(CAST('".trim($codigo)."' AS bpchar),13,'0'))) descripcion,
			(SELECT rec_precio FROM com_rec_pre_proveedor WHERE pro_codigo = '".trim($proveedor)."' AND art_codigo = (LPAD(CAST('".trim($codigo)."' AS bpchar),13,'0'))) costo,
			(SELECT rec_fecha_ultima_compra FROM com_rec_pre_proveedor WHERE pro_codigo = '".trim($proveedor)."' AND art_codigo = (LPAD(CAST('".trim($codigo)."' AS bpchar),13,'0'))) fecha
		FROM
			int_proveedores
		WHERE
			pro_codigo = '".trim($proveedor)."'
		";

		$sqlca->query($sql);

		$data = Array();

		if ($sqlca->numrows()==1){
			$data = $sqlca->fetchRow();
			return array($data);
		}
	}

	function getProveedor($iCodigoProveedor){
		global $sqlca;
		
		$sql = "SELECT pro_razsocial FROM int_proveedores WHERE pro_codigo =  '" .  trim($iCodigoProveedor) . "'";
echo $sql;
		$sqlca->query($sql);

		if($sqlca->numrows()==1){
			$data = $sqlca->fetchRow();
			return $data[0];
		}
		return false;
	}

	function FechaSistema() {
		global $sqlca;

		$sql = "SELECT da_fecha fecha FROM pos_aprosys WHERE ch_poscd = 'A';";

		$sqlca->query($sql);

		$data = $sqlca->fetchRow();

		return $data['fecha'];

	}

	function validaDia($dia, $almacen) {
		global $sqlca;

		$turno = 0;

		$sql = " SELECT validar_consolidacion('$dia',$turno,'$almacen') ";

		$sqlca->query($sql);

		$estado = $sqlca->fetchRow();

		if($estado[0] == 1){
			return 1;//Consolidado
		}else{
			return 0;//No consolidado
		}

	}

}

