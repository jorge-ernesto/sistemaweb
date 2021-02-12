<script>
function mandarDatos(form,opcion){

	form.alma_descri.value=form.alma.options[form.alma.selectedIndex].text;
	form.submit();
}
</script>
<?php
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
//session_start();

//include("../config.php");
//include("inc_top.php");
include("../menu_princ.php");
/*obtenemos fecha*/
$nuevofechad = split('/',$_REQUEST['diasd']);
$diad=$nuevofechad[0];
$mesd=$nuevofechad[1];
$anod=$nuevofechad[2];
$nuevofechaa = split('/',$_REQUEST['diasa']);
$diaa=$nuevofechaa[0];
$mesa=$nuevofechaa[1];
$anoa=$nuevofechaa[2];
include("../functions.php");
include("/sistemaweb/utils/funcion-texto.php");
include("store_procedures.php");

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

$a_rs = pg_exec("select trim(par_valor)  from int_parametros where par_nombre='lista precio'"); 
$X = pg_fetch_array($a_rs,0);
$lista_prec = $X[0];
if(!isset($alma) || $alma==""){
$a_rs = pg_exec("select trim(par_valor)  from int_parametros where par_nombre='codes'"); 
$X = pg_fetch_array($a_rs,0);
$alma = $X[0];
}

$q1 = "select  	'xx'
		,trim(i.tran_codigo) as tip_mov,	i.mov_numero as num_movimiento 
		,to_char(i.mov_fecha,'dd/mm/yyyy') as fecha 	
		,i.com_serie_compra as serie
		,i.mov_entidad as cod_prov	,	i.art_codigo as cod_producto 
		,substring(trim(a.art_descripcion) for 40 from 1) as desc_producto	,	i.mov_cantidad as cantidad 
		,lista.pre_precio_act1	, 	i.mov_costototal as total	
		,i.mov_almaorigen as ori,	i.mov_almadestino as des
		,i.mov_tipdocuref as tip_docurefe,	i.mov_docurefe as num_docurefe
		,t.tran_descripcion as desc_movimiento , i.com_num_compra as orden_compra 
		,almacenes.ch_nombre_breve_almacen as desc_ori
		,i.mov_costounitario as costo_unitario 
		from inv_movialma i , int_articulos a , inv_tipotransa t 
		,fac_lista_precios lista , inv_ta_almacenes almacenes 
		where i.art_codigo = a.art_codigo 
		and i.tran_codigo = t.tran_codigo
		and i.tran_codigo < 23  
		and lista.art_codigo = a.art_codigo
		and i.mov_almacen='$alma'
		and lista.pre_lista_precio ='$lista_prec' 	 
		and i.mov_fecha <= '$anoa-$mesa-$diaa 23:59:59' 
		and i.mov_fecha >= '$anod-$mesd-$diad 00:00:00'
		and almacenes.ch_almacen = i.mov_almaorigen 
		order by tip_mov,num_movimiento
		" ;
		
		$q1 = "select  	'xx'
		,trim(i.tran_codigo) as tip_mov,	i.mov_numero as num_movimiento 
		,to_char(i.mov_fecha,'dd/mm/yyyy') as fecha 	
		,i.com_serie_compra as serie
		,i.mov_entidad as cod_prov	,	i.art_codigo as cod_producto 
		,substring(trim(a.art_descripcion) for 40 from 1) as desc_producto	
		,i.mov_cantidad as cantidad 
		,lista.pre_precio_act1	, 	i.mov_costototal as total	
		,i.mov_almaorigen as ori,	i.mov_almadestino as des
		,i.mov_tipdocuref as tip_docurefe,	i.mov_docurefe as num_docurefe
		,t.tran_descripcion as desc_movimiento , i.com_num_compra as orden_compra 
		,almacenes.ch_nombre_breve_almacen as desc_ori
		,i.mov_costounitario as costo_unitario 
		from inv_movialma i 
		join int_articulos a on i.art_codigo = a.art_codigo
		join inv_tipotransa t on i.tran_codigo = t.tran_codigo
		left join fac_lista_precios lista on lista.pre_lista_precio ='$lista_prec'
		and lista.art_codigo = a.art_codigo
		join inv_ta_almacenes almacenes on almacenes.ch_almacen = i.mov_almaorigen 
		where  
		( cast(trim(i.tran_codigo) as int) < '23'  or
		  cast(trim(i.tran_codigo) as int) ='54'   or
		  cast(trim(i.tran_codigo) as int) ='55' )
		and i.mov_almacen='$alma'
		and i.mov_fecha <= '$anoa-$mesa-$diaa 23:59:59' 
		and i.mov_fecha >= '$anod-$mesd-$diad 00:00:00'
		order by tip_mov,num_movimiento,i.art_codigo
		" ;
				
//echo $q1;		
$rs1 = pg_exec($q1);

$cab2 = "<tr><td><b>(*)Tipo de Movimiento : <br><b>- {Descripcion}</b></b></td><td></td><td></td><td></td></tr>";

$tit_det1 = "<tr><td>{form} {num_mov} {fecha} </td><td>{almacen}</td><td>{num_ord}</td><td>{cod_prov}</td><td></td></tr>";
$det1 = "<tr><td>1x 2x 3x</td><td>4x</td><td>{orden_compra}</td><td>5x</td><td></td></tr>";

$tit_det2 = "<tr><td>{Producto}</td><td>{Descripcion}</td><td>{Cant.}</td><td>{Cost.Uni}</td><td>{Total}</td><td>{Origen}</td><td>{Destino}</td><td>{Tip. Doc}</td><td>{Num. Doc}</td></tr>";
$det2 = "<tr><td>6x</td><td>7x</td><td>8x</td><td>9x</td><td>10x</td><td>11x {ori_desc}</td><td>12x  {des_desc}</td><td>13x</td><td>14x</td></tr>";

$tot = "<tr><td>6x</td><td>7x</td><td>8x</td><td>9x</td><td>10x</td><td>11x</td><td>12x</td><td>13x</td><td>14x</td></tr>";


?>
MOVIMIENTOS DE CONSISTENCIA DE ALMACEN
<hr noshade>
<form action="inv_consistencia2.php" method="post" name="form1">
<br><p>
<script src="/sistemaweb/js/calendario.js" type="text/javascript" ></script>
<script src="/sistemaweb/js/overlib_mini.js" type="text/javascript" ></script>
<!--- <a href="#"  onclick="javascript:form1.imprimir.value='imprimir',form1.submit();">Imprimir</a></p>  --->
  <table border="0" colspan=0>
    <tr> 
      <th colspan="5">CONSULTAR POR RANGO DE FECHAS </th>
    </tr>
    <tr> 
      <th>ALMACEN :</th>
      <th colspan="2"><select name="alma">
          <?php if($boton="Consultar"){print "<option value='$alma'>$alma_descri</option>";}?>
          <?php $rsf=pg_exec(" select  a.ch_almacen,a.ch_nombre_almacen from inv_ta_almacenes a,int_parametros b 
		where trim(a.ch_sucursal)=trim(b.par_valor) and b.par_nombre='codes' ");
		for($i=0;$i<pg_numrows($rsf);$i++){
			$A = pg_fetch_array($rsf,$i);
			print "<option value='$A[0]'>$A[1]</option>";
		}
		?>
        </select></th>
      <th><input type="hidden" name="alma_descri" value="<?php echo $alma_descri;?>"></th>
      <th><input type="hidden" name="boton"></th>
    </tr>
    <tr> 
      <th><div align="left">DESDE :</div></th>
      <th><input type="text" name="diasd" size="10" value="<?php echo $diad.'/'.$mesd.'/'.$anod ?>" readonly="true"/>
	&nbsp;<a href="javascript:show_calendar('form1.diasd');"><img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a><div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"/></div></th>
      <th>HASTA:</th>
      <th><input type="text" name="diasa" size="10" value="<?php echo $diaa.'/'.$mesa.'/'.$anoa ?>" readonly="true"/>
	&nbsp;<a href="javascript:show_calendar('form1.diasa');"><img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a></th>
      <th><input type="button" name="botonN" value="Consultar" onClick="javascript:boton.value='Consultar',mandarDatos(form1,'Consultar');"></th>
    </tr>
  </table>
  <br>
	<?php

		/***   FRED - Exportacion a Texto   ***/
		$ft_cab=fopen('consis_movi_alma_cabecera.txt','w');
		$ft=fopen('consis_movi_alma_cuerpo.txt','w');

		if ($ft_cab>0) {
			$sql = "select ch_nombre_sucursal from int_ta_sucursales where ch_sucursal='$almacen'";
			$dia = date ("d/m/Y - H:i:s", time());
			
			$cod_almacen = trim(pg_result(pg_query($coneccion, $sql),0,0));
			//$buffer_cuerpo=str_pad($cod_almacen,45)."                                                             ".str_pad($dia,25," ",STR_PAD_LEFT)."\n";
			$buffer_cabecera=$buffer_cabecera.str_pad("CONSISTENCIA DE MOVIMIENTOS DE ALMACEN DEL $diad/$mesd/$anod AL $diaa/$mesa/$anoa", 125, " ", STR_PAD_BOTH)."\n";
			$buffer_cabecera=$buffer_cabecera.str_pad("$almacen - $cod_almacen",134," ", STR_PAD_BOTH)."\n\n";
			$buffer_cabecera=$buffer_cabecera." FORMULARIO     FECHA    No.O/C  PROVEEDOR                  \n";
			$buffer_cabecera=$buffer_cabecera."   CODIGO          DESCRIPCION                    CANTIDAD     COSTO     PRECIO      TOTAL      ORIGEN  -  DESTINO        DOC.REF.\n";
		}

		fwrite($ft_cab, $buffer_cabecera);
		fclose($ft_cab);

		/***   FRED - Exportacion a Texto   ***/

	?>
	<a href="/sistemaweb/utils/impresiones.php?imprimir=paginar&cabecera=/sistemaweb/inventarios/consis_movi_alma_cabecera.txt&cuerpo=/sistemaweb/inventarios/consis_movi_alma_cuerpo.txt&archivo_final=/sistemaweb/inventarios/consis_movi_alma.txt" target="_blank">Imprimir</a>
<?php
//echo "<br>".$_SESSION['ip_printer_default'];
?>


  <table width="754" border="0" cellspacing="0">
    <tr> 
      <th colspan="2">RESUMEN DE REGISTROS POR TIPO DE MOVIMIENTO</th>
    </tr>
    <?php
	$tip_mov_act = "x";
	$num_mov_act = "x";
  		for($i=0;$i<pg_numrows($rs1);$i++){
  		$A = pg_fetch_array($rs1,$i);	
		$tip_mov = $A["tip_mov"];
		$num_mov = $A["num_movimiento"];
		
		/*Agregado para Tula y su descripcion de almacenes (acentos omitidos aproposito)*/
		    $rs_alma = pg_exec("select ch_nombre_breve_almacen as des_desc from inv_ta_almacenes 
		    where ch_almacen = '".$A["des"]."' ");
		    $ALMA = pg_fetch_array($rs_alma,0);
		    $destino = $A["des"]." - ".$ALMA['des_desc'];
		/*Agregado para Tula y su descripcion de almacenes*/
		
  	?>
    <tr> 
      <?php if($tip_mov_act!=$tip_mov){
	  $tip_mov_act  = $tip_mov ;
	  ?>
	  <td width="600"><b>(*)Tipo de Movimiento <?php echo $tip_mov_act;?> : 
        <b>- <?php echo $A['desc_movimiento'];?></b></b></td>

<?php		
		/*  FRED  */
		$buffer_cuerpo=$buffer_cuerpo.str_pad("-",134,"-")."\n";
		$buffer_cuerpo=$buffer_cuerpo.str_pad("(*) TIPO DE MOVIMIENTO : ".$A['tip_mov']." - ".$A['desc_movimiento']."\n",50);     
		/*  FRED  */
?>
      <td width="3"></td>
      <td width="0"></td>
      <td width="268"></td>
    </tr>
	
	   <!--Cabeceras-->
	<tr>
		<td>Movimiento Fecha</td>
		<td>Orden</td>
		<td>Proveedor</td>
	</tr>
	
	<tr>
		<td>Codigo</td>
		<td>Descripcion</td>
		<td>Cantidad</td>
		<td>Costo</td>
		<td>Total</td>
		<td>Origen</td>
		<td>Destino</td>
		<td>Doc. Ref</td>
		<td>Num. Doc. Ref</td>
	</tr>
	<!--Cabeceras-->
	<?php } ?>
	
	<!-- Esto es la condicional para la pequena descripcion del tipo de movimiento ---> 
    <?php if($num_mov_act!=$num_mov) {	
				$num_mov_act = $num_mov;
		?>
    <tr> 
      <td><strong><br>
        <?php echo $A['tip_mov']; ?> <?php echo $A['num_movimiento']; ?> <?php echo $A['fecha']; ?></strong></td>
      <td><strong><br>
        <?php echo $A['orden_compra']; ?></strong></td>
      <td><strong><br>
        <?php echo $A['cod_prov']; ?></strong></td>
      <td>&nbsp;</td>
      <td></td>
    </tr>
    <?php 
		$buffer_cuerpo=$buffer_cuerpo."\n";
		$buffer_cuerpo=$buffer_cuerpo.str_pad($A['tip_mov']." ".$A['num_movimiento']." ".$A['fecha']." ".$A['orden_compra']." ".$A['cod_prov'],100)."\n";
	}
	
	?>
	<!-- Esto es la condicional para la pequena descripcion del tipo de movimiento --->

    <tr> 
      <td><?php echo $A['cod_producto'];?></td>
      <td><?php echo $A['desc_producto'];?></td>
      <td><?php echo $A['cantidad'];?></td>
      <td><?php echo $A['costo_unitario'];?></td>
      <td><?php echo $A['total'];?></td>
      <td><?php echo $A['ori'];?> - <?php echo $A['desc_ori'];?></td>
      <td><?php echo $destino;?></td>
      <td><?php echo $A['tip_docurefe'];?></td>
      <td><?php echo $A['num_docurefe'];?></td>
    </tr>
    <?php
		$buffer_cuerpo=$buffer_cuerpo.str_pad($A['cod_producto'],15);
		$buffer_cuerpo=$buffer_cuerpo.str_pad($A['desc_producto'],30);

		$buffer_cuerpo=$buffer_cuerpo.str_pad(number_format($A['cantidad'],3),12," ",STR_PAD_LEFT);
		$buffer_cuerpo=$buffer_cuerpo.str_pad(number_format($A['costo_unitario'],3),12," ",STR_PAD_LEFT);
		$buffer_cuerpo=$buffer_cuerpo.str_pad(" ",10," ",STR_PAD_LEFT);
		$buffer_cuerpo=$buffer_cuerpo.str_pad(number_format($A['total'],3),12," ",STR_PAD_LEFT);

		$desc_ori = trim($desc_ori);
		$destino = trim($destino);
		
		$buffer_cuerpo=$buffer_cuerpo.str_pad(trim($A['desc_ori'])." ".substr($destino,4,strlen($destino)),29," ",STR_PAD_BOTH);
		$buffer_cuerpo=$buffer_cuerpo.str_pad(trim($A['tip_docurefe']." ".$A['num_docurefe']), 12," ",STR_PAD_BOTH);
		$buffer_cuerpo=$buffer_cuerpo."\n";
	}

  imprimirConsistencia($rs1);
  ?>
  </table>

<!--   COMENTADO POR FRED   
  <a href="/sistemaweb/utils/impresiones.php?imprimir=ok&archivo=/sistemaweb/inventarios/consis_movi_alma.txt" target="_blank">Imprimir</a>

  <p><a href="#" onclick="javascript:form1.imprimir.value='imprimir',form1.submit();">Imprimir</a></p>
    --->
	
</form>

</body>
</html>
<?php

/*  FRED - importacion a texto  */
		$buffer_cuerpo=$buffer_cuerpo.str_pad("-",134,"-")."\n";
		$buffer_cuerpo=$buffer_cuerpo."\n";

	fwrite($ft,$buffer_cuerpo);
	fclose($ft);
/*  FRED - importacion a texto  */


	pg_close($coneccion);
