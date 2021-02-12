<?php
/*error_reporting(E_ALL);
ini_set('display_errors', 1);*/

class CanjesModel extends Model
{
    public static function llamadaRemota($procedimiento, $parametros){
        global $sqlca;

        $sql = "SELECT par_valor FROM int_parametros WHERE par_nombre = 'master_puntos';";
        $sqlca->query($sql);
        $row = $sqlca->fetchRow();
        $ip = $row[0];

        $url = "http://" . $ip . "/sistemaweb/puntos/index.php?action=canjes&proc=" . urlencode($procedimiento);

        foreach($parametros as $parametro=>$valor) {
            $url .= "&" . $parametro . "=" . urlencode($valor);
        }
		error_log("****** url ******");
		error_log($url);

        $fh = fopen($url,"rb");
    	if ($fh===FALSE)
			return FALSE;

        $res = '';

        while (!feof($fh)) {
            $res .= fread($fh, 8192);
        }
        fclose($fh);
		error_log("****** unserialize ******");
		error_log( json_encode( array( unserialize($res) ) ) );
        return unserialize($res);
    }

    public static function obtenerDatos($tarjeta){
		$params = array(
			"tarjeta"	=> $tarjeta
		);
	
		return CanjesModel::llamadaRemota("obtenerDatos", $params);
    }
    
    function obtenerArticulosCanje($tarjeta)
    {
	$params = array(
	    "tarjeta"	=> $tarjeta
	);
	
	return CanjesModel::llamadaRemota("obtenerArticulosCanje", $params);
    }
    
    function realizarCanje($tarjeta, $id_item, $observacion){
		global $sqlca;
		global $usuario;

        $sql = "SELECT par_valor FROM int_parametros WHERE par_nombre='razsocial'";
        $sqlca->query($sql);
        if (!$row = $sqlca->fetchRow())
            return false;

        $razsocial1 = $row[0];

        $sql = "SELECT par_valor FROM int_parametros WHERE par_nombre='desces'";
        $sqlca->query($sql);
        if (!$row = $sqlca->fetchRow())
            return false;

        $razsocial2 = $row[0];
	
		$params = array(
		    "tarjeta"		=> $tarjeta,
		    "id_item"		=> $id_item,
		    "observacion"	=> $observacion,
		    "usuario"		=> $usuario->obtenerUsuario(),
		    "sucursal"		=> $usuario->obtenerAlmacenActual(),
		    "razsocial1"	=> $razsocial1,
		    "razsocial2"	=> $razsocial2
		);
		
		$texto = CanjesModel::llamadaRemota("realizarCanje", $params);
		
		$fh = fopen("/sistemaweb/tmp/imprimir/items_canje.txt", "wt");
		fwrite($fh, $texto);
		fclose($fh);
		error_log("****** Texto ******");
		error_log($texto);

		//$sql = "SELECT p.ip, p.pc_samba, p.prn_samba, CASE WHEN trim(p.pos) = trim(i.par_valor) THEN 1 ELSE 0 END FROM pos_cfg p LEFT JOIN int_parametros i ON i.par_nombre = 'pos_consolida' ORDER BY 4 DESC,p.tipo ASC LIMIT 1";
		

		
		$sql="
SELECT
 'tm300' AS printername,
 CASE WHEN terminaldata LIKE '%|%' THEN split_part(terminaldata, '|', 2) ELSE terminaldata END AS printerip
FROM
 s_pos AS c
 JOIN int_parametros AS p
  ON (p.par_nombre='pos_consolida' AND c.s_pos_id::VARCHAR = p.par_valor);
		";
		
/*
		//Cuando el market es windows en pos_cfg al market poner ip de servidor y configurar ahi la imprsora red
		$sql = "
SELECT
'tm300' as printername,
p.ip as printerip
FROM
pos_cfg p
JOIN int_parametros i ON (i.par_nombre='pos_consolida' AND p.pos = i.par_valor)
"*/

		$sqlca->query($sql);
		if ($row = $sqlca->fetchRow()) {
//		    return false;
		
			//$cmd = "smbclient //" . $row['pc_samba'] . "/" . $row['prn_samba'] . " -c 'print /tmp/imprimir/items_canje.txt' -N -I " . $row['ip'];
			//$cmd = "lpr -H " . $row['printerip'] . " -P " . $row['printername'] . " /sistemaweb/tmp/imprimir/items_canje.txt"; //Comentado por ticket OPENSOFT-22
			//exec($cmd); //Comentado por ticket OPENSOFT-22
			//error_log("****** CMD para imprimir ******"); 
			//error_log( $cmd );			

			//MODIFICACION PARA IMPRESION DE TICKET DE CANJE (OPENSOFT-22)
			//ABRIMOS FSOCKOPEN
			$response = array(
				"success" => true,
				"impresion" => true
			);

			// echo "<pre>";
			// echo $texto;
			// echo "</pre>";

			$printerip = $row['printerip'];
			$fp = fsockopen("$printerip", 9100, $errno, $errstr, 2); //"127.0.0.1", 9100
			if ($fp === false) {
				echo "$errstr ($errno)<br />\n";
				
				$response = array(
					"success" => true,
					"impresion" => false
				);
			} else {
				fwrite($fp, $texto);
				fclose($fp);				
			}
		}
/*
		$sql = "
		UPDATE
		    prom_ta_cuentas
		SET
		    nu_cuenta_puntos = nu_cuenta_puntos - " . pg_escape_string($item['nu_item_puntos']) . "
		WHERE
		    id_cuenta=" . pg_escape_string($info['id_cuenta']);
		$sqlca->query($sql);
*/
		return $response;
    }

    function centrar($str, $len)
    {
	return str_pad($str, $len, " ", STR_PAD_BOTH);
    }
        
}

