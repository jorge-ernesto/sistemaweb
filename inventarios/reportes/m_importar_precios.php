<?php

class ImportarPreciosModel extends Model { 

	function buscar($archivo) {				
		$res = Array();
		$c=0;
		$row = 1;
		
		if ( $archivo ){
		      	$data1 = new Spreadsheet_Excel_Reader($archivo);  
			while( $data1 -> val($row,2,0) != null){
				$cod_pro = $data1 -> val($row,1,0);
			      	$cod_art = $data1 -> val($row,2,0);
			      	$nom_art = $data1 -> val($row,3,0);
			      	$moneda = $data1 -> val($row,4,0);
			      	$precio = $data1 -> val($row,5,0);
			      	$res[$c]['cod_pro'] = $cod_pro;
			      	$cp = ImportarPreciosModel::codProveedor($cod_pro);
			      	if(trim($cp['nompro'])!=""){
			      		$res[$c]['nom_pro'] = $cp['nompro'];
			      	} else {
			      		$res[$c]['nom_pro'] = '---';
			      	}
			      	$res[$c]['cod_art'] = $cod_art;
			      	$res[$c]['nom_art'] = $nom_art;
			      	$res[$c]['moneda'] = $moneda;
			      	$res[$c]['precio'] = $precio;			      	
			      	$row++;
			      	$c++;
			}
		} 	
		if($c==0)
			return 0;	
		return $res;	
  	}
  	
  	function filtrar($res) {
		global $sqlca;
		
		$act = 0;
		$ing = 0;
		$inv = 0;
		$actualiza = Array();
		$ingresa = Array();
		$invalido = Array();
		$totales = Array();

		for($i=0;$i<count($res);$i++) {
			$existeArticulo = ImportarPreciosModel::existeArticulo($res[$i]['cod_art']);
			$existeProveedor = ImportarPreciosModel::existeProveedor($res[$i]['cod_pro']);
			$existeArtEnPre = ImportarPreciosModel::existeArtEnPre($res[$i]['cod_art']);
							
			if($existeArtEnPre==1 and $existeProveedor==1) { // datos para actualizar
				$actualiza[$act]= $res[$i];
				$act++;
			} else {			
				if($existeArticulo==1 and $existeProveedor==1 and $existeArtEnPre==0) { // datos para ingresar
					$ingresa[$ing]= $res[$i];
					$ing++;
				} else { 	// datos invalidos
					if($existeArticulo==0) $res[$i]['cod_art'] = "---".$res[$i]['cod_art'];
					$invalido[$inv] = $res[$i];
					$inv++;
				}
			}			
		}
		$totales['actualiza'] 	= $actualiza;
		$totales['ingresa'] 	= $ingresa;
		$totales['invalido'] 	= $invalido;		

		return $totales;
	} 

	function actualizar($cod_pro, $nom_pro, $cod_art, $nom_art, $moneda, $precio, $icod_pro, $inom_pro, $icod_art, $inom_art, $imoneda, $iprecio) {
		global $sqlca;

		$res = Array();
		for($i=0;$i<count($cod_art);$i++) {
			if(trim($moneda[$i])=="SOLES")
				$money = "01";
			else
				$money = "02";
			$sql = "UPDATE 	com_rec_pre_proveedor 
				SET 	rec_precio=".$precio[$i].", 
					rec_moneda='".$money."',
					pro_codigo=int_proveedores.pro_codigo,
					rec_fecha_precio=now(),
					rec_arti_prove='".$_SESSION['auth_usuario']."'  
				FROM 	int_proveedores 
				WHERE 	com_rec_pre_proveedor.pro_codigo=int_proveedores.pro_codigo 
					AND int_proveedores.pro_ruc='".trim($cod_pro[$i])."' 
					AND com_rec_pre_proveedor.art_codigo='".trim($cod_art[$i])."' "; 
			//echo "\n".$sql;
			if($sqlca->query($sql)<0)
				return 0;
		}
		
		for($k=0; $k<count($icod_art); $k++) {
			$res['ingresa'][$k]['cod_pro'] = $icod_pro[$k];  
			$res['ingresa'][$k]['nom_pro'] = $inom_pro[$k];  
			$res['ingresa'][$k]['cod_art'] = $icod_art[$k];  
			$res['ingresa'][$k]['nom_art'] = $inom_art[$k];  
			$res['ingresa'][$k]['moneda'] = $imoneda[$k];  
			$res['ingresa'][$k]['precio'] = $iprecio[$k];  
		}		

		return $res;
	} 
	
	function insertar($cod_pro, $nom_pro, $cod_art, $nom_art, $moneda, $precio, $acod_pro, $anom_pro, $acod_art, $anom_art, $amoneda, $aprecio){
		global $sqlca;
		
		$res = Array();
		for($i=0;$i<count($cod_art);$i++) {
			if(trim($moneda[$i])=="SOLES")
				$money = "01";
			else
				$money = "02";
			$cp = ImportarPreciosModel::codProveedor($cod_pro[$i]);
			$cod_proveedor = $cp['codpro'];
			$sql = "INSERT INTO 
					com_rec_pre_proveedor 
					(	pro_codigo, 
						art_codigo, 
						rec_moneda, 
						rec_precio, 
						rec_descuento1, 
						rec_fecha_precio,
						rec_arti_prove 
					) VALUES (
						'".$cod_proveedor."', 
						'".$cod_art[$i]."', 
						'".$money."', 
						".$precio[$i].", 
						0,
						now(),
						'".$_SESSION['auth_usuario']."' 
					) "; 
			//echo "\n".$sql;
			if($sqlca->query($sql)<0)
				return 0;
		}	
		
		for($k=0; $k<count($acod_art); $k++) {
			$res['actualiza'][$k]['cod_pro'] = $acod_pro[$k];  
			$res['actualiza'][$k]['nom_pro'] = $anom_pro[$k]; 
			$res['actualiza'][$k]['cod_art'] = $acod_art[$k];  
			$res['actualiza'][$k]['nom_art'] = $anom_art[$k];  
			$res['actualiza'][$k]['moneda'] = $amoneda[$k];  
			$res['actualiza'][$k]['precio'] = $aprecio[$k];  
		}		

		return $res;		
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
	
	function codProveedor($ruc) {
		global $sqlca;

		$vec = Array();
		$sql =	"SELECT trim(pro_codigo), trim(pro_rsocialbreve) FROM int_proveedores WHERE pro_ruc='$ruc'";
		//echo "\n".$sql;
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
}
