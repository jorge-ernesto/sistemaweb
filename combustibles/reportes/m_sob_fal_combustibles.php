<?php

class sob_fal_combustibles_Model extends Model {

    function ObtenerEstaciones() {
        global $sqlca;
    
        try {
            $sql = "SELECT
                    ch_almacen as almacen,
                    trim(ch_nombre_almacen) as nombre
                FROM
                    inv_ta_almacenes
                WHERE
                    ch_clase_almacen='1'
                ORDER BY
                    ch_almacen;";

            if($sqlca->query($sql) <= 0){
                throw new Exception("Error no se encontro turnos en la fecha indicada");
            }

            while($reg = $sqlca->fetchRow()){
                $registro[] = $reg;
            }

            return $registro;

        }catch(Exception $e){
            throw $e;
        }

    }


    function ObtenerTanques() {
        global $sqlca;
        try {
            $sql = "SELECT DISTINCT 
                            a.ch_tanque as tanque,
                            a.ch_tanque  || ' -- ' || b.ch_nombrecombustible as name
                        FROM 
                            comb_ta_tanques a,
                            comb_ta_combustibles b,
                            comb_ta_tanques c
                        WHERE 
                            a.ch_codigocombustible=b.ch_codigocombustible
                            AND a.ch_tanque=c.ch_tanque
                            AND c.ch_codigocombustible=b.ch_codigocombustible
                            AND c.ch_sucursal=trim('001') 
                        ORDER BY
                            1 ASC";



            if ($sqlca->query($sql) < 0) {
                throw new Exception("Error al obtener Tanques.");
            }
            while ($reg = $sqlca->fetchRow()) {
                $registro[] = $reg;
            }
            return $registro;
        } catch (Exception $e) {
            throw $e;
        }
    }

    function sobrantesyfaltantesReporte($almacen, $cod_tanque, $fechad, $fechaa, $unidadmedida, $detallecompras){
    
    //Aqui se saca el codigo del combustible de una vez para no estar metiendo mas cosas
   //Aqui se saca el codigo del combustible de una vez para no estar metiendo mas cosas
    $comb = pg_exec("SELECT 
                ch_codigocombustible 
            FROM 
                comb_ta_tanques 
            WHERE 
                ch_tanque='$cod_tanque'
                AND ch_sucursal=trim('$almacen')");
    $C = pg_fetch_row($comb,0);
    $cod_combustible=$C[0];
    // Lo movi aca JCP 04/09/2010
    if(trim($cod_combustible)=='11620307'&&$unidadmedida=='Galones'){
        $factor=3.785411784;
    }else{
        $factor=1;
    }
    
    //FECHA
    $qf = "SELECT 
            to_char(dt_fechamedicion,'DD-MM-YYYY') AS fecha,
            SUM(nu_medicion) AS saldo,
            to_char(dt_fechamedicion- interval '1 day','DD-MM-YYYY') AS fecha2
        FROM 
            comb_ta_mediciondiaria
        WHERE 
            ch_sucursal=trim('$almacen')
            AND ch_tanque='$cod_tanque'
            AND dt_fechamedicion >= to_date('$fechad','dd-mm-yyyy')
            AND dt_fechamedicion <= to_date('$fechaa','dd-mm-yyyy')
        GROUP BY 
            dt_fechamedicion,
            fecha2 
        ORDER BY 
            dt_fechamedicion";
    //echo $qf;
    $rs1 = pg_exec($qf);
    
    //IF POR LO QUE ENCONTO FRED
    if(pg_numrows($rs1)>0){
        for($i=0;$i<pg_numrows($rs1);$i++){
        
            $A = pg_fetch_row($rs1,$i);
        
            $rep[$i][0] = $A[0];
            $fec[$i] = $A[0];
            $fec_saldo[$i] = $A[2];
        }
    
        //SALDO
        $qe =  "SELECT 
                to_char(dt_fechamedicion,'DD-MM-YYYY') AS fecha,
                ROUND(SUM(nu_medicion)/'$factor',3) AS saldo
            FROM    
                comb_ta_mediciondiaria
            WHERE 
                ch_sucursal=trim('$almacen')
                AND dt_fechamedicion >= to_date('$fec_saldo[0]','dd-mm-yyyy')
                AND dt_fechamedicion <= to_date('$fechaa','dd-mm-yyyy')
                AND ch_tanque='$cod_tanque'
            GROUP BY 
                dt_fechamedicion 
            ORDER BY 
                dt_fechamedicion";
    
        $rs1 = pg_exec($qe) ;
    
    
        for($i=0;$i<pg_numrows($rs1);$i++){
            $A = pg_fetch_row($rs1,$i);
            $Fe[$i] = $A[0];
            $Saldo[$i] = $A[1];
        }
    
        for($i=0;$i<count($fec_saldo);$i++){
            $rep[$i][1] = "0.000";
            for($a=0;$a<count($Fe);$a++){
                if($Fe[$a]==$fec_saldo[$i]){  
                    $rep[$i][1] = $Saldo[$a];
                }
            }
        }
    
        //COMPRA
        $limit = count($fec)-1;
        $rs1 = pg_exec("SELECT 
                    to_char(mov_fecha::DATE,'DD-MM-YYYY') AS fecha,
                    ROUND(SUM(mov_cantidad)/'$factor',3) AS compra
                FROM
                    inv_movialma mov 
                WHERE 
                    tran_codigo = '21'
                    AND mov_almacen = trim('$almacen')
                    AND art_codigo  = '$cod_combustible'
                    AND to_date(to_char(mov_fecha,'DD-MM-YYYY'),'DD-MM-YYYY') >= to_date('$fec[0]','dd-mm-yyyy')
                    AND to_date(to_char(mov_fecha,'DD-MM-YYYY'),'DD-MM-YYYY') <= to_date('$fec[$limit]','dd-mm-yyyy')
                GROUP BY 
                    mov_fecha::DATE");


        for($a=0;$a<pg_numrows($rs1);$a++){
                $A = pg_fetch_row($rs1,$a);
                $F2[$a] = $A[0];
                $COMPRA[$a] = $A[1];
        }

        for($i=0;$i<count($fec);$i++){
            $rep[$i][2] = "0.000";
            for($b=0;$b<count($F2);$b++)
            if($F2[$b]==$fec[$i]){  $rep[$i][2] = $COMPRA[$b];  }
        }
    
        //MEDICION O AFERICION

        $rs1 = pg_exec("
                SELECT
                    TO_CHAR(a.dia, 'DD-MM-YYYY') AS fecha,
                    ROUND(SUM(a.cantidad)/'$factor',3) AS medicion
                FROM
                    pos_ta_afericiones a
                    LEFT JOIN comb_ta_tanques t ON(t.ch_codigocombustible = a.codigo AND t.ch_sucursal = a.es)
                WHERE
                    a.es = trim('$almacen')
                    AND a.dia BETWEEN to_date('$fechad','dd-mm-yyyy') AND to_date('$fechaa','dd-mm-yyyy')
                    AND t.ch_tanque = '$cod_tanque'
                GROUP BY
                    a.dia
                ORDER BY
                    a.dia
                ");

        for($a=0;$a<pg_numrows($rs1);$a++){
            $A = pg_fetch_row($rs1,$a);
            $F3[$a] = $A[0];
            $AFE[$a] = $A[1];
        }

        for($i=0;$i<count($fec);$i++){
            $rep[$i][3] = "0.000";
            for($b=0;$b<count($F3);$b++)
            if($F3[$b]==$fec[$i]){  $rep[$i][3] = $AFE[$b];  }
        }
        //VENTA
        //corregido la division en cero encontrado 04/17
        $rs1 = pg_exec("SELECT 
                    to_char(dt_fechaparte,'DD-MM-YYYY') AS fecha,
                    ROUND(SUM(cont.nu_ventagalon)/'$factor',3) AS venta,
                    CASE 
                    WHEN SUM(cont.nu_ventagalon) = 0 THEN 0.00
                    ELSE
                    ROUND((COALESCE(SUM(cont.nu_ventavalor),0) / COALESCE(SUM(cont.nu_ventagalon),1)) , 2) 
                    END AS nu_precio_venta
                FROM 
                    comb_ta_contometros cont
                WHERE 
                    cont.ch_sucursal=trim('$almacen')
                    AND dt_fechaparte >= to_date('$fechad','dd-mm-yyyy')
                    AND dt_fechaparte <= to_date('$fechaa','dd-mm-yyyy')
                    AND cont.ch_tanque='$cod_tanque'
                GROUP BY 
                    dt_fechaparte");

        for($a=0;$a<pg_numrows($rs1);$a++){
            $A = pg_fetch_row($rs1,$a);
            $F4[$a]             = $A[0];
            $VENTA[$a]          = $A[1];
            $PRECIO_VENTA[$a]   = $A[2];
        }

        for($i = 0; $i < count($fec); $i++){

            $rep[$i][4] = "0.000";

            for($b = 0; $b < count($F4); $b++)

            if($F4[$b]==$fec[$i]){
                $rep[$i][4] = $VENTA[$b];
                $rep[$i][12] = $PRECIO_VENTA[$b];
            }

        }

        //INGRESO
        $rs1 = pg_exec("SELECT 
                    to_char(mov_fecha::date,'DD-MM-YYYY') AS fecha,
                    ROUND(SUM(mov_cantidad)/'$factor',3) AS compra
                FROM 
                    inv_movialma
                WHERE 
                    mov_almacen=trim('$almacen')
                    AND art_codigo='$cod_combustible'
                    AND to_date(to_char(mov_fecha,'DD-MM-YYYY'),'DD-MM-YYYY') >= to_date('$fec[0]','DD-MM-YYYY')
                    AND to_date(to_char(mov_fecha,'DD-MM-YYYY'),'DD-MM-YYYY') <= to_date('$fec[$limit]','DD-MM-YYYY')
                    AND tran_codigo='27' 
                GROUP BY 
                    mov_fecha::date");



        for($a=0;$a<pg_numrows($rs1);$a++){
            $A = pg_fetch_row($rs1,$a);
            $F5[$a] = $A[0];
            $ING[$a] = $A[1];
        }

        for($i=0;$i<count($fec);$i++){
            $rep[$i][5] = "0.000";
            for($b=0;$b<count($F5);$b++)
            if($F5[$b]==$fec[$i]){  $rep[$i][5] = $ING[$b];  }
        }
    
        //SALIDA
        $rs1 = pg_exec("SELECT 
                    to_char(mov_fecha::date,'DD-MM-YYYY') AS fecha,
                    ROUND(SUM(mov_cantidad)/'$factor',3) AS compra
                FROM 
                    inv_movialma
                WHERE 
                    mov_almacen=trim('$almacen')
                    AND art_codigo='$cod_combustible'
                    AND to_date(to_char(mov_fecha,'DD-MM-YYYY'),'DD-MM-YYYY') >= to_date('$fec[0]','DD-MM-YYYY')
                    AND to_date(to_char(mov_fecha,'DD-MM-YYYY'),'DD-MM-YYYY') <= to_date('$fec[$limit]','DD-MM-YYYY')
                    AND tran_codigo='28' 
                GROUP BY 
                    mov_fecha::date");

        for($a=0;$a<pg_numrows($rs1);$a++){
            $A = pg_fetch_row($rs1,$a);
            $F6[$a] = $A[0];
            $SAL[$a] = $A[1];
        }

        for($i=0;$i<count($fec);$i++){
            $rep[$i][6] = "0.000";
            for($b=0;$b<count($F6);$b++)
            if($F6[$b]==$fec[$i]){  $rep[$i][6] = $SAL[$b];  }
        }
    
        //PARTE
        for($i=0;$i<count($fec);$i++){
            $rep[$i][7] = $rep[$i][1]+$rep[$i][2]+$rep[$i][3]-$rep[$i][4]+$rep[$i][5]-$rep[$i][6];
        }
    
        //VARILLA
        $rs1 = pg_exec("SELECT 
                    to_char(dt_fechamedicion,'DD-MM-YYYY') AS fecha,
                    ROUND(SUM(nu_medicion)/'$factor',3) AS saldo
                FROM 
                    comb_ta_mediciondiaria
                WHERE 
                    ch_sucursal=trim('$almacen')
                    AND dt_fechamedicion >= to_date('$fechad','dd-mm-yyyy')
                    AND dt_fechamedicion <= to_date('$fechaa','dd-mm-yyyy')
                    AND ch_tanque='$cod_tanque'
    
                GROUP BY 
                    dt_fechamedicion 
                ORDER BY dt_fechamedicion");
    
        for($i=0;$i<pg_numrows($rs1);$i++){
            $A = pg_fetch_row($rs1,$i);
            $FE8[$i] = $A[0];
            $VARI[$i] = $A[1];
        }
    
        for($i=0;$i<count($fec);$i++){
            $rep[$i][8] = "0.000";
            for($a=0;$a<count($FE8);$a++){
                if($FE8[$a]==$fec[$i]){  $rep[$i][8] = $VARI[$a];  }
            }
        }
    
        //DIARIA
        for($i=0;$i<count($fec);$i++){
            $rep[$i][9] = $rep[$i][8]-$rep[$i][7];
        }
    
        //ACUMULADA
        for($i=0;$i<count($fec);$i++){
        
            $rep[$i][10] = $rep[$i][9]+$rep[$i-1][10];
        
        }
    
    } //FIN DEL IF DE LO QUE ENCONTRO FRED
    
    return $rep;

}
}
