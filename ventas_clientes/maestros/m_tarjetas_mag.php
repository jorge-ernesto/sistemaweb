<?php

ini_set("upload_max_filesize", "15M"); 

class TarjetasMagneticasModel extends Model{

	function extension($archivo){
		$partes = explode(".", $archivo);
		$extension = end($partes);

		return $extension;
	}

  	function ModelReportePDF($filtro = array()){
  		global $sqlca;

		$registro = array();
		$codigo = $filtro['codigo'];
		if(!empty($codigo)){
			$query = "SELECT trim(a.codcli) AS cod_cliente, trim(a.numtar) as num_tarjeta, trim(a.nomusu) as usuario, trim(b.cli_razsocial) as razon_social, 
								a.estblo as bloqueada, a.nu_limite_galones, a.nu_limite_importe, 
								ch_tipo_periodo_acumular as periodo, numpla as placa
		      FROM pos_fptshe1 a, int_clientes b
		      WHERE codcli='$codigo' and a.codcli = trim(b.cli_codigo)
		      ORDER BY a.codcli, a.numtar ";
		}
		else{
			$query = "SELECT trim(a.codcli) AS cod_cliente, trim(a.numtar) as num_tarjeta, trim(a.nomusu) as usuario, trim(b.cli_razsocial) as razon_social, 
								a.estblo as bloqueada, a.nu_limite_galones, a.nu_limite_importe, 
								ch_tipo_periodo_acumular as periodo, numpla as placa       
								FROM pos_fptshe1 a, int_clientes b
								WHERE a.codcli = trim(b.cli_codigo)
								 ORDER BY a.codcli, a.numtar ";
		}

		$sqlca->query($query);
		$numrows = $sqlca->numrows();
		while($reg = $sqlca->fetchRow()){
			$registro['datos'][] = $reg;
		}

		return $registro;

	}
  
  function actualizarSegres(){
  	  global $sqlca;
  	  $query = "update pos_fptshe1 set segres='S' where segres='N'";
  	  $sqlca->query($query);
      return OK;
  }
  




  function validarNroTarjeta($NroTarjeta){
    global $sqlca;
    if(!empty($NroTarjeta))
    {
	$query = "SELECT trim(numtar) ".
		 " FROM pos_fptshe1  ".
		 "WHERE numtar = '".$NroTarjeta."' ".
		 " ORDER BY numtar";
	$result = $sqlca->query($query);
	$numrows = $sqlca->numrows();
	
	if($numrows > 0)
	{
	    $rows = $sqlca->fetchRow();
	    return 'El N&uacute;mero ya existe, debe ingresar otro.'; 
	}else{
	    return 'El N&uacute;mero esta Disponible.';
	}
    }else{
       return 'Debe Ingresar el N&uacute;mero de Tarjeta.';
    }
  }

	/* VALIDAR PLACA */

	function validarPlaca($placa){
		global $sqlca;

		if(!empty($placa)){

		$query =" SELECT trim(numpla) ".
			" FROM pos_fptshe1 ".
			" WHERE numpla = '".$placa."' AND estblo = 'N'".
			" ORDER BY numpla ";

		$result = $sqlca->query($query);
		$numrows = $sqlca->numrows();
	
			if($numrows > 0){
				$rows = $sqlca->fetchRow();
				$data[0] = '1';
				$data[1] = '<center><blink style="color: red">El N&uacute;mero de placa ya existe, debe ingresar otro.</blink></center>';
				return $data; 
			}else{
				$data[0] = '2';
				$data[1] = '<center><blink style="color: black">El N&uacute;mero de placa esta Disponible.</blink></center>';
				return $data; 
			}
		}else{
			$data[0] = '3';
			$data[1] = '<center><blink style="color: red">Debe Ingresar el N&uacute;mero de Placa.</blink></center>';
			return $data; 
		}
	}

	function guardarRegistro($registroid, $ip, $usuario){
		global $sqlca;

		$vacio = NULL;
			
		$_REQUEST['tarjeta']['nu_limite_galones']=($_REQUEST['tarjeta']['nu_limite_galones']==''?$vacio:$_REQUEST['tarjeta']['nu_limite_galones']);
		$_REQUEST['tarjeta']['nu_limite_importe']=($_REQUEST['tarjeta']['nu_limite_importe']==''?$vacio:$_REQUEST['tarjeta']['nu_limite_importe']);			
		$_REQUEST['tarjeta']['dt_fecha_upd'] = 'now()';
		$_REQUEST['tarjeta']['ch_ip_upd'] = $ip;
		$_REQUEST['tarjeta']['ch_usuario_upd'] = $usuario;
		$_REQUEST['tarjeta']['flg_replicacion'] = 0;
		$_REQUEST['tarjeta']['fecha_replicacion'] = 'now()';
    
		if (($_REQUEST['tarjeta']['nu_limite_galones']==0 || empty($_REQUEST['tarjeta']['nu_limite_galones'])) && ($_REQUEST['tarjeta']['nu_limite_importe']==0 ||  empty($_REQUEST['tarjeta']['nu_limite_importe']))){
			$_REQUEST['tarjeta']['ch_tipo_periodo_acumular']='';
			$_REQUEST['tarjeta']['ch_dia_de_corte']='';
		}
       
		$reg_tarjeta = $_REQUEST["tarjeta"];

		if(!empty($reg_tarjeta["nu_limite_galones"]) || !empty($reg_tarjeta["nu_limite_importe"])){
			$limite_galones = empty($reg_tarjeta["nu_limite_galones"]) ? 'NULL':$reg_tarjeta["nu_limite_galones"];
			$limite_importe = empty($reg_tarjeta["nu_limite_importe"]) ? 'NULL':$reg_tarjeta["nu_limite_importe"];
		}else{
			$limite_galones = 'NULL';
			$limite_importe = 'NULL';
		}

		if($registroid!='') {
			$registroid = $_REQUEST["registroid"];

			// D = Diario / S = Semanal / M = Mensual
			// Sunday (0) to Saturday (6) -- function extract postresql SELECT EXTRACT(DOW FROM TIMESTAMP '2001-02-16 20:38:40');
			$iDay = ($_REQUEST['tarjeta']['ch_tipo_periodo_acumular'] == 'S' ? (int)$_REQUEST['tarjeta']['ch_dia_de_corte'] : 0);

			// before SQL
			//--ch_dia_de_corte 			= '".$_REQUEST['tarjeta']['ch_dia_de_corte']."',

			$sql = "
			UPDATE
				pos_fptshe1
			SET
				codcli 						= '".$reg_tarjeta["codcli"]."',
				codcue 						= '".$reg_tarjeta["codcue"]."',
				numtar 						= '".$reg_tarjeta["numtar"]."',
				nomusu 						= '".$reg_tarjeta["nomusu"]."',
				numpla 						= '".$reg_tarjeta["numpla"]."',
				ventar 						= '".$reg_tarjeta["ventar"]."',
				estblo 						= '".$reg_tarjeta["estblo"]."',
				segres 						= '".$reg_tarjeta["segres"]."',
				ch_almacen 					= '',
				ch_tipo_producto 			= '',
				nu_limite_galones 			= " . $limite_galones . ",
				nu_limite_importe 			= " . $limite_importe . ",
				ch_tipo_periodo_acumular 	= '".$_REQUEST['tarjeta']['ch_tipo_periodo_acumular']."',
				ch_dia_de_corte 			= '".$iDay."',
				dt_fecha_upd 				= now(),
				ch_ip_upd 					= '".TRIM($ip)."',
				ch_usuario_upd 				= '".TRIM($usuario)."',
				flg_replicacion				= '0',
				fecha_replicacion 			= now()
			WHERE
				numtar = '".TRIM($registroid)."';
			";

			if ($result = $sqlca->query($sql)>=0){

			} else { return $sqlca->get_error(); }

			$query_funcion = "select interface_central_fn_maestros_consulta( to_date( to_char( now(),'yyyy-mm-dd'),'yyyy-mm-dd' ) )" ;

			if ($sqlca->query($query_funcion) < 0){
			} else { return $sqlca->get_error();}

			return '';

		} else {

			$_REQUEST['tarjeta']['segres']	= 'N';
			$reg_tarjeta['feccre']			= 'now()';

			$sql = "
			INSERT INTO pos_fptshe1(
				codcli,
				codcue,
				numtar,
				nomusu,
				numpla,
				ventar,
				estblo,
				nu_limite_galones,
				nu_limite_importe,
				dt_fecha_upd,
				ch_ip_upd,
				ch_usuario_upd,
				flg_replicacion,
				fecha_replicacion,
				feccre
			) VALUES (
				'".$reg_tarjeta["codcli"]."',
				'".$reg_tarjeta["codcue"]."',
				'".$reg_tarjeta["numtar"]."',
				'".$reg_tarjeta["nomusu"]."',
				'".$reg_tarjeta["numpla"]."',
				'".$reg_tarjeta["ventar"]."',
				'".$reg_tarjeta["estblo"]."',
				NULL,
				NULL,
				'".$reg_tarjeta["feccre"]."',
				'".TRIM($ip)."',
				'".TRIM($usuario)."',
				'0',
				now(),
				now()
			);
			";

			if ($result = $sqlca->query($sql)>=0){
			} else { return $sqlca->get_error(); }

			$query_funcion = "select interface_central_fn_maestros_consulta( to_date( to_char( now(),'yyyy-mm-dd'),'yyyy-mm-dd' ) )" ;

			if ($sqlca->query($query_funcion) < 0){
			} else { return $sqlca->get_error();}

			return '';
		}

	}

	function recuperarRegistroArray($registroid){
		global $sqlca;

		$registro = array();

    	$query="
		SELECT
			codcli, ".
			"codcue, ".
			"numtar, ".
			"nomusu, ".
			"numpla, ".
			"ventar, ".
			"estblo, ".
			"cli_grupo, ".
			"to_char(feccre, 'DD/MM/YYYY'), ".
			"estarj, ".
			"segres, ".
			"ch_tipo_producto, ".
			"nu_limite_galones, ".
			"nu_limite_importe, ".
			"ch_tipo_periodo_acumular, ".
			"ch_dia_de_corte, ".
			"nu_galones_acumulados, ".
			"nu_ant_galones_acumulados, ".
			"nu_importe_acumulado, ".
			"nu_ant_importe_acumulado, ".
			"cli_razsocial, ".
			"ch_almacen ".
		"FROM
			pos_fptshe1 she,
			int_clientes clie ".
		"WHERE
			trim(codcli) = trim(cli_codigo) ".
			"AND numtar='$registroid'
		";
     
	$sqlca->query($query);

	while( $reg = $sqlca->fetchRow()){$registro = $reg;}
    
	return $registro;

 	}

	function recuperarDetalledeClientenTarjetasMagneticas($cliente){
	  	global $sqlca;

		$registro = array();

		$query=" ";
    
	}
  
  function eliminarRegistro($idregistro){
    global $sqlca;
    $query = "DELETE FROM pos_fptshe1 WHERE numtar = '$idregistro';";
    $sqlca->query($query);
    return OK;
  }

  function obtener_tarjetas(){
  	global $sqlca;
  	$query = "select 'DE SERVICIO'  as servicio, ','||numpla as placa, ','||nomusu as usuario, 
  	 			','||numtar as tarjeta, ','||ventar as vence, ',7009661'||numtar||'====' as banda 
  	 			from pos_fptshe1 where segres='N' and estblo='N' order by numpla;";
  	$sqlca->query($query);
  	$listado=array();
  	while( $reg = $sqlca->fetchRow()){
      $listado['datos'][] = $reg;
    }    
    return $listado;
  }
  
  //Otras funciones para consultar la DB

	function tmListado($filtro=array(),$pp, $pagina, $placas, $codcliente){
		global $sqlca;
		$cond = '';
		$cond2 = '';
		
		if (!empty($filtro["codigo"]))
			$cond = " WHERE trim(cli.cli_razsocial)||''||trim(numtar)||''||trim(codcli)||''||trim(nomusu)||''||trim(numpla) ~ '".$filtro["codigo"]."' ";

		if (!empty($placas)){

			$placas = str_replace(",","','",$placas);

			$cond2="
				WHERE
					codcli = '$codcliente'
					AND numpla IN ('$placas')
				";

		}

		$query =
			"SELECT
				codcli, ".
				"cli.cli_razsocial, ".
				"numtar, ".
		     		"numpla, ".
		     		"nomusu, ".
		     		"estblo, ".
			     	"ch_nombre_breve_sucursal ".
			"FROM
				pos_fptshe1 she
				LEFT JOIN int_ta_sucursales s ON(s.ch_sucursal = she.ch_almacen)
				LEFT JOIN int_clientes cli ON(cli.cli_codigo = she.codcli) ".
				" ".$cond." ".
				" ".$cond2." ".
			"ORDER BY
				codcli,
				numtar
			";
	
		$resultado_1	= $sqlca->query($query);
		$numrows	= $sqlca->numrows();

		if($pp && $pagina)
			$paginador = new paginador($numrows,$pp, $pagina);
		else
			$paginador = new paginador($numrows,100,0);
	
		$listado2['partir'] 		= $paginador->partir();
		$listado2['fin'] 		= $paginador->fin();
		$listado2['numero_paginas'] 	= $paginador->numero_paginas();
		$listado2['pagina_previa'] 	= $paginador->pagina_previa();
		$listado2['pagina_siguiente'] 	= $paginador->pagina_siguiente();
		$listado2['pp'] 		= $paginador->pp;
		$listado2['paginas'] 		= $paginador->paginas();
		$listado2['primera_pagina'] 	= $paginador->primera_pagina();
		$listado2['ultima_pagina'] 	= $paginador->ultima_pagina();

		if ($pp > 0)
			$query .= "LIMIT " . pg_escape_string($pp) . " ";
		if ($pagina > 0)
			$query .= "OFFSET " . pg_escape_string($paginador->partir());

		if ($sqlca->query($query)<=0)
			return $sqlca->get_error();
    
		$listado[] = array();
    
		while( $reg = $sqlca->fetchRow()){
      			$listado['datos'][] = $reg;
		}    
        
		$listado['paginacion'] = $listado2;
		return $listado;

	}

	/*function tmListadoTotal() {
		global $sqlca;

		$query = "
		SELECT
			codcli
			--cli.cli_razsocial,
			--numtar,
			--numpla,
			--estblo,
			--s.ch_nombre_breve_sucursal
		FROM 
			pos_fptshe1 she
			LEFT JOIN int_ta_sucursales s ON(s.ch_sucursal = she.ch_almacen)
			LEFT JOIN int_clientes cli ON(cli.cli_codigo = she.codcli) 
		ORDER BY
			codcli,
			numtar desc
			limit 3
			";

		$resultado_1 = $sqlca->query($query);
		$numrows = $sqlca->numrows();

		if ($sqlca->query($query) <= 0) {
				return $sqlca->get_error();
		}

		$listado = array();

		while ($reg = $sqlca->fetchRow()) {
			$reg['codcli'] = trim($reg['codcli']);
			if (!empty($reg['codcli'])) {
				$listado['datos'][] = $reg;
			}
		}
		
		$query = "COMMIT";
		$sqlca->query($query);
    
    		return $listado;
  	}*/

  function busquedaExcel() {
		global $sqlca;
	
		$sql = "
		SELECT
			codcli,
			cli.cli_razsocial,
			numtar,
			numpla,
			estblo,
			ch_nombre_breve_sucursal
		FROM
			pos_fptshe1 she
		LEFT JOIN int_ta_sucursales s ON(s.ch_sucursal = she.ch_almacen)
		LEFT JOIN int_clientes cli ON(cli.cli_codigo = she.codcli)
		ORDER BY
			codcli,
			numtar
		;";

 		/*
		echo "<pre>";
		print_r($sql);
		echo "</pre>";
		*/

		if ($sqlca->query($sql) <= 0)
			return $sqlca->get_error();
	    
		$res = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$res[$i]['codcli'] 								= $a[0];
			$res[$i]['cli_razsocial'] 						= $a[1];
			$res[$i]['numtar'] 								= $a[2];
			$res[$i]['numpla'] 								= $a[3];
			$res[$i]['estblo'] 								= $a[4];
			$res[$i]['ch_nombre_breve_sucursal'] 			= $a[5];
		}
		return $res;
    }	

  function ClientesCBArray($condicion=''){
    global $sqlca;
    $cbArray = array();
    $query = "SELECT cli_codigo, cli_razsocial FROM int_clientes";
    $query .= ($condicion!=''?' WHERE '.$condicion:'').' order by 1';
    /*
    echo "<pre>";
    echo $query;
    echo "</pre>";
	*/
	error_log($query);
    if ($sqlca->query($query)<=0)
      return $cbArray;
    while($result = $sqlca->fetchRow()){
      $cbArray[trim($result["cli_codigo"])] = $result["cli_codigo"].' '.$result["cli_razsocial"];
    }
    ksort($cbArray);
    return $cbArray;
  }
  
	function TarjetasMagneticas($codigo){
  		global $sqlca;

  		$query = "select cli_grupo from int_clientes where cli_codigo='".TRIM($codigo)."'";

		$sqlca->query($query);

		$result2 = $sqlca->fetchRow();

		if (is_null($result2['cli_grupo'])){
			$result['maximo']="NO_GRUPO";
		}else{

			$query = "SELECT cast(max(substring(numtar from 8 for 3)) as numeric)+1 as maximo FROM pos_fptshe1 where SUBSTRING(numtar from 5 for 3)='".trim($result2['cli_grupo'])."'";

			$sqlca->query($query);

			$result = $sqlca->fetchRow();

			if (is_null($result['maximo'])){
	    			$result['maximo']="7055".trim($result2['cli_grupo'])."001";
	    		}else{
	    			if (substr($result['maximo'],0,4)=='7056'){
	    				$result['maximo']="EXCEDIO_LIMITE";
	    			}else{
	    				$result['maximo'] = '7055'.trim($result2['cli_grupo']).substr('000'.$result['maximo'],strlen('000'.$result['maximo'])-3);
	    			}
	    		}

	    	}

	  	return $result['maximo'];

	}
  
	function getEstaciones(){
	  	global $sqlca;

	  	$query = "select ''as ch_sucursal,'TODAS' as ch_nombre_sucursal union select ch_sucursal, ch_nombre_breve_sucursal from int_ta_sucursales where ch_sucursal!='001'order by ch_sucursal";

    		$sqlca->query($query);

		$cbArray = array();

		while($result = $sqlca->fetchRow()){
			$cbArray[trim($result["ch_sucursal"])] = $result["ch_sucursal"].' '.$result["ch_nombre_sucursal"];
		}

	return $cbArray;

	}

	function ValidarExcel($codcliente, $placa) {
		global $sqlca;

		if(!empty($placa))
			$condplaca = ", (SELECT numpla FROM pos_fptshe1 WHERE numpla = '".trim($placa)."' AND estblo = 'N' LIMIT 1) codplaca";

		$sql ="
SELECT
 count(*) existe,
 (SELECT cli_razsocial FROM int_clientes WHERE cli_codigo = '".trim($codcliente)."') nomcliente,
 (SELECT cli_grupo FROM int_clientes WHERE cli_codigo = '".trim($codcliente)."') codtar
 " . $condplaca . "
FROM
 int_clientes
WHERE
 cli_codigo = '".trim($codcliente)."'
			";

		$sqlca->query($sql);
		$data = Array();

		if ($sqlca->numrows()==1){
			$data = $sqlca->fetchRow();
			return array($data);
		}
	}

	function CodigoTarjetaMagnetica($codcliente){
		global $sqlca;

  		$query = "SELECT cli_grupo FROM int_clientes WHERE cli_codigo = '".TRIM($codcliente)."'";

		$sqlca->query($query);

		$result2 = $sqlca->fetchRow();

		return trim($result2['cli_grupo']);

	}

	function ObtenerTarjetaMagnetica($codtar){
		global $sqlca;

		$query = "SELECT cast(max(substring(numtar from 8 for 3)) as numeric) as maximo FROM pos_fptshe1 where SUBSTRING(numtar from 5 for 3)='$codtar'";

		$sqlca->query($query);

		$result = $sqlca->fetchRow();

		return $result['maximo'];

	}

	function InsertarExcel($data, $usuario, $ip, $codcliente){
		global $sqlca;

		$resultados 	= count($data->sheets[0]['cells']);
		$codigoexcel	= '';

		$a = 0;
		$b = 0;
		$c = 0;
		$z = 0;

		$codtar	= TarjetasMagneticasModel::CodigoTarjetaMagnetica($codcliente);
		$codtar = trim($codtar);

		for ($i = 4; $i <= ($resultados + 1); $i++) {

			$placa		= $data->sheets[0]['cells'][$i][1];
			$tusuario	= $data->sheets[0]['cells'][$i][2];

			//VALIDAR ARCHIVO EXCEL PRODUCTOS QUE EXISTAN B.D
			$datos	= TarjetasMagneticasModel::ValidarExcel($codcliente, $placa);

			if($codigoexcel == $placa){

				$a++;//CANTIDAD DE PRODUCTOS NO INSERTADOS

			} elseif($datos[0]['codplaca'] == NULL) {

					$b++;//CANTIDAD DE PRODUCTOS INSERTADOS

					$maxtar	= TarjetasMagneticasModel::ObtenerTarjetaMagnetica($codtar);

					if (is_null($maxtar)){

						if(strlen($b) == 1)
							$correlativo = "00".$b;
						elseif(strlen($b) == 2)
							$correlativo = "0".$b;
						else
							$correlativo = $b;

						$numtar = "7055".$codtar.$correlativo;

					}else{

						settype($maxtar, int);

						if($maxtar > $z)
							$z = $maxtar;

						$z++;

						if (substr($maxtar,0,4)=='7056'){
			    				$numtar = "EXCEDIO_LIMITE";
			    			}else{

							if(strlen($z) == 1)
								$correlativo = "00".$z;
							elseif(strlen($z) == 2)
								$correlativo = "0".$z;
							else
								$correlativo = $z;

			    				$numtar = '7055'.$codtar.$correlativo;

						}

					}

					$placab .= $placa.",";

					$sql = "
						INSERT INTO
							pos_fptshe1(
									codcli,
									codcue,
									numtar,
									nomusu,
									numpla,
									ventar,
									estblo,
									nu_limite_galones,
									nu_limite_importe,
									dt_fecha_upd,
									ch_ip_upd,
									ch_usuario_upd,
									flg_replicacion,
									fecha_replicacion,
									feccre
							) VALUES (
									'".$codcliente."',
									'".$codcliente."',
									'".$numtar."',
									'".$tusuario."',
									'".$placa."',
									'mm/aa',
									'N',
									NULL,
									NULL,
									now(),
									'".TRIM($ip)."',
									'".TRIM($usuario)."',
									'0',
									now(),
									now()
							);
					 ";				

					if ($sqlca->query($sql) < 0)
						return false;

			} else {

				$c++;//CANTIDAD DE PRODUCTOS EXISTENTES

			}

			$codigoexcel = $placa;

		}

		return array(true, $a, $b, $c, $placab);

	}

}
