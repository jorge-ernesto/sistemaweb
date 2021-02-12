<?php 
$host = "localhost";
					$data = "opensoft";
					$user = "postgres"; //usuario de postgres
					$pass = "postgres"; //password de usuario de postgres

					$conn_string = "host=". $host . " dbname= " . $data . " user=" . $user . " password=" . $pass;

					$dbconn = pg_connect($conn_string) or die("Error al conectar a la Base de datos\n");
