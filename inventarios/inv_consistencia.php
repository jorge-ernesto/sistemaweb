	`		<?php
if($boton=="Adicionar") {
?>
<script languaje="JavaScript">
	location.href='addmov.php?fm=<?php echo $fm;?>';
</script>
<?php
}elseif($boton=="Modificar") {
   if(strlen($nromov)>0){
?>
<script languaje="JavaScript">
	location.href='updmov.php?fm=<?php echo $fm;?>&nromov=<?php echo $nform;?>';
</script>
<?php }else{  ?>
<script languaje="JavaScript">
	location.href='updmov.php?fm=<?php echo $fm;?>&nromov=<?php echo $nform;?>';
</script>
<?php }
}
session_start();
include("../config.php");
include("inc_top.php");
include("../functions.php");
include("/sistemaweb/utils/funcion-texto.php");

if($imprimir=="imprimir"){ exec("smbclient //server14nw/epson -c 'print /tmp/consistencia.txt' -P -N -I 192.168.1.1 ");  }

if($diad==""){
    $diad = date("d");
    $mesd = date("m");
    $anod = date("Y");
    
    $diaa = date("d");
    $mesa = date("m");
    $anoa = date("Y");
    
}

$titulo = "<tr>REPORTE DE CONSISTENCIA DEL DIA desde $diad/$mesd/$anod a $diaa/$mesa/$anoa<td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>";
$tr = "<tr><td>\n---------------------------</td><td>----------------</td><td>-----------</td><td>----------</td><td>----------</td><td>--------------</td><td>--------------</td><td>-------</td><td>-------\n</td></tr>";

//$tr = "<tr><td><hr noshade></td><td><hr noshade></td><td><hr noshade></td><td><hr noshade></td></tr>";

$a_rs = pg_exec("select trim(par_valor)  from int_parametros where par_nombre='codes'"); 
$X = pg_fetch_array($a_rs,0);
$alma = $X[0];

$q1 = "select  	'xx'
		,i.tran_codigo as Codigo1,	i.mov_numero as Codigo2 
		,to_char(i.mov_fecha,'dd/mm/yyyy') as fecha 	
		,i.com_serie_compra as serie
		,i.mov_entidad as prove	,	i.art_codigo as articulo 
		,substring(trim(a.art_descripcion) for 40 from 1) as des	,	i.mov_cantidad as cantidad 
		,lista.pre_precio_act1	, 	i.mov_costototal as total	
		,i.mov_almaorigen as ori,	i.mov_almadestino as des
		,i.mov_tipdocuref as docuref,	i.mov_docurefe as docurefe
		,t.tran_descripcion as descripcion , i.com_num_compra as orden_compra 
		,almacenes.ch_nombre_breve_almacen as ori_desc 
		from inv_movialma i , int_articulos a , inv_tipotransa t 
		,fac_lista_precios lista , inv_ta_almacenes almacenes 
		where i.art_codigo = a.art_codigo 
		and i.tran_codigo = t.tran_codigo
		and i.tran_codigo < 23  
		and lista.art_codigo = a.art_codigo
		and lista.pre_lista_precio = substring(trim('$alma') for 2 from 2) 	 
		and i.mov_fecha <= '$anoa-$mesa-$diaa 23:59:59' 
		and i.mov_fecha >= '$anod-$mesd-$diad 00:00:00'
		and almacenes.ch_almacen = i.mov_almaorigen 
		order by Codigo1 
		" ;		
		
$rs1 = pg_exec($q1);

$cab2 = "<tr><td><b>(*)Tipo de Movimiento : 1<br><b>- {Descripcion}</b></b></td><td></td><td></td><td></td></tr>";

$tit_det1 = "<tr><td>{form} {num_mov} {fecha} </td><td>{almacen}</td><td>{num_ord}</td><td>{cod_prov}</td><td></td></tr>";
$det1 = "<tr><td>1x 2x 3x</td><td>4x</td><td>{orden_compra}</td><td>5x</td><td></td></tr>";

$tit_det2 = "<tr><td>{Producto}</td><td>{Descripcion}</td><td>{Cant.}</td><td>{Cost.Uni}</td><td>{Total}</td><td>{Origen}</td><td>{Destino}</td><td>{Tip. Doc}</td><td>{Num. Doc}</td></tr>";
$det2 = "<tr><td>6x</td><td>7x</td><td>8x</td><td>9x</td><td>10x</td><td>11x {ori_desc}</td><td>12x  {des_desc}</td><td>13x</td><td>14x</td></tr>";

$tot = "<tr><td>6x</td><td>7x</td><td>8x</td><td>9x</td><td>10x</td><td>11x</td><td>12x</td><td>13x</td><td>14x</td></tr>";
?>
MOVIMIENTOS DE CONSISTENCIA DE ALMACEN
<hr noshade>
<form action="inv_consistencia.php" method="post" name="form1">
<br><p><a href="#"  onclick="javascript:form1.imprimir.value='imprimir',form1.submit();">Imprimir</a></p>
  <table border="0" colspan=0>
    <tr>
      <th colspan="5">CONSULTAR POR RANGO DE FECHAS </th>
    </tr>
    <tr>
      <th>DESDE :</th>
      <th><input type="text" name="diad" size="4" maxlength="2" onKeyPress="return esIntspto(event)" value='<?php echo $diad;?>'>
        / <input type="text" name="mesd" size="4" maxlength="2" onKeyPress="return esIntspto(event)" value='<?php echo $mesd;?>'>
        / <input type="text" name="anod" size="6" maxlength="4" onKeyPress="return esIntspto(event)" value='<?php echo $anod;?>'></th>
      <th>HASTA:</th>
      <th><input type="text" name="diaa" size="4" maxlength="2" onKeyPress="return esIntspto(event)" value='<?php echo $diaa;?>'>
        /
        <input type="text" name="mesa" size="4" maxlength="2" onKeyPress="return esIntspto(event)" value='<?php echo $mesa;?>'>
        /
        <input type="text" name="anoa" size="6" maxlength="4" onKeyPress="return esIntspto(event)" value='<?php echo $anoa;?>'></th>
		<th><input type="submit" name="boton" value="Consultar"></th>
    </tr>
  </table>
  <br>
  <table border="1">
  <tr><th colspan="2">RESUMEN DE REGISTROS POR TIPO DE MOVIMIENTO</th></tr>


  </table>
    <input type="hidden" name="fm" value='<?php echo $fm;?>'><br>


  <table border="1" cellpadding="0" cellspacing="0">
    <tr>
      <th>FORMULARIO</th>
      <th>FECHA</th>
      <th>No O/C</th>
      <th>PROVEEDOR</th>
      <th>&nbsp;</th>
      <th>&nbsp;</th>
      <th>&nbsp;</th>
      <th>&nbsp;</th>
      <th>&nbsp;</th>
      <th>&nbsp;</th>
    </tr>
    <tr>

      <th>CODIGO</th>
      <th colspan="3">DESCRIPCION</th>
      <th>CANTIDAD</th>
      <th>COSTO</th>
      <th>TOTAL</th>
      <th>ORIGEN</th>
      <th>DESTINO</th>
      <th>DOC. REF.</th>
    </tr>

  </table>
  
  <br>
<!--
  <table border="1"  cellpadding="0" cellspacing="0">
    <tr> 
      <td colspan="5">(*)Tipo de Movimiento : <?php echo $tip_mov;?> - 
        {Descripcion}</td>
      <td width="125"></td>
      <td width="98"></td>
      <td width="96"></td>
    </tr>
    <tr> 
      <td width="47">1</td>
      <td width="47">2</td>
      <td width="47">3</td>
      <td width="47">4</td>
      <td width="87">5</td>
    </tr>
	<tr>
		<td>6</td>
		<td>7</td>
		<td>8</td>
		<td>9</td>
		<td>10</td>
		<td>11</td>
		<td>12</td>
		<td>13</td>
		<td width="146">14</td>
		
	</tr>
  </table>
  -->
  <?php
  
  $texto = "";
  $m = "ccxcc";
  $m = str_replace("x","n",$m);
  //echo $m;
  
  print "<table>";
  $texto = "<table>";
  print $titulo;
  $texto = $texto.$titulo;
  $tip_mov_ant = "xxx";
  $num_mov_ant = "xxx";
  $total_mov = 0;
  $tip_mov_cambiado = false;
  
  
  for($i=0;$i<pg_numrows($rs1);$i++){
  		$cabe2 = $cab2;
		$deta1 = $det1;
		$deta2 = $det2;
		
		
		$A = pg_fetch_array($rs1,$i);

  		$tip_mov = $A[1];
		$num_mov = $A[2];		
		
		
  		$cabe2 = str_replace("1",str_pad($A[1],15),$cabe2);				
		$cabe2 = str_replace("{Descripcion}",str_pad($A["descripcion"],20),$cabe2);    		
				
		$deta1 = str_replace("1x",str_pad($A[1],10),$deta1);
		$deta1 = str_replace("2x",str_pad($A[2],20),$deta1);
		$deta1 = str_replace("3x",str_pad($A[3],10),$deta1);
		$deta1 = str_replace("4x",str_pad($A[4],10),$deta1);
		$deta1 = str_replace("5x",str_pad($A[5],10),$deta1);	
		$deta1 = str_replace("{orden_compra}",str_pad($A["orden_compra"],10),$deta1);
		
		
		/*Agregado para Tula y su descricion de almacenes*/
		    $rs_alma = pg_exec("select ch_nombre_breve_almacen as des_desc from inv_ta_almacenes 
		    where ch_almacen = '".$A["des"]."' ");
		    $ALMA = pg_fetch_array($rs_alma,0);
		    
		/*Agregado para Tula y su descricion de almacenes*/
		
		$deta2 = str_replace("6x",str_pad($A[6],15),$deta2);
		$deta2 = str_replace("7x",str_pad($A[7],40),$deta2);
		$deta2 = str_replace("8x",str_pad($A[8],10),$deta2);
		$deta2 = str_replace("9x",str_pad($A[9],10),$deta2);
		$deta2 = str_replace("10x",str_pad($A[10],20),$deta2);
		$deta2 = str_replace("11x",str_pad($A[11],4),$deta2);
		$deta2 = str_replace("12x",str_pad($A[12],4),$deta2);
		$deta2 = str_replace("13x",str_pad($A[13],10),$deta2);
		$deta2 = str_replace("14x",str_pad($A[14],10),$deta2);
  
		$deta2 = str_replace("{ori_desc}",str_pad($A["ori_desc"],15),$deta2);
    //orig
		$deta2 = str_replace("{des_desc}",str_pad($ALMA["des_desc"],15),$deta2);
 //des
		
		/*
			$tit_det1 = "<tr><td>{form} {num_mov} {fecha} {almacen} {num_ord} {cod_prov} </td><td></td><td></td><td></td><td></td></tr>";
		$det1 = "<tr><td>1x 2x 3x 4x {orden_compra} 5x</td><td></td><td></td><td></td><td></td></tr>";

$tit_det2 = "<tr><td>{Producto}</td><td>{Descripcion}</td><td>{Cant.}</td>
<td>{Cost.Uni}</td><td>{Total}</td><td>{Origen}</td><td>{Destino}</td><td>{Tip. Doc}</td><td>{Num. Doc}</td></tr>";

		*/
			
	       $tit_det1 = str_replace("{form}",str_pad("Form.",10),$tit_det1);
	       $tit_det1 = str_replace("{num_mov}",str_pad("Num. Formulario",20),$tit_det1);
	       $tit_det1 = str_replace("{fecha}",str_pad("Fecha",10),$tit_det1);
	       $tit_det1 = str_replace("{almacen}",str_pad("Almacen",10),$tit_det1);
	       $tit_det1 = str_replace("{num_ord}",str_pad("Num. Orden",10),$tit_det1);
	       $tit_det1 = str_replace("{cod_prov}",str_pad("Cod. Prov",10),$tit_det1);
	       
		
	    	$tit_det2 = str_replace("{Producto}",str_pad("Producto.",15),$tit_det2);
			$tit_det2 = str_replace("{Descripcion}",str_pad("Descripcion.",40),$tit_det2);
			$tit_det2 = str_replace("{Cant.}",str_pad("Cant.",10),$tit_det2);
			$tit_det2 = str_replace("{Cost.Uni}",str_pad("Cost. Uni..",10),$tit_det2);
			$tit_det2 = str_replace("{Total}",str_pad("Total.",20),$tit_det2);
			$tit_det2 = str_replace("{Origen}",str_pad("Origen",19),$tit_det2);
			$tit_det2 = str_replace("{Destino}",str_pad("Destino",21),$tit_det2);
			$tit_det2 = str_replace("{Tip. Doc}",str_pad("Tip. Doc",10),$tit_det2);
			$tit_det2 = str_replace("{Num. Doc}",str_pad("Num. Doc",15),$tit_det2);
		
			$txt_cabecera = $tit_det1."\n".$tit_det2;
		
		/*total x movimiento*/
			
			$tot = str_replace("6x",str_pad("TOTAL ",25),$tot);
			$tot = str_replace("7x",str_pad("",30),$tot);
			$tot = str_replace("8x",str_pad("",10),$tot);
			$tot = str_replace("9x",str_pad("",10),$tot);
			//$tot = str_replace("10x",str_pad($total_mov,10),$tot);
			$tot = str_replace("11x",str_pad("",10),$tot);
			$tot = str_replace("12x",str_pad("",10),$tot);
			$tot = str_replace("13x",str_pad("",10),$tot);
			$tot = str_replace("14x",str_pad("",10),$tot);
  
		    
			/*total x movimiento*/
		
		//echo "---------".$total_mov."------------";
		
		
  		if($i==0){
			$tip_mov_ant=$tip_mov;
			print "<br>".$tr.$cabe2;	
			$texto = $texto.$tr.$cabe2;
			
			$num_mov_ant=$num_mov;
			
			print $tit_det1.$tr.$deta1.$tit_det2;
			//$texto = $texto.$tr.$deta1;
			//$texto = $texto.$tr.$tit_det1.$deta1.$tit_det2; //sale con las 2 cabeceras
			//$texto = $texto.$tr.$tit_det1.$deta1; //sale con una cabecera
			$texto = $texto.$tr.$deta1;
			
		}

		
		
		if($tip_mov_ant!=$tip_mov){
			$tip_mov_ant=$tip_mov;


			/*antes de pasar a otro movimiento pintamos
			los totales del movimiento actual*/
			
			$tot = str_replace("10x",str_pad($total_mov,10),$tot);
			print $tot; 
			$texto = $texto.$tot;
			
			$tot = str_replace($total_mov,"10x",$tot);
			$total_mov = 0;
			
			$tip_mov_cambiado = true;
			
			
			

			print $tr.$cabe2;
			$texto = $texto.$tr.$cabe2;
			
		}
		
		
				
		if($num_mov_ant!=$num_mov){
			$num_mov_ant=$num_mov;
			
			/*antes de pasar a otro movimiento pintamos
			los totales del movimiento actual*/
			if(!$tip_mov_cambiado){
			$tot = str_replace("10x",str_pad($total_mov,10),$tot);
			print $tot; 
			$texto = $texto.$tot;
			
			$tot = str_replace($total_mov,"10x",$tot);
			$total_mov = 0;
			$tip_mov_cambiado = false;
			
			}
			/**/
			
			//print $tr.$deta1;
			//$texto = $texto.$tr.$deta1;
			print $tit_det1.$tr.$deta1.$tit_det2;
			//$texto = $texto.$tr.$tit_det1.$deta1.$tit_det2;  //sale con 2 cabeceras en los detalles
			//$texto = $texto.$tr.$tit_det1.$deta1;	//sale con 1 cabecera en los detalles
			$texto = $texto.$tr.$deta1;
		
		}
		
		
		
		
		if($num_mov_ant==$num_mov){
		    $total_mov = $total_mov + $A[10];
		}
		
		print $deta2;
		
		
		$texto = $texto.$deta2;
  		/**/
  
  }
  
  /**/
	$tot = str_replace("10x",str_pad($total_mov,10),$tot);
	print $tot; 
	$texto = $texto.$tot;
	
	$tot = str_replace($total_mov,"10x",$tot);
	$total_mov = 0;
	
  /**/
  
  echo "</table>";
  $texto = $texto."</table>";
  
  //imprimir3($texto,"/tmp/consistencia.txt",true);
  imprimir4($texto,"/tmp/consistencia.txt",true,$txt_cabecera);
  /*
  $F = fopen("/tmp/consis.txt","w+");
  fwrite($F,$texto);
  fclose($F);
  */
  
  
  ?>
  
  <input type="hidden" name="imprimir" value="xxx">
  <br><p><a href="#"  onclick="javascript:form1.imprimir.value='imprimir',form1.submit();">Imprimir</a></p>
  
</form>

</body>
</html>
<?php
pg_close($coneccion);
