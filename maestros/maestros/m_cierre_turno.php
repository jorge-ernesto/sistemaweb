<?php

class CierreTurnoModel extends Model {

	function buscar($fecha,$fecha2,$campo){

		global $sqlca;
		
		if ($campo == "0") {
		    $cond1 = " and stype = '$campo' ";
		}elseif($campo == "1"){
           	    $cond2 = " and stype = '1' ";
		}

		$sql = "SELECT  
				stype,
				systemdate,
				begintime,
				endtime,
				created,
				createdby
			FROM 
				s_shiftconstraint
			WHERE
				created BETWEEN to_date('$fecha','DD/MM/YYYY') AND to_date('$fecha2','DD/MM/YYYY')
				$cond1 $cond2
			ORDER BY
				created;";

		echo $sql;
	
		if ($sqlca->query($sql) < 0)
			return false;
	    
		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$resultado[$i]['stype']		= $a[0];
			$resultado[$i]['systemdate']	= $a[1];
			$resultado[$i]['begintime'] 	= $a[2];
			$resultado[$i]['endtime'] 	= $a[3];
			$resultado[$i]['created'] 	= $a[4];
			$resultado[$i]['createdby'] 	= $a[5];	
		}
		
		return $resultado;
  	}

	function insertar($campo,$hoy,$hora_inicial,$hora_final,$fecha2,$usuario) {
		global $sqlca;

		$validacion = CierreTurnoModel::ValidaCierreDia($tipo,$h_ini,$h_fin);

		/*?><script>alert("<?php echo '+++ la validacion es: '.$validacion ; ?> ");</script><?php*/

		$fecha_servidor = date('Y-m-d');

		$anio = substr($hoy,6,4);
		$mes = substr($hoy,3,2);
		$dia = substr($hoy,0,2);

		$fecha = $anio."-".$mes."-"."$dia";

		if ($validacion == 1) {

		$sql = "insert into
					s_shiftconstraint
							(stype,
							 systemdate,
							 begintime,
							 endtime,
							 created,
							 createdby)
					      values
							('$campo',
							 '$fecha',
 						    	 '$hora_inicial',
							 '$hora_final',
							 now(),
							 '$usuario')";
		echo $sql;
		
		
			if ($sqlca->query($sql) < 0)
					return 0;
				return 1;
			}else{
				return 2;
			}


	}

	function ValidaCierreDia($tipo, $h_ini,$h_fin){
		global $sqlca;

		$tipo  = $_REQUEST['campo'];
		$h_ini = $_REQUEST['hora_inicial'];
		$h_fin = $_REQUEST['hora_final'];

		$sql = "select count(*) from s_shiftconstraint where stype='$tipo' and begintime = '$h_ini' and endtime = '$h_fin';";
		echo $sql;

		if ($sqlca->query($sql) < 0) 
			return false;

		$a = $sqlca->fetchRow();

		if($a[0]>=1){
			return 0;
		}else{
			return 1;
		}

	}
}
