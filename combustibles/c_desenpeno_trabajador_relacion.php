<?php

session_start();

include_once('/sistemaweb/include/dbsqlca.php');
$sqlca = new pgsqlDB('localhost', 'postgres', 'postgres', 'integrado');
include('/sistemaweb/include/mvc_sistemaweb.php');

include('movimientos/t_desenpeno_trabajador.php');
include('movimientos/m_desenpeno_trabajador.php');
include('movimientos/c_desenpeno_trabajador.php');

include_once('../include/libexcel/Worksheet.php');
include_once('../include/libexcel/Workbook.php');
include_once('../include/Classes/PHPExcel.php');

$objmodel = new DesenpenoModel();
$objtem = new DesenpenoTemplate();
$objcomn = new DesenpenoController();


$accion = $_REQUEST['accion'];

try {
    if ($accion == "tipodocumento") {
        $almacen = $_SESSION['almacen'];
        $tipo_doc_numero = trim($_REQUEST['documento']);
        $result = DesenpenoModel::obtenerSucursales("");
        $cmb_serie = "<select id=serie_doc>";
        foreach ($result as $value) {
            if (strcmp($almacen, $value[0]) == 0) {
                $cmb_serie.="<option value=$value[0] selected> " . $value[1] . "</option>";
            } else {
                $cmb_serie.="<option value=$value[0] > " . $value[1] . "</option>";
            }
        }

        $cmb_serie.="</select>";
        echo "{'dato':'$cmb_serie','cliente':null}";

	} else if ($accion == "upadte_datos") {

		foreach ($_REQUEST['data'] as $valor) {
			$datos_update = explode("*", $valor);
			DesenpenoModel::Actualizarpayment($datos_update[0], $datos_update[1]);
		}

	} else if ($accion == "tipo_documento_gennerar") {

		$dia		= trim($_REQUEST['fecha']);

		$flag = DesenpenoModel::validaDia($dia);

		if($flag == 1){
			echo $flag;
			//echo "<blink style='color: red'><b>¡ Dia consolidado, seleccionar otra fecha !</blink>";
			//exit();
		}else{

			$result = DesenpenoModel::obtenerTipoDocumnetos_otros();

			echo "<select id='cmbtipo_doc'>";

			foreach ($result as $key => $value) {
				echo "<option value='" . $value[0] . "'>" . $value[1] . "</option>";
			}

			echo "</select>";
		}

	} else if ($accion == "mostar_resultado_data" || $accion == "mostar_resultado_data_excel" ) {

        $fecha_inicio = $_REQUEST['fecha_inicio'];
        $fecha_final = $_REQUEST['fecha_final'];
        $sucursal = $_REQUEST['sucursal'];
        $limit = $_REQUEST['limit_mostrar'];
        $cliente = trim($_REQUEST['cliente']);
		$type_view = trim($_REQUEST['type_view']);
		

        try {
           // $json_data = DesenpenoModel::MostarResultadoDetalle($fecha_inicio, $fecha_final, $sucursal, $limit, $cliente);
          $venta_dia= DesenpenoModel:: getventadia($fecha_inicio,$fecha_final);
		  $trabajadores=DesenpenoModel::gettrabajador($fecha_inicio,$fecha_final,$sucursal);
		 $ventasLubricantes=DesenpenoModel::getventalubricante($fecha_inicio,$fecha_final,$sucursal);
		 $ventasGnv=DesenpenoModel::getventagnv($fecha_inicio,$fecha_final,$sucursal,$sucursal);
		 
		  $json_ventas=array();
		  $nombres_tra=array();
		  
	$tmp="";
		  	foreach ($trabajadores as $keydt_dia => $ch_posturno){
			foreach ($ch_posturno as $keyposturno => $ch_lado){
				foreach ($ch_lado as $keych_lado => $valor){
					
				
				//cuando es lubricantes primero busca si estan matriculado los que vende combustible.
					foreach($venta_dia[$keyposturno][$keydt_dia][$keych_lado] as $lado  => $valueventas){
						$venta_galones=$valueventas['galon_vendido'];
						$venta_importe=$valueventas['importe_vendido'];
						$magueraactual=$lado;
						
						
;						
						$ch_codigo_trabajador=trim($valor['ch_codigo_trabajador']);
						

						
						if($valueventas['galon_vendido']<0 || $valueventas['importe_vendido']<0 ){
							$infopostran=DesenpenoModel::getventadia_postrans($keydt_dia,$keyposturno,$keych_lado,$sucursal,$magueraactual);
							$venta_galones=$infopostran[0]['cnt_vol'];
							$venta_importe=$infopostran[0]['cnt_val'];
							
							
						}
						
						
						
					    $json_ventas[$keyposturno][$ch_codigo_trabajador][$keydt_dia]['monto_galon']+=$venta_galones;
						 $json_ventas[$keyposturno][$ch_codigo_trabajador][$keydt_dia]['monto_importe']+=$venta_importe;
						$json_ventas[$keyposturno][$ch_codigo_trabajador][$keydt_dia]['monto_lubricante']=DesenpenoModel::getLubricante($ventasLubricantes,$keydt_dia,$keyposturno,$valor['ch_codigo_trabajador']);
						$cod=trim($valor['ch_codigo_trabajador']);
						$nombres_tra[$cod]=$valor['nombre'];
						
						
					}
				}

			}
		}
			
			//anadimos los lubicantes
			
			
		foreach($ventasLubricantes as $keych_posturno =>$codigoTrbajador){
    		foreach($codigoTrbajador as $keycod => $dt_dia){
    			foreach($dt_dia as $keydt_dia => $valores){
    				if($valores['VISU']=="N"){
    					if(is_null($json_ventas[$keych_posturno][$keycod][$keydt_dia]['monto_galon']) || empty($json_ventas[$keych_posturno][$keycod][$keydt_dia]['monto_galon']) ){
    					$json_ventas[$keych_posturno][$keycod][$keydt_dia]['monto_galon']=0;
						$json_ventas[$keych_posturno][$keycod][$keydt_dia]['monto_importe']=0;
						}
						$json_ventas[$keych_posturno][$keycod][$keydt_dia]['monto_lubricante']=$valores['importe'];
						
						if(is_null($json_ventas[$keych_posturno][$keycod][$keydt_dia]['monto_gnv_galon']) || empty($json_ventas[$keych_posturno][$keycod][$keydt_dia]['monto_gnv_galon']) ){
						$json_ventas[$keych_posturno][$keycod][$keydt_dia]['monto_gnv_galon']=0;
						$json_ventas[$keych_posturno][$keycod][$keydt_dia]['monto_gnv_importe']=0;
						}
						
						$cod=trim($keycod);
						$nombres_tra[$cod]=$valores['nombre'];
						$ventasLubricantes[$keych_posturno][$keycod][$keydt_dia]['VISU']='S';
    				
					         				}
														}
			}
    		
    	}

//anadimos GNV
	foreach($ventasGnv as $keych_posturno_gnv =>$codigoTrbajadorgnv){
    		foreach($codigoTrbajadorgnv as $keycodgnv => $dt_diagnv){
    			foreach($dt_diagnv as $keydt_diagnv => $valores){
    				//if($valores['VISU']=="N"){
    					if(is_null($json_ventas[$keych_posturno_gnv][$keycodgnv][$keydt_diagnv]['monto_galon']) || 
    					empty($json_ventas[$keych_posturno_gnv][$keycodgnv][$keydt_diagnv]['monto_galon'])
						){
    					$json_ventas[$keych_posturno_gnv][$keycodgnv][$keydt_diagnv]['monto_galon']=0;
					    $json_ventas[$keych_posturno_gnv][$keycodgnv][$keydt_diagnv]['monto_importe']=0;
					    }
						
					
						$json_ventas[$keych_posturno_gnv][$keycodgnv][$keydt_diagnv]['monto_gnv_galon']=$valores['cantidad'];
						$json_ventas[$keych_posturno_gnv][$keycodgnv][$keydt_diagnv]['monto_gnv_importe']=$valores['importe'];
						$cod=trim($keycodgnv);
						$nombres_tra[$cod]=$valores['nombre'];
						
						
														}
			}
    		
    	}


		
		ksort($json_ventas);
		
	
		

		  
		 
		  
		  if($accion == "mostar_resultado_data_excel"){
			DesenpenoTemplate::CrearTablaExcel($json_ventas,$nombres_tra,$type_view,$fecha_inicio,$fecha_final);
		  }else{
		  	DesenpenoTemplate::CrearTablareporte($json_ventas,$nombres_tra,$type_view);
		  }
        } catch (Exception $e) {
            echo "<h2 style='color:red;'>" . $e->getMessage() . "</h2>";
        }
    }else if ($accion == "nuevo_registro") {
        $almacen = $_SESSION['almacen'];
        $estaciones = DesenpenoModel::obtenerSucursales("");
        $ListTrabajadores = DesenpenoModel::getListTrabajadores();

        echo DesenpenoTemplate::FormularioPrincipalSegundario($estaciones, $ListTrabajadores);
    } else if ($accion == "insert_gnv") {

		$dia		= trim($_REQUEST['fecha']);

		$flag = DesenpenoModel::validaDia($dia);

		if($flag == 1){
			echo "<blink style='color: red'><b>¡ Dia consolidado, seleccionar otra fecha !</blink>";
			exit();
		}else{

			try {
			
			
				
			DesenpenoModel::setventasgnv($_REQUEST['dataenv']);
			echo "<h2 style='color:#336699'>Se inserto correcto la venta GNV</h2>";
			} catch (Exception $e) {
				echo "<h2 style='color:red'>Error Insercion: ".$e->getMessage()."</h2>";
				exit();
			}

		}

	} else if ($accion == "get_gnv") {
        	
        try {
        
               $almacen=$_REQUEST['dataenv']['almacen'];
			   $dia=$_REQUEST['dataenv']['dia'];

                $datagnv = DesenpenoModel::getventasgnv($dia,$almacen);
				
;
if(count($datagnv)>0){
	DesenpenoTemplate::CrearTablaGNV($datagnv);
}else{
	echo "<h2 style='color:#336699'>No hay informacion en la fecha $dia </h2>";
}
                
          
        } catch (Exception $e) {
            echo "ERROR_:No se encontro liquidaciones disponibles";
            exit();
        }
    } 
 else if ($accion == "del_gnv") {

        try {
        
               $data=$_REQUEST['dataenv'];
               $arraydata=explode("*", $data);
			   $dia=$arraydata[0];
			   $turno=$arraydata[1];
			   $codigo_trabajador=$arraydata[2];
			   if(empty($dia) || empty($turno) || empty($codigo_trabajador) ){
			   	throw new Exception(" Parametros vacios..", 1);
				   
			   }
			   

               DesenpenoModel::delventasgnv($dia,$turno,$codigo_trabajador) ;
               echo "<h2 style='color:#336699'>Se elimino correcto la venta GNV(dia=".$dia.",turno=".$turno.",codigo=".$codigo_trabajador.")</h2>";
          
        } catch (Exception $e) {
            echo "Error eliminacion:".$e->getMessage();
            exit();
        }
    } 

    

} catch (Exception $r) {
	echo "{'estado':'error','mes':'" . $r->getMessage() . "'}";
}

