<?php

class ImpresionCierresModel extends Model { 

	function obtenerEstacion($almacen) {
		global $sqlca;

		$sql = "SELECT 	 trim(ch_nombre_almacen) as nombre
			FROM	 inv_ta_almacenes
			WHERE	 ch_clase_almacen='1' AND ch_almacen='$almacen' ";

		if ($sqlca->query($sql) < 0) 
			return false;
		
		$a = $sqlca->fetchRow();
		$nomalmacen = $a['nombre'];
		
		return $nomalmacen;
	}

	function obtenerComandoImprimir($sucursal, $dia, $opcion) {
		global $sqlca;

		$dd = substr($dia,0,2) ;
		$mm = substr($dia,3,2) ;
		$yy = substr($dia,6,4) ;

		$texto =  "ls -l /tmp/imprimir/cierre".$opcion.$dd.$mm.$yy."* > /tmp/imprimir/kuka"; // carga datos al archivo kuka
		exec($texto);
		
		$file = "/tmp/imprimir/";

		$sql =	"SELECT
				trim(pc_samba),
				trim(prn_samba),
				trim(ip) 
			FROM 	pos_cfg 
			WHERE	impcierre = true and pos = (SELECT par_valor from int_parametros where par_nombre='pos_consolida')
			ORDER BY tipo DESC, pos ASC";
		
		$rs = $sqlca->query($sql);
		if ($rs < 0) {
			echo "Error consultando POS\n";
			return false;
		}

		$row     = $sqlca->fetchRow();
		$comand  = "lpr -H {$row[2]} -P {$row[1]} ";
		$cierres = Array();

		$nombre_fichero = "/tmp/imprimir/kuka";
		$handler = fopen($nombre_fichero,"r");
		$i = 0;
		while (!feof($handler)) {
			$linea = fgets($handler, 4096);
			$pos1  = 0;
			$pos1  = strpos($linea, "cierre" );
			$most  = substr($linea,$pos1,22);
			$cierres[$i]['nom']  = $most;
			$cierres[$i]['link'] = $comand.$file.$most;	
			$i++;		
		}

		$fp = fopen("COMANDO.txt","a");
		fwrite($fp, "-".$smbc."-".PHP_EOL);
		fclose($fp);  

		return $cierres;
	}
}
