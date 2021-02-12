<?php
if($visualizar=="no")
{
	exit;
}

include("../valida_sess.php");
include "functions.php";
include("config.php");
include("../functions.php");
require("../clases/funciones.php");

$funcion = new class_funciones;
// crea la clase para controlar errores

$clase_error = new OpensoftError;
$clase_error->_error();

// conectar con la base de datos
$conector_id=$funcion->conectar("","","","","");


//$rs = pg_exec( "select ch_codigo_trabajador, trim(CH_APELLIDO_PATERNO)||' '||trim(CH_APELLIDO_MATERNO)||' '||trim(CH_NOMBRE1)||' '||trim(CH_NOMBRE2) from pla_ta_trabajadores where ch_codigo_trabajador='".$cod."'") ;

/*$sql = "SELECT
			trim(ch_ser_nombre_cliente)
		FROM ser_ta_clientes_servicios
		WHERE trim(ch_ser_codigo_cliente)='$cod'";
$xsql = pg_query($cod);
//$rs = ayuda($consulta,$cod, "null" );
if (pg_num_rows($xsql)>0){
	$desc1 = pg_result($xsql,0,0);
	pg_close();
}
else
{
	$desc1=' NO REGISTRADO ';
}*/
echo '<head>
<script language="javascript">
function ventanaSecundaria (URL)
{
	window.open(URL,"ventana1","width=550, height=450, scrollbars=no, menubar=no,left=100,top=50")
}
</script>
';

switch($consulta)
{
	case articulos:
		if(substr(trim($cod),0,6)!='116203' && strlen(trim($cod))>0)
		{
			$cod = completarCeros(trim($cod),13,"0");
		}
		$rs = ayuda($consulta,$cod, "null" );

		if (pg_numrows($rs)>0){
			$A = pg_fetch_array($rs,0);
			$desc1 = $A[1];
			$precio = pg_result(pg_query($conector_id, "select round(util_fn_precio_articulo('$cod'),2)"),0,0);
			pg_close();
		}
		else
		{
			$desc1=' INEXISTENTE !!';
		}
		break;

	case clientes:
		//$cod = completarCeros(trim($cod),13,"0");
		$cod = strtoupper($cod);
		$rs = ayuda($consulta,$cod, "null" );

		if (pg_numrows($rs)>0){
			$A = pg_fetch_array($rs,0);
			$desc1 = $A[1];
			pg_close();
		}
		else {
			$desc1=' CLIENTE NO REGISTRADO !! ';
		}
		break;

	case placa:
		$nro_placa = strtoupper($cod);
		$cod_cliente = trim($adicional);

		if(strlen($nro_placa)>0)
		{
			$query="select ch_ser_codigo_cliente, ch_ser_tipo_vehiculo from ser_ta_vehiculosxclientes where ch_ser_placa_vehiculo='".strtoupper($nro_placa)."'";
			$xsql=pg_query($conector_id,$query);

			$query = "select par_valor from int_parametros where par_nombre='codes'";
			$cod_est=trim(pg_result(pg_query($conector_id,$query),0,0));

			if(pg_num_rows($xsql)>0)
			{
				$cod_cliente=pg_result($xsql,0,0);

				$query = "select GNRL.TAB_DESCRIPCION
				FROM SER_TA_VEHICULOSXCLIENTES VXC, INT_TABLA_GENERAL GNRL
				WHERE GNRL.TAB_TABLA='71' AND GNRL.TAB_ELEMENTO=VXC.CH_SER_TIPO_VEHICULO
				AND VXC.CH_SER_PLACA_VEHICULO='".$nro_placa."'";
				$xsql = pg_query($conector_id, $query);
				if(pg_num_rows($xsql)>0)
				{
					$v_tipoVehiculo = pg_result($xsql, 0 ,0);
				}

				//AQUI BUSCA LOS DATOS DEL CLIENTE
				$query="SELECT ch_ser_nombre_cliente, ch_ser_direccion, ch_ser_ruc, ch_ser_e_mail
						FROM ser_ta_clientes_servicios
						WHERE ch_ser_codigo_cliente='".trim($cod_cliente)."'";
				//echo $query;
				$xsql = pg_query($conector_id, $query);
				$desc_cliente = pg_result($xsql, 0, 0);
				$v_direccion = pg_result($xsql,0, 1);
				$v_ruc = pg_result($xsql,0,2);
				$v_email = pg_result($xsql,0,3);
				//echo "YA LLEGO HASTA QUI!! EL CLIENTE Y SU PLCAa";
			}
			else if($cod_est==trim($cod_cliente))
			{
				//$v_mensaje=" No se puede Agregar \\n Placa No Registrada !!! ";	$okgraba=false;
				/*echo('<script languaje="JavaScript">');
				echo('alert("ahora creo el Vehiculo por defecto al cliente");');
				echo('</script>');
				*/
				$sql="insert into SER_TA_VEHICULOSXCLIENTES
				(CH_SER_PLACA_VEHICULO, CH_SER_CODIGO_CLIENTE, CH_SER_TIPO_VEHICULO
				, CH_SER_MARCA_VEHICULO, CH_SER_MODELO_VEHICULO, CH_SER_PERIODO_VEHICULO
				, DT_FECHA_UPD, CH_USUARIO_UPD) values
				('$nro_placa','$cod_cliente', '000001'
				, '000009','000002','000001'
				, now(), '$usuario')";

				//echo $sql;
				//sleep(10);
				pg_exec($conector_id, $sql);
				$recargar = "ok";


			}

			else if(strlen($cod_cliente)>0)
			{
				//$v_mensaje=" No se puede Agregar \\n Placa No Registrada !!! ";	$okgraba=false;

				echo('<script languaje="JavaScript">');
				echo('ventanaSecundaria("new_cliente.php?new_placa='.$nro_placa.'&new_identidad='.$cod_cliente.'");');
				echo('</script>');
			}
		}
		else
		{
			$cod_cliente="";
			$v_tipoVehiculo="";
			$desc_cliente = "";
			$v_direccion = "";
			$v_ruc = "";
			$v_email = "";
	//		$v_mensaje=" No se puede Agregar \\n Placa Vacia !!! ";	$okgraba=false;
		}
		break;
	default:
		$rs = ayuda($consulta,$cod, "null" );
		if (pg_numrows($rs)>0){
			$A = pg_fetch_array($rs,0);
			$desc1 = $A[1];
			pg_close();
		}
		else
		{
			$desc1=' INEXISTENTE !!';
		}
		break;
}




?>

<SCRIPT LANGUAGE="JavaScript">
function timeclose()
{
	opener.document.<?php echo $des; ?>.value = '<?php echo $desc1; ?>';
	opener.document.formular.m_precio.value = '<?php echo $precio; ?>';
	opener.document.formular.v_art_codigo.value = '<?php echo $cod; ?>';
}
function ayuda()
{
	opener.document.formular.new_cliente.value = '<?php echo $cod; ?>';
	opener.document.<?php echo $des; ?>.value = '<?php echo $desc1; ?>';
}
function general()
{
	opener.document.<?php echo $des; ?>.value = '<?php echo $desc1; ?>';
}

function comprobarPlaca()
{
	opener.document.formular.nro_placa.value = '<?php echo $nro_placa; ?>';
	//parent.document.formular.cod_cliente.value = '<?php echo $cod_cliente; ?>';
	opener.document.formular.cod_cliente.value = '<?php echo $cod_cliente; ?>';
	opener.document.formular.v_tipoVehiculo.value = '<?php echo $v_tipoVehiculo; ?>';
	opener.document.formular.desc_cliente.value = '<?php echo $desc_cliente; ?>';
	opener.document.formular.v_email.value = '<?php echo $v_email; ?>';
	opener.document.formular.v_direccion.value = '<?php echo $v_direccion; ?>';
	opener.document.formular.v_ruc.value = '<?php echo $v_ruc; ?>';
}

</SCRIPT>
</head>
<body bgcolor="#FFFFCD">
<?php

switch($consulta)
{
	case clientes:
		echo "Comprobando Servicio...!";
		echo '
			<script>
				ayuda();
				setTimeout("window.close()",500);
			</script>
			';
		break;


	case articulos:
		echo "Comprobando Servicio...!";
		if(strlen(trim($cod))>0)
		{
		echo '
			<script>
				timeclose();
				setTimeout("window.close()",500);
			</script>
			';
		}
		break;

	case placa:
		echo "Comprobando Placa...!";
		echo '
			<script>
				comprobarPlaca();';

		if($recargar=="ok")
		{
			echo 'opener.document.formular.submit();';
		}
		echo 'setTimeout("window.close()",500);';

/*		if($recargar=="ok")
		{
   echo 'opener.document.formular.v_art_codigo.focus()';
		}
*/
		echo '
			</script>
			';
		break;

	default:
		echo "AYUDA PARA TODOS LAS AYUDA";
		echo '<script>
				general();
				setTimeout("window.close()",500);
			  </script>
			';
		break;
}
?>
</body>
