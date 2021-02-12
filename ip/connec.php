<?php 					$host = "localhost";
					$data = "opensoft";
					$user = "postgres"; //usuario de postgres
					$pass = "postgres"; //password de usuario de postgres

					$conn_string = "host=". $host . " dbname= " . $data . " user=" . $user . " password=" . $pass."  options='--client_encoding=latin1'";

					$dbconn = pg_connect($conn_string);

					
					
					
					
					//validar la conexión
					if(!$dbconn) {
					$error_cDB= "Error al conectar a la Base de datos\n";
					
					}
					?>
