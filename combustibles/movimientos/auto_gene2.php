<?php
		$MSSQLDBHost = "190.235.198.115:1433";
		$MSSQLDBUser = "sa";
		$MSSQLDBPass = "ene1/.34";
		$MSSQLDBName = "GRA";

		$mssql = mssql_connect($MSSQLDBHost,$MSSQLDBUser,$MSSQLDBPass);
								
		mssql_select_db($MSSQLDBName, $mssql);
		
		
		
		if ($mssql===FALSE) {
			return "Error al conectarse a la base de datos de Energigas";
		}
						
		
			//$control="select valor from control_id where tabla='Aux_Tesacom'";
			$control="select max(Codigo_Pedido) from Aux_Tesacom";
			if (mssql_query($control,$mssql)===FALSE) {return 0;}
			
			
			$res = mssql_query($control, $mssql);
			if ($res===FALSE) {
			return "Error al obtener datos de la tabla control_id";
			}	
			
			for ($i = 0; $i < mssql_num_rows($res); ++$i) {
         		$row = mssql_fetch_row($res);
         		$xxid = $row[0];
			
     		}			
				
		mssql_free_result($res);
						$cod_idx="";
						$cod_idx=$xxid+1;
						//echo $cod_idx;
								
		mssql_close($mssql);
	
	

