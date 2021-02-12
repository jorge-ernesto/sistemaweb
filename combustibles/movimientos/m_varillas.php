<?php

class VarillasModel extends Model {

    function getFechaSistemaPA() {
		global $sqlca;
		$sqlca->query("SELECT TO_CHAR(da_fecha - integer '1', 'DD/MM/YYYY') AS fe_sistema FROM pos_aprosys WHERE ch_poscd = 'A' ORDER BY da_fecha DESC LIMIT 1;");
		$row = $sqlca->fetchRow();
		return $row['fe_sistema'];
    }

	function obtenerSucursales($alm) {
		global $sqlca;
		
		if(trim($alm) == "")
			$cond = "";
		else
			$cond = " AND ch_almacen = '$alm'"; 
	
		$sql = "SELECT
			    ch_almacen,
			    ch_almacen||' - '||ch_nombre_almacen
			FROM
			    inv_ta_almacenes
			WHERE
			    ch_clase_almacen='1' $cond 
			ORDER BY
			    ch_almacen;";
	
		if ($sqlca->query($sql) < 0) 
			return false;
	
		$result = Array();
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    	$a = $sqlca->fetchRow();		    
		    	$result[$a[0]] = $a[1];
		}
	
		return $result;
    }
    
	function obtenerTanques($ch_almacen, $tanque) {
		global $sqlca;
		
		if(trim($tanque) == "")
			$cond = "";
		else
			$cond = " AND a.ch_tanque = '" . $tanque . "'"; 
	
		$sql = "
		SELECT
    		DISTINCT a.ch_tanque,
	    	a.ch_tanque||' - '|| b.ch_nombrecombustible
		FROM
	    	comb_ta_tanques a,
	    	comb_ta_combustibles b
		WHERE
			a.ch_codigocombustible=b.ch_codigocombustible
	    	AND a.ch_sucursal=trim('" . pg_escape_string($ch_almacen) . "')
	    	" . $cond;
		//error_log(json_encode($sql));

		if ($sqlca->query($sql) < 0) 
			return false;
	
		$result = array();	
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
	    	$a = $sqlca->fetchRow();
	    	$result[$a[0]] = $a[1];
		}
	
		//error_log(json_encode($result));
		return $result;
    }
    
    function search($ch_sucursal, $desde, $hasta) {
		global $sqlca;
	
		$sql = "
SELECT
 TO_CHAR(VARILLA.dt_fechamedicion, 'dd/mm/yyyy'),
 VARILLA.ch_tanque,
 COMBU.ch_nombrecombustible,
 VARILLA.nu_medicion,
 VARILLA.ch_responsable,
 VARILLA.dt_fechactualizacion,
 VARILLA.ch_usuario,
 VARILLA.ch_auditorpc,
 VARILLA.ch_sucursal
FROM
 comb_ta_mediciondiaria AS VARILLA
 JOIN comb_ta_tanques AS TANK
  USING (ch_sucursal,ch_tanque)
 JOIN comb_ta_combustibles AS COMBU
  USING(ch_codigocombustible)
WHERE
 VARILLA.ch_sucursal = '" . pg_escape_string($ch_sucursal) . "'
 AND VARILLA.dt_fechamedicion BETWEEN TO_DATE('" . pg_escape_string($desde) . "', 'dd/mm/yyyy') AND TO_DATE('" . pg_escape_string($hasta) . "', 'dd/mm/yyyy')
ORDER BY
 VARILLA.dt_fechamedicion DESC,
 VARILLA.ch_tanque ASC;
";


		if ($sqlca->query($sql) < 0) 
			return false;
	
		$resultado = Array();	
			for ($i = 0; $i < $sqlca->numrows(); $i++) {
				$a = $sqlca->fetchRow();		    
				$dt_fecha		= $a[0];
				$ch_tanque		= $a[1];
				$ch_nombre		= $a[2];
				$nu_medicion	= $a[3];
				$ch_responsable	= $a[4];
				$actualizacion	= $a[5];
				$usuario		= $a[6];
				$ip				= $a[7];
				$sucursal		= $a[8];
		    
				$resultado[$i]['dt_fecha']			= $dt_fecha;
				$resultado[$i]['ch_tanque']			= $ch_tanque;
				$resultado[$i]['ch_nombre']			= $ch_nombre;
				$resultado[$i]['nu_medicion']		= $nu_medicion;
				$resultado[$i]['ch_responsable']	= $ch_responsable;
				$resultado[$i]['fec_actualizacion']	= $actualizacion;
				$resultado[$i]['ch_usuario']		= $usuario;
				$resultado[$i]['ch_auditorpc']		= $ip;
				$resultado[$i]['ch_sucursal']		= $sucursal;
			}
	
		return $resultado;
    	}
    
    	function insertar($ch_almacen, $dt_fecha, $ch_tanque, $nu_medicion, $ch_responsable, $usuario, $ip) {
		global $sqlca;
	
		$dia = substr($dt_fecha,6,4)."-".substr($dt_fecha,3,2)."-".substr($dt_fecha,0,2);
		$flag = VarillasModel::validaDia($dia, $ch_almacen);
		$flag2 = VarillasModel::validaVarilla($ch_almacen, $dia, $ch_tanque);

		if ($flag == 1) {	
			if ($flag2 == 1) {	
				$sql = "INSERT INTO
					    	comb_ta_mediciondiaria
						(
							ch_sucursal,
							ch_tanque,
							dt_fechamedicion,
							nu_medicion,
							ch_responsable,
							dt_fechactualizacion,
							ch_usuario,
							ch_auditorpc
						)
					VALUES
					    	(
							'" . pg_escape_string($ch_almacen)."',
							'" . pg_escape_string($ch_tanque)."',
							to_date('".pg_escape_string($dt_fecha)."', 'dd/mm/yyyy'),
							'" . pg_escape_string($nu_medicion)."',
							'" . pg_escape_string($ch_responsable)."',
							now(),
							'".pg_escape_string($usuario)."',
							'".pg_escape_string($ip)."'
					    	);";

			   var_dump($sql);
				if ($sqlca->query($sql) < 0) 
					return 0;	
				return 1;
			} else {
				return 3;
			}
		} else {
			return 2;
		}
    	}
    
	function obtenerVarilla($sucursal, $dia, $tanque) {
		global $sqlca;
		
		$sql = "SELECT
			    	nu_medicion,
			    	ch_responsable
				FROM
					comb_ta_mediciondiaria
				WHERE
					dt_fechamedicion=to_date('$dia', 'dd/mm/yyyy')
					AND ch_sucursal='$sucursal'
					AND ch_tanque='$tanque';
				";

		if ($sqlca->query($sql) < 0) 
			return false;
	
		$a = $sqlca->fetchRow();	
		$a['dt_fecha']    = $dia;
		$a['ch_sucursal'] = $sucursal;
		$a['ch_tanque']   = $tanque;

		return $a;
    	}
    
    	function guardarVarillas($dt_fecha, $ch_sucursal, $ch_tanque, $nu_medicion, $ch_responsable, $usuario, $ip, $ch_tanque_ant) {
		global $sqlca;

		$dia = substr($dt_fecha,6,4)."-".substr($dt_fecha,3,2)."-".substr($dt_fecha,0,2);
		$flag = VarillasModel::validaDia($dia, $ch_sucursal);

		if ($flag==1) {

			$sql = "
				UPDATE
				    	comb_ta_mediciondiaria
				SET
				    	nu_medicion		= '".pg_escape_string($nu_medicion)."',
				    	ch_responsable		= '".pg_escape_string(substr($ch_responsable, 0, 6))."',
					dt_fechactualizacion	= now(),
					ch_usuario		= '".$usuario."',
					ch_auditorpc		= '".pg_escape_string($ip)."',
				    	ch_tanque		= '".substr($ch_tanque,0,2)."'
				WHERE
					dt_fechamedicion	= '$dia'
				    	AND ch_sucursal		= '".pg_escape_string($ch_sucursal)."'
				    	AND ch_tanque		= '".substr($ch_tanque_ant,0,2)."';
			";

			echo $sql;

			if ($sqlca->query($sql) < 0) 
				return 0;	

			return 1;

		} else
			return 2;

    	}

	function validaDia($dia, $almacen) {
		global $sqlca;

		$turno = 0;

		$sql = " SELECT validar_consolidacion('$dia',$turno,'$almacen') ";

		$sqlca->query($sql);

		$estado = $sqlca->fetchRow();

		echo "devolvio:\n";
		var_dump($estado);

		if($estado[0] == 1){
			return 0;//Consolidado
		}else{
			return 1;//No consolidado
		}
	}
	
	function validaVarilla($almacen, $dia, $tanque) {
		global $sqlca;

		$sql = "SELECT count(*) FROM comb_ta_mediciondiaria WHERE dt_fechamedicion='$dia' AND ch_sucursal='$almacen' AND ch_tanque='$tanque';";

		if ($sqlca->query($sql) < 0) 
			return false;
	
		var_dump($sql);

		$a = $sqlca->fetchRow();	
		if($a[0]>=1) {
			return 0; // ya se ingresó la varilla
		} else {
			return 1; // se puede ingresar normalmente
		}
	}
}
