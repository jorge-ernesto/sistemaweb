<?php

session_start();

include_once('/sistemaweb/include/dbsqlca.php');
$sqlca = new pgsqlDB('localhost', 'postgres', 'postgres', 'integrado');
include('/sistemaweb/include/mvc_sistemaweb.php');

include('Anular_tickes/t_anular_tickes.php');
include('Anular_tickes/m_anular_tickes.php');
include('Anular_tickes/c_anular_tickes.php');

$objmodel	= new AnularTickesModel();
$objtem 	= new AnularTickesTemplate();
$objcomn 	= new AnularTickesController("");

$accion 	= $_REQUEST['data']['accion'];

$txtnualmacen	= $_REQUEST['data']['txtnualmacen'];
$txttickes	= $_REQUEST['data']['txttickes'];
$txtfecha	= $_REQUEST['data']['txtfecha'];
$txtcaja	= $_REQUEST['data']['txtcaja'];
$txttd		= $_REQUEST['data']['txttd'];
$txttv		= $_REQUEST['data']['txttv'];
$txtturno 	= $_REQUEST['data']['txtturno'];
$txttm	 	= $_REQUEST['data']['txttm'];


try {

	if ($accion == "buscar") {

		$sata_result	= AnularTickesModel::getData($txtnualmacen, $txtcaja, $txttickes, $txtfecha, $txttd, $txttv, $txtturno, $txttm);
		$estado 	= "S";
		$msg 		= "";
		$data_json 	= "[";

    	foreach ($sata_result as $rows) {
	    	$data_json .= "{";
	    	$data_json .= "'tm':'" . $rows['tm'] . "',";
	    	$data_json .= "'caja':'" . $rows['caja'] . "',";
	    	$data_json .= "'td':'" . $rows['td'] . "',";
	    	$data_json .= "'turno':'" . $rows['turno'] . "',";
	    	$data_json .= "'codigo':'" . $rows['codigo'] . "',";
	    	$data_json .= "'cantidad':'" . $rows['cantidad'] . "',";
	    	$data_json .= "'precio':'" . $rows['precio'] . "',";
	    	$data_json .= "'igv':'" . $rows['igv'] . "',";
	    	$data_json .= "'importe':'" . $rows['importe'] . "',";
	    	$data_json .= "'ruc':'" . $rows['ruc'] . "',";
	    	$data_json .= "'tipo':'" . $rows['tipo'] . "',";
	    	$data_json .= "'pump':'" . $rows['pump'] . "',";
	    	$data_json .= "'producto':'" . $rows['producto'] . "',";
	    	$data_json .= "'tarjeta':'" . $rows['tarjeta'] . "',";
	    	$data_json .= "'placa':'" . $rows['placa'] . "',";
	    	$data_json .= "'cuenta':'" . $rows['cuenta'] . "',";
	    	$data_json .= "'bi':'" . $rows['bi'] . "',";
	    	$data_json .= "'grupo':'" . $rows['grupo'] . "',";
	    	$data_json .= "'dia':'" . $rows['dia'] . "',";
	    	$data_json .= "'fecha':'" . $rows['fecha'] . "',";
	    	$data_json .= "'usr':'" . $rows['usr'] . "'},";

            if ($rows['importe'] == 0) { //cai verificar la anulacion, no sirve al redondear a cero
                $estado	= 'N';
				$msg	= "El ticket Nro. ".$txttickes." ya se encuentra anulado";
			}

			$diav = substr($txtfecha,6,4)."-".substr($txtfecha,3,2)."-".substr($txtfecha,0,2);
			$flag = AnularTickesModel::Consolidacion($diav, $txtnualmacen);

			if ($flag == 1) {
               	$estado	= 'N';
				$msg	= "El dia esta consolidado!";
			}
		}

    	if (count($sata_result) == 0) {
			$estado = 'N';
            $msg	= "No se encontro el Ticket Nro. ".$txttickes;
        }

        $localtime 	= localtime();
		$day 		= $localtime[3];

		if(strlen($day) < 2 )
		$day = '0'.$day;

		$month = $localtime[4] + 1;

		if(strlen($day) < 2 )
		$month = '0'.$month;						

		$year 			= '20'.substr($localtime[5], -2);
		$today 			= $year.'-'.$month.'-'.$day;
		$today_before 	= date_create($year.'-'.$month.'-'.$day);

		// RESTRICCION PARA FECHA EMISIÓN DE F.E SOLO ANULADOS y MENOR A 5 DIA
		date_add($today_before, date_interval_create_from_date_string('-5 days'));
		$today_before = date_format($today_before, 'Y-m-d');

		if (count($sata_result) > 0 && $rows['fecha'] <= $today && $rows['usr'] !=''){
        	if (count($sata_result) > 0 && $rows['usr'] !='' && $rows['fecha'] < $today_before ) {
				$estado = 'N';
				$msg = "Solo se pueden anular documentos electrónicos hasta 5 días. \\nTicket Nro. " . $txttickes . " Fecha emision: " . $rows['fecha'];
	        }
		}
		// CERRAR RESTRICCION PARA FECHA EMISIÓN DE F.E SOLO ANULADOS y MENOR A 5 DIA

		if (count($sata_result) > 0)
			$data_json = substr($data_json, 0, -1);

		$data_json .= "]";

		echo "{'dato':$data_json,'estado':'$estado','msg':'$msg'}";
	} else if ($accion == "anular_tickes") {

    	$validacion1 = $_REQUEST['data']['chk_validacion1'];
    	$validacion2 = $_REQUEST['data']['chk_validacion2'];

    	$begin = AnularTickesModel::BEGINTransaccion();
       	if ($validacion1 == 'SI' && $validacion2 == "SI") {
            $sata_result1 = AnularTickesModel::getData($txtnualmacen, $txtcaja, $txttickes, $txtfecha, $txttd, $txttv, $txtturno, $txttm);
            $i = 0;
    		foreach ($sata_result1 as $rows1) {
    			AnularTickesModel::Insertar_tickes_Anulado($txttickes, $txtcaja, $txtfecha, $rows1['importe']);
    			
				AnularTickesModel::Anular_tickes($txtnualmacen, $txtcaja, $txttickes, $txtfecha, $txttd, $txttv, $txtturno, $txttm,  $rows1['codigo'], $i);
				$i++;
    		}

			error_log("Etapa 1");
			error_log( json_encode( array( $txtnualmacen, $txtcaja, $txttickes, $txtfecha, $txttd, $txttv, $txtturno, $txttm,  $rows1['codigo'] ) ) );
			error_log( json_encode( $sata_result1 ) );

			$sata_result 		= AnularTickesModel::getData($txtnualmacen, $txtcaja, $txttickes, $txtfecha, $txttd, $txttv, $txtturno, $txttm);
			/*OPENSOFT-12: La function getData2 fue comentada por mal consulta a ebi_queue*/
			//$sata_result2		= AnularTickesModel::getData2($txtnualmacen, $txtcaja, $txttickes, $txtfecha, $txttd, $txttv, $txtturno, $txttm,  $rows1['codigo']);
			$estado_aulacion	= "NO";
			
			error_log("Etapa 2");
			error_log( json_encode( $sata_result ) );
			error_log( json_encode( $sata_result2 ) );

			foreach ($sata_result as $rows) {
				if ($rows['importe'] == 0) {
					//Completa la transaccion para los tickets
					if ($rows['usr'] == '' || $rows['usr'] == NULL || empty($rows['usr'])) {
            			$commit = AnularTickesModel::COMMITransaccion();	
                		$estado_aulacion = "SI";
                    	break;
                	}else{//Completa la transaccion para los documentos electronicos
                		// foreach ($sata_result2 as $rows2) {
                			// if ($rows2['_id'] != '' || $rows2['_id'] != NULL || !empty($rows2['_id'])){
                				$commit = AnularTickesModel::COMMITransaccion();
                    			$estado_aulacion = "SI";
                    			break;
	                    	// }
	                	// }
             		}
             	}
            }
			echo "{'estado':'$estado_aulacion'}";
		} else {
			echo "{'estado':'NO'}";
			$rollback = AnularTickesModel::ROLLBACKTransaccion();
		}
	}else if($accion == "Turnos"){

		$cajas		= AnularTickesModel::ObtenerCajas($txtnualmacen);
		$turnos 	= AnularTickesModel::ObtenerFechaDTurno($txtfecha);

		$turnoa 	= 0;

		$cadena		= '';

		$cadena.='<option value="">Seleccionar...</option>';

		for($i = 0; $i < $turnos[0]['turno']; $i++){

			if($turnoa < $turnos[0]['turno'])
				$turnoa = $turnoa + 1;
  
			$cadena.='<option value="'.$turnoa.'">' . $turnoa . '</option>';

		}

		$cadena2	= '';

		$cadena2	.='<option value="">Seleccionar...</option>';

		foreach($cajas as $fila) {

			$caja = $fila['name'];       
			$cadena2.='<option value="'.$caja.'">' . $fila['name'] . '</option>';

		}

		echo "{'msg':'" . $cadena . "', 'msg2':'" . $cadena2 . "'}";

	}



} catch (Exception $r) {

    echo "{'estado':'error','mes':'" . $r->getMessage() . "'}";
}
