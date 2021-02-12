<?php

class DesenpenoModel extends Model {

    function IniciarTransaccion() {
        global $sqlca;
        try {

            $sql = "BEGIN";

            if ($sqlca->query($sql) < 0) {
                throw new Exception("No se pudo INICIAR la TRANSACION");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    function COMMITransaccion() {
        global $sqlca;
        try {

            $sql = "COMMIT";

            if ($sqlca->query($sql) < 0) {
                throw new Exception("No se pudo procesar la TRANSACION");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    function ROLLBACKTransaccion() {
        global $sqlca;
        try {

            $sql = "ROLLBACK";

            if ($sqlca->query($sql) < 0) {
                throw new Exception("No se pudo Retroceder el proceso.");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

	function getListTrabajadores() {
		global $sqlca;

		$sql = "
				SELECT 
				     t.ch_codigo_trabajador,
					 t.ch_apellido_paterno||'-'||t.ch_apellido_materno||'-'||t.ch_nombre1 as nombre
				FROM
					pla_ta_trabajadores t
				";

		if ($sqlca->query($sql) < 0)
			return false;

		 $result = Array();

        for ($i = 0; $i < $sqlca->numrows(); $i++) {
            $a = $sqlca->fetchRow();
            $result[] = array(trim($a[0]), trim($a[1]));
        }

        return $result;
	}

    function obtenerSucursales($alm) {
        global $sqlca;

        if (trim($alm) == "")
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
            $result[] = array($a[0], $a[1]);
        }

        return $result;
    }








	
	function generarVentaPoscontometros($dataposcontometro){
		$cantidaddias=count($dataposcontometro);
		$fechaactual="";
		
		$ordernar_data=array();
		$arrayglp=DesenpenoModel::getGlp();
		
	
		foreach($dataposcontometro as $keydate => $turno){
			
			$nuevafecha = strtotime ( '+1 day' , strtotime ( $keydate ) ) ;
			$nuevafecha = date ( 'Y-m-d' , $nuevafecha );
			$maximoTurno=max(array_keys($turno));
			
			
			foreach($turno as $keyturno => $lado){
				
				foreach ($lado as $keylado => $maguera){					
					foreach($maguera as $keymaguera => $valores){
					
					if($keyturno==$maximoTurno){
					if($dataposcontometro[$nuevafecha]!=null){
						$dataposcontometro[$keydate][$keyturno][$keylado][$keymaguera]['cnt_vol_end']=$dataposcontometro[$nuevafecha]['1'][$keylado][$keymaguera]['cnt_vol'];
						$dataposcontometro[$keydate][$keyturno][$keylado][$keymaguera]['cnt_val_end']=$dataposcontometro[$nuevafecha]['1'][$keylado][$keymaguera]['cnt_val'];
						
						$dataposcontometro[$keydate][$keyturno][$keylado][$keymaguera]['fecha_ok']=$nuevafecha;
						$dataposcontometro[$keydate][$keyturno][$keylado][$keymaguera]['turno_ok']="1";
					}else{
						$ultimafecha=max(array_keys($dataposcontometro));
						if($ultimafecha==$keydate){
							$dataposcontometro[$keydate]=null;
						}
					}
					
					}else{
						$turnoCurr=$keyturno+1;
						if($dataposcontometro[$keydate][$turnoCurr]!=null){
						$dataposcontometro[$keydate][$keyturno][$keylado][$keymaguera]['cnt_vol_end']=$dataposcontometro[$keydate][$turnoCurr][$keylado][$keymaguera]['cnt_vol'];
						$dataposcontometro[$keydate][$keyturno][$keylado][$keymaguera]['cnt_val_end']=$dataposcontometro[$keydate][$turnoCurr][$keylado][$keymaguera]['cnt_val'];
						$dataposcontometro[$keydate][$keyturno][$keylado][$keymaguera]['fecha_ok']=$keydate;
						$dataposcontometro[$keydate][$keyturno][$keylado][$keymaguera]['turno_ok']=$turnoCurr;
						}else{
							throw new Exception("Error al buscar el siguiente turno, ".$keydate."-".$turnoCurr);
						}
					
					}
					
					
					}
				}
				
			}}




     $dataOrdena=array();

	foreach($dataposcontometro as $keydate => $turno){

			foreach($turno as $keyturno => $lado){
				foreach ($lado as $keylado => $maguera){					
					foreach($maguera as $keymaguera => $valores){
						
					
					if(!empty($arrayglp[$keylado][$keymaguera]) || $arrayglp[$keylado][$keymaguera]!=null ){//si es GLP
					
					$dataOrdena[$valores['turno_ok']][$valores['fecha_ok']][$keylado][$keymaguera]=array(
					"cnt_vol"=>$valores['cnt_vol'],"cnt_vol_end"=>$valores['cnt_vol_end'],"galon_vendido"=>(($valores['cnt_vol_end']-$valores['cnt_vol'])/3.785411784),
					"cnt_val"=>$valores['cnt_val'],"cnt_val_end"=>$valores['cnt_val_end'],"importe_vendido"=>($valores['cnt_val_end']-$valores['cnt_val']),
					);
					}else{
						$dataOrdena[$valores['turno_ok']][$valores['fecha_ok']][$keylado][$keymaguera]=array(
					"cnt_vol"=>$valores['cnt_vol'],"cnt_vol_end"=>$valores['cnt_vol_end'],"galon_vendido"=>($valores['cnt_vol_end']-$valores['cnt_vol']),
					"cnt_val"=>$valores['cnt_val'],"cnt_val_end"=>$valores['cnt_val_end'],"importe_vendido"=>($valores['cnt_val_end']-$valores['cnt_val']),
					);
					}
					}
				}
				
			}
			
		}

	

		
		return $dataOrdena;

		
	}

    function getventalubricante($fecha_inicio,$fecha_final,$alm) {
        global $sqlca;

		$F_I=$fecha_inicio;
		$F_F=$fecha_final;
		$generar_mes=array();
		$generar_mes[date("Ym",strtotime($fecha_inicio))]=0;//el primero
			
		while( strtotime($fecha_inicio)<=strtotime($fecha_final) ){
			
		    $messiguiente=strtotime ( '+1 day' , strtotime ( $fecha_inicio ) ) ;
			$fecha_inicio=date("Y-m-d",$messiguiente);
			if(strtotime($fecha_inicio)<=strtotime($fecha_final)){
				$generar_mes[date("Ym",$messiguiente)]=0;
			}
			
		}

$keypostrans=array_keys($generar_mes);

$query_trabajador="";

$sql="SELECT * FROM  (
					SELECT 
						t.ch_codigo_trabajador,
						t.ch_apellido_paterno,
						t.ch_apellido_materno,
						t.ch_nombre1,
						ch_nombre2,
						phl.ch_sucursal,
						to_char(phl.dt_dia,'YYYY-MM-DD') as dt_dia,
						phl.ch_posturno::INTEGER,phl.ch_lado::INTEGER,
						phl.ch_codigo_trabajador
					FROM pos_historia_ladosxtrabajador  phl
					INNER JOIN pla_ta_trabajadores t ON phl.ch_codigo_trabajador=t.ch_codigo_trabajador
					WHERE  ch_sucursal='$alm' AND dt_dia 
					BETWEEN '$F_I' AND '$F_F' AND phl.ch_tipo='M' ORDER BY phl.dt_dia ASC,phl.ch_posturno ASC,phl.ch_lado::INTEGER ASC
					) AS p
					LEFT JOIN
					(
					";
		
		foreach($keypostrans as $pos_trans){
			
			$patron.=" (SELECT 
				           p.dia as dia,p.turno,p.caja::INTEGER,es,p.importe
		              FROM pos_trans$pos_trans  p
		              WHERE tipo='M' AND es='$alm'
		              AND codigo IN(SELECT art_codigo FROM int_articulos WHERE art_linea='000022')
		              )UNION";
		}


        $quey_pre_preparado= substr(trim($patron), 0,-5);

       
        $sql .= "SELECT  *  FROM (".$quey_pre_preparado.") AS T WHERE T.dia BETWEEN '$F_I' AND '$F_F'   ORDER BY T.dia,T.turno,T.caja   ";
        $sql .= ") AS  o ON (p.dt_dia=to_char(o.dia,'YYYY-MM-DD') AND p.ch_posturno::INTEGER=o.turno::INTEGER AND o.caja::INTEGER =p.ch_lado::INTEGER ) ;";
		//echo $sql;
		
        if ($sqlca->query($sql) < 0)
            return false;

        $result = Array();
		
        for ($i = 0; $i < $sqlca->numrows(); $i++) {
            $a = $sqlca->fetchRow();
            $ch_codigo_trabajador=trim($a['ch_codigo_trabajador']);
            
           $importetmp= $result[$a['ch_posturno']][$ch_codigo_trabajador][$a['dt_dia']]['importe'];
		   if(!is_null($importetmp) || $importetmp !=null){
		   	$importetmp+=$a['importe'];
		   }else{
		   	$importetmp=$a['importe'];
		   }
			
			if($importetmp>0){
            $result[$a['ch_posturno']][$ch_codigo_trabajador][$a['dt_dia']] = array("importe"=>$importetmp,
            "nombre"=> trim($a['ch_apellido_paterno'])."".trim($a['ch_apellido_materno'])."".trim($a['ch_nombre1'])."".trim($a['ch_nombre2']),"VISU"=>"N"
			);
			}
			
			
			
        }
       
	   

        return $result;
		
    }
    
	
	
	
	
	//----------------------
	    function getventagnv($fecha_inicio,$fecha_final,$alm) {
        global $sqlca;





$sql=" 
		SELECT 
				t.ch_codigo_trabajador,
				t.ch_apellido_paterno||''||t.ch_apellido_materno||' '||t.ch_nombre1 as nombre,
				dia,
				turno,
				cantidad,
				importe 
		FROM ventasgnv v
		INNER JOIN pla_ta_trabajadores t ON v.codigo_trabajador=t.ch_codigo_trabajador
		WHERE v.dia BETWEEN '$fecha_inicio' AND '$fecha_final' AND almacen='$alm' ORDER BY v.turno,v.dia,t.ch_codigo_trabajador;

					";
		
	

        if ($sqlca->query($sql) < 0)
            return false;

        $result = Array();
		
        for ($i = 0; $i < $sqlca->numrows(); $i++) {
            $a = $sqlca->fetchRow();
            $ch_codigo_trabajador=trim($a['ch_codigo_trabajador']);
  			$result[$a['turno']][$ch_codigo_trabajador][$a['dia']]=array("importe"=>$a['importe'],"cantidad"=>$a['cantidad'],
			"nombre"=>$a['nombre'],"VISU"=>"N");
		  
        }
       
	   

        return $result;
		
    }
	//-----------------------
	
	
	
	function getLubricante(&$datalubricante,$keydt_dia,$keych_posturno,$keycod){
		if(is_null($datalubricante[$keych_posturno][$keycod][$keydt_dia])){
			return "0.00";
		}
		$datalubricante[$keych_posturno][$keycod][$keydt_dia]['VISU']='S';
		return $datalubricante[$keych_posturno][$keycod][$keydt_dia]['importe'];
		
	}



    function getventadia($fecha_inicio,$fecha_final) {
        global $sqlca;



        $sql = "SELECT * FROM (SELECT *, CASE WHEN ((
						 P.da_fecha<'$fecha_inicio'::DATE AND ch_posturno::INTEGER=PS.turno::INTEGER)
						 OR  ('$fecha_final'::DATE<P.da_fecha AND PS.turno::INTEGER=1 )  OR( P.da_fecha BETWEEN '$fecha_inicio'  AND '$fecha_final' ) ) THEN 'S' ELSE 'N' END AS filtro FROM (
						 SELECT  ch_posturno-1 as ch_posturno,da_fecha  FROM pos_aprosys 
						 WHERE da_fecha BETWEEN CAST('$fecha_inicio' AS DATE) - CAST('1 days' AS INTERVAL) AND CAST('$fecha_final' AS DATE) + CAST('1 days' AS INTERVAL) 
						 AND ch_poscd= 'S' ORDER BY da_fecha ASC) AS P
				INNER JOIN 
				(SELECT 
				num_lado::INTEGER,manguera,cnt_vol,cnt_val,dia,turno    
				FROM pos_contometros   ORDER BY dia,turno,num_lado::INTEGER,manguera) PS
				ON P.da_fecha=PS.dia) AS t 
				WHERE t.filtro='S' ORDER BY t.dia,t.turno,t.num_lado,t.manguera ;";

        if ($sqlca->query($sql) < 0)
            return false;

        $result = Array();
		
        for ($i = 0; $i < $sqlca->numrows(); $i++) {
            $a = $sqlca->fetchRow();
			   
            $result[$a['dia']][$a['turno']][$a['num_lado']][$a['manguera']] = array("cnt_vol"=>$a['cnt_vol'],"cnt_val"=> $a['cnt_val']);
			
			
			
        }
        return DesenpenoModel::generarVentaPoscontometros($result);
		
    }
    
        function getGlp() {
        global $sqlca;



        $sql = "
				SELECT
						ch_surtidor,
						ch_numerolado::INTEGER AS ch_numerolado,
						nu_manguera::INTEGER AS nu_manguera,
						ch_sucursal,
						'3.785411784' AS factor
				FROM  comb_ta_surtidores WHERE ch_codigocombustible='11620307';
";

        if ($sqlca->query($sql) < 0)
            return false;

        $result = Array();
		
        for ($i = 0; $i < $sqlca->numrows(); $i++) {
            $a = $sqlca->fetchRow();
			   
            $result[$a['ch_numerolado']][$a['nu_manguera']]= array("conv"=>$a['factor']);
			
			
			
        }
		
        return $result;
		
    }
    
    
    
        function getventadia_postrans($fecha,$turno,$lado,$alm,$maguerera) {
        global $sqlca;
        $aredia=explode("-", $fecha);
        $post_tran=$aredia[0]."".$aredia[1];

        $sql = "
			SELECT 
				sum(importe) as importe,
				sum(cantidad) as cantidad 
			FROM (
			SELECT 
			*
			FROM  pos_trans$post_tran
			WHERE dia='$fecha' AND  tipo='C' AND  turno='$turno' AND pump::INTEGER=$lado  AND es='$alm' )
			AS p
			INNER JOIN comb_ta_surtidores com ON p.pump::INTEGER=com.ch_numerolado::INTEGER AND p.codigo=com.ch_codigocombustible
			WHERE  nu_manguera=$maguerera AND com.ch_sucursal='$alm' LIMIT 1;
			        ";
					
	$arrayglp=DesenpenoModel::getGlp();

        if ($sqlca->query($sql) < 0)
            return array("cnt_vol"=>-1,"cnt_val"=> -1);

        $result = Array();
		
        for ($i = 0; $i < $sqlca->numrows(); $i++) {
            $a = $sqlca->fetchRow();
			if(!empty($arrayglp[$lado][$maguerera]) || $arrayglp[$lado][$maguerera]!=null ){//si es GLP
					
           $result[] = array("cnt_vol"=>($a['cantidad']/3.785411784),"cnt_val"=> $a['importe']);
			
        }else{
			 $result[] = array("cnt_vol"=>$a['cantidad'],"cnt_val"=> $a['importe']);
		}
		}
       return  $result;
		
    }
    
        function gettrabajador($fecha_inicio,$fecha_final,$alm) {
        global $sqlca;



        $sql = "
        SELECT 
			  t.ch_codigo_trabajador,t.ch_apellido_paterno,t.ch_apellido_materno,t.ch_nombre1,ch_nombre2,
			  phl.ch_sucursal,phl.dt_dia,phl.ch_posturno,phl.ch_lado::INTEGER,phl.ch_codigo_trabajador
       FROM pos_historia_ladosxtrabajador  phl
       INNER JOIN pla_ta_trabajadores t ON phl.ch_codigo_trabajador=t.ch_codigo_trabajador
       WHERE  ch_sucursal='$alm' AND dt_dia 
       BETWEEN '$fecha_inicio' AND '$fecha_final' AND phl.ch_tipo='C' ORDER BY phl.dt_dia ASC,phl.ch_posturno ASC,phl.ch_lado::INTEGER ASC;
        ";

        if ($sqlca->query($sql) < 0)
            return false;

        $result = Array();
		
        for ($i = 0; $i < $sqlca->numrows(); $i++) {
            $a = $sqlca->fetchRow();
			   
            $result[$a['dt_dia']][$a['ch_posturno']][$a['ch_lado']] = array("nombre"=>trim($a['ch_apellido_paterno'])." ".trim($a['ch_apellido_materno'])." ".trim($a['ch_nombre1'])." ".trim($a['ch_nombre2']),
			"ch_codigo_trabajador"=>$a['ch_codigo_trabajador']);
			
        }
		
		return  $result;
//var_dump($result);
       // return $result;
    }

  


	function setventasgnv($datainsert) {
        	global $sqlca;

        	try {
        		$dia=$datainsert['dia'];
        		$turno=$datainsert['turno'];
        		$codigo_trabajador=trim($datainsert['codigo_trabajador']);
        		$cantidad=$datainsert['cantidad'];
        		$importe=$datainsert['importe'];
        		$almacen=$datainsert['almacen'];
        		

	          	$sql = "
				INSERT INTO   ventasgnv 
				( 
				dia ,
				turno ,
				codigo_trabajador ,
				cantidad ,
				importe , 
				almacen
				  )
				VALUES('$dia',$turno,'$codigo_trabajador',$cantidad,$importe,'$almacen');
			";

		if ($sqlca->query($sql) < 0) {
                	throw new Exception("Error no se pudo insertar GNV.");
		}

        
            return $resultado;
        } catch (Exception $e) {
            throw $e;
        }
    }

    function getventasgnv($dia,$almacen) {
        global $sqlca;



        $sql = "SELECT * FROM ventasgnv WHERE dia='$dia' AND almacen='$almacen' ORDER BY  dia,turno,codigo_trabajador ;";

        if ($sqlca->query($sql) < 0)
            return false;

        $items = Array();

        for ($i = 0; $i < $sqlca->numrows(); $i++) {
            $a = $sqlca->fetchRow();
            $items[] = array(
			"dia"=> $a['dia']    , "turno"=> $a['turno']  , "codigo_trabajador"=> $a['codigo_trabajador']  , "cantidad"=> $a['cantidad']  , "importe"=> $a['importe']  ,
			 "almacen"=> $a['almacen'] 
			);
        }

      

        return $items;
    }
    
    
        function delventasgnv($dia,$turno,$codigo_trabajador) {
        global $sqlca;



        $sql = "
        DELETE FROM ventasgnv 
        		WHERE dia='$dia' 
        		AND turno='$turno' 
        		AND codigo_trabajador='$codigo_trabajador' ;";
				

        if ($sqlca->query($sql) < 0)
            return false;

        

      

      

        return $items;
    }
    
    



	function ReporteSubContometros($fecha, $fecha2, $estacion) {
        	global $sqlca;

       		$query = "SELECT
				round(sum(c.volume),2) cantidad,
				round(sum(c.volume * price),2) importe,
				p.ch_nombrebreve producto
			FROM
				f_grade m
				JOIN f_totalizerm c ON(c.f_grade_id = m.f_grade_id)
				JOIN comb_ta_combustibles p ON(m.product = p.ch_codigocombustible)
			WHERE
				c.systemdate BETWEEN to_date('$fecha', 'DD/MM/YYYY') and to_date('$fecha2', 'DD/MM/YYYY')
				AND c.warehouse = '$estacion'
			GROUP BY
				p.ch_nombrebreve;";

        echo $query;

        if ($sqlca->query($query) < 0)
            return false;

        $resultado = array();

        for ($i = 0; $i < $sqlca->numrows(); $i++) {
            $a = $sqlca->fetchRow();
            $resultado[$i]['cantidad'] = $a[0];
            $resultado[$i]['importe'] = $a[1];
            $resultado[$i]['producto'] = $a[2];
        }

        return $resultado;
    }

	function validaDia($dia) {
		global $sqlca;

		$turno = 0;

		$almacen = $_SESSION['almacen'];

		$sql = " SELECT validar_consolidacion('$dia',$turno,'$almacen') ";

		$sqlca->query($sql);

		$estado = $sqlca->fetchRow();

		if($estado[0] == 1){
			return 1;//Consolidado
		}else{
			return 0;//No consolidado
		}

	}

	function Insertarc_cash_transaction($createdby, $type, $d_system, $transaction, $c_cash_id, $c_cash_operation_id, $reference, $bpartner, $c_currency_id, $rate, $amount, $ware_house) {
		global $sqlca;

		try {
			$query="
				INSERT INTO c_cash_transaction(
							    	createdby,
							    	type,
							    	d_system,
							    	transaction,
							    	c_cash_id,
							    	c_cash_operation_id,
							    	reference,
							    	bpartner,
							    	c_currency_id,
							    	rate,
							    	amount,
							    	ware_house
				)VALUES(
								'$createdby',
								'$type',
								'$d_system',
								'$transaction',
								$c_cash_id,
								$c_cash_operation_id,
				    				'$reference',
								'$bpartner',
								'$c_currency_id',
								'$rate',
								$amount,
								'$ware_house'
				);";

			if ($sqlca->query($query) < 0) {
				throw new Exception("Error al insertar Cabecera de caja.");
			}

		} catch (Exception $e) {
			throw $e;
		}

	}

	function Insertarc_cash_transaction_detail($doc_type, $doc_serial_number, $doc_number, $reference, $amount, $c_currency_id) {
		global $sqlca;

		try {
			$query="
				INSERT INTO c_cash_transaction_detail(
									c_cash_transaction_id,
								    	createdby,
								    	doc_type,
								   	doc_serial_number,
								   	doc_number,
								   	reference,
								   	amount,
									c_currency_id
				)VALUES(
									(select CURRVAL('seq_c_cash_transaction_id')),
									'0',
									'$doc_type',
									'$doc_serial_number',
									'$doc_number',
									'$reference',
									'$amount',
									'$c_currency_id'
				);";

			if ($sqlca->query($query) < 0) {
				throw new Exception("Error al insertar en caja detalle.");
			}
		} catch (Exception $e) {
			throw $e;
		}

	}

	function Insertarc_cash_transaction_payment($c_cash_mpayment_id, $pay_number, $c_bank_id, $c_bank_account_id, $c_currency_id, $amount, $fecha_pay) {
		global $sqlca;

		try {
			$query = "INSERT INTO
					c_cash_transaction_payment(
									c_cash_transaction_id,
									createdby,
								    	c_cash_mpayment_id,
								    	pay_number,
								   	c_bank_id,
								    	c_bank_account_id,
								    	c_currency_id,
								    	amount,
									created
					)VALUES(
									(select CURRVAL('seq_c_cash_transaction_id')),
									'0',
									'$c_cash_mpayment_id',
									'$pay_number',
									'$c_bank_id',
									'$c_bank_account_id',
									'$c_currency_id',
									'$amount',
									'$fecha_pay'
					);
				";

			//echo $query;

			if ($sqlca->query($query) < 0) {
				throw new Exception("Error al insertar en medio de pago.");
			}
		} catch (Exception $e) {
			throw $e;
		}
	}

	function capturar_secuencia($ch_tipdocumento, $ch_seriedocumento, $ch_numdocumento, $cli_codigo) {
        global $sqlca;
        $sql = "(SELECT max(ch_identidad::INTEGER) as maximo FROM ccob_ta_detalle 
        where   ch_seriedocumento='$ch_seriedocumento' and  ch_numdocumento='$ch_numdocumento' and cli_codigo='$cli_codigo' and ch_tipmovimiento!='1' limit 1)";

        if ($sqlca->query($sql) < 0) {
            throw new Exception("Error al insertar Cuentas x cobrar.");
        }
        $a = $sqlca->fetchRow();
        $secuencia = 0;
        if (is_null($a['maximo'])) {
            $secuencia = 2;
        } else {
            $secuencia = $a['maximo'] + 1;
        }
        return $secuencia;
    }

    function Insertarc_cuentas_x_cobrar_detalle($cli_codigo, $ch_tipdocumento, $ch_seriedocumento, $ch_numdocumento, $ch_identidad, $ch_tipmovimiento, $dt_fechamovimiento, $ch_moneda, $nu_tipocambio, $nu_importemovimiento, $ch_numdocreferencia, $ch_sucursal, $ch_glosa, $dt_fecha_actualizacion
    ) {
        global $sqlca;
        try {

            $secuencia = RegistroCajasModel::capturar_secuencia($tipo_doc, $ch_seriedocumento, $ch_numdocumento, $cli_codigo);
            $query = "INSERT INTO ccob_ta_detalle
( cli_codigo,ch_tipdocumento,ch_seriedocumento,ch_numdocumento,
 ch_identidad ,ch_tipmovimiento,
 dt_fechamovimiento,ch_moneda,nu_tipocambio,nu_importemovimiento,
 ch_numdocreferencia,ch_sucursal,ch_glosa,dt_fecha_actualizacion )
 
 values (
'$cli_codigo','$ch_tipdocumento','$ch_seriedocumento','$ch_numdocumento',$secuencia ,'$ch_tipmovimiento',
            '$dt_fechamovimiento','$ch_moneda','$nu_tipocambio',$nu_importemovimiento,
            '$ch_numdocreferencia','$ch_sucursal','',now()
);";
            //echo $query;

            if ($sqlca->query($query) < 0) {
                throw new Exception("Error al insertar Cuentas x cobrar.");
            }
            $sql_update = "Update c_cash_transaction_detail set createdby='$secuencia'
            WHERE c_cash_transaction_id in(SELECT max(c_cash_transaction_id) FROM c_cash_transaction) and doc_serial_number='$ch_seriedocumento' and doc_number='$ch_numdocumento'";

            if ($sqlca->query($sql_update) < 0) {
                throw new Exception("Error al UPDATE c_cash_transaction.");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    function Insertarc_Cuenta_bancaria($c_bank_account_id, $c_bank_id, $name, $initials) {
        global $sqlca;
        try {
            $query = " INSERT INTO c_bank_account 
                    (c_bank_account_id,c_bank_id,created,createdby,c_currency_id,name,initials)
                   VALUES($c_bank_account_id,'$c_bank_id',now(),0,1,'$name','$initials');";



            if ($sqlca->query($query) < 0) {
                throw new Exception("Error al insertar Cuenta bancaria($c_bank_account_id).");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

	function Anular_Registro_Ingreso_Caja_solo_cliente($id_transacion, $tipo_accion) {
        	global $sqlca;

		try {
			$query = "
					SELECT
						c_cash_transaction_id,
						c_cash_operation_id
					FROM
						c_cash_transaction
					WHERE
						type = '0'
						AND transaction IN('$id_transacion')
						AND ware_house = '".$_SESSION['almacen']."'
					LIMIT 1;
				";

			if ($sqlca->query($query) < 0) {
				throw new Exception("Error al buscar c_cash_transaction ($id_transacion).");
			}

			$rowsql = $sqlca->fetchRow();
			$c_cash_transaction_id = $rowsql[0];
			$c_cash_operation_id = $rowsql['c_cash_operation_id']; //1 SOLO PARA CLIENTES

			if ($c_cash_operation_id == "1") {

				$query_detail="
						SELECT
							c_cash_transaction_detail_id,
							createdby,
							doc_serial_number,
							doc_number,
							reference,
					                (SELECT round(sum(amount),2) FROM c_cash_transaction_payment WHERE c_cash_transaction_id IN($c_cash_transaction_id)) as amount  
				                FROM
							c_cash_transaction_detail
						WHERE
							c_cash_transaction_id in($c_cash_transaction_id);
						";

				if ($sqlca->query($query_detail) < 0) {
					throw new Exception("Error al buscar c_cash_transaction_detail ($c_cash_transaction_id).");
				}

				$data_record = array();

				while ($rowdetalless = $sqlca->fetchRow()) {
					$data_record[] = $rowdetalless;
				}

				foreach ($data_record as $rowdetalle) {

					$id_detalle_trnsation = $rowdetalle['c_cash_transaction_detail_id'];
					$doc_serial_number = trim($rowdetalle['doc_serial_number']);
					$doc_number = trim($rowdetalle['doc_number']);
					$reference = trim($rowdetalle['reference']);
					$amount = trim($rowdetalle['amount']);
					$c_secuencia = $rowdetalle['createdby'];


					$sql_cob_detalle="
								DELETE FROM
									ccob_ta_detalle 
								WHERE
									ch_seriedocumento = '$doc_serial_number'
									AND ch_numdocumento ='$doc_number'
									AND ch_numdocreferencia = '$reference'
									AND ch_tipmovimiento != '1'
									AND ch_identidad = '$c_secuencia';
							";

					if ($sqlca->query($sql_cob_detalle) < 0) {
						throw new Exception("Error al eliminar  ccob_ta_detalle($doc_serial_number*$doc_number*$reference*$amount)");
					}

					$sql_delete="
							DELETE FROM
								c_cash_transaction_payment
							WHERE
								c_cash_transaction_id IN(SELECT c_cash_transaction_id FROM c_cash_transaction WHERE type='0' AND transaction IN('$id_transacion'))
							";

					if ($sqlca->query($sql_delete) < 0) {
						throw new Exception("Error al eliminar  c_cash_transaction_payment");
					}

					$sql_delete="
							DELETE FROM
								c_cash_transaction_detail
							WHERE
								c_cash_transaction_detail_id IN($id_detalle_trnsation)
							";

					if ($sqlca->query($sql_delete) < 0) {
						throw new Exception("Error al eliminar  c_cash_transaction_detail");
					}

					$sql_monto_total = "
								SELECT
									SUM(nu_importemovimiento) AS monto_cabe
								FROM
									ccob_ta_detalle
								WHERE
									ch_seriedocumento = '$doc_serial_number'
									AND ch_numdocumento = '$doc_number'
									AND ch_numdocreferencia = '$reference'
									AND ch_tipmovimiento != '1'
							";

					if ($sqlca->query($sql_monto_total) < 0) {
						throw new Exception("Error al totalizar monto de cabecera.");
					}

					$rowdmonto = $sqlca->fetchRow();
					$monto_Cabe = $rowdmonto['monto_cabe'];

					if ($monto_Cabe == "") {
						$monto_Cabe = 0;
					}

					$query="
						UPDATE
							ccob_ta_cabecera
						SET
							nu_importesaldo = (nu_importetotal - $monto_Cabe)
						WHERE
							ch_numdocreferencia = '$reference'
							AND ch_seriedocumento = '$doc_serial_number'
							AND ch_numdocumento = '$doc_number';
						";

					if ($sqlca->query($query) < 0) {
						throw new Exception("Error al actualizar monto.");
					}
				}

				$sql_delete="
						DELETE FROM
							c_cash_transaction
						WHERE
							type = '0'
							AND ware_house = '".$_SESSION['almacen']."'
							AND transaction IN('$id_transacion');
					";

				if ($sqlca->query($sql_delete) < 0) {
					throw new Exception("Error al eliminar  c_cash_transaction");
				}

			} else {

				//ELIMINA TICKETS DE VENTAS**************************************************

				$sql_delete =
				        "DELETE FROM c_cash_transaction_payment WHERE c_cash_transaction_id IN('$c_cash_transaction_id');";

				if ($sqlca->query($sql_delete) < 0) {
				    throw new Exception("Error al eliminar  c_cash_transaction_payment");
				} else {

					$sql_delete =
					        "DELETE FROM c_cash_transaction_detail WHERE c_cash_transaction_id IN('$c_cash_transaction_id');";
	
					if ($sqlca->query($sql_delete) < 0) {
					    throw new Exception("Error al eliminar  c_cash_transaction_detail");
					} else {
		
						$sql_delete =
						        "DELETE FROM c_cash_transaction WHERE ware_house = '".$_SESSION['almacen']."' AND type='0' AND transaction IN('$id_transacion'); ";

						if ($sqlca->query($sql_delete) < 0) {
						    throw new Exception("Error al eliminar  c_cash_transaction");
						}
					}
				}
				
				//**************************************************
			}


        } catch (Exception $e) {
            throw $e;
        }
    }

    function Actualizarpayment($id_cash_payment, $pay_number) {
        global $sqlca;
        try {//-$monto
            $query = " 
                UPDATE c_cash_transaction_payment set pay_number='$pay_number' where c_cash_transaction_payment_id =$id_cash_payment;
                ;";


            if ($sqlca->query($query) < 0) {
                throw new Exception("Error al actualizar payment.");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    function ActualizarMontosCabecera($cli_codigo, $ch_seriedocumento, $ch_numdocumento, $monto) {
        global $sqlca;
        try {//-$monto
            $query = " 
                
                update ccob_ta_cabecera set nu_importesaldo= nu_importetotal-(
                
                select case when sum(nu_importemovimiento)is null then '0' else sum(nu_importemovimiento) end from ccob_ta_detalle where  cli_codigo='$cli_codigo' 
                and ch_seriedocumento='$ch_seriedocumento' and ch_numdocumento='$ch_numdocumento' and  ch_tipmovimiento='2'
                    )

                where    cli_codigo='$cli_codigo' and ch_seriedocumento='$ch_seriedocumento' 
                and ch_numdocumento='$ch_numdocumento' ;";


            if ($sqlca->query($query) < 0) {
                throw new Exception("Error al actualizar monto.");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    function fecha_aprosys() {
        global $sqlca;

        $query = "
                  select da_fecha from pos_aprosys order by da_fecha desc limit 1;";
        if ($sqlca->query($query) <= 0) {
            return false;
        }
        $resultado = array();
        $a = $sqlca->fetchRow();
        $resultado['da_fecha'] = $a[0];


        return $resultado['da_fecha'];
    }

    function ReporteCuentasBancarias() {
        global $sqlca;

        $query = "
                  SELECT 
                        cba.c_bank_account_id,cb.initials,cba.name ,cba.initials
                        FROM c_bank_account cba inner join c_bank cb on cba.c_bank_id=cb.c_bank_id 
                 ORDER BY cba.c_bank_id;";
        if ($sqlca->query($query) <= 0) {
            return false;
        }

        $resultado = array();

        for ($i = 0; $i < $sqlca->numrows(); $i++) {
            $a = $sqlca->fetchRow();
            $resultado[$i]['c_bank_account_id'] = $a[0];
            $resultado[$i]['initials'] = $a[1];
            $resultado[$i]['name'] = $a[2];
            $resultado[$i]['initials_cl'] = $a[3];
        }

        return $resultado;
    }

    function DetalleReporteRecibo($id_recibo) {
        global $sqlca;

        $query = "
		         SELECT 
		                cct.ware_house,
		                cct.transaction,
		                cct.d_system,
		                cs.name,
		                cso.name,
		                cct.reference,
		                ic.cli_razsocial 
		        FROM
				c_cash_transaction cct  
				LEFT JOIN int_clientes ic ON (cct.bpartner = ic.cli_codigo)
				INNER JOIN c_cash cs ON (cct.c_cash_id = cs.c_cash_id)
				INNER JOIN c_cash_operation cso ON (cct.c_cash_operation_id = cso.c_cash_operation_id)
		        WHERE
				cct.transaction = '$id_recibo'
				AND cct.type = '0'
			LIMIT 1;
		";

        if ($sqlca->query($query) <= 0) {
            return false;
        }

        $resultado = array();

        for ($i = 0; $i < $sqlca->numrows(); $i++) {
            $a = $sqlca->fetchRow();
            $resultado[$i]['ware_house'] = $a[0];
            $resultado[$i]['transaction'] = $a[1];
            $resultado[$i]['d_system'] = $a[2];
            $resultado[$i]['name_caja'] = $a[3];
            $resultado[$i]['name_ope'] = $a[4];
            $resultado[$i]['reference'] = $a[5];
            $resultado[$i]['cli_razsocial'] = $a[6];
        }

        return $resultado;
    }

    function DetalleReporteRecibo_complemento_registro($id_recibo) {
        global $sqlca;

        $query = "
		        SELECT 
		                cstd.doc_type,
		                cstd.doc_serial_number ||'-'||cstd.doc_number as serie,
		                cstd.reference,
		           	mone.tab_desc_breve as moneda,
		                cstd.amount
			FROM
				c_cash_transaction cct 
			        INNER JOIN c_cash_transaction_detail cstd ON (cct.c_cash_transaction_id=cstd.c_cash_transaction_id)
				LEFT JOIN int_tabla_general mone ON (mone.tab_tabla ='04' and mone.tab_elemento != '000000' AND mone.tab_elemento = (LPAD(CAST(cct.c_currency_id AS bpchar),6,'0')))
		        WHERE
				cct.transaction = '$id_recibo'
				AND cct.type = '0';
		";

        if ($sqlca->query($query) <= 0) {
            return false;
        }

        $resultado = array();

        for ($i = 0; $i < $sqlca->numrows(); $i++) {
            $a = $sqlca->fetchRow();
            $resultado[$i]['doc_type'] = $a[0];
            $resultado[$i]['serie'] = $a[1];
            $resultado[$i]['reference'] = $a[2];
            $resultado[$i]['moneda'] = $a[3];
            $resultado[$i]['amount'] = $a[4];
        }

        return $resultado;
    }

    function DetalleReporteRecibo_medio_pago($id_recibo) {
        global $sqlca;

        $query = "
		        SELECT  
		                csm.name,
		                cstd.pay_number,
		                cstd.created,
		                b.name,
		                cba.c_bank_account_id,
		                case when (cstd.c_currency_id=1)then 'S/'
		                else '$/'
		                end as moneda,
		                cstd.amount,
		                cstd.c_cash_transaction_payment_id
			FROM
				c_cash_transaction cct 
			        INNER JOIN c_cash_transaction_payment cstd ON (cct.c_cash_transaction_id=cstd.c_cash_transaction_id)
			        INNER JOIN c_cash_mpayment csm ON (cstd.c_cash_mpayment_id=csm.c_cash_mpayment_id)
			        LEFT JOIN c_bank b ON (cstd.c_bank_id=b.c_bank_id)
			        LEFT JOIN c_bank_account cba ON (cstd.c_bank_account_id =cba.c_bank_account_id)
		        WHERE
				cct.transaction='$id_recibo'  
				AND cct.type = '0';

		";

        if ($sqlca->query($query) <= 0) {
            return false;
        }

        $resultado = array();

        for ($i = 0; $i < $sqlca->numrows(); $i++) {
            $a = $sqlca->fetchRow();
            $resultado[$i]['tipo_mp'] = $a[0];
            $resultado[$i]['pay_number'] = $a[1];
            $resultado[$i]['created'] = $a[2];
            $resultado[$i]['banco'] = $a[3];
            $resultado[$i]['cuenta_banco'] = $a[4];
            $resultado[$i]['moneda'] = $a[5];
            $resultado[$i]['importe'] = $a[6];
            $resultado[$i]['id_pay'] = $a[7];
        }

        return $resultado;
    }

}
