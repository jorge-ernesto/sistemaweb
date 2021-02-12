<?php

class TanquesModel extends Model {

	function obtenerProducto() {
		global $sqlca;

			$sql = "select
					ch_codigocombustible,
					ch_nombrecombustible
				from
					comb_ta_combustibles
				order by
					ch_nombrecombustible;";
	
			if ($sqlca->query($sql) < 0) 
				return false;
	
			$result = Array();
			for ($i = 0; $i < $sqlca->numrows(); $i++) {
			    	$a = $sqlca->fetchRow();		    
			    	$producto[$a[0]] = $a[1];
			}
	
		return $producto;
    	}

	function obtenerAlmacenes($alm) {
		global $sqlca;
		
			if(trim($alm) == "")
				$cond = "";
			else
				$cond = " AND ch_almacen = '$alm'"; 
	
			$sql = "SELECT
				    ch_almacen,
				    ch_almacen||' - '||ch_nombre_almacen
				FROM
				    inv_ta_almacenes
				WHERE
				    ch_clase_almacen='1' $cond 
				ORDER BY
				    ch_almacen;";
	
			if ($sqlca->query($sql) < 0) 
				return false;
	
			$result = Array();

			for ($i = 0; $i < $sqlca->numrows(); $i++) {
			    	$a = $sqlca->fetchRow();		    
			    	$result[$a[0]] = $a[1];
			}
	
		return $result;
    	}
	
	function buscar($nom_producto,$nom_producto2){
?><script>alert("<?php echo '+++ la campania es: '.$_REQUEST['almacen'] ; ?> ");</script><?php
		global $sqlca;

		$sql = "select
				t.ch_tanque,
				c.ch_nombrecombustible,
				t.nu_capacidad,
				t.nu_ultimamedida,
				t.dt_fechaultimamedida
			from
				comb_ta_tanques as t
			inner join
				comb_ta_combustibles as c on(t.ch_codigocombustible = c.ch_codigocombustible)
			order by
				t.ch_tanque";

		echo $sql;
	
		if ($sqlca->query($sql) < 0)
			return false;
	    
		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$resultado[$i]['ch_tanque']			= $a[0];
			$resultado[$i]['ch_nombrecombustible']		= $a[1];
			$resultado[$i]['nu_capacidad']		 	= $a[2];
			$resultado[$i]['nu_ultimamedida'] 		= $a[3];
			$resultado[$i]['dt_fechaultimamedida'] 		= $a[4];
			
		}
		
		return $resultado;
  	}

	function agregar($cod_tanque,$nom_producto,$capacidad,$lectu_gal,$usuario,$almacen) {
		global $sqlca;
		
		$validar = TanquesModel::ValidaTanque($cod_tanque);

		if ($validar == 0) {

		$sql = "INSERT INTO comb_ta_tanques
							(ch_tanque,
						         ch_codigocombustible,
						         nu_capacidad,
						         nu_ultimamedida,
						         dt_fechaultimamedida,
							 dt_fechactualizacion,
							 ch_usuario,
							 ch_sucursal)
		       VALUES
							('$cod_tanque',
						         '$nom_producto',
					    	         '$capacidad',
						         '$lectu_gal',
						         'now()',
							 'now()',
							 '$usuario',
							 '$almacen');";

		echo $sql;

			if ($sqlca->query($sql) < 0)
					return 0;
				return 1;
			}else{
				return 2;
			}

	}
	
	function eliminarRegistro($cod_tanque){

		global $sqlca;

		$query = "DELETE FROM comb_ta_tanques WHERE ch_tanque = '$cod_tanque';";
		echo $query;
		$sqlca->query($query);
		return OK;
	}

	function actualizar($cod_tanque,$nom_producto,$capacidad,$lectu_gal) {
		global $sqlca;

			$query = "UPDATE 
					comb_ta_tanques
				  SET
					ch_codigocombustible    = '$nom_producto', 
					nu_capacidad   		= '$capacidad',
					nu_ultimamedida    	= '$lectu_gal',
					dt_fechaultimamedida    = 'now()'
				  WHERE 
					ch_tanque = '$cod_tanque';";
			
			echo $query;

			$result = $sqlca->query($query);
			return '';
 	}
	
	function recuperarRegistroArray($cod_tanque){
	  	global $sqlca;
		
		    $registro = array();

		    $query = "select
					t.ch_tanque,
					c.ch_nombrecombustible,
					t.nu_capacidad,
					t.nu_ultimamedida,
					t.dt_fechaultimamedida
				from
					comb_ta_tanques as t
				inner join
					comb_ta_combustibles as c on(t.ch_codigocombustible = c.ch_codigocombustible)
				where
					t.ch_tanque = '$cod_tanque'";

		    echo $query;
			 
		    $sqlca->query($query);

		    while( $reg = $sqlca->fetchRow()){
				$registro = $reg;
			}
		    
		    return $registro;
	  }

	function ValidaTanque($cod_tanque){
		global $sqlca;

		$cod_tanque = $_REQUEST['cod_tanque'];

		$query = "select count(*) from comb_ta_tanques where ch_tanque = '$cod_tanque';";

		echo $query;

		if ($sqlca->query($query) < 0) 
			return false;
		$a = $sqlca->fetchRow();
		if($a[0]>=1){
			return 1;
		}else{
			return 0;
		}

	}

}
