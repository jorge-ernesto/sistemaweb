<?php
function lineas_venesp($cod_almacen,$ordx,$cond,$AR,$fechad,$fechaa,$xdia){
	$fechad = "to_date('$fechad','dd/mm/yyyy')";
	$fechaa = "to_date('$fechaa','dd/mm/yyyy')";
	if($cond==""){
		if($xdia!="xdia"){
		$rs = pg_exec("select distinct linea, des_linea from  VEN_VS_VENTAS_ESPECIALES2");  
			for($i=0;$i<pg_numrows($rs);$i++){
				echo "for xdia";
				$A = pg_fetch_array($rs,$i);
				$lin = $A[0];
				pg_exec("select VEN_FN_REP_VENTAS_ESPECIALES('$lin',$fechad,$fechaa,null,null) ");
				echo "xdia "."select VEN_FN_REP_VENTAS_ESPECIALES('$lin',$fechad,$fechaa,null,null)"." aaaaaaaa";
			}
		}else{
				
				$rs = pg_exec("select distinct linea, des_linea from  VEN_VS_VENTAS_ESPECIALES2");
				for($i=0;$i<pg_numrows($rs);$i++){
				$A = pg_fetch_array($rs,$i);
				$lin = $A[0];
				pg_exec("select VEN_FN_REP_VENTAS_ESPECIALES('$lin',$fechad,$fechaa,null,true) ");
				echo "elsexdia "."select VEN_FN_REP_VENTAS_ESPECIALES('$lin',$fechad,$fechaa,null,true)"." aaaaaaaa";
				}
		}
		
	}else{
		if($xdia!="xdia"){ 
			$xdia = "null";
			echo "xdia es null";
		}else{
			$xdia = "true"; 
			echo "xdia es true";
		}
		llenar_tmp_ventas_especiales($cond , $AR, $fechad, $fechaa ,$xdia);
	
	}
	$q = "select distinct linea, des_linea from tmp_ventas_especiales order by des_linea";
	if($cod_almacen!=""){
	$q = "select distinct linea, des_linea,sum(importe) as cant  from tmp_ventas_especiales 
	where alma='$cod_almacen' group by linea, des_linea order by cant desc";
	}
	if($ordx=="total"){
	$q = "select linea,des_linea,sum(importe) as cant from tmp_ventas_especiales 
	group by linea,des_linea order by cant desc";
	}
	$rs = pg_exec($q);
	return $rs;
}

function articulos_venesp($cod_linea,$cod_almacen,$ordx,$xdia){
	if($xdia=="xdia"){
		$campo = " , dt_fac_fecha";
		$campo2 = "dt_fac_fecha,";
		$cond = " and dt_fac_fecha is not null ";
		$order = " order by dt_fac_fecha ";
	}
//	if($cod_articulo!=""){ $w1 ="and art_codigo='$cod_articulo'"; }
	
	$q2 = "select distinct art_codigo,art_descripcion from VEN_VS_VENTAS_ESPECIALES2
	where linea='$cod_linea' order by art_descripcion";
	//echo $q;
	$q = "select distinct art_codigo,art_descripcion $campo from tmp_ventas_especiales
	where linea='$cod_linea' $cond $order";
	if($cod_almacen!=""){
	$q= "select art_codigo,art_descripcion,sum(importe) $campo as cant from tmp_ventas_especiales 
	where alma='$cod_almacen' and linea='$cod_linea' $cond group by art_codigo,art_descripcion $campo 
	order by cant desc";
	}
	if($ordx=="total"){
	$q= "select art_codigo,art_descripcion,sum(importe) as cant $campo from tmp_ventas_especiales  
	where linea='$cod_linea' $cond group by art_codigo,art_descripcion $campo order by $campo2 cant desc ";
	}
	//echo "articulos_venesp XX-> ".$q;
	$rs = pg_exec($q);

	return $rs;
}

function valor_venesp($cod_linea,$cod_articulo,$alma_cod,$campo_valor,$xdia,$art_fecha){
	$q = "select sum(cantidad) as cant,sum(importe) as imp from VEN_VS_VENTAS_ESPECIALES2
	where linea='$cod_linea' and art_codigo='$cod_articulo' and alma='$alma_cod'";
	$q = "select sum(cantidad) as cant,sum($campo_valor) as imp from tmp_ventas_especiales
	where linea='$cod_linea' and art_codigo='$cod_articulo' and alma='$alma_cod'";
	if($xdia=="xdia"){
		if($art_fecha==""){$cond_fecha = " is null ";}else{$cond_fecha = "='$art_fecha'";}
		//$cond_fecha = "='$art_fecha'";
		$q = "select sum(cantidad) as cant,sum($campo_valor) as imp,dt_fac_fecha from tmp_ventas_especiales
		where linea='$cod_linea' and art_codigo='$cod_articulo' and alma='$alma_cod'
		and dt_fac_fecha $cond_fecha group by dt_fac_fecha order by dt_fac_fecha";
	}
	//echo "valor_venesp ->".$q;
	$rs = pg_exec($q);
	return $rs;
}

function llenar_tmp_ventas_especiales($cond , $A , $fechad , $fechaa, $xdia){
	switch($cond){
		case "linea":

			for($i=0;$i<count($A);$i++){
				$lin = $A[$i];

				pg_exec("select VEN_FN_REP_VENTAS_ESPECIALES('$lin',$fechad,$fechaa,null,$xdia) ");
			}
		break;
		case "tipo":
			for($k=0;$k<count($A);$k++){
				$tipo = $A[$k];
				$rs = pg_exec("select distinct linea, des_linea,tipo from  VEN_VS_VENTAS_ESPECIALES2
				where tipo='$tipo'");
				for($i=0;$i<pg_numrows($rs);$i++){
					$A = pg_fetch_array($rs,$i);
					$lin = $A[0];
					pg_exec("select VEN_FN_REP_VENTAS_ESPECIALES('$lin',$fechad,$fechaa,null,$xdia) ");
				}
			}
		break;
		case "codigo":

				for($k=0;$k<count($A);$k++){
				$articulo = $A[$k];

				$rs = pg_exec(" select distinct linea, des_linea from  VEN_VS_VENTAS_ESPECIALES2
				where art_codigo='$articulo' ");
				$L = pg_fetch_array($rs,0);
				$linea = $L[0];

				$R[0]="nadannn";
				$enc = array_search($linea,$R);
					if($enc==""){   $R[$k+1] = $linea;  }
				}

				/*Ahora con las lineas definidas ejecutamos el procedimiento*/

				for($i=1;$i<=count($R);$i++){
					$lin = $R[$i];
					$articulo = $A[$k-1];
					pg_exec("select VEN_FN_REP_VENTAS_ESPECIALES('$lin',$fechad,$fechaa,'$articulo',$xdia) ");
				}

		break;

	}
}

