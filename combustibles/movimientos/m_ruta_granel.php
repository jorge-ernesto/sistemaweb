<?php

class VentaGranelModel extends Model {

	function obtenerParametros() {
	
		global $sqlca;

		//$defaultparams = Array("190.235.198.115:1433","sa","ene1/.34","GRA");
		//$defaultparams = Array("192.168.1.222:1433","sa","ene1/.34","GRA");
		
		$sql = "	SELECT
					p1.par_valor,
					p2.par_valor,
					p3.par_valor,
					p4.par_valor
				FROM
					int_parametros p1
					LEFT JOIN int_parametros p2 ON p2.par_nombre = 'iridium_username'
					LEFT JOIN int_parametros p3 ON p3.par_nombre = 'iridium_password'
					LEFT JOIN int_parametros p4 ON p4.par_nombre = 'iridium_dbname2'
				WHERE
					p1.par_nombre = 'iridium_server'";

		if ($sqlca->query($sql) < 0)
			return $defaultparams;

		if ($sqlca->numrows() != 1)
			return $defaultparams;

		$reg = $sqlca->fetchRow();

		return Array($reg[0],$reg[1],$reg[2],$reg[3]);
	}


	function busqueda($desde, $hasta, $ruc, $pedido,$plac) { 

		$cond1 = "";
		$cond2 = "";
		$diadesde = substr($desde,6,4)."-".substr($desde,3,2)."-".substr($desde,0,2);
		$diahasta = substr($hasta,6,4)."-".substr($hasta,3,2)."-".substr($hasta,0,2);
		
		$Parametros = VentaGranelModel::obtenerParametros();		

		$MSSQLDBHost = $Parametros[0];
		$MSSQLDBUser = $Parametros[1];
		$MSSQLDBPass = $Parametros[2];
		$MSSQLDBName = $Parametros[3];

		$mssql = mssql_connect($MSSQLDBHost,$MSSQLDBUser,$MSSQLDBPass);
								
		mssql_select_db($MSSQLDBName, $mssql);
		
		if ($mssql===FALSE) {
			return "Error al conectarse a la base de datos de Energigas";
		}
				
		if(trim($ruc)!="")
			$cond1 = " AND RUC= '$ruc' ";
			
		if(trim($pedido)!="")
			$cond2 = " AND Codigo_Pedido= '$pedido' ";
			
		if(trim($plac)!="")
		$cond3 = " AND p.placa= '$plac' ";
		
		$sql = "SELECT 
				CONVERT(int, Codigo_Pedido) as codigo,
				CONVERT(VARCHAR(20), Fecha_Hora_R, 20),
				RUC,
				Razon_Social,
				a.direccion_despacho,
				Codigo_Distrito,
				Cantidad_P_Galones,
				NroScope,
				CONVERT(VARCHAR(20), Fecha_Hora_D, 20),
				p.placa,
				p.id_placa		 
			FROM 
				Aux_Tesacom
				INNER JOIN agendaanexo a ON aux_tesacom.codigo_anexo=a.id
				LEFT JOIN placa p ON p.id_placa=aux_tesacom.id_placa
			WHERE 
				convert(char(10),Fecha_Hora_R,120) BETWEEN '".$diadesde."' and '".$diahasta."' 
				$cond1 $cond2 $cond3
			ORDER BY 
				codigo desc;";    //INNER JOIN placa p ON aux_tesacom.id_placa=p.id_placa
		echo $sql;

		//$eliminar = ("TRUNCATE TABLE Aux_Tesacom");
		//		$res = mssql_query($eliminar, $mssql);
				
		$datos = Array();
		$res = mssql_query($sql, $mssql);
		if ($res===FALSE) {
			return "Error al obtener datos de la tabla Aux_Tesacom";
		}			
		for ($i = 0; $i < mssql_num_rows($res); ++$i) {
         		$row = mssql_fetch_row($res);
         		$datos[$i][0] = $row[0];
			$datos[$i][1] = $row[1];
			$datos[$i][2] = $row[2];
			$datos[$i][3] = $row[3];
			$datos[$i][4] = $row[4];
			$datos[$i][5] = $row[5];
			$datos[$i][6] = $row[6];
			$datos[$i][7] = $row[7];
			$datos[$i][8] = $row[8];
			$datos[$i][9] = $row[9];
			$datos[$i][10] = $row[10];
			/*$datos[$i][11] = $row[11];
			$datos[$i][12] = $row[12];*/
     		}			
				
		mssql_free_result($res);
		mssql_close($mssql);
		
		return $datos;
	}	
	
	function busqueda2($ruc,$codpedido,$idplaca){

		$cond1 = "";
		$cond2 = "";
		/*$diadesde = substr($desde,6,4)."-".substr($desde,3,2)."-".substr($desde,0,2);
		$diahasta = substr($hasta,6,4)."-".substr($hasta,3,2)."-".substr($hasta,0,2);*/
		
		$Parametros = VentaGranelModel::obtenerParametros();		

		$MSSQLDBHost = $Parametros[0];
		$MSSQLDBUser = $Parametros[1];
		$MSSQLDBPass = $Parametros[2];
		$MSSQLDBName = $Parametros[3];

		$mssql = mssql_connect($MSSQLDBHost,$MSSQLDBUser,$MSSQLDBPass);
								
		mssql_select_db($MSSQLDBName, $mssql);
		
		if ($mssql===FALSE) {
			return "Error al conectarse a la base de datos de Energigas";
		}
				
		if($idplaca!=''){
			$parte="p.placa='$idplaca'";
		}else{
			$parte="RUC= '$ruc' AND Codigo_Pedido = '$codpedido' ";
			//antes $parte="RUC= '$ruc' AND Codigo_Pedido=$pedido";
		}
		
		$sql = "SELECT 			
				Fecha_Hora_D as fecha,
				p.placa,
				Codigo_Pedido,
				Cantidad_P_Galones	 
			FROM 
				Aux_Tesacom				
				INNER JOIN placa p ON p.id_placa=aux_tesacom.ID_Placa
			WHERE 				
				$parte";    //INNER JOIN placa p ON aux_tesacom.id_placa=p.id_placa*/

		echo $sql;
				
		$datos = Array();
		$res = mssql_query($sql, $mssql);

		if ($res===FALSE) {
			return "Error al obtener datos de la tabla Aux_Tesacom";
		}

		for ($i = 0; $i < mssql_num_rows($res); ++$i) {
         		$row = mssql_fetch_row($res);
         		$datos[$i][0] = $row[0];
			$datos[$i][1] = $row[1];
			$datos[$i][2] = $row[2];
			$datos[$i][3] = $row[3];	
		}

		/*while($reg = mssql_fetch_row($res)){
			$registros = $reg;
		}*/
				
		mssql_free_result($res);
		mssql_close($mssql);

		return $datos;
			
	}

	function adicionar($codpedido,$fecregistro,$ruc,$razsocial,$dirfiscal,$codanexo,$diranexo,$galones,$precio,$scop,$diascredito,$distrito){ // cerrar conexion??

		$Parametros = VentaGranelModel::obtenerParametros();		

		$MSSQLDBHost = $Parametros[0];
		$MSSQLDBUser = $Parametros[1];
		$MSSQLDBPass = $Parametros[2];
		$MSSQLDBName = $Parametros[3];

		$mssql = mssql_connect($MSSQLDBHost,$MSSQLDBUser,$MSSQLDBPass);
								
		mssql_select_db($MSSQLDBName, $mssql);
		
		if ($mssql===FALSE) {
			return "Error al conectarse a la base de datos de Energigas";
		}
						
		
					
		//	$aux="UPDATE control_id set valor=$codpedido where tabla='Aux_Tesacom'";
			//if (mssql_query($aux,$mssql)===FALSE) {return 0;}
			
									
		$sql = "INSERT INTO Aux_Tesacom 
		
	(	Codigo_Pedido,	
		RUC,	
		Razon_Social,	
		Direccion_Fiscal,
		Codigo_Anexo,
		Direccion_Anexo,
		Cantidad_P_Galones,
		Precio_Galon,
		NroScope,
		Fecha_Hora_R,
		Codigo_Distrito,
		Dias_Credito,
		Cantidad_D_Galones,
		Numero_Golpe,
		Fecha_Hora_D,
		Serie,
		Numero,
		Tipo_Documento,
		ID_Placa
		 )  

	VALUES  
		('".$codpedido."',
		'".$ruc."',
		'".$razsocial."',
		'".$dirfiscal."',
		 ".$codanexo.",
		 '".$diranexo."',
		 ".$galones.", 
		 ".$precio." , 
		 '".$scop."',
		  GETDATE(),
		  ".$distrito.",
		  ".$diascredito.", 
		    0,
		    0,
		    GETDATE(),
		    0,
		    0,
		    0,
		    0 );";
		    
		//echo $sql;
		if (mssql_query($sql,$mssql)===FALSE) {
			return 0;			
		}
		mssql_close($mssql);
		
		return 1;
	}
		
	//function modificar($codpedido,$ruc,$fecha,$placa,$idplaca){

	function autoplaca($placa){

		$Parametros = VentaGranelModel::obtenerParametros();
		
		$MSSQLDBHost = $Parametros[0];
		$MSSQLDBUser = $Parametros[1];
		$MSSQLDBPass = $Parametros[2];
		$MSSQLDBName = $Parametros[3];

		$mssql = mssql_connect($MSSQLDBHost,$MSSQLDBUser,$MSSQLDBPass);		
						
		mssql_select_db($MSSQLDBName, $mssql);
		
		$sql = "select id_placa from placa where placa = '$placa';";
		
		$datos = Array();

		$res = mssql_query($sql, $mssql);

		if ($res===FALSE) {
			return "Error al obtener datos de la tabla Aux_Tesacom";
		}			

		for ($i = 0; $i < mssql_num_rows($res); ++$i) {
			$row = mssql_fetch_row($res);
			$datos[$i][0]  = trim($row[0]);
	     	}			

		mssql_free_result($res);
		mssql_close($mssql);
	
		return $datos;

	}

	function modificar($codpedido,$idplaca,$fecha){

		$Parametros = VentaGranelModel::obtenerParametros();		

		$MSSQLDBHost = $Parametros[0];
		$MSSQLDBUser = $Parametros[1];
		$MSSQLDBPass = $Parametros[2];
		$MSSQLDBName = $Parametros[3];

		$mssql = mssql_connect($MSSQLDBHost,$MSSQLDBUser,$MSSQLDBPass);
								
		mssql_select_db($MSSQLDBName, $mssql);
		
		if ($mssql===FALSE) {
			return "Error al conectarse a la base de datos de Energigas";
		}
						
		$sql = "UPDATE
				Aux_Tesacom 
			SET
				Fecha_Hora_D = '$fecha',
				ID_Placa     = '$idplaca'				
			WHERE 
				Codigo_Pedido = '".$codpedido."';";

		echo $sql;
 	
		//AND RUC='".$ruc."'  ;

		if (mssql_query($sql,$mssql)===FALSE) {
			
			return 0;			
		}
		
		mssql_close($mssql);
		
		return 1;
	}	
	
	function listarRUCs() { 
		
		$Parametros = VentaGranelModel::obtenerParametros();		

		$MSSQLDBHost = $Parametros[0];
		$MSSQLDBUser = $Parametros[1];
		$MSSQLDBPass = $Parametros[2];
		$MSSQLDBName = $Parametros[3];

		$mssql = mssql_connect($MSSQLDBHost,$MSSQLDBUser,$MSSQLDBPass);
								
		mssql_select_db($MSSQLDBName, $mssql);
		
		if ($mssql===FALSE) {
			return "Error al conectarse a la base de datos de Energigas";
		}			
		
		$sql = "SELECT 
				age.ID_Agenda AS ruc,
				CASE (age.TipoPersona) WHEN 115 THEN 'Natural' WHEN 116 THEN 'Juridico' ELSE 'Sin definir' END AS tipopersona,
				REPLACE(age.AgendaNombre, ',', '') AS razonsocial,
				dir.Direccion AS direccion
			FROM 
				Agenda age 
				LEFT JOIN Direcciones dir ON age.ID_Agenda = dir.Op
			ORDER BY 
				age.ID_Agenda;";
		
		$datos = Array();
		$res = mssql_query($sql, $mssql);
		if ($res===FALSE) {
			return "Error al obtener datos de la tabla Aux_Tesacom";
		}			
		for ($i = 0; $i < mssql_num_rows($res); ++$i) {
         		$row = mssql_fetch_row($res);
         		$datos[$i]['ruc'] 	= $row[0];
			$datos[$i]['tipo'] 	= $row[1];
			$datos[$i]['razsocial'] = $row[2];
			$datos[$i]['direccion'] = $row[3];
     		}			
				
		mssql_free_result($res);
		mssql_close($mssql);
		
		return $datos;
	}			
}
