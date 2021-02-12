<?php

class LiquidacionGNVModel extends Model {

    	function obtieneListaEstaciones() {

		global $sqlca;
	
		$sql = "SELECT
				ch_almacen,
				trim(ch_nombre_almacen)
			FROM
				inv_ta_almacenes
			WHERE
				ch_clase_almacen='1'
			ORDER BY
				ch_almacen;";

		if ($sqlca->query($sql) < 0) 
			return false;	
		$result = Array();
	
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    	$a = $sqlca->fetchRow();
		    	$result[$a[0]] = $a[0] . " - " . $a[1];
		}
	
		return $result;

    	}

	function Consolidacion($fecha){
		global $sqlca;

		$query = "select count(*) from pos_consolidacion where dia = to_date('$fecha','DD/MM/YYYY')";

		//echo $query;

		if ($sqlca->query($query) < 0) 
			return false;
		$a = $sqlca->fetchRow();
		if($a[0]>=1){
			return 1;
		}else{
			return 0;
		}

	}

	function ConsolidacionA($fecha){
		global $sqlca;

		$query = "select count(*) from pos_consolidacion where dia = '$fecha'";

		//echo $query;

		if ($sqlca->query($query) < 0) 
			return false;
		$a = $sqlca->fetchRow();
		if($a[0]>=1){
			return 1;
		}else{
			return 0;
		}

	}
	
	function Paginacion($pp, $pagina, $fecha, $fecha2){

		global $sqlca;

		$query = "
			SELECT 
				ch_almacen,          
				to_char(dt_fecha,'DD/MM/YYYY'),
				contometro_inicial,
				contometro_final,
				tot_cantidad,          
				tot_venta,
				tot_abono,
				tot_afericion,
				tot_cli_credito,
				tot_cli_anticipo,
				tot_tar_credito,
				tot_descuentos,
				tot_trab_faltantes,
				tot_trab_sobrantes,
				tot_soles,  
				tot_dolares,
				tot_surtidor_m3,
				tot_surtidor_soles,
				mermas_m3
			FROM 
				comb_liquidaciongnv";

		if($fecha != ''){
		$query .= "
			WHERE
				dt_fecha BETWEEN to_date('$fecha','DD/MM/YYYY') AND to_date('$fecha2','DD/MM/YYYY')";
		}
			
		$query .= "
			ORDER BY 
				dt_fecha";

		$resultado_1 = $sqlca->query($query);
		$numrows = $sqlca->numrows();

		$paginador = new paginador($numrows,$pp, $pagina);
	
		$listado2['partir'] 		= $paginador->partir();
		$listado2['fin'] 		= $paginador->fin();
		$listado2['numero_paginas'] 	= $paginador->numero_paginas();
		$listado2['pagina_previa'] 	= $paginador->pagina_previa();
		$listado2['pagina_siguiente'] 	= $paginador->pagina_siguiente();
		$listado2['pp'] 		= $paginador->pp;
		$listado2['paginas'] 		= $paginador->paginas();
		$listado2['primera_pagina'] 	= $paginador->primera_pagina();
		$listado2['ultima_pagina'] 	= $paginador->ultima_pagina();

		$query .= " LIMIT " . pg_escape_string($pp) . " ";
		$query .= " OFFSET " . pg_escape_string($paginador->partir());

//		echo $query;

		if ($sqlca->query($query) < 0)
			return false;
	    
    		$listado[] = array();
		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$resultado[$i]['ch_almacen']		= $a[0];
			$resultado[$i]['dt_fecha']		= $a[1];
			$resultado[$i]['cnt_inicial'] 		= $a[2];
			$resultado[$i]['cnt_final'] 		= $a[3];
			$resultado[$i]['tot_cantidad'] 		= $a[4];
			$resultado[$i]['tot_venta'] 		= $a[5];
			$resultado[$i]['tot_abono'] 		= $a[6];
			$resultado[$i]['tot_afericion'] 	= $a[7];
			$resultado[$i]['tot_cli_credito'] 	= $a[8];
			$resultado[$i]['tot_cli_anticipo'] 	= $a[9];
			$resultado[$i]['tot_tar_credito'] 	= $a[10];
			$resultado[$i]['tot_descuentos'] 	= $a[11];
			$resultado[$i]['tot_trab_faltantes'] 	= $a[12];
			$resultado[$i]['tot_trab_sobrantes'] 	= $a[13];
			$resultado[$i]['tot_soles'] 		= $a[14];
			$resultado[$i]['tot_dolares'] 		= $a[15];
			$resultado[$i]['tot_surtidor_cantidad']	= $a[16];
			$resultado[$i]['tot_surtidor_soles'] 	= $a[17];
			$resultado[$i]['mermas'] 		= $a[18];
			
		}
		
		$query = "COMMIT";
		$sqlca->query($query);

		$listado['datos']      = $resultado;        
		$listado['paginacion'] = $listado2;

		return $listado;
  	}

	function agregar($almacen,$hoy,$cnt_inicial,$cnt_final,$totcantidad,$totventa,$totabono,$totafericion,$clicredito,$clianticipo,$tarcredito,$descuentos,$faltantes,$sobrantes,$soles,$dolares,$surtidor_soles,$surtidor_m3,$mermas){
		global $sqlca;

		$anio = substr($hoy,6,4);
		$mes = substr($hoy,3,2);
		$dia = substr($hoy,0,2);

		$fecha = $anio."-".$mes."-"."$dia";
		
		$validar = LiquidacionGNVModel::ValidaRegistro($almacen, $fecha);

		settype($totcantidad,"double");
		settype($totventa,"double");


		settype($surtidor_soles,"double");
		settype($surtidor_m3,"double");
		settype($mermas,"double");

		if ($validar == 1){
	
		$query2 = "INSERT INTO comb_liquidaciongnv(
							ch_almacen,          
							dt_fecha,          
							contometro_inicial,
							contometro_final,
							tot_cantidad,
							tot_venta,
							tot_abono,
							tot_afericion,
							tot_cli_credito,
							tot_cli_anticipo,
							tot_tar_credito,
							tot_descuentos,
							tot_trab_faltantes,
							tot_trab_sobrantes,
							tot_soles,  
							tot_dolares,
							tot_surtidor_soles,
							tot_surtidor_m3,
							mermas_m3
		      		 )VALUES(
							'$almacen',
						        '$fecha',
					    	        $cnt_inicial,
					    	        $cnt_final,
					    	        $totcantidad,
					    	        $totventa,
						        $totabono,
						        $totafericion,
						        $clicredito,
						        $clianticipo,
						        $tarcredito,
						        $descuentos,
						        $faltantes,
						        $sobrantes,
						        $soles,
						        $dolares,
							$surtidor_soles,
							$surtidor_m3,
							$mermas

				);";

			//echo $query2;
			
			if ($sqlca->query($query2) < 0) 
				return 0;
			else
				return 1;
		}else{
			return 2;
		}

	}
	
	function eliminarRegistro($idregistro,$hoy){
		global $sqlca;

		$anio = substr($hoy,6,4);
		$mes = substr($hoy,3,2);
		$dia = substr($hoy,0,2);

		$fecha = $anio."-".$mes."-"."$dia";

		$query = "DELETE FROM comb_liquidaciongnv WHERE ch_almacen = '$idregistro' AND dt_fecha = '$fecha';";

		$sqlca->query($query);

		return 'OK';

	}

	function ActualizarRegistro($almacen,$fecha,$cnt_inicial,$cnt_final,$totcantidad,$totventa,$totabono,$totafericion,$clicredito,$clianticipo,$tarcredito,$descuentos,$faltantes,$sobrantes,$soles,$dolares,$surtidor_soles,$surtidor_m3,$mermas){
		global $sqlca;


		//settype($totcantidad,"double");
		settype($totventa,"double");


		settype($surtidor_soles,"double");
		settype($surtidor_m3,"double");
		settype($mermas,"double");

			$query="

				UPDATE 
					comb_liquidaciongnv
				  SET
					contometro_inicial  	 = $cnt_inicial,
					contometro_final  	 = $cnt_final,
					tot_cantidad  		 = $totcantidad,
					tot_venta  		 = $totventa,
					tot_abono   		 = $totabono,
					tot_afericion	  	 = $totafericion,
					tot_cli_credito  	 = $clicredito,
					tot_cli_anticipo 	 = $clianticipo,
					tot_tar_credito  	 = $tarcredito,
					tot_descuentos   	 = $descuentos,
					tot_trab_faltantes   	 = $faltantes,
					tot_trab_sobrantes   	 = $sobrantes,
					tot_soles 		 = $soles,
					tot_dolares 		 = $dolares,
					tot_surtidor_soles	 = $surtidor_soles,
					tot_surtidor_m3		 = $surtidor_m3,
					mermas_m3		 = $mermas
				  WHERE 
					ch_almacen = '$almacen' AND
					dt_fecha  = '$fecha';

			";

			//echo $query;

			$result = $sqlca->query($query);

			return '';

 	}
	
	function recuperarRegistroArray($almacen,$fecha){
	  	global $sqlca;
		
		    $registro = array();
		    $query = "SELECT 
					ch_almacen,          
					dt_fecha,
					contometro_inicial,
					contometro_final,
					tot_cantidad,         
					tot_venta,
					tot_abono,
					tot_afericion,
					tot_cli_credito,
					tot_cli_anticipo,
					tot_tar_credito,
					tot_descuentos,
					tot_trab_faltantes,
					tot_trab_sobrantes,
					tot_soles,  
					tot_dolares,
					tot_surtidor_soles,
					tot_surtidor_m3,
					mermas_m3
				FROM
					comb_liquidaciongnv
				WHERE
					ch_almacen = '$almacen' AND
					dt_fecha  = to_date('$fecha','DD/MM/YYYY')";
			 
		    $sqlca->query($query);

		    while( $reg = $sqlca->fetchRow()){
				$registro = $reg;
			}
		    
		    return $registro;
	  }

	function ValidaRegistro($almacen,$fecha){
		global $sqlca;

		$query = "select count(*) from comb_liquidaciongnv where ch_almacen = '$almacen' and dt_fecha = '$fecha';";

		//echo $query;

		if ($sqlca->query($query) < 0) 
			return false;

		$a = $sqlca->fetchRow();

		if($a[0]>=1){
			return 0;//YA SE INGRESO
		}else{
			return 1;//NO SE INGRESO
		}

	}


	function ContometroInicial(){
		global $sqlca;

		$sql = "SELECT contometro_final FROM comb_liquidaciongnv ORDER BY dt_fecha DESC LIMIT 1;";

		//echo $sql;

		if ($sqlca->query($sql) < 0)
			false;

		$a = $sqlca-> fetchRow();

		return $a[0];

	}

    function datosEmpresa() {
        global $sqlca;

        $sql = "SELECT p1.par_valor, p2.par_valor, p3.par_valor FROM int_parametros p1, int_parametros p2, int_parametros p3 WHERE p1.par_nombre='razsocial' and p2.par_nombre='ruc' and p3.par_nombre='dires';";
        if ($sqlca->query($sql) < 0)
            return null;

        $res = Array();
        $a = $sqlca->fetchRow();
        $res['razsocial'] = $a[0];
        $res['ruc'] = $a[1];
        $res['direccion'] = $a[2];

        return $res;
    }

}
