<?php

class ContMecanicosModel extends Model {

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
    

    	function BuscarContometros($fecha, $fecha2, $estacion, $turno) {
		global $sqlca;
	
		$query = "SELECT
				to_char(f.systemdate, 'DD/MM/YYYY') fecha,
				f.shift turno, 
				to_char(f.created, 'DD/MM/YYYY') || ' ' || to_char(f.created, 'HH:MI:SS AM') dia,
				count(g.f_grade_id)||' '||' Mangueras' as mangueras,
				f.created sistema
			 FROM
				f_totalizerm f
				LEFT JOIN f_grade g ON (f.f_grade_id = g.f_grade_id)
			 WHERE
				f.systemdate BETWEEN to_date('$fecha', 'DD/MM/YYYY') and to_date('$fecha2', 'DD/MM/YYYY')
				AND f.warehouse = '" . pg_escape_string($estacion) . "'";
		if($turno != '')
		$query .="
				AND f.shift = '" . pg_escape_string($turno) . "'";
		$query .="
			GROUP BY
				systemdate,
				created,
				shift
			 ORDER BY
				systemdate desc,
				turno desc;";

		echo $query;

		if ($sqlca->query($query) <= 0){
			return false;
	        }

		$resultado = array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$resultado[$i]['fecha']		= $a[0];
			$resultado[$i]['turno']		= $a[1];
			$resultado[$i]['dia']		= $a[2];
			$resultado[$i]['mangueras']	= $a[3];
			$resultado[$i]['sistema']	= $a[4];
		}
	
		return $resultado;
    	}

	function ReporteContometros($fecha, $fecha2, $estacion,$turno) {
		global $sqlca;

		$validar = ContMecanicosModel::ValidarTurnos($fecha,$fecha2,$turno);
		
		if($validar[0] == 1){
			?><script>alert("<?php echo 'El dia '.$_REQUEST['fecha'].' solo tiene '.$validar[1].' turnos' ?> ");</script><?php
		}else{

			//DIA ANTERIOR   
			$date_system  = substr($fecha,6,4)."-".substr($fecha,3,2)."-".substr($fecha,0,2);
			$date_prev   = date("Y-m-d", strtotime("$date_system -1 day"));
			//DIA ACTUAL
			$fecha = substr($fecha,6,4)."-".substr($fecha,3,2)."-".substr($fecha,0,2);
			$actual = substr($fecha2,6,4)."-".substr($fecha2,3,2)."-".substr($fecha2,0,2);
			//TURNO ANTERIOR
			$turno_prev = trim($turno) - 1;
		
			$query = "SELECT
					I.lado lado,
					I.manguera manguera,
					I.continicial cntinicial,
					F.contfinal cntfinal,
					round(F.contfinal - I.continicial,2) cantidad,
					round((round(F.contfinal - I.continicial,2) * I.precio),2) importe,
					I.precio precio,
					I.producto producto
				FROM(
					SELECT
						l.f_pump_id lado,
						m.f_grade_id manguera,
						round(c.volume,2) continicial,
						round(p.nu_preciocombustible,2) precio,
						p.ch_nombrebreve producto
					FROM
						f_grade m
						JOIN f_pump l ON(l.f_pump_id = m.f_pump_id)
						JOIN f_totalizerm c ON(c.f_grade_id = m.f_grade_id)
						JOIN comb_ta_combustibles p ON(m.product = p.ch_codigocombustible)
					WHERE";
		
			if($turno > 1){
			$query .="
						c.systemdate = '$actual'
						AND c.shift = '$turno_prev'
						AND c.warehouse = '" . pg_escape_string($estacion) . "'";		
			}elseif($turno == "1"){
			$query .="
						c.systemdate = '$date_prev'
						AND c.warehouse = '" . pg_escape_string($estacion) . "'
						AND c.shift in(SELECT
							MAX(shift)
						FROM
							f_totalizerm
						WHERE
							systemdate = '$date_prev')";
			}elseif(empty($turno)){
			$query .="
						c.systemdate = '$date_prev'
						AND c.warehouse = '" . pg_escape_string($estacion) . "'
						AND c.shift in(SELECT
							MAX(shift)
						FROM
							f_totalizerm
						WHERE
							systemdate = '$date_prev')";
			}else{
			$query .="
						c.systemdate = '$date_prev'
						AND c.warehouse = '" . pg_escape_string($estacion) . "'
						AND c.shift in(SELECT
							MAX(shift)
						FROM
							f_totalizerm
						WHERE
							systemdate = '$date_prev')";
			}
			$query .="
					GROUP BY
						lado,
						manguera,
						continicial,
						precio,
						producto
					ORDER BY
						manguera
				) AS I

				LEFT JOIN

				(SELECT
						l.f_pump_id lado,
						m.f_grade_id manguera,
						round(c.volume,2) contfinal,
						round(p.nu_preciocombustible,2) precio,
						p.ch_nombrebreve producto
					FROM
						f_grade m
						JOIN f_pump l ON(l.f_pump_id = m.f_pump_id)
						JOIN f_totalizerm c ON(c.f_grade_id = m.f_grade_id)
						JOIN comb_ta_combustibles p ON(m.product = p.ch_codigocombustible)
					WHERE";

			if($turno != ''){
			$query .= "
						c.systemdate = '$fecha'
						AND c.warehouse = '" . pg_escape_string($estacion) . "'
						AND c.shift = '$turno'";
			}else{
			$query .= "
						c.systemdate = '$actual'
						AND c.warehouse = '" . pg_escape_string($estacion) . "'
						AND c.shift IN
						(SELECT
							MAX(shift)
						FROM
							f_totalizerm
						WHERE
							systemdate = '$actual')";
			}		
			$query .= "
					GROUP BY
						lado,
						manguera,
						contfinal,
						precio,
						producto
					ORDER BY
						manguera
				) AS F ON I.lado = F.lado AND I.manguera = F.manguera;";
		
			//echo $query;

		}

		if ($sqlca->query($query) < 0)
			return false;
	   
		$resultado = array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$resultado[$i]['lado']		= $a[0];
			$resultado[$i]['manguera']	= $a[1];
			$resultado[$i]['cntinicial']	= $a[2];
			$resultado[$i]['cntfinal']	= $a[3];
			$resultado[$i]['cantidad']	= $a[4];
			$resultado[$i]['importe']	= $a[5];
			$resultado[$i]['precio']	= $a[6];
			$resultado[$i]['producto']	= $a[7];
		}
	
		return $resultado;
    	}

	function eliminarRegistro($systemdate,$shift){
		global $sqlca;

		$anio = substr($systemdate,6,4);
		$mes = substr($systemdate,3,2);
		$dia = substr($systemdate,0,2);

		$fecha = $anio."-".$mes."-"."$dia";

		$query = "DELETE FROM f_totalizerm WHERE systemdate = '$fecha' AND shift = '$shift';";

		echo $query;

		$sqlca->query($query);
		return 'OK';

	}

	function Consolidacion($fecha){
		global $sqlca;

		$query = "select count(*) from pos_consolidacion where dia = to_date('$fecha','DD/MM/YYYY')";

		echo $query;

		if ($sqlca->query($query) < 0) 
			return false;
		$a = $sqlca->fetchRow();
		if($a[0]>=1){
			return 1;
		}else{
			return 0;
		}

	}

	function ValidarTurnos($fecha, $fecha2,$turno) {
		global $sqlca;

		$query = "SELECT max(shift) FROM f_totalizerm WHERE systemdate BETWEEN to_date('$fecha', 'DD/MM/YYYY') and to_date('$fecha2', 'DD/MM/YYYY');";

		echo $query;

		if ($sqlca->query($query) < 0) 
			return false;
		$a = $sqlca->fetchRow();
		if($a[0]<$turno){
			$shift = $a[0];
			return Array(1,$shift);
		}else{
			return 0;
		}
	}
}
