<?php

class ClientesModel extends Model {

	function obtenerClientes() {
		global $sqlca;

		$sql = "SELECT
		            	cli_codigo,
				    cli_razsocial,
				    cli_rsocialbreve,
				    cli_direccion,
				    cli_ruc,
				    cli_moneda,
				    cli_telefono1,
				    cli_telefono2
		        FROM
		           	int_clientes
		        ORDER BY
		            	cli_codigo; ";

		if ($sqlca->query($sql) < 0) 
			return false;

		$result = Array();

        	for ($i = 0; $i < $sqlca->numrows(); $i++) {
            		$a = $sqlca->fetchRow();
		    	$cli_codigo 		= $a[0];
		    	$cli_razsocial 		= $a[1];
		    	$cli_razsocialbreve 	= $a[2];
		    	$cli_direccion 		= $a[3];
		    	$cli_ruc 		= $a[4];
		    	$cli_moneda 		= $a[5];
		    	$cli_telefono1 		= $a[6];
		    	$cli_telefono2 		= $a[7];

            		$result[$cli_codigo]['cli_razsocial'] 	   = $cli_razsocial;
            		$result[$cli_codigo]['cli_razsocialbreve'] = $cli_razsocialbreve;
            		$result[$cli_codigo]['cli_direccion'] 	   = $cli_direccion;
            		$result[$cli_codigo]['cli_ruc'] 	   = $cli_ruc;
            		$result[$cli_codigo]['cli_moneda'] 	   = $cli_moneda;
            		$result[$cli_codigo]['cli_telefono'] 	   = $cli_telefono1;
            		$result[$cli_codigo]['cli_fax'] 	   = $cli_telefono2;
       		}

        	return $result;
	}

	function actualizarCliente($codigo, $raz_social, $rsocial_breve, $direccion, $ruc, $moneda, $telefono, $fax) {
		global $sqlca;

		$sql = "UPDATE
		            	int_clientes
		        SET
				cli_razsocial='" . pg_escape_string($raz_social) . "',
				cli_rsocialbreve='" . pg_escape_string($rsocial_breve) . "',
				cli_direccion='" . pg_escape_string($direccion) . "',
				cli_ruc='" . pg_escape_string($ruc) . "',
				cli_moneda='" . pg_escape_string($moneda) . "',
				cli_telefono1='" . pg_escape_string($telefono) . "',
				cli_telefono2='" . pg_escape_string($faz) . "'
		        WHERE
		            	cli_codigo='" . pg_escape_string($codigo) . "' ; ";
        	echo $sql;
	
	        if ($sqlca->query($sql) < 0) 
			return false;

        	return true;
    	}

    	function borrarCliente($codigo) {
		global $sqlca;

		$sql = "DELETE FROM int_clientes WHERE cli_codigo='" . pg_escape_string($codigo) . "' ; ";

		if ($sqlca->query($sql) < 0) 
			return false;

		return true;
    	}

	function agregarCliente($codigo, $razsocial, $rsocialbreve, $direccion, $ruc, $moneda, $telefono, $fax) {
		global $sqlca;

		$sql = "INSERT INTO
				    int_clientes (
				        cli_codigo,
				        cli_razsocial,
				        cli_rsocialbreve,
				        cli_direccion,
				        cli_ruc,
				        cli_moneda,
				        cli_telefono1,
				        cli_telefono2
				    )
		        VALUES (
				    '" . pg_escape_string($codigo) . "',
				    '" . pg_escape_string($razsocial) . "',
				    '" . pg_escape_string($rsocialbreve) . "',
				    '" . pg_escape_string($direccion) . "',
				    '" . pg_escape_string($ruc) . "',
				    '" . pg_escape_string($moneda) . "',
				    '" . pg_escape_string($telefono) . "',
				    '" . pg_escape_string($fax) . "'
		        	); ";

		if ($sqlca->query($sql) < 0) 
			return false;

		return true;
    	}  
}
