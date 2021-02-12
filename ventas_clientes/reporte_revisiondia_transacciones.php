<?php
include("/sistemaweb/valida_sess.php");
include("config.php");
include("../functions.php");
//include("../valida_sess.php");
//include("store_procedures.php");

function reporte_revdia_transaciones_2($fechad, $fechaa, $cod_almacen, $periodo, $mesa, $modo,$tipo, $cod_art, $add_sql){
	//--(ret,fechadd,fechaa,almacen,periodo,modo)

	if(strlen(trim($cod_art))==0 && $add_sql==" and ( TD='B' or TD='F' or TD='N' or TD='A' )"){
			//echo "<br>HOLA SIN ".$cod_art;
			$q = "select VEN_FN_REVISION_TICKETS('ret', '$fechad', '$fechaa', '$cod_almacen', '$periodo' , '$mesa', '$modo','$tipo')";
			//echo "AQUI TA CON TODOS LOS CAMPOS.: y sin articulo <BR>".$q;
			if(existeError($q,$fechad,$fechaa)){
				pg_exec("begin");
				pg_exec($q);

				$rs = pg_exec("fetch all in ret");
				pg_exec("end");
			}else {
			}
	}else{//echo "Ah ocurrido u error";
		//echo "HOLA CON ARTICULO";
		//name="modo" value="historico"
		if($cod_art){ $add_articulo = "and p.codigo='$cod_art'"; }

		if($modo=='actual')
		{
			$q = "select p.td, p.trans, to_char(p.fecha,'dd/mm/yyyy HH24:mi:ss')
					, CAST(p.codigo as varchar), a.art_descbreve, p.cantidad, p.importe
			
					,tm , precio, tarjeta, odometro, placa , caja, pump

				from int_articulos a, pos_transtmp p
				where a.art_codigo=p.codigo and p.trans is not null
					and p.es='$cod_almacen' and p.tipo='$tipo' 
					
					$add_articulo $add_sql 

				order by p.dia";
			//echo "AQUI LOS CAMPOS DISCRIMINADOS o/y sin articulo en modo actual <BR>".$q;
		$rs = pg_exec($q);
		}
		else if($modo='historico'){
			$q = "
				select p.td,p.trans,to_char(p.fecha,'dd/mm/yyyy HH24:mi:ss'),p.pump
				, a.art_descbreve ,p.cantidad,p.importe

				,tm , precio, tarjeta, odometro, placa, caja, pump

				from pos_trans".$periodo.$mesa." p,
				int_articulos a where a.art_codigo=p.codigo
				and p.dia >= to_date('$fechad','dd/mm/yyyy') and p.es = '$cod_almacen' and p.tipo='$tipo'

					$add_articulo $add_sql

				and p.dia <= to_date('$fechaa','dd/mm/yyyy') order by p.dia
			";
			//echo "AQUI TA LOS CAMPOS discriminados en HISTORICO.: o/y sin articulo <BR>".$q;
		$rs = pg_exec($q);
		}
	}
	return $rs;
}



$user = $usuario; 
$user_temp = $user."_rep6";
if($action=="exportar"){
	exec(" mv /var/www/html/sistemaweb/tmp/$user_temp.txt /var/www/html/sistemaweb/tmp/$user_temp.csv");
	$url = "http://localhost/sistemaweb/tmp/$user_temp.csv";
	?>
	<script language="JavaScript1.3" type="text/javascript">
	window.open('<?php echo $url;?>','miwin','width=10,height=35,scrollbars=yes');
	</script>

	<?php
}else{
$titulo = "REVISIÓN DIARIA DE TRANSACCIONES ";
$cabecera = " $sucursal_dis \n TD , TRAN , FECHA , DESCRIPCIÓN , CANTIDAD , IMPORTE";

//echo $cod_articulo;
if($c_boleta) { $add_sql = $add_sql." or TD='B'"; } 
if($c_factura) { $add_sql = $add_sql." or TD='F'"; } 
if($c_despacho) { $add_sql = $add_sql." or TD='N'"; } 
if($c_afericiones) { $add_sql = $add_sql." or TD='A'"; } 
if($c_devoluciones) { $add_sql = $add_sql." and TM='D'"; } 

if(strlen(trim($add_sql))>0)
{
	$add_sql = " and ( ".substr($add_sql,4,strlen($add_sql))." )";
}

/*$rsM = reporte_revdia_transaciones_2($fechad, $fechaa, $cod_almacen, $periodo, $mes, $modo, "M", $cod_articulo);
$rsC = reporte_revdia_transaciones_2($fechad, $fechaa, $cod_almacen, $periodo, $mes, $modo, "C" , $cod_articulo);
*/
$rsM = reporte_revdia_transaciones_2($fechad, $fechaa, $cod_almacen, $periodo, $v_mes, $modo, "M", $cod_articulo, $add_sql);
$rsC = reporte_revdia_transaciones_2($fechad, $fechaa, $cod_almacen, $periodo, $v_mes, $modo, "C", $cod_articulo, $add_sql);

sacarExcelRevDiaTransacciones($user,$titulo,$almacen,$cabecera,$rsM,$rsC);

}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<!-- <title>Revision Diaria de Transacciones</title> -->
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<div align="center"> 
  <table width="767" border="1" cellpadding="0" cellspacing="0">
    <tr> 
      <td width="457"><a href="vta_reporte_excel.csv" target="_blank" >Exportar 
        a Excel</a></td>
      <td>
		<a href="/sistemaweb/utils/impresiones.php?imprimir=paginar&cabecera=/sistemaweb/ventas_clientes/vta_ticket_cabecera.txt&cuerpo=/sistemaweb/ventas_clientes/vta_ticket_cuerpo.txt&archivo_final=/sistemaweb/inventarios/vta_ticket.txt" target="_blank">Imprimir Texto</a>
	  </td>
    </tr>
  </table>




<?php
	/***   FRED - Exportacion a Texto   ***/
  	$directorio = "/sistemaweb/ventas_clientes";
	$archivo_cabecera = "vta_ticket_cabecera.txt";
	$archivo_cuerpo = "vta_ticket_cuerpo.txt";
	$ancho = 100;
	
	$ft=fopen($archivo_cuerpo,'w');
	$ft_cab=fopen($archivo_cabecera,'w');

	$ft_csv=fopen('vta_reporte_excel.csv','w');
	
	if ($ft_cab>0) {
		//$buffer_cuerpo=str_pad($cod_almacen,45)."                                                             ".str_pad($dia,25," ",STR_PAD_LEFT)."\n";
		$buffer_cabecera=$buffer_cabecera.str_pad("REVISION DIARIA DE TRANSACCIONES", $ancho, " ", STR_PAD_BOTH)."\n";
		$buffer_cabecera=$buffer_cabecera.str_pad(trim($sucursal_dis)." DESDE EL ".$fechad." HASTA ".$fechaa, $ancho ," ", STR_PAD_BOTH)."\n";
		$buffer_cabecera=$buffer_cabecera."TIP \n";
		$buffer_cabecera=$buffer_cabecera."DOC TRAN         FECHA             DESCRIPCION                  CANTIDAD    PRECIO   IMPORTE    TARJETA  PLACA   CODCLI   USUARIO  CAJA"."\n";
		$buffer_cabecera=$buffer_cabecera.str_pad("=", $ancho , "=" , STR_PAD_BOTH)."\n";
	
		$csv_cuerpo=$csv_cuerpo."TIP DOC,TRAN,FECHA,DESCRIPCION,CANTIDAD,CANTIDAD,PRECIO,IMPORTE,TARJETA,PLACA,CODCLI,USUARIO,CAJA"."\n";
	}
	fwrite($ft_cab, $buffer_cabecera);
	fclose($ft_cab);
?>
	<strong>REVISION DIARIA DE TRANSACCIONES<br></strong>
	<?php echo $sucursal_dis." DESDE EL ".$fechad." HASTA ".$fechaa; ?></p>
	
  <table width="615" border="1" cellpadding="0" cellspacing="0">
    <tr>
      <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">TM</font></div></td>
      <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">TD</font></div></td>
      <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">TRAN</font></div></td>
      <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">FECHA</font></div></td>
      <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">DESCRIPCION</font></div></td>
      <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">CANTIDAD</font></div></td>
      <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">PRECIO</font></div></td>
      <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">IMPORTE</font></div></td>
      <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">TARJETA</font></div></td>
      <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">ODOMETRO</font></div></td>
      <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">PLACA</font></div></td>
      <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">COD-CLI</font></div></td>
      <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">USUARIO</font></div></td>
      <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">CAJA</font></div></td>
      <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">LADO</font></div></td>
    </tr>
    <!--<?php  
	  for($k=1;$k<=2;$k++){
	  if($k==1){$rs = $rsM;}
	  if($k==2){$rs = $rsC;}
	  for($i=0;$i<pg_numrows($rs);$i++){
	  $A = pg_fetch_array($rs,$i);

	$total_cant = $total_cant + $A[5];
	$total_imp = $total_imp + $A[6];

	$snewbuffer=$snewbuffer.str_pad(trim($A[0]),4," ",STR_PAD_BOTH);
	$snewbuffer=$snewbuffer.str_pad(trim($A[1]),5," ",STR_PAD_BOTH);
	$snewbuffer=$snewbuffer.str_pad(trim($A[2]),23," ",STR_PAD_BOTH);

	$snewbuffer=$snewbuffer.str_pad(substr(trim($A[3])." - ".trim($A[4]),0,30),30," ",STR_PAD_RIGHT);
	$snewbuffer=$snewbuffer.str_pad(NUMBER_FORMAT(trim($A[5]),3),10," ",STR_PAD_LEFT);
	$snewbuffer=$snewbuffer.str_pad(NUMBER_FORMAT(trim($A[8]),2),10," ",STR_PAD_LEFT);
	$snewbuffer=$snewbuffer.str_pad(NUMBER_FORMAT(trim($A[6]),2),10," ",STR_PAD_LEFT);

	$snewbuffer=$snewbuffer.str_pad(trim($A[9]),12," ",STR_PAD_LEFT);
	$snewbuffer=$snewbuffer.str_pad(trim($A[10]),10," ",STR_PAD_LEFT);

	$snewbuffer=$snewbuffer.str_pad(trim($codigo_cliente),7," ",STR_PAD_LEFT);
	$snewbuffer=$snewbuffer.str_pad(substr(trim($contacto),0,10),10," ",STR_PAD_LEFT);
	$snewbuffer=$snewbuffer.str_pad(trim($A[12]),4," ",STR_PAD_LEFT)."\n";	

	$csv_cuerpo = $csv_cuerpo.$A[0].",".$A[1].",".$A[2].",".$A[3]." - ".$A[4].",".$A[5].",".$A[8].",".$A[6].",".$A[9].",".$A[10].",".$codigo_cliente.",".$contacto.",".$A[12]."\n";

	print '
	  ?> -->
    <tr bgcolor="#FFFFFF"> 
      <td> 
        <div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">'.$A[7].'</font></div></td>
      <td> 
        <div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">'.$A[0].'</font></div></td>
      <td> 
        <div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">'.$A[1].'</font></div></td>
      <td> 
        <div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">'.$A[2].'</font></div></td>
      <td> 
        <div align="left"><font size="-4" face="Arial, Helvetica, sans-serif"> 
          '.$A[3].' - '.$A[4].'</font></div></td>
      <td> 
        <div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">'.$A[5].'</font></div></td>
      <td> 
        <div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">'.$A[8].'</font></div></td>
      <td> 
        <div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">'.$A[6].'</font></div></td>
      <td> 
        <div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$A[9].'</font></div></td>
      <td> 
        <div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$A[10].'</font></div></td>
      <td> 
        <div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$A[11].'</font></div></td>
      <td> 
        <div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$codigo_cliente.'</font></div></td>
      <td> 
        <div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$contacto.'</font></div></td>
      <td> 
        <div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$A[12].'</font></div></td>
      <td> 
        <div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$A[13].'</font></div></td>
    </tr>
    <!-- <?php  ';} 
	 	} ?> -->
    <tr>
      <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
      <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
      <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
      <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
      <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
      <td align="right"><font size="-4" face="Arial, Helvetica, sans-serif">TOTAL .:</font></td>
      <td align="center"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo number_format($total_cant,3); ?></font></td>
      <td align="center"><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
      <td align="center"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo number_format($total_imp,3); ?></font></td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
    </tr>
  </table>
</div>
</body>
</html>

<?php
		$snewbuffer=$snewbuffer.str_pad(" ",32," ",STR_PAD_BOTH);		
		$snewbuffer=$snewbuffer.str_pad(" TOTAL .: ",39," ",STR_PAD_LEFT);
		$snewbuffer=$snewbuffer.str_pad(number_format($total_cant,4),14," ",STR_PAD_LEFT);
		$snewbuffer=$snewbuffer.str_pad(number_format($total_imp,4),14," ",STR_PAD_LEFT)."\n";

/*	$snewbuffer=$snewbuffer.str_pad("=", $ancho , "=" , STR_PAD_BOTH)."\n";
	$snewbuffer=$snewbuffer."\t\t\t\t\t\t\t TOTAL \t\t".number_format($total_cant,3)." \t  ".number_format($total_imp,3)." \n"; */

		fwrite($ft,$snewbuffer);
		fclose($ft);
		
		fwrite($ft_csv, $csv_cuerpo);
		fclose($ft_csv);
