<?php
include("../valida_sess.php");

include("/sistemaweb/functions.php");
include("/sistemaweb/caja_bancos/funciones.php");
require("../clases/funciones.php");

$funcion = new class_funciones;
// crea la clase para controlar errores
$clase_error = new OpensoftError;
	$clase_error->_error();
//$clase_error->_error();
// conectar con la base de datos
$conector_id=$funcion->conectar("","","","","");

include("inc_top.php");

echo otorgarAlmacen($conector_id, $almacen)."<br>";
echo "NUMERO DE CAMBIO DE ACEITES Y LAVADOS:";

if(strlen($diaa)==0)
{
	rangodefechas();
	//$diad=$zdiaa; $mesd=$zmesa; $anod=$zanoa;
	$diad="01"; $mesd=$zmesa; $anod=$zanoa;
	$diaa=$zdiaa; $mesa=$zmesa; $anoa=$zanoa;
}
$fechad=$anod."-".$mesd."-".$diad;
$fechaa=$anoa."-".$mesa."-".$diaa;

if(strlen(trim($ch_almacen))<=0)
{
	$ch_almacen=$almacen;
}

if(strlen(trim($fecha_val))<=0)
{
	$fecha_val= $funcion->date_format(trim($fechad),'DD/MM/YYYY');
}



if($boton=="Consultar" or (strlen($diad)>0 and strlen($mesd)>0 and strlen($anod)>0 and strlen($diaa)>0 and strlen($mesa)>0 and strlen($anoa)>0))
{
	$sqladd="and DT_FECHA BETWEEN '$fechad' and '$fechaa' ";
}



$sql = "SELECT trim(ch_sucursal)||trim(dt_fecha)
			, ch_sucursal, dt_fecha, nu_cambios_aceite
			, nu_lavados

		FROM
			caj_ta_ajustes_liq
		WHERE ch_sucursal='$ch_almacen' $sqladd
		ORDER BY dt_fecha";









switch ($boton) {
   case Agregar:
			$okgraba=true;
			if(strlen(trim($fecha_val))<=0)
			{
				$okgraba=false; $mensaje="Error!! \\n Fecha Vacio";
			}else{
				$sql_validar = "select dt_fecha from caj_ta_ajustes_liq where dt_fecha='$fecha_val' and ch_sucursal='$ch_almacen'";
				$xsql_validar = pg_query($conector_id, $sql_validar);
				if(pg_num_rows($xsql_validar)>0)
				{
					$okgraba=false; $mensaje="Error!! Fecha\\n Ya registrada en la Estacion";
				}
			}

			if(strlen(trim($new_aceite))<=0 && strlen(trim($new_lavado))<=0)
			{
				$okgraba=false; 
				$mensaje="Error!! Campos Vacios\\n Como mi­nimo digitar un Campo ";
			}

			if(strlen(trim($new_aceite))<=0)
			{ $new_aceite="0"; }

			if(strlen(trim($new_lavado))<=0)
			{ $new_lavado="0"; }


			if($okgraba==true)
			{
					$sql_insert = "
						INSERT INTO CAJ_TA_AJUSTES_LIQ
						(ch_sucursal, dt_fecha, nu_cambios_aceite
						,nu_lavados)
						values
						('$new_estacion','$fecha_val','$new_aceite'
						,'$new_lavado')";
					//echo $sql_insert;
					$xsql_insert = pg_exec($conector_id, $sql_insert);
					if($xsql_insert)
					{
						$new_aceite="";
						$new_lavado="";
					}
			}else {
				echo '<script>alert("'.$mensaje.'")</script>';
			}
	break;


   case Modificar:
		//$okgraba=true;
		/*if(strlen(trim($new_aceite))>0 && strlen(trim($new_lavado))>0)
		{
				$okgraba=false; $mensaje="Error!! Debe digitar\\n  al menos un Ajuste";
		}*/

		$xsqlm=pg_query($conector_id,$sql);
		$ilimitm=pg_num_rows($xsqlm);
		$irowm=0;
		while($irowm<$ilimitm) {
			$am0=pg_result($xsqlm,$irowm,0);
			$idm[$am0]=$am0;
			//echo "--".$idm[$am0]."--".$idp[$am0]."--<br>";
			if($idm[$am0]==$idp[$am0]) {

					if(strlen(trim($m_aceite[$am0]))<=0)
					{ $m_aceite[$am0]="0"; }

					if(strlen(trim($m_lavado[$am0]))<=0)
					{ $m_lavado[$am0]="0"; }

				$sqlupd=" update caj_ta_ajustes_liq set

					nu_cambios_aceite=".$m_aceite[$am0]."
					, nu_lavados=".$m_lavado[$am0]."

					where trim(ch_sucursal)||trim(dt_fecha)='".$idm[$am0]."'";

				//	echo $sqlupd;
				$xsqlupd=pg_exec($conector_id,$sqlupd);
			}
			$irowm++;
		}
      break;


   case Eliminar:
		$xsqlm=pg_exec($conector_id,$sql);
		$ilimitm=pg_numrows($xsqlm);
		$irowm=0;
		while($irowm<$ilimitm) {
			$am0=pg_result($xsqlm,$irowm,0);
			$idm[$am0]=$am0;
			if($idm[$am0]==$idp[$am0]) {
				$sqlupd="delete from caj_ta_ajustes_liq
						 where trim(ch_sucursal)||trim(dt_fecha)='".$idm[$am0]."'";
				$xsqlupd=pg_exec($conector_id,$sqlupd);
			}
			$irowm++;
		}
      break;
 }





















?>
<html>
<head>
<script language="JavaScript" src="js/miguel.js"></script>
<script language="JavaScript" src="/sistemaweb/clases/reloj.js"></script>
<script language="JavaScript" src="/sistemaweb/compras/valfecha.js"></script>

<script language="JavaScript" src="/sistemaweb/clases/calendario.js"></script>
<script language="JavaScript" src="/sistemaweb/clases/overlib_mini.js"></script>
<script language="JavaScript" src="js/validacion.js"></script>
</head>

<BODY>
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<form name='formular' action="" method="post">
  <table border="1">
    <tr>
      <th colspan="5">CONSULTAR POR RANGO DE FECHAS</th>
    </tr>
    <tr>
      <th>DESDE :</th>
      <th><input type="text" name="diad" size="4" maxlength="2" onKeyPress="return esIntspto(event)" value='<?php echo $diad;?>'>
        /
        <input type="text" name="mesd" size="4" maxlength="2" onKeyPress="return esIntspto(event)" value='<?php echo $mesd;?>'>
        /
        <input type="text" name="anod" size="6" maxlength="4" onKeyPress="return esIntspto(event)" value='<?php echo $anod;?>'></th>
      <th>HASTA:</th>
      <th><input type="text" name="diaa" size="4" maxlength="2" onKeyPress="return esIntspto(event)" value='<?php echo $diaa;?>'>
        /
        <input type="text" name="mesa" size="4" maxlength="2" onKeyPress="return esIntspto(event)" value='<?php echo $mesa;?>'>
        /
        <input type="text" name="anoa" size="6" maxlength="4" onKeyPress="return esIntspto(event)" value='<?php echo $anoa;?>'></th>
		<th><input type="submit" name="boton" value="Consultar"></th>
    </tr>
	<tr>
		<th colspan="5">
		<?php
			//echo "ESTACION : ".$ch_almacen;
			combo_Almacenes($conector_id, $ch_almacen, "ch_almacen", "where ch_clase_almacen='1'","");
//			function combo_desde_tabla($conector_id, $variable, $value, $name_tabla, $col_codigo, $col_etiqueta)
		?>

  </table>
    <input type="hidden" name="fm" value='<?php echo $fm;?>'><br>
	<?php
	$var_pers="fm=".$fm."&diad=".$diad."&mesd=".$mesd."&anod=".$anod."&diaa=".$diaa."&mesa=".$mesa."&anoa=".$anoa;
	//include("../maestros/pagina.php");
	?>


<table border="3">
	<tr>
		<th rowspan="4"></th>
		<th rowspan="2">ESTACION</th>
		<th rowspan="2">FECHA</th>
		<th colspan="2">AJUSTES</th>
	</tr>
	<tr>

		<th>CAMBIOS ACEITE</th>
		<th>LAVADOS</th>
	</tr>
	<tr>

		<td><input readonly type="text" name="new_estacion" value="<?php echo $ch_almacen; ?>" size="7" maxlength='3'></td>
		<td><input type="text" name="fecha_val" size="10" maxlength="10" value='<?php echo $fecha_val; ?>' onKeyUp="javascript:validarFecha(this)" onblur="javascript:validarFecha(this)" >
			<a href="javascript:show_calendar('formular.fecha_val');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;">
			<img src="/sistemaweb/images/show-calendar.gif" width="24" height="22" border="0"></a>
		</td>
		<td><input type='text' name='new_aceite' value='<?php echo $new_aceite; ?>' size='10' maxlength='8'></td>
		<td><input type='text' name='new_lavado' value='<?php echo $new_lavado; ?>' size='10' maxlength='8'></td>
	
		
		<td><input type="submit" value="Agregar" name="boton"></td>
	</tr>
	<tr>
		<th>ESTACION</th>
		<th>FECHA</th>
		<th>CAMBIOS ACEITE</th>
		<th>LAVADOS</th>
	</tr>

	<?php
	//echo $sql;
	$xsql = pg_query($conector_id, $sql);
	$i=0;

	while($i<pg_num_rows($xsql))
	{
		$rs = pg_fetch_array($xsql, $i);
		$a = $rs[0];

		$m_estacion = $rs[1];
		$m_fecha = $funcion->date_format(trim($rs[2]),'DD/MM/YYYY');
//		$m_fecha = $rs[2];
		$m_aceite = $rs[3];
		$m_lavado = $rs[4];

		?>
		<tr bgcolor="#CCCC99"
		onMouseOver="this.style.backgroundColor='#FFFFCC';this.style.cursor='hand';"
		onMouseOut="this.style.backgroundColor='#CCCC99'">
		<?php
		echo "
			<td><input type='checkbox' name='idp[$a]' value='$rs[0]'></td>
			<td>$m_estacion</td>
			<td>$m_fecha</td>
			<td><input type='text' name='m_aceite[$a]' value='$m_aceite' size='10' maxlength='8'></td>
			<td><input type='text' name='m_lavado[$a]' value='$m_lavado' size='10' maxlength='8'></td>
			</tr>";
		$i++;
	}
	?>
	<tr>
		<th></th>
		<th colspan="2"><input type="submit" name="boton" value="Modificar"></th>
		<th colspan="2"><input type="submit" name="boton" value="Eliminar"></th>
	</tr>

</table>
<?php
	//echo $sqladd;

	pg_close($conector_id);
