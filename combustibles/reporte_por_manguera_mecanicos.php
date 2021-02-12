<?php
include("../valida_sess.php");
include("../functions.php");
require("../clases/funciones.php");
$funcion = new class_funciones;
// crea la clase para controlar errores
$clase_error = new OpensoftError;
$clase_error->_error();
// conectar con la base de datos
$conector_id=$funcion->conectar("","","","","");

//include("inc_top.php");

?>
<html>
<head>
<link rel="stylesheet" href="<?php echo $v_path_url; ?>sistemaweb.css" type="text/css">
<script language="JavaScript1.2" src="<?php echo $v_path_url; ?>images/stm31.js"></script>
</head>
<form name='gean' action='' method='post' background='FFFFFF' style='background-color: white !important;'>
<div>
<?php
if(empty($id)){
}else{
$sql_cabecera = "SELECT
			to_char(created, 'DD/MM/YYYY') || ' ' || to_char(created, 'HH:MI:SS AM') as fecha,
			shift,
			to_char(systemdate, 'DD/MM/YYYY')
		 FROM
			f_totalizerm
		 WHERE
			created = '$id'";

$xsql_cabecera = pg_query($conector_id, $sql_cabecera);
}
?>
<LINK href='styles.css' type=text/css rel=stylesheet>
<link href="/central/calendario/css/calendario.css" type="text/css" rel="stylesheet">
<script src="/central/calendario/js/calendar.js" type="text/javascript"></script>
<script src="/central/calendario/js/calendar-es.js" type="text/javascript"></script>
<script src="/central/calendario/js/calendar-setup.js" type="text/javascript"></script>
<h3 align="center"><b>DETALLE POR MANGUERAS</b></h3>
<div style="HEIGHT:550px; WIDTH:450px; OVERFLOW:auto">
<table align = "center">

	<?php if(empty($id)){ ?>
	<tr>
		<th>Sucursales: 
		<td colspan="3">
		<select name="estacion">
		<?php $sql = "SELECT
				ch_almacen,
				ch_almacen||' - '||ch_nombre_almacen
			   FROM
			 	inv_ta_almacenes
			   WHERE
				ch_clase_almacen='1' 
			   ORDER BY
				ch_almacen;";

			if ($sqlca->query($sql) < 0) 
				return false;

			$result = Array();
			for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    	$a = $sqlca->fetchRow();		    
		    	$result[$a[0]] = $a[1];
		?>
			<option value="<?php echo $a[0]; ?>">
				<?php echo $result[$a[0]]; ?>
			</option>
			<?php }?>
		</select> 
		<?php } ?>
	</tr>

	<tr>
		<th>Fecha Sistema:
		<td>
		<?php if(empty($id)){ $fecha = date(d."/".m."/".Y)?>
			<div style = "cursor: pointer;">
			<input type='text' name='fecha' id='fecha' maxlength="10" size="10" onkeypress="return validar(event,3)" value='<?php echo (empty($fecha) ? $fecha : $_REQUEST['fecha']); ?>'>
			<img src='/central/img/showcalendar.gif' border=0 align=top id=lanzador /></div>
			<script type='text/javascript'> 
			Calendar.setup({ 
				inputField  :    'fecha',     // id del campo de texto 
				ifFormat    :     '%d/%m/%Y', // formato de la fecha que se escriba en el campo de texto 
				button      :    'lanzador'   // el id del botón que lanzará el calendario 
			}); 
			</script>	
		<?php }else{
			echo pg_result($xsql_cabecera,0,2);
		   } ?>
		<th>Turno:
		<td>
		<?php if(empty($id)){ ?>
			<input name="turno" type="text" id="turno" maxlength="1" size="1" onkeypress="return validar(event,3)" value = '1' />
		<?php }else{
			 echo pg_result($xsql_cabecera,0,1); 
		   }?>
		
</table>


<table align = "center" border="0" cellpadding="0" cellspacing="1" bgcolor="4682B4">
	<tr>
		<th width="40">LADO
		<th width="80">MANGUERA
		<th width="80">CONTOMETRO
		<th width="40">PRECIO
		<th width="120">PRODUCTO
	</tr>
<script>

function validar(e,tipo){

	tecla=(document.all)?e.keyCode:e.which;

	if (tecla==13 || tecla==8 || tecla== 0)
		return true;

	switch(tipo){
		/*letras y numeros, puntos */
		case 1: patron=/[A-Z a-z0-9./:,;.-]/;break;
		/*solo numeros enteros */
		case 2: patron=/[0-9]/;break;
		/*solo numeros dobles*/
		case 3: patron=/[0-9./]/;break;
		/*solo letras*/
		case 4: patron=/[A-Z a-z]/;break;
		/*telefonos y faxes*/
		case 5: patron=/[0-9/-]/;break;
	}

	teclafinal=String.fromCharCode(tecla);
	return patron.test(teclafinal);
}

</script>
	<?php

	if(empty($id)){
	$sql = "SELECT DISTINCT
			l.f_pump_id lado,
			m.f_grade_id manguera,
			'',
			round(p.nu_preciocombustible,2),
			p.ch_nombrebreve
		FROM
			f_grade m
			JOIN f_pump l ON(l.f_pump_id = m.f_pump_id)
			JOIN comb_ta_combustibles p ON(m.product = p.ch_codigocombustible)
		ORDER BY
			lado,
			manguera;";
	}else{
	$sql = "SELECT
			l.f_pump_id lado,
			m.f_grade_id manguera,
			round(c.volume,2),
			round(p.nu_preciocombustible,2),
			p.ch_nombrebreve
		FROM
			f_grade m
			JOIN f_pump l ON(l.f_pump_id = m.f_pump_id)
			JOIN f_totalizerm c ON(c.f_grade_id = m.f_grade_id)
			JOIN comb_ta_combustibles p ON(m.product = p.ch_codigocombustible)
		WHERE
			c.created = '$id'
		ORDER BY
			lado,
			manguera";
	}
	//echo $sql;
	$sql = $sql.$addsql;
	$xsql = pg_query($conector_id, $sql);
	$i=0;
	$insert = array();

	while($i<pg_num_rows($xsql))
	{

		$rs = pg_fetch_array($xsql, $i);

		echo "<tr bgcolor='FFFFFF'>";

		echo "		<td align='center'><input name='lado[]' type ='hidden' value = '$rs[0]' />$rs[0]
				<td align='center'><input name='manguera[]' type ='hidden' value = '$rs[1]' />$rs[1]</td>
			";
			if(empty($rs[2])){
				$volu = $_REQUEST['volume'][$i];
		echo "		<td align='center'><input name='volume[]' type='text' maxlength='16' size='16' onkeypress='return validar(event,3)' value='$volu' />
				<td align='center'><input name='precio[]' type ='hidden' value = '$rs[3]' />$rs[3]&nbsp;&nbsp;
				<td align='center'><input name='producto[]' type ='hidden' value = '$rs[4]' />$rs[4]&nbsp;&nbsp;
				";
			}else{
		echo "
				<td align='center'><input name='volume[]' type ='hidden' value = '$rs[2]' />$rs[2]&nbsp;&nbsp;
				<td align='center'><input name='precio[]' type ='hidden' value = '$rs[3]' />$rs[3]&nbsp;&nbsp;
				<td align='center'><input name='producto[]' type ='hidden' value = '$rs[4]' />$rs[4]&nbsp;&nbsp;
			";
			}
		echo "</tr>";

		$insert = $i;

		$i++;
	}
	?>
</table>
<?php
	switch ($boton) { 

	    	case 'Guardar':

			$warehouse = $_REQUEST['estacion'];

			$dia = substr($_REQUEST['fecha'],6,4)."-".substr($_REQUEST['fecha'],3,2)."-".substr($_REQUEST['fecha'],0,2);

			/* VALIDAR DIA ABIERTO */

			$sql = "SELECT ch_poscd FROM pos_aprosys where da_fecha ='$dia';";

			if ($sqlca->query($sql) < 0) 
				return false;

			$res = $sqlca->fetchRow();

			if($res[0] == 'S'){
				$abierto = 'cerrado';
			}elseif($res[0] == 'A'){
				$abierto = 'abierto';	
			}

			/* VALIDAR TURNOS */

			$sql = "SELECT ch_posturno-1, da_fecha FROM pos_aprosys where da_fecha ='$dia';";

			if ($sqlca->query($sql) < 0) 
				return false;

			$res = $sqlca->fetchRow();

			if($_REQUEST['turno'] > $res[0]){
				$posturno = $res[0];
				$turno = 'turno';
			}
			/* VALIDAR QUE SEA MENOR O IGUAL QUE FECHA ACTUAL*/

			$sql = "SELECT da_fecha FROM pos_aprosys WHERE ch_poscd ='A';";

			if ($sqlca->query($sql) < 0) 
				return false;

			$res = $sqlca->fetchRow();
						
			if($dia <= $res[0])
				$valida = 'inserta';
			else
				$valida = 'no';

			/* HORA PERUANA */

			date_default_timezone_set("America/Lima" );

			$t       = microtime(true);
			$micro   = sprintf("%06d",($t - floor($t)) * 1000000);
			$hora    = date('H:i:s.'.$micro,$t);
			$created = date(Y."-".m."-".d)." ".$hora;
			
			/* VALIDAR SI EL DIA ESTA CONSOLIDADO */

			$sql = "SELECT count(*) FROM pos_consolidacion WHERE dia = '$dia'";

			if ($sqlca->query($sql) < 0) 
				return false;

			$a = $sqlca->fetchRow();

			if($a[0]>=1){
				$consolidacion = 1;
			}else{
				$consolidacion = 0;
			}
		
			if($consolidacion >= 1){
				?><script>alert("<?php echo 'La fecha ya esta consolidada';?> ");</script><?php
			}elseif($valida == 'no'){
				?><script>alert("<?php echo 'La fecha debe de ser menor a la fecha actual';?> ");</script><?php
			}elseif($abierto == 'abierto'){
				?><script>alert("<?php echo 'El dia '.$dia.' todavia sigue abierto' ;?> ");</script><?php
			}elseif($turno == 'turno'){
				?><script>alert("<?php echo 'El dia '.$dia.' solo tiene '.$posturno.' turnos';?> ");</script><?php
			}else{
				/* VALIDAR SI YA EXISTE */
				$shift = trim($_REQUEST['turno']);

				$sql = "SELECT count(*) FROM f_totalizerm WHERE warehouse = '$warehouse' AND systemdate = '$dia' AND shift = '$shift'";

				if ($sqlca->query($sql) < 0) 
					return false;

				$b = $sqlca->fetchRow();

				if($b[0]>=1){
					?><script>alert("<?php echo 'Ya existen contometros con fecha: '.$fecha.' y turno: '.$_REQUEST['turno'].' ';?> ");</script><?php
				}else{
					
					$i = 0;
					while ($i <= $insert){

					if(empty($_REQUEST['volume'][$i]))
						$_REQUEST['volume'][$i] = 0.00;

					$sql = "INSERT INTO f_totalizerm(
									created,
									createdby,
									updated,
									updatedby,
									isactive,
									f_grade_id,
									volume,
									amount,
									systemdate,
									shift,
									warehouse
							)VALUES(
									'".trim($created)."',
									'0',
									'".trim($created)."',
									'0',
									'1',
									'" . pg_escape_string(trim($_REQUEST['manguera'][$i]))."',		
									'" . pg_escape_string(trim($_REQUEST['volume'][$i]))."',
									0.00,
									'" . pg_escape_string(trim($dia))."',
									'" . pg_escape_string(trim($_REQUEST['turno']))."',
									'" . pg_escape_string(trim($warehouse))."'
							);";

					$xsql_cabecera = pg_query($conector_id, $sql);

					$i++;
		
					}

					?><script>alert("<?php echo 'Contometros con fecha: '.$dia.' y turno: '.$_REQUEST['turno'].' ingresados correctamente';?> ");</script><?php

					echo  "<script type='text/javascript'>";
					echo "window.close();";
					echo "</script>";
				}
			
			}

		break;
	}
?>
<?php		
	if(empty($id))	
		echo "
			<table align = 'center'>
				<tr bgcolor='FFFFFF'>
				<td colspan='2' align='center'>
					<button name='boton' type='submit' value='Guardar'><img src='/sistemaweb/icons/agregar.gif' align='right'/>Guardar</button>
				</td>
				<td></td>
				<td colspan='2' align='center'>
					<button name='action' type='submit' onclick='window.close();'><img src='/sistemaweb/icons/delete.gif' align='right'/>Cancelar</button>
				</td>
				</tr>
			</table>
		";

?>
</div>
</div>
</form>
<?php pg_close($conector_id); ?>

