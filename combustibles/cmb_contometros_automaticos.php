<?php
session_start();
include("../config.php");
$mes = date("m");
$year = $fecha['year'];

?>
<?php 	function agregarRegistros($cod_almacen,$cont_final_valor,$cont_inicial_valor,$cont_final_gal
	,$cont_inicial_gal,$afericion,$consumo_interno,$num_parte,$cod_surtidor){
	$almacen = $cod_almacen;
	$fecha = getdate();
	$mes = date("m");
	$year = $fecha['year'];
	
	$nu_ventavalor =  $cont_final_valor - $cont_inicial_valor;
	$nu_ventagalon = $cont_final_gal - $cont_inicial_gal;
	$rs1 = pg_exec("select ch_tanque,ch_codigocombustible from comb_ta_surtidores 
	where ch_surtidor='$cod_surtidor' and ch_sucursal=trim('$cod_almacen')");
	
	$A = pg_fetch_row($rs1,0);
	$cod_tanque = $A[0]; 
	$cod_combustible = $A[1];
	if($afericion==""){$afericion=0;}
	if($consumo_interno==""){$consumo_interno=0.0;}
	$q1 = "insert into comb_ta_contometros values(trim('$almacen'), '$num_parte'
	, to_date('$fecha_parte','DD-MM-YYYY'), '$cod_tanque', '$cod_combustible', '$cod_surtidor'
	,$cont_inicial_gal,$cont_final_gal, $cont_inicial_valor, $cont_final_valor, $nu_ventavalor
	, $nu_ventagalon, $afericion, $consumo_interno,'respo','now()', 'user' ,'auditorpc', 0, now(), $descuentos, util_fn_igv_porarticulo('$cod_combustible'))";
	//echo $q1;
	$q2 = "update comb_ta_surtidores set nu_contometrogalon=$cont_final_gal 
	, nu_contomtrovalor=$cont_final_valor where ch_surtidor='$cod_surtidor' and ch_tanque='$cod_tanque' 
	and ch_sucursal=trim('$almacen')";
	// para actualizar el inventario INV_MOVIALMA 
	$q3 = "select inv_tipotransa.tran_origen , inv_tipotransa.tran_destino  
	from inv_tipotransa where tran_codigo='25'";
			$Q3 = pg_fetch_row(pg_exec($q3),0);
			$trans_origen = $Q3[0];
			$trans_destino = $Q3[1];
	$q4 = "select stk_costo$mes,stk_stock$mes from inv_saldoalma where stk_almacen=trim('$almacen') 
		   and stk_periodo='$year' and art_codigo='$cod_combustible' ";
			//ECHO// echo $q4;
			$rsq4 = pg_exec($q4);
			if(pg_numrows($rsq4)<1){ //en caso de que no haya saldo para ese mes se pone a cero
			pg_exec("insert into inv_saldoalma(stk_costo$mes,stk_stock$mes,stk_almacen,stk_periodo,art_codigo)
										values (0.0       ,0.0        ,trim('$almacen'),'$year','$cod_combustible') "
					);
			}
			
			$rsq4 = pg_exec($q4);
			$Q4 = pg_fetch_row($rsq4,0);
			$stk_costo03 = $Q4[0];
			$stk_stock03 = $Q4[1];
			
			
	$q5 = "insert into inv_movialma (tran_codigo, mov_numero, mov_fecha, mov_almacen, art_codigo,
	 		mov_almaorigen, mov_almadestino, mov_cantidad, 
	 		mov_costounitario, mov_costopromedio, 
			mov_costototal ) 
			values ('25', '$num_parte', 'now()', trim('$almacen'), '$cod_combustible', 
	 		trim($almacen), $trans_destino, $nu_ventagalon,
			$stk_costo03, $stk_costo03,
	 		$nu_ventagalon*$stk_costo03)";
	$q6 = "update inv_saldoalma set stk_stock$mes=$stk_stock$mes-$nu_ventagalon 
			where stk_almacen=trim('$almacen') and stk_periodo='$year' 
			and art_codigo='$cod_combustible' ";
	pg_exec($q1); 
	pg_exec($q2);
	pg_exec($q5);
	pg_exec($q6);
	
	//EN EL CASO DE QUE SE DE UNA AFERICION
		if($afericion!=0){
				$q3 = "select inv_tipotransa.tran_origen , inv_tipotransa.tran_destino  
				from inv_tipotransa where tran_codigo='23'";
			$Q3 = pg_fetch_row(pg_exec($q3),0);
			$trans_origen = $Q3[0];
			$trans_destino = trim($almacen);
		$Q4 = pg_fetch_row(pg_exec($q4),0); //vuelvo a sacar el stock porque acaba de ser actualizado arriba
			$stk_costo03 = $Q4[0];
			$stk_stock03 = $Q4[1];
		$q7 = "insert into inv_movialma (tran_codigo, mov_numero, mov_fecha, mov_almacen, art_codigo,
	 		mov_almaorigen, mov_almadestino, mov_cantidad, 
	 		mov_costounitario, mov_costopromedio, 
			mov_costototal ) 
			values ('23', '$num_parte', 'now()', trim('$almacen'), '$cod_combustible', 
	 		$trans_origen, $trans_destino, $afericion*5,
			$stk_costo03, $stk_costo03,
	 		$afericion*5*$stk_costo03)";
		$q8 = "update inv_saldoalma set stk_stock$mes=$stk_stock$mes+($afericion*5) where stk_almacen=trim('$almacen')
		 and stk_periodo='$year' and art_codigo='$cod_combustible' ";
	
		pg_exec($q7);
		pg_exec($q8);
		
		} //fin if afericion
	
	if($consumo_interno!=0.0){
		//--EN CASO DE QUE SE DE UN CONSUMO INTERNO
		// esto es por la transacciones 26
			$q3 = "select inv_tipotransa.tran_origen , inv_tipotransa.tran_destino  
			from inv_tipotransa where tran_codigo='26'";
			$Q3 = pg_fetch_row(pg_exec($q3),0);
			$trans_origen = $Q3[0];
			$trans_destino = trim($almacen);
		$Q40 = pg_fetch_row(pg_exec($q4),0); //vuelvo a sacar el stock porque acaba de ser actualizado arriba
			$stk_costo03 = $Q40[0];
			$stk_stock03 = $Q40[1];
		$q41 = "insert into inv_movialma (tran_codigo, mov_numero, mov_fecha, mov_almacen, art_codigo,
	 		mov_almaorigen, mov_almadestino, mov_cantidad, 
	 		mov_costounitario, mov_costopromedio, 
			mov_costototal ) 
			values ('26', '$num_parte', 'now()', trim('$almacen'), '$cod_combustible', 
	 		$trans_origen, $trans_destino, $consumo_interno,
			$stk_costo03, $stk_costo03,
	 		$consumo_interno*$stk_costo03)";
		$q42 = "update inv_saldoalma set stk_stock$mes=$stk_stock$mes-$consumo_interno where stk_almacen=trim('$almacen') and stk_periodo='2004' 
			and art_codigo='$cod_combustible' ";
			
		pg_exec($q41);
		pg_exec($q42);
		
		// esto es por la transacciones 24
		$q3 = "select inv_tipotransa.tran_origen , inv_tipotransa.tran_destino  
		from inv_tipotransa where tran_codigo='24'";
			$Q3 = pg_fetch_row(pg_exec($q3),0);
			$trans_origen =  trim($almacen);
			$trans_destino = $Q3[1];
		
		$Q50 = pg_fetch_row(pg_exec($q4),0); //vuelvo a sacar el stock porque acaba de ser actualizado arriba
			$stk_costo03 = $Q40[0];
			$stk_stock03 = $Q40[1];
		$q51 = "insert into inv_movialma (tran_codigo, mov_numero, mov_fecha, mov_almacen, art_codigo,
	 		mov_almaorigen, mov_almadestino, mov_cantidad, 
	 		mov_costounitario, mov_costopromedio, 
			mov_costototal ) 
			values ('24', '$num_parte', 'now()', trim('$almacen'), '$cod_combustible', 
	 		$trans_origen, $trans_destino, $consumo_interno,
			$stk_costo03, $stk_costo03,
	 		$consumo_interno*$stk_costo03)";
		$q52 = "update inv_saldoalma set stk_stock$mes=$stk_stock$mes-$consumo_interno 
			where stk_almacen=trim('$almacen') and stk_periodo='2004' 
			and art_codigo='$cod_combustible' ";
	
		pg_exec($q51);
		pg_exec($q52);
		
		}//--fin del if del consumo!=0.0
	}
	
	
	
?>
<?php

	switch($accion){

	 case "Importar":
	 	pg_exec(" select combex_fn_reporte_contometros(to_date('$dia','dd/mm/yyyy'),$turno) ");
		/*$q1 = " select distinct man.lado,man.manguera, 
man.ch_surtidor as surtidor,vis.volumen_final
		,vis.valor_final, sur.nu_contometrogalon as inicial_volumen
		,sur.nu_contomtrovalor as inicial_valor , c.nu_preciocombustible ,man.lado,sur.ch_codigocombustible 
		 from comb_ta_surtidores sur,vista_reporte_combex_contometros vis,pos_ta_mangueras man 
		 ,comb_ta_combustibles c 
		 where man.lado=vis.lado and man.manguera=vis.manguera and man.ch_surtidor=sur.ch_surtidor
		and c.ch_codigocombustible=sur.ch_codigocombustible 
		and sur.ch_sucursal='$cod_almacen' ";	
		*/
		$q1 = "select distinct vis.lado,sur.nu_manguera, 
		sur.ch_surtidor as surtidor
		,vis.volumen_final ,vis.valor_final, sur.nu_contometrogalon as 
		inicial_volumen 
		,sur.nu_contomtrovalor as inicial_valor , c.nu_preciocombustible 
		,sur.ch_numerolado
		,sur.ch_codigocombustible 
		from 
		comb_ta_surtidores sur
		,vista_reporte_combex_contometros vis
		,comb_ta_combustibles c 
		where 
		to_number(sur.ch_numerolado,'99G999D9S')=vis.lado 
		and to_number(sur.nu_manguera::text,'99G999D9S')=vis.manguera  
		and c.ch_codigocombustible=sur.ch_codigocombustible and 
		sur.ch_sucursal='$cod_almacen' order by vis.lado";
		
		$q2 = "select distinct man.lado,man.manguera, man.ch_surtidor as surtidor,vis.volumen_final 
		,vis.valor_final, sur.nu_contometrogalon as inicial_volumen 
		,sur.nu_contomtrovalor as inicial_valor , c.nu_preciocombustible 
		from comb_ta_surtidores sur,vista_reporte_combex_contometros vis,pos_ta_mangueras man 
		,comb_ta_combustibles c , pos_ta_afericiones a 
		where man.lado=vis.lado and man.manguera=vis.manguera 
		and man.ch_surtidor=sur.ch_surtidor and c.ch_codigocombustible=sur.ch_codigocombustible 
		and  to_number(a.pump,'99G999D9S')=to_number(man.lado,'99G999D9S') 
		and trim(a.codigo)=trim(c.ch_codigocombustible) and a.dia=to_date('$dia','dd/mm/yyyy') 
		and a.turno='$turno' ";
		//echo $q1;
		$rs1 = pg_exec($q1);
	 break;
	 
	 case "Ingresar":
	 	for($i=0;$i<count($surtidor);$i++){
			$cod_surtidor = $surtidor[$i];
			$cod_almacen = $cod_almacen; //vienen de la primera venmtana
			$cont_final_valor = $final_valor[$i];
			$cont_inicial_valor = $inicial_valor[$i];
			$cont_final_gal = $final_galones[$i];
			$cont_inicial_gal = $inicial_galones[$i];
			$afericion = $afericiones[$i];
			$consumo_interno = $consumos_internos[$i];
			$num_parte = $num_parte; //vienen de la primera ventana
			
			agregarRegistros($cod_almacen,$cont_final_valor,$cont_inicial_valor,$cont_final_gal
			,$cont_inicial_gal,$afericion,$consumo_interno,$num_parte,$cod_surtidor);
			
			
			?>
			<script>
				opener.location.href='/sistemaweb/combustibles/cmb_contometro.php';
				window.close();
			</script>
			<?php
		}
	  break;
	 }
	 
?>




<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<script language="JavaScript">
	function mandarDatos(form, accion){
		var pasa = true;
		var msg = "";
		if(accion=="Ingresar"){
				
			
		}
		
		if(accion=="Importar"){
			if(form.dia.value==""){ pasa = false; msg="Debes definir el dia";}	
			if(form.turno.value==""){ pasa = false; msg="Debes definir el turno";}	
			
		}
		
		if(pasa){
			form.accion.value=accion;
			form.submit();
		}else{
			alert(msg);
		}
		
	}
</script>

<title>Importar Contometros</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../sistemaweb.css" rel="stylesheet" type="text/css">
</head>

<body>
<form name="form1" method="post" >
  <table width="767" border="0">
    <tr> 
      <td colspan="2">Fecha: 
        <input type="text" name="dia" size="11"></td>
      <td>Turno: <input type="text" name="turno" size="2"></td>
      <td><input type="button" name="Submit2" value="Importar" onClick="javascript:mandarDatos(form1,'Importar')"></td>
      <td colspan="4"><input type="hidden" name="num_parte" value="<?php echo $num_parte;?>">  
        <input type="hidden" name="cod_almacen" value="<?php echo $cod_almacen;?>"></td>
    </tr>
    <tr> 
      <td colspan="4">&nbsp;</td>
      <td colspan="4">&nbsp;</td>
    </tr>
    <tr> 
      <td><div align="center"><font size="-4"><strong>SURTIDOR</strong></font></div></td>
      <td><font size="-4"><strong>CONTOMETRO<br>
        INICIAL </strong></font></td>
      <td><div align="center"><font size="-4"><strong>GALONES<br>
          FINAL </strong></font></div></td>
      <td><div align="center"><font size="-4"><strong>CONTOMETRO<br>
          INICIAL </strong></font></div></td>
      <td width="65"><div align="center"><font size="-4"><strong>IMPORTE<br>
          FINAL </strong></font></div></td>
      <td width="66"><div align="center"><font size="-4"><strong>PRECIO</strong></font></div></td>
      <td width="75"><div align="center"><font size="-4"><strong>AFERICIONES</strong></font></div></td>
      <td width="252"><div align="center"><font size="-4"></font></div></td>
    </tr>
    <?php if($accion=="Importar"){ 
		for($i=0;$i<pg_numrows($rs1);$i++){ 
		$A = pg_fetch_array($rs1,$i);
		
		$cod_articulo = $A[9];
		$lado = $A[8];
		$q2 = "select sum(cantidad) as afericion from pos_ta_afericiones where turno='$turno' and 
		dia=to_date('$dia','dd/mm/yyyy') ";
		//echo "CARAjo".$q2;
		$rs2 = pg_exec($q2);
		$B = pg_fetch_array($rs2,0);
		$afericion = $B[0];
		if($afericion==""){$afericion=0;}
	?>
    <tr> 
      <td width="49"><div align="center"> 
          <input name="surtidor[]" type="text" id="surtidor" size="3" readonly="true" value="<?php echo $A[2];?>">
        </div></td>
      <td width="71"><div align="center">
          <input name="inicial_galones[]" type="text" id="contometro_inicial_gal" size="10"  readonly="true" value="<?php echo $A[5];?>">
        </div></td>
      <td width="79"><div align="center">
          <input name="final_galones[]" type="text" id="final_galones[]2" size="10"  readonly="true" value="<?php echo $A[3];?>">
        </div></td>
      <td width="76"><div align="center">
          <input name="inicial_valor[]" type="text" id="inicial_valor[]" size="10"  readonly="true" value="<?php echo $A[6];?>">
        </div></td>
      <td><div align="center">
          <input name="final_valor[]" type="text" id="final_valor[]2" size="10"  readonly="true" value="<?php echo $A[4];?>">
        </div></td>
      <td><div align="center">
          <input name="precios[]" type="text" id="precios3" size="10"  readonly="true" value="<?php echo $A[7];?>">
        </div></td>
      <td><div align="center"> 
          <input name="afericiones[]" type="text" id="afericiones[]2" size="10"  value="<?php echo $afericion;?>">
        </div></td>
      <td><div align="center"> 
          <input name="consumos_internos[]" type="hidden" id="consumo_interno" size="10"  readonly="true" value="0">
        </div></td>
    </tr>
    <?php } 
	} ?>
  </table>
  <div align="center"><br>
    <input type="button" name="Submit" value="Ingresar" onClick="javascript:mandarDatos(form1,'Ingresar')">
    <input type="hidden" name="accion">
  </div>
</form>
</body>
</html>
<?php pg_close();?>
