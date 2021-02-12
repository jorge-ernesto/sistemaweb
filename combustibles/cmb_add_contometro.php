<?php
//session_start();
//include("../config.php");
//include("../valida_sess.php");

extract($_REQUEST);

if($action!="cerrar_parte"){include("../menu_princ.php");}
if($cod_almacen!=""){$almacen=$cod_almacen;}
include("../functions.php");
$fecha = getdate();
$dia = $fecha['mday'];
$mes = $fecha['mon'];
if(strlen($mes)==1){$mes = "0".$mes;}
$year = $fecha['year'];
$hoy = $dia.'-'.$mes.'-'.$year;
if($fecha_parte!=""){$hoy = $fecha_parte;}
//para sacar el siguiente numero de parte osea es decir el correlativo
if($action!="grabar"){
        $rs1 = pg_exec("select max(ch_numeroparte) from comb_ta_contometros where ch_sucursal=trim('$almacen') ");
        $A = pg_fetch_row($rs1,0);
        if($A[0]=="") { $A[0]=trim($almacen)."0000001"; }else{ $A[0]=$A[0]+1;}
        $num_parte = $A[0];
        $num_parte = completarCeros($num_parte,10,"0");
        }else{
        $num_parte = completarCeros($num_parte,10,"0");
}
//para llenar el desplegable


//para grabar los campos, se registra la venta de combustibles
// INSERT COMB_TA_CONTOMETROS, UPDATE COMB_TA_SURTIDORES, INSERT INTO INV_MOVIALMA
if($action=="grabar"){
        $nu_ventavalor =  $cont_final_valor - $cont_inicial_valor;
        $nu_ventagalon = $cont_final_gal - $cont_inicial_gal;
        $rs1 = pg_exec("select ch_tanque,ch_codigocombustible from comb_ta_surtidores
        where ch_surtidor='$cod_surtidor' and ch_sucursal=trim('$almacen')");
        $A = pg_fetch_row($rs1,0);
        $cod_tanque = $A[0];
        $cod_combustible = $A[1];
        if($afericion==""){$afericion=0;}
	if($descuentos==""){$descuentos=0.0;}
        if($consumo_interno==""){$consumo_interno=0.0;}
        $q1 = "insert into comb_ta_contometros values(trim('$almacen'), '$num_parte'
        , to_date('$fecha_parte','DD-MM-YYYY'), '$cod_tanque', '$cod_combustible', '$cod_surtidor'
        ,$cont_inicial_gal,$cont_final_gal, $cont_inicial_valor, $cont_final_valor, $nu_ventavalor
        , $nu_ventagalon, $afericion, $consumo_interno,'MANUAL','now()', 'user' ,'auditorpc', 0, now(), $descuentos, util_fn_igv_porarticulo('$cod_combustible'))";
       // echo $q1;
        $q2 = "update comb_ta_surtidores set nu_contometrogalon=$cont_final_gal
        , nu_contomtrovalor=$cont_final_valor where ch_surtidor='$cod_surtidor' and ch_tanque='$cod_tanque'
        and ch_sucursal=trim('$almacen')";
        // para actualizar el inventario INV_MOVIALMA
        $q3 = "select inv_tipotransa.tran_origen , inv_tipotransa.tran_destino, inv_tipotransa.tran_naturaleza
        from inv_tipotransa where tran_codigo='25'";
                        $Q3 = pg_fetch_row(pg_exec($q3),0);
                        $trans_origen = $Q3[0];
                        $trans_destino = $Q3[1];
                        $trans_naturaleza = $Q3[2];
        $q4 = "select stk_costo$mes,stk_stock$mes from inv_saldoalma where stk_almacen=trim('$almacen')
                   and stk_periodo='$year' and art_codigo='$cod_combustible' ";
                        //ECHO// echo $q4;
                        $rsq4 = pg_exec($q4);
                        if(pg_numrows($rsq4)<1){ //en caso de que no haya saldo para ese mes se pone a cero
                        //pg_exec("insert into inv_saldoalma(stk_costo$mes,stk_stock$mes,stk_almacen,stk_periodo,art_codigo)
                        //                                                        values (0.0       ,0.0        ,trim('$almacen'),'$year','$cod_combustible') "
                        //                );
                        }
                        $rsq4 = pg_exec($q4);
                        $Q4 = pg_fetch_row($rsq4,0);
                        $stk_costo03 = $Q4[0];
                        $stk_stock03 = $Q4[1];

if($stk_costo03 == '') $stk_costo03= 0;
        $q5 = "insert into inv_movialma (tran_codigo, mov_numero, mov_fecha, mov_almacen, art_codigo,
                         mov_almaorigen, mov_almadestino, mov_cantidad,
                         mov_costounitario, mov_costopromedio,
                        mov_costototal, mov_naturaleza )
                        values ('25', '$num_parte', to_date('$fecha_parte','DD-MM-YYYY'), trim('$almacen'), '$cod_combustible',
                         trim('$almacen'), $trans_destino, $nu_ventagalon,
                        $stk_costo03, $stk_costo03,
                         $nu_ventagalon*$stk_costo03, $trans_naturaleza )";
//echo $q5;
//        $q6 = "update inv_saldoalma set stk_stock$mes=$stk_stock$mes-$nu_ventagalon
//                        where stk_almacen=trim('$almacen') and stk_periodo='$year'
//                        and art_codigo='$cod_combustible' ";
        pg_exec($q1);
        pg_exec($q2);
        pg_exec($q5);
//        pg_exec($q6);

        //EN EL CASO DE QUE SE DE UNA AFERICION
                if($afericion!=0){
                        $q3 = "select inv_tipotransa.tran_origen , inv_tipotransa.tran_destino, inv_tipotransa.tran_naturaleza
                               from inv_tipotransa where tran_codigo='23'";
                        $Q3 = pg_fetch_row(pg_exec($q3),0);
                        $trans_origen = $Q3[0];
                        $trans_destino = trim($almacen);
                        $trans_naturaleza = $Q3[2];
                $Q4 = pg_fetch_row(pg_exec($q4),0); //vuelvo a sacar el stock porque acaba de ser actualizado arriba
                        $stk_costo03 = $Q4[0];
                        $stk_stock03 = $Q4[1];
                $q7 = "insert into inv_movialma (tran_codigo, mov_numero, mov_fecha, mov_almacen, art_codigo,
                         mov_almaorigen, mov_almadestino, mov_cantidad,
                         mov_costounitario, mov_costopromedio,
                        mov_costototal, mov_naturaleza )
                        values ('23', '$num_parte', to_date('$fecha_parte','DD-MM-YYYY'), trim('$almacen'), '$cod_combustible',
                         $trans_origen, $trans_destino, $afericion*5,
                        $stk_costo03, $stk_costo03,
                         $afericion*5*$stk_costo03, $trans_naturaleza)";
  //               $q8 = "update inv_saldoalma set stk_stock$mes=$stk_stock$mes+($afericion*5) where stk_almacen=trim('$almacen')
  //               and stk_periodo='$year' and art_codigo='$cod_combustible' ";

                pg_exec($q7);
                // pg_exec($q8);

                } //fin if afericion

        if($consumo_interno!=0.0){
                //--EN CASO DE QUE SE DE UN CONSUMO INTERNO
                // esto es por la transacciones 26
                        $q3 = "select inv_tipotransa.tran_origen , inv_tipotransa.tran_destino, inv_tipotransa.tran_naturaleza
                        from inv_tipotransa where tran_codigo='26'";
                        $Q3 = pg_fetch_row(pg_exec($q3),0);
                        $trans_origen = $Q3[0];
                        $trans_destino = trim($almacen);
                        $trans_naturaleza = $Q3[2];
                $Q40 = pg_fetch_row(pg_exec($q4),0); //vuelvo a sacar el stock porque acaba de ser actualizado arriba
                        $stk_costo03 = $Q40[0];
                        $stk_stock03 = $Q40[1];
                $q41 = "insert into inv_movialma (tran_codigo, mov_numero, mov_fecha, mov_almacen, art_codigo,
                         mov_almaorigen, mov_almadestino, mov_cantidad,
                         mov_costounitario, mov_costopromedio,
                        mov_costototal, mov_naturaleza )
                        values ('26', '$num_parte', to_date('$fecha_parte','DD-MM-YYYY'), trim('$almacen'), '$cod_combustible',
                         $trans_origen, $trans_destino, $consumo_interno,
                        $stk_costo03, $stk_costo03,
                         $consumo_interno*$stk_costo03, $trans_naturaleza )";
          //      $q42 = "update inv_saldoalma set stk_stock$mes=$stk_stock$mes-$consumo_interno where stk_almacen=trim('$almacen') and stk_periodo='2004'
          //              and art_codigo='$cod_combustible' ";

                pg_exec($q41);
                // pg_exec($q42);

                // esto es por la transacciones 24
                $q3 = "select inv_tipotransa.tran_origen , inv_tipotransa.tran_destino, inv_tipotransa.tran_naturaleza
                from inv_tipotransa where tran_codigo='24'";
                        $Q3 = pg_fetch_row(pg_exec($q3),0);
                        $trans_origen =  trim($almacen);
                        $trans_destino = $Q3[1];
                        $trans_naturaleza = $Q3[2];

                $Q50 = pg_fetch_row(pg_exec($q4),0); //vuelvo a sacar el stock porque acaba de ser actualizado arriba
                        $stk_costo03 = $Q40[0];
                        $stk_stock03 = $Q40[1];
                $q51 = "insert into inv_movialma (tran_codigo, mov_numero, mov_fecha, mov_almacen, art_codigo,
                         mov_almaorigen, mov_almadestino, mov_cantidad,
                         mov_costounitario, mov_costopromedio,
                        mov_costototal, mov_naturaleza )
                        values ('24', '$num_parte', to_date('$fecha_parte','DD-MM-YYYY'), trim('$almacen'), '$cod_combustible',
                         $trans_origen, $trans_destino, $consumo_interno,
                        $stk_costo03, $stk_costo03,
                         $consumo_interno*$stk_costo03,$trans_naturaleza )";
                //$q52 = "update inv_saldoalma set stk_stock$mes=$stk_stock$mes-$consumo_interno
                //        where stk_almacen=trim('$almacen') and stk_periodo='2004'
                //        and art_codigo='$cod_combustible' ";

                pg_exec($q51);
                // pg_exec($q52);

                }//--fin del if del consumo!=0.0
        }
//--FIN DE para grabar los campos, se registra la venta de combustibles
//--FIN DE INSERT COMB_TA_CONTOMETROS, UPDATE COMB_TA_SURTIDORES , INSERT INTO INV_MOVIALMA
//para los desplegables con javascript
if($cod_almacen!=""){
$almacen = $cod_almacen;
$where_comb = "where ch_sucursal=trim('$almacen')";
$and_suc = " and s.ch_sucursal=trim('$almacen') ";
}else{
$where_comb = "where ch_sucursal=trim('$almacen')";
$and_suc = " and s.ch_sucursal=trim('$almacen') ";
}
$rs1 = pg_exec("select ch_surtidor,nu_contometrogalon from comb_ta_surtidores $where_comb"); //ultima lectura por galon
$rs2 = pg_exec("select s.ch_surtidor,
'Surtidor ' ||s.ch_surtidor || '-Lado-' || s.ch_numerolado || '-Producto-' || c.ch_nombrecombustible
from comb_ta_surtidores s,comb_ta_combustibles c where   c.ch_codigocombustible=s.ch_codigocombustible
$and_suc order by s.ch_surtidor");
$rs7 = pg_exec("select ch_surtidor,nu_contomtrovalor from comb_ta_surtidores $where_comb"); //ultima lectura por valor
$rs8 = pg_exec("select s.ch_surtidor, c.nu_preciocombustible from comb_ta_combustibles c
, comb_ta_surtidores s
where c.ch_codigocombustible=s.ch_codigocombustible $and_suc "); //para los precios        de los productos

if($action=="cerrar_parte"){
        pg_close();
        header("Location: cmb_contometro.php");
        exit;
}

//para llenar los items de este numero de parte si es que los hay ////////////////////////////////////////////////////////
$rs31 = pg_exec("
			SELECT
				ch_surtidor,
				nu_contometroinicialgalon,
				nu_contometrofinalgalon,
				nu_contometroinicialvalor,
				nu_contometrofinalvalor,
				nu_afericionveces_x_5,
				nu_descuentos
			FROM
				comb_ta_contometros
			WHERE
				ch_numeroparte = '$num_parte'
				AND ch_sucursal = trim('$cod_almacen')
		");


//para el combo de sucursales
$rsx1 = pg_exec("select ch_almacen ,ch_nombre_almacen from inv_ta_almacenes where ch_clase_almacen='1'
  order by ch_nombre_almacen");
if($cod_almacen!=""){
$rsx2 = pg_exec("select ch_almacen ,ch_nombre_almacen from inv_ta_almacenes where ch_clase_almacen='1'
and  trim(ch_almacen)=trim('$cod_almacen')  order by ch_nombre_almacen");
$Rx = pg_fetch_row($rsx2,0);
$sucursal_dis = $Rx[1];
$sucursal_val = $Rx[0];
$almacen=$cod_almacen;
}

if($cod_tanque==""){$cod_tanque="01";}

$suc = pg_exec("select ch_almacen ,ch_nombre_almacen from inv_ta_almacenes where ch_clase_almacen='1'
and  trim(ch_almacen)=trim('$almacen')  order by ch_nombre_almacen");
/*echo "select tab_elemento as cod,tab_descripcion from int_tabla_general
where tab_tabla='ALMA' and tab_car_02='1'
and  tab_elemento='$almacen' order by cod";
*/

$S = pg_fetch_row($suc,0);
$SUC = "<option value='$S[0]'>$S[0] -- $S[1]</option>";
//echo $SUC;

pg_close();

include("cmb_diseno_add_contometro.php");
