<?php

class FacturasElectronicasModel extends Model { 
  	
  	function filtrar($res, $numero) {
		global $sqlca;
		
		$ing = 0;
		$inv = 0;
		$ingresa = Array();
		$invalido = Array();
		$totales = Array();
		$docurefe = $numero[2].substr($numero[3],0,-4);
		$pro1 = $res[0][0];

		for($i=0;$i<count($res)-1;$i++) {
			if(trim($res[$i][0]) != trim($pro1)){
				return 5;
			}
					
			$existeArticulo = FacturasElectronicasModel::existeArticulo($res[$i][4]);
			$existeProveedor = FacturasElectronicasModel::existeProveedor($res[$i][0]);
			$existeArtEnPre = FacturasElectronicasModel::existeArtEnPre($res[$i][4]);
							
			$cp = FacturasElectronicasModel::codProveedor($res[$i][0]);
		      	if(trim($cp['nompro'])!=""){
		      		$res[$i][9] = $cp['nompro'];
		      	} else {
		      		$res[$i][9] = '---';
		      	}						
							
			if($existeArtEnPre==1 and $existeProveedor==1) { // datos para ingresar
				$res[$i][7] = FacturasElectronicasModel::obtieneCostoUnitario($res[$i][4]);
				$res[$i][10] = $res[$i][6]*$res[$i][7];
				$ingresa[$ing]= $res[$i];
				$ing++;
			} else {	
				if($existeArticulo==0) $res[$i][4] = "---".$res[$i][4];	
				$res[$i][10] = 0;
				$invalido[$inv] = $res[$i];
				$inv++;				
			}			
		}
		
		// inicializando variables
		$q ="SELECT tran_nform, (util_fn_fechaactual_aprosys() + current_time) FROM inv_tipotransa WHERE tran_codigo='01';";
		if($sqlca->query($q)<0) return false;
		$qq = $sqlca->fetchRow();
		$numactfac = $qq[0];
		$fecha = $qq[1];
		$fechav = substr($qq[1],8,2)."/".substr($qq[1],5,2)."/".substr($qq[1],0,4);
		$nro_mov = $numactfac+1;
		$nro_mov = FacturasElectronicasModel::completarCeros($nro_mov,7,"0");
		$nro_mov = @$ingresa[0][3].$nro_mov;
		// numero de orden de compra
		$nroorden = FacturasElectronicasModel::numeroOrden("01", trim(@$ingresa[0][3]), "select");
		$nroorden = FacturasElectronicasModel::completarCeros($nroorden,8,"0");
	
		$totales['ingresa'] 	= $ingresa;
		$totales['invalido'] 	= $invalido;
		$totales['numfactura'] 	= $nro_mov;
		$totales['fecha'] 	= $fecha;
		$totales['fechav'] 	= $fechav;
		$totales['almacen'] 	= @$ingresa[0][3];
		
		$f = explode("-", @$ingresa[0][2]);		
		$totales['fechatxtv'] 	= $f[0]."/".$f[1]."/".$f[2];
		
		$totales['docurefe'] 	= $docurefe;
		$totales['nroorden'] 	= $nroorden;
		$cp = FacturasElectronicasModel::codProveedor(@$ingresa[0][0]);
		$totales['codigopro']	= $cp['codpro'];
		$totales['nombrepro'] 	= $cp['nompro'];

		return $totales;
	} 
	
	function ingresar($almacen, $numfactura, $docurefe, $cpagar, $movfecha, $proveedor, $tipodocu, $filas){
		global $sqlca;

		$ao = "SELECT tran_origen, trim(tran_naturaleza) FROM inv_tipotransa WHERE tran_codigo='01';";
		if($sqlca->query($ao)<0)
			return false;
		$a = $sqlca->fetchRow();
		$almaori = $a[0];
		$natu = $a[1];
		
		$items = Array();		
		for($k=0; $k<count($filas['cod_art']); $k++) {			
			$items[$k][0] = "01"; 			// inv_movialma.tran_codigo
			$items[$k][1] = $filas['cod_art'][$k]; 	// inv_movialma.art_codigo
			$items[$k][2] = $movfecha; 		// inv_movialma.mov_fecha
			$items[$k][3] = $almacen; 		// inv_movialma.mov_almacen
			$items[$k][4] = $almaori; 		// inv_movialma.mov_almaorigen
			$items[$k][5] = $almacen; 		// inv_movialma.mov_almadestino
			$items[$k][6] = $natu; 			// inv_movialma.mov_naturaleza
			$items[$k][7] = $tipodocu; 		// inv_movialma.mov_tipdocuref
			$items[$k][8] = $docurefe; 		// inv_movialma.mov_docurefe
			$items[$k][9] = $proveedor; 		// inv_movialma.mov_entidad
			$items[$k][10] = $filas['cantidad'][$k];// inv_movialma.mov_cantidad
			$items[$k][11] = $filas['precio'][$k]; 	// inv_movialma.mov_costounitario
			$items[$k][12] = $filas['valtot'][$k]; 	// inv_movialma.mov_costototal		
		}		
		
		// insertar en inv_movialma
		for($i=0; $i<count($items); $i++) {
			// orden de compra
			if($i==0) {
				$nro_orden = FacturasElectronicasModel::crearOrdendeCompra($items); 					
				$nro_mov = FacturasElectronicasModel::incrementarCorrelativo("01");
				$nro_mov_roca = $nro_mov;
			} else {
				$nro_mov = $nro_mov_roca;
			}
		
			$nro_mov = FacturasElectronicasModel::completarCeros($nro_mov,7,"0");
			$nro_mov = $almacen.$nro_mov;
			// end orden de compra		
				
			$A = $items[$i];		
			$sql = "INSERT INTO 
					inv_movialma 
					(
						mov_numero,
						tran_codigo,
						art_codigo,
						mov_fecha,
						mov_almacen,
						mov_almaorigen,
						mov_almadestino,
						mov_naturaleza,						
						mov_tipdocuref,
						mov_docurefe,
						mov_entidad,						
						mov_cantidad,
						mov_costounitario,
						mov_costototal,						
						com_tipo_compra,
						com_serie_compra,
						com_num_compra,						
						mov_usuario
					) VALUES (
						'$nro_mov',
						'".$A[0]."',
						'".$A[1]."',
						'".$A[2]."',
						'".$A[3]."',
						'".$A[4]."',
						'".$A[5]."',
						'".$A[6]."',
						'".$A[7]."',
						'".$A[8]."',
						'".$A[9]."',
						".$A[10].",
						".$A[11].",
						".$A[12].",
						'01',
						'".$A[3]."',
						'$nro_orden',
						'".$_SESSION['auth_usuario']."' 
					) "; 
					
			echo "\n\n".$sql;
			if($sqlca->query($sql)<0)
				return false;
		}		

		return 1;		
	} 
	
	function crearOrdendeCompra($items) {
		global $sqlca;
		
		$igv = FacturasElectronicasModel::sacarIgv();	
		
		for($i=0;$i<count($items);$i++){	
			$A = $items[$i];
			
			$com_det_precio = (1+$igv)*$A[11];
			$com_det_imparticulo = $com_det_precio * $A[10];
			$com_det_impuesto1 = $A[12] * $igv;	
				
			if($i==0){
				$nro_orden = FacturasElectronicasModel::numeroOrden("01", trim($A[3]), "insert");
				$nro_orden = FacturasElectronicasModel::completarCeros($nro_orden,8,"0");
			}		
		
			$q_cab = "INSERT INTO 
					com_cabecera
					(
						pro_codigo,
						num_tipdocumento,
						num_seriedocumento,
						com_cab_numorden,
						com_cab_almacen,
						com_cab_fechaorden,
						com_cab_fechaofrecida,
						com_cab_fecharecibida,
						com_cab_tipcambio,
						com_cab_credito,
						com_cab_formapago,
						com_cab_imporden,
						com_cab_recargo1,
						com_cab_estado,
						com_cab_transmision,
						com_cab_moneda
					) 
				VALUES 
					(
						'".$A[9]."',
						'01',
						'".$A[3]."',
						'$nro_orden',
						'".$A[3]."',
						'".$A[2]."',
						'".$A[2]."',
						'".$A[2]."',
						0,
						'N',
						'01',
						0,
						0,
						'2',
						't',
						'000001'
					)";
	
			$q_det = "INSERT INTO 
					com_detalle 
					(
						pro_codigo,
						num_tipdocumento,
						num_seriedocumento,
						com_cab_numorden,
						art_codigo,
						com_det_fechaentrega,
						com_det_cantidadpedida,
						com_det_cantidadatendida,
						com_det_precio,
						com_det_imparticulo,
						com_det_descuento1,
						com_det_estado,
						com_det_cd_impuesto1,
						com_det_impuesto1
					) 
				VALUES 
					(
						'".$A[9]."',
						'01',
						'".$A[3]."',
						'$nro_orden',
						'".$A[1]."',
						'".$A[2]."',
						".$A[10].",
						".$A[10].",
						$com_det_precio,
						$com_det_imparticulo,
						0,
						'2',
						'01',
						$com_det_impuesto1
					)";
	
	
			if($i==0){ 
				echo "\n\n".$q_cab;
				if($sqlca->query($q_cab)<0)
					return false;
			}
			echo "\n\n".$q_det;
			if($sqlca->query($q_det)<0)
				return false;			
		}
	
		return $nro_orden;
	}
	
	function cpagar($almacen, $numfactura, $docurefe, $proveedor, $nroorden, $regcompra){
		global $sqlca;
		
		$flag = FacturasElectronicasModel::validaCPagar($regcompra['rgtipodocu'], $regcompra['rgseriedocu'], $regcompra['rgnumerodocu'], $proveedor);
		
		if($flag==1) {
			return 0;
		}
		
		$tipocambio   = $regcompra['rgtcambio'];		
		$plc_codigo = "42101";	
		if($regcompra['rgmoneda']=="02") {
			$plc_codigo="42102";
		}
			
		$cp = "INSERT INTO 
				cpag_ta_cabecera
				(
					pro_cab_tipdocumento,
					pro_cab_seriedocumento,
					pro_cab_numdocumento,
					pro_codigo,
					pro_cab_fechaemision,
					pro_cab_fecharegistro,
					pro_cab_fechavencimiento,
					pro_cab_tipcontable,
					plc_codigo,
					pro_cab_imptotal,
					pro_cab_impsaldo,
					pro_cab_fechasaldo,
					pro_cab_tipdocreferencia,
					pro_cab_numdocreferencia,
					pro_cab_almacen,
					pro_cab_impafecto,
					pro_cab_tipimpto1,
					pro_cab_impto1,
					pro_cab_rubrodoc,
					com_cab_numorden,
					pro_cab_moneda,
					pro_cab_tcambio,
					pro_cab_impinafecto 
				) 
				VALUES 
				(
					'".$regcompra['rgtipodocu']."',
					'".$regcompra['rgseriedocu']."',
					'".$regcompra['rgnumerodocu']."',
					'".$proveedor."',					
					to_date('".$regcompra['rgfechadocumento']."', 'DD/MM/YYYY'),
					'".$regcompra['rgfechasistema']."',
					to_date('".$regcompra['rgvencimiento']."', 'DD/MM/YYYY'),
					UTIL_FN_TIPO_ACCION_CONTABLE('CP',lpad('".$regcompra['rgtipodocu']."',6,'0')),
					'$plc_codigo',
					".$regcompra['rgvtotal'].",
					".$regcompra['rgvtotal'].",
					'".$regcompra['rgfechasistema']."',
					'01',
					'".$regcompra['rgnumerodocu']."',
					'".$almacen."',
					".$regcompra['rgvventa'].",
					'09',
					".$regcompra['rgimpuesto'].",
					'".$regcompra['rgrubro']."',
					'$nroorden',
					'".$regcompra['rgmoneda']."',
					$tipocambio,
					".$regcompra['rgimpinafecto']."	
				) ";
				
		echo "\n\n".$cp;
		if($sqlca->query($cp)<0)
			return false;
	
		$dp = "INSERT INTO 
				cpag_ta_detalle
				(
					pro_cab_tipdocumento,
					pro_cab_seriedocumento,
					pro_cab_numdocumento,
					pro_codigo,
					pro_det_identidad,
					pro_det_tipmovimiento,
					pro_det_fechamovimiento, 
					pro_det_moneda, 
					pro_det_tcambio,
					pro_det_impmovimiento,
					pro_det_tipdocreferencia, 
					pro_det_numdocreferencia, 
					pro_det_almacen
				) 
				VALUES 
				(
					'".$regcompra['rgtipodocu']."',
					'".$regcompra['rgseriedocu']."',
					'".$regcompra['rgnumerodocu']."',
					'".$proveedor."',
					'1',
					'1',
					now(), 
					'".$regcompra['rgmoneda']."', 
					$tipocambio,
					".$regcompra['rgvtotal'].",
					'01',
					'$nroorden',
					'".$almacen."'
				)";
	
		
		echo "\n\n".$dp;
		if($sqlca->query($dp)<0)
			return false;
				
		

		
		return 1;		
	} 
	
	function actCompraDev($tipodocu, $seriedocu, $numdocu, $numfactura){
		global $sqlca;
		
		// Actualiza tabla inv_ta_compras_devoluciones
		$sql = "UPDATE 
				inv_ta_compras_devoluciones 
			SET 
				cpag_tipo_pago = '$tipodocu', 
				cpag_serie_pago = '$seriedocu', 
				cpag_num_pago = '$numdocu' 
			WHERE 
				mov_numero = '$numfactura'
				AND trim(tran_codigo)= '01';";
			
		if($sqlca->query($sql)<0)
			return false;
	}
	
	function incrementarCorrelativo($cod_transa){
		$rs = pg_exec("select UTIL_FN_CORRE_FORM('$cod_transa','insert')");
		$A = pg_fetch_array($rs,0);
		$r = $A[0];
	
		return $r;
	}
	
	function numeroOrden($tipo_docu,$serie,$accion){
		$serie = trim($serie);
		$q = "select UTIL_FN_CORRE_DOCS('$tipo_docu','$serie','$accion')";
		$rs = pg_exec($q);
		$A = pg_fetch_array($rs,0);
		$n = $A[0];
		
		return $n;
	}
	
	function sacarIgv(){
		$rs = pg_exec("SELECT tab_num_01 FROM int_tabla_general WHERE tab_tabla='IGV'");
		$A = pg_fetch_array($rs,0);
		$igv = $A[0];
		$igv = $igv/100;
	
		return $igv;
	}
	
	function completarCeros($cadena, $long_final, $complemento){	
		$long_inicial = strlen($cadena);
		for($i=0;$i<$long_final - $long_inicial;$i++){
			$cadena = $complemento.$cadena ;
		}
		return $cadena;

	}
	
	function existeArticulo($articulo) {
		global $sqlca;

		$sql =	"SELECT 1 FROM int_articulos WHERE art_codigo='$articulo'";
		if($sqlca->query($sql)<0)
			return false;
		$a = $sqlca->fetchRow();
		if($a[0]==1)
			return 1;
		else
			return 0;
	}
	
	function existeProveedor($proveedor) {
		global $sqlca;

		$sql =	"SELECT 1 FROM int_proveedores WHERE pro_ruc='$proveedor'";
		if($sqlca->query($sql)<0)
			return false;
		$a = $sqlca->fetchRow();
		if($a[0]==1)
			return 1;
		else
			return 0;
	}
	
	function existeArtEnPre($articulo) {
		global $sqlca;

		$sql =	"SELECT 1 FROM com_rec_pre_proveedor WHERE art_codigo='$articulo'";
		if($sqlca->query($sql)<0)
			return false;
		$a = $sqlca->fetchRow();
		if($a[0]==1)
			return 1;
		else
			return 0;
	}
	
	function obtieneCostoUnitario($articulo) {
		global $sqlca;

		$sql =	"SELECT rec_precio FROM com_rec_pre_proveedor WHERE art_codigo='$articulo'";
		if($sqlca->query($sql)<0)
			return false;
		$a = $sqlca->fetchRow();
		
		return $a[0];
	}
	
	function codProveedor($ruc) {
		global $sqlca;

		$vec = Array();
		$sql =	"SELECT trim(pro_codigo), trim(pro_rsocialbreve) FROM int_proveedores WHERE pro_ruc='$ruc'";
		if($sqlca->query($sql)<0)
			return false;
		$a = $sqlca->fetchRow();
		$vec['codpro'] = $a[0];
		$vec['nompro'] = $a[1];
		
		return $vec;
	}
	
	function verificaPrecio() {
		global $sqlca;
		
		$sql = "SELECT par_valor FROM int_parametros WHERE par_nombre='precio_centralizado';";
		if($sqlca->query($sql)<0)
			return false;
		$a = $sqlca->fetchRow();
		if($a=="1")
			return "SI";
		else
			return "NO";
	}	
	
	function obtieneListaEstaciones($almacen) {
		global $sqlca;
	
		$sql = "SELECT 
				ch_almacen, 
				trim(ch_nombre_almacen)
			FROM 
				inv_ta_almacenes
			WHERE 
				ch_clase_almacen='1' AND ch_almacen='$almacen' ";

		if ($sqlca->query($sql) < 0) 
			return false;	

		$result = Array();	
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$result[$a[0]] = $a[0]." - ".$a[1];
		}	
		return $result;
	}
	
	function obtieneTipoDocus() {
		global $sqlca;
	
		$sql = "SELECT 
				substring(tab_elemento from 5 for 2),
				tab_descripcion 
			FROM 
				int_tabla_general 
			WHERE 
				tab_tabla='08' AND (trim(tab_car_03)!='' OR tab_car_03 is not null)
			ORDER BY 
				tab_descripcion;";

		if ($sqlca->query($sql) < 0) 
			return false;	

		$result = Array();	
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$result[$a[0]] = $a[0]." - ".$a[1];
		}	
		return $result;
	}	
	
	function obtieneRubros() {
		global $sqlca;
	
		$sql = "SELECT 
				trim(tab_elemento),
				trim(tab_descripcion) 
			FROM 
				int_tabla_general 
			WHERE 
				tab_tabla='RCPG' and tab_elemento!='000000' 
			ORDER BY 
				tab_descripcion;";

		if ($sqlca->query($sql) < 0) 
			return false;	

		$result = Array();	
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$result[$a[0]] = $a[0]." - ".$a[1];
		}	
		return $result;
	}
	
	function obtieneMonedas() {
		global $sqlca;
	
		$sql = "SELECT 
				substring(tab_elemento from 5 for 2),
				tab_descripcion 
			FROM 
				int_tabla_general 
			WHERE 
				tab_tabla='04' and tab_elemento!='000000' 
			ORDER BY 
				tab_elemento;";

		if ($sqlca->query($sql) < 0) 
			return false;	

		$result = Array();	
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$result[$a[0]] = $a[0]." - ".$a[1];
		}	
		return $result;
	}
	
	function obtieneTipoCambio($dia) {
		global $sqlca;
	
		$sql = "SELECT tca_compra_oficial FROM int_tipo_cambio WHERE tca_fecha='$dia';";
		if ($sqlca->query($sql) < 0) 
			return false;	
		$a = $sqlca->fetchRow();
		
		return $a[0];
	}
	
	function validaCPagar($tipdocu, $seriedocu, $numdocu, $proveedor) {
		global $sqlca;
	
		$sql = "SELECT 1 FROM cpag_ta_cabecera 
			WHERE 
				pro_cab_tipdocumento='$tipdocu' 
				AND pro_cab_seriedocumento='$seriedocu' 
				AND pro_cab_numdocumento='$numdocu' 
				AND pro_codigo='$proveedor';";
		if ($sqlca->query($sql) < 0) 
			return false;	
		$a = $sqlca->fetchRow();

		if($a[0]=="1")
			return 1;
		else
			return 0;
	}
}
