<?php
//include("../valida_sess.php");
include("../menu_princ.php");
include("../functions.php");

require("../clases/funciones.php");
$funcion = new class_funciones;

// crea la clase para controlar errores
$clase_error = new OpensoftError;

// conectar con la base de datos
$conector_id=$funcion->conectar("","","","","");

if ( is_null($almacen) or trim($almacen)=="")
	{
	$almacen="001";
	}

if(strlen($v_fecha_desde)==0  ) 
	{
	$dia_actual=1;
	$mes=date("m");
	$ano=date("Y");
	$v_fecha_desde=date("Y-m")."-01";
	$ultimo_dia=ultimoDia($mes,$ano);
	$v_fecha_hasta=date("Y/m")."-".$ultimo_dia;
	$v_fecha_desde=date("d/m/Y");
	$v_fecha_hasta=date("d/m/Y");
	}

if($boton=="Mostrar")
	{
	// $tipo_cierre
	$v_fecha_dia= substr($v_fecha_desde,0,2) ;
	$v_fecha_mes= substr($v_fecha_desde,3,2) ;
	$v_fecha_ano= substr($v_fecha_desde,6,4) ;
        $texto= "ls -l /tmp/imprimir/cierre".$tipo_cierre.$v_fecha_dia.$v_fecha_mes.$v_fecha_ano."* > /tmp/imprimir/kuka";
        // carga las variables para mandar el reporte a impresion texto

	$v_sqlx ="select par_valor from int_parametros where trim(par_nombre)='print_netbios' ";
        $v_xsqlx=pg_exec( $v_sqlx);
        $v_server =pg_result($v_xsqlx,0,0);

        $v_sqlx ="select par_valor from int_parametros where trim(par_nombre)='print_name' ";
        $v_xsqlx=pg_exec($v_sqlx);
        $v_printer=pg_result($v_xsqlx,0,0);

        $v_sqlx ="select par_valor from int_parametros where trim(par_nombre)='print_server' ";
        $v_xsqlx=pg_exec($v_sqlx);
        $v_ipprint=pg_result($v_xsqlx,0,0);

$sql=" select trim(pc_samba), trim(prn_samba), trim(ip), trim(ch_sucursal) from pos_cfg where trim(tipo)='M' order by pos limit 1 ";

$xsqlpar = pg_exec($conector_id,$sql);

$v_server  = pg_result($xsqlpar,0,0);
$v_printer = pg_result($xsqlpar,0,1);
$v_ipprint = pg_result($xsqlpar,0,2);

        $v_archivo="/tmp/imprimir/";

	exec($texto);
	}


?>

<html><link rel="stylesheet" href="<?php echo $v_path_url; ?>sistemaweb.css" type="text/css">
<head> <title>sistemaweb</title>
<script language="JavaScript" src="/sistemaweb/clases/calendario.js"></script>
<script language="JavaScript" src="/sistemaweb/clases/overlib_mini.js"></script>
<script language="JavaScript" src="/sistemaweb/clases/reloj.js"></script>
<script language="JavaScript" src="/sistemaweb/compras/validacion.js"></script>
<script language="JavaScript" src="/sistemaweb/compras/valfecha.js"></script>
<script> 
function cargavalor(valor){
	// carga de frente el formulario con el foco en diad
	document.f_name.m_clave.value = valor;
	//document.f_name.submit();
}

</script> 
</head>

<body onfocus="mueveReloj('f_name.reloj'); activa()"> 
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<form name="f_name" action="" method="post">

IMPRIMIR CIERRES <BR>
<?php
$v_sql="select ch_nombre_almacen from inv_ta_almacenes  where ch_almacen like '%".$almacen."%' and ch_clase_almacen='1' ";
$v_xsql=pg_query($conector_id,$v_sql);
if(pg_numrows($v_xsql)>0)	{	$m_descalma=pg_result($v_xsql,0,0);	}
?>

ALMACEN ORIGEN <?php echo $almacen;?> 	<?php echo $m_descalma; ?> <input type="text" name="reloj" size="10" style="background-color : Black; color : White; font-family : Verdana, Arial, Helvetica; font-size : 8pt; text-align : center;" onfocus="window.document.f_name.reloj.blur()" > 

<input type="hidden" name="m_clave" size="16" maxlength="30" value='<?php echo $m_clave ; ?>'  tabindex="1"   >

<hr noshade>

	<table border="1">
	<tr> 
		<th colspan="5">INDICAR FECHA </th>
	</tr>
	<tr> 
		<th>DIA :</th>
		<th>
		<p>
		<input type="text" name="v_fecha_desde" size="16" maxlength="10" value='<?php echo $v_fecha_desde ; ?>'  tabindex="1"  onKeyUp="javascript:validarFecha(this)" onblur="javascript:validarFecha(this)"  >
		<a href="javascript:show_calendar('f_name.v_fecha_desde');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;" >
	    <img src="/sistemaweb/images/showcalendar.gif" border=0></a>
		</p>
		</th>
		
	</tr>
	<tr>
        	<th>Cierre:</th> 
		<th>
              	<select name="tipo_cierre">
		<option value='t'>Turno</option>
		<option value='d'>Dia</option>
              	</select>
            	</th>
	</tr>
	<tr>
	<th colspan=2>
	<input type="submit" name="boton" value="Mostrar">
	</th>
	</tr>
	</table>

<table border=1>
<?php
$nombre_fichero="/tmp/imprimir/kuka";
$handler = fopen($nombre_fichero,"r");
while (!feof($handler)) {
   $linea = fgets($handler, 4096);
   $pos1=0;

   echo "<tr>";
   $pos1=strpos($linea, "cierre" );
   $most=substr($linea,$pos1,22);
   echo "<td>".$most."</td>" ;
   ?>
   <td><a href="#" onClick="javascript:window.open('/sistemaweb/clases/imprime_samba.php?v_server=<?php echo $v_server; ?>&v_printer=<?php echo $v_printer; ?>&v_ipprint=<?php echo $v_ipprint; ?>&v_archivo=<?php echo $v_archivo.$most; ?>','miwin','width=800,height=450,scrollbars=yes,menubar=yes,left=0,top=0');"> Impresion Texto </a>
   </td>
   <?php
   echo "</tr>";
   
}

fclose($handler);

?>

</table>

</form>
</body>
</html>
<?php

// comprueba si la conexion existe y la cierra
if ($conector_id) pg_close($conector_id);

// restaura el control de errores original
//$clase_error->_error();

