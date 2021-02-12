<?php
//include("../valida_sess.php");
include("../menu_princ.php");
include("../functions.php");

//require("../clases/funciones.php");
$funcion = new class_funciones;

// crea la clase para controlar errores
$clase_error = new OpensoftError;

// conectar con la base de datos
$conector_id=$funcion->conectar("","","","","");

if ( is_null($almacen) or trim($almacen)=="")
	{
	$almacen="001";
	}
	
	
// carga los almacenes en un dropdown 
// $v_xsqlalma=pg_exec("select trim(tab_elemento) as cod,tab_descripcion from int_tabla_general  where tab_tabla='ALMA' and tab_car_02='1' order by cod");
$v_xsqlalma=pg_exec("select trim(ch_almacen) as cod,ch_nombre_almacen from inv_ta_almacenes  where ch_clase_almacen='1' order by cod");
$k_var = pg_fetch_row($v_xsqlalma,0);
$k_almacen=trim($k_var[0]);
$k_almacen_desc=$k_var[1];

if($boton=="Ins" or $boton=="Agregar" ) 
	{
//	carga el ultimo numero orden de compra en que se quedo la tabla de numeradores de documentos
//	echo $v_tipdocumento;
//	echo $v_seriedocumento;
	$v_sql="select int_sp_numero_documento_ins( '$v_tipdocumento', '$v_seriedocumento' )";
	$v_xsql=pg_query( $conector_id,  $v_sql );
	$v_numrequerimiento=pg_result($v_xsql, 0, 0 );
//	echo $v_numrequerimiento;
	$v_tipdocumento=trim($v_tipdocumento);
	$v_seriedocumento=trim($v_seriedocumento);
	
	if(strlen($v_art_codigo)>0) 
		{
		$v_xsql=pg_query($conector_id,"select ART_CODIGO, ART_DESCRIPCION from INT_ARTICULOS where ART_CODIGO='".$v_art_codigo."' ");
		if(pg_numrows($v_xsql)==0) 
			{
			echo('<script languaje="JavaScript"> ');
			echo('alert(" No Existe Articulo !!! "); ');
			echo('</script>');
			}
		}
	else {
		echo('<script languaje="JavaScript"> ');
		echo('alert(" Ingrese Articulo !!! "); ');
		echo('</script>');
		}

	if($v_cantidad_requerida<=0) 
		{
		echo('<script languaje="JavaScript"> ');
		echo('alert(" Cantidad en Cero o Negativo !!! "); ');
		echo('</script>');
		$v_art_codigo='';
		}

	if (strlen($v_fecha_requerida) > 0) 
		{ 
		$v_fecha_requerida=$funcion->date_format($v_fecha_requerida,'YYYY-MM-DD');
		$v_ins_fecha="DT_REQ_FECHA_REQUERIDA,"; 
		$v_val_fecha="'$v_fecha_requerida',"; 
		} 
	else 
		{ 
		$v_fecha_requerida=" "; 
		$v_ins_fecha=" "; 
		$v_val_fecha=" "; 
		}

	$v_sql="insert into COM_TA_REQUERIMIENTOS ( 
					NUM_TIPDOCUMENTO, 
					NUM_SERIEDOCUMENTO, 
					CH_REQ_NUMREQUERIMIENTO, 
					CH_REQ_ALMACEN,
					".$v_ins_fecha."
					ART_CODIGO,
					NU_REQ_CANTIDAD_REQUERIDA,
					NU_REQ_CANTIDAD_ATENDIDA,
					NU_REQ_VENTA_FECHA,
					NU_REQ_VENTA_MES_ACTUAL,
					NU_REQ_VENTA_MES_ANTERIOR,
					NU_REQ_CANTIDAD_STOCK,
					CH_REQ_ESTADO )
				values (
					'$v_tipdocumento',
					'$v_seriedocumento',
					'$v_numrequerimiento',
					'$v_almacen',
					".$v_val_fecha."
					'$v_art_codigo',
					$v_cantidad_requerida,
					$v_cantidad_atendida,
					$v_venta_fecha,
					$v_venta_mes_actual,
					$v_venta_mes_anterior,
					$v_cantidad_stock,
					'1' 
					)";
	
//	echo $v_sql;
	$v_xsql=pg_query( $conector_id,  $v_sql );
	
	$v_sql="select NUM_TIPDOCUMENTO||NUM_SERIEDOCUMENTO||CH_REQ_NUMREQUERIMIENTO as CLAVE 
					from COM_TA_REQUERIMIENTOS 
					where 	NUM_TIPDOCUMENTO='$v_tipdocumento' and 
							NUM_SERIEDOCUMENTO='$v_seriedocumento' and 
							CH_REQ_NUMREQUERIMIENTO='$v_numrequerimiento' ";
							
	$v_xsql=pg_query( $conector_id,  $v_sql );
	$v_clave=pg_result($v_xsql,0,0);

	echo("<script>");	
	echo("	location.href='cmpr_requerimiento_2.php?v_clave=".$v_clave."' " );
	echo("</script>");
	}

if($boton=="Eliminar") 
	{
	$v_sql="delete from COM_TA_REQUERIMIENTOS 
					where 	NUM_TIPDOCUMENTO='$v_tipdocumento' and 
							NUM_SERIEDOCUMENTO='$v_seriedocumento' and 
							CH_REQ_NUMREQUERIMIENTO='$v_numrequerimiento' and
							ART_CODIGO='$r_articulo' ";
				
	$v_xsql=pg_query( $conector_id, $v_sql);
	}
	
if($boton=="Regresar") 
	{
	echo('<script languaje="JavaScript">');
	echo("	location.href='cmpr_requerimiento.php' ");
	echo('</script>');
	}

if($boton=="Modificar cabecera") 
	{
	$v_sql="update COM_TA_REQUERIMIENTO set
					NUM_TIPDOCUMENTO='$v_tipdocumento',
					NUM_SERIEDOCUMENTO='$v_seriedocumento',
					CH_REQ_NUMREQUERIMIENTO='$v_numrequerimiento',
					CH_REQ_ALMACEN='$v_almacen',
					DT_REQ_FECHA_REQUERIDA='$v_fecha_requerida',
					DT_REQ_FECHA_ATENCION='$v_fecha_atencion',
					CH_REQ_ESTADO='1' 
				where
					NUM_TIPDOCUMENTO='$v_tipdocumento' and 
					NUM_SERIEDOCUMENTO='$v_seriedocumento' and 
					CH_REQ_NUMREQUERIMIENTO='$v_numrequerimiento' ";
				
	$v_xsql=pg_query( $conector_id, $v_sql );
	}

?>



<script language="JavaScript1.2"> 
var digitos=10 //cantidad de digitos buscados 
var puntero=0 
var buffer=new Array(digitos) //declaración del array Buffer 
var cadena="" 

function buscar_op(obj,objfoco){ 
	var letra = String.fromCharCode(event.keyCode) 
	if(puntero >= digitos){ 
		cadena=""; 
		puntero=0; 
		} 
	//si se presiona la tecla ENTER, borro el array de teclas presionadas y salto a otro objeto... 
	if (event.keyCode == 13){ 
		borrar_buffer(); 
		if(objfoco!=0) objfoco.focus(); //evita foco a otro objeto si objfoco=0 
		} 
	//sino busco la cadena tipeada dentro del combo... 
	else
		{ 
		buffer[puntero]=letra; 
		//guardo en la posicion puntero la letra tipeada 
		cadena=cadena+buffer[puntero]; //armo una cadena con los datos que van ingresando al array 
		puntero++; 
		//barro todas las opciones que contiene el combo y las comparo la cadena... 
		for (var opcombo=0;opcombo < obj.length;opcombo++){ 
			if(obj[opcombo].text.substr(0,puntero).toLowerCase()==cadena.toLowerCase())
				{ 
				obj.selectedIndex=opcombo; 
				} 
			} 
		} 
		event.returnValue = false; //invalida la acción de pulsado de tecla para evitar busqueda del primer caracter 
	} 

function borrar_buffer()
	{ 
	//inicializa la cadena buscada 
	cadena=""; 
	puntero=0; 
	}
	
</script> 
<html><head>
<script language="JavaScript" src="/sistemaweb/clases/calendario.js"></script>
<script language="JavaScript" src="/sistemaweb/clases/overlib_mini.js"></script>
<script language="JavaScript" src="/sistemaweb/compras/validacion.js"></script>
<script language="javascript"> 
var miPopup 
function abrealma(){
	miPopup = window.open("../maestros/escogealmacen.php?k_variable=formular.v_almacen","miwin","width=500,height=400,scrollbars=yes") 
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
	document.formular.submit();
	}

</script> 
</head>
<body>
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<form name='formular' action="../compras/cmpr_requerimiento_1.php" method="post">


<input type="hidden" name="v_tipdocumento" value="02">
<input type="hidden" name="v_seriedocumento" value="<?php echo $almacen; ?>">  
<input type="hidden" name="v_numrequerimiento" value="<?php echo $v_numrequerimiento; ?>">

<?php 

if (is_null($v_fecha_requerida))
	{
	$v_almacen=$almacen;
	$v_fecha_requerida=date("d/m/Y");
//	$v_art_codigo=(13);
	$v_cantidad_requerida=0;
	$v_cantidad_atendida=0;
	$v_venta_fecha=0;
	$v_venta_mes_actual=0;
	$v_venta_mes_anterior=0;
	$v_cantidad_stock=0;
	}
?>

<input type="hidden" name="v_cantidad_requerida" value="<?php echo $v_cantidad_requerida ?>">  
<input type="hidden" name="v_cantidad_atendida" value="<?php echo $v_cantidad_atendida ?>">  
<input type="hidden" name="v_venta_fecha" value="<?php echo $v_venta_fecha ?>">  
<input type="hidden" name="v_venta_mes_actual" value="<?php echo $v_venta_mes_actual ?>">  
<input type="hidden" name="v_venta_mes_anterior" value="<?php echo $v_venta_mes_anterior ?>">  
<input type="hidden" name="v_cantidad_stock" value="<?php echo $v_cantidad_stock ?>">  

<table border="1">
	<tr> 
		<th width="500">REQUERIMIENTO DE COMPRA</th>
	</tr>
</table>
<table border="1">
	<tr> 
		<th width="100" >N&deg; NUMERO :		</th>
		<td>&nbsp;<?php echo $v_numrequerimiento;?></td>

		<th>FECHA:</th>
		
		<td>		
		<p>
		<input type="text" name="v_fecha_requerida" size="16" maxlength="10" value='<?php echo $v_fecha_requerida; ?>' onKeyUp="javascript:validarFecha(this);" onblur="javascript:validarFecha(this);"  tabindex="1" > 

		<a href="javascript:show_calendar('formular.v_fecha_requerida');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;">
	    <img src="/sistemaweb/images/show-calendar.gif" width=24 height=22 border=0></a>
		</p>
		</td>
	</tr>
	<tr> 
		<th>ALMACEN
		:</th>

		<td width="118" valign="top">  
		<select name="v_almacen" tabindex="2">
			<?php
			for($i=0;$i<pg_numrows($v_xsqlalma);$i++){		
				$k_alma1 = pg_result($v_xsqlalma,$i,0);	
				$k_alma2 = pg_result($v_xsqlalma,$i,1);
				if (trim($k_alma1)==trim($v_almacen)) { 
					echo "<option value='".$k_alma1."' selected >".$k_alma1." -- ".$k_alma2." </option>";	
					} 
				else {
					echo "<option value='".$k_alma1."' >".$k_alma1." -- ".$k_alma2." </option>";	
					}
		  		}
			?>

		
        </select>
		</td>


	</tr>

</table>



<table border="1" cellpadding="0" cellspacing="0">
	<tr> 
		<th>&nbsp;</th>
		<th>CODIGO</th>
		<th>DESCRIPCION</th>
		<th>CANTIDAD REQ</th>
		<th>VTA FECHA</th>
		<th>VTA MES ACT</th>
		<th>VTA MES ANT</th>
		<th>CANT STOCK</th>
		<th>ESTADO</th>

	</tr>
	<tr> 
		<th>&nbsp;</th>
		<?php
	
		if(strlen($v_art_codigo)>0) 
			{
			$v_xsql=pg_query($conector_id,"select ART_CODIGO, ART_DESCRIPCION from INT_ARTICULOS where ART_CODIGO='".$v_art_codigo."' ");
			if(pg_numrows($v_xsql)>0) 
				{ 
				$v_art_codigo=pg_result($v_xsql,0,0); 
				$v_art_descripcion=pg_result($v_xsql,0,1); 
				}
			else
				{
				echo('<script languaje="JavaScript"> ');
				echo('alert(" No Existe Articulo !!! "); ');
				echo('</script>');
				}
			}
				
		?>
		
		<th><input type="text" name="v_art_codigo" size='19' maxlength="13" value="<?php echo $v_art_codigo;?>"  onblur='submit()' onkeyup='validarNumeroEntero(this)' tabindex="5" >
			<input name="imgarti" type="image" src="/sistemaweb/images/help.gif" width="16" height="15" border="0" onClick="abrearti()" ></th>
		<td>&nbsp; <?php echo $v_art_descripcion; ?> </td>
		
		<th><input name="v_cantidad_requerida" type="text" size='15'  maxlength="15" value="<?php echo $v_cantidad_requerida;?>" align="right" onblur='submit()' onkeyup='validarNumeroDecimales(this)' tabindex="6"></th>

		<?php
		if(strlen($v_art_codigo)>0) 
			{
			//del mes actual a la fecha
			$v_ano_act=substr($v_fecha_requerida,6,4);
			$v_mes_act=substr($v_fecha_requerida,3,2);
			$v_sql="select NU_CAN".$v_mes_act."  
					from VEN_TA_VENTA_MENSUALXITEM
					where CH_PERIODO='$v_ano_act' and CH_SUCURSAL='$v_almacen' and ART_CODIGO='$v_art_codigo'  ";
			// echo $v_sql;
			$v_xsql=pg_query($conector_id,$v_sql);
			$v_ilimit=pg_numrows($v_xsql);
			if ($v_ilimit>0) { $v_venta_fecha=pg_result($v_xsql,0,0) ;} else { $v_venta_fecha=0; }

			// del ultimo mes finalizado
			if ($v_mes_act=='01') 
				{ 
				$v_mes_actual='12'; 
				$v_ano_act=$v_ano_act-1; 
				}
			else
				{
				$v_mes_act=$v_mes_act-1;
				$v_mes_act=str_pad(trim($v_mes_act),2,'0', STR_PAD_LEFT );
				}
			$v_sql="select NU_CAN".$v_mes_act."  
					from VEN_TA_VENTA_MENSUALXITEM
					where CH_PERIODO='$v_ano_act' and CH_SUCURSAL='$v_almacen' and ART_CODIGO='$v_art_codigo'  ";
			// echo $v_sql;
			$v_xsql=pg_query($conector_id,$v_sql);
			$v_ilimit=pg_numrows($v_xsql);
			if ($v_ilimit>0) { $v_venta_mes_actual=pg_result($v_xsql,0,0) ;} else { $v_venta_mes_actual=0; }

			// del mes anterior finalizado
			if ($v_mes_act=='01') 
				{ 
				$v_mes_act='12'; 
				$v_ano_act=$v_ano_act-1; 
				}
			else
				{
				$v_mes_act=$v_mes_act-1;
				$v_mes_act=str_pad(trim($v_mes_act),2,'0', STR_PAD_LEFT );
				}
			$v_sql="select NU_CAN".$v_mes_act."  
					from VEN_TA_VENTA_MENSUALXITEM
					where CH_PERIODO='$v_ano_act' and ART_CODIGO='$v_art_codigo' and CH_SUCURSAL='$v_almacen' ";
			// echo $v_sql;
			$v_xsql=pg_query($conector_id,$v_sql);
			$v_ilimit=pg_numrows($v_xsql);
			if ($v_ilimit>0) { $v_venta_mes_anterior=pg_result($v_xsql,0,0) ;} else { $v_venta_mes_anterior=0; }

			// Stock Actual
			$v_ano_act=substr($v_fecha_requerida,6,4);
			$v_mes_act=substr($v_fecha_requerida,3,2);
			$v_sql="select STK_STOCKACTUAL  
					from INV_SALDOALMA 
					where STK_ALMACEN='$v_almacen' and STK_PERIODO='$v_ano_act' and ART_CODIGO='$v_art_codigo' ";
			// echo $v_sql;
			$v_xsql=pg_query($conector_id,$v_sql);
			$v_ilimit=pg_numrows($v_xsql);
			if ($v_ilimit>0) { 	$v_cantidad_stock=pg_result($v_xsql,0,0) ;} else { $v_cantidad_stock=0; }
			
			}
			
		
		// <td><p align='right'>&nbsp;<?php echo $v_venta_fecha; ? ></p></td>
		// <td><p align='right'>&nbsp;<?php echo $v_venta_mes_actual; ? ></p></td>
		//<td><p align='right'>&nbsp;<?php echo $v_venta_mes_anterior; ? ></p></td>
		//<td><p align='right'>&nbsp;<?php echo $v_cantidad_stock; ? ></p></td>
		?>
		
		<td><input type="text" size='15'  maxlength="15"  name="v_venta_fecha" value="<?php echo $v_venta_fecha ?>" readonly='true'>  </td>
		<td><input type="text" size='15'  maxlength="15"  name="v_venta_mes_actual" value="<?php echo $v_venta_mes_actual ?>" readonly='true'> </td> 
		<td><input type="text" size='15'  maxlength="15"  name="v_venta_mes_anterior" value="<?php echo $v_venta_mes_anterior ?>" readonly='true'>  </td>
		<td><input type="text" size='15'  maxlength="15"  name="v_cantidad_stock" value="<?php echo $v_cantidad_stock ?>" readonly='true'>  </td>

		<th><input type="submit" name="boton" value="Agregar" tabindex="7"></th>
	</tr>
	
    <?php
	$v_sql="select  REQ.NUM_TIPDOCUMENTO, 
					REQ.NUM_SERIEDOCUMENTO,
					REQ.CH_REQ_NUMREQUERIMIENTO,
					REQ.CH_REQ_ALMACEN,
					REQ.DT_REQ_FECHA_REQUERIDA,
					REQ.ART_CODIGO, 
					ART.ART_DESCRIPCION, 
					REQ.NU_REQ_CANTIDAD_REQUERIDA
					from COM_TA_REQUERIMIENTOS REQ, INT_ARTICULOS ART 
					where   REQ.ART_CODIGO=ART.ART_CODIGO and 
							REQ.NUM_TIPDOCUMENTO='".$v_tipdocumento."' and 
							REQ.NUM_SERIEDOCUMENTO='".$v_seriedocumento."' and 
							REQ.CH_REQ_NUMREQUERIMIENTO='".$v_numrequerimiento."' " ;
							
	$v_xsql=pg_query($conector_id,$v_sql);
	$v_ilimit=pg_numrows($v_xsql);
	while($v_irow<$v_ilimit) 
		{
		$ad0=pg_result($v_xsql,$v_irow,0);
		$ad1=pg_result($v_xsql,$v_irow,1);
		$ad2=pg_result($v_xsql,$v_irow,2);
		$ad3=pg_result($v_xsql,$v_irow,3);
		$ad4=pg_result($v_xsql,$v_irow,4);
		$ad5=pg_result($v_xsql,$v_irow,5);
		$ad6=pg_result($v_xsql,$v_irow,6);
		$ad7=pg_result($v_xsql,$v_irow,7);
		echo "<tr>";
		echo "<td><input type='radio' name='r_articulo' value='".$ad5."'></td>";
		echo "<td>".$ad6."</td>";
		echo "<td><p align='right'>".$ad7."</p></td>";
		echo "<td>&nbsp;dato vta 1</td>";
		echo "<td>&nbsp;dato vta 2</td>";
		echo "<td>&nbsp;dato vta 3</td>";		
		echo "<td>&nbsp;dato vta 4</td>";
		echo "</tr>";
		$v_irow++;
		}

	?>
	<tr> 
		<td>&nbsp;</td>
		<td><input type="submit" name="boton" value="Eliminar"></td>
		<td><input type="submit" name="boton" value="Modificar cabecera">
		&nbsp;&nbsp;
		<input type="submit" name="boton" value="Regresar"></td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
</table>




</form>
</body>
</html>
<?php 

 
// comprueba si la conexion existe y la cierra
if ($conector_id) pg_close($conector_id);

// restaura el control de errores original
$clase_error->_error();


