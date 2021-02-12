<?php
//include("/sistemasweb/valida_sess.php");
include("../menu_princ.php");
include("../functions.php");

require("../clases/funciones.php");
$funcion = new class_funciones;
$coneccion=$funcion->conectar("","","","","");

$month = pg_exec("SELECT par_valor from int_parametros where par_nombre= 'inv_mes_cierre';");	
$array_month = pg_fetch_row($month,0);
$mes = $array_month[0];
	
$year = pg_exec("SELECT par_valor from int_parametros where par_nombre= 'inv_ano_cierre';");
$array_year = pg_fetch_row($year,0);
$anio = $array_year[0];

if($mes < 12){
     $mes = $mes + 1;     
} else {
     $mes = 1;
     $anio = $anio + 1;	
}

if($boton=="Procesar"){

	$sql = "
		SELECT
			a.par_valor||m.par_valor
		FROM
			int_parametros a,
			int_parametros m
		WHERE
			a.par_nombre = 'inv_ano_cierre'
			AND m.par_nombre = 'inv_mes_cierre';
		";

	$xsql		= pg_exec($coneccion,$sql);
	$par_anomes	= pg_result($xsql,0,0);

	$sql = "
		SELECT TO_CHAR(now(), 'YYYYmm');
		";

	$xsql=pg_exec($coneccion,$sql);
	$max_anomes=pg_result($xsql,0,0);

	if ($anod.$mesd<=$par_anomes )
		{
		echo "ANO MES DE INICIO MENOR ANO MES CERRADO";		
		}
	elseif 	($anod.$mesd>$max_anomes )
		{
		echo "ANO MES DE INICIO MAYOR ANO MES CERRADO";		
		}
	else
	{

	$sql1="select art_codigo from int_articulos where ( art_plutipo='1     ' or art_plutipo='4     ' ) order by art_codigo";
	$xsql1=pg_exec($coneccion,$sql1);
	$ilimit1=pg_numrows($xsql1);
	$irow1=0;
	if ($ilimit1>0)
		{
		while ($irow1<$ilimit1)
			{
			$articulo=pg_result($xsql1,$irow1,0);
			$sql2="SELECT inv_fn_regenera_item('$anod','$mesd','$alma','$articulo','SI')";
			
			echo "-- QUERY : ".$sql2."--";	
			$xsql2=pg_exec($coneccion,$sql2);
			$ilimit2=pg_numrows($xsql2);
			if($ilimit2>0) 
				{
				$numeroRegistros=$ilimit2;
				}

			$sql3="commit;";
			$xsql3=pg_exec($coneccion,$sql3);
			echo "aqui es el numero ".$ilimit2.$articulo;
			$irow1++;
			}
		echo "\nREGENERACION FINALIZADA" ;
		}

	}
	}


?>
<script>
function mandarDatos(form,opcion)
	{
	form.alma_descri.value=form.alma.options[form.alma.selectedIndex].text;
	form.submit();
	}
</script>

REGENERAR SALDOS
<hr noshade>
<form action="inv_regenera.php" method="post" name="form1">
<br><p>

  <table border="0" colspan=0>
    <tr>
      <th colspan="5">Periodo Regeneracion</th>
    </tr>
    <tr>
      <th>ALMACEN :</th>
        <th colspan="2">
	<select name="alma">
          	<?php
		if($boton="Procesar")
			{
			print "<option value='$alma'>$alma_descri</option>";
			}
		?>
        <?php
	$rsf=pg_exec(" select a.ch_almacen,
				a.ch_nombre_almacen 
			from inv_ta_almacenes a
                	where a.ch_clase_almacen='1'  
			 ");
        for($i=0;$i<pg_numrows($rsf);$i++)
		{
                $A = pg_fetch_array($rsf,$i);
                print "<option value='$A[0]'>$A[1]</option>";
                }
        ?>
        </select></th>
        <th><input type="hidden" name="alma_descri" value="<?php echo $alma_descri;?>"></th>
        <th><input type="hidden" name="boton"></th>
    </tr>
	<?php 
		if($mes>0 and $mes<10) $mes = "0".$mes;
	?>
    <tr>
      <th><div align="left">PERIODO MES/ANO :</div></th>
	<th>
		<input type="text" name="mesd" size="4" maxlength="2" onKeyPress="return esIntspto(event)" value="<?php echo $mes;?>">
		/
		<input type="text" name="anod" size="6" maxlength="4" onKeyPress="return esIntspto(event)" value="<?php echo $anio;?>">
	</th>
      <th><input type="button" name="botonN" value="Procesar" onClick="javascript:boton.value='Procesar',mandarDatos(form1,'Procesar');"></th>
    </tr>
  </table>
  <br>

<select name="m_almacen" tabindex="2">
				<option value="all">Todos los Almacenes</option>
				<?php
					for($i=0;$i<pg_numrows($v_xsqlalma);$i++){		
						$k_alma1 = pg_result($v_xsqlalma,$i,0);	
						$k_alma2 = pg_result($v_xsqlalma,$i,1);
						if (trim($k_alma1)==trim($_REQUEST['m_almacen'])) { 
							echo "<option value='".$k_alma1."' selected >".$k_alma1." -- ".$k_alma2." </option>";	
						} 
						else {
							echo "<option value='".$k_alma1."' >".$k_alma1." -- ".$k_alma2." </option>";	
						}
					}
				?>
			</select>

 <?php
?>
</form>
<!--
<form action="inv_regenera.php" method="POST" name="form45" style="width:300px" align="center">
   <div id="login" align="center" style="widht:40%; border:1px solid grey; padding-top:15px; padding-bottom:15px">
      Ingreso al sistema<br><br>
      <table>
         <tr>
            <th>Tipo de usuario</th>
            <th><select name="tipoUsuario"/> 
                   <option>Administrador</option>
                   <option>Regular</option> 	
            </th>
         </tr>
         <tr>
            <th>Usuario:</th>
            <th><input type = "text" name = "usuario" size="12"/></th>
         </tr>
         <tr>
            <th>Password:</th>
            <th> <input type = "password" name = "pass" size="12 "/></th>
         </tr>
      </table><br>
      <input type = "Submit" name = "acepta" size="10" value ="Ingresar"/>
   </div>
</form> -->
