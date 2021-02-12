<?php
if($boton=="Regresar"){
	    header("location: vtacli_vales.php?v_fecha_desde=".$_REQUEST['v_fecha_desde']."&v_fecha_hasta=".$_REQUEST['v_fecha_hasta']."&boton=Consultar");
	    exit ;
}
include("../menu_princ.php");
include("../include/functions.php");
require("../clases/funciones.php");
$funcion = new class_funciones;
$clase_error = new OpensoftError;

global $usuario;

$AlmacenPrincipal = $usuario->obtenerAlmacenActual();
$conector_id=$funcion->conectar("","","","","");

if(strlen($m_clave)>0 && $boton=="Complemento") {
	echo('<script languaje="JavaScript">');
	echo("	location.href='vtacli_vales_complemento.php?v_fecha_desde=".$_REQUEST['v_fecha_desde']."&v_fecha_hasta=".$_REQUEST['v_fecha_hasta']."&m_clave=".$m_clave."' " );
	echo('</script>');
}

$sql8 = "select DT_FECHA, CH_TURNO from VAL_TA_CABECERA where trim(CH_SUCURSAL||DT_FECHA||CH_DOCUMENTO)='".trim($m_clave)."'";
$xsql8 = pg_query($conector_id,$sql8);
$nfecha = trim(pg_result($xsql8,0,'DT_FECHA'));
$nturno = trim(pg_result($xsql8,0,'CH_TURNO'));

// verificando si fecha y turno fueron consolidados
/*$flag = 0;
$que = "select 1 from pos_consolidacion where dia='".$nfecha."' and turno = ".$nturno." ;" ;
$xque = pg_query($conector_id,$que);
$flag = trim(pg_result($xque,0));*/

$flag = 0;
$sql = " SELECT validar_consolidacion('$nfecha',$nturno,'$m_almacen') ";
$xque = pg_query($conector_id, $sql);
$flag = trim(pg_result($xque,0));


if(strlen(trim($v_art_codigo))>0 && strlen(trim($v_art_codigo))!=8){
	$v_art_codigo=completarCeros($v_art_codigo,13,"0");
}

// Vuelve al Programa de seleccion;
if($boton=="Modificar cabecera") {
	$sql3="SELECT ".
		    "ch_sucursal, ".
		    "dt_fecha, ".
		    "ch_cliente, ".
		    "ch_documento, ".
		    "ch_planilla, ".
		    "ch_placa, ".
		    "nu_odometro, ".
		    "ch_tarjeta, ".
		    "ch_turno, ".
		    "ch_caja, ".
		    "ch_lado, ".
		    "ch_glosa ".
	      "FROM val_ta_cabecera ".
	      "WHERE trim(ch_sucursal||dt_fecha||ch_documentO)='".trim($m_clave)."' " ;

	$xsql3=pg_query($conector_id,$sql3);
	$ilimit3=pg_numrows($xsql3);

	$cod_cliente=trim(pg_result($xsql3,0,'ch_cliente'));
	$cod_planilla=trim(pg_result($xsql3,0,'ch_planilla'));
	$nro_placa=trim(pg_result($xsql3,0,'ch_placa'));
	$odometro=trim(pg_result($xsql3,0,'nu_odometro'));
	$tarjeta=trim(pg_result($xsql3,0,'ch_tarjeta'));
	$chofer=trim(pg_result($xsql3,0,'ch_glosa'));
	$turno=trim(pg_result($xsql3,0,'ch_turno'));
	$cajaa=trim(pg_result($xsql3,0,'ch_caja'));
	$lado=trim(pg_result($xsql3,0,'ch_lado'));

	$sql3="select trim(ch_apellido_paterno)
			||' '||trim(ch_apellido_materno)
			||' '||trim(ch_nombre1)
			||' '||trim(ch_nombre2)
			as clave from pla_ta_trabajadores
			where ch_codigo_trabajador='".$cod_planilla."';" ;

	$xsql3=pg_query($conector_id,$sql3);
	$cod_descplanilla=trim(pg_result($xsql3,0));

	$v_modificar_cabecera="Grabar cabecera";
	$v_estado_cabecera=" ";
	$boton=" ";
	}
else
	{
	if ($boton=="Grabar cabecera" and strlen($m_cantidadpedida)>0 and strlen($m_precio)>0)
		{

		if(strlen($cod_cliente)>0)
			{
			$sqlprov="select cli_codigo,cli_razsocial
						from INT_clientes
						where cli_codigo='".$cod_cliente."' ";
			$xsqlprov=pg_query($conector_id,$sqlprov);
		   if(pg_numrows($xsqlprov)>0)
				{
				$cod_cliente=pg_result($xsqlprov,0,0);
				$desc_cliente=pg_result($xsqlprov,0,1);
				}
			}
		$cod_cliente=trim($cod_cliente);
		$cod_planilla=trim($cod_planilla);
		$nro_placa=trim($nro_placa);
		$odometro=trim($odometro);
		$tarjeta=trim($tarjeta);
		$chofer=trim($chofer);
		$turno=trim($turno);
		$cajaa=trim($cajaa);
		$lado=trim($lado);

		if($flag == 1){
				?><script>alert("<?php echo 'No se pueden modificar datos ya consolidados!' ; ?> ");</script><?php
		}else{
				$v_sql="UPDATE val_ta_cabecera SET ".
							       "ch_cliente='$cod_cliente', ".
							       "ch_planilla='$cod_planilla', ".
							       "ch_placa='$nro_placa', ".
							       "nu_odometro=".$odometro.", ".
							       "ch_tarjeta='$tarjeta', ".
							       "ch_turno='$turno', ".
							       "ch_caja='$cajaa', ".
							       "ch_lado='$lado', ".
							       "ch_estado='1', ".
							       "ch_glosa='$chofer' ".
				       "WHERE trim(ch_sucursal||dt_fecha||ch_documento)='".trim($m_clave)."'";
				//$SentenciasReplicacion = SentenciasReplicacion($conector_repli_id, $v_sql, $Datos);
				$v_xsql=pg_query($conector_id, $v_sql);
				$boton=" ";
				$v_modificar_cabecera="Modificar cabecera";
				$v_estado_cabecera="disabled";
		}  
	}
	if (is_null($v_modificar_cabecera))
		{
		$v_modificar_cabecera="Modificar cabecera";
		$v_estado_cabecera="disabled";
		}
	}

if($boton=="Eliminar")
	{
	$sqleli="DELETE FROM val_ta_detalle ".
		"WHERE trim(ch_sucursal||dt_fecha||ch_documento||ch_articulo)='".trim($m_clavedet)."' ";
	//echo "sql eliminar".$sqleli;
	//$SentenciasReplicacion = SentenciasReplicacion($conector_repli_id, $sqleli, $Datos);
	$xsqleli=pg_query($conector_id,$sqleli);
	$boton=" ";
	}

if($boton=="Ins" or $boton=="Agregar")
	{
	$m_almacen	= trim($m_almacen);
	$fecha_val	= $funcion->date_format($fecha_val,'YYYY-MM-DD');

	$cod_cliente	= trim($cod_cliente);
	$nro_vale	= trim($nro_vale);

	$v_art_codigo	= trim($v_art_codigo);
	$m_cantidadpedida=trim($m_cantidadpedida);
	$m_precio	= trim($m_precio);

	if(($m_cantidadpedida*$m_precio)<=0 or $preciouni<0)
	{
		$v_mensaje=" No se puede Agregar \\n Cantidad, Importe y/o Precio en Cero !!! ";	$okgraba=false;
	}
	else
	{
		$okgraba=true;
	}
	if($flag == 1){ 
		$v_mensaje=" No se puede Agregar \\n Datos ya consolidados !!! ";	$okgraba=false;
	}
	if($okgraba)
	{
		$sql="INSERT INTO val_ta_detalle (
		                                 ch_sucursal, 
		                                 dt_fecha, 
		                                 ch_documento, 
		                                 ch_articulo, 
		                                 nu_cantidad, 
		                                 nu_importe, 
		                                 ch_estado, 
					         nu_factor_igv,
					         nu_precio_unitario  
		                                 ) 
		      VALUES ( 
		              '$m_almacen', 
		              '$fecha_val', 
		              '$nro_vale', 
		              '$v_art_codigo', 
		              '$m_cantidadpedida', 
		              '$m_precio', 
		              1, 
			      util_fn_igv_porarticulo('$v_art_codigo'),
			      $preciouni  
		              )";
		//echo "<script language='javascript'> alert '$sql';</script>";
		//$SentenciasReplicacion = SentenciasReplicacion($conector_repli_id, $sql, $Datos);
		$xsql=pg_query( $conector_id, $sql );

		$m_clave=$m_almacen.$fecha_val.$nro_vale;
		$boton=" ";
		echo("<script>");
		echo("	location.href='vtacli_vales_modif.php?m_clave=".trim($m_clave)."'" );
		echo("</script>");
		}
		else
		{
			echo('<script languaje="JavaScript"> ');
			echo('alert("'.$v_mensaje.'"); ');
			echo('</script>');
		}
	}
if ($boton == "Modificar") {
    $cantidades = $_REQUEST['cantidades'];
    $precios = $_REQUEST['precios'];
    $claves = $_REQUEST['claves'];
 
	if($flag == 1){
		echo('<script languaje="JavaScript"> ');
		echo('alert("No se pueden actualizar datos ya consolidados!"); ');
		echo('</script>');
	}else{
    
		for ($i = 0; $i < count($cantidades); $i++) {
			$sql = "UPDATE 
				val_ta_detalle 
			  SET 
				nu_cantidad='" . pg_escape_string($cantidades[$i]) . "', 
				nu_importe='" . pg_escape_string($precios[$i]) . "', 
				nu_precio_unitario='" . pg_escape_string($preciounis[$i]) . "', 
				flg_replicacion=0 
			  WHERE 
				   trim(ch_sucursal||dt_fecha||ch_documento||ch_articulo)='" . pg_escape_string($claves[$i]) . "'";
			//echo "<!--QUERY :$sql \n -->"; $caja=$_POST["caja"];
			//$SentenciasReplicacion = SentenciasReplicacion($conector_repli_id, $sql, $Datos);
			$rs = pg_query($conector_id, $sql);
			if (!$rs) echo "Actualizacion fallida.";
		}   
      	}	    
}

?>
<html><head>
<script language="JavaScript" src="js/miguel.js"></script>
<script language="JavaScript" src="/sistemaweb/clases/calendario.js"></script>
<script language="JavaScript" src="/sistemaweb/clases/overlib_mini.js"></script>
<script language="JavaScript" src="/sistemaweb/compras/validacion.js"></script>

<script language="javascript">
var miPopup
function abrealma(){
	miPopup = window.open("../maestros/escogealmacen.php?k_variable=formular.m_almacen","miwin","width=500,height=400,scrollbars=yes")
	miPopup.focus()
	}

function abrecliente() {
	miPopup = window.open("../maestros/escogecliente.php?k_variable=formular.cod_cliente","miwin","width=500,height=400,scrollbars=yes")
	miPopup.focus()
	}


function abrearti() {
	miPopup = window.open("../maestros/escogearticulo.php?k_variable=formular.v_art_codigo","miwin","width=550,height=400,scrollbars=yes")
	miPopup.focus()
	}

function abretabla( tabla ,k_var ){
	miPopup = window.open("../maestros/escogetabla.php?m_tabla="+tabla+"&k_variable="+k_var+" ","miwin","width=600,height=350,scrollbars=yes")
	miPopup.focus()
	}

function enviadatos(){
	document.formular.submit()
	}

function enviapago(){
	document.formular.m_formapago.value=" ";
	}


function verifica_arti(arti){
	var vr1 = new Array();
	var valor = arti.value;
	var existe = false;

	for(i=0;i<vr1.length;i++){
		if( vr1[i]== valor){  existe = true;   }
		}
	if (!existe) {
		alert('El c�igo de articulo no existe ');
		}
	}

</script>
</head>
<body>
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<form name="formular" action="vtacli_vales_modif.php?m_clave=<?php echo trim($m_clave);?>" method="post">
<?php

//echo "ESTA ES LA CLAVE ".$m_clave;
	
$sql3="select 		  CH_SUCURSAL,
			  DT_FECHA,
			  CH_CLIENTE,
			  CH_DOCUMENTO,
			  CH_PLANILLA,
			  CH_PLACA,
			  NU_ODOMETRO,
			  CH_TARJETA,
			  CH_TURNO,
			  CH_CAJA,
			  CH_LADO,
			  CH_GLOSA
			  from VAL_TA_CABECERA
			  where trim(CH_SUCURSAL||DT_FECHA||CH_DOCUMENTO)='".trim($m_clave)."'";

$xsql3=pg_query($conector_id,$sql3);
$ilimit3=pg_numrows($xsql3);

$m_almacen=pg_result($xsql3,0,'CH_SUCURSAL');
$fecha_val=$funcion->date_format(pg_result($xsql3,0,'DT_FECHA'),'DD/MM/YYYY');

$nro_vale=pg_result($xsql3,0,'CH_DOCUMENTO');
$cod_planilla=pg_result($xsql3,0,'CH_PLANILLA');
$nro_placa=pg_result($xsql3,0,'CH_PLACA');
$odometro=pg_result($xsql3,0,'NU_ODOMETRO');
$tarjeta=pg_result($xsql3,0,'CH_TARJETA');
$chofer=pg_result($xsql3,0,'CH_GLOSA');
$turno=trim(pg_result($xsql3,0,'CH_TURNO'));
$cajaa=trim(pg_result($xsql3,0,'CH_CAJA'));
$lado=trim(pg_result($xsql3,0,'CH_LADO'));

if ($v_estado_cabecera=="disabled")
	{
	$cod_cliente=pg_result($xsql3,0,'CH_CLIENTE');

	$sql3="select trim(ch_apellido_paterno)
			||' '||trim(ch_apellido_materno)
			||' '||trim(ch_nombre1)
			||' '||trim(ch_nombre2)
			as clave from pla_ta_trabajadores
			where ch_codigo_trabajador='".$cod_planilla."';" ;

	$xsql3=pg_query($conector_id,$sql3);
	$cod_descplanilla=trim(pg_result($xsql3,0));
	}


$m_cantidadpedida=1;
$m_precio=0;

?>

<input type="hidden" name="fecha_val" value='<?php echo $fecha_val;?>'>
<input type="hidden" name="m_almacen" value='<?php echo $m_almacen;?>'>
<input type="hidden" name="nro_vale" value='<?php echo $nro_vale;?>'>
<input type="hidden" name="v_fecha_desde" value='<?php echo $_REQUEST['v_fecha_desde'];?>'>
<input type="hidden" name="v_fecha_hasta" value='<?php echo $_REQUEST['v_fecha_hasta'];?>'>

<input type="hidden" name="v_modificar_cabecera" value='<?php echo $v_modificar_cabecera;?>'>
<input type="hidden" name="v_estado_cabecera" value='<?php echo $v_estado_cabecera;?>'>

<table border="0" >
	<tr><th width="500">VALES DE CREDITO</th></tr>
</table>

<table border="0" >
	<tr>
		<th>ALMACEN</th>
		<td>:</td>
		<td>&nbsp;<?php echo $m_almacen;?>
		<?php
		if( strlen($m_almacen)>0 )
			{
			// $sqlalma="select TAB_ELEMENTO, TAB_DESCRIPCION from INT_TABLA_GENERAL where TAB_TABLA='ALMA' and TAB_ELEMENTO like '%".$m_almacen."%' ";
			$sqlalma="select trim(ch_sucursal) as cod, ch_nombre_almacen
					  from inv_ta_almacenes
					  where ch_almacen like '%".$m_almacen."%' and ch_clase_almacen='1' ";

			$xsqlalma=pg_query($conector_id,$sqlalma);
			if(pg_numrows($xsqlalma)>0)
				{
				$m_descalma=pg_result($xsqlalma,0,1);
				echo $m_descalma;
				}
			}
		?>
		</td>
	<tr>
	<th>FECHA</th>
		<td>:</td>
		<td>
		<p>
		<input type="hidden" name="fecha_val" size="16" value="<?php echo $fecha_val; ?>">
		<?php echo $fecha_val; ?>
		</p>
		</td>
	<tr>
		<th>NUMERO VALE</th>
		<td>:</td>
		<td><?php echo $nro_vale; ?><input name="nro_vale" type="hidden" maxlength="10" value="<?php echo $nro_vale;?>">
		</td>
	<tr>

		<th>CLIENTE</th>
		<td>:</td>
		<td>&nbsp;
		<?php		
			{
			$sqlprov="select CLI_RAZSOCIAL from INT_CLIENTES where CLI_CODIGO='".$cod_cliente."'";
			$xsqlprov=pg_query($conector_id,$sqlprov);
			if(pg_numrows($xsqlprov)>0)
				{
				$desc_cliente=pg_result($xsqlprov,0,0);
				}
   			else
   				{
				echo('<script languaje="JavaScript"> ');
				echo('alert(" No Existe Cliente !!! "); ');
   				echo('</script>');
			   	}
			}
		?>

		<input name="cod_cliente" type="text" size="15" maxlength="12" value="<?php echo $cod_cliente;?>" <?php echo $v_estado_cabecera; ?>>
	<!--	<img src="../images/help.gif" width="16" height="15" onClick="javascript:mostrarAyuda('lista_ayuda.php','formular.cod_cliente','formular.cod_cliente','clientes')" <?php echo $v_estado_cabecera; ?>>   -->
		<input name="imgprov" type="image" src="/sistemaweb/images/help.gif" width="16" height="15" border="0" onClick="javascript:mostrarAyuda('lista_ayuda.php','formular.cod_cliente','formular.desc_cliente','clientes')" <?php echo $v_estado_cabecera; ?>>
		<input readonly size='30' type="text" name="desc_cliente" value="<?php echo trim($desc_cliente); ?>" <?php echo $v_estado_cabecera; ?>> </td>

		<th>NRO_PLACA</th>
		<td>:</td>
		<td><input name="nro_placa" type="text" value="<?php echo trim($nro_placa);?>" size="16" maxlength="10" <?php echo $v_estado_cabecera; ?> >
		</td>

		<tr>
		<th>TRABAJADOR</th>
		<td>:</td><td>
		<input type="text" name="cod_planilla"  value='<?php echo $cod_planilla; ?>' maxlength="10" <?php echo $v_estado_cabecera; ?>>
		<img src="../images/help.gif" width="16" height="15" onClick="javascript:mostrarAyuda('lista_ayuda.php','formular.cod_planilla','formular.cod_descplanilla','trabajadores')" <?php echo $v_estado_cabecera; ?>>
		<input type="text" name="cod_descplanilla" size="35" readonly="true" value='<?php echo $cod_descplanilla; ?>' <?php echo $v_estado_cabecera; ?>>

		<th>ODOMETRO</th>
		<td>:</td>
		<td><input name="odometro" type="text" value="<?php echo $odometro;?>" size="16" maxlength="10" onkeyup='validarNumeroDecimales(this)' <?php echo $v_estado_cabecera; ?>>
		</td>
		<tr>

		<th>TARJETA</th>
		<td>:</td>
		<td><input name="tarjeta" type="text" value="<?php echo trim($tarjeta);?>" size="16" maxlength="10" <?php echo $v_estado_cabecera; ?> >
		</td>

		<th>CHOFER</th>
		<td>:</td>
		<td><input name="chofer" type="text" value="<?php echo trim($chofer);?>" size="20" maxlength="20" <?php echo $v_estado_cabecera; ?> >
		</td>

		<tr>	
		</table>

		<table border="0" cellpadding="0" cellspacing="8" style="padding-left:22px">
		<tr>			

	<td>
		<th>TURNO</th>
		<td>:</td>
		<td><input type="text" name="turno" maxlength="2" size="5" value="<?php echo $turno;?>" <?php echo $v_estado_cabecera; ?> ></td>	
	</td>

	<td>
		<th>CAJA</th>
		<td>:</td>
		<td><input type="text" name="cajaa" maxlength="3" size="5" value="<?php echo $cajaa;?>" <?php echo $v_estado_cabecera; ?> ></td>
	</td>
		
	<td>		
		<th>LADO</th>
		<td>:</td>
		<td><input type="text" name="lado" maxlength="3" size="5" value="<?php echo $lado;?>" <?php echo $v_estado_cabecera; ?> ></td>
	</td>
	</tr>
</table>

<table border="1" cellpadding="0" cellspacing="0" >
	<tr>
		<th>&nbsp;</th>
		<th>ARTICULO</th>
		<th>DESCRIPCION</th>
		<th>CANTIDAD</th>
		<th>IMPORTE</th>
		<th>PRECIO</th>
		<th>&nbsp;</th>
	</tr>
	<tr>
		<th>&nbsp;</th>
		<?php

		if(strlen($v_art_codigo)>0)
		{
			$v_xsql=pg_query($conector_id,"select ART_CODIGO, ART_DESCRIPCION from INT_ARTICULOS where ART_CODIGO='".$v_art_codigo."' ");
			if(pg_numrows($v_xsql)>0)
			{
				$rs = pg_fetch_array($v_xsql,0);
				$v_art_codigo=$rs[0];
				$v_art_descripcion=$rs[1];
			}
			else
			{
				echo('<script languaje="JavaScript"> ');
				echo('alert(" No Existe Articulo !!! "); ');
				echo('</script>');
			}
		}
		?>

		<th><input type="text" name="v_art_codigo" size='19' maxlength="13" value="<?php echo $v_art_codigo;?>"   onblur='submit()' onkeyup='validarNumeroEntero(this)' >
		<input name="imgarti" type="image" src="/sistemaweb/images/help.gif" width="16" height="15" border="0" onClick="javascript:mostrarAyuda('lista_ayuda.php','formular.v_art_codigo','formular.v_art_descripcion','articulos')"></th>
		<td><input size='20' type="text" name="v_art_descripcion" value="<?php echo $v_art_descripcion; ?>"></td>

		<th><input name="m_cantidadpedida" type="text" size='15'  maxlength="15" value="<?php echo $m_cantidadpedida;?>" onkeyup='validarNumeroDecimales(this)' ></th>

		<th><input name="m_precio" type="text" size='15' maxlength="15" value="<?php echo $m_precio;?>" onkeyup='validarNumeroDecimales(this)' ></th>
		<th><input name="preciouni" type="text" size='15' maxlength="15" value="<?php echo $preciouni;?>" onkeyup='validarNumeroDecimales(this)' ></th>
		<th><input type="submit" name="boton" value="Agregar" ></th>
	</tr>
    <?php

	$sql3 = "SELECT 
	              trim(det.ch_sucursal||det.dt_fecha||det.ch_documento||det.ch_articulo), 
		      det.ch_articulo, 
		      art.art_descripcion, 
		      det.nu_cantidad, 
		      det.nu_importe, 
		      det.flg_replicacion,
		      det.nu_precio_unitario   
	      FROM val_ta_detalle det, int_articulos art 
	      WHERE det.ch_articulo=art.art_codigo 
	      AND trim(det.ch_sucursal||det.dt_fecha||det.ch_documento)='".trim($m_clave)."' " ;
        //echo '***** QUERY 3 :'.$sql3.' *****';
	$xsql3=pg_query($conector_id,$sql3);
	$ilimit3=pg_numrows($xsql3);
	while($irow3<$ilimit3)
	{
	    $ad0=pg_result($xsql3,$irow3,0);
	    $ad1=pg_result($xsql3,$irow3,1);
	    $ad2=pg_result($xsql3,$irow3,2);
	    $ad3=pg_result($xsql3,$irow3,3);
	    $ad4=pg_result($xsql3,$irow3,4);
	    $ad5=pg_result($xsql3,$irow3,5);
	    $ad6=pg_result($xsql3,$irow3,6);

	    echo "<tr>";
	    echo "<td><input type='radio' name='m_clavedet' value='".$ad0."'></td>";
	    echo "<td>".$ad1."</td>";
	    echo "<td>".$ad2."</td>";

	    //echo "almacen : " . $_SESSION['almacenActual'];
	    if ($ad5 == 0 || $usuario->almacenActual == '001') {
		echo "<td><p align='right'><input type='text' name='cantidades[]' value='".$ad3."'><input type='hidden' name='claves[]' value='" . $ad0 . "'></p></td>";
		echo "<td><p align='right'><input type='text' name='precios[]' value='".$ad4."'></p></td>";
		echo "<td><p align='right'><input type='text' name='preciounis[]' value='".$ad6."'></p></td>";
		echo "<td>&nbsp;</td>";
	    }
	    else {
		echo "<td><p align='right'>$ad3</p></td>";
		echo "<td><p align='right'>$ad4</p></td>";
		echo "<td><p align='right'>$ad6</p></td>";
		echo "<td>&nbsp;</td>";
	    }

	    echo "</tr>";
	    $irow3++;
	}

	?>
	<tr>
		<td>&nbsp;</td>
		<td><input type="submit" name="boton" value="Eliminar"></td>
		<td><input type="submit" name="boton" value="<?php echo $v_modificar_cabecera; ?>" >
		&nbsp;&nbsp;
		<input type="submit" name="boton" value="Regresar"></td>
		<td><input type="submit" name="boton" value="Modificar"></td>
		<td><input type="submit" name="boton" value="Complemento"></td>
		<td colspan="2">&nbsp;</td>
	</tr>
</table>
</form>
</body>
</html>

<?php
// comprueba si la conexion existe y la cierra
if ($conector_id) pg_close($conector_id);
if ($conector_repli_id) pg_close($conector_repli_id);
// restaura el control de errores original
$clase_error->_error();
