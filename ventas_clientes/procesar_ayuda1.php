<?php
if($visualizar=="no")
{
	exit;
}

include("../valida_sess.php");
//include "store_procedures.php";
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
$ip_remote = $_SERVER['REMOTE_ADDR'];

switch($consulta)
{
	case articulos:
			if(substr(trim($cod),0,6)!='116203' && strlen(trim($cod))>0)
			{
				$cod = completarCeros(trim($cod),13,"0");
			}
			$rs = ayuda($consulta,$cod, "null" );

			if (pg_numrows($rs)>0) {
				$A = pg_fetch_array($rs,0);
				$desc1 = $A[1];
				pg_close();
			}
			else 	{
				$desc1='INEXISTENTE !!';
			}
		break;

	case ruc_cliente:
			$cod=trim($cod);
			$sql = "SELECT ruc, nombre FROM rucs
						WHERE trim(ruc)='$cod'";
			$xsql = pg_query($conector_id, $sql);
			//$rs = ayuda($consulta,$cod, "null" );
			if (pg_num_rows($xsql)>0) {
				$A = pg_fetch_array($xsql,0);
				$desc1 = $A[1];
				pg_close();
			}
			else 	{
				$desc1='';
			}
		break;


	case razon_social:
			$cod = strtoupper(trim($cod));

			$sql = "INSERT INTO ruc
							(ruc, razsocial) values
							('$des', '$cod')";
			$xsql = pg_query($conector_id, $sql);

			if(!$xsql)
			{
				echo "ACTUALIZANDO LA TABLA CON LA NUEVA RAZON SOCIAL";
				$sql = "
						UPDATE ruc set
							razsocial='$cod'
						WHERE trim(ruc)='$des'
						";
					pg_exec($conector_id, $sql);
			}
			//echo $sql;
			//$rs = ayuda($consulta,$cod, "null" );

			/*if (pg_num_rows($xsql)>0) {
				$A = pg_fetch_array($xsql,0);
				$desc1 = $A[1];
				pg_close();
			}
			else 	{
				$desc1=' ';
			}*/
		break;
}




?>

<SCRIPT LANGUAGE="JavaScript">
function timeclose()
{
	opener.document.<?php echo $des; ?>.value = '<?php echo $desc1; ?>';
	opener.document.formular.v_art_codigo.value = '<?php echo $cod; ?>';
}

function ayuda_defecto()
{
	opener.document.<?php echo $des; ?>.value = '<?php echo $desc1; ?>';
}


</SCRIPT>
</head>
<body bgcolor="#FFFFCD">
<?php

switch($consulta)
{
	case articulos:
		echo "Comprobando $consulta...!";
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

	case ruc_cliente:
		echo "Comprobando $consulta...!";
		if(strlen(trim($cod))>0)
		{
			echo '
			<script>
				ayuda_defecto();
				setTimeout("window.close()",500);
			</script>
			';
		}
		break;

	case razon_social:
		echo "Actualizando Razon Social";
		echo '
			<script>
				setTimeout("window.close()",500);
			</script>
			';
		break;
}

?>
</body>
