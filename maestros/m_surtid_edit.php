<?php
include("../valida_sess.php");

/*echo '<pre>';
var_dump($_REQUEST);
echo '</pre>';*/

$boton = $_POST['boton'];

if ($_POST) {
	$cod_surtidor_arr = $_POST['cod_surtidor_arr'];
	$arr_surti = $_POST['arr_surti'];
}

$cod_almacen = $_SESSION['usuario']->almacenActual;

//echo '<hr>count($cod_surtidor_arr): '.count($cod_surtidor_arr);

switch($boton){
	case "Agregar":
		/*echo '<hr><pre>';
		var_dump($_POST);
		echo '</pre><hr>';*/
		$cod_tanque = $_POST['cod_tanque'];
		$cod_surtidor = $_POST['cod_surtidor'];
		$num_lado = $_POST['num_lado'];
		$num_manguera = $_POST['num_manguera'];
		$ult_lecgalon = $_POST['ult_lecgalon'];
		$ult_lecvalor = $_POST['ult_lecvalor'];
		$query = "select ch_codigocombustible from comb_ta_tanques where trim(ch_tanque)=trim('$cod_tanque')
		and trim(ch_sucursal)=trim('$cod_almacen')";
		//echo 'query insert: :'.$query;
		$rs1 = pg_exec($query);
		$A[0]=pg_result($rs1,0,0);
		//echo $A[0];

		if(strlen(trim($ult_lecgalon))==0)
		{ $ult_lecgalon = "0"; }

		if(strlen(trim($ult_lecvalor))==0)
		{ $ult_lecvalor= "0"; }

		$q =
		"insert into comb_ta_surtidores(ch_surtidor,ch_codigocombustible,ch_tanque
		,nu_contometrogalon,nu_contomtrovalor, dt_fechacontometro
		,ch_sucursal,ch_numerolado,dt_fechactualizacion,ch_usuario, nu_manguera, ch_auditorpc)
		values( '$cod_surtidor', '$A[0]', '$cod_tanque', $ult_lecgalon,  $ult_lecvalor, 'now()'
		,'$cod_almacen', '$num_lado', 'now()','".$usuario->obtenerUsuario()."','$num_manguera','m_surtid_edit' )";

		//echo 'EXE INSERT: '.$q;
  		pg_exec($q);
		//header("Location: m_surtid.php?cod_almacen=$cod_almacen");
		?>
		<script type="text/javascript" charset="utf-8">
		alert('Agregando Registro.');
		window.location = "m_surtid.php?cod_almacen=<?php echo $cod_almacen ?>";
		</script>
		<?php
	break;

	case "Modificar":
		for($i = 0; $i < count($cod_surtidor_arr); $i++){
			$d1 = $cod_surtidor_arr[$i];

			for($a=0;$a<count($arr_surti[$d1]);$a++){
				$D[$a] = $arr_surti[$d1][$a];
			}

			/*echo '<hr>$d1';
			var_dump($d1);
			echo '<hr>$D';
			var_dump($D);
			echo '<hr>';*/


			$_sql = "SELECT
						comb.ch_codigocombustible,
						comb.nu_preciocombustible
					FROM
						comb_ta_combustibles comb,
						comb_ta_tanques t
					WHERE
						comb.ch_codigocombustible = t.ch_codigocombustible
						AND t.ch_sucursal = '$cod_almacen'
						AND t.ch_tanque = '$D[1]';";

			/*echo '<hr>';
			var_dump($_sql);*/
			$rs = pg_exec($_sql);

			$A = pg_fetch_row($rs,0);

			/*echo '<pre>';
			var_dump($A);
			echo '</pre>';*/

			$query = "
					UPDATE
						comb_ta_surtidores
					SET
						ch_tanque = '$D[1]',
						ch_codigocombustible = '$A[0]',
						nu_contomtrovalor = $D[4],
						nu_contometrogalon = $D[5],
						dt_fechactualizacion = 'now()',
						ch_numerolado = $D[7],
						nu_manguera = $D[8]
					WHERE
						ch_surtidor = '$d1'
						AND ch_sucursal = '$cod_almacen';
				";

			/*echo '<hr>';
			var_dump($query);*/
			
			/*echo "<pre>";
			print_r($query);
			echo "</pre>";*/

			pg_exec($query);

//			 echo "-------NUEVA FILA -------<br>";

		}

		pg_close();
		//header("Location: m_surtid.php?cod_almacen=$cod_almacen");
		?>
		<script type="text/javascript" charset="utf-8">
		alert('Modificando Registro(s)');
		window.location = "m_surtid.php?cod_almacen=<?php echo $cod_almacen ?>";
		</script>
		<?php
		break;

	case "Eliminar":
		//echo '<br>ELIMINAMOS :v<br>';
		for($i=0;$i<count($cod_surtidor_arr);$i++){
			$d1 = $cod_surtidor_arr[$i];
			$_sql = "delete from comb_ta_surtidores where ch_surtidor='$d1' and ch_sucursal='$cod_almacen';";
			//echo '<br>$_sql: '.$_sql;
			pg_exec($_sql);
		}
		pg_close();
		//header("Location: m_surtid.php");
		?>
		<script type="text/javascript" charset="utf-8">
		alert('Eliminando Registro(s)');
		window.location = "m_surtid.php";
		</script>
		<?php
		break;

	case "change_alma":
		pg_close();
		//header("Location: m_surtid.php?cod_almacen=$cod_almacen");
		?>
		<script type="text/javascript" charset="utf-8">
		alert('Modificando Registro(s)');
		window.location = "m_surtid.php?cod_almacen=<?php echo $cod_almacen ?>";
		</script>
		<?php
		break;
}

pg_close();
