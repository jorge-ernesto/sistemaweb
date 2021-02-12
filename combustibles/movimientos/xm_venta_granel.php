<?php


class VentaGranelModel extends Model {

	
	function obtenerParametros() {
	
		global $sqlca;

	$defaultparams = Array("190.235.198.115:1433","sa","ene1/.34","CENTRAL");
	return $defaultparams;
		
		/* $sql = "	SELECT
					p1.par_valor,
					p2.par_valor,
					p3.par_valor,
					p4.par_valor
				FROM
					int_parametros p1
					LEFT JOIN int_parametros p2 ON p2.par_nombre = 'iridium_username'
					LEFT JOIN int_parametros p3 ON p3.par_nombre = 'iridium_password'
					LEFT JOIN int_parametros p4 ON p4.par_nombre = 'iridium_dbname'
				WHERE
					p1.par_nombre = 'iridium_server'"; 

		if ($sqlca->query($sql) < 0)
			return $defaultparams;

		if ($sqlca->numrows() != 1)
			return $defaultparams;

		$reg = $sqlca->fetchRow();

		return Array($reg[0],$reg[1],$reg[2],$reg[3]); */
	}


	function busqueda($desde, $hasta, $ruc, $pedido) { 
	
			
		$cond1 = "";
		$cond2 = "";
		
					$host = "localhost";
					$data = "opensoft";
					$user = "postgres"; //usuario de postgres
					$pass = "postgres"; //password de usuario de postgres

					$conn_string = "host=". $host . " dbname= " . $data . " user=" . $user . " password=" . $pass;

					$dbconn = pg_connect($conn_string) or die;

					//validar la conexión
					if(!$dbconn) {
					return "Error al conectar a la Base de datos\n";
					exit;
					}
//SOLUCIONAR CASO DE LAS FECHAS
		$sql = "select * from c_invoiceheader where updated >= '".$desde."' and (updated <= '".$hasta."') 
				 ORDER BY 2,1;";
				
		$datos = Array();
		$res = pg_query($dbconn,$sql);
	
		if ($res===FALSE) {
			return "Error al obtener datos de la tabla c_invoiceheader";
		}
				
			$i=0;
			while( $row = pg_fetch_array ( $res,$i )) {
			 $datos[$i][0] = $row [0];
			 $datos[$i][1] = $row [1];
			 $datos[$i][2] = $row [2];
			 $datos[$i][3] = $row [3];
			 $datos[$i][4] = $row [4];
			 $datos[$i][5] = $row [5];
			 $datos[$i][6] = $row [6];
			 $datos[$i][7] = $row [7];
			 $datos[$i][8] = $row [8];
			 $datos[$i][9] = $row [9];
			 $datos[$i][10] = $row [10];
			 $datos[$i][11] = $row [11];
			 $datos[$i][12] = $row [12];			
			$i++;
			}	
		
		pg_free_result($res);
		pg_close($dbconn);
		
		return $datos;
	}	
	
	function adicionar($datos) { // cerrar conexion??

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
						
		for ($i = 0; $i < count($datos); $i++) {	
							
		$sql = "INSERT INTO pruebaxport
		
	(	id,	
		campo1,	
		campo2,	
		campo3,
		campo4
		 )  

	VALUES  
		(".$datos[$i][0].",
		'".$datos[$i][1]."',
		'".$datos[$i][2]."',
		'".$datos[$i][3]."',
		 ".$datos[$i][4].");";   



		    
		if (mssql_query($sql,$mssql)===FALSE) {
			return 0;			
		}
	
	
	
	}
		return count($datos);
	}
	
	
	function buscaEditar($codpedido, $ruc, $codanexo) { 
	
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
				Codigo_Pedido,
				CONVERT(VARCHAR(20), Fecha_Hora_R, 20),
				RUC,
				Razon_Social,
				Direccion_Fiscal,
				Codigo_Anexo,
				Direccion_Anexo,				
				Cantidad_P_Galones,
				Precio_Galon,
				NroScope,
				Dias_Credito,
				Codigo_Distrito 
			FROM 
				Aux_Tesacom 
			WHERE 
				Codigo_Pedido='".$codpedido."' 
				AND RUC='".$ruc."' 
				AND Codigo_Anexo='".$codanexo."';";
		echo $sql;
		
		$datos = Array();
		$res = mssql_query($sql, $mssql);
		if ($res===FALSE) {
			return "Error al obtener datos de la tabla Aux_Tesacom";
		}			
		$row = mssql_fetch_row($res);
         	$datos[0] = $row[0];
		$datos[1] = $row[1];
		$datos[2] = $row[2];
		$datos[3] = $row[3];
		$datos[4] = $row[4];
		$datos[5] = $row[5];
		$datos[6] = $row[6];
		$datos[7] = $row[7];
		$datos[8] = $row[8];
		$datos[9] = $row[9];
		$datos[10] = $row[10];
		$datos[11] = $row[11];			
     						
		mssql_free_result($res);
		mssql_close($mssql);
		
		return $datos;
	}
		
	function eliminar($codpedido, $ruc, $codanexo){
		
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
						
		$sql = "DELETE FROM Aux_Tesacom 
			WHERE 
				Codigo_Pedido='".$codpedido."'  
				AND RUC='".$ruc."'  
				AND Codigo_Anexo='".$codanexo."' ;";
		echo $sql;
		if (mssql_query($sql,$mssql)===FALSE) {
			return 0;			
		}
		mssql_close($mssql);
		
		return 1;
	}
		
	function modificar($codpedido,$ruc,$codanexo,$galones,$precio,$scop,$diascredito,$distrito){
		
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
						
		$sql = "UPDATE 	Aux_Tesacom 
			SET 	Cantidad_P_Galones=$galones, 
				Precio_Galon=$precio, 
				NroScope='$scop',
				Dias_Credito='$diascredito',
				Codigo_Distrito='$distrito'  
			WHERE 
				Codigo_Pedido='".$codpedido."'  
				AND RUC='".$ruc."'  
				AND Codigo_Anexo='".$codanexo."' ;";
		echo $sql;
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
